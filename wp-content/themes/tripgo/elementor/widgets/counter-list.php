<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class Tripgo_Elementor_Counter_List
 */
if ( !class_exists( 'Tripgo_Elementor_Counter_List', false ) ) {

	class Tripgo_Elementor_Counter_List extends \Elementor\Widget_Base {

		/**
		 * Get widget name
		 */
		public function get_name() {
			return 'tripgo_elementor_counter_list';
		}

		/**
		 * Get widget title
		 */
		public function get_title() {
			return esc_html__( 'Counter List', 'tripgo' );
		}

		/**
		 * Get widget icon
		 */
		public function get_icon() {
			return 'eicon-counter-circle';
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
			return [ 'ova-odometer', 'tripgo-elementor-counter-list' ];
		}

		/**
		 * Get script depends
		 */
		public function get_script_depends() {
			return [ 'ova-appear', 'ova-odometer', 'tripgo-elementor-counter-list' ];
		}
		
		/**
		 * Register controls
		 */
		protected function register_controls() {
			// Content
			$this->start_controls_section(
				'section_content',
				[
					'label' => esc_html__( 'Content', 'tripgo' )
				]
			);	

			    $this->add_control(
					'number_column',
					[
						'label' 	=> esc_html__( 'Number Column', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::SELECT,
						'default' 	=> 'four_column',
						'options' 	=> [
							'one_column' 	=> esc_html__( 'Single Column', 'tripgo' ),
							'two_column' 	=> esc_html__( '2 Columns', 'tripgo' ),
							'three_column' 	=> esc_html__( '3 Columns', 'tripgo' ),
							'four_column' 	=> esc_html__( '4 Columns', 'tripgo' )
						]
					]
				);

				$this->add_control(
					'show_offsets_between_columns',
					[
						'label' 		=> esc_html__( 'Show Offsets Between Columns', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 		=> esc_html__( 'Yes', 'tripgo' ),
						'label_off' 	=> esc_html__( 'No', 'tripgo' ),
						'return_value' 	=> 'yes',
						'default' 		=> 'no'
					]
				);

			    $repeater = new \Elementor\Repeater();

		    	$repeater->add_control(
					'icon',
					[
						'label' 	=> esc_html__( 'Icon', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::ICONS,
						'default' 	=> [
							'value' 	=> 'icomoon icomoon-profile-2user',
							'library' 	=> 'all'
						]
					]
				);

			    $repeater->add_control(
					'number',
					[
						'label' 	=> esc_html__( 'Number', 'tripgo' ),
						'type'    	=> \Elementor\Controls_Manager::NUMBER,
						'default' 	=> 28
					]
				);

				$repeater->add_control(
					'suffix',
					[
						'label'  	=> esc_html__( 'Suffix', 'tripgo' ),
						'type'   	=> \Elementor\Controls_Manager::TEXT,
						'default' 	=> 'k'
					]
				);

				$repeater->add_control(
					'title',
					[
						'label' 	=> esc_html__( 'Title', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::TEXTAREA,
						'default' 	=> esc_html__( 'Total active pro users', 'tripgo' )
					]
				);
				
				$this->add_control(
					'items',
					[
						'label' 	=> esc_html__( 'Items', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::REPEATER,
						'fields' 	=> $repeater->get_controls(),
						'default' 	=> [
							[	
								'title'   	=> esc_html__( 'Total active pro users', 'tripgo' ),
								'number'  	=> 28
							],
							[	
								'title'  	=> esc_html__( 'Total available tours', 'tripgo' ),
								'number'  	=> 13
							],
							[	
								'title'  	=> esc_html__( 'Social follow likes', 'tripgo' ),
								'number'  	=> 68
							],
							[	
								'title'  	=> esc_html__( '5 star clients ratings', 'tripgo' ),
								'number'  	=> 10
							]
						],
						'title_field' => '{{{ title }}}'
					]
				);

				$this->add_responsive_control(
					'align_heading',
					[
						'label' 	=> esc_html__( 'Alignment', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::CHOOSE,
						'options' 	=> [
							'left' 	=> [
								'title' => esc_html__( 'Left', 'tripgo' ),
								'icon' 	=> 'eicon-text-align-left'
							],
							'center' => [
								'title' => esc_html__( 'Center', 'tripgo' ),
								'icon' 	=> 'eicon-text-align-center'
							],
							'right' => [
								'title' => esc_html__( 'Right', 'tripgo' ),
								'icon' 	=> 'eicon-text-align-right'
							]
						],
						'toggle' 	=> true,
						'selectors' => [
							'{{WRAPPER}} .ova-counter-list' => 'text-align: {{VALUE}}'
						]
					]
				);
				
			$this->end_controls_section();

			/* Begin Counter Style */
			$this->start_controls_section(
	            'counter_style',
	            [
	               'label' 	=> esc_html__( 'Counter', 'tripgo' ),
	               'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE
	            ]
	        );

				$this->add_control(
					'counter_bgcolor',
					[
						'label' 	=> esc_html__( 'Background', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-counter-list' => 'background: {{VALUE}};'
						]
					]
				);

				$this->add_control(
					'counter_bgcolor_hover',
					[
						'label' 	=> esc_html__( 'Background Hover', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-counter-list:hover' => 'background: {{VALUE}};'
						]
					]
				);

			    $this->add_responsive_control(
		            'counter_padding',
		            [
		                'label' 		=> esc_html__( 'Padding', 'tripgo' ),
		                'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
		                'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
		                'selectors' 	=> [
		                    '{{WRAPPER}} .ova-counter-list' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
		                ]
		            ]
		        );

		        $this->add_group_control(
					\Elementor\Group_Control_Box_Shadow::get_type(),
					[
						'name' 		=> 'box_shadow',
						'label' 	=> esc_html__( 'Box Shadow', 'tripgo' ),
						'selector' 	=> '{{WRAPPER}} .ova-counter-list'
					]
				);

				$this->add_responsive_control(
					'counter_border_radius',
					[
						'label'      => esc_html__( 'Border Radius', 'tripgo' ),
						'type'       => \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors'  => [
							'{{WRAPPER}} .ova-counter-list' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
						]
					]
				);

	        $this->end_controls_section(); /* End counter style */
	        
	        /* Begin icon Style */
			$this->start_controls_section(
				'section_icon_style',
				[
					'label' => esc_html__( 'Icon', 'tripgo' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE
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
								'step' 	=> 1
							]
						],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-counter-list .icon' => 'font-size: {{SIZE}}{{UNIT}};'
						]
					]
				);

				$this->add_control(
					'icon_color',
					[
						'label' 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-counter-list .icon' => 'color : {{VALUE}};'
						]
					]
				);

				$this->add_control(
					'icon_color_hover',
					[
						'label' 	=> esc_html__( 'Color Hover', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-counter-list:hover .icon' => 'color : {{VALUE}};'
						]
					]
				);

				$this->add_control(
					'icon_bgcolor',
					[
						'label' 	=> esc_html__( 'Background Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-counter-list .icon' => 'background-color : {{VALUE}};'
						]
					]
				);

				$this->add_control(
					'icon_bgcolor_hover',
					[
						'label' 	=> esc_html__( 'Background Color Hover', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-counter-list:hover .icon' => 'background-color : {{VALUE}};'
						]
					]
				);

				$this->add_responsive_control(
					'icon_margin',
					[
						'label' 		=> esc_html__( 'Margin', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-counter-list .icon' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
						]
					]
				);

			$this->end_controls_section(); // End Style tab Icon

			/* Begin number (odometer) Style */
			$this->start_controls_section(
	            'number_style',
	            [
	                'label' => esc_html__( 'Number', 'tripgo' ),
	                'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE
	            ]
	        );

				 $this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'number_typography',
						'selector' 	=> '{{WRAPPER}} .ova-counter-list .odometer'
					]
				);

				$this->add_control(
					'number_color',
					[
						'label' 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-counter-list .odometer' => 'color: {{VALUE}};'
						]
					]
				);

				$this->add_control(
					'number_color_hover',
					[
						'label' 	=> esc_html__( 'Color Hover', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-counter-list:hover .odometer' => 'color: {{VALUE}};'
						]
					]
				);

	        $this->end_controls_section(); /* End number style */

			/* Begin suffix Style */
			$this->start_controls_section(
	            'suffix_style',
	            [
	                'label' => esc_html__( 'Suffix', 'tripgo' ),
	                'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE
	            ]
	        );

	            $this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'suffix_typography',
						'selector' 	=> '{{WRAPPER}} .ova-counter-list .suffix'
					]
				);

				$this->add_control(
					'suffix_color',
					[
						'label' 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-counter-list .suffix' => 'color: {{VALUE}};'
						]
					]
				);

				$this->add_control(
					'suffix_color_hover',
					[
						'label' 	=> esc_html__( 'Color Hover', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-counter-list:hover .suffix' => 'color: {{VALUE}};'
						]
					]
				);

				$this->add_responsive_control(
		            'suffix_padding',
		            [
		                'label' 		=> esc_html__( 'Padding', 'tripgo' ),
		                'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
		                'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
		                'selectors' 	=> [
		                    '{{WRAPPER}} .ova-counter-list .suffix' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
		                ]
		            ]
		        );

	        $this->end_controls_section(); /* End suffix style */

			/* Begin title Style */
			$this->start_controls_section(
	            'title_style',
	            [
	                'label' => esc_html__( 'Title', 'tripgo' ),
	                'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE
	            ]
	        );

	            $this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'title_typography',
						'selector' 	=> '{{WRAPPER}} .ova-counter-list .title'
					]
				);

				$this->add_control(
					'title_color',
					[
						'label' 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-counter-list .title' => 'color: {{VALUE}};',
						],
					]
				);

				$this->add_control(
					'title_color_hover',
					[
						'label' 	=> esc_html__( 'Color Hover', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-counter-list:hover .title' => 'color: {{VALUE}};',
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
		                    '{{WRAPPER}} .ova-counter-list .title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
	        
	        // Get items
	        $items = tripgo_get_meta_data( 'items', $settings );

	        // Columns
	        $columns = tripgo_get_meta_data( 'number_column', $settings );

	        // Class offsets
	        $class_offsets = '';
	        if ( 'yes' === tripgo_get_meta_data( 'show_offsets_between_columns', $settings ) ) {
	        	$class_offsets = 'columns-offsets';
	        }

	        if ( tripgo_array_exists( $items ) ): ?>
	            <div class="ova-counter-list-wrapper <?php echo esc_attr( $columns ); ?>">
	            	<?php foreach ( $items as $item ):
	            		// Class icon
		                $class_icon = isset( $item['icon']['value'] ) ? $item['icon']['value'] : '';

		                // Get number
						$number = tripgo_get_meta_data( 'number', $item, '100' );

						// Get suffix
						$suffix = tripgo_get_meta_data( 'suffix', $item );

						// Get title
						$title = tripgo_get_meta_data( 'title', $item );
				    ?>
			           <div class="ova-counter-list <?php echo esc_attr( $class_offsets ); ?>" data-count="<?php echo esc_attr( $number ); ?>">
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
		           <?php endforeach; ?>
	            </div>
			<?php endif;
		}
	}

	// init widget
	$widgets_manager->register( new Tripgo_Elementor_Counter_List() );
}