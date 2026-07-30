<?php
/**
 * WC_GC_Gift_Cards class
 *
 * @package  WooCommerce Gift Cards
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gift Card Collection Controller class.
 *
 * @class    WC_GC_Gift_Cards
 * @version  2.7.2
 */
class WC_GC_Gift_Cards {

	/**
	 * Get all active Gift Cards from session.
	 *
	 * @return array
	 */
	public function get() {
		$giftcards = WC()->session->get( '_wc_gc_giftcards' );
		return (array) apply_filters( 'woocommerce_gc_session_giftcards', $giftcards );
	}

	/**
	 * Fetch from session applied giftcards through the inline forms.
	 *
	 * @return array
	 */
	public function get_applied_giftcards_from_session() {
		$cache_key         = WC_Cache_Helper::get_transient_version( 'applied_giftcards' ) . '_wc_gc_applied_giftcards';
		$applied_giftcards = WC()->session->get( $cache_key );
		if ( ! is_array( $applied_giftcards ) ) {
			$applied_giftcards = array();
		}

		return $applied_giftcards;
	}

	/**
	 * Apply a gift card to session.
	 *
	 * @throws Exception
	 *
	 * @param  WC_GC_Gift_Card $giftcard
	 * @return void
	 */
	public function apply_giftcard_to_session( $giftcard ) {

		if ( ! is_object( $giftcard->data ) ) {
			throw new Exception( __( 'Gift card not found.', 'woocommerce-gift-cards' ) );
		}

		if ( ! $giftcard->is_active() ) {
			throw new Exception( __( 'Gift card disabled.', 'woocommerce-gift-cards' ) );
		}

		if ( $giftcard->has_expired() ) {
			throw new Exception( __( 'Gift card expired.', 'woocommerce-gift-cards' ) );
		}

		if ( $giftcard->is_redeemed() ) {
			throw new Exception( __( 'Gift card already linked with a customer account.', 'woocommerce-gift-cards' ) );
		}

		if ( $giftcard->get_balance() == 0 ) {

			// Check for pending balance.
			if ( $giftcard->get_pending_balance() > 0 ) {
				$notice = wc_gc_get_pending_balance_resolution( $giftcard, 'notice' );
				throw new Exception( $notice );
			}

			throw new Exception( __( 'Gift card has no remaining balance.', 'woocommerce-gift-cards' ) );
		}

		/**
		 * Filter to allow custom rules before applying a gift card to the session.
		 *
		 * @since 2.7.2
		 *
		 * @param bool $allow Whether to allow applying a gift card to the session.
		 * @param WC_GC_Gift_Card $giftcard The gift card object.
		 * @return null|mixed Null proceeds, anything else throws exception.
		 */
		$pre = apply_filters( 'woocommerce_gc_pre_apply_gift_card_to_session', null, $giftcard );
		if ( ! is_null( $pre ) ) {
			throw new Exception( esc_html__( 'Gift card cannot be applied.', 'woocommerce-gift-cards' ) );
		}

		// Add to session.
		$cache_key         = WC_Cache_Helper::get_transient_version( 'applied_giftcards' ) . '_wc_gc_applied_giftcards';
		$applied_giftcards = $this->get_applied_giftcards_from_session();
		if ( isset( $applied_giftcards[ $giftcard->get_id() ] ) ) {
			throw new Exception( __( 'Gift card is currently used.', 'woocommerce-gift-cards' ) );
		}

		$applied_giftcards[ $giftcard->get_id() ] = $giftcard->data;
		WC()->session->set( $cache_key, $applied_giftcards );

		do_action( 'woocommerce_gc_gift_card_applied', $giftcard );
	}

	/**
	 * Remove an applied Gift Card from the session.
	 *
	 * @param  int $id
	 * @return void
	 */
	public function remove_giftcard_from_session( $id ) {
		$cache_key         = WC_Cache_Helper::get_transient_version( 'applied_giftcards' ) . '_wc_gc_applied_giftcards';
		$applied_giftcards = $this->get_applied_giftcards_from_session();

		if ( isset( $applied_giftcards[ $id ] ) ) {
			unset( $applied_giftcards[ $id ] );
		}

		WC()->session->set( $cache_key, $applied_giftcards );
	}

	/**
	 * Return giftcards needed to cover the given balance.
	 *
	 * @param  float $balance
	 * @param  array $giftcards
	 * @return array
	 */
	public function cover_balance( $balance, $giftcards = null ) {

		$usage_data = array(
			'giftcards'    => array(),
			'total_amount' => 0.0,
		);

		if ( empty( $balance ) ) {
			return $usage_data;
		}

		$used_giftcards  = array();
		$covered_balance = 0.0;
		$giftcards       = is_null( $giftcards ) ? WC_GC()->account->get_active_giftcards_from_session() : $giftcards;
		usort( $giftcards, array( $this, 'compare_giftcards' ) );

		if ( $giftcards ) {
			foreach ( $giftcards as $giftcard_data ) {

				if ( $giftcard_data->get_balance() <= 0 ) {
					continue;
				}

				$fee_to_apply = $giftcard_data->get_balance();

				// Add to covered balance.
				$covered_balance += $fee_to_apply;

				// Maybe more? then flatten...
				if ( $covered_balance > $balance ) {
					$fee_to_apply    = $fee_to_apply - ( $covered_balance - $balance );
					$covered_balance = $balance;
				}

				// Gift card is used.
				$used_giftcards[] = array(
					'giftcard' => $giftcard_data,
					'amount'   => $fee_to_apply,
				);

				if ( $covered_balance === $balance ) {
					// Covered.
					break;
				}
			}
		}

		$usage_data['giftcards']    = $used_giftcards;
		$usage_data['total_amount'] = $covered_balance;

		return $usage_data;
	}


	/**
	 * Order giftcards to be used by priority.
	 *
	 * @param  WC_GC_Gift_Card_Data $a
	 * @param  WC_GC_Gift_Card_Data $b
	 * @return int
	 */
	private function compare_giftcards( $a, $b ) {
		$A_WINS = 1;
		$B_WINS = -1;
		$ΕQUAL  = 0;

		// First order parameter is expire date.
		if ( $a->get_expire_date() > $b->get_expire_date() ) {
			return $A_WINS;
		} elseif ( $a->get_expire_date() > $b->get_expire_date() ) {
			return $B_WINS;
		} else {
			return ( $a->get_balance() > $b->get_balance() ) ? $A_WINS : $B_WINS;
		}

		return $EQUAL;
	}
}
