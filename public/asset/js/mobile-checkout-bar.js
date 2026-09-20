(() => {
  const bar = document.querySelector('.mobile-order-bar');
  const orderSection = document.getElementById('Order');
  if (!bar || !orderSection) return;

  const setHidden = hidden => bar.classList.toggle('is-checkout-hidden', hidden);
  bar.querySelector('.mobile-order-cta')?.addEventListener('click', () => setHidden(true));

  const syncWithCheckout = () => {
    const rect = orderSection.getBoundingClientRect();
    setHidden(rect.top < window.innerHeight && rect.bottom > 0);
  };

  orderSection.addEventListener('focusin', () => setHidden(true));
  orderSection.addEventListener('pointerdown', () => setHidden(true));
  window.addEventListener('scroll', syncWithCheckout, { passive: true });
  window.addEventListener('resize', syncWithCheckout);

  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver(entries => {
      const orderEntry = entries.find(entry => entry.target === orderSection);
      if (orderEntry) setHidden(orderEntry.isIntersecting);
    }, { threshold: 0.01 });
    observer.observe(orderSection);
  }

  syncWithCheckout();
})();
