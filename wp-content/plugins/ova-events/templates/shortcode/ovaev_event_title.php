<?php if ( !defined( 'ABSPATH' ) ) exit(); 

// Get event id
$id = isset( $args['id'] ) ? $args['id'] : '';
if ( !$id ) return;

// Get class
$class = isset( $args['class'] ) ? $args['class'] : '';

// Get title
$title = get_the_title( $id );
if ( $title ): ?>
	<h2 class="second_font ovaev-shortcode-title<?php echo ' '.esc_html( $class ); ?>">
		<a href="<?php echo get_the_permalink( $id ); ?>">
			<?php echo esc_html( $title ); ?>
		</a>
	</h2>
<?php endif;