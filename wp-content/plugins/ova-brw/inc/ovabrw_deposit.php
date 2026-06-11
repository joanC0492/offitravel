<?php defined( 'ABSPATH' ) || exit();

/**
 * Class OVABRW_Deposit
 */
if ( !class_exists( 'OVABRW_Deposit' ) ) {
    class OVABRW_Deposit {
        /**
         * Constructor.
         */
        public function __construct() {
            // Cart item subtotal
            add_filter( 'woocommerce_cart_item_subtotal', [ $this, 'ovabrw_cart_item_subtotal' ], 11, 3 );

            // Cart totals before order total
            add_action( 'woocommerce_cart_totals_before_order_total', [ $this, 'ovabrw_cart_totals_before_order_total' ] );

            // Review order after order total
            add_action( 'woocommerce_review_order_before_order_total', [ $this, 'ovabrw_cart_totals_before_order_total' ] );

            // Cart totals order total html
            add_filter( 'woocommerce_cart_totals_order_total_html', [ $this, 'ovabrw_cart_totals_order_total_html' ] );

            // Checkout order processed - Save order meta fields
            add_action( 'woocommerce_checkout_order_processed', [ $this, 'ovabrw_checkout_order_processed' ], 10, 3 );

            // Checkout order processed - Cart and Checkout Blocks
            add_action( 'woocommerce_store_api_checkout_order_processed', [ $this, 'store_api_checkout_order_processed' ] );

            // Order formatted line subtotal
            add_filter( 'woocommerce_order_formatted_line_subtotal', [ $this, 'ovabrw_order_formatted_line_subtotal' ], 11, 3 );

            // Hide meta item fields
            add_filter( 'woocommerce_order_item_get_formatted_meta_data', [ $this, 'ovabrw_order_item_get_formatted_meta_data' ], 11, 2 );

            // Get order item totals
            add_filter( 'woocommerce_get_order_item_totals', [ $this, 'ovabrw_get_order_item_totals' ], 10, 3 );

            // Admin order item headers
            add_action( 'woocommerce_admin_order_item_headers', [ $this, 'ovabrw_admin_order_item_headers' ] );
            
            // Admin order item values
            add_action( 'woocommerce_admin_order_item_values', [ $this, 'ovabrw_admin_order_item_values' ], 10, 3 );

            // Admin order totals after tax
            add_action( 'woocommerce_admin_order_totals_after_tax', [ $this, 'ovabrw_admin_order_totals_after_tax' ] );

            // Button pay full and create remaining invoice
            add_action( 'woocommerce_after_order_itemmeta', [ $this, 'ovabrw_after_order_itemmeta' ], 10, 3 );

            // Action pay full and create remaining invoice
            add_action( 'admin_init', [ $this, 'ovabrw_order_item_action' ] );

            // Saved order items
            add_action( 'woocommerce_saved_order_items', [ $this, 'ovabrw_saved_order_items' ], 10, 2 );

            // Hidden order itemmeta
            add_filter( 'woocommerce_hidden_order_itemmeta', [ $this, 'ovabrw_hidden_order_itemmeta' ] );
            
            // Manager order - Add custom column
            add_filter( 'manage_edit-shop_order_columns', [ $this, 'ovabrw_add_custom_columns' ] );
            add_filter( 'woocommerce_shop_order_list_table_columns', [ $this, 'ovabrw_add_custom_columns' ] );
            add_action( 'manage_shop_order_posts_custom_column', [ $this, 'ovabrw_posts_custom_column' ], 10, 2 );
            add_action( 'manage_woocommerce_page_wc-orders_custom_column', [ $this, 'ovabrw_posts_custom_column' ], 10, 2 );

            // Before saved order items
            add_action( 'woocommerce_before_save_order_item', [ $this, 'before_save_order_item' ] );

            add_filter( 'woocommerce_email_styles', [ $this, 'email_styles' ] );
        }

        /**
         * Cart item total
         */
        public function ovabrw_cart_item_subtotal( $product_subtotal, $cart_item, $cart_item_key ) {
            if ( !$cart_item['data']->is_type( OVABRW_RENTAL ) ) return $product_subtotal;

            // Product id
            $product_id = $cart_item['data']->get_id();
            if ( !$product_id ) return $product_subtotal;

            // Item subtotal
            $item_subtotal = $product_subtotal;

            // Number of adutls
            $numberof_adults = (int)ovabrw_get_meta_data( 'ovabrw_adults', $cart_item );

            // Number of children
            $numberof_children = (int)ovabrw_get_meta_data( 'ovabrw_childrens', $cart_item );

            // Number of babies
            $numberof_babies = (int)ovabrw_get_meta_data( 'ovabrw_babies', $cart_item );

            // Get resources
            $resources = ovabrw_get_meta_data( 'ovabrw_resources', $cart_item );

            // Get resource guests
            $resource_guests = ovabrw_get_meta_data( 'ovabrw_resource_guests', $cart_item );

            // Get resource html
            $resource_html = ovabrw_get_html_resources( $product_id, $resources, $numberof_adults, $numberof_children, $numberof_babies, false, $resource_guests );

            // Get services
            $services = ovabrw_get_meta_data( 'ovabrw_services', $cart_item );

            // Get service guests
            $service_guests = ovabrw_get_meta_data( 'ovabrw_service_guests', $cart_item );

            // Get service html
            $service_html = ovabrw_get_html_services( $product_id, $services, $numberof_adults, $numberof_children, $numberof_babies, false, $service_guests );

            // Get cckf
            $cckf = ovabrw_get_meta_data( 'custom_ckf', $cart_item );

            // Get cckf quantity
            $cckf_qty = ovabrw_get_meta_data( 'cckf_qty', $cart_item );

            // Get HTML custom checkout fields
            $cckf_html = ovabrw_get_html_cckf( $cckf, false, $cckf_qty );

            // Check exist resource_html and service_html
            $extra_html = ovabrw_get_html_extra( $resource_html, $service_html, $cckf_html );

            // Is deposit
            $is_deposit = $cart_item['data']->get_meta( 'is_deposit' );
            if ( $is_deposit ) {
                // Get total payable
                $total_payable = $cart_item['data']->get_meta( 'total_payable' );

                // Subtotal
                if ( $total_payable ) {
                    $deposit_type   = $cart_item['data']->get_meta( 'deposit_type' );
                    $deposit_value  = $cart_item['data']->get_meta( 'deposit_value' );

                    // Taxable
                    if ( $cart_item['data']->is_taxable() ) {
                        if ( WC()->cart->display_prices_including_tax() ) {
                            if ( !wc_prices_include_tax() && WC()->cart->get_subtotal_tax() > 0 ) {
                                $total_payable = wc_get_price_including_tax( $cart_item['data'], [
                                    'price' => $total_payable
                                ]);
                            }
                        } else {
                            if ( wc_prices_include_tax() && WC()->cart->get_subtotal_tax() > 0 ) {
                                $total_payable = wc_get_price_excluding_tax( $cart_item['data'], [ 'price' => $total_payable ]);
                            }
                        }
                    }

                    if ( 'percent' === $deposit_type ) {
                        $item_subtotal .= ' <small class="tax_label">' . sprintf( esc_html__( '(%s%% deposit of %s)', 'ova-brw' ), $deposit_value, ovabrw_wc_price( $total_payable, [], false ) ) . '</small>';
                    } else {
                        $item_subtotal .= ' <small class="tax_label">' . sprintf( esc_html__( '(deposit of %s)', 'ova-brw' ), ovabrw_wc_price( $total_payable, [], false ) ) . '</small>';
                    }
                }

                $item_subtotal .= '<dl class="variation">';

                // Extra Price HTML
                $item_subtotal .= $resource_html;
                $item_subtotal .= $service_html;
                $item_subtotal .= $cckf_html;

                // Deposit
                $item_subtotal .= $this->get_cart_item_subdeposit( $cart_item );

                // Remaining
                $item_subtotal .= $this->get_cart_item_subremaining( $cart_item );

                // Total payable
                $item_subtotal .= $this->get_cart_item_subtotal_payable( $cart_item );

                $item_subtotal .= '</dl>';
            } else {
                // Insurance
                $insurance_amount = floatval( $cart_item['data']->get_meta( 'insurance_amount' ) );

                // View insurance
                if ( $insurance_amount ) {
                    $item_subtotal .= '<dl class="variation">';
                    $item_subtotal .= '<dt>'.esc_html__( 'Insurance Fee:', 'ova-brw' ).'</dt>';
                    $item_subtotal .= '<dd>'.ovabrw_wc_price( ovabrw_get_insurance_inclusive_tax( $insurance_amount ), [], false ).'</dd>';
                    $item_subtotal .= '</dl>';
                }

                // Extra Price HTML
                $item_subtotal .= $extra_html;
            }

            return apply_filters( OVABRW_PREFIX.'cart_item_subtotal', $item_subtotal, $product_subtotal, $cart_item, $cart_item_key );
        }

        /**
         * Get cart item subdeposit
         */
        public function get_cart_item_subdeposit( $cart_item ) {
            // init
            $deposit_html = '';

            // Check tour product
            if ( !$cart_item['data']->is_type( OVABRW_RENTAL ) ) return $deposit_html;

            // Deposit amount
            $deposit_amount = floatval( $cart_item['data']->get_meta( 'deposit_amount' ) );

            if ( $deposit_amount ) {
                // Insurance string
                $insurance_string = '';

                // Tax string
                $tax_string = '';

                // Deposit HTML
                $deposit_html .= '<dt>'.esc_html__( 'Deposit:', 'ova-brw' ).'</dt>';
                $deposit_html .= '<dd>';

                // Convert price
                $deposit_price = ovabrw_wc_price( $deposit_amount, [], false );

                // Get insurance amount
                $insurance_amount = floatval( $cart_item['data']->get_meta( 'insurance_amount' ) );
                if ( $insurance_amount ) {
                    $insurance_amount   = ovabrw_get_insurance_inclusive_tax( $insurance_amount );
                    $deposit_price      = ovabrw_wc_price( $deposit_amount + $insurance_amount, [], false );
                    $insurance_string   = ' <small class="tax_label">' . sprintf( esc_html__( '(incl. %s insurance fee)', 'ova-brw' ), ovabrw_wc_price( $insurance_amount, [], false ) ) . '</small>';
                }

                // Taxable
                if ( $cart_item['data']->is_taxable() ) {
                    if ( WC()->cart->display_prices_including_tax() ) {
                        if ( ! wc_prices_include_tax() && WC()->cart->get_subtotal_tax() > 0 ) {
                            $row_price = wc_get_price_including_tax( $cart_item['data'], [
                                'price' => $deposit_amount
                            ]);
                            $row_price += $insurance_amount;

                            // Deposit price
                            $deposit_price  = ovabrw_wc_price( $row_price, [], false );
                            $tax_string     = ' <small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>';
                        }
                    } else {
                        if ( wc_prices_include_tax() && WC()->cart->get_subtotal_tax() > 0 ) {
                            $row_price = wc_get_price_excluding_tax( $cart_item['data'], [
                                'price' => $deposit_amount
                            ]);
                            $row_price += $insurance_amount;

                            // Deposit price
                            $deposit_price  = ovabrw_wc_price( $row_price, [], false );
                            $tax_string     = ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
                        }
                    }
                }

                $deposit_html .= $deposit_price.$insurance_string.$tax_string;
                $deposit_html .= '</dd>';
            }

            return apply_filters( OVABRW_PREFIX.'get_cart_item_subdeposit', $deposit_html, $cart_item );
        }

        /**
         * Get cart item subremaining
         */
        public function get_cart_item_subremaining( $cart_item ) {
            // init
            $remaining_html = '';

            // Check tour product
            if ( !$cart_item['data']->is_type( OVABRW_RENTAL ) ) return $remaining_html;

            // Remaining amount
            $remaining_amount = floatval( $cart_item['data']->get_meta( 'remaining_amount' ) );

            if ( $remaining_amount ) {
                // Insurance string
                $insurance_string = '';

                // Tax string
                $tax_string = '';

                // Remaining HTML
                $remaining_html .= '<dt>'.esc_html__( 'Remaining:', 'ova-brw' ).'</dt>';
                $remaining_html .= '<dd>';

                // Convert price
                $remaining_price = ovabrw_wc_price( $remaining_amount, [], false );

                // Get insurance amount
                $insurance_amount = floatval( $cart_item['data']->get_meta( 'remaining_insurance' ) );
                if ( $insurance_amount ) {
                    $insurance_amount   = ovabrw_get_insurance_inclusive_tax( $insurance_amount );
                    $remaining_price    = ovabrw_wc_price( $remaining_amount + $insurance_amount, [], false );
                    $insurance_string   = ' <small class="tax_label">' . sprintf( esc_html__( '(incl. %s insurance fee)', 'ova-brw' ), ovabrw_wc_price( $insurance_amount, [], false ) ) . '</small>';
                }

                // Taxable
                if ( $cart_item['data']->is_taxable() ) {
                    if ( WC()->cart->display_prices_including_tax() ) {
                        if ( ! wc_prices_include_tax() && WC()->cart->get_subtotal_tax() > 0 ) {
                            $row_price = wc_get_price_including_tax( $cart_item['data'], [
                                'price' => $remaining_amount
                            ]);

                            $row_price          += $insurance_amount;
                            $remaining_price    = ovabrw_wc_price( $row_price, [], false );
                            $tax_string         = ' <small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>';
                        }
                    } else {
                        if ( wc_prices_include_tax() && WC()->cart->get_subtotal_tax() > 0 ) {
                            $row_price = wc_get_price_excluding_tax( $cart_item['data'], [
                                'price' => $remaining_amount
                            ]);

                            $row_price          += $insurance_amount;
                            $remaining_price    = ovabrw_wc_price( $row_price, [], false );
                            $tax_string         = ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
                        }
                    }
                }

                $remaining_html .= $remaining_price.$insurance_string.$tax_string;
                $remaining_html .= '</dd>';
            }

            return apply_filters( OVABRW_PREFIX.'get_cart_item_subremaining', $remaining_html, $cart_item );
        }

        /**
         * Get cart item subtotal payable
         */
        public function get_cart_item_subtotal_payable( $cart_item ) {
            // init
            $payable_html = '';

            // Check tour product
            if ( !$cart_item['data']->is_type( OVABRW_RENTAL ) ) return $payable_html;

            // Deposit amount
            $deposit_amount = floatval( $cart_item['data']->get_meta( 'deposit_amount' ) );

            // Remaining amount
            $remaining_amount = floatval( $cart_item['data']->get_meta( 'remaining_amount' ) );

            if ( $deposit_amount || $remaining_amount ) {
                // Get insurance amount
                $insurance_amount = floatval( $cart_item['data']->get_meta( 'insurance_amount' ) );
                if ( $insurance_amount ) {
                    $insurance_amount = ovabrw_get_insurance_inclusive_tax( $insurance_amount );
                }

                // Get remaining insurance amount
                $remaining_insurance = floatval( $cart_item['data']->get_meta( 'remaining_insurance' ) );
                if ( $remaining_insurance ) {
                    $remaining_insurance = ovabrw_get_insurance_inclusive_tax( $remaining_insurance );
                }

                // Payable HTML
                $payable_html .= '<dt>'.esc_html__( 'Total payable:', 'ova-brw' ).'</dt>';
                $payable_html .= '<dd>';

                // Convert price
                $total_payable = ovabrw_wc_price( $deposit_amount + $remaining_amount + $insurance_amount + $remaining_insurance, [], false );

                // Taxable
                $tax_string = '';

                if ( $cart_item['data']->is_taxable() ) {
                    if ( WC()->cart->display_prices_including_tax() ) {
                        if ( ! wc_prices_include_tax() && WC()->cart->get_subtotal_tax() > 0 ) {
                            // Row deposit
                            $row_deposit = round( wc_get_price_including_tax( $cart_item['data'], [
                                'price' => $deposit_amount
                            ]), wc_get_price_decimals() );

                            // Row remaining
                            $row_remaining = round( wc_get_price_including_tax( $cart_item['data'], [
                                'price' => $remaining_amount
                            ]), wc_get_price_decimals() );

                            // Row price
                            $row_price = $row_deposit + $row_remaining + $insurance_amount + $remaining_insurance;

                            // Total payable
                            $total_payable = ovabrw_wc_price( $row_price, [], false );

                            // String tax
                            $tax_string = ' <small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>';
                        }
                    } else {
                        if ( wc_prices_include_tax() && WC()->cart->get_subtotal_tax() > 0 ) {
                            // Row deposit
                            $row_deposit = round( wc_get_price_excluding_tax( $cart_item['data'], [
                                'price' => $deposit_amount
                            ]), wc_get_price_decimals() );

                            // Row remaining
                            $row_remaining = round( wc_get_price_excluding_tax( $cart_item['data'], [
                                'price' => $remaining_amount
                            ]), wc_get_price_decimals() );

                            // Row price
                            $row_price = $row_deposit + $row_remaining + $insurance_amount + $remaining_insurance;

                            // Total payable
                            $total_payable = ovabrw_wc_price( $row_price, [], false );

                            // String tax
                            $tax_string = ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
                        }
                    }
                }

                $payable_html .= $total_payable.$tax_string;
                $payable_html .= '</dd>';
            }

            return apply_filters( OVABRW_PREFIX.'get_cart_item_subtotal_payable', $payable_html, $cart_item );
        }

        /**
         * Cart totals before order total
         */
        public function ovabrw_cart_totals_before_order_total() {
            // Has deposit
            $has_deposit = isset( WC()->cart->deposit_info[ 'has_deposit' ] ) ? WC()->cart->deposit_info[ 'has_deposit' ] : false;

            if ( $has_deposit ) {
                // Deposit amount
                $deposit_amount = isset( WC()->cart->deposit_info[ 'deposit_amount' ] ) ? floatval( WC()->cart->deposit_info[ 'deposit_amount' ] ) : 0;

                // Remaining amount
                $remaining_amount = isset( WC()->cart->deposit_info[ 'remaining_amount' ] ) ? floatval( WC()->cart->deposit_info[ 'remaining_amount' ] ) : 0;

                // Remaining tax
                $remaining_tax = isset( WC()->cart->deposit_info[ 'remaining_tax' ] ) ? floatval( WC()->cart->deposit_info[ 'remaining_tax' ] ) : 0;

                // Remaining insurance amount
                $remaining_insurance_amount = isset( WC()->cart->deposit_info[ 'remaining_insurance' ] ) ? floatval( WC()->cart->deposit_info[ 'remaining_insurance' ] ) : 0;

                // Remaining insurance tax
                $remaining_insurance_tax = isset( WC()->cart->deposit_info[ 'remaining_insurance_tax' ] ) ? floatval( WC()->cart->deposit_info[ 'remaining_insurance_tax' ] ) : 0;
            ?>
                <tr class="order-paid">
                    <th><?php esc_html_e( 'Deposit','ova-brw' ); ?></th>
                    <td data-title="<?php esc_html_e( 'Deposit','ova-brw' ); ?>">
                        <?php echo wp_kses_post( $this->ovabrw_cart_totals_deposit_amount_html() ); ?>
                    </td>
                </tr>
                <?php if ( $remaining_amount ): ?>
                    <tr class="order-remaining">
                        <th><?php esc_html_e( 'Remaining','ova-brw' ); ?></th>
                        <td data-title="<?php esc_html_e( 'Remaining','ova-brw' ); ?>">
                            <?php echo wp_kses_post( $this->ovabrw_cart_totals_remaining_amount_html( $remaining_amount, $remaining_tax, $remaining_insurance_amount, $remaining_insurance_tax ) ); ?>
                        </td>
                    </tr>
                <?php endif;
            }
        }

        /**
         * Get cart totals deposit amount HTML
         */
        public function ovabrw_cart_totals_deposit_amount_html() {
            // init
            $value = '<strong>' . WC()->cart->get_total() . '</strong> ';

            if ( wc_tax_enabled() && WC()->cart->display_prices_including_tax() ) {
                $tax_string_array = [];
                $cart_tax_totals  = WC()->cart->get_tax_totals();

                if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) {
                    foreach ( $cart_tax_totals as $code => $tax ) {
                        $tax_string_array[] = sprintf( '%s %s', $tax->formatted_amount, $tax->label );
                    }
                } elseif ( !empty( $cart_tax_totals ) ) {
                    $tax_string_array[] = sprintf( '%s %s', wc_price( WC()->cart->get_taxes_total( true, true ) ), WC()->countries->tax_or_vat() );
                }

                if ( !empty( $tax_string_array ) ) {
                    $taxable_address = WC()->customer->get_taxable_address();

                    if ( WC()->customer->is_customer_outside_base() && !WC()->customer->has_calculated_shipping() ) {
                        $country = WC()->countries->estimated_for_prefix( $taxable_address[0] ) . WC()->countries->countries[ $taxable_address[0] ];
                        $tax_text = wp_kses_post( sprintf( __( '(includes %1$s estimated for %2$s)', 'ova-brw' ), implode( ', ', $tax_string_array ), $country ) );
                    } else {
                        $tax_text = wp_kses_post( sprintf( __( '(includes %s)', 'ova-brw' ), implode( ', ', $tax_string_array ) ) );
                    }

                    $value .= '<small class="includes_tax">' . $tax_text . '</small>';
                }
            }

            return apply_filters( OVABRW_PREFIX.'cart_totals_deposit_amount_html', $value );
        }

        /**
         * Get cart totals remaining amount HTML
         */
        public function ovabrw_cart_totals_remaining_amount_html( $remaining_amount, $remaining_tax, $insurance_amount, $insurance_tax ) {
            // Insurance string
            $insurance_string = '';

            // Insurance amount
            if ( $insurance_amount ) {
                $remaining_amount += floatval( $insurance_amount );
                $insurance_string = ' <small class="includes_tax">';

                if ( WC()->cart->display_prices_including_tax() ) {
                    if ( ovabrw_insurance_tax_enabled() && $insurance_tax ) {
                        $insurance_amount += $insurance_tax;
                    }
                    $insurance_string .= sprintf( __( '(includes %s insurance fee)', 'ova-brw' ), ovabrw_wc_price( $insurance_amount, [], false ) );
                } else {
                    $insurance_string .= sprintf( __( '(includes %s insurance fee)', 'ova-brw' ), ovabrw_wc_price( $insurance_amount, [], false ) );
                }

                $insurance_string .= '</small>';
            }

            // Taxable
            $tax_text = '';
            if ( wc_tax_enabled() && $remaining_tax ) {
                // Insurance tax amount
                if ( ovabrw_insurance_tax_enabled() && $insurance_tax ) {
                    $remaining_tax += floatval( $insurance_tax );
                }

                // Tax string
                $tax_string = sprintf( '%s %s', wc_price( $remaining_tax ), WC()->countries->tax_or_vat() );

                if ( wc_prices_include_tax() ) {
                    // Insurance tax amount
                    if ( ovabrw_insurance_tax_enabled() && $insurance_tax ) {
                        $remaining_amount += floatval( $insurance_tax );
                    }

                    if ( WC()->cart->display_prices_including_tax() ) {
                        $value      = '<strong>'.ovabrw_wc_price( $remaining_amount, [], false ).'</strong>';
                        $tax_text   = wp_kses_post( sprintf( __( '(includes %s)', 'ova-brw' ), $tax_string ) );
                    } else {
                        $remaining_amount -= $remaining_tax;

                        $value      = '<strong>'.ovabrw_wc_price( $remaining_amount, [], false ).'</strong>';
                        $tax_text   = wp_kses_post( sprintf( __( '(excludes %s)', 'ova-brw' ), $tax_string ) );
                    }
                } else {
                    if ( WC()->cart->display_prices_including_tax() ) {
                        $remaining_amount += $remaining_tax;

                        $value      = '<strong>'.ovabrw_wc_price( $remaining_amount, [], false ).'</strong>';
                        $tax_text   = wp_kses_post( sprintf( __( '(includes %s)', 'ova-brw' ), $tax_string ) );
                    } else {
                        $value      = '<strong>'.ovabrw_wc_price( $remaining_amount, [], false ).'</strong>';
                        $tax_text   = wp_kses_post( sprintf( __( '(excludes %s)', 'ova-brw' ), $tax_string ) );
                    }
                }
            } else {
                $value = '<strong>'.ovabrw_wc_price( $remaining_amount, [], false ).'</strong>';
            }

            $value .= $insurance_string;
            if ( $tax_text ) $value .= ' <small class="includes_tax">' . $tax_text . '</small>';
            
            return apply_filters( OVABRW_PREFIX.'cart_totals_remaining_amount_html', $value );
        }

        /**
         * Get Cart total order total payable
         */
        public function ovabrw_cart_totals_order_total_html( $value ) {
            // Get has deposit
            $has_deposit = isset( WC()->cart->deposit_info[ 'has_deposit' ] ) ? WC()->cart->deposit_info[ 'has_deposit' ] : false;

            if ( $has_deposit ) {
                // Order total
                $order_totals   = WC()->cart->get_totals();
                $order_total    = isset( $order_totals['total'] ) ? round( $order_totals['total'], wc_get_price_decimals() ) : 0;

                // Remaining
                $remaining_amount   = isset( WC()->cart->deposit_info[ 'remaining_amount' ] ) ? floatval( WC()->cart->deposit_info[ 'remaining_amount' ] ) : 0;
                $remaining_amount   = ovabrw_convert_price( $remaining_amount, [], false );
                $order_total        += floatval( $remaining_amount );

                // Remaining insurance
                $remaining_insurance = isset( WC()->cart->deposit_info[ 'remaining_insurance' ] ) ? floatval( WC()->cart->deposit_info[ 'remaining_insurance' ] ) : 0;
                $remaining_insurance = ovabrw_convert_price( $remaining_insurance, [], false );
                $order_total         += $remaining_insurance;

                if ( wc_tax_enabled() ) {
                    // Total tax
                    $total_tax = isset( $order_totals['total_tax'] ) ? round( $order_totals['total_tax'], wc_get_price_decimals() ) : 0;

                    // Get remaining tax amount
                    $remaining_tax = isset( WC()->cart->deposit_info[ 'remaining_tax' ] ) ? floatval( WC()->cart->deposit_info[ 'remaining_tax' ] ) : 0;

                    // Get remaining insurance tax amount
                    $remaining_insurance_tax = isset( WC()->cart->deposit_info[ 'remaining_insurance_tax' ] ) ? floatval( WC()->cart->deposit_info[ 'remaining_insurance_tax' ] ) : 0;
                    if ( ovabrw_insurance_tax_enabled() && $remaining_insurance_tax ) {
                        $remaining_tax += $remaining_insurance_tax;
                    }

                    if ( wc_prices_include_tax() ) {
                        // Remaining insurance tax amount
                        if ( ovabrw_insurance_tax_enabled() && $remaining_insurance_tax ) {
                            $order_total += $remaining_insurance_tax;
                        }

                        if ( WC()->cart->display_prices_including_tax() ) {
                            $total_tax  += $remaining_tax;

                            $value      = '<strong>' . wc_price( $order_total ) . '</strong> ';
                            $tax_string = sprintf( '%s %s', wc_price( $total_tax ), WC()->countries->tax_or_vat() );
                            $tax_text   = wp_kses_post( sprintf( __( '(includes %s)', 'ova-brw' ), $tax_string ) );
                            $value      .= ' <small class="includes_tax">' . $tax_text . '</small>';
                        } else {
                            $order_total -= $remaining_tax;

                            $value      = '<strong>'.wc_price( $order_total ).'</strong>';
                            $tax_string = sprintf( '%s %s', wc_price( $remaining_tax ), WC()->countries->tax_or_vat() );
                            $tax_text   = wp_kses_post( sprintf( __( '(excludes %s)', 'ova-brw' ), $tax_string ) );
                            $value      .= ' <small class="includes_tax">' . $tax_text . '</small>';
                        }
                    } else {
                        if ( WC()->cart->display_prices_including_tax() ) {
                            $order_total    += $remaining_tax;
                            $total_tax      += $remaining_tax;

                            $value      = '<strong>'.wc_price( $order_total ).'</strong>';
                            $tax_string = sprintf( '%s %s', wc_price( $total_tax ), WC()->countries->tax_or_vat() );
                            $tax_text   = wp_kses_post( sprintf( __( '(includes %s)', 'ova-brw' ), $tax_string ) );
                            $value      .= ' <small class="includes_tax">' . $tax_text . '</small>';
                        } else {
                            $value      = '<strong>'.wc_price( $order_total ).'</strong>';
                            $tax_string = sprintf( '%s %s', wc_price( $remaining_tax ), WC()->countries->tax_or_vat() );
                            $tax_text   = wp_kses_post( sprintf( __( '(excludes %s)', 'ova-brw' ), $tax_string ) );
                            $value      .= ' <small class="includes_tax">' . $tax_text . '</small>';
                        }
                    }
                } else {
                    $value = '<strong>' . ovabrw_wc_price( $order_total, [], false ) . '</strong> ';
                }
            }

            return apply_filters( OVABRW_PREFIX.'cart_totals_order_total_html', $value );
        }

        /**
         * Checkout order processed - Save order meta fields
         */
        public function ovabrw_checkout_order_processed( $order_id, $posted_data, $order ) {
            // Has deposit
            $has_deposit = isset( WC()->cart->deposit_info[ 'has_deposit' ] ) ? WC()->cart->deposit_info[ 'has_deposit' ] : '';

            if ( $has_deposit ) {
                // Get deposit amount
                $deposit_amount = isset( WC()->cart->deposit_info['deposit_amount'] ) ? floatval( WC()->cart->deposit_info['deposit_amount'] ) : 0;

                // Get remaining amount
                $remaining_amount = isset( WC()->cart->deposit_info['remaining_amount'] ) ? floatval( WC()->cart->deposit_info['remaining_amount'] ) : 0;

                // Get remaining tax
                $remaining_tax = isset( WC()->cart->deposit_info[ 'remaining_tax' ] ) ? floatval( WC()->cart->deposit_info[ 'remaining_tax' ] ) : 0;

                // Get remaining insurance
                $remaining_insurance = isset( WC()->cart->deposit_info[ 'remaining_insurance' ] ) ? floatval( WC()->cart->deposit_info[ 'remaining_insurance' ] ) : 0;

                // Get remaining insurance tax
                $remaining_insurance_tax = isset( WC()->cart->deposit_info[ 'remaining_insurance_tax' ] ) ? floatval( WC()->cart->deposit_info[ 'remaining_insurance_tax' ] ) : 0;

                // Prices include tax
                if ( wc_prices_include_tax() ) {
                    $order->add_meta_data( '_ova_prices_include_tax', 1, true );
                }

                // Add has deposit
                $order->add_meta_data( '_ova_has_deposit', 1, true );
                
                // Add deposit amount
                if ( $deposit_amount ) {
                    $order->add_meta_data( '_ova_deposit_amount', ovabrw_convert_price( $deposit_amount, [], false ), true );
                }

                // Add remaining amount
                if ( $remaining_amount ) {
                    $order->add_meta_data( '_ova_remaining_amount', ovabrw_convert_price( $remaining_amount, [], false ), true );
                }

                // Add remaining tax
                if ( $remaining_tax ) {
                    $order->add_meta_data( '_ova_remaining_tax', $remaining_tax, true );
                }

                // Add remaining insurance
                if ( $remaining_insurance ) {
                    $order->add_meta_data( '_ova_remaining_insurance', $remaining_insurance, true );
                }

                // Add remaining insurance tax
                if ( $remaining_insurance_tax ) {
                    $order->add_meta_data( '_ova_remaining_insurance_tax', $remaining_insurance_tax, true );
                }
            } else {
                $order->delete_meta_data( '_ova_has_deposit' );
                $order->delete_meta_data( '_ova_deposit_amount' );
                $order->delete_meta_data( '_ova_remaining_amount' );
                $order->delete_meta_data( '_ova_remaining_tax' );
                $order->delete_meta_data( '_ova_remaining_insurance' );
                $order->delete_meta_data( '_ova_remaining_insurance_tax' );
            }

            // Get insurance tax
            $insurance_tax = isset( WC()->cart->deposit_info[ 'insurance_tax' ] ) ? WC()->cart->deposit_info[ 'insurance_tax' ] : 0;

            // Get insurance amount
            $insurance_amount = isset( WC()->cart->deposit_info[ 'insurance_amount' ] ) ? WC()->cart->deposit_info[ 'insurance_amount' ] : 0;
            if ( $insurance_amount ) {
                $order->add_meta_data( '_ova_insurance_amount', ovabrw_convert_price( $insurance_amount, [], false ), true );

                // Add insurance tax
                if ( $insurance_tax ) {
                    $order->add_meta_data( '_ova_insurance_tax', $insurance_tax, true );
                }
            } else {
                $order->delete_meta_data( '_ova_insurance_amount' );
                $order->delete_meta_data( '_ova_insurance_tax' );
            }

            // Save
            $order->save();
        }

        /**
         * Checkout order processed - Cart and Checkout Blocks
         */
        public function store_api_checkout_order_processed( $order ) {
            // Has deposit
            $has_deposit = isset( WC()->cart->deposit_info[ 'has_deposit' ] ) ? WC()->cart->deposit_info[ 'has_deposit' ] : '';

            if ( $has_deposit ) {
                // Get deposit amount
                $deposit_amount = isset( WC()->cart->deposit_info['deposit_amount'] ) ? floatval( WC()->cart->deposit_info['deposit_amount'] ) : 0;

                // Get remaining amount
                $remaining_amount = isset( WC()->cart->deposit_info['remaining_amount'] ) ? floatval( WC()->cart->deposit_info['remaining_amount'] ) : 0;

                // Get remaining tax
                $remaining_tax = isset( WC()->cart->deposit_info[ 'remaining_tax' ] ) ? floatval( WC()->cart->deposit_info[ 'remaining_tax' ] ) : 0;

                // Get remaining insurance
                $remaining_insurance = isset( WC()->cart->deposit_info[ 'remaining_insurance' ] ) ? floatval( WC()->cart->deposit_info[ 'remaining_insurance' ] ) : 0;

                // Ger remaining insurance tax
                $remaining_insurance_tax = isset( WC()->cart->deposit_info[ 'remaining_insurance_tax' ] ) ? floatval( WC()->cart->deposit_info[ 'remaining_insurance_tax' ] ) : 0;

                // Prices include tax
                if ( wc_prices_include_tax() ) {
                    $order->add_meta_data( '_ova_prices_include_tax', 1, true );
                }

                // Add has deposit
                $order->add_meta_data( '_ova_has_deposit', 1, true );
                
                // Add deposit amount
                if ( $deposit_amount ) {
                    $order->add_meta_data( '_ova_deposit_amount', ovabrw_convert_price( $deposit_amount, [], false ), true );
                }

                // Add remaining amount
                if ( $remaining_amount ) {
                    $order->add_meta_data( '_ova_remaining_amount', ovabrw_convert_price( $remaining_amount, [], false ), true );
                }

                // Add remaining tax
                if ( $remaining_tax ) {
                    $order->add_meta_data( '_ova_remaining_tax', $remaining_tax, true );
                }

                // Add remaining insurance
                if ( $remaining_insurance ) {
                    $order->add_meta_data( '_ova_remaining_insurance', $remaining_insurance, true );
                }

                // Remaining insurance tax
                if ( $remaining_insurance_tax ) {
                    $order->add_meta_data( '_ova_remaining_insurance_tax', $remaining_insurance_tax, true );
                }
            } else {
                $order->delete_meta_data( '_ova_has_deposit' );
                $order->delete_meta_data( '_ova_deposit_amount' );
                $order->delete_meta_data( '_ova_remaining_amount' );
                $order->delete_meta_data( '_ova_remaining_tax' );
                $order->delete_meta_data( '_ova_remaining_insurance' );
                $order->delete_meta_data( '_ova_remaining_insurance_tax' );
            }

            // Get insurance tax
            $insurance_tax = isset( WC()->cart->deposit_info[ 'insurance_tax' ] ) ? WC()->cart->deposit_info[ 'insurance_tax' ] : 0;

            // Get insurance amount
            $insurance_amount = isset( WC()->cart->deposit_info[ 'insurance_amount' ] ) ? WC()->cart->deposit_info[ 'insurance_amount' ] : 0;
            if ( $insurance_amount ) {
                $order->add_meta_data( '_ova_insurance_amount', ovabrw_convert_price( $insurance_amount, [], false ), true );

                // Add insurance tax
                if ( $insurance_tax ) {
                    $order->add_meta_data( '_ova_insurance_tax', $insurance_tax, true );
                }
            } else {
                $order->delete_meta_data( '_ova_insurance_amount' );
                $order->delete_meta_data( '_ova_insurance_tax' );
            }

            // Save
            $order->save();
        }

        /**
         * Order formatted line subtotal
         */
        public function ovabrw_order_formatted_line_subtotal( $subtotal, $item, $order ) {
            // Get product
            $product = $item->get_product();

            // Check is tour product
            if ( !$product || !$product->is_type( OVABRW_RENTAL ) ) return $subtotal;

            // Order ID
            $order_id = $order->get_id();
            if ( !$order_id ) return $subtotal;

            // Product ID
            $product_id = $item->get_product_id();
            if ( !$product_id ) return $subtotal;

            // Number of adults
            $numberof_adults = (int)$item->get_meta( 'ovabrw_adults' );

            // Number of children
            $numberof_children = (int)$item->get_meta( 'ovabrw_childrens' );

            // Number of babies
            $numberof_babies = (int)$item->get_meta( 'ovabrw_babies' );

            // init
            $resource_html = $service_html = $cckf_html = '';

            // Resources
            $resources = $item->get_meta( 'ovabrw_resources' );
            if ( ovabrw_array_exists( $resources ) ) {
                // Get resource guests
                $resource_guests = $item->get_meta( 'ovabrw_resource_guests' );

                // Get HTML resources
                $resource_html = ovabrw_get_html_resources( $product_id, $resources, $numberof_adults, $numberof_children, $numberof_babies, $order_id, $resource_guests );
            }

            // Services
            $services = $item->get_meta( 'ovabrw_services' );
            if ( ovabrw_array_exists( $services ) ) {
                // Get service guests
                $service_guests = $item->get_meta( 'ovabrw_service_guests' );

                // Get HTML services
                $service_html = ovabrw_get_html_services( $product_id, $services, $numberof_adults, $numberof_children, $numberof_babies, $order_id, $service_guests );
            }

            // Custom checkout fields
            $custom_cckf = $item->get_meta( 'ovabrw_custom_ckf' );

            // Get custom checkout field quantity
            $cckf_qty = $item->get_meta( 'ovabrw_cckf_qty' );

            // Get HTML custom checkout fields
            if ( ovabrw_array_exists( $custom_cckf ) ) {
                $cckf_html = ovabrw_get_html_cckf( $custom_cckf, $order_id, $cckf_qty );
            }

            // Check exist resource_html and service_html
            $extra_html = ovabrw_get_html_extra( $resource_html, $service_html, $cckf_html );

            // Is deposit
            $is_deposit = $order->get_meta( '_ova_has_deposit' );
            if ( $is_deposit ) {
                // Get total payable
                $total_payable = floatval( $item->get_meta( 'ovabrw_total_payable' ) );
                
                if ( $total_payable ) {
                    $deposit_type   = $item->get_meta( 'ovabrw_deposit_type' );
                    $deposit_value  = $item->get_meta( 'ovabrw_deposit_value' );

                    if ( wc_tax_enabled() ) {
                        $prices_incl_tax    = $order->get_meta( '_ova_prices_include_tax' );
                        $tax_display        = get_option( 'woocommerce_tax_display_cart' );
                        $rates              = WC_Tax::get_rates( $item->get_tax_class() );

                        if ( $prices_incl_tax ) {
                            if ( 'excl' === $tax_display ) {
                                $incl_tax   = WC_Tax::calc_inclusive_tax( $total_payable, $rates );
                                $tax        = round( array_sum( $incl_tax ), wc_get_price_decimals() );
                                $total_payable -= $tax;
                            }
                        } else {
                            if ( 'incl' === $tax_display ) {
                                $excl_tax   = WC_Tax::calc_exclusive_tax( $total_payable, $rates );
                                $tax        = round( array_sum( $excl_tax ), wc_get_price_decimals() );
                                $total_payable += $tax;
                            }
                        }
                    }

                    if ( 'percent' === $deposit_type ) {
                        $subtotal .= ' <small class="tax_label">' . sprintf( esc_html__( '(%s%% deposit of %s)', 'ova-brw' ), $deposit_value, wc_price( $total_payable, [ 'currency' => $order->get_currency() ] ) ) . '</small>';
                    } else {
                        $subtotal .= ' <small class="tax_label">' . sprintf( esc_html__( '(deposit of %s)', 'ova-brw' ), wc_price( $total_payable, [ 'currency' => $order->get_currency() ] ) ) . '</small>';
                    }
                }

                $subtotal .= '<dl class="variation">';

                // Extra HTML
                $subtotal .= $resource_html;
                $subtotal .= $service_html;
                $subtotal .= $cckf_html;
                
                // Deposit
                $subtotal .= $this->get_order_formatted_line_subdeposit( $item, $order );

                // Remaining
                $subtotal .= $this->get_order_formatted_line_subremaining( $item, $order );

                // Total payable
                $subtotal .= $this->get_order_formatted_line_subtotal_payable( $item, $order );

                $subtotal .= '</dl>';
            } else {
                // Insurance
                $insurance_amount = floatval( $item->get_meta( 'ovabrw_insurance_amount' ) );

                if ( $insurance_amount ) {
                    $subtotal .= '<dl>';
                    $subtotal .= '<dt>'.esc_html__( 'Insurance fee:', 'ova-brw' ).'</dt>';
                    $subtotal .= '<dd>'.wc_price( ovabrw_get_insurance_inclusive_tax( $insurance_amount ), [ 'currency' => $order->get_currency() ] ).'</dd>';
                    $subtotal .= '</dl>';
                }

                // Extra HTML
                $subtotal .= $extra_html;
            }

            return apply_filters( OVABRW_PREFIX.'order_formatted_line_subtotal', $subtotal, $item, $order );
        }

        /**
         * Get order formatted line subdeposit
         */
        public function get_order_formatted_line_subdeposit( $item, $order ) {
            $deposit_html   = '';
            $deposit_amount = floatval( $item->get_meta( 'ovabrw_deposit_amount' ) );

            if ( $deposit_amount ) {
                // Insurance string
                $insurance_string = '';

                // Tax string
                $tax_string = '';

                // Deposit HTML
                $deposit_html .= '<dt>'.esc_html__( 'Deposit:', 'ova-brw' ).'</dt>';
                $deposit_html .= '<dd>';

                // Convert price
                $deposit_price = wc_price( $deposit_amount, [ 'currency' => $order->get_currency() ] );

                // Get insurance amount
                $insurance_amount = floatval( $item->get_meta( 'ovabrw_insurance_amount' ) );
                if ( $insurance_amount ) {
                    $insurance_amount   = ovabrw_get_insurance_inclusive_tax( $insurance_amount );
                    $deposit_price      = wc_price( $deposit_amount + $insurance_amount, [ 'currency' => $order->get_currency() ] );
                    $insurance_string   = ' <small class="tax_label">' . sprintf( esc_html__( '(incl. %s insurance fee)', 'ova-brw' ), wc_price( $insurance_amount, [ 'currency' => $order->get_currency() ] ) ) . '</small>';
                }

                // Taxable
                if ( wc_tax_enabled() ) {
                    $prices_incl_tax    = $order->get_meta( '_ova_prices_include_tax' );
                    $tax_display        = get_option( 'woocommerce_tax_display_cart' );
                    $item_tax           = $item->get_total_tax();

                    if ( $prices_incl_tax ) {
                        if ( 'excl' === $tax_display ) {
                            $deposit_price  = wc_price( $deposit_amount - $item_tax + $insurance_amount, [ 'currency' => $order->get_currency() ] );
                            $tax_string     = ' <small class="tax_label">' . esc_html__( '(ex. tax)', 'ova-brw' ) . '</small>';
                        }
                    } else {
                        if ( 'incl' === $tax_display ) {
                            $deposit_price  = wc_price( $deposit_amount + $item_tax + $insurance_amount, [ 'currency' => $order->get_currency() ] );
                            $tax_string     = ' <small class="tax_label">' . esc_html__( '(incl. tax)', 'ova-brw' ) . '</small>';
                        }
                    }
                }

                $deposit_html .= $deposit_price.$insurance_string.$tax_string;
                $deposit_html .= '</dd>';
            }

            return apply_filters( OVABRW_PREFIX.'get_order_formatted_line_subdeposit', $deposit_html, $item, $order );
        }

        /**
         * Get order formatted line subremaining
         */
        public function get_order_formatted_line_subremaining( $item, $order ) {
            $remaining_html     = '';
            $remaining_amount   = floatval( $item->get_meta( 'ovabrw_remaining_amount' ) );

            if ( $remaining_amount ) {
                // Insurance string
                $insurance_string = '';

                // Tax string
                $tax_string = '';

                // Remaining HTML
                $remaining_html .= '<dt>'.esc_html__( 'Remaining:', 'ova-brw' ).'</dt>';
                $remaining_html .= '<dd>';

                // Convert price
                $remaining_price = wc_price( $remaining_amount, [ 'currency' => $order->get_currency() ] );

                // Get insurance amount
                $insurance_amount = floatval( $item->get_meta( 'ovabrw_remaining_insurance' ) );
                if ( $insurance_amount ) {
                    $insurance_amount   = ovabrw_get_insurance_inclusive_tax( $insurance_amount );
                    $remaining_price    = wc_price( $remaining_amount + $insurance_amount, [ 'currency' => $order->get_currency() ] );
                    $insurance_string   = ' <small class="tax_label">' . sprintf( __( '(incl. %s insurance fee)', 'ova-brw' ), wc_price( $insurance_amount, [ 'currency' => $order->get_currency() ] ) ) . '</small>';
                }

                // Taxable
                if ( wc_tax_enabled() ) {
                    $prices_incl_tax    = $order->get_meta( '_ova_prices_include_tax' );
                    $tax_display        = get_option( 'woocommerce_tax_display_cart' );
                    $remaining_tax      = floatval( $item->get_meta( 'ovabrw_remaining_tax' ) );

                    if ( $prices_incl_tax ) {
                        if ( 'excl' === $tax_display ) {
                            $remaining_price    = wc_price( $remaining_amount - $remaining_tax + $insurance_amount, [ 'currency' => $order->get_currency() ] );
                            $tax_string         = ' <small class="tax_label">' . esc_html__( '(ex. tax)', 'ova-brw' ) . '</small>';
                        }
                    } else {
                        if ( 'incl' === $tax_display ) {
                            $remaining_price    = wc_price( $remaining_amount + $remaining_tax + $insurance_amount, [ 'currency' => $order->get_currency() ] );
                            $tax_string         = ' <small class="tax_label">' . esc_html__( '(incl. tax)', 'ova-brw' ) . '</small>';
                        }
                    }
                }

                $remaining_html .= $remaining_price.$insurance_string.$tax_string;
                $remaining_html .= '</dd>';
            }

            return apply_filters( OVABRW_PREFIX.'get_order_formatted_line_subremaining', $remaining_html, $item, $order );
        }

        /**
         * Get order formatted line subtotal payable
         */
        public function get_order_formatted_line_subtotal_payable( $item, $order ) {
            $payable_html   = '';
            $total_payable  = floatval( $item->get_meta( 'ovabrw_total_payable' ) );

            if ( $total_payable ) {
                // Tax string
                $tax_string = '';

                // Insurance amount
                $insurance_amount = floatval( $item->get_meta( 'ovabrw_insurance_amount' ) );
                if ( $insurance_amount ) {
                    $insurance_amount   = ovabrw_get_insurance_inclusive_tax( $insurance_amount );
                    $total_payable      += $insurance_amount;
                }

                // Remaining insurance amount
                $remaining_insurance = floatval( $item->get_meta( 'ovabrw_remaining_insurance' ) );
                if ( $remaining_insurance ) {
                    $remaining_insurance    = ovabrw_get_insurance_inclusive_tax( $remaining_insurance );
                    $total_payable          += $remaining_insurance;
                }

                // Payable HTML
                $payable_html .= '<dt>'.esc_html__( 'Total payable:', 'ova-brw' ).'</dt>';
                $payable_html .= '<dd>';

                // Convert price
                $payable_price = wc_price( $total_payable, [ 'currency' => $order->get_currency() ] );

                // Taxable
                if ( wc_tax_enabled() ) {
                    $prices_incl_tax    = $order->get_meta( '_ova_prices_include_tax' );
                    $tax_display        = get_option( 'woocommerce_tax_display_cart' );
                    $item_tax           = $item->get_total_tax();
                    $remaining_tax      = floatval( $item->get_meta( 'ovabrw_remaining_tax' ) );

                    if ( $prices_incl_tax ) {
                        if ( 'excl' === $tax_display ) {
                            $payable_price = wc_price( $total_payable - $item_tax - $remaining_tax, [ 'currency' => $order->get_currency() ] );
                            $tax_string = ' <small class="tax_label">' . esc_html__( '(ex. tax)', 'ova-brw' ) . '</small>';
                        }
                    } else {
                        if ( 'incl' === $tax_display ) {
                            $payable_price = wc_price( $total_payable + $item_tax + $remaining_tax, [ 'currency' => $order->get_currency() ] );
                            $tax_string = ' <small class="tax_label">' . esc_html__( '(incl. tax)', 'ova-brw' ) . '</small>';
                        }
                    }
                }

                $payable_html .= $payable_price.$tax_string;
                $payable_html .= '</dd>';
            }

            return apply_filters( OVABRW_PREFIX.'get_order_formatted_line_subtotal_payable', $payable_html, $item, $order );
        }

        /**
         * Order item get formatted meta data
         */
        public function ovabrw_order_item_get_formatted_meta_data( $meta_data, $item ) {
            $hide_fields = apply_filters( OVABRW_PREFIX.'order_item_get_formatted_meta_data_hide_meta', [
                'ovabrw_pickup_date_strtotime',
                'ovabrw_dropoff_date_strtotime',
                'ovabrw_quantity',
                'ovabrw_insurance_amount',
                'ovabrw_remaining_insurance',
                'ovabrw_remaining_insurance_tax',
                'ovabrw_insurance_tax',
                'ovabrw_deposit_type',
                'ovabrw_deposit_value',
                'ovabrw_deposit_amount',
                'ovabrw_remaining_amount',
                'ovabrw_remaining_tax',
                'ovabrw_total_payable',
                'ovabrw_parent_order_id'
            ], $meta_data, $item );
            
            // init new meta
            $new_meta = [];

            foreach ( $meta_data as $id => $meta_array ) {
                if ( in_array( $meta_array->key, $hide_fields ) ) continue;
                if ( !$meta_array->value ) continue;

                // New meta
                $new_meta[$id] = $meta_array;
            }

            return apply_filters( OVABRW_PREFIX.'order_item_get_formatted_meta_data_new_meta', $new_meta, $meta_data, $item );
        }

        /**
         * Order detail
         */
        public function ovabrw_get_order_item_totals( $total_rows, $order, $tax_display ) {
            // Is deposit
            $is_deposit = $order->get_meta( '_ova_has_deposit' );

            if ( $is_deposit ) {
                // Remove order_total
                if ( isset( $total_rows['order_total'] ) ) unset( $total_rows['order_total'] );

                // Deposit amount
                $deposit_amount = floatval( $order->get_meta( '_ova_deposit_amount' ) );
                if ( $deposit_amount ) {
                    $total_rows['deposit_amount'] = [
                        'label' => esc_html__( 'Deposit:', 'ova-brw' ),
                        'value' => $this->ovabrw_get_formatted_order_deposit( $order )
                    ];
                }

                // Remaining amount
                $remaining_amount = floatval( $order->get_meta( '_ova_remaining_amount' ) );
                if ( $remaining_amount ) {
                    $total_rows['remaining_amount'] = [
                        'label' => esc_html__( 'Remaining:', 'ova-brw' ),
                        'value' => $this->ovabrw_get_formatted_order_remaining( $order )
                    ];
                }

                // Total payment
                $total_rows['order_total'] = [
                    'label' => esc_html__( 'Total:', 'ova-brw' ),
                    'value' => $this->ovabrw_get_formatted_order_total_payable( $order )
                ];
            }

            return apply_filters( OVABRW_PREFIX.'get_order_item_totals', $total_rows, $order, $tax_display );
        }

        /**
         * Get formatted order deposit
         */
        public function ovabrw_get_formatted_order_deposit( $order ) {
            $formatted_total    = wc_price( $order->get_total(), [ 'currency' => $order->get_currency() ] );
            $tax_string         = '';
            $tax_display        = get_option( 'woocommerce_tax_display_cart' );

            // Tax for inclusive prices.
            if ( wc_tax_enabled() && 'incl' === $tax_display ) {
                $tax_string_array = [];
                $tax_totals       = $order->get_tax_totals();

                if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) {
                    foreach ( $tax_totals as $code => $tax ) {
                        $tax_amount         = $tax->formatted_amount;
                        $tax_string_array[] = sprintf( '%s %s', $tax_amount, $tax->label );
                    }
                } elseif ( !empty( $tax_totals ) ) {
                    $tax_amount         = $order->get_total_tax();
                    $tax_string_array[] = sprintf( '%s %s', wc_price( $tax_amount, [ 'currency' => $order->get_currency() ] ), WC()->countries->tax_or_vat() );
                }

                if ( !empty( $tax_string_array ) ) {
                    $tax_string = ' <small class="includes_tax">' . sprintf( __( '(includes %s)', 'ova-brw' ), implode( ', ', $tax_string_array ) ) . '</small>';
                }
            }

            $formatted_total .= $tax_string;

            return apply_filters( OVABRW_PREFIX.'get_formatted_order_deposit', $formatted_total, $order );
        }

        /**
         * Get formatted order remaining
         */
        public function ovabrw_get_formatted_order_remaining( $order ) {
            // Get remaining amount
            $remaining_amount = floatval( $order->get_meta( '_ova_remaining_amount' ) );

            // Get remaining tax amount
            $remaining_tax = floatval( $order->get_meta( '_ova_remaining_tax' ) );

            // Get remaining insurance amount
            $remaining_insurance = floatval( $order->get_meta( '_ova_remaining_insurance' ) );
            if ( $remaining_insurance ) {
                $remaining_amount += $remaining_insurance;
            }

            // Get remaining insurance tax amount
            $remaining_insurance_tax = floatval( $order->get_meta( '_ova_remaining_insurance_tax' ) );
            if ( ovabrw_insurance_tax_enabled() && $remaining_insurance_tax ) {
                $remaining_tax += $remaining_insurance_tax;
            }

            // Formatted remaining total
            $formatted_total = wc_price( $remaining_amount, [ 'currency' => $order->get_currency() ] );

            // Insurance string
            $insurance_string = '';

            // Tax string
            $tax_string     = '';
            $tax_display    = get_option( 'woocommerce_tax_display_cart' );

            // Remaining insurance amount
            if ( $remaining_insurance ) {
                // Remaining insurance tax amount
                if ( ovabrw_insurance_tax_enabled() && $remaining_insurance_tax && 'incl' === $tax_display ) {
                    $remaining_insurance += $remaining_insurance_tax;
                }

                $insurance_string = ' <small class="includes_tax">';
                $insurance_string .= sprintf( __( '(includes %s insurance fee)', 'ova-brw' ), wc_price( $remaining_insurance, [ 'currency' => $order->get_currency() ] ) );
                $insurance_string .= ' </small>';
            }

            // Taxable
            if ( wc_tax_enabled() ) {
                // Prices include tax
                $prices_incl_tax = $order->get_meta( '_ova_prices_include_tax' );

                // Tax string array
                $tax_string_array[] = sprintf( '%s %s', wc_price( $remaining_tax, [ 'currency' => $order->get_currency() ] ), WC()->countries->tax_or_vat() );

                if ( $prices_incl_tax ) {
                    // Remaining insurance tax amount
                    if ( ovabrw_insurance_tax_enabled() && $remaining_insurance_tax ) {
                        $remaining_amount   += $remaining_insurance_tax;
                        $formatted_total    = wc_price( $remaining_amount, [ 'currency' => $order->get_currency() ] );
                    }

                    if ( 'incl' === $tax_display ) {
                        $tax_string = ' <small class="includes_tax">' . sprintf( __( '(includes %s)', 'ova-brw' ), implode( ', ', $tax_string_array ) ) . '</small>';
                    } else {
                        $tax_string = ' <small class="includes_tax">' . sprintf( __( '(excludes %s)', 'ova-brw' ), implode( ', ', $tax_string_array ) ) . '</small>';

                        $remaining_amount   -= $remaining_tax;
                        $formatted_total    = wc_price( $remaining_amount, [ 'currency' => $order->get_currency() ] );
                    }
                } else {
                    if ( 'excl' === $tax_display ) {
                        $tax_string         = ' <small class="includes_tax">' . sprintf( __( '(excludes %s)', 'ova-brw' ), implode( ', ', $tax_string_array ) ) . '</small>';
                        $formatted_total    = wc_price( $remaining_amount, [ 'currency' => $order->get_currency() ] );
                    } else {
                        $tax_string         = ' <small class="includes_tax">' . sprintf( __( '(includes %s)', 'ova-brw' ), implode( ', ', $tax_string_array ) ) . '</small>';

                        $remaining_amount   += $remaining_tax;
                        $formatted_total    = wc_price( $remaining_amount, [ 'currency' => $order->get_currency() ] );
                    }
                }
            }

            $formatted_total .= $insurance_string.$tax_string;

            return apply_filters( OVABRW_PREFIX.'get_formatted_order_remaining', $formatted_total, $order );
        }

        /**
         * Get formatted order total payable
         */
        public function ovabrw_get_formatted_order_total_payable( $order ) {
            // Get order total
            $order_total = $order->get_total();

            // Get remaining amount
            $remaining_amount = floatval( $order->get_meta( '_ova_remaining_amount' ) );
            if ( $remaining_amount ) {
                $order_total += $remaining_amount;
            }

            // Get remaining tax amount
            $remaining_tax = floatval( $order->get_meta( '_ova_remaining_tax' ) );

            // Get remaining insurance amount
            $remaining_insurance = floatval( $order->get_meta( '_ova_remaining_insurance' ) );
            if ( $remaining_insurance ) {
                $order_total += $remaining_insurance;
            }

            // Get remaining insurance tax amount
            $remaining_insurance_tax = floatval( $order->get_meta( '_ova_remaining_insurance_tax' ) );
            if ( ovabrw_insurance_tax_enabled() && $remaining_insurance_tax ) {
                $remaining_tax          += $remaining_insurance_tax;
                $remaining_insurance    += $remaining_insurance_tax;
            }

            // Formatted total
            $formatted_total = wc_price( $order_total, [ 'currency' => $order->get_currency() ] );

            // Taxable
            $tax_string     = '';
            $tax_display    = get_option( 'woocommerce_tax_display_cart' );

            if ( wc_tax_enabled() ) {
                $prices_include_tax = $order->get_meta( '_ova_prices_include_tax' );
                $tax_string_array   = [];

                // Get order total tax
                $total_tax = $order->get_total_tax();

                if ( $prices_include_tax ) {
                    // Remaining insurance tax amount
                    if ( ovabrw_insurance_tax_enabled() && $remaining_insurance_tax ) {
                        $order_total    += $remaining_insurance_tax;
                        $formatted_total = wc_price( $order_total, [ 'currency' => $order->get_currency() ] );
                    }

                    if ( 'incl' === $tax_display ) {
                        $total_tax += $remaining_tax;

                        $tax_string_array[] = sprintf( '%s %s', wc_price( $total_tax, [ 'currency' => $order->get_currency() ] ), WC()->countries->tax_or_vat() );
                        $tax_string         = ' <small class="includes_tax">' . sprintf( __( '(includes %s)', 'ova-brw' ), implode( ', ', $tax_string_array ) ) . '</small>';
                    } else {
                        $order_total -= $remaining_tax;

                        $formatted_total    = wc_price( $order_total, [ 'currency' => $order->get_currency() ] );
                        $tax_string_array[] = sprintf( '%s %s', wc_price( $remaining_tax, [ 'currency' => $order->get_currency() ] ), WC()->countries->tax_or_vat() );
                        $tax_string         = ' <small class="includes_tax">' . sprintf( __( '(excludes %s)', 'ova-brw' ), implode( ', ', $tax_string_array ) ) . '</small>';
                    }
                } else {
                    if ( 'excl' === $tax_display ) {
                        $tax_string_array[] = sprintf( '%s %s', wc_price( $remaining_tax, [ 'currency' => $order->get_currency() ] ), WC()->countries->tax_or_vat() );
                        $tax_string         = ' <small class="includes_tax">' . sprintf( __( '(excludes %s)', 'ova-brw' ), implode( ', ', $tax_string_array ) ) . '</small>';
                    } else {
                        $order_total    += $remaining_tax;
                        $total_tax      += $remaining_tax;

                        $formatted_total    = wc_price( $order_total, [ 'currency' => $order->get_currency() ] );
                        $tax_string_array[] = sprintf( '%s %s', wc_price( $total_tax, [ 'currency' => $order->get_currency() ] ), WC()->countries->tax_or_vat() );
                        $tax_string         = ' <small class="includes_tax">' . sprintf( __( '(includes %s)', 'ova-brw' ), implode( ', ', $tax_string_array ) ) . '</small>';
                    }
                }
            }

            $formatted_total .= $tax_string;

            return apply_filters( OVABRW_PREFIX.'get_formatted_order_total_payable', $formatted_total, $order );
        }

        /**
         * Admin order item headers
         */
        public function ovabrw_admin_order_item_headers( $order ) {
            // is order
            if ( !$order ) return;

            // Is deposit
            $is_deposit = $order->get_meta( '_ova_has_deposit' );

            // Extra price
            if ( 'yes' === ovabrw_get_option_setting( 'booking_form_show_extra', 'no' ) ) {
                echo '<th class="ovabrw-extra-price">'.esc_html__( 'Extra Price' , 'ova-brw' ).'</th>';
            }

            // Deposit
            if ( $is_deposit ) {
                echo '<th class="deposit-amount">'.esc_html__( 'Deposit' , 'ova-brw' ).'</th>';
                echo '<th class="remaining-amount">'.esc_html__( 'Remaining' , 'ova-brw' ).'</th>';
            } else {
                // Get insurance amount
                $insurance_amount = floatval( $order->get_meta( '_ova_insurance_amount' ) );

                if ( $insurance_amount ) {
                    echo '<th class="insurance-amount">'.esc_html__( 'Insurance' , 'ova-brw' ).'</th>';
                }
            }
        }

        /**
         * Admin order item values
         */
        public function ovabrw_admin_order_item_values( $product, $item, $item_id ) {
            if ( in_array( $item->get_type(), [ 'fee', 'shop_order_refund', 'shipping' ] ) ) {
                $parent_order = false;

                if ( 'shop_order_refund' === $item->get_type() ) {
                    $parent_order = wc_get_order( $item->get_parent_id() );
                }
                if ( 'fee' === $item->get_type() || 'shipping' === $item->get_type() ) {
                    $parent_order = $item->get_order();
                }

                if ( $parent_order && is_object( $parent_order ) ) {
                    if ( ovabrw_get_option_setting( 'booking_form_show_extra', 'no' ) == 'yes' ): ?>
                        <td class="ovabrw-extra-price" width="12%"></td>
                    <?php endif;

                    // Is deposit
                    if ( $parent_order->get_meta( '_ova_has_deposit' ) ): ?>
                        <td class="ovabrw-deposit-amount" width="12%"></td>
                        <td class="ovabrw-remaining-amount" width="12%"></td>
                    <?php else:
                        // Get insurance amount
                        $insurance_amount = floatval( $parent_order->get_meta( '_ova_insurance_amount' ) );

                        if ( $insurance_amount ): ?>
                            <td class="ovabrw-insurance-amount" width="10%"></td>
                        <?php endif;
                    endif;
                }

                return;
            }

            // Get order
            $order = $item->get_order();
            if ( !$order ) return;

            // Get order id
            $order_id = $order->get_id();

            if ( 'yes' === ovabrw_get_option_setting( 'booking_form_show_extra', 'no' ) ) {
                // Product ID
                $product_id = $item->get_product_id();

                // Number of adults
                $numberof_adults = (int)$item->get_meta( 'ovabrw_adults' );

                // Number of children
                $numberof_children = (int)$item->get_meta( 'ovabrw_childrens' );

                // Number of babies
                $numberof_babies = (int)$item->get_meta( 'ovabrw_babies' );

                // init
                $resource_html = $service_html = $cckf_html = '';

                // Resources
                $resources = $item->get_meta( 'ovabrw_resources' );
                if ( ovabrw_array_exists( $resources ) ) {
                    // Get resource guests
                    $resource_guests = $item->get_meta( 'ovabrw_resource_guests' );

                    // Get HTML resources
                    $resource_html = ovabrw_get_html_resources( $product_id, $resources, $numberof_adults, $numberof_children, $numberof_babies, $order_id, $resource_guests );
                }

                // Services
                $services = $item->get_meta('ovabrw_services');
                if ( ovabrw_array_exists( $services ) ) {
                    // Get service guests
                    $service_guests = $item->get_meta( 'ovabrw_service_guests' );

                    // Get HTML services
                    $service_html = ovabrw_get_html_services( $product_id, $services, $numberof_adults, $numberof_children, $numberof_babies, $order_id, $service_guests );
                }

                // Custom checkout fields
                $custom_cckf = $item->get_meta( 'ovabrw_custom_ckf' );

                // Get custom checkout field quantity
                $cckf_qty = $item->get_meta( 'ovabrw_cckf_qty' );

                // Get HTML custom checkout fields
                if ( ovabrw_array_exists( $custom_cckf ) ) {
                    $cckf_html = ovabrw_get_html_cckf( $custom_cckf, $order_id, $cckf_qty );
                }

                // Get HTML extra
                $extra_html = ovabrw_get_html_extra( $resource_html, $service_html, $cckf_html );

                ?>
                <td class="ovabrw-extra-price" width="12%">
                    <div class="view">
                        <?php echo wp_kses_post( $extra_html ); ?>
                    </div>
                </td>
                <?php
            }

            // Get item insurance amount
            $item_insurance     = floatval( $item->get_meta( 'ovabrw_insurance_amount' ) );
            $item_insurance_tax = floatval( $item->get_meta( 'ovabrw_insurance_tax' ) );

            // Get item remaining insurance amount
            $item_remaining_insurance     = floatval( $item->get_meta( 'ovabrw_remaining_insurance' ) );
            $item_remaining_insurance_tax = floatval( $item->get_meta( 'ovabrw_remaining_insurance_tax' ) );

            // Is deposit
            $is_deposit = $order->get_meta( '_ova_has_deposit' );
            if ( $is_deposit ) {
                $deposit_amount     = floatval( $item->get_meta( 'ovabrw_deposit_amount' ) );
                $remaining_amount   = floatval( $item->get_meta( 'ovabrw_remaining_amount' ) );
                
                // Prices including tax
                $prices_incl_tax = $order->get_meta( '_ova_prices_include_tax' );
                ?>
                <td class="ovabrw-deposit-amount" width="12%">
                    <?php if ( $deposit_amount ):
                        $deposit_amount += $item_insurance;

                        // Get item total tax
                        $item_total_tax = $item->get_total_tax();
                        if ( !$prices_incl_tax ) $deposit_amount += $item_total_tax;

                        if ( wc_tax_enabled() ) {
                            $deposit_amount += $item_insurance_tax;
                            $item_total_tax += $item_insurance_tax;
                        }
                    ?>
                        <div class="view">
                            <?php
                                $deposit_html = wc_price( $deposit_amount, [ 'currency' => $order->get_currency() ] );

                                if ( $item_insurance ) {
                                    $deposit_html .= '<small class="includes_tax">';
                                        $deposit_html .= wp_kses_post( sprintf( __( '(incl. %s insurance fee)', 'ova-brw' ), ovabrw_wc_price( $item_insurance, [ 'currency' => $order->get_currency() ], false )));
                                    $deposit_html .= '</small>';
                                }

                                if ( wc_tax_enabled() && $item_total_tax ) {
                                    $tax_string = '<small class="includes_tax">';
                                    $tax_string .= wp_kses_post( sprintf( __( '(incl. %s %s)' ), wc_price( $item_total_tax, [ 'currency' => $order->get_currency() ] ), WC()->countries->tax_or_vat() ));
                                    $tax_string .= '</small>';

                                    $deposit_html .= $tax_string;
                                }

                                echo wp_kses_post( $deposit_html );
                            ?>
                        </div>
                    <?php endif; ?>
                </td>
                <td class="ovabrw-remaining-amount" width="12%">
                    <?php if ( $remaining_amount ):
                        if ( $item_remaining_insurance ) {
                            $remaining_amount += $item_remaining_insurance;
                        }

                        $remanining_tax = floatval( $item->get_meta( 'ovabrw_remaining_tax' ) );
                    ?>
                        <div class="view">
                            <?php
                                $remaining_html = wc_price( $remaining_amount, [ 'currency' => $order->get_currency() ] );

                                if ( wc_tax_enabled() && $item_remaining_insurance_tax ) {
                                    $remaining_amount   += $item_remaining_insurance_tax;
                                    $remanining_tax     += $item_remaining_insurance_tax;

                                    $remaining_html = wc_price( $remaining_amount, [ 'currency' => $order->get_currency() ]);

                                    $remaining_html .= '<small class="includes_tax">';
                                        $remaining_html .= wp_kses_post( sprintf( __( '(incl. %s insurance fee)', 'ova-brw' ), ovabrw_wc_price( $item_remaining_insurance, [ 'currency' => $order->get_currency() ], false )));
                                    $remaining_html .= '</small>';
                                }

                                // Remaining tax
                                if ( wc_tax_enabled() && $remanining_tax ) {
                                    $tax_string = '<small class="includes_tax">';

                                    if ( $prices_incl_tax ) {
                                        $tax_string .= wp_kses_post( sprintf( __( '(incl. %s %s)' ), ovabrw_wc_price( $remanining_tax, [ 'currency' => $order->get_currency() ], false ), WC()->countries->tax_or_vat() ));
                                    } else {
                                        $tax_string .= wp_kses_post( sprintf( __( '(ex. %s %s)' ), ovabrw_wc_price( $remanining_tax, [ 'currency' => $order->get_currency() ], false ), WC()->countries->tax_or_vat() ));
                                    }
                                    $tax_string .= '</small>';

                                    $remaining_html .= $tax_string;
                                }

                                echo wp_kses_post( $remaining_html );
                            ?>
                        </div>
                    <?php endif; ?>
                </td>
                <?php
            } else {
                // Get insurance amount
                $insurance_amount = floatval( $order->get_meta( '_ova_insurance_amount' ) );

                if ( $insurance_amount ) { ?>
                    <td class="ovabrw-insurance-amount" width="10%">
                        <?php if ( $item_insurance ):
                            $insurance_html = wc_price( $item_insurance, [ 'currency' => $order->get_currency() ]);

                            // Get insurance tax
                            $insurance_tax = floatval( $item->get_meta( 'ovabrw_insurance_tax' ) );

                            if ( wc_tax_enabled() && $insurance_tax ) {
                                $tax_string = '<br>';
                                $tax_string .= '<small class="includes_tax">';
                                $tax_string .= wp_kses_post( sprintf( __( '(ex. %s %s)' ), wc_price( wc_round_tax_total( $insurance_tax ), [ 'currency' => $order->get_currency() ] ), WC()->countries->tax_or_vat() ));
                                $tax_string .= '</small>';

                                $insurance_html .= $tax_string;
                            }
                        ?>
                            <div class="view">
                                <?php
                                    echo wp_kses_post( $insurance_html );
                                ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <?php
                }
            }
        }

        /**
         * Admin order totals after tax
         */
        public function ovabrw_admin_order_totals_after_tax( $order_id ) {
            // is order
            $order = wc_get_order( $order_id );

            if ( !$order ) return;

            // Is deposit
            $is_deposit = $order->get_meta( '_ova_has_deposit' );

            if ( $is_deposit ) {
                $deposit_amount             = floatval( $order->get_meta( '_ova_deposit_amount' ) );
                $remaining_amount           = floatval( $order->get_meta( '_ova_remaining_amount' ) );
                $insurance_amount           = floatval( $order->get_meta( '_ova_insurance_amount' ) );
                $insurance_tax              = floatval( $order->get_meta( '_ova_insurance_tax' ) );
                $remaining_insurance        = floatval( $order->get_meta( '_ova_remaining_insurance' ) );
                $remaining_insurance_tax    = floatval( $order->get_meta( '_ova_remaining_insurance_tax' ) );

                // Remaining insurance amount
                if ( $remaining_insurance ) {
                    $remaining_amount += $remaining_insurance;
                }

                // Remaining insurance tax amount
                if ( wc_tax_enabled() && $remaining_insurance_tax ) {
                    $remaining_amount += $remaining_insurance_tax;
                }

                // Total payable
                $total_payable = $deposit_amount + $remaining_amount + $insurance_amount;

                // Remaining HTML
                $remaining_html = ovabrw_wc_price( $remaining_amount, [ 'currency' => $order->get_currency() ], false );

                // Payable HTML
                $payable_html = ovabrw_wc_price( $total_payable , [ 'currency' => $order->get_currency() ], false );

                // Get total tax
                $total_tax = $order->get_total_tax();
                if ( wc_tax_enabled() && $total_tax ) {
                    $text_tax = '';

                    // Remaining tax
                    $remaining_tax = floatval( $order->get_meta( '_ova_remaining_tax' ) );

                    // Remaining insurance tax amount
                    if ( wc_tax_enabled() && $remaining_insurance_tax ) {
                        $remaining_tax += $remaining_insurance_tax;
                    }

                    // Prices including tax
                    $prices_incl_tax = $order->get_meta( '_ova_prices_include_tax' );
                    if ( $prices_incl_tax ) {
                        $text_tax = esc_html__( '(includes %s %s)', 'ova-brw' );

                        // Update total payable
                        $total_payable += $insurance_tax;

                        // Payable HTML
                        $payable_html = ovabrw_wc_price( $total_payable , [ 'currency' => $order->get_currency() ], false );

                        // Remaining
                        if ( $remaining_amount ) {
                            if ( $remaining_insurance ) {
                                $tax_string = '<small class="includes_tax">';
                                    $tax_string .= wp_kses_post( sprintf( $text_tax, ovabrw_wc_price( $remaining_insurance, [ 'currency' => $order->get_currency() ], false ), esc_html__( 'insurance fee', 'ova-brw' )));
                                $tax_string .= '</small>';

                                $remaining_html .= $tax_string;
                            }

                            if ( $remaining_tax ) {
                                $tax_string = '<small class="includes_tax">';
                                    $tax_string .= wp_kses_post( sprintf( $text_tax, ovabrw_wc_price( $remaining_tax, [ 'currency' => $order->get_currency() ], false ), WC()->countries->tax_or_vat()));
                                $tax_string .= '</small>';

                                $remaining_html .= $tax_string;
                            }
                        }
                    } else {
                        $text_tax = esc_html__( '(excludes %s %s)', 'ova-brw' );

                        // Update total payable
                        $total_payable += $total_tax;

                        // Payable HTML
                        $payable_html = ovabrw_wc_price( $total_payable, [ 'currency' => $order->get_currency() ], false );

                        if ( $remaining_insurance ) {
                            $tax_string = '<small class="includes_tax">';
                                $tax_string .= wp_kses_post( sprintf( $text_tax, ovabrw_wc_price( $remaining_insurance, [ 'currency' => $order->get_currency() ], false ), esc_html__( 'insurance', 'ova-brw' ) ));
                            $tax_string .= '</small>';

                            $remaining_html .= $tax_string;
                        }

                        // Remaining
                        if ( $remaining_tax ) {
                            $tax_string = '<small class="includes_tax">';
                                $tax_string .= wp_kses_post( sprintf( $text_tax, ovabrw_wc_price( $remaining_tax, [ 'currency' => $order->get_currency() ], false ), WC()->countries->tax_or_vat()));
                            $tax_string .= '</small>';

                            if ( $remaining_amount ) {
                                $remaining_html .= $tax_string;
                            }
                            if ( $total_payable ) {
                                $payable_html .= $tax_string;
                            }
                        }
                    }
                } else {
                    if ( $remaining_insurance ) {
                        $text_tax   = esc_html__( '(includes %s %s)', 'ova-brw' );
                        $tax_string = '<small class="includes_tax">';
                            $tax_string .= wp_kses_post( sprintf( $text_tax, ovabrw_wc_price( $remaining_insurance, [ 'currency' => $order->get_currency() ], false ), esc_html__( 'insurance fee', 'ova-brw' )));
                        $tax_string .= '</small>';

                        $remaining_html .= $tax_string;
                    }
                }

                ?>
                <tr>
                    <td class="label"><?php esc_html_e( 'Total Deposit:' , 'ova-brw' ); ?></td>
                    <td width="1%"></td>
                    <td class="total">
                        <div class="view">
                            <?php echo wc_price( $order->get_total(), [ 'currency' => $order->get_currency() ] ); ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="label"><?php esc_html_e( 'Total Remaining:' , 'ova-brw' ); ?></td>
                    <td width="1%"></td>
                    <td class="total">
                        <div class="view">
                            <?php echo wp_kses_post( $remaining_html ); ?>
                        </div>
                    </td>
                </tr>
                <tr class="ovabrw-total-payable">
                    <td class="label"><?php esc_html_e( 'Total Payable:' , 'ova-brw' ); ?></td>
                    <td width="1%"></td>
                    <td class="total">
                        <div class="view">
                            <?php echo wp_kses_post( $payable_html ); ?>
                        </div>
                    </td>
                </tr>
                <?php
            }
        }

        /**
         * Button pay full & remaining invoice
         */
        public function ovabrw_after_order_itemmeta( $item_id, $item, $product ) {
            $order          = $item->get_order();
            $order_id       = $item->get_order_id();
            $order_status   = $order->get_status();

            // Allow insurance refund when order status
            $statuses = apply_filters( OVABRW_PREFIX.'statuses_to_return_insurance_amount', [ 'processing', 'completed' ]);
            if ( !$statuses || !is_array( $statuses ) ) $statuses = [];

            // Get remaining amount
            $remaining_amount = floatval( $item->get_meta( 'ovabrw_remaining_amount' ) );

            // Get remaining balance order ID
            $balance_id = absint( $item->get_meta( 'ovabrw_remaining_balance_order_id' ) );

            // Get parent order ID
            $parent_order_id = absint( $item->get_meta( 'ovabrw_parent_order_id' ) );

            // Get insurance amount
            $insurance_amount   = floatval( $item->get_meta( 'ovabrw_insurance_amount' ) );
            $insurance_tax      = floatval( $item->get_meta( 'ovabrw_insurance_tax' ) );

            if ( $remaining_amount && $remaining_amount > 0 ): ?>
                <div class="ova_pay_full">
                <?php if ( $balance_id && $balance_order = wc_get_order( $balance_id ) ):
                    $balance_order_status = $balance_order->get_status();
                ?>
                    <a href="<?php echo esc_url( $balance_order->get_edit_order_url() ); ?>" class="button" target="_blank">
                        <?php 
                            echo wp_kses_post( sprintf( __( 'Remaining - Invoice #%1$s', 'ova-brw' ), $balance_order->get_order_number() ) );
                        ?>
                    </a>
                    <?php if ( $insurance_amount && in_array( $balance_order_status, $statuses ) && in_array( $order_status, $statuses ) ):
                        $insurance_tax = floatval( $item->get_meta( 'ovabrw_insurance_tax' ) );
                    ?>
                        <div class="ovabrw-update-insurance">
                            <button type="button" class="button ovabrw-update-insurance-btn">
                                <?php esc_html_e( 'Update Insurance', 'ova-brw' ); ?>
                            </button>
                            <div class="update-insurance-input">
                                <div class="ovabrw-input-price">
                                    <small><strong><?php esc_html_e( 'Amount', 'ova-brw' ); ?></strong></small>
                                    <input
                                        type="text"
                                        class="wc_input_price"
                                        name="<?php echo esc_attr( 'ovabrw_insurance_amount' ); ?>"
                                        value="<?php echo esc_attr( $insurance_amount ); ?>"
                                    />
                                </div>
                                <?php if ( wc_tax_enabled() && $insurance_tax ): ?>
                                    <div class="ovabrw-input-price">
                                        <small><strong><?php esc_html_e( 'Tax', 'ova-brw' ); ?></strong></small>
                                        <input
                                            type="text"
                                            class="wc_input_price"
                                            name="<?php echo esc_attr( 'ovabrw_insurance_tax' ); ?>"
                                            value="<?php echo esc_attr( $insurance_tax ); ?>"
                                        />
                                    </div>
                                <?php endif; ?>
                                <button
                                    type="button"
                                    class="button button-primary ovabrw-update-insurance-submit"
                                    data-order_item_id="<?php echo esc_attr( $item_id ); ?>"
                                    data-order_id="<?php echo esc_attr( $order_id ); ?>">
                                    <?php esc_html_e( 'Save', 'ova-brw' ); ?>
                                </button>
                                <button type="button" class="button button-primary ovabrw-update-insurance-cancel" title="<?php esc_attr_e( 'Cancel', 'ova-brw' ); ?>">X</button>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php else:
                    $remaining_invoice_url = wp_nonce_url(
                        add_query_arg(
                            [ 'create_remaining_invoice' => $item_id ],
                            $order->get_edit_order_url()
                        ),
                        'create_remaining_invoice',
                        'create_remaining_invoice_nonce'
                    );
                    $pay_full_url = wp_nonce_url(
                        add_query_arg(
                            [ 'pay_full' => $item_id ],
                            $order->get_edit_order_url()
                        ),
                        'pay_full',
                        'pay_full_nonce'
                    );
                ?>
                    <a href="<?php echo esc_url( $remaining_invoice_url ); ?>" class="button">
                        <?php esc_html_e( 'Create Remaining Invoice', 'ova-brw' ); ?>
                    </a>
                    <a href="<?php echo esc_url( $pay_full_url ); ?>" class="button">
                        <?php esc_html_e( 'Pay Full (offline)', 'ova-brw' ); ?>
                    </a>
                <?php endif; ?>
                </div>
            <?php elseif ( $parent_order_id && $parent_order = wc_get_order( $parent_order_id ) ): ?>
                <div class="ovabrw_deposit_btn">
                    <a href="<?php echo esc_url( $parent_order->get_edit_order_url() ); ?>" class="button" target="_blank">
                        <?php esc_html_e( 'View Original Order', 'ova-brw' ); ?>
                    </a>
                </div>
                <?php if ( $insurance_amount && in_array( $order_status, $statuses ) ): ?>
                    <div class="ovabrw-update-insurance">
                        <button type="button" class="button ovabrw-update-insurance-btn">
                            <?php esc_html_e( 'Update Insurance', 'ova-brw' ); ?>
                        </button>
                        <div class="update-insurance-input">
                            <div class="ovabrw-input-price">
                                <small><strong><?php esc_html_e( 'Amount', 'ova-brw' ); ?></strong></small>
                                <input
                                    type="text"
                                    class="wc_input_price"
                                    name="<?php echo esc_attr( 'ovabrw_insurance_amount' ); ?>"
                                    value="<?php echo esc_attr( $insurance_amount ); ?>"
                                />
                            </div>
                            <?php if ( wc_tax_enabled() && $insurance_tax ): ?>
                                <div class="ovabrw-input-price">
                                    <small><strong><?php esc_html_e( 'Tax', 'ova-brw' ); ?></strong></small>
                                    <input
                                        type="text"
                                        class="wc_input_price"
                                        name="<?php echo esc_attr( 'ovabrw_insurance_tax' ); ?>"
                                        value="<?php echo esc_attr( $insurance_tax ); ?>"
                                    />
                                </div>
                            <?php endif; ?>
                            <button
                                type="button"
                                class="button button-primary ovabrw-update-insurance-submit"
                                data-order_item_id="<?php echo esc_attr( $item_id ); ?>"
                                data-order_id="<?php echo esc_attr( $order_id ); ?>">
                                <?php esc_html_e( 'Save', 'ova-brw' ); ?>
                            </button>
                            <button type="button" class="button button-primary ovabrw-update-insurance-cancel" title="<?php esc_attr_e( 'Cancel', 'ova-brw' ); ?>">X</button>
                        </div>
                    </div>
                <?php endif; ?>
            <?php elseif ( $insurance_amount && in_array( $order_status, $statuses ) ): ?>
                <div class="ovabrw-update-insurance">
                    <button type="button" class="button ovabrw-update-insurance-btn">
                        <?php esc_html_e( 'Update Insurance', 'ova-brw' ); ?>
                    </button>
                    <div class="update-insurance-input">
                        <div class="ovabrw-input-price">
                            <small><strong><?php esc_html_e( 'Amount', 'ova-brw' ); ?></strong></small>
                            <input
                                type="text"
                                class="wc_input_price"
                                name="<?php echo esc_attr( 'ovabrw_insurance_amount' ); ?>"
                                value="<?php echo esc_attr( $insurance_amount ); ?>"
                            />
                        </div>
                        <?php if ( wc_tax_enabled() && $insurance_tax ): ?>
                            <div class="ovabrw-input-price">
                                <small><strong><?php esc_html_e( 'Tax', 'ova-brw' ); ?></strong></small>
                                <input
                                    type="text"
                                    class="wc_input_price"
                                    name="<?php echo esc_attr( 'ovabrw_insurance_tax' ); ?>"
                                    value="<?php echo esc_attr( $insurance_tax ); ?>"
                                />
                            </div>
                        <?php endif; ?>
                        <button
                            type="button"
                            class="button button-primary ovabrw-update-insurance-submit"
                            data-order_item_id="<?php echo esc_attr( $item_id ); ?>"
                            data-order_id="<?php echo esc_attr( $order_id ); ?>">
                            <?php esc_html_e( 'Save', 'ova-brw' ); ?>
                        </button>
                        <button type="button" class="button button-primary ovabrw-update-insurance-cancel" title="<?php esc_attr_e( 'Cancel', 'ova-brw' ); ?>">X</button>
                    </div>
                </div>
            <?php endif;
        }

        /**
         * Action pay full & create remaining invoice
         */
        public function ovabrw_order_item_action() {
            $action = $item_id = false;

            // Get action & item_id
            if ( ovabrw_get_meta_data( 'pay_full', $_GET ) ) {
                // Action
                $action = 'pay_full';

                // Nonce
                $nonce  = isset( $_GET['pay_full_nonce'] ) ? wp_verify_nonce( $_GET['pay_full_nonce'], 'pay_full' ) : false;
                if ( $nonce ) $item_id = absint( $_GET['pay_full'] );
            } elseif ( ovabrw_get_meta_data( 'create_remaining_invoice', $_GET ) ) {
                // Action
                $action = 'create_remaining_invoice';

                // Nonce
                $nonce = isset( $_GET['create_remaining_invoice_nonce'] ) ? wp_verify_nonce( $_GET['create_remaining_invoice_nonce'], 'create_remaining_invoice' ) : false;
                if ( $nonce ) $item_id = absint( $_GET['create_remaining_invoice'] );
            }

            if ( !$item_id ) return;

            // Get Order Item
            $item = new WC_Order_Item_Product( absint( $item_id ) );
            if ( !$item ) return;

            // Get Order
            $order = $item->get_order();
            if ( !$order ) return;

            // Order ID
            $order_id = $order->get_id();

            // Get item remaining amount
            $item_remaining = floatval( $item->get_meta( 'ovabrw_remaining_amount' ) );
            if ( !$item_remaining ) return;

            // Get item remaining tax amount
            $item_remaining_tax = floatval( $item->get_meta( 'ovabrw_remaining_tax' ) );

            // Get item remaining insurance amount
            $item_remaining_insurance = floatval( $item->get_meta( 'ovabrw_remaining_insurance' ) );

            // Get item remaining insurance tax amount
            $item_remaining_insurance_tax = floatval( $item->get_meta( 'ovabrw_remaining_insurance_tax' ) );

            if ( 'pay_full' === $action ) {
                // Get order total
                $order_total = floatval( $order->get_total() );

                // Get order deposit amount
                $order_deposit = floatval( $order->get_meta( '_ova_deposit_amount' ) );

                // Get order remaining amount
                $order_remaining = floatval( $order->get_meta( '_ova_remaining_amount' ) );

                // Order total
                $order_total += $item_remaining;

                // Order total deposit
                $order_deposit += $item_remaining;

                // Order total remaining
                $order_remaining -= $item_remaining;
                if ( $order_remaining < 0 ) $order_remaining = 0;

                // Get item deposit amount
                $item_deposit = floatval( $item->get_meta( 'ovabrw_deposit_amount' ) );

                // Item deposit amount
                $item_deposit += $item_remaining;

                // Item total
                $item_total = $item_deposit;

                // Taxable
                $item_taxes = false;

                if ( wc_tax_enabled() ) {
                    // Prices include tax
                    $prices_incl_tax = $order->get_meta( '_ova_prices_include_tax' );

                    // Get order remaining tax
                    $order_remaining_tax = floatval( $order->get_meta( '_ova_remaining_tax' ) );
                    // Get item remaining tax
                    $item_remaining_tax = floatval( $item->get_meta( 'ovabrw_remaining_tax' ) );
                    $order_remaining_tax -= $item_remaining_tax;
                    if ( $order_remaining_tax < 0 ) $order_remaining_tax = 0;

                    // Calculate tax
                    $calculate_tax_for  = $order->get_taxable_location();
                    $tax_rates          = WC_Tax::find_rates( $calculate_tax_for );

                    if ( $prices_incl_tax ) {
                        $taxes      = WC_Tax::calc_inclusive_tax( $item_total, $tax_rates );
                        $item_total -= WC_Tax::get_tax_total( $taxes );
                    } else {
                        $taxes = WC_Tax::calc_exclusive_tax( $item_total, $tax_rates );
                        $order_total += $item_remaining_tax;
                    }

                    $item_taxes = [
                        'total'    => $taxes,
                        'subtotal' => $taxes
                    ];

                    // Update tax
                    $item->update_meta_data( 'ovabrw_remaining_tax', 0 );
                    $order->update_meta_data( '_ova_remaining_tax', $order_remaining_tax );
                }

                // Update item meta
                $item->set_props([
                    'total'     => $item_total,
                    'subtotal'  => $item_total,
                    'taxes'     => $item_taxes
                ]);

                // Update item deposit amount
                $item->update_meta_data( 'ovabrw_deposit_amount', $item_deposit );

                // Update item remaining amount
                $item->update_meta_data( 'ovabrw_remaining_amount', 0 );

                // Update order fee ( insurance fees )
                if ( $item_remaining_insurance ) {
                    // Insurance key
                    $insurance_key = $order->get_meta( '_ova_insurance_key' );

                    // Order insurance
                    $order_insurance        = floatval( $order->get_meta( '_ova_insurance_amount' ) );
                    $order_insurance_tax    = floatval( $order->get_meta( '_ova_insurance_tax' ) );

                    // Order remaining insurance
                    $order_remaining_insurance      = floatval( $order->get_meta( '_ova_remaining_insurance' ) );
                    $order_remaining_insurance_tax  = floatval( $order->get_meta( '_ova_remaining_insurance_tax' ) );

                    // Item insurance
                    $item_insurance = floatval( $item->get_meta( 'ovabrw_insurance_amount' ) );

                    // Item insurance tax
                    $item_insurance_tax = floatval( $item->get_meta( 'ovabrw_insurance_tax' ) );

                    // Get fees
                    $fees = $order->get_fees();

                    // Update order insurance amount
                    if ( ovabrw_array_exists( $fees ) ) {
                        foreach ( $fees as $item_fee_id => $item_fee ) {
                            $fee_key = sanitize_title( $item_fee->get_name() );

                            if ( $fee_key === $insurance_key ) {
                                // Update order total
                                $order_total += $item_remaining_insurance;

                                // Item insurance amount
                                $item_insurance     += $item_remaining_insurance;
                                $item_insurance_tax += $item_remaining_insurance_tax;

                                // Order insurance amount
                                $order_insurance        += $item_remaining_insurance;
                                $order_insurance_tax    += $item_remaining_insurance_tax;

                                // Order remaining insurance amount
                                $order_remaining_insurance      -= $item_remaining_insurance;
                                $order_remaining_insurance_tax  -= $item_remaining_insurance_tax;

                                // Update item fee
                                if ( wc_tax_enabled() ) {
                                    // Update order total
                                    $order_total += $item_remaining_insurance_tax;

                                    $order_taxes = $order->get_taxes();
                                    $tax_item_id = 0;

                                    foreach ( $order_taxes as $tax_item ) {
                                        $tax_item_id = $tax_item->get_rate_id();
                                        if ( $tax_item_id ) break;
                                    }

                                    $item_fee->set_props([
                                        'total'     => $order_insurance,
                                        'subtotal'  => $order_insurance,
                                        'total_tax' => $order_insurance_tax,
                                        'taxes'     => [
                                            'total' => [
                                                $tax_item_id => $order_insurance_tax
                                            ]
                                        ]
                                    ]);

                                    // Update item meta data
                                    $item->update_meta_data( 'ovabrw_remaining_insurance_tax', 0 );
                                    $item->update_meta_data( 'ovabrw_insurance_tax', $item_insurance_tax );

                                    // Update order meta data
                                    $order->update_meta_data( '_ova_remaining_insurance_tax', $order_remaining_insurance_tax );
                                    $order->update_meta_data( '_ova_insurance_tax', $order_insurance_tax );
                                } else {
                                    $item_fee->set_props([
                                        'total'     => $order_insurance,
                                        'subtotal'  => $order_insurance
                                    ]);
                                }

                                $item_fee->set_amount( $order_insurance );
                                $item_fee->save();
                            }

                            // Update item meta data
                            $item->update_meta_data( 'ovabrw_remaining_insurance', 0 );
                            $item->update_meta_data( 'ovabrw_insurance_amount', $item_insurance );

                            // Update order meta data
                            $order->update_meta_data( '_ova_remaining_insurance', $order_remaining_insurance );
                            $order->update_meta_data( '_ova_insurance_amount', $order_insurance );
                        }
                    }
                }

                // Save item
                $item->save();

                // Update order deposit amount
                $order->update_meta_data( '_ova_deposit_amount', $order_deposit );

                // Update order remaining amount
                $order->update_meta_data( '_ova_remaining_amount', $order_remaining );

                // Order set total
                $order->set_total( $order_total );

                // Order update taxes
                $order->update_taxes();

                // Order save
                $order->save();

                // Send email
                if ( apply_filters( OVABRW_PREFIX.'send_email_pay_full', true ) ) {
                    $emails = WC_Emails::instance();
                    $emails->customer_invoice( $order );
                }

                wp_redirect( admin_url( 'post.php?post=' . absint( $order_id ) . '&action=edit' ) );
                exit;
            } elseif ( 'create_remaining_invoice' === $action ) {
                // Taxable
                if ( wc_tax_enabled() ) {
                    // Prices include tax
                    $prices_incl_tax = $order->get_meta( '_ova_prices_include_tax' );

                    if ( $prices_incl_tax ) {
                        // Calculate tax
                        $calculate_tax_for  = $order->get_taxable_location();
                        $tax_rates          = WC_Tax::find_rates( $calculate_tax_for );
                        $taxes              = WC_Tax::calc_inclusive_tax( $item_remaining, $tax_rates );
                        $item_remaining     -= WC_Tax::get_tax_total( $taxes );
                    }
                }

                // Data item
                $data_item = [
                    'product'   => $item->get_product(),
                    'qty'       => $item->get_quantity(),
                    'subtotal'  => $item_remaining,
                    'total'     => $item_remaining
                ];

                if ( $item_remaining_tax ) {
                    $data_item['remaining_tax'] = $item_remaining_tax;
                }
                if ( $item_remaining_insurance ) {
                    $data_item['insurance_amount'] = $item_remaining_insurance;
                }
                if ( $item_remaining_insurance_tax ) {
                    $data_item['insurance_tax'] = $item_remaining_insurance_tax;
                }

                $new_order_id = ovabrw_create_remaining_invoice( $order_id, $data_item );

                $new_order = wc_get_order( $new_order_id );
                $new_order->add_meta_data( '_ova_original_id', $order_id );
                $new_order->add_meta_data( '_ova_original_item_id', $item_id );
                $new_order->save();

                // Add remaining balance order id
                $item->add_meta_data( 'ovabrw_remaining_balance_order_id', $new_order_id, true );
                $item->save();

                // Order update remaining invoice IDs
                $remaining_invoice_ids = $order->get_meta( '_ova_remaining_invoice_ids' );
                if ( !$remaining_invoice_ids ) $remaining_invoice_ids = [];

                // Check order remaining invoice exists
                foreach ( $remaining_invoice_ids as $k => $remaining_invoice_id ) {
                    if ( !wc_get_order( $remaining_invoice_id ) ) {
                        unset( $remaining_invoice_ids[$k] );
                    }
                }

                // Update remaining invoice ids
                if ( !in_array( $new_order_id, $remaining_invoice_ids ) ) {
                    array_push( $remaining_invoice_ids, $new_order_id );
                }

                $order->update_meta_data( '_ova_remaining_invoice_ids', $remaining_invoice_ids );
                $order->save();

                // Email invoice
                $send_email = get_option( 'send_email_remaining_invoice_enable', 'yes' );
                if ( 'yes' === $send_email ) {
                    $emails = WC_Emails::instance();
                    $emails->customer_invoice( wc_get_order( $new_order_id ) );
                }
                
                wp_redirect( $new_order->get_edit_order_url() );
                exit;
            }
        }

        /**
         * Saved order items
         */
        public function ovabrw_saved_order_items( $order_id, $items ) {
            // Get order
            $order = wc_get_order( $order_id );
            if ( !$order ) return;

            // Order insurance amount
            if ( isset( $items['order_item_id'] ) && is_array( $items['order_item_id'] ) ) {
                // Insurance slug
                $insurance_key = $order->get_meta( '_ova_insurance_key' );

                foreach ( $items['order_item_id'] as $item_id ) {
                    $item = WC_Order_Factory::get_order_item( absint( $item_id ) );
                    if ( !$item ) {
                        continue;
                    }

                    if ( 'fee' === $item->get_type() && isset( $items['line_total'][$item_id] ) ) {
                        $fee_key = sanitize_title( $item->get_name() );

                        if ( $fee_key === $insurance_key ) {
                            $insurance_amount = floatval( $items['line_total'][$item_id] );

                            $order->update_meta_data( '_ova_insurance_amount', $insurance_amount );
                            $order->save();
                        }
                    }
                }
            }
        }

        /**
         * Hidden itemmeta
         */
        public function ovabrw_hidden_order_itemmeta( $meta_keys ) {
            $meta_keys[] = 'ovabrw_original_order_id';
            $meta_keys[] = 'ovabrw_remaining_balance_order_id';

            return $meta_keys;
        }

        /**
         * Manager order - Add custom columns
         */
        public function ovabrw_add_custom_columns( $add_column ) {
            // init
            $new_columns = [];

            foreach ( $add_column as $key => $column ){
                if ( $key === 'order_total' ) {
                    $new_columns['ovabrw_deposit_amount']   = esc_html__( 'Deposit', 'ova-brw' );
                    $new_columns['ovabrw_remaining_amount'] = esc_html__( 'Remaining', 'ova-brw' );
                    $new_columns['ovabrw_deposit_status']   = esc_html__( 'Deposit status', 'ova-brw');
                    $new_columns['ovabrw_insurance_status'] = esc_html__( 'Insurance', 'ova-brw');
                }

                $new_columns[$key] = $column;
            }

            return $new_columns;
        }

        /**
         * Manager order - Posts custom column
         */
        public function ovabrw_posts_custom_column( $column_id, $order ) {
            // For woocommerce order legacy
            if ( is_numeric( $order ) ) {
                $order = wc_get_order( $order );
            }
            // End

            if ( $order ) {
                // Order deposit amount
                if ( 'ovabrw_deposit_amount' === $column_id ) {
                    $deposit_amount = floatval( $order->get_meta( '_ova_deposit_amount' ) );

                    if ( $deposit_amount ): ?>
                        <span class="ova_deposit_amount">
                            <?php echo wc_price( $order->get_total(), [ 'currency' => $order->get_currency() ] ); ?>
                        </span>
                    <?php endif;
                }

                // Order remaining amount
                if ( 'ovabrw_remaining_amount' === $column_id ) {
                    $remaining_amount           = floatval( $order->get_meta( '_ova_remaining_amount' ) );
                    $remaining_tax              = floatval( $order->get_meta( '_ova_remaining_tax' ) );
                    $remaining_insurance        = floatval( $order->get_meta( '_ova_remaining_insurance' ) );
                    $remaining_insurance_tax    = floatval( $order->get_meta( '_ova_remaining_insurance_tax' ) );
                    $prices_incl_tax            = $order->get_meta( '_ova_prices_include_tax' );

                    // Remaining insurance amount
                    if ( $remaining_insurance ) {
                        $remaining_amount += $remaining_insurance;
                    }

                    if ( wc_tax_enabled() ) {
                        // Remaining insurance tax amount
                        if ( $remaining_insurance_tax ) {
                            $remaining_amount += $remaining_insurance_tax;
                        }

                        // Remaining tax amount
                        if ( $remaining_tax && ! $prices_incl_tax ) {
                            $remaining_amount += $remaining_tax;
                        }
                    }

                    if ( $remaining_amount ) { ?>
                        <span class="ova_deposit_amount">
                            <?php echo ovabrw_wc_price( $remaining_amount, [ 'currency' => $order->get_currency() ] ); ?>
                        </span>
                    <?php }
                }

                // Order deposit status
                if ( 'ovabrw_deposit_status' === $column_id ) {
                    // Deposit
                    $is_deposit = $order->get_meta( '_ova_has_deposit' );

                    if ( $is_deposit ) {
                        $remaining_amount = floatval( $order->get_meta( '_ova_remaining_amount' ) );

                        if ( $remaining_amount ):
                            $is_remaining_invoice   = false;
                            $remaining_invoice_ids  = $order->get_meta( '_ova_remaining_invoice_ids' );

                            // Check remaining invoice ids
                            if ( ovabrw_array_exists( $remaining_invoice_ids ) ) {
                                foreach ( $remaining_invoice_ids as $remaining_invoice_id ) {
                                    if ( wc_get_order( $remaining_invoice_id ) ) {
                                        $is_remaining_invoice = true;
                                        break;
                                    }
                                }
                            }

                            if ( $is_remaining_invoice ): ?>
                                <mark class="ovabrw-order-status status-processing">
                                    <span class="ovabrw-deposit-status">
                                        <?php esc_html_e( 'Original Payment', 'ova-brw' ); ?>
                                    </span>
                                </mark>
                            <?php else: ?>
                                <mark class="ovabrw-order-status status-pending">
                                    <span class="ovabrw-deposit-status">
                                        <?php esc_html_e( 'Partial Payment', 'ova-brw' ); ?>
                                    </span>
                                </mark>
                            <?php endif; ?>
                        <?php else: ?>
                            <mark class="ovabrw-order-status status-processing">
                                <span class="ovabrw-deposit-status">
                                    <?php esc_html_e( 'Full Payment', 'ova-brw' ); ?>
                                </span>
                            </mark>
                        <?php endif;

                        // Remaining Invoice
                        $remaining_invoice_ids = $order->get_meta( '_ova_remaining_invoice_ids' );
                        if ( ovabrw_array_exists( $remaining_invoice_ids ) ) {
                            foreach ( $remaining_invoice_ids as $order_remaining_id ) {
                                $remaining_order = wc_get_order( $order_remaining_id );

                                if ( $remaining_order ): ?>
                                    <mark class="ovabrw-order-view">
                                        <a href="<?php echo esc_url( $remaining_order->get_edit_order_url() ); ?>" class="button" target="_blank">
                                            <?php 
                                                echo wp_kses_post( sprintf( __( 'Remaining Invoice #%1$s', 'ova-brw' ), $remaining_order->get_order_number() ) );
                                            ?>
                                        </a>
                                    </mark>
                                <?php endif;
                            }
                        }
                    }

                    // Original Order
                    $original_id = $order->get_meta( '_ova_original_id' );

                    if ( $original_id ) {
                        $original_order = wc_get_order( $original_id );
                        if ( $original_order ): ?>
                            <mark class="ovabrw-order-view">
                                <a href="<?php echo esc_url( $original_order->get_edit_order_url() ); ?>" class="button" target="_blank">
                                    <?php 
                                        echo wp_kses_post( sprintf( __( 'Original Order #%1$s', 'ova-brw' ), $original_order->get_order_number() ) );
                                    ?>
                                </a>
                            </mark>
                        <?php endif;
                    }
                }

                // Order insurance status
                if ( 'ovabrw_insurance_status' === $column_id ) {
                    $insurance_amount = $order->get_meta( '_ova_insurance_amount' );
                    if ( '' !== $insurance_amount ) {
                        $insurance_amount = floatval( $insurance_amount );
                        if ( $insurance_amount > 0 ): ?>
                            <mark class="ovabrw-order-status status-on-hold">
                                <span class="ovabrw-insurance-status">
                                    <?php esc_html_e( 'Received', 'ova-brw' ); ?>
                                </span>
                            </mark>
                        <?php else: ?>
                            <mark class="ovabrw-order-status status-processing">
                                <span class="ovabrw-insurance-status">
                                    <?php esc_html_e( 'Paid for Customers', 'ova-brw' ); ?>
                                </span>
                            </mark>
                        <?php endif;
                    }
                }
            }
        }

        /**
         * Before saved order items
         */
        public function before_save_order_item( $item ) {
            // Get item id
            $item_id = method_exists( $item, 'get_id' ) ? $item->get_id() : '';

            // Get item object
            $item_obj = WC_Order_Factory::get_order_item( absint( $item_id ) );

            if ( $item_obj && apply_filters( OVABRW_PREFIX.'saved_order_item_custom_fields', true ) ) {
                // Pick-up date
                $pickup_date = strtotime( $item->get_meta( 'ovabrw_pickup_date' ) );
                if ( $pickup_date ) {
                    $item->update_meta_data( 'ovabrw_pickup_date_strtotime', $pickup_date );
                }

                // Drop-off date
                $dropoff_date = strtotime( $item->get_meta( 'ovabrw_pickoff_date' ) );
                if ( $dropoff_date ) {
                    $item->update_meta_data( 'ovabrw_dropoff_date_strtotime', $dropoff_date );
                }
            }
        }

        /**
         * Email styles
         */
        public function email_styles( $css ) {
            $css .= apply_filters( OVABRW_PREFIX.'email_styles', 'dt {
                float: left;
                clear: both;
                margin-right: .25em;
                display: inline-block;
                list-style: none outside;
                font-size: 14px;
                font-weight: bold;
            }
            dd {
                font-size: 14px;
                margin: 0;
                margin-bottom: 10px;
            }');

            return $css;
        }
    }

    return new OVABRW_Deposit();
}