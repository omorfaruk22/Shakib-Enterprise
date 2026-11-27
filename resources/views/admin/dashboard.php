<h1 class="font-display font-extrabold text-2xl md:text-3xl text-brand-navy mb-6">Dashboard</h1>
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">
  <?php partial('admin-stat-card', ['label'=>'Total Bookings','value'=>$stats['total'] ?? 0,'icon'=>'fa-solid fa-calendar-check','color'=>'navy','url'=>'/admin/bookings']); ?>
  <?php partial('admin-stat-card', ['label'=>'Pending','value'=>$stats['pending'] ?? 0,'icon'=>'fa-solid fa-clock','color'=>'amber','url'=>'/admin/bookings?status=Pending']); ?>
  <?php partial('admin-stat-card', ['label'=>'Processing','value'=>$stats['processing'] ?? 0,'icon'=>'fa-solid fa-spinner','color'=>'blue','url'=>'/admin/bookings?status=Processing']); ?>
  <?php partial('admin-stat-card', ['label'=>'Confirmed','value'=>$stats['confirmed'] ?? 0,'icon'=>'fa-solid fa-check-circle','color'=>'green','url'=>'/admin/bookings?status=Confirmed']); ?>
  <?php partial('admin-stat-card', ['label'=>'Completed','value'=>$stats['completed'] ?? 0,'icon'=>'fa-solid fa-flag-checkered','color'=>'navy','url'=>'/admin/bookings?status=Completed']); ?>
  <?php partial('admin-stat-card', ['label'=>'Tours','value'=>$stats['tours'] ?? 0,'icon'=>'fa-solid fa-route','color'=>'red','url'=>'/admin/tour-packages']); ?>
  <?php partial('admin-stat-card', ['label'=>'Holidays','value'=>$stats['holidays'] ?? 0,'icon'=>'fa-solid fa-umbrella-beach','color'=>'purple','url'=>'/admin/holiday-packages']); ?>
  <?php partial('admin-stat-card', ['label'=>'Services','value'=>$stats['services'] ?? 0,'icon'=>'fa-solid fa-briefcase','color'=>'blue','url'=>'/admin/services']); ?>
  <?php partial('admin-stat-card', ['label'=>'Messages','value'=>$stats['messages'] ?? 0,'icon'=>'fa-solid fa-envelope','color'=>'red','url'=>'/admin/contacts']); ?>
  <?php partial('admin-stat-card', ['label'=>'Customers','value'=>$stats['customers'] ?? 0,'icon'=>'fa-solid fa-users','color'=>'gray','url'=>'/admin/customers']); ?>
</div>
<div class="grid lg:grid-cols-2 gap-6">
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
    <div class="flex items-center justify-between mb-4"><h3 class="font-display font-bold text-lg text-brand-navy">Recent Bookings</h3><a href="<?= url('/admin/bookings') ?>" class="text-sm text-brand-navy font-semibold hover:text-brand-red">View All</a></div>
    <?php if (empty($recentBookings)): ?><p class="text-gray-500 text-sm py-6 text-center">No bookings yet.</p><?php else: ?>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead><tr class="text-left text-gray-500 border-b"><th class="py-2">Ref</th><th>Customer</th><th>Service</th><th>Status</th><th>Date</th></tr></thead>
        <tbody>
        <?php foreach ($recentBookings as $b): ?>
          <tr class="border-b last:border-0 hover:bg-gray-50">
            <td class="py-2.5 font-mono text-xs"><a href="<?= url('/admin/bookings/'.$b['id']) ?>" class="text-brand-navy font-semibold"><?= htmlspecialchars($b['booking_reference']) ?></a></td>
            <td><?= htmlspecialchars($b['customer_name']) ?><div class="text-xs text-gray-500"><?= htmlspecialchars($b['customer_phone']) ?></div></td>
            <td class="capitalize"><?= htmlspecialchars(str_replace('_',' ',$b['service_type'])) ?></td>
            <td><?php partial('status-badge', ['status'=>$b['status']]) ?></td>
            <td class="text-xs text-gray-500"><?= htmlspecialchars(date('d M Y',strtotime($b['created_at']))) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
    <div class="flex items-center justify-between mb-4"><h3 class="font-display font-bold text-lg text-brand-navy">Recent Messages</h3><a href="<?= url('/admin/contacts') ?>" class="text-sm text-brand-navy font-semibold hover:text-brand-red">View All</a></div>
    <?php if (empty($recentMessages)): ?><p class="text-gray-500 text-sm py-6 text-center">No messages yet.</p><?php else: ?>
    <div class="space-y-3">
    <?php foreach ($recentMessages as $m): ?>
      <a href="<?= url('/admin/contacts/'.$m['id']) ?>" class="block p-3 rounded-lg hover:bg-brand-light">
        <div class="flex justify-between items-start"><div class="font-semibold text-brand-navy"><?= htmlspecialchars($m['name']) ?></div><div class="text-xs text-gray-400"><?= htmlspecialchars(date('d M',strtotime($m['created_at']))) ?></div></div>
        <div class="text-sm text-gray-600 line-clamp-1"><?= htmlspecialchars($m['subject']) ?> — <?= htmlspecialchars($m['message']) ?></div>
        <?php if (empty($m['is_read'])): ?><span class="text-[10px] text-brand-red font-semibold">NEW</span><?php endif; ?>
      </a>
    <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>
