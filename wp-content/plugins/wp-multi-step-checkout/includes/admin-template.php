<?php 
/**
 * Admin settings page
 */

defined( 'ABSPATH' ) || exit; 
?>

<h2>Multi-Step Checkout for WooCommerce by <img src="<?php echo esc_url( WMSC_PLUGIN_URL ); ?>assets/images/silkypress_logo.png" /> <a href="https://www.silkypress.com/" target="_blank">SilkyPress</a></h2>

<div class="wrap">

	<h3 class="nav-tab-wrapper woo-nav-tab-wrapper">
		<?php foreach ( $tabs as $_key => $_val ) : ?>
		<?php $active = ( $_key == $tab_current ) ? ' nav-tab-active' : ''; ?>
		<a href="?page=wmsc-settings&tab=<?php echo esc_attr( $_key ) ?>" class="nav-tab<?php echo esc_attr( $active ); ?>"><?php esc_html_e($_val); ?></a>
		<?php endforeach; ?>
	</h3>

	<div class="panel panel-default">
    	<div class="panel-body">
			<div class="row">
				<div id="alert_messages">
				<?php echo $messages; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
    
				<?php if( isset($without_form) && $without_form == true ) : ?>
				<div class="form-group">
					<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
				<?php else : ?>

				<form class="form-horizontal" method="post" action="" id="form_settings">
					<div class="form-group">
						<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

    					<div class="form-group">
	      					<div class="col-lg-6">
    	  						<button type="submit" class="btn btn-primary"><?php esc_html_e('Save changes', 'wp-multi-step-checkout'); ?></button>
							</div>
						</div>
					</div>
				<?php wp_nonce_field( 'wmsc_' . $tab_current ); ?>
				</form>
				<?php endif; ?>
	    	</div>
		</div>
	</div>
</div>
