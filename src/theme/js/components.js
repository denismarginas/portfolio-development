/* --- animation_blurred_lines/assets/js/animation_blurred_lines.js --- */
(function() {
    'use strict';
})();


/* --- animation_preloader/assets/js/animation_preloader.js --- */
(function() {
    'use strict';
})();


/* --- animation_squares/assets/js/animation_squares.js --- */
(function() {
    'use strict';
})();


/* --- animation_waves/assets/js/animation_waves.js --- */
(function() {
    'use strict';
})();


/* --- carousel_post_items_media/assets/js/carousel_post_items_media.js --- */
class CarouselPostItemsMedia {
  static TEMPLATE_URL = 'components/carousel-post-items-media/assets/html/template.html';
  static init() {
  }
}

window.CarouselPostItemsMedia = window.CarouselPostItemsMedia || CarouselPostItemsMedia;


/* --- carousel_post_items_web/assets/js/carousel_post_items_web.js --- */
class CarouselPostItemsWeb {
  static TEMPLATE_URL = 'components/carousel-post-items-web/assets/html/template.html';
  static init() {
  }
}

window.CarouselPostItemsWeb = window.CarouselPostItemsWeb || CarouselPostItemsWeb;


/* --- carousel_post_items_web_device_layouts/assets/js/carousel_post_items_web_device_layouts.js --- */
class CarouselPostItemsWebDeviceLayouts {
  static TEMPLATE_URL = 'components/carousel-post-items-web-device-layouts/assets/html/template.html';
  static init() {
  }
}

window.CarouselPostItemsWebDeviceLayouts = window.CarouselPostItemsWebDeviceLayouts || CarouselPostItemsWebDeviceLayouts;


/* --- cookie_notice/assets/js/cookie_notice.js --- */
(function() {
    'use strict';
})();


/* --- copyrights/assets/js/copyrights.js --- */
class Copyrights {
  static TEMPLATE_URL = 'components/copyrights/assets/html/template.html';
  static render(template, data = {}) {
    return template.replace(/\{\{\s*([\w_]+)\s*\}\}/g, (match, key) => {
      return data[key] !== undefined ? data[key] : '';
    });
  }
}

window.Copyrights = window.Copyrights || Copyrights;


/* --- devices_post_item_media/assets/js/devices_post_item_media.js --- */
class DevicesPostItemMedia {
  static TEMPLATE_URL = 'components/devices-post-item-media/assets/html/template.html';
  static init() {
  }
}

window.DevicesPostItemMedia = window.DevicesPostItemMedia || DevicesPostItemMedia;


/* --- devices_post_item_web/assets/js/devices_post_item_web.js --- */
class DevicesPostItemWeb {
  static TEMPLATE_URL = 'components/devices-post-item-web/assets/html/template.html';
  static init() {
  }
}

window.DevicesPostItemWeb = window.DevicesPostItemWeb || DevicesPostItemWeb;


/* --- element_hero/assets/js/element_hero.js --- */
// element-hero component


/* --- experience_categories/assets/js/experience_categories.js --- */
class ExperienceCategories {
  static TEMPLATE_URL = 'components/experience-categories/assets/html/template.html';
  static init() {
  }
}

window.ExperienceCategories = window.ExperienceCategories || ExperienceCategories;


/* --- footer/assets/js/footer.js --- */
class Footer {
  static TEMPLATE_URL = 'components/footer/assets/html/template.html';
  static init() {
    // Footer-specific JS can be added here later.
  }
}

window.Footer = window.Footer || Footer;


/* --- footer_linklist/assets/js/footer_linklist.js --- */
class FooterLinklist {
  static TEMPLATE_URL = 'components/footer-linklist/assets/html/template.html';
  static render(template, data = {}) {
    return template.replace(/\{\{\s*([\w_]+)\s*\}\}/g, (match, key) => {
      return data[key] !== undefined ? data[key] : '';
    });
  }
}

window.FooterLinklist = window.FooterLinklist || FooterLinklist;


/* --- header/assets/js/header.js --- */
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


/* --- header_logo/assets/js/header_logo.js --- */
class HeaderLogo {
  static TEMPLATE_URL = 'components/header-logo/assets/html/template.html';
  static render(template, data = {}) {
    return template.replace(/\{\{\s*([\w_]+)\s*\}\}/g, (match, key) => {
      return data[key] !== undefined ? data[key] : '';
    });
  }
}

window.HeaderLogo = window.HeaderLogo || HeaderLogo;


/* --- header_menu/assets/js/header_menu.js --- */
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


/* --- jobs_graph/assets/js/jobs_graph.js --- */
// jobs-graph component


/* --- knowledge_list_icons/assets/js/knowledge_list_icons.js --- */
class KnowledgeListIcons {
  static TEMPLATE_URL = 'components/knowledge-list-icons/assets/html/template.html';
  static init() {
  }
}

window.KnowledgeListIcons = window.KnowledgeListIcons || KnowledgeListIcons;


/* --- post_search_bar/assets/js/post_search_bar.js --- */
// post-search-bar component


/* --- section_404/assets/js/section_404.js --- */


/* --- section_about/assets/js/section_about.js --- */


/* --- section_blog_header/assets/js/section_blog_header.js --- */


/* --- section_blog_posts/assets/js/section_blog_posts.js --- */


/* --- section_carousel_post_items/assets/js/section_carousel_post_items.js --- */


/* --- section_categories/assets/js/section_categories.js --- */


/* --- section_contact_data/assets/js/section_contact_data.js --- */


/* --- section_contact_details/assets/js/section_contact_details.js --- */


/* --- socials_buttons/assets/js/socials_buttons.js --- */
class SocialsButtons {
  static TEMPLATE_URL = 'components/socials-buttons/assets/html/template.html';
  static render(template, data = {}) {
    return template.replace(/\{\{\s*([\w_]+)\s*\}\}/g, (match, key) => {
      return data[key] !== undefined ? data[key] : '';
    });
  }
}

window.SocialsButtons = window.SocialsButtons || SocialsButtons;


/* --- video/assets/js/video.js --- */
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


/* --- video/assets/js/videoControls.js --- */
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


/* --- video_component/assets/js/video_component.js --- */
// video-component component


/* --- workstation_accessories/assets/js/workstation_accessories.js --- */
class WorkstationAccessories {
  static TEMPLATE_URL = 'components/workstation-accessories/assets/html/template.html';
  static init() {
  }
}

window.WorkstationAccessories = window.WorkstationAccessories || WorkstationAccessories;


/* --- workstation_configuration/assets/js/workstation_configuration.js --- */
class WorkstationConfiguration {
  static TEMPLATE_URL = 'components/workstation-configuration/assets/html/template.html';
  static init() {
  }
}

window.WorkstationConfiguration = window.WorkstationConfiguration || WorkstationConfiguration;


/* --- workstation_header/assets/js/workstation_header.js --- */
class WorkstationHeader {
  static TEMPLATE_URL = 'components/workstation-header/assets/html/template.html';
  static init() {
  }
}

window.WorkstationHeader = window.WorkstationHeader || WorkstationHeader;


/* --- workstation_product_card/assets/js/workstation_product_card.js --- */
// workstation-product-card component


/* --- workstation_videos/assets/js/workstation_videos.js --- */
// workstation-videos component


