<?php if ( !defined( 'ABSPATH' ) ) exit();

global $wp_query;

// Get event type template
$event_type_temp = isset( $_GET['event_type_temp'] ) ? $_GET['event_type_temp'] : OVAEV_Settings::archive_event_type();

// Get column
$event_col = isset( $_GET['col'] ) ? $_GET['col'] : OVAEV_Settings::archive_event_col();

// Get header
get_header();

?>
<div class="container-event">
	<div class="content-event archive-event-page">
		<?php do_action( 'ovaev_search_form' ); ?>
		<div class="archive_event <?php echo esc_attr( $event_col ); ?>">
			<?php if ( have_posts() ) : while ( have_posts() ) : the_post();
				ovaev_get_template( 'event-templates/event-'.$event_type_temp.'.php' );
			endwhile; else: ?>
				<div class="search_not_found">
					<?php esc_html_e( 'Not Found Events', 'ovaev' ); ?>
				</div>
			<?php endif; wp_reset_postdata(); ?>
		</div>
		<?php if ( $wp_query->max_num_pages > 1 ): ?>
			<div class="search-ajax-pagination events_pagination">
				<?php echo paginate_links( apply_filters( 'el_pagination_args', [
					'base'         => esc_url_raw( str_replace( 999999999, '%#%', get_pagenum_link( 999999999, false ) ) ),
					'format'       => '',
					'add_args'     => '',
					'current'      => max( 1, get_query_var( 'paged' ) ),
					'total'        => $wp_query->max_num_pages,
					'prev_text'    => esc_html__( 'Previous', 'ovaev' ),
					'next_text'    => esc_html__( 'Next', 'ovaev' ),
					'type'         => 'list',
					'end_size'     => 3,
					'mid_size'     => 3
				])); ?>
			</div>
		<?php endif; ?>
	</div>
</div>
<?php get_footer();