<?php defined( 'ABSPATH' ) || exit();

/**
 * Class OVAEV_Assets
 */
if ( !class_exists( 'OVAEV_Assets' ) ) {

	class OVAEV_Assets {

		/**
		 * Constructor
		 */
		public function __construct() {
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ] );
			add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
		}

		/**
		 * Enqueue styles
		 */
		public function enqueue_styles() {
			wp_enqueue_style( 'elegant_font', OVAEV_PLUGIN_URI.'assets/libs/elegant_font/ele_style.css');
			wp_enqueue_style( 'select2', OVAEV_PLUGIN_URI.'assets/libs/dist/css/select2.min.css' );

			// Moment
	        wp_enqueue_script( 'ova-moment', OVAEV_PLUGIN_URI. 'assets/libs/calendar/moment.min.js', [ 'jquery' ], true, false );
			wp_enqueue_script( 'ova-clndr', OVAEV_PLUGIN_URI.'assets/libs/calendar/clndr.min.js', [ 'jquery' ], true, false );

			// Calendar
			wp_enqueue_style( 'ova-calendar', OVAEV_PLUGIN_URI.'assets/libs/calendar/main.min.css' );

			// Frontend
			wp_enqueue_style( 'event-frontend', OVAEV_PLUGIN_URI.'assets/css/frontend/event.css' );
			wp_enqueue_script( 'event-frontend-js', OVAEV_PLUGIN_URI.'assets/js/frontend/event.js', [ 'jquery' ], false, true );

			wp_enqueue_script( 'select2', OVAEV_PLUGIN_URI.'assets/libs/dist/js/select2.min.js', [ 'jquery' ], false, true );

			wp_enqueue_script( 'ova-calendar', OVAEV_PLUGIN_URI.'assets/libs/calendar/main.min.js', [ 'jquery' ], false, true );

			wp_enqueue_script( 'popper', OVAEV_PLUGIN_URI.'assets/libs/popper.min.js', [ 'jquery' ], false, true );
			wp_enqueue_script( 'tooltip', OVAEV_PLUGIN_URI.'assets/libs/tooltip.min.js', [ 'jquery' ], false, true );

			// Swiper
			if ( is_singular( 'event' ) || is_post_type_archive( [ 'event' ] ) ) { 
				wp_enqueue_style( 'swiper', OVAEV_PLUGIN_URI.'assets/libs/swiper/swiper-bundle.min.css' );
				wp_enqueue_script( 'swiper', OVAEV_PLUGIN_URI.'assets/libs/swiper/swiper-bundle.min.js', [ 'jquery' ], false, true );
			}

			// Datetimepicker
			if ( is_post_type_archive( 'event' ) || is_tax( 'event_category' ) || is_tax( 'event_tag' ) ) {
				wp_enqueue_style( 'datetimepicker-style', OVAEV_PLUGIN_URI.'assets/libs/datetimepicker/jquery.datetimepicker.css' );
				wp_enqueue_script( 'datetimepicker-script', OVAEV_PLUGIN_URI.'assets/libs/datetimepicker/jquery.datetimepicker.js', [ 'jquery' ], false, true );
			}

			// Pretty Photo
			if ( is_singular( 'event' ) ) {
				wp_enqueue_style( 'prettyphoto', OVAEV_PLUGIN_URI.'assets/libs/prettyphoto/css/prettyPhoto.css');
				if ( is_ssl() ) {
					wp_enqueue_script( 'prettyphoto', OVAEV_PLUGIN_URI.'assets/libs/prettyphoto/jquery.prettyPhoto_https.js', [ 'jquery' ], null, true );  
				}
				else{
					wp_enqueue_script( 'prettyphoto', OVAEV_PLUGIN_URI.'assets/libs/prettyphoto/jquery.prettyPhoto.js', [ 'jquery' ], null, true );
				}
			}

			// Add JS
			wp_localize_script( 'event-frontend-js', 'ajax_object', [
				'ajax_url' => admin_url( 'admin-ajax.php' )
			]);
		}

		/**
		 * Admin enqueue scripts
		 */
		public function admin_enqueue_scripts() {
			// Select 2
			wp_enqueue_style( 'select2', OVAEV_PLUGIN_URI.'assets/libs/dist/css/select2.min.css' );
			wp_enqueue_script( 'select2', OVAEV_PLUGIN_URI.'assets/libs/dist/js/select2.min.js', [ 'jquery' ], false, true );
		}
	}

	// init class
	new OVAEV_Assets();
}