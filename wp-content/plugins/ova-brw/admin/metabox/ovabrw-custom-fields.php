<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get post id
$post_id = get_the_ID();

global $woocommerce, $post;

if ( !function_exists( 'woocommerce_wp_text_input' ) && !is_admin() ) {
	include_once WC()->plugin_path() . '/includes/admin/wc-meta-box-functions.php';
}

?>

<div id="ovabrw-rental" class="options_group show_if_ovabrw_car_rental ovabrw_metabox_car_rental">
	<input
		type="hidden"
		name="ovabrw_label_adult_price"
		value="<?php echo sprintf( esc_html__( 'Adult price (%s)', 'ova-brw' ), get_woocommerce_currency_symbol() ); ?>"
		data-label-regular-price=""
	/>
	<?php if ( ovabrw_show_children( $this->get_id() ) ) {
		// Child price
		woocommerce_wp_text_input([
			'type' 		=> 'text',
			'id' 		=> $this->get_meta_name( 'children_price' ),
			'class' 	=> 'short',
			'label' 	=> sprintf( esc_html__( 'Child price (%s)', 'ova-brw' ),get_woocommerce_currency_symbol() ),
			'value' 	=> $this->get_meta_value( 'children_price' ),
			'data_type' => 'price'
		]);
	} ?>
	
	<?php if ( ovabrw_show_babies( $this->get_id() ) ) {
		// Baby price
		woocommerce_wp_text_input([
			'type' 		=> 'text',
			'id' 		=> $this->get_meta_name( 'baby_price' ),
			'class' 	=> 'short',
			'label' 	=> sprintf( esc_html__( 'Baby price (%s)', 'ova-brw' ), get_woocommerce_currency_symbol() ),
			'value' 	=> $this->get_meta_value( 'baby_price' ),
			'data_type' => 'price'
		]);
	} ?>
	<fieldset class="form-field ovabrw-typeof-insurance">
		<legend><?php esc_html_e( 'Insurance', 'ova-brw' ); ?></legend>
		<ul class="wc-radios">
			<li>
				<label>
					<?php ovabrw_admin_text_input([
						'type' 		=> 'radio',
						'class' 	=> 'select short',
						'name' 		=> $this->get_meta_name( 'typeof_insurance' ),
						'value' 	=> 'general',
						'checked' 	=> 'general' === $this->get_meta_value( 'typeof_insurance', 'general' ) ? true : false
					]);
					esc_html_e( 'General price', 'ova-brw' ); ?>
				</label>
			</li>
			<li>
				<label>
					<?php ovabrw_admin_text_input([
						'type' 		=> 'radio',
						'class' 	=> 'select short',
						'name' 		=> $this->get_meta_name( 'typeof_insurance' ),
						'value' 	=> 'guest',
						'checked' 	=> 'guest' === $this->get_meta_value( 'typeof_insurance', 'general' ) ? true : false
					]);
					esc_html_e( 'Guest type price', 'ova-brw' ); ?>
				</label>
				<div class="guest-insurance-fields">
					<ul class="ovabrw-radios">
						<li>
							<label>
								<span class="title">
									<?php esc_html_e( 'Adult', 'ova-brw' ); ?>
								</span>
								<?php ovabrw_admin_text_input([
									'type' 			=> 'text',
									'class' 		=> 'short wc_input_price',
									'name' 			=> $this->get_meta_name( 'adult_insurance' ),
									'value' 		=> $this->get_meta_value( 'adult_insurance' ),
									'placeholder' 	=> '10.5'
								]); ?>
								<span class="currency">
									<?php echo esc_html( get_woocommerce_currency_symbol() ); ?>
								</span>
							</label>
						</li>
						<li>
							<label>
								<span class="title">
									<?php esc_html_e( 'Child', 'ova-brw' ); ?>
								</span>
								<?php ovabrw_admin_text_input([
									'type' 			=> 'text',
									'class' 		=> 'short wc_input_price',
									'name' 			=> $this->get_meta_name( 'child_insurance' ),
									'value' 		=> $this->get_meta_value( 'child_insurance' ),
									'placeholder' 	=> '10.5'
								]); ?>
								<span class="currency">
									<?php echo esc_html( get_woocommerce_currency_symbol() ); ?>
								</span>
							</label>
						</li>
						<li>
							<label>
								<span class="title">
									<?php esc_html_e( 'Baby', 'ova-brw' ); ?>
								</span>
								<?php ovabrw_admin_text_input([
									'type' 			=> 'text',
									'class' 		=> 'short wc_input_price',
									'name' 			=> $this->get_meta_name( 'baby_insurance' ),
									'value' 		=> $this->get_meta_value( 'baby_insurance' ),
									'placeholder' 	=> '10.5'
								]); ?>
								<span class="currency">
									<?php echo esc_html( get_woocommerce_currency_symbol() ); ?>
								</span>
							</label>
						</li>
					</ul>
				</div>
			</li>
		</ul>
	</fieldset>
	<?php // Insurance amount
	woocommerce_wp_text_input([
		'type' 			=> 'text',
		'id' 			=> $this->get_meta_name( 'amount_insurance' ),
		'class' 		=> 'short',
		'label' 		=> '',
		'value' 		=> $this->get_meta_value( 'amount_insurance' ),
		'placeholder' 	=> '10.5',
		'description' 	=> get_woocommerce_currency_symbol(),
		'desc_tip' 		=> false,
		'data_type' 	=> 'price',
	]);

	// Embed video
	woocommerce_wp_text_input([
		'type' 			=> 'text',
		'id' 			=> $this->get_meta_name( 'embed_video' ),
		'class' 		=> 'short',
		'label' 		=> esc_html__( 'Embed video link', 'ova-brw' ),
		'value' 		=> $this->get_meta_value( 'embed_video' ),
		'placeholder' 	=> 'https://www.youtube.com/',
	]);

	// Destination
	$destinations = ovabrw_get_destinations();
	if ( ovabrw_array_exists( $destinations ) ) {
		$destinations = array_filter( $destinations, function( $k ) {
		    return '' !== $k;
		}, ARRAY_FILTER_USE_KEY );
	}

	woocommerce_wp_select([
		'id' 				=> $this->get_meta_name( 'destination' ),
		'label' 			=> esc_html__( 'Destination', 'ova-brw' ),
		'name' 				=> 'ovabrw_destination[]',
		'options' 			=> $destinations,
		'value' 			=> $this->get_meta_value( 'destination' ),
		'custom_attributes' => [
			'multiple' 			=> 'multiple',
			'data-placeholder'	=> esc_html__( 'All Destination', 'ova-brw' )
		]
	]);
	
	// Stock quantity 
	woocommerce_wp_text_input([
		'type' 			=> 'number',
		'id' 			=> $this->get_meta_name( 'stock_quantity' ),
		'class' 		=> 'short ovabrw-input-required',
		'wrapper_class' => 'ovabrw-required',
		'label' 		=> esc_html__( 'Quantity', 'ova-brw' ),
		'value' 		=> (int)$this->get_meta_value( 'stock_quantity' ),
		'placeholder' 	=> '10',
	]); ?>

	<!-- Duration -->
	<div class="ovabrw-advanced-settings">
		<div class="advanced-header">
			<h3 class="advanced-label">
				<?php esc_html_e( 'Duration', 'ova-brw' ); ?>
			</h3>
			<span aria-hidden="true" class="dashicons dashicons-arrow-up"></span>
			<span aria-hidden="true" class="dashicons dashicons-arrow-down"></span>
		</div>
		<div class="advanced-content">
			<p class="form-field ovabrw_duration_field">
			    <label class="ovabrw_duration_label">
			    	<?php ovabrw_wp_text_input([
			    		'type' 		=> 'checkbox',
			    		'id' 		=> $this->get_meta_name( 'duration_checkbox' ),
			    		'class' 	=> $this->get_meta_name( 'duration_checkbox' ),
			    		'name' 		=> $this->get_meta_name( 'duration_checkbox' ),
			    		'value' 	=> '1',
			    		'checked' 	=> $this->get_meta_value( 'duration_checkbox' ) ? true : false
			    	]); ?>
					<span><?php echo esc_html_e( 'Time slots', 'ova-brw' ); ?></span>
				</label>
			</p>

			<!-- Days -->
			<?php woocommerce_wp_text_input([
				'type' 				=> 'number',
				'id' 				=> $this->get_meta_name( 'number_days' ),
				'class' 			=> 'short',
				'label' 			=> esc_html__( 'Days', 'ova-brw' ),
				'value' 			=> $this->get_meta_value( 'number_days' ),
				'placeholder' 		=> 1,
				'description' 		=> esc_html__( 'Tour time (day)', 'ova-brw' ),
				'desc_tip' 			=> true,
				'custom_attributes' => [
					'min' => 0
				]
			]); ?>

			<!-- Fixed Time -->
			<div class="ovabrw-form-field ovabrw_fixed_time_field">
		  		<br/>
		  		<strong class="ovabrw_heading_section">
		  			<?php esc_html_e( 'Fixed dates', 'ova-brw' ); ?>
		  		</strong>
		  		<?php include( OVABRW_PLUGIN_PATH.'admin/metabox/fields/ovabrw_fixed_time.php' ); ?>
			</div>

			<!-- Hour -->
			<?php woocommerce_wp_text_input([
				'type' 			=> 'text',
				'id' 			=> $this->get_meta_name( 'number_hours' ),
				'class' 		=> 'short',
				'label' 		=> esc_html__( 'Hours', 'ova-brw' ),
				'value' 		=> $this->get_meta_value( 'number_hours' ),
				'placeholder' 	=> 1,
				'description' 	=> esc_html__( 'Tour time (hour)', 'ova-brw' ),
				'desc_tip' 		=> true,
				'data_type' 	=> 'price'
			]); ?>

			<!-- Schedule -->
			<div class="ovabrw-form-field ovabrw_schedule">
		  		<strong class="ovabrw_heading_section">
		  			<?php esc_html_e('Schedule', 'ova-brw'); ?>
		  		</strong>
		  		<?php include( OVABRW_PLUGIN_PATH.'/admin/metabox/fields/ovabrw_daily_price.php' ); ?>
			</div>
		</div>
	</div><!-- END duration -->

	<!-- Guests settings -->
	<div class="ovabrw-advanced-settings" style="display: none;">
		<div class="advanced-header">
			<h3 class="advanced-label">
				<?php esc_html_e( 'Guests', 'ova-brw' ); ?>
			</h3>
			<span aria-hidden="true" class="dashicons dashicons-arrow-up"></span>
			<span aria-hidden="true" class="dashicons dashicons-arrow-down"></span>
		</div>

		<div class="advanced-content">
			<p class="form-field ovabrw_stock_quantity_by_guests_field">
				<label>
					<?php esc_html_e( 'Stock quantity by Guests' ); ?>
					<span>
                        <?php echo wc_help_tip( esc_html__( 'Check Tour booking available by Maximum total number of guest', 'ova-brw' ), true ); ?>
                    </span>
				</label>
				<label class="ovabrw-qty-by-guests">
					<?php ovabrw_wp_text_input([
						'type' 		=> 'checkbox',
						'id' 		=> $this->get_meta_name( 'stock_quantity_by_guests' ),
						'class' 	=> $this->get_meta_name( 'stock_quantity_by_guests' ),
						'name' 		=> $this->get_meta_name( 'stock_quantity_by_guests' ),
						'value' 	=> '1',
						'checked' 	=> $this->get_meta_value( 'stock_quantity_by_guests' ) ? true : false
					]); ?>
				</label>
			</p>

			<?php // Minimum number of guests
				woocommerce_wp_text_input([
					'type' 				=> 'number',
					'id' 				=> $this->get_meta_name( 'min_total_guest' ),
					'class' 			=> 'short',
					'label' 			=> esc_html__( 'Minimum total number of guest', 'ova-brw' ),
					'value' 			=> $this->get_meta_value( 'min_total_guest' ),
					'placeholder' 		=> '2',
					'custom_attributes' => [
						'min' => 0
					]
				]);
				
				// Maximum number of guests
				woocommerce_wp_text_input([
					'type' 				=> 'number',
					'id' 				=> $this->get_meta_name( 'max_total_guest' ),
					'class' 			=> 'short',
					'label' 			=> esc_html__( 'Maximum total number of guest', 'ova-brw' ),
					'value' 			=> $this->get_meta_value( 'max_total_guest' ),
					'placeholder' 		=> '10',
					'custom_attributes' => [
						'min' => 0
					]
				]);

				// Mininum number of adults
				woocommerce_wp_text_input([
					'type' 				=> 'number',
					'id' 				=> $this->get_meta_name( 'adults_min' ),
					'class' 			=> 'short',
					'label' 			=> esc_html__( 'Minimum adults', 'ova-brw' ),
					'value' 			=> $this->get_meta_value( 'adults_min' ),
					'placeholder' 		=> '1',
					'custom_attributes' => [
						'min' => 0
					]
				]);

				// Maximum number of adults
				woocommerce_wp_text_input([
					'type' 			=> 'number',
					'id' 			=> $this->get_meta_name( 'adults_max' ),
					'class' 		=> 'short',
					'label' 		=> esc_html__( 'Maximum adults', 'ova-brw' ),
					'value' 		=> $this->get_meta_value( 'adults_max' ),
					'placeholder' 	=> '10',
					'custom_attributes' => [
						'min' => 0
					]
				]);

				// Show child
				if ( ovabrw_show_children( $this->get_id() ) ) {
					// Minimum number of children
					woocommerce_wp_text_input([
						'type' 				=> 'number',
						'id' 				=> $this->get_meta_name( 'childrens_min' ),
						'class' 			=> 'short',
						'label' 			=> esc_html__( 'Minimum children', 'ova-brw' ),
						'value' 			=> $this->get_meta_value( 'childrens_min' ),
						'placeholder' 		=> '1',
						'custom_attributes' => [
							'min' => 0
						]
					]);

					// Maximum number of children
					woocommerce_wp_text_input([
						'type' 				=> 'number',
						'id' 				=> $this->get_meta_name( 'childrens_max' ),
						'class' 			=> 'short',
						'label' 			=> esc_html__( 'Maximum children', 'ova-brw' ),
						'value' 			=> $this->get_meta_value( 'childrens_max' ),
						'placeholder' 		=> '5',
						'custom_attributes' => [
							'min' => 0
						]
					]);
				} // END if

				// Show baby
				if ( ovabrw_show_babies( $this->get_id() ) ) {
					// Minimum number of babies
					woocommerce_wp_text_input([
						'type' 				=> 'number',
						'id' 				=> $this->get_meta_name( 'babies_min' ),
						'class' 			=> 'short',
						'label' 			=> esc_html__( 'Minimum babies', 'ova-brw' ),
						'value' 			=> $this->get_meta_value( 'babies_min' ),
						'placeholder' 		=> '0',
						'custom_attributes' => [
							'min' => 0
						]
					]);

					// Maximum number of babies
					woocommerce_wp_text_input([
						'type' 				=> 'number',
						'id' 				=> $this->get_meta_name( 'babies_max' ),
						'class' 			=> 'short',
						'label' 			=> esc_html__( 'Maximum babies', 'ova-brw' ),
						'value' 			=> $this->get_meta_value( 'babies_max' ),
						'placeholder' 		=> '3',
						'custom_attributes' => [
							'min' => 0
						]
					]);
				} // END if
			?>
		</div>
	</div><!-- END guests settings -->

	<!-- Deposit -->
	<?php if ( apply_filters( OVABRW_PREFIX.'show_backend_deposit', true ) ): ?>
		<div class="ovabrw-advanced-settings" style="display: none;">
			<div class="advanced-header">
				<h3 class="advanced-label"><?php esc_html_e( 'Deposit', 'ova-brw' ); ?></h3>
				<span aria-hidden="true" class="dashicons dashicons-arrow-up"></span>
				<span aria-hidden="true" class="dashicons dashicons-arrow-down"></span>
			</div>

			<div class="advanced-content">
			<?php
				// Enable deposit
				woocommerce_wp_select([
					'id' 			=> $this->get_meta_name( 'enable_deposit' ),
					'label' 		=> esc_html__( 'Enable deposit', 'ova-brw' ),
					'options' 		=> [
						'no'	=> esc_html__( 'No', 'ova-brw' ),
						'yes'	=> esc_html__( 'Yes', 'ova-brw' )
					],
					'value' 		=> $this->get_meta_value( 'enable_deposit', 'no' ),
					'description' 	=> esc_html__( 'Accept partial payment when booking', 'ova-brw' ),
					'desc_tip' 		=> true
				]);
				
				// Show full payment
				woocommerce_wp_select([
					'id' 			=> $this->get_meta_name( 'force_deposit' ),
					'label' 		=> esc_html__( 'Show full payment', 'ova-brw' ),
					'options' 		=> [
						'no' 	=> esc_html__( 'No', 'ova-brw' ),
						'yes'	=> esc_html__( 'Yes', 'ova-brw' )
					],
					'value' 		=> $this->get_meta_value( 'force_deposit', 'no' ),
					'description' 	=> esc_html__( 'Customers can choose to make a Partial Payment or Pay the Full Amount when Booking', 'ova-brw' ),
					'desc_tip' 		=> true
				]);
				
				// Default selected
				woocommerce_wp_radio([
					'id' 			=> $this->get_meta_name( 'deposit_default' ),
					'label' 		=> esc_html__( 'Default selected', 'ova-brw' ),
					'options' 		=> [
						'full' 		=> esc_html__( 'Full payment', 'ova-brw' ),
						'deposit' 	=> esc_html__( 'Pay deposit', 'ova-brw' )
					],
					'value' 		=> $this->get_meta_value( 'deposit_default', 'full' ),
					'description' 	=> esc_html__( 'Full payment or Pay deposit selected by default.', 'ova-brw' ),
					'desc_tip' 		=> true
				]);

				// Type deposit
				woocommerce_wp_select([
					'id' 			=> $this->get_meta_name( 'type_deposit' ),
					'label' 		=> esc_html__( 'Deposit type', 'ova-brw' ),
					'options' 		=> [
						'percent'	=> esc_html__( 'a percentage amount of payment ', 'ova-brw' ),
						'value'		=> esc_html__( 'a fixed amount of payment', 'ova-brw' )
					],
					'value' 		=> $this->get_meta_value( 'type_deposit', 'percent' )
				]);
				
				// Amount deposit
				woocommerce_wp_text_input([
					'type' 				=> 'text',
					'id' 				=> $this->get_meta_name( 'amount_deposit' ),
					'label' 			=> '',
					'value' 			=> $this->get_meta_value( 'amount_deposit' ),
					'placeholder' 		=> '50',
					'description' 		=> esc_html__( 'Insert deposit amount', 'ova-brw' ),
					'desc_tip' 			=> true,
					'data_type' 		=> 'price',
					'custom_attributes' => [
						'data-percent-unit'	=> '%',
						'data-fixed-unit'	=> get_woocommerce_currency_symbol()
					]
				]); ?>
			</div>
		</div>
	<?php endif; ?><!-- END deposit -->

	<!-- Feature -->
	<div class="ovabrw-advanced-settings" style="display: none;">
		<div class="advanced-header">
			<h3 class="advanced-label"><?php esc_html_e( 'Features', 'ova-brw' ); ?></h3>
			<span aria-hidden="true" class="dashicons dashicons-arrow-up"></span>
			<span aria-hidden="true" class="dashicons dashicons-arrow-down"></span>
		</div>

		<div class="advanced-content">
			<div class="ovabrw-form-field ovabrw_features-field">
		  		<?php include( OVABRW_PLUGIN_PATH.'/admin/metabox/fields/ovabrw_features.php' ); ?>
			</div>
		</div>
	</div><!-- END feature -->

	<!-- Global Discount -->
	<div class="ovabrw-advanced-settings" style="display: none;">
		<div class="advanced-header">
			<h3 class="advanced-label"><?php esc_html_e( 'Global discount (GD) / Price per person', 'ova-brw' ); ?></h3>
			<span aria-hidden="true" class="dashicons dashicons-arrow-up"></span>
			<span aria-hidden="true" class="dashicons dashicons-arrow-down"></span>
		</div>
		<div class="advanced-content">
			<div class="ovabrw-form-field price_discount">
		  		<?php include( OVABRW_PLUGIN_PATH.'/admin/metabox/fields/ovabrw_global_discount.php' ); ?>
			</div>
		</div>
	</div><!-- END global Discount -->

	<!-- Price by range time -->
	<div class="ovabrw-advanced-settings" style="display: none;">
		<div class="advanced-header">
			<h3 class="advanced-label">
				<?php esc_html_e( 'Special time (ST) / Price per person', 'ova-brw' ); ?>
			</h3>
			<span aria-hidden="true" class="dashicons dashicons-arrow-up"></span>
			<span aria-hidden="true" class="dashicons dashicons-arrow-down"></span>
		</div>

		<div class="advanced-content">
			<div class="ovabrw-form-field price_special_time">
				<span class="ovabrw_right">
					<?php esc_html_e( 'Note: ST doesn\'t use GD, it will use DST', 'ova-brw' ); ?>
				</span>
				<?php include( OVABRW_PLUGIN_PATH.'/admin/metabox/fields/ovabrw_st.php' ); ?>
			</div>
		</div>
	</div><!-- END price by range time -->
	
	<!-- Resources -->
	<div class="ovabrw-advanced-settings" style="display: none;">
		<div class="advanced-header">
			<h3 class="advanced-label">
				<?php esc_html_e( 'Resources', 'ova-brw' ); ?>
			</h3>
			<span aria-hidden="true" class="dashicons dashicons-arrow-up"></span>
			<span aria-hidden="true" class="dashicons dashicons-arrow-down"></span>
		</div>
		<div class="advanced-content">
			<div class="ovabrw-form-field ovabrw_resources_field">
		  		<?php include( OVABRW_PLUGIN_PATH.'/admin/metabox/fields/ovabrw_resources.php' ); ?>
			</div>
		</div>
	</div><!-- END resources -->

	<!-- Services -->
	<div class="ovabrw-advanced-settings" style="display: none;">
		<div class="advanced-header">
			<h3 class="advanced-label">
				<?php esc_html_e( 'Services', 'ova-brw' ); ?>
			</h3>
			<span aria-hidden="true" class="dashicons dashicons-arrow-up"></span>
			<span aria-hidden="true" class="dashicons dashicons-arrow-down"></span>
		</div>
		<div class="advanced-content">
			<div class="ovabrw-form-field ovabrw_service_field">
		  		<?php include( OVABRW_PLUGIN_PATH.'/admin/metabox/fields/ovabrw_service.php' ); ?>
			</div>
		</div>
	</div><!-- END service options -->

	<!-- Unavailable time -->
	<div class="ovabrw-advanced-settings" style="display: none;">
		<div class="advanced-header">
			<h3 class="advanced-label">
				<?php esc_html_e( 'Unavailable time (UT)', 'ova-brw' ); ?>
			</h3>
			<span aria-hidden="true" class="dashicons dashicons-arrow-up"></span>
			<span aria-hidden="true" class="dashicons dashicons-arrow-down"></span>
		</div>
		<div class="advanced-content">
			<div class="ovabrw-form-field ovabrw_untime_field">
				<?php include( OVABRW_PLUGIN_PATH.'/admin/metabox/fields/ovabrw_untime.php' ); ?>
			</div>
		</div>
	</div><!-- END unavailable time -->

	<?php
	if ( function_exists( 'offitravel_ovabrw_render_whitelist_accordion' ) ) {
		offitravel_ovabrw_render_whitelist_accordion( get_the_ID() );
	}
	if ( function_exists( 'offitravel_ovabrw_render_contact_accordion' ) ) {
		offitravel_ovabrw_render_contact_accordion( get_the_ID() );
	}
	if ( function_exists( 'offitravel_ovabrw_render_room_mode_accordion' ) ) {
		offitravel_ovabrw_render_room_mode_accordion( get_the_ID() );
	}
	?>

	<!-- Place options -->
	<div class="ovabrw-advanced-settings" style="display: none;">
		<div class="advanced-header">
			<h3 class="advanced-label"><?php esc_html_e( 'Place', 'ova-brw' ); ?></h3>
			<span aria-hidden="true" class="dashicons dashicons-arrow-up"></span>
			<span aria-hidden="true" class="dashicons dashicons-arrow-down"></span>
		</div>
		<div class="advanced-content">
			<!-- Short address -->
			<div class="ovabrw-form-field">
				<?php woocommerce_wp_text_input([
					'id' 			=> $this->get_meta_name( 'short_address' ),
					'class' 		=> 'short',
					'label'			=> esc_html__( 'Short address', 'ova-brw' ),
					'type' 			=> 'text',
					'value' 		=> $this->get_meta_value( 'short_address' ),
					'description' 	=> esc_html__( 'If this field is filled in, this field will display as address instead of the Map and Iframe address.', 'ova-brw' ),
					'desc_tip' 		=> true
				]); ?>
			</div>

			<!-- Show map -->
			<div class="ovabrw-form-field">
				<?php woocommerce_wp_select([
					'id' 			=> $this->get_meta_name( 'show_map' ),
					'class' 		=> 'short',
					'label'			=> esc_html__( 'Show map', 'ova-brw' ),
					'value' 		=> $this->get_meta_value( 'show_map', 'global' ),
					'options'		=> [
						'global'	=> esc_html__( 'Global', 'ova-brw' ),
						'yes' 		=> esc_html__( 'Yes', 'ova-brw' ),
						'no' 		=> esc_html__( 'No', 'ova-brw' ),
					]
				]); ?>
			</div>

			<!-- Map -->
			<div class="ovabrw-form-field">
				<div class="ovabrw-map-type">
					<?php $map_type = ovabrw_get_post_meta( $post->ID ,'map_type', 'api' ); ?>
					<label class="container"><?php esc_html_e( 'Google map' ); ?>
						<?php if ( 'api' === $map_type ): ?>
					  		<input type="radio" checked="checked" name="map_type" value="api">
					  	<?php else: ?>
					  		<input type="radio" name="map_type" value="api">
					  	<?php endif; ?>
					  	<span class="checkmark"></span>
					</label>
					<label class="container"><?php esc_html_e( 'Google map iframe' ); ?>
					<?php if ( 'iframe' === $map_type ): ?>
					  	<input type="radio" checked="checked" name="map_type" value="iframe">
					<?php else: ?>
				  		<input type="radio" name="map_type" value="iframe">
				  	<?php endif; ?>
					  	<span class="checkmark"></span>
					</label>
				</div>
				<div class="ovabrw-gg-map">
					<?php
						$map_name = $post_id ? get_post_meta( $post->ID ,'ovaev_map_name', true ) : esc_html__('New York', 'ova-brw');
						$map_address  = $post_id ? get_post_meta( $post_id, 'ovabrw_address', true ) : esc_html__( 'New York, NY, USA', 'ova-brw' );
						if ( !$map_address ) {
							$map_address = esc_html__( 'New York, NY, USA', 'ova-brw' );
						}
						
						// Address
						woocommerce_wp_text_input([
							'type' 				=> 'text',
							'id' 				=> 'pac-input',
							'class' 			=> 'controls',
							'label'				=> esc_html__( '', 'ova-brw' ),
							'value' 			=> $map_address,
							'placeholder'		=> esc_html__( 'Enter a venue', 'ova-brw' ),
							'custom_attributes' => [
								'autocomplete' 	=> 'off',
								'autocorrect'	=> 'off',
								'autocapitalize'=> 'none'
							]
						]);
					?>
					<div id="admin_show_map"></div>
					<div id="infowindow-content">
						<span id="place-name" class="title">
							<?php echo esc_html( $map_name ); ?>
						</span>
						<br>
						<span id="place-address">
							<?php echo esc_html( $map_address ); ?>
						</span>
					</div>
					<div id="map_info">
						<?php include( OVABRW_PLUGIN_PATH.'/admin/metabox/fields/ovabrw_product_map.php' ); ?>
					</div>
					<div class="admin-map-iframe">
						<?php
							// Get iframe
							$map_iframe = ovabrw_get_post_meta( $post->ID ,'map_iframe' );

							// Allowed HTML
							$allowed_html = apply_filters( OVABRW_PREFIX.'allowed_html', [
								'iframe' => [
									'src'             => true,
									'height'          => true,
									'width'           => true,
									'frameborder'     => true,
									'allowfullscreen' => true
								]
							]);
						?>
						<textarea name="map_iframe" id="map_iframe" cols="100%" rows="10"><?php echo wp_kses( $map_iframe, $allowed_html ); ?></textarea>
					</div>
				</div>
			</div>	
		</div>
	</div><!-- END place options -->

	<!-- Advanced options -->
	<div class="ovabrw-advanced-settings">
		<div class="advanced-header">
			<h3 class="advanced-label">
				<?php esc_html_e( 'Advanced options', 'ova-brw' ); ?>
			</h3>
			<span aria-hidden="true" class="dashicons dashicons-arrow-up"></span>
			<span aria-hidden="true" class="dashicons dashicons-arrow-down"></span>
		</div>
		<div class="advanced-content">
			<?php
				// Product templates
	            $product_templates = [
	            	'global' => esc_html__( 'Category settings', 'ova-brw' )
	            ];

				// Get templates from elementor
	            $templates = get_posts([
	            	'post_type' 	=> 'elementor_library',
	            	'meta_key' 		=> '_elementor_template_type',
	            	'meta_value' 	=> 'page',
	            	'numberposts'   => -1,
	            	'fields' 		=> 'ids'
	            ]);
	            if ( ovabrw_array_exists( $templates ) ) {
	                foreach ( $templates as $template_id ) {
	                    $product_templates[$template_id] = get_the_title( $template_id );
	                }
	            }

				woocommerce_wp_select([
					'id' 			=> $this->get_meta_name( 'product_template' ),
					'label' 		=> esc_html__( 'Product template', 'ova-brw' ),
					'options' 		=> $product_templates,
					'value' 		=> $this->get_meta_value( 'product_template' ),
					'desc_tip'		=> true,
					'description'	=> esc_html__( 'Category settings: Setup in per category', 'ova-brw' )
				]);
				
				// Disable Weekday
				woocommerce_wp_text_input([
					'type' 			=> 'text',
					'id' 			=> $this->get_meta_name( 'product_disable_week_day' ),
					'class' 		=> 'short',
					'label' 		=> esc_html__( 'Disable weekday', 'ova-brw' ),
					'value' 		=> $this->get_meta_value( 'product_disable_week_day' ),
					'placeholder' 	=> '0,6',
					'description' 	=> esc_html__( '0: Sunday, 1: Monday, 2: Tuesday, 3: Wednesday, 4: Thursday, 5: Friday, 6: Saturday . Example: 0,6', 'ova-brw' ),
					'desc_tip' 		=> true
				]);
				
				// Preparation time
				woocommerce_wp_text_input([
					'type' 			=> 'number',
					'id' 			=> $this->get_meta_name( 'preparation_time' ),
					'class' 		=> 'short',
					'label' 		=> esc_html__( 'X days preparation time', 'ova-brw' ),
					'value' 		=> $this->get_meta_value( 'preparation_time' ),
					'placeholder' 	=> esc_html__( 'Number', 'ova-brw' ),
					'description' 	=> esc_html__( 'The customer book X days in advance from the current time', 'ova-brw' ),
					'desc_tip' 		=> true
				]);
			?>

			<p class="form-field ovabrw_book_before_x_hours_field">
				<label for="ovabrw_book_before_x_hours">
					<?php esc_html_e( 'Book before X hours today', 'ova-brw' ); ?>
				</label>
				<span class="ovabrw-product-book-x-hours">
					<?php ovabrw_wp_text_input([
						'type' 			=> 'text',
						'class' 		=> 'short',
						'name' 			=> $this->get_meta_name( 'book_before_x_hours' ),
						'value' 		=> $this->get_meta_value( 'book_before_x_hours' ),
						'data_type' 	=> 'timepicker',
						'placeholder' 	=> esc_html__( 'Choose time', 'ova-brw' )
					]); ?>
					<span class="remove-x-hours">x</span>
				</span>
			</p>

			<!-- Guests -->
			<div class="ovabrw-form-field">
				<strong class="ovabrw_heading_section">
					<?php esc_html_e( 'Guests', 'ova-brw' ); ?>
				</strong>
				<?php
					// Show children field
					woocommerce_wp_select([
						'id' 			=> $this->get_meta_name( 'show_children' ),
						'label' 		=> esc_html__( 'Show children field', 'ova-brw' ),
						'options' 		=> [
							'global' 	=> esc_html__( 'Global', 'ova-brw' ),
							'yes'		=> esc_html__( 'Yes', 'ova-brw' ),
							'no'		=> esc_html__( 'No', 'ova-brw' ),
						],
						'value' 		=> $this->get_meta_value( 'show_children', 'global' ),
						'description'	=> esc_html__( 'Global settings: Go to WooCommerce >> Settings >> Booking Tours >> Guests >> Show children field', 'ova-brw' ),
						'desc_tip'		=> true,
					]);
				
					// Show babies field
					woocommerce_wp_select([
						'id' 			=> $this->get_meta_name( 'show_babies' ),
						'label' 		=> esc_html__( 'Show babies field', 'ova-brw' ),
						'options' 		=> [
							'global' 	=> esc_html__( 'Global', 'ova-brw' ),
							'yes'		=> esc_html__( 'Yes', 'ova-brw' ),
							'no'		=> esc_html__( 'No', 'ova-brw' ),
						],
						'value' 		=> $this->get_meta_value( 'show_babies', 'global' ),
						'description'	=> esc_html__( 'Global settings: Go to WooCommerce >> Settings >> Booking Tours >> Guests >> Show babies field', 'ova-brw' ),
						'desc_tip'		=> true,
					]);
				?>
			</div>

			<?php if ( apply_filters( OVABRW_PREFIX.'show_checkout_field_setting_product', true ) ): ?>
				<div class="ovabrw-form-field">
					<strong class="ovabrw_heading_section">
						<?php esc_html_e( 'Custom checkout fields', 'ova-brw' ); ?>
					</strong>
					<?php
						woocommerce_wp_select([
							'id' 			=> $this->get_meta_name( 'manage_custom_checkout_field' ),
							'label' 		=> esc_html__( 'Display custom fields from', 'ova-brw' ),
							'options' 		=> [
								'all'	=> esc_html__( 'Category settings', 'ova-brw' ),
								'new'	=> esc_html__( 'New', 'ova-brw' ),
								'no'	=> esc_html__( 'No', 'ova-brw' ),
							],
							'value' 		=> $this->get_meta_value( 'manage_custom_checkout_field', 'all' ),
							'description'	=> esc_html__( 'Category settings: Setup in per category', 'ova-brw' ),
							'desc_tip'		=> true,
						]);
					
						// Custom checkout field
						woocommerce_wp_textarea_input([
							'id' 			=> $this->get_meta_name( 'product_custom_checkout_field' ),
					        'label' 		=> '',
					        'value' 		=> $this->get_meta_value( 'product_custom_checkout_field' ),
					        'placeholder' 	=> esc_html__( 'Insert new custom checkout fields', 'ova-brw' ),
					        'description' 	=> esc_html__( 'Insert name in general custom checkout field. Example: ova_email_field, ova_address_field', 'ova-brw' ),
					        'desc_tip'		=> true
						]);
					?>
				</div>
			<?php endif; ?>

			<!-- Show/Hide Checkout Field -->
			<div class="ovabrw-form-field">
				<strong class="ovabrw_heading_section">
					<?php esc_html_e( 'Check-out field', 'ova-brw' ); ?>
				</strong>
				<?php woocommerce_wp_select([
					'id' 			=> $this->get_meta_name( 'manage_checkout_field' ),
					'label' 		=> esc_html__( 'Display', 'ova-brw' ),
					'options' 		=> [
						'global'	=> esc_html__( 'Global settings', 'ova-brw' ),
						'show'		=> esc_html__( 'Yes', 'ova-brw' ),
						'hide'		=> esc_html__( 'No', 'ova-brw' )
					],
					'value' 		=> $this->get_meta_value( 'manage_checkout_field', 'global' ),
					'description'	=> esc_html__( 'Global settings: Go to WooCommerce >> Settings >> Booking Tours >> Show Check-out field', 'ova-brw' ),
					'desc_tip'		=> true
				]); ?>
			</div>

			<!-- Show/Hide Form -->
			<div class="ovabrw-form-field">
				<strong class="ovabrw_heading_section">
					<?php esc_html_e( 'Forms', 'ova-brw' ); ?>
				</strong>
				<?php woocommerce_wp_select([
					'id' 			=> $this->get_meta_name( 'forms_product' ),
					'label' 		=> esc_html__( 'Show forms', 'ova-brw' ),
					'options' 		=> [
						''					=> esc_html__( 'Global settings', 'ova-brw' ),
						'booking'			=> esc_html__( 'Only booking form', 'ova-brw' ),
						'enquiry'			=> esc_html__( 'Only request form', 'ova-brw' ),
						'enquiry_shortcode'	=> esc_html__( 'Only enquiry form', 'ova-brw' ),
						'all'				=> esc_html__( 'Show all', 'ova-brw' )
					],
					'value' 		=> $this->get_meta_value( 'forms_product' ),
					'description'	=> esc_html__( 'Global settings: Go to WooCommerce >> Settings >> Booking Tours >> Product Details', 'ova-brw' ),
					'desc_tip'		=> true
				]); // END

				// Enquiry shortcode
				woocommerce_wp_text_input([
					'type' 			=> 'text',
					'id' 			=> $this->get_meta_name( 'enquiry_shortcode' ),
					'class' 		=> 'short',
					'label' 		=> esc_html__( 'Enquiry shortcode', 'ova-brw' ),
					'value' 		=> $this->get_meta_value( 'enquiry_shortcode' ),
					'placeholder' 	=> esc_html__( '[contact-form-7]', 'ova-brw' ),
					'description' 	=> esc_html__( 'Insert a shortcode. You can use shortcode of contact form 7 plugin.', 'ova-brw' ),
					'desc_tip' 		=> true
				]); ?>
			</div>

			<!-- Header -->
			<div class="ovabrw-form-field">
				<strong class="ovabrw_heading_section">
					<?php esc_html_e( 'Header & Footer', 'ova-brw' ); ?>
				</strong>
				<?php
					// Get headers
					$headers = [ '' => esc_html__( 'Global', 'ova-brw' ) ];
					if ( ovabrw_array_exists( apply_filters( 'tripgo_list_header', '' ) ) ) {
						$headers = array_merge( $headers, apply_filters( 'tripgo_list_header', '' ) );
					}

					woocommerce_wp_select([
						'id' 			=> $this->get_meta_name( 'product_header' ),
						'label' 		=> esc_html__( 'Header', 'ova-brw' ),
						'options' 		=> $headers,
						'value' 		=> $this->get_meta_value( 'product_header' ),
						'description'	=> esc_html__( 'Global settings: Go to Appearacce >> Customize >> WooCommerce >> Product detail', 'ova-brw' ),
						'desc_tip'		=> true
					]);
					
					// Get footers
					$footers = [ '' => esc_html__( 'Global', 'ova-brw' ) ];
					if ( ovabrw_array_exists( apply_filters( 'tripgo_list_footer', '' ) ) ) {
						$footers = array_merge( $footers, apply_filters( 'tripgo_list_footer', '' ) );
					}

					woocommerce_wp_select([
						'id' 			=> $this->get_meta_name( 'product_footer' ),
						'label' 		=> esc_html__( 'Footer', 'ova-brw' ),
						'options' 		=> $footers,
						'value' 		=> $this->get_meta_value( 'product_footer' ),
						'description'	=> esc_html__( 'Global settings: Go to Appearacce >> Customize >> WooCommerce >> Product detail', 'ova-brw' ),
						'desc_tip'		=> true
					]);
				?>
			</div>
		</div>
	</div><!-- END advanced options -->
</div>