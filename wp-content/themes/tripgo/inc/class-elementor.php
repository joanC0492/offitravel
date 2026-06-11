<?php if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Class Tripgo_Elementor
 */
if ( !class_exists( 'Tripgo_Elementor' ) ) {

	class Tripgo_Elementor {
		
		/**
		 * Construct
		 */
		public function __construct() {
			// Register Header Footer Category in Pane
		    add_action( 'elementor/elements/categories_registered', [ $this, 'tripgo_add_category' ] );

		    // After register styles
		    add_action( 'elementor/frontend/after_register_styles', [ $this, 'tripgo_enqueue_styles' ] );

		    // After register scripts
		    add_action( 'elementor/frontend/after_register_scripts', [ $this, 'tripgo_enqueue_scripts' ] );
			
			// Register widgets
			add_action( 'elementor/widgets/register', [ $this, 'tripgo_include_widgets' ] );
			
			// Add new animations
			add_filter( 'elementor/controls/animations/additional_animations', [ $this, 'tripgo_add_animations' ], 10 , 0 );

			// Enqueue footer scripts
			add_action( 'wp_print_footer_scripts', [ $this, 'tripgo_enqueue_footer_scripts' ] );

			// Add new icons
			add_filter( 'elementor/icons_manager/additional_tabs', [ $this, 'tripgo_icons_filters_new' ], 9999999, 1 );

			// Add icons social custom
			add_action( 'elementor/element/social-icons/section_social_hover/after_section_end', [ $this, 'tripgo_social_icons_custom' ], 10, 2 );

			// Add text editor custom control style
			add_action( 'elementor/element/text-editor/section_style/after_section_end', [ $this, 'tripgo_text_editor_custom' ], 10, 2 );

			// Add customize accordion 
			add_action( 'elementor/element/accordion/section_toggle_style_content/after_section_end', [ $this, 'tripgo_accordion_custom' ], 10, 2 );

			// Add customize button 
			add_action( 'elementor/element/button/section_style/after_section_end', [ $this, 'tripgo_button_custom' ], 10, 2 );
			
			// Add customize icon box 
			add_action( 'elementor/element/icon-box/section_style_content/after_section_end', [ $this, 'tripgo_icon_box_custom' ], 10, 2 );

			// Add customize icon list 
			add_action( 'elementor/element/icon-list/section_text_style/after_section_end', [ $this, 'tripgo_icon_list_custom' ], 10, 2 );
			
			// Remove animations style from Elementor
			add_action( 'wp_enqueue_scripts', [ $this, 'tripgo_remove_animations_styles' ] );
		}

		/**
		 * Add category
		 */
		public function tripgo_add_category() {
		    \Elementor\Plugin::instance()->elements_manager->add_category(
		        'hf',
		        [
		            'title' => esc_html__( 'Header Footer', 'tripgo' ),
		            'icon' 	=> 'fa fa-plug'
		        ]
		    );

		    \Elementor\Plugin::instance()->elements_manager->add_category(
		        'tripgo',
		        [
		            'title' => esc_html__( 'Tripgo', 'tripgo' ),
		            'icon' 	=> 'fa fa-plug'
		        ]
		    );
		}

		/**
		 * Widget social icons style
		 */
		public function tripgo_enqueue_styles() {
			// Widget social icons
	        if ( defined( 'ELEMENTOR_ASSETS_PATH' ) && defined( 'ELEMENTOR_ASSETS_URL' ) ) {
	        	if ( file_exists( ELEMENTOR_ASSETS_PATH . 'css/widget-social-icons.min.css' ) ) {
	                wp_enqueue_style( 'widget-social-icons', ELEMENTOR_ASSETS_URL . 'css/widget-social-icons.min.css', [], ELEMENTOR_VERSION );
	            }
	        }

	        // Get all css files
	        $css_files = glob( get_theme_file_path( '/assets/scss/elementor/*.css' ) );
	        if ( !empty( $css_files ) && is_array( $css_files ) ) {
	        	foreach ( $css_files as $file ) {
		            $file_name = wp_basename( $file );
		            $handle    = str_replace( '.css', '', $file_name );
		            $src       = get_theme_file_uri( '/assets/scss/elementor/' . $file_name );

		            if ( file_exists( $file ) ) {
		                wp_register_style( 'tripgo-elementor-' . $handle, $src );
		            }
		        }
	        }

			// Odometer for counter
			wp_register_style( 'ova-odometer', TRIPGO_URI.'/assets/libs/odometer/odometer.min.css' );

			// Fancybox
			wp_register_style( 'ova-fancybox', TRIPGO_URI.'/assets/libs/fancybox/fancybox.css' );

			// Slick
			wp_register_style( 'ova-slick', TRIPGO_URI.'/assets/libs/slick/slick.css' );
			wp_register_style( 'ova-slick-theme', TRIPGO_URI.'/assets/libs/slick/slick-theme.css' );

			// Animate
			wp_register_style( 'ova-animate', TRIPGO_URI.'/assets/libs/animate/animate.min.css' );
		}

		/**
		 * Enqueue scripts
		 */
		public function tripgo_enqueue_scripts() {
			// Get all js files
	        $js_files = glob( get_theme_file_path( '/assets/js/elementor/*.js' ) );
	        if ( !empty( $js_files ) && is_array( $js_files ) ) {
	        	foreach ( $js_files as $file ) {
		            $file_name = wp_basename( $file );
		            $handle    = str_replace( '.js', '', $file_name );
		            $src       = get_theme_file_uri( '/assets/js/elementor/' . $file_name );

		            if ( file_exists( $file ) ) {
		                wp_register_script( 'tripgo-elementor-' . $handle, $src, ['jquery'], false, true );
		            }
		        }
	        }

			// Countdown plugin
			wp_register_script( 'ova-countdown-plugin', TRIPGO_URI.'/assets/libs/countdown/jquery.plugin.js', [ 'jquery' ], false, true );

			// Countdown
			wp_register_script( 'ova-countdown', TRIPGO_URI.'/assets/libs/countdown/jquery.countdown.min.js', [ 'jquery' ], false, true );

			// Appear js
			wp_register_script( 'ova-appear', TRIPGO_URI.'/assets/libs/appear/appear.js', [ 'jquery' ], false, true );

			// Odometer
			wp_register_script( 'ova-odometer', TRIPGO_URI.'/assets/libs/odometer/odometer.min.js', [ 'jquery' ], false, true );

			// Isotope
			wp_register_script( 'ova-isotope', TRIPGO_URI.'/assets/libs/isotope/isotope.pkgd.min.js', [ 'jquery' ], false, true );

			// Fancybox
			wp_register_script( 'ova-fancybox', TRIPGO_URI.'/assets/libs/fancybox/fancybox.umd.js', [ 'jquery' ], false, true );

			// Slick
			wp_register_script( 'ova-slick', TRIPGO_URI.'/assets/libs/slick/slick.min.js', [ 'jquery' ], false, true );
		}

		/**
		 * Include widgets
		 */
		public function tripgo_include_widgets( $widgets_manager ) {
	        $widget_files = glob( get_theme_file_path( 'elementor/widgets/*.php' ) );
	        if ( !empty( $widget_files ) && is_array( $widget_files ) ) {
	        	foreach ( $widget_files as $file ) {
		            $file = get_theme_file_path( 'elementor/widgets/' . wp_basename( $file ) );
		            if ( file_exists( $file ) ) {
		                require_once $file;
		            }
		        }
	        }
	    }

	    /**
	     * Add new animations
	     */
	    public function tripgo_add_animations() {
	    	$animations = [
	    		'Tripgo' => [
	            	'ova-move-up' 		=> esc_html__( 'Move Up', 'tripgo' ),
	                'ova-move-down' 	=> esc_html__( 'Move Down', 'tripgo' ),
	                'ova-move-left'     => esc_html__( 'Move Left', 'tripgo' ),
	                'ova-move-right'    => esc_html__( 'Move Right', 'tripgo' ),
	                'ova-scale-up'      => esc_html__( 'Scale Up', 'tripgo' ),
	                'ova-flip'          => esc_html__( 'Flip', 'tripgo' ),
	                'ova-helix'         => esc_html__( 'Helix', 'tripgo' ),
	                'ova-popup'			=> esc_html__( 'PopUp','tripgo' )
	            ]
	    	];

	        return $animations;
	    }

	    /**
	     * Enqueue footer scripts
	     */
		public function tripgo_enqueue_footer_scripts() {
			// Font Icon
		    wp_enqueue_style( 'ova-ovaicon', TRIPGO_URI.'/assets/libs/ovaicon/font/ovaicon.css', [], null );

		    // Icomoon
		    wp_enqueue_style( 'ova-icomoon', TRIPGO_URI.'/assets/libs/icomoon/style.css', [], null );

		    // Flaticon
		    wp_enqueue_style( 'ova-flaticon', TRIPGO_URI.'/assets/libs/flaticon/font/flaticon_tripgo.css', [], null );

		    // Flaticon 2
		    wp_enqueue_style( 'ova-flaticon2', TRIPGO_URI.'/assets/libs/flaticon2/font/flaticon_tripgo2.css', [], null );
		}

		/**
		 * Add new icons
		 */
		public function tripgo_icons_filters_new( $tabs = [] ) {
			$newicons = [];

			// Font Icon
			$font_data['json_url'] 	= TRIPGO_URI.'/assets/libs/ovaicon/ovaicon.json';
			$font_data['name'] 		= 'ovaicon';

			$newicons[ $font_data['name'] ] = [
				'name'          => $font_data['name'],
				'label'         => esc_html__( 'Default', 'tripgo' ),
				'url'           => '',
				'enqueue'       => '',
				'prefix'        => 'ovaicon-',
				'displayPrefix' => '',
				'ver'           => '1.0',
				'fetchJson'     => $font_data['json_url']
			];

			// Icomoon
			$font_icomoon['json_url'] 	= TRIPGO_URI.'/assets/libs/icomoon/icomoon.json';
			$font_icomoon['name'] 		= 'icomoon';

			$newicons[ $font_icomoon['name'] ] = [
				'name'          => $font_icomoon['name'],
				'label'         => esc_html__( 'Icomoon', 'tripgo' ),
				'url'           => '',
				'enqueue'       => '',
				'prefix'        => 'icomoon-',
				'displayPrefix' => '',
				'ver'           => '1.0',
				'fetchJson'     => $font_icomoon['json_url']
			];

			// Flaticon
			$font_flaticon['json_url'] 	= TRIPGO_URI.'/assets/libs/flaticon/flaticon.json';
			$font_flaticon['name'] 		= 'flaticon';

			$newicons[ $font_flaticon['name'] ] = [
				'name'          => $font_flaticon['name'],
				'label'         => esc_html__( 'Flaticon', 'tripgo' ),
				'url'           => '',
				'enqueue'       => '',
				'prefix'        => 'flaticon-',
				'displayPrefix' => '',
				'ver'           => '1.0',
				'fetchJson'     => $font_flaticon['json_url']
			];

			// Flaticon 2
			$font_flaticon2['json_url'] = TRIPGO_URI.'/assets/libs/flaticon2/flaticon.json';
			$font_flaticon2['name'] 	= 'flaticon2';

			$newicons[ $font_flaticon2['name'] ] = [
				'name'          => $font_flaticon2['name'],
				'label'         => esc_html__( 'Flaticon 2', 'tripgo' ),
				'url'           => '',
				'enqueue'       => '',
				'prefix'        => 'flaticon2-',
				'displayPrefix' => '',
				'ver'           => '1.0',
				'fetchJson'     => $font_flaticon2['json_url']
			];

			return array_merge( $tabs, $newicons );
		}

		/**
		 * Social icons
		 */
		public function tripgo_social_icons_custom ( $element, $args ) {
			$element->start_controls_section(
				'ova_social_icons',
				[
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
					'label' => esc_html__( 'Ova Social Icon', 'tripgo' ),
				]
			);

				$element->add_responsive_control(
		            'ova_social_icons_display',
		            [
		                'label' 	=> esc_html__( 'Display', 'tripgo' ),
		                'type' 		=> \Elementor\Controls_Manager::CHOOSE,
		                'options' 	=> [
		                    'inline-block' => [
		                        'title' => esc_html__( 'Block', 'tripgo' ),
		                        'icon' 	=> 'eicon-h-align-left',
		                    ],
		                    'inline-flex' => [
		                        'title' => esc_html__( 'Flex', 'tripgo' ),
		                        'icon' 	=> 'eicon-h-align-center',
		                    ],
		                ],
		                'selectors' => [
		                    '{{WRAPPER}} .elementor-icon.elementor-social-icon' => 'display: {{VALUE}}',
		                ],
		            ]
		        );

			$element->end_controls_section();
		}

		/**
		 * Text editor
		 */
	    public function tripgo_text_editor_custom( $element, $args ) {
			$element->start_controls_section(
				'ova_tabs',
				[
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
					'label' => esc_html__( 'Ova Text Editor', 'tripgo' ),
				]
			);

				$element->add_responsive_control(
					'text_margin',
					[
						'label' 		=> esc_html__( 'Margin', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em' ],
						'selectors' 	=> [
						'{{WRAPPER}}  p' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$element->add_responsive_control(
			        'text_padding',
			        [
			            'label' 		=> esc_html__( 'Padding', 'tripgo' ),
			            'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
			            'size_units' 	=> [ 'px', '%', 'em' ],
			            'selectors' 	=> [
			             '{{WRAPPER}}  p' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			            ],
			         ]
			    );

			$element->end_controls_section();
		}  
	    
	    /**
	     * Accordion widget
	     */
	    public function tripgo_accordion_custom( $element, $args) {
			$element->start_controls_section(
				'ova_accordion_customize',
				[
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
					'label' => esc_html__( 'Ova Accordion', 'tripgo' ),
				]
			);

				$element->add_responsive_control(
					'acc_border_radius',
					[
						'label' 		=> esc_html__( 'Border Radius', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', 'em', '%' ],
						'selectors' 	=> [
							'{{WRAPPER}} .elementor-accordion-item.ova-active' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							
						],
					]
				);

				$element->add_control(
					'acc_border_width',
					[
						'label' => esc_html__( 'Border Width', 'tripgo' ),
						'type' => \Elementor\Controls_Manager::SLIDER,
						'range' => [
							'px' => [
								'min' => 0,
								'max' => 10,
							],
						],
						'selectors' => [
							
							'{{WRAPPER}} .elementor-accordion-item.ova-active' => 'border-width: {{SIZE}}{{UNIT}} !important;',
						],
					]
				);

				$element->add_control(
					'acc_border_color',
					[
						'label' => esc_html__( 'Border Color', 'tripgo' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .elementor-accordion-item.ova-active' => 'border-color: {{VALUE}} !important;',
							
						],
					]
				);

				$element->add_responsive_control(
			        'acc_content_padding',
			        [
			            'label' 		=> esc_html__( 'Content Padding', 'tripgo' ),
			            'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
			            'size_units' 	=> [ 'px', '%', 'em' ],
			            'selectors' 	=> [
			             '{{WRAPPER}}  .elementor-accordion-item .elementor-tab-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} ;',
			            ],
			         ]
			    );
		      
			$element->end_controls_section();
		}

		/**
		 * Button widget
		 */
		public function tripgo_button_custom( $element, $args ) {
			$element->start_controls_section(
				'ova_button_customize',
				[
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
					'label' => esc_html__( 'Ova Button', 'tripgo' ),
				]
			);

				$element->add_control(
					'ova_button_icon_font_size',
					[
						'label' => esc_html__( 'Icon Size', 'tripgo' ),
						'type' => \Elementor\Controls_Manager::SLIDER,
						'size_units' => [ 'px' ],
						'range' => [
							'px' => [
								'min' => 1,
								'max' => 300,
								'step' => 1,
							]
						],
						'selectors' => [
							'{{WRAPPER}} .elementor-button .elementor-button-icon i' => 'font-size: {{SIZE}}{{UNIT}};',
							
						],
					]
				);
	            
	            $element->add_control(
					'ova_button_icon_right_margin_left',
					[
						'label' => esc_html__( 'Icon Margin Left', 'tripgo' ),
						'type' => \Elementor\Controls_Manager::SLIDER,
						'size_units' => [ 'px' ],
						'range' => [
							'px' => [
								'min' => 0,
								'max' => 35,
								'step' => 1,
							]
						],
						'selectors' => [
							'{{WRAPPER}} .elementor-button .elementor-align-icon-right' => 'margin-left: {{SIZE}}{{UNIT}};',
							
						],
					]
				);


				$element->add_responsive_control(
	                'ova_button_alignment',
	                [
	                    'label' => esc_html__( 'Alignment', 'tripgo' ),
	                    'type' => \Elementor\Controls_Manager::CHOOSE,
	                    'options' => [
	                        'flex-start' => [
	                            'title' => esc_html__( 'Flex start', 'tripgo' ),
	                            'icon' => 'eicon-v-align-top',
	                        ],
	                        'center' => [
	                            'title' => esc_html__( 'Center', 'tripgo' ),
	                            'icon' => 'eicon-v-align-middle',
	                        ],
	                        'end' => [
	                            'title' => esc_html__( 'End', 'tripgo' ),
	                            'icon' => 'eicon-v-align-bottom',
	                        ],
	                    ],
	                    'selectors' => [
	                        '{{WRAPPER}} .elementor-button-content-wrapper' => 'align-items: {{VALUE}};',
	                    ],
	                ]
	            );

			$element->end_controls_section();
		}

		/**
		 * Icon box widget
		 */
		public function tripgo_icon_box_custom( $element, $args ) {
			$element->start_controls_section(
				'ova_icon_box_customize',
				[
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
					'label' => esc_html__( 'Ova Icon Box', 'tripgo' ),
				]
			);

				$element->add_control(
					'icon_heading',
					[
						'label' => esc_html__( 'Icon', 'tripgo' ),
						'type' => \Elementor\Controls_Manager::HEADING,
					]
				);

				$element->add_group_control(
					\Elementor\Group_Control_Box_Shadow::get_type(),
					[
						'name' => 'icon_box_shadow',
						'selector' => '{{WRAPPER}} .elementor-icon',
					]
				);
				
				$element->add_responsive_control(
		            'ova_shape_polygon',
		            [
		                'label' 	=> esc_html__( 'Polygon Shape', 'tripgo' ),
		                'type' 		=> \Elementor\Controls_Manager::CHOOSE,
		                'options' 	=> [
		                    'clip-path' => [
		                        'title' => esc_html__( 'Polygon', 'tripgo' ),
		                        'icon' 	=> 'eicon-check',
		                    ],
		                ],
		                'selectors' => [
	  						'{{WRAPPER}} .elementor-icon-box-icon .elementor-icon' => '-webkit-clip-path: polygon(50% 0%, 95% 25%, 95% 75%, 50% 100%, 5% 75%, 5% 25%);',
	  						'{{WRAPPER}} .elementor-icon-box-icon .elementor-icon' => '{{VALUE}}: polygon(50% 0%, 95% 25%, 95% 75%, 50% 100%, 5% 75%, 5% 25%)',
		                ],
		            ]
		        );

		        $element->add_control(
					'content_heading',
					[
						'label' => esc_html__( 'Content', 'tripgo' ),
						'type' => \Elementor\Controls_Manager::HEADING,
						'separator' => 'before',
					]
				);

				$element->add_control(
					'title_color_hover',
					[
						'label' => esc_html__( 'Title Color Hover', 'tripgo' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .elementor-widget-container:hover .elementor-icon-box-title' => 'color: {{VALUE}}',
						],
					]
				);

				$element->add_control(
					'description_color_hover',
					[
						'label' => esc_html__( 'Description Color Hover', 'tripgo' ),
						'type' => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .elementor-widget-container:hover .elementor-icon-box-description' => 'color: {{VALUE}}',
						],
					]
				);

				$element->add_responsive_control(
					'title_margin',
					[
						'label' 		=> esc_html__( 'Title Margin', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em' ],
						'selectors' 	=> [
						'{{WRAPPER}}  .elementor-icon-box-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

			$element->end_controls_section();
		}

		/**
		 * Icon list widget
		 */
		public function tripgo_icon_list_custom( $element, $args ) {
			$element->start_controls_section(
				'ova_icon_list_customize',
				[
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
					'label' => esc_html__( 'Ova Icon List', 'tripgo' ),
				]
			);

			    $element->add_control(
					'icon_heading',
					[
						'label' => esc_html__( 'Icon', 'tripgo' ),
						'type' => \Elementor\Controls_Manager::HEADING,
					]
				);

					$element->add_control(
						'icon_first_child_color',
						[
							'label' => esc_html__( 'First child Color', 'tripgo' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .elementor-icon-list-items .elementor-icon-list-item:first-child .elementor-icon-list-icon i' => 'color: {{VALUE}}',
							],
						]
					);

					$element->add_control(
						'icon_secondary_color',
						[
							'label' => esc_html__( 'Secondary Color', 'tripgo' ),
							'type' => \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .elementor-icon-list-items .elementor-icon-list-item:nth-child(2) .elementor-icon-list-icon i' => 'color: {{VALUE}}',
							],
						]
					);

			$element->end_controls_section();
		}

		/**
		 * Remove animations style from Elementor
		 */
		public function tripgo_remove_animations_styles() {
			// Deregister the stylesheet by handle
		    foreach ( $this->tripgo_add_animations() as $animations ) {
		    	if ( !empty( $animations ) && is_array( $animations ) ) {
		    		foreach ( array_keys( $animations ) as $animation ) {
		    			wp_deregister_style( 'e-animation-'.$animation );
		    			wp_enqueue_style( 'e-animation-'.$animation, TRIPGO_URI.'/assets/scss/none.css', [], null );
		    		}
		    	}
		    }
		}
	}

	// init class
	new Tripgo_Elementor();
}