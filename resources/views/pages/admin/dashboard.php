<?php
declare(strict_types=1);
$cards = [
    ['Total inquiries', $counts['bookings'], '/admin/bookings', 'All booking requests'],
    ['Pending', $counts['pending_bookings'], '/admin/bookings?status=pending', 'Need follow-up'],
    ['Processing', $counts['processing_bookings'], '/admin/bookings?status=processing', 'In progress'],
    ['Confirmed', $counts['confirmed_bookings'], '/admin/bookings?status=confirmed', 'Confirmed by the team'],
    ['Customers', $counts['customers'], '/admin/customers', 'Customer records'],
    ['Unread messages', $counts['unread_messages'], '/admin/messages?status=unread', 'Contact inbox'],
    ['Active packages', $counts['active_packages'], '/admin/catalog/packages', 'Published offers'],
    ['Active services', $counts['active_services'], '/admin/catalog/services', 'Public service pages'],
];
?>
<div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
  <div><h1 class="text-3xl font-extrabold text-brand-navy">Good day, <?= e($admin['name']) ?></h1><p class="mt-2 text-slate-600">Current records and recent website activity.</p></div>
  <a class="inline-flex w-fit items-center rounded-xl bg-brand-red px-5 py-3 font-bold text-white hover:bg-red-700" href="<?= e(app_url('/admin/bookings')) ?>">Review inquiries</a>
</div>
<div class="mt-7 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
  <?php foreach ($cards as [$label, $number, $path, $description]): ?>
    <a class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md" href="<?= e(app_url($path)) ?>">
      <span class="text-sm font-semibold text-slate-500"><?= e($label) ?></span><span class="mt-2 block text-3xl font-extrabold text-brand-navy"><?= e_value((string) $number) ?></span><span class="mt-2 block text-xs text-slate-500"><?= e($description) ?></span>
    </a>
  <?php endforeach; ?>
</div>
<div class="mt-7 grid gap-6 xl:grid-cols-[1.5fr_1fr]">
  <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-5 py-4"><h2 class="text-lg font-extrabold text-brand-navy">Recent inquiries</h2><a class="text-sm font-bold text-brand-red hover:underline" href="<?= e(app_url('/admin/bookings')) ?>">View all</a></div>
    <?php if ($recentBookings === []): ?><p class="p-8 text-center text-sm text-slate-500">No booking inquiries yet.</p><?php else: ?>
      <div class="overflow-x-auto"><table class="w-full min-w-[620px] text-left text-sm"><thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3">Reference</th><th class="px-5 py-3">Customer</th><th class="px-5 py-3">Service</th><th class="px-5 py-3">Status</th></tr></thead><tbody class="divide-y divide-slate-100"><?php foreach ($recentBookings as $booking): ?><tr><td class="px-5 py-4"><a class="font-bold text-brand-navy hover:underline" href="<?= e(app_url('/admin/bookings/' . (int) $booking['id'])) ?>"><?= e_value($booking['booking_reference']) ?></a><span class="mt-1 block text-xs text-slate-500"><?= e_value($booking['created_at']) ?></span></td><td class="px-5 py-4"><?= e_value($booking['customer_name']) ?></td><td class="px-5 py-4"><?= e_value(str_replace('_', ' ', $booking['service_type'])) ?></td><td class="px-5 py-4"><span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-800"><?= e_value($booking['status']) ?></span></td></tr><?php endforeach; ?></tbody></table></div>
    <?php endif; ?>
  </section>
  <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-5 py-4"><h2 class="text-lg font-extrabold text-brand-navy">Recent contact messages</h2><a class="text-sm font-bold text-brand-red hover:underline" href="<?= e(app_url('/admin/messages')) ?>">Inbox</a></div>
    <?php if ($recentMessages === []): ?><p class="p-8 text-center text-sm text-slate-500">No contact messages yet.</p><?php else: ?><ul class="divide-y divide-slate-100"><?php foreach ($recentMessages as $message): ?><li class="px-5 py-4"><a class="font-bold text-brand-navy hover:underline" href="<?= e(app_url('/admin/messages/' . (int) $message['id'])) ?>"><?= e_value($message['subject'] ?: $message['name']) ?></a><span class="mt-1 block text-xs text-slate-500"><?= e_value($message['created_at']) ?> · <?= e_value($message['status']) ?></span></li><?php endforeach; ?></ul><?php endif; ?>
  </section>
</div>
<section class="mt-7 rounded-2xl border border-slate-200 bg-white shadow-sm">
  <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-5 py-4"><h2 class="text-lg font-extrabold text-brand-navy">Recent activity</h2><a class="text-sm font-bold text-brand-red hover:underline" href="<?= e(app_url('/admin/activity')) ?>">Full log</a></div>
  <?php if ($recentActivity === []): ?><p class="p-7 text-sm text-slate-500">Admin changes will appear here.</p><?php else: ?><ul class="divide-y divide-slate-100 sm:grid sm:grid-cols-2 sm:divide-y-0"><?php foreach ($recentActivity as $activity): ?><li class="px-5 py-4 sm:border-b sm:border-slate-100"><p class="font-semibold text-slate-800"><?= e_value(str_replace('.', ' ', $activity['action'])) ?></p><p class="mt-1 text-xs text-slate-500"><?= e_value($activity['admin_name'] ?? 'System') ?> · <?= e_value($activity['created_at']) ?></p></li><?php endforeach; ?></ul><?php endif; ?>
</section>
