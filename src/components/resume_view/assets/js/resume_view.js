/**
 * Opens a resume's big view (popup) when the URL hash is a resume card id,
 * e.g. personal-resumes#cv-3-english. Also reacts to later hash changes (links on the same page).
 * The card must have the view enabled (resume_listing "preview_popup": true, the default).
 */
(function () {
    'use strict';

    function openFromHash() {
        var id = decodeURIComponent((window.location.hash || '').slice(1));
        if (!id) return;

        var card = document.getElementById(id);
        if (!card || !card.matches('[data-resume-view]')) return;

        var trigger = card.querySelector('.resume-view-trigger');
        if (!trigger) return;

        card.scrollIntoView({ block: 'center' });
        trigger.click(); // popup.js listens for clicks on [data-popup="true"]
    }

    // after popup.js has set up its click listener (it does so on DOMContentLoaded)
    function init() { setTimeout(openFromHash, 0); }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();

    window.addEventListener('hashchange', openFromHash);
})();
