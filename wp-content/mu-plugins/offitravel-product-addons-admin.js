/**
 * Administrative interactions for reusable Offitravel product add-ons.
 *
 * This script is loaded only on add-on edit screens. It never participates in
 * the public booking form or its AJAX requests.
 *
 * @package Offitravel
 */
(function ($) {
	'use strict';

	var MODEL_TRAVELER_AGE = 'traveler_age';
	var RULES_FIELD = '_offitravel_addon_age_rules';

	/**
	 * Enable or disable every form control within a conditional section.
	 *
	 * @param {jQuery} $section Section containing controls.
	 * @param {boolean} enabled Whether controls should be submitted.
	 * @return {void}
	 */
	function setSectionEnabled($section, enabled) {
		$section.find('input, select, button').prop('disabled', !enabled);
		$section.find('input, select').each(function () {
			if (typeof this.setCustomValidity === 'function') {
				this.setCustomValidity('');
			}
		});
	}

	/**
	 * Rebuild sequential field names after adding or removing an age row.
	 *
	 * @param {jQuery} $root Add-on metabox root.
	 * @return {void}
	 */
	function reindexRules($root) {
		$root.find('[data-offitravel-addon-age-rule]').each(function (index) {
			$(this)
				.find('input')
				.each(function () {
					var field = $(this).attr('data-field');
					if (!field) {
						var match = String($(this).attr('name') || '').match(/\[(min_age|max_age|price)\]$/);
						field = match ? match[1] : '';
						if (field) {
							$(this).attr('data-field', field);
						}
					}
					if (field) {
						$(this).attr('name', RULES_FIELD + '[' + index + '][' + field + ']');
					}
				});
		});
	}

	/**
	 * Show only the fields compatible with the selected price model.
	 *
	 * @param {jQuery} $root Add-on metabox root.
	 * @return {void}
	 */
	function syncPriceModel($root) {
		var ageEnabled = $root.find('[name="_offitravel_addon_price_model"]').val() === MODEL_TRAVELER_AGE;
		var $fixed = $root.find('[data-offitravel-addon-fixed-fields]');
		var $age = $root.find('[data-offitravel-addon-age-fields]');

		$fixed.toggle(!ageEnabled).attr('aria-hidden', ageEnabled ? 'true' : 'false');
		$age.toggle(ageEnabled).attr('aria-hidden', ageEnabled ? 'false' : 'true');
		setSectionEnabled($fixed, !ageEnabled);
		setSectionEnabled($age, ageEnabled);
		$fixed.find('[name="_offitravel_addon_price"]').prop('required', !ageEnabled);
	}

	/**
	 * Add a blank age rule using the server-rendered template.
	 *
	 * @param {jQuery} $root Add-on metabox root.
	 * @return {void}
	 */
	function addRule($root) {
		var html = $root.find('[data-offitravel-addon-age-rule-template]').html();
		if (!html) {
			return;
		}
		$root.find('[data-offitravel-addon-age-rules] tbody').append(html);
		reindexRules($root);
		$root.find('[data-offitravel-addon-age-rule]').last().find('input').first().trigger('focus');
	}

	/**
	 * Remove a rule, retaining one blank row so an active age model is editable.
	 *
	 * @param {jQuery} $root Add-on metabox root.
	 * @param {jQuery} $row Rule row selected for removal.
	 * @return {void}
	 */
	function removeRule($root, $row) {
		var $rows = $root.find('[data-offitravel-addon-age-rule]');
		if ($rows.length <= 1) {
			$row.find('input').val('').each(function () {
				this.setCustomValidity('');
			});
			return;
		}
		$row.remove();
		reindexRules($root);
	}

	/**
	 * Parse a required or optional non-negative integer age.
	 *
	 * @param {HTMLInputElement} input Age input element.
	 * @param {boolean} optional Whether an empty value is permitted.
	 * @return {?number} Parsed age, null for an allowed empty value, or NaN.
	 */
	function parseAge(input, optional) {
		var value = String(input.value || '').trim();
		if (optional && value === '') {
			return null;
		}
		return /^\d+$/.test(value) ? Number(value) : NaN;
	}

	/**
	 * Check an administrative price without imposing a positive minimum.
	 *
	 * @param {HTMLInputElement} input Price input element.
	 * @return {boolean} Whether the value is a non-negative decimal.
	 */
	function isValidPrice(input) {
		return /^\d+(?:[.,]\d+)?$/.test(String(input.value || '').trim());
	}

	/**
	 * Validate age rows before WordPress submits the add-on editor.
	 *
	 * Server-side validation remains authoritative. This client validation keeps
	 * administrators from submitting negative, decimal, inverted, overlapping,
	 * or multiple open-ended ranges.
	 *
	 * @param {jQuery} $root Add-on metabox root.
	 * @return {boolean} True when the active configuration is valid.
	 */
	function validateAgeRules($root) {
		if ($root.find('[name="_offitravel_addon_price_model"]').val() !== MODEL_TRAVELER_AGE) {
			return true;
		}

		var rules = [];
		var valid = true;
		$root.find('[data-offitravel-addon-age-rule]').each(function () {
			var minInput = $(this).find('[data-field="min_age"]')[0];
			var maxInput = $(this).find('[data-field="max_age"]')[0];
			var priceInput = $(this).find('[data-field="price"]')[0];
			var min = parseAge(minInput, false);
			var max = parseAge(maxInput, true);

			minInput.setCustomValidity('');
			maxInput.setCustomValidity('');
			priceInput.setCustomValidity('');
			if (!Number.isInteger(min) || min < 0) {
				minInput.setCustomValidity('Introduce una edad mínima entera igual o superior a cero.');
				valid = false;
			}
			if (max !== null && (!Number.isInteger(max) || max < 0)) {
				maxInput.setCustomValidity('Introduce una edad máxima entera igual o superior a cero, o déjala vacía.');
				valid = false;
			} else if (Number.isInteger(min) && max !== null && max < min) {
				maxInput.setCustomValidity('La edad máxima no puede ser menor que la edad mínima.');
				valid = false;
			}
			if (!isValidPrice(priceInput)) {
				priceInput.setCustomValidity('Introduce un precio válido igual o superior a cero.');
				valid = false;
			}

			rules.push({ min: min, max: max, minInput: minInput });
		});

		if (!valid) {
			return false;
		}

		rules.sort(function (left, right) {
			return left.min - right.min;
		});
		for (var index = 1; index < rules.length; index++) {
			var previous = rules[index - 1];
			var current = rules[index];
			if (previous.max === null || current.min <= previous.max) {
				current.minInput.setCustomValidity('Este tramo se solapa con otro tramo de edad.');
				return false;
			}
		}
		return true;
	}

	/**
	 * Initialize one add-on editor instance.
	 *
	 * @param {HTMLElement} root Add-on metabox root element.
	 * @return {void}
	 */
	function init(root) {
		var $root = $(root);
		var $form = $root.closest('form');
		reindexRules($root);
		syncPriceModel($root);

		$root.on('change', '[name="_offitravel_addon_price_model"]', function () {
			syncPriceModel($root);
		});
		$root.on('click', '[data-offitravel-addon-add-rule]', function () {
			addRule($root);
		});
		$root.on('click', '[data-offitravel-addon-remove-rule]', function () {
			removeRule($root, $(this).closest('[data-offitravel-addon-age-rule]'));
		});
		$form.on('submit.offitravelAddonAdmin', function (event) {
			if (!validateAgeRules($root)) {
				event.preventDefault();
				this.reportValidity();
			}
		});
	}

	$(function () {
		$('[data-offitravel-addon-admin]').each(function () {
			init(this);
		});
	});
})(window.jQuery);
