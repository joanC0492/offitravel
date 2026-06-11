<header class="page-header">
	<h1 class="page-title">
		<?php esc_html_e( 'Nothing Found', 'tripgo'); ?>
	</h1>
</header>

<div class="page-content page-content-none">
	<?php if ( is_home() && current_user_can( 'publish_posts' ) ) : ?>

	<p><?php printf( __( 'Ready to publish your first post? <a href="%1$s">Get started here</a>.', 'tripgo' ), admin_url( 'post-new.php' ) ); ?></p>

	<?php else : ?>

	<p><?php esc_html_e( 'It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.', 'tripgo' ); ?></p>
	<?php get_search_form(); ?>

	<?php endif; ?>
</div>