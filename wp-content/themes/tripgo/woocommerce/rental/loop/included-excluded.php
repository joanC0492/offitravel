<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get product id
$product_id = tripgo_get_meta_data( 'id', $args, get_the_id() );

// Get product
$product = wc_get_product( $product_id );
if ( !$product || !$product->is_type( 'ovabrw_car_rental' ) ) return;

// Show include & exclude
$show_inc_exc = tripgo_get_meta_data( 'show_inc_exc', $args, 'yes' );

// Included
$included = tripgo_get_post_meta( $product_id, 'group_tour_included' );

// Excluded
$excluded = tripgo_get_post_meta( $product_id, 'group_tour_excluded' );

if ( 'yes' === $show_inc_exc && ( tripgo_array_exists( $included ) || tripgo_array_exists( $excluded ) ) ): ?>
    <div class="content-product-item tour-included-excluded-wrapper" id="tour-included-excluded">
        <h2 class="heading-tour-included-excluded">
            <?php echo esc_html__('Incluido/Excluido', 'tripgo'); ?>
        </h2>
        <div class="tour-included-excluded-content">
            <?php if ( tripgo_array_exists( $included ) ): ?>
                <ul class="tour-included">
                    <?php foreach ( $included as $incl ): ?>
                        <li class="item-tour-included">
                            <?php echo esc_html( tripgo_get_meta_data( 'ovabrw_tour_included_text', $incl ) ); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>  
            <?php endif;

            // Excluded
            if ( tripgo_array_exists( $excluded ) ): ?>
                <ul class="tour-excluded">
                    <?php foreach ( $excluded as $excl ): ?>
                        <li class="item-tour-excluded">
                            <?php echo esc_html( tripgo_get_meta_data( 'ovabrw_tour_excluded_text', $excl ) ); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>  
            <?php endif; ?>                
        </div>
    </div>
<?php endif; ?>