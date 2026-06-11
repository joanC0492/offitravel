<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get product id
$product_id = tripgo_get_meta_data( 'id', $args, get_the_id() );

// Get product
$product = wc_get_product( $product_id );
if ( !$product || !$product->is_type( 'ovabrw_car_rental' ) ) return;

// Show features
$show_features = tripgo_get_meta_data( 'show_features', $args, 'yes' );

// Features icons
$features_icons = tripgo_get_post_meta( $product_id, 'features_icons' );

if ( 'yes' === $show_features && tripgo_array_exists( $features_icons ) ):
    // Get features title
    $features_label = tripgo_get_post_meta( $product_id, 'features_label' );

    // Get features description
    $features_desc = tripgo_get_post_meta( $product_id, 'features_desc' );
?>
    <div class="ova-features-product">
        <?php foreach ( $features_icons as $i => $icon ):
            if ( !$icon ) continue;

            // Label
            $label = tripgo_get_meta_data( $i, $features_label );

            // Description
            $desc = tripgo_get_meta_data( $i, $features_desc );
        ?>
            <div class="feature">
                <i aria-hidden="true" class="<?php echo esc_attr( $icon ); ?>"></i>
                <div class="title-desc">
                    <?php if ( $label ): ?>
                        <h6 class="title">
                            <?php echo esc_html( $label ); ?>
                        </h6>
                    <?php endif;

                    // Description
                    if ( $desc ): ?>
                        <p class="desc">
                            <?php echo esc_html( $desc ); ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>