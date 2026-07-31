/**
 * Pure state helpers for fixed add-ons with a manual room quantity.
 *
 * @module offitravelProductAddonFixedState
 */
(function (root, factory) {
	'use strict';
	var api = factory();
	if (typeof module === 'object' && module.exports) {
		module.exports = api;
	}
	if (root) {
		root.offitravelProductAddonFixedState = api;
	}
})(typeof window !== 'undefined' ? window : globalThis, function () {
	'use strict';

	/**
	 * Validate a strictly positive integer quantity.
	 *
	 * @param {number|string|null|undefined} value Quantity value.
	 * @returns {boolean} Whether the value is an integer of at least one.
	 */
	function isPositiveInteger(value) {
		return value != null && /^[1-9]\d*$/.test(String(value).trim());
	}

	/**
	 * Build quantities for selected manual fixed add-ons.
	 *
	 * @param {Array<{serviceId:number|string,selected:boolean,quantity:string}>} rows Manual rows.
	 * @returns {Object<string,string>} Quantity payload keyed by service ID.
	 */
	function buildQuantityPayload(rows) {
		var payload = {};
		if (!Array.isArray(rows)) {
			return payload;
		}
		rows.forEach(function (row) {
			if (row && row.selected === true && row.serviceId) {
				payload[String(row.serviceId)] = row.quantity == null ? '' : String(row.quantity);
			}
		});
		return payload;
	}

	/**
	 * Return progressive validation feedback for a manual quantity.
	 *
	 * @param {boolean} selected Whether the related add-on is selected.
	 * @param {number|string|null|undefined} value Quantity value.
	 * @param {boolean} reveal Whether validation feedback should be visible.
	 * @returns {string} Error text or an empty string.
	 */
	function validationMessage(selected, value, reveal) {
		return selected === true && reveal === true && !isPositiveInteger(value)
			? 'Introduce un número entero de habitaciones igual o superior a 1.'
			: '';
	}

	return {
		buildQuantityPayload: buildQuantityPayload,
		isPositiveInteger: isPositiveInteger,
		validationMessage: validationMessage,
	};
});
