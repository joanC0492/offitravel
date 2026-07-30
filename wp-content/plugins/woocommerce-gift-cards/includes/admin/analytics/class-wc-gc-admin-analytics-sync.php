<?php
/**
 * WC_GC_Admin_Analytics_Sync class
 *
 * @package  WooCommerce Gift Cards
 * @since    1.8.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Sync Reports Class.
 *
 * @version 2.2.1
 */
class WC_GC_Admin_Analytics_Sync {

	/**
	 * Used to short circuit 'woocommerce_analytics_update_order_stats' action if the data is already filtered.
	 *
	 * @var array
	 */
	protected static $updated_order_ids = array();

	/**
	 * Setup Admin class.
	 */
	public static function init() {

		/*
		 * Update order stats to:
		 * - Subtract the totals of purchased prepaid gift cards from the total and net sales.
		 * - Add the applied gift card balance to the total and net sales.
		 */
		add_filter( 'woocommerce_analytics_update_order_stats_data', array( __CLASS__, 'filter_order_stats' ), 10, 2 );
		add_action( 'woocommerce_analytics_update_order_stats', array( __CLASS__, 'update_order_stats' ) );

		if ( method_exists( WC(), 'queue' ) ) {

			if ( is_admin() ) {

				// Add status tool to regenerate order stats table.
				add_filter( 'woocommerce_debug_tools', array( __CLASS__, 'add_regenerate_order_stats_tool' ) );

				// Handle regenerate button clicks.
				add_action( 'admin_init', array( __CLASS__, 'handle_trigger_order_stats_update' ) );
			}

			// AS action for regenerating order stats for a batch of orders.
			add_action( 'wc_gc_process_order_stats_update_batch', array( __CLASS__, 'process_order_stats_update_batch' ), 10, 2 );

			// Revalidate cache.
			add_action( 'woocommerce_gc_save_gift_card', array( __CLASS__, 'invalidate_reports_cache' ) );
			add_action( 'woocommerce_gc_delete_gift_card', array( __CLASS__, 'invalidate_reports_cache' ) );
		}
	}

	/**
	 * Update order stats if triggered.
	 */
	public static function handle_trigger_order_stats_update() {

		if ( ! empty( $_GET['trigger_wc_gc_order_stats_db_update'] ) && isset( $_GET['_wc_gc_admin_nonce'] ) && wp_verify_nonce( wc_clean( $_GET['_wc_gc_admin_nonce'] ), 'wc_gc_trigger_order_stats_db_update_nonce' ) && ! self::is_order_stats_update_queued() ) {
			self::queue_order_stats_update();
			wp_safe_redirect( remove_query_arg( array( 'trigger_wc_gc_order_stats_db_update', '_wc_gc_admin_nonce' ) ) );
			exit;
		}
	}

	/**
	 * Number of orders per regeneration batch.
	 *
	 * @return int
	 */
	protected static function get_batch_size() {
		return 10;
	}

	/**
	 * Updates all order stats table rows that involve gift cards.
	 *
	 * @return void
	 */
	public static function queue_order_stats_update() {

		global $wpdb;

		$order_stats_table_name = Automattic\WooCommerce\Admin\API\Reports\Orders\Stats\DataStore::get_db_table_name();

		// Count distinct orders in order stats lookup table that also exist in GC activity table.
		$count = $wpdb->get_var(
			$wpdb->prepare(
				"
			SELECT COUNT(*)
			FROM %i AS stats
			INNER JOIN `{$wpdb->prefix}woocommerce_gc_activity` AS activity
			ON stats.order_id = activity.object_id
		",
				$order_stats_table_name
			)
		);

		if ( ! $count ) {
			return;
		}

		$batches = ceil( $count / self::get_batch_size() );

		// Cancel existing jobs and restart.
		WC()->queue()->cancel_all( 'wc_gc_process_order_stats_update_batch' );

		// Queue first batch.
		self::queue_order_stats_update_batch( 1, $batches );

		if ( ! class_exists( 'WC_GC_Admin_Notices' ) ) {
			require_once WC_GC_ABSPATH . 'includes/admin/class-wc-gc-admin-notices.php';
		}

		WC_GC_Admin_Notices::add_maintenance_notice( 'update_order_stats' );
	}

	/**
	 * Schedules a batch of row renenerations for order stats.
	 *
	 * @param  int $batch
	 * @param  int $batches
	 * @return void
	 */
	protected static function queue_order_stats_update_batch( $batch, $batches ) {
		WC()->queue()->add( 'wc_gc_process_order_stats_update_batch', array( $batch, $batches ), 'wc_gc_regenerate_order_stats' );
	}

	/**
	 * Indicates whether an order stats regeneration has been attempted in the past.
	 *
	 * @return boolean
	 */
	public static function is_order_stats_update_actioned() {

		$order_stats_update_batches = WC()->queue()->search(
			array(
				'hook' => 'wc_gc_process_order_stats_update_batch',
			)
		);

		return ! empty( $order_stats_update_batches );
	}

	/**
	 * Indicates whether an order stats regeneration is currently queued.
	 *
	 * @return boolean
	 */
	public static function is_order_stats_update_queued() {

		$order_stats_update_batches = WC()->queue()->search(
			array(
				'hook'   => 'wc_gc_process_order_stats_update_batch',
				'status' => ActionScheduler_Store::STATUS_PENDING,
			)
		);

		return ! empty( $order_stats_update_batches );
	}

	/**
	 * Regenerates order stats for single batch of orders.
	 *
	 * @param  int $batch
	 * @param  int $batches
	 * @return void
	 */
	public static function process_order_stats_update_batch( $batch, $batches ) {

		global $wpdb;

		$order_stats_table_name = Automattic\WooCommerce\Admin\API\Reports\Orders\Stats\DataStore::get_db_table_name();

		// Find order IDs to process.
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"
			SELECT order_id
			FROM %i AS stats
			INNER JOIN `{$wpdb->prefix}woocommerce_gc_activity` AS activity
			ON stats.order_id = activity.object_id
			ORDER BY order_id
			LIMIT %d
			OFFSET %d
		",
				$order_stats_table_name,
				self::get_batch_size(),
				( $batch - 1 ) * self::get_batch_size()
			),
			ARRAY_A
		);

		if ( empty( $results ) ) {
			return;
		}

		$order_ids = wp_list_pluck( $results, 'order_id' );

		// Do the work.
		foreach ( $order_ids as $order_id ) {
			self::update_order_stats( $order_id );
		}

		// Queue next batch.
		if ( $batch < $batches ) {
			self::queue_order_stats_update_batch( $batch + 1, $batches );
		}

		Automattic\WooCommerce\Admin\API\Reports\Cache::invalidate();
	}

	/*
	|--------------------------------------------------------------------------
	| Callbacks.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Filters the order stats data for a single order.
	 *
	 * @param  array    $data
	 * @param  WC_Order $order
	 * @return array
	 */
	public static function filter_order_stats( $data, $order ) {

		$order_stats_mods = apply_filters( 'woocommerce_gc_order_stats_data_modifications', array( 'purchases', 'payments', 'revenue' ) );
		$total_sales      = isset( $data['total_sales'] ) ? $data['total_sales'] : (float) $order->get_total();
		$net_total        = isset( $data['net_total'] ) ? $data['net_total'] : $total_sales - (float) $order->get_total_tax() - (float) $order->get_shipping_total();

		if ( in_array( 'payments', $order_stats_mods ) && 'shop_order' === $order->get_type() ) {

			$applied_gift_cards = WC_GC()->order->get_gift_cards( $order );

			if ( $applied_gift_cards['total'] ) {

				$total_sales += $applied_gift_cards['total'];
				$net_total   += $applied_gift_cards['total'];
			}
		}

		if ( in_array( 'purchases', $order_stats_mods ) && 'shop_order' === $order->get_type() ) {

			foreach ( $order->get_items() as $order_item_id => $order_item ) {

				// Is this a gift card purchase?
				if ( ! $order_item->get_meta( 'wc_gc_giftcard_amount', true ) ) {
					continue;
				}

				// Is this a multi-purpose gift-card (zero tax)?
				if ( $order_item->get_total_tax() ) {
					continue;
				}

				$order_item_total    = $order_item->get_total();
				$order_item_refunded = $order->get_total_refunded_for_item( $order_item->get_id() );

				if ( $order_item_total !== $order_item_refunded ) {
					// Gift card purchases count towards gross sales, but not towards net sales.
					$net_total -= (float) $order_item_total + (float) $order_item_refunded;
				}
			}
		}

		if ( in_array( 'revenue', $order_stats_mods ) && 'shop_order_refund' === $order->get_type() ) {
			$activities = $order->get_meta( '_wc_gc_refund_activities', true );
			if ( ! empty( $activities ) ) {
				$total = 0.0;
				foreach ( $activities as $activity_id ) {
					$activity = WC_GC()->db->activity->get( $activity_id );
					if ( ! $activity || ! $activity->is_type( 'manually_refunded' ) ) {
						continue;
					}

					$total += $activity->get_amount();
				}

				$total_sales = -1 * $total;
				// Hint: Calculate net_total without taxes and shipping.
				$net_total = -1 * ( $total + floatval( $order->get_total_tax() ) + floatval( $order->get_shipping_total() ) );
			}
		}

		self::$updated_order_ids[] = $order->get_id();

		return array_merge(
			$data,
			array(
				'total_sales' => $total_sales,
				'net_total'   => $net_total,
			)
		);
	}

	/**
	 * Updates the order stats table for a single order.
	 *
	 * @param  int $order_id
	 * @return void
	 */
	public static function update_order_stats( $order_id ) {

		// Data already updated using the 'woocommerce_analytics_update_order_stats_data' filter?
		if ( in_array( $order_id, self::$updated_order_ids ) ) {
			return;
		}

		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return;
		}

		if ( ! in_array( $order->get_type(), array( 'shop_order', 'shop_order_refund' ) ) ) {
			return;
		}

		global $wpdb;

		$updated_order_stats_data = self::filter_order_stats( array(), $order );
		$order_stats_table_name   = Automattic\WooCommerce\Admin\API\Reports\Orders\Stats\DataStore::get_db_table_name();

		$wpdb->update( $order_stats_table_name, $updated_order_stats_data, array( 'order_id' => $order_id ), array( '%f', '%f' ), array( '%d' ) );
	}

	/**
	 * Adds status tool to regenerate the order stats table.
	 *
	 * @param  array
	 * @return array
	 */
	public static function add_regenerate_order_stats_tool( $tools ) {

		if ( WC_GC_Core_Compatibility::is_wc_admin_enabled() ) {

			$tools['gc_regenerate_order_stats'] = array(
				'name'     => __( 'Regenerate revenue analytics data', 'woocommerce-gift-cards' ),
				'button'   => __( 'Regenerate data', 'woocommerce-gift-cards' ),
				/* translators: documentation link */
				'desc'     => sprintf( __( 'Regenerates historical Revenue reports in WooCommerce Analytics to <a href="%s" target="_blank">account for prepaid gift cards correctly</a>.', 'woocommerce-gift-cards' ), WC_GC()->get_resource_url( 'faq-multi-prepaid-revenue' ) ),
				'callback' => array( __CLASS__, 'queue_order_stats_update' ),
			);
		}

		return $tools;
	}

	/**
	 * Handle analytics report's cache invalidation.
	 *
	 * @return void
	 */
	public static function invalidate_reports_cache( $giftcard ) {
		WC_Cache_Helper::get_transient_version( 'woocommerce_giftcards_used_reports', true );
		WC_Cache_Helper::get_transient_version( 'woocommerce_giftcards_used_stats_reports', true );
		WC_Cache_Helper::get_transient_version( 'woocommerce_giftcards_issued_reports', true );
		WC_Cache_Helper::get_transient_version( 'woocommerce_giftcards_issued_stats_reports', true );
		WC_Cache_Helper::get_transient_version( 'woocommerce_giftcards_expired_reports', true );
		WC_Cache_Helper::get_transient_version( 'woocommerce_giftcards_expired_stats_reports', true );
	}
}

WC_GC_Admin_Analytics_Sync::init();
