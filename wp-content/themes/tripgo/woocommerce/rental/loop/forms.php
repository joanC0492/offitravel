<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get product id
$product_id = tripgo_get_meta_data( 'id', $args, get_the_id() );

// Get product
$product = wc_get_product( $product_id );
if ( !$product || !$product->is_type( 'ovabrw_car_rental' ) ) return;

// Loading reCAPTCHA
if ( function_exists( 'ovabrw_loading_reCAPTCHA' ) ) ovabrw_loading_reCAPTCHA();

// Get product prices
$product_prices = tripgo_get_price_product( $product_id );

// Show booking form
$show_booking = get_option( 'ova_brw_template_show_booking_form', 'yes' );

// Show request form
$show_request = get_option( 'ova_brw_template_show_request_booking', 'yes' );

// Show enquiry form
$show_enquiry = get_option( 'ova_brw_template_show_enquiry_booking', 'no' );

if ( $product_id ) {
    // Get product form
    $product_form = tripgo_get_post_meta( $product_id, 'forms_product' );

    if ( 'all' === $product_form ) {
        $show_booking = $show_request = $show_enquiry = 'yes';
    } elseif ( 'booking' === $product_form ) {
        $show_booking = 'yes';
        $show_request = $show_enquiry = '';
    } elseif ( 'enquiry' === $product_form ) {
        $show_booking = $show_enquiry = '';
        $show_request = 'yes';
    } elseif ( 'enquiry_shortcode' === $product_form ) {
        $show_booking = $show_request = '';
        $show_enquiry = 'yes';
    }
}

// Show form
$show_form = tripgo_get_meta_data( 'show_form', $args, 'yes' );
if ( 'yes' === $show_form ): ?>
    <div class="ova-forms-product">
        <div class="forms-wrapper">
            <div class="price-product">
                <div class="label">
                    <i aria-hidden="true" class="icomoon icomoon-tag"></i>
                    <span><?php esc_html_e( 'Desde', 'tripgo' ); ?></span>
                </div>
                <div class="price">
                    <span class="regular-price">
                        <?php echo wp_kses_post( wc_price( $product_prices['regular_price'] ) ); ?>
                    </span>
                    <?php if ( $product_prices['sale_price'] ): ?>
                        <span class="sale-price">
                            <?php echo wp_kses_post( wc_price( $product_prices['sale_price'] ) ); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="logo">
                <span class="line"></span>
                <i aria-hidden="true" class="<?php echo esc_attr( apply_filters( 'ovabrw_booking_form_icon', 'icomoon icomoon-flig-outline' ) ); ?>"></i>
            </div>
            <div class="tabs">
                <?php if ( 'yes' === $show_booking ): ?>
                    <div class="item" data-id="#booking-form">
                        <?php esc_html_e( 'Formulario de reserva', 'tripgo' ); ?>
                    </div>
                <?php endif;

                // Request
                if ( 'yes' === $show_request ): ?>
                    <div class="item" data-id="#request-form">
                        <?php esc_html_e( 'Formulario de solicitud', 'tripgo' ); ?>
                    </div>
                <?php endif;

                // Enquiry
                if ( 'yes' === $show_enquiry ): ?>
                    <div class="item" data-id="#enquiry-form">
                        <?php esc_html_e( 'Formulario de consulta', 'tripgo' ); ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php
                // Booking form
                if ( 'yes' === $show_booking ) {
                    wc_get_template( 'rental/loop/booking-form.php', [ 'id' => $product_id ] );
                }

                // Request form
                if ( 'yes' === $show_request ) {
                    wc_get_template( 'rental/loop/request-form.php', [ 'id' => $product_id ] );
                }

                // Enquiry form
                if ( 'yes' === $show_enquiry ) {
                    wc_get_template( 'rental/loop/enquiry-form.php', [ 'id' => $product_id ] );
                }
            ?>
        </div>
    </div>
<?php endif; ?>