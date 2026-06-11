<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get event id
if ( isset( $args['id'] ) ) {
	$id = $args['id'];
} else {
	$id = get_the_id();	
}
if ( !$id ) return;

// Get start date
$start_date = get_post_meta( $id, 'ovaev_start_date_time', true );

// Convert event date
$event_date = $start_date != '' ? date_i18n('d', $start_date ) : '';

// Convert event month
$event_month = $start_date != '' ? date_i18n('F', $start_date ) : '';

// Get week day
$week_day = $start_date != '' ? date_i18n('l', $start_date ) : '';

if ( $start_date != '' ): ?>
	<div class="date-event">
		<span class="date-month second_font">
			<?php echo esc_html( $event_date ); ?>
			<span class="month second_font">
				<?php echo esc_html( $event_month ); ?>
			</span>	
		</span>
		<span class="weekday second_font">
			<?php echo esc_html( $week_day ); ?>
		</span>
	</div>
<?php endif;