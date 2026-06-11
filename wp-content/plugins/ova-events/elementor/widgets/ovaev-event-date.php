<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class OVAEV_Elementor_Event_Date
 */
if ( !class_exists( 'OVAEV_Elementor_Event_Date' ) ) {

	class OVAEV_Elementor_Event_Date extends \Elementor\Widget_Base {

		/**
		 * Get widget name
		 */
		public function get_name() {		
			return 'ova_event_date';
		}

		/**
		 * Get widget title
		 */
		public function get_title() {
			return esc_html__( 'Event Date', 'ovaev' );
		}

		/**
		 * Get widget icon
		 */
		public function get_icon() {
			return 'eicon-date';
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
			// Date controls
			$this->start_controls_section(
				'section_date',
				[
					'label' => esc_html__( 'Date', 'ovaev' ),
				]
			);

				$this->add_control(
					'date_format',
					[
						'label' 	=> esc_html__( 'Date Format', 'ovaev' ),
						'type' 		=> \Elementor\Controls_Manager::SELECT,
						'options' 	=> [
							'default' 	=> esc_html__( 'Default', 'ovaev' ),
							'd-m-Y' 	=> esc_html__( 'd-m-Y', 'ovaev' ),
							'm/d/Y' 	=> esc_html__( 'm/d/Y', 'ovaev' ),
							'Y/m/d' 	=> esc_html__( 'Y/m/d', 'ovaev' ),
							'Y-m-d' 	=> esc_html__( 'Y-m-d', 'ovaev' ),
						],
						'default' 	=> 'default',
					]
				);
				
				$this->add_control(
					'separator',
					[
						'label' 	=> esc_html__( 'Separator', 'ovaev' ),
						'type' 		=> \Elementor\Controls_Manager::TEXT,
						'default' 	=> esc_html__( ' - ', 'ovaev' ),
					]
				);

				$this->add_control(
					'icon',
					[
						'label' 	=> esc_html__( 'Icon', 'ovaev' ),
						'type' 		=> \Elementor\Controls_Manager::TEXT,
						'default' 	=> 'fas fa-calendar-alt',
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
							'{{WRAPPER}} .ovaev-event-date' => 'text-align: {{VALUE}};',
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
		                    '{{WRAPPER}} .ovaev-event-date i' => 'color: {{VALUE}};',
		                ],
		            ]
		        );

		        $this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'icon_typography',
						'selector' 	=> '{{WRAPPER}} .ovaev-event-date i',
					]
				);

		        $this->add_responsive_control(
		            'icon_margin',
		            [
		                'label' 		=> esc_html__( 'Margin', 'ovaev' ),
		                'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
		                'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
		                'selectors' 	=> [
		                    '{{WRAPPER}} .ovaev-event-date i' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		                ],
		            ]
		        );

			$this->end_controls_section(); // END

			/**
			 * Date controls
			 */
			$this->start_controls_section(
				'section_date_style',
				[
					'label' => esc_html__( 'Date', 'ovaev' ),
					'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_control(
		            'date_color',
		            [
		                'label' 	=> esc_html__( 'Color', 'ovaev' ),
		                'type' 		=> \Elementor\Controls_Manager::COLOR,
		                'selectors' => [
		                    '{{WRAPPER}} .ovaev-event-date span' => 'color: {{VALUE}};',
		                ],
		            ]
		        );

		        $this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'date_typography',
						'selector' 	=> '{{WRAPPER}} .ovaev-event-date span',
					]
				);

		        $this->add_responsive_control(
		            'date_margin',
		            [
		                'label' 		=> esc_html__( 'Margin', 'ovaev' ),
		                'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
		                'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
		                'selectors' 	=> [
		                    '{{WRAPPER}} .ovaev-event-date span' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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

			$icon 			= $settings['icon'];
			$separator 		= $settings['separator'];
			$date_format 	= $settings['date_format'];

			if ( 'default' == $date_format ) {
				$format = get_option('date_format');
			} else {
				$format = $settings['date_format'];
			}

			$post_start_date 	= get_post_meta( $id, 'ovaev_start_date_time', true );
			$post_end_date   	= get_post_meta( $id, 'ovaev_end_date_time', true );
			$start_date    		= $post_start_date 	!= '' ? date_i18n( $format, $post_start_date ) 	: '';
			$end_date      		= $post_end_date 	!= '' ? date_i18n( $format, $post_end_date ) 	: '';

			?>
			<div class="ovaev-event-date">
				<?php if ( $start_date == $end_date && $start_date != '' && $end_date != '' ):
					if ( $icon ): ?>
						<i class="<?php echo esc_attr( $icon ); ?>"></i>
					<?php endif; ?>
					<span class="second_font"><?php echo esc_html( $start_date ); ?></span>
				<?php elseif ( $start_date != $end_date && $start_date != '' && $end_date != '' ):
					if ( $icon ): ?>
						<i class="<?php echo esc_attr( $icon ); ?>"></i>
					<?php endif; ?>
					<span class="second_font">
						<?php echo esc_html( $start_date ); ?>
					</span>
					<span class="second_font separator">
						<?php echo esc_html( $separator ); ?>
					</span>
					<span class="second_font">
						<?php echo esc_html( $end_date ); ?>
					</span>
				<?php endif; ?>
			</div>
			<?php
		}
	}

	// Register new widget
	$widgets_manager->register( new OVAEV_Elementor_Event_Date() );
}