(function () {
    'use strict';

    var ESCAPE_KEY = 27;

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

    function openPopup(html, options) {
        options = options || {};
        var popup = createPopup();
        var body = popup.querySelector('.popup-body');
        body.innerHTML = html;
        body.className = 'popup-body' + (options.gallery ? ' has-gallery' : '');
        if (options.index) body.setAttribute('data-initial-index', options.index);

        if (options.gallery) initGallery(body);
        show(popup);
        return popup;
    }

    function closePopup(popup) {
        if (!popup) return;
        popup.hidden = true;
        var body = popup.querySelector('.popup-body');
        if (body) {
            body.innerHTML = '';
            body.removeAttribute('data-initial-index');
            body.classList.remove('has-gallery');
        }
    }

    function show(popup) { popup.hidden = false; }

    function initGallery(body) {
        var slides = body.querySelectorAll('[data-popup-gallery] .popup-slide');
        if (!slides.length) return;

        var index = parseInt(body.getAttribute('data-initial-index') || '0', 10) || 0;
        showSlide(slides, index);

        var nav = document.createElement('div');
        nav.className = 'popup-nav';

        var prev = document.createElement('button');
        prev.type = 'button';
        prev.className = 'popup-nav-btn prev';
        prev.innerHTML = '&#10094;';
        prev.addEventListener('click', function () {
            index = (index - 1 + slides.length) % slides.length;
            showSlide(slides, index);
        });

        var next = document.createElement('button');
        next.type = 'button';
        next.className = 'popup-nav-btn next';
        next.innerHTML = '&#10095;';
        next.addEventListener('click', function () {
            index = (index + 1) % slides.length;
            showSlide(slides, index);
        });

        var counter = document.createElement('span');
        counter.className = 'popup-counter';

        nav.appendChild(prev);
        nav.appendChild(counter);
        nav.appendChild(next);
        body.appendChild(nav);
        updateCounter(counter, index, slides.length);

        function showSlide(list, i) {
            list.forEach(function (s, idx) { s.style.display = idx === i ? 'flex' : 'none'; });
            var c = body.querySelector('.popup-counter');
            if (c) updateCounter(c, i, list.length);
        }
        function updateCounter(el, i, total) { el.textContent = (i + 1) + ' / ' + total; }
    }

    function bindPopupEvents(popup) {
        popup.querySelector('.popup-backdrop').addEventListener('click', function () { closePopup(popup); });
        popup.querySelector('.popup-close').addEventListener('click', function () { closePopup(popup); });

        document.addEventListener('keydown', function (e) {
            if (popup.hidden) return;
            if (e.keyCode === ESCAPE_KEY || e.key === 'Escape') closePopup(popup);
            if (e.key === 'ArrowLeft') { var p = popup.querySelector('.popup-nav-btn.prev'); if (p) p.click(); }
            if (e.key === 'ArrowRight') { var n = popup.querySelector('.popup-nav-btn.next'); if (n) n.click(); }
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

    // Global trigger system: any [data-popup="true"] element
    function initTriggers() {
        document.addEventListener('click', function (event) {
            var trigger = event.target.closest('[data-popup="true"]');
            if (!trigger) return;

            // Popup component with <template class="popup-template">
            var template = trigger.querySelector('template.popup-template');
            if (template) {
                var galleryEl = template.content.querySelector('[data-popup-gallery]');
                var slides = template.content.querySelectorAll('[data-popup-gallery] .popup-slide');
                openPopup(template.innerHTML, { gallery: !!galleryEl && slides.length > 1 });
                return;
            }

            // Gallery group: data-popup-group on multiple elements
            var group = trigger.getAttribute('data-popup-group');
            if (group) {
                var members = Array.prototype.slice.call(document.querySelectorAll('[data-popup-group="' + group + '"]'));
                var idx = members.indexOf(trigger);
                var slidesHtml = members.map(function (m) {
                    return '<div class="popup-slide">' + (m.querySelector('img') ? m.querySelector('img').outerHTML : m.innerHTML) + '</div>';
                }).join('');
                openPopup('<div data-popup-gallery>' + slidesHtml + '</div>', { gallery: members.length > 1, index: idx });
                return;
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
            openPopup(content);
        });
    }

    document.addEventListener('DOMContentLoaded', initTriggers);

    // Expose minimal API
    window.Popup = { open: openPopup, close: closePopup };
})();