<?php if ( !defined( 'ABSPATH' ) ) exit();

// Global
global $post;

?>
<div class="row">
	<div id="tabs">
		<ul>
			<li>
				<a href="#metabox-event-basic">
					<?php esc_html_e( 'Basic', 'ovaev' ); ?>
				</a>
			</li>
			<li>
				<a href="#metabox-event-contact" class="">
					<?php esc_html_e( 'Contact Details', 'ovaev' ); ?>
				</a>
			</li>
			<li>
				<a href="#metabox-event-gallery" class="">
					<?php esc_html_e( 'Gallery', 'ovaev' ); ?>
				</a>
			</li>
		</ul>
		<div id="metabox-event-basic">
			<?php require_once( OVAEV_PLUGIN_PATH.'/admin/views/ovaev-metabox-event-basic.php' ); ?>
		</div>
		<div id="metabox-event-contact">
			<?php require_once( OVAEV_PLUGIN_PATH.'/admin/views/ovaev-metabox-event-contact.php' ); ?>
		</div>
		<div id="metabox-event-gallery">
			<?php require_once( OVAEV_PLUGIN_PATH.'/admin/views/ovaev-metabox-event-gallery.php' ); ?>
		</div>
	</div>
	<br/> 
</div>
<div id="dialogs"></div>
<?php wp_nonce_field( 'ovaev_nonce', 'ovaev_nonce' ); ?>