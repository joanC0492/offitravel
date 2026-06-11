<?php if ( !defined( 'ABSPATH' ) ) exit();

/**
 * Load template
 */
add_filter( 'template_include', function( $template ) {
    // Search
    $search = sanitize_text_field( ovabrw_get_meta_data( 'ovabrw_search', $_REQUEST ) );
    if ( '' != $search ) {
        return ovabrw_get_template( 'search_result.php' );
    }

    // Request booking
    $request_booking = sanitize_text_field( ovabrw_get_meta_data( 'request_booking', $_REQUEST ) );
    if ( '' != $request_booking ) {
        if ( ovabrw_request_booking( $_REQUEST ) ) {
            // Get thank page
            $thank_page = ovabrw_get_option_setting( 'request_booking_form_thank_page', home_url('/') );

            // Multi language
            $object_id = '';
            if ( is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
                $thank_page_id  = url_to_postid( $thank_page );
                $object_id      = apply_filters( 'wpml_object_id', $thank_page_id, 'page', TRUE  );
            } elseif ( is_plugin_active( 'polylang/polylang.php' ) || is_plugin_active( 'polylang-pro/polylang.php' ) ) {
                $thank_page_id  = url_to_postid( $thank_page );
                $object_id      = pll_get_post( $thank_page_id );
            }

            if ( $object_id ) {
                $thank_page = get_permalink( $object_id );
            }

            wp_safe_redirect( apply_filters( OVABRW_PREFIX.'request_booking_thank_page_url', $thank_page ) );
        } else {
            // Get error page
            $error_page = ovabrw_get_option_setting( 'request_booking_form_error_page', home_url('/') );

            // Multi language
            $object_id = '';
            if ( is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
                $error_page_id  = url_to_postid( $error_page );
                $object_id      = apply_filters( 'wpml_object_id', $error_page_id, 'page', TRUE  );
            } elseif ( is_plugin_active( 'polylang/polylang.php' ) || is_plugin_active( 'polylang-pro/polylang.php' ) ) {
                $error_page_id  = url_to_postid( $error_page );
                $object_id      = pll_get_post( $error_page_id );
            }

            if ( $object_id ) {
                $error_page = get_permalink( $object_id );
            }

            wp_safe_redirect( apply_filters( OVABRW_PREFIX.'request_booking_error_page_url', $error_page ) );
        }

        exit();
    } // END if

    // Get product template
    $product_template = ovabrw_get_option_setting( 'template_elementor_template', 'default' );
    if ( is_product() ) {
        // Get product id
        $product_id = get_the_id();

        // Get product
        $product = wc_get_product( $product_id );
        if ( $product && $product->is_type( OVABRW_RENTAL ) ) {
            $template = wc_get_template( 'rental/single-product.php', [
                'id' => $product_id
            ]);
        }
    }

    return $template;
}, 99 );

/**
 * Support Apple and Google Pay Button
 */
add_filter( 'wcpay_payment_request_supported_types', function( $product_types ) {
    if ( ovabrw_array_exists( $product_types ) ) {
        array_push( $product_types , OVABRW_RENTAL );
    }

    return $product_types;
});

/**
 * Support Google Listings & Ads
 */
if ( !function_exists( 'ovabrw_gg_listings_ads_add_product_types' ) ) {
    function ovabrw_gg_listings_ads_add_product_types( $product_types ) {
        if ( ! empty( $product_types ) && is_array( $product_types ) ) {
            array_push( $product_types , OVABRW_RENTAL );
        }
        return $product_types;
    }
}
add_filter( 'woocommerce_gla_supported_product_types', 'ovabrw_gg_listings_ads_add_product_types', 10 );
add_filter( 'woocommerce_gla_attributes_tab_applicable_product_types', 'ovabrw_gg_listings_ads_add_product_types', 10 );

/**
 * Hide order item meta data
 */
add_filter( 'woocommerce_order_item_get_formatted_meta_data', function( $meta_data, $item ) {
    // Get product ID
    $product_id = method_exists( $item, 'get_product_id' ) ? $item->get_product_id() : '';
    if ( !$product_id ) return $meta_data;

    // New meta
    $new_meta = [];

    // Hide fields
    $hide_fields = [
        'pay_total',
        'type_deposit',
        'deposit_value',
        'ovabrw_time_from',
        'ovabrw_resources',
        'ovabrw_resource_guests',
        'ovabrw_services',
        'ovabrw_service_guests'
    ];

    // Show check-out date
    if ( !ovabrw_show_checkout_date( $product_id ) ) {
        array_push( $hide_fields, 'ovabrw_pickoff_date' );
    }

    // Show quantity
    $show_quantity = ovabrw_get_option_setting( 'booking_form_show_quantity', 'no' );
    if ( 'yes' != $show_quantity ) {
        array_push( $hide_fields, 'ovabrw_quantity' );
    }

    // Show children
    if ( !ovabrw_show_children( $product_id ) ) {
        array_push( $hide_fields, 'ovabrw_childrens' );
    }

    // Show baby
    if ( !ovabrw_show_babies( $product_id ) ) {
        array_push( $hide_fields, 'ovabrw_babies' );
    }

    // Loop
    foreach ( $meta_data as $id => $meta ) {
        // We are removing the meta with the key 'something' from the whole array.
        if ( in_array( $meta->key, apply_filters( OVABRW_PREFIX.'hide_fields', $hide_fields ) ) ) { continue; }
        $new_meta[$id] = $meta;
    } // END loop

    return apply_filters( OVABRW_PREFIX.'order_item_get_formatted_meta_data', $new_meta, $meta_data, $item );
}, 20, 2 );

/**
 * Cart item quantity
 */
add_filter( 'woocommerce_cart_item_quantity', function( $quantity, $cart_item_key, $cart_item ) {
    if ( $cart_item['data']->is_type( OVABRW_RENTAL ) ) {
        $quantity = absint( ovabrw_get_meta_data( 'ovabrw_quantity', $cart_item, 1 ) );

        return '<span class="ovabrw_qty">'.$quantity.'</span>';
    } else {
        return $quantity;
    }
}, 10, 3 );

/**
 * Filter quantity for checkout
 */
add_filter( 'woocommerce_checkout_cart_item_quantity', function( $quantity, $cart_item, $cart_item_key ) {
    if ( $cart_item['data']->is_type( OVABRW_RENTAL ) ) {
        $quantity = absint( ovabrw_get_meta_data( 'ovabrw_quantity', $cart_item ) );

        return '<strong class="product-quantity">x '.$quantity.'</strong>';
    } else{
        return $quantity;
    }
}, 10, 3 );

// Filter Quantity for Order detail after checkout
add_filter( 'woocommerce_order_item_quantity_html', function( $quantity, $item ) {
    $product_id = $item->get_product_id();
    $product    = wc_get_product( $product_id );
    if ( $product && $product->is_type( OVABRW_RENTAL ) ) {
        return '<span class="ovabrw_qty"></span>';  
    }

    return $quantity;
}, 10, 2 );

/**
 * Order item display meta key
 */
add_filter( 'woocommerce_order_item_display_meta_key', function( $key, $meta, $item ) {
    // Get date format
    $date_format = ovabrw_get_date_format();

    // Get product ID
    $product_id = method_exists( $item, 'get_product_id' ) ? $item->get_product_id() : '';
    $duration   = get_post_meta( $product_id, 'ovabrw_duration_checkbox', true );
    if ( $duration ) {
        $date_format = ovabrw_get_datetime_format();
    }

    // wc_tax_enabled
    $tax_text = $tax_text_remaining = '';

    if ( wc_tax_enabled() ) {
        // Get item id
        $item_id = $item->get_id();

        // Get order id
        $order_id = $item->get_order_id();

        // Get order
        $order = wc_get_order( $order_id );
        if ( $order ) {
            // Remaining amount
            $remaining_item = (float)$item->get_meta( 'ovabrw_remaining_amount' );

            // Remaining taxes amount
            $remaining_taxes = (float)$order->get_meta( '_ova_remaining_taxes', true );

            // Tax included
            $is_tax_included = $order->get_meta( '_ova_tax_display_cart', true );
            $tax_message     = $is_tax_included ? esc_html__( '(incl. tax)', 'ova-brw' ) : esc_html__( '(excl. tax)', 'ova-brw' );

            if ( $remaining_taxes ) {
                $tax_text = ' <small class="tax_label">' . $tax_message . '</small>';
            }

            if ( $remaining_item && $remaining_taxes ) {
                $tax_text_remaining = ' <small class="tax_label">' . $tax_message . '</small>';
            }
        }
    }

    // Check-in date
    if ( 'ovabrw_pickup_date' === $meta->key ) {
        $meta->value = date_i18n( $date_format, strtotime( $meta->value ) );
        $key = esc_html__(' Check-in date', 'ova-brw'); 
    }

    // Check-out date
    if ( 'ovabrw_pickoff_date' === $meta->key ) {
        $meta->value = date_i18n( $date_format, strtotime( $meta->value ) );
        $key = esc_html__(' Check-out date', 'ova-brw'); 
    }

    // Time from
    if ( 'ovabrw_time_from' === $meta->key ) $key = esc_html__( 'Time From', 'ova-brw' );

    // Number of adults
    if ( 'ovabrw_adults' === $meta->key ) $key = esc_html__( 'Adults', 'ova-brw' );

    // Number of children
    if ( 'ovabrw_childrens' === $meta->key ) $key = esc_html__( 'Children', 'ova-brw' );

    // Number of babies
    if ( 'ovabrw_babies' === $meta->key ) $key = esc_html__( 'Babies', 'ova-brw' );

    // Quantity
    if ( 'ovabrw_quantity' === $meta->key ) $key = esc_html__( 'Quantity', 'ova-brw' );
    
    // Insurance amount
    if ( 'ovabrw_amount_insurance' === $meta->key ) $key = esc_html__( 'Amount Of Insurance', 'ova-brw' );

    // Deposit amount
    if ( 'ovabrw_deposit_amount' === $meta->key ) $key = esc_html__( 'Deposit Amount', 'ova-brw' ) . $tax_text;

    // Remaining amount
    if ( 'ovabrw_remaining_amount' === $meta->key ) $key = esc_html__( 'Remaining Amount', 'ova-brw' ) . $tax_text_remaining;

    // Full amount
    if ( 'ovabrw_deposit_full_amount' === $meta->key ) $key = esc_html__( 'Full Amount', 'ova-brw' ) . $tax_text;
    
    // Custom checkout fields
    $cckf = ovabrw_get_option( 'booking_form', [] );
    if ( ovabrw_array_exists( $cckf ) ) {
        foreach ( $cckf as $key_field => $field ) {
            if ( $key_field === $meta->key ) {
                $key = $field['label'];
            }
        }
    }
    
    return $key;
}, 20, 3 );

/**
 * Order item display meta value
 */
add_filter( 'woocommerce_order_item_display_meta_value', function( $value, $meta, $item ) {
    // Get order
    $order = $item->get_order();

    // Get currency
    $currency = $order ? $order->get_currency() : '';

    // Insurance amount
    if ( 'ovabrw_amount_insurance' === $meta->key ) { 
        $value = wc_price( $meta->value, [ 'currency' => $currency ] );
    }

    // Deposit amount
    if ( 'ovabrw_deposit_amount' === $meta->key ) { 
        $value = wc_price( $meta->value, [ 'currency' => $order->get_currency() ] );
    }

    // Remaining amount
    if ( 'ovabrw_remaining_amount' === $meta->key ) { 
        $value = wc_price( $meta->value, [ 'currency' => $order->get_currency() ] );
    }

    // Full amount
    if ( 'ovabrw_deposit_full_amount' === $meta->key ) { 
        $value = wc_price( $meta->value, [ 'currency' => $order->get_currency() ] );
    }

    return $value;
}, 20, 3 );

/**
 * Add javascript to head
 */
if ( !function_exists( 'ovabrw_hook_javascript' ) ) {
    function ovabrw_hook_javascript() {
        // Defined label for custom checkout field
        $label_option_value = esc_html__( '...', 'ova-brw' );
        $label_option_text  = esc_html__( 'label', 'ova-brw' );
        $label_option_price = esc_html__( 'price', 'ova-brw' );
        $label_option_qty   = esc_html__( 'number', 'ova-brw' );
        $label_add_new_opt  = esc_html__( 'Add new option', 'ova-brw' );
        $label_remove_opt   = esc_html__( 'Remove option', 'ova-brw' );
        $label_are_you_sure = esc_html__( 'Are you sure?', 'ova-brw' );
        ?>
            <script type="text/javascript">
                var label_option_value  = '<?php echo esc_attr( $label_option_value ); ?>';
                var label_option_text   = '<?php echo esc_attr( $label_option_text ); ?>';
                var label_option_price  = '<?php echo esc_attr( $label_option_price ); ?>';
                var label_option_qty    = '<?php echo esc_attr( $label_option_qty ); ?>';
                var label_add_new_opt   = '<?php echo esc_attr( $label_add_new_opt ); ?>';
                var label_remove_opt    = '<?php echo esc_attr( $label_remove_opt ); ?>';
                var label_are_you_sure  = '<?php echo esc_attr( $label_are_you_sure ); ?>';
            </script>
        <?php
    }
}
add_action( 'admin_head', 'ovabrw_hook_javascript');
add_action( 'wp_head', 'ovabrw_hook_javascript');

/**
 * Add order statuses
 */
add_filter( 'wc_order_statuses', function( $order_statuses ) {
    $order_statuses['wc-closed'] = _x( 'Closed', 'Order status', 'ova-brw' );
    return $order_statuses;
});

// Replace product link in Search Result Page
add_filter( 'woocommerce_loop_product_link', function( $product_link ) {
    // New link
    $new_link = $product_link;

    // Search
    if ( ovabrw_get_meta_data( 'ovabrw_search', $_GET ) ) {
        // Pick-up date
        $pickup_date = ovabrw_get_meta_data( 'ovabrw_pickup_date', $_GET );
        if ( $pickup_date ) {
            $new_link = add_query_arg( 'pickup_date', $pickup_date, $new_link );
        }

        // Drop-off date
        $dropoff_date = ovabrw_get_meta_data( 'ovabrw_pickoff_date', $_GET );
        if ( $dropoff_date ) {
            $new_link = add_query_arg( 'dropoff_date', $dropoff_date, $new_link );
        }
    }

    return apply_filters( OVABRW_PREFIX.'loop_product_link', $new_link, $product_link );
}, 10 );

/**
 * Loop add to cart link
 */
add_filter( 'woocommerce_loop_add_to_cart_link', function( $link, $product, $args ) {
    // Get product link
    $product_link = $product->add_to_cart_url();

    if ( ovabrw_get_meta_data( 'ovabrw_search', $_GET ) ) {
        // Pick-up date
        $pickup_date = ovabrw_get_meta_data( 'ovabrw_pickup_date', $_GET );
        if ( $pickup_date ) {
            $product_link = add_query_arg( 'pickup_date', $pickup_date, $product_link );
        }

        // Drop-off date
        $dropoff_date = ovabrw_get_meta_data( 'ovabrw_pickoff_date', $_GET );
        if ( $dropoff_date ) {
            $product_link = add_query_arg( 'dropoff_date', $dropoff_date, $product_link );
        }
    }

    return sprintf(
        '<a href="%s" data-quantity="%s" class="%s" %s>%s</a>',
        esc_url( $product_link ),
        esc_attr( isset( $args['quantity'] ) ? $args['quantity'] : 1 ),
        esc_attr( isset( $args['class'] ) ? $args['class'] : 'button' ),
        isset( $args['attributes'] ) ? wc_implode_html_attributes( $args['attributes'] ) : '',
        esc_html( $product->add_to_cart_text() )
    );
}, 10, 3 );

/**
 * Allow users cancel Order
 */
add_filter( 'woocommerce_valid_order_statuses_for_cancel', function( $array_status, $order ) {
    // init
    $order_status_can_cancel = $time_can_cancel = $other_condition = $total_order_valid = true;
    
    if ( in_array( $order->get_status(), [ 'pending', 'failed' ] ) ) {
        return [ 'pending', 'failed' ];
    }

    // Check order status can order
    if ( !in_array( $order->get_status(), apply_filters( 'ovabrw_order_status_can_cancel', [ 'completed', 'processing', 'on-hold', 'pending', 'failed' ] ) ) ) {
        $order_status_can_cancel = false;
    }
    
    // Validate before x hours can cancel
    // Get Meta Data type line_item of Order
    $order_line_items = $order->get_items( 'line_item' );
    foreach ( $order_line_items as $item_id => $item ) {
        $product_id = $item->get_product_id();
        $product    = wc_get_product( $product_id );

        $cancel_valid_minutes   = ovabrw_get_option_setting( 'cancel_before_x_hours', 0 );
        $cancel_valid_total     = ovabrw_get_option_setting( 'cancel_condition_total_order', 1 );

        // Check if product type is rental
        if ( $product && $product->is_type( OVABRW_RENTAL ) ) {
            // Get value of pickup date, pickoff date
            $ovabrw_pickup_date = strtotime( $item->get_meta( 'ovabrw_pickup_date' ) );

            if ( !( $ovabrw_pickup_date > current_time( 'timestamp' ) && $ovabrw_pickup_date - current_time( 'timestamp' ) > $cancel_valid_minutes*60*60  ) ) {
                $time_can_cancel = false;
                break;
            }
        }
    }

    // Cancel by total order
    if ( empty( $cancel_valid_total ) ) {
        $total_order_valid = true;
    } elseif ( $order->get_total() > floatval( $cancel_valid_total ) ) {
        $total_order_valid = false;
    }

    // Other condition
    $other_condition = apply_filters( 'ovabrw_other_condition_to_cancel_order', true, $order );

    if ( $order_status_can_cancel && $time_can_cancel && $total_order_valid && $other_condition ) {
        return [ 'completed', 'processing', 'on-hold', 'pending', 'failed' ];
    } else {
        return [];
    }
}, 10, 2 );

/**
 * Display Item Meta in Order Detail
 */
add_filter( 'woocommerce_display_item_meta', function( $html, $item, $args ) {
    $strings = [];
    $html    = '';
    $args    = wp_parse_args( $args, [
        'before'       => '<ul class="wc-item-meta"><li>',
        'after'        => '</li></ul>',
        'separator'    => '</li><li>',
        'echo'         => true,
        'autop'        => false,
        'label_before' => '<strong class="wc-item-meta-label">',
        'label_after'  => ':</strong> ',
    ]);

    foreach ( $item->get_formatted_meta_data() as $meta_id => $meta ) {
        if ( in_array( $meta->key , apply_filters( OVABRW_PREFIX.'order_detail_hide_fields', [] ) ) ) {
            $strings[] = '';
        } else {
            $value     = $args['autop'] ? wp_kses_post( $meta->display_value ) : wp_kses_post( make_clickable( trim( $meta->display_value ) ) );
            $strings[] = $args['label_before'] . wp_kses_post( $meta->display_key ) . $args['label_after'] . $value;    
        }
    }

    if ( $strings ) {
        $html = $args['before'] . implode( $args['separator'], $strings ). $args['after'];
    }

    $html = str_replace( 'ovabrw_pickup_date', esc_html__(' Check-in ', 'ova-brw') , $html );
    $html = str_replace( 'ovabrw_pickoff_date', esc_html__(' Check-out ', 'ova-brw') , $html );
    $html = str_replace( 'ovabrw_price_detail', esc_html__(' Price Detail ', 'ova-brw') , $html );
    $html = str_replace( 'ovabrw_original_order_id', esc_html__(' Original Order ', 'ova-brw') , $html );
    $html = str_replace( 'ovabrw_remaining_balance_order_id', esc_html__(' Remaining Balance Order ', 'ova-brw') , $html );

    return $html;
}, 10, 3 );

/**
 * WPCF7 replace [product-link] tag
 */
add_filter( 'wpcf7_mail_components', function( $components, $contact_form ) {
    // Access the current mail body content
    $mail_body = ovabrw_get_meta_data( 'body', $components );

    // Get data
    $submission     = WPCF7_Submission::get_instance();
    $posted_data    = $submission ? $submission->get_posted_data() : [];

    // Get product id
    $product_id = ovabrw_get_meta_data( 'product_id', $posted_data );
    if ( $product_id && $mail_body ) {
        $mail_body = str_replace( '[product-link]', '<a href="'.esc_url( get_the_permalink( $product_id ) ).'" target="_blank">'.get_the_title( $product_id ).'</a>', $mail_body );

        // Update the mail body component
        $components['body'] = $mail_body;
    }

    return $components;
}, 10, 2 );