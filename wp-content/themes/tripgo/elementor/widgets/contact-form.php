<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class Tripgo_Elementor_Contact_Form
 */
if ( !class_exists( 'Tripgo_Elementor_Contact_Form', false ) ) {

	class Tripgo_Elementor_Contact_Form extends \Elementor\Widget_Base {

		/**
		 * Get widget name
		 */
		public function get_name() {
			return 'tripgo_elementor_contact_form';
		}

		/**
		 * Get widget title
		 */
		public function get_title() {
			return esc_html__( 'Contact Form', 'tripgo' );
		}

		/**
		 * Get widget icon
		 */
		public function get_icon() {
			return 'eicon-email-field';
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
			return [ 'tripgo-elementor-contact-form' ];
		}

		/**
		 * Register controls
		 */
		protected function register_controls() {
			// Content Tab
			$this->start_controls_section(
				'section_content',
				[
					'label' => esc_html__( 'Content', 'tripgo' )
				]
			);

				// INFO
				$this->add_control(
					'style_info',
					[
						'label' 	=> esc_html__( 'Info', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::HEADING,
						'separator' => 'before'
					]
				);

				$this->add_control(
					'image',
					[
						'label'   => esc_html__( 'Image', 'tripgo' ),
						'type'    => \Elementor\Controls_Manager::MEDIA,
						'default' => [
							'url' => Elementor\Utils::get_placeholder_image_src()
						]
					]
				);

				$this->add_control(
					'label',
					[
						'label' 	=> esc_html__( 'Label', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::TEXT,
						'default' 	=> esc_html__( 'Need help?', 'tripgo' )
					]
				);

				$this->add_control(
					'label_des',
					[
						'label' 	=> esc_html__( 'Description', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::TEXT,
						'default' 	=> esc_html__( 'Call our product expert', 'tripgo' )
					]
				);

				$this->add_control(
					'phone_label',
					[
						'label' 	=> esc_html__( 'Phone Label', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::TEXT,
						'default' 	=> esc_html__( '(406) 555-0120-78', 'tripgo' )
					]
				);

				$this->add_control(
					'phone_address',
					[
						'label'   		=> esc_html__( 'Phone Adress', 'tripgo' ),
						'type'    		=> \Elementor\Controls_Manager::TEXT,
						'default' 		=> esc_html__( '406555012078', 'tripgo' ),
						'description' 	=> esc_html__( 'Phone number', 'tripgo' ),
						'condition' 	=> [
							'phone_label!' => ''
						]
					]
				);

				// BUTTON
				$this->add_control(
					'style_button',
					[
						'label' 	=> esc_html__( 'Button', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::HEADING,
						'separator' => 'before'
					]
				);

				$this->add_control(
					'button_icon',
					[
						'label' 	=> esc_html__( 'Class Icon', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::ICONS,
						'default' 	=> [
							'value' 	=> 'icomoon icomoon-chat',
							'library' 	=> 'all'
						]
					]
				);

				$this->add_control(
					'button_text',
					[
						'label'   		=> esc_html__( 'Text', 'tripgo' ),
						'type'    		=> \Elementor\Controls_Manager::TEXT,
						'default' 		=> esc_html__( 'chat with us', 'tripgo' ),
						'description' 	=> esc_html__( 'chat with us', 'tripgo' )
					]
				);

				$this->add_control(
					'link',
					[
						'label' 		=> esc_html__( 'Link', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::URL,
						'placeholder' 	=> esc_html__( 'https://your-link.com', 'tripgo' ),
						'default' 		=> [
							'url' 				=> '#',
							'is_external' 		=> false,
							'nofollow' 			=> false,
							'custom_attributes' => ''
						],
						'label_block' 	=> true
					]
				);

				// DATE TIME
				$this->add_control(
					'style_datetime',
					[
						'label' 	=> esc_html__( 'Date Time', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::HEADING,
						'separator' => 'before'
					]
				);

				$this->add_control(
					'date',
					[
						'label'   		=> esc_html__( 'Date', 'tripgo' ),
						'type'    		=> \Elementor\Controls_Manager::TEXT,
						'default' 		=> esc_html__( 'Mondays - Sundays', 'tripgo' ),
						'description' 	=> esc_html__( 'Mondays - Sundays', 'tripgo' )
					]
				);

				$this->add_control(
					'time',
					[
						'label'   		=> esc_html__( 'Time', 'tripgo' ),
						'type'    		=> \Elementor\Controls_Manager::TEXT,
						'default' 		=> esc_html__( '7am - 11pm ET | 4am - 8pm PT', 'tripgo' ),
						'description' 	=> esc_html__( '7am - 11pm ET | 4am - 8pm PT', 'tripgo' )
					]
				);

			$this->end_controls_section(); // End Content Tab

			// GENERAL Style Tab
			$this->start_controls_section(
				'section_general__',
				[
					'label' => esc_html__( 'General', 'tripgo' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE
				]
			);		
				$this->add_responsive_control(
					'general_pad',
					[
						'label' 		=> esc_html__( 'Padding', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-contact-form' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
						]
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Border::get_type(),
					[
						'name' 		=> 'border_general__',
						'label' 	=> esc_html__( 'Border', 'tripgo' ),
						'selector' 	=> '{{WRAPPER}} .ova-contact-form'
					]
				);

				$this->add_responsive_control(
					'generals_border_',
					[
						'label' 		=> esc_html__( 'Border Radius', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-contact-form' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
						]
					]
				);

			$this->end_controls_section(); // End GENERAL Tab

			/**
			 * INFO Style Tab
			 */
			$this->start_controls_section(
				'section_info_style',
				[
					'label' => esc_html__( 'Info', 'tripgo' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE
				]
			);
				// GENERAL
				$this->add_control(
					'general_',
					[
						'label' 	=> esc_html__( 'General', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::HEADING,
						'separator' => 'before'
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Border::get_type(),
					[
						'name' 		=> 'border',
						'label' 	=> esc_html__( 'Border', 'tripgo' ),
						'selector' 	=> '{{WRAPPER}} .ova-contact-form .contact-info'
					]
				);

				$this->add_responsive_control(
					'general_margin',
					[
						'label' 		=> esc_html__( 'Margin', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-contact-form .contact-info' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
						]
					]
				);	

				$this->add_responsive_control(
					'general_padding',
					[
						'label' 		=> esc_html__( 'Padding', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-contact-form .contact-info' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
						]
					]
				);

				//Label
				$this->add_control(
					'label_',
					[
						'label' 	=> esc_html__( 'Label', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::HEADING,
						'separator' => 'before'
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'info_label_typography',
						'selector' 	=> '{{WRAPPER}} .ova-contact-form .contact-info .info .label'
					]
				);

				$this->add_control(
					'info_label_color',
					[
						'label' 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-contact-form .contact-info .info .label' => 'color : {{VALUE}};'
						]
					]
				);

				$this->add_responsive_control(
					'info_label_margin',
					[
						'label' 		=> esc_html__( 'Margin', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-contact-form .contact-info .info .label' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
						]
					]
				);

				// DESCRIPTION
				$this->add_control(
					'DESCRIPTION',
					[
						'label' 	=> esc_html__( 'Description', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::HEADING,
						'separator' => 'before'
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'info_des_typography',
						'selector' 	=> '{{WRAPPER}} .ova-contact-form .contact-info .info .description'
					]
				);

				$this->add_control(
					'info_des_color',
					[
						'label' 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-contact-form .contact-info .info .description' => 'color : {{VALUE}};'
						]
					]
				);

				$this->add_responsive_control(
					'info_des_margin',
					[
						'label' 		=> esc_html__( 'Margin', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-contact-form .contact-info .info .description' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
						]
					]
				);

				// PHONE
				$this->add_control(
					'phone',
					[
						'label' 	=> esc_html__( 'Phone', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::HEADING,
						'separator' => 'before'
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'info_phone_typography',
						'selector' 	=> '{{WRAPPER}} .ova-contact-form .contact-info .info .phone-address'
					]
				);

				$this->add_control(
					'info_phone_color',
					[
						'label' 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-contact-form .contact-info .info .phone-address' => 'color : {{VALUE}};'
						]
					]
				);

				$this->add_responsive_control(
					'info_phone_margin',
					[
						'label' 		=> esc_html__( 'Margin', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-contact-form .contact-info .info .phone-address' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
						]
					]
				);
				
				// IMAGE
				$this->add_control(
					'image_',
					[
						'label' 	=> esc_html__( 'Image', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::HEADING,
						'separator' => 'before'
					]
				);

				$this->add_responsive_control(
					'info_image_margin',
					[
						'label' 		=> esc_html__( 'Margin', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-contact-form .contact-info img' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
						]
					]
				);

			$this->end_controls_section(); // END Info Style Tab

			// BUTTON
			$this->start_controls_section(
				'section_button_style',
				[
					'label' => esc_html__( 'Button', 'tripgo' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE
				]
			);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'button_typography',
						'selector' 	=> '{{WRAPPER}} .ova-contact-form .button span, .ova-contact-form .button i'
					]
				);

				$this->add_control(
					'button_color',
					[
						'label' 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-contact-form .button span' => 'color : {{VALUE}};',
							'{{WRAPPER}} .ova-contact-form .button i' => 'color : {{VALUE}};'
						]
					]
				);			

				$this->add_control(
					'button_color_hover',
					[
						'label' 	=> esc_html__( 'Color hover', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-contact-form .button:hover span' => 'color : {{VALUE}};',
							'{{WRAPPER}} .ova-contact-form .button:hover i' => 'color : {{VALUE}};'
						]
					]
				);			

				$this->add_control(
					'button_bg_color',
					[
						'label' 	=> esc_html__( 'Background', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-contact-form .button' => 'background-color : {{VALUE}};'
						]
					]
				);			

				$this->add_control(
					'button_bg_color_hover',
					[
						'label' 	=> esc_html__( 'Background hover', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-contact-form .button:hover' => 'background-color : {{VALUE}};'
						]
					]
				);

				$this->add_responsive_control(
					'button_margin',
					[
						'label' 		=> esc_html__( 'Margin', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-contact-form .button' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
						]
					]
				);

				$this->add_responsive_control(
					'button_padding',
					[
						'label' 		=> esc_html__( 'Padding', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-contact-form .button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
						]
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Border::get_type(),
					[
						'name' 		=> 'border_button',
						'label' 	=> esc_html__( 'Border', 'tripgo' ),
						'selector' 	=> '{{WRAPPER}} .ova-contact-form .button'
					]
				);

				$this->add_responsive_control(
					'button_border_radius',
					[
						'label' 		=> esc_html__( 'Border Radius', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-contact-form .button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
						]
					]
				);

			$this->end_controls_section(); // END BUTTON Style Tab

			/**
			 *==== DATE - TIME ====
			 */
			$this->start_controls_section(
				'section_date_time_style',
				[
					'label' => esc_html__( 'Date - Time', 'tripgo' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE
				]
			);
				// ====== Date ======
				$this->add_control(
					'style_date_',
					[
						'label' 	=> esc_html__( 'Date', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::HEADING,
						'separator' => 'before'
					]
				);
				
				$this->add_control(
					'date_color',
					[
						'label' 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-contact-form .date' => 'color : {{VALUE}};'
						]
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'date_typography',
						'selector' 	=> '{{WRAPPER}} .ova-contact-form .date'
					]
				);

				$this->add_responsive_control(
					'date_margin',
					[
						'label' 		=> esc_html__( 'Margin', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-contact-form .date' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
						]
					]
				);

				// ====== Time ======
				$this->add_control(
					'style_time_',
					[
						'label' 	=> esc_html__( 'Time', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::HEADING,
						'separator' => 'before'
					]
				);
				
				$this->add_control(
					'time_color',
					[
						'label' 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-contact-form .time' => 'color : {{VALUE}};'
						]
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'time_typography',
						'selector' 	=> '{{WRAPPER}} .ova-contact-form .time'
					]
				);

				$this->add_responsive_control(
					'time_margin',
					[
						'label' 		=> esc_html__( 'Margin', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-contact-form .time' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
						]
					]
				);

			$this->end_controls_section(); // END Label Style Tab
		}

		/**
		 * Render HTML
		 */
		protected function render() {
			// Get settings
			$settings = $this->get_settings_for_display();

			// Get label
			$label = tripgo_get_meta_data( 'label', $settings );

			// Get description
			$description = tripgo_get_meta_data( 'label_des', $settings );

			// Label phone
			$phone_label = tripgo_get_meta_data( 'phone_label', $settings );

			// Phone number
			$phone_number = tripgo_get_meta_data( 'phone_address', $settings );

			// Image URL
			$img_url = isset( $settings['image']['url'] ) ? $settings['image']['url'] : '';

			// Button icon
			$icon = tripgo_get_meta_data( 'button_icon', $settings );

			// Button text
			$text = tripgo_get_meta_data( 'button_text', $settings );

			// Custom link
			$link = isset( $settings['link']['url'] ) ? $settings['link']['url'] : '#';

			// Target
			$target = isset( $settings['link']['is_external'] ) && $settings['link']['is_external'] ? ' _blank' : '_self';
			
			// Date
			$date = tripgo_get_meta_data( 'date', $settings );

			// Time
			$time = tripgo_get_meta_data( 'time', $settings );

			?>
			<div class="ova-contact-form">
				<div class="contact-info">
					<img src="<?php echo esc_attr( $img_url ); ?>" class="avatar" alt="<?php echo esc_attr( $label ); ?>" >
					<div class="info">
						<?php if ( !$label ): ?>
							<h6 class="label"><?php echo esc_html( $label ); ?></h6>
						<?php endif; ?>
						<span class="description"><?php echo esc_html( $description ); ?></span>
						<?php if ( $phone_number && $phone_label ): ?>
							<a href="tel:<?php echo esc_attr( $phone_number ); ?> " class="phone-address">
								<?php echo esc_html( $phone_label ); ?>
							</a>
						<?php endif; ?>
					</div>
				</div>
				<?php if ( $text ): ?>
					<a href="<?php echo esc_url( $link ); ?>" class="button" target="<?php echo esc_attr( $target ); ?>">
						<?php \Elementor\Icons_Manager::render_icon( $icon, [ 'aria-hidden' => 'true' ] ); ?>
						<span> <?php echo esc_html( $text ); ?> </span>
					</a>
				<?php endif;

				// Date
				if ( $date ): ?>
					<span class="date"> <?php echo esc_html( $date ); ?></span>
				<?php endif;

				// Time
				if ( $time ): ?>
					<span class="time"> <?php echo esc_html( $time ); ?></span>
				<?php endif; ?>
			</div>
			<?php
		}
	}

	// init widget
	$widgets_manager->register( new Tripgo_Elementor_Contact_Form() );
}