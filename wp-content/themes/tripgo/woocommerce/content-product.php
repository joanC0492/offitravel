<?php if ( !defined( 'ABSPATH' ) ) exit();

// Global product
global $product;

// Ensure visibility.
if ( !$product || !$product->is_visible() ) return;

?>

<div <?php wc_product_class( 'ova-product', $product ); ?>>
	<?php
	/**
	 * Hook: tripgo_wc_before_shop_loop_item.
	 */
	do_action( 'tripgo_wc_before_shop_loop_item' );

	/**
	 * Hook: tripgo_wc_loop_item.
	 */
	do_action( 'tripgo_wc_loop_item' );

	/**
	 * Hook: tripgo_wc_after_shop_loop_item.
	 */
	do_action( 'tripgo_wc_after_shop_loop_item' );
	?>
</div>