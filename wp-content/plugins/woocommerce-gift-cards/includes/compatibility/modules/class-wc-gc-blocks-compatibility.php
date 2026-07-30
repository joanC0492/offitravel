<?php
/**
 * WC_GC_Blocks_Compatibility class
 *
 * @package  WooCommerce Gift Cards
 * @since    1.11.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce Blocks Compatibility.
 *
 * @version 1.11.0
 */
class WC_GC_Blocks_Compatibility {

	/**
	 * Initialize.
	 */
	public static function init() {

		if ( ! did_action( 'woocommerce_blocks_loaded' ) ) {
			return;
		}

		require_once WC_GC_ABSPATH . 'includes/blocks/class-wc-gc-checkout-blocks-endpoint.php';
		require_once WC_GC_ABSPATH . 'includes/blocks/class-wc-gc-checkout-blocks-integration.php';

		WC_GC_Checkout_Blocks_Endpoint::init();

		add_action(
			'woocommerce_blocks_cart_block_registration',
			function ( $registry ) {
				$registry->register( WC_GC_Checkout_Blocks_Integration::instance() );
			}
		);

		add_action(
			'woocommerce_blocks_checkout_block_registration',
			function ( $registry ) {
				$registry->register( WC_GC_Checkout_Blocks_Integration::instance() );
			}
		);
	}
}

WC_GC_Blocks_Compatibility::init();
