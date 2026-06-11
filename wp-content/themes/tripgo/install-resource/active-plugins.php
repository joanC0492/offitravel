<?php if ( !defined( 'ABSPATH' ) ) exit;

// require tgm plugin activation
require_once (TRIPGO_URL.'/install-resource/class-tgm-plugin-activation.php');

/**
 * Register plugins
 */
add_action( 'tgmpa_register', function() {
    $plugins = [
        [
            'name'      => esc_html__( 'Elementor', 'tripgo' ),
            'slug'      => 'elementor',
            'required'  => true
        ],
        [
            'name'      => esc_html__( 'Contact Form 7', 'tripgo' ),
            'slug'      => 'contact-form-7',
            'required'  => true
        ],
        [
            'name'      => esc_html__( 'Widget importer exporter', 'tripgo' ),
            'slug'      => 'widget-importer-exporter',
            'required'  => true
        ],
        [
            'name'      => esc_html__( 'One click demo import', 'tripgo' ),
            'slug'      => 'one-click-demo-import',
            'required'  => true
        ],
        [
            'name'      => esc_html__( 'OvaTheme Framework', 'tripgo' ),
            'slug'      => 'ova-framework',
            'required'  => true,
            'source'    => get_template_directory() . '/install-resource/plugins/ova-framework.zip',
            'version'   => '1.0.2'
        ],
        [
            'name'      => esc_html__( 'Woocommerce', 'tripgo' ),
            'slug'      => 'woocommerce',
            'required'  => true
        ],
        [
            'name'      => esc_html__( 'Travel and Tour Booking', 'tripgo' ),
            'slug'      => 'ova-brw',
            'required'  => true,
            'source'    => get_template_directory() . '/install-resource/plugins/ova-brw.zip',
            'version'   => '2.0.2'
        ],
        [
            'name'      => esc_html__( 'OvaTheme Destination', 'tripgo' ),
            'slug'      => 'ova-destination',
            'required'  => true,
            'source'    => get_template_directory() . '/install-resource/plugins/ova-destination.zip',
            'version'   => '1.1.3'
        ],
        [
            'name'      => esc_html__( 'OvaTheme Events', 'tripgo' ),
            'slug'      => 'ova-events',
            'required'  => true,
            'source'    => get_template_directory() . '/install-resource/plugins/ova-events.zip',
            'version'   => '1.3.1'
        ],
        [
            'name'      => esc_html__( 'Ovatheme MegaMenu', 'tripgo' ),
            'slug'      => 'ova-megamenu',
            'required'  => true,
            'source'    => get_template_directory() . '/install-resource/plugins/ova-megamenu.zip',
            'version'   => '1.0.2'
        ],
        [
            'name'      => esc_html__( 'CMB2', 'tripgo' ),
            'slug'      => 'cmb2',
            'required'  => true
        ],
        [
            'name'      => esc_html__( 'Mailchimp', 'tripgo' ),
            'slug'      => 'mailchimp-for-wp',
            'required'  => true
        ],
        [
            'name'      => esc_html__( 'YITH WooCommerce Wishlist', 'tripgo' ),
            'slug'      => 'yith-woocommerce-wishlist',
            'required'  => true,
            'priority'  => 2
        ],
        [
            'name'      => esc_html__( 'Woo multi currency', 'tripgo' ),
            'slug'      => 'woo-multi-currency',
            'required'  => true,
            'priority'  => 3
        ]
    ];

    $config = [
        'id'            => 'tripgo', // Unique ID for hashing notices for multiple instances of TGMPA.
        'default_path'  => '', // Default absolute path to bundled plugins.
        'menu'          => 'tgmpa-install-plugins', // Menu slug.
        'has_notices'   => true, // Show admin notices or not.
        'dismissable'   => true, // If false, a user cannot dismiss the nag message.
        'dismiss_msg'   => '', // If 'dismissable' is false, this message will be output at top of nag.
        'is_automatic' => false, // Automatically activate plugins after installation or not.
        'message'      => '' // Message to output right before the plugins table.
    ];

    tripgo_tgmpa( $plugins, $config );
});

/**
 * Before import demo data
 */
add_action( 'ocdi/before_content_import', function( $selected_import ) {
    // Custom Taxonomies
    $custom_taxonomies = tripgo_recursive_replace( '\\', '', get_option( 'ovabrw_custom_taxonomy', array() ) );
    if ( empty( $custom_taxonomies ) && isset( $selected_import['import_file_name'] ) && 'Demo Import' === $selected_import['import_file_name'] ) {
        $import_file    = isset( $selected_import['local_import_file'] ) ? $selected_import['local_import_file'] : '';
        $taxonomies     = array();

        if ( $import_file ) {
            $xml = simplexml_load_file($import_file);

            if ( isset( $xml->channel->ovabrw_custom_taxonomies ) ) {
                foreach ( $xml->channel->ovabrw_custom_taxonomies as $k => $obj_taxonomy ) {
                    if ( is_object( $obj_taxonomy ) ) {
                        $arr_taxonomy = get_object_vars( $obj_taxonomy );

                        if ( ! empty( $arr_taxonomy ) && is_array( $arr_taxonomy ) ) {
                            if ( isset( $arr_taxonomy['slug'] ) && $arr_taxonomy['slug'] ) {
                                $taxonomies[$arr_taxonomy['slug']] = [
                                    'name'              => isset( $arr_taxonomy['name'] ) ? $arr_taxonomy['name'] : '',
                                    'singular_name'     => isset( $arr_taxonomy['singular_name'] ) ? $arr_taxonomy['singular_name'] : '',
                                    'label_frontend'    => isset( $arr_taxonomy['label_frontend'] ) ? $arr_taxonomy['label_frontend'] : '',
                                    'enabled'           => isset( $arr_taxonomy['enabled'] ) ? $arr_taxonomy['enabled'] : '',
                                    'show_listing'      => isset( $arr_taxonomy['show_listing'] ) ? $arr_taxonomy['show_listing'] : ''
                                ];
                            }
                        }
                    }
                }
            }
        }

        if ( ! empty( $taxonomies ) && is_array( $taxonomies ) ) {
            update_option('ovabrw_custom_taxonomy', $taxonomies);

            if ( function_exists( 'ovabrw_create_type_taxonomies' ) ) {
                $taxonomies = ovabrw_create_type_taxonomies();
            }
        }
    }

    // Custom Checkout Fields
    $checkout_fields = tripgo_recursive_replace( '\\', '', get_option( 'ovabrw_booking_form', [] ) );
    if ( empty( $checkout_fields ) && isset( $selected_import['import_file_name'] ) && 'Demo Import' === $selected_import['import_file_name'] ) {
        $import_file    = isset( $selected_import['local_import_file'] ) ? $selected_import['local_import_file'] : '';
        $fields         = array();

        if ( $import_file ) {
            $xml = simplexml_load_file($import_file);

            if ( isset( $xml->channel->ovabrw_custom_checkout_fields ) ) {
                foreach ( $xml->channel->ovabrw_custom_checkout_fields as $k => $obj_checkout_field ) {
                    if ( is_object( $obj_checkout_field ) ) {
                        $arr_ckf = get_object_vars( $obj_checkout_field );
                        
                        if ( ! empty( $arr_ckf ) && is_array( $arr_ckf ) ) {
                            if ( isset( $arr_ckf['slug'] ) && $arr_ckf['slug'] ) {
                                $fields[$arr_ckf['slug']] = [
                                    'type'          => isset( $arr_ckf['type'] ) ? $arr_ckf['type'] : '',
                                    'label'         => isset( $arr_ckf['label'] ) ? $arr_ckf['label'] : '',
                                    'default'       => isset( $arr_ckf['default'] ) ? $arr_ckf['default'] : '',
                                    'placeholder'   => isset( $arr_ckf['placeholder'] ) ? $arr_ckf['placeholder'] : '',
                                    'class'         => isset( $arr_ckf['class'] ) ? $arr_ckf['class'] : '',
                                    'required'      => isset( $arr_ckf['required'] ) ? $arr_ckf['required'] : '',
                                    'enabled'       => isset( $arr_ckf['enabled'] ) ? $arr_ckf['enabled'] : '',
                                ];

                                if ( isset( $arr_ckf['select_keys'] ) && $arr_ckf['select_keys'] ) {
                                    $fields[$arr_ckf['slug']]['ova_options_key'] = explode( '|', $arr_ckf['select_keys'] );
                                }
                                if ( isset( $arr_ckf['select_texts'] ) && $arr_ckf['select_texts'] ) {
                                    $fields[$arr_ckf['slug']]['ova_options_text'] = explode( '|', $arr_ckf['select_texts'] );
                                }
                                if ( isset( $arr_ckf['select_prices'] ) && $arr_ckf['select_prices'] ) {
                                    $fields[$arr_ckf['slug']]['ova_options_price'] = explode( '|', $arr_ckf['select_prices'] );
                                }
                                if ( isset( $arr_ckf['radio_values'] ) && $arr_ckf['radio_values'] ) {
                                    $fields[$arr_ckf['slug']]['ova_radio_values'] = explode( '|', $arr_ckf['radio_values'] );
                                }
                                if ( isset( $arr_ckf['radio_prices'] ) && $arr_ckf['radio_prices'] ) {
                                    $fields[$arr_ckf['slug']]['ova_radio_prices'] = explode( '|', $arr_ckf['radio_prices'] );
                                }
                                if ( isset( $arr_ckf['checkbox_keys'] ) && $arr_ckf['checkbox_keys'] ) {
                                    $fields[$arr_ckf['slug']]['ova_checkbox_key'] = explode( '|', $arr_ckf['checkbox_keys'] );
                                }
                                if ( isset( $arr_ckf['checkbox_texts'] ) && $arr_ckf['checkbox_texts'] ) {
                                    $fields[$arr_ckf['slug']]['ova_checkbox_text'] = explode( '|', $arr_ckf['checkbox_texts'] );
                                }
                                if ( isset( $arr_ckf['checkbox_prices'] ) && $arr_ckf['checkbox_prices'] ) {
                                    $fields[$arr_ckf['slug']]['ova_checkbox_price'] = explode( '|', $arr_ckf['checkbox_prices'] );
                                }
                                if ( isset( $arr_ckf['max_file_size'] ) && $arr_ckf['max_file_size'] ) {
                                    $fields[$arr_ckf['slug']]['max_file_size'] = $arr_ckf['max_file_size'];
                                }
                            }
                        }
                    }
                }
            }
        }

        if ( ! empty( $fields ) && is_array( $fields ) ) {
            update_option('ovabrw_booking_form', $fields);
        }
    }

    // update option elementor cpt support
    $post_types = array('post','page','event','destination','ova_framework_hf_el');
    update_option( 'elementor_cpt_support', $post_types );
});

/**
 * After import setup home & blog page
 */
add_action( 'ocdi/after_import', function() {
    // Assign menus to their locations.
    $primary = get_term_by( 'name', 'Primary Menu', 'nav_menu' );

    if ( !is_wp_error( $primary ) ) {
        set_theme_mod( 'nav_menu_locations', [
            'primary' => $primary->term_id
        ]);
    }

    // Assign front page and posts page (blog page).
    $front_page_id = tripgo_get_page_by_title( 'Home 1' );
    $blog_page_id  = tripgo_get_page_by_title( 'Blog' );

    update_option( 'show_on_front', 'page' );
    update_option( 'page_on_front', $front_page_id->ID );
    update_option( 'page_for_posts', $blog_page_id->ID );

    // Config Elementor
    update_option( 'elementor_disable_color_schemes', 'yes' );
    update_option( 'elementor_disable_typography_schemes', 'yes' );
    update_option( 'elementor_css_print_method', 'internal' );
    update_option( 'elementor_load_fa4_shim', 'yes' );

    // Update customize
    tripgo_replace_url_in_customize();

    // After import replace URLs
    tripgo_after_import_replace_urls();

    // Replace image URLs
    $upload_dir = wp_get_upload_dir();
    $base_url   = $upload_dir['baseurl'];
    tripgo_after_import_replace_urls( $base_url, 'https://ovatheme.nyc3.cdn.digitaloceanspaces.com/tripgo' );
});

/**
 * Import demo files
 */
add_filter( 'ocdi/import_files', function() {
    return [
        [
            'import_file_name'             => 'Demo Import',
            'categories'                   => array( 'Category 1', 'Category 2' ),
            'local_import_file'            => trailingslashit( get_template_directory() ) . 'install-resource/demo-import/demo-content.xml',
            'local_import_widget_file'     => trailingslashit( get_template_directory() ) . 'install-resource/demo-import/widgets.wie',
            'local_import_customizer_file'   => trailingslashit( get_template_directory() ) . 'install-resource/demo-import/customize.dat'
        ]
    ];
});

/**
 * Get page by title
 */
if ( !function_exists( 'tripgo_get_page_by_title' ) ) {
    function tripgo_get_page_by_title( $page_title, $output = OBJECT, $post_type = 'page' ) {
        global $wpdb;

        if ( is_array( $post_type ) ) {
            $post_type           = esc_sql( $post_type );
            $post_type_in_string = "'" . implode( "','", $post_type ) . "'";
            $sql                 = $wpdb->prepare(
                "
                SELECT ID
                FROM $wpdb->posts
                WHERE post_title = %s
                AND post_type IN ($post_type_in_string)
            ",
                $page_title
            );
        } else {
            $sql = $wpdb->prepare(
                "
                SELECT ID
                FROM $wpdb->posts
                WHERE post_title = %s
                AND post_type = %s
            ",
                $page_title,
                $post_type
            );
        }

        $page = $wpdb->get_var( $sql );

        if ( $page ) {
            return get_post( $page, $output );
        }

        return null;
    }
}

/**
 * Replace url in customize
 */
if ( !function_exists( 'tripgo_replace_url_in_customize' ) ) {
    function tripgo_replace_url_in_customize() {
        $demo_url = apply_filters( 'tripgo_demo_url', 'https://demo.ovatheme.com/tripgo' );

        // Get theme mods
        $theme_mods = get_theme_mods();

        if ( !empty( $theme_mods ) ) {
            foreach ( $theme_mods as $key => $val ) {
                if ( is_string( $val ) && str_contains( $val, $demo_url ) ) {
                    $val = str_replace( $demo_url, get_site_url(), $val );

                    // Update theme mod
                    set_theme_mod( $key, $val );
                }
            }
        }
    }
}

/**
 * After import replace URLs
 */
if ( !function_exists( 'tripgo_after_import_replace_urls' ) ) {
    function tripgo_after_import_replace_urls( $site_url = '', $demo_url = '' ) {
        global $wpdb;

        // Site URL
        if ( !$site_url ) {
            $site_url = apply_filters( 'tripgo_site_url', get_site_url() );
        }

        // Demo URL
        if ( !$demo_url ) {
            $demo_url = apply_filters( 'tripgo_demo_url', 'https://demo.ovatheme.com/tripgo' );
        }

        // Replace in option value
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$wpdb->options} " .
                "SET `option_value` = REPLACE(`option_value`, %s, %s);",
                $demo_url,
                $site_url
            )
        );

        // Replace in posts
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$wpdb->posts} " .
                "SET `post_content` = REPLACE(`post_content`, %s, %s), `guid` = REPLACE(`guid`, %s, %s);",
                $demo_url,
                $site_url,
                $demo_url,
                $site_url
            )
        );

        // Replace in meta value
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$wpdb->postmeta} " .
                "SET `meta_value` = REPLACE(`meta_value`, %s, %s) " .
                "WHERE `meta_key` <> '_elementor_data';",
                $demo_url,
                $site_url
            )
        );

        // Elementor Data
        $escaped_from       = str_replace( '/', '\\/', $demo_url );
        $escaped_to         = str_replace( '/', '\\/', $site_url );
        $meta_value_like    = '[%'; // meta_value LIKE '[%' are json formatted

        $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$wpdb->postmeta} " .
                'SET `meta_value` = REPLACE(`meta_value`, %s, %s) ' .
                "WHERE `meta_key` = '_elementor_data' AND `meta_value` LIKE %s;",
                $escaped_from,
                $escaped_to,
                $meta_value_like
            )
        );
    }
}