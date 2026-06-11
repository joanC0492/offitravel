<?php if ( !defined( 'ABSPATH' ) ) exit();

// Global product
global $product;

/**
 * Hook: woocommerce_before_single_product.
 *
 * @hooked woocommerce_output_all_notices - 10
 */
do_action( 'woocommerce_before_single_product' );

if ( post_password_required() ): ?>
	<div class="row_site">
		<div class="container_site">
			<div id="main-content" class="main">
				<?php echo get_the_password_form(); // WPCS: XSS ok. ?>
			</div>
		</div>
	</div>
	<?php return;
endif;

// Show fields
$args_show = [
	'show_title' 		 => get_theme_mod( 'tour_single_show_title', 'yes' ),
	'show_location'		 => get_theme_mod( 'tour_single_show_location', 'yes' ),
	'show_rating'		 => get_theme_mod( 'tour_single_show_rating', 'yes' ),
	'show_wishlist'		 => get_theme_mod( 'tour_single_show_wishlist', 'yes' ),
	'show_video'		 => get_theme_mod( 'tour_single_show_video_button', 'yes' ),
	'show_gallery'	 	 => get_theme_mod( 'tour_single_show_gallery_button', 'yes' ),
	'show_share'  		 => get_theme_mod( 'tour_single_show_share_button', 'yes' ),
	'show_gallery_slide' => get_theme_mod( 'tour_single_show_gallery_slide', 'yes' ),
	'show_features'		 => get_theme_mod( 'tour_single_show_features', 'yes' ),
	'show_description'   => get_theme_mod( 'tour_single_show_description', 'yes' ),
	'show_inc_exc'  	 => get_theme_mod( 'tour_single_show_inc_exc', 'yes' ),
	'show_tour_plan'  	 => get_theme_mod( 'tour_single_show_tour_plan', 'yes' ),
	'show_map'		 	 => get_theme_mod( 'tour_single_show_map', 'yes' ),
	'show_reviews'		 => get_theme_mod( 'tour_single_show_reviews', 'yes' ),
	'show_form'			 => get_theme_mod( 'tour_single_show_form', 'yes' ),
	'show_table_price'	 => get_theme_mod( 'tour_single_show_table_price', 'yes' ),
	'show_related'  	 => get_theme_mod( 'tour_single_show_related', 'yes' )
];

?>

<div id="product-<?php the_ID(); ?>" <?php wc_product_class( '', $product ); ?>>
	<?php
		/**
		 * Hook: tripgo_wc_before_single_product_summary.
		 */
		do_action( 'tripgo_wc_before_single_product_summary' );
	?>
	<div class="ova-content-single-product">
		<div class="single-product-header">
			<?php
				/**
				 * Hook: tripgo_wc_before_single_product_header.
				 */
				do_action( 'tripgo_wc_before_single_product_header' );

				/**
				 * Hook: tripgo_wc_before_single_product_top_header.
				 */
				do_action( 'tripgo_wc_before_single_product_top_header' );

				/**
				 * Hook: tripgo_wc_single_product_top_header.
				 *
				 * @hooked tripgo_wc_template_single_title - 5
				 * @hooked tripgo_wc_template_single_video_gallery - 10
				 */
				do_action( 'tripgo_wc_single_product_top_header', $args_show );
					
				/**
				 * Hook: tripgo_wc_after_single_product_top_header.
				 */
				do_action( 'tripgo_wc_after_single_product_top_header' );
				
				/**
				 * Hook: tripgo_wc_single_product_header.
				 *
				 * @hooked tripgo_wc_template_single_location - 10
				 * @hooked tripgo_wc_template_single_slideshow - 10
				 * @hooked tripgo_wc_template_single_features - 10
				 */
				do_action( 'tripgo_wc_single_product_header', $args_show );

				/**
				 * Hook: tripgo_wc_after_single_product_header.
				 */
				do_action( 'tripgo_wc_after_single_product_header' );
			?>
		</div>
		<div class="single-product-summary">
			<?php
				/**
				 * Hook: tripgo_wc_before_single_product_content.
				 */
				do_action( 'tripgo_wc_before_single_product_content' );
			
				/**
				 * Hook: tripgo_wc_before_single_product_summary_left.
				 */
				do_action( 'tripgo_wc_before_single_product_summary_left' );
				
				/**
				 * Hook: tripgo_wc_single_product_summary_left.
				 *
				 * @hooked tripgo_wc_template_single_content - 10
				 * @hooked tripgo_wc_template_single_included-excluded - 10
				 * @hooked tripgo_wc_template_single_plan - 10
				 * @hooked tripgo_wc_template_single_map - 10
				 * @hooked tripgo_wc_template_single_review - 10
				 */

				do_action( 'tripgo_wc_single_product_summary_left', $args_show );

				/**
				 * Hook: tripgo_wc_after_single_product_summary_left.
				 */
				do_action( 'tripgo_wc_after_single_product_summary_left' );
				
				/**
				 * Hook: tripgo_wc_before_single_product_summary_right.
				 */
				do_action( 'tripgo_wc_before_single_product_summary_right' );
				
				/**
				 * Hook: tripgo_wc_single_product_summary_right.
				 *
				 * @hooked tripgo_wc_template_single_forms - 10
				 * @hooked tripgo_wc_template_single_price_table - 10
				 */

				do_action( 'tripgo_wc_single_product_summary_right', $args_show );

				/**
				 * Hook: tripgo_wc_after_single_product_summary_right.
				 */
				do_action( 'tripgo_wc_after_single_product_summary_right' );
				
				/**
				 * Hook: tripgo_wc_after_single_product_content.
				 */
				do_action( 'tripgo_wc_after_single_product_content' );
			?>
		</div>
		<?php if ( 'yes' === tripgo_get_meta_data( 'show_related', $args_show ) ): ?>
			<div class="single-product-related">
				<?php
					/**
					 * Hook: tripgo_wc_before_single_product_content.
					 */
					do_action( 'tripgo_wc_before_single_product_content' );

					/**
					 * Hook: tripgo_wc_single_product_related.
					 *
					 * @hooked tripgo_wc_template_single_product_related - 10
					 */
					do_action( 'tripgo_wc_single_product_related' );

					/**
					 * Hook: tripgo_wc_after_single_product_content.
					 */
					do_action( 'tripgo_wc_after_single_product_content' );
				?>
			</div>
		<?php endif; ?>
	</div>
	<?php
		/**
		 * Hook: tripgo_wc_after_single_product_summary.
		 *
		 * @hooked woocommerce_output_product_data_tabs - 10
		 * @hooked woocommerce_upsell_display - 15
		 * @hooked woocommerce_output_related_products - 20
		 */
		do_action( 'tripgo_wc_after_single_product_summary' );
	?>
</div>
<?php do_action( 'woocommerce_after_single_product' ); ?>