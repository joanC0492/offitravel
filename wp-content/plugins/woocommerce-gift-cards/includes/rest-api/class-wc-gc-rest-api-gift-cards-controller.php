<?php
/**
 * WC_GC_REST_API_Gift_Cards_Controller class
 *
 * @package  WooCommerce Gift Cards
 * @since    1.8.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Gift Cards class.
 *
 * @class   WC_GC_REST_API_Gift_Cards_Controller
 * @extends WC_GC_REST_API_Gift_Cards_V2_Controller
 * @version 1.8.0
 */
class WC_GC_REST_API_Gift_Cards_Controller extends WC_GC_REST_API_Gift_Cards_V2_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wc/v3';
}
