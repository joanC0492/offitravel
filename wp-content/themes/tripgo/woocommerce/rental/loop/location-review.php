<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get product id
$product_id = tripgo_get_meta_data( 'id', $args, get_the_id() );

// Get product
$product = wc_get_product( $product_id );
if ( !$product || !$product->is_type( 'ovabrw_car_rental' ) ) return;

// Get tour address
$address = tripgo_get_post_meta( $product_id, 'address' );

// Get short address
$short_address = tripgo_get_post_meta( $product_id, 'short_address' );
if ( $short_address ) $address = $short_address;

// Get review count
$review_count = $product->get_review_count();

// Get rating
$rating = $product->get_average_rating();

// Wishlist
$wishlist = do_shortcode('[yith_wcwl_add_to_wishlist]');

// Show location
$show_location = tripgo_get_meta_data( 'show_location', $args, 'yes' );

// Show rating
$show_rating = tripgo_get_meta_data( 'show_rating', $args, 'yes' );

// Show wishlist
$show_wishlist = tripgo_get_meta_data( 'show_wishlist', $args, 'yes' );

?>

<div class="ova-location-review">
    <?php if ( 'yes' === $show_location && $address ): ?>
        <div class="ova-product-location">
            <i aria-hidden="true" class="icomoon icomoon-location-2"></i>
            <a href="#ova-tour-map">
                <?php echo esc_html( $address ); ?>
            </a>
        </div>
    <?php endif;

    // Review
    if ( 'yes' === $show_rating && wc_review_ratings_enabled() && $rating > 0 ): ?>
        <div class="ova-product-review">
            <div class="star-rating" role="img" aria-label="<?php echo sprintf( __( 'Rated %s out of 5', 'tripgo' ), $rating ); ?>">
                <span class="rating-percent" style="width: <?php echo esc_attr( ( $rating / 5 ) * 100 ).'%'; ?>;"></span>
            </div>
            <a href="#reviews" class="woo-review-link" rel="nofollow">
                ( <?php echo sprintf( _n( '%s review', '%s reviews', $review_count, 'tripgo' ), esc_html( $review_count ) ); ?> )
            </a>
        </div>
    <?php endif;

    // Wishlist
    if ( '[yith_wcwl_add_to_wishlist]' != $wishlist && $show_wishlist == 'yes' ): ?>
        <div class="ova-single-product-wishlist">
            <?php echo $wishlist; ?>
        </div>
    <?php endif; ?>
</div>