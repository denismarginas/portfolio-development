class Image {
  static TEMPLATE_URL = 'components/image/assets/html/template.html';
  static render({ src = '', alt = '', className = 'responsive-image', lazy = true, additionalAttributes = {} } = {}) {
    const img = document.createElement('img');
    img.src = src;
    img.alt = alt;
    img.className = className;
    img.loading = lazy ? 'lazy' : 'eager';

    Object.entries(additionalAttributes).forEach(([key, value]) => {
      img.setAttribute(key, value);
    });

    return img;
  }
}

window.Image = window.Image || Image;
