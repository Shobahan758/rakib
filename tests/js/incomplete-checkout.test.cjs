const { test } = require('node:test');
const assert = require('node:assert/strict');
const vm = require('node:vm');
const { readFileSync } = require('node:fs');
const { webcrypto } = require('node:crypto');

function setup() {
  const events = {};
  const fields = Object.fromEntries(Object.entries({ incomplete_token: webcrypto.randomUUID(), name: '', phone: '', email: '', address: '', quantity: '1', _token: 'csrf' }).map(([key, value]) => [key, { value }]));
  const calls = [];
  let timer;
  let poll;
  const form = { dataset: { incompleteAction: '/incomplete-orders' }, elements: { namedItem: name => fields[name] }, addEventListener: (name, fn) => { events[name] = fn; } };
  const window = { addEventListener: (name, fn) => { events[name] = fn; } };
  vm.runInNewContext(readFileSync('public/asset/js/incomplete-checkout.js', 'utf8'), {
    window, crypto: webcrypto, Uint8Array,
    document: { visibilityState: 'visible', getElementById: id => id === 'orderForm' ? form : null, addEventListener() {} },
    sessionStorage: { getItem() {}, setItem() {} },
    setTimeout: fn => { timer = fn; return 1; }, clearTimeout: () => { timer = null; },
    setInterval: fn => { poll = fn; return 2; },
    fetch: async (_, options) => { calls.push(JSON.parse(options.body)); return { ok: true, json: async () => ({ saved: true }) }; },
  });
  return { fields, calls, events, window, tick: async () => { if (timer) await timer(); }, poll: async () => { if (poll) await poll(); } };
}

test('name and valid phone autosave without checkout, repeated blur does not duplicate', async () => {
  const state = setup();
  state.fields.name.value = 'Customer';
  state.events.input(); await state.tick();
  assert.equal(state.calls.length, 0);
  state.fields.phone.value = '+৮৮০১৭১২৩৪৫৬৭৮';
  state.fields.email.value = 'customer@example.com';
  state.events.input(); await state.tick();
  assert.equal(state.calls.length, 1);
  assert.equal(state.calls[0].phone, '01712345678');
  assert.equal(state.calls[0].email, 'customer@example.com');
  assert.equal(state.calls[0].address, '');
  await state.events.pagehide();
  assert.equal(state.calls.length, 1);
  state.fields.address.value = 'Dhaka';
  await state.events.pagehide();
  assert.equal(state.calls.length, 2);
});

test('browser autofill is captured even when it dispatches no input event', async () => {
  const state = setup();
  state.fields.name.value = 'Autofilled Customer';
  state.fields.phone.value = '01812345678';
  state.fields.email.value = 'unfinished@';
  await state.poll();
  assert.equal(state.calls.length, 1);
  assert.equal(state.calls[0].name, 'Autofilled Customer');
  assert.equal(state.calls[0].email, '');
  await state.poll();
  assert.equal(state.calls.length, 1);
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
