<div class="flex items-center justify-between mb-6"><h1 class="font-display font-extrabold text-2xl md:text-3xl text-brand-navy"><?= htmlspecialchars($customer['name']) ?></h1><a href="<?= url('/admin/customers') ?>" class="text-sm text-gray-500 hover:text-brand-navy"><i class="fa-solid fa-arrow-left mr-1"></i> Back</a></div>
<div class="grid lg:grid-cols-3 gap-6">
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
  <div class="w-20 h-20 rounded-full bg-brand-navy text-white flex items-center justify-center font-display font-extrabold text-2xl mb-3"><?= htmlspecialchars(strtoupper(substr($customer['name'],0,1))) ?></div>
  <div class="space-y-2 text-sm"><div><span class="text-gray-500">Phone:</span> <a href="tel:<?= htmlspecialchars($customer['phone']) ?>" class="text-brand-navy font-semibold"><?= htmlspecialchars($customer['phone']) ?></a></div><div><span class="text-gray-500">Email:</span> <?= htmlspecialchars($customer['email'] ?? '—') ?></div><div><span class="text-gray-500">Customer Since:</span> <?= htmlspecialchars(date('d M Y',strtotime($customer['created_at']))) ?></div></div>
</div>
<div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
  <h3 class="font-bold text-brand-navy mb-4">Booking History (<?= count($bookings) ?>)</h3>
  <div class="overflow-x-auto"><table class="w-full text-sm"><thead class="bg-gray-50 text-left text-gray-600 text-xs uppercase tracking-wider"><tr><th class="p-2">Ref</th><th>Service</th><th>Route</th><th>Date</th><th>Status</th><th></th></tr></thead><tbody>
  <?php foreach ($bookings as $b): ?><tr class="border-t"><td class="p-2 font-mono text-xs"><a href="<?= url('/admin/bookings/'.$b['id']) ?>" class="text-brand-navy font-bold"><?= htmlspecialchars($b['booking_reference']) ?></a></td><td class="capitalize"><?= htmlspecialchars(str_replace('_',' ',$b['service_type'])) ?></td><td class="text-xs"><?= htmlspecialchars($b['departure'] ?? '') ?>→<?= htmlspecialchars($b['destination'] ?? '') ?></td><td class="text-xs"><?= htmlspecialchars($b['journey_date'] ?? '-') ?></td><td><?php partial('status-badge',['status'=>$b['status']]) ?></td><td><a href="<?= url('/admin/bookings/'.$b['id']) ?>" class="text-brand-navy text-xs font-bold">View</a></td></tr><?php endforeach; ?>
  </tbody></table></div>
</div>
</div>
