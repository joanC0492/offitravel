<?php if ( !defined( 'ABSPATH' ) ) exit();

/**
 * Class OVABRW_Booking_Tours_Settings
 */
if ( !class_exists( 'OVABRW_Booking_Tours_Settings' ) ) {

    class OVABRW_Booking_Tours_Settings extends WC_Settings_Page {

        /**
         * Constructor
         */
        public function __construct() {
            // Set ID
            $this->id = 'ova_brw';

            // Set label
            $this->label = esc_html__( 'Booking Tours', 'ova-brw' );

            parent::__construct();
        }

        /**
         * Get own sections
         */
        public function get_own_sections() {
            return apply_filters( OVABRW_PREFIX.'get_own_sections', [
                ''              => esc_html__( 'General', 'ova-brw' ),
                'detail'        => esc_html__( 'Product details', 'ova-brw' ),
                'booking'       => esc_html__( 'Booking form', 'ova-brw' ),
                'request'       => esc_html__( 'Request form', 'ova-brw' ),
                'enquiry'       => esc_html__( 'Enquiry form', 'ova-brw' ),
                'guests'        => esc_html__( 'Guests', 'ova-brw' ),
                'guest_info'    => esc_html__( 'Guest Information', 'ova-brw' ),
                'recaptcha'     => esc_html__( 'reCAPTCHA', 'ova-brw' ),
                'deposit'       => esc_html__( 'Deposit', 'ova-brw' ),
                'cancel'        => esc_html__( 'Cancellation policy', 'ova-brw' ),
                'reminder'      => esc_html__( 'Reminder', 'ova-brw' ),
                'order'         => esc_html__( 'Manage bookings', 'ova-brw' )
            ]);
        }

        /**
         * General section.
         */
        protected function get_settings_for_default_section() {
            // Add settings
            $settings = [
                [   
                    'title' => esc_html__( 'General', 'ova-brw' ),
                    'type'  => 'title',
                    'id'    => OVABRW_PREFIX.'general'
                ],
                   [
                        'name'      => esc_html__( 'Date format', 'ova-brw' ),
                        'type'      => 'select',
                        'id'        => 'ova_brw_booking_form_date_format',
                        'options'   => apply_filters( OVABRW_PREFIX.'settings_date_format', [
                            'd-m-Y' => esc_html__( 'd-m-Y', 'ova-brw' ).' ('.date_i18n( 'd-m-Y' ).')',
                            'm/d/Y' => esc_html__( 'm/d/Y', 'ova-brw' ).' ('.date_i18n( 'm/d/Y' ).')',
                            'Y/m/d' => esc_html__( 'Y/m/d', 'ova-brw' ).' ('.date_i18n( 'Y/m/d' ).')',
                            'Y-m-d' => esc_html__( 'Y-m-d', 'ova-brw' ).' ('.date_i18n( 'Y-m-d' ).')'
                        ]),
                        'default'   => 'd-m-Y'
                    ],
                    [
                        'name'      => esc_html__( 'Time format', 'ova-brw' ),
                        'type'      => 'select',
                        'id'        => 'ova_brw_booking_form_time_format',
                        'options'   => apply_filters( OVABRW_PREFIX.'settings_time_format', [
                            'H:i'   => esc_html__( 'H:i', 'ova-brw' ).' ('.date_i18n( 'H:i' ).')',
                            'h:i'   => esc_html__( 'h:i', 'ova-brw' ).' ('.date_i18n( 'h:i' ).')',
                            'h:i a' => esc_html__( 'h:i a', 'ova-brw' ).' ('.date_i18n( 'h:i a' ).')',
                            'h:i A' => esc_html__( 'h:i A', 'ova-brw' ).' ('.date_i18n( 'h:i A' ).')',
                            'G:i'   => esc_html__( 'G:i', 'ova-brw' ).' ('.date_i18n( 'G:i' ).')',
                            'g:i'   => esc_html__( 'g:i', 'ova-brw' ).' ('.date_i18n( 'g:i' ).')',
                            'g:i a' => esc_html__( 'g:i a', 'ova-brw' ).' ('.date_i18n( 'g:i a' ).')',
                            'g:i A' => esc_html__( 'g:i A', 'ova-brw' ).' ('.date_i18n( 'g:i A' ).')'
                        ]),
                        'default'   => 'H:i'
                    ],
                    [
                        'name'      => esc_html__( 'Step time', 'ova-brw' ),
                        'type'      => 'number',
                        'id'        => 'ova_brw_step_time',
                        'default'   => 30
                    ],
                    [
                        'name'      => esc_html__( 'Calendar language', 'ova-brw' ),
                        'type'      => 'select',
                        'id'        => 'ova_brw_calendar_language_general',
                        'options'   => [
                            'ar'    => esc_html__( 'Arabic', 'ova-brw' ),
                            'az'    => esc_html__( 'Azerbaijanian (Azeri)', 'ova-brw' ),
                            'bg'    => esc_html__( 'Bulgarian', 'ova-brw' ),
                            'bs'    => esc_html__( 'Bosanski', 'ova-brw' ),
                            'ca'    => esc_html__( 'Català', 'ova-brw' ),
                            'ch'    => esc_html__( 'Simplified Chinese', 'ova-brw' ),
                            'cs'    => esc_html__( 'Čeština', 'ova-brw' ),
                            'da'    => esc_html__( 'Dansk', 'ova-brw' ),
                            'de'    => esc_html__( 'German', 'ova-brw' ),
                            'el'    => esc_html__( 'Ελληνικά', 'ova-brw' ),
                            'en'    => esc_html__( 'English', 'ova-brw' ),
                            'en-GB' => esc_html__( 'English (British) ', 'ova-brw' ),
                            'es'    => esc_html__( 'Spanish', 'ova-brw' ),
                            'et'    => esc_html__( 'Eesti', 'ova-brw' ),
                            'eu'    => esc_html__( 'Euskara', 'ova-brw' ),
                            'fa'    => esc_html__( 'Persian', 'ova-brw' ),
                            'fi'    => esc_html__( 'Finnish (Suomi)', 'ova-brw' ),
                            'fr'    => esc_html__( 'French', 'ova-brw' ),
                            'gl'    => esc_html__( 'Galego', 'ova-brw' ),
                            'he'    => esc_html__( 'Hebrew (עברית)', 'ova-brw' ),
                            'hr'    => esc_html__( 'Hrvatski', 'ova-brw' ),
                            'hu'    => esc_html__( 'Hungarian', 'ova-brw' ),
                            'id'    => esc_html__( 'Indonesian', 'ova-brw' ),
                            'it'    => esc_html__( 'Italian', 'ova-brw' ),
                            'ja'    => esc_html__( 'Japanese', 'ova-brw' ),
                            'ko'    => esc_html__( 'Korean (한국어)', 'ova-brw' ),
                            'kr'    => esc_html__( 'Korean', 'ova-brw' ),
                            'lt'    => esc_html__( 'Lithuanian (lietuvių)', 'ova-brw' ),
                            'lv'    => esc_html__( 'Latvian (Latviešu)', 'ova-brw' ),
                            'mk'    => esc_html__( 'Macedonian (Македонски)', 'ova-brw' ),
                            'mn'    => esc_html__( 'Mongolian (Монгол)', 'ova-brw' ),
                            'nl'    => esc_html__( 'Dutch', 'ova-brw' ),
                            'no'    => esc_html__( 'Norwegian', 'ova-brw' ),
                            'pl'    => esc_html__( 'Polish', 'ova-brw' ),
                            'pt'    => esc_html__( 'Portuguese', 'ova-brw' ),
                            'pt-BR' => esc_html__( 'Português(Brasil)', 'ova-brw' ),
                            'ro'    => esc_html__( 'Romanian', 'ova-brw' ),
                            'ru'    => esc_html__( 'Russian', 'ova-brw' ),
                            'se'    => esc_html__( 'Swedish', 'ova-brw' ),
                            'sk'    => esc_html__( 'Slovenčina', 'ova-brw' ),
                            'sl'    => esc_html__( 'Slovenščina', 'ova-brw' ),
                            'sq'    => esc_html__( 'Albanian (Shqip)', 'ova-brw' ),
                            'sr'    => esc_html__( 'Serbian Cyrillic (Српски)', 'ova-brw' ),
                            'sr-YU' => esc_html__( 'Serbian (Srpski)', 'ova-brw' ),
                            'sv'    => esc_html__( 'Svenska', 'ova-brw' ),
                            'th'    => esc_html__( 'Thai', 'ova-brw' ),
                            'tr'    => esc_html__( 'Turkish', 'ova-brw' ),
                            'uk'    => esc_html__( 'Ukrainian', 'ova-brw' ),
                            'vi'    => esc_html__( 'Vietnamese', 'ova-brw' ),
                            'zh'    => esc_html__( 'Simplified Chinese (简体中文)', 'ova-brw' ),
                            'zh-TW' => esc_html__( 'Traditional Chinese (繁體中文)', 'ova-brw' ),
                        ],
                        'default'   => 'en',
                        'desc_tip'  => esc_html__( 'Display in Calendar','ova-brw' )
                    ],
                    [
                        'name'          => esc_html__( 'Disable weekdays', 'ova-brw' ),
                        'type'          => 'text',
                        'id'            => 'ova_brw_calendar_disable_week_day',
                        'desc_tip'      => esc_html__( '0: Sunday, 1: Monday, 2: Tuesday, 3: Wednesday, 4: Thursday, 5: Friday, 6: Saturday . Example: 0,6', 'ova-brw' ),
                        'placeholder'   => '0,6'
                    ],
                    [
                        'name'      => esc_html__( 'The first day of the week', 'ova-brw' ),
                        'type'      => 'select',
                        'id'        => 'ova_brw_calendar_first_day',
                        'options'   => [
                            '1' => esc_html__( 'Monday', 'ova-brw' ),
                            '2' => esc_html__( 'Tuesday', 'ova-brw' ),
                            '3' => esc_html__( 'Wednesday', 'ova-brw' ),
                            '4' => esc_html__( 'Thursday', 'ova-brw' ),
                            '5' => esc_html__( 'Friday', 'ova-brw' ),
                            '6' => esc_html__( 'Saturday ', 'ova-brw' ),
                            '0' => esc_html__( 'Sunday', 'ova-brw' )
                        ],
                        'default'   => '1'
                    ],
                    [
                        'name'      => esc_html__( 'Show custom taxonomy', 'ova-brw' ),
                        'type'      => 'select',
                        'id'        => 'ova_brw_search_show_tax_depend_cat',
                        'options'   => [
                            'yes'   => esc_html__( 'Depend on each category', 'ova-brw' ),
                            'no'    => esc_html__( 'All categories', 'ova-brw' )
                        ],
                        'default'   => 'no',
                        'desc_tip'  => esc_html__( 'Taxonomies displayed by Product Category', 'ova-brw' )
                    ],
                [
                    'type'  => 'sectionend',
                    'id'    => OVABRW_PREFIX.'general'
                ],
                [   
                    'type'  => 'title',
                    'id'    => OVABRW_PREFIX.'map',
                    'title' => esc_html__( 'Google Maps', 'ova-brw' )
                ],
                    [
                        'name'          => esc_html__( 'API Key', 'ova-brw' ),
                        'type'          => 'text',
                        'id'            => 'ova_brw_google_key_map',
                        'placeholder'   => esc_html__( 'Enter your API Key', 'ova-brw' ),
                        'desc'          => sprintf( esc_html__( 'You can get API Key %s', 'ova-brw' ), '<a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank">here</a>' )
                    ],
                    [
                        'name'      => esc_html__( 'Zoom', 'ova-brw' ),
                        'type'      => 'text',
                        'id'        => 'ova_brw_zoom_map_default',
                        'class'     => 'ova_brw_zoom_map_default',
                        'default'   => 17
                    ],
                    [
                        'name'      => esc_html__( 'Default latitude ', 'ova-brw' ),
                        'type'      => 'text',
                        'id'        => 'ova_brw_latitude_map_default',
                        'class'     => 'ova_brw_latitude_map_default',
                        'default'   => 39.177972,
                        'desc_tip'  => esc_html__( 'The default latitude of map when the event do not exist','ova-brw' )
                    ],
                    [
                        'name'      => esc_html__( 'Default longitude ', 'ova-brw' ),
                        'type'      => 'text',
                        'id'        => 'ova_brw_longitude_map_default',
                        'class'     => 'ova_brw_longitude_map_default',
                        'default'   => -100.363750,
                        'desc_tip'  => esc_html__( 'The default longitude of map when the event do not exist','ova-brw' )
                    ],
                [
                    'type'  => 'sectionend',
                    'id'    => OVABRW_PREFIX.'map'
                ],
            ];

            return apply_filters( OVABRW_PREFIX.'get_settings_for_default_section', $settings );
        }

        /**
         * Product details section.
         */
        protected function get_settings_for_detail_section() {
            // Product templates
            $product_templates = [
                'default' => esc_html__( 'Default', 'ova-brw' )
            ];

            // Get templates from elementor
            $templates = get_posts([
                'post_type'     => 'elementor_library',
                'meta_key'      => '_elementor_template_type',
                'meta_value'    => 'page',
                'numberposts'   => -1,
                'fields'        => 'ids'
            ]);

            // Loop
            if ( ovabrw_array_exists( $templates ) ) {
                foreach ( $templates as $template_id ) {
                    $product_templates[$template_id] = get_the_title( $template_id );
                }
            } // END loop

            // Add settings
            $settings = [
                [
                    'title' => esc_html__( 'Product details', 'ova-brw' ),
                    'type'  => 'title',
                    'id'    => OVABRW_PREFIX.'product_details'
                ],
                    [
                        'name'      => esc_html__( 'Product templates', 'ova-brw' ),
                        'type'      => 'select',
                        'id'        => 'ova_brw_template_elementor_template',
                        'desc_tip'  => esc_html__( 'Default or Other (made in Templates of Elementor )', 'ova-brw' ),
                        'options'   => $product_templates,
                        'default'   => 'default'
                    ],
                    [
                        'name'      => esc_html__( 'Show booking form', 'ova-brw' ),
                        'type'      => 'select',
                        'id'        => 'ova_brw_template_show_booking_form',
                        'options'   => [
                            'yes'   => esc_html__( 'Yes', 'ova-brw' ),
                            'no'    => esc_html__( 'No', 'ova-brw' ),
                        ],
                        'default'   => 'yes'
                    ],
                    [
                        'name'      => esc_html__( 'Show request form', 'ova-brw' ),
                        'type'      => 'select',
                        'id'        => 'ova_brw_template_show_request_booking',
                        'options'   => [
                            'yes'   => esc_html__( 'Yes', 'ova-brw' ),
                            'no'    => esc_html__( 'No', 'ova-brw' ),
                        ],
                        'default'   => 'yes'
                    ],
                    [
                        'name'      => esc_html__( 'Show enquiry form', 'ova-brw' ),
                        'type'      => 'select',
                        'id'        => 'ova_brw_template_show_enquiry_booking',
                        'options'   => [
                            'yes'   => esc_html__( 'Yes', 'ova-brw' ),
                            'no'    => esc_html__( 'No', 'ova-brw' ),
                        ],
                        'default'   => 'no'
                    ],
                [
                    'type'  => 'sectionend',
                    'id'    => OVABRW_PREFIX.'product_details'
                ]
            ];

            return apply_filters( OVABRW_PREFIX.'get_settings_for_detail_section', $settings );
        }

        /**
         * Booking form section.
         */
        protected function get_settings_for_booking_section() {
            // Add settings
            $settings = [
                [
                    'title' => esc_html__( 'Booking form', 'ova-brw' ),
                    'type'  => 'title',
                    'id'    => OVABRW_PREFIX.'booking_form'
                ],
                    [
                        'name'          => esc_html__( 'Book before X hours today', 'ova-brw' ),
                        'type'          => 'text',
                        'id'            => 'ova_brw_booking_before_x_hours_today',
                        'class'         => 'ovabrw-timepicker',
                        'desc'          => '<span class="ovabrw-remove-time">X</span>',
                        'desc_tip'      => esc_html__( 'Customers can book before X hours today.','ova-brw'),
                        'placeholder'   => esc_html__( 'Choose Time', 'ova-brw' )
                    ],
                    [
                        'name'      => esc_html__( 'Show check-out field', 'ova-brw' ),
                        'type'      => 'select',
                        'id'        => 'ova_brw_booking_form_show_checkout',
                        'options'   => [
                            'yes'   => esc_html__( 'Yes', 'ova-brw' ),
                            'no'    => esc_html__( 'No', 'ova-brw' ),
                        ],
                        'default'   => 'yes',
                        'desc_tip'  => esc_html__( 'Show the check-out field in the Booking Form, Cart, Checkout, Order Detail', 'ova-brw' )
                    ],
                    [
                        'name'      => esc_html__( 'Show quantity', 'ova-brw' ),
                        'id'        => 'ova_brw_booking_form_show_quantity',
                        'type'      => 'select',
                        'options'   => [
                            'yes'   => esc_html__( 'Yes', 'ova-brw' ),
                            'no'    => esc_html__( 'No', 'ova-brw' ),
                        ],
                        'default'   => 'no',
                        'desc_tip'  => esc_html__( 'Show the quantity field in the Booking form, Request form, Cart, Checkout, Order Detail', 'ova-brw' )
                    ],
                    [
                        'name'      => esc_html__( 'Show number of tours available', 'ova-brw' ),
                        'type'      => 'select',
                        'id'        => 'ova_brw_booking_form_show_quantity_availables',
                        'options'   => [
                            'yes'   => esc_html__( 'Yes', 'ova-brw' ),
                            'no'    => esc_html__( 'No', 'ova-brw' ),
                        ],
                        'default'   => 'yes'
                    ],
                    [
                        'name'      => esc_html__( 'Show amount of insurance', 'ova-brw' ),
                        'type'      => 'select',
                        'id'        => 'ovabrw_show_insurance_amount',
                        'options'   => [
                            'yes'   => esc_html__( 'Yes', 'ova-brw' ),
                            'no'    => esc_html__( 'No', 'ova-brw' ),
                        ],
                        'default'   => 'yes'
                    ],
                    [
                        'name'      => esc_html__( 'Apply tax for insurance amount', 'ova-brw' ),
                        'id'        => 'ovabrw_insurance_tax_enabled',
                        'type'      => 'select',
                        'options'   => [
                            'yes'   => esc_html__( 'Yes', 'ova-brw' ),
                            'no'    => esc_html__( 'No', 'ova-brw' ),
                        ],
                        'desc_tip'  => esc_html__( 'Tax will be calculated for insurance during checkout (required enable Taxes)', 'ova-brw' )
                    ],
                    [
                        'name'      => esc_html__( 'Insurance amount will be paid once', 'ova-brw' ),
                        'id'        => 'ovabrw_insurance_paid_once',
                        'type'      => 'select',
                        'options'   => [
                            'yes'   => esc_html__( 'Yes', 'ova-brw' ),
                            'no'    => esc_html__( 'No', 'ova-brw' ),
                        ],
                        'desc_tip'  => esc_html__( 'Insurance amount will be paid in full in the first payment', 'ova-brw' )
                    ],
                    [
                        'name'      => esc_html__( 'Show resources and services in Cart, Checkout, Order detail', 'ova-brw' ),
                        'type'      => 'select',
                        'id'        => 'ova_brw_booking_form_show_extra',
                        'options'   => [
                            'yes'   => esc_html__( 'Yes', 'ova-brw' ),
                            'no'    => esc_html__( 'No', 'ova-brw' ),
                        ],
                        'default'   => 'no'
                    ],
                [
                    'type'  => 'sectionend',
                    'id'    => OVABRW_PREFIX.'booking_form'
                ],
                [
                    'title' => esc_html__( 'Terms and conditions', 'ova-brw' ),
                    'type'  => 'title',
                    'id'    => OVABRW_PREFIX.'booking_form_terms_conditions_settings'
                ],
                    [
                        'name'      => esc_html__( 'Enable', 'ova-brw' ),
                        'type'      => 'select',
                        'id'        => 'ova_brw_booking_form_terms_conditions',
                        'options'   => [
                            'yes'   => esc_html__( 'Yes', 'ova-brw' ),
                            'no'    => esc_html__( 'No', 'ova-brw' ),
                        ],
                        'default'   => 'no'
                    ],
                    [
                        'name'              => esc_html__( 'Content', 'ova-brw' ),
                        'type'              => 'textarea',
                        'id'                => 'ova_brw_booking_form_terms_conditions_content',
                        'default'           => 'I have read and agree to the website <a href="https://demo.ovatheme.com/tripgo/" target="_blank">terms and conditions</a>',
                        'desc'              => esc_html__( 'You can insert text or HTML', 'ova-brw' ),
                        'custom_attributes' => [
                            'rows' => '5',
                        ]
                    ],
                [
                    'type' => 'sectionend',
                    'id'    => OVABRW_PREFIX.'booking_form_terms_conditions_settings'
                ]
            ];

            return apply_filters( OVABRW_PREFIX.'get_settings_for_detail_section', $settings );
        }

        /**
         * Request form section.
         */
        protected function get_settings_for_request_section() {
            // Pages
            $pages = [
                '' => esc_html__( 'Select Page', 'ova-brw' )
            ];

            // Get all pages
            $all_pages = get_pages();
            if ( ovabrw_array_exists( $all_pages ) ) {
                foreach ( $all_pages as $page ) {
                    // Get page title
                    $page_title = $page->post_title;

                    // Get page link
                    $page_link = get_page_link( $page->ID );

                    // Add page
                    $pages[$page_link] = $page_title;
                }
            } // END

            // Add settings
            $settings = [
                [
                    'title' => esc_html__( 'Request form', 'ova-brw' ),
                    'type'  => 'title',
                    'id'    => OVABRW_PREFIX.'request_booking_form'
                ],
                    [
                        'name'      => esc_html__( 'Thank page', 'ova-brw' ),
                        'type'      => 'select',
                        'id'        => 'ova_brw_request_booking_form_thank_page',
                        'options'   => $pages
                    ],
                    [
                        'name'      => esc_html__( 'Error page', 'ova-brw' ),
                        'type'      => 'select',
                        'id'        => 'ova_brw_request_booking_form_error_page',
                        'options'   => $pages
                    ],
                    [
                        'name'      => esc_html__( 'Show phone', 'ova-brw' ),
                        'type'      => 'select',
                        'id'        => 'ova_brw_request_booking_form_show_number',
                        'options'   => [
                            'yes'   => esc_html__( 'Yes', 'ova-brw' ),
                            'no'    => esc_html__( 'No', 'ova-brw' ),
                        ],
                        'default'   => 'yes'
                    ],
                    [
                        'name'      => esc_html__( 'Show address', 'ova-brw' ),
                        'type'      => 'select',
                        'id'        => 'ova_brw_request_booking_form_show_address',
                        'options'   => [
                            'yes'   => esc_html__( 'Yes', 'ova-brw' ),
                            'no'    => esc_html__( 'No', 'ova-brw' ),
                        ],
                        'default'   => 'yes'
                    ],
                    [
                        'name'      => esc_html__( 'Show check-out date', 'ova-brw' ),
                        'type'      => 'select',
                        'id'        => 'ova_brw_request_booking_form_show_dates',
                        'options'   => [
                            'yes'   => esc_html__( 'Yes', 'ova-brw' ),
                            'no'    => esc_html__( 'No', 'ova-brw' ),
                        ],
                        'default'   => 'yes'
                    ],
                    [
                        'name'      => esc_html__( 'Show custom checkout fields', 'ova-brw' ),
                        'type'      => 'select',
                        'id'        => 'ova_brw_request_booking_form_show_ckf',
                        'options'   => [
                            'yes'   => esc_html__( 'Yes', 'ova-brw' ),
                            'no'    => esc_html__( 'No', 'ova-brw' ),
                        ],
                        'default'   => 'yes'
                    ],
                    [
                        'name'      => esc_html__( 'Show extra services', 'ova-brw' ),
                        'type'      => 'select',
                        'id'        => 'ova_brw_request_booking_form_show_extra_service',
                        'options'   => [
                            'yes'   => esc_html__( 'Yes', 'ova-brw' ),
                            'no'    => esc_html__( 'No', 'ova-brw' ),
                        ],
                        'default'   => 'yes'
                    ],
                    [
                        'name'      => esc_html__( 'Show service', 'ova-brw' ),
                        'type'      => 'select',
                        'id'        => 'ova_brw_request_booking_form_show_service',
                        'options'   => [
                            'yes'   => esc_html__( 'Yes', 'ova-brw' ),
                            'no'    => esc_html__( 'No', 'ova-brw' ),
                        ],
                        'default'   => 'yes'
                    ],
                    [
                        'name'      => esc_html__( 'Show extra info', 'ova-brw' ),
                        'type'      => 'select',
                        'id'        => 'ova_brw_request_booking_form_show_extra_info',
                        'options'   => [
                            'yes'   => esc_html__( 'Yes', 'ova-brw' ),
                            'no'    => esc_html__( 'No', 'ova-brw' ),
                        ],
                        'default'   => 'yes'
                    ],
                    [
                        'name'      => esc_html__( 'Show total', 'ova-brw' ),
                        'type'      => 'select',
                        'id'        => 'ova_brw_request_booking_form_show_total',
                        'options'   => [
                            'yes'   => esc_html__( 'Yes', 'ova-brw' ),
                            'no'    => esc_html__( 'No', 'ova-brw' ),
                        ],
                        'default'   => 'no'
                    ],
                    [
                        'name'      => esc_html__( 'Show number of tours available', 'ova-brw' ),
                        'type'      => 'select',
                        'id'        => 'ova_brw_request_booking_form_show_quantity_availables',
                        'options'   => [
                            'yes'   => esc_html__( 'Yes', 'ova-brw' ),
                            'no'    => esc_html__( 'No', 'ova-brw' ),
                        ],
                        'default'   => 'yes'
                    ],
                    [
                        'name'      => esc_html__( 'Show amount of insurance', 'ova-brw' ),
                        'type'      => 'select',
                        'id'        => 'ova_brw_request_booking_form_show_insurance_amount',
                        'options'   => [
                            'yes'   => esc_html__( 'Yes', 'ova-brw' ),
                            'no'    => esc_html__( 'No', 'ova-brw' ),
                        ],
                        'default'   => 'yes'
                    ],
                [
                    'type' => 'sectionend',
                    'id'    => OVABRW_PREFIX.'request_booking_form'
                ],
                [
                    'title' => esc_html__( 'Email settings', 'ova-brw' ),
                    'type'  => 'title',
                    'id'    => OVABRW_PREFIX.'request_booking_mail_setting'
                ],
                    [
                        'name'      => esc_html__( 'Subject', 'ova-brw' ),
                        'type'      => 'text',
                        'id'        => 'ova_brw_request_booking_mail_subject',
                        'default'   => esc_html__( 'Request For Booking', 'ova-brw' ) ,
                        'desc_tip'  => esc_html__( 'The subject displays in the email list', 'ova-brw' )
                    ],
                    [
                        'name'      => esc_html__( 'From name', 'ova-brw' ),
                        'type'      => 'text',
                        'id'        => 'ova_brw_request_booking_mail_from_name',
                        'default'   => esc_html__( 'Request For Booking', 'ova-brw' ),
                        'desc_tip'  => esc_html__( 'The subject displays in mail detail', 'ova-brw' )
                    ],
                    [
                        'name'      => esc_html__( 'Send from email', 'ova-brw' ),
                        'type'      => 'text',
                        'id'        => 'ova_brw_request_booking_mail_from_email',
                        'default'   => get_option( 'admin_email' ),
                        'desc_tip'  => esc_html__( 'The customer will know them to receive mail from which email address is', 'ova-brw' )
                    ],
                    [
                        'name'          => esc_html__( 'Cc', 'ova-brw' ),
                        'type'          => 'text',
                        'id'            => 'ova_brw_request_booking_mail_cc_email',
                        'default'       => '',
                        'placeholder'   => 'email1@gmail.com|email2@gmail.com',
                        'desc_tip'      => esc_html__( 'Emails separated by "|".', 'ova-brw' )
                    ],
                    [
                        'name'              => esc_html__( 'Email content', 'ova-brw' ),
                        'type'              => 'textarea',
                        'id'                => 'ova_brw_request_booking_mail_content',
                        'default'           => esc_html__( 'You booked the tour: [product-name] from [check-in] to [check-out]. [order_details]', 'ova-brw' ),
                        'desc_tip'          => esc_html__( 'Use tags to generate email template. For example: You booked the tour: [product-name] from [check-in] to [check-out]. [order_details]', 'ova-brw' ),
                        'custom_attributes' => [
                            'rows' => '5'
                        ]
                    ],
                [
                    'type' => 'sectionend',
                    'id'    => OVABRW_PREFIX.'request_booking_mail_setting'
                ],
                [
                    'title' => esc_html__( 'Order', 'ova-brw' ),
                    'type'  => 'title',
                    'id'    => OVABRW_PREFIX.'request_booking_order_setting'
                ],
                    [
                        'name'      => esc_html__( 'Allows creating new orders', 'ova-brw' ),
                        'type'      => 'select',
                        'id'        => 'ova_brw_request_booking_create_order',
                        'options'   => [
                            'yes'   => esc_html__( 'Yes', 'ova-brw' ),
                            'no'    => esc_html__( 'No', 'ova-brw' ),
                        ],
                        'default'   => 'no'
                    ],
                    [
                        'name'      => esc_html__( 'Order status', 'ova-brw' ),
                        'type'      => 'select',
                        'id'        => 'ova_brw_request_booking_order_status',
                        'options'   => [
                            'wc-pending'    => esc_html__( 'Pending payment', 'ova-brw' ),
                            'wc-processing' => esc_html__( 'Processing', 'ova-brw' ),
                            'wc-on-hold'    => esc_html__( 'On hold', 'ova-brw' ),
                            'wc-completed'  => esc_html__( 'Completed', 'ova-brw' ),
                            'wc-cancelled'  => esc_html__( 'Cancelled', 'ova-brw' )
                        ],
                        'default'   => 'wc-on-hold'
                    ],
                [
                    'type' => 'sectionend',
                    'id'    => OVABRW_PREFIX.'request_booking_order_setting'
                ],
                [
                    'title' => esc_html__( 'Terms and conditions', 'ova-brw' ),
                    'type'  => 'title',
                    'id'    => OVABRW_PREFIX.'request_booking_terms_conditions_settings'
                ],
                    [
                        'name'      => esc_html__( 'Enable', 'ova-brw' ),
                        'type'      => 'select',
                        'id'        => 'ova_brw_request_booking_terms_conditions',
                        'options'   => [
                            'yes'   => esc_html__( 'Yes', 'ova-brw' ),
                            'no'    => esc_html__( 'No', 'ova-brw' ),
                        ],
                        'default'   => 'no'
                    ],
                    [
                        'name'              => esc_html__( 'Content', 'ova-brw' ),
                        'type'              => 'textarea',
                        'id'                => 'ova_brw_request_booking_terms_conditions_content',
                        'default'           => 'I have read and agree to the website <a href="https://demo.ovatheme.com/tripgo/" target="_blank">terms and conditions</a>',
                        'desc'              => esc_html__( 'You can insert text or HTML', 'ova-brw' ),
                        'custom_attributes' => [
                            'rows' => '5'
                        ]
                    ],
                [
                    'type' => 'sectionend',
                    'id'    => OVABRW_PREFIX.'request_booking_terms_conditions_settings'
                ]
            ];

            return apply_filters( OVABRW_PREFIX.'get_settings_for_request_section', $settings );
        }

        /**
         * Enquiry form
         */
        protected function get_settings_for_enquiry_section() {
            $settings = [
                [
                    'title' => '',
                    'type'  => 'title',
                    'id'    => OVABRW_PREFIX.'enquiry_options'
                ],
                    [
                        'title'         => esc_html__( 'Shortcode', 'ova-brw' ),
                        'type'          => 'text',
                        'id'            => OVABRW_PREFIX.'enquiry_shortcode',
                        'placeholder'   => esc_html__( '[contact-form-7]', 'ova-brw' ),
                        'desc_tip'      => esc_html__( 'Insert a shortcode. You can use shortcode of contact form 7 plugin.', 'ova-brw' ),
                        'row_class'     => 'ovabrw-enquiry-shortcode'
                    ],
                    [
                        'title'     => '',
                        'type'      => 'info',
                        'text'      => esc_html__( 'Insert the [product-link] tag into the "Message body" in Contact Form 7 to show the product title and link.', 'ova-brw' ),
                        'row_class' => 'ovabrw-enquiry-shortcode-info'
                    ],
                [
                    'type'  => 'sectionend',
                    'id'    => OVABRW_PREFIX.'enquiry_options'
                ]
            ];

            return apply_filters( OVABRW_PREFIX.'get_settings_for_enquiry_section', $settings );
        }

        /**
         * Get settings for the custom guests fields section.
         */
        protected function get_settings_for_guests_section() {
            $settings = [
                [
                    'title' => '',
                    'type'  => 'title',
                    'id'    => OVABRW_PREFIX.'guests_options',
                ],
                    [
                        'name'      => esc_html__( 'Show price beside Adults', 'ova-brw' ),
                        'type'      => 'select',
                        'id'        => 'ova_brw_booking_form_show_price_beside_adults',
                        'options'   => [
                            'yes'   => esc_html__( 'Yes', 'ova-brw' ),
                            'no'    => esc_html__( 'No', 'ova-brw' ),
                        ],
                        'default'   => 'yes'
                    ],
                    [
                        'name'          => esc_html__( 'Label beside Adults', 'ova-brw' ),
                        'type'          => 'text',
                        'id'            => 'ova_brw_label_beside_adults',
                        'placeholder'   => esc_html__( '(12y-99y)', 'ova-brw' ),
                        'desc_tip'      => esc_html__( 'Ex: (12y-99y)', 'ova-brw' ),
                        'row_class'     => 'ovabrw-border-buttom'
                    ],
                    [
                        'name'      => esc_html__( 'Show children field', 'ova-brw' ),
                        'type'      => 'select',
                        'id'        => 'ova_brw_booking_form_show_children',
                        'options'   => [
                            'yes'   => esc_html__( 'Yes', 'ova-brw' ),
                            'no'    => esc_html__( 'No', 'ova-brw' ),
                        ],
                        'default'   => 'yes',
                        'desc_tip'  => esc_html__( 'Show the children field in the Booking form and Request Booking form', 'ova-brw' ),
                    ],
                    [
                        'name'      => esc_html__( 'Show price beside Children', 'ova-brw' ),
                        'type'      => 'select',
                        'id'        => 'ova_brw_booking_form_show_price_beside_childrens',
                        'options'   => [
                            'yes'   => esc_html__( 'Yes', 'ova-brw' ),
                            'no'    => esc_html__( 'No', 'ova-brw' ),
                        ],
                        'default'   => 'yes'
                    ],
                    [
                        'name'          => esc_html__( 'Label beside Children', 'ova-brw' ),
                        'type'          => 'text',
                        'id'            => 'ova_brw_label_beside_childrens',
                        'desc_tip'      => esc_html__( 'Ex: (6y-11y)', 'ova-brw' ),
                        'placeholder'   => esc_html__( '(6y-11y)','ova-brw' ),
                        'row_class'     => 'ovabrw-border-buttom'
                    ],
                    [
                        'name'      => esc_html__( 'Show babies field', 'ova-brw' ),
                        'id'        => 'ova_brw_booking_form_show_baby',
                        'type'      => 'select',
                        'options'   => [
                            'yes'   => esc_html__( 'Yes', 'ova-brw' ),
                            'no'    => esc_html__( 'No', 'ova-brw' ),
                        ],
                        'default'   => 'no',
                        'desc_tip'  => esc_html__( 'Show the baby field in the Booking form and Request Booking form', 'ova-brw' )
                    ],
                    [
                        'name'      => esc_html__( 'Show price beside Babies', 'ova-brw' ),
                        'type'      => 'select',
                        'id'        => 'ova_brw_booking_form_show_price_beside_babies',
                        'options'   => [
                            'yes'   => esc_html__( 'Yes', 'ova-brw' ),
                            'no'    => esc_html__( 'No', 'ova-brw' ),
                        ],
                        'default'   => 'yes'
                    ],
                    [
                        'name'          => esc_html__( 'Label beside Babies', 'ova-brw' ),
                        'type'          => 'text',
                        'id'            => 'ova_brw_label_beside_babies',
                        'desc_tip'      => esc_html__( 'Ex: (0y-5y)', 'ova-brw' ),
                        'placeholder'   => esc_html__( '(0y-5y)', 'ova-brw' )
                    ],
                [
                    'type'  => 'sectionend',
                    'id'    => OVABRW_PREFIX.'guests_options'
                ]
            ];

            return apply_filters( OVABRW_PREFIX.'get_settings_for_guest_info_section', $settings );
        }

        /**
         * Get settings for the custom guest information fields section.
         */
        protected function get_settings_for_guest_info_section() {
            $settings = [
                [
                    'title' => '',
                    'type'  => 'title',
                    'id'    => OVABRW_PREFIX.'guest_info_options'
                ],
                    [
                        'title'     => esc_html__( 'Collect customer information', 'ova-brw' ),
                        'type'      => 'checkbox',
                        'id'        => OVABRW_PREFIX.'guest_info',
                        'default'   => '',
                        'tooltip'   => esc_html__( 'Require guests to enter information for each person in the booking/request form.', 'ova-brw' )
                    ],
                [
                    'type'  => 'sectionend',
                    'id'    => OVABRW_PREFIX.'guest_info_options'
                ]
            ];

            return apply_filters( OVABRW_PREFIX.'get_settings_for_guest_info_section', $settings );
        }

        /**
         * reCAPTCHA section.
         */
        protected function get_settings_for_recaptcha_section() {
            // Add settings
            $settings = [
                [
                    'title' => esc_html__( 'reCAPTCHA', 'ova-brw' ),
                    'type'  => 'title',
                    'id'    => OVABRW_PREFIX.'recapcha_settings'
                ],
                    [
                        'name'      => esc_html__( 'Enable', 'ova-brw' ),
                        'type'      => 'select',
                        'id'        => 'ova_brw_recapcha_enable',
                        'options'   => [
                            'yes'   => esc_html__( 'Yes', 'ova-brw' ),
                            'no'    => esc_html__( 'No', 'ova-brw' ),
                        ],
                        'default'   => 'no'
                    ],
                    [
                        'name'      => esc_html__( 'Form', 'ova-brw' ),
                        'type'      => 'select',
                        'id'        => 'ova_brw_recapcha_form',
                        'options'   => [
                            'both'      => esc_html__( 'Both', 'ova-brw' ),
                            'booking'   => esc_html__( 'Booking form', 'ova-brw' ),
                            'enquiry'   => esc_html__( 'Request form', 'ova-brw' ),
                        ],
                        'default'   => 'both'
                    ],
                    [
                        'name'      => esc_html__( 'reCAPTCHA type', 'ova-brw' ),
                        'type'      => 'select',
                        'id'        => 'ova_brw_recapcha_type',
                        'options'   => [
                            'v3' => esc_html__( 'Score based (v3)', 'ova-brw' ),
                            'v2' => esc_html__( 'Challenge (v2)', 'ova-brw' ),
                        ],
                        'default'   => 'v3'
                    ],
                [
                    'type' => 'sectionend',
                    'id'    => OVABRW_PREFIX.'recapcha_settings'
                ],
                [
                    'title' => esc_html__( 'reCAPTCHA v3', 'ova-brw' ),
                    'type'  => 'title',
                    'id'    => OVABRW_PREFIX.'recapcha_v3_settings'
                ],
                    [
                        'name'          => esc_html__( 'Site key', 'ova-brw' ),
                        'type'          => 'text',
                        'id'            => 'ova_brw_recapcha_v3_site_key',
                        'placeholder'   => esc_html__( 'Insert your site key' )
                    ],
                    [
                        'name'          => esc_html__( 'Secret key', 'ova-brw' ),
                        'type'          => 'text',
                        'id'            => 'ova_brw_recapcha_v3_secret_key',
                        'placeholder'   => esc_html__( 'Insert your secret key' )
                    ],
                [
                    'type'  => 'sectionend',
                    'id'    => OVABRW_PREFIX.'recapcha_v3_settings'
                ],
                [
                    'title' => esc_html__( 'reCAPTCHA v2', 'ova-brw' ),
                    'type'  => 'title',
                    'id'    => OVABRW_PREFIX.'recapcha_v2_settings'
                ],
                    [
                        'name'          => esc_html__( 'Site key', 'ova-brw' ),
                        'type'          => 'text',
                        'id'            => 'ova_brw_recapcha_v2_site_key',
                        'placeholder'   => esc_html__( 'Insert your site key' )
                    ],
                    [
                        'name'          => esc_html__( 'Secret key', 'ova-brw' ),
                        'type'          => 'text',
                        'id'            => 'ova_brw_recapcha_v2_secret_key',
                        'placeholder'   => esc_html__( 'Insert your secret key' )
                    ],
                [
                    'type'  => 'sectionend',
                    'id'    => OVABRW_PREFIX.'recapcha_v2_settings'
                ]
            ];

            return apply_filters( OVABRW_PREFIX.'get_settings_for_recaptcha_section', $settings );
        }

        /**
         * Deposit section.
         */
        protected function get_settings_for_deposit_section() {
            // Add settings
            $settings = [
                [
                    'title' => esc_html__( 'Remaining amount', 'ova-brw' ),
                    'type'  => 'title',
                    'id'    => OVABRW_PREFIX.'remaining_options'
                ],
                    [
                        'name'      => esc_html__( 'Send order detail to customer', 'ova-brw' ),
                        'type'      => 'select',
                        'id'        => 'send_email_remaining_invoice_enable',
                        'options'   => [
                            'yes'   => esc_html__( 'Yes', 'ova-brw' ),
                            'no'    => esc_html__( 'No', 'ova-brw' ),
                        ],
                        'default'   => 'yes',
                        'desc_tip'  => esc_html__( 'Email the order for the remaining amount to the customer.', 'ova-brw' )
                    ],
                [
                    'type' => 'sectionend',
                    'id'    => OVABRW_PREFIX.'remaining_options'
                ],
                [
                    'title' => esc_html__( 'Automatically create order details for remaining amount', 'ova-brw' ),
                    'type'  => 'title',
                    'id'    => OVABRW_PREFIX.'remaining_cron_options',
                    'desc'  => esc_html__( 'X days before the customer\'s Check-in date, the order detail for Remaining Amount will be automatically created and sent to the customer\'s email (If the order detail has not been created manually).', 'ova-brw' )
                ],
                    [
                        'name'      => esc_html__( 'Enable', 'ova-brw' ),
                        'type'      => 'select',
                        'id'        => 'remaining_invoice_enable',
                        'options'   => [
                            'yes'   => esc_html__( 'Yes', 'ova-brw' ),
                            'no'    => esc_html__( 'No', 'ova-brw' ),
                        ],
                        'default'   => 'no',
                        'desc_tip'  => esc_html__( 'Auto create remaining invoice', 'ova-brw' )
                    ],
                    [
                        'name'      => esc_html__( 'X day before pick-up date', 'ova-brw' ),
                        'type'      => 'number',
                        'id'        => 'remaining_invoice_before_xday',
                        'default'   => 1,
                        'row_class' => 'ovabrw-hidden'
                    ],
                    [
                        'name'      => esc_html__( 'Check periodically every X seconds for creating a remaining invoice', 'ova-brw' ),
                        'type'      => 'number',
                        'id'        => 'remaining_invoice_per_seconds',
                        'default'   => 86400,
                        'desc_tip'  => esc_html__( '86400s = 24*60*60(1 day)', 'ova-brw' ),
                        'row_class' => 'ovabrw-hidden'
                    ],
                [
                    'type'  => 'sectionend',
                    'id'    => OVABRW_PREFIX.'remaining_cron_options',
                ]
            ];

            return apply_filters( OVABRW_PREFIX.'get_settings_for_deposit_section', $settings );
        }

        /**
         * Cancellation policy section.
         */
        protected function get_settings_for_cancel_section() {
            // Add settings
            $settings = [
                [
                    'title' => esc_html__( 'Cancellation Policy', 'ova-brw' ),
                    'type'  => 'title',
                    'id'    => OVABRW_PREFIX.'cancel_setting'
                ],
                [
                    'name'      => esc_html__( 'Minimum time required before canceling (hours)', 'ova-brw' ),
                    'type'      => 'number',
                    'id'        => 'ova_brw_cancel_before_x_hours',
                    'default'   => 0
                ],
                [
                    'name'      => esc_html__( 'Cancellation is accepted if the total order is less than x amount', 'ova-brw' ),
                    'type'      => 'text',
                    'id'        => 'ova_brw_cancel_condition_total_order',
                    'default'   => 1
                ],
                [
                    'type' => 'sectionend',
                    'id'    => OVABRW_PREFIX.'cancel_setting'
                ]
            ];

            return apply_filters( OVABRW_PREFIX.'get_settings_for_cancel_section', $settings );
        }

        /**
         * Reminder section.
         */
        protected function get_settings_for_reminder_section() {
            // Add settings
            $settings = [
                [
                    'title' => esc_html__( 'Reminder', 'ova-brw' ),
                    'type'  => 'title',
                    'id'    => OVABRW_PREFIX.'reminder_setting'
                ],
                [
                    'name'      => esc_html__( 'Enable', 'ova-brw' ),
                    'type'      => 'select',
                    'id'        => 'remind_mail_enable',
                    'options'   => [
                        'yes'   => esc_html__( 'Yes', 'ova-brw' ),
                        'no'    => esc_html__( 'No', 'ova-brw' ),
                    ],
                    'desc'      => esc_html__( 'Allow to send mail to customer', 'ova-brw' ),
                    'default'   => 'no'
                ],
                [
                    'name'      => esc_html__( 'X day before pick-up date', 'ova-brw' ),
                    'type'      => 'text',
                    'id'        => 'remind_mail_before_xday',
                    'default'   => 1,
                    'row_class' => 'ovabrw-hidden'
                ],
                [
                    'name'      => esc_html__( 'Send a recurring email every X seconds after the initial one.', 'ova-brw' ),
                    'type'      => 'number',
                    'id'        => 'remind_mail_send_per_seconds',
                    'default'   => 86400,
                    'desc_tip'  => esc_html__( '86400s = 24*60*60(1 day)', 'ova-brw' ),
                    'row_class' => 'ovabrw-hidden'
                ],
                [
                    'name'      => esc_html__( 'Subject', 'ova-brw' ),
                    'type'      => 'text',
                    'id'        => 'reminder_mail_subject',
                    'default'   => esc_html__( 'Remind Check-in Date', 'ova-brw' ),
                    'desc_tip'  => esc_html__( 'The subject displays in the email list', 'ova-brw' ),
                    'row_class' => 'ovabrw-hidden'
                ],
                [
                    'name'      => esc_html__( 'From name', 'ova-brw' ),
                    'type'      => 'text',
                    'id'        => 'reminder_mail_from_name',
                    'default'   => esc_html__( 'Remind Check-in Date', 'ova-brw' ),
                    'desc_tip'  => esc_html__( 'The subject displays in mail detail', 'ova-brw' ),
                    'row_class' => 'ovabrw-hidden'
                ],
                [
                    'name'      => esc_html__( 'Send from email', 'ova-brw' ),
                    'type'      => 'text',
                    'id'        => 'reminder_mail_from_email',
                    'default'   => get_option( 'admin_email' ),
                    'desc_tip'  => esc_html__( 'The customer will know them to receive mail from which email address is', 'ova-brw' ),
                    'row_class' => 'ovabrw-hidden'
                ],
                [
                    'name'              => esc_html__( 'Email content', 'ova-brw' ),
                    'type'              => 'textarea',
                    'id'                => 'reminder_mail_content',
                    'default'           => esc_html__( 'You booked the tour: [product-name] at [check-in]', 'ova-brw' ),
                    'desc_tip'          => esc_html__( 'Use tags to generate email template. For example: You booked the tour: [product-name] at [check-in]', 'ova-brw' ),
                    'custom_attributes' => [
                        'rows' => '5'
                    ],
                    'row_class'         => 'ovabrw-hidden'
                ],
                [
                    'type'  => 'sectionend',
                    'id'    => OVABRW_PREFIX.'reminder_setting'
                ]
            ];

            return apply_filters( OVABRW_PREFIX.'get_settings_for_reminder_section', $settings );
        }

        /**
         * Reminder section.
         */
        protected function get_settings_for_order_section() {
            // Add settings
            $settings = [
                [
                    'title' => esc_html__( 'Admin settings', 'ova-brw' ),
                    'type'  => 'title',
                    'id'    => OVABRW_PREFIX.'admin_manage_order_setting',
                    'desc'  => esc_html__( 'The fields are sorted ascending. To hide the field, enter the number: 0 or empty', 'ova-brw' )
                ],
                [
                    'name'      => esc_html__( 'Show booking ID', 'ova-brw' ),
                    'type'      => 'number',
                    'id'        => 'admin_manage_order_show_id',
                    'default'   => 1
                ],
                [
                    'name'      => esc_html__( 'Show customer', 'ova-brw' ),
                    'type'      => 'number',
                    'id'        => 'admin_manage_order_show_customer',
                    'default'   => 2
                ],
                [
                    'name'      => esc_html__( 'Show time', 'ova-brw' ),
                    'type'      => 'number',
                    'id'        => 'admin_manage_order_show_time',
                    'default'   => 3
                ],
                [
                    'name'      => esc_html__( 'Show deposit status', 'ova-brw' ),
                    'type'      => 'number',
                    'id'        => 'admin_manage_order_show_deposit',
                    'default'   => 4
                ],
                [
                    'name'      => esc_html__( 'Show insurance status', 'ova-brw' ),
                    'type'      => 'number',
                    'id'        => 'admin_manage_order_show_insurance',
                    'default'   => 5
                ],
                [
                    'name'      => esc_html__( 'Show product', 'ova-brw' ),
                    'type'      => 'number',
                    'id'        => 'admin_manage_order_show_product',
                    'default'   => 6
                ],
                [
                    'name'      => esc_html__( 'Show status', 'ova-brw' ),
                    'type'      => 'number',
                    'id'        => 'admin_manage_order_show_order_status',
                    'default'   => 7
                ],
                [
                    'type'  => 'sectionend',
                    'id'    => OVABRW_PREFIX.'admin_manage_order_setting'
                ]
            ];

            return apply_filters( OVABRW_PREFIX.'get_settings_for_order_section', $settings );
        }

        /**
         * Output the settings.
         */
        public function output() {
            global $current_section;

            // Custom checkout fields
            if ( 'guest_info' == $current_section && class_exists( 'OVABRW_Guest_Info_Fields' ) ) {
                // Call the parent class's output method
                parent::output();

                // Include custom checkout fields HTML
                OVABRW_Guest_Info_Fields::instance()->guest_info_fields_html();
            } else {
                // Call the parent class's output method
                parent::output();
            }
        }
    }

    return new OVABRW_Booking_Tours_Settings();
}