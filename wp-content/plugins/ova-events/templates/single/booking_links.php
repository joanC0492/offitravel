<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get event id
$event_id = get_the_ID();
if ( !$event_id ) return;

// Get booking link
$booking_link = get_post_meta( $event_id, 'ovaev_booking_links', true );
if ( $booking_link ): ?>
	<a href="<?php echo esc_url( $booking_link ); ?>" target="_blank">
		<?php esc_html_e( 'Booking Now', 'ovaev' ); ?>
	</a>
<?php endif;