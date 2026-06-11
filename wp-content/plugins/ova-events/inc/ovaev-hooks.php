<?php if ( !defined( 'ABSPATH' ) ) exit();

// Search Form action
add_action( 'ovaev_search_form', function() {
    return ovaev_get_template( 'search_form.php' );
});

// Highlight date 1 action
add_action( 'ovaev_loop_highlight_date_1', function( $id = '' ) {
    return ovaev_get_template( 'loop/highlight_date_1.php', [ 'id' => $id ] );
});

// Highlight date 2 action
add_action( 'ovaev_loop_highlight_date_2', function( $id = '' ) {
    return ovaev_get_template( 'loop/highlight_date_2.php', [ 'id' => $id ] );
});

// Highlight date 3 action
add_action( 'ovaev_loop_highlight_date_3', function( $id = '' ) {
    return ovaev_get_template( 'loop/highlight_date_3.php', [ 'id' => $id ] );
});

// Thumbanil archive event list
add_action( 'ovaev_loop_thumbnail_list', function( $id = '' ) {
    return ovaev_get_template( 'loop/thumbnail_list.php', [ 'id' => $id ] );
});

// Thumbanil archive event grid
add_action( 'ovaev_loop_thumbnail_grid', function( $id = '' ) {
    return ovaev_get_template( 'loop/thumbnail_grid.php', [ 'id' => $id ] );
});

// Thumbanil archive event
add_action( 'ovaev_loop_thumbnail', function( $id = '' ) {
    return ovaev_get_template( 'loop/thumbnail.php', [ 'id' => $id ] );
});

// Loop type action
add_action( 'ovaev_loop_type', function( $id = '' ) {
    return ovaev_get_template( 'loop/type.php', [ 'id' => $id ] );
});

// Loop Title
add_action( 'ovaev_loop_title', function( $id = '' ) {
    return ovaev_get_template( 'loop/title.php', [ 'id' => $id ] );
});

// Loop venue
add_action( 'ovaev_loop_venue', function( $id = '' ) {
    return ovaev_get_template( 'loop/venue.php', array( 'id' => $id ) );
});

// Loop excerpt
add_action( 'ovaev_loop_excerpt', function( $id = '' ) {
    return ovaev_get_template( 'loop/excerpt.php', [ 'id' => $id ] );
});

// Event date
add_action( 'ovaev_loop_date_event', function( $id = '' ) {
    return ovaev_get_template( 'loop/date_event.php', [ 'id' => $id ] );
});

// Read more
add_action( 'ovaev_loop_readmore', function( $id = '' ) {
    return ovaev_get_template( 'loop/readmore.php', [ 'id' => $id ] );
});

add_action( 'ovaev_loop_readmore_2', function( $id = '' ) {
    return ovaev_get_template( 'loop/readmore2.php', [ 'id' => $id ] );
});

// Single Thumbnail
add_action( 'oavev_single_thumbnail', function() {
    return ovaev_get_template( 'single/thumbnail.php' );
});

// Single Title
add_action( 'ovaev_single_title', function() {
    return ovaev_get_template( 'single/title.php' );
});

// Single Time Location
add_action( 'oavev_single_time_loc', function() {
    return ovaev_get_template( 'single/time_loc_date_time.php' );
});

add_action( 'oavev_single_time_loc', function() {
    return ovaev_get_template( 'single/time_loc_location.php' );
}, 20 );

// Single Taxonomy Type
add_action( 'oavev_single_type', function() {
    return ovaev_get_template( 'single/type.php' );
});

// Single Booking Links
add_action( 'oavev_single_booking_links', function() {
    return ovaev_get_template( 'single/booking_links.php' );
});

// Single Tags
add_action( 'oavev_single_tags', function() {
    return ovaev_get_template( 'single/tags.php' );
});

// Single Share
add_action( 'oavev_single_share', function() {
    return ovaev_get_template( 'single/share.php' );
});

add_action( 'oavev_single_related', function() {
    return ovaev_get_template( 'single/related.php' );
});

add_filter( 'ovaev_share_social', function( $link, $title ) {
    $html = '<ul class="share-social-icons">
        <li><a class="share-ico ico-facebook" target="_blank" href="http://www.facebook.com/sharer.php?u='.esc_url( $link ).'"><i class="fab fa-facebook-square"></i></a></li>
        <li><a class="share-ico ico-twitter" target="_blank" href="https://twitter.com/share?url='.esc_url( $link ).'&amp;text='.urlencode($title).'"><i class="fab fa-twitter"></i></a></li>
        <li><a class="share-ico ico-pinterest" target="_blank" href="http://www.pinterest.com/pin/create/button/?url='.esc_url( $link ).'"><i class="fab fa-pinterest"></i></a></li>
        <li><a class="share-ico ico-linkedin" target="_blank" href="http://www.linkedin.com/shareArticle?mini=true&url='.esc_url( $link ).'"><i class="fab fa-linkedin-in"></i></a></li>           
    </ul>';

    return apply_filters( 'ovaev_share_social_html', $html, $link, $title );
}, 10, 2 );

add_filter( 'tripgo_header_customize', function( $header ) {
    if ( is_tax( 'event_category' ) || get_query_var( 'event_type' ) != '' || is_tax( 'event_tag' ) ||  get_query_var( 'event_tag' ) != '' || is_post_type_archive( 'event' ) ) {
        $header = OVAEV_Settings::archive_event_header();
    } elseif ( is_singular( 'event' ) ) {
        $header = OVAEV_Settings::single_event_header();
    }

    return $header;
});

add_filter( 'tripgo_footer_customize', function( $footer ) {
    if ( is_tax( 'event_category' ) || get_query_var( 'event_type' ) != '' || is_tax( 'event_tag' ) ||  get_query_var( 'event_tag' ) != '' || is_post_type_archive( 'event' ) ) {
        $footer = OVAEV_Settings::archive_event_footer();
    } elseif ( is_singular( 'event' ) ) {
        $footer = OVAEV_Settings::single_event_footer();
    }

    return $footer;
});