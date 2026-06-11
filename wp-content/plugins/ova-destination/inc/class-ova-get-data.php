<?php if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Pre get posts
 */
add_action( 'pre_get_posts', function( $query ) {
	if ( ( is_post_type_archive( 'destination' ) || is_tax( 'cat_destination' ) ) && !is_admin() ) {
		if ( $query->is_post_type_archive( 'destination' ) || $query->is_tax( 'cat_destination' ) ) {
			$query->set( 'post_type', [ 'destination' ] );
			$query->set( 'posts_per_page', get_theme_mod( 'ova_destination_total_record', 7 ) );

			if ( isset( $_GET['total'] ) && is_numeric( $_GET['total'] ) ) {
				$query->set( 'posts_per_page', $_GET['total'] );
			}

			// Orderby
			$query->set( 'orderby', 'meta_value_num' );
            $query->set( 'meta_type', 'NUMERIC' );
            $query->set( 'meta_key', 'ova_destination_met_order_destination' );

            // Order
            $query->set( 'order', 'DESC' );
		}
	}
});

/**
 * Get data destination
 */
if ( !function_exists( 'ovadestination_get_data_destination_el' ) ) {
	function ovadestination_get_data_destination_el( $args ) {
		$category = $args['category'];

		if ( $category == 'all' ) {
			$args_new = [
				'post_type' 		=> 'destination',
				'post_status' 		=> 'publish',
				'posts_per_page' 	=> $args['total_count'],
				'offset' 			=> $args['offset']
			];
		} else {
			$args_new = [
				'post_type' 		=> 'destination',
				'post_status' 		=> 'publish',
				'posts_per_page' 	=> $args['total_count'],
				'offset' 			=> $args['offset'],
				'tax_query' 		=> [
					[
						'taxonomy' => 'cat_destination',
						'field'    => 'slug',
						'terms'    => $category
					]
				]
			];
		}

		$args_destination_order = [];
		if( $args['orderby_post'] === 'ova_destination_met_order_destination' ) {
			$args_destination_order = [
				'meta_key'   	=> $args['orderby_post'],
				'orderby'    	=> 'meta_value_num',
				'meta_type'  	=> 'NUMERIC',
				'order'   		=> $args['order']
			];
		} else { 
			$args_destination_order = [
				'orderby' => $args['orderby_post'],
				'order'   => $args['order']
			];
		}

		$args_destination 	= array_merge( $args_new, $args_destination_order );
		$destinations  		= new \WP_Query($args_destination);

		return $destinations;
	}
}

/**
 * Get data destination slider
 */
if ( !function_exists( 'ovadestination_get_data_destination_slider_el' ) ) {
	function ovadestination_get_data_destination_slider_el( $args ) {
		// Query products instead of destinations
		$args_new = [
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => $args['total_count'],
			// Only products in product_cat term ID 54
			'tax_query'      => [
				[
					'taxonomy' => 'product_cat',
					'field'    => 'term_id',
					'terms'    => 54,
				],
			],
		];

		// Order configuration
		$args_destination_order = [];

		// If the widget is configured to order by the old
		// destination meta key, fall back to date ordering
		if ( isset( $args['orderby_post'] ) && $args['orderby_post'] === 'ova_destination_met_order_destination' ) {
			$args_destination_order = [
				'orderby' => 'date',
				'order'   => isset( $args['order'] ) ? $args['order'] : 'DESC',
			];
		} else {
			if ( isset( $args['orderby_post'] ) ) {
				$args_destination_order['orderby'] = $args['orderby_post'];
			}

			if ( isset( $args['order'] ) ) {
				$args_destination_order['order'] = $args['order'];
			}
		}

		$args_destination = array_merge( $args_new, $args_destination_order );
		$destinations     = new \WP_Query( $args_destination );

		return $destinations;
	}
}

/**
 * Get product ids by destination id
 */
if ( !function_exists( 'ovadestination_get_product_ids_by_id' ) ) {
	function ovadestination_get_product_ids_by_id( $id ) {
		$product_ids = get_posts([
			'post_type' 		=> 'product',
			'post_status' 		=> 'publish',
			'posts_per_page' 	=> -1,
			'field' 			=> 'ids',
			'meta_query' 		=> [
				[
					'meta_key' => 'ovabrw_destination',
					'compare'  => 'LIKE',
					'value'    => $id
				]
			]
		]);

		return $product_ids;
	}
}