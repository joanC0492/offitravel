<?php
/**
 * WC_GC_FS_Compatibility class
 *
 * @package  WooCommerce Gift Cards
 * @since    1.5.2
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Flatsome integration.
 *
 * @version  1.16.9
 */
class WC_GC_FS_Compatibility {

	public static function init() {
		// Add hooks if the active parent theme is Flatsome.
		add_action( 'after_setup_theme', array( __CLASS__, 'maybe_add_hooks' ) );
	}

	/**
	 * Add hooks if the active parent theme is Flatsome.
	 */
	public static function maybe_add_hooks() {

		if ( function_exists( 'flatsome_quickview' ) ) {
			// Initialize gift cards in quick view modals.
			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'add_quickview_integration' ), 999 );
		}
	}

	/**
	 * Initializes gift cards in quick view modals.
	 *
	 * @return array
	 */
	public static function add_quickview_integration() {

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_style( 'wc-gc-css' );
		WC_GC()->templates->enqueue_scripts();
		wp_register_script( 'wc-gc-flatsome-quickview', WC_GC()->get_plugin_url() . '/assets/js/frontend/integrations/wc-gc-flatsome-quickview' . $suffix . '.js', array( 'jquery', 'jquery-ui-datepicker', 'wc-gc-main' ), WC_GC()->get_plugin_version(), true );
		wp_script_add_data( 'wc-gc-flatsome-quickview', 'strategy', 'defer' );
		wp_enqueue_script( 'wc-gc-flatsome-quickview' );
	}
}

WC_GC_FS_Compatibility::init();
