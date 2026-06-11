<?php if ( !defined( 'ABSPATH' ) ) exit(); 

// Get event id
if ( isset( $args['id'] ) ) {
	$id = $args['id'];
} else {
	$id = get_the_id();	
}
if ( !$id ) return;

// Value event type
$value_event_type = [];

// Get event type
$event_type = get_the_terms( $id, 'event_category') ? get_the_terms( $id, 'event_category') : '' ;
if ( !empty( $event_type ) && is_array( $event_type ) ) {
	foreach ( $event_type as $value ) {
		$value_event_type[] = '<a class="event_type" href="'.get_term_link( $value->term_id ).'">' .$value->name. '</a>' ;
		
	}
}

if ( !empty( $value_event_type ) && is_array( $value_event_type ) ): ?>
	<div class="post_cat">
		<?php echo implode( ' ', $value_event_type ); ?>
	</div>
<?php endif;