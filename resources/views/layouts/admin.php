<?php
declare(strict_types=1);

$adminSiteName = trim((string) env_value('APP_NAME', 'SAKIB ENTERPRISE')) ?: 'SAKIB ENTERPRISE';
$navigation = [
    ['/admin', 'Overview'],
    ['/admin/bookings', 'Bookings'],
    ['/admin/customers', 'Customers'],
    ['/admin/catalog/services', 'Services'],
    ['/admin/catalog/destinations', 'Destinations'],
    ['/admin/catalog/packages', 'Packages'],
    ['/admin/catalog/ship-routes', 'Ship routes'],
    ['/admin/messages', 'Messages'],
    ['/admin/content/pages', 'Website pages'],
    ['/admin/testimonials', 'Testimonials'],
    ['/admin/settings', 'Settings'],
    ['/admin/media', 'Media'],
    ['/admin/activity', 'Activity log'],
];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex,nofollow">
  <title><?= e($adminPageTitle) ?> | <?= e($adminSiteName) ?> Admin</title>
  <link rel="stylesheet" href="<?= e(app_url('assets/css/site.css')) ?>">
  <script defer src="<?= e(app_url('assets/js/site.js')) ?>"></script>
</head>
<body class="min-h-screen bg-slate-100 font-sans text-slate-800 antialiased">
  <?php if ($admin !== null): ?>
    <aside class="fixed inset-y-0 left-0 z-30 hidden w-64 flex-col bg-brand-deep text-white lg:flex">
      <a class="flex items-center gap-3 border-b border-white/10 px-6 py-6 font-extrabold" href="<?= e(app_url('/admin')) ?>">
        <span class="grid size-11 place-items-center rounded-2xl bg-brand-red">SE</span>
        <span><?= e($adminSiteName) ?><small class="mt-1 block text-[10px] font-bold tracking-[.16em] text-blue-200">ADMIN WORKSPACE</small></span>
      </a>
      <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-5" aria-label="Admin navigation">
        <?php foreach ($navigation as [$path, $label]): ?>
          <?php $active = $path === '/admin' ? $adminCurrentPath === $path : str_starts_with($adminCurrentPath, $path); ?>
          <a class="<?= $active ? 'bg-white/15 text-white' : 'text-blue-100 hover:bg-white/10 hover:text-white' ?> block rounded-xl px-4 py-2.5 text-sm font-semibold" href="<?= e(app_url($path)) ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
      </nav>
      <div class="border-t border-white/10 p-4">
        <p class="truncate px-2 text-xs text-blue-200"><?= e($admin['email']) ?></p>
        <form class="mt-3" action="<?= e(app_url('/admin/logout')) ?>" method="post">
          <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
          <button class="w-full rounded-xl border border-white/20 px-4 py-2.5 text-left text-sm font-semibold text-white hover:bg-white/10" type="submit">Sign out</button>
        </form>
      </div>
    </aside>
  <?php endif; ?>

  <div class="<?= $admin !== null ? 'lg:pl-64' : '' ?>">
    <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/95 backdrop-blur">
      <div class="mx-auto flex min-h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6">
        <div>
          <p class="text-xs font-bold uppercase tracking-[.14em] text-brand-red"><?= e($adminSiteName) ?></p>
          <p class="font-bold text-brand-navy"><?= e($adminPageTitle) ?></p>
        </div>
        <?php if ($admin !== null): ?>
          <div class="hidden text-right sm:block"><p class="text-sm font-bold"><?= e($admin['name']) ?></p><p class="text-xs text-slate-500"><?= e($admin['role']) ?></p></div>
          <form class="lg:hidden" action="<?= e(app_url('/admin/logout')) ?>" method="post"><input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>"><button class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold" type="submit">Sign out</button></form>
        <?php else: ?>
          <a class="text-sm font-semibold text-brand-navy hover:text-brand-red" href="<?= e(app_url('/')) ?>">View website</a>
        <?php endif; ?>
      </div>
      <?php if ($admin !== null): ?>
        <nav class="flex gap-2 overflow-x-auto px-4 pb-3 lg:hidden" aria-label="Admin navigation">
          <?php foreach ($navigation as [$path, $label]): ?>
            <a class="shrink-0 rounded-full border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700" href="<?= e(app_url($path)) ?>"><?= e($label) ?></a>
          <?php endforeach; ?>
        </nav>
      <?php endif; ?>
    </header>
    <main class="mx-auto max-w-7xl px-4 py-7 sm:px-6 sm:py-10">
      <?php if ($adminFlash !== null): ?>
        <div class="<?= ($adminFlash['type'] ?? '') === 'error' ? 'border-red-200 bg-red-50 text-red-800' : 'border-emerald-200 bg-emerald-50 text-emerald-800' ?> mb-6 rounded-2xl border p-4 text-sm font-semibold" role="status"><?= e_value($adminFlash['message'] ?? '') ?></div>
      <?php endif; ?>
      <?php require $adminContentView; ?>
    </main>
    <footer class="border-t border-slate-200 px-4 py-5 text-center text-xs text-slate-500 sm:px-6">Private administration area · <?= e($adminSiteName) ?></footer>
  </div>
</body>
</html>
