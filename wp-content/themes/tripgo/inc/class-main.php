<?php if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Class Tripgo_Main
 */
if ( !class_exists( 'Tripgo_Main' ) ) {

	class Tripgo_Main {

		/**
		 * Constructor
		 */
		public function __construct() {
			// Add theme support
			add_action( 'after_setup_theme', [ $this, 'tripgo_theme_support' ] );
	
			// Register Menu
			add_action( 'init', [ $this, 'tripgo_register_menus' ] );

			// Load google font from customize
			add_action( 'wp_enqueue_scripts', [ $this, 'tripgo_load_google_fonts' ] );

			// Add Body class
			add_filter( 'body_class', [ $this, 'tripgo_body_classes' ] );
			
			// Enqueue CSS, Javascript
			add_action( 'wp_enqueue_scripts', [ $this, 'tripgo_enqueue_scripts' ] );
            
            // Enqueue style from customize
			add_action( 'wp_enqueue_scripts', [ $this, 'tripgo_enqueue_customize' ], 11 );
        }

        /**
         * Theme support
         */
		public function tripgo_theme_support() {
			$GLOBALS['content_width'] = apply_filters( 'tripgo_content_width', 800 );
		    
			// Add title tag
		    add_theme_support( 'title-tag' );

		    // Adds RSS feed links to <head> for posts and comments.
		    add_theme_support( 'automatic-feed-links' );

		    // Switches default core markup for search form, comment form, and comments    
		    // to output valid HTML5.
		    add_theme_support( 'html5', [ 'search-form', 'comment-form', 'comment-list' ] );
		    add_theme_support( 'responsive-embeds' );

		    // See http://codex.wordpress.org/Post_Formats
		    add_theme_support( 'post-formats', [ 'image', 'gallery', 'audio', 'video' ] );
		    add_theme_support( 'post-thumbnails' );
		    add_theme_support( 'custom-header' );
		    add_theme_support( 'custom-background' );
		    add_theme_support( 'responsive-embeds' );
		    add_theme_support( 'woocommerce' );
		    add_theme_support( 'ova_framework_hf_el' );
		    add_filter( 'gutenberg_use_widgets_block_editor', '__return_false' );
            add_filter( 'use_widgets_block_editor', '__return_false' );

            // Thumbnail size
		    add_image_size( 'tripgo_thumbnail', 768, 660, true );
		    add_image_size( 'tripgo_blog_thumbnail', 416, 280, true );
		    add_image_size( 'tripgo_product_slider', 636, 426, true );
		    add_image_size( 'tripgo_product_gallery', 800, 450, true );
		}

		/**
		 * Register menus
		 */
		public function tripgo_register_menus() {
		  	register_nav_menus([
		  		'primary' => esc_html__( 'Primary Menu', 'tripgo' )
		  	]);
		}

		/**
		 * Load google fonts
		 */
		public function tripgo_load_google_fonts() {
		    $fonts_url = '';

		    // Get custom font
		    $custom_font = get_theme_mod( 'ova_custom_font', '["HK Grotesk", "300:400:500:600:700:800:900"]' );

		    // Default primary font
		    $default_primary_font = json_decode( tripgo_default_primary_font() );

		    $custom_fonts = get_theme_mod( 'ova_custom_font', '' );

		    // Primary Font 
		    $primary_font = json_decode( get_theme_mod( 'primary_font' ) ) ? json_decode( get_theme_mod( 'primary_font' ) ) : $default_primary_font;
		    $primary_font_family = $primary_font->font;
		    $primary_font_weights_string = $primary_font->regularweight ? $primary_font->regularweight : '100,200,300,400,500,600,700,800,900';
		    $is_custom_primary_font = $custom_fonts != '' ? !strpos($primary_font_family, $custom_fonts) : true;    
		    
		    $general_flag = _x( 'on', $primary_font_family.': on or off', 'tripgo' );
		 
		    if ( 'off' !== $general_flag ) {
		        $font_families = [];
		 
		        if ( 'off' !== $general_flag && $is_custom_primary_font ) {
		            $font_families[] = $primary_font_family.':'.$primary_font_weights_string;
		        }
		        if ( $font_families != null ) {
		          	$query_args = [
		          		'family' => urlencode( implode( '|', $font_families ) )
		          	];  
		          	$fonts_url = add_query_arg( $query_args, '//fonts.googleapis.com/css' );
		        }
		    }
		 
		    $google_fonts =  esc_url_raw( $fonts_url );

		    // Load google font from customize
		    if ( !str_contains( $custom_font, $primary_font_family ) ) {
		    	wp_enqueue_style( 'ova-google-fonts', $google_fonts, [], null );
		    }
		}

		/**
		 * Body classes
		 */
		public function tripgo_body_classes( $classes ) {
            global $is_lynx, $is_gecko, $is_IE, $is_opera, $is_NS4, $is_safari, $is_chrome, $is_iphone;

            if ( $is_lynx ) {
                $classes[] = 'lynx';
            } elseif ( $is_gecko ) {
                $classes[] = 'gecko';
            } elseif ( $is_opera ) {
                $classes[] = 'opera';
            } elseif ( $is_NS4 ) {
                $classes[] = 'ns4';
            } elseif ( $is_safari ) {
                $classes[] = 'safari';
            } elseif ( $is_chrome ) {
                $classes[] = 'chrome';
            } elseif ( $is_IE ) {
                $classes[] = 'ie';
            }

            // is iphone
            if ( $is_iphone ) {
                $classes[] = 'iphone';
            }

            // Adds a class to blogs with more than 1 published author.
            if ( is_multi_author() ) {
                $classes[] = 'group-blog';
            }

            // Add class when using homepage template + featured image.
            if ( has_post_thumbnail() ) {
                $classes[] = 'has-post-thumbnail';
            }

            $classes[] = apply_filters( 'tripgo_theme_sidebar', '' );
            $classes[] = tripgo_woo_sidebar();

            // Wide site
            $wide_site = apply_filters( 'tripgo_wide_site', '' );
            if ( $wide_site == 'boxed' ) {
				$classes[] = 'container_boxed';
            }

            return $classes;
        }

        /**
         * Enqueue scripts
         */
		public function tripgo_enqueue_scripts() {
		    // enqueue the javascript that performs in-link comment reply fanciness
		    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		        wp_enqueue_script( 'comment-reply' ); 
		    }

		    // Font Icon
		    wp_enqueue_style( 'ova-ovaicon', TRIPGO_URI.'/assets/libs/ovaicon/font/ovaicon.css' );

		    // Icomoon
		    wp_enqueue_style( 'ova-icomoon', TRIPGO_URI.'/assets/libs/icomoon/style.css' );

		    // Flaticon
		    wp_enqueue_style( 'ova-flaticon', TRIPGO_URI.'/assets/libs/flaticon/font/flaticon_tripgo.css' );

		    // Flaticon 2
		    wp_enqueue_style( 'ova-flaticon2', TRIPGO_URI.'/assets/libs/flaticon2/font/flaticon_tripgo2.css' );

		    // Masonry
		    wp_enqueue_script( 'ova-masonry', TRIPGO_URI.'/assets/libs/masonry.min.js', [ 'jquery' ], null, true );

		    // Swiper
		    if ( !tripgo_is_elementor_active() ) {
		    	wp_enqueue_style( 'swiper', TRIPGO_URI.'/assets/libs/swiper/swiper-bundle.min.css' );
		    	wp_enqueue_script( 'swiper', TRIPGO_URI.'/assets/libs/swiper/swiper-bundle.min.js', [ 'jquery' ], false, true );
		    } else {
		    	wp_enqueue_style( 'swiper' );
		    	wp_enqueue_script( 'swiper' );
		    }

		    // Theme script
		    wp_enqueue_script( 'tripgo-script', TRIPGO_URI.'/assets/js/script.js', [ 'jquery' ], null, true );
		    wp_localize_script( 'tripgo-script', 'ScrollUpText', [
		    	'value' => esc_html__( 'Go to top', 'tripgo' )
		    ]);
		    wp_enqueue_style( 'tripgo-style', get_template_directory_uri() . '/style.css' );
		}
		
		/**
		 * Enqueue customize
		 */
        public function tripgo_enqueue_customize() {
        	$css = '';
           	
           	// Get primary color
			$primary_color 			= get_theme_mod( 'primary_color', '#FD4C5C' );
			$primary_color_hover 	= get_theme_mod( 'primary_color_hover', '#E64251' );

			// Get secondary color
			$secondary_color 		= get_theme_mod( 'secondary_color', '#00BB98' );
			$secondary_color_hover 	= get_theme_mod( 'secondary_color_hover', '#05977C' );

			// Get heading color
			$heading_color = get_theme_mod( 'heading_color', '#111B19' );

			// Get text color
			$text_color = get_theme_mod( 'text_color', '#444444' );

			// Get light color
			$light_color = get_theme_mod( 'light_color', '#999999' );

			// Get border color
			$border_color = get_theme_mod( 'border_color', '#E6E6E6' );

			// Get first background
			$first_background = get_theme_mod( 'first_background', '#F5F5F5' );

			// Get second background
			$second_background = get_theme_mod( 'second_background', '#F2FBFA' );

			// Get third background
			$third_background = get_theme_mod( 'third_background', '#1A1A3D' );

			// Primary font
			$default_primary_font 	= json_decode( tripgo_default_primary_font() );
			$primary_font 			= json_decode( get_theme_mod( 'primary_font' ) ) ? json_decode( get_theme_mod( 'primary_font' ) ) : $default_primary_font;
			$primary_font_family 	= $primary_font->font;

			// General Typo
			$general_font_size 		= get_theme_mod( 'general_font_size', '16px' );
			$general_line_height 	= get_theme_mod( 'general_line_height', '1.63em' );
			$general_letter_space 	= get_theme_mod( 'general_letter_space', '0px' );
			
			// Width Sidebar
			$global_layout_sidebar 	= apply_filters( 'tripgo_get_layout', '' );
			$width_sidebar 			= is_array( $global_layout_sidebar ) ? $global_layout_sidebar[1] : '0';

			// Container width
			$container_width = get_theme_mod( 'global_boxed_container_width', '1290' );
			$container_width_break = $container_width + 60;
			$boxed_offset = get_theme_mod( 'global_boxed_offset', '20' );

			// WooCommerce Sidebar
			$woo_archive_layout = get_theme_mod( 'woo_archive_layout', 'layout_1c' );
			$woo_sidebar_width 	= get_theme_mod( 'woo_sidebar_width', '320' );

            $css .= '--primary: '.$primary_color.';';
            $css .= '--primary-hover: '.$primary_color_hover.';';
            $css .= '--secondary: '.$secondary_color.';';
            $css .= '--secondary-hover: '.$secondary_color_hover.';';
            $css .= '--heading: '.$heading_color.';';
            $css .= '--text: '.$text_color.';';
            $css .= '--light: '.$light_color.';';
            $css .= '--border: '.$border_color.';';
            $css .= '--first-background: '.$first_background.';';
            $css .= '--second-background: '.$second_background.';';
            $css .= '--third-background: '.$third_background.';';
            $css .= '--primary-font: '.$primary_font_family.';';
            $css .= '--font-size: '.$general_font_size.';';
            $css .= '--line-height: '.$general_line_height.';';
            $css .= '--letter-spacing: '.$general_letter_space.';';
            $css .= '--width-sidebar: '.$width_sidebar.'px;';
            $css .= '--main-content:  calc( 100% - '.$width_sidebar.'px )'.';';
            $css .= '--container-width: '.$container_width.'px;';
            $css .= '--boxed-offset: '.$boxed_offset.'px;';
            $css .= '--woo-layout: '.$woo_archive_layout.';';
            $css .= '--woo-width-sidebar: '.$woo_sidebar_width.'px;';
            $css .= '--woo-main-content:  calc( 100% - '.$woo_sidebar_width.'px )'.';';

            $var = ":root{{$css}}";

            $var .= '@media (min-width: 1024px) and ( max-width: '.$container_width_break.'px ){
		        body .row_site,
		        body .elementor-section.elementor-section-boxed>.elementor-container{
		            max-width: 100%;
		            padding-left: 30px;
		            padding-right: 30px;
		        }
		    }';

            wp_add_inline_style( 'tripgo-style', $var );
        }
	}

	// init class
	new Tripgo_Main();
}