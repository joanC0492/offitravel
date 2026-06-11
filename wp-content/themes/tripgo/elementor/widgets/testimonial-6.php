<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class Tripgo_Elementor_Testimonial_6
 */
if ( !class_exists( 'Tripgo_Elementor_Testimonial_6', false ) ) {

	class Tripgo_Elementor_Testimonial_6 extends \Elementor\Widget_Base {

		/**
		 * Get widget name
		 */
		public function get_name() {
			return 'tripgo_elementor_testimonial_6';
		}

		/**
		 * Get widget title
		 */
		public function get_title() {
			return esc_html__( 'Ova Testimonial 6', 'tripgo' );
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
			return [ 'tripgo-elementor-testimonial-6' ];
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
					'background_image',
					[
						'label' 	=> esc_html__( 'Choose Backround Image', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::MEDIA,
						'default' 	=> [
							'url' 	=> \Elementor\Utils::get_placeholder_image_src(),
						],
					]
				);

				$this->add_control(
					'icon',
					[
						'label' 	=> esc_html__( 'Icon', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::ICONS,
						'default' 	=> [
							'value' 	=> 'fas fa-quote-left',
							'library' 	=> 'all',
						],
					]
				);

				$this->add_control(
					'image_author',
					[
						'label'   => esc_html__( 'Author Image', 'tripgo' ),
						'type'    => \Elementor\Controls_Manager::MEDIA,
						'default' => [
							'url' => \Elementor\Utils::get_placeholder_image_src(),
						],
					]
				);

				$this->add_control(
					'name_author',
					[
						'label'   => esc_html__( 'Author Name', 'tripgo' ),
						'type'    => \Elementor\Controls_Manager::TEXT,
						'default' => 'Henry M. Becerra',
					]
				);

				$this->add_control(
					'job',
					[
						'label'   => esc_html__( 'Job', 'tripgo' ),
						'type'    => \Elementor\Controls_Manager::TEXT,
						'default' => esc_html__( 'CEO & Founder', 'tripgo' ),
					]
				);

				$this->add_control(
					'testimonial',
					[
						'label'   => esc_html__( 'Testimonial ', 'tripgo' ),
						'type'    => \Elementor\Controls_Manager::TEXTAREA,
						'default' => esc_html__( 'This is due to their excellent service, competitive pricing and customer support. It’s throughly refresing to get such', 'tripgo' ),
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
						'separator' 	=> 'before'
					]
				);

				$this->add_control(
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
						'condition' => [
							'show_rating' => 'yes'
						]
					]
				);
				
			$this->end_controls_section(); // END SECTION CONTENT

			/* General */
			$this->start_controls_section(
				'general_style_section',
				[
					'label' => esc_html__( 'General', 'tripgo' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_responsive_control(
					'general_padding',
					[
						'label' 		=> esc_html__( 'Padding', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-testimonial-6' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_control(
					'general_background',
					[
						'label' 	=> esc_html__( 'Backround', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-testimonial-6' => 'background: {{VALUE}}',
						],
					]
				);

			$this->end_controls_section();

			/* Testimonial */
			$this->start_controls_section(
					'desc_style_section',
					[
						'label' => esc_html__( 'Testimonial', 'tripgo' ),
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
							'{{WRAPPER}} .ova-testimonial-6 .desc' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'desc_typography',
						'selector' 	=> '{{WRAPPER}} .ova-testimonial-6 .desc',
					]
				);

				$this->add_control(
					'desc_color',
					[
						'label' 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-testimonial-6 .desc' => 'color: {{VALUE}}',
						],
					]
				);

			$this->end_controls_section();

			/* Icon */
			$this->start_controls_section(
				'icon_style_section',
				[
					'label' => esc_html__( 'Icon', 'tripgo' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_control(
					'icon_color',
					[
						'label' 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-testimonial-6 .author .img .icon i' => 'color: {{VALUE}}',
							'{{WRAPPER}} .ova-testimonial-6 .author .img .icon svg' => 'fill: {{VALUE}}',
						],
					]
				);

				$this->add_control(
					'icon_bg',
					[
						'label' 	=> esc_html__( 'Background', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-testimonial-6 .author .img .icon' => 'background: {{VALUE}}',
						],
					]
				);

			$this->end_controls_section();

			/* Name */
			$this->start_controls_section(
				'name_style_section',
				[
					'label' => esc_html__( 'Author Name', 'tripgo' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_responsive_control(
					'name_margin',
					[
						'label' 		=> esc_html__( 'Margin', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-testimonial-6 .author .name' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'name_typography',
						'selector' 	=> '{{WRAPPER}} .ova-testimonial-6 .author .name',
					]
				);

				$this->add_control(
					'name_color',
					[
						'label' 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-testimonial-6 .author .name' => 'color: {{VALUE}}',
						],
					]
				);

			$this->end_controls_section();

			/* Job */
			$this->start_controls_section(
				'job_style_section',
				[
					'label' => esc_html__( 'Job', 'tripgo' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_responsive_control(
					'job_margin',
					[
						'label' 		=> esc_html__( 'Margin', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-testimonial-6 .author .job' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'job_typography',
						'selector' 	=> '{{WRAPPER}} .ova-testimonial-6 .author .job',
					]
				);

				$this->add_control(
					'job_color',
					[
						'label' 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-testimonial-6 .author .job' => 'color: {{VALUE}}',
						],
					]
				);

			$this->end_controls_section();

			/* Rating */
			$this->start_controls_section(
				'rating_style_section',
				[
					'label' => esc_html__( 'Rating', 'tripgo' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_responsive_control(
					'rating_margin',
					[
						'label' 		=> esc_html__( 'Margin', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-testimonial-6 .author .rating' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_control(
					'rating_color',
					[
						'label' 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-testimonial-6 .author .rating i' => 'color: {{VALUE}}',
						],
					]
				);

			$this->end_controls_section();
		}

		/**
		 * Get rating
		 */
		protected function get_rating( $rating ) {
			return [ $rating, 5 ];
		}

		/**
		 * Render stars
		 */
		protected function render_stars( $icon, $rating ) {
			$rating_data 	= $this->get_rating($rating);
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

			// Get testimonial
			$testimonial = tripgo_get_meta_data( 'testimonial', $settings );

			// Get name author
			$name_author = tripgo_get_meta_data( 'name_author', $settings );

			// Get icon
			$icon = tripgo_get_meta_data( 'icon', $settings );

			// Get background image
			$bg_image_url = isset( $settings['background_image']['url'] ) && $settings['background_image']['url'] ? $settings['background_image']['url'] : \Elementor\Utils::get_placeholder_image_src();

			// Get image author id
			$image_id = isset( $settings['image_author']['id'] ) && $settings['image_author']['id'] ? $settings['image_author']['id'] : '';

			// Get image author URL
			$image_author = isset( $settings['image_author']['url'] ) && $settings['image_author']['url'] ? $settings['image_author']['url'] : \Elementor\Utils::get_placeholder_image_src();

			// Get image author alt
			$image_alt = $name_author;
			if ( $image_id ) {
				$image_alt = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
			}

			// Get job
			$job = tripgo_get_meta_data( 'job', $settings );

			// Show rating
			$show_rating = tripgo_get_meta_data( 'show_rating', $settings );

			// Get rating
			$rating = tripgo_get_meta_data( 'rating', $settings );

			?>
			<div class="ova-testimonial-6">
				<div class="background-img" style="background-image: url('<?php echo esc_url( $bg_image_url ); ?>');"></div>
				<div class="wrapper">
					<?php if ( $testimonial ): ?>
						<p class="desc"><?php echo esc_html( $testimonial ); ?></p>
					<?php endif; ?>
					<div class="author">
						<?php if ( !empty( $icon['value'] ) || !empty( $image_author ) ): ?>
							<div class="img">
								<?php if ( !empty( $icon['value'] ) ): ?>
									<div class="icon">
										<?php \Elementor\Icons_Manager::render_icon( $icon, [ 'aria-hidden' => 'true' ] ); ?>
									</div>
								<?php endif;

								// Image author
								if ( $image_author ): ?>
									<img src="<?php echo esc_url( $image_author ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>">
								<?php endif; ?>
							</div>
						<?php endif; ?>
						<div class="info">
							<?php if ( $name_author ): ?>
								<h3 class="name"><?php echo esc_html( $name_author ); ?></h3>
							<?php endif;

							// Job
							if ( $job ): ?>
								<p class="job"><?php echo esc_html( $job ); ?></p>
							<?php endif;

							// Rating
							if ( 'yes' === $show_rating ):
								// init icon
								$icon = '&#xE934;';

								// Rating data
								$rating_data = $this->get_rating( $rating );

								// Textual rating
								$textual_rating = $rating_data[0] . '/' . $rating_data[1];

								// Rating
								$rating = (float)$rating;

								// Stars element
								$stars_element = '<div class="elementor-star-rating" title="'.$textual_rating.'">' . wp_kses_post( $this->render_stars( $icon, $rating ) ) . ' </div>';

								if ( 0 != $rating ): ?>
									<div class="rating">
										<?php echo wp_kses_post( $stars_element ); ?>
									</div>
								<?php endif;
							endif; ?>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
	}

	// init widget
	$widgets_manager->register( new Tripgo_Elementor_Testimonial_6() );
}