<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get product id
$product_id = tripgo_get_meta_data( 'id', $args, get_the_id() );

// Get product
$product = wc_get_product( $product_id );
if ( !$product || !$product->is_type( 'ovabrw_car_rental' ) ) return;

// Discount from
$dicount_from = tripgo_get_post_meta( $product_id, 'gd_duration_min' );
if ( $dicount_from ) asort( $dicount_from );

// Discount to
$discount_to = tripgo_get_post_meta( $product_id, 'gd_duration_max' );
if ( $discount_to ) asort( $discount_to );

// Adult prices
$adult_prices = tripgo_get_post_meta( $product_id, 'gd_adult_price' );

// Child prices
$child_prices = tripgo_get_post_meta( $product_id, 'gd_children_price' );

// Baby prices
$baby_prices = tripgo_get_post_meta( $product_id, 'gd_baby_price' );

// Special start date
$st_startdate = tripgo_get_post_meta( $product_id, 'st_startdate' );

// Special end date
$st_enddate = tripgo_get_post_meta( $product_id, 'st_enddate' );

// Special adult prices
$st_adult_prices = tripgo_get_post_meta( $product_id, 'st_adult_price' );

// Special child prices
$st_child_prices = tripgo_get_post_meta( $product_id, 'st_children_price' );

// Special baby price
$st_baby_prices = tripgo_get_post_meta( $product_id, 'st_baby_price' );

// Special discounts
$st_discounts = tripgo_get_post_meta( $product_id, 'st_discount' );

// Show child
$show_child = function_exists( 'ovabrw_show_children' ) ? ovabrw_show_children( $product_id ) : false;

// Show baby
$show_baby = function_exists( 'ovabrw_show_babies' ) ? ovabrw_show_babies( $product_id ) : false;

// Show table price
$show_table_price = tripgo_get_meta_data( 'show_table_price', $args, 'yes' );

if ( 'yes' === $show_table_price && ( tripgo_array_exists( $dicount_from ) || tripgo_array_exists( $discount_to ) || tripgo_array_exists( $st_startdate ) || tripgo_array_exists( $st_enddate ) ) ): ?>
	<div class="product_table_price">
		<div class="ovacrs_price_rent">
		<?php if ( tripgo_array_exists( $dicount_from ) || tripgo_array_exists( $discount_to ) ): ?>
			<div class="price_table">
				<h3 class="ovabrw-label">
					<?php esc_html_e( 'Global Discount', 'tripgo' ); ?>
				</h3>
				<table class="gb-discount">
					<thead>
						<tr>
							<th>
								<?php echo esc_html__( 'Min - Max (Persons)', 'tripgo' ); ?>
							</th>
							<th>
								<?php echo esc_html__( 'Adult Price', 'tripgo' ); ?>
							</th>
                            <?php if ( $show_child ): ?>
							    <th>
							    	<?php echo esc_html__( 'Child Price', 'tripgo' ); ?>
							    </th>
							<?php endif;

							// Show baby
							if ( $show_baby ): ?>
							    <th>
							    	<?php echo esc_html__( 'Baby Price', 'tripgo' ); ?>
							    </th>
							<?php endif; ?>
						</tr>
					</thead>
					<tbody>
					<?php foreach ( $dicount_from as $i => $from ):
						// Discount to
						$to = tripgo_get_meta_data( $i, $discount_to );

						// Adult price
						$adult_price = (float)tripgo_get_meta_data( $i, $adult_prices );

						// Child price
						$child_price = (float)tripgo_get_meta_data( $i, $child_prices );

						// Baby price
						$baby_price = (float)tripgo_get_meta_data( $i, $baby_prices );
					?>
						<tr class="<?php echo intval( $i%2 ) ? 'eve' : 'odd'; ?>">
							<td class="bold">
								<?php echo sprintf( esc_html__( '%s - %s', 'tripgo' ), $from, $to ); ?>
							</td>
							<td>
								<?php echo ovabrw_wc_price( $adult_price ); ?>
							</td>
                            <?php if ( $show_child ): ?>
								<td>
									<?php echo ovabrw_wc_price( $child_price ); ?>
								</td>
							<?php endif;

							// Baby price
							if ( $show_baby ): ?>
								<td>
									<?php echo ovabrw_wc_price( $baby_price ); ?>
								</td>
							<?php endif; ?>
						</tr>	
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif;

		// Special times
		if ( tripgo_array_exists( $st_startdate ) && tripgo_array_exists( $st_enddate ) ): ?>
			<div class="price_table">
				<h3 class="ovabrw-label">
					<?php esc_html_e( 'Special Time', 'tripgo' ); ?>
				</h3>
				<table class="special-time">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Start Date', 'tripgo' ); ?></th>
							<th><?php esc_html_e( 'End Date', 'tripgo' ); ?></th>
							<th><?php esc_html_e( 'Adult Price', 'tripgo' ); ?></th>
							<?php if ( $show_child ): ?>
								<th><?php esc_html_e( 'Child Price', 'tripgo' ); ?></th>
							<?php endif; ?>
							<?php if ( $show_baby ): ?>
								<th><?php esc_html_e( 'Baby Price', 'tripgo' ); ?></th>
							<?php endif; ?>
							<th><?php esc_html_e( 'Discount', 'tripgo' ); ?></th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ( $st_startdate as $i => $start_date ):
						// Start date
						if ( !strtotime( $start_date ) ) continue;

						// End date
						$end_date = tripgo_get_meta_data( $i, $st_enddate );
						if ( !strtotime( $end_date ) ) continue;

						// Adult price
						$adult_price = (float)tripgo_get_meta_data( $i, $st_adult_prices );

						// Child price
						$child_price = (float)tripgo_get_meta_data( $i, $st_child_prices );

						// Baby price
						$baby_price = (float)tripgo_get_meta_data( $i, $st_baby_prices );

						// Discounts
						$discounts = tripgo_get_meta_data( $i, $st_discounts );

						// Discount from
						$disc_from = tripgo_get_meta_data( 'min', $discounts );

						// Discount to
						$disc_to = tripgo_get_meta_data( 'max', $discounts );

						// Discount adult prices
						$disc_adult_prices = tripgo_get_meta_data( 'adult_price', $discounts );

						// Discount child prices
						$disc_child_prices = tripgo_get_meta_data( 'children_price', $discounts );

						// Discount baby prices
						$disc_baby_prices = tripgo_get_meta_data( 'baby_price', $discounts );
					?>
						<tr class="<?php echo intval( $i%2 ) ? 'eve' : 'odd'; ?>">
							<td class="date bold">
								<?php echo esc_html( $start_date ); ?>
							</td>
							<td class="date bold">
								<?php echo esc_html( $end_date ); ?>
							</td>
							<td>
								<?php echo ovabrw_wc_price( $adult_price ); ?>
							</td>
                            <?php if ( $show_child ): ?>
								<td>
									<?php echo ovabrw_wc_price( $child_price ); ?>
								</td>
							<?php endif;

							// Baby price
							if ( $show_baby ): ?>
								<td>
									<?php echo ovabrw_wc_price( $baby_price ); ?>
								</td>
							<?php endif; ?>
							<td>
								<a href="#" class="ovabrw_open_popup" data-popup-open="popup-ovacrs-rt-discount-day-<?php echo esc_attr( $i ); ?>">
									<?php esc_html_e( 'View Discount', 'tripgo' ); ?>
									<div class="ovacrs_st_discount popup" data-popup="popup-ovacrs-rt-discount-day-<?php echo esc_attr( $i ); ?>">
										<div class="popup-inner">
											<div class="price_table">
												<div class="time_discount">
													<span>
														<?php esc_html_e( 'Time Discount: ', 'tripgo' ); ?>
													</span>
													<span class="time">
														<?php echo sprintf( esc_html__( '%s to %s', 'tripgo' ), $start_date, $end_date ); ?>  
													</span>
												</div>
												<?php if ( $disc_from || $disc_to ):
													asort( $disc_from );
													asort( $disc_to );
												?>
													<table>
														<thead>
															<tr>
																<th>
																	<?php esc_html_e( 'Min - Max (Persons)', 'tripgo' ); ?>
																</th>
																<th>
																	<?php esc_html_e( 'Adult Price', 'tripgo' ); ?>
																</th>
																<?php if ( $show_child ): ?>
																	<th>
																		<?php esc_html_e( 'Child Price', 'tripgo' ); ?>
																	</th>
																<?php endif; ?>
																<?php if ( $show_baby ): ?>
																	<th>
																		<?php esc_html_e( 'Baby Price', 'tripgo' ); ?>
																	</th>
																<?php endif; ?>
															</tr>
														</thead>
														<tbody>
														<?php foreach ( $disc_from as $k => $from ):
															// Discount to
															$to = tripgo_get_meta_data( $k, $disc_to );

															// Adult price
															$disc_adult_price = (float)tripgo_get_meta_data( $k, $disc_adult_prices );

															// Child price
															$disc_child_price = (float)tripgo_get_meta_data( $k, $disc_child_prices );

															// Baby price
															$disc_baby_price = (float)tripgo_get_meta_data( $k, $disc_baby_prices );
														?>
															<tr class="<?php echo intval($k%2) ? 'eve' : 'odd'; ?>">
																<td class="bold">
																	<?php echo sprintf( esc_html__( '%s - %s', 'tripgo' ), $from, $to ); ?>
																</td>
																<td>
																	<?php echo ovabrw_wc_price( $disc_adult_price ); ?>
																</td>
																<?php if ( $show_child ): ?>
																	<td>
																		<?php echo ovabrw_wc_price( $disc_child_price ); ?>
																	</td>
																<?php endif; ?>
																<?php if ( $show_baby ): ?>
																	<td>
																		<?php echo ovabrw_wc_price( $disc_baby_price ); ?>
																	</td>
																<?php endif; ?>
															</tr>
														<?php endforeach; ?>
														</tbody>
													</table>
												<?php else: ?>
													<div class="no_discount">
														<?php esc_html_e( 'No Discount in this time', 'tripgo' ); ?>
													</div>
												<?php endif; ?>
											</div>	
											<div class="close_discount">
												<a href="#" class="popup-close-2" data-popup-close="popup-ovacrs-rt-discount-day-<?php echo esc_attr( $i ); ?>">
													<?php esc_html_e( 'Close', 'tripgo' ); ?>
												</a>
											</div>
											<a href="#" class="popup-close" data-popup-close="popup-ovacrs-rt-discount-day-<?php echo esc_attr( $i ); ?>">x</a>
										</div>
									</div>
								</a>
							</td>
						</tr>			
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>	
		<?php endif; ?>
		</div>		
	</div>
<?php endif; ?>