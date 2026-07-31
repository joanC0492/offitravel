<?php
/**
 * Plugin Name: Offitravel – OVA BRW fechas disponibles (lista blanca)
 * Description: Rangos o días sueltos de salidas permitidas; fin opcional. Con lista blanca activa, UT no aplica.
 * Version: 1.3.0
 * Author: Offitravel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'OFFITRAVEL_OVABRW_WHITELIST_META_ENABLED', '_offitravel_ovabrw_whitelist_enabled' );
define( 'OFFITRAVEL_OVABRW_WHITELIST_META_START', '_offitravel_ovabrw_available_startdate' );
define( 'OFFITRAVEL_OVABRW_WHITELIST_META_END', '_offitravel_ovabrw_available_enddate' );
/** @deprecated Solo lectura / migración desde v1.1 */
define( 'OFFITRAVEL_OVABRW_WHITELIST_META_DATES', '_offitravel_ovabrw_available_dates' );

/**
 * Lista blanca activa para el producto.
 */
function offitravel_ovabrw_whitelist_enabled( $product_id ) {
	$forced = apply_filters( 'offitravel_ovabrw_whitelist_always_enabled', true, (int) $product_id );
	if ( $forced ) {
		return true;
	}
	return 'yes' === get_post_meta( (int) $product_id, OFFITRAVEL_OVABRW_WHITELIST_META_ENABLED, true );
}

/**
 * Añade al mapa un par inicio/fin (fin vacío = solo el día de inicio).
 *
 * @param array  $map       Referencia mapa Y-m-d => true.
 * @param string $start_raw Fecha inicio.
 * @param string $end_raw   Fecha fin (opcional).
 */
function offitravel_ovabrw_add_pair_to_map( array &$map, $start_raw, $end_raw ) {
	$start_raw = trim( (string) $start_raw );
	if ( '' === $start_raw ) {
		return;
	}
	$start_ts = strtotime( $start_raw );
	if ( ! $start_ts ) {
		return;
	}
	$end_raw = trim( (string) $end_raw );
	if ( '' === $end_raw ) {
		$map[ gmdate( 'Y-m-d', $start_ts ) ] = true;
		return;
	}
	$end_ts = strtotime( $end_raw );
	if ( ! $end_ts || $end_ts < $start_ts ) {
		return;
	}
	for ( $t = $start_ts; $t <= $end_ts; $t += DAY_IN_SECONDS ) {
		$map[ gmdate( 'Y-m-d', $t ) ] = true;
	}
}

/**
 * Mapa Y-m-d => true de todos los días permitidos.
 */
function offitravel_ovabrw_get_allowed_ymd_map( $product_id ) {
	$product_id = (int) $product_id;
	$map        = array();

	$starts = get_post_meta( $product_id, OFFITRAVEL_OVABRW_WHITELIST_META_START, true );
	$ends   = get_post_meta( $product_id, OFFITRAVEL_OVABRW_WHITELIST_META_END, true );
	if ( ! is_array( $starts ) ) {
		$starts = array();
	}
	if ( ! is_array( $ends ) ) {
		$ends = array();
	}

	if ( ! empty( $starts ) ) {
		foreach ( $starts as $i => $start_raw ) {
			$end_raw = isset( $ends[ $i ] ) ? $ends[ $i ] : '';
			offitravel_ovabrw_add_pair_to_map( $map, $start_raw, $end_raw );
		}
	}

	if ( ! empty( $map ) ) {
		return $map;
	}

	// Migración v1.1: un solo array de fechas sueltas.
	$dates = get_post_meta( $product_id, OFFITRAVEL_OVABRW_WHITELIST_META_DATES, true );
	if ( is_array( $dates ) ) {
		foreach ( $dates as $raw ) {
			offitravel_ovabrw_add_pair_to_map( $map, $raw, '' );
		}
	}

	return $map;
}

/**
 * Límites de navegación del calendario (primer día del mes más antiguo, último día del mes más lejano).
 *
 * @param array $allowed_map Mapa Y-m-d => true de offitravel_ovabrw_get_allowed_ymd_map().
 * @return array{min_nav:int,max_nav:int}|null
 */
function offitravel_ovabrw_whitelist_month_nav_bounds( array $allowed_map ) {
	if ( empty( $allowed_map ) ) {
		return null;
	}

	$keys = array_keys( $allowed_map );
	sort( $keys, SORT_STRING );
	$earliest = $keys[0];
	$latest   = $keys[ count( $keys ) - 1 ];

	try {
		$tz = wp_timezone();
		$de = new DateTimeImmutable( $earliest, $tz );
		$dl = new DateTimeImmutable( $latest, $tz );
	} catch ( Exception $e ) {
		return null;
	}

	$first = $de->modify( 'first day of this month' )->setTime( 0, 0, 0 );
	$last  = $dl->modify( 'last day of this month' )->setTime( 0, 0, 0 );

	return array(
		'min_nav' => $first->getTimestamp(),
		'max_nav' => $last->getTimestamp(),
	);
}

/**
 * Interpreta una fecha del calendario a medianoche en la zona horaria indicada.
 *
 * @param string       $raw         Fecha con el formato configurado por OVA.
 * @param string       $date_format Formato PHP de fecha.
 * @param DateTimeZone $timezone    Zona horaria del calendario.
 * @return DateTimeImmutable|null
 */
function offitravel_ovabrw_whitelist_parse_local_date( $raw, $date_format, DateTimeZone $timezone ) {
	$raw = trim( (string) $raw );
	if ( '' === $raw ) {
		return null;
	}

	try {
		$date   = DateTimeImmutable::createFromFormat( '!' . $date_format, $raw, $timezone );
		$errors = DateTimeImmutable::getLastErrors();
	} catch ( Throwable $e ) {
		return null;
	}

	if ( false === $date ) {
		return null;
	}
	if ( false !== $errors && ( $errors['warning_count'] || $errors['error_count'] ) ) {
		return null;
	}
	if ( $raw !== $date->format( $date_format ) ) {
		return null;
	}

	return $date;
}

/**
 * Genera todos los dias bloqueados recorriendo dias de calendario locales.
 *
 * @param array             $allowed_map Mapa Y-m-d => true de fechas permitidas.
 * @param DateTimeImmutable $min_date    Primer dia del recorrido, inclusive.
 * @param DateTimeImmutable $max_date    Ultimo dia del recorrido, inclusive.
 * @param string            $date_format Formato PHP de salida.
 * @return string[]
 */
function offitravel_ovabrw_whitelist_get_blocked_dates( array $allowed_map, DateTimeImmutable $min_date, DateTimeImmutable $max_date, $date_format ) {
	$blocked = array();
	$cursor  = $min_date;

	while ( $cursor <= $max_date ) {
		$ymd = $cursor->format( 'Y-m-d' );
		if ( ! isset( $allowed_map[ $ymd ] ) ) {
			$blocked[] = $cursor->format( $date_format );
		}
		$cursor = $cursor->modify( '+1 day' );
	}

	return $blocked;
}

/**
 * Filtra fechas permitidas por ventana de años (año actual + N siguientes).
 *
 * @param array $allowed_map Mapa Y-m-d => true.
 * @param int   $years_ahead Cantidad de años futuros a incluir.
 * @return array
 */
function offitravel_ovabrw_filter_allowed_map_by_year_window( array $allowed_map, $years_ahead = 2 ) {
	if ( empty( $allowed_map ) ) {
		return array();
	}

	$years_ahead = max( 0, (int) $years_ahead );
	$current_year = (int) wp_date( 'Y' );
	$max_year     = $current_year + $years_ahead;

	$filtered = array();
	foreach ( $allowed_map as $ymd => $state ) {
		$year = (int) substr( (string) $ymd, 0, 4 );
		if ( $year >= $current_year && $year <= $max_year ) {
			$filtered[ $ymd ] = (bool) $state;
		}
	}

	return $filtered;
}

/**
 * ¿La fecha de pickup (timestamp) está permitida?
 */
function offitravel_ovabrw_pickup_in_whitelist( $product_id, $pickup_ts ) {
	if ( ! $pickup_ts ) {
		return false;
	}
	$map = offitravel_ovabrw_get_allowed_ymd_map( $product_id );
	if ( empty( $map ) ) {
		return false;
	}
	$ymd = gmdate( 'Y-m-d', (int) $pickup_ts );
	return isset( $map[ $ymd ] );
}

/**
 * Datepicker front: bloquear todo salvo lista + reservas.
 */
add_filter( 'ovabrw_get_product_datepicker_options', 'offitravel_ovabrw_filter_datepicker_whitelist', 99, 3 );
function offitravel_ovabrw_filter_datepicker_whitelist( $datepicker, $product_id, $form ) {
	if ( ! $product_id || ! offitravel_ovabrw_whitelist_enabled( $product_id ) ) {
		return $datepicker;
	}

	$date_format = function_exists( 'ovabrw_get_date_format' ) ? ovabrw_get_date_format() : 'd-m-Y';
	$timezone    = wp_timezone();
	$allowed_map = offitravel_ovabrw_get_allowed_ymd_map( $product_id );
	$years_ahead = (int) apply_filters( 'offitravel_ovabrw_whitelist_years_ahead', 2, $product_id, $form );
	$allowed_map = offitravel_ovabrw_filter_allowed_map_by_year_window( $allowed_map, $years_ahead );

	$min_str = isset( $datepicker['LockPlugin']['minDate'] ) ? $datepicker['LockPlugin']['minDate'] : '';
	$max_str = isset( $datepicker['LockPlugin']['maxDate'] ) ? $datepicker['LockPlugin']['maxDate'] : '';
	if ( ! $min_str || ! $max_str ) {
		return $datepicker;
	}

	$min_date = offitravel_ovabrw_whitelist_parse_local_date( $min_str, $date_format, $timezone );
	$max_date = offitravel_ovabrw_whitelist_parse_local_date( $max_str, $date_format, $timezone );
	if ( ! $min_date || ! $max_date || $max_date < $min_date ) {
		return $datepicker;
	}

	$nav_bounds = offitravel_ovabrw_whitelist_month_nav_bounds( $allowed_map );
	if ( $nav_bounds && apply_filters( 'offitravel_ovabrw_whitelist_clamp_datepicker_month_range', true, $product_id, $allowed_map ) ) {
		$nav_min = ( new DateTimeImmutable( '@' . $nav_bounds['min_nav'] ) )->setTimezone( $timezone );
		$nav_max = ( new DateTimeImmutable( '@' . $nav_bounds['max_nav'] ) )->setTimezone( $timezone );

		if ( $min_date < $nav_min ) {
			$min_date = $nav_min;
		}
		if ( $max_date > $nav_max ) {
			$max_date = $nav_max;
		}
		if ( $max_date < $min_date ) {
			$min_date = $nav_min;
			$max_date = $nav_max;
		}

		$datepicker['LockPlugin']['minDate'] = $min_date->format( $date_format );
		$datepicker['LockPlugin']['maxDate'] = $max_date->format( $date_format );
		if ( isset( $datepicker['startDate'] ) ) {
			$datepicker['startDate'] = $datepicker['LockPlugin']['minDate'];
		}
	}

	$blocked = offitravel_ovabrw_whitelist_get_blocked_dates( $allowed_map, $min_date, $max_date, $date_format );

	$booked       = isset( $datepicker['bookedDates'] ) && is_array( $datepicker['bookedDates'] ) ? $datepicker['bookedDates'] : array();
	$use_booked   = apply_filters( 'offitravel_ovabrw_whitelist_merge_booked_dates', false, $product_id, $booked, $allowed_map, $form );
	$disable_dates = $use_booked ? array_merge( $blocked, $booked ) : $blocked;

	$datepicker['disableDates']  = array_values( array_unique( $disable_dates ) );
	$datepicker['allowedDates'] = array();

	return $datepicker;
}

/**
 * Carga filas para el editor: pares inicio/fin o migración desde fechas sueltas.
 */
function offitravel_ovabrw_get_whitelist_rows_for_edit( $post_id ) {
	$starts = get_post_meta( $post_id, OFFITRAVEL_OVABRW_WHITELIST_META_START, true );
	$ends   = get_post_meta( $post_id, OFFITRAVEL_OVABRW_WHITELIST_META_END, true );
	if ( ! is_array( $starts ) ) {
		$starts = array();
	}
	if ( ! is_array( $ends ) ) {
		$ends = array();
	}

	$rows = array();
	if ( ! empty( $starts ) ) {
		foreach ( $starts as $i => $s ) {
			$rows[] = array(
				'start' => $s,
				'end'   => isset( $ends[ $i ] ) ? $ends[ $i ] : '',
			);
		}
	} else {
		$dates = get_post_meta( $post_id, OFFITRAVEL_OVABRW_WHITELIST_META_DATES, true );
		if ( is_array( $dates ) ) {
			foreach ( $dates as $d ) {
				$d = trim( (string) $d );
				if ( '' === $d ) {
					continue;
				}
				$rows[] = array( 'start' => $d, 'end' => '' );
			}
		}
	}

	if ( empty( $rows ) ) {
		$rows[] = array( 'start' => '', 'end' => '' );
	}

	return $rows;
}

/**
 * Acordeón dentro del metabox OVA (tras Unavailable time).
 */
function offitravel_ovabrw_render_whitelist_accordion( $post_id ) {
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

	$enabled = offitravel_ovabrw_whitelist_enabled( $post_id );
	$rows    = offitravel_ovabrw_get_whitelist_rows_for_edit( $post_id );

	wp_nonce_field( 'offitravel_ovabrw_whitelist_save', 'offitravel_ovabrw_whitelist_nonce' );

	?>
	<div class="ovabrw-advanced-settings offitravel-ovabrw-whitelist-accordion">
		<div class="advanced-header">
			<h3 class="advanced-label"><?php esc_html_e( 'Fechas disponibles', 'offitravel-ovabrw' ); ?></h3>
			<span aria-hidden="true" class="dashicons dashicons-arrow-up"></span>
			<span aria-hidden="true" class="dashicons dashicons-arrow-down"></span>
		</div>
		<div class="advanced-content">
			<p class="form-field" style="position: absolute;opacity: 0;pointer-events: none;height: 0;width: 0;">
				<label style="width: 100%;">
					<input type="hidden" name="offitravel_ovabrw_whitelist_enabled" value="yes" />
					<input type="checkbox" value="yes" checked="checked" disabled="disabled" />
					<?php esc_html_e( 'Usar solo estas fechas como salidas permitidas (el resto del calendario queda bloqueado). Desactiva el uso de “Unavailable time (UT)” para este producto. (Siempre activo)', 'offitravel-ovabrw' ); ?>
				</label>
			</p>
			<div class="ovabrw-form-field offitravel-ovabrw-whitelist-field">
				<table class="widefat offitravel-ovabrw-whitelist-table">
					<thead>
						<tr>
							<th style="width:49.5%;" class="ovabrw-required"><?php esc_html_e( 'Desde (inicio) ', 'offitravel-ovabrw' ); ?></th>
							<th style="width:49.5%;" class="ovabrw-optional"><?php esc_html_e( 'Hasta (fin) ', 'offitravel-ovabrw' ); ?></th>
							<th style="width:1%;"></th>
						</tr>
					</thead>
					<tbody id="offitravel-ovabrw-day-rows">
						<?php
						foreach ( $rows as $pair ) {
							offitravel_ovabrw_render_range_row( $pair['start'], $pair['end'], false );
						}
						?>
					</tbody>
					<tfoot>
						<tr>
							<th colspan="3">
								<button type="button" class="button" id="offitravel-ovabrw-add-day"><?php esc_html_e( 'Añadir día o rango', 'offitravel-ovabrw' ); ?></button>
							</th>
						</tr>
					</tfoot>
				</table>
				<!--<p class="description">
					<?php //esc_html_e( 'Indica solo “Desde” para un único día. Rellena también “Hasta” para incluir todos los días del intervalo (inclusive). El segundo campo es opcional.', 'offitravel-ovabrw' ); ?>
				</p>-->
			</div>
		</div>
	</div>
	<script type="text/template" id="offitravel-ovabrw-day-tpl">
		<?php offitravel_ovabrw_render_range_row( '', '', true ); ?>
	</script>
	<?php
}

/**
 * Formato jQuery UI datepicker según OVA (d-m-Y → dd-mm-yy).
 */
function offitravel_ovabrw_jquery_ui_date_format() {
	$f = function_exists( 'ovabrw_get_date_format' ) ? ovabrw_get_date_format() : 'd-m-Y';
	$map = array(
		'd-m-Y' => 'dd-mm-yy',
		'm/d/Y' => 'mm/dd/yy',
		'Y-m-d' => 'yy-mm-dd',
		'd/m/Y' => 'dd/mm/yy',
	);
	return isset( $map[ $f ] ) ? $map[ $f ] : 'dd-mm-yy';
}

/**
 * Una fila: inicio + fin opcional.
 *
 * @param string $start_val    Valor inicio.
 * @param string $end_val      Valor fin (puede estar vacío).
 * @param bool   $for_template Plantilla para JS (sin ids).
 */
function offitravel_ovabrw_render_range_row( $start_val, $end_val, $for_template = false ) {
	$uid_s = $for_template ? '' : ( function_exists( 'ovabrw_unique_id' ) ? ovabrw_unique_id( 'offitravel_wl_s_' ) : uniqid( 'offitravel_wls_' ) );
	$uid_e = $for_template ? '' : ( function_exists( 'ovabrw_unique_id' ) ? ovabrw_unique_id( 'offitravel_wl_e_' ) : uniqid( 'offitravel_wle_' ) );
	$ph    = function_exists( 'ovabrw_get_placeholder_date' ) ? ovabrw_get_placeholder_date() : 'DD-MM-YYYY';
	?>
	<tr class="offitravel-ovabrw-day-row">
		<td>
			<input
				type="text"
				<?php echo $uid_s ? 'id="' . esc_attr( $uid_s ) . '"' : ''; ?>
				class="short offitravel-wl-datepicker-input"
				name="offitravel_ovabrw_available_startdate[]"
				value="<?php echo esc_attr( $start_val ); ?>"
				placeholder="<?php echo esc_attr( $ph ); ?>"
				autocomplete="off"
				style="width:100%;"
			/>
		</td>
		<td>
			<input
				type="text"
				<?php echo $uid_e ? 'id="' . esc_attr( $uid_e ) . '"' : ''; ?>
				class="short offitravel-wl-datepicker-input offitravel-wl-datepicker-end"
				name="offitravel_ovabrw_available_enddate[]"
				value="<?php echo esc_attr( $end_val ); ?>"
				placeholder="<?php esc_attr_e( 'Opcional', 'offitravel-ovabrw' ); ?>"
				autocomplete="off"
				style="width:100%;"
			/>
		</td>
		<td>
			<button type="button" class="button offitravel-ovabrw-remove-day" aria-label="<?php esc_attr_e( 'Quitar', 'offitravel-ovabrw' ); ?>">&times;</button>
		</td>
	</tr>
	<?php
}

add_action( 'admin_enqueue_scripts', 'offitravel_ovabrw_whitelist_admin_assets', 20 );
function offitravel_ovabrw_whitelist_admin_assets( $hook ) {
	if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
		return;
	}
	if ( 'post-new.php' === $hook ) {
		if ( ! isset( $_GET['post_type'] ) || 'product' !== $_GET['post_type'] ) {
			return;
		}
	} else {
		global $post;
		if ( ! $post || 'product' !== $post->post_type ) {
			return;
		}
		$product = wc_get_product( $post->ID );
		if ( ! $product || ! $product->is_type( 'ovabrw_car_rental' ) ) {
			return;
		}
	}

	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_enqueue_style( 'jquery-ui-datepicker', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.13.2/themes/smoothness/jquery-ui.min.css', array(), '1.13.2' );

	$fmt_json = wp_json_encode( offitravel_ovabrw_jquery_ui_date_format() );
	$year_now = (int) date_i18n( 'Y', current_time( 'timestamp' ) );

	$inline_js = sprintf(
		<<<'JS'
window.offitravelWlDpFormat=%1$s;
window.offitravelWlYear=%2$d;
function offitravelWlGetDpOpts(){
	var y=window.offitravelWlYear;
	return{
		dateFormat:window.offitravelWlDpFormat||"dd-mm-yy",
		firstDay:1,
		changeMonth:true,
		changeYear:true,
		minDate:0,
		maxDate:new Date(y+2,11,31),
		yearRange:y+":"+(y+2)
	};
}
function offitravelWlInitRow($row){
	var opts=offitravelWlGetDpOpts();
	$row.find(".offitravel-wl-datepicker-input").each(function(){
		var $i=jQuery(this);
		if($i.data("offitravel-wl-dp")){return;}
		$i.data("offitravel-wl-dp",1);
		$i.datepicker(opts);
	});
}
jQuery(function($){
	offitravelWlInitRow($("#offitravel-ovabrw-day-rows"));
	$(document).on("click","#offitravel-ovabrw-add-day",function(){
		var $r=$($("#offitravel-ovabrw-day-tpl").html());
		$("#offitravel-ovabrw-day-rows").append($r);
		offitravelWlInitRow($r);
	});
	$(document).on("click",".offitravel-ovabrw-remove-day",function(){
		var $tb=$("#offitravel-ovabrw-day-rows");
		if($tb.find("tr").length<2){
			$(this).closest("tr").find("input").val("").trigger("change");
			return;
		}
		$(this).closest("tr").remove();
	});
});
JS
		,
		$fmt_json,
		$year_now
	);

	wp_add_inline_script( 'jquery-ui-datepicker', $inline_js, 'after' );
}

/**
 * Guardar metas.
 */
add_action( 'woocommerce_process_product_meta', 'offitravel_ovabrw_save_whitelist_meta', 15, 2 );
function offitravel_ovabrw_save_whitelist_meta( $post_id, $post ) {
	if ( ! isset( $_POST['offitravel_ovabrw_whitelist_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['offitravel_ovabrw_whitelist_nonce'] ) ), 'offitravel_ovabrw_whitelist_save' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_product', $post_id ) ) {
		return;
	}

	$enabled = 'yes';
	update_post_meta( $post_id, OFFITRAVEL_OVABRW_WHITELIST_META_ENABLED, $enabled );

	$starts_in = isset( $_POST['offitravel_ovabrw_available_startdate'] ) ? wp_unslash( $_POST['offitravel_ovabrw_available_startdate'] ) : array();
	$ends_in   = isset( $_POST['offitravel_ovabrw_available_enddate'] ) ? wp_unslash( $_POST['offitravel_ovabrw_available_enddate'] ) : array();

	$starts_clean = array();
	$ends_clean   = array();

	if ( is_array( $starts_in ) ) {
		foreach ( $starts_in as $i => $s ) {
			$s = trim( sanitize_text_field( $s ) );
			$e = isset( $ends_in[ $i ] ) ? trim( sanitize_text_field( $ends_in[ $i ] ) ) : '';
			if ( '' === $s ) {
				continue;
			}
			if ( function_exists( 'ovabrw_format_date' ) ) {
				$s = ovabrw_format_date( $s );
				if ( '' !== $e ) {
					$e = ovabrw_format_date( $e );
				}
			}
			if ( '' !== $e && strtotime( $e ) && strtotime( $s ) && strtotime( $e ) < strtotime( $s ) ) {
				continue;
			}
			$starts_clean[] = $s;
			$ends_clean[]   = $e;
		}
	}

	if ( ! empty( $starts_clean ) ) {
		update_post_meta( $post_id, OFFITRAVEL_OVABRW_WHITELIST_META_START, $starts_clean );
		update_post_meta( $post_id, OFFITRAVEL_OVABRW_WHITELIST_META_END, $ends_clean );
	} else {
		delete_post_meta( $post_id, OFFITRAVEL_OVABRW_WHITELIST_META_START );
		delete_post_meta( $post_id, OFFITRAVEL_OVABRW_WHITELIST_META_END );
	}

	delete_post_meta( $post_id, OFFITRAVEL_OVABRW_WHITELIST_META_DATES );
}
