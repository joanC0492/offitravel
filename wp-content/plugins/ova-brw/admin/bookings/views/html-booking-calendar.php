<?php defined( 'ABSPATH' ) || exit;

// Get rental product IDs
$product_ids = ovabrw_get_tour_product_ids();

// Get data events
$events = $this->get_events();

?>

<div class="ovabrw-booking-calendar-wrap wrap">
    <h1 class="wp-heading-inline">
        <?php esc_html_e( 'Booking Calendar', 'ova-brw' ); ?>
    </h1>
    <form class="ovabrw-booking-calendar-filter" method="POST" action="" autocomplete="off">
        <div class="ovabrw-filter-fields">
            <div class="ovabrw-field">
                <select
                    class="ovabrw-select2"
                    name="pid"
                    data-placeholder="<?php esc_attr_e( 'Select product...', 'ova-brw' ); ?>">
                    <option value="">
                        <?php esc_html_e( 'Select product...', 'ova-brw' ); ?>
                    </option>
                    <?php if ( ovabrw_array_exists( $product_ids ) ):
                        foreach ( $product_ids as $product_id ): ?>
                            <option value="<?php echo esc_attr( $product_id ); ?>">
                                <?php echo esc_html( get_the_title( $product_id ) ); ?>
                            </option>
                        <?php endforeach;
                    endif; ?>
                </select>
            </div>
            <div class="ovabrw-field">
                <select name="show_date">
                    <option value="">
                        <?php esc_html_e( 'Show Check-in & Check-out', 'ova-brw' ); ?>
                    </option>
                    <option value="checkin">
                        <?php esc_html_e( 'Show only Check-in', 'ova-brw' ); ?>
                    </option>
                    <option value="checkout">
                        <?php esc_html_e( 'Show only Check-out', 'ova-brw' ); ?>
                    </option>
                </select>
            </div>
            <?php ovabrw_wp_text_input([
                'type'  => 'submit',
                'class' => 'button filter',
                'name'  => 'filter',
                'value' => esc_attr__( 'Filter', 'ova-brw' ),
                'attrs' => [
                    'title' => esc_attr__( 'Filter', 'ova-brw' )
                ]
            ]); ?>
        </div>
    </form>
    <div class="ovabrw-calendar-container">
        <div id="ovabrw-calendar"></div>
    </div>
    <div class="ovabrw-backbone-modal-backdrop">
        <span class="dashicons dashicons-update-alt"></span>
    </div>
    <?php ovabrw_wp_text_input([
        'type'  => 'hidden',
        'name'  => 'ovabrw-data-events',
        'value' => json_encode( $events )
    ]); ?>
    <?php ovabrw_wp_text_input([
        'type'  => 'hidden',
        'name'  => 'ovabrw-calendar-options',
        'value' => json_encode( $this->get_calendar_options() )
    ]); ?>
</div>