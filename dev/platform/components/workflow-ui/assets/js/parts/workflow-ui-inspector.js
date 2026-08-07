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
