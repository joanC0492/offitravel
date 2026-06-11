<?php if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Class Tripgo_Woo
 */
if ( !class_exists( 'Tripgo_Woo' ) ) {

	class Tripgo_Woo {

		/**
		 * Constructor
		 */
		public function __construct() {
			// Show title archive shop page
			add_filter( 'woocommerce_show_page_title', [ $this, 'tripgo_woocommerce_show_title_shop_page' ] );

			// Insert category to loop product
			add_action( 'woocommerce_shop_loop_item_title', [ $this, 'tripgo_woocommerce_template_loop_product_cat' ], 5 );

			// Main content
			remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );

			// Remove breadcrumb woo
			remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
			add_action( 'woocommerce_before_main_content', [ $this, 'tripgo_woocommerce_before_main_content' ], 10 );

			// Get sidebar
			remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
			add_action( 'woocommerce_sidebar', [ $this, 'tripgo_woocommerce_sidebar' ], 10 );

			// Pagination change next, pre text
			add_filter( 'woocommerce_pagination_args', [ $this, 'tripgo_woocommerce_pagination_args' ] );

			// Change number product related
			add_filter( 'woocommerce_output_related_products_args', [ $this, 'tripgo_change_number_product_related' ] );

			// Add data prettyPhoto in gallery
			add_filter( 'woocommerce_single_product_image_thumbnail_html', [ $this, 'tripgo_single_product_image_thumbnail_html' ], 10, 2 );

			// Login form
			add_action( 'woocommerce_before_customer_login_form', [ $this, 'tripgo_woocommerce_before_customer_login_form' ], 100 );

			// Remove title in Product Detail
			if ( get_theme_mod( 'woo_product_detail_show_title', 'yes' ) != 'yes' ) {
				remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
			}

			// Remove Heading in Content Description Tab Woo
			add_filter( 'woocommerce_product_description_heading', '__return_null' );

			add_action( 'wp_enqueue_scripts', [ $this, 'tripgo_enqueue_scripts_woo' ] );
			
			// Shop filter products by product type
			add_action( 'pre_get_posts', [ $this, 'tripgo_shop_filter_by_product_type' ] );
		}

		/**
		 * Show title show page
		 */
		public function tripgo_woocommerce_show_title_shop_page( $param ) {
		    if ( ( is_shop() || is_product_category() || is_product_tag() ) && get_theme_mod( 'woo_archive_show_title', 'yes' ) == 'yes' ) {
				return true;
			}

			return false;
		}

		/**
		 * Product category
		 */
		public function tripgo_woocommerce_template_loop_product_cat() {
			// Get current product id
			$id = get_the_id();

			// Get categories
			$cats = get_the_terms( $id, 'product_cat') ? get_the_terms( $id, 'product_cat') : '' ;

			$value_cats = [];
			if ( !empty( $cats ) && is_array( $cats ) ) {
				foreach ( $cats as $value ) {
					$value_cats[] = is_object( $value ) && $value->term_id ? '<span class="cat_product">' . $value->name . '</span>' : '';
				}
			}

			echo implode( ' ', $value_cats );
		}

		/**
		 * Main content
		 */
		public function tripgo_woocommerce_before_main_content() { ?>
			<div class="row_site">
				<div class="container_site">
					<div id="woo_main">
						<?php wc_get_template( 'global/wrapper-start.php' );
		}

		/**
		 * Woo sidebar
		 */
		public function tripgo_woocommerce_sidebar() { ?>
			</div>
			<?php if ( tripgo_woo_sidebar() != 'woo_layout_1c' && is_active_sidebar( 'woo-sidebar' ) ): ?>
				<div id="woo_sidebar">
					<?php wc_get_template( 'global/sidebar.php' ); ?>
				</div>
			<?php endif; ?>
			</div>
		</div>
		<?php }

		/**
		 * Pagination
		 */
		public function tripgo_woocommerce_pagination_args( $array ) { 
			$args = [
				'next_text' => '<i class="ovaicon-next"></i>',
                'prev_text' => '<i class="ovaicon-back"></i>'
			];
		    $agrs = array_merge( $array, $args );

		    return $agrs; 
		}

		/**
		 * Product related
		 */
		public function tripgo_change_number_product_related( $agrs ) {
			$agrs_setting = [
				'posts_per_page' => apply_filters( 'number_product_realated_posts_per_page', 3 ),
				'columns'        => apply_filters( 'number_product_realated_columns', 3 )
			];
			$agrs = array_merge( $agrs, $agrs_setting );
			return $agrs;
		}

		/**
		 * Product image thumbnail
		 */
		public function tripgo_single_product_image_thumbnail_html( $html, $attachment_id ) {
			if ( $attachment_id ) {
				$thumbnail_url 	= wp_get_attachment_image_url ( $attachment_id, 'large' );
				$thumbnail_alt 	= trim( wp_strip_all_tags( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ) );
				$html = '<div class="woocommerce-product-gallery__image">';
					$html .= '<a href="'.esc_url( $thumbnail_url ).'">';
						$html .= '<img src="'.esc_url( $thumbnail_url ).'" class="wp-post-image" alt="'.esc_attr( $thumbnail_alt ).'">';
					$html .= '</a>';
				$html .= '</div>';
			} else {
				$html  = '<div class="woocommerce-product-gallery__image--placeholder">';
					$html .= sprintf( '<img src="%s" alt="%s" class="wp-post-image"  />', esc_url( wc_placeholder_img_src() ), esc_html__( 'Awaiting product image', 'tripgo' ) );
				$html .= '</div>';
			}

			return $html;
		}

		/**
		 * Customer login form
		 */
		public function tripgo_woocommerce_before_customer_login_form() { ?>
			<ul class="ova-login-register-woo">
				<li class="active">
					<a href="javascript:void(0)" data-type="login">
						<?php esc_html_e( 'Login', 'tripgo' ); ?>
					</a>
				</li>
				<?php if ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) : ?>
					<li>
						<a href="javascript:void(0)" data-type="register">
							<?php esc_html_e( 'Register', 'tripgo' ); ?>
						</a>
					</li>
				<?php endif; ?>
			</ul>
		<?php }

		/**
		 * Enqueue scripts
		 */
		public function tripgo_enqueue_scripts_woo() {
			if ( is_product() ){
				// Fancybox
				wp_enqueue_script( 'ova-fancybox', TRIPGO_URI.'/assets/libs/fancybox/fancybox.umd.js', [ 'jquery' ], null, true );
				wp_enqueue_style( 'ova-fancybox', TRIPGO_URI.'/assets/libs/fancybox/fancybox.css' );
			}
			
		    wp_enqueue_script( 'tripgo-woo', TRIPGO_URI.'/assets/js/woo.js', [ 'jquery' ], null, true );
		}	

		/**
		 * Shop page: Filter by product type
		 */
		public function tripgo_shop_filter_by_product_type($query) {
			$terms = get_theme_mod( 'woo_archive_display', 'all' );
            
            if ( $terms == 'ovabrw_car_rental' ) {
            	if ( !is_admin() && is_post_type_archive( 'product' ) && $query->is_main_query() ) {
			       $query->set( 'tax_query', [
			       		[
	                    	'taxonomy' 	=> 'product_type',
                           	'field' 	=> 'slug',
                           	'terms' 	=> $terms,
                           	'operator' 	=> 'IN'
	                    ]
			       ]);   
			    }
            } elseif ( $terms == 'not_rental' ) {
            	if ( !is_admin() && is_post_type_archive( 'product' ) && $query->is_main_query() ) {
			       $query->set( 'tax_query', [
			       		[
	                    	'taxonomy' 	=> 'product_type',
                           	'field' 	=> 'slug',
                           	'terms' 	=> [ 'simple', 'grouped', 'variable', 'external' ],
                           	'operator' 	=> 'IN'
	                    ]
			       ]);   
			    }
            }
		}
	}

	// init class
	new Tripgo_Woo();
}