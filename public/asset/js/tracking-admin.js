(() => {
  const button = document.getElementById('trackingCheck');
  const result = document.getElementById('trackingCheckResult');
  if (!button || !result) return;

  button.addEventListener('click', async () => {
    button.disabled = true;
    result.hidden = false;
    result.className = 'tracking-check-result';
    result.textContent = 'সেটআপ যাচাই করা হচ্ছে...';

    try {
      const response = await fetch(button.dataset.checkUrl, {
        headers: { Accept: 'application/json' },
        cache: 'no-store',
        credentials: 'same-origin',
      });
      if (!response.ok || response.redirected) throw new Error('সেটআপ যাচাই করা যায়নি। আবার লগইন করে চেষ্টা করুন।');
      const report = await response.json();
      result.textContent = report.message;
      result.classList.toggle('ready', report.ready === true);
    } catch (error) {
      result.textContent = error.message || 'সেটআপ যাচাই করা যায়নি। আবার চেষ্টা করুন।';
    } finally {
      button.disabled = false;
    }
  });
})();
