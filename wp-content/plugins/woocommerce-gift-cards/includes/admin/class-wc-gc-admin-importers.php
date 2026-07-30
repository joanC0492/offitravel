<?php
/**
 * WC_GC_Admin_Importers class
 *
 * @package  WooCommerce Gift Cards
 * @since    1.6.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_GC_Admin_Importers Class.
 *
 * @version 1.13.1
 */
class WC_GC_Admin_Importers {

	/**
	 * Array of importer IDs.
	 *
	 * @var string[]
	 */
	protected $importers = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		if ( ! $this->import_allowed() ) {
			return;
		}

		// Init.
		add_action( 'admin_init', array( $this, 'register_importers' ) );

		// Render page.
		add_action( 'woocommerce_gc_render_giftcard_importer', array( $this, 'giftcard_importer' ) );

		// Enqueue scripts needed.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_action( 'wp_ajax_woocommerce_gc_do_ajax_import', array( $this, 'do_ajax_gift_card_import' ) );

		// Register WooCommerce Gift Cards importers.
		$this->importers['giftcard_importer'] = array(
			'menu'       => 'woocommerce',
			'name'       => __( 'Gift Card Import', 'woocommerce-gift-cards' ),
			'capability' => 'import',
			'callback'   => array( $this, 'giftcard_importer' ),
		);
	}

	/**
	 * Return true if WooCommerce Gift Cards imports are allowed for current user, false otherwise.
	 *
	 * @return bool Whether current user can perform imports.
	 */
	protected function import_allowed() {
		return current_user_can( 'manage_woocommerce' ) && current_user_can( 'import' );
	}

	/**
	 * Register importer scripts.
	 */
	public function admin_scripts() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_register_script( 'wc-gc-import', WC_GC()->get_plugin_url() . '/assets/js/admin/wc-gc-import' . $suffix . '.js', array( 'jquery' ), WC_GC()->get_plugin_version(), true );
	}

	/**
	 * The product importer.
	 *
	 * This has a custom screen - the Tools > Import item is a placeholder.
	 * If we're on that screen, redirect to the custom one.
	 */
	public function giftcard_importer() {
		if ( defined( 'WP_LOAD_IMPORTERS' ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=gc_giftcards&section=giftcard_importer' ) );
			exit;
		}

		include_once WC_GC_ABSPATH . 'includes/admin/import/class-wc-gc-csv-importer.php';
		include_once WC_ABSPATH . 'includes/admin/importers/class-wc-product-csv-importer-controller.php';

		$importer = new WC_GC_CSV_Importer_Controller();
		$importer->dispatch();
	}

	/**
	 * Register WordPress based importers.
	 */
	public function register_importers() {
		if ( defined( 'WP_LOAD_IMPORTERS' ) ) {
			register_importer( 'woocommerce_gc_giftcards_csv', __( 'WooCommerce Gift Cards (CSV)', 'woocommerce-gift-cards' ), __( 'Import <strong>gift cards</strong> to your store via a csv file.', 'woocommerce-gift-cards' ), array( $this, 'giftcard_importer' ) );
		}
	}

	/**
	 * Ajax callback for importing one batch of products from a CSV.
	 */
	public function do_ajax_gift_card_import() {
		global $wpdb;

		check_ajax_referer( 'wc-gc-import', 'security' );

		if ( ! $this->import_allowed() || ! isset( $_POST['file'] ) ) { // PHPCS: input var ok.
			wp_send_json_error( array( 'message' => __( 'Insufficient privileges to import products.', 'woocommerce-gift-cards' ) ) );
		}

		include_once WC_GC_ABSPATH . 'includes/admin/import/class-wc-gc-csv-importer-controller.php';
		include_once WC_GC_ABSPATH . 'includes/admin/import/class-wc-gc-csv-importer.php';

		$file   = wc_clean( wp_unslash( $_POST['file'] ) ); // PHPCS: input var ok.
		$params = array(
			'delimiter'       => ! empty( $_POST['delimiter'] ) ? wc_clean( wp_unslash( $_POST['delimiter'] ) ) : ',', // PHPCS: input var ok.
			'start_pos'       => isset( $_POST['position'] ) ? absint( $_POST['position'] ) : 0, // PHPCS: input var ok.
			'mapping'         => isset( $_POST['mapping'] ) ? (array) wc_clean( wp_unslash( $_POST['mapping'] ) ) : array(), // PHPCS: input var ok.
			'update_existing' => isset( $_POST['update_existing'] ) ? (bool) $_POST['update_existing'] : false, // PHPCS: input var ok.
			'lines'           => apply_filters( 'woocommerce_product_import_batch_size', 30 ),
			'parse'           => true,
		);

		// Log failures.
		if ( 0 !== $params['start_pos'] ) {
			$error_log = array_filter( (array) get_user_option( 'giftcard_import_error_log' ) );
		} else {
			$error_log = array();
		}

		$importer         = WC_GC_CSV_Importer_Controller::get_importer( $file, $params );
		$results          = $importer->import();
		$percent_complete = $importer->get_percent_complete();
		$error_log        = array_merge( $error_log, $results['failed'], $results['skipped'] );

		update_user_option( get_current_user_id(), 'giftcard_import_error_log', $error_log );

		if ( 100 === $percent_complete ) {

			// Delete uploaded file.
			wp_delete_file( $file );

			// Send success.
			wp_send_json_success(
				array(
					'position'   => 'done',
					'percentage' => 100,
					'url'        => add_query_arg( array( '_wpnonce' => wp_create_nonce( 'woocommerce-gc-csv-importer' ) ), admin_url( 'admin.php?page=gc_giftcards&section=giftcard_importer&step=done' ) ),
					'imported'   => count( $results['imported'] ),
					'failed'     => count( $results['failed'] ),
					'updated'    => count( $results['updated'] ),
					'skipped'    => count( $results['skipped'] ),
				)
			);
		} else {
			wp_send_json_success(
				array(
					'position'   => $importer->get_file_position(),
					'percentage' => $percent_complete,
					'imported'   => count( $results['imported'] ),
					'failed'     => count( $results['failed'] ),
					'updated'    => count( $results['updated'] ),
					'skipped'    => count( $results['skipped'] ),
				)
			);
		}
	}
}

new WC_GC_Admin_Importers();
