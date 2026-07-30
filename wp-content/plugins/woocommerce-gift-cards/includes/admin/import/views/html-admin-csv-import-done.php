<?php
/**
 * Admin View: CSV Import done.
 *
 * @package  WooCommerce Gift Cards
 * @since    1.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wc-progress-form-content woocommerce-importer">
	<section class="woocommerce-importer-done">
		<?php
		$results = array();

		if ( 0 < $imported ) {
			$results[] = sprintf(
				/* translators: %d: gift cards count */
				_n( '%s gift card imported', '%s gift cards imported', $imported, 'woocommerce-gift-cards' ),
				'<strong>' . number_format_i18n( $imported ) . '</strong>'
			);
		}

		if ( 0 < $updated ) {
			$results[] = sprintf(
				/* translators: %d: gift cards count */
				_n( '%s gift card updated', '%s gift cards updated', $updated, 'woocommerce-gift-cards' ),
				'<strong>' . number_format_i18n( $updated ) . '</strong>'
			);
		}

		if ( 0 < $skipped ) {
			$results[] = sprintf(
				/* translators: %d: gift cards count */
				_n( '%s gift card was skipped', '%s gift cards were skipped', $skipped, 'woocommerce-gift-cards' ),
				'<strong>' . number_format_i18n( $skipped ) . '</strong>'
			);
		}

		if ( 0 < $failed ) {
			$results [] = sprintf(
				/* translators: %d: gift cards count */
				_n( 'Failed to import %s gift card', 'Failed to import %s gift cards', $failed, 'woocommerce-gift-cards' ),
				'<strong>' . number_format_i18n( $failed ) . '</strong>'
			);
		}

		if ( 0 < $failed || 0 < $skipped ) {
			$results[] = '<a href="#" class="woocommerce-importer-done-view-errors">' . __( 'View import log', 'woocommerce-gift-cards' ) . '</a>';
		}

		if ( ! empty( $file_name ) ) {
			$results[] = sprintf(
				/* translators: %s: File name */
				__( 'File uploaded: %s', 'woocommerce-gift-cards' ),
				'<strong>' . $file_name . '</strong>'
			);
		}

		/* translators: %d: import results */
		echo wp_kses_post( __( 'Import complete!', 'woocommerce-gift-cards' ) . ' ' . implode( '. ', $results ) );
		?>
	</section>
	<section class="wc-importer-error-log" style="display:none">
		<table class="widefat wc-importer-error-log-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Gift Card', 'woocommerce-gift-cards' ); ?></th>
					<th><?php esc_html_e( 'Reason for failure', 'woocommerce-gift-cards' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				if ( count( $errors ) ) {
					foreach ( $errors as $error ) {
						if ( ! is_wp_error( $error ) ) {
							continue;
						}
						$error_data = $error->get_error_data();
						?>
						<tr>
							<th><code><?php echo esc_html( $error_data['row'] ); ?></code></th>
							<td><?php echo esc_html( $error->get_error_message() ); ?></td>
						</tr>
						<?php
					}
				}
				?>
			</tbody>
		</table>
	</section>
	<script type="text/javascript">
		jQuery( function() {
			jQuery( '.woocommerce-importer-done-view-errors' ).on( 'click', function() {
				jQuery( '.wc-importer-error-log' ).slideToggle();
				return false;
			} );
		} );
	</script>
	<div class="wc-actions">
		<a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=gc_giftcards' ) ); ?>"><?php esc_html_e( 'View gift cards', 'woocommerce-gift-cards' ); ?></a>
	</div>
</div>
