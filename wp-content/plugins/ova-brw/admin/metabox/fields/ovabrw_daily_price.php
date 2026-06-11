<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get timeslots
$timeslots = $this->get_meta_value( 'schedule_time' );

// Get adult prices
$adult_prices = $this->get_meta_value( 'schedule_adult_price' );

// Get children price
$child_prices = $this->get_meta_value( 'schedule_children_price' );

// Get baby prices
$baby_prices = $this->get_meta_value( 'schedule_baby_price' );

// Get types
$types = $this->get_meta_value( 'schedule_type' );

// Daily
$args_daily = [
	'monday' 	=> esc_html__( 'Monday', 'ova-brw' ),
	'tuesday' 	=> esc_html__( 'Tuesday', 'ova-brw' ),
	'wednesday' => esc_html__( 'Wednesday', 'ova-brw' ),
	'thursday' 	=> esc_html__( 'Thursday', 'ova-brw' ),
	'friday' 	=> esc_html__( 'Friday', 'ova-brw' ),
	'saturday' 	=> esc_html__( 'Saturday', 'ova-brw' ),
	'sunday' 	=> esc_html__( 'Sunday', 'ova-brw' )
];

?>

<div class="ovabrw_daily_price">
	<div class="ovabrw_daily_group">
	<?php foreach ( $args_daily as $day => $label ):
		// Daily timeslots
		$daily_timeslots = ovabrw_get_meta_data( $day, $timeslots );

		// Daily adult prices
		$daily_adult_prices = ovabrw_get_meta_data( $day, $adult_prices );

		// Daily child prices
		$daily_child_prices = ovabrw_get_meta_data( $day, $child_prices );

		// Daily baby prices
		$daily_baby_prices = ovabrw_get_meta_data( $day, $baby_prices );

		// Daily types
		$daily_types = ovabrw_get_meta_data( $day, $types );
	?>
		<div class="ovabrw_daily_day ovabrw_<?php echo esc_attr( $day ); ?>">
			<div class="ovabrw_daily_label"><?php echo esc_html( $label ); ?></div>
			<table class="ovabrw_daily_time widefat">
				<thead>
					<tr>
						<th class="ovabrw-required">
							<?php esc_html_e( 'Time', 'ova-brw' ); ?>
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
						<th class="ovabrw-required">
							<?php esc_html_e( 'Type', 'ova-brw' ); ?>
						</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( ovabrw_array_exists( $daily_timeslots ) ): ?>
						<?php foreach ( $daily_timeslots as $k => $time ):
							// Adult price
							$adult_price = ovabrw_get_meta_data( $k, $daily_adult_prices );

							// Child price
							$child_price = ovabrw_get_meta_data( $k, $daily_child_prices );

							// Baby price
							$baby_price = ovabrw_get_meta_data( $k, $daily_baby_prices );

							// Type
							$type = ovabrw_get_meta_data( $k, $daily_types );
						?>
							<tr>
								<td width="19%">
									<?php ovabrw_wp_text_input([
							            'type'      => 'text',
							            'class'     => 'ovabrw-input-required',
							            'name'      => $this->get_meta_name( 'schedule_time['.$day.'][]' ),
							            'value' 	=> $time,
							            'data_type' => 'timepicker'
							        ]); ?>
							    </td>
							    <td width="22%" class="ovabrw-input-price">
							    	<?php ovabrw_wp_text_input([
							            'type'          => 'text',
							            'class'         => 'ovabrw-input-required',
							            'name'          => $this->get_meta_name( 'schedule_adult_price['.$day.'][]' ),
							            'value' 		=> $adult_price,
							            'placeholder'   => '10',
							            'data_type'     => 'price'
							        ]); ?>
							    </td>
							    <?php if ( ovabrw_show_children( $this->get_id() ) ): ?>
								    <td width="22%" class="ovabrw-input-price">
								    	<?php ovabrw_wp_text_input([
								            'type'          => 'text',
								            'class'         => 'ovabrw-input-required',
								            'name'          => $this->get_meta_name( 'schedule_children_price['.$day.'][]' ),
								            'value' 		=> $child_price,
								            'placeholder'   => '10',
								            'data_type'     => 'price'
								        ]); ?>
								    </td>
								<?php endif; ?>
								<?php if ( ovabrw_show_babies( $this->get_id() ) ): ?>
								    <td width="22%" class="ovabrw-input-price">
								    	<?php ovabrw_wp_text_input([
								            'type'          => 'text',
								            'class'         => 'ovabrw-input-required',
								            'name'          => $this->get_meta_name( 'schedule_baby_price['.$day.'][]' ),
								            'value' 		=> $baby_price,
								            'placeholder'   => '10',
								            'data_type'     => 'price'
								        ]); ?>
								    </td>
								<?php endif; ?>
							    <td width="14%">
							        <select name="<?php echo esc_attr( $this->get_meta_name( 'schedule_type['.$day.'][]' ) ); ?>" class="ovabrw-input-required">
							            <option value="person"<?php ovabrw_selected( 'person', $type ); ?>>
							                <?php esc_html_e( '/per person', 'ova-brw' ); ?>
							            </option>
							            <option value="total"<?php ovabrw_selected( 'total', $type ); ?>>
							                <?php esc_html_e( '/order', 'ova-brw' ); ?>
							            </option>
							        </select>
							    </td>
							    <td width="1%">
							        <button class="button ovabrw-remove-timeslot">x</button>
							    </td>
							</tr>
					<?php endforeach;
				endif; ?>
				</tbody>
				<tfoot>
					<tr>
						<th colspan="6">
							<button class="button ovabrw-add-timeslot" data-row="<?php
								ob_start();
								include( OVABRW_PLUGIN_PATH.'admin/metabox/fields/schedule/'.sanitize_file_name( $day ).'.php' );
								echo esc_attr( ob_get_clean() ); ?>">
								<?php esc_html_e( 'Add timeslot', 'ova-brw' ); ?>
							</a>
						</th>
					</tr>
				</tfoot>
			</table>
		</div>
	<?php endforeach; ?>
	</div>
</div>