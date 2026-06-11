<?php

/**
 * Class WOOMULTI_CURRENCY_F_Plugin_Staggs
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WOOMULTI_CURRENCY_F_Plugin_Staggs {
	protected $settings;

	public function __construct() {

		$this->settings = WOOMULTI_CURRENCY_F_Data::get_ins();
		if ( $this->settings->get_enable() ) {
			add_filter( 'staggs_cart_price', array( $this, 'staggs_cart_price' ), 10, 3 );
//			add_filter( 'staggs_configurator_price_totals', array( $this, 'staggs_configurator_price_totals' ), 10, 2 );
//			add_filter( 'staggs_configurator_before_price_totals', array( $this, 'staggs_configurator_before_price_totals' ), 10, 2 );
			add_filter( 'staggs_sanitized_attribute_item', array( $this, 'staggs_sanitized_attribute_item' ), 10 );
		}
	}

	public function staggs_cart_price( $price, $cart_item, $cart ) {

		return wmc_revert_price( $price );
	}

	public function staggs_configurator_price_totals( $filtered_post_options, $post_id ) {
		$ft_return = $filtered_post_options;
		if ( ! is_array( $filtered_post_options ) || ! isset( $filtered_post_options['options'] ) || ! is_array( $filtered_post_options['options'] ) ) {
			return $filtered_post_options;
		}
		foreach ( $ft_return['options'] as $ft_key => $ft_option ) {
			if ( ! is_array( $ft_option ) || ! isset( $ft_option['price'] ) ) {
				continue;
			}
			$ft_return['options'][$ft_key]['price'] = wmc_get_price( floatval( $ft_option['price'] ) );
		}

		return $ft_return;
	}

	public function staggs_configurator_before_price_totals( $filtered_post_options, $post_id ) {
		$ft_return = $filtered_post_options;
		if ( ! is_array( $filtered_post_options ) || ! isset( $filtered_post_options['options'] ) || ! is_array( $filtered_post_options['options'] ) ) {
			return $filtered_post_options;
		}
		foreach ( $ft_return['options'] as $ft_key => $ft_option ) {
			if ( ! is_array( $ft_option ) || ! isset( $ft_option['price'] ) ) {
				continue;
			}
			$ft_return['options'][$ft_key]['price'] = wmc_get_price( floatval( $ft_option['price'] ) );
		}

		return $ft_return;
	}

	public function staggs_sanitized_attribute_item( $sanitized ) {
		if ( 'no' == $sanitized['base_price'] ) {
			$item_price = $sanitized['price'] ? wmc_get_price( $sanitized['price'] ) : $sanitized['price'];
			$sanitized['price'] = $item_price;
		}
		return $sanitized;
	}
}