class Copyrights {
  static TEMPLATE_URL = 'components/copyrights/assets/html/template.html';
  static render(template, data = {}) {
    return template.replace(/\{\{\s*([\w_]+)\s*\}\}/g, (match, key) => {
      return data[key] !== undefined ? data[key] : '';
    });
  }
}

window.Copyrights = window.Copyrights || Copyrights;
