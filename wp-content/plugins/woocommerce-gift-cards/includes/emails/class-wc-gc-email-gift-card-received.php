<?php
/**
 * WC_GC_Email_Gift_Card_Received class
 *
 * @package  WooCommerce Gift Cards
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_GC_Email_Gift_Card_Received', false ) ) :

	/**
	 * Gift Card Received email controller.
	 *
	 * @class    WC_GC_Email_Gift_Card_Received
	 * @version  2.7.2
	 */
	class WC_GC_Email_Gift_Card_Received extends WC_Email {

		/**
		 * Current giftcard object.
		 *
		 * @var WC_GC_Gift_Card
		 */
		protected $giftcard;

		/**
		 * Template for the block editor content.
		 *
		 * Used to render the dynamic content block.
		 *
		 * @var string
		 */
		public $template_block_content = 'emails/block/custom-general-block-email.php';

		/**
		 * Constructor.
		 */
		public function __construct() {

			$this->id             = 'gift_card_received';
			$this->customer_email = true;
			$this->title          = __( 'Gift card received', 'woocommerce-gift-cards' );
			$this->description    = __( 'Emails sent to gift card recipients once their gift card code has been issued and activated.', 'woocommerce-gift-cards' );
			if ( property_exists( $this, 'email_group' ) ) {
				// this property exists since WC 10.3.0.
				$this->email_group = WC_GC_Emails::EMAIL_GROUP;
			}

			$this->template_plain = 'emails/plain/gift-card-received.php';
			$this->template_base  = WC_GC()->get_plugin_path() . '/templates/';

			$this->setup_placeholders();

			// Call parent constructor.
			parent::__construct();
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

				if ( $giftcard->get_deliver_date() > 0 ) {

					$args = array(
						'giftcard' => $giftcard->get_id(),
						'order_id' => $order_id,
					);
					WC()->queue()->schedule_single( $giftcard->get_deliver_date(), 'woocommerce_gc_schedule_send_gift_card_to_customer', $args, 'send_giftcards' );
				} else {
					$this->trigger( $giftcard, $order_id, $order );
				}
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

			// Get the product instance.
			try {
				$order_item = new WC_Order_Item_Product( $giftcard->get_order_item_id() );
				$product    = $order_item->get_product();
			} catch ( Exception $e ) {
				// ...
			}

			// Force set delivery & expiration time.
			if ( isset( $product ) && is_a( $product, 'WC_Product' ) ) {

				// If has a scheduled time, and it's not already sent, force it to now.
				if ( $giftcard->get_deliver_date() > 0 && $giftcard->get_deliver_date() <= time() ) {

					$this->giftcard->data->set_deliver_date( time() );
					$expire_in_days = absint( $product->get_meta( '_gift_card_expiration_days', true ) );
					$expire_date    = 0;

					// Re-align expiration if needed.
					if ( $expire_in_days > 0 ) {
						$base_datetime = new DateTime();
						$base_datetime->setTimestamp( $giftcard->get_deliver_date() );
						$base_datetime->add( new DateInterval( 'P' . absint( $expire_in_days ) . 'D' ) );
						$expire_date = $base_datetime->getTimestamp();
					}

					$this->giftcard->data->set_expire_date( $expire_date );
				}
			}

			$this->giftcard->data->save();

			if ( $this->is_enabled() && $this->get_recipient() ) {
				$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
			}

			$this->restore_locale();
		}

		/**
		 * Trigger the sending of this email.
		 *
		 * @param WC_GC_Gift_card $giftcard
		 * @param int             $order_id
		 * @param WC_Order|false  $order
		 */
		public function trigger( $giftcard, $order_id, $order ) {

			if ( is_numeric( $giftcard ) ) {
				$giftcard = new WC_GC_Gift_Card( $giftcard );
			}

			if ( $order_id && ! is_a( $order, 'WC_Order' ) ) {
				$order = wc_get_order( $order_id );
			}

			if ( ! is_a( $order, 'WC_Order' ) || ! $giftcard->get_id() ) {
				return;
			}

			$this->giftcard  = $giftcard;
			$this->object    = $order;
			$this->recipient = $giftcard->get_recipient();
			$this->set_placeholders_value();

			// Update status.
			$this->giftcard->data->set_active( 'on' );
			$this->giftcard->data->set_delivered( 0 ); // 0: for auto-delivery by system. (int): User ID that forced the delivery.
			$this->giftcard->data->save();

			if ( $this->is_enabled() && $this->get_recipient() ) {
				$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
			}
		}

		/**
		 * Trigger the sending of a scheduled email.
		 *
		 * @param int $giftcard
		 * @param int $order_id
		 */
		public function schedule_trigger( $giftcard, $order_id ) {

			if ( is_numeric( $giftcard ) ) {
				$giftcard = new WC_GC_Gift_Card( $giftcard );
			}

			if ( ! is_a( $giftcard, 'WC_GC_Gift_Card' ) || ! $giftcard->get_id() ) {
				return;
			}

			$order = false;
			if ( $order_id ) {
				$order = wc_get_order( $order_id );
			}

			$this->giftcard  = $giftcard;
			$this->object    = $order;
			$this->recipient = $giftcard->get_recipient();
			$this->set_placeholders_value();

			// Update status.
			$this->giftcard->data->set_active( 'on' );
			$this->giftcard->data->set_delivered( 0 );
			$this->giftcard->data->save();

			if ( $this->is_enabled() && $this->get_recipient() ) {
				$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
			}
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
			return __( '{giftcard_from} sent you a {giftcard_amount} gift card for "{site_title}"', 'woocommerce-gift-cards' );
		}

		/**
		 * Get email heading.
		 *
		 * @return string
		 */
		public function get_default_heading() {
			return __( 'Hooray!', 'woocommerce-gift-cards' );
		}

		/**
		 * Get default email gift card content.
		 *
		 * @since  1.1.0
		 *
		 * @return string
		 */
		public function get_default_intro_content() {
			return __( 'You just received a gift card from {giftcard_from}:', 'woocommerce-gift-cards' );
		}

		/**
		 * Default content to show below main email content.
		 *
		 * @return string
		 */
		public function get_default_additional_content() {
			return __( 'Thanks for shopping with us.', 'woocommerce' );
		}

		/*
		---------------------------------------------------*/
		/*
			Getters.                                         */
		/*---------------------------------------------------*/

		/**
		 * Get giftcard.
		 *
		 * @since  1.2.0
		 *
		 * @return WC_GC_Gift_Card
		 */
		public function get_gift_card() {
			return $this->giftcard;
		}

		/**
		 * Get email gift card content.
		 *
		 * @since  1.1.0
		 *
		 * @return string
		 */
		public function get_intro_content() {
			$get_method = 'get_option';

			if ( is_callable( array( $this, 'get_option_or_transient' ) ) ) {
				$get_method = 'get_option_or_transient';
			}

			/**
			 * Filter the email intro content.
			 *
			 * @since 1.1.0
			 *
			 * @param string          $content
			 * @param WC_GC_Gift_Card $giftcard
			 * @param WC_Order        $order
			 * @param WC_Email        $email
			 */
			return apply_filters( 'woocommerce_gc_email_intro_content', $this->format_string( $this->$get_method( 'intro_content', $this->get_default_intro_content() ) ), $this->giftcard, $this->object, $this );
		}

		/**
		 * Get content html.
		 *
		 * @return string
		 */
		public function get_content_html() {

			// Default template params.
			$template_args = array(
				'giftcard'           => $this->get_gift_card(),
				'email_heading'      => $this->get_heading(),
				'intro_content'      => $this->get_intro_content(),
				'additional_content' => $this->get_additional_content(),
				'email'              => $this,
			);

			// Redeem button.
			$template_args['show_redeem_button'] = false;
			if ( $this->get_gift_card()->is_redeemable() ) {
				$customer = get_user_by( 'email', $this->get_gift_card()->get_recipient() );
				if ( $customer && is_a( $customer, 'WP_User' ) ) {
					$template_args['show_redeem_button'] = true;
				}
			}

			// Fetch the template.
			$template = WC_GC()->emails->get_template( $this->giftcard->get_template_id() );

			// Setup template hooks & filters.
			$template->setup();

			$template_args = array_merge( $template_args, $template->get_args( $this ) );

			// Get the template.
			return wc_get_template_html(
				$template->get_template_html(),
				$template_args,
				false,
				WC_GC()->get_plugin_path() . '/templates/'
			);
		}

		/**
		 * Get content plain.
		 *
		 * @return string
		 */
		public function get_content_plain() {
			return wc_get_template_html(
				$this->template_plain,
				array(
					'giftcard'           => $this->giftcard,
					'email_heading'      => $this->get_heading(),
					'intro_content'      => $this->get_intro_content(),
					'additional_content' => $this->get_additional_content(),
					'email'              => $this,
				),
				false,
				WC_GC()->get_plugin_path() . '/templates/'
			);
		}

		/**
		 * Get block editor email template content.
		 *
		 * @return string
		 */
		public function get_block_editor_email_template_content() {
			// Default template params.
			$template_args = array(
				'giftcard'           => $this->get_gift_card(),
				'email_heading'      => $this->get_heading(),
				'intro_content'      => $this->get_intro_content(),
				'additional_content' => $this->get_additional_content(),
				'email'              => $this,
			);

			// Redeem button.
			$template_args['show_redeem_button'] = false;
			if ( $this->get_gift_card()->is_redeemable() ) {
				$customer = get_user_by( 'email', $this->get_gift_card()->get_recipient() );
				if ( $customer && is_a( $customer, 'WP_User' ) ) {
					$template_args['show_redeem_button'] = true;
				}
			}

			return wc_get_template_html(
				$this->template_block_content,
				$template_args,
				false,
				WC_GC()->get_plugin_path() . '/templates/'
			);
		}

		/*
		---------------------------------------------------*/
		/*
			Setters.                                         */
		/*---------------------------------------------------*/

		/**
		 * Set giftcard.
		 *
		 * @since  1.8.0
		 *
		 * @param  WC_GC_Gift_Card $giftcard
		 * @return string
		 */
		public function set_gift_card( $giftcard ) {
			$this->giftcard = $giftcard;
		}

		/**
		 * Set placeholders.
		 *
		 * @since  1.8.0
		 */
		public function set_placeholders_value() {

			$this->placeholders['{giftcard_from}']   = html_entity_decode( wptexturize( $this->giftcard->get_sender() ), ENT_QUOTES );
			$this->placeholders['{giftcard_amount}'] = preg_replace( $this->plain_search, $this->plain_replace, wp_strip_all_tags( wc_price( $this->giftcard->get_balance() ) ) );

			foreach ( $this->placeholders as $key => $value ) {
				$this->placeholders[ $key ] = apply_filters( 'woocommerce_gc_email_placeholder_' . sanitize_title( $key ) . '_value', $value, $this->giftcard );
			}
		}

		/*
		---------------------------------------------------*/
		/*
			Init.                                            */
		/*---------------------------------------------------*/

		/**
		 * Initialize Settings Form Fields.
		 *
		 * @since  1.1.0
		 *
		 * @return void
		 */
		public function init_form_fields() {

			parent::init_form_fields();

			/* translators: %s: list of placeholders */
			$placeholder_text = sprintf( __( 'Available placeholders: %s', 'woocommerce' ), '<code>' . esc_html( implode( '</code>, <code>', array_keys( $this->placeholders ) ) ) . '</code>' );

			$intro_content_field = array(
				'title'       => __( 'Email content', 'woocommerce-gift-cards' ),
				'description' => __( 'Text to appear below the main email header.', 'woocommerce-gift-cards' ) . ' ' . $placeholder_text,
				'css'         => 'width:400px; height: 75px;',
				'placeholder' => $this->get_default_intro_content(),
				'type'        => 'textarea',
				'desc_tip'    => true,
			);

			// Find `heading` key.
			$inject_index = array_search( 'heading', array_keys( $this->form_fields ), true );
			if ( $inject_index ) {
				++$inject_index;
			} else {
				$inject_index = 0;
			}

			// Inject.
			$this->form_fields = array_slice( $this->form_fields, 0, $inject_index, true ) + array( 'intro_content' => $intro_content_field ) + array_slice( $this->form_fields, $inject_index, count( $this->form_fields ) - $inject_index, true );
		}

		/**
		 * Setup placeholders.
		 *
		 * @since  1.8.0
		 */
		protected function setup_placeholders() {

			$placeholder_keys = (array) apply_filters(
				'woocommerce_gc_email_placeholders',
				array(
					'giftcard_from',
					'giftcard_amount',
				)
			);

			$placeholders = array();
			foreach ( $placeholder_keys as $placeholder_key ) {
				$placeholders[ '{' . $placeholder_key . '}' ] = '';
			}

			$this->placeholders = $placeholders;
		}

		/**
		 * Setup triggers for this e-mail.
		 *
		 * @since  1.8.1
		 */
		public function setup_hooks() {
			add_action( 'woocommerce_gc_send_gift_card_to_customer_notification', array( $this, 'process_giftcards' ), 10, 3 );
			add_action( 'woocommerce_gc_force_send_gift_card_to_customer_notification', array( $this, 'force_trigger' ) );
			add_action( 'woocommerce_gc_schedule_send_gift_card_to_customer_notification', array( $this, 'schedule_trigger' ), 10, 2 );
		}
	}

endif;

return new WC_GC_Email_Gift_Card_Received();
