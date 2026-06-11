<?php defined( 'ABSPATH' ) || exit;

/**
 * Class OVAEV_Shortcode_Events_Search_Ajax
 */
if ( !class_exists( 'OVAEV_Shortcode_Events_Search_Ajax' ) ) {

	class OVAEV_Shortcode_Events_Search_Ajax {

		/**
		 * Shortcode name
		 */
		public $shortcode = 'ovaev_search_ajax';

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
			$posts_per_page = $settings['posts_per_page'];
			$order_post 	= $settings['order'];
			$orderby 		= $settings['order_by'];
			$category_slug 	= $settings['category'];
			$time_event 	= $settings['time_event'];

			$args = [
				'post_type' 		=> 'event',
				'post_status' 		=> 'publish',
				'posts_per_page' 	=> $posts_per_page,
				'order' 			=> $order_post,
				'offset'			=> 0
			];

			switch ( $orderby ) {
				case 'title':
					$args['orderby'] = $orderby;
					break;
				case 'event_custom_sort':
					$args['orderby'] 	= 'meta_value';
					$args['meta_key'] 	= $orderby;
					break;
				case 'ovaev_start_date_time':
					$args['orderby'] 	= 'meta_value';
					$args['meta_key'] 	= $orderby;
					break;
				case 'ID':
					$args['orderby'] = $orderby;
					break;
			}


			if ( 'all' != $category_slug ) {
				$args['tax_query'] = [
					[
						'taxonomy' => 'event_category',
	                    'field'    => 'slug',
	                    'terms'    => $category_slug
					]
				];
	        }

	        if ( 'current' === $time_event ) {
	        	$args['meta_query'] = [
	        		[
	        			'relation' => 'OR',
	                    [
	                    	'key'     => 'ovaev_start_date_time',
	                        'value'   => [ current_time( 'timestamp' ) - 1, current_time( 'timestamp' ) + ( 24*60*60 ) + 1 ],
	                        'type'    => 'numeric',
	                        'compare' => 'BETWEEN'
	                    ],
	                    [
	                    	'relation' => 'AND',
	                        [
	                        	'key'     => 'ovaev_start_date_time',
	                            'value'   => current_time( 'timestamp' ),
	                            'compare' => '<'
	                        ],
	                        [
	                        	'key'     => 'ovaev_end_date_time',
	                            'value'   => current_time( 'timestamp' ),
	                            'compare' => '>='
	                        ]
	                    ]
	        		]
	        	];
	        } elseif ( 'upcoming' === $time_event ) {
	        	$args['meta_query'] = [
	        		[
	        			[
	        				'key'     => 'ovaev_start_date_time',
	                        'value'   => current_time( 'timestamp' ),
	                        'compare' => '>'
	        			]
	        		]
	        	];
	        } elseif ( 'past' === $time_event ) {
	        	$args['meta_query'] = [
	        		[
	        			'key'     => 'ovaev_end_date_time',
	                    'value'   => current_time('timestamp' ),
	                    'compare' => '<'
	        		]
	        	];
	        }

	        // Get events
			$events = new \WP_Query( $args );

			$data = [
				'events' 	=> $events,
				'settings' 	=> $settings,
			];

			return $data;
		}

		/**
		 * init shortcode
		 */
		public function init_shortcode( $args, $content = null ) {
			//get content
			$content = get_the_content( get_the_ID() );

			//check shortcode
			if ( is_page() && has_shortcode( $content, 'ovaev_search_ajax') ) {
				wp_enqueue_style( 'ova-datetimepicker', OVAEV_PLUGIN_URI.'assets/libs/datetimepicker/jquery.datetimepicker.css' );
				wp_enqueue_script( 'ova-datetimepicker', OVAEV_PLUGIN_URI.'assets/libs/datetimepicker/jquery.datetimepicker.js', [ 'jquery' ], false, true );
			}
			//check variable shortcode

			if ( !empty($args) ) {
				$attr = [
					//query events
					'layout' 			=> isset($args['layout']) 			? (int)$args['layout'] 		: 1,
					'column' 			=> isset($args['column']) 			? $args['column'] 			: 'col3',
					'posts_per_page' 	=> isset($args['posts_per_page']) 	? $args['posts_per_page'] 	: 9,
					'order' 			=> isset($args['order']) 			? $args['order'] 			: 'DESC',
					'order_by' 			=> isset($args['order_by']) 		? $args['order_by'] 		: 'title',
					'category' 			=> isset($args['category']) 		? $args['category'] 		: 'all',
					'time_event' 		=> isset($args['time_event']) 		? $args['time_event'] 		: 'all',
				];
			} else {
				$attr = [
					'layout' 			=> 1, 			// 1, 2, 3, 4, 5, 6
					'column' 			=> 'col3',		// col1, col2, col3
					'posts_per_page' 	=> 9,
					'order' 			=> 'DESC',		// DESC, ASC
					'order_by' 			=> 'title',     // title, event_custom_sort, ovaev_start_date_time, ID
					'category' 			=> 'all',		// all or slug
					'time_event' 		=> 'all',		// all, current, upcoming, past
				];
			}
			
			$data = $this->get_data_shortcode($attr);
			
			// Get template
			$template = apply_filters( 'shortcode_ovaev_ajax', 'elements/ovaev_events_search_ajax.php' );

			ob_start();
			ovaev_get_template( $template, $data );
			return ob_get_clean();
		}
	}

	// init class
	new OVAEV_Shortcode_Events_Search_Ajax();
}