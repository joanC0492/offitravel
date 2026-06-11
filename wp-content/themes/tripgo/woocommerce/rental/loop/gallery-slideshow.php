<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get product id
$product_id = tripgo_get_meta_data( 'id', $args, get_the_id() );

// Get product
$product = wc_get_product( $product_id );
if ( !$product || !$product->is_type( 'ovabrw_car_rental' ) ) return;

// Show gallery
$show_gallery = tripgo_get_meta_data( 'show_gallery', $args, 'yes' );

// Get gallery ids
$gallery_ids = tripgo_get_gallery_ids( $product_id );

// Data gallery
$data_gallery = [];

// Slide options
$slide_options = tripgo_get_meta_data( 'data_options', $args );
if ( !tripgo_array_exists( $slide_options ) ) {
    $slide_options = apply_filters( 'tripgo_gallery_slide_options', [
        'slidesPerView'         => 3,
        'slidesPerGroup'        => 1,
        'spaceBetween'          => 24,
        'pauseOnMouseEnter'     => true,
        'loop'                  => true,
        'autoplay'              => true,
        'delay'                 => 3000,
        'speed'                 => 500,
        'dots'                  => false,
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
            ]
        ],
        'rtl'                   => is_rtl() ? true: false
    ]);
} // END

// Thumbnail size
$thumbnail_size = tripgo_get_meta_data( 'thumbnail_size', $args, 'tripgo_product_slider' );

// Gallery size
$gallery_size = apply_filters( 'tripgo_product_gallery_size', $thumbnail_size );

if ( 'yes' === $show_gallery && tripgo_array_exists( $gallery_ids ) ): ?>
    <div class="ova-gallery-popup">
        <div class="ova-gallery-slideshow" data-options="<?php echo esc_attr( json_encode( $slide_options ) ); ?>">
            <div class="swiper swiper-loading">
                <div class="swiper-wrapper">
                    <?php foreach ( $gallery_ids as $i => $img_id ):
                        // Get image URL
                        $img_url = wp_get_attachment_url( $img_id );

                        // Get image alt
                        $img_alt = get_post_meta( $img_id, '_wp_attachment_image_alt', true );
                        if ( !$img_alt ) $img_alt = get_the_title( $img_id );

                        // Add data gallery
                        array_push( $data_gallery, [
                            'src'       => $img_url,
                            'caption'   => $img_alt,
                            'thumb'     => $img_url
                        ]);
                    ?>
                        <div class="item swiper-slide">
                            <a class="gallery-fancybox" data-index="<?php echo esc_attr( $i ); ?>" href="javascript:void(0)">
                                <?php echo wp_get_attachment_image( $img_id, $gallery_size ); ?>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php if ( tripgo_get_meta_data( 'nav', $slide_options ) ): ?>
                <div class="button-nav button-prev">
                    <i class="arrow_carrot-left" aria-hidden="true"></i>
                </div>
                <div class="button-nav button-next">
                    <i class="arrow_carrot-right" aria-hidden="true"></i>
                </div>
            <?php endif; ?>
            <?php if ( tripgo_get_meta_data( 'dots', $slide_options ) ): ?>
                <div class="button-dots"></div>
            <?php endif; ?>
        </div>
        <?php tripgo_text_input([
            'type'  => 'hidden',
            'class' => 'ova-data-gallery',
            'attrs' => [
                'data-gallery' => json_encode( $data_gallery )
            ]
        ]); ?>
    </div>
<?php endif; ?>