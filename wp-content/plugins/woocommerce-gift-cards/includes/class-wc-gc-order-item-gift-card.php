<?php
/**
 * WC_GC_Order_Item_Gift_Card class
 *
 * @package  WooCommerce Gift Cards
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gift Card Order Item Model class.
 *
 * @class    WC_GC_Order_Item_Gift_Card
 * @version  2.7.3
 */
class WC_GC_Order_Item_Gift_Card extends WC_Order_Item {

	/**
	 * Extra item data required for Gift Cards.
	 *
	 * @var array
	 */
	protected $extra_data = array(
		'giftcard_id' => 0,
		'code'        => '',
		'amount'      => 0,
	);

	/**
	 * Runtime cache of the giftcard object.
	 *
	 * @since 1.10.0
	 *
	 * @var array
	 */
	protected $giftcard;

	/*
	---------------------------------------------------*/
	/*
		Setters.                                         */
	/*---------------------------------------------------*/

	/**
	 * Set item amount.
	 *
	 * @param  string $value
	 * @return void
	 */
	public function set_name( $value ) {
		return $this->set_code( $value );
	}

	/**
	 * Set item amount.
	 *
	 * @param  string $value
	 * @return void
	 */
	public function set_code( $value ) {
		$this->set_prop( 'code', wc_clean( $value ) );
	}

	/**
	 * Set item amount.
	 *
	 * @param  float $value
	 * @return void
	 */
	public function set_amount( $value ) {
		$this->set_prop( 'amount', wc_format_decimal( (float) $value ) );
	}

	/**
	 * Set giftcard id.
	 *
	 * @param  int $value
	 * @return void
	 */
	public function set_giftcard_id( $value ) {
		$this->set_prop( 'giftcard_id', absint( $value ) );
	}

	/*
	---------------------------------------------------*/
	/*
		Getters.                                         */
	/*---------------------------------------------------*/

	/**
	 * Magic getters.
	 *
	 * @param string $name Property name.
	 * @return mixed
	 */
	public function &__get( $name ) {
		$value = '';

		switch ( $name ) {
			case 'code':
				$value = $this->get_code();
				break;
		}

		return $value;
	}

	/**
	 * Get name.
	 *
	 * @return string
	 */
	public function get_name( $context = 'view' ) {
		return $this->get_code( $context );
	}

	/**
	 * Get type.
	 *
	 * @return string
	 */
	public function get_type() {
		return 'gift_card';
	}

	/**
	 * Get code.
	 *
	 * @return string
	 */
	public function get_code( $context = 'view' ) {
		return $this->get_prop( 'code', $context );
	}

	/**
	 * Get type.
	 *
	 * @return float
	 */
	public function get_amount( $context = 'view' ) {
		return (float) $this->get_prop( 'amount', $context );
	}

	/**
	 * Get giftcard id.
	 *
	 * @return float
	 */
	public function get_giftcard_id( $context = 'view' ) {
		return (int) $this->get_prop( 'giftcard_id', $context );
	}

	/**
	 * Get giftcard.
	 *
	 * @since 1.10.0
	 *
	 * @return float
	 */
	public function get_giftcard() {

		if ( ! is_a( $this->giftcard, 'WC_GC_Gift_Card_Data' ) ) {
			$this->giftcard = wc_gc_get_gift_card( $this->get_giftcard_id() );
		}

		return $this->giftcard;
	}

	/**
	 * Get captured balance amount.
	 *
	 * @since 1.10.0
	 *
	 * @return float
	 */
	public function get_captured_amount( $context = 'view' ) {

		$cache_key = WC_Cache_Helper::get_cache_prefix( 'orders' ) . 'gc_total_captured_' . $this->get_giftcard_id() . '_' . $this->get_order_id();

		/**
		 * Use this filter to debug issues with the object cache for the total captured amount by gift cards.
		 *
		 * @since 1.10.0
		 *
		 * @param  bool  $debug
		 * @return bool
		 */
		$use_cache   = ! apply_filters( 'WC_GC_DEBUG_ORDER_ITEM_CAPTURED_CACHE', false );
		$force_query = 'db' === $context;
		$cached_data = wp_cache_get( $cache_key, 'order-items' );

		if ( ! $force_query && $use_cache && is_numeric( $cached_data ) ) {
			return $cached_data;
		}

		$total_captured = (float) WC_GC()->db->activity->get_gift_card_captured_amount(
			array(
				'giftcard_id' => $this->get_giftcard_id(),
				'order_id'    => $this->get_order_id(),
			)
		);

		// Sanity normalize.
		if ( $total_captured > $this->get_amount() ) {
			$total_captured = $this->get_amount();
		}

		wp_cache_set( $cache_key, $total_captured, 'order-items' );

		return $total_captured;
	}

	/**
	 * Get refunded balance amount.
	 *
	 * @since 1.10.0
	 *
	 * @return float
	 */
	public function get_refunded_amount( $context = 'view' ) {

		$cache_key = WC_Cache_Helper::get_cache_prefix( 'orders' ) . 'gc_total_refunded_' . $this->get_giftcard_id() . '_' . $this->get_order_id();

		/**
		 * Use this filter to debug issues with the object cache for the total refunded amount by gift cards.
		 *
		 * @since 1.10.0
		 *
		 * @param  bool  $debug
		 * @return bool
		 */
		$use_cache   = ! apply_filters( 'WC_GC_DEBUG_ORDER_ITEM_REFUNDED_CACHE', false );
		$force_query = 'db' === $context;
		$cached_data = wp_cache_get( $cache_key, 'order-items' );

		if ( ! $force_query && $use_cache && is_numeric( $cached_data ) ) {
			return $cached_data;
		}

		$order = wc_get_order( $this->get_order_id() );
		if ( ! is_a( $order, 'WC_Order' ) ) {
			return 0.0;
		}

		$refunds = wc_get_orders(
			array(
				'type'   => 'shop_order_refund',
				'parent' => $order->get_id(),
				'limit'  => -1,
			)
		);

		if ( empty( $refunds ) ) {
			return 0.0;
		}

		$total_refunded = 0.0;
		foreach ( $refunds as $refund ) {
			$activity_ids = $refund->get_meta( '_wc_gc_refund_activities', true );
			if ( empty( $activity_ids ) || ! is_array( $activity_ids ) ) {
				continue;
			} else {

				foreach ( $activity_ids as $activity_id ) {
					$activity = WC_GC()->db->activity->get( $activity_id );
					if ( $this->get_giftcard_id() !== $activity->get_gc_id() ) {
						continue;
					}

					$total_refunded += $activity->get_amount();
				}
			}
		}

		// Sanity normalize.
		if ( $total_refunded > $this->get_amount() ) {
			$total_refunded = $this->get_amount();
		}

		wp_cache_set( $cache_key, (float) $total_refunded, 'order-items' );

		return (float) $total_refunded;
	}

	/*
	---------------------------------------------------*/
	/*
		Utilities.                                       */
	/*---------------------------------------------------*/
}
