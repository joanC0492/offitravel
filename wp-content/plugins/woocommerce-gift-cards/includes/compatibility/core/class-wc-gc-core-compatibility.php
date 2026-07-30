<?php
/**
 * WC_GC_Core_Compatibility class
 *
 * @package  WooCommerce Gift Cards
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Functions related to core back-compatibility.
 *
 * @class    WC_GC_Core_Compatibility
 * @version  2.6.0
 */
class WC_GC_Core_Compatibility {

	/**
	 * Cache 'gte' comparison results.
	 *
	 * @var array
	 */
	private static $is_wc_version_gte = array();

	/**
	 * Cache 'gt' comparison results.
	 *
	 * @var array
	 */
	private static $is_wc_version_gt = array();

	/**
	 * Cache 'gt' comparison results for WP version.
	 *
	 * @var array
	 */
	private static $is_wp_version_gt = array();

	/**
	 * Cache 'gte' comparison results for WP version.
	 *
	 * @var array
	 */
	private static $is_wp_version_gte = array();

	/**
	 * Cache wc admin status result.
	 *
	 * @var bool
	 */
	private static $is_wc_admin_enabled = null;

	/**
	 * Current REST request stack.
	 * An array containing WP_REST_Request instances.
	 *
	 * @since 1.16.12
	 *
	 * @var array
	 */
	private static $requests = array();

	/**
	 * Cache HPOS status.
	 *
	 * @since  1.15.0
	 *
	 * @var bool
	 */
	private static $is_hpos_enabled = null;

	/**
	 * Initialization and hooks.
	 */
	public static function init() {
		// Save current rest request. Is there a better way to get it?
		add_filter( 'rest_pre_dispatch', array( __CLASS__, 'save_rest_request' ), 10, 3 );
		add_filter( 'woocommerce_hydration_dispatch_request', array( __CLASS__, 'save_hydration_request' ), 10, 2 );
		add_filter( 'rest_request_after_callbacks', array( __CLASS__, 'pop_rest_request' ), PHP_INT_MAX );
		add_filter( 'woocommerce_hydration_request_after_callbacks', array( __CLASS__, 'pop_rest_request' ), PHP_INT_MAX );

		// Refactoring of Analytics in WC 9.3 deprecated Query class (https://github.com/woocommerce/woocommerce/pull/49425).
		if ( version_compare( WC_VERSION, '9.3', '<' ) && ! class_exists( 'Automattic\WooCommerce\Admin\API\Reports\GenericQuery' ) ) {
			class_alias( 'Automattic\WooCommerce\Admin\API\Reports\Query', 'Automattic\WooCommerce\Admin\API\Reports\GenericQuery' );
		}
	}

	/*
	|--------------------------------------------------------------------------
	| Callbacks.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Pops the current request from the execution stack.
	 *
	 * @since  1.16.12
	 *
	 * @param  WP_REST_Response     $response
	 * @param  WP_REST_Server|array $handler
	 * @param  WP_REST_Request      $request
	 * @return mixed
	 */
	public static function pop_rest_request( $response ) {
		if ( ! empty( self::$requests ) && is_array( self::$requests ) ) {
			array_pop( self::$requests );
		}

		return $response;
	}

	/**
	 * Saves the current hydration request.
	 *
	 * @since  1.16.12
	 *
	 * @param  mixed           $result
	 * @param  WP_REST_Request $request
	 * @return mixed
	 */
	public static function save_hydration_request( $result, $request ) {
		if ( ! is_array( self::$requests ) ) {
			self::$requests = array();
		}

		self::$requests[] = $request;
		return $result;
	}

	/**
	 * Saves the current rest request.
	 *
	 * @since  1.2.0
	 *
	 * @param  mixed           $result
	 * @param  WP_REST_Server  $server
	 * @param  WP_REST_Request $request
	 * @return mixed
	 */
	public static function save_rest_request( $result, $server, $request ) {
		if ( ! is_array( self::$requests ) ) {
			self::$requests = array();
		}

		self::$requests[] = $request;
		return $result;
	}

	/*
	|--------------------------------------------------------------------------
	| WC version handling.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Helper method to get the version of the currently installed WooCommerce.
	 *
	 * @return string
	 */
	public static function get_wc_version() {
		return defined( 'WC_VERSION' ) && WC_VERSION ? WC_VERSION : null;
	}

	/**
	 * Returns true if the installed version of WooCommerce is greater than or equal to $version.
	 *
	 * @param  string $version
	 * @return boolean
	 */
	public static function is_wc_version_gte( $version ) {
		if ( ! isset( self::$is_wc_version_gte[ $version ] ) ) {
			self::$is_wc_version_gte[ $version ] = self::get_wc_version() && version_compare( self::get_wc_version(), $version, '>=' );
		}
		return self::$is_wc_version_gte[ $version ];
	}

	/**
	 * Returns true if the installed version of WooCommerce is greater than $version.
	 *
	 * @param  string $version
	 * @return boolean
	 */
	public static function is_wc_version_gt( $version ) {
		if ( ! isset( self::$is_wc_version_gt[ $version ] ) ) {
			self::$is_wc_version_gt[ $version ] = self::get_wc_version() && version_compare( self::get_wc_version(), $version, '>' );
		}
		return self::$is_wc_version_gt[ $version ];
	}

	/**
	 * Returns true if the installed version of WooCommerce is lower than or equal $version.
	 *
	 * @param  string $version
	 * @return boolean
	 */
	public static function is_wc_version_lte( $version ) {
		if ( ! isset( self::$is_wc_version_gt[ $version ] ) ) {
			self::$is_wc_version_gt[ $version ] = self::get_wc_version() && version_compare( self::get_wc_version(), $version, '<=' );
		}
		return self::$is_wc_version_gt[ $version ];
	}

	/**
	 * Returns true if the installed version of WooCommerce is lower than $version.
	 *
	 * @param  string $version
	 * @return boolean
	 */
	public static function is_wc_version_lt( $version ) {
		if ( ! isset( self::$is_wc_version_gt[ $version ] ) ) {
			self::$is_wc_version_gt[ $version ] = self::get_wc_version() && version_compare( self::get_wc_version(), $version, '<' );
		}
		return self::$is_wc_version_gt[ $version ];
	}

	/*
	|--------------------------------------------------------------------------
	| WP version handling.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Returns true if the installed version of WooCommerce is greater than or equal to $version.
	 *
	 * @param  string $version
	 * @return boolean
	 */
	public static function is_wp_version_gt( $version ) {
		if ( ! isset( self::$is_wp_version_gt[ $version ] ) ) {
			global $wp_version;
			self::$is_wp_version_gt[ $version ] = $wp_version && version_compare( WC_GC()->get_plugin_version( true, $wp_version ), $version, '>' );
		}
		return self::$is_wp_version_gt[ $version ];
	}

	/**
	 * Returns true if the installed version of WooCommerce is greater than or equal to $version.
	 *
	 * @param  string $version
	 * @return boolean
	 */
	public static function is_wp_version_gte( $version ) {
		if ( ! isset( self::$is_wp_version_gte[ $version ] ) ) {
			global $wp_version;
			self::$is_wp_version_gte[ $version ] = $wp_version && version_compare( WC_GC()->get_plugin_version( true, $wp_version ), $version, '>=' );
		}
		return self::$is_wp_version_gte[ $version ];
	}

	/**
	 * Returns true if the WC Admin feature is installed and enabled.
	 *
	 * @return boolean
	 */
	public static function is_wc_admin_enabled() {

		if ( ! isset( self::$is_wc_admin_enabled ) ) {
			$enabled = defined( 'WC_ADMIN_VERSION_NUMBER' ) && version_compare( WC_ADMIN_VERSION_NUMBER, '1.0.0', '>=' );
			if ( $enabled && version_compare( WC_ADMIN_VERSION_NUMBER, '2.3.0', '>=' ) && true === apply_filters( 'woocommerce_admin_disabled', false ) ) {
				$enabled = false;
			}

			self::$is_wc_admin_enabled = $enabled;
		}

		return self::$is_wc_admin_enabled;
	}

	/**
	 * Returns true if is a react based admin page.
	 *
	 * @since 1.10.3
	 *
	 * @return boolean
	 */
	public static function is_admin_or_embed_page() {

		if ( class_exists( '\Automattic\WooCommerce\Admin\PageController' ) && method_exists( '\Automattic\WooCommerce\Admin\PageController', 'is_admin_or_embed_page' ) ) {

			return \Automattic\WooCommerce\Admin\PageController::is_admin_or_embed_page();

		} elseif ( class_exists( '\Automattic\WooCommerce\Admin\Loader' ) && method_exists( '\Automattic\WooCommerce\Admin\Loader', 'is_admin_or_embed_page' ) ) {

			return \Automattic\WooCommerce\Admin\Loader::is_admin_or_embed_page();
		}

		return false;
	}

	/**
	 * Back-compat wrapper for 'is_rest_api_request'.
	 *
	 * @since  1.2.0
	 *
	 * @return boolean
	 */
	public static function is_rest_api_request() {

		if ( false !== self::get_api_request() ) {
			return true;
		}

		return method_exists( WC(), 'is_rest_api_request' ) ? WC()->is_rest_api_request() : defined( 'REST_REQUEST' );
	}

	/*
	|--------------------------------------------------------------------------
	| Utilities.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Whether this is a Store/REST API request.
	 *
	 * @since  1.2.0
	 *
	 * @return boolean
	 */
	public static function is_api_request() {
		return self::is_store_api_request() || self::is_rest_api_request();
	}

	/**
	 * Returns the current Store/REST API request or false.
	 *
	 * @since  1.2.0
	 *
	 * @return WP_REST_Request|false
	 */
	public static function get_api_request() {
		if ( empty( self::$requests ) || ! is_array( self::$requests ) ) {
			return false;
		}

		return end( self::$requests );
	}

	/**
	 * Whether this is a Store API request.
	 *
	 * @since  1.2.0
	 *
	 * @param  string $route
	 * @return boolean
	 */
	public static function is_store_api_request( $route = '', $method = '' ) {

		// Check the request URI.
		$request = self::get_api_request();

		if ( false !== $request && strpos( $request->get_route(), 'wc/store' ) !== false ) {

			$check_route  = ! empty( $route );
			$check_method = ! empty( $method );

			if ( ! $check_route && ! $check_method ) {
				// Generic store api question.
				return true;
			}

			$route_result  = ! $check_route || strpos( $request->get_route(), $route ) !== false;
			$method_result = ! $check_method || strtolower( $request->get_method() ) === strtolower( $method );

			return $route_result && $method_result;
		}

		return false;
	}

	/**
	 * Check if the usage of the custom orders table is enabled.
	 *
	 * @since  1.15.0
	 *
	 * @return bool
	 */
	public static function is_hpos_enabled() {

		if ( ! isset( self::$is_hpos_enabled ) ) {
			self::$is_hpos_enabled = class_exists( 'Automattic\WooCommerce\Utilities\OrderUtil' ) && Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
		}

		return self::$is_hpos_enabled;
	}
}

WC_GC_Core_Compatibility::init();
