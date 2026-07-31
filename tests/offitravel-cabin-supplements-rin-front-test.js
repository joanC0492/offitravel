'use strict';

const assert = require('assert');
const fs = require('fs');
const path = require('path');

const frontPath = path.join(
	__dirname,
	'../wp-content/mu-plugins/offitravel-cabin-supplements-front.js'
);
const addOnPath = path.join(
	__dirname,
	'../wp-content/mu-plugins/offitravel-product-addons-front.js'
);
const front = fs.existsSync(frontPath) ? require(frontPath) : {};
const addOnSource = fs.readFileSync(addOnPath, 'utf8');

const tests = {
	'public helper formats the three trusted Rin option labels'() {
		assert.strictEqual(typeof front.optionDisplayLabel, 'function');
		assert.strictEqual(front.optionDisplayLabel({ label: 'Sin suplemento', price_per_person: '0.00' }, '€'), 'Sin suplemento');
		assert.strictEqual(front.optionDisplayLabel({ label: 'Puente intermedio', price_per_person: '135.00' }, '€'), 'Puente intermedio (+135,00 €/persona)');
		assert.strictEqual(front.optionDisplayLabel({ label: 'Puente superior', price_per_person: '200.00' }, '€'), 'Puente superior (+200,00 €/persona)');
	},
	'public helper builds browser payload without prices labels or subtotals'() {
		assert.strictEqual(typeof front.buildPayloadFromRows, 'function');
		const payload = front.buildPayloadFromRows([
			{ cabinIndex: 1, people: 2, category: 'puente-intermedio', price: 999, subtotal: 999 },
		]);
		assert.deepStrictEqual(payload, { 1: { people: '2', category: 'puente-intermedio' } });
		assert.strictEqual(JSON.stringify(payload).includes('999'), false);
	},
	'public helper defaults a newly rendered cabin to sin suplemento'() {
		assert.strictEqual(typeof front.reconcileRows, 'function');
		assert.deepStrictEqual(front.reconcileRows([2], {}, 'sin-suplemento'), {
			1: { people: '2', category: 'sin-suplemento' },
		});
	},
	'existing add-on bridge carries cabins in object and serialized AJAX payloads once'() {
		assert.strictEqual((addOnSource.match(/\$\.ajaxPrefilter\s*\(/g) || []).length, 1);
		assert.match(addOnSource, /offitravel_cabins/);
		assert.match(addOnSource, /collectCabinsFromForm/);
		assert.match(addOnSource, /hasCabins/);
		assert.doesNotMatch(addOnSource, /ajaxPrefilter[\s\S]*ajaxPrefilter[\s\S]*ajaxPrefilter/);
	},
	'existing recalculator is exposed for the cabin UI and guards stale responses'() {
		assert.match(addOnSource, /window\.offitravelPrdAddonRecalculate/);
		assert.match(addOnSource, /offitravel-total-recalc-sequence/);
		assert.match(addOnSource, /offitravel-total-recalc-request/);
	},
	'shared recalculator does not duplicate room fields owned by room mode'() {
		assert.doesNotMatch(addOnSource, /a\.offitravel_room_count\s*=/);
		assert.doesNotMatch(addOnSource, /a\.offitravel_room_people\s*=/);
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
