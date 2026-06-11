<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get product id
$product_id = tripgo_get_meta_data( 'id', $args, get_the_id() );

// Get product
$product = wc_get_product( $product_id );
if ( !$product || !$product->is_type('ovabrw_car_rental') ) return;

// Show children
$show_children = function_exists( 'ovabrw_show_children' ) ? ovabrw_show_children( $product_id ) : false;

// Show baby
$show_baby = function_exists( 'ovabrw_show_babies' ) ? ovabrw_show_babies( $product_id ) : false;

// Get service labels
$serv_labels = tripgo_get_post_meta( $product_id, 'label_service' );
if ( !tripgo_array_exists( $serv_labels ) ) return;

// Get service required
$serv_required = tripgo_get_post_meta( $product_id, 'service_required' );

// Get service ids
$serv_ids = tripgo_get_post_meta( $product_id, 'service_id' );

// Get service names
$serv_names = tripgo_get_post_meta( $product_id, 'service_name' );

// Get adult prices
$serv_adult_prices = tripgo_get_post_meta( $product_id, 'service_adult_price' );

// Get child prices
$serv_child_prices = tripgo_get_post_meta( $product_id, 'service_children_price' );

// Get baby prices
$serv_baby_prices = tripgo_get_post_meta( $product_id, 'service_baby_price' );

// Get service quantity
$serv_qtys = tripgo_get_post_meta( $product_id, 'service_quantity' );

// Get service types
$serv_types = tripgo_get_post_meta( $product_id, 'service_duration_type' );

// Basic frontend translation map for common labels/options.
$es_map = [
    'Package'       => 'Paquete',
    'Packages'      => 'Paquetes',
    'Tools'         => 'Herramientas',
    'Tour Guide'    => 'Guia turistico',
    'Tour guide'    => 'Guia turistico',
    'Men'           => 'Hombres',
    'Women'         => 'Mujeres',
    'File'          => 'Archivo',
];

?>

<div class="ovabrw_services rental_item">
	<h3 class="ovabrw-label">
        <?php esc_html_e( 'Servicios', 'tripgo' ); ?>
    </label>
	<?php foreach ( $serv_labels as $i => $label ): 
        // Translate common label names configured in product meta.
        $label = isset( $es_map[$label] ) ? $es_map[$label] : $label;

		// Required
		$required = 'yes' === tripgo_get_meta_data( $i, $serv_required ) ? true : false;

		// Get option ids
		$opt_ids = tripgo_get_meta_data( $i, $serv_ids );

		// Get option names
		$opt_names = tripgo_get_meta_data( $i, $serv_names );

		// Get option adult prices
		$opt_adult_prices = tripgo_get_meta_data( $i, $serv_adult_prices );

		// Get option child prices
		$opt_child_prices = tripgo_get_meta_data( $i, $serv_child_prices );

		// Get option baby prices
		$opt_baby_prices = tripgo_get_meta_data( $i, $serv_baby_prices );

		// Get option quantity
		$opt_qtys = tripgo_get_meta_data( $i, $serv_qtys );

		// Get option types
		$opt_types = tripgo_get_meta_data( $i, $serv_types );

		// Option max quantites
    	$max_qtys = [];

    	// Option guest prices
    	$guest_prices = [];
	?>
		<div class="ovabrw_service_select">
			<select
				class="<?php echo $required ? esc_attr( 'ovabrw-input-required' ) : ''; ?>"
				name="ovabrw_service[]">
				<option value="">
					<?php echo sprintf( esc_html__( 'Selecciona %s', 'tripgo' ), $label ); ?>
				</option>
				<?php if ( tripgo_array_exists( $opt_ids ) ):
					foreach ( $opt_ids as $k => $opt_id ):
						// Name
						$name = tripgo_get_meta_data( $k, $opt_names );
                        $name = isset( $es_map[$name] ) ? $es_map[$name] : $name;

						// Adult price
						$adult_price = (float)tripgo_get_meta_data( $k, $opt_adult_prices );

						// Child price
						$child_price = (float)tripgo_get_meta_data( $k, $opt_child_prices );

						// Baby price
						$baby_price = (float)tripgo_get_meta_data( $k, $opt_baby_prices );

						// Quantity
						$qty = (int)tripgo_get_meta_data( $k, $opt_qtys );

						// Duration
						$duration = tripgo_get_meta_data( $k, $opt_types, 'person' );
						if ( 'person' === $duration ) {
							$duration = esc_html__( '/per person', 'tripgo' );
						} else {
							$duration = esc_html__( '/order', 'tripgo' );
						}

						// Add max quantites
						if ( $qty ) {
							$max_qtys[$opt_id] = $qty;

							// Add guest prices
							$guest_prices[$opt_id] = [
								'adult' => sprintf( '%s%s', ovabrw_wc_price( $adult_price ), $duration ),
								'child' => sprintf( '%s%s', ovabrw_wc_price( $child_price ), $duration ),
								'baby' 	=> sprintf( '%s%s', ovabrw_wc_price( $baby_price ), $duration ),
							];
						}
					?>
						<option value="<?php echo esc_attr( $opt_id ); ?>">
							<?php if ( apply_filters( 'tripgo_show_services_duration', false ) ) {
								// HTML price
								$html_price = '';
								if ( $show_children && $show_baby ) {
									$name .= sprintf( esc_html__( ' (Adult: %s%s - Child: %s%s - Baby: %s%s)', 'tripgo' ), ovabrw_wc_price( $adult_price ), $duration, ovabrw_wc_price( $child_price ), $duration, ovabrw_wc_price( $baby_price ), $duration );
								} elseif ( $show_children && !$show_baby ) {
									$name .= sprintf( esc_html__( ' (Adult: %s%s - Child: %s%s)', 'tripgo' ), ovabrw_wc_price( $adult_price ), $duration, ovabrw_wc_price( $child_price ), $duration );
								} elseif ( !$show_children && $show_baby ) {
									$name .= sprintf( esc_html__( ' (Adult: %s%s - Baby: %s%s)', 'tripgo' ), ovabrw_wc_price( $adult_price ), $duration, ovabrw_wc_price( $baby_price ), $duration );
								} else {
									$name .= sprintf( esc_html__( ' (Adult: %s%s)', 'tripgo' ), ovabrw_wc_price( $adult_price ), $duration );
								}

								echo wp_kses_post( $name );
							} else {
								echo esc_html( $name );
							} ?>
						</option>
					<?php endforeach;
				endif; ?>
			</select>
			<?php if ( tripgo_array_exists( $max_qtys ) ): ?>
				<div class="ovabrw-service-guest">
					<i class="ovaicon-user-2"></i>
				</div>
				<?php foreach ( $max_qtys as $opt_id => $max_qty ):
					// Adult price
					$adult_price = isset( $guest_prices[$opt_id]['adult'] ) ? $guest_prices[$opt_id]['adult'] : '';

					// Child price
					$child_price = isset( $guest_prices[$opt_id]['child'] ) ? $guest_prices[$opt_id]['child'] : '';

					// Baby price
					$baby_price = isset( $guest_prices[$opt_id]['baby'] ) ? $guest_prices[$opt_id]['baby'] : '';
				?>
					<div class="ovabrw-service-guestspicker" data-option="<?php echo esc_attr( $opt_id ); ?>" data-max-quantity="<?php echo esc_attr( $max_qty ); ?>">
						<div class="guests-item">
							<div class="guests-info">
								<h3 class="ovabrw-label">
									<?php echo esc_html__( 'Adult', 'tripgo' ); ?>
								</h3>
								<div class="guests-price">
									<?php echo wp_kses_post( $adult_price ); ?>
								</div>
							</div>
							<div class="guests-action">
								<div class="guests-icon guests-minus">
									<i aria-hidden="true" class="icomoon icomoon-minus"></i>
								</div>
								<?php tripgo_text_input([
									'type' 	=> 'text',
									'class' => 'service-guests-input',
									'name' 	=> 'ovabrw_service_guests['.$opt_id.'][adult]',
									'value' => 1
								]); ?>
								<div class="guests-icon guests-plus">
									<i aria-hidden="true" class="icomoon icomoon-plus"></i>
								</div>
							</div>
						</div>
						<?php if ( $show_children ): ?>
							<div class="guests-item">
								<div class="guests-info">
									<h3 class="ovabrw-label">
										<?php echo esc_html__( 'Child:', 'tripgo' ); ?>
									</h3>
									<div class="guests-price">
										<?php echo wp_kses_post( $child_price ); ?>
									</div>
								</div>
								<div class="guests-action">
									<div class="guests-icon guests-minus">
										<i aria-hidden="true" class="icomoon icomoon-minus"></i>
									</div>
									<?php tripgo_text_input([
										'type' 	=> 'text',
										'class' => 'service-guests-input',
										'name' 	=> 'ovabrw_service_guests['.$opt_id.'][child]',
										'value' => 0
									]); ?>
									<div class="guests-icon guests-plus">
										<i aria-hidden="true" class="icomoon icomoon-plus"></i>
									</div>
								</div>
							</div>
						<?php endif;

						// Show baby
						if ( $show_baby ): ?>
							<div class="guests-item">
								<div class="guests-info">
									<h3 class="ovabrw-label">
										<?php echo esc_html__( 'Baby:', 'tripgo' ); ?>
									</h3>
									<div class="guests-price">
										<?php echo wp_kses_post( $baby_price ); ?>
									</div>
								</div>
								<div class="guests-action">
									<div class="guests-icon guests-minus">
										<i aria-hidden="true" class="icomoon icomoon-minus"></i>
									</div>
									<?php tripgo_text_input([
										'type' 	=> 'text',
										'class' => 'service-guests-input',
										'name' 	=> 'ovabrw_service_guests['.$opt_id.'][baby]',
										'value' => 0
									]); ?>
									<div class="guests-icon guests-plus">
										<i aria-hidden="true" class="icomoon icomoon-plus"></i>
									</div>
								</div>
							</div>
						<?php endif; ?>
					</div>
				<?php endforeach;
			endif; ?>
		</div>
	<?php endforeach; ?>
</div>