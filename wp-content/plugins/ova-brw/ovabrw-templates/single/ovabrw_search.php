<?php if ( !defined( 'ABSPATH' ) ) exit();

extract( $args );

// Form action
$action = home_url();
if ( 'new_page' === ovabrw_get_meta_data( 'search_result', $args ) ) {
	$action = isset( $args['search_result_url']['url'] ) ? $args['search_result_url']['url'] : '';
}

// form method
$method = ovabrw_get_meta_data( 'method', $args, 'GET' );

// Destination
$id_selected = (int)sanitize_text_field( ovabrw_get_meta_data( 'ovabrw_destination', $_GET ) );
if ( !$id_selected && isset( $destination_default ) && $destination_default ) {
	$id_selected = $destination_default;
}

// Get pick-up date (month index(es): one value, comma list, or array from ovabrw_pickup_date[]).
$pickup_date_raw = ovabrw_get_meta_data( 'ovabrw_pickup_date', $_GET );
if ( ! $pickup_date_raw && isset( $_GET['pickup_date'] ) ) {
	$pickup_date_raw = ovabrw_get_meta_data( 'pickup_date', $_GET );
}
$pickup_months_selected = [];
if ( is_array( $pickup_date_raw ) ) {
	foreach ( $pickup_date_raw as $v ) {
		$v = trim( (string) $v );
		if ( $v !== '' && preg_match( '/^(0?[1-9]|1[0-2])$/', $v ) ) {
			$pickup_months_selected[] = str_pad( (string) (int) $v, 2, '0', STR_PAD_LEFT );
		}
	}
} elseif ( is_string( $pickup_date_raw ) && $pickup_date_raw !== '' ) {
	if ( strpos( $pickup_date_raw, ',' ) !== false ) {
		foreach ( explode( ',', $pickup_date_raw ) as $v ) {
			$v = trim( $v );
			if ( $v !== '' && preg_match( '/^(0?[1-9]|1[0-2])$/', $v ) ) {
				$pickup_months_selected[] = str_pad( (string) (int) $v, 2, '0', STR_PAD_LEFT );
			}
		}
	} elseif ( preg_match( '/^(0?[1-9]|1[0-2])$/', trim( $pickup_date_raw ) ) ) {
		$pickup_months_selected[] = str_pad( (string) (int) trim( $pickup_date_raw ), 2, '0', STR_PAD_LEFT );
	}
}
$pickup_months_selected = array_values( array_unique( $pickup_months_selected ) );
$current_year = gmdate( 'Y', current_time( 'timestamp' ) );
$month_labels = [
	// '00' => 'Cualquier fecha',
	'01' => 'Enero',
	'02' => 'Febrero',
	'03' => 'Marzo',
	'04' => 'Abril',
	'05' => 'Mayo',
	'06' => 'Junio',
	'07' => 'Julio',
	'08' => 'Agosto',
	'09' => 'Septiembre',
	'10' => 'Octubre',
	'11' => 'Noviembre',
	'12' => 'Diciembre'
];

// Adults
$max_adult 	= ovabrw_get_meta_data( 'max_adult', $args );
$min_adult 	= ovabrw_get_meta_data( 'min_adult', $args );
$show_adult = ovabrw_get_meta_data( 'show_adult', $args );

// Children
$max_children 	= ovabrw_get_meta_data( 'max_children', $args );
$min_children 	= ovabrw_get_meta_data( 'min_children', $args );
$show_children 	= ovabrw_get_meta_data( 'show_children', $args );

// Baby
$max_baby 	= ovabrw_get_meta_data( 'max_baby', $args );
$min_baby 	= ovabrw_get_meta_data( 'min_baby', $args );
$show_baby 	= ovabrw_get_meta_data( 'show_baby', $args );

// Label guests
$guests_label = ovabrw_get_meta_data( 'guests_label', $args );

// Show guests
$show_guests = ovabrw_get_meta_data( 'show_guests', $args );

// Label adults
$adults_label = ovabrw_get_meta_data( 'adults_label', $args );

// Label children
$children_label = ovabrw_get_meta_data( 'childrens_label', $args );

// Label babies
$babies_label = ovabrw_get_meta_data( 'babies_label', $args );

// Icon guests
$icon_guests = ovabrw_get_meta_data( 'icon_guests', $args );

// Number of guests
$guests = 0;

// Default number of adults
$default_adult_number = absint( ovabrw_get_meta_data( 'ovabrw_adults', $_GET, ovabrw_get_meta_data( 'default_adult_number', $args ) ) );
if ( 'yes' === $show_adult ) {
	$guests += $default_adult_number;
}

// Default number of children
$default_children_number = absint( ovabrw_get_meta_data( 'ovabrw_childrens', $_GET, ovabrw_get_meta_data( 'default_children_number', $args ) ) );
if ( 'yes' === $show_children ) {
	$guests += $default_children_number;
}

// Default number of babies
$default_babies_number = absint( ovabrw_get_meta_data( 'ovabrw_babies', $_GET, ovabrw_get_meta_data( 'default_babies_number', $args ) ) );
if ( 'yes' === $show_baby ) {
	$guests += $default_babies_number;
}

// Placeholde guests
$guests_placeholder = ovabrw_get_meta_data( 'guests_placeholder', $args ); 

// Button icon
$icon_button = ovabrw_get_meta_data( 'icon_button', $args );

// Label button
$button_label = ovabrw_get_meta_data( 'button_label', $args );

// Icon check-in
$icon_check_in = ovabrw_get_meta_data( 'icon_check_in', $args );

// Label check-in date
$check_in_label = ovabrw_get_meta_data( 'check_in_label', $args );

// Show check-in date
$show_check_in = ovabrw_get_meta_data( 'show_check_in', $args );

// Icon custom taxonomy
$icon_custom_taxonomy = ovabrw_get_meta_data( 'icon_custom_taxonomy', $args );

// Label custom taxonomy
$custom_taxonomy_label = ovabrw_get_meta_data( 'custom_taxonomy_label', $args );

// Placeholder custom taxonomy
$custom_taxonomy_placeholder = ovabrw_get_meta_data( 'custom_taxonomy_placeholder', $args );

// Slug selected
$slug_value_selected = '';

// Slug custom taxonomy
$slug_custom_taxonomy = ovabrw_get_meta_data( 'slug_custom_taxonomy', $args );
if ( $slug_custom_taxonomy ) {
	$slug_value_selected = sanitize_text_field( ovabrw_get_meta_data( $slug_custom_taxonomy.'_name', $_GET, 'all' ) );
}

// Slug selected
$ctx_slug_value_selected = ovabrw_get_meta_data( 'ctx_slug_value_selected', $args );
if ( $ctx_slug_value_selected ) {
	$slug_value_selected = $ctx_slug_value_selected;
}

if ( ovabrw_get_meta_data( $slug_custom_taxonomy.'_name', $_GET ) ) {
	$slug_value_selected = sanitize_text_field( $_GET[$slug_custom_taxonomy.'_name'] );
}

// Get terms
$terms = get_taxonomy( $slug_custom_taxonomy );

// Categories
$search_categories = ovabrw_get_meta_data( 'search_categories', $args, [] );

// Icon destination
$icon_destination = ovabrw_get_meta_data( 'icon_destination', $args );

// Label destination
$destination_label = ovabrw_get_meta_data( 'destination_label', $args );

// Placeholcer destination
$destination_placeholder = ovabrw_get_meta_data( 'destination_placeholder', $args );

// Show destination
$show_destination = ovabrw_get_meta_data( 'show_destination', $args );

// Taxonomies
$mutiple_custom_taxonomy = ovabrw_get_meta_data( 'mutiple_custom_taxonomy', $args );

// Get list taxonomies
$list_taxonomies = ovabrw_get_meta_data( 'list_custom_taxonomy', $args, [] );
$data_taxonomies = [];

// Template
if ( !isset( $template ) ) $template = '';

?>

<div class="ovabrw-search ovabrw-search-<?php echo esc_attr( $template ); ?>">
	<form action="<?php echo esc_url( $action ); ?>" method="<?php echo esc_attr( $method ); ?>" class="ovabrw-search-form">
		<div class="ovabrw-s-field">
			<!-- destinations dropdown -->
			<?php if ( $show_destination === 'yes' ): ?>
				<div class="search-field">
					<div class="ovabrw-label">
						<?php if ( $icon_destination ) {
					    	\Elementor\Icons_Manager::render_icon( $icon_destination, [ 'aria-hidden' => 'true' ] );
					    } ?>
						<span class="label">
							<?php echo esc_html( $destination_label ); ?>
						</span>
					</div>
					<div class="ovabrw-input search_in_destination">
						<?php echo ovabrw_destination_dropdown( $destination_placeholder, $id_selected ); ?>
						<?php //echo ovabrw_tour_products_dropdown_simple( $destination_placeholder, $id_selected ); ?>
					</div>
				</div>
			<?php endif;

			// Custom taxonomies
			if ( $slug_custom_taxonomy && !empty( $terms ) ): ?>
	            <!-- custom taxonomy -->
				<div class="search-field">
					<div class="ovabrw-label">
						<?php if ( $icon_custom_taxonomy ) {
					    	\Elementor\Icons_Manager::render_icon( $icon_custom_taxonomy, [ 'aria-hidden' => 'true' ] );
					    } ?>
						<span class="label"><?php echo esc_html( $custom_taxonomy_label ); ?></span>
					</div>
					<div class="ovabrw-input search_in_taxonomy">
						<?php echo ovabrw_search_taxonomy_dropdown( $slug_custom_taxonomy, $custom_taxonomy_placeholder, $slug_value_selected, 'required' ); ?>
					</div>
				</div>
			<?php endif; ?>

			<!-- Taxonomies -->
			<?php if ( 'yes' === $mutiple_custom_taxonomy && ovabrw_array_exists( $list_taxonomies ) ):
				foreach ( $list_taxonomies as $data_taxonomy ):
					// Slug
					$item_slug_taxonomy = ovabrw_get_meta_data( 'item_slug_taxonomy', $data_taxonomy );

					// Icon
					$item_icon_taxonomy = ovabrw_get_meta_data( 'item_icon_taxonomy', $data_taxonomy );

					// Label
					$item_taxonomy_label = ovabrw_get_meta_data( 'item_taxonomy_label', $data_taxonomy );

					// Placeholder
					$item_taxonomy_placeholder = ovabrw_get_meta_data( 'item_taxonomy_placeholder', $data_taxonomy );

					if ( !$item_slug_taxonomy ) continue;

					// Default
					$item_slug_default = ovabrw_get_meta_data( $item_slug_taxonomy.'_name', $_GET, 'all' );

					// Value
					$item_taxonomy_value = sanitize_text_field( ovabrw_get_meta_data( 'item_taxonomy_value_'.$item_slug_taxonomy, $data_taxonomy ) );

					if ( $item_taxonomy_value ) {
						$item_slug_default = $item_taxonomy_value;
					}

					if ( ovabrw_get_meta_data( $item_slug_taxonomy.'_name', $_GET ) ) {
						$item_slug_default = sanitize_text_field( $_GET[$item_slug_taxonomy.'_name'] );
					}

					// Get term
					$item_tern = get_taxonomy( $item_slug_taxonomy );

					if ( $item_slug_taxonomy && !empty( $item_tern ) ):
						array_push( $data_taxonomies, trim($item_slug_taxonomy) );
					?>
						<div class="search-field">
							<div class="ovabrw-label">
								<?php if ( $item_icon_taxonomy ) {
							    	\Elementor\Icons_Manager::render_icon( $item_icon_taxonomy, [ 'aria-hidden' => 'true' ] );
							    } ?>
								<span class="label"><?php echo esc_html( $item_taxonomy_label ); ?></span>
							</div>
							<div class="ovabrw-input search_in_taxonomy">
								<?php echo ovabrw_search_taxonomy_dropdown( $item_slug_taxonomy, $item_taxonomy_placeholder, $item_slug_default, 'required' ); ?>
							</div>
						</div>
					<?php endif;
				endforeach;
			endif;

			// Check-in date
			if ( 'yes' === $show_check_in ): ?>
				<div class="search-field">
					<div class="ovabrw-input">
						<div class="ovabrw-label">
							<?php if ( $icon_check_in ) {
						    	\Elementor\Icons_Manager::render_icon( $icon_check_in, [ 'aria-hidden' => 'true' ] );
						    } ?>
							<span class="label">
								<?php echo esc_html( $check_in_label ); ?>
							</span>
						</div>
						<select
							id="<?php echo esc_attr( ovabrw_unique_id( 'checkin-date' ) ); ?>"
							class="ovabrw-monthpicker-start ovabrw-month-select2"
							name="ovabrw_pickup_date[]"
							multiple="multiple"
							data-date="<?php echo esc_attr( implode( ',', $pickup_months_selected ) ); ?>"
							data-placeholder="<?php echo esc_attr__( 'Cualquier fecha', 'ova-brw' ); ?>"
							aria-label="<?php echo esc_attr__( 'Selecciona el mes de viaje', 'ova-brw' ); ?>"
						>
							<?php foreach ( $month_labels as $month_number => $month_label ) :
								$sel = in_array( $month_number, $pickup_months_selected, true );
								?>
								<option value="<?php echo esc_attr( $month_number ); ?>" <?php selected( $sel ); ?>>
									<?php echo esc_html( $month_label ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
			<?php endif;

			// Guests
			if ( 'yes' === $show_guests ): ?>
				<div class="search-field guestspicker-control">
					<div class="ovabrw-input ovabrw-guestspicker-content-wrapper">
						<div class="ovabrw-label">
							<?php if ( $icon_guests ) {
						    	\Elementor\Icons_Manager::render_icon( $icon_guests, [ 'aria-hidden' => 'true' ] );
						    } ?>
							<span class="label"><?php echo esc_html( $guests_label ); ?></span>
						</div>
						<div class="ovabrw-guestspicker">
							<div class="guestspicker">
								<span class="gueststotal"><?php echo esc_html( $guests ); ?></span>
								<span class="guestslabel"><?php echo esc_html( $guests_placeholder ); ?></span>
							</div>
						</div>
						<div class="ovabrw-guestspicker-content">
							<?php if ( 'yes' === $show_adult ): ?>
								<div class="guests-buttons">
									<div class="description">
										<label><?php echo esc_html( $adults_label ); ?></label>
									</div>
									<div class="guests-button">
										<div class="guests-icon minus">
											<i class="fas fa-minus"></i>
										</div>
										<input
											type="text"
											id="ovabrw_adults"
											name="ovabrw_adults"
											class="ovabrw_adults"
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
										<label><?php echo esc_html( $children_label ); ?></label>
									</div>
									<div class="guests-button">
										<div class="guests-icon minus">
											<i class="fas fa-minus"></i>
										</div>
										<input
											type="text"
											id="ovabrw_childrens"
											name="ovabrw_childrens"
											class="ovabrw_childrens"
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
										<label><?php echo esc_html( $babies_label ); ?></label>
									</div>
									<div class="guests-button">
										<div class="guests-icon minus">
											<i class="fas fa-minus"></i>
										</div>
										<input
											type="text"
											id="ovabrw_babies"
											name="ovabrw_babies"
											class="ovabrw_babies"
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

			<!-- button -->
			<div class="ovabrw-search-btn">
				<?php if ( isset( $args['search_result'] ) && ( 'default' == $args['search_result'] ) ): ?>
					<input type="hidden" name="ovabrw_search_product" value="ovabrw_search_product" />
					<?php if ( ovabrw_array_exists( $search_categories ) ) : ?>
						<input type="hidden" name="cat" value="<?php echo esc_attr( implode( '|', $search_categories ) ); ?>" />
					<?php endif; ?>
					<input type="hidden" name="ovabrw_slug_custom_taxonomy" value="<?php echo esc_attr( $slug_custom_taxonomy ); ?>" />
					<input type="hidden" name="ovabrw_slug_taxonomies" value="<?php echo esc_attr( implode( '|', $data_taxonomies ) ); ?>" />
	                <input type="hidden" name="ovabrw_search" value="search_item" />
	                <input type="hidden" name="post_type" value="product" />
				<?php endif; ?>
				<button class="ovabrw-btn" type="submit">
					<?php if ( $icon_button ) {
					    	\Elementor\Icons_Manager::render_icon( $icon_button, [ 'aria-hidden' => 'true' ] );
					    }

					    echo wp_kses_post( $button_label );    
				    ?>
				</button>		
			</div>
		</div>
	</form>
</div>