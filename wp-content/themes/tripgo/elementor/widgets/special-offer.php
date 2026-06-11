<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class Tripgo_Elementor_Special_Offer
 */
if ( !class_exists( 'Tripgo_Elementor_Special_Offer', false ) ) {

	class Tripgo_Elementor_Special_Offer extends \Elementor\Widget_Base {

		/**
		 * Get widget name
		 */
		public function get_name() {
			return 'tripgo_elementor_special_offer';
		}

		/**
		 * Get widget title
		 */
		public function get_title() {
			return esc_html__( 'Special Offer', 'tripgo' );
		}

		/**
		 * Get widget icon
		 */
		public function get_icon() {
			return 'eicon-image-box';
		}

		/**
		 * Get widget categories
		 */
		public function get_categories() {
			return [ 'tripgo' ];
		}

		/**
		 * Get style depends
		 */
		public function get_style_depends() {
			return [ 'tripgo-elementor-special-offer' ];
		}
		
		/**
		 * Register controls
		 */
		protected function register_controls() {

			$this->start_controls_section(
				'section_content',
				[
					'label' => esc_html__( 'Content', 'tripgo' ),
				]
			);

				$this->add_control(
					'version',
					[
						'label' 	=> esc_html__( 'Version', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::SELECT,
						'default' 	=> 'version_1',
						'options' 	=> [
							'version_1'  => esc_html__( 'Version 1', 'tripgo' ),
							'version_2'  => esc_html__( 'Version 2', 'tripgo' ),
							'version_3'  => esc_html__( 'Version 3', 'tripgo' ),
							'version_4'  => esc_html__( 'Version 4', 'tripgo' ),	
							'version_5'  => esc_html__( 'Version 5', 'tripgo' ),
						],
					]
				);

				$this->add_control(
					'link_address',
					[
						'label'   		=> esc_html__( 'Link', 'tripgo' ),
						'type'    		=> \Elementor\Controls_Manager::URL,
						'show_external' => false,
					]
				);

				$this->add_control(
					'image',
					[
						'label' 	=> esc_html__( 'Choose Image', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::MEDIA,
						'default' 	=> [
							'url' 	=> \Elementor\Utils::get_placeholder_image_src(),
						],
					]
				);
			
				$this->add_control(
					'title',
					[
						'label' 		=> esc_html__( 'Title', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::TEXT,
						'default' 		=> 'Special <br> Offers',
						'description' 	=> esc_html__( 'Can use <br> tag for line breaks', 'tripgo'),
					]
				);
				
				$this->add_control(
					'sale_type',
					[
						'label' 	=> esc_html__( 'Type Sale', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::SELECT,
						'default' 	=> 'save_up_to',
						'options' 	=> [
							'normal' 		=> esc_html__( 'Normal', 'tripgo' ),
							'up_to_off' 	=> esc_html__( 'Sale Off', 'tripgo' ),
							'save_up_to'  	=> esc_html__( 'Save Up To', 'tripgo' ),	
						],
					]
				);

				$this->add_control(
					'sub_title_normal',
					[
						'label' 	=> esc_html__( 'Sub Title', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::TEXT,
						'default' 	=> esc_html__( 'Plan your next trip', 'tripgo'),
						'condition' => [
							'sale_type' => 'normal',
						],
					]
				);
				
				$this->add_control(
					'sub_title_on_both_side_1',
					[
						'label' 	=> esc_html__( 'Sub Title 1', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::TEXT,
						'default' 	=> esc_html__( 'Up to', 'tripgo'),
						'condition' => [
							'sale_type' => 'up_to_off',
						],
					]
				);

				$this->add_control(
					'sub_title_on_both_side_2',
					[
						'label' 	=> esc_html__( 'Sub Title 2', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::TEXT,
						'default' 	=> esc_html__( 'off', 'tripgo'),
						'condition' => [
							'sale_type' => 'up_to_off',
						],
					]
				);

				$this->add_control(
					'sub_title_front',
					[
						'label' 	=> esc_html__( 'Sub Title', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::TEXT,
						'default' 	=> esc_html__( 'Want to save up to', 'tripgo'),
						'condition' => [
							'sale_type' => 'save_up_to',
						],
					]
				);

				$this->add_control(
					'discount_percent',
					[
						'label' 	=> esc_html__( 'Discount Percent', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::NUMBER,
						'min' 		=> 0,
						'max' 		=> 100,
						'step' 		=> 1,
						'default' 	=> 30,
						'condition' => [
							'sale_type' => ['save_up_to','up_to_off'],
						],
					]
				);

				$this->add_control(
					'show_button',
					[
						'label' 		=> esc_html__( 'Show button', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 		=> esc_html__( 'Show', 'tripgo' ),
						'label_off' 	=> esc_html__( 'Hide', 'tripgo' ),
						'return_value' 	=> 'yes',
						'default' 		=> 'yes',
					]
				);

				$this->add_control(
					'text_button',
					[
						'label' 	=> esc_html__( 'Text Button', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::TEXT,
						'default' 	=> esc_html__('View Deals', 'tripgo'),
						'condition' => [
							'show_button' => 'yes',
						],
					]
				);

				$this->add_control(
					'icon',
					[
						'label' 	=> esc_html__( 'Icon', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::ICONS,
						'condition' => [
							'show_button' => 'yes',	
						],
					]
				);

				$this->add_responsive_control(
					'size_icon',
					[
						'label' 		=> esc_html__( 'Size', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::SLIDER,
						'size_units' 	=> [ 'px'],
						'range' 		=> [
							'px' => [
								'min' 	=> 0,
								'max' 	=> 40,
								'step' 	=> 1,
							],
						],
						'selectors' => [
							'{{WRAPPER}} .ova-special-offer .btn-special-offer i' => 'font-size: {{SIZE}}{{UNIT}};',
						],
						'condition' => [
							'show_button' => 'yes',	
						],
					]
				);

			$this->end_controls_section();

			// Image Style 
			$this->start_controls_section(
				'section_image_style',
				[
					'label' => esc_html__( 'Image', 'tripgo' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_group_control(
					\Elementor\Group_Control_Background::get_type(),
					[
						'name' 		=> 'background',
						'label' 	=> esc_html__( 'Background', 'tripgo' ),
						'types' 	=> [ 'classic', 'gradient', 'video' ],
						'selector' 	=> '{{WRAPPER}} .ova-special-offer .mask',
					]
				);

				$this->add_responsive_control(
					'image_height',
					[
						'label' 		=> esc_html__( 'Height', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::SLIDER,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'range' 		=> [
							'px' => [
								'min' 	=> 300,
								'max' 	=> 500,
								'step' 	=> 10,
							],
							'%' => [
								'min' 	=> 50,
								'max' 	=> 100,
								'step' 	=> 2,
							],
						],
						'selectors' => [
							'{{WRAPPER}} .ova-special-offer .special-offer-img' => 'height: {{SIZE}}{{UNIT}};',
						],
					]
				);

			$this->end_controls_section();

			// SECTION TAB STYLE CONTENT VERSION 5
			$this->start_controls_section(
				'section_content_style',
				[
					'label' 	=> esc_html__( 'Content', 'tripgo' ),
					'tab' 		=> \Elementor\Controls_Manager::TAB_STYLE,
					'condition' => [
						'version' => 'version_5'
					]
				]
			);

				$this->add_control(
					'bgcolor_content',
					[
						'label' 	=> esc_html__( 'Background Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-special-offer .special-offer-content' => 'background-color : {{VALUE}};'	
						],
					]
				);

				$this->add_responsive_control(
					'padding_content',
					[
						'label' 		=> esc_html__( 'Padding', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-special-offer .special-offer-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

			$this->end_controls_section(); // END SECTION TAB STYLE CONTENT V5

			// SECTION TAB STYLE TITLE
			$this->start_controls_section(
				'section_title_style',
				[
					'label' => esc_html__( 'Title', 'tripgo' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'content_typography_title',
						'label' 	=> esc_html__( 'Typography', 'tripgo' ),
						'selector' 	=> '{{WRAPPER}} .ova-special-offer .title',
					]
				);

				$this->add_control(
					'color_title',
					[
						'label' 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-special-offer .title' => 'color : {{VALUE}};'
						],
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Text_Shadow::get_type(),
					[
						'name' 		=> 'title_text_shadow',
						'label' 	=> esc_html__( 'Text Shadow', 'tripgo' ),
						'selector' 	=> '{{WRAPPER}} .ova-special-offer .title',
					]
				);

				$this->add_responsive_control(
					'padding_title',
					[
						'label' 		=> esc_html__( 'Padding', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-special-offer .title ' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

			$this->end_controls_section(); // END SECTION TAB STYLE TITLE

			// SECTION TAB STYLE SUB TITLE
			$this->start_controls_section(
				'section_sub_title',
				[
					'label' => esc_html__( 'Sub Title', 'tripgo' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'content_typography_sub_title',
						'label' 	=> esc_html__( 'Typography', 'tripgo' ),
						'selector' 	=> '{{WRAPPER}} .ova-special-offer .sub-title-wrapper .sub-title',
					]
				);

				$this->add_control(
					'color_sub_title',
					[
						'label' 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-special-offer .sub-title-wrapper .sub-title' => 'color : {{VALUE}};'
						],
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Text_Shadow::get_type(),
					[
						'name' 		=> 'sub_title_text_shadow',
						'label' 	=> esc_html__( 'Text Shadow', 'tripgo' ),
						'selector' 	=> '{{WRAPPER}} .ova-special-offer .sub-title-wrapper .sub-title',
					]
				);

				$this->add_responsive_control(
					'padding_sub_title',
					[
						'label' 		=> esc_html__( 'Padding', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-special-offer .sub-title-wrapper .sub-title ' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);
				
			$this->end_controls_section(); // END SECTION TAB STYLE SUB TITLE

			// SECTION TAB STYLE SALE
			$this->start_controls_section(
				'section_sale',
				[
					'label' 	=> esc_html__( 'Discount', 'tripgo' ),
					'tab' 		=> \Elementor\Controls_Manager::TAB_STYLE,
					'condition' => [
						'sale_type' => ['save_up_to','up_to_off'],
					],
				]
			);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'content_typography_sale',
						'label' 	=> esc_html__( 'Typography', 'tripgo' ),
						'selector' 	=> '{{WRAPPER}} .ova-special-offer .sub-title-wrapper .discount',
					]
				);

				$this->add_control(
					'color_sale',
					[
						'label' 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-special-offer .sub-title-wrapper .discount' => 'color : {{VALUE}};'
						],
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Text_Shadow::get_type(),
					[
						'name' 		=> 'sale_text_shadow',
						'label' 	=> esc_html__( 'Text Shadow', 'tripgo' ),
						'selector' 	=> '{{WRAPPER}} .ova-special-offer .sub-title-wrapper .discount',
					]
				);

				$this->add_responsive_control(
					'padding_sale',
					[
						'label' 		=> esc_html__( 'Padding', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-special-offer .sub-title-wrapper .discount' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

			$this->end_controls_section(); // END SECTION TAB STYLE SALE

			// SECTION TAB STYLE button
			$this->start_controls_section(
				'section_button',
				[
					'label' 	=> esc_html__( 'Button', 'tripgo' ),
					'tab' 		=> \Elementor\Controls_Manager::TAB_STYLE,
					'condition' => [
						'show_button' => 'yes',
					],
				]
			);

			$this->start_controls_tabs(
				'style_tabs_button'
			);

				$this->start_controls_tab(
					'style_normal_tab',
					[
						'label' => esc_html__( 'Normal', 'tripgo' ),
					]
				);

					$this->add_group_control(
						\Elementor\Group_Control_Typography::get_type(),
						[
							'name' 		=> 'content_typography_title_btn',
							'label' 	=> esc_html__( 'Typography', 'tripgo' ),
							'selector' 	=> '{{WRAPPER}} .ova-special-offer .btn-special-offer .text',
							
						]
					);

					$this->add_control(	
						'color_title_btn',
						[
							'label' 	=> esc_html__( 'Color', 'tripgo' ),
							'type' 		=> \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ova-special-offer .btn-special-offer .text' => 'color : {{VALUE}};',
							],
						]
					);

					$this->add_control(	
						'color_icon_btn',
						[
							'label' 	=> esc_html__( 'Icon Color', 'tripgo' ),
							'type' 		=> \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ova-special-offer .btn-special-offer i' => 'color : {{VALUE}};',
							],
						]
					);

					$this->add_control(
						'color_button_background',
						[
							'label' 	=> esc_html__( 'Background ', 'tripgo' ),
							'type' 		=> \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ova-special-offer .btn-special-offer' => 'background-color : {{VALUE}};',
							],
						]
					);

				$this->end_controls_tab();

				$this->start_controls_tab(
					'style_hover_tab',
					[
						'label' => esc_html__( 'Hover', 'tripgo' ),
					]
				);

					$this->add_control(
						'color_title_btn_hover',
						[
							'label' 	=> esc_html__( 'Color', 'tripgo' ),
							'type' 		=> \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ova-special-offer .btn-special-offer:hover span' => 'color : {{VALUE}} ;',
							],
						]
					);

					$this->add_control(
						'color_button_hover_background',
						[
							'label' 	=> esc_html__( 'Background', 'tripgo' ),
							'type' 		=> \Elementor\Controls_Manager::COLOR,
							'selectors' => [
								'{{WRAPPER}} .ova-special-offer .btn-special-offer:hover' => 'background-color : {{VALUE}};',
							],
						]
					);

				$this->end_controls_tab();

			$this->end_controls_tabs();

			    $this->add_responsive_control(
					'margin_button',
					[
						'label' 		=> esc_html__( 'Margin', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-special-offer .btn-special-offer' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);
				
				$this->add_responsive_control(
					'padding_button',
					[
						'label' 		=> esc_html__( 'Padding', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-special-offer .btn-special-offer' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

			$this->end_controls_section(); // END SECTION TAB STYLE button	
		}

		/**
		 * Render template
		 */
		protected function render() {
			// Get settings
			$settings = $this->get_settings_for_display();

			// Get version
			$version = tripgo_get_meta_data( 'version', $settings );

			// Get title
			$title = tripgo_get_meta_data( 'title', $settings );

			// Get image URL
			$img_url = isset( $settings['image']['url'] ) && $settings['image']['url'] ? $settings['image']['url'] : '';

			// Get image ID
			$img_id = isset( $settings['image']['id'] ) && $settings['image']['id'] ? $settings['image']['id'] : '';

			// Get image title
			$image_title = get_the_title( $img_id );

			// Get image alt
			$image_alt = get_post_meta( $img_id, '_wp_attachment_image_alt', true );

			// Get link URL
			$link = isset( $settings['link_address']['url'] ) && $settings['link_address']['url'] ? $settings['link_address']['url'] : '';

			// Get link target
			$target = isset( $settings['link_address']['is_external'] ) && $settings['link_address']['is_external'] ? '_blank' : '_self';

			// Get link nofollow
			$nofollow = isset( $settings['link_address']['nofollow'] ) && $settings['link_address']['nofollow'] ? 'nofollow' : '';
	        
	        // Discount percent
			$discount_percent = tripgo_get_meta_data( 'discount_percent', $settings );
	        
	        // Type sale
	        $type = tripgo_get_meta_data( 'sale_type', $settings );

	        // Get sub normal
	        $sub_normal = tripgo_get_meta_data( 'sub_title_normal', $settings );

	        // Get sub front
			$sub_front = tripgo_get_meta_data( 'sub_title_front', $settings );

			// Get sub on both side 1
			$sub_on_both_side_1 = tripgo_get_meta_data( 'sub_title_on_both_side_1', $settings );

			// Get sub on both side 2
			$sub_on_both_side_2 = tripgo_get_meta_data( 'sub_title_on_both_side_2', $settings );
		    
		    // Show button	
			$show_btn = tripgo_get_meta_data( 'show_button', $settings );

			// Text button
			$text_btn = tripgo_get_meta_data( 'text_button', $settings );

			// Icon button
			$icon_btn = isset( $settings['icon']['value'] ) && $settings['icon']['value'] ? $settings['icon']['value'] : '';

			?>
			<div class="ova-special-offer ova-special-offer-<?php echo esc_attr( $version ); ?>">
				<?php if ( $link && ( 'version_4' === $version || 'version_5' === $version ) ): ?>
		        	<a href="<?php echo esc_url( $link ); ?>" target="<?php echo esc_attr( $target ); ?>" rel="<?php echo esc_attr( $nofollow ); ?>">
		        <?php endif; ?>
				    <div class="mask"></div>
					<img src="<?php echo esc_url( $img_url ); ?>" class="special-offer-img" title="<?php echo esc_attr( $image_title ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>">
				<?php if ( $link && ( 'version_4' === $version || 'version_5' === $version ) ): ?>
		        	</a>
		        <?php endif; ?>

			    <!-- Special Offer Content -->
				<div class="special-offer-content">
					<h3 class="title">
						<?php echo wp_kses_post( $title ); ?>
					</h3>
					<div class="sub-title-wrapper">
						<?php if ( 'up_to_off' === $type ): ?>
							<span class="sub-title">
								<?php echo esc_html( $sub_on_both_side_1 ); ?>	
							</span> 
							<span class="discount">
								<?php echo esc_html( $discount_percent ) . '%'; ?>	
							</span> 
							<span class="sub-title">
								<?php echo esc_html( $sub_on_both_side_2 ); ?>	
							</span>
						<?php elseif ( 'normal' === $type ): ?>	
							<span class="sub-title">
								<?php echo esc_html( $sub_normal ); ?>	
							</span> 
						<?php elseif ( 'save_up_to' === $type ): ?>
							<span class="sub-title">
								<?php echo esc_html( $sub_front ); ?>	
							</span> 
							<?php if ( $discount_percent ): ?>
								<span class="discount">
									<?php echo esc_html( $discount_percent ) . '%'; ?>	
								</span> 
							<?php endif;
						endif; ?>
					</div>
					
					<!-- Button -->
					<?php if ( 'yes' === $show_btn && ( $text_btn || $icon_btn ) ):
						if ( $link ): ?>
							<a href="<?php echo esc_url( $link ); ?>" target="<?php echo esc_attr( $target ); ?>" rel="<?php echo esc_attr( $nofollow ); ?>">
								<div class="btn-special-offer">
									<span class="text"> 
										<?php echo esc_html( $text_btn ) ?> 
									</span>
									<?php if ( $icon_btn ): ?>
										<i class="<?php echo esc_attr( $icon_btn ); ?>"></i>
									<?php endif; ?>
								</div>	
							</a>
						<?php else: ?>
							<div class="btn-special-offer">
								<span class="text"> 
									<?php echo esc_html( $text_btn ) ?> 
								</span>
								<?php if ( $icon_btn ): ?>
									<i class="<?php echo esc_attr( $icon_btn ); ?>"></i>
								<?php endif; ?>
							</div>
						<?php endif;
					endif; ?>
				</div>
			</div>
			<?php
		}
	}

	// init widget
	$widgets_manager->register( new Tripgo_Elementor_Special_Offer() );
}