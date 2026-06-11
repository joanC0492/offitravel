<?php if ( !defined( 'ABSPATH' ) ) exit(); 

// Get event id
if ( isset( $args['id'] ) ) {
	$id = $args['id'];
} else {
	$id = get_the_id();	
}
if ( !$id ) return;

// Get event venue
$venue = get_post_meta( $id, 'ovaev_venue', true ); 
if ( $venue ): ?>
	<div class="venue">
		<i class="icomoon icomoon-location"></i>
		<span class="number">
			<?php echo esc_html( $venue ); ?>
		</span>
	</div>
<?php endif;