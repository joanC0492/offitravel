<?php
/**
 * Plugin Name: MultiStep Checkout for WooCommerce
 * Description: MultiStep Checkout for WooCommerce plugin breaks up the usual WooCommerce checkout form into multiple steps for a friendlier user experience.
 * Version:     2.3.4
 * Author:      ThemeHigh
 * Author URI:  https://www.themehigh.com
 * Text Domain: woo-multistep-checkout
 * Domain Path: /languages
 * WC requires at least: 5.0
 * WC tested up to: 10.8
 * Requires Plugins: woocommerce
*/

if(!defined( 'ABSPATH' )) exit;

if (!function_exists('is_woocommerce_active')){
	function is_woocommerce_active(){
	    $active_plugins = (array) get_option('active_plugins', array());
	    if(is_multisite()){
		   $active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
	    }
	    return in_array('woocommerce/woocommerce.php', $active_plugins) || array_key_exists('woocommerce/woocommerce.php', $active_plugins);
	}
}

if(is_woocommerce_active()) {
	
	if(!class_exists('THWMSCF_Multistep_Checkout')){	
		class THWMSCF_Multistep_Checkout {	
			public function __construct(){
				add_action('init', array($this, 'init'));
				add_action('plugins_loaded', array($this,'thwmscf_display_discount_announcement'));
			}

			public function init() {		
				$this->load_plugin_textdomain();

				define('THWMSCF_VERSION', '2.3.4');
				!defined('THWMSCF_BASE_NAME') && define('THWMSCF_BASE_NAME', plugin_basename( __FILE__ ));
				!defined('THWMSCF_PATH') && define('THWMSCF_PATH', plugin_dir_path( __FILE__ ));
				!defined('THWMSCF_URL') && define('THWMSCF_URL', plugins_url( '/', __FILE__ ));
				!defined('THWMSCF_ASSETS_URL') && define('THWMSCF_ASSETS_URL', THWMSCF_URL .'assets/');
				!defined('THWMSCF_TEMPLATE_PATH') && define('THWMSCF_TEMPLATE_PATH', THWMSCF_PATH . 'templates/');

				require_once( THWMSCF_PATH . 'classes/class-thwmscf-settings.php' );   

				THWMSCF_Settings::instance();	 
			}

			public function load_plugin_textdomain(){							
				load_plugin_textdomain('woo-multistep-checkout', FALSE, dirname(plugin_basename( __FILE__ )) . '/languages/');
			}

			public function thwmscf_discount_popup_actions() {
				$nonce = isset($_GET['thwmscf_discount_popup_nonce']) ? $_GET['thwmscf_discount_popup_nonce'] : false;
				if(!wp_verify_nonce($nonce, 'thwmscf_discount_popup_security')){
					die();
				}
				$thwmscf_dissmis_feature_popup = isset($_GET['thwmscf_discount_popup_dismiss']) ? sanitize_text_field( wp_unslash($_GET['thwmscf_discount_popup_dismiss'])) : false;
				
				if ($thwmscf_dissmis_feature_popup) {
					update_user_meta( get_current_user_id(), 'thwmscf_discount_popup' , true);
				}
			}

			public function thwmscf_display_discount_announcement(){

				$thwmscf_since = get_option('thwmscf_since');
				$now = time();

				// $render_time = apply_filters('thwmscf_show_discount_popup_render_time' , 3 * MONTH_IN_SECONDS);
				$render_time  = apply_filters('thwmscf_show_discount_popup_render_time', 1 * MONTH_IN_SECONDS);
				$render_time = $thwmscf_since + $render_time;
				
				if (isset($_GET['thwmscf_discount_popup_dismiss'])) {
					$this->thwmscf_discount_popup_actions();
				}

				$discount_popup = get_user_meta( get_current_user_id(),'thwmscf_discount_popup', true);

				$show_discount_popup = isset($discount_popup) ? $discount_popup : false;

				if (!$show_discount_popup && ($now > $render_time)) {
					$this->secret_discount_popup();
				}
			}

			public function secret_discount_popup(){

				$admin_url  = 'admin.php?page=woo_multistep_checkout';
		        $dismiss_url = $admin_url . '&thwmscf_discount_popup_dismiss=true&thwmscf_discount_popup_nonce=' . wp_create_nonce( 'thwmscf_discount_popup_security');

				$url = "https://www.themehigh.com/?edd_action=add_to_cart&download_id=20&cp=lyCDSy_wmsc&utm_source=free&utm_medium=premium_tab&utm_campaign=wmsc_upgrade_link";

				$current_page = isset( $_GET['page'] ) ? $_GET['page']  : '';
				
				if($current_page !== 'woo_multistep_checkout'){
					return;
				}

				?>
					<div id="thwmscf-pro-discount-popup" class="thwmscf-pro-discount-popup" style="display:none">
						<div id="thwmscf-discount-popup-wrapper" class="thwmscf-discount-popup-wrapper">
							<div class="thwmscf-pro-offer">
								<div class="thwmscf-discount-popup-close">
									<a id="thwmscf-discount-close-btn" class="thwmscf-discount-close-btn close-btn-img-popup" href="<?php echo esc_url($dismiss_url); ?>"></a>
								</div>
								<div class="thwmscf-discount-desc">
									<p class="thwmscf-discount-desc-first">Exclusive offer for you.</p>
									<p class="thwmscf-discount-desc-middle">Flat 50% off</p>
									<p class="thwmscf-discount-desc-last">on your plan upgrade.</p>
									<p class="thwmscf-discount-exp-date"><b>Hurry, grab the offer before it expires.</b></p>
								</div>
							</div>
							<div class="thwmscf-pro-claim-offer">
								<div class="thwmscf-pro-offer-desc">
									<p class="thwmscf-pro-offer-para">Extend the plugin functionalities and optimize your WooCommerce checkout page to its next level using the pro version of Multi-Step Checkout for WooCommerce.</p>
								</div>
								<div class="claim-discount-btn-div">
									<a id="claim-discount-btn" class="claim-discount-btn" href="<?php echo esc_url($url); ?>" onclick="thwmscfPopUpClose(this)" target="_blank" rel="noopener noreferrer">Claim Now</a>
								</div>
							</div>
						</div>
					</div>	
				<?php
			}
		}
	}
	new THWMSCF_Multistep_Checkout();

	add_action( 'before_woocommerce_init', 'thwmscf_before_woocommerce_init' ) ;

	function thwmscf_before_woocommerce_init() {
	    if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
	        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	    }
	}
}