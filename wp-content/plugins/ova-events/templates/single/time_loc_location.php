<?php if ( !defined( 'ABSPATH' ) ) exit(); 

// Get event id
$event_id = get_the_ID();
if ( !$event_id ) return;

// Get event venue
$event_venue = get_post_meta( $event_id, 'ovaev_venue', true );
if ( $event_venue ): ?>
	<div class="wrap-location wrap-pro">
		<i class="icomoon icomoon-location"></i>
		<span class="second_font general-content"><?php echo esc_html( $event_venue ); ?></span>
	</div>
<?php endif; ?>