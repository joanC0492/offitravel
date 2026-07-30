<?php
/**
 * WC_GC_Install class
 *
 * @package  WooCommerce Gift Cards
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles installation and updating tasks.
 *
 * @class    WC_GC_Install
 * @version  2.7.4
 */
class WC_GC_Install {

	/**
	 * DB updates and callbacks that need to be run per version.
	 *
	 * @var array
	 */
	private static $db_updates = array(
		'1.7.0' => array(
			'wc_gc_update_170_main',
			'wc_gc_update_170_db_version',
		),
	);

	/**
	 * Background update class.
	 *
	 * @var WC_GC_Background_Updater
	 */
	private static $background_updater;

	/**
	 * Current plugin version.
	 *
	 * @var string
	 */
	private static $current_version;

	/**
	 * Current DB version.
	 *
	 * @var string
	 */
	private static $current_db_version;

	/**
	 * Whether install() ran in this request.
	 *
	 * @var boolean
	 */
	private static $is_install_request;

	/**
	 * Hook in.
	 */
	public static function init() {

		// Installation and DB updates handling.
		add_action( 'init', array( __CLASS__, 'init_background_updater' ), 5 );
		add_action( 'init', array( __CLASS__, 'define_updating_constant' ) );
		add_action( 'init', array( __CLASS__, 'maybe_install' ) );
		add_action( 'admin_init', array( __CLASS__, 'maybe_update' ) );
		add_filter( 'site_status_tests', array( __CLASS__, 'add_site_health_tests' ) );

		// Show row meta on the plugin screen.
		add_filter( 'plugin_row_meta', array( __CLASS__, 'plugin_row_meta' ), 10, 2 );

		// Get PB plugin and DB versions.
		self::$current_version    = get_option( 'wc_gc_version', null );
		self::$current_db_version = get_option( 'wc_gc_db_version', null );

		include_once WC_GC_ABSPATH . 'includes/class-wc-gc-background-updater.php';
	}

	/**
	 * Init background updates.
	 */
	public static function init_background_updater() {
		self::$background_updater = new WC_GC_Background_Updater();
	}

	/**
	 * Installation needed?
	 *
	 * @return boolean
	 */
	private static function must_install() {
		if ( is_null( self::$current_version ) ) {
			return true;
		}

		return version_compare( self::$current_version, WC_GC()->get_plugin_version(), '<' );
	}

	/**
	 * Installation possible?
	 *
	 * @return boolean
	 */
	private static function can_install() {
		return ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) && ! defined( 'IFRAME_REQUEST' ) && ! self::is_installing();
	}

	/**
	 * Check version and run the installer if necessary.
	 */
	public static function maybe_install() {
		if ( self::must_install() && self::can_install() ) {
			self::install();
		}
	}

	/**
	 * Is currently installing?
	 *
	 * @since  1.2.0
	 *
	 * @return boolean
	 */
	private static function is_installing() {
		return 'yes' === get_transient( 'wc_gc_installing' );
	}

	/**
	 * DB update needed?
	 *
	 * @return boolean
	 */
	private static function must_update() {

		if ( is_null( self::$current_db_version ) ) {
			return false;
		}

		$db_update_versions = array_keys( self::$db_updates );

		if ( empty( $db_update_versions ) ) {
			return false;
		}

		$db_version_target = end( $db_update_versions );

		return version_compare( self::$current_db_version, $db_version_target, '<' );
	}

	/**
	 * DB update possible?
	 *
	 * @return boolean
	 */
	private static function can_update() {
		return ( self::$is_install_request || ( self::can_install() && current_user_can( 'manage_woocommerce' ) ) ) && ( is_null( self::$current_db_version ) || version_compare( self::$current_db_version, WC_GC()->get_plugin_version( true ), '<' ) );
	}

	/**
	 * Run the updater if triggered.
	 */
	public static function maybe_update() {

		if ( ! empty( $_GET['force_wc_gc_db_update'] ) && isset( $_GET['_wc_gc_admin_nonce'] ) && wp_verify_nonce( wc_clean( wp_unslash( $_GET['_wc_gc_admin_nonce'] ) ), 'wc_gc_force_db_update_nonce' ) ) {

			if ( self::can_update() && self::must_update() ) {
				self::force_update();
			}
		} elseif ( ! empty( $_GET['trigger_wc_gc_db_update'] ) && isset( $_GET['_wc_gc_admin_nonce'] ) && wp_verify_nonce( wc_clean( wp_unslash( $_GET['_wc_gc_admin_nonce'] ) ), 'wc_gc_trigger_db_update_nonce' ) ) {

			if ( self::can_update() && self::must_update() ) {
				self::trigger_update();
			}
		} elseif ( ! empty( $_GET['recreate_wc_gc_tables'] ) && isset( $_GET['_wc_gc_admin_nonce'] ) && wp_verify_nonce( wc_clean( wp_unslash( $_GET['_wc_gc_admin_nonce'] ) ), 'wc_gc_recreate_tables_nonce' ) ) {

			$redirect_url = wp_get_referer();
			if ( ! $redirect_url ) {
				$redirect_url = admin_url();
			}

			$redirect_url = remove_query_arg( array( 'recreate_wc_gc_tables', '_wc_gc_admin_nonce' ), $redirect_url );

			if ( current_user_can( 'manage_woocommerce' ) ) {
				self::create_tables();
				$redirect_url = add_query_arg( 'wc_gc_recreate_tables', 'success', $redirect_url );
			} else {
				$redirect_url = add_query_arg( 'wc_gc_recreate_tables', 'permission_error', $redirect_url );
			}

			wp_safe_redirect( $redirect_url );
			exit;

		} else {

			// Queue upgrade tasks.
			if ( self::can_update() ) {

				if ( ! is_blog_installed() ) {
					return;
				}

				// Plugin data exists - queue upgrade tasks.
				if ( self::must_update() ) {

					if ( ! class_exists( 'WC_GC_Admin_Notices' ) ) {
						require_once WC_GC_ABSPATH . 'includes/admin/class-wc-gc-admin-notices.php';
					}

					// Add 'update' notice and save early -- saving on the 'shutdown' action will fail if a chained request arrives before the 'shutdown' hook fires.
					WC_GC_Admin_Notices::add_maintenance_notice( 'update' );
					WC_GC_Admin_Notices::save_notices();

					if ( self::auto_update_enabled() ) {
						self::update();
					} else {
						delete_transient( 'wc_gc_installing' );
						delete_option( 'wc_gc_update_init' );
					}

					// Nothing found - this is a new install :)
				} else {
					self::update_db_version();
				}
			}
		}
	}

	/**
	 * Register Site Health tests.
	 *
	 * @since  2.7.4
	 *
	 * @param  array $tests Site health tests.
	 * @return array
	 */
	public static function add_site_health_tests( $tests ) {

		$tests['direct']['wc_gc_tables'] = array(
			'label' => __( 'WooCommerce Gift Cards database tables', 'woocommerce-gift-cards' ),
			'test'  => array( __CLASS__, 'site_health_test_tables' ),
		);

		return $tests;
	}

	/**
	 * Site Health test: verify all custom tables exist.
	 *
	 * @since  2.7.4
	 *
	 * @return array
	 */
	public static function site_health_test_tables() {

		$result = array(
			'label'       => __( 'WooCommerce Gift Cards database tables are present', 'woocommerce-gift-cards' ),
			'status'      => 'good',
			'badge'       => array(
				'label' => __( 'WooCommerce Gift Cards', 'woocommerce-gift-cards' ),
				'color' => 'blue',
			),
			'description' => '<p>' . __( 'All required database tables for WooCommerce Gift Cards are present.', 'woocommerce-gift-cards' ) . '</p>',
			'actions'     => '',
			'test'        => 'wc_gc_tables',
		);

		if ( self::is_installing() ) {
			return $result;
		}

		if ( ! self::tables_exist() ) {
			$recreate_url = wp_nonce_url( add_query_arg( 'recreate_wc_gc_tables', true, admin_url() ), 'wc_gc_recreate_tables_nonce', '_wc_gc_admin_nonce' );

			$result['label']       = __( 'WooCommerce Gift Cards database tables are missing', 'woocommerce-gift-cards' );
			$result['status']      = 'critical';
			$result['description'] = '<p>' . __( 'One or more required database tables for WooCommerce Gift Cards are missing. Gift card codes will not be generated until the tables are restored.', 'woocommerce-gift-cards' ) . '</p>';
			$result['actions']     = '<a href="' . esc_url( $recreate_url ) . '" class="button button-primary">' . __( 'Recreate tables', 'woocommerce-gift-cards' ) . '</a>';
		}

		return $result;
	}

	/**
	 * If the DB version is out-of-date, a DB update must be in progress: define a 'WC_GC_UPDATING' constant.
	 */
	public static function define_updating_constant() {
		if ( self::is_update_pending() && ! defined( 'WC_GC_TESTING' ) ) {
			wc_maybe_define_constant( 'WC_GC_UPDATING', true );
		}
	}

	/**
	 * Install GC.
	 */
	public static function install() {

		if ( ! is_blog_installed() ) {
			return;
		}

		// Running for the first time? Set a transient now. Used in 'can_install' to prevent race conditions.
		set_transient( 'wc_gc_installing', 'yes', 10 );

		// Set a flag to indicate we're installing in the current request.
		self::$is_install_request = true;

		// Create tables.
		self::create_tables();

		// Create events.
		self::create_events();

		// Update plugin version - once set, 'maybe_install' will not call 'install' again.
		self::update_version();

		if ( ! class_exists( 'WC_GC_Admin_Notices' ) ) {
			require_once WC_GC_ABSPATH . 'includes/admin/class-wc-gc-admin-notices.php';
		}

		if ( is_null( self::$current_version ) ) {
			// Add dismissible welcome notice.
			WC_GC_Admin_Notices::add_maintenance_notice( 'welcome' );
			// Enable expiration reminders on new installations.
			add_option( 'wc_gc_expiration_reminders_enabled', 'yes' );
		}

		// Run a loopback test after every update. Will only run once if successful.
		WC_GC_Admin_Notices::add_maintenance_notice( 'loopback' );

		// Run an AS test after every update. Will only run once if successful.
		if ( method_exists( WC(), 'queue' ) ) {
			WC_GC_Admin_Notices::add_maintenance_notice( 'queue' );
		}

		// Add a notice to update the revenue analytics stats table when updating from a version older than 1.4.
		if ( ! is_null( self::$current_version ) && version_compare( self::$current_version, '1.4.0', '<' ) && ! WC_GC_Admin_Analytics_Sync::is_order_stats_update_actioned() ) {
			WC_GC_Admin_Notices::add_maintenance_notice( 'update_order_stats' );
		}

		// Flush rules to include our new endpoint.
		flush_rewrite_rules();
	}

	/**
	 * Set up the database tables which the plugin needs to function.
	 */
	private static function create_tables() {
		global $wpdb;
		$wpdb->hide_errors();
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( self::get_schema() );
	}

	/**
	 * True if all custom tables exist.
	 *
	 * @since  2.7.4
	 *
	 * @return boolean
	 */
	public static function tables_exist() {
		global $wpdb;

		$tables = array(
			"{$wpdb->prefix}woocommerce_gc_cards",
			"{$wpdb->prefix}woocommerce_gc_cardsmeta",
			"{$wpdb->prefix}woocommerce_gc_activity",
		);

		foreach ( $tables as $table ) {
			$table_like = $wpdb->esc_like( $table );

			if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_like ) ) !== $table ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Schedule cron events.
	 *
	 * @since 1.12.0
	 */
	public static function create_events() {
		if ( ! wp_next_scheduled( 'wc_gc_daily' ) ) {
			wp_schedule_event( time() + 10, 'daily', 'wc_gc_daily' );
		}

		if ( ! wp_next_scheduled( 'wc_gc_hourly' ) ) {
			wp_schedule_event( time() + 10, 'hourly', 'wc_gc_hourly' );
		}
	}

	/**
	 * Get table schema.
	 *
	 * @return string
	 */
	private static function get_schema() {
		global $wpdb;

		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}

		$max_index_length = 191;

		$tables = "CREATE TABLE {$wpdb->prefix}woocommerce_gc_cards (
  `id` BIGINT UNSIGNED NOT NULL auto_increment,
  `code` VARCHAR(128) NOT NULL,
  `order_id` BIGINT UNSIGNED NOT NULL,
  `order_item_id` BIGINT UNSIGNED NOT NULL,
  `recipient` VARCHAR(255) NOT NULL,
  `redeemed_by` INT UNSIGNED default 0 NOT NULL,
  `sender` VARCHAR(128) NOT NULL,
  `sender_email` VARCHAR(255) NOT NULL,
  `message` longtext NULL,
  `balance` double default 0 NOT NULL,
  `remaining` double default 0 NOT NULL,
  `template_id` VARCHAR(128) default 'default' NOT NULL,
  `create_date` INT UNSIGNED NOT NULL,
  `deliver_date` INT UNSIGNED default 0 NOT NULL,
  `delivered` VARCHAR(32) default 'no' NOT NULL,
  `expire_date` INT UNSIGNED default 0 NOT NULL,
  `redeem_date` INT UNSIGNED default 0 NOT NULL,
  `is_virtual` CHAR(3) NOT NULL,
  `is_active` CHAR(3) NOT NULL,
  `last_modify_date` INT UNSIGNED default 0 NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `code` (`code`),
  KEY `remaining` (`remaining`),
  KEY `redeemed_by` (`redeemed_by`),
  KEY `is_active` (`is_active`)

) $collate;
CREATE TABLE {$wpdb->prefix}woocommerce_gc_cardsmeta (
  `meta_id` BIGINT UNSIGNED NOT NULL auto_increment,
  `gc_giftcard_id` BIGINT UNSIGNED NOT NULL,
  `meta_key` VARCHAR($max_index_length) default NULL,
  `meta_value` longtext NULL,
  PRIMARY KEY  (`meta_id`),
  KEY `giftcard_id` (`gc_giftcard_id`),
  KEY `meta_key` (meta_key($max_index_length))

) $collate;
CREATE TABLE {$wpdb->prefix}woocommerce_gc_activity (
  `id` BIGINT UNSIGNED NOT NULL auto_increment,
  `type` VARCHAR(20) NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `user_email` VARCHAR(255) NOT NULL,
  `object_id` BIGINT UNSIGNED NOT NULL,
  `gc_id` BIGINT UNSIGNED NOT NULL,
  `gc_code` VARCHAR(128) NOT NULL,
  `amount` double default 0 NOT NULL,
  `date` INT UNSIGNED NOT NULL,
  `note` text NULL,
  PRIMARY KEY  (`id`),
  KEY `type` (`type`),
  KEY `user_id` (`user_id`),
  KEY `object_id` (`object_id`),
  KEY `gc_id` (`gc_id`)

) $collate;";

		return $tables;
	}

	/**
	 * Update WC GC version to current.
	 */
	private static function update_version() {
		delete_option( 'wc_gc_version' );
		add_option( 'wc_gc_version', WC_GC()->get_plugin_version() );
	}

	/**
	 * Push all needed DB updates to the queue for processing.
	 */
	private static function update() {

		if ( ! is_object( self::$background_updater ) ) {
			self::init_background_updater();
		}

		$update_queued = false;

		foreach ( self::$db_updates as $version => $update_callbacks ) {

			if ( version_compare( self::$current_db_version, $version, '<' ) ) {

				$update_queued = true;
				WC_GC()->log( sprintf( 'Updating to version %s.', $version ), 'info', 'wc_gc_db_updates' );

				foreach ( $update_callbacks as $update_callback ) {
					WC_GC()->log( sprintf( '- Queuing %s callback.', $update_callback ), 'info', 'wc_gc_db_updates' );
					self::$background_updater->push_to_queue( $update_callback );
				}
			}
		}

		if ( $update_queued ) {

			// Define 'WC_GC_UPDATING' constant.
			wc_maybe_define_constant( 'WC_GC_UPDATING', true );

			// Keep track of time.
			delete_option( 'wc_gc_update_init' );
			add_option( 'wc_gc_update_init', gmdate( 'U' ) );

			// Dispatch.
			self::$background_updater->save()->dispatch();
		}
	}

	/**
	 * Is auto-updating enabled?
	 *
	 * @return boolean
	 */
	public static function auto_update_enabled() {
		return apply_filters( 'wc_gc_auto_update_db', true );
	}

	/**
	 * Trigger DB update.
	 */
	public static function trigger_update() {
		self::update();
		wp_safe_redirect( admin_url() );
		exit;
	}

	/**
	 * Force re-start the update cron if everything else fails.
	 */
	public static function force_update() {

		if ( ! is_object( self::$background_updater ) ) {
			self::init_background_updater();
		}

		/**
		 * Updater cron action.
		 */
		do_action( self::$background_updater->get_cron_hook_identifier() );
		wp_safe_redirect( admin_url() );
		exit;
	}

	/**
	 * Updates plugin DB version when all updates have been processed.
	 */
	public static function update_complete() {

		WC_GC()->log( 'Data update complete.', 'info', 'wc_gc_db_updates' );
		self::update_db_version();
		delete_option( 'wc_gc_update_init' );
		wp_cache_flush();
	}

	/**
	 * True if a DB update is pending.
	 *
	 * @return boolean
	 */
	public static function is_update_pending() {
		return self::must_update();
	}

	/**
	 * True if a DB update was started but not completed.
	 *
	 * @return boolean
	 */
	public static function is_update_incomplete() {
		return false !== get_option( 'wc_gc_update_init', false );
	}


	/**
	 * True if a DB update is in progress.
	 *
	 * @return boolean
	 */
	public static function is_update_queued() {
		return self::$background_updater->is_update_queued();
	}

	/**
	 * True if an update process is running.
	 *
	 * @return boolean
	 */
	public static function is_update_process_running() {
		return self::is_update_cli_process_running() || self::is_update_background_process_running();
	}

	/**
	 * True if an update background process is running.
	 *
	 * @return boolean
	 */
	public static function is_update_background_process_running() {
		return self::$background_updater->is_process_running();
	}

	/**
	 * True if a CLI update is running.
	 *
	 * @return boolean
	 */
	public static function is_update_cli_process_running() {
		return false !== get_transient( 'wc_gc_update_cli_init', false );
	}

	/**
	 * Update DB version to current.
	 *
	 * @param  string $version
	 */
	public static function update_db_version( $version = null ) {

		$version = is_null( $version ) ? WC_GC()->get_plugin_version() : $version;

		// Remove suffixes.
		$version = WC_GC()->get_plugin_version( true, $version );

		delete_option( 'wc_gc_db_version' );
		add_option( 'wc_gc_db_version', $version );

		WC_GC()->log( sprintf( 'Database version is %s.', get_option( 'wc_gc_db_version', 'unknown' ) ), 'info', 'wc_gc_db_updates' );
	}

	/**
	 * Get list of DB update callbacks.
	 *
	 * @return array
	 */
	public static function get_db_update_callbacks() {
		return self::$db_updates;
	}

	/**
	 * Show row meta on the plugin screen.
	 *
	 * @param   mixed $links
	 * @param   mixed $file
	 * @return  array
	 */
	public static function plugin_row_meta( $links, $file ) {

		if ( WC_GC()->get_plugin_basename() == $file ) {
			$row_meta = array(
				'docs'    => '<a href="' . WC_GC()->get_resource_url( 'docs-contents' ) . '">' . __( 'Documentation', 'woocommerce-gift-cards' ) . '</a>',
				'support' => '<a href="' . WC_GC()->get_resource_url( 'ticket-form' ) . '">' . __( 'Support', 'woocommerce-gift-cards' ) . '</a>',
			);

			return array_merge( $links, $row_meta );
		}

		return $links;
	}
}

WC_GC_Install::init();
