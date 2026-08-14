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
