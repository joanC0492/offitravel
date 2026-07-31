<?php
/**
 * AJAX and cart-data regression tests for fixed musical add-ons.
 *
 * Run with: php tests/offitravel-product-addons-fixed-ajax-test.php
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
function offitravel_fixed_ajax_same( $expected, $actual, $message ) {
	if ( $expected !== $actual ) {
		throw new RuntimeException( $message . "\nExpected: " . var_export( $expected, true ) . "\nActual: " . var_export( $actual, true ) );
	}
}

$tests = array(
	'repeated AJAX calculation adds manual KIT and cancellation once' => static function () {
		$cancellation = get_page_by_path( 'seguro-anulacion-musicales', OBJECT, OFFITRAVEL_ADDON_PT );
		$_POST = array(
			'action'                         => 'ovabrw_calculate_total',
			'offitravel_addons'              => array( 12027, $cancellation->ID ),
			'offitravel_addon_quantities'    => array( 12027 => '3', $cancellation->ID => '999' ),
			'adults'                         => '8',
			'quantity'                       => '99',
			'offitravel_submitted_unit_price' => '0.01',
		);
		$first  = offitravel_addon_line_total( 100.0, 11528, '', '', array() );
		$second = offitravel_addon_line_total( 100.0, 11528, '', '', array() );
		offitravel_fixed_ajax_same( 142.0, $first, 'First AJAX total is incorrect.' );
		offitravel_fixed_ajax_same( 142.0, $second, 'Repeated AJAX calculation accumulated supplements.' );
	},
	'cart data stores a server-calculated fixed snapshot' => static function () {
		$cancellation = get_page_by_path( 'seguro-anulacion-musicales', OBJECT, OFFITRAVEL_ADDON_PT );
		$_POST = array(
			'offitravel_addons'           => array( 12027, $cancellation->ID ),
			'offitravel_addon_quantities' => array( 12027 => '1' ),
			'ovabrw_adults'               => '12',
			'ovabrw_quantity'             => '77',
		);
		$data = offitravel_addon_cart_data( array(), 11528, 1 );
		offitravel_fixed_ajax_same( '18.00', $data['offitravel_fixed_addons']['total'], 'Cart snapshot total is incorrect.' );
		offitravel_fixed_ajax_same( '12.00', $data['offitravel_fixed_addons']['services'][12027]['unit_price'], 'Browser data altered the KIT price.' );
		offitravel_fixed_ajax_same( '6.00', $data['offitravel_fixed_addons']['services'][ $cancellation->ID ]['total'], 'Cancellation was multiplied in cart data.' );
	},
	'invalid manual quantity cannot add a forged amount through AJAX' => static function () {
		$_POST = array(
			'action'                      => 'ovabrw_calculate_total',
			'offitravel_addons'           => array( 12027 ),
			'offitravel_addon_quantities' => array( 12027 => '1.5' ),
			'price'                       => '999999',
		);
		offitravel_fixed_ajax_same( 100.0, offitravel_addon_line_total( 100.0, 11528, '', '', array() ), 'Invalid manual quantity altered the total.' );
	},
	'unassigned cancellation ID cannot be used on a circuit' => static function () {
		$cancellation = get_page_by_path( 'seguro-anulacion-musicales', OBJECT, OFFITRAVEL_ADDON_PT );
		$_POST = array(
			'action'              => 'ovabrw_calculate_total',
			'offitravel_addons'   => array( $cancellation->ID ),
			'ovabrw_quantity'     => '1',
		);
		offitravel_fixed_ajax_same( 100.0, offitravel_addon_line_total( 100.0, 9487, '', '', array() ), 'Unassigned service altered a circuit total.' );
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
$_POST = array();
printf( "%d test(s), %d failure(s).\n", count( $tests ), $failures );
exit( $failures > 0 ? 1 : 0 );
