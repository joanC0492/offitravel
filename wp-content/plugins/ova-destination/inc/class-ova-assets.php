<?php if ( !defined( 'ABSPATH' ) ) exit;

/**
 * OVADES_Assets
 */
if ( !class_exists( 'OVADES_Assets' ) ) {

	class OVADES_Assets {

		/**
		 * Constructor
		 */
		public function __construct() {
            // Add JS, CSS for admin
			add_action( 'admin_enqueue_scripts', [ $this, 'ovadestination_admin_enqueue_scripts' ] );
            
            // Add JS, CSS for frontend
			add_action( 'wp_enqueue_scripts', [ $this, 'ovadestination_enqueue_scripts' ] );
		}

		/**
		 * Enqueue scripts
		 */
		public function ovadestination_admin_enqueue_scripts() {
			// Add JS
			wp_enqueue_script( 'script-admin-destination', OVADESTINATION_PLUGIN_URI. 'assets/js/script-admin.js', [ 'jquery' ], false, true );		
			wp_localize_script( 'script-admin-destination', 'ovabrwCategoriesDetination', [
				'url' 	=> get_admin_url().'edit-tags.php?taxonomy=cat_destination&post_type=destination',
				'title' => esc_html__( 'Destination categories', 'ova-destination' )
			]);

			// Init Css
			wp_enqueue_style( 'destination_style', OVADESTINATION_PLUGIN_URI.'assets/css/admin-style.css' );
		}

		/**
		 * Enqueue scripts
		 */
		public function ovadestination_enqueue_scripts() {
			// Imagesloaded
			wp_enqueue_script( 'script-destination-imagesloaded', OVADESTINATION_PLUGIN_URI. 'assets/libs/imagesloaded/imagesloaded.min.js', [ 'jquery' ], false, true );

			// Masonry 
			wp_enqueue_script( 'script-destination-masonry', OVADESTINATION_PLUGIN_URI. 'assets/libs/masonry/masonry.min.js', [ 'jquery' ], false, true );

			// Add JS
			wp_enqueue_script( 'script-destination', OVADESTINATION_PLUGIN_URI. 'assets/js/script.js', [ 'jquery' ], false, true );	

			// Fontawesome
			if ( is_post_type_archive( 'destination' ) ) {
				wp_enqueue_style( 'fontawesome', OVADESTINATION_PLUGIN_URI.'/assets/libs/fontawesome/css/all.min.css', [], null );	
			}

			// Init Css
			wp_enqueue_style( 'destination_style', OVADESTINATION_PLUGIN_URI.'assets/css/style.css' );

			// API key
			$api_key = get_option( 'ova_brw_google_key_map', '' );
			if ( $api_key ) {
				wp_enqueue_script( 'ovabrw-google-maps', 'https://maps.googleapis.com/maps/api/js?key='.$api_key.'&libraries=places&loading=async&callback=Function.prototype', false, true );
			}

			// Swiper
			if ( is_single() ) {
				wp_enqueue_style( 'swiper', OVADESTINATION_PLUGIN_URI.'/assets/libs/swiper/swiper-bundle.min.css' );
		    	wp_enqueue_script( 'swiper', OVADESTINATION_PLUGIN_URI.'/assets/libs/swiper/swiper-bundle.min.js', [ 'jquery' ], false, true );
			}

			// Fancybox
			wp_enqueue_script( 'script-destination-fancybox', OVADESTINATION_PLUGIN_URI. 'assets/libs/fancybox/fancybox.umd.js', [ 'jquery' ], false, true );
			wp_enqueue_style( 'destination_fancybox_style', OVADESTINATION_PLUGIN_URI.'assets/libs/fancybox/fancybox.css' );
		}
	}
	
	// init class
	new OVADES_Assets();
}
