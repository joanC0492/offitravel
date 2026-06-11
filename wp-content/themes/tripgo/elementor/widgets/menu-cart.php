<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// is woo active
if ( !tripgo_is_woo_active() ) exit;

/**
 * Class Tripgo_Elementor_Menu_Cart
 */
if ( !class_exists( 'Tripgo_Elementor_Menu_Cart', false ) ) {

	class Tripgo_Elementor_Menu_Cart extends \Elementor\Widget_Base {

		/**
		 * Get widget name
		 */
		public function get_name() {
			return 'tripgo_elementor_menu_cart';
		}

		/**
		 * Get widget title
		 */
		public function get_title() {
			return esc_html__( 'Menu Cart', 'tripgo' );
		}

		/**
		 * Get widget icon
		 */
		public function get_icon() {
			return 'eicon-cart';
		}

		/**
		 * Get categories
		 */
		public function get_categories() {
			return [ 'hf' ];
		}

		/**
		 * Get style depends
		 */
		public function get_style_depends() {
			return [ 'tripgo-elementor-menu-cart' ];
		}

		/**
		 * Get script depends
		 */
		public function get_script_depends() {
			return [ 'tripgo-elementor-menu-cart' ];
		}

		/**
		 * Register controls
		 */
		protected function register_controls() {
			// Content controls
			$this->start_controls_section(
				'section_content',
				[
					'label' => esc_html__( 'Content', 'tripgo' )
				]
			);

				$this->add_control(
					'icon',
					[
						'label' 	=> esc_html__( 'Icon', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::ICONS,
						'default' 	=> [
							'value' 	=> 'ovaicon ovaicon-shopping-cart',
							'library' 	=> 'all'
						]
					]
				);

				$this->add_control(
					'text_empty',
					[
						'label' 	=> esc_html__( 'Text Empty', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::TEXT,
						'default' 	=> esc_html__( 'Your Cart is Empty', 'tripgo' )
					]
				);

			$this->end_controls_section(); // END

			// Icon style
			$this->start_controls_section(
				'section_icon',
				[
					'label' => esc_html__( 'Icon', 'tripgo' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE
				]
			);	

				$this->add_responsive_control(
					'icon_size',
					[
						'label' 		=> esc_html__( 'Size', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::SLIDER,
						'size_units' 	=> [ 'px' ],
						'range' 		=> [
							'px' => [
								'min' 	=> 0,
								'max' 	=> 100,
								'step' 	=> 1
							]
						],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-menu-cart .cart-total i' => 'font-size: {{SIZE}}{{UNIT}};'
						]
					]
				);

				$this->add_control(
					'color_icon',
					[
						'label' 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-menu-cart .cart-total i' => 'color : {{VALUE}};'
						]
					]
				);

			$this->end_controls_section(); // END

			// Items
			$this->start_controls_section(
				'section_items',
				[
					'label' => esc_html__( 'Items', 'tripgo' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE
				]
			);	

				$this->add_responsive_control(
					'bg_items_size',
					[
						'label' 		=> esc_html__( 'Background Size', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::SLIDER,
						'size_units' 	=> [ 'px' ],
						'range' 		=> [
							'px' => [
								'min' 	=> 20,
								'max' 	=> 100,
								'step' 	=> 1
							]
						],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-menu-cart .cart-total .items' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};'
						]
					]
				);

				$this->add_control(
					'color_number',
					[
						'label' 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-menu-cart .cart-total .items' => 'color : {{VALUE}};'
						]
					]
				);

				$this->add_control(
					'bgcolor_number',
					[
						'label' 	=> esc_html__( 'Background Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-menu-cart .cart-total .items' => 'background-color : {{VALUE}};'
						]
					]
				);

			$this->end_controls_section(); // END
		}

		/**
		 * Render template
		 */
		protected function render() {
			// Get settings
			$settings = $this->get_settings_for_display();

			// Text emplty
			$text_empty = tripgo_get_meta_data( 'text_empty', $settings );

			// Icon
			$icon = tripgo_get_meta_data( 'icon', $settings );
			
			?>
			<div class="ova-menu-cart">
                <div class="cart-total">
                	<?php if ( !empty( $icon['value'] ) ) {
                		\Elementor\Icons_Manager::render_icon( $icon, [ 'aria-hidden' => 'true' ] );
                	} ?>
                    <span class="items">
                       <?php echo ( WC()->cart != null && WC()->cart->get_cart_contents_count() >= 1 ) ? WC()->cart->get_cart_contents_count() : 0; ?>
                    </span>
                </div>
                <div class="minicart">
                    <?php if ( WC()->cart != null && WC()->cart->get_cart_contents_count() >= 1 ): ?>
                    	<div class="widget_shopping_cart_content">
                    		<?php woocommerce_mini_cart(); ?>
                        </div>
                    <?php else:
                        echo esc_html( $text_empty );
                    endif; ?>
                </div>
            </div>
			<?php
		}
	}

	// init widget
	$widgets_manager->register( new Tripgo_Elementor_Menu_Cart() );
}