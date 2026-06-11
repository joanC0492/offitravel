<?php if ( !defined( 'ABSPATH' ) ) exit();

// Global product
global $product;

// Check product
if ( !$product || !$product->is_type( OVABRW_RENTAL ) ) return;

// Get product id
$product_id = $product->get_id();

// Get features icon
$features_icon = ovabrw_get_post_meta( $product_id, 'features_icons' );

// Get features label
$features_label = ovabrw_get_post_meta( $product_id, 'features_label' );

// Get description
$features_desc = ovabrw_get_post_meta( $product_id, 'features_desc' );

// Show in category
$show_in_cat = ovabrw_get_post_meta( $product_id, 'features_special' );

$d = 0;

if ( ovabrw_array_exists( $features_desc ) ): ?>
    <ul class="ovabrw-features">
        <?php foreach ( $features_desc as $key => $desc ):
            if ( 'yes' === ovabrw_get_meta_data( $key, $show_in_cat ) && $desc ):
                $class = ($d%2) ? 'eve' : 'odd';

                // Class icon
                $class_icon = ovabrw_get_meta_data( $key, $features_icon );

                // Label
                $label = ovabrw_get_meta_data( $key, $features_label );
            ?>
                <li class="feature-item <?php echo esc_attr( $class ); ?>">
                    <?php if ( apply_filters( OVABRW_PREFIX.'show_features_icon', true ) && $class_icon ): ?>
                        <i class="<?php echo esc_attr( $class_icon ); ?>"></i>
                    <?php endif;

                    // Label
                    if ( apply_filters( OVABRW_PREFIX.'show_features_label', false ) ): ?>    
                        <span class="label"><?php echo esc_html( $label ); ?></span>
                    <?php endif;

                    // Description
                    if ( apply_filters( OVABRW_PREFIX.'show_features_desc', true ) ): ?>    
                        <span class="desc"><?php echo esc_html( $desc ); ?></span>
                    <?php endif; ?>
                </li>
            <?php $d++; endif;
        endforeach; ?>
    </ul>
<?php endif;