<?php if ( !defined( 'ABSPATH' ) ) exit();

/**
 * Class OVABRW_Admin_Settings
 */
if ( !class_exists( 'OVABRW_Admin_Settings' ) ) {

    class OVABRW_Admin_Settings {

        /**
         * Constructor
         */
        public function __construct() {
            // Add settings pages
            add_filter( 'woocommerce_get_settings_pages', [ $this, 'add_setting_page' ] );
        }

        /**
         * Add setting page
         */
        public function add_setting_page( $settings ) {
            $settings[] = include( OVABRW_PLUGIN_PATH . 'admin/settings/class-ovabrw-booking-tours-settings.php' );

            return $settings;
        }
    }

    // init class
    new OVABRW_Admin_Settings();
}