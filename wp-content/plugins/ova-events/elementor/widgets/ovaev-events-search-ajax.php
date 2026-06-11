<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class OVAEV_Elementor_Events_Search_Ajax
 */
if ( !class_exists( 'OVAEV_Elementor_Events_Search_Ajax' ) ) {

	class OVAEV_Elementor_Events_Search_Ajax extends \Elementor\Widget_Base {

		/**
		 * Get widget name
		 */
		public function get_name() {
			return 'ova_events_search_ajax';
		}

		/**
		 * Get widget title
		 */
		public function get_title() {
			return esc_html__( 'Search Ajax', 'ovaev' );
		}

		/**
		 * Get widget icon
		 */
		public function get_icon() {
			return 'eicon-gallery-grid';
		}

		/**
		 * Get widget categories
		 */
		public function get_categories() {
			return [ 'ovatheme' ];
		}

		/**
		 * Get style depends
		 */
		public function get_style_depends() {
			return [ 'ova-datetimepicker' ];
		}

		/**
		 * Get script depends
		 */
		public function get_script_depends() {
			return [ 'ova-datetimepicker', 'ovaev-elementor-events-search-ajax' ];
		}

		/**
		 * Register controls
		 */
		protected function register_controls() {
			$this->start_controls_section(
				'section_setting',
				[
					'label' => esc_html__( 'Settings', 'ovaev' ),
				]
			);

				$this->add_control(
					'layout',
					[
						'label'   => esc_html__('Layout', 'ovaev'),
						'type'    => \Elementor\Controls_Manager::SELECT,
						'options' => [
							'1'   => esc_html__('Layout 1','ovaev'),
							'2'   => esc_html__('Layout 2','ovaev'),
							'3'   => esc_html__('Layout 3','ovaev'),
							'4'   => esc_html__('Layout 4','ovaev'),
							'5'   => esc_html__('Layout 5','ovaev'),
							'6'   => esc_html__('Layout 6','ovaev'),
						],
						'default' => '1',
					]
				);

				$this->add_control(
					'column',
					[
						'label'   => esc_html__( 'Column', 'ovaev' ),
						'type'    => \Elementor\Controls_Manager::SELECT,
						'options' => [
							'col1'   => esc_html__( '1 Column', 'ovaev' ),
							'col2'   => esc_html__( '2 Columns', 'ovaev' ),
							'col3'   => esc_html__( '3 Columns', 'ovaev' ),
						],
						'default' => 'col3',
					]
				);

				// List category
				$list_category = [
					'all' => esc_html__( 'All categories', 'ovaev' )
				];

				// Get categories
				$categories = get_categories([
					'taxonomy' 	=> 'event_category',
		           	'orderby' 	=> 'name',
		           	'order'   	=> 'ASC'
				]);
				if ( !empty( $categories ) && is_array( $categories ) ) {
					foreach ( $categories as $category ) {
						$list_category[$category->slug] = $category->cat_name;
					}
				}

				$this->add_control(
					'category',
					[
						'label'   => esc_html__( 'Category', 'ovaev' ),
						'type'    => \Elementor\Controls_Manager::SELECT,
						'default' => 'all',
						'options' => $list_category
					]
				);

				$this->add_control(
					'time_event',
					[
						'label'   => esc_html__('Choose time', 'ovaev'),
						'type'    => \Elementor\Controls_Manager::SELECT,
						'options' => [
							'all'    	=> esc_html__('All','ovaev'),
							'current'  	=> esc_html__('Current','ovaev'),
							'upcoming' 	=> esc_html__('Upcoming','ovaev'),
							'past'     	=> esc_html__('Past','ovaev'),
						],
						'default'   => 'all',
					]
				);

				$this->add_control(
					'posts_per_page',
					[
						'label' 	=> esc_html__( 'Posts Per Page', 'ovaev' ),
						'type' 		=> \Elementor\Controls_Manager::NUMBER,
						'default' 	=> 9,
					]
				);

				$this->add_control(
					'order_by',
					[
						'label'   => esc_html__( 'Order By', 'ovaev' ),
						'type'    => \Elementor\Controls_Manager::SELECT,
						'default' => 'title',
						'options' => [
							'title'             		=> esc_html__( 'Title', 'ovaev' ),
							'event_custom_sort' 		=> esc_html__( 'Custom Sort', 'ovaev' ),
							'ovaev_start_date_time'  	=> esc_html__( 'Start Date', 'ovaev' ),
							'ID'                		=> esc_html__( 'ID', 'ovaev' ),					
						],
					]
				);

				$this->add_control(
					'order',
					[
						'label' 	=> esc_html__( 'Order Post', 'ovaev' ),
						'type' 		=> \Elementor\Controls_Manager::SELECT,
						'default' 	=> 'DESC',
						'options' 	=> [
							'ASC' 	=> esc_html__( 'Ascending', 'ovaev' ),
							'DESC'  => esc_html__( 'Descending', 'ovaev' ),
						],
					]
				);
			
			$this->end_controls_section(); // END

			$this->start_controls_section(
				'section_filters_style',
				[
					'label' => esc_html__( 'Filters', 'ovaev' ),
					'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_control(
		            'filters_background',
		            [
		                'label' 	=> esc_html__( 'Background', 'ovaev' ),
		                'type' 		=> \Elementor\Controls_Manager::COLOR,
		                'selectors' => [
		                    '{{WRAPPER}} .ovaev-wrapper-search-ajax .search_archive_event' => 'background-color: {{VALUE}};',
		                ],
		            ]
		        );

		        $this->add_group_control(
		            \Elementor\Group_Control_Border::get_type(), [
		                'name' 		=> 'filters_border',
		                'selector' 	=> '{{WRAPPER}} .ovaev-wrapper-search-ajax .search_archive_event',
		                'separator' => 'before',
		            ]
		        );

		        $this->add_control(
		            'filters_border_radius',
		            [
		                'label' 		=> esc_html__( 'Border Radius', 'ovaev' ),
		                'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
		                'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
		                'selectors' 	=> [
		                    '{{WRAPPER}} .ovaev-wrapper-search-ajax .search_archive_event' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		                ],
		            ]
		        );

		        $this->add_responsive_control(
		            'filters_padding',
		            [
		                'label' 		=> esc_html__( 'Padding', 'ovaev' ),
		                'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
		                'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
		                'selectors' 	=> [
		                    '{{WRAPPER}} .ovaev-wrapper-search-ajax .search_archive_event' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		                ],
		            ]
		        );

		        $this->add_responsive_control(
		            'filters_margin',
		            [
		                'label' 		=> esc_html__( 'Margin', 'ovaev' ),
		                'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
		                'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
		                'selectors' 	=> [
		                    '{{WRAPPER}} .ovaev-wrapper-search-ajax .search_archive_event' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		                ],
		            ]
		        );

		        $this->add_control(
					'filter_label',
					[
						'label' 	=> esc_html__( 'Label', 'ovaev' ),
						'type' 		=> \Elementor\Controls_Manager::HEADING,
						'separator' => 'before',
					]
				);

		        $this->add_control(
		            'filters_color',
		            [
		                'label' 	=> esc_html__( 'Color', 'ovaev' ),
		                'type' 		=> \Elementor\Controls_Manager::COLOR,
		                'selectors' => [
		                    '{{WRAPPER}} .ovaev-wrapper-search-ajax .search_archive_event form .ova-label-search' => 'color: {{VALUE}};',
		                ],
		            ]
		        );

		        $this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'labels_typography',
						'selector' 	=> '{{WRAPPER}} .ovaev-wrapper-search-ajax .search_archive_event form .ova-label-search',
					]
				);

		        $this->add_responsive_control(
		            'filters_label_margin',
		            [
		                'label' 		=> esc_html__( 'Margin', 'ovaev' ),
		                'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
		                'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
		                'selectors' 	=> [
		                    '{{WRAPPER}} .ovaev-wrapper-search-ajax .search_archive_event form .ova-label-search' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		                ],
		            ]
		        );

			$this->end_controls_section(); // END

		}

		/**
		 * Get data ajax
		 */
		public function get_data_ajax() {
			// Get settings
			$settings = $this->get_settings_for_display();
			
			// Post data
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
	                        'value'   => [ current_time( 'timestamp' ) - 1, current_time( 'timestamp' )+ ( 24*60*60 ) + 1 ],
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
	                    'value'   => current_time( 'timestamp' ),
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
		 * Render HTML
		 */
		protected function render() {
			// Get settings
			$settings = $this->get_data_ajax();

			// Get template
			$template = apply_filters( 'elementor_ovaev_search_ajax', 'elements/ovaev_events_search_ajax.php' );
			ob_start();
			ovaev_get_template( $template, $settings );
			echo ob_get_clean();
		}
	}

	// Register new widget
	$widgets_manager->register( new OVAEV_Elementor_Events_Search_Ajax() );
}