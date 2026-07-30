<?php
/**
 * WC_GC_SAG_Gift_Card class
 *
 * @since    1.9.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gift Card - Send As Gift gift card manager.
 *
 * @class    WC_GC_SAG_Gift_Card
 * @version  1.10.1
 */
class WC_GC_SAG_Gift_Card {

	public static function init() {}

	/**
	 * Runtime cache of already checked Gift Cards.
	 *
	 * @var array
	 */
	protected static $checked_gift_cards = array();

	/**
	 * Checks if Gift Card will be sent to the buyer.
	 *
	 * @param  WC_GC_Gift_Card $giftcard
	 * @return bool
	 */
	public static function is_a_gift( $giftcard ) {

		$is_a_gift = true;

		if ( isset( self::$checked_gift_cards[ $giftcard->get_id() ] ) ) {
			$is_a_gift = self::$checked_gift_cards[ $giftcard->get_id() ];
		} else {

			if ( ! $giftcard->get_order_item_id() ) {
				self::$checked_gift_cards[ $giftcard->get_id() ] = $is_a_gift;
				return $is_a_gift;
			}

			try {

				$order_item = new WC_Order_Item_Product( $giftcard->get_order_item_id() );
				if ( is_a( $order_item, 'WC_Order_Item' ) ) {

					$recipient_is_buyer = ( 'yes' === $order_item->get_meta( '_wc_gc_recipient_is_buyer', true ) );

					if ( $recipient_is_buyer ) {
						$is_a_gift = false;
					}
				}
			} catch ( Exception $e ) {

				// If the order doesn't exist or has been deleted,
				// check if the gift card's sender and recipient are identical to determine if it was sent as a gift.
				if ( $giftcard->get_recipient() === $giftcard->get_sender_email() ) {
					$is_a_gift = false;
				}
			}

			self::$checked_gift_cards[ $giftcard->get_id() ] = $is_a_gift;
		}

		return $is_a_gift;
	}
}

WC_GC_SAG_Gift_Card::init();
