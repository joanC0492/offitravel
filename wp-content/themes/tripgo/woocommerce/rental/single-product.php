<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get product id
$product_id = tripgo_get_meta_data( 'id', $args, get_the_id() );

// Get product
$product = wc_get_product( $product_id );

// Get product template
$template = ovabrw_get_product_template( $product_id );

// Get header
get_header( 'shop' ); 

/**
 * tripgo_wc_before_main_content hook.
 */
do_action( 'tripgo_wc_before_main_content' );

while ( have_posts() ) : the_post();
	if ( 'default' === $template ) {
		wc_get_template_part( 'rental/content', 'single-product' );
	} else {
		// WPML
		$template = apply_filters( 'wpml_object_id', $template, 'elementor_library', true );

		do_action( 'woocommerce_before_single_product' );
		echo Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $template );
		do_action( 'woocommerce_after_single_product' );
	}
endwhile; // end of the loop.

/**
 * tripgo_wc_after_main_content hook.
 */
do_action( 'tripgo_wc_after_main_content' );

// Get footer
get_footer( 'shop' );