function getPortElement(portRef) {
  return cardsLayer.querySelector(
    `[data-card-id="${escapeSelector(portRef.cardId)}"] [data-port-kind="${escapeSelector(portRef.kind)}"][data-port-name="${escapeSelector(portRef.name)}"]`,
  );
}

function getAnchorPoint(portElement, canvasRect, kind) {
  const rect = portElement.getBoundingClientRect();
  return {
    x: kind === "output" ? rect.right - canvasRect.left : rect.left - canvasRect.left,
    y: rect.top - canvasRect.top + rect.height / 2,
  };
}

function onCardPointerDown(event) {
  if (event.target.closest("button")) return;

  const cardElement = event.currentTarget.closest("[data-card-id]");
  if (!cardElement) return;

  const cardId = cardElement.dataset.cardId;
  const startX = event.clientX;
  const startY = event.clientY;
  const originX = parseFloat(cardElement.style.left || "0");
  const originY = parseFloat(cardElement.style.top || "0");

  dragging = {
    cardId,
    originX,
    originY,
    startX,
    startY,
  };

  selectedCardId = cardId;
  cardElement.setPointerCapture(event.pointerId);

  const move = (moveEvent) => {
    if (!dragging) return;

    const card = getCard(dragging.cardId);
    if (!card) return;

    card.x = dragging.originX + (moveEvent.clientX - dragging.startX) / zoom;
    card.y = dragging.originY + (moveEvent.clientY - dragging.startY) / zoom;
    renderCards();
    renderLinks();
  };

  const up = () => {
    if (!dragging) return;
    dragging = null;
    window.removeEventListener("pointermove", move);
    window.removeEventListener("pointerup", up);
    queueSave();
  };

  window.addEventListener("pointermove", move);
  window.addEventListener("pointerup", up, { once: true });
}
