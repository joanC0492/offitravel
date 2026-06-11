<?php defined( 'ABSPATH' ) || exit();

/**
 * Display Custom Checkout Fields
 */
if ( !function_exists( 'ovabrw_custom_checkout_field' ) ) {
    function ovabrw_custom_checkout_field() {
        // Get custom checkout fields
        $cckf = ovabrw_recursive_replace( '\\', '', ovabrw_get_option( 'booking_form', [] ) );

        // $_POST
        $_POST = ovabrw_recursive_replace( '\\', '', $_POST );

        // Action popup
        $action_popup = sanitize_text_field( ovabrw_get_meta_data( 'ova_action', $_POST ) );

        // Get name
        $name = sanitize_title( sanitize_text_field( ovabrw_get_meta_data( 'name', $_POST ) ) );
        if ( $name ) $name = str_replace( '-', '_', $name );
        
        //update popup
        if ( $action_popup && $name && ovabrw_array_exists( $_POST ) ) {
            $cckf[$name] = [
                'type'          => sanitize_text_field( ovabrw_get_meta_data( 'type', $_POST ) ),
                'label'         => sanitize_text_field( ovabrw_get_meta_data( 'label', $_POST ) ),
                'default'       => sanitize_text_field( ovabrw_get_meta_data( 'default', $_POST ) ),
                'placeholder'   => sanitize_text_field( ovabrw_get_meta_data( 'placeholder', $_POST ) ),
                'class'         => sanitize_text_field( ovabrw_get_meta_data( 'class', $_POST ) ),
                'required'      => sanitize_text_field( ovabrw_get_meta_data( 'required', $_POST ) ),
                'enabled'       => sanitize_text_field( ovabrw_get_meta_data( 'enabled', $_POST ) ),
                'show_in_email' => sanitize_text_field( ovabrw_get_meta_data( 'show_in_email', $_POST ) ),
                'show_in_order' => sanitize_text_field( ovabrw_get_meta_data( 'show_in_order', $_POST ) )
            ];

            // Select
            if ( 'select' === ovabrw_get_meta_data( 'type', $_POST ) ) {
                // Option keys
                $cckf[$name]['ova_options_key'] = ovabrw_get_meta_data( 'ova_options_key', $_POST );

                // Option texts
                $cckf[$name]['ova_options_text'] = ovabrw_get_meta_data( 'ova_options_text', $_POST );

                // Option prices
                $cckf[$name]['ova_options_price'] = ovabrw_get_meta_data( 'ova_options_price', $_POST );

                // Option qtys
                $cckf[$name]['ova_options_qty'] = ovabrw_get_meta_data( 'ova_options_qty', $_POST );

                // Placeholder
                $cckf[$name]['placeholder'] = '';
            } elseif ( 'radio' === ovabrw_get_meta_data( 'type', $_POST ) ) { // Radio
                // Radio values
                $cckf[$name]['ova_radio_values'] = ovabrw_get_meta_data( 'ova_radio_values', $_POST );

                // Radio prices
                $cckf[$name]['ova_radio_prices'] = ovabrw_get_meta_data( 'ova_radio_prices', $_POST );

                // Option qtys
                $cckf[$name]['ova_radio_qtys'] = ovabrw_get_meta_data( 'ova_radio_qtys', $_POST );

                // Placeholder
                $cckf[$name]['placeholder'] = '';
            } elseif ( 'checkbox' === ovabrw_get_meta_data( 'type', $_POST ) ) { // Checkbox
                // Checkbox keys
                $cckf[$name]['ova_checkbox_key'] = ovabrw_get_meta_data( 'ova_checkbox_key', $_POST );

                // Checkbox texts
                $cckf[$name]['ova_checkbox_text'] = ovabrw_get_meta_data( 'ova_checkbox_text', $_POST );

                // Checkbox prices
                $cckf[$name]['ova_checkbox_price'] = ovabrw_get_meta_data( 'ova_checkbox_price', $_POST );

                // Option qtys
                $cckf[$name]['ova_checkbox_qty'] = ovabrw_get_meta_data( 'ova_checkbox_qty', $_POST );

                // Placeholder
                $cckf[$name]['placeholder'] = '';
            } elseif ( 'file' === ovabrw_get_meta_data( 'type', $_POST ) ) { // File
                // Max file size
                $cckf[$name]['max_file_size'] = ovabrw_get_meta_data( 'max_file_size', $_POST );

                // Placeholder
                $cckf[$name]['placeholder'] = '';

                // Default
                $cckf[$name]['default'] = '';
            }

            // Add new
            if ( 'new' === $action_popup ) {
                // Update cckf
                update_option( 'ovabrw_booking_form', $cckf );
            } elseif ( 'edit' === $action_popup ) {
                $old_name = ovabrw_get_meta_data( 'ova_old_name', $_POST );
                if ( $old_name && array_key_exists( $old_name, $cckf ) && $old_name != $name  ) {
                    unset( $cckf[$old_name] );
                }
                if ( !$name ) unset( $cckf[$name] );

                // Update cckf
                update_option( 'ovabrw_booking_form', $cckf );
            }
        }

        // Action update
        $action_update = sanitize_text_field( ovabrw_get_meta_data( 'ovabrw_update_table', $_POST ) );
        if ( 'update_table' === $action_update ) {
            // Select fileds
            $select_field = ovabrw_get_meta_data( 'select_field', $_POST, [] );
            if ( ovabrw_array_exists( $select_field ) ) {
                foreach ( $select_field as $field ) {
                    if ( $field && array_key_exists( $field, $cckf ) ) {
                        if ( 'remove' === ovabrw_get_meta_data( 'remove', $_POST ) ) {
                            unset( $cckf[$field] );
                        } elseif ( 'enable' === ovabrw_get_meta_data( 'enable', $_POST ) ) {
                            $cckf[$field]['enabled'] = 'on';
                        } elseif ( 'disable' === ovabrw_get_meta_data( 'disable', $_POST ) ) {
                            $cckf[$field]['enabled'] = '';
                        }
                    }
                }
            }

            // Update cckf
            update_option('ovabrw_booking_form', $cckf);
        } ?>
        <div class="wrap">
            <div class="ova-list-checkout-field">
                <form method="post" id="ova_update_form" action="">
                    <input type="hidden" name="ovabrw_update_table" value="update_table" >
                    <table cellspacing="0" cellpadding="10px">
                        <thead>
                            <th colspan="6">
                                <button type="button" class="button button-primary" id="ovabrw_openform">
                                    + <?php esc_html_e( 'Add field', 'ova-brw' ); ?>
                                </button>
                                <input type="submit" class="button" name="remove" value="remove">
                                <input type="submit" class="button" name="enable" value="enable">
                                <input type="submit" class="button" name="disable" value="disable">
                            </th>
                            <tr>
                                <th class="check-column">
                                    <input
                                        type="checkbox"
                                        id="ovabrw_select_all_field"
                                        style="margin:0px 4px -1px -1px;"
                                    />
                                </th>
                                <th class="name">
                                    <?php esc_html_e( 'Slug', 'ova-brw' ); ?>
                                </th>
                                <th class="id">
                                    <?php esc_html_e( 'Type', 'ova-brw' ); ?>
                                </th>
                                <th>
                                    <?php esc_html_e( 'Label', 'ova-brw' ); ?>
                                </th>
                                <th>
                                    <?php esc_html_e( 'Placeholder', 'ova-brw' ); ?>
                                </th>
                                <th>
                                    <?php esc_html_e( 'Required', 'ova-brw' ); ?>
                                </th>
                                <th>
                                    <?php esc_html_e( 'Enabled', 'ova-brw' ); ?>
                                </th>    
                                <th>
                                    <?php esc_html_e( 'Edit', 'ova-brw' ); ?>
                                </th>   
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ( ovabrw_array_exists( $cckf ) ):
                            foreach ( $cckf as $name => $field ):
                                // Get type
                                $type = ovabrw_get_meta_data( 'type', $field );

                                // Get label
                                $label = ovabrw_get_meta_data( 'label', $field );

                                // Get max file size
                                $max_file_size = ovabrw_get_meta_data( 'max_file_size', $field );

                                // Get placeholder
                                $placeholder = ovabrw_get_meta_data( 'placeholder', $field );

                                // Default
                                $default = ovabrw_get_meta_data( 'default', $field );

                                // Get class
                                $class = ovabrw_get_meta_data( 'class', $field );

                                // Get required
                                $required = ovabrw_get_meta_data( 'required', $field );

                                // Get enabled
                                $enabled = ovabrw_get_meta_data( 'enabled', $field );

                                // Get option keys
                                $opt_keys = ovabrw_get_meta_data( 'ova_options_key', $field, [] );

                                // Get option texts
                                $opt_texts = ovabrw_get_meta_data( 'ova_options_text', $field, [] );

                                // Get option prices
                                $opt_prices = ovabrw_get_meta_data( 'ova_options_price', $field, [] );

                                // Get option qtys
                                $opt_qtys = ovabrw_get_meta_data( 'ova_options_qty', $field, [] );

                                // Get radio values
                                $radio_values = ovabrw_get_meta_data( 'ova_radio_values', $field, [] );

                                // Get radio prices
                                $radio_prices = ovabrw_get_meta_data( 'ova_radio_prices', $field, [] );

                                // Get radio qtys
                                $radio_qtys = ovabrw_get_meta_data( 'ova_radio_qtys', $field, [] );

                                // Get checkbox keys
                                $checkbox_keys = ovabrw_get_meta_data( 'ova_checkbox_key', $field, [] );

                                // Get checkbox texts
                                $checkbox_texts = ovabrw_get_meta_data( 'ova_checkbox_text', $field, [] );

                                // Get checkbox prices
                                $checkbox_prices = ovabrw_get_meta_data( 'ova_checkbox_price', $field, [] );

                                // Get checkbox qtys
                                $checkbox_qtys = ovabrw_get_meta_data( 'ova_checkbox_qty', $field, [] );

                                // Required status
                                $required_status = $required ? '<span class="dashicons dashicons-yes tips" data-tip="Yes"></span>' : '-';

                                // Enabled status
                                $enabled_status = $enabled ? '<span class="dashicons dashicons-yes tips" data-tip="Yes"></span>' : '-';

                                // Class disabled
                                $class_disable = !$enabled ? 'ova-disable' : '';

                                // Disable button
                                $disable_button = !$enabled ? 'disabled' : '';

                                // Value enabled
                                $value_enabled = 'on' === $enabled ? $name : '';
                                
                                $data_edit = json_encode([
                                    'name'                  => $name,
                                    'type'                  => $type,
                                    'max_file_size'         => $max_file_size,
                                    'label'                 => $label,
                                    'placeholder'           => $placeholder,
                                    'default'               => $default,
                                    'class'                 => $class,
                                    'ova_options_key'       => $opt_keys,
                                    'ova_options_text'      => $opt_texts,
                                    'ova_options_price'     => $opt_prices,
                                    'ova_options_qty'       => $opt_qtys,
                                    'ova_radio_values'      => $radio_values,
                                    'ova_radio_prices'      => $radio_prices,
                                    'ova_radio_qtys'        => $radio_qtys,
                                    'ova_checkbox_key'      => $checkbox_keys,
                                    'ova_checkbox_text'     => $checkbox_texts,
                                    'ova_checkbox_price'    => $checkbox_prices,
                                    'ova_checkbox_qty'      => $checkbox_qtys,
                                    'required'              => $required,
                                    'enabled'               => $enabled
                                ]);
                            ?>
                                <tr class="<?php echo esc_attr( $class_disable ); ?>">
                                    <input type="hidden" name="remove_field[]" value="">
                                    <input type="hidden" name="enable_field[]" value="<?php echo esc_attr( $value_enabled ); ?>">
                                    <td class="ova-checkbox">
                                        <input
                                            type="checkbox"
                                            name="select_field[]"
                                            value="<?php echo esc_attr( $name ); ?>"
                                        />
                                    </td>
                                    <td class="ova-name">
                                        <?php echo esc_html( $name ); ?>
                                    </td>
                                    <td class="ova-type">
                                        <?php echo esc_html( $type ); ?>
                                    </td>
                                    <td class="ova-label">
                                        <?php echo esc_html( $label ); ?>
                                    </td>
                                    <td class="ova-placeholder">
                                        <?php echo esc_html( $placeholder ); ?>
                                    </td>
                                    <td class="ova-require status">
                                        <?php echo wp_kses_post( $required_status ); ?>
                                    </td>
                                    <td class="ova-enable status">
                                        <?php echo wp_kses_post( $enabled_status ); ?>
                                    </td>
                                    <td class="ova-edit edit">
                                    <button type="button" class="button ova-button ovabrw_edit_field_form" data-data_edit="<?php echo esc_attr( $data_edit ); ?>" <?php echo esc_attr( $disable_button ); ?>>
                                        <?php esc_html_e( 'Edit', 'ova-brw' ); ?>
                                    </button>
                                    </td>
                                </tr>
                            <?php endforeach;
                        endif; ?>           
                        </tbody>
                    </table>
                </form>
            </div>
            <div class="ova-wrap-popup-ckf">
                <div id="ova_new_field_form" title="<?php esc_html_e( 'New field', 'ova-brw' ); ?>" class="ova-popup-wrapper">
                    <a href="javascript:void(0)" id="ovabrw_close_popup" class="close_popup">X</a>
                    <?php ova_output_popup_form_fields( 'new', $cckf ); ?>
                </div>
            </div>
        </div>
        <?php
    }
}
    
/**
 * Output popup form fields
 */
if ( !function_exists( 'ova_output_popup_form_fields' ) ) {
    function ova_output_popup_form_fields( $form_type = '', $cckf = [] ) { ?>
        <form id="ova_popup_field_form" action="<?php echo esc_url( get_admin_url( null, 'admin.php?page=ovabrw-custom-checkout-field' ) ); ?>" method="POST">
            <input
                type="hidden"
                name="ova_action"
                value="<?php echo esc_attr( $form_type ); ?>"
            />
            <input
                type="hidden"
                name="ova_old_name"
                value=""
            />
            <table width="100%">
                <tr>                
                    <td colspan="2" class="err_msgs"></td>
                </tr>
                <tr class="ova-row-type">
                    <td class="label">
                        <?php esc_html_e( 'Type', 'ova-brw' ); ?>
                    </td>
                    <td>
                        <select name="type" id="ova_type">
                            <option value="text">
                                <?php esc_html_e( 'Text', 'ova-brw' ); ?>
                            </option>
                            <option value="password">
                                <?php esc_html_e( 'Password', 'ova-brw' ); ?>
                            </option>
                            <option value="email">
                                <?php esc_html_e( 'Email', 'ova-brw' ); ?>
                            </option>
                            <option value="tel">
                                <?php esc_html_e( 'Phone', 'ova-brw' ); ?>
                            </option>
                            <option value="textarea">
                                <?php esc_html_e( 'Textarea', 'ova-brw' ); ?>
                            </option>
                            <option value="select">
                                <?php esc_html_e( 'Select', 'ova-brw' ); ?>
                            </option>
                            <option value="radio">
                                <?php esc_html_e( 'Radio', 'ova-brw' ); ?>
                            </option>
                            <option value="checkbox">
                                <?php esc_html_e( 'Checkbox', 'ova-brw' ); ?>
                            </option>
                            <option value="file">
                                <?php esc_html_e( 'File', 'ova-brw' ); ?>
                            </option>
                        </select>
                        <span class="formats-file-size">
                            <?php esc_html_e( 'Formats: .jpg, .jpeg, .png, .pdf, .doc', 'ova-brw' ); ?>
                        </span>
                    </td>
                </tr>
                <tr class="row-options">
                    <td width="30%" class="label" valign="top">
                        <?php esc_html_e( 'Options', 'ova-brw' ); ?>
                    </td>
                    <td>
                        <table width="100%" border="0" cellpadding="0" cellspacing="0" class="ova-sub-table">
                            <thead>
                                <tr>
                                    <th width="29%">
                                        <?php esc_html_e( 'ID', 'ova-brw' ); ?>
                                    </th>
                                    <th width="29%">
                                        <?php esc_html_e( 'Label', 'ova-brw' ); ?>
                                    </th>
                                    <th width="20%">
                                        <?php esc_html_e( 'Price', 'ova-brw' ); ?>
                                    </th>
                                    <th width="20%">
                                        <?php esc_html_e( 'Quantity' ); ?>
                                    </th>
                                    <th width="1%"></th>
                                    <th width="1%"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <input
                                            type="text"
                                            name="ova_options_key[]"
                                            placeholder="..."
                                        />
                                    </td>
                                    <td>
                                        <input
                                            type="text"
                                            name="ova_options_text[]"
                                            placeholder="<?php esc_attr_e( 'label', 'ova-brw' ); ?>"
                                        />
                                    </td>
                                    <td>
                                        <input
                                            type="text"
                                            name="ova_options_price[]"
                                            placeholder="<?php esc_attr_e( 'price', 'ova-brw' ); ?>"
                                        />
                                    </td>
                                    <td>
                                        <input
                                            type="number"
                                            name="ova_options_qty[]"
                                            placeholder="<?php esc_attr_e( 'number', 'ova-brw' ); ?>"
                                            min="0"
                                        />
                                    </td>
                                    <td class="ova-box">
                                        <a href="javascript:void(0)" class="ovabrw_addfield btn btn-blue" title="<?php esc_attr_e( 'Add new option', 'ova-brw' ); ?>">+</a>
                                    </td>
                                    <td class="ova-box">
                                        <a href="javascript:void(0)" class="ovabrw_remove_row btn btn-red" title="<?php esc_attr_e( 'Remove option', 'ova-brw' ); ?>">x</a>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="6" style="padding: 0;">
                                        <em>
                                            <?php esc_html_e( 'ID: Unique, only lowercase, not space' ); ?>
                                        </em>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>                
                    </td>
                </tr>
                <tr class="row-radio-options">
                    <td width="30%" class="label" valign="top">
                        <?php esc_html_e( 'Options', 'ova-brw' ); ?>
                    </td>
                    <td>
                        <table width="100%" border="0" cellpadding="0" cellspacing="0" class="ova-sub-table">
                            <thead>
                                <tr>
                                    <th width="34%">
                                        <?php esc_html_e( 'Value', 'ova-brw' ); ?>
                                    </th>
                                    <th width="34%">
                                        <?php esc_html_e( 'Price', 'ova-brw' ); ?>
                                    </th>
                                    <th width="30%">
                                        <?php esc_html_e( 'Quantity', 'ova-brw' ); ?>
                                    </th>
                                    <th width="1%"></th>
                                    <th width="1%"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <input
                                            type="text"
                                            name="ova_radio_values[]"
                                            placeholder="..."
                                        />
                                    </td>
                                    <td>
                                        <input
                                            type="text"
                                            name="ova_radio_prices[]"
                                            placeholder="<?php esc_attr_e( 'price', 'ova-brw' ); ?>"
                                        />
                                    </td>
                                    <td>
                                        <input
                                            type="number"
                                            name="ova_radio_qtys[]"
                                            placeholder="<?php esc_attr_e( 'number', 'ova-brw' ); ?>"
                                            min="0"
                                        />
                                    </td>
                                    <td class="ova-box">
                                        <a href="javascript:void(0)" class="ovabrw_add_radio btn btn-blue" title="<?php esc_attr_e( 'Add new option', 'ova-brw' ); ?>">+</a>
                                    </td>
                                    <td class="ova-box">
                                        <a href="javascript:void(0)" class="ovabrw_remove_radio btn btn-red" title="<?php esc_attr_e( 'Remove option', 'ova-brw' ); ?>">x</a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>                
                    </td>
                </tr>
                <tr class="row-checkbox-options">
                    <td width="30%" class="label" valign="top">
                        <?php esc_html_e( 'Options', 'ova-brw' ); ?>
                    </td>
                    <td>
                        <table width="100%" border="0" cellpadding="0" cellspacing="0" class="ova-sub-table">
                            <thead>
                                <tr>
                                    <th width="28%">
                                        <?php esc_html_e( 'ID', 'ova-brw' ); ?>
                                    </th>
                                    <th width="28%">
                                        <?php esc_html_e( 'Label', 'ova-brw' ); ?>
                                    </th>
                                    <th width="20%">
                                        <?php esc_html_e( 'Price', 'ova-brw' ); ?>
                                    </th>
                                    <th width="20%">
                                        <?php esc_html_e( 'Quantity', 'ova-brw' ); ?>
                                    </th>
                                    <th width="1%"></th>
                                    <th width="1%"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <input
                                            type="text"
                                            name="ova_checkbox_key[]"
                                            placeholder="..."
                                        />
                                    </td>
                                    <td>
                                        <input
                                            type="text"
                                            name="ova_checkbox_text[]"
                                            placeholder="<?php esc_attr_e( 'label', 'ova-brw' ); ?>"
                                        />
                                    </td>
                                    <td>
                                        <input
                                            type="text"
                                            name="ova_checkbox_price[]"
                                            placeholder="<?php esc_attr_e( 'price', 'ova-brw' ); ?>"
                                        />
                                    </td>
                                    <td>
                                        <input
                                            type="number"
                                            name="ova_checkbox_qty[]"
                                            placeholder="<?php esc_attr_e( 'number', 'ova-brw' ); ?>"
                                            min="0"
                                        />
                                    </td>
                                    <td class="ova-box">
                                        <a href="javascript:void(0)" class="ovabrw_add_checkbox_option btn btn-blue" title="<?php esc_attr_e( 'Add new option', 'ova-brw' ); ?>">+</a>
                                    </td>
                                    <td class="ova-box">
                                        <a href="javascript:void(0)" class="ovabrw_remove_checkbox_option btn btn-red" title="<?php esc_attr_e( 'Remove option', 'ova-brw' ); ?>">x</a>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="6" style="padding: 0;">
                                        <em>
                                            <?php esc_html_e( 'ID: Unique, only lowercase, not space' ); ?>
                                        </em>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </td>
                </tr>
                <tr class="ova-row-file-size">
                    <td class="label">
                        <?php esc_html_e( 'Max Size', 'ova-brw' ); ?>
                    </td>
                    <td>
                        <input
                            type="text"
                            name="max_file_size"
                            value="20"
                        />
                        <span><?php esc_html_e( 'Default: 20MB', 'ova-brw' ); ?></span>
                    </td>
                </tr>
                <tr class="ova-row-name">
                    <td class="label">
                        <?php esc_html_e( 'Slug', 'ova-brw' ); ?>
                    </td>
                    <td>
                        <input
                            type="text"
                            name="name"
                        />
                        <em><?php esc_html_e( 'Unique, only lowercase, not space' ); ?></em>
                    </td>
                </tr>
                <tr class="ova-row-label">
                    <td class="label">
                        <?php esc_html_e( 'Label', 'ova-brw' ); ?>
                    </td>
                    <td>
                        <input
                            type="text"
                            name="label"
                        />
                    </td>
                </tr>
                <tr class="ova-row-placeholder">
                    <td class="label">
                        <?php esc_html_e( 'Placeholder', 'ova-brw' ); ?>
                    </td>
                    <td>
                        <input
                            type="text"
                            name="placeholder"
                        />
                    </td>
                </tr>
                <tr class="ova-row-default">
                    <td class="label">
                        <?php esc_html_e( 'Default value', 'ova-brw' ); ?>
                    </td>
                    <td>
                        <input
                            type="text"
                            name="default"
                        />
                    </td>
                </tr>
                <tr class="ova-row-class">
                    <td class="label">
                        <?php esc_html_e( 'Class', 'ova-brw' ); ?>
                    </td>
                    <td>
                        <input
                            type="text"
                            name="class"
                        />
                    </td>
                </tr>
                <tr class="row-required">
                    <td></td>
                    <td class="check-box">
                        <label>
                            <input
                                id="ova_required"
                                type="checkbox"
                                name="required"
                                value="on"
                                checked
                            />
                            <?php esc_html_e( 'Required', 'ova-brw' ); ?>
                        </label>
                        <label>
                            <input
                                id="ova_enable"
                                type="checkbox"
                                name="enabled"
                                value="on"
                                checked
                            />
                            <?php esc_html_e( 'Enable', 'ova-brw' ); ?>
                        </label>
                    </td>                     
                    <td class="label"></td>
                </tr>
            </table>
            <button type='submit' class="button button-primary">
                <?php esc_html_e( 'Save', 'ova-brw' ); ?>
            </button>
        </form>
        <?php
    }
}