<?php
/**
 * WC_GC_Cart class
 *
 * @package  WooCommerce Gift Cards
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Automattic\WooCommerce\StoreApi\Exceptions\RouteException;

/**
 * WC_GC_Cart class.
 *
 * @version 2.7.2
 */
class WC_GC_Cart {

	/**
	 * Keep track of form notices.
	 *
	 * @var array
	 */
	private $notices;

	/**
	 * Applied Gift Cards.
	 *
	 * @var array
	 */
	private $giftcards;

	/**
	 * Keep track of various totals.
	 *
	 * @var array
	 */
	private $totals;

	/**
	 * Constructor for the cart class. Loads options and hooks in the init method.
	 */
	public function __construct() {
		add_action( 'template_redirect', array( $this, 'maybe_process_email_session' ) );

		// Alter the Cart total to include Gift Cards.
		add_action( 'woocommerce_after_calculate_totals', array( $this, 'after_calculate_totals' ), 999 );
		add_action( 'woocommerce_cart_emptied', array( $this, 'destroy_cart_session' ) );
		add_filter( 'woocommerce_cart_hash', array( $this, 'handle_cart_hash' ), 10, 2 );

		// Print Gift Card related table.
		add_action( 'woocommerce_cart_totals_before_order_total', array( $this, 'print_gift_cards' ) );
		add_action( 'woocommerce_review_order_before_order_total', array( $this, 'print_gift_cards' ) );
		add_action( 'woocommerce_checkout_update_order_review', array( $this, 'update_order_review' ) );

		// Add inline cart form.
		add_action( 'woocommerce_proceed_to_checkout', array( $this, 'display_form' ), 9 );
		add_action( 'woocommerce_review_order_before_payment', array( $this, 'display_form' ), 9 );

		// Handle Store API add-to-cart requests.
		add_filter( 'woocommerce_store_api_add_to_cart_data', array( $this, 'handle_store_api_add_to_cart_request' ), 10, 2 );

		// Add Store API validation hooks.
		add_action( 'rest_pre_dispatch', array( $this, 'add_store_api_validation_hooks' ), 10, 0 );

		// Coupons and Gift Cards.
		$this->setup_coupon_restrictions();

		// Init runtime cache.
		$this->notices   = array();
		$this->giftcards = array(
			'applied' => array(
				'giftcards'    => array(),
				'total_amount' => 0.0,
			),
			'account' => array(
				'giftcards'    => array(),
				'total_amount' => 0.0,
			),
		);
		$this->totals    = array(
			'cart_total'        => 0,
			'remaining_total'   => 0,
			'total_for_balance' => 0,
			'available_total'   => 0,
			'pending_total'     => 0,
		);
	}

	/**
	 * Add Store API validation hooks.
	 *
	 * @since 2.4.0
	 */
	public function add_store_api_validation_hooks(): void {
		if ( WC_GC_Core_Compatibility::is_store_api_request() ) {
			add_action( 'woocommerce_store_api_validate_add_to_cart', array( $this, 'validate_add_to_cart_store_api' ), 10, 2 );
		}
	}

	/**
	 * Handle Store API add to cart request.
	 *
	 * @since 2.4.0
	 *
	 * @param array           $add_to_cart_data Add to cart data.
	 * @param WP_REST_Request $wp_rest_request WP REST Request.
	 *
	 * @throws RouteException When add to cart isn't valid.
	 *
	 * @return array
	 */
	public function handle_store_api_add_to_cart_request( array $add_to_cart_data, WP_REST_Request $wp_rest_request ): array {
		$params = $wp_rest_request->get_json_params();

		if ( ! isset( $params['gift_card_configuration'] ) ) {
			return $add_to_cart_data;
		}

		$send_as_gift = wc_gc_sag_get_send_as_gift_status( wc_get_product( $params['id'] ) );

		$gift_card_configuration = $params['gift_card_configuration'];

		if ( 'never' === $send_as_gift ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Exception message is escaped later.
			throw new RouteException( 'woocommerce_rest_cart_invalid_configuration', __( 'This gift card cannot be sent as a gift, therefore, no gift_card_configuration should be provided.', 'woocommerce-gift-cards' ) );
		}

		if ( 'maybe' === $send_as_gift && ( ! isset( $gift_card_configuration['send_as_gift'] ) || true !== $gift_card_configuration['send_as_gift'] ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Exception message is escaped later.
			throw new RouteException( 'woocommerce_rest_cart_invalid_configuration', __( 'To send this gift card as a gift, the send_as_gift parameter should be true.', 'woocommerce-gift-cards' ) );
		}

		$this->map_gift_card_config_to_cart_item_data( $gift_card_configuration, $add_to_cart_data['cart_item_data'] );
		return $add_to_cart_data;
	}

	/**
	 * Map gift card configuration to cart item data.
	 *
	 * @since 2.4.0
	 *
	 * @param array $gift_card_configuration Gift card configuration.
	 * @param array $cart_item_data Cart item data.
	 */
	private function map_gift_card_config_to_cart_item_data( array &$gift_card_configuration, array &$cart_item_data ) {
		if ( isset( $gift_card_configuration['code'] ) ) {
			$cart_item_data['wc_gc_giftcard_code'] = $gift_card_configuration['code'];
		}
		if ( isset( $gift_card_configuration['send_as_gift'] ) ) {
			$cart_item_data['wc_gc_send_as_gift_checkbox'] = true === $gift_card_configuration['send_as_gift'] ? 'on' : 'off';
		}
		if ( isset( $gift_card_configuration['to'] ) ) {
			$cart_item_data['wc_gc_giftcard_to'] = $gift_card_configuration['to'];
		}
		if ( isset( $gift_card_configuration['to_multiple'] ) ) {
			$cart_item_data['wc_gc_giftcard_to_multiple'] = $gift_card_configuration['to_multiple'];
		}
		if ( isset( $gift_card_configuration['cc'] ) ) {
			$cart_item_data['wc_gc_giftcard_cc'] = $gift_card_configuration['cc'];
		}
		if ( isset( $gift_card_configuration['from'] ) ) {
			$cart_item_data['wc_gc_giftcard_from'] = $gift_card_configuration['from'];
		}
		if ( isset( $gift_card_configuration['message'] ) ) {
			$cart_item_data['wc_gc_giftcard_message'] = $gift_card_configuration['message'];
		}
		if ( isset( $gift_card_configuration['delivery_date'] ) ) {
			$date                                      = new WC_DateTime( $gift_card_configuration['delivery_date'] );
			$cart_item_data['wc_gc_giftcard_delivery'] = $date->getTimestamp();
			$cart_item_data['_wc_gc_giftcard_delivery_gmt_offset'] = $date->getOffset();

		}
	}

	/**
	 * Validate add to cart action for Store API. Throws an exception if add to cart isn't valid.
	 *
	 * @since 2.4.0
	 *
	 * @param WC_Product $product Product being added to cart.
	 * @param array      $request Simple array with id, quantity, variation and cart_item_data keys. Not WP_REST_Request.
	 *
	 * @return void
	 * @throws RouteException When add to cart isn't valid.
	 */
	public function validate_add_to_cart_store_api( WC_Product $product, array $request ): void {
		try {
			if ( ! WC_GC_Gift_Card_Product::is_gift_card( $product ) ) {
				return;
			}
			$cart_item_data = $request['cart_item_data'] ?? array();

			$send_as_gift = wc_gc_sag_get_send_as_gift_status( $product );

			if ( 'always' === $send_as_gift || ( 'maybe' === $send_as_gift && isset( $cart_item_data['wc_gc_send_as_gift_checkbox'] ) && 'on' === $cart_item_data['wc_gc_send_as_gift_checkbox'] ) ) {
				WC_GC_Gift_Card_Product::validate_form_fields( $cart_item_data, $product );
			}
		} catch ( Exception $e ) {
			throw new RouteException( 'woocommerce_rest_cart_invalid_gift_card', esc_html( $e->getMessage() ) );
		}
	}

	/**
	 * Sets whether to use balance through the Checkout context.
	 *
	 * @param  string $post_data
	 * @return void
	 */
	public function update_order_review( $post_data ) {

		if ( ! wc_gc_is_ui_disabled() ) {
			return;
		}

		parse_str( $post_data, $post );

		// Use balance?
		$use = isset( $post['use_gift_card_balance'] ) && 'on' === $post['use_gift_card_balance'] ? true : false;
		if ( WC_GC()->account->use_balance() !== $use ) {
			WC_GC()->account->set_balance_usage( $use );
		}

		// Remove Gift Card via checkout.
		if ( ! empty( $post['wc_gc_cart_remove_giftcards'] ) ) {

			$remove_id = absint( $post['wc_gc_cart_remove_giftcards'] );
			WC_GC()->giftcards->remove_giftcard_from_session( $remove_id );
		}
	}

	/**
	 * Calculate totals using active Gift Cards.
	 *
	 * @since 1.14.0
	 *
	 * @param  WC_Cart $cart (Optional).
	 * @return void
	 */
	public function calculate_totals( $cart = null ) {

		if ( empty( $cart ) ) {
			$cart = WC()->cart;
		}

		$this->totals['cart_total']      = $cart->get_total( 'edit' );
		$this->totals['remaining_total'] = $this->totals['cart_total'];

		// Giftcards via form?
		$this->giftcards['applied'] = array(
			'giftcards'    => array(),
			'total_amount' => 0.0,
		);

		$applied_giftcards = WC_GC()->giftcards->get_applied_giftcards_from_session();
		if ( $applied_giftcards ) {

			$this->giftcards['applied']      = WC_GC()->giftcards->cover_balance( $this->totals['remaining_total'], $applied_giftcards );
			$this->totals['remaining_total'] = $this->totals['remaining_total'] - (float) $this->giftcards['applied']['total_amount'];
		}

		// Sanity.
		$this->totals['remaining_total'] = max( 0, $this->totals['remaining_total'] );
		// Cache the remaining total to be covered by account balance.
		$this->totals['total_for_balance'] = $this->totals['remaining_total'];

		// Account balance?
		$this->giftcards['account'] = array(
			'giftcards'    => array(),
			'total_amount' => 0.0,
		);

		if ( wc_gc_is_redeeming_enabled() ) {
			$account_giftcards = WC_GC()->account->get_active_giftcards_from_session();
			if ( $account_giftcards ) {

				if ( WC_GC()->account->use_balance() ) {

					$this->giftcards['account']      = WC_GC()->giftcards->cover_balance( $this->totals['remaining_total'], $account_giftcards );
					$this->totals['remaining_total'] = $this->totals['remaining_total'] - (float) $this->giftcards['account']['total_amount'];
				}

				// Calculate pending balance in totals.
				$this->totals['pending_total'] = 0;
				foreach ( $account_giftcards as $account_giftcard ) {
					if ( $account_giftcard->get_pending_balance() ) {
						$this->totals['pending_total'] += $account_giftcard->get_pending_balance();
					}
				}
			}
		}

		// Change the Cart total. Taxes already included in the Gift Card amount.
		$cart->set_total( max( 0, $this->totals['remaining_total'] ) );

		// Calculate available amount.
		$this->totals['available_total'] = min( $this->totals['total_for_balance'], WC_GC()->account->get_balance() );
	}

	/**
	 * Calculate totals using active Gift Cards.
	 *
	 * @param  WC_Cart $cart
	 * @return void
	 */
	public function after_calculate_totals( $cart ) {

		if ( ! wc_gc_is_ui_disabled() ) {
			return;
		}

		if ( ! WC()->session ) {
			return;
		}

		if ( method_exists( WC()->session, 'has_session' ) && ! WC()->session->has_session() ) {
			return;
		}

		if ( property_exists( $cart, 'recurring_cart_key' ) ) {
			return;
		}

		if ( WC_GC_Compatibility::has_cart_totals_loop() ) {
			return;
		}

		if ( ! (bool) apply_filters( 'woocommerce_gc_cart_needs_calculation', true ) ) {
			return;
		}

		// Reset session.
		WC()->session->set( '_wc_gc_giftcards', null );

		// If Giftcards exists, quit.
		if ( $this->cart_contains_gift_card() ) {
			return;
		}

		$this->calculate_totals( $cart );

		// Cache calculated giftcards.
		WC()->session->set( '_wc_gc_giftcards', array_merge( $this->giftcards['applied']['giftcards'], $this->giftcards['account']['giftcards'] ) );
	}

	/**
	 * Remove applied GC from session.
	 *
	 * @return void
	 */
	public function destroy_cart_session() {
		WC()->session->set( WC_Cache_Helper::get_transient_version( 'applied_giftcards' ) . '_wc_gc_applied_giftcards', null );
		// Reset session.
		WC()->session->set( '_wc_gc_giftcards', null );
	}

	/**
	 * Includes Gift Cards in cart hash.
	 *
	 * @since  1.5.4
	 *
	 * @param  string $hash
	 * @param  array  $cart_session
	 * @return string
	 */
	public function handle_cart_hash( $hash, $cart_session ) {
		$giftcards = WC_GC()->giftcards->get();
		if ( empty( $giftcards ) ) {
			return $hash;
		}

		// Flatten array.
		$hashed_giftcards = array();
		foreach ( $giftcards as $giftcard_info ) {
			$hashed_giftcards[ $giftcard_info['giftcard']->get_code() ] = $giftcard_info['amount'];
		}

		$hash = $hash ? md5( $hash . wp_json_encode( $hashed_giftcards ) ) : '';

		return $hash;
	}

	/**
	 * Print Gift Card table rows.
	 *
	 * @return void
	 */
	public function print_gift_cards() {

		if ( ! wc_gc_is_ui_disabled() ) {
			return;
		}

		/**
		 * Introduce filter to modify template arguments for gift cards in cart.
		 *
		 * @since 1.16.10
		 *
		 * @param  array  $args  The template arguments.
		 * @return array
		 */
		$args = (array) apply_filters(
			'woocommerce_gc_cart_gift_cards_template_args',
			array(
				'giftcards'   => $this->giftcards,
				'totals'      => $this->totals,
				'balance'     => WC_GC()->account->get_balance(),
				'use_balance' => WC_GC()->account->use_balance(),
				'has_balance' => WC_GC()->account->has_balance(),
			),
			$this
		);

		wc_get_template(
			'cart/cart-gift-cards.php',
			$args,
			false,
			WC_GC()->get_plugin_path() . '/templates/'
		);
	}

	/**
	 * Display form to add gift card.
	 *
	 * @return void
	 */
	public function display_form() {

		if ( ! wc_gc_is_ui_disabled() ) {
			return;
		}

		if ( $this->cart_contains_gift_card() ) {
			return;
		}

		$button_class = implode(
			' ',
			array_filter(
				array(
					'button',
					wc_gc_wp_theme_get_element_class_name( 'button' ),
				)
			)
		);

		// Load template.
		wc_get_template(
			'cart/apply-gift-card-form.php',
			(array) apply_filters( 'woocommerce_gc_apply_gift_card_form_template_args', array( 'button_class' => $button_class ), $this ),
			false,
			WC_GC()->get_plugin_path() . '/templates/'
		);
	}

	/**
	 * Display gift card related notices.
	 *
	 * @since  1.3.5
	 *
	 * @return void
	 */
	public function display_notices() {

		if ( ! empty( $this->notices ) ) {
			foreach ( $this->notices as $notice ) {
				if ( empty( $notice['type'] ) ) {
					$notice['type'] = 'notice';
				}
				wc_print_notice( $notice['text'], $notice['type'] );
			}
		}
	}

	/**
	 * Get all applied gift cards.
	 *
	 * @since 1.2.0
	 *
	 * @return array {
	 *   giftcards  array An array with all the giftcard objects.
	 *   total_amount  float The total amount covered.
	 * }
	 */
	public function get_applied_gift_cards() {
		return $this->giftcards['applied'];
	}

	/**
	 * Get all account-related applied gift cards.
	 *
	 * @since 1.2.0
	 *
	 * @return array {
	 *   giftcards  array An array with all the giftcard objects.
	 *   total_amount  float The total amount covered.
	 * }
	 */
	public function get_account_gift_cards() {
		return $this->giftcards['account'];
	}

	/**
	 * Gets the totals breakdown for account balance.
	 *
	 * @since 1.2.0
	 *
	 * @return array
	 */
	public function get_account_totals_breakdown() {
		return $this->totals;
	}

	/**
	 * Check if cart contains giftcards.
	 *
	 * @return bool
	 */
	public function cart_contains_gift_card() {

		$contains = false;

		foreach ( WC()->cart->cart_contents as $cart_item_key => $cart_item ) {
			if ( is_a( $cart_item['data'], 'WC_Product' ) && WC_GC_Gift_Card_Product::is_gift_card( $cart_item['data'] ) ) {
				$contains = true;
				break;
			}
		}

		return (bool) apply_filters( 'woocommerce_gc_cart_contains_gift_card', $contains );
	}

	/**
	 * Process front-end Gift Card cart form.
	 *
	 * @param  array $args
	 * @return bool
	 */
	public function process_gift_card_cart_form( $args ) {

		if ( ! wc_gc_is_ui_disabled() ) {
			return false;
		}

		if ( $this->cart_contains_gift_card() ) {
			$this->notices[] = array(
				'text' => __( 'Gift cards cannot be purchased using other gift cards.', 'woocommerce-gift-cards' ),
				'type' => 'notice',
			);
			return false;
		}

		if ( ! empty( $args ) && isset( $args['wc_gc_cart_code'] ) ) {

			$code = wc_clean( $args['wc_gc_cart_code'] );

			if ( empty( $code ) ) {
				$this->notices[] = array(
					'text' => __( 'Please enter your gift card code.', 'woocommerce-gift-cards' ),
					'type' => 'notice',
				);
				return false;
			} elseif ( strlen( $code ) !== 19 ) {
				$this->notices[] = array(
					'text' => __( 'Please enter a gift card code that follows the format XXXX-XXXX-XXXX-XXXX, where X can be any letter or number.', 'woocommerce-gift-cards' ),
					'type' => 'notice',
				);
				return false;
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

				try {

					// If logged in check if auto-redeem is on.
					if ( get_current_user_id() && apply_filters( 'woocommerce_gc_auto_redeem', false ) ) {
						$giftcard->redeem( get_current_user_id() );
					} else {
						WC_GC()->giftcards->apply_giftcard_to_session( $giftcard );
					}

					$this->notices[] = array(
						'text' => __( 'Gift card code applied successfully!', 'woocommerce-gift-cards' ),
						'type' => 'success',
					);

					return true;

				} catch ( Exception $e ) {
					$this->notices[] = array(
						'text' => $e->getMessage(),
						'type' => 'error',
					);
				}
			} else {
				$this->notices[] = array(
					'text' => __( 'Gift card not found.', 'woocommerce-gift-cards' ),
					'type' => 'error',
				);
			}
		}

		return false;
	}

	/**
	 * Process the email link for session if any.
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	public function maybe_process_email_session() {

		if ( isset( $_GET['do_email_session'] ) ) {

			/**
			 * `woocommerce_gc_do_email_session_url` filter.
			 *
			 * @since 1.9.0
			 *
			 * @param  string  $url
			 * @return string
			 */
			$base_url = apply_filters( 'woocommerce_gc_do_email_session_url', add_query_arg( array( 'gc_email_session_handle' => microtime() ), remove_query_arg( array( 'do_email_session', 'giftcard_id' ) ) ) );

			if ( apply_filters( 'woocommerce_gc_disable_email_session', false ) ) {
				wp_safe_redirect( $base_url );
				exit;
			}

			if ( ! WC()->session->has_session() ) {
				// Generate a random customer ID.
				WC()->session->set_customer_session_cookie( true );
			}

			$requested_giftcard = isset( $_GET['giftcard_id'] ) ? absint( $_GET['giftcard_id'] ) : 0;
			$hash               = ! empty( $_GET['do_email_session'] ) ? sanitize_text_field( wp_unslash( $_GET['do_email_session'] ) ) : '';

			// Check for backwards compatibility hashes lt 1.9.0.
			// Hint: Previous hash requests didn't include a 'giftcard_id' param.
			$is_legacy_request = empty( $requested_giftcard );

			// Validate gift card.
			if ( $is_legacy_request ) {

				$current_giftcard = WC_GC()->db->giftcards->get_by_hash( $hash );

				// If gift card has one or more new meta data and still uses the legacy way, then it's probably a "bad" or invalid link.
				if ( ! $current_giftcard || $current_giftcard->get_meta( '_hash_iv' ) || $current_giftcard->get_meta( '_hash_key' ) ) {
					// Reset for safety.
					$current_giftcard = false;
				}
			} else {

				$current_giftcard = wc_gc_get_gift_card( $requested_giftcard );
				$hash_to_check    = urldecode( base64_decode( $hash ) );
				if ( ! $current_giftcard || ! $current_giftcard->validate_hash( $hash_to_check ) ) {
					// Reset for safety.
					$current_giftcard = false;
				}
			}

			if ( ! is_a( $current_giftcard, 'WC_GC_Gift_Card_Data' ) ) {
				wc_add_notice( esc_html__( 'Invalid request. Please try again&hellip;', 'woocommerce-gift-cards' ), 'error' );
				wp_safe_redirect( $base_url );
				exit;
			}

			// Try to make brute-force attacks inefficient.
			sleep( 2 );

			// Decorate.
			$giftcard = new WC_GC_Gift_Card( $current_giftcard );
			try {

				/**
				 * Filter to determine if the gift card should be auto-redeemed.
				 *
				 * @since 1.0.0
				 * @since 2.7.2 Added $giftcard parameter.
				 *
				 * @param bool $auto_redeem Whether to auto-redeem the gift card.
				 * @param WC_GC_Gift_Card $giftcard The gift card object.
				 * @return bool
				*/
				if ( get_current_user_id() && (bool) apply_filters( 'woocommerce_gc_auto_redeem', false, $giftcard ) ) {
					$giftcard->redeem( get_current_user_id() );
				} else {
					WC_GC()->giftcards->apply_giftcard_to_session( $giftcard );
				}

				/* translators: Gift Card code */
				wc_add_notice( sprintf( __( 'Gift card \'%s\' applied successfully!', 'woocommerce-gift-cards' ), $giftcard->get_code() ), 'success' );

			} catch ( Exception $e ) {
				wc_add_notice( $e->getMessage(), 'error' );
			}

			wp_safe_redirect( $base_url );
			exit;
		}
	}

	/*
	---------------------------------------------------*/
	/*
		Coupons.                                         */
	/*---------------------------------------------------*/

	/**
	 * Setup all hooks for coupon usage restrictions.
	 *
	 * @since 1.7.0
	 *
	 * @return void
	 */
	protected function setup_coupon_restrictions() {

		if ( 'yes' !== get_option( 'wc_gc_disable_coupons_with_gift_cards', 'no' ) ) {
			return;
		}

		add_filter( 'woocommerce_coupon_is_valid_for_product', array( $this, 'coupon_is_valid_for_product' ), 10, 2 );
		add_filter( 'woocommerce_coupon_is_valid', array( $this, 'coupon_is_valid' ), 10, 2 );
	}

	/**
	 * Disable coupons for gift card products.
	 *
	 * @since 1.7.0
	 *
	 * @param  bool       $is_valid
	 * @param  WC_Product $product
	 * @return bool
	 */
	public function coupon_is_valid_for_product( $is_valid, $product ) {
		if ( $is_valid && WC_GC_Gift_Card_Product::is_gift_card( $product ) ) {
			$is_valid = false;
		}

		return $is_valid;
	}

	/**
	 * Invalidate coupons when used with gift card products.
	 *
	 * @since 1.7.0
	 *
	 * @param  bool      $is_valid
	 * @param  WC_Coupon $coupon
	 * @return bool
	 */
	public function coupon_is_valid( $is_valid, $coupon ) {

		if ( $is_valid ) {

			switch ( $coupon->get_discount_type() ) {
				case 'percent':
					// @see __CLASS__::coupon_is_valid_for_product.
					break;
				case 'fixed_product':
					// @see __CLASS__::coupon_is_valid_for_product.
					break;
				case 'fixed_cart':
					if ( $this->cart_contains_gift_card() ) {
						throw new Exception( __( 'Sorry, this coupon is not applicable to gift card products.', 'woocommerce-gift-cards' ) );
					}
					break;
			}
		}

		return $is_valid;
	}
}
