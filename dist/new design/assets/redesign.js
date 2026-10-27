/* ============================================================
   PORTFOLIO REDESIGN — page behaviour
   Vanilla JS, no dependencies, scoped to the new design pages.
   ============================================================ */
(function () {
  "use strict";

  var reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  /* ---------- scroll progress ---------- */
  var progressBar = document.querySelector(".rd-progress__bar");
  if (progressBar) {
    var updateProgress = function () {
      var doc = document.documentElement;
      var max = doc.scrollHeight - window.innerHeight;
      var value = max > 0 ? (window.scrollY / max) * 100 : 0;
      progressBar.style.width = Math.min(100, Math.max(0, value)) + "%";
    };
    updateProgress();
    window.addEventListener("scroll", updateProgress, { passive: true });
    window.addEventListener("resize", updateProgress);
  }

  /* ---------- reveal on scroll ---------- */
  var revealItems = document.querySelectorAll(".rd-reveal");
  if (revealItems.length) {
    if (reduceMotion || !("IntersectionObserver" in window)) {
      revealItems.forEach(function (el) { el.classList.add("is-in"); });
    } else {
      var revealObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add("is-in");
            revealObserver.unobserve(entry.target);
          }
        });
      }, { rootMargin: "0px 0px -8% 0px", threshold: 0.08 });

      revealItems.forEach(function (el) { revealObserver.observe(el); });
    }
  }

  /* ---------- lightbox ---------- */
  var lightbox = document.querySelector("[data-lightbox]");
  var lbImg = lightbox ? lightbox.querySelector("[data-lb-img]") : null;
  var lbVideo = lightbox ? lightbox.querySelector("[data-lb-video-el]") : null;
  var lbCaption = lightbox ? lightbox.querySelector("[data-lb-caption]") : null;
  var lbCount = lightbox ? lightbox.querySelector("[data-lb-count]") : null;
  var lbGroup = [];
  var lbIndex = 0;

  function visibleItems(group) {
    return Array.prototype.filter.call(
      group.querySelectorAll("[data-lb-src], [data-lb-video]"),
      function (el) {
        return !el.hasAttribute("hidden") && el.offsetParent !== null;
      }
    );
  }

  function renderLightbox() {
    var el = lbGroup[lbIndex];
    if (!el) return;
    var videoSrc = el.getAttribute("data-lb-video");
    var caption = el.getAttribute("data-lb-caption") || "";

    if (videoSrc && lbVideo) {
      lbImg.hidden = true;
      lbImg.removeAttribute("src");
      lbVideo.hidden = false;
      if (lbVideo.getAttribute("src") !== videoSrc) lbVideo.src = videoSrc;
      var poster = el.getAttribute("data-lb-poster");
      if (poster) lbVideo.setAttribute("poster", poster);
      else lbVideo.removeAttribute("poster");
      var playPromise = lbVideo.play();
      if (playPromise && playPromise.catch) playPromise.catch(function () {});
    } else {
      if (lbVideo) {
        lbVideo.pause();
        lbVideo.hidden = true;
        lbVideo.removeAttribute("src");
        lbVideo.removeAttribute("poster");
        lbVideo.load();
      }
      lbImg.hidden = false;
      lbImg.src = el.getAttribute("data-lb-src");
      lbImg.alt = el.getAttribute("data-lb-alt") || caption || "Project image";
    }

    lbCaption.textContent = caption;
    lbCount.textContent = String(lbIndex + 1).padStart(2, "0") + " / " + String(lbGroup.length).padStart(2, "0");
  }

  function openLightbox(el) {
    var group = el.closest("[data-lightbox-group]");
    if (!group || !lightbox) return;
    lbGroup = visibleItems(group);
    lbIndex = lbGroup.indexOf(el);
    if (lbIndex < 0) lbIndex = 0;
    renderLightbox();
    lightbox.setAttribute("data-open", "true");
    document.body.setAttribute("data-overlay", "true");
    lightbox.querySelector("[data-lb-close]").focus({ preventScroll: true });
  }

  function closeLightbox() {
    if (!lightbox) return;
    lightbox.setAttribute("data-open", "false");
    document.body.removeAttribute("data-overlay");
    lbImg.removeAttribute("src");
    if (lbVideo) {
      lbVideo.pause();
      lbVideo.hidden = true;
      lbVideo.removeAttribute("src");
      lbVideo.removeAttribute("poster");
      lbVideo.load();
    }
  }

  function stepLightbox(dir) {
    if (!lbGroup.length) return;
    lbIndex = (lbIndex + dir + lbGroup.length) % lbGroup.length;
    renderLightbox();
  }

  if (lightbox) {
    document.addEventListener("click", function (event) {
      var trigger = event.target.closest("[data-lb-src]");
      if (trigger && !event.target.closest("[data-video-src]")) {
        event.preventDefault();
        openLightbox(trigger);
        return;
      }
      if (event.target.closest("[data-lb-close]")) closeLightbox();
      if (event.target.closest("[data-lb-prev]")) stepLightbox(-1);
      if (event.target.closest("[data-lb-next]")) stepLightbox(1);
    });

    document.addEventListener("keydown", function (event) {
      if (lightbox.getAttribute("data-open") !== "true") return;
      if (event.key === "Escape") closeLightbox();
      if (event.key === "ArrowLeft") stepLightbox(-1);
      if (event.key === "ArrowRight") stepLightbox(1);
    });

    /* swipe */
    var startX = 0;
    lightbox.addEventListener("touchstart", function (e) {
      startX = e.changedTouches[0].clientX;
    }, { passive: true });
    lightbox.addEventListener("touchend", function (e) {
      var dx = e.changedTouches[0].clientX - startX;
      if (Math.abs(dx) > 55) stepLightbox(dx > 0 ? -1 : 1);
    }, { passive: true });
  }

  /* ---------- video modal ---------- */
  var vmodal = document.querySelector("[data-vmodal]");
  if (vmodal) {
    var vEl = vmodal.querySelector("video");
    var vClose = vmodal.querySelector("[data-vmodal-close]");

    function closeVideo() {
      vmodal.setAttribute("data-open", "false");
      document.body.removeAttribute("data-overlay");
      vEl.pause();
      vEl.removeAttribute("src");
      vEl.load();
    }

    document.addEventListener("click", function (event) {
      var trigger = event.target.closest("[data-video-src]");
      if (trigger) {
        event.preventDefault();
        vEl.src = trigger.getAttribute("data-video-src");
        var poster = trigger.getAttribute("data-video-poster");
        if (poster) vEl.setAttribute("poster", poster);
        vmodal.setAttribute("data-open", "true");
        document.body.setAttribute("data-overlay", "true");
        var playPromise = vEl.play();
        if (playPromise && playPromise.catch) playPromise.catch(function () {});
        return;
      }
      if (event.target.closest("[data-vmodal-close]") || event.target.closest("[data-vmodal-backdrop]")) {
        closeVideo();
      }
    });

    document.addEventListener("keydown", function (event) {
      if (event.key === "Escape" && vmodal.getAttribute("data-open") === "true") closeVideo();
    });

    if (vClose) vClose.addEventListener("click", closeVideo);
  }

  /* ---------- agramix paired screens stage ---------- */
  var axShow = document.querySelector("[data-ax-show]");
  if (axShow) {
    var axSource = axShow.querySelector("[data-ax-source]");
    var axItems = axSource
      ? Array.prototype.slice.call(axSource.querySelectorAll(".project-media-gallery-item"))
      : [];
    var axStage = axShow.querySelector("[data-ax-stage]");
    var axStageDesktop = axShow.querySelector("[data-ax-stage-desktop]");
    var axStagePhone = axShow.querySelector("[data-ax-stage-phone]");
    var axThumbsWrap = axShow.querySelector("[data-ax-thumbs]");
    var axTitle = axShow.querySelector("[data-ax-title]");
    var axSub = axShow.querySelector("[data-ax-sub]");
    var axCounter = axShow.querySelector("[data-ax-counter]");
    var axActive = 0;
    var axThumbs = [];

    // Curated desktop ↔ mobile pairs. Indices refer to the 14 items above:
    // 0–10 desktop, 11–13 mobile.
    var axDesktopPair = [11, 11, 12, 12, 13, 13, 13, 12, 12, 11, 13];
    var axPhonePair = [0, 3, 4];
    var axDesktopCount = 11;

    function axIsPhone(item) {
      return item.getAttribute("data-device") === "phone";
    }

    function axTitleOf(item) {
      var tag = item.querySelector(".ax-device-tag");
      var text = tag ? tag.textContent : "";
      return text.replace(/^\s*\d+\s*/, "").trim() || "Screen";
    }

    function axShotOf(item) {
      var img = item.querySelector(".screen img") || item.querySelector("img");
      return img ? img.getAttribute("src") : "";
    }

    function axCloneDevice(index, target) {
      var item = axItems[index];
      if (!item || !target) return;
      var view = item.querySelector(".dm-post-view");
      if (!view) return;
      var clone = view.cloneNode(true);
      clone.querySelectorAll("img").forEach(function (img) {
        img.removeAttribute("loading");
        img.setAttribute("decoding", "async");
      });
      target.innerHTML = "";
      target.appendChild(clone);
    }

    function axSelect(index, scrollThumb) {
      if (!axItems.length) return;
      axActive = (index + axItems.length) % axItems.length;

      var active = axItems[axActive];
      var phoneView = axIsPhone(active);
      var desktopIndex = phoneView ? axPhonePair[axActive - axDesktopCount] : axActive;
      var phoneIndex = phoneView ? axActive : axDesktopPair[axActive];
      if (typeof desktopIndex !== "number") desktopIndex = 0;
      if (typeof phoneIndex !== "number") phoneIndex = axItems.length - 1;

      axCloneDevice(desktopIndex, axStageDesktop);
      axCloneDevice(phoneIndex, axStagePhone);

      var counterpart = phoneView ? axItems[desktopIndex] : axItems[phoneIndex];
      if (axTitle) axTitle.textContent = axTitleOf(active);
      if (axSub) {
        axSub.textContent = (phoneView ? "Mobile view" : "Desktop view") +
          " · paired with " + axTitleOf(counterpart);
      }
      if (axCounter) {
        axCounter.textContent = String(axActive + 1).padStart(2, "0") +
          " / " + String(axItems.length).padStart(2, "0");
      }

      axThumbs.forEach(function (thumb, i) {
        var on = i === axActive;
        thumb.setAttribute("data-active", on ? "true" : "false");
        thumb.setAttribute("aria-pressed", on ? "true" : "false");
      });

      var activeThumb = axThumbs[axActive];
      if (activeThumb && scrollThumb !== false) {
        activeThumb.scrollIntoView({
          block: "nearest",
          inline: "center",
          behavior: reduceMotion ? "auto" : "smooth"
        });
      }
    }

    if (axThumbsWrap) {
      axItems.forEach(function (item, i) {
        var phone = axIsPhone(item);
        var btn = document.createElement("button");
        btn.type = "button";
        btn.className = "ax-screenshow__thumb";
        btn.setAttribute("data-device", phone ? "phone" : "desktop");
        btn.setAttribute("aria-label", "Show " + axTitleOf(item) + " (" + (phone ? "mobile" : "desktop") + ")");
        btn.setAttribute("aria-pressed", "false");

        // thumbnail = the same device mockup as the gallery item (laptop / phone frame)
        var shot = document.createElement("span");
        shot.className = "ax-screenshow__thumb-shot";
        var view = item.querySelector(".dm-post-view");
        if (view) {
          var mock = view.cloneNode(true);
          mock.querySelectorAll("img").forEach(function (im) {
            im.setAttribute("loading", "lazy");
            im.setAttribute("decoding", "async");
            im.alt = "";
          });
          if (phone) {
            var scr = mock.querySelector(".screen");
            if (scr) {
              scr.classList.remove("bg-white");
              scr.classList.add("bg-primary", "fade-under");
            }
          }
          shot.appendChild(mock);
        }

        var meta = document.createElement("span");
        meta.className = "ax-screenshow__thumb-meta";
        var num = document.createElement("b");
        num.textContent = String(i + 1).padStart(2, "0");
        var type = document.createElement("span");
        type.textContent = phone ? "Mobile" : "Desktop";
        meta.appendChild(num);
        meta.appendChild(type);

        btn.appendChild(shot);
        btn.appendChild(meta);
        btn.addEventListener("click", function () { axSelect(i); });
        axThumbsWrap.appendChild(btn);
        axThumbs.push(btn);
      });
    }

    // fade the rail edges only on the side(s) where more thumbs are hidden
    var axThumbsOuter = axShow.querySelector("[data-ax-thumbs-wrap]");
    function axUpdateFade() {
      if (!axThumbsWrap || !axThumbsOuter) return;
      var max = axThumbsWrap.scrollWidth - axThumbsWrap.clientWidth;
      var x = axThumbsWrap.scrollLeft;
      axThumbsOuter.setAttribute("data-fade-left", x > 4 ? "true" : "false");
      axThumbsOuter.setAttribute("data-fade-right", x < max - 4 ? "true" : "false");
    }
    if (axThumbsWrap) {
      axThumbsWrap.addEventListener("scroll", axUpdateFade, { passive: true });
      window.addEventListener("resize", axUpdateFade);
      window.addEventListener("load", axUpdateFade);
    }

    axShow.querySelectorAll("[data-ax-prev]").forEach(function (btn) {
      btn.addEventListener("click", function () { axSelect(axActive - 1); });
    });

    axShow.querySelectorAll("[data-ax-next]").forEach(function (btn) {
      btn.addEventListener("click", function () { axSelect(axActive + 1); });
    });

    var axOpen = axShow.querySelector("[data-ax-open]");
    if (axOpen) {
      axOpen.addEventListener("click", function () {
        if (axItems[axActive]) axItems[axActive].click();
      });
    }

    if (axStage) {
      axStage.addEventListener("click", function (event) {
        if (event.target.closest("[data-ax-open]")) return;
        if (axItems[axActive]) axItems[axActive].click();
      });
    }

    axSelect(0, false);
    axUpdateFade();
  }

  /* ---------- optional sections + section index ---------- */
  document.querySelectorAll("[data-optional-section]").forEach(function (section) {
    var key = section.getAttribute("data-optional-section");
    var hasContent = key === "photos"
      ? !!section.querySelector("[data-am-grid] .am-tile")
      : key === "videos"
        ? !!section.querySelector("[data-am-playlist] [data-video]")
        : !!section.querySelector("*");
    if (!hasContent) section.hidden = true;
  });

  var sectionNav = document.querySelector(".ax-nav__links");
  if (sectionNav) {
    sectionNav.querySelectorAll('a[href^="#"]').forEach(function (link) {
      var id = link.getAttribute("href").slice(1);
      var target = id ? document.getElementById(id) : null;
      if (!target && id === "minimalist") {
        target = document.getElementById("3d");
        if (target) link.setAttribute("href", "#3d");
      }
      if (!target || target.hidden) link.hidden = true;
    });
  }

  /* ---------- agromir gallery filters ---------- */
  var amGrid = document.querySelector("[data-am-grid]");
  if (amGrid) {
    var tiles = Array.prototype.slice.call(amGrid.querySelectorAll("[data-cat]"));
    var filters = document.querySelectorAll("[data-am-filter]");
    var loadMore = document.querySelector("[data-am-more]");
    var status = document.querySelector("[data-am-status]");
    var showAll = document.querySelector("[data-am-all]");
    var pageSize = 16;
    var limit = pageSize;
    var activeCat = "all";

    function applyGallery() {
      var matching = tiles.filter(function (tile) {
        return activeCat === "all" || tile.getAttribute("data-cat") === activeCat;
      });
      var shown = 0;
      tiles.forEach(function (tile) {
        tile.removeAttribute("data-feature");
        var matches = activeCat === "all" || tile.getAttribute("data-cat") === activeCat;
        if (!matches) {
          tile.hidden = true;
          return;
        }
        if (shown < limit) {
          tile.hidden = false;
          // the first visible frame of the current filter is shown larger
          if (shown === 0) tile.setAttribute("data-feature", "true");
          shown++;
        } else {
          tile.hidden = true;
        }
      });
      if (status) {
        status.textContent = "Showing " + Math.min(shown, matching.length) + " of " + matching.length + " images";
      }
      if (loadMore) {
        loadMore.hidden = shown >= matching.length;
      }
      // "Show all" only makes sense when a lot is still hidden
      if (showAll) {
        showAll.hidden = matching.length - shown <= pageSize;
      }
    }

    filters.forEach(function (filter) {
      filter.addEventListener("click", function () {
        activeCat = filter.getAttribute("data-am-filter");
        limit = pageSize;
        filters.forEach(function (f) {
          f.setAttribute("data-active", f === filter ? "true" : "false");
          f.setAttribute("aria-selected", f === filter ? "true" : "false");
        });
        applyGallery();
      });
    });

    if (loadMore) {
      loadMore.addEventListener("click", function () {
        limit += pageSize;
        applyGallery();
      });
    }

    if (showAll) {
      showAll.addEventListener("click", function () {
        limit = tiles.length;
        applyGallery();
      });
    }

    applyGallery();
  }

  /* ---------- agromir playlist ---------- */
  var playlist = document.querySelector("[data-am-playlist]");
  var mainVideo = document.querySelector("[data-am-video]");
  if (playlist && mainVideo) {
    var playerTitle = document.querySelector("[data-am-video-title]");
    var playerTag = document.querySelector("[data-am-video-tag]");
    var items = playlist.querySelectorAll("[data-video]");
    var detailsBtn = document.querySelector("[data-am-video-details]");
    var ARROW = '<svg viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M2 8h11M9 3.5 13.5 8 9 12.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>';
    var EXPAND = '<svg viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M2.5 6V2.5H6M10 2.5h3.5V6M13.5 10v3.5H10M6 13.5H2.5V10" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>';

    // films with a case study link to it; the rest open large in the video modal
    function updateDetails(item) {
      if (!detailsBtn || !item) return;
      var target = item.getAttribute("data-details");
      if (target && document.querySelector(target)) {
        detailsBtn.setAttribute("href", target);
        detailsBtn.removeAttribute("data-video-src");
        detailsBtn.removeAttribute("data-video-poster");
        detailsBtn.innerHTML = "More details " + ARROW;
      } else {
        detailsBtn.setAttribute("href", "#video");
        detailsBtn.setAttribute("data-video-src", item.getAttribute("data-video") || "");
        detailsBtn.setAttribute("data-video-poster", item.getAttribute("data-poster") || "");
        detailsBtn.innerHTML = "View large " + EXPAND;
      }
    }

    if (detailsBtn) {
      detailsBtn.addEventListener("click", function () { mainVideo.pause(); });
    }

    items.forEach(function (item) {
      item.addEventListener("click", function () {
        items.forEach(function (i) {
          i.setAttribute("data-active", i === item ? "true" : "false");
        });
        mainVideo.pause();
        mainVideo.setAttribute("poster", item.getAttribute("data-poster") || "");
        mainVideo.src = item.getAttribute("data-video");
        mainVideo.load();
        var p = mainVideo.play();
        if (p && p.catch) p.catch(function () {});
        if (playerTitle) playerTitle.textContent = item.getAttribute("data-title") || "";
        if (playerTag) playerTag.textContent = item.getAttribute("data-tag") || "";
        updateDetails(item);
      });
    });
  }

  /* ---------- hero video fallback ---------- */
  var heroVideo = document.querySelector("[data-hero-video]");
  if (heroVideo) {
    var tryPlay = function () {
      var p = heroVideo.play();
      if (p && p.catch) p.catch(function () {});
    };
    if (reduceMotion) {
      heroVideo.removeAttribute("autoplay");
      heroVideo.pause();
    } else {
      tryPlay();
      document.addEventListener("visibilitychange", function () {
        if (document.hidden) heroVideo.pause();
        else tryPlay();
      });
    }
  }

  /* ---------- current year ---------- */
  document.querySelectorAll("[data-year]").forEach(function (el) {
    el.textContent = new Date().getFullYear();
  });
})();
