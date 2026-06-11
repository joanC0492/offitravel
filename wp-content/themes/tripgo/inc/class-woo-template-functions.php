<?php if ( !defined( 'ABSPATH' ) ) exit();

/**
 * Single Product
 */
if ( !function_exists( 'tripgo_wc_before_main_content' ) ) {
    function tripgo_wc_before_main_content() {
        echo '<div class="ova-single-product">';
    }
}
if ( !function_exists( 'tripgo_wc_after_main_content' ) ) {
    function tripgo_wc_after_main_content() {
        echo '</div>';
    }
}

/**
 * Header
 */
if ( !function_exists( 'tripgo_wc_before_single_product_header' ) ) {
    function tripgo_wc_before_single_product_header() {
        echo '<div class="row_site">
            <div class="container_site">';
    }
}
if ( !function_exists( 'tripgo_wc_after_single_product_header' ) ) {
    function tripgo_wc_after_single_product_header() {
        echo '</div>
                </div>';
    }
}

/**
 * Top Header
 */
if ( !function_exists( 'tripgo_wc_before_single_product_top_header' ) ) {
    function tripgo_wc_before_single_product_top_header() {
        echo '<div class="single-product-top-header">';
    }
}
if ( !function_exists( 'tripgo_wc_after_single_product_top_header' ) ) {
    function tripgo_wc_after_single_product_top_header() {
        echo '</div>';
    }
}

/**
 * Product summary left
 */
if ( !function_exists( 'tripgo_wc_before_single_product_summary_left' ) ) {
    function tripgo_wc_before_single_product_summary_left() {
        echo '<div class="ova-single-product-summary-left">';
    }
}
if ( !function_exists( 'tripgo_wc_after_single_product_summary_left' ) ) {
    function tripgo_wc_after_single_product_summary_left() {
        echo '</div>';
    }
}

/**
 * Product summary right
 */
if ( !function_exists( 'tripgo_wc_before_single_product_summary_right' ) ) {
    function tripgo_wc_before_single_product_summary_right() {
        echo '<div class="ova-single-product-summary-right">';
    }
}
if ( !function_exists( 'tripgo_wc_after_single_product_summary_right' ) ) {
    function tripgo_wc_after_single_product_summary_right() {
        echo '</div>';
    }
}

/**
 * Title
 */
if ( !function_exists( 'tripgo_wc_template_single_title' ) ) {
    function tripgo_wc_template_single_title( $args ) {
        wc_get_template( 'rental/loop/title.php', [
            'show_title' => $args['show_title']
        ]);
    }
}

/**
 * Location and Review
 */
if ( !function_exists( 'tripgo_wc_template_single_location_review' ) ) {
    function tripgo_wc_template_single_location_review( $args ) {
        wc_get_template( 'rental/loop/location-review.php', $args );
    }
}

/**
 * Video, Gallery Button + Share Button
 */
if ( !function_exists( 'tripgo_wc_template_single_video_gallery' ) ) {
    function tripgo_wc_template_single_video_gallery( $args ) {
        wc_get_template( 'rental/loop/video-gallery.php', $args );
    }
}

/**
 * Gallery Slideshow
 */
if ( !function_exists( 'tripgo_wc_template_single_slideshow' ) ) {
    function tripgo_wc_template_single_slideshow( $args ) {
        wc_get_template( 'rental/loop/gallery-slideshow.php', [
            'show_gallery' => $args['show_gallery_slide']
        ]);
    }
}

/**
 * Features
 */
if ( !function_exists( 'tripgo_wc_template_single_features' ) ) {
    function tripgo_wc_template_single_features( $args ) {
        wc_get_template( 'rental/loop/features.php', [
            'show_features' => $args['show_features']
        ]);
    }
}

/**
 * Content
 */
if ( !function_exists( 'tripgo_wc_before_single_product_content' ) ) {
    function tripgo_wc_before_single_product_content() {
        echo '<div class="row_site">
            <div class="container_site">';
    }
}
if ( !function_exists( 'tripgo_wc_after_single_product_content' ) ) {
    function tripgo_wc_after_single_product_content() {
        echo '</div>
            </div>';
    }
}

/**
 * Related
 */
if ( !function_exists( 'tripgo_wc_template_single_product_related' ) ) {
    function tripgo_wc_template_single_product_related() {
        wc_get_template( 'rental/loop/related.php' );
    }
}

/**
 * Description
 */
if ( !function_exists( 'tripgo_wc_template_single_content' ) ) {
    function tripgo_wc_template_single_content( $args ) {
        wc_get_template( 'rental/loop/content.php', [
            'show_description' => $args['show_description']
        ]);
    }
}

/**
 * Included/Excluded
 */
if ( ! function_exists( 'tripgo_wc_template_single_included_excluded' ) ) {
    function tripgo_wc_template_single_included_excluded( $args ) {
        wc_get_template( 'rental/loop/included-excluded.php', [
            'show_inc_exc' => $args['show_inc_exc']
        ]);
    }
}

/**
 * Plan
 */
if ( ! function_exists( 'tripgo_wc_template_single_plan' ) ) {
    function tripgo_wc_template_single_plan( $args ) {
        wc_get_template( 'rental/loop/plan.php', [
            'show_tour_plan' => $args['show_tour_plan']
        ]);
    }
}

/**
 * Map
 */
if ( !function_exists( 'tripgo_wc_template_single_map' ) ) {
    function tripgo_wc_template_single_map( $args ) {
        wc_get_template( 'rental/loop/map.php', [
            'show_map' => $args['show_map']
        ]);
    }
}

/**
 * Review
 */
if ( !function_exists( 'tripgo_wc_template_single_review' ) ) {
    function tripgo_wc_template_single_review(  $args ) {
        wc_get_template( 'rental/loop/review.php', [
            'show_reviews' => $args['show_reviews']
        ]);
    }
}

/**
 * Form
 */
if ( !function_exists( 'tripgo_wc_template_single_forms' ) ) {
    function tripgo_wc_template_single_forms( $args ) {
        wc_get_template( 'rental/loop/forms.php', [
            'show_form' => $args['show_form']
        ]);
    }
}

/**
 * Table price
 */
if ( !function_exists( 'tripgo_wc_template_single_table_price' ) ) {
    function tripgo_wc_template_single_table_price( $args ) {
        wc_get_template( 'rental/loop/table_price.php', [
            'show_table_price' => $args['show_table_price']
        ]);
    }
}

/**
 * Unavailable time
 */
if ( !function_exists( 'tripgo_wc_template_single_unavailable_time' ) ) {
    function tripgo_wc_template_single_unavailable_time() {
        wc_get_template( 'rental/loop/unavailable_time.php' );
    }
}

/**
 * Content Product
 */
if ( !function_exists( 'tripgo_template_wc_loop_item' ) ) {
    function tripgo_template_wc_loop_item() {
        wc_get_template( 'rental/content-item-product.php' );
    }
}

/* Booking form */
/**
 * Dates
 */
if ( !function_exists( 'tripgo_booking_form_dates' ) ) {
    function tripgo_booking_form_dates( $args ) {
        wc_get_template( 'rental/loop/fields/dates.php', $args );
    }
}

/**
 * Guests
 */
if ( !function_exists( 'tripgo_booking_form_guests' ) ) {
    function tripgo_booking_form_guests( $args ) {
        wc_get_template( 'rental/loop/fields/guests.php', $args );
    }
}

/**
 * Extra Fields
 */
if ( !function_exists( 'tripgo_booking_form_extra_fields' ) ) {
    function tripgo_booking_form_extra_fields( $args ) {
        wc_get_template( 'rental/loop/fields/extra_fields.php', $args );
    }
}

/**
 * Quantity
 */
if ( !function_exists( 'tripgo_booking_form_quantity' ) ) {
    function tripgo_booking_form_quantity( $args ) {
        wc_get_template( 'rental/loop/fields/quantity.php', $args );
    }
}

/**
 * Resources
 */
if ( !function_exists( 'tripgo_booking_form_resources' ) ) {
    function tripgo_booking_form_resources( $args ) {
        wc_get_template( 'rental/loop/fields/resources.php', $args );
    }
}

/**
 * Services
 */
if ( !function_exists( 'tripgo_booking_form_services' ) ) {
    function tripgo_booking_form_services( $args ) {
        wc_get_template( 'rental/loop/fields/services.php', $args );
    }
}

/**
 * Deposit
 */
if ( !function_exists( 'tripgo_booking_form_deposit' ) ) {
    function tripgo_booking_form_deposit( $args ) {
        wc_get_template( 'rental/loop/fields/deposit.php', $args );
    }
}

/**
 * Ajax Total
 */
if ( !function_exists( 'tripgo_booking_form_ajax_total' ) ) {
    function tripgo_booking_form_ajax_total( $args ) {
        wc_get_template( 'rental/loop/fields/ajax-total.php', $args );
    }
}