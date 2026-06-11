<?php defined( 'ABSPATH' ) || exit;

/**
 * class OVABRW_Guest_Info_Fields
 */
if ( !class_exists( 'OVABRW_Guest_Info_Fields', false ) ) {
	
	class OVABRW_Guest_Info_Fields {
		protected static $_instance = null;

		/**
		 * Guest information fields HTML
		 */
		public function guest_info_fields_html() {
            // Before guest info fields HTML
            do_action( OVABRW_PREFIX.'before_guest_info_fields_html', $this );

            // Include guest information fields HTML
			include( OVABRW_PLUGIN_PATH.'admin/guest-info-fields/views/html-guest-info-fields.php' );

            // After guest info fields HTML
            do_action( OVABRW_PREFIX.'after_guest_info_fields_html', $this );
		}

		/**
		 * Popup guest information field
		 */
		public function popup_guest_info_field( $action = 'new', $type = 'text', $name = '' ) {
            // Before popup guest info field HTML
            do_action( OVABRW_PREFIX.'before_popup_guest_info_field_html', $this );

            if ( 'edit' == $action ) {
                include( OVABRW_PLUGIN_PATH.'admin/guest-info-fields/views/html-popup-edit-guest-info-field.php' );
            } elseif ( 'new' == $action ) {
                include( OVABRW_PLUGIN_PATH.'admin/guest-info-fields/views/html-popup-new-guest-info-field.php' );
            }

            // After popup guest info field HTML
            do_action( OVABRW_PREFIX.'after_popup_guest_info_field_html', $this );
        }

        /**
         * custom checkout type field
         */
        public function get_types() {
            $types = [
                'text'      => esc_html__( 'Text', 'ova-brw' ),
                'number'    => esc_html__( 'Number', 'ova-brw' ),
                'tel'       => esc_html__( 'Tel', 'ova-brw' ),
                'email'     => esc_html__( 'Email', 'ova-brw' ),
                'password'  => esc_html__( 'Password', 'ova-brw' ),
                'textarea'  => esc_html__( 'Textarea', 'ova-brw' ),
                'radio'     => esc_html__( 'Radio', 'ova-brw' ),
                'checkbox'  => esc_html__( 'Checkbox', 'ova-brw' ),
                'select'    => esc_html__( 'Select', 'ova-brw' ),
                'date'      => esc_html__( 'Date', 'ova-brw' ),
                'file'      => esc_html__( 'File', 'ova-brw' )
            ];

            return apply_filters( OVABRW_PREFIX.'guest_info_fields_get_types', $types );
        }

		/**
		 * add new field
		 * @param array $post
		 */
		public function add( $post = [] ) {
            // Before add guest info fields
            do_action( OVABRW_PREFIX.'before_add_guest_info_fields', $post, $this );

			if ( ovabrw_array_exists( $post ) && current_user_can( 'publish_posts' ) ) {
                // Get guest info fields
                $guest_fields = ovabrw_recursive_replace( '\\', '', ovabrw_get_option( 'guest_fields', [] ) );

                // Get name
                $name = sanitize_text_field( sanitize_title( ovabrw_get_meta_data( 'name', $post ) ) );

                if ( $name ) {
                    // Basic
                    $guest_fields[$name] = [
                        'type'          => ovabrw_get_meta_data( 'type', $post ),
                        'label'         => ovabrw_get_meta_data( 'label', $post ),
                        'description'   => ovabrw_get_meta_data( 'description', $post ),
                        'placeholder'   => ovabrw_get_meta_data( 'placeholder', $post ),
                        'default'       => ovabrw_get_meta_data( 'default', $post ),
                        'class'         => ovabrw_get_meta_data( 'class', $post ),
                        'required'      => ovabrw_get_meta_data( 'required', $post ),
                        'enable'        => ovabrw_get_meta_data( 'enable', $post )
                    ];

                    // Tel pattern
                    if ( ovabrw_get_meta_data( 'pattern', $post ) ) {
                        $guest_fields[$name]['pattern'] = $post['pattern'];
                    }

                    // File types
                    if ( ovabrw_get_meta_data( 'accept', $post ) ) {
                        $guest_fields[$name]['accept'] = $post['accept'];
                    }

                    // File max size
                    if ( (float)ovabrw_get_meta_data( 'max_size', $post ) ) {
                        $guest_fields[$name]['max_size'] = (float)$post['max_size'];
                    }

                    // Min
                    if ( ovabrw_get_meta_data( 'min', $post ) ) {
                        $guest_fields[$name]['min'] = $post['min'];
                    }

                    // Max
                    if ( ovabrw_get_meta_data( 'max', $post ) ) {
                        $guest_fields[$name]['max'] = $post['max'];
                    }

                    // Option IDs
                    if ( ovabrw_get_meta_data( 'option_ids', $post ) ) {
                        $guest_fields[$name]['option_ids'] = ovabrw_sanitize_title( $post['option_ids'] );
                    }

                    // Option names
                    if ( ovabrw_get_meta_data( 'option_names', $post ) ) {
                        $guest_fields[$name]['option_names'] = $post['option_names'];
                    }
                }

                update_option( 'ovabrw_guest_fields', $guest_fields );
            }

            // After add guest info fields
            do_action( OVABRW_PREFIX.'after_add_guest_info_fields', $post, $this );
		}

		/**
		 * edit field
		 * @param array $post
		 */
		public function edit( $post = [] ) {
            // Before edit guest info fields
            do_action( OVABRW_PREFIX.'before_edit_guest_info_fields', $post, $this );

			if ( ovabrw_array_exists( $post ) && current_user_can('publish_posts') ) {
                // Get guest info fields
                $guest_fields = ovabrw_recursive_replace( '\\', '', ovabrw_get_option( 'guest_fields', [] ) );

                // Get name
                $new_name = sanitize_text_field( sanitize_title( ovabrw_get_meta_data( 'name', $post ) ) );
                $old_name = sanitize_text_field( ovabrw_get_meta_data( 'old_name', $post ) );

                if ( $new_name && $old_name ) {
                    $data = [
                        'type'          => ovabrw_get_meta_data( 'type', $post ),
                        'label'         => ovabrw_get_meta_data( 'label', $post ),
                        'description'   => ovabrw_get_meta_data( 'description', $post ),
                        'placeholder'   => ovabrw_get_meta_data( 'placeholder', $post ),
                        'default'       => ovabrw_get_meta_data( 'default', $post ),
                        'class'         => ovabrw_get_meta_data( 'class', $post ),
                        'required'      => ovabrw_get_meta_data( 'required', $post ),
                        'enable'        => ovabrw_get_meta_data( 'enable', $post )
                    ];

                    // Tel pattern
                    if ( ovabrw_get_meta_data( 'pattern', $post ) ) {
                        $data['pattern'] = $post['pattern'];
                    }

                    // File accept
                    if ( ovabrw_get_meta_data( 'accept', $post ) ) {
                        $data['accept'] = $post['accept'];
                    }

                    // File max size
                    if ( (float)ovabrw_get_meta_data( 'max_size', $post ) ) {
                        $data['max_size'] = (float)$post['max_size'];
                    }

                    // Min
                    if ( ovabrw_get_meta_data( 'min', $post ) ) {
                        $data['min'] = $post['min'];
                    }

                    // Max
                    if ( ovabrw_get_meta_data( 'max', $post ) ) {
                        $data['max'] = $post['max'];
                    }

                    // Option IDs
                    if ( ovabrw_get_meta_data( 'option_ids', $post ) ) {
                        $data['option_ids'] = ovabrw_sanitize_title( $post['option_ids'] );
                    }

                    // Option names
                    if ( ovabrw_get_meta_data( 'option_names', $post ) ) {
                        $data['option_names'] = $post['option_names'];
                    }

                    // Change name
                    if ( $new_name != $old_name ) {
                    	// Update new name
                        $guest_fields = array_map( function ( $key, $value ) use ( $old_name, $new_name ) {
                            if ( $key === $old_name ) {
                                return [$new_name => $value];
                            }

                            return [$key => $value];
                        }, array_keys( $guest_fields ), $guest_fields);

                        // Merge
                        $guest_fields = call_user_func_array( 'array_merge', $guest_fields );

                        // Add new name
                        $guest_fields[$new_name] = $data;
                    } else {
                        $guest_fields[$old_name] = $data;
                    }
                }

                update_option( 'ovabrw_guest_fields', $guest_fields );
            }

            // After edit guest info fields
            do_action( OVABRW_PREFIX.'after_edit_guest_info_fields', $post, $this );
		}

		/**
		 * delete field
		 * @param array $post
		 */
		public function delete( $post = [] ) {
            // Before delete guest info fields
            do_action( OVABRW_PREFIX.'before_delete_guest_info_fields', $post, $this );

			if ( ovabrw_array_exists( $post ) && current_user_can('publish_posts') ) {
                // Get guest info fields
                $guest_fields = ovabrw_recursive_replace( '\\', '', ovabrw_get_option( 'guest_fields', [] ) );

                // Get fields
                $fields = ovabrw_get_meta_data( 'fields', $post );

                if ( ovabrw_array_exists( $fields ) ) {
                    foreach ( $fields as $name ) {
                        // remove field
                        if ( isset( $guest_fields[$name] ) ) unset( $guest_fields[$name] );
                    }
                }

                // Update guest fields
                update_option( 'ovabrw_guest_fields', $guest_fields );
            }

            // After delete guest info fields
            do_action( OVABRW_PREFIX.'after_delete_guest_info_fields', $post, $this );
		}

        /**
         * required field
         * @param array $post
         */
        public function required( $post = [] ) {
            // Before required guest info fields
            do_action( OVABRW_PREFIX.'before_required_guest_info_fields', $post, $this );

            if ( ovabrw_array_exists( $post ) && current_user_can('publish_posts') ) {
                // Get guest info fields
                $guest_fields = ovabrw_recursive_replace( '\\', '', ovabrw_get_option( 'guest_fields', [] ) );

                // Get fields
                $fields = ovabrw_get_meta_data( 'fields', $post );

                if ( ovabrw_array_exists( $fields ) ) {
                    foreach ( $fields as $name ) {
                        // Update enable
                        if ( isset( $guest_fields[$name]['required'] ) ) {
                            $guest_fields[$name]['required'] = 'on';
                        }
                    }
                }

                // Update guest fields
                update_option( 'ovabrw_guest_fields', $guest_fields );
            }

            // After required guest info fields
            do_action( OVABRW_PREFIX.'afer_required_guest_info_fields', $post, $this );
        }

        /**
         * optional field
         * @param array $post
         */
        public function optional( $post = [] ) {
            // Before optional guest info fields
            do_action( OVABRW_PREFIX.'before_optional_guest_info_fields', $post, $this );

            if ( ovabrw_array_exists( $post ) && current_user_can('publish_posts') ) {
                // Get guest info fields
                $guest_fields = ovabrw_recursive_replace( '\\', '', ovabrw_get_option( 'guest_fields', [] ) );

                // Get fields
                $fields = ovabrw_get_meta_data( 'fields', $post );

                if ( ovabrw_array_exists( $fields ) ) {
                    foreach ( $fields as $name ) {
                        // Update enable
                        if ( isset( $guest_fields[$name]['required'] ) ) {
                            $guest_fields[$name]['required'] = '';
                        }
                    }
                }

                // Update guest fields
                update_option( 'ovabrw_guest_fields', $guest_fields );
            }

            // After optional guest info fields
            do_action( OVABRW_PREFIX.'afer_optional_guest_info_fields', $post, $this );
        }

		/**
		 * enable field
		 * @param array $post
		 */
		public function enable( $post = [] ) {
            // Before enable guest info fields
            do_action( OVABRW_PREFIX.'before_enable_guest_info_fields', $post, $this );

			if ( ovabrw_array_exists( $post ) && current_user_can('publish_posts') ) {
                // Get guest info fields
                $guest_fields = ovabrw_recursive_replace( '\\', '', ovabrw_get_option( 'guest_fields', [] ) );

                // Get fields
                $fields = ovabrw_get_meta_data( 'fields', $post );

                if ( ovabrw_array_exists( $fields ) ) {
                    foreach ( $fields as $name ) {
                        // Update enable
                        if ( isset( $guest_fields[$name]['enable'] ) ) {
                            $guest_fields[$name]['enable'] = 'on';
                        }
                    }
                }

                // Update guest fields
                update_option( 'ovabrw_guest_fields', $guest_fields );
            }

            // After enable guest info fields
            do_action( OVABRW_PREFIX.'afer_enable_guest_info_fields', $post, $this );
		}

		/**
		 * Disable field
		 * @param array $post
		 */
		public function disable( $post = [] ) {
            // Before disable guest info fields
            do_action( OVABRW_PREFIX.'before_disable_guest_info_fields', $post, $this );

			if ( ovabrw_array_exists( $post ) && current_user_can('publish_posts') ) {
                // Get guest info fields
                $guest_fields = ovabrw_recursive_replace( '\\', '', ovabrw_get_option( 'guest_fields', [] ) );

                // Get fields
                $fields = ovabrw_get_meta_data( 'fields', $post );

                if ( ovabrw_array_exists( $fields ) ) {
                    foreach ( $fields as $name ) {
                        // Disable field
                        if ( isset( $guest_fields[$name]['enable'] ) ) {
                            $guest_fields[$name]['enable'] = '';
                        }
                    }
                }

                // Update guest fields
                update_option( 'ovabrw_guest_fields', $guest_fields );
            }

            // After disable guest info fields
            do_action( OVABRW_PREFIX.'after_disable_guest_info_fields', $post, $this );
		}

        /**
         * Save fields
         * @param array $post
         */
        public function save( $post = [] ) {
            // Before save guest info fields
            do_action( OVABRW_PREFIX.'before_save_guest_info_fields', $post, $this );

            if ( ovabrw_array_exists( $post ) && current_user_can('publish_posts') ) {
                // Get action
                $action = ovabrw_get_meta_data( 'action', $post );

                if ( 'new' == $action ) {
                    $this->add( $post );
                } elseif ( 'edit' == $action ) {
                    $this->edit( $post );
                }
            }

            // After save guest info fields
            do_action( OVABRW_PREFIX.'after_save_guest_info_fields', $post, $this );
        }

		/**
		 * sort fields
		 * @param array $args
		 */
		public function sort( $args = [] ) {
            // Before sort guest info fields
            do_action( OVABRW_PREFIX.'before_sort_guest_info_fields', $args, $this );

            // Get fields
			$fields = ovabrw_get_meta_data( 'fields', $args, [] );

            if ( ovabrw_array_exists( $fields ) && current_user_can( 'publish_posts' ) ) {
                // Get guest info fields
                $guest_fields = ovabrw_recursive_replace( '\\', '', ovabrw_get_option( 'guest_fields', [] ) );

                // New data
                $new_data = [];

                foreach ( $fields as $name ) {
                    if ( $name && array_key_exists( $name, $guest_fields ) ) {
                        $new_data[$name] = $guest_fields[$name];
                    }
                }

                // Update guest fields
                if ( ovabrw_array_exists( $new_data ) ) {
                    update_option( 'ovabrw_guest_fields', $new_data );
                }
            }

            // After sort guest info fields
            do_action( OVABRW_PREFIX.'after_sort_guest_info_fields', $args, $this );
		}

		/**
		 * instance
		 */
		public static function instance() {
            if ( is_null( self::$_instance ) ) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }
	}

    // int class
	new OVABRW_Guest_Info_Fields();
}