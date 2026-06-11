<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class OVAEV_Elementor_Events_Simple_Calendar
 */
if ( !class_exists( 'OVAEV_Elementor_Events_Simple_Calendar' ) ) {

	class OVAEV_Elementor_Events_Simple_Calendar extends \Elementor\Widget_Base {

		/**
		 * Get widget name
		 */
		public function get_name() {		
			return 'ova_events_simple_calendar';
		}

		/**
		 * Get widget title
		 */
		public function get_title() {
			return esc_html__( 'Simple Calendar', 'ovaev' );
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
		 * Get script depends
		 */
		public function get_script_depends() {
			return [ 'ova-moment', 'ova-clndr', 'ovaev-elementor-simple-calendar' ];
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
							'special_event'  => esc_html__( 'Special Event', 'ovaev' ),					
						],
					]
				);

				$this->add_control(
					'days_of_the_week',
					[
						'label' 	=> __( 'Days Of The Week', 'ovaev' ),
						'type' 		=> \Elementor\Controls_Manager::TEXT,
						'default' 	=> esc_html__( 'S|M|T|W|T|F|S', 'ovaev' ),
					]
				);
				
			$this->end_controls_section(); // END

			$this->start_controls_section(
				'content_section',
				[
					'label' => esc_html__( 'Style', 'ovaev' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_control(
					'today_bg',
					[
						'label' 	=> esc_html__( 'Background Today', 'ovaev' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .cal1 .clndr .clndr-table tr .day.today.event' => 'background-color: {{VALUE}}',
						],
					]
				);

				$this->add_control(
					'today_color',
					[
						'label' 	=> esc_html__( 'Today Color', 'ovaev' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .cal1 .clndr .clndr-table tr .day.today.event .day-contents' => 'color: {{VALUE}} !important',
						],
					]
				);

				$this->add_control(
					'event_bg',
					[
						'label' 	=> esc_html__( 'Background Event', 'ovaev' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .cal1 .clndr .clndr-table tr .day.event' => 'background-color: {{VALUE}}',
						],
					]
				);

				$this->add_control(
					'event_color',
					[
						'label' 	=> esc_html__( 'Event Color', 'ovaev' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .cal1 .clndr .clndr-table tr .day.event .day-contents' => 'color: {{VALUE}} !important',
						],
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
			$template = apply_filters( 'ovaev_elementor_simple_calendar_template', 'elements/ovaev_events_simple_calendar.php' );

			ob_start();
			ovaev_get_template( $template, $settings );
			echo ob_get_clean();
		}
	}

	// Register new widget
	$widgets_manager->register( new OVAEV_Elementor_Events_Simple_Calendar() );
}