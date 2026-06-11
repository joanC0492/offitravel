<?php if ( !defined( 'ABSPATH' ) ) exit(); ?>

<tr class="ovabrw-special-time-row" data-pos="">
    <td width="9%" class="ovabrw-input-price">
    	<?php ovabrw_wp_text_input([
    		'type' 			=> 'text',
    		'class' 		=> 'ovabrw-input-required',
    		'name' 			=> $this->get_meta_name( 'st_adult_price[]' ),
    		'placeholder' 	=> '10',
    		'data_type' 	=> 'price'
    	]); ?>
    </td>
    <?php if ( ovabrw_show_children( $this->get_id() ) ): ?>
	    <td width="9%" class="ovabrw-input-price">
	    	<?php ovabrw_wp_text_input([
	    		'type' 			=> 'text',
	    		'class' 		=> 'ovabrw-input-required',
	    		'name' 			=> $this->get_meta_name( 'st_children_price[]' ),
	    		'placeholder' 	=> '10',
	    		'data_type' 	=> 'price'
	    	]); ?>
	    </td>
	<?php endif; ?>
	<?php if ( ovabrw_show_babies( $this->get_id() ) ): ?>
	    <td width="9%" class="ovabrw-input-price">
	    	<?php ovabrw_wp_text_input([
	    		'type' 			=> 'text',
	    		'class' 		=> 'ovabrw-input-required',
	    		'name' 			=> $this->get_meta_name( 'st_baby_price[]' ),
	    		'placeholder' 	=> '10',
	    		'data_type' 	=> 'price'
	    	]); ?>
	    </td>
	<?php endif; ?>
    <td width="12.5%">
    	<?php ovabrw_wp_text_input([
			'type' 		=> 'text',
			'id' 		=> 'specialStartUniqueID',
			'class' 	=> 'start-date ovabrw-input-required',
			'name' 		=> $this->get_meta_name( 'st_startdate[]' ),
			'data_type' => 'datepicker'
		]); ?>
    </td>
    <td width="12.5%">
    	<?php ovabrw_wp_text_input([
			'type' 		=> 'text',
			'id' 		=> 'specialEndUniqueID',
			'class' 	=> 'end-date ovabrw-input-required',
			'name' 		=> $this->get_meta_name( 'st_enddate[]' ),
			'data_type' => 'datepicker'
		]); ?>
    </td>
    <td width="39%">
    	<table width="100%">
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
			<tbody></tbody>
			<tfoot>
				<tr>
					<th colspan="5">
						<button class="button ovabrw-add-special-discount">
							<?php esc_html_e( 'Add discount', 'ova-brw' ); ?>
						</button>
					</th>
				</tr>
			</tfoot>
      	</table>
    </td>
    <td width="1%">
    	<button class="button ovabrw-remove-special-time">x</button>
    </td>
</tr>