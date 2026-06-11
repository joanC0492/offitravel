<?php if ( !defined( 'ABSPATH' ) ) exit();

// Fixed time check-in
$fixed_checkin_date = ovabrw_get_post_meta( $post_id, 'fixed_time_check_in' );

// Fixed time check-out
$fixed_checkout_date = ovabrw_get_post_meta( $post_id, 'fixed_time_check_out' );

?>
<div class="ovabrw_fixed_time">
	<table class="widefat">
		<thead>
			<tr>
				<th class="ovabrw-required">
					<?php esc_html_e( 'Check-in date', 'ova-brw' ); ?>
				</th>
				<th class="ovabrw-required">
					<?php esc_html_e( 'Check-out date', 'ova-brw' ); ?>
				</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( ovabrw_array_exists( $fixed_checkin_date ) ): 
				foreach ( $fixed_checkin_date as $i => $checkin_date ):
					// Check-in date
					if ( !strtotime( $checkin_date ) ) continue;

					// Check-out date
					$checkout_date = ovabrw_get_meta_data( $i, $fixed_checkout_date );
					if ( !strtotime( $checkout_date ) ) continue;
			?>
				<tr>
				    <td width="49.5%">
            			<?php ovabrw_wp_text_input([
							'type' 		=> 'text',
							'id' 		=> ovabrw_unique_id( 'fixed_time_start' ),
							'class' 	=> 'ovabrw-input-required start-date',
							'name' 		=> $this->get_meta_name( 'fixed_time_check_in[]' ),
							'value' 	=> $checkin_date,
							'data_type' => 'datepicker'
						]); ?>
				    </td>
				    <td width="49.5%">
				    	<?php ovabrw_wp_text_input([
							'type' 		=> 'text',
							'id' 		=> ovabrw_unique_id( 'fixed_time_end' ),
							'class' 	=> 'ovabrw-input-required end-date',
							'name' 		=> $this->get_meta_name( 'fixed_time_check_out[]' ),
							'value' 	=> $checkout_date,
							'data_type' => 'datepicker'
						]); ?>
				    </td>
				    <td width="1%">
				    	<button class="button ovabrw-remove-fixed-time">x</button>
				    </td>
				</tr>
				<?php endforeach;
			endif; ?>
		</tbody>
		<tfoot>
			<tr>
				<th colspan="3">
					<button class="button ovabrw-add-fixed-time" data-row="<?php
						ob_start();
						include( OVABRW_PLUGIN_PATH.'/admin/metabox/fields/ovabrw_fixed_time_field.php' );
						echo esc_attr( ob_get_clean() );
					?>">
						<?php esc_html_e( 'Add date range', 'ova-brw' ); ?></a>
					</a>
				</th>
			</tr>
		</tfoot>
	</table>
</div>