<?php
/**
 * Plugin Name: Travel and Tour Booking
 * Plugin URI: https://themeforest.net/user/ovatheme/portfolio
 * Description: OvaTheme Travel and Tour Booking WooCommerce Plugin.
 * Author: Ovatheme
 * Version: 2.0.2
 * Author URI: https://themeforest.net/user/ovatheme
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ova-brw
 * Domain Path: /languages/
 * 
 */

if ( !defined( 'ABSPATH' ) ) exit();

/**
 * Class OVABRW
 */
if ( !class_exists( 'OVABRW' ) ) {

	class OVABRW {

		/**
		 * Constructor
		 */
		public function __construct() {
			// Define constants
			$this->define_constants();

			// Includes
			$this->includes();

			// Load textdomain
			add_action( 'init', array( $this, 'load_textdomain' ) );

			// Woocommerce loaded
			add_action( 'woocommerce_loaded', [ $this, 'woocommerce_loaded' ] );

			// Register Elementor
			$this->ovabrw_register_elementor();
		}

		/**
		 * Define constants
		 */
		public function define_constants() {
			define( 'OVABRW_PLUGIN_FILE', __FILE__ );
			define( 'OVABRW_PLUGIN_URI', plugin_dir_url( __FILE__ ) );
			define( 'OVABRW_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
			define( 'OVABRW_PLUGIN_ADMIN', OVABRW_PLUGIN_PATH . 'admin/' );
			define( 'OVABRW_PLUGIN_INC', OVABRW_PLUGIN_PATH . 'inc/' );

			// Global prefix
			define( 'OVABRW_PREFIX', 'ovabrw_' );
			define( 'OVABRW_PREFIX_OPTIONS', 'ova_brw_' );
			define( 'OVABRW_RENTAL', 'ovabrw_car_rental' );
		}

		/**
		 * Include files
		 */
		public function includes() {
			// Funciton
			require_once( OVABRW_PLUGIN_PATH . 'inc/ovabrw-functions.php' );

			// Get order
			require_once( OVABRW_PLUGIN_PATH . 'inc/ovabrw-get-data.php' );

			// Admin
			add_action( 'init', function() {
				require_once OVABRW_PLUGIN_ADMIN . 'class-ovabrw-admin.php';
			});

			// Add taxonomy type
			require_once( OVABRW_PLUGIN_PATH . 'inc/ovabrw-taxonomy.php' );

			// Add Js Css
			require_once( OVABRW_PLUGIN_PATH . 'inc/ovabrw-assets.php' );
			
			// Cart
			require_once( OVABRW_PLUGIN_PATH . 'inc/ovabrw-cart.php' );

			// Calculate Before add to cart
			require_once( OVABRW_PLUGIN_PATH . 'inc/ovabrw-cus-cal-cart.php' );

			// Add tab beside description
			require_once( OVABRW_PLUGIN_PATH . 'inc/ovabrw-extra-tab.php' );			

			// Filter name
			require_once( OVABRW_PLUGIN_PATH . 'inc/ovabrw_hooks.php' );

			// Deposit
			require_once( OVABRW_PLUGIN_PATH . 'inc/ovabrw_deposit.php' );

			// Ajax
			require_once( OVABRW_PLUGIN_PATH . 'inc/ovabrw_ajax.php' );

			// Register Custom Post Type
			require_once( OVABRW_PLUGIN_PATH . 'custom-post-type/register_cpt.php' );

			// Cron
			require_once( OVABRW_PLUGIN_PATH . 'inc/ovabrw-cron.php' );

			// Send mail
			require_once( OVABRW_PLUGIN_PATH . 'inc/ovabrw-mail.php' );				
		}

		/**
		 * Load textdomain
		 */
		public function load_textdomain() {
			if ( is_textdomain_loaded( 'ova-brw' ) ) return;
			load_plugin_textdomain( 'ova-brw', false, basename( dirname( __FILE__ ) ) .'/languages' );
		}

		/**
		 * Woocommerce loaded
		 */
		public function woocommerce_loaded() {
			// init rental product
			require_once OVABRW_PLUGIN_INC . 'ovabrw-tour-product.php';

			// Cart & Checkout Blocks Integrations
			require_once OVABRW_PLUGIN_INC . 'ovabrw-blocks.php';

			// Register IntegrationInterface
			if ( class_exists( 'OVABRW_Blocks' ) ) {
				add_action(
				    'woocommerce_blocks_mini-cart_block_registration',
				    function( $integration_registry ) {
				        $integration_registry->register( new OVABRW_Blocks() );
				    }
				);
				add_action(
				    'woocommerce_blocks_cart_block_registration',
				    function( $integration_registry ) {
				        $integration_registry->register( new OVABRW_Blocks() );
				    }
				);
				add_action(
				    'woocommerce_blocks_checkout_block_registration',
				    function( $integration_registry ) {
				        $integration_registry->register( new OVABRW_Blocks() );
				    }
				);
			}
		}

		/**
		 * Register elementor
		 */
		public function ovabrw_register_elementor() {
			/* Make Elementors */
			if ( did_action( 'elementor/loaded' ) ) {
				include OVABRW_PLUGIN_PATH . 'elementor/ovabrw-register-elementor.php';
			}
		}
	}
}

/**
 * Plugin active
 */
if ( !function_exists( 'is_plugin_active' ) ) {
	// Require plugin.php to use is_plugin_active() below
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

/**
 * Woocommerce active
 */
if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
	new OVABRW();
}