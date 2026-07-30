<?php
/**
 * WC_GC_Admin_Report class
 *
 * @package  WooCommerce Gift Cards
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Base Report Class for GCs.
 *
 * @class    WC_GC_Admin_Report
 * @version  2.7.3
 */
class WC_GC_Report_Gift_Cards extends WC_Admin_Report {

	/**
	 * Chart colors.
	 *
	 * @var array
	 */
	public $chart_colours = array();

	/**
	 * The report data.
	 *
	 * @var stdClass
	 */
	private $report_data;

	/**
	 * Get report data.
	 *
	 * @return stdClass
	 */
	public function get_report_data() {
		if ( empty( $this->report_data ) ) {
			$this->query_report_data();
		}
		return $this->report_data;
	}

	/**
	 * Get all data needed for this report and store in the class.
	 */
	private function query_report_data() {

		// Init container.
		$this->report_data = new stdClass();

		// Query.
		$args = array(
			'start_date' => $this->start_date,
			'end_date'   => strtotime( '+1 day', $this->end_date ),
		);

		$query_hash     = md5( json_encode( $args ) );
		$cached_results = false;
		// $cached_results = get_transient( strtolower( get_class( $this ) ) );

		if ( false === $cached_results || ! isset( $cached_results[ $query_hash ] ) ) {

			$this->report_data->issued  = WC_GC()->db->activity->query( array_merge( $args, array( 'type' => 'issued' ) ) );
			$this->report_data->used    = WC_GC()->db->activity->query( array_merge( $args, array( 'type' => 'used' ) ) );
			$this->report_data->expired = WC_GC()->db->giftcards->query(
				array(
					'expired_start' => $this->start_date,
					'expired_end'   => strtotime( '+1 day', $this->end_date ),
				)
			);

			// Calculate.
			$this->report_data->total_issued  = wc_format_decimal( array_sum( wp_list_pluck( $this->report_data->issued, 'amount' ) ), 2 );
			$this->report_data->total_used    = wc_format_decimal( array_sum( wp_list_pluck( $this->report_data->used, 'amount' ) ), 2 );
			$this->report_data->total_expired = wc_format_decimal( array_sum( wp_list_pluck( $this->report_data->expired, 'remaining' ) ), 2 );

			if ( ! is_array( $cached_results ) ) {
				$cached_results = array();
			}

			$cached_results[ $query_hash ] = $this->report_data;
			set_transient( strtolower( get_class( $this ) ), $cached_results, strtotime( 'tomorrow' ) - time() );

		} else {
			$this->report_data = $cached_results[ $query_hash ];
		}
	}

	/**
	 * Output an export link.
	 */
	public function get_export_button() {
		$current_range = ! empty( $_GET['range'] ) ? sanitize_text_field( $_GET['range'] ) : '7day';
		?>
		<a
			href="#"
			download="report-prl-<?php echo esc_attr( $current_range ); ?>-<?php echo esc_attr( date_i18n( 'Y-m-d', current_time( 'timestamp' ) ) ); ?>.csv"
			class="export_csv"
			data-export="chart"
			data-xaxes="<?php esc_attr_e( 'Date', 'woocommerce' ); ?>"
			data-exclude_series="2"
			data-groupby="<?php echo esc_attr( $this->chart_groupby ); ?>"
		>
			<?php esc_html_e( 'Export CSV', 'woocommerce' ); ?>
		</a>
		<?php
	}

	/**
	 * Get the legend for the main chart sidebar.
	 *
	 * @return array
	 */
	public function get_chart_legend() {

		$legend   = array();
		$data     = $this->get_report_data();
		$legend[] = array(
			'title'            => sprintf(
				/* translators: %s: total issued amount */
				__( '%s issued balance', 'woocommerce-gift-cards' ),
				'<strong>' . wc_price( $data->total_issued ) . '</strong>'
			),
			'placeholder'      => __( 'Total value of gift cards issued in this period.', 'woocommerce-gift-cards' ),
			'color'            => $this->chart_colours['issued'],
			'highlight_series' => 0,
		);

		$legend[] = array(
			'title'            => sprintf(
				/* translators: %s: total used amount */
				__( '%s used balance', 'woocommerce-gift-cards' ),
				'<strong>' . wc_price( $data->total_used ) . '</strong>'
			),
			'placeholder'      => __( 'Total gift cards balance used to pay for orders in this period.', 'woocommerce-gift-cards' ),
			'color'            => $this->chart_colours['used'],
			'highlight_series' => 1,
		);

		$legend[] = array(
			'title'            => sprintf(
				/* translators: %s: total expired amount */
				__( '%s expired balance', 'woocommerce-gift-cards' ),
				'<strong>' . wc_price( $data->total_expired ) . '</strong>'
			),
			'placeholder'      => __( 'Total value of gift cards expired in this period.', 'woocommerce-gift-cards' ),
			'color'            => $this->chart_colours['expired'],
			'highlight_series' => 2,
		);

		return $legend;
	}

	/**
	 * Output the report.
	 */
	public function output_report() {

		$ranges = array(
			'year'       => __( 'Year', 'woocommerce' ),
			'last_month' => __( 'Last month', 'woocommerce' ),
			'month'      => __( 'This month', 'woocommerce' ),
			'7day'       => __( 'Last 7 days', 'woocommerce' ),
		);

		$this->chart_colours = array(
			'issued'  => '#3498db',
			'used'    => '#f1c40f',
			'expired' => '#dbe1e3',
		);

		$current_range = ! empty( $_GET['range'] ) ? sanitize_text_field( $_GET['range'] ) : '7day';

		if ( ! in_array( $current_range, array( 'custom', 'year', 'last_month', 'month', '7day' ) ) ) {
			$current_range = '7day';
		}

		$this->check_current_range_nonce( $current_range );
		$this->calculate_current_range( $current_range );

		include WC()->plugin_path() . '/includes/admin/views/html-report-by-date.php';
	}

	/**
	 * Round our totals correctly.
	 *
	 * @param array|string $amount
	 *
	 * @return array|string
	 */
	private function round_chart_totals( $amount ) {
		if ( is_array( $amount ) ) {
			return array( $amount[0], wc_format_decimal( (float) $amount[1], wc_get_price_decimals() ) );
		} else {
			return wc_format_decimal( (float) $amount, wc_get_price_decimals() );
		}
	}

	/**
	 * Put data with post_date's into an array of times.
	 *
	 * @param  array  $data array of your data
	 * @param  string $date_key key for the 'date' field. e.g. 'post_date'
	 * @param  string $data_key key for the data you are charting
	 * @param  int    $interval
	 * @param  string $start_date
	 * @param  string $group_by
	 * @return array
	 */
	public function prepare( $data, $date_key, $data_key, $interval, $start_date, $group_by ) {
		$prepared_data = array();

		// Ensure all days (or months) have values in this range.
		if ( 'day' === $group_by ) {
			for ( $i = 0; $i <= $interval; $i++ ) {
				$time = strtotime( date_i18n( 'Ymd', strtotime( "+{$i} DAY", $start_date ) ) ) . '000';

				if ( ! isset( $prepared_data[ $time ] ) ) {
					$prepared_data[ $time ] = array( esc_js( $time ), 0 );
				}
			}
		} else {
			$current_yearnum  = date_i18n( 'Y', $start_date );
			$current_monthnum = date_i18n( 'm', $start_date );

			for ( $i = 0; $i <= $interval; $i++ ) {
				$time = strtotime( $current_yearnum . str_pad( $current_monthnum, 2, '0', STR_PAD_LEFT ) . '01' ) . '000';

				if ( ! isset( $prepared_data[ $time ] ) ) {
					$prepared_data[ $time ] = array( esc_js( $time ), 0 );
				}

				++$current_monthnum;

				if ( $current_monthnum > 12 ) {
					$current_monthnum = 1;
					++$current_yearnum;
				}
			}
		}

		foreach ( $data as $d ) {
			switch ( $group_by ) {
				case 'day':
					$time = strtotime( date_i18n( 'Ymd', $d[ $date_key ] ) ) . '000';
					break;
				case 'month':
				default:
					$time = strtotime( date_i18n( 'Ym', $d[ $date_key ] ) . '01' ) . '000';
					break;
			}

			if ( ! isset( $prepared_data[ $time ] ) ) {
				continue;
			}

			if ( $data_key ) {
				$prepared_data[ $time ][1] += $d[ $data_key ];
			} else {
				++$prepared_data[ $time ][1];
			}
		}

		return $prepared_data;
	}

	/**
	 * Get the main chart.
	 */
	public function get_main_chart() {
		global $wp_locale;

		// Prepare data for report
		$data = array(
			'total_issued'  => $this->prepare( $this->report_data->issued, 'date', 'amount', $this->chart_interval, $this->start_date, $this->chart_groupby ),
			'total_used'    => $this->prepare( $this->report_data->used, 'date', 'amount', $this->chart_interval, $this->start_date, $this->chart_groupby ),
			'total_expired' => $this->prepare( $this->report_data->expired, 'expire_date', 'remaining', $this->chart_interval, $this->start_date, $this->chart_groupby ),
		);

		$chart_data = wp_json_encode(
			array(
				'total_issued'  => array_map( array( $this, 'round_chart_totals' ), array_values( $data['total_issued'] ) ),
				'total_used'    => array_map( array( $this, 'round_chart_totals' ), array_values( $data['total_used'] ) ),
				'total_expired' => array_map( array( $this, 'round_chart_totals' ), array_values( $data['total_expired'] ) ),
			)
		);
		?>
		<div class="chart-container">
			<div class="chart-placeholder main"></div>
		</div>
		<script type="text/javascript">

			var main_chart;

			jQuery(function(){
				var chart_data = JSON.parse( decodeURIComponent( '<?php echo rawurlencode( $chart_data ); ?>' ) );
				var drawGraph = function( highlight ) {
					var series = [
						{
							label: "<?php echo esc_js( __( 'Issued balance', 'woocommerce-gift-cards' ) ); ?>",
							data: chart_data.total_issued,
							yaxis: 1,
							color: '<?php echo esc_attr( $this->chart_colours['issued'] ); ?>',
							points: { show: true, radius: 5, lineWidth: 2, fillColor: '#fff', fill: true },
							lines: { show: true, lineWidth: 5, fill: false },
							shadowSize: 0,
							<?php echo $this->get_currency_tooltip(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						},
						{
							label: "<?php echo esc_js( __( 'Used balance', 'woocommerce-gift-cards' ) ); ?>",
							data: chart_data.total_used,
							yaxis: 1,
							color: '<?php echo esc_attr( $this->chart_colours['used'] ); ?>',
							points: { show: true, radius: 5, lineWidth: 2, fillColor: '#fff', fill: true },
							lines: { show: true, lineWidth: 2, fill: false },
							shadowSize: 0,
							<?php echo $this->get_currency_tooltip(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						},
						{
							label: "<?php echo esc_js( __( 'Expired balance', 'woocommerce-gift-cards' ) ); ?>",
							data: chart_data.total_expired,
							yaxis: 1,
							color: '<?php echo esc_attr( $this->chart_colours['expired'] ); ?>',
							points: { show: true, radius: 5, lineWidth: 2, fillColor: '#fff', fill: true },
							lines: { show: true, lineWidth: 2, fill: false },
							shadowSize: 0,
							<?php echo $this->get_currency_tooltip(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						}
					];

					if ( highlight !== 'undefined' && series[ highlight ] ) {
						highlight_series = series[ highlight ];

						highlight_series.color = '#9c5d90';

						if ( highlight_series.bars ) {
							highlight_series.bars.fillColor = '#9c5d90';
						}

						if ( highlight_series.lines ) {
							highlight_series.lines.lineWidth = 5;
						}
					}

					main_chart = jQuery.plot(
						jQuery('.chart-placeholder.main'),
						series,
						{
							legend: {
								show: false
							},
							grid: {
								color: '#aaa',
								borderColor: 'transparent',
								borderWidth: 0,
								hoverable: true
							},
							xaxes: [ {
								color: '#aaa',
								position: "bottom",
								tickColor: 'transparent',
								mode: "time",
								timeformat: "<?php echo ( 'day' === $this->chart_groupby ) ? '%d %b' : '%b'; ?>",
								monthNames: JSON.parse( decodeURIComponent( '<?php echo rawurlencode( wp_json_encode( array_values( $wp_locale->month_abbrev ) ) ); ?>' ) ),
								tickLength: 1,
								minTickSize: [1, "<?php echo esc_attr( $this->chart_groupby ); ?>"],
								font: {
									color: "#aaa"
								}
							} ],
							yaxes: [
								{
									min: 0,
									alignTicksWithAxis: 1,
									tickDecimals: 2,
									color: '#d4d9dc',
									font: { color: "#aaa" }
								}
							],
						}
					);

					jQuery( '.chart-placeholder' ).resize();
				}

				drawGraph();

				jQuery( '.highlight_series' ).hover(
					function() {
						drawGraph( jQuery(this).data( 'series' ) );
					},
					function() {
						drawGraph();
					}
				);
			});
		</script>
		<?php
	}
}
