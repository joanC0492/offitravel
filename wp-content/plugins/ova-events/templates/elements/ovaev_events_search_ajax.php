<?php if ( !defined( 'ABSPATH' ) ) exit();

$lang 			= OVAEV_Settings::archive_format_date_lang();
$date_format 	= OVAEV_Settings::archive_event_format_date();
$time_format 	= OVAEV_Settings::archive_event_format_time();

$settings 		= $args['settings'];
$events			= $args['events'];

$layout 		= $settings['layout'];
$column 		= $settings['column'];
$posts_per_page = $settings['posts_per_page'];
$order 			= $settings['order'];
$orderby 		= $settings['order_by'];
$category_slug 	= $settings['category'];
$time_event 	= $settings['time_event'];

$total_pages 	= $events->max_num_pages;
$current 		= 1;
$first_day   	= apply_filters( 'ovaev_calendar_first_day' , get_option( 'start_of_week' ) );

?>

<div class="ovaev-wrapper-search-ajax">
	<!-- Search Form -->
	<div class="ovaev-search-ajax-form search_archive_event">
		<form action="<?php echo esc_url(get_post_type_archive_link( 'event' )); ?>" method="POST" name="search_ajax_event" autocomplete="off">
			<div class="start_date">
				<input type="text" 
					id="ovaev_start_date_search" 
					class="ovaev_start_date_search" 
					data-lang="<?php echo esc_attr($lang); ?>" 
					data-date="<?php echo esc_attr($date_format); ?>" 
					data-time="<?php echo esc_attr($time_format); ?>" 
					data-first-day="<?php echo esc_attr( $first_day ); ?>" 
					placeholder="<?php echo esc_attr__( 'Choose Date', 'ovaev' ); ?>" 
					name="ovaev_start_date_search" 
					value="" 
				/>
				<i class="far fa-calendar-alt"></i>
			</div>

			<div class="end_date">
				<input 
					type="text" 
					id="ovaev_end_date_search" 
					class="ovaev_end_date_search" 
					data-lang="<?php echo esc_attr($lang); ?>" 
					data-date="<?php echo esc_attr($date_format); ?>" 
					data-first-day="<?php echo esc_attr( $first_day ); ?>" 
					placeholder="<?php echo esc_attr__( 'Choose Date', 'ovaev' ); ?>" 
					name="ovaev_end_date_search" 
					value="" 
				/>
				<i class="far fa-calendar-alt"></i>
			</div>

			<div class="ovaev_cat_search">
				<?php $dropdown = apply_filters( 'OVAEV_event_type', '' ); ?>
				<i class="arrow_carrot-down "></i>
			</div>
		</form>
	</div>
	<!-- End Search Form -->

	<!-- Events -->
	<div class="ovaev-search-ajax-container">
		<div class="search-ajax-content">
			<div class="archive_event ovaev-search-ajax-events<?php echo ' '.esc_attr( $column );?>">
				<?php if( $events->have_posts() ) : while( $events->have_posts() ) : $events->the_post();
					switch ($layout) {
						case '1':
							ovaev_get_template( 'event-templates/event-type1.php' );
							break;
						case '2':
							ovaev_get_template( 'event-templates/event-type2.php' );
							break;
						case '3':
							ovaev_get_template( 'event-templates/event-type3.php' );
							break;
						case '4':
							ovaev_get_template( 'event-templates/event-type4.php' );
							break;
						case '5':
							ovaev_get_template( 'event-templates/event-type5.php' );
							break;
						case '6':
							ovaev_get_template( 'event-templates/event-type6.php' );
							break;
						default:
							ovaev_get_template( 'event-templates/event-type1.php' );
					}
				?>

				<?php endwhile; else: wp_reset_postdata(); ?>
					<div class="search_not_found">
						<?php esc_html_e( 'Not Found Events', 'ovaev' ); ?>
					</div>
				<?php endif; wp_reset_postdata(); ?>
			</div>

			<div class="data-events" 
				data-layout="<?php echo esc_attr( $layout ); ?>" 
				data-column="<?php echo esc_attr( $column ); ?>" 
				data-per-page="<?php echo esc_attr( $posts_per_page ); ?>" 
				data-order="<?php echo esc_attr( $order ); ?>" 
				data-orderby="<?php echo esc_attr( $orderby ); ?>" 
				data-category-slug="<?php echo esc_attr( $category_slug ); ?>" 
				data-time-event="<?php echo esc_attr( $time_event ); ?>">
			</div>
		</div>

		<!-- Loader -->
		<div class="wrap_loader">
			<svg class="loader" width="50" height="50">
				<circle cx="25" cy="25" r="10" stroke="#a1a1a1"/>
				<circle cx="25" cy="25" r="20" stroke="#a1a1a1"/>
			</svg>
		</div>
	<!-- End Loader -->
	</div>
	<!-- End Events -->

	<!-- Pagination -->
	<div class="search-ajax-pagination-wrapper">
	<?php if ( $total_pages > 1 ): ?>
		<div class="search-ajax-pagination" data-total-page="<?php echo esc_attr( $total_pages ); ?>">
			<ul>
				<?php for ( $i = 1; $i <= $total_pages; $i++ ): ?>
					<?php if ( $i == 1 ): ?>
						<li>
							<span class="prev page-numbers" data-paged="<?php echo esc_attr( $current - 1 ); ?>">
								<?php esc_html_e( 'Previous', 'ovaev' ); ?>
							</span>
						</li>
						<li>
							<span class="page-numbers current" data-paged="<?php echo esc_attr( $i ); ?>">
								<?php echo esc_attr( $i ); ?>
							</span>
						</li>
					<?php elseif ( $i == $total_pages ): ?>
						<li>
							<span class="page-numbers" data-paged="<?php echo esc_attr( $i ); ?>">
								<?php echo esc_attr( $i ); ?>
							</span>
						</li>
						<li>
							<span class="next page-numbers" data-paged="<?php echo esc_attr( $current + 1 ); ?>">
								<?php esc_html_e( 'Next', 'ovaev' ); ?>
							</span>
						</li>
					<?php else: ?>
						<li>
							<span class="page-numbers" data-paged="<?php echo esc_attr( $i ); ?>">
								<?php echo esc_attr( $i ); ?>
							</span>
						</li>
					<?php endif; ?>
				<?php endfor; ?>
			</ul>
		</div>
	<?php endif; ?>
	<!-- End Pagination -->
	</div>

</div>

