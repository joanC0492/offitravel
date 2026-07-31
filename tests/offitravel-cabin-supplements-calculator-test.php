<?php
/**
 * Tests for the reusable, server-authoritative cabin supplement calculator.
 *
 * Every product, option, label and price in this file is synthetic. Metadata
 * reads are intercepted and no database writes are performed.
 *
 * Run with: php tests/offitravel-cabin-supplements-calculator-test.php
 *
 * @package Offitravel
 */

require dirname( __DIR__ ) . '/wp-load.php';

const OFFITRAVEL_CABIN_TEST_OVA_PRODUCT = 10618;
const OFFITRAVEL_CABIN_TEST_MISSING_PRODUCT = 424242;
const OFFITRAVEL_CABIN_TEST_NON_PRODUCT = 14;

/**
 * Assert strict equality.
 *
 * @param mixed  $expected Expected value.
 * @param mixed  $actual   Actual value.
 * @param string $message  Failure context.
 * @return void
 * @throws RuntimeException When values differ.
 */
function offitravel_cabin_calc_same( $expected, $actual, $message ) {
	if ( $expected !== $actual ) {
		throw new RuntimeException( $message . "\nExpected: " . var_export( $expected, true ) . "\nActual: " . var_export( $actual, true ) );
	}
}

/**
 * Assert a WordPress error code.
 *
 * @param string $expected Expected error code.
 * @param mixed  $actual   Actual result.
 * @param string $message  Failure context.
 * @return void
 * @throws RuntimeException When the error differs.
 */
function offitravel_cabin_calc_error( $expected, $actual, $message ) {
	$actual_code = is_wp_error( $actual ) ? $actual->get_error_code() : gettype( $actual );
	if ( $expected !== $actual_code ) {
		throw new RuntimeException( $message . "\nExpected error: {$expected}\nActual: {$actual_code}" );
	}
}

/**
 * Run a callback with isolated synthetic cabin metadata.
 *
 * @param callable $callback Test callback.
 * @param bool     $enabled    Whether the fixture product is activated.
 * @param int      $product_id Product ID whose metadata reads are intercepted.
 * @return void
 */
function offitravel_cabin_calc_with_metadata( $callback, $enabled = true, $product_id = OFFITRAVEL_CABIN_TEST_OVA_PRODUCT ) {
	$options = array(
		array( 'id' => 'synthetic-lower', 'label' => 'Synthetic Lower', 'price_per_person' => '14.37' ),
		array( 'id' => 'synthetic-upper', 'label' => 'Synthetic Upper', 'price_per_person' => '26.48' ),
	);
	$filter = static function ( $value, $object_id, $meta_key ) use ( $options, $enabled, $product_id ) {
		if ( (int) $product_id !== (int) $object_id ) {
			return $value;
		}
		if ( OFFITRAVEL_CABIN_META_ENABLED === $meta_key ) {
			return $enabled ? 'yes' : '';
		}
		if ( OFFITRAVEL_CABIN_META_OPTIONS === $meta_key ) {
			return array( $options );
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
	'offitravel_cabin_calculate_snapshot',
	'offitravel_cabin_normalize_snapshot',
	'offitravel_cabin_snapshot_total',
);
foreach ( $required as $function_name ) {
	if ( ! function_exists( $function_name ) ) {
		fwrite( STDERR, "[FAIL] Missing production function: {$function_name}\n" );
		exit( 1 );
	}
}

$tests = array(
	'calculator derives occupants and prices from trusted context and metadata' => static function () {
		offitravel_cabin_calc_with_metadata(
			static function () {
				$result = offitravel_cabin_calculate_snapshot(
					OFFITRAVEL_CABIN_TEST_OVA_PRODUCT,
					array(
						1 => array( 'people' => '2', 'category' => 'synthetic-lower', 'price_per_person' => '999', 'subtotal' => '999' ),
						2 => array( 'people' => '1', 'category' => 'synthetic-upper', 'price_per_person' => '999', 'subtotal' => '999' ),
					),
					array( 'offitravel_room_count' => 2, 'offitravel_room_people' => array( 2, 1 ) )
				);
				offitravel_cabin_calc_same( '55.22', $result['total'], 'Synthetic total was not calculated from stored prices.' );
				offitravel_cabin_calc_same( 2, $result['cabins'][1]['occupants'], 'First cabin occupancy was not authoritative.' );
				offitravel_cabin_calc_same( '14.37', $result['cabins'][1]['price_per_person'], 'Submitted price overrode stored metadata.' );
				offitravel_cabin_calc_same( '28.74', $result['cabins'][1]['subtotal'], 'First cabin subtotal is wrong.' );
				offitravel_cabin_calc_same( '26.48', $result['cabins'][2]['subtotal'], 'Second cabin subtotal is wrong.' );
			}
		);
	},
	'calculator does not invent a two-person occupancy limit' => static function () {
		offitravel_cabin_calc_with_metadata(
			static function () {
				$result = offitravel_cabin_calculate_snapshot(
					OFFITRAVEL_CABIN_TEST_OVA_PRODUCT,
					array( 1 => array( 'people' => '3', 'category' => 'synthetic-lower' ) ),
					array( 'offitravel_room_count' => 1, 'offitravel_room_people' => array( 3 ) )
				);
				offitravel_cabin_calc_same( '43.11', $result['total'], 'Valid configured occupancy was artificially limited.' );
			}
		);
	},
	'cabins must match the authoritative room count and occupancy' => static function () {
		offitravel_cabin_calc_with_metadata(
			static function () {
				$missing = offitravel_cabin_calculate_snapshot(
					OFFITRAVEL_CABIN_TEST_OVA_PRODUCT,
					array( 1 => array( 'people' => '2', 'category' => 'synthetic-lower' ) ),
					array( 'offitravel_room_count' => 2, 'offitravel_room_people' => array( 2, 1 ) )
				);
				offitravel_cabin_calc_error( 'offitravel_cabin_count_mismatch', $missing, 'A missing cabin was accepted.' );

				$mismatch = offitravel_cabin_calculate_snapshot(
					OFFITRAVEL_CABIN_TEST_OVA_PRODUCT,
					array( 1 => array( 'people' => '1', 'category' => 'synthetic-lower' ) ),
					array( 'offitravel_room_count' => 1, 'offitravel_room_people' => array( 2 ) )
				);
				offitravel_cabin_calc_error( 'offitravel_cabin_occupancy_mismatch', $mismatch, 'Browser occupancy overrode trusted context.' );
			}
		);
	},
	'unknown or malformed category selections are rejected' => static function () {
		offitravel_cabin_calc_with_metadata(
			static function () {
				foreach ( array( '', 'unknown-option', array( 'bad' ) ) as $category ) {
					$result = offitravel_cabin_calculate_snapshot(
						OFFITRAVEL_CABIN_TEST_OVA_PRODUCT,
						array( 1 => array( 'people' => '1', 'category' => $category ) ),
						array( 'offitravel_room_count' => 1, 'offitravel_room_people' => array( 1 ) )
					);
					offitravel_cabin_calc_error( 'offitravel_cabin_invalid_category', $result, 'Invalid category was accepted.' );
				}
			}
		);
	},
	'disabled products cannot use cabin options' => static function () {
		offitravel_cabin_calc_with_metadata(
			static function () {
				$result = offitravel_cabin_calculate_snapshot(
					OFFITRAVEL_CABIN_TEST_OVA_PRODUCT,
					array( 1 => array( 'people' => '1', 'category' => 'synthetic-lower' ) ),
					array( 'offitravel_room_count' => 1, 'offitravel_room_people' => array( 1 ) )
				);
				offitravel_cabin_calc_error( 'offitravel_cabin_product_disabled', $result, 'Disabled synthetic product was accepted.' );
			},
			false
		);
	},
	'nonexistent product is rejected even when metadata reads are forged' => static function () {
		offitravel_cabin_calc_with_metadata(
			static function () {
				$result = offitravel_cabin_calculate_snapshot(
					OFFITRAVEL_CABIN_TEST_MISSING_PRODUCT,
					array( 1 => array( 'people' => '1', 'category' => 'synthetic-lower' ) ),
					array( 'offitravel_room_count' => 1, 'offitravel_room_people' => array( 1 ) )
				);
				offitravel_cabin_calc_error( 'offitravel_cabin_invalid_product', $result, 'A nonexistent product was accepted.' );
			},
			true,
			OFFITRAVEL_CABIN_TEST_MISSING_PRODUCT
		);
	},
	'published non-product post is rejected even when metadata reads are forged' => static function () {
		offitravel_cabin_calc_with_metadata(
			static function () {
				$result = offitravel_cabin_calculate_snapshot(
					OFFITRAVEL_CABIN_TEST_NON_PRODUCT,
					array( 1 => array( 'people' => '1', 'category' => 'synthetic-lower' ) ),
					array( 'offitravel_room_count' => 1, 'offitravel_room_people' => array( 1 ) )
				);
				offitravel_cabin_calc_error( 'offitravel_cabin_invalid_product', $result, 'A WordPress page was accepted as a WooCommerce product.' );
			},
			true,
			OFFITRAVEL_CABIN_TEST_NON_PRODUCT
		);
	},
	'WooCommerce product resolved as a non-OVA type is rejected' => static function () {
		$class_filter = static function ( $classname, $product_type, $post_type, $product_id ) {
			return OFFITRAVEL_CABIN_TEST_OVA_PRODUCT === (int) $product_id ? 'WC_Product_Simple' : $classname;
		};
		add_filter( 'woocommerce_product_class', $class_filter, 10, 4 );
		try {
			offitravel_cabin_calc_with_metadata(
				static function () {
					$result = offitravel_cabin_calculate_snapshot(
						OFFITRAVEL_CABIN_TEST_OVA_PRODUCT,
						array( 1 => array( 'people' => '1', 'category' => 'synthetic-lower' ) ),
						array( 'offitravel_room_count' => 1, 'offitravel_room_people' => array( 1 ) )
					);
					offitravel_cabin_calc_error( 'offitravel_cabin_invalid_product_type', $result, 'A non-OVA WooCommerce product was accepted.' );
				}
			);
		} finally {
			remove_filter( 'woocommerce_product_class', $class_filter, 10 );
		}
	},
	'snapshot normalization is idempotent and self-contained' => static function () {
		offitravel_cabin_calc_with_metadata(
			static function () {
				$snapshot = offitravel_cabin_calculate_snapshot(
					OFFITRAVEL_CABIN_TEST_OVA_PRODUCT,
					array( 1 => array( 'people' => '2', 'category' => 'synthetic-upper' ) ),
					array( 'offitravel_room_count' => 1, 'offitravel_room_people' => array( 2 ) )
				);
				$normalized = offitravel_cabin_normalize_snapshot( $snapshot, OFFITRAVEL_CABIN_TEST_OVA_PRODUCT );
				offitravel_cabin_calc_same( $snapshot, $normalized, 'Normalization changed a valid snapshot.' );
				offitravel_cabin_calc_same( 52.96, offitravel_cabin_snapshot_total( $snapshot, OFFITRAVEL_CABIN_TEST_OVA_PRODUCT ), 'Snapshot total could not be rebuilt.' );
			}
		);
	},
	'snapshot with a nonnumeric product ID is rejected' => static function () {
		$snapshot = array(
			'version'    => 1,
			'product_id' => 'not-a-product',
			'cabins'     => array(
				1 => array(
					'cabin_index'      => 1,
					'occupants'        => 1,
					'category'         => 'synthetic-lower',
					'label'            => 'Synthetic Lower',
					'price_per_person' => '14.37',
					'subtotal'         => '14.37',
				),
			),
			'total'      => '14.37',
		);
		offitravel_cabin_calc_error(
			'offitravel_cabin_invalid_snapshot',
			offitravel_cabin_normalize_snapshot( $snapshot ),
			'A snapshot whose product ID normalizes to zero was accepted.'
		);
	},
	'invalid occupancy context is rejected without hardcoded limits' => static function () {
		offitravel_cabin_calc_with_metadata(
			static function () {
				foreach (
					array(
						array(),
						array( 'offitravel_room_count' => 2, 'offitravel_room_people' => array( 1 ) ),
						array( 'offitravel_room_count' => 1, 'offitravel_room_people' => array( 0 ) ),
					)
					as $context
				) {
					$result = offitravel_cabin_calculate_snapshot( OFFITRAVEL_CABIN_TEST_OVA_PRODUCT, array(), $context );
					offitravel_cabin_calc_error( 'offitravel_cabin_invalid_occupancy', $result, 'Invalid trusted occupancy was accepted.' );
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
