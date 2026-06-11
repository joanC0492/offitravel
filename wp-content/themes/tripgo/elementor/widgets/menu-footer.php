<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'Tripgo_Elementor_Menu_Footer', false ) ) {

	class Tripgo_Elementor_Menu_Footer extends \Elementor\Widget_Base {

		/**
		 * Get widget name
		 */
		public function get_name() {
			return 'tripgo_elementor_menu_footer';
		}

		/**
		 * Get widget title
		 */
		public function get_title() {
			return esc_html__( 'Menu Footer', 'tripgo' );
		}

		/**
		 * Get widget icon
		 */
		public function get_icon() {
			return 'eicon-post-list';
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
			return [ 'tripgo-elementor-menu-footer' ];
		}
		
		/**
		 * Register controls
		 */
		protected function register_controls() {

			// Begin Menu Content
			$this->start_controls_section(
				'section_menu',
				[
					'label' => esc_html__( 'Menu', 'tripgo' ),
				]
			);

				// List menu
				$list_menu = [];

				// Default menu
				$default_menu = '';

				// Get menus
				$menus = \wp_get_nav_menus();
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
						'label' 	=> esc_html__( 'Select Menu', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::SELECT,
						'options' 	=> $list_menu,
						'default' 	=> $default_menu,
					]
				);

			$this->end_controls_section(); /* End Menu Content */

			/* Begin Menu Style */
			$this->start_controls_section(
	            'menu_style',
	            [
	                'label' => esc_html__( 'Menu', 'tripgo' ),
	                'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
	            ]
	        );

				$this->add_control(
		            'menu_bg',
		            [
		                'label' 	=> esc_html__( 'Background', 'tripgo' ),
		                'type' 		=> \Elementor\Controls_Manager::COLOR,
		                'selectors' => [
		                    '{{WRAPPER}} .ova-menu-footer .menu' => 'background-color: {{VALUE}}',
		                ],
		            ]
		        );

				$this->start_controls_tabs( 'tabs_title_style' );

					$this->start_controls_tab(
			            'tab_text_normal',
			            [
			                'label' => esc_html__( 'Normal', 'tripgo' ),
			            ]
			        );

				        $this->add_control(
				            'menu_color_normal',
				            [
				                'label' 	=> esc_html__( 'Color', 'tripgo' ),
				                'type' 		=> \Elementor\Controls_Manager::COLOR,
				                'selectors' => [
				                    '{{WRAPPER}} .ova-menu-footer .menu li > a' => 'color: {{VALUE}}',
				                ],
				            ]
				        );

				    $this->end_controls_tab();

				    $this->start_controls_tab(
			            'tab_text_hover',
			            [
			                'label' => esc_html__( 'Hover', 'tripgo' ),
			            ]
			        );
			        
				        $this->add_control(
				            'menu_color_hover',
				            [
				                'label' 	=> esc_html__( 'Color', 'tripgo' ),
				                'type' 		=> \Elementor\Controls_Manager::COLOR,
				                'selectors' => [
				                    '{{WRAPPER}} .ova-menu-footer .menu li:hover > a' => 'color: {{VALUE}}',
				                ],
				            ]
				        );

				    $this->end_controls_tab();
				$this->end_controls_tabs();

		        $this->add_responsive_control(
		            'text_margin',
		            [
		                'label' 		=> esc_html__( 'Margin', 'tripgo' ),
		                'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
		                'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
		                'selectors' 	=> [
		                    '{{WRAPPER}} .ova-menu-footer .menu li' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		                ],
		            ]
		        );

		        $this->add_responsive_control(
		            'text_padding',
		            [
		                'label' 		=> esc_html__( 'Padding', 'tripgo' ),
		                'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
		                'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
		                'selectors' 	=> [
		                    '{{WRAPPER}} .ova-menu-footer .menu li' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		                ],
		            ]
		        );

		        $this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'text_typography',
						'selector' 	=> '{{WRAPPER}} .ova-menu-footer .menu li a',
					]
				);

	        $this->end_controls_section(); /* End Menu Style */

			/* Begin Menu Style */
			$this->start_controls_section(
	            'sub_menu_style',
	            [
	                'label' => esc_html__( 'Sub Menu', 'tripgo' ),
	                'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
	            ]
	        );

				$this->add_control(
		            'sub_menu_bg',
		            [
		                'label' 	=> esc_html__( 'Background', 'tripgo' ),
		                'type' 		=> \Elementor\Controls_Manager::COLOR,
		                'selectors' => [
		                    '{{WRAPPER}} .ova-menu-footer .menu li .sub-menu' => 'background-color: {{VALUE}}',
		                ],
		            ]
		        );

				$this->start_controls_tabs( 'tabs_subtitle_style' );

					$this->start_controls_tab(
			            'tab_subtext_normal',
			            [
			                'label' => esc_html__( 'Normal', 'tripgo' ),
			            ]
			        );

				        $this->add_control(
				            'sub_menu_color_normal',
				            [
				                'label' 	=> esc_html__( 'Color', 'tripgo' ),
				                'type' 		=> \Elementor\Controls_Manager::COLOR,
				                'selectors' => [
				                    '{{WRAPPER}} .ova-menu-footer .menu li .sub-menu li a' => 'color: {{VALUE}}',
				                ],
				            ]
				        );

				    $this->end_controls_tab();

				    $this->start_controls_tab(
			            'tab_subtext_hover',
			            [
			                'label' => esc_html__( 'Hover', 'tripgo' ),
			            ]
			        );
			        
				        $this->add_control(
				            'sub_menu_color_hover',
				            [
				                'label' 	=> esc_html__( 'Color', 'tripgo' ),
				                'type' 		=> \Elementor\Controls_Manager::COLOR,
				                'selectors' => [
				                    '{{WRAPPER}} .ova-menu-footer .menu li .sub-menu li:hover a' => 'color: {{VALUE}}',
				                ],
				            ]
				        );

				    $this->end_controls_tab();
				$this->end_controls_tabs();

		        $this->add_responsive_control(
		            'subtext_margin',
		            [
		                'label' 		=> esc_html__( 'Margin', 'tripgo' ),
		                'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
		                'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
		                'selectors' 	=> [
		                    '{{WRAPPER}} .ova-menu-footer .menu li .sub-menu li' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		                ],
		            ]
		        );

		        $this->add_responsive_control(
		            'subtext_padding',
		            [
		                'label' 		=> esc_html__( 'Padding', 'tripgo' ),
		                'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
		                'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
		                'selectors' 	=> [
		                    '{{WRAPPER}} .ova-menu-footer .menu li .sub-menu li' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		                ],
		            ]
		        );

		        $this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'subtext_typography',
						'selector' 	=> '{{WRAPPER}} .ova-menu-footer .menu li .sub-menu li a',
					]
				);

	        $this->end_controls_section(); /* End Menu Style */
		}

		/**
		 * Render template
		 */
		protected function render() {
			// Get settings
			$settings = $this->get_settings_for_display();

			// Get menu slug
			$menu_slug = tripgo_get_meta_data( 'menu_slug', $settings );

			?>
			<div class="ova-menu-footer">
				<?php wp_nav_menu([
					'menu'              => $menu_slug,
					'container'         => '',
					'container_class'   => '',
					'container_id'      => '',
				]); ?>
			</div>
			<?php
		}
	}

	// init widget
	$widgets_manager->register( new Tripgo_Elementor_Menu_Footer() );
}