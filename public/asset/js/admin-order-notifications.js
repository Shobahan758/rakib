(() => {
  const center = document.getElementById('adminOrderNotifications');
  if (!center) return;

  const endpoint = center.dataset.endpoint;
  const storageKey = `adminLatestOrderId:${center.dataset.userId}`;
  let latestId = 0;
  let polling = false;
  let audioContext = null;

  try { latestId = Number(sessionStorage.getItem(storageKey)) || 0; } catch (_) {}

  const unlockSound = () => {
    try {
      const AudioContext = window.AudioContext || window.webkitAudioContext;
      if (!AudioContext) return;
      audioContext ||= new AudioContext();
      if (audioContext.state === 'suspended') audioContext.resume();
    } catch (_) {}
  };

  document.addEventListener('pointerdown', unlockSound, { once: true });
  document.addEventListener('keydown', unlockSound, { once: true });

  const playSound = () => {
    unlockSound();
    if (!audioContext || audioContext.state !== 'running') return;

    const startedAt = audioContext.currentTime;
    [880, 1175].forEach((frequency, index) => {
      const oscillator = audioContext.createOscillator();
      const gain = audioContext.createGain();
      const start = startedAt + (index * .16);
      oscillator.type = 'sine';
      oscillator.frequency.value = frequency;
      gain.gain.setValueAtTime(.0001, start);
      gain.gain.exponentialRampToValueAtTime(.18, start + .02);
      gain.gain.exponentialRampToValueAtTime(.0001, start + .14);
      oscillator.connect(gain);
      gain.connect(audioContext.destination);
      oscillator.start(start);
      oscillator.stop(start + .15);
    });
  };

  const money = value => new Intl.NumberFormat('bn-BD').format(Number(value) || 0);
  const escapeHtml = value => String(value ?? '').replace(/[&<>'"]/g, character => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;'
  })[character]);

  const showOrder = order => {
    const toast = document.createElement('article');
    toast.className = 'admin-order-toast';
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
      <div class="admin-order-toast__icon"><i class="fa-solid fa-bag-shopping"></i></div>
      <div class="admin-order-toast__copy">
        <strong>নতুন অর্ডার এসেছে! <span>#${Number(order.id) || 0}</span></strong>
        <p>${escapeHtml(order.name)} · ${escapeHtml(order.phone)}</p>
        <b>৳${money(order.total)}</b>
        <a href="${escapeHtml(order.url)}">অর্ডারটি দেখুন</a>
      </div>
      <button type="button" aria-label="Notification বন্ধ করুন">×</button>`;
    const dismiss = () => {
      toast.classList.add('is-leaving');
      setTimeout(() => toast.remove(), 220);
    };
    toast.querySelector('button').addEventListener('click', dismiss);
    center.prepend(toast);
    setTimeout(dismiss, 12000);
  };

  const remember = id => {
    latestId = Math.max(latestId, Number(id) || 0);
    try { sessionStorage.setItem(storageKey, String(latestId)); } catch (_) {}
  };

  const poll = async () => {
    if (polling) return;
    polling = true;
    try {
      const response = await fetch(`${endpoint}?after=${latestId}`, {
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
        cache: 'no-store'
      });
      if (!response.ok) return;
      const data = await response.json();
      const orders = Array.isArray(data.orders) ? data.orders : [];
      if (orders.length) {
        playSound();
        orders.forEach(showOrder);
      }
      remember(data.latest_id);
    } catch (_) {
      // A temporary network failure should not interrupt the admin page.
    } finally {
      polling = false;
    }
  };

  poll();
  setInterval(poll, 8000);
  document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible') poll();
  });
})();
