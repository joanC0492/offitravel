<?php
/**
 * Plugin Name: OvaTheme Framework
 * Plugin URI: https://themeforest.net/user/ovatheme/portfolio
 * Description: A plugin to create custom Post Type, Shortcode, Elementor
 * Version: 1.0.2
 * Author: Ovatheme
 * Author URI: https://themeforest.net/user/ovatheme
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ova-framework
 * Domain Path: /languages 
*/

/**
 * Class OvaFramework
 */
if ( !class_exists( 'OvaFramework' ) ) {

    class OvaFramework {

    	/**
         * Constructor
         */
        public function __construct() {
            if ( !defined( 'OVA_PLUGIN_PATH' ) ) {
                define( 'OVA_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );   
            }
            if ( !defined( 'OVA_PLUGIN_URI' ) ) {
                define( 'OVA_PLUGIN_URI', plugin_dir_url( __FILE__ ) ); 
            }

            load_plugin_textdomain( 'ova-framework', false, basename( dirname( __FILE__ ) ) .'/languages' );

            // Register custom post type
            include OVA_PLUGIN_PATH.'inc/class-hf-builder.php';

            // Metabox
            include OVA_PLUGIN_PATH.'inc/class-metaboxes.php';

            // Shortcode
            include OVA_PLUGIN_PATH.'inc/class-shortcode.php';
            
            // Admin enqueue scripts
            add_action( 'admin_enqueue_scripts', [ $this, 'ova_admin_scripts' ] );

            // Share social in single post
            add_filter( 'ova_share_social', [ $this, 'tripgo_content_social' ], 2, 10 );

            // Upload mimes
            add_filter( 'upload_mimes', [ $this, 'ova_upload_mimes' ], 1, 10);

            // Do shortcode in widget text
            add_filter( 'widget_text', 'do_shortcode' );
            
        }
        
        /**
         * Admin enqueue scripts
         */
        public function ova_admin_scripts() {
            wp_enqueue_script( 'script', OVA_PLUGIN_URI. 'assets/js/admin/script.js', [ 'jquery' ], null, true );
            wp_enqueue_style( 'style', OVA_PLUGIN_URI. 'assets/css/admin/style.css', [], null );
        }

        /**
         * Share social
         */
        public function tripgo_content_social( $link, $title ) {
            $html = '<ul class="share-social-icons clearfix">
                <li>
                    <a class="share-ico ico-facebook" target="_blank" href="http://www.facebook.com/sharer.php?u='.esc_url( $link ).'" title="'.esc_attr( $title ).'">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                </li>
                <li>
                    <a class="share-ico ico-twitter" target="_blank" href="https://twitter.com/share?url='.esc_url( $link ).'&amp;text='.urlencode( esc_attr( $title ) ).'&amp;hashtags=simplesharebuttons" title="'.esc_attr( $title ).'">
                        <i class="fab fa-twitter"></i>
                    </a>
                </li>
                <li>
                    <a class="share-ico ico-pinterest" target="_blank" href="https://pinterest.com/pin/create/button/?url='.esc_url( $link ).'" title="'.esc_attr( $title ).'">
                        <i class="fab fa-pinterest-p"></i>
                    </a>
                </li>
            </ul>';

            echo apply_filters( 'ova_share_social_html', $html, $link, $title );
        }

        /**
         * Upload mimes
         */
        public function ova_upload_mimes( $mimes ) {
            $mimes['svg'] = 'image/svg+xml';
            return $mimes;
        }
    }

    // init class
    new OvaFramework();
}