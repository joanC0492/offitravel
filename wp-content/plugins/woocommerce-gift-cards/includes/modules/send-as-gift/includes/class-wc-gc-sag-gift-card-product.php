<?php
/**
 * WC_GC_SAG_Gift_Card_Product class
 *
 * @since    1.9.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gift Card - Send As Gift product manager.
 *
 * @class    WC_GC_SAG_Gift_Card_Product
 * @version  2.7.2
 */
class WC_GC_SAG_Gift_Card_Product {

	/**
	 * Constructor.
	 */
	public static function init() {

		/*
		* Single product page.
		*
		*/

		// Conditionally hide Gift Cards form, when "Send as Gift?" is set to never.
		add_action( 'woocommerce_before_add_to_cart_button', array( __CLASS__, 'maybe_hide_cards_form' ), 5 );
		add_action( 'woocommerce_before_single_variation', array( __CLASS__, 'maybe_hide_cards_form' ), 5 );

		// Display a checkbox, when "Send as Gift?" is set to "Let customers decide".
		add_action( 'woocommerce_gc_before_form', array( __CLASS__, 'maybe_print_send_as_a_gift_checkbox' ), 9 );

		// Conditionally show/hide the Gift Cards form based on the value of the "Send as Gift" checkbox.
		add_action( 'wp_footer', array( __CLASS__, 'toggle_gift_card_form' ) );

		// Remove Gift Cards default validation and add a new one.
		remove_action( 'woocommerce_add_to_cart_validation', array( 'WC_GC_Gift_Card_Product', 'validate_add_to_cart' ), 10, 6 );
		add_filter( 'woocommerce_add_to_cart_validation', array( __CLASS__, 'validate_add_to_cart' ), 10, 6 );

		/*
		* Cart/Checkout.
		*
		*/

		// Clear Gift Cards data when they are not a gift.
		add_filter( 'woocommerce_add_cart_item_data', array( __CLASS__, 'update_gift_card_item_data' ), 15, 3 );

		// Update the cart item permalink to conditionally include the value of the "Send as Gift?" checkbox.
		add_filter( 'woocommerce_cart_item_permalink', array( __CLASS__, 'cart_item_permalink' ), 11, 3 );

		// Update the order item permalink to conditionally include the value of the "Send as Gift?" checkbox.
		add_filter( 'woocommerce_order_item_permalink', array( __CLASS__, 'order_item_permalink' ), 9, 3 );

		// Update cart item meta in 'order-again' context.
		add_action( 'woocommerce_order_again_cart_item_data', array( __CLASS__, 'update_gift_card_order_again_item_data' ), 11, 3 );

		// Handle guests who buy a Gift Card for themselves.
		add_action( 'woocommerce_checkout_create_order_line_item', array( __CLASS__, 'get_customer_details_from_order' ), 11, 4 );
		add_action( 'woocommerce_store_api_checkout_update_order_meta', array( __CLASS__, 'rest_checkout_update_order_meta_recipient_is_buyer' ) );

		// Hide SAG specific order item meta.
		add_filter( 'woocommerce_hidden_order_itemmeta', array( __CLASS__, 'order_item_hide_sag_meta' ) );
	}

	/**
	 * Conditionally hide Gift Cards form, when "Send as Gift?" is set to never.
	 */
	public static function maybe_hide_cards_form() {

		if ( ! is_product() ) {
			return;
		}

		global $product;

		if ( ! is_a( $product, 'WC_Product' ) ) {
			return;
		}

		if ( ! WC_GC_Gift_Card_Product::is_gift_card( $product ) ) {
			return;
		}

		$send_as_gift = wc_gc_sag_get_send_as_gift_status( $product );

		// Hide Gift Cards form if Gift Cards can never be bought as a gift.
		if ( 'never' === $send_as_gift ) {

			if ( $product->is_type( 'simple' ) ) {
				remove_action( 'woocommerce_before_add_to_cart_button', array( 'WC_GC_Gift_Card_Product', 'handle_simple_gift_card_form' ), 9 );
			} elseif ( $product->is_type( 'variable' ) ) {
				remove_action( 'woocommerce_before_single_variation', array( 'WC_GC_Gift_Card_Product', 'handle_variable_gift_card_form' ), 9 );
			}
		}
	}

	/**
	 * Display a checkbox, when "Send as Gift?" is set to "Let customers decide".
	 */
	public static function maybe_print_send_as_a_gift_checkbox() {

		if ( ! is_product() ) {
			return;
		}

		global $product;

		if ( ! is_a( $product, 'WC_Product' ) ) {
			return;
		}

		if ( ! WC_GC_Gift_Card_Product::is_gift_card( $product ) ) {
			return;
		}

		$send_as_gift = wc_gc_sag_get_send_as_gift_status( $product );

		// Add "Send as Gift" checkbox.
		if ( 'maybe' === $send_as_gift ) {

			self::print_send_as_a_gift_checkbox( $product );

			// Add wrapper around the Gift Cards form.
			self::add_form_container_pre();

			add_action( 'woocommerce_gc_after_form', array( __CLASS__, 'add_form_container_after' ) );
		}
	}

	/**
	 * Print "Send as Gift" checkbox.
	 *
	 * @param  WC_Product $product
	 */
	public static function print_send_as_a_gift_checkbox( $product ) {

		$is_checked = false;

		if ( isset( $_REQUEST['wc_gc_send_as_gift_checkbox'] ) && 'on' === $_REQUEST['wc_gc_send_as_gift_checkbox'] ) {
			$is_checked = true;
		}

		wc_get_template(
			'gift-card-send-as-gift-checkbox.php',
			array( 'is_checked' => $is_checked ),
			false,
			WC_GC_ABSPATH . 'includes/modules/send-as-gift/templates/'
		);
	}

	/**
	 * Open wrapper before the Gift Cards form.
	 */
	public static function add_form_container_pre() {
		echo "<div class='woocommerce_gc_giftcard_form_wrapper' style='display:none;'>";
	}

	/**
	 * Close wrapper after the Gift Cards form.
	 */
	public static function add_form_container_after() {
		echo '</div>';
		echo "<input class='wc_gc_sag_checkbox' name='wc_gc_sag_checkbox' value='posted' style='display:none'></input>";
	}

	/**
	 * Conditionally show/hide the Gift Cards form based on the value of the "Send as Gift" checkbox.
	 */
	public static function toggle_gift_card_form() {

		if ( ! is_product() ) {
			return;
		}

		global $product;

		if ( ! is_a( $product, 'WC_Product' ) ) {
			return;
		}

		if ( ! WC_GC_Gift_Card_Product::is_gift_card( $product ) ) {
			return;
		}

		$handle = 'wc-gc-gift-form-toggle';
		wp_register_script( $handle, '', array( 'jquery' ), false, true ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NoExplicitVersion
		wp_enqueue_script( $handle );
		wp_add_inline_script(
			$handle,
			'
			( function( $ ) {
				$( function() {

					var $checkbox = $( "#wc_gc_send_as_gift_checkbox" ),
						$gc_form  = $( ".woocommerce_gc_giftcard_form_wrapper" );

					var toggle_form = function( $checkbox, $gc_form ) {
						if ( $checkbox.is( ":checked" ) ) {
							$gc_form.show();
						} else {
							$gc_form.hide();
						}
					}

					$checkbox.on( "click", function(){
						toggle_form( $checkbox, $gc_form );
					});

					toggle_form( $checkbox, $gc_form );
				} );
			} )( jQuery );
		'
		);
	}

	/**
	 * Validate add-to-cart action.
	 *
	 * @param  boolean $passed
	 * @param  int     $product_id
	 * @param  int     $quantity
	 * @param  mixed   $variation_id
	 * @param  array   $variations
	 * @param  array   $cart_item_data
	 * @return boolean
	 */
	public static function validate_add_to_cart( $passed, $product_id, $quantity, $variation_id = '', $variations = array(), $cart_item_data = array() ) {

		// This needs to stay here to support WC core < 9.5. See https://github.com/woocommerce/woocommerce/pull/52486.
		if ( WC_GC_Core_Compatibility::is_store_api_request() ) {
			return $passed;
		}

		if ( ! $passed ) {
			return false;
		}

		$product = wc_get_product( $product_id );

		if ( ! WC_GC_Gift_Card_Product::is_gift_card( $product ) ) {
			return $passed;
		}

		$send_as_gift = wc_gc_sag_get_send_as_gift_status( $product );

		// Throw an error if our hidden input field is not posted to the cart. This indicates that the GC form fields could not be posted either.
		if ( ! isset( $_GET['order_again'] ) && 'maybe' === $send_as_gift && ! isset( $_POST['wc_gc_sag_checkbox'] ) ) {

			wc_add_notice( __( 'The gift card could not be added to the cart. Some required data is missing.', 'woocommerce-gift-cards' ), 'error' );

			if ( current_user_can( 'administrator' ) ) {
				wc_add_notice( __( '<i>For administrators:</i> This may indicate that a plugin or theme is preventing Gift Cards from posting form data. Please consider switching off any custom add-to-cart code or buttons.', 'woocommerce-gift-cards' ), 'notice' );
			}

			return false;
		}

		// If the Gift Card is sent as a gift, then fall back to the GC core add to cart validation.
		if ( 'always' === $send_as_gift || ( 'maybe' === $send_as_gift && isset( $_POST['wc_gc_send_as_gift_checkbox'] ) && 'on' === $_POST['wc_gc_send_as_gift_checkbox'] ) ) {
			$passed = WC_GC_Gift_Card_Product::validate_add_to_cart( $passed, $product_id, $quantity, $variation_id, $variations, $cart_item_data );
		}

		return $passed;
	}

	/**
	 * Change cart item permalink.
	 *
	 * @param  string $link
	 * @param  array  $cart_item
	 * @param  string $cart_item_key
	 * @return string
	 */
	public static function cart_item_permalink( $link, $cart_item, $cart_item_key ) {

		if ( ! WC_GC_Gift_Card_Product::is_gift_card( $cart_item['data'] ) ) {
			return $link;
		}

		if ( ! empty( $link ) ) {
			if ( ! isset( $cart_item['_wc_gc_recipient_is_buyer'] ) || 'yes' !== $cart_item['_wc_gc_recipient_is_buyer'] ) {
				$link = add_query_arg( 'wc_gc_send_as_gift_checkbox', 'on', $link );
			}
		}

		return $link; // nosemgrep: audit.php.wp.security.xss.query-arg
	}

	/**
	 * Clear cart item data for users who buy a Gift Card for themselves.
	 *
	 * @param  array $cart_item_data
	 * @param  int   $product_id
	 * @param  int   $variation_id
	 *
	 * @return array
	 */
	public static function update_gift_card_item_data( $cart_item_data, $product_id, $variation_id ) {

		if ( ! empty( $product_id ) ) {
			$product = wc_get_product( $product_id );
		}

		if ( ! isset( $product ) || ! is_a( $product, 'WC_Product' ) ) {
			return $cart_item_data;
		}

		if ( ! WC_GC_Gift_Card_Product::is_gift_card( $product ) ) {
			return $cart_item_data;
		}

		// Do not add cart item data if Gift Cards are always bought as gifts.
		$send_as_gift = wc_gc_sag_get_send_as_gift_status( $product );

		if ( 'always' === $send_as_gift ) {
			return $cart_item_data;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( 'maybe' === $send_as_gift && ( isset( $_POST['wc_gc_send_as_gift_checkbox'] ) || isset( $cart_item_data['wc_gc_send_as_gift_checkbox'] ) ) ) {
			return $cart_item_data;
		}

		foreach ( WC_GC_Gift_Card_Product::get_form_fields() as $key => $label ) {
			if ( isset( $cart_item_data[ $key ] ) ) {
				unset( $cart_item_data[ $key ] );
			}
		}

		$cart_item_data['_wc_gc_recipient_is_buyer'] = 'yes';

		return $cart_item_data;
	}

	/**
	 * Update cart item meta in 'order-again' context.
	 *
	 * @param  array                 $cart_item_data
	 * @param  WC_Order_Item_Product $order_item
	 * @param  WC_Order              $order
	 * @return array
	 */
	public static function update_gift_card_order_again_item_data( $cart_item_data, $order_item, $order ) {

		$recipient_is_buyer = $order_item->get_meta( '_wc_gc_recipient_is_buyer', true );

		if ( 'yes' === $recipient_is_buyer ) {
			foreach ( WC_GC_Gift_Card_Product::get_form_fields() as $key => $label ) {
				if ( isset( $cart_item_data[ $key ] ) ) {
					unset( $cart_item_data[ $key ] );
				}
			}

			$cart_item_data['_wc_gc_recipient_is_buyer'] = 'yes';
		}

		return $cart_item_data;
	}

	/**
	 * Handle guests who buy a Gift Card for themselves.
	 *
	 * @param  WC_Order_Item $order_item
	 * @param  string        $cart_item_key
	 * @param  array         $cart_item
	 * @param  WC_Order      $order
	 * @return void
	 */
	public static function get_customer_details_from_order( $order_item, $cart_item_key, $cart_item, $order ) {

		$product = $cart_item['data'];

		if ( ! isset( $cart_item['_wc_gc_recipient_is_buyer'] ) || 'yes' !== $cart_item['_wc_gc_recipient_is_buyer'] ) {
			return;
		}

		if ( ! WC_GC_Gift_Card_Product::is_gift_card( $product ) ) {
			return;
		}

		self::add_order_item_meta_recipient_is_buyer( $order, $order_item );
	}

	/**
	 * Handle guests who buy a Gift Card for themselves (blocks checkout).
	 *
	 * @throws \Automattic\WooCommerce\StoreApi\Exceptions\RouteException
	 *
	 * @param  WC_Order $order
	 * @return void
	 */
	public static function rest_checkout_update_order_meta_recipient_is_buyer( $order ) {

		if ( ! WC_GC_Core_Compatibility::is_store_api_request( 'checkout', 'POST' ) ) {
			return;
		}

		try {

			$items = $order->get_items();
			if ( empty( $items ) ) {
				return;
			}

			foreach ( $items as $order_item ) {

				if ( ! $order_item->meta_exists( '_wc_gc_recipient_is_buyer' ) ) {
					continue;
				}

				if ( ! WC_GC_Gift_Card_Product::is_gift_card( $order_item->get_product() ) ) {
					continue;
				}

				self::add_order_item_meta_recipient_is_buyer( $order, $order_item );
			}
		} catch ( \Exception $e ) {
			throw new \Automattic\WooCommerce\StoreApi\Exceptions\RouteException( 'woocommerce_gift_cards_invalid_meta', $e->getMessage() );
		}
	}

	/**
	 * Mark SAG meta keys as hidden.
	 *
	 * @param  array $hidden_meta
	 * @return array
	 */
	public static function order_item_hide_sag_meta( $hidden_meta ) {

		$hidden_meta[] = '_wc_gc_recipient_is_buyer';

		return $hidden_meta;
	}

	/**
	 * Change order item permalink.
	 *
	 * @param  string        $link
	 * @param  WC_Order_Item $order_item
	 * @param  WC_Order      $order
	 * @return string
	 */
	public static function order_item_permalink( $link, $order_item, $order ) {

		if ( ! WC_GC_Gift_Card_Product::is_gift_card( $order_item['data'] ) ) {
			return $link;
		}

		if ( ! empty( $link ) ) {
			$recipient_is_buyer = $order_item->get_meta( '_wc_gc_recipient_is_buyer', true );
			if ( ! isset( $recipient_is_buyer ) || 'yes' !== $recipient_is_buyer ) {
				$link = add_query_arg( 'wc_gc_send_as_gift_checkbox', 'on', $link );
			}
		}

		return $link; // nosemgrep: audit.php.wp.security.xss.query-arg
	}

	/**
	 * Saves order item meta, specific to recipient is buyer.
	 *
	 * @param  WC_Order      $order
	 * @param  WC_Order_Item $order_item
	 * @return void
	 */
	private static function add_order_item_meta_recipient_is_buyer( $order, $order_item ) {

		$order_item->add_meta_data( '_wc_gc_recipient_is_buyer', 'yes', true );

		if ( $order->get_billing_first_name() && $order->get_billing_last_name() ) {
			$customer_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
			$order_item->add_meta_data( 'wc_gc_giftcard_from', $customer_name, true );
		}

		if ( $order->get_billing_email() ) {
			$order_item->add_meta_data( 'wc_gc_giftcard_to', (string) $order->get_billing_email(), true );
		}
	}
}

WC_GC_SAG_Gift_Card_Product::init();
