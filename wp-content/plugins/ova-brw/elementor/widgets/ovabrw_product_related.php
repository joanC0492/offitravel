<?php if ( !defined( 'ABSPATH' ) ) exit(); // Exit if accessed directly

/**
 * Class OVABRW_Product_Related
 */
if ( !class_exists( 'OVABRW_Product_Related', false ) ) {

	class OVABRW_Product_Related extends \Elementor\Widget_Base {

		/**
		 * Get widget name
		 */
		public function get_name() {		
			return 'ovabrw_product_related';
		}

		/**
		 * Get widget title
		 */
		public function get_title() {
			return esc_html__( 'Product Related', 'ova-brw' );
		}

		/**
		 * Get widget icon
		 */
		public function get_icon() {
			return 'eicon-product-related';
		}

		/**
		 * Get widget categories
		 */
		public function get_categories() {
			return [ 'ovabrw-product-templates' ];
		}

		/**
		 * Get style depends
		 */
		public function get_style_depends() {
			return [ 'swipe' ];
		}

		/**
		 * Get script depends
		 */
		public function get_script_depends() {
			return [ 'swipe', 'ovabrw-elementor-product-related' ];
		}

		/**
		 * Register controls
		 */
		protected function register_controls() {
			$this->start_controls_section(
				'section_product_related_style',
				[
					'label' => esc_html__( 'Content', 'ova-brw' ),
				]
			);

			    $this->add_control(
					'wc_content_warning',
					[
						'type' 	=> \Elementor\Controls_Manager::RAW_HTML,
						'raw' 	=> esc_html__( "Don't enter Product ID if you use this element in templates for product detail page.In Elementor Preview ( When empty Product ID ) , this element display an example product related of the latest product", 'ova-brw' ),
						'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
					]
				);

				// Products
				$products = [];

				// Default product
				$default_product = '';

				// Get product ids
				$product_ids = ovabrw_get_all_id_product();
				if ( ovabrw_array_exists( $product_ids ) ) {
					// Default
					if ( !$default_product ) $default_product = $product_ids[0];
					
					foreach ( $product_ids as $product_id ) {
						$products[$product_id] = get_the_title( $product_id );
					}
				} else {
					$products[''] = esc_html__( 'No tour products.', 'ova-brw' );
				}

				$this->add_control(
					'product_id',
					[
						'label'  	=> esc_html__( 'Product ID', 'ova-brw' ),
						'type'   	=> \Elementor\Controls_Manager::SELECT,
						'options' 	=> $products,
						'default' 	=> $default_product
					]
				);

				$this->add_control(
					'posts_per_page',
					[
						'label' 	=> esc_html__( 'Products Per Page', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::NUMBER,
						'default' 	=> 5,
						'range' 	=> [
							'px' => [
								'max' => 20,
							],
						],
					]
				);

				$this->add_control(
					'orderby',
					[
						'label' 	=> esc_html__( 'Order By', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::SELECT,
						'default' 	=> 'date',
						'options' 	=> [
							'date' 			=> esc_html__( 'Date', 'ova-brw' ),
							'title' 		=> esc_html__( 'Title', 'ova-brw' ),
							'price' 		=> esc_html__( 'Price', 'ova-brw' ),
							'popularity' 	=> esc_html__( 'Popularity', 'ova-brw' ),
							'rating' 		=> esc_html__( 'Rating', 'ova-brw' ),
							'rand' 			=> esc_html__( 'Random', 'ova-brw' ),
							'menu_order' 	=> esc_html__( 'Menu Order', 'ova-brw' )
						],
					]
				);

				$this->add_control(
					'order',
					[
						'label' 	=> esc_html__( 'Order', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::SELECT,
						'default' 	=> 'desc',
						'options' 	=> [
							'asc' 	=> esc_html__( 'ASC', 'ova-brw' ),
							'desc' 	=> esc_html__( 'DESC', 'ova-brw' ),
						],
					]
				);


			$this->end_controls_section();

			// Additional options
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
						'default' => 24,
					]
					
				);

				$this->add_control(
					'item_number',
					[
						'label'       => esc_html__( 'Item Number', 'ova-brw' ),
						'type'        => \Elementor\Controls_Manager::NUMBER,
						'description' => esc_html__( 'Number Item', 'ova-brw' ),
						'default'     => 4,
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

				$this->add_control(
					'dot_control',
					[
						'label'   => esc_html__( 'Show Dots', 'ova-brw' ),
						'type'    => \Elementor\Controls_Manager::SWITCHER,
						'default' => 'yes',
						'options' => [
							'yes' => esc_html__( 'Yes', 'ova-brw' ),
							'no'  => esc_html__( 'No', 'ova-brw' )
						],
						'frontend_available' => true
					]
				);

			$this->end_controls_section(); // END SECTION ADDITIONAL

			// STYLE
			$this->start_controls_section(
				'section_product_slider_style',
				[
					'label' => esc_html__( 'Content', 'ova-brw' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_control(
					'content_background',
					[
						'label' 	=> esc_html__( 'Background', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-product-slider .ova-product .ova_foot_product' => 'background-color: {{VALUE}}',
						],
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Box_Shadow::get_type(),
					[
						'name' 		=> 'content_box_shadow',
						'label' 	=> esc_html__( 'Box Shadow', 'ova-brw' ),
						'selector' 	=> '{{WRAPPER}} .ova-product-slider .ova-product',
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Border::get_type(),
					[
						'name' 		=> 'content_border',
						'label' 	=> esc_html__( 'Border', 'ova-brw' ),
						'selector' 	=> '{{WRAPPER}} .ova-product-slider .ova-product .ova_foot_product',
						'separator' => 'before',
					]
				);

				$this->add_responsive_control(
					'content_border_radius',
					[
						'label' 		=> esc_html__( 'Border Radius', 'ova-brw' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-product-slider .ova-product' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_responsive_control(
					'content_padding',
					[
						'label' 		=> esc_html__( 'Padding', 'ova-brw' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-product-slider .ova-product .ova_foot_product' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

			$this->end_controls_section();

			// Tour Day style
			$this->start_controls_section(
				'section_tour_day_style',
				[
					'label' => esc_html__( 'Tour Day', 'ova-brw' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

			    $this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'tour_day_typography',
						'selector' 	=> '{{WRAPPER}} .ova-product-slider .ova-product .ova_head_product .ova-tour-day',
					]
				);

				$this->add_control(
					'tour_day_color',
					[
						'label' 	=> esc_html__( 'Color', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-product-slider .ova-product .ova_head_product .ova-tour-day' => 'color: {{VALUE}}',
						],
					]
				);

				$this->add_control(
					'tour_day_bgcolor',
					[
						'label' 	=> esc_html__( 'Background Color', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-product-slider .ova-product .ova_head_product .ova-tour-day' => 'background-color: {{VALUE}}',
						],
					]
				);

				$this->add_responsive_control(
					'tour_day_padding',
					[
						'label' 		=> esc_html__( 'Padding', 'ova-brw' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-product-slider .ova-product .ova_head_product .ova-tour-day' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Border::get_type(),
					[
						'name' 		=> 'tour_day_border',
						'label' 	=> esc_html__( 'Border', 'ova-brw' ),
						'selector' 	=> '{{WRAPPER}} .ova-product-slider .ova-product .ova_head_product .ova-tour-day',
					]
				);

			$this->end_controls_section();

			// Is Featured style
			$this->start_controls_section(
				'section_is_featured_style',
				[
					'label' => esc_html__( 'Is Featured', 'ova-brw' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

			    $this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'is_featured_typography',
						'selector' 	=> '{{WRAPPER}} .ova-product-slider .ova-product .ova_head_product .ova-is-featured',
					]
				);

				$this->add_control(
					'is_featured_color',
					[
						'label' 	=> esc_html__( 'Color', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-product-slider .ova-product .ova_head_product .ova-is-featured' => 'color: {{VALUE}}',
						],
					]
				);

				$this->add_control(
					'is_featured_bgcolor',
					[
						'label' 	=> esc_html__( 'Background Color', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-product-slider .ova-product .ova_head_product .ova-is-featured' => 'background-color: {{VALUE}}',
						],
					]
				);

				$this->add_responsive_control(
					'is_featured_padding',
					[
						'label' 		=> esc_html__( 'Padding', 'ova-brw' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-product-slider .ova-product .ova_head_product .ova-is-featured' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

			$this->end_controls_section();

			// Favorite style
			$this->start_controls_section(
				'section_favorite_style',
				[
					'label' => esc_html__( 'Favorite', 'ova-brw' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

			    $this->add_responsive_control(
					'favourite_size',
					[
						'label' 		=> esc_html__( 'Size', 'ova-brw' ),
						'type' 			=> \Elementor\Controls_Manager::SLIDER,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'range' 		=> [
							'px' => [
								'min' => 0,
								'max' => 50,
							],
						],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-product-slider .ova-product .ova_head_product .ova-product-wishlist .yith-wcwl-add-to-wishlist .yith-wcwl-add-button .add_to_wishlist i' => 'font-size: {{SIZE}}{{UNIT}};',
						],
					]
				);

				$this->add_control(
					'favorite_color',
					[
						'label' 	=> esc_html__( 'Color', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-product-slider .ova-product .ova_head_product .ova-product-wishlist .yith-wcwl-add-to-wishlist .yith-wcwl-add-button .add_to_wishlist i' => 'color: {{VALUE}}',
						],
					]
				);

				$this->add_control(
					'favorite_bgcolor',
					[
						'label' 	=> esc_html__( 'Background Color', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-product-slider .ova-product .ova_head_product .ova-product-wishlist .yith-wcwl-add-to-wishlist .yith-wcwl-add-button' => 'background-color: {{VALUE}}',
						],
					]
				);

			$this->end_controls_section();
	        
	        // Title
			$this->start_controls_section(
				'section_title_style',
				[
					'label' => esc_html__( 'Title', 'ova-brw' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'title_typography',
						'selector' 	=> '{{WRAPPER}} .ova-product-slider .ova-product .ova_foot_product .ova-product-title a',
					]
				);


				$this->add_control(
					'title_normal_color',
					[
						'label' 	=> esc_html__( 'Color', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-product-slider .ova-product .ova_foot_product .ova-product-title a' => 'color: {{VALUE}}',
						],
					]
				);


				$this->add_control(
					'title_hover_color',
					[
						'label' 	=> esc_html__( 'Color Hover', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-product-slider .ova-product .ova_foot_product .ova-product-title:hover a' => 'color: {{VALUE}}',
						],
					]
				);

				$this->add_responsive_control(
					'title_padding',
					[
						'label' 		=> esc_html__( 'Padding', 'ova-brw' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-product-slider .ova-product .ova_foot_product .ova-product-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

			$this->end_controls_section();

			$this->start_controls_section(
				'section_review_style',
				[
					'label' => esc_html__( 'Review', 'ova-brw' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_control(
					'star_color',
					[
						'label' 	=> esc_html__( 'Star Color', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-product-slider .ova-product .ova_foot_product .ova-product-review .star-rating' => 'color: {{VALUE}}',
						],
					]
				);


			$this->end_controls_section();

			$this->start_controls_section(
				'section_price_style',
				[
					'label' => esc_html__( 'Price', 'ova-brw' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

			    $this->add_control(
					'new_price_options',
					[
						'label' 	=> esc_html__( 'New Price', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::HEADING,
						'separator' => 'before',
					]
				);

					$this->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' 		=> 'new_price_typography',
							'selector' 	=> '{{WRAPPER}} .ova-product-slider .ova-product .ova_foot_product .ova-product-wrapper-price .ova-product-price .new-product-price',
						]
					);

					$this->add_control( 
						'new_price_color',
						[
							'label' 	=> esc_html__( 'Color', 'ova-brw' ),
							'type' 		=> \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ova-product-slider .ova-product .ova_foot_product .ova-product-wrapper-price .ova-product-price .new-product-price' => 'color: {{VALUE}}',
							],
						]
					);

				$this->add_control(
					'old_price_options',
					[
						'label' 	=> esc_html__( 'Old Price', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::HEADING,
						'separator' => 'before',
					]
				);

					$this->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' 		=> 'old_price_typography',
							'selector' 	=> '{{WRAPPER}} .ova-product-slider .ova-product .ova_foot_product .ova-product-wrapper-price .ova-product-price .old-product-price',
						]
					);

					$this->add_control(
						'old_price_color',
						[
							'label' 	=> esc_html__( 'Color', 'ova-brw' ),
							'type' 		=> \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ova-product-slider .ova-product .ova_foot_product .ova-product-wrapper-price .ova-product-price .old-product-price' => 'color: {{VALUE}}',
							],
						]
					);

				$this->add_control(
					'negotiable_price_options',
					[
						'label' 	=> esc_html__( 'Negotiable Price', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::HEADING,
						'separator' => 'before',
					]
				);

					$this->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' 		=> 'negotiable_price_typography',
							'selector' 	=> '{{WRAPPER}} .ova-product-slider .ova-product .ova_foot_product .ova-product-wrapper-price .ova-product-price .no-product-price',
						]
					);

					$this->add_control(
						'negotiable_price_color',
						[
							'label' 	=> esc_html__( 'Color', 'ova-brw' ),
							'type' 		=> \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ova-product-slider .ova-product .ova_foot_product .ova-product-wrapper-price .ova-product-price .no-product-price' => 'color: {{VALUE}}',
							],
						]
					);

			$this->end_controls_section();

			// Button style
			$this->start_controls_section(
				'section_button',
				[
					'label' => esc_html__( 'Button', 'ova-brw' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

			$this->start_controls_tabs(
				'style_tabs_button'
			);

				$this->start_controls_tab(
					'style_normal_tab',
					[
						'label' => esc_html__( 'Normal', 'ova-brw' ),
					]
				);

					$this->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' 		=> 'button_typography',		
							'label' 	=> esc_html__( 'Typography', 'ova-brw' ),
							'selector' 	=> '{{WRAPPER}} .ova-product-slider .ova-product .ova_foot_product .ova-product-wrapper-price .product-btn-book-now',
							
						]
					);

					$this->add_control(	
						'color_button',
						[
							'label' 	=> esc_html__( 'Color', 'ova-brw' ),
							'type' 		=> \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ova-product-slider .ova-product .ova_foot_product .ova-product-wrapper-price .product-btn-book-now' => 'color : {{VALUE}};',
							],
						]
					);

					$this->add_control(
						'color_button_background',
						[
							'label' 	=> esc_html__( 'Background ', 'ova-brw' ),
							'type' 		=> \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ova-product-slider .ova-product .ova_foot_product .ova-product-wrapper-price .product-btn-book-now' => 'background-color : {{VALUE}};',
							],
						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Border::get_type(),
						[
							'name' 		=> 'button_border',
							'label' 	=> esc_html__( 'Border', 'ova-brw' ),
							'selector' 	=> '{{WRAPPER}} .ova-product-slider .ova-product .ova_foot_product .ova-product-wrapper-price .product-btn-book-now',
						]
					);
					
					$this->add_control(
						'border_radius_button',
						[
							'label'      => esc_html__( 'Border Radius', 'ova-brw' ),
							'type'       => \Elementor\Controls_Manager::DIMENSIONS,
							'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
							'selectors'  => [
								'{{WRAPPER}} .ova-product-slider .ova-product .ova_foot_product .ova-product-wrapper-price .product-btn-book-now' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							],
						]
					);

				$this->end_controls_tab();

				$this->start_controls_tab(
					'style_hover_tab',
					[
						'label' => esc_html__( 'Hover', 'ova-brw' ),
					]
				);

					$this->add_control(	
						'color_button_hover',
						[
							'label' 	=> esc_html__( 'Color', 'ova-brw' ),
							'type' 		=> \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ova-product-slider .ova-product:hover .ova_foot_product .ova-product-wrapper-price .product-btn-book-now' => 'color : {{VALUE}};',
							],
						]
					);

					$this->add_control(
						'color_button_background_hover',
						[
							'label' 	=> esc_html__( 'Background ', 'ova-brw' ),
							'type' 		=> \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ova-product-slider .ova-product:hover .ova_foot_product .ova-product-wrapper-price .product-btn-book-now' => 'background-color : {{VALUE}};',
							],
						]
					);

					$this->add_group_control(
						\Elementor\Group_Control_Border::get_type(),
						[
							'name' 		=> 'button_border_hover',
							'label' 	=> esc_html__( 'Border', 'ova-brw' ),
							'selector' 	=> '{{WRAPPER}} .ova-product-slider .ova-product:hover .ova_foot_product .ova-product-wrapper-price .product-btn-book-now',
						]
					);

				$this->end_controls_tab();

			$this->end_controls_tabs();

			$this->end_controls_section();

			/* Begin Nav Style */
			$this->start_controls_section(
	            'nav_style',
	            [
	                'label' 	=> esc_html__( 'Nav Control', 'ova-brw' ),
	                'tab' 		=> \Elementor\Controls_Manager::TAB_STYLE,
	                'condition' => [
						'nav_control' => 'yes',
					]
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
							'{{WRAPPER}} .ova-product-slider .swiper-nav i' => 'font-size: {{SIZE}}{{UNIT}};',
						],
					]
				);

				$this->add_responsive_control(
					'nav_top_position',
					[
						'label' 	=> esc_html__( 'Top Position', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::SLIDER,
						'range' 	=> [
							'px' => [
								'min' => -150,
								'max' => 450,
							],
							'%' => [
								'min' => -150,
								'max' => 150,
							],
						],
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-product-slider.elementor-ralated .swiper-nav .button-nav' => 'top: {{SIZE}}{{UNIT}};',
						],
					]
				);

				$this->add_responsive_control(
					'nav_right_position',
					[
						'label' 	=> esc_html__( 'Right Position', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::SLIDER,
						'range' 	=> [
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
							'{{WRAPPER}} .ova-product-slider.elementor-ralated .swiper-nav .button-nav' => 'right: {{SIZE}}{{UNIT}};',
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
				                    '{{WRAPPER}} .ova-product-slider.elementor-ralated .swiper-nav .button-nav i' => 'color: {{VALUE}}',
				                ],
				            ]
				        );

				        $this->add_control(
				            'nav_color_border_normal',
				            [
				                'label' 	=> esc_html__( 'Border Color', 'ova-brw' ),
				                'type' 		=> \Elementor\Controls_Manager::COLOR,
				                'selectors' => [
				                    '{{WRAPPER}} .ova-product-slider.elementor-ralated .swiper-nav .button-nav.button-next, {{WRAPPER}} .ova-product-slider.elementor-ralated .swiper-nav .button-nav.button-prev' => 'border-color: {{VALUE}}',
				                ],
				            ]
				        );

				        $this->add_control(
				            'nav_bgcolor_normal',
				            [
				                'label' 	=> esc_html__( 'Background Color', 'ova-brw' ),
				                'type' 		=> \Elementor\Controls_Manager::COLOR,
				                'selectors' => [
				                    '{{WRAPPER}} .ova-product-slider.elementor-ralated .swiper-nav .button-nav.button-next, {{WRAPPER}} .ova-product-slider.elementor-ralated .swiper-nav .button-nav.button-prev' => 'background-color: {{VALUE}}',
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
				                    '{{WRAPPER}} .ova-product-slider.elementor-ralated .swiper-nav .button-nav:hover i' => 'color: {{VALUE}}',
				                ],
				            ]
				        );

				         $this->add_control(
				            'nav_color_border_hover',
				            [
				                'label' 	=> esc_html__( 'Border Color', 'ova-brw' ),
				                'type' 		=> \Elementor\Controls_Manager::COLOR,
				                'selectors' => [
				                    '{{WRAPPER}} .ova-product-slider.elementor-ralated .swiper-nav .button-nav.button-next:hover, {{WRAPPER}} .ova-product-slider.elementor-ralated .swiper-nav .button-nav.button-prev:hover' => 'border-color: {{VALUE}}',
				                ],
				            ]
				        );

				        $this->add_control(
				            'nav_bgcolor_hover',
				            [
				                'label' 	=> esc_html__( 'Background Color', 'ova-brw' ),
				                'type' 		=> \Elementor\Controls_Manager::COLOR,
				                'selectors' => [
				                    '{{WRAPPER}} .ova-product-slider.elementor-ralated .swiper-nav .button-nav.button-next:hover, {{WRAPPER}} .ova-product-slider.elementor-ralated .swiper-nav .button-nav.button-prev:hover' => 'background-color: {{VALUE}}',
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
		                    '{{WRAPPER}} .ova-product-slider.elementor-ralated .swiper-nav .button-nav' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		                ],
		            ]
		        );

	        $this->end_controls_section(); /* End Nav Style */

			/* Begin Dots Style */
			$this->start_controls_section(
	            'dots_style',
	            [
	                'label' 	=> esc_html__( 'Dots (Mobile)', 'ova-brw' ),
	                'tab' 		=> \Elementor\Controls_Manager::TAB_STYLE,
	                'condition' => [
						'dot_control' => 'yes',
					]
	            ]
	        );

	            $this->add_responsive_control(
					'dots_margin',
					[
						'label'      => esc_html__( 'Margin', 'ova-brw' ),
						'type'       => \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors'  => [
							'{{WRAPPER}} .ova-product-slider .button-dots' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->start_controls_tabs( 'tabs_dots_style' );
					
					$this->start_controls_tab(
			            'tab_dots_normal',
			            [
			                'label' => esc_html__( 'Normal', 'ova-brw' ),
			            ]
			        );

			            $this->add_control(
							'dot_color',
							[
								'label' 	=> esc_html__( 'Color', 'ova-brw' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ova-product-slider .button-dots .swiper-pagination-bullet' => 'background-color: {{VALUE}}',
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
									'{{WRAPPER}} .ova-product-slider .button-dots .swiper-pagination-bullet' => 'width: {{SIZE}}{{UNIT}};',
								],
							]
						);

						$this->add_responsive_control(
							'dots_height',
							[
								'label' 	=> esc_html__( 'Height', 'ova-brw' ),
								'type' 		=> \Elementor\Controls_Manager::SLIDER,
								'range' 	=> [
									'px' 	=> [
										'min' => 0,
										'max' => 100,
									],
								],
								'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
								'selectors' 	=> [
									'{{WRAPPER}} .ova-product-slider .button-dots .swiper-pagination-bullet' => 'height: {{SIZE}}{{UNIT}};',
								],
							]
						);

						$this->add_control(
				            'dots_border_radius',
				            [
				                'label' 		=> esc_html__( 'Border Radius', 'ova-brw' ),
				                'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
				                'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
				                'selectors' 	=> [
				                    '{{WRAPPER}} .ova-product-slider .button-dots .swiper-pagination-bullet' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				                ],
				            ]
				        );

			        $this->end_controls_tab();

			        $this->start_controls_tab(
			            'tab_dots_active',
			            [
			                'label' => esc_html__( 'Active', 'ova-brw' ),
			            ]
			        );

			             $this->add_control(
							'dot_color_active',
							[
								'label' 	=> esc_html__( 'Color', 'ova-brw' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ova-product-slider .button-dots .swiper-pagination-bullet.swiper-pagination-bullet-active' => 'background-color: {{VALUE}}',
								],
							]
						);

						$this->add_responsive_control(
							'dots_width_active',
							[
								'label' 	=> esc_html__( 'Width', 'ova-brw' ),
								'type' 		=> \Elementor\Controls_Manager::SLIDER,
								'range' 	=> [
									'px' 	=> [
										'min' => 0,
										'max' => 100,
									],
								],
								'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
								'selectors' 	=> [
									'{{WRAPPER}} .ova-product-slider .button-dots .swiper-pagination-bullet.swiper-pagination-bullet-active' => 'width: {{SIZE}}{{UNIT}};',
								],
							]
						);

						$this->add_responsive_control(
							'dots_height_active',
							[
								'label' 	=> esc_html__( 'Height', 'ova-brw' ),
								'type' 		=> \Elementor\Controls_Manager::SLIDER,
								'range' 	=> [
									'px' 	=> [
										'min' => 0,
										'max' => 100,
									],
								],
								'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
								'selectors' 	=> [
									'{{WRAPPER}} .ova-product-slider .button-dots .swiper-pagination-bullet.swiper-pagination-bullet-active' => 'height: {{SIZE}}{{UNIT}};',
								],
							]
						);

						$this->add_control(
				            'dots_border_radius_active',
				            [
				                'label' 		=> esc_html__( 'Border Radius', 'ova-brw' ),
				                'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
				                'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
				                'selectors' 	=> [
				                    '{{WRAPPER}} .ova-product-slider .button-dots .swiper-pagination-bullet.swiper-pagination-bullet-active' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				                ],
				            ]
				        );

			        $this->end_controls_tab();
				$this->end_controls_tabs();
	        $this->end_controls_section(); /* END Dots Style */
		}

		/**
		 * Render HTML
		 */
		protected function render() {
			// Get settings
			$settings = $this->get_settings_for_display();

			// Single product
			if ( is_product() ) {
				global $product;
			} else {
				$product = wc_get_product( ovabrw_get_meta_data( 'product_id', $settings ) );
			}

			// Check product
	    	if ( !$product || !$product->is_type( OVABRW_RENTAL ) ): ?>
				<div class="ovabrw_elementor_no_product">
					<span><?php echo wp_kses_post( $this->get_title() ); ?></span>
				</div>
				<?php return;
			endif;

	        // Slide options
			$slide_options = [
				'slidesPerView' 		=> ovabrw_get_meta_data( 'item_number', $settings, 4 ),
				'slidesPerGroup' 		=> ovabrw_get_meta_data( 'slides_to_scroll', $settings, 1 ),
				'spaceBetween' 			=> ovabrw_get_meta_data( 'margin_items', $settings, 24 ),
				'autoplay' 				=> 'yes' === ovabrw_get_meta_data( 'autoplay', $settings ) ? true : false,
				'pauseOnMouseEnter' 	=> 'yes' === ovabrw_get_meta_data( 'pause_on_hover', $settings ) ? true : false,
				'delay' 				=> ovabrw_get_meta_data( 'autoplay_speed', $settings, 3000 ),
				'speed' 				=> ovabrw_get_meta_data( 'smartspeed', $settings, 500 ),
				'loop' 					=> 'yes' === ovabrw_get_meta_data( 'infinite', $settings ) ? true : false,
				'nav' 					=> 'yes' === ovabrw_get_meta_data( 'nav_control', $settings ) ? true : false,
				'dots' 					=> 'yes' === ovabrw_get_meta_data( 'dot_control', $settings ) ? true : false,
				'breakpoints' 			=> [
					'0' 	=> [
						'slidesPerView' => 1
					],
		        	'600' 	=> [
		        		'slidesPerView' => 2
		        	],
		        	'960' 	=> [
		        		'slidesPerView' => ovabrw_get_meta_data( 'item_number', $settings, 4 ) - 1
		        	],
		        	'1200' 	=> [
		        		'slidesPerView' => ovabrw_get_meta_data( 'item_number', $settings, 4 )
		        	]
				],
				'rtl' 					=> is_rtl() ? true: false
			];

			// Query arguments
			$args = [
				'posts_per_page' 	=> ovabrw_get_meta_data( 'posts_per_page', $settings, 5 ),
				'orderby' 			=> ovabrw_get_meta_data( 'orderby', $settings ),
				'order' 			=> ovabrw_get_meta_data( 'order', $settings )
			];

			// Get visible related products then sort them at random.
			$args['related_products'] = array_filter( array_map( 'wc_get_product', wc_get_related_products( $product->get_id(), $args['posts_per_page'], $product->get_upsell_ids() ) ), 'wc_products_array_filter_visible' );

			// Handle orderby.
			$related_products = wc_products_array_orderby( $args['related_products'], $args['orderby'], $args['order'] );

			?>
	        <div class="elementor-ralated-slide">
	            <div class="ova-product-slider elementor-ralated" data-options="<?php echo esc_attr( json_encode( $slide_options ) ); ?>">
	            	<?php if ( ovabrw_array_exists( $related_products ) ): ?>
	            		<div class="swiper swiper-loading">
							<div class="swiper-wrapper">
				            	<?php foreach ( $related_products as $related_product ) {
									$post_object = get_post( $related_product->get_id() );
									setup_postdata( $GLOBALS['post'] =& $post_object );

									?>
									<div class="swiper-slide">
										<?php wc_get_template_part( 'content', 'product' ); ?>
									</div>
									<?php
								}
								
								$post_object = get_post( $product->get_id() );
								setup_postdata( $GLOBALS['post'] =& $post_object ); ?>
							</div>
						</div>
						<?php if ( 'yes' === $settings['nav_control'] ): ?>
							<div class="swiper-nav">
								<div class="button-nav button-prev">
									<i class="arrow_carrot-left" aria-hidden="true"></i>
								</div>
								<div class="button-nav button-next">
									<i class="arrow_carrot-right" aria-hidden="true"></i>
								</div>
							</div>
						<?php endif; ?>
						<?php if ( 'yes' === $settings['dot_control'] ): ?>
							<div class="button-dots"></div>
						<?php endif; ?>
			       	<?php else:
			       		esc_html_e( 'No products found.', 'ova-brw' );
			       	endif; ?>
				</div>
	        </div>	
			<?php
		}
	}

	// Regiser new widget
	$widgets_manager->register( new OVABRW_Product_Related() );
}