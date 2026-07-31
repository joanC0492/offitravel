'use strict';

const assert = require('assert');
const fs = require('fs');
const path = require('path');

const frontPath = path.join(__dirname, '../wp-content/mu-plugins/offitravel-cabin-supplements-front.js');
const statePath = path.join(__dirname, '../wp-content/mu-plugins/offitravel-cabin-supplements-state.js');
const addOnPath = path.join(__dirname, '../wp-content/mu-plugins/offitravel-product-addons-front.js');
const front = require(frontPath);
const state = require(statePath);
const addOnSource = fs.readFileSync(addOnPath, 'utf8');

const tests = {
	'Danube labels preserve the exact decimal tariff without browser calculations'() {
		assert.strictEqual(front.optionDisplayLabel({ label: 'Sin suplemento', price_per_person: '0.00' }, '€'), 'Sin suplemento');
		assert.strictEqual(front.optionDisplayLabel({ label: 'Puente intermedio', price_per_person: '111.50' }, '€'), 'Puente intermedio (+111,50 €/persona)');
		assert.strictEqual(front.optionDisplayLabel({ label: 'Puente superior', price_per_person: '200.00' }, '€'), 'Puente superior (+200,00 €/persona)');
	},
	'Danube payload contains only people and category for independent cabins'() {
		const payload = front.buildPayloadFromRows([
			{ cabinIndex: 1, people: 3, category: 'puente-intermedio', price_per_person: '999', subtotal: '999' },
			{ cabinIndex: 2, people: 2, category: 'puente-superior', price_per_person: '999', total: '999' },
		]);
		assert.deepStrictEqual(payload, {
			1: { people: '3', category: 'puente-intermedio' },
			2: { people: '2', category: 'puente-superior' },
		});
		assert.strictEqual(JSON.stringify(payload).includes('999'), false);
	},
	'new cabins default to no supplement while surviving categories remain independent'() {
		const previous = { 1: { people: '2', category: 'puente-superior' } };
		assert.deepStrictEqual(front.reconcileRows([3, 2], previous, 'sin-suplemento'), {
			1: { people: '3', category: 'puente-superior' },
			2: { people: '2', category: 'sin-suplemento' },
		});
	},
	'removed cabins leave no stale selection in state or payload'() {
		const reconciled = state.reconcileCabins(
			[2],
			{ 1: { people: '3', category: 'puente-intermedio' }, 2: { people: '2', category: 'puente-superior' } },
			'sin-suplemento'
		);
		assert.deepStrictEqual(reconciled, { 1: { people: '2', category: 'puente-intermedio' } });
		assert.deepStrictEqual(state.buildCabinPayload([{ cabinIndex: 1, people: 2, category: reconciled[1].category }]), {
			1: { people: '2', category: 'puente-intermedio' },
		});
	},
	'latest form-local request aborts its predecessor and rejects the stale token'() {
		const coordinator = state.createRequestCoordinator();
		let aborts = 0;
		const oldToken = coordinator.begin({ abort() { aborts += 1; } });
		const newToken = coordinator.begin({ abort() { aborts += 1; } });
		assert.strictEqual(aborts, 1);
		assert.strictEqual(coordinator.isCurrent(oldToken), false);
		assert.strictEqual(coordinator.isCurrent(newToken), true);
	},
	'existing AJAX bridge remains singular and carries cabin payloads in both formats'() {
		assert.strictEqual((addOnSource.match(/\$\.ajaxPrefilter\s*\(/g) || []).length, 1);
		assert.match(addOnSource, /collectCabinsFromForm/);
		assert.match(addOnSource, /offitravel_cabins/);
		assert.match(addOnSource, /typeof d !== 'string'/);
		assert.match(addOnSource, /typeof d === 'object'/);
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
