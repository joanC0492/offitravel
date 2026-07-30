<?php
/**
 * Glocal-scope Send As Gift functions
 *
 * @since    1.9.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gets the value of the "Is Gift Card for a gift?" admin setting.
 *
 * @param  WC_Product|null
 * @return string
 */
function wc_gc_sag_get_send_as_gift_status( $product = null ) {

	$option_value = get_option( 'wc_gc_settings_send_as_gift_status', 'always' );

	if ( isset( $product ) && is_a( $product, 'WC_Product' ) ) {
		$option_value = apply_filters( 'woocommerce_gc_product_send_as_gift', $option_value, $product );
	}

	return $option_value;
}
