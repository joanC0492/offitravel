<?php if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Class OVAEV_Templates_Loader
 */
if ( !class_exists( 'OVAEV_Templates_Loader' ) ) {

	class OVAEV_Templates_Loader {
		
		/**
		 * The Constructor
		 */
		public function __construct() {
			add_filter( 'template_include', [ $this, 'template_loader' ] );
		}

		/**
		 * Template loader
		 */
		public function template_loader( $template ) {
			// Get post type
			$post_type = isset( $_REQUEST['post_type'] ) ? $_REQUEST['post_type'] : get_post_type();

			// is search
			$search = isset( $_REQUEST['search_event'] ) ? $_REQUEST['search_event'] : '';
			
			if ( is_tax( 'event_category' ) || get_query_var( 'event_category' ) != '' || is_tax( 'event_tag' ) || get_query_var( 'event_tag' ) != '' ) {
				ovaev_get_template( 'archive-event.php' );
				return false;
			}
			
			// is event post type
			if ( 'event' == $post_type ) {
				if ( $search != '' || is_post_type_archive( 'event' )  ) { 
					ovaev_get_template( 'archive-event.php' );
					return false;
				} elseif ( is_single() ) {
					ovaev_get_template( 'single-event.php' );
					return false;
				}
			}

			return $template;
		}
	}

	// init class
	new OVAEV_Templates_Loader();
}