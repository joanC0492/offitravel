<?php if (!defined('ABSPATH')) exit();

// Get product id
$product_id = tripgo_get_meta_data('id', $args, get_the_id());

// Get product
$product = wc_get_product($product_id);
if (!$product) return;

// Gallery data
$gallery_data = [];

// Gallery ids
$gallery_ids = $product->get_gallery_image_ids();
if (tripgo_array_exists($gallery_ids)) {
	foreach ($gallery_ids as $k => $gallery_id) {
		// Image URL
		$img_url = wp_get_attachment_image_url($gallery_id, 'tripgo_product_gallery');

		// Image alt
		$img_alt = get_post_meta($gallery_id, '_wp_attachment_image_alt', true);
		if (!$img_alt) $img_alt = get_the_title($gallery_id);

		array_push($gallery_data, [
			'src' 		=> $img_url,
			'caption' 	=> $img_alt,
			'thumb' 	=> $img_url
		]);
	}
}

// Embed URL
$embed_url = tripgo_get_post_meta($product_id, 'embed_video');

// Video controls
$controls = apply_filters('tripgo_video_controls', [
	'autoplay'  => 'yes',
	'mute'      => 'no',
	'loop'      => 'yes',
	'controls'  => 'yes',
	'modest'    => 'yes',
	'rel'       => 'yes'
]);

// Product permalink
$link 	= apply_filters('woocommerce_loop_product_link', get_the_permalink(), $product);
$param 	= [];

// Start date
$start_date = tripgo_get_meta_data('start_date', $_POST);
if ($start_date) {
	$param['pickup_date']  = $start_date;
}

// Number of adults
$numberof_adults = tripgo_get_meta_data('adults', $_POST);
if ($numberof_adults) {
	$param['ovabrw_adults'] = $numberof_adults;
}

// Number of children
$numberof_children = tripgo_get_meta_data('childrens', $_POST);
if ($numberof_children) {
	$param['ovabrw_childrens'] = $numberof_children;
}

// Add query arg
if ($param) {
	$link = add_query_arg($param, $link);
}

// Product title
$title = get_the_title($product_id);

// Get product image ID
$image_id = get_post_thumbnail_id();

// Product image URL
$image_url = get_the_post_thumbnail_url($product_id, 'full');
if (!$image_url) {
	$image_url = \Elementor\Utils::get_placeholder_image_src();
}

// Product image alt
$image_alt 	= '';
if ($image_id) {
	$image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
	if (!$image_alt) {
		$image_alt = get_the_title($image_id);
	}
}

// Max number of guests
$max_guests = '';

// Max total of guests
$max_total_guest = tripgo_get_post_meta($product_id, 'max_total_guest');
if (absint($max_total_guest)) {
	$max_guests = absint($max_total_guest);
} else {
	// Max guests
	$max_guests = absint(tripgo_get_post_meta($product_id, 'adults_max'));

	// Show children
	if (function_exists('ovabrw_show_children') && ovabrw_show_children($product_id)) {
		$max_guests += absint(tripgo_get_post_meta($product_id, 'childrens_max'));
	}

	// Show baby
	if (function_exists('ovabrw_show_babies') && ovabrw_show_babies($product_id)) {
		$max_guests += absint(tripgo_get_post_meta($product_id, 'babies_max'));
	}
}

// Number of days
$numberof_days = (int)tripgo_get_post_meta($product_id, 'number_days');

// Number of hours
$numberof_hours = (int)tripgo_get_post_meta($product_id, 'number_hours');

// Duration
$duration = tripgo_get_post_meta($product_id, 'duration_checkbox');

// Wishlist
$wishlist = do_shortcode('[yith_wcwl_add_to_wishlist]');

// Featured product
$is_featured = $product->is_featured();

// Address
$address = tripgo_get_post_meta($product_id, 'address');

// Short address
$short_address = tripgo_get_post_meta($product_id, 'short_address');
if ($short_address) $address = $short_address;

// Review count
$review_count = $product->get_review_count();

// Rating
$rating = $product->get_average_rating();

// Short description
$short_description = get_the_excerpt($product_id);

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

// Show max guest
$show_max_guest = get_theme_mod('tour_archive_show_max_guest', 'yes');

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

<div class="ovabrw-single-product">
	<div class="product-img">
		<?php if (apply_filters('tripgo_product_list_card_gallery', false)): ?>
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
			<img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($image_alt); ?>">
		<?php endif;

		// is featured
		if ('yes' === $show_featured && $is_featured): ?>
			<div class="ova-is-featured">
				<?php esc_html_e('Featured', 'tripgo'); ?>
			</div>
		<?php endif;

		// Wishlist
		if ('yes' === $show_wishlist && '[yith_wcwl_add_to_wishlist]' != $wishlist): ?>
			<div class="ova-product-wishlist">
				<?php echo $wishlist; ?>
			</div>
		<?php endif; ?>
		<div class="ova-video-gallery">
			<?php if ($embed_url): ?>
				<div class="btn-video btn-video-gallery"
					data-src="<?php echo esc_url($embed_url); ?>"
					data-controls="<?php echo esc_attr(json_encode($controls)); ?>">
					<i aria-hidden="true" class="icomoon icomoon-caret-circle-right"></i>
					<?php esc_html_e('View video', 'tripgo'); ?>
				</div>
				<div class="video-container">
					<div class="modal-container">
						<div class="modal">
							<i class="ovaicon-cancel"></i>
							<iframe class="modal-video" allow="autoplay" allowfullscreen></iframe>
						</div>
					</div>
				</div>
			<?php endif;

			// Gallery data
			if (tripgo_array_exists($gallery_data)): ?>
				<div class="btn-gallery btn-video-gallery fancybox"
					data-gallery="<?php echo esc_attr(json_encode($gallery_data)); ?>">
					<i aria-hidden="true" class="icomoon icomoon-gallery"></i>
					<?php echo sprintf(_n('%s foto', '%s fotos', count($gallery_data), 'tripgo'), count($gallery_data)); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
	<div class="product-container">
		<div class="product-container-left">
			<?php if ('yes' === $show_title): ?>
				<a href="<?php echo esc_url($link); ?>">
					<h2 class="product-title">
						<?php echo esc_html($title); ?>
					</h2>
				</a>
			<?php endif;

			// Address
			if ('yes' === $show_location && $address): ?>
				<div class="ova-product-location">
					<i aria-hidden="true" class="icomoon icomoon-location"></i>
					<span class="location">
						<?php echo esc_html($address); ?>
					</span>
				</div>
			<?php endif;

			// Review
			if ('yes' === $show_rating && wc_review_ratings_enabled() && $rating > 0): ?>
				<div class="ova-product-review">
					<div class="star-rating" role="img" aria-label="<?php echo sprintf(__('Rated %s out of 5', 'tripgo'), $rating); ?>">
						<span class="rating-percent" style="width: <?php echo esc_attr(($rating / 5) * 100) . '%'; ?>;"></span>
					</div>
					<a href="<?php echo esc_url($link); ?>#reviews" class="woo-review-link" rel="nofollow">
						<?php echo sprintf(_n('%s review', '%s reviews', $review_count, 'tripgo'), esc_html($review_count)); ?>
					</a>
				</div>
			<?php endif;

			// Short description
			if ($short_description): ?>
				<div class="product-short-description">
					<?php echo wp_kses_post($short_description); ?>
				</div>
			<?php endif; ?>
		</div>
		<div class="product-container-right">
			<?php if ('yes' === $show_max_guest && $max_guests): ?>
				<div class="ova-tour-day ova-max-people">
					<i aria-hidden="true" class="icomoon icomoon-profile-circle"></i>
					<?php echo esc_html($max_guests); ?>
				</div>
			<?php endif;

			// Number of days
			if ('yes' === $show_duration && $numberof_days): ?>
				<div class="ova-tour-day">
					<i aria-hidden="true" class="icomoon icomoon-clock"></i>
					<?php if ($duration) {
						// Obtener número de horas
						$hours = ovabrw_convert_number_to_hours($numberof_hours);

						// Obtener número de minutos
						$minutes = ovabrw_convert_number_to_minutes($numberof_hours);

						if ($hours && $minutes) {
							if ($hours > 1 && $minutes > 1) {
								echo sprintf(esc_html__('%s horas %s minutos', 'tripgo'), $hours, $minutes);
							} elseif (1 == $hours && $minutes > 1) {
								echo sprintf(esc_html__('%s hora %s minutos', 'tripgo'), $hours, $minutes);
							} elseif ($hours > 1 && 1 == $minutes) {
								echo sprintf(esc_html__('%s horas %s minuto', 'tripgo'), $hours, $minutes);
							} else {
								echo sprintf(esc_html__('%s hora %s minuto', 'tripgo'), $hours, $minutes);
							}
						} elseif (!$hours && $minutes) {
							echo sprintf(_n('%s minuto', '%s minutos', $minutes, 'tripgo'), $minutes);
						} else {
							echo sprintf(_n('%s hora', '%s horas', $hours, 'tripgo'), $hours);
						}
					} else {
						echo sprintf(_n('%s día', '%s días', $numberof_days, 'tripgo'), $numberof_days);
					} ?>
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
						<?php elseif ($regular_price): ?>
							<span class="new-product-price">
								<?php echo wc_price($regular_price); ?>
							</span>
							<?php else:
							if ($product && !$product->is_type('ovabrw_car_rental')): ?>
								<span class="new-product-price">
									<?php echo wp_kses_post($product->get_price_html()); ?>
								</span>
							<?php else: ?>
								<span class="no-product-price">
									<?php esc_html_e('Precio de la opción', 'tripgo'); ?>
								</span>
						<?php endif;
						endif; ?>
					</div>
				<?php endif;

				// Show explore button
				if ('yes' === $show_button): ?>
					<a href="<?php echo esc_url($link); ?>" class="btn product-btn-book-now">
						<?php esc_html_e('Explorar', 'tripgo'); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>