// SAKIB ENTERPRISE — front-end JS (vanilla)
(function () {
  'use strict';

  // Mobile menu toggle
  const toggle = document.getElementById('mobile-menu-toggle');
  const menu = document.getElementById('mobile-menu');
  if (toggle && menu) {
    toggle.addEventListener('click', function () {
      menu.classList.toggle('hidden');
    });
  }

  // Auto-dismiss flash messages
  document.querySelectorAll('.flash-msg').forEach(function (el) {
    setTimeout(function () { el.style.transition = 'opacity .4s'; el.style.opacity = '0'; setTimeout(() => el.remove(), 400); }, 5000);
  });

  // Simple phone input normalization for BD numbers
  document.querySelectorAll('input[type="tel"], input[name="phone"], input[name="customer_phone"]').forEach(function (el) {
    el.addEventListener('blur', function () {
      let v = el.value.replace(/\D/g, '');
      if (v.startsWith('880') && v.length === 13) { /* ok */ }
      else if (v.startsWith('01') && v.length === 11) { /* ok */ }
    });
  });

  // Booking form — show/hide return date for air ticket
  const tripType = document.getElementById('trip_type');
  const returnDateWrap = document.getElementById('return_date_wrap');
  if (tripType && returnDateWrap) {
    const toggleReturn = () => {
      returnDateWrap.classList.toggle('hidden', tripType.value !== 'round');
    };
    tripType.addEventListener('change', toggleReturn); toggleReturn();
  }

  // Mobile admin sidebar toggle
  const adminToggle = document.getElementById('mobile-sidebar-toggle');
  const adminSidebar = document.querySelector('aside.fixed.inset-y-0.left-0.w-64');
  if (adminToggle && adminSidebar) {
    // Create overlay
    const overlay = document.createElement('div');
    overlay.id = 'sidebar-overlay';
    overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:25;display:none;';
    document.body.appendChild(overlay);
    const closeSidebar = () => {
      adminSidebar.classList.remove('open');
      overlay.style.display = 'none';
    };
    const openSidebar = () => {
      adminSidebar.classList.add('open');
      overlay.style.display = 'block';
    };
    adminToggle.addEventListener('click', () => {
      if (adminSidebar.classList.contains('open')) closeSidebar(); else openSidebar();
    });
    overlay.addEventListener('click', closeSidebar);
  }

  // Admin: confirm deletes
  document.querySelectorAll('[data-confirm]').forEach(function (el) {
    el.addEventListener('submit', function (ev) {
      if (!confirm(el.dataset.confirm || 'Are you sure?')) { ev.preventDefault(); }
    });
  });

  // Lazy-load images (native fallback)
  document.querySelectorAll('img[data-src]').forEach(function (img) {
    img.loading = 'lazy';
    const io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          img.src = img.dataset.src;
          io.unobserve(img);
        }
      });
    });
    io.observe(img);
  });
})();
