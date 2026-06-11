<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get product id
$product_id = tripgo_get_meta_data( 'id', $args, get_the_id() );

// Get product
$product = wc_get_product( $product_id );
if ( !$product || !$product->is_type( 'ovabrw_car_rental' ) ) return;

// Guest index
$key = (int)tripgo_get_meta_data( 'key', $args );

// Guest name
$guest_name = tripgo_get_meta_data( 'guest_name', $args );
if ( $guest_name ):
	// Get guest fields
	$guest_fields = get_option( 'ovabrw_guest_fields' );
	if ( !tripgo_array_exists( $guest_fields ) ) return;
?>
	<div class="guest-info-item">
		<div class="guest-info-header">
            <h3 class="ovabrw-label">
            	<?php echo sprintf( esc_html__( 'Guest %s', 'tripgo' ), $key + 1 ); ?>
           	</h3>
            <i class="icomoon icomoon-caret-down" aria-hidden="true"></i>
        </div>
        <div class="guest-info-body">
        	<div class="guest-info-content">
    		<?php if ( tripgo_array_exists( $guest_fields ) ):
    			foreach ( $guest_fields as $name => $fields ):
    				$enable = tripgo_get_meta_data( 'enable', $fields );
        			if ( !$enable ) continue;

        			$type           = tripgo_get_meta_data( 'type', $fields );
			        $label          = tripgo_get_meta_data( 'label', $fields );
			        $accept         = tripgo_get_meta_data( 'accept', $fields );
			        $max_size       = tripgo_get_meta_data( 'max_size', $fields );
			        $pattern        = tripgo_get_meta_data( 'pattern', $fields );
			        $description    = tripgo_get_meta_data( 'description', $fields );
			        $placeholder    = tripgo_get_meta_data( 'placeholder', $fields );
			        $default        = tripgo_get_meta_data( 'default', $fields );
			        $min            = tripgo_get_meta_data( 'min', $fields );
			        $max            = tripgo_get_meta_data( 'max', $fields );
			        $class          = tripgo_get_meta_data( 'class', $fields );
			        $required       = tripgo_get_meta_data( 'required', $fields );

			        // Options
			        $options        = [];
			        $opt_ids        = tripgo_get_meta_data( 'option_ids', $fields );
			        $opt_names      = tripgo_get_meta_data( 'option_names', $fields );

			        if ( tripgo_array_exists( $opt_ids ) && tripgo_array_exists( $opt_names ) ) {
			            foreach ( $opt_ids as $index => $opt_id ) {
			                $opt_name = (string)tripgo_get_meta_data( $index, $opt_names );

			                // Options
			                $options[$opt_id] = $opt_name;
			            }
			        }

			        // Required class
			        $required_class = '';
			        if ( $required ) {
			        	$class .= ' ovabrw-input-required';
			        	$required_class = 'ovabrw-required';
			        }

			        // Get field id
			        $field_id = tripgo_unique_id( 'guest_'.$name.'_'.$product_id );
    			?>
    			<div class="guest-info-field ovabrw-guest-info-<?php echo esc_attr( $type ); ?>">
    				<?php if ( 'checkbox' === $type || 'radio' === $type || 'file' === $type ): ?>
						<h3 class="ovabrw-label <?php echo esc_attr( $required_class ); ?>">
							<?php echo esc_html( $label );

							// Description
							if ( $description ): ?>
			                    <span class="ovabrw-description" aria-label="<?php echo esc_attr( $description ); ?>">
			                    </span>
			                <?php endif; ?>
						</h3>
					<?php else: ?>
	    				<label for="<?php echo esc_attr( $field_id ); ?>" class="<?php echo esc_attr( $required_class ); ?>">
							<?php echo esc_html( $label );

							// Description
							if ( $description ): ?>
			                    <span class="ovabrw-description" aria-label="<?php echo esc_attr( $description ); ?>">
			                    </span>
			                <?php endif; ?>
						</label>
					<?php endif;

					// Textarea
					if ( 'textarea' === $type ): ?>
						<textarea name="<?php echo esc_attr( $guest_name.'_info['.$key.']['.$name.']' ); ?>" id="<?php echo esc_attr( $field_id ); ?>" class="<?php echo esc_attr( $class ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>" value="<?php echo esc_attr( $default ); ?>" rows="5"></textarea>
					<?php elseif ( 'number' === $type ):
						tripgo_text_input([
				            'type'          => 'number',
				            'id' 			=> $field_id,
				            'class'     	=> $class,
				            'name'      	=> $guest_name.'_info['.$key.']['.$name.']',
				            'value'     	=> $default,
				            'placeholder' 	=> $placeholder,
				            'attrs'         => [
		                        'min' 	=> $min,
		                        'max' 	=> $max
		                    ]
				        ]);
					elseif ( 'radio' === $type ): ?>
						<div class="ovabrw-radio <?php echo esc_attr( $class ); ?>">
							<?php foreach ( $options as $opt_id => $opt_name ):
								if ( !$default && $required_class ) $default = $opt_id;
							?>
								<div class="radio-item">
									<?php tripgo_text_input([
										'type' 		=> 'radio',
										'id' 		=> $field_id.'_'.$opt_id,
										'name' 		=> $guest_name.'_info['.$key.']['.$name.']',
										'value' 	=> $opt_id,
										'checked' 	=> $default === $opt_id ? true : false
									]); ?>
									<span class="checkmark"></span>
									<label for="<?php echo esc_attr( $field_id.'_'.$opt_id ); ?>">
										<?php echo esc_html( $opt_name ); ?>
									</label>
								</div>
							<?php endforeach; // END foreach ?>
						</div>
					<?php elseif ( 'checkbox' === $type ): ?>
						<div class="ovabrw-checkbox <?php echo esc_attr( $class ); ?>">
							<?php foreach ( $options as $opt_id => $opt_name ):
								if ( !$default && $required_class ) $default = $opt_id;
							?>
								<div class="checkbox-item">
									<?php tripgo_text_input([
										'type' 		=> 'checkbox',
										'id' 		=> $field_id.'_'.$opt_id,
										'name' 		=> $guest_name.'_info['.$key.']['.$name.'][]',
										'value' 	=> $opt_id,
										'checked' 	=> $default === $opt_id ? true : false
									]); ?>
									<span class="checkmark"></span>
									<label for="<?php echo esc_attr( $field_id.'_'.$opt_id, ); ?>">
										<?php echo esc_html( $opt_name ); ?>
									</label>
								</div>
							<?php endforeach; ?>
						</div>
					<?php elseif ( 'select' === $type ): ?>
						<select name="<?php echo esc_attr( $guest_name.'_info['.$key.']['.$name.']' ); ?>" id="<?php echo esc_attr( $field_id ); ?>" class="<?php echo esc_attr( $class ); ?>">
							<option value="">
								<?php echo esc_html( $placeholder ); ?>
							</option>
							<?php foreach ( $options as $opt_id => $opt_name ): ?>
								<option value="<?php echo esc_attr( $opt_id ); ?>"<?php selected( $default, $opt_id ); ?>>
									<?php echo esc_html( $opt_name ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					<?php elseif ( 'date' === $type ):
						// Date format
						$date_format = function_exists( 'ovabrw_get_date_format' ) ? ovabrw_get_date_format() : 'd-m-Y';
		                // ovabrw-datepicker-field

		                // Min date
		                $min_date = strtotime( $min ) ? gmdate( $date_format, strtotime( $min ) ) : '';

		                // Max date
		                $max_date = strtotime( $max ) ? gmdate( $date_format, strtotime( $max ) ) : '';

		                // Min year
		                $min_year = '';
		                if ( strtotime( $min_date ) ) $min_year = gmdate( 'Y', strtotime( $min_date ) );

		                // Max year
		                $max_year = '';
		                if ( strtotime( $max_date ) ) $max_year = gmdate( 'Y', strtotime( $max_date ) );

		                tripgo_text_input([
				            'type'      => 'text',
				            'id'        => $field_id,
				            'class'     => $class,
				            'name'      => $guest_name.'_info['.$key.']['.$name.']',
				            'value'     => $default,
				            'data_type' => 'datepicker-field',
				            'attrs'     => [
				                'data-min-date' => apply_filters( 'tripgo_guest_info_min_date', $min_date, $fields ),
		                        'data-max-date' => apply_filters( 'tripgo_guest_info_max_date', $max_date, $fields ),
		                        'data-min-year' => apply_filters( 'tripgo_guest_info_min_year', $min_year, $fields ),
		                        'data-max-year' => apply_filters( 'tripgo_guest_info_max_year', $max_year, $fields ),
		                        'data-start-date' => apply_filters( 'tripgo_guest_info_start_date', '', $fields )
				            ]
				        ]);
				    elseif ( 'file' === $type ): ?>
				    	<div class="ovabrw-file <?php echo esc_attr( $class ); ?>">
							<label for="<?php echo esc_attr( $field_id ); ?>">
								<span class="ovabrw-file-chosen">
									<?php esc_html_e( 'Elegir archivo', 'tripgo' ); ?>
								</span>
								<span class="ovabrw-file-name"></span>
							</label>
							<?php tripgo_text_input([
								'type' 	=> $type,
								'id' 	=> $field_id,
								'name' 	=> $guest_name.'_'.$name.'['.$key.']',
								'attrs' => [
									'accept'    => $accept,
									'max-size' 	=> $max_size,
								]
							]); ?>
						</div>
					<?php elseif ( 'tel' === $type ):
						tripgo_text_input([
				            'type'      	=> $type,
				            'id' 			=> $field_id,
				            'class'     	=> $class,
				            'name'      	=> $guest_name.'_info['.$key.']['.$name.']',
				            'value'     	=> $default,
				            'placeholder' 	=> $placeholder,
				            'attrs'         => [
		                        'data-pattern' => $pattern
		                    ]
				        ]);
					else:
						tripgo_text_input([
				            'type'      	=> $type,
				            'id' 			=> $field_id,
				            'class'     	=> $class,
				            'name'      	=> $guest_name.'_info['.$key.']['.$name.']',
				            'value'     	=> $default,
				            'placeholder' 	=> $placeholder,
				        ]);
					endif; ?>
    			</div>
    		<?php endforeach;
    		endif; ?>
            </div>
        </div>
    </div>
<?php endif;