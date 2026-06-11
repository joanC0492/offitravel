<?php if ( !defined( 'ABSPATH' ) ) exit(); 

// Get event id
$id = isset( $args['id'] ) ? $args['id'] : '';
if ( !$id ) return;

// Get class
$class = isset( $args['class'] ) ? $args['class'] : '';

// Get icon class
$icon = isset( $args['icon'] ) ? $args['icon'] : '';

// Get time format
$time_format = OVAEV_Settings::archive_event_format_time();

// Get event start time
$post_start_time = get_post_meta( $id, 'ovaev_start_time', true );

// Get event end time
$post_end_time = get_post_meta( $id, 'ovaev_end_time', true );

// Convert start time
$start_time = strtotime( $post_start_time ) ? date( $time_format, strtotime( $post_start_time ) ) : '';

// Convert end time
$end_time = strtotime( $post_end_time ) ? date( $time_format, strtotime( $post_end_time ) ) : '';

if ( $start_time && $end_time ): ?>
	<div class="ovaev-shortcode-time<?php echo ' '.esc_html( $class ); ?>">
		<i class="<?php echo esc_attr( $icon ); ?>"></i>
		<span class="second_font"><?php echo esc_html( $start_time ); ?></span>
		<span class="second_font"><?php echo esc_html_e( ' - ', 'ovaev' ); ?></span>
		<span class="second_font"><?php echo esc_html( $end_time ); ?></span>
	</div>
<?php endif;