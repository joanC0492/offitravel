<?php
/**
 * The steps tabs
 *
 * @package WPMultiStepCheckout
 */

defined( 'ABSPATH' ) || exit;

$i                  = 0;
$number_of_steps    = ( $show_login_step && $options['show_login_step'] ) ? count( $steps ) + 1 : count( $steps );
$current_step_title = ( $show_login_step ) ? 'login' : key( array_slice( $steps, 0, 1, true ) );

do_action( 'wpmc_before_tabs' );

?>

<!-- The steps tabs -->
<div class="wpmc-tabs-wrapper">
	<ul class="wpmc-tabs-list wpmc-<?php esc_attr_e( $number_of_steps ); ?>-tabs" data-current-title="<?php esc_attr_e( $current_step_title ); ?>">
	<?php if ( $show_login_step && $options['show_login_step'] ) : ?>
		<li class="wpmc-tab-item current wpmc-login" data-step-title="login">
			<div class="wpmc-tab-number"><?php esc_html_e( $i = $i + 1 ); ?></div>
			<div class="wpmc-tab-text"><?php esc_html_e( $options['t_login'] ); ?></div>
		</li>
	<?php endif; ?>
	<?php
	foreach ( $steps as $_id => $_step ) :
		$class = ( ! ( $show_login_step && $options['show_login_step'] ) && $i == 0 ) ? ' current' : '';
		?>
		<li class="wpmc-tab-item<?php esc_attr_e( $class ); ?> wpmc-<?php esc_attr_e( $_id ); ?>" data-step-title="<?php esc_attr_e( $_id ); ?>">
			<div class="wpmc-tab-number"><?php esc_html_e( $i = $i + 1 ); ?></div>
			<div class="wpmc-tab-text"><?php esc_html_e( $_step['title'] ); ?></div>
		</li>
	<?php endforeach; ?>
	</ul>
</div>
