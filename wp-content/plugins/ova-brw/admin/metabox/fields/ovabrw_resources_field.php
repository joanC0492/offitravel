<?php if ( !defined( 'ABSPATH' ) ) exit(); ?>

<tr>
    <td width="12%">
        <?php ovabrw_wp_text_input([
            'type'          => 'text',
            'class'         => 'ovabrw-input-required',
            'name'          => $this->get_meta_name( 'rs_id[]' ),
            'placeholder'   => esc_html__( 'No space', 'ova-brw' )
        ]); ?>
    </td>
    <td width="20%">
        <?php ovabrw_wp_text_input([
            'type'  => 'text',
            'class' => 'ovabrw-input-required',
            'name'  => $this->get_meta_name( 'rs_name[]' )
        ]); ?>
    </td>
    <td width="20%">
        <?php ovabrw_wp_text_input([
            'type'  => 'text',
            'name'  => $this->get_meta_name( 'rs_description[]' )
        ]); ?>
    </td>
    <td width="9%" class="ovabrw-input-price">
        <?php ovabrw_wp_text_input([
            'type'          => 'text',
            'class'         => 'ovabrw-input-required',
            'name'          => $this->get_meta_name( 'rs_adult_price[]' ),
            'placeholder'   => '10',
            'data_type'     => 'price'
        ]); ?>
    </td>
    <?php if ( ovabrw_show_children( $this->get_id() ) ): ?>
        <td width="9%" class="ovabrw-input-price">
            <?php ovabrw_wp_text_input([
                'type'          => 'text',
                'class'         => 'ovabrw-input-required',
                'name'          => $this->get_meta_name( 'rs_children_price[]' ),
                'placeholder'   => '10',
                'data_type'     => 'price'
            ]); ?>
        </td>
    <?php endif; ?>
    <?php if ( ovabrw_show_babies( $this->get_id() ) ): ?>
        <td width="9%" class="ovabrw-input-price">
            <?php ovabrw_wp_text_input([
                'type'          => 'text',
                'class'         => 'ovabrw-input-required',
                'name'          => $this->get_meta_name( 'rs_baby_price[]' ),
                'placeholder'   => '10',
                'data_type'     => 'price'
            ]); ?>
        </td>
    <?php endif; ?>
    <td width="9%">
        <?php ovabrw_wp_text_input([
            'type'          => 'number',
            'name'          => $this->get_meta_name( 'rs_quantity[]' ),
            'placeholder'   => '10',
            'data_type'     => 'number',
            'attrs'         => [
                'min' => 0
            ]
        ]); ?>
    </td>
    <td width="11%">
        <select name="<?php echo esc_attr( $this->get_meta_name( 'rs_duration_type[]' ) ); ?>" class="ovabrw-input-required">
            <option value="person">
                <?php esc_html_e( '/per person', 'ova-brw' ); ?>
            </option>
            <option value="total">
                <?php esc_html_e( '/order', 'ova-brw' ); ?>
            </option>
        </select>
    </td>
    <td width="1%">
        <button class="button ovabrw-remove-resource">x</button>
    </td>
</tr>