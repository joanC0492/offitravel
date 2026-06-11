<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get service labels
$serv_labels = $this->get_meta_value( 'label_service' );

// Get service required
$serv_required = $this->get_meta_value( 'service_required' );

// Get service option ids
$serv_opt_ids = $this->get_meta_value( 'service_id' );

// Get service option names
$serv_opt_names = $this->get_meta_value( 'service_name' );

// Get service adult prices
$serv_adult_prices = $this->get_meta_value( 'service_adult_price' );

// Get service child prices
$serv_child_prices = $this->get_meta_value( 'service_children_price' );

// Get service baby prices
$serv_baby_prices = $this->get_meta_value( 'service_baby_price' );

// Get service quatity
$serv_quantity = $this->get_meta_value( 'service_quantity' );

// Get service duration types
$serv_duration_types = $this->get_meta_value( 'service_duration_type' );

?>

<div class="ovabrw-services">
	<div class="ovabrw-service-container">
		<?php if ( ovabrw_array_exists( $serv_labels ) ):
			foreach ( $serv_labels as $i => $label ):
				// Required
				$required = ovabrw_get_meta_data( $i, $serv_required );

				// Get option ids
				$opt_ids = ovabrw_get_meta_data( $i, $serv_opt_ids );

				// Get option names
				$opt_names = ovabrw_get_meta_data( $i, $serv_opt_names );

				// Get adult prices
				$adult_prices = ovabrw_get_meta_data( $i, $serv_adult_prices );

				// Get child prices
				$child_prices = ovabrw_get_meta_data( $i, $serv_child_prices );

				// Get baby prices
				$baby_prices = ovabrw_get_meta_data( $i, $serv_baby_prices );

				// Get quantity
				$qtys = ovabrw_get_meta_data( $i, $serv_quantity );

				// Get duration types
				$duration_types = ovabrw_get_meta_data( $i, $serv_duration_types );
			?>
				<div class="ovabrw-service-group">
					<div class="ovabrw-service-header">
						<div class="ovabrw-service-field">
							<span class="ovabrw-required">
								<?php esc_html_e( 'Label', 'ova-brw' ); ?>
							</span>
							<?php ovabrw_wp_text_input([
								'type' 	=> 'text',
								'class' => 'ovabrw-input-required ovabrw_input_label',
								'name' 	=> $this->get_meta_name( 'label_service[]' ),
								'value' => $label
							]); ?>
						</div>
						<div class="ovabrw-service-field">
							<span class="ovabrw-required">
								<?php esc_html_e( 'Required', 'ova-brw' ); ?>
							</span>
							<select name="<?php echo esc_attr( $this->get_meta_name( 'service_required[]' ) ); ?>" class="ovabrw-input-required">
								<option value="yes"<?php ovabrw_selected( 'yes', $required ); ?>>
									<?php esc_html_e( 'Yes', 'ova-brw' ); ?>
								</option>
								<option value="no"<?php ovabrw_selected( 'no', $required ); ?>>
									<?php esc_html_e( 'No', 'ova-brw' ); ?>
								</option>
							</select>
						</div>
					</div>
					<button class="button ovabrw-remove-service">x</button>
					<table class="widefat">
						<thead>
							<tr>
								<th class="ovabrw-required">
									<?php esc_html_e( 'Unique ID', 'ova-brw' ); ?>
								</th>
								<th class="ovabrw-required">
									<?php esc_html_e( 'Name', 'ova-brw' ); ?>
								</th>
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
								<th>
									<?php esc_html_e( 'Max quantity', 'ova-brw' ); ?>
									<?php echo wc_help_tip( esc_html__( 'Maximum number of guests per booking.', 'ova-brw' ) ); ?>
								</th>
								<th class="ovabrw-required">
									<?php esc_html_e( 'Type', 'ova-brw' ); ?>
								</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							<?php if ( ovabrw_array_exists( $opt_ids ) ):
								foreach ( $opt_ids as $k => $opt_id ):
									// Option name
									$opt_name = ovabrw_get_meta_data( $k, $opt_names );

									// Adult price
									$adult_price = ovabrw_get_meta_data( $k, $adult_prices );

									// Child price
									$child_price = ovabrw_get_meta_data( $k, $child_prices );

									// Baby price
									$baby_price = ovabrw_get_meta_data( $k, $baby_prices );

									// Quantity
									$qty = ovabrw_get_meta_data( $k, $qtys );

									// Duration type
									$duration_type = ovabrw_get_meta_data( $k, $duration_types );
								?>
								<tr>
								    <td width="13%">
								    	<?php ovabrw_wp_text_input([
								    		'type' 			=> 'text',
								    		'class' 		=> 'ovabrw-input-required',
								    		'name' 			=> $this->get_meta_name( 'service_id['.$i.'][]' ),
								    		'value' 		=> $opt_id,
								    		'placeholder' 	=> esc_html__( 'No space', 'ova-brw' )
								    	]); ?>
								    </td>
								    <td width="25%">
								    	<?php ovabrw_wp_text_input([
								    		'type' 	=> 'text',
								    		'class' => 'ovabrw-input-required',
								    		'name' 	=> $this->get_meta_name( 'service_name['.$i.'][]' ),
								    		'value' => $opt_name
								    	]); ?>
								    </td>
								    <td width="13%" class="ovabrw-input-price">
								    	<?php ovabrw_wp_text_input([
								            'type'          => 'text',
								            'class'         => 'ovabrw-input-required',
								            'name'          => $this->get_meta_name( 'service_adult_price['.$i.'][]' ),
								            'value' 		=> $adult_price,
								            'placeholder'   => '10',
								            'data_type'     => 'price'
								        ]); ?>
								    </td>
								    <?php if ( ovabrw_show_children( $this->get_id() ) ): ?>
									    <td width="13%" class="ovabrw-input-price">
									    	<?php ovabrw_wp_text_input([
									            'type'          => 'text',
									            'class'         => 'ovabrw-input-required',
									            'name'          => $this->get_meta_name( 'service_children_price['.$i.'][]' ),
									            'value' 		=> $child_price,
									            'placeholder'   => '10',
									            'data_type'     => 'price'
									        ]); ?>
									    </td>
									<?php endif; ?>
									<?php if ( ovabrw_show_babies( $this->get_id() ) ): ?>
									    <td width="13%" class="ovabrw-input-price">
									      	<?php ovabrw_wp_text_input([
									            'type'          => 'text',
									            'class'         => 'ovabrw-input-required',
									            'name'          => $this->get_meta_name( 'service_baby_price['.$i.'][]' ),
									            'value' 		=> $baby_price,
									            'placeholder'   => '10',
									            'data_type'     => 'price'
									        ]); ?>
									    </td>
									<?php endif; ?>
								    <td width="10%">
								      	<?php ovabrw_wp_text_input([
								            'type'          => 'text',
								            'name'          => $this->get_meta_name( 'service_quantity['.$i.'][]' ),
								            'value' 		=> $qty,
								            'placeholder'   => '10',
								            'data_type'     => 'number',
								            'attrs'         => [
								                'min' => 0
								            ]
								        ]); ?>
								    </td>
								    <td width="12%">
								      	<select name="<?php echo esc_attr( $this->get_meta_name( 'service_duration_type['.$i.'][]' ) ); ?>" class="ovabrw-input-required">
											<option value="person"<?php ovabrw_selected( 'person', $duration_type ); ?>>
												<?php esc_html_e( '/per person', 'ova-brw' ); ?>
											</option>
								    		<option value="total"<?php ovabrw_selected( 'total', $duration_type ); ?>>
								    			<?php esc_html_e( '/order', 'ova-brw' ); ?>
								    		</option>
								        </select>
								    </td>
								    <td width="1%">
								    	<button class="button ovabrw-remove-service-option">x</button>
								    </td>
								</tr>
								<?php endforeach;
							endif; ?>
						</tbody>
						<tfoot>
							<tr>
								<th colspan="7">
									<button class="button ovabrw-add-service-option" data-pos="<?php echo esc_attr( $i ); ?>">
										<?php esc_html_e( 'Add option', 'ova-brw' ); ?>
									</button>
								</th>
							</tr>
						</tfoot>
					</table>
				</div>
			<?php endforeach;
		endif; ?>
	</div>
	<button class="button ovabrw-add-service" data-row="<?php
		ob_start();
		include( OVABRW_PLUGIN_PATH . 'admin/metabox/fields/ovabrw_service_group.php' );
		echo esc_attr( ob_get_clean() ); ?>" data-row-option="<?php
		ob_start();
		include( OVABRW_PLUGIN_PATH . 'admin/metabox/fields/ovabrw_service_field.php' );
		echo esc_attr( ob_get_clean() ); ?>">
		<?php esc_html_e( 'Add service', 'ova-brw' ); ?></a>
	</a>
</div>


