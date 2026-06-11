<?php if ( !defined( 'ABSPATH' ) ) exit();

/**
 * Create new booking
 */
if ( !function_exists( 'ovabrw_create_new_booking' ) ) {
	function ovabrw_create_new_booking() {
		// Get tour product ids
		$product_ids = ovabrw_get_tour_product_ids();

		// Defautl country
	    $country_setting = get_option( 'woocommerce_default_country', 'US:CA' );
		if ( strstr( $country_setting, ':' ) ) {
			$country_setting = explode( ':', $country_setting );
			$country         = current( $country_setting );
			$state           = end( $country_setting );
		} else {
			$country = $country_setting;
			$state   = '*';
		}

		?>
		<div class="wrap">
		    <form id="booking-filter" method="POST" enctype="multipart/form-data" action="<?php echo esc_url( get_admin_url( null, 'admin.php?page=ovabrw-create-order') ); ?>" autocomplete="off">
		    	<h2><?php esc_html_e( 'Create new booking', 'ova-brw' ); ?></h2>
		    	<div class="ovabrw-wrap">
		    	<?php
	    			// Multi currency
	    			$currencies = [];

	    			if ( is_plugin_active( 'woo-multi-currency/woo-multi-currency.php' ) ) {
	    				$setting 	= WOOMULTI_CURRENCY_F_Data::get_ins();
	    				$currencies = $setting->get_list_currencies();
	    			}
	    			if ( is_plugin_active( 'woocommerce-multi-currency/woocommerce-multi-currency.php' ) ) {
	    				$setting 	= WOOMULTI_CURRENCY_Data::get_ins();
	    				$currencies = $setting->get_list_currencies();
	    			}
	    			if ( is_plugin_active( 'woocommerce-multilingual/wpml-woocommerce.php' ) ) {
	    				// WPML multi currency
			    		global $woocommerce_wpml;

			    		if ( $woocommerce_wpml && is_object( $woocommerce_wpml ) ) {
			    			if ( wp_doing_ajax() ) add_filter( 'wcml_load_multi_currency_in_ajax', '__return_true' );

					        $currencies 	= $woocommerce_wpml->get_setting( 'currency_options' );
					        $current_lang 	= apply_filters( 'wpml_current_language', NULL );

					        if ( $current_lang != 'all' && ! empty( $currencies ) && is_array( $currencies ) ) {
					        	foreach ( $currencies as $k => $data ) {
					        		if ( isset( $currencies[$k]['languages'][$current_lang] ) && $currencies[$k]['languages'][$current_lang] ) {
					        			continue;
					        		} else {
					        			unset( $currencies[$k] );
					        		}
					        	}
					        }
					    }
	    			}

	    			if ( ovabrw_array_exists( $currencies ) ):
						$current_currency = ovabrw_get_meta_data( 'currency', $_GET );
						if ( !$current_currency ) $current_currency = array_key_first( $currencies ); 
					?>
						<div class="ovabrw-row ovabrw-order-currency">
			    			<label for="ovabrw-currency">
			    				<?php esc_html_e( 'Currency', 'ova-brw' ); ?>
			    			</label>
			    			<select name="currency" id="ovabrw-currency" class="ovabrw-input-required">
					    		<?php foreach ( $currencies as $currency => $rate ): ?>
				    				<option value="<?php echo esc_attr( $currency ); ?>"<?php selected( $currency, $current_currency ); ?>>
				    					<?php echo esc_html( $currency ); ?>
				    				</option>
						    	<?php endforeach; ?>
				    		</select>
				    		<?php ovabrw_wp_text_input([
				    			'type' 	=> 'hidden',
				    			'name' 	=> 'ovabrw-admin-url',
				    			'value' => esc_url( get_admin_url( null, 'admin.php?page=ovabrw-create-order' ) )
				    		]); ?>
			    		</div>
				    <?php endif; ?>
		    		<div class="ovabrw-row ovabrw-choose-order-status">
		    			<label for="stattus-order">
		    				<?php esc_html_e( 'Status', 'ova-brw' ) ?>
		    			</label>
		    			<select name="status_order" id="stattus-order" class="ovabrw-input-required">
		    				<option value="completed" selected >
		    					<?php esc_html_e( 'Completed', 'ova-brw' ); ?>
		    				</option>
		    				<option value="processing">
		    					<?php esc_html_e( 'Processing', 'ova-brw' ); ?>
		    				</option>
		    				<option value="pending">
		    					<?php esc_html_e( 'Pending payment', 'ova-brw' ); ?>
		    				</option>
		    				<option value="on-hold">
		    					<?php esc_html_e( 'On hold', 'ova-brw' ); ?>
		    				</option>
		    				<option value="cancelled">
		    					<?php esc_html_e( 'Cancelled', 'ova-brw' ); ?>
		    				</option>
		    				<option value="refunded">
		    					<?php esc_html_e( 'Refunded', 'ova-brw' ); ?>
		    				</option>
		    				<option value="failed">
		    					<?php esc_html_e( 'Failed', 'ova-brw' ); ?>
		    				</option>
		    			</select>
		    		</div>
		            <div class="ovabrw-row ova-column-3">
		            	<div class="item">
		            		<?php ovabrw_wp_text_input([
		            			'type' 			=> 'text',
		            			'class' 		=> 'ovabrw-input-required',
		            			'name' 			=> 'ovabrw_first_name',
		            			'placeholder' 	=> esc_html__( 'First Name', 'ova-brw' )
		            		]); ?>
		            	</div>
		            	<div class="item">
		            		<?php ovabrw_wp_text_input([
		            			'type' 			=> 'text',
		            			'class' 		=> 'ovabrw-input-required',
		            			'name' 			=> 'ovabrw_last_name',
		            			'placeholder' 	=> esc_html__( 'Last Name', 'ova-brw' )
		            		]); ?>
		            	</div>
		            	<div class="item">
		            		<?php ovabrw_wp_text_input([
		            			'type' 			=> 'text',
		            			'name' 			=> 'ovabrw_company',
		            			'placeholder' 	=> esc_html__( 'Company', 'ova-brw' )
		            		]); ?>
		            	</div>
		            	<div class="item">
		            		<?php ovabrw_wp_text_input([
		            			'type' 			=> 'email',
		            			'class' 		=> 'ovabrw-input-required',
		            			'name' 			=> 'ovabrw_email',
		            			'placeholder' 	=> esc_html__( 'Email', 'ova-brw' )
		            		]); ?>
		            	</div>
		            	<div class="item">
		            		<?php ovabrw_wp_text_input([
		            			'type' 			=> 'text',
		            			'class' 		=> 'ovabrw-input-required',
		            			'name' 			=> 'ovabrw_phone',
		            			'placeholder' 	=> esc_html__( 'Phone', 'ova-brw' )
		            		]); ?>
		            	</div>
		            	<div class="item">
		            		<?php ovabrw_wp_text_input([
		            			'type' 			=> 'text',
		            			'class' 		=> 'ovabrw-input-required',
		            			'name' 			=> 'ovabrw_address_1',
		            			'placeholder' 	=> esc_html__( 'Address 1', 'ova-brw' )
		            		]); ?>
		            	</div>
		            	<div class="item">
		            		<?php ovabrw_wp_text_input([
		            			'type' 			=> 'text',
		            			'name' 			=> 'ovabrw_address_2',
		            			'placeholder' 	=> esc_html__( 'Address 2', 'ova-brw' ),
		            		]); ?>
		            	</div>
		            	<div class="item">
		            		<?php ovabrw_wp_text_input([
		            			'type' 			=> 'text',
		            			'class' 		=> 'ovabrw-input-required',
		            			'name' 			=> 'ovabrw_city',
		            			'placeholder' 	=> esc_html__( 'City', 'ova-brw' )
		            		]); ?>
		            	</div>
		            	<div class="item">
		            		<select name="ovabrw_country" class="ovabrw_country ovabrw-input-required" style="width: 100%;">
								<?php WC()->countries->country_dropdown_options( $country, $state ); ?>
							</select>
		            	</div>
		            </div>
		            <div class="wrap_item">
		            	<?php include( OVABRW_PLUGIN_ADMIN . 'bookings/views/html-create-booking-item.php' ); ?>
		            </div>
					<div class="ovabrw-row">
						<a href="#" class="button insert_wrap_item" data-row="<?php
			                ob_start();
			                include( OVABRW_PLUGIN_ADMIN . 'bookings/views/html-create-booking-item.php' );
			                echo esc_attr( ob_get_clean() );
			            ?>">
						<?php esc_html_e( 'Add Item', 'ova-brw' ); ?></a>
						</a>
					</div>
					<button type="submit" class="button btn-add-new-booking">
						<?php esc_html_e( 'Create new booking', 'ova-brw' ); ?>
					</button>
					<span class="create-order-error"></span>
		    	</div>
		        <?php ovabrw_wp_text_input([
	                'type'  => 'hidden',
	                'name'  => 'ovabrw_create_order',
	                'value' => 'create_order'
	            ]); ?>
		        <?php ovabrw_wp_text_input([
	                'type'  => 'hidden',
	                'name'  => 'page',
	                'value' => 'ovabrw-create-order'
	            ]); ?>
		    </form>
		</div>
		<?php 
	}
}

/**
 * Manage booking
 */
if ( !function_exists( 'ovabrw_manage_booking' ) ) {
	function ovabrw_manage_booking() {
		// Get list booking
	    $manage_booking = new OVABRW_List_Booking();

	    // Fetch, prepare, sort, and filter our data...
	    $manage_booking->prepare_items();

	    // Get product ids
	    $product_ids    = ovabrw_get_tour_product_ids();
	    $order_id       = ovabrw_get_meta_data( 'order_id', $_GET );
	    $customer_name  = ovabrw_get_meta_data( 'customer_name', $_GET );
	    $checkin_date   = ovabrw_get_meta_data( 'checkin_date', $_GET );
	    $checkout_date  = ovabrw_get_meta_data( 'checkout_date', $_GET );
	    $product_id     = ovabrw_get_meta_data( 'product_id', $_GET );
	    $order_status   = ovabrw_get_meta_data( 'order_status', $_GET );


	    // Admin manage order show columns
	    $id         = get_option( 'admin_manage_order_show_id', 1 );
	    $customer   = get_option( 'admin_manage_order_show_customer', 2 );
	    $time       = get_option( 'admin_manage_order_show_time', 3 );
	    $deposit    = get_option( 'admin_manage_order_show_deposit', 4 );
	    $insurance  = get_option( 'admin_manage_order_show_insurance', 5 );
	    $product    = get_option( 'admin_manage_order_show_product', 6 );
	    $status     = get_option( 'admin_manage_order_show_order_status', 7 );

	    ?>
	    <div class="ovabrw-manage-order-wrap wrap">
	        <form id="booking-filter" method="GET" action="<?php echo esc_url( get_admin_url( null, 'admin.php?page=ovabrw-manage-order') ); ?>" autocomplete="off">
	        	<h2><?php esc_html_e( 'Manage bookings', 'ova-brw' ); ?></h2>
	        	<div class="booking_filter">
	            <?php
	                // Order ID
	                if ( $id ) {
	                    ovabrw_admin_text_input([
	                        'type'          => 'text',
	                        'name'          => 'order_id',
	                        'value'         => $order_id,
	                        'placeholder'   => esc_html__( 'Booking ID', 'ova-brw' )
	                    ]);
	                }

	                // Customer
	                if ( $customer ) {
	                    ovabrw_admin_text_input([
	                        'type'          => 'text',
	                        'name'          => 'customer_name',
	                        'value'         => $customer_name,
	                        'placeholder'   => esc_html__( 'Customer Name', 'ova-brw' )
	                    ]);
	                }

	                // Check-in & Check-out dates
	                if ( $time ) {
	                    ovabrw_admin_text_input([
	                        'type'          => 'text',
	                        'id'            => ovabrw_unique_id( 'checkin_date' ),
	                        'class'         => 'start-date',
	                        'name'          => 'checkin_date',
	                        'value'         => $checkin_date,
	                        'placeholder'   => esc_html__( 'Check-in Date', 'ova-brw' ),
	                        'data_type'     => 'datepicker'
	                    ]);
	                    ovabrw_admin_text_input([
	                        'type'          => 'text',
	                        'id'            => ovabrw_unique_id( 'checkout_date' ),
	                        'class'         => 'end-date',
	                        'name'          => 'checkout_date',
	                        'value'         => $checkout_date,
	                        'placeholder'   => esc_html__( 'Check-out Date', 'ova-brw' ),
	                        'data_type'     => 'datepicker'
	                    ]);
	                }

	                // Product
	                if ( $product ): ?>
	            		<select name="product_id">
	            			<option value="">
	                            <?php esc_html_e( 'Select product ...', 'ova-brw' ); ?>
	                        </option>
	            			<?php if ( ovabrw_array_exists( $product_ids ) ):
	                            foreach ( $product_ids as $pid ): ?>
	            					<option value="<?php echo esc_attr( $pid ); ?>"<?php ovabrw_selected( $pid, $product_id ); ?>>
	                                    <?php echo esc_html( get_the_title( $pid ) ); ?>
	                                </option>
	                            <?php endforeach;
	                        endif; ?>
	            		</select>
	                <?php endif;

	                // Order status
	                if ( $status ): ?>
	                    <select name="order_status" >
	                        <option value="">
	                            <?php esc_html_e( 'Select status ...', 'ova-brw' ); ?>
	                        </option>
	                        <option value="wc-completed"<?php selected( 'wc-completed', $order_status ); ?>>
	                            <?php esc_html_e( 'Completed', 'ova-brw' ); ?>
	                        </option>
	                        <option value="wc-processing"<?php selected( 'wc-processing', $order_status ); ?>>
	                            <?php esc_html_e( 'Processing', 'ova-brw' ); ?>
	                        </option>
	                        <option value="wc-pending"<?php selected( 'wc-pending', $order_status ); ?>>
	                            <?php esc_html_e( 'Pending payment', 'ova-brw' ); ?>
	                        </option>
	                        <option value="wc-on-hold"<?php selected( 'wc-on-hold', $order_status ); ?>>
	                            <?php esc_html_e( 'On hold', 'ova-brw' ); ?>
	                        </option>
	                        <option value="wc-cancelled"<?php selected( 'wc-cancelled', $order_status ); ?>>
	                            <?php esc_html_e( 'Cancel', 'ova-brw' ); ?>
	                        </option>
	                        <option value="wc-closed"<?php selected( 'wc-closed', $order_status ); ?>>
	                            <?php esc_html_e( 'Closed', 'ova-brw' ); ?>
	                        </option>
	                    </select>
	                <?php endif; ?>
	    			<button type="submit" class="button">
	                    <?php esc_html_e( 'Filter', 'ova-brw' ); ?>
	                </button>
	                <button type="submit" id="clean-search" class="button">
	                    <?php esc_html_e( 'Clear', 'ova-brw' ); ?>
	                </button>
	        	</div>
	            <?php $manage_booking->display();
	            ovabrw_wp_text_input([
	                'type'  => 'hidden',
	                'name'  => 'page',
	                'value' => 'ovabrw-manage-order'
	            ]); ?>
	        </form>
	        <div class="ovabrw-backbone-modal ovabrw-order-preview">
				<div class="ovabrw-backbone-modal-content">
					<section class="ovabrw-backbone-modal-main" role="main">
						<header class="ovabrw-backbone-modal-header">
							<mark class="order-status"><span></span></mark>
							<h1 class="order-number">
								<?php esc_html_e( 'Order #', 'ova-brw' ); ?>
								<span></span>
							</h1>
							<button class="modal-close modal-close-link dashicons dashicons-no-alt">
								<span class="screen-reader-text">
									<?php esc_html_e( 'Close modal panel', 'ova-brw' ); ?>
								</span>
							</button>
						</header>
						<article>
							<?php do_action( OVABRW_PREFIX.'admin_order_preview_start' ); ?>
							<div class="ovabrw-order-preview-addresses">
								<div class="ovabrw-order-preview-address">
									<h2>
										<?php esc_html_e( 'Billing details', 'ova-brw' ); ?>
									</h2>
									<div class="ovabrw-formatted-billing-address"></div>
									<div class="ovabrw-billing-email">
										<strong>
											<?php esc_html_e( 'Email', 'ova-brw' ); ?>
										</strong>
										<a href="#"></a>
									</div>
									<div class="ovabrw-billing-phone">
										<strong>
											<?php esc_html_e( 'Phone', 'ova-brw' ); ?>
										</strong>
										<a href="#"></a>
									</div>
									<div class="ovabrw-payment-via">
										<strong>
											<?php esc_html_e( 'Payment via', 'ova-brw' ); ?>
										</strong>
										<span></span>
									</div>
								</div>
								<div class="ovabrw-needs-shipping">
									<div class="ovabrw-order-preview-address">
										<h2>
											<?php esc_html_e( 'Shipping details', 'ova-brw' ); ?>
										</h2>
										<div class="ovabrw-formatted-shipping-address"></div>
										<div class="ovabrw-ship-to-billing">
											<a href="#"></a>
										</div>
										<div class="ovabrw-shipping-phone">
											<strong>
												<?php esc_html_e( 'Phone', 'ova-brw' ); ?>
											</strong>
											<a href="#"></a>
										</div>
										<div class="ovabrw-shipping-via">
											<strong>
												<?php esc_html_e( 'Shipping method', 'ova-brw' ); ?>
											</strong>
											<span></span>
										</div>
									</div>
								</div>
								<div class="ovabrw-order-preview-note">
									<strong>
										<?php esc_html_e( 'Note', 'ova-brw' ); ?>
									</strong>
									<span></span>
								</div>
							</div>
							<div class="ovabrw-item-html"></div>
							<div class="ovabrw-order-totals"></div>
							<?php do_action( OVABRW_PREFIX.'admin_order_preview_end' ); ?>
						</article>
						<footer>
							<div class="inner">
								<div class="ovabrw-footer-actions"></div>
								<a class="button button-primary button-large ovabrw-edit-order" aria-label="<?php esc_attr_e( 'Edit this order', 'ova-brw' ); ?>" href="#" data-edit-order-url="<?php echo esc_url( admin_url( 'admin.php?page=wc-orders&action=edit' ) ) . '&id=[order_id]'; ?>">
									<?php esc_html_e( 'Edit', 'ova-brw' ); ?>
								</a>
							</div>
						</footer>
					</section>
				</div>
			</div>
			<div class="ovabrw-backbone-modal-backdrop"></div>
	    </div>
	    <?php
	}
}

/**
 * Check booking HTML
 */
if ( !function_exists( 'ovabrw_check_product_view' ) ) {
	function ovabrw_check_product_view() {
	    // Get tour product ids
	    $product_ids = ovabrw_get_tour_product_ids();

	    // Get current product id
	    $product_id = sanitize_text_field( ovabrw_get_meta_data( 'product_id', $_GET ) );

	    ?>
	    <div class="wrap">
	        <div class="booking_filter">
	            <form id="booking-filter" method="GET" action="<?php echo esc_url( get_admin_url( null, 'admin.php?page=ovabrw-check-product') ); ?>">
	                <h2><?php esc_html_e( 'Check Product', 'ova-brw' ); ?></h2>
	                <select name="product_id">
	                    <option value="">
	                        <?php esc_html_e( '-- Choose Product --', 'ova-brw' ); ?>
	                    </option>
	                    <?php if ( ovabrw_array_exists( $product_ids ) ):
	                        foreach ( $product_ids as $pid ): ?>
	                            <option value="<?php echo esc_attr( $pid ); ?>"<?php ovabrw_selected( $pid, $product_id ); ?>>
	                                <?php echo wp_kses_post( get_the_title( $pid ) ); ?>
	                            </option>
	                        <?php endforeach;
	                    endif; ?>
	                </select>
	                <button type="submit" class="button">
	                    <?php esc_html_e( 'Display Schedule', 'ova-brw' ); ?>
	                </button>
	                <div class="total_vehicle">
	                    <?php esc_html_e( 'Total Available','ova-brw' ); ?>:
	                    <?php echo esc_html( ovabrw_get_post_meta( $product_id, 'stock_quantity' ) ); ?>
	                </div>
	                <?php ovabrw_wp_text_input([
	                    'type'  => 'hidden',
	                    'name'  => 'page',
	                    'value' => 'ovabrw-check-product'
	                ]);

	                if ( $product_id ):
	                    // Get order statuses
	                    $statuses = brw_list_order_status();

	                    // Get order booked
	                    $order_date = ovabrw_get_order_rent_time( $product_id, $statuses );
	                    wp_localize_script( 'calendar_booking', 'order_time', $order_date );

	                    // Calendar nav
	                    $toolbar_nav = apply_filters( OVABRW_PREFIX.'calendar_show_nav', [
	                        'dayGridMonth',
	                        'timeGridWeek',
	                        'timeGridDay',
	                        'listWeek'
	                    ]);
	                    $nav = implode( ',', array_filter( $toolbar_nav ) );

	                    // Calendar language
	                    $lang = ovabrw_get_option_setting( 'calendar_language_general', 'en' );

	                    // Default view
	                    $default_view = apply_filters( OVABRW_PREFIX.'calendar_default_view', 'dayGridMonth' );

	                    ?>
	                    
	                    <div class="wrap_calendar">
	                        <div
	                            id="<?php echo esc_attr( 'calendar'.$product_id ); ?>"
	                            class="ovabrw__product_calendar"
	                            data-id="<?php echo esc_attr( 'calendar'.$product_id ); ?>"
	                            data-lang="<?php echo esc_attr( $lang ); ?>"
	                            data-nav="<?php echo esc_attr( $nav ); ?>"
	                            data-default_view="<?php echo esc_attr( $default_view ); ?>"
	                            data_event_number="<?php echo apply_filters( OVABRW_PREFIX.'event_number_cell', 2 ); ?>">
	                            <ul class="intruction">
	                                <li>
	                                    <span class="available"></span>
	                                    <span><?php esc_html_e( 'Available','ova-brw' ); ?></span>     
	                                </li>
	                                <li>
	                                    <span class="unavailable" style="background: <?php echo esc_attr( apply_filters( OVABRW_PREFIX.'background_color_event', '#FF1A1A' ) ); ?>"></span>
	                                    <span><?php esc_html_e( 'Unavailable', 'ova-brw' ); ?></span>
	                                </li>
	                            </ul>
	                            <em class="note">
                            		<?php esc_html_e( '(Event titles such as 1/200 or 2/200 reflect the number of units booked relative to the total available quantity)', 'ova-brw' ); ?>
                            	</em>
	                        </div>
	                    </div>
	                <?php endif; ?>
	            </form>
	            <div style="clear:both;">
	        </div><br>
	        <form id="available-vehicle" method="GET" action="<?php echo esc_url( get_admin_url( null, 'admin.php?page=ovabrw-check-product') ); ?>">
	            <?php
	                // Get date format
	                $date_format = ovabrw_get_date_format();

	                // Get from day
	                $from_day = sanitize_text_field( ovabrw_get_meta_data( 'from_day', $_GET ) );

	                // Get to day
	                $to_day = sanitize_text_field( ovabrw_get_meta_data( 'to_day', $_GET ) );

	                // Quantity
	                $quantity = 1;
	                $data_available = [];
	                $qty_available  = 0;

	                if ( $product_id ) {
	                    // Set Pick-up, Drop-off Date again
	                    $new_dates = ovabrw_new_input_date( $product_id, strtotime( $from_day ), strtotime( $to_day ), $date_format );

	                    // New pick-up date
	                    $pickup_date_new = ovabrw_get_meta_data( 'pickup_date_new', $new_dates );

	                    // New drop-off date
	                    $pickoff_date_new = ovabrw_get_meta_data( 'pickoff_date_new', $new_dates );

	                    if ( ovabrw_qty_by_guests( $product_id ) ) {
	                        if ( !$pickoff_date_new ) $pickoff_date_new = $pickup_date_new;

	                        $qty_available = absint( get_post_meta( $product_id, 'ovabrw_stock_quantity', true ) );

	                        // Unavailable Time (UT)
	                        $validate_ut = ovabrw_validate_unavailable_time( $product_id, $pickup_date_new, $pickoff_date_new, 'search' );
	                        if ( $validate_ut ) return $qty_available = 0;

	                        // Disable week day
	                        $validate_dwd = ovabrw_validate_disable_week_day( $product_id, $pickup_date_new, $pickoff_date_new, 'search' );
	                        if ( $validate_dwd ) return $qty_available = 0;

	                        // Get Guests in Order
	                        $guests_in_order = ovabrw_get_guests_in_order( $product_id, $pickup_date_new );

	                        // Get Guests available
	                        $guests_available = ovabrw_get_guests_available( $product_id, [], [], $guests_in_order, 'search' );

	                        if ( !$guests_available ) {
	                            $qty_available = 0;
	                        }
	                    } else {
	                        // Check Count Product in Order
	                        $check_quantity_order = ovabrw_quantity_available_in_order( $product_id, strtotime( $from_day ), strtotime( $to_day ) );

	                        $stock_quantity = absint( get_post_meta( $product_id, 'ovabrw_stock_quantity', true ) );
	                        $qty_available  = $stock_quantity - $check_quantity_order;

	                        // Check Check Unavailable
	                        $check_unavailable = ovabrw_check_unavailable( $product_id, strtotime( $from_day ), strtotime( $to_day ) );

	                        if ( $check_unavailable ) {
	                            $qty_available = 0;
	                        }
	                    }   
	                }
	            ?>
	            <h3>
	                <?php esc_html_e( 'The Available','ova-brw' ); ?>
	            </h3>
	            <?php ovabrw_admin_text_input([
	                'type'          => 'text',
	                'id'            => ovabrw_unique_id( 'from_day' ),
	                'class'         => 'start-date',
	                'name'          => 'from_day',
	                'value'         => $from_day,
	                'placeholder'   => esc_html__( 'From date', 'ova-brw' ),
	                'data_type'     => 'datepicker'
	            ]);
	            esc_html_e( 'to', 'ova-brw' );
	            ovabrw_admin_text_input([
	                'type'      => 'text',
	                'id'        => ovabrw_unique_id( 'to_day' ),
	                'class'     => 'end-date',
	                'name'      => 'to_day',
	                'value'     => $to_day,
	                'data_type' => 'datepicker'
	            ]); ?>
	            <select name="product_id">
	                <option value="">
	                    <?php esc_html_e( '-- Choose Product --', 'ova-brw' ); ?>
	                </option>
	                <?php if ( ovabrw_array_exists( $product_ids ) ):
	                    foreach ( $product_ids as $pid ): ?>
	                    <option value="<?php echo esc_attr( $pid ); ?>"<?php ovabrw_selected( $pid, $product_id ); ?>>
	                        <?php echo esc_html( get_the_title( $pid ) ); ?>
	                    </option>
	                <?php endforeach;
	                endif; ?>
	            </select>
	            <button type="submit" class="button">
	                <?php esc_html_e( 'Search', 'ova-brw' ); ?>
	            </button>
	            <?php if ( $qty_available && $qty_available > 0 ): ?>
	                <table class="quantity_available">
	                    <thead>
	                        <tr>
	                            <td>
	                                <strong>
	                                    <?php esc_html_e( 'Stock Quantity', 'ova-brw' ); ?>
	                                </strong>
	                            </td>
	                        </tr>
	                    </thead>
	                    <tbody>
	                        <tr>
	                            <td style="text-align: center;">
	                                <?php echo esc_html( $qty_available ); ?>
	                            </td>
	                        </tr>
	                    </tbody>
	                </table>
	            <?php else:
	                esc_html_e( 'Not Found', 'ova-brw' );
	            endif;
	            
	            // Page
	            ovabrw_wp_text_input([
	                'type'  => 'hidden',
	                'name'  => 'page',
	                'value' => 'ovabrw-check-product'
	            ]); ?>
	        </form>
	    </div>
	    <?php
	}
}