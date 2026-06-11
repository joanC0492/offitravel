<div class="row_site">
	<div class="container_site">
		<div id="main-content" class="main">

			<header class="page-header">
				<h1 class="page-title">
					<?php esc_html_e('Search Results for: ','tripgo'); printf( '<span>%s</span>', get_search_query() ); ?>
				</h1>
			</header>

			<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
			        <?php get_template_part( 'template-parts/blog/default' ); ?>
			<?php endwhile; ?>

				<div class="pagination-wrapper">
			    	<?php 
			    		 $args = array(
			                'type'      => 'list',
			                'next_text' => '<i class="ovaicon-next"></i>',
			                'prev_text' => '<i class="ovaicon-back"></i>',
			            );

			            the_posts_pagination($args);
			    	 ?>
				</div>
				
			<?php else : ?>
			        <p>
			        	<?php esc_html_e( 'Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'tripgo' ); ?>
			        </p>
					<?php get_search_form(); ?>
			<?php endif; ?>

			
		</div>
		<?php get_sidebar(); ?>
	</div>
</div>