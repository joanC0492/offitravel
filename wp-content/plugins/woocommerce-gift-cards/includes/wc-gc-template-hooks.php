<?php
/**
 * Template Hooks
 *
 * @package  WooCommerce Gift Cards
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'woocommerce_gc_form_fields_html', 'wc_gc_form_field_recipient_html', 10 );
add_action( 'woocommerce_gc_form_fields_html', 'wc_gc_form_field_cc_html', 15 );
add_action( 'woocommerce_gc_form_fields_html', 'wc_gc_form_field_sender_html', 20 );
add_action( 'woocommerce_gc_form_fields_html', 'wc_gc_form_field_message_html', 30 );
add_action( 'woocommerce_gc_form_fields_html', 'wc_gc_form_field_delivery_html', 40 );
