<?php
/**
 * Include and setup custom metaboxes and fields. (make sure you copy this file to outside the CMB2 directory)
 *
 * Be sure to replace all instances of 'yourprefix_' with your destination's prefix.
 * http://nacin.com/2010/05/11/in-wordpress-prefix-everything/
 *
 * @category YourThemeOrPlugin
 * @package  Demo_CMB2
 * @license  http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link     https://github.com/WebDevStudios/CMB2
 */

add_action( 'cmb2_init', function() {
    // Start with an underscore to hide fields from custom fields list
    $prefix = 'ova_destination_met_';
    
    // Destination Settings
    $destination_settings = new_cmb2_box([
        'id'            => 'ova_destination_settings',
        'title'         => esc_html__( 'Destination Settings', 'ova-destination' ),
        'object_types'  => [ 'destination' ], // Post type
        'context'       => 'normal',
        'priority'      => 'high',
        'show_names'    => true
    ]);

    // Short Description
    $destination_settings->add_field([
        'name'    => esc_html__( 'Short Description', 'ova-destination' ),
        'id'      => $prefix.'short_desc',
        'type'    => 'wysiwyg'
    ]);

    // Sights
    $destination_settings->add_field([
        'name'          => esc_html__( 'Sights', 'ova-destination' ),
        'id'            => $prefix.'sights',
        'type'          => 'file_list',
        'description'   => esc_html__( 'Use in Destination Detail', 'ova-destination' )
    ]);

    // Map
    $destination_settings->add_field([
        'name'          => 'Location',
        'desc'          => 'Drag the marker to set the exact location',
        'id'            => $prefix.'map',
        'type'          => 'pw_map',
        'split_values'  => true, // Save latitude and longitude as two separate fields
        'desc'          => esc_html__( 'Drag the marker to set the exact location ( Location Only Display in Destination Detail Template 1 )', 'ova-destination' )
    ]);

    // Tour details
    $group_tour_details = $destination_settings->add_field([
        'id'          => $prefix.'tour_details',
        'type'        => 'group',
        'description' => esc_html__( 'Tour details', 'ova-destination' ),
        'options'     => [
            'group_title'   => esc_html__( 'Tour details', 'ova-destination' ), 
            'add_button'    => esc_html__( 'Add Tour details', 'ova-destination' ),
            'remove_button' => esc_html__( 'Remove', 'ova-destination' ),
            'sortable'      => true
        ]
    ]);

    $destination_settings->add_group_field( $group_tour_details, [
        'name' => esc_html__( 'Title', 'ova-destination' ),
        'id'   => $prefix.'tour_details_title',
        'type' => 'text'
    ]);       
    
    $destination_settings->add_group_field( $group_tour_details, [
        'name' => esc_html__( 'Content', 'ova-destination' ),
        'id'   => $prefix.'tour_details_content',
        'type' => 'text'
    ]);

    // Info
    $destination_settings->add_field([
        'name'  => esc_html__( 'Info', 'ova-destination' ),
        'id'    => $prefix.'info',
        'type'  => 'wysiwyg'
    ]);
    
    // Sort order
    $destination_settings->add_field([
        'name'      => esc_html__( 'Sort Order', 'ova-destination' ),
        'id'        => $prefix.'order_destination',
        'desc'      => esc_html__( 'Insert Number', 'ova-destination' ),
        'type'      => 'text',
        'default'   => '1'
    ]);
});