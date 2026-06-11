<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class Tripgo_Elementor_Testimonial
 */
if ( !class_exists( 'Tripgo_Elementor_Testimonial', false ) ) {

	class Tripgo_Elementor_Testimonial extends \Elementor\Widget_Base {

		/**
		 * Get widget name
		 */
		public function get_name() {
			return 'tripgo_elementor_testimonial';
		}

		/**
		 * Get widget title
		 */
		public function get_title() {
			return esc_html__( 'Ova Testimonial', 'tripgo' );
		}

		/**
		 * Get widget icon
		 */
		public function get_icon() {
			return 'eicon-testimonial';
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
			return [ 'ova-slick', 'ova-slick-theme', 'tripgo-elementor-testimonial' ];
		}

		/**
		 * Get script depends
		 */
		public function get_script_depends() {
			return [ 'ova-slick', 'tripgo-elementor-testimonial' ];
		}

		/**
		 * Register controls
		 */
		protected function register_controls() {
			// Content controls
			$this->start_controls_section(
				'section_content',
				[
					'label' => esc_html__( 'Content', 'tripgo' ),
				]
			);

	            $this->add_control(
					'class_icon',
					[
						'label' 	=> esc_html__( 'Icon Quote', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::ICONS,
						'default' 	=> [
							'value' 	=> 'icomoon icomoon-quote-outline',
							'library' 	=> 'all',
						],
					]
				);

				// Add Class control
				$repeater = new \Elementor\Repeater();

				$repeater->add_control(
					'name_author',
					[
						'label'   => esc_html__( 'Author Name', 'tripgo' ),
						'type'    => \Elementor\Controls_Manager::TEXT,
					]
				);

				$repeater->add_control(
					'job',
					[
						'label'   => esc_html__( 'Job', 'tripgo' ),
						'type'    => \Elementor\Controls_Manager::TEXT,

					]
				);

				$repeater->add_control(
					'image_author',
					[
						'label'   => esc_html__( 'Author Image', 'tripgo' ),
						'type'    => \Elementor\Controls_Manager::MEDIA,
						'default' => [
							'url' => \Elementor\Utils::get_placeholder_image_src(),
						],
					]
				);

				$repeater->add_control(
					'testimonial',
					[
						'label'   => esc_html__( 'Testimonial ', 'tripgo' ),
						'type'    => \Elementor\Controls_Manager::TEXTAREA,
						'default' => esc_html__( '"Duis tristique volutpat facilisis. Integer vitae augue tellus. Phasellus fringilla tortor a maximus laoreet. Morbi a tristique erat. Fusce luctus urna vitae ornare aliquam."', 'tripgo' ),
					]
				);

				$this->add_control(
					'tab_item',
					[
						'label' 	=> esc_html__( 'Items Testimonial', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::REPEATER,
						'fields' 	=> $repeater->get_controls(),
						'default' 	=> [
							[
								'name_author' 	=> esc_html__( 'Mila McSabbu', 'tripgo' ),
								'job' 			=> esc_html__( 'Freelance Designer', 'tripgo' ),
								'testimonial' 	=> esc_html__( 'Duis tristique volutpat facilisis. Integer vitae augue tellus. Phasellus fringilla tortor a maximus laoreet. Morbi a tristique erat. Fusce luctus urna vitae ornare aliquam.', 'tripgo' ),
							],
							[
								'name_author' 	=> esc_html__( 'jenny wilson', 'tripgo' ),
								'job' 			=> esc_html__( 'Marketing head', 'tripgo' ),
								'testimonial' 	=> esc_html__( 'Duis tristique volutpat facilisis. Integer vitae augue tellus. Phasellus fringilla tortor a maximus laoreet. Morbi a tristique erat. Fusce luctus urna vitae ornare aliquam.', 'tripgo' ),
							],
							[
								'name_author' 	=> esc_html__( 'Mike Hardson', 'tripgo' ),
								'job' 			=> esc_html__( 'Developer', 'tripgo' ),
								'testimonial' 	=> esc_html__( 'Duis tristique volutpat facilisis. Integer vitae augue tellus. Phasellus fringilla tortor a maximus laoreet. Morbi a tristique erat. Fusce luctus urna vitae ornare aliquam.', 'tripgo' ),
							],
						],
						'title_field' => '{{{ name_author }}}',
					]
				);

			$this->end_controls_section(); // END SECTION CONTENT

			// Additional Options
			$this->start_controls_section(
				'section_additional_options',
				[
					'label' => esc_html__( 'Additional Options', 'tripgo' ),
				]
			);
				// VERSION 1
				$this->add_control(
					'pause_on_hover',
					[
						'label'   => esc_html__( 'Pause on Hover', 'tripgo' ),
						'type'    => \Elementor\Controls_Manager::SWITCHER,
						'default' => 'yes',
						'options' => [
							'yes' => esc_html__( 'Yes', 'tripgo' ),
							'no'  => esc_html__( 'No', 'tripgo' ),
						],
						'frontend_available' => true,
					]
				);


				$this->add_control(
					'infinite',
					[
						'label'   => esc_html__( 'Infinite Loop', 'tripgo' ),
						'type'    => \Elementor\Controls_Manager::SWITCHER,
						'default' => 'yes',
						'options' => [
							'yes' => esc_html__( 'Yes', 'tripgo' ),
							'no'  => esc_html__( 'No', 'tripgo' ),
						],
						'frontend_available' => true,
					]
				);

				$this->add_control(
					'autoplay',
					[
						'label'   => esc_html__( 'Autoplay', 'tripgo' ),
						'type'    => \Elementor\Controls_Manager::SWITCHER,
						'default' => 'yes',
						'options' => [
							'yes' => esc_html__( 'Yes', 'tripgo' ),
							'no'  => esc_html__( 'No', 'tripgo' ),
						],
						'frontend_available' => true,
					]
				);

				$this->add_control(
					'autoplay_speed',
					[
						'label'     => esc_html__( 'Autoplay Speed', 'tripgo' ),
						'type'      => \Elementor\Controls_Manager::NUMBER,
						'default'   => 3000,
						'step'      => 500,
						'condition' => [
							'autoplay' => 'yes',
						],
						'frontend_available' => true,
					]
				);

				$this->add_control(
					'smartspeed',
					[
						'label'   => esc_html__( 'Smart Speed', 'tripgo' ),
						'type'    => \Elementor\Controls_Manager::NUMBER,
						'default' => 500,
					]
				);

				$this->add_control(
					'dot_control',
					[
						'label'   => esc_html__( 'Show Dots', 'tripgo' ),
						'type'    => \Elementor\Controls_Manager::SWITCHER,
						'default' => 'yes',
						'options' => [
							'yes' => esc_html__( 'Yes', 'tripgo' ),
							'no'  => esc_html__( 'No', 'tripgo' ),
						],
						'frontend_available' => true,
					]
				);

			$this->end_controls_section(); // END SECTION ADDITIONAL

			// SECTION NAME JOB
			$this->start_controls_section(
				'section_general',
				[
					'label' => esc_html__( 'General', 'tripgo' ),
					'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_control(
					'style_quote',
					[
						'label' 	=> esc_html__( 'Quote', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::HEADING,
						'separator' => 'before',
					]
				);

				$this->add_control(
					'quote_color',
					[
						'label'     => esc_html__( 'Quote Job', 'tripgo' ),
						'type'      => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-testimonial .slide-testimonials .client-info .info .name-job .quote i' => 'color : {{VALUE}};',
						],
					]
				);

				$this->add_responsive_control(
					'quote_size',
					[
						'label' 	=> esc_html__( 'Size quote', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::SLIDER,
						'range' 	=> [
							'px' => [
								'min' => 0,
								'max' => 100,
							],
						],
						'selectors' => [
							'{{WRAPPER}} .ova-testimonial .slide-testimonials .client-info .info .name-job .quote i' => 'font-size: {{SIZE}}{{UNIT}}',
						],
					]
				);

				$this->add_control(
					'style_dots',
					[
						'label' 	=> esc_html__( 'Dots', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::HEADING,
						'separator' => 'before',
						'condition' => [
							'dot_control' => 'yes',
						],
					]
				);

				$this->add_control(
					'dot_color',
					[
						'label'     => esc_html__( 'Dot Color', 'tripgo' ),
						'type'      => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-testimonial .slide-testimonials .slick-dots button' => 'background-color : {{VALUE}};',
							
						],
						'condition' => [
							'dot_control' => 'yes',
						],
					]
				);

				$this->add_control(
					'opacity_dots',
					[
						'label' 	=> esc_html__( 'Opacity Dots', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::SLIDER,
						'default' 	=> [
							'size' => 0.2,
						],
						'range' 	=> [
							'px' => [
								'max' 	=> 1,
								'step' 	=> 0.01,
							],
						],
						'selectors' => [
							'{{WRAPPER}} .ova-testimonial .slide-testimonials .slick-dots button' => 'opacity: {{SIZE}};',
						],
						'condition' => [
							'dot_control' => 'yes',
						],
					]
				);

				$this->add_control(
					'dot_active_color',
					[
						'label'     => esc_html__( 'Dot Active Color', 'tripgo' ),
						'type'      => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-testimonial .slide-testimonials .slick-dots li.slick-active button' => 'background-color : {{VALUE}};',
							
						],
						'condition' => [
							'dot_control' => 'yes',
						],
					]
				);

				$this->add_control(
					'opacity_dots_active',
					[
						'label' 	=> esc_html__( 'Opacity Dots', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::SLIDER,
						'default' 	=> [
							'size' => 1,
						],
						'range' 	=> [
							'px' => [
								'max' 	=> 1,
								'step' 	=> 0.01,
							],
						],
						'selectors' => [
							'{{WRAPPER}} .ova-testimonial .slide-testimonials .slick-dots li.slick-active button' => 'opacity: {{SIZE}};',
						],
						'condition' => [
							'dot_control' => 'yes',
						],
					]
				);
				
				$this->add_control(
					'style_content_testimonial',
					[
						'label' 	=> esc_html__( 'Content', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::HEADING,
						'separator' => 'before',
					]

				);

				$this->add_responsive_control(
					'content__margin',
					[
						'label'      => esc_html__( 'Margin', 'tripgo' ),
						'type'       => \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors'  => [
							'{{WRAPPER}} .ova-testimonial .slide-testimonials ' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_responsive_control(
					'content__padding',
					[
						'label'      => esc_html__( 'Padding', 'tripgo' ),
						'type'       => \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors'  => [
							'{{WRAPPER}} .ova-testimonial .slide-testimonials ' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

			$this->end_controls_section(); // END section job

			// SECTION content testimonial
			$this->start_controls_section(
				'section_content_testimonial',
				[
					'label' => esc_html__( 'Content Testimonial', 'tripgo' ),
					'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name'     => 'content_testimonial_typography',
						'selector' => '{{WRAPPER}} .ova-testimonial .slide-testimonials .client-info .info p.ova-evaluate',
					]
				);

				$this->add_control(
					'content_color',
					[
						'label'     => esc_html__( 'Color', 'tripgo' ),
						'type'      => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-testimonial .slide-testimonials .client-info .info p.ova-evaluate' => 'color : {{VALUE}};',
						],
					]
				);

				$this->add_responsive_control(
					'content_margin',
					[
						'label'      => esc_html__( 'Margin', 'tripgo' ),
						'type'       => \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors'  => [
							'{{WRAPPER}} .ova-testimonial .slide-testimonials .client-info .info p.ova-evaluate' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_responsive_control(
					'content_padding',
					[
						'label'      => esc_html__( 'Padding', 'tripgo' ),
						'type'       => \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors'  => [
							'{{WRAPPER}} .ova-testimonial .slide-testimonials .client-info .info p.ova-evaluate' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

			$this->end_controls_section(); // END section content testimonial

			// SECTION NAME AUTHOR
			$this->start_controls_section(
				'section_author_name',
				[
					'label' => esc_html__( 'Author Name', 'tripgo' ),
					'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name'     => 'author_name_typography',
						'selector' => '{{WRAPPER}} .ova-testimonial .slide-testimonials .client-info .info .name-job .name',
					]
				);

				$this->add_control(
					'author_name_color',
					[
						'label'     => esc_html__( 'Color Author', 'tripgo' ),
						'type'      => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'
							{{WRAPPER}} .ova-testimonial .slide-testimonials .client-info .info .name-job .name' => 'color : {{VALUE}};',
						],
					]
				);

				$this->add_responsive_control(
					'author_name_margin',
					[
						'label'      => esc_html__( 'Margin', 'tripgo' ),
						'type'       => \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors'  => [
							'{{WRAPPER}} .ova-testimonial .slide-testimonials .client-info .info .name-job .name' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_responsive_control(
					'author_name_padding',
					[
						'label'      => esc_html__( 'Padding', 'tripgo' ),
						'type'       => \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors'  => [
							'{{WRAPPER}} .ova-testimonial .slide-testimonials .client_info .info .name-job .name' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

			$this->end_controls_section(); // END section author

			// SECTION NAME JOB
			$this->start_controls_section(
				'section_job',
				[
					'label' => esc_html__( 'Job', 'tripgo' ),
					'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name'     => 'job_typography',
						'selector' => '{{WRAPPER}} .ova-testimonial .slide-testimonials .client-info .info .name-job .job',
					]
				);

				$this->add_control(
					'job_color',
					[
						'label'     => esc_html__( 'Color Job', 'tripgo' ),
						'type'      => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-testimonial .slide-testimonials .client-info .info .name-job .job' => 'color : {{VALUE}};',
						],
					]
				);

				$this->add_responsive_control(
					'job_margin',
					[
						'label'      => esc_html__( 'Margin', 'tripgo' ),
						'type'       => \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors'  => [
							'{{WRAPPER}} .ova-testimonial .slide-testimonials .client-info .info .name-job .job' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_responsive_control(
					'job_padding',
					[
						'label'      => esc_html__( 'Padding', 'tripgo' ),
						'type'       => \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors'  => [
							'{{WRAPPER}} .ova-testimonial .slide-testimonials .client-info .info .name-job .job' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

			$this->end_controls_section(); // END section job

			// SECTION AVATAR
			$this->start_controls_section(
				'section_avatar',
				[
					'label' => esc_html__( 'Avatar', 'tripgo' ),
					'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_control(
					'image_size',
					[
						'label' 		=> esc_html__( 'Size', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::SLIDER,
						'size_units' 	=> [ 'px' ],
						'range' 		=> [
							'px' => [
								'min' 	=> 0,
								'max' 	=> 70,
								'step' 	=> 1,
							]
						],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-testimonial .slide-for .small-img img' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
						],
					]
				);


				$this->add_group_control(
					\Elementor\Group_Control_Border::get_type(),
					[
						'name' 		=> 'border_image',
						'label' 	=> esc_html__( 'Border', 'tripgo' ),
						'selector' 	=> '{{WRAPPER}} .ova-testimonial .slide-for .small-img img',
					]
				);

				$this->add_control(
					'border_color',
					[
						'label' 	=> esc_html__( 'Border Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-testimonial .slide-for .small-img img' => 'border-color: {{VALUE}};',
						],
					]
				);

				$this->add_control(
					'opacity_image_active',
					[
						'label' 	=> esc_html__( 'Opacity image active', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::SLIDER,
						'default' 	=> [
							'size' 	=> 1,
						],
						'range' 	=> [
							'px' => [
								'max' 	=> 1,
								'step' 	=> 0.01,
							],
						],
						'selectors' => [
							'{{WRAPPER}} .ova-testimonial .slide-for .slick-current .small-img img' => 'opacity: {{SIZE}};',
						],
						'condition' => [
							'dot_control' => 'yes',
						],
					]
				);

				$this->add_control(
					'opacity_image_',
					[
						'label' 	=> esc_html__( 'Opacity image', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::SLIDER,
						'default' 	=> [
							'size' 	=> 0.4,
						],
						'range' 	=> [
							'px' => [
								'max' 	=> 1,
								'step' 	=> 0.01,
							],
						],
						'selectors' => [
							'{{WRAPPER}} .ova-testimonial .slide-for .small-img img' => 'opacity: {{SIZE}};',
						],
						'condition' => [
							'dot_control' => 'yes',
						],
					]
				);

			$this->end_controls_section();
		}

		/**
		 * Render template
		 */
		protected function render() {
			// Get settings
			$settings = $this->get_settings_for_display();

			// Get tab item
			$tab_item = tripgo_get_meta_data( 'tab_item', $settings );
			
			// Class icon
			$icon = tripgo_get_meta_data( 'class_icon', $settings );

			// Carousel options
			$carousel_options = [
				'autoplay_speed' 	=> tripgo_get_meta_data( 'autoplay_speed', $settings ),
				'smartSpeed' 		=> tripgo_get_meta_data( 'smartspeed', $settings ),
				'loop' 				=> 'yes' === tripgo_get_meta_data( 'infinite', $settings ) ? true : false,
				'autoplay' 			=> 'yes' === tripgo_get_meta_data( 'autoplay', $settings ) ? true : false,
				'pause_on_hover' 	=> 'yes' === tripgo_get_meta_data( 'pause_on_hover', $settings ) ? true : false,
				'dots' 				=> 'yes' === tripgo_get_meta_data( 'dot_control', $settings ) ? true : false,
				'rtl' 				=> is_rtl() ? true: false
			];

			?>

			<div class="ova-testimonial template_1">
	            <div class="slide-for">
	            	<?php if ( tripgo_array_exists( $tab_item ) ):
	            		foreach ( $tab_item as $k => $item ):
	            			if ( $k >= 3 ) break;

	            			// Get image author
	            			$image_author = isset( $item['image_author']['url'] ) && $item['image_author']['url'] ? $item['image_author']['url'] : '';

	            			// Get image alt
	            			$image_alt = isset( $item['name_author'] ) && $item['name_author'] ? $item['name_author'] : esc_html__( 'Testimonial', 'tripgo' );

	            			if ( $image_author ): ?>
				         	    <div class="small-img">
									<img src="<?php echo esc_url( $image_author ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>">
								</div>	
							<?php endif;
						endforeach;
					endif; ?>
				</div>
				<div class="slide-testimonials slide-testimonial-version1" data-options="<?php echo esc_attr( json_encode( $carousel_options ) ); ?>">
					<?php if ( tripgo_array_exists( $tab_item ) ):
						foreach ( $tab_item as $item ):
							// Get testimonial
							$testimonial = tripgo_get_meta_data( 'testimonial', $item );

							// Get name author
							$name_author = tripgo_get_meta_data( 'name_author', $item );

							// Get job
							$job = tripgo_get_meta_data( 'job', $item );
						?>
						<div class="item">
							<div class="client-info">
								<div class="info">
									<?php if ( $testimonial ): ?>
										<p class="ova-evaluate">
											<?php echo esc_html( $testimonial ); ?>
										</p>
									<?php endif; ?>
									<div class="name-job">
										<div class="quote">
											<?php \Elementor\Icons_Manager::render_icon( $icon, [ 'aria-hidden' => 'true' ] ); ?>
										</div>
										<div class="name_job">
											<?php if ( $name_author ): ?>
												<h6 class="name second_font">
													<?php echo esc_html( $name_author ); ?>
												</h6>
											<?php endif;

											// Job
											if ( $job ): ?>
												<p class="job">
													<?php echo esc_html( $job ); ?>
												</p>
											<?php endif; ?>
										</div>
									</div>
								</div>
							</div>
						</div>
					<?php endforeach;
				endif; ?>
				</div>
			</div>
			<?php 
		}
	}

	// init widget
	$widgets_manager->register( new Tripgo_Elementor_Testimonial() );
}