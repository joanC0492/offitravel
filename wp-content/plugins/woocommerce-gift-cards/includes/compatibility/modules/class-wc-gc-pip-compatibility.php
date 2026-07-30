<?php
/**
 * WC_GC_PIP_Compatibility class
 *
 * @package  WooCommerce Gift Cards
 * @since    1.1.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce Print Invoices/Packing Lists Compatibility.
 *
 * @version  1.1.1
 */
class WC_GC_PIP_Compatibility {

	/**
	 * Initialize integration.
	 */
	public static function init() {
		add_filter( 'wc_pip_document_table_footer', array( __CLASS__, 'add_payment_gateway' ), 10, 3 );
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

	/**
	 * Add payment method in the Invoice.
	 *
	 * @param  array  $rows
	 * @param  string $type
	 * @param  int    $order_id
	 * @return array
	 */
	public static function add_payment_gateway( $rows, $type, $order_id ) {

		// Has giftcards?
		$order = wc_get_order( $order_id );
		if ( is_a( $order, 'WC_Order' ) && ! empty( $order->get_items( 'gift_card' ) ) ) {

			// Append string.
			if ( ! empty( $rows['payment_method'] ) ) {
				$rows['payment_method']['value'] .= ', ' . __( 'Gift Cards', 'woocommerce-gift-cards' );
			} else {

				$payment_row = array(
					'payment_method' => '<strong class="order-payment_method">' . __( 'Payment method:', 'woocommerce' ) . '</strong>',
					'value'          => __( 'Gift Cards', 'woocommerce-gift-cards' ),
				);

				// Inject before Total.
				$total_index = array_search( 'before_giftcards', array_keys( $rows ) );
				if ( false !== $total_index ) {
					$rows = array_slice( $rows, 0, $total_index, true ) + array( 'payment_method' => $payment_row ) + array_slice( $rows, $total_index, count( $rows ) - $total_index, true );
				} else {
					$rows['payment_method'] = $payment_row;
				}
			}
		}

		return $rows;
	}
}

WC_GC_PIP_Compatibility::init();
