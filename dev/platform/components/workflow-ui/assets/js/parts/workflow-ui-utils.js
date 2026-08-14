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
  renderCardStructureLists();
}

function openLivePreview(card) {
  fetch("api/workflow.php?section=live_preview", { headers: { Accept: "application/json" } })
    .then((res) => res.json())
    .then((data) => {
      const vars = (data.variables || []).reduce((acc, v) => { acc[v.name] = v.value; return acc; }, {});
      const postId = vars._id || vars.post_id || "";
      if (!postId) {
        setStatus("Live preview: missing _id in live_preview settings");
        return;
      }
      const url = new URL("preview/?_id=" + encodeURIComponent(postId), window.location.href);
      window.open(url.href, "_blank");
    })
    .catch(() => setStatus("Live preview: failed to load live_preview settings"));
}
