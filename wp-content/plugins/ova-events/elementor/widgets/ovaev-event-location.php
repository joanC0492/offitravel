<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class OVAEV_Elementor_Event_Location
 */
if ( !class_exists( 'OVAEV_Elementor_Event_Location' ) ) {

	class OVAEV_Elementor_Event_Location extends \Elementor\Widget_Base {

		/**
		 * Get widget name
		 */
		public function get_name() {		
			return 'ova_event_location';
		}

		/**
		 * Get widget title
		 */
		public function get_title() {
			return esc_html__( 'Event Location', 'ovaev' );
		}

		/**
		 * Get widget icon
		 */
		public function get_icon() {
			return 'eicon-map-pin';
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
				'section_location',
				[
					'label' => esc_html__( 'Location', 'ovaev' ),
				]
			);

				$this->add_control(
					'icon',
					[
						'label' 	=> esc_html__( 'Icon', 'ovaev' ),
						'type' 		=> \Elementor\Controls_Manager::TEXT,
						'default' 	=> 'fas fa-map-marker-alt',
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
							'{{WRAPPER}} .ovaev-event-location' => 'text-align: {{VALUE}};',
						],
					]
				);

			$this->end_controls_section(); // END

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
		                    '{{WRAPPER}} .ovaev-event-location i' => 'color: {{VALUE}};',
		                ],
		            ]
		        );

		        $this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'icon_typography',
						'selector' 	=> '{{WRAPPER}} .ovaev-event-location i',
					]
				);

		        $this->add_responsive_control(
		            'icon_margin',
		            [
		                'label' 		=> esc_html__( 'Margin', 'ovaev' ),
		                'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
		                'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
		                'selectors' 	=> [
		                    '{{WRAPPER}} .ovaev-event-location i' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		                ],
		            ]
		        );

			$this->end_controls_section(); // END

			$this->start_controls_section(
				'section_location_style',
				[
					'label' => esc_html__( 'Location', 'ovaev' ),
					'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_control(
		            'location_color',
		            [
		                'label' 	=> esc_html__( 'Color', 'ovaev' ),
		                'type' 		=> \Elementor\Controls_Manager::COLOR,
		                'selectors' => [
		                    '{{WRAPPER}} .ovaev-event-location span' => 'color: {{VALUE}};',
		                ],
		            ]
		        );

		        $this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'location_typography',
						'selector' 	=> '{{WRAPPER}} .ovaev-event-location span',
					]
				);

		        $this->add_responsive_control(
		            'location_margin',
		            [
		                'label' 		=> esc_html__( 'Margin', 'ovaev' ),
		                'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
		                'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
		                'selectors' 	=> [
		                    '{{WRAPPER}} .ovaev-event-location span' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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

			$icon 	= $settings['icon'];
			$venue 	= get_post_meta( $id, 'ovaev_venue', true);

			if ( !empty( $venue ) ): ?>
				<div class="ovaev-event-location">
					<?php if ( $icon ): ?>
						<i class="<?php echo esc_attr( $icon ); ?>"></i>
					<?php endif; ?>
					<span class="second_font"><?php echo esc_html( $venue ); ?></span>
				</div>
			<?php endif;
		}
	}

	// Register new widget
	$widgets_manager->register( new OVAEV_Elementor_Event_Location() );
}