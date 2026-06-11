<?php if ( !defined( 'ABSPATH' ) ) exit;

// Get tour product ids
$product_ids = ovabrw_get_tour_product_ids();

?>

<div class="ovabrw-order">
	<div class="item">
		<h3 class="title">
			<?php esc_html_e( 'Product', 'ova-brw' ); ?>
		</h3>
		<div class="rental_item">
			<label>
				<?php esc_html_e( 'Choose Product', 'ova-brw' ); ?>
			</label>
			<select class="ovabrw-data-product ovabrw-input-required" name="ovabrw-data-product[]" data-symbol="<?php echo get_woocommerce_currency_symbol(); ?>">
				<option value="">
					<?php esc_html_e( 'Select product', 'ova-brw' ); ?>
				</option>
				<?php if ( ovabrw_array_exists( $product_ids ) ):
					foreach ( $product_ids as $pid ): ?>
						<option value="<?php echo esc_attr( $pid ); ?>">
							<?php echo esc_html( get_the_title( $pid ) ); ?>
						</option>
					<?php endforeach;
				endif; ?>
			</select>
			<div class="loading">
				<div class="dashicons-before dashicons-update-alt"></div>
			</div>
		</div>
	</div>
	<button class="button delete_order">x</button>
</div>