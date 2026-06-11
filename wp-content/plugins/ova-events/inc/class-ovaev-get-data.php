<?php if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Class OVAEV_Get_Data
 */
if ( !class_exists( 'OVAEV_Get_Data' ) ) {

	class OVAEV_Get_Data {

		/**
		 * Constructor
		 */
		public function __construct() {
			// Event type
			add_filter( 'OVAEV_event_type', [ $this, 'event_type' ], 10, 1 );

			// Hook to wp_query in archive event
			add_action( 'pre_get_posts', [ $this, 'pre_get_archive_events' ] );

			// Hook to wp_query in search event
			add_action( 'pre_get_posts', [ $this, 'pre_get_search_events' ], 11 );
		}

		/**
		 * Event type
		 */
		public function event_type( $selected ) {
			$args = [
				'show_option_all'   => '' ,
				'show_option_none'   => esc_html__( 'All Categories', 'ovaev' ),
				'post_type'         => 'event',
				'post_status'       => 'publish',
				'posts_per_page'    => '-1',
				'option_none_value' => '',
				'orderby'           => 'ID',
				'order'             => 'ASC',
				'show_count'        => 0,
				'hide_empty'        => 0,
				'child_of'          => 0,
				'exclude'           => '',
				'include'           => '',
				'echo'              => 1,
				'selected'          => $selected,
				'hierarchical'      => 1,
				'name'              => 'ovaev_type',
				'id'                => '',
				'depth'             => 0,
				'tab_index'         => 0,
				'taxonomy'          => 'event_category',
				'hide_if_empty'     => false,
				'value_field'       => 'slug',
				'class' 			=> 'ovaev_type'
			];
			
			return wp_dropdown_categories( $args );
		}

		/**
		 * Get search events
		 */
		public function pre_get_search_events( $query ) {
			$search_event = isset( $_GET['search_event'] ) ? esc_html( $_GET['search_event'] ) : '';
			if ( '' != $search_event ) {
				// Post type
				$query->set( 'post_type', 'event' );
				
				// Get paged
				$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
				if ( $paged != '' ) {
					$query->set( 'paged', $paged );
				}

				// Get show past
				$show_past = OVAEV_Settings::ovaev_show_past();

				// Get order
				$order = OVAEV_Settings::archive_event_order();
				$query->set( 'order', $order );

				// Get orderby
				$orderby = OVAEV_Settings::archive_event_orderby();
				switch ( $orderby ) {
					case 'title':
						$query->set( 'orderby', 'title' );
						break;
					case 'event_custom_sort':
						$query->set( 'orderby' , 'meta_value_num' );
						$query->set( 'meta_key', $orderby );
						$query->set('meta_type', 'NUMERIC' );
						break;
					case 'ovaev_start_date':
						$query->set( 'orderby' , 'meta_value_num' );
						$query->set( 'meta_key', 'ovaev_start_date_time' );
						$query->set('meta_type', 'NUMERIC' );
						break;
					case 'ID':
						$query->set( 'orderby', 'ID');
						break;
				}

				// Get event type
				$event_type = isset( $_GET['ovaev_type'] ) ? esc_html( $_GET['ovaev_type'] ) : '';
				if ( $event_type ) {
					$query->set( 
						'tax_query',
						[
							[
								'taxonomy' => 'event_category',
								'field'    => 'slug',
								'terms'    => $event_type
							]
						]	
					);
				}

				// Start date
				$start_date = isset( $_GET['ovaev_start_date_search'] ) ? esc_html( $_GET['ovaev_start_date_search'] ) : '';

				// End date
				$end_date = isset( $_GET['ovaev_end_date_search'] ) ? esc_html( $_GET['ovaev_end_date_search'] ) : '';
				if ( $start_date && $end_date ) {
					$query->set( 
						'meta_query',
						[
							[
								'relation' => 'OR',
								[
									'key' 		=> 'ovaev_start_date_time',
									'value' 	=> [ strtotime( $start_date ) - 1, strtotime( $end_date ) + ( 24*60*60 ) + 1 ],
									'type' 		=> 'numeric',
									'compare' 	=> 'BETWEEN'
								],
								[
									'relation' 	=> 'AND',
									[
										'key' 		=> 'ovaev_start_date_time',
										'value' 	=> strtotime($start_date),
										'compare' 	=> '<'
									],
									[
										'key' 		=> 'ovaev_end_date_time',
										'value' 	=> strtotime( $start_date ),
										'compare' 	=> '>='
									]
								]
							]
						]
					);
				} elseif ( $start_date && !$end_date ) {
					$query->set(
						'meta_query',
						[
							[
								'relation' => 'OR',
								[
									'key' 		=> 'ovaev_start_date_time',
									'value' 	=> [ strtotime( $start_date ), strtotime( $start_date )+24*60*60 ],
									'compare' 	=> 'BETWEEN'
								],
								[
									'relation' 	=> 'AND',
									[
										'key' 		=> 'ovaev_start_date_time',
										'value' 	=> strtotime( $start_date ),
										'compare' 	=> '>='
									],
									[
										'key' 		=> 'ovaev_end_date_time',
										'value' 	=> strtotime( $start_date ),
										'compare' 	=> '>='
									]
								]
							]
						]
					);
				}
				elseif ( !$start_date && $end_date ) {
					$query->set(
						'meta_query',
						[
							'key' 		=> 'ovaev_end_date_time',
							'value' 	=> strtotime( $end_date )+(24*60*60),
							'compare' 	=> '<='
						]
					);
				}

				remove_action( 'pre_get_posts', [ $this, 'pre_get_search_events' ] );
			}
		}
		
		/**
		 * Get archive events
		 */
		public function pre_get_archive_events( $query ) {
			if ( ( is_post_type_archive( 'event' )  && !is_admin() ) || ( is_tax( 'event_category' ) && !is_admin() ) || ( is_tax( 'event_tag' ) && !is_admin() ) ) {
				// Post type
				$query->set( 'post_type', 'event' );
				
				// Show past
				$show_past = OVAEV_Settings::ovaev_show_past();
				if ( $show_past == 'no' ) {
					$query->set(
						'meta_query',
						[
							[
								'key' 		=> 'ovaev_end_date_time',
								'value' 	=> current_time( 'timestamp' ),
								'compare' 	=> '>'
							]
						]
					);
				}

				// Get paged
				$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
				if ( '' != $paged ) {
					$query->set( 'paged', $paged );
				}
				
				// Get posts per page
	            $posts_per_page  = OVAEV_Settings::archive_event_posts_per_page();
				$query->set( 'posts_per_page', $posts_per_page );

				// Get order
				$order = OVAEV_Settings::archive_event_order();
				$query->set( 'order', $order );

				// Orderby
				$orderby = OVAEV_Settings::archive_event_orderby();
				switch ( $orderby ) {
					case 'title':
						$query->set( 'orderby', 'title' );
						break;
					case 'event_custom_sort':
						$query->set( 'orderby' , 'meta_value_num' );
						$query->set( 'meta_key', $orderby );
						$query->set('meta_type', 'NUMERIC' );
						break;
					case 'ovaev_start_date':
						$query->set( 'orderby' , 'meta_value_num' );
						$query->set( 'meta_key', 'ovaev_start_date_time' );
						$query->set('meta_type', 'NUMERIC' );
						break;
					case 'ID':
						$query->set( 'orderby', 'ID');
						break;
					default:
						// do something
				}

				remove_action( 'pre_get_posts', [ $this, 'pre_get_archive_events' ] );
			}
		}

		/**
		 * Get events simple calendar
		 */
		public static function get_events_simple_calendar( $category, $filter_event ) {
			if ( !$category ) return [];

			// Base query
			$args_base = [
				'post_type' 	 => 'event',
				'post_status' 	 => 'publish',
				'orderby'		 => 'id',
				'order'			 => 'ASC',
				'posts_per_page' => '-1'
			];

			if ( $category != 'all' ) {
				$args_base = [
					'post_type' 		=> 'event',
					'post_status' 		=> 'publish',
					'orderby'			=> 'id',
					'order'				=> 'ASC',
					'posts_per_page' 	=> '-1',
					'tax_query' 		=> [
						[
							'taxonomy' => 'event_category',
							'field'    => 'slug',
							'terms'    => $category
						]
					]
				];	
			}

			// Filter event
			if ( 'past_event' == $filter_event ) {
				$args_base['meta_query'] = [
					[
						'key' 		=> 'ovaev_end_date_time',
						'value' 	=> current_time( 'timestamp' ),
						'compare' 	=> '<',
						'type' 		=> 'NUMERIC'
					]
				];
			} elseif ( 'upcoming_event' == $filter_event ) {
				$args_base['meta_query'] = [
					[
						'key' 		=> 'ovaev_start_date_time',
						'value' 	=> current_time( 'timestamp' ),
						'compare' 	=> '>',
						'type' 		=> 'NUMERIC'
					]
				];
			} else {
				if ( 'special_event' == $filter_event ) {
					$args_base['meta_query'] = [
						[
							'key' 		=> 'ovaev_special',
							'value' 	=> 'checked',
							'compare' 	=> '='
						]
					];
				}
			}

			// List event
			$list_event = array();

			// Get events
			$events = new WP_Query( $args_base );
			if ( $events->have_posts() ) : while ( $events->have_posts() ) : $events->the_post();
				$id 		= get_the_id();
				$start_date = get_post_meta( $id, 'ovaev_start_date', true );
				$end_date 	= get_post_meta( $id, 'ovaev_end_date', true );	
				$item 		= [
					'endDate' 	=> date( 'Y-m-d', strtotime( $end_date ) ) ,
					'startDate' => date( 'Y-m-d', strtotime( $start_date ) ) ,
					'url' 		=> get_post_type_archive_link( 'event' ).'?ovaev_start_date_search='.$start_date.'&ovaev_end_date_search=&ovaev_type=&post_type=event&search_event=search-event'
				];

		   		array_push( $list_event, $item );
			endwhile; endif; wp_reset_postdata();

		    return json_encode( $list_event );
		}

		/**
		 * Get events calendar
		 */
		public static function get_events_calendar( $category, $filter_event ) {
			if ( !$category ) return [];

			// Base query
			$args_base = [
				'post_type' 	 => 'event',
				'post_status' 	 => 'publish',
				'orderby'		 => 'id',
				'order'			 => 'ASC',
				'posts_per_page' => '-1',
			];
			
			if ( 'all' != $category ) {
				$args_base = [
					'post_type' 		=> 'event',
					'post_status' 		=> 'publish',
					'orderby'			=> 'id',
					'order'				=> 'ASC',
					'posts_per_page' 	=> '-1',
					'tax_query' 		=> [
						[
							'taxonomy' => 'event_category',
							'field'    => 'slug',
							'terms'    => $category
						]
					]
				];	
			}

			// Filter event
			if ( 'past_event' == $filter_event ) {
				$args_base['meta_query'] = [
					[
						'key' 		=> 'ovaev_end_date_time',
						'value' 	=> current_time( 'timestamp' ),
						'compare' 	=> '<'
					]
				];
			} elseif ( $filter_event == 'upcoming_event' ) {
				$args_base['meta_query'] = [
					[
						'key' 		=> 'ovaev_end_date_time',
						'value' 	=> current_time( 'timestamp' ),
						'compare' 	=> '>'
					]
				];
			} else {
				if ( $filter_event == 'special_event' ) {
					$args_base['meta_query'] = [
						[
							'key' 		=> 'ovaev_special',
							'value' 	=> 'checked',
							'compare' 	=> '='
						]
					];
				}
			}

			// List event
			$list_event = [];

			// Get events
			$events = new WP_Query( $args_base );
			if ( $events->have_posts() ) : while ( $events->have_posts() ) : $events->the_post();
				$id 				= get_the_id();
				$start_date 		= get_post_meta( $id, 'ovaev_start_date', true );
				$end_date 			= get_post_meta( $id, 'ovaev_end_date', true );	
				$special_event 		= get_post_meta( $id, 'ovaev_special', true );	
				$time_format 		= OVAEV_Settings::archive_event_format_time();
				$ovaev_start_date 	= get_post_meta( $id, 'ovaev_start_date_time', true );
				$ovaev_end_date   	= get_post_meta( $id, 'ovaev_end_date_time', true );
				$date_start    		= $ovaev_start_date != '' ? date_i18n( get_option( 'date_format' ), $ovaev_start_date ) : '';
				$date_end      		= $ovaev_end_date != '' ? date_i18n( get_option( 'date_format' ), $ovaev_end_date ) : '';
				$time_start    		= $ovaev_start_date != '' ? date_i18n( 'H:i', $ovaev_start_date ) : '';
				$time_end      		= $ovaev_end_date != '' ? date_i18n( 'H:i', $ovaev_end_date ) : '';

				$item = [
					'start' 	=> date( 'Y-m-d', strtotime( $start_date ) ) .' '.$time_start,
					'end' 		=> date( 'Y-m-d', strtotime( $end_date ) ) .' '.$time_end,
					'url' 		=> get_the_permalink(),
					'title' 	=> get_the_title(),
					'desc' 		=> '<a href='.get_the_permalink().'>'.get_the_post_thumbnail().'</a>'
									.'<p><a href='.get_the_permalink().'>'.get_the_title().'</a></p>',
					'special' 	=> $special_event
				];

				array_push( $list_event, $item );
			endwhile; endif; wp_reset_postdata();
			
		    return json_encode( $list_event );
		}
	}

	// init class
	new OVAEV_Get_Data();
}