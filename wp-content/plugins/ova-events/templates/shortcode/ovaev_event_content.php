<?php if ( !defined( 'ABSPATH' ) ) exit(); 

// Get event id
$id = isset( $args['id'] ) ? $args['id'] : '';
if ( !$id ) return;

// Get class
$class = isset( $args['class'] ) ? $args['class'] : '';

// Get event content
$content = apply_filters( 'the_content', get_post_field( 'post_content', $id ) );
if ( $content ): ?>
	<div class="ovaev-shortcode-content<?php echo ' '.esc_html( $class ); ?>">
		<?php echo wp_kses_post( $content ); ?>
	</div>
<?php endif; ?>