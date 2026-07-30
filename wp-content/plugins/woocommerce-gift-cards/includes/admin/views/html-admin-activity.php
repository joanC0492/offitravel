<?php
/**
 * Admin View: Activity list
 *
 * @package  WooCommerce Gift Cards
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap woocommerce woocommerce-gc-giftcards">

	<?php WC_GC_Admin_Menus::render_tabs(); ?>

	<h1 class="wp-heading-inline"><?php esc_html_e( 'Activity', 'woocommerce-gift-cards' ); ?></h1>

	<hr class="wp-header-end">

	<form id="activity-table" method="GET">
		<p class="search-box">
			<label for="post-search-input" class="screen-reader-text"><?php esc_html_e( 'Search activity', 'woocommerce-gift-cards' ); ?>:</label>
			<input type="search" value="<?php echo esc_attr( $search ); ?>" name="s" id="gc-search-input">
			<input type="submit" value="<?php echo esc_attr( 'Search', 'woocommerce-gift-cards' ); ?>" class="button" id="search-submit" name="">
		</p>
		<input type="hidden" name="page" value="<?php echo isset( $_REQUEST['page'] ) ? esc_attr( wc_clean( $_REQUEST['page'] ) ) : ''; ?>"/>
		<?php $table->display(); ?>
	</form>
</div>
