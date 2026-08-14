class HeaderLogo {
  static TEMPLATE_URL = 'components/header-logo/assets/html/template.html';
  static render(template, data = {}) {
    return template.replace(/\{\{\s*([\w_]+)\s*\}\}/g, (match, key) => {
      return data[key] !== undefined ? data[key] : '';
    });
  }
}

window.HeaderLogo = window.HeaderLogo || HeaderLogo;
