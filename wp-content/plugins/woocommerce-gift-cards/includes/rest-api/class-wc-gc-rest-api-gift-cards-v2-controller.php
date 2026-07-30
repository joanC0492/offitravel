<?php
/**
 * WC_GC_REST_API_Gift_Cards_V2_Controller class
 *
 * @package  WooCommerce Gift Cards
 * @since    1.8.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Gift Cards class.
 *
 * @class    WC_GC_REST_API_Gift_Cards_V2_Controller
 * @extends  WC_REST_Controller
 * @version  2.5.0
 */
class WC_GC_REST_API_Gift_Cards_V2_Controller extends WC_REST_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wc/v2';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'gift-cards';

	/**
	 * Cached batch attributes. Used for batch requests.
	 *
	 * @var array
	 */
	protected $cached_batch_attributes = array(
		'POST'   => array(), // Batch create.
		'PUT'    => array(), // Batch update.
		'DELETE' => array(), // Batch delete.
	);

	/**
	 * Register the routes for Gift Cards.
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $this->get_collection_params(),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'create_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				'args'   => array(
					'id' => array(
						'description' => __( 'Unique identifier for the resource.', 'woocommerce-gift-cards' ),
						'type'        => 'integer',
					),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
					'args'                => array(
						'context' => $this->get_context_param(
							array(
								'default' => 'view',
							)
						),
					),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'update_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
				),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_item' ),
					'permission_callback' => array( $this, 'delete_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::DELETABLE ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/batch',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'batch_items' ),
					'permission_callback' => array( $this, 'batch_items_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::ALLMETHODS ),
				),
				'schema' => array( $this, 'get_public_batch_schema' ),
			)
		);
	}

	/**
	 * Validate date request arguments.
	 *
	 * @param  mixed           $value   Value to validate.
	 * @param  WP_REST_Request $request Request instance.
	 * @param  string          $param   Param to validate.
	 * @return WP_Error|boolean
	 */
	public function rest_validate_date( $value, $request, $param ) {

		$attributes = $request->get_attributes();
		$args       = $attributes['args'][ $param ];

		if ( 'string' === $args['type'] && ! is_string( $value ) ) {
			/* translators: 1: param 2: type */
			return new WP_Error( 'woocommerce_gc_rest_api_invalid_param', sprintf( __( '%1$s is not of type %2$s', 'woocommerce-gift-cards' ), $param, 'string' ) );
		}

		if ( 'date' === $args['format'] ) {
			$regex = '#^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$#';

			if ( ! preg_match( $regex, $value, $matches ) ) {
				/* translators: 1: param 2: format */
				return new WP_Error( 'woocommerce_gc_rest_api_invalid_date_format', sprintf( __( '%1$s is not of date format: %2$s', 'woocommerce-gift-cards' ), $param, 'ISO8601 (Y-m-d H:i:s)' ) );
			}
		}

		return true;
	}

	/**
	 * Sanitize and format the given date.
	 *
	 * @param  string          $date_str Value being sanitized.
	 * @param  WP_REST_Request $request  The Request.
	 * @param  string          $param    The param being sanitized.
	 * @return int
	 */
	public function rest_sanitize_date( $date_str, $request, $param ) {
		$date = wc_string_to_datetime( $date_str );
		return $date->getTimestamp();
	}

	/**
	 * Get a single Gift Card.
	 *
	 * @param  WP_REST_Request $request Request data.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_item( $request ) {

		if ( empty( $request->get_param( 'id' ) ) ) {
			return new WP_Error( 'woocommerce_gc_rest_api_gift_card_invalid', __( 'Resource does not exist.', 'woocommerce-gift-cards' ), array( 'status' => 404 ) );
		}

		$gift_card_obj = wc_gc_get_gift_card( $request->get_param( 'id' ) );
		if ( false === $gift_card_obj ) {
			return new WP_Error( 'woocommerce_gc_rest_api_gift_card_invalid', __( 'Resource does not exist.', 'woocommerce-gift-cards' ), array( 'status' => 404 ) );
		}

		$data = array();
		$data = $this->prepare_item_for_response( $gift_card_obj, $request );
		$data = $this->prepare_response_for_collection( $data );

		return rest_ensure_response( $data );
	}

	/**
	 * Get all Gift Cards.
	 *
	 * @param  WP_REST_Request $request Request data.
	 * @return WP_REST_Response
	 */
	public function get_items( $request ) {

		$total_rows = (int) WC_GC()->db->giftcards->query(
			array(
				'count' => true,
			)
		);

		$page      = (int) $request['page'];
		$per_page  = (int) ( $request['per_page'] > 0 ? $request['per_page'] : 10 );
		$max_pages = ceil( $total_rows / $per_page );

		$prepared_args = array(
			'return'   => 'objects',
			'order_by' => array(
				$request['orderby'] => $request['order'],
			),
			'offset'   => ( $page - 1 ) * $per_page,
			'limit'    => $per_page,
		);

		/**
		 * Filter arguments, before passing to WC_GC()->db->giftcards->query, when querying Gift Cards via the REST API.
		 *
		 * @param array           $prepared_args Array of arguments for the query.
		 * @param WP_REST_Request $request       The current request.
		 */
		$prepared_args = (array) apply_filters( 'woocommerce_gc_rest_api_gift_cards_query', $prepared_args, $request );
		$gift_cards    = WC_GC()->db->giftcards->query( $prepared_args );

		$data = array();
		foreach ( $gift_cards as $gift_card_obj ) {
			$gift_card = $this->prepare_item_for_response( $gift_card_obj, $request );
			$gift_card = $this->prepare_response_for_collection( $gift_card );
			$data[]    = $gift_card;
		}

		$response = rest_ensure_response( $data );
		$response->header( 'X-WP-Total', $total_rows );
		$response->header( 'X-WP-TotalPages', $max_pages );

		$base = add_query_arg( $request->get_query_params(), rest_url( sprintf( '/%s/%s', $this->namespace, $this->rest_base ) ) ); // nosemgrep: audit.php.wp.security.xss.query-arg

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
	 * Create a single Gift Card.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_item( $request ) {

		try {

			$request = $this->maybe_prepare_attributes_for_batch_request( $request );
			if ( is_wp_error( $request ) ) {
				return rest_convert_error_to_response( $request );
			}

			$params = $request->get_params();

			// Let's get the params and exclude the readonly ones.
			// According to rest_get_endpoint_args_for_schema, all readonly fields are excluded from the schema definition.
			$endpoint_args = $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE );
			foreach ( $params as $key => $param ) {
				if ( empty( $endpoint_args[ $key ] ) ) {
					unset( $params[ $key ] );
				}
			}

			// Keep the redeemed_by outside of the args, so we can call the redeem function later on.
			$should_redeem = false;
			$redeemed_by   = 0;
			if ( ! empty( $params['redeemed_by'] ) ) {
				$should_redeem = true;
				$redeemed_by   = absint( $params['redeemed_by'] );
				unset( $params['redeemed_by'] );
			}

			$cached_is_active = $params['is_active'];
			if ( ! empty( $params['is_active'] ) && 'off' === $params['is_active'] ) {
				$params['is_active'] = 'on';
			}

			$args = wp_parse_args(
				$params,
				array(
					'order_id'      => 0,
					'order_item_id' => 0,
					'deliver_date'  => 0,
					'expire_date'   => 0,
					'currency'      => get_woocommerce_currency(),
				)
			);

			try {
				WC_GC()->db->giftcards->validate( $args );
			} catch ( Exception $e ) {
				throw new Exception( $e->getMessage(), 400 );
			}

			// Check if expire date is earlier than create date.
			if ( ! empty( $args['expire_date'] ) && ! empty( $args['create_date'] ) && ( $args['expire_date'] <= $args['create_date'] ) ) {
				throw new Exception( __( 'Τhe expiration date cannot precede the creation date.', 'woocommerce-gift-cards' ), 400 );
			}

			// Check if deliver date is earlier than create date.
			if ( ! empty( $args['deliver_date'] ) && ! empty( $args['create_date'] ) && ( $args['deliver_date'] <= $args['create_date'] ) ) {
				throw new Exception( __( 'The delivery date cannot precede the creation date.', 'woocommerce-gift-cards' ), 400 );
			}

			// Check if currency is valid.
			if ( empty( $args['currency'] ) || ! in_array( $args['currency'], array_keys( get_woocommerce_currencies() ), true ) ) {
				throw new Exception( __( 'Invalid currency.', 'woocommerce-gift-cards' ), 400 );
			}

			// We need to transform meta_data[0][key] and meta_data[0][value].
			if ( ! empty( $args['meta_data'] ) ) {
				$prepared_meta_data = array();
				foreach ( $args['meta_data'] as $meta ) {

					// Key is mandatory but value can be empty (hence the empty vs !array_key_exists).
					if ( empty( $meta['key'] ) || ! array_key_exists( 'value', $meta ) ) {
						throw new Exception( __( 'Invalid meta_data array. "key/value" should exist in all entries.', 'woocommerce-gift-cards' ), 400 );
					}

					$prepared_meta_data[ $meta['key'] ] = $meta['value'];

				}

				// Skip _currency meta.
				if ( ! empty( $prepared_meta_data['_currency'] ) ) {
					unset( $prepared_meta_data['_currency'] );
				}

				$args['meta_data'] = $prepared_meta_data;
			}

			$giftcard = new WC_GC_Gift_Card_Data( $args );
			$giftcard->add_meta( '_currency', $args['currency'] );
			$giftcard->save();

			if ( ! $giftcard->get_id() ) {
				throw new Exception( __( 'Resource cannot be created.', 'woocommerce-gift-cards' ) );
			}

			// Schedule a gift card if we have a deliver date.
			if ( ! $giftcard->is_delivered() && $giftcard->get_deliver_date() > 0 ) {

				$schedule_args = array(
					'giftcard' => $giftcard->get_id(),
					'order_id' => $giftcard->get_order_id(),
				);

				WC()->queue()->schedule_single( $giftcard->get_deliver_date(), 'woocommerce_gc_schedule_send_gift_card_to_customer', $schedule_args, 'send_giftcards' );
			}

			// Add an issued activity.
			$issued_args = array(
				'type'       => 'issued',
				'user_id'    => 0,
				'user_email' => $giftcard->get_sender_email(),
				'object_id'  => $giftcard->get_order_id(),
				'gc_id'      => $giftcard->get_id(),
				'gc_code'    => $giftcard->get_code(),
				'amount'     => $giftcard->get_initial_balance(),
				'date'       => $giftcard->get_date_created(),
			);

			$issued_activity = new WC_GC_Activity_Data();
			$issued_activity->set_all( $issued_args );
			$issued_activity->save();

			// If ( redeemed by <> 0 ) then fill in the redeem date and add a redeem activity.
			if ( $should_redeem ) {
				$gc = new WC_GC_Gift_Card( $giftcard );
				$gc->redeem( $redeemed_by );
				$giftcard = $gc->data;
			}

			if ( 'off' === $cached_is_active ) {
				$giftcard->set_active( $cached_is_active );
				$giftcard->save();
			}

			$request->set_param( 'id', $giftcard->get_id() );
			$response = $this->get_item( $request );
			$response->set_status( 201 );
			$response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $giftcard->get_id() ) ) );

			return $response;

		} catch ( Exception $e ) {
			$data = array( 'status' => $e->getCode() ? $e->getCode() : 500 );
			return new WP_Error( 'woocommerce_gc_rest_api_gift_card_not_created', $e->getMessage(), $data );
		}
	}

	/**
	 * Update a single Gift Card.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_item( $request ) {

		try {

			$giftcard_id = isset( $request['id'] ) ? absint( $request['id'] ) : 0;
			if ( 0 === $giftcard_id ) {
				throw new Exception( __( 'Invalid gift card ID.', 'woocommerce-gift-cards' ), 404 );
			}

			$giftcard = wc_gc_get_gift_card( $giftcard_id );
			if ( false === $giftcard ) {
				throw new Exception( __( 'Invalid gift card ID.', 'woocommerce-gift-cards' ), 404 );
			}

			$request = $this->maybe_prepare_attributes_for_batch_request( $request );
			if ( is_wp_error( $request ) ) {
				return rest_convert_error_to_response( $request );
			}

			$params = $request->get_params();

			// Let's get the params and exclude the readonly ones.
			// According to rest_get_endpoint_args_for_schema, all readonly fields are excluded from the schema definition.
			$endpoint_args = $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE );
			foreach ( $params as $key => $param ) {
				if ( empty( $endpoint_args[ $key ] ) ) {
					unset( $params[ $key ] );
				}
			}

			// Cache previous order ID to be used in AS jobs.
			$previous_order_id = $giftcard->get_order_id();

			$args = wp_parse_args( $params, array() );
			// Check if expire date is earlier than create date.
			if ( ! empty( $args['expire_date'] ) && ! empty( $giftcard->get_date_created() ) && ( $args['expire_date'] <= $giftcard->get_date_created() ) ) {
				throw new Exception( __( 'The expiration date cannot precede the creation date.', 'woocommerce-gift-cards' ), 400 );
			}

			// Check if deliver date is earlier than create date.
			if ( ! empty( $args['deliver_date'] ) && ! empty( $giftcard->get_date_created() ) && ( $args['deliver_date'] <= $giftcard->get_date_created() ) ) {
				throw new Exception( __( 'The delivery date cannot precede the creation date.', 'woocommerce-gift-cards' ), 400 );
			}

			// We need to transform meta_data[0][key] and meta_data[0][value].
			if ( ! empty( $args['meta_data'] ) ) {
				$prepared_meta_data = array();

				foreach ( $args['meta_data'] as $meta ) {

					if ( empty( $meta['key'] ) ) {
						throw new Exception( __( 'Invalid meta_data array. At least "key" should exist in all entries.', 'woocommerce-gift-cards' ), 400 );
					}

					if ( ! array_key_exists( 'value', $meta ) ) {
						$giftcard->delete_meta( $meta['key'] );
						continue;
					}

					$prepared_meta_data[ $meta['key'] ] = $meta['value'];

				}

				$args['meta_data'] = $prepared_meta_data;
			}

			// Let's check if the values are different than the model and unset them from the args.
			foreach ( $args as $key => $arg ) {

				if ( 'meta_data' === $key ) {
					continue;
				}

				$getter_function = 'get_' . $key;
				if ( ! method_exists( 'WC_GC_Gift_Card_Data', $getter_function ) ) {
					continue;
				}

				$model_value = $giftcard->$getter_function();
				if ( $model_value === $arg ) {
					unset( $args[ $key ] );
				}
			}

			$blocked_early = array( 'is_active', 'deliver_date' );
			foreach ( $blocked_early as $blocked_param ) {

				if ( ! empty( $args[ $blocked_param ] ) ) {

					if ( 'is_active' === $blocked_param ) {

						if ( $giftcard->has_expired() ) {
							throw new Exception( __( 'Changing the status of an expired gift card is not allowed.', 'woocommerce-gift-cards' ), 400 );
						}
					} elseif ( 'deliver_date' === $blocked_param ) {
						if ( false !== $giftcard->is_delivered() ) {
							throw new Exception( __( 'Changing the delivery date of a delivered gift card is not allowed.', 'woocommerce-gift-cards' ), 400 );
						}
					}
				}
			}

			// Keep the redeemed_by outside of the args, so we can call the redeem function later on.
			$should_redeem = false;
			$redeemed_by   = 0;
			if ( ! empty( $args['redeemed_by'] ) ) {
				$should_redeem = true;
				$redeemed_by   = absint( $args['redeemed_by'] );
				unset( $args['redeemed_by'] );
			}

			$cached_is_active = '';
			if ( ! empty( $params['is_active'] ) && 'off' === $params['is_active'] ) {
				$cached_is_active = $params['is_active'];
				unset( $args['is_active'] );
			}

			if ( ! empty( $args ) ) {
				try {
					WC_GC()->db->giftcards->validate( $args, $giftcard );
				} catch ( Exception $e ) {
					throw new Exception( $e->getMessage(), 400 );
				}

				$giftcard->set_all( $args );

				if ( false === $giftcard->save() ) {
					throw new Exception( __( 'Resource cannot be updated.', 'woocommerce-gift-cards' ), 500 );
				}
			}

			// Schedule a gift card if we have a deliver date.
			if ( ! empty( $args['deliver_date'] ) && false === $giftcard->is_delivered() ) {

				if ( 0 !== $giftcard->get_deliver_date() ) {

					// Check if is scheduled and cancel action.
					$scheduled_date = WC()->queue()->get_next(
						'woocommerce_gc_schedule_send_gift_card_to_customer',
						array(
							'giftcard' => $giftcard->get_id(),
							'order_id' => $previous_order_id,
						),
						'send_giftcards'
					);
					if ( $scheduled_date ) {
						WC()->queue()->cancel(
							'woocommerce_gc_schedule_send_gift_card_to_customer',
							array(
								'giftcard' => $giftcard->get_id(),
								'order_id' => $giftcard->get_order_id(),
							),
							'send_giftcards'
						);
					}
				}

				// Re-schedule.
				WC()->queue()->schedule_single(
					$args['deliver_date'],
					'woocommerce_gc_schedule_send_gift_card_to_customer',
					array(
						'giftcard' => $giftcard->get_id(),
						'order_id' => $giftcard->get_order_id(),
					),
					'send_giftcards'
				);
			}

			// If ( redeemed by <> 0 ) then fill in the redeem date and add a redeem activity.
			if ( $should_redeem ) {
				$gc = new WC_GC_Gift_Card( $giftcard );
				$gc->redeem( $redeemed_by, true );
				$giftcard = $gc->data;
			}

			if ( 'off' === $cached_is_active ) {
				$giftcard->set_active( $cached_is_active );
				$giftcard->save();
			}

			$request->set_param( 'id', $giftcard->get_id() );
			$response = $this->get_item( $request );
			$response->set_status( 200 );
			$response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $giftcard->get_id() ) ) );

			return $response;

		} catch ( Exception $e ) {
			$data = array( 'status' => $e->getCode() ? $e->getCode() : 500 );
			return new WP_Error( 'woocommerce_gc_rest_api_gift_card_not_updated', $e->getMessage(), $data );
		}
	}

	/**
	 * Delete a single Gift Card.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_item( $request ) {

		try {

			$giftcard_id = isset( $request['id'] ) ? absint( $request['id'] ) : 0;
			if ( 0 === $giftcard_id ) {
				throw new Exception( __( 'Invalid gift card ID.', 'woocommerce-gift-cards' ), 404 );
			}

			$giftcard = wc_gc_get_gift_card( $giftcard_id );
			if ( false === $giftcard ) {
				throw new Exception( __( 'Invalid gift card ID.', 'woocommerce-gift-cards' ), 404 );
			}

			$request = $this->maybe_prepare_attributes_for_batch_request( $request );
			if ( is_wp_error( $request ) ) {
				return rest_convert_error_to_response( $request );
			}

			$request->set_param( 'context', 'view' );
			$data = array(
				'id'      => $giftcard_id,
				'success' => false,
			);

			// Perform Delete.
			WC_GC()->db->giftcards->delete( $giftcard );

			// TODO: delete above should return true/false to avoid the following additional query.
			$deleted_giftcard = WC_GC()->db->giftcards->get( $giftcard_id );
			if ( ! is_a( $deleted_giftcard, 'WC_GC_Gift_Card_Data' ) || ! $deleted_giftcard->get_id() ) {
				$data['success'] = true;
			}

			return rest_ensure_response( $data );

		} catch ( Exception $e ) {
			$data = array( 'status' => $e->getCode() ? $e->getCode() : 500 );
			return new WP_Error( 'woocommerce_gc_rest_api_gift_card_not_deleted', $e->getMessage(), $data );
		}
	}

	/**
	 * Prepare the Gift Card for the REST response.
	 *
	 * @param  WC_GC_Gift_Card_Data $item    Gift Card data.
	 * @param  WP_REST_Request      $request Request object.
	 * @return WP_REST_Response
	 */
	public function prepare_item_for_response( $item, $request ) {

		$activities_query_args = array(
			'gc_id' => $item->get_id(),
		);
		$activities            = WC_GC()->db->activity->query( $activities_query_args );
		$activities_prepared   = array();
		foreach ( $activities as $activity ) {
			$activities_prepared[] = $this->prepare_activity_data_item_for_response( $activity );
		}

		// We need to re-fetch in order to get meta IDs.
		global $wpdb;
		$raw_meta_data = $wpdb->get_results(
			$wpdb->prepare(
				"
			SELECT meta_id, meta_key, meta_value
			FROM {$wpdb->prefix}woocommerce_gc_cardsmeta
			WHERE gc_giftcard_id = %d
			ORDER BY meta_id
		",
				$item->get_id()
			)
		);

		$meta_data_prepared = array();
		foreach ( $raw_meta_data as $meta ) {

			// Skip _currency meta.
			if ( '_currency' === $meta->meta_key ) {
				continue;
			}

			$meta_data_prepared[] = $this->prepare_meta_data_item_for_response( $meta );
		}

		$data = array(
			'id'            => $item->get_id(),
			'code'          => $item->get_code(),
			'recipient'     => $item->get_recipient(),
			'sender'        => $item->get_sender(),
			'sender_email'  => $item->get_sender_email(),
			'message'       => $item->get_message(),
			'balance'       => $item->get_initial_balance(),
			'remaining'     => $item->get_balance(),
			'order_id'      => $item->get_order_id(),
			'order_item_id' => $item->get_order_item_id(),
			'create_date'   => $item->get_date_created() ? date_i18n( 'Y-m-d H:i:s', $item->get_date_created() ) : 0,
			'deliver_date'  => $item->get_deliver_date() ? date_i18n( 'Y-m-d H:i:s', $item->get_deliver_date() ) : 0,
			'expire_date'   => $item->get_expire_date() ? date_i18n( 'Y-m-d H:i:s', $item->get_expire_date() ) : 0,
			'redeem_date'   => $item->get_date_redeemed() ? date_i18n( 'Y-m-d H:i:s', $item->get_date_redeemed() ) : 0,
			'redeemed_by'   => $item->get_redeemed_by(),
			'currency'      => $item->get_meta( '_currency' ) ? $item->get_meta( '_currency' ) : get_woocommerce_currency(),
			'delivered'     => false === $item->is_delivered() ? 'no' : $item->is_delivered(),
			'is_active'     => $item->is_active() ? 'on' : 'off',
			'activities'    => $activities_prepared,
			'meta_data'     => $meta_data_prepared,
		);

		$context = empty( $request['context'] ) ? 'view' : $request['context'];
		$data    = $this->add_additional_fields_to_object( $data, $request );
		$data    = $this->filter_response_by_context( $data, $context );

		// Wrap the data in a response object.
		$response = rest_ensure_response( $data );
		$response->add_links( $this->prepare_links( $data['id'] ) );

		return $response;
	}

	/**
	 * Prepare the Gift Card Activity for the REST response.
	 *
	 * @param  array $activity
	 * @return array
	 */
	protected function prepare_activity_data_item_for_response( $activity ) {

		$prepared_activity               = array();
		$prepared_activity['id']         = (int) $activity['id'];
		$prepared_activity['type']       = $activity['type'];
		$prepared_activity['user_id']    = (int) $activity['user_id'];
		$prepared_activity['user_email'] = $activity['user_email'];
		$prepared_activity['object_id']  = (int) $activity['object_id'];
		$prepared_activity['gc_id']      = (int) $activity['gc_id'];
		$prepared_activity['gc_code']    = $activity['gc_code'];
		$prepared_activity['amount']     = (float) $activity['amount'];
		$prepared_activity['date']       = $activity['date'] ? date_i18n( 'Y-m-d H:i:s', $activity['date'] ) : 0;
		$prepared_activity['note']       = $activity['note'];

		return $prepared_activity;
	}

	/**
	 * Prepare the Gift Card meta data for the REST response.
	 *
	 * @param  object $meta Singe meta object.
	 * @return array
	 */
	protected function prepare_meta_data_item_for_response( $meta ) {

		$meta_prepared = array(
			'id'    => absint( $meta->meta_id ),
			'key'   => $meta->meta_key,
			'value' => maybe_unserialize( $meta->meta_value ),
		);

		return $meta_prepared;
	}

	/**
	 * Prepare links for the request.
	 *
	 * @param  int $gift_card_id Given Gift Card ID.
	 * @return array
	 */
	protected function prepare_links( $gift_card_id ) {
		$base  = '/' . $this->namespace . '/' . $this->rest_base;
		$links = array(
			'self'       => array(
				'href' => rest_url( trailingslashit( $base ) . $gift_card_id ),
			),
			'collection' => array(
				'href' => rest_url( $base ),
			),
		);

		return $links;
	}

	/**
	 * Check whether a given request has permission to read a Gift Cards.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_item_permissions_check( $request ) {

		if ( ! $this->wc_rest_check_gift_card_permissions( 'gift_cards', 'read' ) ) {
			return new WP_Error( 'woocommerce_rest_cannot_view', __( 'Sorry, you cannot list resources.', 'woocommerce-gift-cards' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}


	/**
	 * Check whether a given request has permission to read Gift Cards.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_items_permissions_check( $request ) {

		if ( ! $this->wc_rest_check_gift_card_permissions( 'gift_cards', 'read' ) ) {
			return new WP_Error( 'woocommerce_rest_cannot_view', __( 'Sorry, you cannot list resources.', 'woocommerce-gift-cards' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Get the query params for collections.
	 *
	 * @return array
	 */
	public function get_collection_params() {

		$params = parent::get_collection_params();

		unset( $params['search'] );

		$params['orderby'] = array(
			'description'       => __( 'Sort collection by object attribute.', 'woocommerce-gift-cards' ),
			'type'              => 'string',
			'default'           => 'id',
			'enum'              => array(
				'id',
				'create_date',
				'deliver_date',
				'balance',
				'remaining',
				'order_id',
			),
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['order']   = array(
			'description'       => __( 'Order sort attribute ascending or descending.', 'woocommerce-gift-cards' ),
			'type'              => 'string',
			'default'           => 'desc',
			'enum'              => array( 'asc', 'desc' ),
			'validate_callback' => 'rest_validate_request_arg',
		);

		return $params;
	}

	/**
	 * Check if a given request has access to create Gift Cards.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function create_item_permissions_check( $request ) {

		if ( ! $this->wc_rest_check_gift_card_permissions( 'gift_cards', 'create' ) ) {
			return new WP_Error( 'woocommerce_rest_cannot_create', __( 'Sorry, you cannot create resources.', 'woocommerce-gift-cards' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Check whether a given request has permission to edit Gift Cards.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function update_item_permissions_check( $request ) {

		if ( ! $this->wc_rest_check_gift_card_permissions( 'gift_cards', 'edit' ) ) {
			return new WP_Error( 'woocommerce_rest_cannot_edit', __( 'Sorry, you are not allowed to edit this resource.', 'woocommerce-gift-cards' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Check whether a given request has permission to delete Gift Cards.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function delete_item_permissions_check( $request ) {

		if ( ! $this->wc_rest_check_gift_card_permissions( 'gift_cards', 'delete' ) ) {
			return new WP_Error( 'woocommerce_rest_cannot_delete', __( 'Sorry, you are not allowed to delete this resource.', 'woocommerce-gift-cards' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Check if a given request has access to batch manage Gift Cards.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function batch_items_permissions_check( $request ) {
		if ( ! $this->wc_rest_check_gift_card_permissions( 'gift_cards', 'batch' ) ) {
			return new WP_Error( 'woocommerce_rest_cannot_batch', __( 'Sorry, you are not allowed to batch manipulate this resource.', 'woocommerce-gift-cards' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Get the Gift Card's schema, conforming to JSON Schema.
	 *
	 * @return array
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'gift_card',
			'type'       => 'object',
			'properties' => array(
				'id'            => array(
					'description' => __( 'Unique identifier for the resource.', 'woocommerce-gift-cards' ),
					'context'     => array( 'view', 'edit' ),
					'type'        => 'integer',
					'readonly'    => true,
				),
				'code'          => array(
					'description' => __( 'Code.', 'woocommerce-gift-cards' ),
					'context'     => array( 'view', 'edit' ),
					'type'        => 'string',
				),
				'recipient'     => array(
					'description' => __( 'Recipient.', 'woocommerce-gift-cards' ),
					'context'     => array( 'view', 'edit' ),
					'type'        => 'string',
					'format'      => 'email',
				),
				'sender'        => array(
					'description' => __( 'Sender.', 'woocommerce-gift-cards' ),
					'context'     => array( 'view', 'edit' ),
					'type'        => 'string',
				),
				'sender_email'  => array(
					'description' => __( 'Sender Email.', 'woocommerce-gift-cards' ),
					'context'     => array( 'view', 'edit' ),
					'type'        => 'string',
					'format'      => 'email',
				),
				'message'       => array(
					'description' => __( 'Message.', 'woocommerce-gift-cards' ),
					'context'     => array( 'view', 'edit' ),
					'type'        => 'string',
				),
				'balance'       => array(
					'description' => __( 'Issued Balance.', 'woocommerce-gift-cards' ),
					'context'     => array( 'view', 'edit' ),
					'type'        => 'number',
				),
				'remaining'     => array(
					'description' => __( 'Remaining balance.', 'woocommerce-gift-cards' ),
					'context'     => array( 'view', 'edit' ),
					'type'        => 'number',
					'readonly'    => true,
				),
				'order_id'      => array(
					'description' => __( 'Order ID.', 'woocommerce-gift-cards' ),
					'context'     => array( 'view', 'edit' ),
					'type'        => 'integer',
				),
				'order_item_id' => array(
					'description' => __( 'Order Item ID.', 'woocommerce-gift-cards' ),
					'context'     => array( 'view', 'edit' ),
					'type'        => 'integer',
				),
				'create_date'   => array(
					'description' => __( 'Creation date.', 'woocommerce-gift-cards' ),
					'context'     => array( 'view', 'edit' ),
					'type'        => 'string',
					'format'      => 'date',
					'readonly'    => true,
				),
				'deliver_date'  => array(
					'description' => __( 'Delivery date.', 'woocommerce-gift-cards' ),
					'context'     => array( 'view', 'edit' ),
					'type'        => 'string',
					'format'      => 'date',
					'arg_options' => array(
						'validate_callback' => array( $this, 'rest_validate_date' ),
						'sanitize_callback' => array( $this, 'rest_sanitize_date' ),
					),
				),
				'expire_date'   => array(
					'description' => __( 'Expiration date.', 'woocommerce-gift-cards' ),
					'context'     => array( 'view', 'edit' ),
					'type'        => 'string',
					'format'      => 'date',
					'arg_options' => array(
						'validate_callback' => array( $this, 'rest_validate_date' ),
						'sanitize_callback' => array( $this, 'rest_sanitize_date' ),
					),
				),
				'redeem_date'   => array(
					'description' => __( 'Date redeemed.', 'woocommerce-gift-cards' ),
					'context'     => array( 'view', 'edit' ),
					'type'        => 'string',
					'format'      => 'date',
					'readonly'    => true,
				),
				'redeemed_by'   => array(
					'description' => __( 'Redeemed by user (ID).', 'woocommerce-gift-cards' ),
					'context'     => array( 'view', 'edit' ),
					'type'        => 'integer',
				),
				'delivered'     => array(
					'description' => __( 'Delivered enum ("no", 0 for system deliver, > 0 admin id who triggered forced delivery).', 'woocommerce-gift-cards' ),
					'context'     => array( 'view', 'edit' ),
					'type'        => 'string',
					'readonly'    => true,
				),
				'is_active'     => array(
					'description' => __( 'Status.', 'woocommerce-gift-cards' ),
					'context'     => array( 'view', 'edit' ),
					'type'        => 'string',
					'enum'        => array( 'on', 'off' ),
					'default'     => 'on',
				),
				'currency'      => array(
					'description' => __( 'Currency code.', 'woocommerce-gift-cards' ),
					'context'     => array( 'view', 'edit' ),
					'type'        => 'string',
				),
				'activities'    => array(
					'description' => __( 'Activities.', 'woocommerce-gift-cards' ),
					'context'     => array( 'view', 'edit' ),
					'type'        => 'array',
					'readonly'    => true,
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'id'         => array(
								'description' => __( 'ID.', 'woocommerce-gift-cards' ),
								'context'     => array( 'view', 'edit' ),
								'type'        => 'integer',
								'readonly'    => true,
							),
							'type'       => array(
								'description' => __( 'Type.', 'woocommerce-gift-cards' ),
								'context'     => array( 'view', 'edit' ),
								'type'        => 'string',
								'readonly'    => true,
							),
							'user_id'    => array(
								'description' => __( 'User ID.', 'woocommerce-gift-cards' ),
								'context'     => array( 'view', 'edit' ),
								'type'        => 'integer',
								'readonly'    => true,
							),
							'user_email' => array(
								'description' => __( 'User Email.', 'woocommerce-gift-cards' ),
								'context'     => array( 'view', 'edit' ),
								'type'        => 'string',
								'readonly'    => true,
							),
							'object_id'  => array(
								'description' => __( 'Object ID.', 'woocommerce-gift-cards' ),
								'context'     => array( 'view', 'edit' ),
								'type'        => 'integer',
								'readonly'    => true,
							),
							'gc_id'      => array(
								'description' => __( 'Gift Card ID.', 'woocommerce-gift-cards' ),
								'context'     => array( 'view', 'edit' ),
								'type'        => 'integer',
								'readonly'    => true,
							),
							'gc_code'    => array(
								'description' => __( 'Gift Card Code.', 'woocommerce-gift-cards' ),
								'context'     => array( 'view', 'edit' ),
								'type'        => 'string',
								'readonly'    => true,
							),
							'amount'     => array(
								'description' => __( 'Amount.', 'woocommerce-gift-cards' ),
								'context'     => array( 'view', 'edit' ),
								'type'        => 'number',
								'readonly'    => true,
							),
							'date'       => array(
								'description' => __( 'Date.', 'woocommerce-gift-cards' ),
								'context'     => array( 'view', 'edit' ),
								'type'        => 'string',
								'readonly'    => true,
							),
							'note'       => array(
								'description' => __( 'Note.', 'woocommerce-gift-cards' ),
								'context'     => array( 'view', 'edit' ),
								'type'        => 'string',
								'readonly'    => true,
							),
						),
					),
				),
				'meta_data'     => array(
					'description' => __( 'Meta data.', 'woocommerce-gift-cards' ),
					'context'     => array( 'view', 'edit' ),
					'type'        => 'array',
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'id'    => array(
								'description' => __( 'Meta ID.', 'woocommerce-gift-cards' ),
								'context'     => array( 'view', 'edit' ),
								'type'        => 'integer',
								'readonly'    => true,
							),
							'key'   => array(
								'description' => __( 'Meta key.', 'woocommerce-gift-cards' ),
								'context'     => array( 'view', 'edit' ),
								'type'        => 'string',
							),
							'value' => array(
								'description' => __( 'Meta value.', 'woocommerce-gift-cards' ),
								'context'     => array( 'view', 'edit' ),
								'type'        => 'mixed',
							),
						),
					),
				),
			),
		);

		return $this->add_additional_fields_schema( $schema );
	}

	/**
	 * Fine tune endpoint args.
	 *
	 * @param  string $method HTTP method of the request (Optional).
	 * @return array
	 */
	public function get_endpoint_args_for_item_schema( $method = WP_REST_Server::CREATABLE ) {

		$endpoint_args = parent::get_endpoint_args_for_item_schema( $method );

		if ( WP_REST_Server::CREATABLE === $method ) {

			$endpoint_args['recipient']['required'] = true;
			$endpoint_args['sender']['required']    = true;
			$endpoint_args['balance']['required']   = true;

		} elseif ( WP_REST_Server::EDITABLE === $method ) {

			// Readonly fields should be unset from the schema as in rest_get_endpoint_args_for_schema.
			unset( $endpoint_args['code'], $endpoint_args['balance'] );

		} elseif ( WP_REST_Server::DELETABLE === $method ) {

			$endpoint_args = array();

		} elseif ( WP_REST_Server::ALLMETHODS === $method ) {

			$endpoint_args = array();

		}

		return $endpoint_args;
	}

	/**
	 * Check user permissions on REST API.
	 *
	 * @param  string $object  Object.
	 * @param  string $context Request context.
	 * @return bool
	 */
	public function wc_rest_check_gift_card_permissions( $object, $context = 'read' ) {
		$objects = array(
			'gift_cards' => 'manage_woocommerce',
		);

		$permission = current_user_can( $objects[ $object ] );

		return apply_filters( 'woocommerce_rest_check_permissions', $permission, $context, 0, $object );
	}

	/**
	 * Validate and decorate batch request with the necessary attributes, sanitizations.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Request|WP_Error The decorated request or error if there is no match.
	 */
	protected function maybe_prepare_attributes_for_batch_request( $request ) {

		$path = '/' . $this->namespace . '/' . $this->rest_base;

		// As of WC 6.6 all batch requests routes are not empty.
		$route = $path . '/batch';
		if ( ! empty( $request->get_route() ) && $route !== $request->get_route() ) {
			return $request;
		}

		if ( empty( $this->cached_batch_attributes[ $request->get_method() ] ) ) {
			/* @var WP_REST_Server $wp_rest_server */
			global $wp_rest_server;

			if ( 'PUT' === $request->get_method() || 'DELETE' === $request->get_method() ) {
				$path .= '/(?P<id>[\d]+)';
			}

			$routes     = $wp_rest_server->get_routes( $this->namespace );
			$attributes = array();
			if ( ! empty( $routes[ $path ] ) ) {
				foreach ( $routes[ $path ] as $route ) {
					if ( array_key_exists( $request->get_method(), $route['methods'] ) ) {
						$attributes = $route;
						break;
					}
				}
			}

			if ( ! empty( $attributes ) ) {
				$this->cached_batch_attributes[ $request->get_method() ] = $attributes;
			}
		}

		if ( empty( $this->cached_batch_attributes[ $request->get_method() ] ) ) {

			return new WP_Error(
				'woocommerce_gc_rest_api_gift_card_batch_error',
				/* translators: %s: Request method. */
				sprintf( __( '%s batch action could not be executed. No available request attributes.', 'woocommerce-gift-cards' ), $request->get_method() ),
				array( 'status' => 500 )
			);
		}

		$request->set_attributes( $this->cached_batch_attributes[ $request->get_method() ] );

		$error          = null;
		$check_required = $request->has_valid_params();
		if ( is_wp_error( $check_required ) ) {
			$error = $check_required;
		} else {
			$check_sanitized = $request->sanitize_params();
			if ( is_wp_error( $check_sanitized ) ) {
				$error = $check_sanitized;
			}
		}

		if ( is_wp_error( $error ) ) {
			return $error;
		}

		return $request;
	}
}
