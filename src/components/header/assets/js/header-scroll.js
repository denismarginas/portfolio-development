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
