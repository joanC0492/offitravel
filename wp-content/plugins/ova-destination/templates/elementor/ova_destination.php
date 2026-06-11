<?php if ( !defined( 'ABSPATH' ) ) exit();
 
 // Get destinations
$destinations  = ovadestination_get_data_destination_el( $args );
$args['flag']  = 1;

// Get template
$template = $args['template'];

?>
<div class="ova-destination">
	<div class="content content-<?php echo esc_attr( $template ); ?> content-destination">
		<div class="grid-sizer"></div>
		<?php if ( $destinations->have_posts() ): while ( $destinations->have_posts() ): $destinations->the_post();
			if ( $template === 'template1' ) {
	        	ovadestination_get_template( 'part/item-destination.php', $args );
	        } elseif ( $template === 'template2' ) {
	        	ovadestination_get_template( 'part/item-destination2.php', $args );
	        } elseif ( $template === 'template3' ) {
	        	ovadestination_get_template( 'part/item-destination3.php', $args );
	        } else {
	        	ovadestination_get_template( 'part/item-destination.php', $args );
		    }

		    $args['flag'] += 1;
		endwhile; endif; wp_reset_postdata(); ?>
	</div>
</div>