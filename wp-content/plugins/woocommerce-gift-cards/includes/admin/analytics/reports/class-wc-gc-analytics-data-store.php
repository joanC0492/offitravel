<?php
/**
 * REST API Reports data store.
 *
 * @package  WooCommerce Gift Cards
 * @since    1.8.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Automattic\WooCommerce\Admin\API\Reports\Cache as ReportsCache;
use Automattic\WooCommerce\Admin\API\Reports\DataStore as ReportsDataStore;
use Automattic\WooCommerce\Admin\API\Reports\DataStoreInterface;
use Automattic\WooCommerce\Admin\API\Reports\TimeInterval;

/**
 * WC_GC_REST_Reports_Bundles_Data_Store class.
 *
 * @version 1.8.0
 */
abstract class WC_GC_Analytics_Data_Store extends ReportsDataStore implements DataStoreInterface {

	/**
	 * Date field name.
	 *
	 * @var string
	 */
	protected $date_field_name = 'date';

	/**
	 * Wrapper around DataStore::get_cached_data().
	 *
	 * @param string $cache_key Cache key.
	 * @return mixed
	 */
	protected function get_cached_data( $cache_key ) {

		$using_object_cache = wp_using_ext_object_cache();
		$transient_version  = WC_Cache_Helper::get_transient_version( 'woocommerce_' . $this->cache_key . '_reports' ) . '_' . ReportsCache::get_version();
		$transient_key      = $using_object_cache ? $cache_key : ( 'wc_report_' . $this->cache_key );
		$transient          = get_transient( $transient_key );

		if ( ! is_array( $transient ) ) {
			return false;
		}

		if ( $using_object_cache ) {

			if ( isset( $transient['value'], $transient['version'] ) && $transient['version'] === $transient_version ) {
				return $transient['value'];
			}
		} elseif ( isset( $transient[ $cache_key ], $transient[ $cache_key ]['value'], $transient[ $cache_key ]['version'] ) && $transient[ $cache_key ]['version'] === $transient_version ) {

				return $transient[ $cache_key ]['value'];
		}

		return false;
	}

	/**
	 * Wrapper around DataStore::set_cached_data().
	 *
	 * @param string $cache_key Cache key.
	 * @param mixed  $value     New value.
	 * @return bool
	 */
	protected function set_cached_data( $cache_key, $value ) {

		if ( $this->should_use_cache() ) {

			$using_object_cache = wp_using_ext_object_cache();
			$transient_key      = $using_object_cache ? $cache_key : ( 'wc_report_' . $this->cache_key );
			$transient_version  = WC_Cache_Helper::get_transient_version( 'woocommerce_' . $this->cache_key . '_reports' ) . '_' . ReportsCache::get_version();

			if ( $using_object_cache ) {

				$transient = array(
					'version' => $transient_version,
					'value'   => $value,
				);

			} else {

				$transient = get_transient( $transient_key );

				// Cache up to 100 items.
				$count = -100;

				if ( ! is_array( $transient ) ) {
					$transient = array();
				}

				$transient_keys = array_keys( $transient );

				// Take the opportunity to clean up stale data.
				foreach ( $transient as $cached_data_key => $cached_data ) {

					if ( ! isset( $cached_data['version'] ) || $cached_data['version'] !== $transient_version ) {
						unset( $transient[ $cached_data_key ] );
					}

					if ( $count > -1 ) {
						unset( $transient[ $transient_keys[ $count ] ] );
					}

					++$count;
				}

				$transient[ $cache_key ] = array(
					'version' => $transient_version,
					'value'   => $value,
				);
			}

			$result = set_transient( $transient_key, $transient, WEEK_IN_SECONDS );

			return $result;
		}

		return true;
	}

	/**
	 * Fills WHERE clause of SQL request with date-related constraints.
	 *
	 * @param array  $query_args Parameters supplied by the user.
	 * @param string $table_name Name of the db table relevant for the date constraint.
	 */
	protected function add_time_period_sql_params( $query_args, $table_name ) {
		$this->clear_sql_clause( array( 'from', 'where_time', 'where' ) );
		if ( isset( $this->subquery ) ) {
			$this->subquery->clear_sql_clause( 'where_time' );
		}

		if ( isset( $query_args['before'] ) && '' !== $query_args['before'] ) {

			$datetime_str = $this->parse_date_for_sql( $query_args['before'] );
			if ( isset( $this->subquery ) ) {
				$this->subquery->add_sql_clause( 'where_time', "AND {$table_name}.`{$this->date_field_name}` <= $datetime_str" );
			} else {
				$this->add_sql_clause( 'where_time', "AND {$table_name}.`{$this->date_field_name}` <= $datetime_str" );
			}
		}
		if ( isset( $query_args['after'] ) && '' !== $query_args['after'] ) {

			$datetime_str = $this->parse_date_for_sql( $query_args['after'] );
			if ( isset( $this->subquery ) ) {
				$this->subquery->add_sql_clause( 'where_time', "AND {$table_name}.`{$this->date_field_name}` >= $datetime_str" );
			} else {
				$this->add_sql_clause( 'where_time', "AND {$table_name}.`{$this->date_field_name}` >= $datetime_str" );
			}
		}
	}

	/**
	 * Fills FROM and WHERE clauses of SQL request for 'Intervals' section of data response based on user supplied parameters.
	 *
	 * @param array  $query_args Parameters supplied by the user.
	 * @param string $table_name Name of the db table relevant for the date constraint.
	 */
	protected function add_intervals_sql_params( $query_args, $table_name ) {
		$this->clear_sql_clause( array( 'from', 'where_time', 'where' ) );

		$this->add_time_period_sql_params( $query_args, $table_name );

		if ( isset( $query_args['interval'] ) && '' !== $query_args['interval'] ) {
			$interval = $query_args['interval'];
			$this->clear_sql_clause( 'select' );
			$this->add_sql_clause( 'select', $this->db_datetime_format( $interval, $table_name ) );
		}
	}

	/**
	 * @override TimeInterval::db_datetime_format().
	 *
	 * Returns date format to be used as grouping clause in SQL.
	 *
	 * @param string $time_interval Time interval.
	 * @param string $table_name Name of the db table relevant for the date constraint.
	 * @return mixed
	 */
	public function db_datetime_format( $time_interval, $table_name ) {
		$first_day_of_week = absint( get_option( 'start_of_week' ) );

		if ( false && 1 === $first_day_of_week ) {
			// Week begins on Monday, ISO 8601.
			$week_format = "FROM_UNIXTIME({$table_name}.`{$this->date_field_name}`, '%x-%v')";
		} else {
			// Week begins on day other than specified by ISO 8601, needs to be in sync with function simple_week_number.
			$week_format = "CONCAT(YEAR(FROM_UNIXTIME({$table_name}.`{$this->date_field_name}`, '%Y-%m-%d')), '-', LPAD( FLOOR( ( DAYOFYEAR(FROM_UNIXTIME({$table_name}.`{$this->date_field_name}`, '%Y-%m-%d')) + ( ( DATE_FORMAT(MAKEDATE(YEAR(FROM_UNIXTIME({$table_name}.`{$this->date_field_name}`,'%Y-%m-%d')),1), '%w') - $first_day_of_week + 7 ) % 7 ) - 1 ) / 7  ) + 1 , 2, '0'))";
		}

		// Whenever this is changed, double check method time_interval_id to make sure they are in sync.
		$mysql_date_format_mapping = array(
			'hour'    => "FROM_UNIXTIME({$table_name}.`{$this->date_field_name}`, '%Y-%m-%d %H')",
			'day'     => "FROM_UNIXTIME({$table_name}.`{$this->date_field_name}`, '%Y-%m-%d')",
			'week'    => $week_format,
			'month'   => "FROM_UNIXTIME({$table_name}.`{$this->date_field_name}`, '%Y-%m')",
			'quarter' => "CONCAT(YEAR(FROM_UNIXTIME({$table_name}.`{$this->date_field_name}`, '%Y-%m-%d' )), '-', QUARTER(FROM_UNIXTIME({$table_name}.`{$this->date_field_name}`, '%Y-%m-%d' )))",
			'year'    => "YEAR(FROM_UNIXTIME({$table_name}.`{$this->date_field_name}`, '%Y-%m-%d' ))",
		);

		return $mysql_date_format_mapping[ $time_interval ];
	}

	/**
	 * Updates the LIMIT query part for Intervals query of the report.
	 *
	 * If there are less records in the database than time intervals, then we need to remap offset in SQL query
	 * to fetch correct records.
	 *
	 * @param array  $query_args Query arguments.
	 * @param int    $db_interval_count Database interval count.
	 * @param int    $expected_interval_count Expected interval count on the output.
	 * @param string $table_name Name of the db table relevant for the date constraint.
	 */
	protected function update_intervals_sql_params( &$query_args, $db_interval_count, $expected_interval_count, $table_name ) {
		if ( $db_interval_count === $expected_interval_count ) {
			return;
		}

		$params   = $this->get_limit_params( $query_args );
		$local_tz = new DateTimeZone( wc_timezone_string() );
		if ( 'date' === strtolower( $query_args['orderby'] ) ) {
			// page X in request translates to slightly different dates in the db, in case some
			// records are missing from the db.
			$start_iteration = 0;
			$end_iteration   = 0;
			if ( 'asc' === strtolower( $query_args['order'] ) ) {
				// ORDER BY date ASC.
				$new_start_date    = $query_args['after'];
				$intervals_to_skip = ( $query_args['page'] - 1 ) * $params['per_page'];
				$latest_end_date   = $query_args['before'];
				for ( $i = 0; $i < $intervals_to_skip; $i++ ) {
					if ( $new_start_date > $latest_end_date ) {
						$new_start_date  = $latest_end_date;
						$start_iteration = 0;
						break;
					}
					$new_start_date = TimeInterval::iterate( $new_start_date, $query_args['interval'] );
					++$start_iteration;
				}

				$new_end_date = clone $new_start_date;
				for ( $i = 0; $i < $params['per_page']; $i++ ) {
					if ( $new_end_date > $latest_end_date ) {
						break;
					}
					$new_end_date = TimeInterval::iterate( $new_end_date, $query_args['interval'] );
					++$end_iteration;
				}
				if ( $new_end_date > $latest_end_date ) {
					$new_end_date  = $latest_end_date;
					$end_iteration = 0;
				}
				if ( $end_iteration ) {
					$new_end_date_timestamp = (int) $new_end_date->format( 'U' ) - 1;
					$new_end_date->setTimestamp( $new_end_date_timestamp );
				}
			} else {
				// ORDER BY date DESC.
				$new_end_date        = $query_args['before'];
				$intervals_to_skip   = ( $query_args['page'] - 1 ) * $params['per_page'];
				$earliest_start_date = $query_args['after'];
				for ( $i = 0; $i < $intervals_to_skip; $i++ ) {
					if ( $new_end_date < $earliest_start_date ) {
						$new_end_date  = $earliest_start_date;
						$end_iteration = 0;
						break;
					}
					$new_end_date = TimeInterval::iterate( $new_end_date, $query_args['interval'], true );
					++$end_iteration;
				}

				$new_start_date = clone $new_end_date;
				for ( $i = 0; $i < $params['per_page']; $i++ ) {
					if ( $new_start_date < $earliest_start_date ) {
						break;
					}
					$new_start_date = TimeInterval::iterate( $new_start_date, $query_args['interval'], true );
					++$start_iteration;
				}
				if ( $new_start_date < $earliest_start_date ) {
					$new_start_date  = $earliest_start_date;
					$start_iteration = 0;
				}
				if ( $start_iteration ) {
					// @todo Is this correct? should it only be added if iterate runs? other two iterate instances, too?
					$new_start_date_timestamp = (int) $new_start_date->format( 'U' ) + 1;
					$new_start_date->setTimestamp( $new_start_date_timestamp );
				}
			}
			// @todo - Do this without modifying $query_args?
			$query_args['adj_after']  = $new_start_date;
			$query_args['adj_before'] = $new_end_date;
			$adj_after                = $this->parse_date_for_sql( $new_start_date );
			$adj_before               = $this->parse_date_for_sql( $new_end_date );
			$this->interval_query->clear_sql_clause( array( 'where_time', 'limit' ) );
			$this->interval_query->add_sql_clause( 'where_time', "AND {$table_name}.`{$this->date_field_name}` <= '$adj_before'" );
			$this->interval_query->add_sql_clause( 'where_time', "AND {$table_name}.`{$this->date_field_name}` >= '$adj_after'" );
			$this->clear_sql_clause( 'limit' );
			$this->add_sql_clause( 'limit', 'LIMIT 0,' . $params['per_page'] );
		} else {
			if ( 'asc' === $query_args['order'] ) {
				$offset = ( ( $query_args['page'] - 1 ) * $params['per_page'] ) - ( $expected_interval_count - $db_interval_count );
				$offset = $offset < 0 ? 0 : $offset;
				$count  = $query_args['page'] * $params['per_page'] - ( $expected_interval_count - $db_interval_count );
				if ( $count < 0 ) {
					$count = 0;
				} elseif ( $count > $params['per_page'] ) {
					$count = $params['per_page'];
				}

				$this->clear_sql_clause( 'limit' );
				$this->add_sql_clause( 'limit', 'LIMIT ' . $offset . ',' . $count );
			}

			// Otherwise no change in limit clause.
			// @todo - Do this without modifying $query_args?
			$query_args['adj_after']  = $query_args['after'];
			$query_args['adj_before'] = $query_args['before'];
		}
	}

	/**
	 * Parse date value to be used in SQL queries.
	 *
	 * @param  mixed $date
	 * @return mixed
	 */
	public function parse_date_for_sql( $date ) {
		$value = '';
		if ( is_a( $date, 'WC_DateTime' ) || is_a( $date, 'DateTime' ) ) {
			$value = $date->getTimestamp();
		} elseif ( wc_gc_is_unix_timestamp( $date ) ) {
			$value = absint( $date );
		} else {
			$value = $date;
		}

		return $value;
	}
}
