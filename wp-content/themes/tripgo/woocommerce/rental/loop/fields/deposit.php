<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get product id
$product_id = tripgo_get_meta_data( 'id', $args, get_the_id() );

// Get product
$product = wc_get_product( $product_id );
if ( !$product || !$product->is_type( 'ovabrw_car_rental' ) ) return;

// Deposit enable
$deposit_enable = tripgo_get_post_meta( $product_id, 'enable_deposit' );

// Show full payment
$show_fullpayment = tripgo_get_post_meta( $product_id, 'force_deposit' );

// Defautl selected
$default_selected = tripgo_get_post_meta( $product_id, 'deposit_default', 'full' );

// Deposit type
$deposit_type = tripgo_get_post_meta( $product_id, 'type_deposit' );

// Deposit value
$deposit_value = tripgo_get_post_meta( $product_id, 'amount_deposit' );

if ( 'yes' === $deposit_enable ): ?>
	<div class="ovabrw-deposit rental_item">
		<div class="title-deposit">
			<span><?php esc_html_e( 'Deposit Option', 'tripgo' ); ?></span>
			<?php if ( 'percent' === $deposit_type ): ?>
				<span>
					<?php echo sprintf( esc_html__( '%s%%', 'tripgo' ), $deposit_value ); ?>
				</span>
			<?php else: ?>
				<span><?php echo wp_kses_post( ovabrw_wc_price( $deposit_value ) ); ?></span>
			<?php endif; ?>
			<span><?php esc_html_e('Per item', 'tripgo') ?></span>
		</div>
		<div class="ovabrw-type-deposit">
			<?php if ( 'yes' === $show_fullpayment ):
				tripgo_text_input([
					'type' 		=> 'radio',
					'id' 		=> 'ovabrw-pay-full',
					'class' 	=> 'ovabrw-pay-full',
					'name' 		=> 'ova_type_deposit',
					'value' 	=> 'full',
					'checked' 	=> 'full' === $default_selected ? true : false
				]); ?>
				<label class="ovabrw-pay-full" for="ovabrw-pay-full">
					<?php esc_html_e( 'Full Payment', 'tripgo' ); ?>
				</label>
				<?php tripgo_text_input([
					'type' 		=> 'radio',
					'id' 		=> 'ovabrw-pay-deposit',
					'class' 	=> 'ovabrw-pay-deposit',
					'name' 		=> 'ova_type_deposit',
					'value' 	=> 'deposit',
					'checked' 	=> 'deposit' === $default_selected ? true : false
				]); ?>
				<label class="ovabrw-pay-deposit" for="ovabrw-pay-deposit">
					<?php esc_html_e( 'Pay Deposit', 'tripgo' ); ?>
				</label>
			<?php else:
				tripgo_text_input([
					'type' 		=> 'radio',
					'id' 		=> 'ovabrw-pay-deposit',
					'class' 	=> 'ovabrw-pay-deposit',
					'name' 		=> 'ova_type_deposit',
					'value' 	=> 'deposit',
					'checked' 	=> true
				]); ?>
				<label class="ovabrw-pay-deposit" for="ovabrw-pay-deposit">
					<?php esc_html_e( 'Pay Deposit', 'tripgo' ); ?>
				</label>
			<?php endif; ?>
		</div>
	</div>
<?php endif;