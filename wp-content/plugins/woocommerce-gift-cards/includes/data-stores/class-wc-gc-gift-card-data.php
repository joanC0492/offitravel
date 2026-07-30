<?php
/**
 * WC_GC_Gift_Card_Data class
 *
 * @package  WooCommerce Gift Cards
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gift Card Data model class.
 *
 * @class    WC_GC_Gift_Card_Data
 * @version  2.7.3
 */
class WC_GC_Gift_Card_Data {

	/**
	 * Runtime cache of pending orders.
	 *
	 * @var array
	 */
	protected $pending_orders;

	/**
	 * Data array, with defaults.
	 *
	 * @var array
	 */
	protected $data = array(
		'id'               => 0,
		'is_active'        => 'on',
		'is_virtual'       => 'on',
		'code'             => '',
		'order_id'         => 0,
		'order_item_id'    => 0,
		'recipient'        => '',
		'redeemed_by'      => 0,
		'sender'           => '',
		'sender_email'     => '',
		'template_id'      => 'default',
		'message'          => '',
		'balance'          => 0,
		'remaining'        => 0,
		'create_date'      => 0,
		'deliver_date'     => 0,
		'delivered'        => 'no',
		'expire_date'      => 0,
		'redeem_date'      => 0,
		'last_modify_date' => 0,
	);

	/**
	 * Stores meta data, defaults included.
	 * Meta keys are assumed unique by default. No meta is internal.
	 *
	 * @var array
	 */
	protected $meta_data = array();

	/**
	 * Constructor.
	 *
	 * @param  int|object|array $item  ID to load from the DB (optional) or already queried data.
	 */
	public function __construct( $giftcard = 0 ) {
		if ( $giftcard instanceof WC_GC_Gift_Card_Data ) {
			$this->set_all( $giftcard->get_data() );
		} elseif ( is_array( $giftcard ) ) {
			$this->set_all( $giftcard );
		} else {
			$this->read( $giftcard );
		}
	}

	/*
	---------------------------------------------------*/
	/*
		Getters.                                         */
	/*---------------------------------------------------*/

	/**
	 * Returns all data for this object.
	 *
	 * @return array
	 */
	public function get_data() {
		return $this->data;
	}

	/**
	 * Get id.
	 *
	 * @return int
	 */
	public function get_id() {
		return absint( $this->data['id'] );
	}

	/**
	 * Get code.
	 *
	 * @return string
	 */
	public function get_code() {
		return $this->data['code'];
	}

	/**
	 * Get order ID.
	 *
	 * @return int
	 */
	public function get_order_id() {
		return absint( $this->data['order_id'] );
	}

	/**
	 * Get order item ID.
	 *
	 * @return int
	 */
	public function get_order_item_id() {
		return absint( $this->data['order_item_id'] );
	}

	/**
	 * Get recipient.
	 *
	 * @return string
	 */
	public function get_recipient() {
		return $this->data['recipient'];
	}

	/**
	 * Get redeemed by user.
	 *
	 * @return string
	 */
	public function get_redeemed_by() {
		return absint( $this->data['redeemed_by'] );
	}

	/**
	 * Get sender.
	 *
	 * @return string
	 */
	public function get_sender() {
		return $this->data['sender'];
	}

	/**
	 * Get sender email.
	 *
	 * @return string
	 */
	public function get_sender_email() {
		return $this->data['sender_email'];
	}

	/**
	 * Get message.
	 *
	 * @return string
	 */
	public function get_message() {
		return $this->data['message'];
	}


	/**
	 * Get balance.
	 *
	 * @return float
	 */
	public function get_initial_balance() {
		return (float) $this->data['balance'];
	}

	/**
	 * Get remaining balance.
	 *
	 * @return float
	 */
	public function get_balance() {
		return (float) $this->data['remaining'];
	}

	/**
	 * Get template id.
	 *
	 * @since 1.2.0
	 *
	 * @return string
	 */
	public function get_template_id() {
		return $this->data['template_id'];
	}

	/**
	 * Get create date.
	 *
	 * @return int
	 */
	public function get_date_created() {
		return absint( $this->data['create_date'] );
	}

	/**
	 * Get deliver date.
	 *
	 * @return int
	 */
	public function get_deliver_date() {
		return absint( $this->data['deliver_date'] );
	}

	/**
	 * Get expire date.
	 *
	 * @return int
	 */
	public function get_expire_date() {
		return absint( $this->data['expire_date'] );
	}

	/**
	 * Get redeem date.
	 *
	 * @return int
	 */
	public function get_date_redeemed() {
		return absint( $this->data['redeem_date'] );
	}

	/**
	 * Get last modify date.
	 *
	 * @return int
	 *
	 * @since 2.1.0
	 */
	public function get_last_modify_date() {
		return absint( $this->data['last_modify_date'] );
	}

	/**
	 * Get pending balance if any.
	 *
	 * @since 1.6.0
	 *
	 * @param  bool $expand
	 * @return mixed
	 */
	public function get_pending_balance( $expand = false ) {

		if ( is_null( $this->pending_orders ) ) {

			$pending_orders  = array();
			$pending_balance = 0;
			foreach ( $this->get_meta_data() as $key => $value ) {
				if ( 0 === strpos( $key, 'balance_' ) ) {
					$pending_orders[ str_replace( 'balance_', '', $key ) ] = (float) $value;
					$pending_balance                                      += (float) $value;
				}
			}

			// Cache results.
			$this->pending_orders = $pending_orders;

		} else {

			$pending_orders  = $this->pending_orders;
			$pending_balance = array_sum( $this->pending_orders );
		}

		return $expand ? $pending_orders : $pending_balance;
	}

	/**
	 * Get generated hash.
	 *
	 * @since 1.9.0
	 *
	 * @return string
	 */
	public function get_hash() {

		$key = $this->get_meta( '_hash_key' );
		$iv  = $this->get_meta( '_hash_iv' );

		// Regenerate if needed.
		if ( empty( $key ) || empty( $iv ) ) {
			$this->setup_hash_data();

			/**
			 * We need to save the object here.
			 * This is needed for backwards compatibility lt 1.9.0.
			 *
			 * @see WC_GC_Gift_Card_Data::create()
			 */
			$this->save_meta_data();

			$key = $this->get_meta( '_hash_key' );
			$iv  = $this->get_meta( '_hash_iv' );
		}

		$input     = $this->get_id() . '-' . $this->get_order_id() . '-' . $this->get_date_created();
		$encrypted = openssl_encrypt( $input, 'AES-256-CBC', $key, 0, $iv );
		$hash      = hash( 'sha256', $encrypted );

		return $hash;
	}

	/**
	 * Get All Meta Data.
	 *
	 * @since 1.6.0
	 *
	 * @return array
	 */
	public function get_meta_data() {
		return array_filter( $this->meta_data, array( $this, 'has_meta_value' ) );
	}

	/*
	---------------------------------------------------*/
	/*
		Setters.                                         */
	/*---------------------------------------------------*/

	/**
	 * Set all data based on input array.
	 *
	 * @param  array $data
	 */
	public function set_all( $data ) {
		foreach ( $data as $key => $value ) {
			// Fix some strange namings.
			if ( 'balance' === $key ) {
				$this->set_initial_balance( $value );
			} elseif ( 'is_active' === $key ) {
				$this->set_active( $value );
			} elseif ( 'is_virtual' === $key ) {
				$this->set_virtual( $value );
			} elseif ( 'remaining' === $key ) {
				$this->set_balance( $value );
			} elseif ( is_callable( array( $this, "set_$key" ) ) ) {
				$this->{"set_$key"}( $value );
			} else {
				$this->data[ $key ] = $value;
			}
		}
	}

	/**
	 * Set Deployment ID.
	 *
	 * @param  int
	 */
	public function set_id( $value ) {
		$this->data['id'] = absint( $value );
	}

	/**
	 * Set active.
	 *
	 * @param  string
	 * @return void
	 */
	public function set_active( $value ) {
		$this->data['is_active'] = 'on' === $value ? 'on' : 'off';
	}

	/**
	 * Set virtual.
	 *
	 * @param  string
	 * @return void
	 */
	public function set_virtual( $value ) {
		$this->data['is_virtual'] = 'on' === $value ? 'on' : 'off';
	}

	/**
	 * Set code.
	 *
	 * @param  strings
	 * @return void
	 */
	public function set_code( $value ) {
		$this->data['code'] = substr( $value, 0, 19 );
	}

	/**
	 * Set order ID.
	 *
	 * @param  int
	 * @return void
	 */
	public function set_order_id( $value ) {
		$this->data['order_id'] = absint( $value );
	}

	/**
	 * Set order item ID.
	 *
	 * @param  int
	 * @return void
	 */
	public function set_order_item_id( $value ) {
		$this->data['order_item_id'] = absint( $value );
	}

	/**
	 * Set recipient.
	 *
	 * @param  string
	 * @return void
	 */
	public function set_recipient( $value ) {
		$this->data['recipient'] = $value;
	}

	/**
	 * Set redeemed by.
	 *
	 * @param  string
	 * @return void
	 */
	public function set_redeemed_by( $value ) {
		$this->data['redeemed_by'] = absint( $value );
	}

	/**
	 * Set sender.
	 *
	 * @param  string
	 * @return void
	 */
	public function set_sender( $value ) {
		$this->data['sender'] = $value;
	}

	/**
	 * Set sender_email.
	 *
	 * @param  string
	 * @return void
	 */
	public function set_sender_email( $value ) {
		$this->data['sender_email'] = $value;
	}

	/**
	 * Set message.
	 *
	 * @param  string
	 * @return void
	 */
	public function set_message( $value ) {
		$this->data['message'] = $value;
	}

	/**
	 * Set balance.
	 *
	 * @param  float
	 * @return void
	 */
	public function set_initial_balance( $value ) {
		$this->data['balance'] = wc_format_decimal( (float) $value );
	}

	/**
	 * Set remaining balance.
	 *
	 * @param  float
	 * @return void
	 */
	public function set_balance( $value ) {
		$this->data['remaining'] = wc_format_decimal( (float) $value );
	}

	/**
	 * Set template id.
	 *
	 * @since 1.2.0
	 *
	 * @param  string
	 * @return void
	 */
	public function set_template_id( $value ) {
		$this->data['template_id'] = $value;
	}

	/**
	 * Set create date.
	 *
	 * @param  string
	 * @return void
	 */
	public function set_date_created( $value ) {
		$this->data['create_date'] = absint( $value );
	}

	/**
	 * Set expire date.
	 *
	 * @param  string
	 * @return void
	 */
	public function set_expire_date( $value ) {
		$this->data['expire_date'] = absint( $value );
	}

	/**
	 * Set deliver date.
	 *
	 * @param  string
	 * @return void
	 */
	public function set_deliver_date( $value ) {
		$this->data['deliver_date'] = absint( $value );
	}

	/**
	 * Set delivered status.
	 *
	 * @param  mixed
	 * @return void
	 */
	public function set_delivered( $value ) {
		$this->data['delivered'] = 'no' === $value ? 'no' : absint( $value );
	}

	/**
	 * Set redeem date.
	 *
	 * @param  string
	 * @return void
	 */
	public function set_date_redeemed( $value ) {
		$this->data['redeem_date'] = absint( $value );
	}

	/**
	 * Set all meta data from array.
	 *
	 * @since 1.6.0
	 *
	 * @param array $data
	 */
	public function set_meta_data( $data ) {
		if ( ! empty( $data ) && is_array( $data ) ) {
			foreach ( $data as $key => $value ) {
				if ( $this->has_meta_value( $value ) ) {
					$this->meta_data[ $key ] = $this->sanitize_meta_value( $value, $key );
				}
			}
		}
	}

	/*
	---------------------------------------------------*/
	/*
		CRUD.                                            */
	/*---------------------------------------------------*/

	/**
	 * Insert data into the database.
	 */
	public function create() {
		global $wpdb;

		$data = array(
			'code'             => $this->get_code(),
			'is_active'        => $this->is_active() ? 'on' : 'off',
			'is_virtual'       => $this->is_virtual() ? 'on' : 'off',
			'order_id'         => $this->get_order_id(),
			'order_item_id'    => $this->get_order_item_id(),
			'recipient'        => $this->get_recipient(),
			'redeemed_by'      => $this->get_redeemed_by(),
			'sender'           => $this->get_sender(),
			'sender_email'     => $this->get_sender_email(),
			'message'          => $this->get_message(),
			'balance'          => $this->get_initial_balance(),
			'remaining'        => $this->get_balance(),
			'create_date'      => $this->get_date_created(),
			'deliver_date'     => $this->get_deliver_date(),
			'delivered'        => false === $this->is_delivered() ? 'no' : $this->is_delivered(),
			'expire_date'      => $this->get_expire_date(),
			'redeem_date'      => $this->get_date_redeemed(),
			'last_modify_date' => $this->get_last_modify_date(),
		);

		$prepare_types = array( '%s', '%s', '%s', '%d', '%d', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%d', '%d', '%d' );

		// Insert specific ID if included and bypass the Auto Increment column.
		if ( $this->get_id() ) {
			$data          = array_merge( array( 'id' => $this->get_id() ), $data );
			$prepare_types = array_merge( array( '%d' ), $prepare_types );
		}

		$inserted = $wpdb->insert( $wpdb->prefix . 'woocommerce_gc_cards', $data, $prepare_types );

		if ( false !== $inserted ) {

			$this->set_id( $wpdb->insert_id );
			do_action( 'woocommerce_gc_create_gift_card', $this );

			return $wpdb->insert_id;
		}

		return false;
	}

	/**
	 * Update data in the database.
	 */
	public function update() {
		global $wpdb;

		$data = array(
			'code'             => $this->get_code(),
			'is_active'        => $this->is_active() ? 'on' : 'off',
			'is_virtual'       => $this->is_virtual() ? 'on' : 'off',
			'order_id'         => $this->get_order_id(),
			'order_item_id'    => $this->get_order_item_id(),
			'recipient'        => $this->get_recipient(),
			'redeemed_by'      => $this->get_redeemed_by(),
			'sender'           => $this->get_sender(),
			'sender_email'     => $this->get_sender_email(),
			'message'          => $this->get_message(),
			'balance'          => $this->get_initial_balance(),
			'remaining'        => $this->get_balance(),
			'create_date'      => $this->get_date_created(),
			'deliver_date'     => $this->get_deliver_date(),
			'delivered'        => false === $this->is_delivered() ? 'no' : $this->is_delivered(),
			'expire_date'      => $this->get_expire_date(),
			'redeem_date'      => $this->get_date_redeemed(),
			'last_modify_date' => $this->get_last_modify_date(),
		);

		$updated = $wpdb->update( $wpdb->prefix . 'woocommerce_gc_cards', $data, array( 'id' => $this->get_id() ), array( '%s', '%s', '%s', '%d', '%d', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%d', '%d', '%d' ) );

		do_action( 'woocommerce_gc_update_gift_card', $this );

		return $updated;
	}

	/**
	 * Delete data from the database.
	 */
	public function delete() {

		if ( $this->get_id() ) {
			global $wpdb;

			do_action( 'woocommerce_gc_before_delete_gift_card', $this );

			// Delete and clean up.
			$wpdb->delete( $wpdb->prefix . 'woocommerce_gc_cards', array( 'id' => $this->get_id() ) );
			$wpdb->delete( $wpdb->prefix . 'woocommerce_gc_cardsmeta', array( 'gc_giftcard_id' => $this->get_id() ) );
			$wpdb->delete( $wpdb->prefix . 'woocommerce_gc_activity', array( 'gc_id' => $this->get_id() ) );

			do_action( 'woocommerce_gc_delete_gift_card', $this );
		}
	}

	/**
	 * Save data to the database.
	 *
	 * @return int
	 */
	public function save() {

		$this->validate();

		do_action( 'woocommerce_gc_before_save_gift_card', $this );

		if ( ! $this->get_id() ) {
			$saved = $this->create();
			$this->setup_hash_data();
		} else {
			$saved = $this->update();
		}

		$this->save_meta_data();

		do_action( 'woocommerce_gc_save_gift_card', $this );

		return false !== $saved ? $this->get_id() : false;
	}

	/**
	 * Clear object caches if needed.
	 *
	 * @since 1.6.0
	 *
	 * @return void
	 */
	protected function clear_meta_caches() {
		$this->pending_orders = null;
	}

	/**
	 * Read from DB object using ID.
	 *
	 * @param  int $giftcard
	 * @return void
	 */
	public function read( $giftcard ) {
		global $wpdb;

		if ( is_numeric( $giftcard ) && ! empty( $giftcard ) ) {
			$data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woocommerce_gc_cards WHERE id = %d LIMIT 1;", $giftcard ) );
		} elseif ( ! empty( $giftcard->id ) ) {
			$data = $giftcard;
		} else {
			$data = false;
		}

		if ( $data ) {
			$this->set_all( $data );
			$this->read_meta_data();
		}
	}

	/**
	 * Validates before saving for sanity.
	 */
	public function validate() {
		$this->set_all(
			array(
				// Always set last modify date to now.
				'last_modify_date' => time(),
			)
		);
	}

	/*
	---------------------------------------------------*/
	/*
		Meta CRUD.                                       */
	/*---------------------------------------------------*/

	/**
	 * Read meta data from the database.
	 *
	 * @since 1.6.0
	 *
	 * @return void
	 */
	protected function read_meta_data() {

		$this->clear_meta_caches();
		$this->meta_data = array();

		if ( ! $this->get_id() ) {
			return;
		}

		global $wpdb;
		$raw_meta_data = $wpdb->get_results(
			$wpdb->prepare(
				"
			SELECT meta_id, meta_key, meta_value
			FROM {$wpdb->prefix}woocommerce_gc_cardsmeta
			WHERE gc_giftcard_id = %d ORDER BY meta_id
		",
				$this->get_id()
			)
		);

		foreach ( $raw_meta_data as $meta ) {
			$this->meta_data[ $meta->meta_key ] = $this->sanitize_meta_value( $meta->meta_value, $meta->meta_key );
		}
	}

	/**
	 * Update Meta Data in the database.
	 *
	 * @since 1.6.0
	 *
	 * @return void
	 */
	public function save_meta_data() {

		global $wpdb;

		$raw_meta_data = $wpdb->get_results(
			$wpdb->prepare(
				"
			SELECT meta_id, meta_key, meta_value
			FROM {$wpdb->prefix}woocommerce_gc_cardsmeta
			WHERE gc_giftcard_id = %d ORDER BY meta_id
		",
				$this->get_id()
			)
		);

		$updated_meta_keys = array();

		// Update or delete meta from the db.
		if ( ! empty( $raw_meta_data ) ) {

			// Update or delete meta from the db depending on their presence.
			foreach ( $raw_meta_data as $meta ) {
				if ( isset( $this->meta_data[ $meta->meta_key ] ) && null !== $this->meta_data[ $meta->meta_key ] && ! in_array( $meta->meta_key, $updated_meta_keys ) ) {
					update_metadata_by_mid( 'gc_giftcard', $meta->meta_id, $this->meta_data[ $meta->meta_key ], $meta->meta_key );
					$updated_meta_keys[] = $meta->meta_key;
				} else {
					delete_metadata_by_mid( 'gc_giftcard', $meta->meta_id );
				}
			}
		}

		// Add any meta that weren't updated.
		$add_meta_keys = array_diff( array_keys( $this->meta_data ), $updated_meta_keys );

		foreach ( $add_meta_keys as $meta_key ) {
			if ( null !== $this->meta_data[ $meta_key ] ) {
				add_metadata( 'gc_giftcard', $this->get_id(), $meta_key, $this->meta_data[ $meta_key ], true );
			}
		}

		$this->read_meta_data();
	}

	/**
	 * Get Meta by Key.
	 *
	 * @since 1.6.0
	 *
	 * @param  string $key
	 * @return mixed
	 */
	public function get_meta( $key ) {

		$value = null;

		if ( isset( $this->meta_data[ $key ] ) ) {
			$value = $this->meta_data[ $key ];
		}

		return $value;
	}

	/**
	 * Add meta data.
	 *
	 * @since 1.6.0
	 *
	 * @param  string $key
	 * @param  string $value
	 */
	public function add_meta( $key, $value ) {
		$this->update_meta( $key, $value );
	}

	/**
	 * Add meta data.
	 *
	 * @since 1.6.0
	 *
	 * @param  string $key
	 * @param  string $value
	 */
	public function update_meta( $key, $value ) {
		if ( is_null( $value ) ) {
			$this->delete_meta( $key );
		} else {
			$this->meta_data[ $key ] = $this->sanitize_meta_value( $value, $key );
		}

		$this->clear_meta_caches();
	}

	/**
	 * Delete meta data.
	 *
	 * @since 1.6.0
	 *
	 * @param  array $key
	 */
	public function delete_meta( $key ) {
		$this->meta_data[ $key ] = null;
		$this->clear_meta_caches();
	}

	/*
	---------------------------------------------------*/
	/*
		Utilities.                                       */
	/*---------------------------------------------------*/

	/**
	 * Is Gift Card active.
	 *
	 * @return bool
	 */
	public function is_active() {
		return 'on' === $this->data['is_active'];
	}

	/**
	 * Is Gift Card virtual.
	 *
	 * @return bool
	 */
	public function is_virtual() {
		return 'on' === $this->data['is_virtual'];
	}

	/**
	 * Is Gift Card redeemed.
	 *
	 * @return bool
	 */
	public function is_redeemed() {
		return 0 !== $this->get_date_redeemed() && 0 !== $this->get_redeemed_by();
	}

	/**
	 * Is Gift Card delivered.
	 *
	 * @return bool|int False or user id
	 */
	public function is_delivered() {
		return 'no' === $this->data['delivered'] ? false : absint( $this->data['delivered'] );
	}

	/**
	 * Is Gift Card expired.
	 *
	 * @return bool
	 */
	public function has_expired() {
		return 0 !== $this->get_expire_date() && $this->get_expire_date() <= time();
	}

	/**
	 * Cleans null value meta when getting.
	 *
	 * @since 1.6.0
	 *
	 * @param  mixed $value
	 * @return boolean
	 */
	private function has_meta_value( $value ) {
		return ! is_null( $value );
	}

	/**
	 * Meta value type sanitization on the way in.
	 *
	 * @since 1.6.0
	 *
	 * @param  mixed  $meta_value
	 * @param  string $meta_key
	 */
	private function sanitize_meta_value( $meta_value, $meta_key ) {

		// Always attempt to unserialize on the way in.
		$meta_value = maybe_unserialize( $meta_value );

		return $meta_value;
	}

	/**
	 * Setup hash data for handling gift cards specific secure requests (e.g. Email redeeming).
	 *
	 * @since 1.9.0
	 *
	 * @return void
	 */
	private function setup_hash_data() {
		$key = hash( 'sha256', openssl_random_pseudo_bytes( 32 ) );
		$iv  = substr( hash( 'sha256', openssl_random_pseudo_bytes( openssl_cipher_iv_length( 'AES-256-CBC' ) ) ), 0, 16 );
		$this->update_meta( '_hash_key', $key );
		$this->update_meta( '_hash_iv', $iv );
	}

	/**
	 * Validates a gift cards's specific hash. (e.g. Email redeeming).
	 *
	 * @since 1.9.0
	 *
	 * @return bool
	 */
	public function validate_hash( $hash_to_check ) {
		return $hash_to_check === $this->get_hash();
	}

	/**
	 * Lock checkout transactions for this giftcard.
	 *
	 * @since 1.14.0
	 *
	 * @throws Exception
	 *
	 * @return int|null
	 */
	public function lock_transactions() {
		$result = WC_GC()->db->giftcards->check_and_lock_giftcard( $this->get_id() );
		if ( false === $result ) {
			// translators: Giftcard code.
			throw new Exception( sprintf( __( 'An unexpected error happened while applying gift card code %s.', 'woocommerce-gift-cards' ), $this->get_code() ) );
		} elseif ( 0 === $result ) {
			// translators: Giftcard code.
			throw new Exception( sprintf( __( 'Gift card code %s was used in another transaction during this checkout. Please try again.', 'woocommerce-gift-cards' ), $this->get_code() ) );
		}
		return $result;
	}
}
