<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get product id
$product_id = ovabrw_get_meta_data( 'id', $args, get_the_id() );

// Get product
$product = wc_get_product( $product_id );

// Get tempate
$template = ovabrw_get_product_template( $product_id );

// Get header
get_header( 'shop' );
	/**
	 * woocommerce_before_main_content hook.
	 *
	 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
	 * @hooked woocommerce_breadcrumb - 20
	 */
	do_action( 'woocommerce_before_main_content' );

	while ( have_posts() ): the_post();
		// Check rental type
		if ( !$product || !$product->is_type( OVABRW_RENTAL ) ) {
			wc_get_template_part( 'content', 'single-product' );
		} else {  
			do_action( 'woocommerce_before_single_product' );
			echo Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $template );
			do_action( 'woocommerce_after_single_product' );
		}
	endwhile; // END of the loop.

	/**
	 * woocommerce_after_main_content hook.
	 *
	 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
	 */
	do_action( 'woocommerce_after_main_content' );
	
	/**
	 * woocommerce_sidebar hook.
	 *
	 * @hooked woocommerce_get_sidebar - 10
	 */
	do_action( 'woocommerce_sidebar' );

// Get footer
get_footer( 'shop' );