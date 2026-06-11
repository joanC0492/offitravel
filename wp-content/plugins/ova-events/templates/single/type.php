<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get event id
$event_id = get_the_ID();
if ( !$event_id ) return;

?>
<div class="ovaev-category">
	<?php ovaev_get_category_event_by_id( $event_id ); ?>
</div>