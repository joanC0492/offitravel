<?php if ( !defined( 'ABSPATH' ) ) exit(); // Exit if accessed directly

/**
 * Class OVABRW_Product_Content
 */
if ( !class_exists( 'OVABRW_Product_Content' ) ) {

	class OVABRW_Product_Content extends \Elementor\Widget_Base {

		/**
		 * Get widget name
		 */
		public function get_name() {		
			return 'ovabrw_product_content';
		}

		/**
		 * Get widget title
		 */
		public function get_title() {
			return esc_html__( 'Product Content', 'ova-brw' );
		}

		/**
		 * Get widget icon
		 */
		public function get_icon() {
			return 'eicon-product-description';
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
						'raw' 	=> esc_html__( "Don't enter Product ID if you use this element in templates for product detail page.In Elementor Preview ( When empty Product ID ) , this element display an example product content of the latest product", 'ova-brw' ),
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
				'section_product_short_description_style',
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
					'align',
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
							'justify' => [
								'title' => esc_html__( 'Justified', 'ova-brw' ),
								'icon' 	=> 'eicon-text-align-justify',
							],
						],
						'default' 	=> '',
						'selectors' => [
							'{{WRAPPER}}' => 'text-align: {{VALUE}};',
						],
					]
				);

				$this->add_control(
					'text_color',
					[
						'label'  	=> esc_html__( 'Text Color', 'ova-brw' ),
						'type' 	 	=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .elementor-ovabrw-product-content .content-product-item.tour-description' => 'color: {{VALUE}};',
						],
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 	 	=> 'text_typography',
						'selector' 	=> '{{WRAPPER}} .elementor-ovabrw-product-content .content-product-item.tour-description',
					]
				);

				$this->add_control(
					'heading_color',
					[
						'label'  	=> esc_html__( 'Heading Color', 'ova-brw' ),
						'type' 	 	=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .elementor-ovabrw-product-content .content-product-item.tour-description h2, {{WRAPPER}} .elementor-ovabrw-product-content .content-product-item.tour-description h4' => 'color: {{VALUE}};',
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
			<div class="elementor-ovabrw-product-content">
				<?php wc_get_template( apply_filters( OVABRW_PREFIX.'element_product_content_template' ,'rental/loop/description.php' ), [
					'id' => $product->get_id()
				]); ?>
			</div>
			<?php
		}
	}

	// Regiser new widget
	$widgets_manager->register( new OVABRW_Product_Content() );
}