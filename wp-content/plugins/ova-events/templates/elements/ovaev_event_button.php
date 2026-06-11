<?php if ( !defined( 'ABSPATH' ) ) exit(); 

// Get event id
$id = get_the_id();
if ( !$id ) return;

// Get target
$target = $args['target'] ? '_blank' : '_self';

// Get booking url
$booking_url = get_post_meta( $id, 'ovaev_booking_links', true );
if ( $booking_url ): ?>
	<div class="ovaev-booking-btn">
		<a href="<?php echo esc_url( $booking_url ); ?>" target="<?php echo esc_attr( $target ); ?>">
			<?php esc_html_e( 'Booking Now', 'ovaev' ); ?>
		</a>
	</div>
<?php endif;