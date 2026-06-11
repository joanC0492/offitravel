<?php if ( !defined( 'ABSPATH' ) ) exit(); // Exit if accessed directly

/**
 * Class OVABRW_Product_Forms
 */
if ( !class_exists( 'OVABRW_Product_Forms', false ) ) {

	class OVABRW_Product_Forms extends \Elementor\Widget_Base {

		/**
		 * Get widget name
		 */
		public function get_name() {	
			return 'ovabrw_product_forms';
		}

		/**
		 * Get widget title
		 */
		public function get_title() {
			return esc_html__( 'Product Forms', 'ova-brw' );
		}

		/**
		 * Get widget icon
		 */
		public function get_icon() {
			return 'eicon-form-horizontal';
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
			return [ 'ovabrw-elementor-product-forms' ];
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
						'raw' 	=> esc_html__( "Don't enter Product ID if you use this element in templates for product detail page.In Elementor Preview ( When empty Product ID ) , this element display an example product forms of the latest product", 'ova-brw' ),
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
				'section_product_forms_style',
				[
					'label' => esc_html__( 'Products Forms', 'ova-brw' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

	            $this->add_control(
					'forms_bgcolor',
					[
						'label'  	=> esc_html__( 'Background Color', 'ova-brw' ),
						'type' 	 	=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-forms-product .forms-wrapper' => 'background-color: {{VALUE}};',
						],
					]
				);

			    $this->add_responsive_control(
					'forms_border_radius',
					[
						'label' 	 => esc_html__( 'Border Radius', 'ova-brw' ),
						'type' 		 => \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors'  => [
							'{{WRAPPER}} .ova-forms-product .forms-wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}',
						],
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Box_Shadow::get_type(),
					[
						'name' 		=> 'forms_box_shadow',
						'label' 	=> esc_html__( 'Box Shadow', 'ova-brw' ),
						'selector' 	=> '{{WRAPPER}} .ova-forms-product .forms-wrapper',
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
			<div class="elementor-ovabrw-product-forms">
		        <?php wc_get_template( apply_filters( OVABRW_PREFIX.'element_product_forms_template', 'rental/loop/forms.php' ), [
		        	'id' => $product->get_id()
		        ]); ?>
			</div>
			<?php
		}
	}

	// Regiser new widget
	$widgets_manager->register( new OVABRW_Product_Forms() );
}