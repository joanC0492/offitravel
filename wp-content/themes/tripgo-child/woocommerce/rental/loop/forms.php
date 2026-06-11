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

$stock_qty = absint( get_post_meta( $product_id, 'ovabrw_stock_quantity', true ) );
$allowed_dates_map = function_exists( 'offitravel_ovabrw_get_allowed_ymd_map' ) ? offitravel_ovabrw_get_allowed_ymd_map( $product_id ) : array();
$has_whitelist_dates = ! empty( $allowed_dates_map );

$zero_stock_contact_ui = (
	'yes' === $show_booking
	&& function_exists( 'offitravel_ovabrw_should_show_zero_stock_contact' )
	&& offitravel_ovabrw_should_show_zero_stock_contact( $product_id )
);
$zero_stock_contact_ui = apply_filters( 'offitravel_ovabrw_show_zero_stock_contact_ui', $zero_stock_contact_ui, $product_id, $stock_qty );

/**
 * Sin fechas en lista blanca: solo mensaje si no mostramos bloque de contacto con botones.
 */
$booking_unavailable_no_stock = apply_filters(
	'offitravel_ovabrw_booking_unavailable_zero_stock',
	(
		'yes' === $show_booking
		&& ! $has_whitelist_dates
		&& ! $zero_stock_contact_ui
	),
	$product_id,
	$stock_qty
);

// Show form
$show_form = tripgo_get_meta_data( 'show_form', $args, 'yes' );
if ( 'yes' === $show_form ): ?>
    <div class="ova-forms-product<?php
        echo $booking_unavailable_no_stock ? ' offitravel-booking-no-stock' : '';
        echo $zero_stock_contact_ui ? ' offitravel-zero-stock-contact' : '';
    ?>">
        <div class="forms-wrapper">
            <?php if ( $zero_stock_contact_ui ) : ?>
                <?php wc_get_template( 'rental/loop/zero-stock-contact.php', [ 'product_id' => $product_id, 'product_prices' => $product_prices ] ); ?>
            <?php else : ?>
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
            <?php
            $show_booking_tab = ( 'yes' === $show_booking && ! $booking_unavailable_no_stock );
            $show_any_tab     = $show_booking_tab || 'yes' === $show_request || 'yes' === $show_enquiry;
            ?>
            <?php if ( $show_any_tab ) : ?>
            <div class="tabs">
                <?php if ( $show_booking_tab ) : ?>
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
            <?php endif; ?>
            <?php
                // Booking form or “no stock” message
                if ( 'yes' === $show_booking ) {
                    if ( $booking_unavailable_no_stock ) {
                        echo '<div class="offitravel-booking-unavailable"><p>' . esc_html__( 'La salida aún no está disponible.', 'tripgo-child' ) . '</p></div>';
                    } else {
                        wc_get_template( 'rental/loop/booking-form.php', [ 'id' => $product_id ] );
                    }
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
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
