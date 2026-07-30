<?php
/**
 * WC_GC_Activity_List_Table class
 *
 * @package  WooCommerce Gift Cards
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Adds a custom deployments list table.
 *
 * @class    WC_GC_Activity_List_Table
 * @version  2.0.0
 */
class WC_GC_Activity_List_Table extends WP_List_Table {

	/**
	 * Page home URL.
	 *
	 * @const PAGE_URL
	 */
	const PAGE_URL = 'admin.php?page=gc_activity';

	/**
	 * Total view records.
	 *
	 * @var int
	 */
	private $total_items = 0;

	/**
	 * Specify Gift Card to show activity (Defaults to all).
	 *
	 * @var int
	 */
	private $giftcard;

	/**
	 * Whether or not to mask the gift card codes.
	 *
	 * @var bool
	 */
	private $mask_codes;

	/**
	 * Constructor
	 */
	public function __construct( $giftcard = 0 ) {
		global $status, $page;

		$this->total_items = WC_GC()->db->activity->query( array( 'count' => true ) );
		$this->giftcard    = absint( $giftcard );
		$this->mask_codes  = wc_gc_mask_codes( 'admin' );

		parent::__construct(
			array(
				'singular' => 'activity',
				'plural'   => 'activities',
			)
		);
	}

	/**
	 * This is a default column renderer
	 *
	 * @param $item - row (key, value array)
	 * @param $column_name - string (key)
	 * @return string
	 */
	public function column_default( $item, $column_name ) {

		if ( isset( $item[ $column_name ] ) ) {

			echo wp_kses_post( $item[ $column_name ] );

		} else {

			/**
			 * Fires in each custom column in the Gift Cards Activity list table.
			 *
			 * This hook only fires if the current column_name is not set inside the $item's keys.
			 *
			 * @since 1.3.6
			 *
			 * @param string $column_name The name of the column to display.
			 * @param array  $item
			 */
			do_action( 'manage_gc_giftcards_activity_custom_column', $column_name, $item );
		}
	}

	/**
	 * Handles the title column output.
	 *
	 * @param array $item
	 */
	public function column_gc_code( $item ) {

		global $current_screen;

		// Init actions.
		$actions = array();

		if ( $current_screen && wc_gc_get_formatted_screen_id( 'woocommerce_page_gc_activity' ) === $current_screen->id ) {
			$actions = array(
				'edit' => sprintf( '<a href="' . admin_url( 'admin.php?page=gc_giftcards&section=edit&giftcard=%d' ) . '">%s</a>', $item['gc_id'], __( 'Edit', 'woocommerce-gift-cards' ) ),
			);
		}

		if ( in_array( $item['type'], array( 'issued', 'used', 'refunded' ) ) ) {
			$actions['order'] = sprintf( '<a id="%d" href="' . admin_url( 'post.php?post=%d&action=edit' ) . '">%s</a>', $item['id'], $item['object_id'], __( 'View Order', 'woocommerce-gift-cards' ) );
		}

		$title = $this->mask_codes ? wc_gc_mask_code( $item['gc_code'] ) : $item['gc_code'];

		printf(
			'%s%s',
			esc_html( $title ),
			wp_kses_post( $this->row_actions( $actions ) )
		);
	}

	/**
	 * Handles the customer column output.
	 *
	 * @param array $item
	 */
	public function column_customer( $item ) {

		if ( $item['user_id'] ) {
			$user = get_user_by( 'id', $item['user_id'] );
		}

		if ( isset( $user ) && $user ) {
			printf( '<a href="%s" target="_blank">%s</a>&nbsp;(%s)', esc_url( get_edit_user_link( $user->ID ) ), esc_html( $user->display_name ), esc_html( $user->user_email ) );
		} elseif ( $item['user_email'] ) {
			echo esc_html( $item['user_email'] );
		} else {
			?>
			<mark>
				<?php
				echo esc_html__( 'SYSTEM', 'woocommerce-gift-cards' );
				?>
			</mark>
			<?php
		}
	}

	/**
	 * Handles the balance column output.
	 *
	 * @param array $item
	 */
	public function column_amount( $item ) {
		echo wp_kses_post( wc_price( (float) $item['amount'] ) );
	}

	/**
	 * Handles the type column output.
	 *
	 * @param array $item
	 */
	public function column_type( $item ) {
		echo esc_html( wc_gc_get_activity_type_label( $item['type'] ) );
	}

	/**
	 * Handles the date column output.
	 *
	 * @param array $item
	 */
	public function column_date( $item ) {

		$t_time    = wp_date( _x( 'Y/m/d g:i:s a', 'list table date hover format', 'woocommerce-gift-cards' ), $item['date'] );
		$time_diff = time() - $item['date'];

		if ( $time_diff > 0 && $time_diff < DAY_IN_SECONDS ) {
			/* translators: %s: human time diff */
			$h_time = sprintf( esc_html__( '%s ago', 'woocommerce-gift-cards' ), human_time_diff( $item['date'] ) );
		} else {
			$h_time = wp_date( get_option( 'date_format', 'Y/m/d' ), $item['date'] );
		}

		echo '<span title="' . esc_attr( $t_time ) . '">' . esc_html( $h_time ) . '</span>';
	}

	/**
	 * Get a list of columns. The format is:
	 * 'internal-name' => 'Title'
	 */
	public function get_columns() {

		$columns             = array();
		$columns['gc_code']  = _x( 'Gift Card', 'column_name', 'woocommerce-gift-cards' );
		$columns['customer'] = _x( 'Customer', 'column_name', 'woocommerce-gift-cards' );
		$columns['type']     = _x( 'Event', 'column_name', 'woocommerce-gift-cards' );
		$columns['amount']   = _x( 'Amount', 'column_name', 'woocommerce-gift-cards' );
		$columns['date']     = _x( 'Date', 'column_name', 'woocommerce-gift-cards' );

		/**
		 * Filters the columns displayed in the Gift Cards list table.
		 *
		 * @since 1.3.6
		 *
		 * @param array $columns An associative array of column headings.
		 */
		return apply_filters( 'manage_gc_giftcards_activity_columns', $columns );
	}

	/**
	 * Return sortable columns.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'amount' => array( 'amount', true ),
			'date'   => array( 'date', true ),
		);

		return $sortable_columns;
	}

	/**
	 * Query the DB and attach items.
	 *
	 * @return void
	 */
	public function prepare_items() {

		$per_page = $this->giftcard ? 10 : 20;

		// Table columns.
		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		// Setup params.
		$paged   = isset( $_REQUEST['paged'] ) ? max( 0, intval( $_REQUEST['paged'] ) - 1 ) : 0;
		$orderby = ( isset( $_REQUEST['orderby'] ) && in_array( $_REQUEST['orderby'], array_keys( $this->get_sortable_columns() ) ) ) ? wc_clean( $_REQUEST['orderby'] ) : 'id';
		$order   = ( isset( $_REQUEST['order'] ) && in_array( $_REQUEST['order'], array( 'asc', 'desc' ) ) ) ? wc_clean( $_REQUEST['order'] ) : 'desc';

		// Query args.
		$query_args = array(
			'order_by' => array( $orderby => $order ),
			'limit'    => $per_page,
			'offset'   => $paged * $per_page,
		);

		if ( $this->giftcard > 0 ) {
			$query_args['gc_id'] = $this->giftcard;
		}

		// Search.
		if ( isset( $_REQUEST['s'] ) && ! empty( $_REQUEST['s'] ) ) {
			$query_args['search'] = wc_clean( $_REQUEST['s'] );
		}

		// Filters.
		if ( ! empty( $_GET['_customer_filter'] ) ) {
			$filter                = absint( $_GET['_customer_filter'] );
			$query_args['user_id'] = array( $filter );
		}
		if ( ! empty( $_GET['_type_filter'] ) ) {
			// Sanity check.
			$filter = in_array( $_GET['_type_filter'], array_keys( wc_gc_get_activity_types() ) ) ? wc_clean( $_GET['_type_filter'] ) : '';
			if ( $filter ) {
				$query_args['type'] = array( $filter );
			}
		}
		if ( ! empty( $_GET['m'] ) ) {

			$filter = absint( $_GET['m'] );
			$month  = substr( $filter, 4, 6 );
			$year   = substr( $filter, 0, 4 );
			if ( $filter ) {
				$start_date               = strtotime( "{$year}-{$month}-01" );
				$query_args['start_date'] = $start_date;
				$end_date                 = strtotime( '+1 month', $start_date );
				$query_args['end_date']   = $end_date;
			}
		}

		// Fetch the items.
		// It's safe to ignore semgrep warning, as everything is properly escaped.
		$this->items = WC_GC()->db->activity->query( $query_args ); // nosemgrep: audit.php.wp.security.sqli.input-in-sinks

		// Count total items.
		$query_args['count'] = true;
		unset( $query_args['limit'] );
		unset( $query_args['offset'] );
		$total_items = WC_GC()->db->activity->query( $query_args );

		// Configure pagination.
		$this->set_pagination_args(
			array(
				'total_items' => $total_items, // total items defined above
				'per_page'    => $per_page, // per page constant defined at top of method
				'total_pages' => ceil( $total_items / $per_page ), // calculate pages count
			)
		);
	}

	/**
	 * Message to be displayed when there are no items.
	 *
	 * @return void
	 */
	public function no_items() {

		if ( 0 === $this->total_items ) {
			?>
			<div class="gc-giftcards__empty-state">
				<i class="dashicons dashicons-backup"></i>
				<p class="main">
					<?php esc_html_e( 'Gift Cards activity', 'woocommerce-gift-cards' ); ?>
				</p>
				<p>
					<?php esc_html_e( 'No activity recorded just yet.', 'woocommerce-gift-cards' ); ?>
				</p>
			</div>
			<?php

		} else {
			?>
			<p class="main">
				<?php esc_html_e( 'No activity recorded', 'woocommerce-gift-cards' ); ?>
			</p>
			<?php
		}
	}

	/**
	 * Construct a link string from args.
	 *
	 * @param  array  $args
	 * @param  string $label
	 * @param  string $class
	 * @return string
	 */
	protected function get_link( $args, $label, $class = '' ) {

		$base_url = admin_url( 'admin.php?page=gc_giftcards' );
		$url      = add_query_arg( $args, $base_url );

		$class_html   = '';
		$aria_current = '';
		if ( ! empty( $class ) ) {
			$class_html = sprintf(
				' class="%s"',
				esc_attr( $class )
			);

			if ( 'current' === $class ) {
				$aria_current = ' aria-current="page"';
			}
		}

		return sprintf(
			'<a href="%s"%s%s>%s</a>',
			esc_url( $url ),
			$class_html,
			$aria_current,
			$label
		);
	}

	/**
	 * Display table extra nav.
	 *
	 * @param  string $which top|bottom
	 * @return void
	 */
	public function extra_tablenav( $which ) {
		if ( 'top' === $which && ! is_singular() && ! $this->giftcard ) {
			?>
			<div class="alignleft actions sw-select2-autoinit">
			<?php
				$this->render_filters();
				submit_button( __( 'Filter', 'woocommerce-gift-cards' ), '', 'filter_action', false, array( 'id' => 'post-query-submit' ) );
			?>
			</div>
			<?php
		}
	}

	/**
	 * Display table filters.
	 *
	 * @return void
	 */
	protected function render_filters() {

		$this->display_months_dropdown();
		$this->display_types_dropdown();

		$user_string = '';
		$user_id     = '';

		if ( ! empty( $_GET['_customer_filter'] ) ) {

			$user_id = wc_clean( $_GET['_customer_filter'] );
			$user    = get_user_by( 'id', absint( $user_id ) );

			if ( $user ) {
				$user_string = sprintf(
					/* translators: 1: user display name 2: user ID 3: user email */
					esc_html__( '%1$s (#%2$s &ndash; %3$s)', 'woocommerce' ),
					$user->display_name,
					absint( $user->ID ),
					$user->user_email
				);
			}
		}
		?>
		<select class="sw-select2-search--customers" name="_customer_filter" data-placeholder="<?php esc_attr_e( 'All customers', 'woocommerce-gift-cards' ); ?>" data-allow_clear="true">
			<?php if ( $user_string && $user_id ) { ?>
				<option value="<?php echo esc_attr( $user_id ); ?>" selected="selected"><?php echo wp_kses_post( htmlspecialchars( $user_string, ENT_COMPAT ) ); ?><option>
			<?php } ?>
		</select>
		<?php
	}

	/**
	 * Display types dropdown filter.
	 *
	 * @return void
	 */
	protected function display_types_dropdown() {
		$type_filter = ! empty( $_GET['_type_filter'] ) ? wc_clean( $_GET['_type_filter'] ) : 0;
		?>
		<label for="filter-by-type" class="screen-reader-text"><?php esc_html_e( 'Filter by type', 'woocommerce-gift-cards' ); ?></label>
		<select name="_type_filter" id="filter-by-type">
			<option<?php selected( $type_filter, 0 ); ?> value="0"><?php esc_html_e( 'All types', 'woocommerce-gift-cards' ); ?></option>
			<?php
			foreach ( wc_gc_get_activity_types() as $type => $label ) {
				printf(
					"<option %s value='%s'>%s</option>\n",
					selected( $type_filter, $type, false ),
					esc_attr( $type ),
					esc_html( $label )
				);
			}
			?>
		</select>
		<?php
	}

	/**
	 * Display dates dropdown filter.
	 *
	 * @return void
	 */
	protected function display_months_dropdown() {
		global $wp_locale;

		$months      = WC_GC()->db->activity->get_distinct_dates();
		$month_count = count( $months );

		if ( ! $month_count || ( 1 == $month_count && 0 == $months[0]->month ) ) {
			return;
		}

		$m = isset( $_GET['m'] ) ? (int) $_GET['m'] : 0;
		?>
		<label for="filter-by-date" class="screen-reader-text"><?php esc_html_e( 'Filter by date', 'woocommerce-gift-cards' ); ?></label>
		<select name="m" id="filter-by-date">
			<option<?php selected( $m, 0 ); ?> value="0"><?php esc_html_e( 'All dates', 'woocommerce-gift-cards' ); ?></option>
			<?php
			foreach ( $months as $arc_row ) {
				if ( 0 == $arc_row->year ) {
					continue;
				}

				$month           = zeroise( $arc_row->month, 2 );
				$year            = $arc_row->year;
				$formatted_month = esc_html( $wp_locale->get_month( $month ) );
				$formatted_year  = esc_html( $year );

				/* translators: %1$s: month %2$s: year */
				$label = sprintf( esc_html__( '%1$s %2$d', 'woocommerce-gift-cards' ), $formatted_month, $formatted_year );

				/* translators: 1: selected attrbute 2: value */
				$html = "<option %s value='%s'>%s</option>\n";

				printf(
					$html, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					selected( $m, $year . $month, false ),
					esc_attr( $arc_row->year . $month ),
					esc_html( $label )
				);
			}
			?>
		</select>
		<?php
	}
}
