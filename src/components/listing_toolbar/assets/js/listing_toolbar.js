/**
 * listing_toolbar: keeps a toolbar's state in the page URL, so a filtered / sorted / searched listing
 * can be bookmarked and shared, and opening such a link restores it.
 *
 *   ?q=hotel                  search words
 *   &project_types=media-image   one param per filter: the select "name" = slug of the chosen option
 *   &sort=publish-date&order=desc   sort row (slug of the Sort by label)
 *
 * Only values that differ from the page's default state are written; other params (e.g. _id in the preview)
 * are kept. history.replaceState is used, so the Back button is not filled with every change.
 * Used by filters_post_projects.js, hobby_pc_video_games_filters.js, search.js and filters_sort.js.
 */
(function () {
  function slug(value) {
    var s = String(value == null ? '' : value).toLowerCase();
    if (s.normalize) s = s.normalize('NFD').replace(/[̀-ͯ]/g, '');
    return s.replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
  }

  function params() {
    try { return new URL(window.location.href).searchParams; } catch (e) { return null; }
  }

  /** URL token of an option: its slug, or the raw value when two options share the slug. */
  function token(select, value) {
    var s = slug(value);
    var same = Array.prototype.filter.call(select.options, function (o) { return o.value !== '' && slug(o.value) === s; });
    return same.length > 1 || s === '' ? String(value) : s;
  }

  /** Selects the option matching the URL token (exact value, slug of the value, or slug of the label). */
  function pick(select, wanted) {
    if (wanted == null || wanted === '') return false;
    var options = Array.prototype.filter.call(select.options, function (o) { return o.value !== ''; });
    var w = slug(wanted);
    var match = options.filter(function (o) { return o.value === wanted; })[0]
      || options.filter(function (o) { return slug(o.value) === w; })[0]
      || options.filter(function (o) { return slug(o.textContent) === w; })[0];
    if (!match) return false;
    select.value = match.value;
    return true;
  }

  /** Sets (value) or removes (null / "") URL params, without adding a history entry. */
  function set(values) {
    if (!window.history || !history.replaceState || !window.URL) return;
    var url;
    try { url = new URL(window.location.href); } catch (e) { return; }
    Object.keys(values).forEach(function (key) {
      var v = values[key];
      if (v === null || v === undefined || v === '') url.searchParams.delete(key);
      else url.searchParams.set(key, v);
    });
    var next = url.toString().replace(/\?$/, '');
    if (next !== window.location.href) history.replaceState(history.state, '', next);
  }

  function open(form, buttonSelector, blockSelector) {
    var block = form.querySelector(blockSelector);
    var button = form.querySelector(buttonSelector);
    if (block) block.hidden = false;
    if (button) button.setAttribute('aria-expanded', 'true');
  }

  function filterSelects(form) {
    return Array.prototype.filter.call(form.querySelectorAll('select[data-filter]'), function (s) { return !!s.name; });
  }

  function searchInput(form) {
    return form.querySelector('input[type="search"]');
  }

  /** Filters + search words from the URL; call before the first filtering. Remembers the defaults. */
  function restore(form) {
    var p = params();
    var defaults = {};
    var filtered = false;
    filterSelects(form).forEach(function (select) {
      defaults[select.name] = select.value;
      if (p && p.has(select.name) && pick(select, p.get(select.name)) && select.value !== defaults[select.name]) filtered = true;
    });
    form._toolbarDefaults = defaults;
    if (filtered) open(form, '.toggle-filters', '.block-filters');

    var input = searchInput(form);
    var q = p ? (p.get('q') || '').trim() : '';
    if (input && q) {
      input.value = q;
      var box = input.closest('.search-input');
      if (box && box.hidden) open(form, '.toggle-search', '.search-input');
    }
  }

  /** Writes the filters + search words of the form to the URL. */
  function save(form) {
    if (!form._toolbarDefaults) return;
    var values = {};
    filterSelects(form).forEach(function (select) {
      var v = select.value;
      values[select.name] = v === '' || v === form._toolbarDefaults[select.name] ? null : token(select, v);
    });
    var input = searchInput(form);
    if (input) values.q = input.value.trim();
    set(values);
  }

  /** Sort row from the URL (?sort=<label slug>&order=asc|desc). apply(field changed) re-sorts. */
  function restoreSort(form, bySelect, orderSelect, onField, apply) {
    var defaults = { by: bySelect.value, order: orderSelect.value };
    var p = params();
    var changed = false;
    if (p && p.has('sort') && pick(bySelect, p.get('sort')) && bySelect.value !== defaults.by) {
      onField();
      changed = true;
    }
    var order = p ? p.get('order') : null;
    if ((order === 'asc' || order === 'desc') && !orderSelect.disabled && order !== orderSelect.value) {
      orderSelect.value = order;
      apply();
      changed = true;
    }
    if (changed) open(form, '.toggle-sort', '.block-sort');
    return defaults;
  }

  function saveSort(bySelect, orderSelect, defaults) {
    if (!defaults) return;
    var isDefault = bySelect.value === defaults.by && orderSelect.value === defaults.order;
    var option = bySelect.options[bySelect.selectedIndex];
    set({
      sort: isDefault ? null : (bySelect.value === '' ? 'default' : slug(option ? option.textContent : bySelect.value)),
      order: isDefault || bySelect.value === '' ? null : orderSelect.value
    });
  }

  window.listingToolbarUrl = {
    slug: slug,
    restore: restore,
    save: save,
    restoreSort: restoreSort,
    saveSort: saveSort
  };
})();
