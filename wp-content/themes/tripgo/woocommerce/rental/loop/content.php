<?php if ( !defined( 'ABSPATH' ) ) exit();
	
if ( 'yes' === tripgo_get_meta_data( 'show_description', $args ) ) {
	the_content();
}