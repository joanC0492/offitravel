'use strict';

const assert = require('assert');
const fs = require('fs');
const path = require('path');
const state = require('../wp-content/mu-plugins/offitravel-product-addons-fixed-state.js');
const frontSource = fs.readFileSync(
	path.join(__dirname, '../wp-content/mu-plugins/offitravel-product-addons-front.js'),
	'utf8'
);

const tests = {
	'positive integer validation accepts one and rejects malformed quantities'() {
		assert.strictEqual(state.isPositiveInteger('1'), true);
		assert.strictEqual(state.isPositiveInteger('3'), true);
		assert.strictEqual(state.isPositiveInteger(''), false);
		assert.strictEqual(state.isPositiveInteger('0'), false);
		assert.strictEqual(state.isPositiveInteger('-1'), false);
		assert.strictEqual(state.isPositiveInteger('1.5'), false);
	},
	'manual quantities are sent only for checked configured services'() {
		assert.deepStrictEqual(
			state.buildQuantityPayload([
				{ serviceId: '12027', selected: true, quantity: '3' },
				{ serviceId: '999', selected: false, quantity: '7' },
			]),
			{ 12027: '3' }
		);
	},
	'validation feedback is progressive'() {
		const message = 'Introduce un número entero de habitaciones igual o superior a 1.';
		assert.strictEqual(state.validationMessage(true, '', false), '');
		assert.strictEqual(state.validationMessage(true, '', true), message);
		assert.strictEqual(state.validationMessage(true, '0', true), message);
		assert.strictEqual(state.validationMessage(true, '1', true), '');
		assert.strictEqual(state.validationMessage(false, '', true), '');
	},
	'frontend extends the existing payload and interceptor only once'() {
		assert.match(frontSource, /offitravel_addon_quantities/);
		assert.match(frontSource, /collectFixedAddonQuantities/);
		assert.strictEqual((frontSource.match(/\$\.ajaxPrefilter\(/g) || []).length, 1);
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

