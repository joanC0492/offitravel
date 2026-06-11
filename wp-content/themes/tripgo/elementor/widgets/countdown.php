<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class Tripgo_Elementor_Countdown
 */
if ( !class_exists( 'Tripgo_Elementor_Countdown', false ) ) {

	class Tripgo_Elementor_Countdown extends \Elementor\Widget_Base {

		/**
		 * Get widget name
		 */
		public function get_name() {
			return 'tripgo_elementor_countdown';
		}

		/**
		 * Get widget title
		 */
		public function get_title() {
			return esc_html__( 'Countdown', 'tripgo' );
		}

		/**
		 * Get widget icon
		 */
		public function get_icon() {
			return 'eicon-countdown';
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
			return [ 'tripgo-elementor-countdown' ];
		}

		/**
		 * Get script depends
		 */
		public function get_script_depends() {
			return [ 'ova-countdown-plugin', 'ova-countdown', 'tripgo-elementor-countdown' ];
		}
		
		// Register controls
		protected function register_controls() {
			// Content
			$this->start_controls_section(
				'section_content',
				[
					'label' => esc_html__( 'Content', 'tripgo' )
				]
			);

				$this->add_control(
					'due_date',
					[
						'label' => esc_html__( 'Due Date', 'tripgo' ),
						'type' 	=> \Elementor\Controls_Manager::DATE_TIME
					]
				);

				$this->add_control(
					'text_day',
					[
						'label' 	=> esc_html__( 'Text Day', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::TEXT,
						'default' 	=> esc_html__( 'Days', 'tripgo' )
					]
				);

				$this->add_control(
					'text_hour',
					[
						'label' 	=> esc_html__( 'Text Hour', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::TEXT,
						'default' 	=> esc_html__( 'Hour', 'tripgo' )
					]
				);

				$this->add_control(
					'text_min',
					[
						'label' 	=> esc_html__( 'Text Minute', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::TEXT,
						'default' 	=> esc_html__( 'Min', 'tripgo' )
					]
				);

				$this->add_control(
					'text_sec',
					[
						'label' 	=> esc_html__( 'Text Second', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::TEXT,
						'default' 	=> esc_html__( 'Sec', 'tripgo' )
					]
				);

			$this->end_controls_section(); // END

			// Number
			$this->start_controls_section(
				'number_style_section',
				[
					'label' => esc_html__( 'Number', 'tripgo' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE
				]
			);

				$this->add_responsive_control(
					'number_margin',
					[
						'label' 		=> esc_html__( 'Margin', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-countdown .number' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
						]
					]
				);

				$this->add_responsive_control(
					'number_padding',
					[
						'label' 		=> esc_html__( 'Padding', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-countdown .number' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
						]
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'number_typography',
						'selector' 	=> '{{WRAPPER}} .ova-countdown .number'
					]
				);

				$this->add_control(
					'number_color',
					[
						'label' 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-countdown .number' => 'color: {{VALUE}}'
						]
					]
				);

			$this->end_controls_section(); // END

			// Text
			$this->start_controls_section(
				'text_style_section',
				[
					'label' => esc_html__( 'Text', 'tripgo' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE
				]
			);

				$this->add_responsive_control(
					'text_margin',
					[
						'label' 		=> esc_html__( 'Margin', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-countdown .text' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
						]
					]
				);

				$this->add_responsive_control(
					'text_padding',
					[
						'label' 		=> esc_html__( 'Padding', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-countdown .text' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
						]
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'text_typography',
						'selector' 	=> '{{WRAPPER}} .ova-countdown .text'
					]
				);

				$this->add_control(
					'text_color',
					[
						'label' 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-countdown .text' => 'color: {{VALUE}}'
						]
					]
				);

			$this->end_controls_section(); // END
		}

		// Render HTML
		protected function render() {
			// Get settings
			$settings = $this->get_settings_for_display();

			// Due date
			$due_date = strtotime( tripgo_get_meta_data( 'due_date', $settings ) );
			if ( !$due_date ) $due_date = time();
			
			// Text day
			$text_day = tripgo_get_meta_data( 'text_day', $settings );

			// Text hour
			$text_hour = tripgo_get_meta_data( 'text_hour', $settings );

			// Text min
			$text_min = tripgo_get_meta_data( 'text_min', $settings );

			// Text sec
			$text_sec = tripgo_get_meta_data( 'text_sec', $settings );

			// Data date
			$data_date = [
				'year' 		=> gmdate( 'Y', $due_date ),
				'month' 	=> gmdate( 'n', $due_date ),
				'day' 		=> gmdate( 'j', $due_date ),
				'hours' 	=> gmdate( 'G', $due_date ),
				'minutes' 	=> intval( gmdate( 'i', $due_date ) ),
				'timezone' 	=> get_option( 'gmt_offset' ),
				'textDay' 	=> $text_day,
				'textHour'  => $text_hour,
				'textMin' 	=> $text_min,
				'textSec' 	=> $text_sec
			];

			?>
			<div class="ova-countdown" data-date="<?php echo esc_attr( json_encode( $data_date ) ); ?>"></div>
			<?php
		}
	}

	// init widget
	$widgets_manager->register( new Tripgo_Elementor_Countdown() );
}