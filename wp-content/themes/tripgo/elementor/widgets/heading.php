<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class Tripgo_Elementor_Heading
 */
if ( !class_exists( 'Tripgo_Elementor_Heading', false ) ) {

	class Tripgo_Elementor_Heading extends \Elementor\Widget_Base {

		/**
		 * Get widget name
		 */
		public function get_name() {
			return 'tripgo_elementor_heading';
		}

		/**
		 * Get widget title
		 */
		public function get_title() {
			return esc_html__( 'Ova Heading', 'tripgo' );
		}

		/**
		 * Get widget icon
		 */
		public function get_icon() {
			return 'eicon-heading';
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
			return [ 'tripgo-elementor-heading' ];
		}
		
		/**
		 * Register controls
		 */
		protected function register_controls() {

			$this->start_controls_section(
				'section_content',
				[
					'label' => esc_html__( 'Content', 'tripgo' ),
				]
			);

				$this->add_control(
					'sub_title',
					[
						'label' 	=> esc_html__( 'Sub Title', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::TEXT,
						'default' 	=> 'Sub title'
					]
				);

				$this->add_control(
					'title',
					[
						'label' 	=> esc_html__( 'Title', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::TEXTAREA,
						'default' 	=> 'Title'
					]
				);

				$this->add_control(
					'description',
					[
						'label' 	=> esc_html__( 'Description', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::TEXTAREA,
						'default' 	=> ''
					]
				);

				$this->add_control(
					'link_address',
					[
						'label'   		=> esc_html__( 'Link', 'tripgo' ),
						'type'    		=> \Elementor\Controls_Manager::URL,
						'show_external' => false,
						'default' 		=> [
							'url' 			=> '',
							'is_external' 	=> false,
							'nofollow' 		=> false,
						],
					]
				);
				
				$this->add_control(
					'html_tag',
					[
						'label' 	=> esc_html__( 'HTML Tag', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::SELECT,
						'default' 	=> 'h2',
						'options' 	=> [
							'h1' 		=> esc_html__( 'H1', 'tripgo' ),
							'h2'  		=> esc_html__( 'H2', 'tripgo' ),
							'h3'  		=> esc_html__( 'H3', 'tripgo' ),
							'h4' 		=> esc_html__( 'H4', 'tripgo' ),
							'h5' 		=> esc_html__( 'H5', 'tripgo' ),
							'h6' 		=> esc_html__( 'H6', 'tripgo' ),
							'div' 		=> esc_html__( 'Div', 'tripgo' ),
							'span' 		=> esc_html__( 'span', 'tripgo' ),
							'p' 		=> esc_html__( 'p', 'tripgo' )
						],
					]
				);

				$this->add_responsive_control(
					'align_heading',
					[
						'label' 	=> esc_html__( 'Alignment', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::CHOOSE,
						'options' 	=> [
							'left' 	=> [
								'title' => esc_html__( 'Left', 'tripgo' ),
								'icon' 	=> 'eicon-text-align-left',
							],
							'center' => [
								'title' => esc_html__( 'Center', 'tripgo' ),
								'icon' 	=> 'eicon-text-align-center',
							],
							'right' => [
								'title' => esc_html__( 'Right', 'tripgo' ),
								'icon' 	=> 'eicon-text-align-right',
							],
						],
						'default' 	=> 'center',
						'toggle' 	=> true,
						'selectors' => [
							'{{WRAPPER}} .ova-title' => 'text-align: {{VALUE}}',
						],
					]
				);

			$this->end_controls_section();

			// SECTION TAB STYLE TITLE
			$this->start_controls_section(
				'section_title_style',
				[
					'label' => esc_html__( 'Title', 'tripgo' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
				]
			);
				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'content_typography_title',
						'label' 	=> esc_html__( 'Typography', 'tripgo' ),
						'selector' 	=> '{{WRAPPER}} .ova-title .title',
					]
				);

				$this->add_control(
					'color_title',
					[
						'label' 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-title .title' => 'color : {{VALUE}};',
							'{{WRAPPER}} .ova-title .title a' => 'color : {{VALUE}};',	
						],
					]
				);

				$this->add_control(
					'color_title_hover',
					[
						'label' 	=> esc_html__( 'Color hover', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-title .title a:hover' => 'color : {{VALUE}};'
						],
					]
				);

				$this->add_control(
					'bgcolor_title',
					[
						'label' 	=> esc_html__( 'Background Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-title .title' => 'background-color : {{VALUE}};',
						],
					]
				);

				$this->add_responsive_control(
					'padding_title',
					[
						'label' 	 => esc_html__( 'Padding', 'tripgo' ),
						'type' 		 => \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors'  => [
							'{{WRAPPER}} .ova-title .title ' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_responsive_control(
					'margin_title',
					[
						'label' 	 => esc_html__( 'Margin', 'tripgo' ),
						'type' 		 => \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors'  => [
							'{{WRAPPER}} .ova-title .title ' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

			$this->end_controls_section(); // END SECTION TAB STYLE TITLE

			// SECTION TAB STYLE SUB TITLE
			$this->start_controls_section(
				'section_sub_title',
				[
					'label' => esc_html__( 'Sub Title', 'tripgo' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'content_typography_sub_title',
						'label' 	=> esc_html__( 'Typography', 'tripgo' ),
						'selector' 	=> '{{WRAPPER}} .ova-title h3.sub-title',
					]
				);

				$this->add_control(
					'sub_title_font_family',
					[
						'label' 	=> esc_html__( 'Font Family', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::FONT,
						'default' 	=> 'La Belle Aurore',
						'selectors' => [
							'{{WRAPPER}} .ova-title .sub-title' => 'font-family: {{VALUE}}',
						],
					]
				);

				$this->add_control(
					'color_sub_title',
					[
						'label'	 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-title h3.sub-title' => 'color : {{VALUE}};'
							
							
						],
					]
				);

				$this->add_responsive_control(
					'padding_sub_title',
					[
						'label' 	 => esc_html__( 'Padding', 'tripgo' ),
						'type' 		 => \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors'  => [
							'{{WRAPPER}} .ova-title h3.sub-title ' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_responsive_control(
					'margin_sub_title',
					[
						'label' 	 => esc_html__( 'Margin', 'tripgo' ),
						'type' 		 => \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors'  => [
							'{{WRAPPER}} .ova-title h3.sub-title ' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);
				
			$this->end_controls_section(); // END SECTION TAB STYLE SUB TITLE

			// SECTION TAB STYLE DESCRIPTION
			$this->start_controls_section(
				'section_description',
				[
					'label' => esc_html__( 'Description', 'tripgo' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'content_typography_description',
						'label' 	=> esc_html__( 'Typography', 'tripgo' ),
						'selector' 	=> '{{WRAPPER}} .ova-title .description',
					]
				);

				$this->add_control(
					'color_description',
					[
						'label'	 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-title .description' => 'color : {{VALUE}};'		
						],
					]
				);

				$this->add_responsive_control(
					'padding_description',
					[
						'label' 	 => esc_html__( 'Padding', 'tripgo' ),
						'type' 		 => \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors'  => [
							'{{WRAPPER}} .ova-title .description ' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_responsive_control(
					'margin_description',
					[
						'label' 	 => esc_html__( 'Margin', 'tripgo' ),
						'type' 		 => \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors'  => [
							'{{WRAPPER}} .ova-title .description ' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);
				
			$this->end_controls_section(); // END SECTION TAB STYLE DESCRIPTION
		}

		/**
		 * Render HTML
		 */
		protected function render() {
			// Get settings
			$settings = $this->get_settings_for_display();

			// Get title
			$title = tripgo_get_meta_data( 'title', $settings );

			// Get sub-title
			$sub_title = tripgo_get_meta_data( 'sub_title', $settings );

			// Get description
			$description = tripgo_get_meta_data( 'description', $settings );

			// Get link 
			$link = isset( $settings['link_address']['url'] ) ? $settings['link_address']['url'] : '';

			// Target
			$target = isset( $settings['link_address']['is_external'] ) && $settings['link_address']['is_external'] ? '_blank' : '_self';

			// HTML tag
			$html_tag = tripgo_get_meta_data( 'html_tag', $settings, 'h2' );

			?>
			<div class="ova-title">
				<?php if ( $sub_title ): ?>
					<h3 class="sub-title"><?php echo esc_html( $sub_title ); ?></h3>
				<?php endif;

				// Title
				if ( $title ):
					if ( $link ): ?>
						<<?php echo esc_attr( $html_tag ); ?> class="title">
							<a href="<?php echo esc_url( $link ); ?>" target="<?php echo esc_attr( $target ); ?>">
								<?php echo esc_html( $title ); ?>
							</a>
						</<?php echo esc_attr( $html_tag ); ?>>
					<?php else: ?>
						<<?php echo esc_attr( $html_tag ); ?> class="title">
							<?php echo esc_html( $title ); ?>
						</<?php echo esc_attr( $html_tag ); ?>>
					<?php endif;
				endif;

				// Description
				if ( $description ): ?>
					<p class="description"><?php echo esc_html( $description ); ?></p>
				<?php endif; ?>
			</div>
			<?php
		}
	}

	// init widget
	$widgets_manager->register( new Tripgo_Elementor_Heading() );
}