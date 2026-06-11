<?php defined( 'ABSPATH' ) || exit();

/**
 * Class OVA_Megamenu_Assets
 */
if ( !class_exists( 'OVA_Megamenu_Assets' ) ) {

	class OVA_Megamenu_Assets {

		/**
		 * Constructor
		 */
		public function __construct() {
			add_action( 'wp_enqueue_scripts', [ $this, 'ova_megamenu_enqueue_scripts' ], 11 );
			add_action( 'admin_enqueue_scripts', [ $this, 'ova_megamenu_enqueue_scripts_admin' ], 11 );
		}

		/**
		 * Enqueue scripts
		 */
		public function ova_megamenu_enqueue_scripts() {
			wp_enqueue_script( 'ova_megamenu_script', OVA_MEGAMENU_PLUGIN_URI.'assets/js/script.js', [ 'jquery' ], null, true );
			wp_enqueue_style( 'ova_megamenu_css', OVA_MEGAMENU_PLUGIN_URI.'assets/css/frontend/style.css', [], null );
		}

		/**
		 * Admin enqueue scripts
		 */
		public function ova_megamenu_enqueue_scripts_admin() {
			wp_enqueue_style( 'ova_megamenu_css_admin', OVA_MEGAMENU_PLUGIN_URI.'assets/css/admin/admin_style.css', [], null );
		}
	}

	// init class
	new OVA_Megamenu_Assets();
}