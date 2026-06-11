<?php if ( !defined( 'ABSPATH' ) ) exit(); // Exit if accessed directly

/**
 * Class OVABRW_Product_Filter_Ajax
 */
if ( !class_exists( 'OVABRW_Product_Filter_Ajax', false ) ) {

	class OVABRW_Product_Filter_Ajax extends \Elementor\Widget_Base {

		/**
		 * Get widget name
		 */
		public function get_name() {
			return 'ovabrw_product_filter_ajax';
		}

		/**
		 * Get widget title
		 */
		public function get_title() {
			return esc_html__( 'Product Filter Ajax', 'ova-brw' );
		}

		/**
		 * Get widget icon
		 */
		public function get_icon() {
			return 'eicon-gallery-justified';
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
			return [ 'swipe', 'ovabrw-elementor-product-filter-ajax' ];
		}

		/**
		 * Get script depends
		 */
		public function get_script_depends() {			
			return [ 'swipe', 'ovabrw-elementor-product-filter-ajax' ];
		}
	  	
	  	/**
	  	 * Register controls
	  	 */
		protected function register_controls() {

			$this->start_controls_section(
				'section_product',
				[
					'label' => esc_html__( 'Product', 'ova-brw' ),
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
					'posts_per_page',
					[
						'label'   => esc_html__( 'Products Per Category', 'ova-brw' ),
						'type'    => \Elementor\Controls_Manager::NUMBER,
						'min'     => 1,
						'default' => 4
					]
				);

				$this->add_control(
					'product_orderby',
					[
						'label' 	=> esc_html__( 'Order By', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::SELECT,
						'default' 	=> 'ID',
						'options' 	=> [
							'ID'  	=> esc_html__( 'ID', 'ova-brw' ),
							'title' => esc_html__( 'Title', 'ova-brw' ),
							'date' 	=> esc_html__( 'Date', 'ova-brw' ),
						],
					]
				);

				$this->add_control(
					'product_order',
					[
						'label' 	=> esc_html__( 'Order', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::SELECT,
						'default' 	=> 'DESC',
						'options' 	=> [
							'ASC'  	=> esc_html__( 'Ascending', 'ova-brw' ),
							'DESC'  => esc_html__( 'Descending', 'ova-brw' ),
						],
					]
				);

				$this->add_control(
					'show_featured',
					[
						'label' 		=> esc_html__( 'Show Featured', 'ova-brw' ),
						'type' 			=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 		=> esc_html__( 'Yes', 'ova-brw' ),
						'label_off' 	=> esc_html__( 'No', 'ova-brw' ),
						'default' 		=> 'yes',
					]
				);

				$this->add_control(
					'show_wishlist',
					[
						'label' 		=> esc_html__( 'Show Wishlist', 'ova-brw' ),
						'type' 			=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 		=> esc_html__( 'Yes', 'ova-brw' ),
						'label_off' 	=> esc_html__( 'No', 'ova-brw' ),
						'default' 		=> 'yes',
					]
				);

				$this->add_control(
					'show_duration',
					[
						'label' 		=> esc_html__( 'Show Duration', 'ova-brw' ),
						'type' 			=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 		=> esc_html__( 'Yes', 'ova-brw' ),
						'label_off' 	=> esc_html__( 'No', 'ova-brw' ),
						'default' 		=> 'yes',
					]
				);

				$this->add_control(
					'show_title',
					[
						'label' 		=> esc_html__( 'Show Title', 'ova-brw' ),
						'type' 			=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 		=> esc_html__( 'Yes', 'ova-brw' ),
						'label_off' 	=> esc_html__( 'No', 'ova-brw' ),
						'default' 		=> 'yes',
					]
				);

				$this->add_control(
					'show_location',
					[
						'label' 		=> esc_html__( 'Show Location', 'ova-brw' ),
						'type' 			=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 		=> esc_html__( 'Yes', 'ova-brw' ),
						'label_off' 	=> esc_html__( 'No', 'ova-brw' ),
						'default' 		=> 'yes',
					]
				);

				$this->add_control(
					'show_rating',
					[
						'label' 		=> esc_html__( 'Show Rating', 'ova-brw' ),
						'type' 			=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 		=> esc_html__( 'Yes', 'ova-brw' ),
						'label_off' 	=> esc_html__( 'No', 'ova-brw' ),
						'default' 		=> 'yes',
					]
				);

				$this->add_control(
					'show_price',
					[
						'label' 		=> esc_html__( 'Show Price', 'ova-brw' ),
						'type' 			=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 		=> esc_html__( 'Yes', 'ova-brw' ),
						'label_off' 	=> esc_html__( 'No', 'ova-brw' ),
						'default' 		=> 'yes',
					]
				);

				$this->add_control(
					'show_button',
					[
						'label' 		=> esc_html__( 'Show Explore Button', 'ova-brw' ),
						'type' 			=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 		=> esc_html__( 'Yes', 'ova-brw' ),
						'label_off' 	=> esc_html__( 'No', 'ova-brw' ),
						'default' 		=> 'yes',
					]
				);

			$this->end_controls_section();

			$this->start_controls_section(
				'section_category',
				[
					'label' => esc_html__( 'Category Filter', 'ova-brw' ),
				]
			);     

				$this->add_control(
					'filter_title',
					[
						'label'   => esc_html__( 'Filter Title', 'ova-brw' ),
						'type'    => \Elementor\Controls_Manager::TEXT,
						'default' => esc_html__( 'Popular Categories', 'ova-brw' ),
					]
				);  

				$this->add_control(
					'catAll',
					[
						'label'   => esc_html__( 'Text All', 'ova-brw' ),
						'type'    => \Elementor\Controls_Manager::TEXT,
						'default' => esc_html__( 'All', 'ova-brw' ),
					]
				);

				// Categories
				$categories = [];

				// Default categories
				$default_categories = [];

				// Get product categories
				$product_categories = get_categories([
					'taxonomy' 	=> 'product_cat',
					'orderby' 	=> 'name',
					'order' 	=> 'ASC'
				]);

				// Loop
				if ( ovabrw_array_exists( $product_categories ) ) {
					foreach ( $product_categories as $i => $cat ) {
						// Default
						if ( $i < 3 ) $default_categories[] = $cat->slug;

						// Add
						$categories[$cat->slug] = $cat->name;
					}
				} // END loop

				$this->add_control(
					'categories',
					[
						'label' => esc_html__( 'Categories', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::SELECT2,
						'multiple' 	=> true,
						'options' 	=> $categories,
						'default' 	=> $default_categories
					]
				);

				$this->add_control(
					'orderby',
					[
						'label' 	=> esc_html__( 'Order By', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::SELECT,
						'default' 	=> 'ID',
						'options' 	=> [
							'ID'  	=> esc_html__( 'ID', 'ova-brw' ),
							'title' => esc_html__( 'Title', 'ova-brw' ),
							'date' 	=> esc_html__( 'Date', 'ova-brw' ),
						],
					]
				);

				$this->add_control(
					'order',
					[
						'label' 	=> esc_html__( 'Order', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::SELECT,
						'default' 	=> 'ASC',
						'options' 	=> [
							'ASC' 	=> esc_html__( 'Ascending', 'ova-brw' ),
							'DESC' 	=> esc_html__( 'Descending', 'ova-brw' ),
						],
					]
				);

			$this->end_controls_section();

	        // Additional options
			$this->start_controls_section(
				'section_additional_options',
				[
					'label' => esc_html__( 'Additional Options Slider', 'ova-brw' ),
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
						'default' => 'false',
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
						'default' => 'false',
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
						'default' => 1000,
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
					'dots_control',
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

			// Category Filter
			$this->start_controls_section(
				'style_section_category',
				[
					'label' => esc_html__( 'Category Filter', 'ova-brw' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
				]
			);		

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'category_label_typography',
						'selector' 	=> '{{WRAPPER}} .ova-product-filter-ajax ul li.product-filter-button',
					]
				);

				$this->start_controls_tabs('style_category_tabs');

					$this->start_controls_tab(
						'style_normal_category_tab',
						[
							'label' => esc_html__( 'Normal', 'ova-brw' ),
						]
					);
				
						$this->add_control(
							'label_category_color',
							[
								'label' 	=> esc_html__( 'Color', 'ova-brw' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ova-product-filter-ajax ul li.product-filter-button' => 'color : {{VALUE}};',
								],
							]
						);

						$this->add_control(
							'label_category_bgcolor',
							[
								'label' 	=> esc_html__( 'Background Color', 'ova-brw' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ova-product-filter-ajax ul li.product-filter-button' => 'background-color : {{VALUE}};',
								],
							]
						);

					
					$this->end_controls_tab();

					$this->start_controls_tab(
						'style_hover_category_tab',
						[
							'label' => esc_html__( 'Hover', 'ova-brw' ),
						]
					);

						$this->add_control(
							'label_category_color_hover',
							[
								'label' 	=> esc_html__( 'Color', 'ova-brw' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ova-product-filter-ajax ul li.product-filter-button:hover' => 'color : {{VALUE}};',
								],
							]
						);

						$this->add_control(
							'label_category_bgcolor_hover',
							[
								'label' 	=> esc_html__( 'Background Color', 'ova-brw' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ova-product-filter-ajax ul li.product-filter-button:hover' => 'background-color : {{VALUE}};',
								],
							]
						);

					$this->end_controls_tab();

					$this->start_controls_tab(
						'style_active_category_tab',
						[
							'label' => esc_html__( 'Active', 'ova-brw' ),
						]
					);

						$this->add_control(
							'label_category_color_active',
							[
								'label' 	=> esc_html__( 'Color', 'ova-brw' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ova-product-filter-ajax ul li.product-filter-button.active-category' => 'color : {{VALUE}};',
								],
							]
						);

						$this->add_control(
							'label_category_bgcolor_active',
							[
								'label' 	=> esc_html__( 'Background Color', 'ova-brw' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ova-product-filter-ajax ul li.product-filter-button.active-category' => 'background-color : {{VALUE}};',
								],
							]
						);

					$this->end_controls_tab();

				$this->end_controls_tabs();

				$this->add_control(
					'category_title_heading',
					[
						'label' 	=> esc_html__( 'Filter Title', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::HEADING,
						'separator' => 'before'
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'category_title_typography',
						'selector' 	=> '{{WRAPPER}} .ova-product-filter-ajax ul li.filter-title',
					]
				);

				$this->add_control(
					'category_title_color',
					[
						'label' 	=> esc_html__( 'Color', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-product-filter-ajax ul li.filter-title' => 'color : {{VALUE}};',
						],
					]
				);

				$this->add_control(
					'category_title_line_color',
					[
						'label' 	=> esc_html__( 'Line Color', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-product-filter-ajax ul li.filter-title:after' => 'background-color : {{VALUE}};',
						],
					]
				);

				$this->add_responsive_control(
					'category_title_margin',
					[
						'label' 	 => esc_html__( 'Margin', 'ova-brw' ),
						'type' 		 => \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors'  => [
							'{{WRAPPER}} .ova-product-filter-ajax ul li.filter-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

			$this->end_controls_section();
		}

		/**
		 * Render HTMl
		 */
		protected function render() {
			// Get settings
			$settings = $this->get_settings_for_display();

			// Get template
			ovabrw_get_template( apply_filters( OVABRW_PREFIX.'element_product_filter_ajax_template', 'single/product_filter_ajax.php' ), $settings );
		}
	}

	// Register new widget
	$widgets_manager->register( new OVABRW_Product_Filter_Ajax() );
}