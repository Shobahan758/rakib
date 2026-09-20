const { test } = require('node:test');
const assert = require('node:assert/strict');
const vm = require('node:vm');
const { readFileSync } = require('node:fs');

const source = readFileSync('public/asset/js/mobile-checkout-bar.js', 'utf8');

test('mobile order and WhatsApp bar hides throughout checkout and returns after leaving it', () => {
  let observerCallback;
  const classes = new Set();
  let orderClick;
  const orderButton = { addEventListener(name, callback) { if (name === 'click') orderClick = callback; } };
  const bar = {
    classList: { toggle(name, enabled) { enabled ? classes.add(name) : classes.delete(name); } },
    querySelector: () => orderButton,
  };
  const orderSection = {};

  vm.runInNewContext(source, {
    document: {
      querySelector: selector => selector === '.mobile-order-bar' ? bar : null,
      getElementById: id => id === 'Order' ? orderSection : null,
    },
    window: { IntersectionObserver: true },
    IntersectionObserver: class {
      constructor(callback) { observerCallback = callback; }
      observe(target) { assert.equal(target, orderSection); }
    },
  });

  orderClick();
  assert.equal(classes.has('is-checkout-hidden'), true);

  observerCallback([{ target: orderSection, isIntersecting: false }]);
  assert.equal(classes.has('is-checkout-hidden'), false);

  observerCallback([{ target: orderSection, isIntersecting: true }]);
  assert.equal(classes.has('is-checkout-hidden'), true);
});
