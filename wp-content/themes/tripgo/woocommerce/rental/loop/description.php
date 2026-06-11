<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get product id
$product_id = tripgo_get_meta_data( 'id', $args, get_the_id() );

// Get product
$product = wc_get_product( $product_id );
if ( !$product || !$product->is_type( 'ovabrw_car_rental' ) ) return;

// Get elementor preview
$el_preview = tripgo_get_meta_data( 'elementor-preview', $_GET );

// Get tour description
$tour_description = apply_filters( 'woocommerce_short_description', get_post( $product_id )->post_content );
if ( $tour_description ): ?>
    <div class="content-product-item tour-description" id="tour-description">
        <?php if ( \Elementor\Plugin::instance()->documents->get( $product_id )->is_built_with_elementor() || ( $el_preview && wc_get_product( $el_preview ) ) ) {
            the_content();
        } else {
            echo wp_kses_post( $tour_description ); // WPCS: XSS ok.
        } ?>
    </div>
<?php endif; ?>
