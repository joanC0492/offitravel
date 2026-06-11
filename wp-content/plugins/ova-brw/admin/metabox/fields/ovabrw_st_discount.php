<?php if ( !defined( 'ABSPATH' ) ) exit(); ?>

<tr>						
    <td width="20%" class="ovabrw-input-price">
    	<?php ovabrw_wp_text_input([
			'type' 			=> 'text',
			'class' 		=> 'ovabrw-std-adult-price ovabrw-input-required',
			'name' 			=> $this->get_meta_name( 'st_discount[ovabrw_key][adult_price][]' ),
			'placeholder' 	=> '10',
			'data_type' 	=> 'price'
		]); ?>
    </td>
    <?php if ( ovabrw_show_children( $this->get_id() ) ): ?>
	    <td width="20%" class="ovabrw-input-price">
	    	<?php ovabrw_wp_text_input([
				'type' 			=> 'text',
				'class' 		=> 'ovabrw-std-child-price ovabrw-input-required',
				'name' 			=> $this->get_meta_name( 'st_discount[ovabrw_key][children_price][]' ),
				'placeholder' 	=> '10',
				'data_type' 	=> 'price'
			]); ?>
	    </td>
	<?php endif; ?>
	<?php if ( ovabrw_show_babies( $this->get_id() ) ): ?>
	    <td width="20%" class="ovabrw-input-price">
	    	<?php ovabrw_wp_text_input([
				'type' 			=> 'text',
				'class' 		=> 'ovabrw-std-baby-price ovabrw-input-required',
				'name' 			=> $this->get_meta_name( 'st_discount[ovabrw_key][baby_price][]' ),
				'placeholder' 	=> '10',
				'data_type' 	=> 'price'
			]); ?>
	    </td>
	<?php endif; ?>
    <td width="39%" class="ovabrw-special-discount-duration">
    	<?php ovabrw_wp_text_input([
			'type' 			=> 'text',
			'class' 		=> 'ovabrw-std-min ovabrw-input-required',
			'name' 			=> $this->get_meta_name( 'st_discount[ovabrw_key][min][]' ),
			'placeholder' 	=> '1',
			'data_type' 	=> 'number',
			'attrs' 		=> [
				'min' => 0
			]
		]); ?>
		<?php ovabrw_wp_text_input([
			'type' 			=> 'text',
			'class' 		=> 'ovabrw-std-max ovabrw-input-required',
			'name' 			=> $this->get_meta_name( 'st_discount[ovabrw_key][max][]' ),
			'placeholder' 	=> '2',
			'data_type' 	=> 'number',
			'attrs' 		=> [
				'min' => 0
			]
		]); ?>
    </td>
    <td width="1%">
    	<button class="button ovabrw-remove-special-discount">x</button>
    </td>
</tr>