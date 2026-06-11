<?php if ( !defined( 'ABSPATH' ) ) exit(); 

// Get event id
if ( isset( $args['id'] ) ) {
	$id = $args['id'];
} else {
	$id = get_the_id();	
}
if ( !$id ) return;

// Get excerpt
$excerpt = get_the_excerpt( $id );
$excerpt = wp_trim_words( $excerpt, 10 );
if ( $excerpt ): ?>
	<p class="event-excerpt">
		<?php echo esc_html( $excerpt ); ?>
	</p>
<?php endif;