<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class OVAEV_Elementor_Event_Categories
 */
if ( !class_exists( 'OVAEV_Elementor_Event_Categories' ) ) {

	class OVAEV_Elementor_Event_Categories extends \Elementor\Widget_Base {

		/**
		 * Get widget name
		 */
		public function get_name() {		
			return 'ova_event_categories';
		}

		/**
		 * Get widget title
		 */
		public function get_title() {
			return esc_html__( 'Event Categories', 'ovaev' );
		}

		/**
		 * Get widget icon
		 */
		public function get_icon() {
			return 'eicon-product-categories';
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

			$this->start_controls_section(
				'section_categories',
				[
					'label' => esc_html__( 'Categories', 'ovaev' ),
				]
			);
				
				$this->add_control(
					'separator',
					[
						'label' 	=> esc_html__( 'Separator', 'ovaev' ),
						'type' 		=> \Elementor\Controls_Manager::TEXT,
						'default' 	=> esc_html__( ',', 'ovaev' ),
					]
				);

				$this->add_control(
					'icon',
					[
						'label' 	=> esc_html__( 'Icon', 'ovaev' ),
						'type' 		=> \Elementor\Controls_Manager::TEXT,
						'default' 	=> 'fa fa-list-alt',
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
						'selectors' => [
							'{{WRAPPER}} .ovaev-event-categories' => 'text-align: {{VALUE}};',
						],
					]
				);

			$this->end_controls_section(); // END

			// Icon controls
			$this->start_controls_section(
				'section_icon_style',
				[
					'label' => esc_html__( 'Icon', 'ovaev' ),
					'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_control(
		            'icon_color',
		            [
		                'label' 	=> esc_html__( 'Color', 'ovaev' ),
		                'type' 		=> \Elementor\Controls_Manager::COLOR,
		                'selectors' => [
		                    '{{WRAPPER}} .ovaev-event-categories i' => 'color: {{VALUE}};',
		                ],
		            ]
		        );

		        $this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'icon_typography',
						'selector' 	=> '{{WRAPPER}} .ovaev-event-categories i',
					]
				);

		        $this->add_responsive_control(
		            'icon_margin',
		            [
		                'label' 		=> esc_html__( 'Margin', 'ovaev' ),
		                'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
		                'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
		                'selectors' 	=> [
		                    '{{WRAPPER}} .ovaev-event-categories i' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		                ],
		            ]
		        );

			$this->end_controls_section();

			$this->start_controls_section(
				'section_categoris_style',
				[
					'label' => esc_html__( 'Categories', 'ovaev' ),
					'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->start_controls_tabs( 'tabs_categories_style' );
					
					$this->start_controls_tab(
			            'tab_categories_normal',
			            [
			                'label' => esc_html__( 'Normal', 'ovaev' ),
			            ]
			        );

			        	$this->add_control(
				            'categories_color_normal',
				            [
				                'label' 	=> esc_html__( 'Color', 'ovaev' ),
				                'type' 		=> \Elementor\Controls_Manager::COLOR,
				                'selectors' => [
				                    '{{WRAPPER}} .ovaev-event-categories .event-category a' => 'color: {{VALUE}};',
				                ],
				            ]
				        );

			        $this->end_controls_tab();

			        $this->start_controls_tab(
			            'tab_categories_hover',
			            [
			                'label' => esc_html__( 'Hover', 'ovaev' ),
			            ]
			        );

			        	$this->add_control(
				            'categories_color_hover',
				            [
				                'label' 	=> esc_html__( 'Color', 'ovaev' ),
				                'type' 		=> \Elementor\Controls_Manager::COLOR,
				                'selectors' => [
				                    '{{WRAPPER}} .ovaev-event-categories .event-category a:hover' => 'color: {{VALUE}};',
				                ],
				            ]
				        );

			        $this->end_controls_tab();
				$this->end_controls_tabs();

		        $this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'categories_typography',
						'selector' 	=> '{{WRAPPER}} .ovaev-event-categories .event-category a',
					]
				);

		        $this->add_responsive_control(
		            'time_margin',
		            [
		                'label' 		=> esc_html__( 'Margin', 'ovaev' ),
		                'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
		                'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
		                'selectors' 	=> [
		                    '{{WRAPPER}} .ovaev-event-categories .event-category' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		                ],
		            ]
		        );

			$this->end_controls_section(); // END

			// Separator
			$this->start_controls_section(
				'section_separator_style',
				[
					'label' => esc_html__( 'Separator', 'ovaev' ),
					'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_control(
		            'separator_color',
		            [
		                'label' 	=> esc_html__( 'Color', 'ovaev' ),
		                'type' 		=> \Elementor\Controls_Manager::COLOR,
		                'selectors' => [
		                    '{{WRAPPER}} .ovaev-event-categories .separator' => 'color: {{VALUE}};',
		                ],
		            ]
		        );

		        $this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'separator_typography',
						'selector' 	=> '{{WRAPPER}} .ovaev-event-categories .separator',
					]
				);

		        $this->add_responsive_control(
		            'separator_margin',
		            [
		                'label' 		=> esc_html__( 'Margin', 'ovaev' ),
		                'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
		                'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
		                'selectors' 	=> [
		                    '{{WRAPPER}} .ovaev-event-categories .separator' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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

			$icon 		= $settings['icon'];
			$separator 	= $settings['separator'];
			$categories = get_the_terms( $id, 'event_category' );

			if ( !empty( $categories ) && is_array( $categories ) ):
				$count 	= count( $categories );
				$i 		= 1;
			?>
				<div class="ovaev-event-categories">
					<?php if ( $icon ): ?>
						<i class="<?php echo esc_attr( $icon ); ?>"></i>
					<?php endif;

					// Loop
					foreach ( $categories as $category ):
						if ( $i == $count ) $separator = '';

	            		$link = get_term_link( $category->term_id );
	            		$name = $category->name;
					?>
					<span class="event-category">
	                	<a class="second_font" href="<?php echo esc_url( $link ); ?>">
	                		<?php echo esc_html( $name ); ?>
	                	</a>
	            	</span>
		            <span class="separator">
		                <?php echo esc_html( $separator ); ?>
		            </span>
					<?php $i++; endforeach; ?>
				</div>
			<?php endif;
		}
	}

	// Register new widget
	$widgets_manager->register( new OVAEV_Elementor_Event_Categories() );
}