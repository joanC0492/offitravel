<?php
/**
 * Plugin Name: Offitravel Cabin Supplements
 * Description: Product-scoped cabin option administration, calculation and booking persistence.
 * Version: 0.2.0
 *
 * @package Offitravel
 */

defined( 'ABSPATH' ) || exit;

const OFFITRAVEL_CABIN_META_OPTIONS = '_offitravel_cabin_options';
const OFFITRAVEL_CABIN_META_ENABLED = '_offitravel_cabin_options_enabled';
const OFFITRAVEL_CABIN_CART_KEY = 'offitravel_cabin_supplements';
const OFFITRAVEL_CABIN_ORDER_SNAPSHOT_META = '_offitravel_cabin_supplement_snapshot';
const OFFITRAVEL_CABIN_ORDER_TOTAL_META = '_offitravel_cabin_supplement_total';

/**
 * Format an amount as a WooCommerce decimal snapshot.
 *
 * @param mixed $amount Monetary value.
 * @return string Decimal string using the configured WooCommerce precision.
 */
function offitravel_cabin_money_snapshot( $amount ) {
	$decimals = wc_get_price_decimals();
	$decimal  = wc_format_decimal( (string) $amount, $decimals );
	return number_format( (float) $decimal, $decimals, '.', '' );
}

/**
 * Validate and normalize administrative cabin option rows.
 *
 * Empty rows are ignored. Every non-empty row must be complete, use a unique
 * identifier and contain a non-negative WooCommerce decimal price.
 *
 * @param mixed $raw_rows Untrusted option rows.
 * @return array<int,array{id:string,label:string,price_per_person:string}>|WP_Error
 */
function offitravel_cabin_validate_admin_payload( $raw_rows ) {
	if ( null === $raw_rows || '' === $raw_rows ) {
		return array();
	}
	if ( ! is_array( $raw_rows ) ) {
		return new WP_Error( 'offitravel_cabin_invalid_options', __( 'La configuración de cabinas no es válida.', 'offitravel-cabins' ) );
	}

	$options = array();
	$seen    = array();
	foreach ( $raw_rows as $raw_row ) {
		if ( ! is_array( $raw_row ) ) {
			return new WP_Error( 'offitravel_cabin_invalid_options', __( 'La configuración de cabinas no es válida.', 'offitravel-cabins' ) );
		}
		$raw_id    = isset( $raw_row['id'] ) && is_scalar( $raw_row['id'] ) ? trim( (string) $raw_row['id'] ) : '';
		$raw_label = isset( $raw_row['label'] ) && is_scalar( $raw_row['label'] ) ? trim( (string) $raw_row['label'] ) : '';
		$raw_price = isset( $raw_row['price_per_person'] ) && is_scalar( $raw_row['price_per_person'] ) ? trim( (string) $raw_row['price_per_person'] ) : '';
		if ( '' === $raw_id && '' === $raw_label && '' === $raw_price ) {
			continue;
		}
		if ( '' === $raw_id || '' === $raw_label || '' === $raw_price ) {
			return new WP_Error( 'offitravel_cabin_incomplete_option', __( 'Completa todos los campos de cada opción de cabina.', 'offitravel-cabins' ) );
		}

		$id = sanitize_key( $raw_id );
		if ( '' === $id ) {
			return new WP_Error( 'offitravel_cabin_invalid_option_id', __( 'El identificador de una opción de cabina no es válido.', 'offitravel-cabins' ) );
		}
		if ( isset( $seen[ $id ] ) ) {
			return new WP_Error( 'offitravel_cabin_duplicate_option_id', __( 'Los identificadores de opciones de cabina no pueden repetirse.', 'offitravel-cabins' ) );
		}
		$label = sanitize_text_field( $raw_label );
		if ( '' === $label ) {
			return new WP_Error( 'offitravel_cabin_invalid_option_label', __( 'La etiqueta pública de la opción de cabina no es válida.', 'offitravel-cabins' ) );
		}

		$normalized_price = str_replace( ',', '.', $raw_price );
		if ( ! preg_match( '/^\d+(?:\.\d+)?$/', $normalized_price ) ) {
			return new WP_Error( 'offitravel_cabin_invalid_option_price', __( 'El suplemento por persona debe ser un importe válido igual o superior a cero.', 'offitravel-cabins' ) );
		}
		$price = wc_format_decimal( $normalized_price, wc_get_price_decimals() );
		if ( '' === $price || ! is_numeric( $price ) || (float) $price < 0 ) {
			return new WP_Error( 'offitravel_cabin_invalid_option_price', __( 'El suplemento por persona debe ser un importe válido igual o superior a cero.', 'offitravel-cabins' ) );
		}

		$seen[ $id ] = true;
		$options[]   = array(
			'id'               => $id,
			'label'            => $label,
			'price_per_person' => offitravel_cabin_money_snapshot( $price ),
		);
	}
	return $options;
}

/**
 * Return normalized cabin options stored for a product.
 *
 * Invalid legacy or manually corrupted metadata is treated as unavailable.
 *
 * @param int $product_id Product ID.
 * @return array<int,array{id:string,label:string,price_per_person:string}>
 */
function offitravel_cabin_get_product_options( $product_id ) {
	$raw     = get_post_meta( absint( $product_id ), OFFITRAVEL_CABIN_META_OPTIONS, true );
	$options = offitravel_cabin_validate_admin_payload( $raw );
	return is_wp_error( $options ) ? array() : $options;
}

/**
 * Determine whether later checkpoints have explicitly activated a product.
 *
 * Checkpoint 4 never writes this metadata, so every existing product remains
 * disabled even if an administrator prepares option rows.
 *
 * @param int $product_id Product ID.
 * @return bool
 */
function offitravel_cabin_product_is_enabled( $product_id ) {
	return 'yes' === get_post_meta( absint( $product_id ), OFFITRAVEL_CABIN_META_ENABLED, true );
}

/**
 * Normalize trusted room occupancy supplied by a future integration layer.
 *
 * This primitive deliberately imposes no commercial maximum. Product-specific
 * room limits remain the responsibility of the existing booking mechanism.
 *
 * @param mixed $context Cart or calculation context.
 * @return array{room_count:int,people:array<int,int>}|WP_Error
 */
function offitravel_cabin_normalize_occupancy( $context ) {
	if ( ! is_array( $context ) || ! isset( $context['offitravel_room_count'], $context['offitravel_room_people'] ) || ! is_array( $context['offitravel_room_people'] ) ) {
		return new WP_Error( 'offitravel_cabin_invalid_occupancy', __( 'No se pudo determinar la ocupación de las cabinas.', 'offitravel-cabins' ) );
	}
	$raw_count = $context['offitravel_room_count'];
	if ( ! is_scalar( $raw_count ) || ! preg_match( '/^[1-9]\d*$/', (string) $raw_count ) ) {
		return new WP_Error( 'offitravel_cabin_invalid_occupancy', __( 'No se pudo determinar la ocupación de las cabinas.', 'offitravel-cabins' ) );
	}
	$room_count = (int) $raw_count;
	$raw_people = array_values( $context['offitravel_room_people'] );
	if ( count( $raw_people ) !== $room_count ) {
		return new WP_Error( 'offitravel_cabin_invalid_occupancy', __( 'No se pudo determinar la ocupación de las cabinas.', 'offitravel-cabins' ) );
	}

	$people = array();
	foreach ( $raw_people as $occupants ) {
		if ( ! is_scalar( $occupants ) || ! preg_match( '/^[1-9]\d*$/', (string) $occupants ) ) {
			return new WP_Error( 'offitravel_cabin_invalid_occupancy', __( 'No se pudo determinar la ocupación de las cabinas.', 'offitravel-cabins' ) );
		}
		$people[] = (int) $occupants;
	}

	return array(
		'room_count' => $room_count,
		'people'     => $people,
	);
}

/**
 * Calculate a canonical cabin supplement snapshot from stored configuration.
 *
 * Browser-supplied prices and subtotals are ignored. Occupancy is accepted only
 * when it exactly matches the trusted room context supplied by the caller.
 * Checkpoint 4 exposes this pure server primitive without attaching it to any
 * public, AJAX, cart, price, session or order hook.
 *
 * @param int   $product_id Product whose stored configuration is authoritative.
 * @param mixed $raw_cabins Untrusted selections keyed by one-based cabin index.
 * @param mixed $context    Trusted room count and occupants.
 * @return array{version:int,product_id:int,cabins:array<int,array<string,mixed>>,total:string}|WP_Error
 */
function offitravel_cabin_calculate_snapshot( $product_id, $raw_cabins, $context ) {
	$product_id = absint( $product_id );
	if ( ! $product_id ) {
		return new WP_Error( 'offitravel_cabin_invalid_product', __( 'El producto indicado no es válido.', 'offitravel-cabins' ) );
	}
	$product = wc_get_product( $product_id );
	if ( ! $product instanceof WC_Product ) {
		return new WP_Error( 'offitravel_cabin_invalid_product', __( 'El producto indicado no es válido.', 'offitravel-cabins' ) );
	}
	if ( ! $product->is_type( 'ovabrw_car_rental' ) ) {
		return new WP_Error( 'offitravel_cabin_invalid_product_type', __( 'Las opciones de cabina sólo pueden utilizarse en productos OVA.', 'offitravel-cabins' ) );
	}
	if ( ! offitravel_cabin_product_is_enabled( $product_id ) ) {
		return new WP_Error( 'offitravel_cabin_product_disabled', __( 'Las opciones de cabina no están activadas para este producto.', 'offitravel-cabins' ) );
	}

	$options = offitravel_cabin_get_product_options( $product_id );
	if ( ! $options ) {
		return new WP_Error( 'offitravel_cabin_options_unavailable', __( 'No hay opciones de cabina válidas para este producto.', 'offitravel-cabins' ) );
	}
	$option_map = array();
	foreach ( $options as $option ) {
		$option_map[ $option['id'] ] = $option;
	}

	$occupancy = offitravel_cabin_normalize_occupancy( $context );
	if ( is_wp_error( $occupancy ) ) {
		return $occupancy;
	}
	if ( ! is_array( $raw_cabins ) || count( $raw_cabins ) !== $occupancy['room_count'] ) {
		return new WP_Error( 'offitravel_cabin_count_mismatch', __( 'Debe elegirse una opción para cada cabina.', 'offitravel-cabins' ) );
	}

	$cabins = array();
	$total  = 0.0;
	for ( $index = 1; $index <= $occupancy['room_count']; ++$index ) {
		if ( ! array_key_exists( $index, $raw_cabins ) || ! is_array( $raw_cabins[ $index ] ) ) {
			return new WP_Error( 'offitravel_cabin_count_mismatch', __( 'Debe elegirse una opción para cada cabina.', 'offitravel-cabins' ) );
		}
		$row        = $raw_cabins[ $index ];
		$raw_people = isset( $row['people'] ) && is_scalar( $row['people'] ) ? (string) $row['people'] : '';
		if ( ! preg_match( '/^[1-9]\d*$/', $raw_people ) || (int) $raw_people !== $occupancy['people'][ $index - 1 ] ) {
			return new WP_Error( 'offitravel_cabin_occupancy_mismatch', __( 'La ocupación enviada no coincide con la reserva.', 'offitravel-cabins' ) );
		}

		$raw_category = isset( $row['category'] ) && is_scalar( $row['category'] ) ? trim( (string) $row['category'] ) : '';
		$category     = sanitize_key( $raw_category );
		if ( '' === $category || ! isset( $option_map[ $category ] ) ) {
			return new WP_Error( 'offitravel_cabin_invalid_category', __( 'La opción de cabina seleccionada no es válida.', 'offitravel-cabins' ) );
		}

		$option    = $option_map[ $category ];
		$occupants = $occupancy['people'][ $index - 1 ];
		$subtotal  = offitravel_cabin_money_snapshot( (float) $option['price_per_person'] * $occupants );
		$total    += (float) $subtotal;
		$cabins[ $index ] = array(
			'cabin_index'     => $index,
			'occupants'       => $occupants,
			'category'        => $category,
			'label'           => $option['label'],
			'price_per_person'=> offitravel_cabin_money_snapshot( $option['price_per_person'] ),
			'subtotal'        => $subtotal,
		);
	}

	return array(
		'version'    => 1,
		'product_id' => $product_id,
		'cabins'     => $cabins,
		'total'      => offitravel_cabin_money_snapshot( $total ),
	);
}

/**
 * Normalize a stored cabin snapshot without consulting current product prices.
 *
 * This is the future persistence boundary: historical labels, unit prices and
 * occupancies are retained, while subtotals and the aggregate are rebuilt.
 *
 * @param mixed $snapshot            Untrusted stored snapshot.
 * @param int   $expected_product_id Optional product ownership check.
 * @return array{version:int,product_id:int,cabins:array<int,array<string,mixed>>,total:string}|WP_Error
 */
function offitravel_cabin_normalize_snapshot( $snapshot, $expected_product_id = 0 ) {
	if ( ! is_array( $snapshot ) || 1 !== (int) ( isset( $snapshot['version'] ) ? $snapshot['version'] : 0 ) || empty( $snapshot['product_id'] ) || empty( $snapshot['cabins'] ) || ! is_array( $snapshot['cabins'] ) ) {
		return new WP_Error( 'offitravel_cabin_invalid_snapshot', __( 'El desglose de cabinas no es válido.', 'offitravel-cabins' ) );
	}
	$product_id = absint( $snapshot['product_id'] );
	if ( ! $product_id ) {
		return new WP_Error( 'offitravel_cabin_invalid_snapshot', __( 'El desglose de cabinas no es válido.', 'offitravel-cabins' ) );
	}
	if ( $expected_product_id && absint( $expected_product_id ) !== $product_id ) {
		return new WP_Error( 'offitravel_cabin_snapshot_product_mismatch', __( 'El desglose de cabinas pertenece a otro producto.', 'offitravel-cabins' ) );
	}

	$cabins = array();
	$total  = 0.0;
	$rows   = array_values( $snapshot['cabins'] );
	foreach ( $rows as $offset => $row ) {
		$index = $offset + 1;
		if ( ! is_array( $row ) || ! isset( $row['cabin_index'], $row['occupants'], $row['category'], $row['label'], $row['price_per_person'] ) ) {
			return new WP_Error( 'offitravel_cabin_invalid_snapshot', __( 'El desglose de cabinas no es válido.', 'offitravel-cabins' ) );
		}
		if ( $index !== (int) $row['cabin_index'] || ! is_scalar( $row['occupants'] ) || ! preg_match( '/^[1-9]\d*$/', (string) $row['occupants'] ) ) {
			return new WP_Error( 'offitravel_cabin_invalid_snapshot', __( 'El desglose de cabinas no es válido.', 'offitravel-cabins' ) );
		}
		$category = is_scalar( $row['category'] ) ? sanitize_key( (string) $row['category'] ) : '';
		$label    = is_scalar( $row['label'] ) ? sanitize_text_field( (string) $row['label'] ) : '';
		$raw_price = is_scalar( $row['price_per_person'] ) ? (string) $row['price_per_person'] : '';
		if ( '' === $category || '' === $label || ! preg_match( '/^\d+(?:\.\d+)?$/', $raw_price ) ) {
			return new WP_Error( 'offitravel_cabin_invalid_snapshot', __( 'El desglose de cabinas no es válido.', 'offitravel-cabins' ) );
		}
		$occupants = (int) $row['occupants'];
		$unit_price = offitravel_cabin_money_snapshot( $raw_price );
		$subtotal   = offitravel_cabin_money_snapshot( (float) $unit_price * $occupants );
		$total     += (float) $subtotal;
		$cabins[ $index ] = array(
			'cabin_index'      => $index,
			'occupants'        => $occupants,
			'category'         => $category,
			'label'            => $label,
			'price_per_person' => $unit_price,
			'subtotal'         => $subtotal,
		);
	}

	return array(
		'version'    => 1,
		'product_id' => $product_id,
		'cabins'     => $cabins,
		'total'      => offitravel_cabin_money_snapshot( $total ),
	);
}

/**
 * Return a validated historical cabin snapshot total as a float.
 *
 * @param mixed $snapshot            Stored snapshot.
 * @param int   $expected_product_id Optional product ownership check.
 * @return float Zero when normalization fails.
 */
function offitravel_cabin_snapshot_total( $snapshot, $expected_product_id = 0 ) {
	$normalized = offitravel_cabin_normalize_snapshot( $snapshot, $expected_product_id );
	return is_wp_error( $normalized ) ? 0.0 : (float) $normalized['total'];
}

/**
 * Resolve the current OVA product ID for public rendering and assets.
 *
 * @param mixed $candidate Explicit product ID or product object.
 * @return int Product ID, or zero outside a valid OVA product.
 */
function offitravel_cabin_public_product_id( $candidate = 0 ) {
	if ( $candidate instanceof WC_Product ) {
		$product_id = (int) $candidate->get_id();
	} elseif ( is_scalar( $candidate ) && (int) $candidate > 0 ) {
		$product_id = absint( $candidate );
	} else {
		$product_id = function_exists( 'get_queried_object_id' ) ? absint( get_queried_object_id() ) : 0;
	}
	$product = $product_id ? wc_get_product( $product_id ) : false;
	return $product instanceof WC_Product && $product->is_type( 'ovabrw_car_rental' ) ? $product_id : 0;
}

/**
 * Validate occupancy against limits already stored on the OVA product.
 *
 * No new commercial maximum or minimum is introduced here. This boundary
 * mirrors the current room-mode product configuration before cabin pricing is
 * calculated, so manipulating the cabin payload cannot bypass those limits.
 *
 * @param int                 $product_id Product ID.
 * @param array<string,mixed> $context    Room count and occupants.
 * @return array{room_count:int,people:array<int,int>}|WP_Error
 */
function offitravel_cabin_validate_product_occupancy( $product_id, array $context ) {
	$occupancy = offitravel_cabin_normalize_occupancy( $context );
	if ( is_wp_error( $occupancy ) ) {
		return $occupancy;
	}
	if ( 'yes' !== get_post_meta( $product_id, '_offitravel_ovabrw_room_mode_enabled', true ) ) {
		return new WP_Error( 'offitravel_cabin_product_occupancy_invalid', __( 'La configuración de cabinas no está disponible para esta reserva.', 'offitravel-cabins' ) );
	}

	$max_rooms    = absint( get_post_meta( $product_id, '_offitravel_ovabrw_room_max_rooms', true ) );
	$max_per_room = absint( get_post_meta( $product_id, '_offitravel_ovabrw_room_max_per_room', true ) );
	$minimum      = absint( get_post_meta( $product_id, 'ovabrw_adults_min', true ) );
	if ( ( $max_rooms && $occupancy['room_count'] > $max_rooms )
		|| ( $minimum && array_sum( $occupancy['people'] ) < $minimum )
	) {
		return new WP_Error( 'offitravel_cabin_product_occupancy_invalid', __( 'La ocupación de las cabinas no coincide con los límites del viaje.', 'offitravel-cabins' ) );
	}
	if ( $max_per_room ) {
		foreach ( $occupancy['people'] as $people ) {
			if ( $people > $max_per_room ) {
				return new WP_Error( 'offitravel_cabin_product_occupancy_invalid', __( 'La ocupación de las cabinas no coincide con los límites del viaje.', 'offitravel-cabins' ) );
			}
		}
	}
	return $occupancy;
}

/**
 * Build a cabin snapshot from an untrusted public request.
 *
 * Only cabin index, occupants and category are consumed. Stored WordPress
 * configuration supplies public labels and prices, while product metadata
 * supplies the permitted occupancy limits.
 *
 * @param int                 $product_id Product ID.
 * @param array<string,mixed> $request    Untrusted request data.
 * @param array<string,mixed> $context    Optional trusted cart occupancy.
 * @return array{version:int,product_id:int,cabins:array<int,array<string,mixed>>,total:string}|WP_Error
 */
function offitravel_cabin_calculate_request_snapshot( $product_id, $request, array $context = array() ) {
	if ( ! is_array( $request ) ) {
		return new WP_Error( 'offitravel_cabin_invalid_request', __( 'No se pudo validar la selección de cabina.', 'offitravel-cabins' ) );
	}
	$request = wp_unslash( $request );
	$raw     = isset( $request['offitravel_cabins'] ) ? $request['offitravel_cabins'] : array();
	if ( ! is_array( $raw ) ) {
		return new WP_Error( 'offitravel_cabin_count_mismatch', __( 'Debe elegirse una opción para cada cabina.', 'offitravel-cabins' ) );
	}
	if ( ! isset( $context['offitravel_room_count'] ) && isset( $request['offitravel_room_count'] ) ) {
		$context['offitravel_room_count'] = $request['offitravel_room_count'];
	}
	if ( ! isset( $context['offitravel_room_people'] ) && isset( $request['offitravel_room_people'] ) ) {
		$context['offitravel_room_people'] = $request['offitravel_room_people'];
	}
	$occupancy = offitravel_cabin_validate_product_occupancy( absint( $product_id ), $context );
	if ( is_wp_error( $occupancy ) ) {
		return $occupancy;
	}
	return offitravel_cabin_calculate_snapshot( absint( $product_id ), $raw, $context );
}

/**
 * Convert a WooCommerce amount to safe decoded plain text.
 *
 * @param mixed $amount Monetary amount.
 * @return string Human-readable price including currency symbol.
 */
function offitravel_cabin_plain_price_text( $amount ) {
	$text    = wp_strip_all_tags( wc_price( $amount ) );
	$charset = get_bloginfo( 'charset' );
	$text    = html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, $charset ? $charset : 'UTF-8' );
	$text    = str_replace( "\xC2\xA0", ' ', $text );
	$text    = preg_replace( '/\s+/u', ' ', $text );
	return trim( is_string( $text ) ? $text : '' );
}

/**
 * Build readable lines from a historical cabin snapshot.
 *
 * @param array<string,mixed> $snapshot Normalized snapshot.
 * @return string[] Plain-text commercial lines.
 */
function offitravel_cabin_snapshot_lines( array $snapshot ) {
	$lines = array();
	foreach ( $snapshot['cabins'] as $cabin ) {
		$people_word = 1 === (int) $cabin['occupants'] ? __( 'persona', 'offitravel-cabins' ) : __( 'personas', 'offitravel-cabins' );
		$lines[] = sprintf(
			/* translators: 1: cabin index, 2: occupants, 3: person/persons, 4: separator, 5: option label. */
			__( 'Cabina %1$d: %2$d %3$s %4$s %5$s', 'offitravel-cabins' ),
			(int) $cabin['cabin_index'],
			(int) $cabin['occupants'],
			$people_word,
			"\xE2\x80\x94",
			$cabin['label']
		);
		$lines[] = sprintf( __( 'Precio por persona: %s', 'offitravel-cabins' ), offitravel_cabin_plain_price_text( $cabin['price_per_person'] ) );
		$lines[] = sprintf( __( 'Subtotal: %s', 'offitravel-cabins' ), offitravel_cabin_plain_price_text( $cabin['subtotal'] ) );
	}
	$lines[] = sprintf( __( 'Total suplementos de cabina: %s', 'offitravel-cabins' ), offitravel_cabin_plain_price_text( $snapshot['total'] ) );
	return $lines;
}

/**
 * Convert cabin lines to safe cart and checkout HTML.
 *
 * @param array<string,mixed> $snapshot Normalized snapshot.
 * @return string Safe HTML containing line breaks only.
 */
function offitravel_cabin_snapshot_display( array $snapshot ) {
	return implode( '<br>', array_map( 'esc_html', offitravel_cabin_snapshot_lines( $snapshot ) ) );
}

/**
 * Render the trusted configuration root consumed by the room-row script.
 *
 * Inputs are created by JavaScript only after the existing room-mode UI has
 * built its real cabin rows.
 *
 * @param mixed $product Product ID or product object supplied by Tripgo.
 * @return void
 */
function offitravel_cabin_booking_markup( $product = 0 ) {
	$product_id = offitravel_cabin_public_product_id( $product );
	if ( ! $product_id || ! offitravel_cabin_product_is_enabled( $product_id ) ) {
		return;
	}
	$options = offitravel_cabin_get_product_options( $product_id );
	if ( ! $options ) {
		return;
	}
	?>
	<div class="offitravel-cabin-config" data-offitravel-cabin-config data-product-id="<?php echo esc_attr( $product_id ); ?>">
		<script type="application/json" data-offitravel-cabin-options><?php echo wp_json_encode( $options, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></script>
	</div>
	<?php
}

/**
 * Enqueue the pure cabin state before the shared supplement frontend.
 *
 * @return void
 */
function offitravel_cabin_enqueue_state() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}
	$product_id = offitravel_cabin_public_product_id();
	if ( ! $product_id || ! offitravel_cabin_product_is_enabled( $product_id ) ) {
		return;
	}
	$path = __DIR__ . '/offitravel-cabin-supplements-state.js';
	wp_enqueue_script(
		'offitravel-cabin-supplements-state',
		plugin_dir_url( __FILE__ ) . 'offitravel-cabin-supplements-state.js',
		array(),
		is_readable( $path ) ? (string) filemtime( $path ) : '1',
		true
	);
}

/**
 * Enqueue the public cabin controls after the shared supplement frontend.
 *
 * @return void
 */
function offitravel_cabin_enqueue_front() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}
	$product_id = offitravel_cabin_public_product_id();
	if ( ! $product_id || ! offitravel_cabin_product_is_enabled( $product_id ) ) {
		return;
	}
	$script_path = __DIR__ . '/offitravel-cabin-supplements-front.js';
	$style_path  = __DIR__ . '/offitravel-cabin-supplements-front.css';
	$deps        = array( 'jquery', 'offitravel-cabin-supplements-state', 'offitravel-product-addons' );
	if ( wp_script_is( 'offitravel-ovabrw-room-mode', 'registered' ) ) {
		$deps[] = 'offitravel-ovabrw-room-mode';
	}
	wp_enqueue_script(
		'offitravel-cabin-supplements-front',
		plugin_dir_url( __FILE__ ) . 'offitravel-cabin-supplements-front.js',
		array_values( array_unique( $deps ) ),
		is_readable( $script_path ) ? (string) filemtime( $script_path ) : '1',
		true
	);
	wp_enqueue_style(
		'offitravel-cabin-supplements-front',
		plugin_dir_url( __FILE__ ) . 'offitravel-cabin-supplements-front.css',
		array(),
		is_readable( $style_path ) ? (string) filemtime( $style_path ) : '1'
	);
}

/**
 * Validate a complete category selection before adding an enabled product.
 *
 * @param bool $passed     Previous validation result.
 * @param int  $product_id Product ID.
 * @param int  $quantity   WooCommerce quantity (unused).
 * @return bool
 */
function offitravel_cabin_validate_cart( $passed, $product_id, $quantity ) {
	unset( $quantity );
	if ( ! $passed || ! offitravel_cabin_product_is_enabled( $product_id ) ) {
		return (bool) $passed;
	}
	$result = offitravel_cabin_calculate_request_snapshot( $product_id, $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	if ( is_wp_error( $result ) ) {
		wc_add_notice( esc_html( $result->get_error_message() ), 'error' );
		return false;
	}
	return true;
}

/**
 * Persist a server-calculated cabin snapshot in the cart item.
 *
 * @param array<string,mixed> $cart_item_data Existing cart data.
 * @param int                 $product_id     Product ID.
 * @param int                 $variation_id   Variation ID (unused).
 * @param int                 $quantity       Line quantity (unused).
 * @return array<string,mixed>
 */
function offitravel_cabin_add_cart_item_data( $cart_item_data, $product_id, $variation_id = 0, $quantity = 1 ) {
	unset( $variation_id, $quantity );
	if ( ! offitravel_cabin_product_is_enabled( $product_id ) ) {
		return $cart_item_data;
	}
	$result = offitravel_cabin_calculate_request_snapshot( $product_id, $_POST, is_array( $cart_item_data ) ? $cart_item_data : array() ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	if ( ! is_wp_error( $result ) ) {
		$cart_item_data[ OFFITRAVEL_CABIN_CART_KEY ] = $result;
	}
	return $cart_item_data;
}

/**
 * Add the normalized cabin total once to the OVA base total.
 *
 * @param float               $line_total    Current OVA total.
 * @param int                 $product_id    Product ID.
 * @param mixed               $checkin_date  OVA check-in value (unused).
 * @param mixed               $checkout_date OVA check-out value (unused).
 * @param array<string,mixed> $cart_item     Cart or AJAX context.
 * @return float
 */
function offitravel_cabin_line_total( $line_total, $product_id, $checkin_date, $checkout_date, $cart_item ) {
	unset( $checkin_date, $checkout_date );
	$product_id = absint( $product_id );
	$context    = is_array( $cart_item ) ? $cart_item : array();
	$total      = 0.0;
	if ( isset( $context[ OFFITRAVEL_CABIN_CART_KEY ] ) ) {
		$total = offitravel_cabin_snapshot_total( $context[ OFFITRAVEL_CABIN_CART_KEY ], $product_id );
	} elseif ( wp_doing_ajax() && offitravel_cabin_product_is_enabled( $product_id ) ) {
		$result = offitravel_cabin_calculate_request_snapshot( $product_id, $_POST, $context ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! is_wp_error( $result ) ) {
			$total = (float) $result['total'];
		}
	}
	return round( (float) $line_total + $total, wc_get_price_decimals() );
}

/**
 * Restore a historical cabin snapshot without current tariff lookups.
 *
 * @param array<string,mixed> $cart_item      Rebuilt cart item.
 * @param array<string,mixed> $session_values Stored session values.
 * @return array<string,mixed>
 */
function offitravel_cabin_restore_cart_item( $cart_item, $session_values ) {
	$product_id = isset( $cart_item['product_id'] ) ? absint( $cart_item['product_id'] ) : ( isset( $session_values['product_id'] ) ? absint( $session_values['product_id'] ) : 0 );
	if ( isset( $session_values[ OFFITRAVEL_CABIN_CART_KEY ] ) ) {
		$normalized = offitravel_cabin_normalize_snapshot( $session_values[ OFFITRAVEL_CABIN_CART_KEY ], $product_id );
		if ( ! is_wp_error( $normalized ) ) {
			$cart_item[ OFFITRAVEL_CABIN_CART_KEY ] = $normalized;
		}
	}
	return $cart_item;
}

/**
 * Append a readable cabin breakdown to cart and checkout.
 *
 * @param array<int,array<string,string>> $item_data Existing display rows.
 * @param array<string,mixed>             $cart_item Cart line data.
 * @return array<int,array<string,string>>
 */
function offitravel_cabin_cart_display( $item_data, $cart_item ) {
	if ( empty( $cart_item['data'] ) || ! $cart_item['data'] instanceof WC_Product || ! isset( $cart_item[ OFFITRAVEL_CABIN_CART_KEY ] ) ) {
		return $item_data;
	}
	$normalized = offitravel_cabin_normalize_snapshot( $cart_item[ OFFITRAVEL_CABIN_CART_KEY ], (int) $cart_item['data']->get_id() );
	if ( ! is_wp_error( $normalized ) ) {
		$item_data[] = array(
			'key'   => __( 'Suplemento de cabina', 'offitravel-cabins' ),
			'value' => offitravel_cabin_snapshot_display( $normalized ),
		);
	}
	return $item_data;
}

/**
 * Persist visible and technical cabin metadata on an order line.
 *
 * @param WC_Order_Item_Product $item          Order item.
 * @param string                $cart_item_key Cart item key (unused).
 * @param array<string,mixed>   $values        Cart line values.
 * @param WC_Order|null         $order         Parent order (unused).
 * @return void
 */
function offitravel_cabin_order_item( $item, $cart_item_key, $values, $order = null ) {
	unset( $cart_item_key, $order );
	if ( ! $item instanceof WC_Order_Item_Product || empty( $values['data'] ) || ! $values['data'] instanceof WC_Product || ! isset( $values[ OFFITRAVEL_CABIN_CART_KEY ] ) ) {
		return;
	}
	$normalized = offitravel_cabin_normalize_snapshot( $values[ OFFITRAVEL_CABIN_CART_KEY ], (int) $values['data']->get_id() );
	if ( is_wp_error( $normalized ) ) {
		return;
	}
	$item->add_meta_data( __( 'Suplemento de cabina', 'offitravel-cabins' ), implode( "\n", offitravel_cabin_snapshot_lines( $normalized ) ), true );
	$item->add_meta_data( OFFITRAVEL_CABIN_ORDER_SNAPSHOT_META, $normalized, true );
	$item->add_meta_data( OFFITRAVEL_CABIN_ORDER_TOTAL_META, $normalized['total'], true );
}

/**
 * Hide technical cabin metadata while retaining the commercial row.
 *
 * @param string[] $hidden Existing hidden order item keys.
 * @return string[]
 */
function offitravel_cabin_hidden_order_itemmeta( $hidden ) {
	$hidden[] = OFFITRAVEL_CABIN_ORDER_SNAPSHOT_META;
	$hidden[] = OFFITRAVEL_CABIN_ORDER_TOTAL_META;
	return array_values( array_unique( $hidden ) );
}

/**
 * Add the isolated cabin configuration metabox to OVA rental products.
 *
 * @param WP_Post $post Product post.
 * @return void
 */
function offitravel_cabin_add_product_metabox( $post ) {
	if ( ! $post instanceof WP_Post ) {
		return;
	}
	$product = wc_get_product( $post->ID );
	if ( ! $product instanceof WC_Product || ! $product->is_type( 'ovabrw_car_rental' ) ) {
		return;
	}
	add_meta_box(
		'offitravel-cabin-options',
		__( 'Opciones de cabina — Base técnica', 'offitravel-cabins' ),
		'offitravel_cabin_render_product_metabox',
		'product',
		'normal',
		'default'
	);
}

/**
 * Render product-scoped cabin option rows without activating public behavior.
 *
 * @param WP_Post $post Product post.
 * @return void
 */
function offitravel_cabin_render_product_metabox( $post ) {
	$options = offitravel_cabin_get_product_options( $post->ID );
	$rows    = $options ? $options : array( array( 'id' => '', 'label' => '', 'price_per_person' => '' ) );
	wp_nonce_field( 'offitravel_cabin_save_options', 'offitravel_cabin_nonce' );
	?>
	<div data-offitravel-cabin-admin>
		<input type="hidden" name="offitravel_cabin_metabox_interacted" value="0" data-offitravel-cabin-interacted />
		<p><?php esc_html_e( 'Esta configuración no activa ningún selector público en el Checkpoint 4.', 'offitravel-cabins' ); ?></p>
		<table class="widefat striped" data-offitravel-cabin-options-table>
			<thead>
				<tr>
					<th><?php esc_html_e( 'Identificador interno', 'offitravel-cabins' ); ?></th>
					<th><?php esc_html_e( 'Etiqueta pública', 'offitravel-cabins' ); ?></th>
					<th><?php esc_html_e( 'Suplemento por persona', 'offitravel-cabins' ); ?></th>
					<th><span class="screen-reader-text"><?php esc_html_e( 'Acciones', 'offitravel-cabins' ); ?></span></th>
				</tr>
			</thead>
			<tbody data-offitravel-cabin-option-rows>
				<?php foreach ( $rows as $index => $row ) : ?>
					<?php offitravel_cabin_render_admin_option_row( $index, $row ); ?>
				<?php endforeach; ?>
			</tbody>
		</table>
		<p><button type="button" class="button" data-offitravel-cabin-add-option><?php esc_html_e( 'Añadir opción', 'offitravel-cabins' ); ?></button></p>
		<script type="text/html" data-offitravel-cabin-option-template>
			<?php offitravel_cabin_render_admin_option_row( '__INDEX__', array( 'id' => '', 'label' => '', 'price_per_person' => '' ) ); ?>
		</script>
	</div>
	<?php
}

/**
 * Render one administrative option row.
 *
 * @param int|string          $index Row index or template placeholder.
 * @param array<string,mixed> $row   Normalized row values.
 * @return void
 */
function offitravel_cabin_render_admin_option_row( $index, array $row ) {
	$base = 'offitravel_cabin_options[' . $index . ']';
	?>
	<tr data-offitravel-cabin-option-row>
		<td><input type="text" name="<?php echo esc_attr( $base . '[id]' ); ?>" value="<?php echo esc_attr( isset( $row['id'] ) ? $row['id'] : '' ); ?>" data-offitravel-cabin-option-id /></td>
		<td><input type="text" name="<?php echo esc_attr( $base . '[label]' ); ?>" value="<?php echo esc_attr( isset( $row['label'] ) ? $row['label'] : '' ); ?>" data-offitravel-cabin-option-label /></td>
		<td><input type="text" inputmode="decimal" class="wc_input_price" name="<?php echo esc_attr( $base . '[price_per_person]' ); ?>" value="<?php echo esc_attr( isset( $row['price_per_person'] ) ? $row['price_per_person'] : '' ); ?>" data-offitravel-cabin-option-price /></td>
		<td><button type="button" class="button-link-delete" data-offitravel-cabin-remove-option><?php esc_html_e( 'Eliminar', 'offitravel-cabins' ); ?></button></td>
	</tr>
	<?php
}

/**
 * Record an administrative validation error for the redirect notice.
 *
 * @param int      $post_id Product ID.
 * @param WP_Error $error   Validation error.
 * @return void
 */
function offitravel_cabin_record_admin_error( $post_id, WP_Error $error ) {
	$GLOBALS['offitravel_cabin_admin_errors'][ absint( $post_id ) ] = $error->get_error_code();
}

/**
 * Preserve a cabin validation error code across the product-save redirect.
 *
 * @param string $location Redirect URL.
 * @param int    $post_id  Product ID.
 * @return string
 */
function offitravel_cabin_admin_redirect_error( $location, $post_id ) {
	$errors = isset( $GLOBALS['offitravel_cabin_admin_errors'] ) && is_array( $GLOBALS['offitravel_cabin_admin_errors'] )
		? $GLOBALS['offitravel_cabin_admin_errors']
		: array();
	return isset( $errors[ $post_id ] ) ? add_query_arg( 'offitravel_cabin_error', sanitize_key( $errors[ $post_id ] ), $location ) : $location;
}

/**
 * Display a product-editor notice after rejected cabin configuration.
 *
 * @return void
 */
function offitravel_cabin_admin_notice() {
	if ( empty( $_GET['offitravel_cabin_error'] ) ) {
		return;
	}
	echo '<div class="notice notice-error"><p>' . esc_html__( 'No se guardaron las opciones de cabina porque la configuración no es válida.', 'offitravel-cabins' ) . '</p></div>';
}

/**
 * Enqueue the isolated metabox script on product editor screens only.
 *
 * @param string $hook_suffix Current administrative page hook.
 * @return void
 */
function offitravel_cabin_enqueue_admin_script( $hook_suffix ) {
	if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'product' !== $screen->post_type ) {
		return;
	}
	$script_path = __DIR__ . '/offitravel-cabin-supplements-admin.js';
	wp_enqueue_script(
		'offitravel-cabin-supplements-admin',
		plugins_url( 'offitravel-cabin-supplements-admin.js', __FILE__ ),
		array(),
		file_exists( $script_path ) ? (string) filemtime( $script_path ) : '0.1.0',
		true
	);
}

/**
 * Save explicitly edited option rows after complete validation.
 *
 * The hidden interaction marker starts at zero and is changed by the dedicated
 * admin script. Therefore an ordinary product save performs no cabin metadata
 * writes, migrations or deletions.
 *
 * @param int          $post_id Product ID.
 * @param WP_Post|null $post    Product post when supplied by the hook.
 * @return void
 */
function offitravel_cabin_save_product_options( $post_id, $post = null ) {
	if ( ! isset( $_POST['offitravel_cabin_metabox_interacted'] ) || '1' !== sanitize_text_field( wp_unslash( $_POST['offitravel_cabin_metabox_interacted'] ) ) ) {
		return;
	}
	if ( ! isset( $_POST['offitravel_cabin_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['offitravel_cabin_nonce'] ) ), 'offitravel_cabin_save_options' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$post = $post instanceof WP_Post ? $post : get_post( $post_id );
	if ( ! $post instanceof WP_Post || 'product' !== $post->post_type ) {
		return;
	}
	$product = wc_get_product( $post_id );
	if ( ! $product instanceof WC_Product || ! $product->is_type( 'ovabrw_car_rental' ) ) {
		return;
	}

	$raw     = isset( $_POST['offitravel_cabin_options'] ) ? wp_unslash( $_POST['offitravel_cabin_options'] ) : array();
	$options = offitravel_cabin_validate_admin_payload( $raw );
	if ( is_wp_error( $options ) ) {
		offitravel_cabin_record_admin_error( $post_id, $options );
		return;
	}
	if ( $options ) {
		update_post_meta( $post_id, OFFITRAVEL_CABIN_META_OPTIONS, $options );
	} else {
		delete_post_meta( $post_id, OFFITRAVEL_CABIN_META_OPTIONS );
	}
}

add_action( 'add_meta_boxes_product', 'offitravel_cabin_add_product_metabox', 10, 1 );
add_action( 'woocommerce_process_product_meta', 'offitravel_cabin_save_product_options', 20, 2 );
add_filter( 'redirect_post_location', 'offitravel_cabin_admin_redirect_error', 98, 2 );
add_action( 'admin_notices', 'offitravel_cabin_admin_notice' );
add_action( 'admin_enqueue_scripts', 'offitravel_cabin_enqueue_admin_script' );

add_action( 'tripgo_booking_form', 'offitravel_cabin_booking_markup', 24, 1 );
add_action( 'wp_enqueue_scripts', 'offitravel_cabin_enqueue_state', 390 );
add_action( 'wp_enqueue_scripts', 'offitravel_cabin_enqueue_front', 410 );
add_filter( 'woocommerce_add_to_cart_validation', 'offitravel_cabin_validate_cart', 101, 3 );
add_filter( 'woocommerce_add_cart_item_data', 'offitravel_cabin_add_cart_item_data', 30, 4 );
add_filter( 'ovabrw_get_price_by_guests', 'offitravel_cabin_line_total', 1009, 5 );
add_filter( 'woocommerce_get_cart_item_from_session', 'offitravel_cabin_restore_cart_item', 30, 2 );
add_filter( 'woocommerce_get_item_data', 'offitravel_cabin_cart_display', 40, 2 );
add_action( 'woocommerce_checkout_create_order_line_item', 'offitravel_cabin_order_item', 20, 4 );
add_filter( 'woocommerce_hidden_order_itemmeta', 'offitravel_cabin_hidden_order_itemmeta', 20 );
