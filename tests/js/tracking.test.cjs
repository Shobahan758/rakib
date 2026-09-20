const { test } = require('node:test');
const assert = require('node:assert/strict');
const vm = require('node:vm');
const { readFileSync } = require('node:fs');
const source = readFileSync('public/asset/js/tracking.js', 'utf8');
function setup(config = {}, broken = false) {
  const calls = { meta: [], google: [], tiktok: [] };
  const window = {
    fbq: (...args) => { if (broken) throw new Error('Pixel unavailable'); calls.meta.push(args); },
    gtag: (...args) => calls.google.push(args), ttq: { track: (...args) => calls.tiktok.push(args) },
  };
  const storage = new Map();
  vm.runInNewContext(source, {
    window, document: {
      getElementById: id => id === 'trackingConfig' ? { textContent: JSON.stringify(config) } : { value: '2' },
      querySelector: () => ({ value: '12', dataset: { name: 'Burger', price: '299' } }),
    }, sessionStorage: { getItem: key => storage.get(key), setItem: (key, value) => storage.set(key, value) },
  });
  return { calls, window };
}
const config = { meta_pixel_id: '123456789', google_analytics_id: 'G-TEST123', google_ads_id: 'AW-123456', google_ads_conversion_label: 'Label123', google_tag_manager_id: 'GTM-TEST123', tiktok_pixel_id: 'ABC123456' };
const purchase = { transaction_id: '42', currency: 'BDT', value: 598, shipping: 80, items: [{ item_id: '12', item_name: 'Burger', price: 299, quantity: 2 }] };

test('product views, checkout and one confirmed purchase reach configured providers', () => {
  const { window, calls } = setup(config);
  window.storeTracking.activity('form_start');
  window.storeTracking.purchase(purchase); window.storeTracking.purchase(purchase);
  assert.deepEqual(calls.meta.map(call => call[1]), ['ViewContent', 'InitiateCheckout', 'Purchase']);
  assert.deepEqual(calls.tiktok.map(call => call[0]), ['ViewContent', 'InitiateCheckout', 'Purchase']);
  assert.equal(calls.google.filter(call => call[1] === 'purchase').length, 1);
  const conversion = calls.google.find(call => call[1] === 'conversion');
  assert.equal(conversion[2].send_to, 'AW-123456/Label123');
  assert.equal(conversion[2].value, 598);
  assert.equal(window.dataLayer.filter(item => item.event === 'store_purchase').length, 1);
});

test('disabled providers and unconfirmed purchases send no conversion', () => {
  const { window, calls } = setup();
  window.storeTracking.purchase(null); window.storeTracking.purchase(purchase);
  assert.equal(calls.meta.length + calls.google.length + calls.tiktok.length, 0);
});

test('Meta works with only a Pixel ID and sends checkout once across clicks and inputs', () => {
  const { window, calls } = setup({ meta_pixel_id: '123456789012345' });
  window.storeTracking.activity('order_button_click');
  window.storeTracking.activity('form_start');
  window.storeTracking.activity('order_button_click');
  window.storeTracking.purchase(purchase);
  window.storeTracking.purchase(purchase);
  assert.deepEqual(calls.meta.map(call => call[1]), ['ViewContent', 'InitiateCheckout', 'Purchase']);
  assert.equal(calls.meta[2][2].currency, 'BDT');
  assert.equal(calls.meta[2][2].value, 598);
  assert.equal(calls.meta[2][3].eventID, 'order-42');
  assert.equal(calls.google.length + calls.tiktok.length, 0);
});

test('one broken provider does not break checkout or other providers', () => {
  const { window, calls } = setup(config, true);
  assert.doesNotThrow(() => window.storeTracking.purchase(purchase));
  assert.equal(calls.google.filter(call => call[1] === 'purchase').length, 1);
  assert.equal(calls.tiktok.filter(call => call[0] === 'Purchase').length, 1);
});
