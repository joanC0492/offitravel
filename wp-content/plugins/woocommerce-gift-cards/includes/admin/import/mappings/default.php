<?php
/**
 * Default Gift Card columns mapping.
 *
 * @package  WooCommerce Gift Cards
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add English mapping placeholders when not using English as current language.
 *
 * @since  1.6.0
 *
 * @param  array $mappings Importer columns mappings.
 * @return array
 */
function wc_gc_importer_default_english_mappings( $mappings ) {
	if ( 'en_US' === wc_importer_current_locale() ) {
		return $mappings;
	}

	$new_mappings = array(
		'ID'               => 'id',
		'Code'             => 'code',
		'Recipient'        => 'recipient',
		'Sender'           => 'sender',
		'Sender E-mail'    => 'sender_email',
		'Message'          => 'message',
		'Issued value'     => 'balance',
		'Balance'          => 'remaining',
		'Order ID'         => 'order_id',
		'Order item ID'    => 'order_item_id',
		'Template ID'      => 'template_id',
		'Create date'      => 'create_date',
		'Delivery date'    => 'deliver_date',
		'Expiration date'  => 'expire_date',
		'Redeemed date'    => 'redeem_date',
		'Redeemed by user' => 'redeemed_by',
		'Delivered'        => 'delivered',
		'Virtual'          => 'is_virtual',
		'Status'           => 'is_active',
		'Activities'       => 'activities:json',
	);

	return array_merge( $mappings, $new_mappings );
}
add_filter( 'woocommerce_gc_giftcards_csv_import_mapping_default_columns', 'wc_gc_importer_default_english_mappings', 100 );

/**
 * Add English special mapping placeholders when not using English as current language.
 *
 * @since 1.6.0
 *
 * @param  array $mappings Importer columns mappings.
 * @return array
 */
function wc_gc_importer_default_special_english_mappings( $mappings ) {
	if ( 'en_US' === wc_importer_current_locale() ) {
		return $mappings;
	}

	$new_mappings = array(
		'Meta: %s' => 'meta:',
	);

	return array_merge( $mappings, $new_mappings );
}
add_filter( 'woocommerce_gc_giftcards_csv_import_mapping_special_columns', 'wc_gc_importer_default_special_english_mappings', 100 );
