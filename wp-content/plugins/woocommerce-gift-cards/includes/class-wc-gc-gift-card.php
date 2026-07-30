<?php
/**
 * WC_GC_Gift_Card class
 *
 * @package  WooCommerce Gift Cards
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gift Card Controller class.
 *
 * @class    WC_GC_Gift_Card
 * @version  2.4.0
 */
class WC_GC_Gift_Card {

	/**
	 * A reference to the giftcard data object - @see WC_GC_Gift_Card_Data.
	 *
	 * @var WC_GC_Gift_Card_Data
	 */
	public $data = null;

	/**
	 * Constructor.
	 *
	 * @param  int|object $giftcard  ID to load from the DB (optional).
	 */
	public function __construct( $giftcard = null ) {

		if ( is_numeric( $giftcard ) ) {
			$this->data = WC_GC()->db->giftcards->get( absint( $giftcard ) );
		} elseif ( $giftcard instanceof WC_GC_Gift_Card_Data ) {
			$this->data = WC_GC()->db->giftcards->get( absint( $giftcard->get_id() ) );
		}
	}

	/*
	---------------------------------------------------*/
	/*
		Getters.                                         */
	/*---------------------------------------------------*/

	/**
	 * Get GiftCard ID.
	 * Returns the ID of the associated WC_GC_Gift_Card_Data object - @see WC_GC_Gift_Card_Data class.
	 *
	 * @return int|null
	 */
	public function get_id() {
		return is_object( $this->data ) ? $this->data->get_id() : null;
	}

	/**
	 * Get code.
	 *
	 * @return int|null
	 */
	public function get_code() {
		return is_object( $this->data ) ? $this->data->get_code() : null;
	}

	/**
	 * Get order ID.
	 *
	 * @return int|null
	 */
	public function get_order_id() {
		return is_object( $this->data ) ? $this->data->get_order_id() : null;
	}

	/**
	 * Get order item ID.
	 *
	 * @return int|null
	 */
	public function get_order_item_id() {
		return is_object( $this->data ) ? $this->data->get_order_item_id() : null;
	}

	/**
	 * Get recipient.
	 *
	 * @return string|null
	 */
	public function get_recipient() {
		return is_object( $this->data ) ? $this->data->get_recipient() : null;
	}

	/**
	 * Get redeemed by.
	 *
	 * @return string|null
	 */
	public function get_redeemed_by() {
		return is_object( $this->data ) ? $this->data->get_redeemed_by() : null;
	}

	/**
	 * Get sender.
	 *
	 * @return string|null
	 */
	public function get_sender() {
		return is_object( $this->data ) ? $this->data->get_sender() : null;
	}

	/**
	 * Get sender email.
	 *
	 * @return string|null
	 */
	public function get_sender_email() {
		return is_object( $this->data ) ? $this->data->get_sender_email() : null;
	}

	/**
	 * Get message.
	 *
	 * @return string|null
	 */
	public function get_message() {
		return is_object( $this->data ) ? $this->data->get_message() : null;
	}

	/**
	 * Get balance.
	 *
	 * @return float|null
	 */
	public function get_initial_balance() {
		return is_object( $this->data ) ? $this->data->get_initial_balance() : null;
	}

	/**
	 * Get remaining balance.
	 *
	 * @return float|null
	 */
	public function get_balance() {
		return is_object( $this->data ) ? $this->data->get_balance() : null;
	}

	/**
	 * Get create date.
	 *
	 * @return int|null
	 */
	public function get_date_created() {
		return is_object( $this->data ) ? $this->data->get_date_created() : null;
	}

	/**
	 * Get deliver date.
	 *
	 * @return int|null
	 */
	public function get_deliver_date() {
		return is_object( $this->data ) ? $this->data->get_deliver_date() : null;
	}

	/**
	 * Get expire date.
	 *
	 * @return int|null
	 */
	public function get_expire_date() {
		return is_object( $this->data ) ? $this->data->get_expire_date() : null;
	}

	/**
	 * Get redeem date.
	 *
	 * @return int|null
	 */
	public function get_date_redeemed() {
		return is_object( $this->data ) ? $this->data->get_date_redeemed() : null;
	}

	/**
	 * Get last modify date.
	 *
	 * @return int|null
	 *
	 * @since 2.1.0
	 */
	public function get_last_modify_date() {
		return is_object( $this->data ) ? $this->data->get_last_modify_date() : null;
	}

	/**
	 * Get template id.
	 *
	 * @since 1.2.0
	 *
	 * @return string
	 */
	public function get_template_id() {
		return is_object( $this->data ) ? $this->data->get_template_id() : null;
	}

	/**
	 * Get generated hash.
	 *
	 * @since 1.1.0
	 *
	 * @return string
	 */
	public function get_hash() {
		return is_object( $this->data ) ? $this->data->get_hash() : null;
	}

	/**
	 * Get generated hash.
	 *
	 * @since 1.6.0
	 *
	 * @param  bool $expand
	 * @return string
	 */
	public function get_pending_balance( $expand = false ) {
		return is_object( $this->data ) ? $this->data->get_pending_balance( $expand ) : null;
	}

	/*
	---------------------------------------------------*/
	/*
		Actions.                                         */
	/*---------------------------------------------------*/

	/**
	 * Redeem giftcard.
	 *
	 * @throws Exception
	 *
	 * @param  int  $user_id
	 * @param  bool $force
	 * @param  bool $log
	 * @return bool
	 */
	public function redeem( $user_id, $force = false, $log = true ) {

		if ( ! is_object( $this->data ) ) {
			throw new Exception( __( 'Gift card not found.', 'woocommerce-gift-cards' ) );
		}

		if ( ! $this->is_active() ) {
			throw new Exception( __( 'Gift card disabled.', 'woocommerce-gift-cards' ) );
		}

		if ( $this->has_expired() ) {
			throw new Exception( __( 'Gift card expired.', 'woocommerce-gift-cards' ) );
		}

		if ( apply_filters( 'woocommerce_gc_check_redeeming_email', false, $this ) ) {
			$user = get_user_by( 'id', $user_id );
			if ( ! is_a( $user, 'WP_User' ) || $user->user_email !== $this->get_recipient() ) {
				throw new Exception( __( 'Invalid gift card recipient.', 'woocommerce-gift-cards' ) );
			}
		}

		if ( $this->is_redeemed() && ! $force ) {
			throw new Exception( __( 'Gift card already linked with a customer account.', 'woocommerce-gift-cards' ) );
		}

		if ( ! $this->is_redeemable() ) {
			throw new Exception( __( 'Gift card cannot be linked to an account.', 'woocommerce-gift-cards' ) );
		}

		if ( $this->get_balance() == 0 && $this->get_pending_balance() == 0 ) {
			throw new Exception( __( 'Gift card has no remaining balance.', 'woocommerce-gift-cards' ) );
		}

		$this->data->set_redeemed_by( $user_id );
		$this->data->set_date_redeemed( time() );

		if ( $this->data->save() ) {

			if ( $log ) {

				$activity_args = array(
					'gc_id'   => $this->get_id(),
					'user_id' => $user_id,
					'type'    => 'redeemed',
					'amount'  => $this->get_balance(),
				);

				WC_GC()->db->activity->add( $activity_args );
			}

			do_action( 'woocommerce_gc_gift_card_redeemed', $user_id, $this );

			return true;
		}

		return false;
	}

	/**
	 * Use the Gift Card for an order.
	 *
	 * @throws Exception
	 *
	 * @param  float    $amount
	 * @param  WC_Order $order
	 * @param  bool     $log
	 * @return bool
	 */
	public function debit( $amount, $order, $log = true ) {

		$amount = abs( $amount );
		// Debit the balance.
		if ( ! WC_GC()->db->giftcards->debit_giftcard( $this->get_id(), $amount ) ) {
			return false;
		}

		// Re-fetch to update data model.
		$this->data->read( $this->get_id() );

		if ( $log ) {

			$user = $order->get_user();
			if ( ! $user ) {
				$user_email = $order->get_billing_email();
				$user_id    = 0;
			} else {
				$user_email = $user->user_email;
				$user_id    = $user->ID;
			}

			$activity_args = array(
				'gc_id'      => $this->get_id(),
				'user_id'    => $user_id,
				'user_email' => $user_email,
				'object_id'  => $order->get_id(),
				'type'       => 'used',
				'amount'     => $amount,
			);

			WC_GC()->db->activity->add( $activity_args );
		}

		do_action( 'woocommerce_gc_gift_card_debited', $amount, $this, $order );

		return true;
	}

	/**
	 * Credit the Gift Card.
	 *
	 * @throws Exception
	 *
	 * @return bool
	 */
	public function credit( $amount, $order, $log = true ) {

		$amount  = abs( $amount );
		$current = $this->data->get_balance();

		if ( $amount ) {
			$this->data->set_balance( $current + $amount );
		}

		if ( $this->data->save() ) {

			if ( $log ) {

				$activity_args = array(
					'gc_id'     => $this->get_id(),
					'user_id'   => $order->get_user_id(),
					'object_id' => $order->get_id(),
					'type'      => 'refunded',
					'amount'    => $amount,
				);

				WC_GC()->db->activity->add( $activity_args );
			}

			do_action( 'woocommerce_gc_gift_card_credited', $amount, $this, $order );

			return true;
		}

		return false;
	}

	/*
	---------------------------------------------------*/
	/*
		Utilities.                                       */
	/*---------------------------------------------------*/

	/**
	 * Is usable.
	 *
	 * @return bool
	 */
	public function is_usable() {
		return $this->is_active() && ! $this->has_expired() && ! $this->is_redeemed() && $this->get_balance() > 0;
	}

	/**
	 * Is active.
	 *
	 * @return bool
	 */
	public function is_active() {
		return $this->data->is_active();
	}

	/**
	 * Is virtual.
	 *
	 * @return bool
	 */
	public function is_virtual() {
		return $this->data->is_virtual();
	}

	/**
	 * Is redeemable.
	 *
	 * @since 1.0.4
	 *
	 * @return bool
	 */
	public function is_redeemable() {

		$is_redeeming_enabled = wc_gc_is_redeeming_enabled();
		$is_redeemable        = $is_redeeming_enabled && apply_filters( 'woocommerce_gc_is_redeemable', true, $this->get_id(), $this );

		return $is_redeemable;
	}

	/**
	 * Is redeemed.
	 *
	 * @return bool
	 */
	public function is_redeemed() {
		return $this->data->is_redeemed();
	}

	/**
	 * Is delivered.
	 *
	 * @return bool
	 */
	public function is_delivered() {
		return $this->data->is_delivered();
	}

	/**
	 * Has expired?.
	 *
	 * @return bool
	 */
	public function has_expired() {
		return $this->data->has_expired();
	}
}
