<?php if ( !defined( 'ABSPATH' ) ) exit(); ?>

<tr>
    <td width="19%">
        <?php ovabrw_wp_text_input([
            'type'      => 'text',
            'class'     => 'ovabrw-input-required',
            'name'      => $this->get_meta_name( 'schedule_time[thursday][]' ),
            'data_type' => 'timepicker'
        ]); ?>
    </td>
    <td width="22%" class="ovabrw-input-price">
        <?php ovabrw_wp_text_input([
            'type'          => 'text',
            'class'         => 'ovabrw-input-required',
            'name'          => $this->get_meta_name( 'schedule_adult_price[thursday][]' ),
            'placeholder'   => '10',
            'data_type'     => 'price'
        ]); ?>
    </td>
    <?php if ( ovabrw_show_children( $this->get_id() ) ): ?>
        <td width="22%" class="ovabrw-input-price">
            <?php ovabrw_wp_text_input([
                'type'          => 'text',
                'class'         => 'ovabrw-input-required',
                'name'          => $this->get_meta_name( 'schedule_children_price[thursday][]' ),
                'placeholder'   => '10',
                'data_type'     => 'price'
            ]); ?>
        </td>
    <?php endif; ?>
    <?php if ( ovabrw_show_babies( $this->get_id() ) ): ?>
        <td width="22%" class="ovabrw-input-price">
            <?php ovabrw_wp_text_input([
                'type'          => 'text',
                'class'         => 'ovabrw-input-required',
                'name'          => $this->get_meta_name( 'schedule_baby_price[thursday][]' ),
                'placeholder'   => '10',
                'data_type'     => 'price'
            ]); ?>
        </td>
    <?php endif; ?>
    <td width="14%">
        <select name="<?php echo esc_attr( $this->get_meta_name( 'schedule_type[thursday][]' ) ); ?>" class="ovabrw-input-required">
            <option value="person">
                <?php esc_html_e( '/per person', 'ova-brw' ); ?>
            </option>
            <option value="total">
                <?php esc_html_e( '/order', 'ova-brw' ); ?>
            </option>
        </select>
    </td>
    <td width="1%">
        <button class="button ovabrw-remove-timeslot">x</button>
    </td>
</tr>