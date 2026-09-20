(() => {
  const endpoint = document.querySelector('meta[name="tracking-endpoint"]')?.content;
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
  if (!endpoint || !csrf || window.visitorEventsStarted) return;
  window.visitorEventsStarted = true;
  const onceEvents = new Set();
  const record = async (event, once = false) => {
    if (once && onceEvents.has(event)) return;
    if (once) onceEvents.add(event);
    try { window.storeTracking?.activity(event); } catch (_) {}
    try {
      const response = await fetch(endpoint, {
        method: 'POST', keepalive: true, credentials: 'same-origin',
        headers: { Accept: 'application/json' },
        body: new URLSearchParams({ _token: csrf, event, path: window.location.pathname }),
      });
      if (!response.ok && once) onceEvents.delete(event);
    } catch (_) { if (once) onceEvents.delete(event); }
  };
  record('page_view', true);
  document.addEventListener('click', event => {
    const button = event.target.closest?.('a, button');
    if (!button || button.disabled) return;
    if (button.matches('a[href="#Order"], #orderForm [type="submit"], [data-buy-product]')) record('order_button_click');
    if (button.matches('a.whatsapp, a[href*="wa.me/"], a[href*="api.whatsapp.com/"]')) record('whatsapp_click');
  });
  document.getElementById('orderForm')?.addEventListener('input', () => record('form_start', true));
  const checkout = document.getElementById('Order');
  if (checkout) {
    const visible = () => {
      const rect = checkout.getBoundingClientRect();
      return rect.height > 0 && rect.top < window.innerHeight && rect.bottom > 0;
    };
    if ('IntersectionObserver' in window) {
      const observer = new IntersectionObserver(entries => {
        if (entries.some(entry => entry.isIntersecting)) record('checkout_view', true);
      }, { threshold: 0.1 });
      observer.observe(checkout);
    } else {
      const check = () => { if (visible()) record('checkout_view', true); };
      window.addEventListener('scroll', check, { passive: true });
      check();
    }
  }
})();
