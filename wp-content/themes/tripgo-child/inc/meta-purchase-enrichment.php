<?php
/**
 * OFFITRAVEL - Enriquecimiento del Purchase oficial de Meta.
 *
 * Reemplaza solo el callback Purchase oficial por un callback espejo que
 * reutiliza los metodos publicos del plugin y enriquece el mismo evento.
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Activa logs solo en depuracion explicita.
 *
 * @return bool
 */
function offitravel_meta_purchase_enrichment_debug_enabled()
{
	return (defined('WP_DEBUG') && WP_DEBUG)
		|| (defined('OFFITRAVEL_META_PURCHASE_ENRICHMENT_DEBUG') && OFFITRAVEL_META_PURCHASE_ENRICHMENT_DEBUG);
}

/**
 * Log seguro: no incluye datos personales del comprador.
 *
 * @param string $message Mensaje.
 * @param array  $context Contexto no sensible.
 */
function offitravel_meta_purchase_enrichment_log($message, $context = array())
{
	if (!offitravel_meta_purchase_enrichment_debug_enabled()) {
		return;
	}

	if (!is_array($context)) {
		$context = array();
	}

	$allowed_context = array_intersect_key(
		$context,
		array(
			'order_id' => true,
			'event_id' => true,
			'hook' => true,
			'reason' => true,
			'has_content_name' => true,
			'has_order_id' => true,
			'priority' => true,
		)
	);

	error_log('[OFFI Meta Purchase Enrichment] ' . $message . ' ' . wp_json_encode($allowed_context));
}

/**
 * Clases y callables publicos del plugin oficial que reutilizamos.
 *
 * @return array<string,mixed>
 */
function offitravel_meta_purchase_enrichment_plugin_refs()
{
	$woocommerce_class = 'FacebookPixelPlugin\\Integration\\FacebookWordpressWooCommerce';
	$factory_class = 'FacebookPixelPlugin\\Core\\ServerEventFactory';
	$server_event_class = 'FacebookPixelPlugin\\Core\\FacebookServerSideEvent';
	$utils_class = 'FacebookPixelPlugin\\Core\\FacebookPluginUtils';

	return array(
		'woocommerce_class' => $woocommerce_class,
		'factory_class' => $factory_class,
		'server_event_class' => $server_event_class,
		'utils_class' => $utils_class,
		'official_purchase_callable' => array($woocommerce_class, 'trackPurchaseEvent'),
		'create_purchase_callable' => array($woocommerce_class, 'createPurchaseEvent'),
		'enqueue_pixel_callable' => array($woocommerce_class, 'enqueuePixelCode'),
		'safe_create_event_callable' => array($factory_class, 'safe_create_event'),
		'server_event_instance_callable' => array($server_event_class, 'get_instance'),
		'is_internal_user_callable' => array($utils_class, 'is_internal_user'),
	);
}

/**
 * Confirma que los metodos publicos necesarios existen antes de tocar hooks.
 *
 * @param array<string,mixed> $refs Referencias del plugin.
 * @return bool
 */
function offitravel_meta_purchase_enrichment_plugin_api_available($refs)
{
	$class_keys = array(
		'woocommerce_class',
		'factory_class',
		'server_event_class',
		'utils_class',
	);

	foreach ($class_keys as $key) {
		if (empty($refs[$key]) || !class_exists($refs[$key])) {
			offitravel_meta_purchase_enrichment_log('Plugin API unavailable', array(
				'reason' => $key,
			));
			return false;
		}
	}

	$callable_keys = array(
		'official_purchase_callable' => array($refs['woocommerce_class'], 'trackPurchaseEvent'),
		'create_purchase_callable' => array($refs['woocommerce_class'], 'createPurchaseEvent'),
		'enqueue_pixel_callable' => array($refs['woocommerce_class'], 'enqueuePixelCode'),
		'safe_create_event_callable' => array($refs['factory_class'], 'safe_create_event'),
		'server_event_instance_callable' => array($refs['server_event_class'], 'get_instance'),
		'is_internal_user_callable' => array($refs['utils_class'], 'is_internal_user'),
	);

	foreach ($callable_keys as $key => $expected_callable) {
		if (
			empty($refs[$key]) ||
			!is_array($expected_callable) ||
			!method_exists($expected_callable[0], $expected_callable[1]) ||
			!is_callable($refs[$key])
		) {
			offitravel_meta_purchase_enrichment_log('Plugin callable unavailable', array(
				'reason' => $key,
			));
			return false;
		}
	}

	return true;
}

/**
 * Reemplaza el callback oficial Purchase solo si ambos remove_action funcionan.
 */
function offitravel_meta_purchase_enrichment_replace_official_hooks()
{
	static $installed = false;

	if ($installed) {
		return;
	}

	$refs = offitravel_meta_purchase_enrichment_plugin_refs();
	if (!offitravel_meta_purchase_enrichment_plugin_api_available($refs)) {
		return;
	}

	$hooks = array(
		'woocommerce_thankyou',
		'woocommerce_payment_complete',
	);
	$priority = 40;
	$official_callable = $refs['official_purchase_callable'];
	$replacement_callable = 'offitravel_meta_purchase_enrichment_track_purchase_event';

	foreach ($hooks as $hook) {
		$registered_priority = has_action($hook, $official_callable);
		if ((int) $registered_priority !== $priority) {
			offitravel_meta_purchase_enrichment_log('Official Purchase callback not replaced', array(
				'hook' => $hook,
				'priority' => false === $registered_priority ? -1 : (int) $registered_priority,
				'reason' => 'official_callback_not_found_at_expected_priority',
			));
			return;
		}
	}

	$removed_hooks = array();
	foreach ($hooks as $hook) {
		$removed = remove_action($hook, $official_callable, $priority);
		if (!$removed) {
			foreach ($removed_hooks as $removed_hook) {
				add_action($removed_hook, $official_callable, $priority, 1);
			}

			offitravel_meta_purchase_enrichment_log('Official Purchase callback not replaced', array(
				'hook' => $hook,
				'reason' => 'remove_action_failed',
			));
			return;
		}

		$removed_hooks[] = $hook;
	}

	foreach ($hooks as $hook) {
		add_action($hook, $replacement_callable, $priority, 1);
	}

	$installed = true;
	offitravel_meta_purchase_enrichment_log('Official Purchase callback replaced');
}
add_action('init', 'offitravel_meta_purchase_enrichment_replace_official_hooks', 20);

/**
 * Construye los datos adicionales desde el WC_Order real.
 *
 * @param WC_Order $order Pedido.
 * @return array{order_id:string,content_name:string}
 */
function offitravel_meta_purchase_enrichment_get_order_data($order)
{
	$content_names = array();

	if (is_object($order) && method_exists($order, 'get_items')) {
		foreach ($order->get_items() as $item) {
			if (!is_object($item) || !method_exists($item, 'get_name')) {
				continue;
			}

			$name = trim((string) $item->get_name());
			if ('' !== $name) {
				$content_names[] = $name;
			}
		}
	}

	$content_names = array_values(array_unique($content_names));

	return array(
		'order_id' => (string) $order->get_id(),
		'content_name' => implode(', ', $content_names),
	);
}

/**
 * Anade order_id y content_name al CustomData del evento oficial.
 *
 * @param object $server_event Evento creado por el plugin oficial.
 * @param array  $order_data Datos adicionales.
 * @return bool
 */
function offitravel_meta_purchase_enrichment_apply_custom_data($server_event, $order_data)
{
	if (!is_object($server_event) || !method_exists($server_event, 'getCustomData')) {
		return false;
	}

	$custom_data = $server_event->getCustomData();
	if (!is_object($custom_data)) {
		return false;
	}

	if (!empty($order_data['order_id']) && method_exists($custom_data, 'setOrderId')) {
		$custom_data->setOrderId($order_data['order_id']);
	}

	if (!empty($order_data['content_name']) && method_exists($custom_data, 'setContentName')) {
		$custom_data->setContentName($order_data['content_name']);
	}

	return true;
}

/**
 * Normaliza un arreglo para salida JSON segura.
 *
 * @param mixed $value Valor.
 * @return array
 */
function offitravel_meta_purchase_enrichment_array_values($value)
{
	if (!is_array($value)) {
		return array();
	}

	return array_values($value);
}

/**
 * Construye un payload sanitizado para consola desde el mismo server_event.
 *
 * @param object $server_event Evento oficial enriquecido.
 * @return array
 */
function offitravel_meta_purchase_enrichment_get_purchase_console_payload($server_event)
{
	$event_id = is_object($server_event) && method_exists($server_event, 'getEventId')
		? (string) $server_event->getEventId()
		: '';

	$custom_data_payload = array();
	if (is_object($server_event) && method_exists($server_event, 'getCustomData')) {
		$custom_data = $server_event->getCustomData();
		if (is_object($custom_data) && method_exists($custom_data, 'normalize')) {
			$normalized = $custom_data->normalize();
			if (is_array($normalized)) {
				$custom_data_payload = $normalized;
			}
		}
	}

	return array(
		'event_name' => 'Purchase',
		'event_type' => 'track',
		'event_id' => $event_id,
		'order_id' => isset($custom_data_payload['order_id']) ? (string) $custom_data_payload['order_id'] : '',
		'content_name' => isset($custom_data_payload['content_name']) ? (string) $custom_data_payload['content_name'] : '',
		'content_ids' => offitravel_meta_purchase_enrichment_array_values($custom_data_payload['content_ids'] ?? array()),
		'content_type' => isset($custom_data_payload['content_type']) ? (string) $custom_data_payload['content_type'] : '',
		'contents' => offitravel_meta_purchase_enrichment_array_values($custom_data_payload['contents'] ?? array()),
		'value' => $custom_data_payload['value'] ?? null,
		'currency' => isset($custom_data_payload['currency']) ? (string) $custom_data_payload['currency'] : '',
	);
}

/**
 * Guarda durante esta peticion el payload seguro que se mostrara en consola.
 *
 * @param object $server_event Evento oficial enriquecido.
 */
function offitravel_meta_purchase_enrichment_store_purchase_console_payload($server_event)
{
	if (isset($GLOBALS['offitravel_meta_purchase_console_payload'])) {
		return;
	}

	$GLOBALS['offitravel_meta_purchase_console_payload'] =
		offitravel_meta_purchase_enrichment_get_purchase_console_payload($server_event);
}

/**
 * Imprime un log seguro del Purchase en la pagina de pedido recibido.
 */
function offitravel_meta_purchase_enrichment_render_purchase_console_log()
{
	static $printed = false;

	if ($printed) {
		return;
	}

	if (!function_exists('is_order_received_page') || !is_order_received_page()) {
		return;
	}

	if (empty($GLOBALS['offitravel_meta_purchase_console_payload']) || !is_array($GLOBALS['offitravel_meta_purchase_console_payload'])) {
		return;
	}

	$printed = true;
	$payload_json = wp_json_encode(
		$GLOBALS['offitravel_meta_purchase_console_payload'],
		JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
	);

	if (!$payload_json) {
		return;
	}
	?>
	<script id="offitravel-meta-purchase-console-log">
		(function () {
			var payload = <?php echo $payload_json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>;
			var label = "[OFFI Meta Tracking] Purchase enviado";

			if (window.console && typeof window.console.groupCollapsed === "function" && typeof window.console.groupEnd === "function") {
				window.console.groupCollapsed(label);
				window.console.log(payload);
				window.console.groupEnd();
				return;
			}

			if (window.console && typeof window.console.log === "function") {
				window.console.log(label, payload);
			}
		})();
	</script>
	<?php
}
add_action('wp_footer', 'offitravel_meta_purchase_enrichment_render_purchase_console_log', 30);

/**
 * Callback espejo del Purchase oficial de Meta.
 *
 * @param int $order_id ID de pedido.
 */
function offitravel_meta_purchase_enrichment_track_purchase_event($order_id)
{
	$order_id = absint($order_id);
	if ($order_id <= 0 || !function_exists('wc_get_order')) {
		return;
	}

	$order = wc_get_order($order_id);
	if (!is_object($order) || !method_exists($order, 'get_id')) {
		offitravel_meta_purchase_enrichment_log('Purchase skipped', array(
			'order_id' => $order_id,
			'reason' => 'invalid_order',
		));
		return;
	}

	$refs = offitravel_meta_purchase_enrichment_plugin_refs();
	if (!offitravel_meta_purchase_enrichment_plugin_api_available($refs)) {
		return;
	}

	if (call_user_func($refs['is_internal_user_callable'])) {
		return;
	}

	$tracking_name = defined($refs['woocommerce_class'] . '::TRACKING_NAME')
		? constant($refs['woocommerce_class'] . '::TRACKING_NAME')
		: 'woocommerce';

	try {
		$server_event = call_user_func(
			$refs['safe_create_event_callable'],
			'Purchase',
			$refs['create_purchase_callable'],
			array($order_id),
			$tracking_name
		);
	} catch (Throwable $throwable) {
		offitravel_meta_purchase_enrichment_log('Purchase skipped', array(
			'order_id' => $order_id,
			'reason' => 'safe_create_event_failed',
		));
		return;
	}

	if (!is_object($server_event)) {
		offitravel_meta_purchase_enrichment_log('Purchase skipped', array(
			'order_id' => $order_id,
			'reason' => 'invalid_server_event',
		));
		return;
	}

	$order_data = offitravel_meta_purchase_enrichment_get_order_data($order);
	$enriched = offitravel_meta_purchase_enrichment_apply_custom_data($server_event, $order_data);

	if (!$enriched) {
		offitravel_meta_purchase_enrichment_log('Purchase not enriched', array(
			'order_id' => $order_id,
			'reason' => 'custom_data_api_unavailable',
		));
	}

	$server_side_event = call_user_func($refs['server_event_instance_callable']);
	if (!is_object($server_side_event) || !method_exists($server_side_event, 'track')) {
		offitravel_meta_purchase_enrichment_log('Purchase skipped', array(
			'order_id' => $order_id,
			'reason' => 'server_side_track_unavailable',
		));
		return;
	}

	$server_side_event->track($server_event);
	call_user_func($refs['enqueue_pixel_callable'], $server_event);
	offitravel_meta_purchase_enrichment_store_purchase_console_payload($server_event);

	$event_id = is_object($server_event) && method_exists($server_event, 'getEventId')
		? (string) $server_event->getEventId()
		: '';

	offitravel_meta_purchase_enrichment_log('Purchase tracked', array(
		'order_id' => $order_id,
		'event_id' => $event_id,
		'has_order_id' => !empty($order_data['order_id']),
		'has_content_name' => !empty($order_data['content_name']),
	));
}
