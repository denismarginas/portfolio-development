class Header {
  static TEMPLATE_URL = 'components/header/assets/html/template.html';
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

    const toggle = document.getElementById('toggleTheme');
    if (toggle) {
      const themePreference = localStorage.getItem('theme');
      if (themePreference === 'dark') {
        toggle.checked = true;
        Header.toggleTheme();
      }
      toggle.addEventListener('change', Header.toggleTheme);
    }
  }

  static toggleTheme() {
    const body = document.body;
    const toggle = document.getElementById('toggleTheme');
    const path = document.querySelector('.dm-toggletheme span svg path');

    if (!toggle || !path) return;

    if (toggle.checked) {
      body.classList.remove('theme-light');
      body.classList.add('theme-dark');
      path.style.transition = '0.3s ease-in-out';
      path.setAttribute('d', 'M15 22.1C11 22.1 7.79999 18.9 7.79999 15C7.79999 11 11 7.79999 15 7.79999C18.9 7.79999 12 10.5 12 15C12 19.5 18.9 22.1 15 22.1Z');
      localStorage.setItem('theme', 'dark');
    } else {
      body.classList.remove('theme-dark');
      body.classList.add('theme-light');
      path.style.transition = '0.3s ease-in-out';
      path.setAttribute('d', 'M15 22.1C11 22.1 7.79999 18.9 7.79999 15C7.79999 11 11 7.79999 15 7.79999C18.9 7.79999 22.1 11 22.1 15C22.1 18.9 18.9 22.1 15 22.1Z');
      localStorage.setItem('theme', 'light');
    }
  }
}

window.Header = window.Header || Header;
