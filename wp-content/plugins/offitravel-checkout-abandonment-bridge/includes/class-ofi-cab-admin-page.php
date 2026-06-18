<?php
/**
 * Admin MVP - Offitravel Checkout Abandonment Bridge
 *
 * Submenu bajo WooCommerce con vista unificada de:
 * 1) abandonos sin lead,
 * 2) leads con abandono,
 * 3) leads cuyo abandono fue eliminado por compra directa.
 *
 * @package Ofi_Cab
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Ofi_Cab_Admin_Page {

	private static ?self $instance = null;

	private function __construct() {}

	public static function get_instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function register_hooks(): void {
		add_action( 'admin_menu', [ $this, 'register_menu' ], 99 );
	}

	public function register_menu(): void {
		add_submenu_page(
			'woocommerce',
			'Carritos abandonados OFFITRAVEL',
			'Carritos abandonados OFFITRAVEL',
			'manage_woocommerce',
			'offi-checkout-funnel',
			[ $this, 'render_page' ]
		);
	}

	public function render_page(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'No tienes permisos para acceder a esta página.', 'ofi-cab' ) );
		}

		$paged    = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
		$per_page = 25;
		$search   = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
		$view     = $this->sanitize_view( isset( $_GET['view'] ) ? wp_unslash( $_GET['view'] ) : 'abandoned' );

		$rows  = $this->get_rows( $paged, $per_page, $search, $view );
		$total = $this->count_rows( $search, $view );

		$total_pages = (int) ceil( $total / $per_page );
		if ( $total_pages < 1 ) {
			$total_pages = 1;
		}

		echo '<div class="wrap">';
		echo '<h1>Carritos abandonados OFFITRAVEL</h1>';

		$this->render_views_nav( $view );

		echo '<form method="get" style="margin:12px 0;">';
		echo '<input type="hidden" name="page" value="offi-checkout-funnel" />';
		echo '<input type="hidden" name="view" value="' . esc_attr( $view ) . '" />';
		echo '<input type="search" name="s" value="' . esc_attr( $search ) . '" placeholder="Buscar por email, nombre, session, lead, order" />';
		echo '<button class="button button-primary" type="submit">Filtrar</button>';
		echo '</form>';

		echo '<table class="widefat striped">';
		$this->render_table_head( $view );

		if ( empty( $rows ) ) {
			$empty_colspan = 'abandoned' === $view ? 8 : 10;
			echo '<tr><td colspan="' . esc_attr( (string) $empty_colspan ) . '">Sin resultados.</td></tr>';
		} else {
			foreach ( $rows as $row ) {
				$view_row = $this->build_view_row( $row );

				echo '<tr>';
				echo '<td>' . esc_html( $view_row['date'] ) . '</td>';
				echo '<td>' . esc_html( $view_row['client'] ) . '</td>';
				echo '<td>' . esc_html( $view_row['email'] ) . '</td>';
				if ( 'abandoned' === $view ) {
					echo '<td>' . esc_html( $view_row['phone'] ) . '</td>';
					echo '<td>' . esc_html( $view_row['products'] ) . '</td>';
					echo '<td>' . esc_html( $view_row['total'] ) . '</td>';
					echo '<td>' . wp_kses_post( $view_row['abandon_point_html'] ) . '</td>';
					echo '<td>' . $view_row['actions_html'] . '</td>';
				} else {
					echo '<td>' . esc_html( $view_row['phone'] ) . '</td>';
					echo '<td>' . esc_html( $view_row['products'] ) . '</td>';
					echo '<td>' . esc_html( $view_row['total'] ) . '</td>';
					echo '<td>' . wp_kses_post( $view_row['offi_stage_badge'] ) . '</td>';
					echo '<td>' . wp_kses_post( $view_row['abandonment_state_badge'] ) . '</td>';
					echo '<td>' . esc_html( $view_row['order_info'] ) . '</td>';
					echo '<td>' . esc_html( $view_row['payment_method'] ) . '</td>';
					echo '<td>' . $view_row['actions_html'] . '</td>';
				}
				echo '</tr>';
			}
		}

		echo '</tbody></table>';

		echo '<div class="tablenav"><div class="tablenav-pages">';
		echo '<span class="displaying-num">' . esc_html( (string) $total ) . ' elementos</span> ';
		$base_url = add_query_arg(
			[
				'page' => 'offi-checkout-funnel',
				'view' => $view,
			],
			admin_url( 'admin.php' )
		);
		if ( '' !== $search ) {
			$base_url = add_query_arg( 's', $search, $base_url );
		}
		if ( $paged > 1 ) {
			echo '<a class="button" href="' . esc_url( add_query_arg( 'paged', $paged - 1, $base_url ) ) . '">&laquo; Anterior</a> ';
		}
		echo '<span style="margin:0 8px;">Página ' . esc_html( (string) $paged ) . ' de ' . esc_html( (string) $total_pages ) . '</span>';
		if ( $paged < $total_pages ) {
			echo ' <a class="button" href="' . esc_url( add_query_arg( 'paged', $paged + 1, $base_url ) ) . '">Siguiente &raquo;</a>';
		}
		echo '</div></div>';
		echo '</div>';
	}

	private function render_views_nav( string $current_view ): void {
		$search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';

		$views = [
			'abandoned' => 'Abandonados',
			'pending'   => 'Pedidos pendientes',
			'converted' => 'Recuperados / compras',
			'all'       => 'Todos',
		];

		echo '<ul class="subsubsub">';
		$index = 0;
		foreach ( $views as $slug => $label ) {
			$args = [
				'page' => 'offi-checkout-funnel',
				'view' => $slug,
			];
			if ( '' !== $search ) {
				$args['s'] = $search;
			}
			$url   = add_query_arg( $args, admin_url( 'admin.php' ) );
			$class = $slug === $current_view ? ' class="current" aria-current="page"' : '';
			echo '<li><a href="' . esc_url( $url ) . '"' . $class . '>' . esc_html( $label ) . '</a>';
			if ( $index < ( count( $views ) - 1 ) ) {
				echo ' | ';
			}
			echo '</li>';
			++$index;
		}
		echo '</ul>';
	}

	private function render_table_head( string $view ): void {
		echo '<thead><tr>';
		echo '<th>Fecha</th>';
		echo '<th>Cliente</th>';
		echo '<th>Email</th>';
		echo '<th>Teléfono</th>';
		echo '<th>Tour/producto</th>';
		echo '<th>Total</th>';
		if ( 'abandoned' === $view ) {
			echo '<th>Punto de abandono</th>';
			echo '<th>Acción</th>';
		} else {
			echo '<th>Etapa OFFITRAVEL</th>';
			echo '<th>Estado abandono</th>';
			echo '<th>Pedido</th>';
			echo '<th>Método pago</th>';
			echo '<th>Acciones</th>';
		}
		echo '</tr></thead><tbody>';
	}

	private function sanitize_view( string $view ): string {
		$view = sanitize_key( $view );
		if ( ! in_array( $view, [ 'abandoned', 'pending', 'converted', 'all' ], true ) ) {
			return 'abandoned';
		}
		return $view;
	}

	private function get_rows( int $paged, int $per_page, string $search, string $view ): array {
		global $wpdb;

		$offset = ( $paged - 1 ) * $per_page;
		$params = [];

		$base_sql = $this->get_union_base_sql();

		$where_clauses = [];

		$view_filter = $this->get_view_filter_sql( $view );
		if ( '' !== $view_filter['sql'] ) {
			$where_clauses[] = $view_filter['sql'];
			$params          = array_merge( $params, $view_filter['params'] );
		}

		if ( '' !== $search ) {
			$like            = '%' . $wpdb->esc_like( $search ) . '%';
			$where_clauses[] = "(
				u.session_id LIKE %s OR
				u.lead_email LIKE %s OR
				u.ca_email LIKE %s OR
				u.lead_first_name LIKE %s OR
				u.lead_last_name LIKE %s OR
				u.ca_other_fields LIKE %s OR
				CAST(u.order_id AS CHAR) LIKE %s OR
				CAST(u.lead_id AS CHAR) LIKE %s
			)";
			$params          = array_merge( $params, [ $like, $like, $like, $like, $like, $like, $like, $like ] );
		}

		$where_sql = '';
		if ( ! empty( $where_clauses ) ) {
			$where_sql = ' WHERE ' . implode( ' AND ', $where_clauses );
		}

		$sql = $base_sql . $where_sql . ' ORDER BY COALESCE(u.updated_at, u.ca_time) DESC LIMIT %d OFFSET %d';

		$params[] = $per_page;
		$params[] = $offset;

		$query = $wpdb->prepare( $sql, ...$params );
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return (array) $wpdb->get_results( $query, ARRAY_A );
	}

	private function count_rows( string $search, string $view ): int {
		global $wpdb;

		$params      = [];
		$base_sql    = $this->get_union_base_sql();
		$where_parts = [];

		$view_filter = $this->get_view_filter_sql( $view );
		if ( '' !== $view_filter['sql'] ) {
			$where_parts[] = $view_filter['sql'];
			$params        = array_merge( $params, $view_filter['params'] );
		}

		if ( '' !== $search ) {
			$like          = '%' . $wpdb->esc_like( $search ) . '%';
			$where_parts[] = "(
				u.session_id LIKE %s OR
				u.lead_email LIKE %s OR
				u.ca_email LIKE %s OR
				u.lead_first_name LIKE %s OR
				u.lead_last_name LIKE %s OR
				u.ca_other_fields LIKE %s OR
				CAST(u.order_id AS CHAR) LIKE %s OR
				CAST(u.lead_id AS CHAR) LIKE %s
			)";
			$params        = array_merge( $params, [ $like, $like, $like, $like, $like, $like, $like, $like ] );
		}

		$where_sql = '';
		if ( ! empty( $where_parts ) ) {
			$where_sql = ' WHERE ' . implode( ' AND ', $where_parts );
		}

		$sql = 'SELECT COUNT(*) FROM (' . $base_sql . $where_sql . ') c';

		$query = empty( $params ) ? $sql : $wpdb->prepare( $sql, ...$params );
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return (int) $wpdb->get_var( $query );
	}

	private function get_union_base_sql(): string {
		global $wpdb;

		$leads      = $wpdb->prefix . 'offitravel_checkout_leads';
		$abandon    = $wpdb->prefix . 'cartflows_ca_cart_abandonment';
		$wc_orders  = $wpdb->prefix . 'wc_orders';
		$posts      = $wpdb->posts;
		$postmeta   = $wpdb->postmeta;

		return "
			SELECT * FROM (
				SELECT
					'lead' AS row_source,
					l.id AS lead_id,
					l.wcf_session_id AS session_id,
					l.first_name AS lead_first_name,
					l.last_name AS lead_last_name,
					l.email AS lead_email,
					l.phone AS lead_phone,
					l.product_names AS lead_product_names,
					l.value AS lead_total,
					l.step AS lead_step,
					l.order_id AS order_id,
					l.created_at AS created_at,
					l.updated_at AS updated_at,
					ca.id AS abandonment_id,
					ca.order_status AS abandonment_status,
					ca.email AS ca_email,
					ca.other_fields AS ca_other_fields,
					ca.cart_contents AS ca_cart_contents,
					ca.cart_total AS ca_total,
					ca.time AS ca_time,
					COALESCE(wo.status, p.post_status, '') AS order_status,
					COALESCE(wo.payment_method, pm.meta_value, '') AS order_payment_method
				FROM {$leads} l
				LEFT JOIN {$abandon} ca ON ca.session_id = l.wcf_session_id
				LEFT JOIN {$wc_orders} wo ON wo.id = l.order_id
				LEFT JOIN {$posts} p ON p.ID = l.order_id AND p.post_type = 'shop_order'
				LEFT JOIN {$postmeta} pm ON pm.post_id = l.order_id AND pm.meta_key = '_payment_method_title'

				UNION ALL

				SELECT
					'ca_only' AS row_source,
					NULL AS lead_id,
					ca.session_id AS session_id,
					NULL AS lead_first_name,
					NULL AS lead_last_name,
					NULL AS lead_email,
					NULL AS lead_phone,
					NULL AS lead_product_names,
					NULL AS lead_total,
					NULL AS lead_step,
					NULL AS order_id,
					NULL AS created_at,
					NULL AS updated_at,
					ca.id AS abandonment_id,
					ca.order_status AS abandonment_status,
					ca.email AS ca_email,
					ca.other_fields AS ca_other_fields,
					ca.cart_contents AS ca_cart_contents,
					ca.cart_total AS ca_total,
					ca.time AS ca_time,
					'' AS order_status,
					'' AS order_payment_method
				FROM {$abandon} ca
				LEFT JOIN {$leads} l ON l.wcf_session_id = ca.session_id
				WHERE l.id IS NULL
			) u
		";
	}

	private function get_view_filter_sql( string $view ): array {
		$params = [];

		switch ( $view ) {
			case 'abandoned':
				$sql = "(
					(
						u.abandonment_id IS NOT NULL
						AND COALESCE(u.abandonment_status, '') <> 'completed'
						AND (
							u.lead_id IS NULL
							OR COALESCE(u.lead_step, '') <> 'step_1_completed'
							OR COALESCE(u.order_status, '') = 'wc-failed'
						)
					)
					OR (u.lead_id IS NOT NULL AND COALESCE(u.lead_step, '') = 'step_1_completed' AND u.order_id IS NULL AND COALESCE(u.session_id, '') <> '')
					OR COALESCE(u.order_status, '') = 'wc-failed'
				)
				AND COALESCE(u.order_status, '') NOT IN ('wc-pending', 'wc-on-hold', 'wc-processing', 'wc-completed', 'wc-cancelled', 'wc-refunded')";
				break;

			case 'pending':
				$sql = "COALESCE(u.order_status, '') IN ('wc-pending', 'wc-on-hold')";
				break;

			case 'converted':
				$sql = "(
					COALESCE(u.order_status, '') IN ('wc-processing', 'wc-completed')
					OR COALESCE(u.abandonment_status, '') = 'completed'
				)";
				break;

			case 'all':
			default:
				$sql = '';
				break;
		}

		return [
			'sql'    => $sql,
			'params' => $params,
		];
	}

	private function build_view_row( array $row ): array {
		$date = ! empty( $row['updated_at'] ) ? (string) $row['updated_at'] : (string) ( $row['ca_time'] ?? '' );

		$client = trim( (string) ( $row['lead_first_name'] ?? '' ) . ' ' . (string) ( $row['lead_last_name'] ?? '' ) );
		if ( '' === $client ) {
			$other = maybe_unserialize( $row['ca_other_fields'] ?? '' );
			$first = is_array( $other ) ? (string) ( $other['wcf_first_name'] ?? '' ) : '';
			$last  = is_array( $other ) ? (string) ( $other['wcf_last_name'] ?? '' ) : '';
			$client = trim( $first . ' ' . $last );
		}
		if ( '' === $client ) {
			$client = '-';
		}

		$phone = '-';
		if ( ! empty( $row['lead_phone'] ) ) {
			$phone = (string) $row['lead_phone'];
		} else {
			$other = maybe_unserialize( $row['ca_other_fields'] ?? '' );
			if ( is_array( $other ) ) {
				if ( ! empty( $other['wcf_phone_number'] ) ) {
					$phone = (string) $other['wcf_phone_number'];
				} elseif ( ! empty( $other['wcf_phone'] ) ) {
					$phone = (string) $other['wcf_phone'];
				} elseif ( ! empty( $other['billing_phone'] ) ) {
					$phone = (string) $other['billing_phone'];
				}
			}
		}

		$email = (string) ( $row['lead_email'] ?? '' );
		if ( '' === $email ) {
			$email = (string) ( $row['ca_email'] ?? '' );
		}
		if ( '' === $email ) {
			$email = '-';
		}

		$products = $this->extract_products( $row );

		$total = '-';
		if ( isset( $row['lead_total'] ) && null !== $row['lead_total'] && '' !== (string) $row['lead_total'] ) {
			$total = (string) wc_price( (float) $row['lead_total'] );
		} elseif ( isset( $row['ca_total'] ) && null !== $row['ca_total'] && '' !== (string) $row['ca_total'] ) {
			$total = (string) wc_price( (float) $row['ca_total'] );
		}

		$order_id     = isset( $row['order_id'] ) ? absint( $row['order_id'] ) : 0;
		$order_status = $this->normalize_order_status( (string) ( $row['order_status'] ?? '' ) );
		if ( '' === $order_status ) {
			$order_status = $this->get_order_status( $order_id );
		}

		$payment_method = '-';
		if ( ! empty( $row['order_payment_method'] ) ) {
			$payment_method = (string) $row['order_payment_method'];
		} elseif ( $order_id > 0 ) {
			$order = wc_get_order( $order_id );
			if ( $order ) {
				$title = (string) $order->get_payment_method_title();
				if ( '' !== $title ) {
					$payment_method = $title;
				}
			}
		}

		$offi_stage = $this->compute_offi_stage( $row, $order_status );
		if ( 'completed' === (string) ( $row['abandonment_status'] ?? '' ) ) {
			$offi_stage = 'Carrito recuperado';
		} elseif ( in_array( $order_status, [ 'wc-processing', 'wc-completed' ], true ) && empty( $row['abandonment_id'] ) ) {
			$offi_stage = 'Compra directa';
		} elseif ( in_array( $order_status, [ 'wc-processing', 'wc-completed' ], true ) ) {
			$offi_stage = 'Compra pagada';
		}

		$abandonment_state = '-';
		if ( ! empty( $row['abandonment_id'] ) ) {
			$abandonment_state = $this->translate_abandonment_status( (string) ( $row['abandonment_status'] ?? '-' ) );
		} elseif ( $order_id > 0 ) {
			$abandonment_state = 'No aplicable / compra directa';
		}

		$order_info = '-';
		if ( $order_id > 0 ) {
			$order_info = '#' . $order_id . ' / ' . ( '' !== $order_status ? $this->translate_order_status( $order_status ) : '-' );
		}

		$actions = [];
		$abandonment_id = isset( $row['abandonment_id'] ) ? absint( $row['abandonment_id'] ) : 0;
		if ( $abandonment_id > 0 ) {
			$actions[] = '<a href="' . esc_url( admin_url( 'admin.php?page=woo-cart-abandonment-recovery&path=detailed-report&id=' . $abandonment_id ) ) . '">Detalle abandono</a>';
		}
		if ( $order_id > 0 ) {
			$order = wc_get_order( $order_id );
			if ( $order && method_exists( $order, 'get_edit_order_url' ) ) {
				$actions[] = '<a href="' . esc_url( $order->get_edit_order_url() ) . '">Pedido</a>';
			}
		}

		$abandon_badge_text = '-' !== $abandonment_state ? $abandonment_state : 'Sin abandono';
		$abandon_badge      = $this->build_badge( $abandon_badge_text, '#f0f0f1', '#1d2327' );
		$stage_badge        = $this->build_badge( $offi_stage, '#dbeafe', '#1e3a8a' );

		$abandon_point = $offi_stage;
		if ( ! empty( $row['abandonment_id'] ) && ! empty( $row['abandonment_status'] ) ) {
			$abandon_point .= ' ' . $this->build_badge( $this->translate_abandonment_status( (string) $row['abandonment_status'] ), '#f6f7f7', '#1d2327' );
		}

		return [
			'date'              => $date,
			'client'            => $client,
			'email'             => $email,
			'phone'             => $phone,
			'products'          => $products,
			'total'             => wp_strip_all_tags( $total ),
			'offi_stage'        => $offi_stage,
			'offi_stage_badge'  => $stage_badge,
			'abandonment_state' => $abandonment_state,
			'abandonment_state_badge' => $abandon_badge,
			'abandon_point_html' => $abandon_point,
			'order_info'        => $order_info,
			'payment_method'    => $payment_method,
			'actions_html'      => implode( ' | ', $actions ),
		];
	}

	private function extract_products( array $row ): string {
		if ( ! empty( $row['lead_product_names'] ) ) {
			$names = json_decode( (string) $row['lead_product_names'], true );
			if ( is_array( $names ) && ! empty( $names ) ) {
				return implode( ', ', array_map( 'sanitize_text_field', $names ) );
			}
		}

		if ( ! empty( $row['ca_cart_contents'] ) ) {
			$cart = maybe_unserialize( $row['ca_cart_contents'] );
			if ( is_array( $cart ) && ! empty( $cart ) ) {
				$names = [];
				foreach ( $cart as $item ) {
					if ( ! is_array( $item ) ) {
						continue;
					}
					$product_id = isset( $item['variation_id'] ) && (int) $item['variation_id'] > 0
						? (int) $item['variation_id']
						: ( isset( $item['product_id'] ) ? (int) $item['product_id'] : 0 );
					if ( $product_id <= 0 ) {
						continue;
					}
					$product = wc_get_product( $product_id );
					if ( $product ) {
						$names[] = $product->get_name();
					}
				}
				$names = array_values( array_unique( array_filter( $names ) ) );
				if ( ! empty( $names ) ) {
					return implode( ', ', $names );
				}
			}
		}

		return '-';
	}

	private function get_order_status( int $order_id ): string {
		if ( $order_id <= 0 ) {
			return '';
		}
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return '';
		}
		$status = (string) $order->get_status();
		if ( '' === $status ) {
			return '';
		}
		return 'wc-' . $status;
	}

	private function normalize_order_status( string $status ): string {
		if ( '' === $status ) {
			return '';
		}
		if ( 0 === strpos( $status, 'wc-' ) ) {
			return $status;
		}
		return 'wc-' . ltrim( $status, '-' );
	}

	private function compute_offi_stage( array $row, string $order_status ): string {
		$lead_id      = isset( $row['lead_id'] ) ? absint( $row['lead_id'] ) : 0;
		$order_id     = isset( $row['order_id'] ) ? absint( $row['order_id'] ) : 0;
		$lead_step    = (string) ( $row['lead_step'] ?? '' );
		$has_abandon  = ! empty( $row['abandonment_id'] );
		$session_id   = (string) ( $row['session_id'] ?? '' );

		if ( $lead_id <= 0 && $has_abandon ) {
			return 'Step 1 - Datos de contacto';
		}

		if ( $lead_id > 0 && $order_id <= 0 && 'step_1_completed' === $lead_step ) {
			return 'Step 2 - Pago';
		}

		if ( $order_id > 0 ) {
			if ( in_array( $order_status, [ 'wc-pending', 'wc-on-hold' ], true ) ) {
				return 'Pedido creado pendiente';
			}
			if ( in_array( $order_status, [ 'wc-processing', 'wc-completed' ], true ) ) {
				return 'Compra pagada';
			}
			if ( 'wc-failed' === $order_status ) {
				return 'Pago fallido';
			}
			if ( 'wc-cancelled' === $order_status ) {
				return 'Cancelado';
			}
			if ( 'wc-refunded' === $order_status ) {
				return 'Reembolsado';
			}
			return 'Pedido creado';
		}

		if ( $lead_id > 0 && '' === $session_id && ! $has_abandon ) {
			return 'Histórico sin vínculo';
		}

		return 'Sin clasificar';
	}

	private function translate_abandonment_status( string $status ): string {
		$map = [
			'normal'    => 'Normal',
			'abandoned' => 'Abandonado',
			'completed' => 'Recuperado',
			'lost'      => 'Perdido',
		];

		return $map[ $status ] ?? $status;
	}

	private function translate_order_status( string $status ): string {
		$map = [
			'wc-processing' => 'Procesando',
			'wc-completed'  => 'Completado',
			'wc-on-hold'    => 'En espera',
			'wc-pending'    => 'Pendiente de pago',
			'wc-failed'     => 'Fallido',
			'wc-cancelled'  => 'Cancelado',
			'wc-refunded'   => 'Reembolsado',
		];

		return $map[ $status ] ?? $status;
	}

	private function build_badge( string $text, string $bg, string $fg ): string {
		$style = sprintf(
			'display:inline-block;padding:2px 8px;border-radius:999px;background:%s;color:%s;font-size:12px;font-weight:600;line-height:1.8;',
			esc_attr( $bg ),
			esc_attr( $fg )
		);

		return '<span style="' . $style . '">' . esc_html( $text ) . '</span>';
	}
}
