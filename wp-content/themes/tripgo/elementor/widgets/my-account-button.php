<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class Tripgo_Elementor_My_Account_Button
 */
if ( !class_exists( 'Tripgo_Elementor_My_Account_Button', false ) ) {

	class Tripgo_Elementor_My_Account_Button extends \Elementor\Widget_Base {

		/**
		 * Get widget name
		 */
		public function get_name() {
			return 'tripgo_elementor_my_account_button';
		}

		/**
		 * Get widget title
		 */
		public function get_title() {
			return esc_html__( 'My Account Button', 'tripgo' );
		}

		/**
		 * Get widget icon
		 */
		public function get_icon() {
			return 'eicon-dual-button';
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
			return [ 'tripgo-elementor-my-account-button' ];
		}
		
		/**
		 * Register controls
		 */
		protected function register_controls() {

			$this->start_controls_section(
				'section_content',
				[
					'label' => esc_html__( 'Content', 'tripgo' )
				]
			);	
				
				// Add Class control
				$this->add_control(
					'text_my_account_button',
					[
						'label' 		=> esc_html__( 'Text My Account Button', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::TEXT,
						'label_block' 	=> true,
						'default' 		=> esc_html__( 'My account', 'tripgo' )
					]
				);

				$this->add_control(
					'text_login_button',
					[
						'label' 		=> esc_html__( 'Text Login Button', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::TEXT,
						'label_block' 	=> true,
						'default' 		=> esc_html__( 'Login', 'tripgo' )
					]
				);

				$this->add_control(
					'text_signup_button',
					[
						'label' 		=> esc_html__( 'Text Sign Up Button', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::TEXT,
						'label_block' 	=> true,
						'default' 		=> esc_html__( 'Sign Up', 'tripgo' )
					]
				);

				$this->add_control(
					'logout_status',
					[
						'label' 		=> esc_html__( 'Logout Status', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 		=> esc_html__( 'Yes', 'tripgo' ),
						'label_off' 	=> esc_html__( 'No', 'tripgo' ),
						'return_value' 	=> 'yes',
						'default' 		=> 'no'
					]
				);

			$this->end_controls_section();

			// SECTION TAB STYLE button
			$this->start_controls_section(
				'section_button',
				[
					'label' => esc_html__( 'Button', 'tripgo' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE
				]
			);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'content_typography_title_btn',
						'label' 	=> esc_html__( 'Typography', 'tripgo' ),
						'selector' 	=> '{{WRAPPER}} .ova-my-account-button a.ma-button'
					]
				);
				
				$this->add_responsive_control(
					'padding_button',
					[
						'label' 		=> esc_html__( 'Padding', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-my-account-button a.ma-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
						]
					]
				);

				$this->add_responsive_control(
					'border_radius_button',
					[
						'label' 		=> esc_html__( 'Border radius', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-my-account-button a.ma-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
						]
					]
				);

				$this->start_controls_tabs(
					'style_tabs_button'
				);

					$this->start_controls_tab(
						'style_normal_tab',
						[
							'label' => esc_html__( 'Normal', 'tripgo' )
						]
					);

						$this->add_control(	
							'color_title_btn',
							[
								'label' 	=> esc_html__( 'Login Color', 'tripgo' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ova-my-account-button a.ma-button.login-button' => 'color : {{VALUE}};'
								]
							]
						);

						$this->add_control(
							'color_button_background',
							[
								'label' 	=> esc_html__( 'Login Background ', 'tripgo' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ova-my-account-button a.ma-button.login-button' => 'background-color : {{VALUE}};'
								]
							]
						);

						$this->add_control(	
							'color_title_signup_btn',
							[
								'label' 	=> esc_html__( 'Singup Color', 'tripgo' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ova-my-account-button a.ma-button.singup-button' => 'color : {{VALUE}};'
								]
							]
						);

						$this->add_control(
							'color_signup_button_background',
							[
								'label' 	=> esc_html__( 'Singup Background ', 'tripgo' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ova-my-account-button a.ma-button.singup-button' => 'background-color : {{VALUE}};'
								]
							]
						);

					$this->end_controls_tab();

					$this->start_controls_tab(
						'style_hover_tab',
						[
							'label' => esc_html__( 'Hover', 'tripgo' )
						]
					);

						$this->add_control(
							'color_title_btn_hover',
							[
								'label' 	=> esc_html__( 'Login Color', 'tripgo' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ova-my-account-button a.ma-button.login-button:hover' => 'color : {{VALUE}} ;'
								]
							]
						);

						$this->add_control(
							'color_button_hover_background',
							[
								'label' 	=> esc_html__( 'Login Background', 'tripgo' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ova-my-account-button a.ma-button.login-button:hover' => 'background-color : {{VALUE}};'
								]
							]
						);

						$this->add_control(
							'color_title_signup_btn_hover',
							[
								'label' 	=> esc_html__( 'Singup Color', 'tripgo' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ova-my-account-button a.ma-button.singup-button:hover' => 'color : {{VALUE}} ;'
								]
							]
						);

						$this->add_control(
							'color_signup_button_hover_background',
							[
								'label' 	=> esc_html__( 'Singup Background', 'tripgo' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ova-my-account-button a.ma-button.singup-button:hover' => 'background-color : {{VALUE}};'
								]
							]
						);

					$this->end_controls_tab();
				$this->end_controls_tabs();
			$this->end_controls_section(); // END SECTION TAB STYLE button	
		}

		/**
		 * Render template
		 */
		protected function render() {
			// Get settings
			$settings = $this->get_settings_for_display();

			// Text my account button
			$text_my_account_button = tripgo_get_meta_data( 'text_my_account_button', $settings );

			// Text login button
			$text_login_button = tripgo_get_meta_data( 'text_login_button', $settings );

			// Text signup button
			$text_signup_button = tripgo_get_meta_data( 'text_signup_button', $settings );

			// Logout status
			$logout_status = tripgo_get_meta_data( 'logout_status', $settings );

			// My account page URL
			$my_account_page_url = get_permalink( get_option('woocommerce_myaccount_page_id') );

			if ( is_user_logged_in() && 'yes' != $logout_status ):
				// Get current user
				$current_user = wp_get_current_user();
			?>
				<div class="ova-my-account-button logged-in">
					<?php if ( ( $current_user instanceof WP_User ) ) {
				        echo get_avatar( $current_user->ID, 30 );
				    } ?>
					<a href="<?php echo esc_url( $my_account_page_url ); ?>">
						<?php echo esc_html( $text_my_account_button ); ?>
					</a>
				</div>
			<?php else:
				// Login URL
				$login_url = $my_account_page_url.'#login';

				// Register URL
				$register_url = $my_account_page_url.'#register';
			?>
				<div class="ova-my-account-button logged-out">
					<a class="ma-button login-button" href="<?php echo esc_url( $login_url ); ?>" data-type="login">
						<?php echo esc_html( $text_login_button ); ?>
					</a>
					<a class="ma-button singup-button" href="<?php echo esc_url( $register_url ); ?>" data-type="register">
						<?php echo esc_html( $text_signup_button ); ?>
					</a>
				</div>
			<?php endif;
		}
	}

	// init widget
	$widgets_manager->register( new Tripgo_Elementor_My_Account_Button() );
}