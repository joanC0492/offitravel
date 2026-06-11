<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get event id
$id = get_the_id();
if ( !$id ) return;

?>
<div class="ovaev-content">
	<div class="type4">
		<?php do_action( 'ovaev_loop_highlight_date_3', $id ); ?>
		<div class="desc">
			<div class="event_post">
				<?php do_action( 'ovaev_loop_title', $id ); ?>
				<div class="time-event">
					<?php do_action( 'ovaev_loop_date_event', $id ); ?>
					<?php do_action( 'ovaev_loop_venue', $id ); ?>
				</div>
				<?php do_action( 'ovaev_loop_readmore_2', $id ); ?>
			</div>
		</div>
	</div>
</div>