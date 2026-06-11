<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get id
$id = $args['id'];
if ( !$id ) $id = get_the_ID();

// Get thumbnail
$thumbnail = wp_get_attachment_image_url( get_post_thumbnail_id( $id ), 'ova_destination_square' );  
if ( !$thumbnail ) $thumbnail = \Elementor\Utils::get_placeholder_image_src();

// Get post data
$short_desc         = get_post_meta( $id, 'ova_destination_met_short_desc', true );
$group_tour_details = get_post_meta( $id, 'ova_destination_met_tour_details', true );
$sights             = get_post_meta( $id, 'ova_destination_met_sights', true );

// Show related
$show_related_destination_tour = get_theme_mod( 'single_related_destination_tour', 'yes' );

// Get post title
$post_title = get_the_title();

?>
<div class="info info-template2">
   <div class="left_main_content">
		<?php if ( !empty( $short_desc ) ): ?>
			<div class="short-description">
				<?php echo apply_filters( 'ova_the_content', $short_desc ); ?>
			</div>
		<?php endif;

		// Tour Details
		if ( !empty( $group_tour_details ) ):  ?>
			<div class="tour-details-wrapper">
				<h3 class="heading heading-tour-details">
					<?php echo esc_html__( 'Tour details', 'ova-destination' ); ?>
				</h3>
				<ul class="tour-details-content">
					<?php foreach ( $group_tour_details as $tour_detail ):
                        $tour_detail_title   = isset( $tour_detail['ova_destination_met_tour_details_title'] ) ? $tour_detail['ova_destination_met_tour_details_title'] : ''; 
                        $tour_detail_content = isset( $tour_detail['ova_destination_met_tour_details_content'] ) ? $tour_detail['ova_destination_met_tour_details_content'] : '';
					?>
						<li class="item-tour-details">
							<span class="title">
								<?php echo esc_html( $tour_detail_title ); ?>
							</span>
							<span class="content">
								<?php echo esc_html( $tour_detail_content ); ?>
							</span>
						</li>
					<?php endforeach; ?>					
				</ul>
		    </div>
		<?php endif; ?>	
    </div>
	<div class="main_content">
		<div class="destination-sights">
			<ul class="list-img">
				<li class="item-img featured-img">
					<a class="gallery-fancybox" 
						data-src="<?php echo esc_url( $thumbnail ); ?>" 
						data-fancybox="ova_destiantion_sights_group" 
						data-caption="<?php echo esc_attr( $post_title ); ?>">
	  					<img src="<?php echo esc_url( $thumbnail ); ?>" class="img-responsive" alt="<?php echo esc_attr( $post_title ); ?>" title="<?php echo esc_attr( $post_title ); ?>">
	  				</a>
				</li>
				<?php if ( $sights ):
					$k = 0;

					// Loop
					foreach ( $sights as $image_id => $image_url ):
						$image_alt   = get_post_meta($image_id, '_wp_attachment_image_alt', true);
		        	    $image_title = get_the_title( $image_id );
		        	  
						if ( !$image_alt ) {
							$image_alt = get_the_title( $image_id );
						}

						$blur = false;

						// Item class
						$item_class = 'item-img';
						if ( $k > 1 ) $item_class.= ' gallery_hidden';
						if ( $k == 1 && count( $sights ) > 2 ) {
							$item_class.= ' gallery_blur';
							$blur = true;
						}
					?>
						<li class="<?php echo esc_attr( $item_class ); ?>">
							<a class="gallery-fancybox" 
								data-src="<?php echo esc_url( $image_url ); ?>" 
								data-fancybox="ova_destiantion_sights_group" 
								data-caption="<?php echo esc_attr( $image_alt ); ?>">
			  					<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>" title="<?php echo esc_attr( $image_title ); ?>">
			  					<?php if ( $blur ): ?>
			  						<div class="blur-bg">
			  							<span class="gallery-count">
			  								<?php echo esc_html( '+', 'ova-destination' ) . esc_html( count( $sights ) - 2 ); ?>
			  							</span>
			  						</div>
			  					<?php endif; ?>
			  				</a>
						</li>
					<?php $k = $k +1 ; endforeach;
				endif; ?>
			</ul>
		</div>
	</div>
</div>
<?php if ( 'yes' == $show_related_destination_tour ):
    $slide_options = apply_filters( 'ft_wc_related_destination_tour_options', [
        'slidesPerView' 		=> 4,
		'slidesPerGroup' 		=> 1,
		'spaceBetween' 			=> 24,
		'autoplay' 				=> true,
		'pauseOnMouseEnter' 	=> true,
		'delay' 				=> 3000,
		'speed' 				=> 500,
		'loop' 					=> false,
		'nav' 					=> true,
		'dots' 					=> true,
		'breakpoints' 			=> [
			'0' 	=> [
				'slidesPerView' => 1
			],
        	'600' 	=> [
        		'slidesPerView' => 2
        	],
        	'1024' 	=> [
        		'slidesPerView' => 3
        	],
        	'1200' 	=> [
        		'slidesPerView' => 4
        	]
		],
		'rtl' 					=> is_rtl() ? true: false
    ]);

    // Get products
    $products = new WP_Query([
    	'post_type'      	=> 'product',
        'post_status'    	=> 'publish',
        'posts_per_page' 	=> '-1',
        'orderby'        	=> 'rand',
        'meta_query' 		=> [
        	[
            	'key'     => 'ovabrw_destination',
	            'value'   => $id,
	            'compare' => 'LIKE'
            ]
        ],
        'tax_query' 		=> [
        	[
            	'taxonomy' => 'product_type',
                'field'    => 'slug',
                'terms'    => 'ovabrw_car_rental'
            ]
        ]
    ]);

    if ( $products->have_posts() ): ?>
		<div class="ova-destination-related-wrapper">
			<h3 class="title">
				<?php echo esc_html__( 'Explore', 'ova-destination' ); ?>
			</h3>
			<?php if ( $products->have_posts() ): ?>
				<div class="ova-product-slider" data-options="<?php echo esc_attr(json_encode( $slide_options ) ); ?>">
					<div class="swiper swiper-loading">
						<div class="swiper-wrapper">
							<?php while ( $products->have_posts() ) : $products->the_post(); ?>
								<div class="swiper-slide">
									<?php wc_get_template_part( 'content', 'product' ); ?>
								</div>
							<?php endwhile; ?>
						</div>
					</div>
					<?php if ( $slide_options['nav'] ): ?>
						<div class="swiper-nav">
		                    <div class="button-nav button-prev">
		                        <i class="icomoon icomoon-angle-left" aria-hidden="true"></i>
		                    </div>
		                    <div class="button-nav button-next">
		                        <i class="icomoon icomoon-angle-right" aria-hidden="true"></i>
		                    </div>
		                </div>
					<?php endif; ?>
					<?php if ( $slide_options['dots'] ): ?>
						<div class="button-dots"></div>
					<?php endif; ?>
				</div>
			<?php endif; wp_reset_postdata(); ?>	
		</div>	
	<?php endif;
endif; ?>