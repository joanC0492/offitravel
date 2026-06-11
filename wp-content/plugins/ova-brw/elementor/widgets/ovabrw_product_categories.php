<?php if ( !defined( 'ABSPATH' ) ) exit(); // Exit if accessed directly

/**
 * Class OVABRW_Product_Categories
 */
if ( !class_exists( 'OVABRW_Product_Categories', false ) ) {

	class OVABRW_Product_Categories extends \Elementor\Widget_Base {

		/**
		 * Get widget name
		 */
		public function get_name() {		
			return 'ovabrw_product_categories';
		}

		/**
		 * Get widget title
		 */
		public function get_title() {
			return esc_html__( 'Product Categories', 'ova-brw' );
		}

		/**
		 * Get widget icon
		 */
		public function get_icon() {
			return 'eicon-product-categories';
		}

		/**
		 * Get widget categories
		 */
		public function get_categories() {
			return [ 'ovabrw-tours' ];
		}

		/**
		 * Get style depends
		 */
		public function get_style_depends() {
			return [ 'swipe', 'ovabrw-elementor-product-categories' ];
		}

		/**
		 * Get script depends
		 */
		public function get_script_depends() {
			return [ 'swipe', 'ovabrw-elementor-product-categories' ];
		}

		/**
		 * Register controls
		 */
		protected function register_controls() {
			/**
			 * Content Tab
			*/
			$this->start_controls_section(
				'section_content',
				[
					'label' => esc_html__( 'Content', 'ova-brw' )
				]
			);

				// Categories
				$categories = [];

				// Get product categories
				$product_categories = get_categories([
					'taxonomy' 	=> 'product_cat',
					'orderby' 	=> 'name',
					'order' 	=> 'ASC'
				]);

				// Loop
			  	if ( ovabrw_array_exists( $product_categories ) ) {
				  	foreach ( $product_categories as $cat ) {
					  	$categories[$cat->term_id] = $cat->name;
				  	}
			  	} // END loop

				$this->add_control(
					'categories',
					[
						'label' 	=> esc_html__( 'Categories', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::SELECT2,
						'multiple' 	=> true,
						'options' 	=> $categories,
						'default' 	=> 'all'
					]
				);

				$this->add_control(
					'icon',
					[
						'label' 	=> esc_html__( 'Icon', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::ICONS,
						'default' 	=> [
							'value' 	=> 'icomoon icomoon-arrow-right',
							'library' 	=> 'all',
						],
					]
				);

				$this->add_control(
					'label_view_details',
					[
						'label' 	=> esc_html__( 'Text Button', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::TEXT,
						'default' 	=> esc_html__('View detail', 'ova-brw'),
					]
				);

				$this->add_control(
					'show_image',
					[
						'label' 		=> esc_html__( 'Show Image', 'ova-brw' ),
						'type' 			=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 		=> esc_html__( 'Show', 'ova-brw' ),
						'label_off' 	=> esc_html__( 'Hide', 'ova-brw' ),
						'return_value' 	=> 'yes',
						'default' 		=> 'yes',
					]
				);

				$this->add_control(
					'show_title',
					[
						'label' 		=> esc_html__( 'Show Title', 'ova-brw' ),
						'type' 			=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 		=> esc_html__( 'Show', 'ova-brw' ),
						'label_off' 	=> esc_html__( 'Hide', 'ova-brw' ),
						'return_value' 	=> 'yes',
						'default' 		=> 'yes',
					]
				);

				$this->add_control(
					'show_product_counts',
					[
						'label' 		=> esc_html__( 'Show Product Counts', 'ova-brw' ),
						'type' 			=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 		=> esc_html__( 'Show', 'ova-brw' ),
						'label_off' 	=> esc_html__( 'Hide', 'ova-brw' ),
						'return_value' 	=> 'yes',
						'default' 		=> 'no',
					]
				);

				$this->add_control(
					'text_product_counts',
					[
						'label' 	=> esc_html__( 'Text Product Counts', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::TEXT,
						'default' 	=> esc_html__( 'Available tour palace', 'ova-brw' ),
						'condition' => [
							'show_product_counts' => 'yes'
						]
					]
				);

			$this->end_controls_section(); // End Content Tab

			// Additional Options
			$this->start_controls_section(
				'section_additional_options',
				[
					'label' => esc_html__( 'Additional Options', 'ova-brw' ),
				]
			);

				$this->add_control(
					'margin_items',
					[
						'label'   => esc_html__( 'Margin Right Items', 'ova-brw' ),
						'type'    => \Elementor\Controls_Manager::NUMBER,
						'default' => 40,
					]
					
				);

				$this->add_control(
					'item_number',
					[
						'label'       => esc_html__( 'Item Number', 'ova-brw' ),
						'type'        => \Elementor\Controls_Manager::NUMBER,
						'description' => esc_html__( 'Number Item', 'ova-brw' ),
						'default'     => 5.3,
					]
				);

				$this->add_control(
					'slides_to_scroll',
					[
						'label'       => esc_html__( 'Slides to Scroll', 'ova-brw' ),
						'type'        => \Elementor\Controls_Manager::NUMBER,
						'description' => esc_html__( 'Set how many slides are scrolled per swipe.', 'ova-brw' ),
						'default'     => 1,
					]
				);

				$this->add_control(
					'pause_on_hover',
					[
						'label'   => esc_html__( 'Pause on Hover', 'ova-brw' ),
						'type'    => \Elementor\Controls_Manager::SWITCHER,
						'default' => 'yes',
						'options' => [
							'yes' => esc_html__( 'Yes', 'ova-brw' ),
							'no'  => esc_html__( 'No', 'ova-brw' ),
						],
						'frontend_available' => true,
					]
				);

				$this->add_control(
					'infinite',
					[
						'label'   => esc_html__( 'Infinite Loop', 'ova-brw' ),
						'type'    => \Elementor\Controls_Manager::SWITCHER,
						'default' => 'yes',
						'options' => [
							'yes' => esc_html__( 'Yes', 'ova-brw' ),
							'no'  => esc_html__( 'No', 'ova-brw' ),
						],
						'frontend_available' => true,
					]
				);

				$this->add_control(
					'autoplay',
					[
						'label'   => esc_html__( 'Autoplay', 'ova-brw' ),
						'type'    => \Elementor\Controls_Manager::SWITCHER,
						'default' => 'no',
						'options' => [
							'yes' => esc_html__( 'Yes', 'ova-brw' ),
							'no'  => esc_html__( 'No', 'ova-brw' ),
						],
						'frontend_available' => true,
					]
				);

				$this->add_control(
					'autoplay_speed',
					[
						'label'     => esc_html__( 'Autoplay Speed', 'ova-brw' ),
						'type'      => \Elementor\Controls_Manager::NUMBER,
						'default'   => 3000,
						'step'      => 500,
						'condition' => [
							'autoplay' => 'yes',
						],
						'frontend_available' => true,
					]
				);

				$this->add_control(
					'smartspeed',
					[
						'label'   => esc_html__( 'Smart Speed', 'ova-brw' ),
						'type'    => \Elementor\Controls_Manager::NUMBER,
						'default' => 500,
					]
				);

				$this->add_control(
					'nav_control',
					[
						'label'   => esc_html__( 'Show Nav', 'ova-brw' ),
						'type'    => \Elementor\Controls_Manager::SWITCHER,
						'default' => 'yes',
						'options' => [
							'yes' => esc_html__( 'Yes', 'ova-brw' ),
							'no'  => esc_html__( 'No', 'ova-brw' ),
						],
						'frontend_available' => true,
					]
				);

			$this->end_controls_section(); // Additional Options

			// SECTION TAB STYLE CONTENT
			$this->start_controls_section(
				'section_content_item',
				[
					'label' => esc_html__( 'Content', 'ova-brw' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
				]
			);
				// General
				$this->add_responsive_control(
					'content_border_radius',
					[
						'label' 		=> esc_html__( 'Border Radius', 'ova-brw' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova_product_categories .item .image-thumbnail' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_responsive_control(
					'content_border_radius_hover',
					[
						'label' 		=> esc_html__( 'Border Radius Hover', 'ova-brw' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova_product_categories .item:hover .image-thumbnail' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				// Title
				$this->add_control(
					'title_content',
					[
						'label' 	=> esc_html__( 'Title', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::HEADING,
						'separator' => 'before',
					]
				);

					$this->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' 		=> 'title_typography',
							'selector' 	=> '{{WRAPPER}} .ova_product_categories .item .title a',
						]
					);

					$this->add_control(
						'color_title',
						[
							'label' 	=> esc_html__( 'Color', 'ova-brw' ),
							'type' 		=> \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ova_product_categories .item .title a' => 'color : {{VALUE}};',
							],
						]
					);

					$this->add_control(
						'color_title_hover',
						[
							'label' 	=> esc_html__( 'Color Hover', 'ova-brw' ),
							'type' 		=> \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ova_product_categories .item .title a:hover' => 'color : {{VALUE}};',
							],
						]
					);

					$this->add_responsive_control(
						'margin_title',
						[
							'label' 		=> esc_html__( 'Margin', 'ova-brw' ),
							'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
							'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
							'selectors' 	=> [
								'{{WRAPPER}} .ova_product_categories .item .title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							],
						]
					);

					$this->add_responsive_control(
						'title_alignment',
						[
							'label' 	=> esc_html__( 'Alignment', 'ova-brw' ),
							'type' 		=> \Elementor\Controls_Manager::CHOOSE,
							'options' 	=> [
								'left' 	=> [
									'title' => esc_html__( 'Left', 'ova-brw' ),
									'icon' 	=> 'eicon-text-align-left',
								],
								'center' => [
									'title' => esc_html__( 'Center', 'ova-brw' ),
									'icon' 	=> 'eicon-text-align-center',
								],
								'right' => [
									'title' => esc_html__( 'Right', 'ova-brw' ),
									'icon' 	=> 'eicon-text-align-right',
								],
							],
							'toggle' 	=> true,
							'selectors' => [
								'{{WRAPPER}} .ova_product_categories .item .title' => 'text-align: {{VALUE}}',
							],
						]
					);

				// Text Button (View Detail)
				$this->add_control(
					'view_detail',
					[
						'label' 	=> esc_html__( 'Text Button', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::HEADING,
						'separator' => 'before',
					]
				);

					$this->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' 		=> 'view_detail_typography',
							'selector' 	=> '{{WRAPPER}} .ova_product_categories .item .image-thumbnail .read-more',
						]
					);

					$this->add_control(
						'color_view_detail',
						[
							'label' 	=> esc_html__( 'Color', 'ova-brw' ),
							'type' 		=> \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ova_product_categories .item .image-thumbnail .read-more' => 'color : {{VALUE}};',
							],
						]
					);

					$this->add_control(
						'color_view_detail_hover',
						[
							'label' 	=> esc_html__( 'Color Hover', 'ova-brw' ),
							'type' 		=> \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ova_product_categories .item .image-thumbnail .read-more:hover' => 'color : {{VALUE}};',
							],
						]
					);

				// Icon Button (View Detail)
				$this->add_control(
					'icon_button',
					[
						'label' 	=> esc_html__( 'Icon', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::HEADING,
						'separator' => 'before',
					]
				);

					$this->add_responsive_control(
						'icon_size',
						[
							'label' 	=> esc_html__( 'Icon Size', 'ova-brw' ),
							'type' 		=> \Elementor\Controls_Manager::SLIDER,
							'range' 	=> [
								'px' 	=> [
									'min' => 0,
									'max' => 40,
								],
							],
							'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
							'selectors' 	=> [
								'{{WRAPPER}} .ova_product_categories .item .image-thumbnail .read-more i' => 'font-size: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_responsive_control(
						'icon_rotate',
						[
							'label' 		=> esc_html__( 'Rotate', 'ova-brw' ),
							'type' 			=> \Elementor\Controls_Manager::SLIDER,
							'size_units' 	=> [ 'deg', 'grad', 'rad', 'turn', 'custom' ],
							'default' 		=> [
								'unit' => 'deg',
							],
							'tablet_default' => [
								'unit' => 'deg',
							],
							'mobile_default' => [
								'unit' => 'deg',
							],
							'selectors' => [
								'{{WRAPPER}} .ova_product_categories .item .image-thumbnail .read-more i, {{WRAPPER}} .ova_product_categories .item .image-thumbnail .read-more svg' => 'transform: rotate({{SIZE}}{{UNIT}});',
							],
						]
					);

					$this->add_control(
						'icon_color',
						[
							'label' 	=> esc_html__( 'Color', 'ova-brw' ),
							'type' 		=> \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ova_product_categories .item .image-thumbnail .read-more i' => 'color : {{VALUE}};',
							],
						]
					);


					$this->add_control(
						'icon_color_hover',
						[
							'label' 	=> esc_html__( 'Color Hover', 'ova-brw' ),
							'type' 		=> \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ova_product_categories .item .image-thumbnail .read-more:hover i' => 'color : {{VALUE}};',
							],
						]
					);

					$this->add_control(
						'icon_bgcolor',
						[
							'label' 	=> esc_html__( 'Background Color', 'ova-brw' ),
							'type' 		=> \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ova_product_categories .item .image-thumbnail .read-more i' => 'background-color : {{VALUE}};',
							],
						]
					);

					$this->add_control(
						'icon_bgcolor_hover',
						[
							'label' 	=> esc_html__( 'Background Color Hover', 'ova-brw' ),
							'type' 		=> \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ova_product_categories .item .image-thumbnail .read-more:hover i' => 'background-color : {{VALUE}};',
							],
						]
					);


					$this->add_responsive_control(
						'icon_padding',
						[
							'label' 		=> esc_html__( 'Padding', 'ova-brw' ),
							'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
							'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
							'selectors' 	=> [
								'{{WRAPPER}} .ova_product_categories .item .image-thumbnail .read-more i' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							],
						]
					);

					$this->add_responsive_control(
						'icon_border_radius',
						[
							'label' 		=> esc_html__( 'Border Radius', 'ova-brw' ),
							'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
							'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
							'selectors' 	=> [
								'{{WRAPPER}} .ova_product_categories .item .image-thumbnail .read-more i' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							],
						]
					);

				// Image
				$this->add_control(
					'image_heading',
					[
						'label' 	=> esc_html__( 'Image', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::HEADING,
						'separator' => 'before',
					]
				);

					$this->add_control(
						'overlay_bgcolor',
						[
							'label' 	=> esc_html__( 'Overlay', 'ova-brw' ),
							'type' 		=> \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ova_product_categories .item .image-thumbnail:before' => 'background-color : {{VALUE}};',
							],
						]
					);

					$this->add_responsive_control(
						'overlay_bgcolor_opacity',
						[
							'label' => esc_html__( 'Opacity', 'ova-brw' ),
							'type' 	=> \Elementor\Controls_Manager::SLIDER,
							'range' => [
								'px' => [
									'min' => 0,
									'max' => 1,
								],
							],
							'step' 	=> 0.1,
							'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
							'selectors' 	=> [
								'{{WRAPPER}} .ova_product_categories .item .image-thumbnail:before' => 'opacity: {{SIZE}};',
							],
						]
					);

					$this->add_responsive_control(
						'image_height',
						[
							'label' 	=> esc_html__( 'Height', 'ova-brw' ),
							'type' 		=> \Elementor\Controls_Manager::SLIDER,
							'range' 	=> [
								'px' => [
									'min' => 120,
									'max' => 330,
								],
							],
							'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
							'selectors' 	=> [
								'{{WRAPPER}} .ova_product_categories .item .image-thumbnail img' => 'height: {{SIZE}}{{UNIT}};min-height: {{SIZE}}{{UNIT}};',
							],
						]
					);


			$this->end_controls_section(); // END SECTION TAB STYLE CONTENT

			// Product Counts
			$this->start_controls_section(
				'section_product_counts_style',
				[
					'label' 	=> esc_html__( 'Product Counts', 'ova-brw' ),
					'tab' 		=> \Elementor\Controls_Manager::TAB_STYLE,
					'condition' => [
						'show_product_counts' => 'yes'
					]
				]
			);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'product_counts_typography',
						'selector' 	=> '{{WRAPPER}} .ova_product_categories .item .counts',
					]
				);

				$this->add_control(
					'color_product_counts',
					[
						'label' 	=> esc_html__( 'Color', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova_product_categories .item .counts' => 'color : {{VALUE}};',
						],
					]
				);

				$this->add_responsive_control(
					'margin_product_counts',
					[
						'label' 		=> esc_html__( 'Margin', 'ova-brw' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova_product_categories .item .counts' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_responsive_control(
					'product_counts_alignment',
					[
						'label' 	=> esc_html__( 'Alignment', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::CHOOSE,
						'options' 	=> [
							'left' 	=> [
								'title' => esc_html__( 'Left', 'ova-brw' ),
								'icon' 	=> 'eicon-text-align-left',
							],
							'center' => [
								'title' => esc_html__( 'Center', 'ova-brw' ),
								'icon' 	=> 'eicon-text-align-center',
							],
							'right' => [
								'title' => esc_html__( 'Right', 'ova-brw' ),
								'icon' 	=> 'eicon-text-align-right',
							],
						],
						'toggle' 	=> true,
						'selectors' => [
							'{{WRAPPER}} .ova_product_categories .item .counts' => 'text-align: {{VALUE}}',
						],
					]
				);

			$this->end_controls_section();
			
			// SECTION TAB STYLE NAV
			$this->start_controls_section(
				'section_nav_item',
				[
					'label' => esc_html__( 'Nav', 'ova-brw' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

			    $this->add_responsive_control(
					'nav_size',
					[
						'label' 		=> esc_html__( 'Size', 'ova-brw' ),
						'type' 			=> \Elementor\Controls_Manager::SLIDER,
						'size_units' 	=> [ 'px' ],
						'range' 		=> [
							'px' => [
								'min' 	=> 1,
								'max' 	=> 300,
								'step' 	=> 1,
							]
						],
						'selectors' 	=> [
							'{{WRAPPER}} .ova_product_categories .swiper-nav .button-nav' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
						],
					]
				);

				$this->add_responsive_control(
					'nav_icon_size',
					[
						'label' 	=> esc_html__( 'Icon Size', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::SLIDER,
						'range' 	=> [
							'px' => [
								'min' => 0,
								'max' => 40,
							],
						],
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova_product_categories .swiper-nav .button-nav i' => 'font-size: {{SIZE}}{{UNIT}};',
						],
					]
				);

				$this->add_responsive_control(
					'nav_top_position',
					[
						'label' => esc_html__( 'Top Position', 'ova-brw' ),
						'type' 	=> \Elementor\Controls_Manager::SLIDER,
						'range' => [
							'px' => [
								'min' => -150,
								'max' => 450,
							],
						],
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova_product_categories .swiper-nav' => 'bottom: calc(100% + {{SIZE}}{{UNIT}});',
						],
					]
				);

				$this->add_responsive_control(
					'nav_right_position',
					[
						'label' => esc_html__( 'Right Position', 'ova-brw' ),
						'type' 	=> \Elementor\Controls_Manager::SLIDER,
						'range' => [
							'px' => [
								'min' => 0,
								'max' => 450,
							],
							'%' => [
								'min' => 0,
								'max' => 150,
							],
						],
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova_product_categories .swiper-nav' => 'right: {{SIZE}}{{UNIT}};',
						],
					]
				);

				$this->start_controls_tabs( 'tabs_nav_style' );

					$this->start_controls_tab(
			            'tab_nav_normal',
			            [
			                'label' => esc_html__( 'Normal', 'ova-brw' ),
			            ]
			        );

						$this->add_control(
				            'nav_color_normal',
				            [
				                'label' 	=> esc_html__( 'Color', 'ova-brw' ),
				                'type' 		=> \Elementor\Controls_Manager::COLOR,
				                'selectors' => [
				                    '{{WRAPPER}} .ova_product_categories .swiper-nav .button-nav i' => 'color: {{VALUE}}',
				                ],
				            ]
				        );

				        $this->add_control(
				            'nav_color_border_normal',
				            [
				                'label' 	=> esc_html__( 'Border Color', 'ova-brw' ),
				                'type' 		=> \Elementor\Controls_Manager::COLOR,
				                'selectors' => [
				                    '{{WRAPPER}} .ova_product_categories .swiper-nav .button-nav.button-prev, {{WRAPPER}} .ova_product_categories .swiper-nav .button-nav.button-next' => 'border-color: {{VALUE}}',
				                ],
				            ]
				        );

				        $this->add_control(
				            'nav_bgcolor_normal',
				            [
				                'label' 	=> esc_html__( 'Background Color', 'ova-brw' ),
				                'type' 		=> \Elementor\Controls_Manager::COLOR,
				                'selectors' => [
				                    '{{WRAPPER}} .ova_product_categories .swiper-nav .button-nav.button-prev, {{WRAPPER}} .ova_product_categories .swiper-nav .button-nav.button-next' => 'background-color: {{VALUE}}',
				                ],
				            ]
				        );

					$this->end_controls_tab();

					$this->start_controls_tab(
			            'tab_nav_hover',
			            [
			                'label' => esc_html__( 'Hover', 'ova-brw' ),
			            ]
			        );

						$this->add_control(
				            'nav_color_hover',
				            [
				                'label' 	=> esc_html__( 'Color', 'ova-brw' ),
				                'type' 		=> \Elementor\Controls_Manager::COLOR,
				                'selectors' => [
				                    '{{WRAPPER}} .ova_product_categories .swiper-nav .button-nav:hover i' => 'color: {{VALUE}}',
				                ],
				            ]
				        );

				         $this->add_control(
				            'nav_color_border_hover',
				            [
				                'label' 	=> esc_html__( 'Border Color', 'ova-brw' ),
				                'type' 		=> \Elementor\Controls_Manager::COLOR,
				                'selectors' => [
				                    '{{WRAPPER}} .ova_product_categories .swiper-nav .button-nav:hover ' => 'border-color: {{VALUE}}',
				                ],
				            ]
				        );

				        $this->add_control(
				            'nav_bgcolor_hover',
				            [
				                'label' 	=> esc_html__( 'Background Color', 'ova-brw' ),
				                'type' 		=> \Elementor\Controls_Manager::COLOR,
				                'selectors' => [
				                    '{{WRAPPER}} .ova_product_categories .swiper-nav .button-nav:hover ' => 'background-color: {{VALUE}}',
				                ],
				            ]
				        );

					$this->end_controls_tab();
				$this->end_controls_tabs();

				$this->add_control(
		            'nav_border_radius',
		            [
		                'label' 		=> esc_html__( 'Border Radius', 'ova-brw' ),
		                'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
		                'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
		                'selectors' 	=> [
		                    '{{WRAPPER}} .ova_product_categories .swiper-nav .button-nav' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		                ],
		            ]
		        );


			$this->end_controls_section(); // END SECTION TAB STYLE NAV
			
			// SECTION TAB STYLE DOT CONTROL (MOBILE)
			$this->start_controls_section(
	            'product_dots_style',
	            [
	                'label' => esc_html__( 'Dots (Mobile)', 'ova-brw' ),
	                'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
	            ]
	        );

				$this->start_controls_tabs( 'product_tabs_dots_style' );
					
					$this->start_controls_tab(
			            'product_tab_dots_normal',
			            [
			                'label' => esc_html__( 'Normal', 'ova-brw' ),
			            ]
			        );

			        	$this->add_group_control(
							\Elementor\Group_Control_Background::get_type(),
							[
								'name' 		=> 'product_dots_bg_gradient',
								'label' 	=> esc_html__( 'Background Gradient', 'ova-brw' ),
								'types' 	=> [ 'classic', 'gradient' ],
								'exclude' 	=> [ 'image' ],
								'selector' 	=> '{{WRAPPER}} .ova_product_categories .button-dots .swiper-pagination-bullet',
							]
						);

			        	$this->add_control(
							'opacity_dots',
							[
								'label' 	=> esc_html__( 'Opacity Dots', 'ova-brw' ),
								'type' 		=> \Elementor\Controls_Manager::SLIDER,
								'default' 	=> [
									'size' => 0.2,
								],
								'range' 	=> [
									'px' => [
										'max' 	=> 1,
										'step' 	=> 0.01,
									],
								],
								'selectors' => [
									'{{WRAPPER}} .ova_product_categories .button-dots .swiper-pagination-bullet' => 'opacity: {{SIZE}};',
								],
							]
						);

						$this->add_responsive_control(
							'dots_width',
							[
								'label' => esc_html__( 'Width', 'ova-brw' ),
								'type' 	=> \Elementor\Controls_Manager::SLIDER,
								'range' => [
									'px' => [
										'min' => 0,
										'max' => 100,
									],
								],
								'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
								'selectors' 	=> [
									'{{WRAPPER}} .ova_product_categories .button-dots .swiper-pagination-bullet' => 'width: {{SIZE}}{{UNIT}};',
								],
							]
						);

						$this->add_responsive_control(
							'dots_height',
							[
								'label' => esc_html__( 'Height', 'ova-brw' ),
								'type' 	=> \Elementor\Controls_Manager::SLIDER,
								'range' => [
									'px' => [
										'min' => 0,
										'max' => 100,
									],
								],
								'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
								'selectors' 	=> [
									'{{WRAPPER}} .ova_product_categories .button-dots .swiper-pagination-bullet' => 'height: {{SIZE}}{{UNIT}};',
								],
							]
						);

						$this->add_group_control(
							\Elementor\Group_Control_Background::get_type(),
							[
								'name' 		=> 'dots_bg_gradient',
								'label' 	=> esc_html__( 'Background Gradient', 'ova-brw' ),
								'types' 	=> [ 'classic', 'gradient' ],
								'exclude' 	=> [ 'image' ],
								'selector' 	=> '{{WRAPPER}} .ova_product_categories .button-dots .swiper-pagination-bullet',
							]
						);

						$this->add_control(
				            'dots_border_radius',
				            [
				                'label' 		=> esc_html__( 'Border Radius', 'ova-brw' ),
				                'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
				                'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
				                'selectors' 	=> [
				                    '{{WRAPPER}} .ova_product_categories .button-dots .swiper-pagination-bullet' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				                ],
				            ]
				        );

			        $this->end_controls_tab();

			        $this->start_controls_tab(
			            'product_tab_dots_hover',
			            [
			                'label' => esc_html__( 'Hover', 'ova-brw' ),
			            ]
			        );

				        $this->add_group_control(
							\Elementor\Group_Control_Background::get_type(),
							[
								'name' 		=> 'product_dots_bg_gradient_hover',
								'label' 	=> esc_html__( 'Background Gradient', 'ova-brw' ),
								'types' 	=> [ 'classic', 'gradient' ],
								'exclude' 	=> [ 'image' ],
								'selector' 	=> '{{WRAPPER}} .ova_product_categories .button-dots .swiper-pagination-bullet:hover',
							]
						);

			        $this->end_controls_tab();

			        $this->start_controls_tab(
			            'product_tab_dots_active',
			            [
			                'label' => esc_html__( 'Active', 'ova-brw' ),
			            ]
			        );

				        $this->add_group_control(
							\Elementor\Group_Control_Background::get_type(),
							[
								'name' 		=> 'product_dots_bg_gradient_active',
								'label' 	=> esc_html__( 'Background Gradient', 'ova-brw' ),
								'types' 	=> [ 'classic', 'gradient' ],
								'exclude' 	=> [ 'image' ],
								'selector' 	=> '{{WRAPPER}} .ova_product_categories .button-dots .swiper-pagination-bullet.swiper-pagination-bullet-active',
							]
						);

						$this->add_control(
							'opacity_dots_active',
							[
								'label' 	=> esc_html__( 'Opacity Dots', 'ova-brw' ),
								'type' 		=> \Elementor\Controls_Manager::SLIDER,
								'default' 	=> [
									'size' => 1,
								],
								'range' => [
									'px' => [
										'max' 	=> 1,
										'step' 	=> 0.01,
									],
								],
								'selectors' => [
									'{{WRAPPER}} .ova_product_categories .button-dots .swiper-pagination-bullet.swiper-pagination-bullet-active' => 'opacity: {{SIZE}};',
								],
							]
						);

						$this->add_responsive_control(
							'dots_width_active',
							[
								'label' => esc_html__( 'Width', 'ova-brw' ),
								'type' 	=> \Elementor\Controls_Manager::SLIDER,
								'range' => [
									'px' => [
										'min' => 0,
										'max' => 100,
									],
								],
								'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
								'selectors' 	=> [
									'{{WRAPPER}} .ova_product_categories .button-dots .swiper-pagination-bullet.swiper-pagination-bullet-active' => 'width: {{SIZE}}{{UNIT}};',
								],
							]
						);

						$this->add_responsive_control(
							'dots_height_active',
							[
								'label' => esc_html__( 'Height', 'ova-brw' ),
								'type' 	=> \Elementor\Controls_Manager::SLIDER,
								'range' => [
									'px' => [
										'min' => 0,
										'max' => 100,
									],
								],
								'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
								'selectors' 	=> [
									'{{WRAPPER}} .ova_product_categories .button-dots .swiper-pagination-bullet.swiper-pagination-bullet-active' => 'height: {{SIZE}}{{UNIT}};',
								],
							]
						);

						$this->add_control(
				            'product_dots_border_radius_active',
				            [
				                'label' 		=> esc_html__( 'Border Radius', 'ova-brw' ),
				                'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
				                'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
				                'selectors' 	=> [
				                    '{{WRAPPER}} .ova_product_categories .button-dots .swiper-pagination-bullet.swiper-pagination-bullet-active' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				                ],
				            ]
				        );

			        $this->end_controls_tab();
				$this->end_controls_tabs();
	        $this->end_controls_section(); // END
		}

		/**
		 * Render HTML
		 */
		protected function render() {
			// Get settings
			$settings = $this->get_settings_for_display();

			// Get template
			ovabrw_get_template( apply_filters( OVABRW_PREFIX.'element_product_categories_template', 'elementor/product_categories.php' ), $settings ); 
		}
	}

	// Regiser new widget
	$widgets_manager->register( new OVABRW_Product_Categories() );
}