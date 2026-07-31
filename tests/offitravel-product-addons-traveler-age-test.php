<?php
/**
 * Integration tests for traveler-age product add-ons.
 *
 * Run with: php tests/offitravel-product-addons-traveler-age-test.php
 *
 * @package Offitravel
 */

require dirname( __DIR__ ) . '/wp-load.php';

/**
 * Assert strict equality for traveler-age integration tests.
 *
 * @param mixed  $expected Expected value.
 * @param mixed  $actual   Actual value.
 * @param string $message  Failure context.
 * @return void
 * @throws RuntimeException When values differ.
 */
function offitravel_age_test_assert_same( $expected, $actual, $message ) {
	if ( $expected !== $actual ) {
		throw new RuntimeException(
			$message . "\nExpected: " . var_export( $expected, true ) . "\nActual: " . var_export( $actual, true )
		);
	}
}

/**
 * Assert a WordPress error code.
 *
 * @param string $expected Expected error code.
 * @param mixed  $actual   Actual result.
 * @param string $message  Failure context.
 * @return void
 * @throws RuntimeException When the result is not the expected error.
 */
function offitravel_age_test_assert_error( $expected, $actual, $message ) {
	$actual_code = is_wp_error( $actual ) ? $actual->get_error_code() : gettype( $actual );
	if ( $expected !== $actual_code ) {
		throw new RuntimeException( $message . "\nExpected error: {$expected}\nActual: {$actual_code}" );
	}
}

/**
 * Require a production function before exercising it.
 *
 * @param string $function_name Function name.
 * @return void
 * @throws RuntimeException When the function is unavailable.
 */
function offitravel_age_test_require_function( $function_name ) {
	if ( ! function_exists( $function_name ) ) {
		throw new RuntimeException( 'Missing production function: ' . $function_name );
	}
}

/**
 * Run a callback with a real add-on post and isolated traveler-age metadata.
 *
 * The metadata read boundary is intercepted; no database records are written.
 *
 * @param callable $callback Test callback.
 * @return void
 */
function offitravel_age_test_with_asturias_fixture( $callback ) {
	$metadata = array(
		'_offitravel_addon_public_label' => 'Seguro de viaje',
		'_offitravel_addon_price_model'  => 'traveler_age',
		'_offitravel_addon_age_rules'    => array(
			array( 'min_age' => 0, 'max_age' => 69, 'price' => '32.50' ),
			array( 'min_age' => 70, 'max_age' => null, 'price' => '45.50' ),
		),
		'_offitravel_addon_product_ids'  => array( 9475, 9487 ),
	);
	$filter   = static function ( $value, $object_id, $meta_key ) use ( $metadata ) {
		if ( 12027 === (int) $object_id && array_key_exists( $meta_key, $metadata ) ) {
			$fixture = $metadata[ $meta_key ];
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

/**
 * Build the room-mode context used by the affected circuit fixtures.
 *
 * @param int[] $people Occupants in each room.
 * @return array<string,mixed>
 */
function offitravel_age_test_room_context( array $people ) {
	return array(
		'offitravel_room_count'  => count( $people ),
		'offitravel_room_people' => $people,
		'ovabrw_adults'          => array_sum( $people ),
	);
}

$tests = array(
	'one insured traveler and one uninsured traveler charges only the selected traveler' => static function () {
		offitravel_age_test_require_function( 'offitravel_addon_calculate_traveler_age' );
		offitravel_age_test_with_asturias_fixture(
			static function () {
				$actual = offitravel_addon_calculate_traveler_age(
					9487,
					array(
						12027 => array(
							1 => array(
								1 => array( 'selected' => '1', 'age' => '35' ),
								2 => array( 'selected' => '0', 'age' => '72' ),
							),
						),
					),
					offitravel_age_test_room_context( array( 2 ) )
				);

				offitravel_age_test_assert_same( '32.50', $actual['total'], 'An uninsured traveler changed the total.' );
				offitravel_age_test_assert_same( 1, count( $actual['services'][12027]['travelers'] ), 'The uninsured traveler was persisted.' );
				offitravel_age_test_assert_same( 35, $actual['services'][12027]['travelers'][0]['age'], 'The insured age was not normalized.' );
			}
		);
	},
	'two insured travelers use their individual age brackets' => static function () {
		offitravel_age_test_with_asturias_fixture(
			static function () {
				$actual = offitravel_addon_calculate_traveler_age(
					9487,
					array(
						12027 => array(
							1 => array(
								1 => array( 'selected' => '1', 'age' => '35' ),
								2 => array( 'selected' => '1', 'age' => '72' ),
							),
						),
					),
					offitravel_age_test_room_context( array( 2 ) )
				);

				offitravel_age_test_assert_same( '78.00', $actual['total'], 'Individual brackets were not added correctly.' );
				offitravel_age_test_assert_same( '32.50', $actual['services'][12027]['travelers'][0]['rate'], 'Age 35 used the wrong rate.' );
				offitravel_age_test_assert_same( '45.50', $actual['services'][12027]['travelers'][1]['rate'], 'Age 72 used the wrong rate.' );
			}
		);
	},
	'ages 69 and 70 resolve on opposite sides of the approved boundary' => static function () {
		offitravel_age_test_with_asturias_fixture(
			static function () {
				$actual = offitravel_addon_calculate_traveler_age(
					9487,
					array(
						12027 => array(
							1 => array(
								1 => array( 'selected' => '1', 'age' => '69' ),
								2 => array( 'selected' => '1', 'age' => '70' ),
							),
						),
					),
					offitravel_age_test_room_context( array( 2 ) )
				);

				offitravel_age_test_assert_same( '32.50', $actual['services'][12027]['travelers'][0]['rate'], 'Age 69 did not use the lower bracket.' );
				offitravel_age_test_assert_same( '45.50', $actual['services'][12027]['travelers'][1]['rate'], 'Age 70 did not use the upper bracket.' );
			}
		);
	},
	'blank negative and decimal ages are rejected only when selected' => static function () {
		offitravel_age_test_with_asturias_fixture(
			static function () {
				foreach ( array( '', '-1', '35.5' ) as $age ) {
					$actual = offitravel_addon_calculate_traveler_age(
						9487,
						array( 12027 => array( 1 => array( 1 => array( 'selected' => '1', 'age' => $age ) ) ) ),
						offitravel_age_test_room_context( array( 1 ) )
					);
					offitravel_age_test_assert_error( 'offitravel_addon_invalid_traveler_age', $actual, 'Selected invalid age was accepted: ' . var_export( $age, true ) );
				}

				$uninsured = offitravel_addon_calculate_traveler_age(
					9487,
					array( 12027 => array( 1 => array( 1 => array( 'selected' => '0', 'age' => '35.5' ) ) ) ),
					offitravel_age_test_room_context( array( 1 ) )
				);
				offitravel_age_test_assert_same( '0.00', $uninsured['total'], 'An uninsured age was validated or charged.' );
			}
		);
	},
	'multiple rooms preserve room and traveler positions in the snapshot' => static function () {
		offitravel_age_test_with_asturias_fixture(
			static function () {
				$actual = offitravel_addon_calculate_traveler_age(
					9475,
					array(
						12027 => array(
							1 => array( 2 => array( 'selected' => '1', 'age' => '35' ) ),
							2 => array( 1 => array( 'selected' => '1', 'age' => '72' ) ),
						),
					),
					offitravel_age_test_room_context( array( 2, 1 ) )
				);
				$travelers = $actual['services'][12027]['travelers'];

				offitravel_age_test_assert_same( array( 1, 2, 2, 1 ), array( $travelers[0]['room'], $travelers[0]['position'], $travelers[1]['room'], $travelers[1]['position'] ), 'Room positions were lost.' );
				offitravel_age_test_assert_same( array( 2, 3 ), array( $travelers[0]['traveler'], $travelers[1]['traveler'] ), 'Global traveler positions were not derived from occupancy.' );
			}
		);
	},
	'manipulated service IDs and posted prices cannot alter the configured tariff' => static function () {
		offitravel_age_test_with_asturias_fixture(
			static function () {
				$forged_id = offitravel_addon_calculate_traveler_age(
					9487,
					array( 12028 => array( 1 => array( 1 => array( 'selected' => '1', 'age' => '35' ) ) ) ),
					offitravel_age_test_room_context( array( 1 ) )
				);
				offitravel_age_test_assert_error( 'offitravel_addon_invalid_traveler_age_service', $forged_id, 'A service assigned to another product was accepted.' );

				$forged_price = offitravel_addon_calculate_traveler_age(
					9487,
					array( 12027 => array( 1 => array( 1 => array( 'selected' => '1', 'age' => '35', 'price' => '0.01', 'subtotal' => '0.01' ) ) ) ),
					offitravel_age_test_room_context( array( 1 ) )
				);
				offitravel_age_test_assert_same( '32.50', $forged_price['total'], 'A posted price changed the server-side tariff.' );
			}
		);
	},
	'public markup renders one traveler-age service without a global insurance checkbox' => static function () {
		offitravel_age_test_with_asturias_fixture(
			static function () {
				ob_start();
				offitravel_addon_booking_markup( array( 'id' => 9487 ) );
				$html = (string) ob_get_clean();

				offitravel_age_test_assert_same( true, false !== strpos( $html, 'data-offitravel-age-service="12027"' ), 'The assigned age service was not rendered.' );
				offitravel_age_test_assert_same( true, false !== strpos( $html, 'Seguro de viaje' ), 'The public label was not rendered.' );
				offitravel_age_test_assert_same( true, false !== strpos( $html, 'data-offitravel-traveler-rows' ), 'The traveler row host is missing.' );
				offitravel_age_test_assert_same( false, false !== strpos( $html, 'name="offitravel_addons[]" value="12027"' ), 'Age pricing leaked into the fixed global checkbox.' );
			}
		);
	},
	'public traveler-age tariff legend uses decoded currency text without WooCommerce markup' => static function () {
		offitravel_age_test_with_asturias_fixture(
			static function () {
				ob_start();
				offitravel_addon_booking_markup( array( 'id' => 9487 ) );
				$html = (string) ob_get_clean();
				$expected = '<p class="offitravel-prd-addon-age-rules">'
					. '<span class="offitravel-prd-addon-age-rule">De 0 a 69 años: 32,50 €</span>'
					. '<span class="offitravel-prd-addon-age-rule">Desde 70 años: 45,50 €</span>'
					. '</p>';

				offitravel_age_test_assert_same( true, false !== strpos( $html, $expected ), 'The public tariff legend does not contain the exact decoded age brackets.' );
				offitravel_age_test_assert_same( false, false !== strpos( $html, '&nbsp;' ), 'The public tariff legend contains a non-breaking-space entity.' );
				offitravel_age_test_assert_same( false, false !== strpos( $html, '&amp;nbsp;' ), 'The public tariff legend contains a double-escaped non-breaking-space entity.' );
				offitravel_age_test_assert_same( false, false !== strpos( $html, '&euro;' ), 'The public tariff legend contains an encoded currency entity.' );
				offitravel_age_test_assert_same( false, false !== strpos( $html, '<span class="woocommerce-Price' ), 'The public tariff legend contains WooCommerce price markup.' );
				offitravel_age_test_assert_same( 2, substr_count( $expected, 'class="offitravel-prd-addon-age-rule"' ), 'The expected public legend must define one row per configured tariff.' );
			}
		);
	},
	'cart data stores only the server-calculated structured snapshot' => static function () {
		offitravel_age_test_with_asturias_fixture(
			static function () {
				$previous_post = $_POST;
				$_POST         = array(
					'offitravel_room_count'  => '1',
					'offitravel_room_people' => array( '2' ),
					'ovabrw_adults'           => '2',
					'offitravel_age_addons'  => array(
						12027 => array(
							1 => array(
								1 => array( 'selected' => '1', 'age' => '35', 'price' => '0.01' ),
								2 => array( 'selected' => '1', 'age' => '72', 'subtotal' => '0.01' ),
							),
						),
					),
				);
				try {
					$cart_data = offitravel_addon_cart_data( array(), 9487 );
				} finally {
					$_POST = $previous_post;
				}

				offitravel_age_test_assert_same( '78.00', $cart_data['offitravel_traveler_age']['total'], 'Cart data trusted a submitted price or omitted the snapshot.' );
				offitravel_age_test_assert_same( false, isset( $cart_data['offitravel_traveler_age']['services'][12027]['travelers'][0]['price'] ), 'Untrusted request fields leaked into the snapshot.' );
			}
		);
	},
	'repeated line-total calculations are idempotent and do not mutate the snapshot' => static function () {
		offitravel_age_test_with_asturias_fixture(
			static function () {
				$snapshot = offitravel_addon_calculate_traveler_age(
					9487,
					array( 12027 => array( 1 => array(
						1 => array( 'selected' => '1', 'age' => '35' ),
						2 => array( 'selected' => '1', 'age' => '72' ),
					) ) ),
					offitravel_age_test_room_context( array( 2 ) )
				);
				$before    = $snapshot;
				$cart_item = array(
					'product_id'                    => 9487,
					'offitravel_traveler_age'       => $snapshot,
					'offitravel_room_count'         => 1,
					'offitravel_room_people'        => array( 2 ),
					'ovabrw_adults'                 => 2,
				);

				$first  = offitravel_addon_line_total( 100.0, 9487, '', '', $cart_item );
				$second = offitravel_addon_line_total( 100.0, 9487, '', '', $cart_item );
				offitravel_age_test_assert_same( 178.0, $first, 'The snapshot total was not added exactly once.' );
				offitravel_age_test_assert_same( $first, $second, 'Repeated calculation accumulated the supplement.' );
				offitravel_age_test_assert_same( $before, $snapshot, 'Calculation mutated the cart snapshot.' );
			}
		);
	},
	'session restoration recalculates rates and totals from the stored rules snapshot' => static function () {
		offitravel_age_test_require_function( 'offitravel_addon_restore_cart_item' );
		offitravel_age_test_with_asturias_fixture(
			static function () {
				$snapshot = offitravel_addon_calculate_traveler_age(
					9487,
					array( 12027 => array( 1 => array( 1 => array( 'selected' => '1', 'age' => '70' ) ) ) ),
					offitravel_age_test_room_context( array( 1 ) )
				);
				$snapshot['total'] = '0.01';
				$snapshot['services'][12027]['total'] = '0.01';
				$snapshot['services'][12027]['travelers'][0]['rate'] = '0.01';

				$restored = offitravel_addon_restore_cart_item(
					array( 'product_id' => 9487 ),
					array( 'product_id' => 9487, 'offitravel_traveler_age' => $snapshot )
				);
				offitravel_age_test_assert_same( '45.50', $restored['offitravel_traveler_age']['total'], 'Session restoration trusted a stored derived total.' );
				offitravel_age_test_assert_same( '45.50', $restored['offitravel_traveler_age']['services'][12027]['travelers'][0]['rate'], 'Session restoration trusted a stored derived rate.' );
			}
		);
	},
	'cart checkout order and email-compatible metadata expose the traveler breakdown and snapshot' => static function () {
		offitravel_age_test_with_asturias_fixture(
			static function () {
				$snapshot = offitravel_addon_calculate_traveler_age(
					9487,
					array( 12027 => array( 1 => array( 1 => array( 'selected' => '1', 'age' => '35' ) ) ) ),
					offitravel_age_test_room_context( array( 1 ) )
				);
				$product   = wc_get_product( 9487 );
				$values    = array(
					'product_id'              => 9487,
					'data'                    => $product,
					'offitravel_traveler_age' => $snapshot,
				);
				$display   = offitravel_addon_cart_display( array(), $values );
				offitravel_age_test_assert_same( 'Seguro de viaje', $display[0]['key'], 'Cart/checkout did not use the public label.' );
				offitravel_age_test_assert_same( true, false !== strpos( wp_strip_all_tags( $display[0]['value'] ), 'Viajero 1' ), 'Cart/checkout omitted the traveler breakdown.' );

				$item = new WC_Order_Item_Product();
				offitravel_addon_order_item( $item, 'test-key', $values );
				$stored = $item->get_meta( '_offitravel_traveler_age_snapshot', true );
				offitravel_age_test_assert_same( '32.50', $stored['total'], 'The order did not retain the structured pricing snapshot.' );
				offitravel_age_test_assert_same( true, false !== strpos( (string) $item->get_meta( 'Seguro de viaje', true ), 'Viajero 1' ), 'Visible order/email metadata omitted the traveler breakdown.' );
			}
		);
	},
	'cart checkout and order render decoded monetary text with readable separation' => static function () {
		offitravel_age_test_with_asturias_fixture(
			static function () {
				$snapshot = offitravel_addon_calculate_traveler_age(
					9487,
					array(
						12027 => array(
							1 => array(
								1 => array( 'selected' => '1', 'age' => '35' ),
								2 => array( 'selected' => '1', 'age' => '72' ),
							),
						),
					),
					offitravel_age_test_room_context( array( 2 ) )
				);
				$values = array(
					'product_id'              => 9487,
					'data'                    => wc_get_product( 9487 ),
					'offitravel_traveler_age' => $snapshot,
				);
				$display = offitravel_addon_cart_display( array(), $values );
				$expected_html = 'Viajero 1 (Habitación 1): 35 años — 32,50 €<br>'
					. 'Viajero 2 (Habitación 1): 72 años — 45,50 €<br>'
					. 'Total: 78,00 €';
				offitravel_age_test_assert_same( $expected_html, $display[0]['value'], 'Cart/checkout monetary text contains encoded entities or unreadable markup.' );
				offitravel_age_test_assert_same( false, false !== strpos( $display[0]['value'], '&nbsp;' ), 'Cart/checkout contains a visible non-breaking-space entity.' );
				offitravel_age_test_assert_same( false, false !== strpos( $display[0]['value'], '&amp;nbsp;' ), 'Cart/checkout contains a double-escaped entity.' );
				offitravel_age_test_assert_same( false, false !== strpos( $display[0]['value'], '&euro;' ), 'Cart/checkout contains an encoded currency entity.' );
				offitravel_age_test_assert_same( $expected_html, wp_kses( $display[0]['value'], array( 'br' => array() ) ), 'Cart/checkout output contains unsafe HTML.' );

				$item = new WC_Order_Item_Product();
				offitravel_addon_order_item( $item, 'format-test', $values );
				$expected_text = str_replace( '<br>', "\n", $expected_html );
				offitravel_age_test_assert_same( $expected_text, (string) $item->get_meta( 'Seguro de viaje', true ), 'Order/email metadata does not retain readable line separation.' );
			}
		);
	},
	'cart validation blocks malformed insured ages and accepts an unselected traveler without age' => static function () {
		offitravel_age_test_require_function( 'offitravel_addon_validate_traveler_age_payload' );
		offitravel_age_test_with_asturias_fixture(
			static function () {
				$invalid = offitravel_addon_validate_traveler_age_payload(
					9487,
					array( 12027 => array( 1 => array( 1 => array( 'selected' => '1', 'age' => '' ) ) ) ),
					offitravel_age_test_room_context( array( 1 ) )
				);
				offitravel_age_test_assert_error( 'offitravel_addon_invalid_traveler_age', $invalid, 'Cart validation accepted a selected traveler without age.' );

				$valid = offitravel_addon_validate_traveler_age_payload(
					9487,
					array( 12027 => array( 1 => array( 1 => array( 'selected' => '0', 'age' => '' ) ) ) ),
					offitravel_age_test_room_context( array( 1 ) )
				);
				offitravel_age_test_assert_same( true, $valid, 'Cart validation required age from an uninsured traveler.' );
			}
		);
	},
	'configured checkpoint services retain their exact products rules and public labels' => static function () {
		$expected = array(
			12718 => array(
				'products' => array( 9475, 9487 ),
				'rules'    => array(
					array( 'min_age' => 0, 'max_age' => 69, 'price' => '32.50' ),
					array( 'min_age' => 70, 'max_age' => null, 'price' => '45.50' ),
				),
			),
			12719 => array(
				'products' => array( 9502 ),
				'rules'    => array(
					array( 'min_age' => 0, 'max_age' => 69, 'price' => '17.50' ),
					array( 'min_age' => 70, 'max_age' => null, 'price' => '24.50' ),
				),
			),
		);
		foreach ( $expected as $service_id => $config ) {
			offitravel_age_test_assert_same( 'publish', get_post_status( $service_id ), 'Insurance service is not published: ' . $service_id );
			offitravel_age_test_assert_same( 'Seguro de viaje', get_post_meta( $service_id, '_offitravel_addon_public_label', true ), 'Public label changed: ' . $service_id );
			offitravel_age_test_assert_same( 'traveler_age', get_post_meta( $service_id, '_offitravel_addon_price_model', true ), 'Price model changed: ' . $service_id );
			offitravel_age_test_assert_same( $config['products'], get_post_meta( $service_id, '_offitravel_addon_product_ids', true ), 'Product assignment changed: ' . $service_id );
			offitravel_age_test_assert_same( $config['rules'], get_post_meta( $service_id, '_offitravel_addon_age_rules', true ), 'Age rules changed: ' . $service_id );
			offitravel_age_test_assert_same( '', get_post_meta( $service_id, '_offitravel_addon_price', true ), 'Traveler-age service unexpectedly has a fixed price: ' . $service_id );
			offitravel_age_test_assert_same( '', get_post_meta( $service_id, '_offitravel_addon_billing', true ), 'Traveler-age service unexpectedly has fixed billing: ' . $service_id );
		}
	},
	'A Coruna uses its own configured rates for both approved age brackets' => static function () {
		$actual = offitravel_addon_calculate_traveler_age(
			9502,
			array( 12719 => array( 1 => array(
				1 => array( 'selected' => '1', 'age' => '69' ),
				2 => array( 'selected' => '1', 'age' => '70' ),
			) ) ),
			offitravel_age_test_room_context( array( 2 ) )
		);
		offitravel_age_test_assert_same( '42.00', $actual['total'], 'A Coruña did not use 17.50 + 24.50.' );
		offitravel_age_test_assert_same( '17.50', $actual['services'][12719]['travelers'][0]['rate'], 'A Coruña age 69 used the wrong rate.' );
		offitravel_age_test_assert_same( '24.50', $actual['services'][12719]['travelers'][1]['rate'], 'A Coruña age 70 used the wrong rate.' );
	},
	'products outside the three circuits do not receive traveler-age public controls' => static function () {
		offitravel_age_test_assert_same( array(), offitravel_addon_age_posts_for_product( 10618 ), 'A non-circuit product received a traveler-age service.' );
		ob_start();
		offitravel_addon_booking_markup( array( 'id' => 10618 ) );
		$html = (string) ob_get_clean();
		offitravel_age_test_assert_same( false, false !== strpos( $html, 'data-offitravel-age-service' ), 'A non-circuit form rendered traveler-age controls.' );
	},
	'cart validation session restoration and hidden order snapshot hooks are registered' => static function () {
		offitravel_age_test_assert_same( 100, has_filter( 'woocommerce_add_to_cart_validation', 'offitravel_addon_validate_cart' ), 'Server cart validation hook is missing.' );
		offitravel_age_test_assert_same( 20, has_filter( 'woocommerce_get_cart_item_from_session', 'offitravel_addon_restore_cart_item' ), 'Cart session restoration hook is missing.' );
		offitravel_age_test_assert_same( 10, has_filter( 'woocommerce_hidden_order_itemmeta', 'offitravel_addon_hidden_order_itemmeta' ), 'Hidden snapshot metadata hook is missing.' );
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
