<div class="flex items-center justify-between mb-6">
  <h1 class="font-display font-extrabold text-2xl md:text-3xl text-brand-navy">Booking Inquiries</h1>
  <a href="<?= url('/admin/bookings/export') ?>" class="btn-outline px-4 py-2 rounded-lg font-semibold text-sm inline-flex items-center gap-2"><i class="fa-solid fa-download"></i> Export CSV</a>
</div>
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-5">
  <form method="get" class="grid md:grid-cols-4 gap-3">
    <input type="text" name="search" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Search ref/name/phone..." class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
    <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
      <option value="">All Status</option>
      <?php foreach (['Pending','Contacted','Processing','Confirmed','Completed','Cancelled','Rejected'] as $st): ?>
        <option value="<?= $st ?>" <?= ($statusFilter ?? '') === $st ? 'selected' : '' ?>><?= $st ?></option>
      <?php endforeach; ?>
    </select>
    <select name="service_type" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
      <option value="">All Services</option>
      <?php foreach (['air','ship','bus','tour','holiday'] as $s): ?>
        <option value="<?= $s ?>" <?= ($typeFilter ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
      <?php endforeach; ?>
    </select>
    <button class="btn-primary px-4 py-2 rounded-lg text-sm font-semibold"><i class="fa-solid fa-filter mr-1"></i> Filter</button>
  </form>
</div>
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-x-auto">
  <table class="w-full text-sm">
    <thead class="bg-gray-50 text-left text-gray-600 text-xs uppercase tracking-wider">
      <tr><th class="p-3">Ref</th><th>Customer</th><th>Service</th><th>Route</th><th>Date</th><th>Status</th><th>Created</th><th></th></tr>
    </thead>
    <tbody>
      <?php foreach ($bookings as $b): ?>
      <tr class="border-t hover:bg-gray-50">
        <td class="p-3 font-mono text-xs font-bold"><a href="<?= url('/admin/bookings/'.$b['id']) ?>" class="text-brand-navy hover:text-brand-red"><?= htmlspecialchars($b['booking_reference']) ?></a></td>
        <td><div class="font-semibold"><?= htmlspecialchars($b['customer_name']) ?></div><div class="text-xs text-gray-500"><?= htmlspecialchars($b['customer_phone']) ?></div></td>
        <td class="capitalize"><?= htmlspecialchars(str_replace('_',' ',$b['service_type'])) ?></td>
        <td class="text-xs"><?= htmlspecialchars($b['departure'] ?? '') ?> → <?= htmlspecialchars($b['destination'] ?? '') ?></td>
        <td class="text-xs"><?= htmlspecialchars($b['journey_date'] ?? '-') ?><br><span class="text-gray-500"><?= (int)$b['passenger_count'] ?> pax</span></td>
        <td><?php partial('status-badge', ['status'=>$b['status']]) ?></td>
        <td class="text-xs text-gray-500"><?= htmlspecialchars(date('d M Y H:i',strtotime($b['created_at']))) ?></td>
        <td><a href="<?= url('/admin/bookings/'.$b['id']) ?>" class="text-brand-navy font-semibold text-xs">View <i class="fa-solid fa-chevron-right"></i></a></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php partial('pagination', ['page'=>$page??1,'pages'=>$pages??1]); ?>
