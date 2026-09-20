(() => {
  document.querySelectorAll('.video-review-slider').forEach(slider => {
    const settings = slider.dataset || {};
    const interval = Math.max(2, Math.min(60, Number(settings.interval) || 5)) * 1000;
    const pauseLabel = settings.pauseLabel || 'বিরতি';
    const resumeLabel = settings.resumeLabel || 'চালু করুন';
    const track = slider.querySelector('.video-review-track');
    const slides = [...track.children];
    if (slides.length < 2) return;
    const controls = slider.querySelector('.video-review-controls');
    const toggle = slider.querySelector('[data-video-pause]');
    const reduced = matchMedia('(prefers-reduced-motion: reduce)');
    let paused = reduced.matches || settings.autoplay === '0';
    let hovered = false;
    let focused = false;
    const update = () => {
      toggle.textContent = paused ? resumeLabel : pauseLabel;
      toggle.setAttribute('aria-label', paused ? resumeLabel : pauseLabel);
      toggle.setAttribute('aria-pressed', String(paused));
    };
    const pause = () => { paused = true; update(); };
    const advance = direction => {
      const max = track.scrollWidth - track.clientWidth;
      if (max < 2) return;
      const step = slides[1].offsetLeft - slides[0].offsetLeft;
      const next = direction > 0
        ? (track.scrollLeft >= max - 2 ? 0 : Math.min(max, track.scrollLeft + step))
        : (track.scrollLeft <= 2 ? max : Math.max(0, track.scrollLeft - step));
      // A manually skipped video should not keep playing off-screen.
      track.querySelectorAll('video').forEach(video => video.pause());
      if (paused) track.querySelectorAll('iframe').forEach(frame => { frame.src = frame.src; });
      track.scrollTo({ left: next, behavior: reduced.matches ? 'instant' : 'smooth' });
    };
    const resize = () => { controls.hidden = track.scrollWidth <= track.clientWidth + 2; };
    slider.querySelector('[data-video-prev]').addEventListener('click', () => advance(-1));
    slider.querySelector('[data-video-next]').addEventListener('click', () => advance(1));
    toggle.addEventListener('click', () => { paused = !paused; update(); });
    slider.addEventListener('mouseenter', () => { hovered = true; });
    slider.addEventListener('mouseleave', () => { hovered = false; });
    slider.addEventListener('focusin', () => { focused = true; });
    slider.addEventListener('focusout', event => { focused = slider.contains(event.relatedTarget); });
    track.addEventListener('pointerdown', pause);
    track.addEventListener('touchstart', pause, { passive: true });
    track.querySelectorAll('video').forEach(video => video.addEventListener('play', pause));
    window.addEventListener('blur', () => {
      if (track.contains(document.activeElement)) pause();
    });
    reduced.addEventListener('change', () => { paused = reduced.matches || settings.autoplay === '0'; update(); });
    window.addEventListener('resize', resize);
    setInterval(() => {
      const rect = slider.getBoundingClientRect();
      if (!paused && !hovered && !focused && !document.hidden && rect.bottom > 0 && rect.top < innerHeight) advance(1);
    }, interval);
    resize();
    update();
  });
})();
