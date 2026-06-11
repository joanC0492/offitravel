<?php if ( !defined( 'ABSPATH' ) ) exit();

if ( !class_exists('WP_List_Table') ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

if ( !class_exists( 'OVABRW_List_Booking' ) ) {

    class OVABRW_List_Booking extends WP_List_Table {

        /**
         * Constructor
         */
        public function __construct() {
            global $page;

            //Set parent defaults
            parent::__construct( array(
                'singular'  => 'bookings',     //singular name of the listed records
                'plural'    => 'bookings',    //plural name of the listed records
                'ajax'      => false        //does this table support ajax?
            ));
        }

        function column_default( $item, $column_name ) {
            switch ( $column_name ) {
                case 'order_id':
                case 'customer_name':
                case 'dates':
                case 'deposit_status':
                case 'insurance_status':
                case 'product':
                case 'order_status':
                    return $item[$column_name];
                default:
                    return print_r($item,true); //Show the whole array for troubleshooting purposes
            }
        }

        function column_order_status( $item ) {
            switch ( $item['order_status'] ) {
                case 'processing':
                    $order_status_text = esc_html__( 'Processing', 'ova-brw' );
                    break;
                case 'completed':
                    $order_status_text = esc_html__( 'Completed', 'ova-brw' );
                    break;
                case 'on-hold':
                    $order_status_text = esc_html__( 'On hold', 'ova-brw' );
                    break;
                case 'pending':
                    $order_status_text = esc_html__( 'Pending payment', 'ova-brw' );
                    break;
                case 'cancelled':
                    $order_status_text = esc_html__( 'Cancel', 'ova-brw' );
                    break;
                case 'closed':
                    $order_status_text = esc_html__( 'Closed', 'ova-brw' );
                    break;    
                default:
                    $order_status_text = esc_html__( 'Update Order', 'ova-brw' );
                    break;
            }
            
            //Build row actions
            $selected_action = sprintf( '<select name="update_order_status" class="update_order_status" data-order_id="'.$item['order_number'].'" data-error-per-msg="'.esc_html__( 'You don\'t have permission to update', 'ova-brw' ).'" data-error-update-msg="'.esc_html__( 'Error Update', 'ova-brw' ).'">
                <option value="">'.esc_html__( 'Update status', 'ova-brw' ).'</option>
                <option value="wc-completed">'.esc_html__( 'Completed', 'ova-brw' ).'</option>
                <option value="wc-processing">'.esc_html__( 'Processing', 'ova-brw' ).'</option>
                <option value="wc-on-hold">'.esc_html__( 'On hold', 'ova-brw' ).'</option>
                <option value="wc-pending">'.esc_html__( 'Pending payment', 'ova-brw' ).'</option>
                <option value="wc-cancelled">'.esc_html__( 'Cancel', 'ova-brw' ).'</option>
                <option value="wc-closed">'.esc_html__( 'Closed', 'ova-brw' ).'</option>
            </select>' );

            return sprintf('<span>%1$s</span>%2$s',
                '<mark class="ovabrw-order-status status-'.$item['order_status'].' tips"><span>'.$order_status_text.'</span></mark>',
                $selected_action
            );
        }

        function column_id( $item ) {
            //Build row actions
            if ( current_user_can( apply_filters( 'ovabrw_edit_order_woo_cap' ,'manage_options' ) ) ) {
                return sprintf('<span>%1$s</span>',
                    '<a target="_blank" href="'.admin_url('/post.php?post='.$item['id'].'&action=edit').'">'.$item['id'].'</a>'
                );
            } else {
                return sprintf( '<span>%1$s</span>', $item['id'] );
            }
        }

        function get_columns() {
            $options = $columns = array();

            $id = get_option( 'admin_manage_order_show_id', 1 );
            if ( $id ) {
                $options['order_id'] = $id;
            }

            $customer = get_option( 'admin_manage_order_show_customer', 2 );
            if ( $customer ) {
                $options['customer_name'] = $customer;
            }

            $time = get_option( 'admin_manage_order_show_time', 3 );
            if ( $time ) {
                $options['dates'] = $time;
            }

            $deposit = get_option( 'admin_manage_order_show_deposit', 5 );
            if ( $deposit ) {
                $options['deposit_status'] = $deposit;
            }

            $insurance = get_option( 'admin_manage_order_show_insurance', 6 );
            if ( $insurance ) {
                $options['insurance_status'] = $insurance;
            }

            $product = get_option( 'admin_manage_order_show_product', 8 );
            if ( $product ) {
                $options['product'] = $product;
            }

            $status = get_option( 'admin_manage_order_show_order_status', 9 );
            if ( $status ) {
                $options['order_status'] = $status;
            }

            if ( $options ) {
                asort( $options );
            }

            foreach ( $options as $key => $value ) {
                switch ( $key ) {
                    case 'order_id':
                        $columns[$key] = esc_html__( 'Booking ID', 'ova-brw' );
                        break;
                    case 'customer_name':
                        $columns[$key] = esc_html__( 'Customer name', 'ova-brw' );
                        break;
                    case 'dates':
                        $columns[$key] = esc_html__( 'Check-in @ Check-out', 'ova-brw' );
                        break;
                    case 'deposit_status':
                        $columns[$key] = esc_html__( 'Deposit status', 'ova-brw' );
                        break;
                    case 'insurance_status':
                        $columns[$key] = esc_html__( 'Insurance status', 'ova-brw' );
                        break;
                    case 'product':
                        $columns[$key] = esc_html__( 'Product', 'ova-brw' );
                        break;
                    case 'order_status':
                        $columns[$key] = esc_html__( 'Status', 'ova-brw' );
                        break;
                    default:
                        break;
                }
            }

            return $columns;
        }

        function get_sortable_columns() {
            $sortable_columns = array(
                'order_id' => array( 'order_id', true )
            );

            return $sortable_columns;
        }

        function pagination( $which ) {
            if ( empty( $this->_pagination_args ) ) {
                return;
            }

            // Parameters
            $parameters = array();

            // Order ID
            if ( ovabrw_get_meta_data( 'order_id', $_REQUEST ) ) {
                $parameters['order_id'] = $_REQUEST['order_id'];
            }

            // Customer name
            if ( ovabrw_get_meta_data( 'customer_name', $_REQUEST ) ) {
                $parameters['name_customer'] = $_REQUEST['customer_name'];
            }

            // Check-in date
            if ( ovabrw_get_meta_data( 'checkin_date', $_REQUEST ) ) {
                $parameters['checkin_date'] = $_REQUEST['checkin_date'];
            }

            // Check-out date
            if ( ovabrw_get_meta_data( 'checkin_out', $_REQUEST ) ) {
                $parameters['checkin_out'] = $_REQUEST['checkin_out'];
            }

            // Product ID
            if ( ovabrw_get_meta_data( 'product_id', $_REQUEST ) ) {
                $parameters['product_id'] = $_REQUEST['product_id'];
            }

            // Order status
            if ( ovabrw_get_meta_data( 'order_status', $_REQUEST ) ) {
                $parameters['order_status'] = $_REQUEST['order_status'];
            }

            $total_items     = $this->_pagination_args['total_items'];
            $total_pages     = $this->_pagination_args['total_pages'];
            $infinite_scroll = false;

            if ( isset( $this->_pagination_args['infinite_scroll'] ) ) {
                $infinite_scroll = $this->_pagination_args['infinite_scroll'];
            }

            if ( 'top' === $which && $total_pages > 1 ) {
                $this->screen->render_screen_reader_content( 'heading_pagination' );
            }

            $output = '<span class="displaying-num">' . sprintf(
                /* translators: %s: Number of items. */
                _n( '%s item', '%s items', $total_items ),
                number_format_i18n( $total_items )
            ) . '</span>';

            $current              = $this->get_pagenum();
            $removable_query_args = wp_removable_query_args();

            $current_url    = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
            $current_url    = remove_query_arg( $removable_query_args, $current_url );
            $page_links     = array();

            $total_pages_before = '<span class="paging-input">';
            $total_pages_after  = '</span></span>';

            $disable_first = false;
            $disable_last  = false;
            $disable_prev  = false;
            $disable_next  = false;

            if ( 1 == $current ) {
                $disable_first = true;
                $disable_prev  = true;
            }
            if ( $total_pages == $current ) {
                $disable_last = true;
                $disable_next = true;
            }

            if ( $disable_first ) {
                $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>';
            } else {
                $page_links[] = sprintf(
                    "<a class='first-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
                    esc_url( remove_query_arg( 'paged', $current_url ) ),
                    __( 'First page' ),
                    '&laquo;'
                );
            }

            if ( $disable_prev ) {
                $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>';
            } else {
                $parameters['paged'] = max( 1, $current - 1 );
                $page_links[] = sprintf(
                    "<a class='prev-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
                    esc_url( add_query_arg( $parameters, $current_url ) ),
                    __( 'Previous page' ),
                    '&lsaquo;'
                );
            }

            if ( 'bottom' === $which ) {
                $html_current_page  = $current;
                $total_pages_before = '<span class="screen-reader-text">' . __( 'Current Page' ) . '</span><span id="table-paging" class="paging-input"><span class="tablenav-paging-text">';
            } else {
                $html_current_page = sprintf(
                    "%s<input class='current-page' id='current-page-selector' type='text' name='paged' value='%s' size='%d' aria-describedby='table-paging' /><span class='tablenav-paging-text'>",
                    '<label for="current-page-selector" class="screen-reader-text">' . __( 'Current Page' ) . '</label>',
                    $current,
                    strlen( $total_pages )
                );
            }
            $html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
            $page_links[]     = $total_pages_before . sprintf(
                /* translators: 1: Current page, 2: Total pages. */
                _x( '%1$s of %2$s', 'paging' ),
                $html_current_page,
                $html_total_pages
            ) . $total_pages_after;

            if ( $disable_next ) {
                $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>';
            } else {
                $parameters['paged'] = min( $total_pages, $current + 1 );
                $page_links[] = sprintf(
                    "<a class='next-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
                    esc_url( add_query_arg( $parameters, $current_url ) ),
                    __( 'Next page' ),
                    '&rsaquo;'
                );
            }

            if ( $disable_last ) {
                $page_links[] = '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>';
            } else {
                $parameters['paged'] = $total_pages;
                $page_links[] = sprintf(
                    "<a class='last-page button' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
                    esc_url( add_query_arg( $parameters, $current_url ) ),
                    __( 'Last page' ),
                    '&raquo;'
                );
            }

            $pagination_links_class = 'pagination-links';
            if ( ! empty( $infinite_scroll ) ) {
                $pagination_links_class .= ' hide-if-js';
            }
            $output .= "\n<span class='$pagination_links_class'>" . implode( "\n", $page_links ) . '</span>';

            if ( $total_pages ) {
                $page_class = $total_pages < 2 ? ' one-page' : '';
            } else {
                $page_class = ' no-pages';
            }
            $this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

            echo $this->_pagination;
        }

        function prepare_items() {
            global $wpdb; //This is used only if making any database queries

            /**
             * First, lets decide how many records per page to show
             */
            $per_page   = apply_filters( 'ovabrw_ft_manager_order_per_page', 20 );
            $columns    = $this->get_columns();
            $hidden     = array();
            $sortable   = $this->get_sortable_columns();

            // Date format
            $date_format = ovabrw_get_date_format();
            
            $this->_column_headers = array($columns, $hidden, $sortable);
            
            $data = array();

            // Order ID
            $order_id = ovabrw_get_meta_data( 'order_id', $_GET );

            // Customer name
            $customer_name = ovabrw_get_meta_data( 'customer_name', $_GET );

            // Check-in date
            $checkin_date = strtotime( ovabrw_get_meta_data( 'checkin_date', $_GET ) );
            if ( $checkin_date ) {
                $checkin_date = strtotime( date( $date_format, $checkin_date ). ' 00:00' );
            }

            // Check-out date
            $checkout_date = strtotime( ovabrw_get_meta_data( 'checkout_date', $_GET ) );
            if ( $checkout_date ) {
                $checkout_date = strtotime( date( $date_format, $checkout_date ). ' 24:00' ) - 1;
            }

            // Product ID
            $product_id = ovabrw_get_meta_data( 'product_id', $_GET );

            // Order status
            $order_status = ovabrw_get_meta_data( 'order_status', $_GET );

            if ( $order_status ) {
                $order_status = array( $order_status );
            } else {
                $order_status = array( 'wc-pending', 'wc-processing','wc-completed', 'wc-half-completed', 'wc-on-hold', 'wc-cancelled', 'wc-closed' );
            }

            // Product query
            $product_query = '';
            if ( $product_id ) {
                $product_query = 'AND oitem_meta.meta_value = '.$product_id;
            }

            // Order query
            $order_query = '';
            if ( $order_id ) {
                $order_query = 'AND oitems.order_id = '.$order_id;
            }

            // Query
            if ( ovabrw_wc_custom_orders_table_enabled() ) {
                $result = $wpdb->get_col("
                    SELECT DISTINCT o.id
                    FROM {$wpdb->prefix}wc_orders AS o
                    LEFT JOIN {$wpdb->prefix}woocommerce_order_items AS oitems
                    ON o.id = oitems.order_id
                    LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS oitem_meta
                    ON oitems.order_item_id = oitem_meta.order_item_id
                    WHERE oitems.order_item_type = 'line_item'
                    AND oitem_meta.meta_key = '_product_id'
                    AND o.status IN ( '" . implode( "','", $order_status ) . "' )
                    $product_query
                    $order_query
                ");
            } else {
                $result = $wpdb->get_col("
                    SELECT DISTINCT oitems.order_id
                    FROM {$wpdb->prefix}woocommerce_order_items AS oitems
                    LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS oitem_meta
                    ON oitems.order_item_id = oitem_meta.order_item_id
                    LEFT JOIN {$wpdb->posts} AS posts ON oitems.order_id = posts.ID
                    WHERE posts.post_type = 'shop_order'
                    AND oitems.order_item_type = 'line_item'
                    AND oitem_meta.meta_key = '_product_id'
                    AND posts.post_status IN ( '" . implode( "','", $order_status ) . "' )
                    $product_query
                    $order_query
                ");
            } // END

            // Loop Order IDs
            foreach ( $result as $key => $order_id ) {
                $order      = wc_get_order( $order_id );
                $fullname   = $order->get_formatted_billing_full_name();

                // Customer name
                if ( $customer_name && false === strpos( $fullname, $customer_name ) ) continue;

                // Get Meta Data type line_item of Order
                $items = $order->get_items( apply_filters( 'woocommerce_purchase_order_item_types', 'line_item' ) );

                // For Meta Data
                foreach ( $items as $item_id => $item ) {
                    $item_name  = $item->get_name();
                    $product_id = $item->get_product_id();
                    
                    // Get value of check-in, check-out
                    $item_checkin  = $item->get_meta( 'ovabrw_pickup_date' );
                    $item_checkout = $item->get_meta( 'ovabrw_pickoff_date' );

                    // Strtotime
                    $checkin_strtt  = strtotime( $item_checkin );
                    $checkout_strtt = strtotime( $item_checkout );

                    if ( $checkin_date && $checkout_date && ( $checkin_date > $checkout_strtt || $checkout_date < $checkin_strtt ) ) {
                        continue;
                    }

                    // Insurance status
                    $status_insurance   = '';
                    $insurance_amount   = $item->get_meta( 'ovabrw_insurance_amount' );
                    $order_insurance    = $order->get_meta( '_ova_insurance_amount' );

                    if ( $insurance_amount !== '' ) {
                        $insurance_amount   = floatval( $insurance_amount );
                        $order_insurance    = floatval( $order_insurance );

                        if ( $insurance_amount > 0 && $order_insurance ) {
                            $status_insurance .= '<mark class="ovabrw-order-status status-on-hold">';
                                $status_insurance .= '<span class="ovabrw-insurance-status">';
                                $status_insurance .= esc_html__( 'Received', 'ova-brw' );
                                $status_insurance .= '</span>';
                            $status_insurance .= '</mark>';
                        } else {
                            $status_insurance .= '<mark class="ovabrw-order-status status-processing">';
                                $status_insurance .= '<span class="ovabrw-insurance-status">';
                                $status_insurance .= esc_html__( 'Paid for Customers', 'ova-brw' );
                                $status_insurance .= '</span>';
                            $status_insurance .= '</mark>';
                        }
                    }

                    // Deposit status
                    $status_deposit = '';
                    $is_deposit     = $order->get_meta( '_ova_has_deposit' );

                    if ( $is_deposit ) {
                        $remaining_amount = $item->get_meta( 'ovabrw_remaining_amount' );

                        if ( $remaining_amount ) {
                            $is_remaining_invoice  = false;
                            $remaining_invoice_ids = $order->get_meta( '_ova_remaining_invoice_ids' );

                            // Check remaining invoice ids
                            if ( ! empty( $remaining_invoice_ids ) && is_array( $remaining_invoice_ids ) ) {
                                foreach ( $remaining_invoice_ids as $remaining_invoice_id ) {
                                    $order_remaining_invoice = wc_get_order( $remaining_invoice_id );

                                    if ( ! $order_remaining_invoice ) continue;

                                    $original_item_id = absint( $order_remaining_invoice->get_meta( '_ova_original_item_id' ) );

                                    if ( $original_item_id === $item_id ) {
                                        $is_remaining_invoice = true;
                                        break;
                                    }
                                }
                            }

                            if ( $is_remaining_invoice ) {
                                $status_deposit .= '<mark class="ovabrw-order-status status-processing">';
                                    $status_deposit .= '<span class="ovabrw-deposit-status">';
                                    $status_deposit .= esc_html__( 'Original Payment', 'ova-brw' );
                                    $status_deposit .= '</span>';
                                $status_deposit .= '</mark>';
                            } else {
                                $status_deposit .= '<mark class="ovabrw-order-status status-pending">';
                                    $status_deposit .= '<span class="ovabrw-deposit-status">';
                                    $status_deposit .= esc_html__( 'Partial Payment', 'ova-brw' );
                                    $status_deposit .= '</span>';
                                $status_deposit .= '</mark>';
                            }
                        } else {
                            $status_deposit .= '<mark class="ovabrw-order-status status-processing">';
                                $status_deposit .= '<span class="ovabrw-deposit-status">';
                                $status_deposit .= esc_html__( 'Full Payment', 'ova-brw' );
                                $status_deposit .= '</span>';
                            $status_deposit .= '</mark>';
                        }

                        // Remaining Invoice
                        $remaining_invoice_ids = $order->get_meta( '_ova_remaining_invoice_ids' );

                        if ( ! empty( $remaining_invoice_ids ) && is_array( $remaining_invoice_ids ) ) {
                            foreach ( $remaining_invoice_ids as $order_remaining_id ) {
                                $order_remaining_invoice = wc_get_order( $order_remaining_id );

                                if ( $order_remaining_invoice ) {
                                    $original_item_id = absint( $order_remaining_invoice->get_meta( '_ova_original_item_id' ) );

                                    if ( $original_item_id === $item_id ) {
                                        $status_deposit .= '<mark class="ovabrw-order-view">';
                                            $status_deposit .= '<a href="'.esc_url( $order_remaining_invoice->get_edit_order_url() ).'" class="button" target="_blank">';
                                            $status_deposit .= wp_kses_post( sprintf( __( 'Remaining Invoice #%1$s', 'ova-brw' ), $order_remaining_invoice->get_order_number() ) );
                                            $status_deposit .= '</a>';
                                        $status_deposit .= '</mark>';
                                        break;
                                    }
                                }
                            }
                        }
                    }

                    // Dates
                    $dates = $item_checkin;
                    $dates .= '<br>@<br>';
                    $dates .= $item_checkout;

                    // Parent Order
                    $parent_order_id = $item->get_meta( 'ovabrw_parent_order_id' );

                    if ( absint( $parent_order_id ) ) {
                        $dates = '<mark class="ovabrw-order-view">';
                            $dates .= '<a href="'.esc_url( get_edit_post_link( $parent_order_id ) ).'" class="button" target="_blank">';
                                $dates .= sprintf( __( 'Original Order #%1$s', 'ova-brw' ), $parent_order_id );
                            $dates .= '</a';
                        $dates .= '</mark>';
                    }

                    // Product link
                    $product_link = '<a href="'.get_edit_post_link( $product_id ).'" target="_blank">';
                    $product_link .= $item_name;
                    $product_link .= '</a>';

                    // Order link
                    if ( 'trash' == $order->get_status() ) {
                        $order_url = '<strong>#' . esc_attr( $order_id ) . '</strong>';
                    } else {
                        $order_url = '<a href="'.esc_url( $order->get_edit_order_url() ).'" target="_blank">' . esc_attr( $order_id ) . '</a>';
                        $order_url .= '<a href="#" class="order-preview" data-order-id="' . absint( $order_id ) . '" title="' . esc_attr( __( 'Preview', 'ova-brw' ) ) . '">' . esc_html( __( 'Preview', 'ova-brw' ) ) . '</a>';
                    }

                    // Add item data
                    $data[] = [
                        'order_id'            => $order_url,
                        'order_number'        => $order_id,
                        'customer_name'       => $order->get_formatted_billing_full_name(),
                        'dates'               => $dates,
                        'deposit_status'      => $status_deposit,
                        'insurance_status'    => $status_insurance,
                        'product'             => $product_link,
                        'order_status'        => $order->get_status()
                    ];
                }
            }
            // End Loop Order IDs

            function usort_reorder( $a, $b ) {
                $orderby    = ovabrw_get_meta_data( 'orderby', $_REQUEST, 'order_id' );
                $order      = ovabrw_get_meta_data( 'order', $_REQUEST, 'asc' );
                $result     = strcmp( $a[$orderby], $b[$orderby] );

                return 'asc' === $order ? -$result : $result;
            }
            usort( $data, 'usort_reorder' );

            $current_page   = $this->get_pagenum();
            $total_items    = count($data);
            $data           = array_slice($data,(($current_page-1)*$per_page),$per_page);
           
            $this->items    = $data;
            $this->set_pagination_args( array(
                'total_items' => $total_items,                  //WE have to calculate the total number of items
                'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
                'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
            ));
        }
    }
}