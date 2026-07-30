<?php
/**
 * Gift Cards in the Cart
 *
 * Shows applied Gift Cards in the cart page.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart-gift-cards.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce Gift Cards
 * @version 1.16.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_gc_totals_before_gift_cards' );

$mask_codes = wc_gc_mask_codes( 'checkout' );

if ( ! empty( $giftcards['applied']['giftcards'] ) || ! empty( $giftcards['account']['giftcards'] ) ) :
	// Display the total before Gift Cards.
	if ( apply_filters( 'woocommerce_gc_display_total_before_giftcards', true ) ) {
		?><tr>
			<th>
				<?php esc_html_e( 'Total', 'woocommerce-gift-cards' ); ?>
				<small>
					<?php esc_html_e( '(before gift cards)', 'woocommerce-gift-cards' ); ?>
				</small>
			</th>
			<td data-title="<?php esc_attr_e( 'Total before gift cards', 'woocommerce-gift-cards' ); ?>">
				<strong>
					<?php echo wp_kses_post( wc_price( $totals['cart_total'] ) ); ?>
				</strong>
			</td>
		</tr>
		<?php
	}

endif;

if ( ! empty( $giftcards['applied']['giftcards'] ) ) :

	// Applied Gift Cards through the inline-form.
	foreach ( $giftcards['applied']['giftcards'] as $giftcard_used ) :
		?>
		<tr class="cart-discount gift-card">
			<th>
				<?php esc_html_e( 'Gift Card', 'woocommerce-gift-cards' ); ?>

				<?php if ( apply_filters( 'woocommerce_gc_checkout_show_codes_used', true ) ) : ?>
					<small>
						<?php esc_html_e( 'Code:', 'woocommerce-gift-cards' ); ?>
					</small>
					<small>
						<?php echo $mask_codes ? esc_html( wc_gc_mask_code( $giftcard_used['giftcard']->get_code() ) ) : esc_html( $giftcard_used['giftcard']->get_code() ); ?>
					</small>
				<?php endif; ?>

				<?php if ( apply_filters( 'woocommerce_gc_checkout_show_remaining_balance_per_gift_card', false ) ) : ?>
					<small class="balance_label">
						<?php esc_html_e( 'Remaining Balance:', 'woocommerce-gift-cards' ); ?>
					</small>
					<small class="balance">
						<?php echo wp_kses_post( wc_price( $giftcard_used['giftcard']->get_balance() - $giftcard_used['amount'] ) ); ?>
					</small>
				<?php endif; ?>

			</th>
			<td data-title="<?php esc_attr_e( 'Gift Card', 'woocommerce-gift-cards' ); ?>">
				<?php echo wp_kses_post( wc_price( $giftcard_used['amount'] * -1 ) ); ?>
				[ <a href="#" class="wc_gc_remove_gift_card" data-giftcard="<?php echo esc_attr( $giftcard_used['giftcard']->get_id() ); ?>">
					<?php esc_html_e( 'Remove', 'woocommerce-gift-cards' ); ?>
				</a> ]
				<?php if ( $giftcard_used['giftcard']->get_pending_balance() > 0 ) { ?>
					<small class="woocommerce-MyAccount-Giftcards-pending-amount">
						<?php echo wc_gc_get_pending_balance_resolution( $giftcard_used['giftcard'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span class="warning-icon"></span>
					</small>
				<?php } ?>
			</td>
		</tr>
		<?php
	endforeach;

endif;

// Display checkbox toggler for balance usage.
if ( wc_gc_is_redeeming_enabled() && $totals['available_total'] > 0 && $has_balance && apply_filters( 'woocommerce_gc_checkout_show_balance_checkbox', true ) ) :
	?>
	<tr class="update_totals_on_change">
		<th></th>
		<td>
			<label for="use_gift_card_balance">
				<input type="checkbox" id="use_gift_card_balance" name="use_gift_card_balance"<?php echo $use_balance ? ' checked' : ''; ?>>
					<?php
					/* translators: remaining balance */
					echo wp_kses_post( sprintf( __( 'Use <strong>%s</strong> from your gift cards balance.', 'woocommerce-gift-cards' ), wc_price( $totals['available_total'] ) ) );
					?>
					<?php if ( $totals['pending_total'] > 0 ) { ?>
						<small class="woocommerce-MyAccount-Giftcards-pending-amount">
							<?php
							$link_text = esc_html__( 'pending orders', 'woocommerce-gift-cards' );
							$link      = add_query_arg( array( 'wc_gc_show_pending_orders' => 'yes' ), wc_get_account_endpoint_url( 'orders' ) );
							/* translators: %1$s: text link, %2$s pending amount */
							echo wp_kses_post( sprintf( __( ' %2$s on hold in %1$s', 'woocommerce-gift-cards' ), '<a href="' . $link . '">' . $link_text . '</a>', wc_price( $totals['pending_total'] ) ) );
							?>
							<span class="warning-icon"></span>
						</small>
					<?php } ?>
			</label>

		</td>
	</tr>
	<?php
endif;

if ( $use_balance && $has_balance && ! empty( $giftcards['account']['total_amount'] ) ) :

	// Print style.
	if ( apply_filters( 'woocommerce_gc_print_balance_one_line', true ) ) :

		?>
		<tr class="cart-discount gift-card gift-card--balance">
			<th>
				<?php esc_html_e( 'Gift Cards Balance', 'woocommerce-gift-cards' ); ?>
			</th>
			<td data-title="<?php esc_attr_e( 'Gift Cards Balance', 'woocommerce-gift-cards' ); ?>">
				<?php echo wp_kses_post( wc_price( $giftcards['account']['total_amount'] * -1 ) ); ?>

				<?php if ( apply_filters( 'woocommerce_gc_checkout_show_codes_used', true ) ) : ?>
					<small class="codes_label">
						<strong>
							<?php echo esc_html( _n( 'Code:', 'Codes:', count( $giftcards['account']['giftcards'] ), 'woocommerce-gift-cards' ) ); ?>
						</strong>
					</small>
					<?php foreach ( $giftcards['account']['giftcards'] as $giftcard_used ) : ?>
						<small class="code">
							<?php echo $mask_codes ? esc_html( wc_gc_mask_code( $giftcard_used['giftcard']->get_code() ) ) : esc_html( $giftcard_used['giftcard']->get_code() ); ?>
						</small>
					<?php endforeach; ?>
				<?php endif; ?>

				<?php if ( apply_filters( 'woocommerce_gc_checkout_show_remaining_balance', true ) ) : ?>
					<small class="balance_label">
						<strong>
							<?php esc_html_e( 'Remaining Balance:', 'woocommerce-gift-cards' ); ?>
						</strong>
					</small>
					<small class="balance">
						<?php echo wp_kses_post( wc_price( $balance - $giftcards['account']['total_amount'] ) ); ?>
					</small>
				<?php endif; ?>
			</td>
		</tr>
		<?php

	else :
		// Account balance Gift Cards.
		foreach ( $giftcards['account']['giftcards'] as $giftcard_used ) :
			?>
			<tr class="cart-discount gift-card gift-card--balance">
				<th>
					<?php esc_html_e( 'Gift Card', 'woocommerce-gift-cards' ); ?>

					<?php if ( apply_filters( 'woocommerce_gc_checkout_show_codes_used', true ) ) : ?>

						<small class="code_label">
							<?php esc_html_e( 'Code:', 'woocommerce-gift-cards' ); ?>
						</small>
						<small class="code">
							<?php echo $mask_codes ? esc_html( wc_gc_mask_code( $giftcard_used['giftcard']->get_code() ) ) : esc_html( $giftcard_used['giftcard']->get_code() ); ?>
						</small>
					<?php endif; ?>

				</th>
				<td data-title="<?php esc_attr_e( 'Gift Card Balance', 'woocommerce-gift-cards' ); ?>">
					<?php echo wp_kses_post( wc_price( $giftcard_used['amount'] * -1 ) ); ?>
				</td>
			</tr>
			<?php
		endforeach;
	endif;
endif;

do_action( 'woocommerce_gc_totals_after_gift_cards' );
