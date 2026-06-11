<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get product id
$product_id = tripgo_get_meta_data( 'id', $args, get_the_id() );

// Get product
$product = wc_get_product( $product_id );
if ( !$product || !$product->is_type( 'ovabrw_car_rental' ) ) return;

// Date format
$date_format = function_exists( 'ovabrw_get_date_format' ) ? ovabrw_get_date_format() : 'd-m-Y';

// Get unavailable start date
$start_date = tripgo_get_post_meta( $product_id, 'untime_startdate' );

// Get unavailable end date
$end_date = tripgo_get_post_meta( $product_id, 'untime_enddate' );

if ( tripgo_array_exists( $start_date ) ): ?>
	<div class="ovacrs_single_untime">
		<h3><?php esc_html_e( 'You can\'t book product in this time', 'tripgo' ); ?></h3>
		<ul><?php $flag = 1;
			foreach ( $start_date as $i => $start ):
				// Start
				if ( !strtotime( $start ) ) continue;

				// End
				$end = tripgo_get_meta_data( $i, $end_date );
				if ( !strtotime( $end ) || strtotime( $end ) < current_time( 'timestamp' ) ) continue;

				if ( $start != $end ): ?>
					<li>
						<?php echo sprintf( esc_html__( '%s. %s to %s', 'tripgo' ), $flag, date_i18n( $date_format, strtotime( $start ) ), date_i18n( $date_format, strtotime( $end ) ) ); ?>
					</li>
				<?php else: ?>
					<li>
						<?php echo sprintf( esc_html__( '%s. %s', 'tripgo' ), $flag, date_i18n( $date_format, strtotime( $start ) ) ); ?>
					</li>
				<?php endif;
				$flag++;
			endforeach; ?>
		</ul>
	</div>
<?php endif; ?>