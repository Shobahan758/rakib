const { test } = require('node:test');
const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const vm = require('node:vm');

const source = readFileSync('public/asset/js/admin-order-notifications.js', 'utf8');

const flush = () => new Promise(resolve => setImmediate(resolve));

test('new orders after the initial baseline create a safe popup', async () => {
  const responses = [
    { latest_id: 4, orders: [] },
    { latest_id: 5, orders: [{
      id: 5, name: '<img src=x onerror=alert(1)>', phone: '01712345678', total: 850, url: '/admin/order/5/edit'
    }] }
  ];
  const stored = new Map();
  const shown = [];
  let interval;
  const closeButton = { addEventListener() {} };
  const center = {
    dataset: { endpoint: '/admin/order-notifications', userId: '1' },
    prepend: toast => shown.push(toast)
  };
  const document = {
    visibilityState: 'visible',
    getElementById: id => id === 'adminOrderNotifications' ? center : null,
    addEventListener() {},
    createElement: () => ({
      className: '', innerHTML: '', setAttribute() {},
      querySelector: () => closeButton, classList: { add() {} }, remove() {}
    })
  };

  vm.runInNewContext(source, {
    window: {}, document, Intl,
    sessionStorage: {
      getItem: key => stored.get(key) ?? null,
      setItem: (key, value) => stored.set(key, value)
    },
    fetch: async () => ({ ok: true, json: async () => responses.shift() }),
    setInterval: callback => { interval = callback; return 1; },
    setTimeout: () => 1
  });

  await flush();
  assert.equal(shown.length, 0);
  assert.equal(stored.get('adminLatestOrderId:1'), '4');

  await interval();
  await flush();
  assert.equal(shown.length, 1);
  assert.match(shown[0].innerHTML, /নতুন অর্ডার এসেছে/);
  assert.doesNotMatch(shown[0].innerHTML, /<img src=x/);
  assert.match(shown[0].innerHTML, /&lt;img src=x/);
  assert.equal(stored.get('adminLatestOrderId:1'), '5');
});
