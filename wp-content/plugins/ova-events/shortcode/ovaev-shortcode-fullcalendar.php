<?php defined( 'ABSPATH' ) || exit;

/**
 * Class OVAEV_Shortcode_Event_Calendar
 */
if ( !class_exists( 'OVAEV_Shortcode_Event_Calendar' ) ) {

	class OVAEV_Shortcode_Event_Calendar {

		/**
		 * Shortcode name
		 */
		public $shortcode = 'ovaev_fullcalendar';

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
			if ( is_page() && has_shortcode( $content, 'ovaev_fullcalendar') ) {
				wp_enqueue_script( 'ova-moment', OVAEV_PLUGIN_URI. 'assets/libs/calendar/moment.min.js', [ 'jquery' ], false, true );
				wp_enqueue_script( 'ova-clndr', OVAEV_PLUGIN_URI.'assets/libs/calendar/clndr.min.js', [ 'jquery' ], true, false );
			}
			
			// Check variable shortcode
			if ( !empty($args) ) {
				$attr = [
					'category' 		=> isset( $args['category'] ) ? $args['category'] : 'all',
					'filter_event' 	=> isset( $args['filter_event'] ) ? $args['filter_event'] : 'all',
					'show_filter' 	=> isset( $args['show_filter'] ) ? $args['show_filter'] : 'no',
				];
			} else {
				$attr = [
					'category' 		=> 'all',
					'filter_event' 	=> 'all',
					'show_filter' 	=> 'no',
				];
			}

			// Get template
			$template = apply_filters( 'shortcode_ovaev_simple_fullcalendar', 'elements/ovaev_events_calendar_content.php' );

			ob_start();
			ovaev_get_template( $template, $attr );
			return ob_get_clean();
		}
	}

	// init class
	new OVAEV_Shortcode_Event_Calendar();
}