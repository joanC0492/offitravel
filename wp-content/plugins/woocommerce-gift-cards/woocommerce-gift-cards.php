<?php
/*
* Plugin Name: WooCommerce Gift Cards
* Plugin URI: https://woocommerce.com/products/gift-cards/
* Description: Create and sell digital gift cards that customers can redeem at your store.
* Version: 2.7.4
* Author: WooCommerce
* Author URI: https://woocommerce.com/
*
* Woo: 5571998:ef39f1b1dfb2c215f40fa963c0ae971c
*
* Text Domain: woocommerce-gift-cards
* Domain Path: /languages/
*
* Requires PHP: 7.4
*
* Requires at least: 6.2
* Tested up to: 7.0
*
* WC requires at least: 8.2
* WC tested up to: 10.9
*
* Requires Plugins: woocommerce
*
* License: GNU General Public License v3.0
* License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin class.
 *
 * @class    WC_Gift_Cards
 * @version  2.7.4
 */
class WC_Gift_Cards {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	public $version = '2.7.4';

	/**
	 * Min required WC version.
	 *
	 * @var string
	 */
	private $wc_min_version = '8.2.0';

	/**
	 * The DB helper.
	 *
	 * @var WC_GC_DB
	 */
	public $db;

	/**
	 * Gift Cards Controller.
	 *
	 * @var WC_GC_Gift_Cards
	 */
	public $giftcards;

	/**
	 * Cart Controller.
	 *
	 * @var WC_GC_Cart
	 */
	public $cart;

	/**
	 * Order Controller.
	 *
	 * @var WC_GC_Order
	 */
	public $order;

	/**
	 * Templates Controller.
	 *
	 * @var WC_GC_Templates
	 */
	public $templates;

	/**
	 * Account Controller.
	 *
	 * @var WC_GC_Account
	 */
	public $account;

	/**
	 * Emails Controller.
	 *
	 * @var WC_GC_Emails
	 */
	public $emails;

	/**
	 * The single instance of the class.
	 *
	 * @var WC_Gift_Cards
	 */
	protected static $_instance = null;

	/**
	 * Main WC_Gift_Cards instance. Ensures only one instance is loaded or can be loaded - @see 'WC_GC()'.
	 *
	 * @static
	 * @return  WC_Gift_Cards
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Foul!', 'woocommerce-gift-cards' ), '1.0.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Foul!', 'woocommerce-gift-cards' ), '1.0.0' );
	}

	/**
	 * Make stuff.
	 */
	protected function __construct() {
		// Entry point.
		add_action( 'plugins_loaded', array( $this, 'initialize_plugin' ), 9 );
	}

	/**
	 * Plugin URL getter.
	 *
	 * @return string
	 */
	public function get_plugin_url() {
		return untrailingslashit( plugins_url( '/', __FILE__ ) );
	}

	/**
	 * Plugin path getter.
	 *
	 * @return string
	 */
	public function get_plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

	/**
	 * Plugin base path name getter.
	 *
	 * @return string
	 */
	public function get_plugin_basename() {
		return plugin_basename( __FILE__ );
	}

	/**
	 * Plugin version getter.
	 *
	 * @param  boolean $base
	 * @param  string  $version
	 * @return string
	 */
	public function get_plugin_version( $base = false, $version = '' ) {

		$version = $version ? $version : $this->version;

		if ( $base ) {
			$version_parts = explode( '-', $version );
			$version       = count( $version_parts ) > 1 ? $version_parts[0] : $version;
		}

		return $version;
	}

	/**
	 * Define constants if not present.
	 *
	 * @since  1.1.0
	 *
	 * @return boolean
	 */
	protected function maybe_define_constant( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Indicates whether the plugin is fully initialized.
	 *
	 * @since  1.1.0
	 *
	 * @return boolean
	 */
	public function is_plugin_initialized() {
		return isset( WC_GC()->giftcards );
	}

	/**
	 * Fire in the hole!
	 */
	public function initialize_plugin() {

		$this->define_constants();
		$this->maybe_create_store();

		// WC version sanity check.
		if ( ! function_exists( 'WC' ) || version_compare( WC()->version, $this->wc_min_version ) < 0 ) {
			/* translators: %s: WC min version */
			$notice = sprintf( __( 'WooCommerce Gift Cards requires at least WooCommerce <strong>%s</strong>.', 'woocommerce-gift-cards' ), $this->wc_min_version );
			require_once WC_GC_ABSPATH . 'includes/admin/class-wc-gc-admin-notices.php';
			WC_GC_Admin_Notices::add_notice( $notice, 'error' );

			return false;
		}

		// PHP version check.
		if ( ! function_exists( 'phpversion' ) || version_compare( phpversion(), '7.4.0', '<' ) ) {
			/* translators: %1$s: Version %, %2$s: Update PHP doc URL */
			$notice = sprintf(
				__(
					'WooCommerce Gift Cards requires at least PHP <strong>%1$s</strong>. Learn <a href="%2$s">how to update PHP</a>.',
					'woocommerce-gift-cards'
				),
				'7.4.0',
				$this->get_resource_url( 'update-php' )
			);
			require_once WC_GC_ABSPATH . 'includes/admin/class-wc-gc-admin-notices.php';
			WC_GC_Admin_Notices::add_notice( $notice, 'error' );
		}

		$this->includes();

		// Instantiate global singletons.
		$this->db        = new WC_GC_DB();
		$this->giftcards = new WC_GC_Gift_Cards();
		$this->cart      = new WC_GC_Cart();
		$this->order     = new WC_GC_Order();
		$this->templates = new WC_GC_Templates();
		$this->account   = new WC_GC_Account();
		$this->emails    = new WC_GC_Emails();

		WC_GC_Modules::instance();

		// Load translations hook.
		add_action( 'init', array( $this, 'load_translation' ) );
		// Init Shortcodes.
		add_action( 'init', array( 'WC_GC_Shortcodes', 'init' ) );
	}

	/**
	 * Constants.
	 */
	public function define_constants() {
		$this->maybe_define_constant( 'WC_GC_VERSION', $this->version );
		$this->maybe_define_constant( 'WC_GC_SUPPORT_URL', 'https://woocommerce.com/my-account/marketplace-ticket-form/' );
		$this->maybe_define_constant( 'WC_GC_ABSPATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
	}

	/**
	 * A simple dumb datastore for sharing information accross our plugins.
	 *
	 * @since  1.3.2
	 *
	 * @return void
	 */
	private function maybe_create_store() {
		if ( ! isset( $GLOBALS['sw_store'] ) ) {
			$GLOBALS['sw_store'] = array();
		}
	}

	/**
	 * Includes.
	 */
	public function includes() {

		// Functions.
		require_once WC_GC_ABSPATH . 'includes/wc-gc-functions.php';
		require_once WC_GC_ABSPATH . 'includes/wc-gc-order-functions.php';

		// Install and DB.
		require_once WC_GC_ABSPATH . 'includes/class-wc-gc-install.php';
		require_once WC_GC_ABSPATH . 'includes/db/class-wc-gc-db.php';
		require_once WC_GC_ABSPATH . 'includes/db/class-wc-gc-gift-cards-db.php';
		require_once WC_GC_ABSPATH . 'includes/db/class-wc-gc-activity-db.php';

		// Compatibility.
		require_once WC_GC_ABSPATH . 'includes/compatibility/class-wc-gc-compatibility.php';

		// Modules.
		require_once WC_GC_ABSPATH . 'includes/modules/class-wc-gc-modules.php';

		// Models.
		require_once WC_GC_ABSPATH . 'includes/data-stores/class-wc-gc-gift-card-data.php';
		require_once WC_GC_ABSPATH . 'includes/data-stores/class-wc-gc-activity-data.php';
		require_once WC_GC_ABSPATH . 'includes/data-stores/class-wc-gc-order-item-gift-card-data-store.php';
		require_once WC_GC_ABSPATH . 'includes/class-wc-gc-order-item-gift-card.php';

		// Controllers.
		require_once WC_GC_ABSPATH . 'includes/class-wc-gc-notices.php';
		require_once WC_GC_ABSPATH . 'includes/class-wc-gc-gift-card-product.php';
		require_once WC_GC_ABSPATH . 'includes/class-wc-gc-gift-card.php';
		require_once WC_GC_ABSPATH . 'includes/class-wc-gc-gift-cards.php';
		require_once WC_GC_ABSPATH . 'includes/class-wc-gc-cart.php';
		require_once WC_GC_ABSPATH . 'includes/class-wc-gc-order.php';
		require_once WC_GC_ABSPATH . 'includes/class-wc-gc-refunds.php';

		// Tracking.
		require_once WC_GC_ABSPATH . 'includes/class-wc-gc-tracker.php';
		require_once WC_GC_ABSPATH . 'includes/class-wc-gc-tracks.php';

		// Templates.
		require_once WC_GC_ABSPATH . 'includes/class-wc-gc-templates.php';

		// Front-end AJAX handlers.
		require_once WC_GC_ABSPATH . 'includes/class-wc-gc-ajax.php';

		// Account.
		require_once WC_GC_ABSPATH . 'includes/class-wc-gc-account.php';

		// Emails.
		require_once WC_GC_ABSPATH . 'includes/class-wc-gc-emails.php';
		require_once WC_GC_ABSPATH . 'includes/email-templates/class-wc-gc-abstract-email-template.php';
		require_once WC_GC_ABSPATH . 'includes/email-templates/class-wc-gc-email-template-default.php';

		// Analytics.
		require_once WC_GC_ABSPATH . 'includes/admin/analytics/class-wc-gc-admin-analytics.php';
		// Shortcodes.
		require_once WC_GC_ABSPATH . 'includes/class-wc-gc-shortcodes.php';

		// REST API hooks.
		require_once WC_GC_ABSPATH . 'includes/class-wc-gc-rest-api.php';

		// Abilities API hooks.
		require_once WC_GC_ABSPATH . 'includes/class-wc-gc-abilities.php';

		// Admin includes.
		if ( is_admin() ) {
			$this->admin_includes();
		}

		// WP-CLI includes.
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			require_once WC_GC_ABSPATH . 'includes/class-wc-gc-cli.php';
		}

		// REST API includes.
		require_once WC_GC_ABSPATH . 'includes/class-wc-gc-rest-api.php';
	}

	/**
	 * Admin & AJAX functions and hooks.
	 */
	public function admin_includes() {

		// Admin notices handling.
		require_once WC_GC_ABSPATH . 'includes/admin/class-wc-gc-admin-notices.php';

		// Admin functions and hooks.
		require_once WC_GC_ABSPATH . 'includes/admin/class-wc-gc-admin.php';
		require_once WC_GC_ABSPATH . 'includes/admin/class-wc-gc-admin-gift-cards-page.php';
		require_once WC_GC_ABSPATH . 'includes/admin/class-wc-gc-admin-activity-page.php';

		// List Tables.
		require_once WC_GC_ABSPATH . 'includes/admin/list-tables/class-wc-gc-admin-list-table-gift-cards.php';
		require_once WC_GC_ABSPATH . 'includes/admin/list-tables/class-wc-gc-admin-list-table-activity.php';
	}

	/**
	 * Load textdomain.
	 */
	public function load_translation() {
		load_plugin_textdomain( 'woocommerce-gift-cards', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		// Subscribe to automated translations.
		add_filter( 'woocommerce_translations_updates_for_' . basename( __FILE__, '.php' ), '__return_true' );
	}

	/**
	 * Log using 'WC_Logger' class.
	 *
	 * @param  string $message
	 * @param  string $level
	 * @param  string $context
	 */
	public function log( $message, $level, $context ) {
		$logger = wc_get_logger();
		$logger->log( $level, $message, array( 'source' => $context ) );
	}

	/**
	 * Handle plugin activation process.
	 *
	 * @since  1.12.0
	 *
	 * @return void
	 */
	public function on_activation() {
		// Add daily maintenance process.
		if ( ! wp_next_scheduled( 'wc_gc_daily' ) ) {
			wp_schedule_event( time() + 10, 'daily', 'wc_gc_daily' );
		}

		// Add hourly maintenance process.
		if ( ! wp_next_scheduled( 'wc_gc_hourly' ) ) {
			wp_schedule_event( time() + 10, 'hourly', 'wc_gc_hourly' );
		}
	}

	/**
	 * Handle plugin deactivation process.
	 *
	 * @since  1.12.0
	 *
	 * @return void
	 */
	public function on_deactivation() {
		// Clear daily maintenance process.
		wp_clear_scheduled_hook( 'wc_gc_daily' );

		// Clear hourly maintenance process.
		wp_clear_scheduled_hook( 'wc_gc_hourly' );
	}

	/**
	 * Get screen ids.
	 */
	public function get_screen_ids() {
		$screens = array();

		if ( 'marketing' === wc_gc_get_parent_menu() ) {
			$prefix = sanitize_title( __( 'Marketing', 'woocommerce' ) );
		} elseif ( version_compare( WC()->version, '7.3.0' ) < 0 ) {
				$prefix = sanitize_title( __( 'WooCommerce', 'woocommerce' ) );
		} else {
			$prefix = 'woocommerce';
		}

		$screens[] = $prefix . '_page_gc_activity';
		$screens[] = $prefix . '_page_gc_giftcards';

		return $screens;
	}

	/**
	 * Checks if the current admin screen belongs to extension.
	 *
	 * @param   array $extra_screens_to_check (Optional)
	 * @return  bool
	 */
	public function is_current_screen( $extra_screens_to_check = array() ) {

		global $current_screen;

		$screen_id = $current_screen ? $current_screen->id : '';

		if ( in_array( $screen_id, $this->get_screen_ids(), true ) ) {
			return true;
		}

		if ( ! empty( $extra_screens_to_check ) && in_array( $screen_id, $extra_screens_to_check ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Returns URL to a doc or support resource.
	 *
	 * @since  1.3.2
	 *
	 * @param  string $handle
	 * @return string
	 */
	public function get_resource_url( $handle ) {

		$resource = false;

		if ( 'update-php' === $handle ) {
			$resource = 'https://woocommerce.com/document/how-to-update-your-php-version/';
		} elseif ( 'docs-contents' === $handle ) {
			$resource = 'https://woocommerce.com/document/gift-cards/';
		} elseif ( 'guide' === $handle ) {
			$resource = 'https://woocommerce.com/document/gift-cards/store-owners-guide/';
		} elseif ( 'guide-multi-prepaid-tax' === $handle ) {
			$resource = 'https://woocommerce.com/document/gift-cards/store-owners-guide/#understanding-gift-cards';
		} elseif ( 'faq-multi-prepaid-revenue' === $handle ) {
			$resource = 'https://woocommerce.com/document/gift-cards/faq/#gift-cards-accounting-revenue';
		} elseif ( 'updating' === $handle ) {
			$resource = 'https://woocommerce.com/document/how-to-update-woocommerce/';
		} elseif ( 'ticket-form' === $handle ) {
			$resource = WC_GC_SUPPORT_URL;
		}

		return $resource;
	}
}

/**
 * Returns the main instance of WC_Gift_Cards to prevent the need to use globals.
 *
 * @return  WC_Gift_Cards
 */
function WC_GC() {
	return WC_Gift_Cards::instance();
}

WC_GC();

register_activation_hook( __FILE__, array( WC_GC(), 'on_activation' ) );
register_deactivation_hook( __FILE__, array( WC_GC(), 'on_deactivation' ) );
