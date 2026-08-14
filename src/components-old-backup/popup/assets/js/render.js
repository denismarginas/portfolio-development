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
