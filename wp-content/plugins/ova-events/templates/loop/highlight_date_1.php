<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get event id
if ( isset( $args['id'] ) ) {
	$id = $args['id'];
} else {
	$id = get_the_id();	
}
if ( !$id ) return;

// Get start date
$start_date 	= get_post_meta( $id, 'ovaev_start_date_time', true );
$event_date 	= $start_date != '' ? date_i18n( 'd', $start_date ) : '';
$event_month 	= $start_date != '' ? date_i18n( 'M', $start_date ) : '';

if ( $start_date != '' ): ?>
	<div class="date-event">
		<span class="date"><?php echo esc_html( $event_date ); ?></span>
		<span class="month"><?php echo esc_html( $event_month ); ?></span>
	</div>
<?php endif;