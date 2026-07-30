<?php
/**
 * WC_GC_SAG_Admin class
 *
 * @since    1.9.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gift Card - Send As Gift admin manager.
 *
 * @class    WC_GC_SAG_Admin
 * @version  1.9.0
 */
class WC_GC_SAG_Admin {

	/**
	 * Constructor.
	 */
	public static function init() {

		// Add admin settings to enable/disable "Send as Gift" functionality.
		add_filter( 'woocommerce_gc_settings', array( __CLASS__, 'add_send_as_gift_settings' ) );

		// Prevent admins from creating scheduled "Send to me" gift cards.
		add_filter( 'woocommerce_gc_admin_delivery_date_is_editable', array( __CLASS__, 'disable_delivery_date_field' ), 10, 2 );
	}

	/**
	 * Add admin settings to enable/disable "Send as Gift" functionality.
	 *
	 * @param  array $settings
	 * @return array
	 */
	public static function add_send_as_gift_settings( $settings ) {

		if ( $settings[0]['id'] !== 'gc_settings_product_page' ) {

			array_unshift(
				$settings,
				array(
					'title' => __( 'Gifting', 'woocommerce-gift-cards' ),
					'type'  => 'title',
					'id'    => 'gc_settings_product_page',
				),
				array(
					'type' => 'sectionend',
					'id'   => 'gc_settings_product_page',
				)
			);
		}

		$product_page_section_start = wp_list_filter(
			$settings,
			array(
				'id'   => 'gc_settings_product_page',
				'type' => 'sectionend',
			)
		);

		$send_as_gift_settings = array(
			array(
				'title'   => __( 'Send as gift?', 'woocommerce-gift-cards' ),
				'desc'    => __( 'Choose <strong>Always</strong> to require customers to specify a recipient when purchasing a gift card, <strong>Let customers decide</strong> to optionally allow customers to specify a recipient, or <strong>Never</strong> to have gift cards sent to the billing email provided on the checkout page.', 'woocommerce-gift-cards' ),
				'id'      => 'wc_gc_settings_send_as_gift_status',
				'default' => 'always',
				'type'    => 'select',
				'options' => array(
					'always' => 'Always',
					'maybe'  => 'Let customers decide',
					'never'  => 'Never',
				),
			),
		);

		array_splice( $settings, array_keys( $product_page_section_start )[0], 0, $send_as_gift_settings );

		return $settings;
	}

	/**
	 * Disable the delivery date field for "Send to me" gift cards.
	 *
	 * @param  bool            $enabled
	 * @param  WC_GC_Gift_Card $giftcard
	 * @return bool
	 */
	public static function disable_delivery_date_field( $enabled, $giftcard ) {

		if ( ! WC_GC_SAG_Gift_Card::is_a_gift( $giftcard ) ) {
			$enabled = false;
		}

		return $enabled;
	}
}

WC_GC_SAG_Admin::init();
