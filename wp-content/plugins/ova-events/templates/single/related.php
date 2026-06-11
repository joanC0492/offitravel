<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get event id
$event_id = get_the_ID();
if ( !$event_id ) return;

// Get event related
$event_related = ovaev_get_event_related_by_id( $event_id );
if ( $event_related->have_posts() ): ?>
    <div class="event-related">
		<?php if ( $event_related->have_posts() ) : while ( $event_related->have_posts() ) : $event_related->the_post();		   
        	ovaev_get_template( 'event-templates/event-type2.php' );
    	endwhile; endif; wp_reset_postdata(); ?>
    </div>
<?php endif;