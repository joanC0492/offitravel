<?php if (!defined('ABSPATH')) exit();

/**
 * OVABRW_Ajax
 */
if (!class_exists('OVABRW_Ajax')) {

	class OVABRW_Ajax
	{

		/**
		 * Contructor
		 */
		public function __construct()
		{
			$ajaxs = [
				'ovabrw_loading_datepicker',
				'ovabrw_calculate_total',
				'ovabrw_show_time',
				'ovabrw_choose_time',
				'ovabrw_duration_change',
				'ovabrw_loading_fixed_dates',
				'ovabrw_check_max_guests',
				'ovabrw_remove_cart',
				'ovabrw_verify_reCAPTCHA',
				'ovabrw_search_ajax',
				'ovabrw_load_product_filter',
				'ovabrw_product_category_ajax',
				'ovabrw_product_destination_ajax',
				'ovabrw_load_data_product_create_order',
				'ovabrw_create_order_get_total',
				'ovabrw_create_order_show_time',
				'ovabrw_add_guest_info_field',
				'ovabrw_edit_guest_info_field',
				'ovabrw_required_guest_info_field',
				'ovabrw_optional_guest_info_field',
				'ovabrw_enable_guest_info_field',
				'ovabrw_disable_guest_info_field',
				'ovabrw_delete_guest_info_field',
				'ovabrw_save_guest_info_field',
				'ovabrw_sort_guest_info_fields',
				'ovabrw_change_type_guest_info_field',
				'ovabrw_render_guest_types',
				'ovabrw_add_guest_info_item'
			];

			foreach ($ajaxs as $name) {
				add_action('wp_ajax_' . $name, [$this, $name]);
				add_action('wp_ajax_nopriv_' . $name, [$this, $name]);
			}
		}

		/**
		 * Ajax loading datepicker
		 */
		public function ovabrw_loading_datepicker()
		{
			// Get product ID
			$product_id = absint(ovabrw_get_meta_data('product_id', $_POST));
			if (!$product_id) wp_die();

			// Get product object
			$product = wc_get_product($product_id);
			if (!$product || !$product->is_type(OVABRW_RENTAL)) wp_die();

			// Get form name
			$form = ovabrw_get_meta_data('form', $_POST);

			// Get datepicker options
			$datepicker_options = ovabrw_get_product_datepicker_options($product_id, $form);
			if (ovabrw_array_exists($datepicker_options)) {
				echo wp_json_encode([
					'datePickerOptions' => $datepicker_options
				]);
			}

			wp_die();
		}

		/**
		 * Calculate totals
		 */
		public function ovabrw_calculate_total()
		{
			// Get product id
			$product_id = sanitize_text_field(ovabrw_get_meta_data('product_id', $_POST));

			// Get pick-up date
			$pickup_date = sanitize_text_field(ovabrw_get_meta_data('pickup_date', $_POST));

			// Get time from
			$time_from = sanitize_text_field(ovabrw_get_meta_data('time_from', $_POST));

			// Get drop-off date
			$dropoff_date = sanitize_text_field(ovabrw_get_meta_data('dropoff_date', $_POST));

			// Get quantity
			$quantity = absint(sanitize_text_field(ovabrw_get_meta_data('quantity', $_POST, 1)));

			// Get deposit
			$deposit = sanitize_text_field(ovabrw_get_meta_data('deposit', $_POST));

			// Get custom checkout fields
			$cckf = ovabrw_recursive_replace('\\', '', ovabrw_get_meta_data('custom_ckf', $_POST));
			$cckf = (array)json_decode($cckf);

			// Get custom checkout field quantity
			$cckf_qty = ovabrw_recursive_replace('\\', '', ovabrw_get_meta_data('cckf_qty', $_POST));
			$cckf_qty = (array)json_decode($cckf_qty, true);

			// Get resources
			$resources = ovabrw_recursive_replace('\\', '', ovabrw_get_meta_data('resources', $_POST));
			$resources = (array)json_decode($resources);

			// Get resource guests
			$resource_guests = ovabrw_recursive_replace('\\', '', ovabrw_get_meta_data('resource_guests', $_POST));
			$resource_guests = (array)json_decode($resource_guests, true);

			// Get services
			$services = isset($_POST['services']) ? ovabrw_recursive_replace('\\', '', $_POST['services'])  : '';
			$services = (array)json_decode($services);

			// Get service guests
			$service_guests = ovabrw_recursive_replace('\\', '', ovabrw_get_meta_data('service_guests', $_POST));
			$service_guests = (array)json_decode($service_guests, true);

			// Get date format
			$date_format = ovabrw_get_date_format();

			// Get number of adults
			$adults = absint(sanitize_text_field(ovabrw_get_meta_data('adults', $_POST)));

			// Get number of children
			$children = absint(sanitize_text_field(ovabrw_get_meta_data('childrens', $_POST)));

			// Get number of babies
			$babies = absint(sanitize_text_field(ovabrw_get_meta_data('babies', $_POST)));

			// Total number of guests
			$total_numberof_guests = $adults + $children + $babies;

			// Guests validation
			$guests_validation = ovabrw_guests_validation($product_id, [
				'numberof_adults'   => $adults,
				'numberof_children' => $children,
				'numberof_babies'   => $babies
			]);

			// Show guests error
			if ($guests_validation) {
				echo json_encode(['error' => $guests_validation]);
				wp_die();
			}

			// Total
			$data_total = [
				'adults_price' 		=> '',
				'childrens_price' 	=> '',
				'babies_price' 		=> ''
			];

			// Duration
			$duration = get_post_meta($product_id, 'ovabrw_duration_checkbox', true);
			if ($duration && $time_from) {
				$pickup_date .= ' ' . $time_from;
			}

			// Check-out
			if (!$dropoff_date) {
				$dropoff_date = ovabrw_get_checkout_date($product_id, strtotime($pickup_date));
			}

			if (ovabrw_qty_by_guests($product_id)) {
				$data_total['qty_by_guests'] = true;
			} else {
				// Check product in order
				$store_quantity = ovabrw_quantity_available_in_order($product_id, strtotime($pickup_date), strtotime($dropoff_date));

				// Check product in cart
				$cart_quantity = ovabrw_quantity_available_in_cart($product_id, 'cart', strtotime($pickup_date), strtotime($dropoff_date));

				// Get array quantity available
				$data_quantity = ovabrw_get_quantity_available($product_id, $store_quantity, $cart_quantity, $quantity, false, 'search');

				// Number quantity available
				$data_total['quantity_available'] = isset($data_quantity['quantity_available']) ? absint($data_quantity['quantity_available']) : 0;

				// Check Unavailable
				$unavailable = ovabrw_check_unavailable($product_id, strtotime($pickup_date), strtotime($dropoff_date));
				if ($unavailable) {
					$data_total['quantity_available'] = 0;
				}

				if (!$data_total['quantity_available']) {
					$data_total['error'] = sprintf(__('%s no está disponible para este horario.<br>Por favor, reserva otro horario.', 'ova-brw'), get_the_title($product_id));

					echo json_encode($data_total);
					wp_die();
				}
			}

			// Cart item
			$cart_item['product_id'] 				= $product_id;
			$cart_item['ovabrw_adults']				= $adults;
			$cart_item['ovabrw_childrens']			= $children;
			$cart_item['ovabrw_babies']				= $babies;
			$cart_item['ovabrw_quantity'] 			= $quantity;
			$cart_item['custom_ckf'] 				= $cckf;
			$cart_item['cckf_qty'] 					= $cckf_qty;
			$cart_item['ovabrw_resources'] 			= $resources;
			$cart_item['ovabrw_resource_guests'] 	= $resource_guests;
			$cart_item['ovabrw_services'] 			= $services;
			$cart_item['ovabrw_service_guests'] 	= $service_guests;
			$cart_item['ova_type_deposit'] 			= $deposit;
			$cart_item['ovabrw_time_from'] 			= $time_from;

			// Price per guests
			$guest_prices = ovabrw_price_per_guests($product_id, strtotime($pickup_date), $adults, $children, $babies, $time_from);

			// Adults price
			if (ovabrw_get_meta_data('adults_price', $guest_prices)) {
				$data_total['adults_price'] = ovabrw_wc_price($guest_prices['adults_price']);
			}

			// Childrens price
			if (ovabrw_get_meta_data('childrens_price', $guest_prices)) {
				$data_total['childrens_price'] = ovabrw_wc_price($guest_prices['childrens_price']);
			}

			// Babies price
			if (ovabrw_get_meta_data('babies_price', $guest_prices)) {
				$data_total['babies_price'] = ovabrw_wc_price($guest_prices['babies_price']);
			}

			// Insurance amount
			$insurance_amount = 0;

			// Type of insurance
			$typeof_insurance = ovabrw_get_post_meta($product_id, 'typeof_insurance', 'general');
			if ('general' === $typeof_insurance) {
				$insurance_amount = (float)ovabrw_get_post_meta($product_id, 'amount_insurance');
				$insurance_amount = $insurance_amount * $total_numberof_guests * $quantity;
			} elseif ('guest' === $typeof_insurance) {
				// Adult insurance
				$adult_insurance 	= (float)ovabrw_get_post_meta($product_id, 'adult_insurance');
				$insurance_amount 	+= $adult_insurance * $adults;

				// Child insurance
				$child_insurance 	= (float)ovabrw_get_post_meta($product_id, 'child_insurance');
				$insurance_amount 	+= $child_insurance * $children;

				// Baby insurance
				$baby_insurance 	= (float)ovabrw_get_post_meta($product_id, 'baby_insurance');
				$insurance_amount 	+= $baby_insurance * $babies;
			}

			// Line Total
			$line_total = get_price_by_guests($product_id, strtotime($pickup_date), strtotime($dropoff_date), $cart_item);
			$data_total['line_total'] = $line_total;

			// Total amount
			$total_amount = $line_total;
			if ($insurance_amount) $total_amount += $insurance_amount;

			// Deposit
			$deposit_enable = get_post_meta($product_id, 'ovabrw_enable_deposit', true);
			if ('yes' === $deposit_enable) {
				$deposit_value = floatval(get_post_meta($product_id, 'ovabrw_amount_deposit', true));
				$deposit_type  = get_post_meta($product_id, 'ovabrw_type_deposit', true);

				if ('deposit' === $deposit) {
					if ('percent' === $deposit_type) {
						$line_total = ($line_total * $deposit_value) / 100;

						if ($insurance_amount && !ovabrw_insurance_paid_once()) {
							$insurance_amount = floatval(($insurance_amount * $deposit_value) / 100);
						}
					} else {
						$line_total = $deposit_value;
					}
				}
			}

			// Insurance amount
			if ($insurance_amount) {
				$line_total += $insurance_amount;

				$insurance_html = sprintf(esc_html__('(incluye %s de seguro)', 'ova-brw'), ovabrw_wc_price($insurance_amount));

				$data_total['insurance_amount'] = apply_filters(OVABRW_PREFIX . 'ajax_insurance_html', $insurance_html, $product_id);
			}

			$data_total = apply_filters(OVABRW_PREFIX . 'ajax_data_total', $data_total, $product_id);

			if ($line_total <= 0) {
				$data_total['line_total'] = ovabrw_wc_price(0);

				if (apply_filters(OVABRW_PREFIX . 'validation_total_add_to_cart', true)) {
					$data_total['error'] = esc_html__('El total debe ser mayor que 0', 'ova-brw');
				}
			} else {
				if ('deposit' === $deposit) {
					$line_total = wp_kses_post(sprintf(__('<span class="show_total">%s</span> <small>(de %s)</small>', 'ova-brw'), ovabrw_wc_price($line_total), ovabrw_wc_price($total_amount)));
				} else {
					$line_total = ovabrw_wc_price($line_total);
				}

				$data_total['line_total'] = apply_filters(OVABRW_PREFIX . 'ajax_total_filter', $line_total, $product_id);
			}

			echo json_encode($data_total);

			wp_die();
		}

		/**
		 * Show Time
		 */
		public function ovabrw_show_time()
		{
			// Get product ID
			$product_id = sanitize_text_field(ovabrw_get_meta_data('product_id', $_POST));
			if (!$product_id) wp_die();

			// Get pick-up date
			$pickup_date = sanitize_text_field(ovabrw_get_meta_data('pickup_date', $_POST));
			if (!strtotime($pickup_date)) wp_die();

			$result 	= [];
			$check_in 	= strtotime($pickup_date);
			$dateformat = ovabrw_get_date_format();
			$timeformat = ovabrw_get_time_format();
			$datetime_format = $dateformat . ' ' . $timeformat;

			$duration 		= get_post_meta($product_id, 'ovabrw_duration_checkbox', true);
			$number_days 	= apply_filters(OVABRW_PREFIX . 'get_number_day', get_post_meta($product_id, 'ovabrw_number_days', true));
			$number_hours 	= get_post_meta($product_id, 'ovabrw_number_hours', true);

			if (!$number_days) $number_days = 0;
			if (!$number_hours) $number_hours = 0;

			if ($duration) {
				$duration_time = ovabrw_get_duration_time($product_id, strtotime($pickup_date));

				if (ovabrw_array_exists($duration_time)) {
					$result['durration'] = ovabrw_get_html_duration($duration_time);

					$check_in 	= strtotime($pickup_date . ' ' . $duration_time[0]);
					$check_out 	= apply_filters('ovabrw_calculate_checkout_by_hours', $check_in + floatval($number_hours) * 60 * 60, $check_in, $number_hours);

					$result['checkout'] = date_i18n($datetime_format, $check_out);
				} else {
					$result['error'] = esc_html__('No hay horario disponible, por favor elige otra fecha.', 'ova-brw');
					echo json_encode(apply_filters(OVABRW_PREFIX . 'show_time', $result, $product_id, $pickup_date));
					wp_die();
				}
			} else {
				if ($check_in < current_time('timestamp')) {
					$result['error'] = sprintf(__('%s no está disponible para este horario.<br>Por favor, reserva otro horario.', 'ova-brw'), get_the_title($product_id));
					echo json_encode(apply_filters(OVABRW_PREFIX . 'show_time', $result, $product_id, $pickup_date));
					wp_die();
				}

				if ($check_in) {
					$check_out = apply_filters(OVABRW_PREFIX . 'calculate_checkout_by_days', $check_in + absint($number_days) * 24 * 60 * 60, $check_in, $number_days);

					$result['checkout'] = date_i18n($dateformat, $check_out);
				}
			}

			if (ovabrw_qty_by_guests($product_id) && $check_in && $check_out) {
				$result['qty_by_guests'] = true;

				// Number of adults
				$adults = absint(sanitize_text_field(ovabrw_get_meta_data('adults', $_POST)));

				// Number of children
				$children = absint(sanitize_text_field(ovabrw_get_meta_data('children', $_POST)));

				// Number of babies
				$babies = absint(sanitize_text_field(ovabrw_get_meta_data('babies', $_POST)));

				// Quantity
				$quantity = absint(sanitize_text_field(ovabrw_get_meta_data('quantity', $_POST)));

				// Min of adults
				$min_adults = absint(get_post_meta($product_id, 'ovabrw_adults_min', true));

				// Min of children
				$min_children = absint(get_post_meta($product_id, 'ovabrw_childrens_min', true));

				// Min of babies
				$min_babies = absint(get_post_meta($product_id, 'ovabrw_babies_min', true));

				// Guests
				$guests = [
					'adults'     => $adults * $quantity,
					'children'   => $children * $quantity,
					'babies'     => $babies * $quantity
				];

				// Get available guests
				$guests_available = ovabrw_validate_guests_available($product_id, $check_in, $check_out, $guests, 'search');

				if (ovabrw_array_exists($guests_available)) {
					// Adults
					if (!$guests_available['adults'] || $guests_available['adults'] < 0) {
						$result['max_adults'] = 0;
						$result['min_adults'] = 0;
						$result['val_adults'] = 0;
					} elseif ($guests_available['adults'] <= $min_adults) {
						$result['max_adults'] = $guests_available['adults'];
						$result['min_adults'] = $guests_available['adults'];
						$result['val_adults'] = $guests_available['adults'];
					} else if ($guests_available['adults'] <= $adults) {
						$result['max_adults'] = $guests_available['adults'];
						$result['min_adults'] = $min_adults;
						$result['val_adults'] = $guests_available['adults'];
					} else {
						$result['max_adults'] = $guests_available['adults'];
						$result['min_adults'] = $min_adults;
						$result['val_adults'] = $result['max_adults'] >= $min_adults ? $min_adults : 0;
					}

					// Children
					if (!$guests_available['children'] || $guests_available['children'] < 0) {
						$result['max_children'] = 0;
						$result['min_children'] = 0;
						$result['val_children'] = 0;
					} elseif ($guests_available['children'] <= $min_children) {
						$result['max_children'] = $guests_available['children'];
						$result['min_children'] = $guests_available['children'];
						$result['val_children'] = $guests_available['children'];
					} else if ($guests_available['children'] <= $children) {
						$result['max_children'] = $guests_available['children'];
						$result['min_children'] = $min_children;
						$result['val_children'] = $guests_available['children'];
					} else {
						$result['max_children'] = $guests_available['children'];
						$result['min_children'] = $min_children;
						$result['val_children'] = $result['max_children'] >= $min_children ? $min_children : 0;
					}

					// Babies
					if (!$guests_available['babies'] || $guests_available['babies'] < 0) {
						$result['max_babies'] = 0;
						$result['min_babies'] = 0;
						$result['val_babies'] = 0;
					} elseif ($guests_available['babies'] < $min_babies) {
						$result['max_babies'] = $guests_available['babies'];
						$result['min_babies'] = $guests_available['babies'];
						$result['val_babies'] = $guests_available['babies'];
					} elseif ($guests_available['babies'] < $babies) {
						$result['max_babies'] = $guests_available['babies'];
						$result['min_babies'] = $min_babies;
						$result['val_babies'] = $guests_available['babies'];
					} else {
						$result['max_babies'] = $guests_available['babies'];
						$result['min_babies'] = $min_babies;
						$result['val_babies'] = $result['max_babies'] >= $min_babies ? $min_babies : 0;
					}
				} else {
					$result['error'] = sprintf(__('%s no está disponible para este horario.<br>Por favor, reserva otro horario.', 'ova-brw'), get_the_title($product_id));
					echo json_encode(apply_filters(OVABRW_PREFIX . 'show_time', $result, $product_id, $pickup_date));
					wp_die();
				}
			}

			echo json_encode(apply_filters(OVABRW_PREFIX . 'show_time', $result, $product_id, $pickup_date));

			wp_die();
		}

		/**
		 * Choose Time
		 */
		public function ovabrw_choose_time()
		{
			// Get product id
			$product_id = sanitize_text_field(ovabrw_get_meta_data('product_id', $_POST));

			// Check-in date
			$check_in = sanitize_text_field(ovabrw_get_meta_data('check_in', $_POST));

			// Check-out date
			$check_out = sanitize_text_field(ovabrw_get_meta_data('check_out', $_POST));

			if (!$product_id || !ovabrw_qty_by_guests($product_id)) wp_die();

			// init
			$result = [];

			// Number of adults
			$adults = absint(sanitize_text_field(ovabrw_get_meta_data('adults', $_POST)));

			// Number of children
			$children = absint(sanitize_text_field(ovabrw_get_meta_data('children', $_POST)));

			// Number of babies
			$babies = absint(sanitize_text_field(ovabrw_get_meta_data('babies', $_POST)));

			// Quantity
			$quantity = absint(sanitize_text_field(ovabrw_get_meta_data('quantity', $_POST, 1)));

			// Min adults
			$min_adults = absint(get_post_meta($product_id, 'ovabrw_adults_min', true));

			// Min children
			$min_children = absint(get_post_meta($product_id, 'ovabrw_childrens_min', true));

			// Min babies
			$min_babies = absint(get_post_meta($product_id, 'ovabrw_babies_min', true));

			// Number of guests
			$guests = [
				'adults'     => $adults * $quantity,
				'children'   => $children * $quantity,
				'babies'     => $babies * $quantity
			];

			// Get number of available guests
			$guests_available = ovabrw_validate_guests_available($product_id, strtotime($check_in), strtotime($check_out), $guests, 'search');

			if (ovabrw_array_exists($guests_available)) {
				// Adults
				if (!$guests_available['adults'] || $guests_available['adults'] < 0) {
					$result['max_adults'] = 0;
					$result['min_adults'] = 0;
					$result['val_adults'] = 0;
				} elseif ($guests_available['adults'] <= $min_adults) {
					$result['max_adults'] = $guests_available['adults'];
					$result['min_adults'] = $guests_available['adults'];
					$result['val_adults'] = $guests_available['adults'];
				} elseif ($guests_available['adults'] <= $adults) {
					$result['max_adults'] = $guests_available['adults'];
					$result['min_adults'] = $min_adults;
					$result['val_adults'] = $guests_available['adults'];
				} else {
					$result['max_adults'] = $guests_available['adults'];
					$result['min_adults'] = $min_adults;
					$result['val_adults'] = $result['max_adults'] >= 1 ? 1 : $min_adults;
				}

				// Children
				if (!$guests_available['children'] || $guests_available['children'] < 0) {
					$result['max_children'] = 0;
					$result['min_children'] = 0;
					$result['val_children'] = 0;
				} elseif ($guests_available['children'] <= $min_children) {
					$result['max_children'] = $guests_available['children'];
					$result['min_children'] = $guests_available['children'];
					$result['val_children'] = $guests_available['children'];
				} else if ($guests_available['children'] <= $children) {
					$result['max_children'] = $guests_available['children'];
					$result['min_children'] = $min_children;
					$result['val_children'] = $guests_available['children'];
				} else {
					$result['max_children'] = $guests_available['children'];
					$result['min_children'] = $min_children;
					$result['val_children'] = $result['max_children'] >= 1 ? 1 : $min_children;
				}

				// Babies
				if (!$guests_available['babies'] || $guests_available['babies'] < 0) {
					$result['max_babies'] = 0;
					$result['min_babies'] = 0;
					$result['val_babies'] = 0;
				} elseif ($guests_available['babies'] < $min_babies) {
					$result['max_babies'] = $guests_available['babies'];
					$result['min_babies'] = $guests_available['babies'];
					$result['val_babies'] = $guests_available['babies'];
				} elseif ($guests_available['babies'] < $babies) {
					$result['max_babies'] = $guests_available['babies'];
					$result['min_babies'] = $min_babies;
					$result['val_babies'] = $guests_available['babies'];
				} else {
					$result['max_babies'] = $guests_available['babies'];
					$result['min_babies'] = $min_babies;
					$result['val_babies'] = $result['max_babies'] >= 1 ? 1 : $min_babies;
				}
			} else {
				$result['error'] = sprintf(__('%s no está disponible para este horario.<br>Por favor, reserva otro horario.', 'ova-brw'), get_the_title($product_id));
				echo json_encode(apply_filters(OVABRW_PREFIX . 'show_time', $result, $product_id, $check_in, $check_out));
				wp_die();
			}

			echo json_encode(apply_filters(OVABRW_PREFIX . 'choose_time', $result, $product_id, $check_in, $check_out));

			wp_die();
		}

		/**
		 * Change Duration
		 */
		public function ovabrw_duration_change()
		{
			// Get product id
			$product_id = sanitize_text_field(ovabrw_get_meta_data('product_id', $_POST));

			// Get pick-up date
			$pickup_date = sanitize_text_field(ovabrw_get_meta_data('pickup_date', $_POST));

			// Get time
			$time = sanitize_text_field(ovabrw_get_meta_data('time', $_POST));

			// Check data
			if (!$product_id || !strtotime($pickup_date) || !$time) wp_die();

			$result 	= [];
			$check_in 	= strtotime($pickup_date);
			$dateformat = ovabrw_get_date_format();
			$timeformat = ovabrw_get_time_format();
			$datetime_format 	= $dateformat . ' ' . $timeformat;
			$number_hours 		= get_post_meta($product_id, 'ovabrw_number_hours', true);

			if (!$number_hours) $number_hours = 0;

			$check_in = strtotime($pickup_date . ' ' . $time);

			if ($check_in) {
				$check_out = $check_in + floatval($number_hours) * 60 * 60;

				$result['checkout'] = date_i18n($datetime_format, $check_out);
			} else {
				$result['error'] = esc_html__('Algo salió mal.', 'ova-brw');
			}

			if (ovabrw_qty_by_guests($product_id) && $check_in && $check_out) {
				$result['qty_by_guests'] = true;

				// Number of adults
				$adults = absint(sanitize_text_field(ovabrw_get_meta_data('adults', $_POST)));

				// Number of children
				$children = absint(sanitize_text_field(ovabrw_get_meta_data('children', $_POST)));

				// Number of babies
				$babies = absint(sanitize_text_field(ovabrw_get_meta_data('babies', $_POST)));

				// Quantity
				$quantity = absint(sanitize_text_field(ovabrw_get_meta_data('quantity', $_POST, 1)));

				// Min of adults
				$min_adults = absint(get_post_meta($product_id, 'ovabrw_adults_min', true));

				// Min of children
				$min_children = absint(get_post_meta($product_id, 'ovabrw_childrens_min', true));

				// Min of babies
				$min_babies = absint(get_post_meta($product_id, 'ovabrw_babies_min', true));

				// Guests
				$guests = [
					'adults'     => $adults * $quantity,
					'children'   => $children * $quantity,
					'babies'     => $babies * $quantity
				];

				// Get available guests
				$guests_available = ovabrw_validate_guests_available($product_id, $check_in, $check_out, $guests, 'search');

				if (ovabrw_array_exists($guests_available)) {
					// Adults
					if (!$guests_available['adults'] || $guests_available['adults'] < 0) {
						$result['max_adults'] = 0;
						$result['min_adults'] = 0;
						$result['val_adults'] = 0;
					} elseif ($guests_available['adults'] <= $min_adults) {
						$result['max_adults'] = $guests_available['adults'];
						$result['min_adults'] = $guests_available['adults'];
						$result['val_adults'] = $guests_available['adults'];
					} else if ($guests_available['adults'] <= $adults) {
						$result['max_adults'] = $guests_available['adults'];
						$result['min_adults'] = $min_adults;
						$result['val_adults'] = $guests_available['adults'];
					} else {
						$result['max_adults'] = $guests_available['adults'];
						$result['min_adults'] = $min_adults;
						$result['val_adults'] = $result['max_adults'] >= 1 ? 1 : $min_adults;
					}

					// Children
					if (!$guests_available['children'] || $guests_available['children'] < 0) {
						$result['max_children'] = 0;
						$result['min_children'] = 0;
						$result['val_children'] = 0;
					} elseif ($guests_available['children'] <= $min_children) {
						$result['max_children'] = $guests_available['children'];
						$result['min_children'] = $guests_available['children'];
						$result['val_children'] = $guests_available['children'];
					} else if ($guests_available['children'] <= $children) {
						$result['max_children'] = $guests_available['children'];
						$result['min_children'] = $min_children;
						$result['val_children'] = $guests_available['children'];
					} else {
						$result['max_children'] = $guests_available['children'];
						$result['min_children'] = $min_children;
						$result['val_children'] = $result['max_children'] >= 1 ? 1 : $min_children;
					}

					// Babies
					if (!$guests_available['babies'] || $guests_available['babies'] < 0) {
						$result['max_babies'] = 0;
						$result['min_babies'] = 0;
						$result['val_babies'] = 0;
					} elseif ($guests_available['babies'] < $min_babies) {
						$result['max_babies'] = $guests_available['babies'];
						$result['min_babies'] = $guests_available['babies'];
						$result['val_babies'] = $guests_available['babies'];
					} elseif ($guests_available['babies'] < $babies) {
						$result['max_babies'] = $guests_available['babies'];
						$result['min_babies'] = $min_babies;
						$result['val_babies'] = $guests_available['babies'];
					} else {
						$result['max_babies'] = $guests_available['babies'];
						$result['min_babies'] = $min_babies;
						$result['val_babies'] = $result['max_babies'] >= 1 ? 1 : $min_babies;
					}
				} else {
					$result['error'] = sprintf(__('%s no está disponible para este horario.<br>Por favor, reserva otro horario.', 'ova-brw'), get_the_title($product_id));
					echo json_encode(apply_filters(OVABRW_PREFIX . 'duration_change', $result, $product_id, $pickup_date));
					wp_die();
				}
			}

			echo json_encode(apply_filters(OVABRW_PREFIX . 'duration_change', $result, $product_id, $pickup_date));

			wp_die();
		}

		/**
		 * Fixed Date Loading
		 */
		public function ovabrw_loading_fixed_dates()
		{
			// Get product id
			$product_id = (int)ovabrw_get_meta_data('product_id', $_POST);

			// Get next page
			$next = (int)ovabrw_get_meta_data('next', $_POST);

			if ($product_id && $next) {
				$fixed_date = ovabrw_get_fixed_dates($product_id, $next);

				if (ovabrw_array_exists($fixed_date)) {
					ob_start();

					foreach ($fixed_date as $date_range => $date_string): ?>
						<option value="<?php echo esc_attr($date_range); ?>" <?php selected($date_range, array_key_first($fixed_date)); ?>>
							<?php echo esc_html($date_string); ?>
						</option>
				<?php endforeach;

					$options = ob_get_contents();
					ob_end_clean();

					echo apply_filters(OVABRW_PREFIX . 'ajax_loading_fixed_dates', $options, $_POST);
				}
			}

			wp_die();
		}

		/**
		 * Check maximum number of guests
		 */
		public function ovabrw_check_max_guests()
		{
			// Get product id
			$product_id = absint(sanitize_text_field(ovabrw_get_meta_data('product_id', $_POST)));
			if (!$product_id || !ovabrw_qty_by_guests($product_id)) wp_die();

			// init
			$result = [];

			// Get number of adults
			$adults = absint(sanitize_text_field(ovabrw_get_meta_data('adults', $_POST)));

			// Get number of children
			$children = absint(sanitize_text_field(ovabrw_get_meta_data('children', $_POST)));

			// Get number of babies
			$babies = absint(sanitize_text_field(ovabrw_get_meta_data('babies', $_POST)));

			// Get max number of guests
			$max_guest = (int)get_post_meta($product_id, 'ovabrw_max_total_guest', true);
			if ($max_guest && $max_guest < ($adults + $children + $babies)) {
				$result['error'] = sprintf(esc_html__('Número máximo total de invitados: %s', 'ova-brw'), $max_guest);

				echo json_encode($result);
				wp_die();
			}

			wp_die();
		}

		/**
		 * Remove Cart
		 */
		public function ovabrw_remove_cart()
		{
			// Get cart item key
			$cart_item_key = sanitize_text_field(ovabrw_get_meta_data('cart_item_key', $_POST));

			if ($cart_item_key && false !== WC()->cart->remove_cart_item($cart_item_key)) {
				$count = WC()->cart->get_cart_contents_count();

				echo absint($count);
			} else {
				echo '';
			}

			wp_die();
		}

		/**
		 * Verify reCAPTCHA
		 */
		public function ovabrw_verify_reCAPTCHA()
		{
			// Get error
			$error = ovabrw_get_recaptcha_error();

			// Check data post
			if (!isset($_POST) && empty($_POST)) {
				echo esc_html($error);
				wp_die();
			}

			// Get token
			$token = sanitize_text_field(ovabrw_get_meta_data('token', $_POST));

			// Get action
			$action = sanitize_text_field(ovabrw_get_meta_data('form', $_POST));

			// Score
			$score = apply_filters(OVABRW_PREFIX . 'recaptcha_score', 0.5);

			if (!$token) {
				echo esc_html($error);
				wp_die();
			} else {
				// Params
				$params = [
					'secret'   => ovabrw_get_recaptcha_secret_key(),
					'response' => $token,
					'remoteip' => ovabrw_get_client_ip()
				];

				// Options
				$opts = [
					'http' => [
						'method'  => 'POST',
						'header'  => 'Content-type: application/x-www-form-urlencoded',
						'content' => http_build_query($params)
					]
				];

				$context = stream_context_create($opts);
				$res     = file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
				$res     = json_decode($res, true);

				if ($res['success'] && $res['hostname'] == ovabrw_get_recaptcha_host()) {
					if ('v3' === ovabrw_get_recaptcha_type() && $res['action'] == $action && $res['score'] > $score) {
						$error = '';
					} elseif ('v2' === ovabrw_get_recaptcha_type()) {
						$error = '';
					}
				} else {
					if (isset($res['error-codes'][0]) && $res['error-codes'][0]) {
						$error = ovabrw_get_recaptcha_error($res['error-codes'][0]);
					}
				}
			}

			echo esc_html($error);

			wp_die();
		}

		/**
		 * Search Ajax
		 */
		public function ovabrw_search_ajax()
		{
			// Data post
			$data = $_POST;

			// Get layout
			$layout = sanitize_text_field(ovabrw_get_meta_data('layout', $data, 'grid'));

			// Get columns
			$grid_column = sanitize_text_field(ovabrw_get_meta_data('grid_column', $data, 'column4'));

			// Get thumbnail type
			$thumbnail_type = sanitize_text_field(ovabrw_get_meta_data('thumbnail_type', $data, 'image'));

			// Get order
			$order = sanitize_text_field(ovabrw_get_meta_data('order', $data, 'DESC'));

			// Get orderby
			$orderby = sanitize_text_field(ovabrw_get_meta_data('orderby', $data, 'ID'));

			// Get orderby meta key
			$orderby_meta_key = sanitize_text_field(ovabrw_get_meta_data('orderby_meta_key', $data));

			// Posts per page
			$posts_per_page = sanitize_text_field(ovabrw_get_meta_data('posts_per_page', $data, 4));

			// Default category
			$default_category = ovabrw_get_meta_data('default_category', $data, []);

			// Show category
			$show_category = sanitize_text_field(ovabrw_get_meta_data('show_category', $data));

			// Show paged
			$paged = (int)ovabrw_get_meta_data('paged', $data, 1);

			// Destination
			$destination = sanitize_text_field(ovabrw_get_meta_data('destination', $data, 'all'));

			// Custom taxonomy
			$custom_taxonomy = ovabrw_get_meta_data('custom_taxonomy', $data, []);

			// Taxonomy value
			$taxonomy_value = ovabrw_get_meta_data('taxonomy_value', $data, []);

			// Get pick-up date (full date or month-only 01–12)
			$start_date_raw = sanitize_text_field(ovabrw_get_meta_data('start_date', $data));
			$pickup_parsed  = ovabrw_parse_pickup_date_for_search($start_date_raw);
			$pickup_date    = false;
			if ($pickup_parsed && isset($pickup_parsed['type']) && 'timestamp' === $pickup_parsed['type']) {
				$pickup_date = $pickup_parsed['ts'];
			} elseif (
				! $pickup_parsed
				|| ! isset($pickup_parsed['type'])
				|| ! in_array($pickup_parsed['type'], [ 'month', 'months' ], true)
			) {
				if ('' !== $start_date_raw) {
					$pickup_date = strtotime($start_date_raw);
				}
			}

			// Number of adults
			$adults = (int)sanitize_text_field(ovabrw_get_meta_data('adults', $data));

			// Number of children
			$children = (int)sanitize_text_field(ovabrw_get_meta_data('childrens', $data));

			// Number of babies
			$babies = (int)sanitize_text_field(ovabrw_get_meta_data('babies', $data));

			// Start price
			$start_price = (int)ovabrw_get_meta_data('start_price', $data);

			// End price
			$end_price = (int)ovabrw_get_meta_data('end_price', $data);

			// Review score
			$review_score = ovabrw_get_meta_data('review_score', $data, []);

			// Categories
			$categories = ovabrw_get_meta_data('categories', $data, []);

			// Duration from
			$duration_from = ovabrw_get_meta_data('duration_from', $data, 0);

			// Duration to
			$duration_to = ovabrw_get_meta_data('duration_to', $data);

			// Duration type
			$duration_type = ovabrw_get_meta_data('duration_type', $data);

			// Clicked
			$clicked = ovabrw_get_meta_data('clicked', $data);

			// Get taxonomy
			$list_taxonomy = ovabrw_create_type_taxonomies();

			// Base query
			$args_base = [
				'post_type'      	=> 'product',
				'post_status'    	=> 'publish',
				'posts_per_page' 	=> $posts_per_page,
				'paged' 			=> $paged,
				'order' 			=> $order,
				'orderby' 			=> $orderby,
				'meta_key'          => $orderby_meta_key,
				'tax_query' 		=> [
					[
						'taxonomy' => 'product_type',
						'field'    => 'slug',
						'terms'    => OVABRW_RENTAL
					]
				]
			];

			switch ($orderby) {
				case 'featured':
					$args_base['orderby'] = 'ID';

					array_push($args_base['tax_query'], [
						'taxonomy' => 'product_visibility',
						'field'    => 'name',
						'terms'    => 'featured',
						'operator' => 'IN'
					]);
					break;
				case 'popularity':
					$args_base['meta_key'] 	= 'total_sales';
					$args_base['orderby'] 	= 'meta_value_num';
					break;
				case 'rating':
					$args_base['meta_key'] 	= '_wc_average_rating';
					$args_base['orderby'] 	= 'meta_value_num';
					break;
				case 'price':
					$args_base['meta_key'] 	= '_price';
					$args_base['orderby'] 	= 'meta_value_num';
					break;
			}

			$data_taxonomy = [];

			if (ovabrw_array_exists($custom_taxonomy)) {
				foreach ($custom_taxonomy as $k => $slug) {
					if ($slug && isset($taxonomy_value[$k]) && $taxonomy_value[$k]) {
						$data_taxonomy[str_replace('_name', '', $slug)] = $taxonomy_value[$k];
					}
				}
			}

			// Tax Query custom taxonomy
			$arg_taxonomy_arr = [];

			if (ovabrw_array_exists($list_taxonomy)) {
				foreach ($list_taxonomy as $taxonomy) {
					$slug = $taxonomy['slug'];
					$taxonomy_get = ovabrw_get_meta_data($slug, $data_taxonomy, 'all');

					if ($taxonomy_get && $taxonomy_get != 'all') {
						$arg_taxonomy_arr[] = [
							'taxonomy' => $taxonomy['slug'],
							'field'    => 'slug',
							'terms'    => $taxonomy_get
						];
					}
				}
			}

			$args_meta_query_arr = $args_cus_meta_custom = $args_cus_tax_custom = array();

			if ($args_base) {
				if ($destination != 'all') {
					$args_meta_query_arr[] = [
						'key'     => 'ovabrw_destination',
						'value'   => $destination,
						'compare' => 'LIKE',
					];
				}

				if ($review_score != []) {
					$args_meta_query_arr[] = [
						'key'     => '_wc_average_rating',
						'value'   => $review_score,
						'type'    => 'numeric',
						'compare' => 'IN',
					];
				}

				// Filter by number of adults
				if ($adults) {
					$args_meta_query_arr[] = [
						'key'     => 'ovabrw_adults_max',
						'value'   => $adults,
						'type'    => 'numeric',
						'compare' => '>='
					];
				}

				// Filter by number of children
				if ($children) {
					$args_meta_query_arr[] = [
						'key'     => 'ovabrw_childrens_max',
						'value'   => $children,
						'type'    => 'numeric',
						'compare' => '>='
					];
				}

				// Filter by number of babies
				if ($babies) {
					$args_meta_query_arr[] = [
						'key'     => 'ovabrw_babies_max',
						'value'   => $babies,
						'type'    => 'numeric',
						'compare' => '>='
					];
				}

				if ($end_price != '') {
					$args_meta_query_arr[] = [
						'relation' => 'OR',
						[
							'key'     => '_price',
							'value'   => [$start_price, $end_price],
							'type'    => 'numeric',
							'compare' => 'BETWEEN'
						],
						[
							'key'     => '_sale_price',
							'value'   => [$start_price, $end_price],
							'type'    => 'numeric',
							'compare' => 'BETWEEN',
						]
					];
				}

				if ($categories != []) {
					$arg_taxonomy_arr[] = [
						'taxonomy' => 'product_cat',
						'field'    => 'slug',
						'terms'    => $categories
					];
				} else {
					if (ovabrw_array_exists($default_category) && $show_category != 'yes') {
						$arg_taxonomy_arr[] = [
							'taxonomy' => 'product_cat',
							'field'    => 'slug',
							'terms'    => $default_category
						];
					}
				}

				// Duration check
				if ($duration_to == '') {
					$duration_to = '9999';
				}

				if ($duration_type == 'day') {
					$args_meta_query_arr[] = [
						'key'     => 'ovabrw_duration_checkbox',
						'compare' => 'NOT EXISTS',
					];

					$args_meta_query_arr[] = [
						'key'     => 'ovabrw_number_days',
						'value'   => [$duration_from, $duration_to],
						'type'    => 'numeric',
						'compare' => 'BETWEEN',
					];
				}

				if ($duration_type == 'hour') {
					$args_meta_query_arr[] = [
						'key'     => 'ovabrw_duration_checkbox',
						'value'   => 1,
						'type'    => 'numeric',
						'compare' => '=',
					];

					$args_meta_query_arr[] = [
						'key'     => 'ovabrw_number_hours',
						'value'   => [$duration_from, $duration_to],
						'type'    => 'numeric',
						'compare' => 'BETWEEN',
					];
				}

				if (ovabrw_array_exists($arg_taxonomy_arr)) {
					$args_cus_tax_custom = [
						'tax_query' => [
							'relation'  => 'AND',
							$arg_taxonomy_arr
						]
					];
				}

				if (ovabrw_array_exists($args_meta_query_arr)) {
					$args_cus_meta_custom = [
						'meta_query' => [
							'relation'  => 'AND',
							$args_meta_query_arr
						]
					];
				}

				// Get exclude ids
				if ($pickup_parsed && isset($pickup_parsed['type']) && 'month' === $pickup_parsed['type']) {
					$exclude_ids = ovabrw_get_exclude_ids_for_month_range($pickup_parsed['year'], $pickup_parsed['month']);
					$args_base['post__not_in'] = $exclude_ids;
				} elseif ($pickup_parsed && isset($pickup_parsed['type']) && 'months' === $pickup_parsed['type']) {
					$exclude_ids = ovabrw_get_exclude_ids_for_months_intersection($pickup_parsed['year'], $pickup_parsed['months']);
					$args_base['post__not_in'] = $exclude_ids;
				} elseif ($pickup_date) {
					$exclude_ids = ovabrw_get_exclude_ids($pickup_date);
					$args_base['post__not_in'] = $exclude_ids;
				}

				// Merge query
				$args = array_merge_recursive($args_base, $args_cus_tax_custom, $args_cus_meta_custom);

				// Get products
				$products = new WP_Query(apply_filters(OVABRW_PREFIX . 'query_search_ajax', $args, $data));

				// Number found posts
				$number_results_found = $products->found_posts;

				if (is_plugin_active('woocommerce-multilingual/wpml-woocommerce.php')) {
					add_filter('wcml_load_multi_currency_in_ajax', '__return_true');
				}

				ob_start(); ?>
				<div class="ovabrw-products-result ovabrw-products-result-<?php echo esc_attr($layout); ?> <?php echo esc_attr($grid_column); ?>" data-clicked="<?php echo esc_attr($clicked); ?>">

					<?php

					//var_dump($data);

					if ($products->have_posts()) : while ($products->have_posts()) : $products->the_post();
							if ('gallery' === $thumbnail_type) {
								add_filter(OVABRW_PREFIX . 'product_list_card_gallery', '__return_true');
							} else {
								add_filter(OVABRW_PREFIX . 'product_list_card_gallery', '__return_false');
							}

							if ('grid' === $layout) {
								wc_get_template_part('content', 'product');
							} elseif ('list' === $layout) {
								wc_get_template_part('rental/content-item', 'product-list');
							}
						endwhile;
					else: ?>
						<div class="not_found_product">
							<h3 class="empty-list">
								<?php esc_html_e('Tours no disponibles', 'ova-brw'); ?>
							</h3>
							<p>
								<?php esc_html_e('Parece que no podemos encontrar lo que buscas.', 'ova-brw'); ?>
							</p>
						</div>
					<?php endif;
					wp_reset_postdata(); ?>
					<input
						type="hidden"
						class="tour_number_results_found"
						name="tour_number_results_found"
						value="<?php echo esc_attr($number_results_found); ?>" />
				</div>
				<?php
				// Max number of pages
				$total = $products->max_num_pages;

				if ($total > 1): ?>
					<div class="ovabrw-pagination-ajax" data-paged="<?php echo esc_attr($paged); ?>">
						<?php echo ovabrw_pagination_ajax($number_results_found, $products->query_vars['posts_per_page'], $paged); ?>
					</div>
				<?php
				endif;

				$result = ob_get_contents();
				ob_end_clean();

				echo json_encode(['result' => $result]);
				wp_die();
			} else {
				echo json_encode(['result' => $result]);
				wp_die();
			}
		}

		/**
		 * Filter Product
		 */
		public function ovabrw_load_product_filter()
		{
			// Get term
			$term = sanitize_text_field(ovabrw_get_meta_data('term', $_POST, 'all'));

			// Show on sale
			$show_on_sale = sanitize_text_field(ovabrw_get_meta_data('show_on_sale', $_POST, 'no'));

			// Data show
			$args_show = ovabrw_get_meta_data('args_show', $_POST, []);

			// Posts per page
			$posts_per_page = (int)ovabrw_get_meta_data('posts_per_page', $_POST, 4);

			// Orderby
			$orderby = sanitize_text_field(ovabrw_get_meta_data('orderby', $_POST, 'ID'));

			// Order
			$order = sanitize_text_field(ovabrw_get_meta_data('order', $_POST, 'DESC'));

			// Slide options
			$slide_options = ovabrw_get_meta_data('slide_options', $_POST, []);

			if ('all' != $term) {
				$args = [
					'post_type'   		=> 'product',
					'posts_per_page' 	=> $posts_per_page,
					'post_status' 		=> 'publish',
					'orderby'	 		=> $orderby,
					'order' 	 		=> $order,
					'fields'    		=> 'ids',
					'tax_query' 		=> [
						'relation' => 'AND',
						[
							'taxonomy' => 'product_cat',
							'field'    => 'slug',
							'terms'    => $term
						],
						[
							'taxonomy' => 'product_type',
							'field'    => 'slug',
							'terms'    => OVABRW_RENTAL
						]
					]
				];
			} else {
				$args = [
					'post_type'   		=> 'product',
					'posts_per_page' 	=> $posts_per_page,
					'post_status' 		=> 'publish',
					'orderby' 			=> $orderby,
					'order'	  			=> $order,
					'fields'    		=> 'ids',
					'tax_query' 		=> [
						[
							'taxonomy' => 'product_type',
							'field'    => 'slug',
							'terms'    => OVABRW_RENTAL
						]
					]
				];
			}

			if ('yes' === $show_on_sale) {
				$product_ids_on_sale = wc_get_product_ids_on_sale();
				$args['post__in'] = $product_ids_on_sale;
			}

			// Get list product
			$list_product = get_posts($args);

			// Results
			$results = '';

			ob_start();

			// Loop
			if (ovabrw_array_exists($list_product)): ?>
				<div class="swiper swiper-loading">
					<div class="swiper-wrapper">
						<?php foreach ($list_product as $pid) {
							ovabrw_get_template('elementor/ovabrw_product_filter_ajax.php', [
								'id' 		=> $pid,
								'args_show' => $args_show
							]);
						} ?>
					</div>
				</div>
				<?php if (ovabrw_get_meta_data('nav', $slide_options)): ?>
					<div class="swiper-nav">
						<div class="button-nav button-prev">
							<i class="icomoon icomoon-pre-small" aria-hidden="true"></i>
						</div>
						<div class="button-nav button-next">
							<i class="icomoon icomoon-next-small" aria-hidden="true"></i>
						</div>
					</div>
				<?php endif; ?>
				<?php if (ovabrw_get_meta_data('dots', $slide_options)): ?>
					<div class="button-dots"></div>
			<?php endif;
			endif; // END loop
			wp_reset_postdata();

			$results = ob_get_contents();
			ob_end_clean();

			echo $results;

			wp_die();
		}

		/**
		 * Filter Product by Category
		 */
		public function ovabrw_product_category_ajax()
		{
			// Data post
			$data = $_POST;
			if (!ovabrw_array_exists($data)) wp_die();

			// Get term id
			$term_id = sanitize_text_field(ovabrw_get_meta_data('term_id', $data));

			// Get posts per page
			$posts_per_page = sanitize_text_field(ovabrw_get_meta_data('posts_per_page', $data, 9));

			// Paged
			$paged = sanitize_text_field(ovabrw_get_meta_data('paged', $data, 1));
			if (!$paged) $paged = 1;

			// Order
			$order = sanitize_text_field(ovabrw_get_meta_data('order', $data, 'DESC'));

			// Orderby
			$orderby = sanitize_text_field(ovabrw_get_meta_data('orderby', $data, 'date'));

			// Get layout
			$layout = sanitize_text_field(ovabrw_get_meta_data('layout', $data, 'grid'));

			// Get template
			$grid_template = sanitize_text_field(ovabrw_get_meta_data('grid_template', $data, 'template_1'));

			// Column
			$column = sanitize_text_field(ovabrw_get_meta_data('column', $data, 'column3'));

			// Thumbnail type
			$thumbnail_type = sanitize_text_field(ovabrw_get_meta_data('thumbnail_type', $data, 'image'));

			// Pagination
			$pagination = sanitize_text_field(ovabrw_get_meta_data('pagination', $data, 'yes'));

			// Base query
			$args_query = [
				'post_type'      	=> 'product',
				'post_status'    	=> 'publish',
				'posts_per_page' 	=> $posts_per_page,
				'paged' 			=> $paged,
				'order' 			=> $order,
				'orderby' 			=> $orderby,
				'tax_query' 		=> [
					'relation'  => 'AND',
					[
						'taxonomy' => 'product_type',
						'field'    => 'slug',
						'terms'    => OVABRW_RENTAL
					]
				]
			];

			// Term id
			if ($term_id) {
				$args_query['tax_query'][] = [
					'taxonomy' => 'product_cat',
					'field'    => 'term_id',
					'terms'    => $term_id
				];
			}

			// Get products
			$products = new WP_Query(apply_filters(OVABRW_PREFIX . 'query_product_category_ajax', $args_query, $data));

			ob_start(); ?>
			<div class="ovabrw-products-result ovabrw-products-result-<?php echo esc_attr($layout); ?> grid-layout-<?php echo esc_attr($grid_template); ?> <?php echo esc_attr($column); ?>">
				<?php if ($products->have_posts()) : while ($products->have_posts()) : $products->the_post();
						if ('gallery' === $thumbnail_type) {
							add_filter(OVABRW_PREFIX . 'product_list_card_gallery', '__return_true');
						} else {
							add_filter(OVABRW_PREFIX . 'product_list_card_gallery', '__return_false');
						}

						if ('grid' === $layout) {
							wc_get_template_part('content', 'product');
						} elseif ('list' === $layout) {
							wc_get_template_part('rental/content-item', 'product-list');
						}
					endwhile;
				else: ?>
					<div class="not_found_product">
						<h3 class="empty-list">
							<?php esc_html_e('Tours no disponibles', 'ova-brw'); ?>
						</h3>
						<p>
							<?php esc_html_e('Parece que no podemos encontrar lo que buscas.', 'ova-brw'); ?>
						</p>
					</div>
				<?php endif;
				wp_reset_postdata(); ?>
			</div>
			<?php if ($pagination):
				$total 			= $products->max_num_pages;
				$found_posts 	= $products->found_posts;

				if ($total > 1): ?>
					<div class="ovabrw-pagination-ajax" data-paged="<?php echo esc_attr($paged); ?>">
						<?php echo ovabrw_pagination_ajax($found_posts, $products->query_vars['posts_per_page'], $paged); ?>
					</div>
			<?php endif;
			endif;

			$result = ob_get_contents();
			ob_end_clean();

			echo json_encode(['result' => $result]);

			wp_die();
		}

		/**
		 * Filter Product by Destination
		 */
		public function ovabrw_product_destination_ajax()
		{
			// Date post
			$data = $_POST;
			if (!ovabrw_array_exists($data)) wp_die();

			// Destination id
			$destination_id = sanitize_text_field(ovabrw_get_meta_data('destination_id', $data));

			// Posts per page
			$posts_per_page = sanitize_text_field(ovabrw_get_meta_data('posts_per_page', $data, 9));

			// Paged
			$paged = sanitize_text_field(ovabrw_get_meta_data('paged', $data, 1));
			if (!$paged) $paged = 1;

			// Order
			$order = sanitize_text_field(ovabrw_get_meta_data('order', $data, 'DESC'));

			// Orderby
			$orderby = sanitize_text_field(ovabrw_get_meta_data('orderby', $data, 'date'));

			// Layout
			$layout = sanitize_text_field(ovabrw_get_meta_data('layout', $data, 'grid'));

			// Column
			$column = sanitize_text_field(ovabrw_get_meta_data('column', $data, 'column3'));

			// Thumbnail type
			$thumbnail_type = sanitize_text_field(ovabrw_get_meta_data('thumbnail_type', $data, 'image'));

			// Pagination
			$pagination = sanitize_text_field(ovabrw_get_meta_data('pagination', $data, 'yes'));

			// Base query
			$args_query = [
				'post_type'      	=> 'product',
				'post_status'    	=> 'publish',
				'posts_per_page' 	=> $posts_per_page,
				'paged' 			=> $paged,
				'order' 			=> $order,
				'orderby' 			=> $orderby,
				'tax_query' 		=> [
					'relation'  => 'AND',
					[
						'taxonomy' => 'product_type',
						'field'    => 'slug',
						'terms'    => OVABRW_RENTAL
					]
				]
			];

			// Destination ID
			if ($destination_id) {
				$args_query['meta_query'][] = [
					'key'     => 'ovabrw_destination',
					'value'   => $destination_id,
					'compare' => 'LIKE',
				];
			}

			// Get products
			$products = new WP_Query(apply_filters(OVABRW_PREFIX . 'query_product_category_ajax', $args_query, $data));

			ob_start(); ?>
			<div class="ovabrw-products-result ovabrw-products-result-<?php echo esc_attr($layout); ?> <?php echo esc_attr($column); ?>">
				<?php if ($products->have_posts()) : while ($products->have_posts()) : $products->the_post();
						if ('gallery' === $thumbnail_type) {
							add_filter(OVABRW_PREFIX . 'product_list_card_gallery', '__return_true');
						} else {
							add_filter(OVABRW_PREFIX . 'product_list_card_gallery', '__return_false');
						}

						if ('grid' === $layout) {
							wc_get_template_part('content', 'product');
						} elseif ('list' === $layout) {
							wc_get_template_part('rental/content-item', 'product-list');
						}
					endwhile;
				else: ?>
					<div class="not_found_product">
						<h3 class="empty-list">
							<?php esc_html_e('Tours no disponibles', 'ova-brw'); ?>
						</h3>
						<p>
							<?php esc_html_e('Parece que no podemos encontrar lo que buscas.', 'ova-brw'); ?>
						</p>
					</div>
				<?php endif;
				wp_reset_postdata(); ?>
			</div>
			<?php if ($pagination):
				$total 			= $products->max_num_pages;
				$found_posts 	= $products->found_posts;

				if ($total > 1): ?>
					<div class="ovabrw-pagination-ajax" data-paged="<?php echo esc_attr($paged); ?>">
						<?php echo ovabrw_pagination_ajax($found_posts, $products->query_vars['posts_per_page'], $paged); ?>
					</div>
<?php endif;
			endif;

			$result = ob_get_contents();
			ob_end_clean();

			echo json_encode(['result' => $result]);

			wp_die();
		}

		/**
		 * Create New Order - Loading Product
		 */
		public function ovabrw_load_data_product_create_order()
		{
			// Get product id
			$product_id = absint(sanitize_text_field(ovabrw_get_meta_data('product_id', $_POST)));

			// Currency
			$currency = sanitize_text_field(ovabrw_get_meta_data('currency', $_POST));

			// Currency symbol
			$currency_symbol = get_woocommerce_currency_symbol($currency);

			// Price
			$adult_price = (float)get_post_meta($product_id, '_regular_price', true);

			// Children price
			$children_price = (float)ovabrw_get_post_meta($product_id, 'children_price');

			// Baby price
			$baby_price = (float)ovabrw_get_post_meta($product_id, 'baby_price');

			// Multiple Currency
			$adult_price 	= ovabrw_convert_price($adult_price, ['currency' => $currency]);
			$children_price = ovabrw_convert_price($children_price, ['currency' => $currency]);
			$baby_price 	= ovabrw_convert_price($baby_price, ['currency' => $currency]);

			// Adults
			$adults_max = ovabrw_get_post_meta($product_id, 'adults_max');
			$adults_min = ovabrw_get_post_meta($product_id, 'adults_min');

			// Children
			$show_children 	= ovabrw_show_children($product_id);
			$children_max 	= ovabrw_get_post_meta($product_id, 'childrens_max');
			$children_min 	= ovabrw_get_post_meta($product_id, 'childrens_min');

			// Babies
			$show_babies 	= ovabrw_show_babies($product_id);
			$babies_max 	= ovabrw_get_post_meta($product_id, 'babies_max');
			$babies_min 	= ovabrw_get_post_meta($product_id, 'babies_min');

			// Amount of insurance
			$amount_insurance = 0;

			// Type of insurance
			$typeof_insurance = ovabrw_get_post_meta($product_id, 'typeof_insurance', 'general');
			if ('general' === $typeof_insurance) {
				$amount_insurance = (float)ovabrw_get_post_meta($product_id, 'amount_insurance');
			} elseif ('guest' === $typeof_insurance) {
				$amount_insurance = (float)ovabrw_get_post_meta($product_id, 'adult_insurance');
			}

			$amount_insurance = ovabrw_convert_price($amount_insurance, [
				'currency' => $currency
			]);

			// Number of tours
			$stock_quantity = ovabrw_get_post_meta($product_id, 'stock_quantity');

			// Fixed Times
			$html_fixed_times = ovabrw_get_html_fixed_time_order($product_id);

			// Get html custom checkout fields
			$html_custom_ckf = ovabrw_get_html_ckf_order($product_id);

			// Get html resources
			$html_resources = ovabrw_get_html_resources_order($product_id, $currency);

			// Get html services
			$html_services = ovabrw_get_html_services_order($product_id, $currency);

			ob_start();
			include(OVABRW_PLUGIN_ADMIN . 'bookings/views/html-create-booking-meta-boxes.php');
			$meta_boxes = ob_get_contents();
			ob_end_clean();

			$data = [
				'metaBoxes' 		=> $meta_boxes,
				'currencySymbol' 	=> $currency_symbol,
				'adultPrice' 		=> $adult_price ? $adult_price : 0,
				'minAdults' 		=> $adults_min ? $adults_min : 1,
				'maxAdults' 		=> $adults_max ? $adults_max : 1,
				'showChildren' 		=> $show_children,
				'childPrice' 		=> $children_price ? $children_price : 0,
				'minChildren' 		=> $children_min ? $children_min : 0,
				'max_children' 		=> $children_max ? $children_max : 0,
				'showBabies' 		=> $show_babies,
				'babyPrice' 		=> $baby_price ? $baby_price : 0,
				'minBabies' 		=> $babies_min ? $babies_min : 0,
				'maxBabies' 		=> $babies_max ? $babies_max : 0,
				'insuranceAmount' 	=> $amount_insurance ? $amount_insurance : 0,
				'quantity' 			=> $stock_quantity ? $stock_quantity : 1,
				'cckfHTML' 			=> $html_custom_ckf,
				'resourcesHTML' 	=> $html_resources,
				'servicesHTML' 		=> $html_services,
				'fixedTimeHTML' 	=> $html_fixed_times,
				'qtyByGuests' 		=> ovabrw_qty_by_guests($product_id)
			];

			echo wp_json_encode($data);

			wp_die();
		}

		/**
		 * Create New Order - Get Total
		 */
		public function ovabrw_create_order_get_total()
		{
			// Get product id
			$product_id = absint(sanitize_text_field(ovabrw_get_meta_data('product_id', $_POST)));

			// Get currency
			$currency = trim(sanitize_text_field(ovabrw_get_meta_data('currency', $_POST)));

			// Pick-up date
			$pickup_date = trim(sanitize_text_field(ovabrw_get_meta_data('start_date', $_POST)));

			// Time from
			$time_from = sanitize_text_field(ovabrw_get_meta_data('time_from', $_POST));

			// Drop-off date
			$dropoff_date = trim(sanitize_text_field(ovabrw_get_meta_data('end_date', $_POST)));

			// Deposit amount
			$deposit_amount = floatval(sanitize_text_field(ovabrw_get_meta_data('deposit_amount', $_POST)));

			// Custom checkout fields
			$cckf = ovabrw_recursive_replace('\\', '', ovabrw_get_meta_data('custom_ckf', $_POST));
			$cckf = (array)json_decode($cckf);

			// Custom checkout field quantities
			$cckf_qty = ovabrw_recursive_replace('\\', '', ovabrw_get_meta_data('cckf_qty', $_POST));
			$cckf_qty = (array)json_decode($cckf_qty);

			// Resources
			$resources = ovabrw_recursive_replace('\\', '', ovabrw_get_meta_data('resources', $_POST));
			$resources = (array)json_decode($resources);

			// Resource guests
			$resource_guests = ovabrw_recursive_replace('\\', '', ovabrw_get_meta_data('resource_guests', $_POST));
			$resource_guests = (array)json_decode($resource_guests, true);

			// Services
			$services = ovabrw_recursive_replace('\\', '', ovabrw_get_meta_data('services', $_POST));
			$services = (array)json_decode($services);

			// Service guests
			$service_guests = ovabrw_recursive_replace('\\', '', ovabrw_get_meta_data('service_guests', $_POST));
			$service_guests = (array)json_decode($service_guests, true);

			// Get date format
			$date_format = ovabrw_get_date_format();

			// Min of adults
			$min_adults = absint(get_post_meta($product_id, 'ovabrw_adults_min', true));

			// Min of children
			$min_children = absint(get_post_meta($product_id, 'ovabrw_childrens_min', true));

			// Min babies
			$min_babies = absint(get_post_meta($product_id, 'ovabrw_babies_min', true));

			// Number of adults
			$numberof_adults = absint(sanitize_text_field(ovabrw_get_meta_data('adults', $_POST, $min_adults)));

			// Number of children
			$numberof_children = absint(sanitize_text_field(ovabrw_get_meta_data('childrens', $_POST, $min_children)));

			// Number of babies
			$numberof_babies = absint(sanitize_text_field(ovabrw_get_meta_data('babies', $_POST, $min_babies)));

			// Cart item
			$cart_item = [
				'product_id' 				=> $product_id,
				'ovabrw_adults' 			=> $numberof_adults,
				'ovabrw_childrens' 			=> $numberof_children,
				'ovabrw_babies' 			=> $numberof_babies,
				'ovabrw_quantity' 			=> 1,
				'custom_ckf' 				=> $cckf,
				'cckf_qty' 					=> $cckf_qty,
				'ovabrw_resources' 			=> $resources,
				'ovabrw_resource_guests' 	=> $resource_guests,
				'ovabrw_services' 			=> $services,
				'ovabrw_service_guests' 	=> $service_guests,
				'ovabrw_time_from' 			=> $time_from
			];

			// Total
			$data_total = [
				'error' 			=> '',
				'remaining_amount' 	=> 0,
				'adults_price' 		=> 0,
				'childrens_price' 	=> 0,
				'babies_price' 		=> 0
			];

			// Duration
			$duration = get_post_meta($product_id, 'ovabrw_duration_checkbox', true);
			if ($duration && $time_from) {
				$pickup_date .= ' ' . $time_from;
			}

			// Price Per Guests
			$guest_prices = ovabrw_price_per_guests($product_id, strtotime($pickup_date), $numberof_adults, $numberof_children, $numberof_babies, $time_from);

			// Adult price
			if (ovabrw_check_array($guest_prices, 'adults_price')) {
				$data_total['adults_price'] = ovabrw_convert_price($guest_prices['adults_price'], ['currency' => $currency]);
			}

			// Children price
			if (ovabrw_check_array($guest_prices, 'childrens_price')) {
				$data_total['childrens_price'] = ovabrw_convert_price($guest_prices['childrens_price'], ['currency' => $currency]);
			}

			// Baby price
			if (ovabrw_check_array($guest_prices, 'babies_price')) {
				$data_total['babies_price'] = ovabrw_convert_price($guest_prices['babies_price'], ['currency' => $currency]);
			}

			// Amount insurance
			$amount_insurance = 0;

			// Type of insurance
			$typeof_insurance = ovabrw_get_post_meta($product_id, 'typeof_insurance', 'general');
			if ('general' === $typeof_insurance) {
				$amount_insurance = (float)ovabrw_get_post_meta($product_id, 'amount_insurance');
				$amount_insurance = $amount_insurance * ($numberof_adults + $numberof_children + $numberof_babies);
			} elseif ('guest' === $typeof_insurance) {
				// Adult insurance
				$adult_insurance 	= (float)ovabrw_get_post_meta($product_id, 'adult_insurance');
				$amount_insurance 	+= $adult_insurance * $numberof_adults;

				// Child insurance
				$child_insurance 	= (float)ovabrw_get_post_meta($product_id, 'child_insurance');
				$amount_insurance 	+= $child_insurance * $numberof_children;

				// Baby insurance
				$baby_insurance 	= (float)ovabrw_get_post_meta($product_id, 'baby_insurance');
				$amount_insurance 	+= $baby_insurance * $numberof_babies;
			}

			// Add insurance amount
			$data_total['amount_insurance'] = ovabrw_convert_price($amount_insurance, ['currency' => $currency]);

			// Line Total
			$line_total = get_price_by_guests($product_id, strtotime($pickup_date), strtotime($dropoff_date), $cart_item);
			$line_total += $amount_insurance;
			$line_total = ovabrw_convert_price($line_total, ['currency' => $currency]);
			$amount_insurance = ovabrw_convert_price($amount_insurance, ['currency' => $currency]);

			$data_total['line_total'] = $line_total;

			$data_total = apply_filters(OVABRW_PREFIX . 'ajax_create_order_data_total', $data_total, $product_id);

			// Deposit
			if ($deposit_amount) {
				if ($deposit_amount <= $line_total) {
					$remaining_amount = $line_total - $deposit_amount - $amount_insurance;
					$data_total['remaining_amount'] = $remaining_amount;
				} else {
					$data_total['line_total'] 		= 0;
					$data_total['remaining_amount'] = 0;
					$data_total['error'] 			= esc_html__('El monto del depósito es mayor que el total.', 'ova-brw');
				}
			}

			echo json_encode($data_total);

			wp_die();
		}

		/**
		 * Create New Order - Show Time
		 */
		public function ovabrw_create_order_show_time()
		{
			// Get product id
			$product_id = sanitize_text_field(ovabrw_get_meta_data('product_id', $_POST));

			// Pick-up date
			$pickup_date = sanitize_text_field(ovabrw_get_meta_data('pickup_date', $_POST));
			if (!$product_id || !strtotime($pickup_date)) wp_die();

			$result 	= [];
			$check_in 	= strtotime($pickup_date);
			$dateformat = ovabrw_get_date_format();
			$timeformat = ovabrw_get_time_format();
			$datetime_format = $dateformat . ' ' . $timeformat;

			$duration 		= get_post_meta($product_id, 'ovabrw_duration_checkbox', true);
			$number_days 	= get_post_meta($product_id, 'ovabrw_number_days', true);
			$number_hours 	= get_post_meta($product_id, 'ovabrw_number_hours', true);

			if (!$number_days) $number_days = 0;
			if (!$number_hours) $number_hours = 0;

			if ($duration) {
				$duration_time = ovabrw_get_duration_time($product_id, strtotime($pickup_date));

				if (ovabrw_array_exists($duration_time)) {
					$result['durration'] = ovabrw_create_order_get_html_duration($product_id, $duration_time);

					$check_in 	= strtotime($pickup_date . ' ' . $duration_time[0]);
					$check_out 	= apply_filters('ovabrw_calculate_checkout_by_hours', $check_in + floatval($number_hours) * 60 * 60, $check_in, $number_hours);

					$result['checkout'] = date_i18n($datetime_format, $check_out);
				} else {
					$result['error'] = esc_html__('No hay horario disponible, por favor elige otra fecha.', 'ova-brw');
					echo json_encode($result);
					wp_die();
				}
			} else {
				if ($check_in) {
					$check_out = apply_filters(OVABRW_PREFIX . 'calculate_checkout_by_days', $check_in + absint($number_days) * 24 * 60 * 60, $check_in, $number_days);

					$result['checkout'] = date_i18n($dateformat, $check_out);
				}
			}

			if (ovabrw_qty_by_guests($product_id) && $check_in && $check_out) {
				$result['qty_by_guests'] = true;

				// Number of adults
				$adults = absint(sanitize_text_field(ovabrw_get_meta_data('adults', $_POST)));

				// Number of children
				$children = absint(sanitize_text_field(ovabrw_get_meta_data('children', $_POST)));

				// Number of babies
				$babies = absint(sanitize_text_field(ovabrw_get_meta_data('babies', $_POST)));

				// Quantity
				$quantity = absint(sanitize_text_field(ovabrw_get_meta_data('quantity', $_POST, 1)));

				// Min of adults
				$min_adults = absint(get_post_meta($product_id, 'ovabrw_adults_min', true));

				// Min of children
				$min_children = absint(get_post_meta($product_id, 'ovabrw_childrens_min', true));

				// Min of babies
				$min_babies = absint(get_post_meta($product_id, 'ovabrw_babies_min', true));

				// Guests
				$guests = [
					'adults'     => $adults * $quantity,
					'children'   => $children * $quantity,
					'babies'     => $babies * $quantity
				];

				// Get available guests
				$guests_available = ovabrw_validate_guests_available($product_id, $check_in, $check_out, $guests, 'search');

				if (ovabrw_array_exists($guests_available)) {
					// Adults
					if (!$guests_available['adults'] || $guests_available['adults'] < 0) {
						$result['max_adults'] = 0;
						$result['min_adults'] = 0;
						$result['val_adults'] = 0;
					} elseif ($guests_available['adults'] <= $min_adults) {
						$result['max_adults'] = $guests_available['adults'];
						$result['min_adults'] = $guests_available['adults'];
						$result['val_adults'] = $guests_available['adults'];
					} else if ($guests_available['adults'] <= $adults) {
						$result['max_adults'] = $guests_available['adults'];
						$result['min_adults'] = $min_adults;
						$result['val_adults'] = $guests_available['adults'];
					} else {
						$result['max_adults'] = $guests_available['adults'];
						$result['min_adults'] = $min_adults;
						$result['val_adults'] = $result['max_adults'] >= 1 ? 1 : $min_adults;
					}

					// Children
					if (!$guests_available['children'] || $guests_available['children'] < 0) {
						$result['max_children'] = 0;
						$result['min_children'] = 0;
						$result['val_children'] = 0;
					} elseif ($guests_available['children'] <= $min_children) {
						$result['max_children'] = $guests_available['children'];
						$result['min_children'] = $guests_available['children'];
						$result['val_children'] = $guests_available['children'];
					} else if ($guests_available['children'] <= $children) {
						$result['max_children'] = $guests_available['children'];
						$result['min_children'] = $min_children;
						$result['val_children'] = $guests_available['children'];
					} else {
						$result['max_children'] = $guests_available['children'];
						$result['min_children'] = $min_children;
						$result['val_children'] = $result['max_children'] >= 1 ? 1 : $min_children;
					}

					// Babies
					if (!$guests_available['babies'] || $guests_available['babies'] < 0) {
						$result['max_babies'] = 0;
						$result['min_babies'] = 0;
						$result['val_babies'] = 0;
					} elseif ($guests_available['babies'] < $min_babies) {
						$result['max_babies'] = $guests_available['babies'];
						$result['min_babies'] = $guests_available['babies'];
						$result['val_babies'] = $guests_available['babies'];
					} elseif ($guests_available['babies'] < $babies) {
						$result['max_babies'] = $guests_available['babies'];
						$result['min_babies'] = $min_babies;
						$result['val_babies'] = $guests_available['babies'];
					} else {
						$result['max_babies'] = $guests_available['babies'];
						$result['min_babies'] = $min_babies;
						$result['val_babies'] = $result['max_babies'] >= 1 ? 1 : $min_babies;
					}
				} else {
					$result['error'] = sprintf(__('%s no está disponible para este horario.<br>Por favor, reserva otro horario.', 'ova-brw'), get_the_title($product_id));
					echo json_encode($result);
					wp_die();
				}
			}

			echo json_encode($result);

			wp_die();
		}

		/**
		 * Add guest info field
		 */
		public function ovabrw_add_guest_info_field()
		{
			check_admin_referer('ovabrw-security-ajax', 'security');

			ob_start();

			// include html add new
			OVABRW_Guest_Info_Fields::instance()->popup_guest_info_field();

			echo wp_json_encode(['html' => ob_get_clean()]);
			wp_die();
		}

		/**
		 * Edit guest info field
		 */
		public function ovabrw_edit_guest_info_field()
		{
			check_admin_referer('ovabrw-security-ajax', 'security');

			$name = ovabrw_get_meta_data('name', $_POST);
			$type = ovabrw_get_meta_data('type', $_POST);

			if (!$name || !$type) wp_die();

			ob_start();

			// include html add new
			OVABRW_Guest_Info_Fields::instance()->popup_guest_info_field('edit', $type, $name);

			echo wp_json_encode(['html' => ob_get_clean()]);
			wp_die();
		}

		/**
		 * Required guests info field
		 */
		public function ovabrw_required_guest_info_field()
		{
			check_admin_referer('ovabrw-security-ajax', 'security');

			// Get fields
			$fields = ovabrw_get_meta_data('fields', $_POST);

			// Get name
			$name = ovabrw_get_meta_data('name', $_POST);

			// Check
			if (!$name && !ovabrw_array_exists($fields)) wp_die();

			$post['fields'] = $name ? [$name] : $fields;

			OVABRW_Guest_Info_Fields::instance()->required($post);

			echo 1;
			wp_die();
		}

		/**
		 * Optional guests info field
		 */
		public function ovabrw_optional_guest_info_field()
		{
			check_admin_referer('ovabrw-security-ajax', 'security');

			// Get fields
			$fields = ovabrw_get_meta_data('fields', $_POST);

			// Get name
			$name = ovabrw_get_meta_data('name', $_POST);

			// Check
			if (!$name && !ovabrw_array_exists($fields)) wp_die();

			$post['fields'] = $name ? [$name] : $fields;

			OVABRW_Guest_Info_Fields::instance()->optional($post);

			echo 1;
			wp_die();
		}

		/**
		 * Enable guests info field
		 */
		public function ovabrw_enable_guest_info_field()
		{
			check_admin_referer('ovabrw-security-ajax', 'security');

			// Get fields
			$fields = ovabrw_get_meta_data('fields', $_POST);

			// Get name
			$name = ovabrw_get_meta_data('name', $_POST);

			// Check
			if (!$name && !ovabrw_array_exists($fields)) wp_die();

			$post['fields'] = $name ? [$name] : $fields;

			OVABRW_Guest_Info_Fields::instance()->enable($post);

			echo 1;
			wp_die();
		}

		/**
		 * Disable guests info field
		 */
		public function ovabrw_disable_guest_info_field()
		{
			check_admin_referer('ovabrw-security-ajax', 'security');

			// Get fields
			$fields = ovabrw_get_meta_data('fields', $_POST);

			// Get name
			$name = ovabrw_get_meta_data('name', $_POST);

			// Check
			if (!$name && !ovabrw_array_exists($fields)) wp_die();

			$post['fields'] = $name ? [$name] : $fields;

			OVABRW_Guest_Info_Fields::instance()->disable($post);

			echo 1;
			wp_die();
		}

		/**
		 * Delete guests info field
		 */
		public function ovabrw_delete_guest_info_field()
		{
			check_admin_referer('ovabrw-security-ajax', 'security');

			// Get fields
			$fields = ovabrw_get_meta_data('fields', $_POST);

			// Get name
			$name = ovabrw_get_meta_data('name', $_POST);

			// Check
			if (!$name && !ovabrw_array_exists($fields)) wp_die();

			$post['fields'] = $name ? [$name] : $fields;

			OVABRW_Guest_Info_Fields::instance()->delete($post);

			echo 1;
			wp_die();
		}

		/**
		 * Save guest info field
		 */
		public function ovabrw_save_guest_info_field()
		{
			check_admin_referer('ovabrw-security-ajax', 'security');

			// Get data
			$data = ovabrw_get_meta_data('data', $_POST);
			$data = ovabrw_recursive_replace('\\', '', $data);

			// Save field
			OVABRW_Guest_Info_Fields::instance()->save($data);

			echo esc_html('Saved', 'ova-brw');
			wp_die();
		}

		/**
		 * Sort guest info fields
		 */
		public function ovabrw_sort_guest_info_fields()
		{
			check_admin_referer('ovabrw-security-ajax', 'security');
			OVABRW_Guest_Info_Fields::instance()->sort($_POST);
			wp_die();
		}

		/**
		 * Change type guest info field
		 */
		public function ovabrw_change_type_guest_info_field()
		{
			check_admin_referer('ovabrw-security-ajax', 'security');

			$type = ovabrw_get_meta_data('type', $_POST);
			if (!$type) wp_die();

			ob_start();

			// include html add new
			OVABRW_Guest_Info_Fields::instance()->popup_guest_info_field('new', $type);

			echo wp_json_encode(['html' => ob_get_clean()]);
			wp_die();
		}

		/**
		 * Render guest types
		 */
		public function ovabrw_render_guest_types()
		{
			// Get product ID
			$product_id = absint(ovabrw_get_meta_data('product_id', $_POST));

			// Guests
			$guests = ovabrw_get_meta_data('guests', $_POST);

			// Render HTML
			ob_start();

			// Get template
			wc_get_template('rental/loop/fields/guest-types.php', [
				'id' 		=> $product_id,
				'guests' 	=> $guests
			]);

			$html = ob_get_contents();
			ob_end_clean();

			echo wp_json_encode([
				'html' => $html
			]);

			wp_die();
		}

		/**
		 * Add guest info item
		 */
		public function ovabrw_add_guest_info_item()
		{
			// Get product ID
			$product_id = absint(ovabrw_get_meta_data('product_id', $_POST));

			// Guest name
			$guest_name = ovabrw_get_meta_data('guest_name', $_POST);

			// Number of guests
			$numberof_guests = (int)ovabrw_get_meta_data('numberof_guests', $_POST);
			$numberof_guests -= 1;
			if ($numberof_guests < 0) wp_die();

			// Render HTML
			ob_start();

			// Get template
			wc_get_template('rental/loop/fields/guest-info.php', [
				'id' 			=> $product_id,
				'guest_name' 	=> $guest_name,
				'key'        	=> $numberof_guests
			]);

			$html = ob_get_contents();
			ob_end_clean();

			echo wp_json_encode([
				'html' => $html
			]);

			wp_die();
		}
	}

	// init class
	new OVABRW_Ajax();
}
