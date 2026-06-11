<?php
/**
 * Cabecera de ficha (Desde + precio + línea con avión) y botones de contacto.
 *
 * @var int   $product_id
 * @var array $product_prices Tripgo: regular_price, sale_price.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $product_prices ) || ! is_array( $product_prices ) ) {
	$product_prices = array(
		'regular_price' => '',
		'sale_price'    => '',
	);
}

$c        = function_exists( 'offitravel_ovabrw_get_contact_meta' ) ? offitravel_ovabrw_get_contact_meta( $product_id ) : array( 'phone' => '', 'email' => '', 'whatsapp' => '' );
$phone    = $c['phone'];
$email    = $c['email'];
$whatsapp = $c['whatsapp'];

$tel_href = '';
if ( $phone !== '' ) {
	$compact = preg_replace( '/[^\d+]/', '', $phone );
	if ( $compact !== '' ) {
		$tel_href = 'tel:' . $compact;
	}
}

$mailto = '';
if ( $email !== '' && is_email( $email ) ) {
	$mailto = 'mailto:' . $email;
}

$wa_href = '';
if ( $whatsapp !== '' && function_exists( 'offitravel_ovabrw_whatsapp_href' ) ) {
	$wa_href = offitravel_ovabrw_whatsapp_href( $whatsapp );
}

$icon_phone = '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z" fill="currentColor"/></svg>';
$icon_wa    = '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" fill="currentColor"/></svg>';
$icon_mail  = '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z" fill="currentColor"/></svg>';
?>
<div class="offitravel-contact-widget offitravel-contact-widget--with-header">
	<div class="price-product">
		<div class="label">
			<i aria-hidden="true" class="icomoon icomoon-tag"></i>
			<span><?php esc_html_e( 'Desde', 'tripgo' ); ?></span>
		</div>
		<div class="price">
			<span class="regular-price">
				<?php echo wp_kses_post( wc_price( $product_prices['regular_price'] ) ); ?>
			</span>
			<?php if ( ! empty( $product_prices['sale_price'] ) ) : ?>
				<span class="sale-price">
					<?php echo wp_kses_post( wc_price( $product_prices['sale_price'] ) ); ?>
				</span>
			<?php endif; ?>
		</div>
	</div>
	<div class="logo">
		<span class="line"></span>
		<i aria-hidden="true" class="<?php echo esc_attr( apply_filters( 'ovabrw_booking_form_icon', 'icomoon icomoon-flig-outline' ) ); ?>"></i>
	</div>
	<div class="offitravel-contact-buttons">
		<?php if ( $phone !== '' && $tel_href !== '' ) : ?>
			<a class="offitravel-contact-btn offitravel-contact-btn--phone" href="<?php echo esc_url( $tel_href ); ?>">
				<span class="offitravel-contact-btn__icon" aria-hidden="true"><?php echo $icon_phone; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span class="offitravel-contact-btn__label"><?php esc_html_e( 'Llamar', 'tripgo-child' ); ?></span>
			</a>
		<?php endif; ?>
		<?php if ( $whatsapp !== '' && $wa_href !== '' ) : ?>
			<a class="offitravel-contact-btn offitravel-contact-btn--whatsapp" href="<?php echo esc_url( $wa_href ); ?>" target="_blank" rel="noopener noreferrer">
				<span class="offitravel-contact-btn__icon" aria-hidden="true"><?php echo $icon_wa; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span class="offitravel-contact-btn__label"><?php esc_html_e( 'WhatsApp', 'tripgo-child' ); ?></span>
			</a>
		<?php endif; ?>
		<?php if ( $email !== '' && $mailto !== '' ) : ?>
			<a class="offitravel-contact-btn offitravel-contact-btn--email" href="<?php echo esc_url( $mailto ); ?>">
				<span class="offitravel-contact-btn__icon" aria-hidden="true"><?php echo $icon_mail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span class="offitravel-contact-btn__label"><?php esc_html_e( 'Correo', 'tripgo-child' ); ?></span>
			</a>
		<?php endif; ?>
	</div>
</div>
