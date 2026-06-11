<?php if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Get locate template
 */
if ( !function_exists( 'ovadestination_locate_template' ) ) {
	function ovadestination_locate_template( $template_name = '', $template_path = '', $default_path = '' ) {
		// Set variable to search in ovacoll-templates folder of theme.
		if ( !$template_path ) $template_path = 'ovadestination-templates/';

		// Set default plugin templates path.
		if ( !$default_path ) $default_path = OVADESTINATION_PLUGIN_PATH.'templates/'; // Path to the template folder

		// Search template file in theme folder.
		$template = locate_template([
			$template_path.$template_name
		]);

		// Get plugins template file.
		if ( !$template ) $template = $default_path.$template_name;

		return apply_filters( 'ovadestination_locate_template', $template, $template_name, $template_path, $default_path );
	}
}

/**
 * Include destination template
 */
if ( !function_exists( 'ovadestination_get_template' ) ) {
	function ovadestination_get_template( $template_name = '', $args = [], $tempate_path = '', $default_path = '' ) {
		if ( is_array( $args ) && isset( $args ) ) {
			extract( $args );
		}

		// Get template file
		$template_file = ovadestination_locate_template( $template_name, $tempate_path, $default_path );
		if ( !file_exists( $template_file ) ) {
			_doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', $template_file ), '1.0.0' );
			return;
		}

		include $template_file;
	}
}

/**
 * Add image size
 */
add_action( 'plugins_loaded', function() {
	add_image_size( 'ova_destination_square', 636, 636, true );
	add_image_size( 'ova_destination_thumbnail', 636, 306, true );
	add_image_size( 'ova_destination_square_small', 306, 306, true );
});

/**
 * Get header destination
 */
add_filter( 'tripgo_header_customize', function( $header ) {
	if ( is_tax( 'cat_destination' ) ||  get_query_var( 'cat_destination' ) != '' || is_post_type_archive( 'destination' ) ) {
	  	$header = get_theme_mod( 'header_archive_destination', 'default' );
	} else if( is_singular( 'destination' ) ) {
		$header = get_theme_mod( 'header_single_destination', 'default' );
	}

	return $header;
});

/**
 * Get header background destination
 */
add_filter( 'tripgo_header_bg_customize', function( $bg ) {
	if ( is_tax( 'cat_destination' ) ||  get_query_var( 'cat_destination' ) != '' || is_post_type_archive( 'destination' ) ) {
	  	$bg = get_theme_mod( 'archive_background_destination', '' );
	} else if ( is_singular( 'destination' ) ) {
		$bg = get_theme_mod( 'single_background_destination', '' );

		// Get current id
		$current_id = tripgo_get_current_id();

		// Get header background
        $header_bg_source = get_the_post_thumbnail_url( $current_id, 'full' );
        if ( $header_bg_source ) {
            $bg = $header_bg_source;
        }
	}

	return $bg;
});

/**
 * Get footer destination
 */
add_filter( 'tripgo_footer_customize', function( $footer ) {
	if ( is_tax( 'cat_destination' ) ||  get_query_var( 'cat_destination' ) != '' || is_post_type_archive( 'destination' ) ) {
        $footer = get_theme_mod( 'archive_footer_destination', 'default' );
    } else if ( is_singular( 'destination' ) ) {
        $footer = get_theme_mod( 'single_footer_destination', 'default' );
    }

    return $footer;
});