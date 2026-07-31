<?php
/**
 * Read-only configuration regression tests for musical add-ons.
 *
 * Run with: php tests/offitravel-product-addons-musicals-config-test.php
 *
 * @package Offitravel
 */

require dirname( __DIR__ ) . '/wp-load.php';

/**
 * Assert strict equality for stored WordPress configuration.
 *
 * @param mixed  $expected Expected value.
 * @param mixed  $actual   Actual value.
 * @param string $message  Failure context.
 * @return void
 * @throws RuntimeException When values differ.
 */
function offitravel_musicals_config_same( $expected, $actual, $message ) {
	if ( $expected !== $actual ) {
		throw new RuntimeException( $message . "\nExpected: " . var_export( $expected, true ) . "\nActual: " . var_export( $actual, true ) );
	}
}

$tests = array(
	'KIT configuration covers all musicals and exact manual products' => static function () {
		offitravel_musicals_config_same( '12', (string) get_post_meta( 12027, OFFITRAVEL_ADDON_META_PRICE, true ), 'KIT price changed.' );
		offitravel_musicals_config_same( 'room', get_post_meta( 12027, OFFITRAVEL_ADDON_META_BILLING, true ), 'KIT billing changed.' );
		offitravel_musicals_config_same( 'KIT romántico', get_post_meta( 12027, OFFITRAVEL_ADDON_META_PUBLIC_LABEL, true ), 'KIT public label is incorrect.' );
		offitravel_musicals_config_same( array( 10618, 10628, 11512, 11521, 11528, 11537, 11539, 11545 ), array_values( array_map( 'absint', get_post_meta( 12027, OFFITRAVEL_ADDON_META_PRODUCTS, true ) ) ), 'KIT assignments are incorrect.' );
		offitravel_musicals_config_same( array( 11528, 11537, 11539, 11545 ), array_values( array_map( 'absint', get_post_meta( 12027, OFFITRAVEL_ADDON_META_MANUAL_ROOM_PRODUCTS, true ) ) ), 'KIT manual-room assignments are incorrect.' );
	},
	'cancellation service is six euros once for all musicals' => static function () {
		$post = get_page_by_path( 'seguro-anulacion-musicales', OBJECT, OFFITRAVEL_ADDON_PT );
		if ( ! $post instanceof WP_Post ) {
			throw new RuntimeException( 'Cancellation service does not exist.' );
		}
		offitravel_musicals_config_same( 'publish', $post->post_status, 'Cancellation service is not published.' );
		offitravel_musicals_config_same( '6', (string) get_post_meta( $post->ID, OFFITRAVEL_ADDON_META_PRICE, true ), 'Cancellation price is incorrect.' );
		offitravel_musicals_config_same( 'booking', get_post_meta( $post->ID, OFFITRAVEL_ADDON_META_BILLING, true ), 'Cancellation billing is incorrect.' );
		offitravel_musicals_config_same( 'yes', get_post_meta( $post->ID, OFFITRAVEL_ADDON_META_BOOKING_ONCE, true ), 'Cancellation once-per-booking policy is missing.' );
		offitravel_musicals_config_same( array( 10618, 10628, 11512, 11521, 11528, 11537, 11539, 11545 ), array_values( array_map( 'absint', get_post_meta( $post->ID, OFFITRAVEL_ADDON_META_PRODUCTS, true ) ) ), 'Cancellation assignments are incorrect.' );
	},
	'protected platea and Service 01 configurations are unchanged' => static function () {
		offitravel_musicals_config_same( '20', (string) get_post_meta( 12028, OFFITRAVEL_ADDON_META_PRICE, true ), 'Platea price changed.' );
		offitravel_musicals_config_same( 'person', get_post_meta( 12028, OFFITRAVEL_ADDON_META_BILLING, true ), 'Platea billing changed.' );
		offitravel_musicals_config_same( array( 11789, 10628, 10618 ), array_values( array_map( 'absint', get_post_meta( 12028, OFFITRAVEL_ADDON_META_PRODUCTS, true ) ) ), 'Platea assignments changed.' );
		offitravel_musicals_config_same( '32.50', (string) get_post_meta( 12717, OFFITRAVEL_ADDON_META_PRICE, true ), 'Service 01 price changed.' );
		offitravel_musicals_config_same( 'person', get_post_meta( 12717, OFFITRAVEL_ADDON_META_BILLING, true ), 'Service 01 billing changed.' );
		offitravel_musicals_config_same( array( 10618 ), array_values( array_map( 'absint', get_post_meta( 12717, OFFITRAVEL_ADDON_META_PRODUCTS, true ) ) ), 'Service 01 assignments changed.' );
	},
	'circuit traveler-age services remain unchanged' => static function () {
		offitravel_musicals_config_same( array( 9475, 9487 ), array_values( array_map( 'absint', get_post_meta( 12718, OFFITRAVEL_ADDON_META_PRODUCTS, true ) ) ), 'Asturias/Ribeira insurance assignments changed.' );
		offitravel_musicals_config_same( array( 9502 ), array_values( array_map( 'absint', get_post_meta( 12719, OFFITRAVEL_ADDON_META_PRODUCTS, true ) ) ), 'A Coruña insurance assignments changed.' );
	},
	'public form uses real rooms or manual quantity only where configured' => static function () {
		ob_start();
		offitravel_addon_booking_markup( array( 'id' => 10618 ) );
		$real_html = (string) ob_get_clean();
		if ( false === strpos( $real_html, 'KIT romántico' ) || false === strpos( $real_html, 'Seguro de anulación' ) ) {
			throw new RuntimeException( 'Real-room musical is missing approved services.' );
		}
		if ( false !== strpos( $real_html, 'offitravel_addon_quantities[12027]' ) ) {
			throw new RuntimeException( 'Real-room musical incorrectly renders a manual KIT quantity.' );
		}

		ob_start();
		offitravel_addon_booking_markup( array( 'id' => 11528 ) );
		$manual_html = (string) ob_get_clean();
		if ( false === strpos( $manual_html, 'offitravel_addon_quantities[12027]' ) || false === strpos( $manual_html, 'Número de habitaciones que recibirán el KIT' ) ) {
			throw new RuntimeException( 'Manual-room musical is missing its KIT quantity field.' );
		}

		ob_start();
		offitravel_addon_booking_markup( array( 'id' => 9487 ) );
		$circuit_html = (string) ob_get_clean();
		if ( false !== strpos( $circuit_html, 'KIT romántico' ) || false !== strpos( $circuit_html, 'Seguro de anulación' ) ) {
			throw new RuntimeException( 'Musical services leaked into a circuit.' );
		}
	},
	'all eight musicals expose only their assigned fixed services' => static function () {
		$cancellation = get_page_by_path( 'seguro-anulacion-musicales', OBJECT, OFFITRAVEL_ADDON_PT );
		$expected = array(
			10618 => array( 12027, 12028, 12717, $cancellation->ID ),
			10628 => array( 12027, 12028, $cancellation->ID ),
			11512 => array( 12027, $cancellation->ID ),
			11521 => array( 12027, $cancellation->ID ),
			11528 => array( 12027, $cancellation->ID ),
			11537 => array( 12027, $cancellation->ID ),
			11539 => array( 12027, $cancellation->ID ),
			11545 => array( 12027, $cancellation->ID ),
		);
		foreach ( $expected as $product_id => $expected_ids ) {
			$actual_ids = array_map(
				static function ( $post ) {
					return (int) $post->ID;
				},
				offitravel_addon_posts_for_product( $product_id )
			);
			sort( $actual_ids );
			sort( $expected_ids );
			offitravel_musicals_config_same( $expected_ids, $actual_ids, 'Visible fixed services are incorrect for product ' . $product_id . '.' );
		}
	},
	'configured combination totals KIT platea and cancellation exactly' => static function () {
		$cancellation = get_page_by_path( 'seguro-anulacion-musicales', OBJECT, OFFITRAVEL_ADDON_PT );
		$result = offitravel_addon_calculate_fixed_snapshot(
			10618,
			array( 12027, 12028, $cancellation->ID ),
			array(),
			array( 'offitravel_room_count' => 1, 'ovabrw_adults' => 2, 'ovabrw_quantity' => 1 )
		);
		if ( is_wp_error( $result ) ) {
			throw new RuntimeException( $result->get_error_message() );
		}
		offitravel_musicals_config_same( '58.00', $result['total'], 'KIT + Platea + cancellation total is incorrect.' );
		offitravel_musicals_config_same( '6.00', $result['services'][ $cancellation->ID ]['total'], 'Cancellation was multiplied.' );
	},
);

$failures = 0;
foreach ( $tests as $name => $test ) {
	try {
		$test();
		printf( "[PASS] %s\n", $name );
	} catch ( Throwable $error ) {
		++$failures;
		fwrite( STDERR, '[FAIL] ' . $name . "\n" . $error->getMessage() . "\n" );
	}
}
printf( "%d test(s), %d failure(s).\n", count( $tests ), $failures );
exit( $failures > 0 ? 1 : 0 );
