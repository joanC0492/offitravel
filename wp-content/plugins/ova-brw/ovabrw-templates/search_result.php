<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get header
get_header( 'shop' );

/**
 * Hook: woocommerce_before_main_content.
 *
 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
 * @hooked woocommerce_breadcrumb - 20
 * @hooked WC_Structured_Data::generate_website_data() - 30
 */
do_action( 'woocommerce_before_main_content' );

// Get tour products
$tour_products = ovabrw_search_vehicle( $_GET );

?>
<header class="woocommerce-products-header">
	<?php if ( apply_filters( 'woocommerce_show_page_title', true ) ): ?>
		<h1 class="woocommerce-products-header__title page-title">
			<?php esc_html_e( 'Search Results', 'ova-brw' ); ?>
		</h1>
	<?php endif;

	/**
	 * Hook: woocommerce_archive_description.
	 *
	 * @hooked woocommerce_taxonomy_archive_description - 10
	 * @hooked woocommerce_product_archive_description - 10
	 */
	do_action( 'woocommerce_archive_description' ); ?>
</header>

<?php if ( false != $tour_products ):
	woocommerce_product_loop_start();

	if ( $tour_products->have_posts() ) : while ( $tour_products->have_posts() ) : $tour_products->the_post();
		wc_get_template_part( 'content', 'product' );
	endwhile; else :
		esc_html_e( 'No products found.', 'ova-brw' );
	endif; wp_reset_postdata();
	
	woocommerce_product_loop_end(); ?>
	<nav class="woocommerce-pagination">
		<?php $big = 999999999; // need an unlikely integer

		echo paginate_links([
			'base'               	=> str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
			'format'             	=> '?paged=%#%',
			'current' 				=> max( 1, get_query_var('paged') ),
			'total' 				=> $tour_products->max_num_pages,
			'show_all'           	=> false,
			'end_size'           	=> 1,
			'mid_size'           	=> 2,
			'prev_next'          	=> true,
			'prev_text'          	=> '<i class="arrow_carrot-left"></i>',
			'next_text'          	=> '<i class="arrow_carrot-right"></i>',
			'type'               	=> 'list',
			'add_args'           	=> false,
			'add_fragment'       	=> '',
			'before_page_number' 	=> '',
			'after_page_number'  	=> ''
		]); ?>
	</nav>
<?php else:
	/**
	 * Hook: woocommerce_no_products_found.
	 *
	 * @hooked wc_no_products_found - 10
	 */
	do_action( 'woocommerce_no_products_found' );
endif;

/**
 * Hook: woocommerce_after_main_content.
 *
 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
 */
do_action( 'woocommerce_after_main_content' );

/**
 * Hook: woocommerce_sidebar.
 *
 * @hooked woocommerce_get_sidebar - 10
 */
do_action( 'woocommerce_sidebar' );

// Get footer
get_footer( 'shop' );