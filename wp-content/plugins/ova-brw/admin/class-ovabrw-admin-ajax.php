<?php if ( !defined( 'ABSPATH' ) ) exit();

/**
 * Class OVABRW_Admin_AJAX
 */
if ( !class_exists( 'OVABRW_Admin_AJAX', false ) ) {

	class OVABRW_Admin_AJAX {

		/**
		 * Constructor
		 */
		public function __construct() {
			// Define All Ajax function
			$ajaxs = [
				'update_order_status_woo',
				'ovabrw_get_custom_tax_in_cat',
				'ovabrw_update_insurance',
				'ovabrw_create_order_render_guest_types',
				'ovabrw_create_order_add_guest_info_item',
				'ovabrw_booking_calendar_get_events',
				'ovabrw_preview_booking'
			];

			// Loop
			foreach ( $ajaxs as $action ) {
				add_action( 'wp_ajax_'.$action, [ $this, $action ] );
				add_action( 'wp_ajax_nopriv_'.$action, [ $this, $action ] );
			} // END
		}

		/**
		 * Schedule Ajax
		 */
		public static function update_order_status_woo() {
			// Get order ID
			$order_id = sanitize_text_field( ovabrw_get_meta_data( 'order_id', $_POST ) );

			// New status
			$new_status = sanitize_text_field( ovabrw_get_meta_data( 'new_order_status', $_POST ) );

			if ( $order_id && $new_status ) {
				// Get order
				$order = wc_get_order( $order_id );

				if ( !current_user_can( apply_filters( OVABRW_PREFIX.'update_order_status' ,'publish_posts' ) ) ) {
					echo 'error_permission';	
				} elseif ( $order->update_status( $new_status ) ) {
					echo 'true';
				} else {
					echo 'false';
				}
			} else {
				echo 'false';
			}
			
			wp_die();
		}

		/**
		 * Get Custom Taxonomy choosed in Category
		 */
		public static function ovabrw_get_custom_tax_in_cat() {
			// Taxonomy
			$checked_tax = ovabrw_get_meta_data( 'checked_tax', $_POST, [] );
			
			// init
			$list_tax_values = [];

			// Loop
			if ( ovabrw_array_exists( $checked_tax ) ) {
				foreach ( $checked_tax as $key => $term_id ) {
					$ovabrw_custom_tax = get_term_meta( $term_id, 'ovabrw_custom_tax', true );
					if ( ovabrw_array_exists( $ovabrw_custom_tax ) ) {
						foreach ( $ovabrw_custom_tax as $key => $value ) {
							if ( !in_array( $value, $list_tax_values ) ) {
								if ( $value ) array_push( $list_tax_values, $value );
							}
						}
					}
				}
			} // END loop
			
			echo implode( ',', $list_tax_values ); 
			wp_die();
		}

		/**
		 * Update insurance amount
		 */
		public function ovabrw_update_insurance() {
			// Get order id
			$order_id = (int)ovabrw_get_meta_data( 'order_id', $_POST );
			if ( !$order_id ) wp_die();

			// Get item id
			$item_id = (int)ovabrw_get_meta_data( 'item_id', $_POST );
			if ( !$item_id ) wp_die();

			// Get insurance amount
			$amount = (float)ovabrw_get_meta_data( 'amount', $_POST );
			if ( $amount < 0 ) wp_die();

			// Get tax
			$tax = (float)ovabrw_get_meta_data( 'tax', $_POST );
			if ( $tax < 0 ) wp_die();

			// Get order id
			$order = wc_get_order( $order_id );
            if ( !$order ) wp_die();

			$item = WC_Order_Factory::get_order_item( absint( $item_id ) );
            if ( !$item ) wp_die();

            // Insurance key
           	$insurance_key = $order->get_meta( '_ova_insurance_key' );

            // Total insurance
            $order_insurance 		= floatval( $order->get_meta( '_ova_insurance_amount' ) );
            $order_insurance_tax 	= floatval( $order->get_meta( '_ova_insurance_tax' ) );

            // Item insurance
            $item_insurance = floatval( $item->get_meta( 'ovabrw_insurance_amount' ) );

            // Item insurance tax
            $item_insurance_tax = floatval( $item->get_meta( 'ovabrw_insurance_tax' ) );

            // Original order and item
            $original_order 	= $original_item = false;
            $original_item_id 	= $order->get_meta( '_ova_original_item_id' );

            if ( absint( $original_item_id ) ) {
            	$original_item = WC_Order_Factory::get_order_item( absint( $original_item_id ) );
            	if ( $original_item ) $original_order = $original_item->get_order();
            }

            // Get fees
            $fees = $order->get_fees();
            if ( ovabrw_array_exists( $fees ) ) {
            	foreach ( $fees as $item_fee_id => $item_fee ) {
            		$fee_key = sanitize_title( $item_fee->get_name() );

            		if ( $fee_key === $insurance_key ) {
            			$order_insurance -= $item_insurance;
            			$order_insurance += $amount;

            			$order_insurance_tax -= $item_insurance_tax;
            			$order_insurance_tax += $tax;

            			if ( $order_insurance < 0 ) $order_insurance = 0;
            			if ( $order_insurance_tax < 0 ) $order_insurance_tax = 0;

            			// Update item fee
            			if ( wc_tax_enabled() ) {
                            $order_taxes = $order->get_taxes();
                            $tax_item_id = 0;

                            foreach ( $order_taxes as $tax_item ) {
                                $tax_item_id = $tax_item->get_rate_id();
                                if ( $tax_item_id ) break;
                            }

                            // Set props
                            $item_fee->set_props([
                            	'total'     => $order_insurance,
								'subtotal'  => $order_insurance,
								'total_tax' => $order_insurance_tax,
								'taxes'     => [
									'total' => [ $tax_item_id => $order_insurance_tax ]
								]
                            ]);

                            // Update original item
                            if ( $original_item ) {
                            	// Get original item remaining insurance amount
                            	$item_remaining_insurance = floatval( $original_item->get_meta( 'ovabrw_remaining_insurance' ) );

                            	// Get original item remaining insurance tax amount
                            	$item_remaining_insurance_tax = floatval( $original_item->get_meta( 'ovabrw_remaining_insurance_tax' ) );

                            	// Update original item meta data
                            	$original_item->update_meta_data( 'ovabrw_remaining_insurance', $order_insurance );
                            	$original_item->update_meta_data( 'ovabrw_remaining_insurance_tax', $order_insurance_tax );
                            	$original_item->save();

                            	// Update original order
	                            if ( $original_order ) {
	                            	// Get original order remaining insurance amount
	                            	$order_remaining_insurance = floatval( $original_order->get_meta( '_ova_remaining_insurance' ) );
	                            	$order_remaining_insurance -= $item_remaining_insurance;
	                            	$order_remaining_insurance += $order_insurance;

	                            	// Get original order remaining insurance tax amount
	                            	$order_remaining_insurance_tax = floatval( $original_order->get_meta( '_ova_remaining_insurance_tax' ) );
	                            	$order_remaining_insurance_tax -= $item_remaining_insurance_tax;
	                            	$order_remaining_insurance_tax += $order_insurance_tax;

	                            	// Update original order meta data
	                            	$original_order->update_meta_data( '_ova_remaining_insurance', $order_remaining_insurance );
	                            	$original_order->update_meta_data( '_ova_remaining_insurance_tax', $order_remaining_insurance_tax );
	                            	$original_order->save();
	                            }
                            }
            			} else {
            				$item_fee->set_props([
            					'total'     => $order_insurance,
								'subtotal'  => $order_insurance
            				]);

							// Update original order and item
                            if ( $original_item ) {
                            	// Get original item remaining insurance amount
                            	$item_remaining_insurance = floatval( $original_item->get_meta( 'ovabrw_remaining_insurance' ) );

                            	// Update original item meta data
                            	$original_item->update_meta_data( 'ovabrw_remaining_insurance', $order_insurance );
                            	$original_item->save();

                            	// Update original order
	                            if ( $original_order ) {
	                            	// Get original order remaining insurance amount
	                            	$order_remaining_insurance = floatval( $original_order->get_meta( '_ova_remaining_insurance' ) );
	                            	$order_remaining_insurance -= $item_remaining_insurance;
	                            	$order_remaining_insurance += $order_insurance;

	                            	// Update original order meta data
	                            	$original_order->update_meta_data( '_ova_remaining_insurance', $order_remaining_insurance );
	                            	$original_order->save();
	                            }
                            }
            			}

            			$item_fee->set_amount( $order_insurance );
            			$item_fee->save();

            			// Update item insurance
        				$item->update_meta_data( 'ovabrw_insurance_amount', $amount );
        				$item->update_meta_data( 'ovabrw_insurance_tax', $tax );
        				$item->save();

        				// Update order insurance
        				$order->update_meta_data( '_ova_insurance_amount', $order_insurance );
        				$order->update_meta_data( '_ova_insurance_tax', $order_insurance_tax );
        				$order->update_taxes();
        				$order->calculate_totals( false );
            		}
            	}
            }

            wp_die();
		}

		/**
		 * Render guest types
		 */
		public function ovabrw_create_order_render_guest_types() {
			// Get product ID
			$product_id = absint( ovabrw_get_meta_data( 'product_id', $_POST ) );

			// Guests
			$guests = ovabrw_get_meta_data( 'guests', $_POST );
			
			// Render HTML
			ob_start();

			// Get template
			include( OVABRW_PLUGIN_PATH.'admin/order/fields/guest-types.php' );

			$html = ob_get_contents();
			ob_end_clean();

			echo wp_json_encode([
				'html' => $html
			]);

			wp_die();
		}

		/**
		 * Add guest info item
		 */
		public function ovabrw_create_order_add_guest_info_item() {
			// Get product ID
			$product_id = absint( ovabrw_get_meta_data( 'product_id', $_POST ) );

			// Guest name
			$guest_name = ovabrw_get_meta_data( 'guest_name', $_POST );

			// Number of guests
			$key = (int)ovabrw_get_meta_data( 'numberof_guests', $_POST );
			$key -= 1;
			if ( $key < 0 ) wp_die();

			// Render HTML
			ob_start();

			include( OVABRW_PLUGIN_PATH.'admin/order/fields/guest-info.php' );

			$html = ob_get_contents();
			ob_end_clean();

			echo wp_json_encode([
				'html' => $html
			]);

			wp_die();
		}

		/**
		 * Booking calendar get events
		 */
		public function ovabrw_booking_calendar_get_events() {
			// Product ID
		    $product_id = (int)ovabrw_get_meta_data( 'product_id', $_POST );

		    // Show date
		    $show_date = ovabrw_get_meta_data( 'show_date', $_POST );

		    // Current month
		    $current_month = ovabrw_get_meta_data( 'current_month', $_POST );

		    // Current year
		    $current_year = ovabrw_get_meta_data( 'current_year', $_POST );

		    // Get events
		    $events = OVABRW_Admin_Bookings::instance()->get_events( $product_id, $show_date, $current_month, $current_year );

			echo json_encode([
				'events' => $events
			]);
			wp_die();
		}

		/**
		 * Preview booking
		 */
		public function ovabrw_preview_booking() {
			// Get order id
			$order_id = (int)ovabrw_get_meta_data( 'order_id', $_POST );
			if ( !current_user_can( 'edit_shop_orders' ) || !$order_id ) {
				wp_die(-1);
			}

			$order = wc_get_order( $order_id );

			if ( $order ) {
				include_once WC_ABSPATH . 'includes/admin/list-tables/class-wc-admin-list-table-orders.php';

				// Get order data
				$order_data = WC_Admin_List_Table_Orders::order_preview_get_order_details( $order );

				wp_send_json_success( $order_data );
			}

			wp_die();
		}
	}

	new OVABRW_Admin_AJAX();
}