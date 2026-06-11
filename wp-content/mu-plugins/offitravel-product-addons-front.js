/**
 * OVA: mismo patrón que offitravel_room_* — el payload custom de habitaciones arma el objeto
 * sin leer checkboxes de addons. Extendemos con offitravel_addons[] y jQuery.ajax antes de serializar.
 */
(function ($) {
	'use strict';

	var POST_KEY = 'offitravel_addons';

	function collectAddonIdsFromForm($form) {
		var out = [];
		if (!$form || !$form.length) {
			return out;
		}
		$form
			.find('.offitravel-prd-addon-fields input[type="checkbox"]:checked')
			.each(function () {
				var v = $(this).val();
				if (v) {
					out.push(String(v));
				}
			});
		return out;
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
		var ids = collectAddonIdsFromForm($form);
		if (ids.length) {
			a[POST_KEY] = ids;
		}
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
		var ids = collectAddonIdsFromForm($form);
		if (ids.length) {
			data[POST_KEY] = ids;
		}
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

		$.ajax({
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
		if (d.indexOf('offitravel_addons') !== -1) {
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
		if (!ids.length) {
			return;
		}
		for (var i = 0; i < ids.length; i++) {
			d += '&offitravel_addons[]=' + encodeURIComponent(ids[i]);
		}
		options.data = d;
	});

	function recalc(el) {
		var $form = $(el).closest('form.booking-form');
		if (!$form.length) {
			return;
		}
		/* Modo habitaciones: el total se actualiza con offitravelAjaxCalculateBookingTotal, no con change OVA. */
		if (
			typeof window.offitravelAjaxCalculateBookingTotal === 'function' &&
			$form.find('#offitravel_room_count').length
		) {
			window.offitravelAjaxCalculateBookingTotal($form);
			return;
		}
		var $qty = $form.find('input[name="ovabrw_quantity"]');
		if ($qty.length) {
			$qty.trigger('change');
			return;
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
			offitravelAjaxSubmitBookingTotal($form);
			return;
		}
		var $ovaSelect = $form.find('.ovabrw-select select').first();
		if ($ovaSelect.length) {
			$ovaSelect.trigger('change');
			return;
		}
		var $kick = $form.find(
			'select[name="ovabrw_service[]"], input[name="ovabrw_pickup_date"], input.checkin-date'
		);
		if ($kick.length) {
			$kick.first().trigger('change');
		}
	}

	$(document.body).on(
		'change',
		'.offitravel-prd-addon-fields input[type=checkbox]',
		function () {
			recalc(this);
		}
	);
})(window.jQuery);
