<?php if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Locate template
 */
if ( !function_exists( 'ovaev_locate_template' ) ) {
	function ovaev_locate_template( $template_name, $template_path = '', $default_path = '' ) {
		// Set variable to search in ovaev-templates folder of theme.
		if ( !$template_path ) $template_path = 'ovaev-templates/';

		// Set default plugin templates path.
		if ( !$default_path ) $default_path = OVAEV_PLUGIN_PATH . 'templates/'; // Path to the template folder

		// Search template file in theme folder.
		$template = locate_template([ $template_path . $template_name ]);

		// Get plugins template file.
		if ( !$template ) $template = $default_path . $template_name;

		return apply_filters( 'ovaev_locate_template', $template, $template_name, $template_path, $default_path );
	}
}

/**
 * Get template
 */
if ( !function_exists( 'ovaev_get_template' ) ) {
    function ovaev_get_template( $template_name, $args = [], $tempate_path = '', $default_path = '' ) {
        if ( is_array( $args ) && isset( $args ) ) {
            extract( $args );
        }

        // Get template file
        $template_file = ovaev_locate_template( $template_name, $tempate_path, $default_path );
        if ( !file_exists( $template_file ) ) {
            _doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', $template_file ), '1.0.0' );
            return;
        }

        include $template_file;
    }
}

/**
 * in_array() and multidimensional array
 */
if ( !function_exists( 'in_array_r' ) ) {
    function in_array_r( $needle, $haystack, $strict = false ) {
        foreach ( $haystack as $item ) {
            foreach ( $item as $value ) {
                if ( $value['date'] === $needle ) {
                    return true;
                    break;
                }
            }
        }

        return false;
    }
}

/**
 * Get category event by id
 */
if ( !function_exists( 'ovaev_get_category_event_by_id' ) ) {
    function ovaev_get_category_event_by_id( $id = '' ) {
        if ( !$id ) return;

        $i = 0;

        // Get category event
        $cat_event = get_the_terms( $id, 'event_category' );

        if ( !empty( $cat_event ) && is_array( $cat_event ) ):
            $count_cat = count( $cat_event );
        ?>
            <i class="ovaicon-folder-1"></i>
            <?php foreach ( $cat_event as $cat ):
                $i++;
                $separator  = ( $count_cat !== $i ) ? "," : "";
                $link       = get_term_link( $cat->term_id );
                $name       = $cat->name;
            ?>
                <span class="cat-ovaev">
                    <a class="second_font" href="<?php echo esc_url( $link ); ?>">
                        <?php echo esc_html( $name ); ?>
                    </a>
                </span>
                <span class="separator">
                    <?php echo esc_html( $separator ); ?>
                </span>
            <?php endforeach;
        endif;
    }
}

/**
 * Get tag event by id
 */
if ( !function_exists( 'ovaev_get_tag_event_by_id' ) ) {
    function ovaev_get_tag_event_by_id( $id = '' ) {
        if ( !$id ) return;

        // Get tag event
        $tag_event = get_the_terms( $id, 'event_tag' );
        if ( !empty( $tag_event ) && is_array( $tag_event ) ): ?>
            <div class="event-tags">
                <span class="ovatags second_font">
                    <?php esc_html_e( 'Tags: ', 'ovaev' ); ?>
                </span>
                <?php foreach ( $tag_event as $tag ):
                    $link = get_term_link( $tag->term_id );
                    $name = $tag->name;
                ?>
                    <a class="ovaev-tag second_font" href="<?php echo esc_url( $link ); ?>">
                        <?php echo esc_html( $name ); ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <?php
        endif;
    }
}

/**
 * Get event related by id
 */
if ( !function_exists( 'ovaev_get_event_related_by_id' ) ) {
    function ovaev_get_event_related_by_id( $id = '' ) {
        if ( !$id ) return;

        // Get time format
        $time = OVAEV_Settings::archive_event_format_time();

        // Get type
        $arr_type   = [];
        $terms_type = get_the_terms( $id, 'event_category' );
        if ( !empty( $terms_type ) && is_array( $terms_type ) ) {
            foreach ( $terms_type as $type ) {
                $arr_type[] = $type->term_id;
            }
        }

        // Get tag
        $arr_tag    = [];
        $terms_tag  = get_the_terms( $id, 'event_tag' );
        if ( !empty( $terms_tag ) && is_array( $terms_tag ) ) {
            foreach ( $terms_tag as $tag ) {
                $arr_tag[] = $tag->term_id;
            }
        }

        // Base query
        $args_related = [
            'post_type'         => 'event',
            'posts_per_page'    => apply_filters( 'ovaev_single_related_count', 2 ),
            'post__not_in'      => [ $id ],
            'tax_query'         => [
                'relation' => 'OR',
                [
                    'taxonomy' => 'event_category',
                    'field'    => 'term_id',
                    'terms'    => $arr_type
                ],
                [
                    'taxonomy' => 'event_tag',
                    'field'    => 'term_id',
                    'terms'    => $arr_tag
                ]
            ]
        ];

        // Get events
        $event_related = new WP_Query( $args_related );

        return apply_filters( 'ovaev_get_event_related_by_id', $event_related, $id );
    }
}

/**
 * Get events elements
 */
if ( !class_exists( 'ovaev_get_events_elements' ) ) {
    function ovaev_get_events_elements( $args ) {
        if ( $args['category'] === 'all' ) {
            if ( $args['time_event'] === 'current' ) {
                $args_event = [
                    'post_type'      => 'event',
                    'post_status'    => 'publish',
                    'posts_per_page' => $args['total_count'],
                    'meta_query'     => [
                        [
                            [
                                'relation' => 'AND',
                                [
                                    'key'     => 'ovaev_start_date_time',
                                    'value'   => current_time('timestamp' ),
                                    'compare' => '<'
                                ],
                                [
                                    'key'     => 'ovaev_end_date_time',
                                    'value'   => current_time('timestamp' ),
                                    'compare' => '>='
                                ]
                            ]
                        ]
                    ]
                ];
            } elseif ( $args['time_event'] === 'upcoming' ) {
                $args_event = [
                    'post_type'      => 'event',
                    'post_status'    => 'publish',
                    'posts_per_page' => $args['total_count'],
                    'meta_query'     => [
                        [
                            [
                                'key'     => 'ovaev_start_date_time',
                                'value'   => current_time( 'timestamp' ),
                                'compare' => '>'
                            ]
                        ]
                    ]
                ];
            } elseif ( $args['time_event'] === 'past' ) {
                $args_event = [
                    'post_type'      => 'event',
                    'post_status'    => 'publish',
                    'posts_per_page' => $args['total_count'],
                    'meta_query'     => [
                        [
                            'key'     => 'ovaev_end_date_time',
                            'value'   => current_time('timestamp' ),
                            'compare' => '<'
                        ]
                    ]
                ];
            } else { 
                $args_event = [
                    'post_type'      => 'event',
                    'post_status'    => 'publish',
                    'posts_per_page' => $args['total_count']
                ];
            }
        } elseif ( $args['category'] != 'all' ) {
            if ( $args['time_event'] === 'current' ) {
                $args_event = [
                    'post_type'      => 'event',
                    'post_status'    => 'publish',
                    'posts_per_page' => $args['total_count'],
                    'tax_query'      => [
                        [
                            'taxonomy' => 'event_category',
                            'field'    => 'slug',
                            'terms'    => $args['category']
                        ]
                    ],
                    'meta_query' => [
                        [
                            'relation' => 'OR',
                            [
                                'key'     => 'ovaev_start_date_time',
                                'value'   => [ current_time('timestamp' )-1, current_time('timestamp' )+(24*60*60)+1 ],
                                'type'    => 'numeric',
                                'compare' => 'BETWEEN'
                            ],
                            [
                                'relation' => 'AND',
                                [
                                    'key'     => 'ovaev_start_date_time',
                                    'value'   => current_time('timestamp' ),
                                    'compare' => '<'
                                ],
                                [
                                    'key'     => 'ovaev_end_date_time',
                                    'value'   => current_time('timestamp' ),
                                    'compare' => '>='
                                ]
                            ]
                        ]
                    ]
                ];
            } elseif ( $args['time_event'] === 'upcoming' ) {
                $args_event = array(
                    'post_type'      => 'event',
                    'post_status'    => 'publish',
                    'posts_per_page' => $args['total_count'],
                    'tax_query'      => [
                        [
                            'taxonomy' => 'event_category',
                            'field'    => 'slug',
                            'terms'    => $args['category']
                        ]
                    ],
                    'meta_query'     => [
                        [
                            [
                                'key'     => 'ovaev_start_date_time',
                                'value'   => current_time( 'timestamp' ),
                                'compare' => '>'
                            ]
                        ]
                    ]
                );
            } elseif ( $args['time_event'] === 'past' ) {
                $args_event = [
                    'post_type'      => 'event',
                    'post_status'    => 'publish',
                    'posts_per_page' => $args['total_count'],
                    'tax_query'      => [
                        [
                            'taxonomy' => 'event_category',
                            'field'    => 'slug',
                            'terms'    => $args['category']
                        ]
                    ],
                    'meta_query'     => [
                        [
                            'key'     => 'ovaev_end_date_time',
                            'value'   => current_time('timestamp' ),
                            'compare' => '<'
                        ]
                    ]
                ];
            } else {
                $args_event = [
                    'post_type'   => 'event',
                    'post_status' => 'publish',
                    'tax_query'   => [
                        [
                            'taxonomy' => 'event_category',
                            'field'    => 'slug',
                            'terms'    => explode( ',', $args['category'] )
                        ]
                    ],
                    'posts_per_page' => $args['total_count']
                ];
            }
        }

        // Event order
        $args_event_order = [];
        if ( $args['order_by'] === 'ovaev_start_date_time' || $args['order_by'] === 'event_custom_sort' ) {
            $args_event_order = [
                'meta_key'   => $args['order_by'],
                'orderby'    => 'meta_value_num',
                'order'      => $args['order'],
            ];
        } else {
            $args_event_order = [
                'orderby'        => $args['order_by'],
                'order'          => $args['order'],
            ];
        }

        // Query events
        $args_event = array_merge( $args_event, $args_event_order );

        // Get events
        $events = new \WP_Query( $args_event );

        return apply_filters( 'ovaev_get_events_elements', $events, $args );
    }
}

/**
 * Get event categories
 */
if ( !function_exists( 'ovaev_get_categories_events' ) ) {
    function ovaev_get_categories_events( $events, $incl = [], $excl = [] ) {
        $result = [];
        $names  = [];

        if ( $events->have_posts() ) {
            while ( $events->have_posts() ) {
                $events->the_post(); 
                $terms = get_the_terms( get_the_ID(), 'event_category' );

                if ( !empty( $terms ) && is_array( $terms ) ) {
                    foreach ( $terms as $term ) {
                        $term_id    = $term->term_id;
                        $term_name  = $term->name;
                        $term_slug  = $term->slug;

                        if ( $excl && in_array( $term_id, $excl ) ) continue;
                        if ( $incl ) {
                            if ( in_array( $term_id, $incl ) ) {
                                if ( isset( $result[$term_id] ) && $result[$term_id] ) {
                                    $result[$term_id]['count'] += 1;
                                } else {
                                    $result[$term_id] = [
                                        'term_id'       => $term_id,
                                        'name'          => $term_name,
                                        'slug'          => $term_slug,
                                        'count'         => 1,
                                        'icon_class'    => get_term_meta( $term_id, 'ovaev_icon_class', true ),
                                    ];

                                    $names[$term_id] = $term_name;
                                }
                            }
                        } else {
                            if ( isset( $result[$term_id] ) && $result[$term_id] ) {
                                $result[$term_id]['count'] += 1;
                            } else {
                                $result[$term_id] = [
                                    'term_id'       => $term_id,
                                    'name'          => $term_name,
                                    'slug'          => $term_slug,
                                    'count'         => 1,
                                    'icon_class'    => get_term_meta( $term_id, 'ovaev_icon_class', true ),
                                ];

                                $names[$term_id] = $term_name;
                            }
                        }
                    }
                }
            }
        }
        wp_reset_postdata();

        if ( !empty( $names ) && is_array( $names ) && !empty( $result ) && is_array( $result ) ) {
            array_multisort( $names, SORT_ASC, $result );
        }

        return apply_filters( 'ovaev_get_categories_events', $result, $events, $incl, $excl );
    }
}

// Get end date
if ( !function_exists( 'ovaev_get_end_date' ) ) {
    function ovaev_get_end_date( $time = '' ) {
        $end            = '';
        $date_format    = OVAEV_Settings::archive_event_format_date();
        $today          = current_time( 'timestamp' );

        if ( $time == 'today' ) {
            $end = strtotime( date( $date_format, $today ) . ' 23:59' );
        } elseif ( $time == 'week' || $time == 'weekend' ) {
            $end = strtotime( date( $date_format, strtotime( 'this Sunday' ) ) . ' 23:59' );
        } else {
            $end = $today;
        }

        return apply_filters( 'ovaev_get_end_date', $end, $time );
    }
}