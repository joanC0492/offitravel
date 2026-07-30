<?php
/**
 * WC_GC_Emails class
 *
 * @package  WooCommerce Gift Cards
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gift Card emails manager.
 *
 * @class    WC_GC_Emails
 * @version  2.7.2
 */
class WC_GC_Emails {

	/**
	 * Email group slug used to group Gift Cards emails in WooCommerce.
	 */
	public const EMAIL_GROUP = 'wc-gift-cards';

	/**
	 * Email Templates collection.
	 *
	 * @since 1.2.0
	 *
	 * @var array
	 */
	private $templates;

	/**
	 * Constructor.
	 */
	public function __construct() {

		// Add CC/BCC recipients to email headers.
		add_filter( 'woocommerce_email_headers', array( $this, 'add_cc_bcc_recipients' ), 10, 4 );

		add_filter( 'woocommerce_email_actions', array( $this, 'email_actions' ) );
		add_filter( 'woocommerce_email_classes', array( $this, 'email_classes' ) );

		// Register Gift Cards email group.
		add_filter(
			'woocommerce_email_groups',
			function ( $email_groups ) {
				if ( ! is_array( $email_groups ) ) {
					return $email_groups;
				}

				$email_groups[ self::EMAIL_GROUP ] = __( 'Gift Cards', 'woocommerce-gift-cards' );
				return $email_groups;
			}
		);

		// Load Templates.
		$this->load_templates();

		// Parts.
		add_action( 'woocommerce_email_gift_card_html', array( $this, 'gift_card_email_html' ), 10, 3 );

		// Email preview.
		add_filter( 'woocommerce_prepare_email_for_preview', array( $this, 'prepare_email_for_preview' ) );
		add_filter( 'woocommerce_email_preview_email_content_setting_ids', array( $this, 'inject_setting_ids' ), 10, 2 );

		// Block editor.
		add_filter( 'woocommerce_transactional_emails_for_block_editor', array( $this, 'register_gift_card_received_email_for_block_editor' ) );
	}


	/**
	 * Get a dummy gift card.
	 *
	 * @since 2.4.0
	 * @return WC_GC_Gift_Card
	 */
	private function get_dummy_giftcard(): WC_GC_Gift_Card {
		$giftcard      = new WC_GC_Gift_Card();
		$giftcard_data = new WC_GC_Gift_Card_Data(
			array(
				'balance'     => 100,
				'remaining'   => 100,
				'sender'      => 'John Doe',
				'code'        => 'XXXX-XXXX-XXXX-XXXX',
				'expire_date' => time(),
			)
		);

		$giftcard_data->set_meta_data(
			array(
				// phpcs:ignore
				'_hash_key' => '6331867c0ba08ab8ae65aeb8924378322dd2ee863ced0f31b06e9cfaafaf3a1c', // gitleaks:allow
				'_hash_iv'  => 'c5205e95afe5893c',
			)
		);

		$giftcard->data = $giftcard_data;

		/**
		 * Filter the dummy giftcard object used in email previews.
		 *
		 * @since 2.4.0
		 * @param WC_GC_Gift_Card $giftcard The dummy gift card.
		 * @param string          $email_type The email type being previewed.
		 */
		$giftcard = apply_filters( 'woocommerce_gift_card_email_preview_dummy_gift_card', $giftcard, 'WC_GC_Email_Gift_Card_Received' );

		if ( ! $giftcard instanceof WC_GC_Gift_Card ) {
			_doing_it_wrong( __METHOD__, 'The return value of woocommerce_gift_card_email_preview_dummy_gift_card must be an instance of WC_GC_Gift_Card.', '2.4.0' );
		}

		return $giftcard;
	}

	/**
	 * Prepare email for preview.
	 *
	 * @since 2.4.0
	 * @template T of WC_Email
	 * @param T $email The email object being previewed.
	 *
	 * @return T
	 */
	public function prepare_email_for_preview( $email ) {
		if ( ! $email instanceof WC_GC_Email_Gift_Card_Received && ! $email instanceof WC_GC_Email_Expiration_Reminder ) {
			return $email;
		}

		$giftcard = $this->get_dummy_giftcard();
		$email->set_gift_card( $giftcard );
		$email->set_placeholders_value();

		return $email;
	}

	/**
	 * Inject setting ids for email preview.
	 *
	 * @since 2.4.0
	 *
	 * @param  string[] $setting_ids  The setting ids.
	 * @param  string   $email_id     The email id.
	 *
	 * @return string[]
	 */
	public function inject_setting_ids( array $setting_ids, string $email_id ): array {
		if ( 'gift_card_received' === $email_id || 'gift_card_send_to_buyer' === $email_id ) {
			$setting_ids[] = "woocommerce_{$email_id}_intro_content";
		}

		return $setting_ids;
	}

	/**
	 * Add CC/BCC recipients to email headers.
	 *
	 * @param string     $headers
	 * @param string     $email_id
	 * @param WC_Product $product
	 * @param WC_Email   $email
	 *
	 * @return string
	 */
	public function add_cc_bcc_recipients( $headers, $email_id, $product, $email ) {

		if ( ! in_array( $email_id, apply_filters( 'woocommerce_gc_allowed_cc_bcc_email_ids', array( 'gift_card_received' ), $email_id, $product, $email ) ) ) {
			return $headers;
		}

		if ( ! isset( $email ) || ! is_a( $email, 'WC_Email' ) ) {
			return $headers;
		}

		/*
		* CC
		*/
		$order    = $email->object;
		$giftcard = $email->get_gift_card();

		if ( ! is_a( $order, 'WC_Order' ) ) {
			return $headers;
		}

		$gift_card_order_item         = $order->get_item( $giftcard->get_order_item_id() );
		$gift_card_order_item_meta_cc = $gift_card_order_item->get_meta( 'wc_gc_giftcard_cc' );

		if ( ! empty( $gift_card_order_item_meta_cc ) ) {
			$gift_card_cc_emails = array_unique( wc_gc_parse_email_string( sanitize_text_field( $gift_card_order_item_meta_cc ) ) );

			if ( $this->validate_emails( $gift_card_cc_emails ) ) {
				$headers .= 'Cc: ' . $gift_card_order_item_meta_cc . " \r\n";
			}
		}

		/*
		* BCC
		*/
		$gift_card_bcc = get_option( 'wc_gc_bcc_recipients' );

		if ( ! empty( $gift_card_bcc ) ) {

			$gift_card_bcc_emails = array_unique( wc_gc_parse_email_string( sanitize_text_field( $gift_card_bcc ) ) );

			if ( $this->validate_emails( $gift_card_bcc_emails ) ) {
				$headers .= 'Bcc: ' . $gift_card_bcc . " \r\n";
			}
		}

		return $headers;
	}

	/**
	 * Registers custom emails actions.
	 *
	 * @param  array $actions
	 * @return array
	 */
	public function email_actions( $actions ) {
		$actions[] = 'woocommerce_gc_send_gift_card_to_customer';
		$actions[] = 'woocommerce_gc_force_send_gift_card_to_customer';
		$actions[] = 'woocommerce_gc_schedule_send_gift_card_to_customer';

		return $actions;
	}

	/**
	 * Registers custom emails classes.
	 *
	 * @param  array $emails
	 * @return array
	 */
	public function email_classes( $emails ) {
		$emails['WC_GC_Email_Gift_Card_Received'] = include 'emails/class-wc-gc-email-gift-card-received.php';
		if ( is_a( $emails['WC_GC_Email_Gift_Card_Received'], 'WC_Email' ) ) {
			$emails['WC_GC_Email_Gift_Card_Received']->setup_hooks();
		}
		return $emails;
	}

	/**
	 * Register this email for the block editor.
	 *
	 * @param array $emails Array of email IDs.
	 * @return array Modified array of email IDs.
	 */
	public function register_gift_card_received_email_for_block_editor( $emails ) {
		$emails[] = 'gift_card_received';
		return $emails;
	}

	/**
	 * Load email templates.
	 *
	 * @since 1.2.0
	 *
	 * @return void
	 */
	protected function load_templates() {

		$this->templates = (array) apply_filters(
			'woocommerce_gc_email_templates',
			array(
				'WC_GC_Email_Template_Default',
			)
		);

		foreach ( $this->templates as $template_class ) {
			$template                               = new $template_class();
			$this->templates[ $template->get_id() ] = $template;
		}
	}

	/**
	 * Get template object by template id.
	 *
	 * @since 1.2.0
	 *
	 * @param  string $template_id
	 * @return false|WC_GC_Email_Template
	 */
	public function get_template( $template_id ) {

		if ( ! empty( $this->templates[ $template_id ] ) ) {
			return $this->templates[ $template_id ];
		}

		return false;
	}

	/**
	 * Get template object by product.
	 *
	 * @since 1.2.0
	 *
	 * @param  WC_Product $product
	 * @return false|WC_GC_Email_Template
	 */
	public function get_template_by_product( $product ) {

		if ( ! is_a( $product, 'WC_Product' ) ) {
			return false;
		}

		$template_id = $product->get_meta( '_gift_card_template', true );
		$template_id = ! empty( $template_id ) ? $template_id : 'default';

		return $this->get_template( $template_id );
	}

	/**
	 * Prints code in the email.
	 *
	 * @param  WC_GC_Gift_Card $giftcard
	 * @param  string          $intro_content
	 * @param  WC_Email        $email
	 * @return void
	 */
	public function gift_card_email_html( $giftcard, $intro_content, $email = null ) {

		// Default template params.
		$template_args = array(
			'giftcard'      => $email->get_gift_card(),
			'intro_content' => $email->get_intro_content(),
			'email'         => $email,
		);

		// Redeem button and link.
		$template_args['show_redeem_button'] = false;
		$template_args['button_href']        = add_query_arg(
			array(
				'do_email_session' => urlencode( base64_encode( $giftcard->get_hash() ) ),
				'giftcard_id'      => $giftcard->get_id(),
			),
			apply_filters( 'woocommerce_gc_email_received_action_button_url', get_permalink( wc_get_page_id( 'shop' ) ), $giftcard )
		);

		if ( $giftcard->is_redeemable() ) {
			$customer = get_user_by( 'email', $giftcard->get_recipient() );
			if ( $customer && is_a( $customer, 'WP_User' ) ) {
				$template_args['show_redeem_button'] = true;
				$template_args['button_href']        = add_query_arg(
					array(
						'do_email_redeem' => urlencode( base64_encode( $giftcard->get_hash() ) ),
						'giftcard_id'     => $giftcard->get_id(),
					),
					wc_get_account_endpoint_url( 'giftcards' )
				);
			}
		}

		// Fetch the template.
		$template      = WC_GC()->emails->get_template( $giftcard->get_template_id() );
		$template_args = array_merge( $template_args, $template->get_args( $email ) );

		// Render giftcard part.
		ob_start();
		wc_get_template(
			'emails/html-gift-card-container.php',
			(array) apply_filters( 'woocommerce_gc_email_template_container_args', $template_args, $giftcard, $email ),
			false,
			WC_GC()->get_plugin_path() . '/templates/'
		);
		echo ob_get_clean(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/*
	|--------------------------------------------------------------------------
	| Helpers.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Validates e-mails array.
	 *
	 * @param array $emails
	 *
	 * @return bool
	 */
	public function validate_emails( $emails ) {

		$valid_emails = true;

		foreach ( $emails as $email ) {
			if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
				$valid_emails = false;
				break;
			}
		}

		return $valid_emails;
	}
}
