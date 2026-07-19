class Svg {
  static TEMPLATE_URL = 'components/svg/assets/html/template.html';
  static render({ icon = '', className = 'svg-icon' } = {}) {
    if (!icon) return null;

    const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svg.classList.add(className);
    svg.setAttribute('aria-hidden', 'true');

    const use = document.createElementNS('http://www.w3.org/2000/svg', 'use');
    use.setAttributeNS('http://www.w3.org/1999/xlink', 'href', `#${icon}`);
    svg.appendChild(use);

    return svg;
  }
}

window.Svg = window.Svg || Svg;
