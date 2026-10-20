(function () {
    'use strict';

    // anchor_index: marks the link of the section in view with aria-current="true".
    // Active = the last listed section whose top has passed a line at 35% of the screen;
    // at the very bottom of the page, the last listed section that is visible. Unlisted blocks are ignored;
    // links to ids that are not on the page are hidden (the whole list when fewer than min_items are left).
    function setup(nav) {
        var entries = [];
        nav.querySelectorAll('a.anchor-index-link[href^="#"]').forEach(function (link) {
            var target = document.getElementById(decodeURIComponent(link.getAttribute('href').slice(1)));
            if (target) entries.push({ link: link, target: target });
            // section not on the page (e.g. a gallery without images): hide its link
            else if (link.parentElement) link.parentElement.hidden = true;
        });
        // fewer sections on the page than data-anchor-index-min (default 2): the list makes no sense, hide it
        var min = parseInt(nav.getAttribute('data-anchor-index-min') || '2', 10);
        if (entries.length < Math.max(1, min || 1)) {
            nav.hidden = true;
            return;
        }

        var list = nav.querySelector('.anchor-index-list');
        var current = null;

        function update() {
            var line = window.innerHeight * 0.35;
            var active = null;
            var atBottom = window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 4;

            entries.forEach(function (entry) {
                var top = entry.target.getBoundingClientRect().top;
                if (top <= line || (atBottom && top < window.innerHeight)) active = entry;
            });

            var link = active ? active.link : null;
            if (link === current) return;
            if (current) current.removeAttribute('aria-current');
            if (link) link.setAttribute('aria-current', 'true');
            current = link;

            // keep the marked link visible inside a list that scrolls on its own
            if (link && list && list.scrollHeight > list.clientHeight) {
                var offset = link.offsetTop - list.offsetTop;
                if (offset < list.scrollTop || offset > list.scrollTop + list.clientHeight - link.offsetHeight) {
                    list.scrollTo({ top: offset - list.clientHeight / 2, behavior: 'smooth' });
                }
            }
        }

        var queued = false;
        function queue() {
            if (queued) return;
            queued = true;
            window.requestAnimationFrame(function () { queued = false; update(); });
        }

        window.addEventListener('scroll', queue, { passive: true });
        window.addEventListener('resize', queue);
        update();
    }

    function init() {
        document.querySelectorAll('[data-anchor-index]').forEach(setup);
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
})();
