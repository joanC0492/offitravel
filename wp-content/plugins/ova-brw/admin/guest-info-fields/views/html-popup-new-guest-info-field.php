<?php defined( 'ABSPATH' ) || exit(); ?>

<form action="" method="post" id="ovabrw-popup-guest-info-form" enctype="multipart/form-data">
	<input
		type="hidden"
		name="ovabrw-guest-info-action"
		value="<?php echo esc_attr( $action ); ?>"
	/>
	<button class="popup-guest-info-close">X</button>
	<table width="100%">
		<tbody>
			<?php add_action( OVABRW_PREFIX.'before_popup_add_guest_info', $action, $type, $name ); ?>
			<tr class="type">
				<td class="label ovabrw-required">
					<?php esc_html_e( 'Select type', 'ova-brw' ); ?>
				</td>
				<td>
					<select name="type" id="type" class="ovabrw-input-required">
						<?php foreach ( $this->get_types() as $t => $v ): ?>
							<option value="<?php echo esc_attr( $t ); ?>"<?php ovabrw_selected( $t, $type ); ?>>
								<?php echo esc_html( $v ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<?php if ( 'tel' == $type ): ?>
			<tr class="label">
				<td class="label">
					<?php esc_html_e( 'Pattern', 'ova-brw' ); ?>
				</td>
				<td>
					<input
						type="text"
						name="pattern"
						value="[0-9]{3}-[0-9]{3}-[0-9]{4}"
						placeholder="<?php esc_html_e( '[0-9]{3}-[0-9]{3}-[0-9]{4}', 'ova-brw' ); ?>"
						autocomplete="off"
					/>
					<span>
						<em>
							<strong>
								<?php esc_html_e( 'Example:', 'ova-brw' ); ?>
							</strong>
							<?php esc_html_e( '[0-9]{3}-[0-9]{3}-[0-9]{4}', 'ova-brw' ); ?>
						</em>
					</span>
				</td>
			</tr>
			<?php endif; ?>
			<?php if ( 'file' == $type ): ?>
			<tr class="label">
				<td class="label">
					<?php esc_html_e( 'Allowed file types', 'ova-brw' ); ?>
				</td>
				<td>
					<input
						type="text"
						class="ovabrw-input-required"
						name="accept"
						value=".jpg, .jpeg, .png, .pdf, .doc, .docx"
						placeholder="<?php esc_html_e( '.jpg, .jpeg, .png, .pdf, .doc, .docx', 'ova-brw' ); ?>"
						autocomplete="off"
					/>
					<span>
						<em>
							<strong>
								<?php esc_html_e( 'Example:', 'ova-brw' ); ?>
							</strong>
							<?php esc_html_e( '.jpg, .jpeg, .png, .pdf, .doc, .docx', 'ova-brw' ); ?>
						</em>
					</span>
					<br>
					<span>
						<em>
							<strong>
								<?php esc_html_e( 'Images:', 'ova-brw' ); ?>
							</strong>
							<?php esc_html_e( '.jpg, .jpeg, .png, .gif, .ico, .webp', 'ova-brw' ); ?>
						</em>
					</span>
					<br>
					<span>
						<em>
							<strong>
								<?php esc_html_e( 'Documents:', 'ova-brw' ); ?>
							</strong>
							<?php esc_html_e( '.pdf, .doc, .docx, .ppt, .pptx, .pps, .ppsx, .odt, .xls, .xlsx, .PSD, .XML', 'ova-brw' ); ?>
						</em>
					</span>
					<br>
					<span>
						<em>
							<strong>
								<?php esc_html_e( 'Audio:', 'ova-brw' ); ?>
							</strong>
							<?php esc_html_e( '.mp3, .m4a, .ogg, .wav', 'ova-brw' ); ?>
						</em>
					</span>
					<br>
					<span>
						<em>
							<strong>
								<?php esc_html_e( 'Video:', 'ova-brw' ); ?>
							</strong>
							<?php esc_html_e( '.mp4, .m4v, .mov, .wmv, .avi, .mpg, .ogv, .3gp, .3g2', 'ova-brw' ); ?>
						</em>
					</span>
				</td>
			</tr>
			<tr class="label">
				<td class="label">
					<?php esc_html_e( 'Max size', 'ova-brw' ); ?>
				</td>
				<td>
					<input
						type="text"
						name="max_size"
						placeholder="10"
						autocomplete="off"
					/>
					<span>
						<em>
							<?php esc_html_e( 'Unit: MB (megabyte)', 'ova-brw' ); ?>
						</em>
					</span>
				</td>
			</tr>
			<?php endif; ?>
			<tr class="name">
				<td class="label ovabrw-required">
					<?php esc_html_e( 'Name', 'ova-brw' ); ?>
				</td>
				<td>
					<input
						type="text"
						class="ovabrw-input-required"
						name="name"
						value="<?php echo esc_attr( $name ); ?>"
						placeholder="<?php esc_html_e( 'enter name', 'ova-brw' ); ?>"
						autocomplete="off"
					/>
					<span>
						<em>
							<?php esc_html_e( 'Unique, only lowercase, not space', 'ova-brw' ); ?>
						</em>
					</span>
				</td>
			</tr>
			<tr class="label">
				<td class="label ovabrw-required">
					<?php esc_html_e( 'Label', 'ova-brw' ); ?>
				</td>
				<td>
					<input
						type="text"
						class="ovabrw-input-required"
						name="label"
						autocomplete="off"
						placeholder="<?php esc_html_e( 'enter label', 'ova-brw' ); ?>"
					/>
				</td>
			</tr>
			<tr class="label">
				<td class="label">
					<?php esc_html_e( 'Description', 'ova-brw' ); ?>
				</td>
				<td>
					<textarea name="description" placeholder="<?php esc_html_e( 'enter your text here...', 'ova-brw' ); ?>"></textarea>
				</td>
			</tr>
			<?php if ( !in_array( $type, ['radio', 'checkbox', 'file'] ) ): ?>
			<tr class="label">
				<td class="label">
					<?php esc_html_e( 'Placeholder', 'ova-brw' ); ?>
				</td>
				<td>
					<input
						type="text"
						name="placeholder"
						placeholder="<?php esc_html_e( 'enter placeholder', 'ova-brw' ); ?>"
						autocomplete="off"
					/>
				</td>
			</tr>
			<?php endif; ?>
			<?php if ( in_array( $type, ['radio', 'checkbox', 'select'] ) ): // Radio ?>
				<tr class="radio-options ovabrw-guest-info-options">
					<td class="label">
						<?php esc_html_e( 'Options(*)', 'ova-brw' ); ?>
					</td>
					<td>
						<table class="widefat" width="100%">
							<thead>
								<tr>
									<th class="ovabrw-required" width="45%">
										<?php esc_html_e( 'Option ID', 'ova-brw' ); ?>
									</th>
									<th class="ovabrw-required" width="45%">
										<?php esc_html_e( 'Name', 'ova-brw' ); ?>
									</th>
									<th width="10%">
										<?php esc_html_e( 'Actions', 'ova-brw' ); ?>
									</th>
								</tr>
							</thead>
							<tbody class="ovabrw-guest-info-option-sortable">
								<tr>
									<td>
										<input
											type="text"
											class="ovabrw-input-required"
											name="option_ids[]"
											value="option_1"
											autocomplete="off"
											placeholder="<?php esc_html_e( 'text', 'ova-brw' ); ?>"
										/>
									</td>
									<td>
										<input
											type="text"
											class="ovabrw-input-required"
											name="option_names[]"
											autocomplete="off"
											placeholder="<?php esc_html_e( 'text', 'ova-brw' ); ?>"
										/>
									</td>
									<td>
										<span class="btn btn-add-new" title="<?php esc_attr_e( 'Add', 'ova-brw' ); ?>">+</span>
										<span class="btn btn-add-remove" title="<?php esc_attr_e( 'Remove', 'ova-brw' ); ?>">x</span>
										<span class="dashicons dashicons-menu-alt3" title="<?php esc_attr_e( 'Grab & Drop', 'ova-brw' ); ?>"></span>
									</td>
								</tr>
							</tbody>
							<tfoot>
								<tr>
									<th colspan="4">
										<button class="button ovabrw-guest-info-add-option">
											<?php esc_html_e( 'Add option' ); ?>
										</button>
									</th>
								</tr>
							</tfoot>
						</table>
						<span>
							<em>
								<strong>
									<?php esc_html_e( 'Name:', 'ova-brw' ); ?>
								</strong>
								<?php esc_html_e( 'Unique, only lowercase, not space', 'ova-brw' ); ?>
							</em>
						</span>
					</td>
				</tr>
			<?php endif; // End radio ?>
			<?php if ( 'file' != $type ): ?>
				<tr class="default">
					<td class="label">
						<?php esc_html_e( 'Default', 'ova-brw' ); ?>
					</td>
					<td>
						<?php if ( 'date' == $type ): ?>
							<input
								type="text"
								class="ovabrw-datepicker"
								id="<?php echo esc_attr( ovabrw_unique_id( 'guest_info_date' ) ); ?>"
								name="default"
								placeholder="<?php echo esc_attr( ovabrw_get_placeholder_date() ); ?>"
							/>
							<input
								type="hidden"
								name="ovabrw-datepicker-options"
								value="<?php echo esc_attr( wp_json_encode( ovabrw_admin_datepicker_options() ) ); ?>"
							/>
						<?php elseif ( 'number' == $type ): ?>
							<input
								type="number"
								name="default"
								placeholder="10"
								step="any"
								autocomplete="off"
							/>
						<?php elseif ( 'tel' == $type ): ?>
							<input
								type="text"
								name="default"
								placeholder="<?php esc_html_e( 'enter phone number', 'ova-brw' ); ?>"
								autocomplete="off"
							/>
						<?php elseif ( 'email' == $type ): ?>
							<input
								type="email"
								name="default"
								placeholder="<?php esc_html_e( 'your_email@gmail.com', 'ova-brw' ); ?>"
								autocomplete="off"
							/>
						<?php else: ?>
							<input
								type="text"
								name="default"
								placeholder="<?php esc_html_e( 'enter text value', 'ova-brw' ); ?>"
								autocomplete="off"
							/>
						<?php endif; ?>
					</td>
				</tr>
			<?php endif; ?>
			<?php if ( 'date' == $type ): ?>
				<tr class="min">
					<td class="label">
						<?php esc_html_e( 'Min date', 'ova-brw' ); ?>
					</td>
					<td>
						<input
							type="text"
							class="ovabrw-datepicker"
							id="<?php echo esc_attr( ovabrw_unique_id( 'guest_info_min_date' ) ); ?>"
							name="min"
							placeholder="<?php echo esc_attr( ovabrw_get_placeholder_date() ); ?>"
						/>
					</td>
				</tr>
				<tr class="max">
					<td class="label">
						<?php esc_html_e( 'Max date', 'ova-brw' ); ?>
					</td>
					<td>
						<input
							type="text"
							class="ovabrw-datepicker"
							id="<?php echo esc_attr( ovabrw_unique_id( 'guest_info_max_date' ) ); ?>"
							name="max"
							placeholder="<?php echo esc_attr( ovabrw_get_placeholder_date() ); ?>"
						/>
					</td>
				</tr>
			<?php endif; ?>
			<?php if ( 'number' == $type ): ?>
				<tr class="min">
					<td class="label">
						<?php esc_html_e( 'Min', 'ova-brw' ); ?>
					</td>
					<td>
						<input
							type="number"
							name="min"
							placeholder="1"
							step="any"
							autocomplete="off"
						/>
					</td>
				</tr>
				<tr class="max">
					<td class="label">
						<?php esc_html_e( 'Max', 'ova-brw' ); ?>
					</td>
					<td>
						<input
							type="number"
							name="max"
							placeholder="100000"
							step="any"
							autocomplete="off"
						/>
					</td>
				</tr>
			<?php endif; ?>
			<tr class="class">
				<td class="label">
					<?php esc_html_e( 'Class', 'ova-brw' ); ?>
				</td>
				<td>
					<input
						type="text"
						name="class"
						placeholder="<?php esc_html_e( 'enter class name', 'ova-brw' ); ?>"
						autocomplete="off"
					/>
				</td>
			</tr>
			<?php add_action( OVABRW_PREFIX.'after_popup_add_guest_info', $action, $type, $name ); ?>
			<tr class="status">
				<td class="label"></td>
				<td>
					<label>
						<input
							type="checkbox"
							name="required"
							value="on"
						/>
						<?php esc_html_e( 'Required', 'ova-brw' ); ?>
					</label>
					<label>
						<input
							type="checkbox"
							name="enable"
							value="on"
							checked
						/>
						<?php esc_html_e( 'Enable', 'ova-brw' ); ?>
					</label>
				</td>
			</tr>
		</tbody>
	</table>
	<div class="ovabrw-guest-info-submit">
		<button type="submit" class="button button-primary">
			<?php esc_html_e( 'Save', 'ova-brw' ); ?>
		</button>
		<div class="ovabrw-loading">
			<span class="dashicons dashicons-update-alt"></span>
		</div>
	</div>
</form>