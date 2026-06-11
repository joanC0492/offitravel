<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get icon 
$icon = ovabrw_get_meta_data( 'icon', $args );

// Class icon
$class_icon = ovabrw_get_meta_data( 'value', $icon );

// Text view details
$text_view_details = ovabrw_get_meta_data( 'label_view_details', $args );

// Show image
$show_image = ovabrw_get_meta_data( 'show_image', $args );

// Show title
$show_title = ovabrw_get_meta_data( 'show_title', $args );

// Show product counts
$show_product_counts = ovabrw_get_meta_data( 'show_product_counts', $args );

// Text counts
$text_product_counts = ovabrw_get_meta_data( 'text_product_counts', $args );

// Slide options
$slide_options = [
	'slidesPerView' 		=> ovabrw_get_meta_data( 'item_number', $args ),
	'slidesPerGroup' 		=> ovabrw_get_meta_data( 'slides_to_scroll', $args ),
	'spaceBetween' 			=> ovabrw_get_meta_data( 'margin_items', $args ),
	'autoplay' 				=> 'yes' === ovabrw_get_meta_data( 'autoplay', $args ) ? true : false,
	'pauseOnMouseEnter' 	=> 'yes' === ovabrw_get_meta_data( 'pause_on_hover', $args ) ? true : false,
	'delay' 				=> ovabrw_get_meta_data( 'autoplay_speed', $args, 3000 ),
	'speed' 				=> ovabrw_get_meta_data( 'smartspeed', $args, 500 ),
	'loop' 					=> 'yes' === ovabrw_get_meta_data( 'infinite', $args ) ? true : false,
	'nav' 					=> 'yes' === ovabrw_get_meta_data( 'nav_control', $args ) ? true : false,
	'dots' 					=> true,
	'breakpoints' 			=> [
		'0' 	=> [
			'slidesPerView' => 1
		],
    	'768' 	=> [
    		'slidesPerView' => 2
    	],
    	'1024' 	=> [
    		'slidesPerView' => 3
    	],
    	'1300' 	=> [
    		'slidesPerView' => ovabrw_get_meta_data( 'item_number', $args )
    	]
	],
	'rtl' 					=> is_rtl() ? true: false
];

// Query term
$args_term = [
	'taxonomy'   => 'product_cat',
	'orderby' 	 => 'slug',
	'hide_empty' => true,
];

// Get product categories
$categories = ovabrw_get_meta_data( 'categories', $args );
if ( ovabrw_array_exists( $categories ) ) {
    $args_term['include'] = $categories;
}

// Get product categories
$product_categories = get_terms( $args_term );
if ( ovabrw_array_exists( $product_categories ) ): ?>
	<div class="ova_product_categories" data-options="<?php echo esc_attr( json_encode( $slide_options ) ); ?>">
		<div class="swiper swiper-loading">
			<div class="swiper-wrapper">
				<?php foreach ( $product_categories as $cat ):
					// Get id
					$term_id = $cat->term_id;

					// Get name
					$name = $cat->name;

					// Get permalink
					$link = get_term_link( $term_id, 'product_cat' );

					// Get thumbnail id
					$thumbnail_id = get_term_meta( $term_id, 'thumbnail_id', true );

					// Get thumbnail URL
					$thumbnail_url = wp_get_attachment_url( $thumbnail_id ) ? wp_get_attachment_url( $thumbnail_id ) : wc_placeholder_img_src( 'thumbnail' );

					// Get thumbnail alt
					$alt_text = get_post_meta( $thumbnail_id, '_wp_attachment_image_alt', true );
					if ( !$alt_text ) $alt_text = $name;
				?>
					<div class="item swiper-slide">
						<?php if ( 'yes' === $show_image ): ?>
							<div class="image-thumbnail">
								<img src="<?php echo esc_url( $thumbnail_url ); ?>" alt="<?php echo esc_attr( $alt_text ); ?>">

								<?php if ( $text_view_details || $class_icon ): ?>
									<a href="<?php echo esc_url( $link ); ?>" class="btn read-more">
										<span>
											<?php echo esc_html( $text_view_details ); ?>
										</span>
										<?php if ( $class_icon ) { 
										    \Elementor\Icons_Manager::render_icon( $icon, [ 'aria-hidden' => 'true' ] );
										} ?>
									</a>
								<?php endif; ?>
							</div>
						<?php endif;

						// Name
						if ( 'yes' === $show_title ): ?>
							<h4 class="title">
								<a href="<?php echo esc_url( $link ); ?>" target="_self">
									<?php echo esc_html( $name ); ?>
								</a>
							</h4>
						<?php endif;

						// Product counts
						if ( 'yes' === $show_product_counts ): ?>
							<div class="counts">
								<span>
									<?php echo sprintf( esc_html( '%s +', 'ova-brw' ), $cat->count ); ?>
								</span>
								<span class="text">
									<?php echo esc_html( $text_product_counts ); ?>
								</span>
							</div>
						<?php endif;

						// Show image
						if ( 'yes' != $show_image ): ?>
							<div class="image-thumbnail no-thumbnail">
								<?php if ( $text_view_details || $class_icon ): ?>
									<a href="<?php echo esc_url( $link ); ?>" class="btn read-more">
										<span>
											<?php echo esc_html( $text_view_details ); ?>
										</span>
										<?php if ( $class_icon ) { 
										    \Elementor\Icons_Manager::render_icon( $icon, [ 'aria-hidden' => 'true' ] );
										} ?>
									</a>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php if ( $slide_options['nav'] ): ?>
			<div class="swiper-nav">
				<div class="button-nav button-prev">
					<i class="icomoon icomoon-pre-small" aria-hidden="true"></i>
				</div>
				<div class="button-nav button-next">
					<i class="icomoon icomoon-next-small" aria-hidden="true"></i>
				</div>
			</div>
		<?php endif; ?>
		<div class="button-dots"></div>
	</div>
<?php endif;