<?php if ( !defined( 'ABSPATH' ) ) exit();

/**
 * Class OVAEV_Metaboxes_Render_Event
 */
if ( !class_exists( 'OVAEV_Metaboxes_Render_Event' ) ) {

	class OVAEV_Metaboxes_Render_Event {

		/**
		 * Render
		 */
		public static function render() {
			require_once( OVAEV_PLUGIN_PATH. '/admin/views/ovaev-metabox-event.php' );
		}

		/**
		 * Save
		 */
		public static function save( $post_id, $post_data ) {
			if ( empty( $post_data ) ) exit();

			// Checked special
			if ( array_key_exists( 'ovaev_special', $post_data ) == false ) {
				$post_data['ovaev_special'] = '';
			} else {
				$post_data['ovaev_special'] = 'checked';
			}

			// Check gallery exits
			if ( !isset( $post_data['ovaev_gallery_id'] ) || empty( $post_data['ovaev_gallery_id'] ) ) {
				$post_data['ovaev_gallery_id'] = '';
			}

			// Start date
			if ( isset( $post_data['ovaev_start_date'] ) && !empty( $post_data['ovaev_start_date'] ) ) {
				$post_data['ovaev_start_date_time'] = strtotime( $post_data['ovaev_start_date'] . ' ' . $post_data['ovaev_start_time'] );
			} else {
				$post_data['ovaev_start_date_time'] = '';
			}

			// End date
			if ( isset( $post_data['ovaev_end_date'] ) && !empty( $post_data['ovaev_end_date'] ) ) {
				$post_data['ovaev_end_date_time'] = strtotime( $post_data['ovaev_end_date'] . ' ' . $post_data['ovaev_end_time'] );
			} else {
				$post_data['ovaev_end_date_time'] = '';
			}

			// Update post meta
			foreach ( $post_data as $key => $value ) {
				update_post_meta( $post_id, $key, $value );
			}
		}
	}
} // END if