<?php if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Class OVADES_Customize
 */
if ( !class_exists( 'OVADES_Customize' ) ) {

	class OVADES_Customize {

		/**
		 * Constructor
		 */
		public function __construct() {
			add_action( 'customize_register', [ $this, 'ova_destination_customize_register' ] );
		}

		/**
		 * Register customize
		 */
		public function ova_destination_customize_register( $wp_customize ) {
			$this->ova_destination_init( $wp_customize );
			do_action( 'ova_destination_customize_register', $wp_customize );
		}

		/**
		 * Add settings
		 */
		public function ova_destination_init( $wp_customize ) {
			$wp_customize->add_panel( 'ova_destination_section', [
				'title' 	=> esc_html__( 'Destinations', 'ova-destination' ),
			    'priority' 	=> 5
			]);

			$wp_customize->add_section( 'ova_destination_section_archive', [
				'title' 	=> esc_html__( 'Archive', 'ova-destination' ),
			    'priority'	=> 30,
			    'panel' 	=> 'ova_destination_section'
			]);

			$wp_customize->add_setting( 'ova_destination_total_record', [
				'type' 				=> 'theme_mod', // or 'option'
				'capability' 		=> 'edit_theme_options',
				'theme_supports' 	=> '', // Rarely needed.
				'default' 			=> '7',
				'transport' 		=> 'refresh', // or postMessage
				'sanitize_callback' => 'sanitize_text_field' // Get function name
			]);
			
			$wp_customize->add_control( 'ova_destination_total_record', [
				'label' 	=> esc_html__( 'Number of posts per page', 'ova-destination' ),
				'section' 	=> 'ova_destination_section_archive',
				'settings' 	=> 'ova_destination_total_record',
				'type' 		=> 'number'
			]);	

			$wp_customize->add_setting( 'archive_destination_template', [
				'type' 				=> 'theme_mod', // or 'option'
				'capability' 		=> 'edit_theme_options',
				'theme_supports' 	=> '', // Rarely needed.
				'default' 			=> 'template1',
				'transport' 		=> 'refresh', // or postMessage
				'sanitize_callback' => 'sanitize_text_field' // Get function name
			]);

			$wp_customize->add_control('archive_destination_template', [
				'label' 	=> esc_html__( 'Template', 'ova-destination' ),
				'section' 	=> 'ova_destination_section_archive',
				'settings' 	=> 'archive_destination_template',
				'type' 		=> 'select',
				'choices' 	=> [
					'template1' => esc_html__( 'Template 1', 'ova-destination' ),
					'template2' => esc_html__( 'Template 2', 'ova-destination' ),
					'template3' => esc_html__( 'Template 3', 'ova-destination' )
				]
			]);

			$wp_customize->add_setting( 'header_archive_destination', [
				'type' 				=> 'theme_mod', // or 'option'
				'capability' 		=> 'edit_theme_options',
				'theme_supports' 	=> '', // Rarely needed.
				'default' 			=> 'default',
				'transport' 		=> 'refresh', // or postMessage
				'sanitize_callback' => 'sanitize_text_field' // Get function name
			]);

			$wp_customize->add_control( 'header_archive_destination', [
				'label' 	=> esc_html__( 'Header Archive', 'ova-destination' ),
				'section' 	=> 'ova_destination_section_archive',
				'settings' 	=> 'header_archive_destination',
				'type' 		=> 'select',
				'choices' 	=> apply_filters( 'tripgo_list_header', '' )
			]);

			$wp_customize->add_setting( 'archive_footer_destination', [
				'type' 				=> 'theme_mod', // or 'option'
				'capability' 		=> 'edit_theme_options',
				'theme_supports' 	=> '', // Rarely needed.
				'default' 			=> 'default',
				'transport' 		=> 'refresh', // or postMessage
				'sanitize_callback' => 'sanitize_text_field' // Get function name
			]);

			$wp_customize->add_control( 'archive_footer_destination', [
				'label' 	=> esc_html__( 'Footer Archive', 'ova-destination' ),
				'section' 	=> 'ova_destination_section_archive',
				'settings' 	=> 'archive_footer_destination',
				'type' 		=> 'select',
				'choices' 	=> apply_filters( 'tripgo_list_footer', '' )
			]);

			$wp_customize->add_section( 'ova_destination_section_single', [
				'title' 	=> esc_html__( 'Single', 'ova-destination' ),
			    'priority' 	=> 30,
			    'panel' 	=> 'ova_destination_section'
			]);

		    $wp_customize->add_setting( 'single_destination_template', [
		    	'type' 				=> 'theme_mod', // or 'option'
				'capability' 		=> 'edit_theme_options',
				'theme_supports' 	=> '', // Rarely needed.
				'default' 			=> 'template1',
				'transport' 		=> 'refresh', // or postMessage
				'sanitize_callback' => 'sanitize_text_field' // Get function name
		    ]);

			$wp_customize->add_control( 'single_destination_template', [
				'label' 	=> esc_html__( 'Template', 'ova-destination' ),
				'section' 	=> 'ova_destination_section_single',
				'settings' 	=> 'single_destination_template',
				'type' 		=> 'select',
				'choices' 	=> [
					'template1' => esc_html__( 'Template 1', 'ova-destination' ),
					'template2' => esc_html__( 'Template 2', 'ova-destination' )
				]
			]);

			$wp_customize->add_setting( 'single_detail_background_destination', [
				'type' 				=> 'theme_mod', // or 'option'
			    'capability' 		=> 'edit_theme_options',
			    'theme_supports' 	=> '', // Rarely needed.
			    'transport' 		=> 'refresh', // or postMessage
			    'sanitize_callback' => 'sanitize_text_field', // Get function name
			]);

			$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize,'single_detail_background_destination', [
				'label' 	=> esc_html__( 'Background Content Single Template1', 'ova-destination' ),
				'section' 	=> 'ova_destination_section_single',
				'settings' 	=> 'single_detail_background_destination',
				'mime_type' => 'image'
			]));

		    $wp_customize->add_setting( 'single_related_destination_tour', [
		    	'type' 				=> 'theme_mod', // or 'option'
				'capability' 		=> 'edit_theme_options',
				'theme_supports' 	=> '', // Rarely needed.
				'default' 			=> 'yes',
				'transport' 		=> 'refresh', // or postMessage
				'sanitize_callback' => 'sanitize_text_field' // Get function name
		    ]);

			$wp_customize->add_control( 'single_related_destination_tour', [
				'label' 	=> esc_html__( 'Show Related Destination Tour', 'ova-destination' ),
				'section' 	=> 'ova_destination_section_single',
				'settings' 	=> 'single_related_destination_tour',
				'type' 		=> 'select',
				'choices' 	=> [
					'yes' 	=> esc_html__( 'Yes', 'ova-destination' ),
					'no' 	=> esc_html__( 'No', 'ova-destination' )
				]
			]);

			$wp_customize->add_setting( 'header_single_destination', [
				'type' 				=> 'theme_mod', // or 'option'
			    'capability' 		=> 'edit_theme_options',
			    'theme_supports' 	=> '', // Rarely needed.
			    'default' 			=> 'default',
			    'transport' 		=> 'refresh', // or postMessage
			    'sanitize_callback' => 'sanitize_text_field' // Get function name
			]);

			$wp_customize->add_control( 'header_single_destination', [
				'label' 	=> esc_html__( 'Header Single', 'ova-destination' ),
				'section' 	=> 'ova_destination_section_single',
				'settings' 	=> 'header_single_destination',
				'type' 		=> 'select',
				'choices' 	=> apply_filters( 'tripgo_list_header', '' )
			]);

			$wp_customize->add_setting( 'single_footer_destination', [
				'type' 				=> 'theme_mod', // or 'option'
				'capability' 		=> 'edit_theme_options',
				'theme_supports' 	=> '', // Rarely needed.
				'default' 			=> 'default',
				'transport' 		=> 'refresh', // or postMessage
				'sanitize_callback' => 'sanitize_text_field' // Get function name
			]);

			$wp_customize->add_control( 'single_footer_destination', [
				'label' 	=> esc_html__( 'Footer Single', 'ova-destination' ),
				'section' 	=> 'ova_destination_section_single',
				'settings' 	=> 'single_footer_destination',
				'type' 		=> 'select',
				'choices' 	=> apply_filters( 'tripgo_list_footer', '' )
			]);
		}
	}

	// init class
	new OVADES_Customize();
}