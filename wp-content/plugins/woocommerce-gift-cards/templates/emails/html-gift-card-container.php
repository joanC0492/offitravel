<?php
/**
 * Gift Cards
 *
 * Shows orders on the account page.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/html-gift-card-container.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce Gift Cards
 * @version 2.7.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?><div id="giftcard__container">
	<div id="giftcard__body">
		<?php echo wp_kses_post( wpautop( wptexturize( apply_filters( 'woocommerce_email_intro_content', $intro_content, $giftcard ) ) ) ); ?>
	</div>
	<?php if ( $giftcard->get_message() ) : ?>
		<div id="giftcard__message">
			<?php echo nl2br( esc_html( wptexturize( $giftcard->get_message() ) ) ); ?>
		</div>
	<?php endif; ?>

	<table id="giftcard__card"><tr><td>

		<?php if ( $include_header ) : ?>
			<?php if ( 'background' === $render_image ) : ?>
				<div id="giftcard__card-header" style="height: <?php echo esc_attr( $height ); ?>px;background-image: url( <?php echo esc_attr( $image_src ); ?> );background-position: <?php echo esc_attr( $position_X ); ?> <?php echo esc_attr( $position_Y ); ?>;"></div>
			<?php elseif ( 'element' === $render_image ) : ?>
				<div id="giftcard__card-image">
					<img alt="" src="<?php echo esc_attr( $image_src ); ?>" width="100%" height="auto" />
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<div id="giftcard__card-amount-container">
			<div><?php echo $giftcard->get_balance() === $giftcard->get_initial_balance() ? esc_html_x( 'Amount', 'Email gift card received', 'woocommerce-gift-cards' ) : esc_html_x( 'Remaining amount', 'Email gift card received', 'woocommerce-gift-cards' ); ?>:</div>
			<div id="giftcard__card-amount"><?php echo wp_kses_post( wc_price( $giftcard->get_balance() ) ); ?></div>
		</div>

		<?php if ( $show_redeem_button ) : ?>
			<a href="<?php echo esc_url( $button_href ); ?>" id="giftcard__action-button" class="redeem-action"><?php echo esc_html( apply_filters( 'woocommerce_gc_email_received_redeem_button_text', esc_html_x( 'Add to your Account', 'Email gift card received', 'woocommerce-gift-cards' ), $giftcard ) ); ?></a>

			<div id="giftcard__separator">&mdash; <?php echo esc_html_x( 'or', 'Email gift card received', 'woocommerce-gift-cards' ); ?> &mdash;</div>

			<div id="giftcard__card-code-container">
				<p><?php echo esc_html_x( 'Use this code at checkout', 'Email gift card received', 'woocommerce-gift-cards' ); ?>:</p>
				<span id="giftcard__card-code"><?php echo esc_html( $giftcard->get_code() ); ?></span>
			</div>

		<?php else : ?>

			<a href="<?php echo esc_url( $button_href ); ?>" id="giftcard__action-button" class="shop-action"><?php echo esc_html( apply_filters( 'woocommerce_gc_email_received_action_button_text', esc_html_x( 'Shop Now', 'Email gift card received', 'woocommerce-gift-cards' ), $giftcard ) ); ?></a>

			<div id="giftcard__separator">&nbsp;</div>

			<div id="giftcard__card-code-container">
				<p><?php echo esc_html_x( 'Use this code at checkout', 'Email gift card received', 'woocommerce-gift-cards' ); ?>:</p>
				<span id="giftcard__card-code"><?php echo esc_html( $giftcard->get_code() ); ?></span>
			</div>

		<?php endif; ?>

		<?php if ( $giftcard->get_expire_date() > 0 ) : ?>
			<div id="giftcard__expiration">
			<?php
			$expiration_date = esc_html( date_i18n( get_option( 'date_format' ), $giftcard->get_expire_date() ) );
			/* translators: %s: Gift card expiration date */
			printf( esc_html_x( 'Expires on %s', 'Email gift card received', 'woocommerce-gift-cards' ), esc_html( $expiration_date ) );
			?>
			</div>
		<?php endif; ?>

	</td></tr></table>

</div>
