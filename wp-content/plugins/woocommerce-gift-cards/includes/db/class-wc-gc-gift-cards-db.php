<?php
/**
 * WC_GC_Gift_Cards_DB class
 *
 * @package  WooCommerce Gift Cards
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DB API class.
 *
 * @version  2.2.1
 */
class WC_GC_Gift_Cards_DB {

	/**
	 * Cloning is forbidden.
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Foul!', 'woocommerce-gift-cards' ), '1.0.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Foul!', 'woocommerce-gift-cards' ), '1.0.0' );
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		// ...
	}

	/**
	 * Query gift cards data from the DB.
	 *
	 * @param  array $args  {
	 *     @type  string     $return           Return array format:
	 *
	 *         - 'all': entire row casted to array,
	 *         - 'ids': ids only,
	 *         - 'objects': WC_PRL_Gift_Card_Data objects.
	 *
	 *     @type  bool          $count             Whether or not to count the results and return an integer.
	 *     @type  string        $search            Query with search term.
	 *     @type  bool          $is_redeemed       Query based on redeem status.
	 *     @type  bool          $is_delivered      Query based on delivery status.
	 *     @type  int           $redeemed_by       Redeemed by user id(s) in WHERE clause.
	 *     @type  int           $is_active         Gift Card active status in WHERE clause.
	 *     @type  int           $is_virtual        Gift Card virtual status in WHERE clause.
	 *     @type  int|array     $code              Gift Card code(s) in WHERE clause.
	 *     @type  int|array     $order_id          Gift Card order id(s) in WHERE clause.
	 *     @type  int|array     $order_item_id     Gift Card order item id(s) in WHERE clause.
	 *     @type  int|array     $product_id        Gift Card product id(s) in WHERE clause.
	 *     @type  string|array  $recipient         Gift Card recipient(s) in WHERE clause.
	 *     @type  string|array  $sender            Gift Card sender(s) in WHERE clause.
	 *     @type  string|array  $sender_email      Gift Card senders email(s) in WHERE clause.
	 *     @type  string|array  $template_id       Gift Card template id.
	 *     @type  int           $expired_start     Expired date start in WHERE clause.
	 *     @type  int           $expired_end       Expired date end in WHERE clause.
	 *     @type  int           $last_modify_start Last modify date start in WHERE clause.
	 *     @type  int           $last_modify_end   Last modify date end in WHERE clause.
	 *     @type  int           $start_date        Create date start in WHERE clause.
	 *     @type  int           $end_date          Create date end in WHERE clause.
	 *     @type  array         $order_by          ORDER BY field => order pairs.
	 *     @type  int           $limit             Query limit.
	 *     @type  int           $offset            Query offset.
	 *     @type  array         $meta_query        Meta query array.
	 * }
	 *
	 * @return array
	 */
	public function query( $args ) {
		global $wpdb;

		$args = wp_parse_args(
			$args,
			array(
				'return'            => 'all',
				'count'             => false,
				'search'            => '',
				'is_redeemed'       => null,
				'is_delivered'      => null,
				'redeemed_by'       => 0,
				'is_active'         => '',
				'is_virtual'        => '',
				'code'              => '',
				'order_id'          => 0,
				'order_item_id'     => 0,
				'product_id'        => 0,
				'recipient'         => '',
				'sender'            => '',
				'sender_email'      => '',
				'template_id'       => '',
				'expired_start'     => '',
				'expired_end'       => '',
				'last_modify_start' => '',
				'last_modify_end'   => '',
				'start_date'        => '',
				'end_date'          => '',
				'order_by'          => array( 'id' => 'ASC' ),
				'limit'             => -1,
				'offset'            => -1,
				'meta_query'        => array(), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			)
		);

		$table = $wpdb->prefix . 'woocommerce_gc_cards';

		if ( $args['count'] ) {

			$select = "COUNT( {$table}.id )";

		} elseif ( in_array( $args['return'], array( 'ids', 'objects' ) ) ) {

				$select = $table . '.id';
		} else {
			$select = '*';
		}

		// Build the query.
		$sql      = 'SELECT ' . $select . " FROM {$table}";
		$join     = '';
		$where    = '';
		$order_by = '';

		$where_clauses    = array( '1=1' );
		$where_values     = array();
		$order_by_clauses = array();

		// WHERE clauses.

		if ( $args['code'] ) {

			$codes = is_array( $args['code'] ) ? $args['code'] : array( $args['code'] );
			$codes = array_map( 'esc_sql', $codes );

			$where_clauses[] = "{$table}.code IN ( " . implode( ', ', array_fill( 0, count( $codes ), '%s' ) ) . ' )';
			$where_values    = array_merge( $where_values, $codes );
		}

		if ( ! empty( $args['is_active'] ) ) {

			$active          = ( 'on' === $args['is_active'] ) ? 'on' : 'off';
			$where_clauses[] = "{$table}.is_active = %s";
			$where_values    = array_merge( $where_values, array( $active ) );
		}

		if ( ! empty( $args['is_virtual'] ) ) {

			$virtual         = ( 'on' === $args['is_virtual'] ) ? 'on' : 'off';
			$where_clauses[] = "{$table}.is_virtual = %s";
			$where_values    = array_merge( $where_values, array( $virtual ) );
		}

		if ( $args['order_id'] ) {
			$order_ids = array_map( 'absint', is_array( $args['order_id'] ) ? $args['order_id'] : array( $args['order_id'] ) );
			$order_ids = array_map( 'esc_sql', $order_ids );

			$where_clauses[] = "{$table}.order_id IN ( " . implode( ', ', array_fill( 0, count( $order_ids ), '%d' ) ) . ' )';
			$where_values    = array_merge( $where_values, $order_ids );
		}

		if ( $args['order_item_id'] ) {
			$order_item_ids = array_map( 'absint', is_array( $args['order_item_id'] ) ? $args['order_item_id'] : array( $args['order_item_id'] ) );
			$order_item_ids = array_map( 'esc_sql', $order_item_ids );

			$where_clauses[] = "{$table}.order_item_id IN ( " . implode( ', ', array_fill( 0, count( $order_item_ids ), '%d' ) ) . ' )';
			$where_values    = array_merge( $where_values, $order_item_ids );
		}

		if ( $args['product_id'] ) {
			$product_ids = array_map( 'absint', is_array( $args['product_id'] ) ? $args['product_id'] : array( $args['product_id'] ) );
			$product_ids = array_map( 'esc_sql', $product_ids );

			$where_clauses[] = 'order_itemmeta.meta_value IN ( ' . implode( ', ', array_fill( 0, count( $product_ids ), '%d' ) ) . ' )';
			$where_values    = array_merge( $where_values, $product_ids );
		}

		if ( $args['recipient'] ) {

			$recipients = is_array( $args['recipient'] ) ? $args['recipient'] : array( $args['recipient'] );
			$recipients = array_map( 'esc_sql', $recipients );

			$where_clauses[] = "{$table}.recipient IN ( " . implode( ', ', array_fill( 0, count( $recipients ), '%s' ) ) . ' )';
			$where_values    = array_merge( $where_values, $recipients );
		}

		if ( $args['sender'] ) {

			$senders = is_array( $args['sender'] ) ? $args['sender'] : array( $args['sender'] );
			$senders = array_map( 'esc_sql', $senders );

			$where_clauses[] = "{$table}.recipient IN ( " . implode( ', ', array_fill( 0, count( $senders ), '%s' ) ) . ' )';
			$where_values    = array_merge( $where_values, $senders );
		}

		if ( $args['sender_email'] ) {

			$sender_emails = is_array( $args['sender_email'] ) ? $args['sender_email'] : array( $args['sender_email'] );
			$sender_emails = array_map( 'esc_sql', $sender_emails );

			$where_clauses[] = "{$table}.sender_email IN ( " . implode( ', ', array_fill( 0, count( $sender_emails ), '%s' ) ) . ' )';
			$where_values    = array_merge( $where_values, $sender_emails );
		}

		if ( ! empty( $args['template_id'] ) ) {

			$template_ids = is_array( $args['template_id'] ) ? $args['template_id'] : array( $args['template_id'] );
			$template_ids = array_map( 'esc_sql', $template_ids );

			$where_clauses[] = "{$table}.template_id IN ( " . implode( ', ', array_fill( 0, count( $template_ids ), '%s' ) ) . ' )';
			$where_values    = array_merge( $where_values, $template_ids );
		}

		if ( ! is_null( $args['is_redeemed'] ) ) {

			$redeemed_op     = true === $args['is_redeemed'] ? '>' : '=';
			$where_clauses[] = "{$table}.redeem_date " . $redeemed_op . ' 0';
		}

		if ( ! is_null( $args['is_delivered'] ) ) {

			$redeemed_op     = true === $args['is_delivered'] ? '<>' : '=';
			$where_clauses[] = "{$table}.delivered " . $redeemed_op . " 'no'";
		}

		if ( $args['redeemed_by'] ) {
			$user_ids = array_map( 'absint', is_array( $args['redeemed_by'] ) ? $args['redeemed_by'] : array( $args['redeemed_by'] ) );
			$user_ids = array_map( 'esc_sql', $user_ids );

			$where_clauses[] = "{$table}.redeemed_by IN ( " . implode( ', ', array_fill( 0, count( $user_ids ), '%d' ) ) . ' )';
			$where_values    = array_merge( $where_values, $user_ids );
		}

		if ( $args['search'] ) {
			$s               = esc_sql( '%' . $args['search'] . '%' );
			$where_clauses[] = "( {$table}.code LIKE %s OR {$table}.sender LIKE %s OR {$table}.recipient LIKE %s OR {$table}.sender_email LIKE %s )";
			$where_values    = array_merge( $where_values, array_fill( 0, 4, $s ) );
		}

		if ( $args['expired_start'] ) {
			$start_date      = absint( $args['expired_start'] );
			$where_clauses[] = "{$table}.expire_date >= %d";
			$where_values    = array_merge( $where_values, array( $start_date ) );
		}

		if ( $args['expired_end'] ) {
			$end_date        = absint( $args['expired_end'] );
			$where_clauses[] = "{$table}.expire_date < %d";
			$where_values    = array_merge( $where_values, array( $end_date ) );
		}

		if ( $args['last_modify_start'] ) {
			$start_date      = absint( $args['last_modify_start'] );
			$where_clauses[] = "{$table}.last_modify_date >= %d";
			$where_values    = array_merge( $where_values, array( $start_date ) );
		}

		if ( $args['last_modify_end'] ) {
			$end_date        = absint( $args['last_modify_end'] );
			$where_clauses[] = "{$table}.last_modify_date < %d";
			$where_values    = array_merge( $where_values, array( $end_date ) );
		}

		if ( $args['start_date'] ) {
			$start_date      = absint( $args['start_date'] );
			$where_clauses[] = "{$table}.create_date >= %d";
			$where_values    = array_merge( $where_values, array( $start_date ) );
		}

		if ( $args['end_date'] ) {
			$end_date        = absint( $args['end_date'] );
			$where_clauses[] = "{$table}.create_date < %d";
			$where_values    = array_merge( $where_values, array( $end_date ) );
		}

		// Utilities.
		if ( isset( $args['has_remaining_balance'] ) ) {
			if ( true === $args['has_remaining_balance'] ) {
				$where_clauses[] = "{$table}.remaining > 0";
			} elseif ( false === $args['has_remaining_balance'] ) {
				$where_clauses[] = "{$table}.remaining = 0";
			}
		}

		if ( isset( $args['has_expired'] ) ) {
			if ( true === $args['has_expired'] ) {
				$where_clauses[] = "{$table}.expire_date <= " . time();
			} elseif ( false === $args['has_expired'] ) {
				$where_clauses[] = "( {$table}.expire_date = 0 OR {$table}.expire_date > " . time() . ' )';
			}
		}

		// ORDER BY clauses.
		if ( $args['order_by'] && is_array( $args['order_by'] ) ) {
			foreach ( $args['order_by'] as $what => $how ) {
				$order_by_clauses[] = $table . '.' . esc_sql( strval( $what ) ) . ' ' . esc_sql( strval( $how ) );
			}
		}

		$order_by_clauses = empty( $order_by_clauses ) ? array( $table . '.id, ASC' ) : $order_by_clauses;

		// JOIN.
		if ( ! empty( $args['product_id'] ) ) {
			$join = " INNER JOIN {$wpdb->order_itemmeta} as order_itemmeta ON {$table}.order_item_id = order_itemmeta.order_item_id";
			// Add only meta product_id in the join.
			$where_clauses[] = "order_itemmeta.meta_key = '_product_id'";
		}

		// Build SQL query components.
		$where    = ' WHERE ' . implode( ' AND ', $where_clauses );
		$order_by = ' ORDER BY ' . implode( ', ', $order_by_clauses );
		$limit    = $args['limit'] > 0 ? ' LIMIT ' . absint( $args['limit'] ) : '';
		$offset   = $args['offset'] > 0 ? ' OFFSET ' . absint( $args['offset'] ) : '';

		// Append meta query SQL components.
		if ( $args['meta_query'] && is_array( $args['meta_query'] ) ) {

			$meta_query = new WP_Meta_Query();

			$meta_query->parse_query_vars( $args );

			$meta_sql = $meta_query->get_sql( 'gc_giftcard', $table, 'id' );

			if ( ! empty( $meta_sql ) ) {
				// Meta query JOIN clauses.
				if ( ! empty( $meta_sql['join'] ) ) {
					$join = $meta_sql['join'];
				}
				// Meta query WHERE clauses.
				if ( ! empty( $meta_sql['where'] ) ) {
					$where .= $meta_sql['where'];
				}
			}
		}

		// Assemble and run the query.
		$sql .= $join . $where . $order_by . $limit . $offset;

		/**
		 * WordPress.DB.PreparedSQL.NotPrepared explained.
		 *
		 * The sniff isn't smart enough to follow $sql variable back to its source. So it doesn't know whether the query in $sql incorporates user-supplied values or not.
		 * Whitelisting comment is the solution here. @see https://github.com/WordPress/WordPress-Coding-Standards/issues/469
		 */

		if ( $args['count'] ) {
			if ( empty( $where_values ) ) {
				$count = absint( $wpdb->get_var( $sql ) ); // @phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			} else {
				$count = absint( $wpdb->get_var( $wpdb->prepare( $sql, $where_values ) ) ); // @phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			}
			return $count;
		} elseif ( empty( $where_values ) ) {
				$results = $wpdb->get_results( $sql ); // @phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		} else {
			$results = $wpdb->get_results( $wpdb->prepare( $sql, $where_values ) ); // @phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		}

		if ( empty( $results ) ) {
			return array();
		}

		$a = array();

		if ( 'objects' === $args['return'] ) {
			foreach ( $results as $result ) {
				$a[] = self::get( $result->id );
			}
		} elseif ( 'ids' === $args['return'] ) {
			foreach ( $results as $result ) {
				$a[] = (int) $result->id;
			}
		} else {
			foreach ( $results as $result ) {
				$a[] = (array) $result;
			}
		}

		return $a;
	}

	/**
	 * Get a record from the DB.
	 *
	 * @param  mixed $giftcard
	 * @return false|WC_GC_Gift_Card_Data
	 */
	public function get( $giftcard ) {

		if ( is_numeric( $giftcard ) ) {
			$giftcard = absint( $giftcard );
			$giftcard = new WC_GC_Gift_Card_Data( $giftcard );
		} elseif ( $giftcard instanceof WC_GC_Gift_Card_Data ) {
			$giftcard = new WC_GC_Gift_Card_Data( $giftcard );
		} else {
			$giftcard = false;
		}

		if ( ! $giftcard || ! is_object( $giftcard ) || ! $giftcard->get_id() ) {
			return false;
		}

		return $giftcard;
	}

	/**
	 * Get a giftcard from the DB using hash.
	 *
	 * @since 1.1.0
	 *
	 * @param  string $hash
	 * @return false|WC_GC_Gift_Card_Data
	 */
	public function get_by_hash( $hash ) {

		// Hint: This will be deprecated after dropping support for lt 1.9.0

		$hash  = urldecode( $hash );
		$input = wc_gc_gift_card_hash( $hash, 'decrypt' );
		$ids   = explode( '-', $input );

		if ( empty( $ids ) || count( $ids ) !== 3 ) {
			return false;
		}

		$giftcard = $this->get( absint( $ids[0] ) );
		if ( $giftcard && $giftcard->get_id() && absint( $ids[1] ) === $giftcard->get_order_id() && absint( $ids[2] ) === $giftcard->get_order_item_id() ) {
			return $giftcard;
		}

		return false;
	}

	/**
	 * Create a record in the DB.
	 *
	 * @param  array $args
	 * @return false|int
	 *
	 * @throws Exception
	 */
	public function add( $args ) {

		$args = wp_parse_args(
			$args,
			array(
				'is_active'        => 'on',
				'is_virtual'       => 'on',
				'code'             => '',
				'order_id'         => 0,
				'order_item_id'    => 0,
				'recipient'        => '',
				'redeemed_by'      => 0,
				'sender'           => '',
				'sender_email'     => '',
				'message'          => '',
				'balance'          => 0,
				'remaining'        => 0,
				'template_id'      => 'default',
				'create_date'      => 0,
				'deliver_date'     => 0,
				'delivered'        => 'no',
				'expire_date'      => apply_filters( 'woocommerce_gc_default_expiration_time_from_now', 0 ),
				'redeem_date'      => 0,
				'meta_data'        => array(),
				'last_modify_date' => 0,
			)
		);

		// Empty attributes.
		if ( empty( $args['order_id'] ) || empty( $args['order_item_id'] ) || empty( $args['recipient'] ) || empty( $args['sender'] ) ) {
			throw new Exception( __( 'Missing gift card attributes.', 'woocommerce-gift-cards' ) );
		}

		$this->validate( $args );

		$giftcard = new WC_GC_Gift_Card_Data(
			array(
				'is_active'        => 'on' === $args['is_active'] ? 'on' : 'off',
				'is_virtual'       => 'on' === $args['is_virtual'] ? 'on' : 'off',
				'code'             => $args['code'],
				'order_id'         => $args['order_id'],
				'order_item_id'    => $args['order_item_id'],
				'recipient'        => $args['recipient'],
				'redeemed_by'      => $args['redeemed_by'],
				'sender'           => $args['sender'],
				'sender_email'     => $args['sender_email'],
				'message'          => $args['message'],
				'balance'          => $args['balance'],
				'remaining'        => $args['remaining'],
				'template_id'      => $args['template_id'],
				'create_date'      => $args['create_date'], // Filled automatically.
				'deliver_date'     => $args['deliver_date'],
				'delivered'        => $args['delivered'],
				'expire_date'      => $args['expire_date'],
				'redeem_date'      => $args['redeem_date'],
				'meta_data'        => $args['meta_data'],
				'last_modify_date' => $args['last_modify_date'],
			)
		);

		return $giftcard->save();
	}

	/**
	 * Update a record in the DB.
	 *
	 * @param  mixed $giftcard
	 * @param  array $args
	 * @return bool
	 *
	 * @throws Exception
	 */
	public function update( $giftcard, $args ) {

		if ( is_numeric( $giftcard ) ) {
			$giftcard = absint( $giftcard );
			$giftcard = new WC_GC_Gift_Card_Data( $giftcard );
		}

		if ( is_object( $giftcard ) && $giftcard->get_id() && ! empty( $args ) && is_array( $args ) ) {

			$this->validate( $args, $giftcard );

			$giftcard->set_all( $args );

			return $giftcard->save();
		}

		return false;
	}

	/**
	 * Validate data.
	 *
	 * @param  array &$args
	 * @return void
	 *
	 * @throws Exception
	 */
	public function validate( &$args, $giftcard = false ) {

		// Grap context.
		$context = 'cart';
		if ( isset( $args['context'] ) ) {
			$context = in_array( $context, array( 'cart', 'import' ) ) ? $args['context'] : 'cart';
			unset( $args['context'] );
		}

		if ( ! empty( $args['code'] ) ) {

			if ( ! wc_gc_is_gift_card_code( $args['code'] ) ) {
				throw new Exception( __( 'Invalid gift card code.', 'woocommerce-gift-cards' ) );
			}

			$giftcards_count = $this->query(
				array(
					'code'  => $args['code'],
					'count' => true,
				)
			);
			if ( 0 !== $giftcards_count ) {
				/* translators: Gift Card code */
				throw new Exception( sprintf( __( 'Gift card code `%s` exists.', 'woocommerce-gift-cards' ), $args['code'] ) );
			}
		}

		if ( ! empty( $args['recipient'] ) && ! filter_var( $args['recipient'], FILTER_VALIDATE_EMAIL ) ) {
			/* translators: %s: Email string */
			throw new Exception( sprintf( __( 'Recipient `%s` is an invalid email.', 'woocommerce-gift-cards' ), $args['recipient'] ) );
		}

		if ( ! empty( $args['sender_email'] ) && ! filter_var( $args['sender_email'], FILTER_VALIDATE_EMAIL ) ) {
			/* translators: %s: Email string */
			throw new Exception( sprintf( __( 'Sender `%s` is an invalid email.', 'woocommerce-gift-cards' ), $args['sender_email'] ) );
		}

		// New Gift Card.
		if ( ! is_a( $giftcard, 'WC_GC_Gift_Card_Data' ) || ! $giftcard->get_id() ) {

			// Generate code.
			if ( empty( $args['code'] ) ) {

				$tries = 10;
				while ( $tries ) {
					$args['code']    = wc_gc_generate_gift_card_code();
					$giftcards_count = (int) $this->query(
						array(
							'code'  => $args['code'],
							'count' => true,
						)
					);
					if ( 0 === $giftcards_count ) {
						break;
					}
					--$tries;
				}

				if ( 0 === $tries ) {
					throw new Exception( __( 'Failed to generate a random code. Maximum number of tries reached.', 'woocommerce-gift-cards' ) );
				}
			}

			// Pre-fill remaining balance with initial balance.
			if ( 'import' !== $context && isset( $args['balance'] ) && empty( $args['remaining'] ) ) {
				$args['remaining'] = $args['balance'];
			}

			if ( ! isset( $args['create_date'] ) || 0 === $args['create_date'] ) {
				$args['create_date'] = time();
			}
		}

		// Always set last modify date to now.
		$args['last_modify_date'] = time();
	}

	/**
	 * Delete a record from the DB.
	 *
	 * @param  mixed $giftcard
	 * @return void
	 */
	public function delete( $giftcard ) {
		$giftcard = self::get( $giftcard );
		if ( $giftcard ) {

			$giftcard->delete();

			if ( 0 !== $giftcard->get_deliver_date() ) {
				WC()->queue()->cancel(
					'woocommerce_gc_schedule_send_gift_card_to_customer',
					array(
						'giftcard' => $giftcard->get_id(),
						'order_id' => $giftcard->get_order_id(),
					),
					'send_giftcards'
				);
			}
		}
	}

	/**
	 * Get distinct dates.
	 *
	 * @return array
	 */
	public function get_distinct_dates() {
		global $wpdb;

		$months = $wpdb->get_results(
			"
			SELECT DISTINCT YEAR( FROM_UNIXTIME( {$wpdb->prefix}woocommerce_gc_cards.`create_date` ) ) AS year, MONTH( FROM_UNIXTIME( {$wpdb->prefix}woocommerce_gc_cards.`create_date` ) ) AS month
			FROM {$wpdb->prefix}woocommerce_gc_cards
			ORDER BY {$wpdb->prefix}woocommerce_gc_cards.`create_date` DESC"
		);

		return $months;
	}

	/**
	 * Get active gift cards for given user.
	 *
	 * @since  1.6.0
	 *
	 * @param  int $user_id
	 * @return array
	 */
	public function get_active_giftcards( $user_id ) {
		global $wpdb;

		$giftcards = array();

		if ( ! $user_id ) {
			return $giftcards;
		}

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"
				SELECT {$wpdb->prefix}woocommerce_gc_cards.id as `id` FROM {$wpdb->prefix}woocommerce_gc_cards
				LEFT JOIN {$wpdb->prefix}woocommerce_gc_cardsmeta ON ( {$wpdb->prefix}woocommerce_gc_cards.id = {$wpdb->prefix}woocommerce_gc_cardsmeta.gc_giftcard_id )
				WHERE {$wpdb->prefix}woocommerce_gc_cards.is_active = 'on'
				AND {$wpdb->prefix}woocommerce_gc_cards.redeemed_by IN ( %d )
				AND (
					{$wpdb->prefix}woocommerce_gc_cards.remaining > 0 OR {$wpdb->prefix}woocommerce_gc_cardsmeta.meta_key LIKE %s )
				AND ( {$wpdb->prefix}woocommerce_gc_cards.expire_date = 0 OR {$wpdb->prefix}woocommerce_gc_cards.expire_date > %d )
				GROUP BY {$wpdb->prefix}woocommerce_gc_cards.id
				ORDER BY {$wpdb->prefix}woocommerce_gc_cards.id ASC",
				absint( $user_id ),
				'balance_%',
				time(),
			),
			ARRAY_A
		);

		foreach ( $results as $result ) {
			$giftcards[] = self::get( $result['id'] );
		}

		return $giftcards;
	}

	/**
	 * Check and locks a giftcard for a short period.
	 *
	 * @since 1.14.0
	 *
	 * @param int $giftcard_id
	 *
	 * @return bool|int|string|null Returns meta key if giftcard was locked, null if returned early.
	 */
	public function check_and_lock_giftcard( $giftcard_id ) {

		if ( ! apply_filters( 'woocommerce_gc_checkout_lock_enabled', true ) ) {
			return null;
		}

		global $wpdb;

		// phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
		$held_time = (int) apply_filters( 'woocommerce_gc_checkout_hold_minutes', MINUTE_IN_SECONDS );

		$query_for_locks = $wpdb->prepare(
			"
			SELECT COUNT(meta_id) FROM `{$wpdb->prefix}woocommerce_gc_cardsmeta`
			WHERE meta_key LIKE %s
			AND meta_key > %s
			AND gc_giftcard_id = %d
			FOR UPDATE
			",
			array(
				'_lock_%%',
				'_lock_' . time(),
				$giftcard_id,
			)
		);

		$lock_key = '_lock_' . ( time() + $held_time ) . '_' . wp_generate_password( 6, false );
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$insert_statement = $wpdb->prepare(
			"
			INSERT INTO `{$wpdb->prefix}woocommerce_gc_cardsmeta` ( gc_giftcard_id, meta_key, meta_value )
			SELECT %d, %s, %s FROM DUAL
			WHERE ( $query_for_locks ) < 1
			",
			$giftcard_id,
			$lock_key,
			''
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		// This query can be deadlocked if a combined index on gc_giftcard_id and meta_key is present and there is high concurrency,
		// in which case DB will abort the query which has done less work to resolve deadlock.
		// We will try up to 3 times before giving up.
		for ( $count = 0; $count < 3; $count++ ) {
			//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $insert_statement is prepared.
			$result = $wpdb->query( $insert_statement );
			if ( false !== $result ) {
				break;
			}
		}

		return $result > 0 ? $lock_key : $result;
	}

	/**
	 * Check balance of a gift card for a given amount.
	 *
	 * @since 1.14.0
	 *
	 * @param int   $giftcard_id
	 * @param float $amount
	 *
	 * @return bool
	 */
	public function debit_giftcard( $giftcard_id, $amount ) {
		global $wpdb;

		$wpdb->query( 'START TRANSACTION' );

		// Deadlock the row and get the current remaining balance.
		$current_balance = (float) $wpdb->get_var(
			$wpdb->prepare(
				"
				SELECT remaining
				FROM `{$wpdb->prefix}woocommerce_gc_cards`
				WHERE id = %d
				LIMIT 1
				FOR UPDATE
				",
				$giftcard_id
			)
		);

		if ( empty( $current_balance ) ) {
			// Release row.
			$wpdb->query( 'ROLLBACK' );
			return false;
		}

		// Test balance.
		$new_balance = (float) round( $current_balance - $amount, wc_get_rounding_precision() );
		if ( $new_balance >= 0 ) {
			$result = $wpdb->query(
				$wpdb->prepare(
					"
					UPDATE `{$wpdb->prefix}woocommerce_gc_cards`
					SET remaining = %f, last_modify_date = %d
					WHERE id = %d",
					$new_balance,
					time(),
					$giftcard_id
				)
			);

			if ( $result > 0 ) {
				// Update and Release row.
				$wpdb->query( 'COMMIT' );
				return true;
			}
		}

		// Release row.
		$wpdb->query( 'ROLLBACK' );
		return false;
	}
}
