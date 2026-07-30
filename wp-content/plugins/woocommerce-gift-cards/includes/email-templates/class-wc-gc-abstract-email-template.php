<?php
/**
 * WC_GC_Email_Template abstract class
 *
 * @package  WooCommerce Gift Cards
 * @since    1.2.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract Email template.
 *
 * @class    WC_GC_Email_Template
 * @version  1.2.0
 */
abstract class WC_GC_Email_Template {

	/**
	 * Template id.
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * Template html file.
	 *
	 * @var string
	 */
	protected $template_html;

	/**
	 * Get id.
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get template html file.
	 *
	 * @return string
	 */
	public function get_template_html() {
		return $this->template_html;
	}

	/**
	 * Get template args.
	 *
	 * @param  WC_Email $email
	 * @return array
	 */
	public function get_args( $email ) {
		return array();
	}

	/**
	 * Get template html file.
	 *
	 * @return void
	 */
	public function setup() {
		// Setup styles.
		add_filter( 'woocommerce_email_styles', array( $this, 'add_stylesheets' ), 10, 2 );
	}

	/**
	 * Get admin product fields.
	 *
	 * @param  WC_Product $product
	 * @param  int        $loop
	 * @return void
	 */
	public function get_admin_product_fields_html( $product, $index = false ) {
		// ...
	}

	/**
	 * Process product fields.
	 *
	 * @throws  Exception
	 *
	 * @param  WC_Product $product
	 * @return void
	 */
	public function process_product_data( &$product ) {
		// ...
	}

	/**
	 * Process product variation fields.
	 *
	 * @throws  Exception
	 *
	 * @param  WC_Product $product
	 * @return void
	 */
	public function process_variation_product_data( &$product, $index ) {
		// ...
	}

	/**
	 * Style Giftcard template.
	 *
	 * @param  string   $css
	 * @param  WC_Email $email
	 * @return string
	 */
	public function add_stylesheets( $css, $email = null ) {
		return $css;
	}
}
