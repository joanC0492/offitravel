<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get categories
$categories = isset( $args['categories'] ) ? $args['categories'] : '';

if ( !empty( $categories ) && is_array( $categories ) ): ?>
    <div class="tagcloud">
        <?php foreach ( $categories as $cat ): ?>
            <a class="tag-cloud-link" href="<?php echo esc_url( get_term_link( $cat->term_id ) ); ?>">
                <?php echo esc_html( $cat->cat_name ); ?>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>