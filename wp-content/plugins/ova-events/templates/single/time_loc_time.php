<?php if ( !defined( 'ABSPATH' ) ) exit(); 

// Get event id
$event_id = get_the_ID();
if ( !$event_id ) return;

// Get time format
$time_format = OVAEV_Settings::archive_event_format_time();

// Get event start time
$ovaev_start_time = get_post_meta( $event_id, 'ovaev_start_time', true );

// Get event end time
$ovaev_end_time = get_post_meta( $event_id, 'ovaev_end_time', true );

// Convert start time
$start_time = strtotime( $ovaev_start_time ) ? date( $time_format, strtotime( $ovaev_start_time ) ) : '';

// Convert end time
$end_time = strtotime( $ovaev_end_time ) ? date( $time_format, strtotime( $ovaev_end_time ) ) : '';

if ( $start_time && $end_time ): ?>
	<div class="wrap-time wrap-pro">
		<i class="icomoon icomoon-clock"></i>
		<span class="second_font general-content"><?php echo esc_html( $start_time ); ?> - </span>
		<span class="second_font general-content"><?php echo esc_html( $end_time ); ?></span>
	</div>
<?php endif; ?>