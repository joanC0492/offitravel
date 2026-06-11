<?php if ( !defined( 'ABSPATH' ) ) exit();

// Show feature icons
$width = apply_filters( OVABRW_PREFIX.'show_icon_features', true ) ? '29%' : '49%';

?>

<tr class="tr_rt_feature">
    <?php if ( apply_filters( OVABRW_PREFIX.'show_icon_features', true ) ): ?>
        <td width="30%">
            <?php ovabrw_wp_text_input([
                'type'          => 'text',
                'class'         => 'ovabrw-input-required',
                'name'          => $this->get_meta_name( 'features_icons[]' ),
                'placeholder'   => esc_html__( 'Icon class', 'ova-brw' )
            ]); ?>
        </td>
    <?php endif; ?>
    <td width="30%">
        <?php ovabrw_wp_text_input([
            'type'          => 'text',
            'class'         => 'ovabrw-input-required',
            'name'          => $this->get_meta_name( 'features_label[]' ),
            'placeholder'   => esc_html__( 'Label', 'ova-brw' )
        ]); ?>
    </td>
    <td width="<?php echo esc_attr( $width ); ?>">
        <?php ovabrw_wp_text_input([
            'type'          => 'text',
            'class'         => 'ovabrw-input-required',
            'name'          => $this->get_meta_name( 'features_desc[]' ),
            'placeholder'   => esc_html__( 'Description', 'ova-brw' )
        ]); ?>
    </td>
    <td width="1%">
        <button class="button ovabrw-remove-feature">x</button>
    </td>
</tr>