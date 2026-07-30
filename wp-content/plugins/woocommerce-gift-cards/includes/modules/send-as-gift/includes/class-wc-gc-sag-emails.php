<?php
/**
 * WC_GC_SAG_Emails class
 *
 * @since    1.9.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gift Card - Send As Gift emails manager.
 *
 * @class    WC_GC_SAG_Emails
 * @version  1.9.0
 */
class WC_GC_SAG_Emails {

	/**
	 * Constructor.
	 */
	public static function init() {

		add_filter( 'woocommerce_gc_allowed_cc_bcc_email_ids', array( __CLASS__, 'support_bcc_recipients' ) );

		add_filter( 'woocommerce_email_actions', array( __CLASS__, 'email_actions' ) );
		add_filter( 'woocommerce_email_classes', array( __CLASS__, 'email_classes' ) );
		add_filter( 'woocommerce_gc_emails_to_style', array( __CLASS__, 'style_emails' ) );

		add_filter( 'woocommerce_gc_send_gift_card_hook', array( __CLASS__, 'register_send_to_buyer_hook' ), 10, 2 );
		add_filter( 'woocommerce_gc_force_send_gift_card_hook', array( __CLASS__, 'register_force_send_to_buyer_hook' ), 10, 2 );
	}

	/**
	 * Support BCC recipients in "Send to Me" emails.
	 *
	 * @param array $supported_ids
	 *
	 * @return array
	 */
	public static function support_bcc_recipients( $supported_ids ) {
		$supported_ids[] = 'gift_card_send_to_buyer';
		return $supported_ids;
	}

	/**
	 * Registers custom emails actions.
	 *
	 * @param  array $actions
	 * @return array
	 */
	public static function email_actions( $actions ) {
		$actions[] = 'woocommerce_gc_send_gift_card_to_buyer';
		$actions[] = 'woocommerce_gc_force_send_gift_card_to_buyer';

		return $actions;
	}

	/**
	 * Registers custom emails classes.
	 *
	 * @param  array $emails
	 * @return array
	 */
	public static function email_classes( $emails ) {
		$emails['WC_GC_Email_Gift_Card_Send_To_Buyer'] = include 'emails/class-wc-gc-email-gift-card-send-to-buyer.php';

		if ( is_a( $emails['WC_GC_Email_Gift_Card_Send_To_Buyer'], 'WC_Email' ) ) {
			$emails['WC_GC_Email_Gift_Card_Send_To_Buyer']->setup_hooks();
		}

		return $emails;
	}

	/**
	 * Applies default e-mail styles to SAG emails.
	 *
	 * @param  array $emails
	 * @return array
	 */
	public static function style_emails( $emails ) {
		$emails[] = 'gift_card_send_to_buyer';

		return $emails;
	}

	/**
	 * Filters action hook name for sending Gift Card emails.
	 *
	 * @param  string $hook
	 * @param  array  $giftcards
	 * @return string
	 */
	public static function register_send_to_buyer_hook( $hook, $giftcards ) {

		if ( ! is_array( $giftcards ) || empty( $giftcards ) ) {
			return $hook;
		}

		foreach ( $giftcards as $giftcard_id ) {

			$giftcard = new WC_GC_Gift_card( $giftcard_id );
			if ( ! WC_GC_SAG_Gift_Card::is_a_gift( $giftcard ) ) {
				$hook = 'woocommerce_gc_send_gift_card_to_buyer';
			}
		}

		return $hook;
	}

	/**
	 * Filters action hook name for sending Gift Card emails.
	 *
	 * @param  string          $hook
	 * @param  WC_GC_Gift_Card $giftcard
	 * @return string
	 */
	public static function register_force_send_to_buyer_hook( $hook, $giftcard ) {

		if ( ! WC_GC_SAG_Gift_Card::is_a_gift( $giftcard ) ) {
			$hook = 'woocommerce_gc_force_send_gift_card_to_buyer';
		}

		return $hook;
	}
}
WC_GC_SAG_Emails::init();
