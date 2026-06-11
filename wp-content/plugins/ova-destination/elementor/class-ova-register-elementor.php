<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class OVADES_Register_Elementor
 */
if ( !class_exists( 'OVADES_Register_Elementor' ) ) {

	class OVADES_Register_Elementor {

		/**
		 * Constructor
		 */
		public function __construct() {
			// Register category
		    add_action( 'elementor/elements/categories_registered', [ $this, 'register_category' ] );

		    // After register styles
		    add_action( 'elementor/frontend/after_register_styles', [ $this, 'enqueue_styles' ] );

		    // After register scripts
		    add_action( 'elementor/frontend/after_register_scripts', [ $this, 'enqueue_scripts' ] );
			
			// Register widgets
			add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
		}

		/**
		 * Register category
		 */
		public function register_category(  ) {
		    \Elementor\Plugin::instance()->elements_manager->add_category(
		        'destination',
		        [
		            'title' => esc_html__( 'Destination', 'ova-destination' ),
		            'icon' 	=> 'fa fa-plug'
		        ]
		    );
		}

		/**
		 * Enqueue styles
		 */
		public function enqueue_styles() {
            // Get all css files
	        $css_files = glob( OVADESTINATION_PLUGIN_PATH.'assets/css/elementor/*.css' );
	        if ( !empty( $css_files ) && is_array( $css_files ) ) {
	        	foreach ( $css_files as $file ) {
		            $file_name = wp_basename( $file );
		            $handle    = str_replace( '.css', '', $file_name );
		            $src       = OVADESTINATION_PLUGIN_URI.'assets/css/elementor/' . $file_name ;

		            if ( file_exists( $file ) ) {
		                wp_register_style( 'ovades-elementor-' . $handle, $src );
		            }
		        }
	        }
		}

		/**
		 * Enqueue scripts
		 */
		public function enqueue_scripts() {
			// Get all js files
	        $js_files = glob( OVADESTINATION_PLUGIN_PATH.'/assets/js/elementor/*.js' );
	        if ( !empty( $js_files ) && is_array( $js_files ) ) {
	        	foreach ( $js_files as $file ) {
		            $file_name = wp_basename( $file );
		            $handle    = str_replace( '.js', '', $file_name );
		            $src       = OVADESTINATION_PLUGIN_URI.'assets/js/elementor/' . $file_name;

		            if ( file_exists( $file ) ) {
		                wp_register_script( 'ovades-elementor-' . $handle, $src, ['jquery'], false, true );
		            }
		        }
	        }
		}

		/**
		 * Register widget
		 */
		public function register_widgets( $widgets_manager ) {
			$files = glob( OVADESTINATION_PLUGIN_PATH.'elementor/widgets/*.php' );
			if ( !empty( $files ) && is_array( $files ) ) {
				foreach ( $files as $file ) {
		            $file = OVADESTINATION_PLUGIN_PATH.'elementor/widgets/' . wp_basename( $file );
		            if ( file_exists( $file ) ) {
		                require_once $file;
		            }
		        }
			}
		}
	}

	// init class
	new OVADES_Register_Elementor();
}