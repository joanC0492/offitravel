<?php if ( !defined( 'ABSPATH' ) ) exit(); ?>

<tr>
    <td width="49.5%">
        <?php ovabrw_wp_text_input([
            'type'      => 'text',
            'id'        => 'disabledFromUniqueID',
            'class'     => 'ovabrw-input-required start-date',
            'name'      => $this->get_meta_name( 'untime_startdate[]' ),
            'data_type' => 'datepicker'
        ]); ?>
    </td>
    <td width="49.5%">
        <?php ovabrw_wp_text_input([
            'type'      => 'text',
            'id'        => 'disabledToUniqueID',
            'class'     => 'ovabrw-input-required end-date',
            'name'      => $this->get_meta_name( 'untime_enddate[]' ),
            'data_type' => 'datepicker'
        ]); ?>
    </td>
    <td width="1%">
        <button class="button ovabrw-remove-unavailable-time">x</button>
    </td>
</tr>