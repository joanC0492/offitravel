<?php
/**
 * WC_GC_Importer class
 *
 * @package  WooCommerce Gift Cards
 * @since    1.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Include dependencies.
 */
if ( ! class_exists( 'WC_Importer_Interface', false ) ) {
	include_once WC_ABSPATH . 'includes/interfaces/class-wc-importer-interface.php';
}

/**
 * WC_GC_Importer Class.
 *
 * @version 2.7.4
 */
abstract class WC_GC_Importer implements WC_Importer_Interface {

	/**
	 * CSV file.
	 *
	 * @var string
	 */
	protected $file = '';

	/**
	 * The file position after the last read.
	 *
	 * @var int
	 */
	protected $file_position = 0;

	/**
	 * Importer parameters.
	 *
	 * @var array
	 */
	protected $params = array();

	/**
	 * Raw keys - CSV raw headers.
	 *
	 * @var array
	 */
	protected $raw_keys = array();

	/**
	 * Mapped keys - CSV headers.
	 *
	 * @var array
	 */
	protected $mapped_keys = array();

	/**
	 * Raw data.
	 *
	 * @var array
	 */
	protected $raw_data = array();

	/**
	 * Raw data.
	 *
	 * @var array
	 */
	protected $file_positions = array();

	/**
	 * Parsed data.
	 *
	 * @var array
	 */
	protected $parsed_data = array();

	/**
	 * Start time of current import.
	 *
	 * (default value: 0)
	 *
	 * @var int
	 */
	protected $start_time = 0;

	/**
	 * Get file raw headers.
	 *
	 * @return array
	 */
	public function get_raw_keys() {
		return $this->raw_keys;
	}

	/**
	 * Get file mapped headers.
	 *
	 * @return array
	 */
	public function get_mapped_keys() {
		return ! empty( $this->mapped_keys ) ? $this->mapped_keys : $this->raw_keys;
	}

	/**
	 * Get raw data.
	 *
	 * @return array
	 */
	public function get_raw_data() {
		return $this->raw_data;
	}

	/**
	 * Get parsed data.
	 *
	 * @return array
	 */
	public function get_parsed_data() {
		/**
		 * Filter product importer parsed data.
		 *
		 * @param array $parsed_data Parsed data.
		 * @param WC_Product_Importer $importer Importer instance.
		 */
		return apply_filters( 'woocommerce_gc_giftcards_importer_parsed_data', $this->parsed_data, $this );
	}

	/**
	 * Get importer parameters.
	 *
	 * @return array
	 */
	public function get_params() {
		return $this->params;
	}

	/**
	 * Get file pointer position from the last read.
	 *
	 * @return int
	 */
	public function get_file_position() {
		return $this->file_position;
	}

	/**
	 * Get file pointer position as a percentage of file size.
	 *
	 * @return int
	 */
	public function get_percent_complete() {
		$size = filesize( $this->file );
		if ( ! $size ) {
			return 0;
		}

		return absint( min( round( ( $this->file_position / $size ) * 100 ), 100 ) );
	}

	/**
	 * Prepare a single gift card for create or update.
	 *
	 * @param  array $data     Item data.
	 * @return WC_GC_Gift_Card_Data|WP_Error
	 */
	protected function get_giftcard_object( $data ) {

		$giftcard       = false;
		$search_by_code = false;

		// Sanity.
		if ( ! empty( $data['id'] ) && empty( $data['code'] ) ) {
			return new WP_Error(
				'woocommerce_gc_giftcards_csv_importer_missing_code',
				__( 'Missing gift card code.', 'woocommerce-gift-cards' ),
				array(
					'status' => 401,
				)
			);
		}

		if ( ! empty( $data['id'] ) ) {

			$id       = isset( $data['id'] ) ? absint( $data['id'] ) : 0;
			$giftcard = new WC_GC_Gift_Card_Data( $id );

		} elseif ( empty( $data['id'] ) && ! empty( $data['code'] ) ) {

			// Check for valid code.
			if ( ! wc_gc_is_gift_card_code( $data['code'] ) ) {
				return new WP_Error(
					'woocommerce_gc_giftcards_csv_importer_invalid_code_format',
					__( 'Invalid gift card code format.', 'woocommerce-gift-cards' ),
					array(
						'status' => 401,
					)
				);
			}

			$search_by_code = true;
			$giftcard       = wc_gc_get_gift_card_by_code( $data['code'] );
		}

		if ( ! is_a( $giftcard, 'WC_GC_Gift_Card_Data' ) || ! $giftcard->get_id() ) {

			if ( $search_by_code ) {

				return new WP_Error(
					'woocommerce_gc_giftcards_csv_importer_invalid_code',
					__( 'Invalid gift card code.', 'woocommerce-gift-cards' ),
					array(
						'status' => 401,
					)
				);

			} else {

				return new WP_Error(
					'woocommerce_gc_giftcards_csv_importer_invalid_id',
					__( 'Invalid gift card ID', 'woocommerce-gift-cards' ),
					array(
						'status' => 401,
					)
				);
			}
		}

		return apply_filters( 'woocommerce_gc_giftcards_import_get_giftcard_object', $giftcard, $data );
	}

	/**
	 * Process a single item and save.
	 *
	 * @throws Exception If item cannot be processed.
	 *
	 * @param  array $data Raw CSV data.
	 * @return array|WP_Error
	 */
	protected function process_item( $data ) {

		try {
			do_action( 'woocommerce_gc_giftcards_import_before_process_item', $data );

			$data   = apply_filters( 'woocommerce_gc_giftcards_import_process_item_data', $data );
			$object = $this->get_giftcard_object( $data );

			/*
			---------------------------------------------------*/
			/*
				Updating features are disabled.
			/*---------------------------------------------------*/
			$updating = false;
			if ( is_wp_error( $object ) ) {

				if ( 'woocommerce_gc_giftcards_csv_importer_invalid_code_format' === $object->get_error_code() ) {
					throw new Exception( __( 'Gift card codes must follow the XXXX-XXXX-XXXX-XXXX format. Leave this field blank to let the importer generate a code for you.', 'woocommerce-gift-cards' ) );
				}

				if ( 'woocommerce_gc_giftcards_csv_importer_missing_code' === $object->get_error_code() ) {
					throw new Exception( __( 'Assinging specific gift card ID requires a specific code as well. Leave the ID field blank to let the importer generate a code for you.', 'woocommerce-gift-cards' ) );
				}

				// Else create a new one to import. Continuing...
				$object = new WC_GC_Gift_Card_Data();

			} else {

				$updating = true;

				// A gift card is found... exit.
				throw new Exception( __( 'A gift card is found with the same properties.', 'woocommerce-gift-cards' ) );
			}

			/*
			---------------------------------------------------*/
			/*
				New gift card importing...
			/*---------------------------------------------------*/

			// If there is a remaining but not a issued_value then copy the amount accordingly.
			if ( ! isset( $data['balance'] ) && isset( $data['remaining'] ) ) {
				$data['balance'] = $data['remaining'];
			}

			// If there is balance (issued) but not remaining, copy the amount accordingly.
			if ( ! empty( $data['balance'] ) && ! isset( $data['remaining'] ) ) {
				$data['remaining'] = $data['balance'];
			}

			// Validation for importing process.
			$missing = array();
			if ( empty( $data['recipient'] ) ) {
				$missing[] = __( 'Recipient (must be a valid email address)', 'woocommerce-gift-cards' );
			}
			if ( empty( $data['sender'] ) ) {
				$missing[] = __( 'Sender', 'woocommerce-gift-cards' );
			}
			if ( empty( $data['balance'] ) ) {
				$missing[] = __( 'Issued value (must be a number)', 'woocommerce-gift-cards' );
			}
			if ( ! empty( $missing ) ) {
				/* translators: %s: comma-separated list of missing or invalid fields. */
				throw new Exception( sprintf( __( 'Missing or invalid gift card attributes: %s.', 'woocommerce-gift-cards' ), implode( ', ', $missing ) ) );
			}

			$defaults = array(
				'is_active'     => 'on',
				'is_virtual'    => 'on',
				'code'          => '',
				'order_id'      => 0,
				'order_item_id' => 0,
				'recipient'     => '',
				'redeemed_by'   => 0,
				'sender'        => '',
				'sender_email'  => '',
				'message'       => '',
				'balance'       => 0,
				'remaining'     => 0,
				'currency'      => get_woocommerce_currency(),
				'template_id'   => 'default',
				'create_date'   => 0,
				'deliver_date'  => 0,
				'delivered'     => 'no',
				'expire_date'   => 0,
				'redeem_date'   => 0,
				'meta_data'     => array(),
			);

			$data            = wp_parse_args( $data, $defaults );
			$data['context'] = 'import';
			WC_GC()->db->giftcards->validate( $data, $object );

			// Validate currency.
			if ( empty( $data['currency'] ) ) {
				$data['currency'] = get_woocommerce_currency();
			}

			if ( ! in_array( $data['currency'], array_keys( get_woocommerce_currencies() ), true ) ) {
				throw new Exception( __( 'Invalid currency.', 'woocommerce-gift-cards' ) );
			}

			// Convert null to empty strings.
			// Hint: dbDelta always creates fields that do not accept null values. A CSV could have the word 'NULL' in values.
			foreach ( $data as $key => $value ) {
				if ( is_null( $value ) ) {
					$data[ $key ] = '';
				}
			}

			// Fill fields.
			$object->set_all( $data );
			$object->add_meta( '_currency', $data['currency'] );

			$object = apply_filters( 'woocommerce_gc_giftcards_import_pre_insert_giftcard_object', $object, $data );

			// Add gift card to DB.
			if ( is_integer( $object->create() ) ) {

				$object->save_meta_data();

				// Handle possible new scheduled giftcards.
				if ( ! $object->is_delivered() && $object->get_deliver_date() > 0 ) {

					$args = array(
						'giftcard' => $object->get_id(),
						'order_id' => 0,
					);

					WC()->queue()->schedule_single( $object->get_deliver_date(), 'woocommerce_gc_schedule_send_gift_card_to_customer', $args, 'send_giftcards' );
				}
			} else {

				throw new Exception( __( 'A database error occured while trying to import this gift card.', 'woocommerce-gift-cards' ) );
			}

			/*
			---------------------------------------------------*/
			/*
				Activities/Logs.
			/*---------------------------------------------------*/
			$has_issued_activity = false;

			if ( ! empty( $data['activities'] ) ) {

				foreach ( $data['activities'] as $activity_data ) {

					if ( ! $has_issued_activity && isset( $activity_data['type'] ) && 'issued' === $activity_data['type'] ) {
						$has_issued_activity = true;
					}

					// Create new instance.
					$activity = new WC_GC_Activity_Data();
					$this->set_activity_data( $activity_data, $object );
					$activity->set_all( $activity_data );

					// Save.
					$activity->save();
				}
			}

			if ( ! $has_issued_activity ) {

				$user_id    = 0;
				$user_email = '';

				// Fetch giftcard user.
				$user = wp_get_current_user();
				if ( ! $user || ! is_a( $user, 'WP_User' ) ) {
					$user_email = $object->get_sender_email();
					$user_id    = 0;
				} else {
					$user_email = $user->user_email;
					$user_id    = $user->ID;
				}

				// Add an issued activity.
				$issued_args     = array(
					'type'       => 'issued',
					'user_id'    => $user_id,
					'user_email' => $user_email,
					'object_id'  => $object->get_order_id(),
					'gc_id'      => $object->get_id(),
					'gc_code'    => $object->get_code(),
					'amount'     => $object->get_initial_balance(),
					'date'       => $object->get_date_created(),
				);
				$issued_activity = new WC_GC_Activity_Data();
				$issued_activity->set_all( $issued_args );
				$issued_activity->save();
			}

			do_action( 'woocommerce_gc_giftcards_import_inserted_giftcard_object', $object, $data );

			return array(
				'id'      => $object->get_id(),
				'updated' => $updating,
			);

		} catch ( Exception $e ) {
			return new WP_Error( 'woocommerce_gc_giftcards_importer_error', $e->getMessage(), array( 'status' => $e->getCode() ) );
		}
	}

	/**
	 * Set activity data based on imported object.
	 *
	 * @param array                &$data
	 * @param WC_GC_Gift_Card_Data $giftcard
	 */
	protected function set_activity_data( &$data, $giftcard ) {

		/*
		 * Do not let importer to try and fix obvious data failures.
		 */
		if ( ! (bool) apply_filters( 'woocommerce_gc_giftcards_importer_modify_activity_data', true ) ) {
			return;
		}

		// Tranpose new giftcard id and code.
		$data['gc_id']   = $giftcard->get_id();
		$data['gc_code'] = $giftcard->get_code();

		if ( isset( $data['type'] ) && 'issued' === $data['type'] ) {
			$data['object_id'] = $giftcard->get_order_id();
			$data['amount']    = $giftcard->get_initial_balance();
			$data['date']      = $giftcard->get_date_created();
		}

		// Do not keep id.
		unset( $data['id'] );
	}

	/**
	 * Memory exceeded
	 *
	 * Ensures the batch process never exceeds 90%
	 * of the maximum WordPress memory.
	 *
	 * @return bool
	 */
	protected function memory_exceeded() {
		$memory_limit   = $this->get_memory_limit() * 0.9; // 90% of max memory
		$current_memory = memory_get_usage( true );
		$return         = false;
		if ( $current_memory >= $memory_limit ) {
			$return = true;
		}
		return apply_filters( 'woocommerce_gc_giftcards_importer_memory_exceeded', $return );
	}

	/**
	 * Get memory limit
	 *
	 * @return int
	 */
	protected function get_memory_limit() {
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

		return intval( $memory_limit ) * 1024 * 1024;
	}

	/**
	 * Time exceeded.
	 *
	 * Ensures the batch never exceeds a sensible time limit.
	 * A timeout limit of 30s is common on shared hosting.
	 *
	 * @return bool
	 */
	protected function time_exceeded() {
		$finish = $this->start_time + apply_filters( 'woocommerce_gc_giftcards_importer_default_time_limit', 20 ); // 20 seconds
		$return = false;
		if ( time() >= $finish ) {
			$return = true;
		}
		return apply_filters( 'woocommerce_gc_giftcards_importer_time_exceeded', $return );
	}

	/**
	 * Explode CSV cell values using commas by default, and handling escaped
	 * separators.
	 *
	 * @param  string $value     Value to explode.
	 * @param  string $separator Separator separating each value. Defaults to comma.
	 * @return array
	 */
	protected function explode_values( $value, $separator = ',' ) {
		$value  = str_replace( '\\,', '::separator::', $value );
		$values = explode( $separator, $value );
		$values = array_map( array( $this, 'explode_values_formatter' ), $values );

		return $values;
	}

	/**
	 * Remove formatting and trim each value.
	 *
	 * @param  string $value Value to format.
	 * @return string
	 */
	protected function explode_values_formatter( $value ) {
		return trim( str_replace( '::separator::', ',', $value ) );
	}

	/**
	 * The exporter prepends a ' to escape fields that start with =, +, - or @.
	 * Remove the prepended ' character preceding those characters.
	 *
	 * @param  string $value A string that may or may not have been escaped with '.
	 * @return string
	 */
	protected function unescape_data( $value ) {
		$active_content_triggers = array( "'=", "'+", "'-", "'@" );

		if ( in_array( mb_substr( $value, 0, 2 ), $active_content_triggers, true ) ) {
			$value = mb_substr( $value, 1 );
		}

		return $value;
	}
}
