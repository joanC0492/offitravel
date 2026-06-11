<?php if ( !defined( 'ABSPATH' ) ) exit(); ?>
<tr>
    <td width="13%">
        <?php ovabrw_wp_text_input([
            'type'          => 'text',
            'class'         => 'ovabrw-input-required',
            'name'          => $this->get_meta_name( 'service_id[ovabrw_key][]' ),
            'placeholder'   => esc_html__( 'No space', 'ova-brw' )
        ]); ?>
    </td>
    <td width="25%">
        <?php ovabrw_wp_text_input([
            'type'  => 'text',
            'class' => 'ovabrw-input-required',
            'name'  => $this->get_meta_name( 'service_name[ovabrw_key][]' )
        ]); ?>
    </td>
    <td width="13%" class="ovabrw-input-price">
        <?php ovabrw_wp_text_input([
            'type'          => 'text',
            'class'         => 'ovabrw-input-required',
            'name'          => $this->get_meta_name( 'service_adult_price[ovabrw_key][]' ),
            'placeholder'   => '10',
            'data_type'     => 'price'
        ]); ?>
    </td>
    <?php if ( ovabrw_show_children( $this->get_id() ) ): ?>
        <td width="13%" class="ovabrw-input-price">
            <?php ovabrw_wp_text_input([
                'type'          => 'text',
                'class'         => 'ovabrw-input-required',
                'name'          => $this->get_meta_name( 'service_children_price[ovabrw_key][]' ),
                'placeholder'   => '10',
                'data_type'     => 'price'
            ]); ?>
        </td>
    <?php endif; ?>
    <?php if ( ovabrw_show_babies( $this->get_id() ) ): ?>
        <td width="13%" class="ovabrw-input-price">
            <?php ovabrw_wp_text_input([
                'type'          => 'text',
                'class'         => 'ovabrw-input-required',
                'name'          => $this->get_meta_name( 'service_baby_price[ovabrw_key][]' ),
                'placeholder'   => '10',
                'data_type'     => 'price'
            ]); ?>
        </td>
    <?php endif; ?>
    <td width="10%">
        <?php ovabrw_wp_text_input([
            'type'          => 'text',
            'name'          => $this->get_meta_name( 'service_quantity[ovabrw_key][]' ),
            'placeholder'   => '10',
            'data_type'     => 'number',
            'attrs'         => [
                'min' => 0
            ]
        ]); ?>
    </td>
    <td width="12%">
        <select name="<?php echo esc_attr( $this->get_meta_name( 'service_duration_type[ovabrw_key][]' ) ); ?>" class="ovabrw-input-required">
            <option value="person">
                <?php esc_html_e( '/per person', 'ova-brw' ); ?>
            </option>
            <option value="total">
                <?php esc_html_e( '/order', 'ova-brw' ); ?>
            </option>
        </select>
    </td>
    <td width="1%">
        <button class="button ovabrw-remove-service-option">x</button>
    </td>
</tr>