<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class Tripgo_Elementor_Menu_Nav
 */
if ( !class_exists( 'Tripgo_Elementor_Menu_Nav', false ) ) {

	class Tripgo_Elementor_Menu_Nav extends \Elementor\Widget_Base {

		/**
		 * Get widget name
		 */
		public function get_name() {
			return 'tripgo_elementor_menu_nav';
		}

		/**
		 * Get widget title
		 */
		public function get_title() {
			return esc_html__( 'Menu', 'tripgo' );
		}

		/**
		 * Get widget icon
		 */
		public function get_icon() {
			return 'eicon-nav-menu';
		}

		/**
		 * Get widget categories
		 */
		public function get_categories() {
			return [ 'hf' ];
		}
		
		/**
		 * Register controls
		 */
		protected function register_controls() {
			// Global
			$this->start_controls_section(
				'section_menu_type',
				[
					'label' => esc_html__( 'Global', 'tripgo' ),
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

			$this->end_controls_section(); // END

			// Parent Menu Section
			$this->start_controls_section(
				'section_content',
				[
					'label' => esc_html__( 'Parent Menu', 'tripgo' )
				]
			);

				// Typography Parent Menu
				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'menu_typography',
						'selector'	=> '{{WRAPPER}} ul li a'
					]
				);

				$this->add_responsive_control(
					'menu_padding',
					[
						'label' 		=> esc_html__( 'Padding', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} ul' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
						]
					]
				);

				$this->add_responsive_control(
					'menu_a_padding',
					[
						'label' 		=> esc_html__( 'Content Padding', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} ul li a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
						]
					]
				);

				// Control Tabs
				$this->start_controls_tabs(
					'style_parent_menu_tabs'
				);

					// Normal Tab
					$this->start_controls_tab(
						'style_parent_menu_normal_tab',
						[
							'label' => esc_html__( 'Normal', 'tripgo' )
						]
					);

						$this->add_control(
							'link_color',
							[
								'label' 	=> esc_html__( 'Menu Color', 'tripgo' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'default' 	=> '',
								'selectors' => [
									'{{WRAPPER}} ul.menu > li > a' => 'color: {{VALUE}};'
								],
								'separator' => 'before'
							]
						);

					$this->end_controls_tab();

					// Hover Tab
					$this->start_controls_tab(
						'style_parent_menu_hover_tab',
						[
							'label' => esc_html__( 'Hover', 'tripgo' )
						]
					);

						$this->add_control(
							'link_color_hover',
							[
								'label' 	=> esc_html__( 'Menu Color', 'tripgo' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'default' 	=> '',
								'selectors' => [
									'{{WRAPPER}} ul.menu > li > a:hover' => 'color: {{VALUE}};'
								]
							]
						);

					$this->end_controls_tab();

					// Active Tab
					$this->start_controls_tab(
						'style_parent_menu_active_tab',
						[
							'label' => esc_html__( 'Active', 'tripgo' )
						]
					);

						$this->add_control(
							'link_color_active',
							[
								'label' 	=> esc_html__( 'Menu Color', 'tripgo' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'default' 	=> '',
								'selectors' => [
									'{{WRAPPER}} ul.menu > li.current-menu-item > a' => 'color: {{VALUE}};'
								]
							]
						);

					$this->end_controls_tab();

				$this->end_controls_tabs();

			$this->end_controls_section(); // END

			// Sub Menu Section
			$this->start_controls_section(
				'section_submenu_content',
				[
					'label' => esc_html__( 'Sub Menu', 'tripgo' )
				]
			);	

				// Typography SubMenu
				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'submenu_typography',
						'selector'	=> '{{WRAPPER}} ul.sub-menu li a'
					]
				);

				// Background Submenu
				$this->add_control(
					'submenu_bg_color',
					[
						'label' 	=> esc_html__( 'Background', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'default' 	=> '',
						'selectors' => [
							'{{WRAPPER}} ul.sub-menu' => 'background-color: {{VALUE}};'
						]
					]
				);

				// Background Item Hover In Submenu
				$this->add_control(
					'submenu_bg_item_hover_color',
					[
						'label' 	=> esc_html__( 'Background Item Hover', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'default' 	=> '',
						'selectors' => [
							'{{WRAPPER}} ul.sub-menu li a:hover' => 'background-color: {{VALUE}};'
						]
					]
				);

				// Control Tabs
				$this->start_controls_tabs(
					'style_sub_menu_tabs'
				);

					// Normal Tab
					$this->start_controls_tab(
						'style_sub_menu_normal_tab',
						[
							'label' => esc_html__( 'Normal', 'tripgo' )
						]
					);

						$this->add_control(
							'submenu_link_color',
							[
								'label' 	=> esc_html__( 'Menu Color', 'tripgo' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'default' 	=> '',
								'selectors' => [
									'{{WRAPPER}} ul.sub-menu li a' => 'color: {{VALUE}};'
								]
							]
						);

					$this->end_controls_tab();

					// Hover Tab
					$this->start_controls_tab(
						'style_sub_menu_hover_tab',
						[
							'label' => esc_html__( 'Hover', 'tripgo' )
						]
					);

						$this->add_control(
							'submenu_link_color_hover',
							[
								'label' 	=> esc_html__( 'Menu Color', 'tripgo' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'default' 	=> '',
								'selectors' => [
									'{{WRAPPER}} ul.sub-menu li a:hover' => 'color: {{VALUE}};'
								]
							]
						);

					$this->end_controls_tab();

					// Active Tab
					$this->start_controls_tab(
						'style_sub_menu_active_tab',
						[
							'label' => esc_html__( 'Active', 'tripgo' )
						]
					);

						$this->add_control(
							'submenu_link_color_active',
							[
								'label' 	=> esc_html__( 'Menu Color', 'tripgo' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'default' 	=> '',
								'selectors' => [
									'{{WRAPPER}} ul.sub-menu li.current-menu-item > a' => 'color: {{VALUE}};'
								]
							]
						);

					$this->end_controls_tab();
				$this->end_controls_tabs();
			$this->end_controls_section(); // END
		}

		/**
		 * Render template
		 */
		protected function render() {
			// Get settings
			$settings = $this->get_settings_for_display();

			// Menu slug
			$menu_slug = tripgo_get_meta_data( 'menu_slug', $settings );
			
			?>
			<nav class="main-navigation">
	            <button class="menu-toggle">
	            	<span>
	            		<?php echo esc_html__( 'Menu', 'tripgo' ); ?>
	            	</span>
	            </button>
				<?php $fallback_cb = $walker = '';
				 	if ( class_exists( 'Ova_Megamenu_Walker_Nav_Menu' ) ) {
				      	$fallback_cb 	= 'Ova_Megamenu_Walker_Nav_Menu::fallback';
				      	$walker 		= new Ova_Megamenu_Walker_Nav_Menu;
				    }

					wp_nav_menu([
						'theme_location'  	=> $menu_slug,
						'container_class' 	=> 'primary-navigation',
						'menu'            	=> $menu_slug,
						'fallback_cb' 		=> $fallback_cb,
		                'walker'            => $walker
					]);
				?>
	        </nav>
			<?php
		}
	}

	// init widget
	$widgets_manager->register( new Tripgo_Elementor_Menu_Nav() );
}