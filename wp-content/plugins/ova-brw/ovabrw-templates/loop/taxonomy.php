<?php if ( ! defined( 'ABSPATH' ) ) exit();

// Global product
global $product;

// Get custom taxonomies
$taxonomies = get_all_cus_tax_dis_listing( $product->get_id() );
if ( ovabrw_array_exists( $taxonomies ) ): ?>
	<ul class="product_listing_custom_tax">
		<?php foreach ( $taxonomies as $key => $value ): ?>
			<li class="<?php echo esc_attr( 'tax_'.$value['slug'] ); ?>">
				<?php echo esc_html( $value['name'] ); ?>
			</li>
		<?php endif; ?>
	</ul>
<?php endif;