<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class Tripgo_Elementor_Progress
 */
if ( !class_exists( 'Tripgo_Elementor_Progress', false ) ) {

	class Tripgo_Elementor_Progress extends \Elementor\Widget_Base {

		/**
		 * Get widget name
		 */
		public function get_name() {
			return 'tripgo_elementor_progress';
		}

		/**
		 * Get widget title
		 */
		public function get_title() {
			return esc_html__( 'Progress', 'tripgo' );
		}

		/**
		 * Get widget icon
		 */
		public function get_icon() {
			return 'eicon-skill-bar';
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
			return [ 'tripgo-elementor-progress' ];
		}

		/**
		 * Get script depends
		 */
		public function get_script_depends() {
			return [ 'ova-appear', 'tripgo-elementor-progress' ];
		}
		
		/**
		 * Register controls
		 */
		protected function register_controls() {
			// Begin progress
			$this->start_controls_section(
				'section_progress',
				[
					'label' => esc_html__( 'Ova Progress', 'tripgo' ),
				]
			);

				$this->add_control(
					'title',
					[
						'label' 		=> esc_html__( 'Title', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::TEXT,
						'placeholder' 	=> esc_html__( 'Enter your title', 'tripgo' ),
						'default' 		=> esc_html__( 'My Skill', 'tripgo' ),
						'label_block' 	=> true,
					]
				);

				$this->add_control(
		            'show_title',
		            [
		                'label' 	=> esc_html__( 'Show Title', 'tripgo' ),
		                'type' 		=> \Elementor\Controls_Manager::SWITCHER,
		                'default' 	=> 'yes',
		                'separator' => 'before',
		            ]
		        );

		        $this->add_control(
					'html_tag',
					[
						'label' 	=> esc_html__( 'HTML Tag', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::SELECT,
						'default' 	=> 'h6',
						'options' 	=> [
							'h1' 		=> esc_html__( 'H1', 'tripgo' ),
							'h2'  		=> esc_html__( 'H2', 'tripgo' ),
							'h3'  		=> esc_html__( 'H3', 'tripgo' ),
							'h4' 		=> esc_html__( 'H4', 'tripgo' ),
							'h5' 		=> esc_html__( 'H5', 'tripgo' ),
							'h6' 		=> esc_html__( 'H6', 'tripgo' ),
							'div' 		=> esc_html__( 'Div', 'tripgo' ),
							'span' 		=> esc_html__( 'span', 'tripgo' ),
							'p' 		=> esc_html__( 'p', 'tripgo' )
						],
						'condition' => [
							'show_title!' => '',
						]
					]
				);
				
				$this->add_control(
					'percent',
					[
						'label' 	=> esc_html__( 'Percent', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::NUMBER,
						'min' 		=> 0,
						'max' 		=> 100,
						'step' 		=> 5,
						'default' 	=> 60,
					]
				);

				$this->add_control(
		            'show_percent',
		            [
		                'label' 	=> esc_html__( 'Show Percent', 'tripgo' ),
		                'type' 		=> \Elementor\Controls_Manager::SWITCHER,
		                'default' 	=> 'yes',
		                'separator' => 'before',
		            ]
		        );

		        $this->add_control(
		            'show_notes',
		            [
		                'label' 	=> esc_html__( 'Show Notes', 'tripgo' ),
		                'type' 		=> \Elementor\Controls_Manager::SWITCHER,
		                'default' 	=> 'no',
		                'separator' => 'before',
		            ]
		        );

				$repeater = new \Elementor\Repeater();

		        $repeater->add_control(
		            'item_text',
		            [
		                'label' => esc_html__( 'Text', 'tripgo' ),
		                'type' 	=> \Elementor\Controls_Manager::TEXT,
		            ]
		        );

		        $repeater->add_control(
		            'item_color',
		            [
		                'label' 	=> esc_html__( 'Color', 'tripgo' ),
		                'type' 		=> \Elementor\Controls_Manager::COLOR,
		                'selectors' => [
		                    '{{WRAPPER}} {{CURRENT_ITEM}} .note-text' => 'color: {{VALUE}} !important',
		                ],
		            ]
		        );

		        $repeater->add_control(
		            'item_left',
		            [
		                'label' => esc_html__( 'Left', 'tripgo' ),
		                'type' 	=> \Elementor\Controls_Manager::NUMBER,
		                'min' 	=> 5,
						'max' 	=> 100,
						'step' 	=> 5,
		            ]
		        );

				$this->add_control(
		            'notes',
		            [
		                'type' 		=> \Elementor\Controls_Manager::REPEATER,
		                'fields' 	=> $repeater->get_controls(),
		                'default' 	=> [
		                    [
		                        'item_text' => esc_html__( 'Pre Sale', 'tripgo' ),
		                    ],
		                    [
		                        'item_text' => esc_html__( 'Soft Cap', 'tripgo' ),
		                    ],
		                    [
		                        'item_text' => esc_html__( 'Bonus', 'tripgo' ),
		                    ],
		                ],
		                'title_field' => '{{{ item_text }}}',
		                'condition'	  => [
		                	'show_notes' => 'yes',
		                ],
		            ]
		        );

			$this->end_controls_section(); // End progress

			// Begin progress style
			$this->start_controls_section(
				'section_progress_style',
				[
					'label' => esc_html__( 'Progress Bar', 'tripgo' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_control(
					'progress_bg',
					[
						'label' 	=> esc_html__( 'Background', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-progress .ova-percent-view' => 'background-color: {{VALUE}};',
						],
					]
				);

				$this->add_control(
					'progress_height',
					[
						'label' 	=> esc_html__( 'Height', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::SLIDER,
						'selectors' => [
							'{{WRAPPER}} .ova-progress .ova-percent-view' => 'height: {{SIZE}}{{UNIT}}',
						],
					]
				);

				$this->add_control(
		            'title_alignment',
		            [
		                'label' 	=> esc_html__( 'Alignment List', 'tripgo' ),
		                'type' 		=> \Elementor\Controls_Manager::CHOOSE,
		                'options' 	=> [
		                    'left' 	=> [
		                        'title' => esc_html__( 'Left', 'tripgo' ),
		                        'icon' 	=> 'eicon-text-align-left',
		                    ],
		                    'center' 	=> [
		                        'title' => esc_html__( 'Center', 'tripgo' ),
		                        'icon' 	=> 'eicon-text-align-center',
		                    ],
		                    'right' 	=> [
		                        'title' => esc_html__( 'Right', 'tripgo' ),
		                        'icon' 	=> 'eicon-text-align-right',
		                    ],
		                ],
		                'selectors' => [
		                    '{{WRAPPER}} .ova-progress' => 'text-align: {{VALUE}}',
		                ],
		            ]
		        );

		        $this->add_group_control(
		            \Elementor\Group_Control_Border::get_type(), [
		                'name' 		=> 'progress_border',
		                'selector' 	=> '{{WRAPPER}} .ova-progress .ova-percent-view',
		                'separator' => 'before',
		            ]
		        );

		        $this->add_control(
		            'progress_border_radius',
		            [
		                'label' 		=> esc_html__( 'Border Radius', 'tripgo' ),
		                'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
		                'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
		                'selectors' 	=> [
		                    '{{WRAPPER}} .ova-progress .ova-percent-view' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		                    '{{WRAPPER}} .ova-progress .ova-percent-view .ova-percent' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		                ],
		            ]
		        );

			$this->end_controls_section(); // End progress style

			// Begin percent style
			$this->start_controls_section(
				'section_percent_style',
				[
					'label' => esc_html__( 'Percent', 'tripgo' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_control(
					'percent_bg',
					[
						'label' 	=> esc_html__( 'Background', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-progress .ova-percent-view .ova-percent' => 'background: {{VALUE}};',
						],
					]
				);

				$this->add_control(
					'percent_linear_gradient',
					[
						'label' => esc_html__( 'Linear Gradient', 'tripgo' ),
						'type' 	=> \Elementor\Controls_Manager::COLOR,
					]
				);

			$this->end_controls_section(); // End percent style

			// Begin title style
			$this->start_controls_section(
				'section_title_style',
				[
					'label' => esc_html__( 'Title', 'tripgo' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_control(
					'title_color',
					[
						'label' 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-progress .ova-progress-title' => 'color: {{VALUE}};',
						],
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'title_typography',
						'selector' 	=> '{{WRAPPER}} .ova-progress .ova-progress-title',
					]
				);

				$this->add_control(
		            'title_padding',
		            [
		                'label' 		=> esc_html__( 'Padding', 'tripgo' ),
		                'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
		                'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
		                'selectors' 	=> [
		                    '{{WRAPPER}} .ova-progress .ova-progress-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		                ],
		            ]
		        );

		        $this->add_control(
		            'title_margin',
		            [
		                'label' 		=> esc_html__( 'Margin', 'tripgo' ),
		                'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
		                'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
		                'selectors' 	=> [
		                    '{{WRAPPER}} .ova-progress .ova-progress-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		                ],
		            ]
		        );

			$this->end_controls_section(); // End title style

			// Begin percentage style
			$this->start_controls_section(
				'section_percentage_style',
				[
					'label' => esc_html__( 'Percentage', 'tripgo' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_control(
					'percentage_color',
					[
						'label' 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-progress .ova-percent-view .percentage' => 'color: {{VALUE}};',
						],
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'percentage_typography',
						'selector' 	=> '{{WRAPPER}} .ova-progress .ova-percent-view .percentage',
					]
				);

				$this->add_control(
					'percentage_top',
					[
						'label' 		=> esc_html__( 'Top', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::SLIDER,
						'size_units' 	=> [ 'px', '%' ],
						'range' 		=> [
							'px' => [
								'min' 	=> -100,
								'max' 	=> 100,
								'step' 	=> 5,
							],
							'%' => [
								'min' 	=> -100,
								'max' 	=> 100,
							],
						],
						'default' 	=> [
							'unit' 	=> 'px',
							'size' 	=> -40,
						],
						'selectors' => [
							'{{WRAPPER}} .ova-progress .ova-percent-view .percentage' => 'top: {{SIZE}}{{UNIT}}',
						],
					]
				);

			$this->end_controls_section(); // End percentage style

			// Begin notes style
			$this->start_controls_section(
				'section_note_style',
				[
					'label' => esc_html__( 'Notes', 'tripgo' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'note_typography',
						'selector' 	=> '{{WRAPPER}} .ova-progress .ova-notes .note-text',
					]
				);

				$this->add_control(
		            'note_padding',
		            [
		                'label' 		=> esc_html__( 'Padding', 'tripgo' ),
		                'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
		                'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
		                'selectors' 	=> [
		                    '{{WRAPPER}} .ova-progress .ova-notes .note-text' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		                ],
		            ]
		        );

		        $this->add_control(
		            'note_margin',
		            [
		                'label' 		=> esc_html__( 'Margin', 'tripgo' ),
		                'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
		                'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
		                'selectors' 	=> [
		                    '{{WRAPPER}} .ova-progress .ova-notes .note-text' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		                ],
		            ]
		        );

			$this->end_controls_section(); // End notes style
		}

		/**
		 * Render template
		 */
		protected function render() {
			// Get settings
			$settings = $this->get_settings_for_display();

			// Show title
			$show_title = tripgo_get_meta_data( 'show_title', $settings );

			// Get title
			$title = tripgo_get_meta_data( 'title', $settings );

			// Get HTML tag
			$html_tag = tripgo_get_meta_data( 'html_tag', $settings );

			// Show percent
			$show_percent = tripgo_get_meta_data( 'show_percent', $settings );

			// Get percent
			$percent = tripgo_get_meta_data( 'percent', $settings );

			// Percent background
			$percent_bg = tripgo_get_meta_data( 'percent_bg', $settings );

			// Linear gradient
			$linear_gradient = tripgo_get_meta_data( 'percent_linear_gradient', $settings );

			// Background
			$bg = '';
			if ( $percent_bg && $linear_gradient ) {
				$bg = 'style="background: linear-gradient(270deg, '. $percent_bg .' 0%, '. $linear_gradient .' 100%);"';
			}

			// Show notes
			$show_notes = tripgo_get_meta_data( 'show_notes', $settings );

			// Get notes
			$notes = tripgo_get_meta_data( 'notes', $settings );

			?>
			<div class="ova-progress">
				<?php if ( 'yes' === $show_title ): ?>
					<<?php echo esc_html( $html_tag ); ?> class="ova-progress-title">
						<?php echo esc_html( $title ); ?>
					</<?php echo esc_html( $html_tag ); ?>>
				<?php endif; ?>
				<div class="ova-percent-view" <?php echo wp_kses_post( $bg ); ?>>
					<div class="ova-percent" data-percent="<?php echo esc_attr( $percent ); ?>"></div>
					<span class="percentage" data-show-percent="<?php echo esc_attr( $show_percent ); ?>">
						<?php echo sprintf( esc_html__( '%s%%', 'tripgo' ), $percent ); ?>
					</span>
				</div>
				<?php if ( 'yes' === $show_notes && tripgo_array_exists( $notes ) ): ?>
					<div class="ova-notes">
					<?php foreach ( $notes as $item ):
						// Get item id
						$item_id = tripgo_get_meta_data( '_id', $item );

						// Get item style
						$item_style = '';

						// Get item left
						$left = tripgo_get_meta_data( 'item_left', $item );
						if ( $left ) {
							$item_style 	= 'style="margin-left: '. $left .'%;"';
						}

						// Get item text
						$item_text = tripgo_get_meta_data( 'item_text', $item );
					?>
						<div class="item-note elementor-repeater-item-<?php echo esc_attr( $item_id ); ?>" <?php echo wp_kses_post( $item_style ); ?>>
							<span class="note-text">
								<?php echo esc_html( $item_text ); ?>
							</span>
						</div>
					<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
			<?php
		}
	}

	// init widget
	$widgets_manager->register( new Tripgo_Elementor_Progress() );
}