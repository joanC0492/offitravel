<?php
/**
 * Plugin Name: Ovatheme Destination
 * Plugin URI: https://themeforest.net/user/ovatheme
 * Description: Destination
 * Author: Ovatheme
 * Version: 1.1.3
 * Author URI: https://themeforest.net/user/ovatheme/portfolio
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ova-destination
 * Domain Path: /languages/
*/

if ( ! defined( 'ABSPATH' ) ) exit();

/**
 * Class OvaDestination
 */
if ( !class_exists( 'OvaDestination') ) {

	class OvaDestination {

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
			if ( !defined( 'OVADESTINATION_PLUGIN_FILE' ) ) {
                define( 'OVADESTINATION_PLUGIN_FILE', __FILE__ );   
            }
            if ( !defined( 'OVADESTINATION_PLUGIN_URI' ) ) {
                define( 'OVADESTINATION_PLUGIN_URI', plugin_dir_url( __FILE__ ) );   
            }
            if ( !defined( 'OVADESTINATION_PLUGIN_PATH' ) ) {
                define( 'OVADESTINATION_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );   
            }
			
			load_plugin_textdomain( 'ova-destination', false, basename( dirname( __FILE__ ) ) .'/languages' );
		}

		/**
		 * Includes
		 */
		public function includes() {
			// Custom post type
			require_once( OVADESTINATION_PLUGIN_PATH.'inc/class-ova-custom-post-type.php' );

			// Get data
			require_once( OVADESTINATION_PLUGIN_PATH.'inc/class-ova-get-data.php' );

			// Core functions
			require_once( OVADESTINATION_PLUGIN_PATH.'inc/ova-core-functions.php' );
			
			// Templates loaders
			require_once( OVADESTINATION_PLUGIN_PATH.'inc/class-ova-templates-loaders.php' );

			// Assets
			require_once( OVADESTINATION_PLUGIN_PATH.'inc/class-ova-assets.php' );

			// Meta boxes
			require_once( OVADESTINATION_PLUGIN_PATH.'admin/class-ova-metabox.php' );
			require_once( OVADESTINATION_PLUGIN_PATH.'admin/class-cmb2-field-map.php' );

			// Customize
			require_once OVADESTINATION_PLUGIN_PATH.'/inc/class-customize.php';
		}

		/**
		 * Supports
		 */
		public function supports() {
			// Elementor
			if ( did_action( 'elementor/loaded' ) ) {
				include OVADESTINATION_PLUGIN_PATH.'elementor/class-ova-register-elementor.php';
			}
		}
	}

	// init class
	new OvaDestination();
}