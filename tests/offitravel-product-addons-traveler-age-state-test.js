'use strict';

const assert = require('assert');
const fs = require('fs');
const path = require('path');
const state = require('../wp-content/mu-plugins/offitravel-product-addons-traveler-age-state.js');
const frontSource = fs.readFileSync(
	path.join(__dirname, '../wp-content/mu-plugins/offitravel-product-addons-front.js'),
	'utf8'
);
const roomModeSource = fs.readFileSync(
	path.join(__dirname, '../wp-content/mu-plugins/offitravel-ovabrw-room-mode.php'),
	'utf8'
);

const tests = {
	'adding a traveler preserves existing values and creates an empty row'() {
		const previous = {
			'1:1': { selected: true, age: '35' },
		};
		const actual = state.reconcileTravelerState([2], previous);

		assert.deepStrictEqual(actual, [
			{ key: '1:1', room: 1, position: 1, traveler: 1, selected: true, age: '35' },
			{ key: '1:2', room: 1, position: 2, traveler: 2, selected: false, age: '' },
		]);
	},
	'removing a traveler drops its values instead of reviving them later'() {
		const previous = {
			'1:1': { selected: true, age: '35' },
			'1:2': { selected: true, age: '72' },
		};
		const reduced = state.reconcileTravelerState([1], previous);
		const rebuilt = state.reconcileTravelerState([2], state.indexTravelerState(reduced));

		assert.deepStrictEqual(rebuilt, [
			{ key: '1:1', room: 1, position: 1, traveler: 1, selected: true, age: '35' },
			{ key: '1:2', room: 1, position: 2, traveler: 2, selected: false, age: '' },
		]);
	},
	'multiple rooms keep independent room positions and global traveler numbers'() {
		const previous = {
			'1:2': { selected: true, age: '69' },
			'2:1': { selected: true, age: '70' },
		};
		const actual = state.reconcileTravelerState([2, 1], previous);

		assert.deepStrictEqual(actual.map((row) => [row.room, row.position, row.traveler, row.selected, row.age]), [
			[1, 1, 1, false, ''],
			[1, 2, 2, true, '69'],
			[2, 1, 3, true, '70'],
		]);
	},
	'unselected rows never preserve a residual age'() {
		const actual = state.reconcileTravelerState([1], {
			'1:1': { selected: false, age: '72' },
		});

		assert.strictEqual(actual[0].age, '');
	},
	'traveler selections remain separate from simultaneously selected fixed add-ons'() {
		const travelerAge = state.buildTravelerAgePayload([
			{ serviceId: '12718', room: 1, position: 1, selected: true, age: '35' },
			{ serviceId: '12718', room: 1, position: 2, selected: true, age: '72' },
		]);
		const checkedInputs = [
			{ name: 'offitravel_addons[]', value: '12027' },
			{ name: 'offitravel_age_addons[12718][1][1][selected]', value: '1' },
			{ name: 'offitravel_age_addons[12718][1][2][selected]', value: '1' },
		];
		const fixedIds = checkedInputs
			.filter((input) => input.name === 'offitravel_addons[]')
			.map((input) => input.value);

		assert.deepStrictEqual(fixedIds, ['12027']);
		assert.deepStrictEqual(travelerAge, {
			12718: {
				1: {
					1: { selected: '1', age: '35' },
					2: { selected: '1', age: '72' },
				},
			},
		});
		assert.match(frontSource, /\.find\(['"]input\[name=["']offitravel_addons\[\]["']\]:checked['"]\)/);
		assert.doesNotMatch(frontSource, /\.find\(['"]\.offitravel-prd-addon-fields input\[type=["']checkbox["']\]:checked['"]\)/);
	},
	'the existing AJAX prefilter recognizes traveler-age data already present in a serialized payload'() {
		const serialized = 'action=ovabrw_calculate_total&offitravel_age_addons%5B12718%5D%5B1%5D%5B1%5D%5Bselected%5D=1';

		assert.strictEqual(state.serializedPayloadHasAddonSelections(serialized), true);
		assert.strictEqual(state.serializedPayloadHasAddonSelections('action=ovabrw_calculate_total'), false);
		assert.match(frontSource, /serializedPayloadHasAddonSelections\(d\)/);
	},
	'age zero is valid but selected travelers cannot keep an empty negative or decimal age'() {
		assert.strictEqual(state.isTravelerAgeValueValid('0'), true);
		assert.strictEqual(state.isTravelerAgeValueValid('35'), true);
		assert.strictEqual(state.isTravelerAgeValueValid(''), false);
		assert.strictEqual(state.isTravelerAgeValueValid('-1'), false);
		assert.strictEqual(state.isTravelerAgeValueValid('35.5'), false);
		assert.strictEqual(
			state.selectedTravelerAgesAreValid([
				{ selected: true, age: '0' },
				{ selected: true, age: '72' },
				{ selected: false, age: '' },
			]),
			true
		);
		assert.strictEqual(
			state.selectedTravelerAgesAreValid([{ selected: true, age: '' }]),
			false
		);
	},
	'age errors remain hidden initially and appear only after validation is requested'() {
		const expected = 'Introduce una edad entera igual o superior a 0 para contratar el seguro.';

		assert.strictEqual(state.travelerAgeValidationMessage(false, '', true), '');
		assert.strictEqual(state.travelerAgeValidationMessage(true, '0', true), '');
		assert.strictEqual(state.travelerAgeValidationMessage(true, '', false), '');
		assert.strictEqual(state.travelerAgeValidationMessage(true, '', true), expected);
		assert.strictEqual(state.travelerAgeValidationMessage(true, '-1', true), expected);
		assert.strictEqual(state.travelerAgeValidationMessage(true, '35.5', true), expected);
		assert.match(frontSource, /data-offitravel-traveler-age-error/);
		assert.match(frontSource, /offitravel-age-validation-revealed/);
		assert.match(frontSource, /revealInvalidTravelerAges/);
		assert.match(frontSource, /on\('blur',\s*'\[data-offitravel-traveler-age\]'/);
		assert.match(frontSource, /button\.booking-form-submit/);
		assert.match(frontSource, /attr\('aria-invalid'/);
	},
	'public age inputs use an example placeholder and schedule recalculation while typing'() {
		assert.match(frontSource, /placeholder:\s*'Ej\. 35'/);
		assert.match(frontSource, /on\('input',\s*'\[data-offitravel-traveler-age\]'/);
		assert.match(frontSource, /scheduleTravelerAgeRecalculation/);
	},
	'booking remains disabled until the latest room-mode age recalculation finishes'() {
		assert.match(frontSource, /prop\('disabled',\s*shouldDisable\)/);
		assert.match(frontSource, /previousRequest\.abort\(\)/);
		assert.match(frontSource, /request\.always/);
		assert.match(roomModeSource, /return \$\.ajax\(\{/);
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
