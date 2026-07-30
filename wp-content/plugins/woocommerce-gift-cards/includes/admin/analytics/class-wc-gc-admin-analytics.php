<?php
/**
 * WC_GC_Admin_Analytics class
 *
 * @package  WooCommerce Gift Cards
 * @since    1.4.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gift Cards WooCommerce Analytics.
 *
 * @version 2.0.0
 */
class WC_GC_Admin_Analytics {

	/*
	 * Init.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'setup' ) );
	}

	/*
	 * Setup Analytics.
	 */
	public static function setup() {

		if ( self::is_enabled() ) {

			self::includes();

			// Analytics init.
			add_filter( 'woocommerce_analytics_report_menu_items', array( __CLASS__, 'add_report_menu_item' ) );
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'register_script' ) );

			// Define custom woocommerce_meta keys.
			add_filter( 'woocommerce_admin_get_user_data_fields', array( __CLASS__, 'add_user_data_fields' ) );

			// REST API Controllers.
			add_filter( 'woocommerce_admin_rest_controllers', array( __CLASS__, 'add_rest_api_controllers' ) );

			// Register data stores.
			add_filter( 'woocommerce_data_stores', array( __CLASS__, 'register_data_stores' ) );

			// Sync data.
			WC_GC_Admin_Analytics_Sync::init();
		}
	}

	/**
	 * Includes.
	 *
	 * @return void
	 */
	protected static function includes() {

		// Global.
		require_once WC_GC_ABSPATH . '/includes/admin/analytics/reports/class-wc-gc-analytics-data-store.php';

		// Issued.
		require_once WC_GC_ABSPATH . '/includes/admin/analytics/reports/issued/class-wc-gc-analytics-issued-rest-controller.php';
		require_once WC_GC_ABSPATH . '/includes/admin/analytics/reports/issued/class-wc-gc-analytics-issued-data-store.php';
		require_once WC_GC_ABSPATH . '/includes/admin/analytics/reports/issued/class-wc-gc-analytics-issued-query.php';
		// Stats.
		require_once WC_GC_ABSPATH . '/includes/admin/analytics/reports/issued/stats/class-wc-gc-analytics-issued-stats-rest-controller.php';
		require_once WC_GC_ABSPATH . '/includes/admin/analytics/reports/issued/stats/class-wc-gc-analytics-issued-stats-data-store.php';
		require_once WC_GC_ABSPATH . '/includes/admin/analytics/reports/issued/stats/class-wc-gc-analytics-issued-stats-query.php';

		// Used.
		require_once WC_GC_ABSPATH . '/includes/admin/analytics/reports/used/class-wc-gc-analytics-used-rest-controller.php';
		require_once WC_GC_ABSPATH . '/includes/admin/analytics/reports/used/class-wc-gc-analytics-used-data-store.php';
		require_once WC_GC_ABSPATH . '/includes/admin/analytics/reports/used/class-wc-gc-analytics-used-query.php';
		// Stats.
		require_once WC_GC_ABSPATH . '/includes/admin/analytics/reports/used/stats/class-wc-gc-analytics-used-stats-rest-controller.php';
		require_once WC_GC_ABSPATH . '/includes/admin/analytics/reports/used/stats/class-wc-gc-analytics-used-stats-data-store.php';
		require_once WC_GC_ABSPATH . '/includes/admin/analytics/reports/used/stats/class-wc-gc-analytics-used-stats-query.php';

		// Expired.
		require_once WC_GC_ABSPATH . '/includes/admin/analytics/reports/expired/class-wc-gc-analytics-expired-rest-controller.php';
		require_once WC_GC_ABSPATH . '/includes/admin/analytics/reports/expired/class-wc-gc-analytics-expired-data-store.php';
		require_once WC_GC_ABSPATH . '/includes/admin/analytics/reports/expired/class-wc-gc-analytics-expired-query.php';
		// Stats.
		require_once WC_GC_ABSPATH . '/includes/admin/analytics/reports/expired/stats/class-wc-gc-analytics-expired-stats-rest-controller.php';
		require_once WC_GC_ABSPATH . '/includes/admin/analytics/reports/expired/stats/class-wc-gc-analytics-expired-stats-data-store.php';
		require_once WC_GC_ABSPATH . '/includes/admin/analytics/reports/expired/stats/class-wc-gc-analytics-expired-stats-query.php';

		// Sync.
		require_once WC_GC_ABSPATH . 'includes/admin/analytics/class-wc-gc-admin-analytics-sync.php';
	}


	/**
	 * Add "Gift Cards" as a Analytics submenu item.
	 *
	 * @param  array $report_pages  Report page menu items.
	 * @return array
	 */
	public static function add_report_menu_item( $report_pages ) {

		$giftcards_report = array(
			array(
				'id'       => 'wc-gc-gift-cards-analytics-report',
				'title'    => __( 'Gift Cards', 'woocommerce-gift-cards' ),
				'parent'   => 'woocommerce-analytics',
				'path'     => '/analytics/gift-cards',
				'nav_args' => array(
					'order'  => 110,
					'parent' => 'woocommerce-analytics',
				),
			),
		);

		// Make sure that we are at least above the "Setting" menu item.
		array_splice( $report_pages, count( $report_pages ) - 1, 0, $giftcards_report );

		return $report_pages;
	}

	/**
	 * Register analytics JS.
	 */
	public static function register_script() {

		if ( ! WC_GC_Core_Compatibility::is_admin_or_embed_page() ) {
			return;
		}

		$suffix            = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$script_path       = '/assets/dist/admin/analytics' . $suffix . '.js';
		$script_asset_path = WC_GC_ABSPATH . 'assets/dist/admin/analytics.asset.php';
		$script_asset      = file_exists( $script_asset_path )
			? require $script_asset_path
			: array(
				'dependencies' => array(),
				'version'      => WC_GC()->get_plugin_version(),
			);
		$script_url        = WC_GC()->get_plugin_url() . $script_path;

		wp_register_script(
			'wc-gc-gift-cards-analytics-report',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);

		// Load JS translations.
		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( 'wc-gc-gift-cards-analytics-report', 'woocommerce-gift-cards', WC_GC_ABSPATH . 'languages/' );
		}

		// Enqueue script.
		wp_enqueue_script( 'wc-gc-gift-cards-analytics-report' );
	}

	/**
	 * Adds fields so that we can store user preferences for the columns to display on a report.
	 *
	 * @param array $user_data_fields User data fields.
	 * @return array
	 */
	public static function add_user_data_fields( $user_data_fields ) {
		return array_merge(
			$user_data_fields,
			array(
				'giftcards_issued_report_columns',
				'giftcards_used_report_columns',
				'giftcards_expired_report_columns',
			)
		);
	}

	/**
	 * Analytics includes and register REST contollers.
	 *
	 * @param  array $controllers
	 * @return array
	 */
	public static function add_rest_api_controllers( $controllers ) {
		$controllers[] = 'WC_GC_Analytics_Issued_REST_Controller';
		$controllers[] = 'WC_GC_Analytics_Issued_Stats_REST_Controller';
		$controllers[] = 'WC_GC_Analytics_Used_REST_Controller';
		$controllers[] = 'WC_GC_Analytics_Used_Stats_REST_Controller';
		$controllers[] = 'WC_GC_Analytics_Expired_REST_Controller';
		$controllers[] = 'WC_GC_Analytics_Expired_Stats_REST_Controller';

		return $controllers;
	}

	/**
	 * Register Analytics data stores.
	 *
	 * @param  array $stores
	 * @return array
	 */
	public static function register_data_stores( $stores ) {
		$stores['report-giftcards-issued']        = 'WC_GC_Analytics_Issued_Data_Store';
		$stores['report-giftcards-issued-stats']  = 'WC_GC_Analytics_Issued_Stats_Data_Store';
		$stores['report-giftcards-used']          = 'WC_GC_Analytics_Used_Data_Store';
		$stores['report-giftcards-used-stats']    = 'WC_GC_Analytics_Used_Stats_Data_Store';
		$stores['report-giftcards-expired']       = 'WC_GC_Analytics_Expired_Data_Store';
		$stores['report-giftcards-expired-stats'] = 'WC_GC_Analytics_Expired_Stats_Data_Store';

		return $stores;
	}

	/**
	 * Whether or not the new Analytics reports are enabled.
	 *
	 * @return bool
	 */
	public static function is_enabled() {
		$enabled                   = WC_GC_Core_Compatibility::is_wc_admin_enabled();
		$minimum_wc_admin_required = $enabled && defined( 'WC_ADMIN_VERSION_NUMBER' ) && version_compare( WC_ADMIN_VERSION_NUMBER, '1.6.1', '>=' );

		$is_enabled = $enabled && $minimum_wc_admin_required;
		return (bool) apply_filters( 'woocommerce_gc_analytics_enabled', $is_enabled );
	}
}

WC_GC_Admin_Analytics::init();
