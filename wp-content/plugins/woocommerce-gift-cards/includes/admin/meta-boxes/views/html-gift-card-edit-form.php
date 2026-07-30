<?php
/**
 * Admin edit-Gift Card meta box html
 *
 * @since   1.3.0
 * @version 1.6.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?><div class="woocommerce_gc_giftcard_form">
<?php

	/**
	 * Hook: woocommerce_gc_form_fields_html.
	 *
	 * @hooked wc_gc_form_field_recipient_html - 10
	 * @hooked wc_gc_form_field_sender_html - 20
	 * @hooked wc_gc_form_field_message_html - 30
	 * @hooked wc_gc_form_field_delivery_html - 40
	 * @hooked WC_GC_Meta_Box_Order::admin_form_field_code_html - 50
	 */
	do_action( 'woocommerce_gc_form_fields_html', $product );

?>
</div>
