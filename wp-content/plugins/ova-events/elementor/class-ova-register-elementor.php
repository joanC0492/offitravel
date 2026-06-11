<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Register new elementor widget.
 */
if ( !class_exists( 'OVAEV_Register_elementor' ) ) {

	class OVAEV_Register_elementor {

		/**
		 * Constructor
		 */
		public function __construct() {
			// Register categories
		    add_action( 'elementor/elements/categories_registered', [ $this, 'register_categories' ] );

		    // After register styles
		    add_action( 'elementor/frontend/after_register_styles', [ $this, 'enqueue_styles' ] );

		    // After register scripts
		    add_action( 'elementor/frontend/after_register_scripts', [ $this, 'enqueue_scripts' ] );

		    // Register widgets
			add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
		}
		
		/**
		 * Register new categories
		 */
		public function register_categories() {
		    \Elementor\Plugin::instance()->elements_manager->add_category(
		        'ovatheme',
		        [
		            'title' => esc_html__( 'Ovatheme', 'ovaev' ),
		            'icon' 	=> 'fa fa-plug'
		        ]
		    );

		    \Elementor\Plugin::instance()->elements_manager->add_category(
		        'ovaev_template',
		        [
		            'title' => esc_html__( 'Event Template', 'ovaev' ),
		            'icon' 	=> 'fa fa-plug'
		        ]
		    );
		}

		/**
		 * Enqueue styles
		 */
		public function enqueue_styles() {
			// Calendar
			wp_register_style( 'ova-calendar', OVAEV_PLUGIN_URI.'assets/libs/calendar/main.min.css' );

			// Datetimpicker
	        wp_register_style( 'ova-datetimepicker', OVAEV_PLUGIN_URI.'assets/libs/datetimepicker/jquery.datetimepicker.css' );
		}

		/**
		 * Enqueue scripts
		 */
		public function enqueue_scripts() {
			// Get all js files
	        $js_files = glob( OVAEV_PLUGIN_PATH.'/assets/js/elementor/*.js' );
	        if ( !empty( $js_files ) && is_array( $js_files ) ) {
	        	foreach ( $js_files as $file ) {
		            $file_name = wp_basename( $file );
		            $handle    = str_replace( '.js', '', $file_name );
		            $src       = OVAEV_PLUGIN_URI.'assets/js/elementor/' . $file_name;

		            if ( file_exists( $file ) ) {
		                wp_register_script( 'ovaev-elementor-' . $handle, $src, ['jquery'], false, true );
		            }
		        }
	        }

	        // Datetimpicker
			wp_register_script( 'ova-datetimepicker', OVAEV_PLUGIN_URI.'assets/libs/datetimepicker/jquery.datetimepicker.js', [ 'jquery' ], false, true );
		}

		/**
		 * Register new widgets
		 */
		public function register_widgets( $widgets_manager ) {
			$files = glob( OVAEV_PLUGIN_PATH.'elementor/widgets/*.php' );
			if ( !empty( $files ) && is_array( $files ) ) {
				foreach ( $files as $file ) {
		            $file = OVAEV_PLUGIN_PATH.'elementor/widgets/' . wp_basename( $file );
		            if ( file_exists( $file ) ) {
		                require_once $file;
		            }
		        }
			}
		}
	}

	// init class
	new OVAEV_Register_elementor();
}