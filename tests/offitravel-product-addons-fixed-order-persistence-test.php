<?php
/**
 * Temporary-order persistence test for fixed musical add-ons.
 *
 * The test creates one local order, verifies its visible and technical item
 * metadata, and permanently removes only that exact order in the finally block.
 *
 * Run with: php tests/offitravel-product-addons-fixed-order-persistence-test.php
 *
 * @package Offitravel
 */

require dirname( __DIR__ ) . '/wp-load.php';

$order   = null;
$failure = null;
try {
	$product      = wc_get_product( 10618 );
	$cancellation = get_page_by_path( 'seguro-anulacion-musicales', OBJECT, OFFITRAVEL_ADDON_PT );
	if ( ! $cancellation instanceof WP_Post ) {
		throw new RuntimeException( 'The configured cancellation service does not exist.' );
	}
	$cancellation_id = (int) $cancellation->ID;
	$snapshot = array(
		'version'    => 1,
		'product_id' => 10618,
		'services'   => array(
			12027 => array(
				'service_id'      => 12027,
				'label'           => 'KIT romántico',
				'billing'         => 'room',
				'quantity_source' => 'real_rooms',
				'quantity'        => 3,
				'unit_price'      => '12.00',
				'total'           => '36.00',
			),
			$cancellation_id => array(
				'service_id'      => $cancellation_id,
				'label'           => 'Seguro de anulación',
				'billing'         => 'booking',
				'quantity_source' => 'booking_once',
				'quantity'        => 1,
				'unit_price'      => '6.00',
				'total'           => '6.00',
			),
		),
		'total'      => '42.00',
	);
	$order = wc_create_order( array( 'created_via' => 'offitravel-checkpoint-3-test' ) );
	if ( is_wp_error( $order ) ) {
		throw new RuntimeException( $order->get_error_message() );
	}
	$item = new WC_Order_Item_Product();
	$item->set_product( $product );
	$item->set_quantity( 1 );
	$item->set_subtotal( 42 );
	$item->set_total( 42 );
	offitravel_addon_order_item(
		$item,
		'checkpoint-3-test',
		array(
			'product_id'               => 10618,
			'data'                     => $product,
			'offitravel_addons'        => array( 12027, $cancellation_id ),
			'offitravel_fixed_addons' => $snapshot,
		)
	);
	$order->add_item( $item );
	$order->save();

	$reloaded       = wc_get_order( $order->get_id() );
	$items          = $reloaded ? $reloaded->get_items() : array();
	$persisted_item = $items ? reset( $items ) : null;
	if ( ! $persisted_item instanceof WC_Order_Item_Product ) {
		throw new RuntimeException( 'The temporary order did not persist its product line.' );
	}
	$stored = $persisted_item->get_meta( '_offitravel_fixed_addon_snapshot', true );
	if ( ! is_array( $stored ) || '42.00' !== $stored['total'] ) {
		throw new RuntimeException( 'The structured fixed add-on snapshot was not persisted.' );
	}
	if ( '42.00' !== (string) $persisted_item->get_meta( '_offitravel_fixed_addon_total', true ) ) {
		throw new RuntimeException( 'The fixed add-on total was not persisted.' );
	}
	$kit = (string) $persisted_item->get_meta( 'KIT romántico', true );
	$insurance = (string) $persisted_item->get_meta( 'Seguro de anulación', true );
	if ( "Habitaciones: 3\nPrecio unitario: 12,00 €\nTotal: 36,00 €" !== $kit ) {
		throw new RuntimeException( "The visible KIT breakdown is incorrect.\nActual: " . $kit );
	}
	if ( "Una vez por reserva\nPrecio: 6,00 €\nTotal: 6,00 €" !== $insurance ) {
		throw new RuntimeException( "The visible cancellation breakdown is incorrect.\nActual: " . $insurance );
	}

	printf( "[PASS] temporary order %d persisted fixed snapshot total 42.00 and visible breakdowns.\n", $order->get_id() );
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
