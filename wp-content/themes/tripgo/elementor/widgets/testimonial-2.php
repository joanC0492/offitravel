<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class Tripgo_Elementor_Testimonial_2
 */
if ( !class_exists( 'Tripgo_Elementor_Testimonial_2', false ) ) {

	class Tripgo_Elementor_Testimonial_2 extends \Elementor\Widget_Base {

		/**
		 * Get widget name
		 */
		public function get_name() {
			return 'tripgo_elementor_testimonial_2';
		}

		/**
		 * Get widget title
		 */
		public function get_title() {
			return esc_html__( 'Ova Testimonial 2', 'tripgo' );
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
			return [ 'swiper', 'tripgo-elementor-testimonial-2' ];
		}

		/**
		 * Get script depends
		 */
		public function get_script_depends() {
			return [ 'swiper', 'tripgo-elementor-testimonial-2' ];
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
					'rating',
					[
						'label' 	=> esc_html__( 'Rating', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::NUMBER,
						'min' 		=> 0,
						'max' 		=> 10,
						'step' 		=> 0.1,
						'default' 	=> 5,
						'dynamic' 	=> [
							'active' => true,
						],
					]
				);
		
				$repeater->add_control(
					'star_style',
					[
						'label' 		=> esc_html__( 'Icon', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::SELECT,
						'options' 		=> [
							'star_fontawesome' 	=> 'Font Awesome',
							'star_unicode' 		=> 'Unicode',
						],
						'default' 		=> 'star_fontawesome',
						'render_type' 	=> 'template',
						'prefix_class' 	=> 'elementor--star-style-',
						'separator' 	=> 'before',
					]
				);
		
				$repeater->add_control(
					'unmarked_star_style',
					[
						'label' 	=> esc_html__( 'Unmarked Style', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::CHOOSE,
						'options' 	=> [
							'solid' => [
								'title' => esc_html__( 'Solid', 'tripgo' ),
								'icon' 	=> 'eicon-star',
							],
							'outline' => [
								'title' => esc_html__( 'Outline', 'tripgo' ),
								'icon' 	=> 'eicon-star-o',
							],
						],
						'default' => 'solid',
					]
				);

				$repeater->add_control(
					'link',
					[
						'label' 		=> esc_html__( 'Link', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::URL,
						'placeholder' 	=> esc_html__( 'https://your-link.com', 'tripgo' ),
						'default' 		=> [
							'url' 				=> '',
							'is_external' 		=> false,
							'nofollow' 			=> false,
							'custom_attributes' => '',
						],
						'label_block' => true,
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
						'default' => esc_html__( '"Sed ullamcorper morbi tincidunt or massa eget egestas purus. Non nisi est sit amet facilisis magna etiam."', 'tripgo' ),
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
								'testimonial' 	=> esc_html__( '"OMG! I cannot believe that I have got a brand new landing page after getting appmax. It was super easy to edit and publish.I have got a brand new landing page."', 'tripgo' ),
							],
							[
								'name_author' 	=> esc_html__( 'Jenny Wilson', 'tripgo' ),
								'job' 			=> esc_html__( 'UI/UX Designer', 'tripgo' ),
								'testimonial' 	=> esc_html__( '"OMG! I cannot believe that I have got a brand new landing page after getting appmax. It was super easy to edit and publish.I have got a brand new landing page."', 'tripgo' ),
							],
							[
								'name_author' 	=> esc_html__( 'Mila McSabbu', 'tripgo' ),
								'job' 			=> esc_html__( 'Governer Of Canada', 'tripgo' ),
								'testimonial' 	=> esc_html__( '"OMG! I cannot believe that I have got a brand new landing page after getting appmax. It was super easy to edit and publish.I have got a brand new landing page."', 'tripgo' ),
							],
						],
						'title_field' => '{{{ name_author }}}',
					]
				);
				
			$this->end_controls_section();

			// Additional Options
			$this->start_controls_section(
				'section_additional_options',
				[
					'label' => esc_html__( 'Additional Options', 'tripgo' ),
				]
			);

				$this->add_control(
					'margin_items',
					[
						'label'   => esc_html__( 'Margin Right Items', 'tripgo' ),
						'type'    => \Elementor\Controls_Manager::NUMBER,
						'default' => 30,
					]
					
				);

				$this->add_control(
					'item_number',
					[
						'label'       => esc_html__( 'Item Number', 'tripgo' ),
						'type'        => \Elementor\Controls_Manager::NUMBER,
						'description' => esc_html__( 'Number Item', 'tripgo' ),
						'default'     => 2,
					]
				);

				$this->add_control(
					'slides_to_scroll',
					[
						'label'       => esc_html__( 'Slides to Scroll', 'tripgo' ),
						'type'        => \Elementor\Controls_Manager::NUMBER,
						'description' => esc_html__( 'Set how many slides are scrolled per swipe.', 'tripgo' ),
						'default'     => 1,
					]
				);

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
					'nav_control',
					[
						'label'   => esc_html__( 'Show Nav', 'tripgo' ),
						'type'    => \Elementor\Controls_Manager::SWITCHER,
						'default' => 'no',
						'options' => [
							'yes' => esc_html__( 'Yes', 'tripgo' ),
							'no'  => esc_html__( 'No', 'tripgo' )
						],
						'frontend_available' => true
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
							'no'  => esc_html__( 'No', 'tripgo' )
						],
						'frontend_available' => true
					]
				);

			$this->end_controls_section();

			// SECTION NAME JOB.
			$this->start_controls_section(
				'section_general',
				[
					'label' => esc_html__( 'General', 'tripgo' ),
					'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_responsive_control(
					'testimonial_padding',
					[
						'label'      => esc_html__( 'Padding', 'tripgo' ),
						'type'       => \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors'  => [
							'{{WRAPPER}} .ova-testimonial.version-2 .slide-testimonials .swiper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_responsive_control(
					'testimonial_margin',
					[
						'label'      => esc_html__( 'Margin', 'tripgo' ),
						'type'       => \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors'  => [
							'{{WRAPPER}} .ova-testimonial.version-2 .slide-testimonials .swiper' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_control(
					'style_items',
					[
						'label' 	=> esc_html__( 'Items', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::HEADING,
						'separator' => 'before',
					]
				);

				$this->add_responsive_control(
					'item_padding',
					[
						'label'      => esc_html__( 'Padding', 'tripgo' ),
						'type'       => \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors'  => [
							'{{WRAPPER}} .ova-testimonial.version-2 .slide-testimonials .item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_control(
					'style_avatar',
					[
						'label' 	=> esc_html__( 'Avatar', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::HEADING,
						'separator' => 'before',
					]
				);

				$this->add_responsive_control(
					'avatar_margin',
					[
						'label'      => esc_html__( 'Margin', 'tripgo' ),
						'type'       => \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors'  => [
							'{{WRAPPER}} .ova-testimonial.version-2 .slide-testimonials .item .info-content .client-info .client' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

			$this->end_controls_section();

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
						'selector' => '{{WRAPPER}} .ova-testimonial.version-2 .slide-testimonials .item .evaluate',
					]
				);

				$this->add_control(
					'content_color',
					[
						'label'     => esc_html__( 'Color', 'tripgo' ),
						'type'      => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-testimonial.version-2 .slide-testimonials .item .evaluate' => 'color : {{VALUE}};',
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
							'{{WRAPPER}} .ova-testimonial.version-2 .slide-testimonials .item .evaluate' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
							'{{WRAPPER}} .ova-testimonial.version-2 .slide-testimonials .item .evaluate' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

			$this->end_controls_section();

			// SECTION NAME AUTHOR.
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
						'selector' => '{{WRAPPER}} .ova-testimonial.version-2 .slide-testimonials .item .info-content .client-info .name-job .name',
					]
				);

				$this->add_control(
					'author_name_color',
					[
						'label'     => esc_html__( 'Color Author', 'tripgo' ),
						'type'      => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'
							{{WRAPPER}} .ova-testimonial.version-2 .slide-testimonials .item .info-content .client-info .name-job .name' => 'color : {{VALUE}};',
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
							'{{WRAPPER}} .ova-testimonial.version-2 .slide-testimonials .item .info-content .client-info .name-job .name' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
							'{{WRAPPER}} .ova-testimonial.version-2 .slide-testimonials .item .info-content .client-info .name-job .name' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

			$this->end_controls_section();

			// SECTION NAME JOB.
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
						'selector' => '{{WRAPPER}} .ova-testimonial.version-2 .slide-testimonials .item .info-content .client-info .name-job .job',
					]
				);

				$this->add_control(
					'job_color',
					[
						'label'     => esc_html__( 'Color Job', 'tripgo' ),
						'type'      => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'
							{{WRAPPER}} .ova-testimonial.version-2 .slide-testimonials .item .info-content .client-info .name-job .job' => 'color : {{VALUE}};',
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
							'{{WRAPPER}} .ova-testimonial.version-2 .slide-testimonials .item .info-content .client-info .name-job .job' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
							'{{WRAPPER}} .ova-testimonial.version-2 .slide-testimonials .item .info-content .client-info .name-job .job' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

			$this->end_controls_section();
			
			$this->start_controls_section(
				'section_stars_style',
				[
					'label' 	=> esc_html__( 'Rating Stars', 'tripgo' ),
					'tab' 		=> \Elementor\Controls_Manager::TAB_STYLE,
					'condition' => [
						'version' => 'version_1',
					]
				]
			);

				$this->add_responsive_control(
					'icon_size',
					[
						'label' => esc_html__( 'Size', 'tripgo' ),
						'type' 	=> \Elementor\Controls_Manager::SLIDER,
						'range' => [
							'px' => [
								'min' => 0,
								'max' => 100,
							],
						],
						'selectors' => [
							'{{WRAPPER}} .elementor-star-rating' => 'font-size: {{SIZE}}{{UNIT}}',
						],
					]
				);

				$this->add_responsive_control(
					'icon_space',
					[
						'label' => esc_html__( 'Spacing', 'tripgo' ),
						'type' 	=> \Elementor\Controls_Manager::SLIDER,
						'range' => [
							'px' => [
								'min' => 0,
								'max' => 50,
							],
						],
						'selectors' => [
							'body:not(.rtl) {{WRAPPER}} .elementor-star-rating i:not(:last-of-type)' => 'margin-right: {{SIZE}}{{UNIT}}',
							'body.rtl {{WRAPPER}} .elementor-star-rating i:not(:last-of-type)' => 'margin-left: {{SIZE}}{{UNIT}}',
						],
					]
				);

				$this->add_control(
					'stars_color',
					[
						'label' 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-testimonial.version_1 .slide-testimonials .item .client_info .ova-rating .elementor-star-rating i::before' => 'color: {{VALUE}}',
							
						],
						'separator' => 'before',
					]
				);

				$this->add_control(
					'stars_unmarked_color',
					[
						'label' 	=> esc_html__( 'Unmarked Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .elementor-star-rating i' => 'color: {{VALUE}}',
						],
					]
				);

			$this->end_controls_section();

			// Navigation
			$this->start_controls_section(
				'section_nav',
				[
					'label' 	=> esc_html__( 'Nav', 'tripgo' ),
					'tab' 		=> \Elementor\Controls_Manager::TAB_STYLE,
					'condition' => [
						'nav_control' => 'yes'
					]
				]
			);

				$this->add_control(
					'nav_size',
					[
						'type' 			=> \Elementor\Controls_Manager::SLIDER,
						'label' 		=> esc_html__( 'Size', 'tripgo' ),
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'range' 		=> [
							'px' => [
								'min' 	=> 0,
								'max' 	=> 200
							],
							'%' => [
								'min' => 0,
								'max' => 100
							],
						],
						'selectors' => [
							'{{WRAPPER}} .ova-testimonial.version-2 .slide-testimonials .button-nav' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};'
						]
					]
				);

				$this->add_control(
					'nav_spacing',
					[
						'type' 			=> \Elementor\Controls_Manager::SLIDER,
						'label' 		=> esc_html__( 'Horizontal Spacing', 'tripgo' ),
						'size_units' 	=> [ 'px', 'em', 'rem', 'custom' ],
						'range' 		=> [
							'px' => [
								'min' 	=> -200,
								'max' 	=> 200
							]
						],
						'selectors' => [
							'{{WRAPPER}} .ova-testimonial.version-2 .slide-testimonials .button-nav.button-prev' => 'left: {{SIZE}}{{UNIT}};',
							'{{WRAPPER}} .ova-testimonial.version-2 .slide-testimonials .button-nav.button-next' => 'right: {{SIZE}}{{UNIT}};',
						]
					]
				);

				$this->start_controls_tabs(
					'nav_tabs'
				);

					$this->start_controls_tab(
						'nav_normal_tab',
						[
							'label' => esc_html__( 'Normal', 'tripgo' ),
						]
					);

						$this->add_control(
							'nav_icon_size',
							[
								'type' 			=> \Elementor\Controls_Manager::SLIDER,
								'label' 		=> esc_html__( 'Icon Size', 'tripgo' ),
								'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
								'range' 		=> [
									'px' => [
										'min' 	=> 0,
										'max' 	=> 200
									],
									'%' => [
										'min' => 0,
										'max' => 100
									],
								],
								'selectors' => [
									'{{WRAPPER}} .ova-testimonial.version-2 .slide-testimonials .button-nav i' => 'font-size: {{SIZE}}{{UNIT}};'
								]
							]
						);

						$this->add_control(
							'color_nav',
							[
								'label' 	=> esc_html__( 'Color', 'tripgo' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ova-testimonial.version-2 .slide-testimonials .button-nav i' => 'color : {{VALUE}};'
								]
							]
						);

						$this->add_control(
							'bg_color_nav',
							[
								'label' 	=> esc_html__( 'Background Color', 'tripgo' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ova-testimonial.version-2 .slide-testimonials .button-nav' => 'background-color : {{VALUE}};'
								]
							]
						);

					$this->end_controls_tab();

					$this->start_controls_tab(
						'nav_hover_tab',
						[
							'label' => esc_html__( 'Hover', 'tripgo' ),
						]
					);

						$this->add_control(
							'color_nav_hover',
							[
								'label' 	=> esc_html__( 'Color', 'tripgo' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ova-testimonial.version-2 .slide-testimonials .button-nav:hover i' => 'color : {{VALUE}};'
								]
							]
						);

						$this->add_control(
							'bg_color_nav_hover',
							[
								'label' 	=> esc_html__( 'Background', 'tripgo' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ova-testimonial.version-2 .slide-testimonials .button-nav:hover' => 'background-color : {{VALUE}};'
								],
							]
						);

					$this->end_controls_tab();
				$this->end_controls_tabs();
			$this->end_controls_section(); // END Navigation

			// Dots styles
			$this->start_controls_section(
			    'dot_styles',
			    [
				    'label' 	=> esc_html__( 'Dots', 'tripgo' ),
				    'tab' 		=> \Elementor\Controls_Manager::TAB_STYLE,
				    'condition' => [
				    	'dot_control' => 'yes'
				    ]
			    ]
		    );

		    	$this->add_control(
					'dot_size',
					[
						'type' 			=> \Elementor\Controls_Manager::SLIDER,
						'label' 		=> esc_html__( 'Size', 'tripgo' ),
						'size_units' 	=> [ 'px', 'em', 'rem', 'custom' ],
						'range' 		=> [
							'px' => [
								'min' 	=> 0,
								'max' 	=> 200
							]
						],
						'selectors' => [
							'{{WRAPPER}} .ova-testimonial.version-2 .slide-testimonials .button-dots .swiper-pagination-bullet' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
						]
					]
				);

				$this->add_control(
					'dot_space_between',
					[
						'type' 			=> \Elementor\Controls_Manager::SLIDER,
						'label' 		=> esc_html__( 'Space Between', 'tripgo' ),
						'size_units' 	=> [ 'px', 'em', 'rem', 'custom' ],
						'range' 		=> [
							'px' => [
								'min' 	=> 0,
								'max' 	=> 100
							]
						],
						'selectors' => [
							'{{WRAPPER}} .ova-testimonial.version-2 .slide-testimonials .button-dots' => 'gap: {{SIZE}}{{UNIT}};'
						]
					]
				);

				$this->start_controls_tabs(
					'dot_tabs'
				);

					$this->start_controls_tab(
						'dot_normal_tab',
						[
							'label' => esc_html__( 'Normal', 'tripgo' ),
						]
					);

						$this->add_control(
							'dot_color',
							[
								'label' 	=> esc_html__( 'Color', 'tripgo' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ova-testimonial.version-2 .slide-testimonials .button-dots .swiper-pagination-bullet' => 'background-color: {{VALUE}}',
								],
							]
						);

					$this->end_controls_tab();

					$this->start_controls_tab(
						'dot_active_tab',
						[
							'label' => esc_html__( 'Active', 'tripgo' ),
						]
					);

						$this->add_control(
							'dot_active_color',
							[
								'label' 	=> esc_html__( 'Color', 'tripgo' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ova-testimonial.version-2 .slide-testimonials .button-dots .swiper-pagination-bullet.swiper-pagination-bullet-active' => 'background-color: {{VALUE}}',
								],
							]
						);

					$this->end_controls_tab();

					$this->start_controls_tab(
						'dot_hover_tab',
						[
							'label' => esc_html__( 'Hover', 'tripgo' ),
						]
					);

						$this->add_control(
							'dot_hover_color',
							[
								'label' 	=> esc_html__( 'Color', 'tripgo' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ova-testimonial.version-2 .slide-testimonials .button-dots .swiper-pagination-bullet:hover' => 'background-color: {{VALUE}}',
								],
							]
						);

					$this->end_controls_tab();
				$this->end_controls_tabs();

				$this->add_control(
					'dot_margin',
					[
						'label' 		=> esc_html__( 'Margin', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-testimonial.version-2 .slide-testimonials .button-dots' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						]
					]
				);

		    $this->end_controls_section(); // END dot styles
		}

		/**
		 * Get rating
		 */
		protected function get_rating( $rating ) {
			$settings 		= $this->get_settings();
			$rating_scale 	= 5;

			return [ $rating, $rating_scale ];
		}

		/**
		 * Render stars
		 */
		protected function render_stars( $icon, $rating ) {
			$rating_data 	= $this->get_rating($rating);
			$rating 		= (float) $rating_data[0];
			$floored_rating = floor( $rating );
			$stars_html 	= '';

			for ( $stars = 1.0; $stars <= $rating_data[1]; $stars++ ) {
				if ( $stars <= $floored_rating ) {
					$stars_html .= '<i class="elementor-star-full">' . esc_html__( $icon ) . '</i>';
				} elseif ( $floored_rating + 1 === $stars && $rating !== $floored_rating ) {
					$stars_html .= '<i class="elementor-star-' . ( $rating - $floored_rating ) * 10 . '">' . esc_html__( $icon ) . '</i>';
				} else {
					$stars_html .= '<i class="elementor-star-empty">' . esc_html__( $icon ) . '</i>';
				}
			}

			return $stars_html;
		}

		/**
		 * Render template
		 */
		protected function render() {
			// Get settings
			$settings = $this->get_settings_for_display();

			// Get tab items
			$tab_item = tripgo_get_meta_data( 'tab_item', $settings );

			// Slide options
			$slide_options = [
				'slidesPerView' 		=> tripgo_get_meta_data( 'item_number', $settings ),
				'slidesPerGroup' 		=> tripgo_get_meta_data( 'slides_to_scroll', $settings ),
				'spaceBetween' 			=> tripgo_get_meta_data( 'margin_items', $settings ),
				'autoplay' 				=> 'yes' === tripgo_get_meta_data( 'autoplay', $settings ) ? true : false,
				'pauseOnMouseEnter' 	=> 'yes' === tripgo_get_meta_data( 'pause_on_hover', $settings ) ? true : false,
				'delay' 				=> tripgo_get_meta_data( 'autoplay_speed', $settings, 3000 ),
				'speed' 				=> tripgo_get_meta_data( 'smartspeed', $settings, 500 ),
				'loop' 					=> 'yes' === tripgo_get_meta_data( 'infinite', $settings ) ? true : false,
				'nav' 					=> 'yes' === tripgo_get_meta_data( 'nav_control', $settings ) ? true : false,
				'dots' 					=> 'yes' === tripgo_get_meta_data( 'dot_control', $settings ) ? true : false,
				'breakpoints' 			=> [
					'0' 	=> [
						'slidesPerView' => 1
					],
		        	'768' 	=> [
		        		'slidesPerView' => 1
		        	],
		        	'1024' 	=> [
		        		'slidesPerView' => tripgo_get_meta_data( 'item_number', $settings )
		        	]
				],
				'rtl' 					=> is_rtl() ? true: false
			];

			?>
			<section class="ova-testimonial version-2">
				<?php if ( tripgo_array_exists( $tab_item ) ): ?>
					<div class="slide-testimonials slide-testimonials-version-2" 
						data-options="<?php echo esc_attr( json_encode( $slide_options ) ); ?>"
					>
						<div class="swiper swiper-loading">
							<div class="swiper-wrapper">
								<?php foreach ( $tab_item as $item ):
									// Get testimonial
									$item_testimonial = tripgo_get_meta_data( 'testimonial', $item );

									// Image URL
									$img_url = isset( $item['image_author']['url'] ) && $item['image_author']['url'] ? $item['image_author']['url'] : '';

									// Name author
									$name_author = tripgo_get_meta_data( 'name_author', $item );

									// Link URL
									$link = isset( $item['link']['url'] ) && $item['link']['url'] ? $item['link']['url'] : '';

									// Target
									$target = isset( $item['link']['is_external'] ) && $item['link']['is_external'] ? '_blank' : '_self';

									// Nofollow
									$nofollow = isset( $item['link']['nofollow'] ) && $item['link']['nofollow'] ? 'nofollow' : '';

									// Job
									$job = tripgo_get_meta_data( 'job', $item );

									// Icon
									$icon = '&#xE934;';

									// Get rating
									$rating = (float)tripgo_get_meta_data( 'rating', $item );

									// Get rating data
									$rating_data = $this->get_rating( $rating );

									// Textual rating
									$textual_rating = tripgo_get_meta_data( 0, $rating_data ) . '/' . tripgo_get_meta_data( 1, $rating_data );

									// Unmarked star style
									$unmarked_star_style = tripgo_get_meta_data( 'unmarked_star_style', $item );

									// Star style
									$star_style = tripgo_get_meta_data( 'star_style', $item );
									if ( 'star_fontawesome' === $star_style ) {
										if ( 'outline' === $unmarked_star_style ) {
											$icon = '&#xE933;';
										}
									} elseif ( 'star_unicode' === $star_style ) {
										$icon = '&#9733;';
							
										if ( 'outline' === $unmarked_star_style ) {
											$icon = '&#9734;';
										}
									}

									$stars_element = '<div class="elementor-star-rating" title="'. esc_attr( $textual_rating ) .'">' . wp_kses_post( $this->render_stars( $icon, $rating ) ) . ' </div>';
								?>
									<div class="item swiper-slide">
										<?php if ( $item_testimonial ): ?>
											<p class="evaluate">
												<?php echo esc_html( $item_testimonial ); ?>
											</p>
										<?php endif; ?>
										<div class="info-content">
											<div class="client-info">
												<?php if ( $img_url ):
													$alt = $name_author ? $name_author : esc_html__( 'Testimonial','tripgo' );  
												?>
													<div class="client">
														<?php if ( $link ): ?>
															<a href="<?php echo esc_url( $link ); ?>" target="<?php echo esc_attr( $target ); ?>" rel="<?php echo esc_attr( $nofollow ); ?>">
																<img src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $alt ); ?>">
															</a>
														<?php else: ?>
															<img src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $alt ); ?>">
														<?php endif; ?>
													</div>
												<?php endif; ?>
												<div class="name-job">
													<?php if ( $link ): 
														if ( $name_author ): ?>
															<h6 class="name second_font">
																<a href="<?php echo esc_url( $link ); ?>" target="<?php echo esc_attr( $target ); ?>" rel="<?php echo esc_attr( $nofollow ); ?>">
																	<?php echo esc_html( $name_author ); ?>
																</a>
															</h6>	
														<?php endif; 
													else:
														if ( $name_author ): ?>
															<h6 class="name">
																<?php echo esc_html( $name_author ); ?>
															</h6>
														<?php endif;
													endif;

													// Job
													if ( $job ): ?>
														<p class="job">
															<?php echo esc_html( $job ); ?>
														</p>
													<?php endif; ?>
												</div>
											</div>
											<?php if ( 0 != $rating ): ?>
												<div class="ova-rating <?php echo esc_html( $star_style ); ?>">
													<?php echo wp_kses_post( $stars_element ); ?>
												</div>
											<?php endif; ?>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
						<?php if ( 'yes' === tripgo_get_meta_data( 'nav_control', $settings ) ): ?>
							<div class="button-nav button-prev">
								<i class="arrow_carrot-left" aria-hidden="true"></i>
							</div>
							<div class="button-nav button-next">
								<i class="arrow_carrot-right" aria-hidden="true"></i>
							</div>
						<?php endif; ?>
						<?php if ( 'yes' === tripgo_get_meta_data( 'dot_control', $settings ) ): ?>
							<div class="button-dots"></div>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</section>
			<?php
		}
	}

	// init widget
	$widgets_manager->register( new Tripgo_Elementor_Testimonial_2() );
}