class Carousel {
  static TEMPLATE_URL = 'components/carousel/assets/html/template.html';
  static render(container, items = [], { showNumbers = false } = {}) {
    const root = typeof container === 'string' ? document.querySelector(container) : container;
    if (!root) return null;

    const slider = document.createElement('div');
    slider.className = 'slider';
    slider.dataset.navigation = 'arrows dots';

    const wrapper = document.createElement('div');
    wrapper.className = 'slider-wrapper';

    const track = document.createElement('div');
    track.className = 'slider-container';

    items.forEach((item, index) => {
      const element = document.createElement('div');
      element.className = 'slider-element';
      if (showNumbers) {
        const number = document.createElement('div');
        number.className = 'number-text';
        number.textContent = `${index + 1} / ${items.length}`;
        element.appendChild(number);
      }

      const content = document.createElement('div');
      content.innerHTML = item.content || '';
      element.appendChild(content);
      track.appendChild(element);
    });

    wrapper.appendChild(track);
    slider.appendChild(wrapper);
    root.appendChild(slider);

    return slider;
  }
}

window.Carousel = window.Carousel || Carousel;
