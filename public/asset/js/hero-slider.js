(() => {
  document.querySelectorAll('[data-hero-slider]').forEach(slider => {
    const track = slider.querySelector('.hero-slider-track');
    const slides = [...slider.querySelectorAll('.hero-slide')];
    const dots = [...slider.querySelectorAll('.hero-slider-dots button')];
    if (!track || slides.length < 2) return;

    const interval = Math.max(2, Number(slider.dataset.interval) || 4) * 1000;
    const autoplay = slider.dataset.autoplay !== '0';
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    let current = 0;
    let startX = null;

    const show = index => {
      current = (index + slides.length) % slides.length;
      track.style.transform = `translateX(-${current * 100}%)`;
      slides.forEach((slide, i) => slide.setAttribute('aria-hidden', String(i !== current)));
      dots.forEach((dot, i) => {
        dot.classList.toggle('active', i === current);
        if (i === current) dot.setAttribute('aria-current', 'true');
        else dot.removeAttribute('aria-current');
      });
    };

    dots.forEach((dot, index) => dot.addEventListener('click', () => show(index)));
    slider.addEventListener('touchstart', event => { startX = event.touches[0]?.clientX ?? null; }, { passive: true });
    slider.addEventListener('touchend', event => {
      if (startX === null) return;
      const distance = (event.changedTouches[0]?.clientX ?? startX) - startX;
      if (Math.abs(distance) > 45) show(current + (distance < 0 ? 1 : -1));
      startX = null;
    }, { passive: true });

    setInterval(() => {
      const rect = slider.getBoundingClientRect();
      if (autoplay && !reducedMotion.matches && !document.hidden && rect.bottom > 0 && rect.top < innerHeight) {
        show(current + 1);
      }
    }, interval);
  });
})();
