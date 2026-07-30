<?php
/**
 * Admin export modal html
 *
 * @package  WooCommerce Gift Cards
 * @since    1.6.0
 * @version  1.6.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?><div class="woocommerce_gc_export_form woocommerce-exporter-wrapper">
	<div class="gc_export_form_inner woocommerce-exporter">

		<section>
			<p><?php esc_html_e( 'Generate and download a CSV file with Gift Cards data.', 'woocommerce-gift-cards' ); ?></p>
		</section>

		<section>
			<table class="form-table woocommerce-exporter-options">
				<tr>
					<td colspan="2">
						<h3 class="subheader"><?php esc_html_e( 'Options', 'woocommerce-gift-cards' ); ?></h3>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="woocommerce-exporter-filtered"><?php esc_html_e( 'Apply current filters', 'woocommerce-gift-cards' ); ?></label>
					</th>
					<td>
						<input type="checkbox" id="woocommerce-exporter-filtered" value="1">
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="woocommerce-exporter-meta"><?php esc_html_e( 'Export meta', 'woocommerce-gift-cards' ); ?></label>
					</th>
					<td>
						<input type="checkbox" id="woocommerce-exporter-meta" value="1">
					</td>
				</tr>
			</table>

			<progress class="woocommerce-exporter-progress" max="100" value="0"></progress>
		</section>
		<div class="wc-actions">
			<button type="submit" class="woocommerce-exporter-button button button-primary" value="Generate CSV"><?php esc_html_e( 'Download CSV', 'woocommerce-gift-cards' ); ?></button>
		</div>
	</div>
</div>
