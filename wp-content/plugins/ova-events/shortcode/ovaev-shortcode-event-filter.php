<?php defined( 'ABSPATH' ) || exit;

/**
 * Class OVAEV_Shortcode_Event_Filter
 */
if ( !class_exists( 'OVAEV_Shortcode_Event_Filter' ) ) {

	class OVAEV_Shortcode_Event_Filter {

		/**
		 * Shortcode name
		 */
		public $shortcode = 'ovaev_event_filter';

		/**
		 * Constructor
		 */
		public function __construct() {
			add_shortcode( $this->shortcode, [ $this, 'init_shortcode' ] );
		}

		/**
		 * Get shortcode data
		 */
		public function get_data_shortcode( $args ) {
			// Base
			$args_base = [
				'post_type' 		=> 'event',
				'post_status' 		=> 'publish',
				'posts_per_page' 	=> $args['page_per_posts'],
				'order' 			=> $args['order']
			];

			// Sort
			if ( $args['orderby'] === 'ovaev_start_date_time' || $args['orderby'] === 'event_custom_sort' ) {
		        $args_base['meta_key'] 	= $args['orderby'];
		        $args_base['orderby'] 	= 'meta_value_num';
		        $args_base['meta_type'] = 'NUMERIC';
		    } else {
		        $args_base['orderby'] = $args['orderby'];
		    }

			// Time
			$args_time = [];
			if ( $args['time'] === 'today' ) {
				$end = ovaev_get_end_date( 'today' );

				$args_time = [
					'meta_query' => [
						[
							'key'     => 'ovaev_start_date_time',
                            'value'   => [ current_time( 'timestamp' ), $end ],
                            'type'    => 'numeric',
                        	'compare' => 'BETWEEN'
						]
					]
				];
			} elseif ( $args['time'] === 'week' ) {
				$end = ovaev_get_end_date( 'week' );

				$args_time = [
					'meta_query' => [
						[
							'key'     => 'ovaev_start_date_time',
                            'value'   => [ current_time( 'timestamp' ), $end ],
                            'type'    => 'numeric',
                        	'compare' => 'BETWEEN'
						]
					]
				];
			} elseif ( $args['time'] === 'weekend' ) {
				$date_format 	= OVAEV_Settings::archive_event_format_date();
				$start 			= strtotime( date( $date_format, strtotime('this Saturday') ) );
				$end 			= ovaev_get_end_date( 'weekend' );

				$args_time = [
					'meta_query' => [
						[
							'key'     => 'ovaev_start_date_time',
                            'value'   => [ $start, $end ],
                            'type'    => 'numeric',
                        	'compare' => 'BETWEEN'
						]
					]
				];
			} elseif ( $args['time'] === 'upcoming' ) {
				$args_time = [
					'meta_query' => [
						[
							'key'     => 'ovaev_start_date_time',
                            'value'   => current_time( 'timestamp' ),
                            'compare' => '>'
						]
					]
				];
			} else {
				$args_time = [];
			}

			// Featured
			$args_featured = [];
			if ( $args['featured'] ) {
				$args_featured = [
					'meta_query' => [
						[
							'key' 		=> 'ovaev_special',
							'compare' 	=> '=',
							'value' 	=> 'checked'
						]
					]
				];
			}

			// Category in
			$args_incl_category = [];
			if ( $args['incl_category'] ) {
				$args_incl_category = [
					'tax_query' => [
						[
							'taxonomy' => 'event_category',
							'field'    => 'term_id',
							'terms'    => explode( ',', $args['incl_category'] ),
							'operator' => 'IN'
						]
					]
				];
			}

			// Category not in
			$args_excl_category = [];
			if ( $args['excl_category'] ) {
				$args_excl_category = [
					'tax_query' => [
						[
							'taxonomy' => 'event_category',
							'field'    => 'term_id',
							'terms'    => explode( ',', $args['excl_category'] ),
							'operator' => 'NOT IN'
						]
					]
				];
			}
			
			// Query
			$query = array_merge_recursive( $args_base, $args_time, $args_featured, $args_incl_category, $args_excl_category );

			// Get events
			$events = new \WP_Query( $query );

			return [
				'events' 	=> $events,
				'settings' 	=> $args,
			];
		}

		/**
		 * init shortcode
		 */
		public function init_shortcode( $args, $content = null ) {
			// Get content
			$content = get_the_content( get_the_ID() );

			// Check shortcode
			if ( has_shortcode( $content, 'ovaev_event_filter') ) {
				wp_enqueue_style( 'ova-datetimepicker', OVAEV_PLUGIN_URI.'assets/libs/datetimepicker/jquery.datetimepicker.css' );
				wp_enqueue_script( 'ova-datetimepicker', OVAEV_PLUGIN_URI.'assets/libs/datetimepicker/jquery.datetimepicker.js', [ 'jquery' ], false, true );
			}

			if ( !empty( $args ) ) {
				$attr = [
					'page_per_posts' 	=> isset( $args['page_per_posts'] ) ? absint( $args['page_per_posts'] ) : 9,
					'time' 				=> isset( $args['time'] ) ? $args['time'] : 'upcoming',
					'order' 			=> isset( $args['order'] ) ? $args['order'] : 'ASC',
					'orderby' 			=> isset( $args['orderby'] ) ? $args['orderby'] : 'ovaev_start_date_time',
					'featured' 			=> isset( $args['featured'] ) ? $args['featured'] : '',
					'excl_category' 	=> isset( $args['excl_category'] ) ? $args['excl_category'] : '',
					'incl_category' 	=> isset( $args['incl_category'] ) ? $args['incl_category'] : '',
					'template' 			=> isset( $args['layout'] ) ? absint( $args['layout'] ) : 1,
					'column' 			=> isset( $args['column'] ) ? absint( $args['column'] ) : 3,	
					'pagination' 		=> isset( $args['pagination'] ) ? absint( $args['pagination'] ) : ''
				];
			} else {
				$attr = [
					'page_per_posts' 	=> 9,
					'time' 				=> 'upcoming',
					'order' 			=> 'ASC',
					'orderby' 			=> 'ovaev_start_date_time',
					'featured' 			=> '',
					'excl_category' 	=> '',
					'incl_category' 	=> '',
					'template' 			=> 1,
					'column' 			=> 3,
					'pagination' 		=> ''
				];
			}

			// Get data
			$data = $this->get_data_shortcode( $attr );
			
			// Get template
			$template = apply_filters( 'shortcode_ovaev_event_filter', 'shortcode/ovaev_event_filter.php' );

			ob_start();
			ovaev_get_template( $template, $data );
			return ob_get_clean();
		}
	}

	// init class
	new OVAEV_Shortcode_Event_Filter();
}
