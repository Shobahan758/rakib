const { test } = require('node:test');
const assert = require('node:assert/strict');
const vm = require('node:vm');
const { readFileSync } = require('node:fs');
const source = readFileSync('public/asset/js/review-slider.js', 'utf8');
function setup({ count = 3, reduced = false, dataset = {}, visible = 1 } = {}) {
  const element = () => ({ handlers: {}, addEventListener(name, fn) { this.handlers[name] = fn; }, setAttribute() {} });
  const track = Object.assign(element(), { children: Array.from({ length: count }, (_, i) => ({ offsetLeft: i * 300 })), get scrollWidth() { return this.children.length * 300; }, clientWidth: visible * 300, scrollLeft: 0, scrollTo({ left }) { this.scrollLeft = left; }, querySelectorAll: () => [], contains: () => false });
  Object.defineProperty(track, 'scrollWidth', { get: () => track.children.length * 300 });
  track.appendChild = copy => track.children.push(copy);
  track.children.forEach(slide => {
    slide.cloneNode = () => ({ setAttribute() {}, remove() { track.children.splice(track.children.indexOf(this), 1); } });
  });
  const controls = { hidden: true }; const toggle = element(); const prev = element(); const next = element();
  const slider = Object.assign(element(), { dataset, querySelector: key => ({ '.review-track': track, '.review-controls': controls, '[data-review-pause]': toggle, '[data-review-prev]': prev, '[data-review-next]': next })[key], contains: () => false, getBoundingClientRect: () => ({ top: 0, bottom: 600 }) });
  const doc = { hidden: false, querySelectorAll: () => [slider] }; let tick;
  const window = element();
  vm.runInNewContext(source, { document: doc, window, matchMedia: () => Object.assign(element(), { matches: reduced }), setInterval: fn => { tick = fn; }, innerHeight: 900 });
  return { track, controls, toggle, prev, next, slider, doc, tick, window };
}
test('carousel automatically advances, loops, and supports manual arrows without cloning images', () => {
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
test('single images remain static and reduced motion starts paused', () => {
  const single = setup({ count: 1 }); assert.equal(single.tick, undefined); assert.equal(single.controls.hidden, true);
  const reduced = setup({ reduced: true }); reduced.tick(); assert.equal(reduced.track.scrollLeft, 0);
  reduced.next.handlers.click(); assert.equal(reduced.track.scrollLeft, 300);
});


test('backend autoplay and button labels control the carousel', () => {
  const state = setup({ dataset: { autoplay: '0', pauseLabel: 'Stop', resumeLabel: 'Start', interval: '8' } });
  state.tick(); assert.equal(state.track.scrollLeft, 0); assert.equal(state.toggle.textContent, 'Start');
  state.toggle.handlers.click(); state.tick(); assert.equal(state.track.scrollLeft, 300); assert.equal(state.toggle.textContent, 'Stop');
});


test('large image collections keep their original count and reach the final image', () => {
  const state = setup({ count: 40 });
  for (let i = 0; i < 39; i++) state.next.handlers.click();
  assert.equal(state.track.scrollLeft, 11700);
  assert.equal(state.track.children.length, 40);
  state.next.handlers.click();
  assert.equal(state.track.scrollLeft, 0);
});

test('three desktop images auto-slide even when the original row fits', () => {
  const state = setup({ count: 3, visible: 3 });
  assert.equal(state.controls.hidden, false);
  assert.equal(state.track.children.length, 6);
  state.tick();
  assert.equal(state.track.scrollLeft, 300);
  state.window.handlers.resize();
  assert.equal(state.track.children.length, 6);
  state.track.clientWidth = 300;
  state.window.handlers.resize();
  assert.equal(state.track.children.length, 3);
});
