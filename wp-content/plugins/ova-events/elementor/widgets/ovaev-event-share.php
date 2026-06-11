<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class OVAEV_Elementor_Event_Share
 */
if ( !class_exists( 'OVAEV_Elementor_Event_Share' ) ) {

	class OVAEV_Elementor_Event_Share extends \Elementor\Widget_Base {

		/**
		 * Get widget name
		 */
		public function get_name() {		
			return 'ova_event_share';
		}

		/**
		 * Get widget title
		 */
		public function get_title() {
			return esc_html__( 'Event Share', 'ovaev' );
		}

		/**
		 * Get widget icon
		 */
		public function get_icon() {
			return 'eicon-share';
		}

		/**
		 * Get widget categorie
		 */
		public function get_categories() {
			return [ 'ovaev_template' ];
		}

		/**
		 * Register controls
		 */
		protected function register_controls() {

			$this->start_controls_section(
				'section_share',
				[
					'label' => esc_html__( 'Share', 'ovaev' ),
				]
			);

				$this->add_responsive_control(
					'align',
					[
						'label' 	=> esc_html__( 'Alignment', 'ovaev' ),
						'type' 		=> \Elementor\Controls_Manager::CHOOSE,
						'options' 	=> [
							'left' => [
								'title' => esc_html__( 'Left', 'ovaev' ),
								'icon' 	=> 'eicon-text-align-left',
							],
							'center' => [
								'title' => esc_html__( 'Center', 'ovaev' ),
								'icon' 	=> 'eicon-text-align-center',
							],
							'right' => [
								'title' => esc_html__( 'Right', 'ovaev' ),
								'icon' 	=> 'eicon-text-align-right',
							],
						],
						'default' 	=> '',
						'selectors' => [
							'{{WRAPPER}} .ovaev-event-share' => 'text-align: {{VALUE}};',
						],
					]
				);

			$this->end_controls_section(); // END

			$this->start_controls_section(
				'section_icon_style',
				[
					'label' => esc_html__( 'Icon', 'ovaev' ),
					'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->start_controls_tabs( 'tabs_icons_style' );
					
					$this->start_controls_tab(
			            'tab_icon_normal',
			            [
			                'label' => esc_html__( 'Normal', 'ovaev' ),
			            ]
			        );

						$this->add_control(
							'facebook_options_normal',
							[
								'label' 	=> esc_html__( 'Facebook Options', 'ovaev' ),
								'type' 		=> \Elementor\Controls_Manager::HEADING,
								'separator' => 'before',
							]
						);

			        	$this->add_control(
							'icon_facebook_color',
							[
								'label' 	=> esc_html__( 'Color', 'ovaev' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ovaev-event-share .share-social-icons li a.ico-facebook' => 'color: {{VALUE}};',
								],
							]
						);

						$this->add_control(
							'icon_facebook_background',
							[
								'label' 	=> esc_html__( 'Background', 'ovaev' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ovaev-event-share .share-social-icons li a.ico-facebook' => 'background-color: {{VALUE}};',
								],
							]
						);

						$this->add_control(
							'twitter_options_normal',
							[
								'label' 	=> esc_html__( 'Twitter Options', 'ovaev' ),
								'type' 		=> \Elementor\Controls_Manager::HEADING,
								'separator' => 'before',
							]
						);

			        	$this->add_control(
							'icon_twitter_color',
							[
								'label' 	=> esc_html__( 'Color', 'ovaev' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ovaev-event-share .share-social-icons li a.ico-twitter' => 'color: {{VALUE}};',
								],
							]
						);

						$this->add_control(
							'icon_twitter_background',
							[
								'label' 	=> esc_html__( 'Background', 'ovaev' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ovaev-event-share .share-social-icons li a.ico-twitter' => 'background-color: {{VALUE}};',
								],
							]
						);

						$this->add_control(
							'pinterest_options_normal',
							[
								'label' 	=> esc_html__( 'Pinterest Options', 'ovaev' ),
								'type' 		=> \Elementor\Controls_Manager::HEADING,
								'separator' => 'before',
							]
						);

			        	$this->add_control(
							'icon_pinterest_color',
							[
								'label' 	=> esc_html__( 'Color', 'ovaev' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ovaev-event-share .share-social-icons li a.ico-pinterest' => 'color: {{VALUE}};',
								],
							]
						);

						$this->add_control(
							'icon_pinterest_background',
							[
								'label' 	=> esc_html__( 'Background', 'ovaev' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ovaev-event-share .share-social-icons li a.ico-pinterest' => 'background-color: {{VALUE}};',
								],
							]
						);

						$this->add_control(
							'linkedin_options_normal',
							[
								'label' 	=> esc_html__( 'Linkedin Options', 'ovaev' ),
								'type' 		=> \Elementor\Controls_Manager::HEADING,
								'separator' => 'before',
							]
						);

			        	$this->add_control(
							'icon_linkedin_color',
							[
								'label' 	=> esc_html__( 'Color', 'ovaev' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ovaev-event-share .share-social-icons li a.ico-linkedin' => 'color: {{VALUE}};',
								],
							]
						);

						$this->add_control(
							'icon_linkedin_background',
							[
								'label' 	=> esc_html__( 'Background', 'ovaev' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ovaev-event-share .share-social-icons li a.ico-linkedin' => 'background-color: {{VALUE}};',
								],
							]
						);

			        $this->end_controls_tab(); // END

			        $this->start_controls_tab(
			            'tab_icon_hover',
			            [
			                'label' 	=> esc_html__( 'Hover', 'ovaev' ),
			            ]
			        );

			        	$this->add_control(
							'facebook_options_hover',
							[
								'label' 	=> esc_html__( 'Facebook Options', 'ovaev' ),
								'type' 		=> \Elementor\Controls_Manager::HEADING,
								'separator' => 'before',
							]
						);

			        	$this->add_control(
							'icon_facebook_color_hover',
							[
								'label' 	=> esc_html__( 'Color', 'ovaev' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ovaev-event-share .share-social-icons li a.ico-facebook:hover' => 'color: {{VALUE}};',
								],
							]
						);

						$this->add_control(
							'icon_facebook_background_hover',
							[
								'label' 	=> esc_html__( 'Background', 'ovaev' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ovaev-event-share .share-social-icons li a.ico-facebook:hover' => 'background-color: {{VALUE}} !important;',
								],
							]
						);

						$this->add_control(
							'twitter_options_hover',
							[
								'label' 	=> esc_html__( 'Twitter Options', 'ovaev' ),
								'type' 		=> \Elementor\Controls_Manager::HEADING,
								'separator' => 'before',
							]
						);

			        	$this->add_control(
							'icon_twitter_color_hover',
							[
								'label' 	=> esc_html__( 'Color', 'ovaev' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ovaev-event-share .share-social-icons li a.ico-twitter:hover' => 'color: {{VALUE}};',
								],
							]
						);

						$this->add_control(
							'icon_twitter_background_hover',
							[
								'label' 	=> esc_html__( 'Background', 'ovaev' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ovaev-event-share .share-social-icons li a.ico-twitter:hover' => 'background-color: {{VALUE}} !important;',
								],
							]
						);

						$this->add_control(
							'pinterest_options_hover',
							[
								'label' 	=> esc_html__( 'Pinterest Options', 'ovaev' ),
								'type' 		=> \Elementor\Controls_Manager::HEADING,
								'separator' => 'before',
							]
						);

			        	$this->add_control(
							'icon_pinterest_color_hover',
							[
								'label' 	=> esc_html__( 'Color', 'ovaev' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ovaev-event-share .share-social-icons li a.ico-pinterest:hover' => 'color: {{VALUE}};',
								],
							]
						);

						$this->add_control(
							'icon_pinterest_background_hover',
							[
								'label' 	=> esc_html__( 'Background', 'ovaev' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ovaev-event-share .share-social-icons li a.ico-pinterest:hover' => 'background-color: {{VALUE}} !important;',
								],
							]
						);

						$this->add_control(
							'linkedin_options_hover',
							[
								'label' 	=> esc_html__( 'Linkedin Options', 'ovaev' ),
								'type' 		=> \Elementor\Controls_Manager::HEADING,
								'separator' => 'before',
							]
						);

			        	$this->add_control(
							'icon_linkedin_color_hover',
							[
								'label' 	=> esc_html__( 'Color', 'ovaev' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ovaev-event-share .share-social-icons li a.ico-linkedin:hover' => 'color: {{VALUE}};',
								],
							]
						);

						$this->add_control(
							'icon_linkedin_background_hover',
							[
								'label' 	=> esc_html__( 'Background', 'ovaev' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ovaev-event-share .share-social-icons li a.ico-linkedin:hover' => 'background-color: {{VALUE}} !important;',
								],
							]
						);
			        $this->end_controls_tab();
				$this->end_controls_tabs();

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'icon_typography',
						'selector' 	=> '{{WRAPPER}} .ovaev-event-share .share-social-icons li a i',
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

			// Get current id
			$id = get_the_ID();

			// Get post type
			$post_type = get_post_type( $id );
			if ( empty( $post_type ) || 'event' != $post_type ):
				echo '<div class="ovaev_elementor_none"><span>' . esc_html( $this->get_title() ) . '</span></div>';
				return;
			endif; ?>
			<div class="ovaev-event-share">
				<?php echo apply_filters( 'ovaev_share_social', get_the_permalink(), get_the_title() ); ?>
			</div>
			<?php
		}
	}

	// Register new widget
	$widgets_manager->register( new OVAEV_Elementor_Event_Share() );
}