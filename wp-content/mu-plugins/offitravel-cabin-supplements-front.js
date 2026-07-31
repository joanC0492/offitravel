/**
 * Public cabin option controls for room-mode booking forms.
 *
 * The browser submits cabin index, current occupants and selected category
 * only. Labels, prices and totals remain server-authoritative.
 *
 * @module OffitravelCabinFront
 */
(function (root, factory) {
	'use strict';

	if (typeof module === 'object' && module.exports) {
		module.exports = factory(require('./offitravel-cabin-supplements-state.js'));
		return;
	}
	var api = factory(root.OffitravelCabinState || null);
	root.OffitravelCabinFront = api;
	if (root.jQuery) {
		api.mount(root.jQuery);
	}
}(typeof globalThis !== 'undefined' ? globalThis : this, function (stateApi) {
	'use strict';

	/**
	 * Format a trusted option label using Spanish decimal punctuation.
	 *
	 * This is display-only. PHP still derives every monetary value.
	 *
	 * @param {{label:string,price_per_person:*}} option Trusted public option.
	 * @param {string} currency Currency symbol.
	 * @returns {string} Option label for the select.
	 */
	function optionDisplayLabel(option, currency) {
		var label = option && typeof option.label === 'string' ? option.label : '';
		var price = Number.parseFloat(String(option && option.price_per_person !== undefined ? option.price_per_person : '0'));
		if (!Number.isFinite(price) || price <= 0) {
			return label;
		}
		return label + ' (+' + price.toFixed(2).replace('.', ',') + ' ' + String(currency || '€') + '/persona)';
	}

	/**
	 * Convert rendered rows into the minimal server payload.
	 *
	 * @param {Array<{cabinIndex:*,people:*,category:*}>} rows Cabin rows.
	 * @returns {Object<string,{people:string,category:string}>} Minimal payload.
	 */
	function buildPayloadFromRows(rows) {
		return stateApi && typeof stateApi.buildCabinPayload === 'function'
			? stateApi.buildCabinPayload(rows)
			: {};
	}

	/**
	 * Reconcile visible cabin rows while preserving surviving categories.
	 *
	 * @param {Array<*>} occupants Current room occupants.
	 * @param {Object<string,{people:*,category:*}>} previous Previous form state.
	 * @param {string} initialCategory Default category for a new cabin.
	 * @returns {Object<string,{people:string,category:string}>} Reconciled rows.
	 */
	function reconcileRows(occupants, previous, initialCategory) {
		return stateApi && typeof stateApi.reconcileCabins === 'function'
			? stateApi.reconcileCabins(occupants, previous, initialCategory)
			: {};
	}

	/**
	 * Read trusted public configuration embedded by PHP.
	 *
	 * @param {jQuery} $form Booking form.
	 * @returns {{options:Array<Object>,initialCategory:string}|null} Form configuration.
	 */
	function readConfiguration($form) {
		var $root = $form.find('[data-offitravel-cabin-config]').first();
		if (!$root.length) {
			return null;
		}
		var options;
		try {
			options = JSON.parse($root.find('[data-offitravel-cabin-options]').first().text() || '[]');
		} catch (error) {
			return null;
		}
		if (!Array.isArray(options) || !options.length) {
			return null;
		}
		return {
			options: options,
			initialCategory: String(options[0].id || '')
		};
	}

	/**
	 * Read occupants from the real room-mode controls.
	 *
	 * @param {jQuery} $form Booking form.
	 * @returns {number[]} Occupants in visible cabin order.
	 */
	function readOccupants($form) {
		var occupants = [];
		$form.find('.offitravel-room-row .offitravel-room-people').each(function () {
			occupants.push(Number.parseInt(String($form.constructor(this).val() || ''), 10) || 0);
		});
		return occupants;
	}

	/**
	 * Render one category control inside each current room row.
	 *
	 * @param {jQuery} $form Booking form.
	 * @param {Object} configuration Trusted public configuration.
	 * @returns {Object<string,{people:string,category:string}>} Current form state.
	 */
	function renderRows($form, configuration) {
		var occupants = readOccupants($form);
		var previous = $form.data('offitravel-cabin-state') || {};
		$form.find('[data-offitravel-cabin-control]').each(function () {
			var $control = $form.constructor(this);
			var index = String($control.data('offitravel-cabin-index') || '');
			if (index && previous[index]) {
				previous[index].category = String($control.find('[data-offitravel-cabin-category]').val() || '');
			}
		});
		var cabins = reconcileRows(occupants, previous, configuration.initialCategory);
		$form.data('offitravel-cabin-state', cabins);

		$form.find('.offitravel-room-row').each(function (offset) {
			var index = String(offset + 1);
			var row = cabins[index];
			var $room = $form.constructor(this);
			$room.find('[data-offitravel-cabin-control]').remove();
			if (!row) {
				return;
			}
			var selectId = 'offitravel-cabin-' + String($form.data('offitravel-cabin-instance') || 'form') + '-' + index;
			var $select = $form.constructor('<select/>', {
				id: selectId,
				name: 'offitravel_cabins[' + index + '][category]',
				required: true,
				'data-offitravel-cabin-category': ''
			});
			configuration.options.forEach(function (option) {
				$form.constructor('<option/>', {
					value: String(option.id || ''),
					text: optionDisplayLabel(option, '€'),
					selected: String(option.id || '') === row.category
				}).appendTo($select);
			});
			var $control = $form.constructor('<div/>', {
				'class': 'offitravel-cabin-control',
				'data-offitravel-cabin-control': '',
				'data-offitravel-cabin-index': index
			});
			$form.constructor('<label/>', { for: selectId, text: 'Categoría de cabina' }).appendTo($control);
			$select.appendTo($control);
			$form.constructor('<input/>', {
				type: 'hidden',
				name: 'offitravel_cabins[' + index + '][people]',
				value: row.people,
				'data-offitravel-cabin-people': ''
			}).appendTo($control);
			$form.constructor('<span/>', {
				'class': 'offitravel-cabin-error',
				'data-offitravel-cabin-error': '',
				role: 'alert',
				'aria-live': 'polite',
				hidden: true
			}).appendTo($control);
			$room.append($control);
		});
		return cabins;
	}

	/**
	 * Mount form-scoped room controls and recalculation events.
	 *
	 * @param {jQueryStatic} $ jQuery instance.
	 * @returns {void}
	 */
	function mount($) {
		var instance = 0;

		function initialize($form) {
			var configuration = readConfiguration($form);
			if (!configuration) {
				return;
			}
			if (!$form.data('offitravel-cabin-instance')) {
				instance += 1;
				$form.data('offitravel-cabin-instance', instance);
			}
			$form.data('offitravel-cabin-configuration', configuration);
			renderRows($form, configuration);
		}

		$(document.body).on('change', '[data-offitravel-cabin-category]', function () {
			var $select = $(this);
			var $form = $select.closest('form.booking-form');
			var configuration = $form.data('offitravel-cabin-configuration');
			if (!configuration) {
				return;
			}
			var index = String($select.closest('[data-offitravel-cabin-control]').data('offitravel-cabin-index') || '');
			var cabins = $form.data('offitravel-cabin-state') || {};
			if (cabins[index]) {
				cabins[index].category = String($select.val() || '');
				$form.data('offitravel-cabin-state', cabins);
			}
			if (typeof window.offitravelPrdAddonRecalculate === 'function') {
				window.offitravelPrdAddonRecalculate($form);
			}
		});

		$(document.body).on('change', '#offitravel_room_count, .offitravel-room-people', function () {
			var $form = $(this).closest('form.booking-form');
			window.setTimeout(function () {
				var configuration = $form.data('offitravel-cabin-configuration');
				if (configuration) {
					renderRows($form, configuration);
				}
			}, 0);
		});

		$(document.body).on('click', 'form.booking-form button.booking-form-submit', function (event) {
			var $form = $(this).closest('form.booking-form');
			var configuration = $form.data('offitravel-cabin-configuration');
			if (!configuration) {
				return;
			}
			var cabins = renderRows($form, configuration);
			if (stateApi && stateApi.cabinsAreComplete(cabins)) {
				return;
			}
			event.preventDefault();
			event.stopImmediatePropagation();
			var $error = $form.find('[data-offitravel-cabin-error]').first();
			$error.text('Selecciona una categoría válida para cada cabina.').prop('hidden', false);
			$form.find('[data-offitravel-cabin-category]').first().trigger('focus');
			return false;
		});

		$(function () {
			$('form.booking-form').each(function () {
				initialize($(this));
			});
		});
	}

	return {
		buildPayloadFromRows: buildPayloadFromRows,
		mount: mount,
		optionDisplayLabel: optionDisplayLabel,
		reconcileRows: reconcileRows
	};
}));
