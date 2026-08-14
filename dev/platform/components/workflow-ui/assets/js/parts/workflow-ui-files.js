const fileListCache = {};
const structureCache = {};

async function renderCardStructureLists() {
  const structureCards = state.cards.filter((card) => card.type === "structure");
  if (!structureCards.length) return;

  let data;
  if (structureCache.types) {
    data = structureCache.types;
  } else {
    try {
      const response = await fetch("api/types.php", { headers: { Accept: "application/json" } });
      if (!response.ok) return;
      const payload = await response.json();
      if (!payload || !payload.ok || !payload.types) return;
      data = payload.types;
      structureCache.types = data;
    } catch {
      return;
    }
  }

  const routable = [
    ...(data.post || []).filter((t) => t.routable),
    ...(data.taxonomy || []).filter((t) => t.routable),
  ];

  structureCards.forEach((card) => {
    const node = cardsLayer.querySelector(`[data-card-id="${escapeSelector(card.id)}"]`);
    if (!node) return;

    const container = node.querySelector(".platform-node-structure");
    if (container) container.remove();

    const structure = document.createElement("div");
    structure.className = "platform-node-structure";

    routable.forEach((item) => {
      const tag = document.createElement("span");
      tag.className = "platform-node-type";
      tag.textContent = `${item.label || item.type} (${item.count})`;
      tag.title = item.file;
      structure.appendChild(tag);
    });

    const portsEl = node.querySelector(".platform-node-ports");
    if (portsEl) {
      portsEl.parentNode.insertBefore(structure, portsEl);
    }
  });
}

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
