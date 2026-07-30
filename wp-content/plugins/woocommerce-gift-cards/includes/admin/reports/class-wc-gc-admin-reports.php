<?php
/**
 * WC_GC_Admin_Reports class
 *
 * @package  WooCommerce Gift Cards
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Reports Class.
 *
 * @class    WC_GC_Admin_Reports
 * @version  1.16.0
 */
class WC_GC_Admin_Reports {

	/**
	 * Setup Admin class.
	 */
	public static function init() {

		// Add report tab.
		add_filter( 'woocommerce_admin_reports', array( __CLASS__, 'add_reports' ) );
	}

	/**
	 * Adds the reports tab.
	 *
	 * @param  array $reports
	 * @return array
	 */
	public static function add_reports( $reports ) {

		$reports['gc'] = array(
			'title'   => __( 'Gift Cards', 'woocommerce-gift-cards' ),
			'reports' => array(
				'gift_cards' => array(
					'title'       => _x( 'Gift Cards', 'reports title', 'woocommerce-gift-cards' ),
					'description' => '',
					'hide_title'  => true,
					'callback'    => array( __CLASS__, 'get_report' ),
				),
			),
		);

		return $reports;
	}

	/**
	 * Get a report from our reports subfolder.
	 *
	 * @param string $name
	 */
	public static function get_report( $name ) {
		$name  = sanitize_title( str_replace( '_', '-', $name ) );
		$class = 'WC_GC_Report_' . str_replace( '-', '_', $name );

		include_once 'class-wc-gc-report-' . $name . '.php'; // nosemgrep: audit.php.lang.security.file.inclusion-arg

		if ( ! class_exists( $class ) ) {
			return;
		}

		$report = new $class();
		$report->output_report();
	}
}

WC_GC_Admin_Reports::init();
