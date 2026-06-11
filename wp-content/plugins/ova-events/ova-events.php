<?php
/**
 * Plugin Name: Ovatheme Events
 * Plugin URI: https://themeforest.net/user/ovatheme
 * Description: Ovatheme Events
 * Author: Ovatheme
 * Version: 1.3.1
 * Author URI: https://themeforest.net/user/ovatheme/portfolio
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ovaev
 * Domain Path: /languages/
*/

if ( !defined( 'ABSPATH' ) ) exit();

/**
 * Class OVAEV
 */
if ( !class_exists( 'OVAEV' ) ) {
	
	class OVAEV {

		/**
		 * instance
		 */
		static $_instance = null;

		/**
		 * Constructor
		 */
		public function __construct() {
			$this->define_constants();
			$this->includes();
			$this->supports();
		}

		/**
		 * Define constants
		 */
		public function define_constants() {
			$this->define( 'OVAEV_PLUGIN_FILE', __FILE__ );
			$this->define( 'OVAEV_PLUGIN_URI', plugin_dir_url( __FILE__ ) );
			$this->define( 'OVAEV_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
			load_plugin_textdomain( 'ovaev', false, basename( dirname( __FILE__ ) ) .'/languages' );
		}

		/**
		 * Define
		 */
		public function define( $name, $value ) {
			if ( !defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * instance
		 */
		public static function instance() {
			if ( !empty( self::$_instance ) ) {
				return self::$_instance;
			}
			return self::$_instance = new self();
		}

		/**
		 * Includes
		 */
		public function includes() {
			// Assets
			require_once( OVAEV_PLUGIN_PATH.'inc/class-ovaev-assets.php' );

			// Custom post type
			require_once( OVAEV_PLUGIN_PATH.'inc/class-ovaev-custom-post-type.php' );

			// Get data
			require_once( OVAEV_PLUGIN_PATH.'inc/class-ovaev-get-data.php' );

			// Settings
			require_once( OVAEV_PLUGIN_PATH.'inc/class-ovaev-settings.php' );

			// Template loaders
			require_once( OVAEV_PLUGIN_PATH.'inc/class-ovaev-templates-loaders.php' );

			// Core functions
			require_once( OVAEV_PLUGIN_PATH.'inc/ovaev-core-functions.php' );

			// Hooks
			require_once( OVAEV_PLUGIN_PATH.'inc/ovaev-hooks.php' );

			// Ajax
			require_once( OVAEV_PLUGIN_PATH.'inc/ovaev-data-ajax.php' );

			// Meta boxes
			require_once( OVAEV_PLUGIN_PATH.'admin/class-ovaev-metaboxes.php' );

			// Widgets
			require_once( OVAEV_PLUGIN_PATH.'admin/ovaev-widget.php' );

			// Admin
			if ( is_admin() ) {
				require_once( OVAEV_PLUGIN_PATH.'admin/class-ovaev-admin.php' );
			}

			// Shortcode
			require_once( OVAEV_PLUGIN_PATH.'shortcode/class-ovaev-shortcode.php' );
		}

		/**
		 * Supports
		 */
		public function supports() {
			// Make Elementors
			if ( did_action( 'elementor/loaded' ) ) {
				include OVAEV_PLUGIN_PATH.'elementor/class-ova-register-elementor.php';
			}

			// Event thumbnail size
			$archive_event_thumbnail = OVAEV_Settings::archive_event_thumbnail( '700x450' );
			$archive_event_thumbnail_array = explode( 'x', $archive_event_thumbnail );
			
			$thumb_w = '700';
			$thumb_h = '450';
			if ( is_array( $archive_event_thumbnail_array ) ) {
				$thumb_w = isset( $archive_event_thumbnail_array[0] ) ? $archive_event_thumbnail_array[0] : '700';
			}
			if ( is_array( $archive_event_thumbnail_array ) ) {
				$thumb_h = isset( $archive_event_thumbnail_array[1] ) ? $archive_event_thumbnail_array[1] : '450';
			}

			add_image_size( 'ovaev_event_thumbnail', $thumb_w, $thumb_h, true );
		}
	}
}

function OVAEV() {
	return OVAEV::instance();
}

$GLOBALS['OVAEV'] = OVAEV();