<?php
/**
 * Gift Cards - Send as Gift checkbox.
 *
 * Displays a "Send as Gift?" checkbox before the Gift Cards form.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/gift-card-send-as-gift-checkbox.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @version 1.9.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="wc_gc_send_as_gift_wrapper" style="padding-bottom: 1em;" >
	<label for="wc_gc_send_as_gift_checkbox"><input type="checkbox" name="wc_gc_send_as_gift_checkbox" id="wc_gc_send_as_gift_checkbox"<?php echo $is_checked ? ' checked' : ''; ?>> <?php esc_html_e( 'Send as gift?', 'woocommerce-gift-cards' ); ?></label>
</div>
<?php
