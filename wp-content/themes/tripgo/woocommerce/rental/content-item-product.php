<?php if (!defined('ABSPATH')) exit();

// Get product id
$product_id = tripgo_get_meta_data('id', $args, get_the_id());

// Get product
$product = wc_get_product($product_id);

// Get permalink
$link = apply_filters('woocommerce_loop_product_link', get_the_permalink($product_id), $product);

// Parameters
$param = [];

// Start date
$start_date = tripgo_get_meta_data('start_date', $_POST);
if ($start_date) $param['pickup_date'] = $start_date;

// Number of adults
$numberof_adults = (int)tripgo_get_meta_data('adults', $_POST);
if ($numberof_adults) $param['ovabrw_adults'] = $numberof_adults;

// Number of children
$children = (int)tripgo_get_meta_data('childrens', $_POST);
if ($children) $param['ovabrw_childrens'] = $children;

// Add query args
if ($param) {
	$link = add_query_arg($param, $link);
}

// Get number of days
$numberof_days = (int)tripgo_get_post_meta($product_id, 'number_days');

// Get number of hours
$numberof_hours = (int)tripgo_get_post_meta($product_id, 'number_hours');

// Duration
$duration = tripgo_get_post_meta($product_id, 'duration_checkbox');

// Get address
$address = tripgo_get_post_meta($product_id, 'address');

// Get short address
$short_address = tripgo_get_post_meta($product_id, 'short_address');
if ($short_address) $address = $short_address;

// Get review count
$review_count = $product->get_review_count();

// Get rating
$rating = $product->get_average_rating();

// Wishlist
$wishlist = do_shortcode('[yith_wcwl_add_to_wishlist]');

// Featured product
$is_featured = $product->is_featured();

// Price
$regular_price = $product->get_regular_price();

// Sale price
$sale_price = '';
if ($product->is_on_sale()) {
	$sale_price = $product->get_sale_price();
}

// Show featured
$show_featured = get_theme_mod('tour_archive_show_featured', 'yes');

// Show wishlist
$show_wishlist = get_theme_mod('tour_archive_show_wishlist', 'yes');

// Show duration
$show_duration = get_theme_mod('tour_archive_show_duration', 'yes');

// Show title
$show_title = get_theme_mod('tour_archive_show_title', 'yes');

// Show location
$show_location = get_theme_mod('tour_archive_show_location', 'yes');

// Show rating
$show_rating = get_theme_mod('tour_archive_show_rating', 'yes');

// Show price
$show_price = get_theme_mod('tour_archive_show_price', 'yes');

// Show button
$show_button = get_theme_mod('tour_archive_show_explore_button', 'yes');

?>
<div class="ova_head_product">
	<?php if ('yes' === $show_featured && $is_featured): ?>
		<div class="ova-is-featured">
			<?php esc_html_e('Featured', 'tripgo'); ?>
		</div>
	<?php endif;

	// Wishlist
	if ('yes' === $show_wishlist && '[yith_wcwl_add_to_wishlist]' != $wishlist): ?>
		<div class="ova-product-wishlist">
			<?php echo $wishlist; ?>
		</div>
	<?php endif;

	// Card gallery
	if (apply_filters('tripgo_product_list_card_gallery', false)): ?>
		<div class="ova-card-gallery">
			<?php wc_get_template('rental/loop/gallery-slideshow.php', [
				'data_options' => apply_filters('tripgo_card_gallery_slide_options', [
					'slidesPerView'         => 1,
					'slidesPerGroup'        => 1,
					'spaceBetween'          => 24,
					'pauseOnMouseEnter'     => true,
					'loop'                  => true,
					'autoplay'              => true,
					'delay'                 => 3000,
					'speed'                 => 500,
					'dots'                  => false,
					'nav'                   => true,
					'rtl'                   => is_rtl() ? true : false
				])
			]); ?>
		</div>
	<?php else: ?>
		<a href="<?php echo esc_url($link); ?>" class="ova-product-thumbnail">
			<?php echo woocommerce_get_product_thumbnail('full'); ?>
		</a>
	<?php endif; ?>
</div>
<div class="ova_foot_product">
	<div class="ova-product-day-title-location">
		<?php if ('yes' === $show_duration && ($numberof_days || ($duration && $numberof_hours)) && $product->is_type('ovabrw_car_rental')) : ?>
			<div class="ova-tour-day">
				<i aria-hidden="true" class="icomoon icomoon-clock"></i>
				<?php if ($duration) {
					// Get number of hours
					$hours = ovabrw_convert_number_to_hours($numberof_hours);

					// Get number of minutes
					$minutes = ovabrw_convert_number_to_minutes($numberof_hours);

					if ($hours && $minutes) {
						if ($hours > 1 && $minutes > 1) {
							echo sprintf(esc_html__('%s horas %s minutos', 'tripgo'), $hours, $minutes);
						} elseif ($hours == 1 && $minutes > 1) {
							echo sprintf(esc_html__('%s hora %s minutos', 'tripgo'), $hours, $minutes);
						} elseif ($hours > 1 && $minutes == 1) {
							echo sprintf(esc_html__('%s horas %s minuto', 'tripgo'), $hours, $minutes);
						} else {
							echo sprintf(esc_html__('%s hora %s minuto', 'tripgo'), $hours, $minutes);
						}
					} elseif (! $hours && $minutes) {
						echo sprintf(_n('%s minuto', '%s minutos', $minutes, 'tripgo'), $minutes);
					} else {
						echo sprintf(_n('%s hora', '%s horas', $hours, 'tripgo'), $hours);
					}
				} else {
					echo sprintf(_n('%s día', '%s días', $numberof_days, 'tripgo'), $numberof_days);
				} ?>
			</div>
		<?php endif;

		// Show title
		if ('yes' === $show_title): ?>
			<h2 class="ova-product-title">
				<a href="<?php echo esc_url($link); ?>">
					<?php echo get_the_title($product_id); ?>
				</a>
			</h2>
		<?php endif;
		
		$short_description = get_post_field('post_excerpt', $product_id);

		if (!empty($short_description)): ?>
			<div class="ova-product-short-description">
				<?php echo wp_kses_post($short_description); ?>
			</div>
		<?php endif;

		// Show location
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
		<?php if ('yes' === $show_rating && wc_review_ratings_enabled() && $rating > 0) : ?>
			<div class="ova-product-review">
				<div class="star-rating" role="img" aria-label="<?php echo sprintf(__('Rated %s out of 5', 'tripgo'), $rating); ?>">
					<span class="rating-percent" style="width: <?php echo esc_attr(($rating / 5) * 100) . '%'; ?>;"></span>
					<?php if ($review_count > 0): ?>
						<span class="rating"><?php echo esc_html($review_count); ?></span>
					<?php else: ?>
						<strong class="rating"><?php echo esc_html($rating); ?></strong>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>
		<div class="ova-product-wrapper-price">
			<?php if ($show_price == 'yes'): ?>
				<div class="ova-product-price">
					<?php if ($sale_price && $regular_price): ?>
						<span class="new-product-price"><?php echo wc_price($sale_price); ?></span>
						<span class="old-product-price"><?php echo wc_price($regular_price); ?></span>
					<?php elseif ($regular_price): ?>
						<span class="new-product-price"><?php echo wc_price($regular_price); ?></span>
					<?php else: ?>
						<?php if ($product && !$product->is_type('ovabrw_car_rental')): ?>
							<span class="new-product-price">
								<?php echo wp_kses_post($product->get_price_html()); ?>
							</span>
						<?php else: ?>
							<span class="no-product-price">
								<?php esc_html_e('Option Price', 'tripgo'); ?>
							</span>
					<?php endif;
					endif; ?>
				</div>
			<?php endif;

			// Show explore button
			if ('yes' === $show_button): ?>
				<a href="<?php echo esc_url($link); ?>" class="btn product-btn-book-now">
					<span><?php esc_html_e('Explorar', 'tripgo'); ?></span>
				</a>
			<?php endif; ?>
		</div>
	</div>
</div>