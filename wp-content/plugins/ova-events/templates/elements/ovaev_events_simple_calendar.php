<?php if ( !defined( 'ABSPATH' ) ) exit();

if ( isset( $args['id'] ) && $args['id'] ) {
	$pid = $args['id'];
} else {
	$pid = get_the_id();
}

$category 		= $args['category'];
$filter_event 	= $args['filter_event'] ? $args['filter_event'] : 'all';
$events 		= OVAEV_Get_Data::get_events_simple_calendar( $category, $filter_event );

// Get days of the week
$days_of_the_week = isset( $args['days_of_the_week'] ) ? $args['days_of_the_week'] : '';
if ( $days_of_the_week ) {
	$days_of_the_week = explode( '|', $days_of_the_week );

	if ( ! empty( $days_of_the_week ) && is_array( $days_of_the_week ) ) {
		$days_of_the_week = json_encode( $days_of_the_week );
	}
}
	
?>

<div class="ovaev_simple_calendar" data-days-of-the-week="<?php echo esc_attr( $days_of_the_week ); ?>" events="<?php echo esc_attr( $events ); ?>">
	<div class="ovaev_events_simple_calendar cal1"></div>
</div>
	
 