<?php
/**
 * Modo habitaciones: total de personas = ovabrw_adults (precio OVA).
 *
 * @package tripgo-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$product_id = tripgo_get_meta_data( 'id', $args, get_the_id() );

$product = wc_get_product( $product_id );
if ( ! $product || ! $product->is_type( 'ovabrw_car_rental' ) ) {
	return;
}

$settings = function_exists( 'offitravel_ovabrw_get_room_settings' )
	? offitravel_ovabrw_get_room_settings( $product_id )
	: array(
		'max_rooms'    => 10,
		'max_per_room' => 4,
	);

$max_total_guest = tripgo_get_post_meta( $product_id, 'max_total_guest' );

$min_adults = tripgo_get_post_meta( $product_id, 'adults_min' );
$min_adults = apply_filters( 'ovabrw_min_adults', $min_adults, $product_id );
if ( ! $min_adults ) {
	$min_adults = 0;
}

$max_adults = absint( tripgo_get_post_meta( $product_id, 'adults_max' ) );

$adult_price = tripgo_get_price_product( $product_id );
$show_price_adults = get_option( 'ova_brw_booking_form_show_price_beside_adults', 'yes' );
$label_adults      = get_option( 'ova_brw_label_beside_adults', '' );

$number_from_get = absint( tripgo_get_meta_data( 'ovabrw_adults', $_GET, $min_adults ) );
if ( $number_from_get < $min_adults ) {
	$number_from_get = (int) $min_adults;
}
if ( $max_adults > 0 && $number_from_get > $max_adults ) {
	$number_from_get = $max_adults;
}

$distribution = function_exists( 'offitravel_ovabrw_room_distribution_for_total' )
	? offitravel_ovabrw_room_distribution_for_total(
		$number_from_get,
		$settings['max_per_room'],
		$settings['max_rooms'],
		(int) $min_adults,
		(int) $max_adults
	)
	: array( max( 1, (int) $min_adults ) );

$room_count = count( $distribution );
$gueststotal  = array_sum( $distribution );

$initial_json = wp_json_encode( array_map( 'absint', $distribution ) );

?>
<div class="rental_item offitravel-guests-rooms">
	<h3 class="ovabrw-label ovabrw-required">
		<?php esc_html_e( 'Huespedes', 'tripgo' ); ?>
	</h3>
	<div
		class="ovabrw-wrapper-guestspicker only-show-adults offitravel-room-mode-wrapper"
		data-max-rooms="<?php echo esc_attr( $settings['max_rooms'] ); ?>"
		data-max-per-room="<?php echo esc_attr( $settings['max_per_room'] ); ?>"
		data-min-adults="<?php echo esc_attr( (int) $min_adults ); ?>"
		data-max-adults="<?php echo esc_attr( (int) $max_adults ); ?>"
		data-initial-rooms="<?php echo esc_attr( $initial_json ); ?>"
	>
		<input
			type="hidden"
			name="ovabrw_max_total_guest"
			value="<?php echo esc_attr( $max_total_guest ); ?>"
		/>
		<div class="ovabrw-guestspicker">
			<div class="guestspicker">
				<span class="gueststotal"><?php echo esc_html( $gueststotal ); ?></span>
			</div>
			<span class="ovabrw-guest-loading">
				<i aria-hidden="true" class="flaticon flaticon-spinner-of-dots"></i>
			</span>
		</div>
		<div class="ovabrw-guestspicker-content offitravel-room-mode-fields">
			<p class="offitravel-room-count-row">
				<label for="offitravel_room_count" class="ovabrw-label"><?php esc_html_e( 'Número de habitaciones', 'offitravel-ovabrw' ); ?></label>
				<select name="offitravel_room_count" id="offitravel_room_count" class="offitravel-room-count-select">
					<?php
					for ( $r = 1; $r <= $settings['max_rooms']; $r++ ) {
						printf(
							'<option value="%1$d"%3$s>%2$d</option>',
							(int) $r,
							(int) $r,
							selected( $room_count, $r, false )
						);
					}
					?>
				</select>
			</p>
			<div id="offitravel-room-rows" class="offitravel-room-rows" aria-live="polite"></div>
			<?php if ( 'yes' === $show_price_adults && ! empty( $adult_price['regular_price'] ) ) : ?>
				<p class="description offitravel-room-price-note">
					<?php if ( $label_adults ) : ?>
						<span class="guests-labels beside_adults"><?php echo esc_html( $label_adults ); ?></span>
					<?php endif; ?>
					<span class="guests-price adults-price"><?php echo wc_price( $adult_price['regular_price'] ); ?></span>
					<!-- <span class="offitravel-room-price-hint"><?php esc_html_e( 'Precio por persona y dia (total segun personas abajo).', 'offitravel-ovabrw' ); ?></span> -->
					<span class="offitravel-room-price-hint"><?php esc_html_e( 'por persona para el circuito completo.', 'offitravel-ovabrw' ); ?></span>
				</p>
			<?php endif; ?>
			<?php
			tripgo_text_input(
				array(
					'type'     => 'hidden',
					'class'    => 'guests-input ovabrw_adults',
					'name'     => 'ovabrw_adults',
					'value'    => $gueststotal,
					'required' => true,
					'attrs'    => array(
						'min'        => $min_adults,
						'max'        => $max_adults ? $max_adults : '',
						'data-label' => esc_html__( 'Personas', 'ova-brw' ),
						'data-name'  => 'ovabrw_adults',
					),
				)
			);
			?>
			<input type="hidden" name="ovabrw_childrens" value="0" />
			<input type="hidden" name="ovabrw_babies" value="0" />
		</div>
		<?php if ( 'yes' === get_option( 'ovabrw_guest_info', '' ) ) : ?>
			<div class="ovabrw-guest-info">
				<div class="guest-info-heading">
					<?php esc_html_e( 'Introduce la informacion de los huespedes', 'ova-brw' ); ?>
				</div>
				<div class="guest-info-accordion"></div>
			</div>
		<?php endif; ?>
	</div>
</div>
