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

/**
 * @param mixed $raw
 * @return string booking|person|room
 */
function offitravel_addon_normalize_billing( $raw ) {
	$raw = is_string( $raw ) ? strtolower( trim( $raw ) ) : '';
	return in_array( $raw, array( 'person', 'booking', 'room' ), true ) ? $raw : 'person';
}

/**
 * @param int $product_id
 * @return WP_Post[]
 */
function offitravel_addon_posts_for_product( $product_id ) {
	$product_id = absint( $product_id );
	if ( ! $product_id ) {
		return array();
	}
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
		if ( $id && isset( $ok[ $id ] ) ) {
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

function offitravel_addon_admin_styles() {
	global $post_type;
	if ( OFFITRAVEL_ADDON_PT !== $post_type ) {
		return;
	}
	echo '<style>.offitravel-addon-admin-hint{font-size:12px;color:#646970;margin-top:6px;display:block}</style>';
}

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
}

function offitravel_addon_metabox_render( $post ) {
	wp_nonce_field( 'offitravel_addon_save', 'offitravel_addon_nonce' );

	$price   = get_post_meta( $post->ID, OFFITRAVEL_ADDON_META_PRICE, true );
	$billing = offitravel_addon_normalize_billing(
		get_post_meta( $post->ID, OFFITRAVEL_ADDON_META_BILLING, true )
	);
	$prows = get_post_meta( $post->ID, OFFITRAVEL_ADDON_META_PRODUCTS, true );
	$prows = is_array( $prows ) ? array_map( 'absint', $prows ) : array();

	if ( function_exists( 'WC' ) && WC() ) {
		$f = WC()->plugin_path() . '/includes/admin/wc-meta-box-functions.php';
		if ( file_exists( $f ) ) {
			require_once $f;
		}
	}

	if ( function_exists( 'woocommerce_wp_text_input' ) ) {
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
					// 'booking' => __( 'Por reserva/unidad × cantidad', 'offitravel-addons' ),
				),
				'value'    => $billing,
			)
		);
	}
	?>
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

	if ( isset( $_POST[ OFFITRAVEL_ADDON_META_PRICE ] ) ) {
		update_post_meta( $post_id, OFFITRAVEL_ADDON_META_PRICE, wc_format_decimal( wp_unslash( $_POST[ OFFITRAVEL_ADDON_META_PRICE ] ) ) );
	}
	if ( isset( $_POST[ OFFITRAVEL_ADDON_META_BILLING ] ) ) {
		update_post_meta(
			$post_id,
			OFFITRAVEL_ADDON_META_BILLING,
			offitravel_addon_normalize_billing( sanitize_text_field( wp_unslash( $_POST[ OFFITRAVEL_ADDON_META_BILLING ] ) ) )
		);
	}
	if ( isset( $_POST[ OFFITRAVEL_ADDON_META_PRODUCTS ] ) && is_array( $_POST[ OFFITRAVEL_ADDON_META_PRODUCTS ] ) ) {
		$ids = array_map( 'absint', (array) wp_unslash( $_POST[ OFFITRAVEL_ADDON_META_PRODUCTS ] ) );
		update_post_meta( $post_id, OFFITRAVEL_ADDON_META_PRODUCTS, array_values( array_filter( array_unique( $ids ) ) ) );
	} else {
		update_post_meta( $post_id, OFFITRAVEL_ADDON_META_PRODUCTS, array() );
	}
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
	$items = offitravel_addon_posts_for_product( $product_id );
	if ( ! $items ) {
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
		</style>
		<p class="offitravel-prd-addon-intro"><?php echo esc_html( $intro ); ?></p>
		<hr class="offitravel-prd-addon-sep" />
		<ul class="offitravel-prd-addon-list">
			<?php
			foreach ( $items as $addon_post ) :
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
	if ( ! offitravel_addon_posts_for_product( $p->get_id() ) ) {
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
	if ( empty( $_POST['offitravel_addons'] ) ) {
		return $cart_item_data;
	}
	if ( ! defined( 'OVABRW_RENTAL' ) ) {
		return $cart_item_data;
	}
	$p = wc_get_product( absint( $product_id ) );
	if ( ! $p instanceof WC_Product || ! $p->is_type( OVABRW_RENTAL ) ) {
		return $cart_item_data;
	}
	$raw = wp_unslash( $_POST['offitravel_addons'] );
	$raw = is_array( $raw ) ? $raw : array( $raw );
	$cart_item_data['offitravel_addons'] = offitravel_addon_validate_ids(
		array_map( 'absint', $raw ),
		absint( $product_id )
	);
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
	if ( $add <= 0 ) {
		return $line_total;
	}
	return round( (float) $line_total + $add, wc_get_price_decimals() );
}

function offitravel_addon_cart_display( $item_data, $cart_item ) {
	if ( empty( $cart_item['offitravel_addons'] ) || ! is_array( $cart_item['offitravel_addons'] )
		|| empty( $cart_item['data'] ) || ! defined( 'OVABRW_RENTAL' )
		|| ! $cart_item['data']->is_type( OVABRW_RENTAL )
	) {
		return $item_data;
	}
	$labels = array();
	foreach ( array_map( 'absint', $cart_item['offitravel_addons'] ) as $aid ) {
		$po = get_post( $aid );
		if ( $po && OFFITRAVEL_ADDON_PT === $po->post_type ) {
			$labels[] = $po->post_title;
		}
	}
	if ( ! $labels ) {
		return $item_data;
	}
	$item_data[] = array(
		'key'   => __( 'Servicios adicionales', 'offitravel-addons' ),
		'value' => esc_html( implode( ', ', $labels ) ),
	);
	return $item_data;
}

function offitravel_addon_order_item( $item, $cart_item_key, $values ) {
	unset( $cart_item_key );
	if ( empty( $values['offitravel_addons'] ) || ! is_array( $values['offitravel_addons'] )
		|| empty( $values['data'] ) || ! defined( 'OVABRW_RENTAL' )
		|| ! $values['data']->is_type( OVABRW_RENTAL )
	) {
		return;
	}
	$ids = offitravel_addon_validate_ids(
		array_map( 'absint', $values['offitravel_addons'] ),
		(int) $values['data']->get_id()
	);
	if ( ! $ids ) {
		return;
	}
	$names = array();
	foreach ( $ids as $id ) {
		$t = get_post( $id );
		if ( $t ) {
			$names[] = $t->post_title;
		}
	}
	if ( ! $names ) {
		return;
	}
	$item->add_meta_data(
		__( 'Servicios adicionales', 'offitravel-addons' ),
		implode( ', ', array_map( 'sanitize_text_field', $names ) ),
		true
	);
	$item->add_meta_data( '_offitravel_addon_ids', implode( ',', $ids ), false );
}

function offitravel_addon_boot() {
	add_action( 'init', 'offitravel_addon_register_cpt' );
	add_action( 'admin_head', 'offitravel_addon_admin_styles' );
	add_action( 'admin_enqueue_scripts', 'offitravel_addon_enqueue_admin', 20 );
	add_action( 'add_meta_boxes_' . OFFITRAVEL_ADDON_PT, 'offitravel_addon_metabox_add' );
	add_action( 'save_post_' . OFFITRAVEL_ADDON_PT, 'offitravel_addon_save' );

	add_action( 'tripgo_booking_form', 'offitravel_addon_booking_markup', 23, 1 );
	add_action( 'wp_enqueue_scripts', 'offitravel_addon_enqueue_front', 400 );

	add_filter( 'ovabrw_add_cart_item_data', 'offitravel_addon_cart_data', 20, 3 );
	add_filter( 'ovabrw_get_price_by_guests', 'offitravel_addon_line_total', 1008, 5 );
	add_filter( 'woocommerce_get_item_data', 'offitravel_addon_cart_display', 30, 2 );
	add_action( 'woocommerce_checkout_create_order_line_item', 'offitravel_addon_order_item', 15, 3 );
}

if ( function_exists( 'add_action' ) ) {
	add_action( 'woocommerce_loaded', 'offitravel_addon_boot' );
}
