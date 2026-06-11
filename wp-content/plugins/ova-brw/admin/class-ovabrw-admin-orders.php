<?php if ( !defined( 'ABSPATH' ) ) exit();

/**
 * Class OVABRW_Admin_Orders
 */
if ( !class_exists( 'OVABRW_Admin_Orders' ) ) {

    class OVABRW_Admin_Orders {

        /**
         * Constructor
         */
        public function __construct() {
            add_action( 'admin_init', [ $this, 'ovabrw_create_new_order_manully' ] );
        }

        /**
         * Create new order manully
         */
        public function ovabrw_create_new_order_manully() {
            if ( 'create_order' === ovabrw_get_meta_data( 'ovabrw_create_order', $_POST ) ) {
                // Check Permission
                if ( !current_user_can( apply_filters( OVABRW_PREFIX.'create_order' ,'publish_posts' ) ) ) {
                    echo '<div class="notice notice-error is-dismissible">
                            <h2>'.esc_html__( 'You don\'t have permission to create order', 'ova-brw' ).'</h2>
                        </div>';
                    return;
                }

                $order      = wc_create_order(); // Create new order
                $order_id   = $order->get_id(); // Get order id
                $order_meta = []; // Order meta boxes

                // Get array product ids
                $product_ids = ovabrw_get_meta_data( 'ovabrw-data-product', $_POST, [] );

                // Get currency
                $currency = ovabrw_get_meta_data( 'currency', $_POST );
                if ( $currency ) $order->set_currency( $currency );

                // Check order deposit
                $has_deposit = false;

                // Loop
                if ( ovabrw_array_exists( $product_ids ) ) {
                    foreach ( $product_ids as $key => $product_id ) {
                        // Get product ID
                        $product_id = trim( sanitize_text_field( $product_id ) );

                        // Get product
                        $product = wc_get_product( $product_id );

                        // Item deposit
                        $item_deposit = isset( $_POST['ovabrw_amount_deposite'][$key] ) ? floatval( $_POST['ovabrw_amount_deposite'][$key] ) : 0;
                        if ( $item_deposit > 0 ) {
                            $has_deposit = true;
                        }

                        // Add product
                        $order->add_product( $product, 1 );
                    }
                } // END loop

                // Order item
                $order_data = $this->ovabrw_add_order_item( $order_id );

                // Get data total
                $order_total = (float)ovabrw_get_meta_data( 'order_total', $order_data );

                // Taxable
                $tax_rate_id    = (int)ovabrw_get_meta_data( 'tax_rate_id', $order_data );
                $tax_amount     = (float)ovabrw_get_meta_data( 'tax_amount', $order_data );
                
                // Deposit
                if ( $has_deposit ) {
                    // Has Deposit
                    $order_meta['_ova_has_deposit'] = 1;

                    // Deposit amount
                    $order_meta['_ova_deposit_amount'] = (float)ovabrw_get_meta_data( 'total_deposit', $order_data );

                    // Remaining amount
                    $order_meta['_ova_remaining_amount'] = (float)ovabrw_get_meta_data( 'total_remaining', $order_data );

                    if ( (float)ovabrw_get_meta_data( 'remaining_tax', $order_data ) ) {
                        $order_meta['_ova_remaining_tax'] = (float)ovabrw_get_meta_data( 'remaining_tax', $order_data );
                    }
                }

                // Insurance
                $total_insurance = (float)ovabrw_get_meta_data( 'total_insurance', $order_data );
                if ( $total_insurance ) {
                    $insurance_tax  = (float)ovabrw_get_meta_data( 'insurance_tax', $order_data );
                    $insurance_name = ovabrw_get_insurance_fee_name();
                    $order_meta['_ova_insurance_amount'] = $total_insurance;

                    if ( $insurance_tax ) {
                        $order_total    += $insurance_tax;
                        $tax_amount     += $insurance_tax;
                        
                        $order_meta['_ova_insurance_tax'] = $insurance_tax;
                    }

                    $item_fee = new WC_Order_Item_Fee();
                    $item_fee->set_props([
                        'name'      => $insurance_name,
                        'tax_class' => 0,
                        'amount'    => $total_insurance,
                        'total'     => $total_insurance,
                        'total_tax' => $insurance_tax,
                        'taxes'     => [
                            'total' => [ $tax_rate_id => $insurance_tax ],
                        ],
                        'order_id'  => $order_id
                    ]);

                    $item_fee->save();

                    $order->add_item( $item_fee );

                    $order_meta['_ova_insurance_key'] = sanitize_title( $insurance_name );
                    $order_total += $order_data['total_insurance'];
                }

                foreach ( $order_meta as $key => $update ) {
                    $order->update_meta_data( $key, $update );
                }

                // Set customer
                $email = sanitize_text_field( ovabrw_get_meta_data( 'ovabrw_email', $_POST ) );
                $user = get_user_by( 'email', $email );
                if ( $user ) $order->set_customer_id( $user->ID );

                // Set billing address
                $order->set_address( $this->ovabrw_get_address(), 'billing' );

                // Taxable
                if ( wc_tax_enabled() ) {
                    $item_tax = new WC_Order_Item_Tax();
                    $item_tax->set_props([
                        'rate_id'            => $tax_rate_id,
                        'tax_total'          => $tax_amount,
                        'shipping_tax_total' => 0,
                        'rate_code'          => WC_Tax::get_rate_code( $tax_rate_id ),
                        'label'              => WC_Tax::get_rate_label( $tax_rate_id ),
                        'compound'           => WC_Tax::is_compound( $tax_rate_id ),
                        'rate_percent'       => WC_Tax::get_rate_percent_value( $tax_rate_id )
                    ]);

                    $item_tax->save();

                    $order->add_item( $item_tax );
                    $order->set_cart_tax( $tax_amount );

                    if ( wc_prices_include_tax() ) {
                        $order->update_meta_data( '_ova_prices_include_tax', 1 );
                    }
                }

                // Order status
                $order_status = sanitize_text_field( ovabrw_get_meta_data( 'status_order', $_POST ) );
                if ( $order_status ) $order->update_status( $order_status );

                // Order set total
                $order->set_total( $order_total );
                $order->save();

                do_action( OVABRW_PREFIX.'after_create_new_order_manully', $_POST, $order );

                // Redirect to order detail
                if ( $order_id ) {
                    wp_redirect( $order->get_edit_order_url() );
                    exit;
                }
            }
        }

        /**
         * Get order address
         */
        public function ovabrw_get_address() {
            // Get first name
            $first_name = sanitize_text_field( ovabrw_get_meta_data( 'ovabrw_first_name', $_POST ) );

            // Get last name
            $last_name = sanitize_text_field( ovabrw_get_meta_data( 'ovabrw_last_name', $_POST ) );

            // Get company
            $company = sanitize_text_field( ovabrw_get_meta_data( 'ovabrw_company', $_POST ) );

            // Get email
            $email = sanitize_text_field( ovabrw_get_meta_data( 'ovabrw_email', $_POST ) );

            // Get phone
            $phone = sanitize_text_field( ovabrw_get_meta_data( 'ovabrw_phone', $_POST ) );

            // Get address 1
            $address_1 = sanitize_text_field( ovabrw_get_meta_data( 'ovabrw_address_1', $_POST ) );

            // Get address 2
            $address_2 = sanitize_text_field( ovabrw_get_meta_data( 'ovabrw_address_2', $_POST ) );

            // Get city
            $city = sanitize_text_field( ovabrw_get_meta_data( 'ovabrw_city', $_POST ) );

            // Get country
            $country_setting = sanitize_text_field( ovabrw_get_meta_data( 'ovabrw_country', $_POST, 'US' ) );
            if ( strstr( $country_setting, ':' ) ) {
                $country_setting = explode( ':', $country_setting );
                $country         = current( $country_setting );
                $state           = end( $country_setting );
            } else {
                $country = $country_setting;
                $state   = '*';
            }

            return apply_filters( OVABRW_PREFIX.'create_order_get_address', [
                'first_name' => $first_name,
                'last_name'  => $last_name,
                'company'    => $company,
                'email'      => $email,
                'phone'      => $phone,
                'address_1'  => $address_1,
                'address_2'  => $address_2,
                'city'       => $city,
                'country'    => $country
            ]);
        }

        /**
         * Add order item
         */
        public function ovabrw_add_order_item( $order_id ) {
            // Order data
            $data_order = [];
            if ( !$order_id ) return $data_order;

            // Get order
            $order = wc_get_order( $order_id );

            // init
            $has_deposit = $item_has_deposit = false;
            $order_total = $total_deposit = $total_remaining = $remaining_tax = $total_insurance = $insurance_tax = 0;
            $tax_rate_id = '';
            $tax_amount  = 0;

            // Init $i
            $i = 0;

            // Get guest information data
            $guest_info_enabled = ovabrw_guest_info_enabled();

            // Get order items
            $order_items = $order->get_items();
            foreach ( $order_items as $item_id => $item ) {
                $data_item = [];
                
                // Get order line item
                $item = $order->get_item( $item_id );
                if ( !$item ) continue;

                // Get product ID
                $product_id = $item->get_product_id();

                // Get product
                $product = $item->get_product();

                // Get pick-up date
                $ovabrw_pickup_date = isset( $_POST['ovabrw_pickup_date'][$i] ) ? $_POST['ovabrw_pickup_date'][$i] : '';

                // Get time
                $time_from = isset( $_POST['ovabrw_time_from'][$product_id] ) ? $_POST['ovabrw_time_from'][$product_id] : '';
                if ( $time_from ) $ovabrw_pickup_date .= ' ' . $time_from;

                // Get drop-off date
                $ovabrw_pickoff_date = isset( $_POST['ovabrw_pickoff_date'][$i] ) ? $_POST['ovabrw_pickoff_date'][$i] : '';

                // Number of adults
                $ovabrw_adults = isset( $_POST['ovabrw_adults'][$i] ) ? absint( $_POST['ovabrw_adults'][$i] ) : 1;

                // Number of children
                $ovabrw_children = isset( $_POST['ovabrw_childrens'][$i] ) ? absint( $_POST['ovabrw_childrens'][$i] ) : 0;

                // Number of babies
                $ovabrw_babies = isset( $_POST['ovabrw_babies'][$i] ) ? absint( $_POST['ovabrw_babies'][$i] ) : 0;

                // Guest info
                $guest_info = [];
                
                // Adult info
                if ( $guest_info_enabled && $ovabrw_adults ) {
                    $aduld_info = $this->ovabrw_get_guest_info_data( 'ovabrw_adults', $product_id );
                    if ( ovabrw_array_exists( $aduld_info ) ) $guest_info['adult'] = $aduld_info;
                }

                // Child info
                if ( $guest_info_enabled && $ovabrw_children ) {
                    $child_info = $this->ovabrw_get_guest_info_data( 'ovabrw_childrens', $product_id );
                    if ( ovabrw_array_exists( $child_info ) ) $guest_info['child'] = $child_info;
                }

                // Baby info
                if ( $guest_info_enabled && $ovabrw_babies ) {
                    $baby_info = $this->ovabrw_get_guest_info_data( 'ovabrw_babies', $product_id );
                    if ( ovabrw_array_exists( $baby_info ) ) $guest_info['baby'] = $baby_info;
                }

                // Get resources
                $ovabrw_resources = isset( $_POST['ovabrw_resource_checkboxs'][$product_id] ) ? $_POST['ovabrw_resource_checkboxs'][$product_id] : [];

                // Get resource guests
                $resource_guests = isset( $_POST['ovabrw_resource_guests'][$product_id] ) ? $_POST['ovabrw_resource_guests'][$product_id] : [];

                // Get services
                $ovabrw_services = isset( $_POST['ovabrw_service'][$product_id] ) ? $_POST['ovabrw_service'][$product_id] : [];

                // Get service guests
                $service_guests = isset( $_POST['ovabrw_service_guests'][$product_id] ) ? $_POST['ovabrw_service_guests'][$product_id] : [];

                // Intem insurance
                $item_insurance = isset( $_POST['ovabrw_amount_insurance'][$i] ) ? (float)$_POST['ovabrw_amount_insurance'][$i] : 0;

                // Item deposit
                $item_deposit = isset( $_POST['ovabrw_amount_deposite'][$i] ) ? (float)$_POST['ovabrw_amount_deposite'][$i] : 0;

                // Item remaining
                $item_remaining = isset( $_POST['ovabrw_amount_remaining'][$i] ) ? (float)$_POST['ovabrw_amount_remaining'][$i] : 0;

                // Item subtotal
                $item_subtotal = isset( $_POST['ovabrw-total-product'][$i] ) ? (float)$_POST['ovabrw-total-product'][$i] : 0;
                if ( $item_insurance ) $item_subtotal -= $item_insurance;

                // Add time
                if ( $time_from ) {
                    $data_item[ 'ovabrw_time_from' ] = $time_from;
                }

                // Pick-up date
                $data_item[ 'ovabrw_pickup_date' ]              = $ovabrw_pickup_date;
                $data_item[ 'ovabrw_pickup_date_strtotime' ]    = strtotime( $ovabrw_pickup_date );

                // Drop-off date
                $data_item[ 'ovabrw_pickoff_date' ]             = $ovabrw_pickoff_date;
                $data_item[ 'ovabrw_dropoff_date_strtotime' ]   = strtotime( $ovabrw_pickoff_date );
                
                // Number of adults
                $data_item[ 'ovabrw_adults' ] = $ovabrw_adults;

                // Number of children
                $data_item[ 'ovabrw_childrens' ] = $ovabrw_children;

                // Number of babies
                $data_item[ 'ovabrw_babies' ] = $ovabrw_babies;

                // Guest info
                if ( ovabrw_array_exists( $guest_info ) ) {
                    $data_item['ovabrw_guest_info'] = $guest_info;
                }

                // Quantity
                $data_item[ 'ovabrw_quantity' ] = 1;

                // CCKF
                $custom_ckf = $cckf_qty = [];

                // Custom Checkout Fields
                $list_extra_fields = ovabrw_get_list_field_checkout( $product_id );
                if ( ovabrw_array_exists( $list_extra_fields ) ) {
                    foreach ( $list_extra_fields as $key => $field ) {
                        // Enable
                        if ( 'on' !== ovabrw_get_meta_data( 'enabled', $field ) ) continue;

                        // Field type
                        $field_type = ovabrw_get_meta_data( 'type', $field );
                        if ( 'file' === $field_type ) {
                            $data_file = ovabrw_get_meta_data( $key, $_FILES );
                            if ( ovabrw_array_exists( $data_file ) ) {
                                $files = [];

                                if ( isset( $data_file['name'][$product_id] ) ) {
                                    $files['name'] = $data_file['name'][$product_id];
                                }
                                if ( isset( $data_file['full_path'][$product_id] ) ) {
                                    $files['full_path'] = $data_file['full_path'][$product_id];
                                }
                                if ( isset( $data_file['type'][$product_id] ) ) {
                                    $files['type'] = $data_file['type'][$product_id];
                                }
                                if ( isset( $data_file['tmp_name'][$product_id] ) ) {
                                    $files['tmp_name'] = $data_file['tmp_name'][$product_id];
                                }
                                if ( isset( $data_file['error'][$product_id] ) ) {
                                    $files['error'] = $data_file['error'][$product_id];
                                }
                                if ( isset( $data_file['size'][$product_id] ) ) {
                                    $files['size'] = $data_file['size'][$product_id];
                                }

                                if ( isset( $files['size'] ) && $files['size'] ) {
                                    $mb = absint( $files['size'] ) / 1048576;

                                    if ( $mb > $field['max_file_size'] ) {
                                        continue;
                                    }
                                }

                                $overrides = [
                                    'test_form' => false,
                                    'mimes'     => apply_filters( 'ovabrw_ft_file_mimes', [
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

                                $data_item[$key] = '<a href="'.esc_url( $upload['url'] ).'" title="'.esc_attr( basename( $upload['file'] ) ).'" target="_blank">'.esc_attr( basename( $upload['file'] ) ).'</a>';
                            }
                        } else {
                            $value = isset( $_POST[$key][$product_id] ) ? $_POST[$key][$product_id] : '';
                            if ( !empty( $value ) ) {
                                if ( 'select' === $field_type ) {
                                    $value = sanitize_text_field( $value );

                                    // Add cckf
                                    $custom_ckf[$key] = $value;

                                    // Option key
                                    $options_key = ovabrw_get_meta_data( 'ova_options_key', $field );

                                    // Option text
                                    $options_text = ovabrw_get_meta_data( 'ova_options_text', $field );

                                    // Option quantities
                                    $opt_qtys = isset( $_POST[$key.'_qty'][$product_id] ) ? $_POST[$key.'_qty'][$product_id] : [];

                                    // Get qty
                                    $opt_qty = (int)ovabrw_get_meta_data( $value, $opt_qtys );
                                    if ( $opt_qty ) $cckf_qty[$key] = $opt_qty;

                                    $key_op = array_search( $value, $options_key );
                                    if ( !is_bool( $key_op ) ) {
                                        if ( ovabrw_check_array( $options_text, $key_op ) ) {
                                            $value = $options_text[$key_op];

                                            // Qty
                                            if ( $opt_qty > 1 ) {
                                                $value = sprintf( esc_html__( '%s (x%d)', 'ova-brw' ), $value, $opt_qty );
                                            }
                                        }
                                    }
                                } elseif ( 'checkbox' === $field['type'] ) {
                                    $checkbox_val = [];

                                    if ( ovabrw_array_exists( $value ) ) {
                                        $custom_ckf[$key] = $value;

                                        // Checkbox key
                                        $checkbox_key = ovabrw_get_meta_data( 'ova_checkbox_key', $field );

                                        // Checkbox text
                                        $checkbox_text = ovabrw_get_meta_data( 'ova_checkbox_text', $field );

                                        // Option quantities
                                        $opt_qtys = isset( $_POST[$key.'_qty'][$product_id] ) ? $_POST[$key.'_qty'][$product_id] : [];
                                        if ( ovabrw_array_exists( $opt_qtys ) ) $cckf_qty[$key] = $opt_qtys;

                                        // Loop
                                        foreach ( $value as $val_cb ) {
                                            $key_cb = array_search( $val_cb, $checkbox_key );

                                            // Get qty
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
                                            $value = join( ", ", $checkbox_val );
                                        }
                                    }
                                } elseif ( 'radio' === $field_type ) {
                                    $value = sanitize_text_field( $value );

                                    // Add cckf
                                    $custom_ckf[$key] = $value;

                                    // Option quantities
                                    $opt_qtys = isset( $_POST[$key.'_qty'][$product_id] ) ? $_POST[$key.'_qty'][$product_id] : [];

                                    // Get qty
                                    $opt_qty = (int)ovabrw_get_meta_data( $value, $opt_qtys );
                                    if ( $opt_qty ) {
                                        $cckf_qty[$key] = $opt_qty;

                                        if ( $opt_qty > 1 ) {
                                            $value = sprintf( esc_html__( '%s (x%d)', 'ova-brw' ), $value, $opt_qty );
                                        }
                                    }
                                }

                                // Add data item
                                $data_item[$key] = $value;
                            }
                        }
                    }
                }

                if ( ovabrw_array_exists( $custom_ckf ) ) {
                    $data_item['ovabrw_custom_ckf'] = $custom_ckf;

                    if ( ovabrw_array_exists( $cckf_qty ) ) {
                        $data_item['ovabrw_cckf_qty'] = $cckf_qty;
                    }
                }
                // End custom checkout field

                // Item resources
                if ( ovabrw_array_exists( $ovabrw_resources ) ) {
                    // init
                    $resc_values = [];

                    // Loop
                    foreach ( $ovabrw_resources as $opt_id => $opt_value ) {
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
                    } // END

                    if ( ovabrw_array_exists( $resc_values ) ) {
                        $data_item[sprintf( _n( 'Resource%s', 'Resources%s', count( $resc_values ), 'ova-brw' ), '' )] = implode( ', ', $resc_values );
                    }

                    // Save resources
                    $data_item['ovabrw_resources'] = $ovabrw_resources;

                    // Save resource guests
                    if ( ovabrw_array_exists( $resource_guests ) ) {
                        $data_item['ovabrw_resource_guests'] = $resource_guests;
                    }
                } // END resources

                // Item services
                if ( ovabrw_array_exists( $ovabrw_services ) ) {
                    // Get service labels
                    $serv_labels = ovabrw_get_post_meta( $product_id, 'label_service' );

                    // Get service ids
                    $serv_ids = ovabrw_get_post_meta( $product_id, 'service_id' );

                    // Get service names
                    $serv_names = ovabrw_get_post_meta( $product_id, 'service_name' );

                    // Loop
                    foreach ( $ovabrw_services as $ser_id ) {
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

                                                $s_name = sprintf( esc_html__( '%s (%s)', 'ova-brw' ), $s_name, implode( ', ', $opt_values ) );
                                            }

                                            $data_item[$s_label] = $s_name;
                                        } // END if
                                    } // END foreach
                                } // END if
                            } // END foreach
                        } // END if
                    } // END loop

                    // Save services
                    $data_item['ovabrw_services'] = $ovabrw_services;

                    // Save service guests
                    if ( ovabrw_array_exists( $service_guests ) ) {
                        $data_item['ovabrw_service_guests'] = $service_guests;
                    }
                } // END services

                // Insurance amount
                if ( $item_insurance ) {
                    $data_item[ 'ovabrw_insurance_amount' ] = $item_insurance;

                    $sub_insurance_tax = ovabrw_get_insurance_tax_amount( $item_insurance );

                    if ( $sub_insurance_tax ) {
                        $insurance_tax += $sub_insurance_tax;

                        $data_item[ 'ovabrw_insurance_tax' ] = $sub_insurance_tax;
                    }
                }
                // End
                
                // Deposit
                if ( $item_deposit ) {
                    $data_item[ 'ovabrw_deposit_type' ]     = 'value';
                    $data_item[ 'ovabrw_deposit_value' ]    = $item_deposit;
                    $data_item[ 'ovabrw_deposit_amount' ]   = $item_deposit;
                    $data_item[ 'ovabrw_remaining_amount' ] = $item_remaining;
                    $data_item[ 'ovabrw_total_payable' ]    = $item_subtotal;
                    $item_subtotal = $item_deposit;
                }
                // End
                
                // Order total
                $order_total += $item_subtotal;

                // Taxable
                $item_taxes = false;

                if ( wc_tax_enabled() ) {
                    $tax_rates = WC_Tax::get_rates( $product->get_tax_class() );
                    if ( ! empty( $tax_rates ) ) {
                        $tax_rate_id = key( $tax_rates );
                    }

                    // Remaining tax
                    $item_remaining_tax = ovabrw_get_taxes_by_price( $product, $item_remaining );
                    $remaining_tax      += $item_remaining_tax;

                    // Add item remaining tax
                    $data_item['ovabrw_remaining_tax'] = $item_remaining_tax;

                    if ( wc_prices_include_tax() ) {
                        $taxes          = WC_Tax::calc_inclusive_tax( $item_subtotal, $tax_rates );
                        $item_tax       = WC_Tax::get_tax_total( $taxes );
                        $tax_amount    += $item_tax;
                        $item_subtotal -= $item_tax;
                    } else {
                        $taxes          = WC_Tax::calc_exclusive_tax( $item_subtotal, $tax_rates );
                        $item_tax       = WC_Tax::get_tax_total( $taxes );
                        $tax_amount    += $item_tax;
                        $order_total   += $item_remaining_tax;
                    }

                    $item_taxes = array(
                        'total'    => $taxes,
                        'subtotal' => $taxes,
                    );
                }

                // Update item meta data
                foreach ( $data_item as $meta_key => $meta_value ) {
                    $item->add_meta_data( $meta_key, $meta_value, true );
                }

                // Update item meta
                $item->set_props(
                    array(
                        'total'     => $item_subtotal,
                        'subtotal'  => $item_subtotal,
                        'taxes'     => $item_taxes
                    )
                );

                $item->save();
                // End update item meta
                
                $total_deposit      += $item_deposit;
                $total_remaining    += $item_remaining;
                $total_insurance    += $item_insurance;

                $i++;
            }

            $data_order = [
                'order_total'       => $order_total,
                'total_insurance'   => $total_insurance,
                'insurance_tax'     => $insurance_tax,
                'total_deposit'     => $total_deposit,
                'total_remaining'   => $total_remaining,
                'remaining_tax'     => $remaining_tax,
                'tax_rate_id'       => $tax_rate_id,
                'tax_amount'        => $tax_amount
            ];

            return apply_filters( 'ovabrw_ft_add_order_item', $data_order, $order_id );
        }

        /**
         * Get guest info data
         */
        public function ovabrw_get_guest_info_data( $guest_name, $product_id ) {
            if ( !$guest_name || !$product_id ) return false;

            // Guest info data
            $guest_info = isset( $_POST[$guest_name.'_info'][$product_id] ) ? $_POST[$guest_name.'_info'][$product_id] : [];
            if ( !ovabrw_array_exists( $guest_info ) && !ovabrw_array_exists( $_FILES ) ) {
                return;
            }

            // init
            $info_data = [];

            // Get guest fields
            $guest_fields = ovabrw_get_option( 'guest_fields', [] );
            foreach ( $guest_fields as $name => $fields ) {
                // Enable
                $enable = ovabrw_get_meta_data( 'enable', $fields );
                if ( !$enable ) continue;

                // Label
                $label = ovabrw_get_meta_data( 'label', $fields );

                // Type
                $type = ovabrw_get_meta_data( 'type', $fields );

                // Option IDs
                $option_ids = ovabrw_get_meta_data( 'option_ids', $fields, [] );

                // Option Names
                $option_names = ovabrw_get_meta_data( 'option_names', $fields, [] );

                if ( 'file' === $type ) {
                    // Get guest files
                    $guest_files = ovabrw_get_meta_data( $guest_name.'_'.$name, $_FILES );

                    if ( ovabrw_array_exists( $guest_files ) ) {
                        // Max size
                        $max_size = (float)ovabrw_get_meta_data( 'max_size', $fields );

                        // Accept
                        $accept = ovabrw_get_meta_data( 'accept', $fields );

                        if ( !empty( $guest_files['name'][$product_id] ) ) {
                            foreach ( $guest_files['name'][$product_id] as $k => $file_name ) {
                                if ( !$file_name ) continue;

                                // Files data
                                $files = [
                                    'name'      => $file_name,
                                    'full_path' => isset( $guest_files['full_path'][$product_id][$k] ) ? $guest_files['full_path'][$product_id][$k] : '',
                                    'type'      => isset( $guest_files['type'][$product_id][$k] ) ? $guest_files['type'][$product_id][$k] : '',
                                    'tmp_name'  => isset( $guest_files['tmp_name'][$product_id][$k] ) ? $guest_files['tmp_name'][$product_id][$k] : '',
                                    'error'     => isset( $guest_files['error'][$product_id][$k] ) ? $guest_files['error'][$product_id][$k] : 0,
                                    'size'      => isset( $guest_files['size'][$product_id][$k] ) ? (int)$guest_files['size'][$product_id][$k] : 0
                                ];

                                // Check file max size
                                if ( $max_size ) {
                                    $file_size = $files['size'] / 1048576;
                                    if ( $file_size > $max_size ) continue;
                                }

                                // Check file accept
                                if ( $accept ) {
                                    $file_extension = pathinfo( $file_name, PATHINFO_EXTENSION );
                                    if ( strpos( $accept, $file_extension ) === false ) continue;
                                }

                                // Upload file
                                $overrides = [ 'test_form' => false ];

                                if ( !function_exists( 'wp_handle_upload' ) ) {
                                    require_once( ABSPATH . 'wp-admin/includes/file.php' );
                                }

                                // Upload file
                                $upload = wp_handle_upload( $files, $overrides );
                                if ( ovabrw_get_meta_data( 'error', $upload ) ) continue;

                                $file_path  = ovabrw_get_meta_data( 'file', $upload );
                                $file_name  = ovabrw_get_meta_data( 'file', $upload );
                                $file_url   = ovabrw_get_meta_data( 'url', $upload );
                                $file_type  = ovabrw_get_meta_data( 'type', $upload );

                                try {
                                    // Create attachment id
                                    $attachment_id = wp_insert_attachment([
                                        'guid'              => $file_url,
                                        'post_mime_type'    => $file_type,
                                        'post_title'        => pathinfo( basename( $file_name ), PATHINFO_FILENAME ),
                                        'post_content'      => '',
                                        'post_status'       => 'inherit'
                                    ], $file_path );

                                    if ( $attachment_id ) {
                                        // Generate the metadata for the attachment and update the database record
                                        require_once(ABSPATH . 'wp-admin/includes/image.php');

                                        $attach_data = wp_generate_attachment_metadata( $attachment_id, $file_path );
                                        wp_update_attachment_metadata( $attachment_id, $attach_data );

                                        $info_data[$k][$name] = [
                                            'label'         => $label,
                                            'type'          => $type,
                                            'path'          => $file_path,
                                            'value'         => $file_name,
                                            'url'           => $file_url,
                                            'extention'     => $file_type,
                                            'attachment_id' => $attachment_id
                                        ];
                                    }
                                } catch ( Exception $e ) {
                                    continue;
                                }
                            }
                        }
                    }
                } else {
                    if ( ovabrw_array_exists( $guest_info ) ) {
                        foreach ( $guest_info as $k => $field_data ) {
                            if ( 'date' === $type ) {
                                $date = ovabrw_get_meta_data( $name, $field_data );
                                if ( !strtotime( $date ) ) continue;

                                // Check min date
                                $min_date = ovabrw_get_meta_data( 'min', $fields );
                                $min_date = apply_filters( OVABRW_PREFIX.'guest_info_min_date', $min_date, $fields );
                                $min_date = strtotime( $min_date );
                                if ( $min_date && $min_date > strtotime( $date ) ) continue;

                                // Check max date
                                $max_date = ovabrw_get_meta_data( 'max', $fields );
                                $max_date = apply_filters( OVABRW_PREFIX.'guest_info_date', $max_date, $fields );
                                $max_date = strtotime( $max_date );
                                if ( $max_date && $max_date < strtotime( $date ) ) continue;
                                
                                // Add info data
                                $info_data[$k][$name] = [
                                    'label' => $label,
                                    'type'  => $type,
                                    'value' => $date
                                ];
                            } elseif ( 'radio' === $type || 'select' === $type ) {
                                $opt_id = ovabrw_get_meta_data( $name, $field_data );
                                if ( $opt_id ) {
                                    // Get option index
                                    $opt_index = array_search( $opt_id, $option_ids );

                                    if ( false !== $opt_index ) {
                                        $opt_name = ovabrw_get_meta_data( $opt_index, $option_names );

                                        // Add info data
                                        $info_data[$k][$name] = [
                                            'label'         => $label,
                                            'type'          => $type,
                                            'option_id'     => $opt_id,
                                            'option_name'   => $opt_name
                                        ];
                                    }
                                }
                            } elseif ( 'checkbox' === $type ) {
                                $opt_ids = ovabrw_get_meta_data( $name, $field_data );
                                if ( ovabrw_array_exists( $opt_ids ) ) {
                                    $data_opt_ids = $data_opt_names = [];

                                    // Loop
                                    foreach ( $opt_ids as $opt_id ) {
                                        if ( $opt_id ) {
                                            // Get option index
                                            $opt_index = array_search( $opt_id, $option_ids );

                                            if ( false !== $opt_index ) {
                                                $opt_name = ovabrw_get_meta_data( $opt_index, $option_names );

                                                // Add opt data
                                                $data_opt_ids[]     = $opt_id;
                                                $data_opt_names[]   = $opt_name;
                                            }
                                        }
                                    } // END Loop
                                    
                                    // Add data
                                    if ( ovabrw_array_exists( $data_opt_ids ) ) {
                                        // Add info data
                                        $info_data[$k][$name] = [
                                            'label'         => $label,
                                            'type'          => $type,
                                            'option_id'     => $data_opt_ids,
                                            'option_name'   => $data_opt_names
                                        ];
                                    }
                                }
                            } else {
                                $value = ovabrw_get_meta_data( $name, $field_data );
                                if ( !$value ) continue;

                                // Add info data
                                $info_data[$k][$name] = [
                                    'label' => $label,
                                    'type'  => $type,
                                    'value' => $value
                                ];
                            }
                        }
                    }
                }
            }

            return apply_filters( OVABRW_PREFIX.'create_order_get_guest_info_data', $info_data, $guest_name, $product_id );
        }
    }

    // init class
    new OVABRW_Admin_Orders();
}