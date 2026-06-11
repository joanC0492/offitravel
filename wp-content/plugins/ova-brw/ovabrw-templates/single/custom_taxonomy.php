<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get product id
$product_id = ovabrw_get_meta_data( 'id', $args, get_the_id() );

// Get product
$product = wc_get_product( $product_id );
if ( !$product || !$product->is_type( OVABRW_RENTAL ) ) return;

// Get custom taxonomies
$custom_taxonomies = ovabrw_get_taxonomy_choosed_product( $product_id );

// Loop
if ( ovabrw_array_exists( $custom_taxonomies ) ): ?>
	<ul class="ovabrw_cus_taxonomy">
		<?php foreach ( $custom_taxonomies as $key => $taxo ):
			// Get name
			$name = ovabrw_get_meta_data( 'name', $taxo );

			// Get value
			$value = ovabrw_get_meta_data( 'value', $taxo, [] );
		?>
			<li class="<?php echo esc_attr( $key ); ?>">
				<label>
					<?php echo esc_html( $name ); ?>
				</label>
				<span>
					<?php echo esc_html( implode( ', ', $value ) ); ?>
				</span>
			</li>
		<?php endforeach; ?>			
	</ul>
<?php endif; ?>