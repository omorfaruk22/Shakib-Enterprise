<footer class="bg-brand-dark text-gray-200 mt-20">
  <div class="max-w-7xl mx-auto px-4 py-14 grid md:grid-cols-2 lg:grid-cols-4 gap-10">
    <div>
      <div class="flex items-center gap-3 mb-4">
        <div class="w-12 h-12 rounded-lg bg-white text-brand-navy flex items-center justify-center font-display font-extrabold">SE</div>
        <div>
          <div class="font-display font-extrabold text-white text-lg leading-tight"><?= htmlspecialchars(setting('business_name','SAKIB ENTERPRISE')) ?></div>
          <div class="text-xs text-gray-400"><?= htmlspecialchars(setting('tagline','Your Trusted Travel Partner')) ?></div>
        </div>
      </div>
      <p class="text-sm text-gray-400 leading-relaxed"><?= htmlspecialchars(setting('footer_text','Your trusted travel partner in Cox\'s Bazar. We provide air tickets, ship tickets, bus tickets, and curated tour & holiday packages across Bangladesh.')) ?></p>
    </div>
    <div>
      <h4 class="font-display font-bold text-white mb-4">Quick Links</h4>
      <ul class="space-y-2 text-sm">
        <li><a href="<?= url('/') ?>" class="hover:text-brand-red">Home</a></li>
        <li><a href="<?= url('/about') ?>" class="hover:text-brand-red">About Us</a></li>
        <li><a href="<?= url('/tours') ?>" class="hover:text-brand-red">Tours</a></li>
        <li><a href="<?= url('/holiday-packages') ?>" class="hover:text-brand-red">Holiday Packages</a></li>
        <li><a href="<?= url('/contact') ?>" class="hover:text-brand-red">Contact</a></li>
      </ul>
    </div>
    <div>
      <h4 class="font-display font-bold text-white mb-4">Our Services</h4>
      <ul class="space-y-2 text-sm">
        <li><a href="<?= url('/air-ticket') ?>" class="hover:text-brand-red">Air Ticket Booking</a></li>
        <li><a href="<?= url('/ship-ticket') ?>" class="hover:text-brand-red">Ship Ticket Booking</a></li>
        <li><a href="<?= url('/bus-ticket') ?>" class="hover:text-brand-red">Bus Ticket Booking</a></li>
        <li><a href="<?= url('/tours') ?>" class="hover:text-brand-red">Tour Packages</a></li>
        <li><a href="<?= url('/holiday-packages') ?>" class="hover:text-brand-red">Holiday Packages</a></li>
      </ul>
    </div>
    <div>
      <h4 class="font-display font-bold text-white mb-4">Contact Info</h4>
      <ul class="space-y-3 text-sm">
        <?php if (setting('address')): ?><li class="flex gap-2"><i class="fa-solid fa-location-dot mt-1 text-brand-red"></i><span><?= htmlspecialchars(setting('address')) ?></span></li><?php endif; ?>
        <?php if (setting('phone')): ?><li class="flex gap-2"><i class="fa-solid fa-phone mt-1 text-brand-red"></i><a href="tel:<?= htmlspecialchars(setting('phone')) ?>" class="hover:text-brand-red"><?= htmlspecialchars(setting('phone')) ?></a></li><?php endif; ?>
        <?php if (setting('email')): ?><li class="flex gap-2"><i class="fa-solid fa-envelope mt-1 text-brand-red"></i><a href="mailto:<?= htmlspecialchars(setting('email')) ?>" class="hover:text-brand-red"><?= htmlspecialchars(setting('email')) ?></a></li><?php endif; ?>
        <?php if (setting('opening_hours')): ?><li class="flex gap-2"><i class="fa-regular fa-clock mt-1 text-brand-red"></i><span><?= htmlspecialchars(setting('opening_hours')) ?></span></li><?php endif; ?>
      </ul>
      <div class="flex gap-3 mt-4 text-lg">
        <?php if (setting('facebook')): ?><a href="<?= htmlspecialchars(setting('facebook')) ?>" class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center hover:bg-brand-red" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a><?php endif; ?>
        <?php if (setting('youtube')): ?><a href="<?= htmlspecialchars(setting('youtube')) ?>" class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center hover:bg-brand-red" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a><?php endif; ?>
        <?php if (setting('instagram')): ?><a href="<?= htmlspecialchars(setting('instagram')) ?>" class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center hover:bg-brand-red" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a><?php endif; ?>
        <?php if (setting('linkedin')): ?><a href="<?= htmlspecialchars(setting('linkedin')) ?>" class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center hover:bg-brand-red" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a><?php endif; ?>
      </div>
    </div>
  </div>
  <div class="border-t border-white/10">
    <div class="max-w-7xl mx-auto px-4 py-5 flex flex-col md:flex-row items-center justify-between gap-2 text-xs text-gray-400">
      <div>&copy; <?= date('Y') ?> <?= htmlspecialchars(setting('business_name','SAKIB ENTERPRISE')) ?>. All rights reserved.</div>
      <div>Cox's Bazar Sadar, Bangladesh.</div>
    </div>
  </div>
</footer>
