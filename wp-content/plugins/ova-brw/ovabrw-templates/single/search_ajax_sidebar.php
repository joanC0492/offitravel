<?php if ( !defined( 'ABSPATH' ) ) exit();

// Extract array
extract( $args );

// Destination
$destination = absint( ovabrw_get_meta_data( 'ovabrw_destination', $_GET ) );
if ( !$destination && isset( $destination_default ) && $destination_default ) {
	$destination = $destination_default;
}

$icon_destination        = ovabrw_get_meta_data( 'icon_destination', $args );
$destination_placeholder = ovabrw_get_meta_data( 'destination_placeholder', $args );
$show_destination 		 = ovabrw_get_meta_data( 'show_destination', $args );

// Get pick-up date
$pickup_date = sanitize_text_field( ovabrw_get_meta_data( 'ovabrw_pickup_date', $_GET ) );
if ( !$pickup_date ) {
	$pickup_date = sanitize_text_field( ovabrw_get_meta_data( 'pickup_date', $_GET ) );
}

// Search position
$search_position = ovabrw_get_meta_data( 'search_position', $args );

// Search sidebar title
$search_title = ovabrw_get_meta_data( 'search_title', $args );

// Adults
$min_adult 		= ovabrw_get_meta_data( 'min_adult', $args );
$max_adult 		= ovabrw_get_meta_data( 'max_adult', $args );
$show_adult 	= ovabrw_get_meta_data( 'show_adult', $args );
$adults_label 	= ovabrw_get_meta_data( 'adults_label', $args );

// Children
$min_children 		= ovabrw_get_meta_data( 'min_children', $args );
$max_children 		= ovabrw_get_meta_data( 'max_children', $args );
$show_children 		= ovabrw_get_meta_data( 'show_children', $args );
$childrens_label 	= ovabrw_get_meta_data( 'childrens_label', $args );

// Babies
$min_baby 		= ovabrw_get_meta_data( 'min_baby', $args );
$max_baby 		= ovabrw_get_meta_data( 'max_baby', $args );
$show_baby 		= ovabrw_get_meta_data( 'show_baby', $args );
$babies_label 	= ovabrw_get_meta_data( 'babies_label', $args );

// Guests
$icon_guests = ovabrw_get_meta_data( 'icon_guests', $args );
$show_guests = ovabrw_get_meta_data( 'show_guests', $args );

// Default adults
$default_adult_number = absint( ovabrw_get_meta_data( 'ovabrw_adults', $_GET ) );
if ( !$default_adult_number ) {
	$default_adult_number = absint( ovabrw_get_meta_data( 'default_adult_number', $args ) );
}

// Default children
$default_children_number = absint( ovabrw_get_meta_data( 'ovabrw_childrens', $_GET ) );
if ( !$default_children_number ) {
	$default_children_number = absint( ovabrw_get_meta_data( 'default_children_number', $args ) );
}

// Default babies
$default_babies_number = absint( ovabrw_get_meta_data( 'ovabrw_babies', $_GET ) );
if ( !$default_babies_number ) {
	$default_babies_number = absint( ovabrw_get_meta_data( 'default_babies_number', $args ) );
}

// Number of guests
$guests = $default_adult_number + $default_children_number + $default_babies_number;
$guests_placeholder = ovabrw_get_meta_data( 'guests_placeholder', $args ); 

// Button
$icon_button     = ovabrw_get_meta_data( 'icon_button', $args );
$button_label 	 = ovabrw_get_meta_data( 'button_label', $args );

// Check-in date
$icon_check_in = ovabrw_get_meta_data( 'icon_check_in', $args );
$show_check_in = ovabrw_get_meta_data( 'show_check_in', $args );

// Custom taxonomy
$icon_custom_taxonomy        = ovabrw_get_meta_data( 'icon_custom_taxonomy', $args );
$custom_taxonomy_placeholder = ovabrw_get_meta_data( 'custom_taxonomy_placeholder', $args );
$slug_custom_taxonomy        = ovabrw_get_meta_data( 'slug_custom_taxonomy', $args );
$slug_value_selected 		 = '';

if ( $slug_custom_taxonomy ) {
	$slug_value_selected = sanitize_text_field( ovabrw_get_meta_data( $slug_custom_taxonomy.'_name', $_GET, 'all' ) );
}
if ( isset( $ctx_slug_value_selected ) ) {
	$slug_value_selected = $ctx_slug_value_selected;
}

if ( ovabrw_get_meta_data( $slug_custom_taxonomy.'_name', $_GET ) ) {
	$slug_value_selected = sanitize_text_field( $_GET[$slug_custom_taxonomy.'_name'] );
}

// Get terms
$terms = get_taxonomy( $slug_custom_taxonomy );

// Posts per page
$posts_per_page = ovabrw_get_meta_data( 'posts_per_page', $args );

// Orderby
$orderby = ovabrw_get_meta_data( 'orderby', $args, 'ID' );

// Order
$order = ovabrw_get_meta_data( 'order', $args, 'DESC' );

// Default category
$default_category = ovabrw_get_meta_data( 'default_category', $args, [] );

// Search sesults layout
$search_results_layout = ovabrw_get_meta_data( 'search_results_layout', $args );

// Grid column
$grid_column = ovabrw_get_meta_data( 'search_results_grid_column', $args, 'column3' );

// Thumbnail type
$thumbnail_type = ovabrw_get_meta_data( 'thumbnail_type', $args, 'image' );

// Avanced Search Settings
$show_advanced_search = ovabrw_get_meta_data( 'show_advanced_search', $args );

// Filter price label
$filter_price_label = ovabrw_get_meta_data( 'filter_price_label', $args );

// Show price filter
$show_price_filter = ovabrw_get_meta_data( 'show_price_filter', $args );

// Review label
$review_label = ovabrw_get_meta_data( 'review_label', $args );

// Show review filter
$show_review_filter = ovabrw_get_meta_data( 'show_review_filter', $args );

// Filter category label
$filter_category_label 	= ovabrw_get_meta_data( 'filter_category_label', $args );
$show_category_filter 	= ovabrw_get_meta_data( 'show_category_filter', $args );

// Exclude category
$excl_category = ovabrw_get_meta_data( 'excl_category', $args );

// Show category
$show_category = '';
if ( 'yes' === $show_advanced_search && 'yes' === $show_category_filter ) {
	$show_category = 'yes';
}

// Label duration
$filter_duration_label 	= ovabrw_get_meta_data( 'filter_duration_label', $args );
$show_duration_filter 	= ovabrw_get_meta_data( 'show_duration_filter', $args );

// Duration fields
$duration_fields = ovabrw_get_meta_data( 'duration_fields', $args );

// Filter settings
$show_filter = ovabrw_get_meta_data( 'show_filter', $args );

// Tour found text
$tour_found_text = ovabrw_get_meta_data( 'tour_found_text', $args );

// Clear filter text
$clear_filter_text = ovabrw_get_meta_data( 'clear_filter_text', $args );

// Get product categories
$product_categories = get_categories([
	'taxonomy'   => 'product_cat',
    'orderby' 	 => 'ID',
	'order' 	 => 'DESC',
	'exclude' 	 => $excl_category
]);

// Get min max price
$prices              = ovabrw_get_filtered_price();
$min_price           = floor( $prices->min_price );
$max_price           = round( $prices->max_price );
$currency_symbol     = get_woocommerce_currency_symbol();

// Taxonomies
$mutiple_custom_taxonomy 	= ovabrw_get_meta_data( 'mutiple_custom_taxonomy', $args );
$list_taxonomies 			= ovabrw_get_meta_data( 'list_custom_taxonomy', $args, [] );
$data_taxonomies 			= [];

?>

<div class="ovabrw-search-ajax ovabrw-search-ajax-sidebar">
	<div class="wrap-search-ajax wrap-search-ajax-sidebar <?php echo esc_attr( $search_position ); ?>" 
	    data-adults="<?php echo esc_attr( ovabrw_get_meta_data( 'default_adult_number', $args ) ); ?>"
	    data-childrens="<?php echo esc_attr( ovabrw_get_meta_data( 'default_children_number', $args ) ); ?>" 
	    data-babies="<?php echo esc_attr( ovabrw_get_meta_data( 'default_babies_number', $args ) ); ?>" 
	    data-sort_by_default="<?php echo esc_attr( ovabrw_get_meta_data( 'sort_by_default', $args ) ); ?>"
	    data-start-price="<?php echo esc_attr( $min_price ); ?>"
	    data-end-price="<?php echo esc_attr( $max_price ); ?>"
	    data-grid_column="<?php echo esc_attr( $grid_column ); ?>"
	    data-thumbnail-type="<?php echo esc_attr( $thumbnail_type ); ?>">
		<div class="search-main-content">
		    <!-- Filter -->
			<?php if ( 'yes' === $show_filter ): ?>
		        <div class="ovabrw-tour-filter">
		        	<div class="left-filter">
		        		<span class="tour-found-text number-result-tour-found">
			        		<?php echo esc_html__( '0', 'ova-brw' ); ?>
			        	</span>
		        		<span class="tour-found-text">
			        		<?php echo esc_html( $tour_found_text ); ?>
			        	</span>
			        	<span class="clear-filter">
			        		<?php echo esc_html( $clear_filter_text ); ?>
			        	</span>
		        	</div>
		        	<div class="right-filter">
		        		<div class="filter-sort">
		        			<input
		        				type="text"
		        				class="input_select_input"
		        				name="sr_sort_by_label"
		        				value="<?php echo esc_html__( 'Sort by', 'ova-brw' ); ?>"
		        				autocomplete="off"
		        				readonly="readonly"
		        			/>
							<input
								type="hidden"
								class="input_select_input_value"
								name="sr_sort_by"
								value="date"
							/>
							<ul class="input_select_list" style="display: none;">
							    <li class="term_item <?php if ( 'date' === $sort_by_default ) { echo 'term_item_selected'; } ?>" 
							    	data-id="date"
							    	data-value="<?php esc_attr_e ('Sort by latest','ova-brw' ); ?>">
								    <?php echo esc_html__( 'Latest', 'ova-brw' ); ?>
								</li>
								<li class="term_item <?php if ( 'rating_desc' === $sort_by_default ) { echo 'term_item_selected'; } ?>" 
									data-id="rating_desc" 
									data-value="<?php esc_attr_e('Sort by rating','ova-brw'); ?>">
									<?php echo esc_html__( 'Rating', 'ova-brw' ); ?>
								</li>
								<li class="term_item <?php if ( 'price_asc' === $sort_by_default ) { echo 'term_item_selected'; } ?>" 
									data-id="price_asc" 
									data-value="<?php esc_attr_e( 'Sort by price: low to high', 'ova-brw' ); ?>">
									<?php echo esc_html__( 'Price: low to high', 'ova-brw' ); ?>
								</li>
								<li class="term_item <?php if ( 'price_desc' === $sort_by_default ) { echo 'term_item_selected'; } ?>" 
									data-id="price_desc" 
									data-value="<?php esc_attr_e( 'Sort by price: high to low', 'ova-brw' ); ?>">
									<?php echo esc_html__(' Price: high to low', 'ova-brw' ); ?>
								</li>
							</ul>
						</div>
						<div class="asc_desc_sort">
		        			<i aria-hidden="true" class="asc_sort icomoon icomoon-chevron-up"></i>
		        		    <i aria-hidden="true" class="desc_sort icomoon icomoon-chevron-down"></i>
		        		</div>
		        		<div class="filter-result-layout">
			        		<i aria-hidden="true" class="filter-layout <?php if ( 'list' === $search_results_layout ) { echo 'filter-layout-active'; } ?> icomoon icomoon-list" data-layout="list"></i>
							<i aria-hidden="true" class="filter-layout <?php if ( 'grid' === $search_results_layout ) { echo 'filter-layout-active'; } ?> icomoon icomoon-gird" data-layout="grid"></i>
						</div>
		         	</div>	
		        </div>
		    <?php endif; ?>

			<!-- Load more -->
			<div class="wrap-load-more" style="display: none;">
				<svg class="loader" width="50" height="50">
					<circle cx="25" cy="25" r="10" />
					<circle cx="25" cy="25" r="20" />
				</svg>
			</div><!-- End load more -->

			<!-- Search result -->
			<?php if ( 'yes' === $show_filter ):
				if ( 'date' === $sort_by_default ): ?>
					<div 
						class="brw-search-ajax-result" 
						data-order="DESC" 
						data-orderby="date" 
						data-defautl-category="<?php echo esc_attr( json_encode( $default_category ) ); ?>" 
						data-show-category="<?php echo esc_attr( $show_category ); ?>" 
						data-orderby_meta_key="" 
						data-posts-per-page="<?php echo esc_attr( $posts_per_page ); ?>"
					>
					</div>
				<?php elseif ( 'rating_desc' === $sort_by_default ): ?>
		            <div 
						class="brw-search-ajax-result" 
						data-order="DESC" 
						data-orderby="meta_value_num"
						data-orderby_meta_key="_wc_average_rating" 
						data-posts-per-page="<?php echo esc_attr( $posts_per_page ); ?>" 
						data-defautl-category="<?php echo esc_attr( json_encode( $default_category ) ); ?>" 
						data-show-category="<?php echo esc_attr( $show_category ); ?>"
					>
					</div>
				<?php elseif ( 'price_asc' === $sort_by_default ): ?>
		            <div 
						class="brw-search-ajax-result" 
						data-order="ASC" 
						data-orderby="meta_value_num"
						data-orderby_meta_key="_price" 
						data-posts-per-page="<?php echo esc_attr( $posts_per_page ); ?>" 
						data-defautl-category="<?php echo esc_attr( json_encode( $default_category ) ); ?>" 
						data-show-category="<?php echo esc_attr( $show_category ); ?>" 
					>
					</div>
				<?php elseif ( 'price_desc' === $sort_by_default ): ?>
		            <div 
						class="brw-search-ajax-result" 
						data-order="DESC" 
						data-orderby="meta_value_num"
						data-orderby_meta_key="_price" 
						data-posts-per-page="<?php echo esc_attr( $posts_per_page ); ?>" 
						data-defautl-category="<?php echo esc_attr( json_encode( $default_category ) ); ?>" 
						data-show-category="<?php echo esc_attr( $show_category ); ?>" 
					>
					</div>
				<?php endif;
			else: ?>
				<div 
					class="brw-search-ajax-result" 
					data-order="<?php echo esc_attr( $order ); ?>" 
					data-orderby="<?php echo esc_attr( $orderby ); ?>"
					data-orderby_meta_key="" 
					data-posts-per-page="<?php echo esc_attr( $posts_per_page ); ?>" 
					data-defautl-category="<?php echo esc_attr( json_encode( $default_category ) ); ?>" 
					data-show-category="<?php echo esc_attr( $show_category ); ?>">
				</div>
			<?php endif; ?>
		</div>

		<!-- Search Sidebar -->
		<div class="search-ajax-sidebar">
            <!--Search -->
			<div class="ovabrw-search">
				<?php if ( $search_title ) : ?>
			    	<h4 class="search-title">
			    		<?php echo esc_html( $search_title ); ?>
			    		<i aria-hidden="true" class="icomoon icomoon-chevron-up"></i>
			    	</h4>
				<?php endif; ?>
				<form class="ovabrw-search-form" method="POST" autocomplete="off">	
					<div class="ovabrw-s-field">
						<!-- destinations dropdown -->
						<?php if ( 'yes' === $show_destination ): ?>
							<div class="search-field">
								<div class="ovabrw-label">
									<?php if ( $icon_destination ) {
								    	\Elementor\Icons_Manager::render_icon( $icon_destination, [ 'aria-hidden' => 'true' ] );
								    } ?>
								</div>
								<div class="ovabrw-input search_in_destination">
									<?php echo ovabrw_destination_dropdown( $destination_placeholder, $destination ); ?>
								</div>
							</div>
						<?php endif;

						// Custom taxonomy
						if ( !empty( $slug_custom_taxonomy ) && !empty( $terms ) ): ?>
							<div class="search-field">
								<div class="ovabrw-label">
									<?php if( $icon_custom_taxonomy ) {
								    	\Elementor\Icons_Manager::render_icon( $icon_custom_taxonomy, [ 'aria-hidden' => 'true' ] );
								    } ?>
								</div>
								<div class="ovabrw-input search_in_taxonomy">
									<?php echo ovabrw_search_taxonomy_dropdown( $slug_custom_taxonomy, $custom_taxonomy_placeholder, $slug_value_selected, 'required' ); ?>
								</div>
							</div>
						<?php endif;

						// Taxonomies
						if ( 'yes' === $mutiple_custom_taxonomy && ovabrw_array_exists( $list_taxonomies ) ):
							foreach ( $list_taxonomies as $data_taxonomy ):
								$item_slug_taxonomy 		= ovabrw_get_meta_data( 'item_slug_taxonomy', $data_taxonomy );
								$item_icon_taxonomy 		= ovabrw_get_meta_data( 'item_icon_taxonomy', $data_taxonomy );
								$item_taxonomy_placeholder 	= ovabrw_get_meta_data( 'item_taxonomy_placeholder', $data_taxonomy );

								if ( !$item_slug_taxonomy ) continue;

								// Default
								$item_slug_default = sanitize_text_field( ovabrw_get_meta_data( $item_slug_taxonomy.'_name', $_GET, 'all' ) );

								// Value
								$item_taxonomy_value = sanitize_text_field( ovabrw_get_meta_data( 'item_taxonomy_value_'.$item_slug_taxonomy, $data_taxonomy ) );
								if ( $item_taxonomy_value ) {
									$item_slug_default = $item_taxonomy_value;
								}

								if ( ovabrw_get_meta_data( $item_slug_taxonomy.'_name', $_GET ) ) {
									$item_slug_default = sanitize_text_field( $_GET[$item_slug_taxonomy.'_name'] );
								}

								$item_tern = get_taxonomy( $item_slug_taxonomy );
							
								if ( $item_slug_taxonomy && !empty( $item_tern ) ):
									array_push( $data_taxonomies, trim( $item_slug_taxonomy ) );
								?>
									<div class="search-field">
										<div class="ovabrw-label">
											<?php if ( $item_icon_taxonomy ) {
										    	\Elementor\Icons_Manager::render_icon( $item_icon_taxonomy, [ 'aria-hidden' => 'true' ] );
										    } ?>
										</div>
										<div class="ovabrw-input search_in_taxonomy">
											<?php echo ovabrw_search_taxonomy_dropdown( $item_slug_taxonomy, $item_taxonomy_placeholder, $item_slug_default, 'required' ); ?>
										</div>
									</div>
								<?php endif;
							endforeach;
						endif; ?>

			            <!-- Check-in date -->
			            <?php if ( 'yes' === $show_check_in ): ?>
							<div class="search-field">
								<div class="ovabrw-input">
									<div class="ovabrw-label">
										<?php if ( $icon_check_in ) {
									    	\Elementor\Icons_Manager::render_icon( $icon_check_in, [ 'aria-hidden' => 'true' ] );
									    } ?>
									</div>
									<?php ovabrw_text_input([
							            'type'      	=> 'text',
							            'id'        	=> ovabrw_unique_id( 'checkin-date' ),
							            'class'     	=> 'ovabrw-datepicker-start',
							            'name'      	=> 'ovabrw_pickup_date',
							            'value'     	=> $pickup_date,
							            'placeholder' 	=> ovabrw_get_placeholder_date(),
							            'attrs' 		=> [
							            	'data-date' => $pickup_date
							            ]
							        ]); ?>
								</div>
							</div>
						<?php endif;

						// Guests
						if ( 'yes' === $show_guests ): ?>
							<div class="search-field guestspicker-control">
								<div class="ovabrw-input ovabrw-guestspicker-content-wrapper">
									<div class="ovabrw-label">
										<?php if( $icon_guests ) {
									    	\Elementor\Icons_Manager::render_icon( $icon_guests, [ 'aria-hidden' => 'true' ] );
									    } ?>
									</div>
									<div class="ovabrw-guestspicker">
										<div class="guestspicker">
											<span class="gueststotal">
												<?php echo esc_html( $guests ); ?>
											</span>
											<span class="guestslabel">
												<?php echo esc_html( $guests_placeholder ); ?>
											</span>
										</div>
									</div>
									<div class="ovabrw-guestspicker-content">
										<?php if ( 'yes' === $show_adult ): ?>
											<div class="guests-buttons">
												<div class="description">
													<label>
														<?php echo esc_html( $adults_label ); ?>
													</label>
												</div>
												<div class="guests-button">
													<div class="guests-icon minus">
														<i class="fas fa-minus"></i>
													</div>
													<input
														type="text"
														id="ovabrw_adults"
														class="ovabrw_adults"
														name="ovabrw_adults"
														value="<?php echo esc_attr( $default_adult_number ); ?>"
														min="<?php echo esc_attr( $min_adult ); ?>"
														max="<?php echo esc_attr( $max_adult ); ?>"
													/>
													<div class="guests-icon plus">
														<i class="fas fa-plus"></i>
													</div>
												</div>
											</div>
										<?php endif;

										// Children
										if ( 'yes' === $show_children ): ?>
											<div class="guests-buttons">
												<div class="description">
													<label>
														<?php echo esc_html( $childrens_label ); ?>
													</label>
												</div>
												<div class="guests-button">
													<div class="guests-icon minus">
														<i class="fas fa-minus"></i>
													</div>
													<input
														type="text"
														id="ovabrw_childrens"
														class="ovabrw_childrens"
														name="ovabrw_childrens"
														value="<?php echo esc_attr( $default_children_number ); ?>"
														min="<?php echo esc_attr( $min_children ); ?>"
														max="<?php echo esc_attr( $max_children ); ?>"
													/>
													<div class="guests-icon plus">
														<i class="fas fa-plus"></i>
													</div>
												</div>
											</div>
										<?php endif;

										// Show baby
										if ( 'yes' === $show_baby ): ?>
											<div class="guests-buttons">
												<div class="description">
													<label>
														<?php echo esc_html( $babies_label ); ?>
													</label>
												</div>
												<div class="guests-button">
													<div class="guests-icon minus">
														<i class="fas fa-minus"></i>
													</div>
													<input
														type="text"
														id="ovabrw_babies"
														class="ovabrw_babies"
														name="ovabrw_babies"
														value="<?php echo esc_attr( $default_babies_number ); ?>"
														min="<?php echo esc_attr( $min_baby ); ?>"
														max="<?php echo esc_attr( $max_baby ); ?>"
													/>
													<div class="guests-icon plus">
														<i class="fas fa-plus"></i>
													</div>
												</div>
											</div>
										<?php endif; ?>
									</div>
								</div>
							</div>
						<?php endif; ?>
					</div>

					<!-- Advanced Search -->
					<?php if ( 'yes' === $show_advanced_search ): ?>
				        <div class="ovabrw-search-advanced-sidebar">
				        	<div class="search-advanced-field-wrapper">
				        		<!-- Price Filter -->
				        		<?php if ( 'yes' === $show_price_filter ): ?>
					        	    <div class="search-advanced-field price-field">
					        	    	<span class="ovabrw-label">
					        	    		<?php echo esc_html( $filter_price_label ); ?>
					        	    		<i aria-hidden="true" class="icomoon icomoon-chevron-up"></i>
					        	    	</span>
					        	    	<div class="search-advanced-content">
					        	    		<div class="slider-wrapper">
										    <div id="brw-tour-price-slider"></div>
											</div>
											<div class="brw-tour-price-input" data-currency_symbol="<?php echo esc_attr( $currency_symbol ); ?>" data-auto="<?php echo esc_attr( apply_filters( OVABRW_PREFIX.'price_auto', false ) ); ?>">
												<div class="tour-price-value">
													<span>
														<?php echo esc_html( $currency_symbol ); ?>
													</span>
							        	    	    <input
							        	    	    	type="text"
							        	    	    	class="brw-tour-price-from"
							        	    	    	value="<?php echo esc_attr($min_price); ?>"data-value="<?php echo esc_attr($min_price); ?>"
							        	    	    />
												</div>
												<div class="tour-price-value">
													<span>
														<?php echo esc_html( $currency_symbol ); ?>
													</span>
													<input
														type="text"
														class="brw-tour-price-to"
														value="<?php echo esc_attr( $max_price ); ?>" data-value="<?php echo esc_attr( $max_price ); ?>"
													/>
												</div>
											</div>
					        	    	</div>
					        	    </div>
					        	<?php endif; ?>

				        	    <!-- Rating Filter -->
				        	    <?php if ( 'yes' === $show_review_filter ): ?>
					        	    <div class="search-advanced-field rating-field">
					        	    	<span class="ovabrw-label">
					        	    		<?php echo esc_html( $review_label ); ?>
					        	    		<i aria-hidden="true" class="icomoon icomoon-chevron-up"></i>
					        	    	</span>
					        	    	<div class="search-advanced-content">
						        	     	<?php for ( $i = 5; $i >= 1; $i-- ): ?>
						        	     		<div class="total-rating-stars">
						        	     			<div class="input-rating">
						        	     				<input
						        	     					type="checkbox"
						        	     					id="rating-filter-<?php echo esc_attr( $i ); ?>"
						        	     					class="rating-filter"
						        	     					name="rating_value[<?php echo esc_attr( $i );?>]"
						        	     					value="<?php echo esc_attr( $i ); ?>"
						        	     				/>
						        	     				<label for="rating-filter-<?php echo esc_attr( $i ); ?>">
						        	     					<?php switch ( $i ) {
						        	     						case 1: ?>
																	<span class="rating-stars">
																		<span class="star star-1" data-rating-val="1"><i class="fas fa-star"></i></span>
																	</span>
																<?php break;
																case 2: ?>
																	<span class="rating-stars">
																		<span class="star star-1" data-rating-val="1"><i class="fas fa-star"></i></span>
																		<span class="star star-2" data-rating-val="2"><i class="fas fa-star"></i></span>
																	</span>
																<?php break;
																case 3: ?>
																	<span class="rating-stars">
																		<span class="star star-1" data-rating-val="1"><i class="fas fa-star"></i></span>
																		<span class="star star-2" data-rating-val="2"><i class="fas fa-star"></i></span>
																		<span class="star star-3" data-rating-val="3"><i class="fas fa-star"></i></span>
																	</span>
																<?php break;
																case 4: ?>
																	<span class="rating-stars">
																		<span class="star star-1" data-rating-val="1"><i class="fas fa-star"></i></span>
																		<span class="star star-2" data-rating-val="2"><i class="fas fa-star"></i></span>
																		<span class="star star-3" data-rating-val="3"><i class="fas fa-star"></i></span>
																		<span class="star star-4" data-rating-val="4"><i class="fas fa-star"></i></span>
																	</span>
															    <?php break;
															    case 5: ?>
																	<span class="rating-stars">
																		<span class="star star-1" data-rating-val="1"><i class="fas fa-star"></i></span>
																		<span class="star star-2" data-rating-val="2"><i class="fas fa-star"></i></span>
																		<span class="star star-3" data-rating-val="3"><i class="fas fa-star"></i></span>
																		<span class="star star-4" data-rating-val="4"><i class="fas fa-star"></i></span>
																		<span class="star star-5" data-rating-val="5"><i class="fas fa-star"></i></span> 
																	</span>
															    <?php break;
															} ?>
														</label>

						        	     			</div>
						        	     		</div>
						        	     	<?php endfor; ?>
						        	     </div>
					        	    </div>
					        	<?php endif;

					        	// Tour Categories Filter
					        	if ( 'yes' === $show_category_filter ): ?>
					        	    <div class="search-advanced-field tour-categories-field">
					        	    	<span class="ovabrw-label">
					        	    		<?php echo esc_html( $filter_category_label ); ?>
					        	    	</span>
					        	     	<?php foreach ( $product_categories as $pro_cat ):
					        	     		if ( $pro_cat->category_parent == 0 ):
					        	     			$cat_id 	= $pro_cat->term_id; 
										        $sub_cats 	= get_categories([
										        	'taxonomy'   => 'product_cat',
									                'child_of'   => 0,
									                'parent'     => $cat_id,
									                'orderby' 	 => 'ID',
													'order' 	 => 'DESC',
													'exclude' 	 => $excl_category
										        ]);
					        	     		?>
						        	     		<div class="tour-category-field">
						        	     			<input 
						        	     				id="tour-category-filter-<?php echo esc_attr( $pro_cat->slug ); ?>" 
						        	     				type="checkbox" 
						        	     				class="tour-category-filter" 
						        	     				name="category_value" 
						        	     				value="<?php echo esc_attr( $pro_cat->slug ); ?>" 
						        	     				<?php echo in_array( $pro_cat->slug, $default_category ) ? 'checked' : ''; ?>
						        	     			/>
							        	     		<label for="tour-category-filter-<?php echo esc_attr( $pro_cat->slug ) ;?>">
														<span class="tour-category-name">
															<?php echo esc_html( $pro_cat->name ) ; ?>
														</span>
													</label>
													<?php if ( $sub_cats ):
														foreach ( $sub_cats as $sub_category ): ?>
												            <div class="tour-category-field-child">
									        	     			<input
									        	     				type="checkbox"
									        	     				id="tour-category-filter-<?php echo esc_attr( $sub_category->slug ) ; ?>"
									        	     				class="tour-category-filter"
									        	     				name="category_value"
									        	     				value="<?php echo esc_attr( $sub_category->slug ); ?>"
									        	     				<?php echo in_array( $sub_category->slug, $default_category ) ? 'checked' : ''; ?>
									        	     			/>
										        	     		<label for="tour-category-filter-<?php echo esc_attr( $sub_category->slug ) ; ?>">
																	<span class="tour-category-name">
																		<?php echo esc_html( $sub_category->name ); ?>
																	</span>
																</label>
																<?php 
									        	     			$sub_cat_id = $sub_category->term_id;
														        $sub_cats_2 = get_categories([
														        	'taxonomy'   => 'product_cat',
													                'child_of'   => 0,
													                'parent'     => $sub_cat_id,
													                'orderby' 	 => 'ID',
																	'order' 	 => 'DESC',
																	'exclude' 	 => $excl_category
														        ]);

															    if ( $sub_cats_2 ):
															    	foreach ( $sub_cats_2 as $sub_category_2 ): ?>
															            <div class="tour-category-field-child">
												        	     			<input 
												        	     				id="tour-category-filter-<?php echo esc_attr($sub_category_2->slug) ;?>" 
												        	     				type="checkbox" 
												        	     				class="tour-category-filter" 
												        	     				name="category_value" 
												        	     				value="<?php echo esc_attr( $sub_category_2->slug) ;?>" 
												        	     				<?php echo in_array( $sub_category_2->slug, $default_category ) ? 'checked' : ''; ?>
												        	     			>

													        	     		<label for="tour-category-filter-<?php echo esc_attr($sub_category_2->slug) ;?>">
																				<span class="tour-category-name">
																					<?php echo esc_html( $sub_category_2->name ) ; ?>
																				</span>
																			</label>
																		</div>
																	<?php endforeach;
																endif; ?>
															</div>
											        <?php endforeach;
											        endif; ?> 
							        	     	</div>
							        	    <?php endif; ?>
					        	     	<?php endforeach;?>
					        	    </div>
					        	<?php endif;

					        	// Duration filter
					        	if ( 'yes' === $show_duration_filter ): ?>
					        	    <div class="search-advanced-field tour-duration-field">
					        	    	<span class="ovabrw-label">
					        	    		<?php echo esc_html( $filter_duration_label ); ?>
					        	    	</span>
					        	    	<?php if ( is_array($duration_fields) ):
					        	    		foreach ( $duration_fields as $k => $duration_field ):
					        	    			$duration_type = ovabrw_get_meta_data( 'duration_type', $duration_field );
					        	    			$duration_name = ovabrw_get_meta_data( 'duration_name', $duration_field );

					                			if ( 'day' === $duration_type ) {
					                				$value_from = ovabrw_get_meta_data( 'duration_day_value_from', $duration_field );
					                				$value_to   = ovabrw_get_meta_data( 'duration_day_value_to', $duration_field );
					                		    } elseif ( 'hour' === $duration_type ) {
					                		    	$value_from = ovabrw_get_meta_data( 'duration_hour_value_from', $duration_field );
					                				$value_to   = ovabrw_get_meta_data( 'duration_hour_value_to', $duration_field );
					                            }
					                		?>
					                		<div class="duration-field">
						                		<input
						                			type="radio"
						                			id="duration-filter-<?php echo esc_attr( $k ); ?>"
						                			class="duration-filter"
						                			name="duration_value_from"
						                			value="<?php echo esc_attr( $value_from ); ?>"
						                		/>
						                		<input
						                			type="hidden"
						                			class="duration-filter-to"
						                			name="duration_value_to"
						                			value="<?php echo esc_attr( $value_to ); ?>"
						                		/>
						                		<input
						                			type="hidden"
						                			class="duration-filter-type"
						                			name="duration_value_type"
						                			value="<?php echo esc_attr( $duration_type ); ?>"
						                		/>
						        	     		<label for="duration-filter-<?php echo esc_attr( $k ); ?>">
													<span class="duration-name">
														<?php echo esc_html( $duration_name ); ?>
													</span>
												</label>
					                		</div>
					                		<?php endforeach;
					                	endif;?>
					        	    </div>
					        	<?php endif; ?>
				        	</div>
				        </div>
				    <?php endif; ?>

				    <!-- button -->
					<div class="ovabrw-search-btn">
						<button class="ovabrw-btn" type="submit">
							<?php
								if ( $icon_button ) {
							    	\Elementor\Icons_Manager::render_icon( $icon_button, [ 'aria-hidden' => 'true' ] );
							    }   

							    echo wp_kses_post( $button_label );
						    ?>
						</button>		
					</div>
				</form>
			</div>
		</div>
    </div>
</div>