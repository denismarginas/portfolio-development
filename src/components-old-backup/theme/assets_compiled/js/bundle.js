(function() {
    'use strict';
})();

(function() {
    'use strict';
})();

(function() {
    'use strict';
})();

(function() {
    'use strict';
})();

class CarouselPostItemsMedia {
  static TEMPLATE_URL = 'components/carousel-post-items-media/assets/html/template.html';
  static init() {
  }
}

window.CarouselPostItemsMedia = window.CarouselPostItemsMedia || CarouselPostItemsMedia;

class CarouselPostItemsWeb {
  static TEMPLATE_URL = 'components/carousel-post-items-web/assets/html/template.html';
  static init() {
  }
}

window.CarouselPostItemsWeb = window.CarouselPostItemsWeb || CarouselPostItemsWeb;

class CarouselPostItemsWebDeviceLayouts {
  static TEMPLATE_URL = 'components/carousel-post-items-web-device-layouts/assets/html/template.html';
  static init() {
  }
}

window.CarouselPostItemsWebDeviceLayouts = window.CarouselPostItemsWebDeviceLayouts || CarouselPostItemsWebDeviceLayouts;

(function() {
    'use strict';
})();

class Copyrights {
  static TEMPLATE_URL = 'components/copyrights/assets/html/template.html';
  static render(template, data = {}) {
    return template.replace(/\{\{\s*([\w_]+)\s*\}\}/g, (match, key) => {
      return data[key] !== undefined ? data[key] : '';
    });
  }
}

window.Copyrights = window.Copyrights || Copyrights;

class DevicesPostItemMedia {
  static TEMPLATE_URL = 'components/devices-post-item-media/assets/html/template.html';
  static init() {
  }
}

window.DevicesPostItemMedia = window.DevicesPostItemMedia || DevicesPostItemMedia;

class DevicesPostItemWeb {
  static TEMPLATE_URL = 'components/devices-post-item-web/assets/html/template.html';
  static init() {
  }
}

window.DevicesPostItemWeb = window.DevicesPostItemWeb || DevicesPostItemWeb;

// element-hero component

class ExperienceCategories {
  static TEMPLATE_URL = 'components/experience-categories/assets/html/template.html';
  static init() {
  }
}

window.ExperienceCategories = window.ExperienceCategories || ExperienceCategories;

class Footer {
  static TEMPLATE_URL = 'components/footer/assets/html/template.html';
  static init() {
    // Footer-specific JS can be added here later.
  }
}

window.Footer = window.Footer || Footer;

class FooterLinklist {
  static TEMPLATE_URL = 'components/footer-linklist/assets/html/template.html';
  static render(template, data = {}) {
    return template.replace(/\{\{\s*([\w_]+)\s*\}\}/g, (match, key) => {
      return data[key] !== undefined ? data[key] : '';
    });
  }
}

window.FooterLinklist = window.FooterLinklist || FooterLinklist;

class Header {
  static TEMPLATE_URL = 'components/header/assets/html/template.html';
  static init() {
    const navbarToggle = document.querySelector('.dm-navbar-toggle');
    const navbarToggleSection = document.querySelector('.dm-menu');
    const bodySection = document.body;

    if (navbarToggle && navbarToggleSection) {
      navbarToggle.addEventListener('click', () => {
        navbarToggle.classList.toggle('active');
        navbarToggleSection.classList.toggle('navbar-active');

        if (bodySection.hasAttribute('data-overlay')) {
          bodySection.removeAttribute('data-overlay');
        } else {
          bodySection.setAttribute('data-overlay', 'true');
        }
      });
    }

    const toggle = document.getElementById('toggleTheme');
    if (toggle) {
      const themePreference = localStorage.getItem('theme');
      if (themePreference === 'dark') {
        toggle.checked = true;
        Header.toggleTheme();
      }
      toggle.addEventListener('change', Header.toggleTheme);
    }
  }

  static toggleTheme() {
    const body = document.body;
    const toggle = document.getElementById('toggleTheme');
    const path = document.querySelector('.dm-toggletheme span svg path');

    if (!toggle || !path) return;

    if (toggle.checked) {
      body.classList.remove('theme-light');
      body.classList.add('theme-dark');
      path.style.transition = '0.3s ease-in-out';
      path.setAttribute('d', 'M15 22.1C11 22.1 7.79999 18.9 7.79999 15C7.79999 11 11 7.79999 15 7.79999C18.9 7.79999 12 10.5 12 15C12 19.5 18.9 22.1 15 22.1Z');
      localStorage.setItem('theme', 'dark');
    } else {
      body.classList.remove('theme-dark');
      body.classList.add('theme-light');
      path.style.transition = '0.3s ease-in-out';
      path.setAttribute('d', 'M15 22.1C11 22.1 7.79999 18.9 7.79999 15C7.79999 11 11 7.79999 15 7.79999C18.9 7.79999 22.1 11 22.1 15C22.1 18.9 18.9 22.1 15 22.1Z');
      localStorage.setItem('theme', 'light');
    }
  }
}

window.Header = window.Header || Header;

class HeaderLogo {
  static TEMPLATE_URL = 'components/header-logo/assets/html/template.html';
  static render(template, data = {}) {
    return template.replace(/\{\{\s*([\w_]+)\s*\}\}/g, (match, key) => {
      return data[key] !== undefined ? data[key] : '';
    });
  }
}

window.HeaderLogo = window.HeaderLogo || HeaderLogo;

class HeaderMenu {
  static TEMPLATE_URL = 'components/header-menu/assets/html/template.html';
  static init() {
    const navbarToggle = document.querySelector('.dm-navbar-toggle');
    const navbarToggleSection = document.querySelector('.dm-menu');
    const bodySection = document.body;

    if (navbarToggle && navbarToggleSection) {
      navbarToggle.addEventListener('click', () => {
        navbarToggle.classList.toggle('active');
        navbarToggleSection.classList.toggle('navbar-active');

        if (bodySection.hasAttribute('data-overlay')) {
          bodySection.removeAttribute('data-overlay');
        } else {
          bodySection.setAttribute('data-overlay', 'true');
        }
      });
    }
  }
}

window.HeaderMenu = window.HeaderMenu || HeaderMenu;

// jobs-graph component

class KnowledgeListIcons {
  static TEMPLATE_URL = 'components/knowledge-list-icons/assets/html/template.html';
  static init() {
  }
}

window.KnowledgeListIcons = window.KnowledgeListIcons || KnowledgeListIcons;

function renderPopup() {
  var popup = document.createElement("div");
  popup.id = "popup";
  popup.className = "dm-popup";
  popup.setAttribute("data-motion", "transition-fade-0");

  var content = document.createElement("div");
  content.className = "popup-content";
  content.setAttribute("data-motion", "transition-fade-0");

  var closeBtn = document.createElement("button");
  closeBtn.className = "popup-close-button";
  closeBtn.innerHTML = "<svg data-svg-type='stroke' width='14' height='14' viewBox='0 0 14 14'><path d='M13 1L1 13M1 1L13 13' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'></path></svg>";

  content.appendChild(closeBtn);
  popup.appendChild(content);

  return popup;
}

function openPopup(content, triggerElement) {
  if (document.querySelector("#popup")) return;

  var popup = renderPopup();
  var popupContent = popup.querySelector(".popup-content");

  var clickedElement = triggerElement;
  var initialElement = triggerElement;
  var slider = null;

  while (clickedElement) {
    if (
      clickedElement.parentElement &&
      clickedElement.parentElement.classList.contains("slider-element")
    ) {
      slider = clickedElement.closest(".slider");
      if (slider) break;
    }
    clickedElement = clickedElement.parentElement;
    if (!clickedElement) break;
  }

  if (slider) {
    var clonedSlider = slider.cloneNode(true);
    popupContent.appendChild(clonedSlider);

    ["prev", "next", "dot-section"].forEach(function (className) {
      Array.from(popupContent.getElementsByClassName(className)).forEach(function (el) {
        el.remove();
      });
    });

    var sliderElements = Array.from(clonedSlider.querySelectorAll(".slider-element"));
    var initialItemIndex = sliderElements.findIndex(function (el) {
      return el.outerHTML.includes(initialElement.outerHTML);
    });

    clonedSlider.querySelectorAll("img").forEach(function (image) {
      image.removeAttribute("data-popup");
    });

    var initialIndex = initialItemIndex !== -1 ? initialItemIndex : 0;
    initSlider(clonedSlider, initialIndex);
  } else if (
    initialElement &&
    initialElement.hasAttribute("data-slider-item") &&
    initialElement.getAttribute("data-slider-item") === "true"
  ) {
    var sliderItemsQueryAttr = initialElement.getAttribute("data-slider-item-query-attr");
    var sliderItemsQuerySrc = initialElement.getAttribute("data-slider-items-src");

    var sliderContainer = initialElement.closest("[data-slider-container-src=\"" + sliderItemsQuerySrc + "\"]");
    if (!sliderContainer) {
      sliderContainer = initialElement.closest("[data-slider-container-src]");
    }

    if (sliderContainer && sliderItemsQueryAttr) {
      var sliderElements = Array.from(sliderContainer.querySelectorAll("[data-slider-item-query-attr=" + sliderItemsQueryAttr + "]"));

      slider = renderSlider(sliderElements, true, false, true);

      if (slider) {
        slider.setAttribute("data-slider-container-src", sliderItemsQuerySrc);
        popupContent.appendChild(slider);

        var sliderElementNodes = Array.from(slider.querySelectorAll(".slider-element"));
        var initialItemIndex = sliderElementNodes.findIndex(function (el) {
          return el.outerHTML.includes(initialElement.outerHTML);
        });

        initSlider(slider, initialItemIndex !== -1 ? initialItemIndex : 0);
      }
    }
  } else {
    popupContent.appendChild(content.cloneNode(true));
  }

  popupContent.appendChild(popup.querySelector(".popup-close-button"));
  document.body.appendChild(popup);

  setTimeout(function () {
    animationShowPopup(popupContent, popup);
  }, 1);

  var popupImages = popupContent.querySelectorAll("img");
  popupImages.forEach(function (imgElement) {
    imgElement.addEventListener("click", function (event) {
      toggleZoom(imgElement, event);
    });
  });

  popup.querySelector(".popup-close-button").addEventListener("click", function () {
    closePopup(popup);
  });
}

function closePopup(popup) {
  var popupContent = popup.querySelector(".popup-content");
  if (popupContent && popup) {
    popupContent.setAttribute("data-motion", "transition-fade-0");
    popup.setAttribute("data-motion", "transition-fade-0");
  }
  setTimeout(function () {
    if (popup.parentNode) {
      popup.parentNode.removeChild(popup);
    }
  }, 300);
}

function animationShowPopup(popupContent, popupElement) {
  if (popupContent && popupElement) {
    popupContent.setAttribute("data-motion", "transition-fade-1");
    popupElement.setAttribute("data-motion", "transition-fade-1");
  }
}

function toggleZoom(imageElement, event) {
  var zoomLevel = parseInt(imageElement.dataset.zoomLevel || 0);
  zoomLevel++;

  var rect = imageElement.getBoundingClientRect();
  var mouseX = event.clientX - rect.left;
  var mouseY = event.clientY - rect.top;

  imageElement.style.transformOrigin = (mouseX / rect.width) * 100 + "% " + (mouseY / rect.height) * 100 + "%";

  var imgWidth = imageElement.naturalWidth;
  var imgHeight = imageElement.naturalHeight;
  var sliderElement = imageElement.closest(".slider-element");

  if (zoomLevel === 1) {
    imageElement.style.transform = "scale(1.25)";
  } else if (zoomLevel === 2) {
    imageElement.style.transform = "scale(1.5)";
  } else if (zoomLevel === 3) {
    imageElement.style.transform = "scale(2)";
  } else if (
    zoomLevel === 4 &&
    imgWidth > 1200 &&
    imgHeight > imgWidth * 1.6 &&
    imgHeight > 3000 &&
    sliderElement
  ) {
    sliderElement.classList.add("full-size");
    sliderElement.scrollLeft = (sliderElement.scrollWidth - sliderElement.clientWidth) / 2;
  } else {
    imageElement.style.transform = "scale(1)";
    zoomLevel = 0;

    if (sliderElement) {
      sliderElement.classList.remove("full-size");
    }
  }

  imageElement.dataset.zoomLevel = zoomLevel;
}

function initPopupSystem() {
  document.addEventListener("click", function (event) {
    var target = event.target;

    if (
      target.getAttribute("data-popup") === "true" ||
      (target.closest && target.closest("[data-popup='true']"))
    ) {
      var imgElement = target.tagName.toLowerCase() === "img"
        ? target
        : target.querySelector("img");

      if (imgElement) {
        openPopup(imgElement.cloneNode(), target);
      } else {
        openPopup(target.cloneNode(true), target);
      }
    }
  });
}

document.addEventListener("DOMContentLoaded", function () {
  initPopupSystem();
});

// post-search-bar component









class SocialsButtons {
  static TEMPLATE_URL = 'components/socials-buttons/assets/html/template.html';
  static render(template, data = {}) {
    return template.replace(/\{\{\s*([\w_]+)\s*\}\}/g, (match, key) => {
      return data[key] !== undefined ? data[key] : '';
    });
  }
}

window.SocialsButtons = window.SocialsButtons || SocialsButtons;

class Video {
  static TEMPLATE_URL = 'components/video/assets/html/template.html';
  static #compile(templateStr, data) {
    return templateStr.replace(/\{\{\s*([\w.]+)\s*\}\}/g, (match, key) => {
      return data[key] !== undefined ? data[key] : "";
    });
  }

  static async render(container, data, templateUrl = Video.TEMPLATE_URL) {
    const targetElement =
      typeof container === "string"
        ? document.querySelector(container)
        : container;
    if (!targetElement) return null;

    try {
      const response = await fetch(templateUrl);
      if (!response.ok) return null;
      let html = await response.text();

      let thumbnailSection = "";
      if (data.thumbnail) {
        const match = html.match(
          /<template id="video-thumbnail-template">([\s\S]*?)<\/template>/,
        );
        if (match && match[1]) {
          const bgStyle = data.thumbnail_bg
            ? `style="background-image: url('${data.thumbnail_bg}')"`
            : "";
          const imageHtml = `<img src="${data.thumbnail}" alt="Thumbnail" />`;

          thumbnailSection = this.#compile(match[1], {
            thumbnail_bg_style: bgStyle,
            thumbnail_image: imageHtml,
            play_svg: data.play_svg || "",
            pause_svg: data.pause_svg || "",
          });
        }
      }

      html = html.replace(
        /<template id="video-thumbnail-template">[\s\S]*?<\/template>/,
        "",
      );

      targetElement.innerHTML = this.#compile(html, {
        src: data.src || "",
        thumbnail_section: thumbnailSection,
      });

      return targetElement.querySelector(".video-container");
    } catch (error) {
      console.error(error);
      return null;
    }
  }
}

window.Video = window.Video || Video;

class VideoControls {
  constructor(videoContainer) {
    this.container = videoContainer;
    this.init();
  }

  static initAll() {
    const containers = document.querySelectorAll(
      ".video-container:not([data-video-initialized])",
    );

    containers.forEach((container) => {
      container.setAttribute("data-video-initialized", "true");
      new VideoControls(container);
    });
  }

  init() {
    const playPauseBtn = this.container.querySelector(".play-pause-btn");
    const fullScreenBtn = this.container.querySelector(".full-screen-btn");
    const muteBtn = this.container.querySelector(".mute-btn");
    const speedBtn = this.container.querySelector(".speed-btn");
    const currentTimeElem = this.container.querySelector(".current-time");
    const totalTimeElem = this.container.querySelector(".total-time");
    const volumeSlider = this.container.querySelector(".volume-slider");
    const timelineContainer = this.container.querySelector(
      ".timeline-container",
    );
    const video = this.container.querySelector("video");
    const controlsContainer = this.container.querySelector(
      ".video-controls-container",
    );
    const showPlay = this.container.querySelector(".show-play");
    const showPause = this.container.querySelector(".show-pause");

    if (!video) return;

    if (showPause) showPause.style.display = "none";
    let isFirstPlay = true;
    let isScrubbing = false;
    let wasPaused;
    let lastVolume = 0.5;
    let playPromise = null;

    if (volumeSlider) volumeSlider.value = lastVolume;

    const self = this;

    document.addEventListener("keydown", (e) => {
      const tagName = document.activeElement.tagName.toLowerCase();
      if (tagName === "input") return;

      const isCurrentVideoFocused =
        document.activeElement === video ||
        self.container.contains(document.activeElement);

      if (!isCurrentVideoFocused) return;

      switch (e.key.toLowerCase()) {
        case " ":
          if (tagName === "button") return;
          e.preventDefault();
        case "k":
          togglePlay();
          break;
        case "f":
          toggleFullScreenMode();
          break;
        case "m":
          toggleMute();
          break;
        case "arrowleft":
        case "j":
          skip(-5);
          break;
        case "arrowright":
        case "l":
          skip(5);
          break;
      }
    });

    video.addEventListener("play", () => {
      if (isFirstPlay && controlsContainer && showPlay && showPause) {
        controlsContainer.style.display = "block";
        showPlay.style.display = "none";
        showPause.style.display = "flex";
        isFirstPlay = false;
      }
    });

    if (timelineContainer) {
      timelineContainer.addEventListener("mousemove", handleTimelineUpdate);
      timelineContainer.addEventListener("mousedown", toggleScrubbing);
      timelineContainer.addEventListener("click", (e) => e.stopPropagation());
    }

    document.addEventListener("mouseup", (e) => {
      if (isScrubbing) toggleScrubbing(e);
    });

    document.addEventListener("mousemove", (e) => {
      if (isScrubbing) handleTimelineUpdate(e);
    });

    function safePause() {
      if (playPromise !== null) {
        playPromise.then(() => video.pause()).catch(() => {});
      } else {
        video.pause();
      }
    }

    function safePlay() {
      playPromise = video.play();
      if (playPromise !== null) {
        playPromise.catch(() => {});
      }
    }

    function toggleScrubbing(e) {
      if (!timelineContainer) return;

      const rect = timelineContainer.getBoundingClientRect();
      if (rect.width === 0) return;

      const percent =
        Math.min(Math.max(0, e.clientX - rect.x), rect.width) / rect.width;

      isScrubbing = (e.buttons & 1) === 1;
      self.container.classList.toggle("scrubbing", isScrubbing);

      if (isScrubbing) {
        wasPaused = video.paused;
        safePause();
      } else {
        if (
          !isNaN(video.duration) &&
          isFinite(video.duration) &&
          !isNaN(percent) &&
          isFinite(percent)
        ) {
          video.currentTime = percent * video.duration;
        }

        if (!wasPaused) {
          safePlay();
        }
      }
      handleTimelineUpdate(e);
    }

    function handleTimelineUpdate(e) {
      if (!timelineContainer) return;
      const rect = timelineContainer.getBoundingClientRect();
      if (rect.width === 0) return;
      const percent =
        Math.min(Math.max(0, e.clientX - rect.x), rect.width) / rect.width;
      timelineContainer.style.setProperty("--preview-position", percent);
      if (isScrubbing) {
        timelineContainer.style.setProperty("--progress-position", percent);
      }
    }

    if (speedBtn) speedBtn.addEventListener("click", changePlaybackSpeed);

    function changePlaybackSpeed() {
      let newPlaybackRate = video.playbackRate + 0.25;
      if (newPlaybackRate > 2) newPlaybackRate = 0.25;
      video.playbackRate = newPlaybackRate;
      speedBtn.textContent = `${newPlaybackRate}x`;
    }

    video.addEventListener("loadeddata", () => {
      if (totalTimeElem)
        totalTimeElem.textContent = formatDuration(video.duration);
    });

    video.addEventListener("timeupdate", () => {
      if (isScrubbing) return;
      if (currentTimeElem)
        currentTimeElem.textContent = formatDuration(video.currentTime);
      const percent = video.currentTime / video.duration;
      if (timelineContainer)
        timelineContainer.style.setProperty("--progress-position", percent);
    });

    const leadingZeroFormatter = new Intl.NumberFormat(undefined, {
      minimumIntegerDigits: 2,
    });

    function formatDuration(time) {
      if (isNaN(time)) return "00:00";
      const seconds = Math.floor(time % 60);
      const minutes = Math.floor(time / 60) % 60;
      const hours = Math.floor(time / 3600);

      if (hours === 0) {
        return `${minutes}:${leadingZeroFormatter.format(seconds)}`;
      } else {
        return `${hours}:${leadingZeroFormatter.format(minutes)}:${leadingZeroFormatter.format(seconds)}`;
      }
    }

    function skip(duration) {
      video.currentTime += duration;
    }

    if (muteBtn) muteBtn.addEventListener("click", toggleMute);
    if (volumeSlider) {
      volumeSlider.addEventListener("input", (e) => {
        video.volume = e.target.value;
        video.muted = e.target.value == 0;
        updateSliderBackground();
      });
    }

    function toggleMute() {
      video.muted = !video.muted;

      if (video.muted) {
        lastVolume = video.volume > 0 ? video.volume : lastVolume;
        video.volume = 0;
        if (volumeSlider) volumeSlider.value = 0;
        updateSliderBackground();
      } else {
        video.volume = lastVolume;
        if (volumeSlider) volumeSlider.value = lastVolume;
        updateSliderBackground();
      }
    }

    video.addEventListener("volumechange", () => {
      if (!video.muted && video.volume > 0) {
        lastVolume = video.volume;
      }
      updateSliderBackground();
    });

    function updateSliderBackground() {
      if (!volumeSlider) return;
      const percentage = (volumeSlider.value / volumeSlider.max) * 100;
      volumeSlider.style.background = `linear-gradient(to right, var(--color-range-primary) ${percentage}%, transparent ${percentage}%)`;
      let volumeLevel;

      if (video.muted || video.volume === 0) {
        volumeSlider.value = 0;
        volumeLevel = "muted";
      } else if (video.volume >= 0.5) {
        volumeLevel = "high";
      } else {
        volumeLevel = "low";
      }

      self.container.dataset.volumeLevel = volumeLevel;
    }

    if (fullScreenBtn)
      fullScreenBtn.addEventListener("click", toggleFullScreenMode);

    function toggleFullScreenMode() {
      if (document.fullscreenElement == null) {
        self.container.requestFullscreen();
      } else {
        document.exitFullscreen();
      }
    }

    document.addEventListener("fullscreenchange", () => {
      self.container.classList.toggle(
        "full-screen",
        document.fullscreenElement === self.container,
      );
    });

    if (playPauseBtn) playPauseBtn.addEventListener("click", togglePlay);
    video.addEventListener("click", togglePlay);
    if (showPlay) showPlay.addEventListener("click", togglePlay);
    if (showPause) showPause.addEventListener("click", togglePlay);

    function togglePlay() {
      if (video.paused) {
        safePlay();
        self.container.classList.remove("paused");
        const thumbnailImg = self.container.querySelector(".thumbnail");
        if (thumbnailImg) thumbnailImg.style.display = "none";
      } else {
        if (playPromise !== null) {
          playPromise
            .then(() => {
              video.pause();
              self.container.classList.add("paused");
            })
            .catch(() => {});
        } else {
          video.pause();
          self.container.classList.add("paused");
        }
      }
    }

    video.addEventListener("play", () => {
      self.container.classList.remove("paused");
    });
    video.addEventListener("pause", () => {
      self.container.classList.add("paused");
    });

    updateSliderBackground();
  }
}

window.VideoControls = window.VideoControls || VideoControls;

document.addEventListener("DOMContentLoaded", () => {
  VideoControls.initAll();
});

// video-component component

class WorkstationAccessories {
  static TEMPLATE_URL = 'components/workstation-accessories/assets/html/template.html';
  static init() {
  }
}

window.WorkstationAccessories = window.WorkstationAccessories || WorkstationAccessories;

class WorkstationConfiguration {
  static TEMPLATE_URL = 'components/workstation-configuration/assets/html/template.html';
  static init() {
  }
}

window.WorkstationConfiguration = window.WorkstationConfiguration || WorkstationConfiguration;

class WorkstationHeader {
  static TEMPLATE_URL = 'components/workstation-header/assets/html/template.html';
  static init() {
  }
}

window.WorkstationHeader = window.WorkstationHeader || WorkstationHeader;

// workstation-product-card component

// workstation-videos component

