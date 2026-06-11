<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class Tripgo_Elementor_Canvas_Menu
 */
if ( !class_exists( 'Tripgo_Elementor_Canvas_Menu', false ) ) {

	class Tripgo_Elementor_Canvas_Menu extends \Elementor\Widget_Base {

		/**
		 * Get widget name
		 */
		public function get_name() {
			return 'tripgo_elementor_menu_canvas';
		}

		/**
		 * Get widget title
		 */
		public function get_title() {
			return esc_html__( 'Menu Canvas', 'tripgo' );
		}

		/**
		 * Get widget icon
		 */
		public function get_icon() {
			return 'eicon-menu-bar';
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
			return [ 'tripgo-elementor-menu-canvas' ];
		}

		/**
		 * Get script depends
		 */
		public function get_script_depends() {
			return [ 'tripgo-elementor-menu-canvas' ];
		}
		
		/**
		 * Register controls
		 */
		protected function register_controls() {
			// Global
			$this->start_controls_section(
				'section_menu_type',
				[
					'label' => esc_html__( 'Global', 'tripgo' )
				]
			);
				// List menu
				$list_menu = [];

				// Default menu
				$default_menu = '';

				// Get menus
				$menus = \wp_get_nav_menus([ 'order' => 'DESC' ]);
				if ( tripgo_array_exists( $menus ) ) {
					foreach ( $menus as $menu ) {
						$list_menu[$menu->slug] = $menu->name;

						// Default menu
						if ( !$default_menu ) $default_menu = $menu->slug;
					}
				}

				$this->add_control(
					'menu_slug',
					[
						'label' 		=> esc_html__( 'Select Menu', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::SELECT,
						'options' 		=> $list_menu,
						'default' 		=> $default_menu,
						'prefix_class' 	=> 'elementor-view-'
					]
				);

				$this->add_control(
					'menu_dir',
					[
						'label' 	=> esc_html__( 'Menu Direction', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::CHOOSE,
						'options' 	=> [
							'dir_left' => [
								'title' => esc_html__( 'Left', 'tripgo' ),
								'icon' 	=> 'eicon-h-align-left'
							],
							'dir_right' => [
								'title' => esc_html__( 'Right', 'tripgo' ),
								'icon' 	=> 'eicon-h-align-right'
							]
						],
						'default' 	=> 'dir_left'
					]
				);

				$this->add_control(
					'show_button',
					[
						'label' 	=> esc_html__( 'Show Button', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 	=> esc_html__( 'Show', 'tripgo' ),
						'label_off' => esc_html__( 'Hide', 'tripgo' ),
						'default' 	=> 'no'
					]
				);

				$this->add_control(
					'link_button',
					[
						'label'   		=> esc_html__( 'Link Button', 'tripgo' ),
						'type'    		=> \Elementor\Controls_Manager::URL,
						'description' 	=> esc_html__( 'https://your-domain.com', 'tripgo' ),
						'show_external' => false,
						'default' 		=> [
							'url' 			=> '#',
							'is_external' 	=> false,
							'nofollow' 		=> false
						],
						'condition' 	=> [
							'show_button' => 'yes'
						]
					]
				);

				$this->add_control(
					'text_button',
					[
						'label' 	=> esc_html__( 'Text Button', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::TEXT,
						'default' 	=> esc_html__('Explorar','tripgo'),
						'condition' => [
							'show_button' => 'yes'
						]
					]
				);
				
			$this->end_controls_section();	

			// Style Section
			$this->start_controls_section(
				'section_content',
				[
					'label' => esc_html__( 'Style', 'tripgo' )
				]
			);
				
				$this->start_controls_tabs(
					'style_tabs_button'
				);

					$this->start_controls_tab(
						'style_normal_tab_button',
						[
							'label' => esc_html__( 'Normal', 'tripgo' )
						]
					);

						// Button Color
						$this->add_control(
							'btn_color',
							[
								'label' 	=> esc_html__( 'Button color', 'tripgo' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'default' 	=> '',
								'selectors' => [
									'{{WRAPPER}} .menu-toggle:before' => 'background-color: {{VALUE}};',
									'{{WRAPPER}} .menu-toggle span:before' => 'background-color: {{VALUE}};',
									'{{WRAPPER}} .menu-toggle:after' => 'background-color: {{VALUE}};'
								]
							]
						);

						$this->add_control(
							'btn_color_background',
							[
								'label' 	=> esc_html__( 'Button background', 'tripgo' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'default' 	=> '',
								'selectors' => [
									'{{WRAPPER}} .menu-toggle' => 'background-color: {{VALUE}};'
								]
							]
						);

					$this->end_controls_tab();

					$this->start_controls_tab(
						'style_hover_tab_button',
						[
							'label' => esc_html__( 'Hover', 'tripgo' )
						]
					);

						$this->add_control(
							'btn_color_hover',
							[
								'label' 	=> esc_html__( 'Button color', 'tripgo' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'default' 	=> '',
								'selectors' => [
									'{{WRAPPER}} .menu-toggle:hover:before' => 'background-color: {{VALUE}};',
									'{{WRAPPER}} .menu-toggle:hover span:before' => 'background-color: {{VALUE}};',
									'{{WRAPPER}} .menu-toggle:hover:after' => 'background-color: {{VALUE}};'
								]
							]
						);

						$this->add_control(
							'btn_color_background_hover',
							[
								'label' 	=> esc_html__( 'Button background', 'tripgo' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'default' 	=> '',
								'selectors' => [
									'{{WRAPPER}} .menu-toggle:hover' => 'background-color: {{VALUE}};'
								]
							]
						);

					$this->end_controls_tab();
				$this->end_controls_tabs();

				// Background Menu
				$this->add_control(
					'bg_color',
					[
						'label' 	=> esc_html__( 'Menu Background', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'default' 	=> '',
						'selectors' => [
							'{{WRAPPER}} .container-menu' => 'background-color: {{VALUE}};'
						],
						'separator' => 'before'
					]
				);

				// Typography Menu Item
				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'typography',
						'selector'	=> '{{WRAPPER}} ul li a'
					]
				);

				// Control Tabs
				$this->start_controls_tabs(
					'style_tabs_text'
				);

					// Normal Tab
					$this->start_controls_tab(
						'style_normal_tab_text',
						[
							'label' => esc_html__( 'Normal', 'tripgo' )
						]
					);
				
						$this->add_control(
							'text_color',
							[
								'label' 	=> esc_html__( 'Menu Color', 'tripgo' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'default' 	=> '',
								'selectors' => [
									'{{WRAPPER}} ul li a' => 'color: {{VALUE}};'
								]
							]
						);

					$this->end_controls_tab();

					// Hover Tab
					$this->start_controls_tab(
						'style_hover_tab_text',
						[
							'label' => esc_html__( 'Hover', 'tripgo' )
						]
					);

						$this->add_control(
							'text_color_hover',
							[
								'label' 	=> esc_html__( 'Menu Color', 'tripgo' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'default' 	=> '',
								'selectors' => [
									'{{WRAPPER}} ul li a:hover' => 'color: {{VALUE}};'
								]
							]
						);

					$this->end_controls_tab();

					// Active Tab
					$this->start_controls_tab(
						'style_active_tab_text',
						[
							'label' => esc_html__( 'Active', 'tripgo' ),
						]
					);

						$this->add_control(
							'text_color_active',
							[
								'label' 	=> esc_html__( 'Menu Color', 'tripgo' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'default' 	=> '',
								'selectors' => [
									'{{WRAPPER}} ul li.current-menu-item > a' => 'color: {{VALUE}};',
									'{{WRAPPER}} ul li.current-menu-ancestor > a' => 'color: {{VALUE}};',
									'{{WRAPPER}} ul li.current-menu-parent > a' => 'color: {{VALUE}};'
								]
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

			// Menu dir
			$menu_dir = tripgo_get_meta_data( 'menu_dir', $settings );

			// Menu slug
			$menu_slug = tripgo_get_meta_data( 'menu_slug', $settings );

			// Show button
			$show_button = tripgo_get_meta_data( 'show_button', $settings );

			// Link button
			$link_button = isset( $settings['link_button']['url'] ) ? $settings['link_button']['url'] : '';

			// Text button
			$text_button = isset( $settings['text_button'] ) ? $settings['text_button'] : '';

			// Target
			$target = isset( $settings['link_button']['is_external'] ) && $settings['link_button']['is_external'] ? '_blank' : '_self';
			?>
			<nav class="menu-canvas">
	            <button class="menu-toggle">
	            	<span></span>
	            </button>
	            <nav class="container-menu <?php echo esc_attr( $menu_dir ); ?>">
		            <div class="close-menu">
		            	<i class="ovaicon-cancel"></i>
		            </div>
					<?php wp_nav_menu([
						'theme_location'  	=> $menu_slug,
						'container_class' 	=> 'primary-navigation',
						'menu' 				=> $menu_slug
					]);

					// Show button
					if ( 'yes' === $show_button ): ?>
						<div class="menu-button">
							<a class="explore" href="<?php echo esc_url( $link_button ); ?>" target="<?php echo esc_attr( $target ); ?>">
								<?php echo esc_html( $text_button ); ?>
							</a>
						</div>
					<?php endif; ?>
				</nav>
				<div class="site-overlay"></div>
	        </nav>
			<?php
		}
	}

	// init widget
	$widgets_manager->register( new Tripgo_Elementor_Canvas_Menu() );
}