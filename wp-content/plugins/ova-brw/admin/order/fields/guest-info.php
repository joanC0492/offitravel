<?php if ( !defined( 'ABSPATH' ) ) exit();

if ( $guest_name ):
	// Get guest fields
	$guest_fields = ovabrw_get_option( 'guest_fields' );
	if ( !ovabrw_array_exists( $guest_fields ) ) return;
?>
	<div class="guest-info-item">
		<div class="guest-info-header">
            <h3 class="ovabrw-label">
            	<?php echo sprintf( esc_html__( 'Guest %s', 'ova-brw' ), $key + 1 ); ?>
           	</h3>
            <span class="dashicons dashicons-arrow-down" aria-hidden="true"></span>
        </div>
        <div class="guest-info-body">
        	<div class="guest-info-content">
    		<?php if ( ovabrw_array_exists( $guest_fields ) ):
    			foreach ( $guest_fields as $name => $fields ):
    				$enable = ovabrw_get_meta_data( 'enable', $fields );
        			if ( !$enable ) continue;

        			$type           = ovabrw_get_meta_data( 'type', $fields );
			        $label          = ovabrw_get_meta_data( 'label', $fields );
			        $accept         = ovabrw_get_meta_data( 'accept', $fields );
			        $max_size       = ovabrw_get_meta_data( 'max_size', $fields );
			        $pattern        = ovabrw_get_meta_data( 'pattern', $fields );
			        $description    = ovabrw_get_meta_data( 'description', $fields );
			        $placeholder    = ovabrw_get_meta_data( 'placeholder', $fields );
			        $default        = ovabrw_get_meta_data( 'default', $fields );
			        $min            = ovabrw_get_meta_data( 'min', $fields );
			        $max            = ovabrw_get_meta_data( 'max', $fields );
			        $class          = ovabrw_get_meta_data( 'class', $fields );
			        $required       = ovabrw_get_meta_data( 'required', $fields );

			        // Options
			        $options        = [];
			        $opt_ids        = ovabrw_get_meta_data( 'option_ids', $fields );
			        $opt_names      = ovabrw_get_meta_data( 'option_names', $fields );

			        if ( ovabrw_array_exists( $opt_ids ) && ovabrw_array_exists( $opt_names ) ) {
			            foreach ( $opt_ids as $index => $opt_id ) {
			                $opt_name = (string)ovabrw_get_meta_data( $index, $opt_names );

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
			        $field_id = ovabrw_unique_id( 'guest_'.$name.'_'.$product_id );
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
						<textarea name="<?php echo esc_attr( $guest_name.'_info['.$product_id.']['.$key.']['.$name.']' ); ?>" id="<?php echo esc_attr( $field_id ); ?>" class="<?php echo esc_attr( $class ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>" value="<?php echo esc_attr( $default ); ?>" rows="5"></textarea>
					<?php elseif ( 'number' === $type ):
						ovabrw_text_input([
				            'type'          => 'number',
				            'id' 			=> $field_id,
				            'class'     	=> $class,
				            'name'      	=> $guest_name.'_info['.$product_id.']['.$key.']['.$name.']',
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
									<?php ovabrw_text_input([
										'type' 		=> 'radio',
										'id' 		=> $field_id.'_'.$opt_id,
										'name' 		=> $guest_name.'_info['.$product_id.']['.$key.']['.$name.']',
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
									<?php ovabrw_text_input([
										'type' 		=> 'checkbox',
										'id' 		=> $field_id.'_'.$opt_id,
										'name' 		=> $guest_name.'_info['.$product_id.']['.$key.']['.$name.'][]',
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
						<select name="<?php echo esc_attr( $guest_name.'_info['.$product_id.']['.$key.']['.$name.']' ); ?>" id="<?php echo esc_attr( $field_id ); ?>" class="ovabrw-select <?php echo esc_attr( $class ); ?>">
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
						$date_format = ovabrw_get_date_format();

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

		                ovabrw_text_input([
				            'type'      => 'text',
				            'id'        => $field_id,
				            'class'     => $class,
				            'name'      => $guest_name.'_info['.$product_id.']['.$key.']['.$name.']',
				            'value'     => $default,
				            'data_type' => 'datepicker-field',
				            'attrs'     => [
				                'data-min-date' => apply_filters( OVABRW_PREFIX.'guest_info_min_date', $min_date, $fields ),
		                        'data-max-date' => apply_filters( OVABRW_PREFIX.'guest_info_max_date', $max_date, $fields ),
		                        'data-min-year' => apply_filters( OVABRW_PREFIX.'guest_info_min_year', $min_year, $fields ),
		                        'data-max-yeear' => apply_filters( OVABRW_PREFIX.'guest_info_max_year', $max_year, $fields ),
		                        'data-start-date' => apply_filters( OVABRW_PREFIX.'guest_info_start_date', '', $fields ),
				            ]
				        ]);
				    elseif ( 'file' === $type ): ?>
				    	<div class="<?php echo esc_attr( $class ); ?>">
							<?php ovabrw_text_input([
								'type' 	=> $type,
								'id' 	=> $field_id,
								'name' 	=> $guest_name.'_'.$name.'['.$product_id.']['.$key.']',
								'attrs' => [
									'accept'    => $accept,
									'max-size' 	=> $max_size,
								]
							]); ?>
						</div>
					<?php elseif ( 'tel' === $type ):
						ovabrw_text_input([
				            'type'      	=> $type,
				            'id' 			=> $field_id,
				            'class'     	=> $class,
				            'name'      	=> $guest_name.'_info['.$product_id.']['.$key.']['.$name.']',
				            'value'     	=> $default,
				            'placeholder' 	=> $placeholder,
				            'attrs'         => [
		                        'data-pattern' => $pattern
		                    ]
				        ]);
					else:
						ovabrw_text_input([
				            'type'      	=> $type,
				            'id' 			=> $field_id,
				            'class'     	=> $class,
				            'name'      	=> $guest_name.'_info['.$product_id.']['.$key.']['.$name.']',
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