const { test } = require('node:test');
const assert = require('node:assert/strict');
const vm = require('node:vm');
const { readFileSync } = require('node:fs');

const script = readFileSync('public/asset/js/admin-layout.js', 'utf8');
const source = script.slice(
  script.indexOf('// Sidebar submenu state'),
  script.indexOf('// Color controls.'),
);

const group = (open = false, active = false) => {
  const summary = { attributes: {}, setAttribute(name, value) { this.attributes[name] = value; } };
  return {
    open,
    summary,
    active,
    events: {},
    querySelector(selector) {
      if (selector === 'summary') return summary;
      if (selector === '.nav-link.active') return active ? {} : null;
      return null;
    },
    addEventListener(name, callback) { this.events[name] = callback; },
  };
};

test('admin sidebar keeps one accessible submenu open and remembers the choice', () => {
  const groups = [group(true, true), group(false), group(true)];
  const saved = new Map([['adminSidebarOpenGroup', '2']]);
  const sidebar = {
    querySelectorAll: () => groups,
    querySelector: selector => selector === '.nav-group[open]'
      ? groups.find(item => item.open) || null
      : null,
  };

  vm.runInNewContext(source, {
    sidebar,
    sessionStorage: {
      getItem: key => saved.get(key) ?? null,
      setItem: (key, value) => saved.set(key, value),
      removeItem: key => saved.delete(key),
    },
  });

  assert.deepEqual(groups.map(item => item.open), [true, false, false]);
  assert.deepEqual(groups.map(item => item.summary.attributes['aria-expanded']), ['true', 'false', 'false']);

  groups[1].open = true;
  groups[1].events.toggle();
  assert.deepEqual(groups.map(item => item.open), [false, true, false]);
  assert.equal(saved.get('adminSidebarOpenGroup'), '1');
  assert.deepEqual(groups.map(item => item.summary.attributes['aria-expanded']), ['false', 'true', 'false']);
});

test('admin sidebar leaves all submenus closed without an active or saved group', () => {
  const groups = [group(false), group(false)];
  const sidebar = {
    querySelectorAll: () => groups,
    querySelector: selector => selector === '.nav-group[open]'
      ? groups.find(item => item.open) || null
      : null,
  };

  vm.runInNewContext(source, {
    sidebar,
    sessionStorage: {
      getItem: () => null,
      setItem: () => {},
      removeItem: () => {},
    },
  });

  assert.deepEqual(groups.map(item => item.open), [false, false]);
  assert.deepEqual(groups.map(item => item.summary.attributes['aria-expanded']), ['false', 'false']);
});
