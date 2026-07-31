<?php
/**
 * Public calculation, validation and cart tests for Danube cabin supplements.
 *
 * The test reads the approved product configuration and never writes metadata.
 *
 * Run with: php tests/offitravel-cabin-supplements-danube-test.php
 *
 * @package Offitravel
 */

require dirname( __DIR__ ) . '/wp-load.php';

const OFFITRAVEL_CABIN_DANUBE_PRODUCT = 11259;

/**
 * Assert strict equality.
 *
 * @param mixed  $expected Expected value.
 * @param mixed  $actual   Actual value.
 * @param string $message  Failure context.
 * @return void
 * @throws RuntimeException When values differ.
 */
function offitravel_cabin_danube_same( $expected, $actual, $message ) {
	if ( $expected !== $actual ) {
		throw new RuntimeException( $message . "\nExpected: " . var_export( $expected, true ) . "\nActual: " . var_export( $actual, true ) );
	}
}

/**
 * Assert a WordPress error code.
 *
 * @param string $expected Expected code.
 * @param mixed  $actual   Actual result.
 * @param string $message  Failure context.
 * @return void
 * @throws RuntimeException When the code differs.
 */
function offitravel_cabin_danube_error( $expected, $actual, $message ) {
	$code = is_wp_error( $actual ) ? $actual->get_error_code() : gettype( $actual );
	offitravel_cabin_danube_same( $expected, $code, $message );
}

/**
 * Build an untrusted request matching trusted room occupancy.
 *
 * @param int[]    $occupants Occupants in cabin order.
 * @param string[] $categories Categories in cabin order.
 * @return array<string,mixed>
 */
function offitravel_cabin_danube_request( array $occupants, array $categories ) {
	$cabins = array();
	foreach ( $occupants as $offset => $people ) {
		$cabins[ $offset + 1 ] = array(
			'people'           => $people,
			'category'         => isset( $categories[ $offset ] ) ? $categories[ $offset ] : '',
			'price_per_person' => '999.99',
			'label'            => 'Manipulado',
			'subtotal'         => '999.99',
			'total'            => '999.99',
		);
	}
	return array(
		'offitravel_cabins'      => $cabins,
		'offitravel_room_count'  => count( $occupants ),
		'offitravel_room_people' => $occupants,
	);
}

$tests = array(
	'Danube uses all approved authoritative supplement totals' => static function () {
		$cases = array(
			array( array( 2 ), array( 'sin-suplemento' ), '0.00' ),
			array( array( 2 ), array( 'puente-intermedio' ), '223.00' ),
			array( array( 2 ), array( 'puente-superior' ), '400.00' ),
			array( array( 3, 2 ), array( 'puente-intermedio', 'puente-intermedio' ), '557.50' ),
			array( array( 3, 2 ), array( 'puente-superior', 'puente-superior' ), '1000.00' ),
			array( array( 3, 2 ), array( 'puente-intermedio', 'puente-superior' ), '734.50' ),
		);
		foreach ( $cases as $case ) {
			$result = offitravel_cabin_calculate_request_snapshot( OFFITRAVEL_CABIN_DANUBE_PRODUCT, offitravel_cabin_danube_request( $case[0], $case[1] ) );
			offitravel_cabin_danube_same( $case[2], $result['total'], 'Danube total differs from the approved literal.' );
		}
	},
	'Danube decimal snapshots remain exact two-decimal WooCommerce strings' => static function () {
		$result = offitravel_cabin_calculate_request_snapshot( OFFITRAVEL_CABIN_DANUBE_PRODUCT, offitravel_cabin_danube_request( array( 3, 2 ), array( 'puente-intermedio', 'puente-intermedio' ) ) );
		offitravel_cabin_danube_same( '111.50', $result['cabins'][1]['price_per_person'], 'Unit price drifted.' );
		offitravel_cabin_danube_same( '334.50', $result['cabins'][1]['subtotal'], 'First subtotal drifted.' );
		offitravel_cabin_danube_same( '223.00', $result['cabins'][2]['subtotal'], 'Second subtotal drifted.' );
		offitravel_cabin_danube_same( '557.50', $result['total'], 'Aggregate drifted.' );
	},
	'configured maximum rejects five occupants in one cabin but permits one occupant' => static function () {
		offitravel_cabin_danube_error(
			'offitravel_cabin_product_occupancy_invalid',
			offitravel_cabin_calculate_request_snapshot( OFFITRAVEL_CABIN_DANUBE_PRODUCT, offitravel_cabin_danube_request( array( 5 ), array( 'sin-suplemento' ) ) ),
			'The stored maximum of four occupants was bypassed.'
		);
		$result = offitravel_cabin_calculate_request_snapshot( OFFITRAVEL_CABIN_DANUBE_PRODUCT, offitravel_cabin_danube_request( array( 1 ), array( 'puente-intermedio' ) ) );
		offitravel_cabin_danube_same( '111.50', $result['total'], 'A single occupant was rejected or priced incorrectly.' );
	},
	'invalid empty and non-scalar categories are rejected' => static function () {
		foreach ( array( '', 'categoria-inexistente', array( 'puente-intermedio' ) ) as $category ) {
			$request = offitravel_cabin_danube_request( array( 2 ), array( 'sin-suplemento' ) );
			$request['offitravel_cabins'][1]['category'] = $category;
			offitravel_cabin_danube_error( 'offitravel_cabin_invalid_category', offitravel_cabin_calculate_request_snapshot( OFFITRAVEL_CABIN_DANUBE_PRODUCT, $request ), 'Invalid category was accepted.' );
		}
	},
	'missing additional and occupancy-manipulated cabins are rejected' => static function () {
		$missing = offitravel_cabin_danube_request( array( 3, 2 ), array( 'sin-suplemento', 'sin-suplemento' ) );
		unset( $missing['offitravel_cabins'][2] );
		offitravel_cabin_danube_error( 'offitravel_cabin_count_mismatch', offitravel_cabin_calculate_request_snapshot( OFFITRAVEL_CABIN_DANUBE_PRODUCT, $missing ), 'Missing cabin was accepted.' );
		$extra = offitravel_cabin_danube_request( array( 2 ), array( 'sin-suplemento' ) );
		$extra['offitravel_cabins'][2] = array( 'people' => 1, 'category' => 'sin-suplemento' );
		offitravel_cabin_danube_error( 'offitravel_cabin_count_mismatch', offitravel_cabin_calculate_request_snapshot( OFFITRAVEL_CABIN_DANUBE_PRODUCT, $extra ), 'Additional cabin was accepted.' );
		$forged = offitravel_cabin_danube_request( array( 2 ), array( 'puente-intermedio' ) );
		$forged['offitravel_cabins'][1]['people'] = 4;
		offitravel_cabin_danube_error( 'offitravel_cabin_occupancy_mismatch', offitravel_cabin_calculate_request_snapshot( OFFITRAVEL_CABIN_DANUBE_PRODUCT, $forged ), 'Forged occupancy was accepted.' );
	},
	'browser prices labels subtotals and totals are ignored' => static function () {
		$result = offitravel_cabin_calculate_request_snapshot( OFFITRAVEL_CABIN_DANUBE_PRODUCT, offitravel_cabin_danube_request( array( 2 ), array( 'puente-intermedio' ) ) );
		offitravel_cabin_danube_same( 'Puente intermedio', $result['cabins'][1]['label'], 'Browser label was trusted.' );
		offitravel_cabin_danube_same( '111.50', $result['cabins'][1]['price_per_person'], 'Browser price was trusted.' );
		offitravel_cabin_danube_same( '223.00', $result['cabins'][1]['subtotal'], 'Browser subtotal was trusted.' );
		offitravel_cabin_danube_same( '223.00', $result['total'], 'Browser total was trusted.' );
	},
	'products without cabin activation reject the Danube category' => static function () {
		offitravel_cabin_danube_error(
			'offitravel_cabin_product_disabled',
			offitravel_cabin_calculate_request_snapshot( 9502, offitravel_cabin_danube_request( array( 2 ), array( 'puente-intermedio' ) ) ),
			'An unassigned product accepted the cabin supplement.'
		);
	},
	'Danube single-room fallback is zero and never contributes 150 euros' => static function () {
		offitravel_cabin_danube_same( 0.0, offitravel_ovabrw_get_single_supplement_amount( OFFITRAVEL_CABIN_DANUBE_PRODUCT ), 'Danube still resolves the legacy 150 euro fallback.' );
		$line_total = offitravel_ovabrw_apply_pricing_addons_from_table(
			1250,
			OFFITRAVEL_CABIN_DANUBE_PRODUCT,
			'',
			'',
			array( 'ovabrw_adults' => 1, 'offitravel_room_count' => 1, 'offitravel_room_people' => array( 1 ) )
		);
		offitravel_cabin_danube_same( 1250, $line_total, 'Room-mode pricing added the 150 euro fallback to Danube.' );
	},
	'cart snapshot and line price are server-calculated and idempotent' => static function () {
		$previous = $_POST;
		$_POST = offitravel_cabin_danube_request( array( 3, 2 ), array( 'puente-intermedio', 'puente-superior' ) );
		try {
			$cart = offitravel_cabin_add_cart_item_data(
				array( 'offitravel_room_count' => 2, 'offitravel_room_people' => array( 3, 2 ) ),
				OFFITRAVEL_CABIN_DANUBE_PRODUCT,
				0,
				999
			);
		} finally {
			$_POST = $previous;
		}
		offitravel_cabin_danube_same( '734.50', $cart['offitravel_cabin_supplements']['total'], 'Cart snapshot total is wrong.' );
		offitravel_cabin_danube_same( 1984.5, offitravel_cabin_line_total( 1250, OFFITRAVEL_CABIN_DANUBE_PRODUCT, '', '', $cart ), 'Cabin total was not added exactly once.' );
		offitravel_cabin_danube_same( 1984.5, offitravel_cabin_line_total( 1250, OFFITRAVEL_CABIN_DANUBE_PRODUCT, '', '', $cart ), 'Repeated calculation accumulated the supplement.' );
	},
	'historical session restore does not consult current tariffs' => static function () {
		$snapshot = offitravel_cabin_calculate_request_snapshot( OFFITRAVEL_CABIN_DANUBE_PRODUCT, offitravel_cabin_danube_request( array( 2 ), array( 'puente-intermedio' ) ) );
		$snapshot['cabins'][1]['price_per_person'] = '99.25';
		$snapshot['cabins'][1]['subtotal'] = '1.00';
		$snapshot['total'] = '1.00';
		$restored = offitravel_cabin_restore_cart_item(
			array( 'product_id' => OFFITRAVEL_CABIN_DANUBE_PRODUCT ),
			array( 'product_id' => OFFITRAVEL_CABIN_DANUBE_PRODUCT, 'offitravel_cabin_supplements' => $snapshot )
		);
		offitravel_cabin_danube_same( '99.25', $restored['offitravel_cabin_supplements']['cabins'][1]['price_per_person'], 'Historical unit price was replaced.' );
		offitravel_cabin_danube_same( '198.50', $restored['offitravel_cabin_supplements']['total'], 'Historical total was not rebuilt from its snapshot.' );
	},
	'cart display and public markup are readable and safely scoped' => static function () {
		$snapshot = offitravel_cabin_calculate_request_snapshot( OFFITRAVEL_CABIN_DANUBE_PRODUCT, offitravel_cabin_danube_request( array( 2 ), array( 'puente-intermedio' ) ) );
		$rows = offitravel_cabin_cart_display( array(), array( 'data' => wc_get_product( OFFITRAVEL_CABIN_DANUBE_PRODUCT ), 'offitravel_cabin_supplements' => $snapshot ) );
		offitravel_cabin_danube_same( 'Suplemento de cabina', $rows[0]['key'], 'Cart row label is unclear.' );
		$text = wp_strip_all_tags( str_replace( '<br>', "\n", $rows[0]['value'] ) );
		foreach ( array( 'Cabina 1: 2 personas', 'Puente intermedio', '111,50 €', '223,00 €', 'Total suplementos de cabina: 223,00 €' ) as $fragment ) {
			if ( false === strpos( $text, $fragment ) ) {
				throw new RuntimeException( "Missing readable fragment: {$fragment}\n{$text}" );
			}
		}
		if ( preg_match( '/&(?:amp;)?nbsp;|<[^>]+>/i', $text ) ) {
			throw new RuntimeException( 'Visible escaped entities or HTML leaked into cabin text.' );
		}
		ob_start();
		offitravel_cabin_booking_markup( OFFITRAVEL_CABIN_DANUBE_PRODUCT );
		$html = ob_get_clean();
		if ( false === strpos( $html, 'data-offitravel-cabin-config' ) || false === strpos( $html, '111.50' ) ) {
			throw new RuntimeException( 'Danube public configuration was not rendered.' );
		}
		ob_start();
		offitravel_cabin_booking_markup( 9502 );
		offitravel_cabin_danube_same( '', ob_get_clean(), 'Cabin markup leaked to an unassigned product.' );
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
