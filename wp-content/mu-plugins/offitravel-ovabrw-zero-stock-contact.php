<?php

/**
 * Plugin Name: Offitravel – OVA BRW contacto si cantidad 0
 * Description: Opción de mostrar solo botones de contacto; sin lista blanca, mensaje «salida no disponible».
 * Version: 1.2.1
 * Author: Offitravel
 */

if (! defined('ABSPATH')) {
	exit;
}

define('OFFITRAVEL_OVABRW_CONTACT_META_PHONE', '_offitravel_ovabrw_contact_phone');
define('OFFITRAVEL_OVABRW_CONTACT_META_EMAIL', '_offitravel_ovabrw_contact_email');
define('OFFITRAVEL_OVABRW_CONTACT_META_WHATSAPP', '_offitravel_ovabrw_contact_whatsapp');
/** @deprecated Se elimina al guardar; lectura solo para migración. */
define('OFFITRAVEL_OVABRW_CONTACT_META_FORCE_UNAVAILABLE', '_offitravel_ovabrw_contact_force_unavailable');
define('OFFITRAVEL_OVABRW_CONTACT_META_SHOW_BUTTONS', '_offitravel_ovabrw_contact_show_buttons');

/**
 * @param int $product_id
 * @return array{phone:string,email:string,whatsapp:string}
 */
function offitravel_ovabrw_get_contact_meta($product_id)
{
	$product_id = (int) $product_id;
	return array(
		'phone'    => trim((string) get_post_meta($product_id, OFFITRAVEL_OVABRW_CONTACT_META_PHONE, true)),
		'email'    => trim((string) get_post_meta($product_id, OFFITRAVEL_OVABRW_CONTACT_META_EMAIL, true)),
		'whatsapp' => trim((string) get_post_meta($product_id, OFFITRAVEL_OVABRW_CONTACT_META_WHATSAPP, true)),
	);
}

/**
 * ¿Hay al menos un canal de contacto relleno?
 */
function offitravel_ovabrw_contact_meta_has_any($product_id)
{
	$c = offitravel_ovabrw_get_contact_meta($product_id);
	return ($c['phone'] !== '' || $c['email'] !== '' || $c['whatsapp'] !== '');
}

/**
 * @deprecated Ya no se usa en el front.
 */
function offitravel_ovabrw_contact_force_unavailable_enabled($product_id)
{
	return 'yes' === get_post_meta((int) $product_id, OFFITRAVEL_OVABRW_CONTACT_META_FORCE_UNAVAILABLE, true);
}

/**
 * ¿Modo «solo botones de contacto» activo? (No depende de la lista blanca.)
 * Migración: si la meta nueva no existe, se usa la antigua «forzar solo mensaje» (no botones si estaba en yes).
 *
 * @param int $product_id
 * @return bool
 */
function offitravel_ovabrw_contact_show_buttons_enabled($product_id)
{
	$product_id = (int) $product_id;
	$raw        = get_post_meta($product_id, OFFITRAVEL_OVABRW_CONTACT_META_SHOW_BUTTONS, true);
	if ('yes' === $raw) {
		return true;
	}
	if ('no' === $raw) {
		return false;
	}
	$old_force = get_post_meta($product_id, OFFITRAVEL_OVABRW_CONTACT_META_FORCE_UNAVAILABLE, true);
	if ('yes' === $old_force) {
		return false;
	}
	return true;
}

/**
 * Compatibilidad: el modo antiguo «solo mensaje» se sustituyó por la nueva casilla.
 *
 * @param int $product_id
 * @return bool
 */
function offitravel_ovabrw_should_show_contact_force_message($product_id)
{
	return false;
}

/**
 * Mostrar solo botones de contacto: opción activa + al menos un dato (teléfono, correo o WhatsApp).
 * No depende de si hay fechas en lista blanca.
 */
function offitravel_ovabrw_should_show_zero_stock_contact($product_id)
{
	$product_id = (int) $product_id;
	if ($product_id <= 0) {
		return false;
	}
	if (! offitravel_ovabrw_contact_show_buttons_enabled($product_id)) {
		return false;
	}
	if (! offitravel_ovabrw_contact_meta_has_any($product_id)) {
		return false;
	}
	return true;
}

/**
 * Enlace WhatsApp (número o URL completa).
 *
 * @param string $raw
 * @return string
 */
function offitravel_ovabrw_whatsapp_href($raw)
{
	$raw = trim((string) $raw);
	if ($raw === '') {
		return '';
	}
	if (preg_match('#^https?://#i', $raw)) {
		return $raw;
	}
	$digits = preg_replace('/\D+/', '', $raw);
	if ($digits === '') {
		return '';
	}
	return 'https://wa.me/' . $digits;
}

/**
 * Acordeón admin (después de lista blanca).
 */
function offitravel_ovabrw_render_contact_accordion($post_id)
{
	$post_id = (int) $post_id;
	if (! $post_id) {
		return;
	}

	$product = wc_get_product($post_id);
	if (! $product || ! $product->is_type('ovabrw_car_rental')) {
		return;
	}

	if (! function_exists('woocommerce_wp_text_input')) {
		include_once WC()->plugin_path() . 'includes/admin/wc-meta-box-functions.php';
	}

	$c = offitravel_ovabrw_get_contact_meta($post_id);

	$raw_show = get_post_meta($post_id, OFFITRAVEL_OVABRW_CONTACT_META_SHOW_BUTTONS, true);
	if ('' === $raw_show || false === $raw_show) {
		$show_checked = ! offitravel_ovabrw_contact_force_unavailable_enabled($post_id);
	} else {
		$show_checked = ('yes' === $raw_show);
	}

	wp_nonce_field('offitravel_ovabrw_contact_save', 'offitravel_ovabrw_contact_nonce');
?>
	<div class="ovabrw-advanced-settings offitravel-ovabrw-contact-accordion">
		<div class="advanced-header">
			<h3 class="advanced-label"><?php esc_html_e('Datos de Contacto', 'offitravel-ovabrw'); ?></h3>
			<span aria-hidden="true" class="dashicons dashicons-arrow-up"></span>
			<span aria-hidden="true" class="dashicons dashicons-arrow-down"></span>
		</div>
		<div class="advanced-content">
			<p class="description" style="margin-top:0;float:none;">
				<?php esc_html_e('Si no hay ninguna fecha en «Fechas disponibles» y la opción siguiente no está marcada o faltan datos de contacto, en la ficha se mostrará «La salida aún no está disponible.». Si marcas la opción y rellenas teléfono, correo o WhatsApp, en la ficha solo se mostrarán botones para los datos rellenados (sin formulario de reserva).', 'offitravel-ovabrw'); ?>
			</p>
			<p class="form-field">
				<label style="width:100%;">
					<input type="checkbox" name="offitravel_ovabrw_contact_show_buttons" value="yes" <?php checked($show_checked); ?> />
					<?php esc_html_e('Mostrar solo botones de contacto para los campos que tengan valor', 'offitravel-ovabrw'); ?>
				</label>
			</p>
			<?php
			woocommerce_wp_text_input(
				array(
					'id'          => 'offitravel_ovabrw_contact_phone',
					'name'        => 'offitravel_ovabrw_contact_phone',
					'class'       => 'short',
					'label'       => esc_html__('Teléfono', 'offitravel-ovabrw'),
					'type'        => 'text',
					'value'       => $c['phone'],
					'placeholder' => esc_html__('+34 600 000 000', 'offitravel-ovabrw'),
				)
			);
			woocommerce_wp_text_input(
				array(
					'id'          => 'offitravel_ovabrw_contact_email',
					'name'        => 'offitravel_ovabrw_contact_email',
					'class'       => 'short',
					'label'       => esc_html__('Correo electrónico', 'offitravel-ovabrw'),
					'type'        => 'email',
					'value'       => $c['email'],
					'placeholder' => esc_html__('reservas@ejemplo.com', 'offitravel-ovabrw'),
				)
			);
			woocommerce_wp_text_input(
				array(
					'id'          => 'offitravel_ovabrw_contact_whatsapp',
					'name'        => 'offitravel_ovabrw_contact_whatsapp',
					'class'       => 'short',
					'label'       => esc_html__('WhatsApp', 'offitravel-ovabrw'),
					'type'        => 'text',
					'value'       => $c['whatsapp'],
					'placeholder' => esc_html__('Número o enlace https://wa.me/…', 'offitravel-ovabrw'),
					'description' => esc_html__('Solo número (se abrirá wa.me) o URL completa de WhatsApp.', 'offitravel-ovabrw'),
				)
			);
			?>
		</div>
	</div>
<?php
}

/**
 * Guardar metas.
 */
function offitravel_ovabrw_save_contact_meta($post_id, $post)
{
	if (
		! isset($_POST['offitravel_ovabrw_contact_nonce'])
		|| ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['offitravel_ovabrw_contact_nonce'])), 'offitravel_ovabrw_contact_save')
	) {
		return;
	}

	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}

	if (! current_user_can('edit_post', $post_id)) {
		return;
	}

	if ('product' !== $post->post_type) {
		return;
	}

	$phone    = isset($_POST['offitravel_ovabrw_contact_phone']) ? sanitize_text_field(wp_unslash($_POST['offitravel_ovabrw_contact_phone'])) : '';
	$email    = isset($_POST['offitravel_ovabrw_contact_email']) ? sanitize_email(wp_unslash($_POST['offitravel_ovabrw_contact_email'])) : '';
	$whatsapp = isset($_POST['offitravel_ovabrw_contact_whatsapp']) ? sanitize_text_field(wp_unslash($_POST['offitravel_ovabrw_contact_whatsapp'])) : '';

	$show_buttons = isset($_POST['offitravel_ovabrw_contact_show_buttons']) && 'yes' === $_POST['offitravel_ovabrw_contact_show_buttons'] ? 'yes' : 'no';

	update_post_meta($post_id, OFFITRAVEL_OVABRW_CONTACT_META_PHONE, $phone);
	update_post_meta($post_id, OFFITRAVEL_OVABRW_CONTACT_META_EMAIL, $email);
	update_post_meta($post_id, OFFITRAVEL_OVABRW_CONTACT_META_WHATSAPP, $whatsapp);
	update_post_meta($post_id, OFFITRAVEL_OVABRW_CONTACT_META_SHOW_BUTTONS, $show_buttons);
	delete_post_meta($post_id, OFFITRAVEL_OVABRW_CONTACT_META_FORCE_UNAVAILABLE);
}

add_action('woocommerce_process_product_meta', 'offitravel_ovabrw_save_contact_meta', 16, 2);
