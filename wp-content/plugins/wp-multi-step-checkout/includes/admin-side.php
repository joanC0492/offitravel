<?php

defined( 'ABSPATH' ) || exit; 

class WPMultiStepCheckout_Settings {

	public function __construct() {}

    /**
     * Initialize. 
     */
	public static function init() {

        require_once 'settings-array.php';
        require_once 'frm/class-form-fields.php';
        require_once 'frm/premium-tooltips.php';
        require_once 'frm/warnings.php';

        add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_scripts') );

        self::warnings();
    }

    /**
     * Create the menu link
     */
    public static function admin_menu() {
        add_submenu_page(
            'woocommerce', 
            'Multi-Step Checkout', 
            'Multi-Step Checkout', 
            'manage_options', 
			'wmsc-settings',
            array( __CLASS__, 'admin_settings_page')
        );
    }

    /**
     * Enqueue the scripts and styles 
     */
    public static function admin_enqueue_scripts() {
        $page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_URL);
        if ( $page != 'wmsc-settings' ) return false;

        // Color picker
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script('wp-color-picker');

        $assets     = WMSC_PLUGIN_URL . 'assets/';
        $frm_assets = WMSC_PLUGIN_URL . 'includes/frm/assets/';
        $version    = WMSC_VERSION;
        $dependency = array('jquery');
        $where      = true;

        // Load scripts
        wp_enqueue_script( 'wmsc-admin-script', $assets . 'js/admin-script.js', $dependency, $version, $where);

        // Load styles
        wp_enqueue_style ( 'wmsc-bootstrap',   $frm_assets . 'bootstrap.min.css', array(), $version);
        wp_enqueue_style ( 'wmsc-admin-style', $assets . 'css/admin-style.css', array(), $version);
    }

    /**
     * Output the admin page
     * @access public
     */
	public static function admin_settings_page() {

        // Get the tabs. 
        $tabs = array(
            'general'       => __('General Settings', 'wp-multi-step-checkout'),
            'design'        => __('Design', 'wp-multi-step-checkout'),
            'titles'        => __('Text on Steps and Buttons', 'wp-multi-step-checkout')
        );

        $tab_current = (isset($_GET['tab'])) ? $_GET['tab'] : 'general';

        if ( ! isset( $tabs[ $tab_current ] ) ) $tab_current = 'general';

		// Get the field settings.
		$settings_all   = get_wmsc_settings();
		$values_current = get_option( 'wmsc_options', array() );

		$form = new \SilkyPressFrm\Form_Fields( $settings_all );
		$form->add_setting( 'tooltip_img', WMSC_PLUGIN_URL . 'assets/images/question_mark.svg' );
		$form->add_setting( 'section', $tab_current );
		$form->add_setting( 'label_class', 'col-sm-6' );
		$form->set_current_values( $values_current );

		// The settings were saved.
		if ( ! empty( $_POST ) ) {
            check_admin_referer( 'wmsc_' . $tab_current );

			if ( current_user_can( 'manage_woocommerce' ) ) {

				$values_post_sanitized = $form->validate( $_POST );

				$form->set_current_values( $values_post_sanitized );

				foreach ( $settings_all as $_key => $_setting ) {
					if ( isset( $_setting['pro'] ) && $_setting['pro'] && isset( $_setting['value'] ) ) {
						$values_post_sanitized[ $_key ] = $_setting['value'];
					}
				}

				if ( update_option( 'wmsc_options', $values_post_sanitized ) ) {
					$form->add_message( 'success', '<b>'. __('Your settings have been saved.') . '</b>' );
				}
			}
			
		}

        // Premium tooltips.
		new SilkyPress_PremiumTooltips( [
			/* translators: 1: url */
			'message'      => __('Available only in <a href="%1$s" target="_blank">Pro version</a>', 'wp-multi-step-checkout'),
			'allowed_html' => ['a' => ['href' => true, 'target'=> true]],
			'url'          => 'https://www.silkypress.com/woocommerce-multi-step-checkout-pro/?utm_source=wordpress&utm_campaign=wmsc_free&utm_medium=banner',
		] );

		// Render the content.
		$messages = $form->render_messages();
		$content  = $form->render();

		include_once 'admin-template.php'; 

        include_once 'right_columns.php';
	}

    /**
     * Show admin warnings
     */
    public static function warnings() {

        $allowed_actions = array(
			'wmsc_dismiss_suki_theme',
			'wmsc_dismiss_german_market_hooks',
			'wmsc_dismiss_elementor_pro_widget',
        );

        $w = new SilkyPress_Warnings($allowed_actions); 


        if ( !$w->is_url('plugins') && !$w->is_url('wmsc-settings') ) {
            return;
        }

        // Warning about the Suki theme
        if ( strpos( strtolower(get_template()), 'suki') !== false && $w->is_url('wmsc-settings') ) {
            $message = __('The Suki theme adds some HTML elements to the checkout page in order to create the two columns. This additional HTML messes up the steps from the multi-step checkout plugin. Unfortunately the multi-step checkout plugin isn\'t compatibile with the Suki theme.', 'wp-multi-step-checkout');
            $w->add_notice( 'wmsc_dismiss_suki_theme', $message);
        }


        // Warning if the hooks from the German Market plugin are turned on
        if ( class_exists('Woocommerce_German_Market') && get_option( 'gm_deactivate_checkout_hooks', 'off' ) != 'off' && $w->is_url('wmsc-settings') ) {
            $message = __('The "Deactivate German Market Hooks" option on the <b>WP Admin -> WooCommerce -> German Market -> Ordering</b> page will interfere with the proper working of the <b>Multi-Step Checkout for WooCommerce</b> plugin. Please consider turning the option off.', 'wp-multi-step-checkout');
            $w->add_notice( 'wmsc_dismiss_german_market_hooks', $message);
        }


		// Warning about the Elementor Pro Checkout widget.
		if ( defined( 'ELEMENTOR_PRO_VERSION' ) ) {
			$message = __('If the Elementor Pro Checkout widget is used on the checkout page, make sure the "Skin" option is set to "Multi-Step Checkout" in the widget\'s "Content -> General" section.', 'wp-multi-step-checkout');
			$w->add_notice( 'wmsc_dismiss_elementor_pro_widget', $message);
		}

        $w->show_warnings();
    }
}

WPMultiStepCheckout_Settings::init();
