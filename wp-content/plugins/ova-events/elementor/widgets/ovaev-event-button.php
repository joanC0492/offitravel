<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class OVAEV_Elementor_Event_Button
 */
if ( !class_exists( 'OVAEV_Elementor_Event_Button' ) ) {

	class OVAEV_Elementor_Event_Button extends \Elementor\Widget_Base {

		/**
		 * Get widget name
		 */
		public function get_name() {		
			return 'ova_event_button';
		}

		/**
		 * Get widget title
		 */
		public function get_title() {
			return esc_html__( 'Booking Button', 'ovaev' );
		}

		/**
		 * Get widget icon
		 */
		public function get_icon() {
			return 'eicon-button';
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
		   	// Content
			$this->start_controls_section(
				'section_booking_button',
				[
					'label' => esc_html__( 'Button', 'ovaev' )
				]
			);

				$this->add_control(
					'target',
					[
						'label' 	=> esc_html__( 'Open in new window', 'ovaev' ),
						'type' 		=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 	=> esc_html__( 'Yes', 'ovaev' ),
						'label_off' => esc_html__( 'No', 'ovaev' )
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
								'icon' 	=> 'eicon-text-align-left'
							],
							'center' => [
								'title' => esc_html__( 'Center', 'ovaev' ),
								'icon' 	=> 'eicon-text-align-center'
							],
							'right' => [
								'title' => esc_html__( 'Right', 'ovaev' ),
								'icon' 	=> 'eicon-text-align-right'
							],
						],
						'default' 	=> '',
						'selectors' => [
							'{{WRAPPER}} .ovaev-booking-btn' => 'text-align: {{VALUE}};'
						]
					]
				);

			$this->end_controls_section(); // END

			// Button controls
			$this->start_controls_section(
				'section_Button_style',
				[
					'label' => esc_html__( 'Button', 'ovaev' ),
					'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'booking_btn_typography',
						'selector' 	=> '{{WRAPPER}} .ovaev-booking-btn a',
					]
				);

				$this->start_controls_tabs('style_booking_btn_tabs');

					$this->start_controls_tab(
						'style_booking_btn_normal_tab',
						[
							'label' => esc_html__( 'Normal', 'ovaev' ),
						]
					);

						$this->add_control(
							'booking_btn_color_normal',
							[
								'label' 	=> esc_html__( 'Color', 'ovaev' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ovaev-booking-btn a' => 'color: {{VALUE}}',
								],
							]
						);

						$this->add_control(
							'booking_btn_background_normal',
							[
								'label' 	=> esc_html__( 'Background', 'ovaev' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ovaev-booking-btn a' => 'background-color: {{VALUE}}',
								],
							]
						);

					$this->end_controls_tab();

					$this->start_controls_tab(
						'style_booking_btn_hover_tab',
						[
							'label' => esc_html__( 'Hover', 'ovaev' ),
						]
					);

						$this->add_control(
							'booking_btn_color_hover',
							[
								'label' 	=> esc_html__( 'Color', 'ovaev' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ovaev-booking-btn a:hover' => 'color: {{VALUE}}',
								],
							]
						);

						$this->add_control(
							'booking_btn_background_hover',
							[
								'label' 	=> esc_html__( 'Background', 'ovaev' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ovaev-booking-btn a:hover' => 'background-color: {{VALUE}}',
								],
							]
						);

					$this->end_controls_tab();
				$this->end_controls_tabs();

				$this->add_group_control(
					\Elementor\Group_Control_Border::get_type(),
					[
						'name' 		=> 'booking_btn_border',
						'label' 	=> esc_html__( 'Border', 'ovaev' ),
						'selector' 	=> '{{WRAPPER}} .ovaev-booking-btn a',
						'separator' => 'before',
					]
				);

				$this->add_control(
					'booking_btn_border_color_hover',
					[
						'label' 	=> esc_html__( 'Border Color Hover', 'ovaev' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ovaev-booking-btn a:hover' => 'border-color: {{VALUE}}',
						],
					]
				);

				$this->add_responsive_control(
					'booking_btn_border_radius',
					[
						'label' 		=> esc_html__( 'Border Radius', 'ovaev' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ovaev-booking-btn a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_responsive_control(
					'booking_btn_padding',
					[
						'label' 		=> esc_html__( 'Padding', 'ovaev' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ovaev-booking-btn a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_responsive_control(
					'booking_btn_margin',
					[
						'label' 		=> esc_html__( 'Margin', 'ovaev' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ovaev-booking-btn a' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

			$this->end_controls_section(); // END
		}

		/**
		 * Render
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
			$template = apply_filters( 'ovaev_elementor_event_button_template', 'elements/ovaev_event_button.php' );
			ob_start();
			ovaev_get_template( $template, $settings );
			echo ob_get_clean();
		}
	}

	// Register new widget
	$widgets_manager->register( new OVAEV_Elementor_Event_Button() );
}