const { test } = require('node:test');
const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const vm = require('node:vm');
const source = readFileSync('public/asset/js/visitor-events.js', 'utf8');
function setup(failProvider = false) {
  const calls = []; const handlers = {}; let observe;
  const context = {
    URLSearchParams,
    window: { location: { pathname: '/' }, IntersectionObserver: true, storeTracking: { activity() { if (failProvider) throw new Error('Pixel blocked'); } } },
    document: {
      querySelector: selector => ({ content: selector.includes('endpoint') ? '/tracking/events' : 'csrf' }),
      addEventListener: (name, callback) => { handlers[name] = callback; },
      getElementById: id => id === 'orderForm' ? { addEventListener: (name, callback) => { handlers[name] = callback; } } : {},
    },
    IntersectionObserver: class { constructor(callback) { observe = callback; } observe() {} },
    fetch: async (url, options) => { calls.push({ url, ...Object.fromEntries(options.body) }); return { ok: true }; },
  };
  vm.runInNewContext(source, context);
  return { calls, handlers, context, checkout: () => observe([{ isIntersecting: true }]),
    click: kind => handlers.click({ target: { closest: () => ({ matches: selector => kind === 'order' ? selector.includes('#orderForm') : kind === 'whatsapp' ? selector.includes('wa.me') : false }) } }) };
}

test('every order and WhatsApp click counts, page/form/checkout views count once', () => {
  const state = setup();
  state.click('order'); state.click('order'); state.click('whatsapp'); state.click('admin');
  state.handlers.input(); state.handlers.input(); state.checkout(); state.checkout();
  const events = state.calls.map(call => call.event);
  assert.equal(events.filter(event => event === 'order_button_click').length, 2);
  assert.equal(events.filter(event => event === 'whatsapp_click').length, 1);
  for (const event of ['page_view', 'form_start', 'checkout_view']) assert.equal(events.filter(value => value === event).length, 1);
  assert.equal(state.calls[0]._token, 'csrf');
  vm.runInNewContext(source, state.context);
  assert.equal(state.calls.length, 6);
});

test('local counts still work when advertising tracking throws an error', () => {
  const state = setup(true);
  state.click('order');
  assert.deepEqual(state.calls.map(call => call.event), ['page_view', 'order_button_click']);
});
