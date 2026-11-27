<div class="flex items-center justify-between mb-6"><h1 class="font-display font-extrabold text-2xl md:text-3xl text-brand-navy">Customers</h1></div>
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-5">
  <form method="get" class="flex gap-3"><input type="text" name="search" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Search name/phone/email..." class="border border-gray-300 rounded-lg px-3 py-2 text-sm flex-1"><button class="btn-primary px-4 py-2 rounded-lg text-sm font-semibold"><i class="fa-solid fa-search mr-1"></i> Search</button></form>
</div>
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-x-auto">
  <table class="w-full text-sm"><thead class="bg-gray-50 text-left text-gray-600 text-xs uppercase tracking-wider"><tr><th class="p-3">Name</th><th>Phone</th><th>Email</th><th>Bookings</th><th>Created</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($customers as $c): ?>
    <tr class="border-t hover:bg-gray-50">
      <td class="p-3 font-semibold"><?= htmlspecialchars($c['name']) ?></td>
      <td><?= htmlspecialchars($c['phone']) ?></td>
      <td class="text-xs text-gray-500"><?= htmlspecialchars($c['email'] ?? '-') ?></td>
      <td><span class="bg-brand-light text-brand-navy px-2 py-1 rounded text-xs font-bold"><?= (int)$c['booking_count'] ?></span></td>
      <td class="text-xs text-gray-500"><?= htmlspecialchars(date('d M Y',strtotime($c['created_at']))) ?></td>
      <td><a href="<?= url('/admin/customers/'.$c['id']) ?>" class="text-brand-navy font-semibold text-xs">View</a></td>
    </tr>
  <?php endforeach; ?>
  </tbody></table>
</div>
<?php partial('pagination', ['page'=>$page??1,'pages'=>$pages??1]); ?>
