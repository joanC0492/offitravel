<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get product id
$product_id = tripgo_get_meta_data( 'id', $args, get_the_id() );

// Get product
$product = wc_get_product( $product_id );
if ( !$product || !$product->is_type( 'ovabrw_car_rental' ) ) return;

// Show video
$show_video = tripgo_get_meta_data( 'show_video', $args, 'yes' );

// Get embed URL
$embed_url = tripgo_get_post_meta( $product_id, 'embed_video' );

// Video controls
$controls = apply_filters( 'tripgo_video_controls', [
    'autoplay'  => 'yes',
    'mute'      => 'no',
    'loop'      => 'yes',
    'controls'  => 'yes',
    'modest'    => 'yes',
    'rel'       => 'yes'
]);

// Show gallery
$show_gallery = tripgo_get_meta_data( 'show_gallery', $args, 'yes' );

// Gallery data
$gallery_data = [];

// Get gallery ids
$gallery_ids = tripgo_get_gallery_ids( $product_id );
if ( tripgo_array_exists( $gallery_ids ) ) {
    foreach ( $gallery_ids as $img_id ) {
        // Image URL
        $image_url = wp_get_attachment_image_url( $img_id, 'tripgo_product_gallery' );

        // Image caption
        $image_caption = wp_get_attachment_caption( $img_id );
        if ( !$image_caption ) {
            $image_caption = get_post_meta( $img_id, '_wp_attachment_image_alt', true );
        }
        if ( !$image_caption ) {
            $image_caption = get_the_title( $img_id );
        }

        array_push( $gallery_data, [
            'src'       => $image_url,
            'caption'   => $image_caption,
            'thumb'     => $image_url,
            'type'      => 'image'
        ]);
    }
}

// Show share social
$show_share = tripgo_get_meta_data( 'show_share', $args, 'yes' );

// Get product URL
$product_url = get_permalink( $product_id );

// Get product title
$product_title = get_the_title( $product_id );

// Social
$args_social = apply_filters( 'ovabrw_ft_share_social', [
    'facebook' => [
        'icon'  => 'flaticon flaticon-facebook',
        'url'   => 'https://www.facebook.com/sharer.php?u='.esc_url( $product_url )
    ],
    'twitter' => [
        'icon'  => 'flaticon flaticon-twitter',
        'url'   => 'https://twitter.com/share/?url='.esc_url( $product_url ).'&text='.esc_attr( $product_title )
    ],
    'whatsapp' => [
        'icon'  => 'flaticon flaticon-whatsapp-1',
        'url'   => 'https://api.whatsapp.com/send?text=*'.esc_attr( $product_title ).'*%0A'.esc_url( $product_url )
    ],
    'pinterest' => [
        'icon'  => 'flaticon flaticon-pinterest',
        'url'   => 'https://www.pinterest.com/pin/create/button/?url='.esc_url( $product_url )
    ]
], $product_url, $product_title );

?>

<div class="ova-video-gallery">
    <?php if ( 'yes' === $show_video && $embed_url ): ?>
        <div class="btn-header btn-video btn-video-gallery" 
            data-src="<?php echo esc_url( $embed_url ); ?>" 
            data-controls="<?php echo esc_attr( json_encode( $controls ) ); ?>">
            <i aria-hidden="true" class="icomoon icomoon-caret-circle-right"></i>
            <?php esc_html_e( 'View video', 'tripgo' ); ?>
        </div>
        <div class="video-container">
            <div class="modal-container">
                <div class="modal">
                    <i class="ovaicon-cancel"></i>
                    <iframe class="modal-video" allow="autoplay" allowfullscreen></iframe>
                </div>
            </div>
        </div>
    <?php endif;

    // Show gallery
    if ( 'yes' === $show_gallery && tripgo_array_exists( $gallery_data ) ): ?>
        <div class="btn-header btn-gallery btn-video-gallery fancybox" 
            data-gallery="<?php echo esc_attr( json_encode( $gallery_data ) ); ?>">
            <i aria-hidden="true" class="icomoon icomoon-gallery"></i>
            <?php echo sprintf( _n( '%s foto', '%s fotos', count( $gallery_data ), 'tripgo' ), count( $gallery_data ) ); ?>
        </div>
    <?php endif;

    // Share social
    if ( 'yes' === $show_share ): ?>
        <div class="btn-share btn-video-gallery">
            <i aria-hidden="true" class="flaticon flaticon-share"></i>
            <ul class="ova-social">
                <?php foreach ( $args_social as $name => $item_social ): ?>
                    <li>
                        <a href="<?php echo esc_url( $item_social['url'] ); ?>" class="<?php echo esc_attr( $name ); ?>" target="_blank">
                            <i aria-hidden="true" class="<?php echo esc_attr( $item_social['icon'] ); ?>"></i>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
</div>