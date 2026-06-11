<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get date format
$date_format = ovabrw_get_date_format();

// Get unavailable start time
$untime_startdate = ovabrw_get_post_meta( $post_id, 'untime_startdate' );

// Get unavailable end time
$untime_enddate = ovabrw_get_post_meta( $post_id, 'untime_enddate' );

?>

<div class="ovabrw-unavailable-time">
	<table class="widefat">
		<thead>
			<tr>
				<th class="ovabrw-required">
					<?php esc_html_e( 'Start date', 'ova-brw' ); ?>
				</th>
				<th class="ovabrw-required">
					<?php esc_html_e( 'End date', 'ova-brw' ); ?>
				</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<!-- Append html here -->
			<?php if ( ovabrw_array_exists( $untime_startdate ) ):
				foreach ( $untime_startdate as $i => $start_date ):
					// Check start date
					if ( !strtotime( $start_date ) ) continue;

					// End date
					$end_date = ovabrw_get_meta_data( $i, $untime_enddate );
					if ( !strtotime( $end_date ) ) continue;
				?>
					<tr>
					    <td width="49.5%">
					    	<?php ovabrw_wp_text_input([
								'type' 		=> 'text',
								'id' 		=> ovabrw_unique_id( 'disabled_from' ),
								'class' 	=> 'ovabrw-input-required start-date',
								'name' 		=> $this->get_meta_name( 'untime_startdate[]' ),
								'value' 	=> $start_date,
								'data_type' => 'datepicker'
							]); ?>
					    </td>
					    <td width="49.5%">
					    	<?php ovabrw_wp_text_input([
								'type' 		=> 'text',
								'id' 		=> ovabrw_unique_id( 'disabled_to' ),
								'class' 	=> 'ovabrw-input-required end-date',
								'name' 		=> $this->get_meta_name( 'untime_enddate[]' ),
								'value' 	=> $end_date,
								'data_type' => 'datepicker'
							]); ?>
					    </td>
					    <td width="1%">
					    	<button class="button ovabrw-remove-unavailable-time">x</button>
					    </td>
					</tr>
				<?php endforeach;
			endif; ?>
		</tbody>
		<tfoot>
			<tr>
				<th colspan="3">
					<button class="button ovabrw-add-unavailable-time" data-row="<?php
						ob_start();
						include( OVABRW_PLUGIN_PATH.'/admin/metabox/fields/ovabrw_untime_field.php' );
						echo esc_attr( ob_get_clean() ); ?>">
						<?php esc_html_e( 'Add date range', 'ova-brw' ); ?>
					</button>
				</th>
			</tr>
		</tfoot>
	</table>
</div>