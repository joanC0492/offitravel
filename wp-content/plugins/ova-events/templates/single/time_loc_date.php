<?php if ( !defined( 'ABSPATH' ) ) exit(); 

// Get event id
$event_id = get_the_ID();
if ( !$event_id ) return;

// Get event start date
$ovaev_start_date = get_post_meta( $event_id, 'ovaev_start_date_time', true );

// Get event end date
$ovaev_end_date = get_post_meta( $event_id, 'ovaev_end_date_time', true );

// Convert start date
$start_date = $ovaev_start_date != '' ? date_i18n( get_option( 'date_format' ), $ovaev_start_date ) : '';

// Convert end date
$end_date = $ovaev_end_date != '' ? date_i18n( get_option( 'date_format' ), $ovaev_end_date ) : '';

if ( $start_date && $end_date ): ?>
	<div class="wrap-date wrap-pro">
		<?php if ( $start_date == $end_date && $start_date != '' && $end_date != '' ): ?>
			<i class="icomoon icomoon-clock"></i>
			<span class="second_font general-content"><?php echo esc_html( $start_date ); ?></span>
		<?php elseif ( $start_date != $end_date && $start_date != '' && $end_date != '' ): ?>
			<i class="icomoon icomoon-clock"></i>
			<span class="second_font general-content"><?php echo esc_html( $start_date ); ?></span>
			<span class="second_font general-content"> - <?php echo esc_html( $end_date ); ?></span>
		<?php endif; ?>
	</div>
<?php endif;