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

  const shipRoute = document.querySelector('[data-ship-route]');
  if (shipRoute) {
    shipRoute.addEventListener('change', () => {
      const option = shipRoute.selectedOptions[0];
      const departure = document.querySelector('[name="departure_location"]');
      const destination = document.querySelector('[name="destination"]');
      if (option && option.value !== '') {
        if (departure) departure.value = option.dataset.origin || '';
        if (destination) destination.value = option.dataset.destination || '';
      }
    });
    if (shipRoute.value !== '') shipRoute.dispatchEvent(new Event('change'));
  }
})();
