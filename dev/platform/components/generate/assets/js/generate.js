document.addEventListener('DOMContentLoaded', () => {
  const rootEl = document.querySelector('[data-generate-root]');
  if (!rootEl) return;

  const statusEl = rootEl.querySelector('[data-generate-status]');
  const resultsEl = rootEl.querySelector('[data-generate-results]');

  function resolveUrl(path) {
    const href = window.location.href;
    const qIdx = href.indexOf('?');
    const base = qIdx === -1 ? href : href.substring(0, qIdx);
    const lastSlash = base.lastIndexOf('/');
    const dir = lastSlash === -1 ? '' : base.substring(0, lastSlash + 1);
    return dir + path.replace(/^\//, '');
  }

  async function runTranslate() {
    statusEl.textContent = 'Translating...';
    resultsEl.innerHTML = '';
    try {
      const res = await fetch(resolveUrl('api/translate-data.php'), {
        method: 'POST',
        headers: { Accept: 'application/json' },
      });
      const data = await res.json();
      const langs = (data.languages || []).map((lang) => lang.iso).join(', ');
      if (data.error_count > 0) {
        statusEl.textContent = `Translation: ${data.success_count} OK, ${data.error_count} errors (${langs})`;
      } else {
        statusEl.textContent = `Translation: ${data.total} files OK (${langs})`;
      }
      (data.results || []).forEach((r) => {
        const item = document.createElement('div');
        item.className = 'platform-generate-result-item' + (String(r).indexOf('(invalid)') !== -1 ? ' is-error' : '');
        item.textContent = r;
        resultsEl.appendChild(item);
      });
    } catch (e) {
      statusEl.textContent = 'Translation failed: ' + e.message;
    }
  }

  rootEl.querySelectorAll('[data-generate-action]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const action = btn.dataset.generateAction;
      if (action === 'translate') runTranslate();
    });
  });
});
