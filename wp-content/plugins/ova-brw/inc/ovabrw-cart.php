<?php if ( !defined( 'ABSPATH' ) ) exit();

/**
 * 1: Validate Booking Form And Rent Time
 */
add_filter( 'woocommerce_add_to_cart_validation', function( $passed, $product_id, $quantity ) {
    // Get product
    $product = wc_get_product( $product_id );
    if ( !$product || !$product->is_type( OVABRW_RENTAL ) ) return $passed;

    // Date format
    $date_format = ovabrw_get_date_format();

    // Time format
    $time_format = ovabrw_get_time_format();

    // Check-in date
    $checkin_date = '';

    if ( $product->has_time_slots() ) {
        $date_format    = ovabrw_get_datetime_format();
        $time_from      = ovabrw_get_meta_data( 'ovabrw_time_from', $_POST );

        // Error empty time from
        if ( empty( $time_from ) ) {
            wc_clear_notices();
            wc_add_notice( esc_html__( 'No hay horario disponible, por favor elige otra fecha.', 'ova-brw' ), 'error');
            return false;
        }

        $checkin_date = strtotime( ovabrw_get_meta_data( 'ovabrw_pickup_date', $_POST ) );

        if ( $time_from ) {
            $checkin_date = strtotime( ovabrw_get_meta_data( 'ovabrw_pickup_date', $_POST ) . ' ' . $time_from );
        }
    } else {
        $checkin_date = strtotime( ovabrw_get_meta_data( 'ovabrw_pickup_date', $_POST ) );
    }

    // Check-out date
    $checkout_date = strtotime( ovabrw_get_meta_data( 'ovabrw_pickoff_date', $_POST ) );

    // Check-out
    if ( !$checkout_date ) {
        $checkout_date = ovabrw_get_checkout_date( $product_id, $checkin_date );
        $checkout_date = strtotime( $checkout_date );
    }

    // Number of adults
    $numberof_adults = (int)ovabrw_get_meta_data( 'ovabrw_adults', $_POST );

    // Number of children
    $numberof_children = (int)ovabrw_get_meta_data( 'ovabrw_childrens', $_POST );

    // Number of babies
    $numberof_babies = (int)ovabrw_get_meta_data( 'ovabrw_babies', $_POST );
    
    // Quantity
    $quantity = (int)ovabrw_get_meta_data( 'ovabrw_quantity', $_POST, 1 );

    // Set Pick-up, Drop-off Date again
    $new_dates = ovabrw_new_input_date( $product_id, $checkin_date, $checkout_date, $date_format );

    // New check-in date
    $new_checkin_date = ovabrw_get_meta_data( 'pickup_date_new', $new_dates );

    // New check-out date
    $new_checkout_date = ovabrw_get_meta_data( 'pickoff_date_new', $new_dates );

    // Error empty Pick Up Date
    if ( !$new_checkin_date ) {
        wc_clear_notices();
        echo wc_add_notice( esc_html__( 'Ingresa la fecha de recogida.', 'ova-brw' ), 'error' );
        return false;
    }

    // Error Pick Up Date < Current Time
    if ( $new_checkin_date < current_time( 'timestamp' ) ) {
        wc_clear_notices();
        wc_add_notice( esc_html__( 'La fecha de recogida debe ser posterior a la hora actual.', 'ova-brw' ), 'error' );
        return false;
    }

    // Preparation Time
    $preparation_time = $product->get_meta_value( 'preparation_time' );
    if ( $preparation_time && apply_filters( OVABRW_PREFIX.'preparation_time_validation', true ) ) {
        $today = strtotime( date( 'Y-m-d', current_time( 'timestamp' ) ) );

        if ( $new_checkin_date < ( $today + $preparation_time*86400 - 86400 ) ) {
            wc_clear_notices();
            wc_add_notice( sprintf( esc_html__( 'Reserva con %s día(s) de anticipación respecto a la hora actual.', 'ova-brw' ), $preparation_time ), 'error' );
            return false;
        }
    } // END

    // Booking before X hours today
    $booking_before_x_hours_today = ovabrw_get_option_setting( 'booking_before_x_hours_today' );

    // Get product book before X hours
    $product_book_before_x_hours = $product->get_meta_value( 'book_before_x_hours' );
    if ( $product_book_before_x_hours ) {
        $booking_before_x_hours_today = $product_book_before_x_hours;
    }
    if ( $booking_before_x_hours_today && apply_filters( OVABRW_PREFIX.'book_before_x_hours', true ) ) {
        $date_format = $date_format . ' ' . $time_format;
        $hours_check = $booking_before_x_hours_today;

        // Check hour
        $args_hours_today = explode( ":", $hours_check );

        if ( isset( $args_hours_today[0] ) && $args_hours_today[0] == '00' ) {
            $hours_check = str_replace( array( ' am', ' pm', ' AM', ' PM' ), '', $hours_check );
            $date_format = str_replace( array( ' a', ' A' ), '', $date_format );
        }

        $strtt_today    = strtotime( gmdate( 'Y-m-d', current_time( 'timestamp' ) ) );
        $strtt_pickup   = strtotime( gmdate( 'Y-m-d', $new_checkin_date ) );

        if ( $preparation_time ) {
            $strtt_today += $preparation_time*86400;
        }

        if ( $strtt_today === $strtt_pickup ) {
            $datetime_check = strtotime( gmdate( 'Y-m-d', current_time( 'timestamp' ) ) . ' ' . $hours_check );

            if ( current_time( 'timestamp' ) > $datetime_check ) {
                wc_clear_notices();
                wc_add_notice( sprintf( esc_html__( 'Reserva hoy con al menos %s horas de anticipación.', 'ova-brw' ), $hours_check ), 'error' );
                return false;
            }
        }
    }

    // Error Pick Up Date > Pick Off Date
    if ( $new_checkin_date > $new_checkout_date ) {
        wc_clear_notices();
        wc_add_notice( esc_html__( 'La fecha de devolución debe ser posterior a la fecha de recogida.', 'ova-brw' ), 'error' );
        return false;
    }

    // Guests validation
    $guests_mesg = ovabrw_guests_validation( $product_id, [
        'numberof_adults'   => $numberof_adults,
        'numberof_children' => $numberof_children,
        'numberof_babies'   => $numberof_babies
    ]);

    if ( $guests_mesg ) {
        wc_clear_notices();
        wc_add_notice( $guests_mesg, 'error' );
        return false;
    }

    // Error Quantity
    if ( $quantity < 1 ) {
        wc_clear_notices();
        wc_add_notice( esc_html__( 'Por favor, elige una cantidad mayor que 0.', 'ova-brw'), 'error' );   
        return false;
    }

    // Check service
    $services = ovabrw_get_meta_data( 'ovabrw_service', $_POST );
    if ( ovabrw_array_exists( $services ) ) {
        // Get product services
        $service_required = get_post_meta( $product_id, 'ovabrw_service_required', true );
        if ( ovabrw_array_exists( $service_required ) ) {
            foreach ( $service_required as $key => $value ) {
                if ( 'yes' === $value ) {
                    if ( !ovabrw_get_meta_data( $key, $services ) ) {
                        wc_clear_notices();
                        wc_add_notice( esc_html__( 'Por favor, elige un servicio.', 'ova-brw' ), 'error' );   
                        return false;
                    }
                }
            }
        }
    }

    // Custom Checkout Fields
    $cckf = ovabrw_get_list_field_checkout( $product_id );
    if ( ovabrw_array_exists( $cckf ) ) {
        foreach ( $cckf as $key => $field ) {
            if ( 'on' === ovabrw_get_meta_data( 'enabled', $field ) ) {
                // Get type
                $type = ovabrw_get_meta_data( 'type', $field );

                // Required
                $required = ovabrw_get_meta_data( 'required', $field );

                if ( 'file' === $type ) {
                    // Get files
                    $files = ovabrw_get_meta_data( $key, $_FILES );

                    // Get file name
                    $file_name = ovabrw_get_meta_data( 'name', $files );

                    if ( 'on' === $required && !$file_name  ) {
                        wc_clear_notices();
                        wc_add_notice( sprintf( esc_html__( 'El campo %s es obligatorio.', 'ova-brw'), $field['label'] ), 'error' );
                        return false;
                    }

                    if ( $file_name ) {
                        if ( ovabrw_get_meta_data( 'size', $files ) ) {
                            $mb = absint( $files['size'] ) / 1048576;

                            if ( $mb > $field['max_file_size'] ) {
                                wc_clear_notices();
                                wc_add_notice( sprintf( esc_html__( 'El tamaño máximo permitido para %s es %s MB.', 'ova-brw'), $field['label'], $field['max_file_size'] ), 'error' );
                                return false;
                            }
                        }

                        $overrides = [
                            'test_form' => false,
                            'mimes'     => apply_filters( OVABRW_PREFIX.'file_mimes', [
                                'jpg'   => 'image/jpeg',
                                'jpeg'  => 'image/pjpeg',
                                'png'   => 'image/png',
                                'pdf'   => 'application/pdf',
                                'doc'   => 'application/msword',
                            ]),
                        ];

                        require_once( ABSPATH . 'wp-admin/includes/admin.php' );

                        // Upload file
                        $upload = wp_handle_upload( $files, $overrides );

                        if ( ovabrw_get_meta_data( 'error', $upload ) ) {
                            wc_clear_notices();
                            wc_add_notice( $upload['error'] , 'error' );
                            return false;
                        }
                        
                        $object = array(
                            'name' => basename( $upload['file'] ),
                            'url'  => $upload['url'],
                            'mime' => $upload['type'],
                        );

                        $prefix = 'ovabrw_'.$key;

                        $_POST[$prefix] = $object;
                    }
                } elseif ( 'checkbox' === $type ) {
                    $value = ovabrw_get_meta_data( $key, $_POST );

                    if ( !ovabrw_array_exists( $value ) && 'on' === $required ) {
                        wc_clear_notices();
                        wc_add_notice( sprintf( esc_html__( 'El campo %s es obligatorio.', 'ova-brw' ), $field['label'] ), 'error' );
                        return false;
                    }
                } else {
                    $value = sanitize_text_field( ovabrw_get_meta_data( $key, $_POST ) );
                    if ( !$value && 'on' === $required ) {
                        wc_clear_notices();
                        wc_add_notice( sprintf( esc_html__( 'El campo %s es obligatorio.', 'ova-brw' ), $field['label'] ), 'error' );
                        return false;
                    }
                }
            }
        }
    }

    // Check guests available
    if ( ovabrw_qty_by_guests( $product_id ) ) {
        $guests = [
            'adults'     => $numberof_adults * $quantity,
            'children'   => $numberof_children * $quantity,
            'babies'     => $numberof_babies * $quantity
        ];

        // Get available guests
        $guests_available = ovabrw_validate_guests_available( $product_id, $new_checkin_date, $new_checkout_date, $guests, 'cart' );
        if ( ovabrw_array_exists( $guests_available ) ) {
            return true;
        }
    } else {
        // Get available quantities
        $quantity_available = ova_validate_manage_store( $product_id, $new_checkin_date, $new_checkout_date, $passed, $validate = 'cart', $quantity );
        if ( !empty( $quantity_available ) ) {
            return $quantity_available['status'];
        }
    }

    return false;
}, 10, 3 );

/**
 * 2: Add cart item data
 */
add_filter( 'woocommerce_add_cart_item_data', function( $cart_item_data, $product_id, $variation_id, $quantity ) {
    // Get product
    $product = wc_get_product( $product_id );
    if ( !$product || !$product->is_type( OVABRW_RENTAL ) ) return $cart_item_data;

    // Get date format
    $date_format = ovabrw_get_date_format();

    // Check-in date
    $checkin_date = sanitize_text_field( ovabrw_get_meta_data( 'ovabrw_pickup_date', $_POST ) );

    // Time slots
    if ( $product->has_time_slots() ) {
        $time_from = sanitize_text_field( ovabrw_get_meta_data( 'ovabrw_time_from', $_POST ) );
        $cart_item_data['ovabrw_time_from'] = $time_from;

        // Check-in
        $checkin_date .= ' ' . $time_from;
    }

    // Add pick-up date
    $cart_item_data['ovabrw_pickup_date'] = $checkin_date;

    // Check-out date
    $checkout_date = sanitize_text_field( ovabrw_get_meta_data( 'ovabrw_pickoff_date', $_POST ) );
    if ( !$checkout_date ) {
        $checkout_date = ovabrw_get_checkout_date( $product_id, strtotime( $checkin_date ) );
    }

    // Add drop-off date
    $cart_item_data['ovabrw_pickoff_date'] = $checkout_date;

    // Check dates
    if ( !$checkin_date && !$checkout_date ) {
        return $cart_item_data;
    }

    // Number of Adults
    $cart_item_data['ovabrw_adults'] = (int)ovabrw_get_meta_data( 'ovabrw_adults', $_POST );

    // Number of Children
    $cart_item_data['ovabrw_childrens'] = (int)ovabrw_get_meta_data( 'ovabrw_childrens', $_POST );

    // Number of Babies
    $cart_item_data['ovabrw_babies'] = (int)ovabrw_get_meta_data( 'ovabrw_babies', $_POST );

    // Guest information
    $guest_info = ovabrw_get_meta_data( 'ovabrw_guest_info', $_POST );
    if ( ovabrw_array_exists( $guest_info ) ) {
        $cart_item_data['ovabrw_guest_info'] = $guest_info;
    } // END
    
    // Quantity
    $cart_item_data['ovabrw_quantity'] = (int)ovabrw_get_meta_data( 'ovabrw_quantity', $_POST, 1 );

    // init cckf
    $args_cckf = $cckf_qty = [];

    // Get custom checkout fields
    $cckf = ovabrw_get_list_field_checkout( $product_id );
    if ( ovabrw_array_exists( $cckf ) ) {
        foreach ( $cckf as $key => $field ) {
            if ( 'on' === ovabrw_get_meta_data( 'enabled', $field ) ) {
                // Type
                $type = ovabrw_get_meta_data( 'type', $field );

                if ( 'file' === $type ) {
                    $prefix = 'ovabrw_'.$key;

                    if ( isset( $_POST[$prefix] ) && is_array( $_POST[$prefix] ) ) {
                        $cart_item_data[$key] = '<a href="'.esc_url( $_POST[$prefix]['url'] ).'" title="'.esc_attr( $_POST[$prefix]['name'] ).'" target="_blank">'.esc_attr( $_POST[$prefix]['name'] ).'</a>';
                    } else {
                        $cart_item_data[$key] = '';
                    }
                } elseif ( 'select' === $type ) {
                    // Option key
                    $opt_keys = ovabrw_get_meta_data( 'ova_options_key', $field, [] );

                    // Option text
                    $opt_texts = ovabrw_get_meta_data( 'ova_options_text', $field, [] );

                    // Option value
                    $opt_value = sanitize_text_field( ovabrw_get_meta_data( $key, $_POST ) );

                    // Option qtys
                    $opt_qtys = ovabrw_get_meta_data( $key.'_qty', $_POST, [] );

                    // Add cckf
                    $args_cckf[$key] = $opt_value;

                    // Get quantity
                    $opt_qty = (int)ovabrw_get_meta_data( $opt_value, $opt_qtys );
                    if ( $opt_qty ) $cckf_qty[$key] = $opt_qty;
                    
                    // Search
                    $index = array_search( $opt_value, $opt_keys );
                    if ( !is_bool( $index ) ) {
                        $opt_value = ovabrw_get_meta_data( $index, $opt_texts );
                        if ( $opt_qty > 1 ) {
                            $opt_value = sprintf( esc_html__( '%s (x%d)', 'ova-brw' ), $opt_value, $opt_qty );
                        }
                    }

                    // Add cart item data
                    $cart_item_data[$key] = $opt_value;
                } elseif ( 'checkbox' === $type ) {
                    // Option values
                    $opt_values = [];

                    // Option keys
                    $opt_keys = ovabrw_get_meta_data( 'ova_checkbox_key', $field );

                    // Option texts
                    $opt_texts = ovabrw_get_meta_data( 'ova_checkbox_text', $field );

                    // Get value
                    $values = ovabrw_get_meta_data( $key, $_POST );
                    if ( ovabrw_array_exists( $values ) ) {
                        // Add cckf
                        $args_cckf[$key] = $values;

                        // Add cckf quantity
                        $opt_qtys = ovabrw_get_meta_data( $key.'_qty', $_POST, [] );
                        if ( ovabrw_array_exists( $opt_qtys ) ) $cckf_qty[$key] = $opt_qtys;

                        // Loop
                        foreach ( $values as $value ) {
                            // Search index
                            $index = array_search( $value, $opt_keys );
                            if ( !is_bool( $index ) ) {
                                // Get option text
                                $opt_text = ovabrw_get_meta_data( $index, $opt_texts );

                                // Get option quantity
                                $opt_qty = (int)ovabrw_get_meta_data( $value, $opt_qtys );
                                if ( $opt_qty > 1 ) {
                                    $opt_text = sprintf( esc_html__( '%s (x%d)', 'ova-brw' ), $opt_text, $opt_qty );
                                }

                                // Option values
                                if ( $opt_text ) array_push( $opt_values, $opt_text );
                            }
                        } // END loop
                    }

                    // Add cart item data
                    if ( ovabrw_array_exists( $opt_values ) ) {
                        $cart_item_data[$key] = implode( ', ', $opt_values );
                    }
                } else {
                    // Radio
                    if ( 'radio' === $type ) {
                        // Add cckf
                        $args_cckf[$key] = sanitize_text_field( ovabrw_get_meta_data( $key, $_POST ) );

                        // Get option value
                        $opt_value = sanitize_text_field( ovabrw_get_meta_data( $key, $_POST ) );

                        // Option quantity
                        $opt_qtys = ovabrw_get_meta_data( $key.'_qty', $_POST, [] );

                        // Get quantity
                        $opt_qty = (int)ovabrw_get_meta_data( $opt_value, $opt_qtys );
                        if ( $opt_qty ) $cckf_qty[$key] = $opt_qty;

                        if ( $opt_qty > 1 ) {
                            $opt_value = sprintf( esc_html__( '%s (x%d)', 'ova-brw' ), $opt_value, $opt_qty );
                        }

                        $cart_item_data[$key] = $opt_value;
                    } else {
                        $cart_item_data[$key] = sanitize_text_field( ovabrw_get_meta_data( $key, $_POST ) );
                    }
                }
            }
        }
    }

    // Custom Checkout Fields
    if ( ovabrw_array_exists( $args_cckf ) ) {
        $cart_item_data['custom_ckf'] = $args_cckf;

        // Add cckf qty
        if ( ovabrw_array_exists( $cckf_qty ) ) {
            $cart_item_data['cckf_qty'] = $cckf_qty;
        }
    }

    // Resources
    $resources = ovabrw_get_meta_data( 'ovabrw_rs_checkboxs', $_POST, [] );
    if ( ovabrw_array_exists( $resources ) ) {
        $cart_item_data['ovabrw_resources'] = ovabrw_recursive_replace( '\\', '', $_POST['ovabrw_rs_checkboxs'] );

        // Get resource guests
        $resource_guests = ovabrw_get_meta_data( 'ovabrw_resource_guests', $_POST );
        if ( ovabrw_array_exists( $resource_guests ) ) {
            $cart_item_data['ovabrw_resource_guests'] = $resource_guests;
        }
    }

    // Services
    $services = ovabrw_get_meta_data( 'ovabrw_service', $_POST );
    if ( ovabrw_array_exists( $services ) ) {
        $cart_item_data['ovabrw_services'] = ovabrw_recursive_replace( '\\', '', $_POST['ovabrw_service'] );

        // Get service guests
        $service_guests = ovabrw_get_meta_data( 'ovabrw_service_guests', $_POST );
        if ( ovabrw_array_exists( $service_guests ) ) {
            $cart_item_data['ovabrw_service_guests'] = $service_guests;
        }
    }

    // Deposit
    $type_deposit = sanitize_text_field( ovabrw_get_meta_data( 'ova_type_deposit', $_POST ) );
    if ( 'yes' === $product->get_meta_value( 'enable_deposit' ) && 'deposit' === $type_deposit ) {
        $cart_item_data['is_deposit'] = true;
    }

    return apply_filters( OVABRW_PREFIX.'add_cart_item_data', $cart_item_data, $product_id, $quantity );
}, 10, 4 );

/**
 * 3: Get item data
 */
add_filter( 'woocommerce_get_item_data', function( $item_data, $cart_item ) {
    // Check product type: rental
    if ( !$cart_item['data']->is_type( OVABRW_RENTAL ) ) return $item_data;

    // Get product id
    $product_id = $cart_item['data']->get_id();

    // Check-in date
    $checkin_date = ovabrw_get_meta_data( 'ovabrw_pickup_date', $cart_item );
    if ( !strtotime( $checkin_date ) ) {
        wc_clear_notices();
        wc_add_notice( esc_html__( 'La fecha de entrada es obligatoria.', 'ova-brw' ), 'notice' );
        return false;
    }

    // Check-out date
    $checkout_date = ovabrw_get_meta_data( 'ovabrw_pickoff_date', $cart_item );
    if ( !strtotime( $checkout_date ) ) {
        wc_clear_notices();
        wc_add_notice( esc_html__( 'La fecha de salida es obligatoria.', 'ova-brw' ), 'notice' );
        return false;
    }

    // Get date format
    $date_format = ovabrw_get_date_format();

    // Get time from
    $time_from = ovabrw_get_meta_data( 'ovabrw_time_from', $cart_item );
    if ( $time_from ) {
        // Date format
        $date_format = ovabrw_get_datetime_format();

        // Add item data
        $item_data[] = [
            'key'     => esc_html__( 'Hora desde', 'ova-brw' ),
            'value'   => wc_clean( $time_from ),
            'display' => '',
            'hidden'  => true
        ];
    }

    // Check-in date
    $item_data[] = [
        'key'     => esc_html__( 'Entrada', 'ova-brw' ),
        'value'   => wc_clean( date_i18n( $date_format, strtotime( $checkin_date ) ) ),
        'display' => ''
    ];

    // Check-out date
    if ( ovabrw_show_checkout_date( $product_id ) ) {
        $item_data[] = [
            'key'     => esc_html__( 'Salida', 'ova-brw' ),
            'value'   => wc_clean( date_i18n( $date_format, strtotime( $checkout_date ) ) ),
            'display' => ''
        ];
    } else {
        $item_data[] = [
            'key'     => esc_html__( 'Salida', 'ova-brw' ),
            'value'   => wc_clean( date_i18n( $date_format, strtotime( $checkout_date ) ) ),
            'display' => '',
            'hidden'  => true
        ];
    }

    // Guest information
    $guest_info = ovabrw_get_meta_data( 'ovabrw_guest_info', $cart_item );
    if ( apply_filters( OVABRW_PREFIX.'view_guest_info_in_cart', true ) && ovabrw_array_exists( $guest_info ) ) {
        $guest_info = ovabrw_get_guest_info_html( $product_id, $guest_info );
    } // END

    // Get number of Adults
    $numberof_adults = (int)ovabrw_get_meta_data( 'ovabrw_adults', $cart_item );
    if ( $numberof_adults ) {
        // Adult info
        if ( ovabrw_get_meta_data( 'adult', $guest_info ) && ( ovabrw_is_cart_shortcode() || ovabrw_is_checkout_shortcode() ) ) {
            $numberof_adults .= $guest_info['adult'];
        }

        $item_data[] = [
            'key'     => esc_html__( 'Adultos', 'ova-brw' ),
            'value'   => wp_kses_post( $numberof_adults ),
            'display' => ''
        ];
    }

    // Get number of children
    $numberof_children = (int)ovabrw_get_meta_data( 'ovabrw_childrens', $cart_item );
    if ( $numberof_children ) {
        // Child info
        if ( ovabrw_get_meta_data( 'child', $guest_info ) && ( ovabrw_is_cart_shortcode() || ovabrw_is_checkout_shortcode() ) ) {
            $numberof_children .= $guest_info['child'];
        }

        $item_data[] = [
            'key'     => esc_html__( 'Niños', 'ova-brw' ),
            'value'   => wp_kses_post( $numberof_children ),
            'display' => ''
        ];
    }

    // Get number of babies
    $numberof_babies = (int)ovabrw_get_meta_data( 'ovabrw_babies', $cart_item );
    if ( $numberof_babies ) {
        // Baby info
        if ( ovabrw_get_meta_data( 'baby', $guest_info ) && ( ovabrw_is_cart_shortcode() || ovabrw_is_checkout_shortcode() ) ) {
            $numberof_children .= $guest_info['baby'];
        }

        $item_data[] = [
            'key'     => esc_html__( 'Bebés', 'ova-brw' ),
            'value'   => wp_kses_post( $numberof_babies ),
            'display' => ''
        ];
    }

    // Custom checkout fields
    $cckf = ovabrw_get_list_field_checkout( $cart_item['product_id'] );
    if ( ovabrw_array_exists( $cckf ) ) {
        // Get cckf qty
        $cckf_qty = ovabrw_get_meta_data( 'cckf_qty', $cart_item );

        // Loop
        foreach ( $cckf as $key => $field ) {
            $value = ovabrw_get_meta_data( $key, $cart_item );
            if ( $value && 'on' === $field['enabled'] ) {
                if ( 'file' === $field['type'] ) {
                    $item_data[] = [
                        'key'     => $field['label'],
                        'value'   => $value,
                        'display' => ''
                    ];
                } else {
                    $item_data[] = [
                        'key'     => $field['label'],
                        'value'   => wc_clean( $value ),
                        'display' => ''
                    ];
                }
            }
        } // END loop
    } // END cckf

    // Resources
    $resources = ovabrw_get_meta_data( 'ovabrw_resources', $cart_item );
    if ( ovabrw_array_exists( $resources ) ) {
        // init
        $resc_values = [];

        // Get resource guests
        $resource_guests = ovabrw_get_meta_data( 'ovabrw_resource_guests', $cart_item );

        // Loop
        foreach ( $resources as $opt_id => $opt_value ) {
            // Get resource option guests
            $opt_guests = ovabrw_get_meta_data( $opt_id, $resource_guests );

            if ( ovabrw_array_exists( $opt_guests ) ) {
                // init option values
                $opt_values = [];

                // Get number of adults
                $number_adult = (int)ovabrw_get_meta_data( 'adult', $opt_guests );
                if ( $number_adult ) {
                    $opt_values[] = sprintf( esc_html__( 'Adulto: %s', 'ova-brw' ), $number_adult );
                }

                // Get number of children
                $number_child = (int)ovabrw_get_meta_data( 'child', $opt_guests );
                if ( $number_child ) {
                    $opt_values[] = sprintf( esc_html__( 'Niño: %s', 'ova-brw' ), $number_child );
                }

                // Get number of baby
                $number_baby = (int)ovabrw_get_meta_data( 'baby', $opt_guests );
                if ( $number_baby ) {
                    $opt_values[] = sprintf( esc_html__( 'Bebé: %s', 'ova-brw' ), $number_baby );
                }

                $resc_values[] = sprintf( esc_html__( '%s (%s)', 'ova-brw' ), $opt_value, implode( ', ', $opt_values ) );
            } else {
                $resc_values[] = $opt_value;
            }
        } // END

        if ( ovabrw_array_exists( $resc_values ) ) {
            $item_data[] = [
                'key'     => sprintf( _n( 'Resource%s', 'Resources%s', count( $resc_values ), 'ova-brw' ), '' ),
                'value'   => wc_clean( implode( ', ', $resc_values ) ),
                'display' => ''
            ];
        }
    }

    // Services
    $services = ovabrw_get_meta_data( 'ovabrw_services', $cart_item );
    if ( ovabrw_array_exists( $services ) ) {
        // Get service labels
        $serv_labels = $cart_item['data']->get_meta_value( 'label_service' );

        // Get service ids
        $serv_ids = $cart_item['data']->get_meta_value( 'service_id' );

        // Get service names
        $serv_names = $cart_item['data']->get_meta_value( 'service_name' );

        // Get service guests
        $service_guests = ovabrw_get_meta_data( 'ovabrw_service_guests', $cart_item );

        // Loop
        foreach ( $services as $ser_id ) {
            // Get option guests
            $opt_guests = ovabrw_get_meta_data( $ser_id, $service_guests );

            if ( ovabrw_array_exists( $serv_ids ) ) {
                foreach ( $serv_ids as $key => $value ) {
                    if ( ovabrw_array_exists( $value ) ) {
                        foreach ( $value as $k => $val ) {
                            if ( $val && $ser_id === $val ) {
                                $s_label  = $serv_labels[$key];
                                $s_name   = $serv_names[$key][$k];

                                // Number of guests
                                if ( ovabrw_array_exists( $opt_guests ) ) {
                                    $opt_values = [];

                                    // Get number of adults
                                    $number_adult = ovabrw_get_meta_data( 'adult', $opt_guests );
                                    if ( $number_adult ) {
                                        $opt_values[] = sprintf( esc_html__( 'Adulto: %s', 'ova-brw' ), $number_adult );
                                    }

                                    // Get number of children
                                    $number_child = ovabrw_get_meta_data( 'child', $opt_guests );
                                    if ( $number_child ) {
                                        $opt_values[] = sprintf( esc_html__( 'Niño: %s', 'ova-brw' ), $number_child );
                                    }

                                    // Get number of baby
                                    $number_baby = ovabrw_get_meta_data( 'baby', $opt_guests );
                                    if ( $number_baby ) {
                                        $opt_values[] = sprintf( esc_html__( 'Bebé: %s', 'ova-brw' ), $number_baby );
                                    }

                                    $s_name = sprintf( esc_html__( '%s (%s)', 'ova-brw' ), $s_name, implode( ', ', $opt_values ) );
                                }
                                
                                $item_data[] = [
                                    'key'     => $s_label,
                                    'value'   => wc_clean( $s_name ),
                                    'display' => ''
                                ];
                            }
                        }
                    }
                }
            }
        } // END loop
    } // END if

    return apply_filters( OVABRW_PREFIX.'get_item_data', $item_data, $cart_item );
}, 10, 2 );

/**
 * 4: Checkout validation
 */
add_action( 'woocommerce_after_checkout_validation', function( $data, $errors ) {
    foreach ( WC()->cart->get_cart() as $cart_item ) {
        if ( $cart_item['data'] && $cart_item['data']->is_type( OVABRW_RENTAL ) ) {
            // Get product ID
            $product_id = $cart_item['data']->get_id();

            // Check-in date
            $checkin_date = strtotime( ovabrw_get_meta_data( 'ovabrw_pickup_date', $cart_item ) );

            // Check-out date
            $checkout_date = strtotime( ovabrw_get_meta_data( 'ovabrw_pickoff_date', $cart_item ) );

            // Get quantity
            $quantity = absint( ovabrw_get_meta_data( 'ovabrw_quantity', $cart_item, 1 ) );

            // Get date format
            $date_format = ovabrw_get_date_format();
            if ( $cart_item['data']->has_time_slots() ) {
                $date_format = ovabrw_get_datetime_format();
            }

            // Get new dates
            $new_dates = ovabrw_new_input_date( $product_id, $checkin_date, $checkout_date, $date_format );

            // New check-in date
            $new_checkin = ovabrw_get_meta_data( 'pickup_date_new', $new_dates );

            // New check-out date
            $new_checkout = ovabrw_get_meta_data( 'pickoff_date_new', $new_dates );

            // Quantity by number of guests
            if ( ovabrw_qty_by_guests( $product_id ) ) {
                // Number of adults
                $ovabrw_adults = (int)ovabrw_get_meta_data( 'ovabrw_adults', $cart_item );

                // Number of children
                $ovabrw_children = (int)ovabrw_get_meta_data( 'ovabrw_childrens', $cart_item );

                // Number of babies
                $ovabrw_babies = (int)ovabrw_get_meta_data( 'ovabrw_babies', $cart_item );

                // Guests
                $guests = [
                    'adults'     => $ovabrw_adults * $quantity,
                    'children'   => $ovabrw_children * $quantity,
                    'babies'     => $ovabrw_babies * $quantity
                ];

                // Get available guests
                $guests_available = ovabrw_validate_guests_available( $product_id, $checkin_date, $checkout_date, $guests, 'checkout' );

                if ( ovabrw_array_exists( $guests_available ) ) {
                    return true;
                } else {
                    $errors->add( 'validation', sprintf( esc_html__( '%s no está disponible para este horario. Por favor, reserva otro horario.', 'ova-brw' ), $cart_item['data']->name ) );
                }
            } else {
                $items_available = ova_validate_manage_store( $product_id, $new_checkin, $new_checkout, true, 'checkout', $quantity );

                if ( ovabrw_array_exists( $items_available ) ) {
                    return $items_available['status'];
                } else {
                    $errors->add( 'validation', sprintf( esc_html__( '%s no está disponible para este horario. Por favor, reserva otro horario.', 'ova-brw' ), $cart_item['data']->name ) );
                }
            }
        }
    }
}, 10, 2 );

/**
 * 4: Cart block validation
 */
add_action( 'woocommerce_store_api_cart_errors', function( $cart_errors, $cart ) {
    if ( method_exists( $cart, 'cart_contents' ) && ovabrw_array_exists( $cart->cart_contents ) ) {
        foreach ( $cart->cart_contents as $cart_item ) {
            if ( $cart_item['data'] && $cart_item['data']->is_type( OVABRW_RENTAL ) ) {
                // Get product ID
                $product_id = $cart_item['data']->get_id();

                // Check-in date
                $checkin_date = strtotime( ovabrw_get_meta_data( 'ovabrw_pickup_date', $cart_item ) );

                // Check-out date
                $checkout_date = strtotime( ovabrw_get_meta_data( 'ovabrw_pickoff_date', $cart_item ) );

                // Get quantity
                $quantity = absint( ovabrw_get_meta_data( 'ovabrw_quantity', $cart_item, 1 ) );

                // Get date format
                $date_format = ovabrw_get_date_format();
                if ( $cart_item['data']->has_time_slots() ) {
                    $date_format = ovabrw_get_datetime_format();
                }

                // Get new dates
                $new_dates = ovabrw_new_input_date( $product_id, $checkin_date, $checkout_date, $date_format );

                // New check-in date
                $new_checkin = ovabrw_get_meta_data( 'pickup_date_new', $new_dates );

                // New check-out date
                $new_checkout = ovabrw_get_meta_data( 'pickoff_date_new', $new_dates );

                // Quantity by number of guests
                if ( ovabrw_qty_by_guests( $product_id ) ) {
                    // Number of adults
                    $ovabrw_adults = (int)ovabrw_get_meta_data( 'ovabrw_adults', $cart_item );

                    // Number of children
                    $ovabrw_children = (int)ovabrw_get_meta_data( 'ovabrw_childrens', $cart_item );

                    // Number of babies
                    $ovabrw_babies = (int)ovabrw_get_meta_data( 'ovabrw_babies', $cart_item );

                    // Guests
                    $guests = [
                        'adults'     => $ovabrw_adults * $quantity,
                        'children'   => $ovabrw_children * $quantity,
                        'babies'     => $ovabrw_babies * $quantity
                    ];

                    // Get available guests
                    $guests_available = ovabrw_validate_guests_available( $product_id, $checkin_date, $checkout_date, $guests, 'checkout' );

                    if ( !ovabrw_array_exists( $guests_available ) ) {
                        $errors->add( 'validation', sprintf( esc_html__( '%s no está disponible para este horario. Por favor, reserva otro horario.', 'ova-brw' ), $cart_item['data']->name ) );
                    }
                } else {
                    $items_available = ova_validate_manage_store( $product_id, $new_checkin, $new_checkout, true, 'checkout', $quantity );
                    if ( !ovabrw_array_exists( $items_available ) ) {
                        $errors->add( 'validation', sprintf( esc_html__( '%s no está disponible para este horario. Por favor, reserva otro horario.', 'ova-brw' ), $cart_item['data']->name ) );
                    }
                } // END if
            }
        }
    }
}, 11, 2 );

// 5: Save to Order
add_action( 'woocommerce_checkout_create_order_line_item', function( $item, $cart_item_key, $values, $order ) {
    // Get product id
    $product_id = method_exists( $item, 'get_product_id' ) ? $item->get_product_id() : '';

    // Check product type: rental
    $product = wc_get_product( $product_id );
    if ( !$product || !$product->is_type( OVABRW_RENTAL ) ) return;

    // Check-in date
    $checkin_date = ovabrw_get_meta_data( 'ovabrw_pickup_date', $values );
    if ( !$checkin_date ) return;
    $item->add_meta_data( 'ovabrw_pickup_date', $checkin_date, true );
    $item->add_meta_data( 'ovabrw_pickup_date_strtotime', strtotime( $checkin_date ), true );

    // Check-out date
    $checkout_date = ovabrw_get_meta_data( 'ovabrw_pickoff_date', $values );
    if ( !$checkout_date ) return;
    $item->add_meta_data( 'ovabrw_pickoff_date', $checkout_date, true );
    $item->add_meta_data( 'ovabrw_dropoff_date_strtotime', strtotime( $checkout_date ), true );

    // Time from
    $time_from = ovabrw_get_meta_data( 'ovabrw_time_from', $values );
    if ( $time_from ) {
        $item->add_meta_data( 'ovabrw_time_from', $time_from, true );
    }

    // Number of adults
    $numberof_adults = (int)ovabrw_get_meta_data( 'ovabrw_adults', $values, 1 );
    $item->add_meta_data( 'ovabrw_adults', $numberof_adults, true );

    // Number of children
    $numberof_children = (int)ovabrw_get_meta_data( 'ovabrw_childrens', $values );
    if ( $numberof_children ) {
        $item->add_meta_data( 'ovabrw_childrens', $numberof_children, true );
    }

    // Number of babies
    $numberof_babies = (int)ovabrw_get_meta_data( 'ovabrw_babies', $values );
    if ( $numberof_babies ) {
        $item->add_meta_data( 'ovabrw_babies', $numberof_babies, true );
    }

    // Guest information
    $guest_info = ovabrw_get_meta_data( 'ovabrw_guest_info', $values );
    if ( ovabrw_array_exists( $guest_info ) ) {
        $item->add_meta_data( 'ovabrw_guest_info', $guest_info, true );
    }

    // Quantity
    $quantity = (int)ovabrw_get_meta_data( 'ovabrw_quantity', $values, 1 );
    $item->add_meta_data( 'ovabrw_quantity', $quantity, true );

    // Custom checkout fields
    $cckf = ovabrw_get_list_field_checkout( $product_id );
    if ( ovabrw_array_exists( $cckf ) ) {
        foreach ( $cckf as $key => $field ) {
            $value = ovabrw_get_meta_data( $key, $values );

            if ( $value && 'on' === $field['enabled'] ) {
                if ( 'select' === $field['type'] ) {
                    // Option keys
                    $opt_keys = ovabrw_get_meta_data( 'ova_options_key', $field );

                    // Option texts
                    $opt_texts = ovabrw_get_meta_data( 'ova_options_text', $field );

                    // Search index
                    $index = array_search( $value, $opt_keys );

                    if ( !is_bool( $index ) ) {
                        $value = ovabrw_get_meta_data( $index, $opt_texts );
                    }
                }

                $item->add_meta_data( $key, $value, true );
            }
        }
    }

    if ( ovabrw_array_exists( ovabrw_get_meta_data( 'custom_ckf', $values ) ) ) {
        $item->add_meta_data( 'ovabrw_custom_ckf', $values['custom_ckf'], true );

        // CCKF Quantity
        if ( ovabrw_array_exists( ovabrw_get_meta_data( 'cckf_qty', $values ) ) ) {
            $item->add_meta_data( 'ovabrw_cckf_qty', $values['cckf_qty'], true );
        }
    }

    // Resouces
    $resources = ovabrw_get_meta_data( 'ovabrw_resources', $values );
    if ( ovabrw_array_exists( $resources ) ) {
        // init
        $resc_values = [];

        // Get resource guests
        $resource_guests = ovabrw_get_meta_data( 'ovabrw_resource_guests', $values );

        // Loop
        foreach ( $resources as $opt_id => $opt_value ) {
            // Get resource option guests
            $opt_guests = ovabrw_get_meta_data( $opt_id, $resource_guests );

            if ( ovabrw_array_exists( $opt_guests ) ) {
                // init option values
                $opt_values = [];

                // Get number of adults
                $number_adult = (int)ovabrw_get_meta_data( 'adult', $opt_guests );
                if ( $number_adult ) {
                    $opt_values[] = sprintf( esc_html__( 'Adulto: %s', 'ova-brw' ), $number_adult );
                }

                // Get number of children
                $number_child = (int)ovabrw_get_meta_data( 'child', $opt_guests );
                if ( $number_child ) {
                    $opt_values[] = sprintf( esc_html__( 'Niño: %s', 'ova-brw' ), $number_child );
                }

                // Get number of baby
                $number_baby = (int)ovabrw_get_meta_data( 'baby', $opt_guests );
                if ( $number_baby ) {
                    $opt_values[] = sprintf( esc_html__( 'Bebé: %s', 'ova-brw' ), $number_baby );
                }

                $resc_values[] = sprintf( esc_html__( '%s (%s)', 'ova-brw' ), $opt_value, implode( ', ', $opt_values ) );
            } else {
                $resc_values[] = $opt_value;
            }
        } // END

        if ( ovabrw_array_exists( $resc_values ) ) {
            $item->add_meta_data( sprintf( _n( 'Resource%s', 'Resources%s', count( $resc_values ), 'ova-brw' ), '' ), implode( ', ', $resc_values ), true );
        }

        // Save resources
        $item->add_meta_data( 'ovabrw_resources', $resources, true );

        // Save resource guests
        if ( ovabrw_array_exists( $resource_guests ) ) {
            $item->add_meta_data( 'ovabrw_resource_guests', $resource_guests, true );
        }
    }

    // Services
    $services = ovabrw_get_meta_data( 'ovabrw_services', $values );
    if ( ovabrw_array_exists( $services ) ) {
        // Get service labels
        $serv_labels = $product->get_meta_value( 'label_service' );

        // Get service ids
        $serv_ids = $product->get_meta_value( 'service_id' );

        // Get service names
        $serv_names = $product->get_meta_value( 'service_name' );

        // Get service guests
        $service_guests = ovabrw_get_meta_data( 'ovabrw_service_guests', $values );

        // Loop
        foreach ( $services as $ser_id ) {
            if ( ovabrw_array_exists( $serv_ids ) ) {
                foreach ( $serv_ids as $key => $value ) {
                    if ( ovabrw_array_exists( $value ) ) {
                        foreach ( $value as $k => $val ) {
                            if ( $val && $ser_id == $val ) {
                                $s_label  = $serv_labels[$key];
                                $s_name   = $serv_names[$key][$k];

                                // Get option guests
                                $opt_guests = ovabrw_get_meta_data( $ser_id, $service_guests );
                                if ( ovabrw_array_exists( $opt_guests ) ) {
                                    $opt_values = [];

                                    // Get number of adults
                                    $number_adult = ovabrw_get_meta_data( 'adult', $opt_guests );
                                    if ( $number_adult ) {
                                        $opt_values[] = sprintf( esc_html__( 'Adulto: %s', 'ova-brw' ), $number_adult );
                                    }

                                    // Get number of children
                                    $number_child = ovabrw_get_meta_data( 'child', $opt_guests );
                                    if ( $number_child ) {
                                        $opt_values[] = sprintf( esc_html__( 'Niño: %s', 'ova-brw' ), $number_child );
                                    }

                                    // Get number of baby
                                    $number_baby = ovabrw_get_meta_data( 'baby', $opt_guests );
                                    if ( $number_baby ) {
                                        $opt_values[] = sprintf( esc_html__( 'Bebé: %s', 'ova-brw' ), $number_baby );
                                    }

                                    $s_name = sprintf( esc_html__( '%s (%s)', 'ova-brw' ), $s_name, implode( ', ', $opt_values ) );
                                }

                                $item->add_meta_data( $s_label, $s_name, true );
                            } // END if
                        } // END foreach
                    } // END if
                } // END foreach
            } // END if
        } // END loop

        // Save services
        $item->add_meta_data( 'ovabrw_services', $services, true );

        // Save service guests
        if ( ovabrw_array_exists( $service_guests ) ) {
            $item->add_meta_data( 'ovabrw_service_guests', $service_guests, true );
        }
    }

    // Insurance
    $insurance_amount = $values['data']->get_meta( 'insurance_amount' );
    if ( $insurance_amount ) {
        $item->add_meta_data( 'ovabrw_insurance_amount', ovabrw_convert_price( $insurance_amount ), true );

        // Insurance tax
        $insurance_tax = $values['data']->get_meta( 'insurance_tax' );
        if ( $insurance_tax ) {
            $item->add_meta_data( 'ovabrw_insurance_tax', ovabrw_convert_price( $insurance_tax ), true );
        }

        // Remaining insurance
        $remaining_insurance = $values['data']->get_meta( 'remaining_insurance' );
        if ( $remaining_insurance ) {
            $item->add_meta_data( 'ovabrw_remaining_insurance', ovabrw_convert_price( $remaining_insurance ), true );
        }

        // Remaining insurance tax
        $remaining_insurance_tax = $values['data']->get_meta( 'remaining_insurance_tax' );
        if ( $remaining_insurance_tax ) {
            $item->add_meta_data( 'ovabrw_remaining_insurance_tax', ovabrw_convert_price( $remaining_insurance_tax ), true );
        }
    }

    // Deposit
    if ( isset( WC()->cart->deposit_info[ 'has_deposit' ] ) && WC()->cart->deposit_info[ 'has_deposit' ] ) {
        $deposit_type       = $values['data']->get_meta( 'deposit_type' );
        $deposit_value      = $values['data']->get_meta( 'deposit_value' );
        $deposit_amount     = $values['data']->get_meta( 'deposit_amount' );
        $remaining_amount   = $values['data']->get_meta( 'remaining_amount' );
        $remaining_tax      = $values['data']->get_meta( 'remaining_tax' );
        $total_payable      = $values['data']->get_meta( 'total_payable' );

        if ( $deposit_type ) {
            $item->add_meta_data( 'ovabrw_deposit_type', $deposit_type, true );
        }
        if ( $deposit_value ) {
            $item->add_meta_data( 'ovabrw_deposit_value', $deposit_value, true );
        }
        if ( $deposit_amount ) {
            $item->add_meta_data( 'ovabrw_deposit_amount', ovabrw_convert_price( $deposit_amount ), true );
        }
        if ( $remaining_amount ) {
            $item->add_meta_data( 'ovabrw_remaining_amount', ovabrw_convert_price( $remaining_amount ), true );
        }
        if ( $remaining_tax ) {
            $item->add_meta_data( 'ovabrw_remaining_tax', $remaining_tax, true );
        }
        if ( $total_payable ) {
            $item->add_meta_data( 'ovabrw_total_payable', ovabrw_convert_price( $total_payable ), true );
        }
    }
}, 10, 4 );

// Checkout create order fee item
add_action( 'woocommerce_checkout_create_order_fee_item', function( $item, $fee_key, $fee, $order ) {
    $insurance_key = isset( WC()->cart->deposit_info[ 'insurance_key' ] ) ? WC()->cart->deposit_info[ 'insurance_key' ] : '';

    if ( $insurance_key === $fee_key ) {
        $order->add_meta_data( '_ova_insurance_key', $insurance_key );
    }
}, 10, 4 );

// Order item display meta value
add_filter( 'woocommerce_order_item_display_meta_value', function( $display_value, $meta, $item ) {
    // Get product ID
    $product_id = method_exists( $item, 'get_product_id' ) ? $item->get_product_id() : '';
    if ( !$product_id ) return $display_value;

    // Get guest info
    if ( apply_filters( OVABRW_PREFIX.'view_guest_info_in_order_detail', true ) ) {
        $guest_info = $item->get_meta( 'ovabrw_guest_info' );
        if ( ovabrw_array_exists( $guest_info ) ) {
            // Guest options
            $guest_names = [
                'ovabrw_adults'     => 'adult',
                'ovabrw_childrens'  => 'child',
                'ovabrw_babies'     => 'baby'
            ];

            if ( in_array( $meta->key, array_keys( $guest_names ) ) ) {
                if ( is_wc_endpoint_url( 'order-received' ) || ( is_admin() && !did_action( 'woocommerce_email_before_order_table' ) && !did_action( 'yaymail_before_email_content' ) ) ) {
                    // Get guest info HTML
                    $guest_info_html = ovabrw_get_guest_info_html( $product_id, $guest_info );

                    // Info
                    $info_html = ovabrw_get_meta_data( $guest_names[$meta->key], $guest_info_html );
                    if ( $info_html ) {
                        $display_value .= $info_html;
                    }
                } else {
                    // Order ID
                    $order_id = method_exists( $item, 'get_order_id' ) ? $item->get_order_id() : '';

                    if ( $order_id ) {
                        // Get the 'view-order' endpoint slug
                        $view_order_endpoint = get_option( 'woocommerce_myaccount_view_order_endpoint', 'view-order' );

                        // Order view URL
                        $order_view_url = wc_get_endpoint_url( $view_order_endpoint, $order_id, wc_get_page_permalink('myaccount') );

                        // Display value
                        $display_value .= ' <a href="' . esc_url( $order_view_url ) . '">'.esc_html__( '(ver información)', 'ova-brw' ).'</a>';
                    }
                }
            }
        }
    }

    return apply_filters( OVABRW_PREFIX.'order_item_display_meta_value', $display_value, $meta, $item );
}, 11, 3 );

/**
 * Get available items
 */
if ( !function_exists( 'ova_validate_manage_store' ) ) {
    function ova_validate_manage_store( $product_id = false, $pickup_date = '', $pickoff_date = '', $passed = false, $validate = 'cart', $quantity = 1 ) {
        $quantity = absint( $quantity );

        // Unavailable Time (UT)
        $validate_ut = ovabrw_validate_unavailable_time( $product_id, $pickup_date, $pickoff_date, $validate );
        if ( $validate_ut ) return false;

        // Disable week day
        $validate_dwd = ovabrw_validate_disable_week_day( $product_id, $pickup_date, $pickoff_date, $validate );
        if ( $validate_dwd ) return false;

        // Check Count Product in Order
        $store_quantity = ovabrw_quantity_available_in_order( $product_id, $pickup_date, $pickoff_date );
        
        // Check Count Product in Cart
        $cart_quantity  = ovabrw_quantity_available_in_cart( $product_id, $validate, $pickup_date, $pickoff_date );
        
        // Check Quantity Available
        $qty_available = ovabrw_get_quantity_available( $product_id, $store_quantity, $cart_quantity, $quantity, $passed, $validate );
        
        if ( !empty( $qty_available ) && $qty_available['passed'] && $qty_available['quantity_available'] > 0 ) {
            return apply_filters( OVABRW_PREFIX.'get_available_items', [
                'status'                => $qty_available['passed'],
                'quantity_available'    => $qty_available['quantity_available']
            ], $product_id, $pickup_date, $pickoff_date, $passed, $validate, $quantity );
        }

        return false;
    }
}

/**
 * Check quantity available in order
 */
if ( !function_exists( 'ovabrw_quantity_available_in_order' ) ) {
    function ovabrw_quantity_available_in_order( $product_id, $pickup_date, $dropoff_date ) {
        // Booked quantity
        $booked_qty = 0;

        // Get array product ids when use WPML
        $product_ids = ovabrw_get_wpml_product_ids( $product_id );

        // Get all Order ID by Product ID
        $statuses   = brw_list_order_status();
        $orders_ids = ovabrw_get_orders_by_product_id( $product_id, $statuses );

        if ( $orders_ids ) {
            foreach ( $orders_ids as $key => $order_id ) {
                // Get order id
                $order = wc_get_order( $order_id );

                // Get order items
                $order_items = $order->get_items( 'line_item' );
                
                // For Meta Data
                foreach ( $order_items as $item_id => $item ) {
                    // Get product
                    $product_id = $item->get_product_id();

                    // Check Line Item have item ID is Car_ID
                    if ( in_array( $product_id, $product_ids ) ) {
                        // Get check-in date
                        $checkin_date = strtotime( $item->get_meta( 'ovabrw_pickup_date' ) );

                        // Get check-out date
                        $checkout_date = strtotime( $item->get_meta( 'ovabrw_pickoff_date' ) );

                        // Get booked quantity
                        $quantity = absint( $item->get_meta( 'ovabrw_quantity' ) );

                        // Get quantity
                        if ( $checkout_date >= current_time( 'timestamp' ) ) {
                            if ( !( $pickup_date >= $checkout_date || $dropoff_date <= $checkin_date ) ) {
                                $booked_qty += $quantity;
                            }
                        }  
                    }
                } // END foreach
            } // END foreach
        } // END if

        return apply_filters( OVABRW_PREFIX.'quantity_available_in_order', $booked_qty, $product_id, $pickup_date, $dropoff_date );
    }
}

/**
 * Check quantity available in cart
 */
if ( !function_exists( 'ovabrw_quantity_available_in_cart' ) ) {
    function ovabrw_quantity_available_in_cart( $product_id, $validate, $pickup_date, $dropoff_date ) {
        // init
        $booked_qty = 0;

        // Get array product ids when use WPML
        $product_ids = ovabrw_get_wpml_product_ids( $product_id );

        if ( 'cart' === $validate ) {
            foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

                if ( in_array( $product_id, $product_ids ) ) {
                    // Check-in date
                    $checkin_date = strtotime( ovabrw_get_meta_data( 'ovabrw_pickup_date', $cart_item ) );
                    if ( !$checkin_date ) continue;

                    // Check-out date
                    $checkout_date = strtotime( ovabrw_get_meta_data( 'ovabrw_pickoff_date', $cart_item ) );
                    if ( !$checkout_date ) continue;

                    // Quantity
                    $quantity = (int)ovabrw_get_meta_data( 'ovabrw_quantity', $cart_item, 1 );

                    if ( !( $pickup_date >= $checkout_date || $dropoff_date <= $checkin_date ) ) {
                        $booked_qty += $quantity;
                    }
                }
            }
        }

        return apply_filters( OVABRW_PREFIX.'quantity_available_in_cart', $booked_qty, $product_id, $validate, $pickup_date, $dropoff_date );
    }
}

/**
 * Get quantity available store
 */
if ( !function_exists( 'ovabrw_get_quantity_available' ) ) {
    function ovabrw_get_quantity_available( $product_id = false, $store_quantity = 0, $cart_quantity = 0, $quantity = 1, $passed = false, $validate = 'cart' ) {
        // Get stock quantity of product
        $stock_quantity = absint( get_post_meta( $product_id, 'ovabrw_stock_quantity', true ) );

        // Quantity available
        $quantity_available = (int)( $stock_quantity - (int)$store_quantity - (int)$cart_quantity );

        if ( $quantity_available > 0 && $quantity_available >= $quantity ) {
            $passed = true;
        } else {
            if ( 'search' != $validate && !wp_doing_ajax() ) {
                wc_clear_notices();

                if ( $quantity > $quantity_available && $quantity_available != 0 && $quantity_available > 0 ) {
                    wc_add_notice( sprintf( esc_html__( 'Los tours disponibles son %s', 'ova-brw'  ), $number_available ), 'error' );
                } else {
                    wc_add_notice( esc_html__( 'Este tour no está disponible a esta hora. Por favor, reserve para otro horario.', 'ova-brw' ), 'error' );
                }
            }

            if ( $quantity_available < 0 ) {
                $quantity_available = 0;
            }

            return false;
        }

        return apply_filters( OVABRW_PREFIX.'get_quantity_available', [
            'passed'              => $passed,
            'quantity_available'  => $quantity_available
        ], $product_id, $store_quantity, $cart_quantity, $quantity, $passed, $validate );
    }
}

/**
 * Check Unavailable
 */
if ( !function_exists( 'ovabrw_check_unavailable' ) ) {
    function ovabrw_check_unavailable( $product_id, $pickup_date, $dropoff_date ) {
        $dropoff_original = $dropoff_date;
        $dropoff_date     = apply_filters( 'ovabrw_effective_dropoff_for_availability', $dropoff_date, $product_id, $pickup_date, $dropoff_original );

        if ( function_exists( 'offitravel_ovabrw_whitelist_enabled' ) && offitravel_ovabrw_whitelist_enabled( $product_id ) ) {
            if ( ! function_exists( 'offitravel_ovabrw_pickup_in_whitelist' ) || ! offitravel_ovabrw_pickup_in_whitelist( $product_id, $pickup_date ) ) {
                return true;
            }
        }

        // Error: Unvailable time for renting
        $untime_startdate = get_post_meta( $product_id, 'ovabrw_untime_startdate', true );
        $untime_enddate   = get_post_meta( $product_id, 'ovabrw_untime_enddate', true );

        if ( ! ( function_exists( 'offitravel_ovabrw_whitelist_enabled' ) && offitravel_ovabrw_whitelist_enabled( $product_id ) ) ) {
            if ( ovabrw_array_exists( $untime_startdate ) ) {
                foreach ( $untime_startdate as $key => $value ) {
                    // Start date
                    $start_date = strtotime( $value );
                    if ( !$start_date ) continue;

                    // End date
                    $end_date = strtotime( ovabrw_get_meta_data( $key, $untime_enddate ) );
                    if ( !$end_date ) continue;

                    if ( !( $pickup_date > $end_date || $dropoff_date < $start_date ) ) {
                        return true;
                    }
                }
            }
        }

        // Error: Unavailable Date for booking in settings
        $disable_week_day = get_post_meta( $product_id, 'ovabrw_product_disable_week_day', true );
        if ( !$disable_week_day ) {
            $disable_week_day = ovabrw_get_option_setting( 'calendar_disable_week_day', '' );
        }
        
        $data_disable_week_day = $disable_week_day != '' ? explode( ',', $disable_week_day ) : '';
        if ( $data_disable_week_day && $pickup_date && $dropoff_date ) {
            if ( apply_filters( 'ovabrw_disable_week_day', true ) ) {
                $datediff       = absint( $dropoff_date ) - absint( $pickup_date );
                $total_datediff = round( $datediff / (60 * 60 * 24), wc_get_price_decimals() ) + 1;

                // get number day
                $pickup_date_of_week   = date( 'w', $pickup_date );

                $pickup_date_timestamp = $pickup_date;
                
                $i = 0;

                while ( $i <= $total_datediff ) {
                    if ( in_array( $pickup_date_of_week, $data_disable_week_day ) ) {
                        return true;
                    }

                    $pickup_date_of_week    = date('w', $pickup_date_timestamp );
                    $pickup_date_timestamp  = strtotime('+1 day', $pickup_date_timestamp);
                    $i++;
                }
            } else {
                // get number day
                $pickup_date_of_week = date( 'w', $pickup_date );

                if ( in_array( $pickup_date_of_week, $data_disable_week_day ) ) return true;
            }
        }

        return false;
    }
}

/**
 * Standardized Pick-up, Drop-off that the Guest enter at frontend
 * User for: Search, Compare with real date
 */
if ( !function_exists( 'ovabrw_new_input_date' ) ) {
    function ovabrw_new_input_date( $product_id = '', $pickup_date = '', $pickoff_date = '', $date_format = 'd-m-Y' ) {
        if ( !$product_id ) return [
            'pickup_date_new'   => '',
            'pickoff_date_new'  => ''
        ];

        // Pick-up date
        $pickup_date = $pickup_date ? strtotime( gmdate( $date_format, $pickup_date ) ) : '';

        // Drop-off date
        $pickoff_date = $pickoff_date ? strtotime( gmdate( $date_format, $pickoff_date ) ) : '';

        return apply_filters( OVABRW_PREFIX.'new_input_date', [
            'pickup_date_new'   => $pickup_date,
            'pickoff_date_new'  => $pickoff_date
        ]);
    }
}

/** ======== Quantity by Guests ======== */
/**
 * Product validate Guests available
 */
if ( !function_exists( 'ovabrw_validate_guests_available' ) ) {
    function ovabrw_validate_guests_available( $product_id = null, $check_in = null, $check_out = null, $guests = [], $validate = 'cart' ) {
        if ( ! $product_id || ! $check_in || ! $check_out ) return false;

        // Unavailable Time (UT)
        $validate_ut = ovabrw_validate_unavailable_time( $product_id, $check_in, $check_out, $validate );
        if ( $validate_ut ) return false;

        // Disable week day
        $validate_dwd = ovabrw_validate_disable_week_day( $product_id, $check_in, $check_out, $validate );
        if ( $validate_dwd ) return false;

        // Get Guests in Cart
        $guests_in_cart = ovabrw_get_guests_in_cart( $product_id, $check_in, $validate );

        // Get Guests in Order
        $guests_in_order = ovabrw_get_guests_in_order( $product_id, $check_in );

        // Get Guests available
        $guests_available = ovabrw_get_guests_available( $product_id, $guests, $guests_in_cart, $guests_in_order, $validate );

        if ( ovabrw_array_exists( $guests_available ) ) {
            return apply_filters( OVABRW_PREFIX.'validate_guests_available', $guests_available, $product_id, $check_in, $check_out, $guests, $validate );
        }

        return false;
    }
}

/**
 * Product validate unavailable time
 */
if ( !function_exists( 'ovabrw_validate_unavailable_time' ) ) {
    function ovabrw_validate_unavailable_time( $product_id = null, $check_in = null, $check_out = null, $validate = 'cart' ) {
        if ( !$product_id || !$check_in || !$check_out ) return false;

        $check_out_original = $check_out;
        $check_out          = apply_filters( 'ovabrw_effective_dropoff_for_availability', $check_out, $product_id, $check_in, $check_out_original );

        if ( function_exists( 'offitravel_ovabrw_whitelist_enabled' ) && offitravel_ovabrw_whitelist_enabled( $product_id ) ) {
            if ( ! function_exists( 'offitravel_ovabrw_pickup_in_whitelist' ) || ! offitravel_ovabrw_pickup_in_whitelist( $product_id, $check_in ) ) {
                if ( 'search' != $validate ) {
                    wc_clear_notices();
                    wc_add_notice( esc_html__( 'Este horario no está disponible para reservar.', 'ova-brw' ), 'error' );
                }
                return true;
            }
            return false;
        }

        // Get untime
        $untime_startdate = get_post_meta( $product_id, 'ovabrw_untime_startdate', true );
        $untime_enddate   = get_post_meta( $product_id, 'ovabrw_untime_enddate', true );

        if ( ovabrw_array_exists( $untime_startdate ) ) {
            foreach ( $untime_startdate as $k => $start_date ) {
                // Start date
                $start_date = strtotime( $start_date );
                if ( !$start_date ) continue;

                // End date
                $end_date = strtotime( ovabrw_get_meta_data( $k, $untime_enddate ) );
                if ( !$end_date ) continue;

                if ( !( $check_in > $end_date || $check_out < $start_date ) ) {
                    if ( 'search' != $validate ) {
                        wc_clear_notices();
                        wc_add_notice( esc_html__( 'Este horario no está disponible para reservar.', 'ova-brw' ), 'error' );
                    }

                    return true;
                }
            }
        }

        return false;
    }
}

/**
 * Product validate Disable week day
 */
if ( !function_exists( 'ovabrw_validate_disable_week_day' ) ) {
    function ovabrw_validate_disable_week_day( $product_id = null, $check_in = null, $check_out = null, $validate = 'cart' ) {
        if ( ! $product_id || ! $check_in || ! $check_out ) return false;

        $check_out_original = $check_out;
        $check_out          = apply_filters( 'ovabrw_effective_dropoff_for_availability', $check_out, $product_id, $check_in, $check_out_original );

        // Get disable week day
        $disable_week_day = get_post_meta( $product_id, 'ovabrw_product_disable_week_day', true );
        if ( !$disable_week_day ) {
            $disable_week_day = ovabrw_get_option_setting( 'calendar_disable_week_day', '' );
        }

        if ( $disable_week_day != '' && $check_in && $check_out ) {
            $disable_week_day = explode( ',', $disable_week_day );

            if ( apply_filters( 'ovabrw_disable_week_day', true ) ) {
                $datediff       = absint( $check_out ) - absint( $check_in );
                $total_datediff = round( $datediff / 86400, 2 ) + 1;

                // Get number day
                $check_in_of_week   = date( 'w', $check_in );
                $check_out_of_week  = date( 'w', $check_out );

                $check_in_timestamp = $check_in;
                
                $i = 0;

                while ( $i <= $total_datediff ) {
                    if ( in_array( $check_in_of_week, $disable_week_day ) || in_array( $check_out_of_week, $disable_week_day ) ) {
                        if ( $validate != 'search' ) {
                            wc_clear_notices();
                            wc_add_notice( esc_html__( 'Este horario no está disponible para reservar.', 'ova-brw' ), 'error' );
                        }

                        return true;
                    }

                    $check_in_of_week    = date( 'w', $check_in_timestamp );
                    $check_in_timestamp  = strtotime( '+1 day', $check_in_timestamp);

                    $i++;
                }
            } else {
                // Get number day
                $check_in_of_week = date( 'w', $check_in );

                if ( in_array( $check_in_of_week, $disable_week_day ) ) {
                    if ( $validate != 'search' ) {
                        wc_clear_notices();
                        wc_add_notice( esc_html__( 'Este horario no está disponible para reservar.', 'ova-brw' ), 'error');
                    }
                    
                    return true;
                }
            }
        }

        return false;
    }
}

/**
 * Product get number of guests in Cart
 */
if ( !function_exists( 'ovabrw_get_guests_in_cart' ) ) {
    function ovabrw_get_guests_in_cart( $product_id = null, $check_in = null, $validate = 'cart' ) {
        if ( !$product_id || !$check_in ) return false;

        if ( 'cart' === $validate || 'search' === $validate ) {
            $results = [
                'adults'     => 0,
                'children'   => 0,
                'babies'     => 0
            ];

            // Get array product ids when use WPML
            $args_product_ids = ovabrw_get_wpml_product_ids( $product_id );
            
            if ( WC()->cart && is_object( WC()->cart ) ) {
                // Get current time
                $current_time = strtotime( gmdate( ovabrw_get_date_format(), current_time( 'timestamp' ) ) );

                foreach ( WC()->cart->get_cart() as $k => $cart_item ) {
                    // Get product ID
                    $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $k );
                    if ( !in_array( $product_id, $args_product_ids ) ) continue;

                    // Get check-in date
                    $cart_check_in = strtotime( ovabrw_get_meta_data( 'ovabrw_pickup_date', $cart_item ) );
                    if ( !$cart_check_in ) continue;

                    // Get check-out date
                    $cart_check_out = strtotime( ovabrw_get_meta_data( 'ovabrw_pickoff_date', $cart_item ) );
                    if ( !$cart_check_out || $cart_check_out < $current_time ) continue;

                    // Get number of adults
                    $cart_adults = absint( ovabrw_get_meta_data( 'ovabrw_adults', $cart_item ) );

                    // Get number of chilren
                    $cart_children = absint( ovabrw_get_meta_data( 'ovabrw_childrens', $cart_item ) );

                    // Get number of babies
                    $cart_babies = absint( ovabrw_get_meta_data( 'ovabrw_babies', $cart_item ) );

                    // Get quantity
                    $cart_qty = absint( ovabrw_get_meta_data( 'ovabrw_quantity', $cart_item, 1 ) );

                    if ( $cart_check_in <= $check_in && $check_in <= $cart_check_out ) {
                        $results['adults']      += $cart_adults * $cart_qty;
                        $results['children']    += $cart_children * $cart_qty;
                        $results['babies']      += $cart_babies * $cart_qty;
                    }
                }
            }

            return apply_filters( OVABRW_PREFIX.'get_guests_in_cart', $results, $product_id, $check_in, $validate );
        }

        return false;
    }
}

/**
 * Product get number of guests in Order
 */
if ( !function_exists( 'ovabrw_get_guests_in_order' ) ) {
    function ovabrw_get_guests_in_order( $product_id = null, $check_in = null ) {
        if ( !$product_id || !$check_in ) return false;

        // Get all Order ID by Product ID
        $status     = brw_list_order_status();
        $order_ids  = ovabrw_get_orders_by_product_id( $product_id, $status );

        if ( ovabrw_array_exists( $order_ids ) ) {
            $results = [
                'adults'     => 0,
                'children'   => 0,
                'babies'     => 0
            ];

            // Get current time
            $current_time = strtotime( gmdate( ovabrw_get_date_format(), current_time( 'timestamp' ) ) );

            // Get array product ids when use WPML
            $product_ids = ovabrw_get_wpml_product_ids( $product_id );

            foreach ( $order_ids as $k => $order_id ) {
                // Get Order
                $order = wc_get_order( $order_id );

                // Get order items
                $order_items = $order->get_items( 'line_item' );

                if ( ovabrw_array_exists( $order_items ) ) {
                    foreach ( $order_items as $item_id => $item ) {
                        $product_id = $item->get_product_id();

                        // Check Line Item have item ID is Car_ID
                        if ( in_array( $product_id , $product_ids ) ) {
                            // Get check-in date
                            $item_check_in = strtotime( $item->get_meta( 'ovabrw_pickup_date' ) );
                            if ( !$item_check_in ) continue;

                            // Get check-out date
                            $item_check_out = strtotime( $item->get_meta( 'ovabrw_pickoff_date' ) );
                            if ( !$item_check_out || $item_check_out < $current_time ) continue;

                            // Get number of adults
                            $item_adults = absint( $item->get_meta( 'ovabrw_adults' ) );

                            // Get number of children
                            $item_children = absint( $item->get_meta( 'ovabrw_childrens' ) );

                            // Get number of babies
                            $item_babies = absint( $item->get_meta( 'ovabrw_babies' ) );

                            // Get quantity
                            $item_qty = absint( $item->get_meta( 'ovabrw_quantity' ) );
                            if ( !$item_qty ) $item_qty = 1;

                            if ( $item_check_in <= $check_in && $check_in <= $item_check_out ) {
                                $results['adults']      += $item_adults * $item_qty;
                                $results['children']    += $item_children * $item_qty;
                                $results['babies']      += $item_babies * $item_qty;
                            }  
                        }
                    }
                }
            }

            return apply_filters( OVABRW_PREFIX.'get_guests_in_order', $results, $product_id, $check_in );
        }

        return false;
    }
}

/**
 * Product get number of guests available
 */
if ( !function_exists( 'ovabrw_get_guests_available' ) ) {
    function ovabrw_get_guests_available( $product_id = null, $guests = [], $guests_cart = [], $guests_order = [], $validate = 'cart' ) {
        if ( !$product_id ) return false;

        $max_adults     = absint( get_post_meta( $product_id, 'ovabrw_adults_max', true ) );
        $max_children   = absint( get_post_meta( $product_id, 'ovabrw_childrens_max', true ) );
        $max_babies     = absint( get_post_meta( $product_id, 'ovabrw_babies_max', true ) );
        $min_adults     = absint( get_post_meta( $product_id, 'ovabrw_adults_min', true ) );
        $min_children   = absint( get_post_meta( $product_id, 'ovabrw_childrens_min', true ) );
        $min_babies     = absint( get_post_meta( $product_id, 'ovabrw_babies_min', true ) );
        $quantity       = absint( get_post_meta( $product_id, 'ovabrw_stock_quantity', true ) );

        $guests_available = [
            'adults'     => $max_adults * $quantity,
            'children'   => $max_children * $quantity,
            'babies'     => $max_babies * $quantity
        ];

        if ( ovabrw_array_exists( $guests_cart ) ) {
            $guests_available['adults']    -= $guests_cart['adults'];
            $guests_available['children']  -= $guests_cart['children'];
            $guests_available['babies']    -= $guests_cart['babies'];
        }

        if ( ovabrw_array_exists( $guests_order ) ) {
            $guests_available['adults']    -= $guests_order['adults'];
            $guests_available['children']  -= $guests_order['children'];
            $guests_available['babies']    -= $guests_order['babies'];
        }

        // Check guests
        if ( isset( $guests['adults'] ) && $guests_available['adults'] < $guests['adults'] ) {
            if ( $validate != 'search' ) {
                wc_clear_notices();

                if ( !$guests_available['adults'] ) {
                    wc_add_notice( esc_html__( 'Sin espacio para adultos', 'ova-brw' ), 'error' );
                } else {
                    wc_add_notice( sprintf( esc_html__( 'Número máximo de adultos: %s', 'ova-brw'  ), $guests_available['adults'] ), 'error' );
                }

                return false;
            }
        }

        if ( isset( $guests['children'] ) && $guests_available['children'] < $guests['children'] ) {
            if ( $validate != 'search' ) {
                wc_clear_notices();

                if ( !$guests_available['children'] ) {
                    wc_add_notice( esc_html__( 'Sin espacio para niños', 'ova-brw' ), 'error' );
                } else {
                    wc_add_notice( sprintf( esc_html__( 'Número máximo de niños: %s', 'ova-brw'  ), $guests_available['children'] ), 'error' );
                }

                return false;
            }
        }

        if ( isset( $guests['babies'] ) && $guests_available['babies'] < $guests['babies'] ) {
            if ( $validate != 'search' ) {
                wc_clear_notices();

                if ( !$guests_available['babies'] ) {
                    wc_add_notice( esc_html__( 'Sin espacio para bebés', 'ova-brw' ), 'error' );
                } else {
                    wc_add_notice( sprintf( esc_html__( 'Número máximo de bebés: %s', 'ova-brw'  ), $guests_available['babies'] ), 'error' );
                }

                return false;
            }
        }

        if ( ( !$guests_available['adults'] || $guests_available['adults'] < 0 ) && ( !$guests_available['children'] || $guests_available['children'] < 0 ) && ( !$guests_available['babies'] || $guests_available['babies'] < 0 ) ) {
            return false;
        }

        if ( apply_filters( 'ovabrw_ft_check_min_guests', true, $product_id ) ) {
            if ( $guests_available['adults'] < $min_adults || $guests_available['children'] < $min_children || $guests_available['babies'] < $min_babies  ) {
                return false;
            }
        }

        return apply_filters( OVABRW_PREFIX.'get_guests_available', $guests_available, $product_id, $guests, $guests_cart, $guests_order, $validate );
    }
}

/**
 * Guests validation
 */
if ( !function_exists( 'ovabrw_guests_validation' ) ) {
    function ovabrw_guests_validation( $product_id, $args = [] ) {
        if ( !$product_id ) {
            $mesg = esc_html__( 'El producto no existe..', 'ova-brw' );
            return apply_filters( 'ovabrw_guests_validation', $mesg, $product_id, $args );
        }

        // Number of guests
        $numberof_guests = 0; // Total number of guests

        // Number of adults
        $numberof_adults = (int)ovabrw_get_meta_data( 'numberof_adults', $args );

        // Minimun number of adults
        $min_adults = (int)get_post_meta( $product_id, 'ovabrw_adults_min', true );

        // Maximum number of adults
        $max_adults = (int)get_post_meta( $product_id, 'ovabrw_adults_max', true );

        // Check minimun number of adults
        if ( $numberof_adults < $min_adults ) {
            $mesg = sprintf( esc_html__( 'Por favor, seleccione el número de adultos mayor de %d', 'ova-brw' ), $min_adults );
            return apply_filters( 'ovabrw_guests_validation', $mesg, $product_id, $args );
        }

        // Check maximum number of adults
        if ( $max_adults && $numberof_adults > $max_adults ) {
            $mesg = sprintf( esc_html__( 'Por favor, seleccione un número de adultos menor o igual a %d', 'ova-brw' ), $max_adults );
            return apply_filters( 'ovabrw_guests_validation', $mesg, $product_id, $args );
        }

        // Number of guests
        $numberof_guests += $numberof_adults;

        // Get guest information data
        $guest_info_enabled = ovabrw_guest_info_enabled();
        if ( $guest_info_enabled && $numberof_adults ) {
            // Get guest data
            $guest_data = ovabrw_get_guest_info_data( 'ovabrw_adults' );
            if ( ovabrw_array_exists( $guest_data ) ) {
                $_POST['ovabrw_guest_info']['adult'] = $guest_data;
            }
        } // END if

        // Number of children
        if ( ovabrw_show_children( $product_id ) ) {
            $numberof_children = (int)ovabrw_get_meta_data( 'numberof_children', $args );

            // Minimum number of children
            $min_children = (int)get_post_meta( $product_id, 'ovabrw_childrens_min', true );

            // Maximum number of children
            $max_children = (int)get_post_meta( $product_id, 'ovabrw_childrens_max', true );

            // Check minimun number of children
            if ( $numberof_children < $min_children ) {
                $mesg = sprintf( esc_html__( 'Por favor, seleccione el número de niños mayor de %d', 'ova-brw' ), $min_children );
                return apply_filters( 'ovabrw_guests_validation', $mesg, $product_id, $args );
            }

            // Check maximum number of children
            if ( $max_children && $numberof_children > $max_children ) {
                $mesg = sprintf( esc_html__( 'Por favor, seleccione el número de niños menor o igual a %d', 'ova-brw' ), $max_children );
                return apply_filters( 'ovabrw_guests_validation', $mesg, $product_id, $args );
            }

            // Number of guests
            $numberof_guests += $numberof_children;

            // Get guest information data
            if ( $guest_info_enabled && $numberof_children ) {
                // Get guest data
                $guest_data = ovabrw_get_guest_info_data( 'ovabrw_childrens' );
                if ( ovabrw_array_exists( $guest_data ) ) {
                    $_POST['ovabrw_guest_info']['child'] = $guest_data;
                }
            } // END if
        }

        // Number of babies
        if ( 'yes' === ovabrw_get_option_setting( 'booking_form_show_baby', 'yes' ) ) {
            $numberof_babies = (int)ovabrw_get_meta_data( 'numberof_babies', $args );

            // Minimum number of babies
            $min_babies = (int)get_post_meta( $product_id, 'ovabrw_babies_min', true );

            // Maximum number of babies
            $max_babies = (int)get_post_meta( $product_id, 'ovabrw_babies_max', true );

            // Check minimun number of babies
            if ( $numberof_babies < $min_babies ) {
                $mesg = sprintf( esc_html__( 'Por favor, elija el número de bebés mayor a %d', 'ova-brw' ), $min_babies );
                return apply_filters( 'ovabrw_guests_validation', $mesg, $product_id, $args );
            }

            // Check maximum number of babies
            if ( $max_babies && $numberof_babies > $max_babies ) {
                $mesg = sprintf( esc_html__( 'Por favor, seleccione el número de bebés menor o igual a %d', 'ova-brw' ), $max_babies );
                return apply_filters( 'ovabrw_guests_validation', $mesg, $product_id, $args );
            }

            // Number of guests
            $numberof_guests += $numberof_babies;

            // Get guest information data
            if ( $guest_info_enabled && $numberof_babies ) {
                // Get guest data
                $guest_data = ovabrw_get_guest_info_data( 'ovabrw_babies' );
                if ( ovabrw_array_exists( $guest_data ) ) {
                    $_POST['ovabrw_guest_info']['baby'] = $guest_data;
                }
            } // END if
        }

        // Minimum number of guests
        $min_guests = (int)get_post_meta( $product_id, 'ovabrw_min_total_guest', true );

        // Maximum number of guests
        $max_guests = (int)get_post_meta( $product_id, 'ovabrw_max_total_guest', true );

        // Check number of guests
        if ( $numberof_guests <= 0 ) {
            $mesg = esc_html__( 'Se requiere el número de invitados.', 'ova-brw' );
            return apply_filters( 'ovabrw_guests_validation', $mesg, $product_id, $args );
        }

        // Check minimum number of guests
        if ( $numberof_guests < $min_guests ) {
            $mesg = sprintf( esc_html__( 'El número total de invitados debe ser mayor que %d', 'ova-brw' ), $min_guests );
            return apply_filters( 'ovabrw_guests_validation', $mesg, $product_id, $args );
        }

        // Check maximum number of guests
        if ( $max_guests && $numberof_guests > $max_guests ) {
            $mesg = sprintf( esc_html__( 'El número total de invitados debe ser menor que %d', 'ova-brw' ), $max_guests );
            return apply_filters( 'ovabrw_guests_validation', $mesg, $product_id, $args );
        }

        return apply_filters( 'ovabrw_guests_validation', false, $product_id, $args );
    }
}