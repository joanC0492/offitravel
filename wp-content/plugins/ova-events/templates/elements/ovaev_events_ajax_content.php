<?php if ( !defined( 'ABSPATH' ) ) exit;

// Get event data
$event_data = $args['data_posts'];

// Get slide options
$slide_options = $args['slide_options'];

// Get settings
$settings = $args['settings'];

// Get layout
$layout = $settings['layout'] ? $settings['layout'] : 1;

if ( !empty( $event_data ) ): ?>
	<div class="ovapo_project_grid full_width">
		<?php if ( $settings['show_filter'] == 'yes' ): ?>
			<div class="button-filter container">
				<?php if ( $settings['show_all'] == 'yes' ): ?>
					<button
						class="second_font"
						data-filter="<?php echo esc_attr( 'all' ); ?>"
						data-order="<?php echo esc_attr( $settings['order_post'] ); ?>"
						data-orderby="<?php echo esc_attr( $settings['orderby_post'] ); ?>"
						data-first_term="<?php echo esc_attr( $settings['first_term'] ); ?>"
						data-term_id_filter_string="<?php echo esc_attr( $settings['term_id_filter_string'] ); ?>"
						data-number_post="<?php echo esc_attr( $settings['number_post'] ); ?>"
						data-layout='<?php echo esc_attr( $layout ); ?>'
						data-show_featured="<?php echo esc_attr( $settings['show_featured'] ); ?>">
						<?php esc_html_e( 'All', 'ovaev' ); ?>
					</button>
				<?php endif; ?>
				<?php if ( count( $settings['terms'] ) > 0 ):
					foreach ( $settings['terms'] as $term ): ?>
						<button
							class="second_font"
							data-filter="<?php echo esc_attr( $term->term_id ); ?>"
							data-order="<?php echo esc_attr( $settings['order_post'] ); ?>"
							data-orderby="<?php echo esc_attr( $settings['orderby_post'] ); ?>"
							data-first_term="<?php echo esc_attr( $settings['first_term'] ); ?>"
							data-term_id_filter_string="<?php echo esc_attr( $settings['term_id_filter_string'] ); ?>"
							data-number_post="<?php echo esc_attr( $settings['number_post'] ); ?>"
							data-layout='<?php echo esc_attr( $layout ); ?>'
							data-show_featured="<?php echo esc_attr( $settings['show_featured'] ); ?>">
							<?php esc_html_e( $term->name, 'ovaev' ); ?>
						</button>
					<?php endforeach;
				endif; ?>
			</div>
		<?php endif; ?>
		<div class="content ovapo_project_slide">
			<div class="items grid" data-options="<?php echo esc_attr( json_encode( $slide_options ) ); ?>">	
				<?php if ( $event_data->have_posts() ) : ?>
					<div class="swiper swiper-loading">
						<div class="swiper-wrapper">
							<?php while ( $event_data->have_posts() ) : $event_data->the_post();
								// Get id
								$id = get_the_ID();

								// Get category name
								$cat_name = [];

								// Get event category
								$ovapo_cat = get_the_terms( $id, 'event_category' );
								if ( $ovapo_cat != '' ) {
									foreach ( $ovapo_cat as $key => $value ) {
										$cat_name[] = $value->name;
									}
								}

								$category_name = join( ', ', $cat_name ); ?>

								<div class="swiper-slide">
									<?php switch ( $layout ) {
										case '1':
											ovaev_get_template( 'event-templates/event-type1.php' );
											break;
										case '2':
											ovaev_get_template( 'event-templates/event-type3.php' );
											break;
										default:
											ovaev_get_template( 'event-templates/event-type1.php' );
									} ?>
								</div>
							<?php endwhile; ?>
						</div>
					</div>
					<?php if ( $slide_options['nav'] ): ?>
						<div class="button-nav button-prev">
							<i class="<?php echo esc_attr( $slide_options['nav_prev'] ); ?>" aria-hidden="true"></i>
						</div>
						<div class="button-nav button-next">
							<i class="<?php echo esc_attr( $slide_options['nav_next'] ); ?>" aria-hidden="true"></i>
						</div>
					<?php endif;
					if ( $slide_options['dots'] ): ?>
						<div class="button-dots"></div>
					<?php endif;
				endif; wp_reset_postdata(); ?>
			</div>
	    	<div class="title-readmore">
				<?php if ( $settings['show_read_more'] == 'yes' ): ?>
					<div class="btn_grid" >
						<a href="<?php echo get_post_type_archive_link( 'event' ); ?>" class="read-more second_font btn_grid_event">
							<?php echo esc_html( $settings['text_read_more'] ); ?>
						</a>
					</div>
				<?php endif; ?>
	    	</div>
			<div class="wrap_loader">
				<svg class="loader" width="50" height="50">
					<circle cx="25" cy="25" r="10" stroke="#a1a1a1"/>
					<circle cx="25" cy="25" r="20" stroke="#a1a1a1"/>
				</svg>
			</div>
		</div>
	</div>
<?php else : ?>
	<div class="search_not_found">
		<?php esc_html_e( 'No events found.', 'ovaev' ); ?>
	</div>
<?php endif;