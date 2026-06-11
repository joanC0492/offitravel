<?php if ( !defined( 'ABSPATH' ) ) exit(); // Exit if accessed directly

/**
 * Class OVABRW_Product_Images
 */
if ( !class_exists( 'OVABRW_Product_Images', false ) ) {

	class OVABRW_Product_Images extends \Elementor\Widget_Base {

		/**
		 * Get widget name
		 */
		public function get_name() {		
			return 'ovabrw_product_images';
		}

		/**
		 * Get widget title
		 */
		public function get_title() {
			return esc_html__( 'Product Images', 'ova-brw' );
		}

		/**
		 * Get widget icon
		 */
		public function get_icon() {
			return 'eicon-product-images';
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
			return [ 'swipe', 'ovabrw-elementor-product-images' ];
		}

		/**
		 * Get script depends
		 */
		public function get_script_depends() {
	  		return [ 'swipe', 'ovabrw-elementor-product-images' ];
		}

		/**
		 * Register controls
		 */
		protected function register_controls() {
			// Additional options
			$this->start_controls_section(
				'section_additional_options',
				[
					'label' => esc_html__( 'Additional Options', 'ova-brw' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_CONTENT,
				]
			);

				$this->add_control(
					'margin_items',
					[
						'label'   => esc_html__( 'Margin Right Items', 'ova-brw' ),
						'type'    => \Elementor\Controls_Manager::NUMBER,
						'default' => 24
					]
				);

				$this->add_control(
					'item_number',
					[
						'label'       => esc_html__( 'Item Number', 'ova-brw' ),
						'type'        => \Elementor\Controls_Manager::NUMBER,
						'description' => esc_html__( 'Number Item', 'ova-brw' ),
						'default'     => 3
					]
				);

				$this->add_control(
					'slides_to_scroll',
					[
						'label'       => esc_html__( 'Slides to Scroll', 'ova-brw' ),
						'type'        => \Elementor\Controls_Manager::NUMBER,
						'description' => esc_html__( 'Set how many slides are scrolled per swipe.', 'ova-brw' ),
						'default'     => 1
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
						'frontend_available' => true
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
						'frontend_available' => true
					]
				);

				$this->add_control(
					'autoplay',
					[
						'label'   => esc_html__( 'Autoplay', 'ova-brw' ),
						'type'    => \Elementor\Controls_Manager::SWITCHER,
						'default' => 'yes',
						'options' => [
							'yes' => esc_html__( 'Yes', 'ova-brw' ),
							'no'  => esc_html__( 'No', 'ova-brw' ),
						],
						'frontend_available' => true
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
						'frontend_available' => true
					]
				);

				$this->add_control(
					'smartspeed',
					[
						'label'   => esc_html__( 'Smart Speed', 'ova-brw' ),
						'type'    => \Elementor\Controls_Manager::NUMBER,
						'default' => 500
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
						'frontend_available' => true
					]
				);

			$this->end_controls_section(); // Additional options

			$this->start_controls_section(
				'section_product_id_options',
				[
					'label' => esc_html__( 'Product Option', 'ova-brw' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_CONTENT,
				]
			);

			    $this->add_control(
					'wc_content_warning',
					[
						'type' 	=> \Elementor\Controls_Manager::RAW_HTML,
						'raw' 	=> esc_html__( "Don't enter Product ID if you use this element in templates for product detail page.In Elementor Preview ( When empty Product ID ) , this element display an example product images of the latest product", 'ova-brw' ),
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

			$this->end_controls_section(); // END

			$this->start_controls_section(
				'section_product_gallery_style',
				[
					'label' => esc_html__( 'Style', 'ova-brw' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_control(
					'wc_style_warning',
					[
						'type' 	=> \Elementor\Controls_Manager::RAW_HTML,
						'raw' 	=> esc_html__( 'The style of this widget is often affected by your theme and plugins. If you experience any such issue, try to switch to a basic theme and deactivate related plugins.', 'ova-brw' ),
						'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
					]
				);

				$this->add_responsive_control(
					'image_border_radius',
					[
						'label' 	 => esc_html__( 'Border Radius', 'ova-brw' ),
						'type' 		 => \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors'  => [
							'{{WRAPPER}} .ova-gallery-slideshow .item img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
						],
					]
				);		

			$this->end_controls_section(); // END

			// Navigation
			$this->start_controls_section(
				'section_nav',
				[
					'label' 	=> esc_html__( 'Nav', 'ova-brw' ),
					'tab' 		=> \Elementor\Controls_Manager::TAB_STYLE,
					'condition' => [
						'nav_control' => 'yes'
					]
				]
			);

				$this->add_control(
					'nav_size',
					[
						'type' 			=> \Elementor\Controls_Manager::SLIDER,
						'label' 		=> esc_html__( 'Size', 'ova-brw' ),
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'range' 		=> [
							'px' => [
								'min' 	=> 0,
								'max' 	=> 200
							],
							'%' => [
								'min' => 0,
								'max' => 100
							],
						],
						'selectors' => [
							'{{WRAPPER}} .elementor-product-image .ova-gallery-slideshow .button-nav' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};'
						]
					]
				);

				$this->add_control(
					'nav_spacing',
					[
						'type' 			=> \Elementor\Controls_Manager::SLIDER,
						'label' 		=> esc_html__( 'Horizontal Spacing', 'ova-brw' ),
						'size_units' 	=> [ 'px', 'em', 'rem', 'custom' ],
						'range' 		=> [
							'px' => [
								'min' 	=> -200,
								'max' 	=> 200
							]
						],
						'selectors' => [
							'{{WRAPPER}} .elementor-product-image .ova-gallery-slideshow .button-nav.button-prev' => 'left: {{SIZE}}{{UNIT}};',
							'{{WRAPPER}} .elementor-product-image .ova-gallery-slideshow .button-nav.button-next' => 'right: {{SIZE}}{{UNIT}};',
						]
					]
				);

				$this->start_controls_tabs(
					'nav_tabs'
				);

					$this->start_controls_tab(
						'nav_normal_tab',
						[
							'label' => esc_html__( 'Normal', 'ova-brw' ),
						]
					);

						$this->add_control(
							'icon_size',
							[
								'type' 			=> \Elementor\Controls_Manager::SLIDER,
								'label' 		=> esc_html__( 'Icon Size', 'ova-brw' ),
								'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
								'range' 		=> [
									'px' => [
										'min' 	=> 0,
										'max' 	=> 200
									],
									'%' => [
										'min' => 0,
										'max' => 100
									],
								],
								'selectors' => [
									'{{WRAPPER}} .elementor-product-image .ova-gallery-slideshow .button-nav i' => 'font-size: {{SIZE}}{{UNIT}};'
								]
							]
						);

						$this->add_control(
							'color_nav',
							[
								'label' 	=> esc_html__( 'Color', 'ova-brw' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .elementor-product-image .ova-gallery-slideshow .button-nav i' => 'color : {{VALUE}};'
								]
							]
						);

						$this->add_control(
							'bg_color_nav',
							[
								'label' 	=> esc_html__( 'Background Color', 'ova-brw' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .elementor-product-image .ova-gallery-slideshow .button-nav' => 'background-color : {{VALUE}};'
								]
							]
						);

					$this->end_controls_tab();

					$this->start_controls_tab(
						'nav_hover_tab',
						[
							'label' => esc_html__( 'Hover', 'ova-brw' ),
						]
					);

						$this->add_control(
							'color_nav_hover',
							[
								'label' 	=> esc_html__( 'Color', 'ova-brw' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .elementor-product-image .ova-gallery-slideshow .button-nav:hover i' => 'color : {{VALUE}};'
								]
							]
						);

						$this->add_control(
							'bg_color_nav_hover',
							[
								'label' 	=> esc_html__( 'Background', 'ova-brw' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .elementor-product-image .ova-gallery-slideshow .button-nav:hover' => 'background-color : {{VALUE}};'
								],
							]
						);

					$this->end_controls_tab();
				$this->end_controls_tabs();
			$this->end_controls_section(); // END Navigation
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

			// Slide option
			$slide_options = [
				'slidesPerView'         => ovabrw_get_meta_data( 'item_number', $settings ),
		        'slidesPerGroup'        => ovabrw_get_meta_data( 'slides_to_scroll', $settings ),
		        'spaceBetween'          => ovabrw_get_meta_data( 'margin_items', $settings ),
		        'pauseOnMouseEnter'     => 'yes' === ovabrw_get_meta_data( 'pause_on_hover', $settings ) ? true : false,
		        'loop'                  => 'yes' === ovabrw_get_meta_data( 'infinite', $settings ) ? true : false,
		        'autoplay'              => 'yes' === ovabrw_get_meta_data( 'autoplay', $settings ) ? true : false,
		        'delay'                 => ovabrw_get_meta_data( 'autoplay_speed', $settings, 3000 ),
		        'speed'                 => ovabrw_get_meta_data( 'smartspeed', $settings, 500 ),
		        'dots'                  => false,
		        'nav'                   => 'yes' === ovabrw_get_meta_data( 'nav_control', $settings ) ? true : false,
		        'breakpoints'           => [
		            '0'     => [
		                'slidesPerView' => 1
		            ],
		            '768'   => [
		                'slidesPerView' => 2
		            ],
		            '1024'  => [
		                'slidesPerView' => 3
		            ]
		        ],
		        'rtl'                   => is_rtl() ? true: false
			];

			?>
			<div class="elementor-product-image">
				<?php wc_get_template( apply_filters( OVABRW_PREFIX.'element_product_images_template', 'rental/loop/gallery-slideshow.php' ), [
					'id' 			=> $product->get_id(),
					'data_options' 	=> $slide_options
				]); ?>
			</div>
			<?php
		}
	}

	// Regiser new widget
	$widgets_manager->register( new OVABRW_Product_Images() );
}