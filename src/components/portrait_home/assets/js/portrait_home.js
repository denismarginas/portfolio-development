/**
 * portrait_home: subtle scroll effect for [data-portrait-home-parallax].
 * Sets --ph-progress (-1 entering at the bottom of the screen ... 0 centered ... 1 leaving at the top);
 * the CSS turns it into a small "translate" of the chips (each with its own --ph-depth), the photo and the arch.
 * Adds .is-visible the first time the portrait is in view (chips fade in one after another).
 * Does nothing when the visitor prefers reduced motion (the CSS shows everything static).
 */
(function () {
  var items = [];
  var ticking = false;

  function update() {
    ticking = false;
    var vh = window.innerHeight || document.documentElement.clientHeight;
    items.forEach(function (el) {
      var r = el.getBoundingClientRect();
      if (r.bottom < -100 || r.top > vh + 100) return;
      var center = r.top + r.height / 2;
      var progress = (vh / 2 - center) / (vh / 2 + r.height / 2);
      progress = Math.max(-1, Math.min(1, progress));
      el.style.setProperty('--ph-progress', progress.toFixed(3));
    });
  }

  function onScroll() {
    if (!ticking) {
      ticking = true;
      window.requestAnimationFrame(update);
    }
  }

  function start() {
    items = Array.prototype.slice.call(document.querySelectorAll('[data-portrait-home-parallax]'));
    if (!items.length) return;

    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduce || !('IntersectionObserver' in window)) {
      items.forEach(function (el) { el.classList.add('is-visible'); });
      return;
    }

    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.25 });
    items.forEach(function (el) { observer.observe(el); });

    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll);
    update();
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
  else start();
})();
