<?php if ( !defined( 'ABSPATH' ) ) exit(); // Exit if accessed directly

/**
 * Class OVABRW_Product_Title
 */
if ( !class_exists( 'OVABRW_Product_Title', false ) ) {

	class OVABRW_Product_Title extends \Elementor\Widget_Base {

		/**
		 * Get widget name
		 */
		public function get_name() {		
			return 'ovabrw_product_title';
		}

		/**
		 * Get widget title
		 */
		public function get_title() {
			return esc_html__( 'Product Title', 'ova-brw' );
		}

		/**
		 * Get widget icon
		 */
		public function get_icon() {
			return 'eicon-t-letter';
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
				'section_title',
				[
					'label' => esc_html__( 'Title', 'ova-brw' ),
				]
			);

			    $this->add_control(
					'wc_content_warning',
					[
						'type' 	=> \Elementor\Controls_Manager::RAW_HTML,
						'raw' 	=> esc_html__( "Don't enter Product ID if you use this element in templates for product detail page.In Elementor Preview ( When empty Product ID ) , this element display an example product title of the latest product", 'ova-brw' ),
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
					'link',
					[
						'label' 	=> esc_html__( 'Link', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::URL,
						'dynamic' 	=> [
							'active' => true,
						],
						'default' => [
							'url' => '',
						],
						'separator' => 'before',
					]
				);

				$this->add_control(
					'header_size',
					[
						'label' 	=> esc_html__( 'HTML Tag', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::SELECT,
						'options' 	=> [
							'h1' 	=> 'H1',
							'h2' 	=> 'H2',
							'h3' 	=> 'H3',
							'h4' 	=> 'H4',
							'h5' 	=> 'H5',
							'h6' 	=> 'H6',
							'div' 	=> 'div',
							'span' 	=> 'span',
							'p' 	=> 'p',
						],
						'default' 	=> 'h1',
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
							'{{WRAPPER}} .ovabrw_product_title' => 'text-align: {{VALUE}};',
						],
					]
				);

				$this->end_controls_section();

				$this->start_controls_section(
					'section_title_style',
					[
						'label' => esc_html__( 'Title', 'ova-brw' ),
						'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
					]
				);

				$this->add_control(
					'title_color',
					[
						'label' 	=> esc_html__( 'Color', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ovabrw_product_title .ovabrw_title' => 'color: {{VALUE}};',
						],
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 	 	=> 'typography',
						'selector' 	=> '{{WRAPPER}} .ovabrw_product_title .ovabrw_title',
					]
				);

				$this->add_responsive_control(
					'margin_title',
					[
						'label' 	 => esc_html__( 'Margin', 'ova-brw' ),
						'type' 		 => \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors'  => [
							'{{WRAPPER}} .ovabrw_product_title .ovabrw_title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Text_Shadow::get_type(),
					[
						'name' 		=> 'text_shadow',
						'selector' 	=> '{{WRAPPER}} .ovabrw_product_title .ovabrw_title',
					]
				);

				$this->add_control(
					'blend_mode',
					[
						'label' 	=> esc_html__( 'Blend Mode', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::SELECT,
						'options' 	=> [
							'' 				=> esc_html__( 'Normal', 'ova-brw' ),
							'multiply' 	  	=> esc_html__( 'Multiply', 'ova-brw' ),
							'screen' 	  	=> esc_html__( 'Screen', 'ova-brw' ),
							'overlay' 	  	=> esc_html__( 'Overlay', 'ova-brw' ),
							'darken' 	  	=> esc_html__( 'Darken', 'ova-brw' ),
							'lighten' 	  	=> esc_html__( 'Lighten', 'ova-brw' ),
							'color-dodge' 	=> esc_html__( 'Color Dodge', 'ova-brw' ),
							'saturation'  	=> esc_html__( 'Saturation', 'ova-brw' ),
							'color' 	  	=> esc_html__( 'Color', 'ova-brw' ),
							'difference'  	=> esc_html__( 'Difference', 'ova-brw' ),
							'exclusion'   	=> esc_html__( 'Exclusion'. 'ova-brw' ),
							'hue' 		  	=> esc_html__( 'Hue', 'ova-brw' ),
							'luminosity'  	=> esc_html__( 'Luminosity', 'ova-brw' ),
						],
						'selectors' => [
							'{{WRAPPER}} .ovabrw_product_title .ovabrw_title' => 'mix-blend-mode: {{VALUE}}',
						],
						'separator' => 'none',
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
			endif;

			// Get link
			$link = isset( $settings['link']['url'] ) ? $settings['link']['url'] : '';

			// Target
			$target = '_self';
			if ( isset( $settings['link']['is_external'] ) && $settings['link']['is_external'] ) {
				$target = '_blank';
			}

			// Get header size
			$header_size = ovabrw_get_meta_data( 'header_size', $settings );

			// Get product title
			$title = $product->get_title();
			if ( !$title ): ?>
				<div class="ovabrw_elementor_no_product">
					<span><?php echo wp_kses_post( $this->get_title() ); ?></span>
				</div>
			<?php return;
			endif; ?>
			<div class="ovabrw_product_title">
				<?php if ( $link ): ?>
					<a href="<?php echo esc_url( $link ); ?>" target="<?php echo esc_attr( $blank ); ?>">
						<<?php echo esc_attr( $header_size ); ?> class="ovabrw_title">
							<?php echo wp_kses_post( $title ); ?>
						</<?php echo esc_attr( $header_size ); ?>>
					</a>
				<?php else: ?>
					<<?php echo esc_attr( $header_size ); ?> class="ovabrw_title">
						<?php echo wp_kses_post( $title ); ?>
					</<?php echo esc_attr( $header_size ); ?>>
				<?php endif; ?>
			</div>
			<?php
		}
	}

	// Regiser new widget
	$widgets_manager->register( new OVABRW_Product_Title() );
}