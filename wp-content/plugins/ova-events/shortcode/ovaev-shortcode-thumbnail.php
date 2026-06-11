<?php defined( 'ABSPATH' ) || exit;

/**
 * Class OVAEV_Shortcode_Event_Thumbnail
 */
if ( !class_exists( 'OVAEV_Shortcode_Event_Thumbnail' ) ) {

	class OVAEV_Shortcode_Event_Thumbnail {

		/**
		 * Shortcode name
		 */
		public $shortcode = 'ovaev_shortcode_thumbnail';

		/**
		 * Constructor
		 */
		public function __construct() {
			add_shortcode( $this->shortcode, [ $this, 'init_shortcode' ] );
		}

		/**
		 * init shortcode
		 */
		public function init_shortcode( $args, $content = null ) {
			if ( !empty( $args ) ) {
				$args = [
					'id' 	=> isset( $args['id'] ) ? (int)$args['id'] : get_the_id(),
					'class' => isset( $args['class'] ) ? $args['class'] : ''
				];
			} else {
				$args = [
					'id' 	=> get_the_id(),
					'class' => ''
				];
			}

			// Get template
			$template = apply_filters( 'shortcode_ovaev_title', 'shortcode/ovaev_event_thumbnail.php' );

			ob_start();
			ovaev_get_template( $template, $args );
			return ob_get_clean();
		}
	}

	// init class
	new OVAEV_Shortcode_Event_Thumbnail();
}