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
