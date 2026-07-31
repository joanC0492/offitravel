<?php
/**
 * Calendar-day regressions for the Offitravel OVA BRW whitelist.
 *
 * This test is read-only. Synthetic whitelist metadata is supplied through
 * WordPress filters and is never persisted.
 *
 * Run with: php tests/offitravel-ovabrw-whitelist-availability-test.php
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
function offitravel_whitelist_test_same( $expected, $actual, $message ) {
	if ( $expected !== $actual ) {
		throw new RuntimeException( $message . "\nExpected: " . var_export( $expected, true ) . "\nActual: " . var_export( $actual, true ) );
	}
}

/**
 * Assert that a condition is true.
 *
 * @param bool   $condition Condition under test.
 * @param string $message   Failure context.
 * @return void
 * @throws RuntimeException When the condition is false.
 */
function offitravel_whitelist_test_true( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
}

/**
 * Build the minimal datepicker structure consumed by the whitelist filter.
 *
 * @param string $min_date Minimum date.
 * @param string $max_date Maximum date.
 * @return array
 */
function offitravel_whitelist_test_datepicker( $min_date, $max_date ) {
	return array(
		'LockPlugin'  => array(
			'minDate' => $min_date,
			'maxDate' => $max_date,
		),
		'startDate'   => $min_date,
		'disableDates' => array(),
		'bookedDates' => array(),
		'allowedDates' => array( 'provider-value-that-must-be-cleared' ),
	);
}

/**
 * Run the real whitelist filter with in-memory post metadata.
 *
 * @param string[] $starts    Allowed starts stored in Offitravel format.
 * @param string[] $ends      Optional allowed ends stored in Offitravel format.
 * @param array    $datepicker Datepicker input.
 * @return array
 */
function offitravel_whitelist_test_filter( array $starts, array $ends, array $datepicker ) {
	static $product_id = 900000;
	++$product_id;

	$metadata = array(
		OFFITRAVEL_OVABRW_WHITELIST_META_START => $starts,
		OFFITRAVEL_OVABRW_WHITELIST_META_END   => $ends,
		OFFITRAVEL_OVABRW_WHITELIST_META_DATES => array(),
	);

	$metadata_filter = static function ( $value, $object_id, $meta_key, $single ) use ( $product_id, $metadata ) {
		if ( $product_id !== (int) $object_id || ! array_key_exists( $meta_key, $metadata ) ) {
			return $value;
		}

		return $single ? array( $metadata[ $meta_key ] ) : array( $metadata[ $meta_key ] );
	};

	add_filter( 'get_post_metadata', $metadata_filter, 10, 4 );

	try {
		return offitravel_ovabrw_filter_datepicker_whitelist( $datepicker, $product_id, 'booking' );
	} finally {
		remove_filter( 'get_post_metadata', $metadata_filter, 10 );
	}
}

$tests = array(
	'month navigation bounds retain Madrid midnight across summer and winter offsets' => static function () {
		$bounds = offitravel_ovabrw_whitelist_month_nav_bounds(
			array(
				'2026-09-27' => true,
				'2026-12-08' => true,
			)
		);
		$timezone = wp_timezone();
		$minimum  = ( new DateTimeImmutable( '@' . $bounds['min_nav'] ) )->setTimezone( $timezone );
		$maximum  = ( new DateTimeImmutable( '@' . $bounds['max_nav'] ) )->setTimezone( $timezone );

		offitravel_whitelist_test_same( 'Europe/Madrid', wp_timezone_string(), 'Unexpected WordPress timezone.' );
		offitravel_whitelist_test_same( '2026-09-01 00:00:00 +02:00', $minimum->format( 'Y-m-d H:i:s P' ), 'Summer month minimum is not Madrid midnight.' );
		offitravel_whitelist_test_same( '2026-12-31 00:00:00 +01:00', $maximum->format( 'Y-m-d H:i:s P' ), 'Winter month maximum is not Madrid midnight.' );
		offitravel_whitelist_test_same( '2026-08-31 22:00:00', gmdate( 'Y-m-d H:i:s', $bounds['min_nav'] ), 'Summer UTC offset changed.' );
		offitravel_whitelist_test_same( '2026-12-30 23:00:00', gmdate( 'Y-m-d H:i:s', $bounds['max_nav'] ), 'Winter UTC offset changed.' );
	},
	'every local calendar day is visited once for 28, 29, 30 and 31 day months' => static function () {
		$timezone = wp_timezone();
		$cases = array(
			array( 'first' => '01-02-2026', 'last' => '28-02-2026', 'allowed' => '27-02-2026', 'days' => 28 ),
			array( 'first' => '01-02-2028', 'last' => '29-02-2028', 'allowed' => '28-02-2028', 'days' => 29 ),
			array( 'first' => '01-09-2026', 'last' => '30-09-2026', 'allowed' => '27-09-2026', 'days' => 30 ),
			array( 'first' => '01-10-2026', 'last' => '31-10-2026', 'allowed' => '25-10-2026', 'days' => 31 ),
			array( 'first' => '01-11-2026', 'last' => '30-11-2026', 'allowed' => '29-11-2026', 'days' => 30 ),
			array( 'first' => '01-12-2026', 'last' => '31-12-2026', 'allowed' => '08-12-2026', 'days' => 31 ),
		);

		foreach ( $cases as $case ) {
			$minimum = offitravel_ovabrw_whitelist_parse_local_date( $case['first'], 'd-m-Y', $timezone );
			$maximum = offitravel_ovabrw_whitelist_parse_local_date( $case['last'], 'd-m-Y', $timezone );
			$allowed = offitravel_ovabrw_whitelist_parse_local_date( $case['allowed'], 'd-m-Y', $timezone );

			offitravel_whitelist_test_true( $minimum && $maximum && $allowed, 'A fixed calendar fixture could not be parsed.' );
			$disabled = offitravel_ovabrw_whitelist_get_blocked_dates(
				array( $allowed->format( 'Y-m-d' ) => true ),
				$minimum,
				$maximum,
				'd-m-Y'
			);

			offitravel_whitelist_test_true( in_array( $case['last'], $disabled, true ), 'The invalid month end stayed open: ' . $case['last'] . '.' );
			offitravel_whitelist_test_true( ! in_array( $case['allowed'], $disabled, true ), 'An allowed date was disabled: ' . $case['allowed'] . '.' );
			offitravel_whitelist_test_same( $case['days'] - 1, count( $disabled ), 'The blocked-day count is wrong for ' . $case['last'] . '.' );
			offitravel_whitelist_test_same( count( $disabled ), count( array_unique( $disabled ) ), 'A local day was visited twice for ' . $case['last'] . '.' );

			if ( '31-10-2026' === $case['last'] ) {
				offitravel_whitelist_test_same( '+02:00', $minimum->format( 'P' ), 'October did not begin in Madrid summer time.' );
				offitravel_whitelist_test_same( '+01:00', $maximum->format( 'P' ), 'October did not end in Madrid winter time.' );
			}
		}
	},
	'an allowed month end remains selectable' => static function () {
		$timezone = wp_timezone();
		$minimum  = offitravel_ovabrw_whitelist_parse_local_date( '01-11-2026', 'd-m-Y', $timezone );
		$maximum  = offitravel_ovabrw_whitelist_parse_local_date( '30-11-2026', 'd-m-Y', $timezone );
		$disabled = offitravel_ovabrw_whitelist_get_blocked_dates(
			array( '2026-11-30' => true ),
			$minimum,
			$maximum,
			'd-m-Y'
		);

		offitravel_whitelist_test_true( ! in_array( '30-11-2026', $disabled, true ), 'A valid Danube-style month end was disabled.' );
		offitravel_whitelist_test_same( 29, count( $disabled ), 'The valid month-end control has the wrong blocked-day count.' );
	},
	'configured date formats are preserved without hard-coded day-month ordering' => static function () {
		$timezone = wp_timezone();
		$minimum  = offitravel_ovabrw_whitelist_parse_local_date( '2026-12-01', 'Y-m-d', $timezone );
		$maximum  = offitravel_ovabrw_whitelist_parse_local_date( '2026-12-31', 'Y-m-d', $timezone );
		$disabled = offitravel_ovabrw_whitelist_get_blocked_dates(
			array( '2026-12-08' => true ),
			$minimum,
			$maximum,
			'Y-m-d'
		);

		offitravel_whitelist_test_true( in_array( '2026-12-31', $disabled, true ), 'The formatted invalid month end stayed open.' );
		offitravel_whitelist_test_true( ! in_array( '2026-12-08', $disabled, true ), 'The formatted allowed date was disabled.' );
	},
	'public filter keeps an in-window allowed date open and blocks the month end' => static function () {
		$year       = (int) wp_date( 'Y' );
		$first      = sprintf( '01-09-%d', $year );
		$allowed    = sprintf( '15-09-%d', $year );
		$month_end  = sprintf( '30-09-%d', $year );
		$result     = offitravel_whitelist_test_filter(
			array( $allowed ),
			array( '' ),
			offitravel_whitelist_test_datepicker( $first, $month_end )
		);

		offitravel_whitelist_test_same( $month_end, $result['LockPlugin']['maxDate'], 'The public filter changed the navigation maximum.' );
		offitravel_whitelist_test_true( in_array( $month_end, $result['disableDates'], true ), 'The public filter left an invalid month end open.' );
		offitravel_whitelist_test_true( ! in_array( $allowed, $result['disableDates'], true ), 'The public filter disabled an allowed date.' );
		offitravel_whitelist_test_same( 29, count( $result['disableDates'] ), 'The public filter returned the wrong blocked-day count.' );
		offitravel_whitelist_test_same( array(), $result['allowedDates'], 'Provider allowed dates were not cleared.' );
	},
	'invalid datepicker boundaries return the original options unchanged' => static function () {
		$year       = (int) wp_date( 'Y' );
		$datepicker = offitravel_whitelist_test_datepicker( sprintf( '31-02-%d', $year ), sprintf( '31-12-%d', $year ) );
		$result     = offitravel_whitelist_test_filter( array( sprintf( '08-12-%d', $year ) ), array( '' ), $datepicker );

		offitravel_whitelist_test_same( $datepicker, $result, 'Invalid boundaries were normalized instead of rejected.' );
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
