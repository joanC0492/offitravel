<?php
/**
 * WC_GC_Shortcodes class
 *
 * @package  WooCommerce Gift Cards
 * @since    1.5.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * WooCommerce Gift Cards Shortcodes class.
 *
 * @version 1.16.0
 */
class WC_GC_Shortcodes {

	/**
	 * Init shortcodes.
	 */
	public static function init() {
		$shortcodes = array(
			'woocommerce_my_account_giftcards' => array( __CLASS__, 'my_account_giftcards' ),
			'woocommerce_giftcards_form'       => array( __CLASS__, 'giftcard_form' ),
		);

		foreach ( $shortcodes as $shortcode => $function ) {
			add_shortcode( apply_filters( "{$shortcode}_shortcode_tag", $shortcode ), $function );
		}
	}

	/**
	 * Shortcode Wrapper.
	 *
	 * @param  mixed $function Render function.
	 * @param  array $args     Arguments to pass through the render function.
	 * @param  array $wrapper  Attributes passed in the shortcode call.
	 * @return string
	 */
	public static function shortcode_wrapper( $function, $args = array(), $wrapper = array() ) {

		$wrapper = wp_parse_args(
			$wrapper,
			array(
				'class'  => 'woocommerce',
				'before' => null,
				'after'  => null,
			)
		);

		ob_start();

		echo empty( $wrapper['before'] ) ? '<div class="' . esc_attr( $wrapper['class'] ) . '">' : $wrapper['before']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		call_user_func( $function, $args );
		echo empty( $wrapper['after'] ) ? '</div>' : $wrapper['after']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		return ob_get_clean();
	}

	/**
	 * My account giftcards page shortcode.
	 *
	 * @param  array $atts
	 * @return string
	 */
	public static function my_account_giftcards( $atts ) {
		return self::shortcode_wrapper( array( WC_GC()->account, 'render_page' ), 1, $atts );
	}

	/**
	 * Giftcards apply form shortcode.
	 *
	 * @param  array $atts
	 * @return string
	 */
	public static function giftcard_form( $atts ) {
		return self::shortcode_wrapper( array( WC_GC()->cart, 'display_form' ), 1, $atts );
	}
}
