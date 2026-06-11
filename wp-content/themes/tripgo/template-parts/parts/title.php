<?php if ( is_singular() ) : ?>
	<?php if( apply_filters( 'tripgo_show_singular_title', true ) ){ ?>
		<h1 class="post-title">
		  <?php the_title(); ?>
		</h1>
	<?php } ?>
<?php else : ?>
	<a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>">
		<h2 class="post-title">
	  		<?php the_title(); ?>
	  	</h2>
	</a>
<?php endif; ?>

