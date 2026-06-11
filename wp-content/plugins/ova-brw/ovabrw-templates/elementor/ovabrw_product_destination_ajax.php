<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get destinations
$destinations = ovabrw_get_meta_data( 'destinations', $args );
if ( 'yes' != ovabrw_get_meta_data( 'auto_sorted', $args ) ) {
	$destinations = ovabrw_get_meta_data( 'destination_ids', $args ) ? explode( '|', $args['destination_ids'] ) : '';
}

// Posts per page
$posts_per_page = ovabrw_get_meta_data( 'posts_per_page', $args, 9 );

// Order
$order = ovabrw_get_meta_data( 'order', $args, 'DESC' );

// Orderby
$orderby = ovabrw_get_meta_data( 'orderby', $args, 'date' );

// Layout
$layout = ovabrw_get_meta_data( 'layout', $args, 'grid' );

// Column
$column = ovabrw_get_meta_data( 'column', $args, 'column4' );

// Thumbnail type
$thumbnail_type = ovabrw_get_meta_data( 'thumbnail_type', $args, 'image' );

// Get pagination
$pagination = ovabrw_get_meta_data( 'pagination', $args, 'yes' );

?>

<div class="ovabrw-destination-ajax">
	<ul class="ovabrw-destination-list">
		<?php if ( ovabrw_array_exists( $destinations ) ): ?>
			<?php foreach ( $destinations as $k => $destination_id ):
				$title = get_the_title( $destination_id );
			?>
				<li class="destination-item <?php echo $k === 0 ? esc_attr( 'active' ) : ''; ?>" data-destination-id="<?php echo esc_attr( $destination_id ); ?>">
					<?php echo esc_html( $title ); ?>
				</li>
			<?php endforeach;
		else: ?>
			<li class="destination-item active" data-destination-id="0">
				<?php esc_html_e( 'All', 'ova-brw' ); ?>
			</li>
		<?php endif; ?>
	</ul>
	<div class="ovabrw-destination-products"></div>
	<!-- Load more -->
	<div class="wrap-load-more">
		<svg class="loader" width="50" height="50">
			<circle cx="25" cy="25" r="10" />
			<circle cx="25" cy="25" r="20" />
		</svg>
	</div>
	<input
		type="hidden"
		name="destination-ajax-input"
		data-posts-per-page="<?php echo esc_attr( $posts_per_page ); ?>"
		data-paged="1"
		data-order="<?php echo esc_attr( $order ); ?>"
		data-orderby="<?php echo esc_attr( $orderby ); ?>"
		data-layout="<?php echo esc_attr( $layout ); ?>"
		data-column="<?php echo esc_attr( $column ); ?>"
		data-thumbnail-type="<?php echo esc_attr( $thumbnail_type ); ?>"
		data-pagination="<?php echo esc_attr( $pagination ); ?>"
	/>
</div>