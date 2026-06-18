<?php
/**
 * Ofi_Cab_Sync – Sincronización de wcf_session_id en ambos órdenes de ejecución.
 *
 * ──────────────────────────────────────────────────────────────────────────────
 * CASO A: plugin de abandono se ejecuta primero
 * ──────────────────────────────────────────────────────────────────────────────
 * Secuencia:
 *   1. Usuario escribe email → plugin captura abandono → guarda session_id en
 *      WC()->session->set('wcf_session_id', …).
 *   2. Usuario pulsa "Continuar" → checkout-step-leads.php guarda el lead.
 *   3. checkout-step-leads.php dispara do_action('ofi_after_lead_upsert', $lead_id, $session_key).
 *   4. on_after_lead_upsert() lee WC()->session->get('wcf_session_id') y lo
 *      persiste directamente en el lead recién guardado.
 *
 * CASO B: lead se guarda primero
 * ──────────────────────────────────────────────────────────────────────────────
 * Secuencia:
 *   1. Usuario pulsa "Continuar" → lead guardado sin wcf_session_id.
 *   2. Plugin captura abandono (keypress/change previo o simultáneo).
 *   3. Plugin dispara wcf_ca_after_save_abandonment_data($session_id, $checkout_details).
 *   4. on_wcf_ca_after_save() encuentra el lead pendiente por session_key y
 *      escribe wcf_session_id en ese lead.
 *
 * ──────────────────────────────────────────────────────────────────────────────
 * Invariantes de idempotencia:
 *   – Nunca sobreescribe un wcf_session_id distinto ya vinculado.
 *   – Nunca actualiza leads que ya tengan order_id.
 *   – La relación canónica es wcf_session_id; el email no se usa como criterio.
 *   – Si hay varios leads pendientes para el mismo session_key, se elige el
 *     más reciente (ORDER BY updated_at DESC LIMIT 1).
 *   – El UPDATE incluye una guarda WHERE (wcf_session_id IS NULL OR wcf_session_id = '')
 *     para que sea atómicamente idempotente.
 *
 * ──────────────────────────────────────────────────────────────────────────────
 * Logging:
 *   – Solo activo cuando WP_DEBUG === true.
 *   – No imprime datos personales; los identificadores se enmascaran parcialmente.
 *
 * @package Ofi_Cab
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Ofi_Cab_Sync {

	private static ?self $instance = null;

	private function __construct() {}

	public static function get_instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Registra los dos hooks necesarios para ambos casos.
	 * Se llama desde ofi_cab_boot() en plugins_loaded/20.
	 */
	public function register_hooks(): void {
		/*
		 * Caso A: lead se acaba de guardar (action emitida desde checkout-step-leads.php).
		 * Argumentos: $lead_id (int), $session_key (string).
		 */
		add_action( 'ofi_after_lead_upsert', [ $this, 'on_after_lead_upsert' ], 10, 2 );

		/*
		 * Caso B: el plugin de abandono acaba de guardar o actualizar su registro.
		 * Argumentos: $session_id (string), $checkout_details (array).
		 * Firma verificada en class-cartflows-ca-tracking.php línea 823.
		 */
		add_action( 'wcf_ca_after_save_abandonment_data', [ $this, 'on_wcf_ca_after_save' ], 10, 2 );

		/*
		 * Fase 2: enlace del pedido al lead usando la relación canónica wcf_session_id.
		 * Hook principal: checkout_order_processed (la sesión WC aún suele estar viva).
		 * Hooks de respaldo: new_order y thankyou.
		 */
		add_action( 'woocommerce_checkout_order_processed', [ $this, 'on_checkout_order_processed' ], 20, 3 );
		add_action( 'woocommerce_new_order', [ $this, 'on_new_order' ], 20, 1 );
		add_action( 'woocommerce_thankyou', [ $this, 'on_thankyou' ], 5, 1 );

		/*
		 * Permite que la función legacy del tema delegue primero en el bridge.
		 */
		add_filter( 'ofi_cab_link_order_to_lead_by_session', [ $this, 'filter_link_order_to_lead_by_session' ], 10, 2 );
		add_filter( 'ofi_cab_disable_legacy_order_link_fallback', '__return_true', 10, 2 );
	}

	// ──────────────────────────────────────────────────────────────────────────
	// Handlers públicos (llamados por WordPress via do_action / add_action)
	// ──────────────────────────────────────────────────────────────────────────

	/**
	 * Caso A: se llama justo después de que checkout-step-leads.php guarda el lead.
	 *
	 * @param int    $lead_id     ID del lead recién insertado o actualizado.
	 * @param string $session_key session_key de WooCommerce del lead (= customer_id de sesión).
	 */
	public function on_after_lead_upsert( int $lead_id, string $session_key ): void {
		if ( $lead_id <= 0 ) {
			return;
		}

		// Leer el session_id que el plugin de abandono haya guardado en WC session.
		$wcf_session_id = $this->get_current_wcf_session_id();

		if ( '' === $wcf_session_id ) {
			$this->debug_log(
				'Caso A: wcf_session_id no disponible en sesión WC; sin acción.',
				[ 'lead_id' => $lead_id ]
			);
			return;
		}

		$updated = $this->update_lead_wcf_session_id( $lead_id, $wcf_session_id );

		$this->debug_log(
			'Caso A: resultado de vinculación',
			[
				'lead_id'             => $lead_id,
				'wcf_session_masked'  => $this->mask( $wcf_session_id ),
				'updated'             => $updated,
			]
		);
	}

	/**
	 * Caso B: el plugin de abandono acaba de guardar su registro.
	 *
	 * @param string $session_id       session_id del abandono recién guardado (md5, 32 chars).
	 * @param array  $checkout_details Datos del abandono preparados por el plugin.
	 */
	public function on_wcf_ca_after_save( string $session_id, array $checkout_details ): void {
		if ( '' === trim( $session_id ) ) {
			return;
		}

		/*
		 * Verificar si el lead ya fue vinculado en el mismo request (Caso A ejecutado
		 * antes porque el AJAX del lead llegó primero en esta misma petición del navegador).
		 * Si ya existe un lead con este session_id, no hay nada que hacer.
		 */
		if ( $this->lead_exists_for_session_id( $session_id ) ) {
			$this->debug_log(
				'Caso B: lead ya vinculado (Caso A lo procesó antes); sin acción.',
				[ 'wcf_session_masked' => $this->mask( $session_id ) ]
			);
			return;
		}

		// Obtener el session_key del visitante actual desde la sesión WooCommerce.
		$session_key = $this->get_current_wc_session_key();

		if ( '' === $session_key ) {
			$this->debug_log(
				'Caso B: session_key WC no disponible; sin acción.',
				[ 'wcf_session_masked' => $this->mask( $session_id ) ]
			);
			return;
		}

		// Buscar el lead pendiente más reciente para este session_key.
		$lead_id = $this->find_pending_lead_id_by_session_key( $session_key );

		if ( $lead_id <= 0 ) {
			$this->debug_log(
				'Caso B: no se encontró lead pendiente para este session_key.',
				[ 'wcf_session_masked' => $this->mask( $session_id ) ]
			);
			return;
		}

		$updated = $this->update_lead_wcf_session_id( $lead_id, $session_id );

		$this->debug_log(
			'Caso B: resultado de vinculación',
			[
				'lead_id'            => $lead_id,
				'wcf_session_masked' => $this->mask( $session_id ),
				'updated'            => $updated,
			]
		);
	}

	/**
	 * Hook principal de WooCommerce para enlazar el pedido al lead correcto.
	 *
	 * @param int      $order_id     ID del pedido recién creado.
	 * @param array    $posted_data  Datos posteados del checkout.
	 * @param WC_Order $order        Objeto pedido.
	 */
	public function on_checkout_order_processed( int $order_id, array $posted_data, $order ): void {
		$this->link_order_to_lead_by_session( $order_id, 'checkout_order_processed' );
	}

	/**
	 * Hook de respaldo. En algunos flujos se dispara muy temprano, así que es best effort.
	 *
	 * @param int $order_id ID del pedido.
	 */
	public function on_new_order( int $order_id ): void {
		$this->link_order_to_lead_by_session( $order_id, 'new_order' );
	}

	/**
	 * Hook de respaldo tardío. Corre antes del thankyou tracking del tema (prioridad 5).
	 *
	 * @param int $order_id ID del pedido.
	 */
	public function on_thankyou( int $order_id ): void {
		$this->link_order_to_lead_by_session( $order_id, 'thankyou' );
	}

	/**
	 * Filtro consumido por la función legacy del tema para enlazar canónicamente.
	 *
	 * @param int      $lead_id ID ya resuelto por otros filtros (si lo hubiera).
	 * @param WC_Order $order   Pedido actual.
	 * @return int
	 */
	public function filter_link_order_to_lead_by_session( int $lead_id, $order ): int {
		if ( $lead_id > 0 ) {
			return $lead_id;
		}

		if ( ! is_object( $order ) || ! method_exists( $order, 'get_id' ) ) {
			return 0;
		}

		return $this->link_order_to_lead_by_session( (int) $order->get_id(), 'legacy_theme_filter' );
	}

	// ──────────────────────────────────────────────────────────────────────────
	// Helpers privados
	// ──────────────────────────────────────────────────────────────────────────

	/**
	 * Enlaza un pedido al lead canónico por wcf_session_id.
	 *
	 * Invariantes:
	 *   – No usa email como criterio canónico.
	 *   – No toca leads con order_id ya asignado.
	 *   – Si el pedido ya está enlazado, devuelve ese lead y sale.
	 *   – Si hay varios leads con el mismo session_id, toma el más reciente sin order_id.
	 *
	 * @param  int    $order_id ID del pedido.
	 * @param  string $source   Fuente del intento para logging.
	 * @return int    Lead ID actualizado o ya enlazado; 0 si no hubo vínculo.
	 */
	private function link_order_to_lead_by_session( int $order_id, string $source ): int {
		if ( $order_id <= 0 ) {
			return 0;
		}

		$existing_lead_id = $this->find_lead_id_by_order_id( $order_id );
		if ( $existing_lead_id > 0 ) {
			$this->debug_log(
				'Pedido ya enlazado; sin acción.',
				[
					'order_id' => $order_id,
					'lead_id'  => $existing_lead_id,
					'source'   => $source,
				]
			);
			return $existing_lead_id;
		}

		$wcf_session_id = $this->get_current_wcf_session_id();
		if ( '' === $wcf_session_id ) {
			$this->debug_log(
				'No hay wcf_session_id disponible al intentar enlazar pedido.',
				[
					'order_id' => $order_id,
					'source'   => $source,
				]
			);
			return 0;
		}

		$lead_id = $this->find_pending_lead_id_by_wcf_session_id( $wcf_session_id );
		if ( $lead_id <= 0 ) {
			$this->debug_log(
				'No se encontró lead pendiente para el wcf_session_id del pedido.',
				[
					'order_id'            => $order_id,
					'wcf_session_masked' => $this->mask( $wcf_session_id ),
					'source'              => $source,
				]
			);
			return 0;
		}

		$updated = $this->attach_order_to_lead( $lead_id, $order_id );
		$this->debug_log(
			'Resultado de vínculo pedido-lead.',
			[
				'order_id'            => $order_id,
				'lead_id'             => $lead_id,
				'wcf_session_masked' => $this->mask( $wcf_session_id ),
				'updated'             => $updated,
				'source'              => $source,
			]
		);

		return $updated ? $lead_id : 0;
	}

	/**
	 * Lee el session_id del plugin de abandono desde la sesión WooCommerce actual.
	 * Devuelve '' si WC o la sesión no están disponibles, o si el valor no existe.
	 */
	private function get_current_wcf_session_id(): string {
		if ( ! function_exists( 'WC' ) || ! WC()->session ) {
			return '';
		}

		$value = WC()->session->get( 'wcf_session_id' );
		return ( is_string( $value ) && '' !== $value ) ? $value : '';
	}

	/**
	 * Obtiene el customer_id de la sesión WooCommerce, que coincide con session_key
	 * almacenado en of_offitravel_checkout_leads por checkout-step-leads.php.
	 */
	private function get_current_wc_session_key(): string {
		if ( ! function_exists( 'WC' ) || ! WC()->session ) {
			return '';
		}

		if ( ! method_exists( WC()->session, 'get_customer_id' ) ) {
			return '';
		}

		$key = (string) WC()->session->get_customer_id();
		return '' !== $key ? $key : '';
	}

	/**
	 * Comprueba si ya existe algún lead vinculado con el session_id dado.
	 * Usado para evitar trabajo duplicado cuando Caso A ya actuó en el mismo request.
	 */
	private function lead_exists_for_session_id( string $session_id ): bool {
		global $wpdb;

		$table = $wpdb->prefix . 'offitravel_checkout_leads';

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM `{$table}` WHERE wcf_session_id = %s LIMIT 1",
				$session_id
			)
		);

		return null !== $id;
	}

	/**
	 * Devuelve el ID del lead pendiente más reciente para un session_key dado.
	 *
	 * "Pendiente" = wcf_session_id todavía no asignado + sin order_id.
	 * En caso de varios leads (reenvíos del formulario), se elige el más reciente.
	 *
	 * @param  string $session_key session_key de WooCommerce.
	 * @return int    ID del lead encontrado, 0 si no hay ninguno.
	 */
	private function find_pending_lead_id_by_session_key( string $session_key ): int {
		global $wpdb;

		$table = $wpdb->prefix . 'offitravel_checkout_leads';

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM `{$table}`
				 WHERE session_key = %s
				   AND ( wcf_session_id IS NULL OR wcf_session_id = '' )
				   AND order_id IS NULL
				 ORDER BY updated_at DESC
				 LIMIT 1",
				$session_key
			)
		);

		return (int) ( $id ?? 0 );
	}

	/**
	 * Devuelve el lead pendiente más reciente para un wcf_session_id dado.
	 *
	 * @param  string $session_id session_id del plugin de abandono.
	 * @return int
	 */
	private function find_pending_lead_id_by_wcf_session_id( string $session_id ): int {
		global $wpdb;

		$table = $wpdb->prefix . 'offitravel_checkout_leads';

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM `{$table}`
				 WHERE wcf_session_id = %s
				   AND order_id IS NULL
				 ORDER BY updated_at DESC
				 LIMIT 1",
				$session_id
			)
		);

		return (int) ( $id ?? 0 );
	}

	/**
	 * Busca si el pedido ya fue vinculado previamente a algún lead.
	 *
	 * @param  int $order_id ID del pedido.
	 * @return int
	 */
	private function find_lead_id_by_order_id( int $order_id ): int {
		global $wpdb;

		$table = $wpdb->prefix . 'offitravel_checkout_leads';

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM `{$table}` WHERE order_id = %d LIMIT 1",
				$order_id
			)
		);

		return (int) ( $id ?? 0 );
	}

	/**
	 * Escribe wcf_session_id en un lead concreto de forma idempotente.
	 *
	 * La guarda WHERE asegura que:
	 *   – No sobreescribe un session_id distinto ya vinculado.
	 *   – La operación es segura aunque se llame dos veces con el mismo valor.
	 *
	 * @param  int    $lead_id    ID del lead a actualizar.
	 * @param  string $session_id session_id del plugin de abandono.
	 * @return bool   true si se actualizó al menos una fila, false en cualquier otro caso.
	 */
	private function update_lead_wcf_session_id( int $lead_id, string $session_id ): bool {
		global $wpdb;

		$table = $wpdb->prefix . 'offitravel_checkout_leads';

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$affected = $wpdb->query(
			$wpdb->prepare(
				"UPDATE `{$table}`
				 SET wcf_session_id = %s,
				     updated_at     = %s
				 WHERE id = %d
				   AND ( wcf_session_id IS NULL OR wcf_session_id = '' )
				   AND order_id IS NULL
				 LIMIT 1",
				$session_id,
				current_time( 'mysql' ),
				$lead_id
			)
		);

		return is_int( $affected ) && $affected > 0;
	}

	/**
	 * Asigna order_id y step=order_placed a un lead canónico.
	 *
	 * @param  int $lead_id  ID del lead.
	 * @param  int $order_id ID del pedido.
	 * @return bool
	 */
	private function attach_order_to_lead( int $lead_id, int $order_id ): bool {
		global $wpdb;

		$table = $wpdb->prefix . 'offitravel_checkout_leads';

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$affected = $wpdb->query(
			$wpdb->prepare(
				"UPDATE `{$table}`
				 SET order_id   = %d,
				     step       = %s,
				     updated_at = %s
				 WHERE id = %d
				   AND order_id IS NULL
				 LIMIT 1",
				$order_id,
				'order_placed',
				current_time( 'mysql' ),
				$lead_id
			)
		);

		return is_int( $affected ) && $affected > 0;
	}

	/**
	 * Enmascara los primeros 4 caracteres de un identificador para el log.
	 * Los 4 primeros caracteres son suficientes para correlacionar en debugging
	 * sin exponer el valor completo.
	 */
	private function mask( string $value ): string {
		$len = strlen( $value );
		if ( $len <= 4 ) {
			return '****';
		}
		return substr( $value, 0, 4 ) . str_repeat( '*', $len - 4 );
	}

	/**
	 * Escribe en el log de errores de PHP solo cuando WP_DEBUG está activo.
	 * Nunca incluye datos personales (email, nombre, teléfono, dirección).
	 *
	 * @param string $message Mensaje descriptivo del evento.
	 * @param array  $context Datos de contexto (IDs y valores enmascarados).
	 */
	private function debug_log( string $message, array $context ): void {
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			return;
		}
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( '[OFI CAB] ' . $message . ' | ' . wp_json_encode( $context ) );
	}
}
