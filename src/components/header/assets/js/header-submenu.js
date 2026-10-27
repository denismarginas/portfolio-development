/**
 * Header submenus (li.has-submenu > .header-menu-row > a + button.header-submenu-toggle, ul.header-submenu).
 *   Desktop (> 1024px): opens on hover / keyboard focus (CSS); the chevron button also toggles it (.is-open),
 *                       one open at a time, closed by a click outside or Esc.
 *   Mobile drawer (<= 1024px): accordion, the chevron toggles .is-open; the group of the current page starts open.
 * The chevron rotates 180deg while its submenu is open (CSS).
 */
(function () {
  var MOBILE = "(max-width: 1024px)";

  function init() {
    var menu = document.querySelector("header .header-menu");
    if (!menu) return;

    var items = Array.prototype.slice.call(menu.querySelectorAll("li.has-submenu"));
    if (!items.length) return;
    var mobile = window.matchMedia(MOBILE);

    function set(li, open) {
      li.classList.toggle("is-open", open);
      var button = li.querySelector(".header-submenu-toggle");
      if (button) button.setAttribute("aria-expanded", open ? "true" : "false");
    }

    items.forEach(function (li) {
      var button = li.querySelector(".header-submenu-toggle");
      if (!button) return;
      button.addEventListener("click", function (event) {
        event.preventDefault();
        var open = !li.classList.contains("is-open");
        // desktop dropdowns: only one open at a time
        if (!mobile.matches) items.forEach(function (other) { if (other !== li) set(other, false); });
        set(li, open);
      });
    });

    // mobile: the group of the current page starts open
    function openCurrent() {
      items.forEach(function (li) {
        set(li, mobile.matches && li.classList.contains("is-current-parent"));
      });
    }
    openCurrent();
    if (mobile.addEventListener) mobile.addEventListener("change", openCurrent);

    // desktop: click outside closes
    document.addEventListener("click", function (event) {
      if (mobile.matches) return;
      items.forEach(function (li) { if (!li.contains(event.target)) set(li, false); });
    });

    // Esc closes and gives the focus back to the chevron
    document.addEventListener("keydown", function (event) {
      if (event.key !== "Escape") return;
      items.forEach(function (li) {
        if (!li.classList.contains("is-open") || mobile.matches) return;
        var hadFocus = li.contains(document.activeElement);
        set(li, false);
        if (hadFocus) li.querySelector(".header-submenu-toggle").focus();
      });
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init, { once: true });
  } else {
    init();
  }
})();
