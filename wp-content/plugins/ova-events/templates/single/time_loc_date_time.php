<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get event id
if ( isset( $args['id'] ) ) {
	$id = $args['id'];
} else {
	$id = get_the_id();	
}
if ( !$id ) return;

// Date format
$date_format = apply_filters( 'ovaev_date_event_format', get_option( 'date_format' ) );

// Time format
$time_format = OVAEV_Settings::archive_event_format_time();

// Start date
$ovaev_start_date 	= get_post_meta( $id, 'ovaev_start_date_time', true );
$start_date    		= $ovaev_start_date != '' ? date_i18n( $date_format, $ovaev_start_date ) : '';

// Start time
$ovaev_start_time 	= get_post_meta( $id, 'ovaev_start_time', true );
$start_time 		= $ovaev_start_time ? date( $time_format, strtotime( $ovaev_start_time ) ) : '';

// End date
$ovaev_end_date   	= get_post_meta( $id, 'ovaev_end_date_time', true );
$end_date      		= $ovaev_end_date != '' ? date_i18n( $date_format, $ovaev_end_date ) : '';

// End time
$ovaev_end_time   	= get_post_meta( $id, 'ovaev_end_time', true );
$end_time      		= $ovaev_end_time ? date( $time_format, strtotime( $ovaev_end_time ) ) : '';

if ( $start_date && $end_date ): ?>
	<div class="wrap-date wrap-pro">
		<i class="icomoon icomoon-clock"></i>
		<?php if ( $start_date == $end_date && $start_date != '' ): ?>
			<span class="time-date-child">
				<span class="date-child">
					<?php echo esc_html( $start_time ).' - '.$end_time; ?>
				</span>
			</span>
		<?php else: ?>
			<span class="time-date-child">
				<span><?php echo esc_html( $start_time ) . ' - '; ?></span>
				<span class="date-child">
					<?php echo esc_html( $end_date ) .' '. esc_html__( '@', 'ovaev' ) . ' ' . esc_html( $end_time ); ?>
				</span>
			</span>
		<?php endif; ?>
	</div>
<?php endif; ?>