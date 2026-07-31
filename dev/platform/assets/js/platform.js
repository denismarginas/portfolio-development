let root, canvas, viewport, cardsLayer, linksLayer, statusElement,
    inspectorElement, variableListElement, linkListElement,
    cardForm, variableForm, zoomLevelElement;

const state = { cards: [], links: [], variables: [] };
const strings = {};

let selectedCardId = null;
let pendingPort = null;
let dragging = null;
let saveTimer = null;

let panX = 0, panY = 0, zoom = 1;
let isPanning = false, panStartX = 0, panStartY = 0;

let apiUrl = "api/cards.php";
let stringsUrl = "data/strings.json";

function splitList(value) {
  return String(value || "")
    .split(",")
    .map((item) => item.trim())
    .filter(Boolean);
}

function setStatus(message) {
  statusElement.textContent = message;
}

function t(path, fallback) {
  const value = path.split(".").reduce((current, key) => {
    if (current && typeof current === "object" && key in current) {
      return current[key];
    }
    return undefined;
  }, strings);

  return typeof value === "string" ? value : fallback;
}

function formatText(template, replacements) {
  return Object.keys(replacements).reduce(
    (result, key) => result.replaceAll(`{${key}}`, replacements[key]),
    template,
  );
}

function applyStaticStrings() {
  root.querySelectorAll("[data-platform-i18n]").forEach((element) => {
    const path = element.dataset.platformI18n;
    const fallback = element.dataset.platformFallback || element.textContent || "";
    element.textContent = t(path, fallback);
  });

  root.querySelectorAll("[data-platform-placeholder]").forEach((element) => {
    const path = element.dataset.platformPlaceholder;
    const fallback = element.getAttribute("placeholder") || "";
    element.setAttribute("placeholder", t(path, fallback));
  });

  document.title = t("title", document.title);
}

async function loadStrings() {
  const response = await fetch(stringsUrl, {
    headers: { Accept: "application/json" },
  });

  if (!response.ok) {
    throw new Error(`Strings failed (${response.status})`);
  }

  const data = await response.json();
  Object.assign(strings, data);
  applyStaticStrings();
}

function uid(prefix) {
  return `${prefix}-${Date.now()}-${Math.random().toString(16).slice(2, 8)}`;
}

function queueSave() {
  window.clearTimeout(saveTimer);
  saveTimer = window.setTimeout(() => {
    saveState();
  }, 120);
}

async function loadState() {
  setStatus(t("canvas.loading"));
  const response = await fetch(apiUrl, {
    headers: { Accept: "application/json" },
  });
  if (!response.ok) {
    throw new Error(`Load failed (${response.status})`);
  }
  const data = await response.json();

  state.cards = Array.isArray(data.cards) ? data.cards : [];
  state.links = Array.isArray(data.links) ? data.links : [];
  state.variables = Array.isArray(data.variables) ? data.variables : [];

  if (!selectedCardId && state.cards.length) {
    selectedCardId = state.cards[0].id;
  }

  renderAll();
  fitView();
  setStatus(t("canvas.loaded"));
}

async function saveState() {
  try {
    const response = await fetch(apiUrl, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(state),
    });

    if (!response.ok) {
      throw new Error(`Save failed (${response.status})`);
    }

    setStatus(t("canvas.saved"));
  } catch (error) {
    setStatus(error.message);
  }
}

function escapeHtml(value) {
  return String(value ?? "")
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#39;");
}

function escapeAttribute(value) {
  return escapeHtml(value).replaceAll("`", "&#96;");
}

function escapeSelector(value) {
  if (window.CSS && typeof window.CSS.escape === "function") {
    return window.CSS.escape(String(value ?? ""));
  }

  return String(value ?? "").replaceAll(/(["\\\[\]#.:%])/g, "\\$1");
}

async function compileScss() {
  setStatus("Compiling SCSS...");
  try {
    const response = await fetch("api/compile-scss.php", {
      headers: { Accept: "application/json" },
    });
    const data = await response.json();
    if (data.error_count > 0) {
      setStatus(`SCSS: ${data.success_count} OK, ${data.error_count} errors`);
    } else {
      setStatus(`SCSS: ${data.total} components compiled successfully`);
    }
  } catch (error) {
    setStatus("SCSS compile failed: " + error.message);
  }
}

function updateTransform() {
  viewport.style.transform = `translate(${panX}px, ${panY}px) scale(${zoom})`;
  updateGrid();
  updateZoomDisplay();
  if (typeof renderLinks === "function") renderLinks();
}

function updateGrid() {
  canvas.style.setProperty("--platform-canvas-grid-sub", `${15 * zoom}px`);
  canvas.style.setProperty("--platform-canvas-grid-main-size", `${150 * zoom}px`);
  canvas.style.backgroundPosition = `${panX}px ${panY}px`;
}

function updateZoomDisplay() {
  if (zoomLevelElement) {
    zoomLevelElement.textContent = `${Math.round(zoom * 100)}%`;
  }
}

function fitView() {
  if (!state.cards.length) {
    panX = 0;
    panY = 0;
    zoom = 1;
    updateTransform();
    return;
  }

  const nodeWidth = 280;
  const nodeHeight = 160;

  let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
  state.cards.forEach(card => {
    const x = card.x || 80;
    const y = card.y || 80;
    if (x < minX) minX = x;
    if (y < minY) minY = y;
    if (x + nodeWidth > maxX) maxX = x + nodeWidth;
    if (y + nodeHeight > maxY) maxY = y + nodeHeight;
  });

  const cw = canvas.clientWidth;
  const ch = canvas.clientHeight;
  const contentW = maxX - minX;
  const contentH = maxY - minY;
  const pad = 80;

  const sx = (cw - pad * 2) / contentW;
  const sy = (ch - pad * 2) / contentH;
  zoom = Math.min(sx, sy, 2);
  zoom = Math.max(zoom, 0.1);

  const cx = (minX + maxX) / 2;
  const cy = (minY + maxY) / 2;
  panX = cw / 2 - cx * zoom;
  panY = ch / 2 - cy * zoom;

  updateTransform();
}

function onCanvasPointerDown(event) {
  if (event.target.closest("[data-card-id]")) return;
  if (event.target.closest("button")) return;

  isPanning = true;
  panStartX = event.clientX - panX;
  panStartY = event.clientY - panY;
  canvas.classList.add("is-panning");
  canvas.setPointerCapture(event.pointerId);
}

function onCanvasPointerMove(event) {
  if (!isPanning) return;
  panX = event.clientX - panStartX;
  panY = event.clientY - panStartY;
  updateTransform();
}

function onCanvasPointerUp() {
  if (!isPanning) return;
  isPanning = false;
  canvas.classList.remove("is-panning");
}

function onCanvasWheel(event) {
  event.preventDefault();

  const rect = canvas.getBoundingClientRect();
  const mx = event.clientX - rect.left;
  const my = event.clientY - rect.top;

  const delta = -event.deltaY * 0.001;
  const newZoom = Math.max(0.1, Math.min(5, zoom * (1 + delta)));

  panX = mx - (mx - panX) * (newZoom / zoom);
  panY = my - (my - panY) * (newZoom / zoom);
  zoom = newZoom;

  updateTransform();
}

document.addEventListener("DOMContentLoaded", () => {
  root = document.querySelector("[data-platform-root]");
  if (!root) return;

  apiUrl = root.dataset.apiUrl || "api/cards.php";
  stringsUrl = root.dataset.stringsUrl || "data/strings.json";

  canvas = root.querySelector("[data-platform-canvas]");
  viewport = root.querySelector("[data-platform-viewport]");
  cardsLayer = root.querySelector("[data-platform-cards]");
  linksLayer = root.querySelector("[data-platform-links]");
  statusElement = root.querySelector("[data-platform-status]");
  inspectorElement = root.querySelector("[data-platform-inspector]");
  variableListElement = root.querySelector("[data-platform-variable-list]");
  linkListElement = root.querySelector("[data-platform-link-list]");
  cardForm = root.querySelector("[data-platform-card-form]");
  variableForm = root.querySelector("[data-platform-variable-form]");
  zoomLevelElement = root.querySelector("[data-platform-zoom]");

  root.querySelectorAll("[data-platform-action]").forEach((button) => {
    button.addEventListener("click", () => {
      const action = button.dataset.platformAction;
      if (action === "reload") loadState();
      if (action === "save") saveState();
      if (action === "compile") compileScss();
      if (action === "fit-view") fitView();
    });
  });

  canvas.addEventListener("pointerdown", onCanvasPointerDown);
  window.addEventListener("pointermove", onCanvasPointerMove);
  window.addEventListener("pointerup", onCanvasPointerUp);
  canvas.addEventListener("wheel", onCanvasWheel, { passive: false });

  window.addEventListener("resize", () => { renderLinks(); });

  loadStrings()
    .then(() => loadState())
    .catch((error) => { setStatus(error.message); });
});