class HeaderMenu {
  static TEMPLATE_URL = 'components/header-menu/assets/html/template.html';
  static init() {
    const navbarToggle = document.querySelector('.dm-navbar-toggle');
    const navbarToggleSection = document.querySelector('.dm-menu');
    const bodySection = document.body;

    if (navbarToggle && navbarToggleSection) {
      navbarToggle.addEventListener('click', () => {
        navbarToggle.classList.toggle('active');
        navbarToggleSection.classList.toggle('navbar-active');

        if (bodySection.hasAttribute('data-overlay')) {
          bodySection.removeAttribute('data-overlay');
        } else {
          bodySection.setAttribute('data-overlay', 'true');
        }
      });
    }
  }
}

window.HeaderMenu = window.HeaderMenu || HeaderMenu;
