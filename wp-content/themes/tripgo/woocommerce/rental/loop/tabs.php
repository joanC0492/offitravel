<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get product id
$product_id = tripgo_get_meta_data( 'id', $args, get_the_id() );

// Get product
$product = wc_get_product( $product_id );
if ( !$product || !$product->is_type( 'ovabrw_car_rental' ) ) return;

// Tour description
$tour_description   = wpautop( get_post( $product_id )->post_content );
$label_description  = tripgo_get_meta_data( 'description_label', $args, esc_html__( 'Description', 'tripgo' ) );
$show_description   = tripgo_get_meta_data( 'show_description', $args, 'yes' );

// Tour included
$tour_included = tripgo_get_post_meta( $product_id, 'group_tour_included' );

// Tour excluded
$tour_excluded = tripgo_get_post_meta( $product_id, 'group_tour_excluded' );

// Show included & excluded
$label_incl_excl    = tripgo_get_meta_data( 'incl_excl_label', $args, esc_html__( 'Included/Excluded', 'tripgo' ) );
$show_incl_excl     = tripgo_get_meta_data( 'show_incl_excl', $args, 'yes' );

// Tour plan
$tour_plan          = tripgo_get_post_meta( $product_id, 'group_tour_plan' );
$label_tour_plan    = tripgo_get_meta_data( 'tour_plan_label', $args, esc_html__( 'Tour Plan', 'tripgo' ) );
$show_tour_plan     = tripgo_get_meta_data( 'show_tour_plan', $args, 'yes' );

// Get address
$address        = tripgo_get_post_meta( $product_id, 'address' );
$label_tour_map = tripgo_get_meta_data( 'tour_map_label', $args, esc_html__( 'Tour Map', 'tripgo' ) );
$show_tour_map  = tripgo_get_meta_data( 'show_tour_map', $args, 'no' );

// Reviews
$label_reviews  = tripgo_get_meta_data( 'reviews_label', $args, esc_html__( 'Reviews', 'tripgo' ) );
$show_reviews   = tripgo_get_meta_data( 'show_reviews', $args, 'yes' );

?>

<div class="ova-tabs-product">
    <div class="tabs">
        <?php if ( 'yes' === $show_description && $tour_description ): ?>
            <div class="item" data-id="#tour-description">
                <?php echo esc_html( $label_description ); ?>
            </div>
        <?php endif;

        // Show included & excluded
        if ( 'yes' === $show_incl_excl && ( tripgo_array_exists( $tour_included ) || tripgo_array_exists( $tour_excluded ) ) ):  ?>
            <div class="item" data-id="#tour-included-excluded">
                 <?php echo esc_html( $label_incl_excl ); ?>
            </div>
        <?php endif;

        // Show tour plan
        if ( 'yes' === $show_tour_plan && tripgo_array_exists( $tour_plan ) ): ?>
            <div class="item" data-id="#tour-plan">
                <?php echo esc_html( $label_tour_plan ); ?>
            </div>
        <?php endif;

        // Tour map
        if ( 'yes' === $show_tour_map && $address ): ?>
            <div class="item" data-id="#ova-tour-map">
                <?php echo esc_html( $label_tour_map ); ?>
            </div>
        <?php endif;

        // Review
        if ( 'yes' === $show_reviews ): ?>
            <div class="item" data-id="#ova-tour-review">
                <?php echo esc_html( $label_reviews ); ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
        wc_get_template( 'rental/loop/description.php', [ 'id' => $product_id ] );
        wc_get_template( 'rental/loop/included-excluded.php', [ 'id' => $product_id ] );
        wc_get_template( 'rental/loop/plan.php', [ 'id' => $product_id ] );
        wc_get_template( 'rental/loop/map.php', [ 'id' => $product_id, 'show_map' => $show_tour_map ] );
        wc_get_template( 'rental/loop/review.php', [ 'id' => $product_id ] );
    ?>
</div>