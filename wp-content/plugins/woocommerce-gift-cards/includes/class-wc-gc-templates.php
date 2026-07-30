<?php
/**
 * WC_GC_Templates class
 *
 * @package  WooCommerce Gift Cards
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Display functions and filters.
 *
 * @class    WC_GC_Templates
 * @version  2.5.0
 */
class WC_GC_Templates {

	/**
	 * Should dequeue scripts status.
	 *
	 * @since 1.1.1
	 *
	 * @var array
	 */
	private $should_dequeue_scripts;

	/**
	 * Setup hooks and functions.
	 */
	public function __construct() {

		// Template functions and hooks.
		require_once WC_GC_ABSPATH . 'includes/wc-gc-template-functions.php';
		require_once WC_GC_ABSPATH . 'includes/wc-gc-template-hooks.php';

		// Front end scripts and JS templates.
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );
		add_action( 'wp_print_footer_scripts', array( $this, 'dequeue_product_scripts' ), 9 );

		// Defaults.
		$this->should_dequeue_scripts = true;
	}

	/*
	---------------------------------------------------*/
	/*
		Setters.                                         */
	/*---------------------------------------------------*/

	/**
	 * Force enqueuing scripts or not.
	 *
	 * @since  1.1.1
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		$this->should_dequeue_scripts = false;
	}

	/*
	---------------------------------------------------*/
	/*
		Callbacks.                                       */
	/*---------------------------------------------------*/

	/**
	 * Front-end styles and scripts.
	 *
	 * @return void
	 */
	public function frontend_scripts() {

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		// Styles
		wp_register_style( 'wc-gc-css', WC_GC()->get_plugin_url() . '/assets/css/frontend/woocommerce.css', false, WC_GC()->get_plugin_version(), 'all' );
		wp_style_add_data( 'wc-gc-css', 'rtl', 'replace' );
		wp_enqueue_style( 'wc-gc-css' );

		if ( wp_is_block_theme() ) {
			wp_register_style( 'wc-gc-blocks-style', WC_GC()->get_plugin_url() . '/assets/css/frontend/blocktheme.css', false, WC_GC()->get_plugin_version() );
			wp_style_add_data( 'wc-gc-blocks-style', 'rtl', 'replace' );
			wp_enqueue_style( 'wc-gc-blocks-style' );
		}

		$dependencies = array( 'jquery', 'jquery-ui-datepicker' );

		/**
		 * Filter to allow adding custom script dependencies here.
		 *
		 * @param  array  $dependencies
		 */
		$dependencies = apply_filters( 'woocommerce_gc_script_dependencies', $dependencies );

		wp_register_script( 'wc-gc-main', WC_GC()->get_plugin_url() . '/assets/js/frontend/wc-gc-main' . $suffix . '.js', $dependencies, WC_GC()->get_plugin_version(), true );
		wp_script_add_data( 'wc-gc-main', 'strategy', 'defer' );

		/**
		 * Filter front-end params.
		 *
		 * @param  array  $params
		 */
		$params = apply_filters(
			'woocommerce_gc_front_end_params',
			array(
				'version'                           => WC_GC()->get_plugin_version(),
				'wc_ajax_url'                       => WC_AJAX::get_endpoint( '%%endpoint%%' ),
				'datepicker_class'                  => ! apply_filters( 'woocommerce_gc_disable_datepicker_styles', false ) ? 'wc_gc_datepicker' : '',
				'datepicker_date_format'            => wc_gc_get_js_date_format(),
				'gmt_offset'                        => -1 * wc_gc_get_gmt_offset(), // Revert value to match JS.
				'date_input_timezone_reference'     => wc_gc_get_date_input_timezone_reference(),
				'security_redeem_card_nonce'        => wp_create_nonce( 'redeem-card' ),
				'security_remove_card_nonce'        => wp_create_nonce( 'remove-card' ),
				'security_update_use_balance_nonce' => wp_create_nonce( 'update-use-balance' ),
			)
		);

		wp_localize_script( 'wc-gc-main', 'wc_gc_params', $params );

		wp_enqueue_script( 'wc-gc-main' );

		// Load JS only when needed.
		if ( is_checkout() || is_cart() || is_account_page() ) {
			$this->enqueue_scripts();
		}
	}

	/**
	 * Dequeue script when not viewing a Gift Card product.
	 *
	 * @since  1.1.1
	 *
	 * @return void
	 */
	public function dequeue_product_scripts() {

		if ( $this->should_dequeue_scripts ) {
			wp_dequeue_script( 'wc-gc-main' );
		}
	}
}
