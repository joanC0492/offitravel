<?php if ( !defined( 'ABSPATH' ) ) exit(); 

// Get event id
$id = get_the_id();
if ( !$id ) return;

?>
<div class="ovaev-content">
	<div class="item">
		<?php do_action( 'ovaev_loop_highlight_date_1', $id ); ?>
		<div class="desc">
			<?php do_action( 'ovaev_loop_thumbnail_list', $id ); ?>
			<div class="event_post">
				<?php do_action( 'ovaev_loop_title', $id ); ?>
				<div class="time-event">
					<?php do_action( 'ovaev_loop_date_event', $id ); ?>
					<?php do_action( 'ovaev_loop_venue', $id ); ?>
				</div>
			</div>
		</div>
	</div>
</div>