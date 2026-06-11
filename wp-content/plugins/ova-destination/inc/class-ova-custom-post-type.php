<?php if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Class OVADES_Custom_Post_Type
 */
if ( !class_exists( 'OVADES_Custom_Post_Type' ) ) {

	class OVADES_Custom_Post_Type {

		/**
		 * Constructor
		 */
		public function __construct() {
			add_action( 'init', [ $this, 'register_post_type' ] );
			add_action( 'init', [ $this, 'register_taxonomy' ] );
		}
		
		/**
		 * Register post type
		 */
		public function register_post_type() {
			$labels = [
				'name'                  => _x( 'Destinations', 'Post Type General Name', 'ova-destination' ),
				'singular_name'         => _x( 'Destination', 'Post Type Singular Name', 'ova-destination' ),
				'menu_name'             => esc_html__( 'Destinations', 'ova-destination' ),
				'name_admin_bar'        => esc_html__( 'Destination', 'ova-destination' ),
				'archives'              => esc_html__( 'Item Archives', 'ova-destination' ),
				'attributes'            => esc_html__( 'Item Attributes', 'ova-destination' ),
				'parent_item_colon'     => esc_html__( 'Parent Item:', 'ova-destination' ),
				'all_items'             => esc_html__( 'Destinations', 'ova-destination' ),
				'add_new_item'          => esc_html__( 'Add new destination', 'ova-destination' ),
				'add_new'               => esc_html__( 'Add new', 'ova-destination' ),
				'new_item'              => esc_html__( 'New Item', 'ova-destination' ),
				'edit_item'             => esc_html__( 'Edit destination', 'ova-destination' ),
				'view_item'             => esc_html__( 'View Item', 'ova-destination' ),
				'view_items'            => esc_html__( 'View Items', 'ova-destination' ),
				'search_items'          => esc_html__( 'Search Item', 'ova-destination' ),
				'not_found'             => esc_html__( 'Not found', 'ova-destination' ),
				'not_found_in_trash'    => esc_html__( 'Not found in Trash', 'ova-destination' )
			];

			$args = [
				'description'         	=> esc_html__( 'Post Type Description', 'ova-destination' ),
				'labels'              	=> $labels,
				'supports'            	=> [ 'title', 'editor', 'thumbnail' ],
				'hierarchical'        	=> false,
				'public'              	=> true,
				'show_ui'             	=> true,
				'show_in_menu' 			=> 'ovabrw-settings',
				'menu_position'       	=> 3,
				'query_var'           	=> true,
				'has_archive'         	=> true,
				'exclude_from_search' 	=> true,
				'publicly_queryable'  	=> true,
				'rewrite'             	=> [
					'slug' => apply_filters( 'ova_destination_slug', _x( 'tour_destination', 'URL slug', 'ova-destination' ) )
				],
				'capability_type'     	=> 'post',
				'menu_icon'           	=> 'dashicons-location-alt'
			];

			// Register post type
			register_post_type( 'destination', $args );
		}

		/**
		 * Register taxonomy
		 */
		public function register_taxonomy() {
			$labels = [
				'name'                       => _x( 'Destination categories', 'Post Type General Name', 'ova-destination' ),
				'singular_name'              => _x( 'Category Destination', 'Post Type Singular Name', 'ova-destination' ),
				'menu_name'                  => esc_html__( 'Destination Categories', 'ova-destination' ),
				'all_items'                  => esc_html__( 'All Destination Categories', 'ova-destination' ),
				'parent_item'                => esc_html__( 'Parent Item', 'ova-destination' ),
				'parent_item_colon'          => esc_html__( 'Parent Item:', 'ova-destination' ),
				'new_item_name'              => esc_html__( 'New Item Name', 'ova-destination' ),
				'add_new_item'               => esc_html__( 'Add new category', 'ova-destination' ),
				'add_new'                    => esc_html__( 'Add new category', 'ova-destination' ),
				'edit_item'                  => esc_html__( 'Edit category', 'ova-destination' ),
				'view_item'                  => esc_html__( 'View Item', 'ova-destination' ),
				'separate_items_with_commas' => esc_html__( 'Separate items with commas', 'ova-destination' ),
				'add_or_remove_items'        => esc_html__( 'Add or remove items', 'ova-destination' ),
				'choose_from_most_used'      => esc_html__( 'Choose from the most used', 'ova-destination' ),
				'popular_items'              => esc_html__( 'Popular Items', 'ova-destination' ),
				'search_items'               => esc_html__( 'Search Items', 'ova-destination' ),
				'not_found'                  => esc_html__( 'Not Found', 'ova-destination' ),
				'no_terms'                   => esc_html__( 'No items', 'ova-destination' ),
				'items_list'                 => esc_html__( 'Items list', 'ova-destination' ),
				'items_list_navigation'      => esc_html__( 'Items list navigation', 'ova-destination' )
			];

			$args = [
				'labels'            	=> $labels,
				'hierarchical'      	=> true,
				'publicly_queryable' 	=> true,
				'public'            	=> true,
				'show_in_menu' 			=> true,
				'show_ui'           	=> true,
				'show_admin_column' 	=> true,
				'show_in_nav_menus' 	=> true,
				'show_tagcloud'     	=> false,
				'rewrite'            	=> [
					'slug'       => _x( 'cat_destination', 'Destination Slug', 'ova-destination' ),
					'with_front' => false,
					'feeds'      => true
				]
			];
			
			register_taxonomy( 'cat_destination', [ 'destination' ], $args );
		}
	}

	// init class
	new OVADES_Custom_Post_Type();
}