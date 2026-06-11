<?php $sticky_class = is_sticky()?'sticky':''; ?>

<article id="post-<?php the_ID(); ?>" <?php post_class('post-wrap '. $sticky_class); ?>  >
	<div class="content">
		<?php if( ( has_post_format('audio') || has_post_format('gallery') || has_post_format('video' ) || has_post_thumbnail() ) && tripgo_blog_show_media() == 'yes' ) : ?>
			<div class="post-media">
				<?php 
					if( has_post_format('audio') ){

					 	get_template_part( 'template-parts/parts/audio' );

					}elseif( has_post_format('gallery') ){

						get_template_part( 'template-parts/parts/gallery' );

					}elseif( has_post_format('video' )){

						get_template_part( 'template-parts/parts/video' );

					}elseif(has_post_thumbnail()){

						get_template_part( 'template-parts/parts/thumbnail_grid' );

			        }
				?>
			</div>
		<?php endif; ?>

		
		<?php if( tripgo_blog_show_title() == 'yes' ){
			get_template_part( 'template-parts/parts/title' );
		} ?>
		
		<?php get_template_part( 'template-parts/parts/meta' ); ?>
		
		<?php if( tripgo_blog_show_excerpt() == 'yes' ){ ?>
			<div class="post-excerpt">
				<p>
					<?php echo wp_kses_post( wp_trim_words( get_the_excerpt(), 14 ) ); ?>
				</p>
			</div>
		<?php } ?>

		<?php if( tripgo_blog_show_readmore() == 'yes' ){
			get_template_part( 'template-parts/parts/readmore' ); 
		} ?>
		
	</div>		
</article>



