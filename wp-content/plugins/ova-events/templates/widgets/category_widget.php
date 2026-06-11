<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get categories
$categories = isset( $args['categories'] ) ? $args['categories'] : '';

// Get count
$count = isset( $args['count'] ) ? (int)$args['count'] : 0;

if ( !empty( $categories ) && is_array( $categories ) ): ?>
    <ul>
        <?php foreach ( $categories as $cat ): ?>
            <li>
                <a href="<?php echo esc_url( get_term_link( $cat->term_id ) ); ?>">
                    <?php echo esc_html( $cat->cat_name ); ?>
                </a>
                <?php if ( $count == 1 ) {
                    echo '('.esc_html( $cat->count ).')';
                } ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>