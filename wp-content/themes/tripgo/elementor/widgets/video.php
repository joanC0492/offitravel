<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class Tripgo_Elementor_Video
 */
if ( !class_exists( 'Tripgo_Elementor_Video', false ) ) {

	class Tripgo_Elementor_Video extends \Elementor\Widget_Base {

		/**
		 * Get widget name
		 */
		public function get_name() {
			return 'tripgo_elementor_video';
		}

		/**
		 * Get widget title
		 */
		public function get_title() {
			return esc_html__( 'Ova Video', 'tripgo' );
		}

		/**
		 * Get widget icon
		 */
		public function get_icon() {
			return 'eicon-play-o';
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
			return [ 'tripgo-elementor-video' ];
		}

		/**
		 * Get script depends
		 */
		public function get_script_depends() {
			return [ 'tripgo-elementor-video' ];
		}
		
		/**
		 * Register controls
		 */
		protected function register_controls() {
			/* Begin section icon */
			$this->start_controls_section(
				'section_icon',
				[
					'label' => esc_html__( 'Icon', 'tripgo' ),
				]
			);

				$this->add_control(
					'icon_class',
					[
						'label' 	=> esc_html__( 'Icon Class', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::ICONS,
						'default' 	=> [
							'value' 	=> 'fa fa-play',
							'library' 	=> 'all',
						],
					]
				);

				$this->add_control(
					'icon_url_video',
					[
						'label' 		=> esc_html__( 'URL Video', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::TEXT,
						'placeholder' 	=> esc_html__( 'Enter your URL', 'tripgo' ) . ' (YouTube)',
						'default' 		=> 'https://www.youtube.com/watch?v=XHOmBV4js_E',
					]
				);

				$this->add_control(
					'icon_text',
					[
						'label' 	=> esc_html__( 'Text', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::TEXT,
						'default' 	=> esc_html__( '', 'tripgo' ),
					]
				);

				$this->add_control(
		            'link',
		            [
		                'label' 	=> esc_html__( 'Link', 'tripgo' ),
		                'type' 		=> \Elementor\Controls_Manager::URL,
		                'dynamic' 	=> [
		                    'active' => true,
		                ],
		                'condition' => [
		                	'icon_url_video' => '',
		                ],
		            ]
		        );

		        $this->add_control(
					'video_options',
					[
						'label' 	=> esc_html__( 'Video Options', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::HEADING,
						'separator' => 'before',
						'condition' => [
							'icon_url_video!' => '',
						],
					]
				);

				$this->add_control(
					'autoplay_video',
					[
						'label' 	=> esc_html__( 'Autoplay', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 	=> esc_html__( 'Yes', 'tripgo' ),
						'label_off' => esc_html__( 'No', 'tripgo' ),
						'default' 	=> 'yes',
						'condition' => [
							'icon_url_video!' => '',
						],
					]
				);

				$this->add_control(
					'mute_video',
					[
						'label' 	=> esc_html__( 'Mute', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 	=> esc_html__( 'Yes', 'tripgo' ),
						'label_off' => esc_html__( 'No', 'tripgo' ),
						'default' 	=> 'no',
						'condition' => [
							'icon_url_video!' => '',
						],
					]
				);

				$this->add_control(
					'loop_video',
					[
						'label' 	=> esc_html__( 'Loop', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 	=> esc_html__( 'Yes', 'tripgo' ),
						'label_off' => esc_html__( 'No', 'tripgo' ),
						'default' 	=> 'yes',
						'condition' => [
							'icon_url_video!' => '',
						],
					]
				);

				$this->add_control(
					'player_controls_video',
					[
						'label' 	=> esc_html__( 'Player Controls', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 	=> esc_html__( 'Yes', 'tripgo' ),
						'label_off' => esc_html__( 'No', 'tripgo' ),
						'default' 	=> 'yes',
						'condition' => [
							'icon_url_video!' => '',
						],
					]
				);

				$this->add_control(
					'modest_branding_video',
					[
						'label' 	=> esc_html__( 'Modest Branding', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 	=> esc_html__( 'Yes', 'tripgo' ),
						'label_off' => esc_html__( 'No', 'tripgo' ),
						'default' 	=> 'yes',
						'condition' => [
							'icon_url_video!' => '',
						],
					]
				);

				$this->add_control(
					'show_info_video',
					[
						'label' 	=> esc_html__( 'Show Info', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 	=> esc_html__( 'Yes', 'tripgo' ),
						'label_off' => esc_html__( 'No', 'tripgo' ),
						'default' 	=> 'no',
						'condition' => [
							'icon_url_video!' => '',
						],
					]
				);

			$this->end_controls_section(); /* END section icon */

			/* Begin section icon style */
			$this->start_controls_section(
				'section_icon_style',
				[
					'label' => esc_html__( 'Icon', 'tripgo' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
				]
			);	

				$this->start_controls_tabs( 'tabs_icon_style' );
					$this->start_controls_tab(
			            'tab_icon_normal',
			            [
			                'label' => esc_html__( 'Normal', 'tripgo' ),
			            ]
			        );

			        	$this->add_control(
				            'icon_color_normal',
				            [
				                'label' 	=> esc_html__( 'Color', 'tripgo' ),
				                'type' 		=> \Elementor\Controls_Manager::COLOR,
				                'selectors' => [
				                    '{{WRAPPER}} .ova-video .icon-content-view .content i' => 'color: {{VALUE}};',
				                ],
				            ]
				        );

				        $this->add_control(
				            'icon_background_normal',
				            [
				                'label' 	=> esc_html__( 'Background', 'tripgo' ),
				                'type' 		=> \Elementor\Controls_Manager::COLOR,
				                'selectors' => [
				                    '{{WRAPPER}} .ova-video .icon-content-view .content' => 'background-color: {{VALUE}};',
				                ],
				            ]
				        );

				        $this->add_group_control(
							\Elementor\Group_Control_Background::get_type(),
							[
								'name' 		=> 'icon_bg_gradient_normal',
								'label' 	=> esc_html__( 'Background Gradient', 'tripgo' ),
								'types' 	=> [ 'gradient' ],
								'selector' 	=> '{{WRAPPER}} .ova-video .icon-content-view .content',
							]
						);

			        $this->end_controls_tab();

			        $this->start_controls_tab(
			            'tab_icon_hover',
			            [
			                'label' => esc_html__( 'Hover', 'tripgo' ),
			            ]
			        );

			        	$this->add_control(
				            'icon_color_hover',
				            [
				                'label' 	=> esc_html__( 'Color', 'tripgo' ),
				                'type' 		=> \Elementor\Controls_Manager::COLOR,
				                'selectors' => [
				                    '{{WRAPPER}} .ova-video .icon-content-view .content:hover i' => 'color: {{VALUE}};',
				                ],
				            ]
				        );

			        	$this->add_control(
				            'icon_background_hover',
				            [
				                'label' 	=> esc_html__( 'Background', 'tripgo' ),
				                'type' 		=> \Elementor\Controls_Manager::COLOR,
				                'selectors' => [
				                    '{{WRAPPER}} .ova-video .icon-content-view .content:hover' => 'background-color: {{VALUE}};',
				                ],     
				            ]
				        );

				        $this->add_group_control(
							\Elementor\Group_Control_Background::get_type(),
							[
								'name' 		=> 'icon_bg_gradient_hover',
								'label' 	=> esc_html__( 'Background Gradient', 'tripgo' ),
								'types' 	=> [ 'gradient' ],
								'selector' 	=> '{{WRAPPER}} .ova-video .icon-content-view .content:hover',
							]
						);

			        $this->end_controls_tab();
				$this->end_controls_tabs();

				$this->add_responsive_control(
					'icon_width',
					[
						'label' 	=> esc_html__( 'Width', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::SLIDER,
						'default' 	=> [
							'unit' 	=> 'px',
						],
						'range' 	=> [
							'px' => [
								'min' => 0,
								'max' => 400,
							],
							'%' => [
								'min' => 0,
								'max' => 100,
							],
						],
						'size_units' 	=> [ '%', 'px' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-video .icon-content-view .content' => 'width: {{SIZE}}{{UNIT}}; min-width: {{SIZE}}{{UNIT}};',
						],
						'separator' => 'before'
					]
				);

				$this->add_responsive_control(
					'icon_height',
					[
						'label' 	=> esc_html__( 'Height', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::SLIDER,
						'default' 	=> [
							'unit' 	=> 'px',
						],
						'range' => [
							'px' => [
								'min' => 0,
								'max' => 400,
							],
							'%' => [
								'min' => 0,
								'max' => 100,
							],
						],
						'size_units' 	=> [ '%', 'px' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-video .icon-content-view .content' => 'height: {{SIZE}}{{UNIT}}; min-height: {{SIZE}}{{UNIT}};',
						],
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'icon_typography',
						'selector' 	=> '{{WRAPPER}} .ova-video .icon-content-view .content i',
					]
				);

				$this->add_group_control(
		            \Elementor\Group_Control_Border::get_type(), [
		                'name' 		=> 'icon_before_border',
		                'selector' 	=> '{{WRAPPER}} .ova-video .icon-content-view .content:after',
		                'separator' => 'before',  
		            ]
		        );

				$this->add_group_control(
					\Elementor\Group_Control_Box_Shadow::get_type(),
					[
						'name' 		=> 'icon_box_shadow',
						'label' 	=> esc_html__( 'Box Shadow', 'tripgo' ),
						'selector' 	=> '{{WRAPPER}} .ova-video .icon-content-view .content',
					]
				);

		        $this->add_control(
		            'icon_border_radius',
		            [
		                'label' 		=> esc_html__( 'Border Radius', 'tripgo' ),
		                'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
		                'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
		                'selectors' 	=> [
		                    '{{WRAPPER}} .ova-video .icon-content-view .content'	=> 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		                ],
		            ]
		        );

		        $this->add_responsive_control(
		            'content_margin',
		            [
		                'label' 		=> esc_html__( 'Margin', 'tripgo' ),
		                'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
		                'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
		                'selectors' 	=> [
		                    '{{WRAPPER}} .ova-video .icon-content-view .content' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		                ],
		            ]
		        );

		    $this->end_controls_section();

		    /* Begin text Style */
			$this->start_controls_section(
	            'text_style',
	            [
	                'label' => esc_html__( 'Text', 'tripgo' ),
	                'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
	            ]
	        );

	            $this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'text_typography',
						'selector' 	=> '{{WRAPPER}} .ova-video .icon-content-view .ova-text',
					]
				);

				$this->add_control(
					'text_color',
					[
						'label' 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-video .icon-content-view .ova-text, {{WRAPPER}} .ova-video .icon-content-view .ova-text a' => 'color: {{VALUE}};',
						],
					]
				);

				$this->add_control(
					'text_color_hover',
					[
						'label' 	=> esc_html__( 'Color Hover', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-video .icon-content-view .ova-text:hover a, {{WRAPPER}} .ova-video .icon-content-view .ova-text:hover' => 'color: {{VALUE}};',
						],
					]
				);

				$this->add_responsive_control(
		            'text_padding',
		            [
		                'label' 		=> esc_html__( 'Padding', 'tripgo' ),
		                'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
		                'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
		                'selectors' 	=> [
		                    '{{WRAPPER}} .ova-video .icon-content-view .ova-text' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		                ],
		            ]
		        );

	        $this->end_controls_section(); /* END text style */
		}

		/**
		 * Render template
		 */
		protected function render() {
			// Get settings
			$settings = $this->get_settings_for_display();

			// Get class icon
			$icon_class = tripgo_get_meta_data( 'icon_class', $settings );

			// Get video URL
			$url_video = tripgo_get_meta_data( 'icon_url_video', $settings );

			// Get icon text
			$icon_text = tripgo_get_meta_data( 'icon_text', $settings );

			// Get link URL
			$link_url = isset( $settings['link']['url'] ) && $settings['link']['url'] ? $settings['link']['url'] : '';

			// Get link target
			$link_target = isset( $settings['link']['is_external'] ) && $settings['link']['is_external'] ? '_blank' : '_self';

			// Get link nofollow
			$nofollow = isset( $settings['link']['nofollow'] ) && $settings['link']['nofollow'] ? 'nofollow' : '';

			// Autoplay
			$autoplay = tripgo_get_meta_data( 'autoplay_video', $settings );

			// Mute
			$mute = tripgo_get_meta_data( 'mute_video', $settings );

			// Loop
			$loop = tripgo_get_meta_data( 'loop_video', $settings );

			// Controls
			$controls = tripgo_get_meta_data( 'player_controls_video', $settings );

			// Modest
			$modest = tripgo_get_meta_data( 'modest_branding_video', $settings );

			// Show info
			$show_info = tripgo_get_meta_data( 'show_info_video', $settings );

			?>
	        <div class="ova-video">
				<div class="icon-content-view video_active">
					<?php if ( $url_video ): ?>
						<div class="content video-btn" id="ova-video" 
							data-src="<?php echo esc_url( $url_video ); ?>" 
							data-autoplay="<?php echo esc_attr( $autoplay ); ?>" 
							data-mute="<?php echo esc_attr( $mute ); ?>" 
							data-loop="<?php echo esc_attr( $loop ); ?>" 
							data-controls="<?php echo esc_attr( $controls ); ?>" 
							data-modest="<?php echo esc_attr( $modest ); ?>" 
							data-show_info="<?php echo esc_attr( $show_info ); ?>">
							<?php \Elementor\Icons_Manager::render_icon( $icon_class, [ 'aria-hidden' => 'true' ] ); ?>
						</div>
					<?php else: ?>
						<div class="content">
							<?php \Elementor\Icons_Manager::render_icon( $icon_class, [ 'aria-hidden' => 'true' ] ); ?>
						</div>
					<?php endif;

					// Icon text
					if ( $icon_text ): ?>
						<p class="ova-text">
							<?php if ( $link_url ): ?>
								<a href="<?php echo esc_url( $link_url ); ?>" target="<?php echo esc_attr( $target ); ?>" rel="<?php echo esc_attr( $nofollow ); ?>">
									<?php echo esc_html( $icon_text ); ?>
								</a>
							<?php else:
								echo esc_html( $icon_text );
							endif; ?>
						</p>
					<?php endif; ?>
				</div>
				<div class="modal-container">
					<div class="modal">
						<i class="ovaicon-cancel"></i>
						<iframe class="modal-video" allow="autoplay" allowFullScreen="allowFullScreen" frameBorder="0"></iframe>
					</div>
				</div>
			</div>
			<?php
		}
	}

	// init widget
	$widgets_manager->register( new Tripgo_Elementor_Video() );
}