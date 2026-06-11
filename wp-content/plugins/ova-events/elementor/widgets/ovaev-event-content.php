<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class OVAEV_Elementor_Event_Content
 */
if ( !class_exists( 'OVAEV_Elementor_Event_Content' ) ) {

	class OVAEV_Elementor_Event_Content extends \Elementor\Widget_Base {

		/**
		 * Get widget name
		 */
		public function get_name() {		
			return 'ova_event_content';
		}

		/**
		 * Get widget title
		 */
		public function get_title() {
			return esc_html__( 'Event Content', 'ovaev' );
		}

		/**
		 * Get widget icon
		 */
		public function get_icon() {
			return 'eicon-post-content';
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
			// Content controls
			$this->start_controls_section(
				'section_content',
				[
					'label' => esc_html__( 'Content', 'ovaev' ),
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
							'justify' => [
								'title' => esc_html__( 'Justify', 'ovaev' ),
								'icon' 	=> 'eicon-text-align-justify',
							],
						],
						'default' 	=> '',
						'selectors' => [
							'{{WRAPPER}} .ovaev-event-content' => 'text-align: {{VALUE}};',
						],
					]
				);

			$this->end_controls_section(); // END

			// Styles
			$this->start_controls_section(
				'section_content_style',
				[
					'label' => esc_html__( 'Content', 'ovaev' ),
					'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_control(
					'content_color',
					[
						'label' 	=> esc_html__( 'Color', 'ovaev' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ovaev-event-content p' => 'color: {{VALUE}};',
						],
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'content_typography',
						'selector' 	=> '{{WRAPPER}} .ovaev-event-content p',
					]
				);

		        $this->add_responsive_control(
		            'content_margin',
		            [
		                'label' 		=> esc_html__( 'Margin', 'ovaev' ),
		                'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
		                'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
		                'selectors' 	=> [
		                    '{{WRAPPER}} .ovaev-event-content p' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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

			// Get content
			$content = apply_filters( 'the_content', get_the_content() );
			if ( $content ): ?>
				<div class="ovaev-event-content">
					<?php echo wp_kses_post( $content ); ?>
				</div>
			<?php endif;
		}
	}

	// Register new widget
	$widgets_manager->register( new OVAEV_Elementor_Event_Content() );
}