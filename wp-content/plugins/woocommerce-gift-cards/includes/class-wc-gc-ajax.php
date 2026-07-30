<?php
/**
 * WC_GC_Ajax class
 *
 * @package  WooCommerce Gift Cards
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Front-end AJAX filters for 'get_variation'.
 *
 * @class    WC_GC_Ajax
 * @version  1.0.0
 */
class WC_GC_Ajax {

	/**
	 * Hook in.
	 */
	public static function init() {
		add_action( 'wc_ajax_apply_gift_card_to_session', array( __CLASS__, 'apply_gift_card_to_session' ) );
		add_action( 'wc_ajax_remove_gift_card_from_session', array( __CLASS__, 'remove_gift_card_from_session' ) );
		add_action( 'wc_ajax_toggle_balance_usage', array( __CLASS__, 'toggle_balance_usage' ) );
	}

	/**
	 * Toggle balance usage.
	 */
	public static function toggle_balance_usage() {

		check_ajax_referer( 'update-use-balance', 'security' );

		$use = isset( $_POST['wc_gc_use_balance'] ) && 'on' === $_POST['wc_gc_use_balance'] ? true : false;
		WC_GC()->account->set_balance_usage( $use );
		WC_AJAX::get_cart_totals();
	}

	/**
	 * Apply a gift card code to session.
	 */
	public static function apply_gift_card_to_session() {

		check_ajax_referer( 'redeem-card', 'security' );
		$args = wc_clean( $_POST );

		$args        = wc_clean( $_POST );
		$is_checkout = isset( $args['wc_gc_is_checkout'] ) && 'yes' === $args['wc_gc_is_checkout'] ? true : false;

		$applied = WC_GC()->cart->process_gift_card_cart_form( $args );

		if ( ! $is_checkout ) {
			wc_maybe_define_constant( 'WOOCOMMERCE_CART', true );

			ob_start();
			WC()->cart->calculate_totals();
			woocommerce_cart_totals();
			$html = ob_get_clean();

		} else {
			wc_maybe_define_constant( 'WOOCOMMERCE_CHECKOUT', true );
			$html = '';
		}

		// Grap notices.
		ob_start();
		WC_GC()->cart->display_notices();
		$notices_html = ob_get_clean();

		$response = array(
			'result'       => 'success',
			'applied'      => $applied ? 'yes' : 'no',
			'html'         => $html,
			'notices_html' => $notices_html,
		);

		wp_send_json( $response );
	}

	/**
	 * Remove a Gift Card from the session.
	 */
	public static function remove_gift_card_from_session() {

		check_ajax_referer( 'remove-card', 'security' );

		$giftcard_id = isset( $_POST['wc_gc_cart_id'] ) ? absint( $_POST['wc_gc_cart_id'] ) : 0;
		if ( $giftcard_id ) {
			WC_GC()->giftcards->remove_giftcard_from_session( $giftcard_id );
		}

		if ( isset( $_POST['render_cart_fragments'] ) && 1 === absint( $_POST['render_cart_fragments'] ) ) {
			WC_AJAX::get_cart_totals();
		}
	}
}

WC_GC_Ajax::init();
