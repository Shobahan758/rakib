(() => {
  const bar = document.querySelector('.mobile-order-bar');
  const orderSection = document.getElementById('Order');
  if (!bar || !orderSection) return;

  const setHidden = hidden => bar.classList.toggle('is-checkout-hidden', hidden);
  bar.querySelector('.mobile-order-cta')?.addEventListener('click', () => setHidden(true));

  if (!('IntersectionObserver' in window)) return;
  const observer = new IntersectionObserver(entries => {
    const orderEntry = entries.find(entry => entry.target === orderSection);
    if (orderEntry) setHidden(orderEntry.isIntersecting);
  }, { threshold: 0.01 });
  observer.observe(orderSection);
})();
