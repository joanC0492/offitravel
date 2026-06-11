<?php if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Class Tripgo_Shortcode
 */
if ( !class_exists( 'Tripgo_Shortcode' ) ) {
    
    class Tripgo_Shortcode {

        /**
         * Constructor
         */
        public function __construct() {
            add_shortcode( 'tripgo-elementor-template', [ $this, 'tripgo_elementor_template' ] );
        }

        /**
         * Elementor template
         */
        public function tripgo_elementor_template( $atts ) {
            $atts = extract( shortcode_atts( [ 'id'  => '' ], $atts ));
            $args = [ 'id' => $id ];

            if ( did_action( 'elementor/loaded' ) ) {
                return Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $id );    
            }

            return;
        }
    }

    // init class
    new Tripgo_Shortcode();
}