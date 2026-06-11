<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get id
$id = get_the_id();

// Get data
$template3_style 	= isset( $args['template3_style'] ) ? $args['template3_style'] : 'default' ;
$show_thumbnail 	= isset( $args['show_thumbnail'] ) ? $args['show_thumbnail'] : 'yes' ;
$show_title   		= isset( $args['show_title'] ) ? $args['show_title'] : 'yes' ;
$show_rating  		= isset( $args['show_rating'] ) ? $args['show_rating'] : 'yes' ;
$show_count   		= isset( $args['show_count'] ) ? $args['show_count'] : 'yes' ;
$show_link_to 		= isset( $args['show_link_to_detail'] ) ? $args['show_link_to_detail'] : 'yes' ;

// get size image
$thumbnail_square = wp_get_attachment_image_url( get_post_thumbnail_id( $id ), 'ova_destination_square' ); 
if ( !$thumbnail_square || $show_thumbnail != 'yes' ) {
	$thumbnail_square = \Elementor\Utils::get_placeholder_image_src();
}

if ( isset( $args['flag'] ) ) {
	$flag = $args['flag'];
} else {
	$flag = 'slider';
}

$args_query = [
	'post_type' 		=> 'product',
    'post_status' 		=> 'publish',
    'posts_per_page' 	=> -1,
    'meta_key' 			=> 'ovabrw_destination',
    'meta_value' 		=> $id,
    'meta_compare' 		=> 'LIKE',
    'fields' 			=> 'ids'
];

$query_posts  	= get_posts( $args_query );
$count      	= count( $query_posts );

// Get destination rating from product rating
$total_rating = $average_rating = $r_count = 0;

// Get product from destination id
$product_ids = ovadestination_get_product_ids_by_id($id);
if ( !empty( $product_ids ) && is_array( $product_ids ) ) {
	foreach ( $product_ids as $product_id ) {
		$product = wc_get_product( $product_id );
		$rating  = $product->get_average_rating();
		if ( $rating != 0 ) {
			$r_count += 1;
		}
		$total_rating += $rating;
	}
}

if ( $r_count > 0) {
	$average_rating = $total_rating/$r_count;
}

if ( $show_link_to == 'yes' ): ?>
    <a href="<?php the_permalink(); ?>">
<?php endif; ?>	
	<div class="item-destination item-destination-template3 template3-<?php echo esc_attr( $template3_style ); ?> item-destination-<?php echo esc_attr( $flag ); ?>">
		<div class="item-wrapper">
			<div class="img">
		    	<img src="<?php echo esc_url( $thumbnail_square ); ?>" class="destination-img" alt="<?php the_title(); ?>">
				<div class="mask"></div>
				<?php if ( $show_count == 'yes' ): ?>
					<div class="count-tour">
					    <span class="number">
					    	<?php echo esc_html($count); ?>		    	
					    </span> 
					    <?php if ( $count != 1 ):
					    	esc_html_e(' Tours', 'ova-destination' );
					    else:
					    	esc_html_e(' Tour', 'ova-destination' );
					    endif; ?>
					</div>	
				<?php endif; ?>
			</div>
			<div class="info">	
				<?php if ( $show_title == 'yes' ): ?>
					<h3 class="name">
						<?php the_title(); ?>
					</h3>
				<?php endif; ?>
				<?php if ( $show_rating == 'yes' ): ?>
					<div class="rating">
						<i aria-hidden="true" class="fas fa-star"></i>
						<?php if ( $average_rating && $average_rating != 0 ): ?>
						    <span class="average_rating">
						    	<?php echo sprintf( '%.1f', $average_rating ); ?>		    	
						    </span> 
						<?php else:
							echo esc_html( $average_rating ) . ' ' . esc_html__( 'Reviews', 'ova-destination' ) ;
						endif; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
<?php if ( $show_link_to == 'yes' ): ?>
    </a>
<?php endif; ?>	