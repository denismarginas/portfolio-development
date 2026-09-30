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

    function openPopup(html, options) {
        options = options || {};
        var popup = createPopup();
        applyColors(popup, options.source || null);
        destroyGallery(popup);
        var body = popup.querySelector('.popup-body');
        body.innerHTML = html;
        body.className = 'popup-body' + (options.gallery ? ' has-gallery' : '');

        show(popup);
        if (options.gallery) initGallery(popup, body, parseInt(options.index, 10) || 0);
        return popup;
    }

    function closePopup(popup) {
        if (!popup) return;
        destroyGallery(popup);
        popup.hidden = true;
        var body = popup.querySelector('.popup-body');
        if (body) {
            body.innerHTML = '';
            body.classList.remove('has-gallery');
        }
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
                openPopup(template.innerHTML, { gallery: !!galleryEl && slides.length > 1, source: trigger });
                return;
            }

            // Gallery group: data-popup-group on multiple elements
            var group = trigger.getAttribute('data-popup-group');
            if (group) {
                var members = Array.prototype.slice.call(document.querySelectorAll('[data-popup-group="' + group + '"]'));
                var idx = members.indexOf(trigger);
                var slidesHtml = members.map(function (m) {
                    var img = m.tagName === 'IMG' ? m : m.querySelector('img');
                    return '<div class="popup-slide">' + (img ? img.outerHTML : m.innerHTML) + '</div>';
                }).join('');
                openPopup('<div data-popup-gallery>' + slidesHtml + '</div>', { gallery: members.length > 1, index: idx, source: trigger });
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
            openPopup(content, { source: trigger });
        });
    }

    document.addEventListener('DOMContentLoaded', initTriggers);

    // Expose minimal API
    window.Popup = { open: openPopup, close: closePopup };
})();