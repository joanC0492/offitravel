<?php
/**
 * Expiration reminders controller class.
 *
 * @package WooCommerce Gift Cards
 * @since 2.2.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_GC_Email_Expiration_Reminder', false ) ) :

	/**
	 * WC_GC_Email_Expiration_Reminder class.
	 *
	 * @version 2.7.2
	 */
	class WC_GC_Email_Expiration_Reminder extends WC_Email {

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
			$this->id             = 'gift_card_expiration_reminder';
			$this->customer_email = true;
			$this->title          = __( 'Gift card expiration reminder', 'woocommerce-gift-cards' );
			$this->description    = __( 'Emails sent to gift card recipients to remind them that their gift card is about to expire.', 'woocommerce-gift-cards' );
			if ( property_exists( $this, 'email_group' ) ) {
				// this property exists since WC 10.3.0.
				$this->email_group = WC_GC_Emails::EMAIL_GROUP;
			}

			$this->template_base  = WC_GC_ABSPATH . 'includes/modules/expiration-reminders/templates/';
			$this->template_html  = 'emails/gift-card-expiration-reminder.php';
			$this->template_plain = 'emails/plain/gift-card-expiration-reminder.php';

			$this->setup_placeholders();

			// Call parent constructor.
			parent::__construct();
		}

		/**
		 * Setup hooks.
		 */
		public function setup_hooks() {
			add_action( 'woocommerce_gc_send_expiration_reminder_email', array( $this, 'trigger' ), 10, 1 );
		}

		/**
		 * Trigger the email.
		 *
		 * @throws Exception Email failed to send.
		 * @param WC_GC_Gift_Card $gift_card Gift card object.
		 */
		public function trigger( WC_GC_Gift_Card $gift_card ) {
			if ( ! ( 'yes' === $this->enabled && WC_GC_Expiration_Reminders::is_enabled() && WC_GC_Expiration_Reminders::should_expiration_reminder_be_sent( $gift_card->data ) ) ) {
				return;
			}
			try {
				$this->setup_locale();
				$this->giftcard = $gift_card;
				$this->set_placeholders_value();
				$this->send(
					$gift_card->get_recipient(),
					$this->get_subject(),
					$this->get_content(),
					$this->get_headers(),
					$this->get_attachments()
				);
				$this->restore_locale();
				WC_GC()->db->activity->add(
					array(
						'gc_id'     => $gift_card->get_id(),
						'type'      => 'reminder_sent',
						'object_id' => $gift_card->get_order_id(),
						'amount'    => $gift_card->get_balance(),
					)
				);
			} catch ( Exception $e ) {
				$this->restore_locale();
				throw $e;
			}
		}

		/**
		 * Setup placeholders.
		 */
		protected function setup_placeholders() {
			/**
			 * Filter email placeholders.
			 *
			 * @since 2.2.0
			 */
			$placeholder_keys = (array) apply_filters(
				'woocommerce_gc_email_placeholders',
				array(
					'giftcard_from',
				)
			);

			$placeholders = array();
			foreach ( $placeholder_keys as $placeholder_key ) {
				$placeholders[ '{' . $placeholder_key . '}' ] = '';
			}

			$this->placeholders = $placeholders;
		}

		/**
		 * Set placeholders.
		 */
		public function set_placeholders_value() {

			$this->placeholders['{giftcard_from}'] = html_entity_decode( wptexturize( $this->giftcard->get_sender() ), ENT_QUOTES );

			foreach ( $this->placeholders as $key => $value ) {
				/**
				 * Filter email placeholder value.
				 *
				 * @param string $value Placeholder value.
				 * @param WC_GC_Gift_Card $gift_card Gift card object.
				 *
				 * @since 2.2.0
				 */
				$this->placeholders[ $key ] = apply_filters( 'woocommerce_gc_email_placeholder_' . sanitize_title( $key ) . '_value', $value, $this->giftcard );
			}
		}

		/**
		 * Set giftcard.
		 *
		 * @param WC_GC_Gift_Card $giftcard Gift card object.
		 */
		public function set_gift_card( WC_GC_Gift_Card $giftcard ): void {
			$this->giftcard = $giftcard;
		}

		/**
		 * Get giftcard.
		 *
		 * @return WC_GC_Gift_Card
		 */
		public function get_gift_card() {
			return $this->giftcard;
		}

		/**
		 * Get email subject.
		 *
		 * @return string
		 */
		public function get_default_subject() {
			if ( ! $this->giftcard ) {
				return __( 'Your gift card is about to expire', 'woocommerce-gift-cards' );
			}
			return $this->giftcard->get_recipient() === $this->giftcard->get_sender_email() ? __( 'Your gift card is about to expire', 'woocommerce-gift-cards' ) : __( 'Your gift card from {giftcard_from} is about to expire', 'woocommerce-gift-cards' );
		}

		/**
		 * Get email heading.
		 *
		 * @return string
		 */
		public function get_default_heading() {
			return __( 'Last chance!', 'woocommerce-gift-cards' );
		}

		/**
		 * Get default email gift card content.
		 *
		 * @return string
		 */
		public function get_default_intro_content() {
			if ( ! $this->giftcard ) {
				return __( 'Your gift card is about to expire:', 'woocommerce-gift-cards' );
			}
			return $this->giftcard->get_recipient() === $this->giftcard->get_sender_email() ? __( 'Your gift card is about to expire:', 'woocommerce-gift-cards' ) : __( 'Your gift card from {giftcard_from} is about to expire:', 'woocommerce-gift-cards' );
		}

		/**
		 * Get email gift card content.
		 *
		 * @return string
		 */
		public function get_intro_content() {
			/**
			 * Filter email intro content.
			 *
			 * @param string $intro_content Email intro content.
			 * @param WC_GC_Gift_Card $gift_card Gift card object.
			 * @param object|bool $object The object (ie, product or order) this email relates to, if any.
			 *
			 * @since 2.2.0
			 */
			return apply_filters( 'woocommerce_gc_expiration_reminder_email_intro_content', $this->format_string( $this->get_option( 'intro_content', $this->get_default_intro_content() ) ), $this->giftcard, $this->object, $this );
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

			add_filter( 'woocommerce_email_styles', array( $this, 'add_stylesheets' ), 10, 2 );

			$template_args = array_merge( $template_args, $this->get_template_args( $this ) );

			// Get the template.
			return wc_get_template_html(
				$this->template_html,
				$template_args,
				false,
				WC_GC_ABSPATH . 'includes/modules/expiration-reminders/templates/'
			);
		}

		/**
		 * Style Giftcard template.
		 *
		 * @param  string   $css Stylesheet.
		 * @param  WC_Email $email Email object.
		 * @return string
		 */
		public function add_stylesheets( $css, $email = null ) {
			// Hint: $email param is not added until WC 3.6.

			if ( is_null( $email ) || 'gift_card_expiration_reminder' !== $email->id ) {
				return $css;
			}

			// Background color.
			$bg = get_option( 'woocommerce_email_background_color' );
			// General text.
			$text = get_option( 'woocommerce_email_text_color' );

			// Email body background color.
			$body = get_option( 'woocommerce_email_body_background_color' );
			// phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
			$code_text = (string) apply_filters( 'woocommerce_gc_email_code_text_color', wc_light_or_dark( $body, '#2F2F2F', '#aaaaaa' ), $email );
			// phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
			$mesage_text = (string) apply_filters( 'woocommerce_gc_email_message_text_color', wc_light_or_dark( $text, wc_gc_adjust_color_brightness( $text, -20 ), wc_gc_adjust_color_brightness( $text, 20 ) ), $email );
			// phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
			$complementary_bg = (string) apply_filters( 'woocommerce_gc_email_card_background_color', wc_light_or_dark( $body, wc_gc_adjust_color_brightness( $body, -10 ), wc_gc_adjust_color_brightness( $body, 15 ) ), $email );

			// Primary color.
			$base = get_option( 'woocommerce_email_base_color' );
			// phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
			$base_text = (string) apply_filters( 'woocommerce_gc_email_base_text_color', wc_light_or_dark( $base, '#202020', '#ffffff' ), $email );
			// phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
			$amount_text = (string) apply_filters( 'woocommerce_gc_email_amount_text_color', wc_gc_get_color_diff( $complementary_bg, $base ) >= 230 ? $base : $text, $email );

			ob_start();
			?>
		#header_wrapper h1 {
			line-height: 1em !important;
		}
		#giftcard__container {
			margin-bottom: 20px;
			color: <?php echo esc_attr( $text ); ?>;
		}
		#giftcard__body {
			margin-bottom: 20px;
		}
		#giftcard__message {
			padding: 10px 0 10px 15px;
			font-style: italic;
			color: <?php echo esc_attr( $mesage_text ); ?>;
			border-left: 5px solid <?php echo esc_attr( $complementary_bg ); ?>;
			margin-bottom: 28px;
		}
		#giftcard__card-header {
			background-color: <?php echo esc_attr( $base ); ?>;
			margin-top: -20px;
			margin-left: -20px;
			margin-right: -20px;
			margin-bottom: 20px;
			background-size: cover;
		}
		#giftcard__card-image {
			margin-bottom: 20px;
		}
		#giftcard__card-image img {
			margin-right: 0;
		}
		#giftcard__card {
			padding: 20px 20px;
			text-align: center;
			background: <?php echo esc_attr( $complementary_bg ); ?>;
			font-family: "Helvetica Neue", Helvetica, Roboto, Arial, sans-serif;
			width: 100%;
		}
		#giftcard__card-amount {
			font-size: 42px;
			display: block;
			line-height: 42px;
			font-weight: bold;
			color: <?php echo esc_attr( $amount_text ); ?>;
			padding: 3px 0;
			margin-bottom: 20px;
		}
		#giftcard__separator {
			color: <?php echo esc_attr( $text ); ?>;
			opacity: 0.7;
			display: block;
			margin-top: 10px;
			margin-bottom: 10px;
		}
		#giftcard__card-code {
			color: <?php echo esc_attr( $code_text ); ?>;
			font-weight: bold;
			margin-top: 4px;
			font-size: 14px;
			line-height: 16px;
			border: 1px solid <?php echo esc_attr( $code_text ); ?>;
			padding-top: 5px;
			padding-bottom: 5px;
			padding-left: 10px;
			padding-right: 10px;
			white-space: nowrap;
		}
		#giftcard__action-button {
			text-decoration: none;
			display: inline-block;
			background: <?php echo esc_attr( $base ); ?>;
			color: <?php echo esc_attr( $base_text ); ?>;
			border: 10px solid <?php echo esc_attr( $base ); ?>;
		}
		#giftcard__action-button:focus {
			outline: 2px solid <?php echo esc_attr( $base ); ?>;
			outline-offset: 2px;
		}
		#giftcard__action-button.shop-action {
			text-transform: uppercase;
		}
		#giftcard__expiration {
			text-transform: uppercase;
			margin-top: 20px;
			font-size: 0.8em;
		}
			<?php
			$css .= ob_get_clean();

			return $css;
		}

		/**
		 * Get template args.
		 *
		 * @param  WC_Email $email Email object.
		 * @return array
		 */
		public function get_template_args( $email ) {

			$template_args = array();

			// Get the product instance.
			try {
				$order_item = new WC_Order_Item_Product( $email->get_gift_card()->get_order_item_id() );
				$product    = $order_item->get_product();
			} catch ( Exception $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
				// ...
			}

			$include_header = false;

			if ( isset( $product ) && is_a( $product, 'WC_Product' ) ) {
				$use_image = $product->get_meta( '_gift_card_template_default_use_image', true );

				// Backwards compatibility default.
				if ( empty( $use_image ) ) {
					$use_image = 'product';
				}

				if ( 'product' === $use_image ) {

					$image_id = $product->get_image_id();
					if ( $image_id ) {
						// phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
						$image_src = wp_get_attachment_image_src( $image_id, apply_filters( 'wooocommerce_gc_email_gift_card_image_size', 'woocommerce_single', $product, $email ) );
					}
				} elseif ( 'custom' === $use_image ) {

					$image_id = $product->get_meta( '_gift_card_template_default_custom_image', true );
					if ( $image_id ) {
						// phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
						$image_src = wp_get_attachment_image_src( $image_id, apply_filters( 'wooocommerce_gc_email_gift_card_custom_image_size', 'large', $product, $email ) );
					}
				}

				if ( ! empty( $image_src ) ) {

					$include_header = true;

					// Design args.
					$template_args['image_src']  = $image_src[0];
					$template_args['height']     = 200;
					$template_args['position_X'] = '50%';
					$template_args['position_Y'] = '50%';
				}
			}

				$template_args['include_header'] = $include_header;
				$template_args['render_image']   = 'element';

				return $template_args;
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
				WC_GC_ABSPATH . 'includes/modules/expiration-reminders/templates/'
			);
		}
	}

endif;

return new WC_GC_Email_Expiration_Reminder();
