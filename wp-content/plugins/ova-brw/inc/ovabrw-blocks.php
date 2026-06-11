<?php use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;

if ( !defined( 'ABSPATH' ) ) exit();

/**
 * OVABRW Block class.
 */
if ( !class_exists( 'OVABRW_Blocks', false ) ) {

	class OVABRW_Blocks implements IntegrationInterface {

		// Total deposit amount
		private $total_deposit = 0;

		// Total remaining amount
		private $total_remaining = 0;

		// Remaining taxes amount
		private $remaining_taxes = 0;

		// Have deposit
		private $have_deposit = false;

		// Remaining insurance amount
		private $remaining_insurance = 0;

		// Remaining insurance tax amount
		private $remaining_insurance_tax = 0;

		/**
		 * The name of the integration.
		 */
		public function get_name() {
			return 'ovabrwBlocks';
		}

		/**
		 * When called invokes any initialization/setup for the integration.
		 */
		public function initialize() {
			wp_enqueue_style(
				'ovabrw-blocks-integration',
				OVABRW_PLUGIN_URI.'assets/css/frontend/block-cart.css',
				[],
				null
			);
			wp_register_script(
				'ovabrw-blocks-integration',
				OVABRW_PLUGIN_URI.'assets/js/frontend/block-cart.min.js',
				[ 'jquery' ],
				null,
				true
			);
			wp_set_script_translations(
				'ovabrw-blocks-integration',
				'ova-brw',
				OVABRW_PLUGIN_PATH . '/languages'
			);
		}

		/**
		 * Returns an array of script handles to enqueue in the frontend context.
		 */
		public function get_script_handles() {
			return [ 'ovabrw-blocks-integration' ];
		}

		/**
		 * Returns an array of script handles to enqueue in the editor context.
		 */
		public function get_editor_script_handles() {
			return [ 'ovabrw-blocks-integration' ];
		}

		/**
		 * An array of key, value pairs of data made available to the block on the client side.
		 */
		public function get_script_data() {
			// Cart data
			$cart_data = [];

			if ( !WC()->cart || !is_object( WC()->cart ) ) {
				return apply_filters( OVABRW_PREFIX.'blocks_get_script_data', array_filter( $cart_data ) );
			}

			// Loop
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				// Item data
				$item_data = [];

				// Product ID
				$product_id = $cart_item['data']->get_id();

				// Check is Tour product
				if ( !$product_id || !$cart_item['data']->is_type( OVABRW_RENTAL ) ) continue;

				// is rental product
				$item_data['ovabrw_rental'] = true;

				// Get block cart item subtotal
				$item_subtotal = $this->get_block_cart_item_subtotal( $cart_item );
				if ( $item_subtotal ) {
					$item_data['item_subtotal'] = '<span class="ovabrw-cart-item">'. wp_kses_post( $item_subtotal ) .'</span>';
				}

				// Get guest info HTML
				$guest_info = ovabrw_get_meta_data( 'ovabrw_guest_info', $cart_item );
				if ( apply_filters( OVABRW_PREFIX.'view_guest_info_in_cart', true ) && ovabrw_array_exists( $guest_info ) ) {
					$item_data['guest_info'] = ovabrw_get_guest_info_html( $product_id, $guest_info );
				}

				// Add cart data
				$cart_data['cartItem'][$cart_item_key] = $item_data;
			}
			// End Loop
			
			// Have deposit
			if ( $this->have_deposit ) {
				// Deposit label
				$cart_data['depositLabel'] = esc_html__( 'Deposit', 'ova-brw' );

				// Total remaining
				$cart_data['totalRemaining'] = $this->get_block_cart_total_remaining();

				// Total payable
				$cart_data['totalPayable'] = $this->get_block_cart_total_payable();
			}
			
		    return apply_filters( OVABRW_PREFIX.'blocks_get_script_data', array_filter( $cart_data ) );
		}

		/**
		 * Get block cart item subtotal
		 */
		public function get_block_cart_item_subtotal( $cart_item ) {
			// Get product ID
			$product_id = (int)ovabrw_get_meta_data( 'product_id', $cart_item );
			if ( !$product_id ) return false;

			// Check rental product
        	if ( !$product_id || !$cart_item['data']->is_type( OVABRW_RENTAL ) ) return false;

			// Item subtotal
			$item_subtotal = '';

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

	        // Get custom checkout fields
	        $cckf = ovabrw_get_meta_data( 'custom_ckf', $cart_item );

	        // Get cckf quantity
	        $cckf_qty = ovabrw_get_meta_data( 'cckf_qty', $cart_item );

	        // Get custom checkout fields HTML
            $html_cckf = ovabrw_get_html_cckf( $cckf, false, $cckf_qty );

	        // Get resources
            $resources = ovabrw_get_meta_data( 'ovabrw_resources', $cart_item );

            // Get resource guests
            $resource_guests = ovabrw_get_meta_data( 'ovabrw_resource_guests', $cart_item );

            // Get HTML resources
            $html_resources = ovabrw_get_html_resources( $product_id, $resources, $numberof_adults, $numberof_children, $numberof_babies, false, $resource_guests );

            // Get services
            $services = ovabrw_get_meta_data( 'ovabrw_services', $cart_item );

            // Get service guests
            $service_guests = ovabrw_get_meta_data( 'ovabrw_service_guests', $cart_item );

            // Get HTML services
            $html_services = ovabrw_get_html_services( $product_id, $services, $numberof_adults, $numberof_children, $numberof_babies, false, $service_guests );

            // Get HTML extra prices
            $html_extra = ovabrw_get_html_extra( $html_resources, $html_services, $html_cckf );

	        // Get deposit
            $is_deposit = ovabrw_get_meta_data( 'is_deposit', $cart_item );
            if ( $is_deposit ) {
            	// Update have deposit
            	$this->have_deposit = true;

            	// Sub insurance
        		$sub_insurance = (float)ovabrw_get_post_meta( $product_id, 'amount_insurance' );
        		$sub_insurance = $sub_insurance*$numberof_guests*$quantity;
        		$sub_remaining_insurance = 0;

        		// Get sub total
        		$subtotal = get_price_by_guests( $product_id, $checkin_date, $checkout_date, $cart_item );

        		// Multi currency - Convert subtotal
				if ( is_plugin_active( 'woocommerce-multilingual/wpml-woocommerce.php' ) ) {
		            $subtotal       = ovabrw_convert_price( $subtotal );
		            $sub_insurance  = ovabrw_convert_price( $sub_insurance );
		        }

		        // Subdeposit
	            $sub_deposit = 0;

		        // Deposit type
		        $deposit_type = ovabrw_get_post_meta( $product_id, 'type_deposit' );

		        // Deposit value
            	$deposit_value  = (float)ovabrw_get_post_meta( $product_id, 'amount_deposit' );

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
		                            $sub_deposit 	-= $sub_deposit * ( $coupon_amount / 100 );
		                            $sub_remaining 	-= $sub_remaining * ( $coupon_amount / 100 );
		                        }
		                    }
		                }
		            }
		        }

		        // Sub remaining taxes
	            $sub_remaining_taxes = ovabrw_get_taxes_by_price( $cart_item['data'], ovabrw_convert_price( $sub_remaining, [], false ) );

	            // Sub remaining insurance
            	if ( $sub_remaining_insurance ) {
            		$this->remaining_insurance += $sub_remaining_insurance;

            		$sub_remaining_insurance_tax = ovabrw_get_insurance_tax_amount( ovabrw_convert_price( $sub_remaining_insurance, [], false ) );

	            	if ( $sub_remaining_insurance_tax ) {
	            		$this->remaining_insurance_tax += $sub_remaining_insurance_tax;
	            	}
            	}

	           	// Remaining taxes
            	$this->remaining_taxes += $sub_remaining_taxes;

            	// Total deposit
            	$this->total_deposit += $sub_deposit;

            	// Total remaining
            	$this->total_remaining += $sub_remaining;

            	// Get total payable
        		$total_payable = $subtotal;

        		// Subtotal
            	if ( $total_payable ) {
            		// Taxable
            		if ( $cart_item['data']->is_taxable() ) {
	                    if ( WC()->cart->display_prices_including_tax() ) {
	                        if ( !wc_prices_include_tax() && WC()->cart->get_subtotal_tax() > 0 ) {
	                            $total_payable = wc_get_price_including_tax( $cart_item['data'], [ 'price' => $total_payable ] );
	                        }
	                    } else {
	                        if ( wc_prices_include_tax() && WC()->cart->get_subtotal_tax() > 0 ) {
	                            $total_payable = wc_get_price_excluding_tax( $cart_item['data'], [ 'price' => $total_payable ] );
	                        }
	                    }
	                }

            		if ( 'percent' === $deposit_type ) {
            			$item_subtotal .= ' <small class="tax_label">' . sprintf( esc_html__( '(%s%% deposit of %s)', 'ova-brw' ), $deposit_value, ovabrw_wc_price( $total_payable, [], false ) ) . '</small>';
            		} else {
            			$item_subtotal .= ' <small class="tax_label">' . sprintf( esc_html__( '(deposit of %s)', 'ova-brw' ), ovabrw_wc_price( $total_payable, [], false ) ) . '</small>';
            		}
            	}

            	// Extra HMTL
        		if ( $html_extra ) $item_subtotal .= $html_extra;

            	// Deposit, Remaining, Total Payable HTML
        		$item_subtotal .= '<dl class="variation">';

        		// Deposit HTML
        		if ( $sub_deposit ) {
        			$item_subtotal .= $this->get_block_cart_item_subdeposit( $cart_item['data'], [
        				'deposit_amount' 	=> $sub_deposit,
        				'insurance_amount' 	=> $sub_insurance
        			]);
        		}
        		// End Deposit HTML
        		
        		// Remaining HTML
        		if ( $sub_remaining ) {
        			$item_subtotal .= $this->get_block_cart_item_subremaining( $cart_item['data'], [
        				'remaining_amount' => $sub_remaining,
        				'insurance_amount' => $sub_insurance
        			]);
        		}
        		// End Remaining HTML
        		
        		// Payable HTML
        		if ( $sub_deposit || $sub_remaining ) {
        			$item_subtotal .= $this->get_block_cart_item_payable( $cart_item['data'], [
        				'deposit_amount' 		=> $sub_deposit,
        				'remaining_amount' 		=> $sub_remaining,
        				'insurance_amount' 		=> $sub_insurance,
        				'remaining_insurance' 	=> $sub_remaining_insurance
        			]);
        		}
        		// End Payable HTML

        		$item_subtotal .= '</dl>';
            } else {
            	// Extra HMTL
        		if ( $html_extra ) $item_subtotal .= $html_extra;
            }

			return apply_filters( OVABRW_PREFIX.'get_block_cart_item_subtotal', $item_subtotal, $cart_item, $this );
		}

		/**
		 * Get block cart item subdeposit
		 */
		public function get_block_cart_item_subdeposit( $product, $args ) {
			// init
			$subdeposit = '';

			// Deposit amount
			$deposit_amount = (float)ovabrw_get_meta_data( 'deposit_amount', $args );

			// Insurance amount
			$insurance_amount = (float)ovabrw_get_meta_data( 'insurance_amount', $args );

			if ( $product && $deposit_amount ) {
				// Insurance string
                $insurance_string = '';

                // Tax string
                $tax_string = '';

                // Deposit HTML
                $subdeposit .= '<dt>'.esc_html__( 'Deposit:', 'ova-brw' ).'</dt>';
                $subdeposit .= '<dd>';

                // Convert price
                $deposit_price = ovabrw_wc_price( $deposit_amount, [], false );

                // Get insurance amount
                if ( (float)$insurance_amount ) {
                    $insurance_amount   = ovabrw_get_insurance_inclusive_tax( $insurance_amount );
                    $deposit_price      = ovabrw_wc_price( $deposit_amount + $insurance_amount, [], false );
                    $insurance_string   = ' <small class="tax_label">' . sprintf( esc_html__( '(incl. %s insurance fee)', 'ova-brw' ), ovabrw_wc_price( $insurance_amount, [], false ) ) . '</small>';
                }

                // Taxable
                if ( $product->is_taxable() ) {
                    if ( WC()->cart->display_prices_including_tax() ) {
                        if ( !wc_prices_include_tax() && WC()->cart->get_subtotal_tax() > 0 ) {
                            $row_price = wc_get_price_including_tax( $product, [
                            	'price' => $deposit_amount
                            ]);
                            $row_price += $insurance_amount;

                            $deposit_price  = ovabrw_wc_price( $row_price, [], false );
                            $tax_string     = ' <small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>';
                        }
                    } else {
                        if ( wc_prices_include_tax() && WC()->cart->get_subtotal_tax() > 0 ) {
                            $row_price = wc_get_price_excluding_tax( $product, [
                            	'price' => $deposit_amount
                            ]);
                            $row_price += $insurance_amount;

                            $deposit_price  = ovabrw_wc_price( $row_price, [], false );
                            $tax_string     = ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
                        }
                    }
                }

                $subdeposit .= $deposit_price.$insurance_string.$tax_string;
                $subdeposit .= '</dd>';
			}

			return apply_filters( OVABRW_PREFIX.'get_block_cart_item_subdeposit', $subdeposit, $product, $args );
		}

		/**
		 * Get block cart item subremaining
		 */
		public function get_block_cart_item_subremaining( $product, $args ) {
			// init
			$subremaining = '';

			// Remaining amount
			$remaining_amount = (float)ovabrw_get_meta_data( 'remaining_amount', $args );

			// Insurance amount
			$insurance_amount = (float)ovabrw_get_meta_data( 'insurance_amount', $args );

			if ( $product && $remaining_amount ) {
				// Insurance string
                $insurance_string = '';

                // Tax string
                $tax_string = '';

                // Remaining HTML
                $subremaining .= '<dt>'.esc_html__( 'Remaining:', 'ova-brw' ).'</dt>';
                $subremaining .= '<dd>';

                // Convert price
                $remaining_price = ovabrw_wc_price( $remaining_amount, [], false );

                // Get insurance amount
                if ( (float)$insurance_amount ) {
                    $insurance_amount   = ovabrw_get_insurance_inclusive_tax( $insurance_amount );
                    $remaining_price    = ovabrw_wc_price( $remaining_amount + $insurance_amount, [], false );
                    $insurance_string   = ' <small class="tax_label">' . sprintf( esc_html__( '(incl. %s insurance fee)', 'ova-brw' ), ovabrw_wc_price( $insurance_amount, [], false ) ) . '</small>';
                }

                // Taxable
                if ( $product->is_taxable() ) {
                    if ( WC()->cart->display_prices_including_tax() ) {
                        if ( !wc_prices_include_tax() && WC()->cart->get_subtotal_tax() > 0 ) {
                            $row_price = wc_get_price_including_tax( $product, [
                            	'price' => $remaining_amount
                            ]);

                            $row_price          += $insurance_amount;
                            $remaining_price    = ovabrw_wc_price( $row_price, [], false );
                            $tax_string         = ' <small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>';
                        }
                    } else {
                        if ( wc_prices_include_tax() && WC()->cart->get_subtotal_tax() > 0 ) {
                            $row_price = wc_get_price_excluding_tax( $product, [
                            	'price' => $remaining_amount
                            ]);

                            $row_price          += $insurance_amount;
                            $remaining_price    = ovabrw_wc_price( $row_price, [], false );
                            $tax_string         = ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
                        }
                    }
                }

                $subremaining .= $remaining_price.$insurance_string.$tax_string;
                $subremaining .= '</dd>';
			}

			return apply_filters( OVABRW_PREFIX.'get_block_cart_item_subremaining', $subremaining, $product, $args );
		}

		/**
		 * Get block cart item payable
		 */
		public function get_block_cart_item_payable( $product, $args ) {
			// init
			$payable_html = '';

			// Deposit amount
			$deposit_amount = (float)ovabrw_get_meta_data( 'deposit_amount', $args );

			// Remaining amount
			$remaining_amount = (float)ovabrw_get_meta_data( 'remaining_amount', $args );

			if ( $product && ( $deposit_amount || $remaining_amount ) ) {
				// Get insurance amount
				$insurance_amount = (float)ovabrw_get_meta_data( 'insurance_amount', $args );

                if ( $insurance_amount ) {
                    $insurance_amount = ovabrw_get_insurance_inclusive_tax( $insurance_amount );
                }

                // Get remaining insurance amount
                $remaining_insurance = (float)ovabrw_get_meta_data( 'remaining_insurance', $args );

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

                if ( $product->is_taxable() ) {
                    if ( WC()->cart->display_prices_including_tax() ) {
                        if ( ! wc_prices_include_tax() && WC()->cart->get_subtotal_tax() > 0 ) {
                            $row_deposit    = round( wc_get_price_including_tax( $product, [
                            	'price' => $deposit_amount
                            ]), wc_get_price_decimals() );
                            $row_remaining  = round( wc_get_price_including_tax( $product, [
                            	'price' => $remaining_amount
                            ]), wc_get_price_decimals() );
                            $row_price      = $row_deposit + $row_remaining + $insurance_amount + $remaining_insurance;
                            $total_payable  = ovabrw_wc_price( $row_price, [], false );
                            $tax_string     = ' <small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>';
                        }
                    } else {
                        if ( wc_prices_include_tax() && WC()->cart->get_subtotal_tax() > 0 ) {
                            $row_deposit    = round( wc_get_price_excluding_tax( $product, [
                            	'price' => $deposit_amount
                            ]), wc_get_price_decimals() );
                            $row_remaining  = round( wc_get_price_excluding_tax( $product, [
                            	'price' => $remaining_amount
                            ]), wc_get_price_decimals() );
                            $row_price      = $row_deposit + $row_remaining + $insurance_amount + $remaining_insurance;
                            $total_payable  = ovabrw_wc_price( $row_price, [], false );
                            $tax_string     = ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
                        }
                    }
                }

                $payable_html .= $total_payable.$tax_string;
                $payable_html .= '</dd>';
			}

			return apply_filters( OVABRW_PREFIX.'get_block_cart_item_payable', $payable_html, $product, $args );
		}

		/**
		 * Get total remaining HTML
		 */
		public function get_block_cart_total_remaining() {
			$remaining_html 	= '';
			$remaining_taxes 	= '';

			if ( $this->total_remaining ):
				$remaining_amount 	= $this->total_remaining;
				$remaining_tax 		= $this->remaining_taxes;
				$insurance_amount 	= $this->remaining_insurance;
            	$insurance_tax 		= $this->remaining_insurance_tax;

            	$remaining_taxes = '<span class="wc-block-formatted-money-amount wc-block-components-formatted-money-amount wc-block-components-totals-footer-item-tax-value">';

	            // Insurance amount
	            if ( $insurance_amount ) {
	                $remaining_amount += floatval( $insurance_amount );

	                if ( WC()->cart->display_prices_including_tax() ) {
	                    if ( ovabrw_insurance_tax_enabled() && $insurance_tax ) {
	                        $insurance_amount += $insurance_tax;
	                    }
	                }

	                $remaining_taxes .= sprintf( esc_html__( 'Including %s insurance fee', 'ova-brw' ), ovabrw_wc_price( $insurance_amount, [], false ) );
	                $remaining_taxes .= '<br>';
	            }

	            // Tax enabled
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
	                        $tax_text = esc_html__( 'Including %s', 'ova-brw' );
	                    } else {
	                        $remaining_amount 	-= $remaining_tax;
	                        $tax_text 			= esc_html__( 'Excluding %s', 'ova-brw' );
	                    }
	                } else {
	                    if ( WC()->cart->display_prices_including_tax() ) {
	                        $remaining_amount 	+= $remaining_tax;
	                        $tax_text 			= esc_html__( 'Including %s', 'ova-brw' );
	                    } else {
	                    	$tax_text = esc_html__( 'Excluding %s', 'ova-brw' );
	                    }
	                }

	                $remaining_taxes .= sprintf( $tax_text, wp_kses_post( $tax_string ) );
	            }

	            $remaining_taxes .= '</span>';

	        ob_start(); ?>
				<div class="wc-block-components-totals-item wc-block-components-totals-footer-item ovabrw-block-totals-remaining">
					<span class="wc-block-components-totals-item__label">
						<?php esc_html_e( 'Remaining', 'ova-brw' ); ?>
					</span>
					<div class="wc-block-components-totals-item__value">
						<span class="wc-block-formatted-money-amount wc-block-components-formatted-money-amount wc-block-components-totals-footer-item-tax-value">
							<?php echo wc_price( $remaining_amount ); ?>
						</span>
					</div>
					<?php if ( $remaining_taxes ): ?>
						<div class="wc-block-components-totals-item__description">
							<p class="wc-block-components-totals-footer-item-tax">
								<?php echo wp_kses_post( $remaining_taxes ); ?>
							</p>
						</div>
					<?php endif; ?>
				</div>
			<?php $remaining_html = ob_get_contents();
			ob_end_clean();
			endif;

			return apply_filters( OVABRW_PREFIX.'get_block_cart_total_remaining', $remaining_html, $this );
		}

		/**
		 * Get total payable HTML
		 */
		public function get_block_cart_total_payable() {
			$payable_html 		= '';
			$remaining_taxes 	= '';

			if ( $this->have_deposit ):
				// Order total
                $order_totals   = WC()->cart->get_totals();
                $order_total    = round( ovabrw_get_meta_data( 'total', $order_totals ), wc_get_price_decimals() );

                // Remaining
                $remaining_amount   = $this->total_remaining;
                $remaining_amount   = ovabrw_convert_price( $remaining_amount, [], false );
                $order_total        += floatval( $remaining_amount );

                // Remaining insurance
                $remaining_insurance = $this->remaining_insurance;
                $remaining_insurance = ovabrw_convert_price( $remaining_insurance, [], false );
                $order_total         += $remaining_insurance;

                // Tax enabled
                if ( wc_tax_enabled() ) {
                	$remaining_taxes = '<span class="wc-block-formatted-money-amount wc-block-components-formatted-money-amount wc-block-components-totals-footer-item-tax-value">';

                    // Total tax
                    $total_tax = round( ovabrw_get_meta_data( 'total_tax', $order_totals ), wc_get_price_decimals() );

                    // Get remaining tax amount
                    $remaining_tax = $this->remaining_taxes;

                    // Get remaining insurance tax amount
                    $remaining_insurance_tax = $this->remaining_insurance_tax;
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
                            $tax_string = sprintf( '%s %s', wc_price( $total_tax ), WC()->countries->tax_or_vat() );
                            $tax_text 	= esc_html__( 'Including %s', 'ova-brw' );
                        } else {
                            $order_total -= $remaining_tax;

                            $value      = '<strong>'.wc_price( $order_total ).'</strong>';
                            $tax_string = sprintf( '%s %s', wc_price( $remaining_tax ), WC()->countries->tax_or_vat() );
                            $tax_text 	= esc_html__( 'Excluding %s', 'ova-brw' );
                        }
                    } else {
                        if ( WC()->cart->display_prices_including_tax() ) {
                            $order_total    += $remaining_tax;
                            $total_tax      += $remaining_tax;

                            $value      = '<strong>'.wc_price( $order_total ).'</strong>';
                            $tax_string = sprintf( '%s %s', wc_price( $total_tax ), WC()->countries->tax_or_vat() );
                            $tax_text 	= esc_html__( 'Including %s', 'ova-brw' );
                        } else {
                            $value      = '<strong>'.wc_price( $order_total ).'</strong>';
                            $tax_string = sprintf( '%s %s', wc_price( $remaining_tax ), WC()->countries->tax_or_vat() );
                            $tax_text 	= esc_html__( 'Excluding %s', 'ova-brw' );
                        }
                    }

                    $remaining_taxes .= sprintf( $tax_text, wp_kses_post( $tax_string ) );
                    $remaining_taxes .= '</span>';
                }
            ob_start(); ?>
				<div class="wc-block-components-totals-item wc-block-components-totals-footer-item ovabrw-block-totals-payable">
					<span class="wc-block-components-totals-item__label">
						<?php esc_html_e( 'Total', 'ova-brw' ); ?>
					</span>
					<div class="wc-block-components-totals-item__value">
						<span class="wc-block-formatted-money-amount wc-block-components-formatted-money-amount wc-block-components-totals-footer-item-tax-value">
							<?php echo wc_price( $order_total ); ?>
						</span>
					</div>
					<?php if ( $remaining_taxes ): ?>
						<div class="wc-block-components-totals-item__description">
							<p class="wc-block-components-totals-footer-item-tax">
								<?php echo wp_kses_post( $remaining_taxes ); ?>
							</p>
						</div>
					<?php endif; ?>
				</div>
			<?php $payable_html = ob_get_contents();
			ob_end_clean();
			endif;

			return apply_filters( OVABRW_PREFIX.'get_block_cart_total_payable', $payable_html, $this );
		}
	}
}