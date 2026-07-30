<?php
/**
 * WC_GC_Meta_Box_Product_Data class
 *
 * @package  WooCommerce Gift Cards
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin AJAX meta-box handlers.
 *
 * @class    WC_GC_Meta_Box_Product_Data
 * @version  2.0.0
 */
class WC_GC_Meta_Box_Product_Data {

	/**
	 * Hook in.
	 */
	public static function init() {

		// Notices.

		// Add HTML Fields.
		add_filter( 'product_type_options', array( __CLASS__, 'add_gift_card_checkbox' ) );
		add_action( 'woocommerce_product_options_general_product_data', array( __CLASS__, 'add_gift_card_inputs' ) );
		add_action( 'woocommerce_product_after_variable_attributes', array( __CLASS__, 'add_gift_card_variation_inputs' ), 10, 3 );

		// Process admin fields.
		add_action( 'woocommerce_admin_process_product_object', array( __CLASS__, 'process_post_data' ) );

		add_action( 'woocommerce_admin_process_variation_object', array( __CLASS__, 'process_variation_post_data' ), 10, 2 );
	}

	/*
	---------------------------------------------------*/
	/*
		Admin Settings & Handlers.                       */
	/*---------------------------------------------------*/

	/**
	 * Add checkbox in product type options.
	 *
	 * @param  array $actions
	 * @return array
	 */
	public static function add_gift_card_checkbox( $actions ) {
		global $product_object;

		$wrapper_classes = array();
		foreach ( wc_gc_get_product_types_allowed() as $type ) {
			$wrapper_classes[] = 'show_if_' . $type;
		}

		$wrapper_classes[] = 'hide_if_bundle';
		$wrapper_classes[] = 'hide_if_composite';
		$wrapper_classes[] = 'gift_card_checkbox';

		$actions['gift_card'] = array(
			'id'            => '_gift_card',
			'wrapper_class' => implode( ' ', $wrapper_classes ),
			'label'         => __( 'Gift Card', 'woocommerce-gift-cards' ),
			'description'   => __( 'Gift cards are virtual products that can be purchased by customers and gifted to one or more recipients. Gift card code holders can redeem and use them as store credit.', 'woocommerce-gift-cards' ),
			'default'       => WC_GC_Gift_Card_Product::is_gift_card( $product_object ) || ( isset( $_GET['tutorial_type'] ) && 'gift-card' === $_GET['tutorial_type'] ) ? 'yes' : 'no',
		);

		return $actions;
	}

	/**
	 * Print inputs in product data general tab.
	 *
	 * @return void
	 */
	public static function add_gift_card_inputs() {
		global $product_object;

		?><div class="options_group show_if_giftcard" id="giftcard_settings">
		<div class="hr-section hr-section-components"><?php echo esc_html__( 'Gift Card', 'woocommerce-gift-cards' ); ?></div>
																<?php
																	// Nonce.
																	wp_nonce_field( 'giftcard_meta_data', 'security' );

																	// Expiration.
																	$expiration_days = absint( $product_object->get_meta( '_gift_card_expiration_days', true ) );
																	woocommerce_wp_text_input(
																		array(
																			'id'          => '_gift_card_expiration_days',
																			'value'       => $expiration_days > 0 ? $expiration_days : false,
																			'label'       => __( 'Days to expire', 'woocommerce-gift-cards' ),
																			'placeholder' => _x( 'Never', 'placeholder', 'woocommerce-gift-cards' ),
																			'description' => __( 'Period of time given to gift card recipients to redeem or use their code before it becomes inactive. Leave this field empty to keep the issued gift card codes active indefinitely.', 'woocommerce-gift-cards' ),
																			'desc_tip'    => 'true',
																		)
																	);

																// Template.
																$template = WC_GC()->emails->get_template_by_product( $product_object );

																?>
			<div id="wc_gc_template_admin_fields">
			<?php
				echo $template->get_admin_product_fields_html( $product_object ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>
			</div></div>
		<?php
		// show_if_giftcard
	}

	/**
	 * Print inputs in variation data form.
	 *
	 * @since 1.2.0
	 *
	 * @param  int     $loop
	 * @param  array   $variation_data
	 * @param  WP_Post $variation
	 * @return void
	 */
	public static function add_gift_card_variation_inputs( $loop, $variation_data, $variation ) {

		if ( isset( $variation->ID ) ) {
			$variation = wc_get_product( $variation->ID );
		}

		// Template.
		$template = WC_GC()->emails->get_template_by_product( $variation );

		?>
		<div class="options_group show_if_giftcard"><div id="wc_gc_template_admin_fields">
			<?php
				echo $template->get_admin_product_fields_html( $variation, $loop ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>
			</div></div>
		<?php
		// show_if_giftcard
	}

	/**
	 * Add checkbox in product type options.
	 *
	 * @param  WC_Product $product
	 * @return void
	 */
	public static function process_post_data( $product ) {

		if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( wc_clean( $_POST['security'] ), 'giftcard_meta_data' ) ) {
			return;
		}

		if ( ! $product->is_type( wc_gc_get_product_types_allowed() ) ) {
			return;
		}

		do_action( 'woocommerce_gc_before_process_post_data', $product );

		// Is gift card.
		if ( isset( $_POST['_gift_card'] ) ) {

			$product->update_meta_data( '_gift_card', 'yes' );

			// Clear dismissible welcome notice.
			WC_GC_Admin_Notices::remove_dismissible_notice( 'welcome' );

			// Expiration date.
			if ( isset( $_POST['_gift_card_expiration_days'] ) ) {
				$product->update_meta_data( '_gift_card_expiration_days', absint( $_POST['_gift_card_expiration_days'] ) );
			}

			if ( $product->is_taxable() ) {

				$notice = '';

				if ( $product->is_type( 'simple' ) ) {
					/* translators: documentation link */
					$notice = sprintf( __( '<a href="%s" target="_blank">Multi-purpose</a> prepaid gift cards are not taxable at purchase. Tax will be assessed on any products purchased when this gift card is redeemed. Please choose <strong>Tax Status > None</strong> under <strong>Product Data > General</strong>, or make sure that no tax will be calculated when this gift card is purchased.', 'woocommerce-gift-cards' ), WC_GC()->get_resource_url( 'guide-multi-prepaid-tax' ) );
				} elseif ( $product->is_type( 'variable' ) ) {
					/* translators: documentation link */
					$notice = sprintf( __( '<a href="%s" target="_blank">Multi-purpose</a> prepaid gift cards are not taxable at purchase. Tax will be assessed on any products purchased when this gift card is redeemed. Please choose <strong>Tax Status > None</strong> under <strong>Product Data > General</strong>, or make sure that no tax will be calculated when any variation of this gift card is purchased.', 'woocommerce-gift-cards' ), WC_GC()->get_resource_url( 'guide-multi-prepaid-tax' ) );
				}

				if ( $notice ) {
					WC_GC_Admin_Notices::add_notice( $notice, 'warning', true );
				}
			}

			// Template.
			$template = WC_GC()->emails->get_template_by_product( $product );

			try {
				$template->process_product_data( $product );

			} catch ( Exception $e ) {
				WC_Admin_Meta_Boxes::add_error( $e->getMessage() );
			}
		} else {
			$product->delete_meta_data( '_gift_card' );
		}

		do_action( 'woocommerce_gc_after_process_post_data', $product );
	}

	/**
	 * Process variation attributes.
	 *
	 * @since 1.2.0
	 *
	 * @param  int|WC_Variation_Product $variation
	 * @param  int                      $index
	 * @return void
	 */
	public static function process_variation_post_data( $variation, $index ) {
		$is_legacy = false;

		if ( is_numeric( $variation ) ) {
			$variation = wc_get_product( $variation );
			$is_legacy = true;
		}

		do_action( 'woocommerce_gc_before_process_variation_post_data', $variation );

		// Template.
		$template = WC_GC()->emails->get_template_by_product( $variation );

		try {
			$template->process_variation_product_data( $variation, $index );

			if ( $is_legacy ) {
				$variation->save();
			}
		} catch ( Exception $e ) {
			WC_Admin_Meta_Boxes::add_error( $e->getMessage() );
		}

		do_action( 'woocommerce_gc_after_process_variation_post_data', $variation );
	}
}

WC_GC_Meta_Box_Product_Data::init();
