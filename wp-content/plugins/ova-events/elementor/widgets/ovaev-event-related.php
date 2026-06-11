<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class OVAEV_Elementor_Event_Related
 */
if ( !class_exists( 'OVAEV_Elementor_Event_Related' ) ) {

	class OVAEV_Elementor_Event_Related extends \Elementor\Widget_Base {

		/**
		 * Get widget name
		 */
		public function get_name() {		
			return 'ova_event_related';
		}

		/**
		 * Get widget title
		 */
		public function get_title() {
			return esc_html__( 'Event Related', 'ovaev' );
		}

		/**
		 * Get widget icon
		 */
		public function get_icon() {
			return 'eicon-product-related';
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
				'section_related_style',
				[
					'label' => esc_html__( 'Related', 'ovaev' ),
					'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_responsive_control(
					'item_spacing',
					[
						'label' 	=> esc_html__( 'Space Between', 'ovaev' ),
						'type' 		=> \Elementor\Controls_Manager::SLIDER,
						'range' 	=> [
							'px' 	=> [
								'min' => 0,
								'max' => 100,
							],
						],
						'selectors' => [
							'{{WRAPPER}} .ovaev-event-related .content-event .event-related .archive_event' => 'grid-gap: {{SIZE}}{{UNIT}};',
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
		            'title_color_normal',
		            [
		                'label' 	=> esc_html__( 'Color', 'ovaev' ),
		                'type' 		=> \Elementor\Controls_Manager::COLOR,
		                'selectors' => [
		                    '{{WRAPPER}} .ovaev-event-related .content-event .event-related .related-event' => 'color: {{VALUE}};',
		                ],
		            ]
		        );

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'title_typography',
						'selector' 	=> '{{WRAPPER}} .ovaev-event-related .content-event .event-related .related-event',
					]
				);

		        $this->add_responsive_control(
		            'title_margin',
		            [
		                'label' 		=> esc_html__( 'Margin', 'ovaev' ),
		                'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
		                'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
		                'selectors' 	=> [
		                    '{{WRAPPER}} .ovaev-event-related .content-event .event-related .related-event' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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

			// Get template
			$template = apply_filters( 'ovaev_elementor_event_related_template', 'single/related.php' );

			ob_start();
			?>
			<div class="ovaev-event-related single_event">
				<div class="content-event">
				<?php
					ovaev_get_template( $template, $settings );
					echo ob_get_clean();
				?>
				</div>
			</div>
			<?php
		}
	}

	// Register new widget
	$widgets_manager->register( new OVAEV_Elementor_Event_Related() );
}