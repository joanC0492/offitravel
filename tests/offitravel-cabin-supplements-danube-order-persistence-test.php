<?php
/**
 * Temporary-order persistence test for the Danube cabin supplement snapshot.
 *
 * The test creates one local order and permanently removes only that exact ID
 * in the finally block. Existing orders are never queried or changed.
 *
 * Run with: php tests/offitravel-cabin-supplements-danube-order-persistence-test.php
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
	$product = wc_get_product( 11259 );
	$snapshot = array(
		'version'    => 1,
		'product_id' => 11259,
		'cabins'     => array(
			1 => array(
				'cabin_index'      => 1,
				'occupants'        => 3,
				'category'         => 'puente-intermedio',
				'label'            => 'Puente intermedio',
				'price_per_person' => '111.50',
				'subtotal'         => '334.50',
			),
			2 => array(
				'cabin_index'      => 2,
				'occupants'        => 2,
				'category'         => 'puente-superior',
				'label'            => 'Puente superior',
				'price_per_person' => '200.00',
				'subtotal'         => '400.00',
			),
		),
		'total'      => '734.50',
	);
	$order = wc_create_order( array( 'created_via' => 'offitravel-checkpoint-6-test' ) );
	if ( is_wp_error( $order ) ) {
		throw new RuntimeException( $order->get_error_message() );
	}
	$item = new WC_Order_Item_Product();
	$item->set_product( $product );
	$item->set_quantity( 1 );
	$item->set_subtotal( 1984.5 );
	$item->set_total( 1984.5 );
	offitravel_cabin_order_item(
		$item,
		'checkpoint-6-test',
		array(
			'product_id'                    => 11259,
			'data'                          => $product,
			'offitravel_cabin_supplements' => $snapshot,
		),
		$order
	);
	$order->add_item( $item );
	$order->save();
	$temporary_order_id = $order->get_id();

	$reloaded       = wc_get_order( $temporary_order_id );
	$items          = $reloaded ? $reloaded->get_items() : array();
	$persisted_item = $items ? reset( $items ) : null;
	if ( ! $persisted_item instanceof WC_Order_Item_Product ) {
		throw new RuntimeException( 'The temporary order did not persist its Danube line.' );
	}
	$stored = $persisted_item->get_meta( '_offitravel_cabin_supplement_snapshot', true );
	if ( ! is_array( $stored ) || '734.50' !== $stored['total'] || 2 !== count( $stored['cabins'] ) ) {
		throw new RuntimeException( 'The structured Danube cabin snapshot was not persisted.' );
	}
	if ( '734.50' !== (string) $persisted_item->get_meta( '_offitravel_cabin_supplement_total', true ) ) {
		throw new RuntimeException( 'The Danube cabin total was not persisted.' );
	}
	$visible = (string) $persisted_item->get_meta( 'Suplemento de cabina', true );
	$expected = "Cabina 1: 3 personas — Puente intermedio\nPrecio por persona: 111,50 €\nSubtotal: 334,50 €\nCabina 2: 2 personas — Puente superior\nPrecio por persona: 200,00 €\nSubtotal: 400,00 €\nTotal suplementos de cabina: 734,50 €";
	if ( $expected !== $visible ) {
		throw new RuntimeException( "The visible Danube cabin breakdown is incorrect.\nActual: {$visible}" );
	}
	$hidden = apply_filters( 'woocommerce_hidden_order_itemmeta', array() );
	foreach ( array( '_offitravel_cabin_supplement_snapshot', '_offitravel_cabin_supplement_total' ) as $meta_key ) {
		if ( ! in_array( $meta_key, $hidden, true ) ) {
			throw new RuntimeException( "Technical order metadata is visible: {$meta_key}" );
		}
	}

	printf( "[PASS] temporary order %d persisted Danube snapshot total 734.50 and visible breakdown.\n", $temporary_order_id );
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

