<?php
/**
 * Addons reutilizables (estilo extras de producto): precio + modalidad + asignación a tours OVA.
 *
 * Frontend: wp-content/mu-plugins/offitravel-product-addons-front.js
 * Extensión payload habitaciones: offitravelPrdAddonAugmentPayload en offitravel-ovabrw-room-mode.php
 *
 * @package Offitravel
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const OFFITRAVEL_ADDON_PT            = 'offitravel_prd_addon';
const OFFITRAVEL_ADDON_META_PRICE    = '_offitravel_addon_price';
const OFFITRAVEL_ADDON_META_BILLING  = '_offitravel_addon_billing';
const OFFITRAVEL_ADDON_META_PRODUCTS = '_offitravel_addon_product_ids';
const OFFITRAVEL_ADDON_META_PUBLIC_LABEL = '_offitravel_addon_public_label';
const OFFITRAVEL_ADDON_META_PRICE_MODEL  = '_offitravel_addon_price_model';
const OFFITRAVEL_ADDON_META_AGE_RULES    = '_offitravel_addon_age_rules';

const OFFITRAVEL_ADDON_PRICE_MODEL_FIXED        = 'fixed';
const OFFITRAVEL_ADDON_PRICE_MODEL_TRAVELER_AGE = 'traveler_age';

/**
 * @param mixed $raw
 * @return string booking|person|room
 */
function offitravel_addon_normalize_billing( $raw ) {
	$raw = is_string( $raw ) ? strtolower( trim( $raw ) ) : '';
	return in_array( $raw, array( 'person', 'booking', 'room' ), true ) ? $raw : 'person';
}

/**
 * Normalize the stored price model while preserving legacy fixed-price services.
 *
 * Services created before the model metadata existed have no value. They must
 * continue behaving as fixed-price services without requiring a migration.
 *
 * @param mixed $raw Stored model value.
 * @return string One of the OFFITRAVEL_ADDON_PRICE_MODEL_* constants.
 */
function offitravel_addon_normalize_price_model( $raw ) {
	$raw = is_string( $raw ) ? sanitize_key( $raw ) : '';
	return OFFITRAVEL_ADDON_PRICE_MODEL_TRAVELER_AGE === $raw
		? OFFITRAVEL_ADDON_PRICE_MODEL_TRAVELER_AGE
		: OFFITRAVEL_ADDON_PRICE_MODEL_FIXED;
}

/**
 * Determine whether an add-on may use the current fixed-price public flow.
 *
 * Traveler-age services use their own public rendering, validation and
 * calculation flow. This boundary prevents them from leaking into the
 * independent fixed-price flow. Missing model metadata remains fixed for
 * backward compatibility.
 *
 * @param int $addon_id Add-on post ID.
 * @return bool True only for fixed-price or legacy add-ons.
 */
function offitravel_addon_uses_fixed_price( $addon_id ) {
	return OFFITRAVEL_ADDON_PRICE_MODEL_FIXED === offitravel_addon_normalize_price_model(
		get_post_meta( absint( $addon_id ), OFFITRAVEL_ADDON_META_PRICE_MODEL, true )
	);
}

/**
 * Resolve the public label configured for an add-on.
 *
 * The internal post title remains the fallback for all legacy services. This
 * label is used by the traveler-age form, cart and order; fixed legacy
 * checkboxes retain their existing internal-title behavior.
 *
 * @param int|WP_Post $addon Add-on post or ID.
 * @return string Public label, or the internal title when no label is stored.
 */
function offitravel_addon_get_public_label( $addon ) {
	$post = get_post( $addon );
	if ( ! $post instanceof WP_Post || OFFITRAVEL_ADDON_PT !== $post->post_type ) {
		return '';
	}

	$label = sanitize_text_field( (string) get_post_meta( $post->ID, OFFITRAVEL_ADDON_META_PUBLIC_LABEL, true ) );
	return '' !== $label ? $label : get_the_title( $post );
}

/**
 * Normalize a non-negative monetary value using WooCommerce precision.
 *
 * @param mixed  $raw        Submitted value.
 * @param string $error_code Error code returned for invalid values.
 * @param string $message    Error message returned for invalid values.
 * @return string|WP_Error Decimal string or validation error.
 */
function offitravel_addon_validate_admin_price( $raw, $error_code, $message ) {
	$value = is_scalar( $raw ) ? trim( (string) $raw ) : '';
	if ( '' === $value ) {
		return new WP_Error( $error_code, $message );
	}

	$decimal = wc_format_decimal( $value, wc_get_price_decimals() );
	if ( '' === $decimal || ! is_numeric( $decimal ) || (float) $decimal < 0 ) {
		return new WP_Error( $error_code, $message );
	}

	return $decimal;
}

/**
 * Validate and normalize age-based pricing rules.
 *
 * Ages are non-negative integers without a commercial maximum. Gaps are
 * permitted because continuous coverage is not a generic business rule. Age
 * ranges may not overlap and only the last range may be open-ended.
 *
 * @param mixed $raw_rules Submitted rule rows.
 * @return array<int,array{min_age:int,max_age:?int,price:string}>|WP_Error
 */
function offitravel_addon_validate_age_rules( $raw_rules ) {
	if ( ! is_array( $raw_rules ) || empty( $raw_rules ) ) {
		return new WP_Error(
			'offitravel_addon_age_rules_required',
			__( 'Añade al menos un tramo de edad.', 'offitravel-addons' )
		);
	}

	$rules = array();
	foreach ( $raw_rules as $raw_rule ) {
		if ( ! is_array( $raw_rule ) ) {
			return new WP_Error( 'offitravel_addon_invalid_age', __( 'Las edades deben ser números enteros no negativos.', 'offitravel-addons' ) );
		}

		$min_raw = isset( $raw_rule['min_age'] ) && is_scalar( $raw_rule['min_age'] ) ? trim( (string) $raw_rule['min_age'] ) : '';
		$max_raw = isset( $raw_rule['max_age'] ) && is_scalar( $raw_rule['max_age'] ) ? trim( (string) $raw_rule['max_age'] ) : '';
		if ( ! preg_match( '/^\d+$/', $min_raw ) || ( '' !== $max_raw && ! preg_match( '/^\d+$/', $max_raw ) ) ) {
			return new WP_Error( 'offitravel_addon_invalid_age', __( 'Las edades deben ser números enteros no negativos.', 'offitravel-addons' ) );
		}

		$min_age = (int) $min_raw;
		$max_age = '' === $max_raw ? null : (int) $max_raw;
		if ( null !== $max_age && $max_age < $min_age ) {
			return new WP_Error( 'offitravel_addon_invalid_age_range', __( 'La edad máxima no puede ser menor que la edad mínima.', 'offitravel-addons' ) );
		}

		$price = offitravel_addon_validate_admin_price(
			isset( $raw_rule['price'] ) ? $raw_rule['price'] : '',
			'offitravel_addon_invalid_rule_price',
			__( 'Cada tramo debe tener un precio válido igual o superior a cero.', 'offitravel-addons' )
		);
		if ( is_wp_error( $price ) ) {
			return $price;
		}

		$rules[] = array(
			'min_age' => $min_age,
			'max_age' => $max_age,
			'price'   => $price,
		);
	}

	usort(
		$rules,
		static function ( $left, $right ) {
			return $left['min_age'] <=> $right['min_age'];
		}
	);

	$previous_max = null;
	$has_previous = false;
	foreach ( $rules as $rule ) {
		if ( $has_previous && ( null === $previous_max || $rule['min_age'] <= $previous_max ) ) {
			return new WP_Error( 'offitravel_addon_overlapping_age_ranges', __( 'Los tramos de edad no pueden solaparse.', 'offitravel-addons' ) );
		}
		$previous_max = $rule['max_age'];
		$has_previous = true;
	}

	return $rules;
}

/**
 * Validate and normalize the administrative configuration payload.
 *
 * This function has no persistence side effects, allowing the same server-side
 * rules to protect saves and regression tests. Product assignments retain their
 * submitted order for backward compatibility.
 *
 * @param array<string,mixed> $payload Unslashed administrative request data.
 * @return array{public_label:string,price_model:string,price:?string,billing:string,age_rules:array,product_ids:int[]}|WP_Error
 */
function offitravel_addon_validate_admin_payload( array $payload ) {
	$model_raw = isset( $payload[ OFFITRAVEL_ADDON_META_PRICE_MODEL ] ) && is_scalar( $payload[ OFFITRAVEL_ADDON_META_PRICE_MODEL ] )
		? sanitize_key( (string) $payload[ OFFITRAVEL_ADDON_META_PRICE_MODEL ] )
		: '';
	if ( '' !== $model_raw && ! in_array( $model_raw, array( OFFITRAVEL_ADDON_PRICE_MODEL_FIXED, OFFITRAVEL_ADDON_PRICE_MODEL_TRAVELER_AGE ), true ) ) {
		return new WP_Error( 'offitravel_addon_invalid_price_model', __( 'El modelo de precio seleccionado no es válido.', 'offitravel-addons' ) );
	}
	$model = offitravel_addon_normalize_price_model( $model_raw );

	$billing_raw = isset( $payload[ OFFITRAVEL_ADDON_META_BILLING ] ) && is_scalar( $payload[ OFFITRAVEL_ADDON_META_BILLING ] )
		? sanitize_key( (string) $payload[ OFFITRAVEL_ADDON_META_BILLING ] )
		: '';
	if ( OFFITRAVEL_ADDON_PRICE_MODEL_TRAVELER_AGE === $model ) {
		if ( '' !== $billing_raw && 'person' !== $billing_raw ) {
			return new WP_Error( 'offitravel_addon_age_requires_person_billing', __( 'El precio por edad sólo puede facturarse por viajero.', 'offitravel-addons' ) );
		}
		$billing = 'person';
		$price   = null;
		$rules   = offitravel_addon_validate_age_rules(
			isset( $payload[ OFFITRAVEL_ADDON_META_AGE_RULES ] ) ? $payload[ OFFITRAVEL_ADDON_META_AGE_RULES ] : array()
		);
		if ( is_wp_error( $rules ) ) {
			return $rules;
		}
	} else {
		if ( '' !== $billing_raw && ! in_array( $billing_raw, array( 'person', 'room', 'booking' ), true ) ) {
			return new WP_Error( 'offitravel_addon_invalid_billing', __( 'La modalidad seleccionada no es válida.', 'offitravel-addons' ) );
		}
		$billing = offitravel_addon_normalize_billing( $billing_raw );
		$price   = offitravel_addon_validate_admin_price(
			isset( $payload[ OFFITRAVEL_ADDON_META_PRICE ] ) ? $payload[ OFFITRAVEL_ADDON_META_PRICE ] : '',
			'offitravel_addon_invalid_fixed_price',
			__( 'Indica un precio fijo válido igual o superior a cero.', 'offitravel-addons' )
		);
		if ( is_wp_error( $price ) ) {
			return $price;
		}
		$rules = array();
	}

	$product_ids = isset( $payload[ OFFITRAVEL_ADDON_META_PRODUCTS ] ) && is_array( $payload[ OFFITRAVEL_ADDON_META_PRODUCTS ] )
		? array_map( 'absint', $payload[ OFFITRAVEL_ADDON_META_PRODUCTS ] )
		: array();
	$product_ids = array_values( array_filter( array_unique( $product_ids ) ) );

	return array(
		'public_label' => isset( $payload[ OFFITRAVEL_ADDON_META_PUBLIC_LABEL ] ) && is_scalar( $payload[ OFFITRAVEL_ADDON_META_PUBLIC_LABEL ] )
			? sanitize_text_field( (string) $payload[ OFFITRAVEL_ADDON_META_PUBLIC_LABEL ] )
			: '',
		'price_model'  => $model,
		'price'        => $price,
		'billing'      => $billing,
		'age_rules'    => $rules,
		'product_ids'  => $product_ids,
	);
}

/**
 * @param int $product_id
 * @return WP_Post[]
 */
function offitravel_addon_posts_for_product_by_model( $product_id, $price_model ) {
	$product_id = absint( $product_id );
	if ( ! $product_id ) {
		return array();
	}
	$price_model = offitravel_addon_normalize_price_model( $price_model );
	$q = new WP_Query(
		array(
			'post_type'              => OFFITRAVEL_ADDON_PT,
			'post_status'            => 'publish',
			'posts_per_page'         => -1,
			'orderby'                => 'menu_order title',
			'order'                  => 'ASC',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);
	$out = array();
	foreach ( $q->posts as $post ) {
		$stored_model = offitravel_addon_normalize_price_model(
			get_post_meta( $post->ID, OFFITRAVEL_ADDON_META_PRICE_MODEL, true )
		);
		if ( $price_model !== $stored_model ) {
			continue;
		}
		$allow = get_post_meta( $post->ID, OFFITRAVEL_ADDON_META_PRODUCTS, true );
		$allow = is_array( $allow ) ? array_map( 'absint', $allow ) : array();
		if ( $allow && in_array( $product_id, $allow, true ) ) {
			$out[] = $post;
		}
	}
	wp_reset_postdata();
	return $out;
}

/**
 * Return fixed-price add-ons assigned to a product.
 *
 * Missing model metadata remains fixed for backward compatibility.
 *
 * @param int $product_id Product ID.
 * @return WP_Post[]
 */
function offitravel_addon_posts_for_product( $product_id ) {
	return offitravel_addon_posts_for_product_by_model( $product_id, OFFITRAVEL_ADDON_PRICE_MODEL_FIXED );
}

/**
 * Return traveler-age add-ons assigned to a product.
 *
 * @param int $product_id Product ID.
 * @return WP_Post[]
 */
function offitravel_addon_age_posts_for_product( $product_id ) {
	return offitravel_addon_posts_for_product_by_model( $product_id, OFFITRAVEL_ADDON_PRICE_MODEL_TRAVELER_AGE );
}

/**
 * Validate that a published traveler-age service is assigned to the product.
 *
 * @param int $addon_id   Add-on post ID.
 * @param int $product_id Product ID.
 * @return WP_Post|WP_Error Valid add-on post or a validation error.
 */
function offitravel_addon_validate_age_service( $addon_id, $product_id ) {
	$addon_id   = absint( $addon_id );
	$product_id = absint( $product_id );
	$post       = get_post( $addon_id );
	if ( ! $addon_id || ! $product_id || ! $post instanceof WP_Post || OFFITRAVEL_ADDON_PT !== $post->post_type || 'publish' !== $post->post_status ) {
		return new WP_Error( 'offitravel_addon_invalid_traveler_age_service', __( 'El seguro de viaje seleccionado no es válido para este circuito.', 'offitravel-addons' ) );
	}
	if ( OFFITRAVEL_ADDON_PRICE_MODEL_TRAVELER_AGE !== offitravel_addon_normalize_price_model( get_post_meta( $addon_id, OFFITRAVEL_ADDON_META_PRICE_MODEL, true ) ) ) {
		return new WP_Error( 'offitravel_addon_invalid_traveler_age_service', __( 'El seguro de viaje seleccionado no es válido para este circuito.', 'offitravel-addons' ) );
	}

	$allowed = get_post_meta( $addon_id, OFFITRAVEL_ADDON_META_PRODUCTS, true );
	$allowed = is_array( $allowed ) ? array_map( 'absint', $allowed ) : array();
	if ( ! in_array( $product_id, $allowed, true ) ) {
		return new WP_Error( 'offitravel_addon_invalid_traveler_age_service', __( 'El seguro de viaje seleccionado no es válido para este circuito.', 'offitravel-addons' ) );
	}

	return $post;
}

/**
 * Format an amount as a database-safe WooCommerce decimal snapshot.
 *
 * @param mixed $amount Monetary amount.
 * @return string Decimal string with the configured WooCommerce precision.
 */
function offitravel_addon_money_snapshot( $amount ) {
	$decimals = wc_get_price_decimals();
	$decimal  = wc_format_decimal( (string) $amount, $decimals );
	return number_format( (float) $decimal, $decimals, '.', '' );
}

/**
 * Resolve the real traveler positions from room-mode occupancy.
 *
 * Room and traveler positions are one-based so the submitted structure maps
 * directly to the labels shown to customers and to the order snapshot.
 *
 * @param int                 $product_id Product ID.
 * @param array<string,mixed> $context    Cart or request occupancy data.
 * @return array<int,array{room:int,position:int,traveler:int}>|WP_Error
 */
function offitravel_addon_age_expected_slots( $product_id, array $context ) {
	$product_id = absint( $product_id );
	$room_count = isset( $context['offitravel_room_count'] ) ? absint( $context['offitravel_room_count'] ) : 0;
	$people     = isset( $context['offitravel_room_people'] ) && is_array( $context['offitravel_room_people'] )
		? array_values( $context['offitravel_room_people'] )
		: array();

	if ( function_exists( 'offitravel_ovabrw_room_mode_enabled' ) && offitravel_ovabrw_room_mode_enabled( $product_id ) ) {
		$settings = offitravel_ovabrw_get_room_settings( $product_id );
		if ( $room_count < 1 || $room_count > (int) $settings['max_rooms'] || count( $people ) !== $room_count ) {
			return new WP_Error( 'offitravel_addon_invalid_traveler_structure', __( 'La distribución de viajeros por habitaciones no es válida.', 'offitravel-addons' ) );
		}
	} elseif ( $room_count < 1 || count( $people ) !== $room_count ) {
		$total      = isset( $context['ovabrw_adults'] ) ? absint( $context['ovabrw_adults'] ) : 0;
		$room_count = $total > 0 ? 1 : 0;
		$people     = $total > 0 ? array( $total ) : array();
	}

	if ( $room_count < 1 || count( $people ) !== $room_count ) {
		return new WP_Error( 'offitravel_addon_invalid_traveler_structure', __( 'No se ha podido determinar el número de viajeros.', 'offitravel-addons' ) );
	}

	$slots          = array();
	$global_traveler = 0;
	$max_per_room   = 0;
	if ( function_exists( 'offitravel_ovabrw_room_mode_enabled' ) && offitravel_ovabrw_room_mode_enabled( $product_id ) ) {
		$settings     = offitravel_ovabrw_get_room_settings( $product_id );
		$max_per_room = (int) $settings['max_per_room'];
	}
	foreach ( $people as $room_offset => $occupants_raw ) {
		$occupants = is_scalar( $occupants_raw ) && preg_match( '/^\d+$/', trim( (string) $occupants_raw ) ) ? (int) $occupants_raw : 0;
		if ( $occupants < 1 || ( $max_per_room > 0 && $occupants > $max_per_room ) ) {
			return new WP_Error( 'offitravel_addon_invalid_traveler_structure', __( 'La ocupación de una habitación no es válida.', 'offitravel-addons' ) );
		}
		for ( $position = 1; $position <= $occupants; ++$position ) {
			++$global_traveler;
			$slots[] = array(
				'room'     => $room_offset + 1,
				'position' => $position,
				'traveler' => $global_traveler,
			);
		}
	}

	$posted_total = isset( $context['ovabrw_adults'] ) ? absint( $context['ovabrw_adults'] ) : 0;
	if ( $posted_total > 0 && $posted_total !== $global_traveler ) {
		return new WP_Error( 'offitravel_addon_invalid_traveler_structure', __( 'El total de viajeros no coincide con la ocupación indicada.', 'offitravel-addons' ) );
	}

	return $slots;
}

/**
 * Determine whether a submitted per-traveler checkbox is selected.
 *
 * @param mixed $raw Submitted checkbox value.
 * @return bool
 */
function offitravel_addon_age_is_selected( $raw ) {
	if ( true === $raw || 1 === $raw ) {
		return true;
	}
	$value = is_scalar( $raw ) ? strtolower( trim( (string) $raw ) ) : '';
	return in_array( $value, array( '1', 'yes', 'on', 'true' ), true );
}

/**
 * Match a non-negative integer age against normalized rules.
 *
 * @param int                                                     $age   Traveler age.
 * @param array<int,array{min_age:int,max_age:?int,price:string}> $rules Normalized rules.
 * @return array{min_age:int,max_age:?int,price:string}|null
 */
function offitravel_addon_match_age_rule( $age, array $rules ) {
	foreach ( $rules as $rule ) {
		if ( $age >= (int) $rule['min_age'] && ( null === $rule['max_age'] || $age <= (int) $rule['max_age'] ) ) {
			return $rule;
		}
	}
	return null;
}

/**
 * Validate traveler selections and build a server-owned pricing snapshot.
 *
 * Only selection and age are read from the request. Prices, labels, product
 * assignments and rules are loaded again from WordPress metadata.
 *
 * @param int                 $product_id Product ID.
 * @param mixed               $raw        Nested traveler-age request payload.
 * @param array<string,mixed> $context    Occupancy context.
 * @return array{version:int,product_id:int,services:array<int,array>,total:string}|WP_Error
 */
function offitravel_addon_calculate_traveler_age( $product_id, $raw, array $context ) {
	$product_id = absint( $product_id );
	$slots      = offitravel_addon_age_expected_slots( $product_id, $context );
	if ( is_wp_error( $slots ) ) {
		return $slots;
	}

	$slot_map = array();
	foreach ( $slots as $slot ) {
		$slot_map[ $slot['room'] . ':' . $slot['position'] ] = $slot;
	}

	$snapshot = array(
		'version'    => 1,
		'product_id' => $product_id,
		'services'   => array(),
		'total'      => offitravel_addon_money_snapshot( 0 ),
	);
	if ( empty( $raw ) ) {
		return $snapshot;
	}
	if ( ! is_array( $raw ) ) {
		return new WP_Error( 'offitravel_addon_invalid_traveler_age_payload', __( 'Los datos del seguro de viaje no son válidos.', 'offitravel-addons' ) );
	}

	$grand_total = 0.0;
	foreach ( $raw as $addon_id_raw => $rooms ) {
		$addon_id = is_scalar( $addon_id_raw ) && preg_match( '/^\d+$/', (string) $addon_id_raw ) ? absint( $addon_id_raw ) : 0;
		$addon    = offitravel_addon_validate_age_service( $addon_id, $product_id );
		if ( is_wp_error( $addon ) || ! is_array( $rooms ) ) {
			return is_wp_error( $addon ) ? $addon : new WP_Error( 'offitravel_addon_invalid_traveler_age_payload', __( 'Los datos del seguro de viaje no son válidos.', 'offitravel-addons' ) );
		}

		$rules = offitravel_addon_validate_age_rules( get_post_meta( $addon_id, OFFITRAVEL_ADDON_META_AGE_RULES, true ) );
		if ( is_wp_error( $rules ) ) {
			return new WP_Error( 'offitravel_addon_invalid_traveler_age_rules', __( 'El seguro de viaje no tiene tarifas válidas.', 'offitravel-addons' ) );
		}

		$service_total = 0.0;
		$travelers     = array();
		foreach ( $rooms as $room_raw => $positions ) {
			$room = is_scalar( $room_raw ) && preg_match( '/^\d+$/', (string) $room_raw ) ? (int) $room_raw : 0;
			if ( ! $room || ! is_array( $positions ) ) {
				return new WP_Error( 'offitravel_addon_invalid_traveler_age_payload', __( 'Los datos del seguro de viaje no son válidos.', 'offitravel-addons' ) );
			}
			foreach ( $positions as $position_raw => $selection ) {
				$position = is_scalar( $position_raw ) && preg_match( '/^\d+$/', (string) $position_raw ) ? (int) $position_raw : 0;
				if ( ! is_array( $selection ) || ! offitravel_addon_age_is_selected( isset( $selection['selected'] ) ? $selection['selected'] : null ) ) {
					continue;
				}

				$slot_key = $room . ':' . $position;
				if ( ! $position || ! isset( $slot_map[ $slot_key ] ) ) {
					return new WP_Error( 'offitravel_addon_invalid_traveler_position', __( 'La posición de un viajero asegurado no es válida.', 'offitravel-addons' ) );
				}
				$age_raw = isset( $selection['age'] ) && is_scalar( $selection['age'] ) ? trim( (string) $selection['age'] ) : '';
				if ( ! preg_match( '/^\d+$/', $age_raw ) ) {
					return new WP_Error( 'offitravel_addon_invalid_traveler_age', __( 'Indica una edad entera no negativa para cada viajero asegurado.', 'offitravel-addons' ) );
				}
				$age  = (int) $age_raw;
				$rule = offitravel_addon_match_age_rule( $age, $rules );
				if ( null === $rule ) {
					return new WP_Error( 'offitravel_addon_age_not_covered', __( 'La edad indicada no tiene una tarifa de seguro configurada.', 'offitravel-addons' ) );
				}

				$rate          = offitravel_addon_money_snapshot( $rule['price'] );
				$service_total += (float) $rate;
				$travelers[]    = array(
					'traveler' => (int) $slot_map[ $slot_key ]['traveler'],
					'room'     => $room,
					'position' => $position,
					'age'      => $age,
					'rate'     => $rate,
					'subtotal' => $rate,
				);
			}
		}

		if ( $travelers ) {
			usort(
				$travelers,
				static function ( $left, $right ) {
					return $left['traveler'] <=> $right['traveler'];
				}
			);
			$service_total                  = (float) offitravel_addon_money_snapshot( $service_total );
			$grand_total                   += $service_total;
			$snapshot['services'][ $addon_id ] = array(
				'service_id' => $addon_id,
				'label'      => offitravel_addon_get_public_label( $addon ),
				'rules'      => $rules,
				'travelers'  => $travelers,
				'total'      => offitravel_addon_money_snapshot( $service_total ),
			);
		}
	}

	$snapshot['total'] = offitravel_addon_money_snapshot( $grand_total );
	return $snapshot;
}

/**
 * Normalize a server-created traveler-age snapshot and recalculate its totals.
 *
 * Rules stored in the snapshot are the historical source of truth. Derived
 * rates and totals are rebuilt so corrupted session values cannot accumulate
 * or alter the amount.
 *
 * @param mixed $raw_snapshot       Stored snapshot.
 * @param int   $expected_product_id Optional product ID that must match.
 * @return array{version:int,product_id:int,services:array<int,array>,total:string}|WP_Error
 */
function offitravel_addon_normalize_age_snapshot( $raw_snapshot, $expected_product_id = 0 ) {
	if ( ! is_array( $raw_snapshot ) ) {
		return new WP_Error( 'offitravel_addon_invalid_traveler_age_snapshot', __( 'Los datos guardados del seguro de viaje no son válidos.', 'offitravel-addons' ) );
	}
	$product_id = isset( $raw_snapshot['product_id'] ) ? absint( $raw_snapshot['product_id'] ) : 0;
	if ( ! $product_id || ( $expected_product_id && absint( $expected_product_id ) !== $product_id ) || empty( $raw_snapshot['services'] ) || ! is_array( $raw_snapshot['services'] ) ) {
		return new WP_Error( 'offitravel_addon_invalid_traveler_age_snapshot', __( 'Los datos guardados del seguro de viaje no son válidos.', 'offitravel-addons' ) );
	}

	$normalized = array(
		'version'    => 1,
		'product_id' => $product_id,
		'services'   => array(),
		'total'      => offitravel_addon_money_snapshot( 0 ),
	);
	$grand_total = 0.0;
	foreach ( $raw_snapshot['services'] as $service_key => $service ) {
		if ( ! is_array( $service ) ) {
			return new WP_Error( 'offitravel_addon_invalid_traveler_age_snapshot', __( 'Los datos guardados del seguro de viaje no son válidos.', 'offitravel-addons' ) );
		}
		$service_id = isset( $service['service_id'] ) ? absint( $service['service_id'] ) : absint( $service_key );
		$rules      = offitravel_addon_validate_age_rules( isset( $service['rules'] ) ? $service['rules'] : array() );
		if ( ! $service_id || is_wp_error( $rules ) || empty( $service['travelers'] ) || ! is_array( $service['travelers'] ) ) {
			return new WP_Error( 'offitravel_addon_invalid_traveler_age_snapshot', __( 'Los datos guardados del seguro de viaje no son válidos.', 'offitravel-addons' ) );
		}

		$travelers    = array();
		$seen          = array();
		$service_total = 0.0;
		foreach ( $service['travelers'] as $traveler ) {
			if ( ! is_array( $traveler ) ) {
				return new WP_Error( 'offitravel_addon_invalid_traveler_age_snapshot', __( 'Los datos guardados del seguro de viaje no son válidos.', 'offitravel-addons' ) );
			}
			$room     = isset( $traveler['room'] ) ? absint( $traveler['room'] ) : 0;
			$position = isset( $traveler['position'] ) ? absint( $traveler['position'] ) : 0;
			$ordinal  = isset( $traveler['traveler'] ) ? absint( $traveler['traveler'] ) : 0;
			$age_raw  = isset( $traveler['age'] ) && is_scalar( $traveler['age'] ) ? trim( (string) $traveler['age'] ) : '';
			$key      = $room . ':' . $position;
			if ( ! $room || ! $position || ! $ordinal || isset( $seen[ $key ] ) || ! preg_match( '/^\d+$/', $age_raw ) ) {
				return new WP_Error( 'offitravel_addon_invalid_traveler_age_snapshot', __( 'Los datos guardados del seguro de viaje no son válidos.', 'offitravel-addons' ) );
			}
			$age  = (int) $age_raw;
			$rule = offitravel_addon_match_age_rule( $age, $rules );
			if ( null === $rule ) {
				return new WP_Error( 'offitravel_addon_invalid_traveler_age_snapshot', __( 'Los datos guardados del seguro de viaje no son válidos.', 'offitravel-addons' ) );
			}
			$seen[ $key ]  = true;
			$rate          = offitravel_addon_money_snapshot( $rule['price'] );
			$service_total += (float) $rate;
			$travelers[]    = array(
				'traveler' => $ordinal,
				'room'     => $room,
				'position' => $position,
				'age'      => $age,
				'rate'     => $rate,
				'subtotal' => $rate,
			);
		}
		usort(
			$travelers,
			static function ( $left, $right ) {
				return $left['traveler'] <=> $right['traveler'];
			}
		);

		$grand_total += $service_total;
		$normalized['services'][ $service_id ] = array(
			'service_id' => $service_id,
			'label'      => sanitize_text_field( isset( $service['label'] ) ? (string) $service['label'] : '' ),
			'rules'      => $rules,
			'travelers'  => $travelers,
			'total'      => offitravel_addon_money_snapshot( $service_total ),
		);
	}

	$normalized['total'] = offitravel_addon_money_snapshot( $grand_total );
	return $normalized;
}

/**
 * Return the recalculated numeric total from a traveler-age snapshot.
 *
 * @param mixed $snapshot Stored snapshot.
 * @param int   $product_id Expected product ID.
 * @return float
 */
function offitravel_addon_age_snapshot_total( $snapshot, $product_id = 0 ) {
	$normalized = offitravel_addon_normalize_age_snapshot( $snapshot, $product_id );
	return is_wp_error( $normalized ) ? 0.0 : (float) $normalized['total'];
}

/**
 * Read room occupancy from cart data or the current request.
 *
 * @param array<string,mixed> $cart_item Existing cart context.
 * @return array<string,mixed>
 */
function offitravel_addon_age_request_context( array $cart_item = array() ) {
	$context = $cart_item;
	foreach ( array( 'offitravel_room_count', 'offitravel_room_people', 'ovabrw_adults' ) as $key ) {
		if ( ! array_key_exists( $key, $context ) && isset( $_POST[ $key ] ) ) {
			$context[ $key ] = wp_unslash( $_POST[ $key ] );
		}
	}
	if ( ! isset( $context['ovabrw_adults'] ) && isset( $_POST['adults'] ) ) {
		$context['ovabrw_adults'] = wp_unslash( $_POST['adults'] );
	}
	return $context;
}

/**
 * Read the nested traveler-age selection from the current request.
 *
 * @return array<mixed>|mixed
 */
function offitravel_addon_age_request_payload() {
	return isset( $_POST['offitravel_age_addons'] ) ? wp_unslash( $_POST['offitravel_age_addons'] ) : array();
}

/**
 * Validate traveler-age data for add-to-cart without persisting it.
 *
 * @param int                 $product_id Product ID.
 * @param mixed               $raw        Nested selection payload.
 * @param array<string,mixed> $context    Occupancy context.
 * @return true|WP_Error
 */
function offitravel_addon_validate_traveler_age_payload( $product_id, $raw, array $context ) {
	if ( empty( $raw ) ) {
		return true;
	}
	$result = offitravel_addon_calculate_traveler_age( $product_id, $raw, $context );
	return is_wp_error( $result ) ? $result : true;
}

/**
 * Block add-to-cart when a selected traveler insurance is malformed.
 *
 * @param bool $passed     Previous validation result.
 * @param int  $product_id Product ID.
 * @param int  $quantity   WooCommerce line quantity.
 * @return bool
 */
function offitravel_addon_validate_cart( $passed, $product_id, $quantity ) {
	unset( $quantity );
	if ( ! $passed ) {
		return false;
	}
	$raw = offitravel_addon_age_request_payload();
	if ( empty( $raw ) ) {
		return true;
	}
	$result = offitravel_addon_validate_traveler_age_payload(
		$product_id,
		$raw,
		offitravel_addon_age_request_context()
	);
	if ( is_wp_error( $result ) ) {
		wc_add_notice( esc_html( $result->get_error_message() ), 'error' );
		return false;
	}
	return true;
}

/**
 * Restore and normalize traveler-age data from the WooCommerce cart session.
 *
 * @param array<string,mixed> $cart_item      Rebuilt cart item.
 * @param array<string,mixed> $session_values Stored session values.
 * @return array<string,mixed>
 */
function offitravel_addon_restore_cart_item( $cart_item, $session_values ) {
	if ( empty( $session_values['offitravel_traveler_age'] ) ) {
		return $cart_item;
	}
	$product_id = isset( $cart_item['product_id'] ) ? absint( $cart_item['product_id'] ) : ( isset( $session_values['product_id'] ) ? absint( $session_values['product_id'] ) : 0 );
	$normalized = offitravel_addon_normalize_age_snapshot( $session_values['offitravel_traveler_age'], $product_id );
	if ( ! is_wp_error( $normalized ) ) {
		$cart_item['offitravel_traveler_age'] = $normalized;
	}
	return $cart_item;
}

/**
 * Convert a WooCommerce monetary amount to decoded plain Unicode text.
 *
 * WooCommerce intentionally returns HTML and entities from wc_price(). This
 * helper removes its markup, decodes entities and normalizes non-breaking
 * spaces before the text reaches either HTML escaping or order metadata.
 *
 * @param mixed $amount Monetary amount.
 * @return string Human-readable amount including the currency symbol.
 */
function offitravel_addon_plain_price_text( $amount ) {
	$text    = wp_strip_all_tags( wc_price( $amount ) );
	$charset = get_bloginfo( 'charset' );
	$text    = html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, $charset ? $charset : 'UTF-8' );
	$text    = str_replace( "\xC2\xA0", ' ', $text );
	$text    = preg_replace( '/\s+/u', ' ', $text );
	return trim( is_string( $text ) ? $text : '' );
}

/**
 * Build decoded plain-text lines for cart, checkout, orders and emails.
 *
 * @param array<string,mixed> $service Server-normalized service snapshot.
 * @return string[] Human-readable lines without HTML or encoded entities.
 */
function offitravel_addon_age_service_lines( array $service ) {
	$rows = array();
	foreach ( $service['travelers'] as $traveler ) {
		$rows[] = sprintf(
			/* translators: 1: global traveler, 2: room, 3: age, 4: formatted price */
			esc_html__( 'Viajero %1$d (Habitación %2$d): %3$d años — %4$s', 'offitravel-addons' ),
			(int) $traveler['traveler'],
			(int) $traveler['room'],
			(int) $traveler['age'],
			offitravel_addon_plain_price_text( $traveler['subtotal'] )
		);
	}
	$rows[] = sprintf(
		/* translators: %s: formatted insurance total */
		esc_html__( 'Total: %s', 'offitravel-addons' ),
		offitravel_addon_plain_price_text( $service['total'] )
	);
	return $rows;
}

/**
 * Build safe HTML for the traveler-age breakdown in cart and checkout.
 *
 * @param array<string,mixed> $service Server-normalized service snapshot.
 * @return string Safe HTML containing escaped text separated only by br tags.
 */
function offitravel_addon_age_service_display( array $service ) {
	$rows = offitravel_addon_age_service_lines( $service );
	return implode( '<br>', array_map( 'esc_html', $rows ) );
}

/**
 * @param int[] $ids
 * @param int   $product_id
 * @return int[]
 */
function offitravel_addon_validate_ids( $ids, $product_id ) {
	$product_id = absint( $product_id );
	$ids        = array_map( 'absint', (array) $ids );
	if ( ! $product_id ) {
		return array();
	}
	$ok = array();
	foreach ( offitravel_addon_posts_for_product( $product_id ) as $p ) {
		$ok[ $p->ID ] = true;
	}
	$res = array();
	foreach ( $ids as $id ) {
		if ( $id && isset( $ok[ $id ] ) && offitravel_addon_uses_fixed_price( $id ) ) {
			$res[] = $id;
		}
	}
	return array_values( array_unique( $res ) );
}

function offitravel_addon_get_post_ids_from_request( array $cart_item ) {
	if ( ! empty( $cart_item['offitravel_addons'] ) && is_array( $cart_item['offitravel_addons'] ) ) {
		return array_values( array_unique( array_map( 'absint', $cart_item['offitravel_addons'] ) ) );
	}
	if (
		wp_doing_ajax()
		&& isset( $_POST['action'] )
		&& 'ovabrw_calculate_total' === sanitize_text_field( wp_unslash( $_POST['action'] ) )
		&& isset( $_POST['offitravel_addons'] )
	) {
		$raw = wp_unslash( $_POST['offitravel_addons'] );
		$raw = is_array( $raw ) ? $raw : array( $raw );
		return array_values( array_unique( array_map( 'absint', array_filter( $raw ) ) ) );
	}
	return array();
}

function offitravel_addon_guest_total( array $cart_item ) {
	$a = isset( $cart_item['ovabrw_adults'] ) ? absint( $cart_item['ovabrw_adults'] ) : 0;
	$c = isset( $cart_item['ovabrw_childrens'] ) ? absint( $cart_item['ovabrw_childrens'] ) : 0;
	$b = isset( $cart_item['ovabrw_babies'] ) ? absint( $cart_item['ovabrw_babies'] ) : 0;
	if (
		wp_doing_ajax()
		&& isset( $_POST['action'] )
		&& 'ovabrw_calculate_total' === sanitize_text_field( wp_unslash( $_POST['action'] ) )
		&& ( ! $a && ! $c && ! $b )
	) {
		$a = isset( $_POST['adults'] ) ? absint( wp_unslash( $_POST['adults'] ) ) : 0;
		$c = isset( $_POST['childrens'] ) ? absint( wp_unslash( $_POST['childrens'] ) ) : 0;
		$b = isset( $_POST['babies'] ) ? absint( wp_unslash( $_POST['babies'] ) ) : 0;
	}
	$s = $a + $c + $b;
	return max( 1, $s );
}

function offitravel_addon_room_count( array $cart_item ) {
	if ( ! empty( $cart_item['offitravel_room_count'] ) ) {
		return max( 1, absint( $cart_item['offitravel_room_count'] ) );
	}
	if ( wp_doing_ajax() && isset( $_POST['offitravel_room_count'] ) ) {
		return max( 1, absint( wp_unslash( $_POST['offitravel_room_count'] ) ) );
	}
	return 1;
}

function offitravel_addon_ov_qty( array $cart_item ) {
	return isset( $cart_item['ovabrw_quantity'] ) ? max( 1, absint( $cart_item['ovabrw_quantity'] ) ) : 1;
}

/**
 * @param int[] $valid_ids
 */
function offitravel_addon_sum( array $valid_ids, array $cart_item ) {
	if ( empty( $valid_ids ) ) {
		return 0;
	}
	$guests = offitravel_addon_guest_total( $cart_item );
	$rooms  = offitravel_addon_room_count( $cart_item );
	$ovq    = offitravel_addon_ov_qty( $cart_item );
	$dec    = wc_get_price_decimals();
	$sum    = 0.0;

	foreach ( $valid_ids as $aid ) {
		if ( ! offitravel_addon_uses_fixed_price( $aid ) ) {
			continue;
		}
		$pr = get_post_meta( $aid, OFFITRAVEL_ADDON_META_PRICE, true );
		if ( '' === $pr || null === $pr ) {
			continue;
		}
		$price   = (float) wc_format_decimal( (string) $pr );
		$billing = offitravel_addon_normalize_billing(
			get_post_meta( $aid, OFFITRAVEL_ADDON_META_BILLING, true )
		);

		switch ( $billing ) {
			case 'person':
				$sum += $price * (float) $guests * $ovq;
				break;
			case 'room':
				$sum += $price * (float) $rooms * $ovq;
				break;
			case 'booking':
			default:
				$sum += $price * $ovq;
				break;
		}
	}

	return round( $sum, $dec );
}

function offitravel_addon_register_cpt() {
	$labels = array(
		'name'          => __( 'Servicios adicionales (producto)', 'offitravel-addons' ),
		'singular_name' => __( 'Servicio adicional', 'offitravel-addons' ),
		'add_new_item'  => __( 'Nuevo servicio', 'offitravel-addons' ),
		'edit_item'     => __( 'Editar servicio', 'offitravel-addons' ),
		'menu_name'     => __( 'Servicios adicionales', 'offitravel-addons' ),
	);
	register_post_type(
		OFFITRAVEL_ADDON_PT,
		array(
			'labels'              => $labels,
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => 'edit.php?post_type=product',
			'menu_position'       => 56,
			'capability_type'     => 'product',
			'map_meta_cap'        => true,
			'hierarchical'        => false,
			'supports'            => array( 'title', 'page-attributes' ),
			'exclude_from_search' => true,
		)
	);
}

/**
 * Print styles used only by the product add-on editor.
 *
 * @return void
 */
function offitravel_addon_admin_styles() {
	global $post_type;
	if ( OFFITRAVEL_ADDON_PT !== $post_type ) {
		return;
	}
	echo '<style>
		.offitravel-addon-admin-hint{font-size:12px;color:#646970;margin-top:6px;display:block}
		.offitravel-addon-age-rules{width:100%;max-width:760px;border-collapse:collapse;margin:8px 0 12px}
		.offitravel-addon-age-rules th,.offitravel-addon-age-rules td{padding:8px;text-align:left;vertical-align:middle}
		.offitravel-addon-age-rules input{width:100%}
		.offitravel-addon-age-rules .offitravel-addon-age-rule-remove{color:#b32d2e}
		.offitravel-addon-age-billing{font-weight:600;margin:8px 0}
	</style>';
}

/**
 * Enqueue WooCommerce controls and the add-on configuration behavior.
 *
 * @param string $hook Current WordPress admin screen hook.
 * @return void
 */
function offitravel_addon_enqueue_admin( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}
	$pid   = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
	$ptype = isset( $_GET['post_type'] ) ? sanitize_key( wp_unslash( $_GET['post_type'] ) ) : '';
	if ( 'post-new.php' === $hook && OFFITRAVEL_ADDON_PT !== $ptype ) {
		return;
	}
	if ( 'post.php' === $hook && OFFITRAVEL_ADDON_PT !== get_post_type( $pid ) ) {
		return;
	}
	wp_enqueue_script( 'wc-enhanced-select' );
	wp_enqueue_style( 'woocommerce_admin_styles' );
	$script_path = __DIR__ . '/offitravel-product-addons-admin.js';
	wp_enqueue_script(
		'offitravel-product-addons-admin',
		plugins_url( 'offitravel-product-addons-admin.js', __FILE__ ),
		array( 'jquery' ),
		file_exists( $script_path ) ? (string) filemtime( $script_path ) : '1.0.0',
		true
	);
}

/**
 * Render one age-price rule row.
 *
 * @param int                  $index Row index used in field names.
 * @param array<string,mixed>  $rule  Stored or blank rule values.
 * @return void
 */
function offitravel_addon_render_age_rule_row( $index, array $rule ) {
	$min_age = isset( $rule['min_age'] ) ? (string) $rule['min_age'] : '';
	$max_age = isset( $rule['max_age'] ) && null !== $rule['max_age'] ? (string) $rule['max_age'] : '';
	$price   = isset( $rule['price'] ) ? (string) $rule['price'] : '';
	?>
	<tr data-offitravel-addon-age-rule>
		<td><input type="number" min="0" step="1" required name="<?php echo esc_attr( OFFITRAVEL_ADDON_META_AGE_RULES ); ?>[<?php echo esc_attr( (string) $index ); ?>][min_age]" value="<?php echo esc_attr( $min_age ); ?>" /></td>
		<td><input type="number" min="0" step="1" name="<?php echo esc_attr( OFFITRAVEL_ADDON_META_AGE_RULES ); ?>[<?php echo esc_attr( (string) $index ); ?>][max_age]" value="<?php echo esc_attr( $max_age ); ?>" placeholder="<?php esc_attr_e( 'Sin límite', 'offitravel-addons' ); ?>" /></td>
		<td><input type="text" inputmode="decimal" required class="wc_input_price" name="<?php echo esc_attr( OFFITRAVEL_ADDON_META_AGE_RULES ); ?>[<?php echo esc_attr( (string) $index ); ?>][price]" value="<?php echo esc_attr( $price ); ?>" /></td>
		<td><button type="button" class="button-link-delete offitravel-addon-age-rule-remove" data-offitravel-addon-remove-rule><?php esc_html_e( 'Eliminar', 'offitravel-addons' ); ?></button></td>
	</tr>
	<?php
}

/**
 * Render the reusable product add-on configuration metabox.
 *
 * @param WP_Post $post Add-on post being edited.
 * @return void
 */
function offitravel_addon_metabox_render( $post ) {
	wp_nonce_field( 'offitravel_addon_save', 'offitravel_addon_nonce' );

	$price        = get_post_meta( $post->ID, OFFITRAVEL_ADDON_META_PRICE, true );
	$billing      = offitravel_addon_normalize_billing( get_post_meta( $post->ID, OFFITRAVEL_ADDON_META_BILLING, true ) );
	$public_label = get_post_meta( $post->ID, OFFITRAVEL_ADDON_META_PUBLIC_LABEL, true );
	$price_model  = offitravel_addon_normalize_price_model( get_post_meta( $post->ID, OFFITRAVEL_ADDON_META_PRICE_MODEL, true ) );
	$age_rules    = get_post_meta( $post->ID, OFFITRAVEL_ADDON_META_AGE_RULES, true );
	$age_rules    = is_array( $age_rules ) ? $age_rules : array();
	$prows        = get_post_meta( $post->ID, OFFITRAVEL_ADDON_META_PRODUCTS, true );
	$prows        = is_array( $prows ) ? array_map( 'absint', $prows ) : array();

	if ( function_exists( 'WC' ) && WC() ) {
		$f = WC()->plugin_path() . '/includes/admin/wc-meta-box-functions.php';
		if ( file_exists( $f ) ) {
			require_once $f;
		}
	}
	?>
	<div class="offitravel-addon-admin" data-offitravel-addon-admin>
	<?php
	if ( function_exists( 'woocommerce_wp_text_input' ) ) {
		woocommerce_wp_text_input(
			array(
				'id'          => OFFITRAVEL_ADDON_META_PUBLIC_LABEL . '_f',
				'name'        => OFFITRAVEL_ADDON_META_PUBLIC_LABEL,
				'label'       => __( 'Etiqueta pública', 'offitravel-addons' ),
				'value'       => $public_label,
				'description' => __( 'Opcional. Si se deja vacía, el cliente verá el título interno del servicio.', 'offitravel-addons' ),
			)
		);
		woocommerce_wp_select(
			array(
				'id'          => OFFITRAVEL_ADDON_META_PRICE_MODEL . '_f',
				'name'        => OFFITRAVEL_ADDON_META_PRICE_MODEL,
				'label'       => __( 'Modelo de precio', 'offitravel-addons' ),
				'description' => __( 'El modelo por edad crea una selección independiente y solicita la edad únicamente a cada viajero asegurado.', 'offitravel-addons' ),
				'options'     => array(
					OFFITRAVEL_ADDON_PRICE_MODEL_FIXED        => __( 'Precio fijo', 'offitravel-addons' ),
					OFFITRAVEL_ADDON_PRICE_MODEL_TRAVELER_AGE => __( 'Por edad de cada viajero', 'offitravel-addons' ),
				),
				'value'       => $price_model,
			)
		);
		?>
		<div data-offitravel-addon-fixed-fields>
		<?php
		woocommerce_wp_text_input(
			array(
				'id'          => OFFITRAVEL_ADDON_META_PRICE . '_f',
				'name'        => OFFITRAVEL_ADDON_META_PRICE,
				'label'       => __( 'Precio unitario', 'offitravel-addons' ),
				'value'       => $price,
				'data_type'   => 'price',
				'description' => __( 'Se combina según la modalidad (persona/habitación/reserva).', 'offitravel-addons' ),
			)
		);
		woocommerce_wp_select(
			array(
				'id'       => OFFITRAVEL_ADDON_META_BILLING . '_f',
				'name'     => OFFITRAVEL_ADDON_META_BILLING,
				'label'    => __( 'Modalidad', 'offitravel-addons' ),
				'desc_tip' => false,
				'options'  => array(
					'person'  => __( 'Por persona (personas totales) × cantidad', 'offitravel-addons' ),
					'room'    => __( 'Por habitación (modo habitaciones; si no aplica → 1) × cantidad', 'offitravel-addons' ),
					'booking' => __( 'Por reserva/unidad × cantidad', 'offitravel-addons' ),
				),
				'value'    => $billing,
			)
		);
		?>
		</div>
		<?php
	}
	$display_rules = ! empty( $age_rules ) ? $age_rules : array( array() );
	?>
	<div data-offitravel-addon-age-fields>
		<p class="offitravel-addon-age-billing"><?php esc_html_e( 'Modalidad: por persona asegurada.', 'offitravel-addons' ); ?></p>
		<p class="description"><?php esc_html_e( 'Las edades deben ser enteros no negativos. La edad máxima puede quedar vacía para crear el último tramo abierto. Los tramos no pueden solaparse.', 'offitravel-addons' ); ?></p>
		<table class="widefat striped offitravel-addon-age-rules" data-offitravel-addon-age-rules>
			<thead><tr>
				<th><?php esc_html_e( 'Edad mínima', 'offitravel-addons' ); ?></th>
				<th><?php esc_html_e( 'Edad máxima', 'offitravel-addons' ); ?></th>
				<th><?php esc_html_e( 'Precio', 'offitravel-addons' ); ?></th>
				<th><span class="screen-reader-text"><?php esc_html_e( 'Acciones', 'offitravel-addons' ); ?></span></th>
			</tr></thead>
			<tbody>
			<?php
			foreach ( $display_rules as $index => $rule ) {
				offitravel_addon_render_age_rule_row( $index, is_array( $rule ) ? $rule : array() );
			}
			?>
			</tbody>
		</table>
		<p><button type="button" class="button" data-offitravel-addon-add-rule><?php esc_html_e( 'Añadir tramo', 'offitravel-addons' ); ?></button></p>
	</div>
	<template data-offitravel-addon-age-rule-template>
		<tr data-offitravel-addon-age-rule>
			<td><input type="number" min="0" step="1" required data-field="min_age" /></td>
			<td><input type="number" min="0" step="1" data-field="max_age" placeholder="<?php esc_attr_e( 'Sin límite', 'offitravel-addons' ); ?>" /></td>
			<td><input type="text" inputmode="decimal" required class="wc_input_price" data-field="price" /></td>
			<td><button type="button" class="button-link-delete offitravel-addon-age-rule-remove" data-offitravel-addon-remove-rule><?php esc_html_e( 'Eliminar', 'offitravel-addons' ); ?></button></td>
		</tr>
	</template>
	<p class="form-field">
		<label><?php esc_html_e( 'Productos donde se muestra', 'offitravel-addons' ); ?></label>
		<select
			class="wc-product-search"
			multiple="multiple"
			style="width: 100%; max-width: 560px;"
			name="<?php echo esc_attr( OFFITRAVEL_ADDON_META_PRODUCTS ); ?>[]"
			data-placeholder="<?php esc_attr_e( 'Buscar productos…', 'offitravel-addons' ); ?>"
			data-action="woocommerce_json_search_products_and_variations"
		>
			<?php
			foreach ( $prows as $pid_item ) {
				$pwc = wc_get_product( $pid_item );
				if ( $pwc instanceof WC_Product ) {
					echo '<option value="' . esc_attr( (string) $pid_item ) . '"' . selected( true, true, false ) . '>'
						. esc_html( wp_strip_all_tags( $pwc->get_formatted_name() ) )
						. '</option>';
				}
			}
			?>
		</select>
		<span class="offitravel-addon-admin-hint"><?php esc_html_e( 'Sólo estos tours verán el checkbox en la ficha del tour.', 'offitravel-addons' ); ?></span>
	</p>
	</div>
	<?php
}

function offitravel_addon_metabox_add() {
	add_meta_box(
		'offitravel_addon_cfg',
		__( 'Precio y visibilidad', 'offitravel-addons' ),
		'offitravel_addon_metabox_render',
		OFFITRAVEL_ADDON_PT,
		'normal',
		'high'
	);
}

/**
 * Remember a rejected administrative save until WordPress builds its redirect.
 *
 * @param int      $post_id Add-on post ID.
 * @param WP_Error $error   Validation error.
 * @return void
 */
function offitravel_addon_record_admin_error( $post_id, WP_Error $error ) {
	if ( ! isset( $GLOBALS['offitravel_addon_admin_errors'] ) || ! is_array( $GLOBALS['offitravel_addon_admin_errors'] ) ) {
		$GLOBALS['offitravel_addon_admin_errors'] = array();
	}
	$GLOBALS['offitravel_addon_admin_errors'][ (int) $post_id ] = $error->get_error_code();
}

/**
 * Add a safe validation code to the post-save redirect.
 *
 * @param string $location Redirect URL.
 * @param int    $post_id  Saved post ID.
 * @return string Filtered redirect URL.
 */
function offitravel_addon_admin_redirect_error( $location, $post_id ) {
	$errors = isset( $GLOBALS['offitravel_addon_admin_errors'] ) && is_array( $GLOBALS['offitravel_addon_admin_errors'] )
		? $GLOBALS['offitravel_addon_admin_errors']
		: array();
	if ( empty( $errors[ (int) $post_id ] ) ) {
		return $location;
	}
	return add_query_arg( 'offitravel_addon_error', sanitize_key( $errors[ (int) $post_id ] ), $location );
}

/**
 * Display a server-side validation error after an add-on save is rejected.
 *
 * @return void
 */
function offitravel_addon_admin_validation_notice() {
	if ( ! isset( $_GET['offitravel_addon_error'] ) ) {
		return;
	}
	$code = sanitize_key( wp_unslash( $_GET['offitravel_addon_error'] ) );
	$messages = array(
		'offitravel_addon_age_rules_required'             => __( 'No se guardó la configuración: añade al menos un tramo de edad.', 'offitravel-addons' ),
		'offitravel_addon_invalid_age'                    => __( 'No se guardó la configuración: las edades deben ser enteros no negativos.', 'offitravel-addons' ),
		'offitravel_addon_invalid_age_range'              => __( 'No se guardó la configuración: revisa los límites de edad.', 'offitravel-addons' ),
		'offitravel_addon_overlapping_age_ranges'         => __( 'No se guardó la configuración: los tramos de edad se solapan.', 'offitravel-addons' ),
		'offitravel_addon_invalid_rule_price'             => __( 'No se guardó la configuración: revisa los precios de los tramos.', 'offitravel-addons' ),
		'offitravel_addon_age_requires_person_billing'    => __( 'No se guardó la configuración: el precio por edad sólo admite facturación por viajero.', 'offitravel-addons' ),
		'offitravel_addon_invalid_price_model'             => __( 'No se guardó la configuración: el modelo de precio no es válido.', 'offitravel-addons' ),
		'offitravel_addon_invalid_billing'                 => __( 'No se guardó la configuración: la modalidad no es válida.', 'offitravel-addons' ),
		'offitravel_addon_invalid_fixed_price'             => __( 'No se guardó la configuración: indica un precio fijo válido.', 'offitravel-addons' ),
	);
	if ( ! isset( $messages[ $code ] ) ) {
		return;
	}
	echo '<div class="notice notice-error is-dismissible"><p>' . esc_html( $messages[ $code ] ) . '</p></div>';
}

/**
 * Validate the complete payload before persisting add-on metadata.
 *
 * Fixed-price legacy services keep using absent model metadata as their
 * backward-compatible default. A validation error prevents partial metadata
 * writes; this does not claim or require a database transaction.
 *
 * @param int $post_id Add-on post ID.
 * @return void
 */
function offitravel_addon_save( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( wp_is_post_revision( $post_id ) ) {
		return;
	}
	if ( ! isset( $_POST['post_type'] ) || OFFITRAVEL_ADDON_PT !== sanitize_key( wp_unslash( $_POST['post_type'] ) ) ) {
		return;
	}
	if ( ! isset( $_POST['offitravel_addon_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['offitravel_addon_nonce'] ) ), 'offitravel_addon_save' )
	) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$payload = wp_unslash( $_POST );
	$config  = offitravel_addon_validate_admin_payload( is_array( $payload ) ? $payload : array() );
	if ( is_wp_error( $config ) ) {
		offitravel_addon_record_admin_error( $post_id, $config );
		return;
	}

	if ( '' !== $config['public_label'] ) {
		update_post_meta( $post_id, OFFITRAVEL_ADDON_META_PUBLIC_LABEL, $config['public_label'] );
	} else {
		delete_post_meta( $post_id, OFFITRAVEL_ADDON_META_PUBLIC_LABEL );
	}

	if ( OFFITRAVEL_ADDON_PRICE_MODEL_TRAVELER_AGE === $config['price_model'] ) {
		update_post_meta( $post_id, OFFITRAVEL_ADDON_META_PRICE_MODEL, OFFITRAVEL_ADDON_PRICE_MODEL_TRAVELER_AGE );
		update_post_meta( $post_id, OFFITRAVEL_ADDON_META_AGE_RULES, $config['age_rules'] );
		/* Age pricing is per traveler by definition; keep any fixed-model billing for a future switch back. */
	} else {
		$current_price = get_post_meta( $post_id, OFFITRAVEL_ADDON_META_PRICE, true );
		$stored_price  = $config['price'];
		if ( '' !== $current_price && wc_format_decimal( (string) $current_price, wc_get_price_decimals() ) === $stored_price ) {
			$stored_price = $current_price;
		}
		update_post_meta( $post_id, OFFITRAVEL_ADDON_META_PRICE, $stored_price );
		update_post_meta( $post_id, OFFITRAVEL_ADDON_META_BILLING, $config['billing'] );
		delete_post_meta( $post_id, OFFITRAVEL_ADDON_META_PRICE_MODEL );
		delete_post_meta( $post_id, OFFITRAVEL_ADDON_META_AGE_RULES );
	}

	update_post_meta( $post_id, OFFITRAVEL_ADDON_META_PRODUCTS, $config['product_ids'] );
}

function offitravel_addon_booking_markup( $args ) {
	if ( empty( $args['id'] ) || ! defined( 'OVABRW_RENTAL' ) ) {
		return;
	}
	$product_id = absint( $args['id'] );
	$product    = wc_get_product( $product_id );
	if ( ! $product instanceof WC_Product || ! $product->is_type( OVABRW_RENTAL ) ) {
		return;
	}
	$items     = offitravel_addon_posts_for_product( $product_id );
	$age_items = offitravel_addon_age_posts_for_product( $product_id );
	if ( ! $items && ! $age_items ) {
		return;
	}

	$intro = apply_filters(
		'offitravel_prd_addon_intro_text',
		__( 'Por favor seleccione su servicio adicional de preferencia.', 'offitravel-addons' )
	);
	?>
	<div class="booking-item offitravel-prd-addon offitravel-prd-addon-fields">
		<style>
			.offitravel-prd-addon-intro { font-weight: 600; margin: 0 0 0.5rem; }
			.offitravel-prd-addon-sep { border: 0; border-top: 1px solid #e0e0e0; margin: 0.75rem 0; }
			.offitravel-prd-addon-list { list-style: none; margin: 0; padding: 0; margin-bottom: 1rem; }
			.offitravel-prd-addon-row {
				display: flex; justify-content: space-between; align-items: center; gap: 1rem;
				padding: 0.4rem 0; border-bottom: 1px solid #f0f0f0;
			}
			.offitravel-prd-addon-row:last-child { border-bottom: 0; }
			.offitravel-prd-addon-row label { display: flex; align-items: center; gap: 0.5rem; cursor: pointer; flex: 1; margin: 0; }
			.offitravel-prd-addon-price { font-weight: 700; color: #2271b1; white-space: nowrap; }
			.offitravel-prd-addon-unit { font-weight: 600; color: #2271b1; font-size: 0.9em; }
			.offitravel-prd-addon-age-service { margin: 0 0 1rem; }
			.offitravel-prd-addon-age-title { font-weight: 700; margin: 0 0 0.5rem; }
			.offitravel-prd-addon-age-rules { color: #555; font-size: 0.9em; margin: 0 0 0.65rem; }
			.offitravel-prd-addon-age-rule { display: block; }
			.offitravel-prd-addon-traveler-row { border: 1px solid #e5e5e5; border-radius: 4px; margin: 0 0 0.6rem; padding: 0.65rem; }
			.offitravel-prd-addon-traveler-heading { display: block; font-weight: 700; margin-bottom: 0.4rem; }
			.offitravel-prd-addon-traveler-controls { display: grid; gap: 0.5rem; }
			.offitravel-prd-addon-traveler-controls label { align-items: center; display: flex; gap: 0.45rem; margin: 0; }
			.offitravel-prd-addon-traveler-age input { max-width: 8rem; }
			.offitravel-prd-addon-traveler-age-error { color: #b32d2e; display: block; font-size: 0.875em; }
			.offitravel-prd-addon-traveler-age-error[hidden] { display: none; }
		</style>
		<p class="offitravel-prd-addon-intro"><?php echo esc_html( $intro ); ?></p>
		<hr class="offitravel-prd-addon-sep" />
		<?php if ( $items ) : ?>
			<ul class="offitravel-prd-addon-list">
			<?php
			foreach ( $items as $addon_post ) :
				if ( ! offitravel_addon_uses_fixed_price( $addon_post->ID ) ) {
					continue;
				}
				$pr     = get_post_meta( $addon_post->ID, OFFITRAVEL_ADDON_META_PRICE, true );
				$bill   = offitravel_addon_normalize_billing(
					get_post_meta( $addon_post->ID, OFFITRAVEL_ADDON_META_BILLING, true )
				);
				$u_lbl  = array(
					'person'  => __( '/ Persona', 'offitravel-addons' ),
					'room'    => __( '/ Habitación', 'offitravel-addons' ),
					'booking' => __( '/ Reserva', 'offitravel-addons' ),
				)[ $bill ];
				$dec_pr = wc_format_decimal( (string) $pr );
				?>
				<li class="offitravel-prd-addon-row">
					<label>
						<input
							type="checkbox"
							name="offitravel_addons[]"
							value="<?php echo esc_attr( (string) $addon_post->ID ); ?>"
						/>
						<span><?php echo esc_html( get_the_title( $addon_post ) ); ?></span>
					</label>
					<span class="offitravel-prd-addon-price">
						<?php
						if ( '' !== $dec_pr ) {
							echo wp_kses_post( wc_price( $dec_pr ) );
							echo ' <span class="offitravel-prd-addon-unit">' . esc_html( $u_lbl ) . '</span>';
						}
						?>
					</span>
				</li>
				<?php
			endforeach;
			?>
			</ul>
		<?php endif; ?>
		<?php foreach ( $age_items as $age_addon ) : ?>
			<?php
			$rules = offitravel_addon_validate_age_rules( get_post_meta( $age_addon->ID, OFFITRAVEL_ADDON_META_AGE_RULES, true ) );
			if ( is_wp_error( $rules ) ) {
				continue;
			}
			$rule_labels = array();
			foreach ( $rules as $rule ) {
				$range = null === $rule['max_age']
					? sprintf( esc_html__( 'Desde %d años', 'offitravel-addons' ), (int) $rule['min_age'] )
					: sprintf( esc_html__( 'De %1$d a %2$d años', 'offitravel-addons' ), (int) $rule['min_age'], (int) $rule['max_age'] );
				$rule_labels[] = $range . ': ' . offitravel_addon_plain_price_text( $rule['price'] );
			}
			?>
			<section
				class="offitravel-prd-addon-age-service"
				data-offitravel-age-service="<?php echo esc_attr( (string) $age_addon->ID ); ?>"
				data-offitravel-age-label="<?php echo esc_attr( offitravel_addon_get_public_label( $age_addon ) ); ?>"
			>
				<h4 class="offitravel-prd-addon-age-title"><?php echo esc_html( offitravel_addon_get_public_label( $age_addon ) ); ?></h4>
				<?php
				echo '<p class="offitravel-prd-addon-age-rules">';
				foreach ( $rule_labels as $rule_label ) {
					echo '<span class="offitravel-prd-addon-age-rule">' . esc_html( $rule_label ) . '</span>';
				}
				echo '</p>';
				?>
				<div data-offitravel-traveler-rows aria-live="polite"></div>
			</section>
		<?php endforeach; ?>
	</div>
	<?php
}

function offitravel_addon_enqueue_front() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}
	if ( ! defined( 'OVABRW_RENTAL' ) ) {
		return;
	}
	$p = wc_get_product( get_queried_object_id() );
	if ( ! $p instanceof WC_Product || ! $p->is_type( OVABRW_RENTAL ) ) {
		return;
	}
	if ( ! offitravel_addon_posts_for_product( $p->get_id() ) && ! offitravel_addon_age_posts_for_product( $p->get_id() ) ) {
		return;
	}

	$deps = array( 'jquery', 'ova_brw_js_frontend' );
	foreach ( array( 'elementor-frontend', 'elementor-pro-frontend' ) as $eh ) {
		if ( wp_script_is( $eh, 'registered' ) ) {
			$deps[] = $eh;
		}
	}
	if ( wp_script_is( 'offitravel-ovabrw-room-mode', 'registered' ) ) {
		$deps[] = 'offitravel-ovabrw-room-mode';
	}
	$deps = array_values( array_unique( $deps ) );
	$state_path = dirname( __FILE__ ) . '/offitravel-product-addons-traveler-age-state.js';
	wp_enqueue_script(
		'offitravel-product-addons-traveler-age-state',
		plugin_dir_url( __FILE__ ) . 'offitravel-product-addons-traveler-age-state.js',
		array(),
		is_readable( $state_path ) ? (string) filemtime( $state_path ) : '1',
		true
	);
	$deps[] = 'offitravel-product-addons-traveler-age-state';

	$path = dirname( __FILE__ ) . '/offitravel-product-addons-front.js';
	$bust = is_readable( $path ) ? (string) filemtime( $path ) : '1';

	wp_enqueue_script(
		'offitravel-product-addons',
		plugin_dir_url( __FILE__ ) . 'offitravel-product-addons-front.js',
		$deps,
		$bust,
		true
	);
}

function offitravel_addon_cart_data( $cart_item_data, $product_id, $quantity = null ) {
	unset( $quantity );
	if ( ! defined( 'OVABRW_RENTAL' ) ) {
		return $cart_item_data;
	}
	$p = wc_get_product( absint( $product_id ) );
	if ( ! $p instanceof WC_Product || ! $p->is_type( OVABRW_RENTAL ) ) {
		return $cart_item_data;
	}
	if ( ! empty( $_POST['offitravel_addons'] ) ) {
		$raw = wp_unslash( $_POST['offitravel_addons'] );
		$raw = is_array( $raw ) ? $raw : array( $raw );
		$cart_item_data['offitravel_addons'] = offitravel_addon_validate_ids(
			array_map( 'absint', $raw ),
			absint( $product_id )
		);
	}

	$age_raw = offitravel_addon_age_request_payload();
	if ( ! empty( $age_raw ) ) {
		$snapshot = offitravel_addon_calculate_traveler_age(
			$product_id,
			$age_raw,
			offitravel_addon_age_request_context( $cart_item_data )
		);
		if ( ! is_wp_error( $snapshot ) && ! empty( $snapshot['services'] ) ) {
			$cart_item_data['offitravel_traveler_age'] = $snapshot;
		}
	}
	return $cart_item_data;
}

function offitravel_addon_line_total( $line_total, $product_id, $checkin_date, $checkout_date, $cart_item ) {
	unset( $checkin_date, $checkout_date );
	if ( empty( $product_id ) || ! defined( 'OVABRW_RENTAL' ) ) {
		return $line_total;
	}
	$p = wc_get_product( absint( $product_id ) );
	if ( ! $p instanceof WC_Product || ! $p->is_type( OVABRW_RENTAL ) ) {
		return $line_total;
	}
	$t     = is_array( $cart_item ) ? $cart_item : array();
	$ids   = offitravel_addon_get_post_ids_from_request( $t );
	$valid = offitravel_addon_validate_ids( $ids, absint( $product_id ) );
	$add   = offitravel_addon_sum( $valid, $t );
	if ( ! empty( $t['offitravel_traveler_age'] ) ) {
		$add += offitravel_addon_age_snapshot_total( $t['offitravel_traveler_age'], absint( $product_id ) );
	} elseif ( wp_doing_ajax() && ! empty( $_POST['offitravel_age_addons'] ) ) {
		$age_snapshot = offitravel_addon_calculate_traveler_age(
			$product_id,
			offitravel_addon_age_request_payload(),
			offitravel_addon_age_request_context( $t )
		);
		if ( ! is_wp_error( $age_snapshot ) ) {
			$add += (float) $age_snapshot['total'];
		}
	}
	if ( $add <= 0 ) {
		return $line_total;
	}
	return round( (float) $line_total + $add, wc_get_price_decimals() );
}

function offitravel_addon_cart_display( $item_data, $cart_item ) {
	if ( empty( $cart_item['data'] ) || ! defined( 'OVABRW_RENTAL' )
		|| ! $cart_item['data']->is_type( OVABRW_RENTAL )
	) {
		return $item_data;
	}
	$labels = array();
	if ( ! empty( $cart_item['offitravel_addons'] ) && is_array( $cart_item['offitravel_addons'] ) ) {
		foreach ( array_map( 'absint', $cart_item['offitravel_addons'] ) as $aid ) {
			$po = get_post( $aid );
			if ( $po && OFFITRAVEL_ADDON_PT === $po->post_type ) {
				$labels[] = $po->post_title;
			}
		}
	}
	if ( $labels ) {
		$item_data[] = array(
			'key'   => __( 'Servicios adicionales', 'offitravel-addons' ),
			'value' => esc_html( implode( ', ', $labels ) ),
		);
	}

	$age_snapshot = ! empty( $cart_item['offitravel_traveler_age'] )
		? offitravel_addon_normalize_age_snapshot( $cart_item['offitravel_traveler_age'], $cart_item['data']->get_id() )
		: null;
	if ( is_array( $age_snapshot ) ) {
		foreach ( $age_snapshot['services'] as $service ) {
			$item_data[] = array(
				'key'   => '' !== $service['label'] ? $service['label'] : __( 'Seguro de viaje', 'offitravel-addons' ),
				'value' => offitravel_addon_age_service_display( $service ),
			);
		}
	}
	return $item_data;
}

function offitravel_addon_order_item( $item, $cart_item_key, $values ) {
	unset( $cart_item_key );
	if ( empty( $values['data'] ) || ! defined( 'OVABRW_RENTAL' )
		|| ! $values['data']->is_type( OVABRW_RENTAL )
	) {
		return;
	}
	if ( ! empty( $values['offitravel_addons'] ) && is_array( $values['offitravel_addons'] ) ) {
		$ids = offitravel_addon_validate_ids(
			array_map( 'absint', $values['offitravel_addons'] ),
			(int) $values['data']->get_id()
		);
		$names = array();
		foreach ( $ids as $id ) {
			$t = get_post( $id );
			if ( $t ) {
				$names[] = $t->post_title;
			}
		}
		if ( $names ) {
			$item->add_meta_data(
				__( 'Servicios adicionales', 'offitravel-addons' ),
				implode( ', ', array_map( 'sanitize_text_field', $names ) ),
				true
			);
			$item->add_meta_data( '_offitravel_addon_ids', implode( ',', $ids ), false );
		}
	}

	$age_snapshot = ! empty( $values['offitravel_traveler_age'] )
		? offitravel_addon_normalize_age_snapshot( $values['offitravel_traveler_age'], (int) $values['data']->get_id() )
		: null;
	if ( is_array( $age_snapshot ) ) {
		foreach ( $age_snapshot['services'] as $service ) {
			$item->add_meta_data(
				'' !== $service['label'] ? $service['label'] : __( 'Seguro de viaje', 'offitravel-addons' ),
				implode( "\n", offitravel_addon_age_service_lines( $service ) ),
				true
			);
		}
		$item->add_meta_data( '_offitravel_traveler_age_snapshot', $age_snapshot, true );
		$item->add_meta_data( '_offitravel_traveler_age_total', $age_snapshot['total'], true );
	}
}

/**
 * Hide technical traveler-age metadata while retaining the readable order row.
 *
 * @param string[] $hidden Existing hidden item-meta keys.
 * @return string[]
 */
function offitravel_addon_hidden_order_itemmeta( $hidden ) {
	$hidden[] = '_offitravel_traveler_age_snapshot';
	$hidden[] = '_offitravel_traveler_age_total';
	return array_values( array_unique( $hidden ) );
}

function offitravel_addon_boot() {
	add_action( 'init', 'offitravel_addon_register_cpt' );
	add_action( 'admin_head', 'offitravel_addon_admin_styles' );
	add_action( 'admin_enqueue_scripts', 'offitravel_addon_enqueue_admin', 20 );
	add_action( 'add_meta_boxes_' . OFFITRAVEL_ADDON_PT, 'offitravel_addon_metabox_add' );
	add_action( 'save_post_' . OFFITRAVEL_ADDON_PT, 'offitravel_addon_save' );
	add_filter( 'redirect_post_location', 'offitravel_addon_admin_redirect_error', 99, 2 );
	add_action( 'admin_notices', 'offitravel_addon_admin_validation_notice' );

	add_action( 'tripgo_booking_form', 'offitravel_addon_booking_markup', 23, 1 );
	add_action( 'wp_enqueue_scripts', 'offitravel_addon_enqueue_front', 400 );

	add_filter( 'ovabrw_add_cart_item_data', 'offitravel_addon_cart_data', 20, 3 );
	add_filter( 'ovabrw_get_price_by_guests', 'offitravel_addon_line_total', 1008, 5 );
	add_filter( 'woocommerce_add_to_cart_validation', 'offitravel_addon_validate_cart', 100, 3 );
	add_filter( 'woocommerce_get_cart_item_from_session', 'offitravel_addon_restore_cart_item', 20, 2 );
	add_filter( 'woocommerce_get_item_data', 'offitravel_addon_cart_display', 30, 2 );
	add_action( 'woocommerce_checkout_create_order_line_item', 'offitravel_addon_order_item', 15, 3 );
	add_filter( 'woocommerce_hidden_order_itemmeta', 'offitravel_addon_hidden_order_itemmeta' );
}

if ( function_exists( 'add_action' ) ) {
	add_action( 'woocommerce_loaded', 'offitravel_addon_boot' );
}
