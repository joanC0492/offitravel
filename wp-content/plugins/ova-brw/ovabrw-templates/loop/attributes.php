<?php if ( !defined( 'ABSPATH' ) ) exit();

// Global product
global $product;

// Check product
if ( !$product || !$product->is_type( OVABRW_RENTAL ) ) return;

// init
$html = '';

// Get attributes
$attributes = $product->get_attributes();
if ( !ovabrw_array_exists( $attributes ) ) return;

// Loop
foreach ( $attributes as $attribute ) :
    $values = [];

    if ( $attribute->is_taxonomy() ) {
        $attribute_taxonomy = $attribute->get_taxonomy_object();

        // Get attribute values
        $attribute_values = wc_get_product_terms( $product->get_id(), $attribute->get_name(), [
            'fields' => 'all'
        ]);

        if ( ovabrw_array_exists( $attribute_values ) ) {
            foreach ( $attribute_values as $attribute_value ) {
                $value_name = esc_html( $attribute_value->name );

                if ( $attribute_taxonomy->attribute_public ) {
                    $values[] = '<a href="' . esc_url( get_term_link( $attribute_value->term_id, $attribute->get_name() ) ) . '" rel="tag">' . $value_name . '</a>';
                } else {
                    $values[] = $value_name;
                }
            }
        }
    } else {
        $values = $attribute->get_options();
        if ( ovabrw_array_exists( $values ) ) {
            foreach ( $values as &$value ) {
                $value = make_clickable( esc_html( $value ) );
            }
        }
    }
    
    if ( ovabrw_array_exists( $values ) ): ?>
        <div class="ovabrw_product_attr">
        	<span class="label">
        		<?php echo wc_attribute_label( $attribute->get_name() ); ?> : 
        	</span>
        	<span class="value">
        		<?php echo apply_filters( 'woocommerce_attribute',  wptexturize( implode( ', ', $values ) ) , $attribute, $values ); ?>
        	<span>
        </div>
    <?php endif;
endforeach; // END loop