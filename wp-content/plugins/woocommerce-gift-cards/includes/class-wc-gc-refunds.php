<?php
/**
 * Refunds controller class.
 *
 * @package  WooCommerce Gift Cards
 * @since    1.10.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_GC_Refunds Class.
 *
 * @version 2.7.3
 */
class WC_GC_Refunds {

	/**
	 * Setup refunds in admin.
	 */
	public static function init() {

		// Handle gift card refunds.
		add_action( 'woocommerce_create_refund', array( __CLASS__, 'handle_create_refund' ), 10, 2 );
		add_action( 'woocommerce_gc_create_refund', array( __CLASS__, 'disable_refund_emails' ) );
		add_action( 'woocommerce_gc_refund_created', array( __CLASS__, 'enable_refund_emails' ), 10, 2 );

		// Refund AJAX handler.
		add_action( 'wp_ajax_woocommerce_gc_refund_line_items', array( __CLASS__, 'refund_line_items' ) );

		// Re-hook & override AJAX handler when deleting refunds.
		remove_action( 'wp_ajax_woocommerce_delete_refund', array( 'WC_AJAX', 'delete_refund' ) );
		add_action( 'wp_ajax_woocommerce_delete_refund', array( __CLASS__, 'delete_refund' ) );

		// Handle refunds hooks.
		add_action( 'woocommerce_order_status_refunded', array( __CLASS__, 'order_fully_refunded' ), 9, 2 );

		add_filter( 'woocommerce_order_is_partially_refunded', array( __CLASS__, 'is_partially_refunded' ), 10, 2 );
	}

	/*
	---------------------------------------------------*/
	/*
		Refund flow methods.
	/*---------------------------------------------------*/

	/**
	 * Add gift card specific data when creating a new refund object.
	 * TODO: In case of failure, revert refunds.
	 *
	 * @param  WC_Order_Refund $refund
	 * @param  array           $args
	 * @return void
	 */
	public static function handle_create_refund( $refund, $args ) {

		// Bail early.
		if ( ! isset( $args['amount_to_gift_cards'] ) || 0 > $args['amount_to_gift_cards'] ) {
			return;
		}

		$refund_amount = (float) $args['amount_to_gift_cards'];
		if ( 0 > $refund_amount ) {
			throw new Exception( __( 'Invalid refund amount.', 'woocommerce-gift-cards' ) );
		}

		$order    = wc_get_order( $refund->get_parent_id() );
		$order_id = $order->get_id();

		$left         = $refund_amount;
		$user         = wp_get_current_user();
		$user_id      = is_a( $user, 'WP_User' ) ? $user->ID : 1;
		$activity_ids = array();

		// Find gift cards to refund.
		foreach ( $order->get_items( 'gift_card' ) as $item ) {

			$giftcard = new WC_GC_Gift_Card( $item->get_giftcard_id() );
			if ( ! $giftcard->get_id() ) {
				continue;
			}

			$captured_amount = $item->get_captured_amount();
			if ( 0 == $captured_amount ) {
				continue;
			}

			// Hint: Use min() here for safety and rounding errors.
			$balance_to_use = min( $captured_amount, $left );
			$left          -= $balance_to_use;

			$giftcard->credit( $balance_to_use, $order, false );

			// Log action.
			$activity_ids[] = WC_GC()->db->activity->add(
				array(
					'type'      => 'manually_refunded',
					'gc_id'     => $giftcard->get_id(),
					'object_id' => $order_id,
					'user_id'   => $user_id,
					'amount'    => $balance_to_use,
				)
			);

			// Clear caches.
			$cache_key = WC_Cache_Helper::get_cache_prefix( 'orders' ) . 'gc_total_refunded_' . $giftcard->get_id() . '_' . $order_id;
			wp_cache_delete( $cache_key, 'order-items' );

			if ( 0 == $left ) {
				break;
			}
		}

		if ( empty( $activity_ids ) ) {
			throw new Exception( __( 'Cannot process refund. Please try again.', 'woocommerce-gift-cards' ) );
		} else {
			$refund->add_meta_data( '_wc_gc_refund_activities', $activity_ids );

			// Handle pending balances.
			WC_GC()->order->handle_pending_balance_tracking( $order_id );
		}
	}

	/**
	 * When refunding an order, check for captured balance before consider it as fully refunded.
	 *
	 * @since 1.12.2
	 *
	 * @param  boolean $is_partial
	 * @param  int     $order_id
	 * @return boolean
	 */
	public static function is_partially_refunded( $is_partial, $order_id ) {

		$order = wc_get_order( $order_id );
		if ( ! is_a( $order, 'WC_Order' ) ) {
			return $is_partial;
		}

		// Has gift cards?
		$giftcards = $order->get_items( 'gift_card' );
		if ( empty( $giftcards ) ) {
			return $is_partial;
		}

		return $is_partial || 0 < WC_GC()->order->get_order_total_captured( $order );
	}

	/**
	 * When refunding an order, create a refund line item if the partial refunds do not match gift card order total.
	 *
	 * @param  int      $order_id
	 * @param  WC_Order $order
	 * @return void
	 */
	public static function order_fully_refunded( $order_id, $order ) {
		if ( ! is_a( $order, 'WC_Order' ) ) {
			return;
		}

		// Add a new refund.
		$total_captured = WC_GC()->order->get_order_total_captured( $order, 'db' );
		if ( 0 < $total_captured ) {

			wc_switch_to_site_locale();
			wc_gc_create_refund(
				array(
					'amount'   => $total_captured,
					'order_id' => $order_id,
				)
			);
			wc_restore_locale();
		}
	}

	/**
	 * Re-hook emails actions for the WC_Email_Customer_Refunded_Order email.
	 *
	 * @param  int   $refund_id
	 * @param  array $args
	 * @return void
	 */
	public static function enable_refund_emails( $refund_id, $args ) {
		$order = wc_get_order( absint( $args['order_id'] ) );
		if ( ! is_a( $order, 'WC_Order' ) ) {
			return;
		}

		if ( ! did_action( 'woocommerce_order_status_refunded' ) ) {
			return;
		}

		$max_conventional_refund = wc_format_decimal( $order->get_total() - $order->get_total_refunded() );
		if ( ! $max_conventional_refund ) {
			return;
		}

		$emails = WC()->mailer->get_emails();
		if ( isset( $emails['WC_Email_Customer_Refunded_Order'] ) ) {
			add_action( 'woocommerce_order_fully_refunded_notification', array( $emails['WC_Email_Customer_Refunded_Order'], 'trigger_full' ), 10, 2 );

			add_action( 'woocommerce_order_partially_refunded_notification', array( $emails['WC_Email_Customer_Refunded_Order'], 'trigger_partial' ), 10, 2 );
		}
	}

	/**
	 * Un-hook emails actions for the WC_Email_Customer_Refunded_Order email.
	 *
	 * @param  int   $refund_id
	 * @param  array $args
	 * @return void
	 */
	public static function disable_refund_emails( $args ) {
		$order = wc_get_order( absint( $args['order_id'] ) );
		if ( ! is_a( $order, 'WC_Order' ) ) {
			return;
		}

		if ( ! did_action( 'woocommerce_order_status_refunded' ) ) {
			return;
		}

		$max_conventional_refund = wc_format_decimal( $order->get_total() - $order->get_total_refunded() );
		if ( ! $max_conventional_refund ) {
			return;
		}

		$emails = WC()->mailer->get_emails();
		if ( isset( $emails['WC_Email_Customer_Refunded_Order'] ) ) {
			remove_action( 'woocommerce_order_fully_refunded_notification', array( $emails['WC_Email_Customer_Refunded_Order'], 'trigger_full' ), 10, 2 );

			remove_action( 'woocommerce_order_partially_refunded_notification', array( $emails['WC_Email_Customer_Refunded_Order'], 'trigger_partial' ), 10, 2 );
		}
	}

	/**
	 * Delete a refund.
	 *
	 * @override
	 *
	 * @return void
	 */
	public static function delete_refund() {
		check_ajax_referer( 'order-item', 'security' );

		if ( ! current_user_can( 'edit_shop_orders' ) || ! isset( $_POST['refund_id'] ) ) {
			wp_die( -1 );
		}

		$refund_ids = array_map( 'absint', is_array( $_POST['refund_id'] ) ? wp_unslash( $_POST['refund_id'] ) : array( wp_unslash( $_POST['refund_id'] ) ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$errors     = array();

		foreach ( $refund_ids as $refund_id ) {
			$result = self::handle_delete_refund( $refund_id );
			if ( is_string( $result ) ) {
				$errors[ $refund_id ] = $result;
			} elseif ( false === $result ) {
				$errors[ $refund_id ] = __( 'The refund could not be processed.', 'woocommerce-gift-cards' );
			}
		}

		if ( ! empty( $errors ) ) {
			wp_send_json_error( array( 'errors' => $errors ) );
		}

		// Move on...
		WC_AJAX::delete_refund();
	}

	/**
	 * Handle deleting a refund.
	 *
	 * @param  int $refund_id
	 * @return bool|string
	 */
	public static function handle_delete_refund( $refund_id ) {

		if ( empty( $refund_id ) ) {
			return false;
		}

		$refund = wc_get_order( $refund_id );
		if ( ! is_a( $refund, 'WC_Order_Refund' ) || 'shop_order_refund' !== $refund->get_type() ) {
			return false;
		}

		$activity_ids = array();
		$error        = '';
		$activities   = $refund->get_meta( '_wc_gc_refund_activities', true );
		if ( empty( $activities ) ) {
			// No need to work, move on.
			return true;
		}

		// Do a sanity validation. Does all gift cards has enough balance to proceed?
		$invalid_codes_for_debit = array();
		foreach ( $activities as $activity_id ) {
			$activity = WC_GC()->db->activity->get( $activity_id );
			if ( ! $activity || ! $activity->is_type( 'manually_refunded' ) ) {
				continue;
			}

			$giftcard = wc_gc_get_gift_card( $activity->get_gc_id() );
			if ( ! $giftcard ) {
				continue;
			}

			if ( $activity->get_amount() > $giftcard->get_balance() ) {
				$invalid_codes_for_debit[] = $giftcard->get_code();
			} else {
				$activity_ids[] = $activity_id;
			}
		}

		// Bail early if there is at least one invalid code.
		if ( ! empty( $invalid_codes_for_debit ) ) {

			return _n( sprintf( 'Unable to reverse Refund #%d: Insufficient balance in gift card code `%s`.', $refund_id, implode( ', ', $invalid_codes_for_debit ) ), sprintf( 'Unable to reverse Refund #%d: Insufficient balance in gift card codes `%s`.', $refund_id, implode( ', ', $invalid_codes_for_debit ) ), count( $invalid_codes_for_debit ), 'woocommerce-gift-cards' );
		}

		// Do the actual refund reverse to gift cards.
		$unique_order_ids = array();
		if ( ! empty( $activity_ids ) ) {
			foreach ( $activity_ids as $activity_id ) {
				$activity = WC_GC()->db->activity->get( $activity_id );
				if ( ! $activity || ! $activity->is_type( 'manually_refunded' ) ) {
					continue;
				}

				$giftcard = wc_gc_get_gift_card( $activity->get_gc_id() );
				if ( ! $giftcard ) {
					continue;
				}

				// Decorate object for payments.
				$giftcard = new WC_GC_Gift_Card( $giftcard );
				if ( $giftcard->debit( $activity->get_amount(), false, false ) ) {

					// Log the action.
					$user          = wp_get_current_user();
					$activity_args = array(
						'gc_id'      => $giftcard->get_id(),
						'user_id'    => $user->ID,
						'user_email' => $user->user_email,
						'object_id'  => $activity->get_object_id(),
						'type'       => 'refund_reversed',
						'amount'     => $activity->get_amount(),
					);

					$unique_order_ids[] = $activity->get_object_id();

					WC_GC()->db->activity->add( $activity_args );

					// Clear caches for refunded.
					$cache_key = WC_Cache_Helper::get_cache_prefix( 'orders' ) . 'gc_total_refunded_' . $giftcard->get_id() . '_' . $activity->get_object_id();
					wp_cache_delete( $cache_key, 'order-items' );
				}
			}

			// Handle pending balances.
			$unique_order_ids = array_unique( $unique_order_ids );
			foreach ( $unique_order_ids as $order_id ) {
				WC_GC()->order->handle_pending_balance_tracking( $order_id );
			}
		}

		return true;
	}

	/**
	 * Handle a refund via the edit order screen.
	 *
	 * @return void
	 */
	public static function refund_line_items() {
		ob_start();

		check_ajax_referer( 'order-item', 'security' );

		if ( ! current_user_can( 'edit_shop_orders' ) ) {
			wp_die( -1 );
		}

		$order_id                  = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : 0;
		$refund_amount             = isset( $_POST['refund_amount'] ) ? wc_format_decimal( (float) sanitize_text_field( wp_unslash( $_POST['refund_amount'] ) ), wc_get_price_decimals() ) : 0;
		$refunded_amount           = isset( $_POST['refunded_amount'] ) ? wc_format_decimal( (float) sanitize_text_field( wp_unslash( $_POST['refunded_amount'] ) ), wc_get_price_decimals() ) : 0;
		$gift_card_refunded_amount = isset( $_POST['gift_card_refunded_amount'] ) ? wc_format_decimal( (float) sanitize_text_field( wp_unslash( $_POST['gift_card_refunded_amount'] ) ), wc_get_price_decimals() ) : 0;
		$refund_reason             = isset( $_POST['refund_reason'] ) ? sanitize_text_field( wp_unslash( $_POST['refund_reason'] ) ) : '';
		$line_item_qtys            = isset( $_POST['line_item_qtys'] ) ? json_decode( sanitize_text_field( wp_unslash( $_POST['line_item_qtys'] ) ), true ) : array();
		$line_item_totals          = isset( $_POST['line_item_totals'] ) ? json_decode( sanitize_text_field( wp_unslash( $_POST['line_item_totals'] ) ), true ) : array();
		$line_item_tax_totals      = isset( $_POST['line_item_tax_totals'] ) ? json_decode( sanitize_text_field( wp_unslash( $_POST['line_item_tax_totals'] ) ), true ) : array();
		$restock_refunded_items    = isset( $_POST['restock_refunded_items'] ) && 'true' === $_POST['restock_refunded_items'];
		$refund                    = false;
		$response                  = array();

		try {
			$order = wc_get_order( $order_id );
			if ( ! is_a( $order, 'WC_Order' ) ) {
				throw new Exception( __( 'Error processing refund.', 'woocommerce-gift-cards' ) );
			}

			$giftcard_items = $order->get_items( 'gift_card' );
			if ( empty( $giftcard_items ) ) {
				throw new Exception( __( 'Error processing refund. No gift cards found.', 'woocommerce-gift-cards' ) );
			}

			$max_refund = wc_format_decimal( WC_GC()->order->get_order_total_captured( $order, 'db' ), wc_get_price_decimals() );

			if ( ( ! $refund_amount && ( wc_format_decimal( 0, wc_get_price_decimals() ) !== $refund_amount ) ) || $max_refund < $refund_amount || 0 > $refund_amount ) {
				throw new Exception( __( 'Invalid refund amount', 'woocommerce' ) );
			}

			if ( wc_format_decimal( $order->get_total_refunded(), wc_get_price_decimals() ) !== $refunded_amount ) {
				throw new Exception( __( 'Error processing refund. Please try again.', 'woocommerce' ) );
			}

			if ( wc_format_decimal( WC_GC()->order->get_order_total_gift_cards( $order ) - WC_GC()->order->get_order_total_captured( $order ), wc_get_price_decimals() ) !== $gift_card_refunded_amount ) {
				throw new Exception( __( 'Error processing refund. Please try again.', 'woocommerce' ) );
			}

			// Prepare line items which we are refunding.
			$line_items = array();
			$item_ids   = array_unique( array_merge( array_keys( $line_item_qtys ), array_keys( $line_item_totals ) ) );

			foreach ( $item_ids as $item_id ) {
				$line_items[ $item_id ] = array(
					'qty'          => 0,
					'refund_total' => 0,
					'refund_tax'   => array(),
				);
			}
			foreach ( $line_item_qtys as $item_id => $qty ) {
				$line_items[ $item_id ]['qty'] = max( $qty, 0 );
			}
			foreach ( $line_item_totals as $item_id => $total ) {
				$line_items[ $item_id ]['refund_total'] = wc_format_decimal( (float) $total );
			}
			foreach ( $line_item_tax_totals as $item_id => $tax_totals ) {
				$line_items[ $item_id ]['refund_tax'] = array_filter( array_map( 'floatval', $tax_totals ) );
			}

			wc_switch_to_site_locale();
			$refund = wc_gc_create_refund(
				array(
					'amount'        => $refund_amount,
					'reason'        => $refund_reason,
					'order_id'      => $order_id,
					'line_items'    => $line_items,
					'restock_items' => $restock_refunded_items,
				)
			);
			wc_restore_locale();

			if ( is_wp_error( $refund ) ) {
				throw new Exception( $refund->get_error_message() );
			}
		} catch ( Exception $e ) {
			wp_send_json_error( array( 'error' => $e->getMessage() ) );
		}

		wp_send_json_success( $response );
	}
}

WC_GC_Refunds::init();
