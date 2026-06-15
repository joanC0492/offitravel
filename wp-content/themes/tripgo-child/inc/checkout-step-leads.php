<?php
/**
 * OFFITRAVEL - Checkout step leads (step 1)
 *
 * Guarda datos no sensibles del paso 1 para recuperación de checkout abandonado.
 *
 * Nota legal (UE/Espana): validar con el cliente el encaje final con su politica
 * de privacidad/cookies y base juridica antes de activar en produccion.
 */

if (!defined('ABSPATH')) {
	exit;
}

if (!defined('OFFITRAVEL_CHECKOUT_LEADS_DB_VERSION')) {
	define('OFFITRAVEL_CHECKOUT_LEADS_DB_VERSION', '1.0.1');
}

/**
 * Nombre de tabla custom.
 *
 * @return string
 */
function offitravel_checkout_leads_table_name()
{
	global $wpdb;
	return $wpdb->prefix . 'offitravel_checkout_leads';
}

/**
 * Crea/actualiza tabla con dbDelta solo cuando cambia version.
 */
function offitravel_maybe_create_checkout_leads_table()
{
	static $already_ran = false;

	if ($already_ran) {
		return;
	}

	$already_ran = true;

	$current_version = get_option('offitravel_checkout_leads_db_version');
	if ($current_version === OFFITRAVEL_CHECKOUT_LEADS_DB_VERSION) {
		return;
	}

	global $wpdb;

	$table_name = offitravel_checkout_leads_table_name();
	$charset_collate = $wpdb->get_charset_collate();

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	$sql = "CREATE TABLE {$table_name} (
		id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		session_key VARCHAR(191) NOT NULL DEFAULT '',
		cart_hash VARCHAR(191) NOT NULL DEFAULT '',
		email VARCHAR(191) NOT NULL DEFAULT '',
		first_name VARCHAR(191) NOT NULL DEFAULT '',
		last_name VARCHAR(191) NOT NULL DEFAULT '',
		phone VARCHAR(80) NOT NULL DEFAULT '',
		company VARCHAR(191) NOT NULL DEFAULT '',
		country VARCHAR(20) NOT NULL DEFAULT '',
		state VARCHAR(80) NOT NULL DEFAULT '',
		state_name VARCHAR(191) NOT NULL DEFAULT '',
		city VARCHAR(191) NOT NULL DEFAULT '',
		postcode VARCHAR(80) NOT NULL DEFAULT '',
		address_1 TEXT NULL,
		address_2 TEXT NULL,
		order_comments TEXT NULL,
		product_ids LONGTEXT NULL,
		product_names LONGTEXT NULL,
		contents LONGTEXT NULL,
		value DECIMAL(18,2) NOT NULL DEFAULT 0.00,
		currency VARCHAR(10) NOT NULL DEFAULT '',
		step VARCHAR(80) NOT NULL DEFAULT '',
		order_id BIGINT(20) UNSIGNED NULL,
		user_id BIGINT(20) UNSIGNED NULL,
		user_agent TEXT NULL,
		ip_address VARCHAR(100) NOT NULL DEFAULT '',
		created_at DATETIME NOT NULL,
		updated_at DATETIME NOT NULL,
		PRIMARY KEY  (id),
		KEY idx_session_cart (session_key, cart_hash),
		KEY idx_email (email),
		KEY idx_order_id (order_id),
		KEY idx_updated_at (updated_at)
	) {$charset_collate};";

	dbDelta($sql);

	update_option('offitravel_checkout_leads_db_version', OFFITRAVEL_CHECKOUT_LEADS_DB_VERSION);
}
add_action('init', 'offitravel_maybe_create_checkout_leads_table', 5);
add_action('admin_init', 'offitravel_maybe_create_checkout_leads_table', 5);

/**
 * IP cliente (best effort).
 *
 * @return string
 */
function offitravel_checkout_get_client_ip()
{
	$keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');

	foreach ($keys as $key) {
		if (empty($_SERVER[$key])) {
			continue;
		}

		$value = sanitize_text_field(wp_unslash($_SERVER[$key]));
		if ('HTTP_X_FORWARDED_FOR' === $key && strpos($value, ',') !== false) {
			$parts = explode(',', $value);
			$value = trim((string) $parts[0]);
		}

		if (!empty($value)) {
			return substr($value, 0, 100);
		}
	}

	return '';
}

/**
 * Construye payload ecommerce desde carrito (sin datos personales).
 *
 * @return array
 */
function offitravel_checkout_get_cart_tracking_data()
{
	$data = array(
		'product_ids' => array(),
		'product_names' => array(),
		'contents' => array(),
		'value' => 0.0,
		'currency' => function_exists('get_woocommerce_currency') ? (string) get_woocommerce_currency() : '',
		'cart_hash' => '',
		'session_key' => '',
		'content_name' => '',
		'content_ids' => array(),
	);


	if (!function_exists('WC') || !WC()->cart) {
		return $data;
	}

	$cart = WC()->cart;
	$data['cart_hash'] = method_exists($cart, 'get_cart_hash') ? (string) $cart->get_cart_hash() : '';
	$data['value'] = (float) $cart->get_total('edit');

	if (WC()->session && method_exists(WC()->session, 'get_customer_id')) {
		$data['session_key'] = (string) WC()->session->get_customer_id();
	}

	foreach ($cart->get_cart() as $cart_item) {
		if (empty($cart_item['data']) || !is_object($cart_item['data'])) {
			continue;
		}

		$product = $cart_item['data'];
		$product_id = (int) $product->get_id();
		$quantity = isset($cart_item['quantity']) ? (int) $cart_item['quantity'] : 1;
		$quantity = max(1, $quantity);

		$line_total = isset($cart_item['line_total']) ? (float) $cart_item['line_total'] : 0.0;
		$item_price = $quantity > 0 ? round($line_total / $quantity, 2) : 0.0;

		$data['product_ids'][] = $product_id;
		$data['product_names'][] = (string) $product->get_name();
		$data['contents'][] = array(
			'id' => $product_id,
			'quantity' => $quantity,
			'item_price' => $item_price,
		);
	}

	$data['product_ids'] = array_values(array_unique(array_filter($data['product_ids'])));
	$data['product_names'] = array_values(array_unique(array_filter($data['product_names'])));
	$data['content_ids'] = $data['product_ids'];
	$data['content_name'] = implode(', ', $data['product_names']);

	$num_items = 0;
	if (!empty($data['contents']) && is_array($data['contents'])) {
		foreach ($data['contents'] as $content) {
			$num_items += isset($content['quantity']) ? (int) $content['quantity'] : 0;
		}
	}

	$data['content_type'] = 'product';
	$data['num_items'] = max(0, $num_items);

	return $data;
}

/**
 * Obtiene nombre legible de provincia/estado a partir del codigo.
 *
 * @param string $country Codigo de pais.
 * @param string $state Codigo de provincia/estado.
 * @return string
 */
function offitravel_checkout_get_state_name($country, $state)
{
	$country = strtoupper(trim((string) $country));
	$state = strtoupper(trim((string) $state));

	if ('' === $country || '' === $state || !function_exists('WC') || !WC()->countries) {
		return '';
	}

	$states = WC()->countries->get_states($country);
	if (!is_array($states) || empty($states[$state])) {
		return '';
	}

	return (string) $states[$state];
}

/**
 * Inserta/actualiza lead de step 1.
 *
 * @param array $fields Campos sanitizados del formulario.
 * @return array|WP_Error
 */
function offitravel_checkout_upsert_step_lead($fields)
{
	global $wpdb;

	$table_name = offitravel_checkout_leads_table_name();
	$now = current_time('mysql');
	$user_id = get_current_user_id();

	$tracking = offitravel_checkout_get_cart_tracking_data();
	$country = isset($fields['billing_country']) ? strtoupper(trim((string) $fields['billing_country'])) : '';
	$state = isset($fields['billing_state']) ? strtoupper(trim((string) $fields['billing_state'])) : '';

	$record = array(
		'session_key' => isset($tracking['session_key']) ? (string) $tracking['session_key'] : '',
		'cart_hash' => isset($tracking['cart_hash']) ? (string) $tracking['cart_hash'] : '',
		'email' => isset($fields['billing_email']) ? (string) $fields['billing_email'] : '',
		'first_name' => isset($fields['billing_first_name']) ? (string) $fields['billing_first_name'] : '',
		'last_name' => isset($fields['billing_last_name']) ? (string) $fields['billing_last_name'] : '',
		'phone' => isset($fields['billing_phone']) ? (string) $fields['billing_phone'] : '',
		'company' => isset($fields['billing_company']) ? (string) $fields['billing_company'] : '',
		'country' => $country,
		'state' => $state,
		'state_name' => offitravel_checkout_get_state_name($country, $state),
		'city' => isset($fields['billing_city']) ? (string) $fields['billing_city'] : '',
		'postcode' => isset($fields['billing_postcode']) ? (string) $fields['billing_postcode'] : '',
		'address_1' => isset($fields['billing_address_1']) ? (string) $fields['billing_address_1'] : '',
		'address_2' => isset($fields['billing_address_2']) ? (string) $fields['billing_address_2'] : '',
		'order_comments' => isset($fields['order_comments']) ? (string) $fields['order_comments'] : '',
		'product_ids' => wp_json_encode($tracking['product_ids']),
		'product_names' => wp_json_encode($tracking['product_names']),
		'contents' => wp_json_encode($tracking['contents']),
		'value' => round((float) $tracking['value'], 2),
		'currency' => isset($tracking['currency']) ? (string) $tracking['currency'] : '',
		'step' => 'step_1_completed',
		'user_id' => $user_id > 0 ? (int) $user_id : null,
		'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '',
		'ip_address' => offitravel_checkout_get_client_ip(),
		'updated_at' => $now,
	);

	$existing_id = 0;

	if (!empty($record['session_key']) && !empty($record['cart_hash'])) {
		$existing_id = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$table_name} WHERE session_key = %s AND cart_hash = %s ORDER BY id DESC LIMIT 1",
				$record['session_key'],
				$record['cart_hash']
			)
		);
	}

	if ($existing_id > 0) {
		$updated = $wpdb->update(
			$table_name,
			$record,
			array('id' => $existing_id),
			array(
				'%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s',
				'%s', '%s', '%s', '%s', '%s', '%s', '%f', '%s', '%s', '%d', '%s', '%s', '%s'
			),
			array('%d')
		);

		if (false === $updated) {
			return new WP_Error('offi_lead_update_failed', 'No se pudo actualizar el lead de checkout.');
		}

		return array(
			'lead_id' => $existing_id,
			'tracking' => $tracking,
			'email_present' => !empty($record['email']),
		);
	}

	$record['created_at'] = $now;

	$inserted = $wpdb->insert(
		$table_name,
		$record,
		array(
			'%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s',
			'%s', '%s', '%s', '%s', '%s', '%s', '%f', '%s', '%s', '%d', '%s', '%s', '%s', '%s'
		)
	);

	if (!$inserted) {
		return new WP_Error('offi_lead_insert_failed', 'No se pudo guardar el lead de checkout.');
	}

	return array(
		'lead_id' => (int) $wpdb->insert_id,
		'tracking' => $tracking,
		'email_present' => !empty($record['email']),
	);
}

/**
 * Normaliza telefono conservando solo digitos y '+' inicial opcional.
 *
 * @param string $phone Telefono bruto.
 * @return string
 */
function offitravel_checkout_normalize_phone($phone)
{
	$phone = trim((string) $phone);
	if ('' === $phone) {
		return '';
	}

	$phone = preg_replace('/[^0-9+]/', '', $phone);
	if (!is_string($phone)) {
		return '';
	}

	$has_plus = strpos($phone, '+') === 0;
	$digits_only = preg_replace('/\D+/', '', $phone);
	if (!is_string($digits_only)) {
		return '';
	}

	return $has_plus ? ('+' . $digits_only) : $digits_only;
}

/**
 * Valida campos minimos del step 1 antes de persistir.
 *
 * @param array $fields Campos sanitizados.
 * @return array{is_valid:bool,errors:array,fields:array}
 */
function offitravel_checkout_validate_step1_fields($fields)
{
	$fields = is_array($fields) ? $fields : array();
	$errors = array();

	$required_fields = array(
		'billing_first_name' => __('Nombre', 'tripgo-child'),
		'billing_last_name' => __('Apellidos', 'tripgo-child'),
		'billing_country' => __('Pais/Region', 'tripgo-child'),
		'billing_address_1' => __('Direccion', 'tripgo-child'),
		'billing_postcode' => __('Codigo postal', 'tripgo-child'),
		'billing_city' => __('Ciudad', 'tripgo-child'),
		'billing_state' => __('Provincia', 'tripgo-child'),
		'billing_phone' => __('Telefono', 'tripgo-child'),
		'billing_email' => __('Correo electronico', 'tripgo-child'),
	);

	foreach ($required_fields as $key => $label) {
		$value = isset($fields[$key]) ? trim((string) $fields[$key]) : '';
		if ('' === $value) {
			$errors[$key] = sprintf(__('El campo %s es obligatorio.', 'tripgo-child'), $label);
		}
	}

	if (empty($errors['billing_email'])) {
		$email = isset($fields['billing_email']) ? strtolower(trim((string) $fields['billing_email'])) : '';
		if ('' === $email || !is_email($email)) {
			$errors['billing_email'] = __('El correo electronico no es valido.', 'tripgo-child');
		} else {
			$fields['billing_email'] = $email;
		}
	}

	if (empty($errors['billing_phone'])) {
		$raw_phone = isset($fields['billing_phone']) ? trim((string) $fields['billing_phone']) : '';
		$phone_digits = preg_replace('/\D+/', '', $raw_phone);
		if (!is_string($phone_digits) || strlen($phone_digits) < 6 || strlen($phone_digits) > 15) {
			$errors['billing_phone'] = __('El telefono no es valido.', 'tripgo-child');
		} else {
			$fields['billing_phone'] = offitravel_checkout_normalize_phone($raw_phone);
		}
	}

	return array(
		'is_valid' => empty($errors),
		'errors' => $errors,
		'fields' => $fields,
	);
}

/**
 * AJAX: guarda step 1 al pasar a step 2.
 */
function offitravel_ajax_save_checkout_step()
{
	check_ajax_referer('offi_checkout_step', 'nonce');

	$raw_fields = array(
		'billing_first_name' => isset($_POST['billing_first_name']) ? sanitize_text_field(wp_unslash($_POST['billing_first_name'])) : '',
		'billing_last_name' => isset($_POST['billing_last_name']) ? sanitize_text_field(wp_unslash($_POST['billing_last_name'])) : '',
		'billing_company' => isset($_POST['billing_company']) ? sanitize_text_field(wp_unslash($_POST['billing_company'])) : '',
		'billing_country' => isset($_POST['billing_country']) ? sanitize_text_field(wp_unslash($_POST['billing_country'])) : '',
		'billing_address_1' => isset($_POST['billing_address_1']) ? sanitize_text_field(wp_unslash($_POST['billing_address_1'])) : '',
		'billing_address_2' => isset($_POST['billing_address_2']) ? sanitize_text_field(wp_unslash($_POST['billing_address_2'])) : '',
		'billing_postcode' => isset($_POST['billing_postcode']) ? sanitize_text_field(wp_unslash($_POST['billing_postcode'])) : '',
		'billing_city' => isset($_POST['billing_city']) ? sanitize_text_field(wp_unslash($_POST['billing_city'])) : '',
		'billing_state' => isset($_POST['billing_state']) ? sanitize_text_field(wp_unslash($_POST['billing_state'])) : '',
		'billing_phone' => isset($_POST['billing_phone']) ? sanitize_text_field(wp_unslash($_POST['billing_phone'])) : '',
		'billing_email' => isset($_POST['billing_email']) ? sanitize_email(wp_unslash($_POST['billing_email'])) : '',
		'order_comments' => isset($_POST['order_comments']) ? sanitize_textarea_field(wp_unslash($_POST['order_comments'])) : '',
	);

	$validation = offitravel_checkout_validate_step1_fields($raw_fields);
	if (empty($validation['is_valid'])) {
		wp_send_json_error(
			array(
				'message' => __('Fallo la validacion del paso 1.', 'tripgo-child'),
				'errors' => isset($validation['errors']) && is_array($validation['errors']) ? $validation['errors'] : array(),
			),
			422
		);
	}

	$raw_fields = isset($validation['fields']) && is_array($validation['fields']) ? $validation['fields'] : $raw_fields;

	$result = offitravel_checkout_upsert_step_lead($raw_fields);

	if (is_wp_error($result)) {
		wp_send_json_error(
			array(
				'message' => $result->get_error_message(),
			),
			500
		);
	}

	$tracking = isset($result['tracking']) && is_array($result['tracking']) ? $result['tracking'] : array();

	wp_send_json_success(
		array(
			'event_name' => 'CheckoutStep1Completed',
			'content_name' => isset($tracking['content_name']) ? (string) $tracking['content_name'] : '',
			'content_ids' => isset($tracking['content_ids']) ? array_values((array) $tracking['content_ids']) : array(),
			'contents' => isset($tracking['contents']) ? array_values((array) $tracking['contents']) : array(),
			'content_type' => isset($tracking['content_type']) ? (string) $tracking['content_type'] : 'product',
			'num_items' => isset($tracking['num_items']) ? (int) $tracking['num_items'] : 0,
			'value' => isset($tracking['value']) ? round((float) $tracking['value'], 2) : 0,
			'currency' => isset($tracking['currency']) ? (string) $tracking['currency'] : '',
			'email_present' => !empty($result['email_present']),
			'lead_id' => isset($result['lead_id']) ? (int) $result['lead_id'] : 0,
		)
	);
}
add_action('wp_ajax_offi_save_checkout_step', 'offitravel_ajax_save_checkout_step');
add_action('wp_ajax_nopriv_offi_save_checkout_step', 'offitravel_ajax_save_checkout_step');

/**
 * Intenta enlazar un pedido confirmado con lead previo.
 *
 * @param WC_Order $order Pedido.
 * @return int ID de lead actualizado, 0 si no hubo match.
 */
function offitravel_checkout_link_order_to_lead($order)
{
	if (!function_exists('wc_get_order')) {
		return 0;
	}

	if (!is_object($order) || !method_exists($order, 'get_id')) {
		return 0;
	}

	$order_id = (int) $order->get_id();
	if ($order_id <= 0) {
		return 0;
	}

	global $wpdb;
	$table_name = offitravel_checkout_leads_table_name();
	$now = current_time('mysql');
	$email = sanitize_email((string) $order->get_billing_email());
	$user_id = (int) $order->get_user_id();

	$lead_id = 0;

	if (!empty($email)) {
		$lead_id = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$table_name} WHERE order_id IS NULL AND email = %s ORDER BY id DESC LIMIT 1",
				$email
			)
		);
	}

	if ($lead_id <= 0 && $user_id > 0) {
		$lead_id = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$table_name} WHERE order_id IS NULL AND user_id = %d ORDER BY id DESC LIMIT 1",
				$user_id
			)
		);
	}

	if ($lead_id <= 0) {
		return 0;
	}

	$updated = $wpdb->update(
		$table_name,
		array(
			'order_id' => $order_id,
			'step' => 'order_placed',
			'updated_at' => $now,
		),
		array('id' => $lead_id),
		array('%d', '%s', '%s'),
		array('%d')
	);

	if (false === $updated) {
		return 0;
	}

	return $lead_id;
}
