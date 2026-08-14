class SocialsButtons {
  static TEMPLATE_URL = 'components/socials-buttons/assets/html/template.html';
  static render(template, data = {}) {
    return template.replace(/\{\{\s*([\w_]+)\s*\}\}/g, (match, key) => {
      return data[key] !== undefined ? data[key] : '';
    });
  }
}

window.SocialsButtons = window.SocialsButtons || SocialsButtons;
