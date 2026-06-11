<?php
/**
 * Gutenberg Block for Currency Selector
 *
 * @package woo-multi-currency
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WOOMULTI_CURRENCY_Block {
	protected $settings;
	/**
	 * Register block
	 */
	public function __construct() {
		$this->settings = WOOMULTI_CURRENCY_F_Data::get_ins();
		if ( $this->settings->get_enable() ) {
			add_action( 'init', array( $this, 'register_block' ) );
			add_action( 'rest_api_init', array( $this, 'register_rest_route' ) );
		}
	}

	/**
	 * Register REST API route
	 */
	public function register_rest_route() {
		register_rest_route(
			'wmc/v1',
			'/preview',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'get_preview_html' ),
				'permission_callback' => function() {
					// Allow access if user can edit posts/pages or template parts
					return current_user_can( 'edit_posts' ) || current_user_can( 'edit_pages' ) || current_user_can( 'edit_theme_options' );
				},
			)
		);
	}

	/**
	 * Ensure frontend classes and assets are loaded for REST API
	 */
	private function ensure_frontend_assets() {
		// Load data class first
		if ( ! class_exists( 'WOOMULTI_CURRENCY_F_Data' ) ) {
			$data_path = WOOMULTI_CURRENCY_F_INCLUDES . 'data.php';
			if ( file_exists( $data_path ) ) {
				require_once $data_path;
			}
		}

		// Load functions
		if ( ! function_exists( 'wmc_get_template' ) ) {
			$func_path = WOOMULTI_CURRENCY_F_INCLUDES . 'functions.php';
			if ( file_exists( $func_path ) ) {
				require_once $func_path;
			}
		}

		// Check if shortcode class is loaded, if not load it
		if ( ! class_exists( 'WOOMULTI_CURRENCY_F_Frontend_Shortcode' ) ) {
			$shortcode_path = WOOMULTI_CURRENCY_F_FRONTEND . 'shortcode.php';
			if ( file_exists( $shortcode_path ) ) {
				require_once $shortcode_path;
			}
		}

		// Check if design class is loaded, if not load it
		if ( ! class_exists( 'WOOMULTI_CURRENCY_F_Frontend_Design' ) ) {
			$design_path = WOOMULTI_CURRENCY_F_FRONTEND . 'design.php';
			if ( file_exists( $design_path ) ) {
				require_once $design_path;
			}
		}

		// Initialize settings if not already
		if ( class_exists( 'WOOMULTI_CURRENCY_F_Data' ) ) {
			WOOMULTI_CURRENCY_F_Data::get_ins();
		}

		// Register frontend styles (same as frontend/design.php)
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			wp_register_style(
				'woo-multi-currency',
				WOOMULTI_CURRENCY_F_CSS . 'woo-multi-currency.css',
				array(),
				WOOMULTI_CURRENCY_F_VERSION
			);
			wp_register_style(
				'wmc-flags',
				WOOMULTI_CURRENCY_F_CSS . 'flags-64.css',
				array(),
				WOOMULTI_CURRENCY_F_VERSION
			);
		} else {
			wp_register_style(
				'woo-multi-currency',
				WOOMULTI_CURRENCY_F_CSS . 'woo-multi-currency.min.css',
				array(),
				WOOMULTI_CURRENCY_F_VERSION
			);
			wp_register_style(
				'wmc-flags',
				WOOMULTI_CURRENCY_F_CSS . 'flags-64.min.css',
				array(),
				WOOMULTI_CURRENCY_F_VERSION
			);
		}

		// Enqueue styles
		wp_enqueue_style( 'woo-multi-currency' );
		wp_enqueue_style( 'wmc-flags' );

		// Register and enqueue frontend JS
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			wp_register_script(
				'woo-multi-currency-switcher',
				WOOMULTI_CURRENCY_F_JS . 'woo-multi-currency-switcher.js',
				array( 'jquery' ),
				WOOMULTI_CURRENCY_F_VERSION,
				true
			);
			wp_register_script(
				'woo-multi-currency',
				WOOMULTI_CURRENCY_F_JS . 'woo-multi-currency.js',
				array( 'jquery' ),
				WOOMULTI_CURRENCY_F_VERSION,
				true
			);
		} else {
			wp_register_script(
				'woo-multi-currency-switcher',
				WOOMULTI_CURRENCY_F_JS . 'woo-multi-currency-switcher.min.js',
				array( 'jquery' ),
				WOOMULTI_CURRENCY_F_VERSION,
				true
			);
			wp_register_script(
				'woo-multi-currency',
				WOOMULTI_CURRENCY_F_JS . 'woo-multi-currency.min.js',
				array( 'jquery' ),
				WOOMULTI_CURRENCY_F_VERSION,
				true
			);
		}

		// Enqueue scripts
		wp_enqueue_script( 'woo-multi-currency-switcher' );
		wp_enqueue_script( 'woo-multi-currency' );

		// Localize script with params
		if ( class_exists( 'WOOMULTI_CURRENCY_F_Data' ) ) {
			$settings = WOOMULTI_CURRENCY_F_Data::get_ins();
			$params = array(
				'use_session'        => 0,
				'do_not_reload_page' => 0,
				'ajax_url'           => admin_url( 'admin-ajax.php' ),
				'switch_by_js'       => 0,
				'switch_container'   => 0,
			);
			wp_localize_script( 'woo-multi-currency-switcher', '_woocommerce_multi_currency_params', $params );

			$cache_nonce_check = is_plugin_active( 'litespeed-cache/litespeed-cache.php' );
			// Also localize main script to ensure params are available
			$woo_params = array(
				'enableCacheCompatible'          => $settings->get_param( 'cache_compatible' ),
				'ajaxUrl'                        => admin_url( 'admin-ajax.php' ),
				'nonce'                          => wp_create_nonce('wmc_currency_nonce'),
				'cache_nonce'                    => apply_filters( 'wmc_frontend_ignore_nonce_verify', $cache_nonce_check ),
				'switchByJS'                     => 0,
				'current_currency'               => $settings->get_current_currency(),
			);
			wp_localize_script( 'woo-multi-currency', 'wooMultiCurrencyParams', $woo_params );
		}
	}

	/**
	 * Get all enqueued styles and scripts URLs
	 */
	private function get_enqueued_assets() {
		$styles_urls = array();
		$scripts_urls = array();

		global $wp_scripts;
		global $wp_styles;

		// Get styles - only plugin styles, exclude theme/editor CSS
		$plugin_styles = array(
			'woo-multi-currency',
			'wmc-block-main-style',
			'wmc-flags',
			'wmc-block-editor-style'
		);
		
		if ( isset( $wp_styles->queue ) ) {
			foreach ( $wp_styles->queue as $handle ) {
				// Only include plugin styles
				if ( in_array( $handle, $plugin_styles ) && isset( $wp_styles->registered[ $handle ] ) ) {
					$src = $wp_styles->registered[ $handle ]->src;
					if ( ! empty( $src ) ) {
						// Convert relative URLs to absolute
						if ( strpos( $src, 'http' ) !== 0 ) {
							$src = site_url( $src );
						}
						$styles_urls[] = $src;
					}
				}
			}
		}

		// Get scripts (only main plugin scripts, not dependencies)
		$plugin_scripts = array(
			'woo-multi-currency-switcher',
			'woo-multi-currency'
		);

		if ( isset( $wp_scripts->queue ) ) {
			foreach ( $wp_scripts->queue as $handle ) {
				if ( in_array( $handle, $plugin_scripts ) && isset( $wp_scripts->registered[ $handle ] ) ) {
					$src = $wp_scripts->registered[ $handle ]->src;
					if ( ! empty( $src ) ) {
						if ( strpos( $src, 'http' ) !== 0 ) {
							$src = site_url( $src );
						}
						$scripts_urls[] = $src;
					}
				}
			}
		}

		return array(
			'styles' => $styles_urls,
			'scripts' => $scripts_urls
		);
	}

	/**
	 * Get preview HTML via REST API
	 */
	public function get_preview_html( $request ) {
		$params = $request->get_json_params();

		$layout    = isset( $params['layout'] ) ? sanitize_text_field( $params['layout'] ) : '';
		$flag_size = isset( $params['flagSize'] ) ? floatval( $params['flagSize'] ) : 0.6;
		$direction = isset( $params['direction'] ) ? sanitize_text_field( $params['direction'] ) : 'bottom';

		// Ensure frontend assets are loaded
		$this->ensure_frontend_assets();

		// Build shortcode
		$shortcode_atts = array();

		if ( ! empty( $layout ) ) {
			$shortcode_atts[] = '_' . $layout;
		}

		if ( ! empty( $flag_size ) && $flag_size != 0.6 ) {
			$shortcode_atts[] = 'flag_size=' . $flag_size;
		}

		if ( ! empty( $direction ) && $direction !== 'bottom' ) {
			$shortcode_atts[] = 'direction=' . $direction;
		}

		$shortcode = '[woo_multi_currency' . implode( ' ', $shortcode_atts ) . ']';

		$html = do_shortcode( $shortcode );

		// Get enqueued assets URLs
		$assets = $this->get_enqueued_assets();

		return rest_ensure_response( array(
			'html' => $html,
			'assets' => $assets,
		) );
	}

	/**
	 * Register Gutenberg block
	 */
	public function register_block() {
		// Check if Gutenberg is available
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		// Register the block JavaScript
		wp_register_script(
			'wmc-block-editor',
			WOOMULTI_CURRENCY_F_BLOCKS . 'index.js',
			array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-i18n', 'wp-components', 'wp-api-fetch' ),
			WOOMULTI_CURRENCY_F_VERSION,
			true
		);

		// Register plugin main CSS for consistent styling in all block preview contexts
		wp_register_style(
			'wmc-block-main-style',
			WOOMULTI_CURRENCY_F_CSS . 'woo-multi-currency.css',
			array(),
			WOOMULTI_CURRENCY_F_VERSION
		);

		// Register flags CSS
		wp_register_style(
			'wmc-flags-style',
			WOOMULTI_CURRENCY_F_CSS . 'flags-64.css',
			array(),
			WOOMULTI_CURRENCY_F_VERSION
		);

		// Register block styles if exists
		$css_path = WOOMULTI_CURRENCY_F_DIR . 'css' . DIRECTORY_SEPARATOR . 'blocks' . DIRECTORY_SEPARATOR . 'editor.css';
		if ( file_exists( $css_path ) ) {
			wp_register_style(
				'wmc-block-editor-style',
				WOOMULTI_CURRENCY_F_CSS . 'blocks/editor.css',
				array(),
				WOOMULTI_CURRENCY_F_VERSION
			);
		}

		$args = array(
			'editor_script' => 'wmc-block-editor',
			'render_callback' => array( $this, 'render_callback' ),
			'style' => array( 'wmc-block-main-style', 'wmc-flags-style' ),
			'editor_style' => array( 'wmc-block-main-style', 'wmc-flags-style' ),
		);

		// Add block-specific editor style if available
		if ( file_exists( $css_path ) ) {
			if ( is_array( $args['editor_style'] ) ) {
				$args['editor_style'][] = 'wmc-block-editor-style';
			} else {
				$args['editor_style'] = array( 'wmc-block-main-style', 'wmc-flags-style', 'wmc-block-editor-style' );
			}
		}

		register_block_type(
			WOOMULTI_CURRENCY_F_INCLUDES . 'blocks',
			$args
		);
	}

	/**
	 * Render callback for the block (frontend)
	 *
	 * @param array  $attributes Block attributes.
	 * @param string $content    Block content.
	 *
	 * @return string Rendered block HTML.
	 */
	public function render_callback( $attributes ) {
		// Ensure frontend assets are loaded for proper rendering
		$this->ensure_frontend_assets();
		
		// Build shortcode attributes
		$shortcode_atts = array();

		if ( ! empty( $attributes['layout'] ) ) {
			$shortcode_atts[] = '_' . $attributes['layout'];
		}

		if ( ! empty( $attributes['flagSize'] ) && $attributes['flagSize'] != 0.6 ) {
			$shortcode_atts[] = 'flag_size=' . $attributes['flagSize'];
		}

		if ( ! empty( $attributes['direction'] ) && $attributes['direction'] !== 'bottom' ) {
			$shortcode_atts[] = 'direction=' . $attributes['direction'];
		}

		$shortcode = '[woo_multi_currency' . implode( ' ', $shortcode_atts ) . ']';

		return do_shortcode( $shortcode );
	}
}

new WOOMULTI_CURRENCY_Block();