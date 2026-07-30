<?php
/**
 * WC_GC_CSV_Importer_Controller class
 *
 * @package  WooCommerce Gift Cards
 * @since    1.6.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'WP_Importer' ) ) {
	return;
}

/**
 * Gift Card controller - handles file upload and forms in admin.
 *
 * @version 2.5.0
 */
class WC_GC_CSV_Importer_Controller {

	/**
	 * Page home URL.
	 *
	 * @const PAGE_URL
	 */
	const PAGE_URL = 'admin.php?page=gc_giftcards';

	/**
	 * The path to the current file.
	 *
	 * @var string
	 */
	protected $file = '';

	/**
	 * The current import step.
	 *
	 * @var string
	 */
	protected $step = '';

	/**
	 * Progress steps.
	 *
	 * @var array
	 */
	protected $steps = array();

	/**
	 * Errors.
	 *
	 * @var array
	 */
	protected $errors = array();

	/**
	 * The current delimiter for the file being read.
	 *
	 * @var string
	 */
	protected $delimiter = ',';

	/**
	 * Whether to use previous mapping selections.
	 *
	 * @var bool
	 */
	protected $map_preferences = false;

	/**
	 * Whether to skip existing products.
	 *
	 * @var bool
	 */
	protected $update_existing = false;

	/**
	 * Get importer instance.
	 *
	 * @param  string $file File to import.
	 * @param  array  $args Importer arguments.
	 * @return WC_Product_CSV_Importer
	 */
	public static function get_importer( $file, $args = array() ) {
		$importer_class = apply_filters( 'woocommerce_gc_giftcards_csv_importer_class', 'WC_GC_CSV_Importer' );
		$args           = apply_filters( 'woocommerce_gc_giftcards_csv_importer_args', $args, $importer_class );
		return new $importer_class( $file, $args );
	}

	/**
	 * Check whether a file is a valid CSV file.
	 *
	 * @param string $file File path.
	 * @param bool   $check_path Whether to also check the file is located in a valid location (Default: true).
	 * @return bool
	 */
	public static function is_file_valid_csv( $file, $check_path = true ) {
		if ( $check_path && apply_filters( 'woocommerce_gc_giftcards_csv_importer_check_import_file_path', true ) && false !== stripos( $file, '://' ) ) {
			return false;
		}

		$valid_filetypes = self::get_valid_csv_filetypes();
		$filetype        = wp_check_filetype( $file, $valid_filetypes );
		if ( in_array( $filetype['type'], $valid_filetypes, true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Checks if the provided filepath is within the PHAR executable.
	 *
	 * @since 1.13.1
	 *
	 * @param  string $filepath  The filepath to be checked.
	 * @return bool True if the filepath is within ABSPATH.
	 */
	public static function is_within_phar( $filepath ) {

		if ( 'phar' === strtolower( (string) wp_parse_url( $filepath, PHP_URL_SCHEME ) ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Checks if the provided filepath is within the WordPress absolute path.
	 *
	 * @since 1.13.1
	 *
	 * @param  string $filepath  The filepath to be checked.
	 * @return bool True if the filepath is within ABSPATH.
	 */
	public static function is_within_abspath( $filepath ) {
		return 0 === stripos( realpath( $filepath ), trailingslashit( realpath( ABSPATH ) ) );
	}

	/**
	 * Checks if the provided filepath is within wp-content directory.
	 *
	 * @since 1.15.3
	 *
	 * @param  string $filepath  The filepath to be checked.
	 * @return bool True if the filepath is within WP_CONTENT_DIR.
	 */
	public static function is_within_wp_content( $filepath ) {
		return 0 === stripos( realpath( $filepath ), trailingslashit( realpath( WP_CONTENT_DIR ) ) );
	}

	/**
	 * Get all the valid filetypes for a CSV file.
	 *
	 * @return array
	 */
	protected static function get_valid_csv_filetypes() {
		return apply_filters(
			'woocommerce_gc_giftcards_import_valid_filetypes',
			array(
				'csv' => 'text/csv',
				'txt' => 'text/plain',
			)
		);
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		$default_steps = array(
			'upload'  => array(
				'name'    => __( 'Upload CSV file', 'woocommerce' ),
				'view'    => array( $this, 'upload_form' ),
				'handler' => array( $this, 'upload_form_handler' ),
			),
			'mapping' => array(
				'name'    => __( 'Column mapping', 'woocommerce' ),
				'view'    => array( $this, 'mapping_form' ),
				'handler' => '',
			),
			'import'  => array(
				'name'    => __( 'Import', 'woocommerce' ),
				'view'    => array( $this, 'import' ),
				'handler' => '',
			),
			'done'    => array(
				'name'    => __( 'Done!', 'woocommerce' ),
				'view'    => array( $this, 'done' ),
				'handler' => '',
			),
		);

		$this->steps = apply_filters( 'woocommerce_gc_giftcards_csv_importer_steps', $default_steps );

		$this->step            = isset( $_REQUEST['step'] ) ? sanitize_key( $_REQUEST['step'] ) : current( array_keys( $this->steps ) );
		$this->file            = isset( $_REQUEST['file'] ) ? wc_clean( wp_unslash( $_REQUEST['file'] ) ) : '';
		$this->update_existing = isset( $_REQUEST['update_existing'] ) ? (bool) $_REQUEST['update_existing'] : false;
		$this->delimiter       = ! empty( $_REQUEST['delimiter'] ) ? wc_clean( wp_unslash( $_REQUEST['delimiter'] ) ) : ',';
		$this->map_preferences = isset( $_REQUEST['map_preferences'] ) ? (bool) $_REQUEST['map_preferences'] : false;

		// Import mappings for CSV data.
		include_once __DIR__ . '/mappings/mappings.php';

		if ( $this->map_preferences ) {
			add_filter( 'woocommerce_gc_giftcards_csv_import_mapped_columns', array( $this, 'auto_map_user_preferences' ), 9999 );
		}
	}

	/**
	 * Get the URL for the next step's screen.
	 *
	 * @param string $step  slug (default: current step).
	 * @return string       URL for next step if a next step exists.
	 *                      Admin URL if it's the last step.
	 *                      Empty string on failure.
	 */
	public function get_next_step_link( $step = '' ) {
		if ( ! $step ) {
			$step = $this->step;
		}

		$keys = array_keys( $this->steps );

		if ( end( $keys ) === $step ) {
			return admin_url();
		}

		$step_index = array_search( $step, $keys, true );

		if ( false === $step_index ) {
			return '';
		}

		$params = array(
			'step'            => $keys[ $step_index + 1 ],
			'file'            => str_replace( DIRECTORY_SEPARATOR, '/', $this->file ),
			'delimiter'       => $this->delimiter,
			'update_existing' => $this->update_existing,
			'map_preferences' => $this->map_preferences,
			'_wpnonce'        => wp_create_nonce( 'woocommerce-gc-csv-importer' ), // wp_nonce_url() escapes & to &amp; breaking redirects.
		);

		return add_query_arg( $params ); // nosemgrep: audit.php.wp.security.xss.query-arg
	}

	/**
	 * Output header view.
	 */
	protected function output_header() {
		include __DIR__ . '/views/html-admin-csv-import-header.php';
	}

	/**
	 * Output steps view.
	 */
	protected function output_steps() {
		include __DIR__ . '/views/html-admin-csv-import-steps.php';
	}

	/**
	 * Output footer view.
	 */
	protected function output_footer() {
		include __DIR__ . '/views/html-admin-csv-import-footer.php';
	}

	/**
	 * Add error message.
	 *
	 * @param string $message Error message.
	 * @param array  $actions List of actions with 'url' and 'label'.
	 */
	protected function add_error( $message, $actions = array() ) {
		$this->errors[] = array(
			'message' => $message,
			'actions' => $actions,
		);
	}

	/**
	 * Add error message.
	 */
	protected function output_errors() {
		if ( ! $this->errors ) {
			return;
		}

		foreach ( $this->errors as $error ) {
			echo '<div class="error inline">';
			echo '<p>' . esc_html( $error['message'] ) . '</p>';

			if ( ! empty( $error['actions'] ) ) {
				echo '<p>';
				foreach ( $error['actions'] as $action ) {
					echo '<a class="button button-primary" href="' . esc_url( $action['url'] ) . '">' . esc_html( $action['label'] ) . '</a> ';
				}
				echo '</p>';
			}
			echo '</div>';
		}
	}

	/**
	 * Dispatch current step and show correct view.
	 */
	public function dispatch() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! empty( $_POST['save_step'] ) && ! empty( $this->steps[ $this->step ]['handler'] ) ) {
			call_user_func( $this->steps[ $this->step ]['handler'], $this );
		}
		$this->output_header();
		$this->output_steps();
		$this->output_errors();
		call_user_func( $this->steps[ $this->step ]['view'], $this );
		$this->output_footer();
	}

	/**
	 * Output information about the uploading process.
	 */
	protected function upload_form() {
		$bytes      = apply_filters( 'import_upload_size_limit', wp_max_upload_size() );
		$size       = size_format( $bytes );
		$upload_dir = wp_upload_dir();

		include __DIR__ . '/views/html-admin-csv-import-form.php';
	}

	/**
	 * Handle the upload form and store options.
	 */
	public function upload_form_handler() {
		check_admin_referer( 'woocommerce-gc-csv-importer' );

		$file = $this->handle_upload();

		if ( is_wp_error( $file ) ) {
			$this->add_error( $file->get_error_message() );
			return;
		} else {
			$this->file = $file;
		}

		wp_safe_redirect( esc_url_raw( $this->get_next_step_link() ) );
		exit;
	}

	/**
	 * Handles the CSV upload and initial parsing of the file to prepare for
	 * displaying author import options.
	 *
	 * @return string|WP_Error
	 */
	public function handle_upload() {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce already verified in WC_GC_CSV_Importer_Controller::upload_form_handler()
		$file_url = isset( $_POST['file_url'] ) ? wc_clean( wp_unslash( $_POST['file_url'] ) ) : '';

		if ( empty( $file_url ) ) {
			if ( ! isset( $_FILES['import'] ) ) {
				return new WP_Error( 'woocommerce_gc_giftcards_csv_importer_upload_file_empty', __( 'File is empty. Please upload something more substantial. This error could also be caused by uploads being disabled in your php.ini or by post_max_size being defined as smaller than upload_max_filesize in php.ini.', 'woocommerce' ) );
			}

			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
			if ( ! self::is_file_valid_csv( wc_clean( wp_unslash( $_FILES['import']['name'] ) ), false ) ) {
				return new WP_Error( 'woocommerce_gc_giftcards_csv_importer_upload_file_invalid', __( 'Invalid file type. The importer supports CSV and TXT file formats.', 'woocommerce' ) );
			}

			$overrides = array(
				'test_form' => false,
				'mimes'     => self::get_valid_csv_filetypes(),
			);
			$import    = $_FILES['import']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			$upload    = wp_handle_upload( $import, $overrides );

			if ( isset( $upload['error'] ) ) {
				return new WP_Error( 'woocommerce_gc_giftcards_csv_importer_upload_error', $upload['error'] );
			}

			// Construct the object array.
			$object = array(
				'post_title'     => basename( $upload['file'] ),
				'post_content'   => $upload['url'],
				'post_mime_type' => $upload['type'],
				'guid'           => $upload['url'],
				'context'        => 'import',
				'post_status'    => 'private',
			);

			// Save the data.
			$id = wp_insert_attachment( $object, $upload['file'] );

			/*
			 * Schedule a cleanup for two hours from now in case of failed
			 * import or missing wp_import_cleanup() call.
			 */
			wp_schedule_single_event( time() + ( 2 * HOUR_IN_SECONDS ), 'importer_scheduled_cleanup', array( $id ) );

			return $upload['file'];

		} elseif (
			self::is_within_abspath( ABSPATH . $file_url )
			&& file_exists( ABSPATH . $file_url )
		) {
			if ( ! self::is_file_valid_csv( ABSPATH . $file_url ) ) {
				return new WP_Error( 'woocommerce_gc_giftcards_csv_importer_upload_file_invalid', __( 'Invalid file type. The importer supports CSV and TXT file formats.', 'woocommerce' ) );
			}

			return ABSPATH . $file_url;
		}

		return new WP_Error( 'woocommerce_gc_giftcards_csv_importer_upload_invalid_file', __( 'Please upload or provide the link to a valid CSV file.', 'woocommerce' ) );
	}

	/**
	 * Mapping step.
	 */
	protected function mapping_form() {
		check_admin_referer( 'woocommerce-gc-csv-importer' );
		$args = array(
			'lines'     => 1,
			'delimiter' => $this->delimiter,
		);

		$importer     = self::get_importer( $this->file, $args );
		$headers      = $importer->get_raw_keys();
		$mapped_items = $this->auto_map_columns( $headers );
		$sample       = current( $importer->get_raw_data() );

		if ( empty( $sample ) ) {
			$this->add_error(
				__( 'The file is empty or using a different encoding than UTF-8, please try again with a new file.', 'woocommerce' ),
				array(
					array(
						'url'   => admin_url( 'admin.php?page=gc_giftcards&section=giftcard_importer' ),
						'label' => __( 'Upload a new file', 'woocommerce' ),
					),
				)
			);

			// Force output the errors in the same page.
			$this->output_errors();
			return;
		}

		include_once __DIR__ . '/views/html-admin-csv-import-mapping.php';
	}

	/**
	 * Import the file if it exists and is valid.
	 */
	public function import() {
		// Displaying this page triggers Ajax action to run the import with a valid nonce,
		// therefore this page needs to be nonce protected as well.
		check_admin_referer( 'woocommerce-gc-csv-importer' );

		if ( self::is_within_phar( $this->file ) ) {
			$this->add_error( __( 'Invalid file path. Files must must not be within a PHAR executable.', 'woocommerce-gift-cards' ) );
			$this->output_errors();
			return;
		}

		if ( ! self::is_within_abspath( $this->file ) && ! self::is_within_wp_content( $this->file ) ) {
			$this->add_error( __( 'Invalid file path. Files must be uploaded to a location inside the WordPress absolute path or wp-content.', 'woocommerce-gift-cards' ) );
			$this->output_errors();
			return;
		}

		if ( ! self::is_file_valid_csv( $this->file ) ) {
			$this->add_error( __( 'Invalid file type. The importer supports CSV and TXT file formats.', 'woocommerce' ) );
			$this->output_errors();
			return;
		}

		if ( ! is_file( $this->file ) ) {
			$this->add_error( __( 'The file does not exist, please try again.', 'woocommerce' ) );
			$this->output_errors();
			return;
		}

		if ( ! empty( $_POST['map_from'] ) && ! empty( $_POST['map_to'] ) ) {
			$mapping_from = wc_clean( wp_unslash( $_POST['map_from'] ) );
			$mapping_to   = wc_clean( wp_unslash( $_POST['map_to'] ) );

			// Save mapping preferences for future imports.
			update_user_option( get_current_user_id(), 'woocommerce_gc_giftcards_import_mapping', $mapping_to );
		} else {
			wp_safe_redirect( esc_url_raw( $this->get_next_step_link( 'upload' ) ) );
			exit;
		}

		wp_localize_script(
			'wc-gc-import',
			'wc_gc_import_params',
			array(
				'import_nonce'    => wp_create_nonce( 'wc-gc-import' ),
				'mapping'         => array(
					'from' => $mapping_from,
					'to'   => $mapping_to,
				),
				'file'            => $this->file,
				'update_existing' => $this->update_existing,
				'delimiter'       => $this->delimiter,
			)
		);
		wp_enqueue_script( 'wc-gc-import' );

		include_once __DIR__ . '/views/html-admin-csv-import-progress.php';
	}

	/**
	 * Done step.
	 */
	protected function done() {
		check_admin_referer( 'woocommerce-gc-csv-importer' );
		$imported  = isset( $_GET['giftcards-imported'] ) ? absint( $_GET['giftcards-imported'] ) : 0;
		$updated   = isset( $_GET['giftcards-updated'] ) ? absint( $_GET['giftcards-updated'] ) : 0;
		$failed    = isset( $_GET['giftcards-failed'] ) ? absint( $_GET['giftcards-failed'] ) : 0;
		$skipped   = isset( $_GET['giftcards-skipped'] ) ? absint( $_GET['giftcards-skipped'] ) : 0;
		$file_name = isset( $_GET['file-name'] ) ? sanitize_text_field( wp_unslash( $_GET['file-name'] ) ) : '';
		$errors    = array_filter( (array) get_user_option( 'giftcard_import_error_log' ) );

		include_once __DIR__ . '/views/html-admin-csv-import-done.php';
	}

	/**
	 * Columns to normalize.
	 *
	 * @param  array $columns List of columns names and keys.
	 * @return array
	 */
	protected function normalize_columns_names( $columns ) {
		$normalized = array();

		foreach ( $columns as $key => $value ) {
			$normalized[ strtolower( $key ) ] = $value;
		}

		return $normalized;
	}

	/**
	 * Auto map column names.
	 *
	 * @param  array $raw_headers Raw header columns.
	 * @param  bool  $num_indexes If should use numbers or raw header columns as indexes.
	 * @return array
	 */
	protected function auto_map_columns( $raw_headers, $num_indexes = true ) {

		/*
		 * @hooked wc_gc_importer_default_english_mappings - 100
		 * @hooked wc_gc_importer_shopify_mappings - 100
		 */
		$default_columns = $this->normalize_columns_names(
			apply_filters(
				'woocommerce_gc_giftcards_csv_import_mapping_default_columns',
				array(
					__( 'ID', 'woocommerce-gift-cards' )   => 'id',
					__( 'Code', 'woocommerce-gift-cards' ) => 'code',
					__( 'Recipient', 'woocommerce-gift-cards' ) => 'recipient',
					__( 'Sender', 'woocommerce-gift-cards' ) => 'sender',
					__( 'Sender E-mail', 'woocommerce-gift-cards' ) => 'sender_email',
					__( 'Message', 'woocommerce-gift-cards' ) => 'message',
					__( 'Issued value', 'woocommerce-gift-cards' ) => 'balance',
					__( 'Balance', 'woocommerce-gift-cards' ) => 'remaining',
					__( 'Order ID', 'woocommerce-gift-cards' ) => 'order_id',
					__( 'Order item ID', 'woocommerce-gift-cards' ) => 'order_item_id',
					__( 'Template ID', 'woocommerce-gift-cards' ) => 'template_id',
					__( 'Create date', 'woocommerce-gift-cards' ) => 'create_date',
					__( 'Delivery date', 'woocommerce-gift-cards' ) => 'deliver_date',
					__( 'Expiration date', 'woocommerce-gift-cards' ) => 'expire_date',
					__( 'Redeemed date', 'woocommerce-gift-cards' ) => 'redeem_date',
					__( 'Redeemed by user', 'woocommerce-gift-cards' ) => 'redeemed_by',
					__( 'Delivered', 'woocommerce-gift-cards' ) => 'delivered',
					__( 'Virtual', 'woocommerce-gift-cards' ) => 'is_virtual',
					__( 'Status', 'woocommerce-gift-cards' ) => 'is_active',
					__( 'Currency', 'woocommerce-gift-cards' ) => 'currency',
					__( 'Activities', 'woocommerce-gift-cards' ) => 'activities:json',
				),
				$raw_headers
			)
		);

		/*
		 * @hooked wc_gc_importer_default_special_english_mappings - 100
		 */
		$special_columns = $this->get_special_columns(
			$this->normalize_columns_names(
				apply_filters(
					'woocommerce_gc_giftcards_csv_import_mapping_special_columns',
					array(
						__( 'Meta: %s', 'woocommerce-gift-cards' )   => 'meta:',
					),
					$raw_headers
				)
			)
		);

		$headers = array();
		foreach ( $raw_headers as $key => $field ) {

			$normalized_field  = strtolower( $field );
			$index             = $num_indexes ? $key : $field;
			$headers[ $index ] = $normalized_field;

			if ( isset( $default_columns[ $normalized_field ] ) ) {
				$headers[ $index ] = $default_columns[ $normalized_field ];
			} else {
				foreach ( $special_columns as $regex => $special_key ) {
					// Don't use the normalized field in the regex since meta might be case-sensitive.
					if ( preg_match( $regex, $field, $matches ) ) {
						$headers[ $index ] = $special_key . $matches[1];
						break;
					}
				}
			}
		}

		return apply_filters( 'woocommerce_gc_giftcards_csv_import_mapped_columns', $headers, $raw_headers );
	}

	/**
	 * Map columns using the user's lastest import mappings.
	 *
	 * @param  array $headers Header columns.
	 * @return array
	 */
	public function auto_map_user_preferences( $headers ) {
		$mapping_preferences = get_user_option( 'woocommerce_gc_giftcards_import_mapping' );

		if ( ! empty( $mapping_preferences ) && is_array( $mapping_preferences ) ) {
			return $mapping_preferences;
		}

		return $headers;
	}

	/**
	 * Sanitize special column name regex.
	 *
	 * @param  string $value Raw special column name.
	 * @return string
	 */
	protected function sanitize_special_column_name_regex( $value ) {
		return '/' . str_replace( array( '%d', '%s' ), '(.*)', trim( quotemeta( $value ) ) ) . '/i';
	}

	/**
	 * Get special columns.
	 *
	 * @param  array $columns Raw special columns.
	 * @return array
	 */
	protected function get_special_columns( $columns ) {
		$formatted = array();

		foreach ( $columns as $key => $value ) {
			$regex = $this->sanitize_special_column_name_regex( $key );

			$formatted[ $regex ] = $value;
		}

		return $formatted;
	}

	/**
	 * Get mapping options.
	 *
	 * @param  string $item Item name.
	 * @return array
	 */
	protected function get_mapping_options( $item = '' ) {

		// Get index for special column names.
		$index = $item;

		if ( preg_match( '/\d+/', $item, $matches ) ) {
			$index = $matches[0];
		}

		// Properly format for meta field.
		$meta = str_replace( 'meta:', '', $item );
		// Available options.
		$options = array(
			'id'              => __( 'ID', 'woocommerce-gift-cards' ),
			'code'            => __( 'Code', 'woocommerce-gift-cards' ),
			'recipient'       => __( 'Recipient', 'woocommerce-gift-cards' ),
			'sender'          => __( 'Sender', 'woocommerce-gift-cards' ),
			'sender_email'    => __( 'Sender E-mail', 'woocommerce-gift-cards' ),
			'message'         => __( 'Message', 'woocommerce-gift-cards' ),
			'balance'         => __( 'Issued value', 'woocommerce-gift-cards' ),
			'remaining'       => __( 'Balance', 'woocommerce-gift-cards' ),
			'order_id'        => __( 'Order ID', 'woocommerce-gift-cards' ),
			'order_item_id'   => __( 'Order item ID', 'woocommerce-gift-cards' ),
			'template_id'     => __( 'Template ID', 'woocommerce-gift-cards' ),
			'create_date'     => __( 'Create date', 'woocommerce-gift-cards' ),
			'deliver_date'    => __( 'Delivery date', 'woocommerce-gift-cards' ),
			'expire_date'     => __( 'Expiration date', 'woocommerce-gift-cards' ),
			'redeem_date'     => __( 'Redeemed date', 'woocommerce-gift-cards' ),
			'redeemed_by'     => __( 'Redeemed by user', 'woocommerce-gift-cards' ),
			'delivered'       => __( 'Import as "Delivered" value', 'woocommerce-gift-cards' ),
			'is_virtual'      => __( 'Virtual', 'woocommerce-gift-cards' ),
			'is_active'       => __( 'Status', 'woocommerce-gift-cards' ),
			'currency'        => __( 'Currency', 'woocommerce-gift-cards' ),
			'activities:json' => __( 'Import as "Activities" JSON', 'woocommerce-gift-cards' ),
			'meta:' . $meta   => __( 'Import as meta data', 'woocommerce-gift-cards' ),
		);

		return apply_filters( 'woocommerce_gc_giftcards_csv_import_mapping_options', $options, $item );
	}
}
