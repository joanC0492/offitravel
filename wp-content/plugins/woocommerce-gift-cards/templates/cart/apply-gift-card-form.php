<?php
/**
 * Gift Cards form in the Cart/Checkout
 *
 * Shows an HTML form in the cart/checkout page.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/apply-gift-card-form.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce Gift Cards
 * @since   1.3.5
 * @version 1.16.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_gc_before_apply_gift_card_form' );

?><div class="add_gift_card_form">
	<h4><?php esc_html_e( 'Have a gift card?', 'woocommerce-gift-cards' ); ?></h4>
	<div id="wc_gc_cart_redeem_form">
		<div class="wc_gc_add_gift_card_form__notices"></div>
		<input placeholder="<?php esc_attr_e( 'Enter your code&hellip;', 'woocommerce-gift-cards' ); ?>" type="text" name="wc_gc_cart_code" id="wc_gc_cart_code" class="input-text" autocomplete="off" />
		<button type="button" name="wc_gc_cart_redeem_send" id="wc_gc_cart_redeem_send" class="<?php echo isset( $button_class ) ? esc_attr( $button_class ) : 'button'; ?>"><?php esc_html_e( 'Apply', 'woocommerce-gift-cards' ); ?></button>
	</div>
</div><?php

do_action( 'woocommerce_gc_after_apply_gift_card_form' );
?>
