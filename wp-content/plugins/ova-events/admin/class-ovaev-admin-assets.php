<?php defined( 'ABSPATH' ) || exit();

/**
 * Class OVAEV_Admin_Assets
 */
if ( !class_exists( 'OVAEV_Admin_Assets' ) ) {

	class OVAEV_Admin_Assets {

		/**
		 * Constructor
		 */
		public function __construct() {
			add_action( 'admin_footer', [ $this, 'enqueue_scripts' ], 10, 2 );
		}

		/**
		 * Enqueue scripts
		 */
		public function enqueue_scripts() {
			global $pagenow, $post_type;

			// Media
			wp_enqueue_script( 'media-upload' );
		    wp_enqueue_script( 'thickbox' );

		    // Color picker
		    wp_enqueue_script( 'wp-color-picker' );
    		wp_enqueue_style( 'wp-color-picker' );

			// Init Css Admin
			wp_enqueue_style( 'ovaev-admin-style', OVAEV_PLUGIN_URI.'assets/css/admin/ovaev-admin-style.css' );

			// Init JS Admin
			wp_register_script( 'ovaev-admin-script', OVAEV_PLUGIN_URI.'assets/js/admin/ovaev-admin-script.js', [ 'jquery', 'media-upload', 'thickbox' ], false, true );
			wp_enqueue_script('ovaev-admin-script');

			// Jquery UI
			wp_enqueue_style( 'jquery-ui', OVAEV_PLUGIN_URI.'assets/libs/jquery-ui/jquery-ui.min.css' );
			wp_enqueue_script( 'jquery-ui-tabs' );

			// Jquery Datetimepicker
			if ( ( $pagenow === 'post-new.php' || $pagenow === 'post.php' ) && $post_type === 'event' ) {
		        wp_enqueue_style( 'datetimepicker-style', OVAEV_PLUGIN_URI.'assets/libs/datetimepicker/jquery.datetimepicker.css' );
				wp_enqueue_script( 'datetimepicker-script', OVAEV_PLUGIN_URI.'assets/libs/datetimepicker/jquery.datetimepicker.js', [ 'jquery' ], false, true );
		    }
		    
		}
	}

	// init class
	new OVAEV_Admin_Assets();
}