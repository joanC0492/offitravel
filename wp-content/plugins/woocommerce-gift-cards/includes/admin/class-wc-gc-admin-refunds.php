<?php
/**
 * Admin refunds controller class.
 *
 * @package  WooCommerce Gift Cards
 * @since    1.10.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_GC_Admin_Refunds Class.
 *
 * @version 2.0.0
 */
class WC_GC_Admin_Refunds {

	/**
	 * Setup refunds in admin.
	 */
	public static function init() {

		// Display.
		add_action( 'woocommerce_admin_order_totals_after_total', array( __CLASS__, 'add_admin_refund_totals' ), 9 );
		add_action( 'woocommerce_after_order_refund_item_name', array( __CLASS__, 'add_admin_refund_line_description' ) );

		// Fix admin-order refunds UI.
		add_filter( 'woocommerce_admin_order_should_render_refunds', array( __CLASS__, 'should_render_refunds' ), 10, 2 );
	}

	/**
	 * Whether or not to render the refund button.
	 *
	 * @since 1.12.2
	 *
	 * @param  boolean $should
	 * @param  int     $order_id
	 * @return boolean
	 */
	public static function should_render_refunds( $should, $order_id ) {

		$order = wc_get_order( $order_id );
		if ( ! is_a( $order, 'WC_Order' ) ) {
			return;
		}

		if ( 0 >= WC_GC()->order->get_order_total_gift_cards( $order ) ) {
			return $should;
		}

		return $should || 0 < WC_GC()->order->get_order_total_captured( $order );
	}

	/*
	---------------------------------------------------*/
	/*
		Refunds admin UI.
	/*---------------------------------------------------*/

	/**
	 * Adds Gift Cards refund data in admin order totals.
	 *
	 * @param  int $order_id
	 * @return void
	 */
	public static function add_admin_refund_totals( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		$total_gift_cards = WC_GC()->order->get_order_total_gift_cards( $order );
		$total_captured   = WC_GC()->order->get_order_total_captured( $order );

		if ( $total_gift_cards > $total_captured ) {
			?>
			<tr>
				<td class="label refunded-total gift-cards-refunded-total"><?php esc_html_e( 'Refunded', 'woocommerce' ); ?> <small><?php esc_html_e( '(to gift cards)', 'woocommerce-gift-cards' ); ?></small>:</td>
				<td width="1%"></td>
				<td class="total refunded-total">-<?php echo wp_kses_post( wc_price( $total_gift_cards - $total_captured, array( 'currency' => $order->get_currency() ) ) ); ?></td>
			</tr>
			<tr>
				<td class="label label-highlight"><?php esc_html_e( 'Net payment', 'woocommerce-gift-cards' ); ?> <small><?php esc_html_e( '(via gift cards)', 'woocommerce-gift-cards' ); ?></small>:</td>
				<td width="1%"></td>
				<td class="total"><?php echo wp_kses_post( wc_price( $total_captured, array( 'currency' => $order->get_currency() ) ) ); ?></td>
			</tr>
			<?php
		}

		// Hint: jQuery will move these elements below into the summary totals table.
		?>
		<tr class="wc_gc_move_row_to_refund_summary" style="display: none;">
			<td class="label"><?php esc_html_e( 'Total available to refund to gift cards', 'woocommerce-gift-cards' ); ?>:</td>
			<td class="total"><?php echo wp_kses_post( wc_price( $total_captured, array( 'currency' => $order->get_currency() ) ) ); ?></td>
		</tr>
		<tr class="wc_gc_move_row_to_refund_summary" style="display: none;">
			<td class="label"><?php esc_html_e( 'Amount already refunded to gift cards', 'woocommerce-gift-cards' ); ?>:</td>
			<td class="total">
				-<?php echo wp_kses_post( wc_price( $total_gift_cards - $total_captured, array( 'currency' => $order->get_currency() ) ) ); ?>
				<input type="hidden" id="gift_card_refunded_amount" name="gift_card_refunded_amount" value="<?php echo esc_attr( number_format( $total_gift_cards - $total_captured, wc_get_price_decimals() ) ); ?>" />
			</td>
		</tr>
		<?php
	}

	/**
	 * Adds Gift Cards refund description in admin order totals.
	 *
	 * @param  WC_Order_Refund $refund
	 * @return void
	 */
	public static function add_admin_refund_line_description( $refund ) {

		if ( ! is_a( $refund, 'WC_Order_Refund' ) ) {
			return;
		}

		$activities = $refund->get_meta( '_wc_gc_refund_activities', true );
		if ( empty( $activities ) ) {
			return;
		}

		$mask  = wc_gc_mask_codes( 'admin' );
		$text  = _n( 'Refunded to gift card code:', 'Refunded to gift card codes:', count( $activities ), 'woocommerce-gift-cards' );
		$codes = array();

		foreach ( $activities as $id ) {
			$activity = WC_GC()->db->activity->get( $id );
			if ( ! $activity ) {
				continue;
			}
			$codes[] = $mask ? wc_gc_mask_code( $activity->get_gc_code() ) : $activity->get_gc_code();
		}
		$text .= ' ' . implode( ', ', $codes );
		?>
		<p class="description">
			<?php echo esc_html( $text ); ?>
		</p>
		<?php
	}
}

WC_GC_Admin_Refunds::init();
