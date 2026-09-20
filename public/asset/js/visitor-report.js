(() => {
  const status = document.getElementById('reportRefreshStatus');
  if (!status) return;
  let busy = false;
  const number = value => Number(value || 0).toLocaleString('en-US');
  const refresh = async () => {
    if (busy || document.visibilityState === 'hidden') return;
    busy = true;
    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 8000);
    try {
      const url = new URL(status.dataset.reportUrl, window.location.origin);
      url.searchParams.set('period', document.getElementById('activityPeriod')?.value || 'all');
      const response = await fetch(url, { headers: { Accept: 'application/json' }, cache: 'no-store', signal: controller.signal });
      if (!response.ok || response.redirected) throw new Error('Report unavailable');
      const report = await response.json();
      if (!report.eventCounts || !Array.isArray(report.dailyVisitors)) throw new Error('Invalid report');
      document.querySelectorAll('[data-visitor-metric]').forEach(node => { node.textContent = number(report[node.dataset.visitorMetric]); });
      document.querySelectorAll('[data-event-metric]').forEach(node => { node.textContent = number(report.eventCounts[node.dataset.eventMetric]); });
      const views = Number(report.eventCounts.page_view || 0);
      document.getElementById('conversionRate').textContent = (views ? Number(report.eventCounts.order_completed || 0) / views * 100 : 0).toFixed(1) + '%';
      const rows = document.getElementById('dailyVisitorRows');
      rows.replaceChildren();
      const max = Math.max(1, ...report.dailyVisitors.map(day => Number(day.visitors)));
      for (const day of report.dailyVisitors) {
        const row = document.createElement('tr');
        const date = new Date(day.visited_on);
        for (const text of [date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', timeZone: 'UTC' }), date.toLocaleDateString('en-US', { weekday: 'long', timeZone: 'UTC' }), number(day.visitors)]) {
          const cell = document.createElement('td'); cell.textContent = text; row.appendChild(cell);
        }
        const cell = document.createElement('td');
        const bar = document.createElement('div'); bar.className = 'visitor-bar';
        const fill = document.createElement('span'); fill.style.width = Math.max(4, Number(day.visitors) / max * 100) + '%';
        bar.appendChild(fill); cell.appendChild(bar); row.appendChild(cell); rows.appendChild(row);
      }
      if (!report.dailyVisitors.length) {
        const row = document.createElement('tr'); const cell = document.createElement('td');
        cell.colSpan = 4; cell.textContent = 'No visitor data recorded yet.'; row.appendChild(cell); rows.appendChild(row);
      }
      status.textContent = 'Last updated: ' + new Date().toLocaleTimeString() + ' — refreshing every 10 seconds.';
    } catch (_) {
      status.textContent = 'Report update failed. Check your connection and login; it will retry.';
    } finally { clearTimeout(timeout); busy = false; }
  };
  document.getElementById('activityPeriod')?.addEventListener('change', refresh);
  document.getElementById('activityReportForm')?.addEventListener('submit', event => { event.preventDefault(); refresh(); });
  document.addEventListener('visibilitychange', refresh);
  setInterval(refresh, 10000);
  refresh();
})();
