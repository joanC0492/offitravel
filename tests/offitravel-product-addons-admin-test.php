<?php
/**
 * Regression tests for the administrative product add-on configuration.
 *
 * Run with: php tests/offitravel-product-addons-admin-test.php
 *
 * @package Offitravel
 */

require dirname( __DIR__ ) . '/wp-load.php';

/**
 * Assert that two values are strictly equal.
 *
 * @param mixed  $expected Expected value.
 * @param mixed  $actual   Actual value.
 * @param string $message  Failure context.
 * @return void
 * @throws RuntimeException When the assertion fails.
 */
function offitravel_test_assert_same( $expected, $actual, $message ) {
	if ( $expected !== $actual ) {
		throw new RuntimeException(
			$message . '\nExpected: ' . var_export( $expected, true ) . '\nActual: ' . var_export( $actual, true )
		);
	}
}

/**
 * Assert that a validation result contains the expected WordPress error.
 *
 * @param string $expected_code Expected error code.
 * @param mixed  $actual        Validation result.
 * @param string $message       Failure context.
 * @return void
 * @throws RuntimeException When the assertion fails.
 */
function offitravel_test_assert_wp_error( $expected_code, $actual, $message ) {
	if ( ! is_wp_error( $actual ) || $expected_code !== $actual->get_error_code() ) {
		$actual_code = is_wp_error( $actual ) ? $actual->get_error_code() : gettype( $actual );
		throw new RuntimeException( $message . '\nExpected error: ' . $expected_code . '\nActual: ' . $actual_code );
	}
}

/**
 * Require a production function before exercising its contract.
 *
 * @param string $function_name Function expected from the MU plugin.
 * @return void
 * @throws RuntimeException When the function is unavailable.
 */
function offitravel_test_require_function( $function_name ) {
	if ( ! function_exists( $function_name ) ) {
		throw new RuntimeException( 'Missing production function: ' . $function_name );
	}
}

/**
 * Execute the real save callback while intercepting metadata writes in memory.
 *
 * @param int                 $post_id Add-on post ID.
 * @param array<string,mixed> $payload Administrative payload without nonce boilerplate.
 * @return array{updates:array<string,mixed>,deletes:string[]}
 */
function offitravel_test_capture_addon_save( $post_id, array $payload ) {
	$updates = array();
	$deletes = array();

	$update_filter = static function ( $check, $object_id, $meta_key, $meta_value ) use ( $post_id, &$updates ) {
		if ( $post_id !== (int) $object_id ) {
			return $check;
		}
		$updates[ $meta_key ] = $meta_value;
		return true;
	};
	$delete_filter = static function ( $check, $object_id, $meta_key ) use ( $post_id, &$deletes ) {
		if ( $post_id !== (int) $object_id ) {
			return $check;
		}
		$deletes[] = $meta_key;
		return true;
	};

	$previous_user = get_current_user_id();
	$previous_post = $_POST;
	wp_set_current_user( 1 );
	add_filter( 'update_post_metadata', $update_filter, 10, 5 );
	add_filter( 'delete_post_metadata', $delete_filter, 10, 5 );

	$_POST = array_merge(
		array(
			'post_type'              => 'offitravel_prd_addon',
			'offitravel_addon_nonce' => wp_create_nonce( 'offitravel_addon_save' ),
		),
		$payload
	);
	offitravel_addon_save( $post_id );

	remove_filter( 'update_post_metadata', $update_filter, 10 );
	remove_filter( 'delete_post_metadata', $delete_filter, 10 );
	$_POST = $previous_post;
	wp_set_current_user( $previous_user );

	return array(
		'updates' => $updates,
		'deletes' => $deletes,
	);
}

/**
 * Run multiple real save callbacks against an isolated in-memory metadata map.
 *
 * Reads, updates and deletions for managed add-on keys are intercepted. The
 * returned history represents the persisted state after each save without
 * writing to the WordPress database.
 *
 * @param int                   $post_id       Existing post used for capability checks.
 * @param array<string,mixed>   $initial_state Initial isolated metadata.
 * @param array<int,array>      $payloads      Consecutive administrative payloads.
 * @return array<int,array<string,mixed>> State after every save.
 */
function offitravel_test_run_addon_save_sequence( $post_id, array $initial_state, array $payloads ) {
	$state = $initial_state;
	$history = array();
	$managed_keys = array(
		'_offitravel_addon_public_label',
		'_offitravel_addon_price_model',
		'_offitravel_addon_age_rules',
		'_offitravel_addon_price',
		'_offitravel_addon_billing',
		'_offitravel_addon_product_ids',
	);

	$get_filter = static function ( $value, $object_id, $meta_key ) use ( $post_id, $managed_keys, &$state ) {
		if ( $post_id !== (int) $object_id || ! in_array( $meta_key, $managed_keys, true ) ) {
			return $value;
		}
		if ( ! array_key_exists( $meta_key, $state ) ) {
			return '';
		}
		$stored = $state[ $meta_key ];
		return is_array( $stored ) ? array( $stored ) : $stored;
	};
	$update_filter = static function ( $check, $object_id, $meta_key, $meta_value ) use ( $post_id, $managed_keys, &$state ) {
		if ( $post_id !== (int) $object_id || ! in_array( $meta_key, $managed_keys, true ) ) {
			return $check;
		}
		$state[ $meta_key ] = $meta_value;
		return true;
	};
	$delete_filter = static function ( $check, $object_id, $meta_key ) use ( $post_id, $managed_keys, &$state ) {
		if ( $post_id !== (int) $object_id || ! in_array( $meta_key, $managed_keys, true ) ) {
			return $check;
		}
		unset( $state[ $meta_key ] );
		return true;
	};

	$previous_user = get_current_user_id();
	$previous_post = $_POST;
	wp_set_current_user( 1 );
	add_filter( 'get_post_metadata', $get_filter, 10, 4 );
	add_filter( 'update_post_metadata', $update_filter, 10, 5 );
	add_filter( 'delete_post_metadata', $delete_filter, 10, 5 );
	try {
		foreach ( $payloads as $payload ) {
			$_POST = array_merge(
				array(
					'post_type'              => 'offitravel_prd_addon',
					'offitravel_addon_nonce' => wp_create_nonce( 'offitravel_addon_save' ),
				),
				$payload
			);
			offitravel_addon_save( $post_id );
			$history[] = $state;
		}
	} finally {
		remove_filter( 'get_post_metadata', $get_filter, 10 );
		remove_filter( 'update_post_metadata', $update_filter, 10 );
		remove_filter( 'delete_post_metadata', $delete_filter, 10 );
		$_POST = $previous_post;
		wp_set_current_user( $previous_user );
	}

	return $history;
}

/**
 * Execute a callback with temporary metadata values for one existing add-on.
 *
 * The WordPress metadata read boundary is intercepted, so the real database is
 * never modified. Every production query, renderer and calculator remains real.
 *
 * @param int                 $post_id Add-on post used as an isolated fixture.
 * @param array<string,mixed> $metadata Complete metadata values to overlay.
 * @param callable            $callback Assertions executed with the overlay.
 * @return void
 */
function offitravel_test_with_addon_metadata_overlay( $post_id, array $metadata, $callback ) {
	$filter = static function ( $value, $object_id, $meta_key ) use ( $post_id, $metadata ) {
		if ( $post_id === (int) $object_id && array_key_exists( $meta_key, $metadata ) ) {
			$fixture_value = $metadata[ $meta_key ];
			return is_array( $fixture_value ) ? array( $fixture_value ) : $fixture_value;
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

$tests = array(
	'legacy services default to the fixed model without migration' => static function () {
		offitravel_test_require_function( 'offitravel_addon_normalize_price_model' );
		offitravel_test_assert_same( 'fixed', offitravel_addon_normalize_price_model( '' ), 'Missing model metadata must remain fixed.' );
		offitravel_test_assert_same( 'fixed', offitravel_addon_normalize_price_model( null ), 'Null model metadata must remain fixed.' );
	},
	'valid age rules normalize integer limits and WooCommerce decimals' => static function () {
		offitravel_test_require_function( 'offitravel_addon_validate_age_rules' );
		$actual = offitravel_addon_validate_age_rules(
			array(
				array( 'min_age' => '0', 'max_age' => '69', 'price' => '32,50' ),
				array( 'min_age' => '70', 'max_age' => '', 'price' => '45.50' ),
			)
		);
		offitravel_test_assert_same(
			array(
				array( 'min_age' => 0, 'max_age' => 69, 'price' => '32.50' ),
				array( 'min_age' => 70, 'max_age' => null, 'price' => '45.50' ),
			),
			$actual,
			'Valid age rules were not normalized as expected.'
		);
	},
	'age rules may contain gaps because no continuous commercial coverage is defined' => static function () {
		offitravel_test_require_function( 'offitravel_addon_validate_age_rules' );
		$actual = offitravel_addon_validate_age_rules(
			array(
				array( 'min_age' => '0', 'max_age' => '10', 'price' => '1' ),
				array( 'min_age' => '20', 'max_age' => '', 'price' => '2' ),
			)
		);
		offitravel_test_assert_same( 20, $actual[1]['min_age'], 'A technically valid gap must not be rejected.' );
	},
	'invalid age rules are rejected server side' => static function () {
		offitravel_test_require_function( 'offitravel_addon_validate_age_rules' );
		$cases = array(
			'empty rules'       => array( array(), 'offitravel_addon_age_rules_required' ),
			'negative age'      => array( array( array( 'min_age' => '-1', 'max_age' => '69', 'price' => '10' ) ), 'offitravel_addon_invalid_age' ),
			'decimal age'       => array( array( array( 'min_age' => '1.5', 'max_age' => '69', 'price' => '10' ) ), 'offitravel_addon_invalid_age' ),
			'inverted range'    => array( array( array( 'min_age' => '70', 'max_age' => '69', 'price' => '10' ) ), 'offitravel_addon_invalid_age_range' ),
			'overlapping range' => array(
				array(
					array( 'min_age' => '0', 'max_age' => '69', 'price' => '10' ),
					array( 'min_age' => '69', 'max_age' => '', 'price' => '20' ),
				),
				'offitravel_addon_overlapping_age_ranges',
			),
			'open range first'  => array(
				array(
					array( 'min_age' => '0', 'max_age' => '', 'price' => '10' ),
					array( 'min_age' => '70', 'max_age' => '80', 'price' => '20' ),
				),
				'offitravel_addon_overlapping_age_ranges',
			),
			'invalid price'     => array( array( array( 'min_age' => '0', 'max_age' => '', 'price' => '-1' ) ), 'offitravel_addon_invalid_rule_price' ),
		);

		foreach ( $cases as $label => $case ) {
			offitravel_test_assert_wp_error( $case[1], offitravel_addon_validate_age_rules( $case[0] ), 'Case failed: ' . $label );
		}
	},
	'age pricing rejects non-person billing combinations' => static function () {
		offitravel_test_require_function( 'offitravel_addon_validate_admin_payload' );
		$actual = offitravel_addon_validate_admin_payload(
			array(
				'_offitravel_addon_public_label' => 'Seguro de viaje',
				'_offitravel_addon_price_model'  => 'traveler_age',
				'_offitravel_addon_billing'      => 'room',
				'_offitravel_addon_age_rules'    => array(
					array( 'min_age' => '0', 'max_age' => '', 'price' => '32.50' ),
				),
			)
		);
		offitravel_test_assert_wp_error( 'offitravel_addon_age_requires_person_billing', $actual, 'Age pricing must not accept room billing.' );
	},
	'legacy fixed payload keeps price billing and product assignments' => static function () {
		offitravel_test_require_function( 'offitravel_addon_validate_admin_payload' );
		$actual = offitravel_addon_validate_admin_payload(
			array(
				'_offitravel_addon_public_label' => '',
				'_offitravel_addon_price_model'  => 'fixed',
				'_offitravel_addon_price'        => '12',
				'_offitravel_addon_billing'      => 'room',
				'_offitravel_addon_product_ids'  => array( '10628', '10618' ),
			)
		);
		offitravel_test_assert_same( 'fixed', $actual['price_model'], 'Legacy fixed model changed.' );
		offitravel_test_assert_same( '', $actual['public_label'], 'Empty public labels must keep the title fallback.' );
		offitravel_test_assert_same( '12.00', $actual['price'], 'Legacy price changed.' );
		offitravel_test_assert_same( 'room', $actual['billing'], 'Legacy billing changed.' );
		offitravel_test_assert_same( array( 10628, 10618 ), $actual['product_ids'], 'Legacy assignments changed.' );
	},
	'admin metabox exposes public label price model age rules and booking billing' => static function () {
		$post = get_post( 12027 );
		ob_start();
		offitravel_addon_metabox_render( $post );
		$html = (string) ob_get_clean();

		foreach ( array( '_offitravel_addon_public_label', '_offitravel_addon_price_model', '_offitravel_addon_age_rules', 'value="booking"' ) as $needle ) {
			if ( false === strpos( $html, $needle ) ) {
				throw new RuntimeException( 'Missing administrative control: ' . $needle );
			}
		}
	},
	'admin edit screen enqueues the dedicated configuration script' => static function () {
		$previous_get = $_GET;
		$_GET         = array( 'post' => '12027' );
		offitravel_addon_enqueue_admin( 'post.php' );
		$_GET = $previous_get;

		offitravel_test_assert_same( true, wp_script_is( 'offitravel-product-addons-admin', 'enqueued' ), 'Administrative behavior script was not enqueued.' );
	},
	'valid age configuration persists structured metadata without replacing fixed price' => static function () {
		$capture = offitravel_test_capture_addon_save(
			12027,
			array(
				'_offitravel_addon_public_label' => 'Seguro de viaje',
				'_offitravel_addon_price_model'  => 'traveler_age',
				'_offitravel_addon_age_rules'    => array(
					array( 'min_age' => '0', 'max_age' => '69', 'price' => '32,50' ),
					array( 'min_age' => '70', 'max_age' => '', 'price' => '45,50' ),
				),
				'_offitravel_addon_product_ids'  => array( '9475', '9487' ),
			)
		);

		$updates = $capture['updates'];
		offitravel_test_assert_same( 'Seguro de viaje', $updates['_offitravel_addon_public_label'], 'Public label was not persisted.' );
		offitravel_test_assert_same( 'traveler_age', $updates['_offitravel_addon_price_model'], 'Age model was not persisted.' );
		offitravel_test_assert_same( false, isset( $updates['_offitravel_addon_billing'] ), 'Age model overwrote the stored fixed billing.' );
		offitravel_test_assert_same(
			array(
				array( 'min_age' => 0, 'max_age' => 69, 'price' => '32.50' ),
				array( 'min_age' => 70, 'max_age' => null, 'price' => '45.50' ),
			),
			$updates['_offitravel_addon_age_rules'],
			'Age rules were not stored structurally.'
		);
		offitravel_test_assert_same( array( 9475, 9487 ), $updates['_offitravel_addon_product_ids'], 'Product assignments were not persisted.' );
		offitravel_test_assert_same( false, isset( $updates['_offitravel_addon_price'] ), 'Age configuration must preserve rather than overwrite the fixed-price metadata.' );
	},
	'fixed room billing survives a complete fixed traveler age fixed journey' => static function () {
		$history = offitravel_test_run_addon_save_sequence(
			12027,
			array(
				'_offitravel_addon_price'       => '12',
				'_offitravel_addon_billing'     => 'room',
				'_offitravel_addon_product_ids' => array( 10618 ),
			),
			array(
				array(
					'_offitravel_addon_price_model' => 'traveler_age',
					'_offitravel_addon_age_rules'   => array(
						array( 'min_age' => '0', 'max_age' => '', 'price' => '32.50' ),
					),
					'_offitravel_addon_product_ids' => array( 10618 ),
				),
				array(
					'_offitravel_addon_price_model' => 'fixed',
					'_offitravel_addon_price'       => '12',
					'_offitravel_addon_billing'     => 'room',
					'_offitravel_addon_product_ids' => array( 10618 ),
				),
			)
		);

		$age_state = $history[0];
		offitravel_test_assert_same( 'traveler_age', $age_state['_offitravel_addon_price_model'], 'Age model was not stored.' );
		offitravel_test_assert_same( 'room', $age_state['_offitravel_addon_billing'], 'Room billing was not preserved while age pricing was active.' );
		offitravel_test_assert_same( '12', $age_state['_offitravel_addon_price'], 'Fixed price was not preserved while age pricing was active.' );
		offitravel_test_with_addon_metadata_overlay(
			12027,
			$age_state,
			static function () {
				offitravel_test_assert_same( array(), offitravel_addon_validate_ids( array( 12027 ), 10618 ), 'Age model became publicly selectable during the journey.' );
			}
		);

		$fixed_state = $history[1];
		offitravel_test_assert_same( false, array_key_exists( '_offitravel_addon_price_model', $fixed_state ), 'Fixed fallback should remove the explicit age model.' );
		offitravel_test_assert_same( 'room', $fixed_state['_offitravel_addon_billing'], 'Room billing was not restored.' );
		offitravel_test_assert_same( '12', $fixed_state['_offitravel_addon_price'], 'Fixed price was not restored.' );
		offitravel_test_with_addon_metadata_overlay(
			12027,
			$fixed_state,
			static function () {
				offitravel_test_assert_same( 36.0, offitravel_addon_sum( array( 12027 ), array( 'offitravel_room_count' => 3, 'ovabrw_adults' => 6, 'ovabrw_quantity' => 1 ) ), 'Restored room billing did not calculate 12 euros per room.' );
			}
		);
	},
	'fixed booking billing survives a complete fixed traveler age fixed journey' => static function () {
		$history = offitravel_test_run_addon_save_sequence(
			12027,
			array(
				'_offitravel_addon_price'       => '12',
				'_offitravel_addon_billing'     => 'booking',
				'_offitravel_addon_product_ids' => array( 10618 ),
			),
			array(
				array(
					'_offitravel_addon_price_model' => 'traveler_age',
					'_offitravel_addon_age_rules'   => array( array( 'min_age' => '0', 'max_age' => '', 'price' => '6' ) ),
					'_offitravel_addon_product_ids' => array( 10618 ),
				),
				array(
					'_offitravel_addon_price_model' => 'fixed',
					'_offitravel_addon_price'       => '12',
					'_offitravel_addon_billing'     => 'booking',
					'_offitravel_addon_product_ids' => array( 10618 ),
				),
			)
		);

		offitravel_test_assert_same( 'booking', $history[0]['_offitravel_addon_billing'], 'Booking billing was not preserved while age pricing was active.' );
		offitravel_test_assert_same( 'booking', $history[1]['_offitravel_addon_billing'], 'Booking billing was not restored.' );
		offitravel_test_with_addon_metadata_overlay(
			12027,
			$history[1],
			static function () {
				offitravel_test_assert_same( 12.0, offitravel_addon_sum( array( 12027 ), array( 'ovabrw_adults' => 4, 'offitravel_room_count' => 2, 'ovabrw_quantity' => 1 ) ), 'Restored booking billing did not calculate once.' );
			}
		);
	},
	'new traveler age service without fixed billing defaults to person when later saved as fixed' => static function () {
		$history = offitravel_test_run_addon_save_sequence(
			12027,
			array(),
			array(
				array(
					'_offitravel_addon_price_model' => 'traveler_age',
					'_offitravel_addon_age_rules'   => array( array( 'min_age' => '0', 'max_age' => '', 'price' => '20' ) ),
					'_offitravel_addon_product_ids' => array(),
				),
				array(
					'_offitravel_addon_price_model' => 'fixed',
					'_offitravel_addon_price'       => '15',
					'_offitravel_addon_product_ids' => array(),
				),
			)
		);

		offitravel_test_assert_same( false, array_key_exists( '_offitravel_addon_billing', $history[0] ), 'New age service unexpectedly created fixed billing metadata.' );
		offitravel_test_assert_same( 'person', $history[1]['_offitravel_addon_billing'], 'Missing fixed billing did not use the legacy person default.' );
		offitravel_test_assert_same( '15.00', $history[1]['_offitravel_addon_price'], 'New fixed price was not stored after leaving age pricing.' );
	},
	'invalid age configuration performs no metadata writes' => static function () {
		$capture = offitravel_test_capture_addon_save(
			12027,
			array(
				'_offitravel_addon_public_label' => 'Seguro de viaje',
				'_offitravel_addon_price_model'  => 'traveler_age',
				'_offitravel_addon_billing'      => 'room',
				'_offitravel_addon_age_rules'    => array(
					array( 'min_age' => '0', 'max_age' => '', 'price' => '32.50' ),
				),
				'_offitravel_addon_product_ids'  => array( '9475' ),
			)
		);
		offitravel_test_assert_same( array(), $capture['updates'], 'Invalid configuration caused partial metadata writes.' );
		offitravel_test_assert_same( array(), $capture['deletes'], 'Invalid configuration caused partial metadata deletions.' );
	},
	'traveler age service is excluded from public query rendering and validation' => static function () {
		offitravel_test_with_addon_metadata_overlay(
			12027,
			array(
				'_offitravel_addon_price_model' => 'traveler_age',
				'_offitravel_addon_price'       => '12',
				'_offitravel_addon_billing'     => 'person',
				'_offitravel_addon_product_ids' => array( 10618 ),
			),
			static function () {
				$public_ids = array_map(
					static function ( $post ) {
						return (int) $post->ID;
					},
					offitravel_addon_posts_for_product( 10618 )
				);
				offitravel_test_assert_same( false, in_array( 12027, $public_ids, true ), 'Age service leaked through the public service query.' );
				offitravel_test_assert_same( array(), offitravel_addon_validate_ids( array( 12027 ), 10618 ), 'Age service ID was accepted publicly.' );

				ob_start();
				offitravel_addon_booking_markup( array( 'id' => 10618 ) );
				$html = (string) ob_get_clean();
				offitravel_test_assert_same( false, false !== strpos( $html, 'value="12027"' ), 'Age service checkbox was rendered publicly.' );
			}
		);
	},
	'traveler age service cannot use its retained fixed price through calculation or forged cart data' => static function () {
		offitravel_test_with_addon_metadata_overlay(
			12027,
			array(
				'_offitravel_addon_price_model' => 'traveler_age',
				'_offitravel_addon_price'       => '12',
				'_offitravel_addon_billing'     => 'person',
				'_offitravel_addon_product_ids' => array( 10618 ),
			),
			static function () {
				$cart_item = array(
					'product_id'          => 10618,
					'ovabrw_adults'       => 2,
					'ovabrw_quantity'     => 1,
					'offitravel_addons'   => array( 12027 ),
				);
				offitravel_test_assert_same( 0.0, offitravel_addon_sum( array( 12027 ), $cart_item ), 'Age service reused its retained fixed price.' );
				offitravel_test_assert_same( 100.0, offitravel_addon_line_total( 100.0, 10618, '', '', $cart_item ), 'Forged age service changed the fixed-price line total.' );
			}
		);
	},
	'the same isolated service works again when its model returns to fixed' => static function () {
		offitravel_test_with_addon_metadata_overlay(
			12027,
			array(
				'_offitravel_addon_price_model' => 'fixed',
				'_offitravel_addon_price'       => '12',
				'_offitravel_addon_billing'     => 'person',
				'_offitravel_addon_product_ids' => array( 10618 ),
			),
			static function () {
				$validated = offitravel_addon_validate_ids( array( 12027 ), 10618 );
				offitravel_test_assert_same( array( 12027 ), $validated, 'Fixed service did not return to public validation.' );
				offitravel_test_assert_same( 24.0, offitravel_addon_sum( $validated, array( 'ovabrw_adults' => 2, 'ovabrw_quantity' => 1 ) ), 'Fixed service did not restore its retained price and modality.' );

				ob_start();
				offitravel_addon_booking_markup( array( 'id' => 10618 ) );
				$html = (string) ob_get_clean();
				offitravel_test_assert_same( true, false !== strpos( $html, 'value="12027"' ), 'Fixed service checkbox did not return to the public form.' );
			}
		);
	},
	'existing services retain their fixed calculation semantics' => static function () {
		offitravel_test_assert_same(
			36.0,
			offitravel_addon_sum( array( 12027 ), array( 'offitravel_room_count' => 3, 'ovabrw_adults' => 6, 'ovabrw_quantity' => 1 ) ),
			'KIT must remain 12 euros per room.'
		);
		offitravel_test_assert_same(
			40.0,
			offitravel_addon_sum( array( 12028 ), array( 'ovabrw_adults' => 2, 'ovabrw_quantity' => 1 ) ),
			'Platea must retain its current per-person calculation.'
		);
		offitravel_test_assert_same(
			65.0,
			offitravel_addon_sum( array( 12717 ), array( 'ovabrw_adults' => 2, 'ovabrw_quantity' => 1 ) ),
			'Servicio 01 must retain its current per-person calculation.'
		);
	},
	'existing service records retain their baseline metadata' => static function () {
		$expected = array(
			12027 => array( '12', 'room', array( 10628, 10618 ) ),
			12028 => array( '20', 'person', array( 11789, 10628, 10618 ) ),
			12717 => array( '32.50', 'person', array( 10618 ) ),
		);

		foreach ( $expected as $post_id => $values ) {
			offitravel_test_assert_same( $values[0], get_post_meta( $post_id, '_offitravel_addon_price', true ), 'Price changed for service ' . $post_id );
			offitravel_test_assert_same( $values[1], get_post_meta( $post_id, '_offitravel_addon_billing', true ), 'Billing changed for service ' . $post_id );
			offitravel_test_assert_same( $values[2], get_post_meta( $post_id, '_offitravel_addon_product_ids', true ), 'Assignments changed for service ' . $post_id );
		}
	},
	'saving an untouched legacy service does not migrate or alter its configuration' => static function () {
		offitravel_test_require_function( 'offitravel_addon_save' );
		$post_id = 12027;
		$before  = array(
			'price'       => get_post_meta( $post_id, '_offitravel_addon_price', true ),
			'billing'     => get_post_meta( $post_id, '_offitravel_addon_billing', true ),
			'product_ids' => get_post_meta( $post_id, '_offitravel_addon_product_ids', true ),
		);
		$updates = array();
		$deletes = array();

		$update_filter = static function ( $check, $object_id, $meta_key, $meta_value ) use ( $post_id, &$updates ) {
			if ( $post_id !== (int) $object_id ) {
				return $check;
			}
			$updates[ $meta_key ] = $meta_value;
			return true;
		};
		$delete_filter = static function ( $check, $object_id, $meta_key ) use ( $post_id, &$deletes ) {
			if ( $post_id !== (int) $object_id ) {
				return $check;
			}
			$deletes[] = $meta_key;
			return true;
		};

		$previous_user = get_current_user_id();
		$previous_post = $_POST;
		wp_set_current_user( 1 );
		add_filter( 'update_post_metadata', $update_filter, 10, 5 );
		add_filter( 'delete_post_metadata', $delete_filter, 10, 5 );

		$_POST = array(
			'post_type'                         => 'offitravel_prd_addon',
			'offitravel_addon_nonce'            => wp_create_nonce( 'offitravel_addon_save' ),
			'_offitravel_addon_public_label'    => '',
			'_offitravel_addon_price_model'     => 'fixed',
			'_offitravel_addon_price'           => $before['price'],
			'_offitravel_addon_billing'         => $before['billing'],
			'_offitravel_addon_product_ids'     => $before['product_ids'],
		);

		offitravel_addon_save( $post_id );

		remove_filter( 'update_post_metadata', $update_filter, 10 );
		remove_filter( 'delete_post_metadata', $delete_filter, 10 );
		$_POST = $previous_post;
		wp_set_current_user( $previous_user );

		offitravel_test_assert_same( $before['price'], $updates['_offitravel_addon_price'], 'Untouched legacy price was not preserved.' );
		offitravel_test_assert_same( $before['billing'], $updates['_offitravel_addon_billing'], 'Untouched legacy billing was not preserved.' );
		offitravel_test_assert_same( $before['product_ids'], $updates['_offitravel_addon_product_ids'], 'Untouched legacy assignments were not preserved.' );
		offitravel_test_assert_same( false, isset( $updates['_offitravel_addon_price_model'] ), 'Untouched legacy service was migrated to an explicit model.' );
		offitravel_test_assert_same( false, isset( $updates['_offitravel_addon_public_label'] ), 'Untouched legacy service acquired a public label.' );
		offitravel_test_assert_same( $before['price'], get_post_meta( $post_id, '_offitravel_addon_price', true ), 'The regression test modified the database.' );
	},
);

$failures = 0;
foreach ( $tests as $name => $test ) {
	try {
		$test();
		echo '[PASS] ' . $name . PHP_EOL;
	} catch ( Throwable $error ) {
		++$failures;
		echo '[FAIL] ' . $name . PHP_EOL . $error->getMessage() . PHP_EOL;
	}
}

printf( "%d test(s), %d failure(s).\n", count( $tests ), $failures );
exit( $failures > 0 ? 1 : 0 );
