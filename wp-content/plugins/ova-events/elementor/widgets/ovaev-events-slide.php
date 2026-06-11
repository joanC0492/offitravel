<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class OVAEV_Elementor_Events_Slide
 */
if ( !class_exists( 'OVAEV_Elementor_Events_Slide' ) ) {

	class OVAEV_Elementor_Events_Slide extends \Elementor\Widget_Base {

		/**
		 * Get widget name
		 */
		public function get_name() {		
			return 'ova_events_slide';
		}

		/**
		 * Get widget title
		 */
		public function get_title() {
			return esc_html__( 'Events Slide', 'ovaev' );
		}

		/**
		 * Get widget icon
		 */
		public function get_icon() {
			return 'eicon-slider-album';
		}

		/**
		 * Get widget categories
		 */
		public function get_categories() {
			return [ 'ovatheme' ];
		}

		/**
		 * Get style depends
		 */
		public function get_style_depends() {
			return [ 'swiper' ];
		}

		/**
		 * Get script depends
		 */
		public function get_script_depends() {
			return [ 'swiper', 'ovaev-elementor-events-slide' ];
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
					'layout',
					[
						'label'   => esc_html__( 'Layout', 'ovaev' ),
						'type'    => \Elementor\Controls_Manager::SELECT,
						'options' => [
							'1' => esc_html__( 'Layout 1', 'ovaev' ),
							'2' => esc_html__( 'Layout 2', 'ovaev' ),
						],
						'default' => '1',
					]
				);

				$this->add_control(
					'total_count',
					[
						'label'   => esc_html__( 'Post Total', 'ovaev' ),
						'type'    => \Elementor\Controls_Manager::NUMBER,
						'min'     => 1,
						'default' => 8,
					]
				);

				$this->add_control(
					'time_event',
					[
						'label'   => esc_html__( 'Choose time', 'ovaev' ),
						'type'    => \Elementor\Controls_Manager::SELECT,
						'options' => [
							''     	   => esc_html__( 'All', 'ovaev' ),
							'current'  => esc_html__( 'Current', 'ovaev' ),
							'upcoming' => esc_html__( 'Upcoming', 'ovaev' ),
							'past'     => esc_html__( 'Past', 'ovaev' ),
						],
						'default' => '',
					]
				);

				$this->add_control(
					'column',
					[
						'label' 	=> esc_html__( 'Column', 'ovaev' ),
						'type' 		=> \Elementor\Controls_Manager::SELECT,
						'default' 	=> 'two_column',
						'options' 	=> [
							'two_column' 	=> esc_html__( 'Two Columns', 'ovaev' ),
							'three_column' 	=> esc_html__( 'Three Columns', 'ovaev' ),
	 					],
	 					'condition' => [
	 						'version' => 'version_2'
	 					],
					]
				);

				$this->add_control(
					'order_by',
					[
						'label'   => esc_html__( 'Order By', 'ovaev' ),
						'type'    => \Elementor\Controls_Manager::SELECT,
						'default' => 'title',
						'options' => [
							'title'             	=> esc_html__( 'Title', 'ovaev' ),
							'event_custom_sort' 	=> esc_html__( 'Custom Sort', 'ovaev' ),
							'ovaev_start_date_time' => esc_html__( 'Start Date', 'ovaev' ),
							'ID'                	=> esc_html__( 'ID', 'ovaev' ),					
						],
					]
				);

				$this->add_control(
					'order',
					[
						'label'   => esc_html__( 'Order', 'ovaev' ),
						'type'    => \Elementor\Controls_Manager::SELECT,
						'default' => 'DESC',
						'options' => [
							'DESC' => esc_html__( 'Descending', 'ovaev' ),
							'ASC'  => esc_html__( 'Ascending', 'ovaev' ),
							
						]
					]
				);
				
			$this->end_controls_section(); // END

			// Additional Options
			$this->start_controls_section(
				'section_additional_options',
				[
					'label' => esc_html__( 'Additional Options', 'ovaev' ),
				]
			);

				$this->add_control(
					'margin_items',
					[
						'label'   => esc_html__( 'Margin Right Items', 'ovaev' ),
						'type'    => \Elementor\Controls_Manager::NUMBER,
						'default' => 30,
					]
				);

				$this->add_control(
					'item_number',
					[
						'label'       => esc_html__( 'Item Number', 'ovaev' ),
						'type'        => \Elementor\Controls_Manager::NUMBER,
						'description' => esc_html__( 'Number Item', 'ovaev' ),
						'default'     => 3,
					]
				);

				$this->add_control(
					'slides_to_scroll',
					[
						'label'       => esc_html__( 'Slides to Scroll', 'ovaev' ),
						'type'        => \Elementor\Controls_Manager::NUMBER,
						'description' => esc_html__( 'Set how many slides are scrolled per swipe.', 'ovaev' ),
						'default'     => 1,
					]
				);

				$this->add_control(
					'pause_on_hover',
					[
						'label'   => esc_html__( 'Pause on Hover', 'ovaev' ),
						'type'    => \Elementor\Controls_Manager::SWITCHER,
						'default' => 'yes',
						'options' => [
							'yes' => esc_html__( 'Yes', 'ovaev' ),
							'no'  => esc_html__( 'No', 'ovaev' ),
						],
						'frontend_available' => true,
					]
				);

				$this->add_control(
					'infinite',
					[
						'label'   => esc_html__( 'Infinite Loop', 'ovaev' ),
						'type'    => \Elementor\Controls_Manager::SWITCHER,
						'default' => 'yes',
						'options' => [
							'yes' => esc_html__( 'Yes', 'ovaev' ),
							'no'  => esc_html__( 'No', 'ovaev' ),
						],
						'frontend_available' => true,
					]
				);

				$this->add_control(
					'autoplay',
					[
						'label'   => esc_html__( 'Autoplay', 'ovaev' ),
						'type'    => \Elementor\Controls_Manager::SWITCHER,
						'default' => 'yes',
						'options' => [
							'yes' => esc_html__( 'Yes', 'ovaev' ),
							'no'  => esc_html__( 'No', 'ovaev' ),
						],
						'frontend_available' => true,
					]
				);

				$this->add_control(
					'autoplay_speed',
					[
						'label'     => esc_html__( 'Autoplay Speed', 'ovaev' ),
						'type'      => \Elementor\Controls_Manager::NUMBER,
						'default'   => 3000,
						'step'      => 500,
						'condition' => [
							'autoplay' => 'yes',
						],
						'frontend_available' => true,
					]
				);

				$this->add_control(
					'smartspeed',
					[
						'label'   => esc_html__( 'Smart Speed', 'ovaev' ),
						'type'    => \Elementor\Controls_Manager::NUMBER,
						'default' => 500,
					]
				);

				$this->add_control(
					'dot_control',
					[
						'label'   => esc_html__( 'Show Dots', 'ovaev' ),
						'type'    => \Elementor\Controls_Manager::SWITCHER,
						'default' => 'no',
						'options' => [
							'yes' => esc_html__( 'Yes', 'ovaev' ),
							'no'  => esc_html__( 'No', 'ovaev' ),
						],
						'frontend_available' => true,
					]
				);

				$this->add_control(
					'nav_control',
					[
						'label'   => esc_html__( 'Show Nav', 'ovaev' ),
						'type'    => \Elementor\Controls_Manager::SWITCHER,
						'default' => 'yes',
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
			$template = apply_filters( 'ovaev_elementor_events_slide_template', 'elements/ovaev_events_slide.php' );
			ob_start();
			ovaev_get_template( $template, $settings );
			echo ob_get_clean();

		}
	}

	// Register new widget
	$widgets_manager->register( new OVAEV_Elementor_Events_Slide() );
}