<?php
/**
 * WC_GC_Settings class
 *
 * @package  WooCommerce Gift Cards
 * @since    1.7.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_GC_Settings' ) ) :

	/**
	 * WooCommerce Gift Cards Settings.
	 *
	 * @class    WC_GC_Settings
	 * @version  1.7.3
	 */
	class WC_GC_Settings extends WC_Settings_Page {

		/**
		 * Constructor.
		 */
		public function __construct() {

			$this->id    = 'gc_settings';
			$this->label = __( 'Gift Cards', 'woocommerce-gift-cards' );

			// Add settings page.
			add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			// Output sections.
			add_action( 'woocommerce_sections_' . $this->id, array( $this, 'output_sections' ) );
			// Output content.
			add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
			// Process + save data.
			add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
			// Handle the reverse Cart features setting value.
			add_filter( 'woocommerce_admin_settings_sanitize_option', array( $this, 'manage_settings' ), 10, 2 );
		}

		/**
		 * Get settings array.
		 *
		 * @return array
		 */
		public function get_settings() {

			$settings = array(

				array(
					'title' => __( 'Accounts', 'woocommerce-gift-cards' ),
					'type'  => 'title',
					'id'    => 'gc_settings_account',
				),

				array(
					'title'    => __( 'Enable account features', 'woocommerce-gift-cards' ),
					'desc'     => __( 'Allow customers to store gift cards in their account, and pay for orders using their account balance', 'woocommerce-gift-cards' ),
					'id'       => 'wc_gc_is_redeeming_enabled',
					'default'  => 'yes',
					'type'     => 'checkbox',
					'desc_tip' => __( 'When disabled, customers will always need to enter their gift card codes manually.', 'woocommerce-gift-cards' ),
				),

				array(
					'type' => 'sectionend',
					'id'   => 'gc_settings_account',
				),

				array(
					'title' => __( 'Cart', 'woocommerce-gift-cards' ),
					'type'  => 'title',
					'id'    => 'gc_settings_cart',
				),

				array(
					'title'    => __( 'Enable cart page features', 'woocommerce-gift-cards' ),
					'desc'     => __( 'Allow customers to apply gift cards in the cart page', 'woocommerce-gift-cards' ),
					'default'  => 'yes',
					'id'       => 'wc_gc_disable_cart_ui',
					'value'    => 'yes' === get_option( 'wc_gc_disable_cart_ui', 'yes' ) ? 'no' : 'yes',
					'type'     => 'checkbox',
					'desc_tip' => __( 'When disabled, applied gift card details will be displayed in the checkout page only.', 'woocommerce-gift-cards' ),
				),

				array(
					'title'    => __( 'Block gift card discounts', 'woocommerce-gift-cards' ),
					'desc'     => __( 'Prevent customers from using coupons to discount gift cards', 'woocommerce-gift-cards' ),
					'id'       => 'wc_gc_disable_coupons_with_gift_cards',
					'default'  => 'no',
					'type'     => 'checkbox',
					'desc_tip' => __( 'When enabled, <strong>Percentage discount</strong> and <strong>Fixed product discount</strong> coupons will not be applied to gift cards in the cart, and <strong>Fixed cart discount</strong> coupons will be rejected when purchasing gift cards.', 'woocommerce-gift-cards' ),
				),

				array(
					'type' => 'sectionend',
					'id'   => 'gc_settings_cart',
				),

				array(
					'title' => __( 'Emails', 'woocommerce-gift-cards' ),
					'type'  => 'title',
					'id'    => 'gc_settings_emails',
				),

				array(
					'title'    => __( 'CC', 'woocommerce-gift-cards' ),
					'desc'     => __( 'Allow carbon copying', 'woocommerce-gift-cards' ),
					'default'  => 'no',
					'id'       => 'wc_gc_allow_multiple_recipients',
					'type'     => 'checkbox',
					'desc_tip' => __( 'Enable this option to include an optional "CC" field in gift card purchase forms. Any email addresses entered in this field will receive a <strong>copy</strong> of the "Gift card received" email, in addition to the recipient(s) specified in the "To" field.', 'woocommerce-gift-cards' ),
				),

				array(
					'title'       => __( 'BCC', 'woocommerce-gift-cards' ),
					'desc'        => __( 'The email addresses entered in this field will receive blind carbon copies of all "Gift card received" and "Self-use gift card received" emails. <strong>Use with caution</strong>: Every gift card code issued in your store will be privately copied to these emails.', 'woocommerce-gift-cards' ),
					'id'          => 'wc_gc_bcc_recipients',
					'type'        => 'text',
					'placeholder' => __( 'Enter emails, separated by comma', 'woocommerce-gift-cards' ),
				),

				array(
					'type' => 'sectionend',
					'id'   => 'gc_settings_emails',
				),
			);

			if ( wc_gc_is_site_admin() ) {

				$admin_settings = array(
					array(
						'title' => __( 'Privacy', 'woocommerce-gift-cards' ),
						'type'  => 'title',
						'id'    => 'gc_settings_admin',
					),

					array(
						'title'    => __( 'Grant admin privileges to Shop Managers', 'woocommerce-gift-cards' ),
						'desc'     => __( 'Allow Shop Managers to view gift card codes', 'woocommerce-gift-cards' ),
						'id'       => 'wc_gc_unmask_codes_for_shop_managers',
						'default'  => 'no',
						'type'     => 'checkbox',
						'desc_tip' => __( 'By default, users with the Shop Manager role can see only the last 4 characters of gift card codes.', 'woocommerce-gift-cards' ),
					),

					array(
						'type' => 'sectionend',
						'id'   => 'gc_settings_admin',
					),
				);

				$settings = array_merge( $settings, $admin_settings );
			}

			return apply_filters( 'woocommerce_gc_settings', $settings );
		}

		/**
		 * Validate settings upon save.
		 *
		 * @since 1.9.0
		 *
		 * @param  string $value
		 * @param  string $option
		 * @return string
		 */
		public function manage_settings( $value, $option ) {

			if ( 'wc_gc_disable_cart_ui' == $option['id'] ) {
				$value = $this->manage_disable_ui_setting( $value );
			} elseif ( 'wc_gc_bcc_recipients' == $option['id'] ) {
				$value = $this->manage_bcc_setting( $value );
			}

			return $value;
		}

		/**
		 * Revert disable UI setting value upon save.
		 *
		 * @since 1.7.3
		 *
		 * @param  string $value
		 * @return string
		 */
		public function manage_disable_ui_setting( $value ) {

			if ( empty( $value ) ) {
				return $value;
			}

			$value = 'yes' === $value ? 'no' : 'yes';

			return $value;
		}

		/**
		 * Validate BCC value upon save.
		 *
		 * @since 1.9.0
		 *
		 * @param  string $value
		 * @return string
		 */
		public function manage_bcc_setting( $value ) {

			if ( empty( $value ) ) {
				return $value;
			}

			$email_addresses = array_unique( wc_gc_parse_email_string( sanitize_text_field( $value ) ) );
			$invalid_emails  = array();

			foreach ( $email_addresses as $email ) {
				if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
					$invalid_emails[] = $email;
				}
			}

			if ( empty( $invalid_emails ) ) {
				return $value;
			}

			WC_GC_Admin_Notices::add_notice(
				sprintf(
					/* translators: notifications count */
					_n(
						'Invalid BCC recipient email: &quot;%s&quot;',
						'Invalid BCC recipient emails: &quot;%s&quot;',
						count( $invalid_emails ),
						'woocommerce-gift-cards'
					),
					implode( ', ', $invalid_emails )
				),
				'error'
			);

			$valid_emails = array_diff( $email_addresses, $invalid_emails );

			return implode( ', ', $valid_emails );
		}
	}

endif;

return new WC_GC_Settings();
