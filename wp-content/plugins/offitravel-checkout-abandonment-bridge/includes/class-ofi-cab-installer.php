<?php
/**
 * Ofi_Cab_Installer – Migración idempotente de la columna wcf_session_id.
 *
 * Responsabilidades:
 *   – Añadir la columna wcf_session_id varchar(60) NULL a of_offitravel_checkout_leads.
 *   – Añadir el índice idx_wcf_session_id si no existe.
 *   – No tocar ninguna tabla del plugin woo-cart-abandonment-recovery.
 *   – No destruir datos en rollback (la columna se elimina solo con uninstall explícito).
 *
 * Idempotencia:
 *   – Comprueba SHOW COLUMNS y SHOW INDEX antes de cada ALTER TABLE.
 *   – Guarda la versión en ofi_cab_db_version; si ya coincide, sale sin ejecutar nada.
 *
 * @package Ofi_Cab
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Ofi_Cab_Installer {

	private static ?self $instance = null;

	private function __construct() {}

	public static function get_instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Ejecuta la migración solo si la versión instalada no coincide con OFI_CAB_DB_VERSION.
	 */
	public function maybe_migrate(): void {
		$installed = (string) get_option( 'ofi_cab_db_version', '' );

		if ( version_compare( $installed, OFI_CAB_DB_VERSION, '>=' ) ) {
			return;
		}

		$this->add_wcf_session_id_column();
		$this->add_session_id_index();

		update_option( 'ofi_cab_db_version', OFI_CAB_DB_VERSION, false );
	}

	/**
	 * Añade wcf_session_id varchar(60) NULL después de session_key.
	 * No ejecuta ALTER TABLE si la columna ya existe.
	 */
	private function add_wcf_session_id_column(): void {
		global $wpdb;

		$table = $wpdb->prefix . 'offitravel_checkout_leads';

		// SHOW COLUMNS es idempotente y no requiere privilegios de INFORMATION_SCHEMA.
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$exists = $wpdb->get_results(
			$wpdb->prepare( "SHOW COLUMNS FROM `{$table}` LIKE %s", 'wcf_session_id' )
		);

		if ( ! empty( $exists ) ) {
			return; // La columna ya existe; nada que hacer.
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$wpdb->query(
			"ALTER TABLE `{$table}` ADD COLUMN `wcf_session_id` varchar(60) NULL DEFAULT NULL AFTER `session_key`"
		);
	}

	/**
	 * Añade el índice idx_wcf_session_id si no existe.
	 */
	private function add_session_id_index(): void {
		global $wpdb;

		$table = $wpdb->prefix . 'offitravel_checkout_leads';

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$exists = $wpdb->get_results(
			$wpdb->prepare( "SHOW INDEX FROM `{$table}` WHERE Key_name = %s", 'idx_wcf_session_id' )
		);

		if ( ! empty( $exists ) ) {
			return; // El índice ya existe; nada que hacer.
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$wpdb->query(
			"ALTER TABLE `{$table}` ADD KEY `idx_wcf_session_id` (`wcf_session_id`)"
		);
	}
}
