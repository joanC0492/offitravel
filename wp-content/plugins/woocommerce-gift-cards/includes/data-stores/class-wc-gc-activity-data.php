<?php
/**
 * WC_GC_Activity_Data class
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
 * @class    WC_GC_Activity_Data
 * @version  2.7.3
 */
class WC_GC_Activity_Data {

	/**
	 * Data array, with defaults.
	 *
	 * @var array
	 */
	protected $data = array(
		'id'         => 0,
		'type'       => '',
		'user_id'    => 0,
		'user_email' => '',
		'object_id'  => 0,
		'gc_id'      => 0,
		'gc_code'    => '',
		'amount'     => 0,
		'date'       => 0,
		'note'       => '',
	);

	/**
	 * Constructor.
	 *
	 * @param  int|object|array $item  ID to load from the DB (optional) or already queried data.
	 */
	public function __construct( $activity = 0 ) {
		if ( $activity instanceof WC_GC_Activity_Data ) {
			$this->set_all( $activity->get_data() );
		} elseif ( is_array( $activity ) ) {
			$this->set_all( $activity );
		} else {
			$this->read( $activity );
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
	 * Get type.
	 *
	 * @return string
	 */
	public function get_type() {
		return $this->data['type'];
	}

	/**
	 * Get user id.
	 *
	 * @return int
	 */
	public function get_user_id() {
		return absint( $this->data['user_id'] );
	}

	/**
	 * Get user email.
	 *
	 * @return string
	 */
	public function get_user_email() {
		return $this->data['user_email'];
	}

	/**
	 * Get object id.
	 *
	 * @return int
	 */
	public function get_object_id() {
		return absint( $this->data['object_id'] );
	}

	/**
	 * Get gift card id.
	 *
	 * @return int
	 */
	public function get_gc_id() {
		return absint( $this->data['gc_id'] );
	}

	/**
	 * Get gift card code.
	 *
	 * @return string
	 */
	public function get_gc_code() {
		return $this->data['gc_code'];
	}

	/**
	 * Get amount.
	 *
	 * @return float
	 */
	public function get_amount() {
		return (float) $this->data['amount'];
	}

	/**
	 * Get date.
	 *
	 * @return int
	 */
	public function get_date() {
		return absint( $this->data['date'] );
	}

	/**
	 * Get note.
	 *
	 * @return string
	 */
	public function get_note() {
		return $this->data['note'];
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
			if ( is_callable( array( $this, "set_$key" ) ) ) {
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
	 * Set code.
	 *
	 * @param  string
	 * @return void
	 */
	public function set_type( $value ) {
		$this->data['type'] = $value;
	}

	/**
	 * Set user ID.
	 *
	 * @param  int
	 * @return void
	 */
	public function set_user_id( $value ) {
		$this->data['user_id'] = absint( $value );
	}

	/**
	 * Set user email.
	 *
	 * @param  string
	 * @return void
	 */
	public function set_user_email( $value ) {
		$this->data['user_email'] = $value;
	}

	/**
	 * Set object ID.
	 *
	 * @param  int
	 * @return void
	 */
	public function set_object_id( $value ) {
		$this->data['object_id'] = absint( $value );
	}

	/**
	 * Set gift card ID.
	 *
	 * @param  int
	 * @return void
	 */
	public function set_gc_id( $value ) {
		$this->data['gc_id'] = absint( $value );
	}

	/**
	 * Set gift card code.
	 *
	 * @param  string
	 * @return void
	 */
	public function set_gc_code( $value ) {
		$this->data['gc_code'] = $value;
	}

	/**
	 * Set amount.
	 *
	 * @param  float
	 * @return void
	 */
	public function set_amount( $value ) {
		$this->data['amount'] = wc_format_decimal( (float) $value );
	}

	/**
	 * Set date.
	 *
	 * @param  int
	 * @return void
	 */
	public function set_date( $value ) {
		$this->data['date'] = absint( $value );
	}

	/**
	 * Set note.
	 *
	 * @param  string
	 * @return void
	 */
	public function set_note( $value ) {
		$this->data['note'] = $value;
	}

	/*
	---------------------------------------------------*/
	/*
		Utilities.                                       */
	/*---------------------------------------------------*/

	/**
	 * Is type.
	 *
	 * @since 1.10.0
	 *
	 * @param  string|array $types
	 * @return string
	 */
	public function is_type( $types ) {

		if ( ! is_array( $types ) ) {
			$types = array( $types );
		}

		return in_array( $this->data['type'], $types );
	}

	/*
	---------------------------------------------------*/
	/*
		CRUD.                                            */
	/*---------------------------------------------------*/

	/**
	 * Insert data into the database.
	 */
	private function create() {
		global $wpdb;

		$data = array(
			'type'       => $this->get_type(),
			'user_id'    => $this->get_user_id(),
			'user_email' => $this->get_user_email(),
			'object_id'  => $this->get_object_id(),
			'gc_id'      => $this->get_gc_id(),
			'gc_code'    => $this->get_gc_code(),
			'amount'     => $this->get_amount(),
			'date'       => $this->get_date(),
			'note'       => $this->get_note(),
		);

		$wpdb->insert( $wpdb->prefix . 'woocommerce_gc_activity', $data, array( '%s', '%d', '%s', '%d', '%d', '%s', '%s', '%d', '%s' ) );

		$this->set_id( $wpdb->insert_id );
	}

	/**
	 * Update data in the database.
	 */
	private function update() {
		global $wpdb;

		$data = array(
			'type'       => $this->get_type(),
			'user_id'    => $this->get_user_id(),
			'user_email' => $this->get_user_email(),
			'object_id'  => $this->get_object_id(),
			'gc_id'      => $this->get_gc_id(),
			'gc_code'    => $this->get_gc_code(),
			'amount'     => $this->get_amount(),
			'date'       => $this->get_date(),
			'note'       => $this->get_note(),
		);

		$updated = $wpdb->update( $wpdb->prefix . 'woocommerce_gc_activity', $data, array( 'id' => $this->get_id() ), array( '%s', '%d', '%s', '%d', '%d', '%s', '%s', '%d', '%s' ) );

		do_action( 'woocommerce_gc_update_activity', $this );

		return $updated;
	}

	/**
	 * Delete data from the database.
	 */
	public function delete() {

		if ( $this->get_id() ) {
			global $wpdb;

			do_action( 'woocommerce_gc_before_delete_activity', $this );

			// Delete and clean up.
			$wpdb->delete( $wpdb->prefix . 'woocommerce_gc_activity', array( 'id' => $this->get_id() ) );

			do_action( 'woocommerce_gc_delete_activity', $this );
		}
	}

	/**
	 * Save data to the database.
	 *
	 * @return int
	 */
	public function save() {

		$this->validate();

		if ( ! $this->get_id() ) {
			$this->create();
		} else {
			$this->update();
		}

		return $this->get_id();
	}

	/**
	 * Read from DB object using ID.
	 *
	 * @param  int $activity
	 * @return void
	 */
	public function read( $activity ) {
		global $wpdb;

		if ( is_numeric( $activity ) && ! empty( $activity ) ) {
			$data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woocommerce_gc_activity WHERE id = %d LIMIT 1;", $activity ) );
		} elseif ( ! empty( $activity->id ) ) {
			$data = $activity;
		} else {
			$data = false;
		}

		if ( $data ) {
			$this->set_all( $data );
		}
	}

	/**
	 * Validates before saving for sanity.
	 */
	public function validate() {
		// ...
	}
}
