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
