<?php if ( !defined( 'ABSPATH' ) ) exit;

$post_id = get_the_ID();
$gallery = get_post_meta( $post_id, 'ova_met_gallery_id', true) ? get_post_meta( $post_id, 'ova_met_gallery_id', true ) : '';

// Slide options
$slide_options = [
	'slidesPerView' 		=> 1,
	'slidesPerGroup' 		=> 1,
	'spaceBetween' 			=> 0,
	'autoplay' 				=> true,
	'pauseOnMouseEnter' 	=> true,
	'delay' 				=> 5000,
	'speed' 				=> 500,
	'loop' 					=> true,
	'nav' 					=> false,
	'dots' 					=> true,
	'breakpoints' 			=> [
		'0' 	=> [
			'slidesPerView' => 1
		],
    	'768' 	=> [
    		'slidesPerView' => 1
    	],
    	'1024' 	=> [
    		'slidesPerView' => 1
    	]
	],
	'rtl' 					=> is_rtl() ? true: false
];

if ( !empty( $gallery ) && is_array( $gallery ) ): ?>
    <div class="slide_gallery" data-options="<?php echo esc_attr( json_encode( $slide_options ) ); ?>">
    	<div class="swiper swiper-loading">
			<div class="swiper-wrapper">
			  	<?php foreach ( $gallery as $key => $value ): ?>
				    <div class="swiper-slide">
				    	<?php echo wp_get_attachment_image( $value, 'large' ); ?>
				    </div>
			   	<?php endforeach; ?>
	   		</div>
	   	</div>
	   	<div class="button-dots"></div>
	</div>
	   
<?php endif;