<?php
/**
 * Plugin Name: Offitravel Cabin Supplement Base
 * Description: Administrative and calculation primitives for product-scoped cabin options.
 * Version: 0.1.0
 *
 * Checkpoint 4 intentionally registers no public form, AJAX, cart, pricing or
 * order hooks. Product activation and commercial configuration belong to later
 * checkpoints.
 *
 * @package Offitravel
 */

defined( 'ABSPATH' ) || exit;

const OFFITRAVEL_CABIN_META_OPTIONS = '_offitravel_cabin_options';
const OFFITRAVEL_CABIN_META_ENABLED = '_offitravel_cabin_options_enabled';

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
