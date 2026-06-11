<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get latitude
$latitude = $this->get_meta_value( 'latitude' );
if ( !$latitude ) {
    $latitude = ovabrw_get_option_setting( 'latitude_map_default', 39.177972 );
}

// Get longitude
$longitude = $this->get_meta_value( 'longitude' );
if ( !$longitude ) {
	$longitude = ovabrw_get_option_setting( 'longitude_map_default', -100.36375 );
}

// Map name
woocommerce_wp_text_input([
    'type'  => 'hidden',
    'id'    => $this->get_meta_name( 'map_name' ),
    'class' => 'map_name',
    'label' => esc_html__( 'Map name', 'ova-brw' ),
    'value' => $this->get_meta_value( 'map_name' )
]);

// Address
woocommerce_wp_text_input([
    'type'  => 'hidden',
    'id'    => $this->get_meta_name( 'address' ),
    'class' => 'address',
    'label' => esc_html__( 'Address', 'ova-brw' ),
    'value' => $this->get_meta_value( 'address' ),
]);

// Latitude
woocommerce_wp_text_input([
    'type'  => 'hidden',
    'id'    => $this->get_meta_name( 'latitude' ),
    'class' => 'latitude',
    'label' => esc_html__( 'Latitude', 'ova-brw' ),
    'value' => $latitude
]);

// Longitude
woocommerce_wp_text_input([
    'type'  => 'hidden',
    'id'    => 'ovabrw_longitude',
    'class' => 'longitude',
    'label' => esc_html__( 'Longitude', 'ova-brw' ),
    'value' => $longitude
]);
