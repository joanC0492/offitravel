<?php
/**
 * Tour list: when month + destination return 0 results, show same destination
 * in other months with availability. Otherwise keep default Product related.
 *
 * @package tripgo-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main search result count (same logic as the tour list, GET params).
 *
 * @param array $get $_GET or equivalent.
 * @return int
 */
function offitravel_tour_list_main_search_count( $get ) {
	if ( ! function_exists( 'ovabrw_search_vehicle' ) ) {
		return 0;
	}
	$q = ovabrw_search_vehicle( $get );
	if ( ! $q || ! ( $q instanceof WP_Query ) ) {
		return 0;
	}
	return (int) $q->found_posts;
}

/**
 * Product IDs: same destination (rental), with at least one bookable day in a month
 * outside the user-selected month(s), same year as OVA month search.
 *
 * @param array $get $_GET.
 * @param int   $limit Max products.
 * @return int[]
 */
function offitravel_tour_suggestion_ids_alternate_months( $get, $limit = 8 ) {
	$get    = is_array( $get ) ? $get : array();
	$limit  = max( 1, (int) $limit );
	if ( ! function_exists( 'ovabrw_tour_excluded_on_pickup_timestamp' )
		|| ! function_exists( 'ovabrw_parse_pickup_date_for_search' ) ) {
		return array();
	}

	$dest = isset( $get['ovabrw_destination'] ) ? sanitize_text_field( (string) $get['ovabrw_destination'] ) : '';
	if ( $dest === '' || $dest === 'all' ) {
		return array();
	}

	$raw = isset( $get['ovabrw_pickup_date'] ) ? $get['ovabrw_pickup_date'] : ( isset( $get['pickup_date'] ) ? $get['pickup_date'] : '' );
	$parsed = ovabrw_parse_pickup_date_for_search( $raw );
	if ( ! $parsed || empty( $parsed['type'] ) || 'timestamp' === $parsed['type'] ) {
		return array();
	}

	$searched_months = array();
	$year            = (int) gmdate( 'Y' );
	if ( 'month' === $parsed['type'] ) {
		$searched_months   = array( (int) $parsed['month'] );
		$year              = isset( $parsed['year'] ) ? (int) $parsed['year'] : (int) gmdate( 'Y' );
	} elseif ( 'months' === $parsed['type'] && ! empty( $parsed['months'] ) ) {
		$searched_months = array_map( 'intval', (array) $parsed['months'] );
		$year            = isset( $parsed['year'] ) ? (int) $parsed['year'] : (int) gmdate( 'Y' );
	} else {
		return array();
	}
	$searched_months = array_values( array_unique( array_filter( $searched_months, 'is_numeric' ) ) );
	if ( empty( $searched_months ) ) {
		return array();
	}

	$rental_slug = defined( 'OVABRW_RENTAL' ) ? OVABRW_RENTAL : 'ovabrw_car_rental';
	$candidates  = get_posts(
		array(
			'post_type'        => 'product',
			'post_status'      => 'publish',
			'fields'           => 'ids',
			'posts_per_page'   => -1,
			'suppress_filters' => true,
			'tax_query'        => array(
				array(
					'taxonomy' => 'product_type',
					'field'    => 'slug',
					'terms'    => $rental_slug,
				),
			),
			'meta_query'       => array(
				array(
					'key'     => 'ovabrw_destination',
					'value'   => $dest,
					'compare' => 'LIKE',
				),
			),
		)
	);

	if ( empty( $candidates ) ) {
		return array();
	}

	$ids = array();
	foreach ( $candidates as $product_id ) {
		if ( count( $ids ) >= $limit ) {
			break;
		}
		$product_id = (int) $product_id;
		foreach ( range( 1, 12 ) as $m ) {
			if ( in_array( $m, $searched_months, true ) ) {
				continue;
			}
			$days_in_m = (int) date_i18n( 't', strtotime( sprintf( '%04d-%02d-01', $year, $m ) ) );
			for ( $d = 1; $d <= $days_in_m; $d++ ) {
				$ts = strtotime( sprintf( '%04d-%02d-%02d 12:00:00', $year, $m, $d ) );
				if ( ! $ts ) {
					continue;
				}
				if ( ! ovabrw_tour_excluded_on_pickup_timestamp( $product_id, $ts ) ) {
					$ids[] = $product_id;
					break 2;
				}
			}
		}
	}

	$ids = array_values( array_unique( array_map( 'intval', $ids ) ) );
	return array_slice( apply_filters( 'offitravel_tour_alternate_suggestion_ids', $ids, $get, $limit ), 0, $limit );
}

/**
 * @param array $slide_options Data attribute for ova product slider.
 * @return void
 */
function offitravel_tour_suggestions_default_slide_options( &$slide_options ) {
	$slide_options = array(
		'slidesPerView'     => 4,
		'slidesPerGroup'    => 1,
		'spaceBetween'      => 24,
		'pauseOnMouseEnter' => true,
		'loop'              => false,
		'autoplay'          => false,
		'delay'             => 3000,
		'speed'             => 500,
		'dots'              => true,
		'nav'               => true,
		'breakpoints'       => array(
			'0'    => array( 'slidesPerView' => 1 ),
			'600'  => array( 'slidesPerView' => 2 ),
			'960'  => array( 'slidesPerView' => 3 ),
			'1200' => array( 'slidesPerView' => 4 ),
		),
		'rtl'               => is_rtl(),
	);
}

/**
 * Enqueue script when shortcode runs (Elementor may not run wp_enqueue_scripts early).
 */
function offitravel_tour_suggestions_request_assets() {
	static $done = false;
	if ( $done ) {
		return;
	}
	$done = true;

	$rel = '/js/tour-suggestions-toggle.js';
	$path = get_stylesheet_directory() . $rel;
	if ( ! is_readable( $path ) ) {
		return;
	}

	wp_enqueue_script(
		'offitravel-tour-suggestions',
		get_stylesheet_directory_uri() . $rel,
		array( 'jquery' ),
		(string) filemtime( $path ),
		true
	);
	if ( wp_script_is( 'ovabrw-elementor-product-related', 'registered' ) ) {
		wp_enqueue_script( 'ovabrw-elementor-product-related' );
	}
	$parent_swiper = get_template_directory() . '/assets/libs/swiper/swiper-bundle.min.js';
	if ( is_readable( $parent_swiper ) && ! wp_script_is( 'swipe', 'enqueued' ) && ! wp_script_is( 'swiper', 'enqueued' ) ) {
		wp_enqueue_script(
			'swiper',
			get_template_directory_uri() . '/assets/libs/swiper/swiper-bundle.min.js',
			array( 'jquery' ),
			(string) @filemtime( $parent_swiper ),
			true
		);
	}
}

add_shortcode( 'offitravel_tour_suggestions_alternate', 'offitravel_tour_suggestions_alternate_shortcode' );

/**
 * Renders the alternate suggestions carousel. Place after the Product related widget
 * in the same tour-list page (Elementor: widget HTML o shortcode).
 *
 * Attributes: limit, show_title (yes|no).
 *
 * @param array|string $atts Shortcode attrs.
 * @return string
 */
function offitravel_tour_suggestions_alternate_shortcode( $atts ) {
	offitravel_tour_suggestions_request_assets();

	$atts = shortcode_atts(
		array(
			'limit'      => 8,
			'show_title' => 'no',
		),
		$atts,
		'offitravel_tour_suggestions_alternate'
	);
	$limit = max( 1, (int) $atts['limit'] );

	$get = isset( $_GET ) && is_array( $_GET ) ? wp_unslash( $_GET ) : array();

	$main_count = offitravel_tour_list_main_search_count( $get );
	$ids        = ( 0 === (int) $main_count ) ? offitravel_tour_suggestion_ids_alternate_months( $get, $limit ) : array();

	$slide_options = array();
	offitravel_tour_suggestions_default_slide_options( $slide_options );
	$slide_options = apply_filters( 'offitravel_tour_alternate_slide_options', $slide_options, $get );

	$has_suggestions   = ( 0 === (int) $main_count && ! empty( $ids ) );
	$suggestions_attrs = $has_suggestions
		? ' style="display:block;" data-offitravel-mode="suggestions" data-styled="1"'
		: ' style="display:none;" data-offitravel-mode="hidden" data-styled="1"';

	ob_start();
	?>
	<div
		class="offitravel-tour-suggestions-fallback"
		data-offitravel-suggestions="1"
		data-main-count="<?php echo esc_attr( (string) (int) $main_count ); ?>"
		<?php echo $has_suggestions ? 'data-offitravel-has-suggestions="1"' : 'data-offitravel-has-suggestions="0"'; ?>
		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- controlled attributes
		echo $suggestions_attrs;
		?>
	>
		<?php
		if ( 'yes' === $atts['show_title'] ) {
			echo '<h3 class="related-title offitravel-tour-suggestions-title">' . esc_html__( 'También te puede interesar', 'tripgo-child' ) . '</h3>';
		}
		if ( $has_suggestions && ! empty( $ids ) ) {
			$opts = wp_json_encode( $slide_options );
			?>
			<div class="elementor-ralated-slide offitravel-tour-suggestions-inner">
				<div class="ova-product-slider elementor-ralated" data-options="<?php echo esc_attr( $opts ? $opts : '{}' ); ?>">
					<div class="swiper swiper-loading">
						<div class="swiper-wrapper">
							<?php
							$orig_post = isset( $GLOBALS['post'] ) ? $GLOBALS['post'] : null;
							foreach ( $ids as $pid ) {
								$post_object = get_post( (int) $pid );
								if ( ! $post_object ) {
									continue;
								}
								setup_postdata( $GLOBALS['post'] = $post_object );
								echo '<div class="swiper-slide">';
								wc_get_template_part( 'content', 'product' );
								echo '</div>';
							}
							if ( $orig_post ) {
								setup_postdata( $GLOBALS['post'] = $orig_post );
							} else {
								wp_reset_postdata();
							}
							?>
						</div>
					</div>
					<?php if ( ! empty( $slide_options['nav'] ) ) : ?>
					<div class="swiper-nav">
						<div class="button-nav button-prev">
							<i class="arrow_carrot-left" aria-hidden="true"></i>
						</div>
						<div class="button-nav button-next">
							<i class="arrow_carrot-right" aria-hidden="true"></i>
						</div>
					</div>
					<?php endif; ?>
					<?php if ( ! empty( $slide_options['dots'] ) ) : ?>
					<div class="button-dots"></div>
					<?php endif; ?>
				</div>
			</div>
			<?php
		}
		?>
	</div>
	<?php if ( $has_suggestions ) : ?>
	<script>
		(function(){
			if ( document.body.classList.contains('offitravel-suggestions-inline-hidden') ) { return; }
			document.body.classList.add('offitravel-suggestions-inline-hidden');
			var sels = document.querySelectorAll( '.elementor-widget-ovabrw_product_related' );
			if ( sels && sels.length ) {
				for ( var i = 0; i < sels.length; i++ ) {
					if ( sels[ i ].getAttribute( 'data-offitravel-skip' ) ) { continue; }
					sels[ i ].style.display = 'none';
				}
			}
		})();
	</script>
	<?php endif; ?>
	<?php
	return (string) ob_get_clean();
}

