<?php

// Add Closed status in WooCommerce
add_action( 'init', 'register_wc_closed_order_statuses' );
function register_wc_closed_order_statuses() {
    register_post_status( 'wc-closed', array(
        'label'                     => _x( 'Closed', 'Order status', 'ova-brw' ),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Closed <span class="count">(%s)</span>', 'Closed<span class="count">(%s)</span>', 'ova-brw' )
    ));
}