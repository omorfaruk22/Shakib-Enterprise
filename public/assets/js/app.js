// SAKIB ENTERPRISE — front-end JS (vanilla)
(function () {
  'use strict';

  // ---------- Mobile menu toggle (public header) ----------
  const toggle = document.getElementById('mobile-menu-toggle');
  const menu = document.getElementById('mobile-menu');
  if (toggle && menu) {
    toggle.addEventListener('click', function () {
      menu.classList.toggle('hidden');
      toggle.setAttribute('aria-expanded', !menu.classList.contains('hidden'));
    });
    // Close menu when clicking a link
    menu.querySelectorAll('a').forEach(function (a) {
      a.addEventListener('click', function () { menu.classList.add('hidden'); });
    });
  }

  // ---------- Auto-dismiss flash messages ----------
  document.querySelectorAll('.flash-msg').forEach(function (el) {
    setTimeout(function () {
      el.style.transition = 'opacity .4s'; el.style.opacity = '0';
      setTimeout(function () { el.remove(); }, 400);
    }, 5000);
  });

  // ---------- Phone input normalization for BD numbers ----------
  document.querySelectorAll('input[type="tel"], input[name="phone"], input[name="customer_phone"]').forEach(function (el) {
    el.addEventListener('blur', function () {
      let v = (el.value || '').replace(/\D/g, '');
      // Convert 8801xxx → 01xxx if desired (keep as-is for now)
    });
  });

  // ---------- Booking form: show/hide return date for round-trip ----------
  const tripType = document.getElementById('trip_type');
  const returnDateWrap = document.getElementById('return_date_wrap');
  if (tripType && returnDateWrap) {
    const toggleReturn = function () {
      returnDateWrap.classList.toggle('hidden', tripType.value !== 'round');
    };
    tripType.addEventListener('change', toggleReturn); toggleReturn();
  }

  // ---------- Mobile admin sidebar toggle ----------
  const adminToggle = document.getElementById('mobile-sidebar-toggle');
  const adminSidebar = document.querySelector('aside.fixed.inset-y-0.left-0.w-64');
  if (adminToggle && adminSidebar) {
    const overlay = document.createElement('div');
    overlay.id = 'sidebar-overlay';
    overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:25;display:none;';
    document.body.appendChild(overlay);
    const closeSidebar = function () {
      adminSidebar.classList.remove('open');
      overlay.style.display = 'none';
    };
    const openSidebar = function () {
      adminSidebar.classList.add('open');
      overlay.style.display = 'block';
    };
    adminToggle.addEventListener('click', function () {
      if (adminSidebar.classList.contains('open')) closeSidebar(); else openSidebar();
    });
    overlay.addEventListener('click', closeSidebar);
  }

  // ---------- Admin: confirm deletes ----------
  document.querySelectorAll('[data-confirm]').forEach(function (el) {
    el.addEventListener('submit', function (ev) {
      if (!confirm(el.dataset.confirm || 'Are you sure?')) { ev.preventDefault(); }
    });
  });

  // ---------- Lazy-load images (native + IntersectionObserver fallback) ----------
  if ('IntersectionObserver' in window) {
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
  }

  // ---------- Smooth scroll for anchor links ----------
  document.querySelectorAll('a[href^="#"]').forEach(function (a) {
    a.addEventListener('click', function (e) {
      const target = document.querySelector(a.getAttribute('href'));
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });

  // ---------- Back-to-top button ----------
  const btt = document.createElement('button');
  btt.type = 'button';
  btt.setAttribute('aria-label', 'Back to top');
  btt.innerHTML = '<i class="fa-solid fa-arrow-up"></i>';
  btt.style.cssText = 'position:fixed;bottom:80px;right:20px;z-index:50;width:44px;height:44px;border-radius:50%;border:none;background:#102B70;color:#fff;cursor:pointer;box-shadow:0 4px 14px rgba(16,43,112,0.35);font-size:14px;display:none;align-items:center;justify-content:center;transition:transform .2s,opacity .2s;opacity:0;';
  btt.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: 'smooth' }); });
  document.body.appendChild(btt);
  window.addEventListener('scroll', function () {
    if (window.scrollY > 400) {
      btt.style.display = 'flex';
      requestAnimationFrame(function () { btt.style.opacity = '1'; btt.style.transform = 'translateY(0)'; });
    } else {
      btt.style.opacity = '0'; btt.style.transform = 'translateY(10px)';
      setTimeout(function () { if (window.scrollY <= 400) btt.style.display = 'none'; }, 200);
    }
  }, { passive: true });

  // ---------- Form submission feedback (prevent double-submit) ----------
  document.querySelectorAll('form[data-loading]').forEach(function (form) {
    form.addEventListener('submit', function () {
      const btn = form.querySelector('button[type="submit"]');
      if (btn) {
        btn.disabled = true;
        btn.dataset.originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Processing...';
      }
    });
  });
  // Auto-enable on forms (disable submit after click to prevent duplicates)
  document.querySelectorAll('form button[type="submit"]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const form = btn.closest('form');
      if (!form || form.dataset.noLoading === '1') return;
      if (form.querySelector('[type="file"]')) return; // don't disable upload forms
      setTimeout(function () {
        if (form.reportValidity && !form.reportValidity()) return;
        btn.disabled = true;
        btn.dataset.originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Please wait...';
        // Re-enable after 8 seconds in case of error
        setTimeout(function () {
          btn.disabled = false;
          if (btn.dataset.originalHtml) btn.innerHTML = btn.dataset.originalHtml;
        }, 8000);
      }, 50);
    });
  });

  // ---------- Animate numbers on scroll (Company Stats) ----------
  const statEls = document.querySelectorAll('[data-counter]');
  if (statEls.length && 'IntersectionObserver' in window) {
    const animate = function (el) {
      const target = el.dataset.counter;
      const suffix = el.dataset.suffix || '';
      const numericPart = (target || '').match(/[\d.]+/);
      if (!numericPart) { el.textContent = target; return; }
      const num = parseFloat(numericPart[0]);
      const nonNumeric = target.replace(numericPart[0], '');
      const duration = 1500;
      const start = performance.now();
      const step = function (now) {
        const t = Math.min(1, (now - start) / duration);
        const eased = 1 - Math.pow(1 - t, 3);
        const current = Math.round(eased * num);
        el.textContent = current + nonNumeric + suffix;
        if (t < 1) requestAnimationFrame(step);
        else el.textContent = target + suffix;
      };
      requestAnimationFrame(step);
    };
    const io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          animate(entry.target);
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.3 });
    statEls.forEach(function (el) { io.observe(el); });
  }
})();
