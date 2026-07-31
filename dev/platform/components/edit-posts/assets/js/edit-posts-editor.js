async function loadPost(postId) {
  setStatus(tr('editPosts.loadingPostTypes', 'Loading post...'));
  const data = await apiFetch(`${apiUrl}?post_id=${encodeURIComponent(postId)}`);
  currentPost = data.post;
  currentFile = data.file;
  originalPostId = currentPost.post_id;
  renderEditor();
  editorEl.style.display = 'flex';
  setStatus(tr('editPosts.postLoaded', 'Post loaded'));
}

function renderEditor() {
  if (!currentPost) return;
  const jsonToggle = epRoot.querySelector('[data-ep-toggle-json]');
  if (jsonToggle) jsonToggle.checked = false;
  const jsonEditorEl = epRoot.querySelector('[data-ep-json-editor]');
  if (jsonEditorEl) jsonEditorEl.style.display = 'none';
  jsonEditorInstance = null;
  if (fieldsContainer) fieldsContainer.style.display = '';

  postTitleEl.textContent = currentPost.data?.title || currentPost.title || '(untitled)';

  postIdEl.innerHTML = '';
  const idInput = document.createElement('input');
  idInput.className = 'platform-input';
  idInput.style.cssText = 'width:auto;font-size:11px;padding:2px 6px;min-width:180px';
  idInput.value = currentPost.post_id;
  idInput.addEventListener('change', () => {
    currentPost.post_id = idInput.value;
    updateViewButton();
    markDirty();
  });
  postIdEl.appendChild(idInput);

  updateViewButton();
  const viewBtn = epRoot.querySelector('[data-ep-action="view"]');
  if (viewBtn) viewBtn.style.display = 'inline-flex';

  renderFields();
  renderContent();
}

function updateViewButton() {
  const viewBtn = epRoot.querySelector('[data-ep-action="view"]');
  if (viewBtn && currentPost) {
    viewBtn.href = resolveApiUrl('/preview/?post_id=' + encodeURIComponent(currentPost.post_id));
  }
}

function renderFields() {
  fieldsContainer.innerHTML = '';
  const post = currentPost;
  const dataObj = post.data || {};

  const generalFields = ['title', 'description'];
  addFieldGroup('General', generalFields, dataObj, post);

  const taxonomyObj = dataObj.taxonomy || {};
  const taxFields = Object.keys(taxonomyObj).length ? Object.keys(taxonomyObj) : [];
  if (taxFields.length) {
    addFieldGroup('Taxonomy', taxFields, dataObj, post, 'taxonomy');
  }

  if (dataObj.date) {
    addDateFields(dataObj.date);
  }

  if (dataObj.project) {
    addFieldGroup('Project', Object.keys(dataObj.project), dataObj, post, 'project');
  }

  if (dataObj.web) {
    addFieldGroup('Web', Object.keys(dataObj.web), dataObj, post, 'web');
  }

  if (dataObj.media) {
    addFieldGroup('Media', Object.keys(dataObj.media), dataObj, post, 'media');
  }

  const seoObj = post.data?.seo || post.seo;
  if (seoObj) {
    addFieldGroup('SEO', Object.keys(seoObj), null, post, 'seo');
  }
}

function getFieldVal(key, dataObj, post, prefix) {
  if (prefix) {
    if (prefix === 'seo') return post.data?.seo?.[key] ?? post.seo?.[key] ?? '';
    if (typeof dataObj[prefix] === 'object' && dataObj[prefix] !== null) return dataObj[prefix][key] ?? '';
    return '';
  }
  if (dataObj) return dataObj[key] ?? '';
  return '';
}

function makeFieldRow(key, val, dataObj, post, prefix) {
  const label = document.createElement('label');
  label.className = 'platform-form-label';
  label.textContent = key.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());

  const isComplex = Array.isArray(val) ? val.some(v => typeof v === 'object' && v !== null) : (typeof val === 'object' && val !== null);
  const isSimpleArray = Array.isArray(val) && !isComplex;

  let input;
  let displayVal;

  if (isComplex) {
    input = document.createElement('textarea');
    input.className = 'platform-input';
    input.style.width = '100%';
    input.style.fontFamily = 'monospace';
    input.style.fontSize = '11px';
    displayVal = JSON.stringify(val, null, 2);
  } else if (isSimpleArray) {
    input = document.createElement('input');
    input.className = 'platform-input';
    input.style.width = '100%';
    displayVal = val.join(', ');
  } else {
    input = document.createElement('input');
    input.className = 'platform-input';
    input.style.width = '100%';
    displayVal = val;
  }

  input.value = displayVal;
  input.dataset.fieldKey = key;
  if (prefix) input.dataset.fieldPrefix = prefix;

  input.addEventListener('change', () => {
    const newVal = input.value;
    const prefix2 = input.dataset.fieldPrefix;

    if (isComplex) {
      try { currentPost = setFieldVal(key, JSON.parse(newVal), dataObj, post, prefix2); }
      catch (e) { setStatus(tr('editPosts.invalidJson', 'Invalid JSON: {message}', { message: e.message })); return; }
    } else if (isSimpleArray) {
      currentPost = setFieldVal(key, newVal.split(',').map(s => s.trim()).filter(Boolean), dataObj, post, prefix2);
    } else {
      currentPost = setFieldVal(key, newVal, dataObj, post, prefix2);
    }
    markDirty();
  });

  const row = document.createElement('div');
  row.className = 'platform-form-row';
  row.appendChild(label);
  row.appendChild(input);
  return row;
}

function setFieldVal(key, val, dataObj, post, prefix) {
  const p = { ...post };
  if (prefix === 'seo') {
    if (!p.data) p.data = {};
    if (!p.data.seo) p.data.seo = {};
    p.data = { ...p.data, seo: { ...p.data.seo, [key]: val } };
  } else if (prefix) {
    if (!p.data) p.data = {};
    p.data = { ...p.data };
    if (!p.data[prefix]) p.data[prefix] = {};
    p.data[prefix] = { ...p.data[prefix], [key]: val };
  } else if (p.data) {
    p.data = { ...p.data, [key]: val };
  } else {
    p[key] = val;
  }
  return p;
}

function addFieldGroup(title, keys, dataObj, post, prefix) {
  const group = document.createElement('div');
  group.className = 'ep-field-group';
  group.innerHTML = `<div class="ep-field-group-title">${escapeHtml(title)}</div>`;

  keys.forEach(key => {
    const val = getFieldVal(key, dataObj, post, prefix);
    const row = makeFieldRow(key, val, dataObj, post, prefix);
    group.appendChild(row);
  });

  fieldsContainer.appendChild(group);
}

function addDateFields(dateObj) {
  const group = document.createElement('div');
  group.className = 'ep-field-group';
  group.innerHTML = `<div class="ep-field-group-title">Date</div>`;

  Object.keys(dateObj).forEach(key => {
    const label = document.createElement('label');
    label.className = 'platform-form-label';
    label.textContent = key.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());

    const input = document.createElement('input');
    input.className = 'platform-input';
    input.style.width = '100%';
    input.value = dateObj[key] ?? '';
    input.dataset.fieldKey = key;
    input.dataset.fieldPrefix = 'date';

    input.addEventListener('change', () => {
      currentPost.data.date[input.dataset.fieldKey] = input.value;
      markDirty();
    });

    const row = document.createElement('div');
    row.className = 'platform-form-row';
    row.appendChild(label);
    row.appendChild(input);
    group.appendChild(row);
  });

  fieldsContainer.appendChild(group);
}

function duplicatePost() {
  if (!currentPost) return;
  const clone = JSON.parse(JSON.stringify(currentPost));
  clone.post_id = currentPost.post_id + '_copy';
  posts.push(clone);
  renderPostList();
  loadPost(clone.post_id);
  setStatus(tr('editPosts.duplicated', 'Duplicated as: {id}', { id: clone.post_id }));
}
