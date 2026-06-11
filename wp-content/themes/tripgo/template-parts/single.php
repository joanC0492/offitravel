<?php if ( !defined( 'ABSPATH' ) ) exit; ?>
<div class="row_site">
	<div class="container_site">
		<div id="main-content" class="main">
			<?php if ( have_posts() ) : while ( have_posts() ) : the_post();
				get_template_part( 'template-parts/post/content-post' );

				// Comments
			    if ( tripgo_post_show_leave_a_reply() == 'yes' ){ 
			    	if ( comments_open() || get_comments_number() ) {
				    	comments_template();
				    }
				}
			endwhile; else :
			    get_template_part( 'template-parts/post/content-none' );
			endif; wp_reset_postdata(); ?>
		</div>
		<?php get_sidebar(); ?>
	</div>
</div>

