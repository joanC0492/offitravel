<?php if ( !defined( 'ABSPATH' ) ) exit(); 

// Get type
$search_cat = isset( $_GET['ovaev_type'] ) ? $_GET['ovaev_type'] : '';
if ( is_tax( 'event_category' ) ||  get_query_var( 'event_category' ) != '' ) {
	$search_cat = get_query_var( 'event_category' );
}

// Get start date
$start_date_search = isset( $_GET['ovaev_start_date_search'] ) ? $_GET['ovaev_start_date_search'] : '';

// Get end date
$end_date_search = isset( $_GET['ovaev_end_date_search'] ) ? $_GET['ovaev_end_date_search'] : '';

// Get settings
$lang 			= OVAEV_Settings::archive_format_date_lang();
$date_format 	= OVAEV_Settings::archive_event_format_date();
$time_format 	= OVAEV_Settings::archive_event_format_time();
$first_day   	= apply_filters( 'ovaev_calendar_first_day' , get_option( 'start_of_week' ) );

?>
<div class="search_archive_event">
	<form action="<?php echo esc_url( get_post_type_archive_link( 'event' ) ); ?>" method="GET" name="search_event" autocomplete="off">
		<div class="start_date">
			<input
				type="text"
				id="ovaev_start_date_search"
				class="ovaev_start_date_search"
				name="ovaev_start_date_search"
				data-lang="<?php echo esc_attr( $lang ); ?>"
				data-date="<?php echo esc_attr( $date_format ); ?>"
				data-time="<?php echo esc_attr( $time_format ); ?>"
				data-first-day="<?php echo esc_attr( $first_day ); ?>"
				placeholder="<?php echo esc_attr__( 'Choose Date', 'ovaev' ); ?>"
				value="<?php echo esc_attr( $start_date_search ); ?>"
			/>
			<i class="far fa-calendar-alt"></i>
		</div>
		<div class="end_date">
			<input
				type="text"
				id="ovaev_end_date_search"
				class="ovaev_end_date_search"
				name="ovaev_end_date_search"
				data-lang="<?php echo esc_attr( $lang); ?>"
				data-date="<?php echo esc_attr( $date_format ); ?>"
				data-first-day="<?php echo esc_attr( $first_day ); ?>"
				placeholder="<?php echo esc_attr__( 'Choose Date', 'ovaev' ); ?>"
				value="<?php echo esc_attr( $end_date_search ); ?>"
			/>
			<i class="far fa-calendar-alt"></i>
		</div>
		<div class="ovaev_cat_search">
			<?php $dropdown_args1 = apply_filters( 'OVAEV_event_type', $search_cat ); ?>
			<i class="arrow_carrot-down "></i>
		</div>
		<div class="wrap-ovaev_submit">
			<input
				type="submit"
				class="second_font ovaev_submit"
				value="<?php esc_html_e( 'Find Event', 'ovaev' ); ?>"
			/>
		</div>
		<input type="hidden" name="post_type" value="event">
		<input type="hidden" name="search_event" value="search-event">
	</form>
</div>