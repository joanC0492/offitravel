<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class OVABRW_Register_Elementor
 */
if ( !class_exists( 'OVABRW_Register_Elementor' ) ) {

	class OVABRW_Register_Elementor {

		/**
		 * Constructor
		 */
		public function __construct() {
			// Register Ovatheme Category in Pane
		    add_action( 'elementor/elements/categories_registered', [ $this, 'categories_registered' ] );

		    // After register styles
		    add_action( 'elementor/frontend/after_register_styles', [ $this, 'enqueue_styles' ] );

		    // After register scripts
		    add_action( 'elementor/frontend/after_register_scripts', [ $this, 'enqueue_scripts' ] );

		    // Register widgets
			add_action( 'elementor/widgets/register', [ $this, 'widgets_register' ] );
		}
		
		/**
		 * Register categories
		 */
		public function categories_registered(  ) {
		    \Elementor\Plugin::instance()->elements_manager->add_category(
		        'ovabrw-tours',
		        [
		            'title' => esc_html__( 'Tours', 'ova-brw' ),
		            'icon' 	=> 'fa fa-plug',
		        ]
		    );

		    \Elementor\Plugin::instance()->elements_manager->add_category(
		        'ovabrw-product-templates',
		        [
		            'title' => esc_html__( 'Product Template', 'ova-brw' ),
		            'icon' 	=> 'fa fa-plug',
		        ]
		    );
		}

		/**
		 * Enqueue styles
		 */
		public function enqueue_styles() {
            // Get all css files
	        $css_files = glob( OVABRW_PLUGIN_PATH.'assets/css/elementor/*.css' );
	        if ( !empty( $css_files ) && is_array( $css_files ) ) {
	        	foreach ( $css_files as $file ) {
		            $file_name = wp_basename( $file );
		            $handle    = str_replace( '.css', '', $file_name );
		            $src       = OVABRW_PLUGIN_URI.'assets/css/elementor/' . $file_name ;

		            if ( file_exists( $file ) ) {
		                wp_register_style( 'ovabrw-elementor-' . $handle, $src );
		            }
		        }
	        }

	        // Enqueue jquery UI
			wp_register_style( 'ovabrw-jquery-ui', OVABRW_PLUGIN_URI.'assets/libs/jquery-ui/jquery-ui.css' );
		}

		/**
		 * Enqueue scripts
		 */
		public function enqueue_scripts() {
			// Get all js files
	        $js_files = glob( OVABRW_PLUGIN_PATH.'/assets/js/elementor/*.js' );
	        if ( !empty( $js_files ) && is_array( $js_files ) ) {
	        	foreach ( $js_files as $file ) {
		            $file_name = wp_basename( $file );
		            $handle    = str_replace( '.js', '', $file_name );
		            $src       = OVABRW_PLUGIN_URI.'assets/js/elementor/' . $file_name;

		            if ( file_exists( $file ) ) {
		                wp_register_script( 'ovabrw-elementor-' . $handle, $src, ['jquery'], filemtime( $file ), true );
		            }
		        }
	        }

	        // Enqueue jquery UI
			wp_register_script( 'ovabrw-jquery-ui', OVABRW_PLUGIN_URI.'assets/libs/jquery-ui/jquery-ui.min.js', [ 'jquery' ], false, true );
			wp_register_script( 'ovabrw-jquery-ui-touch', OVABRW_PLUGIN_URI.'assets/libs/jquery-ui/jquery.ui.touch-punch.min.js', [ 'jquery' ], false, true );
		}

		/**
		 * Register widgets
		 */
		public function widgets_register( $widgets_manager ) {
			$widget_files = glob( OVABRW_PLUGIN_PATH . 'elementor/widgets/*.php' );

			if ( ovabrw_array_exists( $widget_files ) ) {
				foreach ( $widget_files as $file ) {
		            $path = OVABRW_PLUGIN_PATH . 'elementor/widgets/' . wp_basename( $file );
		            if ( file_exists( $path ) ) {
		                require_once $path;
		            }
		        }
			}
		}
	}

	new OVABRW_Register_Elementor();
}