<?php if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Class OVAEV_Category_Event_Widget
 */
if ( !class_exists( 'OVAEV_Category_Event_Widget' ) ) {

    class OVAEV_Category_Event_Widget extends WP_Widget {

        /**
         * Constructor
         */
        public function __construct() {
            $widget_ops = [
                'classname'                   => 'widget_categories',
                'description'                 => esc_html__( 'Get list category event', 'ovaev' ),
                'customize_selective_refresh' => true
            ];
            parent::__construct( 'event_category', esc_html__( 'Categories Event' ), $widget_ops );
        }

        /**
         * Add widget
         */
        public function widget( $args, $instance ) {
            $title = apply_filters( 'widget_title', $instance['title'] );
            $title = !empty( $title ) ? $title : esc_html__( 'Categories', 'ovaev' );
            $count = !empty( $instance['count'] ) ? '1' : '0';

            // Before widget
            echo wp_kses_post( $args['before_widget'] );

            if ( $title ) {
                echo wp_kses_post( $args['before_title'] . $title . $args['after_title'] );
            }

            $categories = get_categories([
                'taxonomy'   => 'event_category',
                'orderby'    => 'name',
                'show_count' => $count
            ]);

            echo ovaev_get_template( 'widgets/category_widget.php', [
                'categories'    => $categories,
                'count'         => $count
            ]);
            echo wp_kses_post( $args['after_widget'] );
        }

        /**
         * Form
         */
        public function form( $instance ) {
            // Defaults.
            $instance   = wp_parse_args( (array)$instance, [ 'title' => '' ] );
            $count      = isset( $instance['count'] ) ? (bool)$instance['count'] : false;
            ?>
            <p>
                <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
                    <?php _e( 'Title:' ); ?>
                </label>
                <input
                    type="text"
                    id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
                    class="widefat"
                    name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
                    value="<?php echo esc_attr( $instance['title'] ); ?>"
                />
            </p>
            <p>
                <input
                    type="checkbox"
                    id="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>"
                    class="checkbox"
                    name="<?php echo esc_attr( $this->get_field_name( 'count' ) ); ?>"
                    <?php checked( $count ); ?>
                />
                <label for="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>">
                    <?php _e( 'Show post counts' ); ?>
                </label>
            </p>
            <?php 
        }

        /**
         * Update data
         */
        public function update( $new_instance, $old_instance ) {
            $instance           = $old_instance;
            $instance['title']  = sanitize_text_field( $new_instance['title'] );
            $instance['count']  = !empty( $new_instance['count'] ) ? 1 : 0;
            
            return $instance;
        }
    }
}

// Widgets init
add_action( 'widgets_init', function() {
    register_widget( 'OVAEV_Category_Event_Widget' );
});