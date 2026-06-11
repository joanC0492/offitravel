<?php if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Class OVAEV_Admin
 */
if ( !class_exists( 'OVAEV_Admin' ) ) {

	class OVAEV_Admin {
		public static $custom_meta_fields = [];

		/**
		 * Construct Admin
		 */
		public function __construct() {
			require_once( OVAEV_PLUGIN_PATH. '/admin/class-ovaev-admin-menu.php' );
			require_once( OVAEV_PLUGIN_PATH. '/admin/class-ovaev-admin-assets.php' );
			require_once( OVAEV_PLUGIN_PATH. '/admin/class-ovaev-admin-settings.php' );
		}
	}
	
	// init class
	new OVAEV_Admin();
}