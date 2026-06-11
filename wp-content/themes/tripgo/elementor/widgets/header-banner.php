<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class Tripgo_Elementor_Header_Banner
 */
if ( !class_exists( 'Tripgo_Elementor_Header_Banner', false ) ) {

	class Tripgo_Elementor_Header_Banner extends \Elementor\Widget_Base {

		/**
		 * Get widget name
		 */
		public function get_name() {
			return 'tripgo_elementor_header_banner';
		}

		/**
		 * Get widget title
		 */
		public function get_title() {
			return esc_html__( 'Header Banner', 'tripgo' );
		}

		/**
		 * Get widget icon
		 */
		public function get_icon() {
			return 'eicon-archive-title';
		}

		/**
		 * Get widget categories
		 */
		public function get_categories() {
			return [ 'hf' ];
		}

		/**
		 * Get style depends
		 */
		public function get_style_depends() {
			return [ 'tripgo-elementor-header-banner' ];
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
					'header_boxed_content',
					[
						'label' 	=> esc_html__( 'Display Boxed Content', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::SWITCHER,
						'default' 	=> 'no'
					]
				);

				$this->add_control(
					'header_bg_source',
					[
						'label' 	=> esc_html__( 'Display Background by Feature Image in Post/Page', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::SWITCHER,
						'default' 	=> 'no'
					]
				);

				$this->add_control(
					'cover_color',
					[
						'label' 		=> esc_html__( 'Background Cover Color', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::COLOR,
						'default' 		=> 'rgba(0,0,0,0.51)',
						'description' 	=> esc_html__( 'You can add background image in Advanced Tab', 'tripgo' ),
						'selectors' 	=> [
							'{{WRAPPER}} .cover_color' => 'background-color: {{VALUE}};',
						],
						'separator' 	=> 'after'
					]
				);

				// Title
				$this->add_control(
					'show_title',
					[
						'label' 	=> esc_html__( 'Show Title', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::SWITCHER,
						'default' 	=> 'yes',
						'selector'	=> '{{WRAPPER}} .header_banner_el .header_title',
					]
				);
				
				$this->add_control(
					'title_color',
					[
						'label' 	=> esc_html__( 'Title Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'default' 	=> '#343434',
						'selectors' => [
							'{{WRAPPER}} .header_banner_el .header_title' => 'color: {{VALUE}};',
						]
					]
				);

				$this->add_responsive_control(
					'title_padding',
					[
						'label' 		=> esc_html__( 'Title Padding', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .header_banner_el .header_title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_control(
					'title_tag',
					[
						'label' 	=> esc_html__( 'Choose Title Format', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::SELECT,
						'options' 	=> [
							'h1' 	=> esc_html__( 'H1', 'tripgo' ),
							'h2' 	=> esc_html__( 'H2', 'tripgo' ),
							'h3' 	=> esc_html__( 'H3', 'tripgo' ),
							'h4' 	=> esc_html__( 'H4', 'tripgo' ),
							'h5' 	=> esc_html__( 'H5', 'tripgo' ),
							'h6' 	=> esc_html__( 'H6', 'tripgo' ),
							'div' 	=> esc_html__( 'DIV', 'tripgo' ),
						],
						'default' => 'h1'
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'header_title',
						'label' 	=> esc_html__( 'Title Typo', 'tripgo' ),
						'selector'	=> '{{WRAPPER}} .header_banner_el .header_title'
					]
				);


				// Breadcrumbs
				$this->add_control(
					'show_breadcrumbs',
					[
						'label' 	=> esc_html__( 'Show Breadcrumbs', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::SWITCHER,
						'default' 	=> 'yes',
						'selector'	=> '{{WRAPPER}} .header_breadcrumbs',
						'separator' => 'before'
					]
				);
				
				$this->add_control(
					'breadcrumbs_color',
					[
						'label' 	=> esc_html__( 'Breadcrumbs Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'default' 	=> '#343434',
						'selectors' => [
							'{{WRAPPER}} .header_banner_el ul.breadcrumb li' => 'color: {{VALUE}};',
							'{{WRAPPER}} .header_banner_el ul.breadcrumb li a' => 'color: {{VALUE}};',
							'{{WRAPPER}} .header_banner_el ul.breadcrumb a' => 'color: {{VALUE}};',
							'{{WRAPPER}} .header_banner_el ul.breadcrumb li .separator i' => 'color: {{VALUE}};',
						]
					]
				);
				$this->add_control(
					'breadcrumbs_current_color',
					[
						'label' 	=> esc_html__( 'Breadcrumbs Last Child Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .header_banner_el ul.breadcrumb li:last-child' => 'color: {{VALUE}};',
						]
					]
				);

				$this->add_control(
					'breadcrumbs_end_opacity',
					[
						'label' 	=> esc_html__( 'Breadcrumbs Current Color Opacity', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::SLIDER,
						'default' 	=> [
							'size' 	=> 0.9,
						],
						'range' 	=> [
							'px' => [
								'max' 	=> 1,
								'step' 	=> 0.01,
							],
						],
						'selectors' => [
							'{{WRAPPER}} .header_banner_el ul.breadcrumb li:not(:first-child)' => 'opacity: {{SIZE}};',
							
						],
						
					]
				);

				$this->add_control(
					'breadcrumbs_color_hover',
					[
						'label' 	=> esc_html__( 'Breadcrumbs Color Hover', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'default' 	=> '#343434',
						'selectors' => [
							'{{WRAPPER}} .header_banner_el ul.breadcrumb li a:hover' => 'color: {{VALUE}};',
						]
					]
				);

				$this->add_control(
					'breadcrumbs_opacity_hover',
					[
						'label' 	=> esc_html__( 'Breadcrumbs Opacity Hover', 'tripgo' ),
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
							'{{WRAPPER}} .header_banner_el ul.breadcrumb li:not(:first-child) a:hover' => 'opacity: {{SIZE}};',
						],
						
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'header_breadcrumbs_typo',
						'label' 	=> esc_html__( 'Breadcrumbs Typography', 'tripgo' ),
						'selector'	=> '{{WRAPPER}} .header_banner_el ul.breadcrumb li, {{WRAPPER}} .header_banner_el ul.breadcrumb li a'
					]
				);

				$this->add_responsive_control(
					'breadcrumbs_padding',
					[
						'label' 		=> esc_html__( 'Breadcrumbs Padding', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .header_banner_el .header_breadcrumbs' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_responsive_control(
					'align',
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
						'selectors' => [
							'{{WRAPPER}} .wrap_header_banner' => 'text-align: {{VALUE}};',
							'{{WRAPPER}} .wrap_header_banner ul.breadcrumb' => 'width: auto; display: initial;'
						],
						'default'	=> 'center',
						'separator' => 'before'
					]
				);

				$this->add_control(
					'class',
					[
						'label' => esc_html__( 'Class', 'tripgo' ),
						'type' 	=> \Elementor\Controls_Manager::TEXT,
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

			// Class
			$class = tripgo_get_meta_data( 'class', $settings );

			// Background
			$class_bg = $attr_style = '';

			if ( 'yes' === tripgo_get_meta_data( 'header_bg_source', $settings ) ) {
				// Get current ID
				$current_id = tripgo_get_current_id();

				// Get header background
				$header_bg_source = get_the_post_thumbnail_url( $current_id, 'full' );	

				// Background
				$class_bg 	= 'bg_feature_img';
				$attr_style = 'style="background: url( '.$header_bg_source.' )" ';
			}

			// Align
			$align = tripgo_get_meta_data( 'align', $settings );

			// Title tag
			$title_tag = tripgo_get_meta_data( 'title_tag', $settings );

			?>
		 	<div class="wrap_header_banner <?php echo esc_attr( $class_bg.' '.$align ); ?>" <?php echo wp_kses_post( '%s', $attr_style ); ?>>
		 		<?php if ( 'yes' === tripgo_get_meta_data( 'header_boxed_content', $settings ) ): ?>
		 			<div class="row_site">
		 				<div class="container_site">
		 		<?php endif; ?>
				 	<div class="cover_color"></div>
					<div class="header_banner_el <?php echo esc_attr( $class ); ?>">
						<?php if ( 'yes' === tripgo_get_meta_data( 'show_title', $settings ) ):
							add_filter( 'tripgo_show_singular_title', '__return_false' );
						?>
							<<?php echo esc_attr( $title_tag ); ?> class="header_title">
								<?php echo get_template_part( 'template-parts/parts/breadcrumbs_title' ); ?>
							</<?php echo esc_attr( $title_tag ); ?>>
						<?php endif;

						// Breadcrumbs
						if ( 'yes' === tripgo_get_meta_data( 'show_breadcrumbs', $settings ) ): ?>
							<div class="header_breadcrumbs">
								<?php echo get_template_part( 'template-parts/parts/breadcrumbs' ); ?>
							</div>
						<?php endif; ?>
					</div>
				<?php if ( 'yes' === tripgo_get_meta_data( 'header_boxed_content', $settings ) ): ?>
		 				</div>
	 				</div>
		 		<?php endif; ?>
			</div>
			<?php
		}
	}

	// init widget
	$widgets_manager->register( new Tripgo_Elementor_Header_Banner() );
}