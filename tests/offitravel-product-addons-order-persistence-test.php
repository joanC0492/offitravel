<?php
/**
 * Temporary-order persistence test for traveler-age add-ons.
 *
 * This test creates one local WooCommerce order, reloads its item metadata and
 * permanently removes that exact test order in a finally block.
 *
 * Run with: php tests/offitravel-product-addons-order-persistence-test.php
 *
 * @package Offitravel
 */

require dirname( __DIR__ ) . '/wp-load.php';

$order   = null;
$failure = null;
try {
	$snapshot = offitravel_addon_calculate_traveler_age(
		9487,
		array(
			12718 => array(
				1 => array(
					1 => array( 'selected' => '1', 'age' => '35' ),
					2 => array( 'selected' => '1', 'age' => '72' ),
				),
			),
		),
		array(
			'offitravel_room_count'  => 1,
			'offitravel_room_people' => array( 2 ),
			'ovabrw_adults'          => 2,
		)
	);
	if ( is_wp_error( $snapshot ) ) {
		throw new RuntimeException( $snapshot->get_error_message() );
	}

	$order = wc_create_order( array( 'created_via' => 'offitravel-checkpoint-2-test' ) );
	if ( is_wp_error( $order ) ) {
		throw new RuntimeException( $order->get_error_message() );
	}
	$product = wc_get_product( 9487 );
	$item    = new WC_Order_Item_Product();
	$item->set_product( $product );
	$item->set_quantity( 1 );
	$item->set_subtotal( 1948 );
	$item->set_total( 1948 );
	offitravel_addon_order_item(
		$item,
		'checkpoint-2-test',
		array(
			'product_id'              => 9487,
			'data'                    => $product,
			'offitravel_traveler_age' => $snapshot,
		)
	);
	$order->add_item( $item );
	$order->save();

	$reloaded = wc_get_order( $order->get_id() );
	$items    = $reloaded ? $reloaded->get_items() : array();
	$persisted_item = $items ? reset( $items ) : null;
	if ( ! $persisted_item instanceof WC_Order_Item_Product ) {
		throw new RuntimeException( 'The temporary order did not persist its product line.' );
	}
	$stored = $persisted_item->get_meta( '_offitravel_traveler_age_snapshot', true );
	if ( ! is_array( $stored ) || '78.00' !== $stored['total'] ) {
		throw new RuntimeException( 'The structured traveler-age snapshot was not persisted.' );
	}
	$visible = (string) $persisted_item->get_meta( 'Seguro de viaje', true );
	$expected_visible = "Viajero 1 (Habitación 1): 35 años — 32,50 €\n"
		. "Viajero 2 (Habitación 1): 72 años — 45,50 €\n"
		. 'Total: 78,00 €';
	if ( $expected_visible !== $visible ) {
		throw new RuntimeException( "The visible order/email breakdown was not persisted as decoded readable text.\nActual: " . $visible );
	}

	printf( "[PASS] temporary order %d persisted snapshot total 78.00 and visible traveler breakdown.\n", $order->get_id() );
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
