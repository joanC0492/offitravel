<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get product id
$product_id = tripgo_get_meta_data( 'id', $args, get_the_id() );

// Get product
$product = wc_get_product( $product_id );
if ( !$product || !$product->is_type( 'ovabrw_car_rental' ) ) return;

// ReviewX shortcode
$rvx_summary        =  '[rvx-summary product_id='.$product_id.']';
$rvx_list           =  '[rvx-review-list product_id='.$product_id.']'; 
$rvx_woo_reviews    =  '[rvx-woo-reviews product_id='.$product_id.']';

// Product review
$product_review = [
    'title'    => sprintf( __( 'Reviews (%d)', 'tripgo' ), $product->get_review_count() ),
    'priority' => 30,
    'callback' => 'comments_template'
];

// Show reviews
$show_reviews = tripgo_get_meta_data( 'show_reviews', $args, 'yes' );

if ( 'yes' === $show_reviews ): ?>
    <?php if ( is_singular( 'product' ) ): ?>
        <div class="content-product-item ova-tour-review" id="ova-tour-review">
            <?php call_user_func( $product_review['callback'], 'reviews', $product_review ); ?>
        </div>
    <?php else: ?>
        <div class="content-product-item ova-tour-review" id="ova-tour-review">
            <h4>
                <?php echo esc_html__( 'Reviews are only visible in a single product page', 'tripgo' ); ?>
            </h4>
        </div>
    <?php endif;
endif; ?>