<?php
/**
 * WC_GC_Gift_Cards_Query_Ability class
 *
 * @package WooCommerce Gift Cards
 * @since   2.7.4
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_GC_Gift_Cards_Query_Ability ability definition.
 *
 * @version 2.7.4
 */
class WC_GC_Gift_Cards_Query_Ability implements \Automattic\WooCommerce\Abilities\AbilityDefinition {

	/**
	 * Gift cards query ability ID.
	 */
	const NAME = 'woocommerce-gift-cards/gift-cards-query';

	/**
	 * Ability category.
	 */
	const CATEGORY = 'woocommerce';

	/**
	 * Gift cards collection orderby values.
	 */
	private const ORDERBY_VALUES = array(
		'id',
		'create_date',
		'deliver_date',
		'balance',
		'remaining',
		'order_id',
	);

	/**
	 * Get the ability name.
	 *
	 * @return string
	 */
	public static function get_name(): string {
		return self::NAME;
	}

	/**
	 * Get the ability registration arguments.
	 *
	 * @return array
	 */
	public static function get_registration_args(): array {
		return array(
			'label'               => __( 'Query gift cards', 'woocommerce-gift-cards' ),
			'description'         => __( 'Find gift cards by ID or page through gift cards visible to store managers.', 'woocommerce-gift-cards' ),
			'category'            => self::CATEGORY,
			'input_schema'        => self::get_input_schema(),
			'output_schema'       => self::get_output_schema(),
			'execute_callback'    => array( __CLASS__, 'execute' ),
			'permission_callback' => array( __CLASS__, 'can_query_gift_cards' ),
			'meta'                => self::get_meta(),
		);
	}

	/**
	 * Check whether the current user can query gift cards.
	 *
	 * @param  array $input Ability input.
	 * @return bool
	 */
	public static function can_query_gift_cards( $input = array() ) {
		$permission = current_user_can( 'manage_woocommerce' );

		/**
		 * Filters whether the current user can query gift cards via the Abilities API.
		 *
		 * @since 2.7.4
		 *
		 * @param bool  $permission Whether the current user can query gift cards.
		 * @param array $input      Ability input.
		 */
		return (bool) apply_filters( 'woocommerce_gc_ability_can_query_gift_cards', $permission, is_array( $input ) ? $input : array() );
	}

	/**
	 * Query gift cards.
	 *
	 * @param  array $input Ability input.
	 * @return array|WP_Error
	 */
	public static function execute( $input = array() ) {
		$input = is_array( $input ) ? $input : array();

		if ( ! empty( $input['id'] ) ) {
			$result = self::get_gift_card_result( (int) $input['id'] );
		} else {
			$result = self::get_gift_cards_result( $input );
		}

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$result['gift_cards'] = array_map( array( __CLASS__, 'format_gift_card_response' ), $result['gift_cards'] );

		return $result;
	}

	/**
	 * Get read-only tool metadata.
	 *
	 * @return array
	 */
	private static function get_meta(): array {
		return array(
			'show_in_rest' => true,
			'mcp'          => array(
				'public' => true,
				'type'   => 'tool',
			),
			'annotations'  => array(
				'readonly'    => true,
				'destructive' => false,
				'idempotent'  => true,
			),
		);
	}

	/**
	 * Get the gift cards query input schema.
	 *
	 * @return array
	 */
	private static function get_input_schema(): array {
		return array(
			'type'                 => 'object',
			'properties'           => array(
				'id'       => array(
					'type'        => 'integer',
					'minimum'     => 1,
					'description' => __( 'Gift card ID. When set, returns only this gift card.', 'woocommerce-gift-cards' ),
				),
				'page'     => array(
					'type'        => 'integer',
					'default'     => 1,
					'minimum'     => 1,
					'description' => __( 'Current result page.', 'woocommerce-gift-cards' ),
				),
				'per_page' => array(
					'type'        => 'integer',
					'default'     => 10,
					'minimum'     => 1,
					'maximum'     => 100,
					'description' => __( 'Maximum number of gift cards to return for collection queries.', 'woocommerce-gift-cards' ),
				),
				'orderby'  => array(
					'type'        => 'string',
					'default'     => 'id',
					'enum'        => self::ORDERBY_VALUES,
					'description' => __( 'Sort collection by gift card attribute.', 'woocommerce-gift-cards' ),
				),
				'order'    => array(
					'type'        => 'string',
					'default'     => 'desc',
					'enum'        => array( 'asc', 'desc' ),
					'description' => __( 'Order sort attribute ascending or descending.', 'woocommerce-gift-cards' ),
				),
			),
			'additionalProperties' => false,
			'default'              => array(),
		);
	}

	/**
	 * Get the gift cards query output schema.
	 *
	 * @return array
	 */
	private static function get_output_schema(): array {
		return array(
			'type'                 => 'object',
			'properties'           => array(
				'gift_cards'  => array(
					'type'        => 'array',
					'description' => __( 'Returned gift cards for the current query.', 'woocommerce-gift-cards' ),
					'items'       => self::get_gift_card_output_schema(),
				),
				'total'       => array(
					'type'        => 'integer',
					'description' => __( 'Total number of gift cards available for the current query.', 'woocommerce-gift-cards' ),
				),
				'total_pages' => array(
					'type'        => 'integer',
					'description' => __( 'Total number of result pages available for the current query.', 'woocommerce-gift-cards' ),
				),
				'page'        => array(
					'type'        => 'integer',
					'description' => __( 'Current result page.', 'woocommerce-gift-cards' ),
				),
				'per_page'    => array(
					'type'        => 'integer',
					'description' => __( 'Maximum number of gift cards requested per page.', 'woocommerce-gift-cards' ),
				),
			),
			'required'             => array( 'gift_cards', 'total', 'total_pages', 'page', 'per_page' ),
			'additionalProperties' => false,
		);
	}

	/**
	 * Get the gift card item output schema.
	 *
	 * @return array
	 */
	private static function get_gift_card_output_schema(): array {
		return array(
			'type'                 => 'object',
			'properties'           => array(
				'id'            => array(
					'type'        => 'integer',
					'description' => __( 'Gift card ID.', 'woocommerce-gift-cards' ),
				),
				'code'          => array(
					'type'        => 'string',
					'description' => __( 'Gift card code, masked when the current user cannot view unmasked gift card codes.', 'woocommerce-gift-cards' ),
				),
				'recipient'     => array(
					'type'        => 'string',
					'description' => __( 'Gift card recipient name or email as stored on the gift card.', 'woocommerce-gift-cards' ),
				),
				'sender'        => array(
					'type'        => 'string',
					'description' => __( 'Gift card sender name.', 'woocommerce-gift-cards' ),
				),
				'sender_email'  => array(
					'type'        => 'string',
					'description' => __( 'Gift card sender email address.', 'woocommerce-gift-cards' ),
				),
				'message'       => array(
					'type'        => 'string',
					'description' => __( 'Gift card message, omitted when the current user cannot view gift card messages.', 'woocommerce-gift-cards' ),
				),
				'balance'       => array(
					'type'        => 'number',
					'description' => __( 'Original gift card balance.', 'woocommerce-gift-cards' ),
				),
				'remaining'     => array(
					'type'        => 'number',
					'description' => __( 'Remaining gift card balance.', 'woocommerce-gift-cards' ),
				),
				'order_id'      => array(
					'type'        => 'integer',
					'description' => __( 'Order ID associated with the gift card, or 0 when no order is linked.', 'woocommerce-gift-cards' ),
				),
				'order_item_id' => array(
					'type'        => 'integer',
					'description' => __( 'Order item ID associated with the gift card, or 0 when no order item is linked.', 'woocommerce-gift-cards' ),
				),
				'create_date'   => array(
					'type'        => array( 'string', 'integer' ),
					'description' => __( 'Gift card creation date as a site-local date string, or 0 when unavailable.', 'woocommerce-gift-cards' ),
				),
				'deliver_date'  => array(
					'type'        => array( 'string', 'integer' ),
					'description' => __( 'Scheduled delivery date as a site-local date string, or 0 when not scheduled.', 'woocommerce-gift-cards' ),
				),
				'expire_date'   => array(
					'type'        => array( 'string', 'integer' ),
					'description' => __( 'Expiration date as a site-local date string, or 0 when the gift card does not expire.', 'woocommerce-gift-cards' ),
				),
				'redeem_date'   => array(
					'type'        => array( 'string', 'integer' ),
					'description' => __( 'Redemption date as a site-local date string, or 0 when the gift card has not been redeemed.', 'woocommerce-gift-cards' ),
				),
				'redeemed_by'   => array(
					'type'        => 'integer',
					'description' => __( 'User ID that redeemed the gift card, or 0 when not redeemed by a user.', 'woocommerce-gift-cards' ),
				),
				'currency'      => array(
					'type'        => 'string',
					'description' => __( 'Currency code for the gift card balance.', 'woocommerce-gift-cards' ),
				),
				'delivered'     => array(
					'type'        => array( 'string', 'integer' ),
					'description' => __( 'Gift card delivery status as stored by Gift Cards.', 'woocommerce-gift-cards' ),
				),
				'is_active'     => array(
					'type'        => 'string',
					'enum'        => array( 'on', 'off' ),
					'description' => __( 'Whether the gift card is active.', 'woocommerce-gift-cards' ),
				),
			),
			'required'             => array(
				'id',
				'code',
				'recipient',
				'sender',
				'sender_email',
				'balance',
				'remaining',
				'order_id',
				'order_item_id',
				'create_date',
				'deliver_date',
				'expire_date',
				'redeem_date',
				'redeemed_by',
				'currency',
				'delivered',
				'is_active',
			),
			'additionalProperties' => false,
		);
	}

	/**
	 * Get a single gift card result.
	 *
	 * @param  int $gift_card_id Gift card ID.
	 * @return array|WP_Error
	 */
	private static function get_gift_card_result( $gift_card_id ) {
		$gift_card = wc_gc_get_gift_card( $gift_card_id );

		if ( false === $gift_card ) {
			return new WP_Error(
				'woocommerce_gc_gift_card_not_found',
				__( 'Resource does not exist.', 'woocommerce-gift-cards' ),
				array( 'status' => 404 )
			);
		}

		return array(
			'gift_cards'  => array( $gift_card ),
			'total'       => 1,
			'total_pages' => 1,
			'page'        => 1,
			'per_page'    => 1,
		);
	}

	/**
	 * Get a gift cards collection result.
	 *
	 * @param  array $input Query input.
	 * @return array
	 */
	private static function get_gift_cards_result( $input ) {
		$page     = isset( $input['page'] ) ? (int) $input['page'] : 1;
		$per_page = isset( $input['per_page'] ) ? (int) $input['per_page'] : 10;
		$orderby  = isset( $input['orderby'] ) && in_array( $input['orderby'], self::ORDERBY_VALUES, true )
			? $input['orderby']
			: 'id';
		$order    = isset( $input['order'] ) && in_array( $input['order'], array( 'asc', 'desc' ), true )
			? $input['order']
			: 'desc';

		$total_rows = (int) WC_GC()->db->giftcards->query(
			array(
				'count' => true,
			)
		);

		$gift_cards = WC_GC()->db->giftcards->query(
			array(
				'return'   => 'objects',
				'order_by' => array(
					$orderby => $order,
				),
				'offset'   => ( $page - 1 ) * $per_page,
				'limit'    => $per_page,
			)
		);

		return array(
			'gift_cards'  => is_array( $gift_cards ) ? $gift_cards : array(),
			'total'       => $total_rows,
			'total_pages' => (int) ceil( $total_rows / $per_page ),
			'page'        => $page,
			'per_page'    => $per_page,
		);
	}

	/**
	 * Format a gift card data object for the Gift Cards query ability contract.
	 *
	 * Keep the externally projected ability response explicit, separately from REST response shape.
	 *
	 * @param  WC_GC_Gift_Card_Data $gift_card Gift card data object.
	 * @return array
	 */
	private static function format_gift_card_response( $gift_card ) {
		$formatted = array(
			'id'            => $gift_card->get_id(),
			'code'          => $gift_card->get_code(),
			'recipient'     => $gift_card->get_recipient(),
			'sender'        => $gift_card->get_sender(),
			'sender_email'  => $gift_card->get_sender_email(),
			'message'       => $gift_card->get_message(),
			'balance'       => $gift_card->get_initial_balance(),
			'remaining'     => $gift_card->get_balance(),
			'order_id'      => $gift_card->get_order_id(),
			'order_item_id' => $gift_card->get_order_item_id(),
			'create_date'   => $gift_card->get_date_created() ? date_i18n( 'Y-m-d H:i:s', $gift_card->get_date_created() ) : 0,
			'deliver_date'  => $gift_card->get_deliver_date() ? date_i18n( 'Y-m-d H:i:s', $gift_card->get_deliver_date() ) : 0,
			'expire_date'   => $gift_card->get_expire_date() ? date_i18n( 'Y-m-d H:i:s', $gift_card->get_expire_date() ) : 0,
			'redeem_date'   => $gift_card->get_date_redeemed() ? date_i18n( 'Y-m-d H:i:s', $gift_card->get_date_redeemed() ) : 0,
			'redeemed_by'   => $gift_card->get_redeemed_by(),
			'currency'      => $gift_card->get_meta( '_currency' ) ? $gift_card->get_meta( '_currency' ) : get_woocommerce_currency(),
			'delivered'     => false === $gift_card->is_delivered() ? 'no' : $gift_card->is_delivered(),
			'is_active'     => $gift_card->is_active() ? 'on' : 'off',
		);

		if ( wc_gc_mask_codes( 'admin' ) ) {
			$formatted['code'] = wc_gc_mask_code( $formatted['code'] );
		}

		if ( self::should_mask_message() ) {
			unset( $formatted['message'] );
		}

		return $formatted;
	}

	/**
	 * Check whether the gift card message should be omitted from ability output.
	 *
	 * @return bool
	 */
	private static function should_mask_message() {
		if ( ! wc_gc_is_site_admin() ) {
			return true;
		}

		/**
		 * Filters whether gift card messages should be masked.
		 *
		 * @since 2.7.4
		 *
		 * The wc_gc_mask_messages() helper treats REST requests as unmasked; keep the admin
		 * privacy default and honor its filter for ability output.
		 *
		 * @param bool    $mask Whether gift card messages should be masked.
		 * @param WP_User $user Current user.
		 */
		return (bool) apply_filters( 'woocommerce_gc_mask_message', false, wp_get_current_user() );
	}
}
