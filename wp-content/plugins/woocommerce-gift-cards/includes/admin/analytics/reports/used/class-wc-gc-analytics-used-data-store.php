<?php
/**
 * REST API Reports giftcards used datastore
 *
 * @package  WooCommerce Gift Cards
 * @since    1.8.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Automattic\WooCommerce\Admin\API\Reports\TimeInterval;
use Automattic\WooCommerce\Admin\API\Reports\SqlQuery;

/**
 * WC_GC_Analytics_Revenue_Data_Store class.
 *
 * @version 1.16.9
 */
class WC_GC_Analytics_Used_Data_Store extends WC_GC_Analytics_Data_Store {

	/**
	 * Table used to get the data.
	 *
	 * @var string
	 */
	protected static $table_name = 'woocommerce_gc_activity';

	/**
	 * Cache identifier.
	 *
	 * @var string
	 */
	protected $cache_key = 'giftcards_used';

	/**
	 * Mapping columns to data type to return correct response types.
	 *
	 * @var array
	 */
	protected $column_types = array(
		'date_start'      => 'strval',
		'date_end'        => 'strval',
		'giftcard_id'     => 'intval',
		'giftcard_code'   => 'strval',
		'used_amount'     => 'floatval',
		'refunded_amount' => 'floatval',
		'net_amount'      => 'floatval',
	);

	/**
	 * Extended giftcard attributes to include in the data. @TODO: remove this.
	 *
	 * @var array
	 */
	protected $extended_attributes = array();

	/**
	 * Data store context used to pass to filters.
	 *
	 * @var string
	 */
	protected $context = 'giftcards';

	/**
	 * Assign report columns once full table name has been assigned.
	 */
	protected function assign_report_columns() {
		$table_name           = self::get_db_table_name();
		$this->report_columns = array(
			'giftcard_id'     => 'gc_id as giftcard_id',
			'giftcard_code'   => 'gc_code as giftcard_code',
			'used_amount'     => "SUM( CASE WHEN type IN ('used', 'refund_reversed') THEN {$table_name}.amount END ) as used_amount",
			'refunded_amount' => "SUM( CASE WHEN type IN ('refunded','manually_refunded') THEN {$table_name}.amount END ) as refunded_amount",
			'net_amount'      => "SUM( CASE WHEN type IN ('used', 'refund_reversed') THEN {$table_name}.amount WHEN type IN ('refunded','manually_refunded') THEN -{$table_name}.amount END ) as net_amount",
		);
	}

	/*
	 * Set up all the hooks for maintaining and populating table data.
	 */
	public static function init() {
		// @see WC_GC_Admin_Analytics_Sync
	}

	/**
	 * Updates the database query with parameters used for Products report: categories and order status.
	 *
	 * @param array $query_args Query arguments supplied by the user.
	 */
	protected function add_sql_query_params( $query_args ) {
		global $wpdb;
		$wc_gc_activities_table = self::get_db_table_name();

		$this->add_time_period_sql_params( $query_args, $wc_gc_activities_table );
		$this->get_limit_sql_params( $query_args );
		$this->add_order_by_sql_params( $query_args );
	}

	/**
	 * Returns the report data based on parameters supplied by the user.
	 *
	 * @param array $query_args  Query parameters.
	 * @return stdClass|WP_Error Data.
	 */
	public function get_data( $query_args ) {
		global $wpdb;

		$table_name = self::get_db_table_name();

		// These defaults are only partially applied when used via REST API, as that has its own defaults.
		$defaults = array(
			'per_page'               => get_option( 'posts_per_page' ),
			'page'                   => 1,
			'order'                  => 'DESC',
			'orderby'                => 'date',
			'before'                 => TimeInterval::default_before(),
			'after'                  => TimeInterval::default_after(),
			'fields'                 => '*',
			'giftcard_includes'      => array(),
			'giftcard_code_includes' => array(),
		);

		$query_args = wp_parse_args( $query_args, $defaults );
		$this->normalize_timezones( $query_args, $defaults );

		$cache_key = $this->get_cache_key( $query_args );
		$data      = $this->get_cached_data( $cache_key );

		if ( false === $data ) {

			$this->initialize_queries();

			$data = (object) array(
				'data'    => array(),
				'total'   => 0,
				'pages'   => 0,
				'page_no' => 0,
			);

			$selections = $this->selected_columns( $query_args );
			$params     = $this->get_limit_params( $query_args );
			$this->add_sql_query_params( $query_args );

			$count_query      = "SELECT COUNT(*) FROM (
					{$this->subquery->get_query_statement()}
				) AS tt";
			$db_records_count = (int) $wpdb->get_var(
				$count_query // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			);

			$total_results = $db_records_count;
			$total_pages   = (int) ceil( $db_records_count / $params['per_page'] );

			if ( ( $query_args['page'] < 1 || $query_args['page'] > $total_pages ) ) {
				return $data;
			}

			$this->subquery->clear_sql_clause( 'select' );
			$this->subquery->add_sql_clause( 'select', $selections );
			$this->subquery->add_sql_clause( 'order_by', $this->get_sql_clause( 'order_by' ) );
			$this->subquery->add_sql_clause( 'limit', $this->get_sql_clause( 'limit' ) );
			$giftcards_query = $this->subquery->get_query_statement();

			$giftcard_data = $wpdb->get_results(
				$giftcards_query, // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				ARRAY_A
			);

			if ( null === $giftcard_data ) {
				return $data;
			}

			$giftcard_data = array_map( array( $this, 'cast_numbers' ), $giftcard_data );
			$data          = (object) array(
				'data'    => $giftcard_data,
				'total'   => $total_results,
				'pages'   => $total_pages,
				'page_no' => (int) $query_args['page'],
			);

			$this->set_cached_data( $cache_key, $data );
		}

		return $data;
	}

	/**
	 * Initialize query objects.
	 */
	protected function initialize_queries() {
		$table_name = self::get_db_table_name();
		$this->clear_all_clauses();
		$this->subquery = new SqlQuery( $this->context . '_subquery' );
		$this->subquery->add_sql_clause( 'select', 'gc_id' );
		$this->subquery->add_sql_clause( 'from', $table_name );
		$this->subquery->add_sql_clause( 'where', "AND {$table_name}.`type` IN ( 'used', 'refunded', 'manually_refunded', 'refund_reversed' )" );
		$this->subquery->add_sql_clause( 'group_by', 'gc_id' );
	}
}
