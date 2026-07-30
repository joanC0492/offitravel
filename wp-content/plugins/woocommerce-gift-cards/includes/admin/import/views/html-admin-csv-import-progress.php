<?php
/**
 * Admin View: CSV Import progress.
 *
 * @package  WooCommerce Gift Cards
 * @since    1.6.0
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wc-progress-form-content woocommerce-importer woocommerce-importer__importing">
	<header>
		<span class="spinner is-active"></span>
		<h2><?php esc_html_e( 'Importing', 'woocommerce-gift-cards' ); ?></h2>
		<p><?php esc_html_e( 'Your gift cards are now being imported...', 'woocommerce-gift-cards' ); ?></p>
	</header>
	<section>
		<progress class="woocommerce-importer-progress" max="100" value="0"></progress>
	</section>
</div>
