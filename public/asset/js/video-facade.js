(() => {
  document.querySelectorAll('[data-video-facade]').forEach(button => {
    button.addEventListener('click', () => {
      const source = button.dataset.src;
      if (!source) return;

      const iframe = document.createElement('iframe');
      iframe.src = source;
      iframe.title = button.getAttribute('aria-label')?.replace(' চালু করুন', '') || 'ভিডিও';
      iframe.referrerPolicy = 'strict-origin-when-cross-origin';
      iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
      iframe.allowFullscreen = true;
      iframe.dataset.videoPlayer = '';
      button.replaceWith(iframe);
    }, { once: true });
  });
})();
