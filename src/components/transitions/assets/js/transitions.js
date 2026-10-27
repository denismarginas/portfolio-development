/**
 * transitions: entrance animations when an element scrolls into view.
 *
 *   <div data-transition="fade slide-bottom" data-transition-duration="800" data-transition-delay="150">…</div>
 *
 * Effects (combine with spaces): fade, slide-left, slide-right, slide-top, slide-bottom,
 *   rotate, rotate-left, rotate-right, zoom (zoom-in), zoom-out, blur.
 *   slide-* = comes in FROM that side (slide-left starts on the left and moves right).
 *
 * Options (attributes on the same element):
 *   data-transition-duration   ms or "0.8s" (default 700)
 *   data-transition-delay      ms or "0.2s" (default 0)
 *   data-transition-easing     CSS easing (default "cubic-bezier(0.2, 0.7, 0.2, 1)")
 *   data-transition-distance   slide distance, e.g. "60px", "20%" (default "40px")
 *   data-transition-angle      rotate angle, e.g. "15deg" (default 12deg)
 *   data-transition-scale      zoom start scale (default 0.85 for zoom, 1.15 for zoom-out)
 *   data-transition-blur       blur start, e.g. "12px" (default 10px)
 *   data-transition-threshold  0–1, starts when the element's top passes this part of the screen height above the
 *                              bottom edge (default 0.15); works for elements of any height (also taller than the screen)
 *   data-transition-once       "false" = play again every time it re-enters the view
 *   data-transition-target     animate elements inside instead of the element itself:
 *                              "children" = its direct children, or a CSS selector, e.g. ".taxonomy-item"
 *   data-transition-stagger    with a target: extra delay per item, ms (default 0)
 *   data-transition-each       "true" (with a target) = each item starts when IT scrolls into view (long lists);
 *                              the stagger then counts the items that come into view together
 *   data-transition-auto       "true" = set by the site-wide rules (data_settings_transitions.json): its own animation is
 *                              skipped inside another animated element, everything is skipped in the site header / footer
 *   data-transition-parts      JSON list of parts inside the element, each with its own effect, e.g.
 *                              [{"target":".title","effect":"fade slide-left","delay":150},{"target":"li","effect":"fade","stagger":60}]
 *                              part keys: target, effect, duration, delay, easing, distance, angle, scale, blur, threshold, stagger;
 *                              every matched element starts when it scrolls into view (stagger = items coming in together).
 *                              data-transition="none" = only the parts animate, not the element itself
 *
 * Safe by design:
 *   - played with the Web Animations API: the element's own CSS (transform, transition, opacity, filter) is never
 *     written to, and nothing stays on the element after the animation ends;
 *   - moves with the individual translate / rotate / scale properties, so it adds to the element's own transform;
 *   - fades / un-blurs to the element's own values (implicit end keyframe);
 *   - waiting elements are only hidden (visibility) once this script runs: no JS = content shown as is;
 *   - prefers-reduced-motion: nothing is hidden or animated;
 *   - while a sideways slide waits or plays, html gets .tx-sliding (overflow-x: clip), so an element starting past
 *     the screen edge never adds a horizontal scrollbar; removed as soon as the last sideways slide has ended.
 */
(function () {
  var root = document.documentElement;
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var supported = typeof Element !== 'undefined' && 'animate' in Element.prototype && 'IntersectionObserver' in window;

  if (reduce || !supported) return;
  // hides the waiting elements before the first paint (CSS: html.tx-js [data-transition]:not(.tx-ready))
  root.classList.add('tx-js');

  var DEFAULTS = {
    duration: 700,
    delay: 0,
    easing: 'cubic-bezier(0.2, 0.7, 0.2, 1)',
    distance: '40px',
    angle: '12deg',
    blur: '10px',
    threshold: 0.15
  };

  function ms(value, fallback) {
    if (value === null || value === undefined || value === '') return fallback;
    var s = String(value).trim();
    var n = parseFloat(s);
    if (isNaN(n)) return fallback;
    return /ms$/.test(s) ? n : (/s$/.test(s) ? n * 1000 : n);
  }

  function negate(length) {
    var s = String(length).trim();
    return s.charAt(0) === '-' ? s.slice(1) : '-' + s;
  }

  /** First keyframe for the effects; the end keyframe is implicit (the element's own values). */
  function fromFrame(effects, opts) {
    var frame = {};
    var x = '0px';
    var y = '0px';
    var moved = false;

    effects.forEach(function (effect) {
      switch (effect) {
        case 'fade': frame.opacity = 0; break;
        case 'slide-left': x = negate(opts.distance); moved = true; break;
        case 'slide-right': x = opts.distance; moved = true; break;
        case 'slide-top': y = negate(opts.distance); moved = true; break;
        case 'slide-bottom': y = opts.distance; moved = true; break;
        case 'rotate':
        case 'rotate-left': frame.rotate = negate(opts.angle); break;
        case 'rotate-right': frame.rotate = opts.angle; break;
        case 'zoom':
        case 'zoom-in': frame.scale = String(opts.scale !== null ? opts.scale : 0.85); break;
        case 'zoom-out': frame.scale = String(opts.scale !== null ? opts.scale : 1.15); break;
        case 'blur': frame.filter = 'blur(' + opts.blur + ')'; break;
      }
    });
    if (moved) frame.translate = x + ' ' + y;
    return frame;
  }

  /** Options from data-transition-* attributes. */
  function options(el) {
    var d = el.dataset;
    return read(function (key) { return d['transition' + key.charAt(0).toUpperCase() + key.slice(1)]; }, null);
  }

  /** Options from get(key) (attribute or part object); missing values come from base, then the defaults. */
  function read(get, base) {
    base = base || {};
    var pick = function (key, fallback) {
      var v = get(key);
      if (v !== undefined && v !== null && v !== '') return v;
      return base[key] !== undefined && base[key] !== null ? base[key] : fallback;
    };
    var scale = parseFloat(pick('scale', null));
    var threshold = parseFloat(pick('threshold', DEFAULTS.threshold));
    return {
      duration: Math.max(0, ms(pick('duration', DEFAULTS.duration), DEFAULTS.duration)),
      delay: Math.max(0, ms(pick('delay', DEFAULTS.delay), DEFAULTS.delay)),
      easing: String(pick('easing', DEFAULTS.easing)),
      distance: String(pick('distance', DEFAULTS.distance)),
      angle: String(pick('angle', DEFAULTS.angle)),
      blur: String(pick('blur', DEFAULTS.blur)),
      scale: isNaN(scale) ? null : scale,
      threshold: isNaN(threshold) ? DEFAULTS.threshold : Math.min(1, Math.max(0, threshold)),
      once: String(pick('once', 'true')) !== 'false',
      stagger: Math.max(0, ms(get('stagger'), 0)),
      target: String(get('target') || '').trim(),
      each: String(get('each') || '') === 'true'
    };
  }

  // html.tx-sliding while sideways slides wait or run (no horizontal scrollbar from elements starting off screen)
  var sliding = 0;
  function trackSliding(animation) {
    if (!animation.txSideways || animation.txTracked) return;
    animation.txTracked = true;
    sliding++;
    root.classList.add('tx-sliding');
    var done = function () {
      animation.txTracked = false;
      sliding = Math.max(0, sliding - 1);
      if (!sliding) root.classList.remove('tx-sliding');
    };
    animation.finished.then(done, done);
  }

  /**
   * One element: a paused animation that holds the first frame (fill: backwards) until it is seen.
   * play() runs it; when it ends the animation is gone and the element shows its own styles again.
   */
  function prepare(el, effects, opts, extraDelay) {
    var frame = fromFrame(effects, opts);
    if (!Object.keys(frame).length) return null;
    var animation;
    try {
      animation = el.animate([frame, {}], {
        duration: opts.duration,
        delay: opts.delay + (extraDelay || 0),
        easing: opts.easing,
        fill: 'backwards'
      });
    } catch (e) {
      return null; // unknown easing / value: leave the element alone
    }
    animation.pause();
    animation.currentTime = 0;
    animation.txSideways = effects.indexOf('slide-left') !== -1 || effects.indexOf('slide-right') !== -1;
    trackSliding(animation);
    return animation;
  }

  var entries = new Map(); // observed element -> { animations, opts, batch? }
  var batch = new Map();   // "each" group -> items started in the current observer callback
  var observers = {};      // one IntersectionObserver per threshold

  function observerFor(threshold) {
    if (observers[threshold]) return observers[threshold];
    // threshold 0 + bottom margin: "its top passed X% above the screen bottom", whatever the element's height
    // (a ratio threshold would never be reached by an element taller than the screen)
    observers[threshold] = new IntersectionObserver(function (list, observer) {
      batch.clear();
      list.forEach(function (entry) {
        var item = entries.get(entry.target);
        if (!item) return;
        if (entry.isIntersecting) {
          playItem(entry.target, item, observer);
          return;
        }
        if (!item.opts.once && !item.playing) return;
        if (!item.opts.once) {
          // left the view: back to the first frame, ready to play again
          item.playing = false;
          item.animations.forEach(function (a) {
            a.pause();
            a.currentTime = 0;
            trackSliding(a); // waiting again (a finished animation gets a new "finished" promise)
          });
        }
      });
    }, { threshold: 0, rootMargin: '0px 0px -' + Math.round(threshold * 100) + '% 0px' });
    return observers[threshold];
  }

  function playItem(target, item, observer) {
    if (item.playing) return;
    item.playing = true;
    if (item.batch) {
      var n = batch.get(item.batch) || 0;
      batch.set(item.batch, n + 1);
      item.animations.forEach(function (a) { a.effect.updateTiming({ delay: item.opts.delay + n * item.opts.stagger }); });
    }
    item.animations.forEach(function (a) { a.play(); });
    if (item.opts.once) {
      observer.unobserve(target);
      entries.delete(target);
    }
  }

  function setup(el) {
    if (el.classList.contains('tx-ready')) return;
    var auto = el.dataset.transitionAuto === 'true';
    // automatic (rule) transitions: nothing in the site header / footer
    if (auto && el.closest('header, footer') && !el.closest('main')) {
      el.classList.add('tx-ready');
      return;
    }
    // automatic: no own animation inside another animated element (its parts still run)
    var nested = auto && el.parentElement && el.parentElement.closest('[data-transition]:not([data-transition="none"])');
    if (!nested) setupOwn(el);
    setupParts(el);
    el.classList.add('tx-ready');
  }

  function setupOwn(el) {
    var effects = String(el.dataset.transition || '').toLowerCase().split(/\s+/).filter(Boolean);
    var opts = options(el);
    var animations = [];

    var targets = null;
    if (opts.target === 'children') {
      targets = Array.prototype.slice.call(el.children);
    } else if (opts.target) {
      try { targets = Array.prototype.slice.call(el.querySelectorAll(opts.target)); } catch (e) { targets = []; }
    }

    if (targets && opts.each) {
      // every item is observed on its own
      targets.forEach(function (target) {
        var a = prepare(target, effects, opts, 0);
        if (!a) return;
        var observer = observerFor(opts.threshold);
        entries.set(target, { animations: [a], opts: opts, batch: el, observer: observer });
        observer.observe(target);
      });
      return;
    }

    if (targets) {
      targets.forEach(function (target, i) {
        var a = prepare(target, effects, opts, i * opts.stagger);
        if (a) animations.push(a);
      });
    } else {
      var a = prepare(el, effects, opts, 0);
      if (a) animations.push(a);
    }

    if (!animations.length) return;
    var observer = observerFor(opts.threshold);
    entries.set(el, { animations: animations, opts: opts, observer: observer });
    observer.observe(el);
  }

  /** data-transition-parts: elements inside with their own effects, each started when it scrolls into view. */
  function setupParts(el) {
    var raw = el.dataset.transitionParts;
    if (!raw) return;
    var parts;
    try { parts = JSON.parse(raw); } catch (e) { return; }
    if (!Array.isArray(parts)) return;
    var base = options(el);
    base.delay = 0; // the root's own delay is not added to its parts

    parts.forEach(function (part) {
      if (!part || typeof part !== 'object' || !part.target || !part.effect) return;
      var effects = String(part.effect).toLowerCase().split(/\s+/).filter(Boolean);
      var opts = read(function (key) { return part[key]; }, base);
      var targets;
      try { targets = el.querySelectorAll(part.target); } catch (e) { return; }
      var group = {}; // stagger counts the items of this part that come into view together
      Array.prototype.forEach.call(targets, function (target) {
        // one animation per element: skip elements already animated (another part, or their own data-transition)
        if (target.txPart || entries.has(target) || target.hasAttribute('data-transition')) return;
        var a = prepare(target, effects, opts, 0);
        if (!a) return;
        target.txPart = true;
        var observer = observerFor(opts.threshold);
        entries.set(target, { animations: [a], opts: opts, batch: group, observer: observer });
        observer.observe(target);
      });
    });
  }

  // catch-up after scrolling: an element whose top is already past its start line plays even if the observer
  // never saw it cross (fast scroll / anchor jump over it), and at the very end of the page every visible
  // waiting element plays (nothing more to scroll)
  var catchUpTimer = 0;
  function catchUp() {
    catchUpTimer = 0;
    if (!entries.size) return;
    var vh = window.innerHeight;
    var atEnd = vh + window.scrollY >= document.documentElement.scrollHeight - 2;
    batch.clear();
    entries.forEach(function (item, target) {
      var r = target.getBoundingClientRect();
      var line = vh * (1 - item.opts.threshold);
      if (r.top < line || (atEnd && r.top < vh && r.bottom > 0)) playItem(target, item, item.observer);
    });
  }
  window.addEventListener('scroll', function () {
    if (!entries.size) return;
    clearTimeout(catchUpTimer);
    catchUpTimer = setTimeout(catchUp, 120);
  }, { passive: true });

  function init() {
    document.querySelectorAll('[data-transition]').forEach(setup);
    catchUp();
  }

  // public: window.transitions.refresh() after adding elements with data-transition (e.g. loaded later)
  window.transitions = { refresh: init };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
