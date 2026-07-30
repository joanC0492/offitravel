<?php
/**
 * WC_GC_Gift_Card_Product class
 *
 * @package  WooCommerce Gift Cards
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gift Card Product view controller.
 *
 * @class    WC_GC_Gift_Card_Product
 * @version  2.7.2
 */
class WC_GC_Gift_Card_Product {

	/**
	 * Constructor.
	 */
	public static function init() {

		// Template.
		add_action( 'woocommerce_before_add_to_cart_button', array( __CLASS__, 'handle_simple_gift_card_form' ), 9 );
		add_action( 'woocommerce_before_single_variation', array( __CLASS__, 'handle_variable_gift_card_form' ), 9 );

		add_filter( 'woocommerce_post_class', array( __CLASS__, 'add_single_product_container_class' ), 10, 2 );
		add_filter( 'woocommerce_product_add_to_cart_text', array( __CLASS__, 'change_add_to_cart_text' ), 10, 2 );
		add_filter( 'woocommerce_product_add_to_cart_url', array( __CLASS__, 'add_to_cart_url' ), 10, 2 );
		add_filter( 'woocommerce_product_supports', array( __CLASS__, 'supports_ajax_add_to_cart' ), 10, 3 );

		// Cart.
		add_filter( 'woocommerce_cart_item_permalink', array( __CLASS__, 'cart_item_permalink' ), 10, 3 );
		add_filter( 'woocommerce_product_has_options', array( __CLASS__, 'handle_has_options' ), 10, 2 );

		// Cart quantity.
		add_filter( 'woocommerce_add_cart_item', array( __CLASS__, 'add_cart_item' ) );
		add_filter( 'woocommerce_cart_item_quantity', array( __CLASS__, 'manipulate_cart_quantity_input' ), 10, 3 );
		add_filter( 'woocommerce_update_cart_validation', array( __CLASS__, 'update_cart_validation' ), 10, 4 );

		// Add-to-cart.
		add_filter( 'woocommerce_add_to_cart_validation', array( __CLASS__, 'validate_add_to_cart' ), 10, 6 );
		add_filter( 'woocommerce_add_cart_item_data', array( __CLASS__, 'add_cart_item_data' ), 10, 4 );
		add_filter( 'woocommerce_get_item_data', array( __CLASS__, 'get_item_data' ), 10, 2 );

		// Order.
		add_filter( 'woocommerce_order_again_cart_item_data', array( __CLASS__, 'order_again_add_cart_item_data' ), 10, 3 );
		add_action( 'before_delete_post', array( __CLASS__, 'before_delete_post' ), 0 );
		add_action( 'woocommerce_rest_check_permissions', array( __CLASS__, 'before_delete_rest_post' ), 0, 4 );

		// Order items.
		add_filter( 'woocommerce_order_item_display_meta_key', array( __CLASS__, 'order_item_display_meta_key' ), 10, 3 );
		add_filter( 'woocommerce_order_item_display_meta_value', array( __CLASS__, 'order_item_display_meta_value' ), 10, 3 );
		add_filter( 'woocommerce_hidden_order_itemmeta', array( __CLASS__, 'order_item_hide_meta' ) );
		add_filter( 'woocommerce_order_item_permalink', array( __CLASS__, 'order_item_permalink' ), 10, 3 );
		add_filter( 'woocommerce_order_item_get_formatted_meta_data', array( __CLASS__, 'order_item_add_formatted_meta_again' ), 10, 2 );

		// Handle Gift Cards.
		add_action( 'woocommerce_checkout_create_order_line_item', array( __CLASS__, 'create_order_line_item' ), 10, 4 );

		// Create and Activate.
		add_action( 'woocommerce_payment_complete', array( __CLASS__, 'create_order_gift_cards' ), 10 );
		add_action( 'woocommerce_order_status_completed', array( __CLASS__, 'create_order_gift_cards' ), 10, 2 );

		// Deactivate.
		add_action( 'woocommerce_order_status_cancelled', array( __CLASS__, 'deactivate_order_gift_cards' ), 10, 2 );
		add_action( 'woocommerce_order_status_failed', array( __CLASS__, 'deactivate_order_gift_cards' ), 10, 2 );
		add_action( 'woocommerce_order_status_refunded', array( __CLASS__, 'deactivate_order_gift_cards' ), 10, 2 );

		// Hide qty selector in product page.
		add_filter( 'render_block_woocommerce/add-to-cart-with-options-quantity-selector', array( __CLASS__, 'disable_qty_input_block' ), 10 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'hide_stepper_qty_input' ), 20 );
		add_filter( 'woocommerce_quantity_input_type', array( __CLASS__, 'set_qty_input_hidden' ), 20 );
	}

	/*
	---------------------------------------------------*/
	/*
		Getters.                                         */
	/*---------------------------------------------------*/

	/**
	 * Get form fields.
	 *
	 * @return array
	 */
	public static function get_form_fields() {

		// Set-up form data.
		$form_fields = array(
			'wc_gc_giftcard_to'          => __( 'To', 'woocommerce-gift-cards' ),
			'wc_gc_giftcard_to_multiple' => __( 'To', 'woocommerce-gift-cards' ),
			'wc_gc_giftcard_cc'          => __( 'CC', 'woocommerce-gift-cards' ),
			'wc_gc_giftcard_from'        => __( 'From', 'woocommerce-gift-cards' ),
			'wc_gc_giftcard_message'     => __( 'Message', 'woocommerce-gift-cards' ),
			'wc_gc_giftcard_delivery'    => __( 'Delivery Date', 'woocommerce-gift-cards' ),
		);

		return (array) apply_filters( 'woocommerce_gc_form_fields', $form_fields );
	}

	/*
	---------------------------------------------------*/
	/*
		Front End Handlers.                              */
	/*---------------------------------------------------*/

	/**
	 * Handle gift card form templates on simple products.
	 *
	 * @since 1.6.2
	 *
	 * @return void
	 */
	public static function handle_simple_gift_card_form() {
		global $product;
		if ( ! is_a( $product, 'WC_Product' ) ) {
			return;
		}

		if ( $product->is_type( 'simple' ) ) {
			self::print_gift_card_form();
		}
	}

	/**
	 * Handle gift card form templates on variable products.
	 *
	 * @since 1.6.2
	 *
	 * @return void
	 */
	public static function handle_variable_gift_card_form() {
		global $product;
		if ( ! is_a( $product, 'WC_Product' ) ) {
			return;
		}

		if ( $product->is_type( 'variable' ) ) {
			self::print_gift_card_form();
		}
	}

	/**
	 * Print gift card form template.
	 *
	 * @return void
	 */
	public static function print_gift_card_form() {

		global $product;
		if ( ! is_a( $product, 'WC_Product' ) ) {
			return;
		}

		if ( $product->is_type( wc_gc_get_product_types_allowed() ) && self::is_gift_card( $product ) ) {

			WC_GC()->templates->enqueue_scripts();

			wc_get_template(
				'single-product/gift-card-form.php',
				array(
					'product' => $product,
				),
				false,
				WC_GC()->get_plugin_path() . '/templates/'
			);
		}
	}

	/**
	 * Add Gift Card container CSS class.
	 *
	 * @param  array      $classes
	 * @param  WC_Product $product
	 * @return array
	 */
	public static function add_single_product_container_class( $classes, $product ) {

		// Filter only single product.
		if ( ! did_action( 'woocommerce_before_single_product' ) ) {
			return $classes;
		}

		if ( self::is_gift_card( $product ) ) {
			$classes[] = 'wc_gc_giftcard_product';
		}

		return $classes;
	}

	/**
	 * Disable quantity selector block in product page for add to cart + options beta.
	 *
	 * @param  string $block_content The block content.
	 * @return string
	 */
	public static function disable_qty_input_block( $block_content ) {

		if ( ! is_product() ) {
			return $block_content;
		}

		global $product;

		if ( ! self::is_gift_card( $product ) ) {
			return $block_content;
		}

		/**
		 * `woocommerce_gc_disable_quantity_selector` filter.
		 * Disable quantity selector block in product page.
		 *
		 * @since  1.6.0
		 */
		if ( (bool) apply_filters( 'woocommerce_gc_disable_quantity_selector', true ) ) {
			return '';
		}

		return $block_content;
	}

	/**
	 * Add custom styles to hide Stepper quantity selector, when using add to cart + options.
	 *
	 * @since  2.7.2
	 *
	 * @return void
	 */
	public static function hide_stepper_qty_input() {

		if ( ! is_product() ) {
			return;
		}

		global $product;
		if ( ! is_object( $product ) ) {
			$product = wc_get_product( get_the_ID() );
		}

		if ( ! self::is_gift_card( $product ) ) {
			return;
		}

		/**
		 * `woocommerce_gc_disable_quantity_selector` filter.
		 * Disable quantity selector in product page.
		 *
		 * @since  1.6.0
		 */
		if ( (bool) apply_filters( 'woocommerce_gc_disable_quantity_selector', true ) ) {
			// Stepper quantity selector, when using add to cart + options.
			$inline_css = '.wc_gc_giftcard_product form.cart div.quantity.wc-block-components-quantity-selector{ display: none !important; }';

			wp_add_inline_style( 'wc-gc-css', $inline_css );
		}
	}


	/**
	 * Set quantity input type to hidden, when using add to cart + options and legacy theme.
	 *
	 * @since  2.7.2
	 *
	 * @param  string $type The input type.
	 * @return string
	 */
	public static function set_qty_input_hidden( $type ) {

		if ( ! is_product() ) {
			return $type;
		}

		global $product;
		if ( ! is_object( $product ) ) {
			$product = wc_get_product( get_the_ID() );
		}

		if ( ! self::is_gift_card( $product ) ) {
			return $type;
		}

		/**
		 * `woocommerce_gc_disable_quantity_selector` filter.
		 * Disable quantity selector in product page.
		 *
		 * @since  1.6.0
		 */
		if ( (bool) apply_filters( 'woocommerce_gc_disable_quantity_selector', true ) ) {
			return 'hidden';
		}

		return $type;
	}

	/**
	 * Modify add-to-cart button text.
	 *
	 * @param  string     $text
	 * @param  WC_Product $product
	 * @return string
	 */
	public static function change_add_to_cart_text( $text, $product ) {

		if ( self::is_gift_card( $product ) ) {
			$text = esc_html__( 'Buy gift card', 'woocommerce-gift-cards' );
		}

		return $text;
	}

	/**
	 * Filter Add-to-cart url.
	 *
	 * @param  string     $url
	 * @param  WC_Product $product
	 * @return string
	 */
	public static function add_to_cart_url( $url, $product ) {

		if ( self::is_gift_card( $product ) ) {
			$url = apply_filters( 'woocommerce_gc_add_to_cart_url', $product->get_permalink(), $product );
		}

		return $url;
	}

	/*
	---------------------------------------------------*/
	/*
		Add-to-cart.                                     */
	/*---------------------------------------------------*/

	/**
	 * Disable AJAX add-to-cart feature.
	 *
	 * @param  bool       $supports
	 * @param  string     $feature
	 * @param  WC_Product $product
	 * @return bool
	 */
	public static function supports_ajax_add_to_cart( $supports, $feature, $product ) {

		if ( 'ajax_add_to_cart' === $feature ) {

			if ( self::is_gift_card( $product ) ) {
				$supports = false;
			}
		}

		return $supports;
	}

	/**
	 * Get posted configuration.
	 *
	 * @param  array $posted_data (Optional)
	 * @return array
	 */
	public static function get_posted_gift_card_configuration( $posted_data = array() ) {

		$configuration = array();

		if ( empty( $posted_data ) ) {
			/*
			 * Choose between $_POST or $_GET for grabbing data.
			 */
			$posted_data = $_POST;

			if ( empty( $_POST['add-to-cart'] ) && ! empty( $_GET['add-to-cart'] ) ) {
				$posted_data = $_GET;
			}
		}

		foreach ( array_keys( self::get_form_fields() ) as $key ) {

			if ( ! isset( $posted_data[ $key ] ) ) {
				continue;
			}

			$value = '';
			switch ( $key ) {
				case 'wc_gc_giftcard_to':
					$value = sanitize_text_field( wp_unslash( $posted_data[ $key ] ) );
					break;
				case 'wc_gc_giftcard_to_multiple':
					$value = array_unique( wc_gc_parse_email_string( sanitize_text_field( wp_unslash( $posted_data[ $key ] ) ) ) );
					break;
				case 'wc_gc_giftcard_cc':
					$value = array_unique( wc_gc_parse_email_string( sanitize_text_field( wp_unslash( $posted_data[ $key ] ) ) ) );
					break;
				case 'wc_gc_giftcard_from':
					$value = sanitize_text_field( wp_unslash( $posted_data[ $key ] ) );
					break;
				case 'wc_gc_giftcard_message':
					$value = sanitize_textarea_field( wp_unslash( $posted_data[ $key ] ) );
					break;
				case 'wc_gc_giftcard_delivery':
					$value = absint( $posted_data[ $key ] );

					if ( ! empty( $value ) ) {

						if ( isset( $posted_data['_wc_gc_giftcard_delivery_gmt_offset'] ) ) {
							$configuration['_wc_gc_giftcard_delivery_gmt_offset'] = (float) $posted_data['_wc_gc_giftcard_delivery_gmt_offset'];
						} else {
							// Assume store's offset for outdated templates.
							$configuration['_wc_gc_giftcard_delivery_gmt_offset'] = (float) get_option( 'gmt_offset' );
						}
					}

					break;
				default:
					/**
					 * `woocommerce_gc_parse_custom_form_field` filter.
					 *
					 * Used to parse custom form fields.
					 *
					 * @since 1.6.0
					 *
					 * @param mixed  $value
					 * @param string $key
					 */
					$value = apply_filters( 'woocommerce_gc_parse_custom_form_field', $posted_data[ $key ], $key );
					break;
			}

			$configuration[ $key ] = $value;
		}

		return $configuration;
	}

	/**
	 * Add cart item data.
	 *
	 * @param  array $cart_item_data
	 * @param  int   $product_id
	 * @param  int   $variation_id
	 * @param  int   $quantity (Optional)
	 * @return array
	 */
	public static function add_cart_item_data( $cart_item_data, $product_id, $variation_id, $quantity = 0 ) {

		if ( ! is_array( $cart_item_data ) ) {
			return $cart_item_data;
		}

		$configuration  = self::get_posted_gift_card_configuration();
		$cart_item_data = array_merge( $cart_item_data, $configuration );

		return $cart_item_data;
	}

	/**
	 * Modify cart quanity when adding to cart multiple emails.
	 *
	 * @param  array $cart_item
	 * @return array
	 */
	public static function add_cart_item( $cart_item ) {

		if ( ! empty( $cart_item['wc_gc_giftcard_to_multiple'] ) && is_array( $cart_item['wc_gc_giftcard_to_multiple'] ) ) {
			// Modify quantity based on recipient emails.
			$cart_item['quantity'] = count( $cart_item['wc_gc_giftcard_to_multiple'] ) * $cart_item['quantity'];
		}

		return $cart_item;
	}

	/**
	 * Change the default quantity html for the cart item.
	 *
	 * @param  string $product_quantity_html
	 * @param  string $cart_item_key
	 * @param  array  $cart_item
	 * @return string
	 */
	public static function manipulate_cart_quantity_input( $product_quantity_html, $cart_item_key, $cart_item = null ) {

		if ( null === $cart_item ) {
			$cart_item = WC()->cart->cart_contents[ $cart_item_key ];
		}

		if ( ! empty( $cart_item['wc_gc_giftcard_to_multiple'] ) && is_array( $cart_item['wc_gc_giftcard_to_multiple'] ) ) {
			$emails_count          = count( $cart_item['wc_gc_giftcard_to_multiple'] );
			$product_quantity_html = woocommerce_quantity_input(
				array(
					'input_name'   => "cart[{$cart_item_key}][qty]",
					'input_value'  => $cart_item['quantity'],
					'max_value'    => $cart_item['data']->get_max_purchase_quantity(),
					'min_value'    => $emails_count,
					'product_name' => $cart_item['data']->get_name(),
					'step'         => $emails_count,
				),
				$cart_item['data'],
				false
			);
		}

		return $product_quantity_html;
	}

	/**
	 * Validates in-cart quantity changes.
	 *
	 * @param  bool   $passed
	 * @param  string $cart_item_key
	 * @param  array  $cart_item
	 * @param  int    $quantity
	 * @return bool
	 */
	public static function update_cart_validation( $passed, $cart_item_key, $cart_item, $quantity ) {

		if ( ! empty( $cart_item['wc_gc_giftcard_to_multiple'] ) && is_array( $cart_item['wc_gc_giftcard_to_multiple'] ) ) {
			$emails_count = count( $cart_item['wc_gc_giftcard_to_multiple'] );

			if ( $quantity % $emails_count ) {
				/* translators: %1$s: Product title %2$s: Quantity multiplier */
				wc_add_notice( sprintf( __( 'Cart update failed. The quantity of &quot;%1$s&quot; must be a multiple of %2$d.', 'woocommerce-gift-cards' ), $cart_item['data']->get_title(), $emails_count ), 'error' );
				return false;
			}
		}

		return $passed;
	}

	/**
	 * Disable one-click purchase for gift cards.
	 *
	 * @since 1.16.15
	 *
	 * @param  bool       $has_options
	 * @param  WC_Product $product
	 * @return bool
	 */
	public static function handle_has_options( $has_options, $product ) {
		return $has_options || self::is_gift_card( $product );
	}

	/**
	 * Change cart item permalink.
	 *
	 * @param  string $link
	 * @param  array  $cart_item
	 * @param  string $cart_item_key
	 * @return string
	 */
	public static function cart_item_permalink( $link, $cart_item, $cart_item_key ) {

		if ( ! empty( $link ) ) {
			foreach ( self::get_form_fields() as $key => $label ) {
				if ( strpos( $link, $key ) === false && isset( $cart_item[ $key ] ) ) {
					if ( is_array( $cart_item[ $key ] ) ) {
						$url_value = implode( ',', $cart_item[ $key ] );
					} elseif ( 'wc_gc_giftcard_delivery' === $key ) {
						$url_value = ! empty( $cart_item[ $key ] ) ? wc_gc_convert_timestamp_to_gmt_offset( $cart_item[ $key ], -1 * (float) $cart_item['_wc_gc_giftcard_delivery_gmt_offset'] ) : '';
					} elseif ( 'wc_gc_giftcard_message' === $key ) {
						$url_value = nl2br( $cart_item[ $key ] );
					} else {
						$url_value = $cart_item[ $key ];
					}

					$link = add_query_arg( $key, urlencode( $url_value ), $link );
				}
			}
		}

		return $link; // nosemgrep: audit.php.wp.security.xss.query-arg
	}

	/**
	 * Validate add-to-cart action.
	 *
	 * @param  boolean $passed
	 * @param  int     $product_id
	 * @param  int     $quantity
	 * @param  mixed   $variation_id
	 * @param  array   $variations
	 * @param  array   $cart_item_data
	 * @return boolean
	 */
	public static function validate_add_to_cart( $passed, $product_id, $quantity, $variation_id = '', $variations = array(), $cart_item_data = array() ) {

		// This needs to stay here to support WC core < 9.5. See https://github.com/woocommerce/woocommerce/pull/52486.
		if ( WC_GC_Core_Compatibility::is_store_api_request() ) {
			return $passed;
		}

		if ( ! $passed ) {
			return false;
		}

		$product_type = WC_Product_Factory::get_product_type( $product_id );
		if ( ! in_array( $product_type, wc_gc_get_product_types_allowed() ) ) {
			return $passed;
		}

		$product = wc_get_product( $product_id );
		if ( ! self::is_gift_card( $product ) ) {
			return $passed;
		}

		$configuration = self::get_posted_gift_card_configuration();
		if ( empty( $configuration ) ) {
			// Is order again? Try to fetch configuration from cart_item_data.
			$configuration = ! empty( $cart_item_data ) ? $cart_item_data : array();
		}

		try {
			self::validate_form_fields( $configuration, $product );
		} catch ( Exception $e ) {
			wc_add_notice( $e->getMessage(), 'error' );
			$passed = false;
		}

		return $passed;
	}
	/**
	 * Validate form fields.
	 *
	 * @param  array      $configuration Configuration.
	 * @param  WC_Product $product Gift Card product object.
	 *
	 * @throws Exception If validation fails.
	 */
	public static function validate_form_fields( $configuration, $product ) {

		if ( empty( $configuration ) ) {
			/* translators: %1$s: Product title */
			$notice = sprintf( __( '&quot;%1$s&quot; cannot be purchased &ndash; some required data is missing.', 'woocommerce-gift-cards' ), $product->get_title() );
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
			throw new Exception( $notice );
		}

		if ( ! ( isset( $configuration['wc_gc_giftcard_to_multiple'] ) || isset( $configuration['wc_gc_giftcard_to'] ) ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
			throw new Exception( __( 'Please enter a recipient.', 'woocommerce-gift-cards' ) );
		}

		// Check mantatory fields.
		if ( isset( $configuration['wc_gc_giftcard_to_multiple'] ) && empty( $configuration['wc_gc_giftcard_to_multiple'] ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
			throw new Exception( __( 'Please enter at least one recipient email.', 'woocommerce-gift-cards' ) );
		}

		if ( isset( $configuration['wc_gc_giftcard_to'] ) && empty( $configuration['wc_gc_giftcard_to'] ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
			throw new Exception( __( 'Please enter a recipient email.', 'woocommerce-gift-cards' ) );
		}

		// Email Sanity.
		if ( ! empty( $configuration['wc_gc_giftcard_to'] ) ) {
			if ( ! filter_var( $configuration['wc_gc_giftcard_to'], FILTER_VALIDATE_EMAIL ) ) {

				$maybe_multiple_emails = wc_gc_parse_email_string( $configuration['wc_gc_giftcard_to'] );
				if ( 1 < count( $maybe_multiple_emails ) ) {
					// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
					throw new Exception( __( 'Please enter only one recipient email.', 'woocommerce-gift-cards' ) );
				}
				// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
				throw new Exception( __( 'Recipient email invalid.', 'woocommerce-gift-cards' ) );
			}
		}

		if ( ! empty( $configuration['wc_gc_giftcard_to_multiple'] ) ) {
			foreach ( $configuration['wc_gc_giftcard_to_multiple'] as $email ) {
				if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
					/* translators: %s: Invalid email string */
					throw new Exception( sprintf( __( 'Invalid recipient email: &quot;%s&quot;.', 'woocommerce-gift-cards' ), $email ) ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
				}
			}
		}

		if ( ! empty( $configuration['wc_gc_giftcard_cc'] ) ) {
			foreach ( $configuration['wc_gc_giftcard_cc'] as $email ) {
				if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
					/* translators: %s: Invalid cc email string */
					throw new Exception( sprintf( __( 'Invalid CC recipient email: &quot;%s&quot;.', 'woocommerce-gift-cards' ), $email ) ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
				}
			}
		}

		if ( empty( $configuration['wc_gc_giftcard_from'] ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
			throw new Exception( __( 'Please enter your name.', 'woocommerce-gift-cards' ) );
		}

		if ( isset( $configuration['wc_gc_giftcard_from'] ) && strlen( $configuration['wc_gc_giftcard_from'] ) >= 128 ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
			throw new Exception( __( 'Your name cannot exceed 128 characters.', 'woocommerce-gift-cards' ) );
		}

		// Check delivery date format.
		if ( ! empty( $configuration['wc_gc_giftcard_delivery'] ) ) {

			// Check for offset.
			if ( ! isset( $configuration['_wc_gc_giftcard_delivery_gmt_offset'] ) ) {
				// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
				throw new Exception( __( 'Invalid delivery date timezone.', 'woocommerce-gift-cards' ) );
			}

			$delivery = $configuration['wc_gc_giftcard_delivery'];
			if ( ! wc_gc_is_unix_timestamp( $delivery ) || $delivery < strtotime( 'tomorrow' ) ) {
				// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
				throw new Exception( __( 'Invalid delivery date.', 'woocommerce-gift-cards' ) );
			}
		}

		/**
		 * `woocommerce_gc_validate_form_fields` action.
		 *
		 * Used to validate custom form fields.
		 *
		 * @since 1.6.0
		 *
		 * @param array $configuration
		 */
		do_action( 'woocommerce_gc_validate_form_fields', $configuration );
	}

	/**
	 * Filter to non-scalar all cart item meta.
	 *
	 * @param  array $item_data
	 * @param  array $cart_item
	 * @return array
	 */
	public static function get_item_data( $item_data, $cart_item ) {

		foreach ( self::get_form_fields() as $key => $label ) {
			if ( ! empty( $cart_item[ $key ] ) ) {

				// Treat emails array.
				if ( 'wc_gc_giftcard_to_multiple' === $key ) {
					$value = wc_gc_get_emails_formatted( $cart_item[ $key ] );
				} elseif ( 'wc_gc_giftcard_delivery' === $key ) {
					$value = date_i18n( get_option( 'date_format' ), wc_gc_convert_timestamp_to_gmt_offset( $cart_item[ $key ], -1 * (float) $cart_item['_wc_gc_giftcard_delivery_gmt_offset'] ) );
				} elseif ( 'wc_gc_giftcard_from' === $key ) {
					$value = wptexturize( $cart_item[ $key ] );
				} elseif ( 'wc_gc_giftcard_message' === $key ) {
					$value = wc_gc_display_message_text( $cart_item[ $key ], 'cart' );
				} elseif ( 'wc_gc_giftcard_cc' === $key ) {
					$value = wc_gc_get_emails_formatted( $cart_item[ $key ] );
				} else {
					$value = $cart_item[ $key ];
				}

				$item_data[] = array(
					'key'   => $label,
					'value' => $value,
				);
			}
		}

		return $item_data;
	}

	/*
	---------------------------------------------------*/
	/*
		Order Items.                                     */
	/*---------------------------------------------------*/

	/**
	 * Filter to non-scalar all cart item meta.
	 *
	 * @param  WC_Order_Item $order_item
	 * @param  string        $cart_item_key
	 * @param  array         $cart_item
	 * @param  WC_Order      $order
	 * @return void
	 */
	public static function create_order_line_item( $order_item, $cart_item_key, $cart_item, $order ) {

		$product = $cart_item['data'];
		if ( self::is_gift_card( $product ) ) {

			foreach ( self::get_form_fields() as $key => $label ) {
				if ( isset( $cart_item[ $key ] ) && ! empty( $cart_item[ $key ] ) ) {

					// Convert scalar to string.
					if ( is_array( $cart_item[ $key ] ) ) {
						$cart_item[ $key ] = implode( ', ', $cart_item[ $key ] );
					}

					$order_item->add_meta_data( $key, $cart_item[ $key ], true );
				}
			}

			// Add amount to coverted to balance.
			$amount = apply_filters( 'woocommerce_gc_gift_card_amount', $product->get_regular_price(), $product, $order );
			$order_item->add_meta_data( 'wc_gc_giftcard_amount', $amount, true );

			// Save the offset if delivery date is set.
			if ( ! empty( $cart_item['wc_gc_giftcard_delivery'] ) && isset( $cart_item['_wc_gc_giftcard_delivery_gmt_offset'] ) ) {
				$order_item->add_meta_data( '_wc_gc_giftcard_delivery_gmt_offset', (float) $cart_item['_wc_gc_giftcard_delivery_gmt_offset'], true );
			}
		}
	}

	/**
	 * Copy order item meta to cart meta.
	 *
	 * @param  array                 $cart_item_data
	 * @param  WC_Order_Item_Product $order_item
	 * @param  WC_Order              $order
	 * @return array
	 */
	public static function order_again_add_cart_item_data( $cart_item_data, $order_item, $order ) {

		if ( ! is_array( $cart_item_data ) ) {
			return $cart_item_data;
		}

		$configuration  = self::get_posted_gift_card_configuration( $order_item );
		$cart_item_data = array_merge( $cart_item_data, $configuration );

		return $cart_item_data;
	}

	/**
	 * Change order item permalink.
	 *
	 * @param  string   $link
	 * @param  array    $order_item
	 * @param  WC_Order $order
	 * @return string
	 */
	public static function order_item_permalink( $link, $order_item, $order ) {

		if ( ! empty( $link ) ) {
			foreach ( self::get_form_fields() as $key => $label ) {
				if ( strpos( $link, $key ) === false && isset( $order_item[ $key ] ) ) {
					if ( is_array( $order_item[ $key ] ) ) {
						$url_value = implode( ',', $order_item[ $key ] );
					} elseif ( 'wc_gc_giftcard_message' === $key ) {
						$url_value = nl2br( $order_item[ $key ] );
					} elseif ( 'wc_gc_giftcard_delivery' === $key ) {
						// Try to convert backwards compatibility delivery string to timestamp.
						$url_value = wc_gc_is_unix_timestamp( $order_item[ $key ] ) ? wc_gc_convert_timestamp_to_gmt_offset( $order_item[ $key ], -1 * (float) $order_item['_wc_gc_giftcard_delivery_gmt_offset'] ) : strtotime( $order_item[ $key ] );
					} else {
						$url_value = $order_item[ $key ];
					}
					$link = add_query_arg( $key, urlencode( $url_value ), $link );
				}
			}
		}

		return esc_url( $link ); // esc_url to be consistent with cart_item url.
	}

	/**
	 * Filter the order item's meta labels if needed.
	 *
	 * @param  string        $display_key
	 * @param  stdObject     $meta
	 * @param  WC_Order_Item $order_item
	 * @return string
	 */
	public static function order_item_display_meta_key( $display_key, $meta, $order_item ) {

		foreach ( self::get_form_fields() as $key => $label ) {
			if ( $display_key === $key ) {
				return $label;
			}
		}

		// Code.
		if ( 'wc_gc_giftcard_code' === $display_key ) {
			return _x( 'Code', 'order_item_display_key', 'woocommerce-gift-cards' );
		} elseif ( 'wc_gc_giftcard_amount' === $display_key ) {
			return _x( 'Amount', 'order_item_display_key', 'woocommerce-gift-cards' );
		}

		return $display_key;
	}

	/**
	 * Filter the order item's meta display value if needed.
	 *
	 * @param  string        $display_value
	 * @param  stdObject     $meta
	 * @param  WC_Order_Item $order_item
	 * @return string
	 */
	public static function order_item_display_meta_value( $display_value, $meta = null, $order_item = null ) {

		if ( is_null( $meta ) ) {
			return $display_value;
		}
		if ( 'wc_gc_giftcard_amount' === $meta->key ) {
			$display_value = wc_price( (float) $display_value );
		} elseif ( 'wc_gc_giftcard_delivery' === $meta->key ) {
			$display_value = date_i18n( get_option( 'date_format' ), wc_gc_convert_timestamp_to_gmt_offset( (int) $display_value, is_admin() ? null : -1 * $order_item['_wc_gc_giftcard_delivery_gmt_offset'] ) );
		} elseif ( 'wc_gc_giftcard_from' === $meta->key ) {
			$display_value = wptexturize( $display_value );
		} elseif ( 'wc_gc_giftcard_message' === $meta->key ) {
			$display_value = wc_gc_mask_messages() ? esc_html__( 'This message was hidden to protect the sender & recipient’s privacy.', 'woocommerce-gift-cards' ) : wc_gc_display_message_text( $display_value, 'order' );
		}

		return $display_value;
	}

	/**
	 * Mark meta keys as hidden.
	 *
	 * @param  array $hidden_meta
	 * @return array
	 */
	public static function order_item_hide_meta( $hidden_meta ) {

		$hidden_meta[] = '_wc_gc_deactivated_through_order_status';
		$hidden_meta[] = '_wc_gc_giftcard_delivery_gmt_offset';

		return $hidden_meta;
	}

	/**
	 * Re-add necessary formatted meta data if WC hides them.
	 *
	 * @since  1.3.0
	 *
	 * @param  array         $formatted_meta
	 * @param  WC_Order_Item $order_item
	 * @return array
	 */
	public static function order_item_add_formatted_meta_again( $formatted_meta, $order_item ) {

		$meta_data = $order_item->get_meta_data();
		foreach ( $meta_data as $meta ) {
			if ( 'wc_gc_giftcard_amount' === $meta->key ) {

				$found = false;
				foreach ( $formatted_meta as $formatted_meta_object ) {
					if ( 'wc_gc_giftcard_amount' === $formatted_meta_object->key ) {
						$found = true;
						break;
					}
				}

				// If it's not found then most probably WC is hiding it.
				if ( ! $found ) {

					$display_key   = $meta->key;
					$display_value = wp_kses_post( $meta->value );

					$formatted_meta[ $meta->id ] = (object) array(
						'key'           => $meta->key,
						'value'         => $meta->value,
						'display_key'   => apply_filters( 'woocommerce_order_item_display_meta_key', $display_key, $meta, $order_item ),
						'display_value' => wpautop( make_clickable( apply_filters( 'woocommerce_order_item_display_meta_value', $display_value, $meta, $order_item ) ) ),
					);
				}

				break;
			}
		}

		return $formatted_meta;
	}

	/**
	 * Create gift cards.
	 *
	 * @param  int      $order_id
	 * @param  WC_Order $order
	 * @return void
	 */
	public static function create_order_gift_cards( $order_id, $order = null ) {

		if ( is_null( $order ) ) {
			$order = wc_get_order( $order_id );
		}

		if ( ! is_a( $order, 'WC_Order' ) ) {
			return;
		}

		foreach ( $order->get_items() as $order_item_id => $order_item ) {

			$_product = $order_item->get_product();
			if ( ! $_product || ! is_a( $_product, 'WC_Product' ) || ! self::is_gift_card( $_product ) ) {
				continue;
			}

			if ( $_product->is_type( 'variation' ) ) {
				$_product = wc_get_product( $_product->get_parent_id() );
			}

			// Parse recipients.
			$recipients_string = $order_item->get_meta( 'wc_gc_giftcard_to_multiple', true );
			if ( empty( $recipients_string ) ) {
				$recipients_string = $order_item->get_meta( 'wc_gc_giftcard_to', true );
			}

			$recipients_array = wc_gc_parse_email_string( $recipients_string );
			$recipients_count = count( $recipients_array );

			// Skip if no emails.
			if ( 0 === $recipients_count ) {
				continue;
			}

			// Check for invalid quantity.
			if ( $recipients_count > 1 && ( $recipients_count % $order_item->get_quantity() !== 0 ) ) {
				throw new Exception( __( 'Invalid quantity configuration. Please review the recipients count.', 'woocommerce-gift-cards' ) );
			}

			// Parse the initial quantity and fill a new recipients array.
			$quantity = floor( $order_item->get_quantity() / count( $recipients_array ) );
			if ( $quantity > 1 ) {

				// Create a new array for each qty.
				$recipients = array();
				foreach ( $recipients_array as $recipient ) {
					$recipients = array_merge( $recipients, array_fill( 0, $quantity, $recipient ) );
				}
			} else {
				$recipients = $recipients_array;
			}

			// Get already saved giftcards within this order item.
			$saved_giftcards = $order_item->get_meta( 'wc_gc_giftcards', true ) ? $order_item->get_meta( 'wc_gc_giftcards', true ) : array();

			// Find the difference. What's needed to be created or activated.
			$processed_recipients = array();
			if ( ! empty( $saved_giftcards ) && is_array( $saved_giftcards ) ) {
				$is_flagged = $order_item->meta_exists( '_wc_gc_deactivated_through_order_status' );
				foreach ( $saved_giftcards as $giftcard_id ) {
					$giftcard = new WC_GC_Gift_Card( absint( $giftcard_id ) );
					if ( ! $giftcard->get_id() ) {
						continue;
					}

					if ( ! $giftcard->is_active() && $is_flagged && false !== $giftcard->is_delivered() ) {
						$giftcard->data->set_active( 'on' );
						$giftcard->data->save();
					}

					$processed_recipients[] = $giftcard->get_recipient();
				}
			}

			// Remove flag from deactivation.
			if ( $order_item->meta_exists( '_wc_gc_deactivated_through_order_status' ) ) {
				$order_item->delete_meta_data( '_wc_gc_deactivated_through_order_status' );
				$order_item->save_meta_data();
			}

			// Quick check.
			if ( count( $saved_giftcards ) >= count( $recipients ) ) {
				continue;
			}

			// Check for manual code.
			if ( ! empty( $order_item->get_meta( 'wc_gc_giftcard_code', true ) ) && count( $recipients ) !== 1 ) {
				throw new Exception( __( 'Invalid custom code configuration. Please review the recipients count.', 'woocommerce-gift-cards' ) );
			}

			// Create.
			$remaining_recipients = array_diff_assoc( $recipients, $processed_recipients );
			if ( count( $remaining_recipients ) ) {

				$giftcards = array();

				foreach ( $remaining_recipients as $recipient ) {

					// Check delivery.
					$deliver_date            = 0;
					$wc_gc_giftcard_delivery = $order_item->get_meta( 'wc_gc_giftcard_delivery', true ); // This is in UTC.

					if ( $wc_gc_giftcard_delivery ) {

						// Construct the DateTime object.
						if ( wc_gc_is_unix_timestamp( $wc_gc_giftcard_delivery ) ) {
							$deliver_datetime = new DateTime();
							$deliver_datetime->setTimestamp( $wc_gc_giftcard_delivery );

							// Transfer to store's timezone before saving.
							$store_offset = wc_gc_get_gmt_offset();
							$deliver_datetime->modify( $store_offset * 60 . ' minutes' );

						} else {

							// Backwards compatibility.
							$deliver_datetime = DateTime::createFromFormat( get_option( 'date_format' ), $wc_gc_giftcard_delivery );
						}

						// Filter always apply to the store's timezone.
						$delivery_hour   = (int) apply_filters( 'woocommerce_gc_scheduled_delivery_hour', (int) $deliver_datetime->format( 'H' ), $_product, $order_item, $order );
						$delivery_minute = (int) apply_filters( 'woocommerce_gc_scheduled_delivery_minute', (int) $deliver_datetime->format( 'i' ), $_product, $order_item, $order );
						$deliver_datetime->setTime( $delivery_hour, $delivery_minute, 0 );

						// Revert back to UTC for saving.
						if ( wc_gc_is_unix_timestamp( $wc_gc_giftcard_delivery ) ) {
							$deliver_datetime->modify( -1 * $store_offset * 60 . ' minutes' );
						}

						// Add the final timestamp.
						$deliver_date = apply_filters( 'woocommerce_gc_scheduled_delivery_timestamp', $deliver_datetime->getTimestamp(), $_product, $order_item, $order );
					}

					// Check expiration.
					$expire_date    = 0;
					$expire_in_days = absint( $_product->get_meta( '_gift_card_expiration_days', true ) );
					if ( $expire_in_days > 0 ) {
						$base          = 0 === $deliver_date ? time() : $deliver_date;
						$base_datetime = new DateTime();
						$base_datetime->setTimestamp( $base );
						$base_datetime->add( new DateInterval( 'P' . absint( $expire_in_days ) . 'D' ) );
						$expire_date = $base_datetime->getTimestamp();
					}

					$args = array(
						'is_active'     => $deliver_date > 0 ? 'off' : 'on',
						'is_virtual'    => $_product->is_virtual() ? 'on' : 'off',
						'order_id'      => $order_id,
						'order_item_id' => $order_item->get_id(),
						'recipient'     => $recipient,
						'sender'        => $order_item->get_meta( 'wc_gc_giftcard_from', true ),
						'sender_email'  => $order->get_billing_email(),
						'message'       => $order_item->get_meta( 'wc_gc_giftcard_message', true ),
						'balance'       => $order_item->get_meta( 'wc_gc_giftcard_amount', true ),
						'deliver_date'  => $deliver_date,
						'delivered'     => 'no',
						'expire_date'   => $expire_date,
						'meta_data'     => array( '_currency' => $order->get_currency() ),
					);

					// Check for existing code.
					if ( ! empty( $order_item->get_meta( 'wc_gc_giftcard_code', true ) ) ) {
						$args['code'] = $order_item->get_meta( 'wc_gc_giftcard_code', true );
					}

					// Check for the same footprint.
					// Hint: Prevent concurrent requests to produce doubled giftcards.
					$footprint_args = array(
						'order_id'      => $args['order_id'],
						'order_item_id' => $args['order_item_id'],
						'recipient'     => $args['recipient'],
						'count'         => true,
					);
					$footprint      = WC_GC()->db->giftcards->query( $footprint_args );

					// Add gift card to DB.
					$id = absint( $footprint ) < $order_item->get_quantity() ? WC_GC()->db->giftcards->add( (array) apply_filters( 'woocommerce_gc_create_order_giftcard_args', $args, $_product, $order_item, $order ) ) : false;

					// Log.
					if ( $id ) {

						/**
						 * `woocommerce_gc_order_giftcard_created` action.
						 *
						 * @since 1.6.0
						 *
						 * @param int           $id The giftcard id
						 * @param WC_Order_Item $order_item
						 * @param WC_Order      $order
						 */
						do_action( 'woocommerce_gc_order_giftcard_created', $id, $order_item, $order );

						// Add processed Gift Card ID.
						$giftcards[] = $id;

						// Fetch order user.
						$user = $order->get_user();
						if ( ! $user ) {
							$user_email = $order->get_billing_email();
							$user_id    = 0;
						} else {
							$user_email = $user->user_email;
							$user_id    = $user->ID;
						}

						// Log action.
						WC_GC()->db->activity->add(
							array(
								'type'       => 'issued',
								'gc_id'      => $id,
								'object_id'  => $order_id,
								'user_id'    => $user_id,
								'user_email' => $user_email,
								'amount'     => $order_item->get_meta( 'wc_gc_giftcard_amount', true ),
							)
						);
					}
				}

				// Update with saved giftcards.
				$order_item->update_meta_data( 'wc_gc_giftcards', array_unique( array_merge( $giftcards, $saved_giftcards ) ) );
				$order_item->save();

				/**
				 * `woocommerce_gc_send_gift_card_hook` filter.
				 *
				 * @since  1.8.1
				 *
				 * @param  array  $giftcards
				 * @return string
				 */
				$hook = (string) apply_filters( 'woocommerce_gc_send_gift_card_hook', 'woocommerce_gc_send_gift_card_to_customer', $giftcards );
				// Email newly created Gift Cards.
				do_action( $hook, $giftcards, $order->get_id(), $order );
			}
		}
	}

	/**
	 * Deactivate gift cards.
	 *
	 * @param  int      $order_id
	 * @param  WC_Order $order
	 * @return void
	 */
	public static function deactivate_order_gift_cards( $order_id, $order ) {

		foreach ( $order->get_items() as $order_item_id => $order_item ) {

			if ( ! self::is_gift_card( $order_item->get_product() ) ) {
				continue;
			}

			$giftcards = $order_item->get_meta( 'wc_gc_giftcards', true ) ? $order_item->get_meta( 'wc_gc_giftcards', true ) : array();
			foreach ( $giftcards as $giftcard_id ) {
				$giftcard = new WC_GC_Gift_Card( $giftcard_id );
				if ( ! $giftcard->get_id() ) {
					continue;
				}

				// Deactivate.
				if ( $giftcard->is_active() ) {
					$giftcard->data->set_active( 'off' );
					$giftcard->data->save();
				}
			}

			// Flag order item.
			if ( ! $order_item->meta_exists( '_wc_gc_deactivated_through_order_status' ) ) {
				$order_item->add_meta_data( '_wc_gc_deactivated_through_order_status', 'yes', true );
				$order_item->save_meta_data();
			}
		}
	}

	/**
	 * Check for scheduled giftcards before deleting an Order/Product.
	 *
	 * @since 1.3.6
	 *
	 * @param  int $post_id
	 * @return void
	 */
	public static function before_delete_post( $post_id ) {

		// Fetch the post type.
		$post_type = get_post_type( $post_id );

		// If is order.
		if ( in_array( $post_type, wc_get_order_types(), true ) ) {

			$giftcards = WC_GC()->db->giftcards->query(
				array(
					'order_id'     => $post_id,
					'is_delivered' => false,
				)
			);
			if ( ! empty( $giftcards ) ) {
				/* translators: %d order id. */
				WC_GC_Admin_Notices::add_notice( sprintf( __( 'Order #%d cannot be deleted at the moment. Some pending gift card code deliveries depend on data found in this order.', 'woocommerce-gift-cards' ), $post_id ), 'error', true );
				wp_safe_redirect( admin_url( 'edit.php?post_type=' . $post_type ) );
				exit();
			}
		} elseif ( 'product' === $post_type ) {
			$product = wc_get_product( $post_id );

			if ( self::is_gift_card( $product ) ) {
				$giftcards = WC_GC()->db->giftcards->query(
					array(
						'product_id'   => $post_id,
						'is_delivered' => false,
					)
				);

				if ( ! empty( $giftcards ) ) {
					/* translators: %d product id. */
					WC_GC_Admin_Notices::add_notice( sprintf( __( 'Product #%d cannot be deleted at the moment. Some pending gift card code deliveries will fail if this product is deleted.', 'woocommerce-gift-cards' ), $product->get_id() ), 'error', true );
					wp_safe_redirect( admin_url( 'edit.php?post_type=' . $post_type ) );
					exit();
				}
			}
		}
	}

	/**
	 * Check for scheduled giftcards before deleting an Order/Product through REST API.
	 *
	 * @since 1.3.6
	 *
	 * @param  bool   $permission
	 * @param  string $context
	 * @param  int    $object_id
	 * @param  string $post_type
	 * @return bool
	 */
	public static function before_delete_rest_post( $permission, $context, $object_id, $post_type ) {

		if ( ! $permission || 'delete' !== $context ) {
			return $permission;
		}

		// If is order.
		if ( in_array( $post_type, wc_get_order_types(), true ) ) {

			$giftcards = WC_GC()->db->giftcards->query(
				array(
					'order_id'     => $object_id,
					'is_delivered' => false,
				)
			);
			if ( ! empty( $giftcards ) ) {
				$permission = false;
			}
		} elseif ( 'product' === $post_type ) {
			$product = wc_get_product( $object_id );

			if ( self::is_gift_card( $product ) ) {
				$giftcards = WC_GC()->db->giftcards->query(
					array(
						'product_id'   => $product->get_id(),
						'is_delivered' => false,
					)
				);

				if ( ! empty( $giftcards ) ) {
					$permission = false;
				}
			}
		}

		return $permission;
	}

	/*
	---------------------------------------------------*/
	/*
		Utilities.                                       */
	/*---------------------------------------------------*/

	/**
	 * Check if product is gift card.
	 *
	 * @param  WC_Product $product
	 * @return bool
	 */
	public static function is_gift_card( $product ) {

		if ( ! is_a( $product, 'WC_Product' ) ) {
			return false;
		}

		if ( $product->is_type( 'variation' ) ) {
			$product = wc_get_product( $product->get_parent_id() );

			// Check for orphaned variations.
			if ( ! is_a( $product, 'WC_Product' ) ) {
				return false;
			}
		}

		return $product->meta_exists( '_gift_card' ) && 'yes' === $product->get_meta( '_gift_card', true );
	}
}

// Init.
WC_GC_Gift_Card_Product::init();
