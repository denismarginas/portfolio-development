function renderContent() {
  contentListEl.innerHTML = '';
  const items = currentPost.content || [];

  if (!items.length) {
    contentListEl.innerHTML = '<div class="ep-content-empty">' + escapeHtml(tr('editPosts.noContent', 'No content items. Click "Add Component" to add one.')) + '</div>';
    return;
  }

  items.forEach((item, i) => {
    const el = createContentItemEl(item, i, items, 0);
    contentListEl.appendChild(el);
  });
}

function createContentItemEl(item, index, parentArray, level) {
  const el = document.createElement('div');
  el.className = 'ep-content-item' + (level > 0 ? ' ep-content-item-nested' : '');
  el.dataset.contentIndex = index;

  const isTopLevel = level === 0;
  if (isTopLevel) el.draggable = true;

  const compName = item.component || 'unknown';
  const header = document.createElement('div');
  header.className = 'ep-content-item-header';
  header.innerHTML = `
    ${isTopLevel ? '<span class="ep-content-item-drag">&#9776;</span>' : '<span class="ep-content-item-drag" style="visibility:hidden">&#9776;</span>'}
    <span class="ep-content-item-name">${escapeHtml(compName)}</span>
    <div class="ep-content-item-actions">
      <button class="platform-button platform-button-sm platform-button-ghost" data-ep-action="remove-content" style="color:var(--platform-danger)">&#10005;</button>
    </div>
  `;

  const body = document.createElement('div');
  body.className = 'ep-content-item-body';

  const dataFields = item.data || {};
  const dataKeys = Object.keys(dataFields);
  const children = item.children || [];

  dataKeys.forEach(k => {
    const row = createDataFieldRow(k, dataFields[k], item);
    body.appendChild(row);
  });

  const addFieldRow = document.createElement('div');
  addFieldRow.className = 'ep-add-field-row';

  const addFieldBtn = document.createElement('button');
  addFieldBtn.className = 'platform-button platform-button-sm platform-button-ghost';
  addFieldBtn.textContent = '+ Add Field';
  addFieldRow.appendChild(addFieldBtn);

  const fieldKeyInput = document.createElement('input');
  fieldKeyInput.className = 'platform-input';
  fieldKeyInput.placeholder = 'key';
  fieldKeyInput.style.cssText = 'display:none;width:100px';

  const fieldValInput = document.createElement('input');
  fieldValInput.className = 'platform-input';
  fieldValInput.placeholder = 'value';
  fieldValInput.style.cssText = 'display:none;flex:1';

  const confirmBtn = document.createElement('button');
  confirmBtn.className = 'platform-button platform-button-sm platform-button-primary';
  confirmBtn.textContent = 'Add';
  confirmBtn.style.cssText = 'display:none';

  addFieldBtn.addEventListener('click', () => {
    addFieldBtn.style.display = 'none';
    fieldKeyInput.style.display = '';
    fieldValInput.style.display = '';
    confirmBtn.style.display = '';
    fieldKeyInput.value = '';
    fieldValInput.value = '';
    fieldKeyInput.focus();
  });

  function cancelAddField() {
    addFieldBtn.style.display = '';
    fieldKeyInput.style.display = 'none';
    fieldValInput.style.display = 'none';
    confirmBtn.style.display = 'none';
  }

  confirmBtn.addEventListener('click', () => {
    const k = fieldKeyInput.value.trim();
    if (!k) return;
    if (!item.data) item.data = {};
    item.data[k] = fieldValInput.value;
    cancelAddField();
    markDirty();
    renderContent();
  });

  fieldKeyInput.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') confirmBtn.click();
    if (e.key === 'Escape') cancelAddField();
  });
  fieldValInput.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') confirmBtn.click();
    if (e.key === 'Escape') cancelAddField();
  });

  addFieldRow.appendChild(fieldKeyInput);
  addFieldRow.appendChild(fieldValInput);
  addFieldRow.appendChild(confirmBtn);
  body.appendChild(addFieldRow);

  if (children.length) {
    const childrenWrap = document.createElement('div');
    childrenWrap.className = 'ep-children';
    children.forEach((child, ci) => {
      const childEl = createContentItemEl(child, ci, children, level + 1);
      childrenWrap.appendChild(childEl);
    });
    body.appendChild(childrenWrap);
  }

  const addRow = document.createElement('div');
  addRow.className = 'ep-add-child-row';

  const addBtn = document.createElement('button');
  addBtn.className = 'platform-button platform-button-sm platform-button-ghost';
  addBtn.textContent = '+ Add Child';
  addRow.appendChild(addBtn);

  const selector = document.createElement('select');
  selector.className = 'platform-input';
  selector.style.cssText = 'display:none;width:auto;min-width:160px';
  addRow.appendChild(selector);

  function populateSelector() {
    selector.innerHTML = '<option value="">-- select --</option>' +
      components.map(c => '<option value="' + escapeHtml(c.name) + '">' + escapeHtml(c.name) + '</option>').join('');
  }

  addBtn.addEventListener('click', () => {
    populateSelector();
    addBtn.style.display = 'none';
    selector.style.display = 'inline-block';
    selector.focus();
  });

  selector.addEventListener('change', () => {
    if (!selector.value) return;
    if (!item.children) item.children = [];
    item.children.push({ component: selector.value, data: {} });
    selector.value = '';
    selector.style.display = 'none';
    addBtn.style.display = '';
    markDirty();
    renderContent();
  });

  selector.addEventListener('blur', () => {
    setTimeout(() => {
      selector.style.display = 'none';
      addBtn.style.display = '';
    }, 200);
  });

  body.appendChild(addRow);

  if (!dataKeys.length && !children.length) {
    const empty = document.createElement('div');
    empty.style.cssText = 'color:var(--platform-color-text-secondary);font-size:11px;padding:0 0 8px 0';
    empty.textContent = tr('editPosts.noDataFields', 'No data fields');
    body.insertBefore(empty, addRow);
  }

  el.appendChild(header);
  el.appendChild(body);

  if (isTopLevel) {
    el.addEventListener('dragstart', (e) => {
      e.dataTransfer.effectAllowed = 'move';
      e.dataTransfer.setData('text/plain', String(index));
      el.classList.add('ep-dragging');
    });

    el.addEventListener('dragend', () => {
      el.classList.remove('ep-dragging');
      contentListEl.querySelectorAll('.ep-drag-over').forEach(e => e.classList.remove('ep-drag-over'));
    });

    el.addEventListener('dragover', (e) => {
      e.preventDefault();
      e.dataTransfer.dropEffect = 'move';
      contentListEl.querySelectorAll('.ep-drag-over').forEach(e => e.classList.remove('ep-drag-over'));
      el.classList.add('ep-drag-over');
    });

    el.addEventListener('dragleave', () => {
      el.classList.remove('ep-drag-over');
    });

    el.addEventListener('drop', (e) => {
      e.preventDefault();
      el.classList.remove('ep-drag-over');
      const fromIdx = parseInt(e.dataTransfer.getData('text/plain'));
      const toIdx = index;
      if (fromIdx === toIdx) return;
      const items = currentPost.content || [];
      const [moved] = items.splice(fromIdx, 1);
      items.splice(toIdx, 0, moved);
      markDirty();
      renderContent();
    });
  }
  header.querySelector('[data-ep-action="remove-content"]').addEventListener('click', () => {
    parentArray.splice(index, 1);
    markDirty();
    renderContent();
  });

  return el;
}

function createDataFieldRow(key, val, item) {
  const label = document.createElement('label');
  label.className = 'platform-form-label';
  label.textContent = key.replace(/_/g, ' ');

  const input = document.createElement('input');
  input.className = 'platform-input';
  input.style.width = '100%';
  let v = val;
  if (Array.isArray(v)) v = v.join(', ');
  if (typeof v === 'object' && v !== null) v = JSON.stringify(v);
  input.value = v ?? '';
  input.dataset.dataKey = key;
  input.addEventListener('change', () => {
    if (!item.data) item.data = {};
    item.data[input.dataset.dataKey] = input.value;
    markDirty();
  });
  const row = document.createElement('div');
  row.className = 'platform-form-row';
  row.appendChild(label);
  row.appendChild(input);
  return row;
}

function togglePalette() {
  if (!paletteVisible) {
    if (!paletteListEl.children.length) renderPalette();
    paletteEl.style.display = 'block';
    paletteVisible = true;
  } else {
    paletteEl.style.display = 'none';
    paletteVisible = false;
  }
}

function renderPalette(filter) {
  paletteListEl.innerHTML = '';
  const q = (filter || '').toLowerCase();
  const filtered = components.filter(c => c.name.toLowerCase().includes(q) || c.description.toLowerCase().includes(q));

  filtered.forEach(c => {
    const el = document.createElement('div');
    el.className = 'ep-palette-item';
    el.draggable = true;
    el.innerHTML = `<span class="ep-palette-item-name">${escapeHtml(c.name)}</span><span class="ep-palette-item-desc">${escapeHtml(c.description || '')}</span>`;

    el.addEventListener('dragstart', (e) => {
      e.dataTransfer.effectAllowed = 'copy';
      e.dataTransfer.setData('text/plain', 'new:' + c.name);
      el.classList.add('ep-dragging');
    });

    el.addEventListener('dragend', () => {
      el.classList.remove('ep-dragging');
    });

    el.addEventListener('click', () => {
      addComponentToContent(c.name);
    });

    paletteListEl.appendChild(el);
  });

  if (!filtered.length) {
    paletteListEl.innerHTML = '<div style="color:var(--platform-color-text-secondary);font-size:12px;padding:12px;text-align:center">' + escapeHtml(tr('editPosts.noComponentsMatch', 'No components match.')) + '</div>';
  }
}

function addComponentToContent(name) {
  if (!currentPost) return;
  if (!currentPost.content) currentPost.content = [];
  currentPost.content.push({ component: name, data: {} });
  markDirty();
  renderContent();
  setStatus(tr('editPosts.added', 'Added: {name}', { name }));
}
