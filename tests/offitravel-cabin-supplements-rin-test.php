<?php
/**
 * Public calculation, validation and cart tests for the Rin cabin supplements.
 *
 * Cabin configuration reads are intercepted until the guarded Checkpoint 5
 * data write. No product metadata is changed by this test.
 *
 * Run with: php tests/offitravel-cabin-supplements-rin-test.php
 *
 * @package Offitravel
 */

require dirname( __DIR__ ) . '/wp-load.php';

const OFFITRAVEL_CABIN_RIN_PRODUCT = 11280;

/**
 * Assert strict equality.
 *
 * @param mixed  $expected Expected value.
 * @param mixed  $actual   Actual value.
 * @param string $message  Failure context.
 * @return void
 * @throws RuntimeException When values differ.
 */
function offitravel_cabin_rin_same( $expected, $actual, $message ) {
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
function offitravel_cabin_rin_error( $expected, $actual, $message ) {
	$code = is_wp_error( $actual ) ? $actual->get_error_code() : gettype( $actual );
	offitravel_cabin_rin_same( $expected, $code, $message );
}

/**
 * Return the approved Rin configuration as independent literal test data.
 *
 * @return array<int,array{id:string,label:string,price_per_person:string}>
 */
function offitravel_cabin_rin_options() {
	return array(
		array( 'id' => 'sin-suplemento', 'label' => 'Sin suplemento', 'price_per_person' => '0.00' ),
		array( 'id' => 'puente-intermedio', 'label' => 'Puente intermedio', 'price_per_person' => '135.00' ),
		array( 'id' => 'puente-superior', 'label' => 'Puente superior', 'price_per_person' => '200.00' ),
	);
}

/**
 * Run a callback with non-persistent approved metadata reads for 11280.
 *
 * @param callable $callback Test callback.
 * @return void
 */
function offitravel_cabin_rin_with_metadata( $callback ) {
	$filter = static function ( $value, $object_id, $meta_key ) {
		if ( OFFITRAVEL_CABIN_RIN_PRODUCT !== (int) $object_id ) {
			return $value;
		}
		if ( OFFITRAVEL_CABIN_META_OPTIONS === $meta_key ) {
			return array( offitravel_cabin_rin_options() );
		}
		if ( OFFITRAVEL_CABIN_META_ENABLED === $meta_key ) {
			return array( 'yes' );
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

/**
 * Create one-cabin request data.
 *
 * @param mixed  $people   Browser occupancy claim.
 * @param mixed  $category Selected category.
 * @param mixed  $room_people Trusted room occupants.
 * @return array<string,mixed>
 */
function offitravel_cabin_rin_request( $people, $category, $room_people = 2 ) {
	return array(
		'offitravel_cabins'     => array( 1 => array( 'people' => $people, 'category' => $category, 'price_per_person' => '999', 'subtotal' => '999' ) ),
		'offitravel_room_count' => 1,
		'offitravel_room_people'=> array( $room_people ),
	);
}

$required = array(
	'offitravel_cabin_calculate_request_snapshot',
	'offitravel_cabin_validate_cart',
	'offitravel_cabin_add_cart_item_data',
	'offitravel_cabin_line_total',
	'offitravel_cabin_restore_cart_item',
	'offitravel_cabin_cart_display',
	'offitravel_cabin_booking_markup',
);
foreach ( $required as $function_name ) {
	if ( ! function_exists( $function_name ) ) {
		fwrite( STDERR, "[FAIL] Missing production function: {$function_name}\n" );
		exit( 1 );
	}
}

$tests = array(
	'Rin uses the five approved authoritative supplement totals' => static function () {
		offitravel_cabin_rin_with_metadata(
			static function () {
				$cases = array(
					array( 2, 'sin-suplemento', '0.00' ),
					array( 2, 'puente-intermedio', '270.00' ),
					array( 2, 'puente-superior', '400.00' ),
					array( 5, 'puente-intermedio', '675.00' ),
					array( 5, 'puente-superior', '1000.00' ),
				);
				foreach ( $cases as $case ) {
					$result = offitravel_cabin_calculate_request_snapshot(
						OFFITRAVEL_CABIN_RIN_PRODUCT,
						offitravel_cabin_rin_request( $case[0], $case[1], $case[0] )
					);
					offitravel_cabin_rin_same( $case[2], $result['total'], 'Rin total differs from the approved literal.' );
				}
			}
		);
	},
	'prices labels subtotals and totals submitted by the browser are ignored' => static function () {
		offitravel_cabin_rin_with_metadata(
			static function () {
				$request = offitravel_cabin_rin_request( 2, 'puente-intermedio' );
				$request['offitravel_cabins'][1]['label'] = 'Forged';
				$request['offitravel_cabins'][1]['total'] = '1';
				$result = offitravel_cabin_calculate_request_snapshot( OFFITRAVEL_CABIN_RIN_PRODUCT, $request );
				offitravel_cabin_rin_same( 'Puente intermedio', $result['cabins'][1]['label'], 'Browser label was trusted.' );
				offitravel_cabin_rin_same( '135.00', $result['cabins'][1]['price_per_person'], 'Browser price was trusted.' );
				offitravel_cabin_rin_same( '270.00', $result['cabins'][1]['subtotal'], 'Browser subtotal was trusted.' );
				offitravel_cabin_rin_same( '270.00', $result['total'], 'Browser total was trusted.' );
			}
		);
	},
	'missing extra manipulated cabins and categories are rejected' => static function () {
		offitravel_cabin_rin_with_metadata(
			static function () {
				$missing = offitravel_cabin_rin_request( 2, 'puente-intermedio' );
				$missing['offitravel_cabins'] = array();
				offitravel_cabin_rin_error( 'offitravel_cabin_count_mismatch', offitravel_cabin_calculate_request_snapshot( OFFITRAVEL_CABIN_RIN_PRODUCT, $missing ), 'Missing cabin was accepted.' );
				$extra = offitravel_cabin_rin_request( 2, 'puente-intermedio' );
				$extra['offitravel_cabins'][2] = array( 'people' => 2, 'category' => 'sin-suplemento' );
				offitravel_cabin_rin_error( 'offitravel_cabin_count_mismatch', offitravel_cabin_calculate_request_snapshot( OFFITRAVEL_CABIN_RIN_PRODUCT, $extra ), 'Additional cabin was accepted.' );
				foreach ( array( '', 'unknown', array( 'bad' ) ) as $category ) {
					offitravel_cabin_rin_error( 'offitravel_cabin_invalid_category', offitravel_cabin_calculate_request_snapshot( OFFITRAVEL_CABIN_RIN_PRODUCT, offitravel_cabin_rin_request( 2, $category ) ), 'Invalid category was accepted.' );
				}
				offitravel_cabin_rin_error( 'offitravel_cabin_occupancy_mismatch', offitravel_cabin_calculate_request_snapshot( OFFITRAVEL_CABIN_RIN_PRODUCT, offitravel_cabin_rin_request( 5, 'puente-intermedio', 2 ) ), 'Manipulated occupancy was accepted.' );
			}
		);
	},
	'one occupant is rejected by the existing Rin product minimum' => static function () {
		offitravel_cabin_rin_with_metadata(
			static function () {
				offitravel_cabin_rin_error( 'offitravel_cabin_product_occupancy_invalid', offitravel_cabin_calculate_request_snapshot( OFFITRAVEL_CABIN_RIN_PRODUCT, offitravel_cabin_rin_request( 1, 'sin-suplemento', 1 ) ), 'Existing minimum of two adults was bypassed.' );
			}
		);
	},
	'Rin single-room fallback is zero and never contributes 150 euros' => static function () {
		offitravel_cabin_rin_same( 0.0, offitravel_ovabrw_get_single_supplement_amount( OFFITRAVEL_CABIN_RIN_PRODUCT ), 'Rin still resolves the legacy 150 euro fallback.' );
		$line_total = offitravel_ovabrw_apply_pricing_addons_from_table(
			1298,
			OFFITRAVEL_CABIN_RIN_PRODUCT,
			'',
			'',
			array(
				'ovabrw_adults'         => 1,
				'offitravel_room_count' => 1,
				'offitravel_room_people'=> array( 1 ),
			)
		);
		offitravel_cabin_rin_same( 1298, $line_total, 'Room-mode pricing added the 150 euro fallback to Rin.' );
	},
	'cart data stores a server-calculated snapshot including zero supplement' => static function () {
		offitravel_cabin_rin_with_metadata(
			static function () {
				$previous = $_POST;
				$_POST = offitravel_cabin_rin_request( 2, 'sin-suplemento' );
				try {
					$cart = offitravel_cabin_add_cart_item_data( array( 'offitravel_room_count' => 1, 'offitravel_room_people' => array( 2 ) ), OFFITRAVEL_CABIN_RIN_PRODUCT, 99 );
				} finally {
					$_POST = $previous;
				}
				offitravel_cabin_rin_same( '0.00', $cart['offitravel_cabin_supplements']['total'], 'Zero supplement snapshot was not preserved.' );
			}
		);
	},
	'line price adds the normalized snapshot exactly once and is idempotent' => static function () {
		offitravel_cabin_rin_with_metadata(
			static function () {
				$snapshot = offitravel_cabin_calculate_request_snapshot( OFFITRAVEL_CABIN_RIN_PRODUCT, offitravel_cabin_rin_request( 2, 'puente-intermedio' ) );
				$cart = array( 'offitravel_cabin_supplements' => $snapshot );
				offitravel_cabin_rin_same( 1568.0, offitravel_cabin_line_total( 1298, OFFITRAVEL_CABIN_RIN_PRODUCT, '', '', $cart ), 'Cabin total was not added exactly once.' );
				offitravel_cabin_rin_same( 1568.0, offitravel_cabin_line_total( 1298, OFFITRAVEL_CABIN_RIN_PRODUCT, '', '', $cart ), 'Repeated calculation accumulated the supplement.' );
			}
		);
	},
	'session restore uses the historical snapshot without current metadata' => static function () {
		offitravel_cabin_rin_with_metadata(
			static function () use ( &$snapshot ) {
				$snapshot = offitravel_cabin_calculate_request_snapshot( OFFITRAVEL_CABIN_RIN_PRODUCT, offitravel_cabin_rin_request( 2, 'puente-intermedio' ) );
			}
		);
		$restored = offitravel_cabin_restore_cart_item(
			array( 'product_id' => OFFITRAVEL_CABIN_RIN_PRODUCT ),
			array( 'product_id' => OFFITRAVEL_CABIN_RIN_PRODUCT, 'offitravel_cabin_supplements' => $snapshot )
		);
		offitravel_cabin_rin_same( '270.00', $restored['offitravel_cabin_supplements']['total'], 'Historical cabin snapshot was not restored.' );
	},
	'cart display is readable safe and includes the zero option' => static function () {
		offitravel_cabin_rin_with_metadata(
			static function () use ( &$snapshot ) {
				$snapshot = offitravel_cabin_calculate_request_snapshot( OFFITRAVEL_CABIN_RIN_PRODUCT, offitravel_cabin_rin_request( 2, 'puente-intermedio' ) );
			}
		);
		$rows = offitravel_cabin_cart_display( array(), array( 'data' => wc_get_product( OFFITRAVEL_CABIN_RIN_PRODUCT ), 'offitravel_cabin_supplements' => $snapshot ) );
		offitravel_cabin_rin_same( 'Suplemento de cabina', $rows[0]['key'], 'Cart row label is unclear.' );
		$text = wp_strip_all_tags( str_replace( '<br>', "\n", $rows[0]['value'] ) );
		foreach ( array( 'Cabina 1: 2 personas', 'Puente intermedio', '135,00 €', '270,00 €', 'Total suplementos de cabina: 270,00 €' ) as $fragment ) {
			if ( false === strpos( $text, $fragment ) ) {
				throw new RuntimeException( "Missing readable fragment: {$fragment}\n{$text}" );
			}
		}
		if ( preg_match( '/&(?:amp;)?nbsp;|<[^>]+>/i', $text ) ) {
			throw new RuntimeException( 'Visible escaped entities or HTML leaked into cabin text.' );
		}
	},
	'public booking markup exposes trusted configuration but no browser prices in inputs' => static function () {
		offitravel_cabin_rin_with_metadata(
			static function () {
				ob_start();
				offitravel_cabin_booking_markup( OFFITRAVEL_CABIN_RIN_PRODUCT );
				$html = ob_get_clean();
				if ( false === strpos( $html, 'data-offitravel-cabin-config' ) || false === strpos( $html, 'sin-suplemento' ) ) {
					throw new RuntimeException( 'Rin public configuration root was not rendered.' );
				}
				if ( false !== strpos( $html, 'name="offitravel_cabins' ) ) {
					throw new RuntimeException( 'PHP rendered cabin inputs before real room rows existed.' );
				}
			}
		);
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
