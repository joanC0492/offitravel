<?php defined( 'ABSPATH' ) || exit;

/**
 * Class OVAEV_Shortcode_Event_Tabs
 */
if ( !class_exists( 'OVAEV_Shortcode_Event_Tabs' ) ) {

	class OVAEV_Shortcode_Event_Tabs {

		/**
		 * Shortcode name
		 */
		public $shortcode = 'ovaev_shortcode_tabs';

		/**
		 * Constructor
		 */
		public function __construct() {
			add_shortcode( $this->shortcode, [ $this, 'init_shortcode' ] );
		}

		/**
		 * init shortcode
		 */
		public function init_shortcode( $args, $content = null ) {
			// Get content
			$content = get_the_content( get_the_ID() );
			
			if ( is_page() && has_shortcode( $content, 'ovaev_shortcode_tabs') ) {
				wp_enqueue_style( 'prettyphoto', OVAEV_PLUGIN_URI.'assets/libs/prettyphoto/css/prettyPhoto.css' );
				if ( is_ssl() ) {
					wp_enqueue_script( 'prettyphoto', OVAEV_PLUGIN_URI.'assets/libs/prettyphoto/jquery.prettyPhoto_https.js', [ 'jquery' ], null, true );  
				} else {
					wp_enqueue_script( 'prettyphoto', OVAEV_PLUGIN_URI.'assets/libs/prettyphoto/jquery.prettyPhoto.js', [ 'jquery' ], null, true );
				}
			}

			if ( !empty( $args ) ) {
				$args = [
					'id' 	=> isset( $args['id'] ) ? (int)$args['id'] : get_the_id(),
					'class' => isset( $args['class'] ) ? $args['class'] : ''
				];
			} else {
				$args = [
					'id' 	=> get_the_id(),
					'class' => ''
				];
			}

			// Get template
			$template = apply_filters( 'shortcode_ovaev_title', 'shortcode/ovaev_event_tabs.php' );

			ob_start();
			ovaev_get_template( $template, $args );
			return ob_get_clean();
		}
	}

	// init class
	new OVAEV_Shortcode_Event_Tabs();
}