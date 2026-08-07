(function() {
    'use strict';
})();

(function () {
  const SCROLL_THRESHOLD = 200;

  function initHeaderScroll() {
    const header = document.querySelector("header");

    if (!header) {
      console.warn("Header scroll script: <header> element was not found.");
      return;
    }

    const updateScrolled = () => {
      const isScrolled = window.scrollY > SCROLL_THRESHOLD;
      header.setAttribute("data-scrolled", isScrolled ? "true" : "false");
    };

    updateScrolled();
    window.addEventListener("scroll", updateScrolled, { passive: true });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initHeaderScroll, {
      once: true,
    });
  } else {
    initHeaderScroll();
  }
})();

(function () {
  const TOGGLE_SELECTOR = ".header-menu-navbar-toggle";
  const NAVBAR_SELECTOR = ".header-menu";

  function initHeaderMenu() {
    const toggle = document.querySelector(TOGGLE_SELECTOR);
    const navbar = document.querySelector(NAVBAR_SELECTOR);

    if (!toggle) {
      console.warn("Header menu script: toggle element was not found.");
      return;
    }

    toggle.addEventListener("click", () => {
      const expanded = toggle.getAttribute("aria-expanded") === "true";
      const nextState = expanded ? "false" : "true";

      toggle.setAttribute("aria-expanded", nextState);
      toggle.classList.toggle("active", !expanded);
      document.body.setAttribute("data-overlay", nextState);

      if (navbar) {
        navbar.setAttribute("display-navbar", nextState);
      }
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initHeaderMenu, {
      once: true,
    });
  } else {
    initHeaderMenu();
  }
})();

(function () {
  const STORAGE_KEY = "dm_mode_value";
  const TOGGLE_SELECTOR = ".header-theme-mode-toggle";

  function readMode() {
    try {
      const saved = localStorage.getItem(STORAGE_KEY);
      if (saved === "dark" || saved === "light") {
        return saved;
      }
    } catch (e) {}

    const bodyMode =
      typeof document.body !== "undefined" && document.body
        ? document.body.getAttribute("mode")
        : null;
    return bodyMode === "dark" || bodyMode === "light" ? bodyMode : "light";
  }

  function saveMode(mode) {
    try {
      localStorage.setItem(STORAGE_KEY, mode);
    } catch (e) {}
  }

  function applyMode(mode) {
    if (document.body) {
      document.body.setAttribute("mode", mode);
    }
  }

  function syncIcons(toggle, mode) {
    const icons = toggle.querySelectorAll(".header-theme-mode-toggle-icon");
    icons.forEach((icon) => {
      icon.setAttribute("active", icon.getAttribute("theme-style") === mode ? "true" : "false");
    });
  }

  function initHeaderMode() {
    const toggle = document.querySelector(TOGGLE_SELECTOR);
    const mode = readMode();

    applyMode(mode);

    if (!toggle) return;

    const input = toggle.querySelector(".header-theme-mode-toggle-input");
    input.checked = mode === "dark";
    toggle.setAttribute("data-mode", mode);
    syncIcons(toggle, mode);

    input.addEventListener("change", () => {
      const next = input.checked ? "dark" : "light";
      applyMode(next);
      toggle.setAttribute("data-mode", next);
      syncIcons(toggle, next);
      saveMode(next);
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initHeaderMode, {
      once: true,
    });
  } else {
    initHeaderMode();
  }
})();

class Hero {
  static TEMPLATE_URL = 'components/hero/assets/html/template.html';

  static render({ title = '', description = '', background = '', layout = 'standard', className = 'hero' } = {}) {
    const block = document.createElement('div');
    block.className = className;
    block.dataset.layout = layout;

    const bg = document.createElement('div');
    bg.className = 'hero-img-bg';
    if (background) {
      bg.style.backgroundImage = `url("${background}")`;
    }
    block.appendChild(bg);

    if (title || description) {
      const textContent = document.createElement('div');
      textContent.className = 'container-sm';

      if (title) {
        const heading = document.createElement('h2');
        heading.textContent = title;
        textContent.appendChild(heading);
      }

      if (description) {
        const paragraph = document.createElement('p');
        paragraph.textContent = description;
        textContent.appendChild(paragraph);
      }

      block.appendChild(textContent);
    }

    return block;
  }
}

window.Hero = window.Hero || Hero;

