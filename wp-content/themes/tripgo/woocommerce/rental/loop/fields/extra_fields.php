<?php if ( ! defined( 'ABSPATH' ) ) exit();

// Get product id
$product_id = tripgo_get_meta_data( 'id', $args, get_the_id() );

// Get product
$product = wc_get_product( $product_id );
if ( !$product || !$product->is_type( 'ovabrw_car_rental' ) ) return;

// Get current form
$form = tripgo_get_meta_data( 'form', $args );

// Get custom checkout fields
$cckf = ovabrw_get_list_field_checkout( $product_id );
if ( tripgo_array_exists( $cckf ) ):
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

	foreach ( $cckf as $key => $field ):
		if ( 'on' != tripgo_get_meta_data( 'enabled', $field ) ) continue;

		// Class
		$class = tripgo_get_meta_data( 'class', $field );

		// Required
		$required = tripgo_get_meta_data( 'required', $field );
		if ( $required ) {
			$class .= ' ovabrw-input-required';
		}

		// Type
		$type = tripgo_get_meta_data( 'type', $field );

		// Label
		$label = tripgo_get_meta_data( 'label', $field );
		$label = isset( $es_map[$label] ) ? $es_map[$label] : $label;

		// Placeholder
		$placeholder = tripgo_get_meta_data( 'placeholder', $field );
		$placeholder = isset( $es_map[$placeholder] ) ? $es_map[$placeholder] : $placeholder;

		// Default
		$default = tripgo_get_meta_data( 'default', $field );

		// Get field id
		$field_id = tripgo_unique_id( $key.'_'.$product_id );

		// Option quantity
		$option_qtys = [];
	?>
		<div class="rental_item">
			<?php if ( 'checkbox' === $type || 'radio' === $type || 'file' === $type ): ?>
				<h3 class="ovabrw-label <?php echo $required ? 'ovabrw-required' : ''; ?>">
					<?php echo esc_html( $label ); ?>
				</h3>
			<?php else: ?>
				<label for="<?php echo esc_attr( $field_id ); ?>" class="<?php echo $required ? 'ovabrw-required' : ''; ?>">
					<?php echo esc_html( $label ); ?>
				</label>
			<?php endif; // END if

			// Textarea
			if ( 'textarea' === $field['type'] ): ?>
				<textarea name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $field_id ); ?>" class="<?php echo esc_attr( $class ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>" value="<?php echo esc_attr( $default ); ?>" rows="5"></textarea>
			<?php elseif ( 'select' === $type ): // Select
				// Get option ids
				$opt_ids = tripgo_get_meta_data( 'ova_options_key', $field, [] );
				if ( !tripgo_array_exists( $opt_ids ) ) continue;

				// Get option texts
				$opt_texts = tripgo_get_meta_data( 'ova_options_text', $field, [] );

				// Get option qtys
				$opt_qtys = tripgo_get_meta_data( 'ova_options_qty', $field, [] );
			?>
				<div class="ovabrw-select">
					<select name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $field_id ); ?>" class="<?php echo esc_attr( $class ); ?>">
						<option value="">
							<?php echo sprintf( esc_html__( 'Selecciona %s', 'tripgo' ), esc_attr( $label ) ); ?>
						</option>
						<?php foreach ( $opt_ids as $k => $opt_id ):
							// Default
							if ( !$default && $required ) $default = $opt_id;

							// Get option text
							$text = tripgo_get_meta_data( $k, $opt_texts );
							$text = isset( $es_map[$text] ) ? $es_map[$text] : $text;

							// Get option quantity
							$qty = (int)tripgo_get_meta_data( $k, $opt_qtys );
							if ( $qty ) $option_qtys[$opt_id] = $qty;
						?>
							<option value="<?php echo esc_attr( $opt_id ); ?>"<?php selected( $default, $opt_id ); ?>>
								<?php echo esc_html( $text ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<?php if ( tripgo_array_exists( $option_qtys ) ):
						foreach ( $option_qtys as $opt_id => $opt_qty ): ?>
							<div class="select-item-qty" data-option="<?php echo esc_attr( $opt_id ); ?>">
								<span class="select-qty">1</span>
								<?php tripgo_text_input([
									'type' 	=> 'text',
									'class' => 'select-input-qty',
									'name' 	=> $key.'_qty['.$opt_id.']',
									'value' => 1,
									'attrs' => [
										'min' => 1,
										'max' => $opt_qty
									]
								]); ?>
								<div class="ovabrw-select-icon">
									<i class="arrow_triangle-up" aria-hidden="true"></i>
									<i class="arrow_triangle-down" aria-hidden="true"></i>
								</div>
							</div>
					<?php endforeach;
					endif; ?>
				</div>
			<?php elseif ( 'radio' === $type ): // Radio
				// Get option values
				$opt_values = tripgo_get_meta_data( 'ova_radio_values', $field, [] );
				if ( !tripgo_array_exists( $opt_values ) ) continue;

				// Get option qtys
				$opt_qtys = tripgo_get_meta_data( 'ova_radio_qtys', $field, [] );
			?>
				<div class="ovabrw-radio <?php echo esc_attr( $class ); ?>">
					<?php foreach ( $opt_values as $k => $value ):
						// Default
						if ( !$default && $required ) $default = $value;

						// Get option quantity
						$qty = (int)tripgo_get_meta_data( $k, $opt_qtys );
					?>
						<div class="radio-item">
							<?php tripgo_text_input([
								'type' 		=> 'radio',
								'id' 		=> 'ovabrw-radio'.esc_attr( $k ).esc_attr( $form ),
								'name' 		=> $key,
								'value' 	=> $value,
								'checked' 	=> $default === $value ? true : false
							]); ?>
							<span class="checkmark"></span>
							<label for="<?php echo 'ovabrw-radio'.esc_attr( $k ).esc_attr( $form ); ?>">
								<?php
									$radio_text = isset( $es_map[$value] ) ? $es_map[$value] : $value;
									echo esc_html( $radio_text );
								?>
							</label>
							<span class="ovabrw-remove-checked">
								<i class="icon_close"></i>
							</span>
							<?php if ( $qty ): ?>
								<div class="radio-item-qty" data-option="<?php echo esc_attr( $value ); ?>">
									<span class="radio-qty">1</span>
									<?php tripgo_text_input([
										'type' 	=> 'text',
										'class' => 'radio-input-qty',
										'name' 	=> $key.'_qty['.$value.']',
										'value' => 1,
										'attrs' => [
											'min' => 1,
											'max' => $opt_qty
										]
									]); ?>
									<div class="ovabrw-radio-icon">
										<i class="arrow_triangle-up" aria-hidden="true"></i>
										<i class="arrow_triangle-down" aria-hidden="true"></i>
									</div>
								</div>
							<?php endif; ?>
						</div>
					<?php endforeach; // END foreach ?>
				</div>
			<?php elseif ( 'checkbox' === $type ): // Checkbox
				// Get option ids
				$opt_ids = tripgo_get_meta_data( 'ova_checkbox_key', $field, [] );
				if ( !tripgo_array_exists( $opt_ids ) ) continue;

				// Get option texts
				$opt_texts = tripgo_get_meta_data( 'ova_checkbox_text', $field, [] );

				// Get option qtys
				$opt_qtys = tripgo_get_meta_data( 'ova_checkbox_qty', $field, [] );
			?>
				<div class="ovabrw-checkbox <?php echo esc_attr( $class ); ?>">
					<?php foreach ( $opt_ids as $k => $opt_id ):
						// Default
						if ( !$default && $required ) $default = $opt_id;

						// Get text
						$text = tripgo_get_meta_data( $k, $opt_texts );
						$text = isset( $es_map[$text] ) ? $es_map[$text] : $text;

						// Get option quantity
						$qty = (int)tripgo_get_meta_data( $k, $opt_qtys );
					?>
						<div class="checkbox-item">
							<?php tripgo_text_input([
								'type' 		=> 'checkbox',
								'id' 		=> 'ovabrw-checkbox-'.esc_attr( $opt_id ).esc_attr( $form ),
								'name' 		=> esc_attr( $key ).'['.$opt_id.']',
								'value' 	=> $opt_id,
								'checked' 	=> $default === $opt_id ? true : false
							]); ?>
							<span class="checkmark"></span>
							<label for="<?php echo 'ovabrw-checkbox-'.esc_attr( $opt_id ).esc_attr( $form ); ?>">
								<?php echo esc_html( $text ); ?>
							</label>
							<?php if ( $qty ): ?>
								<div class="checkbox-item-qty" data-option="<?php echo esc_attr( $opt_id ); ?>">
									<span class="checkbox-qty">1</span>
									<?php tripgo_text_input([
										'type' 	=> 'text',
										'class' => 'checkbox-input-qty',
										'name' 	=> $key.'_qty['.$opt_id.']',
										'value' => 1,
										'attrs' => [
											'min' => 1,
											'max' => $opt_qty
										]
									]); ?>
									<div class="ovabrw-checkbox-icon">
										<i class="arrow_triangle-up" aria-hidden="true"></i>
										<i class="arrow_triangle-down" aria-hidden="true"></i>
									</div>
								</div>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php elseif ( 'file' === $type ): // File
				// File size
				$file_size = tripgo_get_meta_data( 'max_file_size', $field );

				// File mimes
				$mimes = apply_filters( 'ovabrw_file_mimes', [
                    'jpg'   => 'image/jpeg',
                    'jpeg'  => 'image/pjpeg',
                    'png'   => 'image/png',
                    'pdf'   => 'application/pdf',
                    'doc'   => 'application/msword',
                ]);
			?>
				<div class="ovabrw-file <?php echo esc_attr( $class ); ?>">
					<label for="<?php echo 'ovabrw-file-'.esc_attr( $key ).esc_attr( $form ); ?>">
						<span class="ovabrw-file-chosen">
							<?php esc_html_e( 'Elegir archivo', 'tripgo' ); ?>
						</span>
						<span class="ovabrw-file-name"></span>
					</label>
					<?php tripgo_text_input([
						'type' 	=> $type,
						'id' 	=> 'ovabrw-file-'.esc_attr( $key ).esc_attr( $form ),
						'name' 	=> $key,
						'attrs' => [
							'data-max-file-size' 	=> $file_size,
							'data-file-mimes' 		=> json_encode( $mimes ),
						]
					]); ?>
				</div>
			<?php else:
				tripgo_text_input([
		            'type'      	=> $type,
		            'id' 			=> $field_id,
		            'class'     	=> $class,
		            'name'      	=> $key ,
		            'value'     	=> $default,
		            'placeholder' 	=> $placeholder,
		        ]);
			endif; ?>
		</div>
	<?php endforeach;

	// Data cckf
	tripgo_text_input([
		'type' 	=> 'hidden',
		'name' 	=> 'data_custom_ckf',
		'attrs' => [
			'data-ckf' => json_encode( $cckf )
		]
	]);
endif; ?>