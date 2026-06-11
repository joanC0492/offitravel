<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get product id
$product_id = tripgo_get_meta_data( 'id', $args, get_the_id() );

// Get product
$product = wc_get_product( $product_id );
if ( !$product || !$product->is_type( 'ovabrw_car_rental' ) ) return;

// Slide options
$slide_options = tripgo_get_meta_data( 'data_options', $args );
if ( !tripgo_array_exists( $slide_options ) ) {
    $slide_options = apply_filters( 'tripgo_related_slide_options', [
        'slidesPerView'         => 4,
        'slidesPerGroup'        => 1,
        'spaceBetween'          => 24,
        'pauseOnMouseEnter'     => true,
        'loop'                  => false,
        'autoplay'              => false,
        'delay'                 => 3000,
        'speed'                 => 500,
        'dots'                  => true,
        'nav'                   => true,
        'breakpoints'           => [
            '0'     => [
                'slidesPerView' => 1
            ],
            '768'   => [
                'slidesPerView' => 2
            ],
            '1024'  => [
                'slidesPerView' => 3
            ],
            '1200'  => [
                'slidesPerView' => 4
            ]
        ],
        'rtl'                   => is_rtl() ? true: false
    ]);
}

// Query arguments
$args = [
    'posts_per_page'    => 5,
    'orderby'           => 'ID',
    'order'             => 'DESC'
];

// Get visible related products then sort them at random.
$args['related_products'] = array_filter( array_map( 'wc_get_product', wc_get_related_products( $product->get_id(), $args['posts_per_page'], $product->get_upsell_ids() ) ), 'wc_products_array_filter_visible' );

// Get related products
$related_products = wc_products_array_orderby( $args['related_products'], $args['orderby'], $args['order'] );

if ( tripgo_array_exists( $related_products ) ): ?>
    <div class="elementor-ralated-slide">
        <h3 class="related-title">
            <?php echo esc_html__( 'También te puede interesar', 'tripgo' ); ?>
        </h3>
        <div class="ova-product-slider elementor-ralated" data-options="<?php echo esc_attr( json_encode( $slide_options ) ); ?>">
            <div class="swiper swiper-loading">
                <div class="swiper-wrapper">
                    <?php foreach ( $related_products as $related_product ) {
                            $post_object = get_post( $related_product->get_id() );
                            setup_postdata( $GLOBALS['post'] =& $post_object );

                            ?><div class="swiper-slide"><?php
                                wc_get_template_part( 'content', 'product' );
                            ?></div><?php
                        }
                        
                        // Get post
                        $post_object = get_post( $product->get_id() );
                        setup_postdata( $GLOBALS['post'] =& $post_object );
                    ?>
                </div>
            </div>
            <?php if ( tripgo_get_meta_data( 'nav', $slide_options ) ): ?>
                <div class="swiper-nav">
                    <div class="button-nav button-prev">
                        <i class="icomoon icomoon-pre-small" aria-hidden="true"></i>
                    </div>
                    <div class="button-nav button-next">
                        <i class="icomoon icomoon-next-small" aria-hidden="true"></i>
                    </div>
                </div>
            <?php endif; ?>
            <?php if ( tripgo_get_meta_data( 'dots', $slide_options ) ): ?>
                <div class="button-dots"></div>
            <?php endif; ?>
        </div>
    </div>  
<?php endif; ?>