<?php
/**
 * Functions
 *
 * @package WPMultiStepCheckout
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'get_wmsc_settings' ) ) {

	/**
	 * The settings array for the admin page.
	 */
	function get_wmsc_settings() {

		$account_url                      = admin_url( 'admin.php?page=wc-settings&tab=account' );
		$registration_settings_screenshot = 'https://www.silkypress.com/wp-content/uploads/2025/08/registration-description.png';
		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '9.2.0', '>=' ) ) {
		    $no_login_screenshot          = 'https://www.silkypress.com/wp-content/uploads/2025/08/multi-step-checkout-no-login.png';
			/* translators: 1: Woocommerce Accounts URL 2: Screenshot URL. */
			$show_login_step              =  sprintf( __( 'Use the "Enable log-in during checkout" option on the <a href="%1$s">WP Admin -> WooCommerce -> Settings -> Accounts</a> page to add/remove the login form to the checkout. See <a href="%2$s" target="_blank">this screenshot</a>.', 'wp-multi-step-checkout' ), esc_url( $account_url ), esc_url( $no_login_screenshot ) );
		} else {
			$no_login_screenshot          = 'https://www.silkypress.com/wp-content/uploads/2018/09/multi-step-checkout-no-login.png';
			/* translators: 1: Woocommerce Accounts URL 2: Screenshot URL. */
			$show_login_step              =  sprintf( __( 'Use the "Allow customers to log into an existing account during checkout" option on the <a href="%1$s">WP Admin -> WooCommerce -> Settings -> Accounts</a> page to add/remove the login form to the checkout. See <a href="%2$s" target="_blank">this screenshot</a>.', 'wp-multi-step-checkout' ), esc_url( $account_url ), esc_url( $no_login_screenshot ) );
		}

		$wmsc_settings                    = array(
			/* General Settings */
			'label1'                   => array(
				'label'      => __( 'Which Steps to show', 'wp-multi-step-checkout' ),
				'input_form' => 'header',
				'value'      => '',
				'section'    => 'general',
			),
			'show_login_step'          => array(
				'label'      => __( 'Show the <code>Login</code> step', 'wp-multi-step-checkout' ),
				'input_form' => 'checkbox',
				'value'      => true,
				'description'=> __( 'If unchecked, then the login form will show up before the steps, as WooCommerce normally shows it.', 'wp-multi-step-checkout' ),
				'section'    => 'general',
				'pro'        => false,
			),
			'show_login_desc'          => array(
				'label'      => '',
				'input_form' => 'text',
				'value'      => sprintf( $show_login_step, esc_url( $account_url ), esc_url( $no_login_screenshot ) ),
				'section'    => 'general',
				'pro'        => false,
			),
			'show_shipping_step'       => array(
				'label'      => __( 'Show the <code>Shipping</code> step', 'wp-multi-step-checkout' ),
				'input_form' => 'checkbox',
				'value'      => true,
				'section'    => 'general',
			),
			'hide_shipping_step_virtual' => array(
				'label'      => __( 'Hide the <code>Shipping</code> step if there are only virtual products in the cart', 'wp-multi-step-checkout' ),
				'input_form' => 'checkbox',
				'value'      => false,
				'section'    => 'general',
			),
			'unite_billing_shipping'   => array(
				'label'      => __( 'Show the <code>Billing</code> and the <code>Shipping</code> steps together', 'wp-multi-step-checkout' ),
				'input_form' => 'checkbox',
				'value'      => false,
				'section'    => 'general',
			),
			'unite_order_payment'      => array(
				'label'      => __( 'Show the <code>Order</code> and the <code>Payment</code> steps together', 'wp-multi-step-checkout' ),
				'input_form' => 'checkbox',
				'value'      => false,
				'section'    => 'general',
			),

			'label2'                   => array(
				'label'      => __( 'Additional Elements', 'wp-multi-step-checkout' ),
				'input_form' => 'header',
				'value'      => '',
				'section'    => 'general',
			),
			'show_back_to_cart_button' => array(
				'label'      => __( 'Show the <code>Back to Cart</code> button', 'wp-multi-step-checkout' ),
				'input_form' => 'checkbox',
				'value'      => true,
				'section'    => 'general',
			),
			'registration_with_login'  => array(
				'label'       => __( 'Show registration form in the <code>Login</code> step', 'wp-multi-step-checkout' ),
				'input_form'  => 'checkbox',
				'value'       => true,
				'section'     => 'general',
				'description' => __( 'The registration form will be shown next to the login form, it will not replace it', 'wp-multi-step-checkout' ),
				'pro'         => true,
			),
			'registration_desc'        => array(
				'label'      => '',
				'input_form' => 'text',
				/* translators: 1: Woocommerce Accounts URL 2: Screenshot URL. */
				'value'      => sprintf( __( 'Use the "Account creation" options on the <a href="%1$s">WP Admin -> WooCommerce -> Settings -> Accounts & Privacy</a> page to modify the Registration form. See <a href="%2$s" target="_blank">this screenshot</a>.', 'wp-multi-step-checkout' ), esc_url( $account_url ), esc_url( $registration_settings_screenshot ) ),
				'section'    => 'general',
				'pro'        => true,
			),
			'review_thumbnails'        => array(
				'label'      => __( 'Add product thumbnails to the <code>Order Review</code> section', 'wp-multi-step-checkout' ),
				'input_form' => 'checkbox',
				'value'      => true,
				'section'    => 'general',
				'pro'        => true,
			),
			'review_address'           => array(
				'label'      => __( 'Add <code>Address Review</code> to the <code>Order</code> section', 'wp-multi-step-checkout' ),
				'input_form' => 'checkbox',
				'value'      => false,
				'section'    => 'general',
				'pro'        => true,
			),
			'label3'                   => array(
				'label'      => __( 'Functionality', 'wp-multi-step-checkout' ),
				'input_form' => 'header',
				'value'      => '',
				'section'    => 'general',
			),
			'validation_per_step'      => array(
				'label'       => __( 'Validate the fields during each step', 'wp-multi-step-checkout' ),
				'description' => __( 'The default WooCommerce validation is done when clicking the Place Order button. With this option the validation is performed when trying to move to the next step', 'wp-multi-step-checkout' ),
				'input_form'  => 'checkbox',
				'value'       => true,
				'section'     => 'general',
				'pro'         => true,
			),
			'validation_inline'        => array(
				'label'       => __( 'Inline validation errors', 'wp-multi-step-checkout' ),
				'description' => __( 'Before moving to the next step, the validation errors will show up below the input field to which the error belongs.', 'wp-multi-step-checkout' ),
				'input_form'  => 'checkbox',
				'value'       => false,
				'section'     => 'general',
				'pro'         => true,
			),
			'clickable_steps'          => array(
				'label'       => __( 'Clickable Steps', 'wp-multi-step-checkout' ),
				'description' => __( 'The user can click on the steps in order to get to the next one.', 'wp-multi-step-checkout' ),
				'input_form'  => 'checkbox',
				'value'       => true,
				'section'     => 'general',
				'pro'         => true,
			),
			'keyboard_nav'             => array(
				'label'       => __( 'Enable the keyboard navigation', 'wp-multi-step-checkout' ),
				'description' => __( 'Use the keyboard\'s left and right keys to move between the checkout steps', 'wp-multi-step-checkout' ),
				'input_form'  => 'checkbox',
				'value'       => false,
				'section'     => 'general',
			),
			'url_hash'                 => array(
				'label'       => __( 'Change the URL for each step', 'wp-multi-step-checkout' ),
				'description' => __( 'Each step will have a hash added to the URL. For example &quot;#login&quot; or &quot;#billing&quot;. This option, together with some &quot;History Change Trigger&quot; settings in the Google Tag Manager, allows Google Analytics to track each step as different pages.', 'wp-multi-step-checkout' ),
				'input_form'  => 'checkbox',
				'value'       => false,
				'section'     => 'general',
				'pro'         => true,
			),
			'disable_mobile'            => array(
				'label'       => __( 'Disable plugin on mobile devices', 'wp-multi-step-checkout' ),
				'description' => __( 'On mobile devices (smart phone, tablet, etc.) your customers will see the default WooCommerce checkout form, without the steps from the Multi-Step Checkout for WooCommerce plugin', 'wp-multi-step-checkout' ),
				'input_form'  => 'checkbox',
				'value'       => false,
				'section'     => 'general',
			),

			/* Templates */
			'main_color'               => array(
				'label'      => __( 'Main Color', 'wp-multi-step-checkout' ),
				'input_form' => 'input_color',
				'value'      => '#1e85be',
				'section'    => 'design',
			),
			'template'                 => array(
				'label'      => __( 'Template', 'wp-multi-step-checkout' ),
				'input_form' => 'radio',
				'value'      => 'default',
				'values'     => array(
					'default'    => __( 'Default', 'wp-multi-step-checkout' ),
					'md'         => __( 'Material Design', 'wp-multi-step-checkout' ),
					'breadcrumb' => __( 'Breadcrumbs', 'wp-multi-step-checkout' ),
					'underline'  => __( 'Underline', 'wp-multi-step-checkout' ),
				),
				'section'    => 'design',
				'pro'        => true,
			),
			'wpmc_buttons'             => array(
				'label'       => __( 'Use the plugin\'s buttons', 'wp-multi-step-checkout' ),
				'input_form'  => 'checkbox',
				'value'       => false,
				'description' => __( 'By default the plugin tries to use the theme\'s design for the buttons. If this fails, enable this option in order to use the plugin\'s button style', 'wp-multi-step-checkout' ),
				'section'     => 'design',
				'pro'         => true,
			),
			'wpmc_check_sign'          => array(
				'label'      => __( 'Show a "check" sign for visited steps', 'wp-multi-step-checkout' ),
				'input_form' => 'checkbox',
				'value'      => false,
				'section'    => 'design',
				'pro'        => true,
			),
			'visited_color'            => array(
				'label'      => __( 'Visited steps color', 'wp-multi-step-checkout' ),
				'input_form' => 'input_color',
				'value'      => '#1EBE3A',
				'section'    => 'design',
				'pro'        => true,
			),

			/* Step Titles */
			't_login'                  => array(
				'label'      => __( 'Login', 'wp-multi-step-checkout' ),
				'input_form' => 'input_text',
				'value'      => __( 'Login', 'wp-multi-step-checkout' ),
				'section'    => 'titles',
			),
			't_billing'                => array(
				'label'      => __( 'Billing', 'wp-multi-step-checkout' ),
				'input_form' => 'input_text',
				'value'      => __( 'Billing', 'wp-multi-step-checkout' ),
				'section'    => 'titles',
			),
			't_shipping'               => array(
				'label'      => __( 'Shipping', 'wp-multi-step-checkout' ),
				'input_form' => 'input_text',
				'value'      => __( 'Shipping', 'wp-multi-step-checkout' ),
				'section'    => 'titles',
			),
			't_order'                  => array(
				'label'      => __( 'Order', 'wp-multi-step-checkout' ),
				'input_form' => 'input_text',
				'value'      => __( 'Order', 'wp-multi-step-checkout' ),
				'section'    => 'titles',
			),
			't_payment'                => array(
				'label'      => __( 'Payment', 'wp-multi-step-checkout' ),
				'input_form' => 'input_text',
				'value'      => __( 'Payment', 'wp-multi-step-checkout' ),
				'section'    => 'titles',
			),
			't_back_to_cart'           => array(
				'label'      => __( 'Back to cart', 'wp-multi-step-checkout' ),
				'input_form' => 'input_text',
				'value'      => _x( 'Back to cart', 'Frontend: button label', 'wp-multi-step-checkout' ),
				'section'    => 'titles',
			),
			't_skip_login'             => array(
				'label'      => __( 'Skip Login', 'wp-multi-step-checkout' ),
				'input_form' => 'input_text',
				'value'      => _x( 'Skip Login', 'Frontend: button label', 'wp-multi-step-checkout' ),
				'section'    => 'titles',
			),
			't_previous'               => array(
				'label'      => __( 'Previous', 'wp-multi-step-checkout' ),
				'input_form' => 'input_text',
				'value'      => _x( 'Previous', 'Frontend: button label', 'wp-multi-step-checkout' ),
				'section'    => 'titles',
			),
			't_next'                   => array(
				'label'      => __( 'Next', 'wp-multi-step-checkout' ),
				'input_form' => 'input_text',
				'value'      => _x( 'Next', 'Frontend: button label', 'wp-multi-step-checkout' ),
				'section'    => 'titles',
			),
			't_error'                  => array(
				'label'       => __( 'Please fix the errors on this step before moving to the next step', 'wp-multi-step-checkout' ),
				'input_form'  => 'input_text',
				'value'       => _x( 'Please fix the errors on this step before moving to the next step', 'Frontend: error message', 'wp-multi-step-checkout' ),
				'section'     => 'titles',
				'description' => __( 'This is an error message shown in the frontend', 'wp-multi-step-checkout' ),
				'pro'         => true,
			),
			'c_sign'                   => array(
				'label'       => __( 'AND sign', 'wp-multi-step-checkout' ),
				'input_form'  => 'input_text',
				'value'       => __( '&', 'wp-multi-step-checkout' ),
				'section'     => 'titles',
				'description' => __( 'The sign between two unified steps. For example "Billing & Shipping"' ),
			),
			't_wpml'                   => array(
				'label'       => __( 'Use WPML to translate the text on the Steps and Buttons', 'wp-multi-step-checkout' ),
				'input_form'  => 'checkbox',
				'value'       => false,
				'section'     => 'titles',
				'description' => __( 'For a multilingual website the translations from WPML will be used instead of the ones in this form', 'wp-multi-step-checkout' ),
			),

		);

		return apply_filters( 'wmsc_settings_admin', $wmsc_settings );

	}
}

if ( ! function_exists( 'get_wmsc_steps' ) ) {

	/**
	 * The steps array.
	 * Note: The Login is always the first step and is not part of the get_wmsc_steps() array.
	 */
	function get_wmsc_steps() {

		$steps = array(
			'billing'  => array(
				'title'    => __( 'Billing', 'wp-multi-step-checkout' ),
				'position' => 10,
				'class'    => 'wpmc-step-billing',
				'sections' => array( 'billing' ),
			),
			'shipping' => array(
				'title'    => __( 'Shipping', 'wp-multi-step-checkout' ),
				'position' => 20,
				'class'    => 'wpmc-step-shipping',
				'sections' => array( 'shipping' ),
			),
			'review'   => array(
				'title'    => __( 'Order', 'wp-multi-step-checkout' ),
				'position' => 30,
				'class'    => 'wpmc-step-review',
				'sections' => array( 'review' ),
			),
			'payment'  => array(
				'title'    => __( 'Payment', 'wp-multi-step-checkout' ),
				'position' => 40,
				'class'    => 'wpmc-step-payment',
				'sections' => array( 'payment' ),
			),

		);

		return $steps;
	}
}

if ( ! function_exists( 'wmsc_step_content_login' ) ) {

	/**
	 * The content for the Login step.
	 *
	 * @param object $checkout The Checkout object from the WooCommerce plugin.
	 * @param bool   $stop_at_login If the user should be logged in in order to checkout.
	 */
	function wmsc_step_content_login( $checkout, $stop_at_login, $registration_with_login = true ) { ?> 
	<div class="wpmc-step-item wpmc-step-login current">
			<div id="checkout_login" class="woocommerce_checkout_login wp-multi-step-checkout-step">
				<?php
				woocommerce_login_form(
					array(
						'message'  => apply_filters( 'woocommerce_checkout_logged_in_message', esc_html__( 'If you have shopped with us before, please enter your details below. If you are a new customer, please proceed to the Billing section.', 'woocommerce' ) ),
						'redirect' => wc_get_checkout_url(),
						'hidden'   => false,
					)
				);
				?>
			</div>
				<?php
				if ( $stop_at_login ) {
					echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
				}
				?>
	</div>
	<?php }
}


/**
 * If the "Show the Login step" option is unchecked, then show the default WooCommerce login form.
 */
add_action( 'wpmc_before_tabs', function() {

	$options = get_option('wmsc_options');

	if ( isset( $options['show_login_step'] ) && ! $options['show_login_step'] ) {
		woocommerce_checkout_login_form();
	}
});


if ( ! function_exists( 'wmsc_step_content_billing' ) ) {

	/**
	 * The content of the Billing step.
	 */
	function wmsc_step_content_billing() {
		do_action( 'woocommerce_checkout_before_customer_details' );
		do_action( 'woocommerce_checkout_billing' );
	}
}

if ( ! function_exists( 'wmsc_step_content_shipping' ) ) {

	/**
	 * The content of the Shipping step.
	 */
	function wmsc_step_content_shipping() {
		do_action( 'woocommerce_checkout_shipping' );
		do_action( 'woocommerce_checkout_after_customer_details' );
	}
}

if ( ! function_exists( 'wmsc_step_content_payment' ) ) {

	/**
	 * The content of the Order Payment step.
	 */
	function wmsc_step_content_payment() {
		echo '<h3 id="payment_heading">' . esc_html__( 'Payment', 'woocommerce' ) . '</h3>';
		do_action( 'wpmc-woocommerce_checkout_payment' );
		do_action( 'woocommerce_checkout_after_order_review' );
	}
}

if ( ! function_exists( 'wmsc_step_content_review' ) ) {

	/**
	 * The content of the Order Review step.
	 */
	function wmsc_step_content_review() {
		do_action( 'woocommerce_checkout_before_order_review_heading' );
		echo '<h3 id="order_review_heading">' . esc_html__( 'Your order', 'woocommerce' ) . '</h3>';
		do_action( 'woocommerce_checkout_before_order_review' );
		echo '<div id="order_review" class="woocommerce-checkout-review-order">';
		do_action( 'woocommerce_checkout_order_review' );
		do_action( 'wpmc-woocommerce_order_review' );
		echo '</div>';
	}
}

if ( ! function_exists( 'wmsc_step_content_payment_germanized' ) ) {

	/**
	 * The content of the Payment step for the "Germanized for WooCommerce" plugin.
	 */
	function wmsc_step_content_payment_germanized() {
		echo '<h3 id="payment_heading">' . esc_html__( 'Choose a Payment Gateway', 'woocommerce-germanized' ) . '</h3>';
		do_action( 'wpmc-woocommerce_checkout_payment' );
	}
}

if ( ! function_exists( 'wmsc_step_content_review_germanized' ) ) {

	/**
	 * The content of the Order Review step for the "Germanized for WooCommerce" plugin.
	 */
	function wmsc_step_content_review_germanized() {
		do_action( 'woocommerce_checkout_before_order_review_heading' );
		echo '<h3 id="order_review_heading">' . esc_html__( 'Your order', 'woocommerce' ) . '</h3>';
		do_action( 'woocommerce_checkout_before_order_review' );
		echo '<div id="order_review" class="woocommerce-checkout-review-order">';
		do_action( 'wpmc-woocommerce_order_review' );
		if ( function_exists( 'woocommerce_gzd_template_order_submit' ) ) {
			woocommerce_gzd_template_order_submit();
		}
		echo '</div>';
	}
}

if ( ! function_exists( 'wmsc_step_content_review_german_market' ) ) {

	/**
	 * The content of the Order Review step for the "German Market" plugin.
	 */
	function wmsc_step_content_review_german_market() {
		do_action( 'woocommerce_checkout_before_order_review_heading' );
		echo '<h3 id="order_review_heading">' . esc_html__( 'Your order', 'woocommerce' ) . '</h3>';
		do_action( 'woocommerce_checkout_before_order_review' );
		echo '<div id="order_review" class="woocommerce-checkout-review-order">';
		do_action( 'wpmc-woocommerce_order_review' );
		do_action( 'woocommerce_checkout_order_review' );
		echo '</div>';
	}
}


if ( ! function_exists( 'wmsc_step_content_payment_german_market' ) ) {

	/**
	 * The content of the Order Payment step for the "German Market" plugin.
	 */
	function wmsc_step_content_payment_german_market() {
		echo '<h3 id="payment_heading">' . esc_html__( 'Payment', 'woocommerce' ) . '</h3>';
		do_action( 'wpmc-woocommerce_checkout_payment' );
	}
}


if ( ! function_exists( 'wpmc_sort_by_position' ) ) {

	/**
	 * Comparison function for sorting the steps.
	 *
	 * @param array $a First array.
	 * @param array $b Second array.
	 */
	function wpmc_sort_by_position( $a, $b ) {
		return $a['position'] - $b['position'];
	}
}


if ( ! function_exists( 'wmsc_delete_step_by_category' ) ) {

	/**
	 * Delete a step if one/all products in a category or not in a category.
	 *
	 * Use the `wmsc_delete_step_by_category` filter to set up the settings.
	 *
	 * @param array $steps The steps array.
	 */
	function wmsc_delete_step_by_category( $steps ) {

		$default_settings = array(
			'remove_step'                           => 'shipping',
			'one_product_in_categories'             => array(),
			'all_products_in_categories'            => array(),
			'one_product_in_all_except_categories'  => array(),
			'all_products_in_all_except_categories' => array(),
			'virtual_products'                      => false,
		);

		$settings = apply_filters( 'wmsc_delete_step_by_category', $default_settings );

		foreach( $default_settings as $_id => $_value ) {
			if ( ! isset( $settings[ $_id ] ) ) {
				$settings[ $_id ] = $_value;
			}
		}

		extract( $settings );

		$cart     = WC()->cart->get_cart_contents();
		$products = array();

		if ( ! is_array( $cart ) || count( $cart ) === 0 ) {
			return $steps;
		}

		$only_virtual_products = true;
		foreach ( $cart as $_product ) {
			$this_product_in_category = false;
			$_cat                     = get_the_terms( $_product['product_id'], 'product_cat' );

			if ( $only_virtual_products && ! $_product['data']->is_virtual() ) {
				$only_virtual_products = false;
			}
			if ( is_array( $_cat ) && count( $_cat ) > 0 ) {
				foreach ( $_cat as $__cat ) {
					$products[ $_product['product_id'] ][] = $__cat->slug;
				}
			}
		}

		if ( count( $products ) === 0 ) {
			return $steps;
		}

		$products_intersect = $products_union = array_shift( $products );
		while ( count( $products ) > 0 ) {
			$one_product = array_shift( $products );
			$products_intersect = array_intersect( $products_intersect, $one_product );
			$products_union     = array_merge( $products_union, $one_product );
		}

		if ( count( $one_product_in_categories ) > 0 ) {
			foreach ( $one_product_in_categories as $_cat ) {
				if ( array_search( $_cat, $products_union, true ) !== false ) {
					unset( $steps[ $remove_step ] );
				}
			}
		}

		if ( count( $all_products_in_categories ) > 0 ) {
			foreach ( $all_products_in_categories as $_cat ) {
				if ( array_search( $_cat, $products_intersect, true ) !== false ) {
					unset( $steps[ $remove_step ] );
				}
			}
		}

		if ( count( $one_product_in_all_except_categories ) > 0 ) {
			if ( count( array_intersect( $one_product_in_all_except_categories, $products_union ) ) === 0 ) {
				unset( $steps[ $remove_step ] );
			}
		}

		if ( count( $all_products_in_all_except_categories ) > 0 ) {
			if ( count( array_intersect( $all_products_in_all_except_categories, $products_intersect ) ) === 0 ) {
				unset( $steps[ $remove_step ] );
			}
		}

		if ( $virtual_products && $only_virtual_products ) {
			unset( $steps[ $remove_step ] );
		}

		return $steps;
	}
}
add_filter( 'wpmc_modify_steps', 'wmsc_delete_step_by_category', 30 );
