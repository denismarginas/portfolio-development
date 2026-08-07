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

function openLivePreview(card) {
  const postId = (card.variables || []).find((v) => v.name === "post_id");
  if (!postId || !postId.value) {
    setStatus("Live preview: missing post_id variable");
    return;
  }
  const url = new URL("preview/?post_id=" + encodeURIComponent(postId.value), window.location.href);
  window.open(url.href, "_blank");
}
