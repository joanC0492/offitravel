<?php if ( !defined( 'ABSPATH' ) ) exit();

// Global post
global $post;

// Get settings
$lang 			= OVAEV_Settings::archive_format_date_lang();
$date_format 	= OVAEV_Settings::archive_event_format_date();
$time 			= OVAEV_Settings::archive_event_format_time();
$first_day 		= apply_filters( 'ovaev_calendar_first_day' , get_option( 'start_of_week' ) );

// Get date time
$ovaev_start_date 		= get_post_meta( $post->ID, 'ovaev_start_date', true );
$ovaev_end_date   		= get_post_meta( $post->ID, 'ovaev_end_date', true );
$ovaev_start_time 		= get_post_meta( $post->ID, 'ovaev_start_time', true );
$ovaev_end_time   		= get_post_meta( $post->ID, 'ovaev_end_time', true );
$ovaev_start_date_time 	= get_post_meta( $post->ID, 'ovaev_start_date_time', true );
$ovaev_end_date_time   	= get_post_meta( $post->ID, 'ovaev_end_date_time', true );

// Get venue
$ovaev_venue = get_post_meta( $post->ID, 'ovaev_venue', true );

// is special
$checked = get_post_meta( $post->ID, 'ovaev_special', true ) ? get_post_meta( $post->ID, 'ovaev_special', true ) : '' ;

// Custom sort
$event_custom_sort = get_post_meta( $post->ID, 'event_custom_sort', true ) ? get_post_meta( $post->ID, 'event_custom_sort', true ) : '1' ;

// Booking link
$booking_links = get_post_meta( $post->ID, 'ovaev_booking_links', true ) ? get_post_meta( $post->ID, 'ovaev_booking_links', true ) : '' ;

// Get current event template
$event_template = get_post_meta( $post->ID, 'event_template', true ) ? get_post_meta( $post->ID, 'event_template', true ) : 'global' ;

// Get templates
$templates = get_posts([
	'post_type' 	=> 'elementor_library',
	'meta_key' 		=> '_elementor_template_type',
	'meta_value' 	=> 'page'
]);

?>
<div class="ovaev_metabox">
	<br>
	<div class="ovaev_row">
		<label class="label">
			<strong>
				<?php esc_html_e( 'Start date:', 'ovaev' ); ?>
			</strong>
		</label>
		<input
			type="text"
			id="ovaev_start_date"
			class="ovaev_start_date"
			name="ovaev_start_date"
			value="<?php echo esc_attr( $ovaev_start_date ); ?>"
			placeholder="<?php echo esc_attr( $date_format ); ?>"
			data-date="<?php echo esc_attr( $date_format ); ?>"
			data-lang="<?php echo esc_attr( $lang ); ?>"
			data-first-day="<?php echo esc_attr( $first_day ); ?>"
			autocomplete="off"
		/>
	</div>
	<br>
	<div class="ovaev_row">
		<label class="label">
			<strong>
				<?php esc_html_e( 'Start Time:', 'ovaev' ); ?>
			</strong>
		</label>
		<input
			type="text"
			id="ovaev_start_time"
			class="ovaev_time_picker"
			name="ovaev_start_time"
			value="<?php echo esc_attr( $ovaev_start_time ); ?>"
			placeholder="<?php echo esc_attr( $time ); ?>"
			data-time="<?php echo esc_attr( $time ); ?>"
			autocomplete="off"
		/>
	</div>
	<br>
	<div class="ovaev_row">
		<label class="label">
			<strong>
				<?php esc_html_e( 'End date:', 'ovaev' ); ?>
			</strong>
		</label>
		<input
			type="text"
			id="ovaev_end_date"
			class="ovaev_end_date"
			name="ovaev_end_date"
			value="<?php echo esc_attr( $ovaev_end_date ); ?>"
			placeholder="<?php echo esc_attr( $date_format ); ?>"
			data-date="<?php echo esc_attr( $date_format ); ?>"
			data-lang="<?php echo esc_attr( $lang ); ?>"
			data-first-day="<?php echo esc_attr( $first_day ); ?>"
			autocomplete="off"
		/>
	</div>
	<br>
	<div class="ovaev_row">
		<label class="label">
			<strong>
				<?php esc_html_e( 'End Time:', 'ovaev' ); ?>
			</strong>
		</label>
		<input
			type="text"
			id="ovaev_end_time"
			class="ovaev_time_picker"
			name="ovaev_end_time"
			value="<?php echo esc_attr( $ovaev_end_time ); ?>"
			placeholder="<?php echo esc_attr( $time ); ?>"
			data-time="<?php echo esc_attr( $time ); ?>"
			autocomplete="off"
		/>
	</div>
	<br>
	<div class="ovaev_row">
		<label class="label">
			<strong>
				<?php esc_html_e( 'Location:', 'ovaev' ); ?>
			</strong>
		</label>
		<input
			type="text"
			id="ovaev_venue"
			name="ovaev_venue"
			value="<?php echo esc_attr( $ovaev_venue ); ?>"
			placeholder="<?php esc_html_e( 'No. 1, Broadway, New York', 'ovaev' ); ?>"
			autocomplete="off"
		/>
	</div>
	<br>
	<div class="ovaev_row">
		<label class="label">
			<strong>
				<?php esc_html_e( 'Special Event:', 'ovaev' ); ?>
			</strong>
		</label>
		<input
			type="checkbox"
			value="<?php echo esc_attr( $checked ); ?>"
			name="ovaev_special"
			<?php echo esc_attr( $checked ); ?>
		/>
	</div>
	<br>
	<div class="ovaev_row">
		<label class="label">
			<strong>
				<?php esc_html_e( 'Custom Sort:', 'ovaev' ); ?>
			</strong>
		</label>
		<input
			type="number"
			name="event_custom_sort"
			value="<?php echo esc_attr( $event_custom_sort ); ?>"
		/>
	</div>
	<br>
	<div class="ovaev_row">
		<label class="label">
			<strong>
				<?php esc_html_e( 'Booking Links:', 'ovaev' ); ?>
			</strong>
		</label>
		<input
			type="text"
			id="ovaev_booking_links"
			name="ovaev_booking_links"
			value="<?php echo esc_attr( $booking_links ); ?>"
			placeholder="<?php esc_html_e( '#', 'ovaev' ); ?>"
			autocomplete="off"
		/>
	</div>
	<br>
	<div class="ovaev_row">
		<label class="label">
			<strong>
				<?php esc_html_e( 'Templates:', 'ovaev' ); ?>
			</strong>
		</label>
		<select name="event_template" id="ovaev_event_templates">
			<option value="global"<?php selected( 'global', $event_template ); ?>>
				<?php echo esc_html__('Global', 'ovaev') ?>
			</option>
			<?php if ( !empty( $templates ) && is_array( $templates ) ):
				foreach ( $templates as $template ):
					$id 	= $template->ID;
					$title 	= $template->post_title;
				?>
					<option value="<?php echo esc_attr( $id ); ?>"<?php selected( $id, $event_template ); ?>>
						<?php echo esc_html( $title ); ?>
					</option>
				<?php  endforeach;
			endif; ?>
		</select>
	</div>
</div>
<?php wp_nonce_field( 'ovaev_nonce', 'ovaev_nonce' ); ?>