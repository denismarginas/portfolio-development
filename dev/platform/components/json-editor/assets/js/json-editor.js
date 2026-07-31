class JsonEditor {
  constructor(container, value, options = {}) {
    this.container = container;
    this._value = value;
    this.onChange = options.onChange || (() => {});
    this.readonly = options.readonly || false;
    this.maxDepth = options.maxDepth || 20;
    this._pathCache = new Map();
    this.render();
  }

  get value() { return this._value; }
  set value(v) { this._value = v; this.render(); }

  render() {
    this.container.innerHTML = '';
    this.container.className = 'platform-je-root';
    this.container.appendChild(this._buildNode(this._value, [], 0));
  }

  _typeOf(val) {
    if (val === null) return 'null';
    if (Array.isArray(val)) return 'array';
    return typeof val;
  }

  _buildNode(val, path, depth) {
    const type = this._typeOf(val);
    const node = document.createElement('div');
    node.className = 'platform-je-node platform-je-type-' + type;

    if (depth >= this.maxDepth) {
      node.textContent = '[max depth]';
      return node;
    }

    if (type === 'object') {
      node.appendChild(this._buildObjectNode(val, path, depth));
    } else if (type === 'array') {
      node.appendChild(this._buildArrayNode(val, path, depth));
    } else {
      node.appendChild(this._buildPrimitiveNode(val, path, type));
    }

    return node;
  }

  _buildObjectNode(obj, path, depth) {
    const wrap = document.createElement('div');
    wrap.className = 'platform-je-object';

    const keys = Object.keys(obj);
    keys.forEach(k => {
      wrap.appendChild(this._buildKeyValueRow(k, obj, path, depth));
    });

    if (!this.readonly) {
      wrap.appendChild(this._buildAddKeyRow(obj, path, depth));
    }

    return wrap;
  }

  _buildKeyValueRow(key, obj, path, depth) {
    const row = document.createElement('div');
    row.className = 'platform-je-row';

    const keyInput = document.createElement('input');
    keyInput.className = 'platform-je-key-input';
    keyInput.value = key;
    keyInput.spellcheck = false;

    if (!this.readonly) {
      keyInput.addEventListener('change', () => {
        const newKey = keyInput.value.trim();
        if (!newKey || newKey === key) return;
        if (newKey in obj) { keyInput.value = key; return; }
        obj[newKey] = obj[key];
        delete obj[key];
        this._emitChange(path);
      });
    } else {
      keyInput.disabled = true;
    }

    const colon = document.createElement('span');
    colon.className = 'platform-je-colon';
    colon.textContent = ': ';

    const valNode = this._buildNode(obj[key], path.concat(key), depth + 1);

    const typeSwitch = document.createElement('select');
    typeSwitch.className = 'platform-je-type-switch';
    ['string', 'number', 'boolean', 'null', 'object', 'array'].forEach(t => {
      const opt = document.createElement('option');
      opt.value = t;
      opt.textContent = t;
      if (t === this._typeOf(obj[key])) opt.selected = true;
      typeSwitch.appendChild(opt);
    });

    if (!this.readonly) {
      typeSwitch.addEventListener('change', () => {
        obj[key] = this._convertType(typeSwitch.value, obj[key]);
        this._emitChange(path);
        this._rebuildValNode(valNode, obj[key], path.concat(key), depth + 1);
      });
    } else {
      typeSwitch.disabled = true;
    }

    const actions = document.createElement('span');
    actions.className = 'platform-je-actions';

    if (!this.readonly) {
      const delBtn = document.createElement('button');
      delBtn.className = 'platform-je-btn platform-je-btn-del';
      delBtn.textContent = '\u2715';
      delBtn.title = 'Delete key';
      delBtn.addEventListener('click', () => {
        delete obj[key];
        this._emitChange(path);
        row.remove();
      });
      actions.appendChild(delBtn);
    }

    row.appendChild(keyInput);
    row.appendChild(colon);
    row.appendChild(typeSwitch);
    row.appendChild(valNode);
    row.appendChild(actions);

    return row;
  }

  _buildAddKeyRow(obj, path, depth) {
    const row = document.createElement('div');
    row.className = 'platform-je-row platform-je-add-row';

    const keyInput = document.createElement('input');
    keyInput.className = 'platform-je-key-input platform-je-add-key-input';
    keyInput.placeholder = 'new key';

    const typeSelect = document.createElement('select');
    typeSelect.className = 'platform-je-type-switch';
    ['string', 'number', 'boolean', 'null', 'object', 'array'].forEach(t => {
      const opt = document.createElement('option');
      opt.value = t;
      opt.textContent = t;
      typeSelect.appendChild(opt);
    });

    const addBtn = document.createElement('button');
    addBtn.className = 'platform-je-btn platform-je-btn-add';
    addBtn.textContent = '+';

    const confirmAdd = () => {
      const k = keyInput.value.trim();
      if (!k || k in obj) return;
      obj[k] = this._defaultValue(typeSelect.value);
      this._emitChange(path);
      this._rebuildValNode(this.container, this._value, [], 0);
    };

    addBtn.addEventListener('click', confirmAdd);
    keyInput.addEventListener('keydown', e => { if (e.key === 'Enter') confirmAdd(); });

    row.appendChild(keyInput);
    row.appendChild(typeSelect);
    row.appendChild(addBtn);

    return row;
  }

  _buildArrayNode(arr, path, depth) {
    const wrap = document.createElement('div');
    wrap.className = 'platform-je-array';

    arr.forEach((item, i) => {
      wrap.appendChild(this._buildArrayItemRow(item, i, arr, path, depth));
    });

    if (!this.readonly) {
      wrap.appendChild(this._buildAddItemRow(arr, path, depth));
    }

    return wrap;
  }

  _buildArrayItemRow(item, index, arr, path, depth) {
    const row = document.createElement('div');
    row.className = 'platform-je-row';

    const idxBadge = document.createElement('span');
    idxBadge.className = 'platform-je-idx';
    idxBadge.textContent = index;

    const valNode = this._buildNode(item, path.concat(index), depth + 1);

    const typeSwitch = document.createElement('select');
    typeSwitch.className = 'platform-je-type-switch';
    ['string', 'number', 'boolean', 'null', 'object', 'array'].forEach(t => {
      const opt = document.createElement('option');
      opt.value = t;
      opt.textContent = t;
      if (t === this._typeOf(item)) opt.selected = true;
      typeSwitch.appendChild(opt);
    });

    if (!this.readonly) {
      typeSwitch.addEventListener('change', () => {
        arr[index] = this._convertType(typeSwitch.value, arr[index]);
        this._emitChange(path);
        this._rebuildValNode(valNode, arr[index], path.concat(index), depth + 1);
      });
    } else {
      typeSwitch.disabled = true;
    }

    const actions = document.createElement('span');
    actions.className = 'platform-je-actions';

    if (!this.readonly) {
      const delBtn = document.createElement('button');
      delBtn.className = 'platform-je-btn platform-je-btn-del';
      delBtn.textContent = '\u2715';
      delBtn.title = 'Delete item';
      delBtn.addEventListener('click', () => {
        arr.splice(index, 1);
        this._emitChange(path);
        this._rebuildValNode(this.container, this._value, [], 0);
      });
      actions.appendChild(delBtn);
    }

    row.appendChild(idxBadge);
    row.appendChild(typeSwitch);
    row.appendChild(valNode);
    row.appendChild(actions);

    return row;
  }

  _buildAddItemRow(arr, path, depth) {
    const row = document.createElement('div');
    row.className = 'platform-je-row platform-je-add-row';

    const typeSelect = document.createElement('select');
    typeSelect.className = 'platform-je-type-switch';
    ['string', 'number', 'boolean', 'null', 'object', 'array'].forEach(t => {
      const opt = document.createElement('option');
      opt.value = t;
      opt.textContent = t;
      typeSelect.appendChild(opt);
    });

    const addBtn = document.createElement('button');
    addBtn.className = 'platform-je-btn platform-je-btn-add';
    addBtn.textContent = '+ Add';

    addBtn.addEventListener('click', () => {
      arr.push(this._defaultValue(typeSelect.value));
      this._emitChange(path);
      this._rebuildValNode(this.container, this._value, [], 0);
    });

    row.appendChild(typeSelect);
    row.appendChild(addBtn);

    return row;
  }

  _buildPrimitiveNode(val, path, type) {
    const node = document.createElement('div');
    node.className = 'platform-je-primitive';

    if (type === 'null') {
      const span = document.createElement('span');
      span.className = 'platform-je-null';
      span.textContent = 'null';
      node.appendChild(span);
      return node;
    }

    if (type === 'boolean') {
      const cb = document.createElement('input');
      cb.type = 'checkbox';
      cb.className = 'platform-checkbox';
      cb.checked = val;
      if (!this.readonly) {
        cb.addEventListener('change', () => {
          this._setPathValue(path, cb.checked);
          this._emitChange(path.slice(0, -1));
        });
      } else {
        cb.disabled = true;
      }
      node.appendChild(cb);
      const label = document.createElement('span');
      label.textContent = String(val);
      node.appendChild(label);
      return node;
    }

    const input = document.createElement('input');
    input.className = 'platform-je-value-input';
    input.type = type === 'number' ? 'number' : 'text';
    input.value = type === 'number' ? String(val) : val;

    if (!this.readonly) {
      input.addEventListener('change', () => {
        const p = path.slice(0, -1);
        const k = path[path.length - 1];
        const parent = this._getPathValue(p);
        if (parent !== undefined) {
          parent[k] = type === 'number' ? parseFloat(input.value) : input.value;
          this._emitChange(p);
        }
      });
    } else {
      input.disabled = true;
    }

    node.appendChild(input);
    return node;
  }

  _convertType(newType, oldVal) {
    switch (newType) {
      case 'string': return String(oldVal ?? '');
      case 'number': return Number(oldVal) || 0;
      case 'boolean': return Boolean(oldVal);
      case 'null': return null;
      case 'object': return typeof oldVal === 'object' && !Array.isArray(oldVal) && oldVal !== null ? oldVal : {};
      case 'array': return Array.isArray(oldVal) ? oldVal : [];
    }
  }

  _defaultValue(type) {
    switch (type) {
      case 'string': return '';
      case 'number': return 0;
      case 'boolean': return false;
      case 'null': return null;
      case 'object': return {};
      case 'array': return [];
    }
  }

  _getPathValue(path) {
    let cur = this._value;
    for (const seg of path) {
      if (cur === undefined || cur === null) return undefined;
      cur = cur[seg];
    }
    return cur;
  }

  _setPathValue(path, val) {
    let cur = this._value;
    for (let i = 0; i < path.length - 1; i++) {
      cur = cur[path[i]];
    }
    cur[path[path.length - 1]] = val;
  }

  _rebuildValNode(node, val, path, depth) {
    const parent = node.parentElement;
    const newNode = this._buildNode(val, path, depth);
    if (parent) parent.replaceChild(newNode, node);
  }

  _emitChange(path) {
    this.onChange(this._value, path);
  }
}
