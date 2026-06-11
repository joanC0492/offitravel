<?php if ( !defined( 'ABSPATH' ) ) exit(); 

// Get event id
if ( isset( $args['id'] ) ) {
	$id = $args['id'];
} else {
	$id = get_the_id();	
}
if ( !$id ) return;

// Get thumbnail url
$thumbnail_url = get_the_post_thumbnail_url( $id, 'post-thumbnail' );

?>
<div class="event-thumbnail" style="background-image: url(<?php echo esc_url( $thumbnail_url ); ?>);">
	<a href="<?php echo the_permalink();?>">
		<?php echo get_the_post_thumbnail( $id, 'ovaev_event_thumbnail' ); ?>
	</a>
</div>