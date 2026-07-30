<?php
/**
 * Admin View: Gift Card edit
 *
 * @package  WooCommerce Gift Cards
 * @since    1.0.0
 *
 * @version  1.16.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap woocommerce woocommerce-gc-giftcards">

	<?php WC_GC_Admin_Menus::render_tabs(); ?>

	<h1 class="wp-heading-inline"><?php esc_html_e( 'Edit Gift Card', 'woocommerce-gift-cards' ); ?></h1>
	<a href="<?php echo esc_url( admin_url( WC_GC_Admin_Gift_Cards_Page::PAGE_URL ) ); ?>" class="page-title-action"><?php esc_html_e( 'All Gift Cards', 'woocommerce-gift-cards' ); ?></a>

	<hr class="wp-header-end">

	<form method="POST" id="edit-gift-card-form">
	<?php wp_nonce_field( 'woocommerce-gc-edit', 'gc_edit_security' ); ?>

	<div id="poststuff">
		<div id="post-body" class="columns-2">

			<!-- SIDEBAR -->
			<div id="postbox-container-1" class="postbox-container">

				<div id="woocommerce-order-actions" class="postbox">

					<h2 class="hndle ui-sortable-handle"><span><?php esc_html_e( 'Gift card actions', 'woocommerce-gift-cards' ); ?></span></h2>

					<div class="inside">
						<ul class="order_actions submitbox">

							<li class="wide" id="actions">
								<select name="wc_gc_action">
									<option value=""><?php esc_html_e( 'Choose an action...', 'woocommerce-gift-cards' ); ?></option>
									<?php if ( ! $giftcard->has_expired() ) : ?>
										<option value="send_giftcard"><?php esc_html_e( 'Send to recipient', 'woocommerce-gift-cards' ); ?></option>
										<?php if ( $giftcard->is_active() ) : ?>
											<option value="disable_giftcard"><?php esc_html_e( 'Deactivate', 'woocommerce-gift-cards' ); ?></option>
										<?php else : ?>
											<option value="enable_giftcard"><?php esc_html_e( 'Activate', 'woocommerce-gift-cards' ); ?></option>
										<?php endif; ?>
									<?php endif; ?>
								</select>
								<button class="button wc-reload"><span><?php esc_html_e( 'Apply', 'woocommerce' ); ?></span></button>
							</li>

							<li class="wide">
								<div id="delete-action">
									<a class="submitdelete deletion" href="<?php echo esc_url( wp_nonce_url( admin_url( sprintf( 'admin.php?page=gc_giftcards&section=delete&giftcard=%d', $giftcard->get_id() ) ), 'delete_giftcard' ) ); ?>"><?php esc_html_e( 'Delete permanently', 'woocommerce-gift-cards' ); ?></a>
								</div>

								<button type="submit" class="button save_order button-primary" name="save" value="<?php esc_attr_e( 'Update', 'woocommerce-gift-cards' ); ?>"><?php esc_html_e( 'Update', 'woocommerce-gift-cards' ); ?></button>
							</li>

						</ul>
					</div>

				</div><!-- .postbox -->

				<div id="giftcards-redeem-action" class="postbox">

					<h2 class="hndle ui-sortable-handle"><span><?php esc_html_e( 'Redeem for customer', 'woocommerce-gift-cards' ); ?></span></h2>

					<div class="inside sw-select2-autoinit">
						<ul class="order_actions submitbox">

							<li class="wide" id="search">
								<select name="redeem_for_customer" class="sw-select2-search--customers" data-placeholder="<?php esc_attr_e( 'Search for a customer&hellip;', 'woocommerce-gift-cards' ); ?>" data-allow_clear="true">
								</select>
							</li>

							<li class="wide">
								<button type="submit" class="button redeem-button" name="redeem" value="<?php esc_attr_e( 'Redeem', 'woocommerce-gift-cards' ); ?>"><?php esc_html_e( 'Redeem', 'woocommerce-gift-cards' ); ?></button>
							</li>

						</ul>
					</div>

				</div><!-- .postbox -->

			</div><!-- #container1 -->

			<!-- MAIN -->
			<div id="postbox-container-2" class="postbox-container">

				<div id="gift-card-data" class="postbox gift-card-data">

					<div class="gift-card-data__row gift-card-data__row--columns">

						<div class="gift-card-data__header-column">

							<h2 class="gift-card-data__header">
								<?php echo wc_gc_mask_codes( 'admin' ) ? esc_html( wc_gc_mask_code( $giftcard->get_code() ) ) : esc_html( $giftcard->get_code() ); ?>
							</h2>

							<div class="gift-card-data__available_balance">
								<p><?php esc_html_e( 'Available balance', 'woocommerce-gift-cards' ); ?></p>
								<span class="text--balance"><?php echo wp_kses_post( wc_price( $giftcard->get_balance() ) ); ?></span>
							</div>

						</div>

						<div class="gift-card-data__status-column">
							<?php echo wc_gc_get_status_labels_html( $giftcard ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>

					</div><!-- #row -->

					<div class="gift-card-data__information">
						<div class="cell">
							<label><?php esc_html_e( 'Issued', 'woocommerce-gift-cards' ); ?></label>
							<span><?php echo esc_html( date_i18n( get_option( 'date_format' ), $giftcard->get_date_created() ) ); ?></span>
						</div>
						<div class="cell">
							<label><?php esc_html_e( 'Issued value', 'woocommerce-gift-cards' ); ?></label>
							<span><?php echo wp_kses_post( wc_price( $giftcard->get_initial_balance() ) ); ?></span>
						</div>
						<div class="cell">
							<label><?php esc_html_e( 'Order', 'woocommerce-gift-cards' ); ?></label>
							<span><a href="<?php echo esc_url( admin_url( sprintf( 'post.php?post=%d&action=edit', $giftcard->get_order_id() ) ) ); ?>"><?php echo '#' . intval( $giftcard->get_order_id() ); ?></a></span>
						</div>
						<div class="cell">
							<label><?php esc_html_e( 'Redeemed', 'woocommerce-gift-cards' ); ?></label>
							<span><?php echo $giftcard->is_redeemed() ? esc_html( date_i18n( get_option( 'date_format' ), $giftcard->get_date_redeemed() ) ) : '&mdash;'; ?></span>
						</div>
						<div class="cell">
							<label><?php esc_html_e( 'Redeemed by', 'woocommerce-gift-cards' ); ?></label>
							<span>
							<?php
								$value = '&mdash;';
							if ( $giftcard->is_redeemed() ) {
								$user = get_user_by( 'id', $giftcard->get_redeemed_by() );

								if ( is_a( $user, 'WP_User' ) ) {
									$value = sprintf( '<a href="%s">%s</a>', esc_url( get_edit_user_link( $user->ID ) ), esc_html( $user->display_name ) );
								}
							}
								echo wp_kses_post( $value );
							?>
							</span>
						</div>
					</div>

					<div class="wp-clearfix"></div>

					<div class="gift-card-data__row gift-card-data__row--columns">

						<div class="gift-card-data__form-field">
							<label for="sender">
								<?php esc_html_e( 'From:', 'woocommerce-gift-cards' ); ?>
								<?php if ( $giftcard->get_sender_email() ) { ?>
									<a href="<?php echo esc_url( admin_url( self::PAGE_URL . '&s=' . urlencode( $giftcard->get_sender_email() ) ) ); ?>"><?php esc_html_e( 'View other gift cards', 'woocommerce-gift-cards' ); ?> &rarr;</a>
								<?php } ?>
							</label>
							<input type="text" name="sender" value="<?php echo esc_attr( $giftcard->get_sender() ); ?>"<?php echo $giftcard->is_redeemed() ? ' disabled' : ''; ?>>
						</div>

						<div class="gift-card-data__form-field">
							<label for="recipient">
								<?php esc_html_e( 'To:', 'woocommerce-gift-cards' ); ?>
								<a href="<?php echo esc_url( admin_url( self::PAGE_URL . '&s=' . urlencode( $giftcard->get_recipient() ) ) ); ?>"><?php esc_html_e( 'View other gift cards', 'woocommerce-gift-cards' ); ?> &rarr;</a>
								<?php
								$recipient_user = get_user_by( 'email', $giftcard->get_recipient() );
								if ( $recipient_user ) {
									?>
									<a href="<?php echo esc_url( get_edit_user_link( $recipient_user->ID ) ); ?>">View Profile &rarr;</a>
								<?php } ?>
							</label>
							<input type="text" name="recipient" value="<?php echo esc_attr( $giftcard->get_recipient() ); ?>"<?php echo $giftcard->is_redeemed() ? ' disabled' : ''; ?>>
						</div>

					</div><!-- #row -->

					<div class="gift-card-data__row">
						<div class="gift-card-data__form-field">
							<?php echo self::get_message_field_html( $giftcard ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
					</div><!-- #row -->


					<div class="gift-card-data__row gift-card-data__row--columns">
						<?php
							/**
							 * `woocommerce_gc_admin_delivery_date_is_editable` filter.
							 *
							 * @since  1.8.1
							 *
							 * @return bool
							 */
							$is_enabled = (bool) apply_filters( 'woocommerce_gc_admin_delivery_date_is_editable', false === $giftcard->is_delivered(), $giftcard );
						?>
						<div class="gift-card-data__form-field date-picker__field">
							<label for="deliver_date_day"><?php esc_html_e( 'Delivery date:', 'woocommerce-gift-cards' ); ?></label>
							<input type="text" autocomplete="off" class="date-picker" name="deliver_date_day" placeholder="<?php esc_attr_e( 'date', 'woocommerce-gift-cards' ); ?>" maxlength="10" value="<?php echo esc_attr( $giftcard->get_deliver_date() ? date_i18n( 'Y-m-d', $giftcard->get_deliver_date() ) : '' ); ?>" pattern="<?php echo esc_attr( apply_filters( 'woocommerce_date_input_html_pattern', '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])' ) ); ?>" <?php echo ! $is_enabled ? ' disabled' : ''; ?>/>@
							&lrm;
							<input type="number" class="hour" placeholder="<?php esc_attr_e( 'h', 'woocommerce-gift-cards' ); ?>" name="deliver_date_hour" min="0" max="23" step="1" value="<?php echo esc_attr( $giftcard->get_deliver_date() ? date_i18n( 'H', $giftcard->get_deliver_date() ) : '' ); ?>" pattern="([01]?[0-9]{1}|2[0-3]{1})" <?php echo ! $is_enabled ? ' disabled' : ''; ?>/>:
							<input type="number" class="minute" placeholder="<?php esc_attr_e( 'm', 'woocommerce-gift-cards' ); ?>" name="deliver_date_minute" min="0" max="59" step="1" value="<?php echo esc_attr( $giftcard->get_deliver_date() ? date_i18n( 'i', $giftcard->get_deliver_date() ) : '' ); ?>" pattern="[0-5]{1}[0-9]{1}" <?php echo ! $is_enabled ? ' disabled' : ''; ?>/>
						</div>

						<div class="gift-card-data__form-field date-picker__field">
							<label for="expire_date_day"><?php esc_html_e( 'Expiration date:', 'woocommerce-gift-cards' ); ?></label>
							<input type="text" autocomplete="off" class="date-picker" name="expire_date_day" placeholder="<?php esc_attr_e( 'date', 'woocommerce-gift-cards' ); ?>" maxlength="10" value="<?php echo esc_attr( $giftcard->get_expire_date() ? date_i18n( 'Y-m-d', $giftcard->get_expire_date() ) : '' ); ?>" pattern="<?php echo esc_attr( apply_filters( 'woocommerce_date_input_html_pattern', '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])' ) ); ?>"/>@
							&lrm;
							<input type="number" class="hour" placeholder="<?php esc_attr_e( 'h', 'woocommerce-gift-cards' ); ?>" name="expire_date_hour" min="0" max="23" step="1" value="<?php echo esc_attr( $giftcard->get_expire_date() ? date_i18n( 'H', $giftcard->get_expire_date() ) : '' ); ?>" pattern="([01]?[0-9]{1}|2[0-3]{1})" />:
							<input type="number" class="minute" placeholder="<?php esc_attr_e( 'm', 'woocommerce-gift-cards' ); ?>" name="expire_date_minute" min="0" max="59" step="1" value="<?php echo esc_attr( $giftcard->get_expire_date() ? date_i18n( 'i', $giftcard->get_expire_date() ) : '' ); ?>" pattern="[0-5]{1}[0-9]{1}" />
						</div>

					</div><!-- #row -->

				</div><!-- .postbox -->

				<h2 class="activity-table-title"><?php esc_html_e( 'Activity', 'woocommerce-gift-cards' ); ?></h2>
				<input type="hidden" name="page" value="<?php echo isset( $_REQUEST['page'] ) ? intval( $_REQUEST['page'] ) : 1; ?>"/>

				<?php $activity_table->display(); ?>

			</div><!-- #container2 -->

		</div><!-- #post-body -->
	</div>

	</form>

</div>
