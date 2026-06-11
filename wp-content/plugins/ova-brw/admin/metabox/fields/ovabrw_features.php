<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get feature labels
$feature_labels = ovabrw_get_post_meta( $post_id, 'features_label' );

// Get feature description
$feature_desc = ovabrw_get_post_meta( $post_id, 'features_desc' );

// Get feature icons
$feature_icons = ovabrw_get_post_meta( $post_id, 'features_icons' );

// Show feature icons
$width = apply_filters( OVABRW_PREFIX.'show_icon_features', true ) ? '29%' : '49%';

?>

<div class="ovabrw-features">
	<table class="widefat">
		<thead>
			<tr>
				<?php if ( apply_filters( OVABRW_PREFIX.'show_icon_features', true ) ): ?>
					<th class="ovabrw-required">
						<?php esc_html_e( 'Icon Class', 'ova-brw' ); ?>
					</th>
				<?php endif; ?>
				<th class="ovabrw-required">
					<?php esc_html_e( 'Label', 'ova-brw' ); ?>
				</th>
				<th class="ovabrw-required">
					<?php esc_html_e( 'Description', 'ova-brw' ); ?>
				</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( ovabrw_array_exists( $feature_labels ) ):
				foreach ( $feature_labels as $i => $label ):
					// Get icon
					$icon = ovabrw_get_meta_data( $i, $feature_icons );

					// Get description
					$desc = ovabrw_get_meta_data( $i, $feature_desc );
			?>
				<tr>
					<?php if ( apply_filters( OVABRW_PREFIX.'show_icon_features', true ) ): ?>
					    <td width="30%">
					    	<?php ovabrw_wp_text_input([
					    		'type' 			=> 'text',
					    		'class' 		=> 'ovabrw-input-required',
					    		'name' 			=> $this->get_meta_name( 'features_icons[]' ),
					    		'value' 		=> $icon,
					    		'placeholder' 	=> esc_html__( 'Icon class', 'ova-brw' )
					    	]); ?>
					    </td>
					<?php endif; ?>
				    <td width="30%">
				    	<?php ovabrw_wp_text_input([
				    		'type' 			=> 'text',
				    		'class' 		=> 'ovabrw-input-required',
				    		'name' 			=> $this->get_meta_name( 'features_label[]' ),
				    		'value' 		=> $label,
				    		'placeholder' 	=> esc_html__( 'Label', 'ova-brw' )
				    	]); ?>
				    </td>
				    <td width="<?php echo esc_attr( $width ); ?>">
				    	<?php ovabrw_wp_text_input([
				    		'type' 			=> 'text',
				    		'class' 		=> 'ovabrw-input-required',
				    		'name' 			=> $this->get_meta_name( 'features_desc[]' ),
				    		'value' 		=> $desc,
				    		'placeholder' 	=> esc_html__( 'Description', 'ova-brw' )
				    	]); ?>
				    </td>
				    <td width="1%">
				    	<button class="button ovabrw-remove-feature">x</button>
				    </td>
				</tr>
			<?php endforeach;
			endif; ?>
		</tbody>
		<tfoot>
			<tr>
				<th colspan="4">
					<button class="button ovabrw-add-feature" data-row="<?php
						ob_start();
						include( OVABRW_PLUGIN_PATH.'/admin/metabox/fields/ovabrw_feature_field.php' );
						echo esc_attr( ob_get_clean() );
					?>">
						<?php esc_html_e( 'Add Feature', 'ova-brw' ); ?></a>
					</button>
				</th>
			</tr>
		</tfoot>
	</table>
</div>