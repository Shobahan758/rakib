const { test } = require('node:test');
const assert = require('node:assert/strict');
const vm = require('node:vm');
const { readFileSync } = require('node:fs');

const script = readFileSync('public/asset/js/script.js', 'utf8');
const source = script.slice(
  script.indexOf('// Product selection and quantity'),
  script.indexOf('// Menu card order button connection'),
);

function setup() {
  const handlers = {};
  const element = (value = '') => ({ value, textContent: '', dataset: {}, addEventListener(name, callback) { this.handlers ??= {}; this.handlers[name] = callback; } });
  const quantityInput = element('1');
  const productIdInput = element('');
  const quantity = element('1');
  const radio = element('9');
  radio.checked = true;
  radio.dataset = { price: '1450', name: 'Furniture polish', image: '/product.webp' };
  const minus = element();
  const plus = element();
  const option = {
    classList: { toggle() {} },
    querySelector(selector) {
      return { '.product-radio': radio, '.product-qty': quantity, '.product-qty-minus': minus, '.product-qty-plus': plus }[selector];
    },
  };
  const delivery = element();
  delivery.checked = true;
  delivery.dataset.charge = '0';
  const summary = { dataset: { currency: '৳', quantityPrefix: 'পরিমাণ' } };
  const outputs = {
    selectedProductImage: {}, selectedProductName: {}, selectedProductQuantity: {},
    selectedProductPrice: {}, selectedOrderTotal: {},
  };

  vm.runInNewContext(source, {
    document: {
      getElementById(id) { return id === 'qty' ? quantityInput : id === 'productId' ? productIdInput : outputs[id] ?? null; },
      querySelector(selector) {
        return { '.selected-product-summary': summary, '.product-radio:checked': radio, 'input[name="delivery_area"]:checked': delivery }[selector] ?? null;
      },
      querySelectorAll(selector) {
        return { '.product-option': [option], 'input[name="delivery_area"]': [delivery], '.delivery-option': [] }[selector] ?? [];
      },
    },
    window: { matchMedia: () => ({ matches: true }), setTimeout(callback) { handlers.timeout = callback; } },
    setTimeout(callback) { handlers.timeout = callback; },
  });

  return { quantity, quantityInput, minus, plus, outputs };
}

test('quantity buttons update the selected product price and order total', () => {
  const state = setup();

  state.plus.handlers.click();
  assert.equal(state.quantityInput.value, 2);
  assert.equal(state.outputs.selectedProductPrice.textContent, '৳2,900');
  assert.equal(state.outputs.selectedOrderTotal.textContent, '৳2,900');
  assert.equal(state.outputs.selectedProductQuantity.textContent, 'পরিমাণ: ২');

  state.minus.handlers.click();
  assert.equal(state.outputs.selectedProductPrice.textContent, '৳1,450');

  state.quantity.value = '7';
  state.quantity.handlers.change();
  assert.equal(state.outputs.selectedProductPrice.textContent, '৳10,150');
  assert.equal(state.outputs.selectedOrderTotal.textContent, '৳10,150');
});
