class FooterLinklist {
  static TEMPLATE_URL = 'components/footer-linklist/assets/html/template.html';
  static render(template, data = {}) {
    return template.replace(/\{\{\s*([\w_]+)\s*\}\}/g, (match, key) => {
      return data[key] !== undefined ? data[key] : '';
    });
  }
}

window.FooterLinklist = window.FooterLinklist || FooterLinklist;
