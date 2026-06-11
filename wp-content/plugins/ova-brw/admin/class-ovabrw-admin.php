<?php if ( !defined( 'ABSPATH' ) ) exit();

/**
 * Class OVABRW_Admin
 */
if ( !class_exists( 'OVABRW_Admin' ) ) {

    class OVABRW_Admin {

        /**
         * Constructor
         */
        public function __construct() {
            if ( current_user_can( 'administrator' ) || current_user_can( 'edit_posts' ) ) {
                // Core functions
                require_once( OVABRW_PLUGIN_ADMIN . 'ovabrw-admin-core-functions.php' );

                // Add menus
                add_action( 'admin_menu', [ $this, 'ovabrw_add_new_sub_menus' ], 11 );

                // Menus
                require_once( OVABRW_PLUGIN_ADMIN . 'class-ovabrw-admin-menus.php' );

                // Setting
                require_once( OVABRW_PLUGIN_ADMIN . 'class-ovabrw-admin-settings.php' );

                // Ajaxs
                require_once( OVABRW_PLUGIN_ADMIN . 'class-ovabrw-admin-ajax.php' );
                
                // Display Table Order 
                require_once( OVABRW_PLUGIN_ADMIN . 'order/class_render_table_order.php' );

                // Booking calendar
                require_once( OVABRW_PLUGIN_ADMIN . 'class-ovabrw-admin-bookings.php' );

                // Custom checkout fields
                require_once( OVABRW_PLUGIN_ADMIN . 'custom-checkout-fields/class_custom_checkout_field.php' );

                // Guest fields
                require_once( OVABRW_PLUGIN_ADMIN . 'guest-info-fields/class-ovabrw-guest-info-fields.php' );

                // Custom taxonomies
                require_once( OVABRW_PLUGIN_ADMIN . 'custom-taxonomy/custom_taxonomy.php' );

                // Category
                require_once( OVABRW_PLUGIN_ADMIN . 'category/init.php' );

                // Save database
                require_once( OVABRW_PLUGIN_ADMIN . 'class-ovabrw-admin-orders.php' );
            }
            
            // Add meta-boxes
            require_once( OVABRW_PLUGIN_ADMIN . 'class-ovabrw-admin-metabox.php' );
        }
        
        /**
         * Add new sub-menus
         */
        public function ovabrw_add_new_sub_menus() {
            add_submenu_page(
                'ovabrw-settings',
                esc_html__( 'Custom taxonomies', 'ova-brw' ),
                esc_html__( 'Custom taxonomies', 'ova-brw' ),
                apply_filters( OVABRW_PREFIX.'submenu_custom_taxonomies_capability', 'manage_options' ),
                'ovabrw-custom-taxonomy',
                'ovabrw_custom_taxonomy',
                4
            );

            add_submenu_page(
                'ovabrw-settings',
                esc_html__( 'Manage bookings', 'ova-brw' ),
                esc_html__( 'Manage bookings', 'ova-brw' ),
                apply_filters( OVABRW_PREFIX.'submenu_manange_order_capability', 'edit_posts' ),
                'ovabrw-manage-order',
                'ovabrw_manage_booking',
                5
            );

            // Create order
            add_submenu_page(
                'ovabrw-settings',
                esc_html__( 'Create new booking', 'ova-brw' ),
                esc_html__( 'Create new booking', 'ova-brw' ),
                apply_filters( OVABRW_PREFIX.'submenu_create_order_capability', 'edit_posts' ),
                'ovabrw-create-order',
                'ovabrw_create_new_booking',
                6
            );

            add_submenu_page(
                'ovabrw-settings',
                esc_html__( 'Check Product', 'ova-brw' ),
                esc_html__( 'Check Product', 'ova-brw' ),
                apply_filters( OVABRW_PREFIX.'submenu_check_product_capability', 'edit_posts' ),
                'ovabrw-check-product',
                'ovabrw_check_product_view',
                8
            );

            add_submenu_page(
                'ovabrw-settings',
                esc_html__( 'Custom Checkout Field', 'ova-brw' ),
                esc_html__( 'Custom Checkout Field', 'ova-brw' ),
                apply_filters( OVABRW_PREFIX.'submenu_cckf_capability', 'manage_options' ),
                'ovabrw-custom-checkout-field',
                'ovabrw_custom_checkout_field',
                9
            );
        }
    }

    // Register class
    new OVABRW_Admin();
}