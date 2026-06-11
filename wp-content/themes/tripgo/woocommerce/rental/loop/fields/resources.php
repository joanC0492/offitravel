<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get product id
$product_id = tripgo_get_meta_data( 'id', $args, get_the_id() );

// Get product
$product = wc_get_product( $product_id );
if ( !$product || !$product->is_type('ovabrw_car_rental') ) return;

// Current form
$current_form = tripgo_get_meta_data( 'form', $args );

// Show children
$show_children = function_exists( 'ovabrw_show_children' ) ? ovabrw_show_children( $product_id ) : false;

// Show baby
$show_baby = function_exists( 'ovabrw_show_babies' ) ? ovabrw_show_babies( $product_id ) : false;

// Get resource ids
$resc_ids = tripgo_get_post_meta( $product_id, 'rs_id' );
if ( !tripgo_array_exists( $resc_ids ) ) return;

// Get resource names
$resc_names = tripgo_get_post_meta( $product_id, 'rs_name' );

// Get resource descriptions
$resc_desc = tripgo_get_post_meta( $product_id, 'rs_description' );

// Get resource adult prices
$adult_prices = tripgo_get_post_meta( $product_id, 'rs_adult_price' );

// Get resource child prices
$child_prices = tripgo_get_post_meta( $product_id, 'rs_children_price' );

// Get resource baby prices
$baby_prices = tripgo_get_post_meta( $product_id, 'rs_baby_price' );

// Get resource quantity
$resc_qtys = tripgo_get_post_meta( $product_id, 'rs_quantity' );

// Get resource duration type
$duration_types = tripgo_get_post_meta( $product_id, 'rs_duration_type' );

?>

<div class="ovabrw-resources rental_item">
	<h3 class="ovabrw-label">
        <?php esc_html_e( 'Extra Services', 'tripgo' ); ?>
    </h3>
	<?php foreach ( $resc_ids as $i => $opt_id ):
		// Check id
		if ( !$opt_id ) continue;

		// Get name
		$name = tripgo_get_meta_data( $i, $resc_names );

		// Get description
		$desc = tripgo_get_meta_data( $i, $resc_desc );

		// Get adult price
		$adult_price = (float)tripgo_get_meta_data( $i, $adult_prices );

		// Get child price
		$child_price = (float)tripgo_get_meta_data( $i, $child_prices );

		// Get baby price
		$baby_price = (float)tripgo_get_meta_data( $i, $baby_prices );

		// Get max quantity
		$max_qty = (int)tripgo_get_meta_data( $i, $resc_qtys );

		// Get duration
		$duration = tripgo_get_meta_data( $i, $duration_types );
	?>
		<div class="item">
			<div class="ovabrw-resource-title">
				<label>
					<?php echo esc_html( $name );

					// Description
					if ( $desc ): ?>
						<span class="ovabrw-description" aria-label="<?php echo esc_attr( $desc ); ?>">
		                    </span>
					<?php endif;

					// View input
					tripgo_text_input([
						'type' 	=> 'checkbox',
						'class' => 'ovabrw_resource_checkboxs',
						'name' 	=> 'ovabrw_rs_checkboxs['.$opt_id.']',
						'value' => $name,
						'attrs' => [
							'data-rs-key' => $opt_id
						]
					]); ?>
					<span class="checkmark"></span>
				</label>
				<?php if ( $max_qty ): ?>
					<div class="ovabrw-resource-guest">
						<i class="ovaicon-user-2"></i>
					</div>
				<?php endif; ?>
			</div>
			<?php if ( apply_filters( 'tripgo_show_resource_prices', false ) ): ?>
				<div class="ovabrw-resource-price">
					<div class="ovabrw-adult-price">
						<h3 class="ovabrw-label">
							<?php echo esc_html__( 'Adult:', 'tripgo' ); ?>
						</h3>
						<div class="ovabrw-guests-price">
							<span class="ovabrw-adult-amount">
								<?php echo ovabrw_wc_price( $adult_price ); ?>
							</span>
							<?php if ( apply_filters( 'tripgo_resources_show_duration', true ) ): ?>
								<span class="ovabrw-rs-duration">
									<?php if ( 'person' === $duration ) {
										echo esc_html__( '/per person', 'tripgo' );
									} else {
										echo esc_html__( '/order', 'tripgo' );
									} ?>
								</span>
							<?php endif; ?>
						</div>
					</div>
					<?php if ( $show_children ): ?>
						<div class="ovabrw-children-price">
							<h3 class="ovabrw-label">
								<?php echo esc_html__( 'Child:', 'tripgo' ); ?>
							</h3>
							<div class="ovabrw-guests-price">
								<span class="ovabrw-children-amount">
									<?php echo ovabrw_wc_price( $child_price ); ?>
								</span>
								<?php if ( apply_filters( 'tripgo_resources_show_duration', true ) ): ?>
									<span class="ovabrw-rs-duration">
										<?php if ( 'person' === $duration ) {
											echo esc_html__( '/per person', 'tripgo' );
										} else {
											echo esc_html__( '/order', 'tripgo' );
										} ?>
									</span>
								<?php endif; ?>
							</div>
						</div>
					<?php endif;

					// Show baby
					if ( $show_baby ): ?>
						<div class="ovabrw-baby-price">
							<h3 class="ovabrw-label">
								<?php echo esc_html__( 'Baby:', 'tripgo' ); ?>
							</h3>
							<div class="ovabrw-guests-price">
								<span class="ovabrw-baby-amount">
									<?php echo ovabrw_wc_price( $baby_price ); ?>
								</span>
								<?php if ( apply_filters( 'tripgo_resources_show_duration', true ) ): ?>
									<span class="ovabrw-rs-duration">
										<?php if ( 'person' === $duration ) {
											echo esc_html__( '/per person', 'tripgo' );
										} else {
											echo esc_html__( '/order', 'tripgo' );
										} ?>
									</span>
								<?php endif; ?>
							</div>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; // END resource prices

			// Max quantity
			if ( $max_qty ): ?>
				<div class="ovabrw-resource-guestspicker" data-max-quantity="<?php echo esc_attr( $max_qty ); ?>">
					<div class="guests-item">
						<div class="guests-info">
							<h3 class="ovabrw-label">
								<?php echo esc_html__( 'Adult', 'tripgo' ); ?>
							</h3>
							<div class="guests-price">
								<span class="adult-price">
									<?php echo ovabrw_wc_price( $adult_price ); ?>
								</span>
								<span class="duration">
									<?php if ( 'person' === $duration ) {
										echo esc_html__( '/per person', 'tripgo' );
									} else {
										echo esc_html__( '/order', 'tripgo' );
									} ?>
								</span>
							</div>
						</div>
						<div class="guests-action">
							<div class="guests-icon guests-minus">
								<i aria-hidden="true" class="icomoon icomoon-minus"></i>
							</div>
							<?php tripgo_text_input([
								'type' 	=> 'text',
								'class' => 'resource-guests-input',
								'name' 	=> 'ovabrw_resource_guests['.$opt_id.'][adult]',
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
								<?php echo esc_html__( 'Child', 'tripgo' ); ?>
							</h3>
							<div class="guests-price">
								<span class="child-price">
									<?php echo ovabrw_wc_price( $child_price ); ?>
								</span>
								<span class="duration">
									<?php if ( 'person' === $duration ) {
										echo esc_html__( '/per person', 'tripgo' );
									} else {
										echo esc_html__( '/order', 'tripgo' );
									} ?>
								</span>
							</div>
						</div>
						<div class="guests-action">
							<div class="guests-icon guests-minus">
								<i aria-hidden="true" class="icomoon icomoon-minus"></i>
							</div>
							<?php tripgo_text_input([
								'type' 	=> 'text',
								'class' => 'resource-guests-input',
								'name' 	=> 'ovabrw_resource_guests['.$opt_id.'][child]',
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
								<?php echo esc_html__( 'Baby', 'tripgo' ); ?>
							</h3>
							<div class="guests-price">
								<span class="baby-price">
									<?php echo ovabrw_wc_price( $baby_price ); ?>
								</span>
								<span class="duration">
									<?php if ( 'person' === $duration ) {
										echo esc_html__( '/per person', 'tripgo' );
									} else {
										echo esc_html__( '/order', 'tripgo' );
									} ?>
								</span>
							</div>
						</div>
						<div class="guests-action">
							<div class="guests-icon guests-minus">
								<i aria-hidden="true" class="icomoon icomoon-minus"></i>
							</div>
							<?php tripgo_text_input([
								'type' 	=> 'text',
								'class' => 'resource-guests-input',
								'name' 	=> 'ovabrw_resource_guests['.$opt_id.'][baby]',
								'value' => 0
							]); ?>
							<div class="guests-icon guests-plus">
								<i aria-hidden="true" class="icomoon icomoon-plus"></i>
							</div>
						</div>
					</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>
</div>