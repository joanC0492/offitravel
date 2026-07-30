<?php
use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;

/**
 * Class for integrating with WooCommerce Blocks scripts
 *
 * @package WooCommerce Gift Cards
 * @since   1.11.0
 *
 * @version 1.16.9
 */
class WC_GC_Checkout_Blocks_Integration implements IntegrationInterface {

	/**
	 * Whether the integration has been initialized.
	 *
	 * @var boolean
	 */
	protected $is_initialized;

	/**
	 * The single instance of the class.
	 *
	 * @var WC_GC_Checkout_Blocks_Integration
	 */
	protected static $_instance = null;

	/**
	 * Main WC_GC_Checkout_Blocks_Integration instance. Ensures only one instance of WC_GC_Checkout_Blocks_Integration is loaded or can be loaded.
	 *
	 * @static
	 * @return WC_GC_Checkout_Blocks_Integration
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
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Foul!', 'woocommerce-gift-cards' ), '1.11.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Foul!', 'woocommerce-gift-cards' ), '1.11.0' );
	}

	/**
	 * The name of the integration.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'wc-gift-cards-blocks';
	}

	/**
	 * When called invokes any initialization/setup for the integration.
	 */
	public function initialize() {

		if ( $this->is_initialized ) {
			return;
		}

		// Enqueue block assets for the editor.
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
		// Enqueue block assets for both editor and front-end.
		add_action( 'enqueue_block_assets', array( $this, 'enqueue_block_assets' ) );
	}

	/**
	 * Returns an array of script handles to enqueue in the frontend context.
	 *
	 * @return string[]
	 */
	public function get_script_handles() {
		return array( 'wc-gift-cards-blocks' );
	}

	/**
	 * Returns an array of script handles to enqueue in the editor context.
	 *
	 * @return string[]
	 */
	public function get_editor_script_handles() {
		return array( 'wc-gift-cards-blocks' );
	}

	/**
	 * An array of key, value pairs of data made available to the block on the client side.
	 *
	 * @return array
	 */
	public function get_script_data() {

		global $post;
		$is_singular = true;
		if ( ! is_a( $post, 'WP_Post' ) ) {
			$is_singular = false;
		}

		$data = array(
			'is_redeeming_enabled'                 => wc_gc_is_redeeming_enabled(),
			'is_cart_disabled'                     => 'yes' === get_option( 'wc_gc_disable_cart_ui' ),
			'show_balance_checkbox'                => (bool) apply_filters( 'woocommerce_gc_checkout_show_balance_checkbox', true ),
			'show_remaining_balance_per_gift_card' => (bool) apply_filters( 'woocommerce_gc_checkout_show_remaining_balance_per_gift_card', true ),

			'is_ui_disabled'                       => ! wc_gc_is_ui_disabled(),
			'is_cart'                              => $is_singular && has_block( 'woocommerce/cart', $post ),
			'is_checkout'                          => $is_singular && has_block( 'woocommerce/checkout', $post ),
			'account_orders_link'                  => add_query_arg( array( 'wc_gc_show_pending_orders' => 'yes' ), wc_get_account_endpoint_url( 'orders' ) ),
		);

		return $data; // nosemgrep: audit.php.wp.security.xss.query-arg
	}

	/**
	 * Enqueue block assets for the editor.
	 *
	 * @since 1.13.1
	 *
	 * @return void
	 */
	public function enqueue_block_editor_assets() {

		$suffix            = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$script_path       = '/assets/dist/admin/blocks' . $suffix . '.js';
		$script_asset_path = WC_GC_ABSPATH . 'assets/dist/admin/blocks.asset.php';
		$script_asset      = file_exists( $script_asset_path )
			? require $script_asset_path
			: array(
				'dependencies' => array(),
				'version'      => WC_GC()->get_plugin_version(),
			);
		$script_url        = WC_GC()->get_plugin_url() . $script_path;

		wp_register_script(
			'wc-gift-cards-admin-blocks',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);

		wp_enqueue_script( 'wc-gift-cards-admin-blocks' );
	}

	/**
	 * Enqueue block assets for both editor and front-end.
	 *
	 * @since 1.13.1
	 *
	 * @return void
	 */
	public function enqueue_block_assets() {

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		// Load main JS file.
		$script_path       = '/assets/dist/frontend/blocks' . $suffix . '.js';
		$script_asset_path = WC_GC_ABSPATH . 'assets/dist/frontend/blocks.asset.php';
		$script_asset      = file_exists( $script_asset_path )
			? require $script_asset_path
			: array(
				'dependencies' => array(),
				'version'      => WC_GC()->get_plugin_version(),
			);
		$script_url        = WC_GC()->get_plugin_url() . $script_path;

		wp_register_script(
			'wc-gift-cards-blocks',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);
		wp_script_add_data( 'wc-gift-cards-blocks', 'strategy', 'defer' );

		// Load JS translations.
		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( 'wc-gift-cards-blocks', 'woocommerce-gift-cards', WC_GC_ABSPATH . 'languages/' );
		}

		// Load stylesheet.
		$style_path       = 'assets/dist/frontend/blocks.css';
		$style_asset_path = WC_GC_ABSPATH . $style_path;
		$style_url        = WC_GC()->get_plugin_url() . '/' . $style_path;
		wp_enqueue_style(
			'wc-gift-cards-blocks-integration',
			$style_url,
			array(),
			$this->get_file_version( $style_asset_path )
		);
	}

	/**
	 * Get the file modified time as a cache buster if we're in dev mode.
	 *
	 * @param string $file Local path to the file.
	 * @return string The cache buster value to use for the given file.
	 */
	protected function get_file_version( $file ) {
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG && file_exists( $file ) ) {
			return filemtime( $file );
		}
		return WC_GC()->get_plugin_version();
	}
}
