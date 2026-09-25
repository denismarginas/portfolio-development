/**
 * filters_post_projects: filters the <li data-post-id> items of <ul id="{data-target}">
 * using the JSON index printed inside the form.
 */
(function () {
  function yearOf(value) {
    var match = String(value || '').match(/\d{4}/);
    return match ? parseInt(match[0], 10) : null;
  }

  function matchesFilter(values, select) {
    var selected = select.value;
    if (!selected) return true;
    values = values || [];

    if (select.dataset.type === 'date') {
      var wanted = parseInt(selected, 10);
      var mode = select.dataset.mode || '=';
      return values.some(function (v) {
        var year = yearOf(v);
        if (year === null) return false;
        if (mode === '>=') return year >= wanted;
        if (mode === '<=') return year <= wanted;
        return year === wanted;
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

    var items = Array.prototype.slice.call(list.querySelectorAll('li[data-post-id]'));
    var selects = Array.prototype.slice.call(form.querySelectorAll('select[data-filter]'));
    var input = form.querySelector('.post-search');
    var amount = form.querySelector('.query-amount');

    function apply() {
      var words = (input ? input.value : '').toLowerCase().split(/\s+/).filter(Boolean);
      var shown = 0;

      items.forEach(function (item) {
        var entry = index[item.dataset.postId];
        var visible = !entry || (
          matchesKeywords(entry, words) &&
          selects.every(function (select) { return matchesFilter(entry[select.dataset.filter], select); })
        );
        item.hidden = !visible;
        if (visible) shown++;
      });

      if (amount) amount.textContent = shown;
      list.setAttribute('data-results', shown);
    }

    function toggle(button, target, attr) {
      var open = button.getAttribute(attr) !== 'true';
      button.setAttribute(attr, open ? 'true' : 'false');
      if (target) target.hidden = !open;
      return open;
    }

    selects.forEach(function (select) { select.addEventListener('change', apply); });
    if (input) input.addEventListener('input', apply);
    form.addEventListener('submit', function (event) { event.preventDefault(); apply(); });
    form.addEventListener('reset', function () { setTimeout(apply, 0); });

    var filtersBtn = form.querySelector('.toggle-filters');
    var queryBtn = form.querySelector('.toggle-query');
    var previewBtn = form.querySelector('.toggle-preview');
    if (filtersBtn) filtersBtn.addEventListener('click', function () { toggle(filtersBtn, form.querySelector('.block-filters'), 'aria-expanded'); });
    if (queryBtn) queryBtn.addEventListener('click', function () { toggle(queryBtn, form.querySelector('.query-text'), 'aria-expanded'); });
    if (previewBtn) previewBtn.addEventListener('click', function () {
      var on = toggle(previewBtn, null, 'aria-pressed');
      list.classList.toggle('show-preview', on);
    });

    apply();
  }

  function start() {
    document.querySelectorAll('form.filters-post-projects[data-target]').forEach(init);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start);
  } else {
    start();
  }
})();
