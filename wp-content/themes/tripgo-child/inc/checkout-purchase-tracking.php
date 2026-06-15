<?php
/**
 * OFFITRAVEL - Purchase tracking (thank you page).
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Renderiza payload de Purchase en thank you page.
 *
 * Por defecto NO dispara Purchase real para evitar duplicados con el plugin oficial Meta.
 * Para habilitar envio real:
 * define('OFFITRAVEL_ENABLE_CUSTOM_PURCHASE_PIXEL', true);
 *
 * @param int $order_id ID de pedido.
 */
function offitravel_render_purchase_tracking_payload($order_id)
{
	if (empty($order_id) || !function_exists('wc_get_order')) {
		return;
	}

	$order = wc_get_order($order_id);
	if (!$order) {
		return;
	}

	$content_ids = array();
	$content_names = array();
	$contents = array();

	foreach ($order->get_items() as $item) {
		if (!is_object($item) || !method_exists($item, 'get_product_id')) {
			continue;
		}

		$product_id = (int) $item->get_product_id();
		$quantity = max(1, (int) $item->get_quantity());
		$line_total = (float) $item->get_total();
		$item_price = $quantity > 0 ? round($line_total / $quantity, 2) : 0.0;

		$content_ids[] = $product_id;
		$content_names[] = (string) $item->get_name();
		$contents[] = array(
			'id' => $product_id,
			'quantity' => $quantity,
			'item_price' => $item_price,
		);
	}

	$content_ids = array_values(array_unique(array_filter($content_ids)));
	$content_names = array_values(array_unique(array_filter($content_names)));

	$num_items = 0;
	foreach ($order->get_items() as $item) {
		$num_items += max(1, (int) $item->get_quantity());
	}

	$payload = array(
		'order_id' => (int) $order->get_id(),
		'content_name' => implode(', ', $content_names),
		'content_ids' => $content_ids,
		'contents' => array_values($contents),
		'content_type' => 'product',
		'num_items' => max(0, $num_items),
		'value' => round((float) $order->get_total(), 2),
		'currency' => (string) $order->get_currency(),
	);

	if (function_exists('offitravel_checkout_link_order_to_lead')) {
		offitravel_checkout_link_order_to_lead($order);
	}

	$enabled = defined('OFFITRAVEL_ENABLE_CUSTOM_PURCHASE_PIXEL') && OFFITRAVEL_ENABLE_CUSTOM_PURCHASE_PIXEL;
	$enabled = (bool) apply_filters('offitravel_enable_custom_purchase_pixel', $enabled, $order, $payload);

	$payload_json = wp_json_encode($payload);
	$storage_key = 'offi_purchase_tracked_' . (int) $order->get_id();

	echo '<script>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '(function () {'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo 'var payload = ' . $payload_json . ';'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo 'var storageKey = ' . wp_json_encode($storage_key) . ';'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo 'var metaPayload = {'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo 'content_name: payload.content_name,'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo 'content_ids: payload.content_ids,'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo 'contents: payload.contents,'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo 'content_type: payload.content_type,'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo 'num_items: payload.num_items,'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo 'value: payload.value,'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo 'currency: payload.currency'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '};'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	if ($enabled) {
		echo 'if (window.localStorage && localStorage.getItem(storageKey)) { return; }'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo 'if (typeof window.fbq === "function") {'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo 'window.fbq("track", "Purchase", metaPayload);'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo 'if (window.localStorage) { localStorage.setItem(storageKey, "1"); }'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '} else if (window.console && typeof window.console.log === "function") {'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo 'console.log("Purchase meta payload prepared (fbq missing):", metaPayload);'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '}'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	} else {
		echo 'if (window.console && typeof window.console.log === "function") {'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo 'console.log("Purchase meta payload preview:", metaPayload);'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '}'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	echo '})();'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo '</script>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
add_action('woocommerce_thankyou', 'offitravel_render_purchase_tracking_payload', 20, 1);
