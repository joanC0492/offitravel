<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class Tripgo_Elementor_Special_Offer_2
 */
if ( !class_exists( 'Tripgo_Elementor_Special_Offer_2', false ) ) {

	class Tripgo_Elementor_Special_Offer_2 extends \Elementor\Widget_Base {

		/**
		 * Get widget name
		 */
		public function get_name() {
			return 'tripgo_elementor_special_offer_2';
		}

		/**
		 * Get widget title
		 */
		public function get_title() {
			return esc_html__( 'Special Offer 2', 'tripgo' );
		}

		/**
		 * Get widget icon
		 */
		public function get_icon() {
			return 'eicon-image-box';
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
			return [ 'tripgo-elementor-special-offer-2' ];
		}
		
		/**
		 * Register controls
		 */
		protected function register_controls() {

			// Content
			$this->start_controls_section(
				'section_content',
				[
					'label' => esc_html__( 'Content', 'tripgo' ),
				]
			);

				$this->add_control(
					'link',
					[
						'label' 		=> esc_html__( 'Link', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::URL,
						'placeholder' 	=> esc_html__( 'https://your-link.com', 'tripgo' ),
						'options' 		=> [ 'url', 'is_external', 'nofollow' ],
						'default' 		=> [
							'url' 			=> '#',
							'is_external' 	=> false,
							'nofollow' 		=> false,
						],
						'label_block' 	=> true,
					]
				);

				$this->add_control(
					'image',
					[
						'label' 	=> esc_html__( 'Choose Image', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::MEDIA,
						'default' 	=> [
							'url' 	=> \Elementor\Utils::get_placeholder_image_src(),
						],
					]
				);

				$this->add_control(
					'subtitle',
					[
						'label' 		=> esc_html__( 'Subtitle', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::TEXT,
						'default' 		=> esc_html__( '-32% OFF', 'tripgo' ),
						'placeholder' 	=> esc_html__( 'Type your title here', 'tripgo' ),
					]
				);

				$this->add_control(
					'title',
					[
						'label' 		=> esc_html__( 'Title', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::TEXTAREA,
						'rows' 			=> 3,
						'default' 		=> esc_html__( 'Special Deal Of This Week', 'tripgo' ),
						'placeholder' 	=> esc_html__( 'Type your title here', 'tripgo' ),
					]
				);

				$this->add_control(
					'desc',
					[
						'label' 		=> esc_html__( 'Description', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::TEXTAREA,
						'rows' 			=> 3,
						'default' 		=> esc_html__( 'An enim nullam tempor sapien gravida donec enim', 'tripgo' ),
						'placeholder' 	=> esc_html__( 'Type your description here', 'tripgo' ),
					]
				);

				$this->add_control(
					'text_button',
					[
						'label' 		=> esc_html__( 'Text Button', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::TEXT,
						'default' 		=> esc_html__( 'Shop Now', 'tripgo' ),
						'placeholder' 	=> esc_html__( 'Type your text button here', 'tripgo' ),
					]
				);

			$this->end_controls_section();

			// General
			$this->start_controls_section(
				'general_style_section',
				[
					'label' => esc_html__( 'General', 'tripgo' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_responsive_control(
					'margin',
					[
						'label' 		=> esc_html__( 'Margin', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-special-offer-2' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_responsive_control(
					'padding',
					[
						'label' 		=> esc_html__( 'Padding', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-special-offer-2' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_responsive_control(
					'size',
					[
						'label' 		=> esc_html__( 'Size', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::SLIDER,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'range' 		=> [
							'px' => [
								'min' 	=> 0,
								'max' 	=> 1000,
								'step' 	=> 5,
							],
							'%' => [
								'min' => 0,
								'max' => 100,
							],
						],
						'selectors' => [
							'{{WRAPPER}} .ova-special-offer-2' => 'height: {{SIZE}}{{UNIT}};',
						],
					]
				);

				$this->add_responsive_control(
					'content_left',
					[
						'label' 		=> esc_html__( 'Content Margin', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::SLIDER,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'range' 		=> [
							'px' => [
								'min' 	=> 0,
								'max' 	=> 100,
								'step' 	=> 5,
							],
							'%' => [
								'min' => 0,
								'max' => 100,
							],
						],
						'selectors' => [
							'{{WRAPPER}} .ova-special-offer-2 .content' => 'padding-left: {{SIZE}}{{UNIT}};',
						],
					]
				);

			$this->end_controls_section();

			// Subtitle
			$this->start_controls_section(
					'subtitle_style_section',
					[
						'label' => esc_html__( 'Subtitle', 'tripgo' ),
						'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
					]
				);

				$this->add_responsive_control(
					'subtitle_margin',
					[
						'label' 		=> esc_html__( 'Margin', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-special-offer-2 .subtitle' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_responsive_control(
					'subtitle_padding',
					[
						'label' 		=> esc_html__( 'Padding', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-special-offer-2 .subtitle' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'subtitle_typography',
						'selector' 	=> '{{WRAPPER}} .ova-special-offer-2 .subtitle',
					]
				);

				$this->add_control(
					'subtitle_color',
					[
						'label' 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-special-offer-2 .subtitle' => 'color: {{VALUE}}',
						],
					]
				);

			$this->end_controls_section();

			// Title
			$this->start_controls_section(
					'title_style_section',
					[
						'label' => esc_html__( 'Title', 'tripgo' ),
						'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
					]
				);

				$this->add_responsive_control(
					'title_margin',
					[
						'label' 		=> esc_html__( 'Margin', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-special-offer-2 .title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_responsive_control(
					'title_padding',
					[
						'label' 		=> esc_html__( 'Padding', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-special-offer-2 .title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'title_typography',
						'selector' 	=> '{{WRAPPER}} .ova-special-offer-2 .title',
					]
				);

				$this->add_control(
					'title_color',
					[
						'label' 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-special-offer-2 .title' => 'color: {{VALUE}}',
							'{{WRAPPER}} .ova-special-offer-2 .title a' => 'color: {{VALUE}}',
						],
					]
				);

				$this->add_control(
					'title_hover_color',
					[
						'label' 	=> esc_html__( 'Hover Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-special-offer-2 .title a:hover' => 'color: {{VALUE}}',
						],
					]
				);

			$this->end_controls_section();

			/* Description */
			$this->start_controls_section(
					'desc_style_section',
					[
						'label' => esc_html__( 'Description', 'tripgo' ),
						'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
					]
				);

				$this->add_responsive_control(
					'desc_margin',
					[
						'label' 		=> esc_html__( 'Margin', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-special-offer-2 .desc' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_responsive_control(
					'desc_padding',
					[
						'label' 		=> esc_html__( 'Padding', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-special-offer-2 .desc' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'desc_typography',
						'selector' 	=> '{{WRAPPER}} .ova-special-offer-2 .desc',
					]
				);

				$this->add_control(
					'desc_color',
					[
						'label' 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-special-offer-2 .desc' => 'color: {{VALUE}}',
						],
					]
				);

			$this->end_controls_section();

			/* Text Button */
			$this->start_controls_section(
					'text_button_style_section',
					[
						'label' => esc_html__( 'Text Button', 'tripgo' ),
						'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
					]
				);

				$this->add_responsive_control(
					'text_button_margin',
					[
						'label' 		=> esc_html__( 'Margin', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-special-offer-2 .link' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_responsive_control(
					'text_button_padding',
					[
						'label' 		=> esc_html__( 'Padding', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-special-offer-2 .link' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'text_button_typography',
						'selector' 	=> '{{WRAPPER}} .ova-special-offer-2 .link',
					]
				);

				
				$this->start_controls_tabs(
					'style_tabs'
				);

					$this->start_controls_tab(
						'style_normal_tab',
						[
							'label' => esc_html__( 'Normal', 'tripgo' ),
						]
					);

						$this->add_control(
							'text_button_color',
							[
								'label' 	=> esc_html__( 'Color', 'tripgo' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ova-special-offer-2 .link' => 'color: {{VALUE}}',
								],
							]
						);

						$this->add_control(
							'text_button_background',
							[
								'label' 	=> esc_html__( 'Background', 'tripgo' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ova-special-offer-2 .link' => 'background: {{VALUE}}',
								],
							]
						);

					$this->end_controls_tab();

					$this->start_controls_tab(
						'style_hover_tab',
						[
							'label' => esc_html__( 'Hover', 'tripgo' ),
						]
					);

						$this->add_control(
							'text_button_hover_color',
							[
								'label' 	=> esc_html__( 'Color', 'tripgo' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ova-special-offer-2 .link:hover' => 'color: {{VALUE}}',
								],
							]
						);

						$this->add_control(
							'text_button_hover_background',
							[
								'label' 	=> esc_html__( 'Background', 'tripgo' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ova-special-offer-2 .link:hover' => 'background: {{VALUE}}',
								],
							]
						);

					$this->end_controls_tab();

				$this->end_controls_tabs();

			$this->end_controls_section();
		}

		/**
		 * Render template
		 */
		protected function render() {
			// Get settings
			$settings = $this->get_settings_for_display();

			// Get sub-title
			$subtitle = tripgo_get_meta_data( 'subtitle', $settings );

			// Get title
			$title = tripgo_get_meta_data( 'title', $settings );

			// Get description
			$desc = tripgo_get_meta_data( 'desc', $settings );

			// Get link URL
			$link_url = isset( $settings['link']['url'] ) && $settings['link']['url'] ? $settings['link']['url'] : '';

			// Get link target
			$link_target = isset( $settings['link']['is_external'] ) && $settings['link']['is_external'] ? '_blank' : '_self';

			// Get link nofollow
			$link_nofollow = isset( $settings['link']['nofollow'] ) && $settings['link']['nofollow'] ? 'nofollow' : '';

			// Get text button
			$text_button = tripgo_get_meta_data( 'text_button', $settings );

			// Get image URL
			$image_url = isset( $settings['image']['url'] ) && $settings['image']['url'] ? $settings['image']['url'] : \Elementor\Utils::get_placeholder_image_src();

			?>
			<div class="ova-special-offer-2">
				<div class="background-image" style="background-image: url('<?php echo esc_url( $image_url ); ?>');"></div>
				<div class="content">
					<?php if ( $subtitle ): ?>
						<h5 class="subtitle"><?php echo esc_html( $subtitle ); ?></h5>
					<?php endif;

					// Title
					if ( $title ):
						if ( $link_url ): ?>
							<h3 class="title">
								<a href="<?php echo esc_url( $link_url ); ?>" target="<?php echo esc_attr( $link_target ); ?>" rel="<?php echo esc_attr( $link_nofollow ); ?>">
									<?php echo esc_html( $title ); ?>
								</a>
							</h3>
						<?php else: ?>
							<h3 class="title"><?php echo esc_html( $title ); ?></h3>
						<?php endif;
					endif;

					// Description
					if ( $desc ): ?>
						<p class="desc"><?php echo esc_html( $desc ); ?></p>
					<?php endif;

					// Text button
					if ( $text_button ): ?>
						<a href="<?php echo esc_url( $link_url ); ?>" class="link" target="<?php echo esc_attr( $link_target ); ?>" rel="<?php echo esc_attr( $link_nofollow ); ?>">
							<?php echo esc_html( $text_button ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>
			<?php
		}
	}

	// init widget
	$widgets_manager->register( new Tripgo_Elementor_Special_Offer_2() );
}