'use strict';

const assert = require('assert');
const fs = require('fs');
const path = require('path');

const modulePath = '../wp-content/mu-plugins/offitravel-cabin-supplements-admin.js';
const admin = require(modulePath);
const source = fs.readFileSync(path.join(__dirname, modulePath), 'utf8');

const tests = {
	'next row index advances beyond the largest existing index'() {
		assert.strictEqual(admin.nextRowIndex(['0', '3', '1']), 4);
		assert.strictEqual(admin.nextRowIndex([]), 0);
	},
	'interaction marker changes only through an explicit metabox action'() {
		const marker = { value: '0' };
		admin.markInteracted(marker);
		assert.strictEqual(marker.value, '1');
		assert.doesNotThrow(() => admin.markInteracted(null));
	},
	'admin module contains no AJAX or public-form interception'() {
		assert.strictEqual(/ajaxPrefilter|\.ajax\s*\(|fetch\s*\(/.test(source), false);
		assert.strictEqual(/wp_enqueue_scripts|woocommerce_add_to_cart/.test(source), false);
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
