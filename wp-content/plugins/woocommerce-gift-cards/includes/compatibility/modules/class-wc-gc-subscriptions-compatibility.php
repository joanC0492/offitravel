<?php
/**
 * WC_GC_WCS_Compatibility class
 *
 * @package  WooCommerce Gift Cards
 * @since    1.7.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC Subscriptions integration.
 *
 * @version 2.7.3
 */
class WC_GC_WCS_Compatibility {

	/**
	 * Init.
	 */
	public static function init() {

		if ( ! wc_gc_is_redeeming_enabled() ) {
			return;
		}

		// Localize params.
		add_filter( 'woocommerce_gc_front_end_params', array( __CLASS__, 'add_front_end_params' ) );

		add_action( 'woocommerce_checkout_subscription_created', array( __CLASS__, 'maybe_enable_on_subscription_creation' ), 10, 2 );
		add_filter( 'wcs_renewal_order_created', array( __CLASS__, 'renewal_order_created' ), 9999, 2 );

		add_action( 'woocommerce_admin_order_data_after_order_details', array( __CLASS__, 'add_giftcard_balance_setting' ) );
		add_action( 'woocommerce_process_shop_subscription_meta', array( __CLASS__, 'save_giftcard_balance_setting' ) );

		add_filter( 'woocommerce_subscription_settings', array( __CLASS__, 'add_global_giftcard_balance_settings' ), 20 );

		add_action( 'wcs_subscription_details_table_before_payment_method', array( __CLASS__, 'add_giftcard_balance_toggle' ) );
		add_filter( 'woocommerce_gc_front_end_params', array( __CLASS__, 'add_ajax_security_nonce' ) );
		add_action( 'wc_ajax_update_subscription_giftcards_toggle', array( __CLASS__, 'update_subscription_giftcards_toggle' ) );
		add_action( 'woocommerce_get_subscription_item_totals', array( __CLASS__, 'get_subscription_item_totals' ), 10, 2 );
	}

	/*
	---------------------------------------------------*/
	/*
		Lifecycle methods.                               */
	/*---------------------------------------------------*/

	/**
	 * Maybe enable gift card balance in newly created subscriptions.
	 *
	 * @param  WC_Subscription $subscription
	 * @param  WC_Order        $order
	 * @return void
	 */
	public static function maybe_enable_on_subscription_creation( $subscription, $order ) {

		if ( ! self::is_giftcard_balance_enabled() ) {
			return;
		}

		if ( ! self::is_giftcard_balance_toggle_enabled( $subscription ) ) {
			return;
		}

		if ( ! self::validate_payment_gateway_for_subscription( $subscription ) ) {
			return;
		}

		/**
		 * `woocommerce_gc_wcs_checkout_enable_giftcard_balance` filter.
		 *
		 * Enable the use of giftcard balance on renewals on the initial order.
		 *
		 * @since  1.7.0
		 *
		 * @param  bool             $enabled
		 * @param  WC_Subscription  $subscription
		 * @return bool
		 */
		if ( ! (bool) apply_filters( 'woocommerce_gc_wcs_checkout_enable_giftcard_balance', true, $subscription ) ) {
			return;
		}

		if ( WC_GC()->account->use_balance() ) {
			$subscription->add_meta_data( '_wc_gc_wcs_use_gifcard_balance', 'yes', true );
			$subscription->save();
		}
	}

	/**
	 * Handle gift card balance when a renewal order is created.
	 *
	 * @param  WC_Order        $renewal_order
	 * @param  WC_Subscription $subscription
	 * @return WC_Order
	 */
	public static function renewal_order_created( $renewal_order, $subscription ) {

		if ( ! is_a( $subscription, 'WC_Subscription' ) || ! is_a( $renewal_order, 'WC_Order' ) ) {
			return $renewal_order;
		}

		if ( 0 === $renewal_order->get_total() ) {
			return $renewal_order;
		}

		if ( ! self::is_giftcard_balance_enabled_for_subscription( $subscription ) || ! self::is_giftcard_balance_enabled() ) {
			return $renewal_order;
		}

		// Get applied giftcards or user id.
		$customer_id = $subscription->get_customer_id();
		$giftcards   = WC_GC()->account->get_active_giftcards( $customer_id );
		$balance     = WC_GC()->account->get_balance( $customer_id, $giftcards );

		if ( $balance < $renewal_order->get_total() && ! (bool) apply_filters( 'woocommerce_gc_wcs_renewals_allow_partial_payment', true ) ) {
			$subscription->add_order_note( __( 'Renewal payment with gift cards balance failed: Insufficient funds.', 'woocommerce-gift-cards' ), false, false );
			$subscription->save();
			return $renewal_order;
		}

		if ( 0 === $balance ) {
			$subscription->add_order_note( __( 'Renewal payment with gift cards balance failed: Zero balance.', 'woocommerce-gift-cards' ), false, false );
			$subscription->save();
			return $renewal_order;
		}

		$used_giftcards_data = WC_GC()->giftcards->cover_balance( $renewal_order->get_total(), $giftcards );
		$has_errors          = false;

		if ( ! empty( $used_giftcards_data['giftcards'] ) ) {
			foreach ( $used_giftcards_data['giftcards'] as $giftcard_info ) {

				// Re-fetch for clarity.
				$giftcard = new WC_GC_Gift_Card( $giftcard_info['giftcard']->get_id() );
				if ( ! is_a( $giftcard, 'WC_GC_Gift_Card' ) ) {
					continue;
				}

				// Sanity checks.
				$is_valid_giftcard = $giftcard->get_id() && $giftcard->is_active() && ! $giftcard->has_expired();
				$is_valid_balance  = $giftcard->get_balance() >= $giftcard_info['amount'];

				if ( $is_valid_giftcard && $is_valid_balance ) {

					$item = new WC_GC_Order_Item_Gift_Card();

					$item->set_props(
						array(
							'giftcard_id' => $giftcard->get_id(),
							'code'        => $giftcard->get_code(),
							'amount'      => (float) $giftcard_info['amount'],
						)
					);

					$renewal_order->add_item( $item );

				} else {
					$has_errors = true;
					/* translators: %1$s: Gift Card code %2$d: order ID */
					$subscription->add_order_note( sprintf( __( 'Failed to use gift card code <span class="woocommerce-giftcards-admin-note-code">%1$s</span> to pay for renewal order #%2$d.', 'woocommerce-gift-cards' ), $giftcard->get_code(), $renewal_order->get_id() ), false, false );
				}
			}
		}

		if ( ! $has_errors && ! empty( $used_giftcards_data['giftcards'] ) ) {
			/* translators: order ID */
			$subscription->add_order_note( sprintf( __( 'Used gift cards balance to pay for renewal order #%d.', 'woocommerce-gift-cards' ), $renewal_order->get_id() ), false, false );
		}

		$renewal_order->update_taxes();
		$renewal_order->calculate_totals( false );

		// Make sure that the order is processed if there is no balance to pay. This isn't handled when using the early renewal modal.
		if ( 0 == $renewal_order->get_total() ) {
			$new_status = $renewal_order->needs_processing() ? 'processing' : 'completed';
			$renewal_order->set_status( $new_status );
		}

		// Persist changes.
		$renewal_order->save();
		$subscription->save();

		return $renewal_order;
	}

	/*
	---------------------------------------------------*/
	/*
		View subscription in account.                    */
	/*---------------------------------------------------*/

	/**
	 * Display balance toggle in View subscription account page.
	 *
	 * @param  WC_Subscription $subscription
	 * @return void
	 */
	public static function add_giftcard_balance_toggle( $subscription ) {

		if ( ! self::is_giftcard_balance_enabled() ) {
			return;
		}

		if ( ! self::is_giftcard_balance_toggle_enabled( $subscription ) ) {
			return;
		}

		if ( ! self::validate_payment_gateway_for_subscription( $subscription ) ) {
			return;
		}

		if ( true === $subscription->can_be_updated_to( 'active' ) ) {
			return;
		}

		$current_setting = $subscription->get_meta( '_wc_gc_wcs_use_gifcard_balance', 'no' );
		ob_start();
		?>
		<tr>
			<td><?php esc_html_e( 'Pay with gift cards balance', 'woocommerce-gift-cards' ); ?></td>
			<td>
				<div class="wcs-gift-cards-toggle">
					<?php

					$toggle_classes      = array( 'subscription-auto-renew-toggle', 'subscription-auto-renew-toggle--hidden' );
					$use_balance_enabled = 'yes' === $current_setting;
					$is_duplicate_site   = false;

					if ( $use_balance_enabled ) {
						$toggle_classes[] = 'subscription-auto-renew-toggle--on';
					} else {
						$toggle_classes[] = 'subscription-auto-renew-toggle--off';

						if ( version_compare( WC_Subscriptions::$version, '4.0.0', '<' ) ) {
							$is_duplicate_site = (bool) WC_Subscriptions::is_duplicate_site();
						} else {
							$is_duplicate_site = (bool) WCS_Staging::is_duplicate_site();
						}
					}

					if ( $is_duplicate_site ) {
						$toggle_classes[] = 'subscription-auto-renew-toggle--disabled';
					}
					?>
					<button
						type="button"
						role="switch"
						aria-checked="<?php echo $use_balance_enabled ? 'true' : 'false'; ?>"
						aria-label="<?php esc_attr_e( 'Use gift cards balance to pay for renewals', 'woocommerce-gift-cards' ); ?>"
						class="<?php echo esc_attr( implode( ' ', $toggle_classes ) ); ?>"
						<?php disabled( $is_duplicate_site ); ?>
					><i class="subscription-auto-renew-toggle__i" aria-hidden="true"></i></button>
				</div>
			</td>
		</tr>
		<?php
		$html = ob_get_clean();
		echo wp_kses_post( $html );
	}

	/**
	 * Handle balance toggle AJAX action.
	 *
	 * @return void
	 */
	public static function update_subscription_giftcards_toggle() {
		check_ajax_referer( 'update-use-balance-renewals', 'security' );

		$failure = array(
			'result' => 'failure',
		);

		if ( ! self::is_giftcard_balance_enabled() ) {
			wp_send_json( $failure );
		}

		$subscription_id = isset( $_POST['subscription_id'] ) ? absint( $_POST['subscription_id'] ) : false;
		if ( ! $subscription_id ) {
			wp_send_json( $failure );
		}

		// Get subscription object.
		$subscription = wcs_get_subscription( $subscription_id );
		if ( ! is_a( $subscription, 'WC_Subscription' ) ) {
			wp_send_json( $failure );
		}

		$current_setting = $subscription->get_meta( '_wc_gc_wcs_use_gifcard_balance', 'no' );

		// Toggle.
		$setting = 'no' === $current_setting ? 'yes' : 'no';
		$subscription->update_meta_data( '_wc_gc_wcs_use_gifcard_balance', $setting );

		// Add subscription note.
		if ( 'yes' === $setting ) {
			$subscription->add_order_note( __( 'Customer enabled renewal payments with gift cards balance.', 'woocommerce-gift-cards' ), false, true );
		} else {
			$subscription->add_order_note( __( 'Customer disabled renewal payments with gift cards balance.', 'woocommerce-gift-cards' ), false, true );
		}

		// Save changes.
		$subscription->save();

		$response = array(
			'result' => 'success',
		);

		wp_send_json( $response );
	}

	/**
	 * Add a security nonce for the balance toggle.
	 *
	 * @param  array $params
	 * @return array
	 */
	public static function add_ajax_security_nonce( $params ) {
		$params['security_use_balance_renewals_nonce'] = wp_create_nonce( 'update-use-balance-renewals' );
		return $params;
	}

	/**
	 * Add front end params.
	 *
	 * @param  array $params
	 * @return array
	 */
	public static function add_front_end_params( $params ) {
		$params['i18n_force_reload_on_changes_notice'] = __( 'The payment settings of this subscription have changed. Please refresh the page before triggering an early renewal.', 'woocommerce-gift-cards' );
		$params['is_early_renewal_via_modal_enabled']  = WCS_Early_Renewal_Manager::is_early_renewal_via_modal_enabled() ? 'yes' : 'no';

		return $params;
	}

	/**
	 * Handle early renewal modal totals table.
	 *
	 * @param  array           $total_rows
	 * @param  WC_Subscription $subscription
	 * @return array
	 */
	public static function get_subscription_item_totals( $total_rows, $subscription ) {

		if ( 0 === $subscription->get_total() ) {
			return $total_rows;
		}

		if ( ! self::is_giftcard_balance_enabled_for_subscription( $subscription ) || ! self::is_giftcard_balance_enabled() ) {
			return $total_rows;
		}

		// Build this array based on balance predictions.
		$giftcards = array();

		// Get applied giftcards or user id.
		$customer_id = $subscription->get_customer_id();
		$giftcards   = WC_GC()->account->get_active_giftcards( $customer_id );
		$balance     = WC_GC()->account->get_balance( $customer_id, $giftcards );

		if ( 0 === $balance ) {
			return $total_rows;
		}

		if ( $balance < $subscription->get_total() && ! (bool) apply_filters( 'woocommerce_gc_wcs_renewals_allow_partial_payment', true ) ) {
			return $total_rows;
		}

		$balance_to_show      = min( $balance, $subscription->get_total() );
		$before_giftcards_row = array(
			'label' => esc_html__( 'Total', 'woocommerce-gift-cards' ) . ' ' . esc_html__( '(before Gift Cards)', 'woocommerce-gift-cards' ) . ':',
			'value' => wc_price( WC_GC()->order->get_order_total( $subscription ), array( 'currency' => $subscription->get_currency() ) ),
		);

		$giftcards_row = array(
			'label' => esc_html__( 'Gift Card balance:', 'woocommerce-gift-cards' ),
			'value' => wc_price( $balance_to_show * -1, array( 'currency' => $subscription->get_currency() ) ),
		);

		// Inject before Total.
		$total_index = array_search( 'order_total', array_keys( $total_rows ) );
		if ( false !== $total_index ) {

			// Modify subscription total.
			$total_rows['order_total']['value'] = wc_price( $balance_to_show - $subscription->get_total(), array( 'currency' => $subscription->get_currency() ) );

			$total_rows = array_slice( $total_rows, 0, $total_index, true ) + array( 'before_giftcards' => $before_giftcards_row ) + array( 'giftcards' => $giftcards_row ) + array_slice( $total_rows, $total_index, count( $total_rows ) - $total_index, true );
		} else {
			$total_rows['before_giftcards'] = $before_giftcards_row;
			$total_rows['giftcards']        = $giftcards_row;
		}

		return $total_rows;
	}

	/*
	---------------------------------------------------*/
	/*
		Admin edit subscription screen.                  */
	/*---------------------------------------------------*/

	/**
	 * Add gift card balance option in admin view.
	 *
	 * @param  WC_Subscription $subscription
	 * @return void
	 */
	public static function add_giftcard_balance_setting( $subscription ) {

		if ( ! is_a( $subscription, 'WC_Order' ) ) {
			return;
		}

		if ( ! self::is_giftcard_balance_enabled() || ! self::validate_payment_gateway_for_subscription( $subscription ) ) {
			return;
		}

		$setting = 'yes' === $subscription->get_meta( '_wc_gc_wcs_use_gifcard_balance', 'no' ) ? 'yes' : 'no';
		ob_start();
		?>
		<p class="form-field form-field-wide">
			<label for="wc_gc_wcs_use_gifcard_balance"><?php esc_html_e( 'Use gift cards balance for renewal payments:', 'woocommerce-gift-cards' ); ?> </label>
			<select id="wc_gc_wcs_use_gifcard_balance" name="wc_gc_wcs_use_gifcard_balance" class="wc-enhanced-select">
				<option value="no" <?php selected( 'no', $setting ); ?>><?php esc_html_e( 'No', 'woocommerce-gift-cards' ); ?></option>
				<option value="yes" <?php selected( 'yes', $setting ); ?>><?php esc_html_e( 'Yes', 'woocommerce-gift-cards' ); ?></option>
			</select>
		</p>
		<?php
		$html = ob_get_clean();
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Save giftcards balance usage.
	 *
	 * @param  int $post_id
	 * @return void
	 */
	public static function save_giftcard_balance_setting( $post_id ) {

		// Get subscription object.
		$subscription = wcs_get_subscription( $post_id );
		if ( ! is_a( $subscription, 'WC_Subscription' ) ) {
			return;
		}

		$current_setting = $subscription->get_meta( '_wc_gc_wcs_use_gifcard_balance', 'no' );
		$setting         = isset( $_POST['wc_gc_wcs_use_gifcard_balance'] ) && 'yes' === wc_clean( $_POST['wc_gc_wcs_use_gifcard_balance'] ) ? 'yes' : 'no';

		// Log and save.
		if ( $current_setting !== $setting ) {
			$subscription->update_meta_data( '_wc_gc_wcs_use_gifcard_balance', $setting );
			if ( 'yes' === $setting ) {
				$subscription->add_order_note( __( 'Enabled renewal payments with gift cards balance.', 'woocommerce-gift-cards' ), false, true );
			} else {
				$subscription->add_order_note( __( 'Disabled renewal payments with gift cards balance.', 'woocommerce-gift-cards' ), false, true );
			}

			// Save changes.
			$subscription->save();
		}
	}

	/*
	---------------------------------------------------*/
	/*
		Global settings.                                  */
	/*---------------------------------------------------*/

	/**
	 * Add a setting to allow store managers to enable or disable the gift card balance toggle.
	 *
	 * @param  array $settings
	 * @return array
	 */
	public static function add_global_giftcard_balance_settings( $settings ) {

		WC_Subscriptions_Admin::insert_setting_after(
			$settings,
			'woocommerce_subscriptions_enable_auto_renewal_toggle',
			array(
				'id'       => WC_Subscriptions_Admin::$option_prefix . '_allow_gift_card_balance_renewal_payments',
				'name'     => __( 'Allow Renewal Payments With Gift Cards Balance', 'woocommerce-gift-cards' ),
				'desc'     => __( 'Let customers pay for renewals using their Gift Cards account balance', 'woocommerce-gift-cards' ),
				'desc_tip' => __( 'Displays a &quot;Pay with gift cards balance&quot; toggle that customers can turn on and off from their View Subscription page.', 'woocommerce-gift-cards' ),
				'default'  => 'no',
				'type'     => 'checkbox',
				'class'    => 'wc_gc_wcs_gifcard_balance_field wc_gc_wcs_allow_balance_on_renewals_field',
			)
		);

		return $settings;
	}

	/*
	---------------------------------------------------*/
	/*
		Utilities.                                       */
	/*---------------------------------------------------*/

	/**
	 * Whether or not the giftcards are enabled for this subscription order.
	 *
	 * @param  WC_Subscription $subscription
	 * @return bool
	 */
	public static function is_giftcard_balance_enabled() {
		return 'yes' === get_option( WC_Subscriptions_Admin::$option_prefix . '_allow_gift_card_balance_renewal_payments', 'no' );
	}

	/**
	 * Whether or not to use giftcards balance for this subscription renewals.
	 *
	 * @param  WC_Subscription $subscription
	 * @return bool
	 */
	public static function is_giftcard_balance_enabled_for_subscription( $subscription ) {

		if ( ! is_a( $subscription, 'WC_Subscription' ) ) {
			return false;
		}

		$is_enabled = false;
		if ( 'yes' === $subscription->get_meta( '_wc_gc_wcs_use_gifcard_balance', 'no' ) ) {
			$is_enabled = true;
		}

		return $is_enabled;
	}

	/**
	 * Whether or not to use giftcards balance for this subscription renewals.
	 *
	 * @param  WC_Subscription $subscription
	 * @return bool
	 */
	public static function validate_payment_gateway_for_subscription( $subscription ) {

		if ( ! is_a( $subscription, 'WC_Subscription' ) ) {
			return false;
		}

		$is_valid               = true;
		$support_amount_changes = $subscription->payment_method_supports( 'subscription_amount_changes' );
		if ( ! $support_amount_changes ) {
			$is_valid = false;
		}

		return $is_valid;
	}

	/**
	 * Whether or not the giftcards are enabled for this subscription order.
	 *
	 * @param  WC_Subscription $subscription
	 * @return bool
	 */
	public static function is_giftcard_balance_toggle_enabled( $subscription ) {

		/**
		 * `woocommerce_gc_wcs_enable_giftcard_balance_toggle` filter.
		 *
		 * Whether or not to show the giftcard balance toggle in Account > View subscription.
		 *
		 * @since  1.7.0
		 *
		 * @param  bool             $show
		 * @param  WC_Subscription  $subscription
		 * @return bool
		 */
		return (bool) apply_filters( 'woocommerce_gc_wcs_enable_giftcard_balance_toggle', true, $subscription );
	}
}

WC_GC_WCS_Compatibility::init();
