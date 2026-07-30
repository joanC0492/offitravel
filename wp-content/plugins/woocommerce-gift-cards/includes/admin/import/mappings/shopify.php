<?php
/**
 * Shopify Gift Card columns mapping.
 *
 * @package  WooCommerce Gift Cards
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add Shopify mappings.
 *
 * @since  1.6.0
 *
 * @param  array $mappings     Importer columns mappings.
 * @param  array $raw_headers  Raw headers from CSV being imported.
 * @return array
 */
function wc_gc_importer_shopify_mappings( $mappings, $raw_headers ) {

	// Only map if this is looks like a Shopify export.
	if ( 0 !== count( array_diff( array( 'Last Characters' ), $raw_headers ) ) ) {
		return $mappings;
	}

	$shopify_mappings = array(
		'Customer Name' => 'sender',
		'Email'         => 'recipient',
		'Created At'    => 'create_date',
		'Expires On'    => 'expire_date',
		'Initial Value' => 'balance',
		'Balance'       => 'remaining',
		'Enabled?'      => 'is_active',
	);

	return array_merge( $mappings, $shopify_mappings );
}
add_filter( 'woocommerce_gc_giftcards_csv_import_mapping_default_columns', 'wc_gc_importer_shopify_mappings', 101, 2 );

/**
 * Removes the `ID` mapping for Shopify.
 *
 * @since  1.6.0
 *
 * @param  array $mappings     Importer columns mappings.
 * @param  array $raw_headers  Raw headers from CSV being imported.
 * @return array
 */
function wc_gc_importer_shopify_mappings_remove_id( $headers, $raw_headers ) {

	// Only change this if this is looks like a Shopify export.
	if ( 0 !== count( array_diff( array( 'Last Characters', 'Initial Value' ), $raw_headers ) ) ) {
		return $headers;
	}

	// Search for id and remove it.
	foreach ( $headers as $index => $normalized_header ) {
		if ( 'id' === $normalized_header ) {
			unset( $headers[ $index ] );
		}
	}

	return $headers;
}
add_filter( 'woocommerce_gc_giftcards_csv_import_mapped_columns', 'wc_gc_importer_shopify_mappings_remove_id', 10, 2 );
