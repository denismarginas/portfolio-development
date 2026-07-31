function getCard(id) {
  return state.cards.find((card) => card.id === id) || null;
}

function normalizePorts(items) {
  return splitList(items).map((item) => {
    const [name, label] = item.split(":").map((part) => part.trim());
    return {
      name: name || label,
      label: label || name,
    };
  });
}

function renderAll() {
  renderCards();
  renderLinks();
  renderVariables();
  renderLinksList();
  renderInspector();
  renderCardFileLists();
}

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
        <button class="platform-button platform-button-ghost" type="button" data-card-delete>${escapeHtml(t("actions.delete"))}</button>
      </div>
      <div class="platform-node-body">
        <div class="platform-node-meta">
          <p class="platform-node-action"><strong>${escapeHtml(t("card.actionPrefix"))}</strong> ${escapeHtml(card.action || "n/a")}</p>
          ${(card.variables || []).length ? `<div class="platform-node-variables">${card.variables.map(v => `<span class="platform-node-variable">${escapeHtml(v.name)}: ${escapeHtml(v.value)}</span>`).join("")}</div>` : ""}
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

function renderLinks() {
  const canvasRect = canvas.getBoundingClientRect();
  linksLayer.innerHTML = "";
  linksLayer.setAttribute("width", `${canvas.clientWidth}`);
  linksLayer.setAttribute("height", `${canvas.clientHeight}`);
  linksLayer.setAttribute("viewBox", `0 0 ${canvas.clientWidth} ${canvas.clientHeight}`);

  state.links.forEach((link) => {
    const source = getPortElement(link.from);
    const target = getPortElement(link.to);

    if (!source || !target) return;

    const start = getAnchorPoint(source, canvasRect, "output");
    const end = getAnchorPoint(target, canvasRect, "input");
    const dx = Math.max(80, Math.abs(end.x - start.x) * 0.4);
    const d = `M ${start.x} ${start.y} C ${start.x + dx} ${start.y}, ${end.x - dx} ${end.y}, ${end.x} ${end.y}`;

    const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
    path.setAttribute("d", d);
    path.setAttribute("class", "platform-link-path");
    linksLayer.appendChild(path);
  });
}

function renderVariables() {
  variableListElement.innerHTML = "";

  if (!state.variables.length) {
    variableListElement.innerHTML = `<p class="platform-empty">${escapeHtml(t("canvas.noVariables"))}</p>`;
    return;
  }

  state.variables.forEach((variable) => {
    const item = document.createElement("div");
    item.className = "platform-list-item";
    item.textContent = `${variable.name}: ${variable.value}`;
    variableListElement.appendChild(item);
  });
}

function renderLinksList() {
  linkListElement.innerHTML = "";

  if (!state.links.length) {
    linkListElement.innerHTML = `<p class="platform-empty">${escapeHtml(t("canvas.noLinks"))}</p>`;
    return;
  }

  state.links.forEach((link) => {
    const sourceCard = getCard(link.from.cardId);
    const targetCard = getCard(link.to.cardId);
    const item = document.createElement("div");
    item.className = "platform-list-item";
    item.textContent = `${sourceCard ? sourceCard.title : link.from.cardId}.${link.from.name} -> ${targetCard ? targetCard.title : link.to.cardId}.${link.to.name}`;
    linkListElement.appendChild(item);
  });
}

function renderInspector() {
  const card = getCard(selectedCardId);
  if (!card) {
    inspectorElement.innerHTML = `<p class="platform-empty">${escapeHtml(t("canvas.selectCard"))}</p>`;
    return;
  }

  const inputsStr = (card.inputs || []).map(p => p.name + (p.label !== p.name ? ':' + p.label : '')).join(', ');
  const outputsStr = (card.outputs || []).map(p => p.name + (p.label !== p.name ? ':' + p.label : '')).join(', ');
  const varsStr = (card.variables || []).map(v => v.name + (v.value ? ':' + v.value : '')).join(', ');

  inspectorElement.innerHTML = `
    <div class="platform-inspector-form">
      <label class="platform-field">
        <span class="platform-field-label">${escapeHtml(t('fields.title'))}</span>
        <input class="platform-input" type="text" data-inspector-field="title" value="${escapeAttribute(card.title)}">
      </label>
      <label class="platform-field">
        <span class="platform-field-label">${escapeHtml(t('fields.type'))}</span>
        <input class="platform-input" type="text" data-inspector-field="type" value="${escapeAttribute(card.type || '')}">
      </label>
      <label class="platform-field">
        <span class="platform-field-label">${escapeHtml(t('fields.action'))}</span>
        <input class="platform-input" type="text" data-inspector-field="action" value="${escapeAttribute(card.action || '')}">
      </label>
      <label class="platform-field">
        <span class="platform-field-label">${escapeHtml(t('fields.inputs'))}</span>
        <input class="platform-input" type="text" data-inspector-field="inputs" value="${escapeAttribute(inputsStr)}" placeholder="${escapeHtml(t('placeholders.inputs'))}">
      </label>
      <label class="platform-field">
        <span class="platform-field-label">${escapeHtml(t('fields.outputs'))}</span>
        <input class="platform-input" type="text" data-inspector-field="outputs" value="${escapeAttribute(outputsStr)}" placeholder="${escapeHtml(t('placeholders.outputs'))}">
      </label>
      <label class="platform-field">
        <span class="platform-field-label">${escapeHtml(t('fields.variables'))}</span>
        <input class="platform-input" type="text" data-inspector-field="variables" value="${escapeAttribute(varsStr)}" placeholder="${escapeHtml(t('placeholders.variables'))}">
      </label>
      <label class="platform-field">
        <span class="platform-field-label">${escapeHtml(t('inspector.note', 'Note'))}</span>
        <input class="platform-input" type="text" data-inspector-field="note" value="${escapeAttribute(card.note || '')}">
      </label>
      <div class="platform-inspector-actions">
        <button class="platform-button" type="button" data-inspector-update>${escapeHtml(t('inspector.update'))}</button>
        <button class="platform-button platform-button-ghost" type="button" data-inspector-delete>${escapeHtml(t('actions.delete'))}</button>
      </div>
    </div>
  `;
}

function handlePortClick(cardId, kind, portName) {
  const card = getCard(cardId);
  if (!card) return;

  if (!pendingPort) {
    pendingPort = { cardId, kind, name: portName };
    setStatus(formatText(t("canvas.selectedPort"), { kind, name: portName }));
    renderCards();
    return;
  }

  const isValidLink = pendingPort.kind === "output" && kind === "input";
  const isDifferentCard = pendingPort.cardId !== cardId;

  if (isValidLink && isDifferentCard) {
    state.links.push({
      id: uid("link"),
      from: pendingPort,
      to: { cardId, kind, name: portName },
    });
    pendingPort = null;
    setStatus(t("canvas.linkCreated"));
    renderAll();
    queueSave();
    return;
  }

  pendingPort = { cardId, kind, name: portName };
  setStatus(formatText(t("canvas.selectedPort"), { kind, name: portName }));
  renderCards();
}

function createCard(formData) {
  const title = formData.get("title");
  const type = formData.get("type");
  const action = formData.get("action");
  const inputs = normalizePorts(formData.get("inputs"));
  const outputs = normalizePorts(formData.get("outputs"));
  const variables = splitList(formData.get("variables")).map((entry) => {
    const [name, value] = entry.split(":").map((part) => part.trim());
    return { name, value };
  }).filter((item) => item.name);

  const offset = state.cards.length * 28;
  const card = {
    id: uid("card"),
    title,
    type,
    action,
    inputs,
    outputs,
    variables,
    x: 80 + (offset % 240),
    y: 80 + (offset % 160),
    note: "",
  };

  state.cards.push(card);
  if (variables.length) {
    state.variables.push(...variables.map((variable) => ({ ...variable, source: card.id })));
  }
  selectedCardId = card.id;
  renderAll();
  queueSave();
}

function addVariable(formData) {
  const name = String(formData.get("name") || "").trim();
  const value = String(formData.get("value") || "").trim();

  if (!name) return;

  const existing = state.variables.find((variable) => variable.name === name && !variable.source);
  if (existing) {
    existing.value = value;
  } else {
    state.variables.push({ id: uid("var"), name, value });
  }

  renderAll();
  queueSave();
}

function deleteCard(cardId) {
  state.cards = state.cards.filter((card) => card.id !== cardId);
  state.links = state.links.filter((link) => link.from.cardId !== cardId && link.to.cardId !== cardId);
  state.variables = state.variables.filter((variable) => variable.source !== cardId);

  if (selectedCardId === cardId) {
    selectedCardId = state.cards[0] ? state.cards[0].id : null;
  }

  renderAll();
  queueSave();
}

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

const fileListCache = {};

async function renderCardFileLists() {
  const pathsToFetch = [];

  state.cards.forEach((card) => {
    const sourceVar = (card.variables || []).find((v) => v.name === "source");
    if (!sourceVar || !sourceVar.value) return;
    const val = sourceVar.value.trim();
    if (val.startsWith("http://") || val.startsWith("https://")) return;
    if (val.endsWith(".json")) return;
    if (!pathsToFetch.includes(val)) pathsToFetch.push(val);
  });

  for (const path of pathsToFetch) {
    let data;
    if (fileListCache[path]) {
      data = fileListCache[path];
    } else {
      try {
        const response = await fetch(
          `api/validate-files.php?path=${encodeURIComponent(path)}`,
          { headers: { Accept: "application/json" } },
        );
        if (!response.ok) continue;
        data = await response.json();
        fileListCache[path] = data;
      } catch {
        continue;
      }
    }

    if (!data || !Array.isArray(data.files)) continue;

    state.cards.forEach((card) => {
      const sourceVar = (card.variables || []).find(
        (v) => v.name === "source" && v.value === path,
      );
      if (!sourceVar) return;

      const node = cardsLayer.querySelector(`[data-card-id="${escapeSelector(card.id)}"]`);
      if (!node) return;

      const container = node.querySelector(".platform-node-filelist");
      if (container) container.remove();

      const fileList = document.createElement("div");
      fileList.className = "platform-node-filelist";

      data.files.forEach((file) => {
        const tag = document.createElement("span");
        tag.className = "platform-node-file" + (file.valid ? "" : " is-invalid");
        tag.textContent = file.name;
        if (!file.valid && file.error) {
          tag.title = file.error;
        }
        fileList.appendChild(tag);
      });

      const portsEl = node.querySelector(".platform-node-ports");
      if (portsEl) {
        portsEl.parentNode.insertBefore(fileList, portsEl);
      }
    });
  }
}

function applyTemplate(templateName) {
  const fields = cardForm.elements;

  if (templateName === "database") {
    fields["title"].value = "DataBase";
    fields["type"].value = "database";
    fields["action"].value = "loadJsonFile";
    fields["inputs"].value = "";
    fields["outputs"].value = "data:Data, count:Count";
    fields["variables"].value = "source:";
  } else if (templateName === "selectfile") {
    fields["title"].value = "Select File";
    fields["type"].value = "selectfile";
    fields["action"].value = "filterByFile";
    fields["inputs"].value = "data:Data";
    fields["outputs"].value = "items:Items";
    fields["variables"].value = "file:,post_type:";
  } else if (templateName === "project_structure") {
    fields["title"].value = "Project Structure";
    fields["type"].value = "project_structure";
    fields["action"].value = "";
    fields["inputs"].value = "items:Items";
    fields["outputs"].value = "config:Config";
    fields["variables"].value = "header:header, footer:footer, page_structure:page_constructor, body_wrapper:";
  } else if (templateName === "page_structure") {
    fields["title"].value = "Page Structure";
    fields["type"].value = "page_structure";
    fields["action"].value = "";
    fields["inputs"].value = "items:Items";
    fields["outputs"].value = "config:Config";
    fields["variables"].value = "header:header, footer:footer, page_structure:page_constructor";
  } else if (templateName === "seo_project") {
    fields["title"].value = "Seo Projects";
    fields["type"].value = "seo_project";
    fields["action"].value = "";
    fields["inputs"].value = "data:Data";
    fields["outputs"].value = "seo:SEO";
    fields["variables"].value = "title_max:50, description_max:140, index:index, keywords_source:post_id";
  } else if (templateName === "seo_page") {
    fields["title"].value = "Seo Page";
    fields["type"].value = "seo_page";
    fields["action"].value = "";
    fields["inputs"].value = "data:Data";
    fields["outputs"].value = "seo:SEO";
    fields["variables"].value = "index:index";
  } else if (templateName === "render") {
    fields["title"].value = "Preview Render";
    fields["type"].value = "render";
    fields["action"].value = "renderPage";
    fields["inputs"].value = "items:Items, config:Config, seo:SEO";
    fields["outputs"].value = "";
    fields["variables"].value = "debug_post_data:false, compile_assets:false";
  } else {
    cardForm.reset();
    fields["title"].focus();
  }
}

function updateCardFromInspector() {
  const card = getCard(selectedCardId);
  if (!card) return;

  const fields = inspectorElement.querySelectorAll("[data-inspector-field]");
  fields.forEach((field) => {
    const name = field.dataset.inspectorField;
    const value = field.value.trim();

    if (name === "inputs") {
      card.inputs = normalizePorts(value);
    } else if (name === "outputs") {
      card.outputs = normalizePorts(value);
    } else if (name === "variables") {
      card.variables = splitList(value).map((entry) => {
        const [n, ...rest] = entry.split(":").map((part) => part.trim());
        return { name: n, value: rest.join(":") || "" };
      }).filter((v) => v.name);
    } else if (name === "title" || name === "type" || name === "action" || name === "note") {
      card[name] = value;
    }
  });

  renderAll();
  queueSave();
  setStatus(t("canvas.saved"));
}

document.addEventListener("DOMContentLoaded", () => {
  if (!root) return;

  cardForm.addEventListener("submit", (event) => {
    event.preventDefault();
    createCard(new FormData(cardForm));
    cardForm.reset();
    setStatus(t("canvas.cardAdded"));
  });

  variableForm.addEventListener("submit", (event) => {
    event.preventDefault();
    addVariable(new FormData(variableForm));
    variableForm.reset();
    setStatus(t("canvas.variableSaved"));
  });

  const templatesContainer = root.querySelector("[data-platform-templates]");
  if (templatesContainer) {
    templatesContainer.addEventListener("click", (event) => {
      const btn = event.target.closest("[data-template]");
      if (!btn) return;
      applyTemplate(btn.dataset.template);
    });
  }

  inspectorElement.addEventListener("click", (event) => {
    const updateBtn = event.target.closest("[data-inspector-update]");
    const deleteBtn = event.target.closest("[data-inspector-delete]");

    if (updateBtn) {
      updateCardFromInspector();
    } else if (deleteBtn) {
      const card = getCard(selectedCardId);
      if (card) deleteCard(card.id);
    }
  });

});
