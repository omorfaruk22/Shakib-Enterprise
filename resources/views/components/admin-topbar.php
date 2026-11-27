<?php
$admin = \App\Core\Auth::user();
?>
<header class="bg-white h-16 border-b border-gray-200 flex items-center justify-between px-4 md:px-6 sticky top-0 z-20">
  <div class="lg:w-64"></div>
  <div class="flex items-center gap-4 ml-auto">
    <a href="<?= url('/') ?>" target="_blank" class="hidden sm:inline-flex items-center gap-2 text-sm text-gray-600 hover:text-brand-navy"><i class="fa-solid fa-globe"></i> View Site</a>
    <?php $unread = \App\Models\ContactMessage::unreadCount(); ?>
    <a href="<?= url('/admin/contacts') ?>" class="relative text-gray-600 hover:text-brand-navy text-lg"><i class="fa-solid fa-bell"></i>
      <?php if ($unread > 0): ?><span class="absolute -top-1 -right-1 bg-brand-red text-white text-[10px] w-4 h-4 rounded-full flex items-center justify-center"><?= (int)$unread ?></span><?php endif; ?>
    </a>
    <div class="flex items-center gap-2 relative group">
      <div class="w-9 h-9 rounded-full bg-brand-navy text-white flex items-center justify-center font-semibold text-sm"><?= htmlspecialchars(strtoupper(substr($admin['name'] ?? 'A',0,1))) ?></div>
      <div class="hidden md:block text-sm leading-tight">
        <div class="font-semibold text-gray-800"><?= htmlspecialchars($admin['name'] ?? 'Admin') ?></div>
        <a href="<?= url('/admin/profile') ?>" class="text-xs text-gray-500 hover:text-brand-navy">My Profile</a>
      </div>
      <form method="POST" action="<?= url('/admin/logout') ?>" class="inline">
        <?= csrf_field() ?>
        <button class="text-gray-500 hover:text-brand-red text-sm" title="Logout"><i class="fa-solid fa-right-from-bracket"></i></button>
      </form>
    </div>
  </div>
</header>
