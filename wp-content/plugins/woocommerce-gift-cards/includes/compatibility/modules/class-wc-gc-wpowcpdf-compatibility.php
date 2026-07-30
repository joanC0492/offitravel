<?php
/**
 * WC_GC_WPO_WCPDF_Compatibility class
 *
 * @package  WooCommerce Gift Cards
 * @since    1.1.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce PDF Invoices & Packing Slips by Ewout Fernhout Compatibility.
 *
 * @version  1.6.0
 */
class WC_GC_WPO_WCPDF_Compatibility {

	/**
	 * Initialize integration.
	 */
	public static function init() {
		add_filter( 'wpo_wcpdf_payment_method', array( __CLASS__, 'add_invoice_payments' ), 10, 2 );
		add_filter( 'woocommerce_order_item_get_formatted_meta_data', array( __CLASS__, 'mask_invoice_message' ), 10, 2 );
	}

	/**
	 * Mask the GC message when viewing invoices.
	 *
	 * @param  string                $display_value
	 * @param  WC_Meta_Data          $meta
	 * @param  WC_Order_Item_Product $order_item
	 * @return string
	 */
	public static function mask_invoice_message( $formatted_meta, $order_item ) {

		if ( ! did_action( 'wpo_wcpdf_init_document' ) && ! did_action( 'wpo_wcpdf_regenerate_document' ) && ( isset( $_GET['action'] ) && 'generate_wpo_wcpdf' !== $_GET['action'] ) ) {
			return $formatted_meta;
		}

		$hidden_meta = array(
			'wc_gc_giftcard_message',
			'wc_gc_giftcard_delivery',
		);

		foreach ( $formatted_meta as $meta_id => $meta ) {
			if ( in_array( $meta->key, $hidden_meta ) ) {
				unset( $formatted_meta[ $meta_id ] );
			}
		}

		return $formatted_meta;
	}

	public static function add_invoice_payments( $payment, $order_document ) {
		if ( ! is_a( $order_document->order, 'WC_Order' ) ) {
			return $payment;
		}

		$has_giftcards = ! empty( $order_document->order->get_items( 'gift_card' ) );

		if ( $has_giftcards ) {
			// Init.
			$gateways = array();
			// Add Giftcards.
			$gateways[] = __( 'Gift Cards', 'woocommerce-gift-cards' );

			if ( ! empty( $payment ) ) {
				$gateways[] = $payment;
			}

			return implode( ', ', $gateways );
		}

		return $payment;
	}
}

WC_GC_WPO_WCPDF_Compatibility::init();
