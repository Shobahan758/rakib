const { test } = require('node:test');
const assert = require('node:assert/strict');
const vm = require('node:vm');
const { readFileSync } = require('node:fs');
const source = readFileSync('public/asset/js/video-reviews.js', 'utf8');
function setup({ count = 3, reduced = false, dataset = {} } = {}) {
  const element = () => ({ handlers: {}, addEventListener(name, fn) { this.handlers[name] = fn; }, setAttribute() {} });
  const track = Object.assign(element(), { children: Array.from({ length: count }, (_, i) => ({ offsetLeft: i * 300 })), scrollWidth: count * 300, clientWidth: 300, scrollLeft: 0, scrollTo({ left }) { this.scrollLeft = left; }, querySelectorAll: () => [], contains: () => false });
  const controls = { hidden: true }; const toggle = element(); const prev = element(); const next = element();
  const slider = Object.assign(element(), { dataset, querySelector: key => ({ '.video-review-track': track, '.video-review-controls': controls, '[data-video-pause]': toggle, '[data-video-prev]': prev, '[data-video-next]': next })[key], contains: () => false, getBoundingClientRect: () => ({ top: 0, bottom: 600 }) });
  const doc = { hidden: false, querySelectorAll: () => [slider] }; let tick;
  vm.runInNewContext(source, { document: doc, window: element(), matchMedia: () => Object.assign(element(), { matches: reduced }), setInterval: fn => { tick = fn; }, innerHeight: 900 });
  return { track, controls, toggle, prev, next, slider, doc, tick };
}
test('carousel automatically advances, loops, and supports manual arrows without cloning videos', () => {
  const state = setup();
  state.tick(); assert.equal(state.track.scrollLeft, 300);
  state.tick(); assert.equal(state.track.scrollLeft, 600);
  state.tick(); assert.equal(state.track.scrollLeft, 0);
  state.prev.handlers.click(); assert.equal(state.track.scrollLeft, 600);
  assert.equal(state.track.children.length, 3);
});
test('touch, pause, hover and hidden tabs prevent automatic movement', () => {
  const state = setup();
  state.track.handlers.touchstart(); state.tick(); assert.equal(state.track.scrollLeft, 0);
  state.toggle.handlers.click(); state.tick(); assert.equal(state.track.scrollLeft, 300);
  state.slider.handlers.mouseenter(); state.tick(); assert.equal(state.track.scrollLeft, 300);
  state.slider.handlers.mouseleave(); state.doc.hidden = true; state.tick(); assert.equal(state.track.scrollLeft, 300);
});
test('single videos remain static and reduced motion starts paused', () => {
  const single = setup({ count: 1 }); assert.equal(single.tick, undefined); assert.equal(single.controls.hidden, true);
  const reduced = setup({ reduced: true }); reduced.tick(); assert.equal(reduced.track.scrollLeft, 0);
  reduced.next.handlers.click(); assert.equal(reduced.track.scrollLeft, 300);
});


test('backend autoplay and button labels control the carousel', () => {
  const state = setup({ dataset: { autoplay: '0', pauseLabel: 'Stop', resumeLabel: 'Start', interval: '8' } });
  state.tick(); assert.equal(state.track.scrollLeft, 0); assert.equal(state.toggle.textContent, 'Start');
  state.toggle.handlers.click(); state.tick(); assert.equal(state.track.scrollLeft, 300); assert.equal(state.toggle.textContent, 'Stop');
});
