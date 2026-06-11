<?php if ( !defined( 'ABSPATH' ) ) exit();

/**
 * Class OVABRW_Admin_Meta_Boxes
 */
if ( !class_exists( 'OVABRW_Admin_Meta_Boxes' ) ) {

    class OVABRW_Admin_Meta_Boxes {

        /**
         * Constructor
         */
        public function __construct() {
            // Product rental selector
            add_filter( 'product_type_selector', [ $this, 'product_rental_selector' ] );

            // Default product rental query
            add_filter( 'woocommerce_product_type_query', [ $this, 'default_product_rental_query' ], 10, 2 );

            // Render meta-boxes
            add_action( 'woocommerce_product_options_general_product_data', [ $this, 'render_meta_boxes' ] );

            // Save meta-boxes
            add_action( 'woocommerce_process_product_meta', [ $this, 'save_meta_boxes' ], 10, 2 );  
        }

        /**
         * Get id
         */
        public function get_id() {
            return get_the_ID();
        }

        /**
         * Get meta name with prefix
         */
        public function get_meta_name( $name = '' ) {
            if ( $name ) $name = OVABRW_PREFIX.$name;

            return apply_filters( OVABRW_PREFIX.'get_meta_name', $name, $this );
        }

        /**
         * Get meta value from database
         */
        public function get_meta_value( $name = '', $default = false ) {            
            $value = get_post_meta( $this->get_id(), $this->get_meta_name( $name ), true );
            if ( !$value && $default !== false ) $value = $default;

            return apply_filters( OVABRW_PREFIX.'get_meta_value', $value, $name, $default, $this );
        }

        /**
         * Product rental selector
         */
        public function product_rental_selector( $product_types ) {
            if ( ovabrw_array_exists( $product_types ) ) {
                $product_types[OVABRW_RENTAL] = esc_html__( 'Tour', 'ova-brw' );
            }

            return $product_types;
        }

        /**
         * Default product rental query
         */
        public function default_product_rental_query( $product_type, $product_id ) {
            global $pagenow, $post_type;

            if ( 'post-new.php' == $pagenow && 'product' == $post_type ) {
                return OVABRW_RENTAL;
            }

            return $product_type;
        }

        /**
         * Update meta
         */
        public function update_meta( $post_id = '', $name = '', $data = [], $type = '', $default = false ) {
            do_action( OVABRW_PREFIX.'before_update_meta', $post_id, $name, $data, $type, $default );

            if ( !$post_id || !$name ) return;

            // Meta key
            $meta_key = ovabrw_meta_key( $name );

            if ( '' !== ovabrw_get_meta_data( $meta_key, $data ) ) {
                if ( 'html' == $type ) {
                    $meta_value = wp_kses_post( trim( $data[$meta_key] ) );
                } else {
                    $meta_value = wc_clean( wp_unslash( $data[$meta_key] ) );
                }

                if ( !$meta_value && $default !== false ) {
                    $meta_value = $default;
                }

                if ( '' !== $meta_value ) {
                    if ( 'date' == $type ) {
                        $meta_value = ovabrw_format_date( $meta_value );
                    } elseif ( 'number' == $type ) {
                        $meta_value = ovabrw_format_number( $meta_value );
                    } elseif ( 'price' == $type ) {
                        $meta_value = ovabrw_format_price( $meta_value );
                    } elseif ( 'slug' == $type ) {
                        $meta_value = ovabrw_sanitize_title( $meta_value );
                    }

                    update_post_meta( $post_id, $meta_key, $meta_value );
                } else {
                    delete_post_meta( $post_id, $meta_key );
                }
            } else {
                delete_post_meta( $post_id, $meta_key );
            }

            do_action( OVABRW_PREFIX.'after_update_meta', $post_id, $name, $data, $type, $default );
        }

        /**
         * Render meta-boxes
         */
        public function render_meta_boxes() {
            include( OVABRW_PLUGIN_PATH.'/admin/metabox/ovabrw-custom-fields.php' );
        }

        /**
         * Save meta-boxes
         */
        public function save_meta_boxes( $post_id, $data ) {
            // Post data
            if ( !is_object( $data ) ) $_POST = $data;

            // Get product type
            $product_type = ovabrw_get_meta_data( 'product-type', $_POST );
            if ( OVABRW_RENTAL === $product_type ) {
                // Regular price
                $regular_price = ovabrw_format_price( ovabrw_get_meta_data( '_regular_price', $_POST ) );

                update_post_meta( $post_id, '_regular_price', $regular_price );
                update_post_meta( $post_id, '_price', $regular_price );

                // Child price
                $this->update_meta( $post_id, 'children_price', $_POST, 'price' );

                // Baby price
                $this->update_meta( $post_id, 'baby_price', $_POST, 'price' );

                // Type of insurance
                $this->update_meta( $post_id, 'typeof_insurance', $_POST );

                // Insurance amount
                $this->update_meta( $post_id, 'amount_insurance', $_POST, 'price' );

                // Adult insurance
                $this->update_meta( $post_id, 'adult_insurance', $_POST, 'price' );

                // Child insurance
                $this->update_meta( $post_id, 'child_insurance', $_POST, 'price' );

                // Baby insurance
                $this->update_meta( $post_id, 'baby_insurance', $_POST, 'price' );

                // Embed video
                $this->update_meta( $post_id, 'embed_video', $_POST );

                // Destination
                $this->update_meta( $post_id, 'destination', $_POST );

                // Quantity
                $this->update_meta( $post_id, 'stock_quantity', $_POST, 'number' );

                // Duration
                $this->update_meta( $post_id, 'duration_checkbox', $_POST );

                // Number of days
                $this->update_meta( $post_id, 'number_days', $_POST, 'number' );

                // Number of hours
                $this->update_meta( $post_id, 'number_hours', $_POST, 'number' );

                // Schedule time
                $this->update_meta( $post_id, 'schedule_time', $_POST, 'date' );

                // Schedule adult price
                $this->update_meta( $post_id, 'schedule_adult_price', $_POST, 'price' );

                // Schedule child price
                $this->update_meta( $post_id, 'schedule_children_price', $_POST, 'price' );

                // Schedule baby price
                $this->update_meta( $post_id, 'schedule_baby_price', $_POST, 'price' );

                // Schedule baby type
                $this->update_meta( $post_id, 'schedule_type', $_POST, '' );

                // Fixed check-in date
                $this->update_meta( $post_id, 'fixed_time_check_in', $_POST, 'date' );

                // Fixed check-out date
                $this->update_meta( $post_id, 'fixed_time_check_out', $_POST, 'date' );

                // Deposit enable
                $this->update_meta( $post_id, 'enable_deposit', $_POST, '', 'no' );

                // Deposit full payment
                $this->update_meta( $post_id, 'force_deposit', $_POST, '', 'no' );

                // Deposit default selected
                $this->update_meta( $post_id, 'deposit_default', $_POST, '', 'full' );

                // Deposit type
                $this->update_meta( $post_id, 'type_deposit', $_POST, '', 'percent' );

                // Deposit amount
                $this->update_meta( $post_id, 'amount_deposit', $_POST, 'price' );

                // Quantity by guests
                $this->update_meta( $post_id, 'stock_quantity_by_guests', $_POST );

                // Min number of guests
                $this->update_meta( $post_id, 'min_total_guest', $_POST, 'number' );

                // Max number of guests
                $this->update_meta( $post_id, 'max_total_guest', $_POST, 'number' );

                // Min number of adults
                $this->update_meta( $post_id, 'adults_min', $_POST, 'number' );

                // Max number of adults
                $this->update_meta( $post_id, 'adults_max', $_POST, 'number' );

                // Min number of children
                $this->update_meta( $post_id, 'childrens_min', $_POST, 'number' );

                // Max number of children
                $this->update_meta( $post_id, 'childrens_max', $_POST, 'number' );

                // Min number of babies
                $this->update_meta( $post_id, 'babies_min', $_POST, 'number' );

                // Max number of babies
                $this->update_meta( $post_id, 'babies_max', $_POST, 'number' );

                // Features icons
                $this->update_meta( $post_id, 'features_icons', $_POST );

                // Features labels
                $this->update_meta( $post_id, 'features_label', $_POST );

                // Features description
                $this->update_meta( $post_id, 'features_desc', $_POST );

                // Discount adult price
                $this->update_meta( $post_id, 'gd_adult_price', $_POST, 'price' );

                // Discount child price
                $this->update_meta( $post_id, 'gd_children_price', $_POST, 'price' );

                // Discount baby price
                $this->update_meta( $post_id, 'gd_baby_price', $_POST, 'price' );

                // Discount from
                $this->update_meta( $post_id, 'gd_duration_min', $_POST, 'number' );

                // Discount to
                $this->update_meta( $post_id, 'gd_duration_max', $_POST, 'number' );

                // Special time - adult price
                $this->update_meta( $post_id, 'st_adult_price', $_POST, 'price' );

                // Special time - child price
                $this->update_meta( $post_id, 'st_children_price', $_POST, 'price' );

                // Special time - baby price
                $this->update_meta( $post_id, 'st_baby_price', $_POST, 'price' );

                // Special time start date
                $this->update_meta( $post_id, 'st_startdate', $_POST, 'date' );

                // Special time end date
                $this->update_meta( $post_id, 'st_enddate', $_POST, 'date' );

                // Special time discounts
                $key = ovabrw_meta_key( 'st_discount' );
                if ( ovabrw_get_meta_data( $key, $_POST ) ) {
                    foreach ( ovabrw_get_meta_data( $key, $_POST ) as $k => $item ) {
                        // Adult price
                        if ( isset( $item['adult_price'] ) ) {
                            $_POST[$key][$k]['adult_price'] = ovabrw_format_price( $item['adult_price'] );
                        }

                        // Child price
                        if ( isset( $item['children_price'] ) ) {
                            $_POST[$key][$k]['children_price'] = ovabrw_format_price( $item['children_price'] );
                        }

                        // Baby price
                        if ( isset( $item['baby_price'] ) ) {
                            $_POST[$key][$k]['baby_price'] = ovabrw_format_price( $item['baby_price'] );
                        }

                        // From
                        if ( isset( $item['min'] ) ) {
                            $_POST[$key][$k]['min'] = ovabrw_format_price( $item['min'] );
                        }

                        // To
                        if ( isset( $item['max'] ) ) {
                            $_POST[$key][$k]['max'] = ovabrw_format_price( $item['max'] );
                        }
                    }
                }
                $this->update_meta( $post_id, 'st_discount', $_POST );

                // Resource ids
                $this->update_meta( $post_id, 'rs_id', $_POST, 'slug' );

                // Resource names
                $this->update_meta( $post_id, 'rs_name', $_POST );

                // Resource descriptions
                $this->update_meta( $post_id, 'rs_description', $_POST );

                // Resource adult price
                $this->update_meta( $post_id, 'rs_adult_price', $_POST, 'price' );

                // Resource child price
                $this->update_meta( $post_id, 'rs_children_price', $_POST, 'price' );

                // Resource baby price
                $this->update_meta( $post_id, 'rs_baby_price', $_POST, 'price' );

                // Resource quantity
                $this->update_meta( $post_id, 'rs_quantity', $_POST, 'number' );

                // Resource duration
                $this->update_meta( $post_id, 'rs_duration_type', $_POST );

                // Service labels
                $this->update_meta( $post_id, 'label_service', $_POST );

                // Service required
                $this->update_meta( $post_id, 'service_required', $_POST );

                // Service ids
                $this->update_meta( $post_id, 'service_id', $_POST, 'slug' );

                // Service names
                $this->update_meta( $post_id, 'service_name', $_POST );

                // Service adult price
                $this->update_meta( $post_id, 'service_adult_price', $_POST, 'price' );

                // Service child price
                $this->update_meta( $post_id, 'service_children_price', $_POST, 'price' );

                // Service babies price
                $this->update_meta( $post_id, 'service_baby_price', $_POST, 'price' );

                // Service quantity
                $this->update_meta( $post_id, 'service_quantity', $_POST, 'number' );

                // Service duration type
                $this->update_meta( $post_id, 'service_duration_type', $_POST );

                // Unavailable time
                $this->update_meta( $post_id, 'untime_startdate', $_POST, 'date' );
                $this->update_meta( $post_id, 'untime_enddate', $_POST, 'date' );

                // Product template
                $this->update_meta( $post_id, 'product_template', $_POST );

                // Disable week day
                $this->update_meta( $post_id, 'product_disable_week_day', $_POST );

                // Preparation time
                $this->update_meta( $post_id, 'preparation_time', $_POST, 'number' );

                // Book before X hours
                $this->update_meta( $post_id, 'book_before_x_hours', $_POST, 'date' );

                // Show children field
                $this->update_meta( $post_id, 'show_children', $_POST );

                // Show babies field
                $this->update_meta( $post_id, 'show_babies', $_POST );

                // Custom checkout fields
                $this->update_meta( $post_id, 'manage_custom_checkout_field', $_POST );
                $this->update_meta( $post_id, 'product_custom_checkout_field', $_POST );

                // Show check-out date field
                $this->update_meta( $post_id, 'manage_checkout_field', $_POST );

                // Show forms
                $this->update_meta( $post_id, 'forms_product', $_POST );

                // Enquiry shortcode
                $this->update_meta( $post_id, 'enquiry_shortcode', $_POST );

                // Header
                $this->update_meta( $post_id, 'product_header', $_POST );

                // Footer
                $this->update_meta( $post_id, 'product_footer', $_POST );

                // Short address
                $this->update_meta( $post_id, 'short_address', $_POST );

                // Show map
                $this->update_meta( $post_id, 'show_map', $_POST );

                // Map type
                $map_type = ovabrw_get_meta_data( 'map_type', $_POST );
                update_post_meta( $post_id, 'ovabrw_map_type', $map_type );

                // Map iframe
                $map_iframe = ovabrw_get_meta_data( 'map_iframe', $_POST );
                update_post_meta( $post_id, 'ovabrw_map_iframe', $map_iframe );

                // Google MAP API
                if ( ovabrw_get_option_setting( 'google_key_map' ) ) {
                    // Map name
                    $this->update_meta( $post_id, 'map_name', $_POST );

                    // Map address
                    $this->update_meta( $post_id, 'address', $_POST );

                    // Map latitude
                    $this->update_meta( $post_id, 'latitude', $_POST );

                    // Map longitude
                    $this->update_meta( $post_id, 'longitude', $_POST );
                }
            } // END
        }
    }

    // init
    new OVABRW_Admin_Meta_Boxes();
}