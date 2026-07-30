<?php
/**
 * Gift Card Form
 *
 * Shows the additional form fields on the product page.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/gift-card-form.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce Gift Cards
 * @version 1.6.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_gc_before_form', $product );

?><div class="woocommerce_gc_giftcard_form">
<?php

	/**
	 * Hook: woocommerce_gc_form_fields_html.
	 *
	 * @hooked wc_gc_form_field_recipient_html - 10
	 * @hooked wc_gc_form_field_sender_html - 20
	 * @hooked wc_gc_form_field_message_html - 30
	 * @hooked wc_gc_form_field_delivery_html - 40
	 */
	do_action( 'woocommerce_gc_form_fields_html', $product );

?>
</div>
<?php

do_action( 'woocommerce_gc_after_form', $product );
