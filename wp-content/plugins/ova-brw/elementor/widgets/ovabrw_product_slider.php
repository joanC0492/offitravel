<?php if ( !defined( 'ABSPATH' ) ) exit(); // Exit if accessed directly

/**
 * Class OVABRW_Product_Slider
 */
if ( !class_exists( 'OVABRW_Product_Slider', false ) ) {

	class OVABRW_Product_Slider extends \Elementor\Widget_Base {

		/**
		 * Get widget name
		 */
		public function get_name() {		
			return 'ovabrw_product_slider';
		}

		/**
		 * Get widget title
		 */
		public function get_title() {
			return esc_html__( 'Product Slider', 'ova-brw' );
		}

		/**
		 * Get widget icon
		 */
		public function get_icon() {
			return 'eicon-posts-carousel';
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
			return [ 'swipe', 'ovabrw-elementor-product-slider' ];
		}

		/**
		 * Get script depends
		 */
		public function get_script_depends() {
	  		return [ 'swipe', 'ovabrw-elementor-product-slider' ];
		}

		/**
		 * Register controls
		 */
		protected function register_controls() {
			$this->start_controls_section(
				'section_product_slider_content',
				[
					'label' => esc_html__( 'Content', 'ova-brw' ),
				]
			);

				$this->add_control(
					'show_featured',
					[
						'label' 		=> esc_html__( 'Only Show Featured', 'ova-brw' ),
						'type' 			=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 		=> esc_html__( 'Yes', 'ova-brw' ),
						'label_off' 	=> esc_html__( 'No', 'ova-brw' ),
						'default' 		=> 'no',
					]
				);

				$this->add_control(
					'show_rental',
					[
						'label' 		=> esc_html__( 'Only Show Rental Products', 'ova-brw' ),
						'type' 			=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 		=> esc_html__( 'Yes', 'ova-brw' ),
						'label_off' 	=> esc_html__( 'No', 'ova-brw' ),
						'default' 		=> 'yes',
					]
				);

				$this->add_control(
					'show_on_sale',
					[
						'label' 		=> esc_html__( 'Only Show On Sale Products', 'ova-brw' ),
						'type' 			=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 		=> esc_html__( 'Yes', 'ova-brw' ),
						'label_off' 	=> esc_html__( 'No', 'ova-brw' ),
						'default' 		=> 'no',
					]
				);

				$this->add_control(
					'template',
					[
						'label' 	=> esc_html__( 'Template', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::SELECT,
						'default' 	=> 'template1',
						'options' 	=> [
							'template1' => esc_html__( 'Template 1', 'ova-brw' ),
							'template2' => esc_html__( 'Template 2', 'ova-brw' ),
						],
					]
				);

				$this->add_control(
					'template2_style',
					[
						'label' 	=> esc_html__( 'Template 2 - Style', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::SELECT,
						'default' 	=> 'template2_style_1',
						'options' 	=> [
							'template2_style_1' => esc_html__( 'Style 1', 'ova-brw' ),
							'template2_style_2' => esc_html__( 'Style 2', 'ova-brw' )
						],
						'condition' => [
							'template' => 'template2'
						]
					]
				);

				// Categories
				$categories = [
					'all' => esc_html__( 'All', 'ova-brw' )
				];

				// Get product categories
				$product_categories = get_categories([
					'taxonomy' 	=> 'product_cat',
					'orderby' 	=> 'name',
		        	'order'   	=> 'ASC'
				]);

				// Loop
				if ( ovabrw_array_exists( $product_categories ) ) {
					foreach ( $product_categories as $cat ) {
						$categories[$cat->slug] = $cat->cat_name;
					}
				} // END loop

				$this->add_control(
					'categories',
					[
						'label' 	=> esc_html__( 'Select Category', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::SELECT,
						'default' 	=> 'all',
						'options' 	=> $categories
					]
				);

				$this->add_control(
					'posts_per_page',
					[
						'label' 	=> esc_html__( 'Posts Per Page', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::NUMBER,
						'default' 	=> 8
					]
				);

				$this->add_control(
					'orderby',
					[
						'label' 	=> esc_html__( 'Order By', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::SELECT,
						'default' 	=> 'ID',
						'options' 	=> [
							'title'    		=> esc_html__( 'Title', 'ova-brw' ),
							'ID' 	   		=> esc_html__( 'ID', 'ova-brw' ),
							'date' 	   		=> esc_html__( 'Date', 'ova-brw' ),
							'featured' 		=> esc_html__( 'Featured', 'ova-brw' ),
							'menu_order' 	=> esc_html__( 'Menu Order', 'ova-brw' ),
							'popularity' 	=> esc_html__( 'Popularity (sales)', 'ova-brw' ),
							'rating' 		=> esc_html__( 'Average rating', 'ova-brw' ),
							'price' 		=> esc_html__( 'Sort by price', 'ova-brw' ),
						],
					]
				);

				$this->add_control(
					'order',
					[
						'label' 	=> esc_html__( 'Order', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::SELECT,
						'default' 	=> 'DESC',
						'options' 	=> [
							'ASC' 	=> esc_html__( 'Ascending', 'ova-brw' ),
							'DESC' 	=> esc_html__( 'Descending', 'ova-brw' ),
						]
					]
				);

				$this->add_control(
					'category_in',
					[
						'label'   		=> esc_html__( 'Category In', 'ova-brw' ),
						'type'    		=> \Elementor\Controls_Manager::TEXT,
						'description' 	=> esc_html__( 'Enter the product category IDs. IDs are separated by "|". Ex: 1|2|3.', 'ova-brw' ),
					]
				);

				$this->add_control(
					'category_not_in',
					[
						'label'   		=> esc_html__( 'Category Not In', 'ova-brw' ),
						'type'    		=> \Elementor\Controls_Manager::TEXT,
						'description' 	=> esc_html__( 'Enter the product category IDs. IDs are separated by "|". Ex: 1|2|3.', 'ova-brw' ),
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
						'default' => 'no',
						'options' => [
							'yes' => esc_html__( 'Yes', 'ova-brw' ),
							'no'  => esc_html__( 'No', 'ova-brw' ),
						],
						'frontend_available' => true,
					]
				);

			$this->end_controls_section(); // Additional options

	        // STYLE
			$this->start_controls_section(
				'section_product_slider_style',
				[
					'label' => esc_html__( 'Content', 'ova-brw' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_responsive_control(
					'content_grap',
					[
						'label'			=> esc_html__( 'Grap', 'ova-brw' ),
						'type' 			=> \Elementor\Controls_Manager::SLIDER,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'range' 		=> [
							'px' => [
								'min' 	=> 0,
								'max' 	=> 50,
								'step' 	=> 5,
							],
							'%' => [
								'min' => 0,
								'max' => 10,
							],
						],
						'default' => [
							'unit' => 'px',
						],
						'selectors' => [
							'{{WRAPPER}} .ova-product-slider' => 'grid-gap: {{SIZE}}{{UNIT}};',
						],
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

				$this->add_control(
					'wrap_content_background',
					[
						'label' 	=> esc_html__( 'Wrap Background', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-product-slider .ova-product' => 'background-color: {{VALUE}}',
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

				$this->add_responsive_control(
					'wrap_content_padding',
					[
						'label' 		=> esc_html__( 'Wrap Padding', 'ova-brw' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-product-slider .ova-product' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
						'selector' 	=> '{{WRAPPER}} .ova-product-slider .ova-product .ova-tour-day',
					]
				);

				$this->add_control(
					'tour_day_color',
					[
						'label' 	=> esc_html__( 'Color', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-product-slider .ova-product .ova-tour-day' => 'color: {{VALUE}}',
						],
					]
				);

				$this->add_control(
					'tour_day_bgcolor',
					[
						'label' 	=> esc_html__( 'Background Color', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-product-slider .ova-product .ova-tour-day' => 'background-color: {{VALUE}}',
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
							'{{WRAPPER}} .ova-product-slider .ova-product .ova-tour-day' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Border::get_type(),
					[
						'name' 		=> 'tour_day_border',
						'label' 	=> esc_html__( 'Border', 'ova-brw' ),
						'selector' 	=> '{{WRAPPER}} .ova-product-slider .ova-product .ova-tour-day',
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
						'selectors' => [
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
							'{{WRAPPER}} .ova-product-slider .ova-product .ova_foot_product .ova-product-review .star-rating, {{WRAPPER}} .ova-product-slider .ova-product .ova_foot_product .ova-product-review .star-rating:before' => 'color: {{VALUE}}',
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
						'padding_button',
						[
							'label'      => esc_html__( 'Padding', 'ova-brw' ),
							'type'       => \Elementor\Controls_Manager::DIMENSIONS,
							'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
							'selectors'  => [
								'{{WRAPPER}} .ova-product-slider .ova-product .ova_foot_product .ova-product-wrapper-price .product-btn-book-now' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							]
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
							'px' 	=> [
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
							'px' 	=> [
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
							'{{WRAPPER}} .ova-product-slider .swiper-nav .button-nav' => 'top: {{SIZE}}{{UNIT}};',
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
							'{{WRAPPER}} .ova-product-slider .swiper-nav .button-nav' => 'right: {{SIZE}}{{UNIT}};',
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
				                    '{{WRAPPER}} .ova-product-slider .swiper-nav .button-nav i' => 'color: {{VALUE}}',
				                ],
				            ]
				        );

				        $this->add_control(
				            'nav_color_border_normal',
				            [
				                'label' 	=> esc_html__( 'Border Color', 'ova-brw' ),
				                'type' 		=> \Elementor\Controls_Manager::COLOR,
				                'selectors' => [
				                    '{{WRAPPER}} .ova-product-slider .swiper-nav .button-nav.button-next, {{WRAPPER}} .ova-product-slider .swiper-nav .button-nav.button-prev' => 'border-color: {{VALUE}}',
				                ],
				            ]
				        );

				        $this->add_control(
				            'nav_bgcolor_normal',
				            [
				                'label' 	=> esc_html__( 'Background Color', 'ova-brw' ),
				                'type' 		=> \Elementor\Controls_Manager::COLOR,
				                'selectors' => [
				                    '{{WRAPPER}} .ova-product-slider .swiper-nav .button-nav.button-next, {{WRAPPER}} .ova-product-slider .swiper-nav .button-nav.button-prev' => 'background-color: {{VALUE}}',
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
				                    '{{WRAPPER}} .ova-product-slider .swiper-nav .button-nav:hover i' => 'color: {{VALUE}}',
				                ],
				            ]
				        );

				         $this->add_control(
				            'nav_color_border_hover',
				            [
				                'label' 	=> esc_html__( 'Border Color', 'ova-brw' ),
				                'type' 		=> \Elementor\Controls_Manager::COLOR,
				                'selectors' => [
				                    '{{WRAPPER}} .ova-product-slider .swiper-nav .button-nav.button-next:hover, {{WRAPPER}} .ova-product-slider .swiper-nav .button-nav.button-prev:hover' => 'border-color: {{VALUE}}',
				                ],
				            ]
				        );

				        $this->add_control(
				            'nav_bgcolor_hover',
				            [
				                'label' 	=> esc_html__( 'Background Color', 'ova-brw' ),
				                'type' 		=> \Elementor\Controls_Manager::COLOR,
				                'selectors' => [
				                    '{{WRAPPER}} .ova-product-slider .swiper-nav .button-nav.button-next:hover, {{WRAPPER}} .ova-product-slider .swiper-nav .button-nav.button-prev:hover' => 'background-color: {{VALUE}}',
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
		                    '{{WRAPPER}} .ova-product-slider .swiper-nav .button-nav' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		                ],
		            ]
		        );

	        $this->end_controls_section(); /* End Nav Style */

	        /* Begin Dots Style */
			$this->start_controls_section(
	            'dots_style',
	            [
	                'label' => esc_html__( 'Dots', 'ova-brw' ),
	                'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
	            ]
	        );

	           	$this->add_responsive_control(
					'dots_margin_top',
					[
						'label' 	=> esc_html__( 'Top', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::SLIDER,
						'range' 	=> [
							'px' 	=> [
								'min' => 0,
								'max' => 60,
							],
						],
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-product-slider .button-dots' => 'margin-top: {{SIZE}}{{UNIT}};',
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

			            $this->add_group_control(
							\Elementor\Group_Control_Background::get_type(),
							[
								'name' 		=> 'dots_bg_gradient_normal',
								'label' 	=> esc_html__( 'Background Gradient', 'ova-brw' ),
								'types' 	=> [ 'classic', 'gradient' ],
								'exclude' 	=> [ 'image' ],
								'selector' 	=> '{{WRAPPER}} .ova-product-slider .button-dots .swiper-pagination-bullet',
							]
						);

						$this->add_responsive_control(
							'dots_width',
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
			            'tab_dots_hover',
			            [
			                'label' => esc_html__( 'Hover', 'ova-brw' ),
			            ]
			        );

				        $this->add_group_control(
							\Elementor\Group_Control_Background::get_type(),
							[
								'name' 		=> 'dots_bg_gradient_hover',
								'label' 	=> esc_html__( 'Background Gradient', 'ova-brw' ),
								'types' 	=> [ 'classic', 'gradient' ],
								'exclude' 	=> [ 'image' ],
								'selector' 	=> '{{WRAPPER}} .ova-product-slider .button-dots .swiper-pagination-bullet:hover',
							]
						);

			        $this->end_controls_tab();

			        $this->start_controls_tab(
			            'tab_dots_active',
			            [
			                'label' => esc_html__( 'Active', 'ova-brw' ),
			            ]
			        );

				        $this->add_group_control(
							\Elementor\Group_Control_Background::get_type(),
							[
								'name' 		=> 'dots_bg_gradient_active',
								'label' 	=> esc_html__( 'Background Gradient', 'ova-brw' ),
								'types' 	=> [ 'classic', 'gradient' ],
								'exclude' 	=> [ 'image' ],
								'selector' 	=> '{{WRAPPER}} .ova-product-slider .button-dots .swiper-pagination-bullet.swiper-pagination-bullet-active',
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
	        $this->end_controls_section(); /* End Dots Style */
		}

		/**
		 * Get featured product ids
		 */
		protected function tripgo_get_featured_product_ids() {			
			// Tax query
			$tax_query[] = [
				'taxonomy' => 'product_visibility',
			    'field'    => 'name',
			    'terms'    => 'featured',
			    'operator' => 'IN'
			];

			// Get featured product
			$featured_ids = get_posts([
				'post_type'           => 'product',
			    'post_status'         => 'publish',
			    'posts_per_page'      => -1,
			    'orderby'             => 'ID',
			    'order'               => 'DESC',
			    'tax_query'           => $tax_query,
			    'fields'              => 'ids'
			]);

			return $featured_ids;
		}

		/**
		 * Get not featured product ids
		 */
		protected function tripgo_get_not_featured_product_ids() {
			$not_featured_ids = get_posts([
				'post_type'           => 'product',
			    'post_status'         => 'publish',
			    'posts_per_page'      => -1,
			    'orderby'             => 'ID',
			    'order'               => 'DESC',
			    'post__not_in'        => $this->tripgo_get_featured_product_ids(),
			    'fields'              => 'ids'
			]);

			return $not_featured_ids;
		}

		/**
		 * Get product list
		 */
		protected function tripgo_get_product_list( $args ) {
			$args_query = [
				'post_type' 		=> 'product',
			    'post_status' 		=> 'publish',
			    'posts_per_page' 	=> $args['posts_per_page'],
			    'orderby' 			=> $args['orderby'],
			    'order'				=> $args['order'],
			    'tax_query' 		=> [],
			];

			switch ( $args['orderby'] ) {
				case 'featured':
					// Orderby featured product
					$id_featured      = $this->tripgo_get_featured_product_ids();
					$id_not_featured  = $this->tripgo_get_not_featured_product_ids();
					$id_order_post_in = array_merge( $id_featured, $id_not_featured );

					if ( $args['order'] === 'ASC' ) {
						sort( $id_order_post_in );
					} else {
						rsort( $id_order_post_in );
					}

					$args_query['post__in'] = $id_order_post_in;
					$args_query['orderby'] 	= 'post__in';
					break;
				case 'popularity':
					$args_query['meta_key'] = 'total_sales';
					$args_query['orderby'] 	= 'meta_value_num';
					break;
				case 'rating':
					$args_query['meta_key'] = '_wc_average_rating';
					$args_query['orderby'] 	= 'meta_value_num';
					break;
				case 'price':
					$args_query['meta_key'] = '_price';
					$args_query['orderby'] 	= 'meta_value_num';
					break;
			}

			if ( 'yes' === $args['show_featured'] ) {
		        $featured = [
		        	'taxonomy' => 'product_visibility',
	                'field'    => 'name',
	                'terms'    => 'featured',
	                'operator' => 'IN'
		        ];

		        array_push( $args_query['tax_query'], $featured );
		    }

		    if ( 'yes' === $args['show_rental'] ) {
		        $rental = [
		        	'taxonomy' => 'product_type',
	                'field'    => 'slug',
	                'terms'    => OVABRW_RENTAL,
	                'operator' => 'IN'
		        ];

		        array_push( $args_query['tax_query'], $rental );
		    }

			if ( 'all' != $args['category_slug'] ) {
				$category_args = [
					'taxonomy' 	=> 'product_cat',
	            	'field' 	=> 'slug',
	            	'terms'     => $args['category_slug'],
	            	'operator'  => 'IN',
				];
				array_push( $args_query['tax_query'], $category_args );
			}

			if ( $args['category_in'] ) {
				$category_in = [
					[
						'taxonomy' 	=> 'product_cat',
						'field'    	=> 'term_id',
						'terms'    	=> explode( '|', $args['category_in'] ),
						'operator'  => 'IN',
					],
				];
				array_push( $args_query['tax_query'], $category_in );
			}

			if ( $args['category_not_in'] ) {
				$category_not_in = [
					[
						'taxonomy' 	=> 'product_cat',
						'field'    	=> 'term_id',
						'terms'    	=> explode( '|', $args['category_not_in'] ),
						'operator' 	=> 'NOT IN',
					],
				];
				array_push( $args_query['tax_query'], $category_not_in );
			}

			if ( 'yes' === $args['show_on_sale'] ) {
		        $product_ids_on_sale = wc_get_product_ids_on_sale();
		        $args_query['post__in'] = $product_ids_on_sale;
		    }

			$result  = new \WP_Query( $args_query );

			return $result;
		}

		/**
		 * Render HTML
		 */
		protected function render() {
			// Get settings
			$settings = $this->get_settings_for_display();

			// Get template
			$template = ovabrw_get_meta_data( 'template', $settings );

			// Get template 2
			$template2_style = ovabrw_get_meta_data( 'template2_style', $settings, 'template2_style_1' );

			// Slide options
			$carousel_options = [
				'items' 				=> ovabrw_get_meta_data( 'item_number', $settings, 4 ),
				'slideBy' 				=> ovabrw_get_meta_data( 'slides_to_scroll', $settings, 1 ),
				'margin' 				=> ovabrw_get_meta_data( 'margin_items', $settings, 24 ),
				'autoplayTimeout' 		=> ovabrw_get_meta_data( 'autoplay_speed', $settings, 3000 ),
				'smartSpeed' 			=> ovabrw_get_meta_data( 'smartspeed', $settings, 500 ),
				'loop' 					=> 'yes' === ovabrw_get_meta_data( 'infinite', $settings ) ? true : false,
				'autoplay' 				=> 'yes' === ovabrw_get_meta_data( 'autoplay', $settings ) ? true : false,
				'nav' 					=> 'yes' === ovabrw_get_meta_data( 'nav_control', $settings ) ? true : false,
				'dots' 					=> 'yes' === ovabrw_get_meta_data( 'dot_control', $settings ) ? true : false,
				'rtl' 					=> is_rtl() ? true: false,
				'autoplayHoverPause' 	=> 'yes' === ovabrw_get_meta_data( 'pause_on_hover', $settings ) ? true : false
			];

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

			// Get products
			$products = $this->tripgo_get_product_list([
				'posts_per_page' 	=> ovabrw_get_meta_data( 'posts_per_page', $settings, 8 ),
				'orderby' 			=> ovabrw_get_meta_data( 'orderby', $settings, 'ID' ),
				'order' 			=> ovabrw_get_meta_data( 'order', $settings, 'DESC' ),
				'category_slug'		=> ovabrw_get_meta_data( 'categories', $settings ),
				'category_in' 		=> ovabrw_get_meta_data( 'category_in', $settings ),
				'category_not_in' 	=> ovabrw_get_meta_data( 'category_not_in', $settings ),
				'show_featured'		=> ovabrw_get_meta_data( 'show_featured', $settings ),
				'show_rental'		=> ovabrw_get_meta_data( 'show_rental', $settings ),
				'show_on_sale'		=> ovabrw_get_meta_data( 'show_on_sale', $settings )
			]);

			if ( $products->have_posts() ): ?>
				<div class="ova-product-slider ova-product-slider-<?php echo esc_attr( $template ); ?> <?php echo esc_attr( $template2_style ); ?>" data-options="<?php echo esc_attr( json_encode( $slide_options ) ); ?>">
					<div class="swiper swiper-loading">
						<div class="swiper-wrapper">
							<?php while( $products->have_posts() ) : $products->the_post(); ?>
								<div class="swiper-slide">
									<?php wc_get_template_part( 'content', 'product' ); ?>
								</div>
							<?php endwhile; ?>
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
				</div>
			<?php else: ?>
				<div class="ova-no-products-found">
					<?php echo esc_html__( 'No products found', 'ova-brw' ); ?>
				</div>
			<?php endif; wp_reset_postdata();
		}
	}

	// Regiser new widget
	$widgets_manager->register( new OVABRW_Product_Slider() );
}