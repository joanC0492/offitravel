<?php if ( !defined( 'ABSPATH' ) ) exit(); 

// Get event id
$id = isset( $args['id'] ) ? $args['id'] : '';

// Get class
$class = $args['class'];

// Get event tags
$tags = get_the_terms( $id, 'event_tag' );
if ( $tags ): ?>
	<div class="ovaev-shortcode-tags<?php echo ' '.esc_html( $class ); ?>">
		<?php ovaev_get_tag_event_by_id( $id ); ?>
	</div>
<?php endif;