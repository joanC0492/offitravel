<?php
/**
 * WC_GC_CSV_Exporter class
 *
 * @package  WooCommerce Gift Cards
 * @since    1.6.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Include dependencies.
 */
if ( ! class_exists( 'WC_CSV_Batch_Exporter', false ) ) {
	include_once WC_ABSPATH . 'includes/export/abstract-wc-csv-batch-exporter.php';
}

/**
 * WC_GC_CSV_Exporter Class.
 *
 * @version 2.5.0
 */
class WC_GC_CSV_Exporter extends WC_CSV_Batch_Exporter {

	/**
	 * Type of export used in filter names.
	 *
	 * @var string
	 */
	protected $export_type = 'gc_giftcards';

	/**
	 * Should meta be exported?
	 *
	 * @var boolean
	 */
	protected $enable_meta_export = false;

	/**
	 * Should activities be exported?
	 *
	 * @var boolean
	 */
	protected $enable_activities_export = false;

	/**
	 * Query filters.
	 *
	 * @var boolean
	 */
	protected $filters = false;

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Force-Cast percent to integer.
	 *
	 * @override
	 */
	public function get_percent_complete() {
		return absint( parent::get_percent_complete() );
	}

	/**
	 * Should meta be exported?
	 *
	 * @param bool $enable_meta_export Should meta be exported.
	 */
	public function enable_meta_export( $enable_meta_export ) {
		$this->enable_meta_export = (bool) $enable_meta_export;
	}

	/**
	 * Should meta be exported?
	 *
	 * @param bool $enable_activities_export Should meta be exported.
	 */
	public function enable_activities_export( $enable_activities_export ) {
		$this->enable_activities_export = (bool) $enable_activities_export;
	}

	/**
	 * Return an array of columns to export.
	 *
	 * @return array
	 */
	public function get_default_column_names() {
		return apply_filters(
			"woocommerce_{$this->export_type}_export_default_columns",
			array(
				'id'            => __( 'ID', 'woocommerce-gift-cards' ),
				'code'          => __( 'Code', 'woocommerce-gift-cards' ),
				'recipient'     => __( 'Recipient', 'woocommerce-gift-cards' ),
				'sender'        => __( 'Sender', 'woocommerce-gift-cards' ),
				'sender_email'  => __( 'Sender E-mail', 'woocommerce-gift-cards' ),
				'message'       => __( 'Message', 'woocommerce-gift-cards' ),
				'balance'       => __( 'Issued value', 'woocommerce-gift-cards' ),
				'remaining'     => __( 'Balance', 'woocommerce-gift-cards' ),
				'order_id'      => __( 'Order ID', 'woocommerce-gift-cards' ),
				'order_item_id' => __( 'Order item ID', 'woocommerce-gift-cards' ),
				'template_id'   => __( 'Template ID', 'woocommerce-gift-cards' ),
				'create_date'   => __( 'Create date', 'woocommerce-gift-cards' ),
				'deliver_date'  => __( 'Delivery date', 'woocommerce-gift-cards' ),
				'expire_date'   => __( 'Expiration date', 'woocommerce-gift-cards' ),
				'redeem_date'   => __( 'Redeemed date', 'woocommerce-gift-cards' ),
				'redeemed_by'   => __( 'Redeemed by user', 'woocommerce-gift-cards' ),
				'delivered'     => __( 'Delivered', 'woocommerce-gift-cards' ),
				'is_virtual'    => __( 'Virtual', 'woocommerce-gift-cards' ),
				'is_active'     => __( 'Status', 'woocommerce-gift-cards' ),
				'currency'      => __( 'Currency', 'woocommerce-gift-cards' ),
			)
		);
	}

	/**
	 * Prepare data for export.
	 */
	public function prepare_data_to_export() {

		$args = array(
			'limit'  => $this->get_limit(),
			'offset' => $this->get_limit() * ( $this->get_page() - 1 ),
			'return' => 'objects',
		);

		// Has filters?
		if ( ! empty( $this->filters ) && is_array( $this->filters ) ) {
			if ( 0 != $this->filters['date'] && 6 === strlen( $this->filters['date'] ) ) {
				$year               = substr( $this->filters['date'], 0, 4 );
				$month              = substr( $this->filters['date'], 4, 6 );
				$args['start_date'] = strtotime( $year . '/' . $month . '/1 00:00:00' );
				$args['end_date']   = strtotime( '+ 1 month', $args['start_date'] );
			}

			if ( false !== $this->filters['customer'] ) {
				$args['redeemed_by'] = absint( $this->filters['customer'] );
			}

			if ( false !== $this->filters['status'] && 'all_gc' !== $this->filters['status'] ) {
				$args['is_redeemed'] = 'redeemed' === $this->filters['status'] ? true : null;
			}
		}

		$giftcards = WC_GC()->db->giftcards->query( apply_filters( "woocommerce_{$this->export_type}_export_query_args", $args ) );
		unset( $args['return'] );
		unset( $args['limit'] );
		unset( $args['offset'] );
		$this->total_rows = WC_GC()->db->giftcards->query( array_merge( array( 'count' => true ), $args ) );
		$this->row_data   = array();

		if ( is_array( $giftcards ) ) {

			foreach ( $giftcards as $giftcard ) {
				$this->row_data[] = $this->generate_row_data( $giftcard );
			}
		}
	}

	/**
	 * Take a giftcard and generate row data from it for export.
	 *
	 * @param WC_GC_Gift_Card_Data $giftcard WC_GC_Gift_Card_Data object.
	 *
	 * @return array
	 */
	protected function generate_row_data( $giftcard ) {
		$columns = $this->get_column_names();
		$row     = array();
		foreach ( $columns as $column_id => $column_name ) {
			$column_id = strstr( $column_id, ':' ) ? current( explode( ':', $column_id ) ) : $column_id;
			$value     = '';

			// Skip some columns if dynamically handled later or if we're being selective.
			if ( in_array( $column_id, array( 'meta' ), true ) || ! $this->is_column_exporting( $column_id ) ) {
				continue;
			}

			if ( has_filter( "woocommerce_{$this->export_type}_export_column_{$column_id}" ) ) {
				// Filter for 3rd parties.
				$value = apply_filters( "woocommerce_{$this->export_type}_export_column_{$column_id}", '', $giftcard, $column_id );

			} elseif ( is_callable( array( $this, "get_column_value_{$column_id}" ) ) ) {
				// Handle special columns which don't map 1:1 to notification data.
				$value = $this->{"get_column_value_{$column_id}"}( $giftcard );

			} elseif ( is_callable( array( $giftcard, "get_{$column_id}" ) ) ) {
				// Default and custom handling.
				$value = $giftcard->{"get_{$column_id}"}();
				if ( 0 != $value && false !== strpos( '_date', $column_id ) ) {
					$value = date_i18n( 'Y-m-d H:i:s', absint( $value ) );
				}
			}

			$row[ $column_id ] = $value;
		}

		$this->prepare_activities_for_export( $giftcard, $row );
		$this->prepare_meta_for_export( $giftcard, $row );
		return apply_filters( "woocommerce_{$this->export_type}_export_row_data", $row, $giftcard );
	}

	/**
	 * Export activity data.
	 *
	 * @param WC_BIS_Giftcard_Data $giftcard
	 * @param array                $row
	 */
	protected function prepare_activities_for_export( $giftcard, &$row ) {
		if ( $this->enable_activities_export ) {

			$query_args = array(
				'gc_id' => $giftcard->get_id(),
			);
			$activities = WC_GC()->db->activity->query( $query_args );

			if ( ! empty( $activities ) ) {

				$column_value = json_encode( $activities );
				$column_key   = 'activities_json';

				/* translators: %s: meta data name */
				$this->column_names[ $column_key ] = __( 'Activities', 'woocommerce-gift-cards' );
				$row[ $column_key ]                = $column_value;
			}
		}
	}

	/**
	 * Export meta data.
	 *
	 * @param WC_BIS_Giftcard_Data $giftcard
	 * @param array                $row
	 */
	protected function prepare_meta_for_export( $giftcard, &$row ) {
		if ( $this->enable_meta_export ) {
			$meta_data = $giftcard->get_meta_data();

			if ( count( $meta_data ) ) {

				/**
				 * Filter the meta keys to skip during export.
				 *
				 * @since 1.6.0
				 *
				 * @param array $meta_keys_to_skip Array of meta keys to skip during export.
				 * @param WC_GC_Gift_Card_Data $giftcard Gift card object.
				 * @return array
				 */
				$meta_keys_to_skip = (array) apply_filters( "woocommerce_{$this->export_type}_export_skip_meta_keys", array(), $giftcard );

				// Enforce skip _currency from meta data.
				if ( ! in_array( '_currency', $meta_keys_to_skip, true ) ) {
					$meta_keys_to_skip[] = '_currency';
				}

				$i = 1;
				foreach ( $meta_data as $key => $value ) {
					if ( in_array( $key, $meta_keys_to_skip, true ) ) {
						continue;
					}

					if ( ! is_scalar( $value ) ) {
						$value = json_encode( $value );
					}

					$column_key = 'meta:' . esc_attr( $key );
					/* translators: %s: meta data name */
					$this->column_names[ $column_key ] = sprintf( __( 'Meta: %s', 'woocommerce-gift-cards' ), $key );
					$row[ $column_key ]                = $value;
					++$i;
				}
			}
		}
	}

	/**
	 * Set filters.
	 *
	 * @param  array $post_data
	 * @return void
	 */
	public function set_filters( $post_data ) {
		$this->filters = array(
			'date'     => isset( $post_data['date_filter'] ) && 0 != $post_data['date_filter'] ? absint( $post_data['date_filter'] ) : false,
			'customer' => isset( $post_data['customer_filter'] ) && 'false' !== $post_data['customer_filter'] ? absint( $post_data['customer_filter'] ) : false,
			'status'   => isset( $post_data['status_filter'] ) && 'false' !== $post_data['status_filter'] ? wc_clean( $post_data['status_filter'] ) : false,
			'search'   => isset( $post_data['search_filter'] ) && 'false' !== $post_data['search_filter'] ? wc_clean( $post_data['search_filter'] ) : false,
		);
	}

	/**
	 * Set export meta.
	 *
	 * @since 2.5.0
	 *
	 * @param  mixed $export_meta Whether to export meta data.
	 * @return void
	 */
	public function set_export_meta( $export_meta ) {
		$this->enable_meta_export = wc_string_to_bool( $export_meta );
	}

	/*
	---------------------------------------------------*/
	/*
		Columns.                                         */
	/*---------------------------------------------------*/

	/**
	 * Get initial balance.
	 *
	 * @param  WC_GC_Gift_Card_Data $giftcard Gift Card being exported.
	 * @return string
	 */
	protected function get_column_value_balance( $giftcard ) {
		return $giftcard->get_initial_balance();
	}

	/**
	 * Get remaining balance.
	 *
	 * @param  WC_GC_Gift_Card_Data $giftcard Gift Card being exported.
	 * @return string
	 */
	protected function get_column_value_remaining( $giftcard ) {
		return $giftcard->get_balance();
	}

	/**
	 * Get create date.
	 *
	 * @param  WC_GC_Gift_Card_Data $giftcard Gift Card being exported.
	 * @return string
	 */
	protected function get_column_value_create_date( $giftcard ) {
		return $giftcard->get_date_created() ? date_i18n( 'Y-m-d H:i:s', $giftcard->get_date_created() ) : 0;
	}

	/**
	 * Get expire date.
	 *
	 * @param  WC_GC_Gift_Card_Data $giftcard Gift Card being exported.
	 * @return string
	 */
	protected function get_column_value_expire_date( $giftcard ) {
		return $giftcard->get_expire_date() ? date_i18n( 'Y-m-d H:i:s', $giftcard->get_expire_date() ) : 0;
	}

	/**
	 * Get Redeemed date.
	 *
	 * @param  WC_GC_Gift_Card_Data $giftcard Gift Card being exported.
	 * @return string
	 */
	protected function get_column_value_redeem_date( $giftcard ) {
		return $giftcard->get_date_redeemed() ? date_i18n( 'Y-m-d H:i:s', $giftcard->get_date_redeemed() ) : 0;
	}

	/**
	 * Get delivered date.
	 *
	 * @param  WC_GC_Gift_Card_Data $giftcard Gift Card being exported.
	 * @return string
	 */
	protected function get_column_value_deliver_date( $giftcard ) {
		return $giftcard->get_deliver_date() ? date_i18n( 'Y-m-d H:i:s', $giftcard->get_deliver_date() ) : 0;
	}

	/**
	 * Get delivered status.
	 *
	 * @param  WC_GC_Gift_Card_Data $giftcard Gift Card being exported.
	 * @return string
	 */
	protected function get_column_value_delivered( $giftcard ) {
		return false === $giftcard->is_delivered() ? 'no' : $giftcard->is_delivered();
	}

	/**
	 * Get virtual status column.
	 *
	 * @param  WC_GC_Gift_Card_Data $giftcard Gift Card being exported.
	 * @return string
	 */
	protected function get_column_value_is_virtual( $giftcard ) {
		return $giftcard->is_virtual() ? 'on' : 'off';
	}

	/**
	 * Get active status column.
	 *
	 * @param  WC_GC_Gift_Card_Data $giftcard Gift_Card being exported.
	 * @return string
	 */
	protected function get_column_value_is_active( $giftcard ) {
		return $giftcard->is_active() ? 'on' : 'off';
	}

	/**
	 * Get currency column.
	 *
	 * @since 2.5.0
	 *
	 * @param  WC_GC_Gift_Card_Data $giftcard Gift_Card being exported.
	 * @return string
	 */
	protected function get_column_value_currency( $giftcard ) {
		$currency = $giftcard->get_meta( '_currency' );
		return $currency ? $currency : get_woocommerce_currency();
	}
}
