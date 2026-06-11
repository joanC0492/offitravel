<?php if ( !defined( 'ABSPATH' ) ) exit();

// Slide options
$slide_options = [
	'slidesPerView' 		=> $args['item_number'],
	'slidesPerGroup' 		=> $args['slides_to_scroll'],
	'spaceBetween' 			=> $args['margin_items'],
	'autoplay' 				=> $args['autoplay'] === 'yes' ? true : false,
	'pauseOnMouseEnter' 	=> $args['pause_on_hover'] === 'yes' ? true : false,
	'delay' 				=> $args['autoplay_speed'] ? $args['autoplay_speed'] : 3000,
	'speed' 				=> $args['smartspeed'] ? $args['smartspeed'] : 500,
	'loop' 					=> $args['infinite'] === 'yes' ? true : false,
	'nav' 					=> $args['nav_control'] === 'yes' ? true : false,
	'dots' 					=> $args['dot_control'] === 'yes' ? true : false,
	'breakpoints' 			=> [
		'0' 	=> [
			'slidesPerView' => 1
		],
    	'600' 	=> [
    		'slidesPerView' => 2
    	],
    	'1024' 	=> [
    		'slidesPerView' => $args['item_number']
    	]
	],
	'rtl' 					=> is_rtl() ? true: false
];

// Get template
$template = $args['template'];

// Get destinations
$destinations = ovadestination_get_data_destination_slider_el( $args );

?>
<div class="ova-destination-slider">
	<?php if ( $destinations->have_posts() ): ?>
		<div class="content content-<?php echo esc_attr( $template ); ?> slide-destination" data-options="<?php echo esc_attr( json_encode( $slide_options ) ); ?>">
			<div class="swiper swiper-loading">
				<div class="swiper-wrapper">
					<?php while ( $destinations->have_posts() ) : $destinations->the_post(); ?>
						<div class="swiper-slide">
							<?php if ( $template === 'template1' ) {
					        	ovadestination_get_template( 'part/item-destination.php', $args );
					        } elseif ( $template === 'template2' ) {
					        	ovadestination_get_template( 'part/item-destination2.php', $args );
					        } elseif ( $template === 'template3' ) {
					        	ovadestination_get_template( 'part/item-destination3.php', $args );
					        } else {
					        	ovadestination_get_template( 'part/item-destination.php', $args );
						    } ?>
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