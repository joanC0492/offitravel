<?php
/**
 * WC_GC_Avatax_Compatibility class
 *
 * @package  WooCommerce Gift Cards
 * @since    1.4.2
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce Avatax Compatibility.
 *
 * @version  1.4.2
 */
class WC_GC_Avatax_Compatibility {

	/**
	 * Initialize integration.
	 */
	public static function init() {
		add_filter( 'woocommerce_gc_cart_needs_calculation', array( __CLASS__, 'needs_calculation' ) );
	}

	/**
	 * Do not calculate Gift Cards while Avatax is reseting the cart.
	 *
	 * @param  bool $needs_calculation
	 * @return bool
	 */
	public static function needs_calculation( $needs_calculation ) {

		if ( did_action( 'wc_avatax_before_checkout_tax_calculated' ) > did_action( 'wc_avatax_after_checkout_tax_calculated' ) ) {
			return false;
		}

		return $needs_calculation;
	}
}

WC_GC_Avatax_Compatibility::init();
