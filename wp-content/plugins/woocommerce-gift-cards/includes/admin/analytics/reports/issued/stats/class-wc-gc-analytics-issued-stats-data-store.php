<?php
/**
 * REST API Reports Stats data store
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
 * WC_GC_Analytics_Issued_Stats_Data_Store class.
 *
 * @version 2.2.1
 */
class WC_GC_Analytics_Issued_Stats_Data_Store extends WC_GC_Analytics_Issued_Data_Store {

	/**
	 * Cache identifier.
	 *
	 * @var string
	 */
	protected $cache_key = 'giftcards_issued_stats';

	/**
	 * Mapping columns to data type to return correct response types.
	 *
	 * @var array
	 */
	protected $column_types = array(
		'date_start'      => 'strval',
		'date_end'        => 'strval',
		'giftcards_count' => 'intval',
		'issued_balance'  => 'floatval',
	);

	/**
	 * Data store context used to pass to filters.
	 *
	 * @var string
	 */
	protected $context = 'giftcards_issued_stats';

	/**
	 * Assign report columns once full table name has been assigned.
	 */
	protected function assign_report_columns() {
		$table_name = self::get_db_table_name();

		// API fields mapped to sql SELECT statements.
		$this->report_columns = array(
			'giftcards_count' => "COUNT( DISTINCT( `{$table_name}`.`gc_id` ) ) as giftcards_count",
			'issued_balance'  => "SUM( `{$table_name}`.`amount` ) as issued_balance",
		);
	}

	/**
	 * Updates the database query with parameters used for Products Stats report: categories and order status.
	 *
	 * @param  array $query_args
	 * @return void
	 */
	protected function update_sql_query_params( $query_args ) {
		$order_product_lookup_table = self::get_db_table_name();

		$this->add_time_period_sql_params( $query_args, $order_product_lookup_table );
		$this->add_intervals_sql_params( $query_args, $order_product_lookup_table );
		$this->interval_query->add_sql_clause( 'select', $this->get_sql_clause( 'select' ) . ' AS time_interval' );
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
		$defaults   = array(
			'per_page' => get_option( 'posts_per_page' ),
			'page'     => 1,
			'order'    => 'DESC',
			'orderby'  => 'date',
			'before'   => TimeInterval::default_before(),
			'after'    => TimeInterval::default_after(),
			'fields'   => '*',
			'interval' => 'week',
		);
		$query_args = wp_parse_args( $query_args, $defaults );
		$this->normalize_timezones( $query_args, $defaults );

		/*
		 * We need to get the cache key here because
		 * parent::update_intervals_sql_params() modifies $query_args.
		 */
		$cache_key = $this->get_cache_key( $query_args );
		$data      = $this->get_cached_data( $cache_key );

		if ( false === $data ) {
			$this->initialize_queries();

			$selections = $this->selected_columns( $query_args );
			$params     = $this->get_limit_params( $query_args );

			$this->update_sql_query_params( $query_args );
			$this->get_limit_sql_params( $query_args );
			$this->interval_query->add_sql_clause( 'where_time', $this->get_sql_clause( 'where_time' ) );

			$db_intervals = $wpdb->get_col(
				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$this->interval_query->get_query_statement()
			);

			$db_interval_count       = count( $db_intervals );
			$expected_interval_count = TimeInterval::intervals_between( $query_args['after'], $query_args['before'], $query_args['interval'] );
			$total_pages             = (int) ceil( $expected_interval_count / $params['per_page'] );
			if ( $query_args['page'] < 1 || $query_args['page'] > $total_pages ) {
				return array();
			}

			$intervals = array();
			$this->update_intervals_sql_params( $query_args, $db_interval_count, $expected_interval_count, $table_name );
			$this->total_query->add_sql_clause( 'select', $selections );
			$this->total_query->add_sql_clause( 'where_time', $this->get_sql_clause( 'where_time' ) );

			$totals = $wpdb->get_results(
				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$this->total_query->get_query_statement(),
				ARRAY_A
			);

			if ( null === $totals ) {
				return new \WP_Error( 'woocommerce_analytics_giftcards_issued_stats_result_failed', __( 'Sorry, fetching revenue data failed.', 'woocommerce-gift-cards' ) );
			}

			$this->interval_query->add_sql_clause( 'order_by', $this->get_sql_clause( 'order_by' ) );
			$this->interval_query->add_sql_clause( 'limit', $this->get_sql_clause( 'limit' ) );
			$this->interval_query->add_sql_clause( 'select', ", FROM_UNIXTIME( MAX({$table_name}.`{$this->date_field_name}`) ) AS datetime_anchor" );
			if ( '' !== $selections ) {
				$this->interval_query->add_sql_clause( 'select', ', ' . $selections );
			}

			$intervals = $wpdb->get_results(
				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$this->interval_query->get_query_statement(),
				ARRAY_A
			);

			if ( null === $intervals ) {
				return new \WP_Error( 'woocommerce_analytics_giftcards_issued_stats_result_failed', __( 'Sorry, fetching revenue data failed.', 'woocommerce-gift-cards' ) );
			}

			$totals = (object) $this->cast_numbers( $totals[0] );

			$data = (object) array(
				'totals'    => $totals,
				'intervals' => $intervals,
				'total'     => $expected_interval_count,
				'pages'     => $total_pages,
				'page_no'   => (int) $query_args['page'],
			);

			if ( TimeInterval::intervals_missing( $expected_interval_count, $db_interval_count, $params['per_page'], $query_args['page'], $query_args['order'], $query_args['orderby'], count( $intervals ) ) ) {
				$this->fill_in_missing_intervals( $db_intervals, $query_args['adj_after'], $query_args['adj_before'], $query_args['interval'], $data );
				$this->sort_intervals( $data, $query_args['orderby'], $query_args['order'] );
				$this->remove_extra_records( $data, $query_args['page'], $params['per_page'], $db_interval_count, $expected_interval_count, $query_args['orderby'], $query_args['order'] );
			} else {
				$this->update_interval_boundary_dates( $query_args['after'], $query_args['before'], $query_args['interval'], $data->intervals );
			}

			$this->create_interval_subtotals( $data->intervals );

			$this->set_cached_data( $cache_key, $data );
		}

		return $data;
	}

	/**
	 * Normalizes order_by clause to match to SQL query.
	 *
	 * @param  string $order_by Order by option requested by user.
	 * @return string
	 */
	protected function normalize_order_by( $order_by ) {
		if ( 'date' === $order_by ) {
			return 'time_interval';
		}

		return $order_by;
	}

	/**
	 * Initialize query objects.
	 */
	protected function initialize_queries() {
		$this->clear_all_clauses();
		unset( $this->subquery );
		$this->total_query = new SqlQuery( $this->context . '_total' );
		$this->total_query->add_sql_clause( 'from', self::get_db_table_name() );
		$this->total_query->add_sql_clause( 'where', 'AND type="issued"' );

		$this->interval_query = new SqlQuery( $this->context . '_interval' );
		$this->interval_query->add_sql_clause( 'from', self::get_db_table_name() );
		$this->interval_query->add_sql_clause( 'where', 'AND type="issued"' );
		$this->interval_query->add_sql_clause( 'group_by', 'time_interval' );
	}
}
