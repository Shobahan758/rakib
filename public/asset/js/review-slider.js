(() => {
  document.querySelectorAll('.review-slider').forEach(slider => {
    const settings = slider.dataset || {};
    const interval = Math.max(2, Math.min(60, Number(settings.interval) || 4)) * 1000;
    const pauseLabel = settings.pauseLabel || 'বিরতি';
    const resumeLabel = settings.resumeLabel || 'চালু করুন';
    const track = slider.querySelector('.review-track');
    if (!track || slider.dataset.initialized === 'true') return;
    slider.dataset.initialized = 'true';
    const slides = [...track.children];
    if (slides.length < 2) return;
    const controls = slider.querySelector('.review-controls');
    let loopCopies = [];
    const updateControls = () => {
      loopCopies.forEach(copy => copy.remove());
      loopCopies = [];
      // A full row still needs scroll space to autoplay (for example, three desktop reviews).
      // Only duplicate this small visible row, never the entire large collection.
      if (track.clientWidth > 0 && track.scrollWidth <= track.clientWidth + 2) {
        slides.slice(0, 3).forEach(slide => {
          const copy = slide.cloneNode(true);
          copy.setAttribute('aria-hidden', 'true');
          copy.inert = true;
          track.appendChild(copy);
          loopCopies.push(copy);
        });
      }
      controls.hidden = track.scrollWidth <= track.clientWidth + 2;
    };
    updateControls();
    window.addEventListener('resize', updateControls);
    const reducedMotion = matchMedia('(prefers-reduced-motion: reduce)');
    const pause = slider.querySelector('[data-review-pause]');
    let paused = reducedMotion.matches || settings.autoplay === '0';
    let hovered = false;
    let focused = false;
    const step = () => slides[1].offsetLeft - slides[0].offsetLeft;
    const advance = direction => {
      const size = step();
      const max = track.scrollWidth - track.clientWidth;
      if (max <= 2 || size <= 0) return;
      const next = direction > 0
        ? (track.scrollLeft >= max - 2 ? 0 : Math.min(max, track.scrollLeft + size))
        : (track.scrollLeft <= 2 ? max : Math.max(0, track.scrollLeft - size));
      track.scrollTo({ left: next, behavior: reducedMotion.matches ? 'instant' : 'smooth' });
    };
    const updatePause = () => {
      pause.textContent = paused ? resumeLabel : pauseLabel;
      pause.setAttribute('aria-label', paused ? resumeLabel : pauseLabel);
      pause.setAttribute('aria-pressed', String(paused));
    };
    pause.addEventListener('click', () => { paused = !paused; updatePause(); });
    reducedMotion.addEventListener('change', () => { paused = reducedMotion.matches || settings.autoplay === '0'; updatePause(); });
    slider.querySelector('[data-review-prev]').addEventListener('click', () => advance(-1));
    slider.querySelector('[data-review-next]').addEventListener('click', () => advance(1));
    slider.addEventListener('mouseenter', () => { hovered = true; });
    slider.addEventListener('mouseleave', () => { hovered = false; });
    slider.addEventListener('focusin', () => { focused = true; });
    slider.addEventListener('focusout', event => { focused = slider.contains(event.relatedTarget); });
    track.addEventListener('touchstart', () => { paused = true; updatePause(); }, {passive: true});
    setInterval(() => {
      const rect = slider.getBoundingClientRect();
      if (!paused && !hovered && !focused && !document.hidden && rect.bottom > 0 && rect.top < innerHeight) advance(1);
    }, interval);
    updatePause();
  });
})();
