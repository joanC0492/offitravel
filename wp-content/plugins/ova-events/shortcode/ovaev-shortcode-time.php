<?php defined( 'ABSPATH' ) || exit;

/**
 * OVAEV_Shortcode_Event_Time
 */
if ( !class_exists( 'OVAEV_Shortcode_Event_Time' ) ) {

	class OVAEV_Shortcode_Event_Time {

		/**
		 * Shortcode name
		 */
		public $shortcode = 'ovaev_shortcode_time';

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
					'class' => isset( $args['class'] ) ? $args['class'] : '',
					'icon' 	=> isset( $args['icon'] ) ? $args['icon'] : 'far fa-clock'
				];
			} else {
				$args = [
					'id' 	=> get_the_id(),
					'class' => '',
					'icon' 	=> 'far fa-clock'
				];
			}

			// Template
			$template = apply_filters( 'shortcode_ovaev_title', 'shortcode/ovaev_event_time.php' );

			ob_start();
			ovaev_get_template( $template, $args );
			return ob_get_clean();
		}
	}

	// init class
	new OVAEV_Shortcode_Event_Time();
}