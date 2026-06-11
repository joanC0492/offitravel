<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get product id
$product_id = tripgo_get_meta_data( 'id', $args, get_the_id() );

// Get product
$product = wc_get_product( $product_id );
if ( !$product || !$product->is_type( 'ovabrw_car_rental' ) ) return;

// Get address
$address = tripgo_get_post_meta( $product_id, 'address' );

// Get map type
$map_type = tripgo_get_post_meta( $product_id, 'map_type' );
if ( !$map_type ) $map_type = 'api';
if ( 'iframe' === $map_type ) $address = true;

// Get latitude
$latitude = tripgo_get_post_meta( $product_id, 'latitude' );

// Get longitude
$longitude = tripgo_get_post_meta( $product_id, 'longitude' );

// Get map iframe
$map_iframe = tripgo_get_post_meta( $product_id, 'map_iframe' );

// Get zoom
$zoom = get_option( 'ova_brw_zoom_map_default', 17 );

// Show map
$show_map = tripgo_get_meta_data( 'show_map', $args, get_theme_mod( 'tour_single_show_map', 'yes' ) );

// Get product show map
$product_show_map = tripgo_get_post_meta( $product_id, 'show_map', 'global' );
if ( 'global' != $product_show_map ) {
    $show_map = $product_show_map;
}

if ( 'yes' === $show_map ):
    if ( 'api' === $map_type ):
        // Get api key
        $api_key = get_option( 'ova_brw_google_key_map', '' );

        if ( $address && $api_key ): ?>
            <div class="content-product-item tripgo-tour-map" id="ova-tour-map">
                <div class="heading-map">
                    <h2 class="title-tour-map">
                        <?php esc_html_e( 'Tour Map', 'tripgo' ); ?>
                    </h2>
                    <?php tripgo_text_input([
                        'type'  => 'hidden',
                        'class' => 'address',
                        'name'  => 'ovabrw-map-data',
                        'value' => $address,
                        'attrs' => [
                            'data-zoom'         => $zoom,
                            'data-latitude'     => $latitude,
                            'data-longitude'    => $longitude
                        ]
                    ]); ?>
                </div>
                <div id="tour-show-map" class="tour-show-map"></div>
            </div>
        <?php endif;
    elseif ( 'iframe' === $map_type ):
        if ( '' != $map_iframe ):
            // Allowed HTML
            $allowed_html = apply_filters( 'ovabrw_allowed_html', [
                'iframe' => [
                    'src'             => true,
                    'height'          => true,
                    'width'           => true,
                    'frameborder'     => true,
                    'allowfullscreen' => true
                ]
            ]);
        ?>
            <div class="content-product-item tripgo-tour-map" id="ova-tour-map">
                <div class="heading-map">
                    <h2 class="title-tour-map">
                        <?php esc_html_e( 'Tour Map', 'tripgo' ); ?>
                    </h2>
                </div>
                <?php echo wp_kses( $map_iframe, $allowed_html ); ?>
            </div>
        <?php endif;
    endif;
endif; ?>