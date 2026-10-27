/**
 * video_playlist: a playlist button loads its player (from its <template>) into the screen, pausing the
 * one that was playing, and updates the title / year / "More details"; the list fades out at the bottom
 * (theme class mask-fade-bottom) while there are more videos below. "More details" opens the video's
 * <template data-video-details> (video_listing with one video) in the popup; popup.js pauses the page videos
 * and sets up the copied player.
 */
(function () {
  'use strict';

  function setup(root) {
    var screen = root.querySelector('[data-video-screen]');
    var items = Array.prototype.slice.call(root.querySelectorAll('[data-video-index]'));
    var title = root.querySelector('[data-video-title]');
    var meta = root.querySelector('[data-video-meta]');
    var detailsBtn = root.querySelector('[data-video-details-open]');
    var list = root.querySelector('[data-video-list]');
    if (!screen || !items.length) return;

    // bottom fade (theme mask-fade-bottom) only while more items are hidden below
    function updateFade() {
      if (!list) return;
      list.classList.toggle('mask-fade-bottom', list.scrollHeight - list.scrollTop - list.clientHeight > 2);
    }
    if (list) {
      list.addEventListener('scroll', updateFade, { passive: true });
      if ('ResizeObserver' in window) new ResizeObserver(updateFade).observe(list);
      else window.addEventListener('resize', updateFade);
      updateFade();
    }

    var active = 0;

    function template(kind, index) {
      return root.querySelector('template[data-video-' + kind + '="' + index + '"]');
    }

    function select(index, autoplay) {
      if (index === active) return;
      var tpl = template('player', index);
      if (!tpl) return;
      active = index;

      screen.querySelectorAll('video').forEach(function (video) { video.pause(); });
      screen.innerHTML = tpl.innerHTML;
      if (window.VideoControls && typeof window.VideoControls.initAll === 'function') window.VideoControls.initAll();

      items.forEach(function (item, i) {
        if (i === index) item.setAttribute('aria-current', 'true');
        else item.removeAttribute('aria-current');
      });

      var item = items[index];
      if (title) title.textContent = item.getAttribute('data-title') || '';
      if (meta) meta.textContent = item.getAttribute('data-meta') || '';
      if (detailsBtn) detailsBtn.hidden = item.getAttribute('data-has-details') !== 'true';

      if (autoplay) {
        var video = screen.querySelector('video');
        var play = screen.querySelector('.show-play');
        // start through the player's own button so its controls follow
        if (play) play.click();
        else if (video) video.play().catch(function () {});
      }
    }

    items.forEach(function (item, i) {
      item.addEventListener('click', function () { select(i, true); });
    });

    if (detailsBtn) {
      detailsBtn.addEventListener('click', function () {
        var tpl = template('details', active);
        if (!tpl || !window.Popup || typeof window.Popup.open !== 'function') return;
        screen.querySelectorAll('video').forEach(function (video) { video.pause(); });
        window.Popup.open(tpl.innerHTML, { source: root });
      });
    }
  }

  function init() {
    document.querySelectorAll('[data-video-playlist]').forEach(setup);
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
