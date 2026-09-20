const { test } = require('node:test');
const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const vm = require('node:vm');
const source = readFileSync('public/asset/js/script.js', 'utf8').split("form?.addEventListener('submit', async (event) => {")[1];

async function submit(response) {
  let handler;
  let shown = 0;
  const button = { disabled: false, innerHTML: 'Order' };
  const status = { classList: { add() {} }, scrollIntoView() {} };
  const form = {
    dataset: {}, action: '/orders', checkValidity: () => true,
    classList: { remove() {} }, querySelector: () => button,
    addEventListener: (_, callback) => { handler = callback; },
  };
  vm.runInNewContext("form?.addEventListener('submit', async (event) => {" + source, {
    window: {}, successModal: null, form, phoneInput: null, productIdInput: { value: '1' },
    document: { getElementById: () => status, querySelector: () => ({ content: 'csrf' }) },
    FormData: class {}, fetch: async () => response,
    showOrderSuccess: () => { shown++; },
  });
  await handler({ preventDefault() {}, stopPropagation() {} });
  assert.equal(button.disabled, false);
  return { shown, status };
}

test('only a confirmed saved order opens the success modal', async () => {
  const result = await submit({ ok: true, status: 201, json: async () => ({ order_id: 42 }) });
  assert.equal(result.shown, 1);
});

test('redirected HTML, missing order IDs and validation errors never show success', async () => {
  for (const response of [
    { ok: true, status: 200, json: async () => { throw new Error('HTML'); } },
    { ok: true, status: 201, json: async () => ({ message: 'Success' }) },
    { ok: false, status: 422, json: async () => ({ message: 'Invalid phone' }) },
  ]) {
    const result = await submit(response);
    assert.equal(result.shown, 0);
    assert.match(result.status.className, /alert-danger/);
  }
});


test('expired sessions, throttling and server failures show actionable errors', async () => {
  for (const [status, text] of [[419, 'সেশন শেষ'], [429, 'এক মিনিট'], [500, 'সার্ভারে সমস্যা']]) {
    const result = await submit({ ok: false, status, json: async () => ({}) });
    assert.equal(result.shown, 0);
    assert.ok(result.status.textContent.includes(text));
  }
});
