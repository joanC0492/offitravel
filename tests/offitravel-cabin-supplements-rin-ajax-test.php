<?php
/**
 * AJAX line-total tests for the Rin cabin supplement.
 *
 * Metadata reads are intercepted; this test performs no database writes.
 *
 * Run with: php tests/offitravel-cabin-supplements-rin-ajax-test.php
 *
 * @package Offitravel
 */

define( 'DOING_AJAX', true );
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
function offitravel_cabin_rin_ajax_same( $expected, $actual, $message ) {
	if ( $expected !== $actual ) {
		throw new RuntimeException( $message . "\nExpected: " . var_export( $expected, true ) . "\nActual: " . var_export( $actual, true ) );
	}
}

$options = array(
	array( 'id' => 'sin-suplemento', 'label' => 'Sin suplemento', 'price_per_person' => '0.00' ),
	array( 'id' => 'puente-intermedio', 'label' => 'Puente intermedio', 'price_per_person' => '135.00' ),
	array( 'id' => 'puente-superior', 'label' => 'Puente superior', 'price_per_person' => '200.00' ),
);
$filter = static function ( $value, $object_id, $meta_key ) use ( $options ) {
	if ( 11280 !== (int) $object_id ) {
		return $value;
	}
	if ( OFFITRAVEL_CABIN_META_OPTIONS === $meta_key ) {
		return array( $options );
	}
	if ( OFFITRAVEL_CABIN_META_ENABLED === $meta_key ) {
		return array( 'yes' );
	}
	return $value;
};
add_filter( 'get_post_metadata', $filter, 10, 4 );

$tests = array(
	'repeated AJAX calculation adds the selected cabin supplement once' => static function () {
		$_POST = array(
			'offitravel_room_count' => 1,
			'offitravel_room_people'=> array( 2 ),
			'offitravel_cabins'     => array( 1 => array( 'people' => 2, 'category' => 'puente-intermedio' ) ),
		);
		$first  = offitravel_cabin_line_total( 1298, 11280, '', '', array() );
		$second = offitravel_cabin_line_total( 1298, 11280, '', '', array() );
		offitravel_cabin_rin_ajax_same( 1568.0, $first, 'First AJAX total is wrong.' );
		offitravel_cabin_rin_ajax_same( 1568.0, $second, 'Repeated AJAX total accumulated the supplement.' );
	},
	'zero option and invalid category cannot inject a forged amount' => static function () {
		$_POST = array(
			'offitravel_room_count' => 1,
			'offitravel_room_people'=> array( 2 ),
			'offitravel_cabins'     => array( 1 => array( 'people' => 2, 'category' => 'sin-suplemento', 'price_per_person' => 999, 'total' => 999 ) ),
		);
		offitravel_cabin_rin_ajax_same( 1298.0, offitravel_cabin_line_total( 1298, 11280, '', '', array() ), 'Forged zero-option price was accepted.' );
		$_POST['offitravel_cabins'][1]['category'] = 'not-stored';
		offitravel_cabin_rin_ajax_same( 1298.0, offitravel_cabin_line_total( 1298, 11280, '', '', array() ), 'Invalid category altered the AJAX total.' );
	},
	'cabin pricing remains after the existing add-on filter in the total chain' => static function () {
		$_POST = array(
			'offitravel_room_count' => 1,
			'offitravel_room_people'=> array( 2 ),
			'offitravel_cabins'     => array( 1 => array( 'people' => 2, 'category' => 'puente-superior' ) ),
		);
		$after_addons = offitravel_addon_line_total( 1298, 11280, '', '', array() );
		$after_cabins = offitravel_cabin_line_total( $after_addons, 11280, '', '', array() );
		offitravel_cabin_rin_ajax_same( 1698.0, $after_cabins, 'Cabin filter did not add after existing add-ons.' );
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
remove_filter( 'get_post_metadata', $filter, 10 );
printf( "%d test(s), %d failure(s).\n", count( $tests ), $failures );
exit( $failures > 0 ? 1 : 0 );
