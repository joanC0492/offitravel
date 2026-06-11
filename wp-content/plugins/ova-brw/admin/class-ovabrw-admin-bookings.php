<?php if ( !defined( 'ABSPATH' ) ) exit();

/**
 * OVABRW Admin Bookings class.
 */
if ( !class_exists( 'OVABRW_Admin_Bookings' ) ) {

	class OVABRW_Admin_Bookings {

		/**
		 * Instance
		 */
		protected static $_instance = null;

		/**
		 * Constructor
		 */
		public function __construct() {
			// Add sub-menu
			add_action( 'admin_menu', [ $this, 'add_submenu' ] );
		}

		/**
		 * Add submenu
		 */
		public function add_submenu() {
			// Booking calendar
			add_submenu_page(
	            'ovabrw-settings',
	            esc_html__( 'Booking calendar', 'ova-brw' ),
	            esc_html__( 'Booking calendar', 'ova-brw' ),
	            apply_filters( OVABRW_PREFIX.'submenu_booking_calendar_capability' ,'edit_posts' ),
	            'ovabrw-booking-calendar',
	            [ $this, 'view_booking_calendar' ],
                4
	        );
		}

		/**
		 * View booking calendar
		 */
		public function view_booking_calendar() {
			include( OVABRW_PLUGIN_ADMIN . 'bookings/views/html-booking-calendar.php' );
		}

		/**
		 * Get events
		 */
		public function get_events( $product_id = '', $show_date = '', $month = '', $year = '' ) {
			// init
            $events = [];

            // Get booked order ids
            $order_ids = $this->get_booked_order_ids( $product_id, $month, $year );

            // Check products
            $check_products = [];
            if ( $product_id ) {
                $check_products = ovabrw_get_wpml_product_ids( $product_id );
            }

            if ( ovabrw_array_exists( $order_ids ) ) {
                foreach ( $order_ids as $order_id ) {
                    // Get order
                    $order = wc_get_order( $order_id );
                    if ( !$order ) continue;

                    // Get order permalink
                    $order_permalink = get_edit_post_link( $order_id );

                    // Get items
                    $items = $order->get_items();
                    if ( !ovabrw_array_exists( $items ) ) continue;

                    // Loop items
                    foreach ( $items as $item_id => $item ) {
                        // Product ID
                        $pid = method_exists( $item, 'get_product_id' ) ? $item->get_product_id() : '';

                        // Get product
                        $product = wc_get_product( $pid );
                        if ( !$product || !$product->is_type( OVABRW_RENTAL ) ) continue;
                        if ( ovabrw_array_exists( $check_products ) && !in_array( $pid , $check_products ) ) continue;

                        // Check-in date
                        $checkin_date = strtotime( $item->get_meta( 'ovabrw_pickup_date' ) );
                        if ( !$checkin_date ) continue;

                        // Check-out date
                        $checkout_date = strtotime( $item->get_meta( 'ovabrw_pickoff_date' ) );
                        if ( !$checkout_date ) continue;

                        // Show date
                        if ( 'checkin' === $show_date ) {
                            $checkout_date = $checkin_date;
                        } elseif ( 'checkout' === $show_date ) {
                            $checkin_date = $checkout_date;
                        } // END show date

                        $events[] = [
                            'title'             => $product->get_title(),
                            'start'             => gmdate( 'Y-m-d', $checkin_date ),
                            'end'               => gmdate( 'Y-m-d', $checkout_date + 86400 ),
                            'allDay'            => true,
                            'url'               => $order_permalink,
                            'backgroundColor'   => get_theme_mod( 'primary_color', '#FD4C5C' ),
                            'borderColor'       => get_theme_mod( 'primary_color', '#FD4C5C' ),
                            'textColor'         => '#FFFFFF',
                            'extendedProps'     => [
                                'htmlEvent' => $this->get_html_popup_event_data( $item, $order )
                            ]
                        ];
                    } // END loop items
                } // END loop orders
            } // END if

            return apply_filters( OVABRW_PREFIX.'booking_calendar_get_events', $events, $product_id, $show_date, $month = '', $year );
		}

		/**
		 * Get booked order ids
		 */
		public function get_booked_order_ids( $product_id = '', $month = '', $year = '' ) {
			// Date range
            $date_range = $this->get_date_range( $month, $year );

            // First day last month
            $first_day_last_month = strtotime( $date_range['first_day_last_month'] );

            // Last day next month
            $last_day_next_month = strtotime( $date_range['last_day_next_month'] );

            // Get order status
            $order_status = apply_filters( OVABRW_PREFIX.'booking_calendar_get_order_status', brw_list_order_status(), $product_id, $month, $year );

            // init
            $order_ids = [];

            // WordPress Database
            global $wpdb;

            if ( ovabrw_wc_custom_orders_table_enabled() ) {
                // Get order IDs by product ID
                if ( $product_id ) {
                    // Get product IDs multi-lang
                    $product_ids = ovabrw_get_wpml_product_ids( $product_id );

                    // Get order IDs
                    // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
                    $order_ids = $wpdb->get_col( $wpdb->prepare( "
                        SELECT DISTINCT o.id
                        FROM {$wpdb->prefix}wc_orders AS o
                        LEFT JOIN {$wpdb->prefix}woocommerce_order_items AS oi
                            ON o.id = oi.order_id
                        LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS oim1
                            ON oi.order_item_id = oim1.order_item_id
                        LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS oim2
                            ON oi.order_item_id = oim2.order_item_id
                        LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS oim3
                            ON oi.order_item_id = oim3.order_item_id
                        WHERE o.type = %s
                            AND oi.order_item_type = %s
                            AND oim1.meta_key = %s
                            AND oim1.meta_value IN (" . implode( ',', array_map( 'esc_sql', $product_ids ) ) . ")
                            AND oim2.meta_key = %s
                            AND oim2.meta_value >= %d
                            AND oim3.meta_key = %s
                            AND oim3.meta_value <= %d
                            AND o.status IN ('". implode( "','", array_map( 'esc_sql', $order_status ) ) . "')",
                        [
                            'shop_order',
                            'line_item',
                            '_product_id',
                            'ovabrw_pickup_date_strtotime',
                            $first_day_last_month,
                            'ovabrw_dropoff_date_strtotime',
                            $last_day_next_month
                        ]
                    ));
                    // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
                } else {
                    // Get order IDs
                    // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
                    $order_ids = $wpdb->get_col( $wpdb->prepare( "
                        SELECT DISTINCT o.id
                        FROM {$wpdb->prefix}wc_orders AS o
                        LEFT JOIN {$wpdb->prefix}woocommerce_order_items AS oi
                            ON o.id = oi.order_id
                        LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS oim1
                            ON oi.order_item_id = oim1.order_item_id
                        LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS oim2
                            ON oi.order_item_id = oim2.order_item_id
                        WHERE o.type = %s
                            AND oi.order_item_type = %s
                            AND oim1.meta_key = %s
                            AND oim1.meta_value >= %d
                            AND oim2.meta_key = %s
                            AND oim2.meta_value <= %d
                            AND o.status IN ('". implode( "','", array_map( 'esc_sql', $order_status ) ) . "')",
                        [
                            'shop_order',
                            'line_item',
                            'ovabrw_pickup_date_strtotime',
                            $first_day_last_month,
                            'ovabrw_dropoff_date_strtotime',
                            $last_day_next_month
                        ]
                    ));
                    // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
                }
            } else {
                // Get order IDs by product ID
                if ( $product_id ) {
                    // Get product IDs multi-lang
                    $product_ids = ovabrw_get_wpml_product_ids( $product_id );

                    // Get order IDs
                    // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
                    $order_ids = $wpdb->get_col( $wpdb->prepare( "
                        SELECT DISTINCT oi.order_id
                        FROM {$wpdb->prefix}woocommerce_order_items AS oi
                        LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS oim1
                        ON oi.order_item_id = oim1.order_item_id
                        LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS oim2
                        ON oi.order_item_id = oim2.order_item_id
                        LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS oim3
                        ON oi.order_item_id = oim3.order_item_id
                        LEFT JOIN {$wpdb->posts} AS p
                        ON oi.order_id = p.ID
                        WHERE p.post_type = %s
                        AND oi.order_item_type = %s
                        AND oim1.meta_key = %s
                        AND oim1.meta_value IN (" . implode( ',', array_map( 'esc_sql', $product_ids ) ) . ")
                        AND oim2.meta_key = %s
                        AND oim2.meta_value >= %d
                        AND oim3.meta_key = %s
                        AND oim3.meta_value <= %d
                        AND p.post_status IN ('". implode( "','", array_map( 'esc_sql', $order_status ) ) . "')",
                        [
                            'shop_order',
                            'line_item',
                            '_product_id',
                            'ovabrw_pickup_date_strtotime',
                            $first_day_last_month,
                            'ovabrw_dropoff_date_strtotime',
                            $last_day_next_month
                        ]
                    ));
                    // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
                } else {
                    // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
                    $order_ids = $wpdb->get_col( $wpdb->prepare( "
                        SELECT DISTINCT oi.order_id
                        FROM {$wpdb->prefix}woocommerce_order_items AS oi
                        LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS oim1
                        ON oi.order_item_id = oim1.order_item_id
                        LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS oim2
                        ON oi.order_item_id = oim2.order_item_id
                        LEFT JOIN {$wpdb->posts} AS p
                        ON oi.order_id = p.ID
                        WHERE p.post_type = %s
                        AND oi.order_item_type = %s
                        AND oim1.meta_key = %s
                        AND oim1.meta_value >= %d
                        AND oim2.meta_key = %s
                        AND oim2.meta_value <= %d
                        AND p.post_status IN ('". implode( "','", array_map( 'esc_sql', $order_status ) ) . "')",
                        [
                            'shop_order',
                            'line_item',
                            'ovabrw_pickup_date_strtotime',
                            $first_day_last_month,
                            'ovabrw_dropoff_date_strtotime',
                            $last_day_next_month
                        ]
                    ));
                    // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
                }
            }

            return apply_filters( OVABRW_PREFIX.'booking_calendar_get_booked_order_ids', $order_ids, $product_id, $month, $year );
		}

		/**
		 * Get date range
		 */
		public function get_date_range( $month, $year ) {
			// Current month
            if ( !$month ) $month = gmdate('m');

            // Current year
            if ( !$year ) $year = gmdate('Y');

            // First day last month
            $first_day_last_month = (new DateTime("$year-$month-01"))->modify('-1 month')->format('Y-m-01');

            // Last day next month
            $last_day_next_month = (new DateTime("$year-$month-01"))->modify('+2 month')->modify('-1 day')->format('Y-m-d');

            return (array)apply_filters( OVABRW_PREFIX.'booking_calendar_get_date_range', [
                'first_day_last_month'  => $first_day_last_month,
                'last_day_next_month'   => $last_day_next_month
            ], $month, $year );
		}

		/**
		 * Get HTML popup event data
		 */
		public function get_html_popup_event_data( $item, $order ) {
            ob_start();

            include( OVABRW_PLUGIN_ADMIN . 'bookings/views/html-popup-event-data.php' );

            $html = ob_get_contents();
            ob_end_clean();

            return apply_filters( OVABRW_PREFIX.'booking_calendar_get_html_popup_event_data', $html, $item, $order );
		}

        /**
         * Get calendar options
         */
        public function get_calendar_options() {
            // Language
            $language = apply_filters( OVABRW_PREFIX.'datepicker_language', ovabrw_get_option_setting( 'calendar_language_general', 'en-GB' ) );
            if ( apply_filters( 'wpml_current_language', NULL ) ) { // WPML
                $language = apply_filters( 'wpml_current_language', NULL );
            } elseif ( function_exists('pll_current_language') ) { // Polylang
                $language = pll_current_language();
            }

            return apply_filters( OVABRW_PREFIX.'get_booking_calendar_options', [
                'firstDay'      => (int)ovabrw_get_option_setting( 'calendar_first_day', 1 ),
                'headerToolbar' => [
                    'left'  => 'title',
                    'right' => 'prev,next,today,dayGridMonth,timeGridWeek,timeGridDay,listWeek'
                ],
                'dayMaxEvents'  => 3,
                'lang'          => $language,
                'initialView'   => 'dayGridMonth',
                'buttonText'    => [
                    'today' => esc_html__( 'today', 'ova-brw' ),
                    'month' => esc_html__( 'month', 'ova-brw' ),
                    'week'  => esc_html__( 'week', 'ova-brw' ),
                    'day'   => esc_html__( 'day', 'ova-brw' ),
                    'list'  => esc_html__( 'list', 'ova-brw' )
                ],
                'allDayText'    => esc_html__( 'all-day', 'ova-brw' )
            ]);
        }

		/**
		 * Main booking calendar instance
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}
	}

	// init class
	new OVABRW_Admin_Bookings();
}