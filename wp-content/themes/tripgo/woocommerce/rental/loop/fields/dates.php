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
$start_fixed_date = tripgo_get_post_meta( $product_id, 'fixed_time_check_in' );

// End fixed date
$end_fixed_date = tripgo_get_post_meta( $product_id, 'fixed_time_check_out' );

// Get duration
$duration = tripgo_get_post_meta( $product_id, 'duration_checkbox' );

// Show/Hide checkout field
$hide_checkout = '';

// Check-out fields
$checkout_field = tripgo_get_post_meta( $product_id, 'manage_checkout_field' );
if ( !$checkout_field ) $checkout_field = 'global';

// Show check-out field
$show_checkout = get_option( 'ova_brw_booking_form_show_checkout', 'yes' );
if ( 'global' === $checkout_field ) {
    if ( $show_checkout != 'yes' ) {
        $hide_checkout = ' ovabrw-hide-field';
    }
} elseif ( $checkout_field === 'hide' ) {
    $hide_checkout = ' ovabrw-hide-field';
} else {
    $hide_checkout = '';
}

// Check-in id
$checkin_id = tripgo_unique_id( 'checkin-date' );

// Check-out id
$checkout_id = tripgo_unique_id( 'checkout-date' );

if ( !$duration && tripgo_array_exists( $start_fixed_date ) && tripgo_array_exists( $end_fixed_date ) ):
    // Get fixed dates
    $fixed_dates = function_exists( 'ovabrw_get_fixed_dates' ) ? ovabrw_get_fixed_dates( $product_id ) : '';
?>
    <div class="rental_item ovabrw_fixed_time_field">
        <h3 class="ovabrw-label ovabrw-required">
            <?php esc_html_e( 'Elegir horario', 'tripgo' ); ?>
        </h3>
        <select name="ovabrw_fixed_time" class="ovabrw_fixed_time ovabrw-input-required">
            <?php if ( tripgo_array_exists( $fixed_dates ) ): ?>
                <?php foreach ( $fixed_dates as $date_range => $date_string ): ?>
                    <option value="<?php echo esc_attr( $date_range ); ?>">
                        <?php echo esc_html( $date_string ); ?>
                    </option>
                <?php endforeach; ?>
            <?php else: ?>
                <option value=""><?php esc_html_e( 'Sin horario', 'tripgo' ); ?></option>
            <?php endif; ?>
        </select>
    </div>
    <div class="rental_item ovabrw_checkin_field">
        <label for="<?php echo esc_attr( $checkin_id ); ?>" class="ovabrw-required">
            <?php esc_html_e( 'Inicio', 'tripgo' ); ?>
        </label>
        <?php tripgo_text_input([
            'type'          => 'text',
            'id'            => $checkin_id,
            'class'         => 'checkin-date',
            'name'          => 'ovabrw_pickup_date',
            'value'         => $checkin_date,
            'placeholder'   => $placeholder,
            'required'      => true,
            'readonly'      => true
        ]); ?>
        <span class="ovabrw-date-loading">
            <i aria-hidden="true" class="flaticon flaticon-spinner-of-dots"></i>
        </span>
    </div>
<?php else: ?>
    <div class="rental_item ovabrw_checkin_field">
        <label for="<?php echo esc_attr( $checkin_id ); ?>" class="ovabrw-required">
            <?php esc_html_e( 'Inicio', 'tripgo' ); ?>
        </label>
        <?php tripgo_text_input([
            'type'      => 'text',
            'id'        => $checkin_id,
            'class'     => 'checkin-date',
            'name'      => 'ovabrw_pickup_date',
            'value'     => $checkin_date,
            'required'  => true,
            'data_type' => 'datepicker',
            'attrs'     => [
                'data-date' => strtotime( $checkin_date ) ? gmdate( $date_format, strtotime( $checkin_date ) ) : ''
            ]
        ]); ?>
        <span class="ovabrw-date-loading">
            <i aria-hidden="true" class="flaticon flaticon-spinner-of-dots"></i>
        </span>
    </div>
<?php endif; ?>
<div class="rental_item ovabrw_checkout_field<?php echo esc_attr( $hide_checkout ); ?>">
    <label for="<?php echo esc_attr( $checkout_id ); ?>" class="ovabrw-required">
        <?php esc_html_e( 'Fin', 'tripgo' ); ?>
    </label>
    <?php tripgo_text_input([
        'type'          => 'text',
        'id'            => $checkout_id,
        'class'         => 'checkout-date',
        'name'          => 'ovabrw_pickoff_date',
        'placeholder'   => $placeholder,
        'required'      => true,
        'readonly'      => true
    ]); ?>
    <span class="ovabrw-date-loading">
        <i aria-hidden="true" class="flaticon flaticon-spinner-of-dots"></i>
    </span>
</div>