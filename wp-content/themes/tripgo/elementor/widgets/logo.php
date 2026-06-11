<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class Tripgo_Elementor_Logo
 */
if ( !class_exists( 'Tripgo_Elementor_Logo', false ) ) {

	class Tripgo_Elementor_Logo extends \Elementor\Widget_Base {

		/**
		 * Get widget name
		 */
		public function get_name() {
			return 'ova_logo';
		}

		/**
		 * Get widget title
		 */
		public function get_title() {
			return esc_html__( 'Logo', 'tripgo' );
		}

		/**
		 * Get widget icon
		 */
		public function get_icon() {
			return 'eicon-image';
		}

		/**
		 * Get widget categories
		 */
		public function get_categories() {
			return [ 'hf' ];
		}

		/**
		 * Get widget keywords
		 */
		public function get_keywords() {
			return [ 'image', 'photo', 'visual' ];
		}

		/**
		 * Get style depends
		 */
		public function get_style_depends() {
			return [ 'tripgo-elementor-logo' ];
		}

		/**
		 * Register controls
		 */
		protected function register_controls() {
			// Image controls
			$this->start_controls_section(
				'section_image',
				[
					'label' => esc_html__( 'Image', 'tripgo' ),
				]
			);

				$this->add_control(
					'link_to',
					[
						'label' 	=> esc_html__( 'Link', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::SELECT,
						'options' 	=> [
							'home' 		=> esc_html__( 'Home Page', 'tripgo' ),
							'none' 		=> esc_html__( 'None', 'tripgo' ),
							'custom' 	=> esc_html__( 'Custom URL', 'tripgo' ),
						],
						'default' => 'home'
					]
				);

				$this->add_control(
					'link',
					[
						'label' 		=> esc_html__( 'Link', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::URL,
						'dynamic' 		=> [
							'active' 	=> true
						],
						'placeholder' 	=> esc_html__( 'https://your-link.com', 'tripgo' ),
						'condition' 	=> [
							'link_to' 	=> 'custom'
						],
						'show_label' 	=> false
					]
				);

				$this->add_responsive_control(
					'align',
					[
						'label' 	=> esc_html__( 'Alignment', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::CHOOSE,
						'options' 	=> [
							'flex-start' 	=> [
								'title' 	=> esc_html__( 'Left', 'tripgo' ),
								'icon' 		=> 'eicon-text-align-left'
							],
							'center' 		=> [
								'title' 	=> esc_html__( 'Center', 'tripgo' ),
								'icon' 		=> 'eicon-text-align-center'
							],
							'flex-end' 		=> [
								'title' 	=> esc_html__( 'Right', 'tripgo' ),
								'icon' 		=> 'eicon-text-align-right'
							],
						],
						'selectors' => [
							'{{WRAPPER}} .brand_el' => 'justify-content: {{VALUE}};'
						]
					]
				);

				$this->add_control(
					'desk_logo',
					[
						'label' 	=> esc_html__( 'Desktop Logo', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::MEDIA,
						'dynamic' 	=> [
							'active' => true
						],
						'default' 	=> [
							'url' 	=> \Elementor\Utils::get_placeholder_image_src()
						],
						'separator' => 'before'
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Image_Size::get_type(),
					[
						'name' 		=> 'desk_logo',
						'default' 	=> 'thumbnail',
						'separator' => 'none'
					]
				);

				$this->add_control(
					'desk_w',
					[
						'label' 		=> esc_html__( 'Desktop Logo Width', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::SLIDER,
						'size_units' 	=> [ 'px' ],
						'range' 		=> [
							'px' => [
								'min' 	=> 1,
								'max' 	=> 1000,
								'step' 	=> 1
							]
						],
						'default' => [
							'unit' => 'px',
							'size' => 132
						]
					]
				);
				$this->add_control(
					'desk_h',
					[
						'label' 		=> esc_html__( 'Desktop Logo Height', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::SLIDER,
						'size_units' 	=> [ 'px' ],
						'range' 		=> [
							'px' => [
								'min' 	=> 1,
								'max' 	=> 1000,
								'step' 	=> 1
							]
						],
						'default' => [
							'unit' => 'px',
							'size' => 36
						]
					]
				);

				$this->add_control(
					'mobile_logo',
					[
						'label' 	=> esc_html__( 'Mobile Logo', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::MEDIA,
						'dynamic' 	=> [
							'active' => true
						],
						'default' => [
							'url' => \Elementor\Utils::get_placeholder_image_src()
						],
						'separator' => 'before'
					]
				);

				$this->add_control(
					'mobile_w',
					[
						'label' 		=> esc_html__( 'Mobile Logo Width', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::SLIDER,
						'size_units' 	=> [ 'px' ],
						'range' 		=> [
							'px' => [
								'min' 	=> 1,
								'max' 	=> 1000,
								'step' 	=> 1
							]
						],
						'default' => [
							'unit' => 'px',
							'size' => 132
						]
					]
				);
				$this->add_control(
					'mobile_h',
					[
						'label' 		=> esc_html__( 'Mobile Logo Height', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::SLIDER,
						'size_units' 	=> [ 'px' ],
						'range' 		=> [
							'px' => [
								'min' 	=> 1,
								'max' 	=> 1000,
								'step' 	=> 1
							]
						],
						'default' => [
							'unit' => 'px',
							'size' => 36
						]
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Image_Size::get_type(),
					[
						'name' 		=> 'mobile_logo',
						'default' 	=> 'thumbnail',
						'separator' => 'none'
					]
				);

				$this->add_control(
					'sticky_logo',
					[
						'label' 	=> esc_html__( 'Sticky Logo', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::MEDIA,
						'dynamic' 	=> [
							'active' => true
						],
						'default' => [
							'url' => \Elementor\Utils::get_placeholder_image_src()
						],
						'separator' => 'before'
					]
				);

				$this->add_control(
					'sticky_w',
					[
						'label' 		=> esc_html__( 'Sticky Logo Width', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::SLIDER,
						'size_units' 	=> [ 'px' ],
						'range' 		=> [
							'px' => [
								'min' 	=> 1,
								'max' 	=> 1000,
								'step' 	=> 1
							]
						],
						'default' => [
							'unit' => 'px',
							'size' => 132
						]
					]
				);
				$this->add_control(
					'sticky_h',
					[
						'label' 		=> esc_html__( 'Sticky Logo Height', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::SLIDER,
						'size_units' 	=> [ 'px' ],
						'range' 		=> [
							'px' => [
								'min' 	=> 1,
								'max' 	=> 1000,
								'step' 	=> 1
							]
						],
						'default' => [
							'unit' => 'px',
							'size' => 36
						]
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Image_Size::get_type(),
					[
						'name' 		=> 'sticky_logo',
						'default' 	=> 'thumbnail',
						'separator' => 'none'
					]
				);

			$this->end_controls_section();
		}

		/**
		 * Get link URL
		 */
		private function get_link_url( $settings ) {
			// init
			$results = false;

			// Get link to
			$link_to = tripgo_get_meta_data( 'link_to', $settings );
			switch ( $link_to ) {
				case 'home':
					$results = [
						'url' => esc_url( home_url('/') )
					];
					break;
				case 'custom':
					if ( !empty( $settings['link']['url'] ) ) {
						$results = $settings['link'];
					}
					break;
				default:
					// do something
					break;
			}

			return $results;
		}

		/**
		 * Render template
		 */
		protected function render() {
			// Get settings
			$settings = $this->get_settings_for_display();

			// Get desktop logo URL
			$desk_logo = isset( $settings['desk_logo']['url'] ) && $settings['desk_logo']['url'] ? $settings['desk_logo']['url'] : '';
			if ( !$desk_logo ) return;

			// Get desktop logo width size
			$desk_ws = isset( $settings['desk_w']['size'] ) && $settings['desk_w']['size'] ? $settings['desk_w']['size'] : '';

			// Get desktop logo height size
			$desk_hs = isset( $settings['desk_h']['size'] ) && $settings['desk_h']['size'] ? $settings['desk_h']['size'] : '';

			// Get desktop logo unit
			$desk_u = isset( $settings['desk_w']['unit'] ) && $settings['desk_w']['unit'] ? $settings['desk_w']['unit'] : '';

			// Get desktop logo width
			$desk_w = $desk_ws ? $desk_ws.$desk_u : 'auto';

			// Get desktop logo height
			$desk_h = $desk_hs ? $desk_hs.$desk_u : 'auto';

			// Get mobile logo URL
			$mobile_logo = isset( $settings['mobile_logo']['url'] ) && $settings['mobile_logo']['url'] ? $settings['mobile_logo']['url'] : '';

			// Get mobile logo width size
			$mobile_ws = isset( $settings['mobile_w']['size'] ) && $settings['mobile_w']['size'] ? $settings['mobile_w']['size'] : '';

			// Get mobile logo height size
			$mobile_hs = isset( $settings['mobile_h']['size'] ) && $settings['mobile_h']['size'] ? $settings['mobile_h']['size'] : '';

			// Get mobile logo width
			$mobile_w = $mobile_ws ? $mobile_ws.$desk_u : 'auto';

			// Get mobile logo height
			$mobile_h = $mobile_hs ? $mobile_hs.$desk_u : 'auto';

			// Get sticky logo URL
			$sticky_logo = isset( $settings['sticky_logo']['url'] ) && $settings['sticky_logo']['url'] ? $settings['sticky_logo']['url'] : '';

			// Get sticky logo width size
			$sticky_ws = isset( $settings['sticky_w']['size'] ) && $settings['sticky_w']['size'] ? $settings['sticky_w']['size'] : '';

			// Get sticky logo height size
			$sticky_hs = isset( $settings['sticky_h']['size'] ) && $settings['sticky_h']['size'] ? $settings['sticky_h']['size'] : '';

			// Get sticky logo width
			$sticky_w = $sticky_ws ? $sticky_ws.$desk_u : 'auto';

			// Get sticky logo height
			$sticky_h = $sticky_hs ? $sticky_hs.$desk_u : 'auto';

			// Get link
			$link = $this->get_link_url( $settings );

			// Get ULR
			$url = tripgo_get_meta_data( 'url', $link );

			// Get nofollow
			$nofollow = tripgo_get_meta_data( 'nofollow', $link ) ? 'nofollow' : '';

			// Get target
			$target = tripgo_get_meta_data( 'is_external', $link ) ? '_blank' : '_self';

			?>
			<div class="brand_el">
				<?php if ( $url ): ?>
					<a href="<?php echo esc_url( $url ); ?>" rel="<?php echo esc_attr( $nofollow ); ?>" target="<?php echo esc_attr( $target ); ?>">
				<?php endif; ?>
					<img src="<?php echo esc_url( $desk_logo ); ?>" alt="<?php bloginfo( 'name' ); ?>" class="logo_desktop" style="width: <?php echo esc_attr( $desk_w ); ?>; height: <?php echo esc_attr( $desk_h ); ?>;">
					<img src="<?php echo esc_url( $mobile_logo ); ?>" alt="<?php bloginfo( 'name' ); ?>" class="logo_mobile" style="width: <?php echo esc_attr( $mobile_w ); ?>; height: <?php echo esc_attr( $mobile_h ); ?>;">
					<img src="<?php echo esc_url( $sticky_logo ); ?>" alt="<?php bloginfo('name'); ?>" class="logo_sticky" style="width:<?php echo esc_attr( $sticky_w ); ?>; height: <?php echo esc_attr( $sticky_h ); ?>;">
				<?php if ( $url ): ?>
					</a>
				<?php endif; ?>
			</div>
			<?php
		}
	}

	// init widget
	$widgets_manager->register( new Tripgo_Elementor_Logo() );
}