<?php defined( 'ABSPATH' ) || exit;

// WP Media
wp_enqueue_media();

// Get guest information fields data
$guest_fields = ovabrw_recursive_replace( '\\', '', ovabrw_get_option( 'guest_fields', [] ) );

?>

<div class="wrap ovabrw-guest-info-fields">
	<h2><?php esc_html_e( 'Information fields', 'ova-brw' ); ?></h2>
	<div id="ovabrw-guest-info-description">
		<p>
			<?php printf( esc_html__( 'By default, all these fields will be applied to all guests. However, you can select specific fields for each guest in the %s tab.', 'ova-brw' ), '<a href="'.admin_url('/admin.php?page=wc-settings&tab=ova_brw&section=guests').'">'.esc_html__( 'Guests', 'ova-brw' ).'</a>' ); ?>
		</p>
	</div>
	<div class="heading">
		<div class="ovabrw-guest-info-action">
			<button type="button" class="button" name="ovabrw-guest-info-action" value="add" title="<?php esc_attr_e( 'Add field', 'ova-brw' ); ?>">
				<?php esc_html_e( 'Add field', 'ova-brw' ); ?>
			</button>
			<button type="submit" class="button" name="ovabrw-guest-info-action" value="required" title="<?php esc_attr_e( 'Required', 'ova-brw' ); ?>">
				<?php esc_html_e( 'Required', 'ova-brw' ); ?>
			</button>
			<button type="submit" class="button" name="ovabrw-guest-info-action" value="optional" title="<?php esc_attr_e( 'Optional', 'ova-brw' ); ?>">
				<?php esc_html_e( 'Optional', 'ova-brw' ); ?>
			</button>
			<button type="submit" class="button" name="ovabrw-guest-info-action" value="enable" title="<?php esc_attr_e( 'Enable', 'ova-brw' ); ?>">
				<?php esc_html_e( 'Enable', 'ova-brw' ); ?>
			</button>
			<button type="submit" class="button" name="ovabrw-guest-info-action" value="disable" title="<?php esc_attr_e( 'Disable', 'ova-brw' ); ?>">
				<?php esc_html_e( 'Disable', 'ova-brw' ); ?>
			</button>
			<button type="submit" class="button" name="ovabrw-guest-info-action" value="delete" title="<?php esc_attr_e( 'Delete', 'ova-brw' ); ?>">
				<?php esc_html_e( 'Delete', 'ova-brw' ); ?>
			</button>
			<div class="ovabrw-loading">
				<span class="dashicons dashicons-update-alt"></span>
			</div>
		</div>
	</div>
	<div class="content">
		<?php if ( ovabrw_array_exists( $guest_fields ) ): ?>
		<table class="widefat fixed">
			<thead>
				<tr>
					<td class="check-column">
						<input type="checkbox" class="ovabrw-check-all">
					</td>
					<th class="type">
						<?php esc_html_e( 'Type', 'ova-brw' ); ?>
					</th>
					<th class="name">
						<?php esc_html_e( 'Name', 'ova-brw' ); ?>
					</th>
					<th class="label">
						<?php esc_html_e( 'Label', 'ova-brw' ); ?>
					</th>
					<th class="required">
						<?php esc_html_e( 'Required', 'ova-brw' ); ?>
					</th>
					<th class="enable">
						<?php esc_html_e( 'Enable', 'ova-brw' ); ?>
					</th>
					<th class="actions">
						<?php esc_html_e( 'Actions', 'ova-brw' ); ?>
					</th>
				</tr>
			</thead>
			<tbody class="ovabrw-guest-info-sortable">
			<?php foreach ( $guest_fields as $item_name => $item_data ): ?>	
				<?php if ( $item_data['enable'] ): ?>
					<tr>
				<?php else: ?>
					<tr class="disabled">
				<?php endif; ?>
					<th class="ovabrw-check-column">
						<input
							type="checkbox"
							name="fields[]"
							value="<?php echo esc_attr( $item_name ); ?>"
						/>
					</th>
					<td>
						<?php echo esc_html( $item_data['type'] ); ?>
						<input
							type="hidden"
							name="type"
							value="<?php echo esc_attr( $item_data['type'] ); ?>"
						/>
					</td>
					<td><?php echo esc_html( $item_name ); ?></td>
					<td><?php echo esc_html( $item_data['label'] ); ?></td>
					<td class="required">
						<?php if ( $item_data['required'] ): ?>
							<span class="dashicons dashicons-yes tips" title="<?php esc_attr_e( 'Yes', 'ova-brw' ); ?>"></span>
						<?php endif; ?>
					</td>
					<td class="enable">
						<?php if ( $item_data['enable'] ): ?>
							<span class="dashicons dashicons-yes tips" title="<?php esc_attr_e( 'Yes', 'ova-brw' ); ?>"></span>
						<?php else: ?>
							<span class="dashicons dashicons-yes tips" title="<?php esc_attr_e( 'Yes', 'ova-brw' ); ?>" style="display: none;"></span>
						<?php endif; ?>
					</td>
					<td>
						<div class="actions">
							<span class="dashicons dashicons-edit" title="<?php esc_attr_e( 'Edit', 'ova-brw' ); ?>"></span>
							<span>|</span>
							<?php if ( $item_data['enable'] ): ?>
								<span class="dashicons dashicons-visibility active" title="<?php esc_attr_e( 'Disable', 'ova-brw' ); ?>"></span>
								<span class="dashicons dashicons-hidden" title="<?php esc_attr_e( 'Enable', 'ova-brw' ); ?>"></span>
							<?php else: ?>
								<span class="dashicons dashicons-visibility" title="<?php esc_attr_e( 'Disable', 'ova-brw' ); ?>"></span>
								<span class="dashicons dashicons-hidden active" title="<?php esc_attr_e( 'Enable', 'ova-brw' ); ?>"></span>
							<?php endif; ?>
							<span>|</span>
							<span class="dashicons dashicons-trash" title="<?php esc_attr_e( 'Delete', 'ova-brw' ); ?>"></span>
							<span>|</span>
							<span class="dashicons dashicons-menu" title="<?php esc_attr_e( 'Grab & Drop', 'ova-brw' ); ?>"></span>
							<div class="ovabrw-loading">
								<span class="dashicons dashicons-update-alt"></span>
							</div>
						</div>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
			<tfoot>
				<tr>
					<td class="check-column">
						<input type="checkbox" class="ovabrw-check-all">
					</td>
					<th class="type">
						<?php esc_html_e( 'Type', 'ova-brw' ); ?>
					</th>
					<th class="name">
						<?php esc_html_e( 'Name', 'ova-brw' ); ?>
					</th>
					<th class="label">
						<?php esc_html_e( 'Label', 'ova-brw' ); ?>
					</th>
					<th class="required">
						<?php esc_html_e( 'Required', 'ova-brw' ); ?>
					</th>
					<th class="enable">
						<?php esc_html_e( 'Enable', 'ova-brw' ); ?>
					</th>
					<th class="actions">
						<?php esc_html_e( 'Actions', 'ova-brw' ); ?>
					</th>
				</tr>
			</tfoot>
		</table>
		<?php endif; ?>
	</div>
	<div class="ovabrw-popup-guest-info">
		<div class="ovabrw-popup-guest-info-field"></div>
	</div>
</div>