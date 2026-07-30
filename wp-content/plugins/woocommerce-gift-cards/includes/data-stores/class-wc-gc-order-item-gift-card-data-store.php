<?php
/**
 * WC_Order_Item_Gift_Card_Data_Store class
 *
 * @package  WooCommerce Gift Cards
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Order_Item_Gift_Card_Data_Store class.
 *
 * @version 1.0.0
 */
class WC_Order_Item_Gift_Card_Data_Store extends Abstract_WC_Order_Item_Type_Data_Store implements WC_Object_Data_Store_Interface, WC_Order_Item_Type_Data_Store_Interface {

	/**
	 * Data stored in meta keys.
	 *
	 * @var array
	 */
	protected $internal_meta_keys = array( 'giftcard_id', 'code', 'amount' );

	/**
	 * Read/populate data properties specific to this order item.
	 *
	 * @param WC_Order_Item_Gift_Card $item
	 */
	public function read( &$item ) {
		parent::read( $item );
		$id = $item->get_id();
		$item->set_props(
			array(
				'giftcard_id' => get_metadata( 'order_item', $id, 'giftcard_id', true ),
				'code'        => get_metadata( 'order_item', $id, 'code', true ),
				'amount'      => get_metadata( 'order_item', $id, 'amount', true ),
			)
		);
		$item->set_object_read( true );
	}

	/**
	 * Saves an item's data to the database / item meta.
	 * Ran after both create and update, so $id will be set.
	 *
	 * @param WC_Order_Item_Gift_Card $item
	 */
	public function save_item_data( &$item ) {
		$id          = $item->get_id();
		$save_values = array(
			'giftcard_id' => $item->get_giftcard_id( 'edit' ),
			'code'        => $item->get_code( 'edit' ),
			'amount'      => $item->get_amount( 'edit' ),
		);
		foreach ( $save_values as $key => $value ) {
			update_metadata( 'order_item', $id, $key, $value );
		}
	}
}
