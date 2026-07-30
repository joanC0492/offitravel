<?php
/**
 * WC_GC_Admin_Ajax class
 *
 * @package  WooCommerce Gift Cards
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin AJAX meta-box handlers.
 *
 * @class    WC_GC_Admin_Ajax
 * @version  2.2.1
 */
class WC_GC_Admin_Ajax {

	/**
	 * Hook in.
	 */
	public static function init() {

		// Notices.
		add_action( 'wp_ajax_wc_gc_dismiss_notice', array( __CLASS__, 'dismiss_notice' ) );

		// Ajax handler for performing loopback tests.
		add_action( 'wp_ajax_wc_gc_health-check-loopback_test', array( __CLASS__, 'ajax_loopback_test' ) );

		// Metaboxes order.
		add_action( 'wp_ajax_wc_gc_remove_order_item_gift_card', array( __CLASS__, 'remove_order_item_gift_card' ) );
		add_action( 'wp_ajax_wc_gc_add_order_item_gift_card', array( __CLASS__, 'add_order_item_gift_card' ) );
		add_action( 'wp_ajax_wc_gc_configure_order_item_gift_card', array( __CLASS__, 'configure_order_item_gift_card' ) );
		add_action( 'wp_ajax_wc_gc_edit_order_item_gift_card_in_order', array( __CLASS__, 'edit_order_item_gift_card_in_order' ) );
	}

	/**
	 * Validate & Save gift card order item.
	 *
	 * @since 1.3.0
	 *
	 * @return void
	 */
	public static function edit_order_item_gift_card_in_order() {

		$failure = array(
			'result' => 'failure',
		);

		if ( ! current_user_can( 'edit_shop_orders' ) ) {
			wp_send_json( $failure );
		}

		if ( ! check_ajax_referer( 'wc_gc_edit_gift_card_order_item', 'security', false ) ) {
			wp_send_json( $failure );
		}

		if ( empty( $_POST['order_id'] ) || empty( $_POST['item_id'] ) ) {
			wp_send_json( $failure );
		}

		$order   = wc_get_order( wc_clean( $_POST['order_id'] ) );
		$item_id = absint( wc_clean( $_POST['item_id'] ) );

		if ( ! ( $order instanceof WC_Order ) ) {
			wp_send_json( $failure );
		}

		$item = $order->get_item( $item_id );

		if ( ! ( $item instanceof WC_Order_Item ) ) {
			wp_send_json( $failure );
		}

		$product = $item->get_product();

		if ( ! WC_GC_Gift_Card_Product::is_gift_card( $product ) ) {
			wp_send_json( $failure );
		}

		if ( ! empty( $_POST['fields'] ) ) {
			parse_str( $_POST['fields'], $posted_form_fields ); // @phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			// Manipulate data.
			if ( isset( $posted_form_fields['wc_gc_giftcard_code_random'] ) ) {
				$posted_form_fields['wc_gc_giftcard_code'] = null;
			}

			// Save to order item.
			$configuration = WC_GC_Gift_Card_Product::get_posted_gift_card_configuration( $posted_form_fields );

			// Validate.
			try {

				// Check mantatory fields.
				if ( isset( $configuration['wc_gc_giftcard_to_multiple'] ) && empty( $configuration['wc_gc_giftcard_to_multiple'] ) ) {
					throw new Exception( __( 'Please enter at least one recipient email.', 'woocommerce-gift-cards' ) );
				}

				if ( isset( $configuration['wc_gc_giftcard_to'] ) && empty( $configuration['wc_gc_giftcard_to'] ) ) {
					throw new Exception( __( 'Please enter a recipient email.', 'woocommerce-gift-cards' ) );
				}

				// Email Sanity.
				if ( ! empty( $configuration['wc_gc_giftcard_to'] ) ) {
					if ( ! filter_var( $configuration['wc_gc_giftcard_to'], FILTER_VALIDATE_EMAIL ) ) {

						$maybe_multiple_emails = wc_gc_parse_email_string( $configuration['wc_gc_giftcard_to'] );
						if ( 1 < count( $maybe_multiple_emails ) ) {
							throw new Exception( __( 'Please enter only one recipient email.', 'woocommerce-gift-cards' ) );
						}

						throw new Exception( __( 'Recipient email invalid.', 'woocommerce-gift-cards' ) );
					}
				}

				if ( ! empty( $configuration['wc_gc_giftcard_to_multiple'] ) ) {
					foreach ( $configuration['wc_gc_giftcard_to_multiple'] as $email ) {
						if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
							/* translators: %s: Invalid email string */
							throw new Exception( sprintf( __( 'Invalid recipient email: &quot;%s&quot;.', 'woocommerce-gift-cards' ), $email ) );
						}
					}
				}

				if ( isset( $configuration['wc_gc_giftcard_from'] ) && empty( $configuration['wc_gc_giftcard_from'] ) ) {
					throw new Exception( __( 'Please enter sender\'s name.', 'woocommerce-gift-cards' ) );
				}

				// Check delivery date format.
				if ( ! empty( $configuration['wc_gc_giftcard_delivery'] ) ) {

					// Check for offset.
					if ( ! isset( $configuration['_wc_gc_giftcard_delivery_gmt_offset'] ) ) {
						throw new Exception( __( 'Invalid delivery date timezone.', 'woocommerce-gift-cards' ) );
					}

					$delivery = $configuration['wc_gc_giftcard_delivery'];
					if ( ! wc_gc_is_unix_timestamp( $delivery ) ) {
						throw new Exception( __( 'Invalid delivery date.', 'woocommerce-gift-cards' ) );
					}
				}

				// Check if custom code already exists.
				if ( ! empty( $posted_form_fields['wc_gc_giftcard_code'] ) ) {

					// Check for valid format.
					if ( ! wc_gc_is_gift_card_code( sanitize_text_field( $posted_form_fields['wc_gc_giftcard_code'] ) ) ) {
						throw new Exception( __( 'Gift card codes must follow the format XXXX-XXXX-XXXX-XXXX (X can be any letter or number).', 'woocommerce-gift-cards' ) );
					}

					if ( ! empty( $configuration['wc_gc_giftcard_to_multiple'] ) && count( $configuration['wc_gc_giftcard_to_multiple'] ) > 1 ) {
						throw new Exception( __( 'Assigning custom codes for multiple recipients is currently not possible. If you must assign a custom code, please enter one recipient only.', 'woocommerce-gift-cards' ) );
					}

					$giftcards_count = WC_GC()->db->giftcards->query(
						array(
							'code'  => $posted_form_fields['wc_gc_giftcard_code'],
							'count' => true,
						)
					);
					if ( 0 !== $giftcards_count ) {
						throw new Exception( __( 'Gift card code exists.', 'woocommerce-gift-cards' ) );
					}
				}

				/**
				 * `woocommerce_gc_validate_form_fields` action.
				 *
				 * Used to validate custom form fields.
				 *
				 * @since 1.6.0
				 *
				 * @param array $configuration
				 */
				do_action( 'woocommerce_gc_validate_form_fields', $configuration );

			} catch ( Exception $e ) {
				wp_send_json( array_merge( $failure, array( 'error' => $e->getMessage() ) ) );
			}

			// Save to order item.
			foreach ( WC_GC_Gift_Card_Product::get_form_fields() as $key => $label ) {
				if ( isset( $configuration[ $key ] ) ) {

					// Convert scalar to string.
					if ( is_array( $configuration[ $key ] ) ) {
						$configuration[ $key ] = implode( ', ', $configuration[ $key ] );
					}

					if ( empty( $configuration[ $key ] ) ) {
						$item->delete_meta_data( $key );
					} else {
						$item->add_meta_data( $key, $configuration[ $key ], true );
					}
				}
			}

			// Save the offset if delivery date is set.
			if ( ! empty( $configuration['wc_gc_giftcard_delivery'] ) && isset( $configuration['_wc_gc_giftcard_delivery_gmt_offset'] ) ) {
				$item->add_meta_data( '_wc_gc_giftcard_delivery_gmt_offset', (float) $configuration['_wc_gc_giftcard_delivery_gmt_offset'], true );
			}

			// Clear offset data if no delivery date.
			if ( empty( $configuration['wc_gc_giftcard_delivery'] ) ) {
				$item->delete_meta_data( '_wc_gc_giftcard_delivery_gmt_offset' );
			}

			// Add amount coverted to balance.
			if ( empty( $item->get_meta( 'wc_gc_giftcard_amount', true ) ) ) {
				$amount = apply_filters( 'woocommerce_gc_gift_card_amount', $product->get_regular_price(), $product, $order );
				$item->add_meta_data( 'wc_gc_giftcard_amount', $amount, true );
			}

			// Add custom code.
			if ( empty( $posted_form_fields['wc_gc_giftcard_code'] ) ) {
				$item->delete_meta_data( 'wc_gc_giftcard_code' );
			} else {
				$item->add_meta_data( 'wc_gc_giftcard_code', sanitize_text_field( $posted_form_fields['wc_gc_giftcard_code'] ), true );
			}

			if ( $item->save() ) {
				/* translators: %1$s: Product title, %2$d: Product ID */
				$order->add_order_note( sprintf( __( '%1s (#%2$d) configured.', 'woocommerce-gift-cards' ), $product->get_title(), $product->get_id() ), false, true );
			}
		}

		ob_start();
		include WC_ABSPATH . 'includes/admin/meta-boxes/views/html-order-items.php';
		$html = ob_get_clean();

		ob_start();
		$notes = wc_get_order_notes( array( 'order_id' => $order->get_id() ) );
		include WC_ABSPATH . 'includes/admin/meta-boxes/views/html-order-notes.php';
		$notes_html = ob_get_clean();
		$response   = array(
			'result'     => 'success',
			'html'       => $html,
			'notes_html' => $notes_html,
		);

		wp_send_json( $response );
	}

	/**
	 * Fetch gift card order item form.
	 *
	 * @since 1.3.0
	 *
	 * @return void
	 */
	public static function configure_order_item_gift_card() {

		global $product;

		$failure = array(
			'result' => 'failure',
		);

		if ( ! check_ajax_referer( 'wc_gc_edit_gift_card_order_item', 'security', false ) ) {
			wp_send_json( $failure );
		}

		if ( empty( $_POST['order_id'] ) || empty( $_POST['item_id'] ) ) {
			wp_send_json( $failure );
		}

		$order   = wc_get_order( wc_clean( $_POST['order_id'] ) );
		$item_id = absint( wc_clean( $_POST['item_id'] ) );

		if ( ! ( $order instanceof WC_Order ) ) {
			wp_send_json( $failure );
		}

		$item = $order->get_item( $item_id );

		if ( ! ( $item instanceof WC_Order_Item ) ) {
			wp_send_json( $failure );
		}

		$product = $item->get_product();
		if ( empty( $product ) ) {
			wp_send_json( $failure );
		}

		// Set mock configuration.
		$configuration = array();
		foreach ( WC_GC_Gift_Card_Product::get_form_fields() as $key => $label ) {
			$configuration[ $key ] = $item->get_meta( $key, true );
		}
		// Add custom code if needed.
		$configuration['wc_gc_giftcard_code'] = $item->get_meta( 'wc_gc_giftcard_code', true );

		// Set the super global $_REQUEST for templates compatibility.
		$_REQUEST = array_merge( $configuration, $_REQUEST );

		ob_start();
		include 'meta-boxes/views/html-gift-card-edit-form.php';
		$html = ob_get_clean();

		$response = array(
			'result' => 'success',
			'html'   => $html,
		);

		wp_send_json( $response );
	}

	/**
	 * Add gift card order item.
	 *
	 * @since 1.2.0
	 *
	 * @return void
	 */
	public static function add_order_item_gift_card() {
		check_ajax_referer( 'order-item', 'security' );

		try {

			$giftcard_code = isset( $_POST['giftcard'] ) ? wc_clean( $_POST['giftcard'] ) : false;

			// Validate GC input.
			if ( ! $giftcard_code ) {
				throw new Exception( __( 'Invalid gift card', 'woocommerce-gift-cards' ) );
			}

			$order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : false;
			$order    = wc_get_order( $order_id );

			// Validate Order.
			if ( ! $order ) {
				throw new Exception( __( 'Invalid order', 'woocommerce-gift-cards' ) );
			}

			if ( in_array( $order->get_status(), array( 'auto-draft', 'draft' ) ) ) {
				throw new Exception( __( 'Please calculate totals and save this order before applying a gift card code', 'woocommerce-gift-cards' ) );
			}

			if ( $order->get_total() <= 0 ) {
				throw new Exception( __( 'Please make sure that the order has a total to be paid', 'woocommerce-gift-cards' ) );
			}

			// Apply giftcard.
			// It's safe to ignore semgrep warning, as everything is properly escaped.
			$giftcard_data = WC_GC()->db->giftcards->query(
				array( // nosemgrep: audit.php.wp.security.sqli.input-in-sinks .
					'code'   => $giftcard_code,
					'return' => 'objects',
				)
			);// nosemgrep: audit.php.wp.security.sqli.input-in-sinks
			if ( empty( $giftcard_data ) ) {
				throw new Exception( __( 'Gift card not found', 'woocommerce-gift-cards' ) );
			}

			$giftcard = new WC_GC_Gift_Card( array_shift( $giftcard_data ) );

			if ( ! $giftcard->is_active() ) {
				throw new Exception( __( 'Gift card disabled.', 'woocommerce-gift-cards' ) );
			}

			if ( $giftcard->has_expired() ) {
				throw new Exception( __( 'Gift card expired.', 'woocommerce-gift-cards' ) );
			}

			if ( $giftcard->get_balance() == 0 ) {
				throw new Exception( __( 'Gift card has no remaining balance.', 'woocommerce-gift-cards' ) );
			}

			// Get existing gift cards.
			$giftcards = $order->get_items( 'gift_card' );
			if ( ! empty( $giftcards ) ) {
				foreach ( $giftcards as $giftcard_item ) {
					if ( $giftcard_item->get_code() === $giftcard->get_code() ) {
						throw new Exception( __( 'Gift card already exists in this order.', 'woocommerce-gift-cards' ) );
					}
				}
			}

			// Add GC order item.
			$item = new WC_GC_Order_Item_Gift_Card();
			$item->set_props(
				array(
					'giftcard_id' => $giftcard->get_id(),
					'code'        => $giftcard->get_code(),
					'amount'      => $order->get_total() > $giftcard->get_balance() ? $giftcard->get_balance() : $order->get_total(),
				)
			);
			$order->add_item( $item );
			/* translators: %s gift card code. */
			$order->add_order_note( sprintf( __( 'Applied gift card: <span class="woocommerce-giftcards-admin-note-code">%s</span>.', 'woocommerce-gift-cards' ), $giftcard->get_code() ), false, true );

			// Update balance.
			WC_GC()->order->maybe_debit_giftcards( $order_id, $order, array( $item ) );

			$order->update_taxes();
			$order->calculate_totals( false );

			/**
			 * `woocommerce_gc_gift_card_added_to_order` action.
			 *
			 * @since  1.6.0
			 *
			 * @param  WC_GC_Order_Item_Gift_Card  $item
			 * @param  WC_Order                    $order
			 */
			do_action( 'woocommerce_gc_gift_card_applied_to_order', $item, $order );

			// Get HTML to return.
			ob_start();
			include WC_ABSPATH . 'includes/admin/meta-boxes/views/html-order-items.php';
			$items_html = ob_get_clean();

			ob_start();
			$notes = wc_get_order_notes( array( 'order_id' => $order_id ) );
			include WC_ABSPATH . 'includes/admin/meta-boxes/views/html-order-notes.php';
			$notes_html = ob_get_clean();
			$response   = array(
				'result'     => 'success',
				'html'       => $items_html,
				'notes_html' => $notes_html,
			);

			wp_send_json( $response );

		} catch ( Exception $e ) {
			wp_send_json_error( array( 'error' => $e->getMessage() ) );
		}
	}

	/**
	 * Remove gift card order item.
	 *
	 * @since 1.2.0
	 *
	 * @return void
	 */
	public static function remove_order_item_gift_card() {
		check_ajax_referer( 'order-item', 'security' );

		if ( ! current_user_can( 'edit_shop_orders' ) || ! isset( $_POST['order_id'], $_POST['order_item_ids'] ) ) {
			wp_die( -1 );
		}

		$response = array();

		try {

			$order_id = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : false;
			$order    = wc_get_order( $order_id );
			$user     = wp_get_current_user();

			if ( ! $order ) {
				throw new Exception( __( 'Invalid order', 'woocommerce-gift-cards' ) );
			}

			if ( ! isset( $_POST['order_item_ids'] ) ) {
				throw new Exception( __( 'Invalid items', 'woocommerce-gift-cards' ) );
			}

			$order_item_ids = isset( $_POST['order_item_ids'] ) ? wp_unslash( $_POST['order_item_ids'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$items          = ( ! empty( $_POST['items'] ) ) ? wp_unslash( $_POST['items'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			if ( ! is_array( $order_item_ids ) && is_numeric( $order_item_ids ) ) {
				$order_item_ids = array( $order_item_ids );
			}

			// If we passed through items it means we need to save first before deleting.
			if ( ! empty( $items ) ) {
				$save_items = array();
				parse_str( $items, $save_items );
				wc_save_order_items( $order->get_id(), $save_items );
			}

			if ( ! empty( $order_item_ids ) ) {

				foreach ( $order_item_ids as $item_id ) {
					$item_id = absint( $item_id );
					$item    = $order->get_item( $item_id );

					if ( 'gift_card' === $item->get_type() ) {

						// Revert refunds.
						if ( 0 < $item->get_refunded_amount( 'db' ) ) {
							/* translators: gift card code */
							throw new Exception( sprintf( __( 'Failed to remove gift card: %s. Please reverse/remove all refunds to this gift card.', 'woocommerce-gift-cards' ), $item->get_code() ) );
						}

						// Credit GC balance.
						WC_GC()->order->maybe_credit_giftcards( $order->get_id(), $order, array( $item ) );
					}

					/* translators: %s gift card code. */
					$order->add_order_note( sprintf( __( 'Removed gift card: <span class="woocommerce-giftcards-admin-note-code">%s</span>.', 'woocommerce-gift-cards' ), $item->get_code() ), false, true );
					wc_delete_order_item( $item_id );

					/**
					 * `woocommerce_gc_gift_card_removed_from_order` action.
					 *
					 * @since  1.6.0
					 *
					 * @param  WC_GC_Order_Item_Gift_Card  $item
					 * @param  WC_Order                    $order
					 */
					do_action( 'woocommerce_gc_gift_card_removed_from_order', $item, $order );
				}
			}

			// Re-fetch.
			$order = wc_get_order( $order_id );
			$order->update_taxes();
			$order->calculate_totals( false );

			// Get HTML to return.
			ob_start();
			include WC_ABSPATH . 'includes/admin/meta-boxes/views/html-order-items.php';
			$items_html = ob_get_clean();

			ob_start();
			$notes = wc_get_order_notes( array( 'order_id' => $order_id ) );
			include WC_ABSPATH . 'includes/admin/meta-boxes/views/html-order-notes.php';
			$notes_html = ob_get_clean();
			$response   = array(
				'result'     => 'success',
				'html'       => $items_html,
				'notes_html' => $notes_html,
			);

			wp_send_json( $response );

		} catch ( Exception $e ) {
			wp_send_json_error( array( 'error' => $e->getMessage() ) );
		}
	}

	/*
	|--------------------------------------------------------------------------
	| Notices.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Dismisses notices.
	 *
	 * @return void
	 */
	public static function dismiss_notice() {

		$failure = array(
			'result' => 'failure',
		);

		if ( ! check_ajax_referer( 'wc_gc_dismiss_notice_nonce', 'security', false ) ) {
			wp_send_json( $failure );
		}

		if ( empty( $_POST['notice'] ) ) {
			wp_send_json( $failure );
		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json( $failure );
		}

		$dismissed = WC_GC_Admin_Notices::dismiss_notice( wc_clean( $_POST['notice'] ) );

		if ( ! $dismissed ) {
			wp_send_json( $failure );
		}

		$response = array(
			'result' => 'success',
		);

		wp_send_json( $response );
	}

	/**
	 * Checks if loopback requests work.
	 *
	 * @since  1.3.2
	 *
	 * @return void
	 */
	public static function ajax_loopback_test() {

		$failure = array(
			'result' => 'failure',
			'reason' => '',
		);

		if ( ! check_ajax_referer( 'wc_gc_loopback_notice_nonce', 'security', false ) ) {
			$failure['reason'] = 'nonce';
			wp_send_json( $failure );
		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			$failure['reason'] = 'user_role';
			wp_send_json( $failure );
		}

		if ( ! class_exists( 'WP_Site_Health' ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-site-health.php';
		}

		$site_health = method_exists( 'WP_Site_Health', 'get_instance' ) ? WP_Site_Health::get_instance() : new WP_Site_Health();
		$result      = $site_health->can_perform_loopback();
		$passes_test = 'good' === $result->status;

		WC_GC_Admin_Notices::set_notice_option( 'loopback', 'last_tested', gmdate( 'U' ) );
		WC_GC_Admin_Notices::set_notice_option( 'loopback', 'last_result', $passes_test ? 'pass' : 'fail' );

		if ( ! $passes_test ) {
			$failure['reason']  = 'status';
			$failure['status']  = $result->status;
			$failure['message'] = $result->message;
			wp_send_json( $failure );
		}

		WC_GC_Admin_Notices::remove_maintenance_notice( 'loopback' );

		$response = array(
			'result' => 'success',
		);

		wp_send_json( $response );
	}
}

WC_GC_Admin_Ajax::init();
