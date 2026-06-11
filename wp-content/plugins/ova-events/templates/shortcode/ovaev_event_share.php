<?php if ( !defined( 'ABSPATH' ) ) exit(); 

// Get event id
$id = isset( $args['id'] ) ? $args['id'] : '';
if ( !$id ) return;

// Get class
$class = isset( $args['class'] ) ? $args['class'] : '';

?>
<div class="ovaev-shortcode-share<?php echo ' '.esc_html( $class ); ?>">
	<?php echo apply_filters( 'ovaev_share_social', get_the_permalink( $id ), get_the_title( $id ) ); ?>
</div>