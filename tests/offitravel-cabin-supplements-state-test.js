'use strict';

const assert = require('assert');
const fs = require('fs');
const path = require('path');

const modulePath = '../wp-content/mu-plugins/offitravel-cabin-supplements-state.js';
const state = require(modulePath);
const source = fs.readFileSync(path.join(__dirname, modulePath), 'utf8');

const tests = {
	'occupancy normalization accepts positive configured values without a hardcoded maximum'() {
		assert.deepStrictEqual(state.normalizeOccupancy(['1', 3, '7']), [1, 3, 7]);
		assert.strictEqual(state.normalizeOccupancy(['0']), null);
		assert.strictEqual(state.normalizeOccupancy(['1.5']), null);
		assert.strictEqual(state.normalizeOccupancy([]), null);
	},
	'reconciliation preserves surviving cabin categories and updates occupants'() {
		const previous = {
			1: { people: '2', category: 'synthetic-lower' },
			2: { people: '1', category: 'synthetic-upper' },
		};
		assert.deepStrictEqual(state.reconcileCabins([2, 3, 1], previous), {
			1: { people: '2', category: 'synthetic-lower' },
			2: { people: '3', category: 'synthetic-upper' },
			3: { people: '1', category: '' },
		});
		assert.deepStrictEqual(state.reconcileCabins([2], previous), {
			1: { people: '2', category: 'synthetic-lower' },
		});
	},
	'new cabins receive the configured initial category without changing survivors'() {
		assert.deepStrictEqual(
			state.reconcileCabins(
				[2, 5],
				{ 1: { people: '2', category: 'puente-superior' } },
				'sin-suplemento'
			),
			{
				1: { people: '2', category: 'puente-superior' },
				2: { people: '5', category: 'sin-suplemento' },
			}
		);
	},
	'payload contains only cabin index occupants and category'() {
		const payload = state.buildCabinPayload([
			{ cabinIndex: '1', people: '2', category: 'synthetic-lower', price: '999', subtotal: '999' },
			{ cabinIndex: '2', people: '1', category: 'synthetic-upper', price_per_person: '999' },
		]);
		assert.deepStrictEqual(payload, {
			1: { people: '2', category: 'synthetic-lower' },
			2: { people: '1', category: 'synthetic-upper' },
		});
		assert.strictEqual(JSON.stringify(payload).includes('999'), false);
	},
	'completeness requires one category for every valid cabin'() {
		assert.strictEqual(state.cabinsAreComplete({ 1: { people: '2', category: 'synthetic-lower' } }), true);
		assert.strictEqual(state.cabinsAreComplete({ 1: { people: '2', category: '' } }), false);
		assert.strictEqual(state.cabinsAreComplete({ 1: { people: '0', category: 'synthetic-lower' } }), false);
		assert.strictEqual(state.cabinsAreComplete({}), false);
	},
	'latest-request coordinator aborts the previous request and rejects stale tokens'() {
		const coordinator = state.createRequestCoordinator();
		let aborts = 0;
		const first = coordinator.begin({ abort() { aborts += 1; } });
		const second = coordinator.begin({ abort() { aborts += 1; } });
		assert.strictEqual(aborts, 1);
		assert.strictEqual(coordinator.isCurrent(first), false);
		assert.strictEqual(coordinator.isCurrent(second), true);
		coordinator.complete(second);
		assert.strictEqual(coordinator.isCurrent(second), true);
	},
	'pure state module has no DOM AJAX or cart integration'() {
		assert.strictEqual(/document\.|querySelector|ajaxPrefilter|\.ajax\s*\(|fetch\s*\(/.test(source), false);
		assert.strictEqual(/woocommerce|add_to_cart|ovabrw_get_price/.test(source), false);
	},
};

let failures = 0;
Object.entries(tests).forEach(([name, test]) => {
	try {
		test();
		console.log(`[PASS] ${name}`);
	} catch (error) {
		failures += 1;
		console.error(`[FAIL] ${name}`);
		console.error(error.stack || error.message);
	}
});
console.log(`${Object.keys(tests).length} test(s), ${failures} failure(s).`);
process.exit(failures > 0 ? 1 : 0);
