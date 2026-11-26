(() => {
  const toggle = document.querySelector('[data-menu-toggle]');
  const menu = document.querySelector('[data-menu]');
  if (toggle && menu) {
    toggle.addEventListener('click', () => {
      const open = toggle.getAttribute('aria-expanded') !== 'true';
      menu.classList.toggle('hidden', !open);
      toggle.setAttribute('aria-expanded', String(open));
    });
    menu.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => {
      menu.classList.add('hidden');
      toggle.setAttribute('aria-expanded', 'false');
    }));
  }
  document.querySelectorAll('[data-year]').forEach((node) => {
    node.textContent = new Date().getFullYear();
  });
})();
