(function () {
    'use strict';

    var mode = null;
    try {
        var saved = localStorage.getItem("dm_mode_value");
        if (saved === "dark" || saved === "light") {
            mode = saved;
        }
    } catch (e) { }

    if (!mode) {
        mode = document.body.getAttribute("mode") === "dark" ? "dark" : "light";
    }

    document.body.setAttribute("mode", mode);

    var toggle = document.querySelector(".header-theme-mode-toggle");
    if (toggle) {
        toggle.setAttribute("data-mode", mode);
        var input = toggle.querySelector(".header-theme-mode-toggle-input");
        if (input) input.checked = mode === "dark";
        var icons = toggle.querySelectorAll(".header-theme-mode-toggle-icon");
        for (var i = 0; i < icons.length; i++) {
            icons[i].setAttribute("active", icons[i].getAttribute("theme-style") === mode ? "true" : "false");
        }
    }
})();