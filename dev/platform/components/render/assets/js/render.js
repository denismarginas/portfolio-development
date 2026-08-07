document.addEventListener('DOMContentLoaded', () => {
  const rootEl = document.querySelector('[data-render-root]');
  if (!rootEl) return;

  const statusEl = rootEl.querySelector('[data-render-status]');
  const resultsEl = rootEl.querySelector('[data-render-scss-results]');
  const htmlStatusEl = rootEl.querySelector('[data-render-html-status]');
  const htmlResultsEl = rootEl.querySelector('[data-render-html-results]');

  let previewDefaults = {};
  try {
    previewDefaults = JSON.parse(rootEl.getAttribute('data-render-preview-defaults') || '{}');
  } catch (e) {
    previewDefaults = {};
  }

  const postTypeSelect = rootEl.querySelector('[data-render-post-type]');
  const postSelect = rootEl.querySelector('[data-render-post]');
  const previewFrame = rootEl.querySelector('[data-render-preview]');
  const openLink = rootEl.querySelector('[data-render-open]');

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
    let data = {};
    try {
      data = await res.json();
    } catch (e) {
      data = {};
    }
    if (!res.ok) throw new Error(data.message || `Request failed (${res.status})`);
    return data;
  }

  async function runCompile() {
    statusEl.textContent = 'Compiling...';
    resultsEl.innerHTML = '';
    try {
      const data = await apiJson('api/compile-scss.php');
      statusEl.textContent = `SCSS: ${data.success_count} OK, ${data.error_count} errors (${data.total} files)`;
      (data.results || []).forEach((r) => {
        const item = document.createElement('div');
        item.className = 'platform-render-result-item';
        const file = document.createElement('span');
        file.className = 'platform-render-result-file';
        file.textContent = r.file || r.output || 'component';
        const state = document.createElement('span');
        state.className = r.success ? 'platform-render-result-ok' : 'platform-render-result-err';
        state.textContent = r.success ? `OK · ${r.bytes || 0} B` : (r.message || 'error');
        item.appendChild(file);
        item.appendChild(state);
        resultsEl.appendChild(item);
      });
    } catch (e) {
      statusEl.textContent = 'Compile failed: ' + e.message;
    }
  }

  function setPreview() {
    const postId = postSelect.value;
    if (!postId) return;
    const url = `preview/?post_id=${encodeURIComponent(postId)}`;
    previewFrame.src = resolveUrl(url);
    openLink.href = resolveUrl(url);
  }

  async function runCompileHtml() {
    if (!htmlStatusEl || !htmlResultsEl) return;
    htmlStatusEl.textContent = 'Rendering...';
    htmlResultsEl.innerHTML = '';
    try {
      const data = await apiJson('api/compile-html.php', { method: 'POST' });
      if (!data.html_compile) {
        htmlStatusEl.textContent = 'html_compile flag is off (Render card params).';
        return;
      }
      htmlStatusEl.textContent = `${data.count} files rendered`;
      (data.results || []).forEach((r) => {
        const item = document.createElement('div');
        item.className = 'platform-render-result-item';
        const file = document.createElement('span');
        file.className = 'platform-render-result-file';
        file.textContent = `${r.post_type}/${r.post_id}`;
        const state = document.createElement('span');
        state.className = r.success ? 'platform-render-result-ok' : 'platform-render-result-err';
        state.textContent = r.success ? 'OK' : (r.error || 'error');
        item.appendChild(file);
        item.appendChild(state);
        htmlResultsEl.appendChild(item);
      });
    } catch (e) {
      htmlStatusEl.textContent = 'Render failed: ' + e.message;
    }
  }

  async function loadTypes() {
    try {
      const data = await apiJson('api/posts.php');
      const types = data.types || [];
      postTypeSelect.innerHTML = '';
      if (!types.length) {
        postTypeSelect.innerHTML = '<option value="">No post types</option>';
        postSelect.innerHTML = '';
        return;
      }
      let defaultType = previewDefaults.post_type || '';
      let selected = types.find((t) => t.post_type === defaultType) ? defaultType : types[0].post_type;
      types.forEach((t) => {
        const opt = document.createElement('option');
        opt.value = t.post_type;
        opt.textContent = `${t.title} (${t.count})`;
        if (t.post_type === selected) opt.selected = true;
        postTypeSelect.appendChild(opt);
      });
      loadPosts();
    } catch (e) {
      statusEl.textContent = 'Error loading post types: ' + e.message;
    }
  }

  async function loadPosts() {
    const postType = postTypeSelect.value;
    if (!postType) return;
    try {
      const data = await apiJson(`api/posts.php?post_type=${encodeURIComponent(postType)}`);
      postSelect.innerHTML = '';
      let defaultPost = previewDefaults.post_id || '';
      (data.posts || []).forEach((p) => {
        const opt = document.createElement('option');
        opt.value = p.post_id;
        opt.textContent = p.title || p.post_id;
        if (p.post_id === defaultPost) opt.selected = true;
        postSelect.appendChild(opt);
      });
      if (defaultPost && !(data.posts || []).some((p) => p.post_id === defaultPost)) {
        postSelect.selectedIndex = 0;
      }
      setPreview();
    } catch (e) {
      statusEl.textContent = 'Error loading posts: ' + e.message;
    }
  }

  const compileBtn = rootEl.querySelector('[data-render-action="compile-scss"]');
  if (compileBtn) compileBtn.addEventListener('click', runCompile);

  const compileHtmlBtn = rootEl.querySelector('[data-render-action="compile-html"]');
  if (compileHtmlBtn) compileHtmlBtn.addEventListener('click', runCompileHtml);

  if (postTypeSelect) postTypeSelect.addEventListener('change', loadPosts);
  if (postSelect) postSelect.addEventListener('change', setPreview);

  if (rootEl.dataset.renderTab === 'preview') loadTypes();
});
