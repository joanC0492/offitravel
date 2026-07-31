<?php
/**
 * Database and hook regressions for the Checkpoint 5 Rin activation.
 *
 * This test is read-only. It confirms that existing services and cruise
 * products remain untouched and that public cabin integration is scoped to Rin.
 *
 * Run with: php tests/offitravel-cabin-supplements-regression-test.php
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
function offitravel_cabin_regression_same( $expected, $actual, $message ) {
	if ( $expected !== $actual ) {
		throw new RuntimeException( $message . "\nExpected: " . var_export( $expected, true ) . "\nActual: " . var_export( $actual, true ) );
	}
}

/**
 * Return names of callbacks registered on a hook with the cabin prefix.
 *
 * @param string $hook_name WordPress hook name.
 * @return string[] Callback names.
 */
function offitravel_cabin_regression_callbacks( $hook_name ) {
	global $wp_filter;
	if ( empty( $wp_filter[ $hook_name ] ) || ! isset( $wp_filter[ $hook_name ]->callbacks ) ) {
		return array();
	}
	$found = array();
	foreach ( $wp_filter[ $hook_name ]->callbacks as $callbacks ) {
		foreach ( $callbacks as $entry ) {
			$callback = $entry['function'];
			$name     = is_string( $callback ) ? $callback : ( is_array( $callback ) && is_string( $callback[1] ) ? $callback[1] : '' );
			if ( 0 === strpos( $name, 'offitravel_cabin_' ) ) {
				$found[] = $name;
			}
		}
	}
	return array_values( array_unique( $found ) );
}

$musicals = array( 10618, 10628, 11512, 11521, 11528, 11537, 11539, 11545 );
$expected_services = array(
	12027 => array(
		'title'                                    => 'Kit romántico sorpresa',
		'_offitravel_addon_public_label'           => 'KIT romántico',
		'_offitravel_addon_price'                  => '12',
		'_offitravel_addon_billing'                => 'room',
		'_offitravel_addon_product_ids'            => $musicals,
		'_offitravel_addon_manual_room_product_ids'=> array( 11528, 11537, 11539, 11545 ),
	),
	12028 => array(
		'title'                         => 'Entradas en platea A',
		'_offitravel_addon_price'       => '20',
		'_offitravel_addon_billing'     => 'person',
		'_offitravel_addon_product_ids' => array( 11789, 10628, 10618 ),
	),
	12717 => array(
		'title'                         => 'Servicio 01',
		'_offitravel_addon_price'       => '32.50',
		'_offitravel_addon_billing'     => 'person',
		'_offitravel_addon_product_ids' => array( 10618 ),
	),
	12718 => array(
		'title'                         => 'Seguro de viaje — Asturias y Ribeira Sacra',
		'_offitravel_addon_price_model' => 'traveler_age',
		'_offitravel_addon_public_label'=> 'Seguro de viaje',
		'_offitravel_addon_product_ids' => array( 9475, 9487 ),
		'_offitravel_addon_age_rules'   => array(
			array( 'min_age' => 0, 'max_age' => 69, 'price' => '32.50' ),
			array( 'min_age' => 70, 'max_age' => null, 'price' => '45.50' ),
		),
	),
	12719 => array(
		'title'                         => 'Seguro de viaje — A Coruña',
		'_offitravel_addon_price_model' => 'traveler_age',
		'_offitravel_addon_public_label'=> 'Seguro de viaje',
		'_offitravel_addon_product_ids' => array( 9502 ),
		'_offitravel_addon_age_rules'   => array(
			array( 'min_age' => 0, 'max_age' => 69, 'price' => '17.50' ),
			array( 'min_age' => 70, 'max_age' => null, 'price' => '24.50' ),
		),
	),
	12732 => array(
		'title'                         => 'Seguro de anulación — Musicales',
		'_offitravel_addon_public_label'=> 'Seguro de anulación',
		'_offitravel_addon_price'       => '6',
		'_offitravel_addon_billing'     => 'booking',
		'_offitravel_addon_booking_once'=> 'yes',
		'_offitravel_addon_product_ids' => $musicals,
	),
);

$tests = array(
	'Rin is the only product with cabin metadata and exact approved options' => static function () {
		global $wpdb;
		$pattern = '%' . $wpdb->esc_like( 'offitravel_cabin' ) . '%';
		$products = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key LIKE %s ORDER BY post_id", $pattern ) );
		offitravel_cabin_regression_same( array( '11280' ), $products, 'Cabin metadata is not isolated to Rin.' );
		offitravel_cabin_regression_same(
			array(
				array( 'id' => 'sin-suplemento', 'label' => 'Sin suplemento', 'price_per_person' => '0.00' ),
				array( 'id' => 'puente-intermedio', 'label' => 'Puente intermedio', 'price_per_person' => '135.00' ),
				array( 'id' => 'puente-superior', 'label' => 'Puente superior', 'price_per_person' => '200.00' ),
			),
			get_post_meta( 11280, OFFITRAVEL_CABIN_META_OPTIONS, true ),
			'Rin options differ from the approved configuration.'
		);
		offitravel_cabin_regression_same( 'yes', get_post_meta( 11280, OFFITRAVEL_CABIN_META_ENABLED, true ), 'Rin cabin options are not enabled.' );
		offitravel_cabin_regression_same( '0', (string) get_post_meta( 11280, '_offitravel_ovabrw_room_single_supplement_eur', true ), 'Rin single supplement fallback is not disabled.' );
	},
	'Danube remains without cabin metadata or activation' => static function () {
		global $wpdb;
		$pattern = '%' . $wpdb->esc_like( 'offitravel_cabin' ) . '%';
		$count = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key LIKE %s", 11259, $pattern ) );
		offitravel_cabin_regression_same( 0, $count, 'Danube received cabin metadata.' );
		offitravel_cabin_regression_same( '', get_post_meta( 11259, '_offitravel_ovabrw_room_single_supplement_eur', true ), 'Danube single supplement configuration changed.' );
	},
	'Rin booking limits package dates and base price remain unchanged' => static function () {
		offitravel_cabin_regression_same( 'yes', get_post_meta( 11280, '_offitravel_ovabrw_room_mode_enabled', true ), 'Rin room mode changed.' );
		offitravel_cabin_regression_same( '1', get_post_meta( 11280, '_offitravel_ovabrw_room_max_rooms', true ), 'Rin maximum rooms changed.' );
		offitravel_cabin_regression_same( '5', get_post_meta( 11280, '_offitravel_ovabrw_room_max_per_room', true ), 'Rin maximum occupants changed.' );
		offitravel_cabin_regression_same( '2', get_post_meta( 11280, 'ovabrw_adults_min', true ), 'Rin minimum adults changed.' );
		offitravel_cabin_regression_same( 'pack_mercadillo_rin', get_post_meta( 11280, 'ovabrw_product_custom_checkout_field', true ), 'Rin package field changed.' );
		offitravel_cabin_regression_same( '1298', get_post_meta( 11280, '_price', true ), 'Rin base price changed.' );
		offitravel_cabin_regression_same( array( '30-11-2026', '08-12-2026' ), get_post_meta( 11280, '_offitravel_ovabrw_available_startdate', true ), 'Rin dates changed.' );
	},
	'existing circuit and musical services retain approved configuration' => static function () use ( $expected_services ) {
		foreach ( $expected_services as $service_id => $expected ) {
			offitravel_cabin_regression_same( $expected['title'], get_the_title( $service_id ), "Service {$service_id} title changed." );
			unset( $expected['title'] );
			foreach ( $expected as $meta_key => $meta_value ) {
				offitravel_cabin_regression_same( $meta_value, get_post_meta( $service_id, $meta_key, true ), "Service {$service_id} metadata {$meta_key} changed." );
			}
		}
	},
	'public cabin hooks use the approved existing WooCommerce and OVA boundaries' => static function () {
		$expected_hooks = array(
			'tripgo_booking_form'                        => array( 'offitravel_cabin_booking_markup' ),
			'wp_enqueue_scripts'                         => array( 'offitravel_cabin_enqueue_state', 'offitravel_cabin_enqueue_front' ),
			'ovabrw_get_price_by_guests'                 => array( 'offitravel_cabin_line_total' ),
			'woocommerce_add_to_cart_validation'         => array( 'offitravel_cabin_validate_cart' ),
			'woocommerce_add_cart_item_data'             => array( 'offitravel_cabin_add_cart_item_data' ),
			'woocommerce_get_cart_item_from_session'     => array( 'offitravel_cabin_restore_cart_item' ),
			'woocommerce_get_item_data'                  => array( 'offitravel_cabin_cart_display' ),
			'woocommerce_checkout_create_order_line_item'=> array( 'offitravel_cabin_order_item' ),
			'woocommerce_hidden_order_itemmeta'           => array( 'offitravel_cabin_hidden_order_itemmeta' ),
		);
		foreach ( $expected_hooks as $hook_name => $callbacks ) {
			offitravel_cabin_regression_same( $callbacks, offitravel_cabin_regression_callbacks( $hook_name ), "Cabin callbacks differ on {$hook_name}." );
		}
		offitravel_cabin_regression_same( array(), offitravel_cabin_regression_callbacks( 'wp_ajax_offitravel_cabin' ), 'A parallel cabin AJAX endpoint was registered.' );
		offitravel_cabin_regression_same( array(), offitravel_cabin_regression_callbacks( 'wp_ajax_nopriv_offitravel_cabin' ), 'A public parallel cabin AJAX endpoint was registered.' );
	},
	'administrative cabin hooks remain isolated from provider save handlers' => static function () {
		offitravel_cabin_regression_same( array( 'offitravel_cabin_add_product_metabox' ), offitravel_cabin_regression_callbacks( 'add_meta_boxes_product' ), 'Unexpected product metabox callbacks.' );
		offitravel_cabin_regression_same( array( 'offitravel_cabin_save_product_options' ), offitravel_cabin_regression_callbacks( 'woocommerce_process_product_meta' ), 'Unexpected product-save callbacks.' );
		offitravel_cabin_regression_same( array( 'offitravel_cabin_enqueue_admin_script' ), offitravel_cabin_regression_callbacks( 'admin_enqueue_scripts' ), 'Unexpected cabin admin script callbacks.' );
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
