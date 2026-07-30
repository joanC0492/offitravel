<?php
/**
 * WC_GC_Tracker class
 *
 * @package  WooCommerce Gift Cards
 * @since    1.12.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gift Cards Tracker.
 *
 * @class    WC_GC_Tracker
 * @version  2.2.1
 */
class WC_GC_Tracker {

	/**
	 * Property to store reusable query data.
	 *
	 * @since 1.15.4
	 * @var   array
	 */
	private static $reusable_data = array();

	/**
	 * Property to store and share tracking data in the class.
	 *
	 * @since 1.15.4
	 * @var   array
	 */
	private static $data = array();

	/**
	 * Property to store previously calculated data.
	 * We need it to retain and not recalculate parts of the data.
	 *
	 * @since 1.15.4
	 * @var   array
	 */
	private static $calculated_data = array();

	/**
	 * Property to store if we should retain the previously calculated data.
	 *
	 * @since 1.15.4
	 * @var   boolean
	 */
	private static $should_retain_calculated_data = false;

	/**
	 * Property to store the starting time of the process.
	 *
	 * @since 1.15.4
	 * @var   int
	 */
	private static $start_time = 0;

	/**
	 * Property to store the tracking events.
	 *
	 * @since 1.15.4
	 * @var   array
	 */
	private static $tracking_events = array();

	/**
	 * Property to store the HPOS table name.
	 *
	 * @since 1.15.4
	 * @var   string
	 */
	private static $hpos_orders_table = '';

	/**
	 * Property to store how often the data will be invalidated.
	 *
	 * @since 1.15.4
	 * @var   string
	 */
	private static $invalidation_interval = '-1 week';

	/**
	 * Initialize the Tracker.
	 */
	public static function init() {
		if ( 'yes' === get_option( 'woocommerce_allow_tracking', 'no' ) ) {
			add_filter( 'woocommerce_tracker_data', array( __CLASS__, 'add_tracking_data' ), 10 );

			// Async tasks.
			if ( defined( 'WC_CALYPSO_BRIDGE_TRACKER_FREQUENCY' ) ) {
				add_action( 'wc_gc_hourly', array( __CLASS__, 'maybe_calculate_tracking_data' ) );
			} else {
				add_action( 'wc_gc_daily', array( __CLASS__, 'maybe_calculate_tracking_data' ) );
			}
		}
	}

	/**
	 * Adds GC data to the tracked data.
	 *
	 * @param  array $data
	 * @return array all the tracking data.
	 */
	public static function add_tracking_data( $data ) {
		$data['extensions']['wc_gc'] = self::get_tracking_data();
		return $data;
	}

	/**
	 * Get all tracking data from cache.
	 *
	 * @return array All the tracking data.
	 */
	protected static function get_tracking_data() {
		self::read_data();
		self::maybe_initialize_data();

		// if there are no data calculated, it will calculate them and then send the data.
		if ( self::has_pending_calculations() ) {
			return array();
		}

		if ( isset( self::$data['info']['started_time'] ) ) {
			unset( self::$data['info']['started_time'] );
		}

		return self::$data;
	}

	/**
	 * Calculates all tracking-related data.
	 * Runs independently in a background task.
	 *
	 * @see ::maybe_calculate_tracking_data().
	 */
	protected static function calculate_tracking_data() {
		self::set_start_time();
		self::calculate_settings_data();
		self::calculate_giftcards_data();
		self::calculate_orders_data();
	}

	/**
	 * Maybe calculate orders data. Also, handles the caching strategy.
	 *
	 * @return bool Returns true if the data are re-calculated, false otherwise.
	 */
	public static function maybe_calculate_tracking_data() {

		self::read_data();
		self::maybe_initialize_data();

		// Let's check if the array has pending data to calculate.
		if ( self::has_pending_calculations() ) {

			self::calculate_tracking_data();
			self::increase_iterations();
			self::set_option_data();

			return true;
		}

		return false;
	}

	/**
	 * Calculate settings data.
	 *
	 * @since  1.15.4
	 * @return void
	 */
	private static function calculate_settings_data() {

		$data = &self::$data['settings'];

		if ( isset( $data['pending'] ) ) {
			$bcc_recipients = get_option( 'wc_gc_bcc_recipients' );

			$data = array(
				'account_features'               => 'yes' === get_option( 'wc_gc_is_redeeming_enabled', 'yes' ) ? 'on' : 'off',
				'cart_features'                  => 'yes' === get_option( 'wc_gc_disable_cart_ui', 'yes' ) ? 'off' : 'on',
				'allow_coupons_with_gift_cards'  => 'yes' === get_option( 'wc_gc_disable_coupons_with_gift_cards', 'no' ) ? 'off' : 'on',
				'allow_multiple_recipients'      => 'yes' !== get_option( 'wc_gc_allow_multiple_recipients', 'no' ) ? 'off' : 'on',
				'bcc_recipients'                 => ! empty( $bcc_recipients ) ? 'on' : 'off',
				'unmask_codes_for_shop_managers' => 'yes' !== get_option( 'wc_gc_unmask_codes_for_shop_managers', 'no' ) ? 'off' : 'on',
				'send_as_gift_status'            => wc_gc_sag_get_send_as_gift_status(),
				'expiration_reminders'           => 'yes' === get_option( 'wc_gc_expiration_reminders_enabled', 'no' ) ? 'on' : 'off',
			);
		}
	}

	/**
	 * Calculate gift cards data.
	 *
	 * The following data are calculated:
	 * - first_issued_date
	 * - product_first_create_date
	 * - issued_previous_month
	 * - issued_previous_year
	 * - issued_total
	 * - issued_revenue_previous_month
	 * - issued_revenue_previous_year
	 * - issued_revenue_total
	 * - redeemed_revenue_total
	 * - used_revenue_total
	 * - used_total
	 *
	 * @since  1.15.4
	 * @return void
	 */
	private static function calculate_giftcards_data() {
		global $wpdb;

		$data              = &self::$data['giftcards'];
		$hpos_orders_table = self::$hpos_orders_table;

		// Retain the calculated ones, if they exist and if we should retain them (on month change).
		if ( self::$should_retain_calculated_data && ! empty( self::$calculated_data['giftcards'] ) ) {
			$calculated_data        = self::$calculated_data['giftcards'];
			$retain_calculated_keys = array(
				'issued_previous_month',
				'issued_previous_year',
				'issued_revenue_previous_month',
				'issued_revenue_previous_year',
			);

			foreach ( $retain_calculated_keys as $key ) {
				if ( isset( $calculated_data[ $key ] ) ) {
					$data[ $key ] = $calculated_data[ $key ];
				}
			}
		}

		// Orders - Date of the first order in store that used gift cards for payment.
		if ( ! isset( $data['first_issued_date'] ) ) {
			$first_date = (int) $wpdb->get_var(
				"
				SELECT min(`date`) as `min_date`
				FROM `{$wpdb->prefix}woocommerce_gc_activity` as `activities`
				WHERE `type` = 'issued'
			"
			);

			$data['first_issued_date'] = $first_date ? gmdate( 'Y-m-d H:i:s', $first_date ) : null;

			if ( self::time_or_memory_exceeded() ) {
				return;
			}
		}

		// Creation date of first gift card product.
		if ( ! isset( $data['product_first_create_date'] ) ) {

			// @see maybe_initialize_data() for tracking events default values.
			if ( null === self::$tracking_events['giftcards_product_first_create_date'] ) {
				self::$tracking_events['giftcards_product_first_create_date'] = $wpdb->get_var(
					"
					SELECT
						`post_date_gmt`
					FROM
						`{$wpdb->prefix}posts` AS `posts`
						INNER JOIN `{$wpdb->prefix}postmeta` AS `postmeta` ON `postmeta`.`post_id` = `posts`.`ID`
					WHERE
						`posts`.`post_type` = 'product'
						AND `posts`.`post_status` = 'publish'
						AND `postmeta`.`meta_key` = '_gift_card'
						AND `postmeta`.`meta_value` = 'yes'
					ORDER BY
						`post_date_gmt` ASC
					LIMIT 1
				"
				);

				update_option( 'woocommerce_gc_tracking_events', self::$tracking_events );
			}

			$data['product_first_create_date'] = self::$tracking_events['giftcards_product_first_create_date'];

			if ( self::time_or_memory_exceeded() ) {
				return;
			}
		}

		// Gift cards issued previous month.
		if ( ! isset( $data['issued_previous_month'] ) ) {
			$giftcards_issued_previous_month_array = self::get_reusable_data( 'giftcards_issued_previous_month_array' );
			$data['issued_previous_month']         = (int) $giftcards_issued_previous_month_array['count'];

			if ( self::time_or_memory_exceeded() ) {
				return;
			}
		}

		// Gift cards issued previous year.
		if ( ! isset( $data['issued_previous_year'] ) ) {
			$giftcards_issued_previous_year_array = self::get_reusable_data( 'giftcards_issued_previous_year_array' );
			$data['issued_previous_year']         = (int) $giftcards_issued_previous_year_array['count'];

			if ( self::time_or_memory_exceeded() ) {
				return;
			}
		}

		// Number of gift cards issued over time.
		if ( ! isset( $data['issued_total'] ) ) {
			$giftcards_issued_total_array = self::get_reusable_data( 'giftcards_issued_total_array' );
			$data['issued_total']         = (int) $giftcards_issued_total_array['count'];

			if ( self::time_or_memory_exceeded() ) {
				return;
			}
		}

		// Gift cards balance issued previous month.
		if ( ! isset( $data['issued_revenue_previous_month'] ) ) {
			$giftcards_issued_previous_month_array = self::get_reusable_data( 'giftcards_issued_previous_month_array' );
			$data['issued_revenue_previous_month'] = (float) $giftcards_issued_previous_month_array['amount'];

			if ( self::time_or_memory_exceeded() ) {
				return;
			}
		}

		// Gift cards balance issued previous year.
		if ( ! isset( $data['issued_revenue_previous_year'] ) ) {
			$giftcards_issued_previous_year_array = self::get_reusable_data( 'giftcards_issued_previous_year_array' );
			$data['issued_revenue_previous_year'] = (float) $giftcards_issued_previous_year_array['amount'];

			if ( self::time_or_memory_exceeded() ) {
				return;
			}
		}

		// Balance of issued gift cards over time.
		if ( ! isset( $data['issued_revenue_total'] ) ) {
			$giftcards_issued_total_array = self::get_reusable_data( 'giftcards_issued_total_array' );
			$data['issued_revenue_total'] = (float) $giftcards_issued_total_array['amount'];

			if ( self::time_or_memory_exceeded() ) {
				return;
			}
		}

		// Balance of gift cards stored in customer accounts over time.
		if ( ! isset( $data['redeemed_revenue_total'] ) ) {
			$data['redeemed_revenue_total'] = (float) $wpdb->get_var(
				"
				SELECT
					SUM( `activities`.`amount` ) AS `amount`
				FROM
					`{$wpdb->prefix}woocommerce_gc_activity` AS `activities`
				WHERE
					`activities`.`type` = 'redeemed'
			"
			);

			if ( self::time_or_memory_exceeded() ) {
				return;
			}
		}

		// Revenue from redeemed gift cards over time
		if ( ! isset( $data['used_revenue_total'] ) ) {

			$data['used_revenue_total'] = (float) WC_GC()->db->activity->get_gift_card_captured_amount(
				array(
					'exclude_statuses' => array( 'cancelled', 'pending', 'failed', 'on-hold', 'checkout-draft', 'trash' ),
				)
			);

			if ( self::time_or_memory_exceeded() ) {
				return;
			}
		}

		// Number of gift cards redeemed over time (type = used).
		if ( ! isset( $data['used_total'] ) ) {
			if ( WC_GC_Core_Compatibility::is_hpos_enabled() ) {
				$data['used_total'] = (int) $wpdb->get_var(
					$wpdb->prepare(
						"
					SELECT
					    COUNT( DISTINCT( `activities`.`gc_id` ) )
					FROM
						`{$wpdb->prefix}woocommerce_gc_activity` AS `activities`
						INNER JOIN %i AS `orders` ON `activities`.`object_id` = `orders`.`id`
					WHERE
						`activities`.`type` = 'used'
						AND `orders`.`status` NOT IN( 'wc-cancelled', 'wc-pending', 'wc-failed', 'wc-on-hold', 'wc-checkout-draft', 'wc-trash' )
						AND `orders`.`type` = 'shop_order'
				",
						$hpos_orders_table
					)
				);
			} else {
				$data['used_total'] = (int) $wpdb->get_var(
					"
					SELECT
					    COUNT( DISTINCT( `activities`.`gc_id` ) )
					FROM
						`{$wpdb->prefix}woocommerce_gc_activity` AS `activities`
						INNER JOIN `{$wpdb->posts}` AS `orders` ON `activities`.`object_id` = `orders`.`ID`
					WHERE
						`activities`.`type` = 'used'
						AND `orders`.`post_status` NOT IN( 'wc-cancelled', 'wc-pending', 'wc-failed', 'wc-on-hold', 'wc-checkout-draft', 'wc-trash' )
						AND `orders`.`post_type` = 'shop_order'
				"
				);
			}

			if ( self::time_or_memory_exceeded() ) {
				// If we don't unset now, it would exit and would need
				// an additional run just to remove the pending flag.
				unset( $data['pending'] );
				return;
			}
		}

		unset( $data['pending'] );
	}

	/**
	 * Calculate orders data.
	 *
	 * The following data is calculated:
	 * - balance_revenue_previous_month
	 * - revenue_previous_month
	 * - balance_revenue_previous_month_percent
	 * - balance_revenue_previous_year
	 * - revenue_previous_year
	 * - balance_revenue_previous_year_percent
	 * - count_with_balance_previous_month
	 * - count_previous_month
	 * - usage_previous_month_percent
	 * - count_with_balance_previous_year
	 * - count_previous_year
	 * - usage_previous_year_percent
	 * - orders_in_multiple_currencies
	 * - first_order_with_giftcard_date
	 * - count_with_giftcard_total
	 *
	 * @since  1.15.4
	 * @return void
	 */
	private static function calculate_orders_data() {
		global $wpdb;

		$data              = &self::$data['orders'];
		$hpos_orders_table = self::$hpos_orders_table;

		// Retain the calculated ones, if they exist and if we should retain them (on month change).
		if ( self::$should_retain_calculated_data && ! empty( self::$calculated_data['orders'] ) ) {
			$calculated_data        = self::$calculated_data['orders'];
			$retain_calculated_keys = array(
				'balance_revenue_previous_month',
				'revenue_previous_month',
				'balance_revenue_previous_month_percent',
				'balance_revenue_previous_year',
				'revenue_previous_year',
				'balance_revenue_previous_year_percent',
				'count_with_balance_previous_month',
				'count_previous_month',
				'usage_previous_month_percent',
				'count_with_balance_previous_year',
				'count_previous_year',
				'usage_previous_year_percent',
			);

			foreach ( $retain_calculated_keys as $key ) {
				if ( isset( $calculated_data[ $key ] ) ) {
					$data[ $key ] = $calculated_data[ $key ];
				}
			}
		}

		// Monthly balance usage.
		if ( ! isset( $data['balance_revenue_previous_month'] ) ) {
			$data['balance_revenue_previous_month'] = self::get_reusable_data( 'orders_balance_revenue_previous_month' );

			if ( self::time_or_memory_exceeded() ) {
				return;
			}
		}

		// Monthly balance usage.
		if ( ! isset( $data['revenue_previous_month'] ) ) {
			$total_revenue_previous_month   = self::get_reusable_data( 'orders_total_revenue_previous_month' );
			$balance_revenue_previous_month = self::get_reusable_data( 'orders_balance_revenue_previous_month' );

			$data['revenue_previous_month'] = $total_revenue_previous_month + $balance_revenue_previous_month;

			if ( self::time_or_memory_exceeded() ) {
				return;
			}
		}

		// Monthly balance usage.
		if ( ! isset( $data['balance_revenue_previous_month_percent'] ) ) {
			$total_revenue_previous_month   = self::get_reusable_data( 'orders_total_revenue_previous_month' );
			$balance_revenue_previous_month = self::get_reusable_data( 'orders_balance_revenue_previous_month' );

			$data['balance_revenue_previous_month_percent'] = ( $total_revenue_previous_month + $balance_revenue_previous_month > 0 )
				? round( $balance_revenue_previous_month / ( $total_revenue_previous_month + $balance_revenue_previous_month ) * 100, 4 )
				: 0;

			if ( self::time_or_memory_exceeded() ) {
				return;
			}
		}

		// Yearly balance usage.
		if ( ! isset( $data['balance_revenue_previous_year'] ) ) {
			$data['balance_revenue_previous_year'] = self::get_reusable_data( 'orders_balance_revenue_previous_year' );

			if ( self::time_or_memory_exceeded() ) {
				return;
			}
		}

		// Yearly balance usage.
		if ( ! isset( $data['revenue_previous_year'] ) ) {
			$total_revenue_previous_year   = self::get_reusable_data( 'orders_total_revenue_previous_year' );
			$balance_revenue_previous_year = self::get_reusable_data( 'orders_balance_revenue_previous_year' );

			$data['revenue_previous_year'] = $total_revenue_previous_year + $balance_revenue_previous_year;

			if ( self::time_or_memory_exceeded() ) {
				return;
			}
		}

		// Yearly balance usage.
		if ( ! isset( $data['balance_revenue_previous_year_percent'] ) ) {
			$total_revenue_previous_year   = self::get_reusable_data( 'orders_total_revenue_previous_year' );
			$balance_revenue_previous_year = self::get_reusable_data( 'orders_balance_revenue_previous_year' );

			$data['balance_revenue_previous_year_percent'] = ( $total_revenue_previous_year + $balance_revenue_previous_year > 0 )
				? round( $balance_revenue_previous_year / ( $total_revenue_previous_year + $balance_revenue_previous_year ) * 100, 4 )
				: 0;

			if ( self::time_or_memory_exceeded() ) {
				return;
			}
		}

		// Monthly usage.
		if ( ! isset( $data['count_with_balance_previous_month'] ) ) {
			$data['count_with_balance_previous_month'] = self::get_reusable_data( 'orders_count_with_balance_previous_month' );

			if ( self::time_or_memory_exceeded() ) {
				return;
			}
		}

		// Monthly usage.
		if ( ! isset( $data['count_previous_month'] ) ) {
			$data['count_previous_month'] = self::get_reusable_data( 'orders_count_previous_month' );

			if ( self::time_or_memory_exceeded() ) {
				return;
			}
		}

		// Monthly usage.
		if ( ! isset( $data['usage_previous_month_percent'] ) ) {
			$total_orders_previous_month              = self::get_reusable_data( 'orders_count_previous_month' );
			$total_orders_with_balance_previous_month = self::get_reusable_data( 'orders_count_with_balance_previous_month' );

			$data['usage_previous_month_percent'] = ! empty( $total_orders_previous_month )
				? round( $total_orders_with_balance_previous_month / $total_orders_previous_month * 100, 4 )
				: 0;

			if ( self::time_or_memory_exceeded() ) {
				return;
			}
		}

		// Yearly usage.
		if ( ! isset( $data['count_with_balance_previous_year'] ) ) {
			$data['count_with_balance_previous_year'] = self::get_reusable_data( 'orders_count_with_balance_previous_year' );

			if ( self::time_or_memory_exceeded() ) {
				return;
			}
		}

		// Yearly usage.
		if ( ! isset( $data['count_previous_year'] ) ) {
			$data['count_previous_year'] = self::get_reusable_data( 'orders_count_previous_year' );

			if ( self::time_or_memory_exceeded() ) {
				return;
			}
		}

		// Yearly usage.
		if ( ! isset( $data['usage_previous_year_percent'] ) ) {
			$total_orders_previous_year              = self::get_reusable_data( 'orders_count_previous_year' );
			$total_orders_with_balance_previous_year = self::get_reusable_data( 'orders_count_with_balance_previous_year' );

			$data['usage_previous_year_percent'] = ! empty( $total_orders_previous_year )
				? round( $total_orders_with_balance_previous_year / $total_orders_previous_year * 100, 4 )
				: 0;

			if ( self::time_or_memory_exceeded() ) {
				return;
			}
		}

		// Multi-currency data.
		if ( ! isset( $data['orders_in_multiple_currencies'] ) ) {

			if ( WC_GC_Core_Compatibility::is_hpos_enabled() ) {
				$orders_currencies_count = (int) $wpdb->get_var(
					$wpdb->prepare(
						'
					SELECT COUNT( DISTINCT( `currency` ) )
					FROM %i AS `orders`
				',
						$hpos_orders_table
					)
				);
			} else {
				$orders_currencies_count = (int) $wpdb->get_var(
					"
					SELECT COUNT( DISTINCT( `meta_value` ) )
					FROM `{$wpdb->postmeta}` AS `orders_meta`
					WHERE `orders_meta`.`meta_key` = '_order_currency'
				"
				);
			}

			$data['orders_in_multiple_currencies'] = ( $orders_currencies_count > 1 ) ? true : false;

			if ( self::time_or_memory_exceeded() ) {
				return;
			}
		}

		// Purchase date of the first order with an applied gift card.
		if ( ! isset( $data['first_order_with_giftcard_date'] ) ) {
			if ( WC_GC_Core_Compatibility::is_hpos_enabled() ) {
				$data['first_order_with_giftcard_date'] = $wpdb->get_var(
					$wpdb->prepare(
						"
					SELECT
						`orders`.`date_created_gmt`
					FROM
						`{$wpdb->prefix}woocommerce_gc_activity` AS `activities`
						INNER JOIN %i AS `orders` ON `activities`.`object_id` = `orders`.`id`
					WHERE
						`activities`.`type` = 'used'
						AND `orders`.`status` NOT IN( 'wc-cancelled', 'wc-pending', 'wc-failed', 'wc-on-hold', 'wc-checkout-draft', 'wc-trash' )
					ORDER BY
						`orders`.`date_created_gmt` ASC
					LIMIT 1
				",
						$hpos_orders_table
					)
				);
			} else {
				$data['first_order_with_giftcard_date'] = $wpdb->get_var(
					"
					SELECT
						`orders`.`post_date_gmt`
					FROM
						`{$wpdb->prefix}woocommerce_gc_activity` AS `activities`
						INNER JOIN `{$wpdb->posts}` AS `orders` ON `activities`.`object_id` = `orders`.`ID`
					WHERE
						`activities`.`type` = 'used'
						AND `orders`.`post_status` NOT IN( 'wc-cancelled', 'wc-pending', 'wc-failed', 'wc-on-hold', 'wc-checkout-draft', 'wc-trash' )
					ORDER BY
						`orders`.`post_date_gmt` ASC
					LIMIT 1
				"
				);
			}

			if ( self::time_or_memory_exceeded() ) {
				return;
			}
		}

		// Orders with redeemed gift cards over time. (used)
		if ( ! isset( $data['count_with_giftcard_total'] ) ) {
			if ( WC_GC_Core_Compatibility::is_hpos_enabled() ) {
				$data['count_with_giftcard_total'] = (int) $wpdb->get_var(
					$wpdb->prepare(
						"
					SELECT
					    COUNT( DISTINCT( `orders`.`id` ) )
					FROM
						`{$wpdb->prefix}woocommerce_gc_activity` AS `activities`
						INNER JOIN %i AS `orders` ON `activities`.`object_id` = `orders`.`id`
					WHERE
						`activities`.`type` = 'used'
						AND `orders`.`status` NOT IN('wc-cancelled', 'wc-pending', 'wc-failed', 'wc-on-hold', 'wc-checkout-draft', 'wc-trash')
						AND `orders`.`type` = 'shop_order'
				",
						$hpos_orders_table
					)
				);
			} else {
				$data['count_with_giftcard_total'] = (int) $wpdb->get_var(
					"
					SELECT
					    COUNT( DISTINCT( `orders`.`ID` ) )
					FROM
						`{$wpdb->prefix}woocommerce_gc_activity` AS `activities`
						INNER JOIN `{$wpdb->posts}` AS `orders` ON `activities`.`object_id` = `orders`.`ID`
					WHERE
						`activities`.`type` = 'used'
						AND `orders`.`post_status` NOT IN('wc-cancelled', 'wc-pending', 'wc-failed', 'wc-on-hold', 'wc-checkout-draft', 'wc-trash')
						AND `orders`.`post_type` = 'shop_order'
				"
				);
			}

			if ( self::time_or_memory_exceeded() ) {
				// If we don't unset now, it would exit and would need
				// an additional run just to remove the pending flag.
				unset( $data['pending'] );
				return;
			}
		}

		unset( $data['pending'] );
	}

	/**
	 * Get dates.
	 *
	 * @param  string   $time_period
	 * @param  DateTime $reference_date  (Optional) Date to act as reference for calculating the previous month, year. Defaults to 'now'.
	 * @return array Array of DateTime objects.
	 */
	protected static function get_dates( $time_period, $reference_date = null ) {

		if ( ! in_array( $time_period, array( 'previous_month', 'previous_year' ) ) ) {
			return array();
		}

		$today = is_a( $reference_date, 'DateTime' ) ? $reference_date : new DateTime();
		$today->setTime( 0, 0, 0 );

		switch ( $time_period ) {

			case 'previous_month':
				$ref       = $today->setDate( $today->format( 'Y' ), $today->format( 'm' ), 1 );
				$last_day  = $ref->sub( new DateInterval( 'P1D' ) );
				$first_day = clone $last_day;
				$first_day->setDate( $last_day->format( 'Y' ), $last_day->format( 'm' ), 1 );
				$last_day->setTime( 23, 59, 59 );

				break;

			case 'previous_year':
				$ref       = $today->setDate( $today->format( 'Y' ), 1, 1 );
				$last_day  = $ref->sub( new DateInterval( 'P1D' ) );
				$first_day = clone $last_day;
				$first_day->setDate( $last_day->format( 'Y' ), 1, 1 );
				$last_day->setTime( 23, 59, 59 );

				break;
		}

		return array(
			'start' => $first_day,
			'end'   => $last_day,
		);
	}

	/**
	 * Get any reusable data, without re-querying the DB.
	 *
	 * @since  1.15.4
	 * @param  array $key  Reusable data key.
	 * @return mixed
	 */
	private static function get_reusable_data( $key = '' ) {

		$valid_keys = array(
			'giftcards_issued_previous_month_array',
			'giftcards_issued_previous_year_array',
			'giftcards_issued_total_array',
			'orders_balance_revenue_previous_month',
			'orders_total_revenue_previous_month',
			'orders_balance_revenue_previous_year',
			'orders_total_revenue_previous_year',
			'orders_count_with_balance_previous_month',
			'orders_count_previous_month',
			'orders_count_with_balance_previous_year',
			'orders_count_previous_year',
		);

		if ( ! in_array( $key, $valid_keys ) ) {
			$notice = sprintf( __( 'Invalid key &quot;%1$s&quot; passed to get_reusable_data.', 'woocommerce-gift-cards' ), $key );
			throw new Exception( $notice );
		}

		// Check if the specific data key is already calculated and bail out early.
		if ( isset( self::$reusable_data[ $key ] ) ) {
			return self::$reusable_data[ $key ];
		}

		global $wpdb;

		if ( WC_GC_Core_Compatibility::is_hpos_enabled() ) {
			$hpos_orders_table = Automattic\WooCommerce\Internal\DataStores\Orders\OrdersTableDataStore::get_orders_table_name();
		}

		$previous_month_dates = self::get_dates( 'previous_month' );
		$previous_year_dates  = self::get_dates( 'previous_year' );

		// Calculate and set reusable data.
		if ( $key === 'giftcards_issued_previous_month_array' ) {
			self::$reusable_data['giftcards_issued_previous_month_array'] = $wpdb->get_row(
				$wpdb->prepare(
					"
				SELECT
					COUNT( `activities`.`gc_id` ) AS `count`,
					SUM( `activities`.`amount` ) AS `amount`
				FROM
					`{$wpdb->prefix}woocommerce_gc_activity` AS `activities`
				WHERE
					`activities`.`type` = 'issued'
					AND `activities`.`date` >= %d
					AND `activities`.`date` < %d
				",
					$previous_month_dates['start']->getTimestamp(),
					$previous_month_dates['end']->getTimestamp()
				),
				ARRAY_A
			);

		} elseif ( $key === 'giftcards_issued_previous_year_array' ) {
			self::$reusable_data['giftcards_issued_previous_year_array'] = $wpdb->get_row(
				$wpdb->prepare(
					"
				SELECT
					COUNT( `activities`.`gc_id` ) AS `count`,
					SUM( `activities`.`amount` ) AS `amount`
				FROM
					`{$wpdb->prefix}woocommerce_gc_activity` AS `activities`
				WHERE
					`activities`.`type` = 'issued'
					AND `activities`.`date` >= %d
					AND `activities`.`date` < %d
				",
					$previous_year_dates['start']->getTimestamp(),
					$previous_year_dates['end']->getTimestamp()
				),
				ARRAY_A
			);
		} elseif ( $key === 'giftcards_issued_total_array' ) {
			self::$reusable_data['giftcards_issued_total_array'] = $wpdb->get_row(
				"
				SELECT
					COUNT( `activities`.`gc_id` ) AS `count`,
					SUM( `activities`.`amount` ) AS `amount`
				FROM
					`{$wpdb->prefix}woocommerce_gc_activity` AS `activities`
				WHERE
					`activities`.`type` = 'issued'
				",
				ARRAY_A
			);
		} elseif ( $key === 'orders_balance_revenue_previous_month' ) {
			self::$reusable_data['orders_balance_revenue_previous_month'] = (float) WC_GC()->db->activity->get_gift_card_captured_amount(
				array(
					'date_start'       => $previous_month_dates['start']->getTimestamp(),
					'date_end'         => $previous_month_dates['end']->getTimestamp(),
					'exclude_statuses' => array( 'cancelled', 'pending', 'failed', 'on-hold', 'checkout-draft', 'trash' ),
				)
			);
		} elseif ( $key === 'orders_total_revenue_previous_month' ) {
			if ( WC_GC_Core_Compatibility::is_hpos_enabled() ) {
				self::$reusable_data['orders_total_revenue_previous_month'] = (float) $wpdb->get_var(
					$wpdb->prepare(
						"
					SELECT SUM( `orders`.`total_amount` )
					FROM %i AS `orders`
					WHERE `orders`.`date_created_gmt` >= %s
					AND `orders`.`date_created_gmt` <= %s
					AND `orders`.`status` NOT IN ( 'wc-cancelled', 'wc-pending', 'wc-failed', 'wc-on-hold', 'wc-checkout-draft', 'wc-trash' )
					AND `orders`.`type` = 'shop_order'
			",
						$hpos_orders_table,
						$previous_month_dates['start']->format( 'Y-m-d H:i:s' ),
						$previous_month_dates['end']->format( 'Y-m-d H:i:s' )
					)
				);
			} else {
				self::$reusable_data['orders_total_revenue_previous_month'] = (float) $wpdb->get_var(
					$wpdb->prepare(
						"
					SELECT SUM( `order_total`.`meta_value` )
					FROM `{$wpdb->posts}` AS `orders`
					LEFT JOIN `{$wpdb->prefix}postmeta` AS `order_total` ON `order_total`.`post_id` = `orders`.`ID`
					WHERE `orders`.`post_date_gmt` >= %s
					AND `orders`.`post_date_gmt` <= %s
					AND `orders`.`post_status` NOT IN ( 'wc-cancelled', 'wc-pending', 'wc-failed', 'wc-on-hold', 'wc-checkout-draft', 'wc-trash' )
					AND `order_total`.`meta_key` = '_order_total'
					AND `orders`.`post_type` = 'shop_order'
			",
						$previous_month_dates['start']->format( 'Y-m-d H:i:s' ),
						$previous_month_dates['end']->format( 'Y-m-d H:i:s' )
					)
				);
			}
		} elseif ( $key === 'orders_balance_revenue_previous_year' ) {
			self::$reusable_data['orders_balance_revenue_previous_year'] = (float) WC_GC()->db->activity->get_gift_card_captured_amount(
				array(
					'date_start'       => $previous_year_dates['start']->getTimestamp(),
					'date_end'         => $previous_year_dates['end']->getTimestamp(),
					'exclude_statuses' => array( 'cancelled', 'pending', 'failed', 'on-hold', 'checkout-draft', 'trash' ),
				)
			);
		} elseif ( $key === 'orders_total_revenue_previous_year' ) {
			if ( WC_GC_Core_Compatibility::is_hpos_enabled() ) {
				self::$reusable_data['orders_total_revenue_previous_year'] = (float) $wpdb->get_var(
					$wpdb->prepare(
						"
					SELECT SUM( `orders`.`total_amount` )
					FROM %i AS `orders`
					WHERE `orders`.`date_created_gmt` >= %s
					AND `orders`.`date_created_gmt` <= %s
					AND `orders`.`status` NOT IN ( 'wc-cancelled', 'wc-pending', 'wc-failed', 'wc-on-hold', 'wc-checkout-draft', 'wc-trash' )
					AND `orders`.`type` = 'shop_order'
			",
						$hpos_orders_table,
						$previous_year_dates['start']->format( 'Y-m-d H:i:s' ),
						$previous_year_dates['end']->format( 'Y-m-d H:i:s' )
					)
				);
			} else {
				self::$reusable_data['orders_total_revenue_previous_year'] = (float) $wpdb->get_var(
					$wpdb->prepare(
						"
					SELECT SUM( `order_total`.`meta_value` )
					FROM `{$wpdb->posts}` AS `orders`
					LEFT JOIN `{$wpdb->prefix}postmeta` AS `order_total` ON `order_total`.`post_id` = `orders`.`ID`
					WHERE `orders`.`post_date_gmt` >= %s
					AND `orders`.`post_date_gmt` <= %s
					AND `orders`.`post_status` NOT IN ( 'wc-cancelled', 'wc-pending', 'wc-failed', 'wc-on-hold', 'wc-checkout-draft', 'wc-trash' )
					AND `order_total`.`meta_key` = '_order_total'
					AND `orders`.`post_type` = 'shop_order'
			",
						$previous_year_dates['start']->format( 'Y-m-d H:i:s' ),
						$previous_year_dates['end']->format( 'Y-m-d H:i:s' )
					)
				);
			}
		} elseif ( $key === 'orders_count_with_balance_previous_month' ) {
			if ( WC_GC_Core_Compatibility::is_hpos_enabled() ) {
				self::$reusable_data['orders_count_with_balance_previous_month'] = (int) $wpdb->get_var(
					$wpdb->prepare(
						"
					SELECT COUNT( DISTINCT( `object_id` ) ) as `num_orders`
					FROM `{$wpdb->prefix}woocommerce_gc_activity` as `activities`
					INNER JOIN %i as `orders` ON `activities`.`object_id` = `orders`.`id`
					WHERE `activities`.`date` >= %d
					AND `activities`.`date` <= %d
					AND `activities`.`type` = 'used'
					AND `orders`.`status` NOT IN ( 'wc-cancelled', 'wc-pending', 'wc-failed', 'wc-on-hold', 'wc-checkout-draft', 'wc-trash' )
			",
						$hpos_orders_table,
						$previous_month_dates['start']->getTimestamp(),
						$previous_month_dates['end']->getTimestamp()
					)
				);
			} else {
				self::$reusable_data['orders_count_with_balance_previous_month'] = (int) $wpdb->get_var(
					$wpdb->prepare(
						"
					SELECT COUNT( DISTINCT( `object_id` ) ) as `num_orders`
					FROM `{$wpdb->prefix}woocommerce_gc_activity` as `activities`
					INNER JOIN `{$wpdb->posts}` as `orders` ON `activities`.`object_id` = `orders`.`ID`
					WHERE `activities`.`date` >= %d
					AND `activities`.`date` <= %d
					AND `activities`.`type` = 'used'
					AND `orders`.`post_status` NOT IN ( 'wc-cancelled', 'wc-pending', 'wc-failed', 'wc-on-hold', 'wc-checkout-draft', 'wc-trash' )
			",
						$previous_month_dates['start']->getTimestamp(),
						$previous_month_dates['end']->getTimestamp()
					)
				);
			}
		} elseif ( $key === 'orders_count_previous_month' ) {
			if ( WC_GC_Core_Compatibility::is_hpos_enabled() ) {
				self::$reusable_data['orders_count_previous_month'] = (int) $wpdb->get_var(
					$wpdb->prepare(
						"
					SELECT COUNT( `orders`.`id` )
					FROM %i AS `orders`
					WHERE `orders`.`date_created_gmt` >= %s
					AND `orders`.`date_created_gmt` <= %s
					AND `orders`.`status` NOT IN ( 'wc-cancelled', 'wc-pending', 'wc-failed', 'wc-on-hold', 'wc-checkout-draft', 'wc-trash' )
					AND `orders`.`type` = 'shop_order'
			",
						$hpos_orders_table,
						$previous_month_dates['start']->format( 'Y-m-d H:i:s' ),
						$previous_month_dates['end']->format( 'Y-m-d H:i:s' )
					)
				);
			} else {
				self::$reusable_data['orders_count_previous_month'] = (int) $wpdb->get_var(
					$wpdb->prepare(
						"
					SELECT COUNT( `ID` )
					FROM `{$wpdb->posts}` AS `orders`
					WHERE `orders`.`post_date_gmt` >= %s
					AND `orders`.`post_date_gmt` <= %s
					AND `orders`.`post_status` NOT IN ( 'wc-cancelled', 'wc-pending', 'wc-failed', 'wc-on-hold', 'wc-checkout-draft', 'wc-trash' )
					AND `orders`.`post_type` = 'shop_order'
			",
						$previous_month_dates['start']->format( 'Y-m-d H:i:s' ),
						$previous_month_dates['end']->format( 'Y-m-d H:i:s' )
					)
				);
			}
		} elseif ( $key === 'orders_count_with_balance_previous_year' ) {
			if ( WC_GC_Core_Compatibility::is_hpos_enabled() ) {
				self::$reusable_data['orders_count_with_balance_previous_year'] = (int) $wpdb->get_var(
					$wpdb->prepare(
						"
					SELECT COUNT( DISTINCT( `object_id` ) ) as `num_orders`
					FROM `{$wpdb->prefix}woocommerce_gc_activity` as `activities`
					INNER JOIN %i as `orders` ON `activities`.`object_id` = `orders`.`id`
					WHERE `activities`.`date` >= %d
					AND `activities`.`date` <= %d
					AND `activities`.`type` = 'used'
					AND `orders`.`status` NOT IN ( 'wc-cancelled', 'wc-pending', 'wc-failed', 'wc-on-hold', 'wc-checkout-draft', 'wc-trash' )
					AND `orders`.`type` = 'shop_order'
			",
						$hpos_orders_table,
						$previous_year_dates['start']->getTimestamp(),
						$previous_year_dates['end']->getTimestamp()
					)
				);
			} else {
				self::$reusable_data['orders_count_with_balance_previous_year'] = (int) $wpdb->get_var(
					$wpdb->prepare(
						"
					SELECT COUNT( DISTINCT( `object_id` ) ) as `num_orders`
					FROM `{$wpdb->prefix}woocommerce_gc_activity` as `activities`
					INNER JOIN `{$wpdb->posts}` as `orders` ON `activities`.`object_id` = `orders`.`ID`
					WHERE `activities`.`date` >= %d
					AND `activities`.`date` <= %d
					AND `activities`.`type` = 'used'
					AND `orders`.`post_status` NOT IN ( 'wc-cancelled', 'wc-pending', 'wc-failed', 'wc-on-hold', 'wc-checkout-draft', 'wc-trash' )
					AND `orders`.`post_type` = 'shop_order'
			",
						$previous_year_dates['start']->getTimestamp(),
						$previous_year_dates['end']->getTimestamp()
					)
				);
			}
		} elseif ( $key === 'orders_count_previous_year' ) {
			if ( WC_GC_Core_Compatibility::is_hpos_enabled() ) {
				self::$reusable_data['orders_count_previous_year'] = (int) $wpdb->get_var(
					$wpdb->prepare(
						"
					SELECT COUNT( `orders`.`id` )
					FROM %i AS `orders`
					WHERE `orders`.`date_created_gmt` >= %s
					AND `orders`.`date_created_gmt` <= %s
					AND `orders`.`status` NOT IN ( 'wc-cancelled', 'wc-pending', 'wc-failed', 'wc-on-hold', 'wc-checkout-draft', 'wc-trash' )
					AND `orders`.`type` = 'shop_order'
			",
						$hpos_orders_table,
						$previous_year_dates['start']->format( 'Y-m-d H:i:s' ),
						$previous_year_dates['end']->format( 'Y-m-d H:i:s' )
					)
				);
			} else {
				self::$reusable_data['orders_count_previous_year'] = (int) $wpdb->get_var(
					$wpdb->prepare(
						"
					SELECT COUNT( `ID` )
					FROM `{$wpdb->posts}` AS `orders`
					WHERE `orders`.`post_date_gmt` >= %s
					AND `orders`.`post_date_gmt` <= %s
					AND `orders`.`post_status` NOT IN ( 'wc-cancelled', 'wc-pending', 'wc-failed', 'wc-on-hold', 'wc-checkout-draft', 'wc-trash' )
					AND `orders`.`post_type` = 'shop_order'
			",
						$previous_year_dates['start']->format( 'Y-m-d H:i:s' ),
						$previous_year_dates['end']->format( 'Y-m-d H:i:s' )
					)
				);
			}
		}

		return self::$reusable_data[ $key ];
	}

	/**
	 * Check if all the main aggregations have pending data.
	 *
	 * @since  1.15.4
	 * @return bool Pending status.
	 */
	private static function has_pending_calculations() {

		if (
			! isset( self::$data['settings']['pending'] )
			&& ! isset( self::$data['giftcards']['pending'] )
			&& ! isset( self::$data['orders']['pending'] )
		) {
			return false;
		}

		return true;
	}

	/**
	 * Check if execution time is high or if available memory is almost consumed.
	 *
	 * @since  1.15.4
	 * @return bool Returns true if we're about to consume our available resources.
	 */
	private static function time_or_memory_exceeded() {
		return self::time_exceeded() || self::memory_exceeded();
	}

	/**
	 * Initialize data if they are empty month/year has changed.
	 *
	 * @since  1.15.4
	 * @return void
	 */
	private static function maybe_initialize_data() {

		// Default interval is -1 week.
		if ( defined( 'WC_CALYPSO_BRIDGE_TRACKER_FREQUENCY' ) ) {
			self::$invalidation_interval = '-1 day';
		}

		if (
			empty( self::$data )
			|| ! isset( self::$data['info']['started_time'] )
			|| self::$data['info']['started_time'] <= strtotime( self::$invalidation_interval )
		) {
			self::$data = array(
				'settings'  => array( 'pending' => true ),
				'giftcards' => array( 'pending' => true ),
				'orders'    => array( 'pending' => true ),
				'info'      => array(
					'iterations'   => 0,
					'started_time' => time(),
				),
			);
		}

		// Keep the month that the data were previously calculated.
		// @see self::read_data()
		if ( isset( self::$calculated_data['info']['started_time'] ) ) {
			$calculated_month = gmdate( 'm', self::$calculated_data['info']['started_time'] );
		} else {
			$calculated_month = gmdate( 'm', self::$data['info']['started_time'] );
		}
		$current_month = gmdate( 'm' );

		// If the month hasn't changed between runs, we should retain the calculated data.
		if ( $current_month === $calculated_month ) {
			self::$should_retain_calculated_data = true;
		}

		self::$tracking_events = get_option( 'woocommerce_gc_tracking_events', array() );
		$event_defaults        = array(
			'giftcards_product_first_create_date' => null,
		);
		self::$tracking_events = wp_parse_args( self::$tracking_events, $event_defaults );

		if ( WC_GC_Core_Compatibility::is_hpos_enabled() ) {
			self::$hpos_orders_table = Automattic\WooCommerce\Internal\DataStores\Orders\OrdersTableDataStore::get_orders_table_name();
		}
	}

	/**
	 * Time exceeded.
	 *
	 * Ensures the batch never exceeds a sensible time limit.
	 * A timeout limit of 30s is common on shared hosting.
	 *
	 * @since  1.15.4
	 * @return bool
	 */
	private static function time_exceeded() {
		$finish = self::$start_time + 20; // 20 seconds
		return time() >= $finish;
	}

	/**
	 * Memory exceeded
	 *
	 * Ensures the batch process never exceeds 90%
	 * of the maximum WordPress memory.
	 *
	 * @since  1.15.4
	 * @return bool
	 */
	private static function memory_exceeded() {
		$memory_limit   = self::get_memory_limit() * 0.8; // 80% of max memory
		$current_memory = memory_get_usage( true );
		return $current_memory >= $memory_limit;
	}

	/**
	 * Get memory limit.
	 *
	 * @since  1.15.4
	 * @return int
	 */
	private static function get_memory_limit() {
		if ( function_exists( 'ini_get' ) ) {
			$memory_limit = ini_get( 'memory_limit' );
		} else {
			// Sensible default.
			$memory_limit = '128M';
		}

		if ( ! $memory_limit || -1 === intval( $memory_limit ) ) {
			// Unlimited, set to 32GB.
			$memory_limit = '32000M';
		}

		return wp_convert_hr_to_bytes( $memory_limit );
	}

	/**
	 * Increase iterations.
	 *
	 * @since  1.15.4
	 * @return void
	 */
	private static function increase_iterations() {
		if ( isset( self::$data['info'] ) && isset( self::$data['info']['iterations'] ) ) {
			self::$data['info']['iterations'] += 1;
		}
	}

	/**
	 * Set starting time.
	 *
	 * @since  1.15.4
	 * @return void
	 */
	private static function set_start_time() {
		self::$start_time = time();
	}

	/**
	 * Set data from option.
	 *
	 * @since  1.15.4
	 * @return void
	 */
	private static function read_data() {
		self::$data            = get_option( 'woocommerce_gc_tracking_data' );
		self::$calculated_data = self::$data;
	}

	/**
	 * Set option with data.
	 *
	 * @since  1.15.4
	 * @return void
	 */
	private static function set_option_data() {
		update_option( 'woocommerce_gc_tracking_data', self::$data );
	}
}

WC_GC_Tracker::init();
