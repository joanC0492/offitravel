<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class Tripgo_Elementor_Ova_Image_Gallery
 */
if ( !class_exists( 'Tripgo_Elementor_Ova_Image_Gallery', false ) ) {

	class Tripgo_Elementor_Ova_Image_Gallery extends \Elementor\Widget_Base {

		/**
		 * Get widget name
		 */
		public function get_name() {
			return 'tripgo_elementor_ova_image_gallery';
		}

		/**
		 * Get widget title
		 */
		public function get_title() {
			return esc_html__( 'Image Gallery', 'tripgo' );
		}

		/**
		 * Get widget icon
		 */
		public function get_icon() {
			return 'eicon-gallery-grid';
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
			return [ 'ova-fancybox', 'tripgo-elementor-image-gallery' ];
		}

		/**
		 * Get script depends
		 */
		public function get_script_depends() {
			return [ 'ova-fancybox', 'tripgo-elementor-image-gallery' ];
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
					'column',
					[
						'label' 	=> esc_html__( 'Column', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::SELECT,
						'default' 	=> 'three_column',
						'options' 	=> [
							'two_column' 	=> esc_html__( '2 Columns', 'tripgo' ),
							'three_column' 	=> esc_html__( '3 Columns', 'tripgo' ),
							'four_column' 	=> esc_html__( '4 Columns', 'tripgo' ),
						],
					]
				);

				$this->add_control(
					'show_title',
					[
						'label'   => esc_html__( 'Show Title', 'tripgo' ),
						'type'    => \Elementor\Controls_Manager::SWITCHER,
						'default' => 'yes',
						'options' => [
							'yes' => esc_html__( 'Yes', 'tripgo' ),
							'no'  => esc_html__( 'No', 'tripgo' ),
						],
						'frontend_available' => true,
					]
				);

				// Title
				$this->add_control(
					'title',
					[
						'label' 		=> esc_html__( 'Title', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::TEXT,
						'default' 		=> esc_html__( 'Photo Gallery', 'tripgo' ),
						'placeholder' 	=> esc_html__( 'Type your title here', 'tripgo' ),
						'condition' 	=> [
							'show_title' => 'yes',
						],
					]
				);

				// Add Class control
				$this->add_control(
					'ova_image_gallery',
					[
						'label' 	=> esc_html__( 'Add Images', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::GALLERY,
						'default' 	=> [],
					]
				);

				$this->add_control(
					'icon',
					[
						'label' 	=> esc_html__( 'Icon', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::ICONS,
						'default' 	=> [
							'value' 	=> 'fab fa-instagram',
							'library' 	=> 'all',
						],
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Image_Size::get_type(),
					[
						'name' 		=> 'medium', // Usage: `{name}_size` and `{name}_custom_dimension`
						'exclude' 	=> [ 'custom' ],
						'default' 	=> 'medium',
						'separator' => 'none',
					]
				);

			$this->end_controls_section();

			$this->start_controls_section(
				'ova_image_gallery_style',
				[
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
					'label' => esc_html__( 'Content', 'tripgo' ),
				]
			);

				$this->add_responsive_control(
					'gap',
					[
						'label' 		=> esc_html__( 'Gap', 'tripgo' ),
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
							'{{WRAPPER}} .ova-image-gallery-ft' => 'gap: {{SIZE}}{{UNIT}};',
						],
					]
				);

				$this->add_responsive_control(
					'overlay_opacity',
					[
						'label' 	=> esc_html__( 'Overlay Hover Opacity', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::SLIDER,
						'default' 	=> [
							'size' => 0.84,
						],
						'range' 	=> [
							'px' => [
								'max' 	=> 1,
								'step' 	=> 0.01,
							],
						],
						'selectors' => [
							'{{WRAPPER}} .ova-image-gallery-ft .item-fancybox-ft:hover .overlay' => 'opacity: {{SIZE}};',
						],
						
					]
				);

		        $this->add_control(
					'overlay_color',
					[
						'label' 	=> esc_html__( 'Overlay Hover Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-image-gallery-ft .item-fancybox-ft .overlay' => 'background-color: {{VALUE}};',
						],
					]
				);

			$this->end_controls_section();

			// Styles
			$this->start_controls_section(
				'title_section',
				[
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
					'label' => esc_html__( 'Title', 'tripgo' ),
				]
			);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'title_typography',
						'selector' 	=> '{{WRAPPER}} .ova-image-gallery .title',
					]
				);

				$this->add_control(
					'title_color',
					[
						'label' 	=> esc_html__( 'Text Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-image-gallery .title' => 'color: {{VALUE}}',
						],
					]
				);

				$this->add_responsive_control(
					'title_margin',
					[
						'label' 		=> esc_html__( 'Margin', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-image-gallery .title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

			$this->end_controls_section(); // END SECTION TITLE
		}

		/**
		 * Render HTML
		 */
		protected function render() {
			// Get settings
			$settings = $this->get_settings_for_display();

			// Get title
			$title = tripgo_get_meta_data( 'title', $settings );

			// Columns
			$columns = tripgo_get_meta_data( 'column', $settings );

			// Galleries
			$galleries = tripgo_get_meta_data( 'ova_image_gallery', $settings );

			// Icon
			$icon = tripgo_get_meta_data( 'icon', $settings );

			// Show title
			$show_title = tripgo_get_meta_data( 'show_title', $settings );

			// Image size
			$img_size = tripgo_get_meta_data( 'medium_size', $settings );

			?>

			<div class="ova-image-gallery">
				<?php if ( 'yes' === $show_title && $title ): ?>
					<h3 class="title">
						<?php echo esc_html( $title ); ?>	
					</h3>
				<?php endif;

				// Galleries
				if ( tripgo_array_exists( $galleries ) ): ?>
					<div class="ova-image-gallery-ft <?php echo esc_attr( $columns ); ?>">
						<?php foreach ( $galleries as $item ):
							// Get image id
							$img_id = tripgo_get_meta_data( 'id', $item );
							
							// Get image URL
							$img_url = tripgo_get_meta_data( 'url', $item );

							// Get image alt
							$alt = get_post_meta( $img_id, '_wp_attachment_image_alt', true );
							if ( !$alt ) $alt = esc_html__( 'Gallery Slide', 'tripgo' );

							// Get thumbnail URL
		                    $thumbnail_url = isset( wp_get_attachment_image_src( $img_id, 'medium' )[0] ) ? wp_get_attachment_image_src( $img_id, $img_size )[0] : '';

		                    // Get caption
		                    $caption = wp_get_attachment_caption( $img_id );
		                    if ( !$caption ) $caption = $alt;
						?>
							<a href="javascript:void(0)" data-src="<?php echo esc_url( $img_url ); ?>" class="item-fancybox-ft" data-fancybox="image-gallery-ft" data-caption="<?php echo esc_attr( $caption ); ?>">
								<img src="<?php echo esc_url( $thumbnail_url ); ?>" alt="<?php echo esc_attr( $alt ); ?>">
								<div class="overlay">
									<?php if ( !empty( $icon['value'] ) ): ?>
										<div class="icon">
											<?php \Elementor\Icons_Manager::render_icon( $icon, [ 'aria-hidden' => 'true' ] ); ?>
										</div>
									<?php endif; ?>
								</div>
							</a>
						<?php endforeach; ?>
					</div> 
				<?php endif; ?>
			</div>	
			<?php
		}
	}

	// init widget
	$widgets_manager->register( new Tripgo_Elementor_Ova_Image_Gallery() );
}