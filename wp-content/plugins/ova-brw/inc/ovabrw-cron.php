<?php if ( !defined( 'ABSPATH' ) ) exit();

/**
 * Class Ovabrw_Mail
 */
if ( !class_exists( 'Ovabrw_Cron' ) ) {
	
	class Ovabrw_Cron {
		// Reminder
		public $hook_remind_pickup_date 		= 'ovabrw_cron_hook_remind_pickup_date';
		public $time_repeat_remind_pickup_date 	= 'time_repeat_remind_pickup_date';

		// Remaining Invoice
		public $hook_remaining_invoice 			= 'ovabrw_cron_hook_remaining_invoice';
		public $time_repeat_remaining_invoice 	= 'time_repeat_remaining_invoice';

		/**
		 * Constructor.
		 */
		public function __construct() {
			// Add cron schedules
			add_filter( 'cron_schedules', [ $this, 'ovabrw_add_cron_interval' ] );

			// Check cron scheduled
			add_action( 'init', [ $this, 'ovabrw_check_scheduled' ] );

			// Register deactivation hook
			register_deactivation_hook( __FILE__, [ $this, 'ovabrw_deactivate_cron' ] );

			// Remind pick-up date
			add_action( $this->hook_remind_pickup_date, [ $this, 'ovabrw_remind_event_time' ] );

			// Remaining invoice
			add_action( $this->hook_remaining_invoice, [ $this, 'ovabrw_remaining_invoice_event_time' ] );
		}

		/**
		 * Add cron interval
		 */
		public function ovabrw_add_cron_interval( $schedules ) {
			// Reminder
			$remind_mail_send_per_seconds = intval( get_option( 'remind_mail_send_per_seconds', 86400 ) );
		    $schedules[$this->time_repeat_remind_pickup_date] = [
		    	'interval' 	=> $remind_mail_send_per_seconds,
		        'display' 	=> sprintf( esc_html__( 'Every %s seconds', 'ova-brw' ), $remind_mail_send_per_seconds )
		    ];

		    // Remaining Invoice
		    $remaining_invoice_per_seconds = intval( get_option( 'remaining_invoice_per_seconds', 86400 ) );
		    $schedules[$this->time_repeat_remaining_invoice] = [
		    	'interval' 	=> $remaining_invoice_per_seconds,
		        'display' 	=> sprintf( esc_html__( 'Every %s seconds', 'ova-brw' ), $remaining_invoice_per_seconds )
		    ];

		    return $schedules;
		}

		/**
		 * Check cron scheduled
		 */
		public function ovabrw_check_scheduled() {
			if ( !wp_next_scheduled( $this->hook_remind_pickup_date ) ) {
			    wp_schedule_event( time(), $this->time_repeat_remind_pickup_date, $this->hook_remind_pickup_date );
			}

			if ( !wp_next_scheduled( $this->hook_remaining_invoice ) ) {
			    wp_schedule_event( time(), $this->time_repeat_remaining_invoice, $this->hook_remaining_invoice );
			}
		}

		/**
		 * Deactivate cron
		 */
		public function ovabrw_deactivate_cron() {
			// Reminder
		    $timestamp_next_remind_pickup_date = wp_next_scheduled( $this->hook_remind_pickup_date );
		    wp_unschedule_event( $timestamp_next_remind_pickup_date, $this->hook_remind_pickup_date );

		    // Remaining Invoice
		    $timestamp_next_remaining_invoice = wp_next_scheduled( $this->hook_remaining_invoice );
		    wp_unschedule_event( $timestamp_next_remaining_invoice, $this->hook_remaining_invoice );
		}

		/**
		 * Remind event time
		 */
		public function ovabrw_remind_event_time() {
			if ( 'yes' != get_option( 'remind_mail_enable', 'no' ) ) return;

			// Send mail before X days
			$send_x_day = intval( ovabrw_get_setting( get_option( 'remind_mail_before_xday', 1 ) ) );

			// Send mail before X times
			$send_before_x_time = current_time('timestamp') + $send_x_day*24*60*60;

			// Get order ids
			$order_ids = ovabrw_get_orders_feature();
			if ( ovabrw_array_exists( $order_ids ) ) {
				foreach ( $order_ids as $key => $order_id ) {
					$order = wc_get_order( $order_id );

					// Get billing mail
					$customer_mail = $order->get_billing_email();

					// Get Meta Data type line_item of Order
	    	    	$order_line_items = $order->get_items( 'line_item' );
					foreach ( $order_line_items as $item_id => $item ) {
						$product_name 	= $item->get_name();
						$product_id 	= $item->get_product_id();
						$pickup_date 	= $item->get_meta( 'ovabrw_pickup_date' );

						if ( strtotime( $pickup_date ) > current_time('timestamp') && strtotime( $pickup_date ) < $send_before_x_time && apply_filters( 'ovabrw_reminder_other_condition', true, $item ) ) {
	                        ovabrw_mail_remind_event_time( $order, $customer_mail, $product_name, $product_id, $pickup_date );
	                    }
					}
				} // END foreach
			} // END if
		}

		/**
		 * Remaining invoice
		 */
		public function ovabrw_remaining_invoice_event_time() {
			if ( 'yes' !== get_option( 'remaining_invoice_enable', 'no' ) ) return;

			// Send email X day
			$send_x_day = intval( get_option( 'remaining_invoice_before_xday', 1 ) );

			// Send email
			$send_email = get_option( 'send_email_remaining_invoice_enable', 'yes' );

			// Send email before X time
			$send_before_x_time = current_time('timestamp') + $send_x_day*24*60*60;

			// Get order ids
			$order_ids = ovabrw_get_orders_not_remaining_invoice();
			if ( ovabrw_array_exists( $order_ids ) ) {
				foreach ( $order_ids as $order_id ) {
					$order = wc_get_order( $order_id );

					foreach ( $order->get_items() as $item_id => $item ) {
						// Get item remaining amount
            			$item_remaining = floatval( $item->get_meta( 'ovabrw_remaining_amount' ) );

            			// Get item remaining invoice id
            			$remaining_invoice = absint( $item->get_meta( 'ovabrw_remaining_balance_order_id' ) );

				        if ( !$item || !$item_remaining || $remaining_invoice || strtotime( $item['ovabrw_pickup_date'] ) > $send_before_x_time || strtotime( $item['ovabrw_pickup_date'] ) < current_time( 'timestamp' ) ) {
					        continue;
					    }

            			// Get item remaining tax amount
			            $item_remaining_tax = floatval( $item->get_meta( 'ovabrw_remaining_tax' ) );

			            // Get remaining insurance amount
			            $remaining_insurance = floatval( $item->get_meta( 'ovabrw_remaining_insurance' ) );

			            // Get remaining insurance tax amount
			            $remaining_insurance_tax = floatval( $item->get_meta( 'ovabrw_remaining_insurance_tax' ) );

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

			            $data_item = array(
			                'product'   => $item->get_product(),
			                'qty'       => $item['qty'],
			                'subtotal'  => $item_remaining,
			                'total'     => $item_remaining
			            );

			            if ( $item_remaining_tax ) {
		                    $data_item['remaining_tax'] = $item_remaining_tax;
		                }
		                if ( $remaining_insurance ) {
		                    $data_item['insurance_amount'] = $remaining_insurance;
		                }
		                if ( $remaining_insurance_tax ) {
		                    $data_item['insurance_tax'] = $remaining_insurance_tax;
		                }

			            $new_order_id = ovabrw_create_remaining_invoice( $order_id, $data_item );
			            $new_order = wc_get_order( $new_order_id );
		                $new_order->add_meta_data( '_ova_original_id', $order_id );
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

			            if ( $send_email === 'yes' ) {
			                // Email invoice
			                $emails = WC_Emails::instance();
			                $emails->customer_invoice( wc_get_order( $new_order_id ) );
			            }
				    }
				}
			}
		}
	}

	new Ovabrw_Cron();
}