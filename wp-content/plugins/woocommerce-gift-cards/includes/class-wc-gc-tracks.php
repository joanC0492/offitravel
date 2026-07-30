<?php
/**
 * WC_GC_Tracks class
 *
 * @package  WooCommerce Gift Cards
 * @since    1.15.6
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tracks support.
 *
 * @class    WC_GC_Tracks
 * @version  1.15.6
 */
class WC_GC_Tracks {

	/**
	 * Tracks event name prefix.
	 */
	const PREFIX = 'gc_';

	/**
	 * Hook in.
	 */
	public static function init() {

		// Record event on product creation.
		add_action( 'woocommerce_before_product_object_save', array( __CLASS__, 'record_gift_card_product_created_event' ) );

		// Record event on order creation.
		add_action( 'woocommerce_before_order_object_save', array( __CLASS__, 'record_order_with_gift_cards_created_event' ) );
		add_action( 'woocommerce_store_api_checkout_order_processed', array( __CLASS__, 'record_order_with_gift_cards_created_event' ) );
	}

	/**
	 * Include necessary classes.
	 *
	 * @return void
	 */
	private static function maybe_include_tracks_classes() {

		if ( ! class_exists( 'WC_Tracks' ) ) {

			if ( ! defined( 'WC_ABSPATH' ) || ! file_exists( WC_ABSPATH . 'includes/tracks/class-wc-tracks.php' ) ) {
				return;
			}

			include_once WC_ABSPATH . 'includes/tracks/class-wc-tracks.php';
			include_once WC_ABSPATH . 'includes/tracks/class-wc-tracks-event.php';
			include_once WC_ABSPATH . 'includes/tracks/class-wc-tracks-client.php';
			include_once WC_ABSPATH . 'includes/tracks/class-wc-tracks-footer-pixel.php';
			include_once WC_ABSPATH . 'includes/tracks/class-wc-site-tracking.php';
		}
	}

	/**
	 * Records a 'gift_card_product_created' event in Tracks every time a new gift card product is created.
	 *
	 * @param  WC_Product $product
	 * @return void
	 */
	public static function record_gift_card_product_created_event( $product ) {

		self::maybe_include_tracks_classes();

		// Bail early.
		if ( ! WC_Site_Tracking::is_tracking_enabled() ) {
			return;
		}

		if ( WC_GC_Gift_Card_Product::is_gift_card( $product ) ) {

			$exists_in_db = true;

			// This never seems to happen anymore when creating a product via the WP Dashboard.
			if ( $product->get_id() == 0 ) {

				$exists_in_db = false;

			} else {
				/*
				 * We have no better way to detect a new product. It seems that get_id() always returns a non-zero value when products are created manually.
				 * This seems to have something to do with WP creating an auto-draft before publishing a new product. The result is that the 'woocommerce_new_product' hook does not run.
				 */
								$exists_in_db = metadata_exists( 'post', $product->get_id(), '_gift_card' );
			}

			// First time saving this product?
			if ( ! $exists_in_db ) {
				self::record_event( 'gift_card_product_created' );
			}
		}
	}

	/**
	 * Records an 'order_with_gift_card' event in Tracks every time a new gift card product is created.
	 * Does not work for orders created via the Store API, because a draft order without any gift cards applied already exists by the time that a new order is saved.
	 *
	 * @param  WC_Order $order
	 * @return void
	 */
	public static function record_order_with_gift_cards_created_event( $order ) {

		self::maybe_include_tracks_classes();

		// Bail early.
		if ( ! WC_Site_Tracking::is_tracking_enabled() ) {
			return;
		}

		// Exit when updating an existing order.
		if ( ! doing_action( 'woocommerce_store_api_checkout_order_processed' ) && $order->get_id() > 0 ) {
			return;
		}

		$order_giftcards = $order->get_items( 'gift_card' );

		if ( ! empty( $order_giftcards ) ) {
			self::record_event( 'order_with_gift_cards_created' );
		}
	}

	/**
	 * Record an event in Tracks - this is the preferred way to record events from PHP.
	 *
	 * @param string $event_name The name of the event.
	 * @param array  $props Custom properties to send with the event.
	 * @return bool|WP_Error True for success or WP_Error if the event pixel could not be fired.
	 */
	public static function record_event( $event_name, $props = array() ) {
		$full_event_name = self::PREFIX . $event_name;
		WC_Tracks::record_event( $full_event_name, $props );
	}
}

WC_GC_Tracks::init();
