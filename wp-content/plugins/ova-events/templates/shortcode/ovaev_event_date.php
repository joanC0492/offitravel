<?php if ( !defined( 'ABSPATH' ) ) exit(); 

// Get event id
$id = isset( $args['id'] ) ? $args['id'] : '';
if ( !$id ) return;

// Get class
$class = isset( $args['class'] ) ? $args['class'] : '';

// Get class icon
$icon = isset( $args['icon'] ) ? $args['icon'] : '';

// Get event start date
$post_start_date = get_post_meta( $id, 'ovaev_start_date_time', true );

// Get event end date
$post_end_date = get_post_meta( $id, 'ovaev_end_date_time', true );

// Convert start date
$start_date = '' != $post_start_date ? date_i18n( get_option( 'date_format' ), $post_start_date ) : '';

// Convert end date
$end_date = '' != $post_end_date ? date_i18n( get_option( 'date_format' ), $post_end_date ) : '';

if ( $start_date && $end_date ): ?>
	<div class="ovaev-shortcode-date<?php echo ' '.esc_html( $class ); ?>">
		<?php if ( $start_date == $end_date && $start_date != '' && $end_date != '' ): ?>
			<i class="<?php echo esc_attr( $icon ); ?>"></i>
			<span class="second_font"><?php echo esc_html( $start_date ); ?></span>
		<?php elseif ( $start_date != $end_date && $start_date != '' && $end_date != '' ): ?>
			<i class="<?php echo esc_html( $icon ); ?>"></i>
			<span class="second_font"><?php echo esc_html( $start_date ); ?></span>
			<span class="second_font"><?php echo esc_html_e( ' - ', 'ovaev' ); ?></span>
			<span class="second_font"><?php echo esc_html( $end_date ); ?></span>
		<?php endif; ?>
	</div>
<?php endif; ?>