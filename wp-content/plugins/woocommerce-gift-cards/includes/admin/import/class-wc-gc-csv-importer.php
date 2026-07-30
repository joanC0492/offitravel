<?php
/**
 * WC_GC_CSV_Importer class
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
if ( ! class_exists( 'WC_GC_Importer', false ) ) {
	include_once WC_GC_ABSPATH . 'includes/admin/import/abstract-class-wc-gc-importer.php';
}

if ( ! class_exists( 'WC_GC_CSV_Importer_Controller', false ) ) {
	include_once WC_GC_ABSPATH . 'includes/admin/import/class-wc-gc-csv-importer-controller.php';
}

/**
 * WC_GC_CSV_Importer Class.
 *
 * @version 2.7.3
 */
class WC_GC_CSV_Importer extends WC_GC_Importer {

	/**
	 * Tracks current row being parsed.
	 *
	 * @var integer
	 */
	protected $parsing_raw_data_index = 0;

	/**
	 * Initialize importer.
	 *
	 * @param string $file   File to read.
	 * @param array  $params Arguments for the parser.
	 */
	public function __construct( $file, $params = array() ) {
		$default_args = array(
			'start_pos'        => 0, // File pointer start.
			'end_pos'          => -1, // File pointer end.
			'lines'            => -1, // Max lines to read.
			'mapping'          => array(), // Column mapping. csv_heading => schema_heading.
			'parse'            => false, // Whether to sanitize and format data.
			'update_existing'  => false, // Whether to update existing items.
			'delimiter'        => ',', // CSV delimiter.
			'prevent_timeouts' => true, // Check memory and time usage and abort if reaching limit.
			'enclosure'        => '"', // The character used to wrap text in the CSV.
			'escape'           => "\0", // PHP uses '\' as the default escape character. This is not RFC-4180 compliant. This disables the escape character.
		);

		$this->params = wp_parse_args( $params, $default_args );
		$this->file   = $file;

		if ( isset( $this->params['mapping']['from'], $this->params['mapping']['to'] ) ) {
			$this->params['mapping'] = array_combine( $this->params['mapping']['from'], $this->params['mapping']['to'] );
		}

		// Import mappings for CSV data.
		include_once WC_ABSPATH . 'includes/admin/importers/mappings/mappings.php';

		$this->read_file();
	}

	/**
	 * Read file.
	 */
	protected function read_file() {

		if ( WC_GC_CSV_Importer_Controller::is_within_phar( $this->file ) ) {
			wp_die( esc_html__( 'Invalid file path. Files must must not be within a PHAR executable.', 'woocommerce-gift-cards' ) );
		}

		if ( ! WC_GC_CSV_Importer_Controller::is_within_abspath( $this->file ) && ! WC_GC_CSV_Importer_Controller::is_within_wp_content( $this->file ) ) {
			wp_die( esc_html__( 'Invalid file path. Files must be uploaded to a location inside the WordPress absolute path or wp-content.', 'woocommerce-gift-cards' ) );
		}

		if ( ! WC_GC_CSV_Importer_Controller::is_file_valid_csv( $this->file ) ) {
			wp_die( esc_html__( 'Invalid file type. The importer supports CSV and TXT file formats.', 'woocommerce' ) );
		}

		$handle = fopen( $this->file, 'r' );

		if ( false !== $handle ) {

			$this->raw_keys = array_map( 'trim', fgetcsv( $handle, 0, $this->params['delimiter'], $this->params['enclosure'], $this->params['escape'] ) );

			// Remove BOM signature from the first item.
			if ( isset( $this->raw_keys[0] ) ) {
				$this->raw_keys[0] = $this->remove_utf8_bom( $this->raw_keys[0] );
			}

			if ( 0 !== $this->params['start_pos'] ) {
				fseek( $handle, (int) $this->params['start_pos'] );
			}

			while ( 1 ) {
				$row = fgetcsv( $handle, 0, $this->params['delimiter'], $this->params['enclosure'], $this->params['escape'] );

				if ( false !== $row ) {
					$this->raw_data[]                                 = $row;
					$this->file_positions[ count( $this->raw_data ) ] = ftell( $handle );

					if ( ( $this->params['end_pos'] > 0 && ftell( $handle ) >= $this->params['end_pos'] ) || 0 === --$this->params['lines'] ) {
						break;
					}
				} else {
					break;
				}
			}

			$this->file_position = ftell( $handle );
		}

		if ( ! empty( $this->params['mapping'] ) ) {
			$this->set_mapped_keys();
		}

		if ( $this->params['parse'] ) {
			$this->set_parsed_data();
		}
	}

	/**
	 * Remove UTF-8 BOM signature.
	 *
	 * @param string $string String to handle.
	 *
	 * @return string
	 */
	protected function remove_utf8_bom( $string ) {
		if ( 'efbbbf' === substr( bin2hex( $string ), 0, 6 ) ) {
			$string = substr( $string, 3 );
		}

		return $string;
	}

	/**
	 * Set file mapped keys.
	 */
	protected function set_mapped_keys() {
		$mapping = $this->params['mapping'];
		foreach ( $this->raw_keys as $key ) {
			$this->mapped_keys[] = isset( $mapping[ $key ] ) ? $mapping[ $key ] : $key;
		}
	}

	/**
	 * Get formatting callback.
	 *
	 * @return array
	 */
	protected function get_formatting_callback() {

		/**
		 * Columns not mentioned here will get parsed with 'wc_clean'.
		 * column_name => callback.
		 */
		$data_formatting = array(
			'id'              => array( $this, 'parse_id_field' ),
			'code'            => array( $this, 'parse_code_field' ),
			'recipient'       => array( $this, 'parse_email_field' ),
			'sender_email'    => array( $this, 'parse_email_field' ),
			'balance'         => array( $this, 'parse_decimal_field' ),
			'remaining'       => array( $this, 'parse_decimal_field' ),
			'message'         => 'sanitize_textarea_field',
			'order_id'        => array( $this, 'parse_int_field' ),
			'order_item_id'   => array( $this, 'parse_int_field' ),
			'template_id'     => array( $this, 'parse_template_field' ),
			'create_date'     => array( $this, 'parse_timestamp_field' ),
			'deliver_date'    => array( $this, 'parse_timestamp_field' ),
			'expire_date'     => array( $this, 'parse_timestamp_field' ),
			'redeem_date'     => array( $this, 'parse_timestamp_field' ),
			'redeemed_by'     => array( $this, 'parse_int_field' ),
			'delivered'       => array( $this, 'parse_delivered_field' ),
			'is_virtual'      => array( $this, 'parse_bool_field' ),
			'is_active'       => array( $this, 'parse_bool_field' ),
			'activities:json' => array( $this, 'parse_activities_field' ),
			// Hint: 'sender' get `wc_clean` by default.
		);

		/**
		 * Match special column names.
		 */
		$regex_match_data_formatting = array(
			'/meta:*/' => 'wp_kses_post', // Allow some HTML in meta fields.
		);

		$callbacks = array();

		// Figure out the parse function for each column.
		foreach ( $this->get_mapped_keys() as $index => $heading ) {
			$callback = 'wc_clean';

			if ( isset( $data_formatting[ $heading ] ) ) {
				$callback = $data_formatting[ $heading ];
			} else {
				foreach ( $regex_match_data_formatting as $regex => $callback ) {
					if ( preg_match( $regex, $heading ) ) {
						$callback = $callback;
						break;
					}
				}
			}

			$callbacks[] = $callback;
		}

		return apply_filters( 'woocommerce_gc_giftcards_importer_formatting_callbacks', $callbacks, $this );
	}

	/**
	 * Check if strings starts with determined word.
	 *
	 * @param string $haystack Complete sentence.
	 * @param string $needle   Excerpt.
	 *
	 * @return bool
	 */
	protected function starts_with( $haystack, $needle ) {
		return substr( $haystack, 0, strlen( $needle ) ) === $needle;
	}

	/**
	 * Expand special and internal data into the correct formats for the product CRUD.
	 *
	 * @param array $data Data to import.
	 *
	 * @return array
	 */
	protected function expand_data( $data ) {
		$data = apply_filters( 'woocommerce_gc_giftcards_importer_pre_expand_data', $data );

		// Handle special column names which span multiple columns.
		$activities = array();
		$meta_data  = array();

		foreach ( $data as $key => $value ) {

			if ( $this->starts_with( $key, 'activities:json' ) ) {
				if ( '' !== $value ) {
					$activities[] = $value;
				}
				unset( $data[ $key ] );

			} elseif ( $this->starts_with( $key, 'meta:' ) ) {
				$meta_data[ str_replace( 'meta:', '', $key ) ] = $value;
				unset( $data[ $key ] );
			}
		}

		if ( ! empty( $activities ) ) {
			$data['activities'] = $activities;
		}

		if ( ! empty( $meta_data ) ) {
			$data['meta_data'] = $meta_data;
		}

		return $data;
	}

	/**
	 * Map and format raw data to known fields.
	 */
	protected function set_parsed_data() {
		$parse_functions = $this->get_formatting_callback();
		$mapped_keys     = $this->get_mapped_keys();
		$use_mb          = function_exists( 'mb_convert_encoding' );

		// Parse the data.
		foreach ( $this->raw_data as $row_index => $row ) {
			// Skip empty rows.
			if ( ! count( array_filter( $row ) ) ) {
				continue;
			}

			$this->parsing_raw_data_index = $row_index;

			$data = array();

			do_action( 'woocommerce_gc_giftcards_importer_before_set_parsed_data', $row, $mapped_keys );

			foreach ( $row as $id => $value ) {
				// Skip ignored columns.
				if ( empty( $mapped_keys[ $id ] ) ) {
					continue;
				}

				// Convert UTF8.
				if ( $use_mb ) {
					$encoding = mb_detect_encoding( $value, mb_detect_order(), true );
					if ( $encoding ) {
						$value = mb_convert_encoding( $value, 'UTF-8', $encoding );
					} else {
						$value = mb_convert_encoding( $value, 'UTF-8', 'UTF-8' );
					}
				} else {
					$value = wp_check_invalid_utf8( $value, true );
				}

				$data[ $mapped_keys[ $id ] ] = call_user_func( $parse_functions[ $id ], $value );
			}

			/**
			 * Filter product importer parsed data.
			 *
			 * @param array               $parsed_data Parsed data.
			 * @param WC_Product_Importer $importer Importer instance.
			 */
			$this->parsed_data[] = apply_filters( 'woocommerce_gc_giftcards_importer_parsed_data', $this->expand_data( $data ), $this );
		}
	}

	/**
	 * Get a string to identify the row from parsed data.
	 *
	 * @param array $parsed_data Parsed data.
	 *
	 * @return string
	 */
	protected function get_row_id( $parsed_data ) {
		$id       = isset( $parsed_data['id'] ) ? absint( $parsed_data['id'] ) : 0;
		$code     = isset( $parsed_data['code'] ) ? esc_attr( $parsed_data['code'] ) : '';
		$row_data = array();

		if ( $id ) {
			/* translators: %d: giftcard ID */
			$row_data[] = sprintf( _x( 'ID %d', 'csv-import', 'woocommerce-gift-cards' ), $id );
		}
		if ( $code ) {
			/* translators: %s: giftcard code */
			$row_data[] = sprintf( _x( 'Code %s', 'csv-import', 'woocommerce-gift-cards' ), $code );
		}

		return implode( ', ', $row_data );
	}

	/**
	 * Process importer.
	 *
	 * Do not import gift cards with IDs or Codes that already exist if option
	 * update existing is false, and likewise, if updating gift cards, do not
	 * process rows which do not exist if an ID/Code is provided.
	 *
	 * @return array
	 */
	public function import() {
		$this->start_time = time();
		$index            = 0;
		$update_existing  = false; // $this->params[ 'update_existing' ];
		$data             = array(
			'imported' => array(),
			'failed'   => array(),
			'updated'  => array(),
			'skipped'  => array(),
		);

		foreach ( $this->parsed_data as $parsed_data_key => $parsed_data ) {
			do_action( 'woocommerce_gc_giftcards_import_before_import', $parsed_data );

			$id          = isset( $parsed_data['id'] ) ? absint( $parsed_data['id'] ) : 0;
			$code        = isset( $parsed_data['code'] ) ? $parsed_data['code'] : '';
			$activities  = isset( $parsed_data['activities'] ) ? $parsed_data['activities'] : '';
			$id_exists   = false;
			$code_exists = false;

			if ( $id ) {
				$giftcard  = wc_gc_get_gift_card( $id );
				$id_exists = is_a( $giftcard, 'WC_GC_Gift_Card_Data' ) && $giftcard->get_id();
			}

			if ( $code ) {
				$giftcard_from_code = wc_gc_get_gift_card_by_code( $code );
				$code_exists        = is_a( $giftcard_from_code, 'WC_GC_Gift_Card_Data' ) && $giftcard_from_code->get_id();
			}

			if ( $id_exists && ! $update_existing ) {

				$data['skipped'][] = new WP_Error(
					'woocommerce_gc_giftcards_importer_error',
					esc_html__( 'A gift card with this ID already exists.', 'woocommerce-gift-cards' ),
					array(
						'id'  => $id,
						'row' => $this->get_row_id( $parsed_data ),
					)
				);
				continue;
			}

			if ( $code_exists && ! $update_existing ) {
				$data['skipped'][] = new WP_Error(
					'woocommerce_gc_giftcards_importer_error',
					esc_html__( 'A gift card with this code already exists.', 'woocommerce-gift-cards' ),
					array(
						'code' => esc_attr( $code ),
						'row'  => $this->get_row_id( $parsed_data ),
					)
				);
				continue;
			}

			if ( $update_existing && ( isset( $parsed_data['id'] ) || isset( $parsed_data['code'] ) ) && ! $id_exists && ! $code_exists ) {
				$data['skipped'][] = new WP_Error(
					'woocommerce_gc_giftcards_importer_error',
					esc_html__( 'No matching gift card exists to update.', 'woocommerce-gift-cards' ),
					array(
						'id'   => $id,
						'code' => esc_attr( $code ),
						'row'  => $this->get_row_id( $parsed_data ),
					)
				);
				continue;
			}

			if ( is_array( $activities ) && in_array( false, $activities, true ) ) {
				$data['skipped'][] = new WP_Error(
					'woocommerce_gc_giftcards_importer_error',
					esc_html__( 'The gift cards activity data should be in a JSON format.', 'woocommerce-gift-cards' ),
					array(
						'id'  => $id,
						'row' => $this->get_row_id( $parsed_data ),
					)
				);
				continue;
			}

			$result = $this->process_item( $parsed_data );

			if ( is_wp_error( $result ) ) {
				$result->add_data( array( 'row' => $this->get_row_id( $parsed_data ) ) );
				$data['failed'][] = $result;
			} elseif ( $result['updated'] ) {
				$data['updated'][] = $result['id'];
			} else {
				$data['imported'][] = $result['id'];
			}

			++$index;

			if ( $this->params['prevent_timeouts'] && ( $this->time_exceeded() || $this->memory_exceeded() ) ) {
				$this->file_position = $this->file_positions[ $index ];
				break;
			}
		}

		return $data;
	}

	/*
	------------------------------------------ */
	/*
	Parsers.
	/* ------------------------------------------ */

	/**
	 * Parse the ID field.
	 *
	 * If we're not doing an update, create a placeholder product so mapping works
	 * for rows following this one.
	 *
	 * @param string $value Field value.
	 *
	 * @return int
	 */
	public function parse_id_field( $value ) {
		global $wpdb;

		$id = absint( $value );
		if ( ! $id ) {
			return 0;
		}

		// Not updating?
		if ( ! $this->params['update_existing'] ) {
			// No need for placeholders right now.
		}

		return $id && ! is_wp_error( $id ) ? $id : 0;
	}

	/**
	 * Parse a comma-delineated field from a CSV.
	 *
	 * @param string $value Field value.
	 *
	 * @return array
	 */
	public function parse_comma_field( $value ) {
		if ( empty( $value ) && '0' !== $value ) {
			return array();
		}

		$value = $this->unescape_data( $value );
		return array_map( 'wc_clean', $this->explode_values( $value ) );
	}

	/**
	 * Parse a field that is generally '1' or '0' but can be something else.
	 *
	 * @param string $value Field value.
	 *
	 * @return bool|string
	 */
	public function parse_bool_field( $value ) {

		if ( in_array( $value, array( '0', 'no', 'FALSE', 'false', 'False' ), true ) ) {
			return 'no';
		}

		if ( in_array( $value, array( '1', 'yes', 'TRUE', 'true', 'True' ), true ) ) {
			return 'yes';
		}

		// Don't return explicit true or false for empty fields or values like 'notify'.
		return wc_clean( $value );
	}

	/**
	 * Parse a float value field.
	 *
	 * @param string $value Field value.
	 *
	 * @return float|string
	 */
	public function parse_float_field( $value ) {
		if ( '' === $value ) {
			return $value;
		}

		// Remove the ' prepended to fields that start with - if needed.
		$value = $this->unescape_data( $value );

		return floatval( $value );
	}

	/**
	 * Parse dates from a CSV to timestamps.
	 * Dates requires the format YYYY-MM-DD and time is optional.
	 *
	 * @param  string $value
	 * @return int
	 */
	public function parse_timestamp_field( $value ) {
		if ( empty( $value ) ) {
			return 0;
		}

		if ( preg_match( '/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])([ 01-9:]*)$/', $value ) ) {
			return strtotime( $value );
		}

		return 0;
	}

	/**
	 * Parse emails from a CSV.
	 *
	 * @param  string $value
	 * @return string|null
	 */
	public function parse_email_field( $value ) {
		if ( empty( $value ) ) {
			return null;
		}

		$value = wc_clean( $value );

		if ( wc_gc_is_email( $value ) ) {
			return $value;
		}

		return null;
	}

	/**
	 * Parse gift card activities from a CSV.
	 *
	 * @param  string $value
	 * @return string|false
	 */
	public function parse_activities_field( $value ) {
		if ( empty( $value ) ) {
			return '';
		}

		$data = json_decode( $value, JSON_OBJECT_AS_ARRAY );

		if ( is_null( $data ) ) {
			return false;
		}

		$data = array_pop( $data );

		if ( ! empty( $data ) ) {
			return $data;
		}

		return '';
	}

	/**
	 * Parse gift card code from a CSV.
	 *
	 * @param  string $value
	 * @return string|null
	 */
	public function parse_code_field( $value ) {
		if ( empty( $value ) ) {
			return null;
		}

		return wc_clean( $value );
	}

	/**
	 * Parse and validate template id from a CSV.
	 *
	 * @param string $value Field value.
	 * @return string
	 */
	public function parse_template_field( $value ) {
		if ( empty( $value ) ) {
			return 'default';
		}

		$template_id = wc_clean( $value );
		if ( WC_GC()->emails->get_template( $template_id ) ) {
			return $template_id;
		}

		return 'default';
	}

	/**
	 * Parse delivered stamp from a CSV.
	 * Delivered stamp might have a `no` value for non-delivered GCs and an integer indicating the user id or `0` for system.
	 *
	 * @param string $value Field value.
	 * @return string
	 */
	public function parse_delivered_field( $value ) {
		if ( 'no' === $value ) {
			return 'no';
		}

		$value = $this->parse_bool_field( $value );
		return absint( $value );
	}

	/**
	 * Just skip current field.
	 *
	 * By default is applied wc_clean() to all not listed fields
	 * in self::get_formatting_callback(), use this method to skip any formatting.
	 *
	 * @param string $value Field value.
	 *
	 * @return string
	 */
	public function parse_skip_field( $value ) {
		return $value;
	}

	/**
	 * Parse an int value field
	 *
	 * @param int $value field value.
	 *
	 * @return int
	 */
	public function parse_int_field( $value ) {
		// Remove the ' prepended to fields that start with - if needed.
		$value = $this->unescape_data( $value );

		return intval( $value );
	}

	/**
	 * Parse a decimal field using wc_format_decimal.
	 *
	 * @since 2.7.3
	 *
	 * @param  string $value Field value.
	 * @return string
	 */
	public function parse_decimal_field( $value ) {
		if ( '' === $value ) {
			return $value;
		}

		// Remove the ' prepended to fields that start with - if needed.
		$value = $this->unescape_data( $value );

		return wc_format_decimal( (float) $value );
	}
}
