<?php
/**
 * WC_GC_REST_API_Order_Controller class
 *
 * @package  WooCommerce Gift Cards
 * @since    1.8.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add custom REST API fields.
 *
 * @class    WC_GC_REST_API_Order_Controller
 * @version  2.0.4
 */
class WC_GC_REST_API_Order_Controller {

	/**
	 * Custom REST API order field names, indicating support for getting/updating.
	 *
	 * @var array
	 */
	private $order_fields = array(
		'gift_cards' => array( 'get', 'update' ),
	);

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->hooks();
	}

	/**
	 * Sets up hooks.
	 */
	private function hooks() {
		// Register WP REST API custom order fields.
		add_action( 'rest_api_init', array( $this, 'register_order_fields' ), 0 );
	}

	/**
	 * Register custom REST API fields for order requests.
	 */
	public function register_order_fields() {

		foreach ( $this->order_fields as $field_name => $field_supports ) {

			$args = array(
				'schema' => $this->get_order_field_schema( $field_name ),
			);

			if ( in_array( 'get', $field_supports, true ) ) {
				$args['get_callback'] = array( $this, 'get_order_field_value' );
			}
			if ( in_array( 'update', $field_supports, true ) ) {
				$args['update_callback'] = array( $this, 'update_order_field_value' );
			}

			register_rest_field( 'shop_order', $field_name, $args );
		}
	}

	/**
	 * Gets schema properties for GC order fields.
	 *
	 * @param string $field_name
	 *
	 * @return array
	 */
	public function get_order_field_schema( $field_name ) {

		$extended_schema = $this->get_extended_order_schema();
		$field_schema    = isset( $extended_schema[ $field_name ] ) ? $extended_schema[ $field_name ] : null;

		return $field_schema;
	}

	/**
	 * Gets values for GC order fields.
	 *
	 * @param  array           $response
	 * @param  string          $field_name
	 * @param  WP_REST_Request $request
	 * @return array
	 */
	public function get_order_field_value( $response, $field_name, $request ) {

		$data = null;
		if ( isset( $response['id'] ) ) {
			$order = wc_get_order( $response['id'] );
			$data  = $this->get_order_field( $field_name, $order );
		}

		return $data;
	}

	/**
	 * Gets GC specific order data.
	 *
	 * @param  string   $key
	 * @param  WC_Order $order
	 * @return array
	 */
	private function get_order_field( $key, $order ) {

		switch ( $key ) {

			case 'gift_cards':
				$items = $order->get_items( 'gift_card' );
				$value = array();

				if ( empty( $items ) ) {
					break;
				}

				/* @var WC_GC_Order_Item_Gift_Card $item */
				foreach ( $items as $item ) {
					$value[] = array(
						'id'     => $item->get_giftcard_id(),
						'code'   => $item->get_code(),
						'amount' => $item->get_amount(),
					);
				}

				break;
		}

		return $value;
	}

	/**
	 * Updates values for GC order fields.
	 *
	 * @param  mixed  $field_value
	 * @param  mixed  $response
	 * @param  string $field_name
	 * @return boolean
	 */
	public function update_order_field_value( $field_value, $response, $field_name ) {

		if ( 'gift_cards' !== $field_name ) {
			return true;
		}

		if ( empty( $field_value ) || ! is_array( $field_value ) ) {
			return true;
		}

		$require_saving     = false;
		$require_refetching = false;
		$order              = false;
		if ( $response instanceof WP_Post ) {
			$order = wc_get_order( absint( $response->ID ) );
		} elseif ( $response instanceof WC_Order ) {
			$order = $response;
		}

		if ( ! is_a( $order, 'WC_Order' ) ) {
			return true;
		}

		// Check order status.
		if ( ! $order->is_editable() ) {
			throw new WC_REST_Exception( 'woocommerce_rest_gift_card_order_not_editable', __( 'Cannot update gift cards on a non editable order.', 'woocommerce-gift-cards' ), 400 );
		}

		// Gather current order item data.
		$current_items = array();
		$items         = $order->get_items( 'gift_card' );
		if ( ! empty( $items ) ) {
			foreach ( $items as $order_item ) {
				$current_items[ $order_item->get_code() ] = $order_item;
			}
		}

		// Parse input data.
		try {
			$parsed_giftcards = $this->parse_order_field_value( $field_value, $order, $current_items );
		} catch ( Exception $e ) {
			throw new WC_REST_Exception( 'woocommerce_rest_gift_card_cannot_parse_data', __( $e->getMessage(), 'woocommerce-gift-cards' ), 400 );
		}

		// Check for being able to apply all statements.
		if ( ! empty( $parsed_giftcards ) ) {
			$order_total_to_cover = $order->get_total();

			// Take into account forced delete balances, before check.
			foreach ( $parsed_giftcards as $index => $parsed_giftcard ) {
				if ( $parsed_giftcard['force_delete'] ) {
					$item                 = $current_items[ $parsed_giftcard['giftcard']->get_code() ];
					$order_total_to_cover = $order_total_to_cover + $item->get_amount();
				}
			}

			// Check.
			foreach ( $parsed_giftcards as $index => $parsed_giftcard ) {
				$is_valid    = false;
				$is_optional = $parsed_giftcard['is_optional'];
				$giftcard    = $parsed_giftcard['giftcard'];

				if ( $parsed_giftcard['exists'] ) {
					$item                 = $current_items[ $giftcard->get_code() ];
					$order_total_to_cover = $order_total_to_cover + $item->get_amount();
					$balance_remaining    = $giftcard->get_balance() + $item->get_amount();
				} else {
					$balance_remaining = $giftcard->get_balance();
				}

				if ( $parsed_giftcard['amount'] > $order_total_to_cover ) {

					if ( $is_optional ) {
						continue;
					}

					// translators: %s gift card code.
					throw new WC_REST_Exception( 'woocommerce_rest_gift_card_cannot_apply', sprintf( __( 'Requested amount for gift card code %s exceeded the order total.', 'woocommerce-gift-cards' ), $giftcard->get_code() ), 400 );

				} elseif ( $parsed_giftcard['amount'] > $balance_remaining ) {

					if ( $is_optional ) {
						continue;
					}

					// translators: %s gift card code.
					throw new WC_REST_Exception( 'woocommerce_rest_gift_card_cannot_apply', sprintf( __( 'Not enough balance for gift card code %s.', 'woocommerce-gift-cards' ), $giftcard->get_code() ), 400 );
				} else {
					$is_valid = true;
				}

				if ( $is_valid ) {
					$order_total_to_cover = $order_total_to_cover - $parsed_giftcard['amount'];
				}
			}
		}

		// Check for early deletions.
		if ( ! empty( $parsed_giftcards ) ) {
			foreach ( $parsed_giftcards as $index => $parsed_giftcard ) {

				if ( ! $parsed_giftcard['exists'] || ! $parsed_giftcard['force_delete'] ) {
					continue;
				}

				$item = $current_items[ $parsed_giftcard['giftcard']->get_code() ];

				// Refund it first.
				WC_GC()->order->maybe_credit_giftcards( $order->get_id(), $order, array( $item ) );

				// Remove item.
				$order->remove_item( $item->get_id() );
				wc_delete_order_item( $item->get_id() );
				unset( $parsed_giftcards[ $index ] );

				// Recalculate.
				$order->calculate_totals( false );
			}

			$order->save();
		}

		if ( empty( $parsed_giftcards ) ) {
			return true;
		}

		// Apply or update if needed.
		foreach ( $parsed_giftcards as $parsed_giftcard ) {

			try {

				$giftcard         = $parsed_giftcard['giftcard'];
				$requested_amount = $parsed_giftcard['amount'];
				$is_optional      = $parsed_giftcard['is_optional'];

				// Giftcard already exists.
				if ( $parsed_giftcard['exists'] ) {

					$item = $current_items[ $giftcard->get_code() ];

					// If amount = 0 then remove gift card.
					if ( 0.0 === $requested_amount ) {

						// Refund it first.
						WC_GC()->order->maybe_credit_giftcards( $order->get_id(), $order, array( $item ) );

						// Remove item.
						$order->remove_item( $item->get_id() );
						wc_delete_order_item( $item->get_id() );

						// Recalculate.
						$order->calculate_totals( false );

						$require_saving = true;
						continue;
					}

					// Calculate order total without the current item.
					$available_order_total = $order->get_total() + $item->get_amount();
					$available_balance     = $giftcard->get_balance() + $item->get_amount();

					// Amount alignment.
					if ( is_null( $requested_amount ) ) {
						$requested_amount = (float) min( $available_balance, $available_order_total );
					}

					if ( $requested_amount > $available_order_total ) {
						throw new Exception( sprintf( __( 'Requested amount for gift card code %s exceeded the order total.', 'woocommerce-gift-cards' ), $giftcard->get_code() ), 400 );
					}

					if ( $requested_amount > $available_balance ) {
						throw new Exception( sprintf( __( 'Not enough balance for gift card code %s.', 'woocommerce-gift-cards' ), $giftcard->get_code() ), 400 );
					}

					if ( 0.0 === $requested_amount || $item->get_amount() === $requested_amount ) {
						// No update needed. Moving on...
						continue;
					}

					// Refund it first.
					WC_GC()->order->maybe_credit_giftcards( $order->get_id(), $order, array( $item ) );
					$item->set_amount( $requested_amount );
					$order->calculate_totals( false );

					// Debit it again.
					WC_GC()->order->maybe_debit_giftcards( $order->get_id(), $order, array( $item ) );

					// Schedule a refetch and save.
					$require_saving = true;

					// Create new order item.
				} elseif ( $requested_amount !== 0.0 ) {

					// Amount alignment.
					if ( is_null( $requested_amount ) ) {
						$requested_amount = (float) min( $giftcard->get_balance(), $order->get_total() );
					}

					if ( 0.0 === (float) $order->get_total() || $requested_amount > $order->get_total() ) {
						// translators: %s gift card code.
						throw new Exception( sprintf( __( 'Requested amount for gift card code %s exceeded the order total.', 'woocommerce-gift-cards' ), $giftcard->get_code() ), 400 );
					}

					if ( $requested_amount > $giftcard->get_balance() ) {
						// translators: %s gift card code.
						throw new Exception( sprintf( __( 'Not enough balance for gift card code %s.', 'woocommerce-gift-cards' ), $giftcard->get_code() ), 400 );
					}

					if ( 0.0 === $requested_amount ) {
						// No update needed. Moving on...
						continue;
					}

					// Sanity checks.
					$is_valid_giftcard = $giftcard->get_id() && $giftcard->is_active() && ! $giftcard->has_expired();
					$is_valid_balance  = $giftcard->get_balance() >= $requested_amount;

					if ( ! $is_valid_giftcard ) {
						// translators: %s gift card code.
						throw new Exception( sprintf( __( 'Gift card code %s is invalid.', 'woocommerce-gift-cards' ), $giftcard->get_code() ), 1 );
					}

					if ( ! $is_valid_balance ) {
						// translators: %s gift card code.
						throw new Exception( sprintf( __( 'Gift card code %s does not have enough balance.', 'woocommerce-gift-cards' ), $giftcard->get_code() ), 1 );
					}

					if ( $is_valid_giftcard && $is_valid_balance ) {

						$item = new WC_GC_Order_Item_Gift_Card();

						$item->set_props(
							array(
								'giftcard_id' => $giftcard->get_id(),
								'code'        => $giftcard->get_code(),
								'amount'      => $requested_amount,
							)
						);

						$item->save();
						$order->add_item( $item );
						$order->calculate_totals( false );
						WC_GC()->order->maybe_debit_giftcards( $order->get_id(), $order, array( $item ) );

						// Schedule a refetch and save.
						$require_saving = true;
					}
				}
			} catch ( Exception $e ) {

				if ( $require_saving ) {
					$order->calculate_totals( false );
					$order->save();
				}

				if ( $is_optional ) {
					continue;
				}

				// This will stop the process and break the loop.
				throw new WC_REST_Exception( 'woocommerce_rest_gift_card_cannot_apply', $e->getMessage(), 400 );
			}
		}

		if ( $require_saving ) {
			$order->calculate_totals( false );
			$order->save();
		}

		return true;
	}

	/**
	 * Parse the value from the REST request.
	 *
	 * @throws Exception
	 *
	 * @return array {
	 *     @type  WC_GC_Gift_Card  $giftcard
	 *     @type  float|null       $amount
	 *     @type  bool             $force_delete
	 *     @type  bool             $is_optional
	 * }
	 */
	private function parse_order_field_value( $value, $order, $current_items = array() ) {

		// Parse input data.
		$parsed_value   = array();
		$existing_codes = array_keys( $current_items );

		foreach ( $value as $info ) {

			if ( empty( $info['code'] ) ) {
				throw new Exception( __( 'Missing gift card code.', 'woocommerce-gift-cards' ) );
				continue;
			}

			$giftcard_data = wc_gc_get_gift_card_by_code( $info['code'] );
			$giftcard      = $giftcard_data ? new WC_GC_Gift_Card( $giftcard_data ) : false;
			if ( ! is_a( $giftcard, 'WC_GC_Gift_Card' ) || ! $giftcard->get_id() ) {
				// translators: %s gift card code.
				throw new Exception( sprintf( __( 'Gift card code %s not found.', 'woocommerce-gift-cards' ), $info['code'] ) );
			}

			if ( isset( $info['id'] ) && absint( $info['id'] ) !== $giftcard->get_id() ) {
				throw new Exception( __( 'Gift card id and code do not match.', 'woocommerce-gift-cards' ) );
			}

			$requested_amount = isset( $info['amount'] ) ? (float) $info['amount'] : null;

			$parsed_giftcard                 = array();
			$parsed_giftcard['giftcard']     = $giftcard;
			$parsed_giftcard['amount']       = $requested_amount;
			$parsed_giftcard['is_optional']  = isset( $info['optional'] ) && 'true' === $info['optional'];
			$parsed_giftcard['force_delete'] = isset( $info['delete'] ) && 'true' === $info['delete'];
			$parsed_giftcard['exists']       = in_array( $info['code'], $existing_codes );

			// Add.
			if ( $parsed_giftcard['force_delete'] && ! $parsed_giftcard['exists'] ) {
				// Bypass delete statements if the giftcard doesn't exist in the order.
				continue;
			}

			$parsed_value[ $giftcard->get_code() ] = $parsed_giftcard;
		}

		return $parsed_value;
	}

	/**
	 * Gets extended (unprefixed) schema properties for orders.
	 *
	 * @return array
	 */
	private function get_extended_order_schema() {
		return array(
			'gift_cards' => array(
				'description' => __( 'List of gift cards used in this order.', 'woocommerce-gift-cards' ),
				'type'        => 'array',
				'context'     => array( 'view', 'edit' ),
				'items'       => array(
					'type'       => 'object',
					'properties' => array(
						'id'       => array(
							'description' => __( 'Gift card ID.', 'woocommerce-gift-cards' ),
							'type'        => 'integer',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
						'code'     => array(
							'description' => __( 'Gift card code.', 'woocommerce-gift-cards' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
							'required'    => true,
						),
						'amount'   => array(
							'description' => __( 'Amount used.', 'woocommerce-gift-cards' ),
							'type'        => 'number',
							'context'     => array( 'view', 'edit' ),
						),
						'delete'   => array(
							'description' => __( 'Delete flag.', 'woocommerce-gift-cards' ),
							'type'        => 'string',
							'context'     => array( 'edit' ),
						),
						'optional' => array(
							'description' => __( 'Optional flag.', 'woocommerce-gift-cards' ),
							'type'        => 'string',
							'context'     => array( 'edit' ),
						),
					),
				),
			),
		);
	}
}
