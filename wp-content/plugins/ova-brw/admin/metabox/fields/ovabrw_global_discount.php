<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get adult prices
$adult_prices = $this->get_meta_value( 'gd_adult_price' );

// Get child prices
$child_prices = $this->get_meta_value( 'gd_children_price' );

// Get baby prices
$baby_prices = $this->get_meta_value( 'gd_baby_price' );

// Get discount form
$discount_from = $this->get_meta_value( 'gd_duration_min' );

// Get discount to
$discount_to = $this->get_meta_value( 'gd_duration_max' );

?>

<div class="ovabrw-global-discount">
	<table class="widefat">
		<thead>
			<tr>
				<th class="ovabrw-required">
					<?php esc_html_e( 'Adult price', 'ova-brw' ); ?>
				</th>
				<?php if ( ovabrw_show_children( $this->get_id() ) ): ?>
					<th class="ovabrw-required">
						<?php esc_html_e( 'Child price', 'ova-brw' ); ?>
					</th>
				<?php endif; ?>
				<?php if ( ovabrw_show_babies( $this->get_id() ) ): ?>
					<th class="ovabrw-required">
						<?php esc_html_e( 'Baby price', 'ova-brw' ); ?>
					</th>
				<?php endif; ?>
				<th class="ovabrw-required">
					<?php esc_html_e( 'Min - Max: Guests', 'ova-brw' ); ?>
				</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( ovabrw_array_exists( $adult_prices ) ):
				foreach ( $adult_prices as $i => $adult_price ):
					// Child price
					$child_price = ovabrw_get_meta_data( $i, $child_prices );

					// Baby price
					$baby_price = ovabrw_get_meta_data( $i, $baby_prices );

					// From
					$from = ovabrw_get_meta_data( $i, $discount_from );

					// To
					$to = ovabrw_get_meta_data( $i, $discount_to );
				?>
					<tr class="row_discount">
					    <td width="20%" class="ovabrw-input-price">
					    	<?php ovabrw_wp_text_input([
					            'type'          => 'text',
					            'class'         => 'ovabrw-input-required',
					            'name'          => $this->get_meta_name( 'gd_adult_price[]' ),
					            'value' 		=> $adult_price,
					            'placeholder'   => '10',
					            'data_type'     => 'price'
					        ]); ?>
					    </td>
					    <?php if ( ovabrw_show_children( $this->get_id() ) ): ?>
						    <td width="20%" class="ovabrw-input-price">
						    	<?php ovabrw_wp_text_input([
						            'type'          => 'text',
						            'class'         => 'ovabrw-input-required',
						            'name'          => $this->get_meta_name( 'gd_children_price[]' ),
						            'value' 		=> $child_price,
						            'placeholder'   => '10',
						            'data_type'     => 'price'
						        ]); ?>
						    </td>
						<?php endif; ?>
						<?php if ( ovabrw_show_babies( $this->get_id() ) ): ?>
						    <td width="20%" class="ovabrw-input-price">
						    	<?php ovabrw_wp_text_input([
						            'type'          => 'text',
						            'class'         => 'ovabrw-input-required',
						            'name'          => $this->get_meta_name( 'gd_baby_price[]' ),
						            'value' 		=> $baby_price,
						            'placeholder'   => '10',
						            'data_type'     => 'price'
						        ]); ?>
						    </td>
						<?php endif; ?>
					    <td width="39%" class="ovabrw-global-discount-duration">
					    	<?php ovabrw_wp_text_input([
					            'type'          => 'text',
					            'class'         => 'ovabrw-input-required',
					            'name'          => $this->get_meta_name( 'gd_duration_min[]' ),
					            'value' 		=> $from,
					            'placeholder'   => '1',
					            'data_type'     => 'number'
					        ]); ?>
					        <?php ovabrw_wp_text_input([
					            'type'          => 'text',
					            'class'         => 'ovabrw-input-required',
					            'name'          => $this->get_meta_name( 'gd_duration_max[]' ),
					            'value' 		=> $to,
					            'placeholder'   => '2',
					            'data_type'     => 'number'
					        ]); ?>
					    </td>
					    <td width="1%">
					    	<button class="button ovabrw-remove-discount">x</button>
					    </td>
					</tr>
				<?php endforeach;
			endif; ?>
		</tbody>
		<tfoot>
			<tr>
				<th colspan="5">
					<button class="button ovabrw-add-discount" data-row="<?php
						ob_start();
						include( OVABRW_PLUGIN_PATH . 'admin/metabox/fields/ovabrw_global_discount_field.php' );
						echo esc_attr( ob_get_clean() ); ?>">
						<?php esc_html_e( 'Add discount', 'ova-brw' ); ?>
					</button>
				</th>
			</tr>
		</tfoot>
	</table>
</div>