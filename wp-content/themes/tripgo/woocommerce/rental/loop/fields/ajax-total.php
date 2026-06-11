<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get product id
$product_id = tripgo_get_meta_data( 'id', $args, get_the_id() );

// Get product
$product = wc_get_product( $product_id );
if ( !$product || !$product->is_type('ovabrw_car_rental') ) return;

// Get insurance amount
$insurance_amount = tripgo_get_post_meta( $product_id, 'amount_insurance' );

?>

<div class="ajax-show-total">
	<?php if ( 'yes' === get_option( 'ova_brw_booking_form_show_quantity_availables', 'yes' ) ): ?>
		<div class="ovabrw-ajax-availables ovabrw-show-amount">
			<span class="availables-label label">
				<?php esc_html_e( 'Disponible: ', 'tripgo' ); ?>
			</span>
			<span class="show-availables-number show-amount"></span>
			<span class="ajax-loading-total">
				<i aria-hidden="true" class="flaticon flaticon-spinner-of-dots"></i>
			</span>
		</div>
	<?php endif; ?>
	<div class="ovabrw-ajax-total ovabrw-show-amount">
		<span class="show-total label">
			<?php esc_html_e( 'Total:', 'tripgo' ); ?>
		</span>
		<span class="show-deposit label" style="display: none;">
			<?php esc_html_e( 'Depósito:', 'tripgo' ); ?>
		</span>
		<span class="show-total-number show-amount"></span>
		<span class="ajax-loading-total">
			<i aria-hidden="true" class="flaticon flaticon-spinner-of-dots"></i>
		</span>
	</div>
	<?php if ( $insurance_amount && function_exists( 'ovabrw_show_insurance_amount' ) && ovabrw_show_insurance_amount() ): ?>
		<div class="ovabrw-ajax-amount-insurance">
			<span class="show-amount-insurance"></span>
		</div>
	<?php endif; ?>
</div>