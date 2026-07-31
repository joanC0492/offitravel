<?php
/**
 * Plugin Name: Offitravel – OVA BRW modo habitaciones
 * Description: Selector de habitaciones y personas por habitación; el total se sincroniza con ovabrw_adults para el precio.
 * Version: 1.7.3
 * Author: Offitravel
 *
 * Mapa de precios (origen pack, suplemento individual, orden de hooks):
 * @see OFFITRAVEL-pricing-map.txt (mismo directorio mu-plugins).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'OFFITRAVEL_OVABRW_ROOM_META_ENABLED', '_offitravel_ovabrw_room_mode_enabled' );
define( 'OFFITRAVEL_OVABRW_ROOM_META_MAX_ROOMS', '_offitravel_ovabrw_room_max_rooms' );
define( 'OFFITRAVEL_OVABRW_ROOM_META_MAX_PER_ROOM', '_offitravel_ovabrw_room_max_per_room' );
define( 'OFFITRAVEL_OVABRW_ROOM_META_FIXED_PRICING', '_offitravel_ovabrw_room_fixed_pricing' );
define( 'OFFITRAVEL_OVABRW_ROOM_META_FIXED_RULES', '_offitravel_ovabrw_room_fixed_rules' );
define( 'OFFITRAVEL_OVABRW_ROOM_META_FIXED_REQUIRE', '_offitravel_ovabrw_room_fixed_require' );
define( 'OFFITRAVEL_OVABRW_ROOM_META_SINGLE_SUPPLEMENT', '_offitravel_ovabrw_room_single_supplement_eur' );
define( 'OFFITRAVEL_OVABRW_ROOM_META_OCC_DISCOUNT_ENABLED', '_offitravel_ovabrw_room_occ_discount_enabled' );
/** @deprecated Migración desde v1.7.0: usar metas por banda. */
define( 'OFFITRAVEL_OVABRW_ROOM_META_DISCOUNT_TRIPLE_PP', '_offitravel_ovabrw_room_discount_triple_pp' );
/** @deprecated */
define( 'OFFITRAVEL_OVABRW_ROOM_META_DISCOUNT_QUAD_PP', '_offitravel_ovabrw_room_discount_quad_pp' );
define( 'OFFITRAVEL_OVABRW_ROOM_META_DISCOUNT_BAND1_DAYS', '_offitravel_ovabrw_room_disc_b1_weekdays' );
define( 'OFFITRAVEL_OVABRW_ROOM_META_DISCOUNT_BAND2_DAYS', '_offitravel_ovabrw_room_disc_b2_weekdays' );
define( 'OFFITRAVEL_OVABRW_ROOM_META_DISCOUNT_B1_TRIPLE_PP', '_offitravel_ovabrw_room_disc_b1_triple_pp' );
define( 'OFFITRAVEL_OVABRW_ROOM_META_DISCOUNT_B1_QUAD_PP', '_offitravel_ovabrw_room_disc_b1_quad_pp' );
define( 'OFFITRAVEL_OVABRW_ROOM_META_DISCOUNT_B2_TRIPLE_PP', '_offitravel_ovabrw_room_disc_b2_triple_pp' );
define( 'OFFITRAVEL_OVABRW_ROOM_META_DISCOUNT_B2_QUAD_PP', '_offitravel_ovabrw_room_disc_b2_quad_pp' );
define( 'OFFITRAVEL_OVABRW_MATRIX_META_ENABLED', '_offitravel_ovabrw_matrix_cckf_enabled' );
define( 'OFFITRAVEL_OVABRW_MATRIX_META_FIELD_KEY', '_offitravel_ovabrw_matrix_cckf_field_key' );
define( 'OFFITRAVEL_OVABRW_MATRIX_META_BAND1_DAYS', '_offitravel_ovabrw_matrix_band1_weekdays' );
define( 'OFFITRAVEL_OVABRW_MATRIX_META_BAND2_DAYS', '_offitravel_ovabrw_matrix_band2_weekdays' );
define( 'OFFITRAVEL_OVABRW_MATRIX_META_BAND1_MAP', '_offitravel_ovabrw_matrix_band1_prices' );
define( 'OFFITRAVEL_OVABRW_MATRIX_META_BAND2_MAP', '_offitravel_ovabrw_matrix_band2_prices' );

/**
 * @param int $product_id
 * @return bool
 */
function offitravel_ovabrw_room_mode_enabled( $product_id ) {
	return 'yes' === get_post_meta( (int) $product_id, OFFITRAVEL_OVABRW_ROOM_META_ENABLED, true );
}

/**
 * @param int $product_id
 * @return array{enabled:bool,max_rooms:int,max_per_room:int}
 */
function offitravel_ovabrw_get_room_settings( $product_id ) {
	$product_id = (int) $product_id;
	$max_rooms  = (int) get_post_meta( $product_id, OFFITRAVEL_OVABRW_ROOM_META_MAX_ROOMS, true );
	if ( $max_rooms < 1 ) {
		$max_rooms = 10;
	}
	$max_rooms = min( 50, max( 1, $max_rooms ) );

	$max_per = (int) get_post_meta( $product_id, OFFITRAVEL_OVABRW_ROOM_META_MAX_PER_ROOM, true );
	if ( $max_per < 1 ) {
		$max_per = 4;
	}
	$max_per = min( 50, max( 1, $max_per ) );

	return array(
		'enabled'      => offitravel_ovabrw_room_mode_enabled( $product_id ),
		'max_rooms'    => $max_rooms,
		'max_per_room' => $max_per,
	);
}

/**
 * Reparto inicial de personas en habitaciones (para URL o mínimos).
 *
 * @param int   $total          Total deseado de adultos.
 * @param int   $max_per_room   Máximo por habitación.
 * @param int   $max_rooms      Máximo de habitaciones.
 * @param int   $min_adults     Mínimo de adultos del producto.
 * @param int   $max_adults     Máximo de adultos del producto (0 = sin límite práctico).
 * @return int[] Lista de enteros (personas por habitación).
 */
function offitravel_ovabrw_room_distribution_for_total( $total, $max_per_room, $max_rooms, $min_adults, $max_adults ) {
	$max_per_room = max( 1, (int) $max_per_room );
	$max_rooms    = max( 1, (int) $max_rooms );
	$min_adults   = max( 0, (int) $min_adults );
	$total = max( $min_adults, (int) $total );
	if ( $total < 1 && $min_adults < 1 ) {
		$total = 1;
	}
	if ( $max_adults > 0 ) {
		$total = min( $total, (int) $max_adults );
	}

	$cap_total = $max_rooms * $max_per_room;
	if ( $total > $cap_total ) {
		$total = $cap_total;
	}

	$rooms = array();
	$rem   = $total;
	while ( $rem > 0 && count( $rooms ) < $max_rooms ) {
		$p       = min( $rem, $max_per_room );
		$rooms[] = $p;
		$rem    -= $p;
	}
	if ( empty( $rooms ) ) {
		$rooms = array( min( $total, $max_per_room ) );
	}
	return $rooms;
}

/**
 * ¿Precio fijo por total de personas (y días de inicio) activo?
 *
 * @param int $product_id
 * @return bool
 */
function offitravel_ovabrw_room_fixed_pricing_enabled( $product_id ) {
	return 'yes' === get_post_meta( (int) $product_id, OFFITRAVEL_OVABRW_ROOM_META_FIXED_PRICING, true );
}

/**
 * Si no hay regla coincidente, bloquear reserva (solo si hay al menos una regla guardada).
 *
 * @param int $product_id
 * @return bool
 */
function offitravel_ovabrw_room_fixed_require_rule( $product_id ) {
	return 'yes' === get_post_meta( (int) $product_id, OFFITRAVEL_OVABRW_ROOM_META_FIXED_REQUIRE, true );
}

/**
 * Convierte fecha de inicio (string, timestamp unix o objeto) al timestamp UNIX en zona del sitio.
 *
 * @param mixed $checkin_date
 * @return int|null
 */
function offitravel_ovabrw_checkin_to_site_timestamp( $checkin_date ) {
	if ( null === $checkin_date || '' === $checkin_date || false === $checkin_date ) {
		return null;
	}

	if ( is_numeric( $checkin_date ) ) {
		$ts = (int) $checkin_date;
		if ( $ts > 100000 ) {
			return $ts;
		}
	}

	$str = trim( is_string( $checkin_date ) ? $checkin_date : (string) $checkin_date );
	if ( '' === $str ) {
		return null;
	}

	if ( function_exists( 'wc_string_to_datetime' ) ) {
		try {
			$dt = wc_string_to_datetime( $str );
			if ( $dt ) {
				return (int) $dt->getTimestamp();
			}
		} catch ( Exception $e ) {
			unset( $e );
		}
	}

	$t = strtotime( $str );

	return $t && $t > 0 ? (int) $t : null;
}

/**
 * Día de semana PHP (date('w')) para un timestamp según zona del sitio: 0=domingo … 6=sábado.
 *
 * @param int $timestamp_unix
 * @return int|null
 */
function offitravel_ovabrw_weekday_for_site_timestamp( $timestamp_unix ) {
	$timestamp_unix = (int) $timestamp_unix;
	if ( $timestamp_unix < 1 ) {
		return null;
	}
	return (int) wp_date( 'w', $timestamp_unix );
}

/**
 * Comprueba si la regla aplica para el día del check-in.
 *
 * @param array    $rule       Regla; clave opcional `weekdays` (0–6, como date 'w'; vacío = todos los días).
 * @param int|null $checkin_ts Timestamp UNIX de inicio o null si no hay fecha válida.
 * @return bool
 */
function offitravel_ovabrw_fixed_rule_matches_weekday( array $rule, $checkin_ts ) {
	$rule_w = isset( $rule['weekdays'] ) && is_array( $rule['weekdays'] ) ? array_map( 'intval', array_values( $rule['weekdays'] ) ) : array();

	// Backward-compatible: ningún filtro por día → todos los días.
	if ( empty( $rule_w ) ) {
		return true;
	}

	$dw = null;
	if ( null !== $checkin_ts ) {
		$dw = offitravel_ovabrw_weekday_for_site_timestamp( (int) $checkin_ts );
	}

	if ( null === $dw ) {
		return false;
	}

	foreach ( $rule_w as $d ) {
		if ( $d === $dw && $d >= 0 && $d <= 6 ) {
			return true;
		}
	}

	return false;
}

/**
 * ¿Descuento por habitación triple/cuádruple (respecto doble) activo?
 *
 * @param int $product_id
 * @return bool
 */
function offitravel_ovabrw_room_occ_discount_enabled( $product_id ) {
	return 'yes' === get_post_meta( (int) $product_id, OFFITRAVEL_OVABRW_ROOM_META_OCC_DISCOUNT_ENABLED, true );
}

/**
 * Días donde aplica cada banda de descuento triple/cuádruple (igual criterio que rejillas: 0 dom … 6 sáb).
 *
 * @param int $product_id
 * @param int $band_num 1 o 2
 * @return int[]
 */
function offitravel_ovabrw_room_occ_discount_band_weekdays( $product_id, $band_num ) {
	$product_id = (int) $product_id;
	$key        = ( 2 === $band_num ) ? OFFITRAVEL_OVABRW_ROOM_META_DISCOUNT_BAND2_DAYS : OFFITRAVEL_OVABRW_ROOM_META_DISCOUNT_BAND1_DAYS;
	$csv        = trim( (string) get_post_meta( $product_id, $key, true ) );
	$days       = function_exists( 'offitravel_ovabrw_matrix_parse_weekdays_csv' )
		? offitravel_ovabrw_matrix_parse_weekdays_csv( $csv )
		: array();
	if ( ! empty( $days ) ) {
		return $days;
	}
	return ( 2 === $band_num ) ? array( 5, 6 ) : array( 0, 2, 3, 4 );
}

/**
 * @param int $product_id
 * @return bool Hay importes guardados por banda (post v1.7.1).
 */
function offitravel_ovabrw_room_occ_discount_has_band_amount_meta( $product_id ) {
	$product_id = (int) $product_id;
	foreach (
		array(
			OFFITRAVEL_OVABRW_ROOM_META_DISCOUNT_B1_TRIPLE_PP,
			OFFITRAVEL_OVABRW_ROOM_META_DISCOUNT_B1_QUAD_PP,
			OFFITRAVEL_OVABRW_ROOM_META_DISCOUNT_B2_TRIPLE_PP,
			OFFITRAVEL_OVABRW_ROOM_META_DISCOUNT_B2_QUAD_PP,
		) as $k
	) {
		$v = get_post_meta( $product_id, $k, true );
		if ( '' !== $v && null !== $v ) {
			return true;
		}
	}
	return false;
}

/**
 * €/persona triple o cuádruple para una banda (meta vacío → defaults tabla comercial).
 *
 * @param int    $product_id
 * @param int    $band_num   1 o 2
 * @param string $kind       triple|quad
 * @return float
 */
function offitravel_ovabrw_room_occ_discount_amount_for_band( $product_id, $band_num, $kind ) {
	$product_id = (int) $product_id;
	$band_num   = (int) $band_num;
	$kind       = ( 'quad' === $kind ) ? 'quad' : 'triple';

	$defaults = array(
		1 => array( 'triple' => 5.0, 'quad' => 12.0 ),
		2 => array( 'triple' => 10.0, 'quad' => 31.0 ),
	);
	$def       = isset( $defaults[ $band_num ][ $kind ] ) ? $defaults[ $band_num ][ $kind ] : 0.0;
	$meta_keys = array(
		1 => array(
			'triple' => OFFITRAVEL_OVABRW_ROOM_META_DISCOUNT_B1_TRIPLE_PP,
			'quad'   => OFFITRAVEL_OVABRW_ROOM_META_DISCOUNT_B1_QUAD_PP,
		),
		2 => array(
			'triple' => OFFITRAVEL_OVABRW_ROOM_META_DISCOUNT_B2_TRIPLE_PP,
			'quad'   => OFFITRAVEL_OVABRW_ROOM_META_DISCOUNT_B2_QUAD_PP,
		),
	);
	if ( ! isset( $meta_keys[ $band_num ] ) ) {
		return 0.0;
	}
	$raw = get_post_meta( $product_id, $meta_keys[ $band_num ][ $kind ], true );

	if ( '' !== $raw && null !== $raw ) {
		$dec = wc_format_decimal( str_replace( ',', '.', (string) $raw ) );
		return max( 0.0, floatval( '' !== $dec && null !== $dec ? $dec : '0' ) );
	}

	$legacy_key = ( 'triple' === $kind ) ? OFFITRAVEL_OVABRW_ROOM_META_DISCOUNT_TRIPLE_PP : OFFITRAVEL_OVABRW_ROOM_META_DISCOUNT_QUAD_PP;
	$leg        = get_post_meta( $product_id, $legacy_key, true );
	if ( '' !== $leg && null !== $leg && ! offitravel_ovabrw_room_occ_discount_has_band_amount_meta( $product_id ) && 1 === $band_num ) {
		return max( 0.0, floatval( wc_format_decimal( str_replace( ',', '.', (string) $leg ) ) ) );
	}

	return $def;
}

/**
 * Pick-up normalizado (pickup_date_new OVA) para saber día de semana del descuento.
 *
 * @param int                     $product_id
 * @param mixed                   $checkin_date   Arg del filtro o vacío.
 * @param mixed                   $checkout_date
 * @param array<string,mixed>     $cart_item
 * @return int|null unix
 */
function offitravel_ovabrw_room_normalized_pickup_for_discount( $product_id, $checkin_date, $checkout_date, array $cart_item ) {
	$product_id = (int) $product_id;
	if ( $product_id < 1 ) {
		return null;
	}

	$cin  = $checkin_date;
	$cout = $checkout_date;

	if ( function_exists( 'ovabrw_get_meta_data' ) ) {
		if ( ( null === $cin || '' === $cin ) ) {
			$pick = ovabrw_get_meta_data( 'ovabrw_pickup_date', $cart_item, '' );
			if ( '' !== trim( (string) $pick ) ) {
				$ts_try = strtotime( (string) $pick );
				$cin    = ( false !== $ts_try && $ts_try > 0 ) ? $ts_try : $pick;
			}
		}
		if ( ( null === $cout || '' === $cout ) ) {
			$poff = ovabrw_get_meta_data( 'ovabrw_pickoff_date', $cart_item, '' );
			if ( '' !== trim( (string) $poff ) ) {
				$ts2 = strtotime( (string) $poff );
				$cout = ( false !== $ts2 && $ts2 > 0 ) ? $ts2 : $poff;
			}
		}
	}

	if ( null === $cin || '' === $cin ) {
		return null;
	}

	if ( function_exists( 'ovabrw_new_input_date' ) && function_exists( 'ovabrw_get_date_format' ) && function_exists( 'ovabrw_get_meta_data' ) ) {
		$d  = ovabrw_new_input_date( $product_id, $cin, $cout ? $cout : '', ovabrw_get_date_format() );
		$ci = (int) ovabrw_get_meta_data( 'pickup_date_new', $d, 0 );
		return $ci > 0 ? $ci : null;
	}

	$fallback = offitravel_ovabrw_checkin_to_site_timestamp( $cin );

	return ( null !== $fallback && $fallback > 0 ) ? $fallback : null;
}

/**
 * Importes triple/cuádruple según día de inicio; vacío si el día no entra en ninguna banda.
 *
 * @param int      $product_id
 * @param int|null $pickup_new_ci_unix Como ovabrw_new_input_date pickup_date_new
 * @return array{triple:float,quad:float}
 */
function offitravel_ovabrw_room_occ_discount_resolve_rates( $product_id, $pickup_new_ci_unix ) {
	$product_id = (int) $product_id;
	$out        = array(
		'triple' => 0.0,
		'quad'   => 0.0,
	);
	if ( $product_id < 1 ) {
		return $out;
	}

	if ( null === $pickup_new_ci_unix || (int) $pickup_new_ci_unix < 1 ) {
		$leg_t = get_post_meta( $product_id, OFFITRAVEL_OVABRW_ROOM_META_DISCOUNT_TRIPLE_PP, true );
		$leg_q = get_post_meta( $product_id, OFFITRAVEL_OVABRW_ROOM_META_DISCOUNT_QUAD_PP, true );
		if ( ! offitravel_ovabrw_room_occ_discount_has_band_amount_meta( $product_id ) && ( '' !== (string) $leg_t || '' !== (string) $leg_q ) ) {
			$out['triple'] = ( '' !== $leg_t && null !== $leg_t )
				? max( 0.0, floatval( wc_format_decimal( str_replace( ',', '.', (string) $leg_t ) ) ) )
				: 5.0;
			$out['quad'] = ( '' !== $leg_q && null !== $leg_q )
				? max( 0.0, floatval( wc_format_decimal( str_replace( ',', '.', (string) $leg_q ) ) ) )
				: 12.0;
		}
		return $out;
	}

	$dw = offitravel_ovabrw_weekday_for_site_timestamp( (int) $pickup_new_ci_unix );
	if ( null === $dw ) {
		return $out;
	}

	$b1_days = offitravel_ovabrw_room_occ_discount_band_weekdays( $product_id, 1 );
	$b2_days = offitravel_ovabrw_room_occ_discount_band_weekdays( $product_id, 2 );

	if ( ! empty( $b1_days ) && in_array( (int) $dw, $b1_days, true ) ) {
		$out['triple'] = offitravel_ovabrw_room_occ_discount_amount_for_band( $product_id, 1, 'triple' );
		$out['quad']   = offitravel_ovabrw_room_occ_discount_amount_for_band( $product_id, 1, 'quad' );

		return $out;
	}
	if ( ! empty( $b2_days ) && in_array( (int) $dw, $b2_days, true ) ) {
		$out['triple'] = offitravel_ovabrw_room_occ_discount_amount_for_band( $product_id, 2, 'triple' );
		$out['quad']   = offitravel_ovabrw_room_occ_discount_amount_for_band( $product_id, 2, 'quad' );

		return $out;
	}

	return $out;
}

/**
 * Total € a restar de la línea (una unidad de reserva) según personas por habitación y día de inicio.
 *
 * @param int        $product_id
 * @param array      $cart_item  Carrito o stub; ocupación puede venir de $_POST en AJAX.
 * @param mixed|null $checkin_date  Opcional; si falta se toma del carrito.
 * @param mixed|null $checkout_date
 * @return float Siempre >= 0 (importe a descontar).
 */
function offitravel_ovabrw_room_occ_discount_line_reduction( $product_id, array $cart_item, $checkin_date = null, $checkout_date = null ) {
	$product_id = (int) $product_id;
	if ( $product_id < 1 || ! offitravel_ovabrw_room_mode_enabled( $product_id ) || ! offitravel_ovabrw_room_occ_discount_enabled( $product_id ) ) {
		return 0.0;
	}
	$occ = offitravel_ovabrw_room_get_occupancy_from_context( $cart_item );
	if ( null === $occ || empty( $occ['people'] ) ) {
		return 0.0;
	}

	$ci_unix = offitravel_ovabrw_room_normalized_pickup_for_discount( $product_id, $checkin_date, $checkout_date, $cart_item );
	$rates   = offitravel_ovabrw_room_occ_discount_resolve_rates( $product_id, $ci_unix );
	$tpp     = (float) $rates['triple'];
	$qpp     = (float) $rates['quad'];

	$sum = 0.0;
	foreach ( $occ['people'] as $n ) {
		$n = (int) $n;
		if ( 3 === $n && $tpp > 0 ) {
			$sum += $tpp * $n;
		} elseif ( $n >= 4 && $qpp > 0 ) {
			$sum += $qpp * $n;
		}
	}
	return round( max( 0.0, $sum ), wc_get_price_decimals() );
}

/**
 * @param float  $line_total
 * @param int    $product_id
 * @param mixed  $checkin_date
 * @param mixed  $checkout_date
 * @param array  $cart_item
 * @return float
 */
function offitravel_ovabrw_room_occ_discount_filter_line( $line_total, $product_id, $checkin_date, $checkout_date, $cart_item ) {
	$product_id = (int) $product_id;
	if ( $product_id < 1 || ! is_array( $cart_item ) || ! function_exists( 'ovabrw_get_meta_data' ) ) {
		return $line_total;
	}

	$cut = offitravel_ovabrw_room_occ_discount_line_reduction( $product_id, $cart_item, $checkin_date, $checkout_date );
	if ( $cut <= 0 ) {
		return $line_total;
	}
	$qty = max( 1, absint( ovabrw_get_meta_data( 'ovabrw_quantity', $cart_item, 1 ) ) );

	return round( max( 0.0, (float) $line_total - $cut * $qty ), wc_get_price_decimals() );
}

add_filter( 'ovabrw_get_price_by_guests', 'offitravel_ovabrw_room_occ_discount_filter_line', 844, 5 );

/**
 * AJAX: repartir descuento ocupación en el precio mostrado por adulto cuando hay POST de habitaciones.
 *
 * @param array $price_guests
 * @param mixed $product_id
 * @param mixed $checkin_date
 * @param int   $numberof_adults
 * @param int   $numberof_children
 * @param int   $numberof_babies
 * @param string $time_from
 * @return array
 */
function offitravel_ovabrw_room_occ_discount_filter_price_per_guests( $price_guests, $product_id, $checkin_date, $numberof_adults, $numberof_children, $numberof_babies, $time_from ) {
	unset( $numberof_children, $numberof_babies, $time_from );
	if ( ! is_array( $price_guests ) || ! $product_id ) {
		return $price_guests;
	}
	$pid = (int) $product_id;
	if ( $pid < 1 ) {
		return $price_guests;
	}
	$p = wc_get_product( $pid );
	if ( ! $p || ! $p->is_type( 'ovabrw_car_rental' ) ) {
		return $price_guests;
	}
	if ( ! function_exists( 'ovabrw_get_meta_data' ) ) {
		return $price_guests;
	}

	$cut = offitravel_ovabrw_room_occ_discount_line_reduction( $pid, array(), $checkin_date, null );
	if ( $cut <= 0 ) {
		return $price_guests;
	}
	$adults = max( 1, absint( $numberof_adults ) );
	$delta  = $cut / $adults;

	$price_guests['adults_price'] = round( max( 0.0, (float) $price_guests['adults_price'] - $delta ), wc_get_price_decimals() );

	return $price_guests;
}

add_filter( 'ovabrw_price_per_guests', 'offitravel_ovabrw_room_occ_discount_filter_price_per_guests', 1015, 7 );

/**
 * @param int $product_id
 * @return bool
 */
function offitravel_ovabrw_matrix_cckf_enabled( $product_id ) {
	return 'yes' === get_post_meta( (int) $product_id, OFFITRAVEL_OVABRW_MATRIX_META_ENABLED, true );
}

/**
 * Slug del CCKF (select/radio…) cuyas opciones = filas en las rejillas.
 *
 * @param int $product_id
 * @return string
 */
function offitravel_ovabrw_matrix_get_field_key( $product_id ) {
	return trim( (string) get_post_meta( (int) $product_id, OFFITRAVEL_OVABRW_MATRIX_META_FIELD_KEY, true ) );
}

/**
 * Opciones del select CCKF del producto (ID OVA + etiqueta), para desplegables en admin rejillas.
 *
 * @param int    $product_id
 * @param string $field_key Slug CCKF (mismo que la rejilla).
 * @return array<int, array{id:string, label:string}>
 */
function offitravel_ovabrw_matrix_admin_cckf_select_options( $product_id, $field_key ) {
	$product_id = (int) $product_id;
	$field_key  = trim( (string) $field_key );
	$out        = array();
	if ( $product_id < 1 || '' === $field_key ) {
		return $out;
	}
	if ( ! function_exists( 'ovabrw_get_list_field_checkout' ) || ! function_exists( 'ovabrw_array_exists' ) || ! function_exists( 'ovabrw_get_meta_data' ) ) {
		return $out;
	}
	$list = ovabrw_get_list_field_checkout( $product_id );
	if ( ! ovabrw_array_exists( $list ) || ! isset( $list[ $field_key ] ) || ! is_array( $list[ $field_key ] ) ) {
		return $out;
	}
	$fields = $list[ $field_key ];
	if ( 'select' !== ovabrw_get_meta_data( 'type', $fields ) ) {
		return $out;
	}
	$keys  = ovabrw_get_meta_data( 'ova_options_key', $fields, array() );
	$texts = ovabrw_get_meta_data( 'ova_options_text', $fields, array() );
	if ( ! is_array( $keys ) ) {
		return $out;
	}
	foreach ( $keys as $idx => $id_raw ) {
		$id_str = trim( is_scalar( $id_raw ) ? (string) $id_raw : '' );
		if ( '' === $id_str ) {
			continue;
		}
		$label = ovabrw_get_meta_data( $idx, $texts, '' );
		$label = is_scalar( $label ) ? trim( (string) $label ) : '';
		if ( '' === $label ) {
			$label = $id_str;
		}
		$out[] = array(
			'id'    => $id_str,
			'label' => $label,
		);
	}
	return $out;
}

/**
 * Campo de clave opción en tablas banda: select (etiqueta visible, value = ID) o texto libre.
 *
 * @param string                                   $input_name   name del control (con [] si aplica).
 * @param array<int, array{id:string, label:string}> $options  De offitravel_ovabrw_matrix_admin_cckf_select_options().
 * @param string                                   $saved_id     Clave guardada en meta.
 * @param bool                                     $use_select   false = input text.
 * @return void
 */
function offitravel_ovabrw_matrix_admin_render_option_control( $input_name, array $options, $saved_id, $use_select ) {
	$saved_id = trim( is_string( $saved_id ) ? $saved_id : (string) $saved_id );
	if ( ! $use_select ) {
		printf(
			'<input type="text" name="%1$s" value="%2$s" class="regular-text" placeholder="%3$s" autocomplete="off" />',
			esc_attr( $input_name ),
			esc_attr( $saved_id ),
			esc_attr( 'sin_tren' )
		);
		return;
	}
	$known = array();
	foreach ( $options as $pair ) {
		$known[ $pair['id'] ] = true;
	}
	printf( '<select name="%s" class="regular-text offitravel-matrix-cckf-opt">', esc_attr( $input_name ) );
	echo '<option value=""' . selected( '', $saved_id, false ) . '>' . esc_html__( '— Elegir opción —', 'offitravel-ovabrw' ) . '</option>';
	foreach ( $options as $pair ) {
		printf(
			'<option value="%1$s"%3$s>%2$s</option>',
			esc_attr( $pair['id'] ),
			esc_html( $pair['label'] ),
			selected( $saved_id, $pair['id'], false )
		);
	}
	if ( '' !== $saved_id && ! isset( $known[ $saved_id ] ) ) {
		printf(
			'<option value="%1$s" selected>%2$s</option>',
			esc_attr( $saved_id ),
			esc_html(
				sprintf(
					/* translators: %s: option key stored in grid but not in current CCKF options */
					__( 'Guardado: %s (no en lista)', 'offitravel-ovabrw' ),
					$saved_id
				)
			)
		);
	}
	echo '</select>';
}

/**
 * Markup interno de un &lt;select&gt; de opciones (placeholder; opcional selected en vacío para filas nuevas).
 *
 * @param array<int, array{id:string, label:string}> $options
 * @param bool                                           $placeholder_selected Marcar «Elegir opción» como selected.
 * @return string HTML seguro (sin envoltorio select).
 */
function offitravel_ovabrw_matrix_admin_option_select_options_html( array $options, $placeholder_selected = false ) {
	ob_start();
	printf(
		'<option value=""%s>%s</option>',
		$placeholder_selected ? ' selected="selected"' : '',
		esc_html__( '— Elegir opción —', 'offitravel-ovabrw' )
	);
	foreach ( $options as $pair ) {
		printf(
			'<option value="%1$s">%2$s</option>',
			esc_attr( $pair['id'] ),
			esc_html( $pair['label'] )
		);
	}
	return ob_get_clean();
}

/**
 * @param string $csv
 * @return int[]
 */
function offitravel_ovabrw_matrix_parse_weekdays_csv( $csv ) {
	$csv = trim( preg_replace( '/\s+/', '', (string) $csv ), " \t\n\r\0\x0b,;" );
	if ( '' === $csv ) {
		return array();
	}
	$parts = preg_split( '/[,;]+/', $csv, -1, PREG_SPLIT_NO_EMPTY );
	$days  = array();
	foreach ( (array) $parts as $p ) {
		$d = absint( $p );
		if ( $d >= 0 && $d <= 6 ) {
			$days[] = $d;
		}
	}
	return array_values( array_unique( $days, SORT_NUMERIC ) );
}

/**
 * Días donde aplica la banda (meta vacío → defaults programa).
 *
 * @param int $product_id
 * @param int $band_num 1 o 2
 * @return int[]
 */
function offitravel_ovabrw_matrix_get_band_weekdays( $product_id, $band_num ) {
	$product_id = (int) $product_id;
	if ( $product_id < 1 ) {
		return array();
	}
	$key  = ( 2 === $band_num ) ? OFFITRAVEL_OVABRW_MATRIX_META_BAND2_DAYS : OFFITRAVEL_OVABRW_MATRIX_META_BAND1_DAYS;
	$days = offitravel_ovabrw_matrix_parse_weekdays_csv( (string) get_post_meta( $product_id, $key, true ) );
	if ( ! empty( $days ) ) {
		return $days;
	}
	return ( 2 === $band_num ) ? array( 5, 6 ) : array( 0, 2, 3, 4 );
}

/**
 * @param int $product_id
 * @param int $band_num
 * @return array<string,float>
 */
function offitravel_ovabrw_matrix_get_band_option_map( $product_id, $band_num ) {
	$key      = ( 2 === $band_num ) ? OFFITRAVEL_OVABRW_MATRIX_META_BAND2_MAP : OFFITRAVEL_OVABRW_MATRIX_META_BAND1_MAP;
	$raw      = get_post_meta( (int) $product_id, $key, true );
	$out      = array();
	if ( ! is_array( $raw ) ) {
		return $out;
	}
	foreach ( $raw as $opt_key => $price_val ) {
		$opt = trim( is_string( $opt_key ) ? $opt_key : (string) $opt_key );
		if ( '' === $opt ) {
			continue;
		}
		$pv = wc_format_decimal( str_replace( ',', '.', (string) $price_val ) );
		if ( '' === $pv ) {
			continue;
		}
		$f = floatval( $pv );
		if ( $f < 0 ) {
			continue;
		}
		$out[ $opt ] = round( $f, wc_get_price_decimals() );
	}
	return $out;
}

/**
 * Busca precio €/persona en el mapa usando la opción seleccionada (clave OVA o alias tolerados).
 *
 * Caso habitual: primera opción del select («Sin Tren») lleva ID `rey_leon` pero en Excel/rejilla se guardó la fila `sin_tren`.
 *
 * @param int    $product_id
 * @param string $field_key  Slug CCKF.
 * @param array  $map        clave_opción → precio
 * @param string $sel_opt    Valor seleccionado (ova_options_key).
 * @return float|null
 */
function offitravel_ovabrw_matrix_lookup_price_in_map( $product_id, $field_key, array $map, $sel_opt ) {
	$product_id = (int) $product_id;
	$field_key  = trim( (string) $field_key );
	$sel_opt    = trim( (string) $sel_opt );

	if ( $product_id < 1 || '' === $field_key || '' === $sel_opt || empty( $map ) ) {
		$fallback = apply_filters(
			'offitravel_ovabrw_matrix_map_price_resolved',
			null,
			$product_id,
			$field_key,
			$map,
			$sel_opt
		);
		return ( is_numeric( $fallback ) ) ? (float) $fallback : null;
	}

	if ( isset( $map[ $sel_opt ] ) ) {
		return (float) $map[ $sel_opt ];
	}

	foreach ( $map as $k => $price ) {
		if ( strcasecmp( trim( (string) $k ), $sel_opt ) === 0 ) {
			return (float) $price;
		}
	}

	if ( function_exists( 'ovabrw_get_list_field_checkout' ) && function_exists( 'ovabrw_array_exists' ) && function_exists( 'ovabrw_get_meta_data' ) ) {
		$list = ovabrw_get_list_field_checkout( $product_id );
		if ( ovabrw_array_exists( $list ) ) {
			$fields = ovabrw_get_meta_data( $field_key, $list );
			if ( ovabrw_array_exists( $fields ) && 'select' === ovabrw_get_meta_data( 'type', $fields ) ) {
				$opt_keys = ovabrw_get_meta_data( 'ova_options_key', $fields );
				if ( ovabrw_array_exists( $opt_keys ) && isset( $opt_keys[0] ) ) {
					$first_key = trim( (string) $opt_keys[0] );
					if ( $first_key !== '' && $first_key === $sel_opt ) {
						$candidates_defaults = array( 'sin_tren', 'sin-tren', 'sintren', 'no_train' );
						$candidates_unique   = array();
						foreach ( apply_filters( 'offitravel_ovabrw_matrix_first_option_aliases', $candidates_defaults, $product_id, $field_key ) as $alias_raw ) {
							$a = trim( is_string( $alias_raw ) ? $alias_raw : (string) $alias_raw );
							if ( '' !== $a && ! isset( $candidates_unique[ $a ] ) ) {
								$candidates_unique[ $a ] = true;
							}
						}
						$candidates = array_keys( $candidates_unique );
						foreach ( $candidates as $alias ) {
							if ( isset( $map[ $alias ] ) ) {
								return (float) $map[ $alias ];
							}
						}
						foreach ( $map as $k => $price ) {
							$nk = strtolower( trim( (string) $k ) );
							foreach ( $candidates as $alias ) {
								if ( strtolower( $alias ) === $nk ) {
									return (float) $price;
								}
							}
						}
					}
				}
			}
		}
	}

	$fallback = apply_filters(
		'offitravel_ovabrw_matrix_map_price_resolved',
		null,
		$product_id,
		$field_key,
		$map,
		$sel_opt
	);
	return ( is_numeric( $fallback ) ) ? (float) $fallback : null;
}

/**
 * Pick-up normalizado como en get_price_by_guests (ovabrw_new_input_date).
 *
 * @param int         $product_id
 * @param mixed       $checkin_any
 * @param string|int $checkout_any Opcional unix o vacío.
 * @return int|null Timestamp unix o null
 */
function offitravel_ovabrw_matrix_normalized_pickup_ts( $product_id, $checkin_any, $checkout_any = '' ) {
	$product_id = (int) $product_id;
	if ( $product_id < 1 || ! function_exists( 'ovabrw_new_input_date' ) || ! function_exists( 'ovabrw_get_meta_data' ) ) {
		return null;
	}
	$chk = null;
	if ( is_numeric( $checkin_any ) && (int) $checkin_any > 100000 ) {
		$chk = (int) $checkin_any;
	} elseif ( '' !== trim( is_string( $checkin_any ) ? $checkin_any : (string) $checkin_any ) ) {
		$conv = offitravel_ovabrw_checkin_to_site_timestamp( $checkin_any );
		if ( null !== $conv ) {
			$chk = $conv;
		}
	}
	if ( null === $chk ) {
		return null;
	}
	$cout = '';
	if ( is_numeric( $checkout_any ) && (int) $checkout_any > 100000 ) {
		$cout = (int) $checkout_any;
	} elseif ( '' !== trim( is_string( $checkout_any ) ? $checkout_any : (string) $checkout_any ) ) {
		$co_conv = offitravel_ovabrw_checkin_to_site_timestamp( $checkout_any );
		if ( null !== $co_conv ) {
			$cout = $co_conv;
		}
	}
	$df       = function_exists( 'ovabrw_get_date_format' ) ? ovabrw_get_date_format() : 'd-m-Y';
	$new      = ovabrw_new_input_date( $product_id, $chk, $cout, $df );
	$new_pick = (int) ovabrw_get_meta_data( 'pickup_date_new', $new, 0 );
	return $new_pick > 0 ? $new_pick : null;
}

/**
 * Precio único sumado por OVA para un CCKF (select/radio/checkbox), para restarlo cuando la rejilla cubre ese campo.
 *
 * @param int    $product_id
 * @param string $field_key
 * @param array  $cckf_data
 * @param array  $cckf_qty
 * @return float
 */
function offitravel_ovabrw_cckf_selected_field_price( $product_id, $field_key, $cckf_data, $cckf_qty ) {
	$product_id = (int) $product_id;
	if ( $product_id < 1 || ! is_string( $field_key ) || '' === $field_key || ! is_array( $cckf_data ) ) {
		return 0.0;
	}
	if ( ! function_exists( 'ovabrw_get_list_field_checkout' ) || ! function_exists( 'ovabrw_array_exists' ) || ! function_exists( 'ovabrw_get_meta_data' ) ) {
		return 0.0;
	}
	$list = ovabrw_get_list_field_checkout( $product_id );
	if ( ! ovabrw_array_exists( $list ) ) {
		return 0.0;
	}
	if ( ! isset( $cckf_data[ $field_key ] ) ) {
		return 0.0;
	}
	$val = $cckf_data[ $field_key ];

	$fields = ovabrw_get_meta_data( $field_key, $list );
	if ( ! ovabrw_array_exists( $fields ) ) {
		return 0.0;
	}

	$type = ovabrw_get_meta_data( 'type', $fields );

	if ( 'radio' === $type ) {
		$opt_values = ovabrw_get_meta_data( 'ova_radio_values', $fields );
		if ( ! ovabrw_array_exists( $opt_values ) ) {
			return 0.0;
		}
		$opt_prices = ovabrw_get_meta_data( 'ova_radio_prices', $fields );
		if ( ! ovabrw_array_exists( $opt_prices ) ) {
			return 0.0;
		}
		$opt_qty = (int) ovabrw_get_meta_data( $field_key, $cckf_qty, 1 );
		foreach ( $opt_values as $k => $v ) {
			if ( $v === $val ) {
				return (float) ovabrw_get_meta_data( $k, $opt_prices ) * max( 1, $opt_qty );
			}
		}
	} elseif ( 'select' === $type ) {
		$opt_keys = ovabrw_get_meta_data( 'ova_options_key', $fields );
		if ( ! ovabrw_array_exists( $opt_keys ) ) {
			return 0.0;
		}
		$opt_prices = ovabrw_get_meta_data( 'ova_options_price', $fields );
		if ( ! ovabrw_array_exists( $opt_prices ) ) {
			return 0.0;
		}
		$opt_qty = (int) ovabrw_get_meta_data( $field_key, $cckf_qty, 1 );
		foreach ( $opt_keys as $k => $v ) {
			if ( $val === $v ) {
				return (float) ovabrw_get_meta_data( $k, $opt_prices ) * max( 1, $opt_qty );
			}
		}
	} elseif ( 'checkbox' === $type && ovabrw_array_exists( $val ) ) {
		$opt_keys = ovabrw_get_meta_data( 'ova_checkbox_key', $fields );
		if ( ! ovabrw_array_exists( $opt_keys ) ) {
			return 0.0;
		}
		$opt_prices = ovabrw_get_meta_data( 'ova_checkbox_price', $fields );
		if ( ! ovabrw_array_exists( $opt_prices ) ) {
			return 0.0;
		}
		$opt_qtys = ovabrw_get_meta_data( $field_key, $cckf_qty, array() );
		$total    = 0.0;
		foreach ( (array) $val as $opt_id ) {
			$idx = array_search( $opt_id, $opt_keys, true );
			if ( false === $idx ) {
				continue;
			}
			$oqty = (int) ovabrw_get_meta_data( $opt_id, $opt_qtys, 1 );
			$total += (float) ovabrw_get_meta_data( $idx, $opt_prices ) * max( 1, $oqty );
		}
		return $total;
	}

	return 0.0;
}

/**
 * PVP cerrado por persona si aplica rejilla banda + opción, o null.
 *
 * @param int                     $product_id
 * @param int|null               $normalized_pickup_ts unix
 * @param array<string,mixed>    $cart_item
 * @return float|null
 */
function offitravel_ovabrw_matrix_resolve_per_person( $product_id, $normalized_pickup_ts, array $cart_item ) {
	$product_id = (int) $product_id;
	if ( ! offitravel_ovabrw_matrix_cckf_enabled( $product_id ) || null === $normalized_pickup_ts ) {
		return null;
	}
	$fkey = offitravel_ovabrw_matrix_get_field_key( $product_id );
	if ( '' === $fkey ) {
		return null;
	}

	$n_child = isset( $cart_item['ovabrw_childrens'] ) ? (int) $cart_item['ovabrw_childrens'] : 0;
	$n_baby  = isset( $cart_item['ovabrw_babies'] ) ? (int) $cart_item['ovabrw_babies'] : 0;
	if ( function_exists( 'ovabrw_get_meta_data' ) ) {
		$n_child = absint( ovabrw_get_meta_data( 'ovabrw_childrens', $cart_item, $n_child ) );
		$n_baby  = absint( ovabrw_get_meta_data( 'ovabrw_babies', $cart_item, $n_baby ) );
	}
	if ( $n_child !== 0 || $n_baby !== 0 ) {
		return null;
	}

	$cckf = array();
	if ( function_exists( 'ovabrw_get_meta_data' ) ) {
		$d = ovabrw_get_meta_data( 'custom_ckf', $cart_item, array() );
		if ( is_array( $d ) ) {
			$cckf = $d;
		}
	} elseif ( ! empty( $cart_item['custom_ckf'] ) && is_array( $cart_item['custom_ckf'] ) ) {
		$cckf = $cart_item['custom_ckf'];
	}

	if ( ! isset( $cckf[ $fkey ] ) || '' === $cckf[ $fkey ] ) {
		return null;
	}
	$raw_sel = $cckf[ $fkey ];
	if ( ! is_scalar( $raw_sel ) ) {
		return null;
	}
	$sel_opt = trim( (string) $raw_sel );
	if ( '' === $sel_opt ) {
		return null;
	}

	$dw = offitravel_ovabrw_weekday_for_site_timestamp( (int) $normalized_pickup_ts );
	if ( null === $dw ) {
		return null;
	}

	$b1_days = offitravel_ovabrw_matrix_get_band_weekdays( $product_id, 1 );
	$b2_days = offitravel_ovabrw_matrix_get_band_weekdays( $product_id, 2 );
	$b1_map  = offitravel_ovabrw_matrix_get_band_option_map( $product_id, 1 );
	$b2_map  = offitravel_ovabrw_matrix_get_band_option_map( $product_id, 2 );

	if ( empty( $b1_map ) && empty( $b2_map ) ) {
		return null;
	}

	$chosen = null;
	if ( ! empty( $b1_days ) && in_array( $dw, $b1_days, true ) && ! empty( $b1_map ) ) {
		$chosen = offitravel_ovabrw_matrix_lookup_price_in_map( $product_id, $fkey, $b1_map, $sel_opt );
	} elseif ( ! empty( $b2_days ) && in_array( $dw, $b2_days, true ) && ! empty( $b2_map ) ) {
		$chosen = offitravel_ovabrw_matrix_lookup_price_in_map( $product_id, $fkey, $b2_map, $sel_opt );
	}

	return null !== $chosen ? (float) $chosen : null;
}

/**
 * Si la rejilla persona+cumple filtros aplicó ya el PVP, no duplicar con suplementos pack_* (resto de viajeros).
 *
 * @param int   $product_id
 * @param mixed $checkin_date
 * @param mixed $checkout_date
 * @param array $cart_item
 * @return bool
 */
function offitravel_ovabrw_matrix_should_suppress_train_pack_addon( $product_id, $checkin_date, $checkout_date, array $cart_item ) {
	$product_id = (int) $product_id;
	if (
		! offitravel_ovabrw_matrix_cckf_enabled( $product_id )
		|| ! function_exists( 'ovabrw_new_input_date' )
		|| ! function_exists( 'ovabrw_get_meta_data' )
		|| ! function_exists( 'ovabrw_get_date_format' )
	) {
		return false;
	}
	$children = absint( ovabrw_get_meta_data( 'ovabrw_childrens', $cart_item, 0 ) );
	$babies   = absint( ovabrw_get_meta_data( 'ovabrw_babies', $cart_item, 0 ) );
	if ( 0 !== $children || 0 !== $babies ) {
		return false;
	}
	$adults = absint( ovabrw_get_meta_data( 'ovabrw_adults', $cart_item, 0 ) );
	if ( $adults < 1 ) {
		return false;
	}
	$d  = ovabrw_new_input_date( $product_id, $checkin_date, $checkout_date, ovabrw_get_date_format() );
	$ci = (int) ovabrw_get_meta_data( 'pickup_date_new', $d, 0 );
	if ( $ci < 1 ) {
		return false;
	}
	return null !== offitravel_ovabrw_matrix_resolve_per_person( $product_id, $ci, $cart_item );
}

/**
 * STUB desde POST AJAX OVA (custom_ckf) cuando ovabrw_price_per_guests no tiene cart_item.
 *
 * @param int|false $product_id
 * @param int       $numberof_adults
 * @param int       $numberof_children
 * @param int       $numberof_babies
 * @return array|null
 */
function offitravel_ovabrw_matrix_cart_stub_from_post( $product_id, $numberof_adults, $numberof_children, $numberof_babies ) {
	if ( ! isset( $_POST['action'], $_POST['custom_ckf'] ) || ! $product_id ) {
		return null;
	}
	$action = sanitize_text_field( wp_unslash( $_POST['action'] ) );
	if ( ! in_array( $action, array( 'ovabrw_calculate_total', 'ovabrw_create_order_get_total' ), true ) ) {
		return null;
	}
	if ( ! empty( $_POST['product_id'] ) ) {
		$pid_post = absint( wp_unslash( $_POST['product_id'] ) );
		if ( $pid_post && absint( $product_id ) !== $pid_post ) {
			return null;
		}
	}
	if ( ! function_exists( 'ovabrw_recursive_replace' ) || ! function_exists( 'ovabrw_get_meta_data' ) ) {
		return null;
	}
	$cckf_raw = ovabrw_recursive_replace( '\\', '', ovabrw_get_meta_data( 'custom_ckf', $_POST ) );
	if ( '' === $cckf_raw ) {
		return null;
	}
	$decoded = json_decode( (string) $cckf_raw, true );
	$decoded = is_array( $decoded ) ? $decoded : array();
	$q_raw   = ovabrw_recursive_replace( '\\', '', ovabrw_get_meta_data( 'cckf_qty', $_POST, '' ) );
	$qo      = '' !== $q_raw ? json_decode( (string) $q_raw, true ) : array();

	return array(
		'custom_ckf'       => $decoded,
		'cckf_qty'         => is_array( $qo ) ? $qo : array(),
		'ovabrw_adults'    => absint( $numberof_adults ),
		'ovabrw_childrens' => absint( $numberof_children ),
		'ovabrw_babies'    => absint( $numberof_babies ),
	);
}

/**
 * Ajuste del total línea: (PVP matriz × adultos − global OVA × qty − precio opción duplicado en CCKF).
 *
 * @param float  $line_total
 * @param int    $product_id
 * @param mixed  $checkin_date
 * @param mixed  $checkout_date
 * @param array  $cart_item
 * @return float
 */
function offitravel_ovabrw_matrix_filter_get_price_by_guests( $line_total, $product_id, $checkin_date, $checkout_date, $cart_item ) {
	$product_id = (int) $product_id;
	if ( $product_id < 1 ) {
		return $line_total;
	}

	$p = wc_get_product( $product_id );
	if ( ! $p || ! $p->is_type( 'ovabrw_car_rental' ) ) {
		return $line_total;
	}

	if ( ! is_array( $cart_item ) || ! function_exists( 'ovabrw_get_meta_data' ) ) {
		return $line_total;
	}

	$children = absint( ovabrw_get_meta_data( 'ovabrw_childrens', $cart_item, 0 ) );
	$babies   = absint( ovabrw_get_meta_data( 'ovabrw_babies', $cart_item, 0 ) );
	if ( $children !== 0 || $babies !== 0 ) {
		return $line_total;
	}

	$adults = absint( ovabrw_get_meta_data( 'ovabrw_adults', $cart_item, 0 ) );
	if ( $adults < 1 ) {
		return $line_total;
	}

	if ( ! offitravel_ovabrw_matrix_cckf_enabled( $product_id ) ) {
		return $line_total;
	}

	if ( ! function_exists( 'ovabrw_new_input_date' ) || ! function_exists( 'ovabrw_get_date_format' ) ) {
		return $line_total;
	}

	$new_dates_c = ovabrw_new_input_date( $product_id, $checkin_date, $checkout_date, ovabrw_get_date_format() );
	$new_ci      = (int) ovabrw_get_meta_data( 'pickup_date_new', $new_dates_c, 0 );
	$new_co      = ovabrw_get_meta_data( 'pickoff_date_new', $new_dates_c, '' );

	if ( $new_ci < 1 ) {
		return $line_total;
	}

	$pp = offitravel_ovabrw_matrix_resolve_per_person( $product_id, $new_ci, $cart_item );
	if ( null === $pp ) {
		return $line_total;
	}

	$time_from = ovabrw_get_meta_data( 'ovabrw_time_from', $cart_item, '' );

	$fkey = offitravel_ovabrw_matrix_get_field_key( $product_id );

	$G = function_exists( 'ovabrw_price_global' )
		? ovabrw_price_global( $product_id, $new_ci, $new_co, $adults, 0, 0, $time_from )
		: (float) 0;

	$cckf_qty = ovabrw_get_meta_data( 'cckf_qty', $cart_item, array() );
	$cckf_dat = ovabrw_get_meta_data( 'custom_ckf', $cart_item, array() );
	if ( ! is_array( $cckf_qty ) ) {
		$cckf_qty = array();
	}
	if ( ! is_array( $cckf_dat ) ) {
		$cckf_dat = array();
	}
	$subtract = ( '' !== $fkey )
		? offitravel_ovabrw_cckf_selected_field_price( $product_id, $fkey, $cckf_dat, $cckf_qty )
		: 0.0;

	$qty = max( 1, absint( ovabrw_get_meta_data( 'ovabrw_quantity', $cart_item, 1 ) ) );

	$delta = ( $pp * (float) $adults - (float) $G ) * (float) $qty - (float) $subtract;

	return round( (float) $line_total + $delta, wc_get_price_decimals() );
}

/**
 * Desglose AJAX: matriz (1010) y descuento triple/cuádruple por habitación (1015).
 *
 * @param array   $price_guests
 * @param int|false $product_id
 * @param mixed   $checkin_date
 * @param int     $numberof_adults
 * @param int     $numberof_children
 * @param int     $numberof_babies
 * @param string  $time_from
 * @return array
 */
function offitravel_ovabrw_matrix_filter_price_guests_fixup( $price_guests, $product_id, $checkin_date, $numberof_adults, $numberof_children, $numberof_babies, $time_from ) {
	unset( $time_from );
	if ( ! is_array( $price_guests ) || ! $product_id ) {
		return $price_guests;
	}
	$wc = wc_get_product( (int) $product_id );
	if ( ! $wc || ! $wc->is_type( 'ovabrw_car_rental' ) ) {
		return $price_guests;
	}
	if ( (int) $numberof_children !== 0 || (int) $numberof_babies !== 0 ) {
		return $price_guests;
	}
	if ( ! offitravel_ovabrw_matrix_cckf_enabled( (int) $product_id ) ) {
		return $price_guests;
	}

	$stub = offitravel_ovabrw_matrix_cart_stub_from_post( $product_id, $numberof_adults, $numberof_children, $numberof_babies );
	if ( ! is_array( $stub ) ) {
		return $price_guests;
	}

	$new_pick = offitravel_ovabrw_matrix_normalized_pickup_ts( (int) $product_id, $checkin_date, '' );
	if ( null === $new_pick ) {
		return $price_guests;
	}

	$pp = offitravel_ovabrw_matrix_resolve_per_person( (int) $product_id, $new_pick, $stub );
	if ( null === $pp ) {
		return $price_guests;
	}

	$price_guests['adults_price'] = $pp;

	return $price_guests;
}

add_filter( 'ovabrw_get_price_by_guests', 'offitravel_ovabrw_matrix_filter_get_price_by_guests', 815, 5 );
add_filter( 'ovabrw_price_per_guests', 'offitravel_ovabrw_matrix_filter_price_guests_fixup', 1010, 7 );

/**
 * Reglas de precio fijo: array de { total_guests, price, weekdays?: int[] }.
 * Meta antigua (rooms + personas por habitación) se interpreta como total_guests = sum(personas).
 *
 * @param int $product_id
 * @return array<int, array{total_guests:int, price:float, weekdays?:array<int,int>}>
 */
function offitravel_ovabrw_get_room_fixed_rules( $product_id ) {
	$raw = get_post_meta( (int) $product_id, OFFITRAVEL_OVABRW_ROOM_META_FIXED_RULES, true );
	if ( ! is_array( $raw ) ) {
		return array();
	}
	$out = array();
	foreach ( $raw as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$price = isset( $row['price'] ) ? floatval( $row['price'] ) : 0;
		if ( $price < 0 ) {
			continue;
		}

		$total_guests = 0;
		if ( isset( $row['total_guests'] ) ) {
			$total_guests = absint( $row['total_guests'] );
		} else {
			$rooms  = isset( $row['rooms'] ) ? absint( $row['rooms'] ) : 0;
			$people = isset( $row['people'] ) && is_array( $row['people'] ) ? array_map( 'absint', $row['people'] ) : array();
			if ( $rooms < 1 || empty( $people ) || count( $people ) !== $rooms ) {
				continue;
			}
			foreach ( $people as $pp ) {
				if ( $pp < 1 ) {
					continue 2;
				}
			}
			$total_guests = (int) array_sum( $people );
		}

		if ( $total_guests < 1 ) {
			continue;
		}

		$weekdays = array();
		if ( isset( $row['weekdays'] ) && is_array( $row['weekdays'] ) ) {
			foreach ( $row['weekdays'] as $d ) {
				$d = absint( $d );
				if ( $d >= 0 && $d <= 6 ) {
					$weekdays[] = $d;
				}
			}
			$weekdays = array_values( array_unique( $weekdays ) );
		}

		$out_row = array(
			'total_guests' => $total_guests,
			'price'        => $price,
		);
		if ( ! empty( $weekdays ) ) {
			$out_row['weekdays'] = $weekdays;
		}
		$out[] = $out_row;
	}
	return $out;
}

/**
 * Total adultos para tarifa fija: suma modo habitaciones o fallback ovabrw_adults.
 *
 * @param array $cart_item Contexto carrito / POST AJAX OVA.
 * @return int|null
 */
function offitravel_ovabrw_fixed_total_guests_for_pricing( array $cart_item ) {
	$occ = offitravel_ovabrw_room_get_occupancy_from_context( $cart_item );
	if ( null !== $occ ) {
		return (int) array_sum( $occ['people'] );
	}

	if ( function_exists( 'ovabrw_get_meta_data' ) ) {
		$adults = absint( ovabrw_get_meta_data( 'ovabrw_adults', $cart_item, 0 ) );
		if ( $adults > 0 ) {
			return $adults;
		}
	}

	return null;
}

/**
 * Obtiene datos de habitaciones desde carrito o POST (AJAX).
 *
 * @param array $cart_item
 * @return array{room_count:int, people:int[]}|null
 */
function offitravel_ovabrw_room_get_occupancy_from_context( array $cart_item ) {
	$room_count = 0;
	$people     = array();

	if ( ! empty( $cart_item['offitravel_room_count'] ) && isset( $cart_item['offitravel_room_people'] ) && is_array( $cart_item['offitravel_room_people'] ) ) {
		$room_count = (int) $cart_item['offitravel_room_count'];
		$people     = array_map( 'absint', $cart_item['offitravel_room_people'] );
	} elseif ( isset( $_POST['offitravel_room_count'], $_POST['offitravel_room_people'] ) ) {
		$room_count = absint( wp_unslash( $_POST['offitravel_room_count'] ) );
		$raw        = wp_unslash( $_POST['offitravel_room_people'] );
		$people     = is_array( $raw ) ? array_map( 'absint', $raw ) : array();
	}

	if ( $room_count < 1 || count( $people ) !== $room_count ) {
		return null;
	}

	return array(
		'room_count' => $room_count,
		'people'     => $people,
	);
}

/**
 * Busca precio fijo por total de personas (+ día de inicio) o null.
 *
 * @param int    $product_id
 * @param int    $total_guests Total adultos.
 * @param mixed  $checkin_date Opcional (fecha/reloj pickup).
 * @return float|null
 */
function offitravel_ovabrw_room_match_fixed_price( $product_id, $total_guests, $checkin_date = null ) {
	$total_guests = (int) $total_guests;
	if ( $total_guests < 1 ) {
		return null;
	}
	$checkin_ts = offitravel_ovabrw_checkin_to_site_timestamp( $checkin_date );
	foreach ( offitravel_ovabrw_get_room_fixed_rules( $product_id ) as $rule ) {
		if ( (int) $rule['total_guests'] !== $total_guests ) {
			continue;
		}
		if ( ! offitravel_ovabrw_fixed_rule_matches_weekday( $rule, $checkin_ts ) ) {
			continue;
		}
		return (float) $rule['price'];
	}
	return null;
}

/**
 * Fecha pickup desde POST (solo fecha, como en OVA).
 *
 * @return string|null
 */
function offitravel_ovabrw_get_pickup_raw_from_post() {
	if ( function_exists( 'ovabrw_get_meta_data' ) ) {
		$r = ovabrw_get_meta_data( 'ovabrw_pickup_date', $_POST );
		if ( is_array( $r ) ) {
			foreach ( $r as $chunk ) {
				$s = trim( wp_unslash( (string) $chunk ) );
				if ( '' !== $s ) {
					return sanitize_text_field( $s );
				}
			}
			return null;
		}
		if ( null === $r || '' === trim( wp_unslash( (string) $r ) ) ) {
			return null;
		}
		return sanitize_text_field( wp_unslash( (string) $r ) );
	}
	if ( ! isset( $_POST['ovabrw_pickup_date'] ) ) {
		return null;
	}
	$pd = wp_unslash( $_POST['ovabrw_pickup_date'] );
	if ( is_array( $pd ) ) {
		foreach ( $pd as $chunk ) {
			$s = trim( (string) $chunk );
			if ( '' !== $s ) {
				return sanitize_text_field( $s );
			}
		}
		return null;
	}
	$s = trim( (string) $pd );

	return '' === $s ? null : sanitize_text_field( $s );
}

/**
 * Cadena fecha/hora pickup para igualar día de semana con OVA (fecha + opcional horario).
 *
 * @param int $product_id Product ID WooCommerce.
 * @return string|null
 */
function offitravel_ovabrw_pickup_for_fixed_rule_matching( $product_id ) {
	$pickup_raw = offitravel_ovabrw_get_pickup_raw_from_post();
	if ( null === $pickup_raw ) {
		return null;
	}
	$p = wc_get_product( (int) $product_id );

	if (
		$p && method_exists( $p, 'has_time_slots' ) && $p->has_time_slots()
		&& function_exists( 'ovabrw_get_meta_data' )
	) {
		$time_from = sanitize_text_field( (string) ovabrw_get_meta_data( 'ovabrw_time_from', $_POST, '' ) );
		if ( '' !== $time_from ) {
			$pickup_raw .= ' ' . $time_from;
		}
	}
	return $pickup_raw;
}

/**
 * Importe suplemento habitación individual (€). Vacío → 150 €; 0 → desactivado.
 *
 * @param int $product_id
 * @return float
 */
function offitravel_ovabrw_get_single_supplement_amount( $product_id ) {
	$product_id = (int) $product_id;
	if ( $product_id < 1 ) {
		return 150.0;
	}
	$raw = get_post_meta( $product_id, OFFITRAVEL_OVABRW_ROOM_META_SINGLE_SUPPLEMENT, true );
	if ( '' === $raw || null === $raw ) {
		return 150.0;
	}
	return max( 0.0, floatval( wc_format_decimal( str_replace( ',', '.', (string) $raw ) ) ) );
}

/**
 * Suma precios de opción seleccionada en campos CCKF tipo select cuya clave empieza por pack_ (origen tren).
 *
 * @param int   $product_id
 * @param array $cart_item
 * @return float
 */
function offitravel_ovabrw_sum_pack_select_unit_prices( $product_id, $cart_item ) {
	$product_id = (int) $product_id;
	if ( $product_id < 1 || ! function_exists( 'ovabrw_get_list_field_checkout' ) || ! function_exists( 'ovabrw_get_meta_data' ) ) {
		return 0.0;
	}

	$cckf_data = ovabrw_get_meta_data( 'custom_ckf', $cart_item, array() );
	if ( ! is_array( $cckf_data ) ) {
		return 0.0;
	}

	$list = ovabrw_get_list_field_checkout( $product_id );
	if ( ! function_exists( 'ovabrw_array_exists' ) || ! ovabrw_array_exists( $list ) ) {
		return 0.0;
	}

	$sum = 0.0;
	foreach ( $cckf_data as $field_key => $selected_val ) {
		if ( ! is_string( $field_key ) || ! preg_match( '/^pack_/i', $field_key ) ) {
			continue;
		}
		if ( ! isset( $list[ $field_key ] ) || ! is_array( $list[ $field_key ] ) ) {
			continue;
		}
		$fields = $list[ $field_key ];
		if ( ! isset( $fields['type'] ) || 'select' !== $fields['type'] ) {
			continue;
		}

		$opt_keys   = isset( $fields['ova_options_key'] ) ? $fields['ova_options_key'] : array();
		$opt_prices = isset( $fields['ova_options_price'] ) ? $fields['ova_options_price'] : array();
		if ( ! is_array( $opt_keys ) || ! is_array( $opt_prices ) ) {
			continue;
		}

		foreach ( $opt_keys as $k => $opt_id ) {
			if ( (string) $selected_val === (string) $opt_id ) {
				$sum += isset( $opt_prices[ $k ] ) ? (float) $opt_prices[ $k ] : 0.0;
				break;
			}
		}
	}

	return $sum;
}

/**
 * Número de habitaciones con 1 sola persona (suplemento individual).
 *
 * @param int   $product_id
 * @param array $cart_item
 * @return int
 */
function offitravel_ovabrw_count_single_occupied_rooms( $product_id, array $cart_item ) {
	if ( ! offitravel_ovabrw_room_mode_enabled( $product_id ) ) {
		return 0;
	}
	$occ = offitravel_ovabrw_room_get_occupancy_from_context( $cart_item );
	if ( null === $occ ) {
		return 0;
	}
	$n = 0;
	foreach ( $occ['people'] as $p ) {
		if ( 1 === (int) $p ) {
			$n++;
		}
	}
	return $n;
}

/**
 * Sumar al total: (extras pack por viajero) + suplementos habitación individual (prioridad menor que precio fijo por total personas).
 *
 * @param float  $line_total
 * @param int    $product_id
 * @param mixed  $checkin_date
 * @param mixed  $checkout_date
 * @param array  $cart_item
 * @return float
 */
function offitravel_ovabrw_apply_pricing_addons_from_table( $line_total, $product_id, $checkin_date, $checkout_date, $cart_item ) {
	$matrix_checkin_capture  = $checkin_date;
	$matrix_checkout_capture = $checkout_date;
	unset( $checkin_date, $checkout_date );
	$product_id = (int) $product_id;
	if ( $product_id < 1 ) {
		return $line_total;
	}

	$product = wc_get_product( $product_id );
	if ( ! $product || ! $product->is_type( 'ovabrw_car_rental' ) ) {
		return $line_total;
	}

	$adults = 0;
	if ( function_exists( 'ovabrw_get_meta_data' ) ) {
		$adults = (int) ovabrw_get_meta_data( 'ovabrw_adults', $cart_item, 0 );
	}
	if ( $adults < 1 ) {
		return $line_total;
	}

	$extra = 0.0;

	/*
	 * OVA suma el pack una vez en ovabrw_get_price_cckf; debe aplicarse × adultos.
	 */
	$suppress_pack_addon = false;
	if ( function_exists( 'offitravel_ovabrw_matrix_should_suppress_train_pack_addon' ) ) {
		$suppress_pack_addon = offitravel_ovabrw_matrix_should_suppress_train_pack_addon( $product_id, $matrix_checkin_capture, $matrix_checkout_capture, $cart_item );
	}
	$pack_unit = offitravel_ovabrw_sum_pack_select_unit_prices( $product_id, $cart_item );
	if ( ! $suppress_pack_addon && $pack_unit > 0 && $adults > 1 ) {
		$extra += $pack_unit * (float) ( $adults - 1 );
	}

	$singles = offitravel_ovabrw_count_single_occupied_rooms( $product_id, $cart_item );
	if ( $singles > 0 ) {
		$amt = offitravel_ovabrw_get_single_supplement_amount( $product_id );
		if ( $amt > 0 ) {
			$extra += $amt * (float) $singles;
		}
	}

	if ( $extra <= 0 ) {
		return $line_total;
	}

	return round( (float) $line_total + $extra, wc_get_price_decimals() );
}

add_filter( 'ovabrw_get_price_by_guests', 'offitravel_ovabrw_apply_pricing_addons_from_table', 850, 5 );

/**
 * Etiquetas de suplementos (pack por viajeros extra, habitación individual) para carrito y pedidos.
 *
 * @param int   $product_id
 * @param array $cart_item
 * @return array<int, array{name:string,value:string}>
 */
function offitravel_ovabrw_pricing_addon_display_rows( $product_id, array $cart_item ) {
	$product_id = (int) $product_id;
	$out        = array();
	if ( $product_id < 1 || ! function_exists( 'ovabrw_get_meta_data' ) ) {
		return $out;
	}

	$adults = (int) ovabrw_get_meta_data( 'ovabrw_adults', $cart_item, 0 );
	$pack_unit = offitravel_ovabrw_sum_pack_select_unit_prices( $product_id, $cart_item );
	$pick_raw  = ovabrw_get_meta_data( 'ovabrw_pickup_date', $cart_item, '' );
	$drop_raw  = ovabrw_get_meta_data( 'ovabrw_pickoff_date', $cart_item, '' );
	$chk_in    = $pick_raw ? strtotime( (string) $pick_raw ) : 0;
	$chk_out   = $drop_raw ? strtotime( (string) $drop_raw ) : '';
	if ( false === $chk_out ) {
		$chk_out = '';
	}
	if ( offitravel_ovabrw_matrix_should_suppress_train_pack_addon( $product_id, $chk_in > 0 ? $chk_in : $pick_raw, $chk_out, $cart_item ) ) {
		$pack_unit = 0.0;
	}
	if ( $pack_unit > 0 && $adults > 1 ) {
		$amt    = round( $pack_unit * (float) ( $adults - 1 ), wc_get_price_decimals() );
		$out[] = array(
			'name'  => esc_html__( 'Suplemento origen (resto de viajeros)', 'offitravel-ovabrw' ),
			'value' => wp_kses_post( wc_price( $amt ) ),
		);
	}

	$qty_disc      = max( 1, (int) ovabrw_get_meta_data( 'ovabrw_quantity', $cart_item, 1 ) );
	$occ_reduction = function_exists( 'offitravel_ovabrw_room_occ_discount_line_reduction' )
		? offitravel_ovabrw_room_occ_discount_line_reduction( $product_id, $cart_item, $chk_in > 0 ? $chk_in : $pick_raw, $chk_out !== '' ? $chk_out : null )
		: 0.0;
	if ( $occ_reduction > 0 ) {
		$disc_show = round( $occ_reduction * $qty_disc, wc_get_price_decimals() );
		$out[]     = array(
			'name'  => esc_html__( 'Descuentos', 'offitravel-ovabrw' ),
			/* translators: %s formatted price deduction */
			'value' => wp_kses_post( sprintf( __( '− %s', 'offitravel-ovabrw' ), wc_price( $disc_show ) ) ),
		);
	}

	$singles = offitravel_ovabrw_count_single_occupied_rooms( $product_id, $cart_item );
	if ( $singles > 0 ) {
		$amt_e = offitravel_ovabrw_get_single_supplement_amount( $product_id );
		if ( $amt_e > 0 ) {
			$row_amt = round( $amt_e * (float) $singles, wc_get_price_decimals() );
			$lab     = 1 === (int) $singles
				? esc_html__( 'Habitación individual', 'offitravel-ovabrw' )
				: sprintf(
					/* translators: %d number of rooms */
					esc_html__( 'Habitaciones individuales (× %d)', 'offitravel-ovabrw' ),
					(int) $singles
				);
			$out[] = array(
				'name'  => $lab,
				'value' => wp_kses_post( wc_price( $row_amt ) ),
			);
		}
	}

	return $out;
}

/**
 * Sustituye el total de línea OVA por precio fijo por total de personas (+ días de inicio) si aplica.
 *
 * @param float  $line_total
 * @param int    $product_id
 * @param mixed  $checkin_date
 * @param mixed  $checkout_date
 * @param array  $cart_item
 * @return float
 */
function offitravel_ovabrw_filter_price_by_fixed_occupancy( $line_total, $product_id, $checkin_date, $checkout_date, $cart_item ) {
	if ( ! offitravel_ovabrw_room_mode_enabled( $product_id ) || ! offitravel_ovabrw_room_fixed_pricing_enabled( $product_id ) ) {
		return $line_total;
	}

	$rules = offitravel_ovabrw_get_room_fixed_rules( $product_id );
	if ( empty( $rules ) ) {
		return $line_total;
	}

	$total_g = offitravel_ovabrw_fixed_total_guests_for_pricing( $cart_item );
	if ( null === $total_g || $total_g < 1 ) {
		return $line_total;
	}

	$fixed = offitravel_ovabrw_room_match_fixed_price( $product_id, $total_g, $checkin_date );
	if ( null === $fixed ) {
		return $line_total;
	}

	return round( (float) $fixed, wc_get_price_decimals() );
}

add_filter( 'ovabrw_get_price_by_guests', 'offitravel_ovabrw_filter_price_by_fixed_occupancy', 999, 5 );

/**
 * Acordeón admin (tras contacto).
 *
 * @param int $post_id
 */
function offitravel_ovabrw_render_room_mode_accordion( $post_id ) {
	$post_id = (int) $post_id;
	if ( ! $post_id ) {
		return;
	}

	$product = wc_get_product( $post_id );
	if ( ! $product || ! $product->is_type( 'ovabrw_car_rental' ) ) {
		return;
	}

	if ( ! function_exists( 'woocommerce_wp_text_input' ) ) {
		include_once WC()->plugin_path() . 'includes/admin/wc-meta-box-functions.php';
	}

	$s     = offitravel_ovabrw_get_room_settings( $post_id );
	$en    = $s['enabled'] ? 'yes' : 'no';
	$max_r = $s['max_rooms'];
	$max_p = $s['max_per_room'];

	$fixed_en = offitravel_ovabrw_room_fixed_pricing_enabled( $post_id ) ? 'yes' : 'no';
	$fixed_req = offitravel_ovabrw_room_fixed_require_rule( $post_id ) ? 'yes' : 'no';
	$fixed_rules = offitravel_ovabrw_get_room_fixed_rules( $post_id );
	if ( empty( $fixed_rules ) ) {
		$fixed_rules = array(
			array(
				'total_guests' => 1,
				'price'        => '',
			),
		);
	}

	$occ_disc_en = offitravel_ovabrw_room_occ_discount_enabled( $post_id ) ? 'yes' : 'no';

	$disc_b1_csv = trim( (string) get_post_meta( $post_id, OFFITRAVEL_OVABRW_ROOM_META_DISCOUNT_BAND1_DAYS, true ) );
	$disc_b2_csv = trim( (string) get_post_meta( $post_id, OFFITRAVEL_OVABRW_ROOM_META_DISCOUNT_BAND2_DAYS, true ) );
	$disc_b1_t   = get_post_meta( $post_id, OFFITRAVEL_OVABRW_ROOM_META_DISCOUNT_B1_TRIPLE_PP, true );
	$disc_b1_q   = get_post_meta( $post_id, OFFITRAVEL_OVABRW_ROOM_META_DISCOUNT_B1_QUAD_PP, true );
	$disc_b2_t   = get_post_meta( $post_id, OFFITRAVEL_OVABRW_ROOM_META_DISCOUNT_B2_TRIPLE_PP, true );
	$disc_b2_q   = get_post_meta( $post_id, OFFITRAVEL_OVABRW_ROOM_META_DISCOUNT_B2_QUAD_PP, true );
	if ( ! offitravel_ovabrw_room_occ_discount_has_band_amount_meta( $post_id ) ) {
		$leg_t = get_post_meta( $post_id, OFFITRAVEL_OVABRW_ROOM_META_DISCOUNT_TRIPLE_PP, true );
		$leg_q = get_post_meta( $post_id, OFFITRAVEL_OVABRW_ROOM_META_DISCOUNT_QUAD_PP, true );
		if ( '' !== $leg_t && null !== $leg_t ) {
			$disc_b1_t = $leg_t;
		}
		if ( '' !== $leg_q && null !== $leg_q ) {
			$disc_b1_q = $leg_q;
		}
	}
	$fmt_disc = static function ( $v ) {
		return is_numeric( $v ) || ( '' !== $v && '0' === (string) $v ) ? wc_format_decimal( (string) $v ) : '';
	};

	$mtx_en     = offitravel_ovabrw_matrix_cckf_enabled( $post_id ) ? 'yes' : 'no';
	$mtx_fkey   = (string) get_post_meta( $post_id, OFFITRAVEL_OVABRW_MATRIX_META_FIELD_KEY, true );
	$mtx_b1_csv = trim( (string) get_post_meta( $post_id, OFFITRAVEL_OVABRW_MATRIX_META_BAND1_DAYS, true ) );
	$mtx_b2_csv = trim( (string) get_post_meta( $post_id, OFFITRAVEL_OVABRW_MATRIX_META_BAND2_DAYS, true ) );

	$b1_saved = offitravel_ovabrw_matrix_get_band_option_map( $post_id, 1 );
	$b2_saved = offitravel_ovabrw_matrix_get_band_option_map( $post_id, 2 );
	if ( empty( $b1_saved ) ) {
		$b1_saved = array( '' => '' );
	}
	if ( empty( $b2_saved ) ) {
		$b2_saved = array( '' => '' );
	}

	$mtx_cckf_opts       = offitravel_ovabrw_matrix_admin_cckf_select_options( $post_id, $mtx_fkey );
	$mtx_use_select      = ! empty( $mtx_cckf_opts );
	$mtx_tpl_select_opts = $mtx_use_select ? offitravel_ovabrw_matrix_admin_option_select_options_html( $mtx_cckf_opts, true ) : '';

	wp_nonce_field( 'offitravel_ovabrw_room_mode_save', 'offitravel_ovabrw_room_mode_nonce' );
	?>
	<div class="ovabrw-advanced-settings offitravel-ovabrw-room-mode-accordion">
		<div class="advanced-header">
			<h3 class="advanced-label"><?php esc_html_e( 'Habitaciones', 'offitravel-ovabrw' ); ?></h3>
			<span aria-hidden="true" class="dashicons dashicons-arrow-up"></span>
			<span aria-hidden="true" class="dashicons dashicons-arrow-down"></span>
		</div>
		<div class="advanced-content">
			<style id="offitravel-ovabrw-room-admin-form-field-reset">
				/*
				 * WooCommerce product data usa .woocommerce_options_panel label { float:left; margin-left:-150px; }
				 * En este acordeón (Contacto/OVA) el panel no tiene la misma rejilla → el label tapa el input.
				 * Solo etiquetas «externas» (for=); no alteramos labels que envuelven checkbox.
				 */
				.offitravel-ovabrw-room-mode-accordion .form-field > label[for] {
					float: none !important;
					width: auto !important;
					max-width: 100%;
					margin: 0 0 6px 0 !important;
					padding: 0 !important;
					display: block;
					line-height: 1.4;
				}
				.offitravel-ovabrw-room-mode-accordion .form-field > input:not([type="checkbox"]):not([type="radio"]),
				.offitravel-ovabrw-room-mode-accordion .form-field > select {
					float: none !important;
					clear: both;
					display: inline-block;
					margin-left: 0 !important;
				}
				.offitravel-ovabrw-room-mode-accordion .form-field .description {
					float: none !important;
					clear: both;
					display: block;
					padding-top: 4px;
				}
				.offitravel-ovabrw-room-mode-accordion .woocommerce-help-tip {
					float: none;
					vertical-align: middle;
					margin-top: -2px;
				}
			</style>
			<p class="description" style="margin-top:0;float:none;">
				<?php esc_html_e( 'Si está activo, en la ficha el cliente elige cuántas habitaciones y cuántas personas en cada una. El precio se calcula por el total de personas (como con Adultos).', 'offitravel-ovabrw' ); ?>
			</p>
			<p class="form-field">
				<label style="width:100%;">
					<input type="checkbox" name="offitravel_ovabrw_room_mode_enabled" value="yes" <?php checked( $en, 'yes' ); ?> />
					<?php esc_html_e( 'Usar selector de habitaciones en lugar del contador de adultos/niños/bebés', 'offitravel-ovabrw' ); ?>
				</label>
			</p>
			<?php
			woocommerce_wp_text_input(
				array(
					'id'          => 'offitravel_ovabrw_room_max_rooms',
					'name'        => 'offitravel_ovabrw_room_max_rooms',
					'class'       => 'short',
					'label'       => esc_html__( 'Máximo de habitaciones', 'offitravel-ovabrw' ),
					'type'        => 'number',
					'value'       => $max_r,
					'custom_attributes' => array(
						'min'  => '1',
						'max'  => '50',
						'step' => '1',
					),
					'description' => esc_html__( 'El desplegable en la ficha irá de 1 hasta este número.', 'offitravel-ovabrw' ),
				)
			);
			woocommerce_wp_text_input(
				array(
					'id'          => 'offitravel_ovabrw_room_max_per_room',
					'name'        => 'offitravel_ovabrw_room_max_per_room',
					'class'       => 'short',
					'label'       => esc_html__( 'Máximo de personas por habitación', 'offitravel-ovabrw' ),
					'type'        => 'number',
					'value'       => $max_p,
					'custom_attributes' => array(
						'min'  => '1',
						'max'  => '50',
						'step' => '1',
					),
					'description' => esc_html__( 'Cada habitación tendrá un desplegable de 1 hasta este número.', 'offitravel-ovabrw' ),
				)
			);
			$s_single = get_post_meta( $post_id, OFFITRAVEL_OVABRW_ROOM_META_SINGLE_SUPPLEMENT, true );
			woocommerce_wp_text_input(
				array(
					'id'          => 'offitravel_ovabrw_single_supplement',
					'name'        => 'offitravel_ovabrw_single_supplement',
					'class'       => 'short wc_input_decimal',
					'label'       => esc_html__( 'Suplemento habitación individual (€)', 'offitravel-ovabrw' ),
					'placeholder' => '150',
					'description' => esc_html__( 'Se aplica una vez por habitación con solo 1 persona. Vacío = 150 €. Pon 0 para desactivar.', 'offitravel-ovabrw' ),
					'type'        => 'text',
					'value'       => is_numeric( $s_single ) || ( '' !== $s_single && '0' === (string) $s_single ) ? wc_format_decimal( (string) $s_single ) : '',
				)
			);
			?>
			<hr style="margin:16px 0;border:none;border-top:1px solid #ddd;" />
			<p class="form-field">
				<label style="width:100%;">
					<input type="checkbox" name="offitravel_ovabrw_room_fixed_pricing" value="yes" <?php checked( $fixed_en, 'yes' ); ?> />
					<?php esc_html_e( 'Precio total fijo por número de personas (según suma de ocupación; días de inicio opcional)', 'offitravel-ovabrw' ); ?>
				</label>
			</p>
			<p class="description" style="margin-top:0;">
				<?php esc_html_e( 'Cada fila aplica a un total de adultos (suma de personas en todas las habitaciones). Da igual el reparto entre habitaciones si el total coincide. Si no hay regla válida para ese total y fecha, se usa el precio normal por persona.', 'offitravel-ovabrw' ); ?>
			</p>
			<p class="description" style="margin-bottom: 12px;">
				<?php esc_html_e( '«Personas (total)»: número entero de huéspedes adultos (ej. 2, 3).', 'offitravel-ovabrw' ); ?>
			</p>
			<p class="description" style="margin-bottom: 12px;">
				<?php esc_html_e( '«Días inicio»: días del calendario de la fecha de inicio del tour donde aplica ese precio. Números 0 domingo … 6 sábado (como en PHP date). Lista separada por comas , o ; ejemplo 2, 3, 4 para mar–mié-jue y 0 para domingo incluidos. Vacío = todos los días.', 'offitravel-ovabrw' ); ?>
			</p>
			<p class="form-field" style="display: none;">
				<label style="width:100%;">
					<input type="checkbox" name="offitravel_ovabrw_room_fixed_require" value="yes" <?php checked( $fixed_req, 'yes' ); ?> />
					<?php esc_html_e( 'Obligar a que el número total de personas coincida con una tarifa (si hay reglas definidas, rechazar otros totales)', 'offitravel-ovabrw' ); ?>
				</label>
			</p>
			<div class="offitravel-fixed-room-rules-wrap">
				<table class="widefat" style="max-width:98%;margin-inline:auto;">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Personas (total)', 'offitravel-ovabrw' ); ?></th>
							<th><?php esc_html_e( 'Precio total (€)', 'offitravel-ovabrw' ); ?></th>
							<th><?php esc_html_e( 'Días inicio', 'offitravel-ovabrw' ); ?></th>
							<th></th>
						</tr>
					</thead>
					<tbody id="offitravel-fixed-room-rules-body">
						<?php
						foreach ( $fixed_rules as $fr ) :
							$total_i = isset( $fr['total_guests'] ) ? (int) $fr['total_guests'] : 1;
							$price_i = isset( $fr['price'] ) ? $fr['price'] : '';
							$wd_i    = '';
							if ( ! empty( $fr['weekdays'] ) && is_array( $fr['weekdays'] ) ) {
								$wd_i = implode( ', ', array_map( 'absint', $fr['weekdays'] ) );
							}
							?>
							<tr class="offitravel-fixed-room-rule-row">
								<td>
									<input type="number" name="offitravel_fixed_rule_total_guests[]" min="1" max="200" step="1" value="<?php echo esc_attr( $total_i ); ?>" class="small-text" />
								</td>
								<td>
									<input type="text" name="offitravel_fixed_rule_price[]" value="<?php echo esc_attr( $price_i ); ?>" class="small-text" placeholder="400" />
								</td>
								<td>
									<input type="text" name="offitravel_fixed_rule_weekdays[]" value="<?php echo esc_attr( $wd_i ); ?>" class="regular-text" placeholder="" title="<?php echo esc_attr__( 'Ej. 0,2,3,4,6 para dom y mar-dom (vacío = todos)', 'offitravel-ovabrw' ); ?>" autocomplete="off" />
								</td>
								<td>
									<button type="button" class="button offitravel-fixed-rule-remove" aria-label="<?php esc_attr_e( 'Quitar fila', 'offitravel-ovabrw' ); ?>">×</button>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<p>
					<button type="button" class="button" id="offitravel-fixed-rule-add"><?php esc_html_e( 'Añadir tarifa', 'offitravel-ovabrw' ); ?></button>
				</p>
			</div>
			<script type="text/template" id="offitravel-fixed-rule-tpl">
				<tr class="offitravel-fixed-room-rule-row">
					<td><input type="number" name="offitravel_fixed_rule_total_guests[]" min="1" max="200" step="1" value="1" class="small-text" /></td>
					<td><input type="text" name="offitravel_fixed_rule_price[]" value="" class="small-text" placeholder="400" /></td>
					<td><input type="text" name="offitravel_fixed_rule_weekdays[]" value="" class="regular-text" autocomplete="off" /></td>
					<td><button type="button" class="button offitravel-fixed-rule-remove" aria-label="<?php esc_attr_e( 'Quitar fila', 'offitravel-ovabrw' ); ?>">×</button></td>
				</tr>
			</script>
			<script>
			(function(){
				var tbody = document.getElementById('offitravel-fixed-room-rules-body');
				var tpl = document.getElementById('offitravel-fixed-rule-tpl');
				var addBtn = document.getElementById('offitravel-fixed-rule-add');
				if (addBtn && tpl && tbody) {
					addBtn.addEventListener('click', function(){
						/* No usar innerHTML en un <div> con <tr>: el navegador descarta etiquetas inválidas y solo queda un <td> suelto. */
						tbody.insertAdjacentHTML('beforeend', tpl.textContent ? tpl.textContent.trim() : tpl.innerHTML.trim());
					});
				}
				if (tbody) {
					tbody.addEventListener('click', function(e){
						if (e.target && e.target.classList.contains('offitravel-fixed-rule-remove')) {
							var row = e.target.closest('tr');
							if (row && tbody.querySelectorAll('tr').length > 1) row.remove();
						}
					});
				}
			})();
			</script>

			<hr style="margin:20px 0;border:none;border-top:1px solid #ddd;" />
			<h4 style="margin:12px 0 8px;"><?php esc_html_e( 'PVP por origen — dos rejillas (días de banda)', 'offitravel-ovabrw' ); ?></h4>
			<p class="description" style="margin-top:0;">
				<?php esc_html_e( 'Cuando está activo, el PVP cerrado por persona según origen viene de estas tablas: banda 1 (por defecto dom + mar–jue si dejas los días vacíos) y banda 2 (vie–sáb por defecto). La clave CCKF debe ser el mismo slug del campo OVA de origen/tren.', 'offitravel-ovabrw' ); ?>
			</p>
			<p class="description" style="margin-bottom:12px;">
				<?php
				if ( $mtx_use_select ) {
					esc_html_e( 'Cada fila elige la opción del select OVA (se guarda el ID interno). Si la primera opción es «Sin Tren» y en la rejilla usas la clave sin_tren, el frontal sigue resolviendo el alias según la lógica del plugin. Solo adultos sin niños/bebés.', 'offitravel-ovabrw' );
				} else {
					esc_html_e( 'Indica el slug del campo CCKF arriba; si ese campo es un select en este producto, verás desplegables con la etiqueta. Si no: escribe a mano el mismo ID que en OVA. Solo adultos sin niños/bebés. Se resta la tarifa CCKF de la opción para no duplicar tren; se omite suplemento pack_* por otros adultos cuando aplica la matriz.', 'offitravel-ovabrw' );
				}
				?>
			</p>
			<p class="form-field">
				<label style="width:100%;">
					<input type="checkbox" name="offitravel_ovabrw_matrix_cckf_enabled" value="yes" <?php checked( $mtx_en, 'yes' ); ?> />
					<?php esc_html_e( 'Usar dos rejillas CCKF (PVP persona por fecha de banda y origen)', 'offitravel-ovabrw' ); ?>
				</label>
			</p>
			<?php
			woocommerce_wp_text_input(
				array(
					'id'          => 'offitravel_ovabrw_matrix_cckf_field',
					'name'        => 'offitravel_ovabrw_matrix_cckf_field',
					'label'       => esc_html__( 'Slug del campo CCKF', 'offitravel-ovabrw' ),
					'placeholder' => 'rey_leon_1',
					'description' => esc_html__( 'Misma «key» que en configuración checkout OVA.', 'offitravel-ovabrw' ),
					'type'        => 'text',
					'value'       => $mtx_fkey,
				)
			);
			woocommerce_wp_text_input(
				array(
					'id'          => 'offitravel_ovabrw_matrix_band1_days',
					'name'        => 'offitravel_ovabrw_matrix_band1_days',
					'label'       => esc_html__( 'Días banda 1 (0 dom … 6 sáb)', 'offitravel-ovabrw' ),
					'placeholder' => '0,2,3,4',
					'description' => esc_html__( 'Vacío = por defecto 0, 2, 3, 4 (domingo y martes–jueves).', 'offitravel-ovabrw' ),
					'type'        => 'text',
					'value'       => esc_attr( $mtx_b1_csv ),
				)
			);
			woocommerce_wp_text_input(
				array(
					'id'          => 'offitravel_ovabrw_matrix_band2_days',
					'name'        => 'offitravel_ovabrw_matrix_band2_days',
					'label'       => esc_html__( 'Días banda 2', 'offitravel-ovabrw' ),
					'placeholder' => '5,6',
					'description' => esc_html__( 'Vacío = por defecto 5, 6 (viernes–sábado). Si el día del inicio no entra en banda 1 ni 2, el precio OVA habitual se mantiene.', 'offitravel-ovabrw' ),
					'type'        => 'text',
					'value'       => esc_attr( $mtx_b2_csv ),
				)
			);
			?>
			<p class="description" style="margin:8px 0 6px;"><strong><?php esc_html_e( 'Banda 1 — Opción → PVP (€/persona)', 'offitravel-ovabrw' ); ?></strong></p>
			<table class="widefat" style="max-width:98%;margin-inline:auto;">
				<thead>
					<tr>
						<th><?php echo $mtx_use_select ? esc_html__( 'Opción (etiqueta; value = ID OVA)', 'offitravel-ovabrw' ) : esc_html__( 'Clave opción', 'offitravel-ovabrw' ); ?></th>
						<th><?php esc_html_e( 'PVP €', 'offitravel-ovabrw' ); ?></th>
						<th></th>
					</tr>
				</thead>
				<tbody id="offitravel-matrix-band1-body">
					<?php foreach ( $b1_saved as $ok => $pv ) : ?>
						<tr class="offitravel-matrix-band1-row">
							<td><?php offitravel_ovabrw_matrix_admin_render_option_control( 'offitravel_matrix_band1_opt[]', $mtx_cckf_opts, trim( is_string( $ok ) ? $ok : (string) $ok ), $mtx_use_select ); ?></td>
							<td><input type="text" name="offitravel_matrix_band1_price[]" value="<?php echo esc_attr( null !== $pv && '' !== (string) $pv ? wc_format_decimal( (string) $pv ) : '' ); ?>" class="small-text wc_input_decimal" autocomplete="off" /></td>
							<td><button type="button" class="button offitravel-matrix-b1-remove" aria-label="<?php esc_attr_e( 'Quitar fila', 'offitravel-ovabrw' ); ?>">×</button></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<p><button type="button" class="button" id="offitravel-matrix-band1-add"><?php esc_html_e( 'Añadir fila banda 1', 'offitravel-ovabrw' ); ?></button></p>
			<script type="text/template" id="offitravel-matrix-band1-tpl">
				<tr class="offitravel-matrix-band1-row">
					<?php if ( $mtx_use_select ) : ?>
					<td><select name="offitravel_matrix_band1_opt[]" class="regular-text offitravel-matrix-cckf-opt"><?php echo $mtx_tpl_select_opts; ?></select></td>
					<?php else : ?>
					<td><input type="text" name="offitravel_matrix_band1_opt[]" value="" class="regular-text" placeholder="" autocomplete="off" /></td>
					<?php endif; ?>
					<td><input type="text" name="offitravel_matrix_band1_price[]" value="" class="small-text wc_input_decimal" autocomplete="off" /></td>
					<td><button type="button" class="button offitravel-matrix-b1-remove" aria-label="<?php esc_attr_e( 'Quitar fila', 'offitravel-ovabrw' ); ?>">×</button></td>
				</tr>
			</script>

			<p class="description" style="margin:16px 0 6px;"><strong><?php esc_html_e( 'Banda 2 — Opción → PVP (€/persona)', 'offitravel-ovabrw' ); ?></strong></p>
			<table class="widefat" style="max-width:98%;margin-inline:auto;">
				<thead>
					<tr>
						<th><?php echo $mtx_use_select ? esc_html__( 'Opción (etiqueta; value = ID OVA)', 'offitravel-ovabrw' ) : esc_html__( 'Clave opción', 'offitravel-ovabrw' ); ?></th>
						<th><?php esc_html_e( 'PVP €', 'offitravel-ovabrw' ); ?></th>
						<th></th>
					</tr>
				</thead>
				<tbody id="offitravel-matrix-band2-body">
					<?php foreach ( $b2_saved as $ok => $pv ) : ?>
						<tr class="offitravel-matrix-band2-row">
							<td><?php offitravel_ovabrw_matrix_admin_render_option_control( 'offitravel_matrix_band2_opt[]', $mtx_cckf_opts, trim( is_string( $ok ) ? $ok : (string) $ok ), $mtx_use_select ); ?></td>
							<td><input type="text" name="offitravel_matrix_band2_price[]" value="<?php echo esc_attr( null !== $pv && '' !== (string) $pv ? wc_format_decimal( (string) $pv ) : '' ); ?>" class="small-text wc_input_decimal" autocomplete="off" /></td>
							<td><button type="button" class="button offitravel-matrix-b2-remove" aria-label="<?php esc_attr_e( 'Quitar fila', 'offitravel-ovabrw' ); ?>">×</button></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<p><button type="button" class="button" id="offitravel-matrix-band2-add"><?php esc_html_e( 'Añadir fila banda 2', 'offitravel-ovabrw' ); ?></button></p>
			<script type="text/template" id="offitravel-matrix-band2-tpl">
				<tr class="offitravel-matrix-band2-row">
					<?php if ( $mtx_use_select ) : ?>
					<td><select name="offitravel_matrix_band2_opt[]" class="regular-text offitravel-matrix-cckf-opt"><?php echo $mtx_tpl_select_opts; ?></select></td>
					<?php else : ?>
					<td><input type="text" name="offitravel_matrix_band2_opt[]" value="" class="regular-text" autocomplete="off" /></td>
					<?php endif; ?>
					<td><input type="text" name="offitravel_matrix_band2_price[]" value="" class="small-text wc_input_decimal" autocomplete="off" /></td>
					<td><button type="button" class="button offitravel-matrix-b2-remove" aria-label="<?php esc_attr_e( 'Quitar fila', 'offitravel-ovabrw' ); ?>">×</button></td>
				</tr>
			</script>
			<script>
			(function(){
				function wire(tableId, addId, tplId, removeCls) {
					var tbody = document.getElementById(tableId);
					var tplEl = document.getElementById(tplId);
					var addBtn = document.getElementById(addId);
					if (!tbody || !tplEl || !addBtn) return;
					addBtn.addEventListener('click', function(){
						tbody.insertAdjacentHTML('beforeend', tplEl.textContent ? tplEl.textContent.trim() : tplEl.innerHTML.trim());
					});
					tbody.addEventListener('click', function(e){
						if (!e.target || !e.target.classList.contains(removeCls)) return;
						var row = e.target.closest('tr');
						if (!row || tbody.querySelectorAll('tr').length < 2) return;
						row.remove();
					});
				}
				wire('offitravel-matrix-band1-body','offitravel-matrix-band1-add','offitravel-matrix-band1-tpl','offitravel-matrix-b1-remove');
				wire('offitravel-matrix-band2-body','offitravel-matrix-band2-add','offitravel-matrix-band2-tpl','offitravel-matrix-b2-remove');
			})();
			</script>

			<hr style="margin:20px 0;border:none;border-top:1px solid #ddd;" />
			<h4 style="margin:12px 0 8px;"><?php esc_html_e( 'Descuentos', 'offitravel-ovabrw' ); ?></h4>
			<p class="description" style="margin-top:0;">
				<?php esc_html_e( 'Referencia tabla comercial: el importe «menos por persona» en triple y cuádruple depende del día de inicio del tour (dos bandas, como las rejillas PVP). Solo con modo habitaciones; 3 adultos = triple, ≥4 = cuádruple; doble no reduce.', 'offitravel-ovabrw' ); ?>
			</p>
			<p class="form-field">
				<label style="width:100%;">
					<input type="checkbox" name="offitravel_ovabrw_room_occ_discount_enabled" value="yes" <?php checked( $occ_disc_en, 'yes' ); ?> />
					<?php esc_html_e( 'Aplicar este descuento en el precio por reserva', 'offitravel-ovabrw' ); ?>
				</label>
			</p>
			<p class="description" style="margin-bottom:12px;">
				<?php esc_html_e( 'Por defecto: banda 1 = domingo y martes–jueves (5 € / 12 €); banda 2 = viernes–sábado (10 € / 31 €). Si el día de inicio no cae en ninguna banda, no hay descuento. Valores vacíos usan esos defaults.', 'offitravel-ovabrw' ); ?>
				
			</p>
			<?php
			woocommerce_wp_text_input(
				array(
					'id'          => 'offitravel_ovabrw_room_disc_band1_days',
					'name'        => 'offitravel_ovabrw_room_disc_band1_days',
					'label'       => esc_html__( 'Descuento — días banda 1 (0 dom … 6 sáb)', 'offitravel-ovabrw' ),
					'placeholder' => '0,2,3,4',
					'description' => esc_html__( 'Vacío = por defecto 0, 2, 3, 4.', 'offitravel-ovabrw' ),
					'type'        => 'text',
					'value'       => esc_attr( $disc_b1_csv ),
				)
			);
			woocommerce_wp_text_input(
				array(
					'id'          => 'offitravel_ovabrw_room_disc_b1_triple',
					'name'        => 'offitravel_ovabrw_room_disc_b1_triple',
					'class'       => 'short wc_input_decimal',
					'label'       => esc_html__( 'Banda 1 — € menos/pax (triple)', 'offitravel-ovabrw' ),
					'placeholder' => '5',
					'description' => esc_html__( 'Vacío → 5 €. Se multiplica × 3 por habitación de 3 adultos.', 'offitravel-ovabrw' ),
					'type'        => 'text',
					'value'       => $fmt_disc( $disc_b1_t ),
				)
			);
			woocommerce_wp_text_input(
				array(
					'id'          => 'offitravel_ovabrw_room_disc_b1_quad',
					'name'        => 'offitravel_ovabrw_room_disc_b1_quad',
					'class'       => 'short wc_input_decimal',
					'label'       => esc_html__( 'Banda 1 — € menos/pax (cuádruple)', 'offitravel-ovabrw' ),
					'placeholder' => '12',
					'description' => esc_html__( 'Vacío → 12 €. Habitación con 4+ adultos: × n personas.', 'offitravel-ovabrw' ),
					'type'        => 'text',
					'value'       => $fmt_disc( $disc_b1_q ),
				)
			);
			woocommerce_wp_text_input(
				array(
					'id'          => 'offitravel_ovabrw_room_disc_band2_days',
					'name'        => 'offitravel_ovabrw_room_disc_band2_days',
					'label'       => esc_html__( 'Descuento — días banda 2', 'offitravel-ovabrw' ),
					'placeholder' => '5,6',
					'description' => esc_html__( 'Vacío = por defecto 5, 6.', 'offitravel-ovabrw' ),
					'type'        => 'text',
					'value'       => esc_attr( $disc_b2_csv ),
				)
			);
			woocommerce_wp_text_input(
				array(
					'id'          => 'offitravel_ovabrw_room_disc_b2_triple',
					'name'        => 'offitravel_ovabrw_room_disc_b2_triple',
					'class'       => 'short wc_input_decimal',
					'label'       => esc_html__( 'Banda 2 — € menos/pax (triple)', 'offitravel-ovabrw' ),
					'placeholder' => '10',
					'description' => esc_html__( 'Vacío → 10 €.', 'offitravel-ovabrw' ),
					'type'        => 'text',
					'value'       => $fmt_disc( $disc_b2_t ),
				)
			);
			woocommerce_wp_text_input(
				array(
					'id'          => 'offitravel_ovabrw_room_disc_b2_quad',
					'name'        => 'offitravel_ovabrw_room_disc_b2_quad',
					'class'       => 'short wc_input_decimal',
					'label'       => esc_html__( 'Banda 2 — € menos/pax (cuádruple)', 'offitravel-ovabrw' ),
					'placeholder' => '31',
					'description' => esc_html__( 'Vacío → 31 €.', 'offitravel-ovabrw' ),
					'type'        => 'text',
					'value'       => $fmt_disc( $disc_b2_q ),
				)
			);
			?>
		</div>
	</div>
	<?php
}

/**
 * Guardar metas habitaciones.
 *
 * @param int     $post_id
 * @param WP_Post $post
 */
function offitravel_ovabrw_save_room_mode_meta( $post_id, $post ) {
	if (
		! isset( $_POST['offitravel_ovabrw_room_mode_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['offitravel_ovabrw_room_mode_nonce'] ) ), 'offitravel_ovabrw_room_mode_save' )
	) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( 'product' !== $post->post_type ) {
		return;
	}

	$enabled = isset( $_POST['offitravel_ovabrw_room_mode_enabled'] ) && 'yes' === $_POST['offitravel_ovabrw_room_mode_enabled'] ? 'yes' : 'no';

	$max_rooms = isset( $_POST['offitravel_ovabrw_room_max_rooms'] ) ? absint( wp_unslash( $_POST['offitravel_ovabrw_room_max_rooms'] ) ) : 10;
	$max_rooms = min( 50, max( 1, $max_rooms ) );

	$max_per = isset( $_POST['offitravel_ovabrw_room_max_per_room'] ) ? absint( wp_unslash( $_POST['offitravel_ovabrw_room_max_per_room'] ) ) : 4;
	$max_per = min( 50, max( 1, $max_per ) );

	update_post_meta( $post_id, OFFITRAVEL_OVABRW_ROOM_META_ENABLED, $enabled );
	update_post_meta( $post_id, OFFITRAVEL_OVABRW_ROOM_META_MAX_ROOMS, $max_rooms );
	update_post_meta( $post_id, OFFITRAVEL_OVABRW_ROOM_META_MAX_PER_ROOM, $max_per );

	if ( isset( $_POST['offitravel_ovabrw_single_supplement'] ) ) {
		$raw_sg = sanitize_text_field( wp_unslash( $_POST['offitravel_ovabrw_single_supplement'] ) );
		if ( '' === trim( $raw_sg ) ) {
			delete_post_meta( $post_id, OFFITRAVEL_OVABRW_ROOM_META_SINGLE_SUPPLEMENT );
		} else {
			$dec_sg = wc_format_decimal( str_replace( ',', '.', $raw_sg ) );
			update_post_meta( $post_id, OFFITRAVEL_OVABRW_ROOM_META_SINGLE_SUPPLEMENT, is_numeric( $dec_sg ) ? $dec_sg : '0' );
		}
	}

	$fixed_pricing = isset( $_POST['offitravel_ovabrw_room_fixed_pricing'] ) && 'yes' === $_POST['offitravel_ovabrw_room_fixed_pricing'] ? 'yes' : 'no';
	$fixed_require = isset( $_POST['offitravel_ovabrw_room_fixed_require'] ) && 'yes' === $_POST['offitravel_ovabrw_room_fixed_require'] ? 'yes' : 'no';
	update_post_meta( $post_id, OFFITRAVEL_OVABRW_ROOM_META_FIXED_PRICING, $fixed_pricing );
	update_post_meta( $post_id, OFFITRAVEL_OVABRW_ROOM_META_FIXED_REQUIRE, $fixed_require );

	$rule_guest_totals = isset( $_POST['offitravel_fixed_rule_total_guests'] ) ? wp_unslash( $_POST['offitravel_fixed_rule_total_guests'] ) : array();
	$rule_prices       = isset( $_POST['offitravel_fixed_rule_price'] ) ? wp_unslash( $_POST['offitravel_fixed_rule_price'] ) : array();
	$rule_weekdays     = isset( $_POST['offitravel_fixed_rule_weekdays'] ) ? wp_unslash( $_POST['offitravel_fixed_rule_weekdays'] ) : array();
	if ( ! is_array( $rule_guest_totals ) ) {
		$rule_guest_totals = array();
	}
	if ( ! is_array( $rule_weekdays ) ) {
		$rule_weekdays = array();
	}

	$parsed_rules = array();
	if ( is_array( $rule_prices ) ) {
		$max_i = max( count( $rule_guest_totals ), count( $rule_prices ), count( $rule_weekdays ) );
		for ( $i = 0; $i < $max_i; $i++ ) {
			$r_total = isset( $rule_guest_totals[ $i ] ) ? absint( $rule_guest_totals[ $i ] ) : 0;
			$r_price = isset( $rule_prices[ $i ] ) ? wc_format_decimal( str_replace( ',', '.', (string) $rule_prices[ $i ] ) ) : '';
			$r_price = ( '' === $r_price || null === $r_price ) ? 0 : floatval( $r_price );

			if ( $r_total < 1 || $r_price < 0 ) {
				continue;
			}

			$wd_raw         = isset( $rule_weekdays[ $i ] ) ? trim( (string) $rule_weekdays[ $i ] ) : '';
			$wd_parts       = preg_split( '/[\s,;]+/', $wd_raw, -1, PREG_SPLIT_NO_EMPTY );
			$saved_weekdays = array();
			foreach ( $wd_parts as $wk ) {
				$wk_int = absint( $wk );
				if ( $wk_int <= 6 ) {
					$saved_weekdays[] = $wk_int;
				}
			}
			$saved_weekdays = array_values( array_unique( $saved_weekdays, SORT_NUMERIC ) );

			$row = array(
				'total_guests' => $r_total,
				'price'        => $r_price,
			);
			if ( ! empty( $saved_weekdays ) ) {
				$row['weekdays'] = $saved_weekdays;
			}
			$parsed_rules[] = $row;
		}
	}

	update_post_meta( $post_id, OFFITRAVEL_OVABRW_ROOM_META_FIXED_RULES, $parsed_rules );

	$occ_disc_chk = isset( $_POST['offitravel_ovabrw_room_occ_discount_enabled'] ) && 'yes' === $_POST['offitravel_ovabrw_room_occ_discount_enabled'] ? 'yes' : 'no';
	update_post_meta( $post_id, OFFITRAVEL_OVABRW_ROOM_META_OCC_DISCOUNT_ENABLED, $occ_disc_chk );

	delete_post_meta( $post_id, OFFITRAVEL_OVABRW_ROOM_META_DISCOUNT_TRIPLE_PP );
	delete_post_meta( $post_id, OFFITRAVEL_OVABRW_ROOM_META_DISCOUNT_QUAD_PP );

	if ( isset( $_POST['offitravel_ovabrw_room_disc_band1_days'] ) ) {
		$d1 = sanitize_text_field( wp_unslash( $_POST['offitravel_ovabrw_room_disc_band1_days'] ) );
		if ( '' === trim( $d1 ) ) {
			delete_post_meta( $post_id, OFFITRAVEL_OVABRW_ROOM_META_DISCOUNT_BAND1_DAYS );
		} else {
			update_post_meta( $post_id, OFFITRAVEL_OVABRW_ROOM_META_DISCOUNT_BAND1_DAYS, trim( preg_replace( '/[^\d,;\s\-]/', '', $d1 ) ) );
		}
	}
	if ( isset( $_POST['offitravel_ovabrw_room_disc_band2_days'] ) ) {
		$d2 = sanitize_text_field( wp_unslash( $_POST['offitravel_ovabrw_room_disc_band2_days'] ) );
		if ( '' === trim( $d2 ) ) {
			delete_post_meta( $post_id, OFFITRAVEL_OVABRW_ROOM_META_DISCOUNT_BAND2_DAYS );
		} else {
			update_post_meta( $post_id, OFFITRAVEL_OVABRW_ROOM_META_DISCOUNT_BAND2_DAYS, trim( preg_replace( '/[^\d,;\s\-]/', '', $d2 ) ) );
		}
	}

	$save_occ_disc_amt = static function ( $post_id_inner, $post_key, $meta_key ) {
		if ( ! isset( $_POST[ $post_key ] ) ) {
			return;
		}
		$raw = sanitize_text_field( wp_unslash( $_POST[ $post_key ] ) );
		if ( '' === trim( $raw ) ) {
			delete_post_meta( $post_id_inner, $meta_key );
			return;
		}
		$dec = wc_format_decimal( str_replace( ',', '.', $raw ) );
		update_post_meta( $post_id_inner, $meta_key, is_numeric( $dec ) ? max( 0.0, floatval( $dec ) ) : '0' );
	};
	$save_occ_disc_amt( $post_id, 'offitravel_ovabrw_room_disc_b1_triple', OFFITRAVEL_OVABRW_ROOM_META_DISCOUNT_B1_TRIPLE_PP );
	$save_occ_disc_amt( $post_id, 'offitravel_ovabrw_room_disc_b1_quad', OFFITRAVEL_OVABRW_ROOM_META_DISCOUNT_B1_QUAD_PP );
	$save_occ_disc_amt( $post_id, 'offitravel_ovabrw_room_disc_b2_triple', OFFITRAVEL_OVABRW_ROOM_META_DISCOUNT_B2_TRIPLE_PP );
	$save_occ_disc_amt( $post_id, 'offitravel_ovabrw_room_disc_b2_quad', OFFITRAVEL_OVABRW_ROOM_META_DISCOUNT_B2_QUAD_PP );

	$mtx_on = isset( $_POST['offitravel_ovabrw_matrix_cckf_enabled'] ) && 'yes' === $_POST['offitravel_ovabrw_matrix_cckf_enabled'] ? 'yes' : 'no';
	update_post_meta( $post_id, OFFITRAVEL_OVABRW_MATRIX_META_ENABLED, $mtx_on );

	if ( 'no' === $mtx_on ) {
		delete_post_meta( $post_id, OFFITRAVEL_OVABRW_MATRIX_META_FIELD_KEY );
		delete_post_meta( $post_id, OFFITRAVEL_OVABRW_MATRIX_META_BAND1_DAYS );
		delete_post_meta( $post_id, OFFITRAVEL_OVABRW_MATRIX_META_BAND2_DAYS );
		delete_post_meta( $post_id, OFFITRAVEL_OVABRW_MATRIX_META_BAND1_MAP );
		delete_post_meta( $post_id, OFFITRAVEL_OVABRW_MATRIX_META_BAND2_MAP );
	} else {
		if ( isset( $_POST['offitravel_ovabrw_matrix_cckf_field'] ) ) {
			$mf = sanitize_text_field( wp_unslash( $_POST['offitravel_ovabrw_matrix_cckf_field'] ) );
			if ( '' === trim( $mf ) ) {
				delete_post_meta( $post_id, OFFITRAVEL_OVABRW_MATRIX_META_FIELD_KEY );
			} else {
				update_post_meta( $post_id, OFFITRAVEL_OVABRW_MATRIX_META_FIELD_KEY, trim( $mf ) );
			}
		}
		if ( isset( $_POST['offitravel_ovabrw_matrix_band1_days'] ) ) {
			$b1csv = sanitize_text_field( wp_unslash( $_POST['offitravel_ovabrw_matrix_band1_days'] ) );
			if ( '' === trim( $b1csv ) ) {
				delete_post_meta( $post_id, OFFITRAVEL_OVABRW_MATRIX_META_BAND1_DAYS );
			} else {
				update_post_meta( $post_id, OFFITRAVEL_OVABRW_MATRIX_META_BAND1_DAYS, trim( preg_replace( '/[^\d,;\s\-]/', '', $b1csv ) ) );
			}
		}
		if ( isset( $_POST['offitravel_ovabrw_matrix_band2_days'] ) ) {
			$b2csv = sanitize_text_field( wp_unslash( $_POST['offitravel_ovabrw_matrix_band2_days'] ) );
			if ( '' === trim( $b2csv ) ) {
				delete_post_meta( $post_id, OFFITRAVEL_OVABRW_MATRIX_META_BAND2_DAYS );
			} else {
				update_post_meta( $post_id, OFFITRAVEL_OVABRW_MATRIX_META_BAND2_DAYS, trim( preg_replace( '/[^\d,;\s\-]/', '', $b2csv ) ) );
			}
		}

		$mtx_parse = static function ( $opts_post, $prices_post ) {
			if ( ! is_array( $opts_post ) ) {
				$opts_post = array();
			}
			if ( ! is_array( $prices_post ) ) {
				$prices_post = array();
			}
			$max_i = max( count( $opts_post ), count( $prices_post ) );
			$map   = array();
			for ( $j = 0; $j < $max_i; $j++ ) {
				$ok = isset( $opts_post[ $j ] ) ? sanitize_text_field( wp_unslash( $opts_post[ $j ] ) ) : '';
				$ok = trim( $ok );
				if ( '' === $ok ) {
					continue;
				}
				$pv = isset( $prices_post[ $j ] )
					? wc_format_decimal( str_replace( ',', '.', sanitize_text_field( wp_unslash( $prices_post[ $j ] ) ) ) )
					: '';
				$fv = ( '' !== $pv && null !== $pv ) ? round( floatval( $pv ), wc_get_price_decimals() ) : 0.0;
				if ( $fv < 0 ) {
					continue;
				}
				$map[ $ok ] = $fv;
			}
			return $map;
		};

		$o1       = isset( $_POST['offitravel_matrix_band1_opt'] ) ? wp_unslash( $_POST['offitravel_matrix_band1_opt'] ) : array();
		$p1       = isset( $_POST['offitravel_matrix_band1_price'] ) ? wp_unslash( $_POST['offitravel_matrix_band1_price'] ) : array();
		$map_band = $mtx_parse( $o1, $p1 );
		if ( empty( $map_band ) ) {
			delete_post_meta( $post_id, OFFITRAVEL_OVABRW_MATRIX_META_BAND1_MAP );
		} else {
			update_post_meta( $post_id, OFFITRAVEL_OVABRW_MATRIX_META_BAND1_MAP, $map_band );
		}

		$o2        = isset( $_POST['offitravel_matrix_band2_opt'] ) ? wp_unslash( $_POST['offitravel_matrix_band2_opt'] ) : array();
		$p2        = isset( $_POST['offitravel_matrix_band2_price'] ) ? wp_unslash( $_POST['offitravel_matrix_band2_price'] ) : array();
		$map_band2 = $mtx_parse( $o2, $p2 );
		if ( empty( $map_band2 ) ) {
			delete_post_meta( $post_id, OFFITRAVEL_OVABRW_MATRIX_META_BAND2_MAP );
		} else {
			update_post_meta( $post_id, OFFITRAVEL_OVABRW_MATRIX_META_BAND2_MAP, $map_band2 );
		}
	}
}

add_action( 'woocommerce_process_product_meta', 'offitravel_ovabrw_save_room_mode_meta', 17, 2 );

/**
 * Validar habitaciones al añadir al carrito.
 *
 * @param bool $passed
 * @param int  $product_id
 * @param int  $quantity
 * @return bool
 */
function offitravel_ovabrw_room_mode_validate_cart( $passed, $product_id, $quantity ) {
	if ( ! $passed ) {
		return false;
	}
	if ( ! offitravel_ovabrw_room_mode_enabled( $product_id ) ) {
		return $passed;
	}

	$product = wc_get_product( $product_id );
	if ( ! $product || ! $product->is_type( 'ovabrw_car_rental' ) ) {
		return $passed;
	}

	$settings = offitravel_ovabrw_get_room_settings( $product_id );

	$min_adults = absint( get_post_meta( $product_id, 'ovabrw_adults_min', true ) );
	$max_adults = absint( get_post_meta( $product_id, 'ovabrw_adults_max', true ) );
	$min_adults = (int) apply_filters( 'ovabrw_min_adults', $min_adults, $product_id );
	if ( ! $min_adults ) {
		$min_adults = 0;
	}

	$room_count = isset( $_POST['offitravel_room_count'] ) ? absint( wp_unslash( $_POST['offitravel_room_count'] ) ) : 0;
	if ( $room_count < 1 || $room_count > $settings['max_rooms'] ) {
		wc_add_notice( esc_html__( 'El número de habitaciones no es válido.', 'offitravel-ovabrw' ), 'error' );
		return false;
	}

	$people = isset( $_POST['offitravel_room_people'] ) ? wp_unslash( $_POST['offitravel_room_people'] ) : array();
	if ( ! is_array( $people ) ) {
		$people = array();
	}

	if ( count( $people ) !== $room_count ) {
		wc_add_notice( esc_html__( 'Debes indicar las personas en cada habitación.', 'offitravel-ovabrw' ), 'error' );
		return false;
	}

	$sum = 0;
	foreach ( $people as $p ) {
		$p = absint( $p );
		if ( $p < 1 || $p > $settings['max_per_room'] ) {
			wc_add_notice( esc_html__( 'El número de personas por habitación no es válido.', 'offitravel-ovabrw' ), 'error' );
			return false;
		}
		$sum += $p;
	}

	$posted_adults = isset( $_POST['ovabrw_adults'] ) ? absint( wp_unslash( $_POST['ovabrw_adults'] ) ) : 0;
	if ( $sum !== $posted_adults ) {
		wc_add_notice( esc_html__( 'El total de personas no coincide con la ocupación por habitaciones.', 'offitravel-ovabrw' ), 'error' );
		return false;
	}

	if ( $sum < $min_adults ) {
		wc_add_notice(
			sprintf(
				/* translators: %d: minimum adults */
				esc_html__( 'Se requiere un mínimo de %d persona(s).', 'offitravel-ovabrw' ),
				$min_adults
			),
			'error'
		);
		return false;
	}

	if ( $max_adults > 0 && $sum > $max_adults ) {
		wc_add_notice(
			sprintf(
				/* translators: %d: maximum adults */
				esc_html__( 'El máximo permitido es %d persona(s).', 'offitravel-ovabrw' ),
				$max_adults
			),
			'error'
		);
		return false;
	}

	if (
		offitravel_ovabrw_room_fixed_pricing_enabled( $product_id )
		&& offitravel_ovabrw_room_fixed_require_rule( $product_id )
		&& ! empty( offitravel_ovabrw_get_room_fixed_rules( $product_id ) )
	) {
		if (
			null === offitravel_ovabrw_room_match_fixed_price(
				$product_id,
				$sum,
				offitravel_ovabrw_pickup_for_fixed_rule_matching( $product_id )
			)
		) {
			wc_add_notice(
				esc_html__( 'Este número de personas y la fecha elegida no tienen una tarifa configurada para este tour. Cambia el total o la fecha de inicio.', 'offitravel-ovabrw' ),
				'error'
			);
			return false;
		}
	}

	return $passed;
}

add_filter( 'woocommerce_add_to_cart_validation', 'offitravel_ovabrw_room_mode_validate_cart', 99, 3 );

/**
 * Guardar desglose en datos del ítem del carrito.
 *
 * @param array $cart_item_data
 * @param int   $product_id
 * @param int   $variation_id
 * @param int   $quantity
 * @return array
 */
function offitravel_ovabrw_room_mode_cart_item_data( $cart_item_data, $product_id, $variation_id, $quantity ) {
	if ( ! offitravel_ovabrw_room_mode_enabled( $product_id ) ) {
		return $cart_item_data;
	}

	$room_count = isset( $_POST['offitravel_room_count'] ) ? absint( wp_unslash( $_POST['offitravel_room_count'] ) ) : 0;
	$people     = isset( $_POST['offitravel_room_people'] ) ? wp_unslash( $_POST['offitravel_room_people'] ) : array();
	if ( ! is_array( $people ) || $room_count < 1 || count( $people ) !== $room_count ) {
		return $cart_item_data;
	}

	$people_clean = array_map( 'absint', $people );
	$cart_item_data['offitravel_room_count']   = $room_count;
	$cart_item_data['offitravel_room_people']    = $people_clean;

	$parts = array();
	$i     = 1;
	foreach ( $people_clean as $p ) {
		$p       = absint( $p );
		$parts[] = sprintf(
			/* translators: 1: room number, 2: people count */
			__( 'Hab. %1$d: %2$d', 'offitravel-ovabrw' ),
			$i,
			$p
		);
		++$i;
	}
	$cart_item_data['offitravel_room_breakdown'] = implode( ' · ', $parts );

	return $cart_item_data;
}

add_filter( 'woocommerce_add_cart_item_data', 'offitravel_ovabrw_room_mode_cart_item_data', 25, 4 );

/**
 * Mostrar desglose en carrito y checkout.
 *
 * @param array $item_data
 * @param array $cart_item
 * @return array
 */
function offitravel_ovabrw_room_mode_get_item_data( $item_data, $cart_item ) {
	$pid = isset( $cart_item['product_id'] ) ? (int) $cart_item['product_id'] : 0;
	if ( ! $pid && isset( $cart_item['data'] ) && is_object( $cart_item['data'] ) && method_exists( $cart_item['data'], 'get_id' ) ) {
		$pid = (int) $cart_item['data']->get_id();
	}
	if ( ! $pid ) {
		return $item_data;
	}
	$product = wc_get_product( $pid );
	if ( ! $product || ! $product->is_type( 'ovabrw_car_rental' ) ) {
		return $item_data;
	}

	if ( ! empty( $cart_item['offitravel_room_breakdown'] ) ) {
		$item_data[] = array(
			'name'  => esc_html__( 'Ocupación', 'offitravel-ovabrw' ),
			'value' => wp_kses_post( $cart_item['offitravel_room_breakdown'] ),
		);
	}

	foreach ( offitravel_ovabrw_pricing_addon_display_rows( $pid, $cart_item ) as $row ) {
		$item_data[] = $row;
	}

	return $item_data;
}

add_filter( 'woocommerce_get_item_data', 'offitravel_ovabrw_room_mode_get_item_data', 10, 2 );

/**
 * Persistir en el pedido.
 *
 * @param WC_Order_Item_Product $item
 * @param string                $cart_item_key
 * @param array                 $values
 * @param WC_Order              $order
 */
function offitravel_ovabrw_room_mode_order_line_item( $item, $cart_item_key, $values, $order ) {
	$pid = isset( $values['product_id'] ) ? (int) $values['product_id'] : 0;
	if ( ! $pid ) {
		return;
	}

	if ( ! empty( $values['offitravel_room_breakdown'] ) ) {
		$item->add_meta_data(
			__( 'Ocupación por habitaciones', 'offitravel-ovabrw' ),
			sanitize_text_field( $values['offitravel_room_breakdown'] ),
			true
		);
	}

	foreach ( offitravel_ovabrw_pricing_addon_display_rows( $pid, $values ) as $row ) {
		if ( empty( $row['name'] ) ) {
			continue;
		}
		$item->add_meta_data(
			wp_strip_all_tags( $row['name'] ),
			wp_strip_all_tags( isset( $row['value'] ) ? $row['value'] : '' ),
			true
		);
	}
}

add_action( 'woocommerce_checkout_create_order_line_item', 'offitravel_ovabrw_room_mode_order_line_item', 10, 4 );

/**
 * Ocultar la clave técnica si WooCommerce la copia al pedido (evitar duplicado).
 *
 * @param string[] $hidden
 * @return string[]
 */
function offitravel_ovabrw_room_mode_hidden_order_itemmeta( $hidden ) {
	$hidden[] = 'offitravel_room_breakdown';
	$hidden[] = 'offitravel_room_count';
	$hidden[] = 'offitravel_room_people';
	return $hidden;
}

add_filter( 'woocommerce_hidden_order_itemmeta', 'offitravel_ovabrw_room_mode_hidden_order_itemmeta' );

/**
 * Scripts front: sincronizar ovabrw_adults y total AJAX OVA.
 */
function offitravel_ovabrw_room_mode_enqueue_scripts() {
	if ( ! is_product() ) {
		return;
	}
	$pid = get_queried_object_id();
	if ( ! $pid || ! offitravel_ovabrw_room_mode_enabled( $pid ) ) {
		return;
	}

	wp_register_script( 'offitravel-ovabrw-room-mode', '', array( 'jquery', 'ova_brw_js_frontend' ), '1.7.3', true );
	wp_enqueue_script( 'offitravel-ovabrw-room-mode' );

	$s = offitravel_ovabrw_get_room_settings( $pid );

	wp_localize_script(
		'offitravel-ovabrw-room-mode',
		'offitravelOvabrwRoom',
		array(
			'maxRooms'    => $s['max_rooms'],
			'maxPerRoom'  => $s['max_per_room'],
			'roomLabel'   => esc_html__( 'Habitación', 'offitravel-ovabrw' ),
			'peopleLabel' => esc_html__( 'Personas', 'offitravel-ovabrw' ),
		)
	);

	$js = <<<'JS'
(function($){
	/**
	 * OVA arma el POST de ovabrw_calculate_total a mano y no envía ocupación por habitaciones.
	 * Sin offitravel_room_* el servidor no puede aplicar precios fijos por combinación (solo ve adults).
	 */
	$(document).ajaxSend(function(_event, _jqXHR, settings) {
		var url = settings.url || '';
		if (url.indexOf('admin-ajax.php') === -1) {
			return;
		}
		var payload = settings.data;
		var str = '';
		if (typeof payload === 'string') {
			str = payload;
		} else if (payload && typeof payload === 'object' && !(payload instanceof window.FormData)) {
			try {
				str = $.param(payload);
			} catch (err) {
				return;
			}
		} else {
			return;
		}
		if (str.indexOf('action=ovabrw_calculate_total') === -1 && str.indexOf('action%3Dovabrw_calculate_total') === -1) {
			return;
		}
		var $form = $('form.booking-form').filter(function() {
			return $(this).find('#offitravel_room_count').length > 0;
		}).first();
		if (!$form.length) {
			return;
		}
		var rc = $form.find('#offitravel_room_count').val();
		var peoples = [];
		$form.find('select.offitravel-room-people').each(function() {
			peoples.push($(this).val());
		});
		if (!rc || peoples.length < 1) {
			return;
		}
		str += '&offitravel_room_count=' + encodeURIComponent(rc);
		for (var i = 0; i < peoples.length; i++) {
			str += '&offitravel_room_people[]=' + encodeURIComponent(peoples[i]);
		}
		settings.data = str;
	});

	function parseInitial($wrap){
		var raw = $wrap.attr("data-initial-rooms");
		if (!raw) return null;
		try {
			var a = JSON.parse(raw);
			return Array.isArray(a) && a.length ? a : null;
		} catch(e){ return null; }
	}

	function initRoomMode($form){
		var $wrap = $form.find(".offitravel-room-mode-wrapper");
		if (!$wrap.length) return;

		var cfg = window.offitravelOvabrwRoom || {};
		var maxRooms = parseInt($wrap.data("max-rooms"), 10) || cfg.maxRooms || 10;
		var maxPer = parseInt($wrap.data("max-per-room"), 10) || cfg.maxPerRoom || 4;
		var minA = parseInt($wrap.data("min-adults"), 10) || 0;
		var maxA = parseInt($wrap.data("max-adults"), 10) || 0;
		var roomLbl = cfg.roomLabel || "Habitación";
		var peopleLbl = cfg.peopleLabel || "Personas";

		var $count = $form.find("#offitravel_room_count");
		var $rows = $form.find("#offitravel-room-rows");
		var recalcTimer = null;

		/**
		 * Replica el POST ovabrw_calculate_total de ova-brw-frontend cuando no hay .ovabrw-select
		 * ni data_custom_ckf: jQuery.change en la fecha NO invoca calculateTotal dentro del IIFE de OVA.
		 */
		function offitravelBuildCalculateTotalPayload($form) {
			var pickup = $form.find('input[name="ovabrw_pickup_date"]').val();
			var productId = $form.find('input[name="product_id"]').val();
			if (!pickup || !productId) {
				return null;
			}
			var a = { action: "ovabrw_calculate_total", pickup_date: pickup, product_id: productId };
			var tf = $form.find('input[name="ovabrw_time_from"]:checked').val();
			if (tf) {
				a.time_from = tf;
			}
			var dof = $form.find('input[name="ovabrw_pickoff_date"]').val();
			if (dof) {
				a.dropoff_date = dof;
			}
			var ad = $form.find('input[name="ovabrw_adults"]').val();
			if (ad) {
				a.adults = ad;
			}
			ad = $form.find('input[name="ovabrw_childrens"]').val();
			if (ad) {
				a.childrens = ad;
			}
			ad = $form.find('input[name="ovabrw_babies"]').val();
			if (ad) {
				a.babies = ad;
			}
			var qEl = $form.find('input[name="ovabrw_quantity"]');
			a.quantity = qEl.length ? qEl.val() : "1";

			ad = $form.find('input[name="ova_type_deposit"]:checked').val();
			if (ad) {
				a.deposit = ad;
			}

			var ckfEl = $form.find('input[name="data_custom_ckf"]');
			var fieldMap = ckfEl.length ? ckfEl.data("ckf") : null;
			if (fieldMap && typeof fieldMap === "object") {
				var r = {};
				var c = {};
				$.each(fieldMap, function (key, field) {
					var opt, t, n, i, arr, s, k2;
					if (field.type === "radio") {
						opt = $form.find('input[name="' + key + '"]:checked');
						if (opt.length) {
							r[key] = opt.val();
							t = opt.closest(".radio-item").find('input[name="' + key + "_qty[" + opt.val() + ']"]');
							n = t.length ? parseInt(t.val(), 10) : 0;
							if (!isNaN(n) && n) {
								c[key] = n;
							}
						}
					} else if (field.type === "checkbox") {
						arr = [];
						s = {};
						$form.find(".ovabrw-checkbox input[type=checkbox]:checked").each(function () {
							var v = $(this).val();
							if (v) {
								arr.push(v);
								t = $(this).closest(".checkbox-item").find('input[name="' + key + "_qty[" + v + ']"]');
								n = t.length ? parseInt(t.val(), 10) : 0;
								if (!isNaN(n) && n) {
									s[v] = n;
								}
							}
						});
						if (arr.length) {
							r[key] = arr;
						}
						if ($.type(s) === "object" && !$.isEmptyObject(s)) {
							c[key] = s;
						}
					} else if (field.type === "select") {
						opt = $form.find('select[name="' + key + '"]').val();
						if (opt) {
							r[key] = opt;
							t = $form.find('input[name="' + key + "_qty[" + opt + ']"]');
							n = t.length ? parseInt(t.val(), 10) : 0;
							if (!isNaN(n) && n) {
								c[key] = n;
							}
						}
					}
				});
				if (!$.isEmptyObject(r)) {
					a.custom_ckf = JSON.stringify(r);
				}
				if (!$.isEmptyObject(c)) {
					a.cckf_qty = JSON.stringify(c);
				}
			}

			var rs = {}, rg = {};
			$form.find(".ovabrw-resources input[type=checkbox]:checked").each(function () {
				var rk = $(this).data("rs-key");
				if (!rk) {
					return;
				}
				rs[rk] = $(this).val();
				var eg = {}, x;
				x = parseInt($form.find('input[name="ovabrw_resource_guests[' + rk + '][adult]"]').val(), 10);
				if ($.isNumeric(x)) {
					eg.adult = x;
				}
				x = parseInt($form.find('input[name="ovabrw_resource_guests[' + rk + '][child]"]').val(), 10);
				if ($.isNumeric(x)) {
					eg.child = x;
				}
				x = parseInt($form.find('input[name="ovabrw_resource_guests[' + rk + '][baby]"]').val(), 10);
				if ($.isNumeric(x)) {
					eg.baby = x;
				}
				if (!$.isEmptyObject(eg)) {
					rg[rk] = eg;
				}
			});
			if (!$.isEmptyObject(rs)) {
				a.resources = JSON.stringify(rs);
			}
			if (!$.isEmptyObject(rg)) {
				a.resource_guests = JSON.stringify(rg);
			}

			var sv = [], sg = {};
			$form.find('select[name="ovabrw_service[]"]').each(function () {
				var sid = $(this).val();
				if (!sid) {
					return;
				}
				sv.push(sid);
				var gs = {}, y;
				y = parseInt($form.find('input[name="ovabrw_service_guests[' + sid + '][adult]"]').val(), 10);
				if ($.isNumeric(y)) {
					gs.adult = y;
				}
				y = parseInt($form.find('input[name="ovabrw_service_guests[' + sid + '][child]"]').val(), 10);
				if ($.isNumeric(y)) {
					gs.child = y;
				}
				y = parseInt($form.find('input[name="ovabrw_service_guests[' + sid + '][baby]"]').val(), 10);
				if ($.isNumeric(y)) {
					gs.baby = y;
				}
				if (!$.isEmptyObject(gs)) {
					sg[sid] = gs;
				}
			});
			if (sv.length) {
				a.services = JSON.stringify(sv);
			}
			if (!$.isEmptyObject(sg)) {
				a.service_guests = JSON.stringify(sg);
			}

			if (typeof window.offitravelPrdAddonAugmentPayload === "function") {
				window.offitravelPrdAddonAugmentPayload(a, $form);
			}

			return a;
		}

		function offitravelAjaxCalculateBookingTotal($form) {
			if (typeof ajax_object === "undefined" || !ajax_object.ajax_url) {
				return;
			}
			var data = offitravelBuildCalculateTotalPayload($form);
			if (!data) {
				return;
			}
			var t = $form.find(".ajax-show-total .ajax-loading-total");
			$form.find(".ajax-show-total .ovabrw-show-amount").css("display", "flex");
			t.show();
			$form.find(".ajax-error").html("").hide();
			$form.find(".ajax-show-total .show-availables-number").html("");
			$form.find(".ajax-show-total .show-amount-insurance").html("");
			$form.find(".ajax-show-total .show-total-number").html("");

			return $.ajax({
				url: ajax_object.ajax_url,
				type: "POST",
				data: data,
				success: function (resp) {
					var e;
					try {
						e = typeof resp === "object" ? resp : JSON.parse(resp);
					} catch (err) {
						t.hide();
						return;
					}
					if (!e) {
						t.hide();
						return;
					}
					if (e.error) {
						$form.find("button.booking-form-submit").addClass("disabled");
						$form.find(".ajax-show-total .ovabrw-show-amount").css("display", "none");
						$form.find(".ajax-show-total .ovabrw-ajax-amount-insurance").hide();
						$form.find(".ajax-error").html("").append(e.error).show();
					} else {
						$form.find("button.booking-form-submit").removeClass("disabled");
						if (e.adults_price) {
							$form.find(".ovabrw-wrapper-guestspicker .adults-price").html("").append(e.adults_price);
						}
						if (e.childrens_price) {
							$form.find(".ovabrw-wrapper-guestspicker .childrens-price").html("").append(e.childrens_price);
						}
						if (e.babies_price) {
							$form.find(".ovabrw-wrapper-guestspicker .babies-price").html("").append(e.babies_price);
						}
						$form.find(".ajax-show-total .show-amount-insurance").html("").append(e.insurance_amount || "");
						$form.find(".ajax-show-total .ovabrw-ajax-amount-insurance").show();
						$form.find(".ajax-show-total .show-total-number").html("").append(e.line_total || "");
						if ("qty_by_guests" in e && e.qty_by_guests) {
							$form.find(".ajax-show-total .ovabrw-ajax-availables").css("display", "none");
						} else if (typeof e.quantity_available !== "undefined") {
							$form.find(".ajax-show-total .show-availables-number").html("").append(e.quantity_available);
						}
					}
					t.hide();
					$form.find(".ovabrw-date-loading").hide();
				}
			});
		}

		/**
		 * Los addons (offitravel-product-addons-front.js) deben poder forzar el mismo POST
		 * que requestTotalRefresh; OVA no siempre recalcula con change en fecha/campos.
		 */
		window.offitravelAjaxCalculateBookingTotal = offitravelAjaxCalculateBookingTotal;

		function requestTotalRefresh(){
			if (recalcTimer) {
				clearTimeout(recalcTimer);
			}
			recalcTimer = setTimeout(function(){
				var $qty = $form.find('input[name="ovabrw_quantity"]');
				if ($qty.length) {
					$qty.trigger("change");
					return;
				}
				var $ovaSelect = $form.find(".ovabrw-select select").first();
				if ($ovaSelect.length) {
					$ovaSelect.trigger("change");
					return;
				}

				offitravelAjaxCalculateBookingTotal($form);
			}, 80);
		}

		function sync(){
			var sum = 0;
			$form.find(".offitravel-room-people").each(function(){
				sum += parseInt($(this).val(), 10) || 0;
			});
			var $adults = $form.find('input[name="ovabrw_adults"].guests-input');
			if (!$adults.length) {
				$adults = $form.find('input[name="ovabrw_adults"]');
			}
			$adults.val(sum);
			$form.find(".gueststotal").text(sum);
			$adults.trigger("input").trigger("change");
			requestTotalRefresh();
		}

		function buildRows(n, initialList){
			$rows.empty();
			n = Math.max(1, Math.min(maxRooms, parseInt(n, 10) || 1));
			for (var i = 0; i < n; i++){
				var def = 1;
				if (initialList && initialList[i]) {
					def = Math.min(maxPer, Math.max(1, parseInt(initialList[i], 10) || 1));
				} else if (i === 0 && minA > 0) {
					def = Math.min(maxPer, minA);
				}
				var $sel = $("<select/>", {
					name: "offitravel_room_people[]",
					"class": "offitravel-room-people",
					"aria-label": roomLbl + " " + (i + 1)
				});
				for (var p = 1; p <= maxPer; p++){
					$sel.append($("<option/>", { value: p, text: p }));
				}
				$sel.val(def);
				var $row = $("<div/>", { "class": "offitravel-room-row" });
				$row.append($("<span/>", { "class": "offitravel-room-row-label" }).text(roomLbl + " " + (i + 1)));
				$row.append($("<label/>", { "class": "offitravel-room-people-label" }).append(
					$("<span/>").text(peopleLbl + ": "),
					$sel
				));
				$rows.append($row);
			}
			sync();
		}

		$count.off("change.offitravelRooms").on("change.offitravelRooms", function(){
			var preserved = null;
			if ($form.find("[data-offitravel-age-service]").length) {
				preserved = [];
				$rows.find(".offitravel-room-people").each(function(){
					preserved.push($(this).val());
				});
			}
			buildRows($(this).val(), preserved);
		});
		$form.off("change.offitravelRooms", ".offitravel-room-people").on("change.offitravelRooms", ".offitravel-room-people", sync);

		var initial = parseInitial($wrap);
		var startN = initial ? initial.length : (parseInt($count.val(), 10) || 1);
		startN = Math.max(1, Math.min(maxRooms, startN));
		$count.val(startN);
		buildRows(startN, initial);
	}

	$(function(){
		$(".ova-booking-form form.booking-form").each(function(){
			initRoomMode($(this));
		});
	});
})(jQuery);
JS;

	wp_add_inline_script( 'offitravel-ovabrw-room-mode', $js );
}

add_action( 'wp_enqueue_scripts', 'offitravel_ovabrw_room_mode_enqueue_scripts', 20 );
