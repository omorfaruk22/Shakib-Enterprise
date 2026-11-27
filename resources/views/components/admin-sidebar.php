<?php
// Active nav detection based on request URI
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$seg = trim(parse_url($uri, PHP_URL_PATH) ?: '/', '/');
$parts = explode('/', $seg);
$section = $parts[1] ?? 'dashboard'; // after "admin/"
$adminRole = $admin['role'] ?? 'admin';
$isFullAdmin = in_array($adminRole, ['super_admin','admin']);
$nav = [
  'dashboard' => ['label'=>'Dashboard','icon'=>'fa-solid fa-gauge-high','url'=>'/admin/dashboard'],
  'bookings'  => ['label'=>'Bookings','icon'=>'fa-solid fa-calendar-check','url'=>'/admin/bookings'],
  'customers' => ['label'=>'Customers','icon'=>'fa-solid fa-users','url'=>'/admin/customers'],
];
if ($isFullAdmin) {
  $nav += [
    'services'  => ['label'=>'Services','icon'=>'fa-solid fa-briefcase','url'=>'/admin/services'],
    'tour-packages'=>['label'=>'Tour Packages','icon'=>'fa-solid fa-route','url'=>'/admin/tour-packages'],
    'holiday-packages'=>['label'=>'Holiday Packages','icon'=>'fa-solid fa-umbrella-beach','url'=>'/admin/holiday-packages'],
    'destinations'=>['label'=>'Destinations','icon'=>'fa-solid fa-location-dot','url'=>'/admin/destinations'],
    'ship-routes'=>['label'=>'Ship Routes','icon'=>'fa-solid fa-ship','url'=>'/admin/ship-routes'],
    'testimonials'=>['label'=>'Testimonials','icon'=>'fa-solid fa-quote-left','url'=>'/admin/testimonials'],
  ];
}
$nav += [
  'contacts'  => ['label'=>'Messages','icon'=>'fa-solid fa-envelope','url'=>'/admin/contacts'],
];
if ($isFullAdmin) {
  $nav += [
    'settings'  => ['label'=>'Website Settings','icon'=>'fa-solid fa-cog','url'=>'/admin/settings'],
  ];
  if ($adminRole === 'super_admin') {
    $nav += ['managers' => ['label'=>'Admin Users','icon'=>'fa-solid fa-user-shield','url'=>'/admin/managers']];
  }
  $nav += ['activity-logs'=>['label'=>'Activity Logs','icon'=>'fa-solid fa-clock-rotate-left','url'=>'/admin/activity-logs']];
}
$unreadContacts = \App\Models\ContactMessage::unreadCount();
?>
<aside class="fixed inset-y-0 left-0 w-64 bg-brand-dark text-white hidden lg:flex flex-col z-30">
  <div class="h-16 flex items-center gap-3 px-5 border-b border-white/10">
    <div class="w-10 h-10 rounded-lg bg-white text-brand-navy flex items-center justify-center font-display font-extrabold">SE</div>
    <div>
      <div class="font-display font-bold leading-tight">SAKIB ENTERPRISE</div>
      <div class="text-xs text-gray-400">Admin Panel</div>
    </div>
  </div>
  <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-0.5 text-sm">
    <?php foreach ($nav as $key => $n): ?>
      <?php $active = ($section === $key); ?>
      <a href="<?= url($n['url']) ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?= $active ? 'bg-brand-red text-white' : 'text-gray-300 hover:bg-white/5 hover:text-white' ?> transition">
        <i class="<?= $n['icon'] ?> w-5 text-center"></i>
        <span class="flex-1"><?= $n['label'] ?></span>
        <?php if ($key === 'contacts' && $unreadContacts > 0): ?>
          <span class="bg-brand-red text-white text-[10px] px-2 py-0.5 rounded-full"><?= (int)$unreadContacts ?></span>
        <?php endif; ?>
      </a>
    <?php endforeach; ?>
  </nav>
  <div class="p-3 border-t border-white/10">
    <a href="<?= url('/') ?>" target="_blank" class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-300 hover:bg-white/5 hover:text-white text-sm"><i class="fa-solid fa-arrow-up-right-from-square w-5 text-center"></i> View Website</a>
  </div>
</aside>
<!-- Mobile top bar toggle -->
<div class="lg:hidden fixed top-4 left-4 z-40">
  <button id="mobile-sidebar-toggle" class="w-10 h-10 bg-white rounded-lg shadow flex items-center justify-center text-brand-navy"><i class="fa-solid fa-bars"></i></button>
</div>
