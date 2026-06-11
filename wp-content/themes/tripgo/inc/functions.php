<?php if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Get current ID of post/page, etc
 */
if ( !function_exists( 'tripgo_get_current_id' ) ) {
	function tripgo_get_current_id() {
        // Current page
	    $current_page_id = '';
	    
	    if ( class_exists( 'woocommerce' ) ) {
	        if ( is_shop() ) {
	            $current_page_id = get_option( 'woocommerce_shop_page_id' );
	        } elseif ( is_cart() ) {
	            $current_page_id = get_option( 'woocommerce_cart_page_id' );
	        } elseif ( is_checkout() ) {
	            $current_page_id = get_option( 'woocommerce_checkout_page_id' );
	        } elseif ( is_account_page() ) {
	            $current_page_id = get_option( 'woocommerce_myaccount_page_id' );
	        } elseif ( is_view_order_page() ) {
	            $current_page_id = get_option( 'woocommerce_view_order_page_id' );
	        }
	    }

	    if ( '' === $current_page_id ) {
	        if ( is_home () && is_front_page () ) {
	            $current_page_id = '';
	        } elseif ( is_home () ) {
	            $current_page_id = get_option( 'page_for_posts' );
	        } elseif ( is_search () || is_category () || is_tag () || is_tax () || is_archive() ) {
	            $current_page_id = '';
	        } elseif ( !is_404 () ) {
	           $current_page_id = get_the_id();
	        } 
	    }

	    return apply_filters( 'tripgo_get_current_id', $current_page_id );
	}
}

/**
 * is elementor active
 */
if ( !function_exists( 'tripgo_is_elementor_active' ) ) {
    function tripgo_is_elementor_active() {
        return did_action( 'elementor/loaded' );
    }
}

/**
 * is woo active
 */
if ( !function_exists( 'tripgo_is_woo_active' ) ) {
    function tripgo_is_woo_active() {
        return class_exists( 'woocommerce' );    
    }
}

/**
 * is blog archive
 */
if ( !function_exists( 'tripgo_is_blog_archive' ) ) {
    function tripgo_is_blog_archive() {
        return ( is_home() && is_front_page() ) || is_archive() || is_category() || is_tag() || is_home();
    }
}

/**
 * is woo archive
 */
if ( !function_exists( 'tripgo_is_woo_archive' ) ) {
    function tripgo_is_woo_archive() {
        return is_post_type_archive( 'product' ) || is_tax( 'product_cat' ) || is_tax( 'product_tag' );
    }
}

/**
 * Get ID from Slug of Header Footer Builder - Post Type
 */
if ( !function_exists( 'tripgo_get_id_by_slug' ) ) {
    function tripgo_get_id_by_slug( $page_slug ) {
        // Get page ID
        $page = get_page_by_path( $page_slug, OBJECT, 'ova_framework_hf_el' );
        if ( $page ) {
            return apply_filters( 'tripgo_get_id_by_slug', $page->ID, $page_slug );
        } else {
            return null;
        }
    }
}

/**
 * Google Font sanitization
 */
if ( !function_exists( 'tripgo_google_font_sanitization' ) ) {
    function tripgo_google_font_sanitization( $font ) {
        // Get fonts
        $default_fonts = json_decode( $font, true );
        if ( tripgo_array_exists( $default_fonts ) ) {
            foreach ( $default_fonts as $k => $value ) {
                $default_fonts[$k] = sanitize_text_field( $value );
            }
            $font = json_encode( $default_fonts );
        } else {
            $font = json_encode( sanitize_text_field( $default_fonts ) );
        }

        return apply_filters( 'tripgo_google_font_sanitization', $font );
    }
}

/**
 * Default Primary Font in Customize
 */
if ( !function_exists( 'tripgo_default_primary_font' ) ) {
    function tripgo_default_primary_font() {
        return apply_filters( 'tripgo_default_primary_font', json_encode([
            'font'          => 'HK Grotesk',
            'regularweight' => '300,400,500,600,700,800,900',
            'category'      => 'serif'
        ]));
    }
}

/**
 * Woo sidebar
 */
if ( !function_exists( 'tripgo_woo_sidebar' ) ) {
    function tripgo_woo_sidebar() {
        if ( class_exists( 'woocommerce' ) && is_product() ) {
            return apply_filters( 'tripgo_woo_sidebar', get_theme_mod( 'woo_product_layout', 'woo_layout_1c' ) );
        } else {
            return apply_filters( 'tripgo_woo_sidebar', get_theme_mod( 'woo_archive_layout', 'woo_layout_1c' ) );
        }
    }
}

/**
 * Blog show media
 */
if ( !function_exists( 'tripgo_blog_show_media' ) ) {
    function tripgo_blog_show_media() {
        return apply_filters( 'tripgo_blog_show_media', sanitize_text_field( tripgo_get_meta_data( 'show_media', $_GET, get_theme_mod( 'blog_archive_show_media', 'yes' ) ) ) );
    }
}

/**
 * Blog show title
 */
if ( !function_exists( 'tripgo_blog_show_title' ) ) {
    function tripgo_blog_show_title() {
        return apply_filters( 'tripgo_blog_show_title', sanitize_text_field( tripgo_get_meta_data( 'show_title', $_GET, get_theme_mod( 'blog_archive_show_title', 'yes' ) ) ) );
    }
}

/**
 * Blog show date
 */
if ( !function_exists( 'tripgo_blog_show_date' ) ) {
    function tripgo_blog_show_date() {
        return apply_filters( 'tripgo_blog_show_date', sanitize_text_field( ovabrw_get_meta_data( 'show_date', $_GET, get_theme_mod( 'blog_archive_show_date', 'yes' ) ) ) );
    }
}

/**
 * Blog show category
 */
if ( !function_exists( 'tripgo_blog_show_cat' ) ) {
    function tripgo_blog_show_cat() {
        return apply_filters( 'tripgo_blog_show_cat', sanitize_text_field( ovabrw_get_meta_data( 'show_cat', $_GET, get_theme_mod( 'blog_archive_show_cat', 'yes' ) ) ) );
    }
}

/**
 * Blog show author
 */
if ( !function_exists( 'tripgo_blog_show_author' ) ) {
    function tripgo_blog_show_author(){
        return apply_filters( 'tripgo_blog_show_author', sanitize_text_field( ovabrw_get_meta_data( 'show_author', $_GET, get_theme_mod( 'blog_archive_show_author', 'yes' ) ) ) );
    }
}

/**
 * Blog show comment
 */
if ( !function_exists( 'tripgo_blog_show_comment' ) ) {
    function tripgo_blog_show_comment() {
        return apply_filters( 'tripgo_blog_show_comment', sanitize_text_field( ovabrw_get_meta_data( 'show_comment', $_GET, get_theme_mod( 'blog_archive_show_comment', 'yes' ) ) ) );
    }
}

/**
 * Blog show excerpt
 */
if ( !function_exists( 'tripgo_blog_show_excerpt' ) ) {
    function tripgo_blog_show_excerpt() {
        return apply_filters( 'tripgo_blog_show_excerpt', sanitize_text_field( ovabrw_get_meta_data( 'show_excerpt', $_GET, get_theme_mod( 'blog_archive_show_excerpt', 'yes' ) ) ) );
    }
}

/**
 * Show readmore button
 */
if ( !function_exists( 'tripgo_blog_show_readmore' ) ) {
    function tripgo_blog_show_readmore() {
        return apply_filters( 'tripgo_blog_show_readmore', sanitize_text_field( tripgo_get_meta_data( 'show_readmore', $_GET, get_theme_mod( 'blog_archive_show_readmore', 'yes' ) ) ) );
    }
}

/**
 * Post show media
 */
if ( !function_exists( 'tripgo_post_show_media' ) ) {
    function tripgo_post_show_media() {
        return apply_filters( 'tripgo_post_show_media', sanitize_text_field( tripgo_get_meta_data( 'show_media', $_GET, get_theme_mod( 'blog_single_show_media', 'yes' ) ) ) );
    }
}

/**
 * Post show title
 */
if ( !function_exists( 'tripgo_post_show_title' ) ) {
    function tripgo_post_show_title() {
        return apply_filters( 'tripgo_post_show_title', sanitize_text_field( tripgo_get_meta_data( 'show_title', $_GET, get_theme_mod( 'blog_single_show_title', 'yes' ) ) ) );
    }
}

/**
 * Post show date
 */
if ( !function_exists( 'tripgo_post_show_date' ) ) {
    function tripgo_post_show_date() {
        return apply_filters( 'tripgo_post_show_date', sanitize_text_field( tripgo_get_meta_data( 'show_date', $_GET, get_theme_mod( 'blog_single_show_date', 'yes' ) ) ) );
    }
}

/**
 * Post show category
 */
if ( !function_exists( 'tripgo_post_show_cat' ) ) {
    function tripgo_post_show_cat() {
        return apply_filters( 'tripgo_post_show_cat', sanitize_text_field( tripgo_get_meta_data( 'show_cat', $_GET, get_theme_mod( 'blog_single_show_cat', 'yes' ) ) ) );
    }
}

/**
 * Post show author
 */
if ( !function_exists( 'tripgo_post_show_author' ) ) {
    function tripgo_post_show_author() {
        return apply_filters( 'tripgo_post_show_author', sanitize_text_field( tripgo_get_meta_data( 'show_author', $_GET, get_theme_mod( 'blog_single_show_author', 'yes' ) ) ) );
    }
}

/**
 * Post show comment
 */
if ( !function_exists( 'tripgo_post_show_comment' ) ) {
    function tripgo_post_show_comment() {
        return apply_filters( 'tripgo_post_show_comment', sanitize_text_field( tripgo_get_meta_data( 'show_comment', $_GET, get_theme_mod( 'blog_single_show_comment', 'yes' ) ) ) );
    }
}

/**
 * Post show content
 */
if ( !function_exists( 'tripgo_post_show_content' ) ) {
    function tripgo_post_show_content() {
        return apply_filters( 'tripgo_post_show_content', sanitize_text_field( tripgo_get_meta_data( 'show_content', $_GET, get_theme_mod( 'blog_single_show_content', 'yes' ) ) ) );
    }
}

/**
 * Post show tag
 */
if ( !function_exists( 'tripgo_post_show_tag' ) ) {
    function tripgo_post_show_tag() {
        return apply_filters( 'tripgo_post_show_tag', sanitize_text_field( tripgo_get_meta_data( 'show_tag', $_GET, get_theme_mod( 'blog_single_show_tag', 'yes' ) ) ) );
    }
}

/**
 * Post show share social icon
 */
if ( !function_exists( 'tripgo_post_show_share_social_icon' ) ) {
    function tripgo_post_show_share_social_icon() {
        return apply_filters( 'tripgo_post_show_share_social_icon', sanitize_text_field( tripgo_get_meta_data( 'show_share_social_icon', $_GET, get_theme_mod( 'blog_single_show_share_social_icon', 'yes' ) ) ) );
    }
}

/**
 * Post show next & prev button
 */
if ( !function_exists( 'tripgo_post_show_next_prev_post' ) ) {
    function tripgo_post_show_next_prev_post() {
        return apply_filters( 'tripgo_post_show_next_prev_post', sanitize_text_field( tripgo_get_meta_data( 'show_next_prev_post', $_GET, get_theme_mod( 'blog_single_show_next_prev_post', 'yes' ) ) ) );
    }
}

/**
 * Post show leave a reply
 */
if ( !function_exists( 'tripgo_post_show_leave_a_reply' ) ) {
    function tripgo_post_show_leave_a_reply() {
        return apply_filters( 'tripgo_post_show_leave_a_reply', sanitize_text_field( tripgo_get_meta_data( 'show_leave_a_reply', $_GET, get_theme_mod( 'blog_single_show_leave_a_reply', 'yes' ) ) ) );
    }
}

/**
 * Get Gallery ids Product
 */
if ( !function_exists( 'tripgo_get_gallery_ids' ) ) {
    function tripgo_get_gallery_ids( $product_id ) {
        // Get product
        $product = wc_get_product( $product_id );
        if ( $product ) {
            $arr_image_ids = [];

            // Get product image id
            $product_image_id = $product->get_image_id();
            if ( $product_image_id ) {
                array_push( $arr_image_ids, $product_image_id );
            }

            // Get product gallery ids
            $product_gallery_ids = $product->get_gallery_image_ids();
            if ( tripgo_array_exists( $product_gallery_ids ) ) {
                $arr_image_ids = array_merge( $arr_image_ids, $product_gallery_ids );
            }

            return apply_filters( 'tripgo_get_gallery_ids', $arr_image_ids, $product_id );
        }

        return false;
    }
}

/**
 * Get product price
 */
if ( !function_exists( 'tripgo_get_price_product' ) ) {
    function tripgo_get_price_product( $product_id ) {
        // Get product
        $product = wc_get_product( $product_id );
        if ( !$product ) {
            return apply_filters( 'tripgo_get_price_product', [
                'regular_price' => 0,
                'sale_price'    => 0
            ], $product_id );
        }

        // init
        $regular_price = $sale_price = 0;

        if ( $product->is_on_sale() && $product->get_sale_price() ) {
            $regular_price  = $product->get_sale_price();
            $sale_price     = $product->get_regular_price();
        } else {
            $regular_price = $product->get_regular_price();
        }

        return apply_filters( 'tripgo_get_price_product', [
            'regular_price' => $regular_price,
            'sale_price'    => $sale_price
        ], $product_id );
    }
}

/**
 * Get Price - Multi Currency
 */
if ( !function_exists( 'ovabrw_wc_price' ) ) {
    function ovabrw_wc_price( $price = null, $args = [], $convert = true ) {
        $new_price = $price;
        if ( !$price ) $new_price = 0;

        // Get currency
        $current_currency = tripgo_get_meta_data( 'currency', $args );

        // CURCY - Multi Currency for WooCommerce
        // WooCommerce Multilingual & Multicurrency
        if ( is_plugin_active( 'woo-multi-currency/woo-multi-currency.php' ) || is_plugin_active( 'woocommerce-multi-currency/woocommerce-multi-currency.php' ) ) {
            $new_price = wmc_get_price( $price, $current_currency );
        } elseif ( is_plugin_active( 'woocommerce-multilingual/wpml-woocommerce.php' ) ) {
            if ( $convert ) {
                // WPML multi currency
                global $woocommerce_wpml;

                if ( $woocommerce_wpml && is_object( $woocommerce_wpml ) ) {
                    if ( wp_doing_ajax() ) add_filter( 'wcml_load_multi_currency_in_ajax', '__return_true' );

                    $multi_currency     = $woocommerce_wpml->get_multi_currency();
                    $currency_options   = $woocommerce_wpml->get_setting( 'currency_options' );
                    $WMCP               = new WCML_Multi_Currency_Prices( $multi_currency, $currency_options );
                    $new_price          = $WMCP->convert_price_amount( $price, $current_currency );
                }
            }
        } else {
            // nothing
        }
        
        return apply_filters( 'ovabrw_wc_price', wc_price( $new_price, $args ), $price, $args, $convert );
    }
}

/**
 * Convert Price - Multi Currency
 */
if ( !function_exists( 'ovabrw_convert_price' ) ) {
    function ovabrw_convert_price( $price = null, $args = [], $convert = true ) {
        $new_price = $price;
        if ( ! $price ) $new_price = 0;

        // Get currency
        $current_currency = tripgo_get_meta_data( 'currency', $args );

        // CURCY - Multi Currency for WooCommerce
        // WooCommerce Multilingual & Multicurrency
        if ( is_plugin_active( 'woo-multi-currency/woo-multi-currency.php' ) || is_plugin_active( 'woocommerce-multi-currency/woocommerce-multi-currency.php' ) ) {
            $new_price = wmc_get_price( $price, $current_currency );
        } elseif ( is_plugin_active( 'woocommerce-multilingual/wpml-woocommerce.php' ) ) {
            if ( $convert ) {
                // WPML multi currency
                global $woocommerce_wpml;

                if ( $woocommerce_wpml && is_object( $woocommerce_wpml ) ) {
                    if ( wp_doing_ajax() ) add_filter( 'wcml_load_multi_currency_in_ajax', '__return_true' );

                    $multi_currency     = $woocommerce_wpml->get_multi_currency();
                    $currency_options   = $woocommerce_wpml->get_setting( 'currency_options' );
                    $WMCP               = new WCML_Multi_Currency_Prices( $multi_currency, $currency_options );
                    $new_price          = $WMCP->convert_price_amount( $price, $current_currency );
                }
            }
        } else {
            // nothing
        }
        
        return apply_filters( 'ovabrw_convert_price', $new_price, $price, $args, $convert );
    }
}

/**
 * Convert Price in Admin - Multi Currency
 */
if ( !function_exists( 'ovabrw_convert_price_in_admin' ) ) {
    function ovabrw_convert_price_in_admin( $price = null, $currency_code = '' ) {
        $new_price = $price;
        if ( !$price ) $new_price = 0;

        if ( is_admin() && ( is_plugin_active( 'woo-multi-currency/woo-multi-currency.php' ) || is_plugin_active( 'woocommerce-multi-currency/woocommerce-multi-currency.php' ) ) ) {
            $setting = '';
            
            if ( is_plugin_active( 'woo-multi-currency/woo-multi-currency.php' ) ) {
                $setting = WOOMULTI_CURRENCY_F_Data::get_ins();
            }

            if ( is_plugin_active( 'woocommerce-multi-currency/woocommerce-multi-currency.php' ) ) {
                $setting = WOOMULTI_CURRENCY_Data::get_ins();
            }

            if ( ! empty( $setting ) && is_object( $setting ) ) {
                /*Check currency*/
                $selected_currencies = $setting->get_list_currencies();
                $current_currency    = $setting->get_current_currency();

                if ( ! $currency_code || $currency_code === $current_currency ) {
                    return $new_price;
                }

                if ( $new_price ) {
                    if ( $currency_code && isset( $selected_currencies[ $currency_code ] ) ) {
                        $new_price = $price * (float) $selected_currencies[ $currency_code ]['rate'];
                    } else {
                        $new_price = $price * (float) $selected_currencies[ $current_currency ]['rate'];
                    }
                }
            }
        }

        return apply_filters( 'ovabrw_convert_price_in_admin', $new_price, $price, $currency_code );
    }
}

/**
 * Conver number to hours
 */
if ( !function_exists( 'ovabrw_convert_number_to_hours' ) ) {
    function ovabrw_convert_number_to_hours( $number = '' ) {
        if ( !$number ) return false;
        $hours = floor( (float)$number );

        return apply_filters( 'ovabrw_convert_number_to_hours', absint( $hours ), $number );
    }
}

/**
 * Conver number to minutes
 */
if ( !function_exists( 'ovabrw_convert_number_to_minutes' ) ) {
    function ovabrw_convert_number_to_minutes( $number = '' ) {
        if ( ! $number ) return false;

        $hours      = floor( (float)$number );
        $minutes    = round( ( $number - $hours ) * 60 );

        return apply_filters( 'ovabrw_convert_number_to_minutes', absint( $minutes ), $number );
    }
}

/**
 * Check array exists
 */
if ( !function_exists( 'tripgo_array_exists' ) ) {
    function tripgo_array_exists( $arr ) {
        if ( !empty( $arr ) && is_array( $arr ) ) {
            return true;
        }

        return false;
    }
}

/**
 * Get post meta
 */
if ( !function_exists( 'tripgo_get_post_meta' ) ) {
    function tripgo_get_post_meta( $id = null, $name = '', $default = false ) {
        $value = '';

        if ( $id && $name ) {
            $value = get_post_meta( $id, 'ovabrw_'.$name, true );

            if ( empty( $value ) && $default !== false ) {
                $value = $default;
            }
        }

        return apply_filters( 'tripgo_get_post_meta', $value, $id, $name, $default );
    }
}

/**
 * Get meta from data
 */
if ( !function_exists( 'tripgo_get_meta_data' ) ) {
    function tripgo_get_meta_data( $key = '', $args = [], $default = false ) {
        $value = '';

        // Check $args
        if ( empty( $args ) || !is_array( $args ) ) $args = [];

        // Get value by key
        if ( $key !== '' && isset( $args[$key] ) && '' !== $args[$key] ) {
            $value = $args[$key];
        }

        // Set default
        if ( !$value && false !== $default ) {
            $value = $default;
        }

        return apply_filters( 'tripgo_get_meta_data', $value, $key, $args, $default );
    }
}

/**
 * Random unique id
 */
if ( !function_exists( 'tripgo_unique_id' ) ) {
    function tripgo_unique_id( $id = '' ) {
        $unique_id = 'tripgo_'.$id . '_' . time() . '_' . mt_rand();

        return apply_filters( 'tripgo_unique_id', $unique_id, $id );
    }
}

/**
 * Output the text input
 */
if ( !function_exists( 'tripgo_text_input' ) ) {
    function tripgo_text_input( $args = [] ) {
        $args['type']           = tripgo_get_meta_data( 'type', $args, 'text' );
        $args['id']             = tripgo_get_meta_data( 'id', $args );
        $args['class']          = tripgo_get_meta_data( 'class', $args );
        $args['name']           = tripgo_get_meta_data( 'name', $args );
        $args['value']          = tripgo_get_meta_data( 'value', $args );
        $args['default']        = tripgo_get_meta_data( 'default', $args );
        $args['placeholder']    = tripgo_get_meta_data( 'placeholder', $args );
        $args['description']    = tripgo_get_meta_data( 'description', $args );
        $args['required']       = tripgo_get_meta_data( 'required', $args );
        $args['readonly']       = tripgo_get_meta_data( 'readonly', $args );
        $args['checked']        = tripgo_get_meta_data( 'checked', $args );
        $args['disabled']       = tripgo_get_meta_data( 'disabled', $args );
        $args['attrs']          = tripgo_get_meta_data( 'attrs', $args );

        // Set value
        if ( ! $args['value'] && $args['default'] ) {
            $args['value'] = $args['default'];
        }

        // Data type
        $data_type = tripgo_get_meta_data( 'data_type', $args );
        switch ( $data_type ) {
            case 'timepicker':
                // Add class
                $args['class'] .= ' ovabrw-timepicker';

                // Get time format
                $time_format = function_exists( 'ovabrw_get_time_format' ) ? ovabrw_get_time_format() : 'H:i';

                // Set value
                $args['value'] = strtotime( $args['value'] ) ? gmdate( $time_format, strtotime( $args['value'] ) ) : '';

                // Set placeholder
                if ( !$args['placeholder'] ) {
                    $args['placeholder'] = function_exists( 'ovabrw_get_time_format_placeholder' ) ? ovabrw_get_time_format_placeholder() : esc_html__( 'H:i', 'tripgo' );
                }
                break;
            case 'datepicker':
                // Add class
                $args['class'] .= ' ovabrw-datepicker';

                // Get date format
                $date_format = function_exists( 'ovabrw_get_date_format' ) ? ovabrw_get_date_format() : 'd-m-Y';

                // Set value
                $args['value']  = strtotime( $args['value'] ) ? gmdate( $date_format, strtotime( $args['value'] ) ) : '';

                // Set placeholder
                if ( !$args['placeholder'] ) {
                    $args['placeholder'] = function_exists( 'ovabrw_get_placeholder_date' ) ? ovabrw_get_placeholder_date() : esc_html__( 'DD-MM-YYYY', 'tripgo' );
                }
                break;
            case 'datepicker-field':
                // Add class
                $args['class'] .= ' ovabrw-datepicker-field';

                // Get date format
                $date_format = function_exists( 'ovabrw_get_date_format' ) ? ovabrw_get_date_format() : 'd-m-Y';

                // Set value
                $args['value']  = strtotime( $args['value'] ) ? gmdate( $date_format, strtotime( $args['value'] ) ) : '';

                // Set placeholder
                if ( !$args['placeholder'] ) {
                    $args['placeholder'] = function_exists( 'ovabrw_get_placeholder_date' ) ? ovabrw_get_placeholder_date() : esc_html__( 'DD-MM-YYYY', 'tripgo' );
                }
                break;
            case 'datepicker-start':
                // Add class
                $args['class'] .= ' ovabrw-datepicker-start';

                // Get date format
                $date_format = function_exists( 'ovabrw_get_date_format' ) ? ovabrw_get_date_format() : 'd-m-Y';

                // Set value
                $args['value']  = strtotime( $args['value'] ) ? gmdate( $date_format, strtotime( $args['value'] ) ) : '';

                // Set placeholder
                if ( !$args['placeholder'] ) {
                    $args['placeholder'] = function_exists( 'ovabrw_get_placeholder_date' ) ? ovabrw_get_placeholder_date() : esc_html__( 'DD-MM-YYYY', 'tripgo' );
                }
                break;
            case 'datepicker-end':
                // Add class
                $args['class'] .= ' ovabrw-datepicker-end';

                // Get date format
                $date_format = function_exists( 'ovabrw_get_date_format' ) ? ovabrw_get_date_format() : 'd-m-Y';

                // Set value
                $args['value']  = strtotime( $args['value'] ) ? gmdate( $date_format, strtotime( $args['value'] ) ) : '';

                // Set placeholder
                if ( !$args['placeholder'] ) {
                    $args['placeholder'] = function_exists( 'ovabrw_get_placeholder_date' ) ? ovabrw_get_placeholder_date() : esc_html__( 'DD-MM-YYYY', 'tripgo' );
                }
                break;
            case 'datetimepicker':
                // Add class
                $args['class'] .= ' ovabrw-datetimepicker';

                // Get date time format
                $datetime_format = function_exists( 'ovabrw_get_datetime_format' ) ? ovabrw_get_datetime_format() : 'd-m-Y H:i';

                // Set value
                $args['value'] = strtotime( $args['value'] ) ? gmdate( $datetime_format, strtotime( $args['value'] ) ) : '';

                // Set placeholder
                if ( !$args['placeholder'] ) {
                    $args['placeholder'] = function_exists( 'ovabrw_get_datetime_format_placeholder' ) ? ovabrw_get_datetime_format_placeholder() : esc_html__( 'DD-MM-YYYY H:i', 'tripgo' );
                }
                break;
            case 'datetimepicker-start':
                // Add class
                $args['class'] .= ' ovabrw-datetimepicker-start';

                // Get date time format
                $datetime_format = function_exists( 'ovabrw_get_datetime_format' ) ? ovabrw_get_datetime_format() : 'd-m-Y H:i';

                // Set value
                $args['value'] = strtotime( $args['value'] ) ? gmdate( $datetime_format, strtotime( $args['value'] ) ) : '';

                // Set placeholder
                if ( !$args['placeholder'] ) {
                    $args['placeholder'] = function_exists( 'ovabrw_get_datetime_format_placeholder' ) ? ovabrw_get_datetime_format_placeholder() : esc_html__( 'DD-MM-YYYY H:i', 'tripgo' );
                }
                break;
            case 'datetimepicker-end':
                // Add class
                $args['class'] .= ' ovabrw-datetimepicker-end';

                // Get date time format
                $datetime_format = function_exists( 'ovabrw_get_datetime_format' ) ? ovabrw_get_datetime_format() : 'd-m-Y H:i';

                // Set value
                $args['value'] = strtotime( $args['value'] ) ? gmdate( $datetime_format, strtotime( $args['value'] ) ) : '';

                // Set placeholder
                if ( !$args['placeholder'] ) {
                    $args['placeholder'] = function_exists( 'ovabrw_get_datetime_format_placeholder' ) ? ovabrw_get_datetime_format_placeholder() : esc_html__( 'DD-MM-YYYY H:i', 'tripgo' );
                }
                break;
            case 'number':
                // Set value
                $args['value'] = $args['value'] ? (int)$args['value'] : '';

                // Set placeholder
                if ( !$args['placeholder'] ) {
                    $args['placeholder'] = esc_html__( 'number', 'ova-brw' );
                }
            default:
                break;
        }

        // Custom attribute handling
        $attrs = [];

        if ( tripgo_array_exists( $args['attrs'] ) ) {
            foreach ( $args['attrs'] as $attr => $value ) {
                if ( !$value && $value !== 0 ) continue;
                $attrs[] = esc_attr( $attr ) . '="' . esc_attr( $value ) . '"';
            }
        }

        // Required
        if ( $args['required'] ) {
            $args['class'] .= ' ovabrw-input-required';
        }

        // Checked
        if ( $args['checked'] ) {
            $attrs[] = 'checked';
        }

        // Disabled
        if ( $args['disabled'] ) {
            $attrs[] = 'disabled';
        }

        // Read only
        if ( $args['readonly'] ) {
            $attrs[] = 'readonly';
        }

        // Input name
        $name = $args['name'];

        // Item key
        $key = tripgo_get_meta_data( 'key', $args );
        if ( $key ) {
            $name = $args['name'].'['.esc_attr( $key ).']';
        }

        do_action( 'tripgo_before_text_input', $args );

        if ( $args['id'] ) {
            echo '<input type="'.esc_attr( $args['type'] ).'" id="'.esc_attr( $args['id'] ).'" class="'.esc_attr( $args['class'] ).'" name="'.esc_attr( $name ).'" value="'.esc_attr( $args['value'] ).'" placeholder="'.esc_attr( $args['placeholder'] ).'" '.wp_kses_post( implode( ' ', $attrs ) ).' />';
        } else {
            echo '<input type="'.esc_attr( $args['type'] ).'" class="'.esc_attr( $args['class'] ).'" name="'.esc_attr( $name ).'" value="'.esc_attr( $args['value'] ).'" placeholder="'.esc_attr( $args['placeholder'] ).'" '.wp_kses_post( implode( ' ', $attrs ) ).' />';
        }

        // Description
        if ( $args['description'] ) {
            echo '<span class="description">'.wp_kses_post( $args['description'] ).'</span>';
        }

        do_action( 'tripgo_after_text_input', $args );
    }
}

/**
 * Recursive array replace \\
 */
if ( !function_exists( 'tripgo_recursive_replace' ) ) {
    function tripgo_recursive_replace( $find, $replace, $array ) {
        if ( !is_array( $array ) ) {
            return str_replace( $find, $replace, $array );
        }

        foreach ( $array as $key => $value ) {
            $array[$key] = tripgo_recursive_replace( $find, $replace, $value );
        }

        return apply_filters( 'tripgo_recursive_replace', $array, $find, $replace );
    }
}