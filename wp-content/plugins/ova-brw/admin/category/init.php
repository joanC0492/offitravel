<?php if ( !defined( 'ABSPATH' ) ) exit();

/**
 * Add new category
 */
add_action( 'product_cat_add_form_fields', function( $array ) { ?>
    <!-- Display category -->
    <div class="form-field">
        <label for="Display"><?php esc_html_e( 'Display', 'ova-brw' ); ?></label>
        <select name="ovabrw_cat_dis">
            <option value="rental"><?php esc_html_e( 'Tour', 'ova-brw' ); ?></option>
            <option value="shop"><?php esc_html_e( 'Shop', 'ova-brw' ); ?></option>
        </select>
    </div>

    <!-- Custom Taxonomy -->
    <div class="form-field">
        <label>
            <?php esc_html_e('Choose Taxonomies', 'ova-brw'); ?>
        </label>
        <select name="ovabrw_custom_tax[]" multiple="multiple" class="ovabrw_custom_tax_with_cat">
            <?php $list_fields = ovabrw_get_option( 'custom_taxonomy', [] );
                if ( ovabrw_array_exists( $list_fields ) ):
                    foreach ( $list_fields as $slug => $field ):
                        // Get name
                        $name = ovabrw_get_meta_data( 'name', $field );

                        // Get singular name
                        $singular_name = ovabrw_get_meta_data( 'singular_name', $field );
                        ?>
                            <option value="<?php echo esc_attr( $slug ); ?>">
                                <?php echo esc_html( $name ); ?>
                            </option>
                    <?php endforeach;
                endif;
            ?>
        </select>
    </div>

    <!-- Custom Checkout Field -->
    <div class="form-field">
        <div class="choose_custom_checkout_field">
            <label>
                <?php esc_html_e('Choose custom checkout fields', 'ova-brw'); ?>
            </label>
            <select name="ovabrw_choose_custom_checkout_field" id="">
                <option value="all"><?php esc_html_e( 'All', 'ova-brw' ); ?></option>
                <option value="special"><?php esc_html_e( 'Choose other fields', 'ova-brw' ); ?></option>
            </select>
        </div>
        <div id="special_cus_fields" class="show_special_checkout_field">
            <br>
            <label>
                <?php esc_html_e('Choose other custom checkout fields', 'ova-brw'); ?>
            </label>
            <select name="ovabrw_custom_checkout_field[]" multiple="multiple" class="ovabrw_custom_tax_with_cat">
                <?php $list_fields = ovabrw_get_option( 'booking_form', [] );
                    if ( ovabrw_array_exists( $list_fields ) ):
                        foreach( $list_fields as $slug => $field ):
                            $label = ovabrw_get_meta_data( 'label', $field );
                        ?>
                            <option value="<?php echo esc_attr( $slug ); ?>">
                                <?php echo esc_html( $label ); ?>
                            </option>
                    <?php endforeach;
                endif; ?>
            </select>
        </div>
    </div>

    <!-- Product Templates -->
    <div class="form-field">
        <label>
            <?php esc_html_e( 'Product template', 'ova-brw' ); ?>
            <span>
                <?php echo wc_help_tip( esc_html__( 'Global Setting (WooCommerce >> Settings >> Booking & Rental >> Product Template) or Other (made in Templates of Elementor )', 'ova-brw' ), true ); ?>
            </span>
        </label>
        <?php
            // Get templates from elementor
            $global_template = ovabrw_get_option_setting( 'template_elementor_template', 'default' );

            // Get templates
            $templates = get_posts([
                'post_type'     => 'elementor_library',
                'meta_key'      => '_elementor_template_type',
                'meta_value'    => 'page',
                'numberposts'   => -1,
                'fields'        => 'ids'
            ]);
        ?>
        <select id="ovabrw_product_templates" name="ovabrw_product_templates">
            <option value="global" selected="selected">
                <?php esc_html_e( 'Global Settings', 'ova-brw' ); ?>
            </option>
            <?php if ( ovabrw_array_exists( $templates ) ):
                foreach ( $templates as $template_id ):
                    // Template ID
                    if ( $global_template == $template_id ) continue;
                ?>
                    <option value="<?php echo esc_attr( $template_id ); ?>">
                        <?php echo esc_html( get_the_title( $template_id ) ); ?>
                    </option>
                <?php endforeach;
            endif; ?>
        </select>
    </div>
<?php });

/**
 * Edit category
 */
add_action( 'product_cat_edit_form_fields', function( $term ) {
    // Get term ID
    $term_id = $term->term_id;

    // retrieve the existing value(s) for this meta field.
    $ovabrw_cat_dis     = get_term_meta( $term_id, 'ovabrw_cat_dis', true );
    $ovabrw_custom_tax  = get_term_meta( $term_id, 'ovabrw_custom_tax', true );
    if ( !$ovabrw_custom_tax ) {
        $ovabrw_custom_tax = [];
    }

    // Custom checkout field
    $ovabrw_custom_checkout_field = get_term_meta( $term_id, 'ovabrw_custom_checkout_field', true );
    if ( !$ovabrw_custom_checkout_field ) {
        $ovabrw_custom_checkout_field = [];
    }
    
    // Category custom checkout field
    $ovabrw_choose_custom_checkout_field = get_term_meta( $term_id, 'ovabrw_choose_custom_checkout_field', true );

    // Get product template
    $global_template = ovabrw_get_option_setting( 'template_elementor_template', 'default' );

    // Get prodyct template
    $product_template = get_term_meta( $term_id, 'ovabrw_product_templates', true );

    // Get templates
    $templates = get_posts([
        'post_type'     => 'elementor_library',
        'meta_key'      => '_elementor_template_type',
        'meta_value'    => 'page',
        'numberposts'   => -1,
        'fields'        => 'ids'
    ]);

    ?>

    <!-- Display category -->
    <tr class="form-field">
        <th scope="row" valign="top">
            <label for="ovabrw_cat_dis">
                <?php esc_html_e( 'Display', 'ova-brw' ); ?>
            </label>
        </th>
        <td>
            <select name="ovabrw_cat_dis">
                <option value="rental"<?php ovabrw_selected( 'rental', $ovabrw_cat_dis ); ?>>
                    <?php esc_html_e( 'Rental', 'ova-brw' ); ?>
                </option>
                <option value="shop"<?php ovabrw_selected( 'shop', $ovabrw_cat_dis ); ?>>
                    <?php esc_html_e( 'Shop', 'ova-brw' ); ?>
                </option>
            </select>
        </td>
    </tr>

    <!-- Custom Taxonomy -->
    <tr class="form-field">
        <th scope="row" valign="top">
            <label>
                <?php esc_html_e('Choose Taxonomies', 'ova-brw'); ?>
            </label>
        </th>
        <td>
            <select name="ovabrw_custom_tax[]" multiple="multiple" class="ovabrw_custom_tax_with_cat">
                <?php $list_fields = ovabrw_get_option( 'custom_taxonomy', [] );
                    if ( ovabrw_array_exists( $list_fields ) ):
                        foreach ( $list_fields as $slug => $field ):
                            // Get name
                            $name = ovabrw_get_meta_data( 'name', $field );

                            // Get singular name
                            $singular_name = ovabrw_get_meta_data( 'singular_name', $field );
                        ?>
                            <option value="<?php echo esc_attr( $slug ); ?>"<?php ovabrw_selected( $slug, $ovabrw_custom_tax ); ?>>
                                <?php echo esc_html( $name ); ?>
                            </option>
                        <?php endforeach;
                    endif; ?>
            </select>
        </td>
    </tr>

    <!-- Custom Checkout Field -->
    <tr class="form-field choose_custom_checkout_field">
        <th scope="row" valign="top">
            <label>
                <?php esc_html_e('Choose custom checkout fields', 'ova-brw'); ?>
            </label>
        </th>
        <td>
            <select name="ovabrw_choose_custom_checkout_field" id="">
                <option value="all"<?php ovabrw_selected( $ovabrw_choose_custom_checkout_field, 'all' ); ?>>
                    <?php esc_html_e( 'All', 'ova-brw' ); ?>
                </option>
                <option value="special"<?php ovabrw_selected( $ovabrw_choose_custom_checkout_field, 'special' ); ?>>
                    <?php esc_html_e( 'Choose other fields', 'ova-brw' ); ?>
                </option>
            </select>
        </td>
    </tr>
    <tr class="form-field show_special_checkout_field">
        <th scope="row" valign="top">
            <label>
                <?php esc_html_e('Choose other custom checkout fields', 'ova-brw'); ?>
            </label>
        </th>
        <td>
            <select name="ovabrw_custom_checkout_field[]" multiple="multiple" class="ovabrw_custom_tax_with_cat">
                <?php $list_fields = ovabrw_get_option( 'booking_form', [] );
                    if ( ovabrw_array_exists( $list_fields ) ):
                        foreach ( $list_fields as $slug => $field ):
                            // Get label
                            $label = ovabrw_get_meta_data( 'label', $field );
                        ?>
                            <option value="<?php echo esc_attr( $slug ); ?>"<?php ovabrw_selected( $slug, $ovabrw_custom_checkout_field ); ?>>
                                <?php echo esc_html( $label ); ?>
                            </option>
                    <?php endforeach;
                endif; ?>
            </select>
        </td>
    </tr>

    <!-- Product template -->
    <tr class="form-field ovabrw_product_templates">
        <th>
            <label>
                <?php esc_html_e('Product template', 'ova-brw'); ?>
                <span>
                    <?php echo wc_help_tip( esc_html__( 'Global Setting (WooCommerce >> Settings >> Booking & Rental >> Product Template) or Other (made in Templates of Elementor )', 'ova-brw' ), true ); ?>
                </span>
            </label>
        </th>
        <td>
            <select id="ovabrw_product_templates" name="ovabrw_product_templates">
                <option value="global">
                    <?php esc_html_e( 'Global Settings', 'ova-brw' ); ?>
                </option>
                <?php if ( ovabrw_array_exists( $templates ) ):
                    foreach ( $templates as $template_id ):
                        // Template ID
                        if ( $global_template == $template_id ) continue;
                    ?>
                        <option value="<?php echo esc_attr( $template_id ); ?>"<?php ovabrw_selected( $product_template, $template_id ); ?>>
                            <?php echo esc_html( get_the_title( $template_id ) ); ?>
                        </option>
                    <?php endforeach;
                endif; ?>
            </select>
        </td>
    </tr>
<?php });

/**
 * Save category
 */
if ( !function_exists( 'ovabrw_save_taxonomy_custom_meta' ) ) {
    function ovabrw_save_taxonomy_custom_meta( $term_id ) {
        // Display
        $ovabrw_cat_dis = ovabrw_get_meta_data( 'ovabrw_cat_dis', $_REQUEST );
        update_term_meta( $term_id, 'ovabrw_cat_dis', $ovabrw_cat_dis );

        // Get custom taxonomy
        $custom_taxo = ovabrw_get_meta_data( 'ovabrw_custom_tax', $_REQUEST );
        update_term_meta( $term_id, 'ovabrw_custom_tax', $custom_taxo );

        // Get custom checkout fields
        $custom_checkout_field = ovabrw_get_meta_data( 'ovabrw_custom_checkout_field', $_REQUEST , [] );
        update_term_meta( $term_id, 'ovabrw_custom_checkout_field', $custom_checkout_field );

        // Choose cckf
        $choose_custom_checkout_field = ovabrw_get_meta_data( 'ovabrw_choose_custom_checkout_field', $_REQUEST, 'all' );
        update_term_meta( $term_id, 'ovabrw_choose_custom_checkout_field', $choose_custom_checkout_field );

        // Get product template
        $product_template = ovabrw_get_meta_data( 'ovabrw_product_templates', $_REQUEST, 'global' );
        update_term_meta( $term_id, 'ovabrw_product_templates', $product_template );
    }
}
add_action( 'edited_product_cat', 'ovabrw_save_taxonomy_custom_meta' );
add_action( 'create_product_cat', 'ovabrw_save_taxonomy_custom_meta' );