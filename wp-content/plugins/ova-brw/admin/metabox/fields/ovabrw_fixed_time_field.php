<?php if ( !defined( 'ABSPATH' ) ) exit(); ?>

<tr>
    <td width="49.5%">
        <?php ovabrw_wp_text_input([
            'type'      => 'text',
            'id'        => 'fixedTimeStart',
            'class'     => 'ovabrw-input-required start-date',
            'name'      => $this->get_meta_name( 'fixed_time_check_in[]' ),
            'data_type' => 'datepicker'
        ]); ?>
    </td>
    <td width="49.5%">
        <?php ovabrw_wp_text_input([
            'type'      => 'text',
            'id'        => 'fixedTimeEnd',
            'class'     => 'ovabrw-input-required end-date',
            'name'      => $this->get_meta_name( 'fixed_time_check_out[]' ),
            'data_type' => 'datepicker'
        ]); ?>
    </td>
    <td width="1%">
        <button class="button ovabrw-remove-fixed-time">x</button>
    </td>
</tr>