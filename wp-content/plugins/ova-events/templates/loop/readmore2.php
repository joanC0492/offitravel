<?php if ( !defined( 'ABSPATH' ) ) exit(); 

// Get event id
if ( isset( $args['id'] ) ) {
	$id = $args['id'];
} else {
	$id = get_the_id();	
}
if ( !$id ) return;

?>
<div class="button_event">
	<a class="view_detail second_font" href="<?php echo esc_url( get_the_permalink( $id ) ); ?>">
		<?php esc_html_e( 'Event details', 'ovaev' ); ?>
	</a>
</div>