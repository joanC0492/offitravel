<?php
/**
 * Admin View: Gift Cards list
 *
 * @package  WooCommerce Gift Cards
 * @since    1.0.0
 * @version  1.16.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap woocommerce woocommerce-gc-giftcards">

	<?php WC_GC_Admin_Menus::render_tabs(); ?>

	<h1 class="wp-heading-inline"><?php esc_html_e( 'Gift Cards', 'woocommerce-gift-cards' ); ?></h1>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=gc_giftcards&section=giftcard_importer' ) ); ?>" class="page-title-action woocommerce-gc-importer-button"><?php esc_html_e( 'Import', 'woocommerce-gift-cards' ); ?></a>
	<a href="#" class="page-title-action woocommerce-gc-exporter-button"><?php esc_html_e( 'Export', 'woocommerce-gift-cards' ); ?></a>


	<hr class="wp-header-end">
	<?php
	if ( $table->total_items > 0 ) {
		$table->views()
		?>
		<form id="giftcards-table" method="GET">
			<p class="search-box">
				<label for="post-search-input" class="screen-reader-text"><?php esc_html_e( 'Search gift cards', 'woocommerce-gift-cards' ); ?>:</label>
				<input type="search" value="<?php echo esc_attr( $search ); ?>" name="s" id="gc-search-input">
				<input type="submit" value="<?php echo esc_attr( 'Search', 'woocommerce-gift-cards' ); ?>" class="button" id="search-submit" name="">
			</p>
			<input type="hidden" name="page" value="<?php echo isset( $_REQUEST['page'] ) ? esc_attr( wc_clean( $_REQUEST['page'] ) ) : ''; ?>"/>
			<?php $table->display(); ?>
		</form>
	<?php } else { ?>

		<?php if ( $gc_product_exists ) { ?>

			<div class="woocommerce-BlankState">
				<h2 class="woocommerce-BlankState-message woocommerce-BlankState-message-step2">
					<?php esc_html_e( 'Hooray! You are now selling gift cards.', 'woocommerce-gift-cards' ); ?>
					<br/>
					<?php esc_html_e( 'Every time a gift card is ordered, a code will be issued and listed here.', 'woocommerce-gift-cards' ); ?>
				</h2>
				<a class="woocommerce-BlankState-cta button" target="_blank" href="<?php echo esc_url( WC_GC()->get_resource_url( 'docs-contents' ) ); ?>"><?php esc_html_e( 'Learn more about Gift Cards', 'woocommerce-gift-cards' ); ?></a>
			</div>

		<?php } else { ?>

			<div class="woocommerce-BlankState">
				<h2 class="woocommerce-BlankState-message">
					<?php esc_html_e( 'No gift card codes issued just yet.', 'woocommerce-gift-cards' ); ?>
					<br/>
					<?php esc_html_e( 'A code will be issued when a gift card product is purchased.', 'woocommerce-gift-cards' ); ?>
				</h2>
				<a class="woocommerce-BlankState-cta button-primary button" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=product&todo=giftcard' ) ); ?>"><?php esc_html_e( 'Create a gift card product', 'woocommerce-gift-cards' ); ?></a>
				<a class="woocommerce-BlankState-cta button" target="_blank" href="<?php echo esc_url( WC_GC()->get_resource_url( 'docs-contents' ) ); ?>"><?php esc_html_e( 'Learn more about Gift Cards', 'woocommerce-gift-cards' ); ?></a>
			</div>

		<?php } ?>

	<?php } ?>
</div>
