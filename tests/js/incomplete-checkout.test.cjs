const { test } = require('node:test');
const assert = require('node:assert/strict');
const vm = require('node:vm');
const { readFileSync } = require('node:fs');
const { webcrypto } = require('node:crypto');

function setup() {
  const events = {};
  const fields = Object.fromEntries(Object.entries({ incomplete_token: webcrypto.randomUUID(), name: '', phone: '', address: '', quantity: '1', _token: 'csrf' }).map(([key, value]) => [key, { value }]));
  const calls = [];
  let timer;
  const form = { dataset: { incompleteAction: '/incomplete-orders' }, elements: { namedItem: name => fields[name] }, addEventListener: (name, fn) => { events[name] = fn; } };
  const window = { addEventListener: (name, fn) => { events[name] = fn; } };
  vm.runInNewContext(readFileSync('public/asset/js/incomplete-checkout.js', 'utf8'), {
    window, crypto: webcrypto, Uint8Array,
    document: { getElementById: id => id === 'orderForm' ? form : null, addEventListener() {} },
    sessionStorage: { getItem() {}, setItem() {} },
    setTimeout: fn => { timer = fn; return 1; }, clearTimeout: () => { timer = null; },
    fetch: async (_, options) => { calls.push(JSON.parse(options.body)); return { ok: true, json: async () => ({ saved: true }) }; },
  });
  return { fields, calls, events, window, tick: async () => { if (timer) await timer(); } };
}

test('name and valid phone autosave without checkout, repeated blur does not duplicate', async () => {
  const state = setup();
  state.fields.name.value = 'Customer';
  state.events.input(); await state.tick();
  assert.equal(state.calls.length, 0);
  state.fields.phone.value = '+৮৮০১৭১২৩৪৫৬৭৮';
  state.events.input(); await state.tick();
  assert.equal(state.calls.length, 1);
  assert.equal(state.calls[0].phone, '01712345678');
  assert.equal(state.calls[0].address, '');
  await state.events.pagehide();
  assert.equal(state.calls.length, 1);
  state.fields.address.value = 'Dhaka';
  await state.events.pagehide();
  assert.equal(state.calls.length, 2);
});

test('submission pauses autosave, completion stays cleared until a new edit', async () => {
  const state = setup();
  state.fields.name.value = 'Customer'; state.fields.phone.value = '01712345678';
  state.window.incompleteCheckout.pause();
  await state.events.pagehide(); assert.equal(state.calls.length, 0);
  const oldToken = state.fields.incomplete_token.value;
  state.window.incompleteCheckout.complete(); state.window.incompleteCheckout.resume();
  await state.events.pagehide(); assert.equal(state.calls.length, 0);
  assert.notEqual(state.fields.incomplete_token.value, oldToken);
  state.events.input(); await state.tick(); assert.equal(state.calls.length, 1);
});
