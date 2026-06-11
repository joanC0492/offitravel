<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class OVAEV_Elementor_Event_Title
 */
if ( !class_exists( 'OVAEV_Elementor_Event_Title' ) ) {

	class OVAEV_Elementor_Event_Title extends \Elementor\Widget_Base {

		/**
		 * Get widget name
		 */
		public function get_name() {		
			return 'ova_event_title';
		}

		/**
		 * Get widget title
		 */
		public function get_title() {
			return esc_html__( 'Event Title', 'ovaev' );
		}

		/**
		 * Get widget icon
		 */
		public function get_icon() {
			return 'eicon-archive-title';
		}

		/**
		 * Get widget categories
		 */
		public function get_categories() {
			return [ 'ovaev_template' ];
		}

		/**
		 * Register controls
		 */
		protected function register_controls() {
			// Title controls
			$this->start_controls_section(
				'section_title',
				[
					'label' => esc_html__( 'Title', 'ovaev' ),
				]
			);
			
				$this->add_control(
					'link',
					[
						'label' 	=> esc_html__( 'Link', 'ovaev' ),
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
						'label' 	=> esc_html__( 'HTML Tag', 'ovaev' ),
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
						'default' => 'h2',
					]
				);

				$this->add_responsive_control(
					'align',
					[
						'label' 	=> esc_html__( 'Alignment', 'ovaev' ),
						'type' 		=> \Elementor\Controls_Manager::CHOOSE,
						'options' 	=> [
							'left' => [
								'title' => esc_html__( 'Left', 'ovaev' ),
								'icon' 	=> 'eicon-text-align-left',
							],
							'center' => [
								'title' => esc_html__( 'Center', 'ovaev' ),
								'icon' 	=> 'eicon-text-align-center',
							],
							'right' => [
								'title' => esc_html__( 'Right', 'ovaev' ),
								'icon' 	=> 'eicon-text-align-right',
							],
						],
						'default' 	=> '',
						'selectors' => [
							'{{WRAPPER}} .ovaev-event-title' => 'text-align: {{VALUE}};',
						],
					]
				);

			$this->end_controls_section(); // END

			$this->start_controls_section(
				'section_title_style',
				[
					'label' => esc_html__( 'Title', 'ovaev' ),
					'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_control(
					'title_color',
					[
						'label' 	=> esc_html__( 'Color', 'ovaev' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ovaev-event-title' => 'color: {{VALUE}};',
						],
						'condition' => [
			                'link[url]' => '',
			            ],
					]
				);

				$this->start_controls_tabs( 'tabs_title_style' );
					
					$this->start_controls_tab(
			            'tab_title_normal',
			            [
			                'label' 	=> esc_html__( 'Normal', 'ovaev' ),
			                'condition' => [
				                'link[url]!' => '',
				            ],
			            ]
			        );

			        	$this->add_control(
				            'title_color_normal',
				            [
				                'label' 	=> esc_html__( 'Color', 'ovaev' ),
				                'type' 		=> \Elementor\Controls_Manager::COLOR,
				                'selectors' => [
				                    '{{WRAPPER}} .ovaev-event-title a' => 'color: {{VALUE}};',
				                ],
				                'condition' => [
					                'link[url]!' => '',
					            ],
				            ]
				        );

			        $this->end_controls_tab();

			        $this->start_controls_tab(
			            'tab_title_hover',
			            [
			                'label' 	=> esc_html__( 'Hover', 'ovaev' ),
			                'condition' => [
				                'link[url]!' => '',
				            ],
			            ]
			        );

			        	$this->add_control(
				            'title_color_hover',
				            [
				                'label' 	=> esc_html__( 'Color', 'ovaev' ),
				                'type' 		=> \Elementor\Controls_Manager::COLOR,
				                'selectors' => [
				                    '{{WRAPPER}} .ovaev-event-title a:hover' => 'color: {{VALUE}};',
				                ],
					            'condition' => [
					                'link[url]!' => '',
					            ],
				            ]
				        );

			        $this->end_controls_tab();
				$this->end_controls_tabs();

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'title_typography',
						'selector' 	=> '{{WRAPPER}} .ovaev-event-title',
					]
				);


				$this->add_responsive_control(
		            'title_padding',
		            [
		                'label' 		=> esc_html__( 'Padding', 'ovaev' ),
		                'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
		                'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
		                'selectors' 	=> [
		                    '{{WRAPPER}} .ovaev-event-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		                ],
		            ]
		        );

		        $this->add_responsive_control(
		            'title_margin',
		            [
		                'label' 		=> esc_html__( 'Margin', 'ovaev' ),
		                'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
		                'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
		                'selectors' 	=> [
		                    '{{WRAPPER}} .ovaev-event-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		                ],
		            ]
		        );

			$this->end_controls_section(); // END
		}

		/**
		 * Render HTML
		 */
		protected function render() {
			// Get settings
			$settings = $this->get_settings_for_display();

			// Get current id
			$id = get_the_ID();

			// Get post type
			$post_type = get_post_type( $id );
			if ( empty( $post_type ) || 'event' != $post_type ) {
				echo '<div class="ovaev_elementor_none"><span>' . esc_html( $this->get_title() ) . '</span></div>';
				return;
			}

			// Get link
			$link 	  	= $settings['link']['url'];
			$target 	= '_blank';
			$target_url = isset( $settings['link']['is_external'] ) ? $settings['link']['is_external'] : '';
			if ( empty( $target_url ) ) {
				$target = '_self';
			}

			// Get header_size
			$header_size = $settings['header_size'];

			?>

			<<?php echo esc_attr( $header_size ); ?> class="ovaev-event-title">
				<?php if ( !empty( $link ) ): ?>
					<a href="<?php echo esc_url( $link ); ?>" target="<?php echo esc_attr( $target ); ?>">
						<?php the_title(); ?>
					</a>
				<?php else: ?>
					<?php the_title(); ?>
				<?php endif; ?>
			</<?php echo esc_attr( $header_size ); ?>>
			<?php
		}
	}

	// Register new widget
	$widgets_manager->register( new OVAEV_Elementor_Event_Title() );
}