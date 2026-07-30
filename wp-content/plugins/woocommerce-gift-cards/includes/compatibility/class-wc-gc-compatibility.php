<?php
/**
 * WC_GC_Compatibility class
 *
 * @package  WooCommerce Gift Cards
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles compatibility with other WC extensions.
 *
 * @class    WC_GC_Compatibility
 * @version  2.3.0
 */
class WC_GC_Compatibility {

	/**
	 * Min required plugin versions to check.
	 *
	 * @var array
	 */
	private static $required = array(
		'wc_stripe' => '4.0.0',
		'blocks'    => '7.0.0',
	);

	/**
	 * Keep track of cart totals recursive loops.
	 *
	 * @var int
	 */
	protected static $recursive_counter;

	/**
	 * Setup compatibility class.
	 */
	public static function init() {

		// Initialize.
		self::load_modules();
		self::$recursive_counter = 0;

		// Manage cart recursive loops.
		add_action( 'woocommerce_after_calculate_totals', array( __CLASS__, 'increase_cart_totals_recursive_counter' ), -999999, 1 );
		add_action( 'woocommerce_after_calculate_totals', array( __CLASS__, 'decrease_cart_totals_recursive_counter' ), 999999, 1 );
	}

	/**
	 * When starting the cart totals calculation increase the counter.
	 *
	 * @since  1.5.0
	 *
	 * @return void
	 */
	public static function increase_cart_totals_recursive_counter( $cart ) {
		++self::$recursive_counter;
	}

	/**
	 * When ending the cart totals calculation decrease the counter.
	 *
	 * @since  1.5.0
	 *
	 * @return void
	 */
	public static function decrease_cart_totals_recursive_counter( $cart ) {
		--self::$recursive_counter;
	}

	/**
	 * Detect if it's a cart totals recursive loop.
	 *
	 * @since  1.5.0
	 *
	 * @return void
	 */
	public static function has_cart_totals_loop() {
		if ( self::$recursive_counter > 1 ) {
			return true;
		}

		return false;
	}

	/**
	 * Initialize.
	 *
	 * @return void
	 */
	protected static function load_modules() {

		if ( is_admin() ) {
			// Check plugin min versions.
			add_action( 'admin_init', array( __CLASS__, 'add_compatibility_notices' ) );
		}

		// Include core compatibility class.
		self::core_includes();

		// Declare HPOS compatibility.
		add_action( 'before_woocommerce_init', array( __CLASS__, 'declare_hpos_compatibility' ) );

		// Declare Blocks compatibility.
		add_action( 'before_woocommerce_init', array( __CLASS__, 'declare_blocks_compatibility' ) );

		// Load modules.
		add_action( 'plugins_loaded', array( __CLASS__, 'module_includes' ), 100 );

		// Prevent initialization of deprecated mini-extensions usually loaded on 'plugins_loaded' -- 10.
		self::unload_modules();
	}

	/**
	 * Core compatibility functions.
	 *
	 * @return void
	 */
	public static function core_includes() {
		require_once WC_GC_ABSPATH . 'includes/compatibility/core/class-wc-gc-core-compatibility.php';
	}

	/**
	 * Declare HPOS( Custom Order tables) compatibility.
	 *
	 * @since 1.13.1
	 */
	public static function declare_hpos_compatibility() {

		if ( ! class_exists( 'Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			return;
		}

		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', WC_GC()->get_plugin_basename(), true );
	}

	/**
	 * Declare cart/checkout Blocks compatibility.
	 *
	 * @since 1.16.6
	 */
	public static function declare_blocks_compatibility() {

		if ( ! class_exists( 'Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			return;
		}

		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', WC_GC()->get_plugin_basename(), true );
	}

	/**
	 * Prevent deprecated mini-extensions from initializing.
	 *
	 * @return void
	 */
	protected static function unload_modules() {
		// Silence.
	}

	/**
	 * Load compatibility classes.
	 *
	 * @return void
	 */
	public static function module_includes() {

		$module_paths = array();

		// WooCommerce Cart/Checkout Blocks support.
		if ( class_exists( 'Automattic\WooCommerce\Blocks\Package' ) && version_compare( \Automattic\WooCommerce\Blocks\Package::get_version(), self::$required['blocks'] ) > 0 ) {
			$module_paths['blocks'] = WC_GC_ABSPATH . 'includes/compatibility/modules/class-wc-gc-blocks-compatibility.php';
		}

		// WC Subscriptions compatibility.
		if ( class_exists( 'WC_Subscriptions' ) ) {
			$module_paths['wc_subscriptions'] = 'modules/class-wc-gc-subscriptions-compatibility.php';
		}

		// WooCommerce PDF Invoices & Packing Slips by Ewout Fernhout support.
		if ( class_exists( 'WPO_WCPDF' ) ) {
			$module_paths['wpowcpdf'] = 'modules/class-wc-gc-wpowcpdf-compatibility.php';
		}

		// WooCommerce PIP.
		if ( class_exists( 'WC_PIP' ) ) {
			$module_paths['pip'] = 'modules/class-wc-gc-pip-compatibility.php';
		}

		// Stripe compatibility.
		if ( class_exists( 'WC_Stripe' ) && defined( 'WC_STRIPE_VERSION' ) && version_compare( WC_STRIPE_VERSION, self::$required['wc_stripe'] ) >= 0 ) {
			$module_paths['wc_stripe'] = 'modules/class-wc-gc-stripe-compatibility.php';
		}

		// WooCommerce Avatax.
		if ( class_exists( 'WC_AvaTax' ) ) {
			$module_paths['avatax'] = 'modules/class-wc-gc-avatax-compatibility.php';
		}

		// Elementor Pro compatibility.
		if ( class_exists( '\ElementorPro\Plugin' ) ) {
			$module_paths['elementor_pro'] = 'modules/class-wc-gc-elementor-compatibility.php';
		}

		// Flatsome compatibility.
		if ( function_exists( 'wc_is_active_theme' ) && wc_is_active_theme( 'flatsome' ) ) {
			$module_paths['flatsome'] = 'modules/class-wc-gc-fs-compatibility.php';
		}

		// WooPayments compatibility.
		if ( class_exists( 'WC_Payments' ) ) {
			$module_paths['wcpay'] = 'modules/class-wc-gc-wc-payments-compatibility.php';
		}

		// PayPal compatibility.
		if ( class_exists( '\WooCommerce\PayPalCommerce\PluginModule' ) ) {
			$module_paths['paypal'] = 'modules/class-wc-gc-paypal-compatibility.php';
		}

		/**
		 * 'woocommerce_gc_compatibility_modules' filter.
		 *
		 * Use this to filter the required compatibility modules.
		 *
		 * @since  1.0.0
		 * @param  array $module_paths
		 */
		$module_paths = apply_filters( 'woocommerce_gc_compatibility_modules', $module_paths );

		foreach ( $module_paths as $path ) {
			require_once $path;
		}
	}

	/**
	 * Checks versions of compatible/integrated/deprecated extensions.
	 *
	 * @return void
	 */
	public static function add_compatibility_notices() {

		// WC Stripe version check.
		if ( class_exists( 'WC_Stripe' ) ) {

			$required_version = self::$required['wc_stripe'];

			if ( ! defined( 'WC_STRIPE_VERSION' ) || version_compare( WC_STRIPE_VERSION, $required_version ) < 0 ) {

				$extension     = __( 'Stripe for WooCommerce', 'woocommerce-gift-cards' );
				$extension_url = 'https://woocommerce.com/products/stripe/';
				/* translators: %1$s: extension %2$s: extension url %3$s: version */
				$notice = sprintf( __( 'The installed version of <strong>%1$s</strong> is not supported by <strong>Gift Cards</strong>. Please update <a href="%2$s" target="_blank">%1$s</a> to version <strong>%3$s</strong> or higher.', 'woocommerce-gift-cards' ), $extension, $extension_url, $required_version );

				WC_GC_Admin_Notices::add_dismissible_notice(
					$notice,
					array(
						'dismiss_class' => 'wc_stripe_lt_' . $required_version,
						'type'          => 'warning',
					)
				);
			}
		}
	}

	/**
	 * Get the normalized total discount from gift cards to use in Level 3 data for Stripe.
	 *
	 * This is a simplified version from \WCPay\Internal\Service\Level3Service::get_data_from_order() and \WCPay\Internal\Service\Level3Service::process_item().
	 *
	 * @param  array    $gift_cards
	 * @param  WC_Order $order
	 * @param  callable $normalize_fn
	 *
	 * @return float
	 */
	protected static function get_level3_total_discount( $gift_cards, $order, $normalize_fn ) {
		// Calculate the gift card total.
		// This is a simplified version from \WCPay\Internal\Service\Level3Service::get_data_from_order() and \WCPay\Internal\Service\Level3Service::process_item().
		$currency = $order->get_currency();

		// Normalize the gift card amounts.
		// Create a callable and map it to the gift cards.
		$process_item_function = function ( $item ) use ( $currency, $normalize_fn ) {
			return $normalize_fn( $item->get_amount(), $currency );
		};

		return array_sum( array_map( $process_item_function, $gift_cards ) );
	}

	/**
	 * Apply gift card discount to Level 3 data for Stripe.
	 *
	 * @param  array    $level3_data
	 * @param  WC_Order $order
	 * @param  callable $normalize_fn
	 *
	 * @return array
	 */
	public static function apply_discount_to_level3_line_items( $level3_data, $order, $normalize_fn ) {

		// No gift cards, return early.
		$gift_cards = array_values( $order->get_items( 'gift_card' ) );

		if ( empty( $gift_cards ) ) {
			return $level3_data;
		}

		// Shorter text as there's a 26 character limit for product_description in the API.
		$gc_data_line_item_key = sprintf( 'WCGC %s', (string) $order->get_id() );

		$gc_total = self::get_level3_total_discount( $gift_cards, $order, $normalize_fn );

		// Updated line item data.
		$updated_line_item = (object) array(
			'unit_cost'           => 0,
			'quantity'            => 1,
			'tax_amount'          => 0,
			'discount_amount'     => $gc_total,
			'product_code'        => 1,
			'product_description' => $gc_data_line_item_key,
		);

		// In case there is already line item for gift cards, use it.
		if ( ! isset( $level3_data['line_items'] ) ) {
			$level3_data['line_items'] = array();
		}

		foreach ( $level3_data['line_items'] as $line_item_key => $line_item ) {
			if ( is_object( $line_item )
				&& isset( $line_item->product_description )
				&& $gc_data_line_item_key === $line_item->product_description ) {
				$level3_data['line_items'][ $line_item_key ] = $updated_line_item;
				return $level3_data;
			}
		}

		// Line item not found, create a new one.
		$level3_data['line_items'][]              = (object) array();
		$last_index                               = count( $level3_data['line_items'] ) - 1;
		$level3_data['line_items'][ $last_index ] = $updated_line_item;

		return $level3_data;
	}
}

WC_GC_Compatibility::init();
