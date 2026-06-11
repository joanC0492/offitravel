<?php if ( !defined( 'ABSPATH' ) ) exit;

/**
 * dump & die
 */
if ( !function_exists( 'dd' ) ) {
    function dd( ...$args ) {
        echo '<pre>';
        var_dump( ...$args );
        echo '</pre>';
        die;
    }
}

/**
 * Check array exists
 */
if ( !function_exists( 'ovabrw_array_exists' ) ) {
	function ovabrw_array_exists( $arr ) {
		if ( ! empty( $arr ) && is_array( $arr ) ) {
			return true;
		}

		return false;
	}
}

/**
 * Random unique id
 */
if ( !function_exists( 'ovabrw_unique_id' ) ) {
	function ovabrw_unique_id( $id = '' ) {
		$unique_id = OVABRW_PREFIX.$id . '_' . time() . '_' . mt_rand();

		return apply_filters( OVABRW_PREFIX.'unique_id', $unique_id, $id );
	}
}

/**
 * Merge unique
 */
if ( !function_exists( 'ovabrw_array_merge_unique' ) ) {
	function ovabrw_array_merge_unique( ...$arrays ) {
		$valid_arrays = [];

		// Filter out non-array elements
	    foreach ( $arrays as $array ) {
	        if ( is_array( $array ) ) {
	            $valid_arrays[] = $array;
	        }
	    }

	    // Merge valid arrays
	    $merged_array = array_merge(...$valid_arrays );
	    $unique_array = array_unique( $merged_array );

	    // Reindex array keys
	    $unique_array = array_values( $unique_array );

	    return apply_filters( OVABRW_PREFIX.'array_merge_unique', $unique_array, ...$arrays );
	}
}

/**
 * Selected
 */
if ( !function_exists( 'ovabrw_selected' ) ) {
	function ovabrw_selected( $value = '', $options = [], $display = true ) {
		$result = '';

		if ( is_array( $options ) ) {
			$options = array_map( 'strval', $options );
			$result  = selected( in_array( (string)$value, $options, true ), true, $display );
		} else {
			$result = selected( $value, $options, $display );
		}

		if ( $display ) {
			echo esc_html( $result );
		}

		return apply_filters( OVABRW_PREFIX.'selected', $result, $value, $options, $display );
	}
}

/**
 * Checked
 */
if ( !function_exists( 'ovabrw_checked' ) ) {
	function ovabrw_checked( $value = '', $options = [], $display = true ) {
		$result = '';

		if ( is_array( $options ) ) {
			$options = array_map( 'strval', $options );
			$result  = checked( in_array( (string) $value, $options, true ), true, $display );
		} else {
			$result = checked( $value, $options, $display );
		}

		if ( $display ) {
			echo esc_html( $result );
		}

		return apply_filters( OVABRW_PREFIX.'checked', $result, $value, $options, $display );
	}
}

/**
 * Disabled
 */
if ( !function_exists( 'ovabrw_disabled' ) ) {
	function ovabrw_disabled( $value = '', $options = [], $display = true ) {
		$result = '';

		if ( is_array( $options ) ) {
			$options = array_map( 'strval', $options );
			$result  = disabled( in_array( (string)$value, $options, true ), true, $display );
		} else {
			$result = disabled( $value, $options, $display );
		}

		if ( $display ) {
			echo esc_html( $result );
		}

		return apply_filters( OVABRW_PREFIX.'disabled', $result, $value, $options, $display );
	}
}

/**
 * Recursive array replace \\
 */
if ( !function_exists( 'ovabrw_recursive_replace' ) ) {
    function ovabrw_recursive_replace( $find, $replace, $array ) {
        if ( !is_array( $array ) ) {
            return str_replace( $find, $replace, $array );
        }

        foreach ( $array as $key => $value ) {
            $array[$key] = ovabrw_recursive_replace( $find, $replace, $value );
        }

        return apply_filters( OVABRW_PREFIX.'recursive_replace', $array, $find, $replace );
    }
}

/**
 * Format date
 */
if ( !function_exists( 'ovabrw_format_date' ) ) {
	function ovabrw_format_date( $args = null ) {
		if ( !$args ) return $args;
		if ( !is_array( $args ) ) {
			return strtotime( $args ) ? $args : '';
		}

		foreach ( $args as $k => $v ) {
			$args[$k] = ovabrw_format_date( $v );
		}

		return apply_filters( OVABRW_PREFIX.'format_date', $args );
	}
}

/**
 * Format number
 */
if ( !function_exists( 'ovabrw_format_number' ) ) {
	function ovabrw_format_number( $args = null ) {
		if ( empty( $args ) ) return $args;
		if ( !is_array( $args ) ) {
			return absint( $args );
		}

		foreach ( $args as $k => $v ) {
			$args[$k] = ovabrw_format_number( $v );
		}

		return apply_filters( OVABRW_PREFIX.'format_number', $args );
	}
}

/**
 * Sanitize title
 */
if ( !function_exists( 'ovabrw_sanitize_title' ) ) {
	function ovabrw_sanitize_title( $args = [] ) {
		if ( empty( $args ) ) return $args;

		if ( !is_array( $args ) ) {
			return sanitize_title( $args );
		}

		foreach ( $args as $k => $v ) {
			$args[$k] = ovabrw_sanitize_title( $v );
		}

		return apply_filters( OVABRW_PREFIX.'sanitize_title', $args );
	}
}

/**
 * Format price
 */
if ( !function_exists( 'ovabrw_format_price' ) ) {
	function ovabrw_format_price( $args = [] ) {
		if ( empty( $args ) ) return $args;

		if ( !is_array( $args ) ) {
			return wc_format_decimal( $args );
		}

		foreach ( $args as $k => $v ) {
			$args[$k] = ovabrw_format_price( $v );
		}

		return apply_filters( OVABRW_PREFIX.'format_price', $args );
	}
}

/**
 * Get meta from data
 */
if ( !function_exists( 'ovabrw_get_meta_data' ) ) {
	function ovabrw_get_meta_data( $key = '', $args = [], $default = false ) {
		$value = '';

		// Check $args
		if ( !ovabrw_array_exists( $args ) ) $args = [];

		// Get value by key
		if ( $key !== '' && isset( $args[$key] ) && '' !== $args[$key] ) {
			$value = $args[$key];
		}

		// Set default
		if ( !$value && false !== $default ) {
			$value = $default;
		}

		return apply_filters( OVABRW_PREFIX.'get_meta_data', $value, $key, $args, $default );
	}
}

/**
 * Get option
 */
if ( !function_exists( 'ovabrw_get_option' ) ) {
	function ovabrw_get_option( $name, $default = false, $prefix = OVABRW_PREFIX ) {
		$value = '';

		// Get option value
		if ( $name ) {
			$value = get_option( $prefix.$name );
		}

		// Set defalt
		if ( '' == $value && $default !== false ) {
			$value = $default;
		}

		return apply_filters( OVABRW_PREFIX.'get_option', $value, $name, $default, $prefix );
	}
}

/**
 * Get option setting 
 */
if ( !function_exists( 'ovabrw_get_option_setting' ) ) {
	function ovabrw_get_option_setting( $name, $default = false, $prefix = OVABRW_PREFIX_OPTIONS ) {
		$value = '';

		// Get option value
		if ( $name ) {
			$value = get_option( $prefix.$name );
		}

		// Set defalt
		if ( '' == $value && $default !== false ) {
			$value = $default;
		}

		return apply_filters( OVABRW_PREFIX.'get_option_setting', $value, $name, $default, $prefix );
	}
}

/**
 * Get meta key
 */
if ( !function_exists( 'ovabrw_meta_key' ) ) {
	function ovabrw_meta_key( $key = '', $display = false ) {
        if ( $key ) $key = OVABRW_PREFIX.$key;

        if ( $display ) {
        	echo esc_attr( $key );
        }

        return apply_filters( OVABRW_PREFIX.'meta_key', $key );
    }
}

/**
 * Get post meta
 */
if ( !function_exists( 'ovabrw_get_post_meta' ) ) {
	function ovabrw_get_post_meta( $id = null, $name = '', $default = false ) {
		$value = '';

		if ( $id && $name ) {
			$value = get_post_meta( $id, OVABRW_PREFIX.$name, true );

			if ( empty( $value ) && $default !== false ) {
				$value = $default;
			}
		}

		return apply_filters( OVABRW_PREFIX.'get_post_meta', $value, $id, $name, $default );
	}
}

/**
 * Return value of setting
 */
if ( !function_exists( 'ovabrw_get_setting' ) ) {
	function ovabrw_get_setting( $setting ) {
		if ( '' === trim( $setting ) ) return;
		return apply_filters( OVABRW_PREFIX.'get_setting', $setting );
	}
}

/**
 * Get date format
 */
if ( !function_exists( 'ovabrw_get_date_format' ) ) {
	function ovabrw_get_date_format() {
		return apply_filters( OVABRW_PREFIX.'get_date_format_hook', ovabrw_get_option_setting( 'booking_form_date_format', 'd-m-Y' ) );
	}
}

/**
 * Get date format placeholder
 */
if ( !function_exists( 'ovabrw_get_placeholder_date' ) ) {
	function ovabrw_get_placeholder_date() {
		$placeholder = '';

		// Get date format
		$date_format = ovabrw_get_date_format();

		if ( 'Y-m-d' === $date_format ) {
			$placeholder = esc_html__( 'YYYY-MM-DD', 'ova-brw' );
		} elseif ( 'm/d/Y' === $date_format ) {
			$placeholder = esc_html__( 'MM/DD/YYYY', 'ova-brw' );
		} elseif ( 'Y/m/d' === $date_format ) {
			$placeholder = esc_html__( 'YYYY/MM/DD', 'ova-brw' );
		} else {
			$placeholder = esc_html__( 'DD-MM-YYYY', 'ova-brw' );
		}

		return apply_filters( OVABRW_PREFIX.'get_placeholder_date', $placeholder );
	}
}

/**
 * Get time format
 */
if ( !function_exists( 'ovabrw_get_time_format' ) ) {
	function ovabrw_get_time_format() {
		return apply_filters( OVABRW_PREFIX.'get_time_format_hook', ovabrw_get_option_setting( 'booking_form_time_format', 'H:i' ) );
	}
}

/**
 * Get date format placeholder
 */
if ( !function_exists( 'ovabrw_get_time_format_placeholder' ) ) {
	function ovabrw_get_time_format_placeholder() {
		$placeholder = '';

		// Get time format
		$time_format = ovabrw_get_time_format();

		switch ( $time_format ) {
			case 'H:i':
				$placeholder = esc_html__( 'H:i', 'ova-brw' );
				break;
			case 'h:i':
				$placeholder = esc_html__( 'h:i', 'ova-brw' );
				break;
			case 'h:i a':
				$placeholder = esc_html__( 'h:i a', 'ova-brw' );
				break;
			case 'h:i A':
				$placeholder = esc_html__( 'h:i A', 'ova-brw' );
				break;
			case 'G:i':
				$placeholder = esc_html__( 'G:i', 'ova-brw' );
				break;
			case 'g:i':
				$placeholder = esc_html__( 'g:i', 'ova-brw' );
				break;
			case 'g:i a':
				$placeholder = esc_html__( 'g:i a', 'ova-brw' );
				break;
			case 'g:i A':
				$placeholder = esc_html__( 'g:i A', 'ova-brw' );
				break;
			default:
				$placeholder = $time_format;
				break;
		}

		return apply_filters( OVABRW_PREFIX.'get_time_format_placeholde', $placeholder );
	}
}

/**
 * Get date time format
 */
if ( !function_exists( 'ovabrw_get_datetime_format' ) ) {
	function ovabrw_get_datetime_format() {
		return apply_filters( OVABRW_PREFIX.'get_datetime_format_hook', ovabrw_get_date_format() . ' ' . ovabrw_get_time_format() );
	}
}

/**
 * Get date time format placeholder
 */
if ( !function_exists( 'ovabrw_get_datetime_format_placeholder' ) ) {
	function ovabrw_get_datetime_format_placeholder() {
		return apply_filters( OVABRW_PREFIX.'get_datetime_format_placeholder', ovabrw_get_placeholder_date() . ' ' . ovabrw_get_time_format_placeholder() );
	}
}

/**
 * Get time step
 */
if ( !function_exists( 'ovabrw_get_step_time' ) ) {
	function ovabrw_get_step_time() {
		return apply_filters( OVABRW_PREFIX.'get_step_time_hook', ovabrw_get_option_setting( 'step_time', 5 ) );
	}
}

/**
 * Get locate template
 */
if ( !function_exists( 'ovabrw_locate_template' ) ) {
	function ovabrw_locate_template( $template_name = '', $template_path = '', $default_path = '' ) {
		// Set variable to search in ovabrw-templates folder of theme.
		if ( !$template_path ) $template_path = 'ovabrw-templates/';

		// Set default plugin templates path.
		if ( !$default_path ) $default_path = OVABRW_PLUGIN_PATH . 'ovabrw-templates/';

		// Search template file in theme folder.
		$template = locate_template([ $template_path . $template_name ]);

		// Get plugins template file.
		if ( !$template ) $template = $default_path . $template_name;

		return apply_filters( OVABRW_PREFIX.'locate_template', $template, $template_name, $template_path, $default_path );
	}
}

/**
 * Include template
 */
if ( !function_exists( 'ovabrw_get_template' ) ) {
	function ovabrw_get_template( $template_name = '', $args = [], $tempate_path = '', $default_path = '' ) {
		// Extract args
		if ( ovabrw_array_exists( $args ) ) extract( $args );

		// Get template path
		$template_file = ovabrw_locate_template( $template_name, $tempate_path, $default_path );
		if ( !file_exists( $template_file ) ) {
			_doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', $template_file ), '1.0.0' );
			return;
		}

		include $template_file;
	}
}

/**
 * Get custom checkout fields
 */
if ( !function_exists( 'ovabrw_get_list_field_checkout' ) ) {
	function ovabrw_get_list_field_checkout( $product_id ) {
		if ( !$product_id ) return [];

		// init
		$cckf = [];

		// Get all ccfl
		$all_cckf = ovabrw_get_option( 'booking_form', [] );

		// Get product cckf type
		$product_cckf_type = ovabrw_get_post_meta( $product_id, 'manage_custom_checkout_field' );
		if ( 'no' === $product_cckf_type ) {
			return apply_filters( OVABRW_PREFIX.'get_product_cckf', $cckf, $product_id );
		}

		// Term id
		$term_id = '';

		// Get product category
		$product_categories = wp_get_post_terms( $product_id, 'product_cat' );
		if ( !is_wp_error( $product_categories ) && ovabrw_array_exists( $product_categories ) ) {
			$term_id = $product_categories[0]->term_id;
		}

		// Get category cckf
		$cat_cckf = $term_id ? get_term_meta( $term_id, 'ovabrw_custom_checkout_field', true ) : '';

		// Get category cckf type
		$cate_cckf_type = $term_id ? get_term_meta( $term_id, 'ovabrw_choose_custom_checkout_field', true ) : '';

		if ( 'new' === $product_cckf_type ) {
			// Get product cckf
			$product_cckf = ovabrw_get_post_meta( $product_id, 'product_custom_checkout_field' );
			if ( $product_cckf ) $product_cckf = explode( ',', $product_cckf );
			if ( ovabrw_array_exists( $product_cckf ) ) {
				$product_cckf = array_map( 'trim', $product_cckf );
			}
			if ( ovabrw_array_exists( $product_cckf ) ) {
				foreach ( $product_cckf as $field_name ) {
					if ( ovabrw_get_meta_data( $field_name, $all_cckf ) ) {
						$cckf[$field_name] = $all_cckf[$field_name];
					}
				}
			}
		} elseif ( 'all' === $cate_cckf_type ) {
			$cckf = $all_cckf;
		} elseif ( 'special' === $cate_cckf_type ) {
			if ( ovabrw_array_exists( $cat_cckf ) ) {
				foreach ( $cat_cckf as $field_name ) {
					if ( ovabrw_get_meta_data( $field_name, $all_cckf ) ) {
						$cckf[$field_name] = $all_cckf[$field_name];
					}
				}
			}
		} else {
			$cckf = $all_cckf;
		}

		return apply_filters( OVABRW_PREFIX.'get_product_cckf', $cckf, $product_id );
	}
}

/**
 * Get order status
 */
if ( !function_exists( 'brw_list_order_status' ) ) {
	function brw_list_order_status() {
		return apply_filters( 'brw_list_order_status', [ 'wc-completed', 'wc-processing' ] );
	}
}

/**
 * Get product total stock
 */
if ( !function_exists( 'ovabrw_get_total_stock' ) ) {
	function ovabrw_get_total_stock( $product_id ) {
	    // Get product quantity
		$product_quantity = (int)ovabrw_get_post_meta( $product_id, 'stock_quantity' );
		if ( !$product_quantity ) $product_quantity = 1;

		return apply_filters( OVABRW_PREFIX.'get_product_quantity', $product_quantity, $product_id );
	}
}

/**
 * Get dates between
 */
if ( !function_exists( 'ovabrw_createDatefull' ) ) {
	function ovabrw_createDatefull( $start = '', $end = '', $format = 'Y-m-d' ) {
	    $dates = [];

	    while ( $start <= $end ) {
	        array_push( $dates, date( $format, $start ) );
	        $start += 86400;
	    }

	    return apply_filters( OVABRW_PREFIX.'get_between_dates', $dates, $start, $end, $format );
	} 
}

/**
 * Get number dates between
 */
if ( !function_exists( 'total_between_2_days' ) ) {
	function total_between_2_days( $start, $end ) {
    	return apply_filters( OVABRW_PREFIX.'get_numberof_between_days', floor( abs( strtotime( $end ) - strtotime( $start ) ) / 86400 ), $start, $end );
	}
}

/**
 * Get product ID with WPML
 */
if ( !function_exists( 'ovabrw_get_wpml_product_ids' ) ) {
	function ovabrw_get_wpml_product_ids( $id_original ) {
		// init
		$translated_ids = [];

		// Get plugin active
		$active_plugins = get_option( 'active_plugins' );

		// Polylang
		if ( in_array ( 'polylang/polylang.php', $active_plugins ) || in_array ( 'polylang-pro/polylang.php', $active_plugins ) ) {
				$languages = pll_languages_list();
				if ( !isset( $languages ) ) return;

				foreach ( $languages as $lang ) {
					$translated_ids[] = pll_get_post( $id_original, $lang );
				}
		} elseif ( in_array ( 'sitepress-multilingual-cms/sitepress.php', $active_plugins ) ) { // WPML
			global $sitepress;
			if ( !isset( $sitepress ) ) return;
			
			// Get trid
			$trid = $sitepress->get_element_trid( $id_original, 'post_product' );

			// Get translations
			$translations = $sitepress->get_element_translations( $trid, 'product' );
			if ( ovabrw_array_exists( $translations ) ) {
				foreach ( $translations as $lang => $translation ) {
				    $translated_ids[] = $translation->element_id;
				}
			}
		} else {
			$translated_ids[] = $id_original;
		}

		return apply_filters( OVABRW_PREFIX.'multiple_languages', $translated_ids, $id_original );
	}
}

/**
 * Get Pick up date from URL in Product detail
 */
if ( !function_exists( 'ovabrw_get_current_date_from_search' ) ) {
	function ovabrw_get_current_date_from_search( $type = 'pickup_date', $product_id = false ) {
		// init
		$date_string = '';

		// Get date from URL
		$date = '';
		if ( 'pickup_date' === $type ) {
			$date = strtotime( ovabrw_get_meta_data( 'pickup_date', $_GET ) );
		} else if ( $type == 'dropoff_date' ) {
			$date = strtotime( ovabrw_get_meta_data( 'dropoff_date', $_GET ) );
		}

		// Convert date
		if ( $date ) $date_string = gmdate( ovabrw_get_date_format(), $date );

		return apply_filters( OVABRW_PREFIX.'get_current_date_from_search', $date_string, $type, $product_id );
	}
}

/**
 * Get all custom taxonomy display in listing of product
 */
if ( !function_exists( 'get_all_cus_tax_dis_listing' ) ) {
	function get_all_cus_tax_dis_listing( $product_id ) {
		// init
		$all_cus_choosed = $all_cus_choosed_tmp = [];

		// Get all categories of this product
		$categories = get_the_terms( $product_id, 'product_cat' );
		if ( ovabrw_array_exists( $categories ) ) {
			foreach ( $categories as $key => $value ) {
				$cat_id = $value->term_id;

				// Get custom tax display in category
				$custom_tax = get_term_meta( $cat_id, 'ovabrw_custom_tax', true );
				if ( ovabrw_array_exists( $custom_tax ) ) {
					foreach ( $custom_tax as $slug_tax) {
						// Get value of terms in product
						$terms = get_the_terms( $product_id, $slug_tax );

						// Get option: custom taxonomy
						$custom_tax_opt = ovabrw_get_option( 'custom_taxonomy', '' );
						$show_listing_status = 'no';

						if ( $custom_tax_opt ) {
							foreach ( $custom_tax_opt as $slug => $value ) {
								if ( $slug_tax == $slug && 'on' === ovabrw_get_meta_data( 'show_listing', $value ) ) {
									$show_listing_status = 'yes';
									break;
								}
							}
						}

						if ( $terms && 'yes' === $show_listing_status ) {
							foreach ( $terms as $term ) {
								if ( !in_array( $slug_tax, $all_cus_choosed_tmp ) ) {
									// Assign array temp to check exist
									array_push( $all_cus_choosed_tmp, $slug_tax);
									array_push( $all_cus_choosed, [
										'slug' => $slug_tax,
										'name' => $term->name
									]);
								}
							}
						}
					}
				}
			}
		}

		return apply_filters( OVABRW_PREFIX.'get_all_cus_tax_dis_listing', $all_cus_choosed, $product_id );
	}
}

/**
 * Get custom taxonomy of an product
 */
if ( !function_exists( 'ovabrw_get_taxonomy_choosed_product' ) ) {
	function ovabrw_get_taxonomy_choosed_product( $product_id ) {
		// Custom taxonomies choosed in post
		$all_cus_tax = $exist_cus_tax = [];
		
		// Get Category of product
		$cats = get_the_terms( $product_id, 'product_cat' );

		// Depend category
		$depend_category = ovabrw_get_option_setting( 'search_show_tax_depend_cat', 'no' );

		if ( 'yes' == $depend_category ) {
			if ( !is_wp_error( $cats ) && ovabrw_array_exists( $cats ) ) {
				foreach ( $cats as $key => $cat ) {
					// Get custom taxonomy display in category
					$ovabrw_custom_tax = get_term_meta( $cat->term_id, 'ovabrw_custom_tax', true );	
					if ( ovabrw_array_exists( $ovabrw_custom_tax ) ) {
						foreach ( $ovabrw_custom_tax as $key => $value ) {
							array_push( $exist_cus_tax, $value );
						}	
					}
				}
			}

			if ( ovabrw_array_exists( $exist_cus_tax ) ) {
				foreach ( $exist_cus_tax as $key => $value ) {
					$cus_tax_terms = get_the_terms( $product_id, $value );

					if ( !is_wp_error( $cus_tax_terms ) && ovabrw_array_exists( $cus_tax_terms ) ) {
						foreach ( $cus_tax_terms as $key => $value ) {
							$list_fields = ovabrw_get_option( 'custom_taxonomy', [] );

							if ( ovabrw_array_exists( $list_fields ) ) {
			                    foreach ( $list_fields as $key => $field ) {
			                    	if ( is_object( $value ) && $value->taxonomy == $key ) {
			                    		if ( array_key_exists( $key, $all_cus_tax ) ) {
			                    			if ( !in_array( $value->name, $all_cus_tax[$key]['value'] ) ) {
			                    				array_push( $all_cus_tax[$key]['value'], $value->name );	
			                    			}
			                    		} else {
		                    				if ( isset( $field['label_frontend'] ) && $field['label_frontend'] ) {
		                    					$all_cus_tax[$key]['name'] = $field['label_frontend'];	
		                    				} else {
		                    					$all_cus_tax[$key]['name'] = $field['name'];	
		                    				}

		                    				$all_cus_tax[$key]['value'] = [ $value->name ];
			                    		}
			                    		break;
			                    	}
			                    } // END foreach
			                } // END if
						} // END foreach
					} // END if
				}
			}
		} else {
			$list_fields = ovabrw_get_option( 'custom_taxonomy', [] );

			if ( ovabrw_array_exists( $list_fields ) ) {
				foreach ( $list_fields as $key => $field ) {
					$terms = get_the_terms( $product_id, $key );
					if ( !is_wp_error( $terms ) && ovabrw_array_exists( $terms ) ) {
						foreach ( $terms as $value ) {
							if ( is_object( $value ) ) {
								if ( array_key_exists( $key, $all_cus_tax ) ) {
									if ( !in_array( $value->name, $all_cus_tax[$key]['value'] ) ) {
			            				array_push($all_cus_tax[$key]['value'], $value->name);	
			            			}
								} else {
									if ( isset( $field['label_frontend'] ) && $field['label_frontend'] ) {
			        					$all_cus_tax[$key]['name'] = $field['label_frontend'];	
			        				} else {
			        					$all_cus_tax[$key]['name'] = $field['name'];
			        				}

									$all_cus_tax[$key]['value'] = [ $value->name ];
								}
							}
						}
					}
				}
			}
		}

		return apply_filters( OVABRW_PREFIX.'get_taxonomy_choosed_product', $all_cus_tax, $product_id );
	}
}

/**
 * Get product template
 */
if ( !function_exists( 'ovabrw_get_product_template' ) ) {
	function ovabrw_get_product_template( $product_id ) {
		// Get default template
		$template = ovabrw_get_option_setting( 'template_elementor_template', 'default' );
		if ( !$product_id ) {
			return apply_filters( OVABRW_PREFIX.'get_product_template', $template, $product_id );
		}

		// Get product template
		$product_template = absint( ovabrw_get_post_meta( $product_id, 'product_template' ) );
		if ( $product_template ) {
			return apply_filters( OVABRW_PREFIX.'get_product_template', $product_template, $product_id );
		}

		// Get product
		$products = wc_get_product( $product_id );

		// Get category
		$categories = $products->get_category_ids();
		if ( ovabrw_array_exists( $categories ) ) {
			// Get first category
	        $term_id = reset( $categories );

	        // Get template by category
	        $template_by_category = get_term_meta( $term_id, 'ovabrw_product_templates', true );

	        if ( $template_by_category && 'global' !== $template_by_category ) {
	        	$template = $template_by_category;
	        }
	    }

		return apply_filters( OVABRW_PREFIX.'get_product_template', $template, $product_id );
	}
}

/**
 * Check key in array
 */
if ( !function_exists( 'ovabrw_check_array' ) ) {
	function ovabrw_check_array( $args, $key ) {
		if ( !empty( $args ) && is_array( $args ) ) {
			if ( isset( $args[$key] ) && '' != $args[$key] ) {
				return true;
			}
		}

		return false;
	}
}

/**
 * recursive price
 * @param  mixed $args
 * @return mixed
 */
if ( !function_exists( 'ovabrw_recursive_price' ) ) {
	function ovabrw_recursive_price( $args = [] ) {
		if ( empty( $args ) ) return $args;
		if ( !is_array( $args ) ) {
			return wc_format_decimal( $args );
		}

		// Loop
		foreach ( $args as $k => $v ) {
			$args[$k] = ovabrw_recursive_price( $v );
		} // END

		return apply_filters( OVABRW_PREFIX.'recursive_price', $args );
	}
}

/**
 * Get Price - Multi Currency
 */
if ( !function_exists( 'ovabrw_wc_price' ) ) {
	function ovabrw_wc_price( $price = null, $args = [], $convert = true ) {
		// New price
		$new_price = ovabrw_convert_price( $price, $args, $convert );
		
		return apply_filters( OVABRW_PREFIX.'wc_price', wc_price( $new_price, $args ), $price, $args, $convert );
	}
}

/**
 * Convert Price - Multi Currency
 */
if ( !function_exists( 'ovabrw_convert_price' ) ) {
	function ovabrw_convert_price( $price = null, $args = [], $convert = true ) {
		// New price
		$new_price = (float)$price;

		// Get current currency
		$current_currency = ovabrw_get_meta_data( 'currency', $args );
		if ( !$current_currency ) $current_currency = false;

		// CURCY - Multi Currency for WooCommerce
		if ( is_plugin_active( 'woo-multi-currency/woo-multi-currency.php' ) || is_plugin_active( 'woocommerce-multi-currency/woocommerce-multi-currency.php' ) ) {
			$new_price = wmc_get_price( $price, $current_currency );
		} elseif ( is_plugin_active( 'woocommerce-multilingual/wpml-woocommerce.php' ) ) { // // WooCommerce Multilingual & Multicurrency
			if ( $convert ) {
				// WPML multi currency
	    		global $woocommerce_wpml;

	    		if ( $woocommerce_wpml && is_object( $woocommerce_wpml ) ) {
	    			if ( wp_doing_ajax() ) add_filter( 'wcml_load_multi_currency_in_ajax', '__return_true' );

			        $multi_currency     = $woocommerce_wpml->get_multi_currency();
			        $currency_options   = $woocommerce_wpml->get_setting( 'currency_options' );
			        $WMCP   			= new WCML_Multi_Currency_Prices( $multi_currency, $currency_options );
			        $new_price  		= $WMCP->convert_price_amount( $price, $current_currency );
			    }
			}
		}
		
		return apply_filters( OVABRW_PREFIX.'convert_price', $new_price, $price, $args, $convert );
	}
}

/**
 * Convert Price in Admin - Multi Currency
 */
if ( !function_exists( 'ovabrw_convert_price_in_admin' ) ) {
	function ovabrw_convert_price_in_admin( $price = null, $currency_code = '' ) {
		// New price
		$new_price = (float)$price;

		if ( is_admin() && ( is_plugin_active( 'woo-multi-currency/woo-multi-currency.php' ) || is_plugin_active( 'woocommerce-multi-currency/woocommerce-multi-currency.php' ) ) ) {
			$setting = '';
			
			if ( is_plugin_active( 'woo-multi-currency/woo-multi-currency.php' ) ) {
				$setting = WOOMULTI_CURRENCY_F_Data::get_ins();
			}

			if ( is_plugin_active( 'woocommerce-multi-currency/woocommerce-multi-currency.php' ) ) {
				$setting = WOOMULTI_CURRENCY_Data::get_ins();
			}

			if ( !empty( $setting ) && is_object( $setting ) ) {
				// Get list currencies
				$selected_currencies = $setting->get_list_currencies();

				// Get current currency
				$current_currency = $setting->get_current_currency();

				if ( !$currency_code || $currency_code === $current_currency ) {
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

		return apply_filters( OVABRW_PREFIX.'convert_price_in_admin', $new_price, $price, $currency_code );
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
        if ( !$number ) return false;

        $hours      = floor( (float)$number );
        $minutes    = round( ( $number - $hours ) * 60 );

        return apply_filters( 'ovabrw_convert_number_to_minutes', absint( $minutes ), $number );
    }
}

/**
 * Get product price from database
 */
if ( !function_exists( 'ovabrw_mcml_get_product_price' ) ) {
	function ovabrw_wcml_get_product_price( $product_id, $meta_key ) {
		// init
		$price = 0;

		if ( !$product_id || !$meta_key ) return $price;
		if ( is_plugin_active( 'woocommerce-multilingual/wpml-woocommerce.php' ) ) {
			global $wpdb;

        	$price = $wpdb->get_var( "SELECT meta_value FROM $wpdb->postmeta WHERE post_id = $product_id AND meta_key = '$meta_key'" );
		}

		return apply_filters( OVABRW_PREFIX.'wcml_get_product_price', (float)$price, $product_id, $meta_key );
	}
}

/**
 * Check High-Performance Order Storage for Woocommerce
 */
if ( !function_exists( 'ovabrw_wc_custom_orders_table_enabled' ) ) {
	function ovabrw_wc_custom_orders_table_enabled() {
		if ( 'yes' === get_option( 'woocommerce_custom_orders_table_enabled', 'no' ) ) {
			return true;
		}

		return false;
	}
}

/**
 * Loading reCAPTCHA
 */
if ( !function_exists( 'ovabrw_loading_reCAPTCHA' ) ) {
	function ovabrw_loading_reCAPTCHA() {
		// reCAPTCHA
		if ( 'yes' === ovabrw_get_option_setting( 'recapcha_enable', 'no' ) && apply_filters( 'ovabrw_loading_reCAPTCHA', true ) ) {
			// Get recaptcha type
			$recaptcha_type = ovabrw_get_recaptcha_type();

			// Get site key
			$site_key  = ovabrw_get_recaptcha_site_key();

			wp_enqueue_script( 'ovabrw_recapcha_loading', OVABRW_PLUGIN_URI.'assets/js/frontend/ova-brw-recaptcha.js', [], false, false );
			wp_localize_script( 'ovabrw_recapcha_loading', 'ovabrw_recaptcha', [
				'site_key' 	=> $site_key,
				'form' 		=> ovabrw_get_option_setting( 'recapcha_form', '' )
			]);

			if ( 'v3' === $recaptcha_type ) {
				wp_enqueue_script( 'ovabrw_recaptcha', 'https://www.google.com/recaptcha/api.js?onload=ovabrwLoadingReCAPTCHAv3&render='.$site_key, [], false, false );
			} else {
				wp_enqueue_script( 'ovabrw_recaptcha', 'https://www.google.com/recaptcha/api.js?onload=ovabrwLoadingReCAPTCHAv2&render=explicit', [], false, false );
			}
		}
	}
}

/**
 * Get reCAPTCHA type
 */
if ( !function_exists( 'ovabrw_get_recaptcha_type' ) ) {
	function ovabrw_get_recaptcha_type() {
		return ovabrw_get_option_setting( 'recapcha_type', 'v3' );
	}
}

/**
 * Get reCAPTCHA site key
 */
if ( !function_exists( 'ovabrw_get_recaptcha_site_key' ) ) {
	function ovabrw_get_recaptcha_site_key() {
		if ( 'v3' === ovabrw_get_recaptcha_type() ) {
			return ovabrw_get_option_setting( 'recapcha_v3_site_key', '' );
		} else {
			return ovabrw_get_option_setting( 'recapcha_v2_site_key', '' );
		}
	}
}

/**
 * Get reCAPTCHA secret key
 */
if ( !function_exists( 'ovabrw_get_recaptcha_secret_key' ) ) {
	function ovabrw_get_recaptcha_secret_key() {
		if ( 'v3' === ovabrw_get_recaptcha_type() ) {
			return ovabrw_get_option_setting( 'recapcha_v3_secret_key', '' );
		} else {
			return ovabrw_get_option_setting( 'recapcha_v2_secret_key', '' );
		}
	}
}

/**
 * Get reCAPTCHA get client IP
 */
if ( !function_exists( 'ovabrw_get_client_ip' ) ) {
	function ovabrw_get_client_ip() {
		if ( !empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} elseif ( !empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = $_SERVER['REMOTE_ADDR'];
		} elseif ( !empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} else {
			$ip = '0.0.0.0';
		}

		return apply_filters( OVABRW_PREFIX.'get_client_ip', $ip );
	}
}

/**
 * Get reCAPTCHA host
 */
if ( !function_exists( 'ovabrw_get_recaptcha_host' ) ) {
	function ovabrw_get_recaptcha_host() {
		// init
		$host = '';

		// Get url
		$url = parse_url( site_url() );
		if ( ovabrw_get_meta_data( 'host', $url ) ) {
			$host = $url['host'];
		}

		return apply_filters( OVABRW_PREFIX.'get_recaptcha_host', $host );
	}
}

/**
 * Get reCAPTCHA error message
 */
if ( !function_exists( 'ovabrw_get_recaptcha_error' ) ) {
	function ovabrw_get_recaptcha_error( $code = '' ) {
		$mesg = apply_filters( OVABRW_PREFIX.'recaptcha_error_message', [
			'default' 					=> esc_html__( 'An error occurred with reCAPTCHA. Please try again later.', 'ova-brw' ),
			'missing-input-secret' 		=> esc_html__( 'The secret parameter is missing.', 'ova-brw' ),
			'invalid-input-secret' 		=> esc_html__( 'The secret parameter is invalid or malformed.', 'ova-brw' ),
			'missing-input-response' 	=> esc_html__( 'The response parameter is missing.', 'ova-brw' ),
			'invalid-input-response' 	=> esc_html__( 'The response parameter is invalid or malformed.', 'ova-brw' ),
			'bad-request' 				=> esc_html__( 'The request is invalid or malformed.', 'ova-brw' ),
			'timeout-or-duplicate' 		=> esc_html__( 'The response is no longer valid: either is too old or has been used previously.', 'ova-brw' ),
		]);

		// Get error
		$error = ovabrw_get_meta_data( $code, $mesg, $mesg['default'] );

		return apply_filters( OVABRW_PREFIX.'get_recaptcha_error', $error );
	}
}

/**
 * Get reCAPTCHA form
 */
if ( !function_exists( 'ovabrw_get_recaptcha_form' ) ) {
	function ovabrw_get_recaptcha_form( $form = '' ) {
		if ( 'both' === ovabrw_get_option_setting( 'recapcha_form', '' ) ) return true;
		if ( $form === ovabrw_get_option_setting( 'recapcha_form', '' ) ) return true;

		return false;
	}
}

/**
 * Create remaining invoice
 */
if ( !function_exists( 'ovabrw_create_remaining_invoice' ) ) {
    function ovabrw_create_remaining_invoice( $order_id, $data ) {
    	// Get order
        $order = wc_get_order( $order_id );

        try {
            $new_order = new WC_Order;
            $new_order->set_props([
            	'status'              => 'wc-pending',
                'customer_id'         => $order->get_user_id(),
                'customer_note'       => $order->get_customer_note(),
                'billing_first_name'  => $order->get_billing_first_name(),
                'billing_last_name'   => $order->get_billing_last_name(),
                'billing_company'     => $order->get_billing_company(),
                'billing_address_1'   => $order->get_billing_address_1(),
                'billing_address_2'   => $order->get_billing_address_2(),
                'billing_city'        => $order->get_billing_city(),
                'billing_state'       => $order->get_billing_state(),
                'billing_postcode'    => $order->get_billing_postcode(),
                'billing_country'     => $order->get_billing_country(),
                'billing_email'       => $order->get_billing_email(),
                'billing_phone'       => $order->get_billing_phone(),
                'shipping_first_name' => $order->get_shipping_first_name(),
                'shipping_last_name'  => $order->get_shipping_last_name(),
                'shipping_company'    => $order->get_shipping_company(),
                'shipping_address_1'  => $order->get_shipping_address_1(),
                'shipping_address_2'  => $order->get_shipping_address_2(),
                'shipping_city'       => $order->get_shipping_city(),
                'shipping_state'      => $order->get_shipping_state(),
                'shipping_postcode'   => $order->get_shipping_postcode(),
                'shipping_country'    => $order->get_shipping_country()
            ]);
            $new_order->set_currency( $order->get_currency() );
            $new_order->save();
        } catch ( Exception $e ) {
            $order->add_order_note( sprintf( esc_html__( 'Error: Unable to create follow up payment (%s)', 'ova-brw' ), $e->getMessage() ) );
            return;
        }

        // Order total
        $order_total = $data['total'];

        // Handle items
        $item_id = $new_order->add_product( $data['product'], $data['qty'], [
        	'totals' => [
            	'subtotal' 	=> $data['subtotal'],
                'total' 	=> $data['total']
            ]
        ]);

        // Get order line item
        $line_item = $new_order->get_item( $item_id );

        $new_order->set_parent_id( $order_id );
        $new_order->set_date_created( date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ) );

        // Get tax rate id
        $tax_class 		= $data['product']->get_tax_class();
        $tax_rate_id 	= 0;
        if ( wc_tax_enabled() ) {
        	$tax_rates = WC_Tax::get_rates( $tax_class );

	        if ( !empty( $tax_rates ) ) {
	            $tax_rate_id = key( $tax_rates );
	        }
        }

        // Remaining tax amount
        $remaining_tax = (float)ovabrw_get_meta_data( 'remaining_tax', $data );

        // Insurance amount
        $insurance_amount = (float)ovabrw_get_meta_data( 'insurance_amount', $data );

        // Insurance tax
        $insurance_tax = (float)ovabrw_get_meta_data( 'insurance_tax', $data );

        // Add item fee
        if ( $insurance_amount ) {
        	// Update order total
        	$order_total += $insurance_amount;

	        // Get insurance name
	        $insurance_name = ovabrw_get_insurance_fee_name();

	        // Init order item fee
        	$item_fee = new WC_Order_Item_Fee();

        	// Item fee data
        	$item_fee_data = [
        		'name'      => $insurance_name,
                'amount'    => $insurance_amount,
                'total'     => $insurance_amount,
                'order_id'  => $order_id
        	];

        	// Add item fee tax
        	if ( wc_tax_enabled() && $insurance_tax ) {
        		// Update order total
            	$order_total += $insurance_tax;

        		// Set tax for item fee
        		$item_fee_data['tax_class'] = $tax_class ? $tax_class : 0;
        		$item_fee_data['total_tax'] = $insurance_tax;
        		$item_fee_data['taxes'] 	= [
        			'total' => [
        				$tax_rate_id => $insurance_tax
        			]
        		];

        		// Order add meta insurance tax
        		$new_order->add_meta_data( '_ova_insurance_tax', $insurance_tax );

        		// Line item add meta insurance tax
        		$line_item->add_meta_data( 'ovabrw_insurance_tax', $insurance_tax );
        	}

            $item_fee->set_props( $item_fee_data );
            $item_fee->save();

            // Order add item fee
            $new_order->add_item( $item_fee );

            // Order add meta data
            $new_order->add_meta_data( '_ova_insurance_key', sanitize_title( $insurance_name ) );
            $new_order->add_meta_data( '_ova_insurance_amount', $insurance_amount );

            // Line item add meta data
            $line_item->add_meta_data( 'ovabrw_insurance_amount', $insurance_amount );
            $line_item->save();
        }

    	// Add item tax
        if ( wc_tax_enabled() && $remaining_tax ) {
        	// Update order total
        	$order_total += $remaining_tax;

        	// Order tax amount
        	$order_tax_amount = $remaining_tax + $insurance_tax;

        	// Init order item tax
            $item_tax = new WC_Order_Item_Tax();

            // Set item tax
            $item_tax->set_props([
            	'rate_id'            => $tax_rate_id,
                'tax_total'          => $order_tax_amount,
                'shipping_tax_total' => 0,
                'rate_code'          => WC_Tax::get_rate_code( $tax_rate_id ),
                'label'              => WC_Tax::get_rate_label( $tax_rate_id ),
                'compound'           => WC_Tax::is_compound( $tax_rate_id ),
                'rate_percent'       => WC_Tax::get_rate_percent_value( $tax_rate_id )
            ]);

            $item_tax->save();
            $new_order->add_item( $item_tax );
            $new_order->set_cart_tax( $order_tax_amount );

            // Set tax for line item
            $line_item->set_props([
            	'taxes' => [
            		'total' 	=> [ $tax_rate_id => $remaining_tax ],
            		'subtotal' 	=> [ $tax_rate_id => $remaining_tax ]
            	]
            ]);
            $line_item->save();

            // Prices include tax
            $prices_incl_tax = $order->get_meta( '_ova_prices_include_tax' );
            if ( $prices_incl_tax ) {
                $new_order->update_meta_data( '_ova_prices_include_tax', $prices_incl_tax );
            }
        }
        
        // Order set total
        $new_order->set_total( $order_total );
        $new_order->save();

        wc_add_order_item_meta( $item_id, 'ovabrw_parent_order_id', $order_id );
        wc_update_order_item( $item_id, [ 'order_item_name' => sprintf( esc_html__( 'Payment remaining for %s', 'ova-brw' ), $data['product']->get_title() ) ] );

        return $new_order->get_id();
    }
}

/**
 * Get predefined ranges
 */
if ( !function_exists( 'ovabrw_get_predefined_ranges' ) ) {
	function ovabrw_get_predefined_ranges() {
		$today 			= esc_html__( 'Today', 'ova-brw' );
		$next_day 		= esc_html__( 'Next Day', 'ova-brw' );
		$this_week 		= esc_html__( 'This Week', 'ova-brw' );
		$next_week 		= esc_html__( 'Next Week', 'ova-brw' );
		$this_month 	= esc_html__( 'This Month', 'ova-brw' );
		$next_month 	= esc_html__( 'Next Month', 'ova-brw' );
		$date_format 	= 'Y-m-d';
		$start_of_week 	= ovabrw_get_option_setting( 'calendar_first_day', 1 );
		$day_of_week 	= gmdate('l', strtotime("Sunday +{$start_of_week} days"));
		// Next month
		$month_number 	= gmdate( 'm', strtotime( 'next month' ) );
		$year_number 	= gmdate( 'Y', strtotime( 'next month' ) );
		$day_number 	= gmdate( 't', strtotime( 'next month' ) );

		// Predefined ranges
		$predefined_ranges = [
			$today 		=> [
				gmdate( $date_format ),
				gmdate( $date_format )
			],
			$next_day 	=> [
				gmdate( $date_format, strtotime('+1 day') ),
				gmdate( $date_format, strtotime('+1 day') )
			],
			$this_week 	=> [
				gmdate( $date_format, strtotime( "last {$day_of_week}" ) ),
				gmdate( $date_format, strtotime( "next {$day_of_week} -1 day" ) )
			],
			$next_week 	=> [
				gmdate( $date_format, strtotime( "next {$day_of_week}" ) ),
				gmdate( $date_format, strtotime( "next {$day_of_week} +6 days" ))
			],
			$this_month => [
				gmdate( $date_format, strtotime( gmdate('Y').'-'.gmdate('m').'-01') ),
				gmdate( $date_format, strtotime( gmdate('Y').'-'.gmdate('m').'-'.gmdate('t') ) )
			],
			$next_month => [
				gmdate( $date_format, strtotime( $year_number.'-'.$month_number.'-01') ),
				gmdate( $date_format, strtotime( $year_number.'-'.$month_number.'-'.$day_number ) )
			]
		];

		return apply_filters( OVABRW_PREFIX.'get_predefined_ranges', $predefined_ranges );
	}
}

/**
 * Output a text input box.
 */
if ( !function_exists( 'ovabrw_admin_text_input' ) ) {
	function ovabrw_admin_text_input( $field ) {
		$field['type'] 			= ovabrw_get_meta_data( 'type', $field, 'text' );
		$field['id'] 			= ovabrw_get_meta_data( 'id', $field );
		$field['class'] 		= ovabrw_get_meta_data( 'class', $field );
		$field['name'] 			= ovabrw_get_meta_data( 'name', $field );
		$field['value'] 		= ovabrw_get_meta_data( 'value', $field );
		$field['placeholder']   = ovabrw_get_meta_data( 'placeholder', $field );
		$field['required'] 		= ovabrw_get_meta_data( 'required', $field );
		$field['checked'] 		= ovabrw_get_meta_data( 'checked', $field );
		$field['readonly'] 		= ovabrw_get_meta_data( 'readonly', $field );
		$field['attrs'] 		= ovabrw_get_meta_data( 'attrs', $field );
		
		// Data type
		$data_type = ovabrw_get_meta_data( 'data_type', $field );

		switch ( $data_type ) {
			case 'price':
				// Add class
				$field['class'] .= ' wc_input_price';

				// Convert value
				$field['value'] = wc_format_localized_price( $field['value'] );

				// Placeholder
				if ( !$field['placeholder'] ) {
					$field['placeholder'] = esc_html__( 'price', 'ova-brw' );
				}
				break;
			case 'decimal':
				// Add class
				$field['class'] .= ' wc_input_decimal';

				// Convert value
				$field['value'] = wc_format_localized_decimal( $field['value'] );

				// Placeholder
				if ( !$field['placeholder'] ) {
					$field['placeholder'] = esc_html__( 'price', 'ova-brw' );
				}
				break;
			case 'timepicker':
				// Add class
				$field['class'] .= ' ovabrw-timepicker';

				// Time format
				$time_format = ovabrw_get_time_format();

				// Convert value
				$field['value'] = strtotime( $field['value'] ) ? gmdate( $time_format, strtotime( $field['value'] ) ) : '';

				// Placeholder
				if ( !$field['placeholder'] ) {
					$field['placeholder'] = ovabrw_get_time_format_placeholder();
				}
				break;
			case 'datepicker':
				// Add class
				$field['class'] .= ' ovabrw-datepicker';

				// Date format
				$date_format = ovabrw_get_date_format();

				// Convert valie
				$field['value'] = strtotime( $field['value'] ) ? gmdate( $date_format, strtotime( $field['value'] ) ) : '';

				// Placeholder
				if ( !$field['placeholder'] ) {
					$field['placeholder'] = ovabrw_get_placeholder_date();
				}
				break;
			case 'datetimepicker':
				// Add class
				$field['class'] .= ' ovabrw-datetimepicker';

				// Get date time format
				$datetime_format = ovabrw_get_datetime_format();

				// Convert value
				$field['value'] = strtotime( $field['value'] ) ? gmdate( $datetime_format, strtotime( $field['value'] ) ) : '';

				// Placeholder
				if ( !$field['placeholder'] ) {
					$field['placeholder'] = ovabrw_get_datetime_format_placeholder();
				}
				break;
			case 'number':
				// Convert value
				$field['value'] = ( '' !== $field['value'] ) ? (int)$field['value'] : '';

				// Placeholder
				if ( !$field['placeholder'] ) {
					$field['placeholder'] = esc_html__( 'number', 'ova-brw' );
				}
			default:
				break;
		}

		// Custom attribute handling
		$attrs = [];

		if ( ovabrw_array_exists( $field['attrs'] ) ) {
			foreach ( $field['attrs'] as $attr => $value ) {
				if ( $value === '' ) continue;
				$attrs[] = esc_attr( $attr ) . '="' . esc_attr( $value ) . '"';
			}
		}

		// Required
		if ( $field['required'] ) {
			$attrs[] = 'required';
		}

		// Checked
		if ( $field['checked'] ) {
			$attrs[] = 'checked';
		}

		// Read only
		if ( $field['readonly'] ) {
			$attrs[] = 'readonly';
		}

		do_action( OVABRW_PREFIX.'before_wp_text_input', $field );

		if ( $field['id'] ) {
			echo '<input type="' . esc_attr( $field['type'] ) . '" id="' . esc_attr( $field['id'] ) . '" class="' . esc_attr( $field['class'] ) . '" name="' . esc_attr( $field['name'] ) . '" value="' . esc_attr( $field['value'] ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" ' . wp_kses_post( implode( ' ', $attrs ) ) . ' />';
		} else {
			echo '<input type="' . esc_attr( $field['type'] ) . '" class="' . esc_attr( $field['class'] ) . '" name="' . esc_attr( $field['name'] ) . '" value="' . esc_attr( $field['value'] ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" ' . wp_kses_post( implode( ' ', $attrs ) ) . ' />';
		}

		do_action( OVABRW_PREFIX.'after_wp_text_input', $field );
	}
}

/**
 * Output the text input
 */
if ( !function_exists( 'ovabrw_text_input' ) ) {
    function ovabrw_text_input( $args = [] ) {
        $args['type']           = ovabrw_get_meta_data( 'type', $args, 'text' );
        $args['id']             = ovabrw_get_meta_data( 'id', $args );
        $args['class']          = ovabrw_get_meta_data( 'class', $args );
        $args['name']           = ovabrw_get_meta_data( 'name', $args );
        $args['value']          = ovabrw_get_meta_data( 'value', $args );
        $args['default']        = ovabrw_get_meta_data( 'default', $args );
        $args['placeholder']    = ovabrw_get_meta_data( 'placeholder', $args );
        $args['description']    = ovabrw_get_meta_data( 'description', $args );
        $args['required']       = ovabrw_get_meta_data( 'required', $args );
        $args['readonly']       = ovabrw_get_meta_data( 'readonly', $args );
        $args['checked']        = ovabrw_get_meta_data( 'checked', $args );
        $args['disabled']       = ovabrw_get_meta_data( 'disabled', $args );
        $args['attrs']          = ovabrw_get_meta_data( 'attrs', $args );

        // Set value
        if ( ! $args['value'] && $args['default'] ) {
            $args['value'] = $args['default'];
        }

        // Required
        if ( $args['required'] ) {
            $args['class'] .= ' required';
        }

        // Data type
        $data_type = ovabrw_get_meta_data( 'data_type', $args );

        switch ( $data_type ) {
            case 'timepicker':
                // Add class
                $args['class'] .= ' ovabrw-timepicker';

                // Get time format
                $time_format = ovabrw_get_time_format();

                // Set value
                $args['value'] = strtotime( $args['value'] ) ? gmdate( $time_format, strtotime( $args['value'] ) ) : '';

                // Set placeholder
                if ( !$args['placeholder'] ) {
                    $args['placeholder'] = ovabrw_get_time_format_placeholder();
                }
                break;
            case 'datepicker':
                // Add class
                $args['class'] .= ' ovabrw-datepicker';

                // Get date format
                $date_format = ovabrw_get_date_format();

                // Set value
                $args['value']  = strtotime( $args['value'] ) ? gmdate( $date_format, strtotime( $args['value'] ) ) : '';

                // Set placeholder
                if ( !$args['placeholder'] ) {
                    $args['placeholder'] = ovabrw_get_placeholder_date();
                }
                break;
            case 'datepicker-field':
                // Add class
                $args['class'] .= ' ovabrw-datepicker-field';

                // Get date format
                $date_format = ovabrw_get_date_format();

                // Set value
                $args['value']  = strtotime( $args['value'] ) ? gmdate( $date_format, strtotime( $args['value'] ) ) : '';

                // Set placeholder
                if ( !$args['placeholder'] ) {
                    $args['placeholder'] = ovabrw_get_placeholder_date();
                }
                break;
            case 'datepicker-start':
                // Add class
                $args['class'] .= ' ovabrw-datepicker-start';

                // Get date format
                $date_format = ovabrw_get_date_format();

                // Set value
                $args['value']  = strtotime( $args['value'] ) ? gmdate( $date_format, strtotime( $args['value'] ) ) : '';

                // Set placeholder
                if ( !$args['placeholder'] ) {
                    $args['placeholder'] = ovabrw_get_placeholder_date();
                }
                break;
            case 'datepicker-end':
                // Add class
                $args['class'] .= ' ovabrw-datepicker-end';

                // Get date format
                $date_format = ovabrw_get_date_format();

                // Set value
                $args['value']  = strtotime( $args['value'] ) ? gmdate( $date_format, strtotime( $args['value'] ) ) : '';

                // Set placeholder
                if ( !$args['placeholder'] ) {
                    $args['placeholder'] = ovabrw_get_placeholder_date();
                }
                break;
            case 'datetimepicker':
                // Add class
                $args['class'] .= ' ovabrw-datetimepicker';

                // Get date time format
                $datetime_format = ovabrw_get_datetime_format();

                // Set value
                $args['value'] = strtotime( $args['value'] ) ? gmdate( $datetime_format, strtotime( $args['value'] ) ) : '';

                // Set placeholder
                if ( !$args['placeholder'] ) {
                    $args['placeholder'] = ovabrw_get_datetime_format_placeholder();
                }
                break;
            case 'datetimepicker-start':
                // Add class
                $args['class'] .= ' ovabrw-datetimepicker-start';

                // Get date time format
                $datetime_format = ovabrw_get_datetime_format();

                // Set value
                $args['value'] = strtotime( $args['value'] ) ? gmdate( $datetime_format, strtotime( $args['value'] ) ) : '';

                // Set placeholder
                if ( !$args['placeholder'] ) {
                    $args['placeholder'] = ovabrw_get_datetime_format_placeholder();
                }
                break;
            case 'datetimepicker-end':
                // Add class
                $args['class'] .= ' ovabrw-datetimepicker-end';

                // Get date time format
                $datetime_format = ovabrw_get_datetime_format();

                // Set value
                $args['value'] = strtotime( $args['value'] ) ? gmdate( $datetime_format, strtotime( $args['value'] ) ) : '';

                // Set placeholder
                if ( !$args['placeholder'] ) {
                    $args['placeholder'] = ovabrw_get_datetime_format_placeholder();
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

        if ( ovabrw_array_exists( $args['attrs'] ) ) {
            foreach ( $args['attrs'] as $attr => $value ) {
                if ( !$value && $value !== 0 ) continue;
                $attrs[] = esc_attr( $attr ) . '="' . esc_attr( $value ) . '"';
            }
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
        $key = ovabrw_get_meta_data( 'key', $args );
        if ( $key ) {
            $name = $args['name'].'['.esc_attr( $key ).']';
        }

        do_action( 'ovabrw_before_text_input', $args );

        if ( $args['id'] ) {
            echo '<input type="'.esc_attr( $args['type'] ).'" id="'.esc_attr( $args['id'] ).'" class="'.esc_attr( $args['class'] ).'" name="'.esc_attr( $name ).'" value="'.esc_attr( $args['value'] ).'" placeholder="'.esc_attr( $args['placeholder'] ).'" '.wp_kses_post( implode( ' ', $attrs ) ).' />';
        } else {
            echo '<input type="'.esc_attr( $args['type'] ).'" class="'.esc_attr( $args['class'] ).'" name="'.esc_attr( $name ).'" value="'.esc_attr( $args['value'] ).'" placeholder="'.esc_attr( $args['placeholder'] ).'" '.wp_kses_post( implode( ' ', $attrs ) ).' />';
        }

        // Description
        if ( $args['description'] ) {
            echo '<span class="description">'.wp_kses_post( $args['description'] ).'</span>';
        }

        do_action( 'ovabrw_after_text_input', $args );
    }
}

/**
 * Output a text input box.
 */
if ( !function_exists( 'ovabrw_wp_text_input' ) ) {
	function ovabrw_wp_text_input( $field ) {
		$field['type'] 			= ovabrw_get_meta_data( 'type', $field, 'text' );
		$field['id'] 			= ovabrw_get_meta_data( 'id', $field );
		$field['class'] 		= ovabrw_get_meta_data( 'class', $field );
		$field['name'] 			= ovabrw_get_meta_data( 'name', $field );
		$field['value'] 		= ovabrw_get_meta_data( 'value', $field );
		$field['placeholder']   = ovabrw_get_meta_data( 'placeholder', $field );
		$field['required'] 		= ovabrw_get_meta_data( 'required', $field );
		$field['checked'] 		= ovabrw_get_meta_data( 'checked', $field );
		$field['readonly'] 		= ovabrw_get_meta_data( 'readonly', $field );
		$field['attrs'] 		= ovabrw_get_meta_data( 'attrs', $field );
		
		// Data type
		$data_type = ovabrw_get_meta_data( 'data_type', $field );

		switch ( $data_type ) {
			case 'price':
				// Add class
				$field['class'] .= ' wc_input_price';

				// Convert value
				$field['value'] = wc_format_localized_price( $field['value'] );

				// Placeholder
				if ( ! $field['placeholder'] ) {
					$field['placeholder'] = esc_html__( 'price', 'ova-brw' );
				}
				break;
			case 'decimal':
				// Add class
				$field['class'] .= ' wc_input_decimal';

				// Convert value
				$field['value'] = wc_format_localized_decimal( $field['value'] );

				// Placeholder
				if ( ! $field['placeholder'] ) {
					$field['placeholder'] = esc_html__( 'price', 'ova-brw' );
				}
				break;
			case 'timepicker':
				// Add class
				$field['class'] .= ' ovabrw-timepicker';

				// Time format
				$time_format = ovabrw_get_time_format();

				// Convert value
				$field['value'] = strtotime( $field['value'] ) ? gmdate( $time_format, strtotime( $field['value'] ) ) : '';

				// Placeholder
				if ( ! $field['placeholder'] ) {
					$field['placeholder'] = ovabrw_get_time_format_placeholder();
				}
				break;
			case 'datepicker':
				// Add class
				$field['class'] .= ' ovabrw-datepicker';

				// Date format
				$date_format = ovabrw_get_date_format();

				// Convert valie
				$field['value'] = strtotime( $field['value'] ) ? gmdate( $date_format, strtotime( $field['value'] ) ) : '';

				// Placeholder
				if ( ! $field['placeholder'] ) {
					$field['placeholder'] = ovabrw_get_placeholder_date();
				}
				break;
			case 'datetimepicker':
				// Add class
				$field['class'] .= ' ovabrw-datetimepicker';

				// Get date time format
				$datetime_format = ovabrw_get_datetime_format();

				// Convert value
				$field['value'] = strtotime( $field['value'] ) ? gmdate( $datetime_format, strtotime( $field['value'] ) ) : '';

				// Placeholder
				if ( ! $field['placeholder'] ) {
					$field['placeholder'] = ovabrw_get_datetime_format_placeholder();
				}
				break;
			case 'number':
				// Convert value
				$field['value'] = ( '' !== $field['value'] ) ? (int)$field['value'] : '';

				// Placeholder
				if ( ! $field['placeholder'] ) {
					$field['placeholder'] = esc_html__( 'number', 'ova-brw' );
				}
			default:
				break;
		}

		// Custom attribute handling
		$attrs = [];

		if ( ovabrw_array_exists( $field['attrs'] ) ) {
			foreach ( $field['attrs'] as $attr => $value ) {
				if ( $value === '' ) continue;
				$attrs[] = esc_attr( $attr ) . '="' . esc_attr( $value ) . '"';
			}
		}

		// Required
		if ( $field['required'] ) {
			$attrs[] = 'required';
		}

		// Checked
		if ( $field['checked'] ) {
			$attrs[] = 'checked';
		}

		// Read only
		if ( $field['readonly'] ) {
			$attrs[] = 'readonly';
		}

		do_action( OVABRW_PREFIX.'before_wp_text_input', $field );

		if ( $field['id'] ) {
			echo '<input type="' . esc_attr( $field['type'] ) . '" id="' . esc_attr( $field['id'] ) . '" class="' . esc_attr( $field['class'] ) . '" name="' . esc_attr( $field['name'] ) . '" value="' . esc_attr( $field['value'] ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" ' . wp_kses_post( implode( ' ', $attrs ) ) . ' />';
		} else {
			echo '<input type="' . esc_attr( $field['type'] ) . '" class="' . esc_attr( $field['class'] ) . '" name="' . esc_attr( $field['name'] ) . '" value="' . esc_attr( $field['value'] ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" ' . wp_kses_post( implode( ' ', $attrs ) ) . ' />';
		}

		do_action( OVABRW_PREFIX.'after_wp_text_input', $field );
	}
}

/**
 * Output a textarea box.
 */
if ( !function_exists( 'ovabrw_wp_textarea' ) ) {
	function ovabrw_wp_textarea( $field ) {
		$field['type'] 			= ovabrw_get_meta_data( 'type', $field, 'text' );
		$field['id'] 			= ovabrw_get_meta_data( 'id', $field );
		$field['class'] 		= ovabrw_get_meta_data( 'class', $field );
		$field['name'] 			= ovabrw_get_meta_data( 'name', $field );
		$field['value'] 		= ovabrw_get_meta_data( 'value', $field );
		$field['placeholder']   = ovabrw_get_meta_data( 'placeholder', $field );
		$field['required'] 		= ovabrw_get_meta_data( 'required', $field );
		$field['readonly'] 		= ovabrw_get_meta_data( 'readonly', $field );
		$field['attrs'] 		= ovabrw_get_meta_data( 'attrs', $field );

		// Custom attribute handling
		$attrs = [];

		if ( ovabrw_array_exists( $field['attrs'] ) ) {
			foreach ( $field['attrs'] as $attr => $value ) {
				if ( $value === '' ) continue;
				$attrs[] = esc_attr( $attr ) . '="' . esc_attr( $value ) . '"';
			}
		}

		// Required
		if ( $field['required'] ) {
			$attrs[] = 'required';
		}

		// Read only
		if ( $field['readonly'] ) {
			$attrs[] = 'readonly';
		}

		do_action( OVABRW_PREFIX.'before_wp_textarea', $field );

		if ( $field['id'] ) {
			echo '<textarea id="' . esc_attr( $field['id'] ) . '" class="' . esc_attr( $field['class'] ) . '" name="' . esc_attr( $field['name'] ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" ' . wp_kses_post( implode( ' ', $attrs ) ) . ' >' . esc_html( $field['value'] ) . '</textarea>';
		} else {
			echo '<textarea class="' . esc_attr( $field['class'] ) . '" name="' . esc_attr( $field['name'] ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" ' . wp_kses_post( implode( ' ', $attrs ) ) . ' >' . esc_html( $field['value'] ) . '</textarea>';
		}

		do_action( OVABRW_PREFIX.'after_wp_textarea', $field );
	}
}

/**
 * Output a select input box.
 */
if ( !function_exists( 'ovabrw_wp_select_input' ) ) {
	function ovabrw_wp_select_input( $field ) {
		$field['id'] 			= ovabrw_get_meta_data( 'id', $field );
		$field['class'] 		= ovabrw_get_meta_data( 'class', $field );
		$field['name'] 			= ovabrw_get_meta_data( 'name', $field );
		$field['value'] 		= ovabrw_get_meta_data( 'value', $field );
		$field['placeholder'] 	= ovabrw_get_meta_data( 'placeholder', $field );
		$field['options'] 		= ovabrw_get_meta_data( 'options', $field );
		$field['required'] 		= ovabrw_get_meta_data( 'required', $field );
		$field['disabled'] 		= ovabrw_get_meta_data( 'disabled', $field );
		$field['multiple'] 		= ovabrw_get_meta_data( 'multiple', $field );
		$field['attrs'] 		= ovabrw_get_meta_data( 'attrs', $field );

		// Custom attribute handling
		$attrs = [];

		if ( ovabrw_array_exists( $field['attrs'] ) ) {
			foreach ( $field['attrs'] as $attr => $value ) {
				$attrs[] = esc_attr( $attr ) . '="' . esc_attr( $value ) . '"';
			}
		}

		// Required
		if ( $field['required'] ) {
			$attrs[] = 'required';
		}

		// Disabled
		if ( $field['disabled'] ) {
			$attrs[] = 'disabled';
		}

		// Multiple
		if ( $field['multiple'] ) {
			$attrs[] = 'multiple';
		}

		do_action( OVABRW_PREFIX.'before_wp_select_input', $field );

		if ( $field['id'] ) {
			echo '<select name="' . esc_attr( $field['name'] ) . '" id="' . esc_attr( $field['id'] ) . '" class="' . esc_attr( $field['class'] ) . '" ' . wp_kses_post( implode( ' ', $attrs ) ) . ' >';
				if ( $field['placeholder'] ) {
					echo '<option value="">' . esc_html( $field['placeholder'] ) . '</option>';
				}

				foreach ( $field['options'] as $key => $value ) {
					echo '<option value="' . esc_attr( $key ) . '" ' . ovabrw_selected( $key, $field['value'], false ) . '>' . esc_html( $value ) . '</option>';
				}
			echo '</select>';
		} else {
			echo '<select name="' . esc_attr( $field['name'] ) . '" class="' . esc_attr( $field['class'] ) . '" ' . wp_kses_post( implode( ' ', $attrs ) ) . ' >';
				if ( $field['placeholder'] ) {
					echo '<option value="">' . esc_html( $field['placeholder'] ) . '</option>';
				}

				foreach ( $field['options'] as $key => $value ) {
					echo '<option value="' . esc_attr( $key ) . '" ' . ovabrw_selected( $key, $field['value'], false ) . '>' . esc_html( $value ) . '</option>';
				}
			echo '</select>';
		}
		
		do_action( OVABRW_PREFIX.'before_wp_select_input', $field );
	}
}

/**
 * Validation messages
 */
if ( !function_exists( 'ovabrw_get_validation_messages' ) ) {
	function ovabrw_get_validation_messages() {
		return (array) apply_filters( OVABRW_PREFIX.'get_validation_messages', [
			'dateFormat' 	=> ovabrw_get_date_format(),
			'required' 		=> esc_html__( 'This field is required.', 'ova-brw' ),
			'duplicateID' 	=> esc_html__( 'Duplicate ID.', 'ova-brw' ),
			'duplicateTime' => esc_html__( 'Duplicate time.', 'ova-brw' ),
			'duplicateDate' => esc_html__( 'Duplicate date.', 'ova-brw' ),
			'conditions' 	=> esc_html__( 'Please read and accept the terms and conditions to continue.', 'ova-brw' ),
			'reCAPTCHA'  	=> esc_html__( 'Please verify that you are not a robot.', 'ova-brw' ),
			'email' 		=> esc_html__( 'Please enter a valid email address.', 'ova-brw' ),
			'phone' 		=> esc_html__( 'Please enter a valid phone number.', 'ova-brw' ),
			'url' 			=> esc_html__( 'Please enter a valid URL.', 'ova-brw' ),
			'date' 			=> esc_html__( 'Please enter a valid date.', 'ova-brw' ),
			'number' 		=> esc_html__( 'Please enter a valid number.', 'ova-brw' ),
			'digits' 		=> esc_html__( 'Please enter only digits.', 'ova-brw' ),
			'size' 			=> esc_html__( 'Please select a file less than [size] MB.' ),
			'fileType' 		=> esc_html__( 'Invalid file type. Please upload an image file (.jpg, .jpeg, .png, .pdf, .doc).', 'ova-brw' ),
			'min' 			=> esc_html__( 'Please enter a value greater than or equal to [number].', 'ova-brw' ),
			'max' 			=> esc_html__( 'Please enter a value less than or equal to [number].', 'ova-brw' ),
			'minQty' 		=> esc_html__( 'Minimum quantity: [number].', 'ova-brw' ),
			'maxQty' 		=> esc_html__( 'Maximum quantity: [number].', 'ova-brw' ),
			'minGuests' 	=> esc_html__( 'Minimum number of guests: [number].', 'ova-brw' ),
			'maxGuests' 	=> esc_html__( 'Maximum number of guests: [number].', 'ova-brw' ),
			'minGuest' 		=> esc_html__( 'Minimum number of [guest]: [number].', 'ova-brw' ),
			'maxGuest' 		=> esc_html__( 'Maximum number of [guest]: [number].', 'ova-brw' ),
			'mapAPI' 		=> esc_html__( 'This page can\'t load Google Maps properly.', 'ovabrw-' ),

			// Custom checkout fields
			'placeholderText' 	=> esc_html__( 'text', 'ova-brw' ),
			'placeholderNumber' => esc_html__( 'number', 'ova-brw' ),
			'btnAddNew' 		=> esc_html__( 'Add new option', 'ova-brw' ),
			'btnRemove' 		=> esc_html__( 'Remove option', 'ova-brw' ),
			'confirmText' 		=> esc_html__( 'Are you sure?', 'ova-brw' ),
			'disabledMesg' 		=> esc_html__( 'You can\'t book on this day!', 'ova-brw' )
		]);
	}
}

/**
 * Get phone number
 */
if ( !function_exists( 'ovabrw_get_phone_number' ) ) {
	function ovabrw_get_phone_number( $phone = '' ) {
		if ( !$phone ) return $phone;

		$phone = trim( $phone );

		if ( strpos( $phone, '+' ) === 0 ) {
	        // Keep + at start, strip rest
	        $clean = '+' . preg_replace('/[^\d]/', '', substr( $phone, 1 ) );
	    } else {
	        $clean = preg_replace('/[^\d]/', '', $phone);
	    }

	    return apply_filters( OVABRW_PREFIX.'get_phone_number', $clean, $phone );
	}
}