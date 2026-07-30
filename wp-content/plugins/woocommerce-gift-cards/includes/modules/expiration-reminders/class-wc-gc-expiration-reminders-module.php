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
 * Expiration Reminders Module
 *
 * @class    WC_GC_SAG_Module
 * @version  2.2.0
 */
class WC_GC_Expiration_Reminders_Module extends WC_GC_Abstract_Module {

	/**
	 * Core.
	 */
	public function load_core() {
		require_once 'includes/class-wc-gc-expiration-reminders-batch-processor.php';
		require_once 'includes/class-wc-gc-expiration-reminders.php';
	}
}
