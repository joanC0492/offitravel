<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get product id
$product_id = tripgo_get_meta_data( 'id', $args, get_the_id() );

// Get product
$product = wc_get_product( $product_id );
if ( !$product || !$product->is_type('ovabrw_car_rental') ) return;

// Get quantity
$stock_quantity = absint( tripgo_get_post_meta( $product_id, 'stock_quantity' ) );
if ( 'yes' === get_option( 'ova_brw_booking_form_show_quantity', 'no' ) ):
    // Get field id
    $field_id = tripgo_unique_id( 'quantity' );
?>
    <div class="rental_item">
        <label for="<?php echo esc_attr( $field_id ); ?>" class="ovabrw-required">
            <?php esc_html_e( 'Quantity', 'tripgo' ); ?>
        </label>
        <?php tripgo_text_input([
            'type'      => 'number',
            'id'        => $field_id,
            'class'     => 'ovabrw-quantity',
            'name'      => 'ovabrw_quantity',
            'value'     => '1',
            'required'  => true,
            'attrs'     => [
                'min'           => 1,
                'max'           => $stock_quantity,
                'data-error'    => esc_html__( 'Quantity is required.', 'tripgo' )
            ]
        ]); ?>
    </div>
<?php endif;