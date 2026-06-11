<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class Tripgo_Elementor_Counter
 */
if ( !class_exists( 'Tripgo_Elementor_Counter', false ) ) {

	class Tripgo_Elementor_Counter extends \Elementor\Widget_Base {

		/**
		 * Get widget name
		 */
		public function get_name() {
			return 'tripgo_elementor_counter';
		}

		/**
		 * Get widget title
		 */
		public function get_title() {
			return esc_html__( 'Ova Counter', 'tripgo' );
		}

		/**
		 * Get widget icon
		 */
		public function get_icon() {
			return 'eicon-counter';
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
			return [ 'ova-odometer', 'tripgo-elementor-counter' ];
		}

		/**
		 * Get script depends
		 */
		public function get_script_depends() {
			return [ 'ova-appear', 'ova-odometer', 'tripgo-elementor-counter' ];
		}
		
		/**
		 * Register controls
		 */
		protected function register_controls() {

			$this->start_controls_section(
				'section_content',
				[
					'label' => esc_html__( 'Ova Counter', 'tripgo' ),
				]
			);	
				
				// Add Class control
			    $this->add_control(
					'icon',
					[
						'label' 	=> esc_html__( 'Icon', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::ICONS,
						'default' 	=> [
							'value' 	=> 'icomoon icomoon-profile-2user',
							'library' 	=> 'all',
						],
					]
				);

			    $this->add_control(
					'number',
					[
						'label' 	=> esc_html__( 'Number', 'tripgo' ),
						'type'    	=> \Elementor\Controls_Manager::NUMBER,
						'default' 	=> esc_html__( '28', 'tripgo' ),
					]
				);

				$this->add_control(
					'suffix',
					[
						'label'  	=> esc_html__( 'Suffix', 'tripgo' ),
						'type'   	=> \Elementor\Controls_Manager::TEXT,
						'default' 	=> 'k',
					]
				);

				$this->add_control(
					'title',
					[
						'label' 	=> esc_html__( 'Title', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::TEXT,
						'default' 	=> esc_html__( 'Total Users', 'tripgo' ),
					]
				);

				$this->add_responsive_control(
					'text_align',
					[
						'label' 	=> esc_html__( 'Alignment', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::CHOOSE,
						'options' 	=> [
							'left' 	=> [
								'title' => esc_html__( 'Left', 'tripgo' ),
								'icon' 	=> 'eicon-text-align-left',
							],
							'center' => [
								'title' => esc_html__( 'Center', 'tripgo' ),
								'icon' 	=> 'eicon-text-align-center',
							],
							'right' => [
								'title' => esc_html__( 'Right', 'tripgo' ),
								'icon' 	=> 'eicon-text-align-right',
							],
						],
						'selectors' => [
							'{{WRAPPER}} .ova-counter' => 'text-align: {{VALUE}};',
						],
					]
				);
				
			$this->end_controls_section();

			/* Begin Counter Style */
			$this->start_controls_section(
	            'counter_style',
	            [
	               'label' 	=> esc_html__( 'Ova Counter', 'tripgo' ),
	               'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
	            ]
	        );

				$this->add_control(
					'counter_bgcolor',
					[
						'label' 	=> esc_html__( 'Background', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-counter' => 'background: {{VALUE}};',
						],
					]
				);

				$this->add_control(
					'counter_bgcolor_hover',
					[
						'label' 	=> esc_html__( 'Background Hover', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-counter:hover' => 'background: {{VALUE}};',
						],
					]
				);

			    $this->add_responsive_control(
		            'counter_padding',
		            [
		                'label' 		=> esc_html__( 'Padding', 'tripgo' ),
		                'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
		                'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
		                'selectors' 	=> [
		                    '{{WRAPPER}} .ova-counter' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		                ],
		            ]
		        );

		        $this->add_group_control(
					\Elementor\Group_Control_Box_Shadow::get_type(),
					[
						'name' 		=> 'box_shadow',
						'label' 	=> esc_html__( 'Box Shadow', 'tripgo' ),
						'selector' 	=> '{{WRAPPER}} .ova-counter',
					]
				);

				$this->add_responsive_control(
					'counter_border_radius',
					[
						'label'      => esc_html__( 'Border Radius', 'tripgo' ),
						'type'       => \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors'  => [
							'{{WRAPPER}} .ova-counter' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

	        $this->end_controls_section(); /* End counter style */
	        
	        /* Begin icon Style */
			$this->start_controls_section(
				'section_icon_style',
				[
					'label' => esc_html__( 'Icon', 'tripgo' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_control(
					'icon_fontsize',
					[
						'label' 		=> esc_html__( 'Size', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::SLIDER,
						'size_units' 	=> [ 'px' ],
						'range' 		=> [
							'px' => [
								'min' 	=> 0,
								'max' 	=> 90,
								'step' 	=> 1,
							]
						],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-counter .icon' => 'font-size: {{SIZE}}{{UNIT}};',
						],
					]
				);

				$this->add_control(
					'icon_color',
					[
						'label' 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-counter .icon' => 'color : {{VALUE}};',
						],
					]
				);

				$this->add_control(
					'icon_color_hover',
					[
						'label' 	=> esc_html__( 'Color Hover', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-counter:hover .icon' => 'color : {{VALUE}};',
						],
					]
				);

				$this->add_control(
					'icon_bgcolor',
					[
						'label' 	=> esc_html__( 'Background Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-counter .icon' => 'background-color : {{VALUE}};',
						],
					]
				);

				$this->add_control(
					'icon_bgcolor_hover',
					[
						'label' 	=> esc_html__( 'Background Color Hover', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-counter:hover .icon' => 'background-color : {{VALUE}};',
						],
					]
				);

				$this->add_responsive_control(
					'icon_margin',
					[
						'label' 		=> esc_html__( 'Margin', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-counter .icon' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

			$this->end_controls_section(); // End Style tab Icon

			/* Begin number (odometer) Style */
			$this->start_controls_section(
	            'number_style',
	            [
	                'label' => esc_html__( 'Number', 'tripgo' ),
	                'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
	            ]
	        );

				 $this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'number_typography',
						'selector' 	=> '{{WRAPPER}} .ova-counter .odometer',
					]
				);

				$this->add_control(
					'number_color',
					[
						'label' 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-counter .odometer' => 'color: {{VALUE}};',
						],
					]
				);

				$this->add_control(
					'number_color_hover',
					[
						'label' 	=> esc_html__( 'Color Hover', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-counter:hover .odometer' => 'color: {{VALUE}};',
						],
					]
				);

	        $this->end_controls_section(); /* End number style */

			/* Begin suffix Style */
			$this->start_controls_section(
	            'suffix_style',
	            [
	                'label' => esc_html__( 'Suffix', 'tripgo' ),
	                'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
	            ]
	        );

	            $this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'suffix_typography',
						'selector' 	=> '{{WRAPPER}} .ova-counter .suffix',
					]
				);

				$this->add_control(
					'suffix_color',
					[
						'label' 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-counter .suffix' => 'color: {{VALUE}};',
						],
					]
				);

				$this->add_control(
					'suffix_color_hover',
					[
						'label' 	=> esc_html__( 'Color Hover', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-counter:hover .suffix' => 'color: {{VALUE}};',
						],
					]
				);

				$this->add_responsive_control(
		            'suffix_padding',
		            [
		                'label' 		=> esc_html__( 'Padding', 'tripgo' ),
		                'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
		                'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
		                'selectors' 	=> [
		                    '{{WRAPPER}} .ova-counter .suffix' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		                ],
		            ]
		        );

	        $this->end_controls_section(); /* End suffix style */

			/* Begin title Style */
			$this->start_controls_section(
	            'title_style',
	            [
	                'label' => esc_html__( 'Title', 'tripgo' ),
	                'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
	            ]
	        );

	            $this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'title_typography',
						'selector' 	=> '{{WRAPPER}} .ova-counter .title',
					]
				);

				$this->add_control(
					'title_color',
					[
						'label' 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-counter .title' => 'color: {{VALUE}};',
						],
					]
				);

				$this->add_control(
					'title_color_hover',
					[
						'label' 	=> esc_html__( 'Color Hover', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-counter:hover .title' => 'color: {{VALUE}};',
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
		                    '{{WRAPPER}} .ova-counter .title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		                ],
		            ]
		        );

	        $this->end_controls_section(); /* End title style */
		}

		/**
		 * Render HTML
		 */
		protected function render() {
			// Get settings
			$settings = $this->get_settings_for_display();

			// Get class icon
	        $class_icon = isset( $settings['icon']['value'] ) ? $settings['icon']['value'] : '';

	        // Get number
			$number = tripgo_get_meta_data( 'number', $settings, '100' );

			// Get suffix
			$suffix = tripgo_get_meta_data( 'suffix', $settings );

			// Get title
			$title = tripgo_get_meta_data( 'title', $settings );

			?>
	        <div class="ova-counter" data-count="<?php echo esc_attr( $number ); ?>">
	            <?php if ( $class_icon ): ?>
	            	<div class="icon-wrapper">
	            		<div class="icon">
							<i class="<?php echo esc_attr( $class_icon ); ?>"></i>
						</div>
	            	</div>
				<?php endif; ?>
                <div class="odometer-wrapper">
					<span class="odometer">0</span>
					<span class="suffix">
						<?php echo esc_html( $suffix ); ?>
			        </span>
			    </div>
	      	    <?php if ( $title ): ?>
					<h4 class="title">
						<?php echo esc_html( $title ); ?>
					</h4>
				<?php endif;?>
	        </div>
			<?php
		}
	}

	// init widget
	$widgets_manager->register( new Tripgo_Elementor_Counter() );
}