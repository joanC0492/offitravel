/**
 * Administrative row management for reusable cabin supplement options.
 *
 * The module only marks the metabox as interacted after a deliberate field,
 * add, or remove action. It never sends requests and has no public-form role.
 *
 * @module OffitravelCabinAdmin
 */
(function (root, factory) {
	'use strict';

	if (typeof module === 'object' && module.exports) {
		module.exports = factory();
		return;
	}
	root.OffitravelCabinAdmin = factory();
}(typeof globalThis !== 'undefined' ? globalThis : this, function () {
	'use strict';

	/**
	 * Return the next numeric row index.
	 *
	 * @param {Array<string|number>} indexes Existing row indexes.
	 * @returns {number} One more than the greatest valid index, or zero.
	 */
	function nextRowIndex(indexes) {
		var numeric = indexes
			.map(function (value) { return Number.parseInt(String(value), 10); })
			.filter(function (value) { return Number.isInteger(value) && value >= 0; });

		return numeric.length ? Math.max.apply(null, numeric) + 1 : 0;
	}

	/**
	 * Mark the metabox as deliberately edited.
	 *
	 * @param {{value: string}|null} marker Hidden interaction input.
	 * @returns {void}
	 */
	function markInteracted(marker) {
		if (marker) {
			marker.value = '1';
		}
	}

	/**
	 * Read row indexes already present in a metabox.
	 *
	 * @param {Element} container Metabox root element.
	 * @returns {string[]} Extracted numeric indexes.
	 */
	function currentIndexes(container) {
		return Array.prototype.map.call(
			container.querySelectorAll('[data-offitravel-cabin-option-id]'),
			function (input) {
				var match = String(input.name || '').match(/offitravel_cabin_options\[(\d+)\]/);
				return match ? match[1] : '';
			}
		).filter(Boolean);
	}

	/**
	 * Initialize one product-editor cabin options metabox.
	 *
	 * @param {Element} container Metabox root element.
	 * @returns {void}
	 */
	function initialize(container) {
		var marker = container.querySelector('[data-offitravel-cabin-interacted]');
		var rows = container.querySelector('[data-offitravel-cabin-option-rows]');
		var template = container.querySelector('[data-offitravel-cabin-option-template]');

		container.addEventListener('input', function (event) {
			if (event.target.matches('[data-offitravel-cabin-option-id], [data-offitravel-cabin-option-label], [data-offitravel-cabin-option-price]')) {
				markInteracted(marker);
			}
		});

		container.addEventListener('click', function (event) {
			var addButton = event.target.closest('[data-offitravel-cabin-add-option]');
			var removeButton = event.target.closest('[data-offitravel-cabin-remove-option]');

			if (addButton && rows && template) {
				event.preventDefault();
				markInteracted(marker);
				rows.insertAdjacentHTML(
					'beforeend',
					template.innerHTML.replace(/__INDEX__/g, String(nextRowIndex(currentIndexes(container))))
				);
				return;
			}

			if (removeButton) {
				event.preventDefault();
				markInteracted(marker);
				var row = removeButton.closest('[data-offitravel-cabin-option-row]');
				if (row) {
					row.remove();
				}
			}
		});
	}

	if (typeof document !== 'undefined') {
		document.addEventListener('DOMContentLoaded', function () {
			Array.prototype.forEach.call(
				document.querySelectorAll('[data-offitravel-cabin-admin]'),
				initialize
			);
		});
	}

	return {
		initialize: initialize,
		markInteracted: markInteracted,
		nextRowIndex: nextRowIndex
	};
}));
