<?php if ( !defined( 'ABSPATH' ) ) exit();

// Show on sale
$show_on_sale = ovabrw_get_meta_data( 'show_on_sale', $args, 'no' );

// Show featured
$show_featured = ovabrw_get_meta_data( 'show_featured', $args, 'yes' );

// Show wishlist
$show_wishlist = ovabrw_get_meta_data( 'show_wishlist', $args, 'yes' );

// Show duration
$show_duration = ovabrw_get_meta_data( 'show_duration', $args, 'yes' );

// Show title
$show_title = ovabrw_get_meta_data( 'show_title', $args, 'yest' );

// Show location
$show_location = ovabrw_get_meta_data( 'show_location', $args, 'yes' );

// Show rating
$show_rating = ovabrw_get_meta_data( 'show_rating', $args, 'yes' );

// Show price
$show_price = ovabrw_get_meta_data( 'show_price', $args, 'yes' );

// Show button
$show_button = ovabrw_get_meta_data( 'show_button', $args, 'yes' );

// Args show
$args_show = [
    'show_featured' => $show_featured,
    'show_wishlist' => $show_wishlist,
    'show_duration' => $show_duration,
    'show_title'    => $show_title,
    'show_location' => $show_location,
    'show_rating'   => $show_rating,
    'show_price'    => $show_price,
    'show_button'   => $show_button
];

// Posts per page
$posts_per_page = (int)ovabrw_get_meta_data( 'posts_per_page', $args, 4 );

// Orderby
$orderby = ovabrw_get_meta_data( 'product_orderby', $args, 'ID' );

// Order
$order = ovabrw_get_meta_data( 'product_order', $args, 'DESC' );

// Filter title
$filter_title = ovabrw_get_meta_data( 'filter_title', $args );

// Text all
$text_all = ovabrw_get_meta_data( 'catAll', $args );

// Categories
$categories = ovabrw_get_meta_data( 'categories', $args, [] );
if ( ovabrw_array_exists( $categories ) ) {
    $categories = get_categories([
        'taxonomy'  => 'product_cat',
        'orderby'   => ovabrw_get_meta_data( 'orderby', $args, 'ID' ),
        'order'     => ovabrw_get_meta_data( 'order', $args, 'ASC' ),
        'slug'      => $categories
    ]);
} else {
    if ( !$text_all ) $text_all = esc_html__( 'All','ova-brw' );
}

// Slide options
$slide_options = [
    'slidesPerView'         => ovabrw_get_meta_data( 'item_number', $args, 1 ),
    'slidesPerGroup'        => ovabrw_get_meta_data( 'slides_to_scroll', $args, 1 ),
    'spaceBetween'          => ovabrw_get_meta_data( 'margin_items', $args, 30 ),
    'autoplay'              => 'yes' === ovabrw_get_meta_data( 'autoplay', $args ) ? true : false,
    'pauseOnMouseEnter'     => 'yes' === ovabrw_get_meta_data( 'pause_on_hover', $args ) ? true : false,
    'delay'                 => ovabrw_get_meta_data( 'autoplay_speed', $args, 3000 ),
    'speed'                 => ovabrw_get_meta_data( 'smartspeed', $args, 500 ),
    'loop'                  => 'yes' === ovabrw_get_meta_data( 'infinite', $args ) ? true : false,
    'nav'                   => 'yes' === ovabrw_get_meta_data( 'nav_control', $args ) ? true : false,
    'dots'                  => 'yes' === ovabrw_get_meta_data( 'dots_control', $args ) ? true : false,
    'breakpoints'           => [
        '0'     => [
            'slidesPerView' => 1
        ],
        '768'   => [
            'slidesPerView' => 1
        ],
        '1024'  => [
            'slidesPerView' => ovabrw_get_meta_data( 'item_number', $args, 1 ),
        ]
    ],
    'rtl'                   => is_rtl() ? true: false
];

?>

<div class="ova-product-filter-ajax"
    data-show_on_sale="<?php echo esc_attr( $show_on_sale ); ?>"
    data-args_show="<?php echo esc_attr( json_encode( $args_show ) ); ?>"
    data-posts_per_page="<?php echo esc_attr( $posts_per_page ); ?>"
    data-orderby="<?php echo esc_attr( $orderby ); ?>"
    data-order="<?php echo esc_attr( $order ); ?>">
    <ul class="product-filter-category">
        <?php if ( $filter_title ): // Title ?>
        	<li class="filter-title">
                <?php echo esc_html( $filter_title ); ?>
            </li>
        <?php endif;

        // Filter - All
        if ( $text_all ): ?>
            <li class="product-filter-button active-category" data-slug="all">
                <span class="category">
                    <?php echo esc_html( $text_all ); ?>
                </span>
                <i aria-hidden="true" class="icomoon icomoon-angle-right"></i>
            </li>
        <?php endif;

        // Loop categories
        if ( ovabrw_array_exists( $categories ) ):
            foreach ( $categories as $i => $category ):
                // Category name
                $name = $category->name;

                // Category slug
                $slug = $category->slug;

                // Active
                $active_class = '';
                if ( !$i && !$text_all ) {
                    $active_class = ' active-category';
                }
            ?>

                <li class="product-filter-button<?php echo esc_attr( $active_class ); ?>" data-slug="<?php echo esc_attr( $slug ); ?>">
                    <span class="category">
                        <?php echo esc_html( $name ); ?>
                    </span>
                    <i aria-hidden="true" class="icomoon icomoon-angle-right"></i>
                </li>
            <?php endforeach;
        endif; // END if ?>
    </ul>
    <div class="content-item slide-product" data-options="<?php echo esc_attr( json_encode( $slide_options ) ); ?>"></div>  
</div>