<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get event id
$id = get_the_id();
if ( !$id ) return;

?>
<div class="ovaev-content">
	<div class="type6">
		<div class="time-title">
			<?php do_action( 'ovaev_loop_date_event', $id ); ?>
			<?php do_action( 'ovaev_loop_venue', $id ); ?>
			<?php do_action( 'ovaev_loop_title', $id ); ?>
		</div>
		<div class="desc-thumbnail">
			<?php do_action( 'ovaev_loop_thumbnail', $id ); ?>
			<div class="desc">
				<?php do_action( 'ovaev_loop_excerpt', $id ); ?>
				<?php do_action( 'ovaev_loop_readmore', $id ); ?>
			</div>
		</div>
	</div>
</div>