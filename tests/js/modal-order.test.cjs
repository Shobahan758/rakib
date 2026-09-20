const { test } = require('node:test');
const assert = require('node:assert/strict');
const vm = require('node:vm');
const { readFileSync } = require('node:fs');
const source = readFileSync('public/asset/js/script.js', 'utf8');
const handlers = source.slice(source.indexOf("successModal?.querySelectorAll('[data-buy-product]')"), source.indexOf("if (successModal?.dataset.openOnLoad"));
async function buy(ok) {
  let handler; let request;
  const quantity = { value: '2', reportValidity: () => true };
  const button = { dataset: { buyProduct: '9' }, innerHTML: 'Buy', disabled: false,
    closest: () => ({ querySelector: () => quantity }), addEventListener: (_, callback) => { handler = callback; } };
  const status = {};
  vm.runInNewContext(handlers, {
    currencySymbol: '৳',
    successModal: { dataset: { addonUrl: '/orders/1/modal-products?signature=test' }, querySelectorAll: () => [button] },
    document: { getElementById: () => status, querySelector: () => ({ content: 'csrf' }) },
    fetch: async (url, options) => { request = { url, ...JSON.parse(options.body) }; return { status: ok ? 201 : 422, json: async () => ok ? { order_id: 2, delivery_total: 1411, message: 'Same delivery' } : { message: 'Unavailable' } }; },
  });
  await handler();
  return { button, quantity, status, request };
}

test('modal sends chosen quantity directly and keeps a successful purchase disabled', async () => {
  const state = await buy(true);
  assert.equal(state.request.product_id, '9'); assert.equal(state.request.quantity, 2);
  assert.equal(state.button.disabled, true); assert.equal(state.quantity.disabled, true);
  assert.match(state.status.textContent, /Same delivery/);
});

test('failed modal purchase can be retried without losing quantity', async () => {
  const state = await buy(false);
  assert.equal(state.button.disabled, false); assert.equal(state.quantity.value, '2');
  assert.equal(state.status.textContent, 'Unavailable');
});
