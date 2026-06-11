<?php if ( ! defined( 'ABSPATH' ) ) exit();

// Get data
$language 		= OVAEV_Settings::archive_format_date_lang();
$date_format 	= OVAEV_Settings::archive_event_format_date();
$time_format 	= OVAEV_Settings::archive_event_format_time();
$first_day   	= apply_filters( 'ovaev_calendar_first_day' , get_option( 'start_of_week' ) );

// Get events
$events = isset( $args['events'] ) ? $args['events'] : '';

// Get settings
$settings = isset( $args['settings'] ) ? $args['settings'] : [];

// Get template
$template = isset( $settings['template'] ) && $settings['template'] ? $settings['template'] : 1;

// Get column
$column = isset( $settings['column'] ) ? $settings['column'] : '';

// Get time
$time = isset( $settings['time'] ) ? $settings['time'] : '';

// Get include category
$incl = isset( $settings['incl_category'] ) && $settings['incl_category'] ? explode( ',', $settings['incl_category'] ) : [];

// Get exclude category
$excl = isset( $settings['excl_category'] ) && $settings['excl_category'] ? explode( ',', $settings['excl_category'] ) : [];

// Get event categories
$categories = ovaev_get_categories_events( $events, $incl, $excl );

?>

<div class="ovaev-filter">
	<div class="ovaev-filter-form">
		<div class="ovaev-date-fields">
			<input
				type="text"
				id="ovaev-start-date"
				class="ovaev-start-date-filter"
				name="ovaev_start_date"
				data-language="<?php echo esc_attr( $language ); ?>"
				data-format="<?php echo esc_attr( $date_format ); ?>"
				data-first-day="<?php echo esc_attr( $first_day ); ?>"
				placeholder="<?php echo esc_attr__( 'Start date', 'ovaev' ); ?>"
				autocomplete="off"
			/>
			<input
				type="text"
				id="ovaev-end-date"
				class="ovaev-start-date-filter"
				name="ovaev_end_date"
				data-language="<?php echo esc_attr( $language ); ?>"
				data-format="<?php echo esc_attr( $date_format ); ?>"
				data-first-day="<?php echo esc_attr( $first_day ); ?>"
				placeholder="<?php echo esc_attr__( 'End date', 'ovaev' ); ?>"
				autocomplete="off"
			/>
		</div>
		<div class="ovaev-keyword-field">
			<input
				type="text"
				class="ovaev-keyword"
				name="ovaev_keyword"
				placeholder="<?php echo esc_attr__( 'Search by keyword(optional)', 'ovaev' ); ?>"
				autocomplete="off"
			/>
		</div>
		<div class="ovaev-filter-time">
			<label class="ovaev-btn-checkbox">
				<?php if ( $time === 'today' ): ?>
					<span class="checkmark active" data-time="today">
						<?php esc_html_e( 'Today', 'ovaev' ); ?>
					</span>
				<?php else: ?>
					<span class="checkmark" data-time="today">
						<?php esc_html_e( 'Today', 'ovaev' ); ?>
					</span>
				<?php endif; ?>
 			</label>
			<label class="ovaev-btn-checkbox">
				<?php if ( $time === 'week' ): ?>
					<span class="checkmark active" data-time="week">
						<?php esc_html_e( 'Week', 'ovaev' ); ?>
					</span>
				<?php else: ?>
					<span class="checkmark" data-time="week">
						<?php esc_html_e( 'Week', 'ovaev' ); ?>
					</span>
				<?php endif; ?>
 			</label>
			<label class="ovaev-btn-checkbox">
				<?php if ( $time === 'weekend' ): ?>
					<span class="checkmark active" data-time="weekend">
						<?php esc_html_e( 'Weekend', 'ovaev' ); ?>
					</span>
				<?php else: ?>
					<span class="checkmark" data-time="weekend">
						<?php esc_html_e( 'Weekend', 'ovaev' ); ?>
					</span>
				<?php endif; ?>
 			</label>
 			<input type="hidden" name="ovaev_time" value="<?php echo esc_attr( $time ); ?>" />
		</div>
		<div class="ovaev-btn-search">
			<button class="ovaev-btn-submit">
				<?php esc_html_e( 'Search', 'ovaev' ); ?>
			</button>
		</div>
	</div>
	<?php if ( !empty( $categories ) && is_array( $categories ) ): ?>
		<div class="ovaev-filter-categories">
			<h3 class="title">
				<?php esc_html_e( 'EVENTS', 'ovaev' ); ?>
			</h3>
			<ul class="event-categories">
				<?php foreach ( $categories as $item_cat ): ?>
					<li class="item-cat">
						<?php if ( $item_cat['icon_class'] ): ?>
							<i class="<?php echo esc_attr( $item_cat['icon_class'] ); ?>" aria-hidden="true"></i>
						<?php endif; ?>
						<a href="javascript:void(0)" class="ovaev-term" data-term-id="<?php echo esc_attr( $item_cat['term_id'] ); ?>">
							<?php echo esc_html( $item_cat['name'] ); ?>
							<span class="count">
								<?php printf( esc_html__( '(%s)' ), $item_cat['count'] ); ?>
							</span>	
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>
	<div class="ovaev-filter-container">
		<div class="ovaev-filter-content">
			<div class="archive_event ovaev-filter-column<?php echo esc_attr( $column ); ?>">
				<?php if ( $events->have_posts() ) : while(  $events->have_posts() ) : $events->the_post();
					switch ( $template ) {
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
				endwhile; else: ?>
					<div class="search_not_found">
						<?php esc_html_e( 'No event found', 'ovaev' ); ?>
					</div>
				<?php endif; wp_reset_postdata(); ?>
			</div>
		</div>
		<div class="wrap_loader">
			<svg class="loader" width="50" height="50">
				<circle cx="25" cy="25" r="10" stroke="#a1a1a1"/>
				<circle cx="25" cy="25" r="20" stroke="#a1a1a1"/>
			</svg>
		</div>
		<input
			type="hidden"
			name="ovaev-data-filter"
			data-settings="<?php echo esc_attr( json_encode( $settings ) ); ?>"
		/>
	</div>
</div>