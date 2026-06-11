<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get product id
$product_id = tripgo_get_meta_data( 'id', $args, get_the_id() );

// Get product
$product = wc_get_product( $product_id );
if ( !$product || !$product->is_type( 'ovabrw_car_rental' ) ) return;

// Get product enquiry shortcode
$enquiry_shortcode = get_post_meta( $product_id, 'ovabrw_enquiry_shortcode', true );
if ( !$enquiry_shortcode ) {
	$enquiry_shortcode = get_option( 'ovabrw_enquiry_shortcode' );
}

?>

<div id="enquiry-form" class="ovabrw-product-form ova-enquiry-form" data-product-id="<?php echo esc_attr( $product_id ); ?>">
	<?php echo do_shortcode( $enquiry_shortcode ); ?>
</div>