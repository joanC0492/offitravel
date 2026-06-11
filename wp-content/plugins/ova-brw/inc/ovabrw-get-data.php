<?php if ( !defined( 'ABSPATH' ) ) exit();

/**
 * Show check-out date
 */
if ( !function_exists( 'ovabrw_show_checkout_date' ) ) {
    function ovabrw_show_checkout_date( $product_id = false ) {
        if ( $product_id ) {
            $show_checkout = ovabrw_get_post_meta( $product_id, 'manage_checkout_field', 'global' );
            if ( 'global' === $show_checkout ) {
                $show_checkout = 'yes' === ovabrw_get_option_setting( 'booking_form_show_checkout', 'yes' ) ? true : false;
            } elseif ( 'show' === $show_checkout ) {
                $show_checkout = true;
            } else {
                $show_checkout = false;
            }
        } else {
            $show_checkout = 'yes' === ovabrw_get_option_setting( 'booking_form_show_checkout', 'yes' ) ? true : false;
        }

        return apply_filters( OVABRW_PREFIX.'show_checkout_date', $show_checkout, $product_id );
    }
}

/**
 * Show child
 */
if ( !function_exists( 'ovabrw_show_children' ) ) {
    function ovabrw_show_children( $product_id = false ) {
        if ( $product_id ) {
            $show_children = ovabrw_get_post_meta( $product_id, 'show_children', 'global' );
            if ( 'global' === $show_children ) {
                $show_children = 'yes' === ovabrw_get_option_setting( 'booking_form_show_children', 'yes' ) ? true : false;
            } elseif ( 'yes' === $show_children ) {
                $show_children = true;
            } else {
                $show_children = false;
            }
        } else {
            $show_children = 'yes' === ovabrw_get_option_setting( 'booking_form_show_children', 'yes' ) ? true : false;
        }

        return apply_filters( OVABRW_PREFIX.'show_children', $show_children, $product_id );
    }
}

/**
 * Show baby
 */
if ( !function_exists( 'ovabrw_show_babies' ) ) {
    function ovabrw_show_babies( $product_id = false ) {
        if ( $product_id ) {
            $show_babies = ovabrw_get_post_meta( $product_id, 'show_babies', 'global' );
            if ( 'global' === $show_babies ) {
                $show_babies = 'yes' === ovabrw_get_option_setting( 'booking_form_show_baby', 'yes' ) ? true : false;
            } elseif ( 'yes' === $show_babies ) {
                $show_babies = true;
            } else {
                $show_babies = false;
            }
        } else {
            $show_babies = 'yes' === ovabrw_get_option_setting( 'booking_form_show_baby', 'yes' ) ? true : false;
        }

        return apply_filters( OVABRW_PREFIX.'show_babies', $show_babies, $product_id );
    }
}

/**
 * Get tour product ids
 */
if ( !function_exists( 'ovabrw_get_tour_product_ids' ) ) {
    function ovabrw_get_tour_product_ids( $args = [] ) {
        // Base query
        $base_query = [
            'post_type'         => 'product',
            'posts_per_page'    => '-1',
            'post_status'       => 'publish',
            'fields'            => 'ids',
            'tax_query'         => [
                [
                    'taxonomy' => 'product_type',
                    'field'    => 'slug',
                    'terms'    => OVABRW_RENTAL
                ]
            ]
        ];

        // Get product IDs
        $product_ids = get_posts( $base_query );
        if ( !ovabrw_array_exists( $product_ids ) ) $product_ids = [];

        return apply_filters( OVABRW_PREFIX.'get_tour_product_ids', $product_ids, $args );
    }
}

/**
 * Push disabled dates
 */
if ( !function_exists( 'ovabrw_push_disabled_dates' ) ) {
    function ovabrw_push_disabled_dates( $checkin_date = '', $checkout_date = '', $product_id = false ) {
        // init disabled dates
        $disabled_dates = [];

        // Get date format
        $date_format = ovabrw_get_date_format();

        // Get start date
        $start_date = gmdate( $date_format, $checkin_date );

        // Get end date
        $end_date = gmdate( $date_format, $checkout_date );
        
        // Get between dates
        $dates_between = total_between_2_days( $start_date, $end_date );
        if ( 0 == $dates_between ) {
            if ( !in_array( $start_date, $disabled_dates ) ) {
                array_push( $disabled_dates, $start_date );
            }
        } else {
            $dates_between = ovabrw_createDatefull( strtotime( $start_date ), strtotime( $end_date ), $date_format );
            if ( ovabrw_array_exists( $dates_between ) ) {
                foreach ( $dates_between as $date ) {
                    if ( !in_array( $date, $disabled_dates ) ) {
                        array_push( $disabled_dates, $date );
                    }
                }
            }
        }
        
        return apply_filters( OVABRW_PREFIX.'push_disabled_dates', $disabled_dates, $checkin_date, $checkout_date, $product_id );
    }
}

/**
 * Get events calendar
 */
if ( !function_exists( 'ovabrw_get_order_rent_time' ) ) {
    function ovabrw_get_order_rent_time( $product_id = false, $order_status = [ 'wc-completed' ] ) {
        global $wpdb;

        // init
        $booked_dates = $disabled_dates = $guest_booked = [];

        // Get product quantity
        $quantity = absint( ovabrw_get_post_meta( $product_id, 'stock_quantity' ) );

        // Get max number of adults
        $max_adults = absint( ovabrw_get_post_meta( $product_id, 'adults_max' ) );

        // Get max number of children
        $max_children = absint( ovabrw_get_post_meta( $product_id, 'childrens_max' ) );

        // Get max number of babies
        $max_babies = absint( ovabrw_get_post_meta( $product_id, 'babies_max' ) );

        // Quatity by guests
        $max_guests = ( $max_adults + $max_children + $max_babies ) * $quantity;

        // Get duration
        $duration = ovabrw_get_post_meta( $product_id, 'duration_checkbox' );
        if ( !$duration ) {
            // Get date format
            $date_format = ovabrw_get_date_format();

            // Current time
            $current_time = strtotime( gmdate( $date_format, current_time( 'timestamp' ) ) );

            // Get product ids when use WPML
            $wpml_product_ids = ovabrw_get_wpml_product_ids( $product_id );

            // Get order ids
            $orders_ids = ovabrw_get_orders_by_product_id( $product_id, $order_status );
            foreach ( $orders_ids as $key => $order_id ) {
                // Get order
                $order = wc_get_order( $order_id );

                // Get order items
                $order_items = $order->get_items('line_item');
               
                // For Meta Data
                foreach ( $order_items as $item_id => $item ) {
                    $push_date_unavailable  = [];

                    if ( in_array( $item->get_product_id(), $wpml_product_ids) ) {
                        if ( ovabrw_qty_by_guests( $product_id ) ) {
                            // Get check-in date
                            $checkin_date = strtotime( $item->get_meta( 'ovabrw_pickup_date' ) );
                            if ( !$checkin_date ) continue;

                            // Get check-out date
                            $checkout_date = strtotime( $item->get_meta( 'ovabrw_pickoff_date' ) );
                            if ( !$checkout_date || $checkout_date < $current_time ) continue;

                            // Get number of adults
                            $item_adults = absint( $item->get_meta( 'ovabrw_adults' ) );

                            // Get number of children
                            $item_children = absint( $item->get_meta( 'ovabrw_childrens' ) );

                            // Get number of babies
                            $item_babies = absint( $item->get_meta( 'ovabrw_babies' ) );

                            // Get quantity booked
                            $item_qty = absint( $item->get_meta( 'ovabrw_quantity' ) );
                            if ( !$item_qty ) $item_qty = 1;

                            // Guest booked
                            $qty_booked = ( $item_adults + $item_children + $item_babies ) * $item_qty;

                            // Get unavailable dates
                            $push_date_unavailable = ovabrw_push_date_unavailable( $checkin_date, $checkout_date );
                            if ( ovabrw_array_exists( $push_date_unavailable ) ) {
                                foreach ( $push_date_unavailable as $date ) {
                                    if ( isset( $guest_booked[$date] ) ) {
                                        $guest_booked[$date] += $qty_booked;
                                    } else {
                                        $guest_booked[$date] = $qty_booked;
                                    }
                                }
                            }
                        } else {
                            // Get check-in date
                            $checkin_date = strtotime( $item->get_meta( 'ovabrw_pickup_date' ) );
                            if ( !$checkin_date ) continue;

                            // Get check-out date
                            $checkout_date = strtotime( $item->get_meta( 'ovabrw_pickoff_date' ) );
                            if ( !$checkout_date || $checkout_date < current_time( 'timestamp' ) ) {
                                continue;
                            }

                            // Get booked quantity
                            $booked_quantity = absint( $item->get_meta( 'ovabrw_quantity' ) );
                            if ( !$booked_quantity ) $booked_quantity = 1;

                            // Get unavailable dates
                            $push_date_unavailable = ovabrw_push_date_unavailable( $checkin_date, $checkout_date );

                            if ( ovabrw_array_exists( $push_date_unavailable ) ) {
                                for ( $i = 0; $i < $booked_quantity ; $i++ ) { 
                                    $disabled_dates = array_merge_recursive( $disabled_dates, $push_date_unavailable );
                                }
                            } // END if
                        }
                    }
                }
            }
        }

        // Check Unavaiable Time in Product
        $untime_startdate    = get_post_meta( $product_id, 'ovabrw_untime_startdate', true );
        $untime_enddate      = get_post_meta( $product_id, 'ovabrw_untime_enddate', true );

        if ( ! ( function_exists( 'offitravel_ovabrw_whitelist_enabled' ) && offitravel_ovabrw_whitelist_enabled( $product_id ) ) ) {
            if ( ovabrw_array_exists( $untime_startdate ) ) {
                foreach ( $untime_startdate as $i => $start_date ) {
                    // Start date
                    $start_date = strtotime( $start_date );
                    if ( !$start_date ) continue;

                    // End date
                    $end_date = strtotime( ovabrw_get_meta_data( $i, $untime_enddate ) );
                    if ( !$end_date ) continue;

                    // Get unavailable dates
                    $unavailable_dates = ovabrw_push_date_unavailable( $start_date, $end_date );
                    if ( ovabrw_array_exists( $unavailable_dates ) ) {
                        for( $i = 0; $i < $quantity; $i++ ) {
                            $disabled_dates = array_merge_recursive( $disabled_dates, $unavailable_dates );
                        }
                    }
                }
            }
        }

        // Get events
        if ( ovabrw_qty_by_guests( $product_id ) ) {
            if ( ovabrw_array_exists( $disabled_dates ) ) {
                $disabled_dates = array_unique( $disabled_dates );
                foreach ( $disabled_dates as $date ) {
                    array_push( $booked_dates, [
                        'start'             => $date,
                        'display'           => 'background',
                        'backgroundColor'   => apply_filters( OVABRW_PREFIX.'background_color_event', '#FF1A1A' )
                    ]);
                }
            }

            // Guest booked
            if ( ovabrw_array_exists( $guest_booked ) ) {
                foreach ( $guest_booked as $date => $qty_booked ) {
                    if ( $qty_booked >= $max_guests ) {
                        array_push( $booked_dates, [
                            'start'             => $date,
                            'display'           => 'background',
                            'backgroundColor'   => apply_filters( OVABRW_PREFIX.'background_color_event', '#FF1A1A' )
                        ]);
                    } else {
                        array_push( $booked_dates, [
                            'title'     => $qty_booked . esc_html__( '/', 'ova-brw' ) . $max_guests,
                            'start'     => $date,
                            'color'     => apply_filters( OVABRW_PREFIX.'color_event', '#FF1A1A' ),
                            'textColor' => apply_filters( OVABRW_PREFIX.'text_color_event', '#FFFFFF' )
                        ]);
                    }
                }
            }
        } else {
            // Unavailable Date for booking
            $data_unavailable = array_count_values( $disabled_dates );
            if ( ovabrw_array_exists( $data_unavailable ) ) {
                foreach( $data_unavailable as $date => $qty ) {
                    array_push( $booked_dates, [
                        'title'     => $qty . esc_html__( '/', 'ova-brw' ) . $quantity,
                        'start'     => $date,
                        'color'     => apply_filters( OVABRW_PREFIX.'color_event', '#FF1A1A' ),
                        'textColor' => apply_filters( OVABRW_PREFIX.'text_color_event', '#FFFFFF' )
                    ]);

                    if ( $qty >= $quantity ) {
                        array_push( $booked_dates, [
                            'start'             => $date,
                            'display'           => 'background',
                            'backgroundColor'   => apply_filters( OVABRW_PREFIX.'background_color_event', '#FF1A1A' )
                        ]);
                    }
                }
            }
        }

        return apply_filters( OVABRW_PREFIX.'get_order_rent_time', $booked_dates, $product_id, $order_status );
    }
}

/**
 * Get unavailable between dates
 */
if ( !function_exists( 'ovabrw_push_date_unavailable' ) ) {
    function ovabrw_push_date_unavailable( $checkin_date, $checkout_date ) {
        if ( !$checkin_date || !$checkout_date ) return [];

        // init
        $dates_available = [];

        // Date format
        $date_format = 'Y-m-d';

        // Start date
        $start_date = gmdate( $date_format, $checkin_date );

        // End date
        $end_date = gmdate( $date_format, $checkout_date );

        // Get number between days
        $numberof_between_days = total_between_2_days( $start_date, $end_date );
        if ( 0 == $numberof_between_days ) { // In a day
            array_push( $dates_available, $start_date );
        } else if ( 1 == $numberof_between_days ) { // 2 day beside
            array_push( $dates_available, $start_date );
            array_push( $dates_available, $checkout_date );
        } else { // from 3 days 
            array_push( $dates_available, $start_date ); 

            // Get between dates
            $date_between = ovabrw_createDatefull( strtotime( $start_date ), strtotime( $end_date ), $date_format );
            // Remove first and last array
            array_shift( $date_between ); 
            array_pop( $date_between );

            foreach ( $date_between as $key => $value ) {
                array_push( $dates_available, $value ); 
            }

            array_push( $dates_available, $end_date );
        }
        
        return apply_filters( OVABRW_PREFIX.'push_date_unavailable', $dates_available, $start_date, $end_date );
    }
}

/**
 * Get order by product id
 */
if ( !function_exists( 'ovabrw_get_orders_by_product_id' ) ) {
    function ovabrw_get_orders_by_product_id( $product_id = false, $order_status = [ 'wc-completed' ] ) {
        global $wpdb;

        // init
        $order_ids = [];

        // Get array product ids when use WPML
        $product_ids = ovabrw_get_wpml_product_ids( $product_id );

        if ( ovabrw_wc_custom_orders_table_enabled() ) {
            $order_ids = $wpdb->get_col( $wpdb->prepare("
                SELECT DISTINCT o.id
                FROM {$wpdb->prefix}wc_orders AS o
                LEFT JOIN {$wpdb->prefix}woocommerce_order_items AS oi
                    ON o.id = oi.order_id
                    AND oi.order_item_type = %s
                LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS oim
                    ON oi.order_item_id = oim.order_item_id
                LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS oim2
                    ON oi.order_item_id = oim2.order_item_id
                WHERE o.type = %s
                    AND oim.meta_key = %s
                    AND oim.meta_value IN (%s)
                    AND oim2.meta_key = %s
                    AND oim2.meta_value >= %d
                    AND o.status IN ('". implode( "','", array_map( 'esc_sql', $order_status ) ) . "')",
                [
                    'line_item',
                    'shop_order',
                    '_product_id',
                    implode( ',', $product_ids ),
                    'ovabrw_dropoff_date_strtotime',
                    current_time( 'timestamp' )
                ]
            ));
        } else {
            $order_ids = $wpdb->get_col( $wpdb->prepare("
                SELECT DISTINCT oitems.order_id
                FROM {$wpdb->prefix}woocommerce_order_items AS oitems
                LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS oitem_meta
                    ON oitems.order_item_id = oitem_meta.order_item_id
                LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS oitem_meta2
                    ON oitems.order_item_id = oitem_meta2.order_item_id
                LEFT JOIN {$wpdb->posts} AS p
                    ON oitems.order_id = p.ID
                WHERE oitems.order_item_type = %s
                    AND p.post_type = %s
                    AND oitem_meta.meta_key = %s
                    AND oitem_meta.meta_value IN (%s)
                    AND oitem_meta2.meta_key = %s
                    AND oitem_meta2.meta_value >= %d
                    AND p.post_status IN ('". implode( "','", array_map( 'esc_sql', $order_status ) ) . "')",
                [
                    'line_item',
                    'shop_order',
                    '_product_id',
                    implode( ',', $product_ids ),
                    'ovabrw_pickoff_date_strtotime',
                    current_time( 'timestamp' )
                ]
            ));
        }
        
        return apply_filters( OVABRW_PREFIX.'get_orders_by_product_id', $order_ids, $product_id, $order_status );
    }
}

/**
 * Parse ovabrw_pickup_date / start_date: full date string, month-only (01–12), or comma-separated months.
 *
 * @param string|array $raw Raw GET/POST value (array from ovabrw_pickup_date[]).
 * @return array|null { type: 'timestamp'|'month'|'months', ... } or null if empty/invalid.
 */
if ( ! function_exists( 'ovabrw_parse_pickup_date_for_search' ) ) {
	function ovabrw_parse_pickup_date_for_search( $raw ) {
		if ( is_array( $raw ) ) {
			$raw = implode( ',', array_map( 'trim', array_filter( $raw ) ) );
		}
		$raw = is_string( $raw ) ? trim( $raw ) : '';
		if ( '' === $raw ) {
			return null;
		}
		$y = (int) current_time( 'Y' );
		// Several months: "05,06" or "05, 12" (OR semantics in search: show if available in any month).
		if ( strpos( $raw, ',' ) !== false ) {
			$parts = array_map( 'trim', explode( ',', $raw ) );
			$months = [];
			foreach ( $parts as $p ) {
				if ( preg_match( '/^(0?[1-9]|1[0-2])$/', $p ) ) {
					$months[] = (int) $p;
				}
			}
			$months = array_values( array_unique( array_filter( $months ) ) );
			if ( empty( $months ) ) {
				return null;
			}
			sort( $months );
			if ( 1 === count( $months ) ) {
				return [
					'type'  => 'month',
					'year'  => $y,
					'month' => (int) $months[0],
				];
			}
			return [
				'type'   => 'months',
				'year'   => $y,
				'months' => $months,
			];
		}
		// Month-only: 1–12 or 01–12 (not full dates). Year = current calendar year in site timezone.
		if ( preg_match( '/^(0?[1-9]|1[0-2])$/', $raw ) ) {
			$month = (int) $raw;
			return [
				'type'  => 'month',
				'year'  => $y,
				'month' => $month,
			];
		}
		$ts = strtotime( $raw );
		if ( $ts ) {
			return [
				'type' => 'timestamp',
				'ts'   => $ts,
			];
		}
		return null;
	}
}

/**
 * Product IDs unavailable for every day in ALL of the given months (intersection of per-month full exclusions).
 * Used when filtering by multiple months (OR availability across months).
 */
if ( ! function_exists( 'ovabrw_get_exclude_ids_for_months_intersection' ) ) {
	function ovabrw_get_exclude_ids_for_months_intersection( $year, array $months ) {
		$months = array_unique( array_map( 'intval', $months ) );
		$months = array_filter(
			$months,
			function ( $m ) {
				return $m >= 1 && $m <= 12;
			}
		);
		sort( $months );
		if ( empty( $months ) ) {
			return [];
		}
		$year = (int) $year;
		$exclude = ovabrw_get_exclude_ids_for_month_range( $year, $months[0] );
		$n       = count( $months );
		for ( $i = 1; $i < $n; $i++ ) {
			$exclude = array_intersect( $exclude, ovabrw_get_exclude_ids_for_month_range( $year, $months[ $i ] ) );
		}
		return apply_filters( OVABRW_PREFIX . 'get_exclude_ids_for_months_intersection', array_values( array_map( 'intval', $exclude ) ), $year, $months );
	}
}

/**
 * Single-day availability check for ovabrw_search_vehicle (matches legacy loop).
 */
if ( ! function_exists( 'ovabrw_product_passes_search_vehicle_day' ) ) {
	function ovabrw_product_passes_search_vehicle_day( $product_id, $pickup_ts ) {
		if ( ! $pickup_ts ) {
			$validate_manage_store = ova_validate_manage_store( $product_id, false, false, false, 'search' );
			return $validate_manage_store && ! empty( $validate_manage_store['status'] );
		}
		$preparation_time = (int) ovabrw_get_post_meta( $product_id, 'preparation_time' );
		$pickup_date      = $pickup_ts;
		if ( $preparation_time && $pickup_date ) {
			$today = strtotime( date( 'Y-m-d', current_time( 'timestamp' ) ) );
			if ( $pickup_date < ( $today + $preparation_time * 86400 - 86400 ) ) {
				return false;
			}
			$pickup_date = $pickup_date + $preparation_time * 86400 - 86400;
		}
		$validate_manage_store = ova_validate_manage_store( $product_id, $pickup_date, $pickup_date, false, 'search' );
		return $validate_manage_store && ! empty( $validate_manage_store['status'] );
	}
}

/**
 * Whether a tour is excluded for one pickup timestamp (same rules as legacy ovabrw_get_exclude_ids body).
 */
if ( ! function_exists( 'ovabrw_tour_excluded_on_pickup_timestamp' ) ) {
	function ovabrw_tour_excluded_on_pickup_timestamp( $product_id, $pickup_date ) {
		$duration = ovabrw_get_post_meta( $product_id, 'duration_checkbox' );

		$preparation_time = (int) ovabrw_get_post_meta( $product_id, 'preparation_time' );
		if ( $preparation_time && $pickup_date ) {
			$today = strtotime( gmdate( 'Y-m-d', current_time( 'timestamp' ) ) );
			if ( $pickup_date < ( $today + $preparation_time * 86400 ) ) {
				return true;
			}
		}

		// Offitravel: lista blanca de fechas de salida permitidas (mu-plugin offitravel-ovabrw-whitelist-availability).
		if ( function_exists( 'offitravel_ovabrw_whitelist_enabled' ) && offitravel_ovabrw_whitelist_enabled( $product_id ) ) {
			if ( ! function_exists( 'offitravel_ovabrw_pickup_in_whitelist' ) || ! offitravel_ovabrw_pickup_in_whitelist( $product_id, $pickup_date ) ) {
				return true;
			}
		}

		// Fixed departure windows (e.g. "From 06-05-2026 to 23-05-2026"): must apply for ALL product
		// types. The duration branch below used to return early and skipped ovabrw_check_fixed_time.
		if ( $pickup_date ) {
			$fixed_in  = ovabrw_get_post_meta( $product_id, 'fixed_time_check_in' );
			$fixed_out = ovabrw_get_post_meta( $product_id, 'fixed_time_check_out' );
			if ( ovabrw_array_exists( $fixed_in ) && ovabrw_array_exists( $fixed_out ) ) {
				if ( ! ovabrw_check_fixed_time( $product_id, $pickup_date ) ) {
					return true;
				}
			}
		}

		if ( $duration ) {
			// Apply UT with numeric pickup/dropoff (same as non-duration). Duration-only path
			// relied on ova_validate_manage_store(); if checkout string parsing fails, UT was skipped.
			$numberof_days_d = (int) ovabrw_get_post_meta( $product_id, 'number_days' );
			$dropoff_d         = '';
			if ( $pickup_date ) {
				$dropoff_d = $pickup_date + $numberof_days_d * 86400;
			}
			if ( $pickup_date && $dropoff_d && ovabrw_check_unavailable( $product_id, $pickup_date, $dropoff_d ) ) {
				return true;
			}
			$duration_time = ovabrw_get_duration_time( $product_id, $pickup_date );
			return empty( $duration_time );
		}

		$numberof_days = (int) ovabrw_get_post_meta( $product_id, 'number_days' );
		$dropoff_date  = '';
		if ( $pickup_date ) {
			$dropoff_date = $pickup_date + $numberof_days * 86400;
		}

		$store_quantity = ovabrw_quantity_available_in_order( $product_id, $pickup_date, $dropoff_date );
		$cart_quantity  = ovabrw_quantity_available_in_cart( $product_id, 'cart', $pickup_date, $dropoff_date );
		$data_quantity  = ovabrw_get_quantity_available( $product_id, $store_quantity, $cart_quantity, 1, false, 'cart' );
		$unavailable    = ovabrw_check_unavailable( $product_id, $pickup_date, $dropoff_date );

		// UT / disabled weekdays must exclude even when get_quantity_available() is false (e.g. stock 0),
		// otherwise the block below never runs and the tour incorrectly stays "available" in month search.
		if ( $unavailable ) {
			return true;
		}

		$excluded_from_qty = false;
		if ( $data_quantity ) {
			$qty_available = $data_quantity['quantity_available'];
			if ( $qty_available <= 0 || is_null( $qty_available ) ) {
				$excluded_from_qty = true;
			}
		}

		$in_fixed_time = ovabrw_check_fixed_time( $product_id, $pickup_date );

		if ( $excluded_from_qty ) {
			return true;
		}
		if ( ! $in_fixed_time ) {
			return true;
		}
		return false;
	}
}

/**
 * Exclude product IDs when the tour is unavailable on every day of the given month.
 */
if ( ! function_exists( 'ovabrw_get_exclude_ids_for_month_range' ) ) {
	function ovabrw_get_exclude_ids_for_month_range( $year, $month ) {
		$exclude_ids = [];
		$products    = ovabrw_get_tour_product_ids();
		$month       = max( 1, min( 12, (int) $month ) );
		$year        = (int) $year;
		$days_in_month = (int) date_i18n( 't', strtotime( sprintf( '%04d-%02d-01', $year, $month ) ) );

		if ( ovabrw_array_exists( $products ) ) {
			foreach ( $products as $product_id ) {
				$any_available = false;
				for ( $d = 1; $d <= $days_in_month; $d++ ) {
					$ts = strtotime( sprintf( '%04d-%02d-%02d 12:00:00', $year, $month, $d ) );
					if ( ! $ts ) {
						continue;
					}
					if ( ! ovabrw_tour_excluded_on_pickup_timestamp( $product_id, $ts ) ) {
						$any_available = true;
						break;
					}
				}
				if ( ! $any_available ) {
					$exclude_ids[] = $product_id;
				}
			}
		}

		return apply_filters( OVABRW_PREFIX . 'get_exclude_ids_for_month_range', $exclude_ids, $year, $month );
	}
}

/**
 * Search vehicle
 */
if ( !function_exists( 'ovabrw_search_vehicle' ) ) {
    function ovabrw_search_vehicle( $data = [] ) {
        // Get destination
        $destination = sanitize_text_field( ovabrw_get_meta_data( 'ovabrw_destination', $data, 'all' ) );

        // Product name
        $product_name = sanitize_text_field( ovabrw_get_meta_data( 'ovabrw_name_product', $data ) );
        
        // Pick-up date (full date or month-only 01–12)
        $pickup_parsed = ovabrw_parse_pickup_date_for_search( ovabrw_get_meta_data( 'ovabrw_pickup_date', $data ) );

        // Number of adults
        $adults = sanitize_text_field( ovabrw_get_meta_data( 'ovabrw_adults', $data ) );

        // Number of children
        $children = sanitize_text_field( ovabrw_get_meta_data( 'ovabrw_childrens', $data ) );

        // Number of babies
        $babies = sanitize_text_field( ovabrw_get_meta_data( 'ovabrw_babies', $data ) );

        // Get order
        $order = sanitize_text_field( ovabrw_get_meta_data( 'order', $data, 'DESC' ) );

        // Get orderby
        $orderby = sanitize_text_field( ovabrw_get_meta_data( 'orderby', $data, 'ID' ) );

        // Attribute name
        $attribute_name = sanitize_text_field( ovabrw_get_meta_data( 'ovabrw_attribute', $data ) );

        // Attribute value
        $attribute_value = sanitize_text_field( ovabrw_get_meta_data( $attribute_name, $data ) );

        // Category
        $category = sanitize_text_field( ovabrw_get_meta_data( 'cat', $data ) );
        if ( !empty( $category ) && !is_array( $category ) ) {
            $category = explode( '|', $category );
        }

        // Product tag
        $product_tag = sanitize_text_field( ovabrw_get_meta_data( 'ovabrw_tag_product', $data ) );

        // Get list taxonomy
        $list_taxonomy = ovabrw_create_type_taxonomies();

        // Get custom taxonomy
        $slug_custom_taxonomy = sanitize_text_field( ovabrw_get_meta_data( 'ovabrw_slug_custom_taxonomy', $data ) );

        // Get taxonomies
        $ovabrw_slug_taxonomies = ovabrw_get_meta_data( 'ovabrw_slug_taxonomies', $data, [] );
        if ( !empty( $ovabrw_slug_taxonomies ) && !is_array( $ovabrw_slug_taxonomies ) ) {
            $ovabrw_slug_taxonomies = explode( '|', $ovabrw_slug_taxonomies );
        } else {
            $ovabrw_slug_taxonomies = [];
        }

        $arg_taxonomy_arr = [];
        if ( ovabrw_array_exists( $list_taxonomy ) ) {
            foreach( $list_taxonomy as $taxonomy ) {
                $taxonomy_get = isset( $data[$taxonomy['slug'].'_name'] ) ? sanitize_text_field( $data[$taxonomy['slug'].'_name'] ) : '';
                if ( $taxonomy_get != 'all' && ( $taxonomy['slug'] == $slug_custom_taxonomy || in_array( trim( $taxonomy['slug'] ), $ovabrw_slug_taxonomies ) ) ) {
                    $arg_taxonomy_arr[] = [
                        'taxonomy' => $taxonomy['slug'],
                        'field'    => 'slug',
                        'terms'    => $taxonomy_get
                    ];
                }
            }
        }

        // Get status
        $statuses = brw_list_order_status();
        $error    = [];
        $items_id = $args_cus_tax_custom = [];

        // Meta queries
        $args_meta_query_arr = $args_cus_meta_custom = [];

        // Base query
        $args_base = [
            'post_type'         => 'product',
            'posts_per_page'    => '-1',
            'post_status'       => 'publish',
            'fields'            => 'ids'
        ];

        // Product name
        if ( '' != $product_name ) {
            $args_base['s'] = $product_name;   
        }

        // Destination
        if ( 'all' != $destination ) {
            $args_meta_query_arr[] = [
                'key'     => 'ovabrw_destination',
                'value'   => $destination,
                'compare' => 'LIKE',
            ];
        }
        
        // Category
        if ( ovabrw_array_exists( $category ) ) {
            $arg_taxonomy_arr[] = [
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => $category
            ];
        }

        // Attribute
        if ( '' != $attribute_name ) {
            $arg_taxonomy_arr[] = [
                'taxonomy'  => 'pa_' . $attribute_name,
                'field'     => 'slug',
                'terms'     => [ $attribute_value ],
                'operator'  => 'IN'
            ];
        }

        // Product tag
        if ( '' != $product_tag ) {
            $arg_taxonomy_arr[] = [
                'taxonomy'  => 'product_tag',
                'field'     => 'name',
                'terms'     => $product_tag
            ];
        }

        // Taxonomy
        if ( ovabrw_array_exists( $arg_taxonomy_arr ) ) {
            $args_cus_tax_custom = [
                'tax_query' => [
                    'relation'  => 'AND',
                    $arg_taxonomy_arr
                ]
            ];
        }

        // Adults
        if ( '' != $adults ) {
            $args_meta_query_arr[] = [
                'key'     => 'ovabrw_adults_max',
                'value'   => $adults,
                'type'    => 'numeric',
                'compare' => '>=',
            ];
        }

        // Children
        if ( '' != $children ) {
            $args_meta_query_arr[] = [
                'key'     => 'ovabrw_childrens_max',
                'value'   => $children,
                'type'    => 'numeric',
                'compare' => '>=',
            ];
        }

        // Babies
        if ( '' != $babies ) {
            $args_meta_query_arr[] = [
                'key'     => 'ovabrw_babies_max',
                'value'   => $babies,
                'type'    => 'numeric',
                'compare' => '>=',
            ];
        }

        // Meta query
        if ( ovabrw_array_exists( $args_meta_query_arr ) ) {
            $args_cus_meta_custom = [
                'meta_query' => [
                    'relation'  => 'AND',
                    $args_meta_query_arr
                ]
            ];
        }

        // Merge query
        $args = array_merge_recursive( $args_base, $args_cus_tax_custom, $args_cus_meta_custom );

        // Get all products
        $items = get_posts( $args );
        if ( ovabrw_array_exists( $items ) ) {
            foreach ( $items as $id ) {
                if ( $pickup_parsed && isset( $pickup_parsed['type'] ) && 'month' === $pickup_parsed['type'] ) {
                    $y   = (int) $pickup_parsed['year'];
                    $mo  = (int) $pickup_parsed['month'];
                    $dim = (int) date_i18n( 't', strtotime( sprintf( '%04d-%02d-01', $y, $mo ) ) );
                    $matched = false;
                    for ( $dom = 1; $dom <= $dim; $dom++ ) {
                        $day_ts = strtotime( sprintf( '%04d-%02d-%02d 12:00:00', $y, $mo, $dom ) );
                        if ( ! $day_ts ) {
                            continue;
                        }
                        // Same rules as tour-list AJAX (fixed dates, stock, unavailability).
                        if ( ! ovabrw_tour_excluded_on_pickup_timestamp( $id, $day_ts ) ) {
                            $matched = true;
                            break;
                        }
                    }
                    if ( $matched ) {
                        array_push( $items_id, $id );
                    }
                    continue;
                }

                if ( $pickup_parsed && isset( $pickup_parsed['type'] ) && 'months' === $pickup_parsed['type'] ) {
                    $y       = (int) $pickup_parsed['year'];
                    $matched = false;
                    foreach ( $pickup_parsed['months'] as $mo ) {
                        $mo  = (int) $mo;
                        $dim = (int) date_i18n( 't', strtotime( sprintf( '%04d-%02d-01', $y, $mo ) ) );
                        for ( $dom = 1; $dom <= $dim; $dom++ ) {
                            $day_ts = strtotime( sprintf( '%04d-%02d-%02d 12:00:00', $y, $mo, $dom ) );
                            if ( ! $day_ts ) {
                                continue;
                            }
                            if ( ! ovabrw_tour_excluded_on_pickup_timestamp( $id, $day_ts ) ) {
                                $matched = true;
                                break 2;
                            }
                        }
                    }
                    if ( $matched ) {
                        array_push( $items_id, $id );
                    }
                    continue;
                }

                if ( $pickup_parsed && isset( $pickup_parsed['type'] ) && 'timestamp' === $pickup_parsed['type'] ) {
                    if ( ! ovabrw_tour_excluded_on_pickup_timestamp( $id, $pickup_parsed['ts'] ) ) {
                        array_push( $items_id, $id );
                    }
                } elseif ( ovabrw_product_passes_search_vehicle_day( $id, false ) ) {
                    array_push( $items_id, $id );
                }
            }
        } else {
            return $items_id;
        }
        
        // Query product
        if ( $items_id ) {
            $paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
            $search_items_page = wc_get_default_products_per_row() * wc_get_default_product_rows_per_page();

            $rental_products = new WP_Query([
                'post_type'         => 'product',
                'posts_per_page'    => $search_items_page,
                'paged'             => $paged,
                'post_status'       => 'publish',
                'post__in'          => $items_id,
                'order'             => $order,
                'orderby'           => $orderby
            ]);

            return $rental_products;
        }

        return false;
    }
}

/**
 * Get all location
 */
if ( !function_exists( 'ovabrw_get_locations' ) ) {
    function ovabrw_get_locations() {
        $locations = new WP_Query([
            'post_type'         => 'location',
            'post_status'       => 'publish',
            'posts_per_page'    => '-1'
        ]);

        return apply_filters( OVABRW_PREFIX.'get_locations', $locations );
    }
}

/**
 * Get product rental ids
 */
if ( !function_exists( 'ovabrw_get_all_id_product' ) ) {
    function ovabrw_get_all_id_product() {
        $product_ids = get_posts([
            'post_type'         => 'product',
            'posts_per_page'    => -1,
            'post_status'       => 'publish',
            'orderby'           => 'ID',
            'order'             => 'ASC',
            'fields'            => 'ids',
            'tax_query'         => [
                [
                    'taxonomy'  => 'product_type',
                    'field'     => 'slug',
                    'terms'     => OVABRW_RENTAL
                ]
            ]
        ]);

        return apply_filters( OVABRW_PREFIX.'get_all_id_product', $product_ids );
    }
}

/**
 * Get all order has pickup date larger current time
 */
if ( !function_exists( 'ovabrw_get_orders_feature' ) ) {
    function ovabrw_get_orders_feature() {
        // init
        $order_ids = [];

        // Order status
        $order_status = brw_list_order_status();

        // Global wpdb
        global $wpdb;

        if ( ovabrw_wc_custom_orders_table_enabled() ) {
            $order_ids = $wpdb->get_col("
                SELECT DISTINCT o.id
                FROM {$wpdb->prefix}wc_orders AS o
                WHERE o.status IN ( '" . implode( "','", $order_status ) . "' )
            ");
        } else {
            $order_ids = $wpdb->get_col("
                SELECT DISTINCT oitems.order_id
                FROM {$wpdb->prefix}woocommerce_order_items AS oitems
                LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS oitem_meta
                ON oitems.order_item_id = oitem_meta.order_item_id
                LEFT JOIN {$wpdb->posts} AS posts
                ON oitems.order_id = posts.ID
                WHERE posts.post_type = 'shop_order'
                AND posts.post_status IN ( '" . implode( "','", $order_status ) . "' )
            ");
        }

        return apply_filters( OVABRW_PREFIX.'get_orders_feature', $order_ids );
    }
}

/**
 * Get html Resources
 */
if ( !function_exists( 'ovabrw_get_html_resources' ) ) {
    function ovabrw_get_html_resources( $product_id = false, $resources = [], $numberof_adults = 0, $numberof_children = 0, $numberof_babies = 0, $order_id = false, $resource_guests = [] ) {
        if ( 'no' === ovabrw_get_option_setting( 'booking_form_show_extra', 'no' ) ) {
            return '';
        }

        // init
        $resource_html = '';

        // Currency
        $currency = '';
        if ( $order_id ) {
            $order = wc_get_order( $order_id );

            if ( !empty( $order ) && is_object( $order ) ) {
                $currency = $order->get_currency();
            }
        }

        if ( ovabrw_array_exists( $resources ) ) {
            // Get resource IDs
            $rs_ids = ovabrw_get_post_meta( $product_id, 'rs_id' );

            // Get resource names
            $rs_names = ovabrw_get_post_meta( $product_id, 'rs_name' );

            // Get resource adult price
            $rs_adult_price = ovabrw_get_post_meta( $product_id, 'rs_adult_price' );

            // Get resource child price
            $rs_child_price = ovabrw_get_post_meta( $product_id, 'rs_children_price' );

            // Get resource baby price
            $rs_baby_price = ovabrw_get_post_meta( $product_id, 'rs_baby_price' );

            // Get resource duration type
            $rs_duration_type = ovabrw_get_post_meta( $product_id, 'rs_duration_type' );

            // Loop
            foreach ( $resources as $rs_id => $rs_name ) {
                // init
                $rs_price = 0;

                $key = array_search( $rs_id, $rs_ids );
                if ( !is_bool( $key ) ) {
                    // Get adult price
                    $adult_price = (float)ovabrw_get_meta_data( $key, $rs_adult_price );

                    // Get child price
                    $child_price = (float)ovabrw_get_meta_data( $key, $rs_child_price );

                    // Get baby price
                    $baby_price = (float)ovabrw_get_meta_data( $key, $rs_baby_price );
                    
                    // Duration type
                    $duration_type = ovabrw_get_meta_data( $key, $rs_duration_type, 'person' );

                    // Get option guests
                    $opt_guests = ovabrw_get_meta_data( $rs_id, $resource_guests );
                    if ( ovabrw_array_exists( $opt_guests ) ) {
                        // Get number of adults
                        $number_adult = (int)ovabrw_get_meta_data( 'adult', $opt_guests );

                        // Get number of children
                        $number_child = (int)ovabrw_get_meta_data( 'child', $opt_guests );

                        // Get number of baby
                        $number_baby = (int)ovabrw_get_meta_data( 'baby', $opt_guests );

                        // Resource price
                        if ( 'person' === $duration_type ) {
                            $rs_price += floatval( $adult_price*$number_adult ) + floatval( $child_price*$number_child ) + floatval( $baby_price*$number_baby );
                        } else {
                            if ( $number_adult ) $rs_price += $adult_price;
                            if ( $number_child ) $rs_price += $child_price;
                            if ( $number_baby ) $rs_price += $baby_price;
                        }
                    } else {
                        if ( 'person' === $duration_type ) {
                            $rs_price += floatval( $adult_price*$numberof_adults ) + floatval( $child_price*$numberof_children ) + floatval( $baby_price*$numberof_babies );
                        } else {
                            $rs_price += $adult_price + $child_price + $baby_price;
                        }
                    }

                    // Convert price
                    $rs_price = ovabrw_convert_price_in_admin( $rs_price, $currency );

                    // Resource HTML
                    $resource_html .=  '<dt>' . $rs_name . esc_html__( ': ', 'ova-brw' ) . '</dt><dd>' . ovabrw_wc_price( $rs_price, ['currency' => $currency] ) . '</dd>';
                }
            } // END foreach
        } // END if

        return apply_filters( OVABRW_PREFIX.'get_html_resources', $resource_html, $product_id, $resources, $numberof_adults, $numberof_children, $numberof_babies, $order_id, $resource_guests );
    }
}

/**
 * Get html Services
 */
if ( !function_exists( 'ovabrw_get_html_services' ) ) {
    function ovabrw_get_html_services( $product_id = false, $services = [], $numberof_adults = 0, $numberof_children = 0, $numberof_babies = 0, $order_id = false, $service_guests = [] ) {
        if ( 'no' === ovabrw_get_option_setting( 'booking_form_show_extra', 'no' ) ) {
            return '';
        }

        // init
        $service_html = '';

        // Get currency
        $currency = '';
        if ( $order_id ) {
            $order = wc_get_order( $order_id );

            if ( !empty( $order ) && is_object( $order ) ) {
                $currency = $order->get_currency();
            }
        }

        if ( ovabrw_array_exists( $services ) ) {
            // Get service ids
            $service_ids = ovabrw_get_post_meta( $product_id, 'service_id' );

            // Get service names
            $service_name = ovabrw_get_post_meta( $product_id, 'service_name' );

            // Get adult price
            $service_adult_price = ovabrw_get_post_meta( $product_id, 'service_adult_price' );

            // Get child price
            $service_child_price = ovabrw_get_post_meta( $product_id, 'service_children_price' );

            // Get baby price
            $service_baby_price = ovabrw_get_post_meta( $product_id, 'service_baby_price' );

            // Get duration type
            $service_duration_type = ovabrw_get_post_meta( $product_id, 'service_duration_type' );

            // Loop
            foreach ( $services as $sv_id ) {
                $service_price = 0;

                if ( $sv_id && ovabrw_array_exists( $service_ids ) ) {
                    foreach ( $service_ids as $key_id => $service_id_arr ) {
                        $key = array_search( $sv_id, $service_id_arr );

                        if ( !is_bool( $key ) ) {
                            // Get adult price
                            $adult_price = isset( $service_adult_price[$key_id][$key] ) ? (float)$service_adult_price[$key_id][$key] : 0;

                            // Get child price
                            $child_price = isset( $service_child_price[$key_id][$key] ) ? (float)$service_child_price[$key_id][$key] : 0;

                            // Get baby price
                            $baby_price = isset( $service_baby_price[$key_id][$key] ) ? (float)$service_baby_price[$key_id][$key] : 0;

                            // Get duration type
                            $duration_type = isset( $service_duration_type[$key_id][$key] ) ? $service_duration_type[$key_id][$key] : 'person';

                            // Get service guests
                            $serv_guests = ovabrw_get_meta_data( $sv_id, $service_guests );
                            if ( ovabrw_array_exists( $serv_guests ) ) {
                                // Get number of adults
                                $number_adult = (int)ovabrw_get_meta_data( 'adult', $serv_guests );

                                // Get number of children
                                $number_child = (int)ovabrw_get_meta_data( 'child', $serv_guests );

                                // Get number of baby
                                $number_baby = (int)ovabrw_get_meta_data( 'baby', $serv_guests );

                                if ( 'person' === $duration_type ) {
                                    $service_price += floatval( $adult_price*$number_adult ) + floatval( $child_price*$number_child ) + floatval( $baby_price*$number_baby );
                                } else {
                                    if ( $number_adult ) $service_price += (float)$adult_price;
                                    if ( $number_child ) $service_price += (float)$child_price;
                                    if ( $number_baby ) $service_price += (float)$baby_price;
                                }
                            } else {
                                if ( 'person' === $duration_type ) {
                                    $service_price += floatval( $adult_price*$numberof_adults ) + floatval( $child_price*$numberof_children ) + floatval( $baby_price*$numberof_babies );
                                } else {
                                    $service_price += (float)$adult_price + (float)$child_price + (float)$baby_price;
                                }
                            }

                            // Convert price
                            $service_price = ovabrw_convert_price_in_admin( $service_price, $currency );

                            // Service HTML
                            $service_html .= '<dt>' . $service_name[$key_id][$key] . esc_html__( ': ', 'ova-brw' ) . '</dt><dd>' . ovabrw_wc_price( $service_price, ['currency' => $currency] ) . '</dd>';
                        }
                    }
                }
            }
        }

        return apply_filters( OVABRW_PREFIX.'get_html_services', $service_html, $product_id, $services, $numberof_adults, $numberof_children, $numberof_babies, $order_id, $service_guests );
    }
}

/**
 * Get html Custom checkout fields
 */
if ( !function_exists( 'ovabrw_get_html_cckf' ) ) {
    function ovabrw_get_html_cckf( $custom_ckf = [], $order_id = false, $cckf_qty = [] ) {
        // init
        $cckf_html = '';

        if ( ovabrw_array_exists( $custom_ckf ) ) {
            $currency = '';

            if ( $order_id ) {
                $order = wc_get_order( $order_id );

                if ( !empty( $order ) && is_object( $order ) ) {
                    $currency = $order->get_currency();
                }
            }

            // Get list fields
            $list_fields = ovabrw_get_option( 'booking_form', [] );

            // Loop
            foreach ( $custom_ckf as $k => $val ) {
                if ( isset( $list_fields[$k] ) && ! empty( $list_fields[$k] ) ) {
                    $type = $list_fields[$k]['type'];

                    if ( 'radio' === $type ) {
                        $val_key = array_search( $val, $list_fields[$k]['ova_radio_values'] );
                        if ( ! is_bool( $val_key ) ) {
                            $price = $list_fields[$k]['ova_radio_prices'][$val_key];
                            $price = ovabrw_convert_price_in_admin( $price, $currency );

                            // Get quantity
                            $qty = (int)ovabrw_get_meta_data( $k, $cckf_qty );
                            if ( $qty ) $price *= $qty;

                            if ( $price ) {
                                $cckf_html .= '<dt>' . $val . esc_html__( ': ', 'ova-brw' ) . '</dt><dd>' . ovabrw_wc_price( $price, ['currency' => $currency] ) . '</dd>';
                            }
                        }
                    } elseif ( 'checkbox' === $type ) {
                        if ( ovabrw_array_exists( $val ) ) {
                            // Get option quantity
                            $opt_qtys = ovabrw_get_meta_data( $k, $cckf_qty, [] );

                            foreach ( $val as $val_cb ) {
                                $val_key = array_search( $val_cb, $list_fields[$k]['ova_checkbox_key'] );

                                if ( !is_bool( $val_key ) ) {
                                    $label = $list_fields[$k]['ova_checkbox_text'][$val_key];
                                    $price = $list_fields[$k]['ova_checkbox_price'][$val_key];
                                    $price = ovabrw_convert_price_in_admin( $price, $currency );

                                    // Get quantity
                                    $qty = (int)ovabrw_get_meta_data( $val_cb, $opt_qtys );
                                    if ( $qty ) $price *= $qty;

                                    if ( $price ) {
                                        $cckf_html .= '<dt>' . $label . esc_html__( ': ', 'ova-brw' ) . '</dt><dd>' . ovabrw_wc_price( $price, ['currency' => $currency] ) . '</dd>';
                                    }
                                }
                            }
                        }
                    } elseif ( 'select' === $type ) {
                        $val_key = array_search( $val, $list_fields[$k]['ova_options_key'] );

                        if ( !is_bool( $val_key ) ) {
                            $label = $list_fields[$k]['ova_options_text'][$val_key];
                            $price = $list_fields[$k]['ova_options_price'][$val_key];
                            $price = ovabrw_convert_price_in_admin( $price, $currency );

                            // Get quantity
                            $qty = (int)ovabrw_get_meta_data( $k, $cckf_qty );
                            if ( $qty ) $price *= $qty;

                            if ( $price ) {
                                $cckf_html .= '<dt>' . $label . esc_html__( ': ', 'ova-brw' ) . '</dt><dd>' . ovabrw_wc_price( $price, ['currency' => $currency] ) . '</dd>';
                            }
                        }
                    }
                }
            }
        }

        return apply_filters( OVABRW_PREFIX.'get_html_cckf', $cckf_html, $custom_ckf, $order_id, $cckf_qty );
    }
}

/**
 * Get html Resources + Services + Custom checkout fields
 */
if ( !function_exists( 'ovabrw_get_html_extra' ) ) {
    function ovabrw_get_html_extra( $resource_html = '', $service_html = '', $cckf_html = '' ) {
        $html = '';

        if ( $cckf_html || $resource_html || $service_html ) {
            $html .= '<dl class="ovabrw_extra_item">';
            $html .= $cckf_html;
            $html .= $resource_html;
            $html .= $service_html;
            $html .= '</dl>';
        }

        return apply_filters( OVABRW_PREFIX.'get_html_extra', $html, $resource_html, $service_html, $cckf_html );
    }
}

/**
 * Get taxes when wc_tax_enabled()
 */
if ( !function_exists( 'ovabrw_get_taxes_by_price' ) ) {
    function ovabrw_get_taxes_by_price( $product, $price ) {
        if ( ! $product || ! $price ) return 0;

        // Tax amount
        $taxes = 0;

        if ( $product->is_taxable() ) {
            $tax_rates = WC_Tax::get_rates( $product->get_tax_class() );

            if ( wc_prices_include_tax() ) {
                $incl_tax = WC_Tax::calc_inclusive_tax( $price, $tax_rates );
                $taxes    = wc_round_tax_total( array_sum( $incl_tax ) );
            } else {
                $excl_tax = WC_Tax::calc_exclusive_tax( $price, $tax_rates );
                $taxes    = wc_round_tax_total( array_sum( $excl_tax ) );
            }
        }

        return apply_filters( OVABRW_PREFIX.'get_taxes_by_price', $taxes, $product, $price );
    }
}

/**
 * Get html custom checkout fields
 */
if ( !function_exists( 'ovabrw_get_html_ckf_order' ) ) {
    function ovabrw_get_html_ckf_order( $product_id ) {
        if ( !$product_id ) return;

        // Get custom checkout fields
        $cckf = ovabrw_get_list_field_checkout( $product_id );

        ob_start();
        if ( ovabrw_array_exists( $cckf ) ):
            foreach ( $cckf as $name => $fields ):
                // Get enabled
                $enabled = ovabrw_get_meta_data( 'enabled', $fields );
                if ( 'on' !== $enabled ) continue;

                // Get type
                $type = ovabrw_get_meta_data( 'type', $fields );

                // Get class
                $class = ovabrw_get_meta_data( 'class', $fields );

                // Get required
                $required = 'on' === ovabrw_get_meta_data( 'required', $fields ) ? true : false;
                if ( $required ) $class .= ' ovabrw-input-required';

                // Get label
                $label = ovabrw_get_meta_data( 'label' , $fields );

                // Get default
                $default = ovabrw_get_meta_data( 'default', $fields );

                // Get placeholder
                $placeholder = ovabrw_get_meta_data( 'placeholder', $fields );

                // Option quantity
                $option_qtys = [];
            ?>
                <div class="ovabrw-cckf ovabrw-ckf-<?php echo esc_attr( $name ); ?>">
                    <label class="<?php echo $required ? 'ovabrw-required' : ''; ?>">
                        <?php echo esc_html( $label ); ?>
                    </label>
                    <?php if ( 'checkbox' === $type ):
                        // Get option ids
                        $opt_ids = ovabrw_get_meta_data( 'ova_checkbox_key', $fields, [] );
                        if ( !ovabrw_array_exists( $opt_ids ) ) continue;

                        // Get option values
                        $opt_values = ovabrw_get_meta_data( 'ova_checkbox_text', $fields, [] );

                        // Get option qtys
                        $opt_qtys = ovabrw_get_meta_data( 'ova_checkbox_qty', $fields, [] );
                    ?>
                        <div class="ovabrw-checkbox <?php echo esc_attr( $class ); ?>">
                            <?php foreach ( $opt_ids as $k => $opt_id ):
                                // Get default
                                if ( !$default && $required ) $default = $opt_id;

                                // Get value
                                $value = ovabrw_get_meta_data( $k, $opt_values );

                                // Get option quantity
                                $qty = (int)ovabrw_get_meta_data( $k, $opt_qtys );
                            ?>
                                <div class="checkbox-item">
                                    <label>
                                        <?php ovabrw_wp_text_input([
                                            'type'      => 'checkbox',
                                            'name'      => $name.'['.$product_id.']['.$opt_id.']',
                                            'value'     => $opt_id,
                                            'checked'   => $default === $opt_id ? true : false
                                        ]);

                                        echo esc_html( $value ); ?>
                                    </label>
                                    <?php if ( $qty ): ?>
                                        <div class="checkbox-item-qty" data-option="<?php echo esc_attr( $opt_id ); ?>">
                                            <span class="checkbox-qty">1</span>
                                            <?php ovabrw_wp_text_input([
                                                'type'  => 'text',
                                                'class' => 'checkbox-input-qty',
                                                'name'  => $name.'_qty['.$product_id.']['.$opt_id.']',
                                                'value' => 1,
                                                'attrs' => [
                                                    'min' => 1,
                                                    'max' => $opt_qty
                                                ]
                                            ]); ?>
                                            <div class="ovabrw-checkbox-icon">
                                                <span class="dashicons dashicons-arrow-up"></span>
                                                <span class="dashicons dashicons-arrow-down"></span>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif ( 'radio' === $type ):
                        // Get option values
                        $opt_values = ovabrw_get_meta_data( 'ova_radio_values', $fields, [] );
                        if ( !ovabrw_array_exists( $opt_values ) ) continue;

                        // Get option qtys
                        $opt_qtys = ovabrw_get_meta_data( 'ova_radio_qtys', $fields, [] );
                    ?>
                        <div class="ovabrw-radio <?php echo esc_attr( $class ); ?>">
                            <?php foreach ( $opt_values as $k => $value ):
                                // Get default
                                if ( !$default && $required ) $default = $value;

                                // Get option quantity
                                $qty = (int)ovabrw_get_meta_data( $k, $opt_qtys );
                            ?>
                                <div class="radio-item">
                                    <label>
                                        <?php ovabrw_wp_text_input([
                                            'type'      => 'radio',
                                            'name'      => $name.'['.$product_id.']',
                                            'value'     => $value,
                                            'checked'   => $default === $value ? true : false
                                        ]);

                                        echo esc_html( $value ); ?>
                                    </label>
                                    <span class="ovabrw-remove-checked">
                                        <span class="dashicons dashicons-no-alt"></span>
                                    </span>
                                    <?php if ( $qty ): ?>
                                        <div class="radio-item-qty" data-option="<?php echo esc_attr( $value ); ?>">
                                            <span class="radio-qty">1</span>
                                            <?php ovabrw_wp_text_input([
                                                'type'  => 'text',
                                                'class' => 'radio-input-qty',
                                                'name'  => $name.'_qty['.$product_id.']['.$value.']',
                                                'value' => 1,
                                                'attrs' => [
                                                    'min' => 1,
                                                    'max' => $opt_qty
                                                ]
                                            ]); ?>
                                            <div class="ovabrw-radio-icon">
                                                <span class="dashicons dashicons-arrow-up"></span>
                                                <span class="dashicons dashicons-arrow-down"></span>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; // END foreach ?>
                        </div>
                    <?php elseif ( 'select' === $type ):
                        // Get option ids
                        $opt_ids = ovabrw_get_meta_data( 'ova_options_key', $fields, [] );
                        if ( !ovabrw_array_exists( $opt_ids ) ) continue;

                        // Get option values
                        $opt_values = ovabrw_get_meta_data( 'ova_options_text', $fields, [] );

                        // Get option qtys
                        $opt_qtys = ovabrw_get_meta_data( 'ova_options_qty', $fields, [] );
                    ?>
                        <div class="ovabrw-select">
                            <select name="<?php echo esc_attr( $name.'['.$product_id.']' ); ?>" class="<?php echo esc_attr( $class ); ?>">
                                <option value="">
                                    <?php echo sprintf( esc_html__( 'Select %s', 'tripgo' ), esc_attr( $label ) ); ?>
                                </option>
                                <?php foreach ( $opt_ids as $k => $opt_id ):
                                    // Get default
                                    if ( !$default && $required ) $default = $opt_id;

                                    // Get option value
                                    $value = ovabrw_get_meta_data( $k, $opt_values );

                                    // Get option quantity
                                    $qty = (int)ovabrw_get_meta_data( $k, $opt_qtys );
                                    if ( $qty ) $option_qtys[$opt_id] = $qty;
                                ?>
                                    <option value="<?php echo esc_attr( $opt_id ); ?>"<?php selected( $default, $opt_id ); ?>>
                                        <?php echo esc_html( $value ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ( ovabrw_array_exists( $option_qtys ) ):
                                foreach ( $option_qtys as $opt_id => $opt_qty ): ?>
                                    <div class="select-item-qty" data-option="<?php echo esc_attr( $opt_id ); ?>">
                                        <span class="select-qty">1</span>
                                        <?php tripgo_text_input([
                                            'type'  => 'text',
                                            'class' => 'select-input-qty',
                                            'name'  => $name.'_qty['.$product_id.']['.$opt_id.']',
                                            'value' => 1,
                                            'attrs' => [
                                                'min' => 1,
                                                'max' => $opt_qty
                                            ]
                                        ]); ?>
                                        <div class="ovabrw-select-icon">
                                            <span class="dashicons dashicons-arrow-up"></span>
                                            <span class="dashicons dashicons-arrow-down"></span>
                                        </div>
                                    </div>
                            <?php endforeach;
                            endif; ?>
                        </div>
                    <?php elseif ( 'textarea' === $type ):
                        ovabrw_wp_textarea([
                            'class'         => $class,
                            'name'          => $name.'['.$product_id.']',
                            'placeholder'   => $placeholder,
                            'value'         => $default,
                            'attrs'         => [
                                'rows' => 5
                            ]
                        ]);
                    elseif ( 'file' === $type ):
                        // Get file max size
                        $max_size = ovabrw_get_meta_data( 'max_file_size', $fields );

                        // File mimes
                        $mimes = apply_filters( OVABRW_PREFIX.'file_mimes', [
                            'jpg'   => 'image/jpeg',
                            'jpeg'  => 'image/pjpeg',
                            'png'   => 'image/png',
                            'pdf'   => 'application/pdf',
                            'doc'   => 'application/msword',
                        ]);
                    ?>
                        <div class="ovabrw-file">
                            <?php ovabrw_wp_text_input([
                                'type'  => $type,
                                'class' => $class,
                                'name'  => $name.'['.$product_id.']',
                                'attrs' => [
                                    'data-max-size' => $max_size,
                                    'data-mimes'    => json_encode( $mimes )
                                ]
                            ]); ?>
                        </div>
                    <?php else:
                        ovabrw_wp_text_input([
                            'type'          => $type,
                            'class'         => $class,
                            'name'          => $name.'['.$product_id.']',
                            'value'         => $default,
                            'placeholder'   => $placeholder
                        ]);
                    endif; ?>
                </div>
            <?php endforeach;

            // CCKF data
            ovabrw_wp_text_input([
                'type'  => 'hidden',
                'name'  => 'data_custom_ckf',
                'value' => json_encode( $cckf )
            ]);
        endif;

        $html = ob_get_contents();
        ob_end_clean();

        return apply_filters( OVABRW_PREFIX.'get_html_ckf_order', $html, $product_id );
    }
}

/**
 * Get html resources when created order in admin
 */
if ( !function_exists( 'ovabrw_get_html_resources_order' ) ) {
    function ovabrw_get_html_resources_order( $product_id = false, $currency = '' ) {
        if ( !$product_id ) return '';

        // init
        $html = '';

        // Get option ids
        $opt_ids = ovabrw_get_post_meta( $product_id, 'rs_id' );
        if ( ovabrw_array_exists( $opt_ids ) ) {
            // Show children
            $show_children = ovabrw_show_children( $product_id );

            // Show baby
            $show_baby = ovabrw_show_babies( $product_id );

            // Get option name
            $opt_names = ovabrw_get_post_meta( $product_id, 'rs_name' );

            // Get adult prices
            $adult_prices = ovabrw_get_post_meta( $product_id, 'rs_adult_price' );

            // Get child prices
            $child_prices = ovabrw_get_post_meta( $product_id, 'rs_children_price' );

            // Get baby prices
            $baby_prices = ovabrw_get_post_meta( $product_id, 'rs_baby_price' );

            // Get max quantity
            $max_qtys = tripgo_get_post_meta( $product_id, 'rs_quantity' );

            // Get durations
            $durations = ovabrw_get_post_meta( $product_id, 'rs_duration_type' );

            ob_start(); ?>
            <div class="resources_order">
                <?php foreach ( $opt_ids as $k => $opt_id ):
                    if ( !$opt_id ) continue;

                    // Resource name
                    $name = ovabrw_get_meta_data( $k, $opt_names );

                    // Adult price
                    $adult_price = (float)ovabrw_get_meta_data( $k, $adult_prices );

                    // Children price
                    $child_price = (float)ovabrw_get_meta_data( $k, $child_prices );

                    // Baby price
                    $baby_price = (float)ovabrw_get_meta_data( $k, $baby_prices );

                    // Get max quantity
                    $max_qty = (int)tripgo_get_meta_data( $k, $max_qtys );

                    // Duration
                    $duration = ovabrw_get_meta_data( $k, $durations, 'person' );
                ?>
                    <div class="item">
                        <div class="left">
                            <label>
                                <?php ovabrw_admin_text_input([
                                    'type'  => 'checkbox',
                                    'class' => 'ovabrw_resource_checkboxs',
                                    'name'  => 'ovabrw_resource_checkboxs['.$product_id.']['.$opt_id.']',
                                    'value' => $name,
                                    'attrs' => [
                                        'data-rs-key' => $opt_id
                                    ]
                                ]);

                                echo esc_html( $name ); ?>
                            </label>
                            <?php if ( $max_qty ): ?>
                                <div class="ovabrw-resource-guest">
                                    <span class="dashicons dashicons-admin-users"></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php if ( apply_filters( OVABRW_PREFIX.'show_resource_prices', false ) ): ?>
                            <div class="right">
                                <div class="adult-price">
                                    <label class="adult-label">
                                        <?php esc_html_e( 'Adult: ', 'ova-brw' ); ?>
                                    </label>
                                    <span class="price">
                                        <?php echo wp_kses_post( ovabrw_wc_price( $adult_price, ['currency' => $currency] ) ); ?>
                                    </span>
                                    <span class="duration">
                                        <?php if ( 'person' === $duration ) {
                                            esc_html_e( '/per person', 'ova-brw' );
                                        } else {
                                            esc_html_e( '/order', 'ova-brw' );
                                        } ?>
                                    </span>
                                </div>
                                <?php if ( $show_children ): ?>
                                    <div class="children-price">
                                        <label class="children-label">
                                            <?php esc_html_e( 'Child: ', 'ova-brw' ); ?>
                                        </label>
                                        <span class="price">
                                            <?php echo wp_kses_post( ovabrw_wc_price( $child_price, ['currency' => $currency] ) ); ?>
                                        </span>
                                        <span class="duration">
                                            <?php if ( 'person' === $duration ) {
                                                esc_html_e( '/per person', 'ova-brw' );
                                            } else {
                                                esc_html_e( '/order', 'ova-brw' );
                                            } ?>
                                        </span>
                                    </div>
                                <?php endif;

                                // Baby
                                if ( $show_baby ): ?>
                                    <div class="baby-price">
                                        <label class="baby-label">
                                            <?php esc_html_e( 'Baby: ', 'ova-brw' ); ?>
                                        </label>
                                        <span class="price">
                                            <?php echo wp_kses_post( ovabrw_wc_price( $baby_price, ['currency' => $currency] ) ); ?>
                                        </span>
                                        <span class="duration">
                                            <?php if ( 'person' === $duration ) {
                                                esc_html_e( '/per person', 'ova-brw' );
                                            } else {
                                                esc_html_e( '/order', 'ova-brw' );
                                            } ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif;

                        // Max quantity
                        if ( $max_qty ): ?>
                            <div class="ovabrw-resource-guestspicker" data-max-quantity="<?php echo esc_attr( $max_qty ); ?>">
                                <div class="guests-item">
                                    <div class="guests-info">
                                        <label>
                                            <?php echo esc_html__( 'Adult', 'tripgo' ); ?>
                                        </label>
                                        <div class="guests-price">
                                            <span class="adult-price">
                                                <?php echo ovabrw_wc_price( $adult_price ); ?>
                                            </span>
                                            <span class="duration">
                                                <?php if ( 'person' === $duration ) {
                                                    echo esc_html__( '/per person', 'tripgo' );
                                                } else {
                                                    echo esc_html__( '/order', 'tripgo' );
                                                } ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="guests-action">
                                        <div class="guests-icon guests-minus">
                                            <span class="dashicons dashicons-minus"></span>
                                        </div>
                                        <?php ovabrw_admin_text_input([
                                            'type'  => 'text',
                                            'class' => 'resource-guests-input',
                                            'name'  => 'ovabrw_resource_guests['.$product_id.']['.$opt_id.'][adult]',
                                            'value' => 1
                                        ]); ?>
                                        <div class="guests-icon guests-plus">
                                            <span class="dashicons dashicons-plus-alt2"></span>
                                        </div>
                                    </div>
                                </div>
                                <?php if ( $show_children ): ?>
                                    <div class="guests-item">
                                    <div class="guests-info">
                                        <label>
                                            <?php echo esc_html__( 'Child', 'tripgo' ); ?>
                                        </label>
                                        <div class="guests-price">
                                            <span class="child-price">
                                                <?php echo ovabrw_wc_price( $child_price ); ?>
                                            </span>
                                            <span class="duration">
                                                <?php if ( 'person' === $duration ) {
                                                    echo esc_html__( '/per person', 'tripgo' );
                                                } else {
                                                    echo esc_html__( '/order', 'tripgo' );
                                                } ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="guests-action">
                                        <div class="guests-icon guests-minus">
                                            <span class="dashicons dashicons-minus"></span>
                                        </div>
                                        <?php ovabrw_admin_text_input([
                                            'type'  => 'text',
                                            'class' => 'resource-guests-input',
                                            'name'  => 'ovabrw_resource_guests['.$product_id.']['.$opt_id.'][child]',
                                            'value' => 0
                                        ]); ?>
                                        <div class="guests-icon guests-plus">
                                            <span class="dashicons dashicons-plus-alt2"></span>
                                        </div>
                                    </div>
                                </div>
                                <?php endif;

                                // Show baby
                                if ( $show_baby ): ?>
                                    <div class="guests-item">
                                    <div class="guests-info">
                                        <label>
                                            <?php echo esc_html__( 'Baby', 'tripgo' ); ?>
                                        </label>
                                        <div class="guests-price">
                                            <span class="baby-price">
                                                <?php echo ovabrw_wc_price( $baby_price ); ?>
                                            </span>
                                            <span class="duration">
                                                <?php if ( 'person' === $duration ) {
                                                    echo esc_html__( '/per person', 'tripgo' );
                                                } else {
                                                    echo esc_html__( '/order', 'tripgo' );
                                                } ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="guests-action">
                                        <div class="guests-icon guests-minus">
                                            <span class="dashicons dashicons-minus"></span>
                                        </div>
                                        <?php ovabrw_admin_text_input([
                                            'type'  => 'text',
                                            'class' => 'resource-guests-input',
                                            'name'  => 'ovabrw_resource_guests['.$product_id.']['.$opt_id.'][baby]',
                                            'value' => 0
                                        ]); ?>
                                        <div class="guests-icon guests-plus">
                                            <span class="dashicons dashicons-plus-alt2"></span>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php $html = ob_get_contents(); 
            ob_end_clean();
        }

        return apply_filters( OVABRW_PREFIX.'get_html_resources_order', $html, $product_id, $currency );
    }
}

/**
 * Get html services when created order in admin
 */
if ( !function_exists( 'ovabrw_get_html_services_order' ) ) {
    function ovabrw_get_html_services_order( $product_id = false, $currency = '' ) {
        if ( !$product_id ) return '';

        // init
        $html = '';

        // Get services
        $services = ovabrw_get_post_meta( $product_id, 'label_service' );
        if ( ovabrw_array_exists( $services ) ) {
            // Show children
            $show_children = ovabrw_show_children( $product_id );

            // Show baby
            $show_baby = ovabrw_show_babies( $product_id );

            // Get service id
            $serv_ids = ovabrw_get_post_meta( $product_id, 'service_id' );

            // Get service required
            $serv_required = ovabrw_get_post_meta( $product_id, 'service_required' );

            // Get service name
            $serv_names = ovabrw_get_post_meta( $product_id, 'service_name' );

            // Get adult prices
            $serv_adult_prices = ovabrw_get_post_meta( $product_id, 'service_adult_price' );

            // Get child prices
            $serv_child_prices = ovabrw_get_post_meta( $product_id, 'service_children_price' );

            // Get baby prices
            $serv_baby_prices = ovabrw_get_post_meta( $product_id, 'service_baby_price' );

            // Get service quantity
            $serv_qtys = ovabrw_get_post_meta( $product_id, 'service_quantity' );

            // Get durations
            $serv_durations = ovabrw_get_post_meta( $product_id, 'service_duration_type' );

            ob_start(); ?>
            <div class="services_order">
                <?php foreach ( $services as $i => $label ):
                    // Get option ids
                    $opt_ids = ovabrw_get_meta_data( $i, $serv_ids );
                    if ( !ovabrw_array_exists( $opt_ids ) ) continue;

                    // is required
                    $is_required = ovabrw_get_meta_data( $i, $serv_required );

                    // Get option names
                    $opt_names = ovabrw_get_meta_data( $i, $serv_names );

                    // Get adult prices
                    $adult_prices = ovabrw_get_meta_data( $i, $serv_adult_prices );

                    // Get child prices
                    $child_prices = ovabrw_get_meta_data( $i, $serv_child_prices );

                    // Get baby prices
                    $baby_prices = ovabrw_get_meta_data( $i, $serv_baby_prices );

                    // Max quantity
                    $opt_qtys = ovabrw_get_meta_data( $i, $serv_qtys );

                    // Durations
                    $durations = ovabrw_get_meta_data( $i, $serv_durations );

                    // Option max quantites
                    $max_qtys = [];

                    // Option guest prices
                    $guest_prices = [];
                ?>
                    <div class="item">
                        <select name="ovabrw_service[<?php echo esc_attr( $product_id ); ?>][]" class="<?php echo 'yes' === $is_required ? 'required' : ''; ?>" data-error="<?php echo 'yes' === $is_required ? sprintf( esc_html__( '%s is required.', 'ova-brw' ), $label ) : ''; ?>">
                            <option value="">
                                <?php echo sprintf( esc_html__( 'Select %s', 'ova-brw' ), $label ); ?>
                            </option>
                            <?php foreach ( $opt_ids as $k => $opt_id ):
                                // Get name
                                $name = ovabrw_get_meta_data( $k, $opt_names );

                                // Adult price
                                $adult_price = ovabrw_get_meta_data( $k, $adult_prices );

                                // Child price
                                $child_price = ovabrw_get_meta_data( $k, $child_prices );

                                // Baby price
                                $baby_price = ovabrw_get_meta_data( $k, $baby_prices );

                                // Max quantity
                                $qty = (int)ovabrw_get_meta_data( $k, $opt_qtys );

                                // Duration
                                $duration = ovabrw_get_meta_data( $k, $durations );
                                if ( 'person' === $duration ) {
                                    $duration = esc_html__( '/per person', 'tripgo' );
                                } else {
                                    $duration = esc_html__( '/order', 'tripgo' );
                                }

                                // Add max quantites
                                if ( $qty ) {
                                    $max_qtys[$opt_id] = $qty;

                                    // Add guest prices
                                    $guest_prices[$opt_id] = [
                                        'adult' => sprintf( '%s%s', ovabrw_wc_price( $adult_price ), $duration ),
                                        'child' => sprintf( '%s%s', ovabrw_wc_price( $child_price ), $duration ),
                                        'baby'  => sprintf( '%s%s', ovabrw_wc_price( $baby_price ), $duration ),
                                    ];
                                }
                            ?>
                                <option value="<?php echo esc_attr( $opt_id ); ?>">
                                    <?php if ( apply_filters( OVABRW_PREFIX.'show_services_duration', false ) ) {
                                        if ( $show_children && $show_baby ) {
                                            $name .= sprintf( esc_html__( ' (Adult: %s%s - Child: %s%s - Baby: %s%s)', 'ova-brw' ), ovabrw_wc_price( $adult_price, [ 'currency' => $currency ] ), $duration, ovabrw_wc_price( $child_price, [ 'currency' => $currency ] ), $duration, ovabrw_wc_price( $baby_price, [ 'currency' => $currency ] ), $duration );
                                        } elseif ( $show_children && !$show_baby ) {
                                            $name .= sprintf( esc_html__( ' (Adult: %s%s - Child: %s%s', 'ova-brw' ), ovabrw_wc_price( $adult_price, [ 'currency' => $currency ] ), $duration, ovabrw_wc_price( $child_price, [ 'currency' => $currency ] ), $duration );
                                        } elseif ( !$show_children && $show_baby ) {
                                            $name .= sprintf( esc_html__( ' (Adult: %s%s - Baby: %s%s)', 'ova-brw' ), ovabrw_wc_price( $adult_price, [ 'currency' => $currency ] ), $duration, ovabrw_wc_price( $baby_price, [ 'currency' => $currency ] ), $duration );
                                        } else {
                                            $name .= sprintf( esc_html__( ' (Adult: %s%s)', 'ova-brw' ), ovabrw_wc_price( $adult_price, [ 'currency' => $currency ] ), $duration );
                                        }

                                        echo wp_kses_post( $name );
                                    } else {
                                        echo esc_html( $name );
                                    } ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ( tripgo_array_exists( $max_qtys ) ): ?>
                            <div class="ovabrw-service-guest">
                                <span class="dashicons dashicons-admin-users"></span>
                            </div>
                            <?php foreach ( $max_qtys as $opt_id => $max_qty ):
                                // Adult price
                                $adult_price = isset( $guest_prices[$opt_id]['adult'] ) ? $guest_prices[$opt_id]['adult'] : '';

                                // Child price
                                $child_price = isset( $guest_prices[$opt_id]['child'] ) ? $guest_prices[$opt_id]['child'] : '';

                                // Baby price
                                $baby_price = isset( $guest_prices[$opt_id]['baby'] ) ? $guest_prices[$opt_id]['baby'] : '';
                            ?>
                                <div class="ovabrw-service-guestspicker" data-option="<?php echo esc_attr( $opt_id ); ?>" data-max-quantity="<?php echo esc_attr( $max_qty ); ?>">
                                    <div class="guests-item">
                                        <div class="guests-info">
                                            <label>
                                                <?php echo esc_html__( 'Adult', 'tripgo' ); ?>
                                            </label>
                                            <div class="guests-price">
                                                <?php echo wp_kses_post( $adult_price ); ?>
                                            </div>
                                        </div>
                                        <div class="guests-action">
                                            <div class="guests-icon guests-minus">
                                                <span class="dashicons dashicons-minus"></span>
                                            </div>
                                            <?php tripgo_text_input([
                                                'type'  => 'text',
                                                'class' => 'service-guests-input',
                                                'name'  => 'ovabrw_service_guests['.$product_id.']['.$opt_id.'][adult]',
                                                'value' => 1
                                            ]); ?>
                                            <div class="guests-icon guests-plus">
                                                <span class="dashicons dashicons-plus-alt2"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if ( $show_children ): ?>
                                        <div class="guests-item">
                                            <div class="guests-info">
                                                <label>
                                                    <?php echo esc_html__( 'Child:', 'tripgo' ); ?>
                                                </label>
                                                <div class="guests-price">
                                                    <?php echo wp_kses_post( $child_price ); ?>
                                                </div>
                                            </div>
                                            <div class="guests-action">
                                                <div class="guests-icon guests-minus">
                                                    <span class="dashicons dashicons-minus"></span>
                                                </div>
                                                <?php tripgo_text_input([
                                                    'type'  => 'text',
                                                    'class' => 'service-guests-input',
                                                    'name'  => 'ovabrw_service_guests['.$product_id.']['.$opt_id.'][child]',
                                                    'value' => 0
                                                ]); ?>
                                                <div class="guests-icon guests-plus">
                                                    <span class="dashicons dashicons-plus-alt2"></span>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif;

                                    // Show baby
                                    if ( $show_baby ): ?>
                                        <div class="guests-item">
                                            <div class="guests-info">
                                                <label>
                                                    <?php echo esc_html__( 'Baby:', 'tripgo' ); ?>
                                                </label>
                                                <div class="guests-price">
                                                    <?php echo wp_kses_post( $baby_price ); ?>
                                                </div>
                                            </div>
                                            <div class="guests-action">
                                                <div class="guests-icon guests-minus">
                                                    <span class="dashicons dashicons-minus"></span>
                                                </div>
                                                <?php tripgo_text_input([
                                                    'type'  => 'text',
                                                    'class' => 'service-guests-input',
                                                    'name'  => 'ovabrw_service_guests['.$product_id.']['.$opt_id.'][baby]',
                                                    'value' => 0
                                                ]); ?>
                                                <div class="guests-icon guests-plus">
                                                    <span class="dashicons dashicons-plus-alt2"></span>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach;
                        endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php $html = ob_get_contents();
            ob_end_clean();
        }

        return apply_filters( OVABRW_PREFIX.'get_html_services_order', $html, $product_id, $currency );
    }
}

/**
 * Get html fixed time when created order in admin
 */
if ( !function_exists( 'ovabrw_get_html_fixed_time_order' ) ) {
    function ovabrw_get_html_fixed_time_order( $product_id = null ) {
        if ( !$product_id ) return '';

        // init
        $html = '';

        // Get duration
        $duration = ovabrw_get_post_meta( $product_id, 'duration_checkbox' );

        // Check-in
        $fixed_time_check_in = ovabrw_get_post_meta( $product_id, 'fixed_time_check_in' );

        // Check-out
        $fixed_time_check_out = ovabrw_get_post_meta( $product_id, 'fixed_time_check_out' );
        if ( !$duration && ovabrw_array_exists( $fixed_time_check_in ) && ovabrw_array_exists( $fixed_time_check_out ) ) {
            // Get date format
            $date_format = ovabrw_get_date_format();

            // Had time
            $had_time = false;

            // Preparation Time
            $preparation_time = (int)ovabrw_get_post_meta( $product_id, 'preparation_time' );

            $html .= '<div class="rental_item ovabrw-fixed-time">';
                $html .= '<label for="ovabrw-fixed-time">';
                    $html .= esc_html__( 'Choose time *', 'ova-brw' );
                $html .= '</label>';
                $html .= '<select name="ovabrw-fixed-time">';
                    $flag = 0;
                    foreach ( $fixed_time_check_in as $k => $check_in ) {
                        if ( !strtotime( $check_in ) || strtotime( $check_in ) < current_time('timestamp') ) continue;

                        // Get check-out
                        $check_out = ovabrw_get_meta_data( $k, $fixed_time_check_out );
                        if ( !strtotime( $check_out ) ) continue;
                        
                        // Preparation time
                        if ( $preparation_time ) {
                            $new_input_date = ovabrw_new_input_date( $product_id, strtotime( $check_in ), strtotime( $check_out ), $date_format );

                            if ( $new_input_date['pickup_date_new'] < ( current_time( 'timestamp' ) + $preparation_time*86400 - 86400 ) ) continue;
                        }

                        // Get guests available
                        if ( ovabrw_qty_by_guests( $product_id ) ) {
                            $guests_available = ovabrw_validate_guests_available( $product_id, strtotime( $check_in ), strtotime( $check_out ), [], 'search' );

                            if ( !$guests_available ) continue;
                        } else {
                            $ovabrw_quantity = sanitize_text_field( filter_input( INPUT_POST, 'ovabrw_quantity' ) );
                            $quantity = !empty( $ovabrw_quantity ) ? absint( $ovabrw_quantity ) : 1;

                            $qty_available = ova_validate_manage_store( $product_id, strtotime( $check_in ), strtotime( $check_out ), false, 'search', $quantity );

                            if ( !$qty_available ) continue;
                        }

                        $had_time = true;
                        $txt_time = sprintf( esc_html__( 'From %s to %s', 'ova-brw' ), $check_in, $check_out );

                        $html .= '<option value="'.esc_html( $check_in.'|'.$check_out ).'"'.ovabrw_selected( $flag, 0, false ).'>';
                            $html .= esc_html( $txt_time );
                        $html .= '</option>';

                        $flag++;
                    } // END foreach

                    if ( !$had_time ) {
                        $html .= '<option value="">';
                            $html .= esc_html__( 'No time', 'ova-brw' );
                        $html .= '</option>';
                    }
                $html .= '</select>';
            $html .= '</div';
        }

        return apply_filters( OVABRW_PREFIX.'get_html_fixed_time_order', $html, $product_id );
    }
}

if ( ! function_exists( 'ovabrw_tour_products_dropdown_simple' ) ) {
    function ovabrw_tour_products_dropdown_simple( $placeholder = '', $selected = '' ) {
        if ( ! $placeholder ) {
            $placeholder = esc_html__( 'Selecciona un tour', 'ova-brw' );
        }

        $html  = '<select id="brw-destinations-select-box" name="ovabrw_destination">';
        $html .= '<option value="all">' . esc_html( $placeholder ) . '</option>';

        $products = new WP_Query([
            'post_type'      => 'product',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'order'          => 'ASC',
            'orderby'        => 'title',
            'tax_query'      => [
                [
                    'taxonomy' => 'product_type',
                    'field'    => 'slug',
                    'terms'    => 'ovabrw_car_rental',
                ]
            ]
        ]);

        if ( $products->have_posts() ) {
            while ( $products->have_posts() ) {
                $products->the_post();

                $id    = get_the_ID();
                $title = get_the_title();

                $html .= '<option value="' . esc_attr( $id ) . '" ' . selected( $selected, $id, false ) . '>';
                $html .= esc_html( $title );
                $html .= '</option>';
            }
        }

        wp_reset_postdata();

        $html .= '</select>';

        return apply_filters( OVABRW_PREFIX . 'tour_products_dropdown_simple', $html, $placeholder, $selected );
    }
}

/**
 * Map destination post ID => space-separated province names from ACF (Buscador destinos).
 *
 * @return array<int, string>
 */
if ( ! function_exists( 'ovabrw_get_destination_province_search_map' ) ) {
    function ovabrw_get_destination_province_search_map() {
        static $cache = null;

        if ( null !== $cache ) {
            return $cache;
        }

        $map = [];

        if ( ! function_exists( 'get_field' ) ) {
            $cache = apply_filters( 'ovabrw_destination_province_search_map', $map );
            return $cache;
        }

        /**
         * ACF options context: subpage slug, main options, etc.
         * Filter to match your options page post_id if needed.
         */
        $contexts = apply_filters(
            'ovabrw_destination_province_acf_contexts',
            [
                'buscador-destinos',
                'buscador_destinos',
                'option',
            ]
        );

        $rows = null;
        foreach ( $contexts as $ctx ) {
            $rows = get_field( 'comunidades_autonomas', $ctx );
            if ( ! empty( $rows ) && is_array( $rows ) ) {
                break;
            }
        }

        if ( empty( $rows ) || ! is_array( $rows ) ) {
            $cache = apply_filters( 'ovabrw_destination_province_search_map', $map );
            return $cache;
        }

        foreach ( $rows as $row ) {
            if ( ! is_array( $row ) ) {
                continue;
            }

            $com = isset( $row['comunidad_autonoma'] ) ? $row['comunidad_autonoma'] : null;
            $post_id = 0;

            if ( is_object( $com ) && isset( $com->ID ) ) {
                $post_id = (int) $com->ID;
            } elseif ( is_numeric( $com ) ) {
                $post_id = (int) $com;
            }

            if ( ! $post_id ) {
                continue;
            }

            $provs = [];
            if ( ! empty( $row['provincias'] ) && is_array( $row['provincias'] ) ) {
                foreach ( $row['provincias'] as $p_row ) {
                    if ( ! is_array( $p_row ) || empty( $p_row['provincia'] ) ) {
                        continue;
                    }
                    $t = trim( wp_strip_all_tags( (string) $p_row['provincia'] ) );
                    if ( $t !== '' ) {
                        $provs[] = $t;
                    }
                }
            }

            $map[ $post_id ] = implode( ' ', array_unique( $provs ) );
        }

        $cache = apply_filters( 'ovabrw_destination_province_search_map', $map );
        return $cache;
    }
}

/**
 * data-ovabrw-search for Select2: destination title + provinces (ACF).
 *
 * @param int               $post_id    Destination post ID.
 * @param string            $title      Destination title.
 * @param array<int,string> $search_map From ovabrw_get_destination_province_search_map().
 */
if ( ! function_exists( 'ovabrw_destination_option_data_attr' ) ) {
    function ovabrw_destination_option_data_attr( $post_id, $title, $search_map ) {
        $post_id = (int) $post_id;
        $extra   = isset( $search_map[ $post_id ] ) ? $search_map[ $post_id ] : '';
        $combined = trim( $title . ' ' . $extra );

        return ' data-ovabrw-search="' . esc_attr( $combined ) . '"';
    }
}

/**
 *  HTML Destinantion Dropdown
 */
if ( !function_exists( 'ovabrw_destination_dropdown' ) ) {
    function ovabrw_destination_dropdown( $placeholder, $selected ) {
        // init
        $html = '';

        $search_map = ovabrw_get_destination_province_search_map();

        // Get destination category
        $cats = get_categories([
            'taxonomy'  => 'cat_destination',
            'orderby'   => 'name',
            'order'     => 'ASC'
        ]);

        if ( !$placeholder ) {
            $placeholder = esc_html__( 'What are you going?', 'ova-brw' );
        }

        if ( ovabrw_array_exists( $cats ) ) {
            $html .= '<select id="brw-destinations-select-box" name="ovabrw_destination"><option value="all">'. esc_html( $placeholder ) .'</option>';

            // Loop
            foreach ( $cats as $cat ) {
                $cat_id = $cat->term_id;
                //$html .= '<optgroup label="'. esc_attr( $cat->name ) . '">';

                // Get destinations
                $destinations = new WP_Query([
                    'post_type'         => 'destination',
                    'posts_per_page'    => -1,
                    'order'             => 'ASC',
                    'orderby'           => 'title',
                    'tax_query'         => [
                        [
                            'taxonomy' => 'cat_destination',
                            'field'    => 'term_id',
                            'terms'    => $cat_id
                        ]
                    ]
                ]);

                if ( $destinations->have_posts()) : while ( $destinations->have_posts()) : $destinations->the_post();
                    global $post;
                    $id    = get_the_id();
                    $title = get_the_title();
                    $dattr = ovabrw_destination_option_data_attr( $id, $title, $search_map );

                    if ( $id == $selected ) {
                        $html .= '<option value="'. esc_attr( $id ) .'" selected="selected"'. $dattr .'>'. esc_html( $title ) .'</option>';
                    } else {
                        $html .= '<option value="'. esc_attr( $id ) .'"'. $dattr .'>'. esc_html( $title ) .'</option>';
                    }
                endwhile; endif; wp_reset_postdata();

                //$html .= '</optgroup>';
            }

            $html .= '</select>';
        } else {
            $html .= '<select id="brw-destinations-select-box" name="ovabrw_destination"><option value="all">'. esc_html( $placeholder ) .'</option>';

            // Get destinations
            $destinations = new WP_Query([
                'post_type'         => 'destination',
                'posts_per_page'    => -1,
                'order'             => 'ASC',
                'orderby'           => 'title'
            ]);

            if ( $destinations->have_posts()) : while ( $destinations->have_posts()) : $destinations->the_post();
                global $post;
                $id    = get_the_id();
                $title = get_the_title();
                $dattr = ovabrw_destination_option_data_attr( $id, $title, $search_map );

                if ( $id == $selected ) {
                    $html .= '<option value="'. esc_attr( $id ) .'" selected="selected"'. $dattr .'>'. esc_html( $title ) .'</option>';
                } else {
                    $html .= '<option value="'. esc_attr( $id ) .'"'. $dattr .'>'. esc_html( $title ) .'</option>';
                }
            endwhile; endif; wp_reset_postdata();

            $html .= '</select>';
        }

        return apply_filters( OVABRW_PREFIX.'destination_dropdown', $html, $placeholder, $selected );
    }
}

/**
 *  Get html taxonomy search ajax
 */
if ( !function_exists( 'ovabrw_search_taxonomy_dropdown' ) ) {
    function ovabrw_search_taxonomy_dropdown( $taxonomy, $name, $selected, $class ) {
        // WPML
        if ( $selected ) {
            $selected = ovabrw_get_translated_term_slug( $selected, $taxonomy );
        }

        $args = [
            'show_option_all'    => '',
            'show_option_none'   => esc_html( $name ),
            'option_none_value'  => 'all',
            'orderby'            => 'ID',
            'order'              => 'ASC',
            'show_count'         => 0,
            'hide_empty'         => 0,
            'child_of'           => 0,
            'exclude'            => '',
            'include'            => '',
            'echo'               => 0,
            'selected'           => $selected,
            'hierarchical'       => 1,
            'name'               => $taxonomy.'_name',
            'id'                 => '',
            'class'              => $class. ' brw_custom_taxonomy_dropdown',
            'depth'              => 0,
            'tab_index'          => 0,
            'taxonomy'           => $taxonomy,
            'hide_if_empty'      => false,
            'value_field'        => 'slug'
        ];

        return apply_filters( OVABRW_PREFIX.'search_taxonomy_dropdown', wp_dropdown_categories( $args ), $taxonomy, $name, $selected, $class );
    }
}

/**
 * Get translated slug taxonomy
 */
if ( !function_exists( 'ovabrw_get_translated_term_slug' ) ) {
    function ovabrw_get_translated_term_slug( $selected, $taxonomy ) {
        // WPML
        if ( $selected && is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
            $term = get_term_by('slug', $selected, $taxonomy);
            
            if ( $term && is_object( $term ) ) {
                $term_id = apply_filters( 'wpml_object_id', $term->term_id, $taxonomy );
                
                if ( $term_id ) {
                    $translated_term = get_term( $term_id, $taxonomy );
                    
                    if ( $translated_term && is_object( $translated_term ) ) {
                        $selected = $translated_term->slug;
                    }
                }
            }
        }

        return apply_filters( OVABRW_PREFIX.'get_translated_term_slug', $selected, $taxonomy );
    }
}

/**
 * Pagination ajax
 */
if ( !function_exists( 'ovabrw_pagination_ajax' ) ) {
    function ovabrw_pagination_ajax( $total, $limit, $current ) {
        $html   = '';
        $pages  = ceil( $total / $limit );

        if ( $pages > 1 ) {
            $html .= '<ul>';

            // Prev
            if ( $current > 1 ) {
                $html .= '<li>';
                    $html .= '<span data-paged="'. esc_attr( $current - 1 ) .'" class="prev page-numbers">';
                        $html .= '<i class="icomoon icomoon-angle-left"></i>';
                        $html .= esc_html__( 'Anterior', 'ova-brw' );
                    $html .= '</span>';
                $html .= '</li>';
            } // END if

            // Loop
            for ( $i = 1; $i <= $pages; $i++ ) {
                if ( $current == $i ) {
                    $html .= '<li><span data-paged="'. esc_attr( $i ) .'" class="prev page-numbers current" >'. esc_html( $i ) .'</span></li>';
                } else {
                    $html .= '<li><span data-paged="'. esc_attr( $i ) .'" class="prev page-numbers" >'. esc_html( $i ) .'</span></li>';
                }
            } // END for

            // Next
            if ( $current < $pages ) {
                $html .= '<li>';
                    $html .= '<span data-paged="'. esc_attr( $current + 1 ) .'" class="next page-numbers">';
                        $html .= esc_html__( 'Siguiente', 'ova-brw' );
                        $html .= '<i class="icomoon icomoon-angle-right"></i>';
                    $html .= '</span>';
                $html .= '</li>';
            } // END if
        }

        return apply_filters( OVABRW_PREFIX.'pagination_ajax', $html, $total, $limit, $current );
    }
}

/**
 * Get destinations
 */
if ( !function_exists( 'ovabrw_get_destinations' ) ) {
    function ovabrw_get_destinations() {
        $results = [
            '' => esc_html__( 'All Destination', 'ova-brw' )
        ];

        // Get destinations
        $destinations = get_posts([
            'post_type'         => 'destination',
            'post_status'       => 'publish',
            'posts_per_page'    => -1,
            'orderby'           => 'ID',
            'order'             => 'DESC',
            'fields'            => 'ids'
        ]);

        // Loop
        if ( ovabrw_array_exists( $destinations ) ) {
            foreach ( $destinations as $destination_id ) {
                $results[$destination_id] = get_the_title( $destination_id );
            }
        } // END if

        return apply_filters( OVABRW_PREFIX.'get_destinations', $results );
    }
}

/**
 * Get filtered price
 */
if ( !function_exists( 'ovabrw_get_filtered_price' ) ) {
    function ovabrw_get_filtered_price() {
        // Global wpdb
        global $wpdb;

        $sql = "
            SELECT min( min_price ) as min_price, MAX( max_price ) as max_price
            FROM {$wpdb->wc_product_meta_lookup}
            WHERE product_id IN (
                SELECT ID FROM {$wpdb->posts} 
                WHERE {$wpdb->posts}.post_type IN ('" . implode( "','", array_map( 'esc_sql', [ 'product' ] ) ) . "')
                AND {$wpdb->posts}.post_status = 'publish'
                " . ')';
                
        return $wpdb->get_row( $sql ); // WPCS: unprepared SQL ok.
    }
}

/**
 * Get exclude ids not available pickup date
 */
if ( !function_exists( 'ovabrw_get_exclude_ids' ) ) {
    function ovabrw_get_exclude_ids( $pickup_date ) {
        $exclude_ids = [];

        $products = ovabrw_get_tour_product_ids();
        if ( ovabrw_array_exists( $products ) ) {
            foreach ( $products as $product_id ) {
                if ( ovabrw_tour_excluded_on_pickup_timestamp( $product_id, $pickup_date ) ) {
                    array_push( $exclude_ids, $product_id );
                }
            }
        }

        return apply_filters( OVABRW_PREFIX.'get_exclude_ids', $exclude_ids, $pickup_date );
    }
}

/**
 * Check fixed time
 */
if ( !function_exists( 'ovabrw_check_fixed_time' ) ) {
    function ovabrw_check_fixed_time( $product_id, $pickup_date ) {
        $flag = false;

        // Get fixed time check-in
        $fixed_time_check_in = ovabrw_get_post_meta( $product_id, 'fixed_time_check_in' );

        // Get fixed time check-out
        $fixed_time_check_out = ovabrw_get_post_meta( $product_id, 'fixed_time_check_out' );

        if ( ovabrw_array_exists( $fixed_time_check_in ) && ovabrw_array_exists( $fixed_time_check_out ) ) {
            foreach ( $fixed_time_check_in as $k => $check_in ) {
                // Check-in date
                $check_in = strtotime( $check_in );
                if ( !$check_in ) continue;

                // Check-out date
                $check_out = strtotime( ovabrw_get_meta_data( $k, $fixed_time_check_out ) );
                if ( !$check_out ) continue;

                if ( $check_in <= $pickup_date && $pickup_date <= $check_out ) {
                    $flag = true;
                    break;
                }
            }
        } else {
            $flag = true;
        }

        return apply_filters( OVABRW_PREFIX.'check_fixed_time', $flag, $product_id, $pickup_date );
    }
}

/**
 * Get weekday string
 */
if ( !function_exists( 'ovabrw_get_weekday' ) ) {
    function ovabrw_get_weekday( $pickup_date = false ) {
        if ( !$pickup_date ) return false;

        // Weekday
        $week_day = '';

        // Get number of weekday
        $day = gmdate( 'w', $pickup_date );

        switch ( $day ) {
            case '0':
                $week_day = 'sunday';
                break;
            case '1':
                $week_day = 'monday';
                break;
            case '2':
                $week_day = 'tuesday';
                break;
            case '3':
                $week_day = 'wednesday';
                break;
            case '4':
                $week_day = 'thursday';
                break;
            case '5':
                $week_day = 'friday';
                break;
            case '6':
                $week_day = 'saturday';
                break;
            default:
                // code...
                break;
        }

        return apply_filters( OVABRW_PREFIX.'get_weekday', $week_day, $pickup_date );
    }
}

/**
 * Get duration time
 */
if ( !function_exists( 'ovabrw_get_duration_time' ) ) {
    function ovabrw_get_duration_time( $product_id = null, $pickup_date = false ) {
        if ( !$product_id || !$pickup_date ) return false;

        // Duration
        $duration_data = [];

        // Get date format
        $date_format = ovabrw_get_date_format();

        // Get datetime format
        $datetime_format = ovabrw_get_datetime_format();

        // Get weekday
        $week_day = ovabrw_get_weekday( $pickup_date );
        if ( !$week_day ) return false;

        // Get quantity
        $quantity = (int)sanitize_text_field( filter_input( INPUT_POST, 'ovabrw_quantity' ) );
        if ( !$quantity ) $quantity = 1;

        // Get schedule time
        $schedule_time = ovabrw_get_post_meta( $product_id, 'schedule_time' );

        if ( isset( $schedule_time[$week_day] ) && ! empty( $schedule_time[$week_day] ) && is_array( $schedule_time[$week_day] ) ) {
            foreach ( $schedule_time[$week_day] as $time ) {
                $checkin = date_i18n( $date_format, $pickup_date );
                if ( $time ) $checkin .= ' ' . $time;

                if ( strtotime( $checkin ) < current_time( 'timestamp' ) ) continue;
                $checkout = ovabrw_get_checkout_date( $product_id, strtotime( $checkin ) );

                if ( ovabrw_qty_by_guests( $product_id ) ) {
                    $guests_available = ovabrw_validate_guests_available( $product_id, strtotime( $checkin ), strtotime( $checkout ), [], 'search' );

                    if ( !empty( $guests_available ) && is_array( $guests_available ) ) {
                        array_push( $duration_data , $time );
                    }
                } else {
                    $qty_available = ova_validate_manage_store( $product_id, strtotime( $checkin ), strtotime( $checkout ), true, 'search', $quantity );

                    if ( $qty_available ) {
                        array_push( $duration_data , $time );
                    }
                }
            }

            return apply_filters( OVABRW_PREFIX.'get_duration_time', $duration_data, $product_id, $pickup_date );
        }

        return false;
    }
}

/**
 * Get duration HTML
 */
if ( !function_exists( 'ovabrw_get_html_duration' ) ) {
    function ovabrw_get_html_duration( $duration_time = [] ) {
        if ( !ovabrw_array_exists( $duration_time ) ) return false;

        $html = '<div class="rental_item ovabrw_times_field">';
            $html .= '<h3 class="ovabrw-label ovabrw-required">';
                $html .= esc_html__( 'Time', 'ova-brw' );
            $html .= '</h3>';
            $html .= '<div class="ovabrw-times ovabrw-input-required">';
                foreach ( $duration_time as $k => $time ) {
                    $html .= '<label class="duration">' . esc_html( $time );

                    if ( 0 == $k ) {
                        $html .= '<input type="radio" name="ovabrw_time_from" class="ovabrw_time_from" value="'. esc_attr( $time ) .'" checked="checked">';
                    } else {
                        $html .= '<input type="radio" name="ovabrw_time_from" class="ovabrw_time_from" value="'. esc_attr( $time ) .'">';
                    }
                    
                    $html .= '<span class="checkmark"></span>';
                    $html .= '</label>';
                }
            $html .= '</div>';
        $html .= '</div>';

        return apply_filters( OVABRW_PREFIX.'get_html_duration', $html, $duration_time );
    }
}

/**
 * Create order get duration HTML
 */
if ( !function_exists( 'ovabrw_create_order_get_html_duration' ) ) {
    function ovabrw_create_order_get_html_duration( $product_id = false, $duration_time = [] ) {
        if ( !$product_id || !ovabrw_array_exists( $duration_time ) ) return false;

        $html = '<div class="rental_item ovabrw_times_field">';
            $html .= '<label class="ovabrw-required">';
                $html .= esc_html__( 'Time', 'ova-brw' );
            $html .= '</label>';
            $html .= '<div class="ovabrw-times">';

                foreach ( $duration_time as $k => $time ) {
                    $html .= '<label class="duration">' . esc_html( $time );

                    if ( 0 == $k ) {
                        $html .= '<input type="radio" name="ovabrw_time_from['. esc_attr( $product_id ) .']" class="ovabrw_time_from" value="'. esc_attr( $time ) .'" checked="checked">';
                    } else {
                        $html .= '<input type="radio" name="ovabrw_time_from['. esc_attr( $product_id ) .']" class="ovabrw_time_from" value="'. esc_attr( $time ) .'">';
                    }
                    
                    $html .= '<span class="checkmark"></span>';
                    $html .= '</label>';
                }

            $html .= '</div>';
        $html .= '</div>';

        return apply_filters( OVABRW_PREFIX.'create_order_get_html_duration', $html, $product_id, $duration_time );
    }
}

/**
 * Duration
 */
if ( !function_exists( 'ovabrw_get_checkout_date' ) ) {
    function ovabrw_get_checkout_date( $product_id = false, $check_in = '' ) {
        if ( !$product_id || !$check_in ) return false;

        // Check-out date
        $check_out = '';

        // Get date format
        $date_format = ovabrw_get_date_format();

        // Get number of days
        $number_days = ovabrw_get_post_meta( $product_id, 'number_days' );

        // Get number of hours
        $number_hours = ovabrw_get_post_meta( $product_id, 'number_hours' );

        // Get duration
        $duration = ovabrw_get_post_meta( $product_id, 'duration_checkbox' );
        if ( $duration ) {
            $date_format    = ovabrw_get_datetime_format();
            $check_out      = $check_in + floatval( $number_hours ) * 3600;
        } else {
            $check_out = $check_in + absint( $number_days ) * 86400;
        }

        return apply_filters( OVABRW_PREFIX.'get_checkout_date', date_i18n( $date_format, $check_out ), $product_id, $check_in );
    }
}

/**
 * Check Qty by Guests
 */
if ( !function_exists( 'ovabrw_qty_by_guests' ) ) {
    function ovabrw_qty_by_guests( $product_id = null ) {
        if ( !$product_id ) return false;

        // Get quantity
        $qty_by_guests = ovabrw_get_post_meta( $product_id, 'stock_quantity_by_guests' );
        if ( $qty_by_guests ) return true;

        return false;
    }
}

/**
 * Get total number Guests
 */
if ( !function_exists( 'ovabrw_get_total_guests' ) ) {
    function ovabrw_get_total_guests( $product_id = null ) {
        if ( !$product_id ) return 0;

        // Get number of adults
        $number_adults = absint( ovabrw_get_post_meta( $product_id, 'adults_max' ) );

        // Get number of children
        $number_children = absint( ovabrw_get_post_meta( $product_id, 'childrens_max' ) );

        // Get number of babies
        $number_babies = absint( ovabrw_get_post_meta( $product_id, 'babies_max' ) );

        // Get stock quantity
        $stock_quantity = absint( ovabrw_get_post_meta( $product_id, 'stock_quantity' ) );
        $stock_quantity = $stock_quantity * ( $number_adults + $number_children + $number_babies );

        return apply_filters( OVABRW_PREFIX.'get_total_guests', $stock_quantity, $product_id );
    }
}

/**
 * Get fixed dates
 */
if ( !function_exists( 'ovabrw_get_fixed_dates' ) ) {
    function ovabrw_get_fixed_dates( $product_id = false, $next = 0 ) {
        // init
        $return_dates = [];

        if ( $product_id ) {
            // Fixed dates
            $checkin_dates  = ovabrw_get_post_meta( $product_id, 'fixed_time_check_in' );
            $checkout_dates = ovabrw_get_post_meta( $product_id, 'fixed_time_check_out' );

            if ( ovabrw_array_exists( $checkin_dates ) && ovabrw_array_exists( $checkout_dates ) ) {
                // Date format
                $date_format = ovabrw_get_date_format();

                // Number of days limit appears
                $limited = (int)apply_filters( OVABRW_PREFIX.'number_of_days_limit_appears', 5 );

                // Quantity
                $quantity = (int)ovabrw_get_post_meta( $product_id, 'stock_quantity' );

                // Number of guests
                if ( ovabrw_qty_by_guests( $product_id ) ) {
                    // Min number of adults
                    $min_adults = (int)ovabrw_get_post_meta( $product_id, 'adults_min' );

                    // Max number of adults
                    $max_adults = (int)ovabrw_get_post_meta( $product_id, 'adults_max' );
                    $numberof_adults = $max_adults*$quantity;

                    // Min number of children
                    $min_children = (int)ovabrw_get_post_meta( $product_id, 'childrens_min' );

                    // Max number of children
                    $max_children = (int)ovabrw_get_post_meta( $product_id, 'childrens_max' );
                    $numberof_children = $max_children*$quantity;

                    // Min number of babies
                    $min_babies = (int)ovabrw_get_post_meta( $product_id, 'babies_min' );

                    // Max number of babies
                    $max_babies = (int)ovabrw_get_post_meta( $product_id, 'babies_max' );
                    $numberof_babies = $max_babies*$quantity;
                }

                // Next date
                if ( $next ) {
                    $next += 1;
                    $checkin_dates  = array_slice( $checkin_dates, $next );
                    $checkout_dates = array_slice( $checkout_dates, $next );
                }

                // Unavailable time
                $untime_startdate = ovabrw_get_post_meta( $product_id, 'untime_startdate' );
                $untime_enddate   = ovabrw_get_post_meta( $product_id, 'untime_enddate' );

                // Disable weekdays
                $disable_weekdays = ovabrw_get_post_meta( $product_id, 'product_disable_week_day' );
                if ( !$disable_weekdays ) {
                    $disable_weekdays = ovabrw_get_option_setting( 'calendar_disable_week_day', '' );
                }
                if ( '' !== $disable_weekdays ) $disable_weekdays = explode( ',', $disable_weekdays );

                // Cart
                $cart_booked = ovabrw_get_booked_dates_form_cart( $product_id );

                // Order
                $order_booked = ovabrw_get_booked_dates_from_order( $product_id );

                // Loop
                foreach ( $checkin_dates as $k => $checkin_date ) {
                    // Check-in date
                    $checkin_date = strtotime( $checkin_date );
                    if ( !$checkin_date || $checkin_date < current_time( 'timestamp' ) ) continue;

                    // Check-out date
                    $checkout_date = isset( $checkout_dates[$k] ) ? strtotime( $checkout_dates[$k] ) : '';
                    if ( !$checkout_date || $checkout_date < $checkin_date ) continue;

                    // Preparation Time
                    $preparation_time = ovabrw_get_post_meta( $product_id, 'preparation_time' );
                    if ( $preparation_time ) {
                        $new_dates = ovabrw_new_input_date( $product_id, $checkout_date, $checkout_date, $date_format );

                        if ( $new_dates['pickup_date_new'] < ( current_time( 'timestamp' ) + $preparation_time*86400 - 86400 ) ) continue;
                    }

                    // Check Unavailable time
                    if ( ovabrw_array_exists( $untime_startdate ) ) {
                        // Day is blocked
                        $is_blocked = false;

                        foreach ( $untime_startdate as $untime_key => $start_date ) {
                            // Start date
                            $start_date = strtotime( $start_date );
                            $end_date   = strtotime( ovabrw_get_meta_data( $untime_key, $untime_enddate ) );

                            if ( !$start_date || !$end_date ) continue;
                            if ( !( $checkin_date > $end_date || $checkout_date < $start_date ) ) {
                                $is_blocked = true;
                                break;
                            }
                        }

                        // This day is blocked
                        if ( $is_blocked ) continue;
                    }
                    // End Check Unavailable time

                    // Check disable weekdays
                    if ( ovabrw_array_exists( $disable_weekdays ) ) {
                        // Day is blocked
                        $is_blocked = false;

                        // Init dates
                        $checkin_weekday    = $checkin_date;
                        $checkout_weekday   = $checkout_date;

                        // Get number of week
                        $checkin_numberof_week  = date( 'w', $checkin_weekday );
                        $checkout_numberof_week = date( 'w', $checkout_weekday );

                        if ( apply_filters( OVABRW_PREFIX.'disable_week_day', true ) ) {
                            if ( in_array( $checkin_numberof_week, $disable_weekdays ) || in_array( $checkout_numberof_week, $disable_weekdays ) ) {
                                continue;
                            }

                            while ( $checkin_weekday < $checkout_weekday ) {
                                $checkin_weekday    += 86400; // 24*60*60 = 1 day
                                $numberof_week      = date('w', $checkin_weekday );

                                if ( in_array( $numberof_week, $disable_weekdays ) ) {
                                    $is_blocked = true;
                                    break;
                                }
                            }
                        } else {
                            if ( in_array( $checkin_numberof_week, $disable_weekdays ) ) {
                                continue;
                            }
                        }

                        // This day is blocked
                        if ( $is_blocked ) continue;
                    }
                    // End Check disable weekdays
                    
                    // Check Cart
                    if ( ovabrw_array_exists( $cart_booked ) ) {
                        // Day is blocked
                        $is_blocked = false;

                        foreach ( $cart_booked as $cart_item ) {
                            if ( ovabrw_qty_by_guests( $product_id ) && $checkin_date === $cart_item['checkin_date'] ) {
                                $checkin_date       -= (int)$cart_item['numberof_adults'];
                                $numberof_children  -= (int)$cart_item['numberof_children'];
                                $numberof_babies    -= (int)$cart_item['numberof_babies'];

                                // Get number of guests available
                                if ( $numberof_adults <= 0 && $numberof_children <= 0 && $numberof_babies <= 0 ) {
                                    $is_blocked = true;
                                    continue;
                                }

                                // Check min guests
                                if ( apply_filters( OVABRW_PREFIX.'check_min_guests', true, $product_id ) ) {
                                    if ( $numberof_adults < $min_adults || $numberof_children < $min_children || $numberof_babies < $min_babies ) {
                                        $is_blocked = true;
                                        continue;
                                    }
                                }
                            } else {
                                if ( !( $checkin_date >= $cart_item['checkout_date'] || $checkout_date <= $cart_item['checkin_date'] ) ) {
                                    $quantity -= (int)$cart_item['quantity'];

                                    if ( $quantity <= 0 ) {
                                        $is_blocked = true;
                                        continue;
                                    }
                                }
                            }
                        }

                        // This day is blocked
                        if ( $is_blocked ) continue;
                    } // END Check Cart
                    
                    // Check Order
                    if ( ovabrw_array_exists( $order_booked ) ) {
                        // Day is blocked
                        $is_blocked = false;

                        foreach ( $order_booked as $order_item ) {
                            if ( ovabrw_qty_by_guests( $product_id ) && $checkin_date === $order_item['checkin_date'] ) {
                                $numberof_adults     -= (int)$order_item['numberof_adults'];
                                $numberof_children   -= (int)$order_item['numberof_children'];
                                $numberof_babies     -= (int)$order_item['numberof_babies'];

                                // Get number of guests available
                                if ( $numberof_adults <= 0 && $numberof_children <= 0 && $numberof_babies <= 0 ) {
                                    $is_blocked = true;
                                    continue;
                                }

                                // Check min guests
                                if ( apply_filters( OVABRW_PREFIX.'check_min_guests', true, $product_id ) ) {
                                    if ( $numberof_adults < $min_adults || $numberof_children < $min_children || $numberof_babies < $min_babies ) {
                                        $is_blocked = true;
                                        continue;
                                    }
                                }
                            } else {
                                if ( !( $checkin_date >= $order_item['checkout_date'] || $checkout_date <= $order_item['checkin_date'] ) ) {
                                    $quantity -= (int)$order_item['quantity'];

                                    if ( $quantity <= 0 ) {
                                        $is_blocked = true;
                                        continue;
                                    }
                                }
                            }
                        }

                        // This day is blocked
                        if ( $is_blocked ) continue;
                    } // END Check Order

                    // Convert check-in date and check-out date
                    $checkin_date   = date( $date_format, $checkin_date );
                    $checkout_date  = date( $date_format, $checkout_date );
                    $return_dates[$checkin_date.'|'.$checkout_date] = sprintf( esc_html__( 'Desde %s hasta %s', 'ova-brw' ), $checkin_date, $checkout_date );

                    if ( count( $return_dates ) === $limited ) {
                        break;
                    }
                }
                // End Loop
                
                // Loading more option
                if ( count( $checkin_dates ) > $limited ) {
                    $k += $next;
                    $return_dates[$k] = esc_html__( 'más...', 'ova-brw' );
                }
            }
        }

        return apply_filters( OVABRW_PREFIX.'get_fixed_dates', $return_dates, $product_id );
    }
}

/**
 * Get booked dates from Cart
 */
if ( !function_exists( 'ovabrw_get_booked_dates_form_cart' ) ) {
    function ovabrw_get_booked_dates_form_cart( $product_id ) {
        // init
        $cart_booked = [];

        if ( $product_id && WC()->cart && !empty( WC()->cart->get_cart() ) ) {
            // WPML
            $product_ids = ovabrw_get_wpml_product_ids( $product_id );

            foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                $prod_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

                if ( in_array( $prod_id, $product_ids ) ) {
                    // Check-in date
                    $checkin_date = strtotime( ovabrw_get_meta_data( 'ovabrw_pickup_date', $cart_item ) );
                    if ( !$checkin_date ) continue;

                    // Check-out date
                    $checkout_date = strtotime( ovabrw_get_meta_data( 'ovabrw_pickoff_date', $cart_item ) );
                    if ( !$checkout_date ) continue;

                    // Cart quantity
                    $quantity = (int)ovabrw_get_meta_data( 'ovabrw_quantity', $cart_item );

                    // Get number of adults
                    $numberof_adults = (int)ovabrw_get_meta_data( 'ovabrw_adults', $cart_item );

                    // Get number of children
                    $numberof_children = (int)ovabrw_get_meta_data( 'ovabrw_childrens', $cart_item );
                    // Get number of babies
                    $numberof_babies = (int)ovabrw_get_meta_data( 'ovabrw_babies', $cart_item );

                    // Cart booked
                    $cart_booked[] = [
                        'checkin_date'      => $checkin_date,
                        'checkout_date'     => $checkout_date,
                        'quantity'          => $quantity,
                        'numberof_adults'   => $numberof_adults*$quantity,
                        'numberof_children' => $numberof_children*$quantity,
                        'numberof_babies'   => $numberof_babies*$quantity,
                    ];
                }
            }
        }

        return apply_filters( OVABRW_PREFIX.'get_booked_dates_form_cart', $cart_booked, $product_id );
    }
}

/**
 * Get booked dates from Order
 */
if ( !function_exists( 'ovabrw_get_booked_dates_from_order' ) ) {
    function ovabrw_get_booked_dates_from_order( $product_id ) {
        $order_booked = [];

        if ( $product_id ) {
            // Get status
            $status = brw_list_order_status();

            // Get order ids
            $order_ids = ovabrw_get_orders_by_product_id( $product_id, $status );

            if ( ovabrw_array_exists( $order_ids ) ) {
                // WPML
                $product_ids = ovabrw_get_wpml_product_ids( $product_id );

                foreach ( $order_ids as $k => $order_id ) {
                    // Get Order Detail by Order ID
                    $order = wc_get_order( $order_id );

                    // Get Meta Data type line_item of Order
                    $line_items = $order->get_items( apply_filters( 'woocommerce_purchase_order_item_types', 'line_item' ) );

                    foreach ( $line_items as $item_id => $item ) {
                        $prod_id = $item->get_product_id();

                        if ( in_array( $prod_id, $product_ids ) ) {
                            // Check-in date
                            $checkin_date = strtotime( $item->get_meta( 'ovabrw_pickup_date' ) );
                            if ( !$checkin_date ) continue;

                            // Check-out date
                            $checkout_date = strtotime( $item->get_meta( 'ovabrw_pickoff_date' ) );
                            if ( !$checkout_date ) continue;

                            // Quantity
                            $quantity = (int)$item->get_meta( 'ovabrw_quantity' );

                            // Number of adults
                            $numberof_adults = (int)$item->get_meta( 'ovabrw_adults' );

                            // Number of children
                            $numberof_children = (int)$item->get_meta( 'ovabrw_childrens' );

                            // Number of babies
                            $numberof_babies = (int)$item->get_meta( 'ovabrw_babies' );

                            // Order booked
                            $order_booked[] = [
                                'checkin_date'      => $checkin_date,
                                'checkout_date'     => $checkout_date,
                                'quantity'          => $quantity,
                                'numberof_adults'   => $numberof_adults*$quantity,
                                'numberof_children' => $numberof_children*$quantity,
                                'numberof_babies'   => $numberof_babies*$quantity
                            ];
                        }
                    }
                }
            }
        }

        return apply_filters( OVABRW_PREFIX.'get_booked_dates_from_order', $order_booked, $product_id );
    }
}

/**
 * Show insurance amount
 */
if ( !function_exists( 'ovabrw_show_insurance_amount' ) ) {
    function ovabrw_show_insurance_amount() {
        return apply_filters( OVABRW_PREFIX.'show_insurance_amount', 'yes' === ovabrw_get_option( 'show_insurance_amount', 'yes' ) ? true : false );
    }
}

/**
 * Get insurance fee name
 */
if ( !function_exists( 'ovabrw_get_insurance_fee_name' ) ) {
    function ovabrw_get_insurance_fee_name() {
        return apply_filters( OVABRW_PREFIX.'get_insurance_fee_name', esc_html__( 'Insurance fees', 'ova-brw' ) );
    }
}

/**
 * Get insurance fee key
 */
if ( !function_exists( 'ovabrw_get_insurance_fee_key' ) ) {
    function ovabrw_get_insurance_fee_key() {
        return apply_filters( OVABRW_PREFIX.'get_insurance_fee_key', sanitize_title( ovabrw_get_insurance_fee_name() ) );
    }
}

/**
 * Insurance tax enabled
 */
if ( !function_exists( 'ovabrw_insurance_tax_enabled' ) ) {
    function ovabrw_insurance_tax_enabled() {
        return apply_filters( OVABRW_PREFIX.'insurance_tax_enabled', ( wc_tax_enabled() && 'yes' === ovabrw_get_option( 'insurance_tax_enabled', 'no' ) ? true : false ) );
    }
}

/**
 * Insurance paid once
 */
if ( !function_exists( 'ovabrw_insurance_paid_once' ) ) {
    function ovabrw_insurance_paid_once() {
        return apply_filters( OVABRW_PREFIX.'insurance_paid_once', 'yes' === ovabrw_get_option( 'insurance_paid_once', 'no' ) ? true : false );
    }
}

/**
 * Get insurance tax class
 */
if ( !function_exists( 'ovabrw_get_insurance_tax_class' ) ) {
    function ovabrw_get_insurance_tax_class() {
        return apply_filters( OVABRW_PREFIX.'get_insurance_tax_class', '' );
    }
}

/**
 * Get insurance tax amount
 */
if ( !function_exists( 'ovabrw_get_insurance_tax_amount' ) ) {
    function ovabrw_get_insurance_tax_amount( $price = 0 ) {
        $tax_amount = 0;

        if ( ovabrw_insurance_tax_enabled() ) {
            $tax_rates  = WC_Tax::get_rates( ovabrw_get_insurance_tax_class() );
            $taxes      = WC_Tax::calc_exclusive_tax( $price, $tax_rates );
            $tax_amount += WC_Tax::get_tax_total( $taxes );
        }

        return apply_filters( OVABRW_PREFIX.'get_insurance_tax_amount', $tax_amount, $price );
    }
}

/**
 * Get insurance inclusive tax
 */
if ( !function_exists( 'ovabrw_get_insurance_inclusive_tax' ) ) {
    function ovabrw_get_insurance_inclusive_tax( $price = 0 ) {
        $tax_display = get_option( 'woocommerce_tax_display_cart' );

        if ( wc_tax_enabled() && 'incl' === $tax_display ) {
            $price += ovabrw_get_insurance_tax_amount( $price );
        }

        return apply_filters( OVABRW_PREFIX.'get_insurance_inclusive_tax', $price );
    }
}

/**
 * Get orders not remaining invoice
 */
if ( !function_exists( 'ovabrw_get_orders_not_remaining_invoice' ) ) {
    function ovabrw_get_orders_not_remaining_invoice() {
        // Order ids
        $order_ids = [];

        // Order status
        $order_status = brw_list_order_status();

        global $wpdb;
        if ( ovabrw_wc_custom_orders_table_enabled() ) {
            $order_ids = $wpdb->get_col("
                SELECT DISTINCT o.id
                FROM {$wpdb->prefix}wc_orders AS o
                LEFT JOIN {$wpdb->prefix}woocommerce_order_items AS oitems
                ON o.id = oitems.order_id
                LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS oitem_meta
                ON oitems.order_item_id = oitem_meta.order_item_id
                WHERE oitem_meta.meta_key = 'ovabrw_remaining_amount'
                AND oitem_meta.meta_value != 0
                AND o.status IN ( '" . implode( "','", $order_status ) . "' )
            ");
        } else {
            $order_ids = $wpdb->get_col("
                SELECT DISTINCT oitems.order_id
                FROM {$wpdb->prefix}woocommerce_order_items AS oitems
                LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS oitem_meta
                ON oitems.order_item_id = oitem_meta.order_item_id
                LEFT JOIN {$wpdb->posts} AS posts
                ON oitems.order_id = posts.ID
                WHERE posts.post_type = 'shop_order'
                AND oitem_meta.meta_key = 'ovabrw_remaining_amount'
                AND oitem_meta.meta_value != 0
                AND posts.post_status IN ( '" . implode( "','", $order_status ) . "' )
            ");
        }

        return apply_filters( OVABRW_PREFIX.'get_orders_not_remaining_invoice', $order_ids );
    }
}

/**
 * Datepicker global CSS
 */
if ( !function_exists( 'ovabrw_datepicker_global_css' ) ) {
    function ovabrw_datepicker_global_css() {
        // CSS
        $css = '';

        // Global
        $light_color    = get_theme_mod( 'light_color', '#999999' );
        $primary_color  = get_theme_mod( 'primary_color', '#FD4C5C' );

        $primary_background     = get_theme_mod( 'secondary_color', '#00BB98' );
        $color_streak           = apply_filters( OVABRW_PREFIX.'input_calendar_color_streak', '#FFFFFF' );
        $color_available        = get_theme_mod( 'text_color', '#444444' );
        $background_available   = apply_filters( OVABRW_PREFIX.'input_calendar_background_available', '#FFFFFF' );
        $color_disable          = apply_filters( OVABRW_PREFIX.'input_calendar_color_not_available', '#FFFFFF' );
        $background_disable     = get_theme_mod( 'primary_color', '#FD4C5C' );
        $color_booked           = apply_filters( OVABRW_PREFIX.'color_booked_date', '#FFFFFF' );
        $background_booked      = get_theme_mod( 'primary_color', '#FD4C5C' );
        

        $css .= "--ovabrw-primary-color:{$primary_color};";
        $css .= "--ovabrw-light-color:{$light_color};";

        $css .= "--ovabrw-primary-calendar:{$primary_background};";
        $css .= "--ovabrw-color-streak:{$color_streak};";
        $css .= "--ovabrw-available-color:{$color_available};";
        $css .= "--ovabrw-available-background:{$background_available};";
        $css .= "--ovabrw-disable-color:{$color_disable};";
        $css .= "--ovabrw-disable-background:{$background_disable};";
        $css .= "--ovabrw-booked-color:{$color_booked};";
        $css .= "--ovabrw-booked-background:{$background_booked};";

        return apply_filters( OVABRW_PREFIX.'datepicker_global_css', $css );
    }
}

/**
 * Get timepicker options
 */
if ( !function_exists( 'ovabrw_admin_timepicker_options' ) ) {
    function ovabrw_admin_timepicker_options() {
        return apply_filters( 'ovabrw_admin_timepicker_options', [
            'timeFormat'        => ovabrw_get_time_format(),
            'step'              => ovabrw_get_step_time(),
            'scrollDefault'     => '07:00',
            'forceRoundTime'    => true,
            'disableTextInput'  => true,
            'autoPickTime'      => true,
            'defaultStartTime'  => apply_filters( OVABRW_PREFIX.'default_start_time', '07:00' ),
            'defaultEndTime'    => apply_filters( OVABRW_PREFIX.'default_end_time', '07:00' ),
            'allowTimes'        => [],
            'allowStartTimes'   => apply_filters( OVABRW_PREFIX.'allow_start_time', [] ),
            'allowEndTimes'     => apply_filters( OVABRW_PREFIX.'allow_end_time', [] ),
            'lang'              => apply_filters( OVABRW_PREFIX.'admin_timepicker_options_lang', [
                'am'        => 'am',
                'pm'        => 'pm',
                'AM'        => 'AM',
                'PM'        => 'PM',
                'decimal'   => '.',
                'mins'      => 'mins',
                'hr'        => 'hr',
                'hrs'       => 'hrs',
                'pickUp'    => esc_html__( 'Pick-up', 'ova-brw' ),
                'dropOff'   => esc_html__( 'Drop-off', 'ova-brw' )
            ])
        ]);
    }
}

/**
 * Get datepicker options
 */
if ( !function_exists( 'ovabrw_admin_datepicker_options' ) ) {
    function ovabrw_admin_datepicker_options() {
        // Date format
        $date_format = ovabrw_get_date_format();

        // Min year, Max year
        $min_year = (int)apply_filters( OVABRW_PREFIX.'admin_datepicker_min_year', gmdate('Y') );
        $max_year = (int)apply_filters( OVABRW_PREFIX.'admin_datepicker_max_year', gmdate('Y')+3 );

        // Min date, Max date
        $min_date = $max_date = '';

        if ( $min_year ) {
            $min_date = gmdate( $date_format, strtotime( "$min_year-01-01" ) );
        }
        if ( $max_year ) {
            $december_date = new DateTime("$max_year-12-01");
            $december_date->modify('last day of this month');

            // Get max date
            $max_date = $december_date->format($date_format);
        }

        // Start date when calendar show
        $start_date = '';
        if ( $min_date && strtotime( $min_date ) > current_time( 'timestamp' ) ) {
            $start_date = $min_date;
        }

        // Language
        $language = apply_filters( OVABRW_PREFIX.'admin_datepicker_language', ovabrw_get_option_setting( 'calendar_language_general', 'en-GB' ) );
        if ( apply_filters( 'wpml_current_language', NULL ) ) { // WPML
            $language = apply_filters( 'wpml_current_language', NULL );
        } elseif ( function_exists('pll_current_language') ) { // Polylang
            $language = pll_current_language();
        }

        // Disable weekdays
        $disable_weekdays = [];

        if ( apply_filters( OVABRW_PREFIX.'admin_use_disable_weekdays', true ) ) {
            $disable_weekdays = ovabrw_get_option_setting( 'calendar_disable_week_day' );
            if ( '' !== $disable_weekdays ) $disable_weekdays = explode( ',', $disable_weekdays );
        }

        // Datepicker CSS
        $datepciker_css = [
            OVABRW_PLUGIN_URI.'assets/libs/easepick/easepick.min.css',
            OVABRW_PLUGIN_URI.'assets/css/datepicker/datepicker.css'
        ];

        return apply_filters( OVABRW_PREFIX.'admin_datepicker_options', [
            'css'           => apply_filters( OVABRW_PREFIX.'admin_datepicker_css', $datepciker_css ),
            'firstDay'      => (int)ovabrw_get_option_setting( 'calendar_first_day', 1 ),
            'lang'          => $language,
            'format'        => $date_format,
            'grid'          => 2,
            'calendars'     => 2,
            'zIndex'        => 999999999,
            'inline'        => false,
            'readonly'      => true,
            'header'        => apply_filters( OVABRW_PREFIX.'admin_datepicker_header', '' ),           
            'autoApply'     => true,
            'locale'        => apply_filters( OVABRW_PREFIX.'admin_datepicker_locale', [
                'cancel'    => esc_html__( 'Cancel', 'ova-brw' ),
                'apply'     => esc_html__( 'Apply', 'ova-brw' )
            ]),
            'AmpPlugin'     => apply_filters( OVABRW_PREFIX.'admin_datepicker_amp_plugin', [
                'dropdown'  => [
                    'months'    => true,
                    'years'     => true,
                    'minYear'   => $min_year ? $min_year : gmdate('Y'),
                    'maxYear'   => $max_year ? $max_year : gmdate('Y')+3
                ],
                'resetButton'   => true,
                'darkMode'      => false
            ]),
            'RangePlugin'   => apply_filters( OVABRW_PREFIX.'admin_datepicker_range_plugin', [
                'repick'    => false,
                'strict'    => true,
                'tooltip'   => true,
                'locale'    => [
                    'zero'  => '',
                    'one'   => esc_html__( 'day', 'ova-brw' ),
                    'two'   => '',
                    'many'  => '',
                    'few'   => '',
                    'other' => esc_html__( 'days', 'ova-brw' )
                ]
            ]),
            'LockPlugin'    => apply_filters( OVABRW_PREFIX.'admin_datepicker_lock_plugin', [
                'minDate'           => $min_date,
                'maxDate'           => $max_date,
                'minDays'           => '',
                'maxDays'           => '',
                'selectForward'     => false,
                'selectBackward'    => false,
                'presets'           => false,
                'inseparable'       => false
            ]),
            'PresetPlugin'  => apply_filters( OVABRW_PREFIX.'admin_datepicker_preset_plugin', [
                'position'      => 'left',
                'customLabels'  => [
                    'Today',
                    'Yesterday',
                    'Last 7 Days',
                    'Last 30 Days',
                    'This Month',
                    'Last Month'
                ],
                'customPreset'  => ovabrw_get_predefined_ranges()
            ]),
            'plugins' => apply_filters( OVABRW_PREFIX.'admin_datepicker_plugins', [
                'AmpPlugin',
                'RangePlugin',
                'LockPlugin',
                'PresetPlugin'
            ]),
            'disableWeekDays'   => apply_filters( OVABRW_PREFIX.'admin_datepicker_disable_weekdays', $disable_weekdays ),
            'disableDates'      => apply_filters( OVABRW_PREFIX.'admin_datepicker_disable_dates', [] ),
            'bookedDates'       => apply_filters( OVABRW_PREFIX.'admin_datepicker_booked_dates', [] ),
            'allowedDates'      => apply_filters( OVABRW_PREFIX.'admin_datepicker_allowed_dates', [] ),
            'startDate'         => apply_filters( OVABRW_PREFIX.'admin_datepicker_start_date', $start_date )
        ]);
    }
}

/**
 * Get datepicker options
 */
if ( !function_exists( 'ovabrw_get_datepicker_options' ) ) {
    function ovabrw_get_datepicker_options() {
        // Date format
        $date_format = ovabrw_get_date_format();

        // Time format
        $time_format = ovabrw_get_time_format();

        // Min year, Max year
        $min_year = (int)apply_filters( OVABRW_PREFIX.'datepicker_min_year', gmdate('Y') );
        $max_year = (int)apply_filters( OVABRW_PREFIX.'datepicker_max_year', gmdate('Y')+3 );

        // Get min date
        $min_date = gmdate( $date_format, current_time( 'timestamp' ) );
        if ( $min_year && $min_year > gmdate('Y') ) {
            $min_date = gmdate( $date_format, strtotime( "$min_year-01-01" ) );
        }

        // Get max date
        $max_date = '';
        if ( $max_year ) {
            $december_date = new DateTime("$max_year-12-01");
            $december_date->modify('last day of this month');

            // Get max date
            $max_date = $december_date->format($date_format);
        }

        // Start date when calendar show
        $start_date = '';
        if ( $min_date && strtotime( $min_date ) > current_time( 'timestamp' ) ) {
            $start_date = $min_date;
        }

        // Language
        $language = apply_filters( OVABRW_PREFIX.'datepicker_language', ovabrw_get_option_setting( 'calendar_language_general', 'en-GB' ) );
        if ( apply_filters( 'wpml_current_language', NULL ) ) { // WPML
            $language = apply_filters( 'wpml_current_language', NULL );
        } elseif ( function_exists('pll_current_language') ) { // Polylang
            $language = pll_current_language();
        }

        // Datepicker CSS
        $datepciker_css = [
            OVABRW_PLUGIN_URI.'assets/libs/easepick/easepick.min.css',
            OVABRW_PLUGIN_URI.'assets/css/datepicker/datepicker.css'
        ];

        return (array)apply_filters( OVABRW_PREFIX.'datepicker_options', [
            'css'           => apply_filters( OVABRW_PREFIX.'datepicker_css', $datepciker_css ),
            'firstDay'      => (int)ovabrw_get_option_setting( 'calendar_first_day', 1 ),
            'lang'          => $language,
            'format'        => $date_format,
            'grid'          => 2,
            'calendars'     => 2,
            'zIndex'        => 999999999,
            'inline'        => false,
            'readonly'      => true,
            'header'        => apply_filters( OVABRW_PREFIX.'datepicker_header', '' ),
            'autoApply'     => true,
            'locale'        => apply_filters( OVABRW_PREFIX.'datepicker_locale', [
                'cancel'    => esc_html__( 'Cancel', 'ova-brw' ),
                'apply'     => esc_html__( 'Apply', 'ova-brw' )
            ]),
            'AmpPlugin'     => apply_filters( OVABRW_PREFIX.'datepicker_amp_plugin', [
                'dropdown'  => [
                    'months'    => true,
                    'years'     => true,
                    'minYear'   => $min_year ? $min_year : gmdate('Y'),
                    'maxYear'   => $max_year ? $max_year : gmdate('Y')+3
                ],
                'resetButton'   => true,
                'darkMode'      => false
            ]),
            'RangePlugin'   => apply_filters( OVABRW_PREFIX.'datepicker_range_plugin', [
                'repick'    => false,
                'strict'    => true,
                'tooltip'   => true,
                'locale'    => [
                    'zero'  => '',
                    'one'   => esc_html__( 'day', 'ova-brw' ),
                    'two'   => '',
                    'many'  => '',
                    'few'   => '',
                    'other' => esc_html__( 'days', 'ova-brw' )
                ]
            ]),
            'LockPlugin'    => apply_filters( OVABRW_PREFIX.'datepicker_lock_plugin', [
                'minDate'           => $min_date,
                'maxDate'           => $max_date,
                'minDays'           => '',
                'maxDays'           => '',
                'selectForward'     => false,
                'selectBackward'    => false,
                'presets'           => true,
                'inseparable'       => apply_filters( OVABRW_PREFIX.'datepicker_inseparable', false )
            ]),
            'PresetPlugin'      => apply_filters( OVABRW_PREFIX.'datepicker_preset_plugin', [
                'position'      => 'left',
                'customLabels'  => [
                    esc_html__( 'Today', 'ova-brw' ),
                    esc_html__( 'Yesterday', 'ova-brw' ),
                    esc_html__( 'Last 7 Days', 'ova-brw' ),
                    esc_html__( 'Last 30 Days', 'ova-brw' ),
                    esc_html__( 'This Month', 'ova-brw' ),
                    esc_html__( 'Last Month', 'ova-brw' )
                ],
                'customPreset'  => ovabrw_get_predefined_ranges()
            ]),
            'plugins' => apply_filters( OVABRW_PREFIX.'datepicker_plugins', [
                'AmpPlugin',
                'RangePlugin',
                'LockPlugin'
            ]),
            'disableWeekDays'   => apply_filters( OVABRW_PREFIX.'datepicker_disable_weekdays', [] ),
            'disableDates'      => apply_filters( OVABRW_PREFIX.'datepicker_disable_dates', [] ),
            'bookedDates'       => apply_filters( OVABRW_PREFIX.'datepicker_booked_dates', [] ),
            'allowedDates'      => apply_filters( OVABRW_PREFIX.'datepicker_allowed_dates', [] ),
            'regularPrice'      => apply_filters( OVABRW_PREFIX.'datepicker_regular_prices', '' ),
            'dailyPrices'       => apply_filters( OVABRW_PREFIX.'datepicker_daily_prices', [] ),
            'specialPrices'     => apply_filters( OVABRW_PREFIX.'datepicker_special_prices', [] ),
            'startDate'         => $start_date
        ]);
    }
}

/**
 * Get product datepicker options
 */
if ( !function_exists( 'ovabrw_get_product_datepicker_options' ) ) {
    function ovabrw_get_product_datepicker_options( $product_id, $form = 'booking' ) {
        // Get datepicker options
        $datepicker = ovabrw_get_datepicker_options();
        if ( !$product_id ) return $datepicker;

        // Date format
        $date_format = ovabrw_get_date_format();

        // Min date
        $min_date = $datepicker['LockPlugin']['minDate'];
        if ( !$min_date || strtotime( $min_date ) < current_time( 'timestamp' ) ) {
            $min_date = gmdate( $date_format, current_time( 'timestamp' ) );
        }

        // Book before X hours today
        $before_x_hour = ovabrw_get_post_meta( $product_id, 'book_before_x_hours' );
        if ( strtotime( $before_x_hour ) && strtotime( $before_x_hour ) <= current_time( 'timestamp' ) ) {
            $min_date = gmdate( $date_format, strtotime( '+1 day' ) );
        }

        // Get preparation time
        $preparation_time = (int)ovabrw_get_post_meta( $product_id, 'preparation_time' );
        if ( $preparation_time ) {
            if ( $preparation_time == 1 ) {
                $min_date = gmdate( $date_format, strtotime( '+1 day' ) );
            } else {
                $strtotime  = current_time( 'timestamp' ) + $preparation_time*86400;
                $min_date   = gmdate( $date_format, $strtotime );
            }
        } // END if

        // Update min date & start date
        $datepicker['LockPlugin']['minDate']    = $min_date;
        $datepicker['startDate']                = $min_date;
        $datepicker['timestamp']                = time();

        // Disabled weekdays
        $disabled_weekdays = ovabrw_get_post_meta( $product_id, 'product_disable_week_day' );
        if ( '' == $disabled_weekdays ) {
            $disabled_weekdays = ovabrw_get_option_setting( 'calendar_disable_week_day' );
        }
        $disabled_weekdays = '' !== $disabled_weekdays ? explode( ',', $disabled_weekdays ) : '';
        if ( ovabrw_array_exists( $disabled_weekdays ) ) {
            $disabled_weekdays = array_map( 'trim', $disabled_weekdays );
            $datepicker['disableWeekDays'] = $disabled_weekdays;
        } // END

        // Disabled dates
        $disabled_dates = [];
        $disabled_start = ovabrw_get_post_meta( $product_id, 'untime_startdate' );
        $disabled_end   = ovabrw_get_post_meta( $product_id, 'untime_enddate' );
        if ( ovabrw_array_exists( $disabled_start ) && ovabrw_array_exists( $disabled_end ) ) {
            foreach ( $disabled_start as $i => $start_date ) {
                // Start date
                $start_date = strtotime( $start_date );
                if ( !$start_date ) continue;

                // End date
                $end_date = strtotime( ovabrw_get_meta_data( $i, $disabled_end ) );
                if ( !$end_date || $end_date < current_time( 'timestamp' ) ) continue;

                // Get between dates
                $between_dates = ovabrw_createDatefull( $start_date, $end_date, $date_format );
                if ( ovabrw_array_exists( $between_dates ) ) {
                    $datepicker['disableDates'] = ovabrw_array_merge_unique( $datepicker['disableDates'], $between_dates );
                }
            }
        }

        // Booked dates
        $booked_dates = ovabrw_get_booked_dates( $product_id );
        if ( ovabrw_array_exists( $booked_dates ) ) {
            $datepicker['bookedDates'] = ovabrw_array_merge_unique( $datepicker['bookedDates'], $booked_dates );
        }

        return apply_filters( OVABRW_PREFIX.'get_product_datepicker_options', $datepicker, $product_id, $form );
    }
}

/**
 * Get booked dates
 */
if ( !function_exists( 'ovabrw_get_booked_dates' ) ) {
    function ovabrw_get_booked_dates( $product_id = false, $order_status = [] ) {
        global $wpdb;

        // Order status
        if ( !ovabrw_array_exists( $order_status ) ) {
            $order_status = brw_list_order_status();
        }

        // init
        $booked_dates = $order_booked = $dates_guests = [];

        // Get quantity
        $quantity = absint( ovabrw_get_post_meta( $product_id, 'stock_quantity' ) );

        // Get min number of adults
        $min_adults = absint( ovabrw_get_post_meta( $product_id, 'adults_min' ) );

        // Get max number of adults
        $max_adults = absint( ovabrw_get_post_meta( $product_id, 'adults_max' ) );

        // Get min number of children
        $min_children = absint( ovabrw_get_post_meta( $product_id, 'childrens_min' ) );

        // Get max number of children
        $max_children = absint( ovabrw_get_post_meta( $product_id, 'childrens_max' ) );

        // Get min number of babies
        $min_babies = absint( ovabrw_get_post_meta( $product_id, 'babies_min' ) );

        // Get max number of babies
        $max_babies = absint( ovabrw_get_post_meta( $product_id, 'babies_max' ) );
        
        // Get duration
        $duration = ovabrw_get_post_meta( $product_id, 'duration_checkbox' );
        if ( !$duration ) {
            // Get array product ids when use WPML
            $wpml_product_ids = ovabrw_get_wpml_product_ids( $product_id );

            // Get order ids
            $order_ids = ovabrw_get_orders_by_product_id( $product_id, $order_status );

            // Loop
            foreach ( $order_ids as $key => $order_id ) {
                // Get Order Detail by Order ID
                $order = wc_get_order( $order_id );

                // Get Meta Data type line_item of Order
                $order_items = $order->get_items('line_item');
               
                // For Meta Data
                foreach ( $order_items as $item_id => $item ) {
                    if ( in_array( $item->get_product_id(), $wpml_product_ids ) ) {
                        if ( ovabrw_qty_by_guests( $product_id ) ) {
                            // Get check-in date
                            $checkin_date = strtotime( $item->get_meta( 'ovabrw_pickup_date' ) );
                            if ( !$checkin_date || $checkin_date < current_time( 'timestamp' ) ) {
                                continue;
                            }

                            // Get number of adults
                            $item_adults = absint( $item->get_meta( 'ovabrw_adults' ) );

                            // Get number of children
                            $item_children = absint( $item->get_meta( 'ovabrw_childrens' ) );

                            // Get number of babies
                            $item_babies = absint( $item->get_meta( 'ovabrw_babies' ) );

                            // Get quantity
                            $item_qty = absint( $item->get_meta( 'ovabrw_quantity' ) );
                            if ( !$item_qty ) $item_qty = 1;

                            // Get date format
                            $date_format = ovabrw_get_date_format();

                            // Get string date
                            $date = gmdate( $date_format, $checkin_date );
                            
                            if ( isset( $dates_guests[$date] ) ) {
                                if ( !$dates_guests[$date] ) continue;

                                $dates_guests[$date]['adults']      -= $item_adults * $item_qty;
                                $dates_guests[$date]['children']    -= $item_children * $item_qty;
                                $dates_guests[$date]['babies']      -= $item_babies * $item_qty;
                            } else {
                                $dates_guests[$date] = [
                                    'adults'    => $max_adults * $quantity - $item_adults * $item_qty,
                                    'children'  => $max_children * $quantity - $item_children * $item_qty,
                                    'babies'    => $max_babies * $quantity - $item_babies * $item_qty
                                ];
                            }

                            if ( ( !$dates_guests[$date]['adults'] || $dates_guests[$date]['adults'] < 0 ) && ( ! $dates_guests[$date]['children'] || $dates_guests[$date]['children'] < 0 ) && ( ! $dates_guests[$date]['babies'] || $dates_guests[$date]['babies'] < 0 ) ) {
                                array_push( $booked_dates, $date );
                            }

                            if ( apply_filters( OVABRW_PREFIX.'check_min_guests', true, $product_id ) && !in_array( $date, $booked_dates ) ) {
                                if ( $dates_guests[$date]['adults'] < $min_adults || $dates_guests[$date]['children'] < $min_children || $dates_guests[$date]['babies'] < $min_babies  ) {
                                    array_push( $booked_dates, $date );
                                }
                            }
                        } else {
                            // Get check-in date
                            $checkin_date = strtotime( $item->get_meta( 'ovabrw_pickup_date' ) );
                            if ( !$checkin_date ) continue;

                            // Get check-out date
                            $checkout_date = strtotime( $item->get_meta( 'ovabrw_pickoff_date' ) );
                            if ( !$checkout_date || $checkout_date < current_time( 'timestamp' ) ) {
                                continue;
                            }

                            // Get quantity
                            $order_quantity = absint( $item->get_meta( 'ovabrw_quantity' ) );
                            if ( !$order_quantity ) $order_quantity = 1;

                            // Loop
                            for ( $i = 0; $i < $order_quantity ; $i++ ) {
                                // Get between dates
                                $between_dates = ovabrw_push_disabled_dates( $checkin_date, $checkout_date, $product_id );

                                // Merge
                                $order_booked = array_merge_recursive( $order_booked, $between_dates );
                            } // END loop
                        }
                    } // END if
                } // END loop order items
            } // END loop order ids
        } // END if

        // Add disabled dates in order
        if ( ovabrw_array_exists( $order_booked ) ) {
            $order_booked = array_count_values( $order_booked );

            // Loop
            foreach ( $order_booked as $date => $count ) {
                if ( $count >= $quantity && !in_array( $date, $booked_dates ) ) {
                    array_push( $booked_dates, $date );
                }
            } // END loop
        }

        // Remove duplicate value
        $booked_dates = array_values( array_unique( $booked_dates ) );

        return apply_filters( OVABRW_PREFIX.'get_booked_dates', $booked_dates, $product_id, $order_status );
    }
}

/**
 * Get guest info enable
 */
if ( !function_exists( 'ovabrw_guest_info_enabled' ) ) {
    function ovabrw_guest_info_enabled() {
        return apply_filters( OVABRW_PREFIX.'guest_info_enabled', 'yes' === ovabrw_get_option( 'guest_info' ) ? true : false );
    }
}

/**
 * Get guest info data
 */
if ( !function_exists( 'ovabrw_get_guest_info_data' ) ) {
    function ovabrw_get_guest_info_data( $guest_name = '' ) {
        if ( !$guest_name ) return false;

        // Guest info data
        $guest_info = ovabrw_get_meta_data( $guest_name.'_info', $_POST );
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

                    foreach ( $guest_files['name'] as $k => $file_name ) {
                        if ( !$file_name ) continue;

                        // Files data
                        $files = [
                            'name'      => $file_name,
                            'full_path' => isset( $guest_files['full_path'][$k] ) ? $guest_files['full_path'][$k] : '',
                            'type'      => isset( $guest_files['type'][$k] ) ? $guest_files['type'][$k] : '',
                            'tmp_name'  => isset( $guest_files['tmp_name'][$k] ) ? $guest_files['tmp_name'][$k] : '',
                            'error'     => isset( $guest_files['error'][$k] ) ? $guest_files['error'][$k] : 0,
                            'size'      => isset( $guest_files['size'][$k] ) ? (int)$guest_files['size'][$k] : 0
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

        return apply_filters( OVABRW_PREFIX.'get_guest_info_data', $info_data, $guest_name );
    }
}

/**
 * Get guest info HTML
 */
if ( !function_exists( 'ovabrw_get_guest_info_html' ) ) {
    function ovabrw_get_guest_info_html( $product_id = false, $guest_info = [] ) {
        if ( !ovabrw_array_exists( $guest_info ) ) return;

        // init
        $results = [];

        // Guest options
        $guest_options = [
            'adult' => esc_html__( 'Adult', 'ova-brw' )
        ];

        // Show child
        if ( ovabrw_show_children( $product_id ) ) {
            $guest_options['child'] = esc_html__( 'Child', 'ova-brw' );
        }

        // Show baby
        if ( ovabrw_show_babies( $product_id ) ) {
            $guest_options['baby'] = esc_html__( 'Baby', 'ova-brw' );
        }

        foreach ( $guest_options as $name => $label ) {
            // Get guest data
            $guest_data = ovabrw_get_meta_data( $name, $guest_info );
            if ( !ovabrw_array_exists( $guest_data ) ) continue;

            ob_start(); ?>
            <div class="ovabrw-popup-guest-info" data-guest-name="<?php echo esc_attr( $name ); ?>">
                <span class="guest-info-text">
                    <?php esc_html_e( '(view information)', 'ova-brw' ); ?>
                </span>
                <div class="guest-info-wrap">
                    <div class="popup-guest-info">
                        <?php if ( is_admin() ): ?>
                            <span class="close-popup">
                                <i class="dashicons dashicons-no-alt"></i>
                            </span>
                        <?php else: ?>
                            <span class="close-popup">
                                <i class="ovaicon ovaicon-cancel"></i>
                            </span>
                        <?php endif; ?>
                        <div class="guest-info-content">
                            <?php foreach ( $guest_data as $k => $fields ):
                                $actived = '';
                                if ( 0 == $k ) $actived = ' active';
                            ?>
                                <div class="guest-info-item<?php echo esc_attr( $actived ); ?>">
                                    <div class="guest-info-header">
                                        <label>
                                            <?php echo sprintf( esc_html__( 'Guest %s', 'ova-brw' ), $k + 1 ); ?>
                                        </label>
                                        <i class="icomoon icomoon-caret-down" aria-hidden="true"></i>
                                    </div>
                                    <ul class="guest-info-body">
                                    <?php foreach ( $fields as $f_name => $f_item ):
                                        // Type
                                        $f_type = ovabrw_get_meta_data( 'type', $f_item );

                                        // Label
                                        $f_label = ovabrw_get_meta_data( 'label', $f_item );

                                        // Value
                                        $f_value = ovabrw_get_meta_data( 'value', $f_item );

                                        // Radio
                                        if ( 'radio' === $f_type || 'select' === $f_type ) {
                                            // Option name
                                            $f_value = ovabrw_get_meta_data( 'option_name', $f_item );
                                            if ( !$f_value ) continue;
                                        } elseif ( 'checkbox' === $f_type ) {
                                            $f_value = '';

                                            // Option names
                                            $opt_names = ovabrw_get_meta_data( 'option_name', $f_item );
                                            if ( !ovabrw_array_exists( $opt_names ) ) continue;

                                            foreach ( $opt_names as $i => $opt_name ) {
                                                if ( !$opt_name ) continue;

                                                $f_value .= $opt_name;
                                                if ( $i < count( $opt_names ) - 1 ) $f_value .= ', ';
                                            }
                                        } elseif ( 'file' === $f_type ) {
                                            // File URL
                                            $f_url = ovabrw_get_meta_data( 'url', $f_item );
                                            if ( !$f_url ) continue;

                                            $f_value = '<a href="'.esc_url( $f_url ).'" target="_blank">';
                                                $f_value .= basename( $f_url );
                                            $f_value .= '</a>';
                                        } elseif ( 'email' === $f_type ) {
                                            $email = $f_value;

                                            $f_value = '<a href="mailto:'.esc_attr( $email ).'">';
                                                $f_value .= esc_html( $email );
                                            $f_value .= '</a>';
                                        } elseif ( 'tel' === $f_type ) {
                                            // Phone string
                                            $phone_string = $f_value;

                                            // Get phone number
                                            $phone_number = ovabrw_get_phone_number( $f_value );

                                            $f_value = '<a href="tel:'.esc_attr( $phone_number ).'">';
                                                $f_value .= esc_html( $phone_string );
                                            $f_value .= '</a>';
                                        }

                                        if ( !$f_label ) continue;
                                    ?>
                                        <li>
                                            <span class="field-label">
                                                <?php echo esc_html( $f_label ).':'; ?>
                                            </span>
                                            <span class="field-val">
                                                <?php echo wp_kses_post( $f_value ); ?>
                                            </span>
                                        </li>
                                    <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php $results[$name] = ob_get_contents();
            ob_end_clean();
        }

        return apply_filters( OVABRW_PREFIX.'get_guest_info_html', $results, $product_id, $guest_info );
    }
}

/**
 * is cart shortcode
 */
if ( !function_exists( 'ovabrw_is_cart_shortcode' ) ) {
    function ovabrw_is_cart_shortcode() {
        return is_cart() && !Automattic\WooCommerce\Blocks\Utils\CartCheckoutUtils::is_cart_block_default();
    }
}

/**
 * is checkout shortcode
 */
if ( !function_exists( 'ovabrw_is_checkout_shortcode' ) ) {
    function ovabrw_is_checkout_shortcode() {
        return is_checkout() && !Automattic\WooCommerce\Blocks\Utils\CartCheckoutUtils::is_checkout_block_default();
    }
}