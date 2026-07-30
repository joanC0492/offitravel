<?php
/**
 * REST API Reports giftcards issued controller.
 *
 * @package  WooCommerce Gift Cards
 * @since    1.8.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Automattic\WooCommerce\Admin\API\Reports\ExportableInterface;

/**
 * WC_GC_Analytics_Issued_REST_Controller class.
 *
 * @version 1.16.0
 */
class WC_GC_Analytics_Issued_REST_Controller extends WC_REST_Reports_Controller implements ExportableInterface {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wc-analytics';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'reports/giftcards-issued';

	/**
	 * Mapping between external parameter name and name used in query class.
	 *
	 * @var array
	 */
	protected $param_mapping = array();

	/**
	 * Get items.
	 *
	 * @param  WP_REST_Request $request Request data.
	 * @return array|WP_Error
	 */
	public function get_items( $request ) {
		$args       = array();
		$registered = array_keys( $this->get_collection_params() );
		foreach ( $registered as $param_name ) {
			if ( isset( $request[ $param_name ] ) ) {
				if ( isset( $this->param_mapping[ $param_name ] ) ) {
					$args[ $this->param_mapping[ $param_name ] ] = $request[ $param_name ];
				} else {
					$args[ $param_name ] = $request[ $param_name ];
				}
			}
		}

		$reports       = new WC_GC_Analytics_Issued_Query( $args );
		$products_data = $reports->get_data();

		$data = array();

		// Prepare and sanitize characters for response.
		foreach ( $products_data->data as $product_data ) {
			$item   = $this->prepare_item_for_response( $product_data, $request );
			$data[] = $this->prepare_response_for_collection( $item );
		}

		$response = rest_ensure_response( $data );
		$response->header( 'X-WP-Total', (int) $products_data->total );
		$response->header( 'X-WP-TotalPages', (int) $products_data->pages );

		$page      = $products_data->page_no;
		$max_pages = $products_data->pages;
		$base      = add_query_arg( $request->get_query_params(), rest_url( sprintf( '/%s/%s', $this->namespace, $this->rest_base ) ) ); // nosemgrep: audit.php.wp.security.xss.query-arg
		if ( $page > 1 ) {
			$prev_page = $page - 1;
			if ( $prev_page > $max_pages ) {
				$prev_page = $max_pages;
			}
			$prev_link = add_query_arg( 'page', $prev_page, $base );
			$response->link_header( 'prev', $prev_link );
		}
		if ( $max_pages > $page ) {
			$next_page = $page + 1;
			$next_link = add_query_arg( 'page', $next_page, $base );
			$response->link_header( 'next', $next_link );
		}

		return $response;
	}

	/**
	 * Prepare a report object for serialization.
	 *
	 * @param  array           $report  Report data.
	 * @param  WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function prepare_item_for_response( $report, $request ) {
		$data = $report;

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data    = $this->add_additional_fields_to_object( $data, $request );
		$data    = $this->filter_response_by_context( $data, $context );

		// Wrap the data in a response object.
		$response = rest_ensure_response( $data );
		// $response->add_links( $this->prepare_links( $report ) );

		/**
		 * Filter a report returned from the API.
		 *
		 * Allows modification of the report data right before it is returned.
		 *
		 * @param WP_REST_Response $response The response object.
		 * @param object           $report   The original report object.
		 * @param WP_REST_Request  $request  Request used to generate the response.
		 */
		return apply_filters( 'woocommerce_rest_prepare_report_giftcards', $response, $report, $request );
	}

	/**
	 * Prepare links for the request.
	 *
	 * @param  array $object Object data.
	 * @return array
	 */
	protected function prepare_links( $object ) {
		// @TODO: Remove analytics endpoint? Hmm
		$links = array(
			'gift-card' => array(
				'href' => rest_url( sprintf( '/%s/%s/%d', $this->namespace, 'gift-cards', $object['giftcard_id'] ) ),
			),
		);

		return $links;
	}

	/**
	 * Get the Report's schema, conforming to JSON Schema.
	 *
	 * @return array
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'report_issued_giftcards',
			'type'       => 'object',
			'properties' => array(
				'giftcard_id'    => array(
					'type'        => 'integer',
					'readonly'    => true,
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'Gift card ID.', 'woocommerce-gift-cards' ),
				),
				'giftcard_code'  => array(
					'type'        => 'string',
					'readonly'    => true,
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'Gift card code.', 'woocommerce-gift-cards' ),
				),
				'issued_balance' => array(
					'type'        => 'number',
					'readonly'    => true,
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'Issued balance.', 'woocommerce-gift-cards' ),
				),
				'create_date'    => array(
					'type'        => 'string',
					'readonly'    => true,
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'Creation date.', 'woocommerce-gift-cards' ),
				),
				'expire_date'    => array(
					'type'        => 'string',
					'readonly'    => true,
					'context'     => array( 'view', 'edit' ),
					'description' => __( 'Expiration date.', 'woocommerce-gift-cards' ),
				),
			),
		);

		return $this->add_additional_fields_schema( $schema );
	}

	/**
	 * Get the query params for collections.
	 *
	 * @return array
	 */
	public function get_collection_params() {
		$params             = array();
		$params['context']  = $this->get_context_param( array( 'default' => 'view' ) );
		$params['page']     = array(
			'description'       => __( 'Current page of the collection.', 'woocommerce-gift-cards' ),
			'type'              => 'integer',
			'default'           => 1,
			'sanitize_callback' => 'absint',
			'validate_callback' => 'rest_validate_request_arg',
			'minimum'           => 1,
		);
		$params['per_page'] = array(
			'description'       => __( 'Maximum number of items to be returned in result set.', 'woocommerce-gift-cards' ),
			'type'              => 'integer',
			'default'           => 10,
			'minimum'           => 1,
			'maximum'           => 100,
			'sanitize_callback' => 'absint',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['after']    = array(
			'description'       => __( 'Limit response to resources published after a given ISO8601 compliant date.', 'woocommerce-gift-cards' ),
			'type'              => 'string',
			'format'            => 'date-time',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['before']   = array(
			'description'       => __( 'Limit response to resources published before a given ISO8601 compliant date.', 'woocommerce-gift-cards' ),
			'type'              => 'string',
			'format'            => 'date-time',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['order']    = array(
			'description'       => __( 'Order sort attribute ascending or descending.', 'woocommerce-gift-cards' ),
			'type'              => 'string',
			'default'           => 'desc',
			'enum'              => array( 'asc', 'desc' ),
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['orderby']  = array(
			'description'       => __( 'Sort collection by object attribute.', 'woocommerce-gift-cards' ),
			'type'              => 'string',
			'default'           => 'date',
			'enum'              => array(
				'date',
				'issued_balance',
				'expire_date',
			),
			'validate_callback' => 'rest_validate_request_arg',
		);

		return $params;
	}

	/**
	 * Get the column names for export.
	 *
	 * @return array Key value pair of Column ID => Label.
	 */
	public function get_export_columns() {

		$export_columns = array(
			'code'           => __( 'Gift Card Code', 'woocommerce-gift-cards' ),
			'issued_balance' => __( 'Issued Balance', 'woocommerce-gift-cards' ),
			'create_date'    => __( 'Issued Dated', 'woocommerce-gift-cards' ),
			'expire_date'    => __( 'Expriration Date', 'woocommerce-gift-cards' ),
		);

		/**
		 * `woocommerce_report_giftcards_issued_export_columns` filter.
		 *
		 * Filter to add or remove column names from the products report for
		 * export.
		 */
		return apply_filters(
			'woocommerce_report_giftcards_issued_export_columns',
			$export_columns
		);
	}

	/**
	 * Get the column values for export.
	 *
	 * @param  array $item Single report item/row.
	 * @return array Key value pair of Column ID => Row Value.
	 */
	public function prepare_item_for_export( $item ) {

		$export_item = array(
			'code'           => $item['giftcard_code'],
			'issued_balance' => $item['issued_balance'],
			'create_date'    => date_i18n( 'Y-m-d H:i:s', absint( $item['create_date'] ) ),
			'expire_date'    => date_i18n( 'Y-m-d H:i:s', absint( $item['expire_date'] ) ),
		);

		/**
		 * `woocommerce_report_giftcards_issued_prepare_export_item` filter.
		 *
		 * Filter to prepare extra columns in the export item for the products
		 * report.
		 */
		return apply_filters(
			'woocommerce_report_giftcards_issued_prepare_export_item',
			$export_item,
			$item
		);
	}
}
