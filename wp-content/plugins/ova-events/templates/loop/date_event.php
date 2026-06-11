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
$start_time 		= $ovaev_start_time ? date( $time_format, strtotime($ovaev_start_time) ) : '';

// End date
$ovaev_end_date   	= get_post_meta( $id, 'ovaev_end_date_time', true );
$end_date      		= $ovaev_end_date != '' ? date_i18n( $date_format, $ovaev_end_date) : '';

// End time
$ovaev_end_time   	= get_post_meta( $id, 'ovaev_end_time', true );
$end_time      		= $ovaev_end_time ? date( $time_format, strtotime($ovaev_end_time) ) : '';

?>
<div class="time equal-date">
	<i class="icomoon icomoon-clock"></i>
	<?php if ( $start_date == $end_date && $start_date != '' ): ?>
		<span class="time-date-child">
			<span class="date-child">
				<?php echo esc_html( $start_time ).' - '.$end_time; ?>
			</span>
		</span>
	<?php else: ?>
		<span class="time-date-child">
			<span class="date-child">
				<?php echo esc_html( $start_date ) .' '. esc_html__( '@', 'ovaev' ); ?>
			</span>
			<span><?php echo esc_html( $start_time ); ?></span>
			<?php if ( apply_filters( 'ovaev_show_more_date_text', true ) ): ?>
				<a href="<?php echo esc_url( get_the_permalink( $id ) ); ?> " class="more_date_text" data-id="<?php echo get_the_id(); ?>">
					<span><?php esc_html_e( ', more', 'ovaev' ); ?></span>	
				</a>
			<?php endif; ?>
	
		</span>
	<?php endif; ?>
</div>