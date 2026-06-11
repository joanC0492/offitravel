<?php if ( !defined( 'ABSPATH' ) ) exit();

/**
 * Class OVABRW_Assets
 */
if ( !class_exists( 'OVABRW_Assets' ) ) {

	class OVABRW_Assets {

		/**
		 * Constructor
		 */
		public function __construct() {
			// Admin enqueue scripts
			add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );

			// Frontend enqueue scripts
			add_action( 'wp_enqueue_scripts', [ $this, 'frontend_enqueue_scripts' ], 10, 0 );
			add_action( 'wp_enqueue_scripts', [ $this, 'ovabrw_admin_head' ] );

			// Admin head
			add_action( 'admin_head', [ $this, 'ovabrw_admin_head' ] );
		}

		/**
		 * Admin enqueue scripts
		 */
		public function admin_enqueue_scripts() {
			// Map
			$api_key = ovabrw_get_option_setting( 'google_key_map' );
			if ( $api_key ) {
				wp_enqueue_script( 'ovabrw-google-maps', 'https://maps.googleapis.com/maps/api/js?key='. esc_attr( $api_key ) .'&libraries=places&loading=async&callback=Function.prototype', false, true );
			}

			// Select2
			wp_enqueue_style( 'ovabrw-select2', OVABRW_PLUGIN_URI.'assets/libs/select2/select2.min.css' );
			wp_enqueue_script( 'ovabrw-select2', OVABRW_PLUGIN_URI.'assets/libs/select2/select2.min.js', [ 'jquery' ], false, true );

			// Calendar
		    wp_enqueue_script( 'ovabrw-calendar', OVABRW_PLUGIN_URI.'assets/libs/fullcalendar/index.global.min.js', [ 'jquery' ], false, true );
		    wp_enqueue_script( 'ovabrw-calendar-locales', OVABRW_PLUGIN_URI.'assets/libs/fullcalendar/locales-all.global.min.js', [ 'jquery' ], false, true );

		    // Calendar Booking
			wp_enqueue_script( 'calendar_booking', OVABRW_PLUGIN_URI.'assets/js/admin/calendar.js', [ 'jquery' ], false, true );

			// Easepick
			wp_enqueue_script( 'ovabrw-admin-easepick', OVABRW_PLUGIN_URI.'assets/libs/easepick/easepick.min.js', [ 'jquery' ], false, true );

			// Timepicker script
			wp_enqueue_style( 'ovabrw-admin-timepicker', OVABRW_PLUGIN_URI.'assets/libs/timepicker/timepicker.min.css' );
			wp_enqueue_script( 'ovabrw-admin-timepicker', OVABRW_PLUGIN_URI.'assets/libs/timepicker/timepicker.min.js', [ 'jquery' ], false, true );

			// Tippy scale stype
			wp_enqueue_style( 'ovabrw-tippy-scale', OVABRW_PLUGIN_URI.'assets/libs/tippy/scale.css' );
			wp_enqueue_script( 'ovabrw-popper', OVABRW_PLUGIN_URI.'assets/libs/tippy/popper.min.js', [ 'jquery' ], false, true );
			wp_enqueue_script( 'ovabrw-tippy-bundle', OVABRW_PLUGIN_URI.'assets/libs/tippy/tippy-bundle.min.js', [ 'jquery' ], false, true );

			// Admin Css
			wp_enqueue_style( 'ovabrw_admin', OVABRW_PLUGIN_URI.'assets/css/admin/ovabrw_admin.css', [], null );

			// Global CSS
	        $css    = ovabrw_datepicker_global_css();
	        $root   = ":root{{$css}}";
	        wp_add_inline_style( 'ovabrw_admin', $root );

			// Admin js
			wp_enqueue_script( 'admin_script', OVABRW_PLUGIN_URI.'assets/js/admin/admin_script.min.js', [ 'jquery' ], false, true );
		    wp_localize_script( 'admin_script', 'ajax_object', [
		    	'ajax_url' => admin_url( 'admin-ajax.php' ),
		    	'security' => wp_create_nonce( 'ovabrw-security-ajax' ),
		    	'ggApiKey' => $api_key
		    ]);

		    // Timepicker options
		    wp_localize_script( 'admin_script', 'timePickerOptions', ovabrw_admin_timepicker_options() );

		    // Datepicker options
			wp_localize_script( 'admin_script', 'datePickerOptions', ovabrw_admin_datepicker_options() );

			// Error messages
			wp_localize_script( 'admin_script', 'ovabrwErrorMessages', ovabrw_get_validation_messages() );

			// Confirm text
			wp_localize_script( 'admin_script', 'ovabrwConfirm', [
				'delete' 	=> esc_html__( 'Are you sure you want to delete this field?', 'ova-brw' ),
				'continue' 	=> esc_html__( 'Are you sure you want to continue?', 'ova-brw' )
			]);
		}

		/**
		 * Frontend enqueue scripts
		 */
		public function frontend_enqueue_scripts() {
			// Map
			$api_key = ovabrw_get_option_setting( 'google_key_map' );
			if ( $api_key ) {
				wp_enqueue_script( 'ovabrw-google-maps', 'https://maps.googleapis.com/maps/api/js?key='. esc_attr( $api_key ) .'&libraries=places&loading=async&callback=Function.prototype', false, true );
			}
			
			// UI autocomplete
		    wp_enqueue_script( 'jquery-ui-autocomplete' );

			// Elegant font
			if ( apply_filters( OVABRW_PREFIX.'use_elegant_font', true ) ) {
				wp_enqueue_style( 'elegant_font', OVABRW_PLUGIN_URI.'assets/libs/elegant_font/style.css', [], null );	
			}

			// Tippy scale stype
			wp_enqueue_style( 'ovabrw-tippy-scale', OVABRW_PLUGIN_URI.'assets/libs/tippy/scale.css', [], false );

			// Tippy
			wp_enqueue_script( 'ovabrw-popper', OVABRW_PLUGIN_URI.'assets/libs/tippy/popper.min.js', [ 'jquery' ], false, true );
			wp_enqueue_script( 'ovabrw-tippy-bundle', OVABRW_PLUGIN_URI.'assets/libs/tippy/tippy-bundle.min.js', [ 'jquery' ], false, true );

			// Easepick
			wp_enqueue_script( 'ovabrw-easepick', OVABRW_PLUGIN_URI.'assets/libs/easepick/easepick.min.js', [ 'jquery' ], false, true );

			// CSS frontend
			wp_enqueue_style(
				'ovabrw-frontend',
				OVABRW_PLUGIN_URI.'assets/css/frontend/ovabrw_frontend.css',
				[],
				filemtime( OVABRW_PLUGIN_PATH.'assets/css/frontend/ovabrw_frontend.css' )
			);

			// Global CSS
	        $css    = ovabrw_datepicker_global_css();
	        $root   = ":root{{$css}}";
	        wp_add_inline_style( 'ovabrw-frontend', $root );

	        // BRW - JS frontend
			wp_enqueue_script( 'ova_brw_js_frontend', OVABRW_PLUGIN_URI.'assets/js/frontend/ova-brw-frontend.min.js', [ 'jquery' ], null, true );
			
			wp_localize_script( 'ova_brw_js_frontend', 'ajax_object', [
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'security' => wp_create_nonce( 'ovabrw-security-ajax' )
			]);

			// Error messages
			wp_localize_script( 'ova_brw_js_frontend', 'ovabrwErrorMessages', ovabrw_get_validation_messages() );

			// Datepicker options
			wp_localize_script( 'ova_brw_js_frontend', 'datePickerOptions', ovabrw_get_datepicker_options() );

			// Add select2
	    	wp_enqueue_style( 'select2', OVABRW_PLUGIN_URI.'assets/libs/select2/select2.min.css', [], null );
			wp_enqueue_script( 'select2', OVABRW_PLUGIN_URI.'assets/libs/select2/select2.min.js', [ 'jquery' ], null, true );

			if ( is_singular( 'product' ) ) {
				// Jquery tiptip
				wp_enqueue_style( 'jquery-tiptip', OVABRW_PLUGIN_URI.'assets/libs/jquery-tiptip/tipTip.css', [], null );
				wp_enqueue_script( 'jquery-tiptip', OVABRW_PLUGIN_URI.'assets/libs/jquery-tiptip/jquery-tiptip.min.js', [ 'jquery' ], null, true );
			}
		}

		/**
		 * Admin head
		 */
		public function ovabrw_admin_head() {
			global $wp;

			if ( ( isset( $_GET['post_type'] ) && $_GET['post_type'] == 'product' ) || ( isset( $_GET['post'] ) && isset( $_GET['action'] ) && $_GET['action'] == 'edit' ) || ( isset( $wp->query_vars['wcfm-products-manage'] ) ) ) {

				// Custom taxonomies choosed in post
				$all_cus_tax 			= [];
				$exist_cus_tax 			= [];
				$cus_tax_hide_p_loaded 	= [];

				// Get All Custom taxonomy
				$ovabrw_custom_taxonomy = ovabrw_create_type_taxonomies();

				// All custom slug tax
				if ( $ovabrw_custom_taxonomy ) {
					foreach ( $ovabrw_custom_taxonomy as $key => $value ) {
						array_push($all_cus_tax, $value['slug']);
					}
				}
		
				// Edit product in backend and WCFM plugin
				if ( ( isset( $_GET['post'] ) && $_GET['action'] == 'edit' ) || isset( $wp->query_vars['wcfm-products-manage'] ) ) {
					$id = isset( $_GET['post'] ) ? $_GET['post'] : '';

					if ( ! $id &&  isset( $wp->query_vars['wcfm-products-manage'] ) &&  $wp->query_vars['wcfm-products-manage'] != '' ) {
						$id = $wp->query_vars['wcfm-products-manage'];
					}

					$terms_id = get_the_terms( $id, 'product_cat' );
					
					if ( $terms_id ) {
						foreach ( $terms_id as $key => $term ) {
							$ovabrw_custom_tax = get_term_meta($term->term_id, 'ovabrw_custom_tax', true);	
							
							if ( $ovabrw_custom_tax ) {
								foreach ( $ovabrw_custom_tax as $key => $value ) {
									array_push( $exist_cus_tax, $value );
								}	
							}
						}
					}

					if ( $ovabrw_custom_taxonomy ) {
						foreach ( $ovabrw_custom_taxonomy as $key => $value ) {
							if ( !in_array( $value['slug'], $exist_cus_tax ) ) {
								array_push( $cus_tax_hide_p_loaded, $value['slug'] );
							}
						}
					}
				} else { // Add new product
					$cus_tax_hide_p_loaded = $all_cus_tax;
				}
				
				// Check show custom taxonomy depend category	
				$ova_brw_search_show_tax_depend_cat = ovabrw_get_option_setting( 'search_show_tax_depend_cat', 'no' );

				if ( $ova_brw_search_show_tax_depend_cat == 'no' ) {
					$cus_tax_hide_p_loaded = $all_cus_tax = [];
				}
				
				echo '<script type="text/javascript"> var ova_brw_search_show_tax_depend_cat = "'.$ova_brw_search_show_tax_depend_cat.'"; var cus_tax_hide_p_loaded = "'.implode(',', $cus_tax_hide_p_loaded).'"; var all_cus_tax = "'.implode(',', $all_cus_tax).'"; </script>';
			}
		}
	}

	// init class
	new OVABRW_Assets();
}