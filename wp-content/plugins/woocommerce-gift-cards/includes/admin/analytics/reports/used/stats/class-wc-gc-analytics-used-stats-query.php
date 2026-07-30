<?php
/**
 * REST API Reports Stats Query
 * Handles requests to the '/reports/giftcards/used/stats' endpoint.
 *
 * @package  WooCommerce Gift Cards
 * @since    1.8.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_GC_Analytics_Used_Stats_Query class.
 *
 * @version 2.1.1
 */
class WC_GC_Analytics_Used_Stats_Query extends Automattic\WooCommerce\Admin\API\Reports\GenericQuery {

	/**
	 * Valid fields for report.
	 *
	 * @return array
	 */
	protected function get_default_query_vars() {
		return array();
	}

	/**
	 * Get gift card data based on the current query vars.
	 *
	 * @return array
	 */
	public function get_data() {
		$args       = apply_filters( 'woocommerce_analytics_giftcards_used_stats_query_args', $this->get_query_vars() );
		$data_store = WC_Data_Store::load( 'report-giftcards-used-stats' );
		$results    = $data_store->get_data( $args );
		return apply_filters( 'woocommerce_analytics_giftcards_used_stats_select_query', $results, $args );
	}
}
