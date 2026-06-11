<?php defined( 'ABSPATH' ) || exit;

/**
 * Class OVAEV_Shortcode_Events_Simple_Calendar
 */
if ( !class_exists( 'OVAEV_Shortcode_Events_Simple_Calendar' ) ) {

	class OVAEV_Shortcode_Events_Simple_Calendar {

		/**
		 * Shortcode name
		 */
		public $shortcode = 'ovaev_calendar';

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
			// Get content
			$content = get_the_content( get_the_ID() );

			// Check shortcode
			if ( is_page() && has_shortcode( $content, 'ovaev_calendar') ) {
				wp_enqueue_script( 'ova-moment', OVAEV_PLUGIN_URI. 'assets/libs/calendar/moment.min.js', [ 'jquery' ], false, true );
				wp_enqueue_script( 'ova-clndr', OVAEV_PLUGIN_URI.'assets/libs/calendar/clndr.min.js',  [ 'jquery' ], false, true );
			}
			
			// Check variable shortcode
			if ( !empty($args) ) {
				$attr = [
					'category' 		=> isset( $args['category'] ) ? $args['category'] : 'all',
					'filter_event' 	=> isset( $args['filter_event'] ) ? $args['filter_event'] : 'all',
				];
			} else {
				$attr = [
					'category' 		=> 'all',
					'filter_event' 	=> 'all',
				];
			}

			// Get template
			$template = apply_filters( 'shortcode_ovaev_simple_calendar', 'elements/ovaev_events_simple_calendar.php' );

			ob_start();
			ovaev_get_template( $template, $attr );
			return ob_get_clean();
		}
	}

	// init class
	new OVAEV_Shortcode_Events_Simple_Calendar();
}