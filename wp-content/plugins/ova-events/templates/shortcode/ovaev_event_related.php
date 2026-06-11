<?php if ( !defined( 'ABSPATH' ) ) exit(); 

// Get event id
$id = isset( $args['id'] ) ? $args['id'] : '';

// Get class
$class = isset( $args['class'] ) ? $args['class'] : '';

// Get event related
$related = ovaev_get_event_related_by_id( $id );
if ( $related->have_posts() ): ?>
	<div class="single_event ovaev-shortcode-related<?php echo ' '.esc_html( $class ); ?>">
		<div class="content-event">
			<div class="event-related">
		        <h3 class="related-event">
		        	<?php esc_html_e( 'Related Events', 'ovaev' ); ?>
		        </h3>
	        	<div class="archive_event">
		    		<?php if ( $related->have_posts() ) : while ( $related->have_posts() ) : $related->the_post();
		        		ovaev_get_template( 'event-templates/event-type2.php' );
		        	endwhile; endif; wp_reset_postdata(); ?>
	        	</div>
		    </div>
		</div>
	</div>
<?php endif;