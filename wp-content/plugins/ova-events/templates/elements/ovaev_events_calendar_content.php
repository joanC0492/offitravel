<?php
if ( ! defined( 'ABSPATH' ) ) exit();

if( isset( $args['id'] ) && $args['id'] ){
	$pid = $args['id'];
}else{
	$pid = get_the_id();
}

$show_filter 	= $args['show_filter'] ? $args['show_filter'] : 'no';
$filter_event 	= $args['filter_event'] ? $args['filter_event'] : 'all';
$category 		= $args['category'];
$lang 			= OVAEV_Settings::archive_format_date_lang();

$button_text = apply_filters( 'ovaev_calendar_button_text', array( 
	"today" => "today",
  	"month" => "month",
  	"week" 	=> "week",
  	"day" 	=> "day",
  	"list" 	=> "list"
 ));

$all_day_text 	= apply_filters( 'ovaev_calendar_all_day_text', esc_html__('all-day', 'ovaev' ) );
$no_events_text = apply_filters( 'ovaev_calendar_no_events_text', esc_html__('No events to display', 'ovaev' ) );
$first_day 		= apply_filters( 'ovaev_calendar_first_day' , get_option( 'start_of_week' ) );

$events   		= OVAEV_Get_Data::get_events_calendar( $category, $filter_event );

?>

<div class="ovaev_fullcalendar" 
	 full_events="<?php echo esc_attr( $events ); ?>" 
	 data-lang="<?php echo esc_attr( $lang ); ?>" 
	 data-button-text="<?php echo esc_attr( json_encode( $button_text ) ); ?>" 
	 data-no-events-text="<?php echo esc_attr( $no_events_text ); ?>" 
	 data-all-day-text="<?php echo esc_attr( $all_day_text ); ?>"
	 data-first-day="<?php echo esc_attr( $first_day ); ?>">
	<?php if ( $show_filter == 'yes' ): ?>
		<div class="calendar_filter_event">
		  <label for="calendar_filter_event"><?php echo esc_html__( 'Filter Event', 'ovaev' ); ?></label>
		  <select class="form-control" id="calendar_filter_event">
		      <option value="all"><?php echo esc_html__( 'All', 'ovaev' ); ?></option>
		      <option value="past_event"><?php echo esc_html__( 'Past Event', 'ovaev' ); ?></option>
		      <option value="upcoming_event"><?php echo esc_html__( 'Upcoming Event', 'ovaev' ); ?></option>
		      <option value="special_event"><?php echo esc_html__( 'Special Event', 'ovaev' ); ?></option>
		    </select>
		</div>
	<?php endif; ?>
	<div class="ovaev_events_fullcalendar"></div>
</div>
