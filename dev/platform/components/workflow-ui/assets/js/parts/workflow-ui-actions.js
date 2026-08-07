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
