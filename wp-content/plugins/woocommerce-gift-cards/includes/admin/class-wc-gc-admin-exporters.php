<?php
/**
 * WC_GC_Admin_Exporters class
 *
 * @package  WooCommerce Gift Cards
 * @since    1.6.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_GC_Admin_Exporters Class.
 *
 * @version 2.5.0
 */
class WC_GC_Admin_Exporters {

	/**
	 * Constructor.
	 */
	public function __construct() {

		add_action( 'admin_init', array( $this, 'download_export_file' ) );

		// Process step.
		add_action( 'wp_ajax_woocommerce_gc_do_ajax_giftcards_export', array( $this, 'do_ajax_export' ) );
		// Render export modal.
		add_action( 'wp_ajax_wc_gc_export_modal_html', array( $this, 'export_modal_html' ) );
		// Add JS template.
		add_action( 'admin_footer', array( __CLASS__, 'add_js_template' ) );
	}

	/**
	 * Return true if WooCommerce export is allowed for current user, false otherwise.
	 *
	 * @return bool Whether current user can perform export.
	 */
	protected function export_allowed() {
		return current_user_can( 'manage_woocommerce' ) && current_user_can( 'export' ) && ! wc_gc_mask_codes( 'admin' );
	}

	/**
	 * Serve the generated file.
	 */
	public function download_export_file() {

		// If accessed directly will echo "The link you followed has expired".
		if ( ! $this->export_allowed() ) {
			return;
		}

		if ( isset( $_GET['action'], $_GET['nonce'] ) && wp_verify_nonce( wc_clean( $_GET['nonce'] ), 'giftcard-csv' ) && 'download_giftcard_csv' === wc_clean( $_GET['action'] ) ) {
			include_once WC_GC_ABSPATH . 'includes/admin/export/class-wc-gc-csv-exporter.php';
			$exporter = new WC_GC_CSV_Exporter();

			if ( ! empty( $_GET['filename'] ) ) {
				$exporter->set_filename( wc_clean( $_GET['filename'] ) );
			}

			$exporter->export();
		}
	}

	/**
	 * AJAX callback for doing the actual export to the CSV file.
	 */
	public function do_ajax_export() {
		check_ajax_referer( 'wc-gc-giftcards-export', 'security' );

		if ( ! $this->export_allowed() ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient privileges to export gift cards.', 'woocommerce-gift-cards' ) ) );
		}

		include_once WC_GC_ABSPATH . 'includes/admin/export/class-wc-gc-csv-exporter.php';

		$step     = isset( $_POST['step'] ) ? absint( $_POST['step'] ) : 1;
		$exporter = new WC_GC_CSV_Exporter();
		$exporter->set_column_names( $exporter->get_default_column_names() );
		$exporter->set_filters( $_POST );

		if ( isset( $_POST['export_meta'] ) && '1' === $_POST['export_meta'] ) {
			$exporter->set_export_meta( true );
		}

		if ( ! empty( $_POST['filename'] ) ) {
			$exporter->set_filename( wc_clean( $_POST['filename'] ) );
		}

		$exporter->enable_activities_export( true );
		$exporter->set_page( $step );
		$exporter->generate_file();

		$query_args = apply_filters(
			'woocommerce_gc_export_get_ajax_query_args',
			array(
				'nonce'    => wp_create_nonce( 'giftcard-csv' ),
				'action'   => 'download_giftcard_csv',
				'filename' => $exporter->get_filename(),
			)
		);

		if ( 100 === $exporter->get_percent_complete() ) {
			wp_send_json_success(
				array(
					'step'       => 'done',
					'percentage' => 100,
					'url'        => add_query_arg( $query_args, admin_url( 'admin.php?page=gc_giftcards' ) ),
				)
			);
		} else {
			wp_send_json_success(
				array(
					'step'       => ++$step,
					'percentage' => $exporter->get_percent_complete(),
					'columns'    => $exporter->get_column_names(),
				)
			);
		}
	}

	/*
	|--------------------------------------------------------------------------
	| Modals.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Returns export modal's html.
	 *
	 * @return void
	 */
	public function export_modal_html() {

		$failure = array(
			'result' => 'failure',
		);

		if ( ! check_ajax_referer( 'wc-gc-modal-giftcards-export', 'security', false ) ) {
			wp_send_json( $failure );
		}

		if ( ! $this->export_allowed() ) {
			// Result is "success" on purpose, in order to render the message.
			$response = array(
				'result' => 'success',
				'html'   => __( 'Insufficient privileges to export gift cards. Please contact the site administrator.', 'woocommerce-gift-cards' ),
			);

			wp_send_json( $response );
		}

		ob_start();
		include WC_GC_ABSPATH . 'includes/admin/export/views/html-admin-modal-gift-cards-export.php';
		$html = ob_get_clean();

		$response = array(
			'result' => 'success',
			'html'   => $html,
		);

		wp_send_json( $response );
	}

	/**
	 * JS template of modal for exporting giftcards.
	 *
	 * @return void
	 */
	public static function add_js_template() {

		if ( wp_script_is( 'wc-gc-writepanel' ) ) {
			?>
			<script type="text/template" id="tmpl-wc-gc-export-giftcards">
				<div class="wc-backbone-modal">
					<div class="wc-backbone-modal-content wc-backbone-modal-content-export-giftcards">
						<section class="wc-backbone-modal-main" role="main">
							<header class="wc-backbone-modal-header">
								<h1>{{{ data.action }}}</h1>
								<button class="modal-close modal-close-link dashicons dashicons-no-alt">
									<span class="screen-reader-text">Close modal panel</span>
								</button>
							</header>
							<article>
								<form action="" method="post">
								</form>
							</article>
						</section>
					</div>
				</div>
				<div class="wc-backbone-modal-backdrop modal-close"></div>
			</script>
			<?php
		}
	}
}

new WC_GC_Admin_Exporters();

