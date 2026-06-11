<?php
/**
 * Plugin Name: Ovatheme MegaMenu
 * Plugin URI: https://themeforest.net/user/ovatheme
 * Description: OvaTheme MegaMenu
 * Author: Ovatheme
 * Version: 1.0.2
 * Author URI: https://themeforest.net/user/ovatheme/portfolio
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ova-megamenu
*/

if ( !defined( 'ABSPATH' ) ) exit();

/**
 * Class OVA_MEGAMENU
 */
if ( !class_exists( 'OVA_MEGAMENU' ) ) {

	final class OVA_MEGAMENU {

		private static $_instance = null;
		
		/**
		 * OVA_MEGAMENU Constructor
		 */
		public function __construct() {
			$this->define_constants();
			$this->includes();
		}

		/**
		 * Define constants
		 */
		public function define_constants() {
			$this->define( 'OVA_MEGAMENU_PLUGIN_FILE', __FILE__ );
			$this->define( 'OVA_MEGAMENU_PLUGIN_URI', plugin_dir_url( __FILE__ ) );
			$this->define( 'OVA_MEGAMENU_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
		}

		/**
		 * Define
		 */
		private function define( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * Include files
		 */
		public function includes() {
			require_once( OVA_MEGAMENU_PLUGIN_PATH.'/inc/class-assets.php' );
   			require_once( OVA_MEGAMENU_PLUGIN_PATH.'/inc/class-process.php' );	
		}

		/**
		 * Main Ova Events Manager Instance.
		 */
		public static function instance() {
			if ( !empty( self::$_instance ) ) {
				return self::$_instance;
			}
			return self::$_instance = new self();
		}
	}
}

/**
 * Main instance of Ova Events Manager
 */
function OVA_MEGAMENU() {
	return OVA_MEGAMENU::instance();
}

$GLOBALS['OVA_MEGAMENU'] = OVA_MEGAMENU();