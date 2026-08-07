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
