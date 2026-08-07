document.addEventListener('DOMContentLoaded', () => {
  const rootEl = document.querySelector('[data-settings-root]');
  if (!rootEl) return;

  const fileListEl = rootEl.querySelector('[data-settings-file-list]');
  const searchInput = rootEl.querySelector('[data-settings-search]');
  const fileTitleEl = rootEl.querySelector('[data-settings-file-title]');
  const statusEl = rootEl.querySelector('[data-settings-status]');
  const saveBtn = rootEl.querySelector('[data-settings-save]');
  const editorEl = rootEl.querySelector('[data-settings-json-editor]');

  let files = [];
  let currentPath = '';
  let currentData = null;
  let editor = null;

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

  function escapeHtml(v) {
    return String(v ?? '').replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;');
  }

  function renderFileList(filter) {
    fileListEl.innerHTML = '';
    const settingsFiles = files.filter((f) => f.startsWith('settings/'));
    const contentFiles = files.filter((f) => !f.startsWith('settings/'));

    const buildGroup = (title, list) => {
      if (!list.length) return;
      const group = document.createElement('div');
      group.className = 'platform-settings-file-group';
      const heading = document.createElement('div');
      heading.className = 'platform-settings-file-group-title';
      heading.textContent = title;
      group.appendChild(heading);
      list.forEach((name) => {
        const item = document.createElement('div');
        item.className = 'platform-settings-file-item' + (name === currentPath ? ' is-active' : '');
        item.textContent = name;
        item.title = name;
        item.addEventListener('click', () => loadFile(name));
        group.appendChild(item);
      });
      fileListEl.appendChild(group);
    };

    if (filter) {
      const f = filter.toLowerCase();
      buildGroup('Matches', [...settingsFiles, ...contentFiles].filter((n) => n.toLowerCase().includes(f)));
      return;
    }
    buildGroup('Settings', settingsFiles);
    buildGroup('Content', contentFiles);
  }

  async function loadFiles() {
    try {
      const data = await apiJson('api/data-files.php');
      files = data.files || [];
      renderFileList(searchInput.value);
      if (files.length && !currentPath) loadFile(files[0]);
    } catch (e) {
      statusEl.textContent = 'Error loading files: ' + e.message;
    }
  }

  async function loadFile(name) {
    currentPath = name;
    renderFileList(searchInput.value);
    fileTitleEl.textContent = name;
    statusEl.textContent = 'Loading...';
    saveBtn.disabled = true;
    try {
      const data = await apiJson(`api/json-file.php?path=${encodeURIComponent(name)}`);
      currentData = data.content;
      editor = new JsonEditor(editorEl, currentData, {
        onChange: (val) => {
          currentData = val;
          saveBtn.disabled = false;
        },
      });
      statusEl.textContent = 'Loaded';
    } catch (e) {
      editorEl.innerHTML = '';
      statusEl.textContent = 'Error loading file: ' + e.message;
    }
  }

  async function saveFile() {
    saveBtn.disabled = true;
    statusEl.textContent = 'Saving...';
    try {
      const data = await apiJson('api/json-file.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ path: currentPath, content: currentData }),
      });
      statusEl.textContent = data.ok ? 'Saved' : 'Save failed';
    } catch (e) {
      statusEl.textContent = 'Save failed: ' + e.message;
      saveBtn.disabled = false;
    }
  }

  saveBtn.addEventListener('click', saveFile);
  searchInput.addEventListener('input', () => renderFileList(searchInput.value));

  loadFiles();
});
