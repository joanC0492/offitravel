<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get event id
$event_id = get_the_ID();
if ( !$event_id ) return;

if ( has_filter( 'ovaev_share_social' ) ): ?>
    <div class="share_social">
    	<?php echo apply_filters('ovaev_share_social', get_the_permalink( $event_id ), get_the_title( $event_id ) ); ?>
    </div>
<?php endif;