<?php if ( !defined( 'ABSPATH' ) ) exit(); 

// Get event id
$id = isset( $args['id'] ) ? $args['id'] : '';
if ( !$id ) return;

// Get class
$class = isset( $args['class'] ) ? $args['class'] : '';

// Get icon class
$icon = isset( $args['icon'] ) ? $args['icon'] : '';

// Get event venue
$venue = get_post_meta( $id, 'ovaev_venue', true);
if ( $venue ): ?>
	<div class="ovaev-shortcode-location<?php echo ' '.esc_html( $class ); ?>">
		<i class="<?php echo esc_attr( $icon ); ?>"></i>
		<span class="second_font">
			<?php echo esc_html( $venue ); ?>
		</span>
	</div>
<?php endif; ?>