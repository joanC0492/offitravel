<?php if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Class OVADES_Templates_Loader
 */
if ( !class_exists( 'OVADES_Templates_Loader' ) ) {

	class OVADES_Templates_Loader {
		
		/**
		 * Constructor
		 */
		public function __construct() {
			add_filter( 'template_include', [ $this, 'template_loader' ] );
		}

		/**
		 * Template loader
		 */
		public function template_loader( $template ) {
			if ( is_tax( 'cat_destination' ) ||  get_query_var( 'cat_destination' ) != '' ) {
				$paged = get_query_var('paged') ? get_query_var('paged') : '1';
				query_posts( '&cat_destination='.get_query_var( 'cat_destination' ).'&paged=' . $paged );
				ovadestination_get_template( 'archive-destination.php' );

				return false;
			}

			// Get post type
			$post_type = isset( $_REQUEST['post_type'] ) ? esc_html( $_REQUEST['post_type'] ) : get_post_type();

			// is destination post type
			if (  $post_type == 'destination' ) {
				if ( is_post_type_archive( 'destination' ) ) { 
					ovadestination_get_template( 'archive-destination.php' );
					return false;
				} else if ( is_single() ) {
					ovadestination_get_template( 'single-destination.php' );
					return false;
				}
			}

			if ( $post_type !== 'destination' ) {
				return $template;
			}
		}
	}

	// init class
	new OVADES_Templates_Loader();
}