/**
 * Pure traveler-row reconciliation shared by the public form and Node tests.
 *
 * @module offitravelProductAddonTravelerState
 */
(function (root, factory) {
	'use strict';

	var api = factory();
	if (typeof module === 'object' && module.exports) {
		module.exports = api;
	}
	if (root) {
		root.offitravelProductAddonTravelerState = api;
	}
})(typeof window !== 'undefined' ? window : globalThis, function () {
	'use strict';

	/**
	 * Normalize a room occupancy list to positive integers.
	 *
	 * @param {Array<number|string>} people Occupants per room.
	 * @returns {number[]} Valid occupants in room order.
	 */
	function normalizeOccupancy(people) {
		if (!Array.isArray(people)) {
			return [];
		}
		return people.map(function (value) {
			var parsed = parseInt(value, 10);
			return Number.isFinite(parsed) && parsed > 0 ? parsed : 0;
		});
	}

	/**
	 * Index rendered traveler rows by their stable room/position key.
	 *
	 * Unselected rows intentionally discard age so removing a selection cannot
	 * revive stale personal data during a later reconstruction.
	 *
	 * @param {Array<Object>} rows Traveler row state.
	 * @returns {Object<string,{selected:boolean,age:string}>} Indexed state.
	 */
	function indexTravelerState(rows) {
		var indexed = {};
		if (!Array.isArray(rows)) {
			return indexed;
		}
		rows.forEach(function (row) {
			if (!row || !row.key) {
				return;
			}
			var selected = row.selected === true;
			indexed[String(row.key)] = {
				selected: selected,
				age: selected && row.age != null ? String(row.age) : '',
			};
		});
		return indexed;
	}

	/**
	 * Reconcile traveler rows with current room occupancy.
	 *
	 * Existing room/position keys retain their selection and age. Removed keys
	 * disappear, and newly added travelers always start empty.
	 *
	 * @param {Array<number|string>} people Occupants per room.
	 * @param {Object<string,{selected:boolean,age:string}>} previous Previous state.
	 * @returns {Array<{key:string,room:number,position:number,traveler:number,selected:boolean,age:string}>}
	 */
	function reconcileTravelerState(people, previous) {
		var occupancy = normalizeOccupancy(people);
		var prior = previous && typeof previous === 'object' ? previous : {};
		var rows = [];
		var traveler = 0;

		occupancy.forEach(function (occupants, roomOffset) {
			for (var position = 1; position <= occupants; position += 1) {
				traveler += 1;
				var key = String(roomOffset + 1) + ':' + String(position);
				var old = prior[key] || {};
				var selected = old.selected === true;
				rows.push({
					key: key,
					room: roomOffset + 1,
					position: position,
					traveler: traveler,
					selected: selected,
					age: selected && old.age != null ? String(old.age) : '',
				});
			}
		});

		return rows;
	}

	/**
	 * Build the nested request payload for selected traveler-age rows.
	 *
	 * Fixed add-ons are intentionally outside this function and remain in the
	 * independent `offitravel_addons[]` request field.
	 *
	 * @param {Array<{serviceId:number|string,room:number|string,position:number|string,selected:boolean,age:string}>} rows Traveler selections.
	 * @returns {Object<string,Object<string,Object<string,{selected:string,age:string}>>>} Traveler-age payload.
	 */
	function buildTravelerAgePayload(rows) {
		var payload = {};
		if (!Array.isArray(rows)) {
			return payload;
		}
		rows.forEach(function (row) {
			if (!row || row.selected !== true) {
				return;
			}
			var serviceId = String(row.serviceId || '');
			var room = String(row.room || '');
			var position = String(row.position || '');
			if (!serviceId || !room || !position) {
				return;
			}
			payload[serviceId] = payload[serviceId] || {};
			payload[serviceId][room] = payload[serviceId][room] || {};
			payload[serviceId][room][position] = {
				selected: '1',
				age: row.age == null ? '' : String(row.age),
			};
		});
		return payload;
	}

	/**
	 * Detect add-on selections already serialized into an AJAX payload.
	 *
	 * Both raw square brackets and their percent-encoded form retain the base
	 * field name, so checking the two independent request keys is sufficient.
	 *
	 * @param {string} payload Serialized request data.
	 * @returns {boolean} Whether fixed or traveler-age selections are present.
	 */
	function serializedPayloadHasAddonSelections(payload) {
		var value = typeof payload === 'string' ? payload : '';
		return (
			value.indexOf('offitravel_addons') !== -1 ||
			value.indexOf('offitravel_age_addons') !== -1
		);
	}

	/**
	 * Validate one direct age value using the same integer rule as PHP.
	 *
	 * Zero is a valid age. Empty, signed and decimal values are rejected.
	 *
	 * @param {number|string|null|undefined} value Direct traveler age.
	 * @returns {boolean} Whether the value is a non-negative integer.
	 */
	function isTravelerAgeValueValid(value) {
		if (value == null) {
			return false;
		}
		return /^\d+$/.test(String(value).trim());
	}

	/**
	 * Validate ages only for traveler rows whose insurance is selected.
	 *
	 * @param {Array<{selected:boolean,age:number|string}>} rows Traveler rows.
	 * @returns {boolean} Whether every selected traveler has a valid age.
	 */
	function selectedTravelerAgesAreValid(rows) {
		if (!Array.isArray(rows)) {
			return true;
		}
		return rows.every(function (row) {
			return !row || row.selected !== true || isTravelerAgeValueValid(row.age);
		});
	}

	/**
	 * Return the public explanation for one selected traveler with invalid age.
	 *
	 * @param {boolean} selected Whether this traveler selected insurance.
	 * @param {number|string|null|undefined} age Direct traveler age.
	 * @param {boolean} reveal Whether interaction has requested validation feedback.
	 * @returns {string} Validation text, or an empty string when valid/not selected.
	 */
	function travelerAgeValidationMessage(selected, age, reveal) {
		return reveal === true && selected === true && !isTravelerAgeValueValid(age)
			? 'Introduce una edad entera igual o superior a 0 para contratar el seguro.'
			: '';
	}

	return {
		buildTravelerAgePayload: buildTravelerAgePayload,
		indexTravelerState: indexTravelerState,
		isTravelerAgeValueValid: isTravelerAgeValueValid,
		normalizeOccupancy: normalizeOccupancy,
		reconcileTravelerState: reconcileTravelerState,
		selectedTravelerAgesAreValid: selectedTravelerAgesAreValid,
		serializedPayloadHasAddonSelections: serializedPayloadHasAddonSelections,
		travelerAgeValidationMessage: travelerAgeValidationMessage,
	};
});
