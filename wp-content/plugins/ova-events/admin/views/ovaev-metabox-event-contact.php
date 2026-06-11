<?php if ( !defined( 'ABSPATH' ) ) exit();

// Global post
global $post;

// Get event data
$ovaev_organizer = get_post_meta( $post->ID, 'ovaev_organizer', true );
$ovaev_phone     = get_post_meta( $post->ID, 'ovaev_phone', true );
$ovaev_email     = get_post_meta( $post->ID, 'ovaev_email', true );
$ovaev_website   = get_post_meta( $post->ID, 'ovaev_website', true );

?>
<div class="ovaev_metabox">
	<br>
	<div class="ovaev_row">
		<label class="label">
			<strong>
				<?php esc_html_e( 'Organizer Name:', 'oavev' ); ?>
			</strong>
		</label>
		<input
			type="text"
			id="ovaev_organizer"
			name="ovaev_organizer"
			value="<?php echo esc_attr( $ovaev_organizer ); ?>"
			placeholder="<?php esc_html_e( 'Ovatheme Company', 'ovaev' ); ?>"
			autocomplete="off"
		/>
	</div>
	<br>
	<div class="ovaev_row">
		<label class="label">
			<strong>
				<?php esc_html_e( 'Phone:', 'oavev' ); ?>
			</strong>
		</label>
		<input
			type="text"
			id="ovaev_phone"
			name="ovaev_phone"
			value="<?php echo esc_attr( $ovaev_phone ); ?>"
			placeholder="<?php esc_html_e( '0123456789', 'ovaev' ); ?>"
			autocomplete="off"
		/>
	</div>
	<br>
	<div class="ovaev_row">
		<label class="label">
			<strong>
				<?php esc_html_e( 'Email:', 'oavev' ); ?>
			</strong>
		</label>
		<input
			type="text"
			id="ovaev_email"
			name="ovaev_email"
			value="<?php echo esc_attr( $ovaev_email ); ?>"
			placeholder="<?php esc_html_e( 'info@company.com', 'ovaev' ); ?>"
			autocomplete="off"
		/>
	</div>
	<br>
	<div class="ovaev_row">
		<label class="label">
			<strong>
				<?php esc_html_e( 'Website:', 'oavev' ); ?>
			</strong>
		</label>
		<input
			type="text"
			id="ovaev_website"
			name="ovaev_website"
			value="<?php echo esc_attr( $ovaev_website ); ?>"
			placeholder="<?php esc_html_e( 'https://ovatheme.com', 'ovaev' ); ?>"
			autocomplete="off"
		/>
	</div>
	<br>
</div>
<?php wp_nonce_field( 'ovaev_nonce', 'ovaev_nonce' ); ?>