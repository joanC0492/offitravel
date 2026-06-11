<?php if (!defined( 'ABSPATH' )) exit;

/**
 * Class Tripgo_Widgets
 */
if ( !class_exists( 'Tripgo_Widgets' ) ) {
	
	class Tripgo_Widgets {

		/**
		 * Constructor
		 */
		public function __construct() {
			// Regsiter widget
			add_action( 'widgets_init', [ $this, 'tripgo_register_widgets' ] );
		}
		
		/**
		 * Register widgets
		 */
		public function tripgo_register_widgets() {
		  	// Register: Main Sidebar
		    register_sidebar([
		    	'name' 			=> esc_html__( 'Main Sidebar', 'tripgo' ),
		    	'id' 			=> 'main-sidebar',
		    	'description' 	=> esc_html__( 'Main Sidebar', 'tripgo' ),
		    	'class' 		=> '',
		    	'before_widget' => '<div id="%1$s" class="widget %2$s">',
		    	'after_widget' 	=> '</div>',
		    	'before_title' 	=> '<h4 class="widget-title">',
		    	'after_title' 	=> '</h4>'
		    ]);

		  	if ( tripgo_is_woo_active() ) {
		    	register_sidebar([
		    		'name' 			=> esc_html__( 'WooCommerce Sidebar', 'tripgo'),
		      		'id' 			=> 'woo-sidebar',
		      		'description' 	=> esc_html__( 'WooCommerce Sidebar', 'tripgo' ),
		      		'class' 		=> '',
		      		'before_widget' => '<div id="%1$s" class="widget woo_widget %2$s">',
		      		'after_widget' 	=> '</div>',
		      		'before_title' 	=> '<h4 class="widget-title">',
		      		'after_title' 	=> '</h4>'
		    	]);
		   	}
		}
	}

	// init class
	new Tripgo_Widgets();
}