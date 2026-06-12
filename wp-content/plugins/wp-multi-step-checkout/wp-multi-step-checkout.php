<?php
/**
 * Plugin Name:          Multi-Step Checkout for WooCommerce
 * Requires Plugins:     woocommerce
 * Plugin URI:           https://wordpress.org/plugins/wp-multi-step-checkout/
 * Description:          Split the different sections of the default WooCommerce checkout page into multiple steps
 * Version:              2.35.1
 * Author:               SilkyPress
 * Author URI:           https://www.silkypress.com
 *
 * Text Domain:          wp-multi-step-checkout
 * Domain Path:          /languages/
 *
 * WC requires at least: 3.0.0
 * WC tested up to:      10.9
 * Requires PHP:         5.2.4
 *
 * @package WPMultiStepCheckout
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WPMultiStepCheckout' ) ) :
	/**
	 * @class WPMultiStepCheckout
	 */
	class WPMultiStepCheckout {

		/**
		 * Plugin's version.
		 *
		 * @var string
		 */
		public static $version = '2.35.1';

		/**
		 * Plugin's options.
		 *
		 * @var array
		 */
		public static $options = array();


		public function __construct() {}

		/**
		 * WPMultiStepCheckout plugin init. 
		 */
		public static function init() {

			define( 'WMSC_PLUGIN_FILE', __FILE__ );
			define( 'WMSC_PLUGIN_URL', plugins_url( '/', __FILE__ ) );
			define( 'WMSC_PLUGIN_PATH', plugin_dir_url( '/', __FILE__ ) );
			define( 'WMSC_VERSION', self::$version );

			if ( class_exists( 'WPMultiStepCheckoutPro' ) ) {
                return false;
            }

			if ( ! class_exists( 'woocommerce' ) ) {
				add_action( 'admin_notices', array( __CLASS__, 'install_woocommerce_admin_notice' ) );
				return false;
			}

			if ( is_admin() ) {
				include_once 'includes/admin-side.php';
			}


			// Disable on mobile devices, if necessary.
			self::$options = get_option( 'wmsc_options', array() );
			if ( isset( self::$options['disable_mobile'] ) && self::$options['disable_mobile'] && wp_is_mobile() ) {
				return;
			}


			self::update_14_version();

			add_filter( 'woocommerce_locate_template', array( __CLASS__, 'woocommerce_locate_template' ), 30, 3 );

			self::adjust_hooks();

			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'wp_enqueue_scripts' ) );

			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( __CLASS__, 'settings_link' ) );

		}


		/**
		 * Modify the default WooCommerce hooks
		 */
		public static function adjust_hooks() {
			// Remove login messages.
			remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10 );
			remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );

			// Split the `Order` and the `Payment` tabs.
			remove_action( 'woocommerce_checkout_order_review', 'woocommerce_order_review', 10 );
			remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );
			add_action( 'wpmc-woocommerce_order_review', 'woocommerce_order_review', 20 );
			add_action( 'wpmc-woocommerce_checkout_payment', 'woocommerce_checkout_payment', 10 );

			// Split the `woocommerce_before_checkout_form`.
			remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10 );
			remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
			add_action( 'wpmc-woocommerce_checkout_login_form', 'woocommerce_checkout_login_form', 10 );
			add_action( 'wpmc-woocommerce_checkout_coupon_form', 'woocommerce_checkout_coupon_form', 10 );

			// Add the content functions to the steps.
			add_action( 'wmsc_step_content_login', 'wmsc_step_content_login', 10 );
			add_action( 'wmsc_step_content_shipping', 'wmsc_step_content_shipping', 10 );
			add_action( 'wmsc_step_content_billing', 'wmsc_step_content_billing', 10 );

			add_filter( 'wmsc_delete_step_by_category', array( __CLASS__, 'hide_shipping_step_virtual' ) ); 
		}

		/**
		 * Load the form-checkout.php template from this plugin.
		 *
		 * @param string $template      Template name.
		 * @param string $template_name Template name.
		 * @param string $template_path Template path. (default: '').
		 * @return string
		 */
		public static function woocommerce_locate_template( $template, $template_name, $template_path ) {
			if ( 'checkout/form-checkout.php' !== $template_name ) {
				return $template;
			}
			$template = plugin_dir_path( __FILE__ ) . 'includes/form-checkout.php';
			return $template;
		}

		/**
		 * Enqueue the JS and CSS assets
		 */
		public static function wp_enqueue_scripts() {

			if ( ! is_checkout() ) {
				return;
			}

			$keyboard_nav = ( isset( self::$options['keyboard_nav'] ) && self::$options['keyboard_nav'] ) ? true : false;
			$color        = ( isset( self::$options['main_color'] ) ) ? wp_strip_all_tags( self::$options['main_color'] ) : '#1e85be';
			$url          = plugins_url( '/', __FILE__ ) . 'assets/';
			$prefix       = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

			// JS variables.
			$vars         = array( 'keyboard_nav' => $keyboard_nav );
			$vars_filters = array( 'hide_last_prev', 'hide_last_back_to_cart', 'skip_login_above_form', 'skip_login_next_to_login_button' );
			foreach ( $vars_filters as $_filter ) {
				if ( apply_filters( 'wpmc_' . $_filter, false ) ) {
					$vars[ $_filter ] = true;
				}
			}

			// Load scripts.
			wp_register_script( 'wpmc', $url . 'js/script' . $prefix . '.js', array( 'jquery' ), self::$version, true );
			wp_localize_script( 'wpmc', 'WPMC', apply_filters( 'wmsc_js_variables', $vars ) );
			wp_register_style( 'wpmc', $url . 'css/style-progress' . $prefix . '.css', array(), self::$version );

			wp_enqueue_script( 'wpmc' );
			wp_enqueue_style( 'wpmc' );

			// Load the inline styles.
			$style  = '.wpmc-tabs-wrapper .wpmc-tab-item.current::before { border-bottom-color:' . $color . '; }';
			$style .= '.wpmc-tabs-wrapper .wpmc-tab-item.current .wpmc-tab-number { border-color: ' . $color . '; }';
			if ( is_rtl() ) {
				$style .= '.wpmc-tabs-list .wpmc-tab-item { float: right; }';
			}
			wp_add_inline_style( 'wpmc', $style );
		}

		/**
		 * Admin notice that WooCommerce is not activated
		 */
		public static function install_woocommerce_admin_notice() {
			?>
			<div class="error">
			<p><?php _x( 'The <b>Multi-Step Checkout for WooCommerce</b> plugin is enabled, but it requires WooCommerce in order to work.', 'Alert Message: WooCommerce require', 'wp-multi-step-checkout' ); ?></p>
			</div>
			<?php
		}

		/**
		 * Add Settings link on the Plugins page.
		 *
		 * @param array $links Currently available links.
		 */
		public static function settings_link( $links ) {
			$action_links = array(
				'settings' => '<a href="' . admin_url( 'admin.php?page=wmsc-settings' ) . '" aria-label="' . esc_attr__( 'View plugin\'s settings', 'wp-multi-step-checkout' ) . '">' . esc_html__( 'Settings', 'wp-multi-step-checkout' ) . '</a>',
			);
			return array_merge( $action_links, $links );
		}


		/**
		 * Update options array for the 1.4 version
		 */
		public static function update_14_version() {
			if ( ! $old_options = get_option( 'wpmc-settings' ) ) {
				return;
			}

			require_once 'includes/settings-array.php';
			$defaults = get_wmsc_settings();

			$new_options = array();
			foreach ( $defaults as $_key => $_value ) {
				if ( isset( $old_options[ $_key ] ) ) {
					$new_options[ $_key ] = $old_options[ $_key ][2];
				} else {
					$new_options[ $_key ] = $_value['value'];
				}
			}

			update_option( 'wmsc_options', $new_options );
			delete_option( 'wpmc-settings' );
		}


		/*
		 * Hide the "Shipping" step if there are only virtual products in the cart.
		 */
		public static function hide_shipping_step_virtual( $settings ) {

			if ( isset( self::$options['hide_shipping_step_virtual'] ) && self::$options['hide_shipping_step_virtual'] ) {
				$settings['virtual_products'] = true;
			}

			return $settings;
		} 

	}

add_action( 'init', array( 'WPMultiStepCheckout', 'init' ) );


include_once 'includes/class-wmsc-compatibilities.php';

endif;
