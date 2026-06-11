<?php if ( !defined( 'ABSPATH' ) ) exit(); 

// Get event id
if ( isset( $args['id'] ) ) {
	$id = $args['id'];
} else {
	$id = get_the_id();	
}
if ( !$id ) return;

?>
<div class="event-readmore">
	<a href="<?php echo esc_url( get_the_permalink( $id ) ); ?>" class="readmore second_font">
		<?php echo esc_html_e( 'Event details', 'ovaev' ); ?>
		<i aria-hidden="true" class="icomoon icomoon-arrow-right"></i>
	</a>
</div>