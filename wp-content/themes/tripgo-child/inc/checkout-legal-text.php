<?php
/**
 * OFFITRAVEL - Texto legal custom del checkout clásico.
 */

if (!defined('ABSPATH')) {
  exit;
}

/**
 * Oculta el texto de privacidad default:
 * "Your personal data will be used..."
 */
add_filter('woocommerce_get_privacy_policy_text', 'offitravel_hide_checkout_privacy_text', 10, 2);

function offitravel_hide_checkout_privacy_text($text, $type)
{
  if ('checkout' === $type) {
    return '';
  }

  return $text;
}

/**
 * Evita que WooCommerce muestre/valide el checkbox clásico de términos.
 */
add_filter('woocommerce_checkout_show_terms', '__return_false');

/**
 * Quita el contenido largo de la página de términos y el checkbox default.
 */
add_action('wp', 'offitravel_remove_default_checkout_terms');

function offitravel_remove_default_checkout_terms()
{
  if (!function_exists('is_checkout') || !is_checkout()) {
    return;
  }

  remove_action('woocommerce_checkout_terms_and_conditions', 'wc_terms_and_conditions_page_content', 30);
  remove_action('woocommerce_checkout_terms_and_conditions', 'wc_terms_and_conditions_checkbox', 40);
}

/**
 * Imprime texto legal custom antes del botón "Realizar el pedido".
 */
add_action('woocommerce_review_order_before_submit', 'offitravel_render_checkout_legal_text', 5);

function offitravel_render_checkout_legal_text()
{
  if (!function_exists('is_checkout') || !is_checkout()) {
    return;
  }

  $terms_url = home_url('/condiciones-generales-de-contratacion/');
  $privacy_url = home_url('/politica-de-privacidad/'); ?>

  <div
    class="wc-block-checkout__terms wc-block-checkout__terms--with-separator wp-block-woocommerce-checkout-terms-block offi-checkout-legal-text">
    <span class="wc-block-components-checkbox__label">
      Al proceder con tu compra aceptas nuestros
      <a href="<?= esc_url($terms_url); ?>" target="_blank" rel="noopener noreferrer">Términos y condiciones</a>
      y
      <a href="<?= esc_url($privacy_url); ?>" target="_blank" rel="noopener noreferrer">Política de privacidad</a>
    </span>
  </div>

  <?php
}