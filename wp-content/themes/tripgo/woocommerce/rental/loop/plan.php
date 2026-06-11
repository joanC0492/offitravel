<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get product id
$product_id = tripgo_get_meta_data( 'id', $args, get_the_id() );

// Get product
$product = wc_get_product( $product_id );
if ( !$product || !$product->is_type( 'ovabrw_car_rental' ) ) return;

// Get tour plans
$tour_plans = tripgo_get_post_meta( $product_id, 'group_tour_plan' );

// Show plan
$show_plan = tripgo_get_meta_data( 'show_tour_plan', $args, 'yes' );

if ( 'yes' === $show_plan && tripgo_array_exists( $tour_plans ) ): ?>
    <div class="content-product-item tour-plan-wrapper" id="tour-plan">
        <h2 class="heading-tour-plan">
            <?php echo esc_html__( 'Plan Turístico', 'tripgo' ); ?>
        </h2>
        <div class="tour-plan-content">
            <?php foreach ( $tour_plans as $k => $plan ):
                // Get plan day
                $plan_day = tripgo_get_meta_data( 'ovabrw_tour_plan_day', $plan );

                // Get plan label
                $plan_label = tripgo_get_meta_data( 'ovabrw_tour_plan_label', $plan );

                // Get plan description
                $plan_desc = tripgo_get_meta_data( 'ovabrw_tour_plan_desc', $plan );
            ?>
                <div class="item-tour-plan <?php if ( 0 == $k ) { echo 'active'; } ?>">
                    <div class="tour-plan-title">
                        <?php if ( $plan_day ): ?>
                            <span class="tour-plan-day">
                                <?php echo esc_html( $plan_day ); ?>
                            </span>
                        <?php endif;

                        // Plan label
                        if ( $plan_label ): ?>
                            <span class="tour-plan-label">
                                <?php echo esc_html( $plan_label ); ?>
                            </span>
                        <?php endif;

                        // Active icon
                        if ( 0 == $k ): ?>
                            <i aria-hidden="true" class="icomoon icomoon-chevron-up"></i>
                        <?php else : ?>
                            <i aria-hidden="true" class="icomoon icomoon-chevron-down"></i>
                        <?php endif; ?>
                    </div>
                    <?php if ( $plan_desc ): ?>
                        <div class="tour-plan-description">
                            <?php echo wpautop( wp_trim_excerpt( $plan_desc ) ); ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>                  
        </div>
    </div>
<?php endif; ?>