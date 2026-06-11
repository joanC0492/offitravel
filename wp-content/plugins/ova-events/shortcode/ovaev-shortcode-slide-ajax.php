<?php defined( 'ABSPATH' ) || exit;

/**
 * Class OVAEV_Shortcode_Events_Slide_Ajax
 */
if ( !class_exists( 'OVAEV_Shortcode_Events_Slide_Ajax' ) ) {

	class OVAEV_Shortcode_Events_Slide_Ajax {

		/**
		 * Shortcode name
		 */
		public $shortcode = 'ovaev_slide_ajax';

		/**
		 * Constructor
		 */
		public function __construct() {
			add_shortcode( $this->shortcode, [ $this, 'init_shortcode' ] );
		}

		/**
		 * Get shortcode data
		 */
		public function get_data_shortcode( $settings ) {
			// Post data
			$number_post 	= $settings['number_post'];
			$order_post 	= $settings['order_post'];
			$orderby_post 	= $settings['orderby_post'];
			$show_all 		= $settings['show_all'];
			$show_featured 	= $settings['show_featured'];
			$show_filter 	= $settings['show_filter'];
			$exclude_cat 	= $settings['exclude_cat'];
			$text_read_more = $settings['text_read_more'];
	        $show_read_more = $settings['show_read_more'] != '' ? esc_html( $settings['show_read_more'] ) : '';

	        $cat_exclude = [
	        	'exclude' => explode( ", ",$exclude_cat ),
	        ];


			$terms 				= get_terms( 'event_category', $cat_exclude );
			$settings['terms'] 	= $terms;
			$count 				= count( $terms );

			$term_id_filter 	= [];
			foreach ( $terms as $term ) {
				$term_id_filter[] = $term->term_id;
			}

			$term_id_filter_string = implode(", ", $term_id_filter);
			$first_term = '';
			if( $terms ){
				$first_term = $terms[0]->term_id;	
			}
			$settings['first_term'] 			= $first_term;
			$settings['term_id_filter_string'] 	= $term_id_filter_string;
			$settings['column'] 				= 1;
			
			$args_base = [
				'post_type' 		=> 'event',
				'post_status' 		=> 'publish',
				'posts_per_page' 	=> $number_post,
				'order' 			=> $order_post,
				'orderby' 			=> $orderby_post,
			];

			// Show Featured
			if ( $show_featured == 'yes' ) {
				$args_featured = [
					'meta_key' 		=> 'ovaev_special',
					'meta_query'	=> [
						[
							'key' 		=> 'ovaev_special',
							'compare' 	=> '=',
							'value' 	=> 'checked'
						]
					]
				];
			} else {
				$args_featured = [];
			}

			if ( $show_all !== 'yes' && $first_term != '' ) {
				$args_cat = [
					'tax_query' => [
						[
							'taxonomy' => 'event_category',
							'field'    => 'id',
							'terms'    => $first_term
						]
					]
				];

				$args = array_merge_recursive( $args_cat, $args_base, $args_featured );
				$event_posts = new \WP_Query( $args );

			} else {
				$args_cat = [
					'tax_query' => [
						[
							'taxonomy' => 'event_category',
							'field'    => 'id',
							'terms'    => $term_id_filter
						]
					]
				];

				$args = array_merge_recursive($args_cat, $args_base, $args_featured);
				$event_posts = new \WP_Query( $args );
			}

			// Slide options
			$slide_options = [
				'slidesPerView' 		=> isset( $settings['item_number'] ) ? (int)$settings['item_number'] : 3,
				'slidesPerGroup' 		=> 1,
				'spaceBetween' 			=> isset( $settings['margin_items'] ) ? (int)$settings['margin_items'] : 30,
				'autoplay' 				=> isset( $settings['autoplay'] ) && $settings['autoplay'] === 'yes' ? true : false,
				'pauseOnMouseEnter' 	=> isset( $settings['pause_on_hover'] ) && $settings['pause_on_hover'] === 'yes' ? true : false,
				'delay' 				=> isset( $settings['autoplay_speed'] ) ? (int)$settings['autoplay_speed'] : 3000,
				'speed' 				=> isset( $settings['smartspeed'] ) ? (int)$settings['smartspeed'] : 500,
				'loop' 					=> isset( $settings['infinite'] ) && $settings['infinite'] === 'yes' ? true : false,
				'nav' 					=> isset( $settings['nav_control'] ) && $settings['nav_control'] === 'yes' ? true : false,
				'nav_prev' 				=> isset( $settings['owl_nav_prev'] ) ? $settings['owl_nav_prev'] : 'arrow_carrot-left',
				'nav_next' 				=> isset( $settings['owl_nav_next'] ) ? $settings['owl_nav_next'] : 'arrow_carrot-right',
				'dots' 					=> isset( $settings['dot_control'] ) && $settings['dot_control'] === 'yes' ? true : false,
				'breakpoints' 			=> [
					'0' 	=> [
						'slidesPerView' => 1
					],
		        	'768' 	=> [
		        		'slidesPerView' => 2
		        	],
		        	'1024' 	=> [
		        		'slidesPerView' => 3
		        	]
				],
				'rtl' 					=> is_rtl() ? true: false
			];

			$data = [
				'data_posts' 	=> $event_posts,
				'slide_options' => $slide_options,
				'settings' 		=> $settings
			];

			return $data;
		}

		/**
		 * init shortcode
		 */
		function init_shortcode( $args, $content = null ) {
			// Get content
			$content = get_the_content( get_the_ID() );
			if ( has_shortcode( $content, 'ovaev_slide_ajax') ) {
				wp_enqueue_style( 'swiper', OVAEV_PLUGIN_URI.'assets/libs/swiper/swiper-bundle.min.css' );
				wp_enqueue_script( 'swiper', OVAEV_PLUGIN_URI.'assets/libs/swiper/swiper-bundle.min.js', [ 'jquery' ], false, true );
			}

			if ( !empty( $args ) && is_array( $args ) ) {
				$attr = [
					//query events
					'number_post' 		=> isset($args['number_post']) ? (int)$args['number_post'] : 8,
					'order_post' 		=> isset($args['order_post']) ? $args['order_post'] : 'desc',
					'orderby_post' 		=> isset($args['orderby_post']) ? $args['orderby_post'] : 'date',
					'show_all' 			=> isset($args['show_all']) ? $args['show_all'] : 'yes',
					'show_featured' 	=> isset($args['show_featured']) ? $args['show_featured'] : 'no',
					'show_filter' 		=> isset($args['show_filter']) ? $args['show_filter'] : 'yes',
					'exclude_cat' 		=> isset($args['exclude_cat']) ? $args['exclude_cat'] : '',
					'text_read_more' 	=> isset($args['text_read_more']) ? $args['text_read_more'] : 'See All Events',
					'show_read_more' 	=> isset($args['show_read_more']) ? $args['show_read_more'] : 'yes',
					'layout' 			=> isset($args['layout']) ? (int)$args['layout'] : 1,

					// Slide options
					'item_number' 		=> isset($args['items']) ? (int)$args['items'] : 3,
					'slides_to_scroll' 	=> isset($args['slide_by']) ? (int)$args['slide_by'] : 1,
					'margin_items' 		=> isset($args['margin']) ? (int)$args['margin'] : 20,
					'pause_on_hover' 	=> isset($args['pause_on_hover']) ? $args['pause_on_hover'] : 'yes',
					'infinite' 			=> isset($args['loop']) ? $args['loop'] : 'no',
					'autoplay' 			=> isset($args['autoplay']) ? $args['autoplay'] : 'no',
					'autoplay_speed' 	=> isset($args['speed']) ? (int)$args['speed'] : 3000,
					'smartspeed' 		=> isset($args['smart_speed']) ? (int)$args['smart_speed'] : 500,
					'dot_control' 		=> isset($args['dot']) ? $args['dot'] : 'no',
					'nav_control' 		=> isset($args['nav']) ? $args['nav'] : 'yes',
					'owl_lazyload'		=> isset($args['owl_lazyload']) ? $args['owl_lazyload'] : 'yes',
					'owl_nav_prev' 		=> isset($args['owl_nav_prev']) ? $args['owl_nav_prev'] : 'arrow_carrot-left',
					'owl_nav_next' 		=> isset($args['owl_nav_next']) ? $args['owl_nav_next'] : 'arrow_carrot-right',

					'category' 			=> isset($args['category']) ? $args['category'] : 'all',
				];
			} else {
				$attr = [
					//query events
					'number_post' 		=> 8,
					'order_post' 		=> 'desc',
					'orderby_post' 		=> 'date',
					'show_all' 			=> 'yes',
					'show_featured' 	=> 'no',
					'show_filter' 		=> 'yes',
					'exclude_cat' 		=> '',
					'text_read_more' 	=> 'See All Events',
					'show_read_more' 	=> 'yes',
					'layout' 			=> 1,

					// Slider
					'item_number' 		=> 3,
					'slides_to_scroll' 	=> 1,
					'margin_items' 		=> 20,
					'pause_on_hover' 	=> 'yes',
					'infinite' 			=> 'no',
					'autoplay' 			=> 'yes',
					'autoplay_speed' 	=> 3000,
					'smartspeed' 		=> 500,
					'dot_control' 		=> 'no',
					'nav_control' 		=> 'yes',
					'owl_nav_prev' 		=> 'arrow_carrot-left',
					'owl_nav_next' 		=> 'arrow_carrot-right',
					'category' 			=> 'all',
				];
			}
			
			// Get shortcode data
			$data = $this->get_data_shortcode( $attr );
			
			// Get template
			$template = apply_filters( 'shortcode_ovaev_ajax', 'elements/ovaev_events_ajax_content.php' );

			ob_start();
			ovaev_get_template( $template, $data );
			return ob_get_clean();
		}
	}

	// init class
	new OVAEV_Shortcode_Events_Slide_Ajax();
}
