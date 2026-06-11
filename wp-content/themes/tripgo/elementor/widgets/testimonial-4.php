<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class Tripgo_Elementor_Testimonial_4
 */
if ( !class_exists( 'Tripgo_Elementor_Testimonial_4', false ) ) {

	class Tripgo_Elementor_Testimonial_4 extends \Elementor\Widget_Base {

		/**
		 * Get widget name
		 */
		public function get_name() {
			return 'tripgo_elementor_testimonial_4';
		}

		/**
		 * Get widget title
		 */
		public function get_title() {
			return esc_html__( 'Ova Testimonial 4', 'tripgo' );
		}

		/**
		 * Get widget icon
		 */
		public function get_icon() {
			return 'eicon-testimonial-carousel';
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
			return [ 'swiper', 'tripgo-elementor-testimonial-4' ];
		}

		/**
		 * Get script depends
		 */
		public function get_script_depends() {
			return [ 'swiper', 'tripgo-elementor-testimonial-4' ];
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
					'template',
					[
						'label' 	=> esc_html__( 'Template', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::SELECT,
						'default' 	=> 'template1',
						'options' 	=> [
							'template1' => esc_html__('Template 1', 'tripgo'),
							'template2' => esc_html__('Template 2', 'tripgo'),
						]
					]
				);

				$this->add_control(
					'quote',
					[
						'label' 	=> esc_html__( 'Quote', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::ICONS,
						'default' 	=> [
							'value' 	=> 'ovaicon ovaicon-right-quote',
							'library' 	=> 'ovaicon',
						],
					]
				);

				$this->add_control(
					'show_rating',
					[
						'label' 		=> esc_html__( 'Show Rating', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 		=> esc_html__( 'Show', 'tripgo' ),
						'label_off' 	=> esc_html__( 'Hide', 'tripgo' ),
						'return_value' 	=> 'yes',
						'default' 		=> 'yes',
					]
				);

				$repeater = new \Elementor\Repeater();

				$repeater->add_control(
					'evaluate',
					[
						'label' 		=> esc_html__( 'Evaluate', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::TEXT,
						'default' 		=> esc_html__( 'Good Experience', 'tripgo' ),
						'placeholder' 	=> esc_html__( 'Type your evaluate here', 'tripgo' ),
					]
				);

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
						'default' 	=> 'solid',
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
						'default' => esc_html__( 'OMG! I cannot believe that I have got a brand new landing page after getting appmax. It was super easy to edit and publish.I have got a brand new landing page.', 'tripgo' ),
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
								'name_author' 	=> esc_html__('Mila McSabbu', 'tripgo'),
								'job' 			=> esc_html__('Freelance Designer', 'tripgo'),
							],
							[
								'name_author' 	=> esc_html__('Henry K. Melendez', 'tripgo'),
								'job' 			=> esc_html__('Freelance Designer', 'tripgo'),
							],
							[
								'name_author' 	=> esc_html__('Somalia D. Silva', 'tripgo'),
								'job' 			=> esc_html__('Freelance Designer', 'tripgo'),
							],
							[
								'name_author' 	=> esc_html__('Pacific D. Lee', 'tripgo'),
								'job' 			=> esc_html__('Freelance Designer', 'tripgo'),
							],
						],
						'title_field' => '{{{ name_author }}}',
					]
				);
				
			$this->end_controls_section(); // END SECTION CONTENT

			// START SECTION ADDITIONAL
			$this->start_controls_section(
				'section_additional_options',
				[
					'label' => esc_html__( 'Additional Options', 'tripgo' ),
				]
			);

				$this->add_control(
					'margin_items',
					[
						'label'   	=> esc_html__( 'Margin Right Items', 'tripgo' ),
						'type'    	=> \Elementor\Controls_Manager::NUMBER,
						'default' 	=> 24,
						'condition' => [
							'template' => 'template1',
						],
					]
				);

				$this->add_control(
					'margin_items_v2',
					[
						'label'   	=> esc_html__( 'Margin Right Items', 'tripgo' ),
						'type'    	=> \Elementor\Controls_Manager::NUMBER,
						'default' 	=> 0,
						'condition' => [
							'template' => 'template2',
						],
					]
				); 

				$this->add_control(
					'item_number',
					[
						'label'       	=> esc_html__( 'Item Number', 'tripgo' ),
						'type'        	=> \Elementor\Controls_Manager::NUMBER,
						'description' 	=> esc_html__( 'Number Item', 'tripgo' ),
						'default'     	=> 3,
						'condition' 	=> [
							'template' 	=> 'template1',
						],
					]
				);

				$this->add_control(
					'item_number_v2',
					[
						'label'       	=> esc_html__( 'Item Number', 'tripgo' ),
						'type'        	=> \Elementor\Controls_Manager::NUMBER,
						'description' 	=> esc_html__( 'Number Item', 'tripgo' ),
						'default'     	=> 1,
						'condition' 	=> [
							'template' 	=> 'template2',
						],
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
					'center_mode',
					[
						'label'   => esc_html__( 'Center Mode', 'tripgo' ),
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

			$this->end_controls_section(); // END SECTION ADDITIONAL

			$this->start_controls_section(
				'section_item_style',
				[
					'label' => esc_html__( 'Item', 'tripgo' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_control(
					'item_background_color',
					[
						'label' 	=> esc_html__( 'Background Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-testimonial-4 .slide-testimonials .item' => 'background-color: {{VALUE}}',
						],
					]
				);

				$this->add_responsive_control(
					'item_padding',
					[
						'label' 		=> esc_html__( 'Padding', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-testimonial-4 .slide-testimonials .item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Border::get_type(),
					[
						'name' 		=> 'item_border',
						'selector' 	=> '{{WRAPPER}} .ova-testimonial-4 .slide-testimonials .item',
					]
				);

				$this->add_control(
					'item_border_color_center',
					[
						'label' 	=> esc_html__( 'Border Color Center', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-testimonial-4.ova-testimonial-template1 .item' => 'border-color: {{VALUE}}',
						],
						'condition' => [
							'template' => 'template1',
						],
					]
				);

				$this->add_control(
					'item_border_radius',
					[
						'label' 		=> esc_html__( 'Border Radius', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-testimonial-4 .slide-testimonials .item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Box_Shadow::get_type(),
					[
						'name' 		=> 'item_box_shadow',
						'selector' 	=> '{{WRAPPER}} .ova-testimonial-4 .slide-testimonials .item ',
					]
				);

			$this->end_controls_section();

			$this->start_controls_section(
				'section_rating',
				[
					'label' => esc_html__( 'Rating', 'tripgo' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_responsive_control(
					'rating_size',
					[
						'label' 		=> esc_html__( 'Size', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::SLIDER,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'range' 		=> [
							'px' => [
								'min' 	=> 0,
								'max' 	=> 100,
								'step' 	=> 1,
							],
							'%' => [
								'min' => 0,
								'max' => 100,
							],
						],
						'selectors' => [
							'{{WRAPPER}} .ova-testimonial-4 .slide-testimonials .item .rating i' => 'font-size: {{SIZE}}{{UNIT}};',
						],
					]
				);

				$this->add_responsive_control(
					'rating_spacing',
					[
						'label' 		=> esc_html__( 'Spacing', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::SLIDER,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'range' 		=> [
							'px' => [
								'min' => 0,
								'max' => 100,
								'step' => 1,
							],
							'%' => [
								'min' => 0,
								'max' => 100,
							],
						],
						'selectors' => [
							'{{WRAPPER}} .ova-testimonial-4 .slide-testimonials .item .rating i' => 'margin-right: {{SIZE}}{{UNIT}};',
						],
					]
				);

				$this->add_control(
					'rating_color',
					[
						'label' 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-testimonial-4 .slide-testimonials .item .wrap-evaluate .evaluate .rating .elementor-star-rating .elementor-star-full::before' => 'color: {{VALUE}}',
						],
					]
				);

				$this->add_responsive_control(
					'rating_margin',
					[
						'label' 		=> esc_html__( 'Margin', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-testimonial-4 .item .rating' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

			$this->end_controls_section();

			$this->start_controls_section(
				'section_quote_style',
				[
					'label' => esc_html__( 'Quote', 'tripgo' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_responsive_control(
					'quote_size',
					[
						'label' 		=> esc_html__( 'Size', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::SLIDER,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'range' 		=> [
							'px' => [
								'min' 	=> 0,
								'max' 	=> 500,
								'step' 	=> 1,
							],
							'%' => [
								'min' => 0,
								'max' => 100,
							],
						],
						'selectors' => [
							'{{WRAPPER}} .ova-testimonial-4 .item .quote i' => 'font-size: {{SIZE}}{{UNIT}};',
						],
					]
				);

				$this->add_control(
					'quote_color',
					[
						'label' 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-testimonial-4 .item .quote i' => 'color: {{VALUE}}',
						],
					]
				);

				$this->add_responsive_control(
					'quote_position_margin',
					[
						'label'      => esc_html__( 'Margin', 'tripgo' ),
						'type'       => \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors'  => [
							'{{WRAPPER}} .ova-testimonial-4 .item .quote' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

			$this->end_controls_section();

			// SECTION content testimonial
			$this->start_controls_section(
				'section_content_testimonial',
				[
					'label' => esc_html__( 'Content', 'tripgo' ),
					'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name'     => 'content_testimonial_typography',
						'selector' => '{{WRAPPER}} .ova-testimonial-4 .slide-testimonials .item .content',
					]
				);

				$this->add_control(
					'content_color',
					[
						'label'     => esc_html__( 'Color', 'tripgo' ),
						'type'      => \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-testimonial-4 .slide-testimonials .item .content' => 'color : {{VALUE}};',
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
							'{{WRAPPER}} .ova-testimonial-4 .slide-testimonials .item .content' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

			$this->end_controls_section();

			$this->start_controls_section(
				'section_author_style',
				[
					'label' => esc_html__( 'Image', 'tripgo' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_responsive_control(
					'author_image_size',
					[
						'label' 		=> esc_html__( 'Size', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::SLIDER,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'range' 		=> [
							'px' => [
								'min' 	=> 0,
								'max' 	=> 500,
								'step' 	=> 1,
							],
							'%' => [
								'min' => 0,
								'max' => 100,
							],
						],
						'selectors' => [
							'{{WRAPPER}} .ova-testimonial-4 .slide-testimonials .item .info .author-image img' => 'width: {{SIZE}}{{UNIT}};min-width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
						],
					]
				);

				$this->add_control(
					'author_image_border_radius',
					[
						'label' 		=> esc_html__( 'Border Radius', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-testimonial-4 .slide-testimonials .item .info .author-image img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_responsive_control(
					'author_image_margin',
					[
						'label' 		=> esc_html__( 'Margin', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-testimonial-4 .slide-testimonials .item .info .author-image' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

			$this->end_controls_section();

			$this->start_controls_section(
				'section_name',
				[
					'label' => esc_html__( 'Name', 'tripgo' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'name_typography',
						'selector' 	=> '{{WRAPPER}} .ova-testimonial-4 .slide-testimonials .item .info .name-job .name',
					]
				);

				$this->add_control(
					'name_color',
					[
						'label' 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-testimonial-4 .slide-testimonials .item .info .name-job .name' => 'color: {{VALUE}}',
						],
					]
				);

				$this->add_responsive_control(
					'name_margin',
					[
						'label' 		=> esc_html__( 'Margin', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-testimonial-4 .slide-testimonials .item .info .name-job .name' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

			$this->end_controls_section();

			$this->start_controls_section(
				'section_job',
				[
					'label' => esc_html__( 'Job', 'tripgo' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'job_typography',
						'selector' 	=> '{{WRAPPER}} .ova-testimonial-4 .slide-testimonials .item .info .name-job .job',
					]
				);

				$this->add_control(
					'job_color',
					[
						'label' 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-testimonial-4 .slide-testimonials .item .info .name-job .job' => 'color: {{VALUE}}',
						],
					]
				);

				$this->add_responsive_control(
					'job_margin',
					[
						'label' 		=> esc_html__( 'Margin', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-testimonial-4 .slide-testimonials .item .info .name-job .job' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
							'{{WRAPPER}} .ova-testimonial-4 .slide-testimonials .button-nav' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};'
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
							'{{WRAPPER}} .ova-testimonial-4 .slide-testimonials .button-nav.button-prev' => 'left: {{SIZE}}{{UNIT}};',
							'{{WRAPPER}} .ova-testimonial-4 .slide-testimonials .button-nav.button-next' => 'right: {{SIZE}}{{UNIT}};',
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
									'{{WRAPPER}} .ova-testimonial-4 .slide-testimonials .button-nav i' => 'font-size: {{SIZE}}{{UNIT}};'
								]
							]
						);

						$this->add_control(
							'color_nav',
							[
								'label' 	=> esc_html__( 'Color', 'tripgo' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ova-testimonial-4 .slide-testimonials .button-nav i' => 'color : {{VALUE}};'
								]
							]
						);

						$this->add_control(
							'bg_color_nav',
							[
								'label' 	=> esc_html__( 'Background Color', 'tripgo' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ova-testimonial-4 .slide-testimonials .button-nav' => 'background-color : {{VALUE}};'
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
									'{{WRAPPER}} .ova-testimonial-4 .slide-testimonials .button-nav:hover i' => 'color : {{VALUE}};'
								]
							]
						);

						$this->add_control(
							'bg_color_nav_hover',
							[
								'label' 	=> esc_html__( 'Background', 'tripgo' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ova-testimonial-4 .slide-testimonials .button-nav:hover' => 'background-color : {{VALUE}};'
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
							'{{WRAPPER}} .ova-testimonial-4 .slide-testimonials .button-dots .swiper-pagination-bullet' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
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
							'{{WRAPPER}} .ova-testimonial-4 .slide-testimonials .button-dots' => 'gap: {{SIZE}}{{UNIT}};'
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
									'{{WRAPPER}} .ova-testimonial-4 .slide-testimonials .button-dots .swiper-pagination-bullet' => 'background-color: {{VALUE}}',
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
									'{{WRAPPER}} .ova-testimonial-4 .slide-testimonials .button-dots .swiper-pagination-bullet.swiper-pagination-bullet-active' => 'background-color: {{VALUE}}',
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
									'{{WRAPPER}} .ova-testimonial-4 .slide-testimonials .button-dots .swiper-pagination-bullet:hover' => 'background-color: {{VALUE}}',
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
							'{{WRAPPER}} .ova-testimonial-4 .slide-testimonials .button-dots' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						]
					]
				);

		    $this->end_controls_section(); // END dot styles
		}

		/**
		 * Get rating
		 */
		protected function get_rating( $rating ) {
			$settings 		= $this->get_settings_for_display();
			$rating_scale 	= 5;

			return [ $rating, $rating_scale ];
		}

		/**
		 * Render stars
		 */
		protected function render_stars( $icon, $rating ) {
			$rating_data 	= $this->get_rating( $rating );
			$rating 		= (float)$rating_data[0];
			$floored_rating = floor( $rating );
			$stars_html 	= '';

			for ( $stars = 1.0; $stars <= $rating_data[1]; $stars++ ) {
				if ( $stars <= $floored_rating ) {
					$stars_html .= '<i class="elementor-star-full">' . esc_html( $icon ) . '</i>';
				} elseif ( $floored_rating + 1 === $stars && $rating !== $floored_rating ) {
					$stars_html .= '<i class="elementor-star-' . ( $rating - $floored_rating ) * 10 . '">' . esc_html( $icon ) . '</i>';
				} else {
					$stars_html .= '<i class="elementor-star-empty">' . esc_html( $icon ) . '</i>';
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

			// Get template
			$template = tripgo_get_meta_data( 'template', $settings );

			// Get quote
			$quote = tripgo_get_meta_data( 'quote', $settings );

			// Get tab item
			$tab_item = tripgo_get_meta_data( 'tab_item', $settings );

			// Show rating
			$show_rating = tripgo_get_meta_data( 'show_rating', $settings );

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
		        		'slidesPerView' => 2
		        	],
		        	'1150' 	=> [
		        		'slidesPerView' => tripgo_get_meta_data( 'item_number', $settings )
		        	]
				],
				'rtl' 					=> is_rtl() ? true: false
			];

			if ( 'template2' === $template ) {
				$slide_options['slidesPerView'] = tripgo_get_meta_data( 'item_number_v2', $settings );
				$slide_options['spaceBetween'] 	= tripgo_get_meta_data( 'margin_items_v2', $settings );
				$slide_options['breakpoints'] 	= [
					'0' 	=> [
						'slidesPerView' => 1
					],
		        	'768' 	=> [
		        		'slidesPerView' => 1
		        	],
		        	'1150' 	=> [
		        		'slidesPerView' => tripgo_get_meta_data( 'item_number_v2', $settings )
		        	]
				];
			}

			?>
			<div class="ova-testimonial-4 ova-testimonial-<?php echo esc_attr( $template ); ?>">
				<?php if ( tripgo_array_exists( $tab_item ) ): ?>
					<div class="slide-testimonials" data-options="<?php echo esc_attr( json_encode( $slide_options ) ); ?>">
						<div class="swiper swiper-loading">
							<div class="swiper-wrapper">
								<?php foreach ( $tab_item as $item ):
									// Get evaluate
									$evaluate = tripgo_get_meta_data( 'evaluate', $item );

									// Get testimonial
									$testimonial = tripgo_get_meta_data( 'testimonial', $item );

									// Name author
									$name_author = tripgo_get_meta_data( 'name_author', $item );

									// Image author
									$image_author = isset( $item['image_author']['url'] ) && $item['image_author']['url'] ? $item['image_author']['url'] : '';

									// Image alt
									$image_alt = $name_author ? $name_author : esc_html__( 'Testimonial','tripgo' );

									// Get job
									$job = tripgo_get_meta_data( 'job', $item );
								?>
									<div class="item swiper-slide">
										<div class="wrap-evaluate">
											<div class="evaluate">
												<?php if ( $evaluate ): ?>
													<p class="text">
														<?php echo esc_html( $evaluate ); ?>
													</p>
												<?php endif;

												if ( 'yes' === $show_rating ):
													// init icon
													$icon = '&#xE934;';

													// Get rating
													$rating = (float)tripgo_get_meta_data( 'rating', $item );

													// Rating data
													$rating_data = $this->get_rating( $rating );

													// Textual rating
													$textual_rating = $rating_data[0] . '/' . $rating_data[1];

													// Unmarker star style
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

													$stars_element = '<div class="elementor-star-rating" title="'.$textual_rating.'">' . wp_kses_post( $this->render_stars( $icon, $item['rating'] ) ) . '</div>';

													if ( 0 != $rating ): ?>
														<div class="rating <?php echo wp_kses_post( $star_style ); ?>">
															<?php echo wp_kses_post( $stars_element ); ?>
														</div>
													<?php endif;
												endif; ?>
											</div>
				                            <?php if ( !empty( $quote['value'] ) ): ?>
					                            <span class="quote">
					                            	<?php \Elementor\Icons_Manager::render_icon( $quote, [ 'aria-hidden' => 'true' ] ); ?>
					                            </span>
											<?php endif; ?>
										</div>
										<?php if ( $testimonial ): ?>
											<p class="content">
												<?php echo esc_html( $testimonial ); ?>
											</p>
										<?php endif; ?>
										<div class="info">
											<?php if ( $image_author ): ?>
												<div class="author-image">
													<img src="<?php echo esc_url( $image_author ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>">
												</div>
											<?php endif; ?>
											<div class="name-job">
												<?php if ( $name_author ): ?>
													<p class="name">
														<?php echo esc_html( $name_author ); ?>
													</p>
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
			</div>
			<?php
		}
	}

	// init widget
	$widgets_manager->register( new Tripgo_Elementor_Testimonial_4() );
}