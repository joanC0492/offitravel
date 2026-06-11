<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get product id
$product_id = tripgo_get_meta_data( 'id', $args, get_the_id() );

// Get product
$product = wc_get_product( $product_id );
if ( !$product || !$product->is_type( 'ovabrw_car_rental' ) ) return;

// Terms & conditions
$terms_conditions   = get_option( 'ova_brw_booking_form_terms_conditions', '' );
$terms_content      = get_option( 'ova_brw_booking_form_terms_conditions_content', '' );

?>

<div id="booking-form" class="ovabrw-product-form ova-booking-form">
    <form 
        class="form booking-form" 
        action="<?php home_url('/'); ?>" 
        method="post" 
        enctype="multipart/form-data"
        autocomplete="off">
        <div class="ovabrw-form-container">
        <?php
            /**
             * Hook: tripgo_booking_form
             * @hooked: tripgo_booking_form_dates - 5
             * @hooked: tripgo_booking_form_guests - 5
             * @hooked: tripgo_booking_form_extra_fields - 10
             * @hooked: tripgo_booking_form_quantity - 10
             * @hooked: tripgo_booking_form_resources - 15
             * @hooked: tripgo_booking_form_services - 15
             * @hooked: tripgo_booking_form_deposit - 20
             * @hooked: tripgo_booking_form_ajax_total - 25
             */
            do_action( 'tripgo_booking_form', [ 'id' => $product_id ] );
        ?>
        </div>
        <?php if ( 'yes' === $terms_conditions && $terms_content ): ?>
            <div class="terms-conditions">
                <label>
                    <?php tripgo_text_input([
                        'type'      => 'checkbox',
                        'class'     => 'ovabrw-conditions',
                        'name'      => 'ovabrw-booking-terms-conditions',
                        'value'     => 'yes',
                        'required'  => true
                    ]); ?>
                    <span class="terms-conditions-content">
                        <?php echo wp_kses_post( $terms_content ); ?>
                        <span class="terms-conditions-required">*</span>
                    </span>
                </label>
            </div>
        <?php endif;

        // Recaptcha
        if ( 'yes' === get_option( 'ova_brw_recapcha_enable', 'no' ) && ovabrw_get_recaptcha_form( 'booking' ) ): ?>
            <div id="ovabrw-g-recaptcha-booking" class="ovabrw-g-recaptcha"></div>
            <?php tripgo_text_input([
                'type'  => 'hidden',
                'id'    => 'ovabrw-recaptcha-booking-token',
                'class' => 'ovabrw-input-required',
                'name'  => 'ovabrw-recaptcha-token',
                'value' => ''
            ]);
        endif; ?>
        <div class="ajax-error"></div>
        <button type="submit" class="booking-form-submit">
            <?php esc_html_e( 'Reservar ahora', 'tripgo' ); ?>
            <span class="ovabrw-submit-loading">
                <i aria-hidden="true" class="flaticon flaticon-spinner-of-dots"></i>
            </span>
        </button>
        <?php
            // Product ID
            tripgo_text_input([
                'type'  => 'hidden',
                'name'  => 'product_id',
                'value' => $product_id
            ]);

            // Add to cart
            tripgo_text_input([
                'type'  => 'hidden',
                'name'  => 'add-to-cart',
                'value' => $product_id
            ]);

            // Quantity
            tripgo_text_input([
                'type'  => 'hidden',
                'name'  => 'quantity',
                'value' => 1
            ]);

            // Quantity by number of guests
            tripgo_text_input([
                'type'  => 'hidden',
                'name'  => 'qty-by-guests',
                'value' => ovabrw_qty_by_guests( $product_id )
            ]);
        ?>
    </form>
</div>