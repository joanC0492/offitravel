<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get product id
$product_id = tripgo_get_meta_data( 'id', $args, get_the_id() );

// Get product
$product = wc_get_product( $product_id );
if ( !$product || !$product->is_type( 'ovabrw_car_rental' ) ) return;

// Get guests
$guests = tripgo_get_meta_data( 'guests', $args );

if ( tripgo_array_exists( $guests  ) ):
	foreach ( $guests as $name => $item ):
		// Label
		$label = tripgo_get_meta_data( 'label', $item );

		// Number of guests
		$numberof_guests = (int)tripgo_get_meta_data( 'number', $item );

		if ( !$numberof_guests ): ?>
			<div class="guest-info-item" data-guest-name="<?php echo esc_attr( $name ); ?>" style="display: none;">
		<?php else: ?>
			<div class="guest-info-item" data-guest-name="<?php echo esc_attr( $name ); ?>">
		<?php endif; ?>
			<div class="guest-info-header">
				<h3 class="ovabrw-label"><?php echo esc_html( $label ); ?></h3>
				<i class="icomoon icomoon-caret-down" aria-hidden="true"></i>
			</div>
			<div class="guest-info-body">
				<div class="guest-info-total">
					<span class="text">
						<?php esc_html_e( 'Total guests:', 'tripgo' ); ?>
					</span>
					<span class="number">
						<?php echo esc_html( $numberof_guests ); ?>
					</span>
				</div>
				<div class="guest-info-content">
					<div class="guest-info-accordion">
						<?php for ( $k = 0; $k < $numberof_guests; $k++ ) {
							// Get template
							wc_get_template( 'rental/loop/fields/guest-info.php', [
				                'id' 			=> $product_id,
								'guest_name' 	=> $name,
								'key'        	=> $k
				            ]);
						} ?>
					</div>
				</div>
			</div>
		</div>
	<?php endforeach;
endif; ?>