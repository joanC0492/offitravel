<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get product id
$product_id = tripgo_get_meta_data( 'id', $args, get_the_id() );

// Get product
$product = wc_get_product( $product_id );
if ( !$product || !$product->is_type( 'ovabrw_car_rental' ) ) return;

if ( 'yes' === tripgo_get_meta_data( 'show_title', $args, 'yes' ) ): ?>
    <h1 class="ova-product-title">
        <?php echo get_the_title( $product_id ); ?>
    </h1>
<?php endif; ?>