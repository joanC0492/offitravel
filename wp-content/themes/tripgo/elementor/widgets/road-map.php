<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class Tripgo_Elementor_Road_Map
 */
if ( !class_exists( 'Tripgo_Elementor_Road_Map', false ) ) {

	class Tripgo_Elementor_Road_Map extends \Elementor\Widget_Base {

		/**
		 * Get widget name
		 */
		public function get_name() {
			return 'tripgo_elementor_road_map';
		}

		/**
		 * Get widget title
		 */
		public function get_title() {
			return esc_html__( 'Road Map', 'tripgo' );
		}

		/**
		 * Get widget icon
		 */
		public function get_icon() {
			return 'eicon-time-line';
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
			return [ 'tripgo-elementor-road-map' ];
		}

		/**
		 * Get script depends
		 */
		public function get_script_depends() {
			return [ 'ova-appear', 'tripgo-elementor-road-map' ];
		}
		
		/**
		 * Register controls
		 */
		protected function register_controls() {

			// Begin section road map
			$this->start_controls_section(
					'section_road_map',
					[
						'label' => esc_html__( 'Road Map', 'tripgo' ),
					]
				);	
				
				// Version 1
		        $repeater = new \Elementor\Repeater();

					$repeater->add_control(
						'date',
						[
							'label' 	=> esc_html__( 'Date', 'tripgo' ),
							'type' 		=> \Elementor\Controls_Manager::NUMBER,
							'min' 		=> 1,
							'max' 		=> 9999,
							'step' 		=> 1,
							'default' 	=> 2023,
						]
					);

					$repeater->add_control(
						'date_active',
						[
							'label' 		=> esc_html__( 'Active Date', 'tripgo' ),
							'type' 			=> \Elementor\Controls_Manager::SWITCHER,
							'label_on' 		=> esc_html__( 'Yes', 'tripgo' ),
							'label_off' 	=> esc_html__( 'No', 'tripgo' ),
							'return_value' 	=> 'yes',
							'default' 		=> 'no',
						]
					);

					$repeater->add_control(
						'image',
						[
							'label' 	=> esc_html__( 'Choose Image', 'tripgo' ),
							'type' 		=> \Elementor\Controls_Manager::MEDIA,
							'default' 	=> [
								'url' => \Elementor\Utils::get_placeholder_image_src(),
							],
						]
					);

					$repeater->add_control(
						'title',
						[
							'label' 		=> esc_html__( 'Title', 'tripgo' ),
							'type' 			=> \Elementor\Controls_Manager::TEXT,
							'default' 		=> esc_html__( 'When We Started', 'tripgo' ),
							'placeholder' 	=> esc_html__( 'Type your title here', 'tripgo' ),
						]
					);

					$repeater->add_control(
						'desc',
						[
							'label' 	=> esc_html__( 'Description', 'tripgo' ),
							'type' 		=> \Elementor\Controls_Manager::TEXTAREA,
							'default' 	=> esc_html__( 'Sit voluptatem accusantium doloremque laudantium totae aperiam eaque inventore', 'tripgo' ),
						]
					);

					$repeater->add_responsive_control(
						'animation_content',
						[
							'label' => esc_html__( 'Animation Content', 'tripgo' ),
							'type' 	=> \Elementor\Controls_Manager::ANIMATION,
						]
					);

					$repeater->add_control(
						'animation_duration_content',
						[
							'label' 	=> esc_html__( 'Animation Duration', 'tripgo' ),
							'type' 		=> \Elementor\Controls_Manager::SELECT,
							'default' 	=> '',
							'options' 	=> [
								'slow' 	=> esc_html__( 'Slow', 'tripgo' ),
								'' 		=> esc_html__( 'Normal', 'tripgo' ),
								'fast' 	=> esc_html__( 'Fast', 'tripgo' ),
							],
							'condition' => [
								'animation_content!' => '',
							],
						]
					);

					$repeater->add_control(
						'animation_delay_content',
						[
							'label' 	=> esc_html__( 'Animation Delay', 'tripgo' ) . ' (ms)',
							'type' 		=> \Elementor\Controls_Manager::NUMBER,
							'default' 	=> '',
							'min' 		=> 0,
							'step' 		=> 100,
							'condition' => [
								'animation_content!' => '',
							],
						]
					);

				$this->add_control(
					'road_map_items',
					[
						'label' 	=> esc_html__( 'Items', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::REPEATER,
						'fields' 	=> $repeater->get_controls(),
						'default' 	=> [
							[
								'date' 			=> 1993,
								'date_active' 	=> 'yes',
								'image' 		=> [
									'url' => \Elementor\Utils::get_placeholder_image_src()
								],
								'title' 		=> esc_html__( 'When We Started', 'tripgo' ),
								'desc' 			=> esc_html__( 'Sit voluptatem accusantium doloremque laudantium totae aperiam eaque inventore', 'tripgo' ),
							],
							[
								'date' 			=> 1995,
								'date_active' 	=> 'no',
								'image' 		=> [
									'url' => \Elementor\Utils::get_placeholder_image_src()
								],
								'title' 		=> esc_html__( 'Join Team Member', 'tripgo' ),
								'desc' 			=> esc_html__( 'Sit voluptatem accusantium doloremque laudantium totae aperiam eaque inventore', 'tripgo' ),
							],
							[
								'date' 			=> 1996,
								'date_active' 	=> 'no',
								'image' 		=> [
									'url' => \Elementor\Utils::get_placeholder_image_src()
								],
								'title' 		=> esc_html__( 'Golden Age’s', 'tripgo' ),
								'desc' 			=> esc_html__( 'Sit voluptatem accusantium doloremque laudantium totae aperiam eaque inventore', 'tripgo' ),
							],
							[
								'date' 			=> 1998,
								'date_active' 	=> 'no',
								'image' 		=> [
									'url' => \Elementor\Utils::get_placeholder_image_src()
								],
								'title' 		=> esc_html__( 'Win 1st Awards', 'tripgo' ),
								'desc' 			=> esc_html__( 'Sit voluptatem accusantium doloremque laudantium totae aperiam eaque inventore', 'tripgo' ),
							],
							[
								'date' 			=> 2001,
								'date_active' 	=> 'no',
								'image' 		=> [
									'url' => \Elementor\Utils::get_placeholder_image_src()
								],
								'title' 		=> esc_html__( 'Leading Company', 'tripgo' ),
								'desc' 			=> esc_html__( 'Sit voluptatem accusantium doloremque laudantium totae aperiam eaque inventore', 'tripgo' ),
							],
							[
								'date' 			=> 2005,
								'date_active' 	=> 'no',
								'image' 		=> [
									'url' => \Elementor\Utils::get_placeholder_image_src()
								],
								'title' 		=> esc_html__( 'Best Company In USA', 'tripgo' ),
								'desc' 			=> esc_html__( 'Sit voluptatem accusantium doloremque laudantium totae aperiam eaque inventore', 'tripgo' ),
							],
						],
						'title_field' => '{{{ title }}}',
					]
				);

			$this->end_controls_section(); // End section road map

			// General
			$this->start_controls_section(
				'general_style_section',
				[
					'label' => esc_html__( 'General', 'tripgo' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

			$this->add_responsive_control(
				'timeline_gap',
				[
					'label' 		=> esc_html__( 'Timeline Gap', 'tripgo' ),
					'type' 			=> \Elementor\Controls_Manager::SLIDER,
					'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
					'range' 		=> [
						'px' => [
							'min' 	=> 0,
							'max' 	=> 500,
							'step' 	=> 5,
						],
						'%' => [
							'min' => 0,
							'max' => 100,
						],
					],
					'selectors' => [
						'{{WRAPPER}} .ova-road-map .road-map-wrapper .timeline-column .timeline-item-right:not(:last-child)' => 'padding-bottom: {{SIZE}}{{UNIT}};',
						'{{WRAPPER}} .ova-road-map .road-map-wrapper .timeline-column .timeline-item-left:not(:last-child)' => 'padding-bottom: {{SIZE}}{{UNIT}};',
					],
				]
			);

			$this->add_control(
				'timeline_color',
				[
					'label' 	=> esc_html__( 'Timeline', 'tripgo' ),
					'type' 		=> \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ova-road-map .road-map-wrapper .timeline-column .timeline-item-left:before' => 'border-left-color: {{VALUE}}',
						'{{WRAPPER}} .ova-road-map .road-map-wrapper .timeline-column .timeline-item-right:before' => 'border-left-color: {{VALUE}}',
					],
				]
			);

			$this->add_control(
				'content_box_color',
				[
					'label' 	=> esc_html__( 'Content Box Background', 'tripgo' ),
					'type' 		=> \Elementor\Controls_Manager::COLOR,
					'selectors' => [
						'{{WRAPPER}} .ova-road-map .road-map-wrapper .timeline-column .timeline-info .content-info' => 'background: {{VALUE}}',
					],
				]
			);

			$this->end_controls_section();

			// Date Style
			$this->start_controls_section(
					'date_style_section',
					[
						'label' => esc_html__( 'Date', 'tripgo' ),
						'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
					]
				);

				$this->add_responsive_control(
					'date_border_radius',
					[
						'label' 		=> esc_html__( 'Border Radius', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::SLIDER,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'range' 		=> [
							'px' => [
								'min' 	=> 0,
								'max' 	=> 100,
								'step' 	=> 5,
							],
							'%' => [
								'min' => 0,
								'max' => 100,
							],
						],
						'selectors' => [
							'{{WRAPPER}} .ova-road-map .date' => 'border-radius: {{SIZE}}{{UNIT}};',
						],
					]
				);

				$this->add_responsive_control(
					'date_size',
					[
						'label' 		=> esc_html__( 'Size', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::SLIDER,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'range' 		=> [
							'px' => [
								'min' 	=> 0,
								'max' 	=> 200,
								'step' 	=> 5,
							],
							'%' => [
								'min' => 0,
								'max' => 100,
							],
						],
						'selectors' => [
							'{{WRAPPER}} .ova-road-map .date' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
						],
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'date_typography',
						'selector' 	=> '{{WRAPPER}} .ova-road-map .date',
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Box_Shadow::get_type(),
					[
						'name' 		=> 'date_box_shadow',
						'selector' 	=> '{{WRAPPER}} .ova-road-map .date',
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Border::get_type(),
					[
						'name' 		=> 'date_border',
						'selector' 	=> '{{WRAPPER}} .ova-road-map .date',
					]
				);

				$this->start_controls_tabs(
					'style_tabs'
				);

					$this->start_controls_tab(
						'style_normal_tab',
						[
							'label' => esc_html__( 'Normal', 'tripgo' ),
						]
					);

						$this->add_control(
							'date_color',
							[
								'label' 	=> esc_html__( 'Color', 'tripgo' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ova-road-map .date' => 'color: {{VALUE}}',
								],
							]
						);

						$this->add_control(
							'date_background_color',
							[
								'label' 	=> esc_html__( 'Background', 'tripgo' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ova-road-map .date' => 'background: {{VALUE}}',
								],
							]
						);

					$this->end_controls_tab();

					$this->start_controls_tab(
						'style_hover_tab',
						[
							'label' => esc_html__( 'Active', 'tripgo' ),
						]
					);

						$this->add_control(
							'date_active_color',
							[
								'label' 	=> esc_html__( 'Color', 'tripgo' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ova-road-map .date.active' => 'color: {{VALUE}}',
								],
							]
						);

						$this->add_control(
							'date_active_background_color',
							[
								'label' 	=> esc_html__( 'Background', 'tripgo' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ova-road-map .date.active' => 'background: {{VALUE}}',
								],
							]
						);

					$this->end_controls_tab();

				$this->end_controls_tabs();

			$this->end_controls_section();

			// Image Style
			$this->start_controls_section(
				'image_style_section',
				[
					'label' => esc_html__( 'Image', 'tripgo' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_responsive_control(
					'image_margin',
					[
						'label' 		=> esc_html__( 'Margin', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-road-map .img' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_responsive_control(
					'image_padding',
					[
						'label' 		=> esc_html__( 'Paddding', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-road-map .img' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_responsive_control(
					'image_size',
					[
						'label' 		=> esc_html__( 'Size', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::SLIDER,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'range' 		=> [
							'px' => [
								'min' 	=> 0,
								'max' 	=> 500,
								'step' 	=> 5,
							],
							'%' => [
								'min' => 0,
								'max' => 100,
							],
						],
						'selectors' => [
							'{{WRAPPER}} .ova-road-map .road-map-wrapper .timeline-column .timeline-item-right .timeline-info .content-info .timeline-content .img' => 'height: {{SIZE}}{{UNIT}};',
							'{{WRAPPER}} .ova-road-map .road-map-wrapper .timeline-column .timeline-item-left .timeline-info .content-info .timeline-content .img' => 'height: {{SIZE}}{{UNIT}};',
						],
					]
				);

			$this->end_controls_section();

			// Title Style
			$this->start_controls_section(
				'title_style_section',
				[
					'label' => esc_html__( 'Title', 'tripgo' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_responsive_control(
					'title_margin',
					[
						'label' 		=> esc_html__( 'Margin', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-road-map .road-map-wrapper .timeline-column .timeline-item-right .timeline-info .content-info .timeline-content .content .title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							'{{WRAPPER}} .ova-road-map .road-map-wrapper .timeline-column .timeline-item-left .timeline-info .content-info .timeline-content .content .title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_responsive_control(
					'title_padding',
					[
						'label' 		=> esc_html__( 'Padding', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-road-map .road-map-wrapper .timeline-column .timeline-item-right .timeline-info .content-info .timeline-content .content .title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							'{{WRAPPER}} .ova-road-map .road-map-wrapper .timeline-column .timeline-item-left .timeline-info .content-info .timeline-content .content .title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'title_typography',
						'selector' 	=> '{{WRAPPER}} .ova-road-map .road-map-wrapper .timeline-column .timeline-info .content-info .timeline-content .content .title',
					]
				);

				$this->add_control(
					'title_color',
					[
						'label' 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-road-map .road-map-wrapper .timeline-column .timeline-item-right .timeline-info .content-info .timeline-content .content .title' => 'color: {{VALUE}}',
							'{{WRAPPER}} .ova-road-map .road-map-wrapper .timeline-column .timeline-item-left .timeline-info .content-info .timeline-content .content .title' => 'color: {{VALUE}}',
						],
					]
				);

			$this->end_controls_section();

			// Description Style
			$this->start_controls_section(
				'desc_style_section',
				[
					'label' => esc_html__( 'Description', 'tripgo' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_responsive_control(
					'desc_margin',
					[
						'label' 		=> esc_html__( 'Margin', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-road-map .road-map-wrapper .timeline-column .timeline-item-left .timeline-info .content-info .timeline-content .content .desc' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							'{{WRAPPER}} .ova-road-map .road-map-wrapper .timeline-column .timeline-item-right .timeline-info .content-info .timeline-content .content .desc' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_responsive_control(
					'desc_padding',
					[
						'label' 		=> esc_html__( 'Padding', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-road-map .road-map-wrapper .timeline-column .timeline-item-right .timeline-info .content-info .timeline-content .content .desc' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							'{{WRAPPER}} .ova-road-map .road-map-wrapper .timeline-column .timeline-item-left .timeline-info .content-info .timeline-content .content .desc' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'desc_typography',
						'selector' 	=> '{{WRAPPER}} .ova-road-map .road-map-wrapper .timeline-column .timeline-info .content-info .timeline-content .content .desc',
					]
				);

				$this->add_control(
					'desc_color',
					[
						'label' 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-road-map .road-map-wrapper .timeline-column .timeline-item-left .timeline-info .content-info .timeline-content .content .desc' => 'color: {{VALUE}}',
							'{{WRAPPER}} .ova-road-map .road-map-wrapper .timeline-column .timeline-item-right .timeline-info .content-info .timeline-content .content .desc' => 'color: {{VALUE}}',
						],
					]
				);

			$this->end_controls_section();
		}

		/**
		 * Render template
		 */
		protected function render() {
			// Get settings
			$settings = $this->get_settings_for_display();

			// Get road map
			$road_map = tripgo_get_meta_data( 'road_map_items', $settings );

			?>
			<div class="ova-road-map">
				<div class="road-map-wrapper">
					<?php if ( tripgo_array_exists( $road_map ) ): ?>
						<div class="timeline-column">
							<?php foreach ( $road_map as $k => $item ):
								// Get item date
								$date = tripgo_get_meta_data( 'date', $item );

								// Date active
								$date_active = 'yes' === tripgo_get_meta_data( 'date_active', $item ) ? 'active' : '';

								// Get item image URL
								$img_url = isset( $item['image']['url'] ) && $item['image']['url'] ? $item['image']['url'] : '';

								// Get item title
								$title = tripgo_get_meta_data( 'title', $item );

								// Get item description
								$desc = tripgo_get_meta_data( 'desc', $item );

								// Animation Content
								$animation_content = tripgo_get_meta_data( 'animation_content', $item );

								// Duration content
								$duration_content = tripgo_get_meta_data( 'animation_duration_content', $item );

								// Delay content
								$delay_content = tripgo_get_meta_data( 'animation_delay_content', $item );

								// Data animation
								$data_animation_content = [
									'animation' => $animation_content,
									'duration' 	=> $duration_content,
									'delay' 	=> $delay_content
								];

								if ( $k % 2 == 0 ): ?>
									<div class="timeline-item-left">
										<div class="timeline-info">
											<div class="date <?php echo esc_attr( $date_active ); ?>">
												<?php echo esc_html( $date ); ?>
											</div>
											<div class="content-info<?php if ( $animation_content ) echo ' ova-invisible'; ?>" data-animation="<?php echo esc_attr( json_encode( $data_animation_content ) ); ?>">
												<div class="triangle-topright"></div>
												<div class="timeline-content">
													<?php if ( $img_url ) : ?>
														<div class="img">
															<img src="<?php echo esc_url( $img_url ); ?>" alt="">
														</div>
													<?php endif; ?>
													<div class="content">
														<?php if ( $title ): ?>
															<h3 class="title">
																<?php echo esc_html( $title ); ?>
															</h3>
														<?php endif;

														// Description
														if ( $desc ) : ?>
															<p class="desc">
																<?php echo esc_html( $desc ); ?>
															</p>
														<?php endif; ?>
													</div>
												</div>
											</div>
										</div>
									</div>
								<?php else: ?>
									<div class="timeline-item-right">
										<div class="timeline-info">
											<div class="date <?php echo esc_attr( $date_active ); ?>">
												<?php echo esc_html( $date ); ?>
											</div>

											<div class="content-info<?php if ( $animation_content ) echo ' ova-invisible'; ?>" data-animation="<?php echo esc_attr( json_encode( $data_animation_content ) ); ?>">
												<div class="triangle-topleft"></div>
												<div class="triangle-topright"></div>
												<div class="timeline-content">
													<div class="content">
														<?php if ( $title ): ?>
															<h3 class="title">
																<?php echo esc_html( $title ); ?>
															</h3>
														<?php endif; ?>
														<?php if ( $desc ): ?>
															<p class="desc">
																<?php echo esc_html( $desc ); ?>
															</p>
														<?php endif; ?>
													</div>
													<?php if ( $img_url ) : ?>
														<div class="img">
															<img src="<?php echo esc_url( $img_url ); ?>" alt="">
														</div>
													<?php endif; ?>
												</div>
											</div>
										</div>
									</div>
								<?php endif;
							endforeach; ?>
							<div class="icon">
								<i class="fas fa-chevron-down"></i>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</div>
			<?php
		}	
	}

	// init widget
	$widgets_manager->register( new Tripgo_Elementor_Road_Map() );
}