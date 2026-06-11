<?php if ( !defined( 'ABSPATH' ) ) exit();

/**
 * Send mail in Request for booking
 */
if ( !function_exists( 'ovabrw_request_booking' ) ) {
    function ovabrw_request_booking( $data ) {
        // Validation data
        if ( !ovabrw_request_booking_validation( $data ) ) return false;

        // Get subject setting
        $subject = ovabrw_get_option_setting( 'request_booking_mail_subject', esc_html__( 'Request For Booking' ) );
        $subject = apply_filters( 'ovabrw_request_booking_subject', $subject, $data );

        // Get email setting
        $mail_to_setting = ovabrw_get_option_setting( 'request_booking_mail_from_email', get_option( 'admin_email' ) );

        // Mail to
        $mail_to = [];

        // Add $mail_to_settings to $mail_to
        if ( apply_filters( 'ovabrw_request_booking_add_mail_to_settings', true ) ) {
            array_push( $mail_to , $mail_to_setting );
        }
        
        // Add email customer
        array_push( $mail_to, $data['email'] );

        // Emails Cc
        $email_cc = ovabrw_get_option_setting( 'request_booking_mail_cc_email' );
        if ( $email_cc ) {
            $email_cc = explode( '|', $email_cc );
            $email_cc = array_map('trim', $email_cc);

            if ( $email_cc && is_array( $email_cc ) ) {
                $mail_to = array_unique( array_merge ( $mail_to, $email_cc ) );
            }
        }

        // Mail to hook
        $mail_to = apply_filters( 'ovabrw_request_booking_mail_to', $mail_to, $data );

        // Remove duplicate email
        if ( ovabrw_array_exists( $mail_to ) ) $mail_to = array_unique( $mail_to );

        // Body
        $body = '';

        // Product name
        $product_name = sanitize_text_field( ovabrw_get_meta_data( 'product_name', $data ) );

        // Product ID
        $product_id = sanitize_text_field( ovabrw_get_meta_data( 'product_id', $data ) );

        // Time from
        $time_from = sanitize_text_field( ovabrw_get_meta_data( 'ovabrw_time_from', $data ) );

        // Name
        $name = sanitize_text_field( ovabrw_get_meta_data( 'name', $data ) );

        // Email
        $email = sanitize_email( ovabrw_get_meta_data( 'email', $data ) );

        // Phone number
        $number = sanitize_text_field( ovabrw_get_meta_data( 'phone', $data ) );

        // Address
        $address = sanitize_text_field( ovabrw_get_meta_data( 'address', $data ) );

        // Get check-in date
        $pickup_date = sanitize_text_field( ovabrw_get_meta_data( 'ovabrw_request_pickup_date', $data ) );

        // Get check-out date
        $pickoff_date = sanitize_text_field( ovabrw_get_meta_data( 'ovabrw_request_pickoff_date', $data ) );

        // Fixed time
        $fixed_time = sanitize_text_field( ovabrw_get_meta_data( 'ovabrw_fixed_time', $data ) );

        // Convert date
        if ( $fixed_time ) {
            $fixed_time = explode( '|', $fixed_time );

            if ( isset( $fixed_time[0] ) && $fixed_time[0] ) $pickup_date = $fixed_time[0];
            if ( isset( $fixed_time[1] ) && $fixed_time[1] ) $pickoff_date = $fixed_time[1];
        }

        if ( $time_from ) {
            $pickup_date    .= ' ' . $time_from;
            $pickoff_date   = ovabrw_get_checkout_date( $product_id, strtotime( $pickup_date ) );
        }

        // Get number of adults
        $adults = (int)sanitize_text_field( ovabrw_get_meta_data( 'ovabrw_adults', $data, 1 ) );

        // Get number of children
        $children = (int)sanitize_text_field( ovabrw_get_meta_data( 'ovabrw_childrens', $data ) );

        // Get number of babies
        $babies = (int)sanitize_text_field( ovabrw_get_meta_data( 'ovabrw_babies', $data ) );

        // Get quantity
        $quantity = (int)sanitize_text_field( ovabrw_get_meta_data( 'ovabrw_quantity', $data, 1 ) );

        // Get resources
        $resources = ovabrw_get_meta_data( 'ovabrw_rs_checkboxs', $data, [] );

        // Get resource guests
        $resource_guests = ovabrw_get_meta_data( 'ovabrw_resource_guests', $data, [] );

        // Get services
        $services = ovabrw_get_meta_data( 'ovabrw_service', $data, [] );

        // Get service guests
        $service_guests = ovabrw_get_meta_data( 'ovabrw_service_guests', $data, [] );

        // Get extra
        $extra = sanitize_text_field( ovabrw_get_meta_data( 'extra', $data ) );
        
        // Get product service ids
        $service_ids = get_post_meta( $product_id, 'ovabrw_service_id', true );

        // Get product service name
        $service_name = get_post_meta( $product_id, 'ovabrw_service_name', true );

        // Services
        $arr_services = [];
        if ( ovabrw_array_exists( $services ) ) {
            foreach ( $services as $s_id ) {
                // Get option guests
                $opt_guests = ovabrw_get_meta_data( $s_id, $service_guests );

                if ( $s_id && ovabrw_array_exists( $service_ids ) ) {
                    foreach ( $service_ids as $key_id => $service_id_arr ) {
                        $key = array_search( $s_id, $service_id_arr );
                        if ( !is_bool( $key ) ) {
                            $val_ser = isset( $service_name[$key_id][$key] ) ? $service_name[$key_id][$key] : '';
                            if ( $val_ser ) {
                                // Number of guests
                                if ( ovabrw_array_exists( $opt_guests ) ) {
                                    $opt_values = [];

                                    // Get number of adults
                                    $number_adult = ovabrw_get_meta_data( 'adult', $opt_guests );
                                    if ( $number_adult ) {
                                        $opt_values[] = sprintf( esc_html__( 'Adult: %s', 'ova-brw' ), $number_adult );
                                    }

                                    // Get number of children
                                    $number_child = ovabrw_get_meta_data( 'child', $opt_guests );
                                    if ( $number_child ) {
                                        $opt_values[] = sprintf( esc_html__( 'Child: %s', 'ova-brw' ), $number_child );
                                    }

                                    // Get number of baby
                                    $number_baby = ovabrw_get_meta_data( 'baby', $opt_guests );
                                    if ( $number_baby ) {
                                        $opt_values[] = sprintf( esc_html__( 'Baby: %s', 'ova-brw' ), $number_baby );
                                    }

                                    $val_ser = sprintf( esc_html__( '%s (%s)', 'ova-brw' ), $val_ser, implode( ', ', $opt_values ) );
                                } // END

                                array_push( $arr_services, $val_ser );
                            }
                        }
                    }
                }
            }
        }

        // Get order
        $order = '<h2>'.esc_html__( 'Order details: ', 'ova-brw' ).'</h2>';

        // <table>
        $order .= '<table>';

        // Product name
        $order .= $product_id ? '<tr><td>'.esc_html__( 'Tour: ', 'ova-brw' ).'</td><td><a href="'.get_permalink( $product_id ).'">'.$product_name.'</a><td></tr>' : '';

        $order .= $name ? '<tr><td>'.esc_html__( 'Name: ', 'ova-brw' ).'</td><td>'.$name.'</td></tr>' : '';
        $order .= $email ? '<tr><td>'.esc_html__( 'Email: ', 'ova-brw' ).'</td><td>'.$email.'</td></tr>' : '';

        if ( ovabrw_get_option_setting( 'request_booking_form_show_number', 'yes' ) == 'yes' ) {
            $order .= $number ? '<tr><td>'. esc_html__( 'Phone: ', 'ova-brw' ).'</td><td>'.$number.'</td></tr>' : '';
        }

        if ( ovabrw_get_option_setting( 'request_booking_form_show_address', 'yes' ) == 'yes' ) {
            $order .= $address ? '<tr><td>'.esc_html__( 'Address: ', 'ova-brw' ).'</td><td>'.$address.'</td></tr>' : '';
        }

        $order .= $pickup_date ? '<tr><td>'.esc_html__( 'Check-in: ', 'ova-brw' ).'</td><td>'.$pickup_date.'</td></tr>' : '';

        // Show check-out date
        $show_checkout = ovabrw_get_post_meta( $product_id, 'manage_checkout_field', 'global' );
        if ( 'global' === $show_checkout ) {
            $show_checkout = 'yes' === ovabrw_get_option_setting( 'request_booking_form_show_dates', 'yes' ) ? true : false;
        } elseif ( 'show' === $show_checkout ) {
            $show_checkout = true;
        } else {
            $show_checkout = false;
        }
        if ( $show_checkout ) {
            $order .= $pickoff_date ? '<tr><td>'.esc_html__( 'Check-out: ', 'ova-brw' ).'</td><td>'.$pickoff_date.'</td></tr>' : '';
        }

        if ( ovabrw_get_option_setting( 'request_booking_form_show_guests', 'yes' ) == 'yes' ) {
            $order .= $adults ? '<tr><td>'. esc_html__( 'Adults: ', 'ova-brw' ).'</td><td>'.$adults.'</td></tr>' : '';
            $order .= $children ? '<tr><td>'.esc_html__( 'Children: ', 'ova-brw' ).'</td><td>'.$children.'</td></tr>' : '';
            $order .= $babies ? '<tr><td>'.esc_html__( 'Babies: ', 'ova-brw' ).'</td><td>'.$babies.'</td></tr>' : '';
        }

        // Custom Checkout Fields
        $cckf               = ovabrw_get_list_field_checkout( $product_id );
        $custom_ckf         = [];
        $custom_ckf_save    = [];
        $cckf_qty           = [];

        if ( ovabrw_array_exists( $cckf ) ) {
            foreach ( $cckf as $key => $field ) {
                // Enabled
                $enabled = ovabrw_get_meta_data( 'enabled', $field );
                if ( 'on' !== $enabled ) continue;

                // Get type
                $type = ovabrw_get_meta_data( 'type', $field );

                if ( 'file' === $type ) {
                    // Get fiels
                    $files = ovabrw_get_meta_data( $key, $_FILES );
                    if ( ovabrw_array_exists( $files ) ) {
                        // Get max size
                        $max_size = (float)ovabrw_get_meta_data( 'max_file_size', $field );

                        // Get file size
                        $file_size = (int)ovabrw_get_meta_data( 'size', $files );
                        if ( $file_size ) {
                            $mb = absint( $file_size ) / 1048576;

                            if ( $mb > $max_size ) continue;
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

                        $upload = wp_handle_upload( $files, $overrides );

                        if ( isset( $upload['error'] ) ) {
                            continue;
                        }
                        
                        $order .= '<tr>
                                    <td>'.sprintf( '%s: ', esc_html( $field['label'] ) ).'</td>
                                    <td><a href="'.esc_url( $upload['url'] ).'" title="'.esc_attr( basename( $upload['file'] ) ).'" target="_blank">'.esc_attr( basename( $upload['file'] ) ).'</a></td>
                                </tr>';
                        $custom_ckf_save[$key] = '<a href="'.esc_url( $upload['url'] ).'" title="'.esc_attr( basename( $upload['file'] ) ).'" target="_blank">'.esc_attr( basename( $upload['file'] ) ).'</a>';
                    }
                } else {
                    // Get value
                    $value = ovabrw_get_meta_data( $key, $data );
                    if ( empty( $value ) ) continue;

                    if ( 'select' === $type ) {
                        // Value
                        $value = sanitize_text_field( $value );

                        // Add cckf
                        $custom_ckf[$key] = $value;

                        // Get option key
                        $options_key = ovabrw_get_meta_data( 'ova_options_key', $field );

                        // Get option text
                        $options_text = ovabrw_get_meta_data( 'ova_options_text', $field );

                        // Get option qtys
                        $opt_qtys = ovabrw_get_meta_data( $key.'_qty', $data );

                        // Get option qty
                        $opt_qty = (int)ovabrw_get_meta_data( $value, $opt_qtys );
                        if ( $opt_qty ) $cckf_qty[$key] = $opt_qty;

                        // Search key index
                        $key_op = array_search( $value, $options_key );
                        if ( !is_bool( $key_op ) ) {
                            // Get option text
                            $opt_text = ovabrw_get_meta_data( $key_op, $options_text );
                            if ( $opt_text ) $value = $opt_text;

                            // Qty
                            if ( $opt_qty > 1 ) {
                                $value = sprintf( esc_html__( '%s (x%d)', 'ova-brw' ), $value, $opt_qty );
                            }
                        }
                    } elseif ( 'checkbox' === $type ) {
                        if ( !ovabrw_array_exists( $value ) ) continue;

                        // init values
                        $checkbox_val = [];

                        // Add cckf
                        $custom_ckf[$key] = $value;

                        // Get checkbox key
                        $checkbox_key = ovabrw_get_meta_data( 'ova_checkbox_key', $field );

                        // Get checkbox text
                        $checkbox_text = ovabrw_get_meta_data( 'ova_checkbox_text', $field );

                        // Get checkbox quantities
                        $opt_qtys = ovabrw_get_meta_data( $key.'_qty', $data );
                        if ( ovabrw_array_exists( $opt_qtys ) ) $cckf_qty[$key] = $opt_qtys;

                        foreach ( $value as $val_cb ) {
                            // Get key index
                            $key_cb = array_search( $val_cb, $checkbox_key );

                            // Get option qty
                            $opt_qty = (int)ovabrw_get_meta_data( $val_cb, $opt_qtys );

                            if ( !is_bool( $key_cb ) ) {
                                // Get option text
                                $opt_text = ovabrw_get_meta_data( $key_cb, $checkbox_text );
                                if ( $opt_text ) {
                                    // Qty
                                    if ( $opt_qty > 1 ) {
                                        $opt_text = sprintf( esc_html__( '%s (x%d)', 'ova-brw' ), $opt_text, $opt_qty );
                                    }

                                    array_push( $checkbox_val , $opt_text );
                                }
                            }
                        }

                        if ( ovabrw_array_exists( $checkbox_val ) ) {
                            $value = implode( ', ', $checkbox_val );
                        }
                    } elseif ( 'radio' === $type ) {
                        // Value
                        $value = sanitize_text_field( $value );

                        // Add cckf
                        $custom_ckf[$key] = $value;

                        // Get option quantities
                        $opt_qtys = ovabrw_get_meta_data( $key.'_qty', $data );

                        // Get option qty
                        $opt_qty = (int)ovabrw_get_meta_data( $value, $opt_qtys );
                        if ( $opt_qty ) {
                            $cckf_qty[$key] = $opt_qty;

                            if ( $opt_qty > 1 ) {
                                $value = sprintf( esc_html__( '%s (x%d)', 'ova-brw' ), $value, $opt_qty );
                            }
                        }
                    }

                    $order .= '<tr><td>'.sprintf( '%s: ', esc_html( $field['label'] ) ).'</td><td>'.esc_html( stripslashes( $value ) ).'</td></tr>';

                    $custom_ckf_save[$key] = $value;
                }
            }
        }

        $data['custom_ckf']         = $custom_ckf;
        $data['cckf_qty']           = $cckf_qty;
        $data['custom_ckf_save']    = $custom_ckf_save;

        if ( ovabrw_get_option_setting( 'booking_form_show_quantity', 'no' ) == 'yes' ) {
            $order .= $quantity ? '<tr><td>'.esc_html__( 'Quantity: ', 'ova-brw' ).'</td><td>'.$quantity.'</td></tr>' : '';
        }
        
        if ( ovabrw_get_option_setting( 'request_booking_form_show_extra_service', 'yes' ) == 'yes' ) {
            if ( ovabrw_array_exists( $resources ) ) {
                if ( 1 == count( $resources ) ) {
                    $order .= '<tr><td>'. esc_html__( 'Resource: ', 'ova-brw' );
                } else {
                    $order .= '<tr><td>'. esc_html__( 'Resources: ', 'ova-brw' );
                }

                // Get resource value
                $resc_values = [];
                foreach ( $resources as $opt_id => $opt_value ) {
                    // Get resource option guests
                    $opt_guests = ovabrw_get_meta_data( $opt_id, $resource_guests );

                    if ( ovabrw_array_exists( $opt_guests ) ) {
                        // init option values
                        $opt_values = [];

                        // Get number of adults
                        $number_adult = (int)ovabrw_get_meta_data( 'adult', $opt_guests );
                        if ( $number_adult ) {
                            $opt_values[] = sprintf( esc_html__( 'Adult: %s', 'ova-brw' ), $number_adult );
                        }

                        // Get number of children
                        $number_child = (int)ovabrw_get_meta_data( 'child', $opt_guests );
                        if ( $number_child ) {
                            $opt_values[] = sprintf( esc_html__( 'Child: %s', 'ova-brw' ), $number_child );
                        }

                        // Get number of baby
                        $number_baby = (int)ovabrw_get_meta_data( 'baby', $opt_guests );
                        if ( $number_baby ) {
                            $opt_values[] = sprintf( esc_html__( 'Baby: %s', 'ova-brw' ), $number_baby );
                        }

                        $resc_values[] = sprintf( esc_html__( '%s (%s)', 'ova-brw' ), $opt_value, implode( ', ', $opt_values ) );
                    } else {
                        $resc_values[] = $opt_value;
                    }
                }

                $order .= '</td><td>' . implode(', ', $resc_values) . '</td></tr>';
            }
        } // END

        if ( ovabrw_get_option_setting( 'request_booking_form_show_service', 'yes' ) === 'yes' ) {
            if ( ovabrw_array_exists( $arr_services ) ) {
                if ( 1 == count( $arr_services ) ) {
                    $order .= '<tr><td>'.esc_html__( 'Service: ', 'ova-brw' );
                } else {
                    $order .= '<tr><td>'.esc_html__( 'Services: ', 'ova-brw' );
                }
                
                $order .= '</td><td>' . implode(', ', $arr_services) . '</td></tr>';
            }
        }
        
        if ( ovabrw_get_option_setting( 'request_booking_form_show_extra_info', 'yes' ) == 'yes' ) {
            $order .= $extra ? '<tr><td>'.esc_html__( 'Extra: ', 'ova-brw' ).'</td><td>'.stripslashes($extra).'</td></tr>' : '';
        }

        // Line Total
        $line_total = get_price_by_guests( $product_id, strtotime( $pickup_date ), strtotime( $pickoff_date ), [
            'product_id'                => $product_id,
            'ovabrw_adults'             => $adults,
            'ovabrw_childrens'          => $children,
            'ovabrw_babies'             => $babies,
            'ovabrw_quantity'           => $quantity,
            'custom_ckf'                => $custom_ckf,
            'cckf_qty'                  => $cckf_qty,
            'ovabrw_resources'          => $resources,
            'ovabrw_resource_guests'    => $resource_guests,
            'ovabrw_services'           => $services,
            'ovabrw_service_guests'     => $service_guests,
            'ovabrw_time_from'          => $time_from
        ]);

        // Multiple Currency
        $line_total = ovabrw_convert_price( $line_total );

        // Insurance amount
        $insurance_amount = floatval( get_post_meta( $product_id, 'ovabrw_amount_insurance', true ) );
        $insurance_amount = $insurance_amount * ( $adults + $children + $babies );
        if ( $insurance_amount ) {
            // Multiple Currency
            $insurance_amount = ovabrw_convert_price( $insurance_amount );

            // Update line total
            $line_total += $insurance_amount;
            $order .= '<tr><td>'.esc_html__( 'Order total: ', 'ova-brw' ).'</td><td><strong>'.wc_price( $line_total ).'</strong>'. sprintf( esc_html__( ' (includes %s insurance)', 'ova-brw' ), wc_price( $insurance_amount ) ) .'</td></tr>';
        } else {
            $order .= '<tr><td>'.esc_html__( 'Order total: ', 'ova-brw' ).'</td><td><strong>'.wc_price( $line_total ).'</strong></td></tr>';
        }
        
        // </table>
        $order .= '</table>';

        // Get Email Content
        $body = ovabrw_get_option_setting( 'request_booking_mail_content', esc_html__( 'You booked the tour: [product-name] from [check-in] to [check-out]. [order_details]', 'ova-brw' ) );

        if ( empty( $body ) ) {
            $body = esc_html__( 'You booked the tour: [product-name] from [check-in] to [check-out]. [order_details]', 'ova-brw' );
        }

        $body = str_replace('[br]', '<br/>', $body);
        $body = str_replace('[product-name]', '<a href="'.get_permalink($product_id).'" target="_blank">'.$product_name.'</a>', $body);

        // Replace body
        $body = str_replace('[check-in]', $pickup_date, $body);
        $body = str_replace('[check-out]', $pickoff_date, $body);
        $body = str_replace('[order_details]', $order, $body);
        $body = apply_filters( 'ovabrw_request_booking_content_mail', $body, $data );

        // Create Order
        if ( 'yes' === ovabrw_get_option_setting( 'request_booking_create_order', 'no' ) ) {
            $order_id = ovabrw_request_booking_create_new_order( $data );
        }

        // Before send email
        do_action( 'ovabrw_request_booking_before_send_mail', $mail_to, $subject, $body, $data );

        return ovabrw_sendmail( $mail_to, $subject, $body );
    }
}

/**
 * Mail from
 */
if ( !function_exists( 'ova_wp_mail_from' ) ) {
    function ova_wp_mail_from() {
        $mail_from = ovabrw_get_option_setting( 'request_booking_mail_from_email', get_option( 'admin_email' ) );

        return apply_filters( OVABRW_PREFIX.'request_booking_mail_from', $mail_from, $_REQUEST );
    }
}

/**
 * Mail from name
 */
if ( !function_exists( 'ova_wp_mail_from_name' ) ) {
    function ova_wp_mail_from_name() {
        $mail_from_name = ovabrw_get_option_setting( 'request_booking_mail_from_name', esc_html__( 'Request For Booking', 'ova-brw' ) );

        if ( !$mail_from_name ) {
            $mail_from_name = esc_html__( 'Request For Booking', 'ova-brw' );
        }

        return apply_filters( OVABRW_PREFIX.'request_booking_mail_from_name', $mail_from_name, $_REQUEST );
    }
}

/**
 * Send mail
 */
if ( !function_exists( 'ovabrw_sendmail' ) ) {
    function ovabrw_sendmail( $mail_to, $subject, $body ) {
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=".get_bloginfo( 'charset' )."\r\n";
        
        add_filter( 'wp_mail_from', 'ova_wp_mail_from' );
        add_filter( 'wp_mail_from_name', 'ova_wp_mail_from_name' );

        if ( wp_mail( $mail_to, $subject, $body, $headers ) ) {
            $result = true;
        } else {
            $result = false;
        }

        remove_filter( 'wp_mail_from', 'ova_wp_mail_from');
        remove_filter( 'wp_mail_from_name', 'ova_wp_mail_from_name' );

        return apply_filters( OVABRW_PREFIX.'request_booking_sendmail', $result );
    }
}

/**
 * Request for Booking create new order
 */
if ( !function_exists( 'ovabrw_request_booking_create_new_order' ) ) {
    function ovabrw_request_booking_create_new_order( $data ) {
        $product_id = (int)ovabrw_get_meta_data( 'product_id', $data );
        if ( !$product_id ) return false;

        // Get time from
        $time_from = sanitize_text_field( ovabrw_get_meta_data( 'ovabrw_time_from', $data ) );

        // Get customer name
        $name = sanitize_text_field( ovabrw_get_meta_data( 'name', $data ) );

        // Get customer email
        $email = sanitize_text_field( ovabrw_get_meta_data( 'email', $data ) );

        // Get phone number
        $phone = sanitize_text_field( ovabrw_get_meta_data( 'phone', $data ) );

        // Get customer address
        $address = sanitize_text_field( ovabrw_get_meta_data( 'address', $data ) );

        // Get pick-up date
        $pickup_date = sanitize_text_field( ovabrw_get_meta_data( 'ovabrw_request_pickup_date', $data ) );

        // Get drop-off date
        $pickoff_date = sanitize_text_field( ovabrw_get_meta_data( 'ovabrw_request_pickoff_date', $data ) );

        // Fixed time
        $fixed_time = sanitize_text_field( ovabrw_get_meta_data( 'ovabrw_fixed_time', $data ) );

        // Convert date
        if ( $fixed_time ) {
            $fixed_time = explode( '|', $fixed_time );

            if ( isset( $fixed_time[0] ) && $fixed_time[0] ) $pickup_date = $fixed_time[0];
            if ( isset( $fixed_time[1] ) && $fixed_time[1] ) $pickoff_date = $fixed_time[1];
        }

        if ( $time_from ) {
            $pickup_date    .= ' ' . $time_from;
            $pickoff_date   = ovabrw_get_checkout_date( $product_id, strtotime( $pickup_date ) );
        }

        // Get number of adults
        $adults = (int)ovabrw_get_meta_data( 'ovabrw_adults', $data, 1 );

        // Get number of children
        $children = (int)ovabrw_get_meta_data( 'ovabrw_childrens', $data );

        // Get number of babies
        $babies = (int)ovabrw_get_meta_data( 'ovabrw_babies', $data );

        // Get cckf
        $custom_ckf = ovabrw_get_meta_data( 'custom_ckf', $data, [] );

        // Get cckf quantity
        $cckf_qty = ovabrw_get_meta_data( 'cckf_qty', $data, [] );

        // Get quantity
        $quantity = (int)ovabrw_get_meta_data( 'ovabrw_quantity', $data, 1 );

        // Get resources
        $resources = ovabrw_get_meta_data( 'ovabrw_rs_checkboxs', $data, [] );

        // Get resource guests
        $resource_guests = ovabrw_get_meta_data( 'ovabrw_resource_guests', $data, [] );

        // Get services
        $services = ovabrw_get_meta_data( 'ovabrw_service', $data, [] );

        // Get service guests
        $service_guests = ovabrw_get_meta_data( 'ovabrw_service_guests', $data, [] );

        // Get extra
        $extra = ovabrw_get_meta_data( 'extra', $data );

        // Cart item
        $cart_item = [
            'product_id'                => $product_id,
            'ovabrw_adults'             => $adults,
            'ovabrw_childrens'          => $children,
            'ovabrw_babies'             => $babies,
            'ovabrw_quantity'           => $quantity,
            'custom_ckf'                => $custom_ckf,
            'cckf_qty'                  => $cckf_qty,
            'ovabrw_resources'          => $resources,
            'ovabrw_resource_guests'    => $resource_guests,
            'ovabrw_services'           => $services,
            'ovabrw_service_guests'     => $service_guests,
            'ovabrw_time_from'          => $time_from
        ];

        // Check-out
        if ( !$pickoff_date ) {
            $pickoff_date = ovabrw_get_checkout_date( $product_id, strtotime( $pickup_date ) );
        }

        // Insurance amount
        $insurance_amount = floatval( get_post_meta( $product_id, 'ovabrw_amount_insurance', true ) );
        $insurance_amount = $insurance_amount * ( $adults + $children + $babies );

        // Line Total
        $line_total = get_price_by_guests( $product_id, strtotime( $pickup_date ), strtotime( $pickoff_date ), $cart_item );

        // Multiple Currency
        $insurance_amount   = ovabrw_convert_price( $insurance_amount );
        $line_total         = ovabrw_convert_price( $line_total );

        // Billing
        $order_address = [
            'billing' => [
                'first_name' => $name,
                'last_name'  => '',
                'company'    => '',
                'email'      => $email,
                'phone'      => $phone,
                'address_1'  => $address,
                'address_2'  => '',
                'city'       => '',
                'country'    => ''
            ],
            'shipping' => []
        ];
        
        // Create order
        $order = wc_create_order([
            'status'        => '',
            'customer_note' => $extra,
        ]);

        // Get order id
        $order_id = $order->get_id();

        // Get product
        $product = wc_get_product( $product_id );

        // Tax
        if ( wc_tax_enabled() && wc_prices_include_tax() ) {
            $tax_rates  = WC_Tax::get_rates( $product->get_tax_class() );
            $incl_tax   = WC_Tax::calc_inclusive_tax( $line_total, $tax_rates );
            $line_total -= round( array_sum( $incl_tax ), wc_get_price_decimals() ); 
        }

        // Handle items
        $item_id = $order->add_product( $product, $quantity, [ 'total' => $line_total ] );

        // Get order line item
        $line_item = $order->get_item( $item_id );

        // Loop order items
        if ( $line_item ) {
            // Date item
            $data_item = [
                'ovabrw_pickup_date'    => $pickup_date,
                'ovabrw_pickoff_date'   => $pickoff_date,
                'ovabrw_adults'         => $adults,
                'ovabrw_childrens'      => $children,
                'ovabrw_babies'         => $babies,
                'ovabrw_quantity'       => $quantity
            ];

            // Insurance amount
            if ( $insurance_amount ) {
                $data_item['ovabrw_insurance_amount'] = $insurance_amount;
            }

            // Custom Checkout Fields
            if ( ovabrw_array_exists( $custom_ckf ) ) {
                $data_item['ovabrw_custom_ckf'] = $custom_ckf;

                // CCKF quantity
                if ( ovabrw_array_exists( $cckf_qty ) ) {
                    $data_item['ovabrw_cckf_qty'] = $cckf_qty;
                }
            }
            if ( ovabrw_get_meta_data( 'custom_ckf_save', $data ) ) {
                foreach ( $data['custom_ckf_save'] as $k => $val ) {
                    $data_item[$k] = $val;
                }
            }

            // Resources
            if ( ovabrw_array_exists( $resources ) ) {
                $data_item['ovabrw_resources'] = $resources;

                // Save resource guests
                if ( ovabrw_array_exists( $resource_guests ) ) {
                    $data_item['ovabrw_resource_guests'] = $resource_guests;
                }

                // init resource name
                $res_name = [];

                // Loop
                foreach ( $resources as $res_id => $val ) {
                    // Get resource option guests
                    $opt_guests = ovabrw_get_meta_data( $res_id, $resource_guests );

                    if ( ovabrw_array_exists( $opt_guests ) ) {
                        // init option values
                        $opt_values = [];

                        // Get number of adults
                        $number_adult = (int)ovabrw_get_meta_data( 'adult', $opt_guests );
                        if ( $number_adult ) {
                            $opt_values[] = sprintf( esc_html__( 'Adult: %s', 'ova-brw' ), $number_adult );
                        }

                        // Get number of children
                        $number_child = (int)ovabrw_get_meta_data( 'child', $opt_guests );
                        if ( $number_child ) {
                            $opt_values[] = sprintf( esc_html__( 'Child: %s', 'ova-brw' ), $number_child );
                        }

                        // Get number of baby
                        $number_baby = (int)ovabrw_get_meta_data( 'baby', $opt_guests );
                        if ( $number_baby ) {
                            $opt_values[] = sprintf( esc_html__( 'Baby: %s', 'ova-brw' ), $number_baby );
                        }

                        $res_name[] = sprintf( esc_html__( '%s (%s)', 'ova-brw' ), $val, implode( ', ', $opt_values ) );
                    } else {
                        $res_name[] = $val;
                    }
                } // END loop
                
                if ( count( $res_name ) == 1 ) {
                    $data_item[esc_html__( 'Resource', 'ova-brw' )] = implode( ', ', $res_name );
                } else {
                    $data_item[esc_html__( 'Resources', 'ova-brw' )] = implode( ', ', $res_name );
                }
            }

            // Services
            if ( ovabrw_array_exists( $services ) ) {
                $data_item['ovabrw_services'] = $services;

                // Save service guests
                if ( ovabrw_array_exists( $service_guests ) ) {
                    $data_item['ovabrw_service_guests'] = $service_guests;
                }

                $services_id      = get_post_meta( $product_id, 'ovabrw_service_id', true ); 
                $services_name    = get_post_meta( $product_id, 'ovabrw_service_name', true );
                $services_label   = get_post_meta( $product_id, 'ovabrw_label_service', true ); 

                foreach ( $services as $val_ser ) {
                    // Get service option guests
                    $opt_guests = ovabrw_get_meta_data( $val_ser, $service_guests );

                    if ( ovabrw_array_exists( $services_id ) ) {
                        foreach ( $services_id as $key => $value ) {
                            if ( ovabrw_array_exists( $value ) ) {
                                foreach ( $value as $k => $val ) {
                                    if ( $val_ser == $val && !empty( $val ) ) {
                                        $service_name   = $services_name[$key][$k];
                                        $service_label  = $services_label[$key];

                                        // Number of guests
                                        if ( ovabrw_array_exists( $opt_guests ) ) {
                                            $opt_values = [];

                                            // Get number of adults
                                            $number_adult = ovabrw_get_meta_data( 'adult', $opt_guests );
                                            if ( $number_adult ) {
                                                $opt_values[] = sprintf( esc_html__( 'Adult: %s', 'ova-brw' ), $number_adult );
                                            }

                                            // Get number of children
                                            $number_child = ovabrw_get_meta_data( 'child', $opt_guests );
                                            if ( $number_child ) {
                                                $opt_values[] = sprintf( esc_html__( 'Child: %s', 'ova-brw' ), $number_child );
                                            }

                                            // Get number of baby
                                            $number_baby = ovabrw_get_meta_data( 'baby', $opt_guests );
                                            if ( $number_baby ) {
                                                $opt_values[] = sprintf( esc_html__( 'Baby: %s', 'ova-brw' ), $number_baby );
                                            }

                                            $service_name = sprintf( esc_html__( '%s (%s)', 'ova-brw' ), $service_name, implode( ', ', $opt_values ) );
                                        }

                                        // Add item data
                                        $data_item[$service_label] = $service_name;
                                    }
                                }
                            }
                        }
                    }
                }
            } // END loop

            // Add item
            foreach ( $data_item as $meta_key => $meta_value ) {
                $line_item->add_meta_data( $meta_key, $meta_value, true );
            }

            // Update item meta
            $line_item->set_props([
                'total'     => $line_total,
                'subtotal'  => $line_total
            ]);

            // Set quantity
            $line_item->set_quantity( $quantity );

            // Save item
            $line_item->save();
        }

        // Insurace
        if ( $insurance_amount ) {
            $order->add_meta_data( '_ova_insurance_amount', $insurance_amount, true );

            // Get insurance name
            $insurance_name = ovabrw_get_insurance_fee_name();

            // Add item fee
            $item_fee = new WC_Order_Item_Fee();
            $item_fee->set_props([
                'name'      => $insurance_name,
                'tax_class' => 0,
                'total'     => $insurance_amount,
                'order_id'  => $order_id
            ]);

            $item_fee->save();

            $order->add_item( $item_fee );

            $order->add_meta_data( '_ova_insurance_key', sanitize_title( $insurance_name ), true );
        }

        $order->set_address( $order_address['billing'], 'billing' );

        // Set customer
        $user = get_user_by( 'email', $data['email'] );
        if ( $user ) $order->set_customer_id( $user->ID );

        // Set date created
        $order->set_date_created( date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ) );

        // Set Tax
        $order->calculate_totals( wc_tax_enabled() );

        // Set Order Status
        $order_status = ovabrw_get_option_setting( 'request_booking_order_status', 'wc-on-hold' );
        $order->set_status( $order_status );

        // Save
        $order->save();

        return apply_filters( OVABRW_PREFIX.'request_booking_create_new_order', $order_id, $data );
    }
}

/**
 * Validation request form
 */
if ( !function_exists( 'ovabrw_request_booking_validation' ) ) {
    function ovabrw_request_booking_validation( $data ) {
        $passed = true;

        // Product ID
        $product_id = sanitize_text_field( ovabrw_get_meta_data( 'product_id', $data ) );

        // Customer name
        $name = sanitize_text_field( ovabrw_get_meta_data( 'name', $data ) );
        if ( strlen( $name ) < 2 || strlen( $name ) > 50 ) return false;

        // Customer email
        $email = sanitize_email( ovabrw_get_meta_data( 'email', $data ) );
        if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) return false;

        // Customer phone
        if ( 'yes' === ovabrw_get_option_setting( 'request_booking_form_show_number', 'yes' ) ) {
            $phone = sanitize_text_field( ovabrw_get_meta_data( 'phone', $data ) );
            if ( $phone && ! preg_match( "/^[0-9\s\-()+]+$/", $phone ) ) return false;
        }

        // Customer address
        if ( 'yes' === ovabrw_get_option_setting( 'request_booking_form_show_address', 'yes' ) ) {
            $address = sanitize_text_field( ovabrw_get_meta_data( 'address', $data ) );
            if ( !$address ) return false;
        }

        // Check-in date
        $checkin_date = sanitize_text_field( ovabrw_get_meta_data( 'ovabrw_request_pickup_date', $data ) );

        // Time from
        $time_from = sanitize_text_field( ovabrw_get_meta_data( 'ovabrw_time_from', $data ) );

        // Check-out date
        $checkout_date = sanitize_text_field( ovabrw_get_meta_data( 'ovabrw_request_pickoff_date', $data ) );

        // Fixed time
        $fixed_time = sanitize_text_field( ovabrw_get_meta_data( 'ovabrw_fixed_time', $data ) );

        // Convert date
        if ( $fixed_time ) {
            $fixed_time = explode( '|', $fixed_time );

            if ( isset( $fixed_time[0] ) && $fixed_time[0] ) $checkin_date = $fixed_time[0];
            if ( isset( $fixed_time[1] ) && $fixed_time[1] ) $checkout_date = $fixed_time[1];
        }

        // Time slots
        if ( $time_from ) {
            $checkin_date   = strtotime( $checkin_date . ' ' . $time_from );
            $checkout_date  = ovabrw_get_checkout_date( $product_id, $checkin_date );
        } else {
            $checkin_date = strtotime( $checkin_date );
        }
        
        // Validation date
        if ( !$checkin_date ) return false;
        if ( $checkout_date ) $checkout_date = strtotime( $checkout_date );
        if ( !$checkout_date ) return false;

        // Number of adults
        $numberof_adults = (int)sanitize_text_field( ovabrw_get_meta_data( 'ovabrw_adults', $data, 1 ) );
        if ( $numberof_adults && !is_numeric( $numberof_adults ) ) return false;

        // Number of children
        $numberof_children = (int)sanitize_text_field( ovabrw_get_meta_data( 'ovabrw_childrens', $data ) );
        if ( $numberof_children && !is_numeric( $numberof_children ) ) return false;

        // Number of babies
        $numberof_babies = (int)sanitize_text_field( ovabrw_get_meta_data( 'ovabrw_babies', $data ) );
        if ( $numberof_babies && !is_numeric( $numberof_babies ) ) return false;

        // Quantity
        $quantity = (int)sanitize_text_field( ovabrw_get_meta_data( 'ovabrw_quantity', $data, 1 ) );
        if ( $quantity && !is_numeric( $quantity ) ) return false;

        return apply_filters( OVABRW_PREFIX.'request_booking_validation', $passed, $data );
    }
}