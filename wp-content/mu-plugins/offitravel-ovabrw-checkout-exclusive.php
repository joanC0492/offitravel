<?php
/**
 * Plugin Name: Offitravel – OVA BRW día de salida (disponibilidad)
 * Description: Trata el día mostrado como “salida” como exclusivo al comprobar bloqueos y días deshabilitados en tours por días (evita fallos cuando solo el día de salida está bloqueado en el calendario).
 * Version: 1.0.0
 * Author: Offitravel
 *
 * Requiere el filtro `ovabrw_effective_dropoff_for_availability` añadido en ova-brw/inc/ovabrw-cart.php.
 * Para desactivar el comportamiento sin borrar este archivo: define( 'OFFITRAVEL_OVABRW_CHECKOUT_EXCLUSIVE', false ); en wp-config.php
 *
 * Modos (wp-config o filtros):
 * - Por defecto: solo se validan bloqueos / días prohibidos en la fecha de ENTRADA (recomendado para circuitos con calendario de salidas fijas).
 * - OFFITRAVEL_OVABRW_AVAILABILITY_PICKUP_ONLY false + CHECKOUT_EXCLUSIVE true: solo se excluye el día de salida del chequeo (comportamiento anterior).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'ovabrw_effective_dropoff_for_availability', 'offitravel_ovabrw_effective_dropoff_for_availability', 10, 4 );

/**
 * Contrae el intervalo usado en comprobaciones de “no disponible” para que no fallen los días
 * intermedios ni el de salida cuando el calendario solo marca fechas de inicio de tour.
 *
 * @param int $dropoff_date     Fecha de salida (timestamp) que OVA BRW pasa al filtro.
 * @param int $product_id       ID del producto.
 * @param int $pickup_date      Fecha de entrada (timestamp).
 * @param int $original_dropoff Fecha de salida original del intervalo de reserva.
 */
function offitravel_ovabrw_effective_dropoff_for_availability( $dropoff_date, $product_id, $pickup_date, $original_dropoff ) {
	if ( defined( 'OFFITRAVEL_OVABRW_CHECKOUT_EXCLUSIVE' ) && ! OFFITRAVEL_OVABRW_CHECKOUT_EXCLUSIVE ) {
		return $original_dropoff;
	}

	if ( ! $product_id || ! $pickup_date || ! $original_dropoff ) {
		return $dropoff_date;
	}

	// Reservas por horas / franjas horarias: no modificar el intervalo.
	$duration_checkbox = get_post_meta( $product_id, 'ovabrw_duration_checkbox', true );
	if ( in_array( $duration_checkbox, array( 'yes', 'on', '1', 1, true ), true ) ) {
		return $original_dropoff;
	}

	$number_days = absint( get_post_meta( $product_id, 'ovabrw_number_days', true ) );
	$multi_day   = $number_days >= 1
		|| ( (int) $original_dropoff - (int) $pickup_date >= DAY_IN_SECONDS );
	if ( ! $multi_day ) {
		return $original_dropoff;
	}

	$pickup_only = true;
	if ( defined( 'OFFITRAVEL_OVABRW_AVAILABILITY_PICKUP_ONLY' ) ) {
		$pickup_only = (bool) OFFITRAVEL_OVABRW_AVAILABILITY_PICKUP_ONLY;
	}
	$pickup_only = apply_filters( 'offitravel_ovabrw_availability_pickup_day_only', $pickup_only, $product_id, $pickup_date, $original_dropoff );

	// Solo el día de entrada debe estar permitido frente a UT / días de semana bloqueados.
	if ( $pickup_only ) {
		return (int) $pickup_date;
	}

	// Modo alternativo: tratar el último día ocupado como el anterior al de salida mostrado.
	$adjusted = (int) $original_dropoff - DAY_IN_SECONDS;

	if ( $adjusted < (int) $pickup_date ) {
		return $pickup_date;
	}

	return $adjusted;
}
