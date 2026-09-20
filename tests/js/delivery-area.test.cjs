const { test } = require('node:test');
const assert = require('node:assert/strict');
const vm = require('node:vm');
const { readFileSync } = require('node:fs');
const source = readFileSync('public/asset/js/delivery-area.js', 'utf8');

function setup(initial = '') {
  let selected = 'inside_dhaka';
  let changes = 0;
  const handlers = {};
  const address = { value: initial, addEventListener: (name, fn) => { handlers[name] = fn; } };
  const options = Object.fromEntries(['inside_dhaka', 'outside_dhaka'].map(area => [area, {
    get checked() { return selected === area; },
    set checked(value) { if (value) selected = area; },
    dispatchEvent(event) { assert.equal(event.type, 'change'); assert.equal(event.bubbles, true); changes++; },
  }]));
  const form = { querySelector: selector => options[selector.includes('"inside_dhaka"') ? 'inside_dhaka' : 'outside_dhaka'] };
  vm.runInNewContext(source, {
    document: { getElementById: id => id === 'address' ? address : form },
    window: { addEventListener: (name, fn) => { handlers[name] = fn; } },
    Event: class { constructor(type, init) { this.type = type; Object.assign(this, init); } },
  });
  return { address, handlers, options, selected: () => selected, changes: () => changes };
}

test('English and Bengali Dhaka addresses select inside delivery', () => {
  for (const value of ['Mirpur, Dhaka-1216', '  DHAKA  ', 'ধানমন্ডি, ঢাকা', 'ঢাকায় মিরপুর', 'Dacca']) {
    const state = setup('Bogura');
    state.address.value = value;
    state.handlers.input();
    assert.equal(state.selected(), 'inside_dhaka');
  }
});

test('other addresses select outside delivery and notify the total calculator', () => {
  for (const value of ['Malgram, Dokhin para', 'বগুড়া', 'Chattogram', 'Dhakalia']) {
    const state = setup(value);
    assert.equal(state.selected(), 'outside_dhaka');
    assert.equal(state.changes(), 1);
    state.handlers.input();
    assert.equal(state.changes(), 1);
  }
});

test('pasted and restored addresses update; empty addresses preserve the selection', () => {
  const state = setup();
  assert.equal(state.changes(), 0);
  state.address.value = 'Sylhet'; state.handlers.change();
  assert.equal(state.selected(), 'outside_dhaka');
  state.address.value = ' '; state.handlers.input();
  assert.equal(state.selected(), 'outside_dhaka');
  state.address.value = 'Dhaka'; state.handlers.pageshow();
  assert.equal(state.selected(), 'inside_dhaka');
});

test('the buyer can manually correct the area until the address changes again', () => {
  const state = setup('Dhaka');
  state.options.outside_dhaka.checked = true;
  assert.equal(state.selected(), 'outside_dhaka');
  state.address.value = 'Mirpur, Dhaka'; state.handlers.input();
  assert.equal(state.selected(), 'inside_dhaka');
});
