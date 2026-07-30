<?php
/**
 * WooCommerce GC Extend Store API.
 *
 * A class to extend the store public API with gift card related data.
 *
 * @package WooCommerce Gift Cards
 * @since   1.11.0
 * @version 1.16.7
 */

use Automattic\WooCommerce\StoreApi\Exceptions\RouteException;
use Automattic\WooCommerce\StoreApi\Schemas\V1\CartSchema;

class WC_GC_Checkout_Blocks_Endpoint {

	/**
	 * Plugin Identifier, unique to each plugin.
	 *
	 * @var string
	 */
	const IDENTIFIER = 'woocommerce-gift-cards';

	/**
	 * Bootstrap.
	 */
	public static function init() {

		// Extend StoreAPI.
		self::extend_store();

		// Handle Checkout.
		add_action( 'woocommerce_store_api_checkout_update_order_meta', array( __CLASS__, 'rest_checkout_update_order_meta' ) );

		// Filter minimum cart item quantity.
		add_filter( 'woocommerce_store_api_product_quantity_minimum', array( __CLASS__, 'filter_min_cart_item_qty' ), 10, 3 );

		// Filter group of cart item quantity.
		add_filter( 'woocommerce_store_api_product_quantity_multiple_of', array( __CLASS__, 'filter_multiple_of_cart_item_qty' ), 10, 3 );
	}

	/**
	 * Register extensibility points.
	 */
	protected static function extend_store() {

		if ( function_exists( 'woocommerce_store_api_register_endpoint_data' ) ) {
			woocommerce_store_api_register_endpoint_data(
				array(
					'endpoint'        => CartSchema::IDENTIFIER,
					'namespace'       => self::IDENTIFIER,
					'data_callback'   => array( 'WC_GC_Checkout_Blocks_Endpoint', 'extend_cart_data' ),
					'schema_callback' => array( 'WC_GC_Checkout_Blocks_Endpoint', 'extend_cart_schema' ),
					'schema_type'     => ARRAY_A,
				)
			);
		}

		if ( function_exists( 'woocommerce_store_api_register_update_callback' ) ) {
			woocommerce_store_api_register_update_callback(
				array(
					'namespace' => self::IDENTIFIER,
					'callback'  => array( 'WC_GC_Checkout_Blocks_Endpoint', 'rest_handle_gift_card_endpoint' ),
				)
			);
		}
	}

	/**
	 * Create gift card order line items.
	 *
	 * @throws RouteException
	 *
	 * @param \WC_Order $order
	 */
	public static function rest_checkout_update_order_meta( $order ) {

		if ( ! WC_GC_Core_Compatibility::is_store_api_request( 'checkout', 'POST' ) ) {
			// Create gift card items only when submitting the order.
			return;
		}

		try {

			// Check for "resume" order and clean up.
			$items = $order->get_items( 'gift_card' );
			if ( ! empty( $items ) ) {
				$order->remove_order_items( 'gift_card' );
			}

			// Create line items based on session data.
			WC_GC()->order->checkout_create_order( $order );

			// Calculate totals to include gift cards.
			$order->calculate_totals();

			// Persist changes.
			$order->save();

		} catch ( \Exception $e ) {
			throw new RouteException( 'woocommerce_gift_cards_invalid', $e->getMessage() );
		}
	}

	/**
	 * Handle gift card related actions through StoreAPI.
	 *
	 * @param array $args
	 */
	public static function rest_handle_gift_card_endpoint( $args ) {

		switch ( $args['action'] ) {

			/**
			 * Handle balance checkbox toggle.
			 */
			case 'set_balance_usage':
				$is_using = 'yes' === $args['value'];
				WC_GC()->account->set_balance_usage( $is_using );
				break;

			/**
			 * Handle removing gift cards.
			 */
			case 'remove_gift_card_from_session':
				$giftcard_id = (int) $args['wc_gc_remove_id'];
				if ( ! $giftcard_id ) {
					break;
				}
				WC_GC()->giftcards->remove_giftcard_from_session( $giftcard_id );
				break;

			/**
			 * Apply gift cards in the cart.
			 */
			case 'apply_gift_card_to_session':
				self::rest_process_gift_card_form( $args );
				break;
		}
	}

	/**
	 * Process front-end gift card form through REST API.
	 *
	 * @throws RouteException
	 *
	 * @param  array $args
	 */
	protected static function rest_process_gift_card_form( $args ) {

		if ( ! isset( $args['action'] ) && $args['action'] !== 'apply_gift_card_to_session' ) {
			return;
		}

		if ( ! wc_gc_is_ui_disabled() ) {
			return;
		}

		if ( WC_GC()->cart->cart_contains_gift_card() ) {
			throw new RouteException( 'woocommerce_blocks_gift_card_unsupported_cart', __( 'Gift cards cannot be purchased using other gift cards.', 'woocommerce-gift-cards' ) );
		}

		if ( empty( $args ) || ! isset( $args['wc_gc_cart_code'] ) ) {
			return;
		}

		$code = wc_clean( $args['wc_gc_cart_code'] );
		if ( empty( $code ) ) {
			throw new RouteException( 'woocommerce_blocks_gift_card_code_empty', __( 'Please enter your gift card code.', 'woocommerce-gift-cards' ) );
		} elseif ( strlen( $code ) !== 19 ) {
			throw new RouteException( 'woocommerce_blocks_gift_card_invalid', __( 'Please enter a gift card code that follows the format XXXX-XXXX-XXXX-XXXX, where X can be any letter or number.', 'woocommerce-gift-cards' ) );
		}

		$results       = WC_GC()->db->giftcards->query(
			array(
				'return' => 'objects',
				'code'   => $code,
				'limit'  => 1,
			)
		);
		$giftcard_data = count( $results ) ? array_shift( $results ) : false;

		if ( $giftcard_data ) {

			$giftcard = new WC_GC_Gift_Card( $giftcard_data );
			// If logged in check if auto-redeem is on.
			if ( get_current_user_id() && apply_filters( 'woocommerce_gc_auto_redeem', false ) ) {
				$giftcard->redeem( get_current_user_id() );
			} else {
				WC_GC()->giftcards->apply_giftcard_to_session( $giftcard );
			}
		} else {
			throw new RouteException( 'woocommerce_blocks_gift_card_not_found', __( 'Gift card not found.', 'woocommerce-gift-cards' ) );
		}
	}

	/**
	 * Register gift card data into cart API.
	 */
	public static function extend_cart_data() {

		// Disable UI if there are gift card products in the cart.
		if ( WC_GC()->cart->cart_contains_gift_card() ) {
			return array();
		}

		// UI disabled by filter.
		if ( ! wc_gc_is_ui_disabled() ) {
			return array();
		}

		// Extended cart data state.
		$cart_data = array();

		// Formatters.
		$money_formatter = woocommerce_store_api_get_formatter( 'money' );
		$mask_codes      = wc_gc_mask_codes( 'checkout' );

		// Fetch session data from GC Cart Contoller.
		$applied_giftcard_data = WC_GC()->cart->get_applied_gift_cards();
		$account_giftcard_data = WC_GC()->cart->get_account_gift_cards();
		$totals                = WC_GC()->cart->get_account_totals_breakdown();

		// Account balance related data.
		$cart_data['balance']          = $money_formatter->format( WC_GC()->account->get_balance() );
		$cart_data['is_using_balance'] = (bool) WC_GC()->account->use_balance();
		$cart_data['pending_total']    = isset( $totals['pending_total'] ) ? $money_formatter->format( $totals['pending_total'] ) : 0;
		$cart_data['cart_total']       = isset( $totals['cart_total'] ) ? $money_formatter->format( $totals['cart_total'] ) : 0;
		$cart_data['available_total']  = isset( $totals['available_total'] ) ? $money_formatter->format( $totals['available_total'] ) : 0;

		// Format balance related gift cards.
		$account_giftcards = array();
		if ( ! empty( $account_giftcard_data['giftcards'] ) ) {
			foreach ( $account_giftcard_data['giftcards'] as $data ) {

				$amount              = $data['amount'];
				$giftcard            = $data['giftcard'];
				$account_giftcards[] = array(
					'code'   => $mask_codes ? wc_gc_mask_code( $giftcard->get_code() ) : $giftcard->get_code(),
					'amount' => $money_formatter->format( $amount ),
				);
			}
		}

		$cart_data['account_giftcards'] = $account_giftcards;

		// Format gift cards applied through the inline form.
		$applied_giftcards = array();
		if ( ! empty( $applied_giftcard_data['giftcards'] ) ) {
			foreach ( $applied_giftcard_data['giftcards'] as $data ) {

				$amount              = $data['amount'];
				$giftcard            = $data['giftcard'];
				$applied_giftcards[] = array(
					'id'              => $giftcard->get_id(),
					'code'            => $mask_codes ? wc_gc_mask_code( $giftcard->get_code() ) : $giftcard->get_code(),
					'amount'          => $money_formatter->format( $amount ),
					'balance'         => $money_formatter->format( $giftcard->get_balance() ),
					'pending_message' => $giftcard->get_pending_balance() > 0 ? wc_gc_get_pending_balance_resolution( $giftcard ) : '',
				);
			}
		}

		$cart_data['applied_giftcards'] = $applied_giftcards;

		return $cart_data;
	}

	/**
	 * Register gift cards schema into cart endpoint.
	 *
	 * @return array Registered schema.
	 */
	public static function extend_cart_schema() {

		if ( isset( WC()->cart ) && WC_GC()->cart->cart_contains_gift_card() ) {
			return array();
		}

		// UI disabled by filter.
		if ( ! wc_gc_is_ui_disabled() ) {
			return array();
		}

		return array(
			'balance'           => array(
				'description' => __( 'Total balance for the current customer.', 'woocommerce-gift-cards' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'cart_total'        => array(
				'description' => __( 'Cart total before gift cards.', 'woocommerce-gift-cards' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'pending_total'     => array(
				'description' => __( 'Total pending amount in customer\'s balance.', 'woocommerce-gift-cards' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'available_total'   => array(
				'description' => __( 'Total amount available to be captured by gift card balance.', 'woocommerce-gift-cards' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'is_using_balance'  => array(
				'description' => __( 'True if the customer is using their gift card balance.', 'woocommerce-gift-cards' ),
				'type'        => 'boolean',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'account_giftcards' => array(
				'description' => __( 'All gift cards applied using gift card balance.', 'woocommerce-gift-cards' ),
				'type'        => 'array',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
				'items'       => array(
					'code'   => array(
						'description' => __( 'Gift card code.', 'woocommerce-gift-cards' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
					'amount' => array(
						'description' => __( 'Gift card amount.', 'woocommerce-gift-cards' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
				),
			),
			'applied_giftcards' => array(
				'description' => __( 'All gift cards applied using the gift card form.', 'woocommerce-gift-cards' ),
				'type'        => 'array',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
				'items'       => array(
					'id'              => array(
						'description' => __( 'Gift card ID.', 'woocommerce-gift-cards' ),
						'type'        => 'integer',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
					'code'            => array(
						'description' => __( 'Gift card code.', 'woocommerce-gift-cards' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
					'amount'          => array(
						'description' => __( 'Gift card amount.', 'woocommerce-gift-cards' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
					'balance'         => array(
						'description' => __( 'Gift card balance.', 'woocommerce-gift-cards' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
					'pending_message' => array(
						'description' => __( 'Gift Card pending balance message (HTML).', 'woocommerce-gift-cards' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
				),
			),
		);
	}

	/**
	 * Adjust cart item quantity limits to ensure proper handling of multiple reciepients.
	 *
	 * @since 1.16.7
	 *
	 * @param mixed       $value The value being filtered.
	 * @param \WC_Product $product The product object.
	 * @param array|null  $cart_item The cart item if the product exists in the cart, or null.
	 *
	 * @return mixed
	 */
	public static function filter_min_cart_item_qty( $value, $product, $cart_item ) {

		if ( ! WC_GC_Gift_Card_Product::is_gift_card( $product ) || ! is_array( $cart_item ) || empty( $cart_item['wc_gc_giftcard_to_multiple'] ) ) {
			return $value;
		}

		return count( $cart_item['wc_gc_giftcard_to_multiple'] );
	}

	/**
	 * Ensure that cart item quantity can be changed in specific intervals based on reciepients.
	 *
	 * @since 1.16.7
	 *
	 * @param mixed       $value The value being filtered.
	 * @param \WC_Product $product The product object.
	 * @param array|null  $cart_item The cart item if the product exists in the cart, or null.
	 *
	 * @return mixed
	 */
	public static function filter_multiple_of_cart_item_qty( $value, $product, $cart_item ) {

		if ( ! WC_GC_Gift_Card_Product::is_gift_card( $product ) || ! is_array( $cart_item ) || empty( $cart_item['wc_gc_giftcard_to_multiple'] ) ) {
			return $value;
		}

		return count( $cart_item['wc_gc_giftcard_to_multiple'] );
	}
}
