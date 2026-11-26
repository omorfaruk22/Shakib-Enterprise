<?php
declare(strict_types=1);
$serviceLabels = [
    'air_ticket' => 'Air ticket',
    'ship_ticket' => 'Ship ticket',
    'bus_ticket' => 'Bus ticket',
    'tour_package' => 'Tour package',
    'holiday_package' => 'Holiday package',
];
?>
<div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
  <div><h1 class="text-3xl font-extrabold text-brand-navy">Booking inquiries</h1><p class="mt-2 text-slate-600"><?= e_value((string) $result['total']) ?> records match these filters.</p></div>
  <a class="inline-flex w-fit items-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-brand-navy hover:bg-slate-50" href="<?= e($exportUrl) ?>">Export filtered CSV</a>
</div>
<form class="mt-6 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-2 xl:grid-cols-6" action="<?= e(app_url('/admin/bookings')) ?>" method="get">
  <label class="sm:col-span-2"><span class="block text-xs font-bold text-slate-600">Search</span><input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" name="q" type="search" maxlength="120" placeholder="Reference, customer, phone, destination" value="<?= e_value($filters['q']) ?>"></label>
  <label><span class="block text-xs font-bold text-slate-600">Status</span><select class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" name="status"><option value="">All statuses</option><?php foreach ($statuses as $status): ?><option value="<?= e($status) ?>"<?= $filters['status'] === $status ? ' selected' : '' ?>><?= e(ucfirst($status)) ?></option><?php endforeach; ?></select></label>
  <label><span class="block text-xs font-bold text-slate-600">Service</span><select class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" name="service_type"><option value="">All services</option><?php foreach ($serviceLabels as $type => $label): ?><option value="<?= e($type) ?>"<?= $filters['service_type'] === $type ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></label>
  <label><span class="block text-xs font-bold text-slate-600">From</span><input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" name="date_from" type="date" value="<?= e_value($filters['date_from']) ?>"></label>
  <label><span class="block text-xs font-bold text-slate-600">To</span><input class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" name="date_to" type="date" value="<?= e_value($filters['date_to']) ?>"></label>
  <div class="flex items-end gap-2 sm:col-span-2 xl:col-span-6"><button class="rounded-lg bg-brand-navy px-4 py-2.5 text-sm font-bold text-white hover:bg-blue-900" type="submit">Apply filters</button><a class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50" href="<?= e(app_url('/admin/bookings')) ?>">Clear</a></div>
</form>
<section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
  <?php if ($result['items'] === []): ?><div class="p-12 text-center"><h2 class="text-lg font-bold text-brand-navy">No inquiries found</h2><p class="mt-2 text-sm text-slate-500">Try changing or clearing the filters.</p></div><?php else: ?>
    <div class="overflow-x-auto"><table class="w-full min-w-[820px] text-left text-sm">
      <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3">Inquiry</th><th class="px-5 py-3">Customer</th><th class="px-5 py-3">Travel</th><th class="px-5 py-3">Status</th><th class="px-5 py-3">Submitted</th><th class="px-5 py-3"><span class="sr-only">Open</span></th></tr></thead>
      <tbody class="divide-y divide-slate-100">
        <?php foreach ($result['items'] as $booking): ?>
          <tr class="hover:bg-slate-50">
            <td class="px-5 py-4"><a class="font-bold text-brand-navy hover:underline" href="<?= e(app_url('/admin/bookings/' . (int) $booking['id'])) ?>"><?= e_value($booking['booking_reference']) ?></a><span class="mt-1 block text-xs text-slate-500"><?= e($serviceLabels[$booking['service_type']] ?? 'Travel inquiry') ?></span></td>
            <td class="px-5 py-4"><span class="font-semibold"><?= e_value($booking['customer_name']) ?></span><span class="mt-1 block text-xs text-slate-500"><?= e_value($booking['customer_phone']) ?></span></td>
            <td class="px-5 py-4"><span><?= e_value($booking['departure_location'] ?: $booking['ship_origin'] ?: '—') ?> → <?= e_value($booking['destination'] ?: $booking['ship_destination'] ?: '—') ?></span><span class="mt-1 block text-xs text-slate-500"><?= e_value($booking['journey_date'] ?: 'Date not specified') ?></span></td>
            <td class="px-5 py-4"><span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-800"><?= e_value(ucfirst($booking['status'])) ?></span></td>
            <td class="px-5 py-4 text-xs text-slate-600"><?= e_value($booking['created_at']) ?></td>
            <td class="px-5 py-4"><a class="font-bold text-brand-red hover:underline" href="<?= e(app_url('/admin/bookings/' . (int) $booking['id'])) ?>">Open</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table></div>
  <?php endif; ?>
</section>
<?php if ($result['pages'] > 1): ?>
  <nav class="mt-5 flex items-center justify-between gap-3" aria-label="Booking pages">
    <span class="text-sm text-slate-600">Page <?= e_value((string) $result['page']) ?> of <?= e_value((string) $result['pages']) ?></span>
    <div class="flex gap-2">
      <?php if ($result['page'] > 1): ?><a class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold" href="<?= e($pageUrl($result['page'] - 1)) ?>">Previous</a><?php endif; ?>
      <?php if ($result['page'] < $result['pages']): ?><a class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold" href="<?= e($pageUrl($result['page'] + 1)) ?>">Next</a><?php endif; ?>
    </div>
  </nav>
<?php endif; ?>
