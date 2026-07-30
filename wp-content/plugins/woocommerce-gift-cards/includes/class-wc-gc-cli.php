<?php
/**
 * WC_GC_CLI class
 *
 * @package  WooCommerce Gift Cards
 * @since    1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DB updating and other stuff via WP-CLI.
 *
 * @class    WC_GC_CLI
 * @version  1.0.0
 */
class WC_GC_CLI {

	/**
	 * Load required files and hooks.
	 */
	public function __construct() {
		$this->includes();
		$this->hooks();
	}

	/**
	 * Load command files.
	 */
	private function includes() {
		require_once WC_GC_ABSPATH . 'includes/cli/class-wc-gc-cli-update.php';
	}

	/**
	 * Sets up and hooks WP CLI to our CLI code.
	 */
	private function hooks() {
		WP_CLI::add_hook( 'after_wp_load', 'WC_GC_CLI_Update::register_command' );
	}
}

new WC_GC_CLI();
