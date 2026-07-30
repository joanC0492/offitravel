<?php
/**
 * Gift Cards
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/plain/gift-card-received.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce Gift Cards
 * @version 2.5.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n";
echo esc_html( wp_strip_all_tags( $email_heading ) );
echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

/* translators: %s: Sender name */
echo esc_html( wp_strip_all_tags( wptexturize( apply_filters( 'woocommerce_email_intro_content', $intro_content, $giftcard ) ) ) );
echo "\n\n";
/* translators: %s: Gifcard amount */
echo sprintf( esc_html_x( 'Amount %s', 'Email gift card received', 'woocommerce-gift-cards' ), wp_kses_post( wc_price( $giftcard->get_balance() ) ) ) . "\n\n";
/* translators: %s: Gifcard code */
printf( esc_html_x( 'Code: %s', 'Email gift card received', 'woocommerce-gift-cards' ), esc_html( $giftcard->get_code() ) );

if ( $giftcard->get_expire_date() > 0 ) {
	/* translators: %s: Gift card expiration date */
	echo "\n\n" . sprintf( esc_html_x( 'Expires on %s', 'Email gift card received', 'woocommerce-gift-cards' ), esc_html( date_i18n( get_option( 'date_format' ), $giftcard->get_expire_date() ) ) );
}

echo "\n\n----------------------------------------\n\n";

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( $additional_content ) {
	echo esc_html( wp_strip_all_tags( wptexturize( $additional_content ) ) );
	echo "\n\n----------------------------------------\n\n";
}

echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
