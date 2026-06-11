<?php if ( !defined( 'ABSPATH' ) ) exit();

/**
 * Class OVABRW_Admin_Menus
 */
if ( !class_exists( 'OVABRW_Admin_Menus', false ) ) {

	class OVABRW_Admin_Menus {

		/**
		 * Constructor
		 */
		public function __construct() {
			// Register menu
			add_action( 'admin_menu', [ $this, 'register_menu' ] );

			// Active menu "Tripgo" when access destination categories
			add_filter( 'parent_file', [ $this, 'parent_file' ] );

			// Active sub-menu "Destinations" when access destination categories
			add_filter( 'submenu_file', [ $this, 'submenu_file' ] );
		}

		/**
		 * Register menu
		 */
		public function register_menu() {
			add_menu_page(
                esc_html__( 'Tripgo', 'ova-brw' ),
                esc_html__( 'Tripgo', 'ova-brw' ),
                apply_filters( OVABRW_PREFIX.'menu_brw_capability', 'edit_posts' ),
                'ovabrw-settings',
                [ $this, 'view_preview_page' ],
				'dashicons-calendar-alt',
				55.4
            );

            // Add sub-menu home
            add_submenu_page( 
				'ovabrw-settings', 
				esc_html__( 'Home', 'ova-brw' ), 
				esc_html__( 'Home', 'ova-brw' ), 
				apply_filters( OVABRW_PREFIX.'submenu_home_capability', 'edit_posts' ),
				'home',
				[ $this, 'view_preview_page' ],
				0
			);

            // Add sub-menu product
            add_submenu_page( 
				'ovabrw-settings', 
				esc_html__( 'Products', 'ova-brw' ), 
				esc_html__( 'Products', 'ova-brw' ), 
				apply_filters( OVABRW_PREFIX.'submenu_products_capability', 'edit_posts' ),
				'edit.php?post_type=product&post_status=all&product_type=ovabrw_car_rental&paged=1',
				null,
				1
			);

			// Add sub-menu categories
            add_submenu_page( 
				'ovabrw-settings', 
				esc_html__( 'Categories', 'ova-brw' ), 
				esc_html__( 'Categories', 'ova-brw' ), 
				apply_filters( OVABRW_PREFIX.'submenu_category_capability', 'edit_posts' ),
				'edit-tags.php?taxonomy=product_cat&post_type=product',
				null,
				2
			);

			// Add sub-menu settings
            add_submenu_page( 
				'ovabrw-settings', 
				esc_html__( 'Settings', 'ova-brw' ), 
				esc_html__( 'Settings', 'ova-brw' ), 
				apply_filters( OVABRW_PREFIX.'submenu_settings_capability', 'edit_posts' ),
				'admin.php?page=wc-settings&tab=ova_brw',
				null
			);

            // Remove sub-menu
			remove_submenu_page( 'ovabrw-settings', 'ovabrw-settings' );
		}

		/**
		 * View home page
		 */
		public function view_preview_page() {
			include( OVABRW_PLUGIN_PATH . 'admin/menus/views/html-preview.php' );
		}

		/**
		 * Parent file
		 */
		public function parent_file( $parent_file ) {
			global $current_screen;

		    if ( 'cat_destination' === $current_screen->taxonomy || str_contains( $current_screen->taxonomy, 'brw_' ) ) {
		        return 'ovabrw-settings';
		    }

		    return $parent_file;
		}

		/**
		 * Submenu file
		 */
		public function submenu_file( $submenu_file ) {
			global $current_screen;

		    if ( 'cat_destination' === $current_screen->taxonomy ) {
		        return 'edit.php?post_type=destination';
		    } elseif ( str_contains( $current_screen->taxonomy, 'brw_' ) ) {
		    	global $plugin_page;
		    	$plugin_page = 'ovabrw-custom-taxonomy';
		    	return null;
		    }

		    return $submenu_file;
		}
	}

	// init class
	new OVABRW_Admin_Menus();
}