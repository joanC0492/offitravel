<?php if ( !defined( 'ABSPATH' ) ) exit(); ?>

<?php if ( OVAEV_Settings::ovaev_show_title_single() == 'yes' ): ?>
	<h1 class="event_title">
		<?php the_title(); ?>
	</h1>
<?php endif; ?>