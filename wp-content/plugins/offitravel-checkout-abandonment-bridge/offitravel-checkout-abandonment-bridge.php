<?php
/**
 * Plugin Name:  Offitravel Checkout Abandonment Bridge
 * Description:  Fase 1 – Vincula of_offitravel_checkout_leads con of_cartflows_ca_cart_abandonment
 *               mediante la columna wcf_session_id. No modifica ningún plugin externo.
 * Version:      1.0.0
 * Author:       Offitravel
 * License:      GPL-2.0+
 * Text Domain:  ofi-cab
 *
 * Alcance Fase 1:
 *   – Migración idempotente de la columna wcf_session_id + índice.
 *   – Sincronización canónica en ambos órdenes de ejecución (Caso A y Caso B).
 *
 * Fuera de alcance (fases posteriores):
 *   – Pantalla administrativa / WP_List_Table.
 *   – Relación histórica / tabla de_offi_checkout_ca_links.
 *   – Vínculo con pedidos.
 *   – Retención / privacidad.
 *   – Eventos Meta.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'OFI_CAB_VERSION',    '1.0.0' );
define( 'OFI_CAB_DB_VERSION', '1.0.0' );
define( 'OFI_CAB_FILE',       __FILE__ );
define( 'OFI_CAB_DIR',        plugin_dir_path( __FILE__ ) );

/**
 * Punto de entrada. Se ejecuta en plugins_loaded/20, después de WooCommerce
 * y del tema hijo (que registra sus AJAX handlers en init/wp_ajax_*).
 */
function ofi_cab_boot(): void {
	require_once OFI_CAB_DIR . 'includes/class-ofi-cab-installer.php';
	require_once OFI_CAB_DIR . 'includes/class-ofi-cab-sync.php';
	require_once OFI_CAB_DIR . 'includes/class-ofi-cab-admin-page.php';

	Ofi_Cab_Installer::get_instance()->maybe_migrate();
	Ofi_Cab_Sync::get_instance()->register_hooks();
	Ofi_Cab_Admin_Page::get_instance()->register_hooks();
}
add_action( 'plugins_loaded', 'ofi_cab_boot', 20 );
