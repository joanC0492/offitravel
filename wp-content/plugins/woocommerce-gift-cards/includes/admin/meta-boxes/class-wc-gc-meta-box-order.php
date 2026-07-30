<?php
/**
 * WC_GC_Meta_Box_Order class
 *
 * @package  WooCommerce Gift Cards
 * @since    1.2.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin AJAX meta-box handlers.
 *
 * @class    WC_GC_Meta_Box_Order
 * @version  2.1.1
 */
class WC_GC_Meta_Box_Order {

	/**
	 * Order object to use in 'display_edit_button'.
	 *
	 * @since  1.3.0
	 *
	 * @var WC_Order
	 */
	protected static $order;

	/**
	 * Hook in.
	 */
	public static function init() {

		// Add 'Apply gift card' button in editable orders.
		add_filter( 'woocommerce_order_item_add_action_buttons', array( __CLASS__, 'add_order_item_action_button' ) );

		// Save order object to use in 'display_edit_button'.
		add_action( 'woocommerce_admin_order_item_headers', array( __CLASS__, 'set_order' ) );

		// Display "Configure/Edit" button next to Gift Card Product items in the edit-order screen.
		add_action( 'woocommerce_after_order_itemmeta', array( __CLASS__, 'display_edit_button' ), 10, 3 );

		// Add JS template.
		add_action( 'admin_footer', array( __CLASS__, 'add_js_template' ) );

		// Configure gift card template hooks.
		add_action( 'woocommerce_gc_form_fields_html', array( __CLASS__, 'admin_form_field_code_html' ), 50 );
	}

	/**
	 * Save order object to use in 'display_edit_button'.
	 *
	 * Although the order object can be retrieved via 'WC_Order_Item::get_order', we've seen a significant performance hit when using that method.
	 *
	 * @since  1.3.0
	 *
	 * @param  WC_Order $order
	 * @return void
	 */
	public static function set_order( $order ) {
		self::$order = $order;
	}

	/**
	 * Display "Configure/Edit" button next to Gift Card Product items in the edit-order screen.
	 *
	 * @since  1.3.0
	 *
	 * @param  $item_id  int
	 * @param  $item     WC_Order_Item
	 * @param  $product  WC_Product
	 * @return void
	 */
	public static function display_edit_button( $item_id, $item, $product ) {

		if ( self::$order && self::$order->is_editable() && 'line_item' === $item->get_type() ) {

			if ( $product && WC_GC_Gift_Card_Product::is_gift_card( $product ) ) {

				// Is configurable?
				$is_configurable = empty( $item->get_meta( 'wc_gc_giftcards', true ) );
				if ( ! $is_configurable ) {
					return;
				}

				// Already configured?
				$is_configured = ! empty( $item->get_meta( 'wc_gc_giftcard_amount', true ) ) || 0 == $item->get_meta( 'wc_gc_giftcard_amount', true );

				?>
				<div class="configure_order_item">
					<button class="<?php echo $is_configured ? 'edit_gift_card' : 'configure_gift_card'; ?> button">
												<?php

												if ( $is_configured ) {
													esc_html_e( 'Edit Gift Card', 'woocommerce-gift-cards' );
												} else {
													esc_html_e( 'Configure Gift Card', 'woocommerce-gift-cards' );
												}

												?>
					</button>
				</div>
				<?php
			}
		}
	}

	/**
	 * JS template of modal for configuring/editing gift cards.
	 *
	 * @since  1.3.0
	 *
	 * @return void
	 */
	public static function add_js_template() {

		if ( wp_script_is( 'wc-gc-writepanel' ) ) {
			?>
			<script type="text/template" id="tmpl-wc-modal-edit-gift-card">
				<div class="wc-backbone-modal">
					<div class="wc-backbone-modal-content wc-backbone-modal-content-gift-card">
						<section class="wc-backbone-modal-main" role="main">
							<header class="wc-backbone-modal-header">
								<h1>{{{ data.action }}}</h1>
								<button class="modal-close modal-close-link dashicons dashicons-no-alt">
									<span class="screen-reader-text">Close modal panel</span>
								</button>
							</header>
							<article>
								<form action="" method="post">
								</form>
							</article>
							<footer>
								<div class="inner">
									<button id="btn-ok" class="button button-primary button-large"><?php esc_html_e( 'Done', 'woocommerce-gift-cards' ); ?></button>
								</div>
							</footer>
						</section>
					</div>
				</div>
				<div class="wc-backbone-modal-backdrop modal-close"></div>
			</script>
			<?php
		}
	}

	/**
	 * Add action button in order items table.
	 *
	 * @since  1.2.0
	 *
	 * @param  WC_order $order
	 * @return void
	 */
	public static function add_order_item_action_button( $order ) {

		if ( ! $order->is_editable() ) {
			return;
		}

		if ( 'shop_order' !== $order->get_type() ) {
			return;
		}

		?>
		<button type="button" class="button add-gift-card"><?php esc_html_e( 'Apply gift card', 'woocommerce-gift-cards' ); ?></button>
		<?php
	}

	/**
	 * Prints the admin form field html for code input.
	 *
	 * @since  1.6.0
	 *
	 * @param  WC_Product $product
	 * @return void
	 */
	public static function admin_form_field_code_html( $product ) {

		if ( ! is_admin() ) {
			return;
		}

		$code = ! empty( $_REQUEST['wc_gc_giftcard_code'] ) ? sanitize_text_field( $_REQUEST['wc_gc_giftcard_code'] ) : '';

		?>
		<div class="wc-gc-edit-code">

			<label><input type="checkbox" name="wc_gc_giftcard_code_random"<?php echo empty( $code ) ? ' checked="checked"' : ''; ?>><?php esc_html_e( 'Generate a random code', 'woocommerce-gift-cards' ); ?></label>

			<div class="wc-gc-field wc_gc_giftcard_code">
				<label for="wc_gc_giftcard_code"><?php esc_html_e( 'Code', 'woocommerce-gift-cards' ); ?></label>
				<input type="text" placeholder="<?php esc_attr_e( 'XXXX-XXXX-XXXX-XXXX', 'woocommerce-gift-cards' ); ?>" name="wc_gc_giftcard_code" autocomplete="off" value="<?php echo esc_attr( $code ); ?>" />
			</div>

		</div>
		<?php
	}
}

WC_GC_Meta_Box_Order::init();
