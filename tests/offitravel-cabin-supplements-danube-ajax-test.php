<?php
/**
 * AJAX line-total tests for Danube cabin supplements.
 *
 * Run with: php tests/offitravel-cabin-supplements-danube-ajax-test.php
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
function offitravel_cabin_danube_ajax_same( $expected, $actual, $message ) {
	if ( $expected !== $actual ) {
		throw new RuntimeException( $message . "\nExpected: " . var_export( $expected, true ) . "\nActual: " . var_export( $actual, true ) );
	}
}

$tests = array(
	'repeated AJAX calculations add a single Danube supplement once' => static function () {
		$_POST = array(
			'offitravel_room_count'  => 1,
			'offitravel_room_people' => array( 2 ),
			'offitravel_cabins'      => array( 1 => array( 'people' => 2, 'category' => 'puente-intermedio' ) ),
		);
		$first  = offitravel_cabin_line_total( 1250, 11259, '', '', array() );
		$second = offitravel_cabin_line_total( 1250, 11259, '', '', array() );
		offitravel_cabin_danube_ajax_same( 1473.0, $first, 'First AJAX total is wrong.' );
		offitravel_cabin_danube_ajax_same( 1473.0, $second, 'Repeated AJAX total accumulated the supplement.' );
	},
	'multiple cabins support exact shared and independent categories' => static function () {
		$_POST = array(
			'offitravel_room_count'  => 2,
			'offitravel_room_people' => array( 3, 2 ),
			'offitravel_cabins'      => array(
				1 => array( 'people' => 3, 'category' => 'puente-intermedio' ),
				2 => array( 'people' => 2, 'category' => 'puente-intermedio' ),
			),
		);
		offitravel_cabin_danube_ajax_same( 1807.5, offitravel_cabin_line_total( 1250, 11259, '', '', array() ), 'Two-cabin intermediate total is wrong.' );
		$_POST['offitravel_cabins'][2]['category'] = 'puente-superior';
		offitravel_cabin_danube_ajax_same( 1984.5, offitravel_cabin_line_total( 1250, 11259, '', '', array() ), 'Independent cabin total is wrong.' );
	},
	'forged amounts and invalid categories cannot alter the AJAX total' => static function () {
		$_POST = array(
			'offitravel_room_count'  => 1,
			'offitravel_room_people' => array( 2 ),
			'offitravel_cabins'      => array( 1 => array( 'people' => 2, 'category' => 'sin-suplemento', 'price_per_person' => 999, 'subtotal' => 999, 'total' => 999 ) ),
		);
		offitravel_cabin_danube_ajax_same( 1250.0, offitravel_cabin_line_total( 1250, 11259, '', '', array() ), 'Forged zero-option amount was accepted.' );
		$_POST['offitravel_cabins'][1]['category'] = 'not-stored';
		offitravel_cabin_danube_ajax_same( 1250.0, offitravel_cabin_line_total( 1250, 11259, '', '', array() ), 'Invalid category altered the AJAX total.' );
	},
	'cabin pricing remains after the existing add-on filter' => static function () {
		$_POST = array(
			'offitravel_room_count'  => 1,
			'offitravel_room_people' => array( 2 ),
			'offitravel_cabins'      => array( 1 => array( 'people' => 2, 'category' => 'puente-superior' ) ),
		);
		$after_addons = offitravel_addon_line_total( 1250, 11259, '', '', array() );
		$after_cabins = offitravel_cabin_line_total( $after_addons, 11259, '', '', array() );
		offitravel_cabin_danube_ajax_same( 1650.0, $after_cabins, 'Cabin filter did not add after existing add-ons.' );
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

