<?php if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class Tripgo_Elementor_Blog_Grid
 */
if ( !class_exists( 'Tripgo_Elementor_Blog_Grid', false ) ) {

	class Tripgo_Elementor_Blog_Grid extends \Elementor\Widget_Base {

		/**
		 * Get widget name
		 */
		public function get_name() {
			return 'tripgo_elementor_blog_grid';
		}

		/**
		 * Get widget title
		 */
		public function get_title() {
			return esc_html__( 'Blog Grid', 'tripgo' );
		}

		/**
		 * Get widget icon
		 */
		public function get_icon() {
			return 'eicon-posts-ticker';
		}

		/**
		 * Get widget categories
		 */
		public function get_categories() {
			return [ 'tripgo' ];
		}

		/**
		 * Get style depends
		 */
		public function get_style_depends() {
			return [ 'tripgo-elementor-blog-grid' ];
		}

		/**
		 * Register controls
		 */
		protected function register_controls() {
			// Categories
			$categories = [];

			// Get post categories
			$post_categories = get_categories([
				'orderby' 	=> 'name',
				'order' 	=> 'ASC'
			]);

			if ( tripgo_array_exists( $post_categories ) ) {
				foreach ( $post_categories as $cat ) {
					$categories[$cat->slug] = $cat->cat_name;
				}
			}

			// SECTION CONTENT
			$this->start_controls_section(
				'section_content',
				[
					'label' => esc_html__( 'Content', 'tripgo' )
				]
			);

				$this->add_control(
					'template',
					[
						'label' 	=> esc_html__( 'Template', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::SELECT,
						'default' 	=> 'template1',
						'options' 	=> [
							'template1' => esc_html__( 'Template 1', 'tripgo' ),
							'template2' => esc_html__( 'Template 2', 'tripgo' ),
							'template3' => esc_html__( 'Template 3', 'tripgo' ),
							'template4' => esc_html__( 'Template 4', 'tripgo' )
						]
					]
				);

				$this->add_control(
					'category',
					[
						'label' 	=> esc_html__( 'Category', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::SELECT2,
						'multiple' 	=> true,
						'options' 	=> $categories
					]
				);

				$this->add_control(
					'total_count',
					[
						'label' 	=> esc_html__( 'Post Total', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::NUMBER,
						'default' 	=> 3
					]
				);

				$this->add_control(
					'number_column',
					[
						'label' 	=> esc_html__( 'Columns', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::SELECT,
						'default' 	=> 'columns3',
						'options' 	=> [
							'columns2' => esc_html__( '2 Columns', 'tripgo' ),
							'columns3' => esc_html__( '3 Columns', 'tripgo' ),
							'columns4' => esc_html__( '4 Columns', 'tripgo' )
						],
						'condition' => [
							'template!' => 'template4'
						]
					]
				);
	            
	            $this->add_control(
					'orderby',
					[
						'label' 	=> esc_html__('Order By', 'tripgo'),
						'type' 		=> \Elementor\Controls_Manager::SELECT,
						'default' 	=> 'ID',
						'options' 	=> [
							'ID' 		=> esc_html__( 'ID', 'tripgo' ),
							'title' 	=> esc_html__( 'Title', 'tripgo' ),
							'date' 		=> esc_html__( 'Date', 'tripgo' ),
							'modified' 	=> esc_html__( 'Modified', 'tripgo' ),
							'rand' 		=> esc_html__( 'Rand', 'tripgo' )
						]
					]
				);

				$this->add_control(
					'order_by',
					[
						'label' 	=> esc_html__( 'Order', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::SELECT,
						'default' 	=> 'desc',
						'options' 	=> [
							'asc' 	=> esc_html__( 'Ascending', 'tripgo' ),
							'desc' 	=> esc_html__( 'Descending', 'tripgo' )
						]
					]
				);

				$this->add_control(
					'text_readmore',
					[
						'label' 	=> esc_html__( 'Text Read More', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::TEXT,
						'default' 	=> esc_html__( 'Read More', 'tripgo' )
					]
				);

				$this->add_control(
					'show_thumbnail',
					[
						'label' 		=> esc_html__( 'Show Thumbnail', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 		=> esc_html__( 'Show', 'tripgo' ),
						'label_off' 	=> esc_html__( 'Hide', 'tripgo' ),
						'return_value' 	=> 'yes',
						'default' 		=> 'yes'
					]
				);

				$this->add_control(
					'show_short_desc',
					[
						'label' 		=> esc_html__( 'Show Short Description', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 		=> esc_html__( 'Show', 'tripgo' ),
						'label_off' 	=> esc_html__( 'Hide', 'tripgo' ),
						'return_value' 	=> 'yes',
						'default' 		=> 'yes'
					]
				);
				
				$this->add_control(
					'order_text',
					[
						'label' 	=> esc_html__( 'Words Total', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::NUMBER,
						'default' 	=> 20,
						'condition' => [
							'show_short_desc' => 'yes'
						]
					]
				);

				$this->add_control(
					'show_date',
					[
						'label' 		=> esc_html__( 'Show Date', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 		=> esc_html__( 'Show', 'tripgo' ),
						'label_off' 	=> esc_html__( 'Hide', 'tripgo' ),
						'return_value' 	=> 'yes',
						'default' 		=> 'yes'
					]
				);

				$this->add_control(
					'show_author',
					[
						'label' 		=> esc_html__( 'Show Author', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 		=> esc_html__( 'Show', 'tripgo' ),
						'label_off' 	=> esc_html__( 'Hide', 'tripgo' ),
						'return_value' 	=> 'yes',
						'default' 		=> 'yes'
					]
				);

				$this->add_control(
					'show_title',
					[
						'label' 		=> esc_html__( 'Show Title', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 		=> esc_html__( 'Show', 'tripgo' ),
						'label_off' 	=> esc_html__( 'Hide', 'tripgo' ),
						'return_value' 	=> 'yes',
						'default' 		=> 'yes'
					]
				);

				$this->add_control(
					'show_read_more',
					[
						'label' 		=> esc_html__( 'Show Read More', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::SWITCHER,
						'label_on' 		=> esc_html__( 'Show', 'tripgo' ),
						'label_off' 	=> esc_html__( 'Hide', 'tripgo' ),
						'return_value' 	=> 'yes',
						'default' 		=> 'yes'
					]
				);

				$this->add_control(
					'icon',
					[
						'label' 	=> esc_html__( 'Icon', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::ICONS,
						'default' 	=> [
							'value' 	=> 'icomoon icomoon-arrow-right',
							'library' 	=> 'all',
						],
						'condition'	=> [
							'show_read_more' => 'yes'
						]
					]
				);

			$this->end_controls_section(); // END SECTION CONTENT

			// SECTION TAB STYLE CONTENT
			$this->start_controls_section(
				'section_content_item',
				[
					'label' => esc_html__( 'Content', 'tripgo' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE
				]
			);

				$this->add_control(
					'bg_content',
					[
						'label' 	=> esc_html__( 'Background', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-blog .item .ova-content' => 'background-color : {{VALUE}};',
						],
						'condition' => [
							'template!' => 'template3'
						]
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Background::get_type(),
					[
						'name' 		=> 'background',
						'types' 	=> [ 'classic', 'gradient'],
						'selector' 	=> '{{WRAPPER}} .ova-blog.template3 .item .media .overlay',
						'exclude' 	=> ['image'],
						'condition' => [
							'template' => 'template3'
						]
					]
				);

				$this->add_responsive_control(
					'paddding_content',
					[
						'label' 		=> esc_html__( 'Padding', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-blog .item .ova-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
						]
					]
				);

				$this->add_responsive_control(
					'margin_content',
					[
						'label' 		=> esc_html__( 'Margin', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-blog .item .ova-content' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
						]
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Box_Shadow::get_type(),
					[
						'name' 		=> 'content_box_shadow',
						'selector' 	=> '{{WRAPPER}} .ova-blog .item .ova-content , {{WRAPPER}} .ova-blog.template1 .item ',
						'condition' => [
							'template!' => 'template3'
						]
					]
				);

				$this->add_responsive_control(
					'content_border_radius',
					[
						'label' 		=> esc_html__( 'Border Radius', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
						   '{{WRAPPER}} .ova-blog.template1 .item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						   '{{WRAPPER}} .ova-blog .item .ova-content' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
						],
						'condition' 	=> [
							'template!' => 'template3'
						]
					]
				);

				$this->add_control(
					'image_heading',
					[
						'label' 	=> esc_html__( 'Image', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::HEADING,
						'separator' => 'before'
					]
				);

				$this->add_responsive_control(
					'image_min_height',
					[
						'label' 		=> esc_html__( 'Min height (px)', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::SLIDER,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'range' 		=> [
							'px' => [
								'min' 	=> 50,
								'max' 	=> 500,
								'step' 	=> 1
							]
						],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-blog .item .media a img' => 'min-height: {{SIZE}}{{UNIT}};'
						]
					]
				);

				$this->add_responsive_control(
					'image_border_radius',
					[
						'label' 		=> esc_html__( 'Border Radius', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-blog .item .media a img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
							'{{WRAPPER}} .ova-blog .item .media' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
						]
					]
				);

			$this->end_controls_section(); // END SECTION TAB STYLE CONTENT

			// SECTION TAB STYLE TITLE
			$this->start_controls_section(
				'section_title',
				[
					'label' => esc_html__( 'Title', 'tripgo' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE
				]
			);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'title_typography',
						'selector' 	=> '{{WRAPPER}} .ova-blog .post-title a'
					]
				);

				$this->add_control(
					'color_title',
					[
						'label' 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-blog .post-title a' => 'color : {{VALUE}};'
						]
					]
				);

				$this->add_control(
					'color_title_hover',
					[
						'label' 	=> esc_html__( 'Color Hover', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-blog .post-title a:hover' => 'color : {{VALUE}};'
						]
					]
				);

				$this->add_responsive_control(
					'margin_title',
					[
						'label' 		=> esc_html__( 'Margin', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-blog .post-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
						]
					]
				);

			$this->end_controls_section(); // END SECTION TAB STYLE TITLE

			// START SECTION TAB STYLE DESCRIPTION
			$this->start_controls_section(
				'section_short_desc',
				[
					'label' 	=> esc_html__( 'Short Description', 'tripgo' ),
					'tab' 		=> \Elementor\Controls_Manager::TAB_STYLE,
					'condition' => [
						'template!' => 'template3'
					]
				]
			);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'short_desc_typography',
						'selector' 	=> '{{WRAPPER}} .ova-blog .short_desc p'
					]
				);

				$this->add_control(
					'color_short_desc',
					[
						'label' 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-blog .short_desc p' => 'color : {{VALUE}};'
						]
					]
				);

				$this->add_responsive_control(
					'margin_short_desc',
					[
						'label' 		=> esc_html__( 'Margin', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-blog .short_desc' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
						]
					]
				);

			$this->end_controls_section(); // END SECTION TAB STYLE DESCRIPTION

			$this->start_controls_section(
				'section_meta',
				[
					'label' => esc_html__( 'Meta', 'tripgo' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE
				]
			);

				$this->add_responsive_control(
					'meta_spacing',
					[
						'label' 		=> esc_html__( 'Spacing', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::SLIDER,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'range' 		=> [
							'px' => [
								'min' 	=> 0,
								'max' 	=> 300,
								'step' 	=> 1
							],
							'%' => [
								'min' => 0,
								'max' => 100
							]
						],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-blog .item .ova-content .post-meta' => 'gap: {{SIZE}}{{UNIT}};'
						]
					]
				);

				$this->add_responsive_control(
					'margin_meta',
					[
						'label' 		=> esc_html__( 'Margin', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-blog .item .ova-content .post-meta' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
						]
					]
				);

				$this->add_control(
					'meta_icon_heading',
					[
						'label' 	=> esc_html__( 'Icon ', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::HEADING,
						'separator' => 'before'
					]
				);

				$this->add_responsive_control(
					'meta_icon_size',
					[
						'label' 		=> esc_html__( 'Size', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::SLIDER,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'range' 		=> [
							'px' => [
								'min' 	=> 0,
								'max' 	=> 100,
								'step' 	=> 1,
							],
							'%' => [
								'min' => 0,
								'max' => 100,
							],
						],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-blog .item .ova-content .post-meta .item-meta i' => 'font-size: {{SIZE}}{{UNIT}};',
						],
					]
				);

				$this->add_responsive_control(
					'meta_icon_opacity',
					[
						'label' 		=> esc_html__( 'Opacity', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::SLIDER,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'range' 		=> [
							'px' => [
								'min' 	=> 0,
								'max' 	=> 1,
								'step' 	=> 0.01,
							],
						],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-blog .item .ova-content .post-meta .item-meta .left' => 'opacity: {{SIZE}};',
						],
					]
				);

				$this->add_control(
					'icon_color_meta',
					[
						'label' 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-blog .item .ova-content .post-meta .item-meta i' => 'color : {{VALUE}};',
						],
					]
				);

				$this->add_responsive_control(
					'margin_meta_icon',
					[
						'label' 		=> esc_html__( 'Margin', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-blog .item .ova-content .post-meta .item-meta .left' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

				$this->add_control(
					'meta_text_heading',
					[
						'label' 	=> esc_html__( 'Text ', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::HEADING,
						'separator' => 'before',
					]
				);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'meta_typography',
						'selector' 	=> '{{WRAPPER}} .ova-blog .item .post-meta .item-meta .right, {{WRAPPER}} .ova-blog .item .post-meta .item-meta .right a',
					]
				);

				$this->add_control(
					'text_color_meta',
					[
						'label' 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-blog .item .post-meta .item-meta .right' => 'color: {{VALUE}};',
							'{{WRAPPER}} .ova-blog .item .ova-content .post-meta .item-meta .right a' => 'color: {{VALUE}};',
							'{{WRAPPER}} .ova-blog .item .ova-content .post-meta .item-meta i' => 'color: {{VALUE}};'
						]
					]
				);

				$this->add_control(
					'link_color_meta',
					[
						'label' 	=> esc_html__( 'Link Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-blog .item .ova-content .post-meta .item-meta.wp-author .post-author a' => 'color: {{VALUE}};'
						]
					]
				);

				$this->add_control(
					'link_color_meta_hover',
					[
						'label' 	=> esc_html__( 'Link Color hover', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-blog .item .ova-content .post-meta .item-meta.wp-author .post-author:hover a' => 'color: {{VALUE}};'
						]
					]
				);

			$this->end_controls_section();

			// SECTION TAB STYLE READMORE
			$this->start_controls_section(
				'section_readmore',
				[
					'label' => esc_html__( 'Read More', 'tripgo' ),
					'tab' 	=> \Elementor\Controls_Manager::TAB_STYLE,
				]
			);

				$this->add_group_control(
					\Elementor\Group_Control_Typography::get_type(),
					[
						'name' 		=> 'readmore_typography',
						'selector' 	=> '{{WRAPPER}} .ova-blog .item .read-more',
					]
				);

				$this->add_control(
					'color_readmore',
					[
						'label' 	=> esc_html__( 'Color', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-blog .item .read-more' => 'color : {{VALUE}};',
						],
					]
				);

				$this->add_control(
					'color_readmore_hover',
					[
						'label' 	=> esc_html__( 'Color Hover', 'tripgo' ),
						'type' 		=> \Elementor\Controls_Manager::COLOR,
						'selectors' => [
							'{{WRAPPER}} .ova-blog .item .read-more:hover' => 'color : {{VALUE}};',
						],
					]
				);

				$this->add_responsive_control(
					'margin_readmore',
					[
						'label' 		=> esc_html__( 'Margin', 'tripgo' ),
						'type' 			=> \Elementor\Controls_Manager::DIMENSIONS,
						'size_units' 	=> [ 'px', '%', 'em', 'rem', 'custom' ],
						'selectors' 	=> [
							'{{WRAPPER}} .ova-blog .item .read-more' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
						],
					]
				);

			$this->end_controls_section(); // END SECTION TAB STYLE READMORE
		}

		/**
		 * Render HTML
		 */
		protected function render() {
			//  Get settings
			$settings = $this->get_settings_for_display();
			
			// Get template
			$template = tripgo_get_meta_data( 'template', $settings );

			// Get categories
			$categories = tripgo_get_meta_data( 'category', $settings );

			// Get posts per page
			$posts_per_page = tripgo_get_meta_data( 'total_count', $settings );

			// Order
			$order = tripgo_get_meta_data( 'order_by', $settings );

			// Orderby
			$orderby = tripgo_get_meta_data( 'orderby', $settings );

			// Columns
			$columns = tripgo_get_meta_data( 'number_column', $settings );

			// Text readmore
			$text_readmore = tripgo_get_meta_data( 'text_readmore', $settings );

			// Show thumbnail
			$show_thumbnail = tripgo_get_meta_data( 'show_thumbnail', $settings );

			// Show date
			$show_date = tripgo_get_meta_data( 'show_date', $settings );

			// Show author
			$show_author = tripgo_get_meta_data( 'show_author', $settings );

			// Show title
			$show_title = tripgo_get_meta_data( 'show_title', $settings );

			// Show short description
			$show_short_desc = tripgo_get_meta_data( 'show_short_desc', $settings );

			// Show read more
			$show_read_more = tripgo_get_meta_data( 'show_read_more', $settings );

			// Icon
			$icon = tripgo_get_meta_data( 'icon', $settings );

			// Order text
			$order_text = tripgo_get_meta_data( 'order_text', $settings, 20 );

			// Query arguments
			$args = [
				'post_type' 		=> 'post',
				'post_status'       => 'publish',
				'posts_per_page' 	=> $posts_per_page,
				'order' 			=> $order,
				'orderby' 			=> $orderby,
				'fields' 			=> 'ids'
			];

			// Categories
			if ( tripgo_array_exists( $categories ) ) {
				$args['category_name'] = implode( ',', $categories );
			}

			// Get blog ids
			$blog_ids = get_posts( $args );

			?>
			
			<ul class="ova-blog <?php echo esc_attr( $columns ); ?> <?php echo esc_attr( $template ); ?>">
				<?php if ( tripgo_array_exists( $blog_ids ) ):
					foreach ( $blog_ids as $blog_id ):
						// Blog title
						$blog_title = get_the_title( $blog_id );

						// Blog link detail
						$blog_link = get_the_permalink( $blog_id );

						// Thumbnail
						$thumbnail_id 	= get_post_thumbnail_id( $blog_id );
						$thumbnail_url 	= wp_get_attachment_image_url( $thumbnail_id, 'tripgo_thumbnail' );
						$thumbnail_alt 	= get_post_meta( $thumbnail_id, '_wp_attachment_image_alt', true );

						if ( !$thumbnail_url ) $thumbnail_url = \Elementor\Utils::get_placeholder_image_src();
						if ( !$thumbnail_alt ) $thumbnail_alt = get_the_title( $blog_id );
					?>
						<li class="item <?php if ( 'yes' != $show_thumbnail ) echo 'no-thumbnail'; ?>">
							<?php if ( 'yes' === $show_thumbnail ): ?>
								<div class="media">
						        	<a href="<?php echo esc_url( $thumbnail_url ); ?>" rel="bookmark" title="<?php echo esc_attr( $blog_title ); ?>">
						        		<img loading="lazy" decoding="async" src="<?php echo esc_url( $thumbnail_url ); ?>" alt="<?php echo esc_attr( $thumbnail_alt ); ?>" class="img-responsive">
						        		<div class="overlay"></div>
						        	</a>
						        </div>
						    <?php endif;

						    // Template 3
						    if ( 'yes'!= $show_thumbnail && 'template3' === $template ):
						    	$thumbnail_url = \Elementor\Utils::get_placeholder_image_src();
						    ?>
					        	<div class="media">
						        	<a href="<?php echo esc_url( $thumbnail_url ); ?>" rel="bookmark" title="<?php echo esc_attr( $blog_title ); ?>">
						        		<img loading="lazy" decoding="async" src="<?php echo esc_url( $thumbnail_url ); ?>" alt="<?php echo esc_attr( $thumbnail_alt ); ?>" class="img-responsive">
						        		<div class="overlay"></div>
						        	</a>
						        </div>
						    <?php endif; ?>
				        	<div class="ova-content">
			        			<ul class="post-meta">
								    <?php if ( 'yes' === $show_author ):
								    	$author_id = get_post_field( 'post_author', $blog_id );
								    ?>
										<li class="item-meta wp-author">
									    	<span class="left author"> 
									    	 	<i class="icomoon icomoon-profile-circle"></i>
									    	</span>
									    	<!-- far fa-user-circle -->
										    <span class="right post-author">
									        	<a href="<?php echo esc_url( get_author_posts_url( $author_id ) ); ?>">
									        		<?php echo wp_kses_post( get_the_author_meta( 'display_name', $author_id ) ); ?>
									        	</a>
										    </span>
									    </li>
									<?php endif;

									// Show date
									if ( 'yes' === $show_date ): ?>
									    <li class="item-meta post-date">
									        <span class="left date">
									        	<i class="icomoon icomoon-calander"></i>
									        </span>
									        <span class="right date">
									        	<?php echo esc_html( get_the_time( get_option( 'date_format' ) , $blog_id ) ); ?>
									        </span>
									    </li>
								    <?php endif; ?>
								</ul>
								<?php if ( 'yes' === $show_title ): ?>
						            <h4 class="post-title">
								        <a href="<?php echo esc_url( $blog_link ); ?>" rel="bookmark" title="<?php echo esc_attr( $blog_title ); ?>">
											<?php echo wp_kses_post( $blog_title ); ?>
									  	</a>
								    </h4>
							    <?php endif;

							    // Show description
							    if ( 'yes' === $show_short_desc ): ?>
								    <div class="short_desc">
								    	<?php echo wp_kses_post( wp_trim_words( get_the_excerpt( $blog_id ), $order_text ) ); ?>
								    </div>
								<?php endif;

								// Read more
								if ( 'yes' === $show_read_more ): ?>
								    <a class="read-more" href="<?php echo esc_url( $blog_link ); ?>">
								    	<span><?php echo esc_html( $text_readmore ); ?></span>
								    	<?php \Elementor\Icons_Manager::render_icon( $icon, [ 'aria-hidden' => 'true' ] ); ?>
								    </a>
							    <?php endif; ?>
				        	</div>
						</li>		
					<?php endforeach;
				endif; ?>
			</ul>
			<?php
		}
	}

	// init widget
	$widgets_manager->register( new Tripgo_Elementor_Blog_Grid() );
}