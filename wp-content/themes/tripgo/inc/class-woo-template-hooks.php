<?php if ( !defined( 'ABSPATH' ) ) exit();

add_action( 'tripgo_wc_before_main_content', 'tripgo_wc_before_main_content', 10 );
add_action( 'tripgo_wc_after_main_content', 'tripgo_wc_after_main_content', 10 );

// Header
add_action( 'tripgo_wc_before_single_product_header', 'tripgo_wc_before_single_product_header', 5 );
add_action( 'tripgo_wc_after_single_product_header', 'tripgo_wc_after_single_product_header', 5 );
add_action( 'tripgo_wc_before_single_product_top_header', 'tripgo_wc_before_single_product_top_header', 5 );
add_action( 'tripgo_wc_after_single_product_top_header', 'tripgo_wc_after_single_product_top_header', 5 );
add_action( 'tripgo_wc_single_product_top_header', 'tripgo_wc_template_single_title', 10 );
add_action( 'tripgo_wc_single_product_top_header', 'tripgo_wc_template_single_video_gallery', 10 );
add_action( 'tripgo_wc_single_product_header', 'tripgo_wc_template_single_location_review', 10 );
add_action( 'tripgo_wc_single_product_header', 'tripgo_wc_template_single_slideshow', 10 );
add_action( 'tripgo_wc_single_product_header', 'tripgo_wc_template_single_features', 10 );

// Content
add_action( 'tripgo_wc_before_single_product_content', 'tripgo_wc_before_single_product_content', 10 );
add_action( 'tripgo_wc_before_single_product_summary_left', 'tripgo_wc_before_single_product_summary_left', 15 );
add_action( 'tripgo_wc_single_product_summary_left', 'tripgo_wc_template_single_content', 20 );
add_action( 'tripgo_wc_single_product_summary_left', 'tripgo_wc_template_single_included_excluded', 20 );
add_action( 'tripgo_wc_single_product_summary_left', 'tripgo_wc_template_single_plan', 20 );
add_action( 'tripgo_wc_single_product_summary_left', 'tripgo_wc_template_single_map', 20 );
add_action( 'tripgo_wc_single_product_summary_left', 'tripgo_wc_template_single_review', 20 );
add_action( 'tripgo_wc_after_single_product_summary_left', 'tripgo_wc_after_single_product_summary_left', 15 );
add_action( 'tripgo_wc_before_single_product_summary_right', 'tripgo_wc_before_single_product_summary_right', 15 );
add_action( 'tripgo_wc_single_product_summary_right', 'tripgo_wc_template_single_forms', 20 );
add_action( 'tripgo_wc_single_product_summary_right', 'tripgo_wc_template_single_table_price', 20 );
add_action( 'tripgo_wc_after_single_product_summary_right', 'tripgo_wc_after_single_product_summary_right', 15 );
add_action( 'tripgo_wc_after_single_product_content', 'tripgo_wc_after_single_product_content', 10 );

// Related
add_action( 'tripgo_wc_single_product_related', 'tripgo_wc_template_single_product_related', 20 );

/**
 * Tripgo Product Loop Items.
 * @see tripgo_wc_loop_item()
 */
add_action( 'tripgo_wc_loop_item', 'tripgo_template_wc_loop_item', 10 );

// Booking form
add_action( 'tripgo_booking_form', 'tripgo_booking_form_dates', 5 );
add_action( 'tripgo_booking_form', 'tripgo_booking_form_guests', 5 );
add_action( 'tripgo_booking_form', 'tripgo_booking_form_extra_fields', 10 );
add_action( 'tripgo_booking_form', 'tripgo_booking_form_quantity', 10 );
add_action( 'tripgo_booking_form', 'tripgo_booking_form_resources', 15 );
add_action( 'tripgo_booking_form', 'tripgo_booking_form_services', 15 );
add_action( 'tripgo_booking_form', 'tripgo_booking_form_deposit', 20 );
add_action( 'tripgo_booking_form', 'tripgo_booking_form_ajax_total', 25 );