/**
 * utility_carousel_loop: duplicates the items of every [data-carousel-loop] once (aria-hidden, not
 * focusable) so the CSS animation can loop seamlessly, then turns the animation on (data-animated).
 * Skipped when the user prefers reduced motion (the items then simply wrap, centered).
 * window.CarouselLoop.init(element) sets up a loop added to the page later.
 */
(function () {
  'use strict';

  function reducedMotion() {
    return !!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);
  }

  function init(loop) {
    if (!loop || loop.dataset.animated) return;
    if (reducedMotion()) return;

    var list = loop.querySelector('.carousel-loop-list');
    if (!list || !list.children.length) return;

    Array.prototype.slice.call(list.children).forEach(function (item) {
      var copy = item.cloneNode(true);
      copy.setAttribute('aria-hidden', 'true');
      copy.removeAttribute('id');
      copy.querySelectorAll('[id]').forEach(function (el) { el.removeAttribute('id'); });
      copy.querySelectorAll('a, button, input, [tabindex]').forEach(function (el) {
        el.setAttribute('tabindex', '-1');
      });
      list.appendChild(copy);
    });

    loop.dataset.animated = 'true';
  }

  function start() {
    document.querySelectorAll('[data-carousel-loop]').forEach(init);
  }

  window.CarouselLoop = { init: init };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start);
  } else {
    start();
  }
})();
