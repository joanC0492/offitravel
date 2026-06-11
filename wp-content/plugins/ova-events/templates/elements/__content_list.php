<?php if ( !defined( 'ABSPATH' ) ) exit;

// Get event id
$id = get_the_ID();
if ( !$id ) return;

// Get event start date
$ovaev_start_date = get_post_meta( $id, 'ovaev_start_date_time', true );

// Get event end date
$ovaev_end_date = get_post_meta( $id, 'ovaev_end_date_time', true );

// Convert start date
$start_date = $ovaev_start_date != '' ? date_i18n( get_option( 'date_format' ), $ovaev_start_date ) : '';

// Convert start time
$start_time = $ovaev_start_date != '' ? date_i18n( get_option( 'time_format' ), $ovaev_start_date ) : '';

// Convert end date
$end_date = $ovaev_end_date != '' ? date_i18n( get_option( 'date_format' ), $ovaev_end_date ) : '';

// Convert end time
$end_time = $ovaev_end_date != '' ? date_i18n( get_option( 'time_format' ), $ovaev_end_date ) : '';

?>
<div class="item">
	<h3 class="title">
		<a class="second_font" href="<?php echo get_the_permalink( $id ); ?>">
			<?php echo get_the_title( $id ); ?>
		</a>
	</h3>
	<div class="time-event">
		<?php if ( $start_date === $end_date && $end_date != '' && $start_date != '' ): ?>
			<div class="time">
				<span><?php echo esc_html( $start_date ); ?> @ <?php echo esc_html( $start_time ); ?> - <?php echo esc_html( $time_end ); ?></span>
			</div>
		<?php elseif ( $start_date != $end_date && $end_date && $start_date != '' ): ?>
			<div class="time">
				<span><?php echo esc_html( $start_date ); ?> - <?php echo esc_html( $end_date ); ?></span>
			</div>
		<?php endif; ?>
	</div>
</div>