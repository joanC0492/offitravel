<?php if ( !defined( 'ABSPATH' ) ) exit();

/**
 * Class OVAEV_Loadmore
 */
if ( !class_exists( 'OVAEV_Loadmore' ) ) {

	class OVAEV_Loadmore {

		/**
		 * Constructor
		 */
		public function __construct() {
			// Filter elementor grid
			add_action( 'wp_ajax_filter_elementor_grid', [ $this, 'filter_elementor_grid' ] );
			add_action( 'wp_ajax_nopriv_filter_elementor_grid', [ $this, 'filter_elementor_grid' ] );

			// Search ajax events
			add_action( 'wp_ajax_search_ajax_events', [ $this, 'search_ajax_events' ] );
			add_action( 'wp_ajax_nopriv_search_ajax_events', [ $this, 'search_ajax_events' ] );

			// Ajax events pagination
			add_action( 'wp_ajax_search_ajax_events_pagination', [ $this, 'search_ajax_events_pagination' ] );
			add_action( 'wp_ajax_nopriv_search_ajax_events_pagination', [ $this, 'search_ajax_events_pagination' ] );

			// Filter ajax
			add_action( 'wp_ajax_ovaev_filter_ajax', [ $this, 'ovaev_filter_ajax' ] );
			add_action( 'wp_ajax_nopriv_ovaev_filter_ajax', [ $this, 'ovaev_filter_ajax' ] );

			// Category filter ajax
			add_action( 'wp_ajax_ovaev_category_filter_ajax', [ $this, 'ovaev_category_filter_ajax' ] );
			add_action( 'wp_ajax_nopriv_ovaev_category_filter_ajax', [ $this, 'ovaev_category_filter_ajax' ] );
		}

		/**
		 * Ajax Load Post Click Elementor
		 */
		public static function filter_elementor_grid() {
			// Get filter
			$filter = sanitize_text_field( $_POST['filter'] );

			// Get order
			$order = sanitize_text_field( $_POST['order'] );

			// Get orderby
			$orderby = sanitize_text_field( $_POST['orderby'] );

			// Get number post
			$number_post = sanitize_text_field( $_POST['number_post'] );

			// Get column
			$column = isset( $_POST['column'] ) ? sanitize_text_field( $_POST['column'] ) : 1;

			// Get first term
			$first_term = sanitize_text_field( $_POST['first_term'] );

			// Get term id
			$term_id = sanitize_text_field( $_POST['term_id_filter_string'] );

			// Show featured
			$show_featured = sanitize_text_field( $_POST['show_featured'] );

			// Get layout
			$layout = sanitize_text_field( $_POST['layout'] );

			// Slide options
			$slide_options = isset( $_POST['slide_options'] ) ? $_POST['slide_options'] : '';

			// Base query
			$args_base = [
				'post_type' 		=> 'event',
				'post_status' 		=> 'publish',
				'posts_per_page' 	=> $number_post,
				'order' 			=> $order,
				'orderby' 			=> $orderby
			];

			// Term id filter
			$term_id_filter = explode( ', ', $term_id );

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

			if ( $filter != 'all' ) {
				$args_cat = [
					'tax_query' => [
						[
							'taxonomy' => 'event_category',
							'field'    => 'id',
							'terms'    => $filter
						]
					]
				];

				$args = array_merge_recursive( $args_cat, $args_base, $args_featured );
				$my_posts = new WP_Query( $args );
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

				$args 		= array_merge_recursive($args_base, $args_cat, $args_featured);
				$my_posts 	= new WP_Query( $args );
			}

			// Loop
			if ( $my_posts->have_posts() ): ?>
				<div class="swiper swiper-loading">
					<div class="swiper-wrapper">
						<?php while( $my_posts->have_posts() ) : $my_posts->the_post();
						$id = get_the_ID();

						// Get event category
						$ovaev_cat = get_the_terms( $id, 'event_category' );

						// Category name
						$cat_name = [];
						if ( $ovaev_cat != '' ) {
							foreach ( $ovaev_cat as $key => $value ) {
								$cat_name[] = $value->name;
							}
						}

						$category_name = join( ', ', $cat_name );
					?>
						<div class="swiper-slide">
							<?php if ( !empty( $layout ) && $layout == 1 ) {
								ovaev_get_template( 'event-templates/event-type1.php' );
							} else {
								ovaev_get_template( 'event-templates/event-type3.php' );
							} ?>
						</div>
					<?php endwhile; ?>
					</div>
				</div>
				<?php if ( isset( $slide_options['nav'] ) && $slide_options['nav'] ): ?>
					<div class="button-nav button-prev">
						<i class="<?php echo esc_attr( $slide_options['nav_prev'] ); ?>" aria-hidden="true"></i>
					</div>
					<div class="button-nav button-next">
						<i class="<?php echo esc_attr( $slide_options['nav_next'] ); ?>" aria-hidden="true"></i>
					</div>
				<?php endif;
			endif; wp_reset_postdata();
			wp_die();
		}

		/**
		 * Ajax Search Events Elementor
		 */
		public static function search_ajax_events() {
			$start_date = isset( $_POST['start_date'] ) ? sanitize_text_field( $_POST['start_date'] ) 	: '';
			$end_date 	= isset( $_POST['end_date'] ) 	? sanitize_text_field( $_POST['end_date'] ) 	: '';
			$category 	= isset( $_POST['category'] ) 	? sanitize_text_field( $_POST['category'] ) 	: '';
			$layout 	= isset( $_POST['layout'] ) 	? sanitize_text_field( $_POST['layout'] ) 		: 1;
			$column 	= isset( $_POST['column'] ) 	? sanitize_text_field( $_POST['column'] ) 		: 'col3';
			$per_page 	= isset( $_POST['per_page'] ) 	? sanitize_text_field( $_POST['per_page'] ) 	: 6;
			$order 		= isset( $_POST['order'] ) 		? sanitize_text_field( $_POST['order'] ) 		: 'ASC';
			$orderby 	= isset( $_POST['orderby'] ) 	? sanitize_text_field( $_POST['orderby'] ) 		: 'title';
			$cat_slug 	= isset( $_POST['cat_slug'] ) 	? sanitize_text_field( $_POST['cat_slug'] ) 	: 'all';
			$time_event = isset( $_POST['time_event'] ) ? sanitize_text_field( $_POST['time_event'] ) 	: 'all';

			// Args base
			$args = [
				'post_type' 		=> 'event',
				'post_status' 		=> 'publish',
				'posts_per_page' 	=> $per_page,
				'order' 			=> $order,
				'offset'			=> 0
			];

			// Date
			if ( $start_date && $end_date ) {
				$args['meta_query'] = [
					[
						'relation' => 'OR',
						[
							'key' 		=> 'ovaev_start_date_time',
							'value' 	=> array( strtotime( $start_date )-1, strtotime( $end_date ) + ( 24*60*60 ) + 1 ),
							'type' 		=> 'numeric',
							'compare' 	=> 'BETWEEN'
						],
						[
							'relation' 	=> 'AND',
							[
								'key' 		=> 'ovaev_start_date_time',
								'value' 	=> strtotime( $start_date ),
								'compare' 	=> '<'
							],
							[
								'key' 		=> 'ovaev_end_date_time',
								'value' 	=> strtotime( $start_date ),
								'compare' 	=> '>='
							]
						]
					]
				];
			} elseif ( $start_date && ! $end_date ) {
				$args['meta_query'] = [
					[
						'relation' => 'OR',
						[
							'key' 		=> 'ovaev_start_date_time',
							'value' 	=> [ strtotime( $start_date ), strtotime( $start_date ) + 24*60*60 ],
							'compare' 	=> 'BETWEEN'
						],
						[
							'relation' 	=> 'AND',
							[
								'key' 		=> 'ovaev_start_date_time',
								'value' 	=> strtotime( $start_date ),
								'compare' 	=> '<'
							],
							[
								'key' 		=> 'ovaev_end_date_time',
								'value' 	=> strtotime( $start_date ),
								'compare' 	=> '>='
							]
						]
					]
				];
			} elseif ( ! $start_date && $end_date ) {
				$args['meta_query'] = [
					'key' 		=> 'ovaev_end_date_time',
					'value' 	=> strtotime( $end_date ) + ( 24*60*60 ),
					'compare' 	=> '<='
				];
			} else {
				// Time event
		        if ( 'current' === $time_event ) {
		        	$args['meta_query'] = [
		        		[
		                	'relation' => 'OR',
		                    [
		                    	'key'     => 'ovaev_start_date_time',
		                        'value'   => array( current_time('timestamp' )-1, current_time('timestamp' )+(24*60*60)+1 ),
		                        'type'    => 'numeric',
		                        'compare' => 'BETWEEN'  
		                    ],
		                    [
		                    	'relation' => 'AND',
		                        [
		                        	'key'     => 'ovaev_start_date_time',
		                            'value'   => current_time('timestamp' ),
		                            'compare' => '<'
		                        ],
		                        [
		                        	'key'     => 'ovaev_end_date_time',
		                            'value'   => current_time('timestamp' ),
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
			}

			// Orderby
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

			// category
			if ( $category ) {
				$args['tax_query'] = [
					[
						'taxonomy' => 'event_category',
	                    'field'    => 'slug',
	                    'terms'    => $category
					]
				];
	        } else {
	        	if ( 'all' != $cat_slug ) {
	        		$args['tax_query'] = [
	        			[
							'taxonomy' => 'event_category',
		                    'field'    => 'slug',
		                    'terms'    => $cat_slug
						]
	        		];
	        	}
	        }

	        // Get events
	        $events = new \WP_Query( $args );
	        ob_start();

        	?>
			<div class="archive_event search-ajax-content<?php echo ' '.esc_attr( $column ); ?>">
				<?php if ( $events->have_posts() ) : while( $events->have_posts() ) : $events->the_post();
					switch ( $layout ) {
						case '1':
							ovaev_get_template( 'event-templates/event-type1.php' );
							break;
						case '2':
							ovaev_get_template( 'event-templates/event-type2.php' );
							break;
						case '3':
							ovaev_get_template( 'event-templates/event-type3.php' );
							break;
						case '4':
							ovaev_get_template( 'event-templates/event-type4.php' );
							break;
						case '5':
							ovaev_get_template( 'event-templates/event-type5.php' );
							break;
						case '6':
							ovaev_get_template( 'event-templates/event-type6.php' );
							break;
						default:
							ovaev_get_template( 'event-templates/event-type1.php' );
					}
				endwhile; else: ?>
					<div class="search_not_found">
						<?php esc_html_e( 'Not Found Events', 'ovaev' ); ?>
					</div>
				<?php endif; wp_reset_postdata(); ?>
				<div class="wrap_loader">
					<svg class="loader" width="50" height="50">
						<circle cx="25" cy="25" r="10" stroke="#a1a1a1"/>
						<circle cx="25" cy="25" r="20" stroke="#a1a1a1"/>
					</svg>
				</div>
			</div>
			<div class="data-events" 
				data-layout="<?php echo esc_attr( $layout ); ?>" 
				data-column="<?php echo esc_attr( $column ); ?>" 
				data-per-page="<?php echo esc_attr( $per_page ); ?>" 
				data-order="<?php echo esc_attr( $order ); ?>" 
				data-orderby="<?php echo esc_attr( $orderby ); ?>" 
				data-category-slug="<?php echo esc_attr( $cat_slug ); ?>" 
				data-time-event="<?php echo esc_attr( $time_event ); ?>">
			</div>
        	<?php

        	$result = ob_get_contents(); 
			ob_end_clean();

			// Pagination
			$total_pages = $events->max_num_pages;
			$current 	 = 1;
			ob_start();

			if ( $total_pages > 1 ): ?>
				<div class="search-ajax-pagination" data-total-page="<?php echo esc_attr( $total_pages ); ?>">
					<ul>
						<?php for ( $i = 1; $i <= $total_pages; $i++ ):
							if ( $i == 1 ): ?>
								<li>
									<span class="prev page-numbers" data-paged="<?php echo esc_attr( $current - 1 ); ?>">
										<?php esc_html_e( 'Previous', 'ovaev' ); ?>
									</span>
								</li>
								<li>
									<span class="page-numbers current" data-paged="<?php echo esc_attr( $i ); ?>">
										<?php echo esc_attr( $i ); ?>
									</span>
								</li>
							<?php elseif ( $i == $total_pages ): ?>
								<li>
									<span class="page-numbers" data-paged="<?php echo esc_attr( $i ); ?>">
										<?php echo esc_attr( $i ); ?>
									</span>
								</li>
								<li>
									<span class="next page-numbers" data-paged="<?php echo esc_attr( $current + 1 ); ?>">
										<?php esc_html_e( 'Next', 'ovaev' ); ?>
									</span>
								</li>
							<?php else: ?>
								<li>
									<span class="page-numbers" data-paged="<?php echo esc_attr( $i ); ?>">
										<?php echo esc_attr( $i ); ?>
									</span>
								</li>
							<?php endif;
						endfor; ?>
					</ul>
				</div>
			<?php endif;

			$pagination = ob_get_contents();
			ob_end_clean();

			echo json_encode([
				'result' 		=> $result,
				'pagination' 	=> $pagination
			]);
			wp_die();
		}

		/**
		 * Ajax Search Events Pagination
		 */
		public function search_ajax_events_pagination() {
			$start_date = isset( $_POST['start_date'] ) ? sanitize_text_field( $_POST['start_date'] ) 	: '';
			$end_date 	= isset( $_POST['end_date'] ) 	? sanitize_text_field( $_POST['end_date'] ) 	: '';
			$category 	= isset( $_POST['category'] ) 	? sanitize_text_field( $_POST['category'] ) 	: '';
			$layout 	= isset( $_POST['layout'] ) 	? sanitize_text_field( $_POST['layout'] ) 		: 1;
			$column 	= isset( $_POST['column'] ) 	? sanitize_text_field( $_POST['column'] ) 		: 'col3';
			$per_page 	= isset( $_POST['per_page'] ) 	? sanitize_text_field( $_POST['per_page'] ) 	: 6;
			$order 		= isset( $_POST['order'] ) 		? sanitize_text_field( $_POST['order'] ) 		: 'ASC';
			$orderby 	= isset( $_POST['orderby'] ) 	? sanitize_text_field( $_POST['orderby'] ) 		: 'title';
			$cat_slug 	= isset( $_POST['cat_slug'] ) 	? sanitize_text_field( $_POST['cat_slug'] ) 	: 'all';
			$time_event = isset( $_POST['time_event'] ) ? sanitize_text_field( $_POST['time_event'] ) 	: 'all';
			$offset 	= isset( $_POST['offset'] ) 	? sanitize_text_field( $_POST['offset'] ) 		: 0;

			// Args base
			$args = [
				'post_type' 		=> 'event',
				'post_status' 		=> 'publish',
				'posts_per_page' 	=> $per_page,
				'order' 			=> $order,
				'paged' 			=> $offset,
				'offset'			=> ( $offset -1 ) * $per_page
			];

			// Date
			if ( $start_date && $end_date ) {
				$args['meta_query'] = [
					[
						'relation' => 'OR',
						[
							'key' 		=> 'ovaev_start_date_time',
							'value' 	=> [ strtotime( $start_date )-1, strtotime( $end_date ) + ( 24*60*60 ) + 1 ],
							'type' 		=> 'numeric',
							'compare' 	=> 'BETWEEN'
						],
						[
							'relation' 	=> 'AND',
							[
								'key' 		=> 'ovaev_start_date_time',
								'value' 	=> strtotime( $start_date ),
								'compare' 	=> '<'
							],
							[
								'key' 		=> 'ovaev_end_date_time',
								'value' 	=> strtotime( $start_date ),
								'compare' 	=> '>='
							]
						]
					]
				];
			} elseif ( $start_date && !$end_date ) {
				$args['meta_query'] = [
					[
						'relation' => 'OR',
						[
							'key' 		=> 'ovaev_start_date_time',
							'value' 	=> [ strtotime( $start_date ), strtotime( $start_date ) + 24*60*60 ],
							'compare' 	=> 'BETWEEN'
						],
						[
							'relation' 	=> 'AND',
							[
								'key' 		=> 'ovaev_start_date_time',
								'value' 	=> strtotime( $start_date ),
								'compare' 	=> '<'
							],
							[
								'key' 		=> 'ovaev_end_date_time',
								'value' 	=> strtotime( $start_date ),
								'compare' 	=> '>='
							]
						]
					]
				];
			} elseif ( ! $start_date && $end_date ) {
				$args['meta_query'] = [
					'key' 		=> 'ovaev_end_date_time',
					'value' 	=> strtotime( $end_date ) + ( 24*60*60 ),
					'compare' 	=> '<='
				];
			} else {
				// Time event
		        if ( 'current' === $time_event ) {
		        	$args['meta_query'] = [
		        		[
		        			'relation' => 'OR',
		                    [
		                    	'key'     => 'ovaev_start_date_time',
		                        'value'   => [ current_time( 'timestamp' )-1, current_time( 'timestamp' )+(24*60*60)+1 ],
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
			}

			// Orderby
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
				default:
					// do something
			}

			// category
			if ( $category ) {
				$args['tax_query'] = [
					[
						'taxonomy' => 'event_category',
	                    'field'    => 'slug',
	                    'terms'    => $category
					]
				];
	        } else {
	        	if ( 'all' != $cat_slug ) {
	        		$args['tax_query'] = [
	        			[
	        				'taxonomy' => 'event_category',
		                    'field'    => 'slug',
		                    'terms'    => $cat_slug
	        			]
	        		];
	        	}
	        }

	        // Get events
	        $events = new \WP_Query( $args );
	        ob_start();

        	?>
			<div class="archive_event search-ajax-content<?php echo ' '.esc_attr( $column ); ?>">
				<?php if ( $events->have_posts() ) : while( $events->have_posts() ) : $events->the_post();
					switch ( $layout ) {
						case '1':
							ovaev_get_template( 'event-templates/event-type1.php' );
							break;
						case '2':
							ovaev_get_template( 'event-templates/event-type2.php' );
							break;
						case '3':
							ovaev_get_template( 'event-templates/event-type3.php' );
							break;
						case '4':
							ovaev_get_template( 'event-templates/event-type4.php' );
							break;
						case '5':
							ovaev_get_template( 'event-templates/event-type5.php' );
							break;
						case '6':
							ovaev_get_template( 'event-templates/event-type6.php' );
							break;
						default:
							ovaev_get_template( 'event-templates/event-type1.php' );
					}
				?>

				<?php endwhile; else: wp_reset_postdata(); ?>
					<div class="search_not_found">
						<?php esc_html_e( 'No events found.', 'ovaev' ); ?>
					</div>
				<?php endif; wp_reset_postdata(); ?>

				<div class="wrap_loader">
					<svg class="loader" width="50" height="50">
						<circle cx="25" cy="25" r="10" stroke="#a1a1a1"/>
						<circle cx="25" cy="25" r="20" stroke="#a1a1a1"/>
					</svg>
				</div>
			</div>
			<div class="data-events" 
				data-layout="<?php echo esc_attr( $layout ); ?>" 
				data-column="<?php echo esc_attr( $column ); ?>" 
				data-per-page="<?php echo esc_attr( $per_page ); ?>" 
				data-order="<?php echo esc_attr( $order ); ?>" 
				data-orderby="<?php echo esc_attr( $orderby ); ?>" 
				data-category-slug="<?php echo esc_attr( $cat_slug ); ?>" 
				data-time-event="<?php echo esc_attr( $time_event ); ?>">
			</div>
        	<?php

        	$result = ob_get_contents(); 
			ob_end_clean();

			echo json_encode([
				'result' => $result
			]);
			wp_die();
		}

		/**
		 * Filter Event Ajax
		 */
		public static function ovaev_filter_ajax() {
			$settings 	= isset( $_POST['settings'] ) ? $_POST['settings'] : '';
			$start_date = isset( $_POST['start_date'] ) && $_POST['start_date'] ? strtotime( $_POST['start_date'] ) : '';
			$end_date 	= isset( $_POST['end_date'] ) && $_POST['start_date'] ? strtotime( $_POST['end_date'] . ' 23:59' ) : '';
			$keyword 	= isset( $_POST['keyword'] ) ? sanitize_text_field( $_POST['keyword'] ) : '';
			$time 		= isset( $_POST['time'] ) ? sanitize_text_field( $_POST['time'] ) : '';
			$categories = isset( $_POST['categories'] ) ? $_POST['categories'] : '';

			// Base
			$args_base = [
				'post_type' 		=> 'event',
				'post_status' 		=> 'publish',
				'posts_per_page' 	=> $settings['page_per_posts'],
				'order' 			=> $settings['order']
			];

			// Sort
			if ( $settings['orderby'] === 'ovaev_start_date_time' || $settings['orderby'] === 'event_custom_sort' ) {
		        $args_base['meta_key'] 	= $settings['orderby'];
		        $args_base['orderby'] 	= 'meta_value_num';
		        $args_base['meta_type'] = 'NUMERIC';
		    } else {
		        $args_base['orderby'] = $settings['orderby'];
		    }

		    // Keyword
		    if ( $keyword ) {
		    	$args_base['s'] = $keyword;
		    }

		    // Date
		    $args_date = [];
		    if ( $start_date && $end_date ) {
		    	$args_date = [
		    		'meta_query' => [
						'relation' => 'AND',
	                    [
	                    	'key'     => 'ovaev_start_date_time',
                            'value'   => $start_date,
                            'compare' => '>'
	                    ],
                        [
                        	'key'     => 'ovaev_end_date_time',
                            'value'   => $end_date,
                            'compare' => '<='
                        ]
					]
		    	];
		    } elseif ( $start_date && ! $end_date ) {
		    	$args_date = [
		    		'meta_query' => [
		    			[
		    				'key'     => 'ovaev_start_date_time',
                            'value'   => $start_date,
                            'compare' => '>='
		    			]
		    		]
		    	];
		    } elseif ( !$start_date && $end_date ) {
		    	$args_date = [
		    		'meta_query' => [
		    			[
                        	'key'     => 'ovaev_end_date_time',
                            'value'   => $end_date,
                            'compare' => '<='
                        ]
		    		]
		    	];
		    } else {
		    	$args_date = [];
		    }

		    // Time
			$args_time = [];
			if ( $time === 'today' ) {
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
			} elseif ( $time === 'week' ) {
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
			} elseif ( $time === 'weekend' ) {
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
			} elseif ( $time === 'upcoming' ) {
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

			// Category in
			$args_category = [];
			if ( !empty( $categories ) && is_array( $categories ) ) {
				$args_category = [
					'tax_query' => [
						[
							'taxonomy' => 'event_category',
							'field'    => 'term_id',
							'terms'    => $categories,
							'operator' => 'IN'
						]
					]
				];
			}

			$query 	= array_merge_recursive( $args_base, $args_date, $args_time, $args_category );
			$events = new \WP_Query( $query );

			$incl 		= $settings['incl_category'] ? explode( ",", $settings['incl_category'] ) : [];
			$excl 		= $settings['excl_category'] ? explode( ",", $settings['excl_category'] ) : [];
			$categories = ovaev_get_categories_events( $events, $incl, $excl );

			// Events
	        ob_start();
        	?>
        		<div class="archive_event ovaev-filter-column<?php echo esc_attr( $settings['column'] );?>">
					<?php if ( $events->have_posts() ) : while(  $events->have_posts() ) : $events->the_post();
						switch ( $settings['template'] ) {
							case '1':
								ovaev_get_template( 'event-templates/event-type1.php' );
								break;
							case '2':
								ovaev_get_template( 'event-templates/event-type2.php' );
								break;
							case '3':
								ovaev_get_template( 'event-templates/event-type3.php' );
								break;
							case '4':
								ovaev_get_template( 'event-templates/event-type4.php' );
								break;
							case '5':
								ovaev_get_template( 'event-templates/event-type5.php' );
								break;
							case '6':
								ovaev_get_template( 'event-templates/event-type6.php' );
								break;
							default:
								ovaev_get_template( 'event-templates/event-type1.php' );
						}
					endwhile; else: ?>
						<div class="search_not_found">
							<?php esc_html_e( 'No events found.', 'ovaev' ); ?>
						</div>
					<?php endif; wp_reset_postdata(); ?>
				</div>
        	<?php

        	$result = ob_get_contents(); 
			ob_end_clean();
			
			// Categories
	        ob_start();
	        
	        if ( !empty( $categories ) && is_array( $categories ) ):
	        	foreach ( $categories as $item_cat ): ?>
	        		<li class="item-cat">
						<?php if ( $item_cat['icon_class'] ): ?>
							<i class="<?php echo esc_attr( $item_cat['icon_class'] ); ?>" aria-hidden="true"></i>
						<?php endif; ?>
						<a href="javascript:void(0)" class="ovaev-term" data-term-id="<?php echo esc_attr( $item_cat['term_id'] ); ?>">
							<?php echo esc_html( $item_cat['name'] ); ?>
							<span class="count">
								<?php printf( esc_html__( '(%s)' ), $item_cat['count'] ); ?>
							</span>	
						</a>
					</li>
	        	<?php endforeach;
	        endif;

	        $category = ob_get_contents(); 
			ob_end_clean();

			echo json_encode([
				'result' 	=> $result,
				'category' 	=> $category
			]);
			wp_die();
		}

		/**
		 * Category Filter Event Ajax
		 */
		public static function ovaev_category_filter_ajax() {
			$settings 	= isset( $_POST['settings'] ) ? $_POST['settings'] : '';
			$start_date = isset( $_POST['start_date'] ) && $_POST['start_date'] ? strtotime( $_POST['start_date'] ) : '';
			$end_date 	= isset( $_POST['end_date'] ) && $_POST['start_date'] ? strtotime( $_POST['end_date'] . ' 23:59' ) : '';
			$keyword 	= isset( $_POST['keyword'] ) ? sanitize_text_field( $_POST['keyword'] ) : '';
			$time 		= isset( $_POST['time'] ) ? sanitize_text_field( $_POST['time'] ) : '';
			$categories = isset( $_POST['categories'] ) ? $_POST['categories'] : '';

			// Base
			$args_base = [
				'post_type' 		=> 'event',
				'post_status' 		=> 'publish',
				'posts_per_page' 	=> $settings['page_per_posts'],
				'order' 			=> $settings['order']
			];

			// Sort
			if ( $settings['orderby'] === 'ovaev_start_date_time' || $settings['orderby'] === 'event_custom_sort' ) {
		        $args_base['meta_key'] 	= $settings['orderby'];
		        $args_base['orderby'] 	= 'meta_value_num';
		        $args_base['meta_type'] = 'NUMERIC';
		    } else {
		        $args_base['orderby'] = $settings['orderby'];
		    }

		    // Keyword
		    if ( $keyword ) {
		    	$args_base['s'] = $keyword;
		    }

		    // Date
		    $args_date = [];
		    if ( $start_date && $end_date ) {
		    	$args_date = [
		    		'meta_query' => [
		    			'relation' => 'AND',
	                    [
	                    	'key'     => 'ovaev_start_date_time',
                            'value'   => $start_date,
                            'compare' => '>'
	                    ],
                        [
                        	'key'     => 'ovaev_end_date_time',
                            'value'   => $end_date,
                            'compare' => '<='
                        ]
		    		]
		    	];
		    } elseif ( $start_date && !$end_date ) {
		    	$args_date = [
		    		'meta_query' => [
		    			[
		    				'key'     => 'ovaev_start_date_time',
                            'value'   => $start_date,
                            'compare' => '>='
		    			]
		    		]
		    	];
		    } elseif ( !$start_date && $end_date ) {
		    	$args_date = [
		    		'meta_query' => [
		    			[
                        	'key'     => 'ovaev_end_date_time',
                            'value'   => $end_date,
                            'compare' => '<='
                        ]
		    		]
		    	];
		    } else {
		    	$args_date = [];
		    }

		    // Time
			$args_time = [];
			if ( $time === 'today' ) {
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
			} elseif ( $time === 'week' ) {
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
			} elseif ( $time === 'weekend' ) {
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
			} elseif ( $time === 'upcoming' ) {
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

			// Category in
			$args_category = [];
			if ( !empty( $categories ) && is_array( $categories ) ) {
				$args_category = [
					'tax_query' => [
						[
							'taxonomy' => 'event_category',
							'field'    => 'term_id',
							'terms'    => $categories,
							'operator' => 'IN'
						]
					]
				];
			}

			$query 	= array_merge_recursive( $args_base, $args_date, $args_time, $args_category );
			$events = new \WP_Query( $query );

			// Events
	        ob_start();
        	?>
        		<div class="archive_event ovaev-filter-column<?php echo esc_attr( $settings['column'] );?>">
					<?php if ( $events->have_posts() ) : while(  $events->have_posts() ) : $events->the_post();
						switch ( $settings['template'] ) {
							case '1':
								ovaev_get_template( 'event-templates/event-type1.php' );
								break;
							case '2':
								ovaev_get_template( 'event-templates/event-type2.php' );
								break;
							case '3':
								ovaev_get_template( 'event-templates/event-type3.php' );
								break;
							case '4':
								ovaev_get_template( 'event-templates/event-type4.php' );
								break;
							case '5':
								ovaev_get_template( 'event-templates/event-type5.php' );
								break;
							case '6':
								ovaev_get_template( 'event-templates/event-type6.php' );
								break;
							default:
								ovaev_get_template( 'event-templates/event-type1.php' );
						}
					endwhile; else: wp_reset_postdata(); ?>
						<div class="search_not_found">
							<?php esc_html_e( 'No events found.', 'ovaev' ); ?>
						</div>
					<?php endif; wp_reset_postdata(); ?>
				</div>
        	<?php

        	$result = ob_get_contents(); 
			ob_end_clean();

			echo json_encode([ 'result' => $result ]);
			wp_die();
		}
	}

	// init class
	new OVAEV_Loadmore();
}