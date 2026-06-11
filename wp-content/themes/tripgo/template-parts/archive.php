<?php if ( !defined( 'ABSPATH' ) ) exit;

// Get blog template
$blog_template = apply_filters( 'tripgo_blog_template', '' );

?>
<div class="row_site">
	<div class="container_site">
		<div id="main-content" class="main">
			<?php if ( have_posts() ): ?>
				<div class="blog_<?php echo esc_attr( $blog_template ); ?>">
					<?php while ( have_posts() ) : the_post();
						get_template_part( 'template-parts/blog/' . sanitize_file_name( $blog_template ) );
					endwhile; ?>
				</div>
			    <div class="pagination-wrapper">
			    	<?php the_posts_pagination([
			    		'type'      => 'list',
		                'next_text' => '<i class="ovaicon-next"></i>',
		                'prev_text' => '<i class="ovaicon-back"></i>'
			    	]); ?>
				</div>
			<?php else:
				get_template_part( 'template-parts/content/content-none' );
			endif; wp_reset_postdata(); ?>
		</div>
		<?php get_sidebar(); ?>
	</div>
</div>