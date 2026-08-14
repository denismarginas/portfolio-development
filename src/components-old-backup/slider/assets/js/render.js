function renderSlider(elements, showArrows, showDots, showNumbers) {
  if (showArrows === undefined) showArrows = true;
  if (showDots === undefined) showDots = true;
  if (showNumbers === undefined) showNumbers = false;
  if (!elements || !elements.length) return null;

  var navClasses = (showArrows ? "arrows" : "") + (showDots ? " dots" : "").trim();

  var slider = document.createElement("div");
  slider.classList.add("slider");
  slider.setAttribute("data-navigation", navClasses);

  var sliderWrapper = document.createElement("div");
  sliderWrapper.classList.add("slider-wrapper");

  var sliderContainer = document.createElement("div");
  sliderContainer.classList.add("slider-container");

  elements.forEach(function (element, index) {
    var sliderElement = document.createElement("div");
    sliderElement.classList.add("slider-element");

    if (showNumbers) {
      var numberText = document.createElement("div");
      numberText.classList.add("number-text");
      numberText.textContent = (index + 1) + " / " + elements.length;
      sliderElement.appendChild(numberText);
    }

    sliderElement.appendChild(element.cloneNode(true));
    sliderContainer.appendChild(sliderElement);
  });

  sliderWrapper.appendChild(sliderContainer);
  slider.appendChild(sliderWrapper);

  return slider;
}
