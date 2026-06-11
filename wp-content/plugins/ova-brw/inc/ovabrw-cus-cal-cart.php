<?php if ( !defined( 'ABSPATH' ) ) exit();

/**
 * Before calculate totals
 */
add_action( 'woocommerce_before_calculate_totals', function( $cart_object ) {
    $deposit_amount     = $remaining_amount = $remaining_tax = 0;
    $insurance_amount   = $insurance_tax = $remaining_insurance = $remaining_insurance_tax = 0;
    $has_deposit        = false;

    // Init deposit
    WC()->cart->deposit_info = [];

    // Loop cart object
    foreach ( $cart_object->get_cart() as $cart_item_key => $cart_item ) {
        // Get product id
        $product_id = $cart_item['data']->get_id();

        // Check rental product
        if ( !$product_id || !$cart_item['data']->is_type( OVABRW_RENTAL ) ) continue;

        // Quantity
        $quantity = (int)ovabrw_get_meta_data( 'ovabrw_quantity', $cart_item, 1 );

        // Check-in date
        $checkin_date = strtotime( ovabrw_get_meta_data( 'ovabrw_pickup_date', $cart_item ) );

        // Check-out date
        $checkout_date = strtotime( ovabrw_get_meta_data( 'ovabrw_pickoff_date', $cart_item ) );

        // Number of adults
        $numberof_adults = (int)ovabrw_get_meta_data( 'ovabrw_adults', $cart_item );

        // Number of children
        $numberof_children = (int)ovabrw_get_meta_data( 'ovabrw_childrens', $cart_item );

        // Number of babies
        $numberof_babies = (int)ovabrw_get_meta_data( 'ovabrw_babies', $cart_item );

        // Total number of guests
        $numberof_guests = $numberof_adults + $numberof_children + $numberof_babies;

        // Get sub-total
        $subtotal = get_price_by_guests( $product_id, $checkin_date, $checkout_date, $cart_item );

        // Insurance amount
        $sub_insurance = $sub_remaining_insurance = 0;

        // Type of insurance
        $typeof_insurance = ovabrw_get_post_meta( $product_id, 'typeof_insurance', 'general' );
        if ( 'general' === $typeof_insurance ) {
            $sub_insurance = (float)ovabrw_get_post_meta( $product_id, 'amount_insurance' );
            $sub_insurance = $sub_insurance*$numberof_guests*$quantity;
        } elseif ( 'guest' === $typeof_insurance ) {
            // Adult insurance
            $adult_insurance = (float)ovabrw_get_post_meta( $product_id, 'adult_insurance' );
            $sub_insurance += $adult_insurance*$numberof_adults;

            // Child insurance
            $child_insurance = (float)ovabrw_get_post_meta( $product_id, 'child_insurance' );
            $sub_insurance += $child_insurance*$numberof_children;

            // Baby insurance
            $baby_insurance = (float)ovabrw_get_post_meta( $product_id, 'baby_insurance' );
            $sub_insurance += $baby_insurance*$numberof_babies;
        }

        if ( is_plugin_active( 'woocommerce-multilingual/wpml-woocommerce.php' ) ) {
            $subtotal       = ovabrw_convert_price( $subtotal );
            $sub_insurance  = ovabrw_convert_price( $sub_insurance );
        }

        // Get deposit
        $is_deposit = isset( $cart_item['is_deposit'] ) ? $cart_item['is_deposit'] : false;

        if ( $is_deposit ) {
            $has_deposit    = true;
            $sub_deposit    = 0;
            $deposit_type   = get_post_meta( $product_id, 'ovabrw_type_deposit', true );
            $deposit_value  = (float)get_post_meta( $product_id, 'ovabrw_amount_deposit', true );

            // Calculate deposit
            if ( 'percent' === $deposit_type ) {
                $sub_deposit = apply_filters( OVABRW_PREFIX.'calculate_deposit_percent', ( $subtotal * $deposit_value ) / 100, $cart_item );

                if ( $sub_insurance && !ovabrw_insurance_paid_once() ) {
                    $sub_remaining_insurance = $sub_insurance - floatval( ( $sub_insurance * $deposit_value ) / 100 );
                    $sub_insurance = floatval( ( $sub_insurance * $deposit_value ) / 100 );
                }
            } elseif ( 'value' === $deposit_type ) {
                $sub_deposit = apply_filters( OVABRW_PREFIX.'calculate_deposit_fixed', $deposit_value, $cart_item );
            }

            // Sub remaining
            $sub_remaining = floatval( $subtotal - $sub_deposit );

            // Coupons
            $coupons = WC()->cart->get_applied_coupons();
            if ( ovabrw_array_exists( $coupons ) ) {
                foreach ( $coupons as $coupon_code ) {
                    $coupon = new WC_Coupon( $coupon_code );

                    // Valid coupon
                    if ( !$coupon->is_valid_for_product( $cart_item['data'] ) ) continue;

                    if ( $coupon && is_object( $coupon ) ) {
                        $coupon_type    = $coupon->get_discount_type();
                        $coupon_amount  = $coupon->get_amount();
                        
                        if ( $coupon_type && $coupon_amount ) {
                            if ( 'fixed_cart' == $coupon_type || 'fixed_product' == $coupon_type ) {
                                $sub_deposit -= $coupon_amount;
                            } elseif ( 'percent' == $coupon_type ) {
                                $sub_deposit    -= $sub_deposit * ( $coupon_amount / 100 );
                                $sub_remaining  -= $sub_remaining * ( $coupon_amount / 100 );
                            }
                        }
                    }
                }
            }

            // Remaining tax
            $sub_remaining_taxes    = ovabrw_get_taxes_by_price( $cart_item['data'], ovabrw_convert_price( $sub_remaining, [], false ) );
            $remaining_tax          += $sub_remaining_taxes;

            // Cart item add data
            $cart_item['data']->add_meta_data( 'is_deposit', $is_deposit, true );
            $cart_item['data']->add_meta_data( 'deposit_type', $deposit_type, true );
            $cart_item['data']->add_meta_data( 'deposit_value', $deposit_value, true );
            $cart_item['data']->add_meta_data( 'deposit_amount', round( $sub_deposit, wc_get_price_decimals() ), true );
            $cart_item['data']->add_meta_data( 'remaining_amount', round( $sub_remaining, wc_get_price_decimals() ), true );
            $cart_item['data']->add_meta_data( 'remaining_tax', round( $sub_remaining_taxes, wc_get_price_decimals() ), true );
            $cart_item['data']->add_meta_data( 'total_payable', round( $subtotal, wc_get_price_decimals() ), true );

            // Set item price
            $cart_item['data']->set_price( round( $sub_deposit / $quantity, wc_get_price_decimals() ) );

            $deposit_amount     += $sub_deposit;
            $remaining_amount   += $sub_remaining;
        } else {
            // Set item price
            $cart_item['data']->set_price( round( $subtotal / $quantity, wc_get_price_decimals() ) );
        }

        // Insurance
        if ( $sub_insurance ) {
            $insurance_amount += $sub_insurance;
            $cart_item['data']->add_meta_data( 'insurance_amount', round( $sub_insurance, wc_get_price_decimals() ), true );

            $sub_insurance_tax = ovabrw_get_insurance_tax_amount( ovabrw_convert_price( $sub_insurance, array(), false ) );

            if ( $sub_insurance_tax ) {
                $insurance_tax += $sub_insurance_tax;

                $cart_item['data']->add_meta_data( 'insurance_tax', round( $sub_insurance_tax, wc_get_price_decimals() ), true );
            }
        }

        // Remaining insurance
        if ( $sub_remaining_insurance ) {
            $remaining_insurance += $sub_remaining_insurance;
            $cart_item['data']->add_meta_data( 'remaining_insurance', round( $sub_remaining_insurance, wc_get_price_decimals() ), true );

            // Get sub-remaining insurance tax
            $sub_remaining_insurance_tax = ovabrw_get_insurance_tax_amount( ovabrw_convert_price( $sub_remaining_insurance, [], false ) );
            if ( $sub_remaining_insurance_tax ) {
                $remaining_insurance_tax += $sub_remaining_insurance_tax;

                // Add remaining insurance tax
                $cart_item['data']->add_meta_data( 'remaining_insurance_tax', round( $sub_remaining_insurance_tax, wc_get_price_decimals() ), true );
            }
        }

        // Quantity
        $cart_object->cart_contents[ $cart_item_key ]['quantity'] = $quantity;
    } // END loop cart object

    // Deposit info
    if ( $has_deposit ) {
        WC()->cart->deposit_info[ 'has_deposit' ]       = $has_deposit;
        WC()->cart->deposit_info[ 'deposit_amount' ]    = round( $deposit_amount, wc_get_price_decimals() );
        WC()->cart->deposit_info[ 'remaining_amount' ]  = round( $remaining_amount, wc_get_price_decimals() );
        WC()->cart->deposit_info[ 'remaining_tax' ]     = round( $remaining_tax, wc_get_price_decimals() );
    } // END if

    // Cart fee - Insurance
    if ( $insurance_amount ) {
        $insurance_name         = ovabrw_get_insurance_fee_name();
        $enable_insurance_tax   = ovabrw_insurance_tax_enabled();
        $tax_class              = ovabrw_get_insurance_tax_class();

        WC()->cart->add_fee( $insurance_name, ovabrw_convert_price( $insurance_amount, [], false ), $enable_insurance_tax, $tax_class );

        WC()->cart->deposit_info[ 'insurance_amount' ]  = $insurance_amount;
        WC()->cart->deposit_info[ 'insurance_tax' ]     = $insurance_tax;
        WC()->cart->deposit_info[ 'insurance_key' ]     = ovabrw_get_insurance_fee_key();
    } // END cart fee

    // Remaining insurance
    if ( $remaining_insurance ) {
        WC()->cart->deposit_info[ 'remaining_insurance' ]       = $remaining_insurance;
        WC()->cart->deposit_info[ 'remaining_insurance_tax' ]   = $remaining_insurance_tax;
    }
}, 10 );

/**
 * Get price a product with Pick-up date, Drop-off date
 */
if ( !function_exists( 'get_price_by_guests' ) ) {
    function get_price_by_guests( $product_id = false, $checkin_date = '', $checkout_date = '', $cart_item = [] ) {
        // Date foramt
        $date_format = ovabrw_get_date_format();

        // Get new dates
        $new_dates = ovabrw_new_input_date( $product_id, $checkin_date, $checkout_date, $date_format );

        // New check-in date
        $new_checkin = ovabrw_get_meta_data( 'pickup_date_new', $new_dates );

        // New check-out date
        $new_checkout = ovabrw_get_meta_data( 'pickoff_date_new', $new_dates );

        // Number of adults
        $numberof_adults = (int)ovabrw_get_meta_data( 'ovabrw_adults', $cart_item );

        // Number of children
        $numberof_children = (int)ovabrw_get_meta_data( 'ovabrw_childrens', $cart_item );

        // Number of babies
        $numberof_babies = (int)ovabrw_get_meta_data( 'ovabrw_babies', $cart_item );

        // Time From
        $time_from = ovabrw_get_meta_data( 'ovabrw_time_from', $cart_item );

        // Global price
        $line_total = ovabrw_price_global( $product_id, $new_checkin, $new_checkout, $numberof_adults, $numberof_children, $numberof_babies, $time_from );

        // Resources
        $resources = ovabrw_get_meta_data( 'ovabrw_resources', $cart_item );
        if ( ovabrw_array_exists( $resources ) ) {
            // Get resource guests
            $resource_guests = ovabrw_get_meta_data( 'ovabrw_resource_guests', $cart_item );

            // Get total resources
            $total_resources = ovabrw_get_total_resoures( $product_id, $resources, $numberof_adults, $numberof_children, $numberof_babies, $resource_guests );

            // Update line total
            $line_total += $total_resources;
        }

        // Services
        $services = ovabrw_get_meta_data( 'ovabrw_services', $cart_item );
        if ( ovabrw_array_exists( $services ) ) {
            // Service guests
            $service_guests = ovabrw_get_meta_data( 'ovabrw_service_guests', $cart_item );
            
            // Get total services
            $total_services = ovabrw_get_total_services( $product_id, $services, $numberof_adults, $numberof_children, $numberof_babies, $service_guests );

            // Update line total
            $line_total += $total_services;
        }

        // Quantity
        $ovabrw_quantity = (int)ovabrw_get_meta_data( 'ovabrw_quantity', $cart_item, 1 );
        $line_total *= $ovabrw_quantity;

        // Custom checkout fields
        $cckf = ovabrw_get_meta_data( 'custom_ckf', $cart_item );
        if ( ovabrw_array_exists( $cckf ) ) {
            // Custom checkout field quantity
            $cckf_qty = ovabrw_get_meta_data( 'cckf_qty', $cart_item );

            // Get total cckf
            $total_cckf = ovabrw_get_price_cckf( $product_id, $cckf, $cckf_qty );
            if ( $total_cckf ) $line_total += $total_cckf;
        }

        // Decimals
        $line_total = round( $line_total, wc_get_price_decimals() );

        return apply_filters( OVABRW_PREFIX.'get_price_by_guests', $line_total, $product_id, $checkin_date, $checkout_date, $cart_item );
    }
}

/**
 * Get price per guests
 */
if ( !function_exists( 'ovabrw_price_per_guests' ) ) {
    function ovabrw_price_per_guests( $product_id = false, $checkin_date = '', $numberof_adults = 0, $numberof_children = 0, $numberof_babies = 0, $time_from = '' ) {
        // Adults price
        $adult_price    = ovabrw_regular_price_global( $product_id, $checkin_date );
        $child_price    = (float)get_post_meta( $product_id, 'ovabrw_children_price', true );
        $baby_price     = (float)get_post_meta( $product_id, 'ovabrw_baby_price', true );

        // Duration
        $duration = get_post_meta( $product_id, 'ovabrw_duration_checkbox', true );
        if ( $duration && $time_from ) {
            $weekday = ovabrw_get_weekday( $checkin_date );

            $schedule_price = ovabrw_get_price_from_schedule( $product_id, $weekday, $time_from );

            if ( $schedule_price ) {
                $adult_price    = $schedule_price['adults_price'];
                $child_price    = $schedule_price['childrens_price'];
                $baby_price     = $schedule_price['babies_price'];
            }
        }

        // Global Discount (GD)
        $gd_prices = ovabrw_get_price_by_global_discount( $product_id, $numberof_adults, $numberof_children, $numberof_babies );

        if ( $gd_prices && is_array( $gd_prices ) ) {
            $adult_price    = $gd_prices['adults_price'];
            $child_price    = $gd_prices['childrens_price'];
            $baby_price    = $gd_prices['babies_price'];
        }

        // Special Time (ST)
        $st_prices = ovabrw_get_price_by_special_time( $product_id, $checkin_date, $numberof_adults, $numberof_children, $numberof_babies );
        if ( $st_prices && is_array( $st_prices ) ) {
            $adult_price    = $st_prices['adults_price'];
            $child_price    = $st_prices['childrens_price'];
            $baby_price     = $st_prices['babies_price'];
        }

        $price_guests = array(
            'adults_price'      => $adult_price,
            'childrens_price'   => $child_price,
            'babies_price'      => $baby_price,
        );

        return apply_filters( OVABRW_PREFIX.'price_per_guests', $price_guests, $product_id, $checkin_date, $numberof_adults, $numberof_children, $numberof_babies, $time_from );
    }
}

/**
 * Get price in global
 */
if ( !function_exists( 'ovabrw_price_global' ) ) {
    function ovabrw_price_global( $product_id = false, $checkin_date = '', $checkout_date = '', $numberof_adults = 0, $numberof_children = 0, $numberof_babies = 0, $time_from = '' ) {
        // Adults price
        $adult_price    = ovabrw_regular_price_global( $product_id, $checkin_date );
        $child_price    = (float)get_post_meta( $product_id, 'ovabrw_children_price', true );
        $baby_price     = (float)get_post_meta( $product_id, 'ovabrw_baby_price', true );

        // Duration
        $duration   = get_post_meta( $product_id, 'ovabrw_duration_checkbox', true );
        $type_price = '';
        
        if ( $duration && $time_from ) {
            $weekday = ovabrw_get_weekday( $checkin_date );

            $schedule_price = ovabrw_get_price_from_schedule( $product_id, $weekday, $time_from );

            if ( $schedule_price ) {
                $adult_price    = $schedule_price['adults_price'];
                $child_price    = $schedule_price['childrens_price'];
                $baby_price     = $schedule_price['babies_price'];
                $type_price     = $schedule_price['type_price'];
            }
        }

        // Global Discount (GD)
        $gd_prices = ovabrw_get_price_by_global_discount( $product_id, $numberof_adults, $numberof_children, $numberof_babies );
        if ( $gd_prices && is_array( $gd_prices ) ) {
            $adult_price    = $gd_prices['adults_price'];
            $child_price    = $gd_prices['childrens_price'];
            $baby_price     = $gd_prices['babies_price'];
        }

        // Special Time (ST)
        $st_prices = ovabrw_get_price_by_special_time( $product_id, $checkin_date, $numberof_adults, $numberof_children, $numberof_babies );
        if ( $st_prices && is_array( $st_prices ) ) {
            $adult_price    = $st_prices['adults_price'];
            $child_price    = $st_prices['childrens_price'];
            $baby_price    = $st_prices['babies_price'];
        }

        $total = $adult_price*$numberof_adults + $child_price*$numberof_children + $baby_price*$numberof_babies;

        if ( $type_price === 'total' ) {
            $total = 0;
            if ( $numberof_adults ) $total += $adult_price;
            if ( $numberof_children ) $total += $child_price;
            if ( $numberof_babies ) $total += $baby_price;
        }

        return apply_filters( OVABRW_PREFIX.'price_global', floatval( $total ), $product_id, $checkin_date, $checkout_date, $numberof_adults, $numberof_children, $numberof_babies, $time_from );
    }
}

/**
 * Get Sale Price in Global
 */
if ( !function_exists( 'ovabrw_regular_price_global' ) ) {
    function ovabrw_regular_price_global( $product_id, $checkin_date ) {
        // Regular Price
        $regular_price = get_post_meta( $product_id, '_regular_price', true );

        if ( ovabrw_wcml_get_product_price( $product_id, '_regular_price' ) ) {
            $regular_price = ovabrw_wcml_get_product_price( $product_id, '_regular_price' );
        }

        // Sale Price
        $sale_price = get_post_meta( $product_id, '_sale_price', true );

        if ( ovabrw_wcml_get_product_price( $product_id, '_sale_price' ) ) {
            $sale_price = ovabrw_wcml_get_product_price( $product_id, '_sale_price' );
        }
        
        if ( $sale_price ) {
            // Sale date
            $sale_from  = absint( get_post_meta( $product_id, '_sale_price_dates_from', true ) );
            $sale_to    = absint( get_post_meta( $product_id, '_sale_price_dates_to', true ) );

            if ( $sale_from && $sale_to ) {
                if ( $sale_from <= $checkin_date && $checkin_date <= $sale_to ) {
                    $regular_price = $sale_price;
                }
            } else if ( $sale_from && !$sale_to ) {
                if ( $sale_from <= $checkin_date ) {
                    $regular_price = $sale_price;
                }
            } else if ( !$sale_from && $sale_to ) {
                if ( $checkin_date <= $sale_to ) {
                    $regular_price = $sale_price;
                }
            } else {
                $regular_price = $sale_price;
            }
        }

        if ( ! $regular_price ) {
            $regular_price = 0;
        }

        return apply_filters( OVABRW_PREFIX.'regular_price_global', (float)$regular_price, $product_id, $checkin_date );
    }
}

/**
 * Get Price in Global Discount (GD)
 */
if ( !function_exists( 'ovabrw_get_price_by_global_discount' ) ) {
    function ovabrw_get_price_by_global_discount( $product_id = false, $numberof_adults = 0, $numberof_children = 0, $numberof_babies = 0 ) {
        $ovabrw_gd_duration_min = get_post_meta( $product_id, 'ovabrw_gd_duration_min', true );

        if ( $ovabrw_gd_duration_min && is_array( $ovabrw_gd_duration_min ) ) {
            asort( $ovabrw_gd_duration_min );

            // Total number of guests
            $numberof_guests = apply_filters( 'ovabrw_get_total_guests_by_global_discount', (int)$numberof_adults + (int)$numberof_children + (int)$numberof_babies, $product_id, $numberof_adults, $numberof_children, $numberof_babies );

            foreach ( $ovabrw_gd_duration_min as $key => $duration_min ) {
                $ovabrw_gd_duration_max      = get_post_meta( $product_id, 'ovabrw_gd_duration_max', true );
                $ovabrw_gd_adult_price       = get_post_meta( $product_id, 'ovabrw_gd_adult_price', true );
                $ovabrw_gd_children_price    = get_post_meta( $product_id, 'ovabrw_gd_children_price', true );
                $ovabrw_gd_baby_price        = get_post_meta( $product_id, 'ovabrw_gd_baby_price', true );

                // Duration Max Number
                $gd_duration_max = 0;
                if ( isset( $ovabrw_gd_duration_max[$key] ) && $ovabrw_gd_duration_max[$key] ) {
                    $gd_duration_max = floatval( $ovabrw_gd_duration_max[$key] );
                }

                // Discount Adult Price
                $gd_adult_price = 0;
                if ( isset( $ovabrw_gd_adult_price[$key] ) && $ovabrw_gd_adult_price[$key] ) {
                    $gd_adult_price = floatval( $ovabrw_gd_adult_price[$key] );
                }

                // Discount Children Price
                $gd_child_price = 0;
                if ( isset( $ovabrw_gd_children_price[$key] ) && $ovabrw_gd_children_price[$key] ) {
                    $gd_child_price = floatval( $ovabrw_gd_children_price[$key] );
                }

                // Discount Baby Price
                $gd_baby_price = 0;
                if ( isset( $ovabrw_gd_baby_price[$key] ) && $ovabrw_gd_baby_price[$key] ) {
                    $gd_baby_price = floatval( $ovabrw_gd_baby_price[$key] );
                }

                if ( $numberof_guests >= $duration_min && $numberof_guests <= $gd_duration_max ){
                    $gd_prices = array(
                        'adults_price'      => $gd_adult_price,
                        'childrens_price'   => $gd_child_price,
                        'babies_price'      => $gd_baby_price,
                    );

                    return apply_filters( OVABRW_PREFIX.'get_price_by_global_discount', $gd_prices, $product_id, $numberof_adults, $numberof_children, $numberof_babies );
                }
            }
        }

        return apply_filters( OVABRW_PREFIX.'get_price_by_global_discount', false, $product_id, $numberof_adults, $numberof_children, $numberof_babies );
    }
}

/**
 * Get Price Product
 */
if ( !function_exists( 'ovabrw_get_price_product' ) ) {
    function ovabrw_get_price_product( $product_id ) {
        $product        = wc_get_product( $product_id );
        $regular_price  = 0;

        if ( $product->is_on_sale() && $product->get_sale_price() ) {
            $regular_price  = $product->get_sale_price();
            $sale_price     = $product->get_regular_price();
        } else {
            $regular_price = $product->get_regular_price();
        }

        return apply_filters( OVABRW_PREFIX.'get_price_product', $regular_price, $product_id );
    }
}

/**
 * Get Price in Special Time (ST)
 */
if ( !function_exists( 'ovabrw_get_price_by_special_time' ) ) {
    function ovabrw_get_price_by_special_time( $product_id = false, $checkin_date = '', $numberof_adults = 0, $numberof_children = 0, $numberof_babies = 0 ) {
        // Get date format
        $date_format = ovabrw_get_date_format();

        // Check-in date
        $checkin_date = strtotime( date_i18n( $date_format, $checkin_date ) );
        if ( !$checkin_date ) return false;

        // Special
        $st_prices = [];

        // Get start date
        $ovabrw_st_startdate = get_post_meta( $product_id, 'ovabrw_st_startdate', true );
        if ( ovabrw_array_exists( $ovabrw_st_startdate ) ) {
            // Total number of guests
            $numberof_guests = (int)$numberof_adults + (int)$numberof_children + (int)$numberof_babies;

            // ST
            $ovabrw_st_enddate          = get_post_meta( $product_id, 'ovabrw_st_enddate', true );
            $ovabrw_st_adult_price      = get_post_meta( $product_id, 'ovabrw_st_adult_price', true );
            $ovabrw_st_children_price   = get_post_meta( $product_id, 'ovabrw_st_children_price', true );
            $ovabrw_st_baby_price       = get_post_meta( $product_id, 'ovabrw_st_baby_price', true );
            $ovabrw_st_discount         = get_post_meta( $product_id, 'ovabrw_st_discount', true );

            foreach ( $ovabrw_st_startdate as $key => $start_date ) {
                // Start date
                $start_date = strtotime( ovabrw_get_meta_data( $key, $ovabrw_st_startdate ) );

                // End date
                $end_date = strtotime( ovabrw_get_meta_data( $key, $ovabrw_st_enddate ) );

                // Adult Price
                $adult_price = (float)ovabrw_get_meta_data( $key,$ovabrw_st_adult_price  );

                // Child Price
                $child_price = (float)ovabrw_get_meta_data( $key, $ovabrw_st_children_price );

                // Baby Price
                $baby_price = (float)ovabrw_get_meta_data( $key, $ovabrw_st_baby_price );

                // Discounts
                $discount = ovabrw_get_meta_data( $key, $ovabrw_st_discount, [] );

                if ( $start_date && $end_date ) {
                    if ( $checkin_date >= $start_date && $checkin_date <= $end_date ) {
                        $st_prices = [
                            'adults_price'      => $adult_price,
                            'childrens_price'   => $child_price,
                            'babies_price'      => $baby_price
                        ];

                        if ( ovabrw_array_exists( $discount ) ) {
                            // Min
                            $dsc_min = ovabrw_get_meta_data( 'min', $discount, [] );

                            // Max
                            $dsc_max = ovabrw_get_meta_data( 'max', $discount, [] );

                            // Adult price
                            $dsc_adult_price = ovabrw_get_meta_data( 'adult_price', $discount, [] );

                            // Children price
                            $dsc_child_price = ovabrw_get_meta_data( 'children_price', $discount, [] );

                            // Baby price
                            $dsc_baby_price = ovabrw_get_meta_data( 'baby_price', $discount, [] );

                            if ( ovabrw_array_exists( $dsc_min ) ) {
                                foreach ( $dsc_min as $dsc_key => $dsc_min_number ) {
                                    // Min number
                                    $dsc_min_number = absint( $dsc_min_number );

                                    // Max number
                                    $dsc_max_number = absint( ovabrw_get_meta_data( $dsc_key, $dsc_max ) );

                                    // Adult amount
                                    $dsc_adult_amount = (float)ovabrw_get_meta_data( $dsc_key, $dsc_adult_price );

                                    // Child amount
                                    $dsc_child_amount = (float)ovabrw_get_meta_data( $dsc_key, $dsc_child_price );

                                    // Baby amount
                                    $dsc_baby_amount = (float)ovabrw_get_meta_data( $dsc_key, $dsc_baby_price );

                                    if ( $numberof_guests >= $dsc_min_number && $numberof_guests <= $dsc_max_number  ) {
                                        $st_prices = [
                                            'adults_price'      => $dsc_adult_amount,
                                            'childrens_price'   => $dsc_child_amount,
                                            'babies_price'      => $dsc_baby_amount
                                        ];
                                    }
                                }
                            }
                        }

                        return apply_filters( OVABRW_PREFIX.'get_price_by_special_time', $st_prices, $product_id, $checkin_date, $numberof_adults, $numberof_children, $numberof_babies );
                    }
                }
            }
        }

        return apply_filters( OVABRW_PREFIX.'get_price_by_special_time', false, $product_id, $checkin_date, $numberof_adults, $numberof_children, $numberof_babies );
    }
}

/**
 * Get resource prices
 */
if ( !function_exists( 'ovabrw_get_total_resoures' ) ) {
    function ovabrw_get_total_resoures( $product_id = false, $resources = [], $numberof_adults = 0, $numberof_children = 0, $numberof_babies = 0, $resource_guests = [] ) {
        // init
        $total_resources = 0;

        if ( ovabrw_array_exists( $resources ) ) {
            // Get option ids
            $opt_ids = ovabrw_get_post_meta( $product_id, 'rs_id' );

            // Get adult prices
            $adult_prices = ovabrw_get_post_meta( $product_id, 'rs_adult_price' );

            // Get child prices
            $child_prices = ovabrw_get_post_meta( $product_id, 'rs_children_price' );

            // Get baby prices
            $baby_prices = ovabrw_get_post_meta( $product_id, 'rs_baby_price' );

            // Get durations
            $durations = ovabrw_get_post_meta( $product_id, 'rs_duration_type' );

            // Loop
            foreach ( $resources as $rs_id => $rs_name ) {
                // Search index
                $key = array_search( $rs_id, $opt_ids );
                if ( !is_bool( $key ) ) {
                    // Get adult price
                    $adult_price = (float)ovabrw_get_meta_data( $key, $adult_prices );

                    // Get child price
                    $child_price = (float)ovabrw_get_meta_data( $key, $child_prices );

                    // Get baby price
                    $baby_price = (float)ovabrw_get_meta_data( $key, $baby_prices );

                    // Get duration
                    $duration = ovabrw_get_meta_data( $key, $durations, 'person' );

                    // Get resource guests
                    $rs_guests = ovabrw_get_meta_data( $rs_id, $resource_guests );
                    if ( ovabrw_array_exists( $rs_guests ) ) {
                        // Get number of adults
                        $number_adult = (int)ovabrw_get_meta_data( 'adult', $rs_guests );

                        // Get number of children
                        $number_child = (int)ovabrw_get_meta_data( 'child', $rs_guests );

                        // Get number of babies
                        $number_baby = (int)ovabrw_get_meta_data( 'baby', $rs_guests );

                        if ( 'person' === $duration ) {
                            $total_resources += floatval( $adult_price*$number_adult ) + floatval( $child_price*$number_child ) + floatval( $baby_price*$number_baby );
                        } else {
                            if ( $number_adult ) $total_resources += (float)$adult_price;
                            if ( $number_child ) $total_resources += (float)$child_price;
                            if ( $number_baby ) $total_resources += (float)$baby_price;
                        }
                    } else {
                        if ( 'person' === $duration ) {
                            $total_resources += floatval( $adult_price*$numberof_adults ) + floatval( $child_price*$numberof_children ) + floatval( $baby_price*$numberof_babies );
                        } else {
                            $total_resources += (float)$adult_price + (float)$child_price + (float)$baby_price;
                        }
                    }
                }
            }
        }

        return apply_filters( OVABRW_PREFIX.'get_total_resoures', (float)$total_resources, $product_id, $resources, $numberof_adults, $numberof_children, $numberof_babies, $resource_guests );
    }
}

/**
 * Get Price in Services
 */
if ( !function_exists( 'ovabrw_get_total_services' ) ) {
    function ovabrw_get_total_services( $product_id = false, $services = [], $numberof_adults = 0, $numberof_children = 0, $numberof_babies = 0, $service_guests = [] ) {
        // init
        $total_services = 0;

        if ( ovabrw_array_exists( $services ) ) {
            // Get service ids
            $serv_ids = ovabrw_get_post_meta( $product_id, 'service_id' );

            // Get adult prices
            $adult_prices = ovabrw_get_post_meta( $product_id, 'service_adult_price' );

            // Get child prices
            $child_prices = ovabrw_get_post_meta( $product_id, 'service_children_price' );

            // Get baby prices
            $baby_prices = ovabrw_get_post_meta( $product_id, 'service_baby_price' );

            // Get durations
            $durations = ovabrw_get_post_meta( $product_id, 'service_duration_type' );

            // Loop
            foreach ( $services as $opt_id ) {
                if ( $opt_id && ovabrw_array_exists( $serv_ids ) ) {
                    foreach ( $serv_ids as $opt_key => $opt_ids ) {
                        // Search index
                        $key = array_search( $opt_id, $opt_ids );
                        if ( !is_bool( $key ) ) {
                            // Adult price
                            $adult_price = isset( $adult_prices[$opt_key][$key] ) ? (float)$adult_prices[$opt_key][$key] : 0;

                            // Child price
                            $child_price = isset( $child_prices[$opt_key][$key] ) ? (float)$child_prices[$opt_key][$key] : 0;

                            // Baby price
                            $baby_price = isset( $baby_prices[$opt_key][$key] ) ? (float)$baby_prices[$opt_key][$key] : 0;

                            // Duration type
                            $duration = isset( $durations[$opt_key][$key] ) ? $durations[$opt_key][$key] : '';

                            // Get service guests
                            $serv_guests = ovabrw_get_meta_data( $opt_id, $service_guests );
                            if ( ovabrw_array_exists( $serv_guests ) ) {
                                // Get number of adults
                                $number_adult = (int)ovabrw_get_meta_data( 'adult', $serv_guests );

                                // Get number of children
                                $number_child = (int)ovabrw_get_meta_data( 'child', $serv_guests );

                                // Get number of baby
                                $number_baby = (int)ovabrw_get_meta_data( 'baby', $serv_guests );

                                // Update total services
                                if ( 'person' === $duration ) {
                                    $total_services += floatval( $adult_price*$number_adult ) + floatval( $child_price*$number_child ) + floatval( $baby_price*$number_baby );
                                } else {
                                    if ( $number_adult ) $total_services += (float)$adult_price;
                                    if ( $number_child ) $total_services += (float)$child_price;
                                    if ( $number_baby ) $total_services += (float)$baby_price;
                                }
                            } else {
                                if ( 'person' === $duration ) {
                                    $total_services += floatval( $adult_price*$numberof_adults ) + floatval( $child_price*$numberof_children ) + floatval( $baby_price*$numberof_babies );
                                } else {
                                    $total_services += (float)$adult_price + (float)$child_price + (float)$baby_price;
                                }
                            }
                        }
                    }
                }
            }
        }

        return apply_filters( OVABRW_PREFIX.'get_total_services', $total_services, $product_id, $services, $numberof_adults, $numberof_children, $numberof_babies, $service_guests );
    }
}

/**
 * Get Price in Schedule
 */
if ( !function_exists( 'ovabrw_get_price_from_schedule' ) ) {
    function ovabrw_get_price_from_schedule( $product_id = false, $weekday = '', $time_from = '' ) {
        if ( !$product_id || !$weekday || !$time_from ) return false;

        // Price guests
        $price_guests = [
            'adults_price'      => 0,
            'childrens_price'   => 0,
            'babies_price'      => 0,
            'type_price'        => 'person'
        ];

        $ovabrw_schedule_time           = get_post_meta( $product_id, 'ovabrw_schedule_time', true );
        $ovabrw_schedule_adult_price    = get_post_meta( $product_id, 'ovabrw_schedule_adult_price', true );
        $ovabrw_schedule_child_price    = get_post_meta( $product_id, 'ovabrw_schedule_children_price', true );
        $ovabrw_schedule_baby_price     = get_post_meta( $product_id, 'ovabrw_schedule_baby_price', true );
        $ovabrw_schedule_type           = get_post_meta( $product_id, 'ovabrw_schedule_type', true );

        if ( ovabrw_get_meta_data( $weekday, $ovabrw_schedule_time ) ) {
            $schedule_time          = $ovabrw_schedule_time[$weekday];
            $schedule_adult_price   = ovabrw_get_meta_data( $weekday, $ovabrw_schedule_adult_price, [] );
            $schedule_child_price   = ovabrw_get_meta_data( $weekday, $ovabrw_schedule_child_price, [] );
            $schedule_baby_price    = ovabrw_get_meta_data( $weekday, $ovabrw_schedule_baby_price, [] );
            $schedule_type          = ovabrw_get_meta_data( $weekday, $ovabrw_schedule_type, [] );

            if ( ovabrw_array_exists( $schedule_time ) ) {
                foreach ( $schedule_time as $k => $time ) {
                    if ( $time === $time_from ) {
                        $price_guests['adults_price']       = (float)ovabrw_get_meta_data( $k, $schedule_adult_price );
                        $price_guests['childrens_price']    = (float)ovabrw_get_meta_data( $k, $schedule_child_price );
                        $price_guests['babies_price']       = (float)ovabrw_get_meta_data( $k, $schedule_baby_price );
                        $price_guests['type_price']         = ovabrw_get_meta_data( $k, $schedule_type, 'person' );

                        break;
                    }
                }
            }
        }

        return apply_filters( OVABRW_PREFIX.'get_price_from_schedule', $price_guests, $product_id, $weekday, $time_from );
    }
}

/**
 * Get price custom checkout fields
 */
if ( !function_exists( 'ovabrw_get_price_cckf' ) ) {
    function ovabrw_get_price_cckf( $product_id, $cckf_data = [], $cckf_qty = [] ) {
        if ( !$product_id || !ovabrw_array_exists( $cckf_data ) ) return 0;

        // init
        $price = 0;

        // Get custom checkout fields
        $cckf = ovabrw_get_list_field_checkout( $product_id );

        // Loop
        foreach ( $cckf_data as $name => $val ) {
            // Get fields
            $fields = ovabrw_get_meta_data( $name, $cckf );
            if ( !ovabrw_array_exists( $fields ) ) continue;

            // Get type
            $type = ovabrw_get_meta_data( 'type', $fields );
            if ( !$type || !in_array( $type, [ 'radio', 'select', 'checkbox' ] ) ) continue;

            // Type: Radio
            if ( 'radio' === $type ) {
                // Get option values
                $opt_values = ovabrw_get_meta_data( 'ova_radio_values', $fields );
                if ( !ovabrw_array_exists( $opt_values ) ) continue;

                // Get option prices
                $opt_prices = ovabrw_get_meta_data( 'ova_radio_prices', $fields );
                if ( !ovabrw_array_exists( $opt_prices ) ) continue;

                // Get option qty
                $opt_qty = (int)ovabrw_get_meta_data( $name, $cckf_qty, 1 );

                // Loop
                foreach ( $opt_values as $k => $v ) {
                    if ( $v === $val ) {
                        $price += (float)ovabrw_get_meta_data( $k, $opt_prices ) * $opt_qty;

                        // Break out of the loop
                        break;
                    }
                } // END loop
            } elseif ( 'select' === $type ) {
                // Get option keys
                $opt_keys = ovabrw_get_meta_data( 'ova_options_key', $fields );
                if ( !ovabrw_array_exists( $opt_keys ) ) continue;

                // Get option prices
                $opt_prices = ovabrw_get_meta_data( 'ova_options_price', $fields );
                if ( !ovabrw_array_exists( $opt_prices ) ) continue;

                // Get option qty
                $opt_qty = (int)ovabrw_get_meta_data( $name, $cckf_qty, 1 );

                // Loop
                foreach ( $opt_keys as $k => $v ) {
                    if ( $val === $v ) {
                        $price += (float)ovabrw_get_meta_data( $k, $opt_prices ) * $opt_qty;

                        // Break out of the loop
                        break;
                    }
                } // END loop
            } elseif ( 'checkbox' === $type ) {
                // Option values
                if ( !ovabrw_array_exists( $val ) ) continue;

                // Get option keys
                $opt_keys = ovabrw_get_meta_data( 'ova_checkbox_key', $fields );
                if ( !ovabrw_array_exists( $opt_keys ) ) continue;

                // Get option prices
                $opt_prices = ovabrw_get_meta_data( 'ova_checkbox_price', $fields );
                if ( !ovabrw_array_exists( $opt_prices ) ) continue;

                // Get option qtys
                $opt_qtys = ovabrw_get_meta_data( $name, $cckf_qty, [] );

                // Loop
                foreach ( $val as $opt_id ) {
                    // Search option index
                    $opt_index = array_search( $opt_id, $opt_keys );
                    if ( !is_bool( $opt_index ) ) {
                        // Get option qty
                        $opt_qty = (int)ovabrw_get_meta_data( $opt_id, $opt_qtys, 1 );

                        // Update price
                        $price += (float)ovabrw_get_meta_data( $opt_index, $opt_prices ) * $opt_qty;
                    }
                } // END loop
            } // END if
        } // END loop

        return apply_filters( OVABRW_PREFIX.'get_price_cckf', (float)$price, $product_id, $cckf_data, $cckf_qty );
    }
}