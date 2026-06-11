<?php if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Class OVAEV_Tag_Event_Widget
 */
if ( !class_exists( 'OVAEV_Tag_Event_Widget' ) ) {

    class OVAEV_Tag_Event_Widget extends WP_Widget {

        /**
         * Constructor
         */
        public function __construct() {
            $widget_ops = [
                'classname'                   => 'widget_tag_cloud',
                'description'                 => esc_html__( 'Get list tag event', 'ovaev' ),
                'customize_selective_refresh' => true
            ];
            parent::__construct( 'event_tag', esc_html__( 'Tag Event' ), $widget_ops );
        }

        /**
         * New widget
         */
        public function widget( $args, $instance ) {
            $title = apply_filters( 'widget_title', $instance['title'] );
            $title = !empty( $title ) ? $title : esc_html__( 'Tags', 'ovaev' );
            
            // Before widget
            echo wp_kses_post( $args['before_widget'] );
            if ( $title ) {
                echo wp_kses_post( $args['before_title'] . $title . $args['after_title'] );
            }

            // Get categories
            $categories = get_categories([
                'taxonomy'   => 'event_tag',
               'orderby'    => 'name'
            ]);
            
            echo ovaev_get_template( 'widgets/tag_widget.php', [ 'categories' => $categories ] );
            echo wp_kses_post( $args['after_widget'] );
        }

        /**
         * Form
         */
        public function form( $instance ) {
            // Defaults.
            $instance     = wp_parse_args( (array)$instance, [ 'title' => '' ] );
            $hierarchical = isset( $instance['hierarchical'] ) ? (bool)$instance['hierarchical'] : false;

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
            <?php 
        }

        /**
         * Update widget
         */
        public function update( $new_instance, $old_instance ) {
            $instance                 = $old_instance;
            $instance['title']        = sanitize_text_field( $new_instance['title'] );

            return $instance;
        }
    }
}

// Widgets init
add_action( 'widgets_init', function() {
    register_widget( 'OVAEV_Tag_Event_Widget' );
});