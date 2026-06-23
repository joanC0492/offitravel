<?php
/**
 * OFFITRAVEL - Ajuste visual de importes en la pagina order-received.
 */

defined('ABSPATH') || exit;

/**
 * Limita los filtros al render de la tabla en la pagina de gracias.
 * Asi no se modifican el pedido, los emails ni el payload de Purchase.
 */
function offitravel_order_received_begin_price_cleanup($order)
{
	if (!function_exists('is_order_received_page') || !is_order_received_page()) {
		return;
	}

	add_filter(
		'woocommerce_order_item_get_formatted_meta_data',
		'offitravel_order_received_hide_monetary_item_meta',
		20,
		2
	);
	add_filter(
		'woocommerce_get_order_item_totals',
		'offitravel_order_received_keep_final_total',
		20,
		3
	);
}
add_action(
	'woocommerce_order_details_before_order_table',
	'offitravel_order_received_begin_price_cleanup',
	5
);

/**
 * Retira del detalle solo metas cuyo valor completo es un importe monetario.
 * Fechas, ocupacion, adultos, paquete y demas datos descriptivos permanecen.
 */
function offitravel_order_received_hide_monetary_item_meta($formatted_meta, $item)
{
	$currency_code = function_exists('get_woocommerce_currency')
		? get_woocommerce_currency()
		: '';

	foreach ($formatted_meta as $meta_id => $meta) {
		$value = isset($meta->display_value) ? (string) $meta->display_value : '';
		$value = html_entity_decode(
			wp_strip_all_tags($value),
			ENT_QUOTES,
			get_bloginfo('charset') ?: 'UTF-8'
		);
		$value = trim($value);

		$has_currency_symbol = 1 === preg_match('/\p{Sc}/u', $value);
		$has_currency_code   = '' !== $currency_code
			&& false !== stripos($value, $currency_code);

		if (!$has_currency_symbol && !$has_currency_code) {
			continue;
		}

		$price_characters = preg_replace('/\p{Sc}/u', '', $value);
		if ('' !== $currency_code) {
			$price_characters = preg_replace(
				'/' . preg_quote($currency_code, '/') . '/iu',
				'',
				$price_characters
			);
		}

		if (
			1 === preg_match('/\d/u', $price_characters)
			&& 1 === preg_match('/^[\d\s.,\x{00A0}\x{202F}\'\x{2019}+\-\x{2212}()]+$/u', $price_characters)
		) {
			unset($formatted_meta[$meta_id]);
		}
	}

	return $formatted_meta;
}

/**
 * En el pie deja el total final y el metodo de pago (dato no monetario).
 */
function offitravel_order_received_keep_final_total($total_rows, $order, $tax_display)
{
	$visible_rows = array('order_total', 'payment_method');

	return array_intersect_key($total_rows, array_flip($visible_rows));
}

/**
 * Quita los filtros al terminar la tabla para no afectar el tracking Purchase.
 */
function offitravel_order_received_end_price_cleanup($order)
{
	remove_filter(
		'woocommerce_order_item_get_formatted_meta_data',
		'offitravel_order_received_hide_monetary_item_meta',
		20
	);
	remove_filter(
		'woocommerce_get_order_item_totals',
		'offitravel_order_received_keep_final_total',
		20
	);
}
add_action(
	'woocommerce_order_details_after_order_table',
	'offitravel_order_received_end_price_cleanup',
	5
);
