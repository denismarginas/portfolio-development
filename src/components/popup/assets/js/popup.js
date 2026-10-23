/**
 * popup: modal for images, galleries and any HTML (a [data-popup="true"] trigger with a
 * <template class="popup-template"> inside, rendered by popup.php).
 *
 * Reusable hooks:
 *   - Events on document, so components can set themselves up inside the popup:
 *       popup:open  detail { popup, body, source }   after the content is inserted
 *       popup:close detail { popup, body }           before the content is removed
 *   - data-popup-hash="name" on a trigger: #name in the URL opens it (on load and on hashchange);
 *     opening it sets #name, closing it removes #name again (history.replaceState, no scroll).
 *   - A trigger that is / contains a link opens the popup instead of navigating, unless the
 *     link points to an element of the current page (then the browser simply scrolls to it)
 *     or it is opened in a new tab / window (Ctrl, Cmd, Shift, middle click).
 *   - data-popup-move on an element of the page (usually with data-popup-hash): the element itself
 *     moves into the popup and back to its place on close, so nothing is copied (listeners, state
 *     and ids stay). data-popup-move="selector" moves that element instead.
 *     data-popup-panel / data-popup-width="720px": show it in the scrollable .popup-panel.
 */
(function () {
    'use strict';

    var ESCAPE_KEY = 27;

    function emit(name, detail) {
        document.dispatchEvent(new CustomEvent(name, { detail: detail }));
    }

    function createPopup() {
        var existing = document.getElementById('popup');
        if (existing) return existing;

        var tpl = document.querySelector('.popup-template-source');
        var markup = tpl ? tpl.innerHTML : (
            '<div class="popup-backdrop"></div>' +
            '<button type="button" class="popup-close" aria-label="Close">' +
              '<svg width="14" height="14" viewBox="0 0 14 14"><path d="M13 1L1 13M1 1L13 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>' +
            '</button>' +
            '<div class="popup-content">' +
              '<div class="popup-body"></div>' +
            '</div>'
        );

        var popup = document.createElement('div');
        popup.id = 'popup';
        popup.className = 'popup';
        popup.hidden = true;
        popup.innerHTML = markup;
        document.body.appendChild(popup);
        bindPopupEvents(popup);
        return popup;
    }

    // Post colors (--post-color-*) of the element that opened the popup; the popup
    // lives in <body>, outside the section that declares them.
    var POST_COLORS = ['--post-color-primary', '--post-color-secondary'];

    function applyColors(popup, source) {
        var styles = source ? window.getComputedStyle(source) : null;
        POST_COLORS.forEach(function (name) {
            var value = styles ? styles.getPropertyValue(name).trim() : '';
            if (value) popup.style.setProperty(name, value);
            else popup.style.removeProperty(name);
        });
    }

    /**
     * html: content to insert (string), or options.node: a page element moved in (and back on close).
     * options: { gallery, index, source, hash, node, panel, width }
     */
    function openPopup(html, options) {
        options = options || {};
        var popup = createPopup();
        if (!popup.hidden) closePopup(popup);
        applyColors(popup, options.source || null);
        destroyGallery(popup);
        var body = popup.querySelector('.popup-body');
        body.className = 'popup-body' + (options.gallery ? ' has-gallery' : '');

        if (options.node) {
            body.innerHTML = '';
            body.appendChild(wrapPanel(moveIn(popup, options.node), options));
        } else {
            body.innerHTML = html;
        }

        show(popup);
        if (options.gallery) initGallery(popup, body, parseInt(options.index, 10) || 0);
        initVideos(body);

        popup.dataset.hash = options.hash || '';
        if (options.hash) setHash(options.hash);

        emit('popup:open', { popup: popup, body: body, source: options.source || null });
        return popup;
    }

    function closePopup(popup) {
        if (!popup || popup.hidden) return;
        var body = popup.querySelector('.popup-body');
        emit('popup:close', { popup: popup, body: body });

        destroyGallery(popup);
        popup.hidden = true;
        moveBack(popup);
        if (body) {
            body.innerHTML = '';
            body.classList.remove('has-gallery');
        }

        if (popup.dataset.hash && currentHash() === popup.dataset.hash) setHash('');
        popup.dataset.hash = '';
    }

    // ---------- moved page elements (data-popup-move) ----------
    /** Leaves a placeholder where the element was, so it can go back to the same place. */
    function moveIn(popup, node) {
        var placeholder = document.createComment('popup-move');
        node.parentNode.insertBefore(placeholder, node);
        popup.movedNode = { node: node, placeholder: placeholder };
        return node;
    }

    function moveBack(popup) {
        var moved = popup.movedNode;
        if (!moved) return;
        if (moved.placeholder.parentNode) {
            moved.placeholder.parentNode.insertBefore(moved.node, moved.placeholder);
            moved.placeholder.parentNode.removeChild(moved.placeholder);
        }
        popup.movedNode = null;
    }

    function wrapPanel(node, options) {
        if (!options.panel) return node;
        var panel = document.createElement('div');
        panel.className = 'popup-panel';
        if (options.width) panel.style.setProperty('--popup-panel-width', options.width);
        panel.appendChild(node);
        return panel;
    }

    // ---------- URL hash (data-popup-hash) ----------
    function currentHash() {
        return decodeURIComponent((window.location.hash || '').slice(1));
    }

    function setHash(hash) {
        if (!window.history || !window.history.replaceState) return;
        var url = window.location.pathname + window.location.search + (hash ? '#' + encodeURIComponent(hash) : '');
        window.history.replaceState(window.history.state, '', url);
    }

    function openFromHash() {
        var hash = currentHash();
        if (!hash) return;
        var popup = document.getElementById('popup');
        if (popup && !popup.hidden && popup.dataset.hash === hash) return;

        var triggers = document.querySelectorAll('[data-popup-hash]');
        for (var i = 0; i < triggers.length; i++) {
            if (triggers[i].getAttribute('data-popup-hash') === hash) {
                openTrigger(triggers[i]);
                return;
            }
        }
    }

    // ---------- links inside triggers ----------
    /** true when the link should keep its normal behavior (new tab, or a target already on this page). */
    function linkHandledByBrowser(link, event) {
        if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || event.button > 0) return true;

        var url;
        try { url = new URL(link.href, window.location.href); } catch (e) { return false; }
        var samePage = url.origin === window.location.origin
            && url.pathname === window.location.pathname
            && url.search === window.location.search;
        var id = url.hash ? decodeURIComponent(url.hash.slice(1)) : '';
        return samePage && id !== '' && document.getElementById(id) !== null;
    }

    function show(popup) { popup.hidden = false; }

    /**
     * Gallery = utility_slider (same sliding, arrows and counter as block_slider_images).
     * The slider root is #popup itself, so the arrows / counter sit on the screen edges.
     */
    function initGallery(popup, body, index) {
        var gallery = body.querySelector('[data-popup-gallery]');
        var slides = gallery ? gallery.querySelectorAll('.popup-slide') : [];
        if (!slides.length || !window.UtilitySlider) return;

        var track = document.createElement('div');
        track.className = 'utility-slider-track';
        Array.prototype.forEach.call(slides, function (slide) {
            slide.classList.add('utility-slider-slide');
            track.appendChild(slide);
        });
        gallery.classList.add('utility-slider-viewport');
        gallery.appendChild(track);

        popup.classList.add('popup-has-gallery');
        window.UtilitySlider.init(popup, { viewport: gallery, start: index, arrows: true, counter: true, dots: false });
    }

    /**
     * Copied video players (.video-container) come without their VideoControls listeners:
     * reset them to the paused / thumbnail state and set them up again. Videos on the page are paused.
     */
    function initVideos(body) {
        var players = body.querySelectorAll('.video-container');
        if (!players.length) return;

        Array.prototype.forEach.call(document.querySelectorAll('video'), function (video) {
            if (!body.contains(video) && !video.paused) video.pause();
        });

        Array.prototype.forEach.call(players, function (player) {
            player.removeAttribute('data-video-initialized');
            player.classList.add('paused');
            player.classList.remove('full-screen', 'scrubbing');
            var thumbnail = player.querySelector('.thumbnail');
            if (thumbnail) thumbnail.style.display = '';
            var showPlay = player.querySelector('.show-play');
            if (showPlay) showPlay.style.display = 'flex';
            var showPause = player.querySelector('.show-pause');
            if (showPause) showPause.style.display = 'none';
            var controls = player.querySelector('.video-controls-container');
            if (controls) controls.style.display = '';
        });

        if (window.VideoControls && typeof window.VideoControls.initAll === 'function') window.VideoControls.initAll();
    }

    function destroyGallery(popup) {
        if (popup.utilitySlider) popup.utilitySlider.destroy();
        popup.classList.remove('popup-has-gallery');
    }

    function bindPopupEvents(popup) {
        popup.querySelector('.popup-backdrop').addEventListener('click', function () { closePopup(popup); });
        popup.querySelector('.popup-close').addEventListener('click', function () { closePopup(popup); });

        document.addEventListener('keydown', function (e) {
            if (popup.hidden) return;
            if (e.keyCode === ESCAPE_KEY || e.key === 'Escape') closePopup(popup);
            if (!popup.utilitySlider) return;
            // Arrow keys inside a video player seek the video (VideoControls), not the gallery.
            if (document.activeElement && document.activeElement.closest && document.activeElement.closest('.video-container')) return;
            if (e.key === 'ArrowLeft') { e.preventDefault(); popup.utilitySlider.prev(); }
            if (e.key === 'ArrowRight') { e.preventDefault(); popup.utilitySlider.next(); }
        });

        // Zoom for images inside popup
        popup.addEventListener('click', function (event) {
            var img = event.target.closest('img');
            if (!img || !img.closest('.popup-body')) return;
            toggleZoom(img, event);
        });
    }

    function toggleZoom(img, event) {
        var level = parseInt(img.dataset.zoomLevel || 0, 10) + 1;
        var rect = img.getBoundingClientRect();
        img.style.transformOrigin = ((event.clientX - rect.left) / rect.width) * 100 + '% ' + ((event.clientY - rect.top) / rect.height) * 100 + '%';

        if (level === 1) img.style.transform = 'scale(1.25)';
        else if (level === 2) img.style.transform = 'scale(1.5)';
        else if (level === 3) img.style.transform = 'scale(2)';
        else { img.style.transform = 'scale(1)'; level = 0; }
        img.dataset.zoomLevel = level;
    }

    /** Opens what a [data-popup="true"] trigger points to (template, gallery group, target or own content). */
    function openTrigger(trigger) {
        var hash = trigger.getAttribute('data-popup-hash') || '';

        // Page element moved into the popup (itself, or the data-popup-move selector)
        if (trigger.hasAttribute('data-popup-move')) {
            var selector = trigger.getAttribute('data-popup-move');
            var node = selector ? document.querySelector(selector) : trigger;
            if (!node) return null;
            return openPopup('', {
                node: node,
                source: trigger,
                hash: hash,
                panel: trigger.hasAttribute('data-popup-panel'),
                width: trigger.getAttribute('data-popup-width') || ''
            });
        }

        // Popup component with <template class="popup-template">
        var template = trigger.querySelector('template.popup-template');
        if (template) {
            var galleryEl = template.content.querySelector('[data-popup-gallery]');
            var slides = template.content.querySelectorAll('[data-popup-gallery] .popup-slide');
            return openPopup(template.innerHTML, { gallery: !!galleryEl && slides.length > 1, source: trigger, hash: hash });
        }

        // Gallery group: data-popup-group on multiple elements
        var group = trigger.getAttribute('data-popup-group');
        if (group) {
            var members = Array.prototype.slice.call(document.querySelectorAll('[data-popup-group="' + group + '"]'));
            var idx = members.indexOf(trigger);
            var slidesHtml = members.map(function (m) {
                // Video slide (block_slider_images "videos"): the whole player, set up again in the popup.
                var player = m.querySelector('.video-container');
                if (player) return '<div class="popup-slide popup-slide-video">' + player.outerHTML + '</div>';
                var img = m.tagName === 'IMG' ? m : m.querySelector('img');
                return '<div class="popup-slide">' + (img ? img.outerHTML : m.innerHTML) + '</div>';
            }).join('');
            return openPopup('<div data-popup-gallery>' + slidesHtml + '</div>', { gallery: members.length > 1, index: idx, source: trigger, hash: hash });
        }

        // Simple content: own innerHTML or referenced target
        var targetSel = trigger.getAttribute('data-popup-target');
        var content = '';
        if (targetSel) {
            var t = document.querySelector(targetSel);
            content = t ? t.innerHTML : '';
        } else {
            var img = trigger.tagName === 'IMG' ? trigger : trigger.querySelector('img');
            content = img ? img.outerHTML : trigger.innerHTML;
        }
        return openPopup(content, { source: trigger, hash: hash });
    }

    // Global trigger system: any [data-popup="true"] element
    function initTriggers() {
        document.addEventListener('click', function (event) {
            var trigger = event.target.closest('[data-popup="true"]');
            if (!trigger) return;

            var link = event.target.closest('a[href]');
            if (link && trigger.contains(link)) {
                if (linkHandledByBrowser(link, event)) return;
                event.preventDefault();
            }

            openTrigger(trigger);
        });

        openFromHash();
        window.addEventListener('hashchange', openFromHash);
    }

    document.addEventListener('DOMContentLoaded', initTriggers);

    // Expose minimal API
    window.Popup = {
        open: openPopup,
        close: function (popup) { closePopup(popup || document.getElementById('popup')); },
        openTrigger: openTrigger
    };
})();