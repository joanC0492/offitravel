<?php if ( !defined( 'ABSPATH' ) ) exit(); ?>

<div class="ovabrw-service-group">
	<div class="ovabrw-service-header">
		<div class="ovabrw-service-field">
			<span class="ovabrw-required">
				<?php esc_html_e( 'Label', 'ova-brw' ); ?>
			</span>
			<?php ovabrw_wp_text_input([
				'type' 	=> 'text',
				'class' => 'ovabrw-input-required ovabrw_input_label',
				'name' 	=> $this->get_meta_name( 'label_service[]' )
			]); ?>
		</div>
		<div class="ovabrw-service-field">
			<span class="ovabrw-required">
				<?php esc_html_e( 'Required', 'ova-brw' ); ?>
			</span>
			<select name="<?php echo esc_attr( $this->get_meta_name( 'service_required[]' ) ); ?>" class="ovabrw-input-required">
				<option value="yes">
					<?php esc_html_e( 'Yes', 'ova-brw' ); ?>
				</option>
				<option value="no" selected>
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
		<tbody></tbody>
		<tfoot>
			<tr>
				<th colspan="8">
					<button class="button ovabrw-add-service-option">
						<?php esc_html_e( 'Add option', 'ova-brw' ); ?>
					</button>
				</th>
			</tr>
		</tfoot>
	</table>
</div>