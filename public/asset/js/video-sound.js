(() => {
  const enableSound = container => {
    const player = container.querySelector('[data-video-player]');
    const button = container.querySelector('[data-video-sound]');
    if (!player || !button) return;

    if (container.dataset.playerType === 'embed') {
      const command = func => player.contentWindow?.postMessage(JSON.stringify({
        event: 'command',
        func,
        args: func === 'setVolume' ? [100] : [],
      }), 'https://www.youtube-nocookie.com');

      command('unMute');
      command('setVolume');
      command('playVideo');
    } else {
      player.muted = false;
      player.volume = 1;
      player.play().catch(() => {});
    }

    const enabledLabel = container.dataset.soundEnabledLabel || 'সাউন্ড চালু হয়েছে';
    button.innerHTML = `<span aria-hidden="true">🔊</span> ${enabledLabel}`;
    button.setAttribute('aria-label', enabledLabel);
    button.classList.add('sound-enabled');
  };

  document.querySelectorAll('[data-autoplay-video]').forEach(container => {
    container.querySelector('[data-video-sound]')?.addEventListener('click', () => enableSound(container));
  });
})();
