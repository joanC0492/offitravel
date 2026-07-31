<?php
/**
 * Integration tests for fixed product add-on snapshots.
 *
 * Run with: php tests/offitravel-product-addons-fixed-snapshot-test.php
 *
 * @package Offitravel
 */

require dirname( __DIR__ ) . '/wp-load.php';

/**
 * Assert strict equality.
 *
 * @param mixed  $expected Expected value.
 * @param mixed  $actual   Actual value.
 * @param string $message  Failure context.
 * @return void
 * @throws RuntimeException When values differ.
 */
function offitravel_fixed_test_same( $expected, $actual, $message ) {
	if ( $expected !== $actual ) {
		throw new RuntimeException( $message . "\nExpected: " . var_export( $expected, true ) . "\nActual: " . var_export( $actual, true ) );
	}
}

/**
 * Assert a WordPress error code.
 *
 * @param string $expected Expected code.
 * @param mixed  $actual   Actual value.
 * @param string $message  Failure context.
 * @return void
 * @throws RuntimeException When the code differs.
 */
function offitravel_fixed_test_error( $expected, $actual, $message ) {
	$code = is_wp_error( $actual ) ? $actual->get_error_code() : gettype( $actual );
	if ( $expected !== $code ) {
		throw new RuntimeException( $message . "\nExpected error: {$expected}\nActual: {$code}" );
	}
}

/**
 * Run with isolated KIT and cancellation metadata without database writes.
 *
 * Real posts 12027 and 12028 provide valid CPT records; all commercial
 * metadata read by production is intercepted for the duration of the test.
 *
 * @param callable $callback Test callback.
 * @return void
 */
function offitravel_fixed_test_with_services( $callback ) {
	$products = array( 10618, 10628, 11512, 11521, 11528, 11537, 11539, 11545 );
	$metadata = array(
		12027 => array(
			'_offitravel_addon_public_label'            => 'KIT romántico',
			'_offitravel_addon_price'                   => '12',
			'_offitravel_addon_billing'                 => 'room',
			'_offitravel_addon_product_ids'             => $products,
			'_offitravel_addon_manual_room_product_ids' => array( 11528, 11537, 11539, 11545 ),
		),
		12028 => array(
			'_offitravel_addon_public_label' => 'Seguro de anulación',
			'_offitravel_addon_price'        => '6',
			'_offitravel_addon_billing'      => 'booking',
			'_offitravel_addon_product_ids'  => $products,
			'_offitravel_addon_booking_once' => 'yes',
		),
	);
	$filter = static function ( $value, $object_id, $meta_key ) use ( $metadata ) {
		$object_id = (int) $object_id;
		if ( isset( $metadata[ $object_id ] ) && array_key_exists( $meta_key, $metadata[ $object_id ] ) ) {
			$fixture = $metadata[ $object_id ][ $meta_key ];
			return is_array( $fixture ) ? array( $fixture ) : $fixture;
		}
		return $value;
	};
	add_filter( 'get_post_metadata', $filter, 10, 4 );
	try {
		$callback();
	} finally {
		remove_filter( 'get_post_metadata', $filter, 10 );
	}
}

$required = array(
	'offitravel_addon_calculate_fixed_snapshot',
	'offitravel_addon_normalize_fixed_snapshot',
	'offitravel_addon_fixed_snapshot_total',
);
foreach ( $required as $function_name ) {
	if ( ! function_exists( $function_name ) ) {
		throw new RuntimeException( 'Missing production function: ' . $function_name );
	}
}

$tests = array(
	'KIT uses real room count and ignores OVA quantity' => static function () {
		offitravel_fixed_test_with_services(
			static function () {
				$one = offitravel_addon_calculate_fixed_snapshot( 10618, array( 12027 ), array(), array( 'offitravel_room_count' => 1, 'ovabrw_quantity' => 9 ) );
				$three = offitravel_addon_calculate_fixed_snapshot( 10618, array( 12027 ), array(), array( 'offitravel_room_count' => 3, 'ovabrw_quantity' => 9 ) );
				offitravel_fixed_test_same( '12.00', $one['total'], 'One real room did not cost 12 euros.' );
				offitravel_fixed_test_same( '36.00', $three['total'], 'Three real rooms did not cost 36 euros.' );
				offitravel_fixed_test_same( 'real_rooms', $three['services'][12027]['quantity_source'], 'KIT did not record real-room billing.' );
			}
		);
	},
	'KIT uses a positive manual room quantity on configured products' => static function () {
		offitravel_fixed_test_with_services(
			static function () {
				$one = offitravel_addon_calculate_fixed_snapshot( 11528, array( 12027 ), array( 12027 => '1' ), array( 'ovabrw_adults' => 8 ) );
				$three = offitravel_addon_calculate_fixed_snapshot( 11528, array( 12027 ), array( 12027 => '3' ), array( 'ovabrw_adults' => 1 ) );
				offitravel_fixed_test_same( '12.00', $one['total'], 'Manual quantity one did not cost 12 euros.' );
				offitravel_fixed_test_same( '36.00', $three['total'], 'Manual quantity three did not cost 36 euros.' );
				offitravel_fixed_test_same( 'manual_rooms', $three['services'][12027]['quantity_source'], 'KIT did not record manual-room billing.' );
			}
		);
	},
	'manual KIT rejects empty zero negative and decimal quantities' => static function () {
		offitravel_fixed_test_with_services(
			static function () {
				foreach ( array( '', '0', '-1', '1.5' ) as $value ) {
					$result = offitravel_addon_calculate_fixed_snapshot( 11528, array( 12027 ), array( 12027 => $value ), array() );
					offitravel_fixed_test_error( 'offitravel_addon_invalid_manual_quantity', $result, 'Invalid manual quantity was accepted: ' . $value );
				}
			}
		);
	},
	'cancellation is exactly six euros once regardless of submitted quantities' => static function () {
		offitravel_fixed_test_with_services(
			static function () {
				$result = offitravel_addon_calculate_fixed_snapshot(
					10618,
					array( 12028 ),
					array( 12028 => '999' ),
					array( 'ovabrw_adults' => 20, 'offitravel_room_count' => 10, 'ovabrw_quantity' => 99 )
				);
				offitravel_fixed_test_same( '6.00', $result['total'], 'Cancellation was multiplied.' );
				offitravel_fixed_test_same( 1, $result['services'][12028]['quantity'], 'Cancellation snapshot quantity was not one.' );
				offitravel_fixed_test_same( 'booking_once', $result['services'][12028]['quantity_source'], 'Cancellation did not record once-per-booking semantics.' );
			}
		);
	},
	'KIT and cancellation totals combine without accumulation' => static function () {
		offitravel_fixed_test_with_services(
			static function () {
				$snapshot = offitravel_addon_calculate_fixed_snapshot( 11528, array( 12027, 12028 ), array( 12027 => '3' ), array() );
				offitravel_fixed_test_same( '42.00', $snapshot['total'], 'Combined total was not exact.' );
				offitravel_fixed_test_same( 142.0, offitravel_addon_line_total( 100.0, 11528, '', '', array( 'offitravel_fixed_addons' => $snapshot ) ), 'First calculation was wrong.' );
				offitravel_fixed_test_same( 142.0, offitravel_addon_line_total( 100.0, 11528, '', '', array( 'offitravel_fixed_addons' => $snapshot ) ), 'Repeated calculation accumulated.' );
			}
		);
	},
	'unassigned fixed service is rejected' => static function () {
		offitravel_fixed_test_with_services(
			static function () {
				$result = offitravel_addon_calculate_fixed_snapshot( 9487, array( 12028 ), array(), array() );
				offitravel_fixed_test_error( 'offitravel_addon_fixed_service_not_assigned', $result, 'Unassigned cancellation service was accepted.' );
			}
		);
	},
	'fixed snapshot normalization is idempotent and preserves historical price' => static function () {
		offitravel_fixed_test_with_services(
			static function () {
				$snapshot = offitravel_addon_calculate_fixed_snapshot( 11528, array( 12027, 12028 ), array( 12027 => '3' ), array() );
				$normalized = offitravel_addon_normalize_fixed_snapshot( $snapshot, 11528 );
				offitravel_fixed_test_same( $snapshot, $normalized, 'Normalization changed a valid fixed snapshot.' );
				offitravel_fixed_test_same( 42.0, offitravel_addon_fixed_snapshot_total( $snapshot, 11528 ), 'Snapshot total was not rebuilt.' );
			}
		);
	},
	'cart session restores the normalized fixed snapshot and compatibility IDs' => static function () {
		offitravel_fixed_test_with_services(
			static function () {
				$snapshot = offitravel_addon_calculate_fixed_snapshot( 11528, array( 12027, 12028 ), array( 12027 => '3' ), array() );
				$restored = offitravel_addon_restore_cart_item(
					array( 'product_id' => 11528 ),
					array( 'product_id' => 11528, 'offitravel_fixed_addons' => $snapshot )
				);
				offitravel_fixed_test_same( $snapshot, $restored['offitravel_fixed_addons'], 'Session restoration changed the fixed snapshot.' );
				offitravel_fixed_test_same( array( 12027, 12028 ), $restored['offitravel_addons'], 'Compatibility IDs were not restored.' );
			}
		);
	},
	'cart and checkout display readable safe fixed-service breakdowns' => static function () {
		offitravel_fixed_test_with_services(
			static function () {
				$snapshot = offitravel_addon_calculate_fixed_snapshot( 11528, array( 12027, 12028 ), array( 12027 => '3' ), array() );
				$display = offitravel_addon_cart_display(
					array(),
					array( 'data' => wc_get_product( 11528 ), 'offitravel_fixed_addons' => $snapshot )
				);
				offitravel_fixed_test_same( 2, count( $display ), 'Cart did not expose one row per fixed service.' );
				offitravel_fixed_test_same( 'KIT romántico', $display[0]['key'], 'KIT public label was not used.' );
				if ( false === strpos( $display[0]['value'], 'Habitaciones: 3<br>Precio unitario: 12,00 €<br>Total: 36,00 €' ) ) {
					throw new RuntimeException( 'KIT cart breakdown is not readable.' );
				}
				if ( preg_match( '/&(?:amp;)?nbsp;|<span/i', $display[0]['value'] ) ) {
					throw new RuntimeException( 'Cart breakdown exposes WooCommerce entities or unsafe markup.' );
				}
			}
		);
	},
);

$failures = 0;
foreach ( $tests as $name => $test ) {
	try {
		$test();
		echo "[PASS] {$name}\n";
	} catch ( Throwable $error ) {
		++$failures;
		echo "[FAIL] {$name}\n{$error->getMessage()}\n";
	}
}
echo count( $tests ) . " test(s), {$failures} failure(s).\n";
exit( $failures > 0 ? 1 : 0 );
