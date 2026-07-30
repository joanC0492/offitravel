<?php
/**
 * WC_GC_Notices class
 *
 * @package  WooCommerce Gift Cards
 * @since    1.3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Notice handling.
 *
 * @class    WC_GC_Notices
 * @version  2.0.0
 */
class WC_GC_Notices {

	/**
	 * Notice options.
	 *
	 * @var array
	 */
	public static $notice_options = array();

	/**
	 * Determines if notice options should be updated in the DB.
	 *
	 * @var boolean
	 */
	private static $should_update = false;

	/**
	 * Constructor.
	 */
	public static function init() {

		self::$notice_options = get_option( 'wc_gc_notice_options', array() );

		// Save notice options.
		add_action( 'shutdown', array( __CLASS__, 'save_notice_options' ), 100 );

		// Page cache testing is only available through the WC queing system.
		if ( function_exists( 'WC' ) && method_exists( WC(), 'queue' ) ) {

			// Schedules the 'page_cache' notice test.
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'update_notice_data' ) );
		}
	}

	/**
	 * Get a setting for a notice type.
	 *
	 * @since  1.3.4
	 *
	 * @param  string $notice_name
	 * @param  string $key
	 * @param  mixed  $default
	 * @return array
	 */
	public static function get_notice_option( $notice_name, $key, $default = null ) {
		return isset( self::$notice_options[ $notice_name ] ) && is_array( self::$notice_options[ $notice_name ] ) && isset( self::$notice_options[ $notice_name ][ $key ] ) ? self::$notice_options[ $notice_name ][ $key ] : $default;
	}

	/**
	 * Set a setting for a notice type.
	 *
	 * @since  1.3.4
	 *
	 * @param  string $notice_name
	 * @param  string $key
	 * @param  mixed  $value
	 * @return array
	 */
	public static function set_notice_option( $notice_name, $key, $value ) {

		if ( ! is_scalar( $value ) && ! is_array( $value ) ) {
			return;
		}

		if ( ! is_string( $key ) ) {
			$key = strval( $key );
		}

		if ( ! is_string( $notice_name ) ) {
			$notice_name = strval( $notice_name );
		}

		if ( ! isset( self::$notice_options ) || ! is_array( self::$notice_options ) ) {
			self::$notice_options = array();
		}

		if ( ! isset( self::$notice_options[ $notice_name ] ) || ! is_array( self::$notice_options[ $notice_name ] ) ) {
			self::$notice_options[ $notice_name ] = array();
		}

		self::$notice_options[ $notice_name ][ $key ] = $value;
		self::$should_update                          = true;
	}

	/**
	 * Save notice options to the DB.
	 */
	public static function save_notice_options() {
		if ( self::$should_update ) {
			update_option( 'wc_gc_notice_options', self::$notice_options );
		}
	}

	/**
	 * Updates data used to display notices.
	 *
	 * @since  1.3.4
	 */
	public static function update_notice_data() {

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		self::update_queue_notice_data();
	}

	/**
	 * Updates data for the 'queue' notice.
	 *
	 * @since  1.3.4
	 */
	private static function update_queue_notice_data( $force = false ) {

		if ( ! method_exists( WC(), 'queue' ) ) {
			return;
		}

		$now          = gmdate( 'U' );
		$last_updated = self::get_notice_option( 'queue', 'last_updated', 0 );

		// Time for a check-up?
		if ( $now - $last_updated > DAY_IN_SECONDS ) {

			$has_overdue_deliveries = self::has_overdue_deliveries();

			self::set_notice_option( 'queue', 'last_updated', $now );
			self::set_notice_option( 'queue', 'has_overdue_deliveries', $has_overdue_deliveries ? 'yes' : 'no' );

			if ( $has_overdue_deliveries ) {

				if ( ! class_exists( 'WC_GC_Admin_Notices' ) ) {
					require_once WC_GC_ABSPATH . 'includes/admin/class-wc-gc-admin-notices.php';
				}

				// Add maintenance notice.
				WC_GC_Admin_Notices::add_maintenance_notice( 'queue' );
			}
		}
	}

	/**
	 * Searches for overdue delivery tasks and returns true if any are found.
	 *
	 * @since  1.3.4
	 *
	 * @param  int $time_overdue
	 * @return boolean
	 */
	public static function has_overdue_deliveries( $time_overdue = DAY_IN_SECONDS ) {

		$has_overdue_deliveries = false;
		$pending_deliveries     = WC()->queue()->search(
			array(
				'group'  => 'send_giftcards',
				'status' => ActionScheduler_Store::STATUS_PENDING,
			)
		);

		if ( $pending_deliveries ) {

			// Keep pending deliveries that should have been processed a day ago.
			foreach ( $pending_deliveries as $pending_delivery ) {
				$pending_delivery_date = $pending_delivery->get_schedule()->get_date();
				if ( $pending_delivery_date && $pending_delivery_date->format( 'U' ) < ( gmdate( 'U' ) - $time_overdue ) ) {
					$has_overdue_deliveries = true;
					break;
				}
			}
		}

		return $has_overdue_deliveries;
	}
}

WC_GC_Notices::init();
