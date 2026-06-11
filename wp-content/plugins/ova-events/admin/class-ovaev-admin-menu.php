<?php defined( 'ABSPATH' ) || exit();

/**
 * Class OVAEV_Admin_Menu
 */
if ( !class_exists( 'OVAEV_Admin_Menu' ) ) {

	class OVAEV_Admin_Menu {

		/**
		 * Constructor
		 */
		public function __construct() {
			// Add menu
			add_action( 'admin_menu', [ $this, 'register_menu' ] );

			// Parent file
			add_filter( 'parent_file', [ $this, 'parent_file' ] );

			// Submenu file
			add_filter( 'submenu_file', [ $this, 'submenu_file' ], 11 );
		}

		/**
		 * Register menu
		 */
		public function register_menu() {
			// Get Options
			add_menu_page( 
				esc_html__( 'Events', 'ovaev' ), 
				esc_html__( 'Events', 'ovaev' ), 
				'edit_posts',
				'ovaev-menu', 
				null,
				'dashicons-calendar', 
				20
			);

			add_submenu_page( 
				'ovaev-menu', 
				esc_html__( 'Categories', 'ovaev' ), 
				esc_html__( 'Categories', 'ovaev' ), 
				'administrator',
				'edit-tags.php?taxonomy=event_category'.'&post_type=event'
			);

			add_submenu_page( 
				'ovaev-menu', 
				esc_html__( 'Tags', 'ovaev' ), 
				esc_html__( 'Tags', 'ovaev' ), 
				'administrator',
				'edit-tags.php?taxonomy=event_tag'.'&post_type=event'
			);

			add_submenu_page( 
				'ovaev-menu', 
				esc_html__( 'Settings', 'ovaev' ),
				esc_html__( 'Settings', 'ovaev' ),
				'administrator',
				'ovaev_general_settings',
				[ 'OVAEV_Admin_Settings', 'create_admin_setting_page' ]
			);
		}

		/**
		 * Parent file
		 */
		public function parent_file( $parent_file ) {
			global $current_screen;

		    if ( str_contains( $current_screen->taxonomy, 'event_' ) ) {
		        return 'ovaev-menu';
		    }

			return $parent_file;
		}

		/**
		 * Submenu file
		 */
		public function submenu_file( $submenu_file ) {
			global $current_screen;

		    if ( 'event_category' === $current_screen->taxonomy ) {
		    	return 'edit-tags.php?taxonomy=event_category&post_type=event';
		    } elseif ( 'event_tag' === $current_screen->taxonomy ) {
		    	return 'edit-tags.php?taxonomy=event_tag&post_type=event';
		    }

		    return $submenu_file;
		}
	}

	// init class
	new OVAEV_Admin_Menu();
}