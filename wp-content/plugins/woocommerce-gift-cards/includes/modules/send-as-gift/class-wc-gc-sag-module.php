<?php
/**
 * WC_GC_SAG_Module class
 *
 * @package  WooCommerce Gift Cards
 * @since    1.9.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Send As Gift Module
 *
 * @class    WC_GC_SAG_Module
 * @version  1.9.0
 */
class WC_GC_SAG_Module extends WC_GC_Abstract_Module {

	/**
	 * Core.
	 */
	public function load_core() {

		// Admin.
		if ( is_admin() ) {
			require_once WC_GC_ABSPATH . 'includes/modules/send-as-gift/includes/admin/class-wc-gc-sag-admin.php';
		}

		// Gift Card.
		require_once WC_GC_ABSPATH . 'includes/modules/send-as-gift/includes/class-wc-gc-sag-gift-card.php';

		// Product.
		require_once WC_GC_ABSPATH . 'includes/modules/send-as-gift/includes/class-wc-gc-sag-gift-card-product.php';

		// E-mails.
		require_once WC_GC_ABSPATH . 'includes/modules/send-as-gift/includes/class-wc-gc-sag-emails.php';

		// Global-scope functions.
		require_once WC_GC_ABSPATH . 'includes/modules/send-as-gift/includes/wc-gc-sag-functions.php';
	}
}
