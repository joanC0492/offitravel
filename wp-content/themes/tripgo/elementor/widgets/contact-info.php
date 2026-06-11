<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class Tripgo_Elementor_Contact_Info
 */
if ( !class_exists( 'Tripgo_Elementor_Contact_Info' ) ) {

	class Tripgo_Elementor_Contact_Info extends \Elementor\Widget_Base {

		/**
		 * Get widget name
		 */
		public function get_name() {
			return 'tripgo_elementor_contact_info';
		}

		/**
		 * Get widget title
		 */
		public function get_title() {
			return esc_html__( 'Contact Info', 'tripgo' );
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
			return [ 'tripgo-elementor-contact-info' ];
		}

		/**
		 * Register controls
		 */
		protected function register_controls() {
			/**
			 * Content Tab
			 */
			$this->start_controls_section(
				'section_content',
				[
					'label' => esc_html__( 'Content', 'tripgo' )
					
				]
			);

				$this->add_control(
					'icon',
					[
						'label' 	=> esc_html__( 'Class Icon', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::ICONS,
						'default' 	=> [
							'value' 	=> 'ovaicon-facebook-logo',
							'library' 	=> 'all'
						]
					]
				);

				$this->add_control(
					'label',
					[
						'label' 	=> esc_html__( 'Label', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::TEXT,
						'default' 	=> esc_html__( 'Label', 'tripgo' )
					]
				);

				$repeater = new \Elementor\Repeater();

				$repeater->add_control(
					'type',
					[
						'label' 	=> esc_html__( 'Type', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::SELECT,
						'default' 	=> 'email',
						'options' 	=> [
							'email' => esc_html__( 'Email', 'tripgo' ),
							'phone' => esc_html__( 'Phone', 'tripgo' ),
							'link' 	=> esc_html__( 'Link', 'tripgo' ),
							'text' 	=> esc_html__( 'Text', 'tripgo' )
						]
					]
				);

				$repeater->add_control(
					'email_label',
					[
						'label'   		=> esc_html__( 'Email Label', 'tripgo' ),
						'type'    		=> \Elementor\Controls_Manager::TEXT,
						'description' 	=> esc_html__( 'email@company.com', 'tripgo' ),
						'condition' 	=> [
							'type' => 'email'
						]
					]
				);

				$repeater->add_control(
					'email_address',
					[
						'label'   		=> esc_html__( 'Email Adress', 'tripgo' ),
						'type'    		=> \Elementor\Controls_Manager::TEXT,
						'description' 	=> esc_html__( 'email@company.com', 'tripgo' ),
						'condition' 	=> [
							'type' => 'email'
						]
					]
				);

				$repeater->add_control(
					'phone_label',
					[
						'label'   		=> esc_html__( 'Phone Label', 'tripgo' ),
						'type'    		=> \Elementor\Controls_Manager::TEXT,
						'description' 	=> esc_html__( '+012 (345) 678', 'tripgo' ),
						'condition' 	=> [
							'type' => 'phone'
						]
					]
				);

				$repeater->add_control(
					'phone_address',
					[
						'label'   		=> esc_html__( 'Phone Adress', 'tripgo' ),
						'type'    		=> \Elementor\Controls_Manager::TEXT,
						'description' 	=> esc_html__( '+012345678', 'tripgo' ),
						'condition' 	=> [
							'type' => 'phone'
						]
					]
				);

				$repeater->add_control(
					'link_label',
					[
						'label'   		=> esc_html__( 'Link Label', 'tripgo' ),
						'type'    		=> \Elementor\Controls_Manager::TEXT,
						'description' 	=> esc_html__( 'https://your-domain.com', 'tripgo' ),
						'condition' 	=> [
							'type' => 'link'
						]
					]
				);

				$repeater->add_control(
					'link_address',
					[
						'label'   		=> esc_html__( 'Link Adress', 'tripgo' ),
						'type'    		=> \Elementor\Controls_Manager::URL,
						'description' 	=> esc_html__( 'https://your-domain.com', 'tripgo' ),
						'condition' 	=> [
							'type' => 'link'
						],
						'show_external' => false,
						'default' => [
							'url' 			=> '#',
							'is_external' 	=> false,
							'nofollow' 		=> false
						]
					]
				);

				$repeater->add_control(
					'text',
					[
						'label'   		=> esc_html__( 'Text', 'tripgo' ),
						'type'    		=> \Elementor\Controls_Manager::TEXT,
						'description' 	=> esc_html__( 'Your text', 'tripgo' ),
						'condition' 	=> [
							'type' => 'text'
						]
					]
				);

				$this->add_control(
					'items_info',
					[
						'label' 	=> esc_html__( 'Items Info', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::REPEATER,
						'fields' 	=> $repeater->get_controls(),
						'default' 	=> [
							[
								'type' 			=> 'email',
								'email_label' 	=> esc_html__( 'email@company.com', 'tripgo' ),
								'email_address' => esc_html__( 'email@company.com', 'tripgo' )
							]
						],
						'title_field' => '{{{ type }}}'
					]
				);

			$this->end_controls_section(); // End Content Tab

			/**
			 * Icon Style Tab
			 */
			$this->start_controls_section(
				'section_icon_style',
				[
					'label' => esc_html__( 'Icon', 'tripgo' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE
				]
			);

				$this->add_control(
					'icon_fontsize',
					[
						'label' 		=> esc_html__( 'Font Size', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::SLIDER,
						'size_units' 	=> [ 'px' ],
						'range' 		=> [
							'px' => [
								'min' 	=> 1,
								'max' 	=> 300,
								'step' 	=> 1
							]
						],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-contact-info .icon' => 'font-size: {{SIZE}}{{UNIT}};',
							'{{WRAPPER}} .ova-contact-info .icon svg' => 'width: {{SIZE}}{{UNIT}};'
						]
					]
				);

				$this->add_control(
					'icon_color',
					[
						'label' 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-contact-info .icon' => 'color : {{VALUE}};',
							'{{WRAPPER}} .ova-contact-info .icon svg, {{WRAPPER}} .ova-contact-info .icon svg path' => 'fill : {{VALUE}};'
						]
					]
				);

				$this->add_control(
					'icon_background',
					[
						'label' 	=> esc_html__( 'Background', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-contact-info .icon' => 'background-color : {{VALUE}};'
						]
					]
				);

				$this->add_responsive_control(
					'icon_margin',
					[
						'label' 		=> esc_html__( 'Margin', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-contact-info .icon' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
						]
					]
				);

				$this->add_responsive_control(
					'icon_padding',
					[
						'label' 		=> esc_html__( 'Padding', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-contact-info .icon' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
						]
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Border::get_type(),
					[
						'name' 		=> 'border_icon',
						'label' 	=> esc_html__( 'Border', 'tripgo' ),
						'selector' 	=> '{{WRAPPER}} .ova-contact-info .icon'
					]
				);

				$this->add_responsive_control(
					'border_radius_icon',
					[
						'label'      => esc_html__( 'Border Radius', 'tripgo' ),
						'type'       => \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors'  => [
							'{{WRAPPER}} .ova-contact-info .icon' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
						]
					]
				);

			$this->end_controls_section(); // END Icon Style Tab

			/**
			 * Label Style Tab
			 */
			$this->start_controls_section(
				'section_label_style',
				[
					'label' => esc_html__( 'Label', 'tripgo' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE
				]
			);

				$this->add_control(
					'label_color',
					[
						'label' 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-contact-info .contact .label' => 'color : {{VALUE}};'
						]
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'label_typography',
						'selector' 	=> '{{WRAPPER}} .ova-contact-info .contact .label'
					]
				);

				$this->add_responsive_control(
					'label_margin',
					[
						'label' 		=> esc_html__( 'Margin', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-contact-info .contact .label' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
						]
					]
				);

			$this->end_controls_section(); // END Label Style Tab

			/**
			 * Info Style Tab
			 */
			$this->start_controls_section(
				'section_info_style',
				[
					'label' => esc_html__( 'Info', 'tripgo' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE
				]
			);

				$this->add_control(
					'info_color',
					[
						'label' 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-contact-info .contact .info .item' => 'color : {{VALUE}};',
							'{{WRAPPER}} .ova-contact-info .contact .info .item a' => 'color : {{VALUE}};'
						]
					]
				);

				$this->add_control(
					'info_color_hover',
					[
						'label' 	=> esc_html__( 'Link Color hover', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-contact-info .contact .info .item a:hover' => 'color : {{VALUE}};'
						]
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'info_typography',
						'selector' 	=> '{{WRAPPER}} .ova-contact-info .contact .info .item, {{WRAPPER}} .ova-contact-info .contact .info .item a'
					]
				);

				$this->add_responsive_control(
					'info_margin',
					[
						'label' 		=> esc_html__( 'Margin', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-contact-info .contact .info .item' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
						]
					]
				);

				$this->add_responsive_control(
	                'info_alignment',
	                [
	                    'label' 	=> esc_html__( 'Alignment', 'tripgo' ),
	                    'type' 		=> \Elementor\Controls_Manager::CHOOSE,
	                    'options' 	=> [
	                        'flex-start' 	=> [
	                            'title' 	=> esc_html__( 'Left', 'tripgo' ),
	                            'icon' 		=> 'eicon-v-align-top'
	                        ],
	                        'center' => [
	                            'title' => esc_html__( 'Center', 'tripgo' ),
	                            'icon' 	=> 'eicon-v-align-middle'
	                        ],
	                        'end' => [
	                            'title' => esc_html__( 'Right', 'tripgo' ),
	                            'icon' 	=> 'eicon-v-align-bottom'
	                        ],
	                    ],
	                    'selectors' => [
	                        '{{WRAPPER}} .ova-contact-info' => 'align-items: {{VALUE}};'
	                    ]
	                ]
	            );

			$this->end_controls_section(); // End Label Style Tab
		}

		/**
		 * Render HTML
		 */
		protected function render() {
			// Get settings
			$settings = $this->get_settings_for_display();

			// Get icon
			$icon = tripgo_get_meta_data( 'icon', $settings );

			// Get label
			$label = tripgo_get_meta_data( 'label', $settings );

			// Get items
			$items = tripgo_get_meta_data( 'items_info', $settings );
			
			?>
			<div class="ova-contact-info">
				<?php if ( !empty( $icon['value'] ) ): ?>
					<div class="icon">
						<?php \Elementor\Icons_Manager::render_icon( $icon, [ 'aria-hidden' => 'true' ] ); ?>
					</div>	
				<?php endif; ?>
				<div class="contact">
					<?php if ( $label ): ?>
						<div class="label">
							<?php echo esc_html( $label ); ?>
						</div>
					<?php endif; ?>
					<ul class="info">
						<?php if ( tripgo_array_exists( $items ) ):
							foreach ( $items as $item ):
								$type = tripgo_get_meta_data( 'type', $item );
							?>
								<li class="item">
									<?php switch ( $type ) {
										case 'email':
											// Get email address
											$email_address = tripgo_get_meta_data( 'email_address', $item );

											// Get label email
											$email_label = tripgo_get_meta_data( 'email_label', $item );

											if ( $email_address && $email_label ): ?>
												<a href="mailto:<?php echo esc_attr( $email_address ); ?>">
													<?php echo esc_html( $email_label ); ?>
												</a>
											<?php endif;
											break;
										case 'phone':
											// Get phone number
											$phone_number = tripgo_get_meta_data( 'phone_address', $item );

											// Get label phone
											$phone_label = tripgo_get_meta_data( 'phone_label', $item );

											if ( $phone_number && $phone_label ): ?>
												<a href="tel:<?php echo esc_attr( $phone_number ) ?> ">
													<?php echo esc_html( $phone_label ); ?>
												</a>
											<?php endif;
											break;
										case 'link':
											// Get URL
											$link_address = isset( $item['link_address']['url'] ) ? $item['link_address']['url'] : '';

											// Target
											$link_target = isset( $item['link_address']['is_external'] ) && $item['link_address']['is_external'] ? '_blank' : '_self';

											// Get label
											$link_label = tripgo_get_meta_data( 'link_label', $item );

											if ( $link_address ): ?>
												<a href="<?php echo esc_url( $link_address ); ?>" target="<?php echo esc_attr( $link_target ); ?>">
													<?php echo wp_kses_post( $link_label ); ?>
												</a>
											<?php else:
												echo wp_kses_post( $link_label );
											endif;
											break;
										case 'text':
											$text = tripgo_get_meta_data( 'text', $item );
											echo esc_html( $text );
											break;
										default:
											break;
									} // END switch ?>
								</li>
							<?php endforeach;
						endif; ?>
					</ul>
				</div>
			</div>
			<?php
		}
	}

	// init widget
	$widgets_manager->register( new Tripgo_Elementor_Contact_Info() );
}