<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get resource ids
$res_ids = $this->get_meta_value( 'rs_id' );

// Get resource names
$res_names = $this->get_meta_value( 'rs_name' );

// Get resource description
$res_desc = $this->get_meta_value( 'rs_description' );

// Get resource adult prices
$adult_prices = $this->get_meta_value( 'rs_adult_price' );

// Get resource child price
$child_prices = $this->get_meta_value( 'rs_children_price' );

// Get resource baby price
$baby_prices = $this->get_meta_value( 'rs_baby_price' );

// Get resource quantity
$res_qtys = $this->get_meta_value('rs_quantity');

// Get resource duration type
$types = $this->get_meta_value( 'rs_duration_type' );

?>

<div class="ovabrw-resources">
	<table class="widefat">
		<thead>
			<tr>
				<th class="ovabrw-required">
					<?php esc_html_e( 'Unique ID', 'ova-brw' ); ?>
				</th>
				<th class="ovabrw-required">
					<?php esc_html_e( 'Name', 'ova-brw' ); ?>
				</th>
				<th>
					<?php esc_html_e( 'Description', 'ova-brw' ); ?>
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
			<?php if ( ovabrw_array_exists( $res_ids ) ):
				foreach ( $res_ids as $i => $id ):
					// Name
					$name = ovabrw_get_meta_data( $i, $res_names );

					// Description
					$decs = ovabrw_get_meta_data( $i, $res_desc );

					// Adult price
					$adult_price = ovabrw_get_meta_data( $i, $adult_prices );

					// Child price
					$child_price = ovabrw_get_meta_data( $i, $child_prices );

					// Baby price
					$baby_price = ovabrw_get_meta_data( $i, $baby_prices );

					// Quantity
					$qty = ovabrw_get_meta_data( $i, $res_qtys );

					// Type
					$type = ovabrw_get_meta_data( $i, $types );
				?>
					<tr>
						<td width="12%">
							<?php ovabrw_wp_text_input([
								'type' 			=> 'text',
								'class' 		=> 'ovabrw-input-required',
								'name' 			=> $this->get_meta_name( 'rs_id[]' ),
								'value' 		=> $id,
								'placeholder' 	=> esc_html__( 'No space', 'ova-brw' )
							]); ?>
					    </td>
					    <td width="20%">
					    	<?php ovabrw_wp_text_input([
								'type' 	=> 'text',
								'class' => 'ovabrw-input-required',
								'name' 	=> $this->get_meta_name( 'rs_name[]' ),
								'value' => $name
							]); ?>
					    </td>
					    <td width="20%">
					    	<?php ovabrw_wp_text_input([
								'type' 	=> 'text',
								'name' 	=> $this->get_meta_name( 'rs_description[]' ),
								'value' => $decs
							]); ?>
					    </td>
					    <td width="9%" class="ovabrw-input-price">
					    	<?php ovabrw_wp_text_input([
					            'type'          => 'text',
					            'class'         => 'ovabrw-input-required',
					            'name'          => $this->get_meta_name( 'rs_adult_price[]' ),
					            'value' 		=> $adult_price,
					            'placeholder'   => '10',
					            'data_type'     => 'price'
					        ]); ?>
					    </td>
					    <?php if ( ovabrw_show_children( $this->get_id() ) ): ?>
						    <td width="9%" class="ovabrw-input-price">
						    	<?php ovabrw_wp_text_input([
						            'type'          => 'text',
						            'class'         => 'ovabrw-input-required',
						            'name'          => $this->get_meta_name( 'rs_children_price[]' ),
						            'value' 		=> $child_price,
						            'placeholder'   => '10',
						            'data_type'     => 'price'
						        ]); ?>
						    </td>
						<?php endif; ?>
						<?php if ( ovabrw_show_babies( $this->get_id() ) ): ?>
						    <td width="9%" class="ovabrw-input-price">
						    	<?php ovabrw_wp_text_input([
						            'type'          => 'text',
						            'class'         => 'ovabrw-input-required',
						            'name'          => $this->get_meta_name( 'rs_baby_price[]' ),
						            'value' 		=> $baby_price,
						            'placeholder'   => '10',
						            'data_type'     => 'price'
						        ]); ?>
						    </td>
						<?php endif; ?>
					    <td width="9%">
					    	<?php ovabrw_wp_text_input([
					            'type'          => 'number',
					            'name'          => $this->get_meta_name( 'rs_quantity[]' ),
					            'value' 		=> $qty,
					            'placeholder'   => '10',
					            'data_type'     => 'number',
					            'attrs'         => [
					                'min' => 0
					            ]
					        ]); ?>
					    </td>
					    <td width="11%">
					    	<select name="<?php echo esc_attr( $this->get_meta_name( 'rs_duration_type[]' ) ); ?>" class="ovabrw-input-required">
								<option value="person"<?php ovabrw_selected( $type, 'person' ); ?>>
									<?php esc_html_e( '/per person', 'ova-brw' ); ?>
								</option>
					    		<option value="total"<?php ovabrw_selected( $type, 'total' ); ?>>
					    			<?php esc_html_e( '/order', 'ova-brw' ); ?>
					    		</option>
					    	</select>
					    </td>
					    <td width="1%">
					    	<button class="button ovabrw-remove-resource">x</button>
					    </td>
					</tr>
				<?php endforeach;
			endif; ?>
		</tbody>
		<tfoot>
			<tr>
				<th colspan="9">
					<button class="button ovabrw-add-resource" data-row="<?php
						ob_start();
						include( OVABRW_PLUGIN_PATH . 'admin/metabox/fields/ovabrw_resources_field.php' );
						echo esc_attr( ob_get_clean() ); ?>">
						<?php esc_html_e( 'Add option', 'ova-brw' ); ?></a>
					</button>
				</th>
			</tr>
		</tfoot>
	</table>
</div>


