(() => {
  const form = document.getElementById('orderForm');
  const token = form?.elements.namedItem('incomplete_token');
  if (!form?.dataset.incompleteAction || !token) return;

  const storageKey = 'furniturePolishIncompleteCheckoutToken';
  const uuidPattern = /^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i;
  try {
    const saved = sessionStorage.getItem(storageKey);
    if (uuidPattern.test(saved || '')) token.value = saved;
    sessionStorage.setItem(storageKey, token.value);
  } catch (_) {}

  let timer;
  let paused = false;
  let completed = false;
  let lastSaved = '';
  let pending = Promise.resolve();
  const phoneNumber = value => {
    let phone = value.replace(/[০-৯]/g, digit => '০১২৩৪৫৬৭৮৯'.indexOf(digit)).replace(/\D/g, '');
    if (phone.length === 13 && phone.startsWith('88')) phone = phone.slice(2);
    if (phone.length === 10 && phone.startsWith('1')) phone = '0' + phone;
    return phone;
  };
  const snapshot = () => {
    const name = form.elements.namedItem('name').value.trim();
    const phone = phoneNumber(form.elements.namedItem('phone').value);
    if (name.length < 2 || !/^01[3-9][0-9]{8}$/.test(phone)) return null;
    return {
      token: token.value, name, phone,
      address: form.elements.namedItem('address')?.value || '',
      quantity: Number(form.elements.namedItem('quantity')?.value) || 1,
    };
  };
  const save = () => {
    clearTimeout(timer);
    if (paused || completed) return pending;
    const data = snapshot();
    if (!data) return pending;
    const serialized = JSON.stringify(data);
    pending = pending.catch(() => {}).then(async () => {
      if (paused || completed || serialized === lastSaved) return;
      try {
        const response = await fetch(form.dataset.incompleteAction, {
          method: 'POST', keepalive: true,
          headers: {
            'Content-Type': 'application/json', 'Accept': 'application/json',
            'X-CSRF-TOKEN': form.elements.namedItem('_token').value,
          },
          body: serialized,
        });
        if (response.ok) {
          const result = await response.json();
          if (result.saved) lastSaved = serialized;
          if (result.completed) window.incompleteCheckout.complete();
        }
      } catch (_) {
        // Retry on the next edit, blur, or page exit.
      }
    });
    return pending;
  };
  const begin = () => {
    completed = false;
    clearTimeout(timer);
    timer = setTimeout(save, 800);
  };
  form.addEventListener('input', begin);
  form.addEventListener('change', begin);
  document.getElementById('productPicker')?.addEventListener('change', begin);
  document.getElementById('productPicker')?.addEventListener('click', begin);
  form.addEventListener('focusout', () => { if (!completed) save(); });
  window.addEventListener('pagehide', save);
  document.addEventListener('visibilitychange', () => { if (document.visibilityState === 'hidden') save(); });

  window.incompleteCheckout = {
    begin,
    pause() { paused = true; clearTimeout(timer); },
    resume() { paused = false; if (!completed) save(); },
    complete() {
      completed = true;
      clearTimeout(timer);
      lastSaved = '';
      token.value = typeof crypto.randomUUID === 'function' ? crypto.randomUUID() : '10000000-1000-4000-8000-100000000000'.replace(/[018]/g, digit =>
        (Number(digit) ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> Number(digit) / 4).toString(16));
      try { sessionStorage.setItem(storageKey, token.value); } catch (_) {}
    },
  };
})();
