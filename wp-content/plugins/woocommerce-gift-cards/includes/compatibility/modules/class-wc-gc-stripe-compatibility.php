<?php
/**
 * WC_GC_Stripe_Compatibility class
 *
 * @package  WooCommerce Gift Cards
 * @since    1.1.5
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce Stripe Gateway Compatibility.
 *
 * @version  2.0.0
 */
class WC_GC_Stripe_Compatibility {

	/**
	 * Initialize integration.
	 */
	public static function init() {
		add_filter( 'wc_stripe_hide_payment_request_on_product_page', array( __CLASS__, 'handle_express_checkout_buttons' ), 11, 2 );

		// Update level 3 data with gift card information.
		add_filter( 'wc_stripe_payment_request_level3_data', array( __CLASS__, 'update_level3_data' ), 10, 2 );
	}

	/**
	 * Hide express checkout buttons on single Product page.
	 *
	 * @param  bool    $hide
	 * @param  WP_Post $post (Optional)
	 * @return bool
	 */
	public static function handle_express_checkout_buttons( $hide, $post = null ) {

		if ( is_null( $post ) ) {
			global $post;
		}

		if ( ! is_object( $post ) || empty( $post->ID ) ) {
			return $hide;
		}

		$product = wc_get_product( $post->ID );
		if ( $product && is_a( $product, 'WC_Product' ) && WC_GC_Gift_Card_Product::is_gift_card( $product ) ) {
			$hide = true;
		}

		return $hide;
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

		return WC_GC_Compatibility::apply_discount_to_level3_line_items( $level3_data, $order, array( 'WC_Stripe_Helper', 'get_stripe_amount' ) );
	}
}

WC_GC_Stripe_Compatibility::init();
