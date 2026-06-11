<?php if ( !defined( 'ABSPATH' ) ) exit;

// Slide options
$slide_options = [
	'slidesPerView' 		=> (int)$args['item_number'],
	'slidesPerGroup' 		=> (int)$args['slides_to_scroll'],
	'spaceBetween' 			=> (int)$args['margin_items'],
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
    	'576' 	=> [
    		'slidesPerView' => 1
    	],
    	'992' 	=> [
    		'slidesPerView' => 2
    	],
    	'1170' 	=> [
    		'slidesPerView' => (int)$args['item_number']
    	]
	],
	'rtl' 					=> is_rtl() ? true: false
];

// Get term
$term 		= get_term_by( 'name', $args['category'], 'event_category' );
$term_link 	= get_term_link( $term );

// Get layout
$layout = $args['layout'];

// Get events
$events = ovaev_get_events_elements( $args );

?>

<div class="ovaev-event-element ovaev-event-slide" >
	<div class="wp-content ovaev-slide" data-options="<?php echo esc_attr( json_encode( $slide_options ) ); ?>">
		<?php if( $events->have_posts() ): ?>
			<div class="swiper swiper-loading">
				<div class="swiper-wrapper">
					<?php while ( $events->have_posts() ) : $events->the_post(); ?>
						<div class="swiper-slide">
							<?php switch ( $layout ) {
								case '1':
									ovaev_get_template( 'event-templates/event-type1.php' );
									break;
								case '2':
									ovaev_get_template( 'event-templates/event-type3.php' );
									break;
								default:
									ovaev_get_template( 'event-templates/event-type1.php' );
							} ?>
						</div>
					<?php endwhile; ?>
				</div>
			</div>
			<?php if ( $slide_options['nav'] ): ?>
				<div class="button-nav button-prev">
					<i class="arrow_carrot-left" aria-hidden="true"></i>
				</div>
				<div class="button-nav button-next">
					<i class="arrow_carrot-right" aria-hidden="true"></i>
				</div>
			<?php endif; ?>
			<?php if ( $slide_options['dots'] ): ?>
				<div class="button-dots"></div>
			<?php endif; ?>
		<?php endif; wp_reset_postdata(); ?>
	</div>
</div>