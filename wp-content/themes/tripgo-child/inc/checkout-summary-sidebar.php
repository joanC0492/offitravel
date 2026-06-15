<?php
/**
 * OFFITRAVEL - Aside custom del checkout.
 * Shortcode: [offi_checkout_summary]
 */

if (!defined('ABSPATH')) {
  exit;
}

add_shortcode('offi_checkout_summary', 'offi_checkout_summary_shortcode');

function offi_checkout_summary_shortcode()
{
  if (!function_exists('WC') || !WC()->cart || WC()->cart->is_empty()) {
    return '';
  }

  return offi_get_checkout_summary_html();
}

function offi_get_checkout_summary_html()
{
  if (!function_exists('WC') || !WC()->cart || WC()->cart->is_empty()) {
    return '';
  }

  ob_start();
  ?>

  <aside class="offi-checkout-summary">
    <div class="offi-checkout-summary__header">
      <h2>Resumen del pedido</h2>
    </div>

    <div class="offi-checkout-summary__items">
      <?php foreach (WC()->cart->get_cart() as $cart_item): ?>
        <?php
        $_product = $cart_item['data'] ?? null;

        if (!$_product || !$_product->exists()) {
          continue;
        }

        $product_name = $_product->get_name();
        $quantity = !empty($cart_item['quantity']) ? absint($cart_item['quantity']) : 1;
        $item_data = wc_get_formatted_cart_item_data($cart_item);
        $item_total = WC()->cart->get_product_subtotal($_product, $quantity);
        ?>

        <div class="offi-summary-item">
          <div class="offi-summary-item__image">
            <span class="offi-summary-item__qty"><?php echo esc_html($quantity); ?></span>
            <?php echo wp_kses_post($_product->get_image('woocommerce_thumbnail')); ?>
          </div>

          <div class="offi-summary-item__content">
            <h3><?php echo esc_html($product_name); ?></h3>
            <!--  -->
            <div class="offi-summary-item__price">
              <?php echo wp_kses_post($item_total); ?>
            </div>
            <?php
            $product_description = $_product->get_short_description();

            if (empty($product_description) && $_product->is_type('variation')) {
              $parent_product = wc_get_product($_product->get_parent_id());

              if ($parent_product) {
                $product_description = $parent_product->get_short_description();
              }
            }
            ?>
            <?php if (!empty($product_description)): ?>
              <div class="offi-summary-item__description">
                <?php echo wp_kses_post(wpautop($product_description)); ?>
              </div>
            <?php endif; ?>
            <!--  -->

            <?php if (!empty($item_data)): ?>
              <div class="offi-summary-item__meta">
                <?php echo wp_kses_post($item_data); ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>


    <?php if (wc_coupons_enabled()): ?>
      <details class="offi-checkout-summary__coupon">
        <summary class="offi-checkout-summary__coupon-title">
          <span>Añadir cupones</span>
          <span class="offi-checkout-summary__coupon-icon" aria-hidden="true"></span>
        </summary>

        <form id="offi_checkout_coupon_form" class="checkout_coupon woocommerce-form-coupon offi-checkout-summary__coupon-form" method="post">
          <p class="form-row form-row-first">
            <label for="offi_coupon_code" class="screen-reader-text">Cupón:</label>
            <input type="text" name="coupon_code" class="input-text" placeholder="Introduce el código" id="offi_coupon_code"
              value="">
          </p>

          <p class="form-row form-row-last">
            <button type="submit" class="button" name="apply_coupon" value="Aplicar">Aplicar</button>
          </p>

          <div class="clear"></div>
        </form>
      </details>
    <?php endif; ?>


    <div class="offi-checkout-summary__totals">
      <div class="offi-summary-total-row">
        <span>Subtotal</span>
        <strong><?php echo wp_kses_post(WC()->cart->get_cart_subtotal()); ?></strong>
      </div>

      <div class="offi-summary-total-row offi-summary-total-row--final">
        <span>Total</span>
        <strong><?php echo wp_kses_post(WC()->cart->get_total()); ?></strong>
      </div>
    </div>
  </aside>

  <?php
  return ob_get_clean();
}

add_filter('woocommerce_update_order_review_fragments', 'offi_update_checkout_summary_fragment');

function offi_update_checkout_summary_fragment($fragments)
{
  $fragments['.offi-checkout-summary'] = offi_get_checkout_summary_html();
  return $fragments;
}