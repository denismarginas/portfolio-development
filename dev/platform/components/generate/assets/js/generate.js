document.addEventListener('DOMContentLoaded', () => {
  const rootEl = document.querySelector('[data-generate-root]');
  if (!rootEl) return;

  function resolveUrl(path) {
    const href = window.location.href;
    const qIdx = href.indexOf('?');
    const base = qIdx === -1 ? href : href.substring(0, qIdx);
    const lastSlash = base.lastIndexOf('/');
    const dir = lastSlash === -1 ? '' : base.substring(0, lastSlash + 1);
    return dir + path.replace(/^\//, '');
  }

  async function apiJson(path, options) {
    const res = await fetch(resolveUrl(path), Object.assign({ headers: { Accept: 'application/json' } }, options));
    const text = await res.text();
    let data = null;
    try {
      data = JSON.parse(text);
    } catch (e) {
      data = null;
    }
    if (!data) {
      const plain = text.replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim();
      throw new Error(plain ? plain.slice(0, 300) : `Invalid response (${res.status})`);
    }
    if (!res.ok) throw new Error(data.message || `Request failed (${res.status})`);
    return data;
  }

  /* ---------------------------------------------------------------- translate */

  const statusEl = rootEl.querySelector('[data-generate-status]');
  const resultsEl = rootEl.querySelector('[data-generate-results]');

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

  /* ---------------------------------------------------------------- thumbnails */

  const thumbsPanel = rootEl.querySelector('[data-generate-panel-thumbnails]');
  if (!thumbsPanel) return;

  const MISSING = '__missing__';
  const ALL = '__all__';
  const BIG = '__big__';
  const projectSelect = thumbsPanel.querySelector('[data-thumbs-project]');
  const thumbsStatus = thumbsPanel.querySelector('[data-thumbs-status]');
  const thumbsResults = thumbsPanel.querySelector('[data-thumbs-results]');
  const warningsEl = thumbsPanel.querySelector('[data-thumbs-warnings]');
  const previewBtn = thumbsPanel.querySelector('[data-thumbs-action="preview"]');
  const generateBtn = thumbsPanel.querySelector('[data-thumbs-action="generate"]');
  const stopBtn = thumbsPanel.querySelector('[data-thumbs-action="stop"]');

  let state = { projects: [], settings: { variants: ['default'], skip_existing: true } };
  let stopRequested = false;

  function projectLabel(p) {
    const mark = p.has_thumbnail ? ' ✓' : '';
    const kinds = [p.has_web ? 'web' : '', p.has_media ? 'media' : ''].filter(Boolean).join('+') || 'no images';
    return `${p.path}${mark} (${kinds})`;
  }

  function missingProjects() {
    return state.projects.filter((p) => !p.has_thumbnail);
  }

  // projects with a generated thumbnail heavier than the "overwrite_bigger_than_kb" param
  function bigProjects() {
    const s = state.settings || {};
    if (!s.overwrite_if_bigger) return [];
    return state.projects.filter((p) => Object.values(p.generated || {}).some((kb) => kb !== false && kb > s.overwrite_bigger_than));
  }

  async function loadThumbs() {
    thumbsStatus.textContent = 'Loading projects...';
    try {
      const data = await apiJson('api/generate-thumbnails.php');
      state = data;
      warningsEl.innerHTML = '';
      (data.requirements || []).forEach((msg) => {
        const w = document.createElement('div');
        w.className = 'platform-generate-result-item is-error';
        w.textContent = msg;
        warningsEl.appendChild(w);
      });

      const selected = projectSelect.value;
      projectSelect.innerHTML = '';
      const add = (value, text) => {
        const opt = document.createElement('option');
        opt.value = value;
        opt.textContent = text;
        projectSelect.appendChild(opt);
      };
      add(MISSING, `All projects without a thumbnail (${missingProjects().length})`);
      if (data.settings.overwrite_if_bigger) {
        add(BIG, `Generated thumbnails over ${data.settings.overwrite_bigger_than} KB (${bigProjects().length})`);
      }
      add(ALL, `All render:true projects (${data.projects.length})`);
      data.projects.forEach((p) => add(p.path, projectLabel(p)));
      if (selected && [...projectSelect.options].some((o) => o.value === selected)) projectSelect.value = selected;

      thumbsStatus.textContent = `${data.projects.length} projects, ${missingProjects().length} without a thumbnail`;
      updateButtons();
    } catch (e) {
      thumbsStatus.textContent = 'Error loading projects: ' + e.message;
    }
  }

  // one thumbnail.webp per project
  function chosenVariants() {
    return ['default'];
  }

  function chosenProjects() {
    const v = projectSelect.value;
    if (v === MISSING) return missingProjects();
    if (v === BIG) return bigProjects();
    if (v === ALL) {
      const big = bigProjects();
      return state.projects.filter((p) => !(state.settings.skip_existing && p.has_thumbnail) || big.includes(p));
    }
    return state.projects.filter((p) => p.path === v);
  }

  function updateButtons() {
    const single = ![MISSING, ALL, BIG].includes(projectSelect.value);
    previewBtn.disabled = !single;
  }

  function addResult(r, imgSrc) {
    const item = document.createElement('figure');
    const ok = r.success && !r.skipped;
    item.className = 'platform-generate-thumb' + (r.success ? '' : (r.skipped ? ' is-skipped' : ' is-error'));
    if (imgSrc) {
      const img = document.createElement('img');
      img.src = imgSrc;
      img.alt = r.path;
      img.loading = 'lazy';
      item.appendChild(img);
    }
    const cap = document.createElement('figcaption');
    const title = document.createElement('strong');
    title.textContent = r.path;
    const info = document.createElement('span');
    info.textContent = ok
      ? (r.file || '') + (r.bytes ? ` · ${(r.bytes / 1024).toFixed(1)} KB` : '') + (r.replaced_kb ? ` (was ${r.replaced_kb} KB)` : '')
      : (r.message || 'error');
    cap.appendChild(title);
    cap.appendChild(info);
    if (r.sources) {
      const src = document.createElement('span');
      src.className = 'platform-generate-thumb-sources';
      src.textContent = Object.entries(r.sources).map(([k, f]) => `${k}: ${String(f).split('/').slice(-2).join('/')}`).join(' · ');
      cap.appendChild(src);
    }
    item.appendChild(cap);
    thumbsResults.prepend(item);
  }

  function setRunning(running) {
    stopBtn.style.display = running ? '' : 'none';
    generateBtn.disabled = running;
    previewBtn.disabled = running || [MISSING, ALL, BIG].includes(projectSelect.value);
    projectSelect.disabled = running;
  }

  async function runPreview() {
    const projects = chosenProjects();
    if (projects.length !== 1) return;
    setRunning(true);
    try {
      for (const variant of chosenVariants()) {
        thumbsStatus.textContent = `Preview ${projects[0].path}...`;
        const r = await apiJson('api/generate-thumbnails.php', {
          method: 'POST',
          body: JSON.stringify({ path: projects[0].path, variant, preview: true }),
        });
        addResult(r, r.data_uri || '');
      }
      thumbsStatus.textContent = 'Preview ready (nothing was saved)';
    } catch (e) {
      thumbsStatus.textContent = 'Preview failed: ' + e.message;
    } finally {
      setRunning(false);
    }
  }

  async function runGenerate() {
    const projects = chosenProjects();
    const variants = chosenVariants();
    const jobs = [];
    projects.forEach((p) => variants.forEach((variant) => jobs.push({ path: p.path, variant })));
    if (!jobs.length) {
      thumbsStatus.textContent = 'Nothing to generate.';
      return;
    }

    stopRequested = false;
    setRunning(true);
    let done = 0;
    let saved = 0;
    let skipped = 0;
    let errors = 0;
    for (const job of jobs) {
      if (stopRequested) break;
      thumbsStatus.textContent = `Generating ${done + 1}/${jobs.length} · ${job.path}`;
      try {
        const r = await apiJson('api/generate-thumbnails.php', { method: 'POST', body: JSON.stringify(job) });
        if (r.skipped) skipped++;
        else if (r.success) saved++;
        else errors++;
        const src = r.success && !r.skipped && r.file
          ? resolveUrl(`api/serve-file.php?path=${encodeURIComponent(r.file)}&v=${Date.now()}`)
          : '';
        addResult(r, src);
      } catch (e) {
        errors++;
        addResult({ path: job.path, variant: job.variant, success: false, message: e.message }, '');
      }
      done++;
    }
    thumbsStatus.textContent = `${stopRequested ? 'Stopped' : 'Done'}: ${saved} saved, ${skipped} skipped, ${errors} errors (${done}/${jobs.length})`;
    setRunning(false);
    loadThumbs();
  }

  projectSelect.addEventListener('change', updateButtons);
  previewBtn.addEventListener('click', runPreview);
  generateBtn.addEventListener('click', runGenerate);
  stopBtn.addEventListener('click', () => {
    stopRequested = true;
    thumbsStatus.textContent = 'Stopping after the current image...';
  });

  if (rootEl.dataset.generateTab === 'thumbnails') loadThumbs();
});
