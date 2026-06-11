<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get adult prices
$adult_prices = ovabrw_get_post_meta( $post_id, 'st_adult_price' );

// Get child prices
$child_prices = ovabrw_get_post_meta( $post_id, 'st_children_price' );

// Get baby prices
$baby_prices = ovabrw_get_post_meta( $post_id, 'st_baby_price' );

// Start date
$start_date = ovabrw_get_post_meta( $post_id, 'st_startdate' );

// End date
$end_date = ovabrw_get_post_meta( $post_id, 'st_enddate' );

// Special discounts
$special_discounts = ovabrw_get_post_meta( $post_id, 'st_discount' );

?>
<div class="ovabrw-special-time">
	<table class="widefat">
		<thead>
			<tr>
				<th class="ovabrw-required">
					<?php esc_html_e( 'Adult price', 'ova-brw' ); ?>
				</th>
				<?php if ( ovabrw_show_children( $this->get_id() ) ): ?>
					<th class="ovabrw-required">
						<?php esc_html_e( 'Child price', 'ova-brw' ); ?>
					</th>
				<?php endif; ?>
				<?php if ( ovabrw_show_babies( $this->get_id() ) ): ?>
					<th class="ovabrw-required">
						<?php esc_html_e( 'Baby price', 'ova-brw' ); ?>
					</th>
				<?php endif; ?>
				<th class="ovabrw-required">
					<?php esc_html_e( 'Start date', 'ova-brw' ); ?>
				</th>
				<th class="ovabrw-required">
					<?php esc_html_e( 'End date', 'ova-brw' ); ?>
				</th>
				<th>
					<?php esc_html_e( 'Discount in special time (DST)', 'ova-brw' ); ?>
				</th>
				<th></th>
			</tr>
		</thead>
		<tbody class="ovabrw-special-time-content">
			<?php if ( ovabrw_array_exists( $adult_prices ) ):
				foreach ( $adult_prices as $i => $adult_price ):
					// Child price
					$child_price = ovabrw_get_meta_data( $i, $child_prices );

					// Baby price
					$baby_price = ovabrw_get_meta_data( $i, $baby_prices );

					// Start date
					$start = ovabrw_get_meta_data( $i, $start_date );

					// End date
					$end = ovabrw_get_meta_data( $i, $end_date );

					// Discounts
					$discounts = ovabrw_get_meta_data( $i, $special_discounts );
				?>
					<tr class="ovabrw-special-time-row" data-pos="<?php echo esc_attr( $i ); ?>">
					    <td width="9%" class="ovabrw-input-price">
					    	<?php ovabrw_wp_text_input([
					    		'type' 			=> 'text',
					    		'class' 		=> 'ovabrw-input-required',
					    		'name' 			=> $this->get_meta_name( 'st_adult_price[]' ),
					    		'value' 		=> $adult_price,
					    		'placeholder' 	=> '10',
					    		'data_type' 	=> 'price'
					    	]); ?>
					    </td>
					    <?php if ( ovabrw_show_children( $this->get_id() ) ): ?>
						    <td width="9%" class="ovabrw-input-price">
						    	<?php ovabrw_wp_text_input([
						    		'type' 			=> 'text',
						    		'class' 		=> 'ovabrw-input-required',
						    		'name' 			=> $this->get_meta_name( 'st_children_price[]' ),
						    		'value' 		=> $child_price,
						    		'placeholder' 	=> '10',
						    		'data_type' 	=> 'price'
						    	]); ?>
						    </td>
						<?php endif; ?>
						<?php if ( ovabrw_show_babies( $this->get_id() ) ): ?>
						    <td width="9%" class="ovabrw-input-price">
						    	<?php ovabrw_wp_text_input([
						    		'type' 			=> 'text',
						    		'class' 		=> 'ovabrw-input-required',
						    		'name' 			=> $this->get_meta_name( 'st_baby_price[]' ),
						    		'value' 		=> $baby_price,
						    		'placeholder' 	=> '10',
						    		'data_type' 	=> 'price'
						    	]); ?>
						    </td>
						<?php endif; ?>
					    <td width="12.5%">
					    	<?php ovabrw_wp_text_input([
								'type' 		=> 'text',
								'id' 		=> ovabrw_unique_id( 'start_date' ),
								'class' 	=> 'start-date ovabrw-input-required',
								'name' 		=> $this->get_meta_name( 'st_startdate[]' ),
								'value' 	=> $start,
								'data_type' => 'datepicker'
							]); ?>
					    </td>
					    <td width="12.5%">
					    	<?php ovabrw_wp_text_input([
								'type' 		=> 'text',
								'id' 		=> ovabrw_unique_id( 'end_date' ),
								'class' 	=> 'end-date ovabrw-input-required',
								'name' 		=> $this->get_meta_name( 'st_enddate[]' ),
								'value' 	=> $end,
								'data_type' => 'datepicker'
							]); ?>
					    </td>
					    <td width="49%">
					    	<table width="100%">
						      	<thead>
									<tr>
										<th class="ovabrw-required">
											<?php esc_html_e( 'Adult price', 'ova-brw' ); ?>
										</th>
										<?php if ( ovabrw_show_children( $this->get_id() ) ): ?>
											<th class="ovabrw-required">
												<?php esc_html_e( 'Child price', 'ova-brw' ); ?>
											</th>
										<?php endif; ?>
										<?php if ( ovabrw_show_babies( $this->get_id() ) ): ?>
											<th class="ovabrw-required">
												<?php esc_html_e( 'Baby price', 'ova-brw' ); ?>
											</th>
										<?php endif; ?>
										<th class="ovabrw-required">
											<?php esc_html_e( 'Min - Max: Guests', 'ova-brw' ); ?>
										</th>
										<th></th>
									</tr>
								</thead>
								<tbody>
								<?php if ( ovabrw_array_exists( $discounts ) ):
									// Discount adult prices
									$disc_adult_prices = ovabrw_get_meta_data( 'adult_price', $discounts );

									// Discount child prices
									$disc_child_prices = ovabrw_get_meta_data( 'children_price', $discounts );

									// Discount baby prices
									$disc_baby_prices = ovabrw_get_meta_data( 'baby_price', $discounts );

									// Discount min
									$disc_min = ovabrw_get_meta_data( 'min', $discounts );

									// Discount max
									$disc_max = ovabrw_get_meta_data( 'max', $discounts );

									if ( ovabrw_array_exists( $disc_adult_prices ) ):
										foreach ( $disc_adult_prices as $k => $adult_p ):
											// Child price
											$child_p = ovabrw_get_meta_data( $k, $disc_child_prices );

											// Baby price
											$baby_p = ovabrw_get_meta_data( $k, $disc_baby_prices );

											// Min
											$min = ovabrw_get_meta_data( $k, $disc_min );

											// Max
											$max = ovabrw_get_meta_data( $k, $disc_max );
										?>
											<tr>
												<td width="20%" class="ovabrw-input-price">
													<?php ovabrw_wp_text_input([
														'type' 			=> 'text',
														'class' 		=> 'ovabrw-std-adult-price ovabrw-input-required',
														'name' 			=> $this->get_meta_name( 'st_discount['.$i.'][adult_price][]' ),
														'value' 		=> $adult_p,
														'placeholder' 	=> '10',
														'data_type' 	=> 'price'
													]); ?>
												</td>
												<?php if ( ovabrw_show_children( $this->get_id() ) ): ?>
													<td width="20%" class="ovabrw-input-price"> 
														<?php ovabrw_wp_text_input([
															'type' 			=> 'text',
															'class' 		=> 'ovabrw-std-child-price ovabrw-input-required',
															'name' 			=> $this->get_meta_name( 'st_discount['.$i.'][children_price][]' ),
															'value' 		=> $child_p,
															'placeholder' 	=> '10',
															'data_type' 	=> 'price'
														]); ?>
													</td>
												<?php endif; ?>
												<?php if ( ovabrw_show_babies( $this->get_id() ) ): ?>
													<td width="20%" class="ovabrw-input-price">
														<?php ovabrw_wp_text_input([
															'type' 			=> 'text',
															'class' 		=> 'ovabrw-std-baby-price ovabrw-input-required',
															'name' 			=> $this->get_meta_name( 'st_discount['.$i.'][baby_price][]' ),
															'value' 		=> $baby_p,
															'placeholder' 	=> '10',
															'data_type' 	=> 'price'
														]); ?>
													</td>
												<?php endif; ?>
												<td width="39%" class="ovabrw-special-discount-duration">
													<?php ovabrw_wp_text_input([
														'type' 			=> 'text',
														'class' 		=> 'ovabrw-std-min ovabrw-input-required',
														'name' 			=> $this->get_meta_name( 'st_discount['.$i.'][min][]' ),
														'value' 		=> $min,
														'placeholder' 	=> '1',
														'data_type' 	=> 'number',
														'attrs' 		=> [
															'min' => 0
														]
													]); ?>
													<?php ovabrw_wp_text_input([
														'type' 			=> 'text',
														'class' 		=> 'ovabrw-std-max ovabrw-input-required',
														'name' 			=> $this->get_meta_name( 'st_discount['.$i.'][max][]' ),
														'value' 		=> $max,
														'placeholder' 	=> '2',
														'data_type' 	=> 'number',
														'attrs' 		=> [
															'min' => 0
														]
													]); ?>
												</td>
												<td width="1%">
													<button class="button ovabrw-remove-special-discount">x</button>
												</td>
											</tr>
										<?php endforeach;
									endif;
								endif; ?>
								</tbody>
								<tfoot>
									<tr>
										<th colspan="6">
											<button class="button ovabrw-add-special-discount">
												<?php esc_html_e( 'Add discount', 'ova-brw' ); ?>
											</button>
										</th>
									</tr>
								</tfoot>
					      	</table>
					    </td>
					    <td width="1%">
					    	<button class="button ovabrw-remove-special-time">x</button>
					    </td>
					</tr>
				<?php endforeach;
			endif; ?>
		</tbody>
		<tfoot>
			<tr>
				<th colspan="7">
					<button class="button ovabrw-add-special-time" data-row="<?php
						ob_start();
						include( OVABRW_PLUGIN_PATH.'admin/metabox/fields/ovabrw_st_record.php' );
						echo esc_attr( ob_get_clean() );
					?>" data-new-discount="<?php
						ob_start();
						include( OVABRW_PLUGIN_PATH.'admin/metabox/fields/ovabrw_st_discount.php' );
						echo esc_attr( ob_get_clean() );
					?>">
						<?php esc_html_e( 'Add special time', 'ova-brw' ); ?></a>
					</button>
				</th>
			</tr>
		</tfoot>
	</table>
</div>