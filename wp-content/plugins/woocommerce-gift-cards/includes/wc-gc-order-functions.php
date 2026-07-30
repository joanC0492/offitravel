<?php
/**
 * Order Functions
 *
 * @author   SomewhereWarm <info@somewherewarm.com>
 * @package  WooCommerce Gift Cards
 * @since    1.10.0
 */

/**
 * Create a new order refund programmatically.
 *
 * Returns a new refund object on success which can then be used to add additional data.
 *
 * @since 1.10.0
 *
 * @throws Exception Throws exceptions when fail to create, but returns WP_Error instead.
 *
 * @param  array $args
 * @return WC_Order_Refund|WP_Error
 */
function wc_gc_create_refund( $args = array() ) {
	$default_args = array(
		'amount'        => 0,
		'reason'        => null,
		'order_id'      => 0,
		'refund_id'     => 0,
		'line_items'    => array(),
		'restock_items' => false,
	);

	try {
		$args = wp_parse_args( $args, $default_args );

		// Prevent zero refunds.
		if ( 0 == $args['amount'] ) {
			throw new Exception( __( 'Invalid refund amount.', 'woocommerce-gift-cards' ) );
		}

		// Fetch the order.
		$order = wc_get_order( $args['order_id'] );
		if ( ! $order ) {
			throw new Exception( __( 'Invalid order ID.', 'woocommerce-gift-cards' ) );
		}

		$order_id       = $order->get_id();
		$total_captured = WC_GC()->order->get_order_total_captured( $order, 'db' );
		if ( 0 == $total_captured ) {
			throw new Exception( __( 'The gift cards applied to this order have no captured funds to refund.', 'woocommerce-gift-cards' ) );
		}

		$total_captured = wc_format_decimal( $total_captured, wc_get_price_decimals() );
		if ( 0 > $args['amount'] || $args['amount'] > $total_captured ) {
			throw new Exception( __( 'Invalid refund amount.', 'woocommerce-gift-cards' ) );
		}

		/**
		 * Action `woocommerce_gc_create_refund`.
		 *
		 * @param array $args
		 */
		do_action( 'woocommerce_gc_create_refund', $args );

		// Create the refund object.
		$refund = wc_create_refund(
			array(
				'amount'               => 0,
				'reason'               => $args['reason'],
				'order_id'             => $order_id,
				'line_items'           => $args['line_items'],
				'restock_items'        => $args['restock_items'],
				'amount_to_gift_cards' => $args['amount'],
			)
		);

		if ( is_wp_error( $refund ) ) {
			throw new Exception( $refund->get_error_message() );
		}

		/**
		 * Action `woocommerce_gc_refund_created`.
		 *
		 * @param int   $refund_id
		 * @param array $args
		 */
		do_action( 'woocommerce_gc_refund_created', $refund->get_id(), $args );

	} catch ( Exception $e ) {
		return new WP_Error( 'error', $e->getMessage() );
	}

	return $refund;
}
