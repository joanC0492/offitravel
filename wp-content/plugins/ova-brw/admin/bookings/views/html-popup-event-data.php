<?php if ( !defined( 'ABSPATH' ) ) exit;

// Get order id
$order_id = $order->get_id();

// Customer address
$customer_address = $order->get_formatted_billing_address();
if ( !$customer_address ) $customer_address = $order->get_formatted_shipping_address();

// Customer email
$customer_email = $order->get_billing_email();

// Customer phone
$customer_phone = $order->get_billing_phone();

// Product ID
$product_id = method_exists( $item, 'get_product_id' ) ? $item->get_product_id() : '';

// Get Product
$product = wc_get_product( $product_id );

// Hidden order itemmeta
$hidden_order_itemmeta = apply_filters( 'woocommerce_hidden_order_itemmeta', [
    '_qty',
    '_tax_class',
    '_product_id',
    '_variation_id',
    '_line_subtotal',
    '_line_subtotal_tax',
    '_line_total',
    '_line_tax',
    'method_id',
    'cost',
    '_reduced_stock',
    '_restock_refunded_items'
]);

// Get order permalink
$order_permalink = get_edit_post_link( $order_id );

// WCFM
if ( !is_admin() && function_exists( 'get_wcfm_view_order_url' ) ) {
    $order_permalink = get_wcfm_view_order_url( $order_id );
}

?>

<ul class="ovabrw-event-data">
    <li>
        <strong class="label">
            <a href="<?php echo esc_url( $order_permalink ); ?>">
                <?php echo wp_kses_post( '#'.$order_id ); ?>
            </a>
        </strong>
    </li>
    <?php if ( $customer_address ): // Address ?>
        <li>
            <strong class="label">
                <?php esc_html_e( 'Customer:', 'ova-brw' ); ?>
            </strong>
            <p>
                <?php echo wp_kses_post( $customer_address ); ?>
            </p>
        </li>
    <?php endif;

    // Customer email
    if ( $customer_email ): ?>
        <li>
            <strong class="label">
                <?php esc_html_e( 'Email:', 'ova-brw' ); ?>
            </strong>
            <span>
                <a href="mailto:<?php echo esc_attr( $customer_email ); ?>">
                    <?php echo wp_kses_post( $customer_email ); ?>
                </a>
            </span>
        </li>
    <?php endif;

    // Customer phone
    if ( $customer_phone ): ?>
        <li>
            <strong class="label">
                <?php esc_html_e( 'Phone:', 'ova-brw' ); ?>
            </strong>
            <span>
                <a href="tel:<?php echo esc_attr( $customer_phone ); ?>">
                    <?php echo wp_kses_post( $customer_phone ); ?>
                </a>
            </span>
        </li>
    <?php endif;

    // Product
    if ( $product ): ?>
        <li>
            <strong class="label">
                <?php esc_html_e( 'Product:', 'ova-brw' ); ?>
            </strong>
            <span>
                <a href="<?php echo esc_url( get_edit_post_link( $product_id ) ); ?>">
                    <?php echo wp_kses_post( $product->get_title() ); ?>
                </a>
            </span>
        </li>
    <?php endif;

    // Guests name
    $guest_names = [
        'ovabrw_adults',
        'ovabrw_childrens',
        'ovabrw_babies'
    ];

    // Get meta data
    $meta_data = $item->get_all_formatted_meta_data('');
    if ( ovabrw_array_exists( $meta_data ) ):
        foreach ( $meta_data as $meta_id => $meta ):
            // Check meta field
            if ( in_array( $meta->key, $hidden_order_itemmeta, true ) || !$meta->value ) continue;
            if ( in_array( $meta->key, $guest_names ) ) {
                $meta->display_value = '<p>'.$meta->value.'</p>';
            }
        ?>
            <li>
                <strong class="label">
                    <?php echo wp_kses_post( $meta->display_key ); ?>:
                </strong>
                <span>
                    <?php echo wp_kses_post( $meta->display_value ); ?>
                </span>
            </li>
        <?php endforeach;
    endif; ?>
</ul>