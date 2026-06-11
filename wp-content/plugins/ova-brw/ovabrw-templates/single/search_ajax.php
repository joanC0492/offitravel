<?php if ( !defined( 'ABSPATH' ) ) exit();

extract( $args );

// Get posts per page
$posts_per_page = ovabrw_get_meta_data( 'posts_per_page', $args, -1 );

// Get orderby
$orderby = ovabrw_get_meta_data( 'orderby', $args, 'ID' );

// Get order
$order = ovabrw_get_meta_data( 'order', $args, 'DESC' );

// Get default category
$defautl_category = ovabrw_get_meta_data( 'default_category', $args, [] );

// Method
$args['method'] = 'POST';

// Results layout
$search_results_layout = ovabrw_get_meta_data( 'search_results_layout', $args );

// Columns
$grid_column = ovabrw_get_meta_data( 'search_results_grid_column', $args );

// Thumbnail type
$thumbnail_type = ovabrw_get_meta_data( 'thumbnail_type', $args, 'image' );

// Avanced Search Settings
$show_advanced_search = ovabrw_get_meta_data( 'show_advanced_search', $args );

// Show price filter
$show_price_filter = ovabrw_get_meta_data( 'show_price_filter', $args );

// Show review filter
$show_review_filter = ovabrw_get_meta_data( 'show_review_filter', $args );

// Show category filter
$show_category_filter = ovabrw_get_meta_data( 'show_category_filter', $args );

// Label advanced search
$advanced_search_label = ovabrw_get_meta_data( 'advanced_search_label', $args );

// Icon advanced search
$advanced_search_icon = ovabrw_get_meta_data( 'advanced_search_icon', $args );

// Label filter price
$filter_price_label = ovabrw_get_meta_data( 'filter_price_label', $args );

// Label review
$review_label = ovabrw_get_meta_data( 'review_label', $args );

// Label filter category
$filter_category_label = ovabrw_get_meta_data( 'filter_category_label', $args );

// Exclude category
$excl_category = ovabrw_get_meta_data( 'excl_category', $args );

// Lable filter duration
$filter_duration_label = ovabrw_get_meta_data( 'filter_duration_label', $args );

// Show duration filter
$show_duration_filter = ovabrw_get_meta_data( 'show_duration_filter', $args );

// Duration filters
$duration_fields = ovabrw_get_meta_data( 'duration_fields', $args );

// Show category
$show_category = '';
if ( 'yes' === $show_advanced_search && 'yes' === $show_category_filter ) {
	$show_category = 'yes';
}

// Filter Settings
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
$min_price           = floor( $prices->min_price ? $prices->min_price : 0 );
$max_price           = round( $prices->max_price ? $prices->max_price : 0 );
$currency_symbol     = get_woocommerce_currency_symbol();

?>

<div class="ovabrw-search-ajax">
	<div class="wrap-search-ajax" 
	    data-adults="<?php echo esc_attr( ovabrw_get_meta_data( 'default_adult_number', $args ) ) ;?>"
	    data-childrens="<?php echo esc_attr( ovabrw_get_meta_data( 'default_children_number', $args ) ) ;?>"
	    data-sort_by_default="<?php echo esc_attr( ovabrw_get_meta_data( 'sort_by_default', $args ) ) ;?>"
	    data-start-price="<?php echo esc_attr( $min_price ) ;?>"
	    data-end-price="<?php echo esc_attr( $max_price ) ;?>"
	    data-grid_column="<?php echo esc_attr( $grid_column ) ;?>"
	    data-thumbnail-type="<?php echo esc_attr( $thumbnail_type ); ?>">
		
		<!-- Search -->
		<?php ovabrw_get_template( 'single/ovabrw_search.php', $args ); ?>

		<!-- Advanced Search -->
		<?php if ( 'yes' === $show_advanced_search ): ?>
	        <div class="ovabrw-search-advanced">
	        	<div class="search-advanced-input">
	        		<?php if ( $advanced_search_icon ): ?>
		        		<div class="advanced-search-icon">
		        			<?php \Elementor\Icons_Manager::render_icon( $advanced_search_icon, [ 'aria-hidden' => 'true' ] ); ?>
		        		</div>
	        		<?php endif; ?>
		        	<span class="search-advanced-text">
		        		<?php echo esc_html( $advanced_search_label ); ?>
		        	</span>
		        	<i aria-hidden="true" class="icomoon icomoon-chevron-down"></i>
	        	</div>
	        	<div class="search-advanced-field-wrapper">
	        		<?php if ( 'yes' === $show_price_filter ): ?>
		        	    <div class="search-advanced-field price-field">
		        	    	<span class="ovabrw-label">
		        	    		<?php echo esc_html( $filter_price_label ); ?>
		        	    	</span>
		        	    	<div class="brw-tour-price-input" data-currency_symbol="<?php echo esc_attr( $currency_symbol ); ?>">
			        	    	<input type="text" class="brw-tour-price-from" value="<?php echo esc_attr( $min_price ); ?>" data-value="<?php echo esc_attr( $min_price ); ?>">
								<input type="text" class="brw-tour-price-to" value="<?php echo esc_attr( $max_price ); ?>" data-value="<?php echo esc_attr( $max_price ); ?>">
							</div>
		        	     	<div class="slider-wrapper">
							    <div id="brw-tour-price-slider"></div>
							</div> 
		        	    </div>
	        		<?php endif;

	        		// Show review filter
	        		if ( 'yes' === $show_review_filter ): ?>
		        	    <div class="search-advanced-field rating-field">
		        	    	<span class="ovabrw-label">
		        	    		<?php echo esc_html( $review_label ); ?>
		        	    	</span>
		        	     	<?php for ( $i = 5; $i >= 1 ; $i-- ): ?>
		        	     		<div class="total-rating-stars">
		        	     			<div class="input-rating">
		        	     				<input id="rating-filter-<?php echo esc_attr( $i ); ?>" type="checkbox" class="rating-filter" name="rating_value[<?php echo esc_attr($i); ?>]" value="<?php echo esc_attr( $i ); ?>">
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
	        	    <?php endif;

	        	    // Show category filter
	        	    if ( 'yes' === $show_category_filter ): ?>
		        	    <div class="search-advanced-field tour-categories-field">
		        	    	<span class="ovabrw-label">
		        	    		<?php echo esc_html( $filter_category_label ); ?>
		        	    	</span>
		        	     	<?php foreach ( $product_categories as $pro_cat ):
		        	     		if ( $pro_cat->category_parent == 0 ):
		        	     			$cat_id = $pro_cat->term_id;

							        $sub_cats = get_categories([
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
			        	     				id="tour-category-filter-<?php echo esc_attr( $pro_cat->slug ) ;?>"
			        	     				type="checkbox"
			        	     				class="tour-category-filter"
			        	     				name="category_value"
			        	     				value="<?php echo esc_attr( $pro_cat->slug ); ?>"
			        	     				<?php echo in_array( $pro_cat->slug, $defautl_category ) ? 'checked' : ''; ?>
			        	     			/>
				        	     		<label for="tour-category-filter-<?php echo esc_attr( $pro_cat->slug ); ?>">
											<span class="tour-category-name">
												<?php echo esc_html( $pro_cat->name ); ?>
											</span>
										</label>
										<?php if ( ovabrw_array_exists( $sub_cats ) ):
											foreach ( $sub_cats as $sub_category ): ?>
									            <div class="tour-category-field-child">
						        	     			<input
						        	     				type="checkbox"
						        	     				id="tour-category-filter-<?php echo esc_attr( $sub_category->slug ); ?>"
						        	     				class="tour-category-filter"
						        	     				name="category_value"
						        	     				value="<?php echo esc_attr( $sub_category->slug ); ?>"
						        	     				<?php echo in_array( $sub_category->slug, $defautl_category ) ? 'checked' : ''; ?>
						        	     			/>
							        	     		<label for="tour-category-filter-<?php echo esc_attr( $sub_category->slug ) ;?>">
														<span class="tour-category-name">
															<?php echo esc_html( $sub_category->name ) ; ?>
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
											    	
											    	if ( ovabrw_array_exists( $sub_cats_2 ) ):
											    		foreach ( $sub_cats_2 as $sub_category_2 ): ?>
												            <div class="tour-category-field-child">
									        	     			<input
									        	     				type="checkbox"
									        	     				id="tour-category-filter-<?php echo esc_attr( $sub_category_2->slug ); ?>"
									        	     				class="tour-category-filter"
									        	     				name="category_value"
									        	     				value="<?php echo esc_attr( $sub_category_2->slug ); ?>"
									        	     				<?php echo in_array( $sub_category_2->slug, $defautl_category ) ? 'checked' : ''; ?>
									        	     			/>
										        	     		<label for="tour-category-filter-<?php echo esc_attr( $sub_category_2->slug ) ;?>">
																	<span class="tour-category-name">
																		<?php echo esc_html( $sub_category_2->name ); ?>
																	</span>
																</label>
															</div>
													<?php endforeach;
													endif; ?>
												</div>
								        <?php endforeach;
								    	endif; ?> 
				        	     	</div>
				        	    <?php endif;
				        	endforeach;?>
		        	    </div>
	        	    <?php endif;

	        	    // Duration Filter
	        	    if ( 'yes' === $show_duration_filter ): ?>
		        	    <div class="search-advanced-field tour-duration-field">
		        	    	<span class="ovabrw-label">
		        	    		<?php echo esc_html($filter_duration_label) ; ?>
		        	    	</span>
		        	    	<?php if ( is_array($duration_fields) ):
		        	    		foreach ( $duration_fields as $k => $duration_field ):
		                			if ( $duration_field['duration_type'] === "day" ) {
		                				$value_from = $duration_field['duration_day_value_from'];
		                				$value_to   = $duration_field['duration_day_value_to'];

		                		    } elseif( $duration_field['duration_type'] === "hour" ) {
		                		    	$value_from = $duration_field['duration_hour_value_from'];
		                				$value_to   = $duration_field['duration_hour_value_to'];
		                            }
	                			?>
			                		<div class="duration-field">
				                		<input id="duration-filter-<?php echo esc_attr( $k ); ?>" type="radio" class="duration-filter" name="duration_value_from" value="<?php echo esc_attr( $value_from ); ?>">
				                		<input type="hidden" class="duration-filter-to" name="duration_value_to" value="<?php echo esc_attr( $value_to ); ?>">
				                		<input type="hidden" class="duration-filter-type" name="duration_value_type" value="<?php echo esc_attr( $duration_field['duration_type'] ); ?>">
				        	     		<label for="duration-filter-<?php echo esc_attr( $k ); ?>">
											<span class="duration-name">
												<?php echo esc_html( $duration_field['duration_name'] ); ?>
											</span>
										</label>
			                		</div>
		                	<?php endforeach;
		                	endif;?>
		        	    </div>
		        	<?php endif; ?>
	        	</div>
	        </div>
	    <?php endif;

	    // Filter
	    if ( 'yes' === $show_filter ): ?>
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
	        			<input type="text" class="input_select_input" name="sr_sort_by_label" value="<?php echo esc_html__('Sort by','ova-brw'); ?>" autocomplete="off" readonly="readonly">

						<input type="hidden" class="input_select_input_value" name="sr_sort_by" value="date">

						<ul class="input_select_list" style="display: none;">
						    <li class="term_item <?php if ( $sort_by_default == 'date' ) { echo 'term_item_selected' ; } ?>" 
						    	data-id="date"
						    	data-value="<?php esc_attr_e('Ordenar por más recientes','ova-brw'); ?>"
						    >
							    <?php echo esc_html__('Más recientes','ova-brw'); ?>
							</li>
							<li class="term_item <?php if ( $sort_by_default == 'rating_desc' ) { echo 'term_item_selected' ; } ?>" 
								data-id="rating_desc" 
								data-value="<?php esc_attr_e('Ordenar por valoración','ova-brw'); ?>"
							>
								<?php echo esc_html__('Valoración','ova-brw'); ?>
							</li>
							<li class="term_item <?php if ( $sort_by_default == 'price_asc' ) { echo 'term_item_selected' ; } ?>" 
								data-id="price_asc" 
								data-value="<?php esc_attr_e('Ordenar por precio: de menor a mayor','ova-brw'); ?>"
							>
								<?php echo esc_html__('Precio: de menor a mayor','ova-brw'); ?>
							</li>
							<li class="term_item <?php if ( $sort_by_default == 'price_desc' ) { echo 'term_item_selected' ; } ?>" 
								data-id="price_desc" 
								data-value="<?php esc_attr_e('Ordenar por precio: de mayor a menor','ova-brw'); ?>"
							>
								<?php echo esc_html__('Precio: de mayor a menor','ova-brw'); ?>
							</li>
						</ul>
					</div>

					<div class="asc_desc_sort">
	        			<i aria-hidden="true" class="asc_sort icomoon icomoon-chevron-up"></i>
	        		    <i aria-hidden="true" class="desc_sort icomoon icomoon-chevron-down"></i>
	        		</div>

	        		<div class="filter-result-layout">
		        		<i aria-hidden="true" class="filter-layout <?php if ( 'list' === $search_results_layout ) { echo esc_attr( 'filter-layout-active' ); } ?> icomoon icomoon-list" data-layout="list"></i>
						<i aria-hidden="true" class="filter-layout <?php if ( 'grid' === $search_results_layout ) { echo esc_attr( 'filter-layout-active' ); } ?> icomoon icomoon-gird" data-layout="grid"></i>
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
		</div>
		<!-- End load more -->

		<!-- Search result -->
		<?php if ( 'yes' === $show_filter ):
			if ( 'date' === $sort_by_default ): ?>
				<div 
					class="brw-search-ajax-result" 
					data-order="DESC" 
					data-orderby="date" 
					data-defautl-category="<?php echo esc_attr( json_encode( $defautl_category ) ); ?>" 
					data-show-category="<?php echo esc_attr( $show_category ); ?>" 
					data-orderby_meta_key="" 
					data-posts-per-page="<?php echo esc_attr( $posts_per_page ); ?>">
				</div>
			<?php elseif ( 'rating_desc' === $sort_by_default ): ?>
	            <div 
					class="brw-search-ajax-result" 
					data-order="DESC" 
					data-defautl-category="<?php echo esc_attr( json_encode( $defautl_category ) ); ?>" 
					data-show-category="<?php echo esc_attr( $show_category ); ?>" 
					data-orderby="meta_value_num"
					data-orderby_meta_key="_wc_average_rating" 
					data-posts-per-page="<?php echo esc_attr( $posts_per_page ); ?>">
				</div>
			<?php elseif ( 'price_asc' === $sort_by_default ): ?>
	            <div 
					class="brw-search-ajax-result" 
					data-order="ASC" 
					data-orderby="meta_value_num" 
					data-defautl-category="<?php echo esc_attr( json_encode( $defautl_category ) ); ?>" 
					data-show-category="<?php echo esc_attr( $show_category ); ?>" 
					data-orderby_meta_key="_price" 
					data-posts-per-page="<?php echo esc_attr( $posts_per_page ); ?>">
				</div>
			<?php elseif ( 'price_desc' === $sort_by_default ): ?>
	            <div 
					class="brw-search-ajax-result" 
					data-order="DESC" 
					data-defautl-category="<?php echo esc_attr( json_encode( $defautl_category ) ); ?>" 
					data-show-category="<?php echo esc_attr( $show_category ); ?>" 
					data-orderby="meta_value_num"
					data-orderby_meta_key="_price" 
					data-posts-per-page="<?php echo esc_attr( $posts_per_page ); ?>">
				</div>
			<?php endif;
		else: ?>
			<div 
				class="brw-search-ajax-result" 
				data-order="<?php echo esc_attr( $order ); ?>" 
				data-defautl-category="<?php echo esc_attr( json_encode( $defautl_category ) ); ?>" 
				data-show-category="<?php echo esc_attr( $show_category ); ?>" 
				data-orderby="<?php echo esc_attr( $orderby ); ?>"
				data-orderby_meta_key="" 
				data-posts-per-page="<?php echo esc_attr( $posts_per_page ); ?>">
			</div>
		<?php endif; ?>
    </div>
</div>