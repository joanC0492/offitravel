/**
 * OVA: mismo patrón que offitravel_room_* — el payload custom de habitaciones arma el objeto
 * sin leer checkboxes de addons. Extendemos con offitravel_addons[] y jQuery.ajax antes de serializar.
 */
(function ($) {
	'use strict';

	var POST_KEY = 'offitravel_addons';
	var AGE_POST_KEY = 'offitravel_age_addons';
	var AGE_RECALC_DELAY = 250;

	function collectAddonIdsFromForm($form) {
		var out = [];
		if (!$form || !$form.length) {
			return out;
		}
		$form
			.find('input[name="offitravel_addons[]"]:checked')
			.each(function () {
				var v = $(this).val();
				if (v) {
					out.push(String(v));
				}
			});
		return out;
	}

	/**
	 * Collect only selected traveler-age rows from one booking form.
	 *
	 * Prices and subtotals are deliberately absent; PHP resolves both from the
	 * assigned service and its stored rules.
	 *
	 * @param {jQuery} $form Booking form.
	 * @returns {Object<string,Object<string,Object<string,{selected:string,age:string}>>>}
	 */
	function collectTravelerAgeFromForm($form) {
		var rows = [];
		if (!$form || !$form.length) {
			return {};
		}
		$form.find('[data-offitravel-age-service]').each(function () {
			var $service = $(this);
			var serviceId = String($service.data('offitravel-age-service') || '');
			if (!serviceId) {
				return;
			}
			$service.find('[data-offitravel-traveler-row]').each(function () {
				var $row = $(this);
				var $checkbox = $row.find('[data-offitravel-traveler-selected]');
				if (!$checkbox.prop('checked')) {
					return;
				}
				var room = String($row.data('room') || '');
				var position = String($row.data('position') || '');
				if (!room || !position) {
					return;
				}
				rows.push({
					serviceId: serviceId,
					room: room,
					position: position,
					selected: true,
					age: String($row.find('[data-offitravel-traveler-age]').val() || ''),
				});
			});
		});
		var stateApi = window.offitravelProductAddonTravelerState;
		return stateApi && typeof stateApi.buildTravelerAgePayload === 'function'
			? stateApi.buildTravelerAgePayload(rows)
			: {};
	}

	/**
	 * Collect the selection and direct age used by client-side form validity.
	 *
	 * Server-side validation remains authoritative; this state only prevents a
	 * customer from submitting while the visible total is stale or incomplete.
	 *
	 * @param {jQuery} $form Booking form.
	 * @returns {Array<{selected:boolean,age:string}>} Rendered traveler states.
	 */
	function collectTravelerAgeValidationRows($form) {
		var rows = [];
		$form.find('[data-offitravel-traveler-row]').each(function () {
			var $row = $(this);
			rows.push({
				selected: $row.find('[data-offitravel-traveler-selected]').prop('checked') === true,
				age: String($row.find('[data-offitravel-traveler-age]').val() || ''),
			});
		});
		return rows;
	}

	/**
	 * Determine whether every selected traveler has a non-negative integer age.
	 *
	 * @param {jQuery} $form Booking form.
	 * @returns {boolean} Whether selected traveler ages are ready to submit.
	 */
	function selectedTravelerAgesAreValid($form) {
		var stateApi = window.offitravelProductAddonTravelerState;
		return !stateApi || typeof stateApi.selectedTravelerAgesAreValid !== 'function'
			? true
			: stateApi.selectedTravelerAgesAreValid(collectTravelerAgeValidationRows($form));
	}

	/**
	 * Explain invalid selected ages beside their own input and expose ARIA state.
	 *
	 * @param {jQuery} $form Booking form.
	 * @returns {void}
	 */
	function updateTravelerAgeValidationMessages($form) {
		var stateApi = window.offitravelProductAddonTravelerState;
		if (!stateApi || typeof stateApi.travelerAgeValidationMessage !== 'function') {
			return;
		}
		$form.find('[data-offitravel-traveler-row]').each(function () {
			var $row = $(this);
			var $age = $row.find('[data-offitravel-traveler-age]');
			var selected = $row.find('[data-offitravel-traveler-selected]').prop('checked') === true;
			var reveal = $row.data('offitravel-age-validation-revealed') === true;
			var message = stateApi.travelerAgeValidationMessage(selected, String($age.val() || ''), reveal);
			var $error = $row.find('[data-offitravel-traveler-age-error]');
			$age.attr('aria-invalid', message ? 'true' : 'false');
			$error.text(message).prop('hidden', !message);
		});
	}

	/**
	 * Reveal feedback for selected travelers whose direct age is invalid.
	 *
	 * @param {jQuery} $form Booking form.
	 * @returns {jQuery} First invalid age input, or an empty jQuery object.
	 */
	function revealInvalidTravelerAges($form) {
		var stateApi = window.offitravelProductAddonTravelerState;
		var $firstInvalid = $();
		$form.find('[data-offitravel-traveler-row]').each(function () {
			var $row = $(this);
			var selected = $row.find('[data-offitravel-traveler-selected]').prop('checked') === true;
			var $age = $row.find('[data-offitravel-traveler-age]');
			var valid = stateApi && typeof stateApi.isTravelerAgeValueValid === 'function'
				? stateApi.isTravelerAgeValueValid(String($age.val() || ''))
				: true;
			if (selected && !valid) {
				$row.data('offitravel-age-validation-revealed', true);
				if (!$firstInvalid.length) {
					$firstInvalid = $age;
				}
			}
		});
		updateTravelerAgeValidationMessages($form);
		return $firstInvalid;
	}

	/**
	 * Synchronize the real submit-button state with age validity and AJAX state.
	 *
	 * @param {jQuery} $form Booking form.
	 * @param {boolean} pending Whether the latest age recalculation is pending.
	 * @returns {void}
	 */
	function updateTravelerAgeBookingButton($form, pending) {
		updateTravelerAgeValidationMessages($form);
		var $button = $form.find('button.booking-form-submit');
		if (!$button.length) {
			return;
		}
		var shouldDisable = pending === true;
		$button.prop('disabled', shouldDisable);
		$button.attr('aria-disabled', shouldDisable ? 'true' : 'false');
		if (pending === true) {
			$button.attr('aria-busy', 'true');
		} else {
			$button.removeAttr('aria-busy');
		}
	}

	/**
	 * Add fixed and traveler-age add-ons to an object AJAX payload.
	 *
	 * @param {Object} data OVA request object.
	 * @param {jQuery} $form Booking form.
	 * @returns {void}
	 */
	function mergeSelectionsIntoDataObject(data, $form) {
		var ids = collectAddonIdsFromForm($form);
		if (ids.length) {
			data[POST_KEY] = ids;
		}
		var travelerAge = collectTravelerAgeFromForm($form);
		if (!$.isEmptyObject(travelerAge)) {
			data[AGE_POST_KEY] = travelerAge;
		}
	}

	/**
	 * Desde offitravelBuildCalculateTotalPayload (room-mode.js).
	 *
	 * @param {Object} a Payload OVA
	 * @param {jQuery} $form
	 */
	window.offitravelPrdAddonAugmentPayload = function (a, $form) {
		if (!a || a.action !== 'ovabrw_calculate_total') {
			return;
		}
		mergeSelectionsIntoDataObject(a, $form);
	};

	function mergeAddonsIntoDataObject(data, $ctxForm) {
		if (!data || data.action !== 'ovabrw_calculate_total') {
			return;
		}
		var $form = $ctxForm;
		if ((!$form || !$form.length) && data.product_id != null) {
			var pid = String(data.product_id);
			$form = $('form.booking-form').filter(function () {
				return (
					String($(this).find('input[name="product_id"]').first().val() || '') ===
					pid
				);
			});
			if (!$form.length) {
				$form = $('form.booking-form');
			}
		}
		mergeSelectionsIntoDataObject(data, $form);
	}

	function ajaxUrlIsWp(u) {
		return typeof u === 'string' && u.indexOf('admin-ajax.php') !== -1;
	}

	/**
	 * Igual que offitravelBuildCalculateTotalPayload en offitravel-ovabrw-room-mode.php.
	 * OVA solo llama calculateTotal desde +/- huéspedes, qty, select/checkbox propios —
	 * no desde change en ovabrw_adults, así que forzamos el mismo POST cuando faltan addons en el total live.
	 */
	function offitravelBuildCalculateTotalPayloadForForm($form) {
		var pickup = $form.find('input[name="ovabrw_pickup_date"]').val();
		var productId = $form.find('input[name="product_id"]').val();
		if (!pickup || !productId) {
			return null;
		}
		var a = {
			action: 'ovabrw_calculate_total',
			pickup_date: pickup,
			product_id: productId,
		};
		var tf = $form.find('input[name="ovabrw_time_from"]:checked').val();
		if (tf) {
			a.time_from = tf;
		}
		var dof = $form.find('input[name="ovabrw_pickoff_date"]').val();
		if (dof) {
			a.dropoff_date = dof;
		}
		var ad = $form.find('input[name="ovabrw_adults"]').val();
		if (ad) {
			a.adults = ad;
		}
		ad = $form.find('input[name="ovabrw_childrens"]').val();
		if (ad) {
			a.childrens = ad;
		}
		ad = $form.find('input[name="ovabrw_babies"]').val();
		if (ad) {
			a.babies = ad;
		}
		var qEl = $form.find('input[name="ovabrw_quantity"]');
		a.quantity = qEl.length ? qEl.val() : '1';

		ad = $form.find('input[name="ova_type_deposit"]:checked').val();
		if (ad) {
			a.deposit = ad;
		}

		var ckfEl = $form.find('input[name="data_custom_ckf"]');
		var fieldMap = ckfEl.length ? ckfEl.data('ckf') : null;
		if (fieldMap && typeof fieldMap === 'object') {
			var r = {};
			var c = {};
			$.each(fieldMap, function (key, field) {
				var opt,
					t,
					n,
					i,
					arr,
					s;
				if (field.type === 'radio') {
					opt = $form.find('input[name="' + key + '"]:checked');
					if (opt.length) {
						r[key] = opt.val();
						t = opt.closest('.radio-item').find(
							'input[name="' + key + '_qty[' + opt.val() + ']"]'
						);
						n = t.length ? parseInt(t.val(), 10) : 0;
						if (!isNaN(n) && n) {
							c[key] = n;
						}
					}
				} else if (field.type === 'checkbox') {
					arr = [];
					s = {};
					$form
						.find('.ovabrw-checkbox input[type=checkbox]:checked')
						.each(function () {
							var v = $(this).val();
							if (v) {
								arr.push(v);
								t = $(this)
									.closest('.checkbox-item')
									.find('input[name="' + key + '_qty[' + v + ']"]');
								n = t.length ? parseInt(t.val(), 10) : 0;
								if (!isNaN(n) && n) {
									s[v] = n;
								}
							}
						});
					if (arr.length) {
						r[key] = arr;
					}
					if ($.type(s) === 'object' && !$.isEmptyObject(s)) {
						c[key] = s;
					}
				} else if (field.type === 'select') {
					opt = $form.find('select[name="' + key + '"]').val();
					if (opt) {
						r[key] = opt;
						t = $form.find('input[name="' + key + '_qty[' + opt + ']"]');
						n = t.length ? parseInt(t.val(), 10) : 0;
						if (!isNaN(n) && n) {
							c[key] = n;
						}
					}
				}
			});
			if (!$.isEmptyObject(r)) {
				a.custom_ckf = JSON.stringify(r);
			}
			if (!$.isEmptyObject(c)) {
				a.cckf_qty = JSON.stringify(c);
			}
		}

		var rs = {};
		var rg = {};
		$form.find('.ovabrw-resources input[type=checkbox]:checked').each(function () {
			var rk = $(this).data('rs-key');
			if (!rk) {
				return;
			}
			rs[rk] = $(this).val();
			var eg = {};
			var x = parseInt(
				$form.find('input[name="ovabrw_resource_guests[' + rk + '][adult]"]').val(),
				10
			);
			if ($.isNumeric(x)) {
				eg.adult = x;
			}
			x = parseInt(
				$form.find('input[name="ovabrw_resource_guests[' + rk + '][child]"]').val(),
				10
			);
			if ($.isNumeric(x)) {
				eg.child = x;
			}
			x = parseInt(
				$form.find('input[name="ovabrw_resource_guests[' + rk + '][baby]"]').val(),
				10
			);
			if ($.isNumeric(x)) {
				eg.baby = x;
			}
			if (!$.isEmptyObject(eg)) {
				rg[rk] = eg;
			}
		});
		if (!$.isEmptyObject(rs)) {
			a.resources = JSON.stringify(rs);
		}
		if (!$.isEmptyObject(rg)) {
			a.resource_guests = JSON.stringify(rg);
		}

		var sv = [];
		var sg = {};
		$form.find('select[name="ovabrw_service[]"]').each(function () {
			var sid = $(this).val();
			if (!sid) {
				return;
			}
			sv.push(sid);
			var gs = {};
			var y = parseInt(
				$form.find('input[name="ovabrw_service_guests[' + sid + '][adult]"]').val(),
				10
			);
			if ($.isNumeric(y)) {
				gs.adult = y;
			}
			y = parseInt(
				$form.find('input[name="ovabrw_service_guests[' + sid + '][child]"]').val(),
				10
			);
			if ($.isNumeric(y)) {
				gs.child = y;
			}
			y = parseInt(
				$form.find('input[name="ovabrw_service_guests[' + sid + '][baby]"]').val(),
				10
			);
			if ($.isNumeric(y)) {
				gs.baby = y;
			}
			if (!$.isEmptyObject(gs)) {
				sg[sid] = gs;
			}
		});
		if (sv.length) {
			a.services = JSON.stringify(sv);
		}
		if (!$.isEmptyObject(sg)) {
			a.service_guests = JSON.stringify(sg);
		}

		if (typeof window.offitravelPrdAddonAugmentPayload === 'function') {
			window.offitravelPrdAddonAugmentPayload(a, $form);
		}

		return a;
	}

	/**
	 * Misma UX de respuesta que offitravelAjaxCalculateBookingTotal (room-mode).
	 */
	function offitravelAjaxSubmitBookingTotal($form) {
		if (
			typeof ajax_object === 'undefined' ||
			!ajax_object ||
			!ajax_object.ajax_url
		) {
			return;
		}
		var data = offitravelBuildCalculateTotalPayloadForForm($form);
		if (!data) {
			return;
		}
		var $loader = $form.find('.ajax-show-total .ajax-loading-total');
		$form.find('.ajax-show-total .ovabrw-show-amount').css('display', 'flex');
		$loader.show();
		$form.find('.ajax-error').html('').hide();
		$form.find('.ajax-show-total .show-availables-number').html('');
		$form.find('.ajax-show-total .show-amount-insurance').html('');
		$form.find('.ajax-show-total .show-total-number').html('');

		return $.ajax({
			url: ajax_object.ajax_url,
			type: 'POST',
			data: data,
			success: function (resp) {
				var e;
				try {
					e = typeof resp === 'object' ? resp : JSON.parse(resp);
				} catch (err) {
					$loader.hide();
					return;
				}
				if (!e) {
					$loader.hide();
					return;
				}
				if (e.error) {
					$form.find('button.booking-form-submit').addClass('disabled');
					$form.find('.ajax-show-total .ovabrw-show-amount').css(
						'display',
						'none'
					);
					$form.find('.ajax-show-total .ovabrw-ajax-amount-insurance').hide();
					$form.find('.ajax-error').html('').append(e.error).show();
				} else {
					$form.find('button.booking-form-submit').removeClass('disabled');
					if (e.adults_price) {
						$form
							.find('.ovabrw-wrapper-guestspicker .adults-price')
							.html('')
							.append(e.adults_price);
					}
					if (e.childrens_price) {
						$form
							.find('.ovabrw-wrapper-guestspicker .childrens-price')
							.html('')
							.append(e.childrens_price);
					}
					if (e.babies_price) {
						$form
							.find('.ovabrw-wrapper-guestspicker .babies-price')
							.html('')
							.append(e.babies_price);
					}
					$form
						.find('.ajax-show-total .show-amount-insurance')
						.html('')
						.append(e.insurance_amount || '');
					$form.find('.ajax-show-total .ovabrw-ajax-amount-insurance').show();
					$form
						.find('.ajax-show-total .show-total-number')
						.html('')
						.append(e.line_total || '');
					if ('qty_by_guests' in e && e.qty_by_guests) {
						$form.find('.ajax-show-total .ovabrw-ajax-availables').css(
							'display',
							'none'
						);
					} else if (typeof e.quantity_available !== 'undefined') {
						$form
							.find('.ajax-show-total .show-availables-number')
							.html('')
							.append(e.quantity_available);
					}
				}
				$loader.hide();
				$form.find('.ovabrw-date-loading').hide();
			},
		});
	}

	if (typeof $.ajax === 'function' && !$.ajax.__offitravel_prd_addon_wrap) {
		var origAjax = $.ajax;
		$.ajax = function (url, settings) {
			if (typeof url === 'object') {
				var conf = url;
				if (ajaxUrlIsWp(conf.url || '')) {
					mergeAddonsIntoDataObject(conf.data, null);
				}
				return origAjax.call(this, conf);
			}
			settings = settings || {};
			var u =
				typeof url === 'string'
					? url
					: settings.url || '';
			if (ajaxUrlIsWp(u)) {
				mergeAddonsIntoDataObject(settings.data, null);
			}
			return origAjax.call(this, url, settings);
		};
		$.ajax.__offitravel_prd_addon_wrap = true;
	}

	$.ajaxPrefilter(function (options) {
		var u = options.url || '';
		if (!ajaxUrlIsWp(u)) {
			return;
		}

		var d = options.data;

		if (typeof d !== 'string') {
			if (
				d &&
				typeof d === 'object' &&
				!(d instanceof FormData) &&
				d.action === 'ovabrw_calculate_total' &&
				typeof options.processData !== 'undefined' &&
				options.processData === false
			) {
				mergeAddonsIntoDataObject(d, null);
			}
			return;
		}

		if (
			d.indexOf('action=ovabrw_calculate_total') === -1 &&
			d.indexOf('action%3Dovabrw_calculate_total') === -1
		) {
			return;
		}
		var stateApi = window.offitravelProductAddonTravelerState;
		if (
			stateApi &&
			typeof stateApi.serializedPayloadHasAddonSelections === 'function' &&
			stateApi.serializedPayloadHasAddonSelections(d)
		) {
			return;
		}

		var m = /(?:^|[?&])product_id=([^&]*)/.exec(d);
		var pid = m
			? decodeURIComponent(String(m[1]).replace(/\+/g, '%20')).trim()
			: '';
		var $form = $('form.booking-form');
		if (pid) {
			$form = $form.filter(function () {
				return (
					String($(this).find('input[name="product_id"]').first().val() || '') ===
					pid
				);
			});
		}
		var ids = collectAddonIdsFromForm($form.first());
		for (var i = 0; i < ids.length; i++) {
			d += '&offitravel_addons[]=' + encodeURIComponent(ids[i]);
		}
		var travelerAge = collectTravelerAgeFromForm($form.first());
		if (!$.isEmptyObject(travelerAge)) {
			d += '&' + $.param((function () {
				var payload = {};
				payload[AGE_POST_KEY] = travelerAge;
				return payload;
			})());
		}
		options.data = d;
	});

	/**
	 * Read the current occupants per room from the booking form.
	 *
	 * @param {jQuery} $form Booking form.
	 * @returns {number[]} Occupants in room order.
	 */
	function collectRoomOccupancy($form) {
		var people = [];
		$form.find('.offitravel-room-people').each(function () {
			people.push(parseInt($(this).val(), 10) || 0);
		});
		if (!people.length) {
			var total = parseInt($form.find('input[name="ovabrw_adults"]').val(), 10) || 0;
			if (total > 0) {
				people.push(total);
			}
		}
		return people;
	}

	/**
	 * Capture the currently rendered values for one traveler-age service.
	 *
	 * @param {jQuery} $service Service container.
	 * @returns {Object<string,{selected:boolean,age:string}>} State by room/position.
	 */
	function captureTravelerRows($service) {
		var rows = [];
		$service.find('[data-offitravel-traveler-row]').each(function () {
			var $row = $(this);
			var selected = $row.find('[data-offitravel-traveler-selected]').prop('checked');
			rows.push({
				key: String($row.data('room')) + ':' + String($row.data('position')),
				selected: selected === true,
				age: selected ? String($row.find('[data-offitravel-traveler-age]').val() || '') : '',
			});
		});
		return window.offitravelProductAddonTravelerState.indexTravelerState(rows);
	}

	/**
	 * Render synchronized checkbox and age controls for every real traveler.
	 *
	 * @param {jQuery} $form Booking form.
	 * @returns {void}
	 */
	function rebuildTravelerRows($form) {
		var stateApi = window.offitravelProductAddonTravelerState;
		if (!stateApi) {
			return;
		}
		var occupancy = collectRoomOccupancy($form);
		$form.find('[data-offitravel-age-service]').each(function () {
			var $service = $(this);
			var serviceId = String($service.data('offitravel-age-service') || '');
			var label = String($service.data('offitravel-age-label') || 'Seguro de viaje');
			var previous = captureTravelerRows($service);
			var rows = stateApi.reconcileTravelerState(occupancy, previous);
			var $host = $service.find('[data-offitravel-traveler-rows]').empty();

			rows.forEach(function (row) {
				var baseName = AGE_POST_KEY + '[' + serviceId + '][' + row.room + '][' + row.position + ']';
				var selectedId = 'offitravel-age-' + serviceId + '-' + row.room + '-' + row.position;
				var ageId = selectedId + '-years';
				var ageErrorId = ageId + '-error';
				var $checkbox = $('<input/>', {
					type: 'checkbox',
					id: selectedId,
					name: baseName + '[selected]',
					value: '1',
					'data-offitravel-traveler-selected': '',
				}).prop('checked', row.selected);
				var $age = $('<input/>', {
					type: 'number',
					id: ageId,
					name: baseName + '[age]',
					min: '0',
					step: '1',
					inputmode: 'numeric',
					placeholder: 'Ej. 35',
					value: row.age,
					'aria-describedby': ageErrorId,
					'data-offitravel-traveler-age': '',
				}).prop({ disabled: !row.selected, required: row.selected });
				var $row = $('<div/>', {
					'class': 'offitravel-prd-addon-traveler-row',
					'data-offitravel-traveler-row': '',
					'data-room': row.room,
					'data-position': row.position,
				}).data('offitravel-age-validation-revealed', false);
				$row.append(
					$('<span/>', { 'class': 'offitravel-prd-addon-traveler-heading' }).text(
						'Viajero ' + row.traveler + ' — Habitación ' + row.room
					)
				);
				var $controls = $('<div/>', { 'class': 'offitravel-prd-addon-traveler-controls' });
				$controls.append($('<label/>', { for: selectedId }).append($checkbox, $('<span/>').text('Contratar ' + label.toLowerCase())));
				$controls.append(
					$('<label/>', { for: ageId, 'class': 'offitravel-prd-addon-traveler-age' }).append(
						$('<span/>').text('Edad:'),
						$age
					)
				);
				$controls.append(
					$('<span/>', {
						id: ageErrorId,
						'class': 'offitravel-prd-addon-traveler-age-error',
						role: 'alert',
						'aria-live': 'polite',
						'data-offitravel-traveler-age-error': '',
					}).prop('hidden', true)
				);
				$row.append($controls);
				$host.append($row);
			});
		});
		updateTravelerAgeBookingButton($form, false);
	}

	/**
	 * Recalculate the live booking total through the existing OVA mechanism.
	 *
	 * @param {HTMLElement} el Control that initiated recalculation.
	 * @returns {jqXHR|null} AJAX request when directly available.
	 */
	function recalc(el) {
		var $form = $(el).closest('form.booking-form');
		if (!$form.length) {
			return null;
		}
		/* Modo habitaciones: el total se actualiza con offitravelAjaxCalculateBookingTotal, no con change OVA. */
		if (
			typeof window.offitravelAjaxCalculateBookingTotal === 'function' &&
			$form.find('#offitravel_room_count').length
		) {
			return window.offitravelAjaxCalculateBookingTotal($form);
		}
		var $qty = $form.find('input[name="ovabrw_quantity"]');
		if ($qty.length) {
			$qty.trigger('change');
			return null;
		}
		/*
		 * Sin ovabrw_quantity por defecto: OVA sólo recalcula totales desde +/- en huéspedes
		 * (guestsPicker), no desde ovabrw_adults change — el total live ignora addons.
		 * Misma petición que modo habitaciones: offitravelBuildCalculateTotalPayload + augment.
		 */
		var pickupLive = $form.find('input[name="ovabrw_pickup_date"]').val();
		var pidLive = $form.find('input[name="product_id"]').val();
		if (
			typeof ajax_object !== 'undefined' &&
			ajax_object &&
			ajax_object.ajax_url &&
			pickupLive &&
			pidLive
		) {
			return offitravelAjaxSubmitBookingTotal($form);
		}
		var $ovaSelect = $form.find('.ovabrw-select select').first();
		if ($ovaSelect.length) {
			$ovaSelect.trigger('change');
			return null;
		}
		var $kick = $form.find(
			'select[name="ovabrw_service[]"], input[name="ovabrw_pickup_date"], input.checkin-date'
		);
		if ($kick.length) {
			$kick.first().trigger('change');
		}
		return null;
	}

	/**
	 * Debounce direct-age input and let only the latest AJAX request unlock booking.
	 *
	 * Invalid selected ages do not trigger a calculation and keep the real submit
	 * button disabled. A later valid integer replaces that state and recalculates.
	 *
	 * @param {HTMLElement} el Traveler checkbox or age input.
	 * @param {boolean} immediate Whether to skip the typing debounce.
	 * @returns {void}
	 */
	function scheduleTravelerAgeRecalculation(el, immediate) {
		var $form = $(el).closest('form.booking-form');
		if (!$form.length) {
			return;
		}

		var sequence = (parseInt($form.data('offitravel-age-recalc-sequence'), 10) || 0) + 1;
		$form.data('offitravel-age-recalc-sequence', sequence);

		var timer = $form.data('offitravel-age-recalc-timer');
		if (timer) {
			window.clearTimeout(timer);
			$form.removeData('offitravel-age-recalc-timer');
		}

		var previousRequest = $form.data('offitravel-age-recalc-request');
		if (previousRequest && typeof previousRequest.abort === 'function') {
			previousRequest.abort();
			$form.removeData('offitravel-age-recalc-request');
		}

		if (!selectedTravelerAgesAreValid($form)) {
			updateTravelerAgeBookingButton($form, false);
			return;
		}

		updateTravelerAgeBookingButton($form, true);
		timer = window.setTimeout(function () {
			var request = recalc(el);
			$form.removeData('offitravel-age-recalc-timer');
			if (!request || typeof request.always !== 'function') {
				if (sequence === $form.data('offitravel-age-recalc-sequence')) {
					updateTravelerAgeBookingButton($form, false);
				}
				return;
			}

			$form.data('offitravel-age-recalc-request', request);
			request.always(function () {
				if (sequence !== $form.data('offitravel-age-recalc-sequence')) {
					return;
				}
				$form.removeData('offitravel-age-recalc-request');
				updateTravelerAgeBookingButton($form, false);
			});
		}, immediate === true ? 0 : AGE_RECALC_DELAY);
		$form.data('offitravel-age-recalc-timer', timer);
	}

	$(document.body).on(
		'change',
		'.offitravel-prd-addon-fields input[type=checkbox]',
		function () {
			if ($(this).is('[data-offitravel-traveler-selected]')) {
				var $row = $(this).closest('[data-offitravel-traveler-row]');
				var checked = $(this).prop('checked');
				var $age = $row.find('[data-offitravel-traveler-age]');
				$age.prop({ disabled: !checked, required: checked });
				$row.data('offitravel-age-validation-revealed', false);
				if (!checked) {
					$age.val('');
					scheduleTravelerAgeRecalculation(this, true);
				} else {
					$age.val('').trigger('focus');
					updateTravelerAgeBookingButton($(this).closest('form.booking-form'), false);
				}
				return;
			}
			recalc(this);
		}
	);

	$(document.body).on('input', '[data-offitravel-traveler-age]', function () {
		scheduleTravelerAgeRecalculation(this, false);
	});

	$(document.body).on('blur', '[data-offitravel-traveler-age]', function () {
		var $row = $(this).closest('[data-offitravel-traveler-row]');
		$row.data('offitravel-age-validation-revealed', true);
		updateTravelerAgeBookingButton($(this).closest('form.booking-form'), false);
	});

	$(document.body).on('click', 'form.booking-form button.booking-form-submit', function (event) {
		var $form = $(this).closest('form.booking-form');
		if (selectedTravelerAgesAreValid($form)) {
			return;
		}
		event.preventDefault();
		event.stopImmediatePropagation();
		var $invalidAge = revealInvalidTravelerAges($form);
		if ($invalidAge.length) {
			$invalidAge.trigger('focus');
		}
		return false;
	});

	$(document.body).on('change', '#offitravel_room_count, .offitravel-room-people', function () {
		var $form = $(this).closest('form.booking-form');
		window.setTimeout(function () {
			rebuildTravelerRows($form);
		}, 0);
	});

	$(function () {
		$('.ova-booking-form form.booking-form').each(function () {
			rebuildTravelerRows($(this));
		});
	});
})(window.jQuery);
