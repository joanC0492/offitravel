<?php
/**
 * WC_GC_Abilities class
 *
 * @package  WooCommerce Gift Cards
 * @since    2.7.4
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers WooCommerce Gift Cards abilities.
 *
 * @class    WC_GC_Abilities
 * @version  2.7.4
 */
class WC_GC_Abilities {

	/**
	 * Hook into the Abilities API lifecycle.
	 */
	public static function init() {
		if ( interface_exists( '\\Automattic\\WooCommerce\\Abilities\\AbilityDefinition' ) ) {
			require_once WC_GC_ABSPATH . 'includes/class-wc-gc-gift-cards-query-ability.php';

			add_filter( 'woocommerce_ability_definition_classes', array( __CLASS__, 'add_ability_definition_classes' ) );
		}
	}

	/**
	 * Adds Gift Cards ability definitions to WooCommerce's ability loader.
	 *
	 * @param  array $classes Ability definition class names.
	 * @return array
	 */
	public static function add_ability_definition_classes( array $classes ) {
		$classes[] = WC_GC_Gift_Cards_Query_Ability::class;

		return $classes;
	}
}

WC_GC_Abilities::init();
