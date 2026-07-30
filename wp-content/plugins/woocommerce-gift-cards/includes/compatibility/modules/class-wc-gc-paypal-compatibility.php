<?php
/**
 * WC_GC_PayPal_Compatibility class
 *
 * @package  WooCommerce Gift Cards
 * @since    1.16.9
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PayPal Payments Compatibility.
 *
 * @version  1.16.9
 */
class WC_GC_PayPal_Compatibility {

	/**
	 * Initialize integration.
	 */
	public static function init() {
		// Hide smart buttons in gift card product pages.
		add_filter( 'woocommerce_paypal_payments_product_supports_payment_request_button', array( __CLASS__, 'handle_smart_buttons' ), 10, 2 );
	}

	/**
	 * Hide smart buttons in gift card product pages.
	 *
	 * @param  bool       $is_supported
	 * @param  WC_Product $product
	 * @return bool
	 */
	public static function handle_smart_buttons( $is_supported, $product ) {

		if ( WC_GC_Gift_Card_Product::is_gift_card( $product ) && 'never' !== get_option( 'wc_gc_settings_send_as_gift_status', 'always' ) ) {
			$is_supported = false;
		}

		return $is_supported;
	}
}

WC_GC_PayPal_Compatibility::init();
