<?php
require dirname( __DIR__ ) . '/wp-load.php';

$failures = array();

$settings = array(
	'category'      => array( 'internacionales' ),
	'total_count'   => 8,
	'orderby_post'  => 'ova_destination_met_order_destination',
	'order'         => 'DESC',
);

$args = function_exists( 'offitravel_home_destination_slider_product_query_args' )
	? offitravel_home_destination_slider_product_query_args( $settings )
	: null;

if ( ! is_array( $args ) ) {
	$failures[] = 'Expected product query args array.';
} else {
	if ( 'product' !== ( $args['post_type'] ?? '' ) ) {
		$failures[] = 'Expected post_type product.';
	}
	if ( 8 !== (int) ( $args['posts_per_page'] ?? 0 ) ) {
		$failures[] = 'Expected posts_per_page from Elementor total_count.';
	}
	$terms = $args['tax_query'][0]['terms'] ?? array();
	if ( array( 'internacionales' ) !== $terms ) {
		$failures[] = 'Expected selected product_cat slug to be used.';
	}
	if ( 'date' !== ( $args['orderby'] ?? '' ) ) {
		$failures[] = 'Expected old destination custom order to fall back to date for products.';
	}
}

$default_args = function_exists( 'offitravel_home_destination_slider_product_query_args' )
	? offitravel_home_destination_slider_product_query_args( array( 'category' => array(), 'total_count' => 3 ) )
	: null;
$default_terms = is_array( $default_args ) ? ( $default_args['tax_query'][0]['terms'] ?? array() ) : array();
if ( array( 'los-mas-populares' ) !== $default_terms ) {
	$failures[] = 'Expected empty category to default to los-mas-populares.';
}

$all_args = function_exists( 'offitravel_home_destination_slider_product_query_args' )
	? offitravel_home_destination_slider_product_query_args( array( 'category' => array( 'all' ), 'total_count' => 3 ) )
	: null;
if ( is_array( $all_args ) && isset( $all_args['tax_query'] ) ) {
	$failures[] = 'Expected explicit all category to query all products.';
}

$options = function_exists( 'offitravel_home_destination_slider_product_category_options' )
	? offitravel_home_destination_slider_product_category_options()
	: array();
if ( ! isset( $options['all'], $options['internacionales'] ) ) {
	$failures[] = 'Expected product_cat options for Elementor control.';
}

if ( ! shortcode_exists( 'offitravel_product_category_slider' ) ) {
	$failures[] = 'Expected offitravel_product_category_slider shortcode to exist.';
}

$id_args = function_exists( 'offitravel_home_destination_slider_product_query_args' )
	? offitravel_home_destination_slider_product_query_args( array( 'category' => array( '54' ), 'total_count' => 2 ) )
	: null;
$id_terms = is_array( $id_args ) ? ( $id_args['tax_query'][0]['terms'] ?? array() ) : array();
if ( array( 'internacionales' ) !== $id_terms ) {
	$failures[] = 'Expected numeric product_cat IDs to resolve to slugs.';
}

$shortcode_html = do_shortcode( '[offitravel_product_category_slider category="54" total="2"]' );
if ( false === strpos( $shortcode_html, 'ova-destination-nav-right' ) ) {
	$failures[] = 'Expected shortcode wrapper to include original nav positioning class.';
}
if ( false === strpos( $shortcode_html, 'top: -94px !important' ) ) {
	$failures[] = 'Expected shortcode to include cloned nav top style.';
}

if ( $failures ) {
	foreach ( $failures as $failure ) {
		echo "FAIL: {$failure}\n";
	}
	exit( 1 );
}

echo "OK\n";
