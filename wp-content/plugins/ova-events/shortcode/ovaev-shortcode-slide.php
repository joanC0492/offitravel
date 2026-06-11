<?php defined( 'ABSPATH' ) || exit;

/**
 * Class OVAEV_Shortcode_Events_Slide
 */
if ( !class_exists( 'OVAEV_Shortcode_Events_Slide' ) ) {

	class OVAEV_Shortcode_Events_Slide {

		/**
		 * Shortcode name
		 */
		public $shortcode = 'ovaev_slide';

		/**
		 * Constructor
		 */
		public function __construct() {
			add_shortcode( $this->shortcode, [ $this, 'init_shortcode' ] );
		}

		/**
		 * init shortcode
		 */
		public function init_shortcode( $args, $content = null ) {
			// Get content
			$content = get_the_content( get_the_ID() );

			// Check variable shortcode
			if ( !empty($args) ) {
				$attr = [
					'item_number' 		=> isset($args['items']) ? (int)$args['items'] : 3,
					'slides_to_scroll' 	=> isset($args['slide_by']) ? (int)$args['slide_by'] : 1,
					'margin_items' 		=> isset($args['margin']) ? (int)$args['margin'] : 20,
					'pause_on_hover' 	=> isset($args['pause_on_hover']) ? $args['pause_on_hover'] : 'yes',
					'infinite' 			=> isset($args['loop']) ? $args['loop'] : 'yes',
					'autoplay' 			=> isset($args['autoplay']) ? $args['autoplay'] : 'yes',
					'autoplay_speed' 	=> isset($args['speed']) ? (int)$args['speed'] : 3000,
					'smartspeed' 		=> isset($args['smart_speed']) ? (int)$args['smart_speed'] : 500,
					'dot_control' 		=> isset($args['dot']) ? $args['dot'] : 'no',
					'nav_control' 		=> isset($args['nav']) ? $args['nav'] : 'yes',
					'layout' 			=> isset($args['layout']) ? (int)$args['layout'] : 1,
					'category' 			=> isset($args['category']) ? $args['category'] : 'all',
					'time_event'		=> isset($args['time_event']) ? $args['time_event'] : '',
					'total_count'		=> isset($args['number_post']) ? (int)$args['number_post'] : 8,
					'order_by'			=> isset($args['order_by']) ? $args['order_by'] : 'title',
					'order'				=> isset($args['order']) ? $args['order'] : 'DESC',
				];
			} else {
				$attr = [
					'item_number' 		=> 3,
					'slides_to_scroll' 	=> 1,
					'margin_items' 		=> 20,
					'pause_on_hover' 	=> 'yes',
					'infinite' 			=> 'yes',
					'autoplay' 			=> 'yes',
					'autoplay_speed' 	=> 3000,
					'smartspeed' 		=> 500,
					'dot_control' 		=> 'no',
					'nav_control' 		=> 'yes',
					'layout' 			=> 1,
					'category' 			=> 'all',
					'time_event'		=> '',
					'total_count'		=> 8,
					'order_by'			=> 'title',
					'order'				=> 'DESC',
				];
			}
			
			// Get template
			$template = apply_filters( 'shortcode_ovaev_slide', 'elements/ovaev_events_slide.php' );

			ob_start();
			ovaev_get_template( $template, $attr );
			return ob_get_clean();
		}
	}

	// init class
	new OVAEV_Shortcode_Events_Slide();
}
