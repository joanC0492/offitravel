<?php if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Class OVAEV_List_Event_Widget
 */
if ( !class_exists( 'OVAEV_List_Event_Widget' ) ) {

    class OVAEV_List_Event_Widget extends WP_Widget {

        /**
         * Constructor
         */
        public function __construct() {
            $widget_ops = [
                'classname'                   => 'widget_list_event',
                'description'                 => esc_html__( 'Get list upcomming event', 'ovaev' ),
                'customize_selective_refresh' => true
            ];
            parent::__construct( 'event_upcomming', esc_html__( 'Upcomming Event' ), $widget_ops );
        }

        /**
         * New widget
         */
        public function widget( $args, $instance ) {
            $title          = apply_filters( 'widget_title', $instance['title'] );
            $title          = !empty( $title ) ? $title : esc_html__( 'Upcoming Events', 'ovaev' );
            $count          = isset( $instance['count'] ) ? $instance['count'] : 5;
            $hierarchical   = !empty( $instance['hierarchical'] ) ? '1' : '0';

            // Before widget
            echo wp_kses_post( $args['before_widget'] );
            if ( $title ) {
                echo wp_kses_post( $args['before_title'] . $title . $args['after_title'] );
            }

            $args_event_basic = [
                'post_type'         => 'event',
                'posts_per_page'    => $count,
                'orderby'           => 'meta_value',
                'meta_key'          => 'ovaev_start_date_time',
                'order'             => 'ASC',
                'meta_query'        => [
                    [
                        'key'       => 'ovaev_start_date_time',
                        'value'     => current_time( 'timestamp' ),
                        'compare'   => '>'
                    ]
                ]
            ];

            // Query with Category (Type)
            $args_event_tax = [];

            $selected_categories = $instance['wcw_selected_categories'] ? $instance['wcw_selected_categories'] : '';
            $wcw_action_on_cat = $instance['wcw_action_on_cat'] ? $instance['wcw_action_on_cat'] : '';
            if ( $wcw_action_on_cat == 'include' ) {
                $args_event_tax = [
                    'tax_query' => [
                        [
                            'taxonomy' => 'event_category',
                            'field'    => 'term_id',
                            'terms'    => $selected_categories,
                            'operator' => 'IN'
                        ]
                    ]
                ];
            } elseif ( $wcw_action_on_cat == 'exclude' ) {
                $args_event_tax = [
                    'tax_query' => [
                        [
                            'taxonomy' => 'event_category',
                            'field'    => 'term_id',
                            'terms'    => $selected_categories,
                            'operator' => 'NOT IN'
                        ]
                    ]
                ];
            }

            $args_event     = array_merge( $args_event_basic, $args_event_tax );
            $list_events    = get_posts( $args_event );

            echo ovaev_get_template( 'widgets/list_event.php', [ 'list_events' => $list_events ] );
            echo wp_kses_post( $args['after_widget'] );
        }

        /**
         * Form
         */
        public function form( $instance ) {
            // Defaults.
            $instance = wp_parse_args( (array)$instance, [
                'title' => 'Upcomming Event',
                'count' => '5'
            ]);
            $wcw_selected_categories    = ( !empty( $instance['wcw_selected_categories'] ) && !empty( $instance['wcw_action_on_cat'] ) ) ? $instance['wcw_selected_categories'] : '';
            $wcw_action_on_cat          = !empty( $instance['wcw_action_on_cat'] ) ? $instance['wcw_action_on_cat'] : '';

            ?>
            <p>
                <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
                    <?php _e( 'Title:' ); ?>
                </label>
                <input
                    type="text"
                    class="widefat"
                    id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
                    name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
                    value="<?php echo esc_attr( $instance['title'] ); ?>"
                />
            </p>
            <p>
                <label>
                    <?php esc_html_e( 'Count:', 'ovaev' ); ?>
                </label>
                <input
                    type="text"
                    class="widefat"
                    id="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>"
                    name="<?php echo esc_attr( $this->get_field_name( 'count' ) ); ?>"
                    value="<?php echo esc_attr( $instance['count'] ); ?>"
                />
            </p>
            <div class="wcwmultiselect">
                <select id="<?php echo esc_attr( $this->get_field_id( 'wcw_action_on_cat' ) ); ?>" class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'wcw_action_on_cat' ) ); ?>">
                    <option value=""<?php selected( $wcw_action_on_cat,'' ); ?>>
                        <?php esc_html_e( 'Show All Category:', 'ovaev' ); ?>
                    </option>       
                    <option value="include"<?php selected( $wcw_action_on_cat, 'include' ); ?>>
                        <?php esc_html_e( 'Include Selected Category:', 'ovaev' ); ?>
                    </option>       
                    <option value="exclude"<?php selected( $wcw_action_on_cat, 'exclude' ); ?>>
                        <?php esc_html_e( 'Exclude Selected Category:', 'ovaev' ); ?>
                    </option>
                </select>
                <div class="wcwcheckboxes" id="wcwcb-<?php echo esc_attr( $this->get_field_id( 'wcw_action_on_cat' ) ); ?>">
                <?php $i = 0;
                    
                    // Get terms
                    $terms = get_terms([
                        'taxonomy' => 'event_category'
                    ]);
                    if ( !empty( $terms ) && is_array( $terms ) ) {
                        foreach ( $terms as $term ) {
                            echo '<label for="'.esc_attr( $this->get_field_id( 'wcw_action_on_cat' ) ).'-'.$i.'"><input type="checkbox" id="'.esc_attr( $this->get_field_id( 'wcw_action_on_cat' ) ).'-'.$i.'"  '.checked(true, ($wcw_selected_categories!='' ? in_array($term->term_id,$wcw_selected_categories) : ($wcw_selected_categories=='' ? true : '')), false).' name="'.esc_attr( $this->get_field_name( 'wcw_selected_categories' ) ).'[]" value="'.$term->term_id.'"/>'.$term->name.'</label></br>';
                            $i++;
                        }
                    }
                ?>   
                </div>
            </div>
            <?php
        }

        /**
         * Update data
         */
        public function update( $new_instance, $old_instance ) {
            $instance                 = $old_instance;
            $instance['title']        = sanitize_text_field( $new_instance['title'] );
            $instance['count']        = sanitize_text_field( $new_instance['count'] );
            $instance['wcw_selected_categories']    = !empty( $new_instance['wcw_selected_categories'] ) ? $new_instance['wcw_selected_categories'] : '';
            $instance['wcw_action_on_cat']          = !empty( $new_instance['wcw_action_on_cat'] ) ? $new_instance['wcw_action_on_cat'] : '';

            return $instance;
        }
    }
}

// Widgets init
add_action( 'widgets_init', function() {
    register_widget( 'OVAEV_List_Event_Widget' );
});