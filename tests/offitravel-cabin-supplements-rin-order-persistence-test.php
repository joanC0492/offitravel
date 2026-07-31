<?php
/**
 * Temporary-order persistence test for the Rin cabin supplement snapshot.
 *
 * The test creates one local order and permanently removes only that exact ID
 * in the finally block. Existing orders are never queried or changed.
 *
 * Run with: php tests/offitravel-cabin-supplements-rin-order-persistence-test.php
 *
 * @package Offitravel
 */

require dirname( __DIR__ ) . '/wp-load.php';

$order   = null;
$failure = null;
try {
	if ( ! function_exists( 'offitravel_cabin_order_item' ) ) {
		throw new RuntimeException( 'Missing production function: offitravel_cabin_order_item' );
	}
	$product = wc_get_product( 11280 );
	$snapshot = array(
		'version'    => 1,
		'product_id' => 11280,
		'cabins'     => array(
			1 => array(
				'cabin_index'      => 1,
				'occupants'        => 2,
				'category'         => 'puente-intermedio',
				'label'            => 'Puente intermedio',
				'price_per_person' => '135.00',
				'subtotal'         => '270.00',
			),
		),
		'total'      => '270.00',
	);
	$order = wc_create_order( array( 'created_via' => 'offitravel-checkpoint-5-test' ) );
	if ( is_wp_error( $order ) ) {
		throw new RuntimeException( $order->get_error_message() );
	}
	$item = new WC_Order_Item_Product();
	$item->set_product( $product );
	$item->set_quantity( 1 );
	$item->set_subtotal( 1568 );
	$item->set_total( 1568 );
	offitravel_cabin_order_item(
		$item,
		'checkpoint-5-test',
		array(
			'product_id'                    => 11280,
			'data'                          => $product,
			'offitravel_cabin_supplements' => $snapshot,
		),
		$order
	);
	$order->add_item( $item );
	$order->save();

	$reloaded       = wc_get_order( $order->get_id() );
	$items          = $reloaded ? $reloaded->get_items() : array();
	$persisted_item = $items ? reset( $items ) : null;
	if ( ! $persisted_item instanceof WC_Order_Item_Product ) {
		throw new RuntimeException( 'The temporary order did not persist its Rin line.' );
	}
	$stored = $persisted_item->get_meta( '_offitravel_cabin_supplement_snapshot', true );
	if ( ! is_array( $stored ) || '270.00' !== $stored['total'] ) {
		throw new RuntimeException( 'The structured cabin snapshot was not persisted.' );
	}
	if ( '270.00' !== (string) $persisted_item->get_meta( '_offitravel_cabin_supplement_total', true ) ) {
		throw new RuntimeException( 'The cabin total was not persisted.' );
	}
	$visible = (string) $persisted_item->get_meta( 'Suplemento de cabina', true );
	$expected = "Cabina 1: 2 personas — Puente intermedio\nPrecio por persona: 135,00 €\nSubtotal: 270,00 €\nTotal suplementos de cabina: 270,00 €";
	if ( $expected !== $visible ) {
		throw new RuntimeException( "The visible cabin breakdown is incorrect.\nActual: {$visible}" );
	}
	$hidden = apply_filters( 'woocommerce_hidden_order_itemmeta', array() );
	foreach ( array( '_offitravel_cabin_supplement_snapshot', '_offitravel_cabin_supplement_total' ) as $meta_key ) {
		if ( ! in_array( $meta_key, $hidden, true ) ) {
			throw new RuntimeException( "Technical order metadata is visible: {$meta_key}" );
		}
	}

	printf( "[PASS] temporary order %d persisted Rin snapshot total 270.00 and visible breakdown.\n", $order->get_id() );
} catch ( Throwable $error ) {
	$failure = $error;
} finally {
	if ( $order instanceof WC_Order && $order->get_id() ) {
		$order_id = $order->get_id();
		$order->delete( true );
		if ( wc_get_order( $order_id ) ) {
			fwrite( STDERR, '[FAIL] Temporary order cleanup failed: ' . $order_id . PHP_EOL );
			exit( 1 );
		}
		printf( "[PASS] temporary order %d removed after verification.\n", $order_id );
	}
}
if ( $failure instanceof Throwable ) {
	fwrite( STDERR, '[FAIL] ' . $failure->getMessage() . PHP_EOL );
	exit( 1 );
}
