class Hero {
  static TEMPLATE_URL = 'components/hero/assets/html/template.html';
  static render({ title = '', description = '', background = '', layout = 'standard', className = 'dm-hero-block' } = {}) {
    const block = document.createElement('div');
    block.className = className;
    block.dataset.layout = layout;
    block.dataset.motion = 'transition-fade-0';
    block.dataset.duration = '0.3s';
    block.dataset.delay = '0s';

    const bg = document.createElement('div');
    bg.className = 'dm-hero-bg';
    if (background) {
      bg.style.backgroundImage = `url('${background}')`;
    }
    block.appendChild(bg);

    const heading = document.createElement('h2');
    heading.dataset.motion = 'transition-fade-0 transition-blur-0 transition-slideInBottom-0';
    heading.dataset.duration = '0.5s';
    heading.dataset.delay = '0.2s';
    heading.textContent = title;
    block.appendChild(heading);

    if (description) {
      const paragraph = document.createElement('p');
      paragraph.dataset.motion = 'transition-fade-0 transition-blur-0 transition-slideInBottom-0';
      paragraph.dataset.duration = '0.6s';
      paragraph.dataset.delay = '0.25s';
      paragraph.textContent = description;
      block.appendChild(paragraph);
    }

    return block;
  }
}

window.Hero = window.Hero || Hero;
