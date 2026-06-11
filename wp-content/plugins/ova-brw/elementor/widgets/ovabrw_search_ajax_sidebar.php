<?php if ( !defined( 'ABSPATH' ) ) exit(); // Exit if accessed directly

/**
 * Class OVABRW_Search_Ajax_Sidebar
 */
if ( !class_exists( 'OVABRW_Search_Ajax_Sidebar' ) ) {

	class OVABRW_Search_Ajax_Sidebar extends \Elementor\Widget_Base {

		/**
		 * Get widget name
		 */
		public function get_name() {		
			return 'ovabrw_search_ajax_sidebar';
		}

		/**
		 * Get widget title
		 */
		public function get_title() {
			return esc_html__( 'Search Ajax Sidebar', 'ova-brw' );
		}

		/**
		 * Get widget icon
		 */
		public function get_icon() {
			return 'eicon-sidebar';
		}

		/**
		 * Get widget categories
		 */
		public function get_categories() {
			return [ 'ovabrw-tours' ];
		}

		/**
		 * Get style depends
		 */
		public function get_style_depends() {
			return [ 'ovabrw-jquery-ui' ];
		}

		/**
		 * Get script depends
		 */
		public function get_script_depends() {
			return [ 'ovabrw-jquery-ui', 'ovabrw-jquery-ui-touch', 'ovabrw-elementor-product-search', 'ovabrw-elementor-product-search-ajax-sidebar' ];
		}

		/**
		 * Register controls
		 */
		protected function register_controls() {
			
			$this->start_controls_section(
				'section_setting',
				[
					'label' => esc_html__( 'Settings', 'ova-brw' ),
				]
			);

				$this->add_control(
					'show_advanced_search',
					[
						'label' 		=> esc_html__('Show Advanced Search', 'ova-brw' ),
						'type' 			=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 		=> esc_html__( 'Show', 'ova-brw' ),
						'label_off' 	=> esc_html__( 'Hide', 'ova-brw' ),
						'return_value' 	=> 'yes',
						'default' 		=> 'yes',
					]
				);

				$this->add_control(
					'show_filter',
					[
						'label' 		=> esc_html__('Show Filter', 'ova-brw' ),
						'type' 			=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 		=> esc_html__( 'Show', 'ova-brw' ),
						'label_off' 	=> esc_html__( 'Hide', 'ova-brw' ),
						'return_value' 	=> 'yes',
						'default' 		=> 'yes',
					]
				);

				$this->add_control(
					'search_position',
					[
						'label' 	=> esc_html__( 'Search Position', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::SELECT,
						'default' 	=> 'right_sidebar',
						'options' 	=> [
							'right_sidebar' 	=> esc_html__( 'Right Sidebar', 'ova-brw' ),
							'left_sidebar'  	=> esc_html__( 'Left Sidebar', 'ova-brw' ),
						],
					]
				);    

				$this->add_control(
					'posts_per_page',
					[
						'label' 	=> esc_html__( 'Tours Per Page', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::NUMBER,
						'min' 		=> -1,
						'step' 		=> 1,
						'default' 	=> 5,
					]
				);

				$this->add_control(
					'orderby',
					[
						'label' 	=> esc_html__( 'Order By', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::SELECT,
						'default' 	=> 'ID',
						'options' 	=> [
							'title'    		=> esc_html__( 'Title', 'ova-brw' ),
							'ID' 	   		=> esc_html__( 'ID', 'ova-brw' ),
							'date' 	   		=> esc_html__( 'Date', 'ova-brw' ),
							'featured' 		=> esc_html__( 'Featured', 'ova-brw' ),
							'menu_order' 	=> esc_html__( 'Menu Order', 'ova-brw' ),
							'popularity' 	=> esc_html__( 'Popularity (sales)', 'ova-brw' ),
							'rating' 		=> esc_html__( 'Average rating', 'ova-brw' ),
							'price' 		=> esc_html__( 'Sort by price', 'ova-brw' ),
						],
						'condition' => [
							'show_filter' => ''
						]
					]
				);

				$this->add_control(
					'order',
					[
						'label' 	=> esc_html__( 'Order', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::SELECT,
						'default' 	=> 'DESC',
						'options' 	=> [
							'ASC' 	=> esc_html__( 'Ascending', 'ova-brw' ),
							'DESC' 	=> esc_html__( 'Descending', 'ova-brw' ),
						],
						'condition' => [
							'show_filter' => ''
						]
					]
				);

				// Categories
				$categories = $excl_category = [];

				// Get product categories
				$product_categories = get_categories([
					'taxonomy' 	=> 'product_cat',
					'orderby' 	=> 'name',
		        	'order'   	=> 'ASC'
				]);

				// Loop
				if ( ovabrw_array_exists( $product_categories ) ) {
					foreach ( $product_categories as $cat ) {
						// Add category
						$categories[$cat->slug] = $cat->cat_name;

						// Exclude category
						$excl_category[$cat->term_id] 	= $cat->cat_name;
					}
				} // END loop

				$this->add_control(
					'default_category',
					[
						'label' 	=> esc_html__( 'Default Category', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::SELECT2,
						'multiple' 	=> true,
						'options' 	=> $categories,
					]
				);

				$this->add_control(
					'search_results_layout',
					[
						'label' 	=> esc_html__( 'Default Layout', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::SELECT,
						'default' 	=> 'list',
						'options' 	=> [
							'list' 	=> esc_html__( 'List', 'ova-brw' ),
							'grid' 	=> esc_html__( 'Grid', 'ova-brw' ),
						],
					]
				);

				$this->add_control(
					'search_results_grid_column',
					[
						'label' 	=> esc_html__( 'Grid Column', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::SELECT,
						'default' 	=> 'column3',
						'options' 	=> [
							'column2' 	=> esc_html__( '2 Column', 'ova-brw' ),
							'column3' 	=> esc_html__( '3 Column', 'ova-brw' ),
						],
						'condition' => [
							'search_results_layout' => 'grid'
						]
					]
				);

				$this->add_control(
					'thumbnail_type',
					[
						'label' 	=> esc_html__( 'Thumbnail', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::SELECT,
						'default' 	=> 'image',
						'options' 	=> [
							'image' 	=> esc_html__( 'Image', 'ova-brw' ),
							'gallery' 	=> esc_html__( 'Gallery', 'ova-brw' ),
						]
					]
				);

				$this->add_control(
					'search_title',
					[
						'label' 	=> esc_html__( 'Search Title', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::TEXT,
						'default'   => esc_html__( 'Search Tours', 'ova-brw' ),
					]
				);

				$this->add_control(
					'button_label',
					[
						'label' 	=> esc_html__( 'Button Label', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::TEXT,
						'default'   => esc_html__( 'Search', 'ova-brw' ),
					]
				);

				$this->add_control(
					'icon_button',
					[
						'label' 	=> esc_html__( 'Icon Button', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::ICONS,
						'default' 	=> [
							'value' 	=> 'icomoon icomoon-search',
							'library' 	=> 'all',
						],
					]
				);

			$this->end_controls_section();

			$this->start_controls_section(
				'section_advanced_search_setting',
				[
					'label' => esc_html__( 'Advanced Search Settings', 'ova-brw' ),
				]
			);

				$this->add_control(
					'filter_price_label',
					[
						'label' 	=> esc_html__( 'Price Label', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::TEXT,
						'default'   => esc_html__( 'Filter Price', 'ova-brw' ),
						'condition' => [
							'show_price_filter' => 'yes',
						],
					]
				);

				$this->add_control(
					'show_price_filter',
					[
						'label' 		=> esc_html__( 'Show Price Filter', 'ova-brw' ),
						'type' 			=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 		=> esc_html__( 'Show', 'ova-brw' ),
						'label_off' 	=> esc_html__( 'Hide', 'ova-brw' ),
						'return_value' 	=> 'yes',
						'default' 		=> 'yes',
						'separator' 	=> 'after',
					]
				);

				$this->add_control(
					'review_label',
					[
						'label' 	=> esc_html__( 'Review Label', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::TEXT,
						'default'   => esc_html__( 'Review Score', 'ova-brw' ),
						'condition' => [
							'show_review_filter' => 'yes',
						],
					]
				);

				$this->add_control(
					'show_review_filter',
					[
						'label' 		=> esc_html__( 'Show Review Filter', 'ova-brw' ),
						'type' 			=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 		=> esc_html__( 'Show', 'ova-brw' ),
						'label_off' 	=> esc_html__( 'Hide', 'ova-brw' ),
						'return_value' 	=> 'yes',
						'default' 		=> 'yes',
						'separator' 	=> 'after',
					]
				);

				$this->add_control(
					'filter_category_label',
					[
						'label' 	=> esc_html__( 'Categories Label', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::TEXT,
						'default'   => esc_html__( 'Categories', 'ova-brw' ),
						'condition' => [
							'show_category_filter' => 'yes',
						],
					]
				);

				$this->add_control(
					'excl_category',
					[
						'label' 	=> esc_html__( 'Exclude Category', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::SELECT2,
						'multiple' 	=> true,
						'options' 	=> $excl_category,
						'condition' => [
							'show_category_filter' => 'yes',
						],
					]
				);

				$this->add_control(
					'show_category_filter',
					[
						'label' 		=> esc_html__( 'Show Category Filter', 'ova-brw' ),
						'type' 			=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 		=> esc_html__( 'Show', 'ova-brw' ),
						'label_off' 	=> esc_html__( 'Hide', 'ova-brw' ),
						'return_value' 	=> 'yes',
						'default' 		=> 'yes',
						'separator' 	=> 'after',
					]
				);

				$this->add_control(
					'filter_duration_label',
					[
						'label' 	=> esc_html__( 'Duration Label', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::TEXT,
						'default'   => esc_html__( 'Duration', 'ova-brw' ),
						'condition' => [
							'show_duration_filter' => 'yes'
						]
					]
				);

				$this->add_control(
					'show_duration_filter',
					[
						'label' 		=> esc_html__('Show Duration Filter', 'ova-brw' ),
						'type' 			=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 		=> esc_html__( 'Show', 'ova-brw' ),
						'label_off' 	=> esc_html__( 'Hide', 'ova-brw' ),
						'return_value' 	=> 'yes',
						'default' 		=> 'yes',
					]
				);

			$this->end_controls_section();

			$this->start_controls_section(
				'section_duration_fields',
				[
					'label' 	=> esc_html__( 'Create Duration Fields', 'ova-brw' ),
					'condition' => [
						'show_duration_filter' => 'yes'
					]
				]
			);

				$repeater = new \Elementor\Repeater();

				$repeater->add_control(
					'duration_type',
					[
						'label' 	=> esc_html__( 'Type', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::SELECT,
						'default' 	=> 'day',
						'options' 	=> [
							'day' 	=> esc_html__('Day', 'ova-brw'),
							'hour' 	=> esc_html__('Hour', 'ova-brw'),
						]
					]
				);

				$repeater->add_control(
					'duration_name',
					[
						'label' 	=> esc_html__( 'Name', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::TEXT,
						'default' 	=> esc_html__('Fullday', 'ova-brw'),
					]
				);

				$repeater->add_control(
					'duration_day_value_from',
					[
						'label' 	=> esc_html__( 'Min', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::NUMBER,	
						'min' 		=> 0,
						'default' 	=> 1,
						'condition' => [
							'duration_type' => 'day'
						]
					]
				);

				$repeater->add_control(
					'duration_day_value_to',
					[
						'label' 	=> esc_html__( 'Max', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::NUMBER,	
						'min' 		=> 1,
						'condition' => [
							'duration_type' => 'day'
						]
					]
				);

				$repeater->add_control(
					'duration_hour_value_from',
					[
						'label' 	=> esc_html__( 'Min', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::NUMBER,	
						'min' 		=> 0,
						'default' 	=> 0,
						'condition' => [
							'duration_type' => 'hour'
						]
					]
				);

				$repeater->add_control(
					'duration_hour_value_to',
					[
						'label' 	=> esc_html__( 'Max', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::NUMBER,	
						'min' 		=> 1,
						'condition' => [
							'duration_type' => 'hour'
						]
					]
				);

				$this->add_control(
					'duration_fields',
					[
						'label' 	=> esc_html__( 'Items', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::REPEATER,
						'fields' 	=> $repeater->get_controls(),
						'default' 	=> [
							[
								'duration_type' 			=> 'hour',
								'duration_name' 			=> esc_html__( '0-3 hours', 'ova-brw' ),
								'duration_hour_value_from' 	=> 0,
								'duration_hour_value_to' 	=> 3,
							],
							[
								'duration_type' 			=> 'hour',
								'duration_name' 			=> esc_html__( '3-5 hours', 'ova-brw' ),
								'duration_hour_value_from' 	=> 3,
								'duration_hour_value_to' 	=> 5,
							],
							[
								'duration_type' 			=> 'hour',
								'duration_name' 			=> esc_html__( '5-7 hours', 'ova-brw' ),
								'duration_hour_value_from' 	=> 5,
								'duration_hour_value_to' 	=> 7,
							],
							[
								'duration_type' 			=> 'hour',
								'duration_name' 			=> esc_html__( '+7 hours', 'ova-brw' ),
								'duration_hour_value_from' 	=> 7,
							],
							[
								'duration_type' 			=> 'day',
								'duration_name' 			=> esc_html__( '1-3 days', 'ova-brw' ),
								'duration_day_value_from' 	=> 1,
								'duration_day_value_to' 	=> 3,
							],
							[
								'duration_type' 			=> 'day',
								'duration_name' 			=> esc_html__( '+3 days', 'ova-brw' ),
								'duration_day_value_from' 	=> 3,
							],
						],
						'title_field' => '{{{ duration_type }}} - {{{ duration_name }}}',
					]
				);	

			$this->end_controls_section();

			$this->start_controls_section(
				'section_filter_setting',
				[
					'label' => esc_html__( 'Filter Settings', 'ova-brw' ),
				]
			);

				$this->add_control(
					'tour_found_text',
					[
						'label' 	=> esc_html__( 'Tour Found Text', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::TEXT,
						'default'   => esc_html__( 'Tours found', 'ova-brw' ),
					]
				);

				$this->add_control(
					'clear_filter_text',
					[
						'label' 	=> esc_html__( 'Clear Text', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::TEXT,
						'default'   => esc_html__( 'Clear Filter', 'ova-brw' ),
					]
				);

				$this->add_control(
					'sort_by_default',
					[
						'label' 	=> esc_html__( 'Default Sort by', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::SELECT,
						'default' 	=> 'date',
						'options' 	=> [
							'date'  	    => esc_html__( 'Latest', 'ova-brw' ),
							'rating_desc'  	=> esc_html__( 'Rating', 'ova-brw' ),
							'price_asc' 	=> esc_html__( 'Price: low to high', 'ova-brw' ),
							'price_desc' 	=> esc_html__( 'Price: high to low', 'ova-brw' ),
						],
					]
				);

			$this->end_controls_section();

			$this->start_controls_section(
				'section_destinations',
				[
					'label' => esc_html__( 'Destinations', 'ova-brw' ),
				]
			);

			    $this->add_control(
					'icon_destination',
					[
						'label' 	=> esc_html__( 'Icon', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::ICONS,
						'default' 	=> [
							'value' 	=> 'icomoon icomoon-location',
							'library' 	=> 'all',
						],
					]
				);

				$this->add_control(
					'destination_placeholder',
					[
						'label' 	=> esc_html__( 'Placeholder', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::TEXT,
						'default' 	=> esc_html__( 'Destinations', 'ova-brw' ),
					]
				);

				$this->add_control(
					'destination_default',
					[
						'label' 	=> esc_html__( 'Default', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::SELECT,
						'options' 	=> ovabrw_get_destinations(),
					]
				);

				$this->add_control(
					'show_destination',
					[
						'label' 		=> esc_html__( 'Show', 'ova-brw' ),
						'type' 			=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 		=> esc_html__( 'Show', 'ova-brw' ),
						'label_off' 	=> esc_html__( 'Hide', 'ova-brw' ),
						'return_value' 	=> 'yes',
						'default' 		=> 'yes',
					]
				);

			$this->end_controls_section();

			$this->start_controls_section(
				'section_custom_taxonomy',
				[
					'label' => esc_html__( 'Custom Taxonomy', 'ova-brw' ),
				]
			);

				$taxonomies 		= ovabrw_get_option( 'custom_taxonomy', [] );
				$data_taxonomy[''] 	= esc_html__( 'None', 'ova-brw' );
				$slug_taxonomys 	= [];

				if ( ovabrw_array_exists( $taxonomies ) ) {
					foreach( $taxonomies as $key => $value ) {
						$data_taxonomy[$key] = $value['name'];
						array_push($slug_taxonomys, $key);
					}
				}

				$this->add_control(
					'slug_custom_taxonomy', 
					[
						'label' 		=> esc_html__( 'Select Custom Taxonomy', 'ova-brw' ),
						'type' 			=> \Elementor\Controls_Manager::SELECT,
						'label_block' 	=> true,
						'options' 		=> $data_taxonomy,
					]
				);

				$this->add_control(
					'icon_custom_taxonomy',
					[
						'label' 	=> esc_html__( 'Icon', 'ova-brw' ), 
						'type' 		=> \Elementor\Controls_Manager::ICONS,
						'default' 	=> [
							'value' 	=> 'icomoon icomoon-plane',
							'library' 	=> 'all',
						],
					]
				);

				$this->add_control(
					'custom_taxonomy_placeholder',
					[
						'label' 	=> esc_html__( 'Placeholder', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::TEXT,
						'default' 	=> esc_html__( 'Activity', 'ova-brw' ),
					]
				);

				if ( ovabrw_array_exists( $slug_taxonomys ) ) {
					foreach ( $slug_taxonomys as $slug_taxonomy ) {
						$data_term = [
							'' => esc_html__( 'All', 'ova-brw' ) . ' ' .$data_taxonomy[$slug_taxonomy]
						];

						if ( $slug_taxonomy ) {	
							$terms = get_terms([
								'taxonomy' => $slug_taxonomy
							]);

							if ( ovabrw_array_exists( $terms ) && isset( $taxonomies[$slug_taxonomy]['name'] ) ) {
								foreach ( $terms as $term ) {
									if ( is_object( $term ) ) {
										$data_term[$term->slug] = $term->name;
									}
								}
							}				
						}

						$this->add_control(
							'taxonomy_value_'.esc_html( $slug_taxonomy ),
							[
								'label' 	=> esc_html__( 'Default', 'ova-brw' ),
								'type' 		=> \Elementor\Controls_Manager::SELECT,
								'default' 	=> '',
								'options' 	=> $data_term,
								'condition' => [
									'slug_custom_taxonomy' => $slug_taxonomy,
								]
							]
						);
						
					}

				}

				$this->add_control(
					'mutiple_custom_taxonomy',
					[
						'label' 		=> esc_html__( 'More Taxonomies', 'ova-brw' ),
						'type' 			=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 		=> esc_html__( 'Yes', 'ova-brw' ),
						'label_off' 	=> esc_html__( 'No', 'ova-brw' ),
						'separator' 	=> 'before',
						'default' 		=> '',
					]
				);

				$repeater = new \Elementor\Repeater();

				$repeater->add_control(
					'item_slug_taxonomy', [
						'label' 		=> esc_html__( 'Select Custom Taxonomy', 'ova-brw' ),
						'type' 			=> \Elementor\Controls_Manager::SELECT,
						'label_block' 	=> true,
						'options' 		=> $data_taxonomy,
					]
				);

				$repeater->add_control(
					'item_icon_taxonomy',
					[
						'label' 	=> esc_html__( 'Icon', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::ICONS,
						'default' 	=> [
							'value' 	=> 'icomoon icomoon-plane',
							'library' 	=> 'all',
						],
					]
				);

				$repeater->add_control(
					'item_taxonomy_placeholder',
					[
						'label' 	=> esc_html__( 'Placeholder', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::TEXT,
						'default' 	=> esc_html__( 'Taxonomy', 'ova-brw' ),
					]
				);

				if ( ovabrw_array_exists( $slug_taxonomys ) ) {
					foreach ( $slug_taxonomys as $slug_taxonomy ) {
						$data_term = [
							'' => esc_html__( 'All', 'ova-brw' ) . ' ' .$data_taxonomy[$slug_taxonomy]
						];

						if ( $slug_taxonomy ) {	
							$terms = get_terms([
								'taxonomy' => $slug_taxonomy
							]);

							if ( ovabrw_array_exists( $terms ) && isset( $taxonomies[$slug_taxonomy]['name'] ) ) {
								foreach ( $terms as $term ) {
									if ( is_object( $term ) ) {
										$data_term[$term->slug] = $term->name;
									}
								}
							}				
						}

						$repeater->add_control(
							'item_taxonomy_value_'.esc_html( $slug_taxonomy ),
							[
								'label' 	=> esc_html__( 'Default', 'ova-brw' ),
								'type' 		=> \Elementor\Controls_Manager::SELECT,
								'default' 	=> '',
								'options' 	=> $data_term,
								'condition' => [
									'item_slug_taxonomy' => $slug_taxonomy,
								]
							]
						);
					}
				}

				$this->add_control(
					'list_custom_taxonomy',
					[
						'label' 	=> esc_html__( 'Taxonomies', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::REPEATER,
						'fields' 	=> $repeater->get_controls(),
						'condition' => [
							'mutiple_custom_taxonomy!' => '',
						],
						'title_field' => '{{{ item_taxonomy_placeholder }}}',
					]
				);

			$this->end_controls_section();

			$this->start_controls_section(
				'section_check_in',
				[
					'label' => esc_html__( 'Check in', 'ova-brw' ),
				]
			);

			    $this->add_control(
					'icon_check_in',
					[
						'label' 	=> esc_html__( 'Icon', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::ICONS,
						'default' 	=> [
							'value' 	=> 'icomoon icomoon-calander',
							'library' 	=> 'all',
						],
					]
				);

				$this->add_control(
					'show_check_in',
					[
						'label' 		=> esc_html__( 'Show', 'ova-brw' ),
						'type' 			=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 		=> esc_html__( 'Show', 'ova-brw' ),
						'label_off' 	=> esc_html__( 'Hide', 'ova-brw' ),
						'return_value' 	=> 'yes',
						'default' 		=> 'yes',
					]
				);

			$this->end_controls_section();

			$this->start_controls_section(
				'section_adults',
				[
					'label' => esc_html__( 'Adults', 'ova-brw' ),
				]
			);

				$this->add_control(
					'adults_label',
					[
						'label' 	=> esc_html__( 'Label', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::TEXT,
						'default' 	=> esc_html__( 'Adults', 'ova-brw' ),
					]
				);

				$this->add_control(
					'default_adult_number',
					[
						'label' 	=> esc_html__( 'Default Adults Number', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::NUMBER,
						'min' 		=> 0,
						'step' 		=> 1,
						'default' 	=> 0
					]
				);

				$this->add_control(
					'max_adult',
					[
						'label' 	=> esc_html__( 'Maximum Adults', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::NUMBER,
						'min' 		=> 0,
						'step' 		=> 1,
						'default' 	=> 30,
					]
				);

				$this->add_control(
					'min_adult',
					[
						'label' 	=> esc_html__( 'Minimum Adults', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::NUMBER,
						'min' 		=> 0,
						'step' 		=> 1,
						'default' 	=> 0
					]
				);

				$this->add_control(
					'show_adult',
					[
						'label' 		=> esc_html__( 'Show', 'ova-brw' ),
						'type' 			=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 		=> esc_html__( 'Show', 'ova-brw' ),
						'label_off' 	=> esc_html__( 'Hide', 'ova-brw' ),
						'return_value' 	=> 'yes',
						'default' 		=> 'yes',
					]
				);

			$this->end_controls_section();

			$this->start_controls_section(
				'section_childrens',
				[
					'label' => esc_html__( 'Children', 'ova-brw' ),
				]
			);

				$this->add_control(
					'childrens_label',
					[
						'label' 	=> esc_html__( 'Label', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::TEXT,
						'default' 	=> esc_html__( 'Children', 'ova-brw' ),
					]
				);

				$this->add_control(
					'default_children_number',
					[
						'label' 	=> esc_html__( 'Default Children Number', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::NUMBER,
						'min' 		=> 0,
						'step' 		=> 1,
						'default' 	=> 0,
					]
				);

				$this->add_control(
					'max_children',
					[
						'label' 	=> esc_html__( 'Maximum Children', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::NUMBER,
						'min' 		=> 0,
						'step' 		=> 1,
						'default' 	=> 10,
					]
				);

				$this->add_control(
					'min_children',
					[
						'label' 	=> esc_html__( 'Minimum Children', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::NUMBER,
						'min' 		=> 0,
						'step' 		=> 1,
						'default' 	=> 0,
					]
				);

				$this->add_control(
					'show_children',
					[
						'label' 		=> esc_html__( 'Show', 'ova-brw' ),
						'type' 			=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 		=> esc_html__( 'Show', 'ova-brw' ),
						'label_off' 	=> esc_html__( 'Hide', 'ova-brw' ),
						'return_value' 	=> 'yes',
						'default' 		=> 'yes',
					]
				);

			$this->end_controls_section();

			$this->start_controls_section(
				'section_babies',
				[
					'label' => esc_html__( 'Babies', 'ova-brw' ),
				]
			);

				$this->add_control(
					'babies_label',
					[
						'label' 	=> esc_html__( 'Label', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::TEXT,
						'default' 	=> esc_html__( 'Babies', 'ova-brw' ),
					]
				);

				$this->add_control(
					'default_babies_number',
					[
						'label' 	=> esc_html__( 'Default Babies Number', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::NUMBER,
						'min' 		=> 0,
						'step' 		=> 1,
						'default' 	=> 0,
					]
				);

				$this->add_control(
					'max_baby',
					[
						'label' 	=> esc_html__( 'Maximum Babies', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::NUMBER,
						'min' 		=> 0,
						'step' 		=> 1,
						'default' 	=> 10,
					]
				);

				$this->add_control(
					'min_baby',
					[
						'label' 	=> esc_html__( 'Minimum Babies', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::NUMBER,
						'min' 		=> 0,
						'step' 		=> 1,
						'default' 	=> 0,
					]
				);

				$this->add_control(
					'show_baby',
					[
						'label' 		=> esc_html__( 'Show', 'ova-brw' ),
						'type' 			=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 		=> esc_html__( 'Show', 'ova-brw' ),
						'label_off' 	=> esc_html__( 'Hide', 'ova-brw' ),
						'default' 		=> 'no',
					]
				);

			$this->end_controls_section();

			$this->start_controls_section(
				'section_guests',
				[
					'label' => esc_html__( 'Guests', 'ova-brw' ),
				]
			);

			    $this->add_control(
					'icon_guests',
					[
						'label' 	=> esc_html__( 'Icon', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::ICONS,
						'default' 	=> [
							'value' 	=> 'icomoon icomoon-user',
							'library' 	=> 'all',
						],
					]
				);

				$this->add_control(
					'guests_placeholder',
					[
						'label' => esc_html__( 'Placeholder', 'ova-brw' ),
						'type' 	=> \Elementor\Controls_Manager::TEXT,
					]
				);

				$this->add_control(
					'show_guests',
					[
						'label' 		=> esc_html__( 'Show', 'ova-brw' ),
						'type' 			=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 		=> esc_html__( 'Show', 'ova-brw' ),
						'label_off' 	=> esc_html__( 'Hide', 'ova-brw' ),
						'return_value' 	=> 'yes',
						'default' 		=> 'yes',
					]
				);

			$this->end_controls_section();

			$this->start_controls_section(
				'section_ovabrw_search',
				[
					'label' => esc_html__( 'Search Wrapper', 'ova-brw' ),
					'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_control(
					'search_bgcolor',
					[
						'label' 	=> esc_html__( 'Background Color', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ovabrw-search' => 'background-color: {{VALUE}};',
						],
					]
				);

				$this->add_responsive_control(
		            'search_padding',
		            [
		                'label' 		=> esc_html__( 'Padding', 'ova-brw' ),
		                'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
		                'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
		                'selectors' 	=> [
		                    '{{WRAPPER}} .ovabrw-search' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		                ],
		            ]
		        );

		        $this->add_responsive_control(
		            'search_margin',
		            [
		                'label' 		=> esc_html__( 'Margin', 'ova-brw' ),
		                'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
		                'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
		                'selectors' 	=> [
		                    '{{WRAPPER}} .ovabrw-search' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		                ],
		            ]
		        );

		        $this->add_responsive_control(
		            'search_border_radius',
		            [
		                'label' 		=> esc_html__( 'Border Radius', 'ova-brw' ),
		                'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
		                'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
		                'selectors' 	=> [
		                    '{{WRAPPER}} .ovabrw-search' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		                ],
		            ]
		        );

				$this->add_group_control(
					\Elementor\Group_Control_Border::get_type(),
					[
						'name' 		=> 'search_border',
						'label' 	=> esc_html__( 'Border', 'ova-brw' ),
						'selector' 	=> '{{WRAPPER}} .ovabrw-search',
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Box_Shadow::get_type(),
					[
						'name' 		=> 'search_box_shadow',
						'label' 	=> esc_html__( 'Box Shadow', 'ova-brw' ),
						'selector' 	=> '{{WRAPPER}} .ovabrw-search',
					]
				);

			$this->end_controls_section();

			/* Begin Icon Label Style */
			$this->start_controls_section(
	            'icon_label_style',
	            [
	                'label' => esc_html__( 'Icon Label', 'ova-brw' ),
	                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
	            ]
	        );

				$this->add_responsive_control(
					'size_label_icon',
					[
						'label' 		=> esc_html__( 'Size', 'ova-brw' ),
						'type' 			=> \Elementor\Controls_Manager::SLIDER,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'range' 		=> [
							'px' => [
								'min' 	=> 0,
								'max' 	=> 35,
								'step' 	=> 1,
							],
						],
						'selectors' 	=> [
							'{{WRAPPER}} .ovabrw-search .ovabrw-search-form .ovabrw-s-field i' => 'font-size: {{SIZE}}{{UNIT}};',
							'{{WRAPPER}} .ovabrw-search .ovabrw-search-form .ovabrw-s-field svg' => 'width: {{SIZE}}{{UNIT}};',
						],
					]
				);

				$this->add_control(
					'icon_color',
					[
						'label' 	=> esc_html__( 'Color', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ovabrw-search .ovabrw-search-form .ovabrw-s-field i' => 'color: {{VALUE}};',
							'{{WRAPPER}} .ovabrw-search .ovabrw-search-form .ovabrw-s-field svg, {{WRAPPER}} .ovabrw-search .ovabrw-search-form .ovabrw-s-field svg path' => 'fill : {{VALUE}};'
						],
					]
				);

				$this->add_responsive_control(
		            'search_filed_padding',
		            [
		                'label' 		=> esc_html__( 'Padding', 'ova-brw' ),
		                'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
		                'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
		                'selectors' 	=> [
		                    '{{WRAPPER}} .ovabrw-search .ovabrw-search-form .ovabrw-s-field .search-field' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		                ],
		            ]
		        );

	        $this->end_controls_section(); /* End label style */

			/* Begin Placeholder Style */
			$this->start_controls_section(
	            'placeholder_style',
	            [
	                'label' => esc_html__( 'Placeholder', 'ova-brw' ),
	                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
	            ]
	        );

				$this->add_control(
					'placeholder_color',
					[
						'label' 	=> esc_html__( 'Color', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ovabrw-search .ovabrw-search-form .ovabrw-s-field .search-field .ovabrw-input input::placeholder, {{WRAPPER}} .ovabrw-search .ovabrw-search-form .ovabrw-s-field .search-field .ovabrw-guestspicker .guestspicker, {{WRAPPER}} .ovabrw-search .ovabrw-search-form .ovabrw-s-field .search-field .ovabrw-input select, .ovabrw-search .select2-container--default .select2-selection--single .select2-selection__rendered' => 'color: {{VALUE}};',
						],
					]
				);

	        $this->end_controls_section(); /* End Placeholder style */

			/* Begin Button Style */
			$this->start_controls_section(
	            'button_style',
	            [
	                'label' => esc_html__( 'Search Button', 'ova-brw' ),
	                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
	            ]
	        );

	            $this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 	 	=> 'search_button_typography',
						'selector' 	=> '{{WRAPPER}} .ovabrw-search .ovabrw-search-form .ovabrw-search-btn button.ovabrw-btn',
					]
				);

				$this->add_control(
					'button_text_color',
					[
						'label' 	=> esc_html__( 'Color', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ovabrw-search .ovabrw-search-form .ovabrw-search-btn button.ovabrw-btn' => 'color: {{VALUE}};',
						],
					]
				);

				$this->add_control(
					'button_text_color_hover',
					[
						'label' 	=> esc_html__( 'Color Hover', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ovabrw-search .ovabrw-search-form .ovabrw-search-btn button.ovabrw-btn:hover' => 'color: {{VALUE}};',
						],
					]
				);

				$this->add_control(
					'button_bgcolor',
					[
						'label' 	=> esc_html__( 'Backgrund Color', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ovabrw-search .ovabrw-search-form .ovabrw-search-btn button.ovabrw-btn' => 'background-color: {{VALUE}};',
						],
					]
				);

				$this->add_control(
					'button_bgcolor_hover',
					[
						'label' 	=> esc_html__( 'Backgrund Color Hover', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ovabrw-search .ovabrw-search-form .ovabrw-search-btn button.ovabrw-btn:hover' => 'background-color: {{VALUE}};',
						],
					]
				);

				$this->add_responsive_control(
		            'button_padding',
		            [
		                'label' 		=> esc_html__( 'Padding', 'ova-brw' ),
		                'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
		                'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
		                'selectors' 	=> [
		                    '{{WRAPPER}} .ovabrw-search .ovabrw-search-form .ovabrw-search-btn button.ovabrw-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		                ],
		            ]
		        );

		        $this->add_responsive_control(
		            'button_border_radius',
		            [
		                'label' 		=> esc_html__( 'Border Radius', 'ova-brw' ),
		                'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
		                'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
		                'selectors' 	=> [
		                    '{{WRAPPER}} .ovabrw-search .ovabrw-search-form .ovabrw-search-btn button.ovabrw-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		                ],
		            ]
		        );

		        $this->add_group_control(
					\Elementor\Group_Control_Border::get_type(),
					[
						'name' 		=> 'search_button_border',
						'label' 	=> esc_html__( 'Border', 'ova-brw' ),
						'selector' 	=> '{{WRAPPER}} .ovabrw-search .ovabrw-search-form .ovabrw-search-btn button.ovabrw-btn',
					]
				);

	        $this->end_controls_section(); /* End Button style */

			/* Begin guest Style */
			$this->start_controls_section(
	            'guest_style',
	            [
	                'label' => esc_html__( 'Guest', 'ova-brw' ),
	                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
	            ]
	        );

	            $this->add_responsive_control(
					'guest_width',
					[
						'label' 		=> esc_html__( 'Width', 'ova-brw' ),
						'type' 			=> \Elementor\Controls_Manager::SLIDER,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'range' 		=> [
							'px' => [
								'min' 	=> 200,
								'max' 	=> 450,
								'step' 	=> 1,
							],
							'%' => [
								'min' 	=> 0,
								'max' 	=> 100,
								'step' 	=> 1,
							],
						],
						'selectors' 	=> [
							'{{WRAPPER}} .ovabrw-search .ovabrw-search-form .search-field .ovabrw-guestspicker-content' => 'width: {{SIZE}}{{UNIT}};',
						],
					]
				);
	            
	            $this->add_control(
					'guest_dropdown_heading',
					[
						'label' 	=> esc_html__( 'Guest Dropdown', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::HEADING,
						'separator' => 'before'
					]
				);

	            $this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 	 	=> 'guest_typography',
						'selector' 	=> '{{WRAPPER}} .ovabrw-search .ovabrw-search-form .ovabrw-s-field .search-field .ovabrw-guestspicker-content .guests-buttons .description label',
					]
				);

				$this->add_control(
					'label_guest_color',
					[
						'label' 	=> esc_html__( 'Color', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ovabrw-search .ovabrw-search-form .ovabrw-s-field .search-field .ovabrw-guestspicker-content .guests-buttons .description label' => 'color: {{VALUE}};',
						],
					]
				);

				$this->add_responsive_control(
		            'guest_dropdown_padding',
		            [
		                'label' 		=> esc_html__( 'Padding', 'ova-brw' ),
		                'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
		                'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
		                'selectors' 	=> [
		                    '{{WRAPPER}} .ovabrw-search .ovabrw-search-form .ovabrw-s-field .search-field .ovabrw-guestspicker-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
		                ],
		            ]
		        );

		        $this->add_group_control(
					\Elementor\Group_Control_Border::get_type(),
					[
						'name' 		=> 'guests_border',
						'label' 	=> esc_html__( 'Border', 'ova-brw' ),
						'selector' 	=> '{{WRAPPER}} .ovabrw-search .ovabrw-search-form .ovabrw-s-field .search-field .ovabrw-guestspicker-content',
					]
				);

				$this->add_control(
					'guest_dropdown_caret_color',
					[
						'label' 	=> esc_html__( 'Caret Color', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ovabrw-search .ovabrw-search-form .ovabrw-s-field .search-field .ovabrw-guestspicker-content:before' => 'border-bottom-color: {{VALUE}};',
						],
					]
				);

	            $this->add_control(
					'icon_guest_heading',
					[
						'label' 	=> esc_html__( 'Icon', 'ova-brw' ),
						'type' 		=> \Elementor\Controls_Manager::HEADING,
						'separator' => 'before'
					]
				);
	            
					$this->add_responsive_control(
						'size_guest_icon',
						[
							'label' 		=> esc_html__( 'Size', 'ova-brw' ),
							'type' 			=> \Elementor\Controls_Manager::SLIDER,
							'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
							'range' 		=> [
								'px' => [
									'min' 	=> 0,
									'max' 	=> 50,
									'step' 	=> 1,
								],
							],
							'selectors' 	=> [
								'{{WRAPPER}} .ovabrw-search .ovabrw-search-form .ovabrw-s-field .search-field .ovabrw-guestspicker-content .guests-buttons .guests-button .guests-icon i' => 'font-size: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->add_responsive_control(
						'bgsize_guest_icon',
						[
							'label' 		=> esc_html__( 'Background Size', 'ova-brw' ),
							'type' 			=> \Elementor\Controls_Manager::SLIDER,
							'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
							'range' 		=> [
								'px' => [
									'min' 	=> 0,
									'max' 	=> 50,
									'step' 	=> 1,
								],
							],
							'selectors' 	=> [
								'{{WRAPPER}} .ovabrw-search .ovabrw-search-form .ovabrw-s-field .search-field .ovabrw-guestspicker-content .guests-buttons .guests-button .guests-icon' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
							],
						]
					);

					$this->start_controls_tabs(
						'style_tabs'
					);

					$this->start_controls_tab(
						'style_guests_normal_tab',
						[
							'label' => esc_html__( 'Normal', 'ova-brw' ),
						]
					);

						$this->add_control(
							'icon_guest_color',
							[
								'label' 	=> esc_html__( 'Color', 'ova-brw' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ovabrw-search .ovabrw-search-form .ovabrw-s-field .search-field .ovabrw-guestspicker-content .guests-buttons .guests-button .guests-icon i' => 'color: {{VALUE}};',
								],
							]
						);

						$this->add_control(
							'icon_guest_bgcolor',
							[
								'label' 	=> esc_html__( 'Background Color', 'ova-brw' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ovabrw-search .ovabrw-search-form .ovabrw-s-field .search-field .ovabrw-guestspicker-content .guests-buttons .guests-button .guests-icon' => 'background-color: {{VALUE}};',
								],
							]
						);

					$this->end_controls_tab();

					$this->start_controls_tab(
						'style_guests_hover_tab',
						[
							'label' => esc_html__( 'Hover', 'ova-brw' ),
						]
					);
	                     
	                    $this->add_control(
							'icon_guest_color_hover',
							[
								'label' 	=> esc_html__( 'Color', 'ova-brw' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ovabrw-search .ovabrw-search-form .ovabrw-s-field .search-field .ovabrw-guestspicker-content .guests-buttons .guests-button .guests-icon:hover i' => 'color: {{VALUE}};',
								],
							]
						);

						$this->add_control(
							'icon_guest_bgcolor_hover',
							[
								'label' 	=> esc_html__( 'Background Color', 'ova-brw' ),
								'type' 		=> \Elementor\Controls_Manager::COLOR,
								'selectors' => [
									'{{WRAPPER}} .ovabrw-search .ovabrw-search-form .ovabrw-s-field .search-field .ovabrw-guestspicker-content .guests-buttons .guests-button .guests-icon:hover' => 'background-color: {{VALUE}};',
								],
							]
						);
						

					$this->end_controls_tab();
				$this->end_controls_tabs();
	        $this->end_controls_section(); /* End guest style */
		}

		/**
		 * Render HTML
		 */
		protected function render() {
			// Get settings
			$settings = $this->get_settings_for_display();

			// Get slug taxonomy
			$slug_taxo = ovabrw_get_meta_data( 'slug_custom_taxonomy', $settings );

			// Taxonomy value
			$value_taxo = ovabrw_get_meta_data( 'taxonomy_value_'.$slug_taxo, $settings );
			if ( $value_taxo ) {
				$settings['ctx_slug_value_selected'] = $value_taxo;
			}

			// Get template
			ovabrw_get_template( apply_filters( OVABRW_PREFIX.'element_product_search_ajax_sidebar_template' ,'single/search_ajax_sidebar.php' ), $settings );
		}
	}

	// Regiser new widget
	$widgets_manager->register( new OVABRW_Search_Ajax_Sidebar() );
}