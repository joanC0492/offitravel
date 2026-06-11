<?php
/**
 * Plugin Name: Offitravel – OVA BRW checkout directo
 * Description: Al reservar un tour OVA BRW, vacía el carrito, deja solo ese producto y redirige directamente al checkout.
 * Version: 1.0.0
 * Author: Offitravel
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comprueba si un producto es tour OVA BRW.
 *
 * @param int $product_id ID de producto.
 * @return bool
 */
function offitravel_ovabrw_is_rental_product( $product_id ) {
	$product_id = absint( $product_id );
	if ( ! $product_id ) {
		return false;
	}

	$product = wc_get_product( $product_id );
	return ( $product && $product->is_type( 'ovabrw_car_rental' ) );
}

/**
 * Antes de añadir al carrito: compra exclusiva de un solo tour OVA BRW.
 */
add_filter( 'woocommerce_add_to_cart_validation', 'offitravel_ovabrw_exclusive_add_to_cart', 20, 2 );
function offitravel_ovabrw_exclusive_add_to_cart( $passed, $product_id ) {
	if ( ! $passed ) {
		return $passed;
	}

	if ( ! offitravel_ovabrw_is_rental_product( $product_id ) ) {
		return $passed;
	}

	if ( function_exists( 'WC' ) && WC()->cart ) {
		WC()->cart->empty_cart();
	}

	return $passed;
}

/**
 * Tras añadir al carrito un tour OVA BRW: ir directo al checkout.
 */
add_filter( 'woocommerce_add_to_cart_redirect', 'offitravel_ovabrw_redirect_to_checkout_after_add', 20 );
function offitravel_ovabrw_redirect_to_checkout_after_add( $url ) {
	$product_id = 0;
	if ( isset( $_REQUEST['add-to-cart'] ) ) {
		$product_id = absint( wp_unslash( $_REQUEST['add-to-cart'] ) );
	}

	if ( $product_id && offitravel_ovabrw_is_rental_product( $product_id ) ) {
		return wc_get_checkout_url();
	}

	return $url;
}
