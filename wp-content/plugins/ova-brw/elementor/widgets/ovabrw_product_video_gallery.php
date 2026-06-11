<?php if ( !defined( 'ABSPATH' ) ) exit(); // Exit if accessed directly

/**
 * Class OVABRW_Product_Video_Gallery
 */
if ( !class_exists( 'OVABRW_Product_Video_Gallery', false ) ) {

	class OVABRW_Product_Video_Gallery extends \Elementor\Widget_Base {

		/**
		 * Get widget name
		 */
		public function get_name() {		
			return 'ovabrw_product_video_gallery';
		}

		/**
		 * Get widget title
		 */
		public function get_title() {
			return esc_html__( 'Video & Gallery', 'ova-brw' );
		}

		/**
		 * Get widget icon
		 */
		public function get_icon() {
			return 'eicon-video-playlist';
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
						'raw' 	=> esc_html__( "Don't enter Product ID if you use this element in templates for product detail page.In Elementor Preview ( When empty Product ID ) , this element display an example product video and gallery of the latest product", 'ova-brw' ),
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
					'show_video',
					[
						'label' 		=> esc_html__( 'Show Video Button', 'ova-brw' ),
						'type' 			=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 		=> esc_html__( 'Show', 'ova-brw' ),
						'label_off' 	=> esc_html__( 'Hide', 'ova-brw' ),
						'return_value' 	=> 'yes',
						'default' 		=> 'yes',
					]
				);

				$this->add_control(
					'show_gallery',
					[
						'label' 		=> esc_html__( 'Show Gallery Button', 'ova-brw' ),
						'type' 			=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 		=> esc_html__( 'Show', 'ova-brw' ),
						'label_off' 	=> esc_html__( 'Hide', 'ova-brw' ),
						'return_value' 	=> 'yes',
						'default' 		=> 'yes',
					]
				);

				$this->add_control(
					'show_share',
					[
						'label' 		=> esc_html__( 'Show Share Button', 'ova-brw' ),
						'type' 			=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 		=> esc_html__( 'Show', 'ova-brw' ),
						'label_off' 	=> esc_html__( 'Hide', 'ova-brw' ),
						'return_value' 	=> 'yes',
						'default' 		=> 'yes',
					]
				);

			$this->end_controls_section();


			$this->start_controls_section(
				'section_product_video_gallery_button_style',
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
								'name' 		=> 'content_typography_title_btn',
								'label' 	=> esc_html__( 'Typography', 'ova-brw' ),
								'selector' 	=> '{{WRAPPER}} .ova-video-gallery .btn-video-gallery',
								
							]
						);

						$this->add_control(	
							'color_title_btn',
							[
								'label' 	=> esc_html__( 'Color', 'ova-brw' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ova-video-gallery .btn-video-gallery' => 'color : {{VALUE}};',
								],
							]
						);

						$this->add_control(	
							'color_icon_btn',
							[
								'label' 	=> esc_html__( 'Icon Color', 'ova-brw' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ova-video-gallery .btn-video-gallery i' => 'color : {{VALUE}};',
								],
							]
						);

						$this->add_control(
							'color_button_background',
							[
								'label' 	=> esc_html__( 'Background ', 'ova-brw' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ova-video-gallery .btn-video-gallery' => 'background-color : {{VALUE}};',
								],
							]
						);

						$this->add_group_control(
							\Elementor\Group_Control_Border::get_type(),
							[
								'name' 		=> 'button_border',
								'label' 	=> esc_html__( 'Border', 'ova-brw' ),
								'selector' 	=> '{{WRAPPER}} .ova-video-gallery .btn-video-gallery',
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
							'color_title_btn_hover',
							[
								'label' 	=> esc_html__( 'Color', 'ova-brw' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ova-video-gallery .btn-video-gallery:hover' => 'color : {{VALUE}} ;',
								],
							]
						);

						$this->add_control(
							'color_button_hover_background',
							[
								'label' 	=> esc_html__( 'Background', 'ova-brw' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ova-video-gallery .btn-video-gallery:hover' => 'background-color : {{VALUE}};',
								],
							]
						);

						$this->add_group_control(
							\Elementor\Group_Control_Border::get_type(),
							[
								'name' 		=> 'button_border_hover',
								'label' 	=> esc_html__( 'Border', 'ova-brw' ),
								'selector' 	=> '{{WRAPPER}} .ova-video-gallery .btn-video-gallery:hover',
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
								'{{WRAPPER}} .ova-video-gallery .btn-video-gallery' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							],
							'separator' 	=> 'before'
						]
					);
					
					$this->add_responsive_control(
						'padding_button',
						[
							'label' 		=> esc_html__( 'Padding', 'ova-brw' ),
							'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
							'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
							'selectors' 	=> [
								'{{WRAPPER}} .ova-video-gallery .btn-video-gallery' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
			<div class="elementor-video-gallery">
				<?php wc_get_template( 'rental/loop/video-gallery.php', [
					'id' 			=> $product->get_id(),
					'show_video' 	=> ovabrw_get_meta_data( 'show_video', $settings ),
					'show_gallery' 	=> ovabrw_get_meta_data( 'show_gallery', $settings ),
					'show_share' 	=> ovabrw_get_meta_data( 'show_share', $settings )
				]); ?>
			</div>
			<?php
		}
	}

	// Regiser new widget
	$widgets_manager->register( new OVABRW_Product_Video_Gallery() );
}