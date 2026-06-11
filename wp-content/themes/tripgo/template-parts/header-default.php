<div class="row_site">
	<div class="container_site">
		<header class="wrap_header" >
			
			<div class="site-brand">
				<a href="<?php echo esc_url(home_url('/')); ?>" class="navbar-brand">
					<?php if( get_theme_mod( 'logo', '' ) != '' ) { ?>
						<img src="<?php  echo esc_url( get_theme_mod('logo', '') ); ?>" alt="<?php bloginfo('name');  ?>">
					<?php }else { ?> 
						<span class="blogname">
							<?php bloginfo('name');  ?>
						</span>
					<?php } ?>
				</a>
			</div>

			<?php if ( has_nav_menu( 'primary' ) ) : ?>

				<nav class="main-navigation" role="navigation">
	                <button class="menu-toggle">
	                	<span>
	                		<?php echo esc_html__( 'Menu', 'tripgo' ); ?>
	                	</span>
	                </button>
					<?php
						wp_nav_menu( [
							'theme_location'  => 'primary',
							'container_class' => 'primary-navigation',
						] );
					?>
	            </nav>
				
			<?php endif; ?>
			
		</header>
	</div>
</div>

<div class="wrap_breadcrumbs">
	<div class="row_site">
		<div class="container_site">
			<?php echo get_template_part( 'template-parts/parts/breadcrumbs' ); ?>
		</div>
	</div>
</div>

