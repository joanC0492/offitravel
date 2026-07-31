/**
 * Pure state helpers reserved for future cabin-selection interfaces.
 *
 * Checkpoint 4 does not enqueue this module. It defines the data contract used
 * by later checkpoints without binding events or sending requests.
 *
 * @module OffitravelCabinState
 */
(function (root, factory) {
	'use strict';

	if (typeof module === 'object' && module.exports) {
		module.exports = factory();
		return;
	}
	root.OffitravelCabinState = factory();
}(typeof globalThis !== 'undefined' ? globalThis : this, function () {
	'use strict';

	/**
	 * Determine whether a value is a positive integer.
	 *
	 * @param {*} value Candidate value.
	 * @returns {boolean} Whether the value is a positive integer.
	 */
	function isPositiveInteger(value) {
		return /^[1-9]\d*$/.test(String(value));
	}

	/**
	 * Normalize configured occupants without imposing a commercial maximum.
	 *
	 * @param {Array<*>} values Occupants in cabin order.
	 * @returns {number[]|null} Normalized occupants or null when invalid.
	 */
	function normalizeOccupancy(values) {
		if (!Array.isArray(values) || values.length === 0 || !values.every(isPositiveInteger)) {
			return null;
		}
		return values.map(function (value) { return Number.parseInt(String(value), 10); });
	}

	/**
	 * Reconcile selections with a new ordered occupancy list.
	 *
	 * Categories survive only for cabin positions that still exist. Occupants
	 * always come from the current room state, while newly added cabins start
	 * without a category.
	 *
	 * @param {Array<*>} occupants Current occupants in cabin order.
	 * @param {Object<string,{people: *, category: *}>} previous Previous state.
	 * @returns {Object<string,{people: string, category: string}>} Reconciled state.
	 */
	function reconcileCabins(occupants, previous) {
		var normalized = normalizeOccupancy(occupants);
		var oldState = previous && typeof previous === 'object' ? previous : {};
		var result = {};

		if (!normalized) {
			return result;
		}
		normalized.forEach(function (people, offset) {
			var index = String(offset + 1);
			var oldRow = oldState[index] && typeof oldState[index] === 'object' ? oldState[index] : {};
			result[index] = {
				people: String(people),
				category: typeof oldRow.category === 'string' ? oldRow.category : ''
			};
		});
		return result;
	}

	/**
	 * Build the future request payload without client prices or subtotals.
	 *
	 * @param {Array<{cabinIndex: *, people: *, category: *}>} rows UI state rows.
	 * @returns {Object<string,{people: string, category: string}>} Minimal payload.
	 */
	function buildCabinPayload(rows) {
		var result = {};
		if (!Array.isArray(rows)) {
			return result;
		}
		rows.forEach(function (row) {
			if (!row || !isPositiveInteger(row.cabinIndex)) {
				return;
			}
			result[String(Number.parseInt(String(row.cabinIndex), 10))] = {
				people: String(row.people === undefined ? '' : row.people),
				category: typeof row.category === 'string' ? row.category : ''
			};
		});
		return result;
	}

	/**
	 * Determine whether every cabin has valid occupants and a category.
	 *
	 * @param {Object<string,{people: *, category: *}>} cabins Cabin state.
	 * @returns {boolean} Whether the state is complete enough to submit.
	 */
	function cabinsAreComplete(cabins) {
		if (!cabins || typeof cabins !== 'object' || Object.keys(cabins).length === 0) {
			return false;
		}
		return Object.keys(cabins).every(function (index) {
			var row = cabins[index];
			return row && isPositiveInteger(index) && isPositiveInteger(row.people) &&
				typeof row.category === 'string' && row.category.trim() !== '';
		});
	}

	return {
		buildCabinPayload: buildCabinPayload,
		cabinsAreComplete: cabinsAreComplete,
		isPositiveInteger: isPositiveInteger,
		normalizeOccupancy: normalizeOccupancy,
		reconcileCabins: reconcileCabins
	};
}));
