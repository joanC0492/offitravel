<?php if ( !defined( 'ABSPATH' ) ) exit(); // Exit if accessed directly

/**
 * Class OVABRW_Product_Features
 */
if ( !class_exists( 'OVABRW_Product_Features', false ) ) {

	class OVABRW_Product_Features extends \Elementor\Widget_Base {

		/**
		 * Get widget name
		 */
		public function get_name() {		
			return 'ovabrw_product_features';
		}

		/**
		 * Get widget name
		 */
		public function get_title() {
			return esc_html__( 'Product Features', 'ova-brw' );
		}

		/**
		 * Get widget name
		 */
		public function get_icon() {
			return 'eicon-product-meta';
		}

		/**
		 * Get widget categories
		 */
		public function get_categories() {
			return [ 'ovabrw-product-templates' ];
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
						'raw' 	=> esc_html__( "Don't enter Product ID if you use this element in templates for product detail page.In Elementor Preview ( When empty Product ID ) , this element display an example product features of the latest product", 'ova-brw' ),
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
				'section_price_style',
				[
					'label' => esc_html__( 'Features', 'ova-brw' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

			$this->add_control(
				'wc_style_warning',
				[
					'type' => \Elementor\Controls_Manager::RAW_HTML,
					'raw'  => esc_html__( 'The style of this widget is often affected by your theme and plugins. If you experience any such issue, try to switch to a basic theme and deactivate related plugins.', 'ova-brw' ),
					'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
				]
			);

			$this->add_responsive_control(
				'feature_max_width',
				[
					'label' 		=> esc_html__( 'Max Width', 'ova-brw' ),
					'type' 			=> \Elementor\Controls_Manager::SLIDER,
					'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
					'range' 		=> [
						'px' => [
							'min' => 600,
							'max' => 1920,
						],
						'%' => [
							'min' => 50,
							'max' => 100,
						],
					],
					'selectors' 	=> [
						'{{WRAPPER}}  .ova-features-product' => 'max-width: {{SIZE}}{{UNIT}}',
					],
				]
			);

			$this->add_control(
				'feature_bgcolor',
				[
					'label'  	=> esc_html__( 'Background Color', 'ova-brw' ),
					'type' 	 	=> \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ova-features-product' => 'background-color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'feature_color',
				[
					'label'  	=> esc_html__( 'Color Title', 'ova-brw' ),
					'type' 	 	=> \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ova-features-product .feature  .title-desc .title' => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' 		=> 'features_typography',
					'selector' 	=> '{{WRAPPER}} .ova-features-product .feature .title-desc .title',
				]
			);

			$this->add_responsive_control(
				'features_padding',
				[
					'label' 	 => esc_html__( 'Padding', 'ova-brw' ),
					'type' 		 => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
					'selectors'  => [
						'{{WRAPPER}} .ova-features-product' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->add_responsive_control(
				'features_border_radius',
				[
					'label' 	 => esc_html__( 'Border Radius', 'ova-brw' ),
					'type' 		 => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
					'selectors'  => [
						'{{WRAPPER}} .ova-features-product' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);


			$this->add_control(
				'feature_value_color',
				[
					'label'  	=> esc_html__( 'Color Value', 'ova-brw' ),
					'type' 	 	=> \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ova-features-product .feature .title-desc .desc' => 'color: {{VALUE}}',
					],
				]
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				[
					'name' 	 	=> 'feature_value_typography',
					'selector' 	=> '{{WRAPPER}} .ova-features-product .feature .title-desc .desc',
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
			<div class="elementor-features">
				<?php wc_get_template( apply_filters( OVABRW_PREFIX.'element_product_features_template', 'rental/loop/features.php' ), [
					'id' => $product->get_id()
				]); ?>
			</div>
			<?php
		}
	}

	// Regiser new widget
	$widgets_manager->register( new OVABRW_Product_Features() );
}