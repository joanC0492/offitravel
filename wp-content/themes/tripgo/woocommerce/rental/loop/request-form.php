<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get product id
$product_id = tripgo_get_meta_data( 'id', $args, get_the_id() );

// Get product
$product = wc_get_product( $product_id );
if ( !$product || !$product->is_type( 'ovabrw_car_rental' ) ) return;

// Date format
$date_format = function_exists( 'ovabrw_get_date_format' ) ? ovabrw_get_date_format() : 'd-m-Y';

// Placeholder date
$placeholder = function_exists( 'ovabrw_get_placeholder_date' ) ? ovabrw_get_placeholder_date() : esc_html__( 'DD-MM-YYYY', 'tripgo' );

// Get check-in date
$checkin_date = function_exists( 'ovabrw_get_current_date_from_search' ) ? ovabrw_get_current_date_from_search( 'pickup_date', $product_id ) : '';

// Start fixed date
$start_fixed_date = get_post_meta( $product_id, 'ovabrw_fixed_time_check_in', true );

// End fixed date
$end_fixed_date = get_post_meta( $product_id, 'ovabrw_fixed_time_check_out', true );

// Get duration
$duration = get_post_meta( $product_id, 'ovabrw_duration_checkbox', true );

// Show date
$show_date = get_option( 'ova_brw_request_booking_form_show_dates', 'yes' );

// Show/Hide checkout field
$hide_checkout = '';

// Check-out field
$checkout_field = get_post_meta( $product_id, 'ovabrw_manage_checkout_field', true );
if ( !$checkout_field ) $checkout_field = 'global';
if ( 'global' === $checkout_field ) {
    if ( $show_date != 'yes' ) {
        $hide_checkout = ' ovabrw-hide-field';
    }
} elseif ( 'hide' === $checkout_field ) {
    $hide_checkout = ' ovabrw-hide-field';
} else {
    $hide_checkout = '';
}

// Terms & conditions
$terms_conditions   = get_option( 'ova_brw_request_booking_terms_conditions', '' );
$terms_content      = get_option( 'ova_brw_request_booking_terms_conditions_content', '' );

// Check-in id
$checkin_id = tripgo_unique_id( 'checkin-date' );

// Check-out id
$checkout_id = tripgo_unique_id( 'checkout-date' );

?>

<div id="request-form" class="ovabrw-product-form ova-request-form">
    <form 
        class="form request-form" 
        action="<?php echo home_url('/'); ?>" 
        method="post" 
        enctype="multipart/form-data"
        autocomplete="off">
        <div class="ovabrw-form-container">
            <div class="rental_item"> 
                <label for="<?php echo esc_attr( 'ovabrw-name-'.$product_id ); ?>" class="ovabrw-required">
                    <?php esc_html_e( 'Name', 'tripgo' ); ?>
                </label>
                <?php tripgo_text_input([
                    'type'          => 'text',
                    'id'            => 'ovabrw-name-'.$product_id,
                    'name'          => 'name',
                    'placeholder'   => esc_html__( 'Your name', 'tripgo' ),
                    'required'      => true,
                    'attrs'         => [
                        'autocomplete' => 'off'
                    ]
                ]); ?>
            </div>
            <div class="rental_item"> 
                <label for="<?php echo esc_attr( 'ovabrw-email-'.$product_id ); ?>" class="ovabrw-required">
                    <?php esc_html_e( 'Email', 'tripgo' ); ?>
                </label>
                <?php tripgo_text_input([
                    'type'          => 'email',
                    'id'            => 'ovabrw-email-'.$product_id,
                    'name'          => 'email',
                    'placeholder'   => esc_html__( 'Your email', 'tripgo' ),
                    'required'      => true,
                    'attrs'         => [
                        'autocomplete' => 'off'
                    ]
                ]); ?>
            </div>
            <?php if ( 'yes' === get_option( 'ova_brw_request_booking_form_show_number', 'yes' ) ): ?>
                <div class="rental_item"> 
                    <label for="<?php echo esc_attr( 'ovabrw-phone-'.$product_id ); ?>" class="ovabrw-required">
                        <?php esc_html_e( 'Phone', 'tripgo' ); ?>
                    </label>
                    <?php tripgo_text_input([
                        'type'          => 'tel',
                        'id'            => 'ovabrw-phone-'.$product_id,
                        'name'          => 'phone',
                        'placeholder'   => esc_html__( 'Your phone', 'tripgo' ),
                        'required'      => true,
                        'attrs'         => [
                            'autocomplete' => 'off'
                        ]
                    ]); ?>
                </div>
            <?php endif; ?>
            <?php if ( 'yes' === get_option( 'ova_brw_request_booking_form_show_address', 'yes' ) ): ?>
                <div class="rental_item"> 
                    <label for="<?php echo esc_attr( 'ovabrw-address-'.$product_id ); ?>" class="ovabrw-required">
                        <?php esc_html_e( 'Address', 'tripgo' ); ?>
                    </label>
                    <?php tripgo_text_input([
                        'type'          => 'text',
                        'id'            => 'ovabrw-address-'.$product_id,
                        'name'          => 'address',
                        'placeholder'   => esc_html__( 'Your address', 'tripgo' ),
                        'required'      => true,
                        'attrs'         => [
                            'autocomplete' => 'off'
                        ]
                    ]); ?>
                </div>
            <?php endif;

            // Timeslots
            if ( !$duration && tripgo_array_exists( $start_fixed_date ) && tripgo_array_exists( $end_fixed_date ) ):
                // Get fixed dates
                $fixed_dates = function_exists( 'ovabrw_get_fixed_dates' ) ? ovabrw_get_fixed_dates( $product_id ) : '';
            ?>
                <div class="rental_item">
                    <h3 class="ovabrw-label ovabrw-required">
                        <?php echo esc_html_e( 'Choose time', 'tripgo' ); ?>
                    </h3>
                    <select name="ovabrw_fixed_time" class="ovabrw_fixed_time ovabrw-input-required">
                        <?php if ( tripgo_array_exists( $fixed_dates ) ):
                            foreach ( $fixed_dates as $date_range => $date_string ): ?>
                                <option value="<?php echo esc_attr( $date_range ); ?>">
                                    <?php echo esc_html( $date_string ); ?>
                                </option>
                            <?php endforeach;
                        else: ?>
                            <option value=""><?php esc_html_e( 'No hay fechas disponibles', 'tripgo' ); ?></option>
                        <?php endif; ?>
                    </select>
                </div>
                <?php if ( 'yes' === $show_date ): ?>
                    <div class="rental_item ovabrw_checkin_field"> 
                        <label for="<?php echo esc_attr( $checkin_id ); ?>" class="ovabrw-required">
                            <?php esc_html_e( 'Check in', 'tripgo' ); ?>
                        </label>
                        <?php tripgo_text_input([
                            'type'          => 'text',
                            'id'            => $checkin_id,
                            'class'         => 'checkin-date',
                            'name'          => 'ovabrw_request_pickup_date',
                            'value'         => $checkin_date,
                            'placeholder'   => $placeholder,
                            'required'      => true,
                            'readonly'      => true
                        ]); ?>
                        <span class="ovabrw-date-loading">
                            <i aria-hidden="true" class="flaticon flaticon-spinner-of-dots"></i>
                        </span>
                    </div>
                <?php endif;
            else: ?>
                <div class="rental_item ovabrw_checkin_field"> 
                    <label for="<?php echo esc_attr( $checkin_id ); ?>" class="ovabrw-required">
                        <?php esc_html_e( 'Check in', 'tripgo' ); ?>
                    </label>
                    <?php tripgo_text_input([
                        'type'      => 'text',
                        'id'        => $checkin_id,
                        'class'     => 'checkin-date',
                        'name'      => 'ovabrw_request_pickup_date',
                        'value'     => $checkin_date,
                        'required'  => true,
                        'data_type' => 'datepicker',
                        'attrs'     => [
                            'data-date'     => strtotime( $checkin_date ) ? gmdate( $date_format, strtotime( $checkin_date ) ) : '',
                            'data-error'    => esc_html__( 'Check-in is required.', 'tripgo' )
                        ]
                    ]); ?>
                    <span class="ovabrw-date-loading">
                        <i aria-hidden="true" class="flaticon flaticon-spinner-of-dots"></i>
                    </span>
                </div>
            <?php endif; ?>
            <div class="rental_item ovabrw_checkout_field<?php echo esc_attr( $hide_checkout ); ?>"> 
                <label for="<?php echo esc_attr( $checkout_id ); ?>" class="ovabrw-required">
                    <?php esc_html_e( 'Check out', 'tripgo' ); ?>
                </label>
                <?php tripgo_text_input([
                    'type'          => 'text',
                    'id'            => $checkout_id,
                    'class'         => 'checkout-date',
                    'name'          => 'ovabrw_request_pickoff_date',
                    'required'      => true,
                    'readonly'      => true,
                    'placeholder'   => $placeholder
                ]); ?>
                <span class="ovabrw-date-loading">
                    <i aria-hidden="true" class="flaticon flaticon-spinner-of-dots"></i>
                </span>
            </div>

            <?php // Guests field
            wc_get_template( 'rental/loop/fields/guests.php', [
                'id' => $product_id
            ]);

            // Custom checkout fields
            if ( 'yes' === get_option( 'ova_brw_request_booking_form_show_ckf', 'yes' ) ) {
                wc_get_template( 'rental/loop/fields/extra_fields.php', [
                    'id'    => $product_id,
                    'form'  => 'request'
                ]);
            }

            // Quantity field
            wc_get_template( 'rental/loop/fields/quantity.php', [
                'id'    => $product_id,
                'form'  => 'request'
            ]);

            // Extra service
            if ( 'yes' === get_option( 'ova_brw_request_booking_form_show_extra_service', 'yes' ) ) {
                wc_get_template( 'rental/loop/fields/resources.php', [
                    'id'    => $product_id,
                    'form'  => 'request'
                ]);
            }

            // Services
            if ( 'yes' === get_option( 'ova_brw_request_booking_form_show_service', 'yes' ) ) {
                wc_get_template( 'rental/loop/fields/services.php', [
                    'id' => $product_id
                ]);
            }

            // Extra info
            if ( 'yes' === get_option( 'ova_brw_request_booking_form_show_extra_info', 'yes' ) ): ?>
                <div class="rental_item">
                    <textarea name="extra" cols="50" rows="5" placeholder="<?php esc_html_e( 'Extra Information', 'tripgo' ); ?>"></textarea>
                </div>
            <?php endif;

            // Show total
            if ( 'yes' === get_option( 'ova_brw_request_booking_form_show_total', 'no' ) ):
                // Get insurance amount
                $insurance_amount = tripgo_get_post_meta( $product_id, 'amount_insurance' );
            ?>
                <div class="ajax-show-total">
                    <?php if ( 'yes' === get_option( 'ova_brw_request_booking_form_show_quantity_availables', 'yes' ) ): ?>
                        <div class="ovabrw-ajax-availables ovabrw-show-amount">
                            <span class="availables-label label">
                                <?php esc_html_e( 'Available: ', 'tripgo' ); ?>
                            </span>
                            <span class="show-availables-number show-amount"></span>
                            <span class="ajax-loading-total">
                                <i aria-hidden="true" class="flaticon flaticon-spinner-of-dots"></i>
                            </span>
                        </div>
                    <?php endif; ?>
                    <div class="ovabrw-ajax-total ovabrw-show-amount">
                        <span class="show-total label">
                            <?php esc_html_e( 'Total:', 'tripgo' ); ?>
                        </span>
                        <span class="show-total-number show-amount"></span>
                        <span class="ajax-loading-total">
                            <i aria-hidden="true" class="flaticon flaticon-spinner-of-dots"></i>
                        </span>
                    </div>
                    <?php if ( $insurance_amount && 'yes' === get_option( 'ova_brw_request_booking_form_show_insurance_amount', 'yes' ) ): ?>
                        <div class="ovabrw-ajax-amount-insurance">
                            <span class="show-amount-insurance"></span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php if ( 'yes' === $terms_conditions && $terms_content ): ?>
            <div class="terms-conditions">
                <label>
                    <?php tripgo_text_input([
                        'type'      => 'checkbox',
                        'class'     => 'ovabrw-conditions',
                        'name'      => 'ovabrw-request-booking-terms-conditions',
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
        if ( 'yes' === get_option( 'ova_brw_recapcha_enable', 'no' ) && ovabrw_get_recaptcha_form( 'enquiry' ) ): ?>
            <div id="ovabrw-g-recaptcha-enquiry" class="ovabrw-g-recaptcha"></div>
            <?php tripgo_text_input([
                'type'  => 'hidden',
                'id'    => 'ovabrw-recaptcha-enquiry-token',
                'class' => 'ovabrw-input-required',
                'name'  => 'ovabrw-recaptcha-token',
                'value' => ''
            ]);
        endif; ?>
        <div class="ajax-error"></div>
        <button type="submit" class="request-form-submit">
            <?php esc_html_e( 'Send Now', 'tripgo' ); ?>
            <span class="ovabrw-submit-loading">
                <i aria-hidden="true" class="flaticon flaticon-spinner-of-dots"></i>
            </span>
        </button>
        <?php
            // Product name
            tripgo_text_input([
                'type'  => 'hidden',
                'name'  => 'product_name',
                'value' => $product->get_title()
            ]);

            // Product ID
            tripgo_text_input([
                'type'  => 'hidden',
                'name'  => 'product_id',
                'value' => $product_id
            ]);

            // Request booking
            tripgo_text_input([
                'type'  => 'hidden',
                'name'  => 'request_booking',
                'value' => 'request_booking'
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