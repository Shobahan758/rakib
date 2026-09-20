(() => {
  const sidebar = document.getElementById('adminSidebar');
  const overlay = document.getElementById('sidebarOverlay');
  const toggle = document.getElementById('menuToggle');
  const mobile = matchMedia('(max-width: 1024px)');
  const setOpen = open => {
    const active = mobile.matches && open;
    sidebar.classList.toggle('open', active);
    overlay.classList.toggle('show', active);
    document.body.classList.toggle('admin-menu-open', active);
    toggle.setAttribute('aria-expanded', String(active));
    toggle.setAttribute('aria-label', active ? 'Close menu' : 'Open menu');
    sidebar.inert = mobile.matches && !active;
    if (active) sidebar.querySelector('a,summary,button')?.focus();
  };
  toggle.addEventListener('click', () => setOpen(!sidebar.classList.contains('open')));
  overlay.addEventListener('click', () => { setOpen(false); toggle.focus(); });
  document.addEventListener('keydown', event => {
    if (!mobile.matches || !sidebar.classList.contains('open')) return;
    if (event.key === 'Escape') { setOpen(false); toggle.focus(); }
    if (event.key === 'Tab') {
      const nodes = Array.from(sidebar.querySelectorAll('a,summary,button')).filter(node => node.getClientRects().length);
      const first = nodes[0], last = nodes[nodes.length - 1];
      if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus(); }
      else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus(); }
    }
  });
  sidebar.addEventListener('click', event => { if (event.target.closest('a') && mobile.matches) setOpen(false); });
  mobile.addEventListener('change', () => setOpen(false));
  setOpen(false);

  // Sidebar submenu state: keep one group open, preserve manual choice, and
  // expose the native <details> state correctly to assistive technology.
  const navGroups = Array.from(sidebar.querySelectorAll('.nav-group'));
  const navStorageKey = 'adminSidebarOpenGroup';
  let savedGroup = null;
  try { savedGroup = sessionStorage.getItem(navStorageKey); } catch (_) {}
  const savedGroupIndex = savedGroup !== null && /^\d+$/.test(savedGroup)
    ? Number(savedGroup)
    : -1;
  const activeGroup = navGroups.find(group => group.querySelector('.nav-link.active'));
  const initialGroup = activeGroup || navGroups.find(group => group.open)
    || navGroups[savedGroupIndex] || null;
  navGroups.forEach(group => { group.open = group === initialGroup; });
  const syncNavGroups = () => navGroups.forEach(group =>
    group.querySelector('summary')?.setAttribute('aria-expanded', String(group.open)));
  syncNavGroups();
  navGroups.forEach((group, index) => group.addEventListener('toggle', () => {
    if (group.open) {
      navGroups.forEach(other => { if (other !== group) other.open = false; });
      try { sessionStorage.setItem(navStorageKey, String(index)); } catch (_) {}
    } else if (!sidebar.querySelector('.nav-group[open]')) {
      try { sessionStorage.removeItem(navStorageKey); } catch (_) {}
    }
    syncNavGroups();
  }));

  // Color controls.
  document.querySelectorAll('[data-color-target]').forEach(picker => {
    const input = document.getElementById(picker.dataset.colorTarget);
    picker.addEventListener('input', () => { input.value = picker.value; });
    input.addEventListener('input', () => { if (/^#[a-fA-F0-9]{6}$/.test(input.value)) picker.value = input.value; });
  });
  document.querySelectorAll('.table-wrap').forEach(wrapper => {
    wrapper.tabIndex = 0;
    wrapper.setAttribute('role', 'region');
    wrapper.setAttribute('aria-label', 'Table — scroll horizontally to see all columns');
  });
})();
