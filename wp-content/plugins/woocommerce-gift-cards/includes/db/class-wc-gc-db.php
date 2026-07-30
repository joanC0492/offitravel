<?php
/**
 * WC_GC_DB class
 *
 * @package  WooCommerce Gift Cards
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DB API class.
 *
 * @version 1.6.0
 */
class WC_GC_DB {

	/**
	 * A reference to the DB Model - @see WC_GC_Gift_Cards_DB.
	 *
	 * @var WC_GC_Gift_Cards_DB
	 */
	public $giftcards;

	/**
	 * A reference to the DB Model - @see WC_GC_Activity_DB.
	 *
	 * @var WC_GC_Activity_DB
	 */
	public $activity;

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
	 * Constructor.
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'wpdb_giftcards_meta_table_fix' ), 0 );
		add_action( 'switch_blog', array( $this, 'wpdb_giftcards_meta_table_fix' ), 0 );

		// Attach DB Models to public properties.
		$this->giftcards = new WC_GC_Gift_Cards_DB();
		$this->activity  = new WC_GC_Activity_DB();
	}

	/**
	 * Make WP see 'gc_giftcard' as a meta type.
	 *
	 * @since 1.6.0
	 */
	public function wpdb_giftcards_meta_table_fix() {
		global $wpdb;
		$wpdb->gc_giftcardmeta = $wpdb->prefix . 'woocommerce_gc_cardsmeta';
		$wpdb->tables[]        = 'woocommerce_gc_cardsmeta';
	}
}
