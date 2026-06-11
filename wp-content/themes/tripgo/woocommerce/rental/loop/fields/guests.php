<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get product id
$product_id = tripgo_get_meta_data( 'id', $args, get_the_id() );

// Get product
$product = wc_get_product( $product_id );
if ( !$product || !$product->is_type( 'ovabrw_car_rental' ) ) return;

// Max total of guests
$max_total_guest = tripgo_get_post_meta( $product_id, 'max_total_guest' );

// Get adult prices
$adult_price = tripgo_get_price_product( $product_id );

// Max number of adults
$max_adults = tripgo_get_post_meta( $product_id, 'adults_max' );

// Min number of adults
$min_adults = tripgo_get_post_meta( $product_id, 'adults_min' );
$min_adults = apply_filters( 'ovabrw_min_adults', $min_adults, $product_id );
if ( !$min_adults ) $min_adults = 0;

// Show price adults
$show_price_adults = get_option( 'ova_brw_booking_form_show_price_beside_adults', 'yes' );

// Label adults
$label_adults = get_option( 'ova_brw_label_beside_adults', '' );

// Children price
$children_price = tripgo_get_post_meta( $product_id, 'children_price' );

// Max number of children
$max_children = tripgo_get_post_meta( $product_id, 'childrens_max' );

// Min number of children
$min_children = tripgo_get_post_meta( $product_id, 'childrens_min' );
$min_children = apply_filters( 'ovabrw_min_children', $min_children, $product_id );
if ( !$min_children ) $min_children = 0;

// Show children
$show_children = function_exists( 'ovabrw_show_children' ) ? ovabrw_show_children( $product_id ) : false;

// Show price childrens
$show_price_children = get_option( 'ova_brw_booking_form_show_price_beside_childrens', 'yes' );

// Label children
$label_children = get_option( 'ova_brw_label_beside_childrens', '' );

// Baby prices
$baby_price = tripgo_get_post_meta( $product_id, 'baby_price' );

// Max number of babies
$max_babies = tripgo_get_post_meta( $product_id, 'babies_max' );

// Min number of babies
$min_babies = tripgo_get_post_meta( $product_id, 'babies_min' );
$min_babies = apply_filters( 'ovabrw_min_babies', $min_babies, $product_id );
if ( !$min_babies ) $min_babies = 0;

// Show baby
$show_baby = function_exists( 'ovabrw_show_babies' ) ? ovabrw_show_babies( $product_id ) : false;

// Show price babies
$show_price_babies = get_option( 'ova_brw_booking_form_show_price_beside_babies', 'yes' );

// Label babies
$label_babies = get_option( 'ova_brw_label_beside_babies', '' );

// Number of adults
$number_adults = absint( tripgo_get_meta_data( 'ovabrw_adults', $_GET, $min_adults ) );

// Number of children
$number_children = absint( tripgo_get_meta_data( 'ovabrw_childrens', $_GET, $min_children ) );

// Number of babies
$number_babies = absint( tripgo_get_meta_data( 'ovabrw_babies', $_GET, $min_babies ) );

// Total of guests
$gueststotal = absint( $number_adults ) + absint( $number_children ) + absint( $number_babies );

// Guests picker class
$wrap_guestspicker_class = '';
if ( !$show_children && !$show_baby ) {
    $wrap_guestspicker_class = 'only-show-adults';
}

?>

<div class="rental_item">
    <h3 class="ovabrw-label ovabrw-required">
        <?php esc_html_e( 'Huespedes', 'tripgo' ); ?>
    </h3>
    <div class="ovabrw-wrapper-guestspicker <?php echo esc_attr( $wrap_guestspicker_class ); ?>">
        <input
            type="hidden"
            name="ovabrw_max_total_guest"
            value="<?php echo esc_attr( $max_total_guest ); ?>"
        />
        <div class="ovabrw-guestspicker">
            <div class="guestspicker">
                <span class="gueststotal"><?php echo esc_html( $gueststotal ); ?></span>
            </div>
            <span class="ovabrw-guest-loading">
                <i aria-hidden="true" class="flaticon flaticon-spinner-of-dots"></i>
            </span>
        </div>
        <div class="ovabrw-guestspicker-content">
            <div class="guests-buttons">
                <div class="description">
                    <h3 class="ovabrw-label">
                        <?php esc_html_e( 'Adultos', 'tripgo' ); ?>
                    </h3>
                    <?php if ( $label_adults ): ?>
                        <span class="guests-labels beside_adults">
                            <?php echo esc_html( $label_adults ); ?>
                        </span>
                    <?php endif;

                    // Show price
                    if ( 'yes' === $show_price_adults ): ?>
                        <span class="guests-price adults-price">
                            <?php echo wc_price( $adult_price['regular_price'] ); ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="guests-button">
                    <div class="guests-icon minus">
                        <i aria-hidden="true" class="icomoon icomoon-minus"></i>
                    </div>
                    <?php tripgo_text_input([
                        'type'      => 'text',
                        'class'     => 'guests-input ovabrw_adults',
                        'name'      => 'ovabrw_adults',
                        'value'     => $number_adults,
                        'required'  => true,
                        'readonly'  => true,
                        'attrs'     => [
                            'min'           => $min_adults,
                            'max'           => $max_adults,
                            'data-label'    => esc_html__( 'Adultos', 'ova-brw' ),
                            'data-name'     => 'ovabrw_adults'
                        ]
                    ]); ?>
                    <div class="guests-icon plus">
                        <i aria-hidden="true" class="icomoon icomoon-plus"></i>
                    </div>
                </div>
            </div>
            <?php if ( $show_children ): ?>
                <div class="guests-buttons">
                    <div class="description">
                        <h3 class="ovabrw-label">
                            <?php esc_html_e( 'Ninos', 'tripgo' ); ?>
                        </h3>
                        <?php if ( $label_children ): ?>
                            <span class="guests-labels beside_childrens">
                                <?php echo esc_html( $label_children ); ?>
                            </span>
                        <?php endif;

                        // Show price
                        if ( 'yes' === $show_price_children ): ?>
                            <span class="guests-price childrens-price">
                                <?php echo ovabrw_wc_price( $children_price ); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="guests-button">
                        <div class="guests-icon minus">
                            <i aria-hidden="true" class="icomoon icomoon-minus"></i>
                        </div>
                        <?php tripgo_text_input([
                            'type'      => 'text',
                            'class'     => 'guests-input ovabrw_childrens',
                            'name'      => 'ovabrw_childrens',
                            'value'     => $number_children,
                            'readonly'  => true,
                            'attrs'     => [
                                'min'           => $min_children,
                                'max'           => $max_children,
                                'data-label'    => esc_html__( 'Ninos', 'ova-brw' ),
                                'data-name'     => 'ovabrw_childrens'
                            ]
                        ]); ?>
                        <div class="guests-icon plus">
                            <i aria-hidden="true" class="icomoon icomoon-plus"></i>
                        </div>
                    </div>
                </div>
            <?php endif;

            // Show baby
            if ( $show_baby ): ?>
                <div class="guests-buttons">
                    <div class="description">
                        <h3 class="ovabrw-label">
                            <?php esc_html_e( 'Bebes', 'tripgo' ); ?>
                        </h3>
                         <?php if ( $label_babies ): ?>
                            <span class="guests-labels beside_babies">
                                <?php echo esc_html( $label_babies ); ?>
                            </span>
                        <?php endif;

                        // Show price
                        if ( 'yes' === $show_price_babies ): ?>
                            <span class="guests-price babies-price">
                                <?php echo ovabrw_wc_price( $baby_price ); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="guests-button">
                        <div class="guests-icon minus">
                            <i aria-hidden="true" class="icomoon icomoon-minus"></i>
                        </div>
                        <?php tripgo_text_input([
                            'type'      => 'text',
                            'class'     => 'guests-input ovabrw_babies',
                            'name'      => 'ovabrw_babies',
                            'value'     => $number_babies,
                            'readonly'  => true,
                            'attrs'     => [
                                'min'           => $min_babies,
                                'max'           => $max_babies,
                                'data-label'    => esc_html__( 'Bebes', 'ova-brw' ),
                                'data-name'     => 'ovabrw_babies'
                            ]
                        ]); ?>
                        <div class="guests-icon plus">
                            <i aria-hidden="true" class="icomoon icomoon-plus"></i>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php if ( 'yes' === get_option( 'ovabrw_guest_info', '' ) ): ?>
            <div class="ovabrw-guest-info">
                <div class="guest-info-heading">
                    <?php esc_html_e( 'Introduce la informacion de los huespedes', 'ova-brw' ); ?>
                </div>
                <div class="guest-info-accordion"></div>
            </div>
        <?php endif; ?>
    </div>
</div>