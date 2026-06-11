<?php if ( !defined( 'ABSPATH' ) ) exit();

if ( ovabrw_array_exists( $guests  ) ):
	foreach ( $guests as $guest_name => $item ):
		// Label
		$label = ovabrw_get_meta_data( 'label', $item );

		// Number of guests
		$numberof_guests = (int)ovabrw_get_meta_data( 'number', $item );

		if ( !$numberof_guests ): ?>
			<div class="guest-info-item" data-guest-name="<?php echo esc_attr( $guest_name ); ?>" style="display: none;">
		<?php else: ?>
			<div class="guest-info-item" data-guest-name="<?php echo esc_attr( $guest_name ); ?>">
		<?php endif; ?>
			<div class="guest-info-header">
				<h3 class="ovabrw-label"><?php echo esc_html( $label ); ?></h3>
				<span class="dashicons dashicons-arrow-down" aria-hidden="true"></span>
			</div>
			<div class="guest-info-body">
				<div class="guest-info-total">
					<span class="text">
						<?php esc_html_e( 'Total guests:', 'ova-brw' ); ?>
					</span>
					<span class="number">
						<?php echo esc_html( $numberof_guests ); ?>
					</span>
				</div>
				<div class="guest-info-content">
					<div class="guest-info-accordion">
						<?php for ( $key = 0; $key < $numberof_guests; $key++ ) {
							// Get template
							include( OVABRW_PLUGIN_PATH.'admin/order/fields/guest-info.php' );
						} ?>
					</div>
				</div>
			</div>
		</div>
	<?php endforeach;
endif; ?>