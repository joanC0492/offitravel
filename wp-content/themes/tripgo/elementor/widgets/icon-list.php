<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class Tripgo_Elementor_Icon_List
 */
if ( !class_exists( 'Tripgo_Elementor_Icon_List', false ) ) {

	class Tripgo_Elementor_Icon_List extends \Elementor\Widget_Base {

		/**
		 * Get widget name
		 */
		public function get_name() {
			return 'tripgo_elementor_icon_list';
		}

		/**
		 * Get widget title
		 */
		public function get_title() {
			return esc_html__( 'Ova Icon List', 'tripgo' );
		}

		/**
		 * Get widget icon
		 */
		public function get_icon() {
			return 'eicon-bullet-list';
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
			return [ 'tripgo-elementor-icon-list' ];
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
				
				// Add Class control
				$repeater = new \Elementor\Repeater();

				$repeater->add_control(
					'icon',
					[
						'label' 	=> esc_html__( 'Icon', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::ICONS,
						'default' 	=> [
							'value' 	=> 'fas fa-check',
							'library' 	=> 'all',
						],
					]
				);

				$repeater->add_control(
					'title',
					[
						'label' 	=> esc_html__( 'Title', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::TEXT,
						'default' 	=> esc_html__( '723+ Destinations', 'tripgo' ),
					]
				);

	            $repeater->add_control(
					'desc',
					[
						'label' 	=> esc_html__( 'Description', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::TEXTAREA,
						'default' 	=> esc_html__('Available, but the majority have suffered simply', 'tripgo' ),
					]
				);

				$repeater->add_responsive_control(
					'item_margin',
					[
						'label' 		=> esc_html__( 'Margin', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-icon-list {{CURRENT_ITEM}}' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_control(
					'ico_items',
					[
						'label' 	=> esc_html__( 'Items', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::REPEATER,
						'fields' 	=> $repeater->get_controls(),
						'default' 	=> [
							[
								'title' => esc_html__( '723+ Destinations', 'tripgo' ),
							],
							[
								'title' => esc_html__( 'Best Price Gurantee', 'tripgo' ),
							],
							[
								'title' => esc_html__( 'Top Notch Support', 'tripgo' ),
							],
						
						],
						'title_field' => '{{{ title }}}',
					]
				);

			$this->end_controls_section();

			/* Begin Item Style */
			$this->start_controls_section(
	            'item_style',
	            [
	               'label' 	=> esc_html__( 'Item', 'tripgo' ),
	               'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
	            ]
	        );

				$this->add_control(
					'item_bgcolor',
					[
						'label' 	=> esc_html__( 'Background', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-icon-list .item' => 'background: {{VALUE}};',
						],
					]
				);

			    $this->add_responsive_control(
		            'item_padding',
		            [
		                'label' 		=> esc_html__( 'Padding', 'tripgo' ),
		                'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
		                'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
		                'selectors' 	=> [
		                    '{{WRAPPER}} .ova-icon-list .item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		                ],
		            ]
		        );

				$this->add_responsive_control(
					'item_border_radius',
					[
						'label'      => esc_html__( 'Border Radius', 'tripgo' ),
						'type'       => \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors'  => [
							'{{WRAPPER}} .ova-icon-list .item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Box_Shadow::get_type(),
					[
						'name' 		=> 'item_box_shadow',
						'label' 	=> esc_html__( 'Box Shadow', 'tripgo' ),
						'selector' 	=> '{{WRAPPER}} .ova-icon-list .item',
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Border::get_type(),
					[
						'name' 		=> 'item_border',
						'label' 	=> esc_html__( 'Border', 'tripgo' ),
						'selector' 	=> '{{WRAPPER}} .ova-icon-list .item',
					]
				);

	        $this->end_controls_section(); /* End Item style */

			$this->start_controls_section(
				'section_icon_style',
				[
					'label' => esc_html__( 'Icon', 'tripgo' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_responsive_control(
					'icon_size',
					[
						'label' 		=> esc_html__( 'Size', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::SLIDER,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'range' 		=> [
							'px' => [
								'min' 	=> 0,
								'max' 	=> 200,
								'step' 	=> 1,
							],
						],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-icon-list .item i' => 'font-size: {{SIZE}}{{UNIT}};',
							'{{WRAPPER}} .ova-icon-list .item svg' => 'width: {{SIZE}}{{UNIT}};min-width: {{SIZE}}{{UNIT}};',
						],
					]
				);

				$this->add_control(
					'color_icon',
					[
						'label' 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-icon-list .item i' => 'color : {{VALUE}};',
							'{{WRAPPER}} .ova-icon-list .item svg' => 'fill : {{VALUE}};',
							'{{WRAPPER}} .ova-icon-list .item svg path' => 'fill : {{VALUE}};',
						],
					]
				);

			$this->end_controls_section();

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
						'name' 		=> 'title_typography',
						'selector' 	=> '{{WRAPPER}} .ova-icon-list .item .title',
					]
				);

				$this->add_control(
					'color_title',
					[
						'label' 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-icon-list .item .title' => 'color : {{VALUE}};',
						],
					]
				);

				$this->add_responsive_control(
					'padding_title',
					[
						'label' 		=> esc_html__( 'Padding', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-icon-list .title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_responsive_control(
					'margin_title',
					[
						'label' 		=> esc_html__( 'Margin', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-icon-list .title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

	        $this->end_controls_section();

			$this->start_controls_section(
				'section_desc_style',
				[
					'label' => esc_html__( 'Description', 'tripgo' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'desc_typography',
						'selector' 	=> '{{WRAPPER}} .ova-icon-list .item .desc',
					]
				);

				$this->add_control(
					'color_desc',
					[
						'label' 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-icon-list .item .desc' => 'color : {{VALUE}};',
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

			// Get items
	        $items = tripgo_get_meta_data( 'ico_items', $settings );

			?>
			<div class="ova-icon-list">
				<?php if ( tripgo_array_exists( $items ) ):
					foreach ( $items as $item ):
						// Get item id
						$item_id = 'elementor-repeater-item-' . tripgo_get_meta_data( '_id', $item );

						// Get item icon
						$item_icon = tripgo_get_meta_data( 'icon', $item );

						// Get item title
						$item_title = tripgo_get_meta_data( 'title', $item );

						// Get item description
						$item_desc = tripgo_get_meta_data( 'desc', $settings );
					?>
						<div class="item <?php echo esc_attr( $item_id ); ?>">
							<?php if ( !empty( $item_icon['value'] ) ): ?>
								<?php \Elementor\Icons_Manager::render_icon( $item_icon, [ 'aria-hidden' => 'true' ] ); ?>
							<?php endif; ?>
							<div class="info">
								<h3 class="title">
									<?php echo esc_html( $item_title ); ?>
								</h3>
								<?php if ( $item_desc ): ?>
									<p class="desc">
										<?php echo esc_html( $item_desc ); ?>
									</p>
								<?php endif; ?>
							</div>
						</div>
					<?php endforeach; 
				endif; ?>
			</div>
			<?php
		}
	}

	// init widget
	$widgets_manager->register( new Tripgo_Elementor_Icon_List() );
}