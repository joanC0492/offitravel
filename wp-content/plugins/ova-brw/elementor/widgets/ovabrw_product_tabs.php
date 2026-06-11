<?php if ( !defined( 'ABSPATH' ) ) exit(); // Exit if accessed directly

/**
 * Class OVABRW_Product_Tabs
 */
if ( !class_exists( 'OVABRW_Product_Tabs', false ) ) {

	class OVABRW_Product_Tabs extends \Elementor\Widget_Base {

		/**
		 * Get widget name
		 */
		public function get_name() {		
			return 'ovabrw_product_tabs';
		}

		/**
		 * Get widget title
		 */
		public function get_title() {
			return esc_html__( 'Product Tabs', 'ova-brw' );
		}

		/**
		 * Get widget icon
		 */
		public function get_icon() {
			return 'eicon-product-tabs';
		}

		/**
		 * Get widget categories
		 */
		public function get_categories() {
			return [ 'ovabrw-product-templates' ];
		}

		/**
		 * Get script depends
		 */
		public function get_script_depends() {
			// Enqueue script
			wp_enqueue_script( 'ovabrw-product-tabs', OVABRW_PLUGIN_URI.'assets/js/elementor/product-tabs.js', [ 'jquery' ], false, true );

			return [];
		}

		/**
		 * Register controls
		 */
		protected function register_controls() {

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
						'raw' 	=> esc_html__( "Don't enter Product ID if you use this element in templates for product detail page.In Elementor Preview ( When empty Product ID ) , this element display an example product tabs of the latest product", 'ova-brw' ),
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

			$this->end_controls_section();

			$this->start_controls_section(
				'section_description_options',
				[
					'label' => esc_html__( 'Description', 'ova-brw' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_CONTENT,
				]
			);

			    $this->add_control(
					'description_label',
					[
						'type' 		=> \Elementor\Controls_Manager::TEXT,
						'label' 	=> esc_html__( 'Label', 'ova-brw' ),
						'default' 	=> esc_html__( 'Description', 'ova-brw' ),
					]
				);

				$this->add_control(
					'show_description',
					[
						'label' 		=> esc_html__( 'Show/Hide', 'ova-brw' ),
						'type' 			=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 		=> esc_html__( 'Show', 'ova-brw' ),
						'label_off' 	=> esc_html__( 'Hide', 'ova-brw' ),
						'return_value' 	=> 'yes',
						'default' 		=> 'yes',
					]
				);

			$this->end_controls_section();

			$this->start_controls_section(
				'section_incl_excl_options',
				[
					'label' => esc_html__( 'Included/Excluded', 'ova-brw' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_CONTENT,
				]
			);

			    $this->add_control(
					'incl_excl_label',
					[
						'type' 		=> \Elementor\Controls_Manager::TEXT,
						'label' 	=> esc_html__( 'Label', 'ova-brw' ),
						'default' 	=> esc_html__( 'Included/Excluded', 'ova-brw' ),
					]
				);

				$this->add_control(
					'show_incl_excl',
					[
						'label' 		=> esc_html__( 'Show/Hide', 'ova-brw' ),
						'type' 			=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 		=> esc_html__( 'Show', 'ova-brw' ),
						'label_off' 	=> esc_html__( 'Hide', 'ova-brw' ),
						'return_value' 	=> 'yes',
						'default' 		=> 'yes',
					]
				);

			$this->end_controls_section();

			$this->start_controls_section(
				'section_tour_plan_options',
				[
					'label' => esc_html__( 'Tour Plan', 'ova-brw' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_CONTENT,
				]
			);

			    $this->add_control(
					'tour_plan_label',
					[
						'type' 		=> \Elementor\Controls_Manager::TEXT,
						'label' 	=> esc_html__( 'Label', 'ova-brw' ),
						'default' 	=> esc_html__( 'Tour Plan', 'ova-brw' ),
					]
				);

				$this->add_control(
					'show_tour_plan',
					[
						'label' 		=> esc_html__( 'Show/Hide', 'ova-brw' ),
						'type' 			=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 		=> esc_html__( 'Show', 'ova-brw' ),
						'label_off' 	=> esc_html__( 'Hide', 'ova-brw' ),
						'return_value' 	=> 'yes',
						'default' 		=> 'yes',
					]
				);

			$this->end_controls_section();

			$this->start_controls_section(
				'section_tour_map_options',
				[
					'label' => esc_html__( 'Tour Map', 'ova-brw' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_CONTENT,
				]
			);

			    $this->add_control(
					'tour_map_label',
					[
						'type' 		=> \Elementor\Controls_Manager::TEXT,
						'label' 	=> esc_html__( 'Label', 'ova-brw' ),
						'default' 	=> esc_html__( 'Tour Map', 'ova-brw' ),
					]
				);

				$this->add_control(
					'show_tour_map',
					[
						'label' 		=> esc_html__( 'Show/Hide', 'ova-brw' ),
						'type' 			=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 		=> esc_html__( 'Show', 'ova-brw' ),
						'label_off' 	=> esc_html__( 'Hide', 'ova-brw' ),
						'return_value' 	=> 'yes',
						'default' 		=> 'yes',
					]
				);

			$this->end_controls_section();

			$this->start_controls_section(
				'section_reviews_options',
				[
					'label' => esc_html__( 'Reviews', 'ova-brw' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_CONTENT,
				]
			);

			    $this->add_control(
					'reviews_label',
					[
						'type' 		=> \Elementor\Controls_Manager::TEXT,
						'label' 	=> esc_html__( 'Label', 'ova-brw' ),
						'default' 	=> esc_html__( 'Reviews', 'ova-brw' ),
					]
				);

				$this->add_control(
					'show_reviews',
					[
						'label' 		=> esc_html__( 'Show/Hide', 'ova-brw' ),
						'type' 			=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 		=> esc_html__( 'Show', 'ova-brw' ),
						'label_off' 	=> esc_html__( 'Hide', 'ova-brw' ),
						'return_value' 	=> 'yes',
						'default' 		=> 'yes',
					]
				);

			$this->end_controls_section();
			
			$this->start_controls_section(
				'section_product_tabs_style',
				[
					'label' => esc_html__( 'Tabs', 'ova-brw' ),
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
								'name' 		=> 'content_typography_title_btn',
								'label' 	=> esc_html__( 'Typography', 'ova-brw' ),
								'selector' 	=> '{{WRAPPER}} .ova-tabs-product .tabs .item ',
								
							]
						);

						$this->add_control(	
							'color_title_btn',
							[
								'label' 	=> esc_html__( 'Color', 'ova-brw' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ova-tabs-product .tabs .item ' => 'color : {{VALUE}};',
								],
							]
						);

						$this->add_control(
							'color_button_background',
							[
								'label' 	=> esc_html__( 'Background ', 'ova-brw' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ova-tabs-product .tabs .item ' => 'background-color : {{VALUE}};',
								],
							]
						);

					$this->end_controls_tab();

					$this->start_controls_tab(
						'style_active_tab',
						[
							'label' => esc_html__( 'Active', 'ova-brw' ),
						]
					);

						$this->add_control(
							'color_title_btn_active',
							[
								'label' 	=> esc_html__( 'Color', 'ova-brw' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ova-tabs-product .tabs .item.active' => 'color : {{VALUE}} ;',
								],
							]
						);

						$this->add_control(
							'color_button_active_background',
							[
								'label' 	=> esc_html__( 'Background', 'ova-brw' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ova-tabs-product .tabs .item.active' => 'background-color : {{VALUE}};',
								],
							]
						);

					$this->end_controls_tab();

				$this->end_controls_tabs();

				    $this->add_responsive_control(
						'margin_button',
						[
							'label' 		=> esc_html__( 'Margin', 'ova-brw' ),
							'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
							'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
							'selectors' 	=> [
								'{{WRAPPER}} .ova-tabs-product .tabs .item' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							],
							'separator' => 'before'
						]
					);
					
					$this->add_responsive_control(
						'padding_button',
						[
							'label' 		=> esc_html__( 'Padding', 'ova-brw' ),
							'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
							'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
							'selectors' 	=> [
								'{{WRAPPER}} .ova-tabs-product .tabs .item ' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							],
						]
					);

					$this->add_responsive_control(
						'border_radius_button',
						[
							'label' 		=> esc_html__( 'Border Radius', 'ova-brw' ),
							'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
							'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
							'selectors' 	=> [
								'{{WRAPPER}} .ova-tabs-product .tabs .item ' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							],
						]
					);

		    $this->end_controls_section();
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
			endif; ?>
			<div class="elementor-ovabrw-product-tabs">
				<?php wc_get_template( apply_filters( OVABRW_PREFIX.'element_product_tabs_template' ,'rental/loop/tabs.php' ), $settings ); ?>
			</div>
			<?php
		}
	}

	// Regiser new widget
	$widgets_manager->register( new OVABRW_Product_Tabs() );
}