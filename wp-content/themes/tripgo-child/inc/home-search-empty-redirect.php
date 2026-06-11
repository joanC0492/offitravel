<?php
/**
 * Front page: si el buscador OVA envía destino "all" y sin meses, no ir a tour-list:
 * permanecer en la home con ancla a la sección de categorías y tab "Todos".
 *
 * ID de sección Elementor (CSS ID del widget o de la sección que lo contiene): categoria-viajes.
 * Submit vacío en la misma página solo cambia el hash → sin recarga; se usa hashchange + activateTodosIfAnchored.
 * Filtro: offitravel_home_category_section_id
 *
 * @package tripgo-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return void
 */
function offitravel_enqueue_home_search_empty_redirect() {
	if ( is_admin() || ! is_front_page() ) {
		return;
	}
	$path = get_stylesheet_directory() . '/js/home-search-empty-redirect.js';
	if ( ! is_readable( $path ) ) {
		return;
	}
	wp_enqueue_script(
		'offitravel-home-search-empty',
		get_stylesheet_directory_uri() . '/js/home-search-empty-redirect.js',
		array( 'jquery' ),
		(string) filemtime( $path ),
		true
	);
	wp_localize_script(
		'offitravel-home-search-empty',
		'offitravelHomeSearch',
		array(
			'homeUrl'   => esc_url( home_url( '/' ) ),
			'sectionId' => sanitize_html_class( apply_filters( 'offitravel_home_category_section_id', 'categoria-viajes' ) ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'offitravel_enqueue_home_search_empty_redirect', 35 );
