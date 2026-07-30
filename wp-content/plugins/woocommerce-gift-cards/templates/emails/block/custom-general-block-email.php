<?php
/**
 * Custom General block email template for Gift Cards
 *
 * Used to render information for the email editor WooCommerce content block (BlockEmailRenderer::WOO_EMAIL_CONTENT_PLACEHOLDER).
 *
 * We are using this custom template instead of the core version because gift card email does not require the woocommerce_email_order_details, woocommerce_email_order_meta and woocommerce_email_customer_details hooks.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce Gift Cards
 * @version 2.7.2
 */

defined( 'ABSPATH' ) || exit;

/**
 * Information note about this file.
 * This file is used to render the gift card email content in the WooCommerce email editor.
 * It handles the dynamic part of the email block content.
 * More customization can be done here to improve the look and feel of the email.
 * Please ensure to only use email compatible HTML and CSS.
 *
 * For the initial block email editor integration (STRPROD-685), we are keeping it simple by reusing the same old template "do_action:woocommerce_email_gift_card_html"
 *
 * Next steps:
 * - Use personalization tags to display placeholder or variable content.
 * - Remove the woocommerce_email_gift_card_html do_action and relocate the text string to `templates/emails/block/gift-card-received.php`.
 * - Use this template only for dynamic or logical conditional content.
 */

/**
 * Show gift card specific HTML.
 *
 * @hook   woocommerce_email_gift_card_html
 * @hooked WC_GC_Emails::gift_card_email_html()
 * @since  2.7.2
 *
 * @param  string           $intro_content  The intro content of the email.
 * @param  WC_Email         $email          The email object.
 * @param  WC_GC_Gift_Card  $giftcard       The gift card object.
 *
 * @return void
 */
do_action( 'woocommerce_email_gift_card_html', $giftcard, $intro_content, $email );
