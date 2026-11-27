<?php $_activeNav='';
\App\Core\View::partial('breadcrumbs', ['items'=>[['label'=>'Home','url'=>'/'],['label'=>'My Account']]]); ?>
<section class="py-10">
<div class="max-w-6xl mx-auto px-4 grid md:grid-cols-4 gap-6">
  <aside class="md:col-span-1">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 sticky top-24">
      <div class="flex items-center gap-3 mb-5 pb-4 border-b">
        <div class="w-12 h-12 rounded-full bg-brand-navy text-white flex items-center justify-center font-bold"><?= htmlspecialchars(strtoupper(substr($customer['name'],0,1))) ?></div>
        <div><div class="font-bold text-brand-navy"><?= htmlspecialchars($customer['name']) ?></div><div class="text-xs text-gray-500"><?= htmlspecialchars($customer['phone']) ?></div></div>
      </div>
      <nav class="space-y-1 text-sm">
        <a href="<?= url('/customer') ?>" class="block px-3 py-2 rounded-lg bg-brand-light text-brand-navy font-semibold"><i class="fa-solid fa-gauge-high mr-2"></i> Dashboard</a>
        <a href="#bookings" class="block px-3 py-2 rounded-lg hover:bg-gray-50 text-gray-700"><i class="fa-solid fa-calendar-check mr-2"></i> My Bookings (<?= count($bookings) ?>)</a>
        <a href="<?= url('/contact') ?>" class="block px-3 py-2 rounded-lg hover:bg-gray-50 text-gray-700"><i class="fa-solid fa-headset mr-2"></i> Contact Support</a>
        <form method="POST" action="<?= url('/logout') ?>"><?= csrf_field() ?><button class="w-full text-left px-3 py-2 rounded-lg hover:bg-red-50 text-red-600"><i class="fa-solid fa-right-from-bracket mr-2"></i> Logout</button></form>
      </nav>
    </div>
  </aside>
  <div class="md:col-span-3 space-y-6">
    <h1 class="font-display font-extrabold text-2xl md:text-3xl text-brand-navy">Welcome back, <?= htmlspecialchars(explode(' ', $customer['name'])[0]) ?> 👋</h1>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm"><div class="text-sm text-gray-500 mb-1">Total Bookings</div><div class="font-display font-extrabold text-3xl text-brand-navy"><?= (int)$stats['total'] ?></div></div>
      <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm"><div class="text-sm text-gray-500 mb-1">Pending</div><div class="font-display font-extrabold text-3xl text-amber-600"><?= (int)$stats['pending'] ?></div></div>
      <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm"><div class="text-sm text-gray-500 mb-1">Confirmed</div><div class="font-display font-extrabold text-3xl text-emerald-600"><?= (int)$stats['confirmed'] ?></div></div>
    </div>
    <div id="bookings" class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
      <h3 class="font-display font-bold text-xl text-brand-navy mb-4">My Bookings</h3>
      <?php if (empty($bookings)): ?>
        <div class="text-center py-10 text-gray-500"><i class="fa-solid fa-inbox text-4xl mb-3 text-gray-300"></i><p>You don't have any bookings yet.</p><a href="<?= url('/tours') ?>" class="mt-3 btn-primary px-5 py-2 rounded-lg text-sm font-semibold inline-block"><i class="fa-solid fa-compass mr-1"></i> Explore Tours</a></div>
      <?php else: ?>
      <div class="overflow-x-auto"><table class="w-full text-sm">
        <thead class="text-left text-gray-500 border-b text-xs uppercase tracking-wider"><tr><th class="py-2">Ref</th><th>Service</th><th>Route</th><th>Date</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($bookings as $b): ?>
        <tr class="border-b last:border-0 hover:bg-gray-50">
          <td class="py-3 font-mono text-xs font-bold text-brand-navy"><?= htmlspecialchars($b['booking_reference']) ?></td>
          <td class="capitalize"><?= htmlspecialchars(str_replace('_',' ',$b['service_type'])) ?></td>
          <td class="text-xs"><?= htmlspecialchars($b['departure'] ?? '—') ?> → <?= htmlspecialchars($b['destination'] ?? '—') ?></td>
          <td class="text-xs"><?= htmlspecialchars($b['journey_date'] ?? '—') ?></td>
          <td><?php \App\Core\View::partial('status-badge', ['status'=>$b['status']]) ?></td>
          <td><a href="<?= url('/customer/bookings/'.$b['id']) ?>" class="text-brand-navy font-bold text-xs">View</a></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table></div>
      <?php endif; ?>
    </div>
  </div>
</div>
</section>
