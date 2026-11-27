<?php
$phone = setting('phone');
$whatsapp = setting('whatsapp');
$email = setting('email');
$hours = setting('opening_hours');
$customerUser = \App\Core\CustomerAuth::user();
?>
<!-- Top info bar -->
<div class="bg-brand-dark text-white text-xs sm:text-sm">
  <div class="max-w-7xl mx-auto px-3 sm:px-4 py-1.5 flex flex-wrap items-center justify-between gap-2">
    <div class="flex flex-wrap items-center gap-3">
      <?php if ($phone): ?><a href="tel:<?= htmlspecialchars($phone) ?>" class="flex items-center gap-1.5 hover:text-red-300 whitespace-nowrap"><i class="fa-solid fa-phone"></i> <span class="hidden sm:inline"><?= htmlspecialchars($phone) ?></span></a><?php endif; ?>
      <?php if ($email): ?><a href="mailto:<?= htmlspecialchars($email) ?>" class="hidden md:flex items-center gap-1.5 hover:text-red-300"><i class="fa-solid fa-envelope"></i> <?= htmlspecialchars($email) ?></a><?php endif; ?>
      <?php if ($hours): ?><span class="hidden lg:flex items-center gap-1.5"><i class="fa-regular fa-clock"></i> <?= htmlspecialchars($hours) ?></span><?php endif; ?>
    </div>
    <div class="flex items-center gap-3">
      <?php if (setting('facebook')): ?><a href="<?= htmlspecialchars(setting('facebook')) ?>" class="hover:text-red-300" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a><?php endif; ?>
      <?php if (setting('youtube')): ?><a href="<?= htmlspecialchars(setting('youtube')) ?>" class="hover:text-red-300" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a><?php endif; ?>
      <?php if (setting('instagram')): ?><a href="<?= htmlspecialchars(setting('instagram')) ?>" class="hover:text-red-300" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a><?php endif; ?>
      <?php if ($whatsapp): ?><a href="<?= whatsapp_url('Hello! I would like to know more about your travel services.') ?>" target="_blank" rel="noopener" class="flex items-center gap-1.5 text-green-400 hover:text-green-300"><i class="fa-brands fa-whatsapp"></i> <span class="hidden sm:inline">WhatsApp</span></a><?php endif; ?>
    </div>
  </div>
</div>

<!-- Main header -->
<header class="sticky top-0 z-40 bg-white shadow-sm border-b border-gray-100">
  <div class="max-w-7xl mx-auto px-3 sm:px-4">
    <div class="flex items-center justify-between h-16 lg:h-[72px]">
      <!-- Logo -->
      <a href="<?= url('/') ?>" class="flex items-center gap-2.5 shrink-0">
        <div class="w-10 h-10 lg:w-11 lg:h-11 rounded-lg bg-brand-navy text-white flex items-center justify-center font-display font-extrabold text-base lg:text-lg shadow-brand">SE</div>
        <div class="leading-tight">
          <div class="font-display font-extrabold text-lg lg:text-xl text-brand-navy"><?= htmlspecialchars(setting('business_name','SAKIB ENTERPRISE')) ?></div>
          <div class="text-[11px] text-gray-500 tracking-wide hidden sm:block"><?= htmlspecialchars(setting('tagline','Your Trusted Travel Partner')) ?></div>
        </div>
      </a>

      <!-- Desktop nav (lg+) -->
      <nav class="hidden lg:flex items-center gap-0.5 text-sm xl:text-[15px] font-medium">
        <?php
        $nav = [
          '/' => 'Home',
          '/air-ticket' => 'Air Ticket',
          '/ship-ticket' => 'Ship Ticket',
          '/bus-ticket' => 'Bus Ticket',
          '/tours' => 'Tours',
          '/holiday-packages' => 'Holidays',
          '/about' => 'About',
          '/contact' => 'Contact',
        ];
        foreach ($nav as $u => $label):
          // Short labels on lg to fit
          $short = [
            'Air Ticket' => 'Air', 'Ship Ticket' => 'Ship', 'Bus Ticket' => 'Bus',
          ];
          $lgLabel = $short[$label] ?? $label;
        ?>
          <?php
            $seg = trim($u,'/');
            $active = false;
            if ($u === '/' && ($_activeNav ?? '') === 'home') $active = true;
            elseif ($u !== '/' && ($_activeNav ?? '') && str_starts_with($seg, $_activeNav)) $active = true;
          ?>
          <a href="<?= url($u) ?>" class="nav-link whitespace-nowrap px-2 xl:px-2.5 py-2 text-gray-700 hover:text-brand-navy text-sm <?= $active ? 'active text-brand-navy font-semibold' : '' ?>">
            <span class="lg:inline xl:hidden"><?= $lgLabel ?></span>
            <span class="hidden xl:inline"><?= $label ?></span>
          </a>
        <?php endforeach; ?>
      </nav>

      <!-- Desktop actions -->
      <div class="hidden lg:flex items-center gap-2 shrink-0">
        <?php if ($customerUser): ?>
          <a href="<?= url('/customer') ?>" class="inline-flex items-center gap-1.5 text-brand-navy font-semibold text-sm hover:text-brand-red">
            <i class="fa-regular fa-user"></i>
            <span class="hidden xl:inline"><?= htmlspecialchars(explode(' ', $customerUser['name'])[0] ?? 'Account') ?></span>
          </a>
        <?php else: ?>
          <a href="<?= url('/login') ?>" class="inline-flex items-center gap-1.5 text-brand-navy font-semibold text-sm hover:text-brand-red">
            <i class="fa-regular fa-user"></i><span class="hidden xl:inline"> Login</span>
          </a>
          <a href="<?= url('/register') ?>" class="btn-outline px-3 py-1.5 lg:px-3.5 lg:py-2 rounded-lg text-sm font-semibold">Register</a>
        <?php endif; ?>
        <a href="<?= url('/contact') ?>" class="btn-accent px-3.5 py-2 lg:px-4 lg:py-2 rounded-lg font-semibold text-sm inline-flex items-center gap-1.5 shadow-brand">
          <i class="fa-solid fa-paper-plane text-xs"></i><span class="hidden md:inline"> Book Now</span>
        </a>
      </div>

      <!-- Mobile toggle (lg-) -->
      <button id="mobile-menu-toggle" class="lg:hidden p-2 rounded-lg text-brand-navy" aria-label="Menu">
        <i class="fa-solid fa-bars text-xl"></i>
      </button>
    </div>

    <!-- Mobile menu -->
    <div id="mobile-menu" class="hidden lg:hidden pb-4 border-t border-gray-100">
      <div class="flex flex-col gap-1 pt-3">
        <?php
        $mobNav = [
          '/' => 'Home','/air-ticket'=>'Air Ticket','/ship-ticket'=>'Ship Ticket','/bus-ticket'=>'Bus Ticket',
          '/tours'=>'Tours','/holiday-packages'=>'Holiday Packages','/about'=>'About','/contact'=>'Contact'
        ];
        foreach ($mobNav as $u => $label): ?>
          <a href="<?= url($u) ?>" class="px-3 py-2.5 rounded-lg hover:bg-brand-light text-gray-800 font-medium"><?= $label ?></a>
        <?php endforeach; ?>
        <div class="border-t border-gray-100 my-2"></div>
        <?php if ($customerUser): ?>
          <a href="<?= url('/customer') ?>" class="px-3 py-2.5 rounded-lg bg-brand-light text-brand-navy font-semibold"><i class="fa-regular fa-user mr-2"></i>My Account</a>
        <?php else: ?>
          <a href="<?= url('/login') ?>" class="px-3 py-2.5 rounded-lg hover:bg-brand-light text-brand-navy font-semibold"><i class="fa-regular fa-user mr-2"></i>Login</a>
          <a href="<?= url('/register') ?>" class="px-3 py-2.5 rounded-lg hover:bg-brand-light text-brand-navy font-semibold"><i class="fa-solid fa-user-plus mr-2"></i>Register</a>
        <?php endif; ?>
        <a href="<?= url('/contact') ?>" class="mt-2 btn-accent px-5 py-2.5 rounded-lg font-semibold text-center"><i class="fa-solid fa-paper-plane mr-2"></i>Book Now</a>
      </div>
    </div>
  </div>
</header>

<?php if ($msg = flash('success')): ?><div class="flash-msg max-w-7xl mx-auto px-4 mt-3"><div class="bg-green-50 border-l-4 border-green-500 text-green-800 p-3 rounded text-sm"><?= htmlspecialchars($msg) ?></div></div><?php endif; ?>
<?php if ($msg = flash('error')): ?><div class="flash-msg max-w-7xl mx-auto px-4 mt-3"><div class="bg-red-50 border-l-4 border-red-500 text-red-800 p-3 rounded text-sm"><?= htmlspecialchars($msg) ?></div></div><?php endif; ?>
