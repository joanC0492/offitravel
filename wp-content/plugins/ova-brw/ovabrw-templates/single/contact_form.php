<?php if ( !defined( 'ABSPATH' ) ) exit();

// Global product
global $product;

// Check product
if ( !$product || !$product->is_type( OVABRW_RENTAL ) ) return;

// Get product id
$product_id = $product->get_id();

// Get contact form
$contact_form = ovabrw_get_post_meta( $product_id, 'manage_extra_tab' );

?>

<div class="ova-contact-form-tabs-update">
	<?php switch ( $contact_form ) {
		case 'in_setting':
			$shortcode_form = ovabrw_get_option_setting( 'extra_tab_shortcode_form' );
			break;
		case 'new_form':
			$shortcode_form = ovabrw_get_post_meta( $product_id, 'extra_tab_shortcode' );
			break;
		case 'no':
			$shortcode_form = '';
			break;
		default:
			$shortcode_form = ovabrw_get_option_setting( 'extra_tab_shortcode_form' );
			break;
	}

	if ( $shortcode_form ) {
		echo do_shortcode( htmlspecialchars_decode( $shortcode_form ) );
	} ?>
</div>