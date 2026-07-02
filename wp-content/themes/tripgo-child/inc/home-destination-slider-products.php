<?php
/**
 * Home destination slider: show tour products by product category.
 *
 * @package tripgo-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'OFFITRAVEL_HOME_POPULAR_PRODUCT_CAT_SLUG' ) ) {
	define( 'OFFITRAVEL_HOME_POPULAR_PRODUCT_CAT_SLUG', 'los-mas-populares' );
}

/**
 * Product category options for the Elementor category control.
 *
 * @return array
 */
function offitravel_home_destination_slider_product_category_options() {
	$options = array(
		'all' => esc_html__( 'All categories', 'tripgo-child' ),
	);

	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'orderby'   => 'name',
			'order'     => 'ASC',
		)
	);

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return $options;
	}

	foreach ( $terms as $term ) {
		$options[ $term->slug ] = $term->name;
	}

	return $options;
}

/**
 * Normalize Elementor's category setting into product_cat slugs.
 *
 * Empty means the home slider defaults to Los mas populares. Explicit "all"
 * means no taxonomy filter.
 *
 * @param array $settings Elementor widget settings.
 * @return string[]
 */
function offitravel_home_destination_slider_product_category_slugs( $settings ) {
	$category = isset( $settings['category'] ) ? $settings['category'] : array();

	if ( is_string( $category ) ) {
		$category = array_filter( array_map( 'trim', explode( ',', $category ) ) );
	} elseif ( ! is_array( $category ) ) {
		$category = array();
	}

	$slugs = array();
	$has_all = false;
	foreach ( $category as $slug ) {
		$raw_slug = trim( (string) $slug );
		$slug     = sanitize_title( $raw_slug );
		if ( 'all' === $slug ) {
			$has_all = true;
			continue;
		}
		if ( '' === $slug ) {
			continue;
		}
		if ( is_numeric( $raw_slug ) ) {
			$term = get_term( absint( $raw_slug ), 'product_cat' );
			if ( $term && ! is_wp_error( $term ) && ! empty( $term->slug ) ) {
				$slug = sanitize_title( $term->slug );
			}
		}
		$slugs[] = $slug;
	}

	if ( $has_all ) {
		return array();
	}

	$slugs = array_values( array_unique( $slugs ) );
	if ( empty( $slugs ) ) {
		$slugs = array( OFFITRAVEL_HOME_POPULAR_PRODUCT_CAT_SLUG );
	}

	return $slugs;
}

/**
 * Product query args for the home "Our Destination Slide" widget.
 *
 * @param array $settings Elementor widget settings.
 * @return array
 */
function offitravel_home_destination_slider_product_query_args( $settings ) {
	$total = isset( $settings['total_count'] ) ? absint( $settings['total_count'] ) : 8;
	if ( $total < 1 ) {
		$total = 8;
	}

	$order = isset( $settings['order'] ) ? strtoupper( (string) $settings['order'] ) : 'DESC';
	if ( ! in_array( $order, array( 'ASC', 'DESC' ), true ) ) {
		$order = 'DESC';
	}

	$orderby = isset( $settings['orderby_post'] ) ? sanitize_key( (string) $settings['orderby_post'] ) : 'date';
	if ( 'ova_destination_met_order_destination' === $orderby ) {
		$orderby = 'date';
	}

	$allowed_orderby = array( 'ID', 'title', 'date', 'rand', 'menu_order' );
	if ( ! in_array( $orderby, $allowed_orderby, true ) ) {
		$orderby = 'date';
	}

	$args = array(
		'post_type'           => 'product',
		'post_status'         => 'publish',
		'posts_per_page'      => $total,
		'ignore_sticky_posts' => true,
		'orderby'             => $orderby,
		'order'               => $order,
	);

	$category_slugs = offitravel_home_destination_slider_product_category_slugs( $settings );
	if ( ! empty( $category_slugs ) ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'product_cat',
				'field'    => 'slug',
				'terms'    => $category_slugs,
			),
		);
	}

	return $args;
}

/**
 * Default slider settings shared by the home override and the shortcode.
 *
 * @param array $settings Raw settings.
 * @return array
 */
function offitravel_home_product_category_slider_settings( $settings = array() ) {
	$settings = is_array( $settings ) ? $settings : array();

	return array_merge(
		array(
			'category'            => array( OFFITRAVEL_HOME_POPULAR_PRODUCT_CAT_SLUG ),
			'template'            => 'template1',
			'total_count'         => 8,
			'orderby_post'        => 'date',
			'order'               => 'DESC',
			'show_thumbnail'      => 'yes',
			'show_title'          => 'yes',
			'show_count'          => 'yes',
			'show_link_to_detail' => 'yes',
			'item_number'         => 3.7,
			'slides_to_scroll'    => 1,
			'margin_items'        => 24,
			'autoplay'            => 'yes',
			'pause_on_hover'      => 'yes',
			'autoplay_speed'      => 3000,
			'smartspeed'          => 500,
			'infinite'            => 'yes',
			'nav_control'         => 'yes',
			'dot_control'         => 'no',
			'wrapper_class'       => 'ova-destination-nav-right',
			'nav_top'             => -94,
		),
		$settings
	);
}

/**
 * Enqueue the same assets used by the original destination slider widget.
 *
 * @return void
 */
function offitravel_home_product_category_slider_assets() {
	if ( wp_style_is( 'ovades-elementor-destination-slider', 'registered' ) ) {
		wp_enqueue_style( 'ovades-elementor-destination-slider' );
	}
	if ( wp_style_is( 'swipe', 'registered' ) ) {
		wp_enqueue_style( 'swipe' );
	}
	if ( wp_script_is( 'swipe', 'registered' ) ) {
		wp_enqueue_script( 'swipe' );
	}
	if ( wp_script_is( 'ovades-elementor-destination-slider', 'registered' ) ) {
		wp_enqueue_script( 'ovades-elementor-destination-slider' );
	}
}

/**
 * Render product category slider using the original destination slider markup.
 *
 * @param array $settings Slider settings.
 * @return string
 */
function offitravel_home_product_category_slider_render( $settings ) {
	$args = offitravel_home_product_category_slider_settings( $settings );
	offitravel_home_product_category_slider_assets();

	$slide_options = array(
		'slidesPerView'     => $args['item_number'],
		'slidesPerGroup'    => $args['slides_to_scroll'],
		'spaceBetween'      => $args['margin_items'],
		'autoplay'          => 'yes' === $args['autoplay'],
		'pauseOnMouseEnter' => 'yes' === $args['pause_on_hover'],
		'delay'             => $args['autoplay_speed'] ? $args['autoplay_speed'] : 3000,
		'speed'             => $args['smartspeed'] ? $args['smartspeed'] : 500,
		'loop'              => 'yes' === $args['infinite'],
		'nav'               => 'yes' === $args['nav_control'],
		'dots'              => 'yes' === $args['dot_control'],
		'breakpoints'       => array(
			'0'    => array(
				'slidesPerView' => 1,
			),
			'600'  => array(
				'slidesPerView' => 2,
			),
			'1024' => array(
				'slidesPerView' => $args['item_number'],
			),
		),
		'rtl'               => is_rtl(),
	);

	$template = $args['template'];
	$products = new WP_Query( offitravel_home_destination_slider_product_query_args( $args ) );
	$wrapper_classes = array_filter(
		array_map(
			'sanitize_html_class',
			preg_split( '/\s+/', (string) $args['wrapper_class'] )
		)
	);
	$wrapper_class_attr = implode( ' ', array_unique( $wrapper_classes ) );
	$nav_top = is_numeric( $args['nav_top'] ) ? (float) $args['nav_top'] : -94;
	$scope_id = 'offitravel-product-category-slider-' . wp_rand( 1000, 999999 );

	ob_start();
	?>
	<div id="<?php echo esc_attr( $scope_id ); ?>" class="<?php echo esc_attr( $wrapper_class_attr ); ?>">
		<style>
			@media (min-width: 768px) {
				#<?php echo esc_html( $scope_id ); ?> .swiper-nav .button-nav {
					top: <?php echo esc_html( rtrim( rtrim( number_format( $nav_top, 2, '.', '' ), '0' ), '.' ) ); ?>px !important;
				}
			}
		</style>
		<div class="ova-destination-slider offitravel-home-product-category-slider">
			<?php if ( $products->have_posts() ) : ?>
				<div class="content content-<?php echo esc_attr( $template ); ?> slide-destination" data-options="<?php echo esc_attr( wp_json_encode( $slide_options ) ); ?>">
					<div class="swiper swiper-loading">
						<div class="swiper-wrapper">
							<?php while ( $products->have_posts() ) : $products->the_post(); ?>
								<div class="swiper-slide">
									<?php
									if ( 'template1' === $template ) {
										ovadestination_get_template( 'part/item-destination.php', $args );
									} elseif ( 'template2' === $template ) {
										ovadestination_get_template( 'part/item-destination2.php', $args );
									} elseif ( 'template3' === $template ) {
										ovadestination_get_template( 'part/item-destination3.php', $args );
									} else {
										ovadestination_get_template( 'part/item-destination.php', $args );
									}
									?>
								</div>
							<?php endwhile; ?>
						</div>
					</div>
					<?php if ( ! empty( $slide_options['nav'] ) ) : ?>
						<div class="swiper-nav">
							<div class="button-nav button-prev">
								<i class="icomoon icomoon-angle-left" aria-hidden="true"></i>
							</div>
							<div class="button-nav button-next">
								<i class="icomoon icomoon-angle-right" aria-hidden="true"></i>
							</div>
						</div>
					<?php endif; ?>
					<?php if ( ! empty( $slide_options['dots'] ) ) : ?>
						<div class="button-dots"></div>
					<?php endif; ?>
				</div>
			<?php endif; wp_reset_postdata(); ?>
		</div>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Shortcode for explicit product category sliders.
 *
 * Example:
 * [offitravel_product_category_slider category="los-mas-populares" total="8"]
 *
 * @param array|string $atts Shortcode attributes.
 * @return string
 */
function offitravel_product_category_slider_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'category'         => OFFITRAVEL_HOME_POPULAR_PRODUCT_CAT_SLUG,
			'total'            => 8,
			'template'         => 'template1',
			'orderby'          => 'date',
			'order'            => 'DESC',
			'items'            => 3.7,
			'slides_to_scroll' => 1,
			'margin'           => 24,
			'autoplay'         => 'yes',
			'pause_on_hover'   => 'yes',
			'autoplay_speed'   => 3000,
			'smartspeed'       => 500,
			'infinite'         => 'yes',
			'nav'              => 'yes',
			'dots'             => 'no',
			'class'            => 'ova-destination-nav-right',
			'nav_top'          => -94,
		),
		$atts,
		'offitravel_product_category_slider'
	);

	return offitravel_home_product_category_slider_render(
		array(
			'category'         => $atts['category'],
			'template'         => sanitize_key( $atts['template'] ),
			'total_count'      => absint( $atts['total'] ),
			'orderby_post'     => sanitize_key( $atts['orderby'] ),
			'order'            => strtoupper( (string) $atts['order'] ),
			'item_number'      => (float) $atts['items'],
			'slides_to_scroll' => absint( $atts['slides_to_scroll'] ),
			'margin_items'     => absint( $atts['margin'] ),
			'autoplay'         => sanitize_key( $atts['autoplay'] ),
			'pause_on_hover'   => sanitize_key( $atts['pause_on_hover'] ),
			'autoplay_speed'   => absint( $atts['autoplay_speed'] ),
			'smartspeed'       => absint( $atts['smartspeed'] ),
			'infinite'         => sanitize_key( $atts['infinite'] ),
			'nav_control'      => sanitize_key( $atts['nav'] ),
			'dot_control'      => sanitize_key( $atts['dots'] ),
			'wrapper_class'    => sanitize_text_field( $atts['class'] ),
			'nav_top'          => (float) $atts['nav_top'],
		)
	);
}
add_shortcode( 'offitravel_product_category_slider', 'offitravel_product_category_slider_shortcode' );
