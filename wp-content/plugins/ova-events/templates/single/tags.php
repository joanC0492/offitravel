<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get event id
$event_id = get_the_ID();
if ( !$event_id ) return;

ovaev_get_tag_event_by_id( $event_id );

?>