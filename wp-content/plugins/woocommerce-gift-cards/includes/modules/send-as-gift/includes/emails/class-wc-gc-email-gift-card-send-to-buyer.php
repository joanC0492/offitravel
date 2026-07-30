<?php
/**
 * WC_GC_Email_Gift_Card_Send_To_Buyer class
 *
 * @since    1.9.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_GC_Email_Gift_Card_Send_To_Buyer', false ) ) :

	/**
	 * Gift Card Send_To_Buyer email controller.
	 *
	 * @class    WC_GC_Email_Gift_Card_Send_To_Buyer
	 * @version  2.7.2
	 */
	class WC_GC_Email_Gift_Card_Send_To_Buyer extends WC_GC_Email_Gift_Card_Received {

		/**
		 * Current giftcard object.
		 *
		 * @var WC_GC_Gift_Card
		 */
		protected $giftcard;

		/**
		 * Constructor.
		 */
		public function __construct() {

			$this->id             = 'gift_card_send_to_buyer';
			$this->customer_email = true;
			$this->title          = __( 'Self-use gift card received', 'woocommerce-gift-cards' );
			$this->description    = __( 'Emails sent to customers purchasing gift cards for themselves (not as a gift) once their gift card code has been issued and activated.', 'woocommerce-gift-cards' );
			if ( property_exists( $this, 'email_group' ) ) {
				// this property exists since WC 10.3.0.
				$this->email_group = WC_GC_Emails::EMAIL_GROUP;
			}

			$this->template_plain = 'emails/plain/gift-card-received.php';
			$this->template_base  = WC_GC()->get_plugin_path() . '/templates/';

			// Call grand-parent constructor.
			WC_Email::__construct();
		}

		/*
		---------------------------------------------------*/
		/*
			Triggers.                                        */
		/*---------------------------------------------------*/

		/**
		 * Process giftcards.
		 *
		 * @param array          $giftcards
		 * @param int            $order_id
		 * @param WC_Order|false $order
		 */
		public function process_giftcards( $giftcards, $order_id, $order = false ) {
			$this->setup_locale();

			foreach ( $giftcards as $giftcard_id ) {
				$giftcard = new WC_GC_Gift_card( $giftcard_id );
				if ( ! $giftcard->get_id() ) {
					continue;
				}

				$this->trigger( $giftcard, $order_id, $order );
			}

			$this->restore_locale();
		}

		/**
		 * Trigger the sending of this email.
		 *
		 * @param WC_GC_Gift_Card $giftcard
		 */
		public function force_trigger( $giftcard ) {
			$this->setup_locale();

			if ( is_numeric( $giftcard ) || is_a( $giftcard, 'WC_GC_Gift_Card_Data' ) ) {
				$giftcard = new WC_GC_Gift_Card( $giftcard );
			}

			if ( ! is_a( $giftcard, 'WC_GC_Gift_Card' ) || ! $giftcard->get_id() ) {
				return;
			}

			$this->giftcard  = $giftcard;
			$this->recipient = $giftcard->get_recipient();
			$this->set_placeholders_value();

			// Update delivery status.
			$this->giftcard->data->set_delivered( get_current_user_id() );

			$this->giftcard->data->save();

			if ( $this->is_enabled() && $this->get_recipient() ) {
				$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
			}

			$this->restore_locale();
		}

		/*
		---------------------------------------------------*/
		/*
			Defaults.                                        */
		/*---------------------------------------------------*/

		/**
		 * Get email subject.
		 *
		 * @return string
		 */
		public function get_default_subject() {
			return __( 'Your "{site_title}" gift card', 'woocommerce-gift-cards' );
		}

		/**
		 * Get email heading.
		 *
		 * @return string
		 */
		public function get_default_heading() {
			return __( 'Your gift card is here', 'woocommerce-gift-cards' );
		}

		/**
		 * Get default email gift card content.
		 *
		 * @since  1.0.0
		 *
		 * @return string
		 */
		public function get_default_intro_content() {
			return __( 'Enjoy your new gift card!', 'woocommerce-gift-cards' );
		}

		/*
		---------------------------------------------------*/
		/*
			Init.                                            */
		/*---------------------------------------------------*/

		/**
		 * Setup triggers for this e-mail.
		 *
		 * @since  1.0.0
		 */
		public function setup_hooks() {
			add_action( 'woocommerce_gc_send_gift_card_to_buyer_notification', array( $this, 'process_giftcards' ), 11, 3 );
			add_action( 'woocommerce_gc_force_send_gift_card_to_buyer_notification', array( $this, 'force_trigger' ) );
		}
	}

endif;

return new WC_GC_Email_Gift_Card_Send_To_Buyer();
