function initSlider(slider, startIndex) {
  if (!slider || slider.hasAttribute("data-initialized")) return;
  if (startIndex === undefined) startIndex = 0;
  var slides = slider.querySelectorAll(".slider-element");
  var sliderWrapper = slider.querySelector(".slider-wrapper");
  var sliderContainer = slider.querySelector(".slider-container");
  var slideCount = slides.length;
  var navigation = slider.getAttribute("data-navigation");
  var slideIndex = startIndex;

  if (!sliderWrapper || !sliderContainer || !slideCount) return;

  sliderWrapper.style.width = "100%";
  sliderContainer.style.width = slideCount * 100 + "%";

  slides.forEach(function (slide) {
    slide.style.width = 100 / slideCount + "%";
  });

  function goToSlide(index) {
    if (index < 0) index = slideCount - 1;
    if (index >= slideCount) index = 0;
    slideIndex = index;
    updateSlider();
  }

  function updateSlider() {
    sliderContainer.style.transform = "translateX(-" + slideIndex * (100 / slideCount) + "%)";
    updateNumberText();
    updateDots();
    updateActiveSlide();
  }

  function updateActiveSlide() {
    slides.forEach(function (slide, index) {
      slide.classList.toggle("active", index === slideIndex);
    });
  }

  function plusSlides(n) {
    goToSlide(slideIndex + n);
  }

  function currentSlide(n) {
    goToSlide(n);
  }

  function updateNumberText() {
    slides.forEach(function (slide, index) {
      var numberText = slide.querySelector(".number-text");
      if (numberText) {
        numberText.innerText = (index + 1) + " / " + slideCount;
      }
    });
  }

  function updateDots() {
    var dots = slider.querySelectorAll(".dot");
    dots.forEach(function (dot, index) {
      dot.classList.toggle("active", index === slideIndex);
    });
  }

  if (navigation === "arrows" || navigation === "arrows dots") {
    renderArrows(slider);
  }

  if (navigation === "dots" || navigation === "arrows dots") {
    renderDots(slider, slides);
  }

  function renderArrows(container) {
    var prevButton = document.createElement("span");
    prevButton.classList.add("prev");
    var prevSvg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
    prevSvg.setAttribute("data-svg-type", "fill");
    prevSvg.setAttribute("viewBox", "0 0 320 512");
    prevSvg.setAttribute("aria-hidden", "true");
    var prevUse = document.createElementNS("http://www.w3.org/2000/svg", "use");
    prevUse.setAttributeNS("http://www.w3.org/1999/xlink", "href", "#chevron-left");
    prevSvg.appendChild(prevUse);
    prevButton.appendChild(prevSvg);
    prevButton.addEventListener("click", function () {
      plusSlides(-1);
    });
    container.appendChild(prevButton);

    var nextButton = document.createElement("span");
    nextButton.classList.add("next");
    var nextSvg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
    nextSvg.setAttribute("data-svg-type", "fill");
    nextSvg.setAttribute("viewBox", "0 0 320 512");
    nextSvg.setAttribute("aria-hidden", "true");
    var nextUse = document.createElementNS("http://www.w3.org/2000/svg", "use");
    nextUse.setAttributeNS("http://www.w3.org/1999/xlink", "href", "#chevron-right");
    nextSvg.appendChild(nextUse);
    nextButton.appendChild(nextSvg);
    nextButton.addEventListener("click", function () {
      plusSlides(1);
    });
    container.appendChild(nextButton);
  }

  function renderDots(container, slides) {
    var dotSection = document.createElement("div");
    dotSection.classList.add("dot-section");
    slides.forEach(function (_, i) {
      var dot = document.createElement("span");
      dot.classList.add("dot");
      if (i === 0) dot.classList.add("active");
      dot.addEventListener("click", function () {
        currentSlide(i);
      });
      dotSection.appendChild(dot);
    });
    container.appendChild(dotSection);
  }

  goToSlide(startIndex);
  slider.setAttribute("data-initialized", "");
}

function initAllSliders(container) {
  var root = container || document;
  var sliders = root.querySelectorAll(".slider:not([data-initialized])");
  sliders.forEach(function (slider) {
    initSlider(slider);
  });
}

document.addEventListener("DOMContentLoaded", function () {
  initAllSliders();
});
