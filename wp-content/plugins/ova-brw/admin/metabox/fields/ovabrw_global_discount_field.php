<?php if ( !defined( 'ABSPATH' ) ) exit(); ?>

<tr class="row_discount">
    <td width="20%" class="ovabrw-input-price">
    	<?php ovabrw_wp_text_input([
            'type'          => 'text',
            'class'         => 'ovabrw-input-required',
            'name'          => $this->get_meta_name( 'gd_adult_price[]' ),
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
                'placeholder'   => '10',
                'data_type'     => 'price'
            ]); ?>
        </td>
    <?php endif; ?>
    <td width="39%" class="ovabrw-global-discount-duration">
    	<?php ovabrw_wp_text_input([
            'type'          => 'text',
            'class'         => 'ovabrw-input-required ovabrw-global-duration',
            'name'          => $this->get_meta_name( 'gd_duration_min[]' ),
            'placeholder'   => '1',
            'data_type'     => 'number'
        ]); ?>
        <?php ovabrw_wp_text_input([
            'type'          => 'text',
            'class'         => 'ovabrw-input-required ovabrw-global-duration',
            'name'          => $this->get_meta_name( 'gd_duration_max[]' ),
            'placeholder'   => '2',
            'data_type'     => 'number'
        ]); ?>
    </td>
    <td width="1%">
    	<button class="button ovabrw-remove-discount">x</button>
    </td>
</tr>