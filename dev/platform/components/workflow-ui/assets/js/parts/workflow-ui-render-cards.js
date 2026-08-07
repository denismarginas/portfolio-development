function renderCards() {
  cardsLayer.innerHTML = "";

  state.cards.forEach((card) => {
    const node = document.createElement("article");
    node.className = "platform-node" + (card.id === selectedCardId ? " is-selected" : "");
    node.dataset.cardId = card.id;
    node.style.left = `${card.x || 80}px`;
    node.style.top = `${card.y || 80}px`;

    node.innerHTML = `
      <div class="platform-node-header" data-drag-handle>
        <div>
          <h3 class="platform-node-title">${escapeHtml(card.title || t("card.label"))}</h3>
          <span class="platform-node-type">${escapeHtml(card.type || t("card.nodeType"))}</span>
        </div>
        <button class="platform-button platform-button-ghost" type="button" data-card-delete>${PlatformSvg.render('trash', { size: 14 })}<span>${escapeHtml(t("actions.delete"))}</span></button>
      </div>
        <div class="platform-node-body">
        <div class="platform-node-meta">
          <p class="platform-node-action"><strong>${escapeHtml(t("card.actionPrefix"))}</strong> ${escapeHtml(card.action || "n/a")}</p>
          ${(card.variables || []).length ? `<div class="platform-node-variables">${card.variables.map(v => `<span class="platform-node-variable">${escapeHtml(v.name)}: ${escapeHtml(v.value)}</span>`).join("")}</div>` : ""}
          ${card.type === "live_preview" ? `<div class="platform-node-actions"><button class="platform-button platform-button-small" type="button" data-live-preview-view>${PlatformSvg.render('view', { size: 14 })}<span>${escapeHtml(t("actions.view"))}</span></button></div>` : ""}
          ${card.type === "compile_scss" || card.type === "translation" ? `<div class="platform-node-actions"><button class="platform-button platform-button-small" type="button" data-${card.type === "compile_scss" ? "compile-scss" : "translate"}>${PlatformSvg.render(card.type === "compile_scss" ? 'update' : 'web', { size: 14 })}<span>${escapeHtml(card.type === "compile_scss" ? t("actions.compileScss") : t("actions.translate"))}</span></button></div>` : ""}
          <p class="platform-node-note">${escapeHtml(card.note || "")}</p>
        </div>
        <div class="platform-node-ports">
          <div class="platform-node-port-group">
            <span class="platform-node-port-group-title">${escapeHtml(t("card.inputs"))}</span>
            <div class="platform-node-port-list">
              ${(card.inputs || []).map(renderPort("input", card.id)).join("")}
            </div>
          </div>
          <div class="platform-node-port-group">
            <span class="platform-node-port-group-title">${escapeHtml(t("card.outputs"))}</span>
            <div class="platform-node-port-list">
              ${(card.outputs || []).map(renderPort("output", card.id)).join("")}
            </div>
          </div>
        </div>
      </div>
    `;

    node.addEventListener("pointerdown", onCardPointerDown);
    node.addEventListener("click", () => {
      selectedCardId = card.id;
      renderAll();
    });

    node.querySelector("[data-card-delete]").addEventListener("click", (event) => {
      event.stopPropagation();
      deleteCard(card.id);
    });

    const viewBtn = node.querySelector("[data-live-preview-view]");
    if (viewBtn) {
      viewBtn.addEventListener("click", (event) => {
        event.stopPropagation();
        openLivePreview(card);
      });
    }

    const compileBtn = node.querySelector("[data-compile-scss]");
    if (compileBtn) {
      compileBtn.addEventListener("click", (event) => {
        event.stopPropagation();
        compileScss();
      });
    }

    const translateBtn = node.querySelector("[data-translate]");
    if (translateBtn) {
      translateBtn.addEventListener("click", (event) => {
        event.stopPropagation();
        translateData();
      });
    }

    node.querySelectorAll("[data-port-kind]").forEach((portButton) => {
      portButton.addEventListener("click", (event) => {
        event.stopPropagation();
        handlePortClick(card.id, portButton.dataset.portKind, portButton.dataset.portName);
      });
    });

    cardsLayer.appendChild(node);
  });
}

function renderPort(kind, cardId) {
  return (port) => {
    const pending =
      pendingPort &&
      pendingPort.cardId === cardId &&
      pendingPort.kind === kind &&
      pendingPort.name === port.name;

    return `<button class="platform-node-port${pending ? " is-pending" : ""}" type="button" data-port-kind="${kind}" data-port-name="${escapeAttribute(port.name)}">${escapeHtml(port.label || port.name)}</button>`;
  };
}
