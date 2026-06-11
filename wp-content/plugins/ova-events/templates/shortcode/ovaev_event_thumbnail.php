<?php if ( !defined( 'ABSPATH' ) ) exit(); 

// Get event id
$id = isset( $args['id'] ) ? $args['id'] : '';
if ( !$id ) return;

// Get class
$class = $args['class'];

?>
<div class="ovaev-shortcode-thumbnail<?php echo ' '.esc_html( $class ); ?>">	
	<a href="<?php echo get_the_permalink( $id ); ?>">
		<?php echo get_the_post_thumbnail( $id, 'ovaev_event_thumbnail' ); ?>
	</a>
</div>