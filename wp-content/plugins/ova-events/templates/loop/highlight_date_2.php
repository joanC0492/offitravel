<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get event id
if ( isset( $args['id'] ) ) {
	$id = $args['id'];
} else {
	$id = get_the_id();	
}
if ( !$id ) return;

// Get event start date
$start_date = get_post_meta( $id, 'ovaev_start_date_time', true );

// Convert event date
$event_date = $start_date != '' ? date_i18n( 'd', $start_date ) : '';

// Convert event month
$event_month = $start_date != '' ? date_i18n( 'M', $start_date ) : '';

// Convert event year
$event_year = $start_date != '' ? date_i18n( 'Y', $start_date ) : '';

if ( $start_date != '' ): ?>
	<div class="date-event">
		<span class="date second_font">
			<?php echo esc_html( $event_date ); ?>
		</span>
		<span class="month-year second_font">
			<span class="month">
				<?php echo esc_html( $event_month ); ?>
			</span>
			<span class="year">
				<?php echo esc_html( $event_year ); ?>
			</span>
		</span>
	</div>
<?php endif;