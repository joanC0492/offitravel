<?php if (!defined('ABSPATH')) exit();

// Get product id
$product_id = ovabrw_get_meta_data('id', $args, get_the_id());

// Get product
$product = wc_get_product($product_id);

// Get product title
$product_title = get_the_title($product_id);

// Get date format
$date_format = ovabrw_get_date_format();

// link
$link = apply_filters('woocommerce_loop_product_link', get_the_permalink($product_id), $product);

// Show featured
$show_featured = ovabrw_get_meta_data('show_featured', $args_show, 'yes');

// Show wishlist
$show_wishlist = ovabrw_get_meta_data('show_wishlist', $args_show, 'yes');

// Show duration
$show_duration = ovabrw_get_meta_data('show_duration', $args_show, 'yes');

// Show title
$show_title = ovabrw_get_meta_data('show_title', $args_show, 'yes');

// Show location
$show_location = ovabrw_get_meta_data('show_location', $args_show, 'yes');

// Show rating
$show_rating = ovabrw_get_meta_data('show_rating', $args_show, 'yes');

// Show price
$show_price = ovabrw_get_meta_data('show_price', $args_show, 'yes');

// Show button
$show_button = ovabrw_get_meta_data('show_button', $args_show, 'yes');

// Number of days
$tour_days = ovabrw_get_post_meta($product_id, 'number_days');

// Number of hours
$tour_hours = ovabrw_get_post_meta($product_id, 'number_hours');

// Duration
$duration = ovabrw_get_post_meta($product_id, 'duration_checkbox');

// Address
$address = ovabrw_get_post_meta($product_id, 'address');

// Short address
$short_address = ovabrw_get_post_meta($product_id, 'short_address');
if ($short_address) {
	$address = $short_address;
}

// Get review count
$review_count = $product->get_review_count();

// Rating
$rating = $product->get_average_rating();

// Wishlist
$wishlist = '[yith_wcwl_add_to_wishlist product_id=' . $product_id . ']';

// Featured product
$is_featured = $product->is_featured();

// Price
$regular_price = $product->get_regular_price();

// Sale price
$sale_price = '';
if ($product->is_on_sale()) {
	$sale_price = $product->get_sale_price();
}

?>

<div class="ova-product swiper-slide">
	<div class="ova_head_product">
		<?php if ('yes' === $show_featured && $is_featured): ?>
			<div class="ova-is-featured">
				<?php esc_html_e('Featured', 'ova-brw'); ?>
			</div>
		<?php endif;

		// Wishlist
		if ('yes' === $show_wishlist && '[yith_wcwl_add_to_wishlist product_id=' . $product_id . ']' != do_shortcode($wishlist)): ?>
			<div class="ova-product-wishlist">
				<?php echo do_shortcode($wishlist); ?>
			</div>
		<?php endif;

		// Card gallery
		if (apply_filters(OVABRW_PREFIX . 'product_list_card_gallery', false)): ?>
			<div class="ova-card-gallery">
				<?php
				$data_options = apply_filters('ft_wc_card_gallery_slideshow_options', [
					'slidesPerView'         => 3,
					'slidesPerGroup'        => 1,
					'spaceBetween'          => 24,
					'pauseOnMouseEnter'     => true,
					'loop'                  => true,
					'autoplay'              => true,
					'delay'                 => 3000,
					'speed'                 => 500,
					'dots'                  => false,
					'nav'                   => true,
					'breakpoints'           => [
						'0'     => [
							'slidesPerView' => 1
						],
						'768'   => [
							'slidesPerView' => 2
						],
						'1024'  => [
							'slidesPerView' => 3
						]
					],
					'rtl'                   => is_rtl() ? true : false
				]);

				// Get gallery
				wc_get_template('rental/loop/gallery-slideshow.php', [
					'data_options' => $data_options
				]);
				?>
			</div>
		<?php else: ?>
			<a href="<?php echo esc_url($link); ?>" class="ova-product-thumbnail">
				<?php if (has_post_thumbnail($product_id)) {
					$product_img_url = wp_get_attachment_image_src(get_post_thumbnail_id($product_id), 'tripgo_product_slider')[0];
				} else {
					$product_img_url = \Elementor\Utils::get_placeholder_image_src();
				} ?>
				<img src="<?php echo esc_url($product_img_url); ?>" alt="<?php echo esc_attr($product_title); ?>">
			</a>
		<?php endif; ?>
	</div>
	<div class="ova_foot_product">
		<div class="ova-product-day-title-location">
			<?php if (($tour_days || ($duration && $tour_hours)) && $product->is_type(OVABRW_RENTAL) && 'yes' === $show_duration): ?>
				<div class="ova-tour-day">
					<i aria-hidden="true" class="icomoon icomoon-clock"></i>
					<?php if ($duration) {
						// Obtener número de horas
						$hours = ovabrw_convert_number_to_hours($tour_hours);

						// Obtener número de minutos
						$minutes = ovabrw_convert_number_to_minutes($tour_hours);

						if ($hours && $minutes) {
							if ($hours > 1 && $minutes > 1) {
								echo sprintf(esc_html__('%s horas %s minutos', 'ova-brw'), $hours, $minutes);
							} elseif ($hours == 1 && $minutes > 1) {
								echo sprintf(esc_html__('%s hora %s minutos', 'ova-brw'), $hours, $minutes);
							} elseif ($hours > 1 && $minutes == 1) {
								echo sprintf(esc_html__('%s horas %s minuto', 'ova-brw'), $hours, $minutes);
							} else {
								echo sprintf(esc_html__('%s hora %s minuto', 'ova-brw'), $hours, $minutes);
							}
						} elseif (! $hours && $minutes) {
							if ($minutes == 1) {
								echo sprintf(esc_html__('%s minuto', 'ova-brw'), $minutes);
							} else {
								echo sprintf(esc_html__('%s minutos', 'ova-brw'), $minutes);
							}
						} else {
							if ($hours == 1) {
								echo sprintf(esc_html__('%s hora', 'ova-brw'), $hours);
							} else {
								echo sprintf(esc_html__('%s horas', 'ova-brw'), $hours);
							}
						}
					} else {
						if (absint($tour_days) == 1) {
							echo sprintf(esc_html__('%s día', 'ova-brw'), $tour_days);
						} else {
							echo sprintf(esc_html__('%s días', 'ova-brw'), $tour_days);
						}
					} ?>
				</div>
			<?php endif;

			// Product title
			if ('yes' === $show_title): ?>
				<h2 class="ova-product-title">
					<a href="<?php echo esc_url($link); ?>">
						<?php echo get_the_title($product_id); ?>
					</a>
				</h2>
			<?php endif;

			// Address
			if ('yes' === $show_location && $address): ?>
				<div class="ova-product-location">
					<i aria-hidden="true" class="icomoon icomoon-location"></i>
					<span class="location">
						<?php echo esc_html($address); ?>
					</span>
				</div>
			<?php endif; ?>
		</div>
		<div class="ova-product-review-and-price">
			<?php if (wc_review_ratings_enabled() && $rating > 0 && 'yes' === $show_rating): ?>
				<div class="ova-product-review">
					<div class="star-rating" role="img" aria-label="<?php echo sprintf(esc_html__('Rated %s out of 5', 'ova-brw'), $rating); ?>">
						<span class="rating-percent" style="width: <?php echo esc_attr(($rating / 5) * 100) . '%'; ?>;"></span>
						<?php if ($review_count > 0): ?>
							<span class="rating"><?php echo esc_html($review_count); ?></span>'
						<?php else: ?>
							<strong class="rating"><?php echo esc_html($rating); ?></strong>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>

			<div class="ova-product-wrapper-price">
				<?php if ('yes' === $show_price): ?>
					<div class="ova-product-price">
						<?php if ($sale_price && $regular_price): ?>
							<span class="new-product-price">
								<?php echo wc_price($sale_price); ?>
							</span>
							<span class="old-product-price">
								<?php echo wc_price($regular_price); ?>
							</span>
						<?php elseif (!$sale_price && $regular_price): ?>
							<span class="new-product-price">
								<?php echo wc_price($regular_price); ?>
							</span>
						<?php else: ?>
							<?php if ($product && !$product->is_type(OVABRW_RENTAL)): ?>
								<span class="new-product-price">
									<?php echo wp_kses_post($product->get_price_html()); ?>
								</span>
							<?php else: ?>
								<span class="no-product-price">
									<?php esc_html_e('Option Price', 'ova-brw'); ?>
								</span>
						<?php endif;
						endif; ?>
					</div>
				<?php endif;

				// Show button
				if ('yes' === $show_button): ?>
					<a href="<?php echo esc_url($link); ?>" class="btn product-btn-book-now">
						<span><?php esc_html_e('Explore', 'ova-brw'); ?></span>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>