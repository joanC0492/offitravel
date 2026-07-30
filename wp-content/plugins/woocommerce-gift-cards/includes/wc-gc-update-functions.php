<?php
/**
 * Update Functions
 *
 * @package  WooCommerce Gift Cards
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Update 1.7.0 db function.
 *
 * @return void
 */
function wc_gc_update_170_main() {
	update_option( 'wc_gc_disable_cart_ui', 'no' );
}

/**
 * Update 1.7.0 db version function.
 *
 * @return void
 */
function wc_gc_update_170_db_version() {
	WC_GC_Install::update_db_version( '1.7.0' );
}
