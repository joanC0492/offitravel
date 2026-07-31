<?php
/**
 * Administrative tests for the reusable cabin supplement configuration.
 *
 * These tests use synthetic option data and intercept metadata writes. They do
 * not create or modify product metadata.
 *
 * Run with: php tests/offitravel-cabin-supplements-admin-test.php
 *
 * @package Offitravel
 */

require dirname( __DIR__ ) . '/wp-load.php';

if ( ! function_exists( 'offitravel_cabin_validate_admin_payload' ) ) {
	fwrite( STDERR, "[FAIL] Missing production function: offitravel_cabin_validate_admin_payload\n" );
	exit( 1 );
}

/**
 * Assert strict equality.
 *
 * @param mixed  $expected Expected value.
 * @param mixed  $actual   Actual value.
 * @param string $message  Failure context.
 * @return void
 * @throws RuntimeException When values differ.
 */
function offitravel_cabin_admin_same( $expected, $actual, $message ) {
	if ( $expected !== $actual ) {
		throw new RuntimeException( $message . "\nExpected: " . var_export( $expected, true ) . "\nActual: " . var_export( $actual, true ) );
	}
}

/**
 * Assert a WordPress error code.
 *
 * @param string $expected Expected error code.
 * @param mixed  $actual   Actual value.
 * @param string $message  Failure context.
 * @return void
 * @throws RuntimeException When the result is not the expected error.
 */
function offitravel_cabin_admin_error( $expected, $actual, $message ) {
	$actual_code = is_wp_error( $actual ) ? $actual->get_error_code() : gettype( $actual );
	if ( $expected !== $actual_code ) {
		throw new RuntimeException( $message . "\nExpected error: {$expected}\nActual: {$actual_code}" );
	}
}

$tests = array(
	'valid synthetic options normalize IDs labels and WooCommerce decimals' => static function () {
		$result = offitravel_cabin_validate_admin_payload(
			array(
				array( 'id' => ' SAMPLE-LOWER ', 'label' => ' Synthetic Lower ', 'price_per_person' => '14,37' ),
				array( 'id' => 'sample_upper', 'label' => 'Synthetic Upper', 'price_per_person' => '26.48' ),
				array( 'id' => '', 'label' => '', 'price_per_person' => '' ),
			)
		);
		offitravel_cabin_admin_same(
			array(
				array( 'id' => 'sample-lower', 'label' => 'Synthetic Lower', 'price_per_person' => '14.37' ),
				array( 'id' => 'sample_upper', 'label' => 'Synthetic Upper', 'price_per_person' => '26.48' ),
			),
			$result,
			'Synthetic options were not normalized.'
		);
	},
	'duplicate option IDs are rejected before persistence' => static function () {
		$result = offitravel_cabin_validate_admin_payload(
			array(
				array( 'id' => 'sample', 'label' => 'Synthetic One', 'price_per_person' => '14.37' ),
				array( 'id' => 'sample', 'label' => 'Synthetic Two', 'price_per_person' => '26.48' ),
			)
		);
		offitravel_cabin_admin_error( 'offitravel_cabin_duplicate_option_id', $result, 'Duplicate option IDs were accepted.' );
	},
	'partial and invalid rows are rejected' => static function () {
		$fixtures = array(
			array( 'rows' => array( array( 'id' => 'sample', 'label' => '', 'price_per_person' => '14.37' ) ), 'error' => 'offitravel_cabin_incomplete_option' ),
			array( 'rows' => array( array( 'id' => '', 'label' => 'Synthetic', 'price_per_person' => '14.37' ) ), 'error' => 'offitravel_cabin_incomplete_option' ),
			array( 'rows' => array( array( 'id' => 'sample', 'label' => 'Synthetic', 'price_per_person' => '' ) ), 'error' => 'offitravel_cabin_incomplete_option' ),
			array( 'rows' => array( array( 'id' => 'sample', 'label' => 'Synthetic', 'price_per_person' => '-1' ) ), 'error' => 'offitravel_cabin_invalid_option_price' ),
			array( 'rows' => array( array( 'id' => 'sample', 'label' => 'Synthetic', 'price_per_person' => 'abc' ) ), 'error' => 'offitravel_cabin_invalid_option_price' ),
		);
		foreach ( $fixtures as $fixture ) {
			offitravel_cabin_admin_error(
				$fixture['error'],
				offitravel_cabin_validate_admin_payload( $fixture['rows'] ),
				'Invalid option row was accepted: ' . wp_json_encode( $fixture['rows'] )
			);
		}
	},
	'label removed completely by WordPress sanitization is rejected' => static function () {
		$result = offitravel_cabin_validate_admin_payload(
			array(
				array( 'id' => 'synthetic', 'label' => '<script></script>', 'price_per_person' => '14.37' ),
			)
		);
		offitravel_cabin_admin_error( 'offitravel_cabin_invalid_option_label', $result, 'A public label that sanitizes to an empty string was accepted.' );
	},
	'empty configuration is valid and products remain disabled by default' => static function () {
		offitravel_cabin_admin_same( array(), offitravel_cabin_validate_admin_payload( array() ), 'Empty configuration was not accepted.' );
		offitravel_cabin_admin_same( false, offitravel_cabin_product_is_enabled( 10618 ), 'A product without cabin metadata became enabled.' );
	},
	'saving without interacting with the metabox performs no metadata writes' => static function () {
		$writes = array();
		$updates = static function ( $check, $object_id, $meta_key ) use ( &$writes ) {
			if ( false !== strpos( (string) $meta_key, 'offitravel_cabin' ) ) {
				$writes[] = array( 'update', (int) $object_id, (string) $meta_key );
				return true;
			}
			return $check;
		};
		$deletes = static function ( $check, $object_id, $meta_key ) use ( &$writes ) {
			if ( false !== strpos( (string) $meta_key, 'offitravel_cabin' ) ) {
				$writes[] = array( 'delete', (int) $object_id, (string) $meta_key );
				return true;
			}
			return $check;
		};
		add_filter( 'update_post_metadata', $updates, 10, 3 );
		add_filter( 'delete_post_metadata', $deletes, 10, 3 );
		$before = $_POST;
		$_POST = array();
		try {
			offitravel_cabin_save_product_options( 10618, get_post( 10618 ) );
		} finally {
			$_POST = $before;
			remove_filter( 'update_post_metadata', $updates, 10 );
			remove_filter( 'delete_post_metadata', $deletes, 10 );
		}
		offitravel_cabin_admin_same( array(), $writes, 'Untouched metabox attempted to write metadata.' );
	},
	'invalid interacted configuration is rejected before any metadata write' => static function () {
		$writes       = array();
		$updates      = static function ( $check, $object_id, $meta_key ) use ( &$writes ) {
			if ( false !== strpos( (string) $meta_key, 'offitravel_cabin' ) ) {
				$writes[] = array( 'update', (int) $object_id, (string) $meta_key );
				return true;
			}
			return $check;
		};
		$deletes      = static function ( $check, $object_id, $meta_key ) use ( &$writes ) {
			if ( false !== strpos( (string) $meta_key, 'offitravel_cabin' ) ) {
				$writes[] = array( 'delete', (int) $object_id, (string) $meta_key );
				return true;
			}
			return $check;
		};
		$previous_post = $_POST;
		$previous_user = get_current_user_id();
		add_filter( 'update_post_metadata', $updates, 10, 3 );
		add_filter( 'delete_post_metadata', $deletes, 10, 3 );
		wp_set_current_user( 1 );
		$_POST = array(
			'offitravel_cabin_metabox_interacted' => '1',
			'offitravel_cabin_nonce'               => wp_create_nonce( 'offitravel_cabin_save_options' ),
			'offitravel_cabin_options'             => array(
				array( 'id' => 'synthetic', 'label' => '', 'price_per_person' => '14.37' ),
			),
		);
		try {
			offitravel_cabin_save_product_options( 10618, get_post( 10618 ) );
		} finally {
			$_POST = $previous_post;
			wp_set_current_user( $previous_user );
			remove_filter( 'update_post_metadata', $updates, 10 );
			remove_filter( 'delete_post_metadata', $deletes, 10 );
		}
		offitravel_cabin_admin_same( array(), $writes, 'Invalid configuration attempted a partial metadata write.' );
	},
	'valid interacted configuration writes only normalized options through the isolated save path' => static function () {
		$writes = array();
		$updates = static function ( $check, $object_id, $meta_key, $meta_value ) use ( &$writes ) {
			if ( false !== strpos( (string) $meta_key, 'offitravel_cabin' ) ) {
				$writes[] = array( 'update', (int) $object_id, (string) $meta_key, $meta_value );
				return true;
			}
			return $check;
		};
		$deletes = static function ( $check, $object_id, $meta_key ) use ( &$writes ) {
			if ( false !== strpos( (string) $meta_key, 'offitravel_cabin' ) ) {
				$writes[] = array( 'delete', (int) $object_id, (string) $meta_key );
				return true;
			}
			return $check;
		};
		$previous_post = $_POST;
		$previous_user = get_current_user_id();
		add_filter( 'update_post_metadata', $updates, 10, 4 );
		add_filter( 'delete_post_metadata', $deletes, 10, 3 );
		wp_set_current_user( 1 );
		$_POST = array(
			'offitravel_cabin_metabox_interacted' => '1',
			'offitravel_cabin_nonce'               => wp_create_nonce( 'offitravel_cabin_save_options' ),
			'offitravel_cabin_options'             => array(
				array( 'id' => ' SYNTHETIC ', 'label' => ' Synthetic label ', 'price_per_person' => '14,37' ),
			),
		);
		try {
			offitravel_cabin_save_product_options( 10618, get_post( 10618 ) );
		} finally {
			$_POST = $previous_post;
			wp_set_current_user( $previous_user );
			remove_filter( 'update_post_metadata', $updates, 10 );
			remove_filter( 'delete_post_metadata', $deletes, 10 );
		}
		offitravel_cabin_admin_same(
			array(
				array(
					'update',
					10618,
					OFFITRAVEL_CABIN_META_OPTIONS,
					array( array( 'id' => 'synthetic', 'label' => 'Synthetic label', 'price_per_person' => '14.37' ) ),
				),
			),
			$writes,
			'Valid save did not write exactly the normalized option list.'
		);
	},
	'OVA product metabox renders reusable option fields without an activation control' => static function () {
		global $wp_meta_boxes;
		offitravel_cabin_add_product_metabox( get_post( 10618 ) );
		$box = isset( $wp_meta_boxes['product']['normal']['default']['offitravel-cabin-options'] )
			? $wp_meta_boxes['product']['normal']['default']['offitravel-cabin-options']
			: null;
		if ( ! is_array( $box ) || 'offitravel_cabin_render_product_metabox' !== $box['callback'] ) {
			throw new RuntimeException( 'Reusable OVA product metabox was not registered.' );
		}
		ob_start();
		offitravel_cabin_render_product_metabox( get_post( 10618 ) );
		$html = (string) ob_get_clean();
		foreach ( array( '[id]', '[label]', '[price_per_person]' ) as $field_suffix ) {
			if ( false === strpos( $html, $field_suffix ) ) {
				throw new RuntimeException( 'Administrative field is missing: ' . $field_suffix );
			}
		}
		if ( false !== strpos( $html, OFFITRAVEL_CABIN_META_ENABLED ) ) {
			throw new RuntimeException( 'Checkpoint 4 exposed an activation control.' );
		}
	},
	'admin hooks remain registered alongside the approved Rin public boundaries' => static function () {
		offitravel_cabin_admin_same( 10, has_action( 'add_meta_boxes_product', 'offitravel_cabin_add_product_metabox' ), 'Product metabox hook is missing.' );
		offitravel_cabin_admin_same( 20, has_action( 'woocommerce_process_product_meta', 'offitravel_cabin_save_product_options' ), 'Product save hook is missing.' );
		offitravel_cabin_admin_same( 1009, has_filter( 'ovabrw_get_price_by_guests', 'offitravel_cabin_line_total' ), 'Approved cabin price hook has the wrong priority.' );
		offitravel_cabin_admin_same( 101, has_filter( 'woocommerce_add_to_cart_validation', 'offitravel_cabin_validate_cart' ), 'Approved cabin validation hook has the wrong priority.' );
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
