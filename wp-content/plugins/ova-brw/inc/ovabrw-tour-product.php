<?php if ( !defined( 'ABSPATH' ) ) exit();

/**
 * Class WC_Product_Ovabrw_Car_Rental
 */
if ( !class_exists( 'WC_Product_Ovabrw_Car_Rental' ) ) {

	class WC_Product_Ovabrw_Car_Rental extends WC_Product {

		/**
		 * Prefix product
		 */
		protected $prefix = OVABRW_PREFIX.'product_';

		/**
		 * Init rental product
		 */
		public function __construct( $product = 0 ) {
	        parent::__construct( $product );
	    }

	    /**
	     * Get product type
	     */
	    public function get_type() {
	        return OVABRW_RENTAL;
	    }

	    /**
	     * Get tour meta key
	     */
	    public function get_meta_key( $key = '' ) {
	        if ( $key ) $key = OVABRW_PREFIX.$key;

	        return apply_filters( $this->prefix.'get_meta_key', $key );
	    }

	    /**
	     * Get meta value by key
	     */
	    public function get_meta_value( $key = '', $default = false ) {
	        $value = $this->get_meta( $this->get_meta_key( $key ) );

	        if ( !$value && $default !== false ) $value = $default;

	        return apply_filters( $this->prefix.'get_meta_value', $value, $key, $default, $this );
	    }

	    /**
	     * Has time slots
	     */
	    public function has_time_slots() {
	    	return $this->get_meta_value( 'duration_checkbox' ) ? true : false;
	    }
	}
}