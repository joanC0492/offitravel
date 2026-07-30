<?php
/**
 * WC_GC_WC_Payments_Compatibility class
 *
 * @package  WooCommerce Gift Cards
 * @since    1.10.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooPayments Compatibility.
 *
 * @version  2.0.0
 */
class WC_GC_WC_Payments_Compatibility {

	/**
	 * Initialize integration.
	 */
	public static function init() {
		// Hide express checkout buttons in gift card product pages.
		add_filter( 'wcpay_payment_request_is_product_supported', array( __CLASS__, 'handle_express_checkout_buttons' ), 10, 2 );
		add_filter( 'wcpay_woopay_button_is_product_supported', array( __CLASS__, 'handle_express_checkout_buttons' ), 10, 2 );

		// Update level 3 data with gift card information.
		add_filter( 'wcpay_payment_request_level3_data', array( __CLASS__, 'update_level3_data' ), 10, 2 );
	}

	/**
	 * Hide express checkout buttons in gift card product pages.
	 *
	 * @param  bool       $is_supported
	 * @param  WC_Product $product
	 * @return bool
	 */
	public static function handle_express_checkout_buttons( $is_supported, $product ) {
		if ( false === $is_supported ) {
			return $is_supported;
		}

		if ( WC_GC_Gift_Card_Product::is_gift_card( $product ) && 'never' !== get_option( 'wc_gc_settings_send_as_gift_status', 'always' ) ) {
			$is_supported = false;
		}

		return $is_supported;
	}

	/**
	 * Update level 3 data with gift card information.
	 *
	 * Allows to partially pay an order with a gift card and send the remaining amount to the payment gateway.
	 *
	 * @since 2.0.0
	 *
	 * @param array    $level3_data The level 3 data.
	 * @param WC_Order $order The order object.
	 *
	 * @return array The updated level 3 data.
	 */
	public static function update_level3_data( $level3_data, $order ) {

		return WC_GC_Compatibility::apply_discount_to_level3_line_items( $level3_data, $order, array( 'WC_Payments_Utils', 'prepare_amount' ) );
	}
}

WC_GC_WC_Payments_Compatibility::init();
