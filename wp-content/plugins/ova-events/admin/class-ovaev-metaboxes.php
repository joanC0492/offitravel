<?php if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Class OVAEV_Metaboxes
 */
if ( !class_exists( 'OVAEV_Metaboxes' ) ) {

	class OVAEV_Metaboxes {

		/**
		 * Constructor
		 */
		public function __construct() {
			// Require
			$this->require_metabox();

			// Add meta boxes
			add_action( 'add_meta_boxes', [ $this, 'add_metabox' ] );
			add_action( 'save_post', [ $this, 'save_metabox' ] );

			// Save
			add_action( 'ovaev_update_meta_event', [ 'OVAEV_Metaboxes_Render_Event' ,'save' ], 10, 2 );

			// Category
			add_action( 'event_category_add_form_fields', [ $this, 'taxonomy_add_new_meta_field' ] );
			add_action( 'event_category_edit_form_fields', [ $this, 'OVAEV_taxonomy_edit_meta_field' ] );
			add_action( 'edited_event_category', [ $this, 'taxonomy_save_meta_field' ] );
			add_action( 'create_event_category', [ $this, 'taxonomy_save_meta_field' ] );
		}

		/**
		 * include meta boxes
		 */
		public function require_metabox() {
			require_once( OVAEV_PLUGIN_PATH.'admin/meta-boxes/ovaev-metaboxes-event.php' );
		}

		/**
		 * Add metabox
		 */
		public function add_metabox() {
			add_meta_box( 'ovaev-metabox-settings-event',
				'Events',
				[ 'OVAEV_Metaboxes_Render_Event', 'render' ],
				'event',
				'normal',
				'high'
			);
		}

		/**
		 * Save metabox
		 */
		public function save_metabox( $post_id ) {
			// Bail if we're doing an auto save
			if ( empty( $_POST ) && defined( 'DOING_AJAX' ) && DOING_AJAX ) return;

			// if our nonce isn't there, or we can't verify it, bail
			if ( !isset( $_POST['ovaev_nonce'] ) || !wp_verify_nonce( $_POST['ovaev_nonce'], 'ovaev_nonce' ) ) return;

			do_action( 'ovaev_update_meta_event', $post_id, $_POST );
		}

		/**
		 * Add new meta field
		 */
		public function taxonomy_add_new_meta_field( $array ) { ?>
			<div class="form-field ovaev-icon-class-wrap">
				<label for="ovaev-icon-class">
					<?php esc_html_e( 'Icon Class', 'ovaev' ); ?>
				</label>
				<input
					type="text"
					id="ovaev-icon-class"
					name="ovaev_icon_class"
					value=""
					size="40"
					aria-describedby="icon-class-description"
				/>
				<p class="description" id="icon-class-description">
					<?php esc_html_e( 'Applies to [ovaev_event_filter] shortcode', 'ovaev' ); ?>
				</p>
			</div>
			<?php
		}

		/**
		 * Edit meta field
		 */
		public function OVAEV_taxonomy_edit_meta_field( $term ) {
			// Get term id
			$term_id = $term->term_id;

			// icon class
			$icon_class = get_term_meta( $term_id, 'ovaev_icon_class', true );

			?>
			<tr class="form-field ovaev-icon-class-wrap">
				<th scope="row">
					<label for="ovaev-icon-class">
						<?php esc_html_e( 'Icon Class', 'ovaev' ); ?>
					</label>
				</th>
				<td>
					<input
						id="ovaev-icon-class"
						type="text"
						name="ovaev_icon_class"
						value="<?php echo esc_attr( $icon_class ); ?>"
						size="40"
						aria-describedby="icon-class-description"
					/>
					<p class="description" id="icon-class-description">
						<?php esc_html_e( 'Applies to [ovaev_event_filter] shortcode', 'ovaev' ); ?>
					</p>
				</td>
			</tr>
			<?php
		}

		/**
		 * Save meta field
		 */
		public function taxonomy_save_meta_field( $term_id ) {
			$icon_class = isset( $_REQUEST['ovaev_icon_class'] ) ? $_REQUEST['ovaev_icon_class'] : '';
    		update_term_meta( $term_id, 'ovaev_icon_class', $icon_class );
		}
	}

	// init class
	new OVAEV_Metaboxes();
}
?>