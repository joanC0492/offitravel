<?php
/**
 * WC_GC_Elementor_Compatibility class
 *
 * @package  WooCommerce Gift Cards
 * @since    1.5.2
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce Elementor Compatibility.
 *
 * @version  1.5.2
 */
class WC_GC_Elementor_Compatibility {

	/**
	 * Initialize integration.
	 */
	public static function init() {
		add_action( 'elementor/widget/render_content', array( __CLASS__, 'render_add_to_cart_widget' ), 10, 2 );
	}

	/**
	 * Flex layout issues. Elementor has no hooks we can use to add a class on a higher level
	 * so, we'll add the class by search/replace in the generated markup
	 *
	 * @param string                 $widget_content The content of the widget.
	 * @param \Elementor\Widget_Base $widget The widget.
	 *
	 * @return string
	 */
	public static function render_add_to_cart_widget( $widget_content, $widget ) {

		if ( 'woocommerce-product-add-to-cart' !== $widget->get_name() ) {
			return $widget_content;
		}

		global $product;

		if ( $product && ! is_a( $product, 'WC_Product' ) ) {
			return $widget_content;
		}

		if ( ! WC_GC_Gift_Card_Product::is_gift_card( $product ) ) {
			return $widget_content;
		}

		// Space is important at the end of the $needle / $replace strings.
		$needle         = 'class="elementor-add-to-cart ';
		$replace        = 'class="elementor-add-to-cart elementor-add-to-cart-wc-gc-giftcard ';
		$found_position = strpos( $widget_content, $needle );
		if ( false !== $found_position ) {
			$widget_content = substr_replace( $widget_content, $replace, $found_position, strlen( $needle ) );
		}

		return $widget_content;
	}
}

WC_GC_Elementor_Compatibility::init();
