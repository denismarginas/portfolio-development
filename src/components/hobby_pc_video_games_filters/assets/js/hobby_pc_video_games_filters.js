/**
 * hobby_pc_video_games_filters: filters the <li data-item-id> items of <ul id="{data-target}">
 * using the JSON index printed inside the form. Sorting is done by filters_sort.
 * Display select (data-type="display"): "" / "default" hides items with data-display="false", "all" shows them.
 */
(function () {
  function toNumber(value) {
    var match = String(value == null ? '' : value).match(/-?\d+(?:[.,]\d+)?/);
    return match ? parseFloat(match[0].replace(',', '.')) : null;
  }

  function matchesFilter(values, select) {
    var selected = select.value;
    if (!selected) return true;
    values = values || [];

    var type = select.dataset.type;
    if (type === 'number' || type === 'date') {
      var wanted = toNumber(selected);
      var mode = select.dataset.mode || '=';
      return values.some(function (v) {
        var n = toNumber(v);
        if (n === null || wanted === null) return false;
        if (mode === '>=') return n >= wanted;
        if (mode === '<=') return n <= wanted;
        return n === wanted;
      });
    }
    return values.indexOf(selected) !== -1;
  }

  function matchesKeywords(entry, words) {
    var text = entry.keywords || '';
    return words.every(function (word) { return text.indexOf(word) !== -1; });
  }

  function init(form) {
    var list = document.getElementById(form.dataset.target);
    var indexEl = form.querySelector('.filters-index');
    if (!list || !indexEl) return;

    var index = {};
    try { index = JSON.parse(indexEl.textContent || '{}'); } catch (e) { return; }

    var items = Array.prototype.slice.call(list.querySelectorAll('li[data-item-id]'));
    var selects = Array.prototype.slice.call(form.querySelectorAll('select[data-filter]:not([data-type="display"])'));
    var displaySelect = form.querySelector('select[data-filter][data-type="display"]');
    var input = form.querySelector('.item-search');
    var amount = form.querySelector('.query-amount');
    var empty = list.parentNode ? list.parentNode.querySelector('.hobby-pc-video-games-empty') : null;

    function apply() {
      var words = (input ? input.value : '').toLowerCase().split(/\s+/).filter(Boolean);
      var shown = 0;
      var showAll = displaySelect && displaySelect.value === 'all';
      list.setAttribute('data-display', showAll ? 'all' : 'default');

      items.forEach(function (item) {
        var entry = index[item.dataset.itemId];
        var displayed = showAll || item.dataset.display !== 'false';
        var visible = displayed && (!entry || (
          matchesKeywords(entry, words) &&
          selects.every(function (select) { return matchesFilter(entry[select.dataset.filter], select); })
        ));
        item.hidden = !visible;
        if (visible) shown++;
      });

      if (amount) amount.textContent = shown;
      if (empty) empty.hidden = shown !== 0;
      list.setAttribute('data-results', shown);
    }

    function toggle(button, target) {
      var open = button.getAttribute('aria-expanded') !== 'true';
      button.setAttribute('aria-expanded', open ? 'true' : 'false');
      if (target) target.hidden = !open;
    }

    selects.forEach(function (select) { select.addEventListener('change', apply); });
    if (displaySelect) displaySelect.addEventListener('change', apply);
    if (input) input.addEventListener('input', apply);
    form.addEventListener('submit', function (event) { event.preventDefault(); apply(); });
    form.addEventListener('reset', function () { setTimeout(apply, 0); });

    var filtersBtn = form.querySelector('.toggle-filters');
    var queryBtn = form.querySelector('.toggle-query');
    if (filtersBtn) filtersBtn.addEventListener('click', function () { toggle(filtersBtn, form.querySelector('.block-filters')); });
    if (queryBtn) queryBtn.addEventListener('click', function () { toggle(queryBtn, form.querySelector('.query-text')); });

    apply();
  }

  function start() {
    document.querySelectorAll('form.hobby-pc-video-games-filters[data-target]').forEach(init);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start);
  } else {
    start();
  }
})();
