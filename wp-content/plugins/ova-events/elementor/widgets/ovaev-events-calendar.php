<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class OVAEV_Elementor_Events_Calendar
 */
if ( !class_exists( 'OVAEV_Elementor_Events_Calendar' ) ) {

	class OVAEV_Elementor_Events_Calendar extends \Elementor\Widget_Base {

		/**
		 * Get widget name
		 */
		public function get_name() {		
			return 'ova_events_calendar';
		}

		/**
		 * Get widget title
		 */
		public function get_title() {
			return esc_html__( 'Event Calendar', 'ovaev' );
		}

		/**
		 * Get widget icon
		 */
		public function get_icon() {
			return 'eicon-posts-grid';
		}

		/**
		 * Get widget categories
		 */
		public function get_categories() {
			return [ 'ovatheme' ];
		}

		/**
		 * Get stype depends
		 */
		public function get_style_depends() {
			return [ 'ova-calendar' ];
		}

		/**
		 * Get script depends
		 */
		public function get_script_depends() {
			return [ 'ova-moment', 'ova-clndr', 'ovaev-elementor-calendar' ];
		}

		/**
		 * Register controls
		 */
		protected function register_controls() {
			// List category
			$list_category = [
				'all' => esc_html__( 'All categories', 'ovaev' )
			];

			// Get categories
			$categories = get_categories([
				'taxonomy' 	=> 'event_category',
	           	'orderby' 	=> 'name',
	           	'order'   	=> 'ASC'
			]);
			if ( !empty( $categories ) && is_array( $categories ) ) {
				foreach ( $categories as $category ) {
					$list_category[$category->slug] = $category->cat_name;
				}
			}
		   
			$this->start_controls_section(
				'section_content',
				[
					'label' => esc_html__( 'Content', 'ovaev' ),
				]
			);

				$this->add_control(
					'category',
					[
						'label'   => esc_html__( 'Category', 'ovaev' ),
						'type'    => \Elementor\Controls_Manager::SELECT,
						'default' => 'all',
						'options' => $list_category
					]
				);

				$this->add_control(
					'filter_event',
					[
						'label'   => esc_html__( 'Filter Event', 'ovaev' ),
						'type'    => \Elementor\Controls_Manager::SELECT,
						'default' => 'all',
						'options' => [
							'all'            => esc_html__( 'All', 'ovaev' ),
							'past_event' 	 => esc_html__( 'Past Event', 'ovaev' ),
							'upcoming_event' => esc_html__( 'Upcoming Event', 'ovaev' ),
							'special_event'  => esc_html__( 'Special Event', 'ovaev' )				
						]
					]
				);

				$this->add_control(
					'show_filter',
					[
						'label'   => esc_html__( 'Show Filter Event', 'ovaev' ),
						'type'    => \Elementor\Controls_Manager::SWITCHER,
						'default' => 'no',
						'options' => [
							'yes' => esc_html__( 'Yes', 'ovaev' ),
							'no'  => esc_html__( 'No', 'ovaev' ),
						],
						'frontend_available' => true,
					]
				);

			$this->end_controls_section(); // END
		}

		/**
		 * Render HTML
		 */
		protected function render() {
			// Get settings
			$settings = $this->get_settings_for_display();

			// Get template
			$template = apply_filters( 'ovaev_elementor_calendar_template', 'elements/ovaev_events_calendar_content.php' );

			ob_start();
			ovaev_get_template( $template, $settings );
			echo ob_get_clean();

		}
	}

	// Register new widget
	$widgets_manager->register( new OVAEV_Elementor_Events_Calendar() );
}