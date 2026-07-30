<?php
/**
 * WC_GC_Order class
 *
 * @package  WooCommerce Gift Cards
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Automattic\WooCommerce\Utilities\NumberUtil;

/**
 * WC_GC_Order class.
 *
 * @version 2.7.3
 */
class WC_GC_Order {

	private $debit_order_statuses;
	private $credit_order_statuses;

	/**
	 * Constructor for the cart class. Loads options and hooks in the init method.
	 */
	public function __construct() {
		add_action( 'woocommerce_update_order', array( $this, 'update_order' ) );
		add_action( 'woocommerce_order_after_calculate_totals', array( $this, 'after_calculate_totals' ), 10, 2 );
		add_action( 'woocommerce_get_order_item_totals', array( $this, 'add_order_details_totals' ), 10, 2 );
		add_action( 'woocommerce_admin_order_totals_after_tax', array( $this, 'add_admin_order_totals' ) );
		add_action( 'woocommerce_admin_order_items_after_fees', array( $this, 'add_admin_order_items' ) );
		add_action( 'woocommerce_order_refund_get_amount', array( $this, 'filter_display_refund_amount' ), 10, 2 );

		// Status transitions.
		$this->debit_order_statuses = (array) apply_filters(
			'woocommerce_gc_debit_order_statuses',
			array(
				'on-hold',
				'pending',
				'processing',
				'completed',
			)
		);

		foreach ( $this->debit_order_statuses as $status ) {
			add_action( 'woocommerce_order_status_' . wc_clean( $status ), array( $this, 'maybe_debit_giftcards' ), 10, 2 );
		}

		$this->credit_order_statuses = (array) apply_filters(
			'woocommerce_gc_credit_order_statuses',
			array(
				'cancelled',
				'failed',
				'refunded',
			)
		);

		foreach ( $this->credit_order_statuses as $status ) {
			add_action( 'woocommerce_order_status_' . wc_clean( $status ), array( $this, 'maybe_credit_giftcards' ), 10, 2 );
		}

		add_action( 'woocommerce_checkout_order_processed', array( $this, 'checkout_order_processed' ), 10, 3 );

		add_filter( 'woocommerce_order_item_needs_processing', array( $this, 'gift_card_needs_processing' ), 10, 3 );

		// Add Gift Card line items.
		add_action( 'woocommerce_checkout_create_order', array( $this, 'checkout_create_order' ) );
		add_action( 'woocommerce_resume_order', array( $this, 'handle_order_awaiting_payment' ) );
		add_action( 'woocommerce_checkout_order_exception', array( $this, 'unlock_giftcards' ) );

		// Order item configuration.
		add_filter( 'woocommerce_get_order_item_classname', array( $this, 'get_order_item_classname' ), 10, 2 );
		add_filter( 'woocommerce_get_items_key', array( $this, 'get_items_key' ), 10, 2 );
		add_filter( 'woocommerce_order_type_to_group', array( $this, 'order_type_to_group' ) );
		add_filter( 'woocommerce_data_stores', array( $this, 'order_item_data_store' ) );

		// Order deletion management.
		if ( WC_GC_Core_Compatibility::is_hpos_enabled() ) {
			add_action( 'woocommerce_before_trash_order', array( $this, 'handle_trash_order' ), 0 ); // HPOS Compatibility.
			add_action( 'woocommerce_trash_order', array( $this, 'after_trash_order' ), 0 ); // HPOS Compatibility.
			add_action( 'woocommerce_before_delete_order', array( $this, 'before_delete_order' ), 0 ); // HPOS Compatibility.
		} else {
			add_action( 'wp_trash_post', array( $this, 'handle_trash_order' ), 0 );
			add_action( 'trashed_post', array( $this, 'after_trash_order' ), 0 );
			add_action( 'before_delete_post', array( $this, 'before_delete_order' ), 0 );
		}
		add_action( 'woocommerce_rest_check_permissions', array( $this, 'before_delete_rest_order' ), 0, 4 );

		// Keep track of on-hold balances.
		add_action( 'woocommerce_order_status_changed', array( $this, 'handle_pending_balance_tracking' ), 11 );
		add_filter( 'woocommerce_order_status_pending', array( $this, 'handle_pending_balance_tracking' ), 11 );
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'handle_pending_balance_tracking' ), 11 );

		// Keep track of on-hold balances on specific items.
		add_action( 'woocommerce_gc_gift_card_applied_to_order', array( $this, 'add_pending_balance_tracking' ) );
		add_action( 'woocommerce_gc_gift_card_removed_from_order', array( $this, 'remove_pending_balance_tracking' ) );

		// Add notice in pay page requests.
		add_action( 'template_redirect', array( $this, 'add_notices_in_pay_page' ) );
		add_filter( 'woocommerce_get_checkout_payment_url', array( $this, 'maybe_add_notices_in_order_pay_url' ) );
	}

	/**
	 * Get Gift Cards total amount from a given order.
	 *
	 * @param  WC_Order $order
	 * @return array
	 */
	public function get_gift_cards( $order ) {

		$return = array(
			'codes' => array(),
			'total' => 0.0,
		);

		$giftcards = $order->get_items( 'gift_card' );

		if ( $giftcards ) {

			$total_amount = 0.0;
			$codes        = array();

			foreach ( $giftcards as $id => $giftcard ) {
				$codes[]       = $giftcard->get_code();
				$total_amount += $giftcard->get_amount();
			}

			$return['codes'] = $codes;
			$return['total'] = $total_amount;
		}

		return $return;
	}

	/**
	 * Get total captured amount.
	 *
	 * @since 1.10.0
	 *
	 * @param  int|WC_Order $order
	 * @param  string       $context
	 * @return float
	 */
	public function get_order_total_captured( $order, $context = 'view' ) {

		if ( is_numeric( $order ) ) {
			$order = wc_get_order( $order );
		}

		if ( ! is_a( $order, 'WC_Order' ) ) {
			return 0.0;
		}

		$giftcard_items = $order->get_items( 'gift_card' );
		if ( empty( $giftcard_items ) ) {
			return 0.0;
		}

		$total_captured = 0.0;
		foreach ( $giftcard_items as $order_item ) {
			// Hint: This works using object caching; that’s why we iterate items instead of doing a single query.
			$total_captured += $order_item->get_captured_amount( $context );
		}

		return (float) $total_captured;
	}

	/**
	 * Get total refunded amount.
	 *
	 * @since 1.10.0
	 *
	 * @param  int|WC_Order $order
	 * @param  string       $context
	 * @return float
	 */
	public function get_order_total_refunded( $order, $context = 'view' ) {

		$order_id = 0;
		if ( is_numeric( $order ) ) {
			$order_id = wc_get_order( $order );
		}

		if ( ! is_a( $order, 'WC_Order' ) ) {
			return 0.0;
		}

		$giftcard_items = $order->get_items( 'gift_card' );
		if ( empty( $giftcard_items ) ) {
			return 0.0;
		}

		$total_refunded = 0.0;
		foreach ( $giftcard_items as $order_item ) {
			// Hint: This works using object caching; that’s why we iterate items instead of doing a single query.
			$total_refunded += $order_item->get_refunded_amount( $context );
		}

		return (float) $total_refunded;
	}

	/**
	 * Get total gift cards amount.
	 *
	 * @since 1.10.0
	 *
	 * @param  int|WC_Order $order
	 * @return float
	 */
	public function get_order_total_gift_cards( $order ) {

		if ( is_numeric( $order ) ) {
			$order = wc_get_order( $order );
		}

		if ( ! is_a( $order, 'WC_Order' ) ) {
			return 0.0;
		}

		$total     = 0.0;
		$giftcards = $order->get_items( 'gift_card' );
		if ( $giftcards ) {
			foreach ( $giftcards as $id => $giftcard ) {
				$total += $giftcard->get_amount();
			}
		}

		return $total;
	}

	/**
	 * Get the total amount of gift cards formatted.
	 *
	 * @since 1.10.0
	 *
	 * @param  WC_Order $order
	 * @param  bool     $display_refunded (Optional)
	 * @return string
	 */
	public function get_formatted_order_total_gift_cards( $order, $display_refunded = true ) {

		$total_gift_cards = $this->get_order_total_gift_cards( $order );
		$total_captured   = $this->get_order_total_captured( $order );
		// Hint: We do not wanna use the `get_order_total_refunded` here, as we need to show the captured balance even if an order is canceled, or failed.
		$total_refunded  = $total_gift_cards - $total_captured;
		$formatted_total = wc_price( '-' . $total_gift_cards, array( 'currency' => $order->get_currency() ) );
		if ( $total_refunded && $display_refunded ) {
			$total_paid = $total_gift_cards - $total_refunded;
			if ( 0 < $total_paid ) {
				$total_paid = $total_paid * -1;
			}

			$formatted_total = '<del aria-hidden="true">' . wp_strip_all_tags( $formatted_total ) . '</del> <ins>' . wc_price( $total_paid, array( 'currency' => $order->get_currency() ) ) . '</ins>';
		}

		/**
		 * Filter WooCommerce Gift Cards formatted order total.
		 *
		 * @param string   $formatted_total  Total to display.
		 * @param WC_Order $order            Order data.
		 * @param bool     $display_refunded If should include refunded value.
		 */
		return apply_filters( 'woocommerce_gc_get_formatted_order_total', $formatted_total, $order, $display_refunded );
	}

	/**
	 * Filters the refund amount in view context.
	 *
	 * @since 1.10.0
	 *
	 * @param  float           $value
	 * @param  WC_Order_Refund $refund
	 * @return void
	 */
	public function filter_display_refund_amount( $value, $refund ) {

		if ( did_action( 'woocommerce_admin_order_item_values' ) < did_action( 'woocommerce_admin_order_items_after_refunds' ) || did_action( 'woocommerce_order_details_before_order_table_items' ) < did_action( 'woocommerce_order_details_after_order_table_items' ) ) {
			return $value;
		}

		$activities = $refund->get_meta( '_wc_gc_refund_activities', true );
		if ( empty( $activities ) ) {
			return $value;
		}

		$value = 0.0;
		foreach ( $activities as $activity_id ) {
			$activity = WC_GC()->db->activity->get( $activity_id );
			if ( ! $activity || ! $activity->is_type( 'manually_refunded' ) ) {
				continue;
			}

			$value += $activity->get_amount();
		}

		return $value;
	}

	/**
	 * Re-Calculate order's total.
	 *
	 * @param  WC_Order $order
	 * @return float
	 */
	public function get_order_total( $order ) {

		$items_total       = 0;
		$fees_total        = 0;
		$round_at_subtotal = 'yes' === get_option( 'woocommerce_tax_round_at_subtotal' );

		/**
		 * Manually calculate order total.
		 *
		 * We use NumberUtil::round() for consistency with WooCommerce's internal calculations (see WC_Item_Totals::get_rounded_items_total() ).
		 * Similar to WooCommerce, we pass a value in precision format to NumberUtil::round() and then remove the precision to get the final value.
		 */
		if ( ! $round_at_subtotal ) {
			foreach ( $order->get_items() as $item ) {
				$item_total   = wc_add_number_precision( $item->get_total(), false );
				$items_total += wc_remove_number_precision( NumberUtil::round( $item_total ) );
			}
		} else {
			foreach ( $order->get_items() as $item ) {
				$items_total += $item->get_total();
			}
		}

		foreach ( $order->get_fees() as $item ) {
			$fee_total = $item->get_total();

			// Negative fees should not make the order total go negative.
			if ( 0 > $fee_total ) {
				$max_discount = round( $items_total + $fees_total + $order->get_shipping_total(), wc_get_price_decimals() ) * -1;

				if ( $fee_total < $max_discount && 0 > $max_discount ) {
					$item->set_total( $max_discount );
				}
			}

			$fees_total += $item->get_total();
		}

		$total = round( $items_total + $fees_total + $order->get_shipping_total() + $order->get_cart_tax() + $order->get_shipping_tax(), wc_get_price_decimals() );

		return $total;
	}

	/**
	 * Has order refunds to gift cards.
	 *
	 * @param  int|WC_Order $order_id
	 * @return bool
	 */
	public function has_order_refunds_to_gift_cards( $order ) {

		if ( is_numeric( $order ) ) {
			$order = wc_get_order( absint( $order ) );
		}

		if ( ! is_a( $order, 'WC_Order' ) ) {
			return false;
		}

		foreach ( $order->get_refunds() as $refund ) {
			if ( ! empty( $refund->get_meta( '_wc_gc_refund_activities', true ) ) ) {
				return true;
			}
		}
	}

	/**
	 * Get Gift Cards total amount from a given order.
	 *
	 * @param  bool     $and_taxes
	 * @param  WC_Order $order
	 * @return void
	 */
	public function after_calculate_totals( $and_taxes, $order ) {
		$giftcards_total = $this->get_gift_cards( $order );
		if ( $giftcards_total['total'] > 0 ) {
			$order->set_total( max( 0, $order->get_total() - $giftcards_total['total'] ) );
		}
	}

	/**
	 * Calculate totals when updating an order.
	 *
	 * @param  int $order_id
	 * @return void
	 */
	public function update_order( $order_id ) {

		// Avoid infinite loops.
		remove_action( 'woocommerce_update_order', array( $this, 'update_order' ) );

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		$giftcards = $this->get_gift_cards( $order );
		if ( $giftcards['total'] > 0 ) {

			$order_total = $this->get_order_total( $order );
			$order->set_total( max( 0, $order_total - $giftcards['total'] ) );
			$order->save();
		}

		// Re-hook self.
		add_action( 'woocommerce_update_order', array( $this, 'update_order' ) );
	}

	/**
	 * Print Gift Card item in admin order details.
	 *
	 * @param  WC_GC_Order_Item_Gift_Card $giftcard_order_item
	 * @param  WC_Order                   $order
	 * @return void
	 */
	private function print_admin_order_item( $giftcard_order_item, $order ) {

		$giftcard        = $giftcard_order_item->get_giftcard();
		$giftcard_exists = false !== $giftcard;
		$captured_amount = $giftcard_order_item->get_captured_amount();
		$mask            = wc_gc_mask_codes( 'admin' );

		?><tr class="giftcards item" data-gc_code="<?php echo esc_attr( $giftcard_order_item->get_code() ); ?>" data-order_item_id="<?php echo intval( $giftcard_order_item->get_id() ); ?>">
			<td class="thumb">
				<div></div>
			</td>
			<td class="name">
				<div class="view">
					<?php
					esc_html_e( 'Gift Card', 'woocommerce-gift-cards' );
					if ( ! $giftcard_exists ) {
						?>
						<span class="giftcard-item-deleted">
							<?php
							echo '&nbsp;&mdash;&nbsp;';
							echo esc_html_x( 'deleted', 'giftcard admin status label', 'woocommerce-gift-cards' );
							printf( '<div class="woocommerce-help-tip" data-tip="%s"></div>', esc_html__( 'This gift card was deleted after it was applied to this order. Order totals are not affected.', 'woocommerce-gift-cards' ) );
							?>
						</span>
						<?php
					}
					?>
				</div>
				<div class="view">
					<table cellspacing="0" class="display_meta">
						<tbody>
							<tr>
								<th>
									<?php esc_html_e( 'Code:', 'woocommerce-gift-cards' ); ?>
								</th>
								<td>
									<?php echo esc_html( $mask ? wc_gc_mask_code( $giftcard_order_item->get_code() ) : $giftcard_order_item->get_code() ); ?>
								</td>
							</tr>
							<tr>
								<th>
									<?php esc_html_e( 'Captured amount:', 'woocommerce-gift-cards' ); ?>
								</th>
								<td>
									<?php echo wp_kses_post( wc_price( $captured_amount ) ); ?>
								</td>
							</tr>
							<tr>
								<th>
									<?php esc_html_e( 'Available balance:', 'woocommerce-gift-cards' ); ?>
								</th>
								<td>
									<?php echo $giftcard_exists ? wp_kses_post( wc_price( $giftcard->get_balance() ) ) : esc_html__( 'N/A', 'woocommerce-gift-cards' ); ?>
								</td>
							</tr>
							<tr>
								<th>
									<?php esc_html_e( 'Expires:', 'woocommerce-gift-cards' ); ?>
								</th>
								<td>
									<?php
									if ( ! $giftcard_exists ) {
										echo esc_html__( 'N/A', 'woocommerce-gift-cards' );
									} elseif ( $giftcard->has_expired() ) {
										/* translators: %s: Giftcard Expiration date */
										printf( esc_html__( 'Expired on %s', 'woocommerce-gift-cards' ), esc_html( date_i18n( get_option( 'date_format' ), $giftcard->get_expire_date() ) ) );
									} else {
										echo 0 === $giftcard->get_expire_date() ? '&ndash;' : esc_html( date_i18n( get_option( 'date_format', $giftcard->get_expire_date() ) ) );
									}
									?>
								</td>
							</tr>
							<tr>
								<td colspan="2">
									<?php if ( $giftcard_exists ) { ?>
										<a target="_blank" href="<?php echo esc_url( admin_url( 'admin.php?page=gc_giftcards&section=edit&giftcard=' . $giftcard->get_id() ) ); ?>">
											<?php esc_html_e( 'View Gift Card &rarr;', 'woocommerce-gift-cards' ); ?>
										</a>
									<?php } ?>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</td>
			<td class="item_cost" width="1%">&nbsp;</td>
			<td class="quantity" width="1%">&nbsp;</td>

			<td class="line_cost" width="1%">
				<div class="view">-
				<?php

					echo wp_kses_post( wc_price( $giftcard_order_item->get_amount(), array( 'currency' => $order->get_currency() ) ) );
				if ( $giftcard_order_item->get_amount() > $captured_amount ) {
					?>
						<small class="refunded giftcard_refunded">+
						<?php
						echo wp_kses_post( wc_price( $giftcard_order_item->get_amount() - $captured_amount ) );
						?>
						</small>
						<?php
				}

				?>
				</div>
			</td>
			<?php

			if ( wc_tax_enabled() && $order->get_total_tax() > 0 ) {

				?>
				<td class="line_tax" width="1%">
					<div class="view">
						&ndash;
					</div>
				</td>
				<?php
			}
			?>
			<td class="wc-order-edit-line-item" width="1%">
				<div class="wc-order-edit-line-item-actions">
					<?php if ( 0 == $giftcard_order_item->get_refunded_amount() ) { ?>
					<a class="delete-gift-card-item tips" href="#" data-tip="<?php esc_attr_e( 'Remove gift card', 'woocommerce-gift-cards' ); ?>"></a>
					<?php } ?>
				</div>
			</td>
		</tr>
		<?php
	}

	/**
	 * Adds Gift Cards totals in admin order details.
	 *
	 * @param  int $order_id
	 * @return void
	 */
	public function add_admin_order_items( $order_id ) {

		// Maybe cache the object in prop?
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		$giftcards = $order->get_items( 'gift_card' );
		if ( $giftcards ) {
			foreach ( $giftcards as $id => $giftcard_order_item ) {
				$this->print_admin_order_item( $giftcard_order_item, $order );
			}
		}
	}

	/**
	 * Adds Gift Cards totals in admin order details.
	 *
	 * @param  int $order_id
	 * @return void
	 */
	public function add_admin_order_totals( $order_id ) {

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		$giftcards = $this->get_gift_cards( $order );
		if ( $giftcards['total'] ) {

			?>
			<tr>
				<td class="label">
					<?php esc_html_e( 'Order Total', 'woocommerce-gift-cards' ); ?>
					<small><?php esc_html_e( '(before gift cards)', 'woocommerce-gift-cards' ); ?></small>:
				</td>
				<td width="1%"></td>
				<td class="total">
					<?php echo wp_kses_post( wc_price( $this->get_order_total( $order ), array( 'currency' => $order->get_currency() ) ) ); ?>
				</td>
			</tr>
			<tr>
				<td class="label"><?php echo esc_html( __( 'Gift Cards:', 'woocommerce-gift-cards' ) ); ?></td>
				<td width="1%"></td>
				<td class="total">-
					<?php echo wp_kses_post( wc_price( $giftcards['total'], array( 'currency' => $order->get_currency() ) ) ); ?>
				</td>
			</tr>
			<?php
		}
	}

	/**
	 * Adds Gift Cards totals in order details.
	 *
	 * @param  array    $total_rows
	 * @param  WC_Order $order
	 * @return array
	 */
	public function add_order_details_totals( $total_rows, $order ) {

		if ( isset( $total_rows['giftcards'] ) ) {
			return $total_rows;
		}

		$giftcards        = $this->get_gift_cards( $order );
		$total_gift_cards = $giftcards['total'];
		if ( $total_gift_cards ) {

			$before_giftcards_row = array(
				'label' => esc_html__( 'Total', 'woocommerce-gift-cards' ) . ' ' . esc_html__( '(before Gift Cards)', 'woocommerce-gift-cards' ) . ':',
				'value' => wc_price( $this->get_order_total( $order ), array( 'currency' => $order->get_currency() ) ),
			);

			$giftcards_row = array(
				'label' => __( 'Gift Cards:', 'woocommerce-gift-cards' ),
				'value' => $this->get_formatted_order_total_gift_cards( $order ),
			);

			// Inject before Total.
			$total_index = array_search( 'order_total', array_keys( $total_rows ) );
			if ( false !== $total_index ) {
				$total_rows = array_slice( $total_rows, 0, $total_index, true ) + array( 'before_giftcards' => $before_giftcards_row ) + array( 'giftcards' => $giftcards_row ) + array_slice( $total_rows, $total_index, count( $total_rows ) - $total_index, true );
			} else {
				$total_rows['before_giftcards'] = $before_giftcards_row;
				$total_rows['giftcards']        = $giftcards_row;
			}
		}

		return $total_rows;
	}

	/**
	 * If only Gift Cards in cart transition to Complete.
	 *
	 * @param  bool       $is_processing_needed
	 * @param  WC_Product $product
	 * @param  int        $order_id
	 * @return bool
	 */
	public function gift_card_needs_processing( $is_processing_needed, $product, $order_id ) {

		if ( WC_GC_Gift_Card_Product::is_gift_card( $product ) ) {
			$is_processing_needed = false;
		}

		return $is_processing_needed;
	}

	/**
	 * Maybe debit balances.
	 *
	 * @throws Exception
	 *
	 * @param  int      $order_id
	 * @param  WC_Order $order
	 * @param  array    $giftcard_items (Optional)
	 * @return void
	 */
	public function maybe_debit_giftcards( $order_id, $order, $giftcard_items = array() ) {

		if ( empty( $giftcard_items ) ) {
			$giftcard_items = $order->get_items( 'gift_card' );
		}

		if ( empty( $giftcard_items ) ) {
			return;
		}

		$error = null;
		try {

			foreach ( $giftcard_items as $id => $order_item ) {

				$giftcard = new WC_GC_Gift_Card( $order_item->get_giftcard_id() );
				if ( $giftcard->get_id() ) {

					if ( $order_item->meta_exists( 'gift_card_debited' ) || $order_item->get_amount() === $order_item->get_captured_amount( 'db' ) || $order_item->get_amount() === $order_item->get_refunded_amount( 'db' ) ) {
						continue;
					}

					$amount = $order_item->get_amount() - $order_item->get_captured_amount() - $order_item->get_refunded_amount();
					if ( $amount > 0 ) {

						if ( $giftcard->debit( $amount, $order ) ) {
							/* translators: %1$s gift card code, %2$s debited amount */
							$order->add_order_note( sprintf( __( 'Debited %2$s to gift card code <span class="woocommerce-giftcards-admin-note-code">%1$s</span>.', 'woocommerce-gift-cards' ), $giftcard->get_code(), wc_price( $amount, array( 'currency' => $order->get_currency() ) ) ), false, true );
						} else {

							throw new Exception( sprintf( __( 'Gift card code %s does not have enough balance.', 'woocommerce-gift-cards' ), $giftcard->get_code() ), 1 );
						}
					}

					// Cancel debit action.
					$order_item->delete_meta_data( 'gift_card_credited' );
					$order_item->add_meta_data( 'gift_card_debited', 'yes', true );
					$order_item->save();

				} else {
					throw new Exception( __( 'Gift card not found.', 'woocommerce-gift-cards' ), 1 );
				}
			}
		} catch ( Exception $e ) {
			$error = $e;
		} finally {
			$this->unlock_giftcards( $order );

			if ( $error instanceof Exception ) {
				throw $error;
			}
		}
	}

	/**
	 * Maybe credit balances.
	 *
	 * @param  int      $order_id
	 * @param  WC_Order $order
	 * @param  array    $giftcard_items (Optional)
	 * @return void
	 */
	public function maybe_credit_giftcards( $order_id, $order, $giftcard_items = array() ) {

		if ( empty( $giftcard_items ) ) {
			$giftcard_items = $order->get_items( 'gift_card' );
		}

		if ( empty( $giftcard_items ) ) {
			return;
		}

		foreach ( $giftcard_items as $id => $order_item ) {

			$giftcard = new WC_GC_Gift_Card( $order_item->get_giftcard_id() );
			if ( $giftcard->get_id() ) {

				if ( $order_item->meta_exists( 'gift_card_credited' ) || 0 == $order_item->get_captured_amount( 'db' ) ) {
					continue;
				}

				$amount = $order_item->get_captured_amount();
				if ( $amount > 0 ) {
					$giftcard->credit( $amount, $order );
					/* translators: %1$s gift card code, %2$s credited amount */
					$order->add_order_note( sprintf( __( 'Credited %2$s to gift card code <span class="woocommerce-giftcards-admin-note-code">%1$s</span>.', 'woocommerce-gift-cards' ), $giftcard->get_code(), wc_price( $amount, array( 'currency' => $order->get_currency() ) ) ), false, true );
				}

				// Cancel debit action.
				$order_item->delete_meta_data( 'gift_card_debited' );
				$order_item->add_meta_data( 'gift_card_credited', 'yes', true );
				$order_item->save();
			}
		}
	}

	/**
	 * Adds Gift Cards order items.
	 *
	 * @throws Exception
	 *
	 * @param  WC_Order $order
	 * @return void
	 */
	public function checkout_create_order( $order ) {

		$locked_giftcard_keys = array();
		$error                = null;

		try {

			// Fetch all active giftcards.
			$giftcards = WC_GC()->giftcards->get();
			if ( empty( $giftcards ) ) {
				return;
			}

			$is_resuming_order = did_action( 'woocommerce_resume_order' );
			foreach ( $giftcards as $giftcard_info ) {

				$lock_key = $giftcard_info['giftcard']->lock_transactions();
				if ( $lock_key ) {
					$locked_giftcard_keys[ $giftcard_info['giftcard']->get_id() ] = $lock_key;
				}

				// Re-fetch.
				$giftcard = new WC_GC_Gift_Card( $giftcard_info['giftcard']->get_id() );
				$amount   = (float) $giftcard_info['amount'];

				// Sanity checks.
				$is_valid_giftcard = $giftcard->get_id() && $giftcard->is_active() && ! $giftcard->has_expired();
				$is_valid_balance  = $is_resuming_order ? true : $giftcard->get_balance() >= $amount;

				if ( $is_valid_giftcard && $is_valid_balance ) {

					$item = new WC_GC_Order_Item_Gift_Card();

					$item->set_props(
						array(
							'giftcard_id' => $giftcard->get_id(),
							'code'        => $giftcard->get_code(),
							'amount'      => $amount,
						)
					);

					$order->add_item( $item );

				} else {
					throw new Exception( __( 'Failed to apply gift card codes.', 'woocommerce-gift-cards' ), 1 );
				}
			}
		} catch ( Exception $e ) {
			$error = $e;
		} finally {

			if ( ! empty( $locked_giftcard_keys ) ) {
				$order->update_meta_data( '_giftcard_lock_keys', $locked_giftcard_keys );
			}

			if ( $error instanceof Exception ) {
				throw $error;
			}
		}
	}

	/**
	 * Unlocks all giftcards in the given order.
	 *
	 * @since  1.14.0
	 *
	 * @param  WC_Order $order
	 * @return void
	 */
	public function unlock_giftcards( $order ) {

		$lock_keys = $order->get_meta( '_giftcard_lock_keys' );
		if ( empty( $lock_keys ) ) {
			return;
		}

		global $wpdb;
		foreach ( $lock_keys as $key ) {
			$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}woocommerce_gc_cardsmeta where meta_key = %s", $key ) );
		}

		$order->delete_meta_data( '_giftcard_lock_keys' );
	}

	/**
	 * If a resume order is placed we need to sanity check all on-hold balances. @see WC_GC_Order::checkout_create_order.
	 *
	 * @since  1.5.4
	 *
	 * @throws Exception
	 *
	 * @param  int $order_id
	 * @return void
	 */
	public function handle_order_awaiting_payment( $order_id ) {
		$order = $order_id ? wc_get_order( $order_id ) : null;
		if ( ! is_a( $order, 'WC_Order' ) ) {
			return;
		}

		// Fetch all active giftcards.
		$giftcards      = WC_GC()->giftcards->get();
		$giftcard_items = $order->get_items( 'gift_card' );

		if ( $giftcard_items ) {
			foreach ( $giftcard_items as $id => $order_item ) {

				foreach ( $giftcards as $giftcard_info ) {

					// Search for order item's GC in session.
					if ( $giftcard_info['giftcard']->get_code() === $order_item->get_code() ) {

						// Check balances.
						if ( $giftcard_info['amount'] !== $order_item->get_amount() ) {
							/* translators: Gift Card code */
							throw new Exception( sprintf( __( 'Failed to apply gift card code %s.', 'woocommerce-gift-cards' ), $order_item->get_code() ), 1 );
						}

						break;
					}
				}
			}
		}
	}

	/**
	 * Debit Gift Cards on newly created orders.
	 *
	 * @param  int      $order_id
	 * @param  array    $posted_data
	 * @param  WC_Order $order
	 * @return void
	 */
	public function checkout_order_processed( $order_id, $posted_data, $order ) {

		// Check order status. @see `woocommerce_default_order_status` filter.
		if ( in_array( $order->get_status(), $this->debit_order_statuses ) ) {
			$this->maybe_debit_giftcards( $order_id, $order );
		}
	}

	/*
	---------------------------------------------------*/
	/*
		Order Item Configuration.                        */
	/*---------------------------------------------------*/

	/**
	 * Filter order item classname.
	 *
	 * @param  string $classname
	 * @param  string $item_type
	 * @return string
	 */
	public function get_order_item_classname( $classname, $item_type ) {

		if ( 'gift_card' === $item_type ) {
			$classname = 'WC_GC_Order_Item_Gift_Card';
		}

		return $classname;
	}

	/**
	 * Filter order item key.
	 *
	 * @param  string $key
	 * @param  mixed  $item
	 * @return string
	 */
	public function get_items_key( $key, $item ) {

		if ( is_a( $item, 'WC_GC_Order_Item_Gift_Card' ) ) {
			return 'gift_cards';
		}

		return $key;
	}

	/**
	 * Filter order item type group.
	 *
	 * @param  array $groups
	 * @return array
	 */
	public function order_type_to_group( $groups ) {

		$groups['gift_card'] = 'gift_cards';

		return $groups;
	}

	/**
	 * Attach data store to order item.
	 *
	 * @param  array $stores
	 * @return array
	 */
	public function order_item_data_store( $stores ) {

		if ( ! isset( $stores['order-item-gift_card'] ) ) {
			$stores['order-item-gift_card'] = 'WC_Order_Item_Gift_Card_Data_Store';
		}

		return $stores;
	}

	/**
	 * Handle trashing orders.
	 *
	 * @since  1.5.4
	 *
	 * @param  int $post_id Post ID or Order id.
	 * @return void
	 */
	public function handle_trash_order( $post_id ) {

		if ( WC_GC_Core_Compatibility::is_hpos_enabled() ) {

			if ( ! Automattic\WooCommerce\Utilities\OrderUtil::is_order( $post_id, wc_get_order_types() ) ) {
				return;
			}

			$order = wc_get_order( $post_id );
			if ( ! is_a( $order, 'WC_Order' ) ) {
				return;
			}

			if ( ! in_array( $order->get_status(), wc_gc_get_order_pending_statuses() ) ) {
				return;
			}

			$order_giftcards = $order->get_items( 'gift_card' );
			if ( empty( $order_giftcards ) ) {
				// No giftcards.
				return;
			}

			$order->update_status( 'wc-cancelled', __( 'Moving Pending order with applied gift cards to the Trash.', 'woocommerce-gift-cards' ) );
			$order->update_meta_data( '_wc_gc_restore_order_status', true );
			$order->save();

		} else {

			// Fetch the post type.
			$post_type = get_post_type( $post_id );
			if ( ! in_array( $post_type, wc_get_order_types(), true ) ) {
				return;
			}

			$order = wc_get_order( $post_id );
			if ( ! is_a( $order, 'WC_Order' ) ) {
				return;
			}

			if ( ! in_array( $order->get_status(), wc_gc_get_order_pending_statuses() ) ) {
				return;
			}

			$order_giftcards = $order->get_items( 'gift_card' );
			if ( empty( $order_giftcards ) ) {
				// No giftcards.
				return;
			}

			$order->update_status( 'wc-cancelled', __( 'Moving Pending order with applied gift cards to the Trash.', 'woocommerce-gift-cards' ) );
			update_post_meta( $post_id, '_wc_gc_restore_order_status', true );
		}
	}

	/**
	 * After order trashed replace with cancelled status.
	 *
	 * @since  1.5.4
	 *
	 * @param  int $post_id Post ID or Order ID.
	 * @return void
	 */
	public function after_trash_order( $post_id ) {

		if ( WC_GC_Core_Compatibility::is_hpos_enabled() ) {

			if ( ! Automattic\WooCommerce\Utilities\OrderUtil::is_order( $post_id, wc_get_order_types() ) ) {
				return;
			}

			$order = wc_get_order( $post_id );
			if ( ! is_a( $order, 'WC_Order' ) ) {
				return;
			}

			if ( $order->get_meta( '_wc_gc_restore_order_status', true ) ) {
				$order->update_meta_data( '_wp_trash_meta_status', 'wc-cancelled' );
				$order->delete_meta_data( '_wc_gc_restore_order_status' );
				$order->save();
			}
		} else {

			// There is no need to get the post type and check if valid order, as this is already done in 'handle_trash_order'.
			if ( get_post_meta( $post_id, '_wc_gc_restore_order_status', true ) ) {
				update_post_meta( $post_id, '_wp_trash_meta_status', 'wc-cancelled' );
				delete_post_meta( $post_id, '_wc_gc_restore_order_status' );
			}
		}
	}

	/**
	 * Check for pending balances in giftcards before deleting an Order.
	 *
	 * @since  1.5.4
	 *
	 * @param  int $post_id Post ID or Order id.
	 * @return void
	 */
	public function before_delete_order( $post_id ) {

		if ( WC_GC_Core_Compatibility::is_hpos_enabled() ) {

			if ( ! Automattic\WooCommerce\Utilities\OrderUtil::is_order( $post_id, wc_get_order_types() ) ) {
				return;
			}

			$order = wc_get_order( $post_id );
			if ( ! is_a( $order, 'WC_Order' ) ) {
				return;
			}

			if ( ! in_array( $order->get_status(), wc_gc_get_order_pending_statuses() ) ) {
				return;
			}

			$giftcards = $order->get_items( 'gift_card' );
			if ( ! empty( $giftcards ) ) {
				// Cancel the order in order to return gift card funds.
				$order->update_status( 'wc-cancelled' );
				$order->save();
			}
		} else {

			// Fetch the post type.
			$post_type = get_post_type( $post_id );
			if ( ! in_array( $post_type, wc_get_order_types(), true ) ) {
				return;
			}

			$order = wc_get_order( $post_id );
			if ( ! is_a( $order, 'WC_Order' ) ) {
				return;
			}

			if ( ! in_array( $order->get_status(), wc_gc_get_order_pending_statuses() ) ) {
				return;
			}

			$giftcards = $order->get_items( 'gift_card' );
			if ( ! empty( $giftcards ) ) {
				// Cancel the order in order to return gift card funds.
				$order->update_status( 'wc-cancelled' );
			}
		}
	}

	/**
	 * Check for pending balances in giftcards before deleting an Order through REST API.
	 *
	 * @since  1.5.4
	 *
	 * @param  bool   $permission
	 * @param  string $context
	 * @param  int    $object_id
	 * @param  string $post_type
	 * @return bool
	 */
	public function before_delete_rest_order( $permission, $context, $object_id, $post_type ) {

		if ( ! $permission || 'delete' !== $context ) {
			return $permission;
		}

		if ( ! in_array( $post_type, wc_get_order_types(), true ) ) {
			return $permission;
		}

		$order = wc_get_order( $object_id );
		if ( ! is_a( $order, 'WC_Order' ) ) {
			return $permission;
		}

		if ( ! in_array( $order->get_status(), wc_gc_get_order_pending_statuses() ) ) {
			return $permission;
		}

		$giftcards = $order->get_items( 'gift_card' );
		if ( ! empty( $giftcards ) ) {
			$permission = false;
		}

		return $permission;
	}

	/**
	 * Handle pending balances.
	 *
	 * @since  1.6.0
	 *
	 * @param  int $order_id
	 * @return void
	 */
	public function handle_pending_balance_tracking( $order_id ) {

		$order = wc_get_order( $order_id );
		if ( ! is_a( $order, 'WC_Order' ) ) {
			return;
		}

		$giftcard_items = $order->get_items( 'gift_card' );
		if ( $giftcard_items ) {

			// If it's pending payment, track the balance.
			if ( in_array( $order->get_status(), wc_gc_get_order_pending_statuses() ) ) {
				array_map( array( $this, 'add_pending_balance_tracking' ), $giftcard_items );
				// If it's not pending payment, remove tracking.
			} else {
				array_map( array( $this, 'remove_pending_balance_tracking' ), $giftcard_items );
			}
		}
	}

	/**
	 * Add pending balances tracking meta.
	 *
	 * @since  1.6.0
	 *
	 * @param  WC_Order_Item $order_id
	 * @param  WC_Order      $order (Optional)
	 * @return bool
	 */
	public function add_pending_balance_tracking( $order_item, $order = null ) {
		if ( ! is_a( $order_item, 'WC_Order_Item' ) ) {
			return false;
		}

		if ( ! is_a( $order, 'WC_Order' ) ) {
			$order = wc_get_order( $order_item->get_order_id() );
		}

		if ( ! is_a( $order, 'WC_Order' ) ) {
			return false;
		}

		// Sanity check.
		if ( ! in_array( $order->get_status(), wc_gc_get_order_pending_statuses() ) ) {
			return false;
		}

		$giftcard = new WC_GC_Gift_Card_Data( $order_item->get_giftcard_id() );
		if ( ! $giftcard || ! $giftcard->get_id() ) {
			return false;
		}

		/**
		 * `woocommerce_gc_before_add_pending_balance_tracking` action.
		 *
		 * @since 1.6.0
		 *
		 * @param WC_GC_Gift_Card_Data $giftcard
		 * @param WC_Order_Item        $order_item
		 */
		do_action( 'woocommerce_gc_before_add_pending_balance_tracking', $giftcard, $order_item );

		$giftcard->update_meta( 'balance_' . $order_item->get_order_id(), $order_item->get_captured_amount( 'db' ) );

		return $giftcard->save();
	}

	/**
	 * Add pending balances tracking meta.
	 *
	 * @since  1.6.0
	 *
	 * @param  WC_Order_Item $order_id
	 * @return bool
	 */
	public function remove_pending_balance_tracking( $order_item ) {
		if ( ! is_a( $order_item, 'WC_Order_Item' ) ) {
			return false;
		}

		$giftcard = new WC_GC_Gift_Card_Data( $order_item->get_giftcard_id() );
		if ( ! $giftcard || ! $giftcard->get_id() ) {
			return false;
		}

		if ( ! $giftcard->get_meta( 'balance_' . $order_item->get_order_id() ) ) {
			return false;
		}

		/**
		 * `woocommerce_gc_before_remove_pending_balance_tracking` action.
		 *
		 * @since 1.6.0
		 *
		 * @param WC_GC_Gift_Card_Data $giftcard
		 * @param WC_Order_Item        $order_item
		 */
		do_action( 'woocommerce_gc_before_remove_pending_balance_tracking', $giftcard, $order_item );

		$giftcard->delete_meta( 'balance_' . $order_item->get_order_id() );

		return $giftcard->save();
	}

	/**
	 * Add help notice in pay-pages.
	 *
	 * @since 1.6.0
	 *
	 * @return void
	 */
	public function add_notices_in_pay_page() {

		if ( ! is_checkout_pay_page() || ! isset( $_GET['wc_gc_pay_order_pending_status'] ) ) {
			return;
		}

		if ( 'notice' === $_GET['wc_gc_pay_order_pending_status'] ) {
			global $wp;

			$order_id = absint( $wp->query_vars['order-pay'] );
			if ( ! $order_id ) {
				return;
			}

			$order = wc_get_order( $order_id );
			if ( ! is_a( $order, 'WC_Order' ) ) {
				return;
			}

			$notice_text = esc_html__( 'Pay or cancel this order now to release gift card funds on hold.', 'woocommerce-gift-cards' );
			if ( ! current_user_can( 'pay_for_order', $order_id ) && ! is_user_logged_in() ) {
				$notice = $notice_text;
			} else {
				$button_class    = wc_gc_wp_theme_get_element_class_name( 'button' );
				$wp_button_class = $button_class ? ' ' . $button_class : '';
				$notice          = sprintf( '<a href="%s" class="button wc-forward%s">%s</a> %s', $order->get_cancel_order_url(), $wp_button_class, esc_html__( 'Cancel order', 'woocommerce-gift-cards' ), $notice_text );
			}

			wc_add_notice( $notice );
		}
	}

	/**
	 * Maybe move pay page notice params in the next redirect url.
	 *
	 * @since 1.6.0
	 *
	 * @return void
	 */
	public function maybe_add_notices_in_order_pay_url( $url ) {

		if ( ! is_checkout_pay_page() || ! isset( $_GET['wc_gc_pay_order_pending_status'] ) ) {
			return $url;
		}

		if ( 'notice' === $_GET['wc_gc_pay_order_pending_status'] ) {
			$url = add_query_arg( array( 'wc_gc_pay_order_pending_status' => 'notice' ), $url );
		}

		return $url; // nosemgrep: audit.php.wp.security.xss.query-arg
	}
}
