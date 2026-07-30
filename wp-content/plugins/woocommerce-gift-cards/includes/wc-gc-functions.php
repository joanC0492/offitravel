<?php
/**
 * Gift Cards Functions
 *
 * @package  WooCommerce Gift Cards
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
---------------------------------------------------*/
/*
	Data functions.                                  */
/*---------------------------------------------------*/

/**
 * Get activity types.
 *
 * @return array
 */
function wc_gc_get_activity_types() {
	return array(
		'issued'            => __( 'Issue', 'woocommerce-gift-cards' ),
		'redeemed'          => __( 'Redeem', 'woocommerce-gift-cards' ),
		'used'              => __( 'Debit', 'woocommerce-gift-cards' ), // To be renamed to 'debitted'.
		'refunded'          => __( 'Credit', 'woocommerce-gift-cards' ), // To be renamed to 'credited'.
		'manually_refunded' => __( 'Refund', 'woocommerce-gift-cards' ), // To be renamed to `refunded`.
		'refund_reversed'   => __( 'Refund reversal', 'woocommerce-gift-cards' ),
		'reminder_sent'     => __( 'Expiration reminder sent', 'woocommerce-gift-cards' ),
	);
}

/**
 * Get activity type label.
 *
 * @param  string $slug
 * @return string
 */
function wc_gc_get_activity_type_label( $slug ) {

	$types = wc_gc_get_activity_types();

	if ( ! in_array( $slug, array_keys( $types ) ) ) {
		return '-';
	}

	return $types[ $slug ];
}

/**
 * Get Product types that can be used as Gift Cards.
 *
 * @return array
 */
function wc_gc_get_product_types_allowed() {
	return (array) apply_filters(
		'woocommerce_gc_product_types_allowed',
		array(
			'simple',
			'variable',
		)
	);
}

/**
 * Returns all WooCommerce Order statuses that match with a "Pending Payment" state.
 *
 * @since  1.5.4
 *
 * @return array
 */
function wc_gc_get_order_pending_statuses() {

	$pending_statuses = array( 'pending' );

	/**
	 * `woocommerce_gc_order_pending_statuses` filter.
	 *
	 * @since  1.5.4
	 *
	 * @return array
	 */
	return apply_filters( 'woocommerce_gc_order_pending_statuses', $pending_statuses );
}

/*
---------------------------------------------------*/
/*
	DB.                                              */
/*---------------------------------------------------*/

/**
 * Get a gift card object.
 *
 * @since  1.6.0
 *
 * @param  int $giftcard_id
 * @return WC_GC_Gift_Card_Data|false
 */
function wc_gc_get_gift_card( $giftcard_id ) {
	$giftcard = new WC_GC_Gift_Card_Data( absint( $giftcard_id ) );
	if ( is_a( $giftcard, 'WC_GC_Gift_Card_Data' ) && $giftcard->get_id() ) {
		return $giftcard;
	}

	return false;
}

/**
 * Get a gift card object by code.
 *
 * @since  1.6.0
 *
 * @param  string $code
 * @return WC_GC_Gift_Card_Data|false
 */
function wc_gc_get_gift_card_by_code( $code ) {
	$args = array(
		'code'   => $code,
		'limit'  => 1,
		'return' => 'objects',
	);

	$results  = WC_GC()->db->giftcards->query( $args );
	$giftcard = ! empty( $results ) ? array_pop( $results ) : false;

	if ( $giftcard && is_a( $giftcard, 'WC_GC_Gift_Card_Data' ) ) {
		return $giftcard;
	}

	return false;
}

/**
 * Get a notification object.
 *
 * @since  1.6.0
 *
 * @param  array $query_args (Optional)
 * @return array|false Array of WC_GC_Gift_Card_Data objects or false if nothing is found.
 */
function wc_gc_get_gift_cards( $query_args = array() ) {
	if ( ! is_array( $query_args ) ) {
		$query_args = array( $query_args );
	}

	if ( ! isset( $query_args['return'] ) ) {
		$query_args['return'] = 'objects';
	}

	$results = WC_GC()->db->giftcards->query( $query_args );
	if ( $results ) {
		return $results;
	}

	return false;
}

/*
---------------------------------------------------*/
/*
	Template functions.                              */
/*---------------------------------------------------*/

/**
 * Boolean whether or not to mask the gc codes.
 *
 * @param  string $context
 * @return bool
 */
function wc_gc_mask_codes( $context = '' ) {

	$mask = true;
	$user = wp_get_current_user();

	if ( 'admin' === $context && is_a( $user, 'WP_User' ) && wc_gc_is_site_admin() || ( wc_gc_is_shop_manager() && 'yes' === get_option( 'wc_gc_unmask_codes_for_shop_managers', 'no' ) ) ) {
		$mask = false;
	}

	if ( 'account' === $context ) {
		$mask = true;
	}

	if ( 'checkout' === $context ) {
		$mask = true;
	}

	return apply_filters( 'woocommerce_gc_mask_codes', $mask, $context, $user );
}

/**
 * Boolean whether or not to mask the gc messages.
 *
 * @since  1.1.0
 *
 * @return bool
 */
function wc_gc_mask_messages() {

	$mask = true;
	$user = wp_get_current_user();

	if ( is_a( $user, 'WP_User' ) && wc_gc_is_site_admin() ) {
		$mask = false;
	}

	if ( ! is_admin() ) {
		$mask = false;
	}

	return apply_filters( 'woocommerce_gc_mask_message', $mask, $user );
}

/**
 * Parse delimiter-seperated emails string.
 *
 * @param  string $email_string
 * @return array
 */
function wc_gc_parse_email_string( $email_string ) {
	$max_recipients = absint( apply_filters( 'woocommerce_gc_max_recipients_number', 100 ) );
	$regex          = sprintf( '/\s*%s\s*/', preg_quote( wc_gc_get_emails_delimiter() ) );
	$value          = (array) preg_split( $regex, $email_string, $max_recipients, PREG_SPLIT_NO_EMPTY );

	return $value;
}

/**
 * Get emails string delimiter.
 *
 * @since  1.1.5
 *
 * @return string
 */
function wc_gc_get_emails_delimiter() {
	return apply_filters( 'woocommerce_gc_emails_delimiter', ',' );
}

/*
---------------------------------------------------*/
/*
	Utilities.                                       */
/*---------------------------------------------------*/

/**
 * Is site admin helper.
 *
 * @return bool
 */
function wc_gc_is_site_admin() {

	$current_user = wp_get_current_user();
	if ( ! $current_user || ! is_a( $current_user, 'WP_User' ) ) {
		return false;
	}

	return in_array( 'administrator', $current_user->roles );
}

/**
 * Is shop manager helper.
 *
 * @since 1.7.0
 *
 * @return bool
 */
function wc_gc_is_shop_manager() {
	return in_array( 'shop_manager', wp_get_current_user()->roles );
}

/**
 * Front-End components.
 *
 * @return bool
 */
function wc_gc_is_ui_disabled() {

	// Handle for different page contexts.
	$disable_ui = false;
	if ( is_cart() ) {
		$hidden_in_cart = get_option( 'wc_gc_disable_cart_ui', 'yes' );
		$disable_ui     = 'no' === $hidden_in_cart ? false : true;
	}

	return ! apply_filters( 'woocommerce_gc_disable_ui', $disable_ui );
}

/**
 * Store-wide redeeming.
 *
 * @return bool
 */
function wc_gc_is_redeeming_enabled() {

	$enabled = get_option( 'wc_gc_is_redeeming_enabled', 'yes' );
	$enabled = 'no' === $enabled ? false : true;

	return apply_filters( 'woocommerce_gc_is_redeeming_enabled', $enabled );
}

/**
 * Generates a unique 19 character Gift Card code.
 *
 * @return string
 */
function wc_gc_generate_gift_card_code() {

	/**
	 * `wc_gc_gift_card_code_characters_source` filter.
	 *
	 * Use to limit or expand the characters source used for generating gift card codes.
	 * Hint: Source is missing the "I", 1, "0" and "O" tokens for better readability.
	 *
	 * @since  1.7.0
	 *
	 * @param  string $source
	 * @return string
	 */
	$characters_source = (string) apply_filters( 'wc_gc_gift_card_code_characters_source', 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789' );

	// Make sure all characters are uppercase.
	$characters_source = strtoupper( $characters_source );

	// Pre-allocate code memory.
	$code      = '0000000000000000';
	$max_index = strlen( $characters_source ) - 1;

	// Generate code.
	for ( $i = 0; $i < 16; $i++ ) {
		$code[ $i ] = $characters_source[ mt_rand( 0, $max_index ) ];
	}

	// Format.
	$code = sprintf(
		'%s-%s-%s-%s',
		substr( $code, 0, 4 ),
		substr( $code, 4, 4 ),
		substr( $code, 8, 4 ),
		substr( $code, 12, 4 )
	);

	$code = apply_filters( 'wc_gc_gift_card_code', $code );
	return $code;
}

/**
 * Has code pattern.
 *
 * @since 1.6.0
 *
 * @param  string $code
 * @return bool
 */
function wc_gc_is_gift_card_code( $code ) {
	return 1 === preg_match( '/^([a-zA-Z0-9]{4}[\-]){3}[a-zA-Z0-9]{4}$/', $code ) ? true : false;
}

/**
 * Is email format.
 *
 * @since 1.6.0
 *
 * @param  string $value
 * @return bool
 */
function wc_gc_is_email( $value ) {
	return filter_var( $value, FILTER_VALIDATE_EMAIL );
}

/**
 * Generates a unique Gift Card hash.
 *
 * @since 1.0.4
 *
 * @param  string $input
 * @param  string $action
 * @return string
 */
function wc_gc_gift_card_hash( $input, $action ) {

	// Hint: This will be deprecated after dropping support for lt 1.9.0

	$output         = '';
	$encrypt_method = 'AES-256-CBC';
	// Caution: Do not touch secret_key.
	$secret_key = 'secret_wc_gc_key';
	// Caution: Do not touch secret_iv.
	$secret_iv = 'secret_wc_gc_iv';

	// Hash it.
	$key = hash( 'sha256', $secret_key );
	// Tip: Encrypt method AES-256-CBC expects 16 bytes Initialization Vector.
	$iv = substr( hash( 'sha256', $secret_iv ), 0, 16 );

	if ( 'encrypt' === $action ) {
		$output = openssl_encrypt( $input, $encrypt_method, $key, 0, $iv );
		$output = base64_encode( $output );
	} elseif ( 'decrypt' === $action ) {
		$output = openssl_decrypt( base64_decode( $input ), $encrypt_method, $key, 0, $iv );
	}

	return $output;
}

/**
 * Utility function, returns the given status with the `wc-` prefix.
 *
 * @since  1.6.0
 *
 * @param  string $order_status
 * @return string
 */
function wc_gc_prefix_order_status( $order_status ) {
	return 'wc-' . $order_status;
}

/**
 * Utility function, adjusts the RGB brightness steps of a given hex color.
 *
 * @since  1.7.0
 *
 * @param  string $hex
 * @param  int    $steps
 * @return string
 */
function wc_gc_adjust_color_brightness( $hex, $steps ) {

	// Steps should be between -255 and 255. Negative = darker, positive = lighter
	$steps = max( -255, min( 255, $steps ) );
	$hex   = str_replace( '#', '', $hex );

	if ( 3 === strlen( $hex ) ) {
		$hex = str_repeat( substr( $hex, 0, 1 ), 2 ) . str_repeat( substr( $hex, 1, 1 ), 2 ) . str_repeat( substr( $hex, 2, 1 ), 2 );
	}

	// Split into three parts: R, G and B
	$color_parts = str_split( $hex, 2 );
	$return      = '#';

	foreach ( $color_parts as $color ) {
		$color   = hexdec( $color );
		$color   = max( 0, min( 255, $color + $steps ) );
		$return .= str_pad( dechex( $color ), 2, '0', STR_PAD_LEFT );
	}

	return $return;
}

/**
 * Utility function, returns the RGB contrast between two colors.
 *
 * @since  1.7.0
 *
 * @param  string $color_1
 * @param  string $color_2
 * @return string
 */
function wc_gc_get_color_diff( $color_1, $color_2 ) {

	if ( 3 === strlen( $color_1 ) ) {
		$color_1 = str_repeat( substr( $color_1, 0, 1 ), 2 ) . str_repeat( substr( $color_1, 1, 1 ), 2 ) . str_repeat( substr( $color_1, 2, 1 ), 2 );
	}

	if ( 3 === strlen( $color_2 ) ) {
		$color_2 = str_repeat( substr( $color_2, 0, 1 ), 2 ) . str_repeat( substr( $color_2, 1, 1 ), 2 ) . str_repeat( substr( $color_2, 2, 1 ), 2 );
	}

	$color_1       = str_replace( '#', '', $color_1 );
	$color_2       = str_replace( '#', '', $color_2 );
	$color_1_parts = array_map( 'hexdec', str_split( $color_1, 2 ) );
	$color_2_parts = array_map( 'hexdec', str_split( $color_2, 2 ) );

	if ( count( $color_1_parts ) !== 3 && count( $color_2_parts ) !== 3 ) {
		return 0;
	}

	return max( $color_1_parts[0], $color_2_parts[0] ) - min( $color_1_parts[0], $color_2_parts[0] ) + max( $color_1_parts[1], $color_2_parts[1] ) - min( $color_1_parts[1], $color_2_parts[1] ) + max( $color_1_parts[2], $color_2_parts[2] ) - min( $color_1_parts[2], $color_2_parts[2] );
}

/*
---------------------------------------------------*/
/*
	Screen functions.                                */
/*---------------------------------------------------*/

/**
 * Get parent menu.
 *
 * @since 1.6.0
 *
 * @param  string $key
 * @return string
 */
function wc_gc_get_parent_menu() {

	/**
	 * `woocommerce_gc_parent_menu` filter.
	 *
	 * Specify the parent menu item for gift cards.
	 * Available options are {
		- 'woocommerce'
		- 'marketing'
	}
	 *
	 * @since 1.6.0
	 *
	 * @param  string  $parent_menu
	 * @return string
	 */
	$parent_menu              = apply_filters( 'woocommerce_gc_parent_menu', 'marketing' );
	$parent_menu_is_marketing = 'marketing' === $parent_menu;

	// Override if the new WooCommerce menu is enabled.
	if ( $parent_menu_is_marketing && 'yes' === get_option( 'woocommerce_navigation_enabled', 'no' ) ) {
		$parent_menu = 'woocommerce';
	}

	// Override if Marketing feature is disabled.
	if ( $parent_menu_is_marketing && class_exists( 'Automattic\WooCommerce\Admin\Features\Features' ) && ! Automattic\WooCommerce\Admin\Features\Features::is_enabled( 'marketing' ) ) {
		$parent_menu = 'woocommerce';
	}

	return in_array( $parent_menu, array( 'woocommerce', 'marketing' ), true ) ? $parent_menu : 'woocommerce';
}

/**
 * Get formatted screen id.
 *
 * @since 1.5.2
 *
 * @param string $screen_id
 * @param  bool   $replace (Optional) Replace submenu parent
 *
 * @return string
 */
function wc_gc_get_formatted_screen_id( string $screen_id, bool $replace = true ): string {

	if ( $replace && 'marketing' === wc_gc_get_parent_menu() ) {
		$prefix = sanitize_title( __( 'Marketing', 'woocommerce' ) );
	} elseif ( version_compare( WC()->version, '7.3.0' ) < 0 ) {
			$prefix = sanitize_title( __( 'WooCommerce', 'woocommerce' ) );
	} else {
		$prefix = 'woocommerce';
	}

	if ( 0 === strpos( $screen_id, 'woocommerce_' ) ) {
		$screen_id = str_replace( 'woocommerce_', $prefix . '_', $screen_id );
	}

	return $screen_id;
}

/*
---------------------------------------------------*/
/*
	Date functions.                                  */
/*---------------------------------------------------*/

/**
 * Is a unix timestamp.
 *
 * @since 1.2.0
 *
 * @return bool
 */
function wc_gc_is_unix_timestamp( $stamp ) {
	return is_numeric( $stamp ) && (int) $stamp == $stamp && $stamp > 0;
}

/**
 * Takes a timestamp in GMT and converts it to store's timezone.
 *
 * @since 1.3.0
 *
 * @param  int   $timestamp
 * @param  float $offset
 * @return int
 */
function wc_gc_convert_timestamp_to_gmt_offset( $timestamp, $gmt_offset = null ) {

	$store_timestamp = new DateTime();
	$store_timestamp->setTimestamp( $timestamp );

	// Get the Store's offset.
	if ( is_null( $gmt_offset ) ) {
		$gmt_offset = wc_gc_get_gmt_offset();
	}

	$store_timestamp->modify( $gmt_offset * 60 . ' minutes' );

	return $store_timestamp->getTimestamp();
}

/**
 * Get the store's GMT offset.
 *
 * @since  1.3.0
 *
 * @return float
 */
function wc_gc_get_gmt_offset() {
	return (float) get_option( 'gmt_offset' );
}

/**
 * Get the date format for JS.
 *
 * Hint: Doesn't support time formats.
 *
 * @since  1.12.1
 *
 * @param  string $date_format (Optional) Date format in PHP
 * @return string The date format for JS
 */
function wc_gc_get_js_date_format( $date_format = null ) {

	$format       = ! empty( $date_format ) ? $date_format : get_option( 'date_format' );
	$replacements = array(
		'A' => '',
		'a' => '',
		'B' => '',
		'b' => '',
		'C' => '',
		'c' => '',
		'D' => 'D',
		'd' => 'dd',
		'E' => '',
		'e' => '',
		'F' => 'MM',
		'f' => '',
		'G' => '',
		'g' => '',
		'H' => '',
		'h' => '',
		'I' => '',
		'i' => '',
		'J' => '',
		'j' => 'd',
		'K' => '',
		'k' => '',
		'L' => '',
		'l' => 'DD',
		'M' => 'M',
		'm' => 'mm',
		'N' => '',
		'n' => 'm',
		'O' => '',
		'o' => 'yy',
		'P' => '',
		'p' => '',
		'Q' => '',
		'q' => '',
		'R' => '',
		'r' => '', // RFC 2822, No equivalent.
		'S' => '',
		's' => '',
		'T' => 'z',
		't' => '',
		'U' => '@', // Unix timestamp.
		'u' => '',
		'V' => '',
		'v' => '',
		'W' => '',
		'w' => '',
		'X' => '',
		'x' => '',
		'Y' => 'yy',
		'y' => 'y',
		'Z' => '',
		'z' => '',
	);

	// Converts escaped characters.
	foreach ( $replacements as $from => $to ) {
		$replacements[ '\\' . $from ] = '\'' . $from . '\'';
	}

	$js_format = strtr( $format, $replacements );
	// Remove single quotes doubling up -- Hint: This action will not allow single quotes in date format strings.
	$js_format = str_replace( '\'\'', '', $js_format );

	return $js_format;
}


/**
 * Get the delivery timestamp type.
 *
 * @since  1.3.0
 *
 * @return string
 */
function wc_gc_get_date_input_timezone_reference() {

	/**
	 * `woocommerce_gc_date_input_timezone_reference` filter.
	 *
	 * How should the delivery time be set. Available options are:
	 *
	 * @param  string  $reference  {
	 *
	 *     - 'store'  : Show UI with Store's Timezone and keep the date to the Store's timezone.
	 *                  Users select the time based on Store's clock. Foreign visitors need to be warned about this.
	 *
	 *     - 'default': Show UI with Clients's Timezone and convert the date to the Store's timezone.
	 *                  Works best when users are sending gift cards in the same timezone (Default)
	 * }
	 *
	 * @return string
	 */
	return apply_filters( 'woocommerce_gc_date_input_timezone_reference', 'default' );
}
