<?php if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Class OVA_HF_Builder
 */
if ( !function_exists( 'OVA_HF_Builder' ) ) {

    class OVA_HF_Builder {

        /**
         * instance
         */
        private static $instance;

        /**
         * Constructor
         */
        public function __construct() {
            add_action( 'init', [ $this, 'ova_framework_hf_el' ] );
            add_action( 'template_redirect', [ $this, 'block_template_frontend' ] );
            add_filter( 'single_template', [ $this, 'ova_load_canvas_template' ] );

            if ( is_admin() ) {
                add_action( 'load-post.php', [ $this, 'init_metabox' ] );
                add_action( 'load-post-new.php', [ $this, 'init_metabox' ] );
            }
        }

        /**
         * Make Custom Post Type - ova_hf_elementor
         */
        public function ova_framework_hf_el() {
            $labels = [
                'name'               => esc_html__( 'Builder Header Footer', 'post type general name', 'ova-framework' ),
                'singular_name'      => esc_html__( 'Builder Header Footer', 'post type singular name', 'ova-framework' ),
                'menu_name'          => esc_html__( 'Builder Header Footer', 'admin menu', 'ova-framework' ),
                'name_admin_bar'     => esc_html__( 'HF', 'add new on admin bar', 'ova-framework' ),
                'add_new'            => esc_html__( 'Add New HF', 'Slide', 'ova-framework' ),
                'add_new_item'       => esc_html__( 'Add New HF', 'ova-framework' ),
                'new_item'           => esc_html__( 'New HF', 'ova-framework' ),
                'edit_item'          => esc_html__( 'Edit HF', 'ova-framework' ),
                'view_item'          => esc_html__( 'View HF', 'ova-framework' ),
                'all_items'          => esc_html__( 'All HF', 'ova-framework' ),
                'search_items'       => esc_html__( 'Search HF', 'ova-framework' ),
                'parent_item_colon'  => esc_html__( 'Parent HF:', 'ova-framework' ),
                'not_found'          => esc_html__( 'No HF found.', 'ova-framework' ),
                'not_found_in_trash' => esc_html__( 'No HF found in Trash.', 'ova-framework' )
            ];

            $args = [
                'labels'              => $labels,
                'public'              => true,
                'rewrite'             => true,
                'show_ui'             => true,
                'show_in_menu'        => true,
                'show_in_nav_menus'   => false,
                'exclude_from_search' => true,
                'capability_type'     => 'post',
                'hierarchical'        => false,
                'menu_icon'           => 'dashicons-editor-kitchensink',
                'supports'            => [ 'title', 'elementor' ]
            ];

            register_post_type( 'ova_framework_hf_el', $args );
        }

        /**
         * Block template
         */
        public function block_template_frontend() {
            if ( is_singular( 'ova_framework_hf_el' ) && ! current_user_can( 'edit_posts' ) ) {
                wp_redirect( site_url(), 301 );
                die;
            }
        }

        /**
         * Load canvas template
         */
        public function ova_load_canvas_template( $single_template ) {
            global $post;

            if ( 'ova_framework_hf_el' == $post->post_type ) {
                $elementor_2_0_canvas = ELEMENTOR_PATH . 'modules/page-templates/templates/canvas.php';
                if ( file_exists( $elementor_2_0_canvas ) ) {
                    return $elementor_2_0_canvas;
                } else {
                    return ELEMENTOR_PATH . '/includes/page-templates/canvas.php';
                }
            }

            return $single_template;
        }

        /**
         * Meta box initialization.
         */
        public function init_metabox() {
            add_action( 'add_meta_boxes', [ $this, 'add_metabox' ] );
            add_action( 'save_post', [ $this, 'save_metabox' ], 10, 2 );
        }
     
        /**
         * Adds the meta box.
         */
        public function add_metabox() {
            add_meta_box(
                'hf_el',
                esc_html__( 'Elementor Header Footer Option', 'ova-framework' ),
                [ $this, 'ova_render_metabox' ],
                'ova_framework_hf_el',
                'normal',
                'high'
            );
        }

        /**
         * Renders the meta box.
         */
        public function ova_render_metabox( $post ) {
            // Add nonce for security and authentication.
            $value = get_post_meta( $post->ID, 'hf_options', true );
        ?>
            <label>
                <?php esc_html_e( 'Display in', 'ova-framework' ); ?>&nbsp;&nbsp;
                <select name="hf_options" id="hf_options" >
                    <option value="footer"<?php selected( $value, 'footer' ); ?>>
                        <?php esc_html_e( 'Footer', 'ova-framework' ); ?>
                    </option>
                    <option value="header"<?php selected( $value, 'header' ); ?>>
                        <?php esc_html_e( 'Header', 'ova-framework' ); ?>
                    </option>
                    <option value="none"<?php selected( $value, 'none' ); ?>>
                        <?php esc_html_e( 'None', 'ova-framework' ); ?>
                    </option>
                </select>
            </label>
            <?php wp_nonce_field( 'custom_nonce_action', 'custom_nonce' );
        }

        /**
         * Save meta box
         */
        public function save_metabox( $post_id, $post ) {
            // Add nonce for security and authentication.
            $nonce_name   = isset( $_POST['custom_nonce'] ) ? $_POST['custom_nonce'] : '';
            $nonce_action = 'custom_nonce_action';
     
            // Check if nonce is set.
            if ( !isset( $nonce_name ) ) return;
     
            // Check if nonce is valid.
            if ( !wp_verify_nonce( $nonce_name, $nonce_action ) ) return;
     
            // Check if user has permissions to save data.
            if ( !current_user_can( 'edit_post', $post_id ) ) return;

            if ( array_key_exists( 'hf_options', $_POST ) ) {
                update_post_meta( $post_id, 'hf_options', sanitize_text_field( $_POST['hf_options'] ) );
            }
     
            // Check if not an autosave.
            if ( wp_is_post_autosave( $post_id ) ) return;
     
            // Check if not a revision.
            if ( wp_is_post_revision( $post_id ) ) return;
        }
    }

    // init class
    new OVA_HF_Builder();
}