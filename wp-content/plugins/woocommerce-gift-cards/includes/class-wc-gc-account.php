<?php
/**
 * WC_GC_Account class
 *
 * @package  WooCommerce Gift Cards
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Account class.
 *
 * @class    WC_GC_Account
 * @version  2.6.0
 */
class WC_GC_Account {

	/**
	 * Cache query vars locally to avoid multiple get_option calls.
	 *
	 * @since 1.13.0
	 * @var array
	 */
	protected $query_vars = array();

	/**
	 * Hook.
	 */
	public function __construct() {

		if ( ! wc_gc_is_redeeming_enabled() ) {
			return;
		}

		// Add menu item.
		add_action( 'woocommerce_account_menu_items', array( $this, 'add_navigation_item' ) );

		// Add endpoint setting.
		add_action( 'woocommerce_settings_pages', array( $this, 'add_endpoint_setting' ) );

		// Form handler.
		add_action( 'template_redirect', array( $this, 'maybe_process_email_redeem' ) );
		add_action( 'template_redirect', array( $this, 'process_redeem' ) );

		// Invalidate session.
		// These should be removed in a future release.
		add_action( 'woocommerce_gc_gift_card_redeemed', array( $this, 'maybe_clear_caches' ) );
		add_action( 'woocommerce_gc_gift_card_debited', array( $this, 'maybe_clear_caches' ) );
		add_action( 'woocommerce_gc_gift_card_credited', array( $this, 'maybe_clear_caches' ) );

		// Invalidate session basic operations.
		add_action( 'woocommerce_gc_before_save_gift_card', array( $this, 'maybe_clear_caches' ) );
		add_action( 'woocommerce_gc_before_delete_gift_card', array( $this, 'maybe_clear_caches' ) );
		add_action( 'woocommerce_gc_before_add_pending_balance_tracking', array( $this, 'maybe_clear_caches' ) );
		add_action( 'woocommerce_gc_before_remove_pending_balance_tracking', array( $this, 'maybe_clear_caches' ) );
		add_action( 'wp_login', array( $this, 'maybe_clear_caches' ) );

		// Page.
		add_action( 'woocommerce_account_giftcards_endpoint', array( $this, 'render_page' ) );
		add_action( 'woocommerce_endpoint_giftcards_title', array( $this, 'get_endpoint_title' ), 10, 2 );
		add_action( 'woocommerce_get_query_vars', array( $this, 'add_query_var' ) );

		// My order table.
		add_filter( 'woocommerce_my_account_my_orders_column_order-status', array( $this, 'add_pending_balance_in_account_orders_list' ), 10, 2 );
		add_filter( 'template_redirect', array( $this, 'init_my_account_pending_orders' ) );
		add_filter( 'woocommerce_my_account_my_orders_query', array( $this, 'filter_my_account_pending_orders' ) );

		$this->init_query_vars();
	}

	/**
	 * Init query vars by loading options.
	 *
	 * @since 1.13.0
	 */
	public function init_query_vars() {
		$this->query_vars['giftcards'] = get_option( 'woocommerce_checkout_gc_giftcards_endpoint', 'giftcards' );
	}

	/**
	 * Whether or not a customer wants to use the balance.
	 *
	 * @return bool
	 */
	public function use_balance() {
		$use = WC()->session->get( '_wc_gc_use_balance', true );
		return $use;
	}

	/**
	 * Set the balance usage to on/off.
	 *
	 * @param  bool $use
	 * @return void
	 */
	public function set_balance_usage( $use ) {
		WC()->session->set( '_wc_gc_use_balance', (bool) $use );
	}

	/**
	 * Fetches all active giftcards for customer.
	 *
	 * @param  int $user_id
	 * @return array
	 */
	public function get_active_giftcards( $user_id = 0 ) {

		if ( empty( $user_id ) ) {
			$user_id = get_current_user_id();

			if ( ! $user_id ) {
				return array();
			}
		}

		$giftcards = WC_GC()->db->giftcards->get_active_giftcards( $user_id );
		return $giftcards;
	}

	/**
	 * Try to fetch from session active giftcards. If not, save for later use.
	 *
	 * @return array
	 */
	public function get_active_giftcards_from_session() {

		$use_cache = ! defined( 'WC_GC_DEBUG_ACCOUNT_CACHE' );
		// TODO: Add plugin version on the cache key
		$cache_key   = WC_Cache_Helper::get_transient_version( 'account_giftcards' ) . '_wc_gc_active_giftcards';
		$cached_data = $use_cache ? WC()->session->get( $cache_key, null ) : null;

		/**
		 * `woocommerce_gc_account_session_timeout_minutes` filter.
		 *
		 * How long (in minutes) should keep active giftcards cache before revalidating.
		 *
		 * @since  1.5.7
		 *
		 * @param  int
		 * @return int
		 */
		$session_timeout = absint( apply_filters( 'woocommerce_gc_account_session_timeout_minutes', 5 ) ) * 60;

		if ( empty( $cached_data ) || ! isset( $cached_data['active_giftcards'], $cached_data['timestamp'] ) || $cached_data['timestamp'] < time() - $session_timeout ) {

			$active_giftcards = $this->get_active_giftcards();
			$cached_data      = array(
				'active_giftcards' => $active_giftcards,
				'timestamp'        => time(),
			);

			// Save for later.
			WC()->session->set( $cache_key, $cached_data );
		}

		return $cached_data['active_giftcards'];
	}

	/**
	 * Clear caches.
	 *
	 * @return void
	 */
	public function clear_caches() {
		$cache_key = WC_Cache_Helper::get_transient_version( 'account_giftcards' ) . '_wc_gc_active_giftcards';
		WC()->session->set( $cache_key, null );
	}

	/**
	 * Clear caches.
	 *
	 * @return void
	 */
	public function maybe_clear_caches() {
		if ( isset( WC()->session ) && WC()->session->has_session() && ! is_admin() && ! defined( 'DOING_CRON' ) ) {

			// Clear current data only.
			$this->clear_caches();
		} else {

			// Force invalidate all data.
			WC_Cache_Helper::get_transient_version( 'account_giftcards', true );
			WC_Cache_Helper::get_transient_version( 'applied_giftcards', true );
		}
	}

	/**
	 * Whether or not the customer has balance.
	 *
	 * @return bool
	 */
	public function has_balance() {
		return $this->get_balance() > 0;
	}

	/**
	 * Retrieve balance for customer.
	 *
	 * @param  int        $user_id
	 * @param  array|null $giftcards
	 * @return float
	 */
	public function get_balance( $user_id = 0, $giftcards = null ) {

		$balance = 0.0;
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return $balance;
		}

		if ( is_null( $giftcards ) ) {
			$giftcards = $this->get_active_giftcards_from_session();
		}

		foreach ( $giftcards as $giftcard_data ) {
			$balance += (float) $giftcard_data->get_balance();
		}

		return $balance;
	}

	/**
	 * Proccess the email link redeeming if any.
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	public function maybe_process_email_redeem() {

		if ( ! empty( $_GET['do_email_redeem'] ) ) {

			if ( ! is_user_logged_in() ) {
				return;
			}

			$requested_giftcard = isset( $_GET['giftcard_id'] ) ? absint( $_GET['giftcard_id'] ) : 0;
			$hash               = ! empty( $_GET['do_email_redeem'] ) ? sanitize_text_field( wp_unslash( $_GET['do_email_redeem'] ) ) : '';
			$base_url           = remove_query_arg( array( 'do_email_redeem', 'giftcard_id' ) );

			// Check for backwards compatibility hashes lt 1.9.0.
			// Hint: Previous hash requests didn't include a 'giftcard_id' param.
			$is_legacy_request = empty( $requested_giftcard );

			// Validate gift card.
			if ( $is_legacy_request ) {

				$current_giftcard = WC_GC()->db->giftcards->get_by_hash( $hash );

				// If gift card has one or more new meta data and still uses the legacy way, then it's probably a "bad" or invalid link.
				if ( $current_giftcard->get_meta( '_hash_iv' ) || $current_giftcard->get_meta( '_hash_key' ) ) {
					// Reset for safety.
					$current_giftcard = false;
				}
			} else {

				$current_giftcard = wc_gc_get_gift_card( $requested_giftcard );
				$hash_to_check    = urldecode( base64_decode( $hash ) );
				if ( ! $current_giftcard || ! $current_giftcard->validate_hash( $hash_to_check ) ) {
					// Reset for safety.
					$current_giftcard = false;
				}
			}

			if ( ! is_a( $current_giftcard, 'WC_GC_Gift_Card_Data' ) ) {
				wc_add_notice( esc_html__( 'Invalid request. Please try again&hellip;', 'woocommerce-gift-cards' ), 'error' );
				wp_safe_redirect( $base_url );
				exit;
			}

			// Try to make brute-force attacks inefficient.
			sleep( 2 );

			// Decorate.
			$giftcard = new WC_GC_Gift_Card( $current_giftcard );
			try {

				$giftcard->redeem( get_current_user_id() );
				// Re-init cart giftcards.
				WC_GC()->cart->destroy_cart_session();
				wc_add_notice( __( 'The gift card has been added to your account.', 'woocommerce-gift-cards' ) );

			} catch ( Exception $e ) {
				wc_add_notice( $e->getMessage(), 'error' );
			}

			wp_safe_redirect( $base_url );
			exit;
		}
	}

	/**
	 * Proccess the FE redeem form.
	 *
	 * @param  array $items
	 * @return array
	 */
	public function process_redeem() {

		if ( ! empty( $_POST ) && ! empty( $_POST['wc_gc_redeem_save'] ) ) {

			if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( wc_clean( $_REQUEST['_wpnonce'] ), 'customer_redeems_gift_card' ) ) {
				wc_add_notice( __( 'Failed to link your gift card to your account. Please try again later, or get in touch with us for assistance.', 'woocommerce-gift-cards' ), 'error' );
				wp_safe_redirect( add_query_arg() );
				exit;
			}

			$code = ! empty( $_POST['wc_gc_redeem_code'] ) ? sanitize_text_field( wp_unslash( $_POST['wc_gc_redeem_code'] ) ) : '';

			if ( ! $code ) {
				wc_add_notice( __( 'Please enter a valid gift card code.', 'woocommerce-gift-cards' ), 'error' );
				wp_safe_redirect( add_query_arg( array() ) );
				exit;
			} elseif ( strlen( $code ) !== 19 ) {
				wc_add_notice( __( 'Please enter a gift card code that follows the format XXXX-XXXX-XXXX-XXXX, where X can be any letter or number.', 'woocommerce-gift-cards' ), 'error' );
				wp_safe_redirect( add_query_arg( array() ) );
				exit;
			}

			// It's safe to ignore semgrep warning, as everything is properly escaped.
			$gc_results = WC_GC()->db->giftcards->query(
				array( // nosemgrep: audit.php.wp.security.sqli.input-in-sinks .
					'return' => 'objects',
					'code'   => $code,
					'limit'  => 1,
				)
			);

			if ( count( $gc_results ) > 0 ) {

				$gc_data = array_shift( $gc_results );
				$gc      = new WC_GC_Gift_Card( $gc_data );

				try {

					$gc->redeem( get_current_user_id() );
					// Re-init cart giftcards.
					WC_GC()->cart->destroy_cart_session();
					wc_add_notice( __( 'The gift card has been added to your account.', 'woocommerce-gift-cards' ) );

				} catch ( Exception $e ) {
					wc_add_notice( $e->getMessage(), 'error' );
				}
			} else {
				wc_add_notice( __( 'Invalid gift card code.', 'woocommerce-gift-cards' ), 'error' );
			}

			wp_safe_redirect( add_query_arg( array() ) );
			exit;
		}
	}


	/**
	 * Add Gift Cards navigation item.
	 *
	 * @param  array $items
	 * @return array
	 */
	public function add_navigation_item( $items ) {

		// If the Gift Cards endpoint setting is empty, don't display it; in line with core WC behaviour.
		if ( empty( $this->query_vars['giftcards'] ) ) {
			return $items;
		}

		$after_menu_position = 3;
		$giftcards_menu_item = array( 'giftcards' => __( 'Gift Cards', 'woocommerce-gift-cards' ) );
		$items               = array_slice( $items, 0, $after_menu_position, true ) + $giftcards_menu_item + array_slice( $items, $after_menu_position, count( $items ) - $after_menu_position, true );

		return $items;
	}

	/**
	 * Add Gift Cards navigation item.
	 *
	 * @since 1.2.2
	 *
	 * @param  array $settings
	 * @return array
	 */
	public function add_endpoint_setting( $settings ) {

		// Find where is the id "account_endpoint_options" with type "sectionend".
		$counted_index = 0; // Settings array is not entirely zero-based.
		foreach ( $settings as $index => $setting ) {
			if ( isset( $setting['type'], $setting['id'] ) && 'sectionend' === $setting['type'] && 'account_endpoint_options' === $setting['id'] ) {
				$end_index = $index;
				break;
			}

			// Increase counted index.
			++$counted_index;
		}

		if ( isset( $end_index ) ) {

			$setting = array(
				'title'    => __( 'Gift cards', 'woocommerce-gift-cards' ),
				'desc'     => __( 'Endpoint for the "My account &rarr; Gift Cards" page.', 'woocommerce-gift-cards' ),
				'id'       => 'woocommerce_checkout_gc_giftcards_endpoint',
				'type'     => 'text',
				'default'  => 'giftcards',
				'desc_tip' => true,
			);

			$settings = array_slice( $settings, 0, $counted_index, true ) + array( 'giftcards' => $setting ) + array_slice( $settings, $counted_index, count( $settings ) - $counted_index, true );
		}

		return $settings;
	}

	/**
	 * Render page html.
	 *
	 * @param  int $current_page (Optional)
	 * @return void
	 */
	public function render_page( $current_page = 1 ) {

		// Page.
		$current_page = ! empty( $current_page ) ? absint( $current_page ) : 1;
		$per_page     = 5;

		// Giftcards.
		$giftcards     = $this->get_active_giftcards_from_session();
		$balance       = $this->get_balance();
		$has_giftcards = count( $giftcards ) > 0 ? true : false;

		// Get all redeemed gift cards by current user.
		$redeemed_query_args = array(
			'return'      => 'ids',
			'redeemed_by' => get_current_user_id(),
		);
		$giftcard_ids        = WC_GC()->db->giftcards->query( $redeemed_query_args );

		if ( ! empty( $giftcard_ids ) ) {
			// Activities.
			$query_args_activity = array(
				'return'   => 'objects',
				'type'     => array( 'redeemed', 'used', 'refunded', 'manually_refunded', 'refund_reversed' ),
				'gc_id'    => $giftcard_ids,
				'order_by' => array( 'date' => 'desc' ),
				'limit'    => $per_page,
				'offset'   => ( $current_page - 1 ) * $per_page,
			);

			$activities = WC_GC()->db->activity->query( $query_args_activity );
		} else {
			$activities = array();
		}

		$has_activities = count( $activities ) > 0 ? true : false;
		if ( $has_activities ) {
			// Count total items.
			$query_args_activity['count'] = true;
			unset( $query_args_activity['limit'] );
			unset( $query_args_activity['offset'] );
			$total_items = WC_GC()->db->activity->query( $query_args_activity );
			$total_pages = ceil( $total_items / $per_page );
		} else {
			$total_pages = 0;
		}

		$button_class = implode(
			' ',
			array_filter(
				array(
					'woocommerce-Button',
					'woocommerce-button',
					'button',
					wc_gc_wp_theme_get_element_class_name( 'button' ),
				)
			)
		);

		wc_get_template(
			'myaccount/giftcards.php',
			array(
				'current_page'   => $current_page,
				'total_pages'    => $total_pages,
				'has_giftcards'  => $has_giftcards,
				'giftcards'      => $giftcards,
				'activities'     => $activities,
				'has_activities' => $has_activities,
				'button_class'   => $button_class,
				'balance'        => $balance,
			),
			false,
			WC_GC()->get_plugin_path() . '/templates/'
		);
	}

	/**
	 * Get the endpoint page title.
	 *
	 * @param  string $title
	 * @param  string $endpoint
	 * @return string
	 */
	public function get_endpoint_title( $title, $endpoint ) {

		if ( 'giftcards' === $endpoint ) {
			$title = __( 'Gift Cards', 'woocommerce-gift-cards' );
		}

		return $title;
	}

	/**
	 * Add endpoint slug as query var.
	 *
	 * @param  array $query_vars
	 * @return array
	 */
	public function add_query_var( $query_vars ) {
		$query_vars['giftcards'] = $this->query_vars['giftcards'];
		return $query_vars;
	}

	/**
	 * Init action on my account orders page.
	 *
	 * @since 1.6.0
	 *
	 * @param  WC_Order $order
	 * @return void
	 */
	public function init_my_account_pending_orders( $query_args ) {

		if ( ! is_account_page() || ! isset( $_GET['wc_gc_show_pending_orders'] ) ) {
			return;
		}

		if ( 'yes' === $_GET['wc_gc_show_pending_orders'] ) {
			$button_class    = wc_gc_wp_theme_get_element_class_name( 'button' );
			$wp_button_class = $button_class ? ' ' . $button_class : '';
			$notice_text     = __( 'Currently viewing pending orders only.', 'woocommerce-gift-cards' );
			$notice          = sprintf( '<a href="%s" class="button wc-forward%s">%s</a> %s', wc_get_endpoint_url( 'orders' ), $wp_button_class, esc_html__( 'View all orders', 'woocommerce-gift-cards' ), $notice_text );
			wc_add_notice( $notice, 'notice' );
		}
	}

	/**
	 * Filters my account order to show only pending orders.
	 *
	 * @since 1.6.0
	 *
	 * @param  array $query_args
	 * @return array
	 */
	public function filter_my_account_pending_orders( $query_args ) {

		if ( ! is_account_page() || ! isset( $_GET['wc_gc_show_pending_orders'] ) ) {
			return $query_args;
		}

		if ( 'yes' === wc_clean( $_GET['wc_gc_show_pending_orders'] ) ) {
			$query_args['status'] = array_map( 'wc_gc_prefix_order_status', wc_gc_get_order_pending_statuses() );
		}

		return $query_args;
	}

	/**
	 * Adds an indicator of pending balances under the status column.
	 *
	 * @since 1.6.0
	 *
	 * @param  WC_Order $order
	 * @return void
	 */
	public function add_pending_balance_in_account_orders_list( $order ) {

		// Copy from original.
		echo esc_html( wc_get_order_status_name( $order->get_status() ) );

		if ( $order->has_status( wc_gc_get_order_pending_statuses() ) ) {

			$giftcard_items = $order->get_items( 'gift_card' );
			if ( $giftcard_items ) {
				$pending_balance = 0;
				foreach ( $giftcard_items as $order_item ) {
					$pending_balance += $order_item->get_amount();
				}

				// Append Gift Cards.
				echo '<small class="woocommerce-MyAccount-Giftcards-pending-amount"><span class="warning-icon"></span>'
					. wp_kses_post( sprintf( __( 'Gift card funds on hold: %s', 'woocommerce-gift-cards' ), wc_price( $pending_balance ) ) )
					. ' </small>';
			}
		}
	}
}
