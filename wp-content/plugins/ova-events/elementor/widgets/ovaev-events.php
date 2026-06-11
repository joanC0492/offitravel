<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * OVAEV_Elementor_Events
 */
if ( !class_exists( 'OVAEV_Elementor_Events' ) ) {

	class OVAEV_Elementor_Events extends \Elementor\Widget_Base {

		/**
		 * Get widget name
		 */
		public function get_name() {		
			return 'ova_events';
		}

		/**
		 * Get widget title
		 */
		public function get_title() {
			return esc_html__( 'Events', 'ovaev' );
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
					'total_count',
					[
						'label'   => esc_html__( 'Post Total', 'ovaev' ),
						'type'    => \Elementor\Controls_Manager::NUMBER,
						'min'     => 1,
						'default' => 3,
					]
				);

				$this->add_control(
					'time_event',
					[
						'label'   => esc_html__('Choose time', 'ovaev'),
						'type'    => \Elementor\Controls_Manager::SELECT,
						'options' => [
							''     		=> esc_html__('All','ovaev'),
							'current'  	=> esc_html__('Current','ovaev'),
							'upcoming' 	=> esc_html__('Upcoming','ovaev'),
							'past'     	=> esc_html__('Past','ovaev'),
						],
						'default'   => '',
					]
				);

				$this->add_control(
					'version',
					[
						'label' 	=> esc_html__('Version','ovaev'),
						'type' 		=> \Elementor\Controls_Manager::SELECT,
						'default' 	=> 'version_1',
						'options' 	=> [
							'version_1' => esc_html__( 'List', 'ovaev' ),
							'version_2' => esc_html__( 'Grid', 'ovaev' ),
							'version_3' => esc_html__( 'Templates', 'ovaev' ),
	 					]
					]
				);

				$this->add_control(
					'type_event',
					[
						'label' 	=> esc_html__('Template','ovaev'),
						'type' 		=> \Elementor\Controls_Manager::SELECT,
						'default' 	=> 'type1',
						'options' 	=> [
							'type1' => esc_html__( 'Template 1', 'ovaev' ),
							'type2' => esc_html__( 'Template 2', 'ovaev' ),
							'type3' => esc_html__( 'Template 3', 'ovaev' ),
							'type4' => esc_html__( 'Template 4', 'ovaev' ),
							'type5' => esc_html__( 'Template 5', 'ovaev' ),
							'type6' => esc_html__( 'Template 6', 'ovaev' ),
	 					],
	 					'condition' => [
	 						'version' => ['version_3'],
	 					],
					]
				);

				$this->add_control(
					'column',
					[
						'label' 	=> esc_html__('Column','ovaev'),
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
					'column_template',
					[
						'label' 	=> esc_html__('Column','ovaev'),
						'type' 		=> \Elementor\Controls_Manager::SELECT,
						'default' 	=> 'col2',
						'options' 	=> [
							'col1' => esc_html__( 'One Column', 'ovaev' ),
							'col2' => esc_html__( 'Two Columns', 'ovaev' ),
							'col3' => esc_html__( 'Three Columns', 'ovaev' ),
	 					],
	 					'condition' => [
	 						'version' => 'version_3'
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
							'ID'  					=> esc_html__( 'ID', 'ovaev' ),			
							'title'             	=> esc_html__( 'Title', 'ovaev' ),
							'event_custom_sort' 	=> esc_html__( 'Custom Sort', 'ovaev' ),
							'ovaev_start_date_time' => esc_html__( 'Start Date', 'ovaev' ),		
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
							'ASC'  => esc_html__( 'Ascending', 'ovaev' )
						]
					]
				);
				
			$this->end_controls_section(); // END

			// Content style
			$this->start_controls_section(
				'section_style_content',
				[
					'label' => esc_html__( 'Content', 'ovaev' ),
					'tab'   => \Elementor\Controls_Manager::TAB_STYLE
				]
			);

				$this->add_responsive_control(
					'margin_content',
					[
						'label' 		=> esc_html__( 'Margin', 'ovaev' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ovaev-event-element' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							'{{WRAPPER}} .ovaev-event-element.version_2' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							'{{WRAPPER}} .ovaev-event-element.version_3' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_responsive_control(
					'padding_content',
					[
						'label' 		=> esc_html__( 'Padding', 'ovaev' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' => [
							'{{WRAPPER}} .ovaev-event-element' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							'{{WRAPPER}} .ovaev-event-element.version_2' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							'{{WRAPPER}} .ovaev-event-element.version_3' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
			$template = apply_filters( 'ovaev_elementor_events_template', 'elements/ovaev_events.php' );
			ob_start();
			ovaev_get_template( $template, $settings );
			echo ob_get_clean();
		}
	}

	// Register new widget
	$widgets_manager->register( new OVAEV_Elementor_Events() );
}